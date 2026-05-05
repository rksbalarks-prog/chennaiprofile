<?php
// One-time S3 upload test. DELETE this file after testing.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/s3.php';

header('Content-Type: text/plain; charset=utf-8');

if (!defined('S3_ENABLED') || !S3_ENABLED) {
    die("S3_ENABLED is false — check config.production.php on the server.\n");
}

// Create a tiny 100x100 test image in memory
$img = imagecreatetruecolor(100, 100);
$bg  = imagecolorallocate($img, 255, 140, 0);
$txt = imagecolorallocate($img, 255, 255, 255);
imagefill($img, 0, 0, $bg);
imagestring($img, 5, 10, 40, 'S3 TEST', $txt);

$tmpFile = sys_get_temp_dir() . '/s3test_' . uniqid() . '.jpg';
imagejpeg($img, $tmpFile, 90);
imagedestroy($img);

echo "Uploading test image to S3...\n";
echo "Bucket : " . S3_BUCKET . "\n";
echo "Region : " . S3_REGION . "\n\n";

$url = s3_put($tmpFile, 'photos/s3-test-' . date('Ymd-His') . '.jpg', 'image/jpeg');
@unlink($tmpFile);

if ($url) {
    echo "SUCCESS!\n";
    echo "URL: " . $url . "\n";
} else {
    echo "FAILED — check server error logs.\n";
}
