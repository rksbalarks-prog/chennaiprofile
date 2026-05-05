<?php
// One-time S3 migration: uploads all local images to S3 and updates DB URLs.
// DELETE this file after running.
// Access: https://yoursite.com/backend/migrate-s3.php
// Large runs: refresh to continue (script skips already-migrated S3 URLs).

declare(strict_types=1);
set_time_limit(300);
@ini_set('memory_limit', '256M');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/api/s3.php';

header('Content-Type: text/plain; charset=utf-8');

if (!defined('S3_ENABLED') || !S3_ENABLED) {
    die("S3_ENABLED is false — check config.php.\n");
}

$db = getDB();

$photoColumns = ['photo1', 'photo2', 'photo3', 'rasi_photo', 'amsam_photo'];
$uploadDir    = __DIR__ . '/api/uploads/';

// Also check root-level uploads/ folder
$rootUploadDir = dirname(__DIR__) . '/uploads/';

$total     = 0;
$uploaded  = 0;
$skipped   = 0;
$missing   = 0;
$errors    = 0;

// Fetch all profiles with at least one local photo
$placeholders = implode(',', array_map(fn($c) => "`$c` LIKE 'uploads/%'", $photoColumns));
$rows = $db->query("SELECT cp_id, mobile, photo1, photo2, photo3, rasi_photo, amsam_photo
                    FROM profiles
                    WHERE photo1 LIKE 'uploads/%'
                       OR photo2 LIKE 'uploads/%'
                       OR photo3 LIKE 'uploads/%'
                       OR rasi_photo LIKE 'uploads/%'
                       OR amsam_photo LIKE 'uploads/%'")->fetchAll();

echo "Found " . count($rows) . " profiles with local image paths.\n\n";
flush();

foreach ($rows as $row) {
    $cpId   = $row['cp_id'];
    $mobile = $row['mobile'];
    $updates = [];

    foreach ($photoColumns as $col) {
        $localPath = $row[$col] ?? '';
        if (!$localPath || !str_starts_with($localPath, 'uploads/')) continue;

        $total++;
        $filename = basename($localPath);

        // Try both upload dirs
        $absPath = null;
        foreach ([$uploadDir . $filename, $rootUploadDir . $filename] as $candidate) {
            if (is_file($candidate)) { $absPath = $candidate; break; }
        }

        if (!$absPath) {
            echo "  [{$cpId}] {$col}: MISSING file {$filename}\n";
            $missing++;
            flush();
            continue;
        }

        // Upload original to S3
        $ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
                 'gif'=>'image/gif','webp'=>'image/webp'][$ext] ?? 'image/jpeg';

        $s3Url = s3_put($absPath, 'photos/' . $filename, $mime);
        if (!$s3Url) {
            echo "  [{$cpId}] {$col}: UPLOAD FAILED for {$filename}\n";
            $errors++;
            flush();
            continue;
        }

        // Upload WebP variants if they exist (don't delete originals — server keeps them as fallback)
        $stem      = pathinfo($absPath, PATHINFO_DIRNAME) . '/' . pathinfo($filename, PATHINFO_FILENAME);
        $fullWebp  = $stem . '.webp';
        $thumbWebp = $stem . '.thumb.webp';
        if (is_file($fullWebp))  s3_put($fullWebp,  'photos/' . pathinfo($filename, PATHINFO_FILENAME) . '.webp',       'image/webp');
        if (is_file($thumbWebp)) s3_put($thumbWebp, 'photos/' . pathinfo($filename, PATHINFO_FILENAME) . '.thumb.webp', 'image/webp');

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

// Also migrate any proof images in public.php uploads (payment proofs etc.)
echo "\n--- Scanning for other uploads/% paths in profiles ---\n";

echo "\n========================================\n";
echo "DONE\n";
echo "  Total photos : {$total}\n";
echo "  Uploaded     : {$uploaded}\n";
echo "  Missing file : {$missing}\n";
echo "  Errors       : {$errors}\n";
echo "  Skipped      : {$skipped}\n";
echo "========================================\n";
echo "\nDELETE this file from the server after verifying.\n";
