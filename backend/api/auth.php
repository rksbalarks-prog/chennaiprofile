<?php
// matrimony/api/auth.php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../sms.php';

cors();
secureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_err('Method not allowed', 405);
}

$b      = body();
$action = str_clean($b['action'] ?? '', 20);

switch ($action) {

    // ── direct login (no OTP) ───────────────────────────────────────────────
    case 'direct_login': {
        $mobile = str_clean($b['mobile'] ?? '', 15);
        if (!preg_match('/^\d{10}$/', $mobile)) json_err('Invalid mobile.');

        $db = getDB();
        $dl = $db->prepare("SELECT * FROM direct_login WHERE mobile = :m AND status = 'active' LIMIT 1");
        $dl->execute([':m' => $mobile]);
        $row = $dl->fetch();
        if (!$row) json_err('Direct login not enabled for this number.', 403);

        // Update usage & log
        $db->prepare("UPDATE direct_login SET last_used = NOW(), use_count = use_count + 1 WHERE id = :id")
           ->execute([':id' => $row['id']]);
        $db->prepare("INSERT INTO direct_login_log (mobile, name, cp_id, action, action_by, created_at) VALUES (:m, :n, :cp, 'Login Used', 'User', NOW())")
           ->execute([':m' => $mobile, ':n' => $row['name'], ':cp' => $row['cp_id']]);

        // Set session
        $_SESSION['mobile'] = $mobile;
        session_regenerate_id(true);

        // Update otp_logs
        $prof = $db->prepare("SELECT cp_id, name FROM profiles WHERE mobile = :m LIMIT 1");
        $prof->execute([':m' => $mobile]);
        $p = $prof->fetch();
        $db->prepare("INSERT INTO otp_logs (mobile, cp_id, name, otp_requested_at, verified, last_login, login_count, banned)
            VALUES (:m, :cp, :n, NOW(), 'verified', NOW(), 1, 0)
            ON DUPLICATE KEY UPDATE verified='verified', last_login=NOW(), login_count=login_count+1")
           ->execute([':m' => $mobile, ':cp' => $p['cp_id'] ?? null, ':n' => $p['name'] ?? null]);

        json_ok(['mobile' => $mobile, 'direct' => true]);
    }

    // ── check direct login eligibility ──────────────────────────────────────
    case 'check_direct': {
        $mobile = str_clean($b['mobile'] ?? '', 15);
        if (!$mobile) json_err('Mobile required');
        $db = getDB();
        $dl = $db->prepare("SELECT status FROM direct_login WHERE mobile = :m LIMIT 1");
        $dl->execute([':m' => $mobile]);
        $row = $dl->fetch();
        json_ok(['direct' => $row && $row['status'] === 'active']);
    }

    // ── send OTP ─────────────────────────────────────────────────────────────
    case 'send': {
        $mobile = str_clean($b['mobile'] ?? '', 15);

        if (!preg_match('/^\d{10}$/', $mobile)) {
            json_err('Invalid mobile number. Must be 10 digits.');
        }

        $db  = getDB();
        $otp = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $exp = date('Y-m-d H:i:s', time() + 120); // 2 minutes

        // Upsert otp_sessions (update if mobile already exists)
        $stmt = $db->prepare(
            "INSERT INTO otp_sessions (mobile, otp, attempts, expires_at, verified)
             VALUES (:m, :o, 0, :e, 0)
             ON DUPLICATE KEY UPDATE
               otp        = VALUES(otp),
               attempts   = 0,
               expires_at = VALUES(expires_at),
               verified   = 0"
        );
        $stmt->execute([':m' => $mobile, ':o' => $otp, ':e' => $exp]);

        // Upsert otp_logs – record that OTP was requested
        $profile = $db->prepare("SELECT cp_id, name FROM profiles WHERE mobile = :m LIMIT 1");
        $profile->execute([':m' => $mobile]);
        $prof = $profile->fetch();

        $stmt2 = $db->prepare(
            "INSERT INTO otp_logs (mobile, cp_id, name, otp_requested_at, verified, login_count, banned)
             VALUES (:m, :c, :n, NOW(), 'otp_request', 0, 0)
             ON DUPLICATE KEY UPDATE
               cp_id              = COALESCE(VALUES(cp_id), cp_id),
               name               = COALESCE(VALUES(name), name),
               otp_requested_at   = NOW(),
               verified           = IF(verified = 'verified', verified, 'otp_request')"
        );
        $stmt2->execute([
            ':m' => $mobile,
            ':c' => $prof['cp_id'] ?? null,
            ':n' => $prof['name']  ?? null,
        ]);

        // Send OTP via SMS
        $smsResult = sendOTP($mobile, $otp);

        $response = ['msg' => 'OTP sent successfully.'];

        if (SMS_ENABLED && !$smsResult['success']) {
            // SMS failed — return OTP as fallback so user isn't locked out
            $response['msg'] = 'OTP generated. SMS delivery may be delayed.';
            $response['otp'] = $otp;
            $response['sms_error'] = $smsResult['error'];
        }

        if (!SMS_ENABLED) {
            $response['otp'] = $otp; // Demo mode
        }

        json_ok($response);
    }

    // ── verify OTP ───────────────────────────────────────────────────────────
    case 'verify': {
        $mobile = str_clean($b['mobile'] ?? '', 15);
        $otp    = str_clean($b['otp']    ?? '', 10);

        if (!preg_match('/^\d{10}$/', $mobile)) {
            json_err('Invalid mobile number.');
        }
        if (!preg_match('/^\d{4}$/', $otp)) {
            json_err('Invalid OTP format.');
        }

        $db = getDB();

        // Check ban status
        $banCheck = $db->prepare("SELECT banned FROM otp_logs WHERE mobile = :m LIMIT 1");
        $banCheck->execute([':m' => $mobile]);
        $banRow = $banCheck->fetch();
        if ($banRow && (int)$banRow['banned'] === 1) {
            json_err('This mobile number is banned.', 403);
        }

        // Fetch OTP session
        $stmt = $db->prepare(
            "SELECT otp, attempts, expires_at, verified FROM otp_sessions
             WHERE mobile = :m LIMIT 1"
        );
        $stmt->execute([':m' => $mobile]);
        $sess = $stmt->fetch();

        if (!$sess) {
            json_err('No OTP request found. Please request a new OTP.');
        }
        if ($sess['verified']) {
            json_err('OTP already used. Please request a new OTP.');
        }
        if (strtotime($sess['expires_at']) < time()) {
            json_err('OTP has expired. Please request a new OTP.');
        }
        if ((int)$sess['attempts'] >= 5) {
            json_err('Too many failed attempts. Please request a new OTP.');
        }

        if ($sess['otp'] !== $otp) {
            // Increment attempts and mark this attempt as failed in otp_logs
            $db->prepare("UPDATE otp_sessions SET attempts = attempts + 1 WHERE mobile = :m")
               ->execute([':m' => $mobile]);
            $db->prepare(
                "UPDATE otp_logs
                   SET verified = IF(verified = 'verified', verified, 'otp_failed')
                 WHERE mobile = :m"
            )->execute([':m' => $mobile]);
            json_err('Invalid OTP.', 401);
        }

        // Mark verified in otp_sessions
        $db->prepare("UPDATE otp_sessions SET verified = 1 WHERE mobile = :m")
           ->execute([':m' => $mobile]);

        // Update otp_logs
        $db->prepare(
            "UPDATE otp_logs
             SET verified    = 'verified',
                 last_login  = NOW(),
                 login_count = login_count + 1
             WHERE mobile = :m"
        )->execute([':m' => $mobile]);

        // Start session
        $_SESSION['mobile'] = $mobile;
        session_regenerate_id(true);

        json_ok(['mobile' => $mobile]);
    }

    // ── auto_login (from frontend OTP session) ────────────────────────────
    case 'auto_login': {
        // Only works if contact is already verified in the session
        if (empty($_SESSION['contact_verified']) || empty($_SESSION['contact_mobile'])) {
            json_err('Not verified');
        }
        if ((time() - ($_SESSION['contact_verified_at'] ?? 0)) > 86400) {
            json_err('Session expired');
        }
        $mobile = $_SESSION['contact_mobile'];

        // Set user-panel session
        $_SESSION['mobile'] = $mobile;

        // Update otp_logs
        $db = getDB();
        $db->prepare("UPDATE otp_logs SET last_login = NOW(), login_count = login_count + 1 WHERE mobile = :m")
           ->execute([':m' => $mobile]);

        json_ok(['mobile' => $mobile, 'ok' => true]);
    }

    // ── logout ───────────────────────────────────────────────────────────────
    case 'logout': {
        // If an admin is also logged in the same PHP session (admin-panel open
        // in another tab), only clear the user-specific keys. Nuking the whole
        // session would log the admin out of admin-panel too.
        if (!empty($_SESSION['admin_id'])) {
            foreach (['mobile','contact_verified','contact_mobile','contact_verified_at',
                      'admin_impersonation','admin_impersonation_expires_at'] as $k) {
                unset($_SESSION[$k]);
            }
            json_ok(['msg' => 'Logged out (admin session preserved).']);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']
            );
        }
        session_destroy();
        json_ok(['msg' => 'Logged out successfully.']);
    }

    // ── check session ────────────────────────────────────────────────────────
    case 'check': {
        // Enforce 30-min hard expiry for admin-impersonated sessions
        if (!empty($_SESSION['admin_impersonation_expires_at'])
            && time() > $_SESSION['admin_impersonation_expires_at']) {
            foreach (['mobile','contact_verified','contact_mobile','contact_verified_at',
                      'admin_impersonation','admin_impersonation_expires_at'] as $k) {
                unset($_SESSION[$k]);
            }
            json_ok(['loggedIn' => false, 'reason' => 'admin_session_expired']);
        }
        $loggedIn = !empty($_SESSION['mobile']);
        json_ok([
            'loggedIn' => $loggedIn,
            'mobile'   => $loggedIn ? $_SESSION['mobile'] : null,
        ]);
    }

    default:
        json_err('Unknown action.');
}
