<?php
/**
 * One-shot helper to convert `default-male-raw.*` and `default-female-raw.*`
 * (which have black bars at top and/or bottom) into clean
 * `default-male.png` / `default-female.png`.
 *
 * Usage (CLI from project root or anywhere):
 *   /c/xampp/php/php.exe frontend/public/_crop_defaults.php
 *
 * Algorithm:
 *   1. Open raw image with GD.
 *   2. Scan rows top-to-bottom; skip rows where >= 95% of pixels are near-black
 *      (R,G,B all < 18). Stop at the first non-black row → that's the new top.
 *   3. Scan rows bottom-to-top the same way → new bottom.
 *   4. Repeat for columns (handles left/right bars too, rare for these images).
 *   5. Crop + re-save as PNG at the same folder.
 *   6. Delete the raw file after successful crop.
 */

@set_time_limit(0);
@ini_set('memory_limit', '256M');

$dir   = __DIR__ . DIRECTORY_SEPARATOR;
$pairs = ['male', 'female'];
$exts  = ['png', 'jpg', 'jpeg', 'webp'];

function loadImage(string $path): ?GdImage {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'png':           return @imagecreatefrompng($path);
        case 'jpg':
        case 'jpeg':          return @imagecreatefromjpeg($path);
        case 'webp':          return @imagecreatefromwebp($path);
        default:              return null;
    }
}

/**
 * Return the row index (inclusive) where non-black content starts.
 * A "black row" is one where >= $threshold of sampled pixels are near-black.
 */
function firstNonBlackRow(GdImage $im, int $w, int $h, bool $fromBottom = false, float $threshold = 0.95, int $darkCut = 18): int {
    $step = max(1, (int)($w / 60)); // sample ~60 columns per row
    for ($y = $fromBottom ? $h - 1 : 0; $fromBottom ? $y >= 0 : $y < $h; $fromBottom ? $y-- : $y++) {
        $blackHits = 0; $total = 0;
        for ($x = 0; $x < $w; $x += $step) {
            $rgb = imagecolorat($im, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            if ($r < $darkCut && $g < $darkCut && $b < $darkCut) $blackHits++;
            $total++;
        }
        if ($total > 0 && ($blackHits / $total) < $threshold) return $y;
    }
    return $fromBottom ? 0 : $h - 1;
}

function firstNonBlackCol(GdImage $im, int $w, int $h, bool $fromRight = false, float $threshold = 0.95, int $darkCut = 18): int {
    $step = max(1, (int)($h / 60));
    for ($x = $fromRight ? $w - 1 : 0; $fromRight ? $x >= 0 : $x < $w; $fromRight ? $x-- : $x++) {
        $blackHits = 0; $total = 0;
        for ($y = 0; $y < $h; $y += $step) {
            $rgb = imagecolorat($im, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            if ($r < $darkCut && $g < $darkCut && $b < $darkCut) $blackHits++;
            $total++;
        }
        if ($total > 0 && ($blackHits / $total) < $threshold) return $x;
    }
    return $fromRight ? 0 : $w - 1;
}

foreach ($pairs as $gender) {
    // Resolve raw file (any supported extension)
    $raw = null;
    foreach ($exts as $ext) {
        $p = $dir . "default-{$gender}-raw.{$ext}";
        if (is_file($p)) { $raw = $p; break; }
    }
    if (!$raw) {
        echo "[$gender] No raw file found (looked for default-{$gender}-raw.{" . implode(',', $exts) . "}). Skipping.\n";
        continue;
    }

    $im = loadImage($raw);
    if (!$im) { echo "[$gender] GD failed to load $raw — aborting.\n"; continue; }

    $w = imagesx($im); $h = imagesy($im);
    $top    = firstNonBlackRow($im, $w, $h, false);
    $bottom = firstNonBlackRow($im, $w, $h, true);
    $left   = firstNonBlackCol($im, $w, $h, false);
    $right  = firstNonBlackCol($im, $w, $h, true);
    // Safety fallback if crop detection went wrong
    if ($bottom <= $top || $right <= $left) {
        $top = 0; $bottom = $h - 1; $left = 0; $right = $w - 1;
    }
    $cropW = $right - $left + 1;
    $cropH = $bottom - $top + 1;

    echo "[$gender] raw={$w}x{$h} → crop @ top=$top bottom=$bottom left=$left right=$right  ({$cropW}x{$cropH})\n";

    $out = imagecreatetruecolor($cropW, $cropH);
    // preserve transparency for PNG output
    imagesavealpha($out, true);
    $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
    imagefill($out, 0, 0, $transparent);
    imagecopy($out, $im, 0, 0, $left, $top, $cropW, $cropH);

    $dest = $dir . "default-{$gender}.png";
    if (is_file($dest)) @unlink($dest);
    imagepng($out, $dest, 6);
    imagedestroy($out); imagedestroy($im);
    echo "[$gender] wrote $dest\n";

    // Clean up the raw once we have a good output
    @unlink($raw);

    // Also drop the stale SVG default so the PNG is the only thing the
    // frontend picks up.
    $svg = $dir . "default-{$gender}.svg";
    if (is_file($svg)) { @unlink($svg); echo "[$gender] removed legacy $svg\n"; }
}

echo "\nDone. Reload the frontend to see the new defaults.\n";
