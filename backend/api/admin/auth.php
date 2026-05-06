<?php
// Admin authentication: login (password + OTP), verify, logout, check
require_once __DIR__ . '/../../admin-config.php';
$_smsFile = __DIR__ . '/../../sms.php';
if (is_file($_smsFile)) require_once $_smsFile;
if (!defined('SMS_ENABLED')) define('SMS_ENABLED', false);
require_once __DIR__ . '/../../sms-helpers.php';
cors();
adminSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_err('Method not allowed', 405);

$b = body();
$action = str_clean($b['action'] ?? '', 20);

switch ($action) {

    case 'login': {
        $username = str_clean($b['username'] ?? '', 100);
        $password = $b['password'] ?? '';
        if (!$username || !$password) json_err('Username and password required.');
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = :u AND status = 'active' LIMIT 1");
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();
        if (!$admin) {
            pushAdminLog('Failed Login', 'Unknown: ' . $username, 'login');
            json_err('Invalid username or account inactive.');
        }
        $hashOk = password_verify($password, $admin['password']);
        if (!$hashOk) {
            // Fallback: plain-text comparison for accounts not yet upgraded to bcrypt
            if ($password !== $admin['password']) {
                pushAdminLog('Failed Login', 'Bad password: ' . $username, 'login');
                json_err('Invalid username or password.');
            }
            // Plain-text matched — silently upgrade to bcrypt
            try {
                $db->prepare("UPDATE admins SET password = :p WHERE id = :id")
                   ->execute([':p' => password_hash($password, PASSWORD_DEFAULT), ':id' => $admin['id']]);
            } catch (Throwable $e) { /* column/permission issue — non-fatal */ }
        }

        // Generate OTP and send to admin's mobile
        $mobile = $admin['mobile'];
        if (!$mobile || strlen($mobile) < 10) {
            // No mobile — direct login fallback
            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_role'] = $admin['role'];
            session_regenerate_id(true);
            pushAdminLog('Logged In (no OTP)', $admin['name'], 'login', $admin);
            json_ok(['admin' => ['id' => $admin['id'], 'name' => $admin['name'], 'username' => $admin['username'], 'role' => $admin['role'], 'mobile' => $mobile], 'direct' => true]);
        }

        $otp = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $exp = date('Y-m-d H:i:s', time() + 120);

        $db->prepare(
            "INSERT INTO otp_sessions (mobile, otp, attempts, expires_at, verified)
             VALUES (:m, :o, 0, :e, 0)
             ON DUPLICATE KEY UPDATE otp = VALUES(otp), attempts = 0, expires_at = VALUES(expires_at), verified = 0"
        )->execute([':m' => 'admin_' . $admin['id'], ':o' => $otp, ':e' => $exp]);

        $_SESSION['admin_pending_id'] = $admin['id'];

        // Send OTP via SMS
        $smsResult = sendOTP($mobile, $otp);

        $response = [
            'needOtp' => true,
            'mobile'  => $mobile,
            'name'    => $admin['name'],
            'msg'     => 'OTP sent to your registered mobile.'
        ];

        if (!SMS_ENABLED || !$smsResult['success']) {
            $response['otp'] = $otp; // Fallback
        }

        json_ok($response);
    }

    case 'verify': {
        $otp = str_clean($b['otp'] ?? '', 10);
        if (empty($_SESSION['admin_pending_id'])) json_err('No pending login.');
        $adminId = $_SESSION['admin_pending_id'];
        $db = getDB();
        $sess = $db->prepare("SELECT * FROM otp_sessions WHERE mobile = :m LIMIT 1");
        $sess->execute([':m' => 'admin_' . $adminId]);
        $row = $sess->fetch();
        if (!$row) json_err('No OTP found. Request again.');
        if ($row['verified']) json_err('OTP already used.');
        if (strtotime($row['expires_at']) < time()) json_err('OTP expired.');
        if ((int)$row['attempts'] >= 5) json_err('Too many attempts.');
        if ($row['otp'] !== $otp) {
            $db->prepare("UPDATE otp_sessions SET attempts = attempts + 1 WHERE mobile = :m")
               ->execute([':m' => 'admin_' . $adminId]);
            json_err('Invalid OTP.', 401);
        }
        $db->prepare("UPDATE otp_sessions SET verified = 1 WHERE mobile = :m")
           ->execute([':m' => 'admin_' . $adminId]);
        $admin = $db->prepare("SELECT * FROM admins WHERE id = :id LIMIT 1");
        $admin->execute([':id' => $adminId]);
        $adm = $admin->fetch();
        $_SESSION['admin_id']   = $adm['id'];
        $_SESSION['admin_name'] = $adm['name'];
        $_SESSION['admin_role'] = $adm['role'];
        unset($_SESSION['admin_pending_id']);
        session_regenerate_id(true);
        pushAdminLog('Logged In', $adm['name'] . ' (@' . $adm['username'] . ')', 'login', $adm);
        json_ok(['admin' => ['id' => $adm['id'], 'name' => $adm['name'], 'username' => $adm['username'], 'role' => $adm['role'], 'mobile' => $adm['mobile']]]);
    }

    case 'logout': {
        if (!empty($_SESSION['admin_name'])) {
            pushAdminLog('Logged Out', $_SESSION['admin_name'], 'login');
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        json_ok(['msg' => 'Logged out.']);
    }

    case 'check': {
        $loggedIn = !empty($_SESSION['admin_id']);
        $admin = null;
        if ($loggedIn) {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, name, username, role, mobile FROM admins WHERE id = :id AND status = 'active' LIMIT 1");
            $stmt->execute([':id' => $_SESSION['admin_id']]);
            $admin = $stmt->fetch();
            if (!$admin) $loggedIn = false;
        }
        json_ok(['loggedIn' => $loggedIn, 'admin' => $admin]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Issue a one-time token so the admin can jump into user-panel as THEIR own
    // mobile without re-doing OTP. Token is 60-second TTL, single-use, and the
    // resulting user-panel session hard-expires after 30 minutes.
    // ─────────────────────────────────────────────────────────────────────────
    case 'my_account_token': {
        $admin = adminRequired();
        $mobile = preg_replace('/\D/', '', $admin['mobile'] ?? '');
        if (strlen($mobile) !== 10) {
            json_err('Your admin account has no valid 10-digit mobile. Update your admin profile first.', 400);
        }
        $token = bin2hex(random_bytes(16));
        cache_set('admin_imp:' . $token, [
            'admin_id'   => (int)$admin['id'],
            'admin_name' => $admin['name'],
            'mobile'     => $mobile,
            'issued_at'  => time(),
        ], 60); // token must be redeemed within 60s
        pushAdminLog('My Account Opened', 'As ' . $mobile, 'login', $admin);
        json_ok([
            'token' => $token,
            'url'   => '/backend/user-panel.php?admin_token=' . $token,
        ]);
    }

    case 'forgot': {
        $username = str_clean($b['username'] ?? '', 100);
        $mobile   = str_clean($b['mobile'] ?? '', 15);
        if (!$username || !$mobile) json_err('Username and mobile required.');
        $db = getDB();
        $admin = $db->prepare("SELECT * FROM admins WHERE username = :u AND mobile = :m LIMIT 1");
        $admin->execute([':u' => $username, ':m' => $mobile]);
        $adm = $admin->fetch();
        if (!$adm) json_err('No admin found with that username and mobile.');
        $otp = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $exp = date('Y-m-d H:i:s', time() + 120);
        $db->prepare(
            "INSERT INTO otp_sessions (mobile, otp, attempts, expires_at, verified)
             VALUES (:m, :o, 0, :e, 0)
             ON DUPLICATE KEY UPDATE otp = VALUES(otp), attempts = 0, expires_at = VALUES(expires_at), verified = 0"
        )->execute([':m' => 'admin_' . $adm['id'], ':o' => $otp, ':e' => $exp]);
        $_SESSION['admin_pending_id'] = $adm['id'];

        // Send OTP via SMS
        $smsResult = sendOTP($adm['mobile'], $otp);

        $response = ['mobile' => $adm['mobile'], 'name' => $adm['name'], 'msg' => 'OTP sent.'];
        if (!SMS_ENABLED) {
            $response['otp'] = $otp; // Demo mode only
        }
        json_ok($response);
    }

    default: json_err('Unknown action.');
}
