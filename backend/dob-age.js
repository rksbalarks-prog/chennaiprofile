// ===== DOB (dd/mm/yyyy) + Auto Age Calculator + Legal Age Restriction =====
// Shared by user-panel.php and admin-panel.php
// Male min age: 21, Female min age: 18

const DobAge = (() => {
  const _fields = {}; // { dobId: { ageDisplayId, ageHiddenId, genderId } }

  // Calculate age from dd/mm/yyyy string
  function calcAge(ddmmyyyy) {
    const parts = ddmmyyyy.split('/');
    if (parts.length !== 3) return null;
    const day = parseInt(parts[0], 10);
    const month = parseInt(parts[1], 10) - 1;
    const year = parseInt(parts[2], 10);
    if (isNaN(day) || isNaN(month) || isNaN(year)) return null;
    if (year < 1900 || year > new Date().getFullYear()) return null;
    const dob = new Date(year, month, day);
    if (dob.getDate() !== day || dob.getMonth() !== month) return null;
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const mDiff = today.getMonth() - dob.getMonth();
    if (mDiff < 0 || (mDiff === 0 && today.getDate() < dob.getDate())) age--;
    return age >= 0 && age <= 120 ? age : null;
  }

  // Get the latest allowed birth date for a given gender (today minus minAge years)
  function getMaxBirthDate(gender) {
    const g = (gender || '').toLowerCase();
    const minAge = g === 'female' ? 18 : 21;
    const today = new Date();
    return new Date(today.getFullYear() - minAge, today.getMonth(), today.getDate());
  }

  // Format date as dd/mm/yyyy
  function formatDmy(d) {
    const dd = String(d.getDate()).padStart(2, '0');
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    return dd + '/' + mm + '/' + d.getFullYear();
  }

  function isoToDmy(iso) {
    if (!iso) return '';
    const parts = iso.split('-');
    if (parts.length !== 3) return iso;
    return parts[2] + '/' + parts[1] + '/' + parts[0];
  }

  function dmyToIso(dmy) {
    if (!dmy) return '';
    const parts = dmy.split('/');
    if (parts.length !== 3) return dmy;
    return parts[2] + '-' + parts[1] + '-' + parts[0];
  }

  // Get gender value from the linked gender field
  function getGender(dobId) {
    const cfg = _fields[dobId];
    if (!cfg || !cfg.genderId) return '';
    const el = document.getElementById(cfg.genderId);
    return el ? el.value : '';
  }

  // Update the age display + validate legal age
  function updateAgeDisplay(dobId, dateStr) {
    const cfg = _fields[dobId];
    if (!cfg) return;
    const ageEl = cfg.ageDisplayId ? document.getElementById(cfg.ageDisplayId) : null;
    const hidEl = cfg.ageHiddenId ? document.getElementById(cfg.ageHiddenId) : null;
    const ageInp = cfg.ageInputId ? document.getElementById(cfg.ageInputId) : null;
    const hintEl = document.getElementById(dobId + '_hint');

    if (!dateStr || dateStr.length < 10) {
      if (ageEl) ageEl.textContent = '';
      if (hidEl) hidEl.value = '';
      if (ageInp) ageInp.value = '';
      return;
    }

    const age = calcAge(dateStr);
    if (age === null) {
      if (ageEl) { ageEl.textContent = 'Invalid date'; ageEl.style.color = '#dc2626'; }
      if (hidEl) hidEl.value = '';
      if (ageInp) ageInp.value = '';
      return;
    }

    const gender = getGender(dobId);
    const g = gender.toLowerCase();
    const minAge = g === 'female' ? 18 : 21;
    const label = g === 'female' ? 'women' : 'men';

    if (age < minAge) {
      // Underage - show error, clear the field
      if (ageEl) {
        ageEl.textContent = age + ' yrs - Not eligible';
        ageEl.style.color = '#dc2626';
      }
      if (hidEl) hidEl.value = '';

      // Show popup/toast warning and clear after delay
      const input = document.getElementById(dobId);
      if (input) {
        input.style.borderColor = '#dc2626';
        input.style.background = '#fef2f2';
      }

      // Show restriction message below input
      if (hintEl) {
        const maxDate = getMaxBirthDate(gender);
        hintEl.textContent = 'Minimum age for ' + label + ' is ' + minAge + ' yrs. Must be born on or before ' + formatDmy(maxDate) + '.';
        hintEl.style.color = '#dc2626';
        hintEl.style.display = '';
      }

      if (ageInp) { ageInp.value = age; ageInp.style.color = '#dc2626'; }

      // Clear the invalid date after a brief moment so user sees the error
      setTimeout(() => {
        if (input) {
          input.value = '';
          input.style.borderColor = '';
          input.style.background = '';
        }
        if (ageEl) ageEl.textContent = '';
        if (hidEl) hidEl.value = '';
        if (ageInp) { ageInp.value = ''; ageInp.style.color = ''; }
      }, 2500);
      return;
    }

    // Valid age
    if (ageEl) {
      ageEl.textContent = age + ' yrs';
      ageEl.style.color = '#16a34a';
    }
    if (hidEl) hidEl.value = age;
    if (ageInp) { ageInp.value = age; ageInp.style.color = '#16a34a'; }
    if (hintEl) hintEl.style.display = 'none';

    const input = document.getElementById(dobId);
    if (input) { input.style.borderColor = ''; input.style.background = ''; }
  }

  // Input mask: auto-insert slashes as user types
  function onDobInput(e) {
    const input = e.target;
    let v = input.value.replace(/[^0-9/]/g, '');

    const digits = v.replace(/\//g, '');
    if (digits.length <= 2) {
      v = digits;
    } else if (digits.length <= 4) {
      v = digits.slice(0, 2) + '/' + digits.slice(2);
    } else {
      v = digits.slice(0, 2) + '/' + digits.slice(2, 4) + '/' + digits.slice(4, 8);
    }

    input.value = v;
    updateAgeDisplay(input.id, v);
  }

  // Paste handler
  function onDobPaste(e) {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData).getData('text').trim();
    let converted = pasted;
    if (/^\d{4}-\d{2}-\d{2}$/.test(pasted)) converted = isoToDmy(pasted);
    if (/^\d{2}-\d{2}-\d{4}$/.test(pasted)) converted = pasted.replace(/-/g, '/');
    document.execCommand('insertText', false, converted);
  }

  // When gender changes, re-validate DOB and update the hint
  function onGenderChange(dobId) {
    const input = document.getElementById(dobId);
    if (!input) return;
    const hintEl = document.getElementById(dobId + '_hint');
    const gender = getGender(dobId);

    // Update max date hint
    if (hintEl && gender) {
      const maxDate = getMaxBirthDate(gender);
      const g = gender.toLowerCase();
      const minAge = g === 'female' ? 18 : 21;
      hintEl.textContent = 'Must be born on or before ' + formatDmy(maxDate) + ' (' + minAge + '+ yrs)';
      hintEl.style.color = 'var(--ink4, #888)';
      hintEl.style.display = '';
    }

    // Re-validate current DOB if entered
    if (input.value && input.value.length === 10) {
      updateAgeDisplay(dobId, input.value);
    }
  }

  // Create hint element below DOB input
  function createHintEl(dobId) {
    const input = document.getElementById(dobId);
    if (!input) return;
    const existing = document.getElementById(dobId + '_hint');
    if (existing) return;

    const hint = document.createElement('div');
    hint.id = dobId + '_hint';
    hint.style.cssText = 'font-size:10.5px;margin-top:2px;display:none';
    input.parentNode.appendChild(hint);
  }

  // Initialize a DOB field
  // dobId: input element ID
  // ageDisplayId: span to show age text
  // ageHiddenId: hidden input for age value
  // genderId: gender select element ID (for age restriction)
  // ageInputId: visible readonly input to show age (optional)
  function init(dobId, ageDisplayId, ageHiddenId, genderId, ageInputId) {
    const input = document.getElementById(dobId);
    if (!input) return;

    _fields[dobId] = {
      ageDisplayId: ageDisplayId || '',
      ageHiddenId: ageHiddenId || '',
      genderId: genderId || '',
      ageInputId: ageInputId || ''
    };

    // Convert from date to text
    const currentVal = input.value;
    input.type = 'text';
    input.placeholder = 'dd/mm/yyyy';
    input.maxLength = 10;
    input.setAttribute('inputmode', 'numeric');

    if (currentVal && /^\d{4}-\d{2}-\d{2}$/.test(currentVal)) {
      input.value = isoToDmy(currentVal);
    }

    // Create hint element
    createHintEl(dobId);

    // Calculate initial age
    if (input.value.length === 10) {
      updateAgeDisplay(dobId, input.value);
    }

    // Attach DOB handlers
    input.addEventListener('input', onDobInput);
    input.addEventListener('paste', onDobPaste);

    // Attach gender change handler
    if (genderId) {
      const genderEl = document.getElementById(genderId);
      if (genderEl) {
        genderEl.addEventListener('change', () => onGenderChange(dobId));
        // Show initial hint if gender is already selected
        if (genderEl.value) onGenderChange(dobId);
      }
    }
  }

  function getIso(dobId) {
    const input = document.getElementById(dobId);
    if (!input) return '';
    return dmyToIso(input.value);
  }

  function setFromIso(dobId, isoVal) {
    const input = document.getElementById(dobId);
    if (!input) return;
    input.value = isoToDmy(isoVal || '');
    input.dispatchEvent(new Event('input'));
  }

  // Validate minimum marriage age (for submit-time check)
  function validateAge(dobId, gender) {
    const input = document.getElementById(dobId);
    if (!input || !input.value || input.value.length < 10) return null;
    const age = calcAge(input.value);
    if (age === null) return 'Invalid date of birth.';
    const g = (gender || '').toLowerCase();
    const minAge = g === 'female' ? 18 : 21;
    if (age < minAge) {
      const label = g === 'female' ? 'women' : 'men';
      return 'Minimum age for ' + label + ' is ' + minAge + ' years. Current age: ' + age + '. Please come back when you reach the legal marriage age.';
    }
    return null;
  }

  return { init, getIso, setFromIso, calcAge, isoToDmy, dmyToIso, validateAge };
})();
