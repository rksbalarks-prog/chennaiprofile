// ===== ADDRESS EXTRACTION: Area, City, District, State from Present Address =====
// Indian states & districts for dropdown + auto-extract from address text
// Shared by user-panel.php and admin-panel.php

const INDIAN_STATES_LIST = [
  "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh",
  "Goa", "Gujarat", "Haryana", "Himachal Pradesh", "Jharkhand",
  "Karnataka", "Kerala", "Madhya Pradesh", "Maharashtra", "Manipur",
  "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Pondicherry",
  "Punjab", "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana",
  "Tripura", "Uttar Pradesh", "Uttarakhand", "West Bengal"
];

const TN_DISTRICTS_LIST = [
  "Ariyalur", "Chengalpattu", "Chennai", "Coimbatore", "Cuddalore",
  "Dharmapuri", "Dindigul", "Erode", "Kallakurichi", "Kancheepuram",
  "Kanyakumari", "Karur", "Krishnagiri", "Madurai", "Mayiladuthurai",
  "Nagapattinam", "Namakkal", "Nilgiris", "Perambalur", "Pudukkottai",
  "Ramanathapuram", "Ranipet", "Salem", "Sivagangai", "Tenkasi",
  "Thanjavur", "Theni", "Thoothukudi", "Tiruchirappalli", "Tirunelveli",
  "Tirupattur", "Tiruvallur", "Tiruvannamalai", "Tiruvarur",
  "Vellore", "Viluppuram", "Virudhunagar"
];

const STATE_DISTRICTS = {
  "Pondicherry": ["Pondicherry","Karaikal","Mahe","Yanam"],
  "Tamil Nadu": TN_DISTRICTS_LIST,
  "Kerala": ["Thiruvananthapuram","Kollam","Pathanamthitta","Alappuzha","Kottayam","Idukki","Ernakulam","Thrissur","Palakkad","Malappuram","Kozhikode","Wayanad","Kannur","Kasaragod"],
  "Karnataka": ["Bangalore Urban","Bangalore Rural","Belgaum","Bellary","Bidar","Bijapur","Chamarajanagar","Chikkaballapur","Chikkamagaluru","Chitradurga","Dakshina Kannada","Davangere","Dharwad","Gadag","Hassan","Haveri","Kolar","Koppal","Mandya","Mysore","Raichur","Ramanagara","Shimoga","Tumkur","Udupi","Uttara Kannada","Yadgir"],
  "Andhra Pradesh": ["Anantapur","Chittoor","East Godavari","Guntur","Krishna","Kurnool","Nellore","Prakasam","Srikakulam","Visakhapatnam","Vizianagaram","West Godavari","YSR Kadapa"],
  "Telangana": ["Adilabad","Hyderabad","Karimnagar","Khammam","Mahbubnagar","Medak","Nalgonda","Nizamabad","Rangareddy","Warangal"],
  "Maharashtra": ["Mumbai","Pune","Nagpur","Thane","Nashik","Aurangabad","Solapur","Kolhapur","Sangli","Satara","Ratnagiri"],
  "Gujarat": ["Ahmedabad","Surat","Vadodara","Rajkot","Bhavnagar","Jamnagar","Junagadh","Gandhinagar"],
  "Rajasthan": ["Jaipur","Jodhpur","Udaipur","Kota","Ajmer","Bikaner","Alwar","Bharatpur"],
  "Uttar Pradesh": ["Lucknow","Kanpur","Agra","Varanasi","Allahabad","Meerut","Noida","Ghaziabad"],
  "Madhya Pradesh": ["Bhopal","Indore","Jabalpur","Gwalior","Ujjain","Sagar","Rewa"],
  "West Bengal": ["Kolkata","Howrah","North 24 Parganas","South 24 Parganas","Hooghly","Nadia","Bardhaman"],
  "Bihar": ["Patna","Gaya","Bhagalpur","Muzaffarpur","Darbhanga","Purnia"],
  "Odisha": ["Bhubaneswar","Cuttack","Puri","Berhampur","Sambalpur","Rourkela"],
  "Punjab": ["Ludhiana","Amritsar","Jalandhar","Patiala","Bathinda","Mohali"],
  "Haryana": ["Gurugram","Faridabad","Panipat","Ambala","Karnal","Hisar"],
  "Delhi": ["New Delhi","North Delhi","South Delhi","East Delhi","West Delhi"],
};

// Populate state dropdown
function populateStateDropdown(selectId, currentVal) {
  const el = document.getElementById(selectId);
  if (!el) return;
  el.innerHTML = '<option value="">— Select —</option>';
  INDIAN_STATES_LIST.forEach(s => {
    el.innerHTML += '<option value="' + s + '"' + (s === 'Tamil Nadu' ? ' style="font-weight:700"' : '') + '>' + s + '</option>';
  });
  if (currentVal) el.value = currentVal;
}

// Populate district dropdown based on selected state
function populateDistrictDropdown(selectId, currentVal, stateSelectId) {
  const el = document.getElementById(selectId);
  if (!el) return;
  el.innerHTML = '<option value="">— Select —</option>';

  // Get selected state
  let selectedState = '';
  if (stateSelectId) {
    const stateEl = document.getElementById(stateSelectId);
    if (stateEl) selectedState = stateEl.value;
  }

  // Get districts for the selected state, fallback to TN
  let districts = [];
  if (selectedState && STATE_DISTRICTS[selectedState]) {
    districts = STATE_DISTRICTS[selectedState];
  } else {
    // Show all districts from all states
    const allDistricts = new Set();
    Object.values(STATE_DISTRICTS).forEach(arr => arr.forEach(d => allDistricts.add(d)));
    districts = [...allDistricts].sort();
  }

  districts.forEach(d => {
    el.innerHTML += '<option value="' + d + '">' + d + '</option>';
  });

  if (currentVal) {
    if (!districts.includes(currentVal)) {
      el.innerHTML += '<option value="' + currentVal + '">' + currentVal + '</option>';
    }
    el.value = currentVal;
  }
}

// Auto-update district when state changes
function bindStateToDistrict(stateId, districtId) {
  const stateEl = document.getElementById(stateId);
  if (stateEl) {
    stateEl.addEventListener('change', () => {
      populateDistrictDropdown(districtId, '', stateId);
    });
  }
}

// Try to extract area, city, district, state from address text
function extractAddressParts(addressText) {
  if (!addressText) return {};
  const text = addressText.trim();
  const parts = text.split(',').map(p => p.trim()).filter(Boolean);
  const result = {};

  // Try to find state (last part usually)
  for (let i = parts.length - 1; i >= 0; i--) {
    const p = parts[i].replace(/[.\-]/g, '').trim();
    const found = INDIAN_STATES_LIST.find(s => p.toLowerCase() === s.toLowerCase() || p.toLowerCase().includes(s.toLowerCase()));
    if (found) { result.state = found; parts.splice(i, 1); break; }
  }

  // Try to find district
  for (let i = parts.length - 1; i >= 0; i--) {
    const p = parts[i].replace(/[.\-]/g, '').trim();
    const found = TN_DISTRICTS_LIST.find(d => p.toLowerCase() === d.toLowerCase() || p.toLowerCase().includes(d.toLowerCase()));
    if (found) { result.district = found; parts.splice(i, 1); break; }
  }

  // Remaining parts: first = area, second = city (or last = city)
  if (parts.length >= 2) {
    result.area = parts[0];
    result.city = parts[parts.length - 1];
  } else if (parts.length === 1) {
    result.city = parts[0];
  }

  // Default state to Tamil Nadu if district found in TN list
  if (result.district && !result.state && TN_DISTRICTS_LIST.includes(result.district)) {
    result.state = 'Tamil Nadu';
  }

  return result;
}

// Setup address location fields for a form
// prefix: field prefix (e.g. 'ep', 'cp', 'a', 'e')
// addrId: the present address textarea ID
function setupAddressExtract(prefix, addrId) {
  const addr = document.getElementById(addrId);
  if (!addr) return;

  const areaId = prefix + '_present_area';
  const cityId = prefix + '_present_city';
  const distId = prefix + '_present_district';
  const stateId = prefix + '_present_state';

  // Populate dropdowns
  populateStateDropdown(stateId, 'Tamil Nadu');
  populateDistrictDropdown(distId, '', stateId);
  bindStateToDistrict(stateId, distId);

  // Attach city autocomplete if PlaceSuggest is available
  if (typeof PlaceSuggest !== 'undefined') {
    PlaceSuggest.attach(areaId);
    PlaceSuggest.attach(cityId);
  }

  // Auto-extract on address blur (when user finishes typing address)
  addr.addEventListener('blur', () => {
    const parts = extractAddressParts(addr.value);
    const areaEl = document.getElementById(areaId);
    const cityEl = document.getElementById(cityId);
    const distEl = document.getElementById(distId);
    const stateEl = document.getElementById(stateId);
    // Only fill empty fields (don't overwrite manual entries)
    if (parts.area && areaEl && !areaEl.value) areaEl.value = parts.area;
    if (parts.city && cityEl && !cityEl.value) cityEl.value = parts.city;
    if (parts.district && distEl && !distEl.value) { populateDistrictDropdown(distId, parts.district); }
    if (parts.state && stateEl && !stateEl.value) stateEl.value = parts.state;
  });
}

// Populate location fields from saved profile data
function setAddressLocation(prefix, area, city, district, state) {
  const areaEl = document.getElementById(prefix + '_present_area');
  const cityEl = document.getElementById(prefix + '_present_city');
  const distId = prefix + '_present_district';
  const stateId = prefix + '_present_state';
  if (areaEl) areaEl.value = area || '';
  if (cityEl) cityEl.value = city || '';
  populateStateDropdown(stateId, state || 'Tamil Nadu');
  populateDistrictDropdown(distId, district || '', stateId);
  bindStateToDistrict(stateId, distId);
}
