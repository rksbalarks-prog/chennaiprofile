// ===== FORM AUTO-SAVE: Persist unsaved form data in localStorage =====
// Shared by user-panel.php and admin-panel.php

const FormAutoSave = (() => {
  const PREFIX = 'matrimony_draft_';
  const PHOTO_PREFIX = 'matrimony_photo_';
  const DEBOUNCE_MS = 500;
  const PHOTO_DEBOUNCE_MS = 800;
  const _timers = {};
  const _photoTimers = {};
  const _suspend = {}; // { formKey: true } — when true, save is suppressed (used during clearAll)
  const _tracked = {}; // { formKey: { containerSelector, fieldPrefix, excludeIds } }

  // ── Get storage key ──
  function storageKey(formKey) { return PREFIX + formKey; }
  function photoKey(formKey) { return PHOTO_PREFIX + formKey; }

  // ── Collect all field values from a form ──
  function collectFields(formKey) {
    const cfg = _tracked[formKey];
    if (!cfg) return null;
    const data = {};
    const container = cfg.container ? document.querySelector(cfg.container) : document;
    if (!container) return null;
    container.querySelectorAll('input, select, textarea').forEach(el => {
      if (!el.id) return;
      if (el.type === 'file' || el.type === 'hidden') return;
      if (cfg.excludeIds && cfg.excludeIds.includes(el.id)) return;
      if (cfg.fieldPrefix && !el.id.startsWith(cfg.fieldPrefix)) return;

      if (el.type === 'radio') {
        if (el.checked) data[el.name] = el.value;
      } else if (el.type === 'checkbox') {
        data[el.id] = el.checked;
      } else {
        if (el.value && el.value.trim()) data[el.id] = el.value;
      }
    });
    return Object.keys(data).length > 0 ? data : null;
  }

  // ── Read file as base64 data URL ──
  function fileToDataUrl(file) {
    return new Promise(resolve => {
      const reader = new FileReader();
      reader.onload = e => resolve(e.target.result);
      reader.onerror = () => resolve(null);
      reader.readAsDataURL(file);
    });
  }

  // ── Collect photo previews from a form ──
  async function collectPhotos(formKey) {
    const cfg = _tracked[formKey];
    if (!cfg) return null;
    const container = cfg.container ? document.querySelector(cfg.container) : document;
    if (!container) return null;
    const photos = {};
    const fileInputs = container.querySelectorAll('input[type="file"][accept*="image"]');
    for (const input of fileInputs) {
      if (!input.id) continue;
      const file = input.files[0];
      if (!file) continue;
      try {
        // Limit to 200KB base64 to avoid localStorage quota
        if (file.size > 200 * 1024) {
          // Compress via canvas
          const img = await new Promise((res, rej) => { const i = new Image(); i.onload = () => res(i); i.onerror = rej; i.src = URL.createObjectURL(file); });
          const canvas = document.createElement('canvas');
          const maxDim = 400;
          const scale = Math.min(maxDim / img.width, maxDim / img.height, 1);
          canvas.width = Math.round(img.width * scale);
          canvas.height = Math.round(img.height * scale);
          canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
          photos[input.id] = canvas.toDataURL('image/jpeg', 0.5);
        } else {
          photos[input.id] = await fileToDataUrl(file);
        }
      } catch (e) { /* skip */ }
    }
    return Object.keys(photos).length > 0 ? photos : null;
  }

  // ── Save photos to localStorage (debounced, separate key) ──
  function schedulePhotoSave(formKey) {
    if (_suspend[formKey]) return;
    clearTimeout(_photoTimers[formKey]);
    _photoTimers[formKey] = setTimeout(async () => {
      if (_suspend[formKey]) return;
      try {
        const photos = await collectPhotos(formKey);
        if (photos) {
          localStorage.setItem(photoKey(formKey), JSON.stringify(photos));
        }
      } catch (e) {
        // localStorage quota exceeded — silently skip photo save
        console.warn('FormAutoSave: photo save skipped (quota)', e.message);
      }
    }, PHOTO_DEBOUNCE_MS);
  }

  // ── Restore photo previews ──
  function restorePhotos(formKey) {
    const raw = localStorage.getItem(photoKey(formKey));
    if (!raw) return 0;
    try {
      const photos = JSON.parse(raw);
      if (!photos || typeof photos !== 'object') return 0;
      let count = 0;
      Object.keys(photos).forEach(inputId => {
        const dataUrl = photos[inputId];
        if (!dataUrl) return;
        // Find the preview img and placeholder span near the file input
        // Convention: input id "xx_photo1_file" → preview "xx_photo1_prev" or "xx_photo1_preview", placeholder "xx_photo1_ph" or "xx_photo1_placeholder"
        const base = inputId.replace('_file', '');
        const prevImg = document.getElementById(base + '_prev') || document.getElementById(base + '_preview');
        const placeholder = document.getElementById(base + '_ph') || document.getElementById(base + '_placeholder');
        if (prevImg) {
          prevImg.src = dataUrl;
          prevImg.style.display = 'block';
          if (placeholder) placeholder.style.display = 'none';
          count++;
        }
      });
      return count;
    } catch (e) { return 0; }
  }

  // ── Save to localStorage (debounced) ──
  function scheduleSave(formKey) {
    if (_suspend[formKey]) return;
    clearTimeout(_timers[formKey]);
    _timers[formKey] = setTimeout(() => {
      if (_suspend[formKey]) return;
      const data = collectFields(formKey);
      if (data) {
        data._savedAt = new Date().toISOString();
        localStorage.setItem(storageKey(formKey), JSON.stringify(data));
      }
    }, DEBOUNCE_MS);
  }

  // ── Restore fields from localStorage ──
  function restore(formKey) {
    const raw = localStorage.getItem(storageKey(formKey));
    if (!raw) return false;
    try {
      const data = JSON.parse(raw);
      if (!data || typeof data !== 'object') return false;

      const cfg = _tracked[formKey];
      const container = cfg.container ? document.querySelector(cfg.container) : document;
      if (!container) return false;

      let restored = 0;
      Object.keys(data).forEach(key => {
        if (key === '_savedAt') return;

        const radio = container.querySelector('input[type="radio"][name="' + key + '"][value="' + data[key] + '"]');
        if (radio) { radio.checked = true; restored++; return; }

        const el = document.getElementById(key);
        if (!el) return;
        if (cfg.excludeIds && cfg.excludeIds.includes(key)) return;
        if (el.readOnly || el.disabled) return;

        if (el.type === 'checkbox') {
          el.checked = !!data[key]; restored++;
        } else {
          if (!el.value || el.value === '' || el.value === '-' || el.tagName === 'SELECT') {
            el.value = data[key]; restored++;
          }
        }
      });

      // Also restore photos
      restored += restorePhotos(formKey);

      return restored > 0;
    } catch (e) {
      console.warn('FormAutoSave restore error:', e);
      return false;
    }
  }

  // ── Get saved timestamp ──
  function getSavedTime(formKey) {
    try {
      const raw = localStorage.getItem(storageKey(formKey));
      if (raw) {
        const data = JSON.parse(raw);
        if (data._savedAt) return data._savedAt;
      }
      // If only photos saved, return current time approximation
      if (localStorage.getItem(photoKey(formKey))) return new Date().toISOString();
      return null;
    } catch { return null; }
  }

  // ── Clear saved draft ──
  function clear(formKey) {
    localStorage.removeItem(storageKey(formKey));
    localStorage.removeItem(photoKey(formKey));
    clearTimeout(_timers[formKey]);
    clearTimeout(_photoTimers[formKey]);
  }

  // ── Clear saved draft AND reset the form UI ──
  // Use this when the user explicitly discards the draft — they expect the
  // form to look empty, not just the localStorage to be wiped.
  function clearAll(formKey) {
    const cfg = _tracked[formKey];
    clear(formKey);
    if (!cfg) return;

    const container = cfg.container ? document.querySelector(cfg.container) : document;
    if (!container) return;

    // Suspend autosave so the cascade of synthetic input/change events doesn't
    // re-write the draft with the post-clear default values
    _suspend[formKey] = true;
    try {
      container.querySelectorAll('input, select, textarea').forEach(el => {
        if (!el.id) return;
        if (el.type === 'hidden') return;
        if (cfg.excludeIds && cfg.excludeIds.includes(el.id)) return;
        if (cfg.fieldPrefix && !el.id.startsWith(cfg.fieldPrefix)) return;
        if (el.disabled) return;

        if (el.type === 'file') { el.value = ''; return; }
        if (el.type === 'checkbox' || el.type === 'radio') { el.checked = false; return; }
        if (el.tagName === 'SELECT') {
          el.selectedIndex = 0;
          el.dispatchEvent(new Event('change', { bubbles: true }));
          return;
        }
        // text / date / number / tel / email / textarea (clear even if readonly,
        // so auto-computed fields like age inputs go blank)
        el.value = '';
        el.dispatchEvent(new Event('input', { bubbles: true }));
      });

      // Reset image previews (photo placeholders show, previews hide)
      container.querySelectorAll('input[type="file"][accept*="image"]').forEach(input => {
        if (!input.id) return;
        if (cfg.fieldPrefix && !input.id.startsWith(cfg.fieldPrefix)) return;
        const base = input.id.replace('_file', '');
        const prev = document.getElementById(base + '_prev') || document.getElementById(base + '_preview');
        const ph = document.getElementById(base + '_ph') || document.getElementById(base + '_placeholder');
        if (prev) { prev.src = ''; prev.style.display = 'none'; }
        if (ph) ph.style.display = '';
      });

      // Reset partner-caste checkbox widgets, if PartnerCaste module is loaded
      if (typeof PartnerCaste !== 'undefined' && cfg.fieldPrefix) {
        const pfx = cfg.fieldPrefix;
        const pCasteHidden = document.getElementById(pfx + 'p_caste');
        const pSubHidden = document.getElementById(pfx + 'p_subcaste');
        if (pCasteHidden) { try { PartnerCaste.setValue(pfx + 'p_caste_box', pfx + 'p_caste', ''); } catch {} }
        if (pSubHidden)   { try { PartnerCaste.setSubValue(pfx + 'p_subcaste_box', pfx + 'p_subcaste', ''); } catch {} }
      }
    } finally {
      _suspend[formKey] = false;
      // Drop any pending saves scheduled by the synthetic events above
      clearTimeout(_timers[formKey]);
      clearTimeout(_photoTimers[formKey]);
    }
  }

  // ── Check if draft exists ──
  function hasDraft(formKey) {
    return !!(localStorage.getItem(storageKey(formKey)) || localStorage.getItem(photoKey(formKey)));
  }

  // ── Format relative time ──
  function timeAgo(isoStr) {
    if (!isoStr) return '';
    const diff = Date.now() - new Date(isoStr).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return mins + ' min ago';
    const hrs = Math.floor(mins / 60);
    if (hrs < 24) return hrs + ' hr ago';
    const days = Math.floor(hrs / 24);
    return days + ' day' + (days > 1 ? 's' : '') + ' ago';
  }

  // ── Create restore banner UI ──
  function showRestoreBanner(formKey, targetSelector, onRestore, onDiscard) {
    const savedTime = getSavedTime(formKey);
    if (!savedTime) return false;

    const target = document.querySelector(targetSelector);
    if (!target) return false;

    // Remove existing banner if any
    const existing = target.querySelector('.autosave-banner');
    if (existing) existing.remove();

    const banner = document.createElement('div');
    banner.className = 'autosave-banner';
    banner.style.cssText = 'background:#fffbeb;border:1.5px solid #fde68a;border-radius:9px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:12.5px;animation:slideDown .3s ease';
    // Check photo count
    let photoCount = 0;
    try { const p = JSON.parse(localStorage.getItem(photoKey(formKey)) || '{}'); photoCount = Object.keys(p).length; } catch {}
    const photoLabel = photoCount > 0 ? ' + ' + photoCount + ' photo' + (photoCount > 1 ? 's' : '') : '';

    banner.innerHTML = '<div style="display:flex;align-items:center;gap:8px">'
      + '<span style="font-size:16px">📝</span>'
      + '<div><strong style="color:#92400e">Unsaved draft found' + photoLabel + '</strong>'
      + '<div style="color:#a16207;font-size:11px;margin-top:2px">Saved ' + timeAgo(savedTime) + '</div></div></div>'
      + '<div style="display:flex;gap:6px">'
      + '<button class="as-restore-btn" style="padding:5px 12px;background:#16a34a;color:#fff;border:none;border-radius:6px;font-size:11.5px;font-weight:600;cursor:pointer">Restore</button>'
      + '<button class="as-discard-btn" style="padding:5px 12px;background:#dc2626;color:#fff;border:none;border-radius:6px;font-size:11.5px;font-weight:600;cursor:pointer">Discard</button>'
      + '</div>';

    banner.querySelector('.as-restore-btn').onclick = () => {
      restore(formKey);
      banner.style.display = 'none';
      if (onRestore) onRestore();
    };
    banner.querySelector('.as-discard-btn').onclick = () => {
      clearAll(formKey);
      banner.style.display = 'none';
      if (onDiscard) onDiscard();
    };

    // Insert at top of target
    target.insertBefore(banner, target.firstChild);
    return true;
  }

  // ── Track a form: auto-save on every input change ──
  // formKey: unique key for localStorage
  // options: { container, fieldPrefix, excludeIds }
  function track(formKey, options = {}) {
    _tracked[formKey] = {
      container: options.container || null,
      fieldPrefix: options.fieldPrefix || null,
      excludeIds: options.excludeIds || []
    };

    const container = options.container ? document.querySelector(options.container) : document;
    if (!container) return;

    // Listen for text/select input changes
    const handler = (e) => {
      const el = e.target;
      if (!el.id && !el.name) return;
      if (el.type === 'hidden') return;
      // File inputs trigger photo save
      if (el.type === 'file') {
        if (el.files && el.files[0]) schedulePhotoSave(formKey);
        return;
      }
      if (options.fieldPrefix && el.id && !el.id.startsWith(options.fieldPrefix)) return;
      scheduleSave(formKey);
    };

    container.addEventListener('input', handler);
    container.addEventListener('change', handler);
  }

  return { track, restore, clear, clearAll, hasDraft, getSavedTime, showRestoreBanner, timeAgo };
})();
