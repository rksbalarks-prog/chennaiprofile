import { useState, useMemo, useEffect } from "react";
import { useNavigate, useLocation } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { API_BASE, PHOTO_BASE, UPLOADS_PREFIX, getPhotoUrls } from "./config";
const INIT = { gender:"Female", language:"Any", caste:"Any", sortId:"desc", ageFrom:"", ageTo:"", marital:"Any",
  religion:"Any", star:"Any", raasi:"Any", photo:"any", horoscope:"any", diet:"Any", dosham:"Any",
  heightFrom:"", heightTo:"", qualification:"", district:"", search:"" };

const STARS = ["Any","Ashwini","Bharani","Karthigai","Rohini","Mirigasirisham","Thiruvathirai","Punarpoosam","Poosam","Ayilyam","Makam","Pooram","Uthiram","Hastham","Chithirai","Swathi","Visakam","Anusham","Kettai","Moolam","Pooradam","Uthradam","Thiruvonam","Avittam","Sadhayam","Puratathi","Uthirattathi","Revathi"];
const RAASIS = ["Any","Mesham","Rishabam","Midhunam","Kadagam","Simham","Kanni","Thulam","Viruchigam","Dhanusu","Makaram","Kumbam","Meenam"];
const RELIGIONS = ["Any","Hindu","Muslim","Christian","Sikh","Jain","Buddhist"];
const DIETS = ["Any","Vegetarian","Non-Vegetarian","Eggetarian"];
const DOSHAMS = ["Any","No","Yes","Partial"];
const HEIGHTS = ["Any","4ft 5in","4ft 6in","4ft 7in","4ft 8in","4ft 9in","4ft 10in","4ft 11in","5ft 0in","5ft 1in","5ft 2in","5ft 3in","5ft 4in","5ft 5in","5ft 6in","5ft 7in","5ft 8in","5ft 9in","5ft 10in","5ft 11in","6ft 0in","6ft 1in","6ft 2in","6ft 3in","6ft 4in","6ft 5in"];

export default function MatrimonySearch() {
  const navigate = useNavigate();
  const location = useLocation();
  const { t } = useTranslation();
  const [filters, setFilters] = useState(INIT);
  const [applied, setApplied] = useState(INIT);
  const [showFilters, setShowFilters] = useState(true);
  const [showAdvanced, setShowAdvanced] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const [apiProfiles, setApiProfiles] = useState([]);
  const [loading, setLoading] = useState(false);
  const [totalProfiles, setTotalProfiles] = useState(0);
  const itemsPerPage = 15;

  const genderOptions = t("search.genderOptions", { returnObjects: true }) || [];
  const languageOptions = t("search.languageOptions", { returnObjects: true }) || [];
  const casteOptions = t("search.casteOptions", { returnObjects: true }) || [];
  const maritalOptions = t("search.maritalOptions", { returnObjects: true }) || [];

  const set = (k, v) => setFilters(f => ({ ...f, [k]: v }));

  // Build query string once — all filters sent to server
  const buildParams = (f, page) => {
    const params = new URLSearchParams({ action: 'search' });
    const eq = ['gender','caste','language','religion','star','raasi','diet','dosham'];
    eq.forEach(k => { if (f[k] && f[k] !== 'Any') params.set(k, f[k]); });
    if (f.marital && f.marital !== 'Any') {
      params.set('marital', f.marital === 'Single' ? 'Unmarried' : f.marital === 'Divorce' ? 'Divorced' : f.marital);
    }
    if (f.ageFrom) params.set('ageFrom', f.ageFrom);
    if (f.ageTo) params.set('ageTo', f.ageTo);
    if (f.heightFrom) params.set('heightFrom', f.heightFrom);
    if (f.heightTo) params.set('heightTo', f.heightTo);
    if (f.qualification) params.set('qualification', f.qualification);
    if (f.district) params.set('district', f.district);
    if (f.photo === 'with' || f.photo === 'without') params.set('photo', f.photo);
    if (f.horoscope === 'with' || f.horoscope === 'without') params.set('horoscope', f.horoscope);
    if (f.search) params.set('q', f.search);
    if (f.sortId) params.set('sortId', f.sortId);
    params.set('limit', String(itemsPerPage));
    params.set('offset', String((page - 1) * itemsPerPage));
    return params;
  };

  const mapProfile = (p) => ({
    id: p.cp_id, regId: p.cp_id, name: p.name || 'N/A',
    caste: p.caste || '', gender: p.gender || '',
    language: p.mother_tongue || '', religion: p.religion || '',
    marital: p.marital || '', age: p.age || '',
    height: p.height || '',
    qualification: p.qualification || '', job: p.job || '',
    star: p.star || '', raasi: p.raasi || '',
    district: p.present_district || '', city: p.present_city || '', state: p.present_state || '',
    photo: p.photo1 && !p.photo1.startsWith('default_')
      ? (p.photo1.startsWith('uploads/') ? UPLOADS_PREFIX + p.photo1 : PHOTO_BASE + p.photo1)
      : '',
    photoRaw: p.photo1 || '',
  });

  const fetchProfiles = async (f, page = 1) => {
    setLoading(true);
    try {
      const resp = await fetch(API_BASE + '?' + buildParams(f, page).toString());
      const data = await resp.json();
      if (data.ok && data.profiles) {
        if (typeof data.total === 'number') setTotalProfiles(data.total);
        setApiProfiles(data.profiles.map(mapProfile));
      }
    } catch (e) { console.error('Search error:', e); }
    setLoading(false);
  };

  useEffect(() => {
    if (location.state?.quickSearchFilters) {
      const qf = location.state.quickSearchFilters;
      const newFilters = { ...INIT, ...qf };
      setFilters(newFilters);
      setApplied(newFilters);
      fetchProfiles(newFilters, 1);
      window.history.replaceState({}, document.title);
    } else {
      fetchProfiles(applied, 1);
    }
  }, []);

  // Server returns pre-filtered + pre-sorted page. No client-side filtering.
  const results = apiProfiles;
  const totalPages = Math.max(1, Math.ceil(totalProfiles / itemsPerPage));
  const paged = results;

  const activeFilterCount = Object.entries(applied).filter(([k, v]) => {
    if (['sortId'].includes(k)) return false;
    if (INIT[k] === v) return false;
    if (v === '' || v === 'Any' || v === 'any') return false;
    return true;
  }).length;

  const doSearch = () => { setApplied({ ...filters }); setCurrentPage(1); fetchProfiles(filters, 1); };
  const doReset = () => { setFilters(INIT); setApplied(INIT); setCurrentPage(1); fetchProfiles(INIT, 1); };
  const goToPage = (p) => { setCurrentPage(p); fetchProfiles(applied, p); window.scrollTo(0, 0); };

  const Sel = ({ label, value, onChange, children }) => (
    <div>
      <label style={{ fontSize:10, fontWeight:700, color:'#888', textTransform:'uppercase', letterSpacing:0.5, marginBottom:3, display:'block' }}>{label}</label>
      <select value={value} onChange={e => onChange(e.target.value)}
        style={{ width:'100%', padding:'9px 10px', border:'1.5px solid #e0e0e0', borderRadius:8, fontSize:13, background:'#fff', color:'#333', outline:'none', appearance:'none', cursor:'pointer',
          backgroundImage:"url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath fill='%23999' d='M5 6L0 0h10z'/%3E%3C/svg%3E\")",
          backgroundRepeat:'no-repeat', backgroundPosition:'right 10px center' }}>
        {children}
      </select>
    </div>
  );

  const Inp = ({ label, ...props }) => (
    <div>
      <label style={{ fontSize:10, fontWeight:700, color:'#888', textTransform:'uppercase', letterSpacing:0.5, marginBottom:3, display:'block' }}>{label}</label>
      <input {...props} style={{ width:'100%', padding:'9px 10px', border:'1.5px solid #e0e0e0', borderRadius:8, fontSize:13, background:'#fff', color:'#333', outline:'none', boxSizing:'border-box', ...(props.style||{}) }} />
    </div>
  );

  return (
    <div style={{ background:'#f5f5f5', minHeight:'100vh', paddingBottom:70 }}>

      {/* Top bar */}
      <div style={{ background:'#fff', padding:'10px 16px', borderBottom:'1px solid #f0f0f0', display:'flex', alignItems:'center', justifyContent:'space-between', position:'sticky', top:56, zIndex:50 }}>
        <div style={{ whiteSpace:'nowrap' }}>
          <span style={{ fontSize:14, fontWeight:700, color:'#222' }}>
            {applied.gender !== 'Any' ? applied.gender : 'All'}
          </span>
          <span style={{ fontSize:12, color:'#999', marginLeft:4 }}>· {totalProfiles} found</span>
        </div>
        <div style={{ display:'flex', gap:6 }}>
          <button onClick={() => setShowFilters(!showFilters)}
            style={{ padding:'6px 12px', borderRadius:20, border:'1.5px solid #e0e0e0', background: showFilters ? '#8B0000' : '#fff', color: showFilters ? '#fff' : '#666', fontSize:11, fontWeight:600, cursor:'pointer', display:'flex', alignItems:'center', gap:4 }}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
            Filters {activeFilterCount > 0 && <span style={{ background:'#C41E3A', color:'#fff', borderRadius:10, padding:'0 5px', fontSize:9, fontWeight:700 }}>{activeFilterCount}</span>}
          </button>
          <select value={applied.sortId} onChange={e => { const v = e.target.value; const nf={...filters,sortId:v}; const na={...applied,sortId:v}; setFilters(nf); setApplied(na); setCurrentPage(1); fetchProfiles(na,1); }}
            style={{ padding:'6px 10px', borderRadius:20, border:'1.5px solid #e0e0e0', fontSize:11, fontWeight:600, color:'#666', cursor:'pointer', background:'#fff', outline:'none' }}>
            <option value="desc">Newest</option>
            <option value="asc">Oldest</option>
          </select>
          <select value={applied.photo} onChange={e => { const v = e.target.value; const nf={...filters,photo:v}; const na={...applied,photo:v}; setFilters(nf); setApplied(na); setCurrentPage(1); fetchProfiles(na,1); }}
            style={{ padding:'6px 10px', borderRadius:20, border:'1.5px solid #e0e0e0', fontSize:11, fontWeight:600, color:'#666', cursor:'pointer', background:'#fff', outline:'none' }}>
            <option value="any">All Photos</option>
            <option value="with">With Photo</option>
            <option value="without">No Photo</option>
          </select>
        </div>
      </div>

      {/* Filter Panel */}
      {showFilters && (
        <div style={{ background:'#fff', padding:'14px 16px', borderBottom:'1px solid #f0f0f0' }}>
          {/* Search bar */}
          <div style={{ marginBottom:12 }}>
            <Inp label="Search by Name or CP ID" value={filters.search} onChange={e => set('search', e.target.value)} placeholder="e.g. Priya or CM2012345" />
          </div>

          {/* Basic Filters */}
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
            <div style={{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:6 }}>
              <Inp label="Age From" type="number" min={18} max={80} value={filters.ageFrom} onChange={e => set('ageFrom', e.target.value)} placeholder="18" />
              <Inp label="Age To" type="number" min={18} max={80} value={filters.ageTo} onChange={e => set('ageTo', e.target.value)} placeholder="60" />
            </div>
          </div>

          {/* Advanced toggle */}
          <button onClick={() => setShowAdvanced(!showAdvanced)}
            style={{ marginTop:10, background:'none', border:'none', color:'#8B0000', fontSize:12, fontWeight:600, cursor:'pointer', display:'flex', alignItems:'center', gap:4, padding:0 }}>
            {showAdvanced ? '- Hide' : '+ Show'} Advanced Filters
          </button>

          {/* Advanced Filters */}
          {showAdvanced && (
            <div style={{ marginTop:10, display:'grid', gridTemplateColumns:'1fr 1fr', gap:'10px 8px' }}>
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
                {HEIGHTS.map(h => <option key={h} value={h === 'Any' ? '' : h}>{h}</option>)}
              </Sel>
              <Sel label="Height To" value={filters.heightTo} onChange={v => set('heightTo', v)}>
                {HEIGHTS.map(h => <option key={h} value={h === 'Any' ? '' : h}>{h}</option>)}
              </Sel>
              <Inp label="Qualification" value={filters.qualification} onChange={e => set('qualification', e.target.value)} placeholder="e.g. B.E, MBA" />
              <Inp label="District / City" value={filters.district} onChange={e => set('district', e.target.value)} placeholder="e.g. Thanjavur" />
              <Sel label="Horoscope Chart" value={filters.horoscope} onChange={v => set('horoscope', v)}>
                <option value="any">Any</option>
                <option value="with">With Horoscope</option>
                <option value="without">Without Horoscope</option>
              </Sel>
            </div>
          )}

          {/* Search / Reset */}
          <div style={{ display:'flex', gap:8, marginTop:12 }}>
            <button onClick={doSearch}
              style={{ flex:1, padding:'10px', background:'linear-gradient(135deg,#8B0000,#C41E3A)', color:'#fff', border:'none', borderRadius:8, fontSize:14, fontWeight:700, cursor:'pointer' }}>
              Search
            </button>
            <button onClick={doReset}
              style={{ padding:'10px 20px', background:'#fff', color:'#8B0000', border:'1.5px solid #8B0000', borderRadius:8, fontSize:13, fontWeight:600, cursor:'pointer' }}>
              Reset
            </button>
          </div>
        </div>
      )}

      {/* Results */}
      {loading ? (
        <div style={{ textAlign:'center', padding:60, color:'#999', fontSize:14 }}>Loading profiles...</div>
      ) : results.length === 0 ? (
        <div style={{ textAlign:'center', padding:60, color:'#bbb' }}>
          <div style={{ fontSize:40, marginBottom:10 }}>?</div>
          <div style={{ fontSize:14, fontWeight:500 }}>No profiles found</div>
          <div style={{ fontSize:12, color:'#ccc', marginTop:4 }}>Try adjusting your filters</div>
        </div>
      ) : (
        <div style={{ padding:12, display:'flex', flexDirection:'column', gap:10, maxWidth:900, margin:'0 auto' }}>
          {paged.map(p => (
            <div key={p.id} style={{ display:'flex', background:'#fff', borderRadius:12, overflow:'hidden', boxShadow:'0 1px 6px rgba(0,0,0,0.06)', border:'1px solid #f0f0f0', cursor:'pointer' }}
              onClick={() => navigate(`/detail/${p.id}`, { state: { profile: p } })}>
              {(() => {
                const genderFallback = p.gender === 'Male' ? '/default-male.png' : '/default-female.png';
                if (!p.photo) {
                  return (
                    <img src={genderFallback} alt={p.name}
                      loading="lazy" decoding="async"
                      width="110" height="130"
                      style={{ width:110, height:130, objectFit:'cover', flexShrink:0, background:'#f5f5f5' }} />
                  );
                }
                const urls = getPhotoUrls(p.photoRaw);
                return (
                  <picture>
                    {urls && <source type="image/webp" srcSet={urls.thumb} />}
                    <img src={urls ? urls.orig : p.photo} alt={p.name}
                      loading="lazy" decoding="async"
                      width="110" height="130"
                      style={{ width:110, height:130, objectFit:'cover', flexShrink:0, background:'#f5f5f5' }}
                      onError={e => { e.target.onerror=null; e.target.src = genderFallback; }} />
                  </picture>
                );
              })()}
              <div style={{ flex:1, padding:'10px 12px', display:'flex', flexDirection:'column', gap:3, minWidth:0 }}>
                <div style={{ fontSize:14, fontWeight:700, color:'#222', overflow:'hidden', textOverflow:'ellipsis', whiteSpace:'nowrap' }}>{p.name}</div>
                <span style={{ fontSize:11, color:'#8B0000', fontWeight:600, background:'#fef2f2', padding:'1px 8px', borderRadius:4, width:'fit-content' }}>{p.regId}</span>
                <div style={{ fontSize:12, color:'#777' }}>
                  {[p.age ? p.age+' yrs' : '', p.height, [p.city,p.district,p.state].filter(Boolean).join(', ')].filter(Boolean).join(' · ')}
                </div>
                <div style={{ fontSize:12, color:'#777' }}>
                  {[p.qualification, p.job].filter(Boolean).join(' · ')}
                </div>
                <div style={{ fontSize:12, color:'#777' }}>
                  {[p.star, p.raasi].filter(Boolean).join(' · ')}
                </div>
                <div style={{ display:'flex', gap:4, flexWrap:'wrap', marginTop:'auto' }}>
                  {p.caste && <span style={{ fontSize:10, fontWeight:600, padding:'2px 7px', borderRadius:10, background:'#f5f0ff', color:'#6d28d9' }}>{p.caste}</span>}
                  {p.religion && <span style={{ fontSize:10, fontWeight:600, padding:'2px 7px', borderRadius:10, background:'#fff7ed', color:'#c2410c' }}>{p.religion}</span>}
                  {p.marital && <span style={{ fontSize:10, fontWeight:600, padding:'2px 7px', borderRadius:10, background:'#f0fdf4', color:'#166534' }}>{p.marital}</span>}
                </div>
              </div>
            </div>
          ))}

          {/* Pagination */}
          {totalPages > 1 && (
            <div style={{ display:'flex', justifyContent:'center', alignItems:'center', gap:12, padding:'12px 0' }}>
              <button disabled={currentPage===1 || loading} onClick={() => goToPage(currentPage - 1)}
                style={{ padding:'8px 16px', borderRadius:8, border:'1.5px solid #e0e0e0', background:'#fff', color: currentPage===1 ? '#ccc' : '#8B0000', fontSize:13, fontWeight:600, cursor: currentPage===1 ? 'not-allowed' : 'pointer' }}>
                Prev
              </button>
              <span style={{ fontSize:13, color:'#999' }}>Page <strong>{currentPage}</strong> / <strong>{totalPages}</strong></span>
              <button disabled={currentPage===totalPages || loading} onClick={() => goToPage(currentPage + 1)}
                style={{ padding:'8px 16px', borderRadius:8, border:'1.5px solid #e0e0e0', background: currentPage===totalPages ? '#fff' : '#8B0000', color: currentPage===totalPages ? '#ccc' : '#fff', fontSize:13, fontWeight:600, cursor: currentPage===totalPages ? 'not-allowed' : 'pointer' }}>
                Next
              </button>
            </div>
          )}
        </div>
      )}
    </div>
  );
}
