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

// Patch s3_put to expose the raw response for debugging
$bucket   = S3_BUCKET;
$region   = S3_REGION;
$awsKey   = AWS_KEY;
$awsSec   = AWS_SECRET;
$host     = "{$bucket}.s3.{$region}.amazonaws.com";
$s3Key    = 'photos/s3-test-' . date('Ymd-His') . '.jpg';
$uri      = '/' . $s3Key;
$datetime = gmdate('Ymd\THis\Z');
$date     = substr($datetime, 0, 8);
$body     = file_get_contents($tmpFile);
@unlink($tmpFile);
$bodyHash = hash('sha256', $body);

$hdrs = [
    'content-type'         => 'image/jpeg',
    'host'                 => $host,
    'x-amz-acl'            => 'public-read',
    'x-amz-content-sha256' => $bodyHash,
    'x-amz-date'           => $datetime,
];
$canonicalHdrs = $signedList = '';
foreach ($hdrs as $k => $v) { $canonicalHdrs .= "$k:$v\n"; $signedList .= "$k;"; }
$signedList = rtrim($signedList, ';');
$canonicalReq = "PUT\n$uri\n\n$canonicalHdrs\n$signedList\n$bodyHash";
$credScope    = "$date/$region/s3/aws4_request";
$sts = "AWS4-HMAC-SHA256\n$datetime\n$credScope\n" . hash('sha256', $canonicalReq);
$sigKey = hash_hmac('sha256','aws4_request',hash_hmac('sha256','s3',hash_hmac('sha256',$region,hash_hmac('sha256',$date,'AWS4'.$awsSec,true),true),true),true);
$sig = hash_hmac('sha256', $sts, $sigKey);
$auth = "AWS4-HMAC-SHA256 Credential={$awsKey}/{$credScope}, SignedHeaders={$signedList}, Signature={$sig}";

$curlHdrs = ["Authorization: $auth"];
foreach ($hdrs as $k => $v) $curlHdrs[] = "$k: $v";

$ch = curl_init("https://$host$uri");
curl_setopt_array($ch, [CURLOPT_CUSTOMREQUEST=>'PUT', CURLOPT_POSTFIELDS=>$body,
    CURLOPT_HTTPHEADER=>$curlHdrs, CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>30]);
$response = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

echo "HTTP Code : $httpCode\n";
if ($curlErr) echo "cURL Error: $curlErr\n";
echo "Response  : $response\n\n";

if ($httpCode === 200) {
    echo "SUCCESS!\nURL: https://$host$uri\n";
} else {
    echo "FAILED.\n";
}
