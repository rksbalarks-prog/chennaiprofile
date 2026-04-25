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

// Realistic samples: web_in / web_out are partial digit counts (3–9),
// since OTP send requires a full 10-digit number. Drop any earlier seed
// rows from the previous placeholder run.
$cleanup = ['9000000001','9000000002','9000000003','9000000004','9000000005'];
$ph = implode(',', array_fill(0, count($cleanup), '?'));
$db->prepare("DELETE FROM otp_logs WHERE mobile IN ($ph)")->execute($cleanup);

$samples = [
    ['mobile' => '7800',       'name' => null, 'status' => 'web_in'],       // 4 digits, still typing
    ['mobile' => '78000',      'name' => null, 'status' => 'web_out'],      // 5 digits, left
    ['mobile' => '987654',     'name' => null, 'status' => 'web_out'],      // 6 digits, left
    ['mobile' => '9876543210', 'name' => 'Sample OTP Request',  'status' => 'otp_request'],
    ['mobile' => '9876543211', 'name' => 'Sample OTP Verified', 'status' => 'verified'],
    ['mobile' => '9876543212', 'name' => 'Sample OTP Failed',   'status' => 'otp_failed'],
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
