// ===== PLACE AUTOCOMPLETE: Indian Cities, Districts, Towns =====
// Shared by user-panel.php and admin-panel.php
// Focus on Tamil Nadu + major Indian cities

const PlaceSuggest = (() => {

  // Tamil Nadu Districts (38 districts as per 2024 govt records)
  const TN_DISTRICTS = [
    "Ariyalur", "Chengalpattu", "Chennai", "Coimbatore", "Cuddalore",
    "Dharmapuri", "Dindigul", "Erode", "Kallakurichi", "Kancheepuram",
    "Kanyakumari", "Karur", "Krishnagiri", "Madurai", "Mayiladuthurai",
    "Nagapattinam", "Namakkal", "Nilgiris", "Perambalur", "Pudukkottai",
    "Ramanathapuram", "Ranipet", "Salem", "Sivagangai", "Tenkasi",
    "Thanjavur", "Theni", "Thoothukudi", "Tiruchirappalli", "Tirunelveli",
    "Tirupattur", "Tiruvallur", "Tiruvannamalai", "Tiruvarur",
    "Vellore", "Viluppuram", "Virudhunagar"
  ];

  // Tamil Nadu Major Cities & Towns
  const TN_CITIES = [
    "Alandur", "Ambattur", "Ambur", "Arakkonam", "Aruppukkottai",
    "Attur", "Avadi", "Bhavani", "Bodinayakanur", "Chidambaram",
    "Coonoor", "Devakottai", "Dharapuram", "Gobichettipalayam",
    "Gudiyatham", "Hosur", "Jayamkondacholapuram", "Kadalur",
    "Kallupatti", "Kanchipuram", "Kangeyam", "Karaikudi", "Karungal",
    "Karur", "Komarapalayam", "Kovilpatti", "Kumbakonam",
    "Kuzhithurai", "Lalgudi", "Mamallapuram", "Manachanallur",
    "Manapparai", "Mannargudi", "Marthandam", "Melur", "Mettupalayam",
    "Mettur", "Musiri", "Nagercoil", "Nandivaram", "Natham",
    "Neyveli", "Omalur", "Ooty", "Palani", "Palladam",
    "Pallavaram", "Panruti", "Paramakudi", "Pattukkottai",
    "Perambalur", "Perundurai", "Pollachi", "Pondicherry",
    "Poonamallee", "Pudukkottai", "Pullambadi",
    "Rajapalayam", "Ramanathapuram", "Rasipuram",
    "Sankarankovil", "Sankari", "Sathyamangalam", "Sendurai",
    "Sirkali", "Sivakasi", "Srirangam", "Srivilliputhur",
    "Tambaram", "Tanjore", "Thambaram", "Theni", "Thirumangalam",
    "Thiruvarur", "Thoothukudi", "Tindivanam", "Tiruchendur",
    "Tiruchengode", "Tirunelveli", "Tiruppur", "Tiruttani",
    "Tiruvannamalai", "Trichy", "Udumalpet", "Ulundurpet",
    "Usilampatti", "Vandalur", "Vandavasi", "Vaniyambadi",
    "Vedaranyam", "Velachery", "Vellore", "Villupuram",
    "Virudhachalam", "Virudhunagar", "Walajapet"
  ];

  // Union Territories
  const UT = ["Pondicherry", "Karaikal", "Mahe", "Yanam"];

  // Other Indian State Capitals & Major Cities
  const INDIAN_CITIES = [
    "Agra", "Ahmedabad", "Allahabad", "Amritsar", "Aurangabad",
    "Bangalore", "Bhopal", "Bhubaneswar", "Chandigarh", "Dehradun",
    "Delhi", "Faridabad", "Gangtok", "Ghaziabad", "Goa",
    "Gurgaon", "Guwahati", "Gwalior", "Hubli", "Hyderabad",
    "Imphal", "Indore", "Itanagar", "Jaipur", "Jalandhar",
    "Jammu", "Jamshedpur", "Jodhpur", "Kanpur", "Kochi",
    "Kolhapur", "Kolkata", "Kozhikode", "Lucknow", "Ludhiana",
    "Mangalore", "Mumbai", "Mysore", "Nagpur", "Nashik",
    "Navi Mumbai", "Noida", "Panaji", "Patna", "Pune",
    "Raipur", "Rajkot", "Ranchi", "Shillong", "Shimla",
    "Siliguri", "Srinagar", "Surat", "Thane", "Thiruvananthapuram",
    "Thrissur", "Udaipur", "Vadodara", "Varanasi", "Vijayawada",
    "Visakhapatnam", "Warangal"
  ];

  // Other Indian States (for nativity)
  const INDIAN_STATES = [
    "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar",
    "Chhattisgarh", "Goa", "Gujarat", "Haryana", "Himachal Pradesh",
    "Jharkhand", "Karnataka", "Kerala", "Madhya Pradesh",
    "Maharashtra", "Manipur", "Meghalaya", "Mizoram", "Nagaland",
    "Odisha", "Punjab", "Rajasthan", "Sikkim", "Tamil Nadu",
    "Telangana", "Tripura", "Uttar Pradesh", "Uttarakhand", "West Bengal"
  ];

  // Combined unique sorted list
  const ALL_PLACES = [...new Set([
    ...TN_DISTRICTS, ...TN_CITIES, ...UT, ...INDIAN_CITIES, ...INDIAN_STATES
  ])].sort();

  // ── Autocomplete UI ──

  let _activeInput = null;
  let _dropdown = null;

  function createDropdown() {
    if (_dropdown) return _dropdown;
    const d = document.createElement('div');
    d.id = '_placeSuggestDD';
    d.style.cssText = 'position:absolute;z-index:9999;background:#fff;border:1.5px solid #d1d5db;border-radius:8px;max-height:200px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.12);display:none;width:100%;font-size:13px';
    document.body.appendChild(d);
    _dropdown = d;

    // Close on outside click
    document.addEventListener('click', (e) => {
      if (_dropdown && !_dropdown.contains(e.target) && e.target !== _activeInput) {
        _dropdown.style.display = 'none';
      }
    });

    return d;
  }

  function positionDropdown(input) {
    const rect = input.getBoundingClientRect();
    const dd = createDropdown();
    dd.style.top = (rect.bottom + window.scrollY + 2) + 'px';
    dd.style.left = (rect.left + window.scrollX) + 'px';
    dd.style.width = rect.width + 'px';
  }

  function showSuggestions(input, query) {
    const dd = createDropdown();
    if (!query || query.length < 1) { dd.style.display = 'none'; return; }

    const q = query.toLowerCase();
    // Prioritize starts-with matches, then contains matches
    const startsWith = ALL_PLACES.filter(p => p.toLowerCase().startsWith(q));
    const contains = ALL_PLACES.filter(p => !p.toLowerCase().startsWith(q) && p.toLowerCase().includes(q));
    const matches = [...startsWith, ...contains].slice(0, 12);

    if (matches.length === 0) { dd.style.display = 'none'; return; }

    positionDropdown(input);
    dd.innerHTML = matches.map((m, i) => {
      // Highlight matching part
      const idx = m.toLowerCase().indexOf(q);
      const before = m.slice(0, idx);
      const match = m.slice(idx, idx + q.length);
      const after = m.slice(idx + q.length);
      return '<div class="ps-item" data-idx="' + i + '" style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #f3f4f6;transition:background .1s"'
        + ' onmouseenter="this.style.background=\'#f0f4ff\'" onmouseleave="this.style.background=\'#fff\'">'
        + before + '<strong style="color:#1d4ed8">' + match + '</strong>' + after + '</div>';
    }).join('');
    dd.style.display = 'block';

    // Click handler for items
    dd.querySelectorAll('.ps-item').forEach((item, i) => {
      item.onclick = () => {
        input.value = matches[i];
        dd.style.display = 'none';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      };
    });
  }

  // Keyboard navigation
  function onKeyDown(e) {
    const dd = _dropdown;
    if (!dd || dd.style.display === 'none') return;
    const items = dd.querySelectorAll('.ps-item');
    if (items.length === 0) return;

    let active = dd.querySelector('.ps-item[data-active]');
    let idx = active ? parseInt(active.dataset.idx) : -1;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      idx = Math.min(idx + 1, items.length - 1);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      idx = Math.max(idx - 1, 0);
    } else if (e.key === 'Enter' && active) {
      e.preventDefault();
      active.click();
      return;
    } else if (e.key === 'Escape') {
      dd.style.display = 'none';
      return;
    } else {
      return;
    }

    items.forEach(it => { it.removeAttribute('data-active'); it.style.background = '#fff'; });
    if (items[idx]) {
      items[idx].setAttribute('data-active', '1');
      items[idx].style.background = '#f0f4ff';
      items[idx].scrollIntoView({ block: 'nearest' });
    }
  }

  // ── Attach autocomplete to an input field ──
  function attach(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.setAttribute('autocomplete', 'off');

    input.addEventListener('input', () => {
      _activeInput = input;
      showSuggestions(input, input.value.trim());
    });

    input.addEventListener('focus', () => {
      _activeInput = input;
      if (input.value.trim().length >= 1) {
        showSuggestions(input, input.value.trim());
      }
    });

    input.addEventListener('keydown', onKeyDown);
  }

  return { attach };
})();
