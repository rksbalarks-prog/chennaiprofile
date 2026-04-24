import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { API_BASE, USER_PANEL_URL } from './config';

export default function Navbar() {
  const [menuOpen, setMenuOpen] = useState(false);
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

  useEffect(() => { setMenuOpen(false); }, [location.pathname]);

  const handleLogout = async () => {
    await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'contact_logout' }), credentials:'include' });
    try { sessionStorage.removeItem('cc_v1'); } catch (e) {}
    setVerified(false); setVerifiedMobile('');
    navigate('/');
    window.location.reload();
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
          border-bottom: 1px solid #f0f0f0;
          box-shadow: 0 1px 8px rgba(0,0,0,0.06);
          display: flex; align-items: center; justify-content: space-between;
          padding: 0 16px; height: 56px;
        }

        .top-bar-brand {
          display: flex; align-items: center; gap: 10px;
          text-decoration: none; color: inherit;
        }

        .mobile-back-btn {
          width: 36px; height: 36px; border-radius: 50%;
          background: #f5f5f5; border: none; cursor: pointer;
          display: flex; align-items: center; justify-content: center;
          margin-right: 6px; color: #333; flex-shrink: 0;
          transition: background 0.15s;
        }
        .mobile-back-btn:active { background: #e8e8e8; }
        .mobile-back-btn svg { width: 20px; height: 20px; }
        @media (min-width: 769px) {
          .mobile-back-btn { display: none; }
        }

        .top-bar-logo {
          width: 40px; height: 40px; border-radius: 50%;
          border: 2px solid #8B0000; overflow: hidden;
          display: flex; align-items: center; justify-content: center;
          flex-shrink: 0;
        }

        .top-bar-logo img {
          width: 100%; height: 100%; object-fit: cover; border-radius: 50%;
        }

        .top-bar-title {
          font-size: 16px; font-weight: 800; color: #8B0000;
          letter-spacing: -0.3px; line-height: 1.1;
        }

        .top-bar-subtitle {
          font-size: 11px; font-weight: 500; color: #999;
          letter-spacing: 0.5px;
        }

        .top-bar-actions {
          display: flex; align-items: center; gap: 8px;
        }

        .lang-toggle {
          display: flex; background: #f5f5f5; border-radius: 20px;
          overflow: hidden; border: 1px solid #e8e8e8;
        }

        .lang-btn {
          padding: 6px 12px; font-size: 11px; font-weight: 600;
          border: none; cursor: pointer; transition: all 0.2s;
          background: transparent; color: #888;
        }

        .lang-btn.active {
          background: #8B0000; color: #fff;
        }

        .hamburger-btn {
          width: 36px; height: 36px; border-radius: 50%;
          background: #f5f5f5; border: none; cursor: pointer;
          display: flex; align-items: center; justify-content: center;
          transition: all 0.2s;
        }

        .hamburger-btn:active { background: #e8e8e8; }

        .hamburger-btn svg { width: 20px; height: 20px; color: #333; }

        /* ── SLIDE MENU ── */
        .slide-overlay {
          position: fixed; inset: 0; background: rgba(0,0,0,0.4);
          z-index: 2000; opacity: 0; pointer-events: none;
          transition: opacity 0.3s;
        }

        .slide-overlay.open { opacity: 1; pointer-events: auto; }

        .slide-menu {
          position: fixed; top: 0; right: -280px; bottom: 0;
          width: 280px; background: #fff; z-index: 2001;
          box-shadow: -4px 0 20px rgba(0,0,0,0.15);
          transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
          display: flex; flex-direction: column;
          overflow-y: auto;
        }

        .slide-menu.open { right: 0; }

        .slide-menu-header {
          background: linear-gradient(135deg, #8B0000, #C41E3A);
          padding: 24px 20px 20px; color: #fff;
          display: flex; align-items: center; justify-content: space-between;
        }

        .slide-menu-header h3 {
          font-size: 18px; font-weight: 700; margin: 0;
        }

        .slide-close {
          width: 32px; height: 32px; border-radius: 50%;
          background: rgba(255,255,255,0.2); border: none;
          color: #fff; font-size: 18px; cursor: pointer;
          display: flex; align-items: center; justify-content: center;
        }

        .slide-menu-body { flex: 1; padding: 8px 0; }

        .slide-link {
          display: flex; align-items: center; gap: 14px;
          padding: 14px 20px; color: #333; text-decoration: none;
          font-size: 15px; font-weight: 500; transition: all 0.2s;
          border-bottom: 1px solid #f5f5f5;
        }

        .slide-link:hover, .slide-link:active { background: #fef2f2; color: #8B0000; }

        .slide-link-icon {
          width: 36px; height: 36px; border-radius: 10px;
          background: #f8f8f8; display: flex; align-items: center;
          justify-content: center; font-size: 18px; flex-shrink: 0;
        }

        .slide-link.active { color: #8B0000; font-weight: 700; }
        .slide-link.active .slide-link-icon { background: #fef2f2; }

        .slide-menu-footer {
          padding: 16px 20px; border-top: 1px solid #f0f0f0;
          background: #fafafa;
        }

        /* ── BOTTOM NAV ── */
        .bottom-nav {
          position: fixed; bottom: 0; left: 0; right: 0;
          z-index: 1000; background: #fff;
          border-top: 1px solid #f0f0f0;
          box-shadow: 0 -2px 12px rgba(0,0,0,0.06);
          display: flex; align-items: stretch; justify-content: space-around;
          height: 60px; padding-bottom: env(safe-area-inset-bottom, 0);
        }

        .bottom-nav-item {
          flex: 1; display: flex; flex-direction: column;
          align-items: center; justify-content: center; gap: 2px;
          text-decoration: none; color: #999; font-size: 10px;
          font-weight: 500; transition: all 0.2s; position: relative;
          padding: 4px 0;
        }

        .bottom-nav-item.active { color: #8B0000; }

        .bottom-nav-item.active::after {
          content: ''; position: absolute; top: 0; left: 25%; right: 25%;
          height: 2.5px; background: #8B0000; border-radius: 0 0 4px 4px;
        }

        .bottom-nav-icon {
          width: 24px; height: 24px; display: flex;
          align-items: center; justify-content: center; font-size: 20px;
        }

        .bottom-nav-label { font-size: 10px; letter-spacing: 0.2px; }

        /* Center FAB */
        .bottom-nav-fab {
          position: relative; flex: 1; display: flex;
          flex-direction: column; align-items: center;
          justify-content: flex-end; padding-bottom: 6px;
          text-decoration: none; color: #fff; font-size: 10px; font-weight: 600;
        }

        .bottom-nav-fab-circle {
          position: absolute; top: -18px;
          width: 52px; height: 52px; border-radius: 50%;
          background: linear-gradient(135deg, #8B0000, #C41E3A);
          display: flex; align-items: center; justify-content: center;
          box-shadow: 0 4px 16px rgba(139,0,0,0.35);
          font-size: 24px; color: #fff;
          border: 3px solid #fff;
        }

        .bottom-nav-fab-label {
          font-size: 10px; color: #8B0000; font-weight: 600; margin-top: 18px;
        }

        /* ── SPACERS ── */
        .top-spacer { height: 56px; }
        .bottom-spacer { height: 4px; }

        /* ── DESKTOP: hide bottom nav, show top nav links ── */
        @media (min-width: 769px) {
          .bottom-nav { display: none; }
          .bottom-spacer { display: none; }
          .top-bar { height: 64px; padding: 0 24px; }
          .top-spacer { height: 64px; }
          .top-bar-title { font-size: 18px; }
          .desktop-links {
            display: flex !important; align-items: center; gap: 6px;
          }
          .desktop-link {
            padding: 8px 14px; font-size: 14px; font-weight: 500;
            color: #555; text-decoration: none; border-radius: 8px;
            transition: all 0.2s;
          }
          .desktop-link:hover { background: #fef2f2; color: #8B0000; }
          .desktop-link.active { color: #8B0000; font-weight: 700; background: #fef2f2; }
          .desktop-login {
            padding: 8px 18px; background: linear-gradient(135deg, #8B0000, #C41E3A);
            color: #fff; border-radius: 8px; font-size: 13px; font-weight: 600;
            text-decoration: none; transition: all 0.2s; border: none; cursor: pointer;
          }
          .desktop-login:hover { box-shadow: 0 4px 12px rgba(139,0,0,0.3); transform: translateY(-1px); }
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
            <img src="/assets/kumbakonam_logo.svg" alt="Logo"
              onError={(e) => { e.target.parentElement.innerHTML = '<span style="font-size:18px;font-weight:900;color:#8B0000">KM</span>'; }} />
          </div>
          <div>
            <div className="top-bar-title">Kumbakonam</div>
            <div className="top-bar-subtitle">Free Matrimony</div>
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
              <button onClick={handleLogout} style={{ padding:'8px 14px', background:'transparent', color:'#dc2626', border:'1.5px solid #dc2626', borderRadius:8, fontSize:13, fontWeight:600, cursor:'pointer' }}>
                Sign Out
              </button>
            </>
          ) : (
            <a href={USER_PANEL_URL} className="desktop-login">User Login</a>
          )}

          <div className="lang-toggle">
            <button className={`lang-btn ${i18n.language === 'en' ? 'active' : ''}`} onClick={() => handleLanguageChange('en')}>EN</button>
            <button className={`lang-btn ${i18n.language === 'ta' ? 'active' : ''}`} onClick={() => handleLanguageChange('ta')}>த</button>
          </div>
        </div>

        {/* Mobile actions */}
        <div className="top-bar-actions">
          <div className="lang-toggle">
            <button className={`lang-btn ${i18n.language === 'en' ? 'active' : ''}`} onClick={() => handleLanguageChange('en')}>EN</button>
            <button className={`lang-btn ${i18n.language === 'ta' ? 'active' : ''}`} onClick={() => handleLanguageChange('ta')}>த</button>
          </div>

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
            <a href={USER_PANEL_URL} className="slide-link" style={{ color: '#8B0000', fontWeight: 700 }} onClick={() => setMenuOpen(false)}>
              <div className="slide-link-icon">🔑</div> User Login
            </a>
          )}
        </div>
        <div className="slide-menu-footer">
          <div style={{ fontSize: 11, color: '#999', textAlign: 'center' }}>Kumbakonam Free Matrimony</div>
        </div>
      </div>

      {/* ═══ BOTTOM NAV ═══ */}
      <nav className="bottom-nav">
        <Link to="/" className={`bottom-nav-item ${isActive('/') ? 'active' : ''}`}>
          <div className="bottom-nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
          <span className="bottom-nav-label">Home</span>
        </Link>

        <Link to="/search" className={`bottom-nav-item ${isActive('/search') ? 'active' : ''}`}>
          <div className="bottom-nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></div>
          <span className="bottom-nav-label">Search</span>
        </Link>

        <a href={`${USER_PANEL_URL}?create=1`} className="bottom-nav-fab">
          <div className="bottom-nav-fab-circle"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2.5" strokeLinecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
          <span className="bottom-nav-fab-label">Register</span>
        </a>

        <a href={USER_PANEL_URL} className="bottom-nav-item">
          <div className="bottom-nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
          <span className="bottom-nav-label">My Profile</span>
        </a>

        <button className={`bottom-nav-item`} onClick={() => setMenuOpen(true)} style={{ background: 'none', border: 'none', cursor: 'pointer' }}>
          <div className="bottom-nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></div>
          <span className="bottom-nav-label">More</span>
        </button>
      </nav>

      <div className="bottom-spacer" />
    </>
  );
}
