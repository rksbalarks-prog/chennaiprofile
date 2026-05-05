<?php
// Fetches missing images from kumbakonamfreematrimony.com, uploads to S3, updates DB.
// Runs entirely on chennaiprofile.in — no access to kumbakonam server needed.
// DELETE this file after running.

declare(strict_types=1);
set_time_limit(600);
@ini_set('memory_limit', '256M');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/s3.php';

header('Content-Type: text/plain; charset=utf-8');

if (!defined('S3_ENABLED') || !S3_ENABLED) {
    die("S3_ENABLED is false.\n");
}

$db = getDB();

$REMOTE_BASE = 'https://kumbakonamfreematrimony.com/backend/api/uploads/';
$photoColumns = ['photo1', 'photo2', 'photo3', 'rasi_photo', 'amsam_photo'];
$localUploadDir = __DIR__ . '/api/uploads/';

$total    = 0;
$uploaded = 0;
$missing  = 0;
$skipped  = 0;
$errors   = 0;

// Only process profiles that still have local uploads/ paths (not yet S3 URLs)
$rows = $db->query(
    "SELECT cp_id, mobile, photo1, photo2, photo3, rasi_photo, amsam_photo
     FROM profiles
     WHERE photo1 LIKE 'uploads/%'
        OR photo2 LIKE 'uploads/%'
        OR photo3 LIKE 'uploads/%'
        OR rasi_photo LIKE 'uploads/%'
        OR amsam_photo LIKE 'uploads/%'"
)->fetchAll();

echo "Found " . count($rows) . " profiles still needing migration.\n\n";
flush();

function fetch_remote(string $url): ?string {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'S3-Migrator/1.0',
    ]);
    $body = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($code === 200 && $body) ? $body : null;
}

foreach ($rows as $row) {
    $cpId   = $row['cp_id'];
    $mobile = $row['mobile'];
    $updates = [];

    foreach ($photoColumns as $col) {
        $localPath = $row[$col] ?? '';
        if (!$localPath || !str_starts_with($localPath, 'uploads/')) continue;

        $total++;
        $filename = basename($localPath);
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime     = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
                     'gif'=>'image/gif','webp'=>'image/webp'][$ext] ?? 'image/jpeg';

        // Check if file already on this server
        $absPath = $localUploadDir . $filename;
        $body    = null;

        if (is_file($absPath)) {
            $body = file_get_contents($absPath);
        } else {
            // Fetch from kumbakonam
            $body = fetch_remote($REMOTE_BASE . $filename);
            if (!$body) {
                // Try .jpg if original had different extension
                echo "  [{$cpId}] {$col}: NOT FOUND on kumbakonam — {$filename}\n";
                $missing++;
                flush();
                continue;
            }
        }

        // Write to temp file, upload to S3
        $tmpFile = sys_get_temp_dir() . '/s3mig_' . uniqid() . '.' . $ext;
        file_put_contents($tmpFile, $body);

        $s3Url = s3_put($tmpFile, 'photos/' . $filename, $mime);
        @unlink($tmpFile);

        if (!$s3Url) {
            echo "  [{$cpId}] {$col}: S3 UPLOAD FAILED — {$filename}\n";
            $errors++;
            flush();
            continue;
        }

        // Also fetch and upload WebP variants if available
        $stem = pathinfo($filename, PATHINFO_FILENAME);
        foreach (['.webp' => 'image/webp', '.thumb.webp' => 'image/webp'] as $suffix => $wMime) {
            $wFile = $stem . $suffix;
            $wAbs  = $localUploadDir . $wFile;
            $wBody = is_file($wAbs) ? file_get_contents($wAbs) : fetch_remote($REMOTE_BASE . $wFile);
            if ($wBody) {
                $wTmp = sys_get_temp_dir() . '/s3mig_' . uniqid() . '.webp';
                file_put_contents($wTmp, $wBody);
                s3_put($wTmp, 'photos/' . $wFile, $wMime);
                @unlink($wTmp);
            }
        }

        $updates[$col] = $s3Url;
        $uploaded++;
        echo "  [{$cpId}] {$col}: OK → {$s3Url}\n";
        flush();
    }

    if (!empty($updates)) {
        $setParts = implode(', ', array_map(fn($c) => "`$c` = :{$c}", array_keys($updates)));
        $params   = array_combine(
            array_map(fn($c) => ":{$c}", array_keys($updates)),
            array_values($updates)
        );
        $params[':mobile'] = $mobile;
        $db->prepare("UPDATE profiles SET {$setParts} WHERE mobile = :mobile")->execute($params);
    }
}

echo "\n========================================\n";
echo "DONE\n";
echo "  Total   : {$total}\n";
echo "  Uploaded: {$uploaded}\n";
echo "  Missing : {$missing}\n";
echo "  Errors  : {$errors}\n";
echo "========================================\n";
echo "\nDELETE this file from the server after verifying.\n";
