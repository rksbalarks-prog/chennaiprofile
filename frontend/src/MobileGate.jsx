import React, { useState, useEffect, useRef } from 'react';
import { API_BASE } from './config';

const C = {
  maroon: '#7f1d1d',
  maroon2: '#991b1b',
  maroon3: '#b91c1c',
  maroonDk: '#5b1313',
  amber: '#f59e0b',
  gold: '#fcd34d',
  ink: '#111827',
  ink2: '#374151',
  ink3: '#6b7280',
  ink4: '#9ca3af',
  line: '#e5e7eb',
  line2: '#d1d5db',
  bgSoft: '#fafaf9',
  red50: '#fef2f2',
  red100: '#fee2e2',
  green50: '#ecfdf5',
  green700: '#047857',
  green100: '#d1fae5',
  amber50: '#fffbeb',
};

export default function MobileGate({ children }) {
  const [checking, setChecking] = useState(true);
  const [verified, setVerified] = useState(false);
  const [mobile, setMobile] = useState('');
  const [otp, setOtp] = useState(['', '', '', '']);
  const [stage, setStage] = useState('mobile');
  const [msg, setMsg] = useState('');
  const [msgKind, setMsgKind] = useState('info');
  const [loading, setLoading] = useState(false);
  const [timer, setTimer] = useState(0);
  const [focusRing, setFocusRing] = useState(false);
  const otpRefs = useRef([]);

  useEffect(() => {
    // Cache verified state 5 min in sessionStorage so refresh is instant
    // (same key as Navbar so both stay in sync). Wrapped in try/catch because
    // Instagram in-app browser throws on storage access.
    const CACHE_KEY = 'cc_v1';
    const TTL = 5 * 60 * 1000;
    try {
      const raw = sessionStorage.getItem(CACHE_KEY);
      const cached = raw ? JSON.parse(raw) : null;
      if (cached && cached.v && Date.now() - cached.t < TTL) {
        setVerified(true);
        setChecking(false);
        return;
      }
    } catch (e) {}

    fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'contact_check' }),
      credentials: 'include',
    })
      .then(r => r.json())
      .then(d => {
        const v = !!(d.ok && (d.verified || d.skipped));
        if (v) setVerified(true);
        try {
          sessionStorage.setItem(CACHE_KEY, JSON.stringify({
            v, m: d.mobile || '', t: Date.now()
          }));
        } catch (e) {}
      })
      .catch(() => {})
      .finally(() => setChecking(false));
  }, []);


  useEffect(() => {
    if (timer <= 0) return;
    const t = setTimeout(() => setTimer(timer - 1), 1000);
    return () => clearTimeout(t);
  }, [timer]);

  // Track partial mobile entries on the gate.
  //
  // Transport: sendBeacon as primary (fetch as fallback). sendBeacon is the
  // designed-for-analytics path — survives page unloads, doesn't trigger CORS
  // preflight, and is generally allowed through WAFs that block keepalive
  // fetch POSTs. Use text/plain content-type because some WAFs treat JSON
  // POSTs as suspicious and challenge them.
  const sendTrack = (mobileStr, action = 'contact_mobile_typed') => {
    const payload = JSON.stringify({ action, mobile: mobileStr });
    try {
      if (navigator.sendBeacon) {
        const blob = new Blob([payload], { type: 'text/plain;charset=UTF-8' });
        if (navigator.sendBeacon(API_BASE, blob)) return;
      }
    } catch (e) {}
    // Fallback: regular fetch (no keepalive — that flag can confuse WAF cookie handling).
    fetch(API_BASE, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: payload,
      credentials: 'include',
    }).catch(() => {});
  };

  // Record on every keystroke after a short debounce. Lowered to 1 digit so
  // even a single keypress shows up — the backend collapses shorter prefix
  // rows into the longest entered value.
  useEffect(() => {
    if (mobile.length < 1 || mobile.length > 10) return;
    const t = setTimeout(() => sendTrack(mobile, 'contact_mobile_typed'), 250);
    return () => clearTimeout(t);
  }, [mobile]);

  // Flush on visibility change / blur / pagehide so tab-switchers and
  // back-navigators are still captured.
  useEffect(() => {
    const flushTyped = () => {
      if (mobile.length < 1 || mobile.length > 10) return;
      sendTrack(mobile, 'contact_mobile_typed');
    };
    const flushSkip = () => {
      if (stage !== 'mobile' || mobile.length < 1) return;
      sendTrack(mobile, 'contact_skip_gate');
    };
    const onVis = () => { if (document.visibilityState === 'hidden') flushTyped(); };
    document.addEventListener('visibilitychange', onVis);
    window.addEventListener('blur', flushTyped);
    window.addEventListener('pagehide', flushSkip);
    return () => {
      document.removeEventListener('visibilitychange', onVis);
      window.removeEventListener('blur', flushTyped);
      window.removeEventListener('pagehide', flushSkip);
    };
  }, [mobile, stage]);

  const show = (text, kind = 'info') => { setMsg(text); setMsgKind(kind); };

  const sendOtp = async () => {
    if (!/^\d{10}$/.test(mobile)) { show('Enter a valid 10-digit mobile number', 'error'); return; }
    setLoading(true); show('');
    try {
      const r = await fetch(API_BASE, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'contact_otp_send', mobile }),
        credentials: 'include',
      }).then(r => r.json());
      if (r.ok) {
        if (r.auto_verified) {
          setVerified(true);
          try { sessionStorage.setItem('cc_v1', JSON.stringify({ v:true, m:mobile, t:Date.now() })); } catch(e){}
          return;
        }
        setStage('otp'); setTimer(120);
        show(r.otp ? `Dev OTP: ${r.otp}` : `OTP sent to +91 ${mobile}`, 'success');
        setTimeout(() => otpRefs.current[0]?.focus(), 50);
      } else show(r.error || 'Failed to send OTP', 'error');
    } catch { show('Network error. Please try again.', 'error'); }
    setLoading(false);
  };

  const verifyOtp = async () => {
    const code = otp.join('');
    if (code.length !== 4) { show('Enter the 4-digit OTP', 'error'); return; }
    setLoading(true); show('');
    try {
      const r = await fetch(API_BASE, {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'contact_otp_verify', mobile, otp: code }),
        credentials: 'include',
      }).then(r => r.json());
      if (r.ok && r.verified) {
        setVerified(true);
        try { sessionStorage.setItem('cc_v1', JSON.stringify({ v:true, m:mobile, t:Date.now() })); } catch(e){}
      }
      else show(r.error || 'Invalid OTP', 'error');
    } catch { show('Network error. Please try again.', 'error'); }
    setLoading(false);
  };

  const handleOtpInput = (i, v) => {
    if (!/^\d?$/.test(v)) return;
    const n = [...otp]; n[i] = v; setOtp(n);
    if (v && i < 3) otpRefs.current[i + 1]?.focus();
  };
  const handleOtpKey = (i, e) => {
    if (e.key === 'Backspace' && !otp[i] && i > 0) otpRefs.current[i - 1]?.focus();
    if (e.key === 'Enter') verifyOtp();
  };
  const handleOtpPaste = (e) => {
    const txt = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 4);
    if (!txt) return;
    e.preventDefault();
    const n = ['', '', '', ''];
    for (let i = 0; i < txt.length; i++) n[i] = txt[i];
    setOtp(n);
    otpRefs.current[Math.min(txt.length, 3)]?.focus();
  };

  if (verified) return children;

  return (
    <>
      <div aria-hidden="true" style={S.blurLayer}>{children}</div>
      <div style={S.wrap}>
        <style>{globalCss}</style>
        {checking ? (
          <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 12 }}>
            <div style={S.spinner} />
            <div style={{ color: '#fff', fontSize: 14, textShadow: '0 1px 3px rgba(0,0,0,0.4)' }}>Loading…</div>
            <style>{spinnerKeyframes}</style>
          </div>
        ) : (
      <div className="mg-card" style={S.card}>
        {/* Form */}
        <div style={S.right}>
          <div className="mg-mobile-brand" style={S.mobileBrand}>
            <div style={S.mobileBrandBadge}>KM</div>
            <div>
              <div style={S.mobileBrandTitle}>Kumbakonam Free Matrimony</div>
              <div style={S.mobileBrandSub}>Verified · Safe · Free</div>
            </div>
          </div>
          <div style={S.stepRow}>
            <span style={{ ...S.stepPill, background: stage === 'mobile' ? C.maroon2 : '#16a34a' }} />
            <span style={{ ...S.stepPill, background: stage === 'otp' ? C.maroon2 : C.line }} />
            <span style={S.stepLabel}>Step {stage === 'mobile' ? '1' : '2'} of 2</span>
          </div>

          <h1 style={S.title}>{stage === 'mobile' ? 'Verify your mobile' : 'Enter the OTP'}</h1>
          <p style={S.subtitle}>
            {stage === 'mobile'
              ? 'We\'ll send a 4-digit OTP to your mobile to keep the community safe.'
              : <>4-digit code sent to <b style={{ color: C.ink }}>+91 {mobile}</b></>}
          </p>

          {stage === 'mobile' && (
            <>
              <label style={S.label}>MOBILE NUMBER</label>
              <div style={{ ...S.inputRow, borderColor: focusRing ? C.maroon2 : C.line2, boxShadow: focusRing ? `0 0 0 3px ${C.red100}` : 'none' }}>
                <span style={S.prefix}>+91</span>
                <input
                  type="tel"
                  inputMode="numeric"
                  maxLength={10}
                  value={mobile}
                  onChange={e => setMobile(e.target.value.replace(/\D/g, ''))}
                  onFocus={() => setFocusRing(true)}
                  onBlur={() => setFocusRing(false)}
                  onKeyDown={e => { if (e.key === 'Enter') sendOtp(); }}
                  placeholder="98765 43210"
                  style={S.input}
                  autoFocus
                />
              </div>

              <button onClick={sendOtp} disabled={loading} style={{ ...S.primaryBtn, opacity: loading ? 0.6 : 1, cursor: loading ? 'not-allowed' : 'pointer' }}>
                {loading ? 'Sending OTP…' : 'Send OTP →'}
              </button>

              <p style={S.termsNote}>
                By continuing, you agree to our{' '}
                <a href="/terms-and-conditions" style={S.link}>Terms</a> and{' '}
                <a href="/privacy-policy" style={S.link}>Privacy Policy</a>.
              </p>
            </>
          )}

          {stage === 'otp' && (
            <>
              <div style={S.otpRow} onPaste={handleOtpPaste}>
                {otp.map((v, i) => (
                  <input
                    key={i}
                    ref={el => (otpRefs.current[i] = el)}
                    type="tel"
                    inputMode="numeric"
                    maxLength={1}
                    value={v}
                    onChange={e => handleOtpInput(i, e.target.value)}
                    onKeyDown={e => handleOtpKey(i, e)}
                    className="otp-box"
                    style={S.otpBox}
                  />
                ))}
              </div>

              <button onClick={verifyOtp} disabled={loading} style={{ ...S.primaryBtn, opacity: loading ? 0.6 : 1, cursor: loading ? 'not-allowed' : 'pointer' }}>
                {loading ? 'Verifying…' : 'Verify & Continue'}
              </button>

              <div style={S.backRow}>
                <button onClick={() => { setStage('mobile'); setOtp(['', '', '', '']); show(''); setTimer(0); }} style={S.linkBtn}>
                  ← Change number
                </button>
                {timer > 0 ? (
                  <span style={{ color: C.ink3, fontSize: 13 }}>Resend in <b style={{ color: C.ink2 }}>{timer}s</b></span>
                ) : (
                  <button onClick={sendOtp} disabled={loading} style={{ ...S.linkBtn, color: C.maroon2, fontWeight: 600 }}>Resend OTP</button>
                )}
              </div>
            </>
          )}

          {msg && (
            <div style={{
              ...S.msg,
              background: msgKind === 'error' ? C.red50 : msgKind === 'success' ? C.green50 : '#f9fafb',
              color: msgKind === 'error' ? '#b91c1c' : msgKind === 'success' ? C.green700 : C.ink2,
              borderColor: msgKind === 'error' ? C.red100 : msgKind === 'success' ? C.green100 : C.line,
            }}>
              {msg}
            </div>
          )}
        </div>
      </div>
        )}
      </div>
    </>
  );
}

const spinnerKeyframes = `@keyframes mg-spin{to{transform:rotate(360deg)}}`;
const globalCss = `
  .otp-box:focus { border-color: ${C.maroon2} !important; box-shadow: 0 0 0 3px ${C.red100}; outline: none; }
  .mg-primary:hover:not(:disabled) { background: ${C.maroonDk} !important; }
  button[aria-label="Skip verification"]:hover { background: rgba(0,0,0,0.08) !important; color: #111827 !important; border-color: rgba(0,0,0,0.12) !important; }
  .mg-mobile-brand { display: flex; }
  @media (max-width: 520px) {
    .mg-card { aspect-ratio: auto !important; min-height: 100%; }
  }
`;

const S = {
  blurLayer: {
    position: 'fixed', inset: 0, zIndex: 9998,
    overflow: 'hidden',
    filter: 'blur(4px) saturate(0.9)',
    pointerEvents: 'none',
    userSelect: 'none',
  },
  wrap: {
    position: 'fixed', inset: 0, zIndex: 9999,
    minHeight: '100vh',
    display: 'flex', alignItems: 'center', justifyContent: 'center',
    padding: 16, overflowY: 'auto',
    background: 'rgba(17, 24, 39, 0.55)',
    backdropFilter: 'blur(2px)',
    WebkitBackdropFilter: 'blur(2px)',
    fontFamily: `'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif`,
  },
  card: {
    position: 'relative',
    width: '100%', maxWidth: 460,
    background: '#fff',
    borderRadius: 24,
    boxShadow: '0 30px 80px rgba(0,0,0,0.15), 0 6px 18px rgba(0,0,0,0.06)',
    overflow: 'hidden',
    display: 'flex',
  },
  left: {
    position: 'relative',
    padding: 40,
    color: '#fff',
    background: `linear-gradient(135deg, ${C.maroon} 0%, ${C.maroon2} 45%, ${C.maroon3} 100%)`,
    display: 'flex', flexDirection: 'column', justifyContent: 'space-between',
    overflow: 'hidden', minHeight: 520,
  },
  leftOrnament: {
    position: 'absolute', inset: 0, pointerEvents: 'none',
    backgroundImage:
      'radial-gradient(circle at 20% 20%, rgba(255,255,255,0.10) 0, transparent 35%),' +
      'radial-gradient(circle at 85% 85%, rgba(255,255,255,0.07) 0, transparent 40%)',
  },
  logoBadge: {
    width: 56, height: 56, borderRadius: 16,
    background: 'rgba(255,255,255,0.15)', border: '1px solid rgba(255,255,255,0.25)',
    display: 'flex', alignItems: 'center', justifyContent: 'center',
    fontSize: 22, fontWeight: 800, letterSpacing: 1,
    backdropFilter: 'blur(8px)',
  },
  brandTitle: {
    marginTop: 24, fontSize: 30, fontWeight: 800, lineHeight: 1.15, color: '#fff',
  },
  brandSub: {
    marginTop: 12, fontSize: 14, lineHeight: 1.6, color: 'rgba(255,255,255,0.85)', maxWidth: 320,
  },
  trustList: { listStyle: 'none', padding: 0, margin: '32px 0 0', position: 'relative', zIndex: 1 },
  trustItem: { display: 'flex', alignItems: 'flex-start', gap: 8, fontSize: 14, color: 'rgba(255,255,255,0.92)', marginBottom: 10 },
  tick: { color: C.gold, fontWeight: 700 },
  footerNote: { fontSize: 12, color: 'rgba(255,255,255,0.6)', position: 'relative', zIndex: 1 },
  right: {
    flex: 1, padding: 32,
    display: 'flex', flexDirection: 'column',
  },
  mobileBrand: {
    alignItems: 'center', gap: 10, marginBottom: 16,
    paddingBottom: 12, borderBottom: `1px solid ${C.line}`,
    flexShrink: 0,
  },
  mobileBrandBadge: {
    width: 40, height: 40, borderRadius: 10, flexShrink: 0,
    background: `linear-gradient(135deg, ${C.maroon} 0%, ${C.maroon3} 100%)`,
    color: '#fff', display: 'flex', alignItems: 'center', justifyContent: 'center',
    fontSize: 14, fontWeight: 800, letterSpacing: 1,
  },
  mobileBrandTitle: { fontSize: 15, fontWeight: 700, color: C.ink, lineHeight: 1.2 },
  mobileBrandSub: { fontSize: 11, fontWeight: 500, color: C.ink3, letterSpacing: 0.5, marginTop: 2 },
  stepRow: { display: 'flex', alignItems: 'center', gap: 6, marginBottom: 14, flexShrink: 0 },
  stepPill: { height: 5, width: 32, borderRadius: 999, transition: 'background .2s' },
  stepLabel: { marginLeft: 6, fontSize: 11, fontWeight: 600, letterSpacing: 1.5, color: C.ink3, textTransform: 'uppercase' },
  title: { margin: 0, fontSize: 24, fontWeight: 800, color: C.ink },
  subtitle: { margin: '6px 0 18px', fontSize: 13, color: C.ink3, lineHeight: 1.5, flexShrink: 0 },
  label: { display: 'block', fontSize: 11, fontWeight: 700, letterSpacing: 1.5, color: C.ink3, marginBottom: 6, flexShrink: 0 },
  inputRow: {
    display: 'flex', alignItems: 'stretch', flexShrink: 0,
    border: `1.5px solid ${C.line2}`, borderRadius: 12, overflow: 'hidden',
    transition: 'border-color .15s, box-shadow .15s',
  },
  prefix: { padding: '12px 16px', background: '#f9fafb', color: C.ink2, fontWeight: 600, fontSize: 15, borderRight: `1px solid ${C.line}`, display: 'flex', alignItems: 'center', flexShrink: 0 },
  input: { flex: 1, padding: '12px 16px', fontSize: 16, border: 'none', outline: 'none', background: '#fff', color: C.ink, minWidth: 0 },
  primaryBtn: {
    width: '100%', marginTop: 20, flexShrink: 0,
    padding: '13px 16px', borderRadius: 12, border: 'none',
    background: C.maroon2, color: '#fff',
    fontSize: 15, fontWeight: 700, letterSpacing: 0.2,
    cursor: 'pointer', transition: 'background .15s, transform .05s',
    boxShadow: '0 1px 2px rgba(0,0,0,0.05)',
  },
  termsNote: { marginTop: 20, fontSize: 12, color: C.ink4, textAlign: 'center', lineHeight: 1.6 },
  link: { color: C.maroon2, textDecoration: 'none', fontWeight: 500 },
  otpRow: { display: 'flex', justifyContent: 'center', gap: 12, marginBottom: 20 },
  otpBox: {
    width: 56, height: 56, textAlign: 'center',
    fontSize: 24, fontWeight: 700,
    border: `2px solid ${C.line2}`, borderRadius: 12,
    outline: 'none', background: '#fff', color: C.ink,
    transition: 'border-color .15s, box-shadow .15s',
  },
  backRow: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 18, fontSize: 13 },
  linkBtn: { background: 'none', border: 'none', padding: 0, color: C.ink2, cursor: 'pointer', fontSize: 13, fontWeight: 500 },
  msg: { marginTop: 18, padding: '10px 14px', borderRadius: 10, fontSize: 13, textAlign: 'center', border: '1px solid transparent' },
  spinner: { width: 40, height: 40, borderRadius: '50%', border: `4px solid ${C.red100}`, borderTopColor: C.maroon2, animation: 'mg-spin 0.9s linear infinite' },
};
