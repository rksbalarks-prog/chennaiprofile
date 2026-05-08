<?php
// Update DB photo columns from local filenames → S3 URLs.
// Run via CLI: php update-photo-urls.php
// Idempotent: skips rows already pointing at https:// or default_ photos.

require_once __DIR__ . '/../config.php';

if (PHP_SAPI !== 'cli') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
        http_response_code(403); exit('Localhost only.');
    }
    header('Content-Type: text/plain; charset=utf-8');
    @ob_implicit_flush(true);
}

$bucket = S3_BUCKET;
$region = S3_REGION;
$base   = "https://{$bucket}.s3.{$region}.amazonaws.com";

$db      = getDB();
$columns = ['photo1', 'photo2', 'photo3', 'rasi_photo', 'amsam_photo'];

$stats = ['updated' => 0, 'skipped' => 0, 'dry' => 0];

$whereClause = implode(' OR ', array_map(
    fn($c) => "`{$c}` IS NOT NULL AND `{$c}` != '' AND `{$c}` NOT LIKE 'http%' AND `{$c}` NOT LIKE 'default_%'",
    $columns
));

$rows = $db->query(
    "SELECT cp_id, mobile, " . implode(', ', $columns) . " FROM profiles WHERE {$whereClause}"
)->fetchAll();

echo "Profiles with local photo paths: " . count($rows) . "\n\n";

foreach ($rows as $row) {
    $cpId   = $row['cp_id'];
    $mobile = $row['mobile'];

    foreach ($columns as $col) {
        $val = trim($row[$col] ?? '');
        if (!$val || str_starts_with($val, 'http') || str_starts_with($val, 'default_')) {
            continue;
        }

        // Strip any leading uploads/ or uploads\ prefix to get bare filename
        $bare   = ltrim(preg_replace('#^uploads[/\\\\]#i', '', $val), '/\\');
        $s3Url  = $base . '/photos/' . $bare;

        $db->prepare("UPDATE profiles SET `{$col}` = :u WHERE mobile = :m AND `{$col}` = :old")
           ->execute([':u' => $s3Url, ':m' => $mobile, ':old' => $val]);

        echo "OK  [{$cpId}] {$col}: {$val} → {$s3Url}\n";
        $stats['updated']++;
    }

    @ob_flush();
}

echo "\n— Done —\n";
echo "Updated : {$stats['updated']}\n";
