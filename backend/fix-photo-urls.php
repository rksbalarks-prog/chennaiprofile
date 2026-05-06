<?php
// One-shot: remap all remaining uploads/filename DB values → S3 URLs.
// DELETE this file after running.
declare(strict_types=1);
require_once __DIR__ . '/config.php';
header('Content-Type: text/plain; charset=utf-8');

$s3Base  = 'https://' . S3_BUCKET . '.s3.' . S3_REGION . '.amazonaws.com/photos/';
$db      = getDB();
$cols    = ['photo1', 'photo2', 'photo3', 'rasi_photo', 'amsam_photo'];
$updated = 0;
$skipped = 0;

foreach ($cols as $col) {
    $stmt = $db->query("SELECT mobile, `{$col}` FROM profiles WHERE `{$col}` LIKE 'uploads/%'");
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $filename = basename($row[$col]);
        $s3Url    = $s3Base . $filename;
        $db->prepare("UPDATE profiles SET `{$col}` = :u WHERE mobile = :m")
           ->execute([':u' => $s3Url, ':m' => $row['mobile']]);
        echo "OK [{$col}] {$row['mobile']} → {$filename}\n";
        $updated++;
        flush();
    }
}

// Also fix any values stored as bare filenames (no uploads/ prefix, no http)
foreach ($cols as $col) {
    $stmt = $db->query("SELECT mobile, `{$col}` FROM profiles WHERE `{$col}` IS NOT NULL AND `{$col}` != '' AND `{$col}` NOT LIKE 'http%' AND `{$col}` NOT LIKE 'uploads/%' AND `{$col}` NOT LIKE 'default_%'");
    $rows = $stmt->fetchAll();
    foreach ($rows as $row) {
        $filename = basename($row[$col]);
        $s3Url    = $s3Base . $filename;
        $db->prepare("UPDATE profiles SET `{$col}` = :u WHERE mobile = :m")
           ->execute([':u' => $s3Url, ':m' => $row['mobile']]);
        echo "OK [{$col}] {$row['mobile']} (bare) → {$filename}\n";
        $updated++;
        flush();
    }
}

echo "\nDone. Updated: {$updated}\n";
echo "DELETE this file from the server.\n";
