// Centralized paths — auto-detects local XAMPP vs live server.
// On localhost the backend lives under /ChennaiMatrimony/; on the live host it's at /.
const host = typeof window !== 'undefined' ? window.location.hostname : '';
const isLocal = host === 'localhost' || host === '127.0.0.1';
export const PREFIX = isLocal ? '/ChennaiMatrimony' : '';

export const API_BASE = `${PREFIX}/backend/api/public.php`;
export const CONTACT_API = `${PREFIX}/backend/api/contact.php`;
// Photos on both prod and local XAMPP live under /backend/api/uploads/.
// The /uploads/ path exists on local but is rewritten to index.html by the
// SPA .htaccess on production, so it is only used as the onError fallback.
export const PHOTO_BASE = `${PREFIX}/backend/api/uploads/`;
export const PHOTO_BASE_OLD = `${PREFIX}/uploads/`;
export const UPLOADS_PREFIX = `${PREFIX}/backend/api/`;
export const USER_PANEL_URL = `${PREFIX}/backend/user-panel.php`;

// This repo IS Chennai Profile — points gate is always active (local + live).
export const IS_CHENNAI_PROFILE = true;
export const POINTS_PER_CONTACT = 10;
export const POINTS_API = `${PREFIX}/backend/api/points.php`;

// Resolve variants of a photo path coming from the DB.
// Handles S3 URLs (https://...) and legacy local paths (uploads/abc.jpg).
export const getPhotoUrls = (raw) => {
  if (!raw || raw.startsWith('default_')) return null;
  if (raw.startsWith('http')) {
    // S3 bucket only stores originals — WebP variants may not exist.
    // Use orig for all sizes; the img onError fallback handles missing variants.
    const base = raw.replace(/\.(jpe?g|png|gif|webp)$/i, '');
    const thumb = `${base}.thumb.webp`;
    const full  = `${base}.webp`;
    return { thumb, full, orig: raw };
  }
  const rel  = raw.startsWith('uploads/') ? raw : `uploads/${raw}`;
  const orig = `${UPLOADS_PREFIX}${rel}`;
  const base = rel.replace(/\.(jpe?g|png|gif|webp)$/i, '');
  return {
    thumb: `${UPLOADS_PREFIX}${base}.thumb.webp`,
    full:  `${UPLOADS_PREFIX}${base}.webp`,
    orig,
  };
};
