<?php
// One-shot DB fix: update local photo paths → S3 URLs.
// Protected by a secret token in the query string.
// Usage: https://example.com/backend/api/fix-photo-urls.php?token=cm_fix_2024

require_once __DIR__ . '/../config.php';

$SECRET = 'cm_fix_2024';
if (($_GET['token'] ?? '') !== $SECRET) {
    http_response_code(403);
    exit('Forbidden.');
}

header('Content-Type: text/plain; charset=utf-8');
@ob_implicit_flush(true);
@ob_end_flush();

$bucket  = S3_BUCKET;
$region  = S3_REGION;
$baseUrl = "https://{$bucket}.s3.{$region}.amazonaws.com";

$db      = getDB();
$columns = ['photo1', 'photo2', 'photo3', 'rasi_photo', 'amsam_photo'];

$whereClause = implode(' OR ', array_map(
    fn($c) => "(`{$c}` IS NOT NULL AND `{$c}` != '' AND `{$c}` NOT LIKE 'http%' AND `{$c}` NOT LIKE 'default_%')",
    $columns
));

$rows = $db->query(
    "SELECT cp_id, mobile, " . implode(', ', $columns) . " FROM profiles WHERE {$whereClause}"
)->fetchAll();

$total = count($rows);
echo "Profiles with local photo paths: {$total}\n\n";

$updated = 0;
foreach ($rows as $row) {
    $cpId   = $row['cp_id'];
    $mobile = $row['mobile'];

    foreach ($columns as $col) {
        $val = trim($row[$col] ?? '');
        if (!$val || str_starts_with($val, 'http') || str_starts_with($val, 'default_')) {
            continue;
        }

        // Strip any leading uploads/ or uploads\ prefix to get bare filename
        $bare  = ltrim(preg_replace('#^uploads[/\\\\]#i', '', $val), '/\\');
        $s3Url = $baseUrl . '/photos/' . $bare;

        $db->prepare("UPDATE profiles SET `{$col}` = :u WHERE mobile = :m AND `{$col}` = :old")
           ->execute([':u' => $s3Url, ':m' => $mobile, ':old' => $val]);

        echo "[{$cpId}] {$col}: {$val}  =>  {$s3Url}\n";
        $updated++;
    }

    @ob_flush(); flush();
}

echo "\n--- Done ---\n";
echo "Profiles found : {$total}\n";
echo "Columns updated: {$updated}\n";
