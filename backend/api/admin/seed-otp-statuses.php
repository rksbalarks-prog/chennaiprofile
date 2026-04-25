<?php
// One-shot admin tool: insert 5 sample otp_logs rows demonstrating each
// of the new lifecycle statuses. Visit this URL once while logged in as
// admin to seed the test rows; rerunning is idempotent (uses INSERT ...
// ON DUPLICATE KEY UPDATE on a unique mobile index).
//
// DELETE THIS FILE AFTER VERIFYING THE ADMIN OTP LOGS PAGE.

require_once __DIR__ . '/../../admin-config.php';
cors();
adminSession();
adminRequired();

$db = getDB();

$samples = [
    ['mobile' => '9000000001', 'name' => 'Sample Web In',      'status' => 'web_in'],
    ['mobile' => '9000000002', 'name' => 'Sample Web Out',     'status' => 'web_out'],
    ['mobile' => '9000000003', 'name' => 'Sample OTP Request', 'status' => 'otp_request'],
    ['mobile' => '9000000004', 'name' => 'Sample OTP Verified','status' => 'verified'],
    ['mobile' => '9000000005', 'name' => 'Sample OTP Failed',  'status' => 'otp_failed'],
];

$stmt = $db->prepare(
    "INSERT INTO otp_logs (mobile, cp_id, name, otp_requested_at, verified, last_login, login_count, banned)
     VALUES (:m, NULL, :n, NOW(), :v, NULL, 0, 0)
     ON DUPLICATE KEY UPDATE
       name             = VALUES(name),
       otp_requested_at = NOW(),
       verified         = VALUES(verified)"
);

foreach ($samples as $s) {
    $stmt->execute([':m' => $s['mobile'], ':n' => $s['name'], ':v' => $s['status']]);
}

json_ok([
    'message' => 'Seeded ' . count($samples) . ' sample rows.',
    'samples' => $samples,
    'note'    => 'Open admin → OTP Logs to verify. Delete this file when done.',
]);
