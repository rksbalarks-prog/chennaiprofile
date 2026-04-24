// ===== GOTHRAM AUTOCOMPLETE: Standard Vedic Gothra list =====
// Based on traditional Hindu Gothra records (Saptarishi lineage + major branches)
// Shared by user-panel.php and admin-panel.php

const GOTHRAM_LIST = [
  // Saptarishi (7 primary)
  "Agasthya", "Angirasa", "Atri", "Bharadwaja", "Gautama", "Jamadagni", "Kashyapa",
  "Vasishta", "Vishwamitra",
  // Major branches & common Gothrams
  "Aathreya", "Aavatsaara", "Agamarkana", "Alambaayana", "Aupamanyava",
  "Baadharayana", "Baijavapa", "Bharadvaja", "Bhargava", "Bhrigu",
  "Chaandilya", "Chandilya", "Daalabhya", "Dhananjaya",
  "Garga", "Gargeya", "Gautama Maharishi", "Harita", "Harithasa",
  "Idhmavaaha", "Jambu Maharishi",
  "Kaashyapa", "Kalabodhana", "Kamsa", "Kanva", "Kapi", "Kasyapa",
  "Katyayana", "Kaundinya", "Kaushika", "Koushika", "Krishnaatreya",
  "Kutsa", "Lohitha", "Maadhyandina", "Maitreya", "Mandavya",
  "Markandeya", "Maudgalya", "Mudgala", "Naidhruva",
  "Parasara", "Parthivasa", "Pourukutsa", "Prachina",
  "Raathithara", "Rouhithya",
  "Saandiilya", "Saankrithi", "Saavarni", "Salankayana",
  "Sandilya", "Sankriti", "Satamarshana", "Savarna",
  "Shaalankaayana", "Shaandilya", "Shandilya", "Shiva", "Siva",
  "Shounaka", "Srivatsa", "Srivatsasa", "Sumantu",
  "Suparnasa", "Suryadhwaja",
  "Upamanyu", "Upmanya",
  "Vaadhula", "Vaalmiki", "Vadhoola", "Vainya",
  "Vatsa", "Vatula", "Vishnu", "Viswamitra",
  // Tamil-specific Gothrams
  "Agnikula", "Angirasa Gothram", "Bharadwaja Gothram",
  "Dakshinamurthy", "Ganesa", "Kumara",
  "Nandhi", "Siva Gothram", "Subramaniya", "Surya"
];

const GothramSuggest = (() => {
  let _activeInput = null;
  let _dropdown = null;

  function createDropdown() {
    if (_dropdown) return _dropdown;
    const d = document.createElement('div');
    d.id = '_gothramSuggestDD';
    d.style.cssText = 'position:absolute;z-index:9999;background:#fff;border:1.5px solid #d1d5db;border-radius:8px;max-height:200px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,0.12);display:none;width:100%;font-size:13px';
    document.body.appendChild(d);
    _dropdown = d;
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
    const startsWith = GOTHRAM_LIST.filter(g => g.toLowerCase().startsWith(q));
    const contains = GOTHRAM_LIST.filter(g => !g.toLowerCase().startsWith(q) && g.toLowerCase().includes(q));
    const matches = [...startsWith, ...contains].slice(0, 10);
    if (matches.length === 0) { dd.style.display = 'none'; return; }

    positionDropdown(input);
    dd.innerHTML = matches.map((m, i) => {
      const idx = m.toLowerCase().indexOf(q);
      const before = m.slice(0, idx);
      const match = m.slice(idx, idx + q.length);
      const after = m.slice(idx + q.length);
      return '<div class="gs-item" data-idx="' + i + '" style="padding:8px 12px;cursor:pointer;border-bottom:1px solid #f3f4f6;transition:background .1s"'
        + ' onmouseenter="this.style.background=\'#f0fdf4\'" onmouseleave="this.style.background=\'#fff\'">'
        + before + '<strong style="color:#16a34a">' + match + '</strong>' + after + '</div>';
    }).join('');
    dd.style.display = 'block';

    dd.querySelectorAll('.gs-item').forEach((item, i) => {
      item.onclick = () => {
        input.value = matches[i];
        dd.style.display = 'none';
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
      };
    });
  }

  function onKeyDown(e) {
    const dd = _dropdown;
    if (!dd || dd.style.display === 'none') return;
    const items = dd.querySelectorAll('.gs-item');
    if (items.length === 0) return;
    let active = dd.querySelector('.gs-item[data-active]');
    let idx = active ? parseInt(active.dataset.idx) : -1;

    if (e.key === 'ArrowDown') { e.preventDefault(); idx = Math.min(idx + 1, items.length - 1); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); idx = Math.max(idx - 1, 0); }
    else if (e.key === 'Enter' && active) { e.preventDefault(); active.click(); return; }
    else if (e.key === 'Escape') { dd.style.display = 'none'; return; }
    else return;

    items.forEach(it => { it.removeAttribute('data-active'); it.style.background = '#fff'; });
    if (items[idx]) {
      items[idx].setAttribute('data-active', '1');
      items[idx].style.background = '#f0fdf4';
      items[idx].scrollIntoView({ block: 'nearest' });
    }
  }

  function attach(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    input.setAttribute('autocomplete', 'off');
    input.addEventListener('input', () => { _activeInput = input; showSuggestions(input, input.value.trim()); });
    input.addEventListener('focus', () => { _activeInput = input; if (input.value.trim().length >= 1) showSuggestions(input, input.value.trim()); });
    input.addEventListener('keydown', onKeyDown);
  }

  return { attach };
})();
