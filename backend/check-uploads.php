<?php
// Temporary diagnostic — DELETE after use
$dir = __DIR__ . '/api/uploads/';
$files = glob($dir . '*') ?: [];
$jpg = $png = $gif = $webp = $thumb = $other = 0;
foreach ($files as $f) {
    $b = basename($f);
    if (str_ends_with($b, '.thumb.webp')) { $thumb++; continue; }
    $ext = strtolower(pathinfo($b, PATHINFO_EXTENSION));
    if ($ext === 'jpg' || $ext === 'jpeg') $jpg++;
    elseif ($ext === 'png') $png++;
    elseif ($ext === 'gif') $gif++;
    elseif ($ext === 'webp') $webp++;
    else $other++;
}
echo "uploads/ file counts:\n";
echo "  .jpg/.jpeg : $jpg\n";
echo "  .png       : $png\n";
echo "  .gif       : $gif\n";
echo "  .webp (full): $webp\n";
echo "  .thumb.webp: $thumb\n";
echo "  other      : $other\n";
echo "  TOTAL      : " . count($files) . "\n";
