<?php
// Staff activity log API — called by staff-hq.php to fetch this site's admin_log.
// Auth: HMAC-SHA256 with same .deploy-secret used by deploy.php.
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$secretFile = __DIR__ . '/.deploy-secret';
if (!is_readable($secretFile)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'deploy-secret missing on server']);
    exit;
}
$secret = trim((string)file_get_contents($secretFile));

$tsHeader  = $_SERVER['HTTP_X_DEPLOY_TIMESTAMP'] ?? '';
$sigHeader = $_SERVER['HTTP_X_DEPLOY_SIGNATURE']  ?? '';

if (!$tsHeader || !$sigHeader) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing auth headers']);
    exit;
}
if (abs(time() - (int)$tsHeader) > 300) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'timestamp expired']);
    exit;
}
if (!preg_match('/^sha256=([a-f0-9]{64})$/', $sigHeader, $m)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'bad signature format']);
    exit;
}
$expected = hash_hmac('sha256', $tsHeader . ':staff-logs', $secret);
if (!hash_equals($expected, $m[1])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'signature mismatch']);
    exit;
}

$limit = min((int)($_GET['limit'] ?? 300), 500);
$since = $_GET['since'] ?? '';

$db = getDB();

$sql    = "SELECT admin_name, role, action, detail, type, timestamp FROM admin_log";
$params = [];
if ($since) {
    $sql .= " WHERE timestamp > :since";
    $params[':since'] = $since;
}
$sql .= " ORDER BY timestamp DESC LIMIT :lim";

$stmt = $db->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$host    = strtolower($_SERVER['HTTP_HOST'] ?? '');
$siteTag = (strpos($host, 'kumbakonam') !== false) ? 'KFH' : 'CPA';

echo json_encode(['ok' => true, 'site' => $siteTag, 'count' => count($rows), 'rows' => $rows]);
