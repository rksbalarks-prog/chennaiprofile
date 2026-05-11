<?php
// One-time migration runner — self-deletes after success.
// Access: must be logged in as admin (admin_id in session).
require_once __DIR__ . '/config.php';
cors();
secureSession();

header('Content-Type: application/json');

if (empty($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Admin session required.']);
    exit;
}

$db = getDB();
$results = [];

$migrations = [
    'add target_cp_id to point_orders' =>
        "ALTER TABLE `point_orders` ADD COLUMN IF NOT EXISTS `target_cp_id` VARCHAR(20) NOT NULL DEFAULT '' AFTER `status`",
];

foreach ($migrations as $name => $sql) {
    try {
        $db->exec($sql);
        $results[$name] = 'ok';
    } catch (PDOException $e) {
        $results[$name] = 'error: ' . $e->getMessage();
    }
}

// Self-delete
@unlink(__FILE__);

echo json_encode(['ok' => true, 'results' => $results, 'deleted' => !file_exists(__FILE__)]);
