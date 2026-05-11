import { useState, useEffect } from "react";
import { useLocation } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { API_BASE, getPhotoUrls, PREFIX, USER_PANEL_URL } from "./config";

const C  = '#0D7B6A';   // app primary teal
const CG = 'linear-gradient(135deg,#0D7B6A,#6B3FA0)'; // app gradient

const INIT = {
  gender:"Female", language:"Any", caste:"Any", sortId:"desc",
  ageFrom:"", ageTo:"", marital:"Any", religion:"Any",
  star:"Any", raasi:"Any", photo:"any", horoscope:"any",
  diet:"Any", dosham:"Any", heightFrom:"", heightTo:"",
  qualification:"", district:"", search:""
};

const STARS    = ["Any","Ashwini","Bharani","Karthigai","Rohini","Mirigasirisham","Thiruvathirai","Punarpoosam","Poosam","Ayilyam","Makam","Pooram","Uthiram","Hastham","Chithirai","Swathi","Visakam","Anusham","Kettai","Moolam","Pooradam","Uthradam","Thiruvonam","Avittam","Sadhayam","Puratathi","Uthirattathi","Revathi"];
const RAASIS   = ["Any","Mesham","Rishabam","Midhunam","Kadagam","Simham","Kanni","Thulam","Viruchigam","Dhanusu","Makaram","Kumbam","Meenam"];
const RELIGIONS= ["Any","Hindu","Muslim","Christian","Sikh","Jain","Buddhist"];
const DIETS    = ["Any","Vegetarian","Non-Vegetarian","Eggetarian"];
const DOSHAMS  = ["Any","No","Yes","Partial"];
const HEIGHTS  = ["Any","4ft 5in","4ft 6in","4ft 7in","4ft 8in","4ft 9in","4ft 10in","4ft 11in","5ft 0in","5ft 1in","5ft 2in","5ft 3in","5ft 4in","5ft 5in","5ft 6in","5ft 7in","5ft 8in","5ft 9in","5ft 10in","5ft 11in","6ft 0in","6ft 1in","6ft 2in","6ft 3in","6ft 4in","6ft 5in"];
const AGES     = ["Any", ...Array.from({length: 63}, (_, i) => String(18 + i))]; // 18–80

export default function MatrimonySearch() {
  const location  = useLocation();
  const { t }     = useTranslation();

  const [filters, setFilters]         = useState(INIT);
  const [applied, setApplied]         = useState(INIT);
  const [showFilters, setShowFilters] = useState(true);
  const [showAdvanced, setShowAdvanced] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [apiProfiles, setApiProfiles] = useState([]);
  const [loading, setLoading]         = useState(false);
  const [totalProfiles, setTotalProfiles] = useState(0);
  const [isLoggedIn, setIsLoggedIn]   = useState(false);
  const itemsPerPage = 15;

  const genderOptions  = t("search.genderOptions",  { returnObjects: true }) || [];
  const languageOptions= t("search.languageOptions", { returnObjects: true }) || [];
  const casteOptions   = t("search.casteOptions",    { returnObjects: true }) || [];
  const maritalOptions = t("search.maritalOptions",  { returnObjects: true }) || [];

  const set = (k, v) => setFilters(f => ({ ...f, [k]: v }));

  const buildParams = (f, page) => {
    const p = new URLSearchParams({ action: 'search' });
    ['gender','caste','language','religion','star','raasi','diet','dosham'].forEach(k => {
      if (f[k] && f[k] !== 'Any') p.set(k, f[k]);
    });
    if (f.marital && f.marital !== 'Any')
      p.set('marital', f.marital==='Single' ? 'Unmarried' : f.marital==='Divorce' ? 'Divorced' : f.marital);
    if (f.ageFrom)       p.set('ageFrom',       f.ageFrom);
    if (f.ageTo)         p.set('ageTo',         f.ageTo);
    if (f.heightFrom)    p.set('heightFrom',    f.heightFrom);
    if (f.heightTo)      p.set('heightTo',      f.heightTo);
    if (f.qualification) p.set('qualification', f.qualification);
    if (f.district)      p.set('district',      f.district);
    if (f.photo === 'with' || f.photo === 'without') p.set('photo', f.photo);
    if (f.horoscope === 'with' || f.horoscope === 'without') p.set('horoscope', f.horoscope);
    if (f.search)  p.set('q',      f.search);
    if (f.sortId)  p.set('sortId', f.sortId);
    p.set('limit',  String(itemsPerPage));
    p.set('offset', String((page - 1) * itemsPerPage));
    return p;
  };

  const mapProfile = p => {
    const urls = getPhotoUrls(p.photo1);
    return {
      id: p.cp_id, regId: p.cp_id, name: p.name || 'N/A',
      caste: p.caste || '', gender: p.gender || '',
      language: p.mother_tongue || '', religion: p.religion || '',
      marital: p.marital || '', age: p.age || '', height: p.height || '',
      qualification: p.qualification || '', job: p.job || '',
      star: p.star || '', raasi: p.raasi || '',
      district: p.present_district || '', city: p.present_city || '', state: p.present_state || '',
      urls,
      photoRaw: p.photo1 || '',
    };
  };

  const fetchProfiles = async (f, page = 1) => {
    setLoading(true);
    try {
      const resp = await fetch(API_BASE + '?' + buildParams(f, page).toString());
      const data = await resp.json();
      if (data.ok && data.profiles) {
        if (typeof data.total === 'number') setTotalProfiles(data.total);
        setApiProfiles(data.profiles.map(mapProfile));
      }
    } catch (e) {}
    setLoading(false);
  };

  useEffect(() => {
    // Check login status
    fetch(API_BASE, { method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ action:'contact_check' }), credentials:'include' })
      .then(r => r.json()).then(d => { if (d.ok && d.verified) setIsLoggedIn(true); }).catch(() => {});

    if (location.state?.quickSearchFilters) {
      const qf = location.state.quickSearchFilters;
      const nf = { ...INIT, ...qf };
      setFilters(nf); setApplied(nf); fetchProfiles(nf, 1);
      window.history.replaceState({}, document.title);
    } else {
      fetchProfiles(applied, 1);
    }
  }, []);

  const totalPages = Math.max(1, Math.ceil(totalProfiles / itemsPerPage));

  const activeFilterCount = Object.entries(applied).filter(([k, v]) => {
    if (k === 'sortId') return false;
    if (INIT[k] === v) return false;
    if (v === '' || v === 'Any' || v === 'any') return false;
    return true;
  }).length;

  const advancedActiveCount = Object.entries(applied).filter(([k, v]) => {
    if (!['star','raasi','diet','dosham','heightFrom','heightTo','qualification','district','horoscope'].includes(k)) return false;
    if (INIT[k] === v) return false;
    if (v === '' || v === 'Any' || v === 'any') return false;
    return true;
  }).length;

  const doSearch = () => { setApplied({...filters}); setCurrentPage(1); fetchProfiles(filters, 1); };
  const doReset  = () => { setFilters(INIT); setApplied(INIT); setCurrentPage(1); fetchProfiles(INIT, 1); setShowAdvanced(false); };
  const clearAdvanced = () => setFilters(f => ({
    ...f, star:'Any', raasi:'Any', diet:'Any', dosham:'Any',
    heightFrom:'', heightTo:'', qualification:'', district:'', horoscope:'any'
  }));
  const goToPage = p => { setCurrentPage(p); fetchProfiles(applied, p); window.scrollTo(0, 0); };

  const selStyle = {
    width:'100%', padding:'9px 10px', border:'1.5px solid #e0e0e0', borderRadius:8,
    fontSize:15, background:'#fff', color:'#333', outline:'none', appearance:'none', cursor:'pointer',
    backgroundImage:"url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%23999' d='M5 6L0 0h10z'/%3E%3C/svg%3E\")",
    backgroundRepeat:'no-repeat', backgroundPosition:'right 10px center'
  };
  const inpStyle = { width:'100%', padding:'9px 10px', border:'1.5px solid #e0e0e0', borderRadius:8,
    fontSize:15, background:'#fff', color:'#333', outline:'none', boxSizing:'border-box' };
  const lblStyle = { fontSize:12, fontWeight:700, color:'#888', textTransform:'uppercase',
    letterSpacing:0.5, marginBottom:3, display:'block' };

  const Sel = ({ label, value, onChange, children }) => (
    <div>
      <label style={lblStyle}>{label}</label>
      <select value={value} onChange={e => onChange(e.target.value)} style={selStyle}>{children}</select>
    </div>
  );
  const Inp = ({ label, ...props }) => (
    <div>
      <label style={lblStyle}>{label}</label>
      <input {...props} style={{ ...inpStyle, ...(props.style||{}) }} />
    </div>
  );

  return (
    <div style={{ background:'#fafafa', minHeight:'100vh', paddingBottom:70 }}>

      {/* Top bar */}
      <div style={{ background:'#fff', padding:'10px 16px', borderBottom:'1px solid #f0f0f0',
        display:'flex', alignItems:'center', justifyContent:'space-between',
        position:'sticky', top:56, zIndex:50 }}>
        <div>
          <span style={{ fontSize:18, fontWeight:700, color:'#222' }}>
            {applied.gender !== 'Any' ? applied.gender : 'All'}
          </span>
          <span style={{ fontSize:15, color:'#999', marginLeft:4 }}>· {totalProfiles} found</span>
        </div>
        <div style={{ display:'flex', gap:6 }}>
          <button onClick={() => setShowFilters(!showFilters)}
            style={{ padding:'6px 12px', borderRadius:20, border:`1.5px solid ${C}`,
              background: showFilters ? C : '#fff', color: showFilters ? '#fff' : C,
              fontSize:14, fontWeight:600, cursor:'pointer', display:'flex', alignItems:'center', gap:4 }}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
              <line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/>
            </svg>
            Filters {activeFilterCount > 0 && (
              <span style={{ background:'#065F46', color:'#fff', borderRadius:10, padding:'0 5px', fontSize:11, fontWeight:700 }}>
                {activeFilterCount}
              </span>
            )}
          </button>
          <select value={applied.sortId} onChange={e => { const v=e.target.value; const nf={...filters,sortId:v}; const na={...applied,sortId:v}; setFilters(nf); setApplied(na); setCurrentPage(1); fetchProfiles(na,1); }}
            style={{ padding:'6px 10px', borderRadius:20, border:'1.5px solid #e0e0e0', fontSize:13, fontWeight:600, color:'#555', cursor:'pointer', background:'#fff', outline:'none' }}>
            <option value="desc">Newest</option>
            <option value="asc">Oldest</option>
          </select>
          <select value={applied.photo} onChange={e => { const v=e.target.value; const nf={...filters,photo:v}; const na={...applied,photo:v}; setFilters(nf); setApplied(na); setCurrentPage(1); fetchProfiles(na,1); }}
            style={{ padding:'6px 10px', borderRadius:20, border:'1.5px solid #e0e0e0', fontSize:13, fontWeight:600, color:'#555', cursor:'pointer', background:'#fff', outline:'none' }}>
            <option value="any">All Photos</option>
            <option value="with">With Photo</option>
            <option value="without">No Photo</option>
          </select>
        </div>
      </div>

      {/* Filter Panel */}
      {showFilters && (
        <div style={{ background:'#fff', padding:'14px 16px', borderBottom:'1px solid #f0f0f0' }}>

          {/* Name / ID search */}
          <div style={{ marginBottom:12 }}>
            <Inp label="Search by Name or CP ID" value={filters.search}
              onChange={e => set('search', e.target.value)} placeholder="e.g. Priya or CM2012345" />
          </div>

          {/* Basic filters */}
          <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:'10px 8px' }}>
            <Sel label="Gender" value={filters.gender} onChange={v => set('gender', v)}>
              {genderOptions.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
            </Sel>
            <Sel label="Religion" value={filters.religion} onChange={v => set('religion', v)}>
              {RELIGIONS.map(r => <option key={r} value={r}>{r}</option>)}
            </Sel>
            <Sel label="Caste" value={filters.caste} onChange={v => set('caste', v)}>
              {casteOptions.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
            </Sel>
            <Sel label="Language" value={filters.language} onChange={v => set('language', v)}>
              {languageOptions.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
            </Sel>
            <Sel label="Marital Status" value={filters.marital} onChange={v => set('marital', v)}>
              {maritalOptions.map(o => <option key={o.value} value={o.value}>{o.label}</option>)}
            </Sel>
            <div />
            <Sel label="Age From" value={filters.ageFrom || 'Any'} onChange={v => set('ageFrom', v === 'Any' ? '' : v)}>
              {AGES.map(a => <option key={a} value={a === 'Any' ? '' : a}>{a}</option>)}
            </Sel>
            <Sel label="Age To" value={filters.ageTo || 'Any'} onChange={v => set('ageTo', v === 'Any' ? '' : v)}>
              {AGES.map(a => <option key={a} value={a === 'Any' ? '' : a}>{a}</option>)}
            </Sel>
          </div>

          {/* Advanced toggle */}
          <button onClick={() => { if (!isLoggedIn) return; setShowAdvanced(!showAdvanced); }}
            style={{ marginTop:12, background:'none', border:'none', color: isLoggedIn ? C : '#999',
              fontSize:15, fontWeight:600, cursor: isLoggedIn ? 'pointer' : 'default',
              display:'flex', alignItems:'center', gap:6, padding:0 }}>
            <span style={{ fontSize:15 }}>{showAdvanced ? '▲' : '▼'}</span>
            {showAdvanced ? 'Hide' : '+ Show'} Advanced Filters
            {advancedActiveCount > 0 && (
              <span style={{ background:'#065F46', color:'#fff', borderRadius:10, padding:'1px 6px', fontSize:11, fontWeight:700 }}>
                {advancedActiveCount} active
              </span>
            )}
            {!isLoggedIn && <span style={{ fontSize:11, color:'#aaa', fontWeight:500 }}>(Login required)</span>}
          </button>

          {/* Advanced filters — login gate */}
          {showAdvanced && !isLoggedIn && (
            <div style={{ marginTop:10, border:'1.5px solid #C8EDE6', borderRadius:10, padding:'20px 16px',
              background:'#F4FAF8', textAlign:'center' }}>
              <div style={{ fontSize:28, marginBottom:8 }}>🔒</div>
              <div style={{ fontSize:16, fontWeight:700, color:'#065F46', marginBottom:4 }}>Login Required</div>
              <div style={{ fontSize:14, color:'#0D7B6A', marginBottom:14 }}>
                Advanced filters are available for registered members only.
              </div>
              <a href={USER_PANEL_URL}
                style={{ display:'inline-block', padding:'9px 24px', background:CG, color:'#fff',
                  borderRadius:8, fontSize:15, fontWeight:700, textDecoration:'none' }}>
                Login / Register
              </a>
            </div>
          )}

          {/* Advanced filters — logged in */}
          {showAdvanced && isLoggedIn && (
            <div style={{ marginTop:10, border:`1.5px solid #fecaca`, borderRadius:10,
              padding:'14px 12px', background:'#F4FAF8' }}>
              <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:'10px 8px' }}>
                <Sel label="Star (Nakshatra)" value={filters.star} onChange={v => set('star', v)}>
                  {STARS.map(s => <option key={s} value={s}>{s}</option>)}
                </Sel>
                <Sel label="Raasi" value={filters.raasi} onChange={v => set('raasi', v)}>
                  {RAASIS.map(r => <option key={r} value={r}>{r}</option>)}
                </Sel>
                <Sel label="Diet" value={filters.diet} onChange={v => set('diet', v)}>
                  {DIETS.map(d => <option key={d} value={d}>{d}</option>)}
                </Sel>
                <Sel label="Dosham" value={filters.dosham} onChange={v => set('dosham', v)}>
                  {DOSHAMS.map(d => <option key={d} value={d}>{d}</option>)}
                </Sel>
                <Sel label="Height From" value={filters.heightFrom} onChange={v => set('heightFrom', v)}>
                  {HEIGHTS.map(h => <option key={h} value={h==='Any'?'':h}>{h}</option>)}
                </Sel>
                <Sel label="Height To" value={filters.heightTo} onChange={v => set('heightTo', v)}>
                  {HEIGHTS.map(h => <option key={h} value={h==='Any'?'':h}>{h}</option>)}
                </Sel>
                <Inp label="Qualification" value={filters.qualification}
                  onChange={e => set('qualification', e.target.value)} placeholder="e.g. B.E, MBA" />
                <Inp label="District / City" value={filters.district}
                  onChange={e => set('district', e.target.value)} placeholder="e.g. Thanjavur" />
                <Sel label="Horoscope Chart" value={filters.horoscope} onChange={v => set('horoscope', v)}>
                  <option value="any">Any</option>
                  <option value="with">With Horoscope</option>
                  <option value="without">Without Horoscope</option>
                </Sel>
              </div>
              <div style={{ display:'flex', gap:8, marginTop:12 }}>
                <button onClick={doSearch}
                  style={{ flex:1, padding:'9px', background:CG, color:'#fff', border:'none',
                    borderRadius:8, fontSize:16, fontWeight:700, cursor:'pointer' }}>
                  Search
                </button>
                <button onClick={clearAdvanced}
                  style={{ padding:'9px 16px', background:'#fff', color:'#666',
                    border:'1.5px solid #e0e0e0', borderRadius:8, fontSize:14, fontWeight:600, cursor:'pointer' }}>
                  Clear Filters
                </button>
              </div>
            </div>
          )}

          {/* Main Search / Reset */}
          <div style={{ display:'flex', gap:8, marginTop:12 }}>
            <button onClick={doSearch}
              style={{ flex:1, padding:'11px', background:CG, color:'#fff', border:'none',
                borderRadius:8, fontSize:17, fontWeight:700, cursor:'pointer' }}>
              Search
            </button>
            <button onClick={doReset}
              style={{ padding:'11px 20px', background:'#fff', color:C,
                border:`1.5px solid ${C}`, borderRadius:8, fontSize:16, fontWeight:600, cursor:'pointer' }}>
              Reset
            </button>
          </div>
        </div>
      )}

      {/* Results */}
      {loading ? (
        <div style={{ textAlign:'center', padding:60, color:'#999', fontSize:18 }}>Loading profiles...</div>
      ) : apiProfiles.length === 0 ? (
        <div style={{ textAlign:'center', padding:60, color:'#bbb' }}>
          <div style={{ fontSize:44, marginBottom:10 }}>🔍</div>
          <div style={{ fontSize:18, fontWeight:500 }}>No profiles found</div>
          <div style={{ fontSize:15, color:'#ccc', marginTop:4 }}>Try adjusting your filters</div>
        </div>
      ) : (
        <div style={{ padding:12, display:'flex', flexDirection:'column', gap:14, maxWidth:560, margin:'0 auto' }}>
          {apiProfiles.map(p => {
            const goDetail = () => window.open(`${PREFIX}/detail/${p.id}`, '_blank');
            const genderFallback = p.gender === 'Male' ? '/default-male.png' : '/default-female.png';
            const locationStr = [p.city, p.district, p.state].filter(Boolean).join(', ');
            return (
              <div key={p.id} onClick={goDetail}
                style={{ background:'#fff', borderRadius:14, overflow:'hidden',
                  boxShadow:'0 2px 10px rgba(0,0,0,0.07)', border:'1px solid #C8EDE6', cursor:'pointer' }}>

                {/* Photo */}
                <div style={{ position:'relative', width:'100%', aspectRatio:'4/3', background:'#f9f0f0' }}>
                  {p.urls ? (
                    <picture>
                      <source type="image/webp" srcSet={`${p.urls.thumb} 1x, ${p.urls.full} 2x`} />
                      <img src={p.urls.orig} alt={p.name} loading="lazy" decoding="async"
                        style={{ width:'100%', height:'100%', objectFit:'cover', objectPosition:'center 20%', display:'block' }}
                        onError={e => { e.target.onerror=null; e.target.src=genderFallback; }} />
                    </picture>
                  ) : (
                    <img src={genderFallback} alt={p.name} loading="lazy"
                      style={{ width:'100%', height:'100%', objectFit:'cover', display:'block' }} />
                  )}
                  <span style={{ position:'absolute', top:10, right:10, background:'rgba(13,123,106,0.85)',
                    color:'#fff', fontSize:14, fontWeight:700, padding:'3px 10px', borderRadius:14, backdropFilter:'blur(4px)' }}>
                    1 / 1
                  </span>
                </div>

                {/* ID + religion + Call Now */}
                <div style={{ padding:'10px 14px 6px', display:'flex', alignItems:'center', justifyContent:'space-between', gap:8 }}>
                  <div style={{ display:'flex', alignItems:'center', gap:8, flexWrap:'wrap', minWidth:0 }}>
                    <span style={{ fontSize:15, color:C, fontWeight:700, letterSpacing:0.3 }}>{p.regId}</span>
                    {p.religion && <span style={{ fontSize:13, fontWeight:600, padding:'2px 8px', borderRadius:12,
                      background:'#F4FAF8', color:'#0D7B6A', border:'1px solid #C8EDE6' }}>{p.religion}</span>}
                  </div>
                  <button onClick={e => { e.stopPropagation(); goDetail(); }}
                    style={{ display:'flex', alignItems:'center', gap:5, background:CG, color:'#fff', border:'none',
                      borderRadius:18, padding:'6px 12px', fontSize:14, fontWeight:700, cursor:'pointer',
                      boxShadow:'0 1px 4px rgba(13,123,106,0.3)', whiteSpace:'nowrap' }}>
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor">
                      <path d="M20 15.5c-1.25 0-2.45-.2-3.57-.57a1 1 0 0 0-1.02.24l-2.2 2.2a15.07 15.07 0 0 1-6.59-6.58l2.2-2.21a.96.96 0 0 0 .25-1A11.36 11.36 0 0 1 8.5 4a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1c0 9.39 7.61 17 17 17a1 1 0 0 0 1-1v-3.5a1 1 0 0 0-1-1z"/>
                    </svg>
                    Call Now
                  </button>
                </div>

                {/* Name */}
                <div style={{ padding:'2px 14px 4px', display:'flex', alignItems:'center', gap:6 }}>
                  <span style={{ color:C, fontSize:17 }}>📍</span>
                  <span style={{ fontSize:19, fontWeight:700, color:'#1a1a2e', overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>{p.name}</span>
                </div>

                {/* Tags */}
                <div style={{ padding:'2px 14px 6px', display:'flex', gap:6, flexWrap:'wrap' }}>
                  {p.caste  && <span style={{ fontSize:13, fontWeight:600, padding:'3px 8px', borderRadius:6, background:'#f3f4f6', color:'#374151' }}>Caste: {p.caste}</span>}
                  {p.marital && <span style={{ fontSize:13, fontWeight:600, padding:'3px 8px', borderRadius:6, background:'#f3f4f6', color:'#374151' }}>Status: {p.marital}</span>}
                </div>

                {/* Stats */}
                <div style={{ padding:'4px 14px 10px', display:'flex', gap:12, flexWrap:'wrap', fontSize:15, color:'#4b5563' }}>
                  {p.gender && <span>👤 {p.gender}</span>}
                  {p.age    && <span>🎂 {p.age} yrs</span>}
                  {p.height && <span>📏 {p.height}</span>}
                </div>

                {/* Education / Occupation bar */}
                <div style={{ background:'linear-gradient(135deg,#E8F5F2,#fafafa)', borderTop:'1px solid #fecaca', borderBottom:'1px solid #fecaca', display:'flex' }}>
                  <div style={{ flex:1, padding:'10px 14px', borderLeft:`3px solid ${C}` }}>
                    <div style={{ fontSize:11, fontWeight:700, color:'#9ca3af', letterSpacing:0.5, textTransform:'uppercase' }}>Education</div>
                    <div style={{ fontSize:16, fontWeight:700, color:C, marginTop:2, overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>{p.qualification || '—'}</div>
                  </div>
                  <div style={{ flex:1, padding:'10px 14px', textAlign:'right' }}>
                    <div style={{ fontSize:11, fontWeight:700, color:'#9ca3af', letterSpacing:0.5, textTransform:'uppercase' }}>Occupation</div>
                    <div style={{ fontSize:16, fontWeight:700, color:'#1a1a2e', marginTop:2, overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>{p.job || '—'}</div>
                  </div>
                </div>

                {/* Footer */}
                <div style={{ padding:'8px 14px', display:'flex', alignItems:'center', justifyContent:'space-between', fontSize:14, color:'#6b7280', background:'#fafafa' }}>
                  <span style={{ overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap', minWidth:0 }}>
                    📍 {locationStr || 'Location not set'}
                  </span>
                  <span style={{ color:C, fontWeight:600, whiteSpace:'nowrap', marginLeft:8 }}>
                    📞 View Contact
                  </span>
                </div>
              </div>
            );
          })}

          {/* Pagination */}
          {totalPages > 1 && (
            <div style={{ display:'flex', justifyContent:'center', alignItems:'center', gap:12, padding:'12px 0' }}>
              <button disabled={currentPage===1||loading} onClick={() => goToPage(currentPage-1)}
                style={{ padding:'8px 18px', borderRadius:8, border:`1.5px solid ${C}`, background:'#fff',
                  color: currentPage===1 ? '#ccc' : C, fontSize:16, fontWeight:600,
                  cursor: currentPage===1 ? 'not-allowed' : 'pointer',
                  borderColor: currentPage===1 ? '#e0e0e0' : C }}>
                ← Prev
              </button>
              <span style={{ fontSize:15, color:'#999' }}>Page <strong>{currentPage}</strong> / <strong>{totalPages}</strong></span>
              <button disabled={currentPage===totalPages||loading} onClick={() => goToPage(currentPage+1)}
                style={{ padding:'8px 18px', borderRadius:8, border:'none',
                  background: currentPage===totalPages ? '#e0e0e0' : CG,
                  color: currentPage===totalPages ? '#999' : '#fff',
                  fontSize:16, fontWeight:600, cursor: currentPage===totalPages ? 'not-allowed' : 'pointer' }}>
                Next →
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
