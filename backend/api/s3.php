<?php
// Minimal AWS S3 uploader — no SDK/Composer required.
// Uses AWS Signature V4 over HTTPS PUT.
// Requires config constants: S3_BUCKET, S3_REGION, AWS_KEY, AWS_SECRET.
// Optionally S3_CDN_URL overrides the returned URL base (e.g. CloudFront).

declare(strict_types=1);

function s3_put(string $localPath, string $s3Key, string $contentType = 'application/octet-stream'): ?string {
    if (!defined('S3_ENABLED') || !S3_ENABLED) return null;

    $bucket   = S3_BUCKET;
    $region   = S3_REGION;
    $awsKey   = AWS_KEY;
    $awsSec   = AWS_SECRET;
    $host     = "{$bucket}.s3.{$region}.amazonaws.com";
    $uri      = '/' . ltrim($s3Key, '/');
    $datetime = gmdate('Ymd\THis\Z');
    $date     = substr($datetime, 0, 8);
    $body     = file_get_contents($localPath);
    if ($body === false) return null;
    $bodyHash = hash('sha256', $body);

    // Headers that will be signed (must be sorted by lowercase name)
    $hdrs = [
        'content-type'        => $contentType,
        'host'                => $host,
        'x-amz-acl'           => 'public-read',
        'x-amz-content-sha256'=> $bodyHash,
        'x-amz-date'          => $datetime,
    ];

    $canonicalHdrs  = '';
    $signedHdrsList = '';
    foreach ($hdrs as $k => $v) {
        $canonicalHdrs  .= $k . ':' . $v . "\n";
        $signedHdrsList .= $k . ';';
    }
    $signedHdrsList = rtrim($signedHdrsList, ';');

    $canonicalRequest = "PUT\n{$uri}\n\n{$canonicalHdrs}\n{$signedHdrsList}\n{$bodyHash}";
    $credScope        = "{$date}/{$region}/s3/aws4_request";
    $stringToSign     = "AWS4-HMAC-SHA256\n{$datetime}\n{$credScope}\n" . hash('sha256', $canonicalRequest);

    $sigKey = hash_hmac('sha256', 'aws4_request',
        hash_hmac('sha256', 's3',
            hash_hmac('sha256', $region,
                hash_hmac('sha256', $date, 'AWS4' . $awsSec, true),
            true),
        true),
    true);
    $signature = hash_hmac('sha256', $stringToSign, $sigKey);

    $authHeader = "AWS4-HMAC-SHA256 Credential={$awsKey}/{$credScope}, SignedHeaders={$signedHdrsList}, Signature={$signature}";

    $curlHdrs = ["Authorization: {$authHeader}"];
    foreach ($hdrs as $k => $v) {
        $curlHdrs[] = "{$k}: {$v}";
    }

    $ch = curl_init("https://{$host}{$uri}");
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'PUT',
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => $curlHdrs,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        if (function_exists('log_error')) {
            log_error('s3_put failed', ['key' => $s3Key, 'http' => $httpCode]);
        }
        return null;
    }

    $base = defined('S3_CDN_URL') && S3_CDN_URL
        ? rtrim(S3_CDN_URL, '/')
        : "https://{$host}";

    return $base . $uri;
}

function s3_delete(string $s3Key): bool {
    if (!defined('S3_ENABLED') || !S3_ENABLED) return false;

    $bucket   = S3_BUCKET;
    $region   = S3_REGION;
    $awsKey   = AWS_KEY;
    $awsSec   = AWS_SECRET;
    $host     = "{$bucket}.s3.{$region}.amazonaws.com";
    $uri      = '/' . ltrim($s3Key, '/');
    $datetime = gmdate('Ymd\THis\Z');
    $date     = substr($datetime, 0, 8);
    $bodyHash = hash('sha256', '');

    $hdrs = [
        'host'                 => $host,
        'x-amz-content-sha256' => $bodyHash,
        'x-amz-date'           => $datetime,
    ];

    $canonicalHdrs = $signedHdrsList = '';
    foreach ($hdrs as $k => $v) {
        $canonicalHdrs  .= $k . ':' . $v . "\n";
        $signedHdrsList .= $k . ';';
    }
    $signedHdrsList = rtrim($signedHdrsList, ';');

    $canonicalRequest = "DELETE\n{$uri}\n\n{$canonicalHdrs}\n{$signedHdrsList}\n{$bodyHash}";
    $credScope        = "{$date}/{$region}/s3/aws4_request";
    $stringToSign     = "AWS4-HMAC-SHA256\n{$datetime}\n{$credScope}\n" . hash('sha256', $canonicalRequest);

    $sigKey = hash_hmac('sha256', 'aws4_request',
        hash_hmac('sha256', 's3',
            hash_hmac('sha256', $region,
                hash_hmac('sha256', $date, 'AWS4' . $awsSec, true),
            true),
        true),
    true);
    $signature = hash_hmac('sha256', $stringToSign, $sigKey);

    $authHeader = "AWS4-HMAC-SHA256 Credential={$awsKey}/{$credScope}, SignedHeaders={$signedHdrsList}, Signature={$signature}";

    $ch = curl_init("https://{$host}{$uri}");
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_HTTPHEADER     => [
            "Authorization: {$authHeader}",
            "host: {$host}",
            "x-amz-content-sha256: {$bodyHash}",
            "x-amz-date: {$datetime}",
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 204 || $httpCode === 200;
}

/**
 * Delete a photo and its WebP variants from S3.
 * Accepts a full S3 URL (https://...). Silently skips local/empty/default values.
 */
function s3_delete_photo(string $dbValue): void {
    if (!$dbValue || str_starts_with($dbValue, 'default_') || str_starts_with($dbValue, 'uploads/')) return;
    if (!str_starts_with($dbValue, 'http')) return;

    $path = parse_url($dbValue, PHP_URL_PATH);
    if (!$path) return;
    $key    = ltrim($path, '/');
    $dir    = dirname($key);
    $stem   = pathinfo(basename($key), PATHINFO_FILENAME);
    $prefix = ($dir && $dir !== '.') ? $dir . '/' : '';

    s3_delete($key);
    s3_delete($prefix . $stem . '.webp');
    s3_delete($prefix . $stem . '.thumb.webp');
}

/**
 * Upload the original file and its WebP variants to S3.
 * Returns the S3 URL of the original, or null on failure.
 * Deletes local files after successful upload.
 */
function s3_upload_photo(string $absPath): ?string {
    $dir      = dirname($absPath);
    $basename = pathinfo($absPath, PATHINFO_BASENAME);
    $stem     = pathinfo($absPath, PATHINFO_FILENAME);
    $ext      = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

    $mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                'gif' => 'image/gif', 'webp' => 'image/webp'];
    $mime = $mimeMap[$ext] ?? 'image/jpeg';

    $url = s3_put($absPath, 'photos/' . $basename, $mime);
    if (!$url) return null;

    $fullWebp  = $dir . '/' . $stem . '.webp';
    $thumbWebp = $dir . '/' . $stem . '.thumb.webp';
    if (is_file($fullWebp))  s3_put($fullWebp,  'photos/' . $stem . '.webp',       'image/webp');
    if (is_file($thumbWebp)) s3_put($thumbWebp, 'photos/' . $stem . '.thumb.webp', 'image/webp');

    // Clean up local temp files
    @unlink($absPath);
    if (is_file($fullWebp))  @unlink($fullWebp);
    if (is_file($thumbWebp)) @unlink($thumbWebp);

    return $url;
}
