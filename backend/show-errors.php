<?php
// Temporary: show last 50 lines of today's error log. DELETE after use.
$token = $_GET['t'] ?? '';
if ($token !== 'kfm2026debug') { http_response_code(403); exit('forbidden'); }
$log = __DIR__ . '/logs/errors-' . date('Y-m-d') . '.log';
header('Content-Type: text/plain; charset=utf-8');
if (!is_file($log)) { echo "No log file today: $log\n"; exit; }
$lines = file($log);
$last  = array_slice($lines, -50);
foreach ($last as $l) {
    $d = json_decode($l, true);
    if ($d) echo '[' . ($d['ts']??'') . '] ' . ($d['level']??'') . ': ' . ($d['msg']??'') . ' ' . json_encode($d['ctx'] ?? []) . "\n";
    else echo $l;
}
