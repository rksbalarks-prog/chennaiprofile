<?php
// Temp admin diagnostic: returns the last 100 lines of track-debug.log so we
// can see whether MobileGate tracking pings are reaching the backend.
// DELETE THIS FILE once tracking is confirmed working.
require_once __DIR__ . '/../../admin-config.php';
cors();
adminSession();
adminRequired();

$path = __DIR__ . '/../../logs/track-debug.log';
header('Content-Type: text/plain; charset=utf-8');
if (!file_exists($path)) {
    echo "(log file not found: $path)\n";
    echo "If MobileGate fires sendBeacon successfully, this file should appear after the first request.\n";
    exit;
}
$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$tail  = array_slice($lines, -100);
echo "=== last " . count($tail) . " of " . count($lines) . " entries ===\n";
echo implode("\n", $tail) . "\n";
