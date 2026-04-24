// Centralized paths — auto-detects local XAMPP vs live server.
// On localhost the backend lives under /matrimony/; on the live host it's at /.
const host = typeof window !== 'undefined' ? window.location.hostname : '';
const isLocal = host === 'localhost' || host === '127.0.0.1';
const PREFIX = isLocal ? '/matrimony' : '';

export const API_BASE = `${PREFIX}/backend/api/public.php`;
export const CONTACT_API = `${PREFIX}/backend/api/contact.php`;
// Photos on both prod and local XAMPP live under /backend/api/uploads/.
// The /uploads/ path exists on local but is rewritten to index.html by the
// SPA .htaccess on production, so it is only used as the onError fallback.
export const PHOTO_BASE = `${PREFIX}/backend/api/uploads/`;
export const PHOTO_BASE_OLD = `${PREFIX}/uploads/`;
export const UPLOADS_PREFIX = `${PREFIX}/backend/api/`;
export const USER_PANEL_URL = `${PREFIX}/backend/user-panel.php`;

// Resolve variants of a photo path coming from the DB (e.g. "uploads/abc.jpg").
// Returns the absolute URLs for: WebP thumb (~400px), full WebP (~1200px), and
// the untouched original as final fallback. Old uploads without variants fall
// through to the original via <picture>'s <img> fallback.
export const getPhotoUrls = (raw) => {
  if (!raw || raw.startsWith('default_')) return null;
  const rel = raw.startsWith('uploads/') ? raw : `uploads/${raw}`;
  const orig = raw.startsWith('http') || raw.startsWith('/') ? raw : `${UPLOADS_PREFIX}${rel}`;
  const base = rel.replace(/\.(jpe?g|png|gif|webp)$/i, '');
  return {
    thumb: `${UPLOADS_PREFIX}${base}.thumb.webp`,
    full:  `${UPLOADS_PREFIX}${base}.webp`,
    orig,
  };
};
