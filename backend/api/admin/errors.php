<?php
// Admin-only error log viewer. Tails the most recent errors.log entries.
// GET params:
//   ?date=YYYY-MM-DD  → which day to read (default: today)
//   ?limit=100        → how many entries from the end (default: 100, max: 1000)
//   ?level=error      → filter by level (error|warn|info). Omit = all.
//   ?action=search    → filter by action name. Omit = all.

require_once __DIR__ . '/../../admin-config.php';
cors();
$admin = adminRequired();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') json_err('Method not allowed', 405);

$date   = $_GET['date']   ?? date('Y-m-d');
$limit  = min(max((int)($_GET['limit'] ?? 100), 1), 1000);
$level  = $_GET['level']  ?? '';
$action = $_GET['action'] ?? '';

// Strict YYYY-MM-DD to prevent path traversal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) json_err('Bad date', 400);

$file = LOG_DIR . '/errors-' . $date . '.log';
if (!is_file($file)) {
    json_ok(['entries' => [], 'file' => basename($file), 'note' => 'no log file for that date']);
}

// Tail-style read: efficient even for big logs — read last ~1 MB, split by lines.
$size = filesize($file);
$readLen = min($size, 1048576);
$fh = fopen($file, 'r');
fseek($fh, -$readLen, SEEK_END);
$chunk = fread($fh, $readLen);
fclose($fh);
if ($readLen < $size) {
    // Trim partial first line
    $chunk = substr($chunk, strpos($chunk, "\n") + 1);
}

$entries = [];
foreach (array_reverse(explode("\n", trim($chunk))) as $line) {
    if ($line === '') continue;
    $e = json_decode($line, true);
    if (!$e) continue;
    if ($level  !== '' && ($e['level']  ?? '') !== $level)  continue;
    if ($action !== '' && ($e['action'] ?? '') !== $action) continue;
    $entries[] = $e;
    if (count($entries) >= $limit) break;
}

// Small aggregate: top-10 error messages for the day (helps spot hotspots)
$byMsg = [];
foreach ($entries as $e) {
    if (($e['level'] ?? '') !== 'error') continue;
    $k = $e['msg'] ?? '';
    $byMsg[$k] = ($byMsg[$k] ?? 0) + 1;
}
arsort($byMsg);
$top = array_slice($byMsg, 0, 10, true);

json_ok([
    'file'    => basename($file),
    'count'   => count($entries),
    'top'     => $top,
    'entries' => $entries,
]);
