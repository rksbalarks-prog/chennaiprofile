<?php
// matrimony/api/logs.php

require_once __DIR__ . '/../config.php';

cors();
$mobile = authRequired();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_err('Method not allowed', 405);
}

$db = getDB();

// Return the otp_logs record for this mobile (login history)
$stmt = $db->prepare(
    "SELECT
       id,
       mobile,
       cp_id,
       name,
       otp_requested_at,
       verified,
       last_login,
       login_count,
       banned
     FROM otp_logs
     WHERE mobile = :m
     LIMIT 1"
);
$stmt->execute([':m' => $mobile]);
$log = $stmt->fetch();

if ($log) {
    $log['login_count'] = (int) $log['login_count'];
    $log['banned']      = (bool) $log['banned'];
}

// Also return a history of OTP request timestamps (approximated from otp_logs)
// For richer history we expose what we have in the single-row log model
// and additionally return recent sessions (without the OTP value).
$sessStmt = $db->prepare(
    "SELECT
       expires_at,
       attempts,
       verified
     FROM otp_sessions
     WHERE mobile = :m
     LIMIT 1"
);
$sessStmt->execute([':m' => $mobile]);
$session = $sessStmt->fetch();

if ($session) {
    $session['attempts'] = (int) $session['attempts'];
    $session['verified'] = (bool) $session['verified'];
}

json_ok([
    'log'     => $log     ?: null,
    'session' => $session ?: null,
]);
