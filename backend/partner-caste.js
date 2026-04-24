// ===== PARTNER CASTE & SUB CASTE PREFERENCE: Multi-select checkbox widgets =====
// Shared by user-panel.php and admin-panel.php

const PartnerCaste = (() => {

  const SPECIAL = ['Same Caste', 'Any Caste', 'Not Interested'];
  const _links = {}; // { casteContainerId: { subContainerId, subHiddenId, ownCasteId } }

  function getCasteList() {
    return typeof SUBCASTE_MAP !== 'undefined' ? Object.keys(SUBCASTE_MAP).sort() : [];
  }

  function getSubcastes(casteName) {
    return (typeof SUBCASTE_MAP !== 'undefined' && SUBCASTE_MAP[casteName]) ? SUBCASTE_MAP[casteName] : [];
  }

  // Get the profile's own caste from a caste select element
  function getOwnCaste(ownCasteId) {
    const el = document.getElementById(ownCasteId);
    return el ? el.value : '';
  }

  // Get all checked castes from a caste widget
  function getCheckedCastes(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return { special: [], castes: [] };
    const special = [];
    const castes = [];
    container.querySelectorAll('.pc-chk:checked').forEach(c => {
      if (c.dataset.special === '1') special.push(c.dataset.val);
      else castes.push(c.dataset.val);
    });
    return { special, castes };
  }

  // ── Build caste checkbox widget ──
  function build(containerId, hiddenId) {
    const container = document.getElementById(containerId);
    const hidden = document.getElementById(hiddenId);
    if (!container) return;

    const castes = getCasteList();
    let html = '<div style="display:flex;flex-wrap:wrap;gap:6px 12px">';
    SPECIAL.forEach((s, i) => {
      const color = i === 0 ? '#16a34a' : i === 1 ? '#2563eb' : '#dc2626';
      html += '<label style="display:flex;align-items:center;gap:4px;font-size:12.5px;font-weight:600;color:' + color + ';cursor:pointer;padding:3px 0">'
        + '<input type="checkbox" class="pc-chk" data-special="1" data-val="' + s + '" style="accent-color:' + color + '"> ' + s + '</label>';
    });
    html += '</div><div class="pc-castes" style="display:flex;flex-wrap:wrap;gap:4px 10px;margin-top:6px;padding-top:6px;border-top:1px solid #e5e7eb">';
    castes.forEach(c => {
      html += '<label style="display:flex;align-items:center;gap:3px;font-size:11.5px;color:#374151;cursor:pointer;padding:2px 0">'
        + '<input type="checkbox" class="pc-chk" data-val="' + c + '" style="accent-color:#7c3aed"> ' + c + '</label>';
    });
    html += '</div>';
    container.innerHTML = html;

    container.querySelectorAll('.pc-chk').forEach(chk => {
      chk.addEventListener('change', () => {
        const val = chk.dataset.val;
        const isSpecial = chk.dataset.special === '1';
        if (isSpecial && chk.checked) {
          if (val === 'Any Caste' || val === 'Not Interested') {
            container.querySelectorAll('.pc-chk').forEach(c => { if (c !== chk) c.checked = false; });
          }
          if (val === 'Same Caste') {
            container.querySelectorAll('.pc-chk[data-special="1"]').forEach(c => { if (c !== chk) c.checked = false; });
          }
        }
        if (!isSpecial && chk.checked) {
          container.querySelectorAll('.pc-chk[data-special="1"]').forEach(c => {
            if (c.dataset.val === 'Any Caste' || c.dataset.val === 'Not Interested') c.checked = false;
          });
        }
        syncHidden(container, hidden);
        // Update linked sub caste widget
        if (_links[containerId]) updateSubCasteWidget(containerId);
      });
    });
  }

  function syncHidden(container, hidden) {
    if (!hidden) return;
    const vals = [];
    container.querySelectorAll('.pc-chk:checked').forEach(c => vals.push(c.dataset.val));
    hidden.value = vals.join(', ');
  }

  function setValue(containerId, hiddenId, value) {
    const container = document.getElementById(containerId);
    const hidden = document.getElementById(hiddenId);
    if (!container) return;
    const selected = (value || '').split(',').map(s => s.trim()).filter(Boolean);
    container.querySelectorAll('.pc-chk').forEach(c => c.checked = false);
    selected.forEach(s => {
      const chk = container.querySelector('.pc-chk[data-val="' + s + '"]');
      if (chk) chk.checked = true;
    });
    if (selected.includes('Any') || selected.includes('any')) {
      const c = container.querySelector('.pc-chk[data-val="Any Caste"]'); if (c) c.checked = true;
    }
    if (selected.includes('Same') || selected.includes('Same Caste')) {
      const c = container.querySelector('.pc-chk[data-val="Same Caste"]'); if (c) c.checked = true;
    }
    syncHidden(container, hidden);
  }

  // ── Sub Caste Widget ──

  // Link a caste widget to a sub caste widget
  // casteContainerId: the caste checkbox widget container
  // subContainerId: the sub caste checkbox widget container
  // subHiddenId: hidden input for sub caste value
  // ownCasteId: the profile's own caste select element ID (for "Same Caste")
  function linkSubCaste(casteContainerId, subContainerId, subHiddenId, ownCasteId) {
    _links[casteContainerId] = { subContainerId, subHiddenId, ownCasteId };
  }

  // Update sub caste widget based on checked castes
  function updateSubCasteWidget(casteContainerId) {
    const link = _links[casteContainerId];
    if (!link) return;
    const subContainer = document.getElementById(link.subContainerId);
    const subHidden = document.getElementById(link.subHiddenId);
    const wrapEl = subContainer ? subContainer.parentElement : null;
    if (!subContainer) return;

    const { special, castes } = getCheckedCastes(casteContainerId);

    // "Any Caste" or "Not Interested" → hide sub caste (all subcastes implied)
    if (special.includes('Any Caste') || special.includes('Not Interested')) {
      if (wrapEl) wrapEl.style.display = 'none';
      subContainer.innerHTML = '';
      if (subHidden) subHidden.value = 'Any';
      return;
    }

    // Collect subcastes for selected castes
    let relevantCastes = [...castes];
    if (special.includes('Same Caste')) {
      const own = getOwnCaste(link.ownCasteId);
      if (own && !relevantCastes.includes(own)) relevantCastes.unshift(own);
    }

    if (relevantCastes.length === 0) {
      if (wrapEl) wrapEl.style.display = 'none';
      subContainer.innerHTML = '';
      if (subHidden) subHidden.value = '';
      return;
    }

    // Show widget
    if (wrapEl) wrapEl.style.display = '';

    // Build sub caste checkboxes grouped by caste
    let html = '<div style="display:flex;flex-wrap:wrap;gap:6px 14px;margin-bottom:6px">'
      + '<label style="display:flex;align-items:center;gap:4px;font-size:12px;font-weight:600;color:#2563eb;cursor:pointer">'
      + '<input type="checkbox" class="psc-chk" data-val="Any Sub Caste" style="accent-color:#2563eb"> Any Sub Caste</label>'
      + '</div>';

    relevantCastes.forEach(caste => {
      const subs = getSubcastes(caste);
      if (subs.length === 0) return;
      html += '<div style="margin-bottom:4px"><div style="font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px">' + caste + '</div>'
        + '<div style="display:flex;flex-wrap:wrap;gap:3px 10px">';
      subs.forEach(sc => {
        html += '<label style="display:flex;align-items:center;gap:3px;font-size:11.5px;color:#374151;cursor:pointer;padding:1px 0">'
          + '<input type="checkbox" class="psc-chk" data-val="' + sc + '" data-caste="' + caste + '" style="accent-color:#7c3aed"> ' + sc + '</label>';
      });
      html += '</div></div>';
    });

    // Preserve old selections
    const oldVals = subHidden ? subHidden.value.split(',').map(s => s.trim()).filter(Boolean) : [];

    subContainer.innerHTML = html;

    // Restore old selections
    oldVals.forEach(v => {
      const chk = subContainer.querySelector('.psc-chk[data-val="' + v + '"]');
      if (chk) chk.checked = true;
    });

    // Handle checkbox logic
    subContainer.querySelectorAll('.psc-chk').forEach(chk => {
      chk.addEventListener('change', () => {
        if (chk.dataset.val === 'Any Sub Caste' && chk.checked) {
          subContainer.querySelectorAll('.psc-chk').forEach(c => { if (c !== chk) c.checked = false; });
        }
        if (chk.dataset.val !== 'Any Sub Caste' && chk.checked) {
          const anyChk = subContainer.querySelector('.psc-chk[data-val="Any Sub Caste"]');
          if (anyChk) anyChk.checked = false;
        }
        syncSubHidden(subContainer, subHidden);
      });
    });

    syncSubHidden(subContainer, subHidden);
  }

  function syncSubHidden(container, hidden) {
    if (!hidden) return;
    const vals = [];
    container.querySelectorAll('.psc-chk:checked').forEach(c => vals.push(c.dataset.val));
    hidden.value = vals.join(', ');
  }

  // Set sub caste value from existing data
  function setSubValue(subContainerId, subHiddenId, value) {
    const container = document.getElementById(subContainerId);
    const hidden = document.getElementById(subHiddenId);
    if (!container || !hidden) return;
    hidden.value = value || '';
    // Checkboxes will be restored when updateSubCasteWidget runs
  }

  return { build, setValue, linkSubCaste, updateSubCasteWidget, setSubValue };
})();
