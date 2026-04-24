import React, { useState, useEffect } from "react";
import { useParams, useNavigate, useLocation } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { API_BASE, PHOTO_BASE, UPLOADS_PREFIX, getPhotoUrls } from "./config";

function mapProfile(p) {
  const photoVal = p.photo1 || p.photo || '';
  let photo = '';
  if (photoVal && !photoVal.startsWith('default_')) {
    photo = photoVal.startsWith('http') ? photoVal
      : photoVal.startsWith('uploads/') ? UPLOADS_PREFIX + photoVal
      : PHOTO_BASE + photoVal;
  }
  return {
    _full: true,
    id: p.cp_id || p.id || p.regId, regId: p.cp_id || p.regId || p.id,
    name: p.name || 'N/A', gender: p.gender || '',
    age: p.age || '', dob: p.dob || '', photo,
    motherTongue: p.mother_tongue || p.motherTongue || p.language || '',
    maritalStatus: p.marital || p.maritalStatus || '',
    religion: p.religion || '',
    caste: p.caste || '', subCaste: p.sub_caste || p.subCaste || '',
    gothram: p.gothram || '', complexion: p.complexion || '',
    nativity: p.nativity || '', nationality: p.nationality || '',
    ownHouse: p.own_house || p.ownHouse || '',
    bornAs: p.born_as || p.bornAs || '',
    workplace: p.workplace || '',
    placeBirth: p.place_birth || p.placeBirth || '',
    presentArea: p.present_area || p.presentArea || '',
    presentCity: p.present_city || p.presentCity || '',
    presentDistrict: p.present_district || p.presentDistrict || '',
    presentState: p.present_state || p.presentState || '',
    others: p.others || '',
    birthHour: p.birth_hour || p.birthHour || '',
    birthMin: p.birth_min || p.birthMin || '',
    birthAmPm: p.birth_ampm || p.birthAmPm || '',
    star: p.star || '', raasi: p.raasi || '',
    paadam: p.paadam || '', lagnam: p.lagnam || '',
    dosham: p.dosham || '', doshamType: p.dosham_type || p.doshamType || '',
    qualification: p.qualification || '',
    job: p.job || '', placeJob: p.place_of_job || p.placeJob || p.jobPlace || '',
    income: p.income || p.incomeMonth || '',
    height: p.height || '', weight: p.weight || '',
    bloodGroup: p.blood_group || p.bloodGroup || p.blood || '',
    diet: p.diet || '', disability: p.disability || '',
    fatherName: p.father || p.fatherName || '',
    fatherJob: p.father_job || p.fatherJob || '',
    fatherAlive: p.father_alive || p.fatherAlive || '',
    motherName: p.mother || p.motherName || '',
    motherJob: p.mother_job || p.motherJob || '',
    motherAlive: p.mother_alive || p.motherAlive || '',
    sibMarriedEB: p.sib_married_eb || p.sibMarriedEb || '',
    sibMarriedYB: p.sib_married_yb || p.sibMarriedYb || '',
    sibMarriedES: p.sib_married_es || p.sibMarriedEs || '',
    sibMarriedYS: p.sib_married_ys || p.sibMarriedYs || '',
    sibUnmarriedEB: p.sib_unmarried_eb || p.sibUnmarriedEb || '',
    sibUnmarriedYB: p.sib_unmarried_yb || p.sibUnmarriedYb || '',
    sibUnmarriedES: p.sib_unmarried_es || p.sibUnmarriedEs || '',
    sibUnmarriedYS: p.sib_unmarried_ys || p.sibUnmarriedYs || '',
    partnerQualification: p.partner_qualification || p.partnerQualification || '',
    partnerJob: p.partner_job || p.partnerJob || '',
    partnerIncome: p.partner_income_month || p.partnerIncome || '',
    partnerAge: (p.partner_age_from || p.partnerAgeFrom || '') + (p.partner_age_to || p.partnerAgeTo ? ' - ' + (p.partner_age_to || p.partnerAgeTo) : ''),
    partnerDiet: p.partner_diet || p.partnerDiet || '',
    partnerHoroscope: p.partner_horoscope_required || p.partnerHoroscope || '',
    partnerCaste: p.partner_caste || p.partnerCaste || '',
    partnerSubCaste: p.partner_sub_caste || p.partnerSubCaste || '',
    partnerMarital: p.partner_marital_status || p.partnerMarital || '',
    partnerJobReq: p.partner_job_requirement || p.partnerJobReq || '',
    partnerOtherRequirement: p.partner_other_requirement || p.partnerOtherRequirement || '',
    photo2: p.photo2 || '', photo3: p.photo3 || '',
    rasiPhoto: p.rasi_photo || p.rasiPhoto || '',
    amsamPhoto: p.amsam_photo || p.amsamPhoto || '',
    phone: p.mobile || p.phone || '',
    contactNumber: p.mobile || p.phone || p.contactNumber || '',
    email: p.email || '', altMobile: p.alt_mobile || p.altMobile || '',
    contactPerson: p.contact_person || p.contactPerson || '',
    permanentAddress: p.perm_address || p.permAddress || p.permAddr || p.permanentAddress || '',
    presentAddress: p.present_address || p.presentAddress || p.presentAddr || '',
  };
}

export default function Detail() {
  const { id } = useParams();
  const navigate = useNavigate();
  const location = useLocation();
  const { t } = useTranslation();
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showContact, setShowContact] = useState(false);
  const [contactVerified, setContactVerified] = useState(false);
  const [showOtpModal, setShowOtpModal] = useState(false);
  const [otpMobile, setOtpMobile] = useState('');
  const [otpValue, setOtpValue] = useState(['','','','']);
  const [otpSent, setOtpSent] = useState(false);
  const [otpMsg, setOtpMsg] = useState('');
  const [otpLoading, setOtpLoading] = useState(false);
  const [otpTimer, setOtpTimer] = useState(0);
  const [showReportModal, setShowReportModal] = useState(false);
  const [reportReason, setReportReason] = useState('');
  const [showMarriedConfirm, setShowMarriedConfirm] = useState(false);
  const [marriedSubmitting, setMarriedSubmitting] = useState(false);

  const submitReport = async (reason) => {
    try {
      const chk = contactVerified
        ? await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'contact_check' }), credentials:'include' }).then(r=>r.json())
        : {};
      await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'report_profile', cp_id: p.regId, reason, reporter_mobile: chk.mobile || '' }), credentials:'include' });
    } catch(e) {}
  };
  const [limitMsg, setLimitMsg] = useState(null);
  const [lightboxImg, setLightboxImg] = useState(null);
  const [activePhoto, setActivePhoto] = useState(0);

  useEffect(() => {
    fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'contact_check' }), credentials:'include' })
      .then(r => r.json()).then(d => { if (d.ok && d.verified) setContactVerified(true); })
      .catch(() => {});
  }, []);

  useEffect(() => {
    if (otpTimer <= 0) return;
    const tm = setTimeout(() => setOtpTimer(otpTimer - 1), 1000);
    return () => clearTimeout(tm);
  }, [otpTimer]);

  const sendOtp = async () => {
    if (!/^\d{10}$/.test(otpMobile)) { setOtpMsg('Enter valid 10-digit mobile'); return; }
    setOtpLoading(true); setOtpMsg('');
    try {
      const resp = await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'contact_otp_send', mobile: otpMobile }), credentials:'include' });
      const data = await resp.json();
      if (data.ok) {
        if (data.auto_verified) { setContactVerified(true); setShowOtpModal(false); setShowContact(true); return; }
        setOtpSent(true); setOtpTimer(120);
        setOtpMsg(data.otp ? `OTP: ${data.otp}` : 'OTP sent');
      } else setOtpMsg(data.error || 'Failed');
    } catch (e) { setOtpMsg('Network error'); }
    setOtpLoading(false);
  };

  const verifyOtp = async () => {
    const otp = otpValue.join('');
    if (otp.length !== 4) { setOtpMsg('Enter 4-digit OTP'); return; }
    setOtpLoading(true); setOtpMsg('');
    try {
      const resp = await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ action:'contact_otp_verify', mobile: otpMobile, otp }), credentials:'include' });
      const data = await resp.json();
      if (data.ok && data.verified) {
        setContactVerified(true); setShowOtpModal(false); setShowContact(true);
        fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
          body: JSON.stringify({ action:'track_view', target_cp_id: id, type:'contact_view' }), credentials:'include' }).catch(()=>{});
      } else setOtpMsg(data.error || 'Invalid OTP');
    } catch (e) { setOtpMsg('Network error'); }
    setOtpLoading(false);
  };

  const handleOtpInput = (i, val) => {
    if (!/^\d?$/.test(val)) return;
    const newOtp = [...otpValue]; newOtp[i] = val; setOtpValue(newOtp);
    if (val && i < 3) document.getElementById(`otp-box-${i+1}`)?.focus();
  };

  // Track time spent on profile
  useEffect(() => {
    const startTime = Date.now();
    let maxScroll = 0;
    let sent = false;

    const handleScroll = () => {
      const totalH = document.body.scrollHeight - window.innerHeight;
      if (totalH > 0) {
        const scrollPct = Math.round((window.scrollY / totalH) * 100);
        if (scrollPct > maxScroll) maxScroll = scrollPct;
      }
    };

    window.addEventListener('scroll', handleScroll);

    const sendDuration = () => {
      const seconds = Math.round((Date.now() - startTime) / 1000);
      if (seconds < 2) return;
      // Use Blob with sendBeacon for proper Content-Type
      const payload = JSON.stringify({
        action: 'track_view', target_cp_id: id, type: 'profile_view',
        time_spent: seconds, scroll_depth: maxScroll
      });
      try {
        const blob = new Blob([payload], { type: 'application/json' });
        const beaconSent = navigator.sendBeacon?.(API_BASE, blob);
        if (!beaconSent) {
          fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
            body: payload, credentials:'include', keepalive: true }).catch(()=>{});
        }
      } catch(e) {
        fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
          body: payload, credentials:'include', keepalive: true }).catch(()=>{});
      }
    };

    // Single send on tab hide / unload via sendBeacon — captures time_spent and
    // scroll_depth accurately without the 15s polling that produced ~4 req/min
    // per open tab. 1000 idle tabs used to = 4000 req/min of pure noise.
    const onVisChange = () => { if (document.visibilityState === 'hidden') sendDuration(); };
    window.addEventListener('beforeunload', sendDuration);
    document.addEventListener('visibilitychange', onVisChange);

    return () => {
      window.removeEventListener('scroll', handleScroll);
      window.removeEventListener('beforeunload', sendDuration);
      document.removeEventListener('visibilitychange', onVisChange);
      sendDuration();
    };
  }, [id]);

  useEffect(() => {
    if (profile?._full) return;
    (async () => {
      try {
        const resp = await fetch(API_BASE + '?action=detail&cp_id=' + encodeURIComponent(id));
        const data = await resp.json();
        if (data.ok && data.profile) {
          setProfile(mapProfile(data.profile));
          fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({ action:'track_view', target_cp_id: id, type:'profile_view' }), credentials:'include' }).catch(()=>{});
        }
      } catch (e) { console.error('Detail fetch error:', e); }
      setLoading(false);
    })();
  }, [id]);

  if (loading) return <div style={{ minHeight:'100vh', display:'flex', alignItems:'center', justifyContent:'center', fontSize:16, color:'#999' }}>Loading...</div>;
  if (!profile) return (
    <div style={{ minHeight:'100vh', display:'flex', alignItems:'center', justifyContent:'center', background:'#f5f5f5' }}>
      <div style={{ textAlign:'center', padding:40, background:'#fff', borderRadius:16, boxShadow:'0 2px 12px rgba(0,0,0,0.06)', maxWidth:400 }}>
        <div style={{ fontSize:40, marginBottom:12 }}>?</div>
        <h2 style={{ fontSize:18, fontWeight:700, color:'#333', marginBottom:8 }}>Profile Not Found</h2>
        <p style={{ fontSize:13, color:'#999', marginBottom:20 }}>The profile you're looking for doesn't exist.</p>
        <button onClick={() => navigate("/")} style={{ padding:'10px 28px', background:'#8B0000', color:'#fff', border:'none', borderRadius:8, fontSize:14, fontWeight:600, cursor:'pointer' }}>Go Home</button>
      </div>
    </div>
  );

  const p = profile;
  const val = (v) => v && v !== '-Select-' && v !== '-select-' && v !== '-Select Rasi-' ? v : null;

  const Row = ({ label, value }) => {
    const v = typeof value === 'string' ? val(value) : value;
    return (
      <div style={{ display:'flex', padding:'9px 0', borderBottom:'1px solid #f5f5f5' }}>
        <span style={{ width:140, flexShrink:0, fontSize:12, fontWeight:600, color:'#999', textTransform:'uppercase', letterSpacing:0.3 }}>{label}</span>
        <span style={{ flex:1, fontSize:14, color: v ? '#222' : '#ccc', fontWeight: v ? 500 : 400 }}>{v || '—'}</span>
      </div>
    );
  };

  const Section = ({ title, children }) => (
    <div style={{ background:'#fff', borderRadius:12, padding:'16px 18px', marginBottom:10, boxShadow:'0 1px 4px rgba(0,0,0,0.04)', border:'1px solid #f0f0f0' }}>
      <div style={{ fontSize:14, fontWeight:700, color:'#8B0000', marginBottom:12, paddingBottom:8, borderBottom:'2px solid #fef2f2', textTransform:'uppercase', letterSpacing:0.5 }}>{title}</div>
      {children}
    </div>
  );

  const location2 = [p.presentArea, p.presentCity, p.presentDistrict, p.presentState].filter(v => v && v.trim()).join(', ');

  return (
    <div style={{ background:'#f5f5f5', minHeight:'100vh', paddingBottom:70 }}>

      {/* Profile Header with Photos */}
      {(() => {
        const allPhotos = [p.photo, p.photo2, p.photo3]
          .filter(ph => ph && !ph.includes('default_'))
          .map(ph => {
            const raw = ph.replace(UPLOADS_PREFIX, '').replace(PHOTO_BASE, '');
            let orig;
            // Absolute paths (http(s):// or any /-prefixed path like
            // /backend/api/uploads/... or /matrimony/...) are used as-is;
            // only bare "uploads/..." or file-only paths get a prefix.
            if (ph.startsWith('http') || ph.startsWith('/')) orig = ph;
            else if (ph.startsWith('uploads/')) orig = UPLOADS_PREFIX + ph;
            else orig = PHOTO_BASE + ph;
            const urls = getPhotoUrls(raw);
            return { orig, raw, full: urls ? urls.full : orig, thumb: urls ? urls.thumb : orig };
          });
        const active = allPhotos[activePhoto] || allPhotos[0];
        return (
      <div style={{ background:'#fff', borderBottom:'1px solid #f0f0f0' }}>
        {/* Main Photo */}
        <div style={{ position:'relative', background:'#f0f0f0' }}>
          {allPhotos.length > 0 ? (
            <picture onClick={() => setLightboxImg(active.orig)} style={{ cursor:'pointer', display:'block' }}>
              <source type="image/webp" srcSet={active.full} />
              <img src={active.orig} alt={p.name}
                loading="eager" decoding="async" fetchpriority="high"
                style={{ width:'100%', maxHeight:350, objectFit:'contain', objectPosition:'center', display:'block', background:'#f8f8f8' }}
                onError={e => { e.target.onerror=null; e.target.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(p.name)}&background=8B0000&color=fff&size=300`; }} />
            </picture>
          ) : (
            <div style={{ width:'100%', height:200, display:'flex', alignItems:'center', justifyContent:'center', background:'#f8f8f8' }}>
              <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ccc" strokeWidth="1.5"><circle cx="12" cy="7" r="4"/><path d="M5.5 21a6.5 6.5 0 0 1 13 0"/></svg>
            </div>
          )}
          {/* Photo count badge */}
          {allPhotos.length > 1 && (
            <div style={{ position:'absolute', bottom:10, right:10, background:'rgba(0,0,0,0.6)', color:'#fff', padding:'4px 10px', borderRadius:12, fontSize:11, fontWeight:600 }}>
              {activePhoto + 1} / {allPhotos.length}
            </div>
          )}
          {/* Tap to view */}
          {allPhotos.length > 0 && (
            <div style={{ position:'absolute', bottom:10, left:10, background:'rgba(0,0,0,0.5)', color:'#fff', padding:'4px 10px', borderRadius:12, fontSize:10, display:'flex', alignItems:'center', gap:4 }}>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2"><path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/></svg>
              Tap to enlarge
            </div>
          )}
        </div>
        {/* Thumbnail strip */}
        {allPhotos.length > 1 && (
          <div style={{ display:'flex', gap:6, padding:'8px 12px', overflowX:'auto', background:'#fff' }}>
            {allPhotos.map((ph, i) => (
              <picture key={i} onClick={() => setActivePhoto(i)} style={{ flexShrink:0, cursor:'pointer' }}>
                <source type="image/webp" srcSet={ph.thumb} />
                <img src={ph.orig} alt={`Photo ${i+1}`}
                  loading="lazy" decoding="async"
                  width="56" height="56"
                  style={{ width:56, height:56, objectFit:'cover', borderRadius:8, border: activePhoto === i ? '2.5px solid #8B0000' : '2px solid #e0e0e0', opacity: activePhoto === i ? 1 : 0.6, background:'#f5f5f5' }}
                  onError={e => { e.target.onerror=null; e.target.style.display='none'; }} />
              </picture>
            ))}
          </div>
        )}
        {/* Info */}
        <div style={{ padding:'14px 16px' }}>
          <div style={{ fontSize:18, fontWeight:700, color:'#222', marginBottom:2 }}>{p.name}</div>
          <div style={{ fontSize:12, color:'#8B0000', fontWeight:600, background:'#fef2f2', padding:'2px 8px', borderRadius:4, display:'inline-block', marginBottom:6 }}>{p.regId}</div>
          <div style={{ fontSize:13, color:'#666', marginBottom:8 }}>
            {[p.age ? p.age + ' yrs' : '', val(p.maritalStatus), p.motherTongue].filter(Boolean).join(' · ')}
          </div>
          <div style={{ display:'flex', flexWrap:'wrap', gap:4 }}>
            {val(p.caste) && <span style={{ fontSize:11, fontWeight:600, padding:'2px 8px', borderRadius:12, background:'#f5f0ff', color:'#6d28d9' }}>{p.caste}</span>}
            {p.religion && <span style={{ fontSize:11, fontWeight:600, padding:'2px 8px', borderRadius:12, background:'#fff7ed', color:'#c2410c' }}>{p.religion}</span>}
            {val(p.star) && <span style={{ fontSize:11, fontWeight:600, padding:'2px 8px', borderRadius:12, background:'#f0fdf4', color:'#166534' }}>{p.star}</span>}
            {val(p.raasi) && <span style={{ fontSize:11, fontWeight:600, padding:'2px 8px', borderRadius:12, background:'#eff6ff', color:'#1e40af' }}>{p.raasi}</span>}
          </div>
          <div style={{ fontSize:12, color:'#888', marginTop:6 }}>
            {[val(p.height), p.qualification, p.job].filter(Boolean).join(' · ')}
          </div>
        </div>
      </div>
        );
      })()}

      {/* Content */}
      <div style={{ maxWidth:800, margin:'0 auto', padding:'10px 12px' }}>

        {/* Contact Button */}
        <div style={{ background:'#fff', borderRadius:12, padding:'14px 18px', marginBottom:10, boxShadow:'0 1px 4px rgba(0,0,0,0.04)', border:'1px solid #f0f0f0' }}>
          {showContact && contactVerified ? (
            <div>
              <div style={{ fontSize:12, fontWeight:600, color:'#999', textTransform:'uppercase', marginBottom:8 }}>Contact Details</div>
              <div style={{ display:'flex', flexDirection:'column', gap:8 }}>
                {p.contactNumber && (
                  <a href={`tel:${p.contactNumber}`} style={{ display:'flex', alignItems:'center', gap:10, padding:'10px 14px', background:'#f0fdf4', borderRadius:8, textDecoration:'none', border:'1px solid #bbf7d0' }}>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" strokeWidth="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span style={{ fontSize:16, fontWeight:700, color:'#16a34a', letterSpacing:1 }}>{p.contactNumber}</span>
                  </a>
                )}
                {p.altMobile && (
                  <a href={`tel:${p.altMobile}`} style={{ display:'flex', alignItems:'center', gap:10, padding:'10px 14px', background:'#eff6ff', borderRadius:8, textDecoration:'none', border:'1px solid #bfdbfe' }}>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" strokeWidth="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span style={{ fontSize:14, fontWeight:600, color:'#2563eb' }}>{p.altMobile} (Alt)</span>
                  </a>
                )}
                {p.contactPerson && <div style={{ fontSize:13, color:'#666' }}>Contact Person: <strong>{p.contactPerson}</strong></div>}
                {p.email && <div style={{ fontSize:13, color:'#666' }}>Email: <strong>{p.email}</strong></div>}
              </div>
              <button onClick={() => setShowContact(false)} style={{ marginTop:10, width:'100%', padding:'8px', background:'#f5f5f5', border:'1px solid #e8e8e8', borderRadius:8, fontSize:12, fontWeight:600, color:'#888', cursor:'pointer' }}>Hide Contact</button>
            </div>
          ) : (
            <button onClick={async () => {
              if (!contactVerified) { setShowOtpModal(true); return; }
              // Check limits
              try {
                const chk = await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
                  body: JSON.stringify({ action:'contact_check' }), credentials:'include' }).then(r=>r.json());
                const mobile = chk.mobile || '';
                if (mobile) {
                  const limResp = await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({ action:'user_limits', mobile }), credentials:'include' }).then(r=>r.json());
                  if (limResp.ok) {
                    const { limits, used } = limResp;
                    if (limits.day > 0 && used.day >= limits.day) { setLimitMsg({ title:'Daily Limit Reached', desc:`You have used all ${limits.day} contact views for today.`, sub:'Try again tomorrow.' }); return; }
                    if (limits.month > 0 && used.month >= limits.month) { setLimitMsg({ title:'Monthly Limit Reached', desc:`You have used all ${limits.month} contact views this month.`, sub:'Limit resets next month.' }); return; }
                    if (limits.total > 0 && used.total >= limits.total) { setLimitMsg({ title:'Total Limit Reached', desc:`You have used all ${limits.total} lifetime contact views.`, sub:'Upgrade your plan for more.' }); return; }
                  }
                  await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({ action:'track_view', target_cp_id: p.regId, type:'contact_view' }), credentials:'include' });
                }
              } catch(e) { console.error('Limit check error:', e); }
              setShowContact(true);
            }}
              style={{ width:'100%', padding:'12px', background:'linear-gradient(135deg,#16a34a,#15803d)', color:'#fff', border:'none', borderRadius:10, fontSize:14, fontWeight:700, cursor:'pointer', letterSpacing:0.3 }}>
              View Free Contact
            </button>
          )}
        </div>

        {/* Personal Details */}
        <Section title="Personal Details">
          <Row label="Full Name" value={p.name} />
          <Row label="Gender" value={p.gender} />
          <Row label="Date of Birth" value={p.dob ? new Date(p.dob).toLocaleDateString("en-IN",{day:"numeric",month:"long",year:"numeric"}) : null} />
          <Row label="Age" value={p.age ? p.age + ' yrs' : null} />
          <Row label="Birth Time" value={p.birthHour && p.birthMin ? `${p.birthHour}:${p.birthMin} ${p.birthAmPm}` : null} />
          <Row label="Place of Birth" value={p.placeBirth} />
          <Row label="Mother Tongue" value={p.motherTongue} />
          <Row label="Religion" value={p.religion} />
          <Row label="Marital Status" value={p.maritalStatus} />
          <Row label="Nativity" value={p.nativity} />
          <Row label="Nationality" value={p.nationality} />
          <Row label="Own House" value={p.ownHouse} />
          <Row label="Born As" value={p.bornAs} />
          <Row label="Complexion" value={p.complexion} />
          {p.others && <Row label="Additional" value={p.others} />}
        </Section>

        {/* Family Details */}
        <Section title="Family Details">
          <Row label="Father's Name" value={p.fatherName} />
          <Row label="Father's Job" value={p.fatherJob} />
          <Row label="Father Status" value={p.fatherAlive} />
          <Row label="Mother's Name" value={p.motherName} />
          <Row label="Mother's Job" value={p.motherJob} />
          <Row label="Mother Status" value={p.motherAlive} />
          <div style={{ fontSize:11, fontWeight:700, color:'#999', textTransform:'uppercase', marginTop:10, marginBottom:6 }}>Siblings</div>
          <div style={{ overflowX:'auto', borderRadius:8, border:'1px solid #f0f0f0' }}>
            <table style={{ width:'100%', borderCollapse:'collapse', fontSize:12 }}>
              <thead>
                <tr style={{ background:'#f8f8f8' }}>
                  <th style={{ padding:'8px 10px', textAlign:'left', fontWeight:700, color:'#666' }}></th>
                  <th style={{ padding:'8px 6px', textAlign:'center', fontWeight:600, color:'#666' }}>Elder Bro</th>
                  <th style={{ padding:'8px 6px', textAlign:'center', fontWeight:600, color:'#666' }}>Yng Bro</th>
                  <th style={{ padding:'8px 6px', textAlign:'center', fontWeight:600, color:'#666' }}>Elder Sis</th>
                  <th style={{ padding:'8px 6px', textAlign:'center', fontWeight:600, color:'#666' }}>Yng Sis</th>
                </tr>
              </thead>
              <tbody>
                <tr><td style={{ padding:'6px 10px', fontWeight:600, color:'#444' }}>Married</td>
                  {['sibMarriedEB','sibMarriedYB','sibMarriedES','sibMarriedYS'].map(k => <td key={k} style={{ padding:'6px', textAlign:'center', color:'#333' }}>{p[k] || '—'}</td>)}
                </tr>
                <tr style={{ background:'#fafafa' }}><td style={{ padding:'6px 10px', fontWeight:600, color:'#444' }}>Unmarried</td>
                  {['sibUnmarriedEB','sibUnmarriedYB','sibUnmarriedES','sibUnmarriedYS'].map(k => <td key={k} style={{ padding:'6px', textAlign:'center', color:'#333' }}>{p[k] || '—'}</td>)}
                </tr>
              </tbody>
            </table>
          </div>
        </Section>

        {/* Physical Attributes */}
        <Section title="Physical Attributes">
          <Row label="Height" value={p.height} />
          <Row label="Weight" value={p.weight} />
          <Row label="Blood Group" value={p.bloodGroup} />
          <Row label="Complexion" value={p.complexion} />
          <Row label="Diet" value={p.diet} />
          <Row label="Disability" value={p.disability} />
        </Section>

        {/* Education & Career */}
        <Section title="Education & Career">
          <Row label="Qualification" value={p.qualification} />
          <Row label="Occupation" value={p.job} />
          <Row label="Place of Work" value={p.placeJob} />
          <Row label="Monthly Income" value={p.income ? `Rs. ${p.income}` : null} />
        </Section>

        {/* Astrology */}
        <Section title="Astrology">
          <Row label="Caste" value={p.caste} />
          <Row label="Sub Caste" value={p.subCaste} />
          <Row label="Gothram" value={p.gothram} />
          <Row label="Star" value={p.star} />
          <Row label="Raasi" value={p.raasi} />
          <Row label="Padam" value={p.paadam} />
          <Row label="Laknam" value={p.lagnam} />
          <Row label="Dosham" value={p.dosham} />
          {p.dosham === 'Yes' && <Row label="Dosham Type" value={p.doshamType} />}
        </Section>

        {/* Horoscope Charts */}
        {(() => {
          const fixUrl = (ph) => {
            if (!ph) return null;
            if (ph.startsWith('http') || ph.startsWith('/matrimony')) return ph;
            if (ph.startsWith('uploads/')) return UPLOADS_PREFIX + ph;
            return PHOTO_BASE + ph;
          };
          const rasi = fixUrl(p.rasiPhoto);
          const amsam = fixUrl(p.amsamPhoto);
          return (rasi || amsam) ? (
            <Section title="Horoscope Charts">
              <div style={{ display:'flex', gap:12, flexWrap:'wrap' }}>
                {rasi && <div style={{ flex:1, minWidth:120 }}>
                  <div style={{ fontSize:11, fontWeight:600, color:'#999', marginBottom:4, textTransform:'uppercase' }}>Rasi Chart</div>
                  <img src={rasi} alt="Rasi" onClick={() => setLightboxImg(rasi)} style={{ width:'100%', borderRadius:8, border:'1px solid #f0f0f0', cursor:'pointer' }} onError={e => e.target.parentElement.style.display='none'} />
                </div>}
                {amsam && <div style={{ flex:1, minWidth:120 }}>
                  <div style={{ fontSize:11, fontWeight:600, color:'#999', marginBottom:4, textTransform:'uppercase' }}>Amsam Chart</div>
                  <img src={amsam} alt="Amsam" onClick={() => setLightboxImg(amsam)} style={{ width:'100%', borderRadius:8, border:'1px solid #f0f0f0', cursor:'pointer' }} onError={e => e.target.parentElement.style.display='none'} />
                </div>}
              </div>
            </Section>
          ) : null;
        })()}

        {/* Partner Expectations */}
        <Section title="Partner Expectations">
          <Row label="Qualification" value={p.partnerQualification} />
          <Row label="Job" value={p.partnerJob} />
          <Row label="Job Requirement" value={p.partnerJobReq} />
          <Row label="Income" value={p.partnerIncome ? `Rs. ${p.partnerIncome}` : null} />
          <Row label="Age Range" value={p.partnerAge && p.partnerAge.trim() !== '-' ? p.partnerAge : null} />
          <Row label="Diet" value={p.partnerDiet} />
          <Row label="Horoscope Req" value={p.partnerHoroscope} />
          <Row label="Caste Pref" value={p.partnerCaste} />
          <Row label="Sub Caste Pref" value={p.partnerSubCaste} />
          <Row label="Marital Status" value={p.partnerMarital} />
          {p.partnerOtherRequirement && <Row label="Other" value={p.partnerOtherRequirement} />}
        </Section>

        {/* Address / Location */}
        <Section title="Location & Address">
          <Row label="Present Address" value={p.presentAddress} />
          <Row label="Area" value={p.presentArea} />
          <Row label="City" value={p.presentCity} />
          <Row label="District" value={p.presentDistrict} />
          <Row label="State" value={p.presentState} />
          <Row label="Permanent Addr" value={p.permanentAddress} />
          {p.workplace && <Row label="Present Country" value={p.workplace} />}
        </Section>


        {/* Report Profile */}
        <div style={{ padding:'0 0 10px', display:'flex', gap:8, justifyContent:'center' }}>
          <button onClick={() => setShowReportModal(true)}
            style={{ padding:'8px 20px', background:'#fff', border:'1px solid #fecaca', borderRadius:8, fontSize:12, fontWeight:600, color:'#dc2626', cursor:'pointer' }}>
            Report Profile
          </button>
          <button onClick={() => setShowMarriedConfirm(true)}
            style={{ padding:'8px 20px', background:'#fff', border:'1px solid #fed7aa', borderRadius:8, fontSize:12, fontWeight:600, color:'#ea580c', cursor:'pointer' }}>
            Already Married
          </button>
        </div>

      </div>

      {/* Image Lightbox */}
      {lightboxImg && (
        <div onClick={() => setLightboxImg(null)}
          style={{ position:'fixed', inset:0, background:'rgba(0,0,0,0.9)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:3000, cursor:'pointer' }}>
          <button onClick={() => setLightboxImg(null)}
            style={{ position:'absolute', top:16, right:16, width:36, height:36, borderRadius:'50%', background:'rgba(255,255,255,0.2)', border:'none', color:'#fff', fontSize:20, cursor:'pointer', display:'flex', alignItems:'center', justifyContent:'center', zIndex:1 }}>
            x
          </button>
          <img src={lightboxImg} alt="Full size" onClick={e => e.stopPropagation()}
            style={{ maxWidth:'95%', maxHeight:'90vh', objectFit:'contain', borderRadius:4, cursor:'default' }} />
        </div>
      )}

      {/* Limit Reached Popup */}
      {limitMsg && (
        <div style={{ position:'fixed', inset:0, background:'rgba(0,0,0,0.5)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:3000, backdropFilter:'blur(4px)' }}
          onClick={() => setLimitMsg(null)}>
          <div style={{ background:'#fff', borderRadius:16, overflow:'hidden', maxWidth:340, width:'90%', boxShadow:'0 20px 60px rgba(0,0,0,0.25)' }}
            onClick={e => e.stopPropagation()}>
            <div style={{ background:'linear-gradient(135deg,#dc2626,#ef4444)', padding:'24px 20px', textAlign:'center' }}>
              <div style={{ width:48, height:48, borderRadius:'50%', background:'rgba(255,255,255,0.2)', display:'inline-flex', alignItems:'center', justifyContent:'center', marginBottom:10 }}>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" strokeWidth="2" strokeLinecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              </div>
              <div style={{ color:'#fff', fontSize:17, fontWeight:700 }}>{limitMsg.title}</div>
            </div>
            <div style={{ padding:'20px', textAlign:'center' }}>
              <p style={{ fontSize:14, color:'#333', fontWeight:500, marginBottom:6 }}>{limitMsg.desc}</p>
              <p style={{ fontSize:12, color:'#999' }}>{limitMsg.sub}</p>
              <button onClick={() => setLimitMsg(null)}
                style={{ marginTop:16, padding:'10px 32px', background:'linear-gradient(135deg,#8B0000,#C41E3A)', color:'#fff', border:'none', borderRadius:8, fontSize:14, fontWeight:700, cursor:'pointer' }}>
                OK
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Report Modal */}
      {showReportModal && (
        <div style={{ position:'fixed', inset:0, background:'rgba(0,0,0,0.5)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:3000, backdropFilter:'blur(4px)' }}
          onClick={() => setShowReportModal(false)}>
          <div style={{ background:'#fff', borderRadius:16, overflow:'hidden', maxWidth:380, width:'90%', boxShadow:'0 20px 60px rgba(0,0,0,0.25)' }}
            onClick={e => e.stopPropagation()}>
            <div style={{ background:'linear-gradient(135deg,#8B0000,#C41E3A)', padding:'18px 20px', display:'flex', justifyContent:'space-between', alignItems:'center' }}>
              <span style={{ color:'#fff', fontSize:15, fontWeight:700 }}>Report Profile</span>
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
                    <input type="radio" name="report" value={opt.value} checked={reportReason === opt.value} onChange={e => setReportReason(e.target.value)}
                      style={{ accentColor:'#8B0000' }} />
                    <span style={{ fontSize:13, fontWeight:600, color: reportReason === opt.value ? '#8B0000' : '#333' }}>{opt.label}</span>
                  </label>
                ))}
              </div>
              <div style={{ display:'flex', gap:8 }}>
                <button onClick={() => setShowReportModal(false)} style={{ flex:1, padding:10, background:'#f5f5f5', border:'1px solid #e8e8e8', borderRadius:8, fontSize:13, fontWeight:600, color:'#666', cursor:'pointer' }}>Cancel</button>
                <button disabled={!reportReason} onClick={async () => {
                  try {
                    const chk = contactVerified ? await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ action:'contact_check' }), credentials:'include' }).then(r=>r.json()) : {};
                    await fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
                      body: JSON.stringify({ action:'report_profile', cp_id: p.regId, reason: reportReason, reporter_mobile: chk.mobile || '' }), credentials:'include' });
                    alert('Report submitted. Thank you!');
                  } catch(e) { alert('Report submitted.'); }
                  setShowReportModal(false); setReportReason('');
                }} style={{ flex:1, padding:10, background: reportReason ? 'linear-gradient(135deg,#8B0000,#C41E3A)' : '#ddd', color:'#fff', border:'none', borderRadius:8, fontSize:13, fontWeight:700, cursor: reportReason ? 'pointer' : 'not-allowed' }}>Submit Report</button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* Already Married — simple Yes/No confirmation (no reason picker) */}
      {showMarriedConfirm && (
        <div style={{ position:'fixed', inset:0, background:'rgba(0,0,0,0.5)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:3000, backdropFilter:'blur(4px)' }}
          onClick={() => !marriedSubmitting && setShowMarriedConfirm(false)}>
          <div style={{ background:'#fff', borderRadius:16, overflow:'hidden', maxWidth:340, width:'90%', boxShadow:'0 20px 60px rgba(0,0,0,0.25)' }}
            onClick={e => e.stopPropagation()}>
            <div style={{ background:'linear-gradient(135deg,#ea580c,#c2410c)', padding:'18px 20px' }}>
              <span style={{ color:'#fff', fontSize:15, fontWeight:700 }}>Already Married?</span>
            </div>
            <div style={{ padding:20 }}>
              <p style={{ fontSize:13, color:'#333', marginBottom:16, lineHeight:1.5 }}>
                Do you confirm that <b>{p.name || 'this person'}</b> ({p.regId}) is already married and this profile should be reported?
              </p>
              <div style={{ display:'flex', gap:8 }}>
                <button disabled={marriedSubmitting} onClick={() => setShowMarriedConfirm(false)}
                  style={{ flex:1, padding:10, background:'#f5f5f5', border:'1px solid #e8e8e8', borderRadius:8, fontSize:13, fontWeight:600, color:'#666', cursor: marriedSubmitting ? 'not-allowed' : 'pointer' }}>
                  No
                </button>
                <button disabled={marriedSubmitting} onClick={async () => {
                  setMarriedSubmitting(true);
                  await submitReport('already_married');
                  setMarriedSubmitting(false);
                  setShowMarriedConfirm(false);
                  alert('Report submitted. Thank you!');
                }}
                  style={{ flex:1, padding:10, background: marriedSubmitting ? '#ddd' : 'linear-gradient(135deg,#ea580c,#c2410c)', color:'#fff', border:'none', borderRadius:8, fontSize:13, fontWeight:700, cursor: marriedSubmitting ? 'wait' : 'pointer' }}>
                  {marriedSubmitting ? 'Submitting…' : 'Yes, Report'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      {/* OTP Modal */}
      {showOtpModal && (
        <div style={{ position:'fixed', inset:0, background:'rgba(0,0,0,0.5)', display:'flex', alignItems:'center', justifyContent:'center', zIndex:3000, backdropFilter:'blur(4px)' }}
          onClick={() => setShowOtpModal(false)}>
          <div style={{ background:'#fff', borderRadius:20, overflow:'hidden', maxWidth:380, width:'90%', boxShadow:'0 20px 60px rgba(0,0,0,0.25)' }}
            onClick={e => e.stopPropagation()}>
            <div style={{ background:'linear-gradient(135deg,#8B0000,#C41E3A)', padding:22, textAlign:'center' }}>
              <div style={{ fontSize:14, fontWeight:700, color:'#fff' }}>Verify Your Mobile</div>
              <div style={{ fontSize:11, color:'rgba(255,255,255,0.7)', marginTop:4 }}>Enter your number to view contact details</div>
            </div>
            <div style={{ padding:20 }}>
              {!otpSent ? (
                <>
                  <label style={{ fontSize:11, fontWeight:700, color:'#8B0000', textTransform:'uppercase', letterSpacing:0.8, display:'block', marginBottom:6 }}>Mobile Number</label>
                  <input type="tel" maxLength={10} value={otpMobile} onChange={e => setOtpMobile(e.target.value.replace(/\D/g,''))}
                    placeholder="Enter 10-digit mobile" style={{ width:'100%', padding:'12px 14px', border:'1.5px solid #e0e0e0', borderRadius:10, fontSize:16, fontFamily:'monospace', letterSpacing:2, outline:'none', boxSizing:'border-box' }} />
                  <button onClick={sendOtp} disabled={otpLoading}
                    style={{ width:'100%', marginTop:14, padding:12, background:'linear-gradient(135deg,#8B0000,#C41E3A)', color:'#fff', border:'none', borderRadius:10, fontSize:15, fontWeight:700, cursor:'pointer' }}>
                    {otpLoading ? 'Sending...' : 'Send OTP'}
                  </button>
                </>
              ) : (
                <>
                  <p style={{ fontSize:13, color:'#666', textAlign:'center', marginBottom:14 }}>
                    OTP sent to <strong style={{ color:'#8B0000' }}>{otpMobile.substring(0,3)}****{otpMobile.substring(7)}</strong>
                    {otpTimer > 0 && <span style={{ color:'#999' }}> ({Math.floor(otpTimer/60)}:{String(otpTimer%60).padStart(2,'0')})</span>}
                  </p>
                  <div style={{ display:'flex', justifyContent:'center', gap:8, marginBottom:14 }}>
                    {[0,1,2,3].map(i => (
                      <input key={i} id={`otp-box-${i}`} type="tel" maxLength={1} value={otpValue[i]}
                        onChange={e => handleOtpInput(i, e.target.value)}
                        onKeyDown={e => { if (e.key === 'Backspace' && !otpValue[i] && i > 0) document.getElementById(`otp-box-${i-1}`)?.focus(); }}
                        style={{ width:48, height:54, textAlign:'center', fontSize:22, fontWeight:700, border:'2px solid #e0e0e0', borderRadius:12, outline:'none', fontFamily:'monospace', color:'#8B0000' }} />
                    ))}
                  </div>
                  <button onClick={verifyOtp} disabled={otpLoading}
                    style={{ width:'100%', padding:12, background:'linear-gradient(135deg,#8B0000,#C41E3A)', color:'#fff', border:'none', borderRadius:10, fontSize:15, fontWeight:700, cursor:'pointer' }}>
                    {otpLoading ? 'Verifying...' : 'Verify OTP'}
                  </button>
                  {otpTimer === 0 && (
                    <button onClick={() => { setOtpValue(['','','','']); sendOtp(); }}
                      style={{ width:'100%', marginTop:8, padding:10, background:'transparent', color:'#8B0000', border:'1.5px solid #8B0000', borderRadius:10, fontSize:13, fontWeight:600, cursor:'pointer' }}>
                      Resend OTP
                    </button>
                  )}
                </>
              )}
              {otpMsg && <p style={{ textAlign:'center', marginTop:10, fontSize:13, color: otpMsg.includes('OTP:') || otpMsg.includes('sent') ? '#16a34a' : '#dc2626', fontWeight:600 }}>{otpMsg}</p>}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
