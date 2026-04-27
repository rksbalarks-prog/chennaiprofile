// Bilingual profile summary (English + Tamil), 300-500 chars per language.
// Accepts either the home-card shape (mapP) or the Detail page shape (mapProfile)
// — fields are read with fallbacks to handle both naming conventions.

const ok = (v) => {
  if (v == null) return null;
  if (typeof v === 'number') return v ? String(v) : null;
  if (typeof v !== 'string') return null;
  const t = v.trim();
  if (!t) return null;
  if (['-Select-', '-select-', '-Select Rasi-', '-'].includes(t)) return null;
  return t;
};

const join = (parts, sep = ', ') => parts.filter(Boolean).join(sep);

const TA_GENDER = { Male: 'ஆண்', Female: 'பெண்' };
const TA_MARITAL = {
  Unmarried: 'திருமணமாகாதவர்',
  Married: 'திருமணமானவர்',
  Divorced: 'விவாகரத்து பெற்றவர்',
  Widow: 'விதவை',
  Widower: 'விதூர்',
};
const TA_DIET = {
  Veg: 'சைவம்',
  Vegetarian: 'சைவம்',
  'Non-Veg': 'அசைவம்',
  'Non-Vegetarian': 'அசைவம்',
  NonVeg: 'அசைவம்',
  Eggetarian: 'முட்டை சைவம்',
  Any: 'எதுவும்',
};

export function buildSummary(p) {
  if (!p) return { en: '', ta: '' };

  const name = p.name || '';
  const regId = p.regId || p.cpId || p.id || '';
  const motherTongue = p.motherTongue || p.language || '';
  const maritalStatus = p.maritalStatus || p.marital || '';
  const presentCity = p.presentCity || p.city || '';
  const presentDistrict = p.presentDistrict || p.district || '';
  const placeBirth = p.placeBirth || '';
  const placeJob = p.placeJob || '';

  const city = [ok(presentCity), ok(presentDistrict)].filter(Boolean).join(', ');
  const dob = p.dob
    ? new Date(p.dob).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' })
    : '';
  const caste = [ok(p.caste), ok(p.subCaste)].filter(Boolean).join('/');
  const partnerAge = p.partnerAge && String(p.partnerAge).trim() && String(p.partnerAge).trim() !== '-'
    ? p.partnerAge + ' yrs' : '';

  // ===== ENGLISH =====
  const enHeader = `${name}${regId ? ` (${regId})` : ''}` +
    [ok(p.age) && `${p.age} yrs`, ok(p.gender), ok(maritalStatus), city && `from ${city}`]
      .filter(Boolean).reduce((acc, v) => acc + ', ' + v, '');

  const en = [
    enHeader,
    join([ok(motherTongue) && `speaks ${motherTongue}`, ok(p.religion), caste]),
    join([ok(p.height), ok(p.complexion) && `${p.complexion} complexion`, ok(p.diet), ok(p.bloodGroup) && `blood ${p.bloodGroup}`]),
    join([ok(p.qualification), ok(p.job) && `works as ${p.job}${ok(placeJob) ? ` at ${placeJob}` : ''}`, ok(p.income) && `earns Rs.${p.income}/mo`]),
    join([dob && `born ${dob}${ok(placeBirth) ? ` at ${placeBirth}` : ''}`, ok(p.star) && `Star ${p.star}`, ok(p.raasi) && `Raasi ${p.raasi}`, ok(p.lagnam) && `Lagnam ${p.lagnam}`, ok(p.dosham) && `Dosham ${p.dosham}${ok(p.doshamType) ? ` (${p.doshamType})` : ''}`]),
    join([ok(p.fatherJob) && `father ${p.fatherJob}`, ok(p.motherJob) && `mother ${p.motherJob}`]),
    p.partnerQualification || p.partnerJob || partnerAge || p.partnerCaste || p.partnerDiet
      ? `seeks ${join([ok(p.partnerQualification), ok(p.partnerJob), partnerAge, ok(p.partnerCaste), ok(p.partnerDiet)])}`
      : '',
  ].filter(s => s && s.trim()).join('. ') + '.';

  // ===== TAMIL =====
  const taGender = TA_GENDER[p.gender] || ok(p.gender) || '';
  const taMarital = TA_MARITAL[maritalStatus] || ok(maritalStatus) || '';
  const taDiet = TA_DIET[p.diet] || ok(p.diet) || '';
  const taPartnerDiet = TA_DIET[p.partnerDiet] || ok(p.partnerDiet) || '';

  const taHeader = `${name}${regId ? ` (${regId})` : ''}` +
    [ok(p.age) && `${p.age} வயது`, taGender, taMarital, city && `${city}-ல் வசிக்கிறார்`]
      .filter(Boolean).reduce((acc, v, i) => acc + (i === 0 ? ' — ' : ', ') + v, '');

  const ta = [
    taHeader,
    join([ok(motherTongue) && `தாய்மொழி: ${motherTongue}`, ok(p.religion) && `மதம்: ${p.religion}`, caste && `சாதி: ${caste}`]),
    join([ok(p.height) && `உயரம் ${p.height}`, ok(p.complexion) && `${p.complexion} நிறம்`, taDiet, ok(p.bloodGroup) && `இரத்த வகை ${p.bloodGroup}`]),
    join([ok(p.qualification) && `படிப்பு: ${p.qualification}`, ok(p.job) && `பணி: ${p.job}${ok(placeJob) ? ` (${placeJob})` : ''}`, ok(p.income) && `மாத வருமானம் ரூ.${p.income}`]),
    join([dob && `பிறப்பு: ${dob}${ok(placeBirth) ? `, ${placeBirth}` : ''}`, ok(p.star) && `நட்சத்திரம் ${p.star}`, ok(p.raasi) && `ராசி ${p.raasi}`, ok(p.lagnam) && `லக்னம் ${p.lagnam}`, ok(p.dosham) && `தோஷம் ${p.dosham}${ok(p.doshamType) ? ` (${p.doshamType})` : ''}`]),
    join([ok(p.fatherJob) && `தந்தை: ${p.fatherJob}`, ok(p.motherJob) && `தாய்: ${p.motherJob}`]),
    p.partnerQualification || p.partnerJob || partnerAge || p.partnerCaste || p.partnerDiet
      ? `எதிர்பார்ப்பு: ${join([ok(p.partnerQualification), ok(p.partnerJob), partnerAge && `வயது ${partnerAge}`, ok(p.partnerCaste), taPartnerDiet])}`
      : '',
  ].filter(s => s && s.trim()).join('. ') + '.';

  return { en, ta };
}

export default buildSummary;
