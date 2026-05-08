<?php
// matrimony/api/public.php
// Public endpoints (no auth required): register, search, detail
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../admin-config.php';
$_smsFile = __DIR__ . '/../sms.php';
if (is_file($_smsFile)) require_once $_smsFile;
if (!defined('SMS_ENABLED')) define('SMS_ENABLED', false);
require_once __DIR__ . '/../sms-helpers.php';
cors();

$db = getDB();

// ── Contact-view gating ─────────────────────────────────────────────────────
// Anonymous visitors get N free contact reveals per session. After that —
// and on every subsequent session for users who have ever verified — they
// must pass OTP again. The "ever verified" flag is held in a long-lived
// cookie (the user's session itself only lives 12h).
// Defaults — overridden by the global row in `restrictions` if set by admin.
const KFM_FREE_VIEWS       = 5;
const KFM_FREE_WINDOW_SEC  = 24 * 60 * 60; // rolling reset window
const KFM_RETURNING_COOKIE = 'kfm_returning';

// Read admin-configurable per-session limits for unverified visitors. Cached
// per-request via a static so we hit the DB at most once.
function kfm_unverified_limits(): array {
    static $cached = null;
    if ($cached !== null) return $cached;
    $views = KFM_FREE_VIEWS;
    $win   = KFM_FREE_WINDOW_SEC;
    try {
        $db = getDB();
        $row = $db->query("SELECT unverified_session_views, unverified_session_hours
                             FROM restrictions WHERE type='global' LIMIT 1")->fetch();
        if ($row) {
            if ($row['unverified_session_views'] !== null && (int)$row['unverified_session_views'] > 0) {
                $views = (int)$row['unverified_session_views'];
            }
            if ($row['unverified_session_hours'] !== null && (int)$row['unverified_session_hours'] > 0) {
                $win = (int)$row['unverified_session_hours'] * 3600;
            }
        }
    } catch (Throwable $e) { /* fall back to defaults */ }
    return $cached = ['views' => $views, 'window_sec' => $win];
}

function kfm_set_returning_cookie(): void {
    // setcookie must run before any echo. All callers do so via json_ok/json_err.
    setcookie(KFM_RETURNING_COOKIE, '1', [
        'expires'  => time() + 365 * 24 * 3600,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE[KFM_RETURNING_COOKIE] = '1'; // make it visible in this same request
}

function kfm_is_returning(): bool {
    return !empty($_COOKIE[KFM_RETURNING_COOKIE]);
}

// Roll the free-view window if it has expired. Clears the recorded cpid list
// and resets the window-start timestamp so the visitor gets a fresh batch of
// the configured per-session views. Window length comes from the admin
// restriction; falls back to the KFM_FREE_WINDOW_SEC default.
function kfm_anon_roll_window(): void {
    $start = (int)($_SESSION['anon_window_start'] ?? 0);
    $cfg   = kfm_unverified_limits();
    if ($start > 0 && (time() - $start) >= $cfg['window_sec']) {
        $_SESSION['anon_view_cpids']   = [];
        $_SESSION['anon_window_start'] = 0;
    }
}

function kfm_anon_views_used(): int {
    kfm_anon_roll_window();
    $arr = $_SESSION['anon_view_cpids'] ?? [];
    return is_array($arr) ? count($arr) : 0;
}

function kfm_anon_record(string $cpId): void {
    kfm_anon_roll_window();
    $arr = $_SESSION['anon_view_cpids'] ?? [];
    if (!is_array($arr)) $arr = [];
    if ($cpId !== '' && !in_array($cpId, $arr, true)) $arr[] = $cpId;
    $_SESSION['anon_view_cpids'] = $arr;
    // Stamp the window start the first time a view is recorded inside it.
    if (empty($_SESSION['anon_window_start'])) {
        $_SESSION['anon_window_start'] = time();
    }
}

// True when a contact_view request must be blocked behind OTP.
function kfm_gate_required(string $targetCpId): bool {
    // Already OTP-verified this session OR logged in via user-panel → no gate.
    if (!empty($_SESSION['contact_verified']) || !empty($_SESSION['mobile'])) return false;
    // Returning device but no current-session verification → must OTP.
    if (kfm_is_returning()) return true;
    // Anonymous: re-revealing the same profile is free; new profile only if under quota.
    $arr = $_SESSION['anon_view_cpids'] ?? [];
    if (is_array($arr) && in_array($targetCpId, $arr, true)) return false;
    $cfg = kfm_unverified_limits();
    return kfm_anon_views_used() >= $cfg['views'];
}

// Snapshot for clients to drive UI without a separate request.
function kfm_gate_snapshot(): array {
    $verified = !empty($_SESSION['contact_verified']) || !empty($_SESSION['mobile']);
    $cfg      = kfm_unverified_limits();
    $used     = kfm_anon_views_used();
    return [
        'returning'         => kfm_is_returning(),
        'anon_views_used'   => $used,
        'anon_views_limit'  => $cfg['views'],
        'anon_window_sec'   => $cfg['window_sec'],
        'anon_window_start' => (int)($_SESSION['anon_window_start'] ?? 0),
        // Will the *next* contact_view on a brand-new profile be gated?
        'gate_required'     => !$verified && (kfm_is_returning() || $used >= $cfg['views']),


        ];
}

// ── GET: Check mobile duplicate ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['checkMobile'])) {
    $m = preg_replace('/\D/', '', $_GET['checkMobile']);
    if (strlen($m) === 10) {
        $chk = $db->prepare("SELECT cp_id FROM profiles WHERE mobile = :m LIMIT 1");
        $chk->execute([':m' => $m]);
        json_ok(['exists' => !!$chk->fetch()]);
    }
    json_ok(['exists' => false]);
}

// ── GET: Image-pixel tracking (MobileGate web_in / web_out) ─────────────────
// MobileGate sends these via `new Image().src = ...`. Image requests pass
// through WAFs that block POST/sendBeacon. Always returns a 1×1 GIF.
if ($_SERVER['REQUEST_METHOD'] === 'GET'
    && isset($_GET['action'])
    && in_array($_GET['action'], ['contact_mobile_typed', 'contact_skip_gate'], true)) {
    $action = $_GET['action'];
    $mobile = preg_replace('/\D/', '', $_GET['mobile'] ?? '');
    @file_put_contents(__DIR__ . '/../logs/track-debug.log',
        date('c') . " GET-pixel action=$action ip=" . client_ip() . " mobile='$mobile' len=" . strlen($mobile) . "\n",
        FILE_APPEND);

    if (strlen($mobile) >= 1 && strlen($mobile) <= 15) {
        try {
            $profile = $db->prepare("SELECT cp_id, name FROM profiles WHERE mobile = :m LIMIT 1");
            $profile->execute([':m' => $mobile]);
            $prof = $profile->fetch();

            // Collapse shorter prefix rows still in 'in-progress' state.
            $db->prepare(
                "DELETE FROM otp_logs
                  WHERE verified IN ('web_in', 'typing')
                    AND mobile != :m1
                    AND :m2 LIKE CONCAT(mobile, '%')"
            )->execute([':m1' => $mobile, ':m2' => $mobile]);

            if ($action === 'contact_mobile_typed') {
                $db->prepare(
                    "INSERT INTO otp_logs (mobile, cp_id, name, otp_requested_at, verified, login_count, banned)
                     VALUES (:m, :c, :n, NOW(), 'web_in', 0, 0)
                     ON DUPLICATE KEY UPDATE
                       cp_id            = COALESCE(VALUES(cp_id), cp_id),
                       name             = COALESCE(VALUES(name), name),
                       otp_requested_at = NOW(),
                       verified         = IF(verified IN ('verified','otp_request','otp_failed','web_out'), verified, 'web_in')"
                )->execute([':m' => $mobile, ':c' => $prof['cp_id'] ?? null, ':n' => $prof['name'] ?? null]);
            } else { // contact_skip_gate
                $db->prepare(
                    "INSERT INTO otp_logs (mobile, cp_id, name, otp_requested_at, verified, login_count, banned)
                     VALUES (:m, :c, :n, NOW(), 'web_out', 0, 0)
                     ON DUPLICATE KEY UPDATE
                       cp_id            = COALESCE(VALUES(cp_id), cp_id),
                       name             = COALESCE(VALUES(name), name),
                       otp_requested_at = NOW(),
                       verified         = IF(verified IN ('verified','otp_request','otp_failed'), verified, 'web_out')"
                )->execute([':m' => $mobile, ':c' => $prof['cp_id'] ?? null, ':n' => $prof['name'] ?? null]);
            }
        } catch (\Throwable $e) {
            @file_put_contents(__DIR__ . '/../logs/track-debug.log',
                date('c') . " GET-pixel ERROR: " . $e->getMessage() . "\n",
                FILE_APPEND);
        }
    }

    // Always respond with a 1×1 transparent GIF.
    header('Content-Type: image/gif');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

// ── POST ────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    if (strpos($contentType, 'multipart/form-data') !== false) {
        $input = $_POST;
    } elseif (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    } else {
        // Fallback: try JSON-decoding the raw body anyway. sendBeacon defaults
        // to text/plain (the only "simple" content-type that doesn't trigger
        // CORS preflight or WAF JSON-body inspection), so the tracking pings
        // arrive as text/plain but contain a JSON payload.
        $raw = file_get_contents('php://input');
        $input = $_POST;
        if ($raw && ($raw[0] === '{' || $raw[0] === '[')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) $input = $decoded;
        }
    }

    // ── Upload payment proof ───────────────────────────────────────────
    if (($input['action'] ?? $_POST['action'] ?? '') === 'upload_proof') {
        $orderId = (int)($_POST['order_id'] ?? $input['order_id'] ?? 0);
        $mobile = trim($_POST['mobile'] ?? $input['mobile'] ?? '');
        $txnRef = trim($_POST['txn_ref'] ?? $input['txn_ref'] ?? '');

        if (!$orderId || !$mobile) json_err('Order ID and mobile required');

        // Verify order belongs to this mobile
        $chk = $db->prepare("SELECT id FROM user_orders WHERE id = :id AND mobile = :m AND status = 'pending'");
        $chk->execute([':id' => $orderId, ':m' => $mobile]);
        if (!$chk->fetch()) json_err('Order not found or already processed');

        // Handle file upload
        $proofFile = '';
        if (!empty($_FILES['proof']) && $_FILES['proof']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','pdf','webp'])) json_err('Invalid file type');
            $filename = 'proof_' . $orderId . '_' . time() . '.' . $ext;
            $dest = __DIR__ . '/uploads/' . $filename;
            move_uploaded_file($_FILES['proof']['tmp_name'], $dest);
            $proofFile = $filename;
        } else {
            json_err('Please upload a proof image');
        }

        $db->prepare("UPDATE user_orders SET payment_proof = :p, txn_ref = CASE WHEN :t != '' THEN :t2 ELSE txn_ref END WHERE id = :id")
           ->execute([':p' => $proofFile, ':t' => $txnRef, ':t2' => $txnRef, ':id' => $orderId]);

        // Archive log
        $db->prepare("INSERT INTO order_archive (order_id, mobile, action, action_by, admin_note, created_at)
            VALUES (:oid, :m, 'Proof Uploaded', 'User', :n, NOW())")
            ->execute([':oid' => $orderId, ':m' => $mobile, ':n' => 'File: ' . $proofFile]);

        json_ok(['message' => 'Proof uploaded']);
    }                                                                                           

    // ── Contact OTP (must be before registration) ───────────────────────
    $act = trim($input['action'] ?? '');

    // Per-action rate limits. Tuned to allow legitimate bursts but stop abuse.
    // Each violation is logged; 10+ in 5 min auto-bans the IP for 1 hour.
    $RATE_LIMITS = [
        'contact_otp_send'     => [3,   60],   // 3 OTPs/min (SMS costs money!)
        'contact_otp_verify'   => [6,   60],   // 6 verify attempts/min
        'contact_mobile_typed' => [120, 60],   // 120 log hits/min per IP — purely tracking, no SMS cost
        'contact_check'        => [30,  60],
        'report_profile'       => [5,   60],
        'track_view'           => [60,  60],
        'tag_profile'          => [20,  60],
        'remove_tag'           => [20,  60],
        'place_order'          => [3,  300],   // 3 orders per 5 min
    ];
    if (isset($RATE_LIMITS[$act])) {
        [$max, $window] = $RATE_LIMITS[$act];
        // OTP endpoints keyed on mobile+IP so one bad IP can't lock out a whole mobile,
        // and one mobile can't be farmed across multiple IPs.
        $subject = in_array($act, ['contact_otp_send', 'contact_otp_verify'], true)
            ? (preg_replace('/\D/', '', $input['mobile'] ?? '') . ':' . client_ip())
            : client_ip();
        rate_limit($act, $max, $window, $subject);
    }

    // Honeypot + bot signature check on registration/OTP (the valuable mutation actions).
    $sensitive = ['contact_otp_send', 'contact_otp_verify', 'register', 'place_order'];
    if (in_array($act, $sensitive, true)) {
        check_honeypot($input);
        block_bots_on_sensitive();
    }

    if ($act === 'contact_otp_send') {
        $mobile = preg_replace('/\D/', '', $input['mobile'] ?? '');
        if (strlen($mobile) !== 10) json_err('Enter valid 10-digit mobile number');

        // Check direct login — auto-verify without OTP
        $dlCheck = $db->prepare("SELECT id FROM direct_login WHERE mobile = :m AND status = 'active' LIMIT 1");
        $dlCheck->execute([':m' => $mobile]);
        if ($dlCheck->fetch()) {
            secureSession();
            $_SESSION['contact_verified'] = true;
            $_SESSION['contact_mobile'] = $mobile;
            $_SESSION['contact_verified_at'] = time();
            $db->prepare("UPDATE direct_login SET last_used = NOW(), use_count = use_count + 1 WHERE mobile = :m")
               ->execute([':m' => $mobile]);
            json_ok(['message' => 'Auto-verified', 'auto_verified' => true]);
        }

        $otp = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $exp = date('Y-m-d H:i:s', time() + 120);

        $stmt = $db->prepare("INSERT INTO otp_sessions (mobile, otp, attempts, expires_at, verified)
            VALUES (:m, :o, 0, :e, 0)
            ON DUPLICATE KEY UPDATE otp = VALUES(otp), attempts = 0, expires_at = VALUES(expires_at), verified = 0");
        $stmt->execute([':m' => $mobile, ':o' => $otp, ':e' => $exp]);

        $profile = $db->prepare("SELECT cp_id, name FROM profiles WHERE mobile = :m LIMIT 1");
        $profile->execute([':m' => $mobile]);
        $prof = $profile->fetch();
        // Promote to 'otp_request' (user is now on the OTP entry page).
        // Don't downgrade an already-'verified' row.
        $db->prepare(
            "INSERT INTO otp_logs (mobile, cp_id, name, otp_requested_at, verified, login_count, banned)
             VALUES (:m, :c, :n, NOW(), 'otp_request', 0, 0)
             ON DUPLICATE KEY UPDATE
               cp_id            = COALESCE(VALUES(cp_id), cp_id),
               name             = COALESCE(VALUES(name), name),
               otp_requested_at = NOW(),
               verified         = IF(verified = 'verified', verified, 'otp_request')"
        )->execute([':m' => $mobile, ':c' => $prof['cp_id'] ?? null, ':n' => $prof['name'] ?? null]);

        $sent = sendOTP($mobile, $otp);

        $resp = ['message' => 'OTP sent to ' . substr($mobile, 0, 3) . '****' . substr($mobile, -3)];
        if (!$sent) $resp['otp'] = $otp;
        json_ok($resp);
    }

    if ($act === 'contact_otp_verify') {
        $mobile = preg_replace('/\D/', '', $input['mobile'] ?? '');
        $otp    = trim($input['otp'] ?? '');
        if (!$mobile || !$otp) json_err('Mobile and OTP required');

        $stmt = $db->prepare("SELECT * FROM otp_sessions WHERE mobile = :m AND verified = 0 LIMIT 1");
        $stmt->execute([':m' => $mobile]);
        $row = $stmt->fetch();

        if (!$row) json_err('No OTP found. Please request again.');
        if (strtotime($row['expires_at']) < time()) json_err('OTP expired. Please request again.');
        if ((int)$row['attempts'] >= 5) json_err('Too many attempts. Please request again.');

        $db->prepare("UPDATE otp_sessions SET attempts = attempts + 1 WHERE mobile = :m")->execute([':m' => $mobile]);
        if ($row['otp'] !== $otp) {
            // Mark this attempt as failed in otp_logs so admins can see wrong-OTP entries.
            $db->prepare(
                "UPDATE otp_logs
                   SET verified = IF(verified = 'verified', verified, 'otp_failed')
                 WHERE mobile = :m"
            )->execute([':m' => $mobile]);
            json_err('Invalid OTP');
        }

        $db->prepare("UPDATE otp_sessions SET verified = 1 WHERE mobile = :m")->execute([':m' => $mobile]);

        $db->prepare(
            "UPDATE otp_logs
               SET verified    = 'verified',
                   last_login  = NOW(),
                   login_count = login_count + 1
             WHERE mobile = :m"
        )->execute([':m' => $mobile]);

        secureSession();
        $_SESSION['contact_verified'] = true;
        $_SESSION['contact_mobile'] = $mobile;
        $_SESSION['contact_verified_at'] = time();

        // Mark this device as "ever verified" so future sessions must OTP again
        // (the session cookie itself only lives 12h; this one is 1 year).
        kfm_set_returning_cookie();

        json_ok(['message' => 'OTP verified successfully', 'verified' => true]);
    }

    if ($act === 'contact_mobile_typed') {
        // Track the (partial) mobile number entered into the gate input,
        // collapsing shorter prefixes into the longest entered value.
        $mobile = preg_replace('/\D/', '', $input['mobile'] ?? '');
        @file_put_contents(__DIR__ . '/../logs/track-debug.log',
            date('c') . " typed ip=" . client_ip() . " ct=" . ($_SERVER['CONTENT_TYPE'] ?? '?') . " mobile='$mobile' len=" . strlen($mobile) . "\n",
            FILE_APPEND);
        if (strlen($mobile) < 1 || strlen($mobile) > 15) json_ok(['skipped' => true]);

        $profile = $db->prepare("SELECT cp_id, name FROM profiles WHERE mobile = :m LIMIT 1");
        $profile->execute([':m' => $mobile]);
        $prof = $profile->fetch();

        // Drop any shorter prefix rows still in an 'in-progress' (web_in/typing)
        // state — e.g. when user typed 99 → 994 → 994455, only keep 994455.
        // Never touches rows that have already progressed past entry.
        $db->prepare(
            "DELETE FROM otp_logs
              WHERE verified IN ('web_in', 'typing')
                AND mobile != :m1
                AND :m2 LIKE CONCAT(mobile, '%')"
        )->execute([':m1' => $mobile, ':m2' => $mobile]);

        // Upsert as 'web_in' (user is on the page, actively entering a number).
        // Don't downgrade rows that have already progressed (otp_request, verified, etc.).
        $db->prepare(
            "INSERT INTO otp_logs (mobile, cp_id, name, otp_requested_at, verified, login_count, banned)
             VALUES (:m, :c, :n, NOW(), 'web_in', 0, 0)
             ON DUPLICATE KEY UPDATE
               cp_id            = COALESCE(VALUES(cp_id), cp_id),
               name             = COALESCE(VALUES(name), name),
               otp_requested_at = NOW(),
               verified         = IF(verified IN ('verified','otp_request','otp_failed','web_out'), verified, 'web_in')"
        )->execute([':m' => $mobile, ':c' => $prof['cp_id'] ?? null, ':n' => $prof['name'] ?? null]);

        json_ok(['tracked' => true]);
    }

    if ($act === 'contact_skip_gate') {
        // User left the mobile-verification gate after typing 1–10 digits but
        // never requested an OTP. Record as 'web_out' in otp_logs so admins can
        // see drop-offs. Never overwrites a row that has already progressed
        // past the mobile-entry stage (otp_request / otp_failed / verified).
        $mobile = preg_replace('/\D/', '', $input['mobile'] ?? '');
        @file_put_contents(__DIR__ . '/../logs/track-debug.log',
            date('c') . " skip  ip=" . client_ip() . " ct=" . ($_SERVER['CONTENT_TYPE'] ?? '?') . " mobile='$mobile' len=" . strlen($mobile) . "\n",
            FILE_APPEND);

        if (strlen($mobile) >= 1 && strlen($mobile) <= 15) {
            $profile = $db->prepare("SELECT cp_id, name FROM profiles WHERE mobile = :m LIMIT 1");
            $profile->execute([':m' => $mobile]);
            $prof = $profile->fetch();

            // Collapse earlier in-progress prefixes into this final web_out value.
            $db->prepare(
                "DELETE FROM otp_logs
                  WHERE verified IN ('web_in', 'typing')
                    AND mobile != :m1
                    AND :m2 LIKE CONCAT(mobile, '%')"
            )->execute([':m1' => $mobile, ':m2' => $mobile]);

            $db->prepare(
                "INSERT INTO otp_logs (mobile, cp_id, name, otp_requested_at, verified, login_count, banned)
                 VALUES (:m, :c, :n, NOW(), 'web_out', 0, 0)
                 ON DUPLICATE KEY UPDATE
                   cp_id            = COALESCE(VALUES(cp_id), cp_id),
                   name             = COALESCE(VALUES(name), name),
                   otp_requested_at = NOW(),
                   verified         = IF(verified IN ('verified','otp_request','otp_failed'), verified, 'web_out')"
            )->execute([':m' => $mobile, ':c' => $prof['cp_id'] ?? null, ':n' => $prof['name'] ?? null]);
        }

        secureSession();
        $_SESSION['contact_skipped']    = true;
        $_SESSION['contact_skipped_at'] = time();

        json_ok(['skipped' => true]);
    }

    if ($act === 'contact_check') {
        secureSession();
        // A user logged in via user-panel (auth.php sets $_SESSION['mobile']) is already
        // OTP-authenticated — treat them as verified.
        $loggedIn = !empty($_SESSION['mobile']);
        $verified = $loggedIn
            || (!empty($_SESSION['contact_verified']) && (time() - ($_SESSION['contact_verified_at'] ?? 0)) < 86400);
        $skipped  = !empty($_SESSION['contact_skipped']) && (time() - ($_SESSION['contact_skipped_at'] ?? 0)) < 86400;
        $mobile = $verified
            ? ($_SESSION['contact_mobile'] ?? ($_SESSION['mobile'] ?? ''))
            : '';
        $name = ''; $cpId = '';
        if ($verified && $mobile) {
            $pStmt = $db->prepare("SELECT name, cp_id FROM profiles WHERE mobile = :m LIMIT 1");
            $pStmt->execute([':m' => $mobile]);
            $pRow = $pStmt->fetch();
            if ($pRow) { $name = $pRow['name']; $cpId = $pRow['cp_id']; }
        }
        // Promote any verified visitor to "returning" so future sessions re-prompt.
        // Covers the user-panel login path too, which never went through SPA OTP.
        if ($verified && !kfm_is_returning()) kfm_set_returning_cookie();

        json_ok([
            'verified' => $verified,
            'skipped'  => $skipped,
            'mobile'   => $mobile,
            'name'     => $name,
            'cp_id'    => $cpId,
        ] + kfm_gate_snapshot());
    }

    if ($act === 'contact_otp_get') {
        // Internal: return current OTP for auto-login (only if contact already verified)
        secureSession();
        if (empty($_SESSION['contact_verified'])) json_err('Not verified');
        $mobile = preg_replace('/\D/', '', $input['mobile'] ?? '');
        $stmt = $db->prepare("SELECT otp FROM otp_sessions WHERE mobile = :m ORDER BY id DESC LIMIT 1");
        $stmt->execute([':m' => $mobile]);
        $row = $stmt->fetch();
        if ($row) json_ok(['otp' => $row['otp']]);
        json_err('No OTP found');
    }

    if ($act === 'report_profile') {
        $cpId = trim($input['cp_id'] ?? '');
        $reason = trim($input['reason'] ?? '');
        $reporterMobile = trim($input['reporter_mobile'] ?? '');
        if (!$cpId || !$reason) json_err('Profile ID and reason required');

        $db->prepare("INSERT INTO profile_reports (cp_id, reason, reporter_mobile, reported_at)
            VALUES (:cp, :r, :m, NOW())")
            ->execute([':cp' => $cpId, ':r' => $reason, ':m' => $reporterMobile]);

        json_ok(['message' => 'Report submitted']);
    }

    if ($act === 'my_reports') {
        secureSession();
        $mobile = trim($input['mobile'] ?? '');
        if (!$mobile) json_err('Mobile required');
        $db = getDB();
        $stmt = $db->prepare("SELECT r.*, p.name as profile_name, p.mobile as profile_mobile
            FROM profile_reports r LEFT JOIN profiles p ON r.cp_id = p.cp_id
            WHERE r.reporter_mobile = :m ORDER BY r.reported_at DESC");
        $stmt->execute([':m' => $mobile]);
        json_ok(['reports' => $stmt->fetchAll()]);
    }

    if ($act === 'track_view') {
        secureSession();
        $viewerMobile = trim($input['viewer_mobile'] ?? $_SESSION['contact_mobile'] ?? $_SESSION['mobile'] ?? '');
        $targetCpId = trim($input['target_cp_id'] ?? '');
        $type = trim($input['type'] ?? 'profile_view'); // profile_view or contact_view
        if (!$targetCpId) json_ok(['tracked' => false]);

        // Chennai Profile uses a points gate instead of OTP gate — skip KFM gate entirely.
        $isChennai   = defined('SITE_ID') && SITE_ID === 'chennaip';
        $gateBlocked = (!$isChennai && $type === 'contact_view' && kfm_gate_required($targetCpId));

        // Get viewer profile info
        $viewerProfile = null;
        if ($viewerMobile) {
            $vp = $db->prepare("SELECT cp_id, name, plan FROM profiles WHERE mobile = :m LIMIT 1");
            $vp->execute([':m' => $viewerMobile]);
            $viewerProfile = $vp->fetch();
        }

        $timeSpent = (int)($input['time_spent'] ?? 0);
        $scrollDepth = (int)($input['scroll_depth'] ?? 0);

        // If time_spent > 0, try to UPDATE existing row first (update duration on page leave)
        if ($timeSpent > 0 && $viewerMobile && !$gateBlocked) {
            $upd = $db->prepare("UPDATE usage_activity SET time_spent = :ts, scroll_depth = :sd
                WHERE mobile = :m AND target_cp_id = :tcp AND activity_type = :t
                AND datetime >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY id DESC LIMIT 1");
            $upd->execute([':ts' => $timeSpent, ':sd' => $scrollDepth, ':m' => $viewerMobile, ':tcp' => $targetCpId, ':t' => $type]);
            if ($upd->rowCount() > 0) { json_ok(['tracked' => true, 'updated' => true]); }
        }

        $db->prepare("INSERT INTO usage_activity (mobile, cp_id, name, plan, activity_type, target_cp_id, datetime, time_spent, scroll_depth)
            VALUES (:m, :cp, :n, :pl, :t, :tcp, NOW(), :ts, :sd)")
            ->execute([
                ':m' => $viewerMobile ?: 'anonymous',
                ':cp' => $viewerProfile['cp_id'] ?? '',
                ':n' => $viewerProfile['name'] ?? '',
                ':pl' => $viewerProfile['plan'] ?? 'free',
                ':t' => $type,
                ':tcp' => $targetCpId,
                ':ts' => $timeSpent,
                ':sd' => $scrollDepth,
            ]);

        // Now apply the gate response — the row is already logged.
        if ($gateBlocked) {
            http_response_code(403);
            echo json_encode([
                'ok'            => false,
                'error'         => 'OTP verification required',
                'gate_required' => true,
                'gate_reason'   => kfm_is_returning() ? 'returning_user' : 'free_limit_reached',
            ] + kfm_gate_snapshot());
            exit;
        }

        // For a contact reveal, return the target's mobile.
        // - Verified sessions (SPA OTP or user-panel login): always.
        // - Anonymous within free quota: also yes (record toward quota).
        if ($type === 'contact_view') {
            $verified = !empty($_SESSION['contact_mobile']) || !empty($_SESSION['mobile']);
            // Don't count toward the anon quota on Chennai Profile — points gate handles access.
            if (!$verified && !$isChennai) kfm_anon_record($targetCpId);

            // ── Points gate (Chennai Profile only) ────────────────────────
            if ($isChennai) {
                require_once __DIR__ . '/points.php';

                // Fetch contact info FIRST — only charge if there's something to reveal.
                $tgt = $db->prepare("SELECT mobile, alt_mobile, email, contact_person FROM profiles WHERE cp_id = :c AND status = 'Approved' LIMIT 1");
                $tgt->execute([':c' => $targetCpId]);
                $row = $tgt->fetch() ?: [];
                $hasContact = !empty($row['mobile']) || !empty($row['alt_mobile']) || !empty($row['email']);

                $viewerMob = $_SESSION['mobile'] ?? $_SESSION['contact_mobile'] ?? '';
                if (!$viewerMob) {
                    http_response_code(402);
                    echo json_encode(['ok' => false, 'need_points' => true, 'balance' => 0, 'required' => POINTS_PER_CONTACT, 'error' => 'Login and points required to view contact.']);
                    exit;
                }
                if ($hasContact) {
                    $bal = pts_get_balance($db, $viewerMob);
                    if ($bal < POINTS_PER_CONTACT) {
                        http_response_code(402);
                        echo json_encode(['ok' => false, 'need_points' => true, 'balance' => $bal, 'required' => POINTS_PER_CONTACT, 'error' => 'Insufficient points to view contact.']);
                        exit;
                    }
                    $alreadyPaid = $db->prepare("SELECT 1 FROM point_transactions WHERE mobile = :m AND ref_id = :r AND type = 'deduct' LIMIT 1");
                    $alreadyPaid->execute([':m' => $viewerMob, ':r' => $targetCpId]);
                    if (!$alreadyPaid->fetchColumn()) {
                        $result = pts_deduct($db, $viewerMob, POINTS_PER_CONTACT, 'Contact view: ' . $targetCpId, $targetCpId);
                        if ($result === false) {
                            http_response_code(402);
                            echo json_encode(['ok' => false, 'need_points' => true, 'balance' => pts_get_balance($db, $viewerMob), 'required' => POINTS_PER_CONTACT, 'error' => 'Insufficient points to view contact.']);
                            exit;
                        }
                    }
                }
                json_ok(array_merge([
                    'tracked'        => true,
                    'mobile'         => $row['mobile'] ?? '',
                    'alt_mobile'     => $row['alt_mobile'] ?? '',
                    'email'          => $row['email'] ?? '',
                    'contact_person' => $row['contact_person'] ?? '',
                    'points_balance' => pts_get_balance($db, $viewerMob),
                ], kfm_gate_snapshot()));
            }
            // ─────────────────────────────────────────────────────────────

            $tgt = $db->prepare("SELECT mobile, alt_mobile, email, contact_person FROM profiles WHERE cp_id = :c AND status = 'Approved' LIMIT 1");
            $tgt->execute([':c' => $targetCpId]);
            $row = $tgt->fetch();
            json_ok(array_merge([
                'tracked'        => true,
                'mobile'         => $row['mobile'] ?? '',
                'alt_mobile'     => $row['alt_mobile'] ?? '',
                'email'          => $row['email'] ?? '',
                'contact_person' => $row['contact_person'] ?? '',
            ], kfm_gate_snapshot()));
        }
        json_ok(['tracked' => true]);
    }

    if ($act === 'user_activity') {
        $mobile = trim($input['mobile'] ?? '');
        if (!$mobile) json_err('Mobile required');
        $prof = $db->prepare("SELECT cp_id FROM profiles WHERE mobile = :m LIMIT 1");
        $prof->execute([':m' => $mobile]);
        $p = $prof->fetch();
        $cpId = $p['cp_id'] ?? '';

        $pv = $db->prepare("SELECT ua.target_cp_id, ua.datetime, ua.time_spent, p.name as target_name
            FROM usage_activity ua LEFT JOIN profiles p ON p.cp_id = ua.target_cp_id
            WHERE (ua.mobile = :m OR ua.cp_id = :c) AND ua.activity_type = 'profile_view'
            ORDER BY ua.datetime DESC LIMIT 200");
        $pv->execute([':m' => $mobile, ':c' => $cpId]);

        $cv = $db->prepare("SELECT ua.target_cp_id, ua.datetime, p.name as target_name
            FROM usage_activity ua LEFT JOIN profiles p ON p.cp_id = ua.target_cp_id
            WHERE (ua.mobile = :m OR ua.cp_id = :c) AND ua.activity_type = 'contact_view'
            ORDER BY ua.datetime DESC LIMIT 200");
        $cv->execute([':m' => $mobile, ':c' => $cpId]);

        $vb = $db->prepare("SELECT ua.mobile as viewer_mobile, ua.name as viewer_name, ua.plan as viewer_plan, ua.datetime, ua.time_spent,
            p.name as viewer_profile_name, p.plan as viewer_profile_plan
            FROM usage_activity ua LEFT JOIN profiles p ON p.cp_id = ua.cp_id
            WHERE ua.target_cp_id = :c AND ua.activity_type = 'profile_view'
            ORDER BY ua.datetime DESC LIMIT 200");
        $vb->execute([':c' => $cpId]);

        json_ok(['profileViews' => $pv->fetchAll(), 'contactViews' => $cv->fetchAll(), 'viewedBy' => $vb->fetchAll()]);
    }

    if ($act === 'revoke_report') {
        secureSession();
        $id = (int)($input['id'] ?? 0);
        $mobile = trim($input['mobile'] ?? '');
        if (!$id || !$mobile) json_err('Invalid request');
        $db = getDB();
        // Only allow revoking own reports that are still pending
        $stmt = $db->prepare("SELECT * FROM profile_reports WHERE id = :id AND reporter_mobile = :m AND status = 'pending'");
        $stmt->execute([':id' => $id, ':m' => $mobile]);
        if (!$stmt->fetch()) json_err('Report not found or already resolved');
        $db->prepare("UPDATE profile_reports SET status = 'revoked', admin_note = 'Revoked by reporter', resolved_at = NOW() WHERE id = :id")
           ->execute([':id' => $id]);
        json_ok(['message' => 'Report revoked']);
    }

    if ($act === 'user_limits') {
        $mobile = trim($input['mobile'] ?? '');
        if (!$mobile) json_err('Mobile required');
        $db = getDB();

        // Get individual override first, then global
        $ind = $db->prepare("SELECT per_day, per_month, total FROM restrictions WHERE type='individual' AND mobile = :m LIMIT 1");
        $ind->execute([':m' => $mobile]);
        $indRow = $ind->fetch();

        $glob = $db->prepare("SELECT per_day, per_month, total FROM restrictions WHERE type='global' LIMIT 1");
        $glob->execute();
        $globRow = $glob->fetch();

        $limits = $indRow ?: $globRow ?: ['per_day' => 0, 'per_month' => 0, 'total' => 0];

        // Count usage
        $today = date('Y-m-d');
        $monthStart = date('Y-m-01');

        $usedToday = $db->prepare("SELECT COUNT(*) FROM usage_activity WHERE mobile = :m AND activity_type = 'contact_view' AND datetime >= :d");
        $usedToday->execute([':m' => $mobile, ':d' => $today]);
        $dayUsed = (int)$usedToday->fetchColumn();

        $usedMonth = $db->prepare("SELECT COUNT(*) FROM usage_activity WHERE mobile = :m AND activity_type = 'contact_view' AND datetime >= :d");
        $usedMonth->execute([':m' => $mobile, ':d' => $monthStart]);
        $monthUsed = (int)$usedMonth->fetchColumn();

        $usedTotal = $db->prepare("SELECT COUNT(*) FROM usage_activity WHERE mobile = :m AND activity_type = 'contact_view'");
        $usedTotal->execute([':m' => $mobile]);
        $totalUsed = (int)$usedTotal->fetchColumn();

        json_ok([
            'limits' => [
                'day' => (int)($limits['per_day'] ?? 0),
                'month' => (int)($limits['per_month'] ?? 0),
                'total' => (int)($limits['total'] ?? 0),
            ],
            'used' => [
                'day' => $dayUsed,
                'month' => $monthUsed,
                'total' => $totalUsed,
            ],
            'isOverride' => !!$indRow,
        ]);
    }

    if ($act === 'suggestions') {
        $mobile = trim($input['mobile'] ?? '');
        if (!$mobile) json_err('Mobile required');

        // Allow caller to override the target gender (e.g. Home tab picker: selecting "Male"
        // treats the viewer as female and surfaces male profiles, and vice versa).
        $targetGender = trim($input['target_gender'] ?? '');
        if (!in_array($targetGender, ['Male', 'Female'], true)) $targetGender = '';

        // Get user's profile
        $prof = $db->prepare("SELECT * FROM profiles WHERE mobile = :m LIMIT 1");
        $prof->execute([':m' => $mobile]);
        $me = $prof->fetch();
        if (!$me) {
            // Logged in but no matching profile row — fall back to a browse of all approved profiles
            $allSql = "SELECT cp_id, name, age, gender, caste, star, raasi, photo1, qualification, height, marital
                       FROM profiles WHERE status = 'Approved'";
            $allParams = [];
            if ($targetGender) { $allSql .= " AND gender = ?"; $allParams[] = $targetGender; }
            $allSql .= " ORDER BY id DESC LIMIT 30";
            $allStmt = $db->prepare($allSql);
            $allStmt->execute($allParams);
            json_ok(['interest' => [], 'preference' => [], 'notViewed' => [], 'allProfiles' => $allStmt->fetchAll()]);
        }

        // User has a profile — their gender is confirmed; opposite gender is fixed.
        // target_gender is ignored in this branch (only used as the fallback for users
        // without a profile row, handled above).
        $myGender = $me['gender'];
        $oppGender = $myGender === 'Male' ? 'Female' : 'Male';
        $myCpId = $me['cp_id'];

        // Get profiles I've already viewed
        $viewedStmt = $db->prepare("SELECT DISTINCT target_cp_id FROM usage_activity WHERE (mobile = :m OR cp_id = :c) AND activity_type = 'profile_view'");
        $viewedStmt->execute([':m' => $mobile, ':c' => $myCpId]);
        $viewedIds = $viewedStmt->fetchAll(PDO::FETCH_COLUMN);
        $viewedIds[] = $myCpId; // exclude self

        $excludePlaceholder = implode(',', array_fill(0, count($viewedIds), '?'));

        // 1. Interest pattern — profiles similar to ones I've viewed (same caste/star)
        $interestCastes = $db->prepare("SELECT DISTINCT p.caste FROM usage_activity ua JOIN profiles p ON p.cp_id = ua.target_cp_id
            WHERE (ua.mobile = :m OR ua.cp_id = :c) AND ua.activity_type = 'profile_view' AND p.caste IS NOT NULL");
        $interestCastes->execute([':m' => $mobile, ':c' => $myCpId]);
        $castes = $interestCastes->fetchAll(PDO::FETCH_COLUMN);

        $interest = [];
        if ($castes) {
            $castePlaceholders = implode(',', array_fill(0, count($castes), '?'));
            $sql = "SELECT cp_id, name, age, gender, caste, star, raasi, photo1, qualification, height, marital
                    FROM profiles WHERE gender = ? AND caste IN ($castePlaceholders) AND cp_id NOT IN ($excludePlaceholder)
                    AND status = 'Approved' ORDER BY RAND() LIMIT 10";
            $params = array_merge([$oppGender], $castes, $viewedIds);
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $interest = $stmt->fetchAll();
        }

        // 2. Partner preference match
        $prefWhere = ["gender = ?", "status = 'Approved'", "cp_id NOT IN ($excludePlaceholder)"];
        $prefParams = [$oppGender];
        if ($me['partner_caste'] && $me['partner_caste'] !== 'Any') { $prefWhere[] = "caste = ?"; $prefParams[] = $me['partner_caste']; }
        if ($me['partner_marital_status'] && $me['partner_marital_status'] !== 'Any') { $prefWhere[] = "marital = ?"; $prefParams[] = $me['partner_marital_status']; }
        if ($me['partner_age_from']) { $prefWhere[] = "age >= ?"; $prefParams[] = (int)$me['partner_age_from']; }
        if ($me['partner_age_to']) { $prefWhere[] = "age <= ?"; $prefParams[] = (int)$me['partner_age_to']; }
        $prefParams = array_merge($prefParams, $viewedIds);

        $prefSql = "SELECT cp_id, name, age, gender, caste, star, raasi, photo1, qualification, height, marital
                    FROM profiles WHERE " . implode(' AND ', $prefWhere) . " ORDER BY RAND() LIMIT 10";
        $prefStmt = $db->prepare($prefSql);
        $prefStmt->execute($prefParams);
        $preference = $prefStmt->fetchAll();

        // 3. Not viewed — recent profiles
        $nvSql = "SELECT cp_id, name, age, gender, caste, star, raasi, photo1, qualification, height, marital
                  FROM profiles WHERE gender = ? AND cp_id NOT IN ($excludePlaceholder) AND status = 'Approved'
                  ORDER BY id DESC LIMIT 10";
        $nvParams = array_merge([$oppGender], $viewedIds);
        $nvStmt = $db->prepare($nvSql);
        $nvStmt->execute($nvParams);
        $notViewed = $nvStmt->fetchAll();

        // Get user's existing tags
        $tagStmt = $db->prepare("SELECT target_cp_id, tag FROM profile_tags WHERE mobile = :m");
        $tagStmt->execute([':m' => $mobile]);
        $tags = [];
        while ($t = $tagStmt->fetch()) $tags[$t['target_cp_id']] = $t['tag'];

        // Add match score to each profile
        $addScore = function(&$profiles) use ($me, $tags) {
            foreach ($profiles as &$p) {
                $score = 0; $max = 0;
                // Caste match
                $max += 3;
                if ($me['partner_caste'] && $me['partner_caste'] !== 'Any' && $p['caste'] === $me['partner_caste']) $score += 3;
                elseif (!$me['partner_caste'] || $me['partner_caste'] === 'Any') $score += 2;
                // Age match
                $max += 2;
                $age = (int)($p['age'] ?? 0);
                $ageFrom = (int)($me['partner_age_from'] ?? 0);
                $ageTo = (int)($me['partner_age_to'] ?? 99);
                if ($age >= $ageFrom && $age <= $ageTo) $score += 2;
                // Marital match
                $max += 2;
                if ($me['partner_marital_status'] && $me['partner_marital_status'] !== 'Any' && $p['marital'] === $me['partner_marital_status']) $score += 2;
                elseif (!$me['partner_marital_status'] || $me['partner_marital_status'] === 'Any') $score += 1;
                // Star match
                $max += 1;
                if ($p['star'] && $me['star']) $score += 1;
                // Photo available
                $max += 1;
                if ($p['photo1'] && !str_starts_with($p['photo1'], 'default_')) $score += 1;
                // Qualification
                $max += 1;
                if ($me['partner_qualification'] && $me['partner_qualification'] !== 'Any' && $p['qualification'] === $me['partner_qualification']) $score += 1;
                elseif (!$me['partner_qualification'] || $me['partner_qualification'] === 'Any') $score += 0.5;

                $p['match_score'] = $max > 0 ? round($score / $max * 5, 1) : 0;
                $p['tag'] = $tags[$p['cp_id']] ?? null;
            }
            unset($p);
        };

        $addScore($interest);
        $addScore($preference);
        $addScore($notViewed);

        // 4. Viewed profiles (recently viewed by me)
        $viewedSql = "SELECT DISTINCT p.cp_id, p.name, p.age, p.gender, p.caste, p.star, p.raasi, p.photo1, p.qualification, p.height, p.marital
                      FROM usage_activity ua JOIN profiles p ON p.cp_id = ua.target_cp_id
                      WHERE (ua.mobile = :m OR ua.cp_id = :c) AND ua.activity_type = 'profile_view' AND p.status = 'Approved'
                      ORDER BY ua.datetime DESC LIMIT 20";
        $viewedStmt2 = $db->prepare($viewedSql);
        $viewedStmt2->execute([':m' => $mobile, ':c' => $myCpId]);
        $viewed = $viewedStmt2->fetchAll();
        $addScore($viewed);

        // 5. With photos (random approved opposite gender with photos, not in other lists)
        $usedIds = array_merge(
            array_column($interest, 'cp_id'),
            array_column($preference, 'cp_id'),
            array_column($notViewed, 'cp_id'),
            array_column($viewed, 'cp_id'),
            [$myCpId]
        );
        $usedIds = array_unique(array_filter($usedIds));
        $wpExclude = count($usedIds) > 0 ? implode(',', array_fill(0, count($usedIds), '?')) : "'__none__'";
        $wpSql = "SELECT cp_id, name, age, gender, caste, star, raasi, photo1, qualification, height, marital
                  FROM profiles WHERE gender = ? AND status = 'Approved'
                  AND photo1 IS NOT NULL AND photo1 != '' AND photo1 NOT LIKE 'default_%'
                  AND cp_id NOT IN ($wpExclude) ORDER BY RAND() LIMIT 20";
        $wpParams = array_merge([$oppGender], $usedIds);
        $wpStmt = $db->prepare($wpSql);
        $wpStmt->execute($wpParams);
        $withPhotos = $wpStmt->fetchAll();
        $addScore($withPhotos);

        // 6. Fallback — if primary sections are empty, surface all approved opposite-gender profiles
        $allProfiles = [];
        if (empty($interest) && empty($preference) && empty($notViewed)) {
            $apSql = "SELECT cp_id, name, age, gender, caste, star, raasi, photo1, qualification, height, marital
                      FROM profiles WHERE gender = ? AND status = 'Approved'
                      AND cp_id NOT IN ($excludePlaceholder)
                      ORDER BY id DESC LIMIT 30";
            $apStmt = $db->prepare($apSql);
            $apStmt->execute(array_merge([$oppGender], $viewedIds));
            $allProfiles = $apStmt->fetchAll();
            $addScore($allProfiles);
        }

        foreach ($interest as &$r)    resolve_profile_photos($r);
        foreach ($preference as &$r)  resolve_profile_photos($r);
        foreach ($notViewed as &$r)   resolve_profile_photos($r);
        foreach ($viewed as &$r)      resolve_profile_photos($r);
        foreach ($withPhotos as &$r)  resolve_profile_photos($r);
        foreach ($allProfiles as &$r) resolve_profile_photos($r);
        unset($r);
        json_ok(['interest' => $interest, 'preference' => $preference, 'notViewed' => $notViewed, 'viewed' => $viewed, 'withPhotos' => $withPhotos, 'allProfiles' => $allProfiles]);
    }

    // ── Matches: basic & mutual ──────────────────────────────────────────────
    // Basic   = profiles that satisfy MY partner preferences (one-way).
    // Mutual  = profiles that satisfy MY partner preferences AND whose own
    //           partner preferences are satisfied by ME (two-way handshake).
    if ($act === 'basic_matches' || $act === 'mutual_matches') {
        $mobile = trim($input['mobile'] ?? '');
        $cpId   = trim($input['cp_id'] ?? '');
        if (!$mobile && !$cpId) json_err('mobile or cp_id required');

        if ($cpId) {
            $prof = $db->prepare("SELECT * FROM profiles WHERE cp_id = :c LIMIT 1");
            $prof->execute([':c' => $cpId]);
        } else {
            $prof = $db->prepare("SELECT * FROM profiles WHERE mobile = :m LIMIT 1");
            $prof->execute([':m' => $mobile]);
        }
        $me = $prof->fetch();
        if (!$me) json_err('Source profile not found');

        $myCpId   = $me['cp_id'];
        $oppGender = $me['gender'] === 'Male' ? 'Female' : 'Male';

        // Treat these as wildcards in any *_pref text column
        $isWild = function($v) {
            if ($v === null) return true;
            $t = trim((string)$v);
            if ($t === '') return true;
            $low = strtolower($t);
            return in_array($low, ['any', 'doesn\'t matter', "doesn't matter", 'not applicable', 'all'], true);
        };

        // ── Build "MY partner prefs satisfied by candidate" filter ──────────
        $where  = ["p.gender = ?", "p.status = 'Approved'", "p.cp_id <> ?"];
        $params = [$oppGender, $myCpId];

        if (!$isWild($me['partner_caste']))           { $where[] = "p.caste = ?";      $params[] = $me['partner_caste']; }
        if (!$isWild($me['partner_sub_caste']))       { $where[] = "p.sub_caste = ?";  $params[] = $me['partner_sub_caste']; }
        if (!$isWild($me['partner_marital_status'])) { $where[] = "p.marital = ?";    $params[] = $me['partner_marital_status']; }
        if (!$isWild($me['partner_diet']))            { $where[] = "p.diet = ?";       $params[] = $me['partner_diet']; }
        if (!$isWild($me['partner_qualification']))   { $where[] = "p.qualification = ?"; $params[] = $me['partner_qualification']; }
        if (!$isWild($me['partner_job']))             { $where[] = "p.job = ?";        $params[] = $me['partner_job']; }
        if (!empty($me['partner_age_from'])) { $where[] = "p.age >= ?"; $params[] = (int)$me['partner_age_from']; }
        if (!empty($me['partner_age_to']))   { $where[] = "p.age <= ?"; $params[] = (int)$me['partner_age_to']; }

        // ── Mutual: ALSO require candidate's prefs are satisfied by me ──────
        if ($act === 'mutual_matches') {
            // Candidate's partner_caste either wild or = my caste, etc.
            $oppExpr = function($prefCol, $myVal) use (&$where, &$params) {
                $where[] = "(p.$prefCol IS NULL OR p.$prefCol = '' OR LOWER(p.$prefCol) IN ('any','doesn\\'t matter','not applicable','all') OR p.$prefCol = ?)";
                $params[] = (string)($myVal ?? '');
            };
            $oppExpr('partner_caste',           $me['caste']);
            $oppExpr('partner_sub_caste',       $me['sub_caste']);
            $oppExpr('partner_marital_status',  $me['marital'] ?? '');
            $oppExpr('partner_diet',            $me['diet']);
            $oppExpr('partner_qualification',   $me['qualification']);
            $oppExpr('partner_job',             $me['job']);

            $myAge = (int)($me['age'] ?? 0);
            if ($myAge > 0) {
                $where[] = "(p.partner_age_from IS NULL OR p.partner_age_from = '' OR CAST(p.partner_age_from AS UNSIGNED) <= ?)";
                $params[] = $myAge;
                $where[] = "(p.partner_age_to IS NULL OR p.partner_age_to = '' OR CAST(p.partner_age_to AS UNSIGNED) >= ?)";
                $params[] = $myAge;
            }
        }

        $limit  = max(1, min(60, (int)($input['limit'] ?? 30)));
        $offset = max(0, (int)($input['offset'] ?? 0));

        $sql = "SELECT p.cp_id, p.name, p.age, p.gender, p.caste, p.sub_caste, p.star, p.raasi,
                       p.photo1, p.qualification, p.job, p.height, p.marital, p.religion,
                       p.present_city, p.present_district, p.present_state
                FROM profiles p
                WHERE " . implode(' AND ', $where) . "
                ORDER BY p.id DESC
                LIMIT $limit OFFSET $offset";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Also return total count (without limit) for paging UI
        $cntSql = "SELECT COUNT(*) FROM profiles p WHERE " . implode(' AND ', $where);
        $cntStmt = $db->prepare($cntSql);
        $cntStmt->execute($params);
        $total = (int)$cntStmt->fetchColumn();

        json_ok([
            'profiles' => $rows,
            'total'    => $total,
            'limit'    => $limit,
            'offset'   => $offset,
            'mode'     => $act,
            'source'   => ['cp_id' => $myCpId, 'gender' => $me['gender']],
        ]);
    }

    if ($act === 'tag_profile') {
        $mobile = trim($input['mobile'] ?? '');
        $targetCpId = trim($input['target_cp_id'] ?? '');
        $tag = trim($input['tag'] ?? '');
        if (!$mobile || !$targetCpId || !in_array($tag, ['interested','not_interested','later']))
            json_err('Invalid tag data');
        $db->prepare("INSERT INTO profile_tags (mobile, target_cp_id, tag) VALUES (:m, :t, :tag)
            ON DUPLICATE KEY UPDATE tag = VALUES(tag), updated_at = NOW()")
            ->execute([':m' => $mobile, ':t' => $targetCpId, ':tag' => $tag]);
        json_ok(['message' => 'Tagged']);
    }

    if ($act === 'remove_tag') {
        $mobile = trim($input['mobile'] ?? '');
        $targetCpId = trim($input['target_cp_id'] ?? '');
        if (!$mobile || !$targetCpId) json_err('Invalid data');
        $db->prepare("DELETE FROM profile_tags WHERE mobile = :m AND target_cp_id = :t")
            ->execute([':m' => $mobile, ':t' => $targetCpId]);
        json_ok(['message' => 'Tag removed']);
    }

    if ($act === 'my_tags') {
        $mobile = trim($input['mobile'] ?? '');
        if (!$mobile) json_err('Mobile required');
        $stmt = $db->prepare("SELECT pt.target_cp_id, pt.tag, pt.updated_at, p.name, p.age, p.gender, p.caste, p.star, p.photo1, p.marital, p.qualification
            FROM profile_tags pt LEFT JOIN profiles p ON p.cp_id = pt.target_cp_id
            WHERE pt.mobile = :m ORDER BY pt.updated_at DESC");
        $stmt->execute([':m' => $mobile]);
        $tags = $stmt->fetchAll();
        foreach ($tags as &$t) resolve_profile_photos($t);
        unset($t);
        json_ok(['tags' => $tags]);
    }

    if ($act === 'my_orders') {
        $mobile = trim($input['mobile'] ?? '');
        if (!$mobile) json_err('Mobile required');
        $stmt = $db->prepare("SELECT * FROM user_orders WHERE mobile = :m ORDER BY created_at DESC");
        $stmt->execute([':m' => $mobile]);
        json_ok(['orders' => $stmt->fetchAll()]);
    }

    if ($act === 'place_order') {
        $mobile = trim($input['mobile'] ?? '');
        $plan   = trim($input['plan'] ?? '');
        $amount = trim($input['amount'] ?? '');
        $method = trim($input['method'] ?? '');
        $txnRef = trim($input['txn_ref'] ?? '');
        $notes  = trim($input['notes'] ?? '');
        if (!$mobile || !$plan) json_err('Plan and mobile required');

        $prof = $db->prepare("SELECT cp_id, name FROM profiles WHERE mobile = :m LIMIT 1");
        $prof->execute([':m' => $mobile]);
        $p = $prof->fetch();

        $db->prepare("INSERT INTO user_orders (mobile, cp_id, name, plan, amount, method, txn_ref, notes, created_at)
            VALUES (:m, :cp, :n, :pl, :a, :mt, :tx, :nt, NOW())")
            ->execute([':m' => $mobile, ':cp' => $p['cp_id'] ?? '', ':n' => $p['name'] ?? '',
                       ':pl' => $plan, ':a' => $amount, ':mt' => $method, ':tx' => $txnRef, ':nt' => $notes]);

        $orderId = $db->lastInsertId();
        // Archive log
        $db->prepare("INSERT INTO order_archive (order_id, mobile, cp_id, name, plan, amount, method, txn_ref, notes, action, action_by, created_at)
            VALUES (:oid, :m, :cp, :n, :pl, :a, :mt, :tx, :nt, 'Placed', 'User', NOW())")
            ->execute([':oid' => $orderId, ':m' => $mobile, ':cp' => $p['cp_id'] ?? '', ':n' => $p['name'] ?? '',
                       ':pl' => $plan, ':a' => $amount, ':mt' => $method, ':tx' => $txnRef, ':nt' => $notes]);
        json_ok(['message' => 'Order placed', 'order_id' => $orderId]);
    }

    if ($act === 'contact_logout') {
        secureSession();
        unset($_SESSION['contact_verified'], $_SESSION['contact_mobile'], $_SESSION['contact_verified_at']);
        session_destroy();
        json_ok(['message' => 'Logged out']);
    }

    // ── Registration ────────────────────────────────────────────────────
  try {
    // ── Validate required fields ─────────────────────────────────────────
    $name     = trim($input['name'] ?? '');
    $gender   = $input['gender'] ?? '';
    $dob      = $input['dob'] ?? '';
    $mobile   = trim($input['contactNumber'] ?? '');

    $errors = [];
    if (!$name)                                    $errors[] = 'Name is required';
    if (!$gender || $gender === '-Select-')        $errors[] = 'Gender is required';
    if (!$dob)                                     $errors[] = 'Date of Birth is required';
    if (!$mobile || !preg_match('/^\d{10}$/', $mobile)) $errors[] = 'Valid 10-digit mobile required';

    if ($mobile) {
        $chk = $db->prepare("SELECT cp_id FROM profiles WHERE mobile = :m LIMIT 1");
        $chk->execute([':m' => $mobile]);
        if ($chk->fetch()) $errors[] = 'This mobile number already has a profile. One number = one profile only';
    }

    if ($errors) json_err(implode(', ', $errors));

    // ── Handle photo uploads ─────────────────────────────────────────────
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $allowedExts = ['jpg','jpeg','png','gif','webp'];
    $maxSize = 512000; // 500KB (client compresses to ~100KB, allow margin)

    require_once __DIR__ . '/image-utils.php';
    $uploadFile = function($key) use ($uploadDir, $allowedExts, $maxSize) {
        if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) return null;
        $file = $_FILES[$key];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts) || $file['size'] > $maxSize) return null;
        $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($file['name']));
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $filename)) {
            @generate_webp_variants($uploadDir . $filename);
            require_once __DIR__ . '/s3.php';
            return s3_upload_photo($uploadDir . $filename) ?? 'uploads/' . $filename;
        }
        return null;
    };

    $photo1     = $uploadFile('photo1');
    $photo2     = $uploadFile('photo2');
    $photo3     = $uploadFile('photo3');
    $rasiPhoto  = $uploadFile('rasiPhoto');
    $amsamPhoto = $uploadFile('amsamPhoto');

    // ── Generate CP ID & calculate age ───────────────────────────────────
    $cpId = nextCpId($db);
    $age = null;
    if ($dob) {
        $age = (new DateTime())->diff(new DateTime($dob))->y;
    }

    // ── Validate minimum marriage age: Male=21, Female=18 ────────────────
    if ($age !== null) {
        $genderLower = strtolower(trim($gender));
        $minAge = ($genderLower === 'female') ? 18 : 21;
        if ($age < $minAge) {
            $label = ($genderLower === 'female') ? 'women' : 'men';
            json_err("Minimum age for $label is $minAge years. Current age: $age. Please come back when you reach the legal marriage age.");
        }
    }

    // ── Insert into profiles table ───────────────────────────────────────
    $sql = "INSERT INTO profiles (
        cp_id, mobile, name, age, gender, status, plan, created, created_by, dob,
        birth_hour, birth_min, birth_ampm, place_birth, nativity, workplace,
        mother_tongue, marital, nationality, own_house, born_as, religion,
        father, father_alive, father_job,
        mother, mother_alive, mother_job,
        sib_married_eb, sib_married_yb, sib_married_es, sib_married_ys,
        sib_unmarried_eb, sib_unmarried_yb, sib_unmarried_es, sib_unmarried_ys,
        others,
        height, weight, blood_group, diet, disability, complexion,
        qualification, job, place_of_job, income,
        caste, sub_caste, gothram, star, raasi, paadam, lagnam, dosham, dosham_type,
        partner_qualification, partner_job, partner_job_requirement,
        partner_income_month, partner_age_from, partner_age_to,
        partner_diet, partner_horoscope_required, partner_marital_status,
        partner_caste, partner_sub_caste, partner_other_requirement,
        email, alt_mobile, contact_person, perm_address, present_address, present_area, present_city, present_district, present_state,
        photo1, photo2, photo3, rasi_photo, amsam_photo
    ) VALUES (
        :cp_id, :mobile, :name, :age, :gender, 'Preapproved', 'free', CURDATE(), 'user', :dob,
        :birth_hour, :birth_min, :birth_ampm, :place_birth, :nativity, :workplace,
        :mother_tongue, :marital, :nationality, :own_house, :born_as, :religion,
        :father, :father_alive, :father_job,
        :mother, :mother_alive, :mother_job,
        :sib_married_eb, :sib_married_yb, :sib_married_es, :sib_married_ys,
        :sib_unmarried_eb, :sib_unmarried_yb, :sib_unmarried_es, :sib_unmarried_ys,
        :others,
        :height, :weight, :blood_group, :diet, :disability, :complexion,
        :qualification, :job, :place_of_job, :income,
        :caste, :sub_caste, :gothram, :star, :raasi, :paadam, :lagnam, :dosham, :dosham_type,
        :partner_qualification, :partner_job, :partner_job_requirement,
        :partner_income_month, :partner_age_from, :partner_age_to,
        :partner_diet, :partner_horoscope_required, :partner_marital_status,
        :partner_caste, :partner_sub_caste, :partner_other_requirement,
        :email, :alt_mobile, :contact_person, :perm_address, :present_address, :present_area, :present_city, :present_district, :present_state,
        :photo1, :photo2, :photo3, :rasi_photo, :amsam_photo
    )";

    $clean = function($key, $max = 255) use ($input) {
        return str_clean($input[$key] ?? '', $max);
    };

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':cp_id'          => $cpId,
        ':mobile'         => $mobile,
        ':name'           => $name,
        ':age'            => $age,
        ':gender'         => $gender,
        ':dob'            => $dob ?: null,
        ':birth_hour'     => $clean('birthHour', 5),
        ':birth_min'      => $clean('birthMin', 5),
        ':birth_ampm'     => $clean('birthAmPm', 5),
        ':place_birth'    => $clean('placeBirth'),
        ':nativity'       => $clean('nativity'),
        ':workplace'      => $clean('workplace'),
        ':mother_tongue'  => $clean('motherTongue', 50),
        ':marital'        => $clean('maritalStatus', 50),
        ':nationality'    => $clean('nationality', 100) ?: 'Indian',
        ':own_house'      => $clean('ownHouse', 10) ?: 'Yes',
        ':born_as'        => $clean('bornAs', 20),
        ':religion'       => $clean('religion', 50),
        ':father'         => $clean('fatherName', 150),
        ':father_alive'   => $clean('fatherAlive', 10),
        ':father_job'     => $clean('fatherJob', 100),
        ':mother'         => $clean('motherName', 150),
        ':mother_alive'   => $clean('motherAlive', 10),
        ':mother_job'     => $clean('motherJob', 100),
        ':sib_married_eb'   => $clean('sibMarriedEB', 5),
        ':sib_married_yb'   => $clean('sibMarriedYB', 5),
        ':sib_married_es'   => $clean('sibMarriedES', 5),
        ':sib_married_ys'   => $clean('sibMarriedYS', 5),
        ':sib_unmarried_eb' => $clean('sibUnmarriedEB', 5),
        ':sib_unmarried_yb' => $clean('sibUnmarriedYB', 5),
        ':sib_unmarried_es' => $clean('sibUnmarriedES', 5),
        ':sib_unmarried_ys' => $clean('sibUnmarriedYS', 5),
        ':others'         => $clean('others', 1000),
        ':height'         => $clean('height', 10),
        ':weight'         => $clean('weight', 20),
        ':blood_group'    => $clean('bloodGroup', 5),
        ':diet'           => $clean('diet', 50),
        ':disability'     => $clean('disability', 50),
        ':complexion'     => $clean('complexion', 50),
        ':qualification'  => $clean('qualification', 100),
        ':job'            => $clean('job', 100),
        ':place_of_job'   => $clean('placeJob', 100),
        ':income'         => $clean('incomeMonth', 50),
        ':caste'          => $clean('caste', 100),
        ':sub_caste'      => $clean('subCaste', 100),
        ':gothram'        => $clean('gothram', 100),
        ':star'           => $clean('star', 50),
        ':raasi'          => $clean('raasi', 50),
        ':paadam'         => $clean('padam', 20),
        ':lagnam'         => $clean('laknam', 50),
        ':dosham'         => $clean('dosham', 50),
        ':dosham_type'    => $clean('doshamType', 255),
        ':partner_qualification'      => $clean('partnerQualification'),
        ':partner_job'                => $clean('partnerJob'),
        ':partner_job_requirement'    => $clean('partnerJobRequirement', 50),
        ':partner_income_month'       => $clean('partnerIncomeMonth', 100),
        ':partner_age_from'           => $clean('partnerAgeFrom', 10),
        ':partner_age_to'             => $clean('partnerAgeTo', 10),
        ':partner_diet'               => $clean('partnerDiet', 50),
        ':partner_horoscope_required' => $clean('partnerHoroscopeRequired', 10),
        ':partner_marital_status'     => $clean('partnerMaritalStatus', 50),
        ':partner_caste'              => $clean('partnerCaste', 100),
        ':partner_sub_caste'          => $clean('partnerSubCaste', 100),
        ':partner_other_requirement'  => $clean('partnerOtherRequirement', 1000),
        ':email'          => $clean('email', 150),
        ':alt_mobile'     => $clean('altMobile', 15),
        ':contact_person' => $clean('contactPerson', 150),
        ':perm_address'   => $clean('permanentAddress', 1000),
        ':present_address'=> $clean('presentAddress', 1000),
        ':present_area'   => $clean('presentArea', 255),
        ':present_city'   => $clean('presentCity', 255),
        ':present_district'=> $clean('presentDistrict', 255),
        ':present_state'  => $clean('presentState', 255),
        ':photo1'         => $photo1,
        ':photo2'         => $photo2,
        ':photo3'         => $photo3,
        ':rasi_photo'     => $rasiPhoto,
        ':amsam_photo'    => $amsamPhoto,
    ]);

    // Also create otp_logs entry so user can login with this mobile
    $db->prepare(
        "INSERT INTO otp_logs (mobile, cp_id, name) VALUES (:m, :cp, :n)
         ON DUPLICATE KEY UPDATE cp_id = VALUES(cp_id), name = VALUES(name)"
    )->execute([':m' => $mobile, ':cp' => $cpId, ':n' => $name]);

    recordHistory('profile', $cpId, 'created', null, null, $name.' / '.$mobile, ['name'=>'Registration','role'=>'user']);

    echo json_encode(['ok' => true, 'success' => true,
        'message' => 'Registration successful! Your profile ID is ' . $cpId,
        'cp_id' => $cpId, 'id' => $db->lastInsertId()]);
    exit;
  } catch (Throwable $e) {
    // Log full detail server-side; return generic message to client.
    log_error('Registration failed: ' . $e->getMessage(), [
        'exception' => get_class($e),
        'file'      => $e->getFile(),
        'line'      => $e->getLine(),
        'mobile'    => $input['mobile'] ?? null,
    ]);
    json_err('Registration failed. Please try again or contact support.', 500);
  }
}

// ── GET: Search & Detail ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_err('Method not allowed', 405);

$action = $_GET['action'] ?? 'search';

// Read-side rate limits. Real users scroll fast; bots scroll much faster.
// Limits are generous for humans and still bite scrapers harvesting profiles.
$GET_LIMITS = [
    'search'     => [60, 60],   // 60 req/min (= smooth scroll + tab switches)
    'bootstrap'  => [20, 60],
    'detail'     => [40, 60],   // browsing profiles
    'checkMobile'=> [15, 60],
];
if (isset($GET_LIMITS[$action])) {
    [$max, $window] = $GET_LIMITS[$action];
    rate_limit('get_' . $action, $max, $window);
}

switch ($action) {

    // ─────────────────────────────────────────────────────────────────────────
    // Bootstrap: everything the homepage needs in ONE round-trip.
    // Returns: { contact: {verified, mobile}, male: {profiles, total}, female: {profiles, total} }
    // Replaces 3 separate calls (contact_check POST + male search + female search).
    // ─────────────────────────────────────────────────────────────────────────
    case 'bootstrap': {
        $limit = min(max((int)($_GET['limit'] ?? 12), 1), 24);

        // Session-check without going through POST (we already have cookie context).
        // A user-panel login (auth.php sets $_SESSION['mobile']) counts as verified too —
        // otherwise the MobileGate re-prompts an already-logged-in user for OTP.
        secureSession();
        $verified = !empty($_SESSION['contact_mobile']) || !empty($_SESSION['mobile']);
        $mobile = (string)($_SESSION['contact_mobile'] ?? $_SESSION['mobile'] ?? '');
        if ($verified && !kfm_is_returning()) kfm_set_returning_cookie();

        // Optional shuffle seed: when set, profiles are returned in a deterministic
        // pseudo-random order across the FULL approved-with-photo set (not just the
        // newest N). The same seed produces the same global ordering, so the client
        // can paginate consistently via subsequent `search` calls with the same seed.
        $seed = trim((string)($_GET['seed'] ?? ''));
        $useRandom = $seed !== '';

        // Helper: paged list for one gender, with photo. Newest-first by default;
        // pseudo-random across the full set when a seed is supplied. Cached 60s only
        // on the deterministic newest-first path — seeded responses are per-visitor.
        $byGender = function($gender) use ($db, $limit, $seed, $useRandom) {
            $runQuery = function() use ($db, $gender, $limit, $seed, $useRandom) {
                $w = "p.status = 'Approved' AND p.gender = :g
                      AND p.photo1 IS NOT NULL AND p.photo1 != '' AND p.photo1 NOT LIKE 'default_%'";
                $c = $db->prepare("SELECT COUNT(*) FROM profiles p WHERE $w");
                $c->execute([':g' => $gender]);
                $total = (int)$c->fetchColumn();

                $orderBy = $useRandom ? "MD5(CONCAT(p.id, :seed))" : "p.id DESC";
                $s = $db->prepare("SELECT p.cp_id, p.name, p.age, p.gender, p.caste, p.mother_tongue,
                                          p.marital, p.height, p.qualification, p.job, p.star, p.raasi,
                                          p.religion, p.photo1,
                                          p.present_area, p.present_city, p.present_district, p.present_state,
                                          IF(p.mobile IS NOT NULL AND p.mobile != '', 1, 0) AS has_phone
                                   FROM profiles p
                                   WHERE $w
                                   ORDER BY $orderBy
                                   LIMIT $limit");
                $params = [':g' => $gender];
                if ($useRandom) $params[':seed'] = $seed;
                $s->execute($params);
                $profiles = $s->fetchAll();
                foreach ($profiles as &$p) resolve_profile_photos($p);
                unset($p);
                return ['profiles' => $profiles, 'total' => $total];
            };
            if ($useRandom) return $runQuery();
            $key = "bootstrap:g=$gender:l=$limit";
            return cache_remember($key, 60, $runQuery);
        };

        header('Cache-Control: private, max-age=30');
        json_ok([
            'contact' => ['verified' => $verified, 'mobile' => $mobile] + kfm_gate_snapshot(),
            'male'    => $byGender('Male'),
            'female'  => $byGender('Female'),
            'limit'   => $limit,
        ]);
    }

    case 'search': {
        $where = ["p.status = 'Approved'"];
        $params = [];

        // Simple equality filters
        $eqMap = [
            'gender'   => 'p.gender',
            'caste'    => 'p.caste',
            'language' => 'p.mother_tongue',
            'marital'  => 'p.marital',
            'religion' => 'p.religion',
            'star'     => 'p.star',
            'raasi'    => 'p.raasi',
            'diet'     => 'p.diet',
            'dosham'   => 'p.dosham',
        ];
        foreach ($eqMap as $key => $col) {
            if (!empty($_GET[$key]) && $_GET[$key] !== 'Any') {
                $ph = ':' . $key;
                $where[] = "$col = $ph";
                $params[$ph] = $_GET[$key];
            }
        }

        // Range filters
        if (!empty($_GET['ageFrom'])) { $where[] = "p.age >= :ageFrom"; $params[':ageFrom'] = (int)$_GET['ageFrom']; }
        if (!empty($_GET['ageTo']))   { $where[] = "p.age <= :ageTo";   $params[':ageTo']   = (int)$_GET['ageTo']; }
        if (!empty($_GET['heightFrom'])) { $where[] = "p.height >= :hF"; $params[':hF'] = $_GET['heightFrom']; }
        if (!empty($_GET['heightTo']))   { $where[] = "p.height <= :hT"; $params[':hT'] = $_GET['heightTo']; }

        // LIKE filters
        if (!empty($_GET['q'])) {
            $where[] = "(p.name LIKE :q OR p.cp_id LIKE :q)";
            $params[':q'] = '%' . $_GET['q'] . '%';
        }
        if (!empty($_GET['qualification'])) {
            $where[] = "p.qualification LIKE :qual";
            $params[':qual'] = '%' . $_GET['qualification'] . '%';
        }
        if (!empty($_GET['district'])) {
            $where[] = "(p.present_district LIKE :dist OR p.present_city LIKE :dist)";
            $params[':dist'] = '%' . $_GET['district'] . '%';
        }

        // Photo filter
        $photo = $_GET['photo'] ?? '';
        if ($photo === 'with') {
            $where[] = "p.photo1 IS NOT NULL AND p.photo1 != '' AND p.photo1 NOT LIKE 'default_%'";
        } elseif ($photo === 'without') {
            $where[] = "(p.photo1 IS NULL OR p.photo1 = '' OR p.photo1 LIKE 'default_%')";
        }

        // Horoscope filter
        $horo = $_GET['horoscope'] ?? '';
        if ($horo === 'with') {
            $where[] = "((p.rasi_photo IS NOT NULL AND p.rasi_photo != '') OR (p.amsam_photo IS NOT NULL AND p.amsam_photo != ''))";
        } elseif ($horo === 'without') {
            $where[] = "(p.rasi_photo IS NULL OR p.rasi_photo = '') AND (p.amsam_photo IS NULL OR p.amsam_photo = '')";
        }

        $sort = (!empty($_GET['sortId']) && $_GET['sortId'] === 'desc') ? 'DESC' : 'ASC';
        // Tight defaults: 12 per page, max 50 (was 500/1000)
        $limit  = min(max((int)($_GET['limit'] ?? 12), 1), 50);
        $offset = max((int)($_GET['offset'] ?? 0), 0);
        $whereStr = implode(' AND ', $where);

        // Optional shuffle seed: when set, results are returned in a deterministic
        // pseudo-random order across the FULL filtered set, consistent across pages
        // for the same seed — so paginated calls with the same seed walk the
        // whole DB in one stable random ordering.
        $seed = trim((string)($_GET['seed'] ?? ''));
        $useRandom = $seed !== '';
        if ($useRandom) {
            $params[':seed'] = $seed;
            $orderClause = "MD5(CONCAT(p.id, :seed))";
        } else {
            $orderClause = "p.id $sort";
        }

        // Count only on first page — saves a COUNT(*) on every scroll.
        // Cached 60s per unique filter signature (most traffic hits the same few filter combos).
        $total = null;
        if ($offset === 0 || !empty($_GET['withTotal'])) {
            $countParams = $params;
            unset($countParams[':seed']); // count doesn't depend on order
            $countKey = 'cnt:' . md5($whereStr . '|' . serialize($countParams));
            $total = cache_remember($countKey, 60, function() use ($db, $whereStr, $countParams) {
                $s = $db->prepare("SELECT COUNT(*) FROM profiles p WHERE $whereStr");
                $s->execute($countParams);
                return (int)$s->fetchColumn();
            });
        }

        // Trimmed column list — only what list views actually render.
        // Detail page fetches full row via action=detail.
        $sql = "SELECT p.cp_id, p.name, p.age, p.gender, p.caste, p.mother_tongue,
                       p.marital, p.height, p.qualification, p.job, p.star, p.raasi,
                       p.religion, p.photo1,
                       p.present_area, p.present_city, p.present_district, p.present_state,
                       IF(p.mobile IS NOT NULL AND p.mobile != '', 1, 0) AS has_phone
                FROM profiles p
                WHERE $whereStr
                ORDER BY $orderClause
                LIMIT $limit OFFSET $offset";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        // Cache: deterministic ordering is shareable; seeded ordering is per-visitor.
        if ($useRandom) {
            header('Cache-Control: private, max-age=60');
        } else {
            header('Cache-Control: public, max-age=60, stale-while-revalidate=120');
        }

        $profiles = $stmt->fetchAll();
        foreach ($profiles as &$p) resolve_profile_photos($p);
        unset($p);
        $resp = ['profiles' => $profiles, 'limit' => $limit, 'offset' => $offset];
        if ($total !== null) $resp['total'] = $total;
        json_ok($resp);
    }

    case 'detail': {
        $cpId = str_clean($_GET['cp_id'] ?? '', 20);
        if (!$cpId) json_err('CP ID required');

        $stmt = $db->prepare("SELECT * FROM profiles WHERE cp_id = :cp AND status = 'Approved' LIMIT 1");
        $stmt->execute([':cp' => $cpId]);
        $profile = $stmt->fetch();
        if (!$profile) json_err('Profile not found', 404);

        // Remove sensitive fields
        unset($profile['pending_plan'], $profile['pending_amount'], $profile['pending_pay_opt_id'], $profile['payment_status']);
        resolve_profile_photos($profile);

        // Chennai Profile: always strip contact — revealed only via track_view (points gate).
        if (defined('SITE_ID') && SITE_ID === 'chennaip') {
            $profile['mobile'] = '';
            $profile['alt_mobile'] = '';
            $profile['email'] = '';
            $profile['contact_person'] = '';
        }

        json_ok(['profile' => $profile]);
    }

    default: json_err('Unknown action');
}
