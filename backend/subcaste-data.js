// ===== SUBCASTE DATA: Standardized Caste → Subcaste mapping =====
// Based on Tamil Nadu government BC/MBC/SC/ST records
// Shared by user-panel.php and admin-panel.php

const SUBCASTE_MAP = {
  "Adi Dravidar": [
    "Arunthathiar", "Chakkiliyan", "Kuravan", "Madiga", "Pallar", "Parayan", "Valluvar"
  ],
  "Agamudayar": [
    "Agamudaiyar", "Arcot Mudaliar", "Maravar", "Pillai", "Thuluva Vellalar"
  ],
  "Brahmin": [
    "Iyer", "Iyengar", "Smartha", "Vadakalai", "Vadamal", "Asthasahasram", "Thenkalai"
  ],
  "Chettiar": [
    "24 Manai Telugu Chettiar", "Arya Vysya", "Devanga Chettiar", "Kongu Chettiar",
    "Nagarathar", "Natukottai Chettiar", "Padmasaliar", "Saiva Chettiar",
    "Senai Thalaivar Chettiar", "Sozhia Chettiar", "Vaniar Chettiar"
  ],
  "Devendra Kula Vellalar": [
    "Devendrakula Vellalar", "Pallar"
  ],
  "Gounder": [
    "Kongu Vellala Gounder", "Kurumba Gounder", "Nattu Gounder",
    "Periya Gounder", "Vettuva Gounder", "Vanniya Gounder"
  ],
  "Gramani": [
    "Gramani"
  ],
  "Iyengar": [
    "Thenkalai", "Vadakalai"
  ],
  "Iyer": [
    "Ashtasahasram", "Brahacharanam", "Vadamal", "Vathimal"
  ],
  "Kallar": [
    "Ambalakarar", "Kondayan Kottai Maravar", "Piramalai Kallar"
  ],
  "Kulalar": [
    "Kulalar", "Mannudaiyar", "Udayar"
  ],
  "Labbai": [
    "Labbai", "Rawther", "Maraikayar"
  ],
  "Maravar": [
    "Kondayan Kottai Maravar", "Sembanad Maravar", "Vallambar"
  ],
  "Maruthuvar": [
    "Maruthuvar", "Navithar"
  ],
  "Mudaliar": [
    "Agamudaiyar Mudaliar", "Arcot Mudaliar", "Isai Vellalar", "Kaikolar",
    "Saiva Vellalar", "Senai Thalaivar", "Sengunthar",
    "Thondai Mandala Saiva Vellalar", "Thuluva Vellalar"
  ],
  "Nadar": [
    "Gramani", "Kongu Nadar", "Nadar"
  ],
  "Naicker": [
    "Naicker", "Vanniya Kula Kshatriya"
  ],
  "Naidu": [
    "Balija Naidu", "Gavara Naidu", "Kamma Naidu", "Muthuraja", "Telugu Naidu"
  ],
  "Nair": [
    "Nair", "Menon", "Kurup"
  ],
  "Parvatha Rajakulam": [
    "Nattar", "Meenavar"
  ],
  "Pillai": [
    "Karkartha Saiva Pillai", "Nanjil Pillai", "Saiva Pillai",
    "Sozhia Vellalar Pillai", "Thuluva Vellalar Pillai"
  ],
  "Reddiar": [
    "Desuru Reddiar", "Ganjam Reddiar", "Kapu Reddiar", "Panta Reddiar"
  ],
  "Roman Catholic": [
    "Latin Catholic", "Syro Malabar", "Adi Dravidar Catholic"
  ],
  "Thevar": [
    "Agamudaiyar", "Kallar", "Kondayan Kottai", "Maravar", "Mukkulathor", "Vallambar"
  ],
  "Udayar": [
    "Nathaman", "Parkavakulam", "Udayar"
  ],
  "Vanniyar": [
    "Gounder", "Naicker", "Padayachi", "Vannia Kula Kshatriya"
  ],
  "Vellalar": [
    "Isai Vellalar", "Kongu Vellalar", "Sozhia Vellalar", "Thuluva Vellalar"
  ],
  "Vishwakarma": [
    "Goldsmith", "Blacksmith", "Carpenter", "Sculptor", "Kammalar"
  ],
  "Yadav": [
    "Konar", "Idaiyar", "Sambar", "Tamil Yadavar", "Telugu Yadavar"
  ],
  "Ambalakarar": [
    "Ambalakarar"
  ],
  "Aruthathiyar": [
    "Arunthathiar", "Chakkiliyan"
  ],
  "Boyer": [
    "Boyer"
  ],
  "CSI": [
    "CSI Protestant", "Church of South India"
  ],
  "Gowda": [
    "Gowda", "Vokkaliga"
  ],
  "Meenavar": [
    "Meenavar", "Pattinavar", "Paravar"
  ],
  "Moopanar": [
    "Moopanar"
  ],
  "Mutharaiyar": [
    "Mutharaiyar"
  ],
  "Muthuraja": [
    "Muthuraja", "Ambalakarar"
  ],
  "Padayatchi": [
    "Padayatchi", "Vanniyar"
  ],
  "Pandaram": [
    "Pandaram", "Aandi Pandaram"
  ],
  "Parkavakulam": [
    "Parkavakulam"
  ],
  "Pattinavar": [
    "Pattinavar", "Meenavar"
  ],
  "Ravuthar": [
    "Ravuthar", "Rowther"
  ],
  "Schedul Tribes": [
    "Irular", "Kurumbar", "Palliyan", "Toda"
  ],
  "Sheik": [
    "Sheik", "Labbai", "Maraikayar"
  ],
  "Sourashtra": [
    "Sourashtra", "Patnulkarar"
  ],
  "Vaniyar Chettiar": [
    "Vaniyar", "Vaniar Chettiar"
  ],
  "Vannar": [
    "Vannar", "Tamil Vannar", "Telugu Vannar"
  ],
  "Valluvar": [
    "Valluvar", "Valluvan"
  ],
  "PENTACOAST": [
    "Pentecostal"
  ],
  "Jangam": [
    "Jangam", "Lingayath"
  ]
};

// Populate a subcaste select dropdown based on selected caste
// casteSelectId: the caste dropdown element ID
// subcasteSelectId: the subcaste dropdown element ID
// currentVal: current subcaste value to preserve (for edit forms)
function populateSubcaste(casteSelectId, subcasteSelectId, currentVal) {
  const casteEl = document.getElementById(casteSelectId);
  const subEl = document.getElementById(subcasteSelectId);
  if (!casteEl || !subEl) return;

  const selectedCaste = casteEl.value;
  subEl.innerHTML = '<option value="">— Select —</option>';

  const subcastes = SUBCASTE_MAP[selectedCaste];
  if (subcastes && subcastes.length > 0) {
    subcastes.forEach(sc => {
      subEl.innerHTML += '<option value="' + sc + '">' + sc + '</option>';
    });
  }

  // If current value exists but not in the standard list, add it
  if (currentVal && currentVal.trim()) {
    let found = false;
    for (let i = 0; i < subEl.options.length; i++) {
      if (subEl.options[i].value === currentVal) { found = true; break; }
    }
    if (!found) {
      subEl.innerHTML += '<option value="' + currentVal + '">' + currentVal + '</option>';
    }
    subEl.value = currentVal;
  }
}

// Populate a caste dropdown with all castes from SUBCASTE_MAP + optionally from DB
// casteSelectId: the caste dropdown element ID
// currentVal: current value to preserve
// extraCastes: optional array of additional castes (from DB) to merge in
function populateCasteDropdown(casteSelectId, currentVal, extraCastes) {
  const el = document.getElementById(casteSelectId);
  if (!el) return;
  const cur = currentVal || el.value;

  // Merge SUBCASTE_MAP keys + extraCastes + "Others"
  const standard = Object.keys(SUBCASTE_MAP);
  const extra = (extraCastes || []).filter(c => c && !standard.includes(c) && c !== 'Others');
  const allCastes = [...standard, ...extra].sort();
  allCastes.push('Others');

  // Preserve onchange
  const onChange = el.getAttribute('onchange') || '';
  el.innerHTML = '<option value="">— Select —</option>';
  allCastes.forEach(c => {
    el.innerHTML += '<option value="' + c + '">' + c + '</option>';
  });

  // Restore current value (add if not in list)
  if (cur) {
    if (!allCastes.includes(cur)) {
      el.innerHTML += '<option value="' + cur + '">' + cur + '</option>';
    }
    el.value = cur;
  }
}

// Fetch castes from DB and populate all caste dropdowns on the page
// apiUrl: the API endpoint that returns { castes: [{id, caste}] }
// casteIds: array of caste select element IDs to populate
async function loadAndPopulateCastes(apiUrl, casteIds) {
  let dbCastes = [];
  try {
    const resp = await fetch(apiUrl, { credentials: 'same-origin' });
    const data = await resp.json();
    if (data.castes) dbCastes = data.castes.map(c => c.caste || c.id).filter(Boolean);
  } catch (e) { /* use standard list only */ }

  casteIds.forEach(id => {
    const el = document.getElementById(id);
    if (el) populateCasteDropdown(id, el.value, dbCastes);
  });
}
