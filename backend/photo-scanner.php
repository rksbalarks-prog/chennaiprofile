<?php
/**
 * Photo Scanner - Detect non-face/non-person images in uploads
 * Checks: file size, dimensions, aspect ratio, color variance, dominant colors
 * Flags: horoscope charts, documents, blank images, logos, too-small images
 */
require_once __DIR__ . '/admin-config.php';
adminSession();

$uploadsDir = __DIR__ . '/api/uploads/';
$action = $_GET['action'] ?? 'scan';
$db = getDB();

if ($action === 'delete' && isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $path = $uploadsDir . $file;
    if (file_exists($path)) {
        unlink($path);
        // Clear from profiles
        foreach (['photo1','photo2','photo3','rasi_photo','amsam_photo'] as $col) {
            $db->prepare("UPDATE profiles SET $col = NULL WHERE $col = :f OR $col = :f2")
               ->execute([':f' => $file, ':f2' => 'uploads/' . $file]);
        }
        header('Location: photo-scanner.php?msg=Deleted+' . urlencode($file));
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Photo Scanner</title>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:Arial,sans-serif; background:#f5f5f5; padding:20px; font-size:13px; }
h1 { font-size:20px; color:#8B0000; margin-bottom:4px; }
.sub { font-size:12px; color:#999; margin-bottom:16px; }
.stats { display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
.stat { background:#fff; border-radius:10px; padding:12px 16px; border:1px solid #e0e0e0; text-align:center; min-width:100px; }
.stat-num { font-size:20px; font-weight:800; }
.stat-label { font-size:10px; color:#999; margin-top:2px; }
.grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:10px; }
.card { background:#fff; border-radius:10px; overflow:hidden; border:1px solid #e0e0e0; position:relative; }
.card img { width:100%; height:150px; object-fit:cover; display:block; }
.card-body { padding:8px 10px; }
.card-name { font-size:11px; font-weight:700; color:#333; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.card-info { font-size:10px; color:#999; margin-top:2px; }
.card-flag { position:absolute; top:6px; left:6px; font-size:9px; font-weight:700; padding:2px 8px; border-radius:10px; }
.flag-ok { background:#dcfce7; color:#16a34a; }
.flag-warn { background:#fef3c7; color:#92400e; }
.flag-bad { background:#fee2e2; color:#dc2626; }
.card-actions { display:flex; gap:4px; margin-top:6px; }
.card-actions a, .card-actions button { font-size:10px; padding:3px 8px; border-radius:4px; border:1px solid #e0e0e0; background:#fff; color:#666; text-decoration:none; cursor:pointer; }
.card-actions .del { background:#fee2e2; color:#dc2626; border-color:#fecaca; }
.tabs { display:flex; gap:6px; margin-bottom:14px; }
.tab { padding:7px 16px; border-radius:20px; font-size:12px; font-weight:600; cursor:pointer; border:1.5px solid #e0e0e0; background:#fff; color:#666; text-decoration:none; }
.tab.active { background:#8B0000; color:#fff; border-color:#8B0000; }
.msg { background:#dcfce7; color:#16a34a; padding:10px 14px; border-radius:8px; margin-bottom:14px; font-weight:600; }
</style>
</head>
<body>
<h1>Photo Scanner</h1>
<div class="sub">Scan uploaded images — detect non-face/suspicious photos</div>

<?php if (isset($_GET['msg'])): ?>
<div class="msg"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<?php
$files = glob($uploadsDir . '*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,webp}', GLOB_BRACE);
$total = count($files);
$results = ['ok'=>[], 'warn'=>[], 'bad'=>[]];

// Get all profile photo references
$profilePhotos = [];
foreach (['photo1','photo2','photo3','rasi_photo','amsam_photo'] as $col) {
    $rows = $db->query("SELECT cp_id, name, $col as photo FROM profiles WHERE $col IS NOT NULL AND $col != ''")->fetchAll();
    foreach ($rows as $r) {
        $fn = basename($r['photo']);
        $profilePhotos[$fn] = ['cp_id'=>$r['cp_id'], 'name'=>$r['name'], 'col'=>$col];
    }
}

foreach ($files as $filepath) {
    $filename = basename($filepath);
    $size = filesize($filepath);
    $flags = [];
    $status = 'ok';

    // Skip very small files
    if ($size < 1024) { $flags[] = 'Tiny file (<1KB)'; $status = 'bad'; }

    // Check dimensions
    $info = @getimagesize($filepath);
    $w = $info[0] ?? 0;
    $h = $info[1] ?? 0;

    if ($w === 0 || $h === 0) { $flags[] = 'Invalid image'; $status = 'bad'; }
    elseif ($w < 50 || $h < 50) { $flags[] = 'Too small ('.$w.'x'.$h.')'; $status = 'bad'; }
    elseif ($w < 100 || $h < 100) { $flags[] = 'Very small ('.$w.'x'.$h.')'; $status = 'warn'; }

    // Check aspect ratio (extreme ratios = likely not a portrait)
    if ($w > 0 && $h > 0) {
        $ratio = $w / $h;
        if ($ratio > 3 || $ratio < 0.2) { $flags[] = 'Extreme ratio ('.round($ratio,1).')'; $status = 'bad'; }
        elseif ($ratio > 2.2) { $flags[] = 'Wide image ('.round($ratio,1).')'; if($status!='bad') $status = 'warn'; }
    }

    // Check if it's a horoscope chart (rasi/amsam) — check filename patterns
    $lname = strtolower($filename);
    if (preg_match('/rasi|amsam|chart|horoscope|jathak/i', $lname)) {
        $flags[] = 'Horoscope filename';
        if($status!='bad') $status = 'warn';
    }

    // Check if linked to rasi_photo or amsam_photo column
    if (isset($profilePhotos[$filename]) && in_array($profilePhotos[$filename]['col'], ['rasi_photo','amsam_photo'])) {
        $flags[] = 'Horoscope chart ('.$profilePhotos[$filename]['col'].')';
        if($status!='bad') $status = 'warn';
    }

    // Check color variance (low variance = blank/solid color)
    if ($w > 0 && $h > 0 && $size > 1024 && function_exists('imagecreatefromjpeg')) {
        try {
            $img = null;
            $ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg'])) $img = @imagecreatefromjpeg($filepath);
            elseif ($ext === 'png') $img = @imagecreatefrompng($filepath);
            elseif ($ext === 'gif') $img = @imagecreatefromgif($filepath);
            elseif ($ext === 'webp') $img = @imagecreatefromwebp($filepath);

            if ($img) {
                // Sample colors
                $colors = [];
                $step = max(1, (int)($w / 10));
                $stepy = max(1, (int)($h / 10));
                for ($x = 0; $x < $w; $x += $step) {
                    for ($y = 0; $y < $h; $y += $stepy) {
                        $rgb = @imagecolorat($img, $x, $y);
                        $r = ($rgb >> 16) & 0xFF;
                        $g = ($rgb >> 8) & 0xFF;
                        $b = $rgb & 0xFF;
                        $colors[] = [$r, $g, $b];
                    }
                }
                imagedestroy($img);

                // Check variance
                if (count($colors) > 5) {
                    $avgR = array_sum(array_column($colors, 0)) / count($colors);
                    $avgG = array_sum(array_column($colors, 1)) / count($colors);
                    $avgB = array_sum(array_column($colors, 2)) / count($colors);
                    $variance = 0;
                    foreach ($colors as $c) {
                        $variance += pow($c[0]-$avgR,2) + pow($c[1]-$avgG,2) + pow($c[2]-$avgB,2);
                    }
                    $variance = sqrt($variance / count($colors));

                    if ($variance < 10) { $flags[] = 'Solid color (var:'.round($variance).')'; $status = 'bad'; }
                    elseif ($variance < 25) { $flags[] = 'Low color variety'; if($status!='bad') $status = 'warn'; }

                    // Check if mostly white/document-like
                    if ($avgR > 230 && $avgG > 230 && $avgB > 230 && $variance < 40) {
                        $flags[] = 'Mostly white/blank';
                        if($status!='bad') $status = 'warn';
                    }
                }
            }
        } catch (Exception $e) {}
    }

    // Check if file is not used by any profile
    if (!isset($profilePhotos[$filename])) {
        $flags[] = 'Orphan (no profile link)';
        if($status!='bad') $status = 'warn';
    }

    if (empty($flags)) $flags[] = 'Looks OK';

    $results[$status][] = [
        'file' => $filename,
        'size' => $size,
        'w' => $w, 'h' => $h,
        'flags' => $flags,
        'profile' => $profilePhotos[$filename] ?? null,
    ];
}

$filter = $_GET['filter'] ?? 'bad';
?>

<div class="stats">
    <div class="stat"><div class="stat-num"><?= $total ?></div><div class="stat-label">Total Files</div></div>
    <div class="stat"><div class="stat-num" style="color:#16a34a"><?= count($results['ok']) ?></div><div class="stat-label">OK</div></div>
    <div class="stat"><div class="stat-num" style="color:#d97706"><?= count($results['warn']) ?></div><div class="stat-label">Warning</div></div>
    <div class="stat"><div class="stat-num" style="color:#dc2626"><?= count($results['bad']) ?></div><div class="stat-label">Bad / Remove</div></div>
</div>

<div class="tabs">
    <a href="?filter=bad" class="tab <?= $filter==='bad'?'active':'' ?>">Bad (<?= count($results['bad']) ?>)</a>
    <a href="?filter=warn" class="tab <?= $filter==='warn'?'active':'' ?>">Warning (<?= count($results['warn']) ?>)</a>
    <a href="?filter=ok" class="tab <?= $filter==='ok'?'active':'' ?>">OK (<?= count($results['ok']) ?>)</a>
    <a href="?filter=all" class="tab <?= $filter==='all'?'active':'' ?>">All (<?= $total ?>)</a>
</div>

<div class="grid">
<?php
$show = $filter === 'all' ? array_merge($results['bad'], $results['warn'], $results['ok']) : ($results[$filter] ?? []);
foreach (array_slice($show, 0, 200) as $item):
    $flagClass = in_array($item, $results['bad']) ? 'flag-bad' : (in_array($item, $results['warn']) ? 'flag-warn' : 'flag-ok');
    $flagLabel = in_array($item, $results['bad']) ? 'BAD' : (in_array($item, $results['warn']) ? 'WARN' : 'OK');
?>
<div class="card">
    <img src="api/uploads/<?= htmlspecialchars($item['file']) ?>" alt="<?= htmlspecialchars($item['file']) ?>" loading="lazy"
         onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22180%22 height=%22150%22><rect fill=%22%23f0f0f0%22 width=%22180%22 height=%22150%22/><text x=%2290%22 y=%2275%22 text-anchor=%22middle%22 fill=%22%23ccc%22 font-size=%2212%22>Error</text></svg>'">
    <div class="card-flag <?= $flagClass ?>"><?= $flagLabel ?></div>
    <div class="card-body">
        <div class="card-name" title="<?= htmlspecialchars($item['file']) ?>"><?= htmlspecialchars($item['file']) ?></div>
        <div class="card-info">
            <?= round($item['size']/1024) ?>KB · <?= $item['w'] ?>x<?= $item['h'] ?>
            <?php if ($item['profile']): ?>
                · <strong><?= htmlspecialchars($item['profile']['cp_id']) ?></strong>
            <?php endif; ?>
        </div>
        <div class="card-info" style="color:<?= $flagClass==='flag-bad'?'#dc2626':($flagClass==='flag-warn'?'#d97706':'#16a34a') ?>">
            <?= implode(' · ', $item['flags']) ?>
        </div>
        <div class="card-actions">
            <?php if ($item['profile']): ?>
                <span style="font-size:10px;color:#666"><?= htmlspecialchars($item['profile']['name']) ?> (<?= $item['profile']['col'] ?>)</span>
            <?php endif; ?>
            <a href="?action=delete&file=<?= urlencode($item['file']) ?>" class="del" onclick="return confirm('Delete <?= htmlspecialchars($item['file']) ?>?')">Delete</a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php if (count($show) > 200): ?>
<p style="text-align:center;padding:20px;color:#999">Showing first 200 of <?= count($show) ?></p>
<?php endif; ?>

</body>
</html>
