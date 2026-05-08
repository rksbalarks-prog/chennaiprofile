<?php
// One-off migration: upload existing local profile photos to S3 and update DB.
// Run from browser (admin session) or CLI: php migrate-photos-to-s3.php
// Idempotent — skips photos that already have an https:// S3 URL in the DB.

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/image-utils.php';
require_once __DIR__ . '/s3.php';

// Localhost or CLI only
if (PHP_SAPI !== 'cli') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
        http_response_code(403);
        exit('Localhost only.');
    }
    header('Content-Type: text/plain; charset=utf-8');
    @ob_implicit_flush(true);
}

if (!defined('S3_ENABLED') || !S3_ENABLED) {
    exit("ERROR: S3_ENABLED is false. Enable S3 in config.php first.\n");
}

@set_time_limit(0);
@ini_set('memory_limit', '512M');

// All possible local upload directories (checked in order)
$uploadDirs = [
    __DIR__ . '/uploads/',                                    // ChennaiMatrimony/backend/api/uploads/
    __DIR__ . '/../../uploads/',                              // ChennaiMatrimony/uploads/
    'C:/xampp/htdocs/matrimony/backend/api/uploads/',         // matrimony project uploads
    'C:/xampp/htdocs/matrimony/uploads/',                     // matrimony root uploads
];
$db = getDB();

$columns = ['photo1', 'photo2', 'photo3', 'rasi_photo', 'amsam_photo'];

$stats = [
    'checked'   => 0,
    'uploaded'  => 0,
    'skipped'   => 0,  // already S3
    'missing'   => 0,  // file not found on disk
    'failed'    => 0,
];

echo "Starting S3 migration …\n";
echo "Bucket: " . S3_BUCKET . " / Region: " . S3_REGION . "\n\n";

// Fetch all profiles that have at least one local photo
$placeholders = implode(',', array_fill(0, count($columns), '?'));
$whereClause  = implode(' OR ', array_map(fn($c) => "`{$c}` IS NOT NULL AND `{$c}` != '' AND `{$c}` NOT LIKE 'http%' AND `{$c}` NOT LIKE 'default_%'", $columns));
$rows = $db->query("SELECT cp_id, mobile, " . implode(', ', $columns) . " FROM profiles WHERE {$whereClause}")->fetchAll();

echo "Profiles with local photos: " . count($rows) . "\n\n";

foreach ($rows as $row) {
    $cpId   = $row['cp_id'];
    $mobile = $row['mobile'];

    foreach ($columns as $col) {
        $val = $row[$col] ?? '';
        if (!$val || str_starts_with($val, 'http') || str_starts_with($val, 'default_')) {
            if ($val && str_starts_with($val, 'http')) $stats['skipped']++;
            continue;
        }

        $stats['checked']++;

        // Strip leading uploads/ prefix to get bare filename, then strip extension for stem
        $bare = ltrim(preg_replace('/^uploads\//', '', $val), '/');
        $stem = preg_replace('/\.(thumb\.webp|webp|jpe?g|png|gif)$/i', '', $bare);

        $origPath  = null;
        $webpPath  = null;
        $thumbPath = null;

        foreach ($uploadDirs as $dir) {
            // Priority 1: find original image file
            foreach (['jpg', 'jpeg', 'png', 'gif', 'JPG', 'JPEG', 'PNG'] as $ext) {
                $c = $dir . $stem . '.' . $ext;
                if (is_file($c)) { $origPath = $c; break 2; }
            }
        }

        if (!$origPath) {
            // Priority 2: find webp variant (originals deleted after webp conversion)
            foreach ($uploadDirs as $dir) {
                $fw = $dir . $stem . '.webp';
                $ft = $dir . $stem . '.thumb.webp';
                if (is_file($fw))  $webpPath  = $fw;
                if (is_file($ft))  $thumbPath = $ft;
                if ($webpPath) break;
            }
        }

        if (!$origPath && !$webpPath) {
            echo "MISS  [{$cpId}] {$col} = {$val}\n";
            $stats['missing']++;
            continue;
        }

        if ($origPath) {
            // Full pipeline: generate webp variants + upload all to S3
            try { generate_webp_variants($origPath); } catch (Throwable $e) {}
            $s3Url = s3_upload_photo($origPath);
        } else {
            // Only webp exists — upload it directly
            $s3Key = 'photos/' . $stem . '.webp';
            $s3Url = s3_put($webpPath, $s3Key, 'image/webp');
            if ($s3Url && $thumbPath) {
                s3_put($thumbPath, 'photos/' . $stem . '.thumb.webp', 'image/webp');
            }
        }

        if (!$s3Url) {
            echo "FAIL  [{$cpId}] {$col} = {$val}  (S3 upload failed)\n";
            $stats['failed']++;
            continue;
        }

        // Update DB
        $db->prepare("UPDATE profiles SET `{$col}` = :u WHERE mobile = :m")
           ->execute([':u' => $s3Url, ':m' => $mobile]);

        echo "OK    [{$cpId}] {$col} → {$s3Url}\n";
        $stats['uploaded']++;
    }

    @ob_flush();
}

echo "\n— Done —\n";
echo "Profiles scanned : " . count($rows) . "\n";
echo "Columns checked  : {$stats['checked']}\n";
echo "Uploaded to S3   : {$stats['uploaded']}\n";
echo "Already on S3    : {$stats['skipped']}\n";
echo "File not found   : {$stats['missing']}\n";
echo "Upload failed    : {$stats['failed']}\n";
