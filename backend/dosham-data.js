// ===== DOSHAM TYPE: Show/hide dosham type dropdown based on dosham selection =====
// Based on Vedic astrology dosham classifications
// Shared by user-panel.php and admin-panel.php

const DOSHAM_TYPES = [
  "Chevvai Dosham (Manglik / Kuja Dosham)",
  "Rahu Dosham",
  "Ketu Dosham",
  "Naga Dosham (Sarpa Dosham)",
  "Kalasarpa Dosham",
  "Sani Dosham (Shani Dosham)",
  "Pitru Dosham",
  "Guru Dosham",
  "Surya Dosham",
  "Chandra Dosham",
  "Kalathra Dosham",
  "Putra Dosham",
  "Sevvai & Sani Dosham",
  "Rahu-Ketu Dosham",
  "Naga Dosham & Kalasarpa Dosham",
  "Multiple Dosham",
  "Others"
];

// Populate dosham type dropdown
function populateDoshamType(selectId) {
  const el = document.getElementById(selectId);
  if (!el) return;
  const cur = el.value;
  el.innerHTML = '<option value="">— Select Dosham Type —</option>';
  DOSHAM_TYPES.forEach(d => {
    el.innerHTML += '<option value="' + d + '">' + d + '</option>';
  });
  if (cur) {
    if (!DOSHAM_TYPES.includes(cur)) {
      el.innerHTML += '<option value="' + cur + '">' + cur + '</option>';
    }
    el.value = cur;
  }
}

// Toggle dosham type visibility based on dosham value
// doshamVal: 'No', 'Yes', or 'Partial'
// typeWrapperId: the wrapper div ID for the dosham type dropdown
function toggleDoshamType(doshamVal, typeWrapperId) {
  const wrapper = document.getElementById(typeWrapperId);
  if (!wrapper) return;
  if (doshamVal === 'Yes' || doshamVal === 'Partial') {
    wrapper.style.display = '';
  } else {
    wrapper.style.display = 'none';
    // Clear the selection
    const sel = wrapper.querySelector('select');
    if (sel) sel.value = '';
  }
}

// Attach dosham change listener
// For <select> elements (user panel)
function attachDoshamSelect(doshamSelectId, typeWrapperId) {
  const sel = document.getElementById(doshamSelectId);
  if (!sel) return;
  sel.addEventListener('change', () => toggleDoshamType(sel.value, typeWrapperId));
  // Initial state
  toggleDoshamType(sel.value, typeWrapperId);
}

// For radio button groups (admin panel)
function attachDoshamRadio(radioName, typeWrapperId) {
  const radios = document.querySelectorAll('input[name="' + radioName + '"]');
  radios.forEach(r => {
    r.addEventListener('change', () => toggleDoshamType(r.value, typeWrapperId));
  });
  // Initial state
  const checked = document.querySelector('input[name="' + radioName + '"]:checked');
  if (checked) toggleDoshamType(checked.value, typeWrapperId);
}
