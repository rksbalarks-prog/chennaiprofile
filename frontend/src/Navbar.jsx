import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { API_BASE, USER_PANEL_URL } from './config';

export default function Navbar() {
  const [menuOpen, setMenuOpen] = useState(false);
  const [morePageOpen, setMorePageOpen] = useState(false);
  const [loginPromptOpen, setLoginPromptOpen] = useState(false);
  const [verified, setVerified] = useState(null); // null = loading, true/false = resolved
  const [verifiedMobile, setVerifiedMobile] = useState('');
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();
  const location = useLocation();

  useEffect(() => {
    // Cache the result in sessionStorage for 5 min so every SPA navigation
    // doesn't re-hit contact_check. Wrapped in try/catch — Instagram in-app
    // browser and private mode throw on sessionStorage access.
    const CACHE_KEY = 'cc_v1';
    const TTL = 5 * 60 * 1000;
    let cached = null;
    try {
      const raw = sessionStorage.getItem(CACHE_KEY);
      if (raw) cached = JSON.parse(raw);
    } catch (e) {}
    if (cached && Date.now() - cached.t < TTL) {
      setVerified(!!cached.v);
      setVerifiedMobile(cached.m || '');
      return;
    }
    fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'contact_check' }), credentials:'include' })
      .then(r => r.json()).then(d => {
        const v = !!(d.ok && d.verified);
        const m = d.mobile || '';
        setVerified(v); setVerifiedMobile(m);
        try { sessionStorage.setItem(CACHE_KEY, JSON.stringify({ v, m, t: Date.now() })); } catch (e) {}
      }).catch(() => setVerified(false));
  }, []);

  useEffect(() => { setMenuOpen(false); setMorePageOpen(false); setLoginPromptOpen(false); }, [location.pathname]);

  // Bottom nav visibility — show when the visitor is within EDGE px of the top
  // or bottom of the document, hide otherwise. Also stays visible on short
  // pages that don't scroll. Recomputes on scroll, resize, route change.
  const [bottomNavVisible, setBottomNavVisible] = useState(true);
  useEffect(() => {
    const EDGE = 120;
    const recompute = () => {
      const doc = document.documentElement;
      const scrollY = window.scrollY || window.pageYOffset || 0;
      const viewport = window.innerHeight || doc.clientHeight;
      const total = Math.max(doc.scrollHeight, doc.offsetHeight, document.body.scrollHeight);
      const distFromBottom = total - (scrollY + viewport);
      // Always-show when the page barely scrolls.
      if (total - viewport <= EDGE) { setBottomNavVisible(true); return; }
      setBottomNavVisible(scrollY <= EDGE || distFromBottom <= EDGE);
    };
    recompute();
    let raf = 0;
    const onScroll = () => {
      if (raf) return;
      raf = requestAnimationFrame(() => { raf = 0; recompute(); });
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
    return () => {
      window.removeEventListener('scroll', onScroll);
      window.removeEventListener('resize', onScroll);
      if (raf) cancelAnimationFrame(raf);
    };
  }, [location.pathname]);

  const handleLogout = async () => {
    await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'contact_logout' }), credentials:'include' });
    try { sessionStorage.removeItem('cc_v1'); } catch (e) {}
    setVerified(false); setVerifiedMobile('');
    navigate('/');
  };

  const handleLanguageChange = (lng) => {
    i18n.changeLanguage(lng);
    document.documentElement.lang = lng;
    document.documentElement.setAttribute('lang', lng);
    localStorage.setItem('language', lng);
  };

  const isActive = (path) => location.pathname === path;

  // Hide Navbar entirely when the page is loaded inside a host iframe (e.g.
  // user-panel.php embeds "/" with ?embed=1 so it can show only the profile cards)
  const isEmbed = typeof window !== 'undefined' && new URLSearchParams(window.location.search).has('embed');
  if (isEmbed) return null;

  return (
    <>
      <style>{`
        /* ── TOP BAR ── */
        .top-bar {
          position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
          background: #fff;
          border-bottom: 2px solid #C8EDE6;
          box-shadow: 0 2px 12px rgba(13,123,106,0.08);
          display: flex; align-items: center; justify-content: space-between;
          padding: 0 16px; height: 60px;
        }

        .top-bar-brand {
          display: flex; align-items: center; gap: 10px;
          text-decoration: none; color: inherit;
        }

        .mobile-back-btn {
          width: 40px; height: 40px; border-radius: 50%;
          background: #F4FAF8; border: none; cursor: pointer;
          display: flex; align-items: center; justify-content: center;
          margin-right: 6px; color: #1A1A2E; flex-shrink: 0;
          transition: background 0.15s;
        }
        .mobile-back-btn:active { background: #C8EDE6; }
        .mobile-back-btn svg { width: 22px; height: 22px; }
        @media (min-width: 769px) {
          .mobile-back-btn { display: none; }
        }

        .top-bar-logo {
          width: 42px; height: 42px; border-radius: 50%;
          border: 2px solid #0D7B6A; overflow: hidden;
          display: flex; align-items: center; justify-content: center;
          flex-shrink: 0;
        }

        .top-bar-logo img {
          width: 100%; height: 100%; object-fit: cover; border-radius: 50%;
        }

        .top-bar-title {
          font-size: 18px; font-weight: 800; color: #0D7B6A;
          letter-spacing: -0.3px; line-height: 1.1;
        }

        .top-bar-subtitle {
          font-size: 13px; font-weight: 500; color: #6B3FA0;
          letter-spacing: 0.5px;
        }

        .top-bar-actions {
          display: flex; align-items: center; gap: 8px;
        }

        .lang-toggle {
          display: flex; background: #F4FAF8; border-radius: 20px;
          overflow: hidden; border: 1px solid #C8EDE6;
        }

        .lang-btn {
          padding: 7px 14px; font-size: 13px; font-weight: 600;
          border: none; cursor: pointer; transition: all 0.2s;
          background: transparent; color: #888;
        }

        .lang-btn.active {
          background: #0D7B6A; color: #fff;
        }

        /* Single-button toggle: shows the OTHER language as its label */
        .lang-toggle-single {
          padding: 7px 14px; font-size: 14px; font-weight: 700;
          background: linear-gradient(135deg, #0D7B6A, #6B3FA0); color: #fff;
          border: none; border-radius: 20px;
          cursor: pointer; transition: opacity 0.15s, transform 0.05s;
          line-height: 1; min-width: 52px; text-align: center;
        }
        .lang-toggle-single:hover { opacity: 0.88; }
        .lang-toggle-single:active { transform: scale(0.97); }

        .hamburger-btn {
          width: 40px; height: 40px; border-radius: 50%;
          background: #F4FAF8; border: none; cursor: pointer;
          display: flex; align-items: center; justify-content: center;
          transition: all 0.2s;
        }

        .hamburger-btn:active { background: #C8EDE6; }

        .hamburger-btn svg { width: 22px; height: 22px; color: #1A1A2E; }

        /* ── SLIDE MENU ── */
        .slide-overlay {
          position: fixed; inset: 0; background: rgba(0,0,0,0.4);
          z-index: 2000; opacity: 0; pointer-events: none;
          transition: opacity 0.3s;
        }

        .slide-overlay.open { opacity: 1; pointer-events: auto; }

        .slide-menu {
          position: fixed; top: 0; right: -290px; bottom: 0;
          width: 290px; background: #fff; z-index: 2001;
          box-shadow: -4px 0 20px rgba(0,0,0,0.15);
          transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
          display: flex; flex-direction: column;
          overflow-y: auto;
        }

        .slide-menu.open { right: 0; }

        .slide-menu-header {
          background: linear-gradient(135deg, #0D7B6A, #6B3FA0);
          padding: 26px 22px 22px; color: #fff;
          display: flex; align-items: center; justify-content: space-between;
        }

        .slide-menu-header h3 {
          font-size: 20px; font-weight: 700; margin: 0;
        }

        .slide-close {
          width: 34px; height: 34px; border-radius: 50%;
          background: rgba(255,255,255,0.2); border: none;
          color: #fff; font-size: 19px; cursor: pointer;
          display: flex; align-items: center; justify-content: center;
        }

        .slide-menu-body { flex: 1; padding: 8px 0; }

        .slide-link {
          display: flex; align-items: center; gap: 14px;
          padding: 16px 22px; color: #1A1A2E; text-decoration: none;
          font-size: 17px; font-weight: 500; transition: all 0.2s;
          border-bottom: 1px solid #F4FAF8;
        }

        .slide-link:hover, .slide-link:active { background: #E8F5F2; color: #0D7B6A; }

        .slide-link-icon {
          width: 38px; height: 38px; border-radius: 10px;
          background: #F4FAF8; display: flex; align-items: center;
          justify-content: center; font-size: 20px; flex-shrink: 0;
        }

        .slide-link.active { color: #0D7B6A; font-weight: 700; }
        .slide-link.active .slide-link-icon { background: #E8F5F2; }

        .slide-menu-footer {
          padding: 18px 22px; border-top: 1px solid #C8EDE6;
          background: #F4FAF8;
        }

        /* ── BOTTOM NAV ── */
        .bottom-nav {
          position: fixed; bottom: 0; left: 0; right: 0;
          z-index: 1000; background: #fff;
          border-top: 2px solid #C8EDE6;
          box-shadow: 0 -2px 12px rgba(13,123,106,0.08);
          display: flex; align-items: stretch; justify-content: space-around;
          height: 64px; padding-bottom: env(safe-area-inset-bottom, 0);
          transform: translateY(0);
          transition: transform 0.22s ease, opacity 0.22s ease;
          opacity: 1;
        }
        .bottom-nav.is-hidden {
          transform: translateY(110%);
          opacity: 0;
          pointer-events: none;
        }

        .bottom-nav-item {
          flex: 1; display: flex; flex-direction: column;
          align-items: center; justify-content: center; gap: 3px;
          text-decoration: none; color: #999; font-size: 12px;
          font-weight: 500; transition: all 0.2s; position: relative;
          padding: 4px 0;
        }

        .bottom-nav-item.active { color: #0D7B6A; }

        .bottom-nav-item.active::after {
          content: ''; position: absolute; top: 0; left: 25%; right: 25%;
          height: 3px; background: #0D7B6A; border-radius: 0 0 4px 4px;
        }

        .bottom-nav-icon {
          width: 26px; height: 26px; display: flex;
          align-items: center; justify-content: center; font-size: 22px;
        }

        .bottom-nav-label { font-size: 12px; letter-spacing: 0.2px; }

        /* Center FAB */
        .bottom-nav-fab {
          position: relative; flex: 1; display: flex;
          flex-direction: column; align-items: center;
          justify-content: flex-end; padding-bottom: 6px;
          text-decoration: none; color: #fff; font-size: 12px; font-weight: 600;
        }

        .bottom-nav-fab-circle {
          position: absolute; top: -20px;
          width: 56px; height: 56px; border-radius: 50%;
          background: linear-gradient(135deg, #0D7B6A, #6B3FA0);
          display: flex; align-items: center; justify-content: center;
          box-shadow: 0 4px 16px rgba(13,123,106,0.4);
          font-size: 26px; color: #fff;
          border: 3px solid #fff;
        }

        .bottom-nav-fab-label {
          font-size: 12px; color: #0D7B6A; font-weight: 600; margin-top: 20px;
        }

        /* ── MORE FULL PAGE ── */
        .more-page {
          position: fixed; inset: 0; z-index: 3000;
          background: #f7faf9;
          display: flex; flex-direction: column;
          transform: translateY(100%);
          transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
          overflow-y: auto;
        }
        .more-page.open { transform: translateY(0); }

        .more-page-header {
          background: linear-gradient(135deg, #0D7B6A, #6B3FA0);
          padding: 48px 22px 24px; color: #fff;
          display: flex; align-items: center; justify-content: space-between;
          flex-shrink: 0;
        }
        .more-page-user { display: flex; flex-direction: column; gap: 4px; }
        .more-page-user-name { font-size: 20px; font-weight: 700; }
        .more-page-user-mobile { font-size: 14px; opacity: 0.85; }
        .more-page-close {
          width: 38px; height: 38px; border-radius: 50%;
          background: rgba(255,255,255,0.2); border: none;
          color: #fff; font-size: 20px; cursor: pointer;
          display: flex; align-items: center; justify-content: center;
          flex-shrink: 0;
        }

        .more-page-list { flex: 1; padding: 12px 16px; display: flex; flex-direction: column; gap: 8px; }

        .more-page-item {
          display: flex; align-items: center; gap: 14px;
          padding: 16px 18px; background: #fff; border-radius: 14px;
          color: #1A1A2E; text-decoration: none; font-size: 16px; font-weight: 500;
          box-shadow: 0 1px 4px rgba(0,0,0,0.06); transition: all 0.15s;
          border: none; cursor: pointer; width: 100%; text-align: left;
        }
        .more-page-item:active { background: #E8F5F2; color: #0D7B6A; }
        .more-page-item.danger { color: #dc2626; }
        .more-page-item.danger:active { background: #fff5f5; }
        .more-page-item-icon {
          width: 40px; height: 40px; border-radius: 10px;
          background: #F4FAF8; display: flex; align-items: center;
          justify-content: center; font-size: 20px; flex-shrink: 0;
        }
        .more-page-item.danger .more-page-item-icon { background: #fff5f5; }

        .more-page-divider { height: 1px; background: #e5f0ec; margin: 4px 0; }

        .more-page-footer {
          padding: 20px; text-align: center;
          font-size: 13px; color: #aaa; flex-shrink: 0;
        }

        /* ── LOGIN PROMPT BOTTOM SHEET ── */
        .login-prompt-overlay {
          position: fixed; inset: 0; background: rgba(0,0,0,0.45);
          z-index: 3000; opacity: 0; pointer-events: none;
          transition: opacity 0.25s;
        }
        .login-prompt-overlay.open { opacity: 1; pointer-events: auto; }

        .login-prompt-sheet {
          position: fixed; bottom: -100%; left: 0; right: 0;
          z-index: 3001; background: #fff;
          border-radius: 20px 20px 0 0;
          padding: 28px 24px 40px;
          transition: bottom 0.3s cubic-bezier(0.4,0,0.2,1);
          text-align: center;
        }
        .login-prompt-sheet.open { bottom: 0; }
        .login-prompt-sheet h3 { font-size: 20px; font-weight: 700; color: #1A1A2E; margin: 0 0 8px; }
        .login-prompt-sheet p { font-size: 15px; color: #666; margin: 0 0 24px; line-height: 1.5; }
        .login-prompt-btn {
          display: block; width: 100%; padding: 15px;
          background: linear-gradient(135deg, #0D7B6A, #6B3FA0);
          color: #fff; font-size: 16px; font-weight: 700;
          border: none; border-radius: 12px; cursor: pointer;
          text-decoration: none; margin-bottom: 10px;
        }
        .login-prompt-cancel {
          background: none; border: none; color: #999;
          font-size: 15px; cursor: pointer; padding: 8px;
          width: 100%;
        }

        /* ── SPACERS ── */
        .top-spacer { height: 60px; }
        .bottom-spacer { height: 4px; }

        @media (min-width: 769px) {
          .bottom-nav { max-width: 520px; left: 50%; right: auto; transform: translateX(-50%); border-radius: 14px 14px 0 0; bottom: 0; }
          .bottom-nav.is-hidden { transform: translate(-50%, 110%); }
          .bottom-spacer { display: none; }
          .top-bar { height: 68px; padding: 0 24px; }
          .top-spacer { height: 68px; }
          .top-bar-title { font-size: 20px; }
          .desktop-links {
            display: flex !important; align-items: center; gap: 6px;
          }
          .desktop-link {
            padding: 9px 16px; font-size: 16px; font-weight: 500;
            color: #555; text-decoration: none; border-radius: 8px;
            transition: all 0.2s;
          }
          .desktop-link:hover { background: #E8F5F2; color: #0D7B6A; }
          .desktop-link.active { color: #0D7B6A; font-weight: 700; background: #E8F5F2; }
          .desktop-login {
            padding: 9px 20px; background: linear-gradient(135deg, #0D7B6A, #6B3FA0);
            color: #fff; border-radius: 8px; font-size: 15px; font-weight: 600;
            text-decoration: none; transition: all 0.2s; border: none; cursor: pointer;
            box-shadow: 0 4px 12px rgba(13,123,106,.25);
          }
          .desktop-login:hover { box-shadow: 0 6px 18px rgba(13,123,106,0.4); transform: translateY(-1px); }
          .hamburger-btn { display: none; }
        }

        @media (max-width: 768px) {
          .desktop-links { display: none !important; }
        }
      `}</style>

      {/* ═══ TOP BAR ═══ */}
      <header className="top-bar">
        {location.pathname !== '/' && (
          <button
            className="mobile-back-btn"
            aria-label="Back"
            onClick={() => { if (window.history.length > 1) navigate(-1); else navigate('/'); }}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
              <polyline points="15 18 9 12 15 6"/>
            </svg>
          </button>
        )}
        <Link to="/" className="top-bar-brand">
          <div className="top-bar-logo">
            <img src="/assets/chennaiprofile_logo.svg" alt="Logo"
              onError={(e) => { e.target.parentElement.innerHTML = '<span style="font-size:18px;font-weight:900;color:#8B0000">CP</span>'; }} />
          </div>
          <div>
            <div className="top-bar-title">Chennai Profile</div>
            <div className="top-bar-subtitle">Matrimony</div>
          </div>
        </Link>

        {/* Desktop nav links */}
        <div className="desktop-links" style={{ display: 'none' }}>
          <Link to="/" className={`desktop-link ${isActive('/') ? 'active' : ''}`}>{t('navbar.home')}</Link>
          <Link to="/search" className={`desktop-link ${isActive('/search') ? 'active' : ''}`}>{t('navbar.search')}</Link>
          <Link to="/contact" className={`desktop-link ${isActive('/contact') ? 'active' : ''}`}>{t('navbar.contact')}</Link>
          <Link to="/about-us" className={`desktop-link ${isActive('/about-us') ? 'active' : ''}`}>About</Link>

          {verified === null ? null : verified ? (
            <>
              <a href={USER_PANEL_URL} className="desktop-login">👤 My Profile</a>
              <button onClick={handleLogout} style={{ padding:'9px 16px', background:'transparent', color:'#dc2626', border:'1.5px solid #dc2626', borderRadius:8, fontSize:16, fontWeight:600, cursor:'pointer' }}>
                Sign Out
              </button>
            </>
          ) : (
            <a href={USER_PANEL_URL} className="desktop-login">User Login</a>
          )}
        </div>

        {/* Top-bar actions (visible on both mobile and desktop) */}
        <div className="top-bar-actions">
          <button
            className="lang-toggle-single"
            onClick={() => handleLanguageChange(i18n.language === 'en' ? 'ta' : 'en')}
            aria-label={i18n.language === 'en' ? 'Switch to Tamil' : 'Switch to English'}
            title={i18n.language === 'en' ? 'Switch to Tamil' : 'Switch to English'}
          >
            {i18n.language === 'en' ? 'தமிழ்' : 'EN'}
          </button>

          <Link to="/search" aria-label="Search" style={{ display:'flex', alignItems:'center', justifyContent:'center', width:36, height:36, borderRadius:8, color:'#333', textDecoration:'none' }}>
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          </Link>

          <button className="hamburger-btn" onClick={() => setMenuOpen(true)}>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>
        </div>
      </header>

      <div className="top-spacer" />

      {/* ═══ SLIDE MENU ═══ */}
      <div className={`slide-overlay ${menuOpen ? 'open' : ''}`} onClick={() => setMenuOpen(false)} />
      <div className={`slide-menu ${menuOpen ? 'open' : ''}`}>
        <div className="slide-menu-header">
          <h3>Menu</h3>
          <button className="slide-close" onClick={() => setMenuOpen(false)}>✕</button>
        </div>
        <div className="slide-menu-body">
          <Link to="/" className={`slide-link ${isActive('/') ? 'active' : ''}`} onClick={() => setMenuOpen(false)}>
            <div className="slide-link-icon">🏠</div> {t('navbar.home')}
          </Link>
          <Link to="/search" className={`slide-link ${isActive('/search') ? 'active' : ''}`} onClick={() => setMenuOpen(false)}>
            <div className="slide-link-icon">🔍</div> {t('navbar.search')}
          </Link>
          <Link to="/contact" className={`slide-link ${isActive('/contact') ? 'active' : ''}`} onClick={() => setMenuOpen(false)}>
            <div className="slide-link-icon">📞</div> {t('navbar.contact')}
          </Link>
          <Link to="/about-us" className={`slide-link ${isActive('/about-us') ? 'active' : ''}`} onClick={() => setMenuOpen(false)}>
            <div className="slide-link-icon">ℹ️</div> About Us
          </Link>
          <Link to="/privacy-policy" className={`slide-link ${isActive('/privacy-policy') ? 'active' : ''}`} onClick={() => setMenuOpen(false)}>
            <div className="slide-link-icon">🔒</div> Privacy Policy
          </Link>
          <Link to="/terms-and-conditions" className={`slide-link ${isActive('/terms-and-conditions') ? 'active' : ''}`} onClick={() => setMenuOpen(false)}>
            <div className="slide-link-icon">📄</div> Terms & Conditions
          </Link>

          {verified === null ? null : verified ? (
            <>
              <a href={USER_PANEL_URL} className="slide-link" onClick={() => setMenuOpen(false)}>
                <div className="slide-link-icon">👤</div> My Profile
              </a>
              <a href="#" className="slide-link" style={{ color: '#dc2626' }} onClick={(e) => { e.preventDefault(); setMenuOpen(false); handleLogout(); }}>
                <div className="slide-link-icon">🚪</div> Sign Out
              </a>
            </>
          ) : (
            <a href={USER_PANEL_URL} className="slide-link" style={{ color: '#0D7B6A', fontWeight: 700 }} onClick={() => setMenuOpen(false)}>
              <div className="slide-link-icon">🔑</div> User Login
            </a>
          )}
        </div>
        <div className="slide-menu-footer">
          <div style={{ fontSize: 15, color: '#999', textAlign: 'center' }}>Chennai Profile Matrimony</div>
        </div>
      </div>

      {/* ═══ MORE FULL PAGE (logged in) ═══ */}
      <div className={`more-page ${morePageOpen ? 'open' : ''}`}>
        <div className="more-page-header">
          <div className="more-page-user">
            <div className="more-page-user-name">👋 Hello!</div>
            {verifiedMobile && <div className="more-page-user-mobile">📱 {verifiedMobile}</div>}
          </div>
          <button className="more-page-close" onClick={() => setMorePageOpen(false)}>✕</button>
        </div>
        <div className="more-page-list">
          <Link to="/" className="more-page-item" onClick={() => setMorePageOpen(false)}>
            <div className="more-page-item-icon">🏠</div> {t('navbar.home')}
          </Link>
          <Link to="/search" className="more-page-item" onClick={() => setMorePageOpen(false)}>
            <div className="more-page-item-icon">🔍</div> {t('navbar.search')}
          </Link>
          <a href={USER_PANEL_URL} className="more-page-item" onClick={() => setMorePageOpen(false)}>
            <div className="more-page-item-icon">👤</div> My Profile
          </a>
          <div className="more-page-divider" />
          <Link to="/contact" className="more-page-item" onClick={() => setMorePageOpen(false)}>
            <div className="more-page-item-icon">📞</div> {t('navbar.contact')}
          </Link>
          <Link to="/about-us" className="more-page-item" onClick={() => setMorePageOpen(false)}>
            <div className="more-page-item-icon">ℹ️</div> About Us
          </Link>
          <Link to="/privacy-policy" className="more-page-item" onClick={() => setMorePageOpen(false)}>
            <div className="more-page-item-icon">🔒</div> Privacy Policy
          </Link>
          <Link to="/terms-and-conditions" className="more-page-item" onClick={() => setMorePageOpen(false)}>
            <div className="more-page-item-icon">📄</div> Terms & Conditions
          </Link>
          <div className="more-page-divider" />
          <button className="more-page-item danger" onClick={() => { setMorePageOpen(false); handleLogout(); }}>
            <div className="more-page-item-icon">🚪</div> Sign Out
          </button>
        </div>
        <div className="more-page-footer">Chennai Profile Matrimony</div>
      </div>

      {/* ═══ LOGIN PROMPT BOTTOM SHEET (not logged in) ═══ */}
      <div className={`login-prompt-overlay ${loginPromptOpen ? 'open' : ''}`} onClick={() => setLoginPromptOpen(false)} />
      <div className={`login-prompt-sheet ${loginPromptOpen ? 'open' : ''}`}>
        <h3>🔐 Login Required</h3>
        <p>Please verify your mobile number to access your profile and more options.</p>
        <a href={USER_PANEL_URL} className="login-prompt-btn">Login / Verify Now</a>
        <button className="login-prompt-cancel" onClick={() => setLoginPromptOpen(false)}>Cancel</button>
      </div>

      {/* ═══ BOTTOM NAV ═══ */}
      <nav className={`bottom-nav ${bottomNavVisible ? '' : 'is-hidden'}`}>
        <Link to="/" className={`bottom-nav-item ${isActive('/') ? 'active' : ''}`}>
          <div className="bottom-nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
          <span className="bottom-nav-label">Home</span>
        </Link>

        <a href={USER_PANEL_URL} className="bottom-nav-item">
          <div className="bottom-nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <span className="bottom-nav-label">My Profile</span>
        </a>

        <a href={`${USER_PANEL_URL}?create=1`} className="bottom-nav-fab">
          <div className="bottom-nav-fab-circle"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5" strokeLinecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
          <span className="bottom-nav-fab-label">Register</span>
        </a>

        <button
          className={`bottom-nav-item ${morePageOpen ? 'active' : ''}`}
          style={{ background: 'none', border: 'none', cursor: 'pointer' }}
          onClick={() => {
            if (verified === true) { window.location.href = USER_PANEL_URL; }
            else setLoginPromptOpen(true);
          }}
        >
          <div className="bottom-nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></div>
          <span className="bottom-nav-label">More</span>
        </button>
      </nav>

      <div className="bottom-spacer" />
    </>
  );
}
