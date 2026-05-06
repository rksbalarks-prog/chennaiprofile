<?php
// Batch S3 uploader — safe to re-run, skips files already in S3.
// Processes 20 files per request to avoid web server timeouts.
// Usage: visit ?offset=0, then click "Next batch" link until done.
// DELETE this file after migration is complete.

declare(strict_types=1);
set_time_limit(120);
@ini_set('memory_limit', '256M');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/s3.php';

header('Content-Type: text/html; charset=utf-8');
echo '<pre style="font-family:monospace;font-size:13px;padding:16px">';

if (!defined('S3_ENABLED') || !S3_ENABLED) die("S3_ENABLED is false.\n");

$db           = getDB();
$uploadDir    = __DIR__ . '/api/uploads/';
$photoColumns = ['photo1', 'photo2', 'photo3', 'rasi_photo', 'amsam_photo'];
$mimeMap      = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
                 'gif'=>'image/gif','webp'=>'image/webp'];
$batchSize    = 20;
$offset       = max(0, (int)($_GET['offset'] ?? 0));

// Collect originals (jpg/png/gif + full-size webp, skip .thumb.webp)
$files = glob($uploadDir . '*') ?: [];
$originals = array_values(array_filter($files, function($f) {
    $base = basename($f);
    if (str_ends_with($base, '.thumb.webp')) return false;
    $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg','jpeg','png','gif','webp']);
}));

$total = count($originals);
$batch = array_slice($originals, $offset, $batchSize);

echo "Total originals found : {$total}\n";
echo "Processing            : " . count($batch) . " files (offset {$offset}–" . ($offset + count($batch) - 1) . ")\n\n";
flush();

$uploaded = 0;
$errors   = 0;
$dbUpdated = 0;

foreach ($batch as $absPath) {
    $filename = basename($absPath);
    $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mime     = $mimeMap[$ext] ?? 'image/jpeg';
    $stem     = pathinfo($filename, PATHINFO_FILENAME);

    $s3Url = s3_put($absPath, 'photos/' . $filename, $mime);
    if (!$s3Url) {
        echo "FAILED : {$filename}\n";
        $errors++;
        flush();
        continue;
    }

    // Upload WebP variants
    if ($ext !== 'webp') {
        $fullWebp  = $uploadDir . $stem . '.webp';
        $thumbWebp = $uploadDir . $stem . '.thumb.webp';
        if (is_file($fullWebp))  s3_put($fullWebp,  'photos/' . $stem . '.webp',       'image/webp');
        if (is_file($thumbWebp)) s3_put($thumbWebp, 'photos/' . $stem . '.thumb.webp', 'image/webp');
    } else {
        $thumbWebp = $uploadDir . $stem . '.thumb.webp';
        if (is_file($thumbWebp)) s3_put($thumbWebp, 'photos/' . $stem . '.thumb.webp', 'image/webp');
    }

    $uploaded++;
    echo "OK : {$filename}\n";
    flush();

    // Update DB if profile still references local path
    foreach ($photoColumns as $col) {
        $stmt = $db->prepare("SELECT mobile FROM profiles WHERE `{$col}` = :p LIMIT 1");
        $stmt->execute([':p' => 'uploads/' . $filename]);
        $row = $stmt->fetch();
        if ($row) {
            $db->prepare("UPDATE profiles SET `{$col}` = :url WHERE mobile = :m")
               ->execute([':url' => $s3Url, ':m' => $row['mobile']]);
            echo "   DB [{$col}] → {$row['mobile']}\n";
            $dbUpdated++;
        }
    }
}

$nextOffset = $offset + $batchSize;
$done = $nextOffset >= $total;

echo "\n--- Batch done ---\n";
echo "  Uploaded : {$uploaded}\n";
echo "  DB rows  : {$dbUpdated}\n";
echo "  Errors   : {$errors}\n";

if ($done) {
    echo "\n✓ ALL FILES PROCESSED. DELETE this script from the server.\n";
} else {
    $remaining = $total - $nextOffset;
    $url = '?offset=' . $nextOffset;
    echo "\n{$remaining} files remaining.\n";
    echo '</pre><a href="' . htmlspecialchars($url) . '" style="display:inline-block;margin:16px;padding:10px 24px;background:#8B0000;color:#fff;font-size:15px;font-weight:700;text-decoration:none;border-radius:6px">Next batch →</a><pre>';
}

echo '</pre>';
