// Google Analytics 4 integration.
//
// Activation: set VITE_GA_ID in frontend/.env.production (or any .env Vite
// reads at build time) to your GA4 Measurement ID, e.g. "G-ABC1234567".
// When unset, the module is a no-op — zero network requests, zero gtag.js
// download. When set, gtag.js loads asynchronously and pageviews are
// tracked on every React Router navigation.

const GA_ID = import.meta.env.VITE_GA_ID || '';
const ENABLED = /^G-[A-Z0-9]{6,}$/.test(GA_ID);

let initialized = false;

export function initAnalytics() {
  if (!ENABLED || initialized || typeof window === 'undefined') return;
  initialized = true;

  // Inject gtag.js — async, non-blocking.
  const s = document.createElement('script');
  s.async = true;
  s.src = `https://www.googletagmanager.com/gtag/js?id=${GA_ID}`;
  document.head.appendChild(s);

  window.dataLayer = window.dataLayer || [];
  window.gtag = function () { window.dataLayer.push(arguments); };
  window.gtag('js', new Date());
  // send_page_view: false — we fire pageviews manually from the router so
  // SPA navigations are tracked, not just the initial load.
  window.gtag('config', GA_ID, { send_page_view: false });

  // Fire the first pageview now that gtag is up.
  trackPageview(window.location.pathname + window.location.search);
}

export function trackPageview(path) {
  if (!ENABLED || typeof window === 'undefined' || !window.gtag) return;
  window.gtag('event', 'page_view', {
    page_path: path,
    page_location: window.location.href,
    page_title: document.title,
  });
}

export function trackEvent(name, params) {
  if (!ENABLED || typeof window === 'undefined' || !window.gtag) return;
  window.gtag('event', name, params || {});
}

export const ANALYTICS_ENABLED = ENABLED;
