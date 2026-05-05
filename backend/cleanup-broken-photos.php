<?php
// Clears broken local uploads/ photo references from the DB.
// Only removes paths where the file doesn't exist on this server AND is not an S3 URL.
// Profiles will show default image after this.
// DELETE this file after running.

declare(strict_types=1);
set_time_limit(120);

require_once __DIR__ . '/config.php';

header('Content-Type: text/plain; charset=utf-8');

$db = getDB();
$photoColumns = ['photo1', 'photo2', 'photo3', 'rasi_photo', 'amsam_photo'];
$uploadDir    = __DIR__ . '/api/uploads/';

$cleared = 0;
$kept    = 0;
$s3kept  = 0;

$rows = $db->query(
    "SELECT mobile, photo1, photo2, photo3, rasi_photo, amsam_photo
     FROM profiles
     WHERE photo1 LIKE 'uploads/%'
        OR photo2 LIKE 'uploads/%'
        OR photo3 LIKE 'uploads/%'
        OR rasi_photo LIKE 'uploads/%'
        OR amsam_photo LIKE 'uploads/%'"
)->fetchAll();

echo "Checking " . count($rows) . " profiles...\n\n";
flush();

foreach ($rows as $row) {
    $mobile  = $row['mobile'];
    $updates = [];

    foreach ($photoColumns as $col) {
        $path = $row[$col] ?? '';
        if (!$path) continue;

        if (str_starts_with($path, 'http')) {
            $s3kept++;
            continue;
        }

        if (str_starts_with($path, 'uploads/')) {
            $filename = basename($path);
            if (is_file($uploadDir . $filename)) {
                $kept++;
                continue;
            }
            // File missing — clear it
            $updates[$col] = null;
            $cleared++;
        }
    }

    if (!empty($updates)) {
        $setParts = implode(', ', array_map(fn($c) => "`$c` = :{$c}", array_keys($updates)));
        $params   = [':mobile' => $mobile];
        foreach ($updates as $col => $val) $params[":$col"] = $val;
        $db->prepare("UPDATE profiles SET {$setParts} WHERE mobile = :mobile")->execute($params);
    }
}

echo "========================================\n";
echo "DONE\n";
echo "  Cleared (broken paths) : {$cleared}\n";
echo "  Kept (file exists)     : {$kept}\n";
echo "  Kept (S3 URLs)         : {$s3kept}\n";
echo "========================================\n";
echo "\nDELETE this file from the server after verifying.\n";
