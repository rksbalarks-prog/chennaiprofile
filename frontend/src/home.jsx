import React, { useState, useEffect, useRef } from 'react';
import { useTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { API_BASE, PHOTO_BASE, PHOTO_BASE_OLD, UPLOADS_PREFIX, USER_PANEL_URL, getPhotoUrls } from './config';
import { buildSummary } from './profileSummary';

const mapP = (p) => ({
  id: p.cp_id, cpId: p.cp_id, name: p.name || 'N/A',
  caste: p.caste || '', gender: p.gender || '',
  language: p.mother_tongue || '', religion: p.religion || '',
  marital: p.marital || '', age: p.age || '',
  phone: p.mobile || '', qualification: p.qualification || '',
  job: p.job || '', height: p.height || '', star: p.star || '', raasi: p.raasi || '',
  area: p.present_area || '', city: p.present_city || '', district: p.present_district || '', state: p.present_state || '',
  permAddress: p.perm_address || '',
  matchScore: p.match_score || 0,
  photo: p.photo1 && !p.photo1.startsWith('default_')
    ? (p.photo1.startsWith('http') ? p.photo1
      : p.photo1.startsWith('/') ? p.photo1
      : p.photo1.startsWith('uploads/') ? UPLOADS_PREFIX + p.photo1
      : PHOTO_BASE + p.photo1)
    : '',
  photoFallback: p.photo1 && !p.photo1.startsWith('default_') && !p.photo1.startsWith('http') && !p.photo1.startsWith('/')
    ? PHOTO_BASE_OLD + p.photo1 : '',
});

const sortPhotosFirst = (arr) => {
  const w = arr.filter(p => p.photo);
  const wo = arr.filter(p => !p.photo);
  return [...w, ...wo];
};

export default function Home() {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();

  const [sections, setSections] = useState({ interest:[], preference:[], withPhotos:[], notViewed:[], viewed:[], others:[] });
  const [allProfiles, setAllProfiles] = useState([]);
  const [maleCount, setMaleCount] = useState(0);
  const [femaleCount, setFemaleCount] = useState(0);
  // Display helper: cap any profile count at "999+" so the badges stay compact.
  const fmtCount = (n) => (n > 999 ? '999+' : n);
  const [activeTab, setActiveTab] = useState('all'); // 'all' | 'bride' | 'groom'
  const [feedFilter, setFeedFilter] = useState('recent'); // 'recent' | 'random' | 'photos' | 'notViewed'
  const [contactVerified, setContactVerified] = useState(false);
  const [userMobile, setUserMobile] = useState('');
  const [hasUserProfile, setHasUserProfile] = useState(false);
  const [showOtpModal, setShowOtpModal] = useState(false);
  const [otpMobile, setOtpMobile] = useState('');
  const [otpValue, setOtpValue] = useState(['','','','']);
  const [otpSent, setOtpSent] = useState(false);
  const [otpMsg, setOtpMsg] = useState('');
  const [otpLoading, setOtpLoading] = useState(false);
  const [otpTimer, setOtpTimer] = useState(0);
  const [otpIntent, setOtpIntent] = useState('contact');
  const [revealedContactId, setRevealedContactId] = useState(null);
  const [revealedPhones, setRevealedPhones] = useState({}); // { [cpId]: mobile }
  const [pendingContactId, setPendingContactId] = useState(null);
  const [limitMsg, setLimitMsg] = useState(null);
  // Anonymous-visitor gate state. Server is the source of truth — these fields
  // exist purely to drive UI hints ("4 of 5 free views used"). Updated from
  // the bootstrap response and from each track_view response.
  const [gateState, setGateState] = useState({ returning:false, anonViewsUsed:0, anonViewsLimit:5, gateRequired:false, anonWindowSec:24*3600, anonWindowStart:0 });
  // Banner text shown at the top of the OTP modal when it was opened because
  // of the gate (free-limit reached or returning user). Keeps the styling
  // separate from otpMsg, which is wired to the success/error indicator.
  const [gatePromptMsg, setGatePromptMsg] = useState('');
  // 24-hour "to get your 5 free contacts" countdown. The window starts the
  // first time the gate modal opens and persists in localStorage. The user
  // can dismiss the popup; if they try another contact before the timer
  // finishes the popup re-opens showing the remaining balance time. After
  // the timer expires the localStorage entry is cleared so the next gate
  // hit arms a fresh 24-hour window.
  const GATE_WINDOW_KEY = 'gate_window_until';
  const GATE_WINDOW_MS  = 24 * 60 * 60 * 1000;
  const [nowTick, setNowTick] = useState(Date.now());
  const [searchQuery, setSearchQuery] = useState('');
  const [visibleCards, setVisibleCards] = useState(20);
  // Server-side pagination state for infinite scroll
  const PAGE_SIZE = 20;
  // Fresh per-visit shuffle seed. The server uses this to return profiles in a
  // deterministic pseudo-random order across the FULL approved-with-photo set,
  // consistent across paginated requests so Load More walks the same global shuffle.
  const [shuffleSeed] = useState(() => Math.random().toString(36).slice(2, 12));
  const [loadingMore, setLoadingMore] = useState(false);
  const [maleOffset, setMaleOffset] = useState(PAGE_SIZE);
  const [femaleOffset, setFemaleOffset] = useState(PAGE_SIZE);
  const [maleExhausted, setMaleExhausted] = useState(false);
  const [femaleExhausted, setFemaleExhausted] = useState(false);
  const [showReportModal, setShowReportModal] = useState(false);
  const [reportProfileId, setReportProfileId] = useState(null);
  const [reportReason, setReportReason] = useState('');

  // Bootstrap (runs once): verification + male/female profile lists
  useEffect(() => {
    const init = async () => {
      let boot;
      try {
        boot = await fetch(`${API_BASE}?action=bootstrap&limit=${PAGE_SIZE}&seed=${shuffleSeed}`, { credentials:'include' }).then(r=>r.json());
      } catch (e) { boot = { ok:false }; }

      if (boot?.ok && boot.contact?.verified) {
        setContactVerified(true);
        setUserMobile(boot.contact.mobile || '');
      }
      if (boot?.ok && boot.contact) {
        setGateState({
          returning:        !!boot.contact.returning,
          anonViewsUsed:    boot.contact.anon_views_used   ?? 0,
          anonViewsLimit:   boot.contact.anon_views_limit  ?? 5,
          gateRequired:     !!boot.contact.gate_required,
          anonWindowSec:    boot.contact.anon_window_sec   ?? 24*3600,
          anonWindowStart:  boot.contact.anon_window_start ?? 0,
        });
      }
      setMaleCount(boot?.male?.total || 0);
      setFemaleCount(boot?.female?.total || 0);
      const allM = ((boot?.male?.profiles) || []).map(mapP);
      const allF = ((boot?.female?.profiles) || []).map(mapP);
      setAllProfiles([...allF, ...allM]);
      if (allM.length < PAGE_SIZE) setMaleExhausted(true);
      if (allF.length < PAGE_SIZE) setFemaleExhausted(true);
    };
    init();
  }, []);

  // Suggestions loader — refetches when the active tab changes.
  // target_gender tells the server which gender to surface:
  //   - If the user has a registered profile, the server locks gender to the opposite
  //     of their confirmed profile gender (target_gender is ignored).
  //   - If verified-only (no profile row), the server uses target_gender to pick
  //     which gender's "All Profiles" fallback to return.
  // Effect A — fetch personalized suggestions. Runs ONCE per user/tab change,
  // NOT on every allProfiles update. Without this split, each Load More would
  // refetch suggestions and overwrite sections, discarding the newly-appended
  // profiles and leaving the feed stuck at its initial size.
  useEffect(() => {
    if (!userMobile) return;               // incognito path handled in Effect B
    if (allProfiles.length === 0) return;  // wait for bootstrap
    const targetGender = activeTab === 'groom' ? 'Male' : 'Female';

    (async () => {
      try {
        const sg = await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ action:'suggestions', mobile: userMobile, target_gender: targetGender }),
          credentials:'include' }).then(r=>r.json());
        if (!sg.ok) return;

        const interest = sortPhotosFirst((sg.interest || []).map(mapP));
        const preference = sortPhotosFirst((sg.preference || []).map(mapP));
        const withPhotos = sortPhotosFirst((sg.withPhotos || []).map(mapP));
        const notViewed = sortPhotosFirst((sg.notViewed || []).map(mapP));
        const viewed = sortPhotosFirst((sg.viewed || []).map(mapP));
        const allP = sortPhotosFirst((sg.allProfiles || []).map(mapP));
        const totalSuggested = interest.length + preference.length + withPhotos.length + notViewed.length + viewed.length;

        if (totalSuggested > 0) {
          setHasUserProfile(interest.length > 0 || preference.length > 0);
          setSections(prev => {
            const usedIds = new Set([...interest, ...preference, ...withPhotos, ...notViewed, ...viewed].map(p => p.id));
            // Start others from current allProfiles minus personalized sections.
            // Effect B will keep it in sync as more profiles arrive.
            const others = sortPhotosFirst(allProfiles.filter(p => !usedIds.has(p.id)));
            return { interest, preference, withPhotos, notViewed, viewed, others };
          });
        } else if (allP.length > 0) {
          setHasUserProfile(false);
          setSections({ interest:[], preference:[], withPhotos: allP, notViewed:[], viewed:[], others:[] });
        } else {
          const shuffle = (arr) => { const a=[...arr]; for(let i=a.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[a[i],a[j]]=[a[j],a[i]];} return a; };
          setSections({ interest:[], preference:[], withPhotos: shuffle(allProfiles.filter(p => p.photo)), notViewed:[], viewed:[], others: shuffle(allProfiles.filter(p => !p.photo)) });
        }
      } catch (e) { console.error('Suggestions error:', e); }
    })();
  }, [userMobile, activeTab]);

  // Effect B — append new profiles into the feed on every Load More.
  // For incognito: server returns rows already in global random order (seeded
  // shuffle across the FULL approved-with-photo DB), so we just append stably.
  // For logged-in: append into `others` (suggestion sections stay server-sourced).
  useEffect(() => {
    if (allProfiles.length === 0) return;
    if (!userMobile) {
      setSections(prev => {
        const seenPhoto = new Set(prev.withPhotos.map(p => p.id));
        const seenOther = new Set(prev.others.map(p => p.id));
        const newPhoto  = allProfiles.filter(p => p.photo && !seenPhoto.has(p.id));
        const newOther  = allProfiles.filter(p => !p.photo && !seenOther.has(p.id));
        if (newPhoto.length === 0 && newOther.length === 0) return prev;
        return {
          interest: [], preference: [], notViewed: [], viewed: [],
          withPhotos: [...prev.withPhotos, ...newPhoto],
          others:     [...prev.others,     ...newOther],
        };
      });
      return;
    }
    // Logged-in: only grow `others`. Sections from the suggestions API are
    // server-authoritative and should not be mutated here.
    setSections(prev => {
      const usedIds = new Set([
        ...prev.interest, ...prev.preference, ...prev.withPhotos,
        ...prev.notViewed, ...prev.viewed, ...prev.others,
      ].map(p => p.id));
      const newOthers = allProfiles.filter(p => !usedIds.has(p.id));
      if (newOthers.length === 0) return prev;
      return { ...prev, others: [...prev.others, ...sortPhotosFirst(newOthers)] };
    });
  }, [allProfiles, userMobile]);

  useEffect(() => { if(otpTimer<=0)return; const t=setTimeout(()=>setOtpTimer(otpTimer-1),1000); return()=>clearTimeout(t); }, [otpTimer]);

  // 1Hz tick that drives the "Verify or Reset in HH:MM:SS" countdown while
  // the gate modal is visible. No-op when the modal is closed.
  useEffect(() => {
    if (!showOtpModal) return;
    const id = setInterval(() => setNowTick(Date.now()), 1000);
    return () => clearInterval(id);
  }, [showOtpModal]);

  // Server-side pagination: fetch next page for the active tab's gender.
  // For 'all' tab, alternate to whichever side is less loaded so the
  // chronological feed stays balanced. Falls through to the other gender
  // if one side is exhausted.
  const loadMoreFromServer = async () => {
    if (loadingMore) return;
    let pickGroom;
    if (activeTab === 'all') {
      if (maleExhausted && femaleExhausted) return;
      if (maleExhausted) pickGroom = false;
      else if (femaleExhausted) pickGroom = true;
      else pickGroom = maleOffset <= femaleOffset; // load the side that has fewer rows yet
    } else {
      pickGroom = activeTab === 'groom';
      if (pickGroom ? maleExhausted : femaleExhausted) return;
    }
    const gender = pickGroom ? 'Male' : 'Female';
    const offset = pickGroom ? maleOffset : femaleOffset;
    setLoadingMore(true);
    try {
      const r = await fetch(`${API_BASE}?action=search&gender=${gender}&limit=${PAGE_SIZE}&offset=${offset}&seed=${shuffleSeed}&photo=with`).then(r=>r.json());
      const batch = (r.ok ? r.profiles : []).map(mapP);
      if (batch.length > 0) setAllProfiles(prev => [...prev, ...batch]);
      if (batch.length < PAGE_SIZE) {
        if (pickGroom) setMaleExhausted(true); else setFemaleExhausted(true);
      } else {
        if (pickGroom) setMaleOffset(o => o + PAGE_SIZE); else setFemaleOffset(o => o + PAGE_SIZE);
      }
    } catch (e) { console.error('loadMore error:', e); }
    setLoadingMore(false);
  };

  const sendOtp = async () => {
    if (!/^\d{10}$/.test(otpMobile)){setOtpMsg('Enter valid 10-digit mobile');return;}
    setOtpLoading(true);setOtpMsg('');
    try{const r=await fetch(API_BASE,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'contact_otp_send',mobile:otpMobile}),credentials:'include'}).then(r=>r.json());
      if(r.ok){if(r.auto_verified){setContactVerified(true);setShowOtpModal(false);setGatePromptMsg('');if(otpIntent==='register')window.location.href=`${USER_PANEL_URL}?create=1`;else if(pendingContactId){const pid=pendingContactId;setPendingContactId(null);handleViewContact(pid);}return;}setOtpSent(true);setOtpTimer(120);setOtpMsg(r.otp?`OTP: ${r.otp}`:'OTP sent');}else setOtpMsg(r.error||'Failed');
    }catch(e){setOtpMsg('Network error');}setOtpLoading(false);
  };
  const verifyOtp = async () => {
    const otp=otpValue.join('');if(otp.length!==4){setOtpMsg('Enter 4-digit OTP');return;}setOtpLoading(true);setOtpMsg('');
    try{const r=await fetch(API_BASE,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'contact_otp_verify',mobile:otpMobile,otp}),credentials:'include'}).then(r=>r.json());
      if(r.ok&&r.verified){setContactVerified(true);setShowOtpModal(false);setGatePromptMsg('');if(otpIntent==='register')window.location.href=`${USER_PANEL_URL}?create=1`;else if(pendingContactId){const pid=pendingContactId;setPendingContactId(null);handleViewContact(pid);}}else setOtpMsg(r.error||'Invalid OTP');
    }catch(e){setOtpMsg('Network error');}setOtpLoading(false);
  };
  const handleOtpInput=(i,val)=>{if(!/^\d?$/.test(val))return;const n=[...otpValue];n[i]=val;setOtpValue(n);if(val&&i<3)document.getElementById(`home-otp-${i+1}`)?.focus();};

  // Open the OTP modal because the server-side gate blocked a contact view.
  // gate_reason is either 'returning_user' or 'free_limit_reached'.
  const openGateModal = (profileId, reason, limit) => {
    // Arm the 24-hour reset window the first time the gate fires, or refresh
    // it if a previous window has already expired.
    const existing = parseInt(localStorage.getItem(GATE_WINDOW_KEY) || '0', 10);
    if (!existing || Date.now() >= existing) {
      localStorage.setItem(GATE_WINDOW_KEY, String(Date.now() + GATE_WINDOW_MS));
    }
    setPendingContactId(profileId);
    setOtpIntent('view');
    setOtpMobile(''); setOtpValue(['','','','']); setOtpSent(false); setOtpMsg('');
    setGatePromptMsg(reason === 'returning_user'
      ? 'Welcome back. Please verify your mobile to continue viewing contacts.'
      : `You've used your ${limit || 5} free contact views. Verify your mobile to keep viewing contacts.`);
    setShowOtpModal(true);
  };
  // Skip handler shared by the top-right "✕" and the countdown button below
  // Send OTP. Just closes the modal — the 24-hour window keeps running so the
  // next gate hit re-opens the popup showing the remaining balance time.
  const skipGateModal = () => {
    setShowOtpModal(false);
    setGatePromptMsg('');
  };

  const handleViewContact = async (profileId) => {
    // Verified users still face their plan-based limits (day/month/total) —
    // those are independent of the anonymous 5-view gate.
    if (contactVerified) {
      try {
        const chk = await fetch(API_BASE,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'contact_check'}),credentials:'include'}).then(r=>r.json());
        const mobile = chk.mobile||'';
        if (mobile) {
          const lim = await fetch(API_BASE,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'user_limits',mobile}),credentials:'include'}).then(r=>r.json());
          if (lim.ok) {
            const {limits,used}=lim;
            if(limits.day>0&&used.day>=limits.day){setLimitMsg({title:'Daily Limit Reached',desc:`You have used all ${limits.day} contact views for today.`,sub:'Try again tomorrow.'});return;}
            if(limits.month>0&&used.month>=limits.month){setLimitMsg({title:'Monthly Limit Reached',desc:`You have used all ${limits.month} contact views this month.`,sub:'Limit resets next month.'});return;}
            if(limits.total>0&&used.total>=limits.total){setLimitMsg({title:'Total Limit Reached',desc:`You have used all ${limits.total} lifetime contact views.`,sub:'Upgrade your plan for more.'});return;}
          }
        }
      } catch(e) {}
    }

    // Server is the source of truth for the anon gate AND for fetching the
    // target's mobile. A gated request returns 403 with gate_required=true.
    try {
      const res = await fetch(API_BASE,{
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'track_view',target_cp_id:profileId,type:'contact_view'}),
        credentials:'include',
      });
      const tv = await res.json().catch(()=>({}));

      // Keep the local quota indicator in sync.
      if (tv && tv.anon_views_used != null) {
        setGateState(g => ({
          ...g,
          returning:        !!tv.returning,
          anonViewsUsed:    tv.anon_views_used,
          anonViewsLimit:   tv.anon_views_limit ?? g.anonViewsLimit,
          gateRequired:     !!tv.gate_required,
          anonWindowSec:    tv.anon_window_sec   ?? g.anonWindowSec,
          anonWindowStart:  tv.anon_window_start ?? g.anonWindowStart,
        }));
      }

      if (!res.ok && tv && tv.gate_required) {
        openGateModal(profileId, tv.gate_reason, tv.anon_views_limit);
        return;
      }
      if (tv && tv.mobile) setRevealedPhones(prev => ({ ...prev, [profileId]: tv.mobile }));
    } catch(e) {}
    setRevealedContactId(profileId);
  };

  // Get profiles for active tab AND search filter.
  // For registered users the server already returns opposite-gender-only
  // profiles in `sections` (gender is locked to !me.gender), so we skip the
  // tab gender filter to avoid an empty feed when activeTab happens to match
  // the user's own gender.
  const getTabProfiles = (list) => {
    const q = searchQuery.trim().toLowerCase();
    return list.filter(p => {
      if (!hasUserProfile) {
        if (activeTab === 'groom' && p.gender !== 'Male') return false;
        if (activeTab === 'bride' && p.gender !== 'Female') return false;
        // 'all' → no gender filter
      }
      if (q) {
        const name = (p.name || '').toLowerCase();
        const cpId = (p.cpId || '').toLowerCase();
        if (!name.includes(q) && !cpId.includes(q)) return false;
      }
      return true;
    });
  };

  // Pick a random blurred photo from same-gender profiles (deterministic by id)
  const getRandomPhoto = (gender, id) => {
    const pool = allProfiles.filter(pr => pr.gender === gender && pr.photo);
    if (pool.length === 0) return gender === 'Male' ? '/default-male.svg' : '/default-female.svg';
    const hash = (id || '').split('').reduce((a, c) => a + c.charCodeAt(0), 0);
    return pool[hash % pool.length].photo;
  };

  const getLocation = (p) => {
    const structured = [p.area, p.city, p.district, p.state].filter(Boolean).join(', ');
    if (structured) return structured;
    if (p.permAddress) {
      const parts = p.permAddress.split(',').map(s => s.trim()).filter(Boolean);
      if (parts.length) return parts.slice(-2).join(', ');
    }
    return 'South Arcot District';
  };

  const ProfileCard = ({ p }) => {
    const [slide, setSlide] = useState(0); // 0 = details, 1 = brief summary
    const summary = slide === 1 ? buildSummary(p) : null;
    const isTa = i18n.language === 'ta';
    const briefText = summary ? (isTa ? summary.ta : summary.en) : '';

    return (
    <div style={{ position:'relative', display:'flex', background:'#fff', borderRadius:12, overflow:'hidden', boxShadow:'0 1px 6px rgba(0,0,0,0.06)', border:'1px solid #f0f0f0', cursor:'pointer' }}
      onMouseEnter={() => setSlide(1)}
      onMouseLeave={() => setSlide(0)}
      onClick={() => navigate(`/detail/${p.id}`, { state: { profile: p } })}>
      {(() => {
        const svgFallback = p.gender === 'Male' ? '/default-male.png' : '/default-female.png';
        // When the profile has no photo, show a random blurred same-gender
        // live photo so the card feels "alive" instead of a flat silhouette.
        const hasPhoto = !!p.photo;
        const photoSrc = hasPhoto ? p.photo : getRandomPhoto(p.gender, p.id);
        const isSvg = typeof photoSrc === 'string' && photoSrc.endsWith('.svg');
        const urls = (!isSvg && photoSrc) ? getPhotoUrls(photoSrc.replace(UPLOADS_PREFIX, '').replace(PHOTO_BASE, '').replace(PHOTO_BASE_OLD, '')) : null;
        return (
          <div style={{ width:100, height:120, flexShrink:0, overflow:'hidden', background:`#f0f0f0 url(${svgFallback}) center/60% no-repeat` }}>
            <picture>
              {urls && <source type="image/webp" srcSet={urls.thumb} />}
              <img
                src={urls ? urls.orig : svgFallback}
                alt=""
                loading="lazy" decoding="async"
                width="100" height="120"
                style={{
                  width:'100%', height:'100%', objectFit:'cover', display:'block',
                  // Blur + scale slightly so edge-pixels don't leak when hasPhoto=false
                  filter: hasPhoto ? 'none' : 'blur(10px) saturate(0.9)',
                  transform: hasPhoto ? 'none' : 'scale(1.15)',
                }}
                onError={e => {
                  if (e.target.dataset.fb === '2') return;
                  if (!e.target.dataset.fb && hasPhoto && p.photoFallback) { e.target.dataset.fb = '1'; e.target.src = p.photoFallback; return; }
                  e.target.dataset.fb = '2'; e.target.src = svgFallback;
                }}
              />
            </picture>
          </div>
        );
      })()}
      <div style={{ flex:1, padding:'8px 10px', display:'flex', flexDirection:'column', gap:2, minWidth:0, position:'relative' }}>
        {/* Carousel swap arrow — top-right of the right pane */}
        <button
          type="button"
          aria-label={slide === 0 ? 'Show brief summary' : 'Show details'}
          onClick={e => { e.stopPropagation(); setSlide(s => (s === 0 ? 1 : 0)); }}
          style={{ position:'absolute', top:6, right:6, width:22, height:22, borderRadius:'50%', background:'#fff', border:'1px solid #e5e7eb', color:'#8B0000', display:'inline-flex', alignItems:'center', justifyContent:'center', cursor:'pointer', padding:0, zIndex:1, boxShadow:'0 1px 2px rgba(0,0,0,0.06)' }}
        >
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            {slide === 0
              ? <><polyline points="9 18 15 12 9 6"/></>
              : <><polyline points="15 18 9 12 15 6"/></>}
          </svg>
        </button>

        {slide === 0 ? (
          <>
            <div style={{ fontSize:13, fontWeight:700, color:'#222', overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap', paddingRight:26 }}>{p.name}</div>
            <div style={{ display:'flex', alignItems:'center', gap:5, flexWrap:'wrap' }}>
              <span style={{ fontSize:10, color:'#8B0000', fontWeight:600, background:'#fef2f2', padding:'1px 6px', borderRadius:3 }}>{p.cpId}</span>
              <span style={{ fontSize:10, color:'#8B0000', fontWeight:600, cursor:'pointer' }} onClick={e=>{e.stopPropagation();navigate(`/detail/${p.id}`,{state:{profile:p}});}}>View →</span>
              <span
                role="button"
                tabIndex={0}
                onClick={e=>{e.stopPropagation();setReportProfileId(p.cpId);setReportReason('');setShowReportModal(true);}}
                style={{ fontSize:10, fontWeight:700, color:'#b91c1c', background:'#fef2f2', border:'1px solid #fecaca', padding:'1px 7px', borderRadius:10, cursor:'pointer', display:'inline-flex', alignItems:'center', gap:3, lineHeight:1 }}>
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#b91c1c" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                  <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                  <line x1="12" y1="9" x2="12" y2="13"/>
                  <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
                Flag
              </span>
            </div>
            <div style={{ fontSize:11, color:'#777' }}>{[p.age?p.age+' yrs':'',p.height,getLocation(p)].filter(Boolean).join(' · ')}</div>
            <div style={{ fontSize:11, color:'#777' }}>{[p.qualification,p.job].filter(Boolean).join(' · ')}</div>
            <div style={{ display:'flex', gap:3, flexWrap:'wrap', marginTop:'auto' }}>
              {p.caste && <span style={{ fontSize:9, fontWeight:600, padding:'1px 6px', borderRadius:8, background:'#f5f0ff', color:'#6d28d9' }}>{p.caste}</span>}
              {p.religion && <span style={{ fontSize:9, fontWeight:600, padding:'1px 6px', borderRadius:8, background:'#fff7ed', color:'#c2410c' }}>{p.religion}</span>}
              {p.star && <span style={{ fontSize:9, fontWeight:600, padding:'1px 6px', borderRadius:8, background:'#f0fdf4', color:'#166534' }}>{p.star}</span>}
            </div>
          </>
        ) : (
          <>
            <div style={{ fontSize:13, fontWeight:700, color:'#222', overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap', paddingRight:26 }}>
              {p.name} <span style={{ fontSize:10, color:'#8B0000', fontWeight:600, background:'#fef2f2', padding:'1px 6px', borderRadius:3, marginLeft:4 }}>{p.cpId}</span>
            </div>
            <div
              onClick={e => e.stopPropagation()}
              style={{ fontSize:10.5, lineHeight:1.5, color:'#374151', background:'#fffbeb', border:'1px solid #fde68a', borderRadius:6, padding:'5px 7px', overflow:'hidden', display:'-webkit-box', WebkitLineClamp:4, WebkitBoxOrient:'vertical', flex:1, marginTop:1 }}
            >
              {briefText}
            </div>
            <div style={{ marginTop:2 }}>
              <a
                href={`/detail/${p.id}`}
                onClick={e => { e.stopPropagation(); e.preventDefault(); navigate(`/detail/${p.id}`, { state: { profile: p } }); }}
                style={{ fontSize:10.5, fontWeight:700, color:'#8B0000', textDecoration:'none' }}
              >
                {isTa ? 'மேலும் படிக்க →' : 'Read more →'}
              </a>
            </div>
          </>
        )}

        {/* Contact button — always visible across both slides */}
        <div style={{ display:'flex', marginTop:4, minWidth:0, width:'100%' }}>
          {revealedContactId === p.id ? (() => {
            const num = revealedPhones[p.id] || p.phone || '';
            return (
              <a href={`tel:${num}`} onClick={e=>e.stopPropagation()} style={{ flex:'1 1 0', minWidth:0, padding:'5px 0', background:'#1a6ea8', color:'#fff', borderRadius:6, fontSize:11, fontWeight:700, textAlign:'center', textDecoration:'none', display:'block', overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>
                {num ? `Call: ${num}` : 'Number unavailable'}
              </a>
            );
          })() : (
            <button onClick={e=>{e.stopPropagation();handleViewContact(p.id);}} style={{ flex:'1 1 0', minWidth:0, padding:'5px 4px', background:'linear-gradient(135deg,#16a34a,#15803d)', color:'#fff', border:'none', borderRadius:6, fontSize:11, fontWeight:700, cursor:'pointer', overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>
              View Free Contact
            </button>
          )}
        </div>
      </div>
    </div>
    );
  };

  // Build flat feed: 200 photo profiles first, then "View More" expands the rest
  const [showMore, setShowMore] = useState(false);

  const buildFeed = () => {
    const feed = [];
    const addSection = (title, icon, profiles, limit, countOverride) => {
      const filtered = getTabProfiles(profiles);
      if (filtered.length === 0) return;
      const shown = limit ? filtered.slice(0, limit) : filtered;
      feed.push({ type:'header', title, icon, count: countOverride != null ? countOverride : filtered.length });
      shown.forEach(p => feed.push({ type:'card', ...p }));
      if (limit && filtered.length > limit) {
        feed.push({ type:'viewmore', remaining: filtered.length - limit });
      }
    };
    addSection('Based on Your Interest', '🔥', sections.interest.filter(p => p.photo));
    addSection('Partner Preference Match', '💝', sections.preference.filter(p => p.photo));

    // Primary list — driven by the Recent / Random / Photos / Not Viewed
    // filter buttons.
    //
    // Pool choice is tab-driven:
    //   - 'all'           → allProfiles (server DESC, both genders) so
    //                        Recent shows truly chronological entries.
    //   - 'bride'/'groom' → sections.withPhotos, which Effect B shuffles
    //                        once on first load and then appends to. That
    //                        gives a fresh randomised feed per page visit
    //                        (the user's "every new session" requirement)
    //                        without cards jumping around on Load More.
    const rawPool = activeTab === 'all'
      ? allProfiles.filter(p => p.photo)
      : sections.withPhotos;
    // Apply the tab gender + search filter FIRST so subsequent sort/shuffle
    // operates only on the visible-to-this-tab subset. Without this, the
    // mixed-gender pool used for the bride/groom tabs would leak the wrong
    // gender into the feed.
    let primary = getTabProfiles(rawPool);
    if (feedFilter === 'random') {
      const arr = [...primary];
      for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [arr[i], arr[j]] = [arr[j], arr[i]];
      }
      primary = arr;
    } else if (feedFilter === 'photos') {
      primary = primary.filter(p => p.photo);
    } else if (feedFilter === 'notViewed') {
      const viewedIds = new Set(sections.viewed.map(p => p.id));
      primary = primary.filter(p => !viewedIds.has(p.id));
    }
    // 'recent' falls through — primary is already in pool order.
    primary.forEach(p => feed.push({ type:'card', ...p }));
    return feed;
  };
  const feed = buildFeed();
  // Slice the feed so that exactly `visibleCards` *cards* are shown (headers don't count).
  // Drop any trailing header/viewmore that has no cards following it.
  const { visibleFeed, shownCards, totalCards } = (() => {
    const total = feed.filter(f => f.type === 'card').length;
    let count = 0;
    let cutoff = 0;
    for (let i = 0; i < feed.length; i++) {
      if (feed[i].type === 'card') {
        if (count >= visibleCards) break;
        count++;
      }
      cutoff = i + 1;
    }
    let sliced = feed.slice(0, cutoff);
    while (sliced.length && sliced[sliced.length - 1].type !== 'card') sliced.pop();
    return { visibleFeed: sliced, shownCards: count, totalCards: total };
  })();
  const exhausted = activeTab === 'all'
    ? (maleExhausted && femaleExhausted)
    : (activeTab === 'groom' ? maleExhausted : femaleExhausted);
  const hasMore = shownCards < totalCards || !exhausted;

  // Load More: advance by 20 cards; unlock hidden sections on first trigger;
  // fetch from the server when the cached feed is nearly exhausted.
  const handleLoadMore = () => {
    setShowMore(true);
    setVisibleCards(v => v + 20);
    if (!exhausted && shownCards >= totalCards - 10) loadMoreFromServer();
  };

  // Reset pagination on tab or filter change
  useEffect(() => { setVisibleCards(20); setShowMore(false); }, [activeTab, feedFilter]);

  // Infinite-scroll sentinel: when the bottom marker enters view, trigger
  // the same logic the Load More button used to run. Ref wraps the latest
  // handler so the observer callback always sees current state.
  const sentinelRef = useRef(null);
  const loadMoreRef = useRef(handleLoadMore);
  loadMoreRef.current = handleLoadMore;

  useEffect(() => {
    if (!hasMore || loadingMore) return;
    const el = sentinelRef.current;
    if (!el) return;
    const io = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting) loadMoreRef.current();
    }, { rootMargin: '400px 0px' });
    io.observe(el);
    return () => io.disconnect();
  }, [hasMore, loadingMore, visibleCards, totalCards]);

  return (
    <div style={{ background:'#f5f5f5', minHeight:'100vh', paddingBottom:80 }}>

      {/* Filter Buttons — hidden for registered users since the server already
          locks gender to the opposite of their own (tabs would be confusing). */}
      {!hasUserProfile && (
        <div style={{ display:'flex', gap:6, padding:'8px 12px 6px', background:'#fff' }}>
          <button onClick={()=>setActiveTab('all')} style={{ flex:1, padding:'8px 0', borderRadius:20, fontSize:12, fontWeight:600, cursor:'pointer', border:'1.5px solid', display:'flex', alignItems:'center', justifyContent:'center', gap:4,
            background:activeTab==='all'?'#8B0000':'#f5f5f5', color:activeTab==='all'?'#fff':'#666', borderColor:activeTab==='all'?'#8B0000':'#e8e8e8' }}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><circle cx="9" cy="7" r="4"/><circle cx="17" cy="7" r="4"/><path d="M2 21a7 7 0 0 1 14 0"/><path d="M14 21a7 7 0 0 1 8-6.7"/></svg>
            All <span style={{ fontSize:10, fontWeight:700, background:activeTab==='all'?'rgba(255,255,255,0.25)':'rgba(0,0,0,0.06)', padding:'0 5px', borderRadius:8 }}>{fmtCount(maleCount + femaleCount)}</span>
          </button>
          <button onClick={()=>setActiveTab('bride')} style={{ flex:1, padding:'8px 0', borderRadius:20, fontSize:12, fontWeight:600, cursor:'pointer', border:'1.5px solid', display:'flex', alignItems:'center', justifyContent:'center', gap:4,
            background:activeTab==='bride'?'#8B0000':'#f5f5f5', color:activeTab==='bride'?'#fff':'#666', borderColor:activeTab==='bride'?'#8B0000':'#e8e8e8' }}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0"/></svg>
            Female <span style={{ fontSize:10, fontWeight:700, background:activeTab==='bride'?'rgba(255,255,255,0.25)':'rgba(0,0,0,0.06)', padding:'0 5px', borderRadius:8 }}>{fmtCount(femaleCount)}</span>
          </button>
          <button onClick={()=>setActiveTab('groom')} style={{ flex:1, padding:'8px 0', borderRadius:20, fontSize:12, fontWeight:600, cursor:'pointer', border:'1.5px solid', display:'flex', alignItems:'center', justifyContent:'center', gap:4,
            background:activeTab==='groom'?'#8B0000':'#f5f5f5', color:activeTab==='groom'?'#fff':'#666', borderColor:activeTab==='groom'?'#8B0000':'#e8e8e8' }}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0"/></svg>
            Male <span style={{ fontSize:10, fontWeight:700, background:activeTab==='groom'?'rgba(255,255,255,0.25)':'rgba(0,0,0,0.06)', padding:'0 5px', borderRadius:8 }}>{fmtCount(maleCount)}</span>
          </button>
          <button style={{ padding:'8px 14px', borderRadius:20, fontSize:12, fontWeight:600, cursor:'pointer', background:'#fff7ed', color:'#c2410c', border:'1.5px solid #fed7aa', whiteSpace:'nowrap' }}
            onClick={()=>{setOtpIntent('register');contactVerified?(window.location.href=`${USER_PANEL_URL}?create=1`):setShowOtpModal(true);}}>
            + Add Profile
          </button>
        </div>
      )}

      {/* Search */}
      <div style={{ padding:'4px 12px 10px', background:'#fff', borderBottom:'1px solid #f0f0f0', position:'relative' }}>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#999" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" style={{ position:'absolute', left:24, top:'50%', transform:'translateY(-40%)', pointerEvents:'none' }}>
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          type="text"
          value={searchQuery}
          onChange={e=>setSearchQuery(e.target.value)}
          placeholder="Search by name or profile ID…"
          style={{ width:'100%', padding:'8px 34px 8px 34px', border:'1.5px solid #e8e8e8', borderRadius:20, fontSize:13, outline:'none', background:'#fafafa', boxSizing:'border-box' }}
          onFocus={e=>{e.target.style.borderColor='#8B0000';e.target.style.background='#fff';}}
          onBlur={e=>{e.target.style.borderColor='#e8e8e8';e.target.style.background='#fafafa';}}
        />
        {searchQuery && (
          <button
            onClick={()=>setSearchQuery('')}
            aria-label="Clear search"
            style={{ position:'absolute', right:22, top:'50%', transform:'translateY(-40%)', background:'transparent', border:'none', fontSize:16, color:'#999', cursor:'pointer', padding:'2px 6px', lineHeight:1 }}>
            ×
          </button>
        )}
      </div>

      {/* Filter buttons — pick which slice of profiles to show below */}
      <div style={{ display:'flex', gap:6, padding:'8px 12px 10px', background:'#fff', borderBottom:'1px solid #f0f0f0', overflowX:'auto' }}>
        {[
          { key:'recent',    label:'🆕 Recent' },
          { key:'random',    label:'🎲 Random' },
          { key:'photos',    label:'📸 Photos' },
          { key:'notViewed', label:'✨ Not Viewed' },
        ].map(f => {
          const active = feedFilter === f.key;
          return (
            <button key={f.key} onClick={() => setFeedFilter(f.key)}
              style={{ flexShrink:0, padding:'6px 14px', borderRadius:18, fontSize:12, fontWeight:600, cursor:'pointer', whiteSpace:'nowrap',
                background: active ? '#8B0000' : '#f5f5f5',
                color: active ? '#fff' : '#555',
                border: active ? '1.5px solid #8B0000' : '1.5px solid #e8e8e8',
                transition: 'all 0.15s' }}>
              {f.label}
            </button>
          );
        })}
      </div>

      {/* Feed */}
      <style>{`
        .home-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        @media(max-width:768px){.home-grid{grid-template-columns:1fr}}
        @keyframes spin{to{transform:rotate(360deg)}}
      `}</style>
      <div style={{ padding:'0 12px', maxWidth:960, margin:'0 auto' }}>
        {visibleFeed.length === 0 && (
          <div style={{ textAlign:'center', padding:60, color:'#bbb' }}>
            <div style={{ fontSize:40, marginBottom:10 }}>?</div>
            <div style={{ fontSize:14, fontWeight:500 }}>No profiles found</div>
          </div>
        )}
        {(() => {
          const elements = [];
          let cardBatch = [];
          const flushCards = () => {
            if (cardBatch.length === 0) return;
            elements.push(
              <div className="home-grid" key={`grid-${elements.length}`}>
                {cardBatch.map(item => (
                  <div key={item.id}><ProfileCard p={item} /></div>
                ))}
              </div>
            );
            cardBatch = [];
          };
          visibleFeed.forEach((item, i) => {
            if (item.type === 'header') {
              flushCards();
              elements.push(
                <div key={`h-${i}`} style={{ display:'flex', alignItems:'center', gap:6, padding:'14px 0 6px' }}>
                  <span style={{ fontSize:16 }}>{item.icon}</span>
                  <span style={{ fontSize:14, fontWeight:700, color:'#222' }}>{item.title}</span>
                  <span style={{ fontSize:10, color:'#999', background:'#f0f0f0', padding:'1px 7px', borderRadius:10 }}>{fmtCount(item.count)}</span>
                  <div style={{ flex:1, height:1, background:'#e8e8e8', marginLeft:6 }} />
                </div>
              );
            } else if (item.type === 'viewmore') {
              // Skipped — a single Load More button at the bottom handles all pagination now.
            } else {
              cardBatch.push(item);
            }
          });
          flushCards();
          return elements;
        })()}
        {/* Infinite-scroll sentinel — the IntersectionObserver in the effect
            above triggers handleLoadMore whenever this element enters view. */}
        {visibleFeed.length > 0 && hasMore && (
          <div ref={sentinelRef} style={{ textAlign:'center', padding:'20px 0 28px', color:'#888', fontSize:13, fontWeight:500, display:'flex', alignItems:'center', justifyContent:'center', gap:8 }}>
            <span style={{ width:14, height:14, border:'2px solid rgba(139,0,0,0.25)', borderTopColor:'#8B0000', borderRadius:'50%', animation:'spin 0.8s linear infinite' }} />
            Loading more profiles…
          </div>
        )}
        {visibleFeed.length > 0 && !hasMore && (
          <div style={{ textAlign:'center', padding:'20px 0 28px', color:'#aaa', fontSize:12 }}>You've reached the end</div>
        )}
      </div>

      {/* Limit Popup */}
      {limitMsg && (
        <div style={{ position:'fixed', inset:0, background:'rgba(0,0,0,0.5)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:3000, backdropFilter:'blur(4px)' }} onClick={()=>setLimitMsg(null)}>
          <div style={{ background:'#fff', borderRadius:16, overflow:'hidden', maxWidth:340, width:'90%', boxShadow:'0 20px 60px rgba(0,0,0,0.25)' }} onClick={e=>e.stopPropagation()}>
            <div style={{ background:'linear-gradient(135deg,#dc2626,#ef4444)', padding:'24px 20px', textAlign:'center' }}>
              <div style={{ width:48, height:48, borderRadius:'50%', background:'rgba(255,255,255,0.2)', display:'inline-flex', alignItems:'center', justifyContent:'center', marginBottom:10 }}>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2" strokeLinecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              </div>
              <div style={{ color:'#fff', fontSize:17, fontWeight:700 }}>{limitMsg.title}</div>
            </div>
            <div style={{ padding:'20px', textAlign:'center' }}>
              <p style={{ fontSize:14, color:'#333', fontWeight:500, marginBottom:6 }}>{limitMsg.desc}</p>
              <p style={{ fontSize:12, color:'#999' }}>{limitMsg.sub}</p>
              <button onClick={()=>setLimitMsg(null)} style={{ marginTop:16, padding:'10px 32px', background:'linear-gradient(135deg,#8B0000,#C41E3A)', color:'#fff', border:'none', borderRadius:8, fontSize:14, fontWeight:700, cursor:'pointer' }}>OK</button>
            </div>
          </div>
        </div>
      )}

      {/* OTP Modal */}
      {showOtpModal && (
        <div style={{ position:'fixed', inset:0, background:'rgba(0,0,0,0.5)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:3000, backdropFilter:'blur(4px)' }} onClick={()=>{setShowOtpModal(false);setGatePromptMsg('');}}>
          <div style={{ background:'#fff', borderRadius:20, overflow:'hidden', maxWidth:380, width:'90%', boxShadow:'0 20px 60px rgba(0,0,0,0.25)' }} onClick={e=>e.stopPropagation()}>
            <div style={{ background:'linear-gradient(135deg,#8B0000,#C41E3A)', padding:22, textAlign:'center', position:'relative' }}>
              <button onClick={skipGateModal} title="Skip"
                style={{ position:'absolute', top:10, right:10, background:'rgba(255,255,255,0.18)', border:'none', color:'#fff', padding:'4px 10px', borderRadius:14, fontSize:11, fontWeight:700, cursor:'pointer', letterSpacing:0.4 }}>
                Skip ✕
              </button>
              <div style={{ fontSize:14, fontWeight:700, color:'#fff' }}>Verify Your Mobile</div>
              <div style={{ fontSize:11, color:'rgba(255,255,255,0.7)', marginTop:4 }}>Enter your number to continue</div>
            </div>
            <div style={{ padding:20 }}>
              {gatePromptMsg && (
                <div style={{ background:'#fef9e7', border:'1px solid #fde68a', color:'#92400e', padding:'10px 12px', borderRadius:8, fontSize:12.5, lineHeight:1.45, marginBottom:14 }}>
                  {gatePromptMsg}
                </div>
              )}
              {!otpSent ? (
                <>
                  <div style={{ display:'flex', alignItems:'baseline', justifyContent:'space-between', gap:8, marginBottom:6, flexWrap:'wrap' }}>
                    <label style={{ fontSize:11, fontWeight:700, color:'#8B0000', textTransform:'uppercase', letterSpacing:0.8 }}>Mobile Number</label>
                    <span style={{ fontSize:11, fontWeight:700, color:'#16a34a', letterSpacing:0.2 }}>Verify &amp; get unlimited free contacts</span>
                  </div>
                  <input type="tel" maxLength={10} value={otpMobile} onChange={e=>setOtpMobile(e.target.value.replace(/\D/g,''))} placeholder="Enter 10-digit mobile"
                    style={{ width:'100%', padding:'12px 14px', border:'1.5px solid #e0e0e0', borderRadius:10, fontSize:16, fontFamily:'monospace', letterSpacing:2, outline:'none', boxSizing:'border-box' }} />
                  <button onClick={sendOtp} disabled={otpLoading}
                    style={{ width:'100%', marginTop:14, padding:12, background:'linear-gradient(135deg,#8B0000,#C41E3A)', color:'#fff', border:'none', borderRadius:10, fontSize:15, fontWeight:700, cursor:'pointer' }}>
                    {otpLoading ? 'Sending...' : 'Send OTP'}
                  </button>
                  {(() => {
                    // Prefer the server-published window; fall back to the
                    // localStorage timestamp the client armed when offline.
                    const winMs = (gateState.anonWindowSec || GATE_WINDOW_MS/1000) * 1000;
                    const serverEnd = gateState.anonWindowStart ? gateState.anonWindowStart*1000 + winMs : 0;
                    const localEnd  = parseInt(localStorage.getItem(GATE_WINDOW_KEY) || '0', 10);
                    const endAt     = serverEnd || localEnd || (nowTick + winMs);
                    const remain    = Math.max(0, endAt - nowTick);
                    const hh = String(Math.floor(remain/3600000)).padStart(2,'0');
                    const mm = String(Math.floor((remain%3600000)/60000)).padStart(2,'0');
                    const ss = String(Math.floor((remain%60000)/1000)).padStart(2,'0');
                    const limit = gateState.anonViewsLimit || 5;
                    return (
                      <button onClick={skipGateModal}
                        style={{ width:'100%', marginTop:8, padding:10, background:'transparent', color:'#8B0000', border:'1.5px solid #8B0000', borderRadius:10, fontSize:13, fontWeight:700, cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center', gap:8 }}>
                        <span>⏱</span>
                        <span>To get your {limit} free contacts in</span>
                        <span style={{ fontFamily:'monospace', background:'#fef2f2', padding:'2px 8px', borderRadius:6, fontSize:12 }}>{hh}:{mm}:{ss}</span>
                      </button>
                    );
                  })()}
                </>
              ) : (
                <>
                  <p style={{ fontSize:13, color:'#666', textAlign:'center', marginBottom:14 }}>
                    OTP sent to <strong style={{ color:'#8B0000' }}>{otpMobile.substring(0,3)}****{otpMobile.substring(7)}</strong>
                    {otpTimer > 0 && <span style={{ color:'#999' }}> ({Math.floor(otpTimer/60)}:{String(otpTimer%60).padStart(2,'0')})</span>}
                  </p>
                  <div style={{ display:'flex', justifyContent:'center', gap:8, marginBottom:14 }}>
                    {[0,1,2,3].map(i => (
                      <input key={i} id={`home-otp-${i}`} type="tel" maxLength={1} value={otpValue[i]}
                        onChange={e=>handleOtpInput(i,e.target.value)}
                        onKeyDown={e=>{if(e.key==='Backspace'&&!otpValue[i]&&i>0)document.getElementById(`home-otp-${i-1}`)?.focus();}}
                        style={{ width:48, height:54, textAlign:'center', fontSize:22, fontWeight:700, border:'2px solid #e0e0e0', borderRadius:12, outline:'none', fontFamily:'monospace', color:'#8B0000' }} />
                    ))}
                  </div>
                  <button onClick={verifyOtp} disabled={otpLoading}
                    style={{ width:'100%', padding:12, background:'linear-gradient(135deg,#8B0000,#C41E3A)', color:'#fff', border:'none', borderRadius:10, fontSize:15, fontWeight:700, cursor:'pointer' }}>
                    {otpLoading ? 'Verifying...' : 'Verify OTP'}
                  </button>
                  {otpTimer === 0 && (
                    <button onClick={()=>{setOtpValue(['','','','']);sendOtp();}}
                      style={{ width:'100%', marginTop:8, padding:10, background:'transparent', color:'#8B0000', border:'1.5px solid #8B0000', borderRadius:10, fontSize:13, fontWeight:600, cursor:'pointer' }}>
                      Resend OTP
                    </button>
                  )}
                </>
              )}
              {otpMsg && <p style={{ textAlign:'center', marginTop:10, fontSize:13, color:otpMsg.includes('OTP:')||otpMsg.includes('sent')?'#16a34a':'#dc2626', fontWeight:600 }}>{otpMsg}</p>}
            </div>
          </div>
        </div>
      )}

      {/* Report Profile Modal */}
      {showReportModal && (
        <div style={{ position:'fixed', inset:0, background:'rgba(0,0,0,0.5)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:3000, backdropFilter:'blur(4px)' }}
          onClick={() => setShowReportModal(false)}>
          <div style={{ background:'#fff', borderRadius:16, overflow:'hidden', maxWidth:380, width:'90%', boxShadow:'0 20px 60px rgba(0,0,0,0.25)' }}
            onClick={e => e.stopPropagation()}>
            <div style={{ background:'linear-gradient(135deg,#8B0000,#C41E3A)', padding:'18px 20px', display:'flex', justifyContent:'space-between', alignItems:'center' }}>
              <span style={{ color:'#fff', fontSize:15, fontWeight:700 }}>Report Profile {reportProfileId}</span>
              <button onClick={() => setShowReportModal(false)} style={{ background:'rgba(255,255,255,0.2)', border:'none', color:'#fff', width:28, height:28, borderRadius:'50%', fontSize:14, cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center' }}>x</button>
            </div>
            <div style={{ padding:20 }}>
              <p style={{ fontSize:13, color:'#666', marginBottom:16 }}>Why are you reporting this profile?</p>
              <div style={{ display:'flex', flexDirection:'column', gap:8, marginBottom:16 }}>
                {[
                  { value:'already_married', label:'Already Married' },
                  { value:'misinformation', label:'Wrong / False Information' },
                  { value:'fraud', label:'Fraud / Scam' },
                ].map(opt => (
                  <label key={opt.value} style={{ display:'flex', alignItems:'center', gap:10, padding:'10px 14px', border: reportReason === opt.value ? '2px solid #8B0000' : '1.5px solid #e8e8e8', borderRadius:8, cursor:'pointer', background: reportReason === opt.value ? '#fef2f2' : '#fff' }}>
                    <input type="radio" name="home-report" value={opt.value} checked={reportReason === opt.value} onChange={e => setReportReason(e.target.value)}
                      style={{ accentColor:'#8B0000' }} />
                    <span style={{ fontSize:13, fontWeight:600, color: reportReason === opt.value ? '#8B0000' : '#333' }}>{opt.label}</span>
                  </label>
                ))}
              </div>
              <div style={{ display:'flex', gap:8 }}>
                <button onClick={() => setShowReportModal(false)} style={{ flex:1, padding:10, background:'#f5f5f5', border:'1px solid #e8e8e8', borderRadius:8, fontSize:13, fontWeight:600, color:'#666', cursor:'pointer' }}>Cancel</button>
                <button disabled={!reportReason} onClick={async () => {
                  try {
                    await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
                      body: JSON.stringify({ action:'report_profile', cp_id: reportProfileId, reason: reportReason, reporter_mobile: userMobile || '' }), credentials:'include' });
                    alert('Report submitted. Thank you!');
                  } catch(e) { alert('Report submitted.'); }
                  setShowReportModal(false); setReportReason(''); setReportProfileId(null);
                }} style={{ flex:1, padding:10, background: reportReason ? 'linear-gradient(135deg,#8B0000,#C41E3A)' : '#ddd', color:'#fff', border:'none', borderRadius:8, fontSize:13, fontWeight:700, cursor: reportReason ? 'pointer' : 'not-allowed' }}>Submit Report</button>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
