<?php
// Scans ALL files in uploads/, uploads each to S3, updates DB if a profile references it.
// Safe to re-run — skips files already in S3.
// DELETE this file after running.

declare(strict_types=1);
set_time_limit(600);
@ini_set('memory_limit', '256M');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/s3.php';

header('Content-Type: text/plain; charset=utf-8');

if (!defined('S3_ENABLED') || !S3_ENABLED) die("S3_ENABLED is false.\n");

$db         = getDB();
$uploadDir  = __DIR__ . '/api/uploads/';
$photoColumns = ['photo1', 'photo2', 'photo3', 'rasi_photo', 'amsam_photo'];

$mimeMap = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
            'gif'=>'image/gif','webp'=>'image/webp'];

// Scan originals (.jpg/.png/.gif) AND full-size .webp (skip .thumb.webp)
$files = glob($uploadDir . '*') ?: [];
$originals = array_filter($files, function($f) {
    $base = basename($f);
    if (str_ends_with($base, '.thumb.webp')) return false;
    $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg','jpeg','png','gif','webp']);
});

echo "Found " . count($originals) . " original images to process.\n\n";
flush();

$uploaded = 0;
$dbUpdated = 0;
$errors   = 0;

foreach ($originals as $absPath) {
    $filename = basename($absPath);
    $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mime     = $mimeMap[$ext] ?? 'image/jpeg';
    $stem     = pathinfo($filename, PATHINFO_FILENAME);

    // Upload original to S3
    $s3Url = s3_put($absPath, 'photos/' . $filename, $mime);
    if (!$s3Url) {
        echo "FAILED: {$filename}\n";
        $errors++;
        flush();
        continue;
    }

    // Upload WebP variants if they exist (skip if source is already .webp — already uploaded above)
    if ($ext !== 'webp') {
        $fullWebp  = $uploadDir . $stem . '.webp';
        $thumbWebp = $uploadDir . $stem . '.thumb.webp';
        if (is_file($fullWebp))  s3_put($fullWebp,  'photos/' . $stem . '.webp',       'image/webp');
        if (is_file($thumbWebp)) s3_put($thumbWebp, 'photos/' . $stem . '.thumb.webp', 'image/webp');
    } else {
        // Source is full-size .webp — also upload the thumbnail variant
        $thumbWebp = $uploadDir . $stem . '.thumb.webp';
        if (is_file($thumbWebp)) s3_put($thumbWebp, 'photos/' . $stem . '.thumb.webp', 'image/webp');
    }

    $uploaded++;
    echo "OK: {$filename} → {$s3Url}\n";
    flush();

    // Update DB: find any profile column still pointing to this file
    foreach ($photoColumns as $col) {
        $stmt = $db->prepare("SELECT mobile FROM profiles WHERE `{$col}` = :p LIMIT 1");
        $stmt->execute([':p' => 'uploads/' . $filename]);
        $row = $stmt->fetch();
        if ($row) {
            $db->prepare("UPDATE profiles SET `{$col}` = :url WHERE mobile = :m")
               ->execute([':url' => $s3Url, ':m' => $row['mobile']]);
            echo "  DB updated [{$col}] for mobile {$row['mobile']}\n";
            $dbUpdated++;
        }
    }
}

echo "\n========================================\n";
echo "DONE\n";
echo "  Uploaded to S3 : {$uploaded}\n";
echo "  DB rows updated: {$dbUpdated}\n";
echo "  Errors         : {$errors}\n";
echo "========================================\n";
echo "\nDELETE this file from the server after verifying.\n";
