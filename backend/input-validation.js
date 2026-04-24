// ===== INPUT VALIDATION: Real-time + Submit-time =====
// Shared by user-panel.php and admin-panel.php
// Auto-attaches validators to inputs based on ID patterns

const InputValidator = (() => {

  // ── Regex patterns for stripping invalid chars ──
  const STRIP = {
    alpha:    /[^a-zA-Z\s.]/g,           // Names: letters, spaces, dots
    place:    /[^a-zA-Z\s.,\-()]/g,      // Places: letters, spaces, commas, dots, hyphens, parens
    alphaDot: /[^a-zA-Z\s.,\-/&()0-9]/g, // Qualifications/Jobs: alphanumeric + punctuation
    digits:   /[^0-9]/g,                  // Mobile, Income: digits only
    email:    /[^a-zA-Z0-9@._\-+]/g,     // Email: standard email chars
  };

  // ── Field rules: maps ID suffix patterns to validation type ──
  // Each rule: { type, maxLen?, minLen?, exactLen?, regex? (for submit), msg }
  const RULES = [
    // === Name fields ===
    { match: /_name$|^(ep|cp|a|e)_name$/,           type: 'alpha',    maxLen: 150, minLen: 2, msg: 'Only letters, spaces & dots allowed' },
    { match: /_father$|^(ep|cp|a|e)_father$/,        type: 'alpha',    maxLen: 100, msg: 'Only letters, spaces & dots allowed' },
    { match: /_mother$|^(ep|cp|a|e)_mother$/,        type: 'alpha',    maxLen: 100, msg: 'Only letters, spaces & dots allowed' },
    { match: /_contact_person$/,                      type: 'alpha',    maxLen: 100, msg: 'Only letters, spaces & dots allowed' },

    // === Place fields ===
    { match: /_place_birth$|_pob$/,                   type: 'place',    maxLen: 255, msg: 'Only letters, spaces & common punctuation' },
    { match: /_nativity$/,                            type: 'place',    maxLen: 255, msg: 'Only letters, spaces & common punctuation' },
    { match: /_place_job$|_place_of_job$/,            type: 'place',    maxLen: 100, msg: 'Only letters, spaces & common punctuation' },

    // === Job / Qualification fields ===
    { match: /_qual$|_qualification$/,                type: 'alphaDot', maxLen: 100, msg: 'Only letters, numbers, spaces & dots allowed' },
    { match: /_(job|occupation)$/,                    type: 'alphaDot', maxLen: 100, msg: 'Only letters, numbers, spaces & dots allowed' },
    { match: /_father_job$/,                          type: 'alphaDot', maxLen: 100, msg: 'Only letters, numbers, spaces & dots allowed' },
    { match: /_mother_job$/,                          type: 'alphaDot', maxLen: 100, msg: 'Only letters, numbers, spaces & dots allowed' },
    { match: /_p_qual|_p_qualification$/,             type: 'alphaDot', maxLen: 100, msg: 'Only letters, numbers, spaces & dots allowed' },
    { match: /_p_job$|_p_job_req$/,                   type: 'alphaDot', maxLen: 100, msg: 'Only letters, numbers, spaces & dots allowed' },

    // === Caste / Gothram / Subcaste ===
    { match: /_subcaste$/,                            type: 'alpha',    maxLen: 100, msg: 'Only letters, spaces & dots allowed' },
    { match: /_gothram$/,                             type: 'alpha',    maxLen: 100, msg: 'Only letters, spaces & dots allowed' },
    { match: /_p_caste$/,                             type: 'alpha',    maxLen: 100, msg: 'Only letters, spaces & dots allowed' },
    { match: /_p_subcaste$/,                          type: 'alpha',    maxLen: 100, msg: 'Only letters, spaces & dots allowed' },

    // === Mobile / Phone fields ===
    { match: /^(cp|a|up_ap|ap)_mobile$/,                type: 'digits',   exactLen: 10, msg: 'Must be exactly 10 digits' },
    { match: /_alt$|_alt_mobile$/,                    type: 'digits',   exactLen: 10, msg: 'Must be exactly 10 digits' },

    // === Income fields ===
    { match: /_income$/,                              type: 'digits',   maxLen: 10, msg: 'Only numbers allowed' },
    { match: /_p_income$/,                            type: 'digits',   maxLen: 10, msg: 'Only numbers allowed' },

    // === Email ===
    { match: /_email$/,                               type: 'email',    maxLen: 150, msg: 'Enter a valid email address' },

    // === Partner age ===
    { match: /_p_agefrom$|_p_age_from$/,              type: 'digits',   maxLen: 2, msg: 'Enter a valid age (18-70)' },
    { match: /_p_ageto$|_p_age_to$/,                  type: 'digits',   maxLen: 2, msg: 'Enter a valid age (18-70)' },
  ];

  // ── Error tooltip styling ──
  const ERR_STYLE = 'position:absolute;bottom:-18px;left:0;font-size:10px;color:#dc2626;font-weight:600;white-space:nowrap;pointer-events:none';

  function getRule(id) {
    for (const r of RULES) {
      if (r.match.test(id)) return r;
    }
    return null;
  }

  // Show inline error below input
  function showErr(input, msg) {
    let tip = input._valTip;
    if (!tip) {
      tip = document.createElement('div');
      tip.style.cssText = ERR_STYLE;
      tip.className = 'val-err-tip';
      const parent = input.parentElement;
      if (parent && getComputedStyle(parent).position === 'static') parent.style.position = 'relative';
      (parent || input.parentElement).appendChild(tip);
      input._valTip = tip;
    }
    tip.textContent = msg;
    tip.style.display = '';
    input.style.borderColor = '#dc2626';
  }

  function clearErr(input) {
    if (input._valTip) input._valTip.style.display = 'none';
    input.style.borderColor = '';
  }

  // ── Real-time input handler ──
  function onInput(e) {
    const input = e.target;
    const rule = getRule(input.id);
    if (!rule) return;

    const strip = STRIP[rule.type];
    if (strip) {
      const pos = input.selectionStart;
      const before = input.value;
      const cleaned = before.replace(strip, '');
      if (cleaned !== before) {
        input.value = cleaned;
        // Adjust cursor position
        const diff = before.length - cleaned.length;
        input.setSelectionRange(pos - diff, pos - diff);
        showErr(input, rule.msg);
        setTimeout(() => clearErr(input), 2000);
      } else {
        clearErr(input);
      }
    }

    // Enforce maxLen
    if (rule.maxLen && input.value.length > rule.maxLen) {
      input.value = input.value.slice(0, rule.maxLen);
    }
  }

  // ── Paste handler: clean pasted content ──
  function onPaste(e) {
    const input = e.target;
    const rule = getRule(input.id);
    if (!rule) return;

    const strip = STRIP[rule.type];
    if (!strip) return;

    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData).getData('text');
    const cleaned = pasted.replace(strip, '');
    document.execCommand('insertText', false, cleaned);

    if (cleaned !== pasted) {
      showErr(input, rule.msg);
      setTimeout(() => clearErr(input), 2000);
    }
  }

  // ── Validate a single field (for submit-time) ──
  // Returns error message or null
  function validateField(id) {
    const el = document.getElementById(id);
    if (!el) return null;
    const val = el.value.trim();
    const rule = getRule(id);
    if (!rule) return null;

    if (rule.exactLen && val && val.length !== rule.exactLen) {
      showErr(el, rule.msg);
      return rule.msg;
    }
    if (rule.minLen && val && val.length < rule.minLen) {
      showErr(el, 'Minimum ' + rule.minLen + ' characters');
      return 'Minimum ' + rule.minLen + ' characters';
    }
    if (rule.type === 'email' && val && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
      showErr(el, 'Invalid email format');
      return 'Invalid email format';
    }
    if ((id.includes('_p_agefrom') || id.includes('_p_age_from') || id.includes('_p_ageto') || id.includes('_p_age_to')) && val) {
      const n = parseInt(val);
      if (isNaN(n) || n < 18 || n > 70) {
        showErr(el, 'Age must be 18-70');
        return 'Age must be 18-70';
      }
    }

    clearErr(el);
    return null;
  }

  // ── Validate all fields for a given prefix (e.g. 'cp_', 'ep_', 'a_', 'e_') ──
  // Returns array of { id, msg } errors
  function validateAll(prefix) {
    const errors = [];
    document.querySelectorAll('input[id^="' + prefix + '"], textarea[id^="' + prefix + '"]').forEach(el => {
      if (el.type === 'hidden' || el.type === 'file') return;
      const msg = validateField(el.id);
      if (msg) errors.push({ id: el.id, msg });
    });
    return errors;
  }

  // ── Auto-attach validators to all matching inputs ──
  function init() {
    document.querySelectorAll('input, textarea').forEach(el => {
      if (!el.id || el.type === 'hidden' || el.type === 'file' || el.type === 'radio' || el.type === 'checkbox') return;
      const rule = getRule(el.id);
      if (!rule) return;
      el.addEventListener('input', onInput);
      el.addEventListener('paste', onPaste);
      // Set maxlength attribute
      if (rule.maxLen && !el.maxLength) el.maxLength = rule.maxLen;
      if (rule.exactLen) el.maxLength = rule.exactLen;
    });
  }

  return { init, validateField, validateAll };
})();

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', InputValidator.init);
} else {
  // DOM already loaded, but modals may not be rendered yet
  // Use a small delay to ensure modal HTML is in DOM
  setTimeout(InputValidator.init, 100);
}
