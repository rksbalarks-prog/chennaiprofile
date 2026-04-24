// ===== COMBOBOX: input-with-suggestions + free typing =====
// Replaces native <datalist>-backed inputs with a custom dropdown that:
//   • shows all options when the input is focused (or on chevron click)
//   • filters options as the user types
//   • lets the user pick a suggestion OR keep a custom typed value
// Auto-enhances every `<input list="...">` it finds. Safe to run multiple times.
// Shared by user-panel.php and admin-panel.php.

(function(){
  const INJECTED_STYLE_ID = 'cbx-style';

  function injectStyle(){
    if (document.getElementById(INJECTED_STYLE_ID)) return;
    const s = document.createElement('style');
    s.id = INJECTED_STYLE_ID;
    s.textContent = `
.cbx{position:relative;display:block}
.cbx > input{width:100%;padding-right:30px !important}
.cbx-chev{position:absolute;right:8px;top:50%;transform:translateY(-50%);width:18px;height:18px;border:none;background:transparent;cursor:pointer;color:var(--ink3,#6b7280);padding:0;display:flex;align-items:center;justify-content:center;transition:color .12s,transform .15s}
.cbx-chev:hover{color:var(--accent,#c2553d)}
.cbx-chev::after{content:'';width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-top:5px solid currentColor}
.cbx.open .cbx-chev{color:var(--accent,#c2553d)}
.cbx.open .cbx-chev::after{transform:rotate(180deg)}
.cbx-menu{position:absolute;top:calc(100% + 4px);left:0;right:0;max-height:240px;overflow-y:auto;background:#fff;border:1px solid var(--border,#e5e7eb);border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.14);z-index:60;padding:4px 0}
.cbx-opt{display:block;width:100%;text-align:left;padding:7px 12px;background:transparent;border:none;font-size:12.5px;color:var(--ink,#1a1a2e);cursor:pointer;font-family:inherit}
.cbx-opt:hover,.cbx-opt.active{background:#fef2ed;color:var(--accent,#c2553d)}
.cbx-empty{padding:9px 12px;font-size:11.5px;color:var(--ink4,#94a3b8);font-style:italic}
`;
    document.head.appendChild(s);
  }

  function attach(input){
    if (!input || input.dataset.cbxInit === '1') return;
    const listId = input.getAttribute('list');
    if (!listId) return;
    const dl = document.getElementById(listId);
    if (!dl) return;

    // Snapshot options from the datalist. We keep the <datalist> in the DOM
    // (other inputs may still reference the same id), but remove the `list`
    // attribute from THIS input so the browser's native suggestion popup
    // doesn't fight our custom one.
    const options = Array.from(dl.querySelectorAll('option'))
      .map(o => (o.value || o.textContent || '').trim())
      .filter(Boolean);

    input.removeAttribute('list');
    input.setAttribute('autocomplete', 'off');
    input.dataset.cbxInit = '1';

    // Wrap input so we can position the chevron and menu around it
    const wrap = document.createElement('div');
    wrap.className = 'cbx';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    const chev = document.createElement('button');
    chev.type = 'button';
    chev.className = 'cbx-chev';
    chev.tabIndex = -1;
    chev.setAttribute('aria-label', 'Show suggestions');
    wrap.appendChild(chev);

    const menu = document.createElement('div');
    menu.className = 'cbx-menu';
    menu.style.display = 'none';
    wrap.appendChild(menu);

    let isOpen = false;
    let activeIdx = -1;
    let filtered = options.slice();

    function render(){
      menu.innerHTML = '';
      if (filtered.length === 0) {
        const e = document.createElement('div');
        e.className = 'cbx-empty';
        e.textContent = 'No matches — press Enter or Tab to keep your text';
        menu.appendChild(e);
        return;
      }
      filtered.forEach((opt, i) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'cbx-opt' + (i === activeIdx ? ' active' : '');
        b.textContent = opt;
        // Use mousedown so it fires before the input's blur closes the menu
        b.addEventListener('mousedown', (e) => { e.preventDefault(); pick(opt); });
        menu.appendChild(b);
      });
      // Scroll active item into view
      if (activeIdx >= 0) {
        const active = menu.querySelector('.cbx-opt.active');
        if (active) active.scrollIntoView({ block: 'nearest' });
      }
    }

    function filter(q){
      const s = (q || '').trim().toLowerCase();
      if (!s) filtered = options.slice();
      else filtered = options.filter(o => o.toLowerCase().includes(s));
      activeIdx = -1;
      render();
    }

    function open(){
      if (isOpen) return;
      isOpen = true;
      filter(input.value);
      menu.style.display = 'block';
      wrap.classList.add('open');
      // If menu would overflow viewport, flip above the input
      setTimeout(() => {
        const r = menu.getBoundingClientRect();
        if (r.bottom > window.innerHeight - 8 && r.height < r.top) {
          menu.style.top = 'auto';
          menu.style.bottom = 'calc(100% + 4px)';
        } else {
          menu.style.top = '';
          menu.style.bottom = '';
        }
      }, 0);
    }

    function close(){
      if (!isOpen) return;
      isOpen = false;
      menu.style.display = 'none';
      wrap.classList.remove('open');
      activeIdx = -1;
    }

    function pick(val){
      input.value = val;
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
      close();
    }

    input.addEventListener('focus', () => open());
    input.addEventListener('input', () => { open(); filter(input.value); });
    input.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!isOpen) { open(); return; }
        activeIdx = Math.min(activeIdx + 1, filtered.length - 1);
        render();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (!isOpen) return;
        activeIdx = Math.max(activeIdx - 1, 0);
        render();
      } else if (e.key === 'Enter') {
        if (isOpen && activeIdx >= 0 && activeIdx < filtered.length) {
          e.preventDefault();
          pick(filtered[activeIdx]);
        } else {
          // Keep whatever the user typed; don't submit the surrounding form.
          if (isOpen) { e.preventDefault(); close(); }
        }
      } else if (e.key === 'Escape') {
        if (isOpen) { e.preventDefault(); close(); }
      } else if (e.key === 'Tab') {
        close();
      }
    });

    // Clicking the chevron toggles the menu (don't steal focus first — that
    // would fire the focus listener and re-open immediately after close).
    chev.addEventListener('mousedown', (e) => {
      e.preventDefault();
      if (isOpen) { close(); input.focus(); }
      else { input.focus(); open(); }
    });

    // Close when clicking outside
    document.addEventListener('mousedown', (e) => {
      if (!wrap.contains(e.target)) close();
    });
    // Also close when the input loses focus to an element outside the wrap.
    // (Use 'focusout' so programmatic blur scenarios still close cleanly.)
    wrap.addEventListener('focusout', (e) => {
      if (!wrap.contains(e.relatedTarget)) close();
    });
  }

  function autoInit(root){
    injectStyle();
    const container = root || document;
    container.querySelectorAll('input[list]').forEach(attach);
  }

  window.Combobox = { attach, autoInit };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => autoInit());
  } else {
    autoInit();
  }
})();
