// ===== INSTANT MOBILE DUPLICATE CHECK =====
// Shows status immediately when 10 digits entered
// Shared by user-panel.php and admin-panel.php

const MobileCheck = (() => {

  function createStatusEl(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return null;
    let el = document.getElementById(inputId + '_status');
    if (el) return el;
    el = document.createElement('div');
    el.id = inputId + '_status';
    el.style.cssText = 'font-size:11px;font-weight:600;margin-top:3px;min-height:16px';
    input.parentNode.appendChild(el);
    return el;
  }

  function showStatus(inputId, type, msg) {
    const el = createStatusEl(inputId);
    const input = document.getElementById(inputId);
    if (!el) return;
    if (type === 'ok') {
      el.textContent = msg;
      el.style.color = '#16a34a';
      if (input) { input.style.borderColor = '#16a34a'; input.style.background = '#f0fdf4'; }
    } else if (type === 'err') {
      el.textContent = msg;
      el.style.color = '#dc2626';
      if (input) { input.style.borderColor = '#dc2626'; input.style.background = '#fef2f2'; }
    } else if (type === 'loading') {
      el.textContent = msg;
      el.style.color = '#a16207';
      if (input) { input.style.borderColor = '#f59e0b'; input.style.background = '#fffbeb'; }
    } else {
      el.textContent = '';
      if (input) { input.style.borderColor = ''; input.style.background = ''; }
    }
  }

  // Check against local profiles array (admin panel has all profiles loaded)
  function checkLocal(mobile, profilesArr) {
    if (!profilesArr || !Array.isArray(profilesArr)) return false;
    return profilesArr.some(p => p.mobile === mobile);
  }

  // Check against server API
  async function checkServer(mobile) {
    try {
      const resp = await fetch('api/public.php?checkMobile=' + mobile, { credentials: 'same-origin' });
      const data = await resp.json();
      return data.exists === true;
    } catch (e) {
      return null; // unknown
    }
  }

  // Attach instant check to a mobile input
  // inputId: the mobile input element ID
  // getProfiles: function that returns the local profiles array (or null to use server check)
  function attach(inputId, getProfiles) {
    const input = document.getElementById(inputId);
    if (!input) return;

    let timer = null;
    input.addEventListener('input', () => {
      clearTimeout(timer);
      const val = input.value.replace(/\D/g, '');
      input.value = val; // strip non-digits

      if (val.length < 10) {
        showStatus(inputId, 'clear', '');
        return;
      }
      if (val.length > 10) {
        input.value = val.slice(0, 10);
      }

      const mobile = input.value;

      // Try local check first (instant)
      const localProfiles = getProfiles ? getProfiles() : null;
      if (localProfiles) {
        if (checkLocal(mobile, localProfiles)) {
          showStatus(inputId, 'err', 'This number already has a profile');
        } else {
          showStatus(inputId, 'ok', 'Number available');
        }
        return;
      }

      // Server check (with debounce)
      showStatus(inputId, 'loading', 'Checking...');
      timer = setTimeout(async () => {
        const exists = await checkServer(mobile);
        if (exists === true) {
          showStatus(inputId, 'err', 'This number already has a profile');
        } else if (exists === false) {
          showStatus(inputId, 'ok', 'Number available');
        } else {
          showStatus(inputId, 'clear', '');
        }
      }, 300);
    });
  }

  return { attach };
})();
