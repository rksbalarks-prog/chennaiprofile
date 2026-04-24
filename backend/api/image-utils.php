<?php
// Image processing — creates WebP thumbnail + full-size WebP alongside the original.
// Convention:
//   original:  uploads/abc.jpg        (kept untouched as fallback)
//   full webp: uploads/abc.webp       (max 1200px, quality 82)
//   thumbnail: uploads/abc.thumb.webp (max 400px,  quality 75)
//
// Frontend uses <picture><source type="image/webp" srcSet=thumb.webp><img src=orig.jpg>
// so browsers with WebP get thumbs, others fall back to original.

function img_ext_to_mime(string $ext): ?string {
    $ext = strtolower($ext);
    return [
        'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
        'png'  => 'image/png',  'gif'  => 'image/gif',
        'webp' => 'image/webp',
    ][$ext] ?? null;
}

function img_load(string $path): ?GdImage {
    $info = @getimagesize($path);
    if (!$info) return null;
    switch ($info[2]) {
        case IMAGETYPE_JPEG: $im = @imagecreatefromjpeg($path); break;
        case IMAGETYPE_PNG:  $im = @imagecreatefrompng($path);  break;
        case IMAGETYPE_GIF:  $im = @imagecreatefromgif($path);  break;
        case IMAGETYPE_WEBP: $im = @imagecreatefromwebp($path); break;
        default: return null;
    }
    if (!$im) return null;
    // Respect EXIF orientation for JPEGs (phone photos)
    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif = @exif_read_data($path);
        $orient = $exif['Orientation'] ?? 1;
        if ($orient === 3) $im = imagerotate($im, 180, 0);
        elseif ($orient === 6) $im = imagerotate($im, -90, 0);
        elseif ($orient === 8) $im = imagerotate($im, 90, 0);
    }
    return $im;
}

function img_resize_to(GdImage $src, int $maxEdge): GdImage {
    $w = imagesx($src); $h = imagesy($src);
    if ($w <= $maxEdge && $h <= $maxEdge) return $src;
    if ($w >= $h) { $nw = $maxEdge; $nh = (int)round($h * $maxEdge / $w); }
    else          { $nh = $maxEdge; $nw = (int)round($w * $maxEdge / $h); }
    $dst = imagecreatetruecolor($nw, $nh);
    // Preserve alpha for PNGs
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    return $dst;
}

/**
 * Generate WebP variants for an uploaded image.
 * Returns ['full' => path|null, 'thumb' => path|null] (filesystem paths relative to same dir).
 * Idempotent — skips variants that already exist.
 */
function generate_webp_variants(string $absSrcPath): array {
    if (!function_exists('imagewebp')) return ['full' => null, 'thumb' => null];
    if (!is_file($absSrcPath)) return ['full' => null, 'thumb' => null];

    $dir  = dirname($absSrcPath);
    $base = pathinfo($absSrcPath, PATHINFO_FILENAME);        // "abc"
    $fullPath  = $dir . DIRECTORY_SEPARATOR . $base . '.webp';
    $thumbPath = $dir . DIRECTORY_SEPARATOR . $base . '.thumb.webp';

    $needFull  = !is_file($fullPath);
    $needThumb = !is_file($thumbPath);
    if (!$needFull && !$needThumb) {
        return ['full' => $fullPath, 'thumb' => $thumbPath];
    }

    $src = img_load($absSrcPath);
    if (!$src) return ['full' => null, 'thumb' => null];

    try {
        if ($needFull) {
            $big = img_resize_to($src, 1200);
            @imagewebp($big, $fullPath, 82);
            if ($big !== $src) imagedestroy($big);
        }
        if ($needThumb) {
            $small = img_resize_to($src, 400);
            @imagewebp($small, $thumbPath, 75);
            if ($small !== $src) imagedestroy($small);
        }
    } finally {
        imagedestroy($src);
    }

    return [
        'full'  => is_file($fullPath)  ? $fullPath  : null,
        'thumb' => is_file($thumbPath) ? $thumbPath : null,
    ];
}
