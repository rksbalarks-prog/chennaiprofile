<?php
/**
 * Fix photo1 column: scan uploads/ folder for files matching cp_id
 * and update DB where photo1 is default/null but actual file exists.
 */
require_once __DIR__ . '/../config.php';
$db = getDB();

$uploadsDir = __DIR__ . '/uploads/';
$extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'JPG', 'JPEG', 'PNG'];

// Get all profiles with default or missing photo1
$stmt = $db->query("SELECT cp_id, photo1 FROM profiles");
$rows = $stmt->fetchAll();

$updated = 0;
$alreadyOk = 0;
$noFile = 0;

foreach ($rows as $r) {
    $cpId = $r['cp_id'];
    $currentPhoto = trim($r['photo1'] ?? '');

    // If photo1 already points to a real file (not default), check if it exists
    if ($currentPhoto && strpos($currentPhoto, 'default_') !== 0) {
        // Check if the referenced file exists
        $path = $uploadsDir . basename($currentPhoto);
        if (strpos($currentPhoto, 'uploads/') === 0) {
            $path = __DIR__ . '/' . $currentPhoto;
        }
        if (file_exists($path)) {
            $alreadyOk++;
            continue;
        }
        // File referenced but doesn't exist - try to find by cp_id
    }

    // Try to find a file matching this cp_id in uploads/
    $found = null;
    foreach ($extensions as $ext) {
        // Try: F12007.jpg, F12007t1234.jpg, PM081082t1775027884.jpg
        $pattern = $uploadsDir . $cpId . '*.' . $ext;
        $matches = glob($pattern);
        if ($matches && count($matches) > 0) {
            // Use the first match (or largest file)
            $best = $matches[0];
            $bestSize = filesize($best);
            foreach ($matches as $m) {
                $sz = filesize($m);
                if ($sz > $bestSize) { $best = $m; $bestSize = $sz; }
            }
            $found = 'uploads/' . basename($best);
            break;
        }
    }

    if ($found) {
        $upd = $db->prepare("UPDATE profiles SET photo1 = :p WHERE cp_id = :c");
        $upd->execute([':p' => $found, ':c' => $cpId]);
        $updated++;
        if ($updated <= 20) echo "UPDATED: $cpId -> $found\n";
    } else {
        $noFile++;
    }
}

echo "\nDone!\n";
echo "  Already OK (photo exists): $alreadyOk\n";
echo "  Updated (found file): $updated\n";
echo "  No file found: $noFile\n";
echo "  Total profiles: " . count($rows) . "\n";
