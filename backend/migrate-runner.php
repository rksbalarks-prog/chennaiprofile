<?php
// DB migration runner — called by GitHub Actions after each deploy.
// Authenticated via the same HMAC-SHA256 mechanism as deploy.php.
// Safe to call repeatedly — all migrations use IF NOT EXISTS / idempotent SQL.
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

$secretFile = __DIR__ . '/.deploy-secret';
if (!is_readable($secretFile)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'deploy-secret missing']);
    exit;
}
$secret = trim((string)file_get_contents($secretFile));

$tsHeader  = $_SERVER['HTTP_X_DEPLOY_TIMESTAMP'] ?? '';
$sigHeader = $_SERVER['HTTP_X_DEPLOY_SIGNATURE']  ?? '';

if (!$tsHeader || !$sigHeader) {
    http_response_code(400); echo json_encode(['ok' => false, 'error' => 'missing auth headers']); exit;
}
if (abs(time() - (int)$tsHeader) > 300) {
    http_response_code(401); echo json_encode(['ok' => false, 'error' => 'timestamp expired']); exit;
}
if (!preg_match('/^sha256=([a-f0-9]{64})$/', $sigHeader, $m)) {
    http_response_code(400); echo json_encode(['ok' => false, 'error' => 'bad signature']); exit;
}
$expected = hash_hmac('sha256', $tsHeader . ':migrate', $secret);
if (!hash_equals($expected, $m[1])) {
    http_response_code(401); echo json_encode(['ok' => false, 'error' => 'signature mismatch']); exit;
}

$db = getDB();
$results = [];

$migrations = [
    '001_add_target_cp_id_to_point_orders' =>
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

echo json_encode(['ok' => true, 'results' => $results]);
