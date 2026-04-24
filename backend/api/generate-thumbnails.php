<?php
// One-off backfill: generate .webp and .thumb.webp for every existing upload.
// Run once from browser (localhost only) OR from CLI: php generate-thumbnails.php
// Idempotent — safe to re-run; it skips files that already have variants.

require_once __DIR__ . '/image-utils.php';

// Restrict browser execution to local — prevents accidental public DOS.
if (PHP_SAPI !== 'cli') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($ip, ['127.0.0.1', '::1'])) {
        http_response_code(403);
        exit('CLI or localhost only.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

@set_time_limit(0);
@ini_set('memory_limit', '512M');

if (!function_exists('imagewebp')) {
    exit("ERROR: GD WebP support not available. Enable 'gd' with WebP in PHP Selector.\n");
}

$dir = __DIR__ . '/uploads/';
if (!is_dir($dir)) exit("ERROR: $dir not found.\n");

$stats = ['scanned' => 0, 'created_full' => 0, 'created_thumb' => 0, 'skipped' => 0, 'failed' => 0, 'bytes_in' => 0, 'bytes_out' => 0];
$startedAt = microtime(true);

echo "Scanning $dir …\n";
$it = new DirectoryIterator($dir);
foreach ($it as $f) {
    if ($f->isDot() || !$f->isFile()) continue;
    $name = $f->getFilename();
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

    // Skip anything that's already a generated variant, and unsupported types
    if (!in_array($ext, ['jpg','jpeg','png','gif'], true)) continue;
    if (str_ends_with($name, '.thumb.webp')) continue;

    $abs = $f->getPathname();
    $base = pathinfo($abs, PATHINFO_FILENAME);
    $fullOut  = $dir . $base . '.webp';
    $thumbOut = $dir . $base . '.thumb.webp';

    $stats['scanned']++;
    $hadFull  = is_file($fullOut);
    $hadThumb = is_file($thumbOut);
    if ($hadFull && $hadThumb) { $stats['skipped']++; continue; }

    $stats['bytes_in'] += filesize($abs);
    try {
        $r = generate_webp_variants($abs);
        if (!$hadFull  && $r['full'])  { $stats['created_full']++;  $stats['bytes_out'] += filesize($r['full']); }
        if (!$hadThumb && $r['thumb']) { $stats['created_thumb']++; $stats['bytes_out'] += filesize($r['thumb']); }
        echo "OK  $name\n";
    } catch (Throwable $e) {
        $stats['failed']++;
        echo "ERR $name — " . $e->getMessage() . "\n";
    }
    // Flush output for long-running jobs
    if ($stats['scanned'] % 25 === 0) @ob_flush();
}

$elapsed = round(microtime(true) - $startedAt, 1);
$mbIn  = round($stats['bytes_in']  / 1048576, 1);
$mbOut = round($stats['bytes_out'] / 1048576, 1);
$saved = $mbIn > 0 ? round(100 - ($mbOut / $mbIn * 100), 1) : 0;

echo "\n— Done in {$elapsed}s —\n";
echo "Scanned:       {$stats['scanned']}\n";
echo "Thumbs made:   {$stats['created_thumb']}\n";
echo "Full webp:     {$stats['created_full']}\n";
echo "Skipped:       {$stats['skipped']} (already had variants)\n";
echo "Failed:        {$stats['failed']}\n";
echo "Source bytes:  {$mbIn} MB\n";
echo "Variant bytes: {$mbOut} MB  (≈ {$saved}% smaller when WebP served to clients)\n";
