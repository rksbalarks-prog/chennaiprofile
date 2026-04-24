<?php
// matrimony/backend/api/health.php
// Uptime health check endpoint for cron-job.org.
//
// Hit every 10 minutes with a secret token:
//   https://YOURDOMAIN/backend/api/health.php?token=CHANGE_ME_TO_A_LONG_RANDOM_STRING
//
// Returns 200 OK + JSON when healthy, 503 + JSON when unhealthy.
// On a DOWN transition (was OK last check, failing this check), sends a
// login-OTP-template SMS with OTP "0000" to the first active admin's mobile so
// the recipient can identify it as a server alert, not a real login.
//
// cron-job.org setup:
//   URL:        https://YOURDOMAIN/backend/api/health.php?token=YOUR_SECRET
//   Schedule:   */10 * * * *   (every 10 min)
//   Notify on failure: email (cron-job.org built-in)
//   Success regex (recommended): "ok":true
//
// If the whole server is unreachable, cron-job.org sends the email itself;
// if only the app is broken (DB down, disk full, uploads unwritable), this
// script sends the "OTP 0000" SMS and returns 503.

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../sms.php';

$isCli = PHP_SAPI === 'cli';
if (!$isCli) header('Content-Type: application/json; charset=utf-8');

// ── Shared secret — required for HTTP requests (cron-job.org / curl).
//    Skipped when run directly via PHP CLI (`/usr/bin/php health.php`) since
//    only a server-side cron or admin shell can invoke that path.
$EXPECTED_TOKEN = '7178c1aaa07f2f8570ca98dd254503336f342f43fc4ce884';

if (!$isCli) {
    $providedToken = $_GET['token'] ?? '';
    if (!hash_equals($EXPECTED_TOKEN, (string)$providedToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }
}

// ── Run checks ────────────────────────────────────────────────────────────
$checks  = [];
$failed  = [];

// DB connectivity + simple query
try {
    $db = getDB();
    $db->query("SELECT 1")->fetch();
    $checks['db'] = 'ok';
} catch (Throwable $e) {
    $checks['db'] = 'fail';
    $failed[] = 'db:' . substr($e->getMessage(), 0, 80);
}

// Uploads folder writable (new Create Profile submits land here)
$uploadDir = __DIR__ . '/uploads/';
if (is_dir($uploadDir) && is_writable($uploadDir)) {
    $checks['uploads'] = 'ok';
} else {
    $checks['uploads'] = 'fail';
    $failed[] = 'uploads:not_writable';
}

// Disk space — alert if < 500MB free on the partition holding uploads
$free = @disk_free_space($uploadDir) ?: 0;
$freeMB = (int)round($free / 1048576);
if ($free > 500 * 1048576) {
    $checks['disk'] = 'ok (' . $freeMB . 'MB free)';
} else {
    $checks['disk'] = 'low (' . $freeMB . 'MB free)';
    $failed[] = 'disk:' . $freeMB . 'MB';
}

$allOk = empty($failed);

// ── State tracking — only SMS on DOWN transition (avoid spamming) ────────
$stateFile = __DIR__ . '/../health-state.json';
$lastState = ['status' => 'unknown', 'since' => null, 'last_alert' => null];
if (is_file($stateFile)) {
    $raw = @file_get_contents($stateFile);
    $decoded = $raw ? json_decode($raw, true) : null;
    if (is_array($decoded)) $lastState = array_merge($lastState, $decoded);
}

$newStatus = $allOk ? 'up' : 'down';
$isTransition = ($newStatus === 'down' && $lastState['status'] !== 'down');

// Belt-and-suspenders: also re-alert if we've been down for > 1 hour (e.g. DB
// has been bouncing and first alert got eaten by the carrier).
$staleDown = ($newStatus === 'down'
    && !empty($lastState['last_alert'])
    && (time() - strtotime($lastState['last_alert'])) > 3600);

$shouldAlert = $isTransition || $staleDown;

// ── Send SMS (OTP "0000") on DOWN transition ─────────────────────────────
$smsOutcome = null;
if ($shouldAlert) {
    try {
        $adm = $db->query("SELECT mobile FROM admins WHERE status='active' AND mobile IS NOT NULL AND mobile != '' ORDER BY id ASC LIMIT 1")->fetch();
        $adminMobile = $adm['mobile'] ?? null;
        if ($adminMobile) {
            $r = sendOTP($adminMobile, '0000');
            $smsOutcome = $r['success'] ? 'sent to ****' . substr($adminMobile, -4) : 'failed: ' . ($r['error'] ?? 'unknown');
        } else {
            $smsOutcome = 'skipped: no active admin mobile';
        }
    } catch (Throwable $e) {
        $smsOutcome = 'exception: ' . substr($e->getMessage(), 0, 80);
    }
}

// ── Persist state ────────────────────────────────────────────────────────
$nextState = [
    'status'     => $newStatus,
    'since'      => ($lastState['status'] === $newStatus && $lastState['since']) ? $lastState['since'] : date('c'),
    'last_check' => date('c'),
    'last_alert' => $shouldAlert ? date('c') : ($lastState['last_alert'] ?? null),
    'checks'     => $checks,
    'failed'     => $failed,
];
@file_put_contents($stateFile, json_encode($nextState, JSON_PRETTY_PRINT));

// ── Respond ──────────────────────────────────────────────────────────────
if (!$allOk) http_response_code(503);
echo json_encode([
    'ok'        => $allOk,
    'checks'    => $checks,
    'failed'    => $failed,
    'since'     => $nextState['since'],
    'alerted'   => $shouldAlert,
    'sms'       => $smsOutcome,
], JSON_PRETTY_PRINT);
