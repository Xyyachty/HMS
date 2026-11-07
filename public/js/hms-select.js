/* A themed drop-down for the Staff Tools pages, in place of the browser's <select>.
 *
 * The browser draws a <select>'s list itself: plain text on the system's own
 * background, which ignores the page's colours and looks out of place. This draws
 * the list in the page's card colours instead, with groups, a tick on the chosen
 * option and full keyboard use (arrows, Home/End, Enter, Escape, type a letter).
 *
 * It takes the same props and <option>/<optgroup> children as a <select>, and
 * calls onChange with an event-like { target: { value } } where value is a string,
 * so swapping the tag is the whole change:
 *
 *   <HmsSelect className="booking-input" value={v} onChange={e => setV(e.target.value)}>
 *     <option value="">Choose…</option>
 *     {list.map(x => <option key={x.id} value={x.id}>{x.name}</option>)}
 *   </HmsSelect>
 *
 * The list is drawn in document.body, so a modal's overflow never cuts it off.
 */
(function () {
  if (!window.React || !window.ReactDOM) return;
  var h = React.createElement;

  var css = ''
    + '.hms-select{position:relative;min-width:0;max-width:100%}'
    + '.hms-select__btn{display:flex;align-items:center;gap:.5rem;max-width:100%;text-align:left;cursor:pointer;font:inherit;color:inherit;-webkit-appearance:none;appearance:none}'
    + '.hms-select__btn:disabled{cursor:not-allowed;opacity:.6}'
    + '.hms-select__value{flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}'
    + '.hms-select__value.is-empty{opacity:.65}'
    + '.hms-select__chev{flex:none;font-size:.7em;opacity:.65;transition:transform .15s}'
    + '.hms-select.is-open .hms-select__chev{transform:rotate(180deg)}'
    + '.hms-select__list{position:fixed;z-index:100000;box-sizing:border-box;margin:0;padding:.3rem;list-style:none;overflow-y:auto;'
    + 'max-width:calc(100vw - 16px);border-radius:.75rem;background:var(--card,#fff);color:var(--fg,#1a1a1a);'
    + 'border:1px solid var(--border,#e2ddd5);box-shadow:0 18px 40px -12px rgba(0,0,0,.45),0 2px 6px rgba(0,0,0,.12);'
    + 'font-size:.875rem;animation:hms-select-in .12s ease-out}'
    + '@keyframes hms-select-in{from{opacity:0;transform:translateY(-4px)}}'
    + '.hms-select__group{padding:.65rem .7rem .3rem;font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--fg-muted,#7a7570)}'
    + '.hms-select__group:first-child{padding-top:.4rem}'
    + '.hms-select__opt{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.55rem .7rem;border-radius:.5rem;cursor:pointer;line-height:1.3}'
    + '.hms-select__opt.is-active{background:color-mix(in srgb,var(--accent,#1b4332) 14%,transparent)}'
    + '.hms-select__opt[aria-selected="true"]{color:var(--accent,#1b4332);font-weight:600}'
    + '.hms-select__opt i{flex:none;font-size:.75em}'
    + '.hms-select__opt.is-disabled{opacity:.45;cursor:not-allowed}'
    + '@media (prefers-reduced-motion:reduce){.hms-select__list{animation:none}}';
  var style = document.createElement('style');
  style.textContent = css;
  (document.head || document.documentElement).appendChild(style);

  // Style keys that place the field in its row; they go on the wrapper, the rest on the button.
  var LAYOUT = ['flex', 'flexGrow', 'flexShrink', 'flexBasis', 'width', 'minWidth', 'maxWidth', 'alignSelf', 'justifySelf',
    'gridColumn', 'order', 'margin', 'marginTop', 'marginBottom', 'marginLeft', 'marginRight'];
  // Page colours copied onto the list, since it sits in <body>, outside the page's own wrapper.
  var TOKENS = ['--card', '--fg', '--fg-muted', '--border', '--accent', '--bg'];

  function text(node) {
    if (node == null || typeof node === 'boolean') return '';
    if (typeof node === 'string' || typeof node === 'number') return String(node);
    if (Array.isArray(node)) return node.map(text).join('');
    return node.props ? text(node.props.children) : '';
  }

  // <option>/<optgroup> children into rows: { group } headings and { value, label, disabled } options.
  function readRows(children, rows) {
    React.Children.forEach(children, function (c) {
      if (!c || !c.type) return;
      if (c.type === React.Fragment) { readRows(c.props.children, rows); return; }
      if (c.type === 'optgroup') { rows.push({ group: c.props.label }); readRows(c.props.children, rows); return; }
      if (c.type === 'option') {
        var label = text(c.props.children);
        rows.push({ value: String(c.props.value !== undefined ? c.props.value : label), label: label, disabled: !!c.props.disabled });
      }
    });
    return rows;
  }

  window.HmsSelect = function HmsSelect(props) {
    var rows = readRows(props.children, []);
    var opts = rows.filter(function (r) { return !r.group; });
    var value = props.value != null ? String(props.value) : '';
    var selIndex = opts.findIndex(function (o) { return o.value === value; });
    // Like a <select>, an unknown value shows the first option.
    if (selIndex < 0) selIndex = opts.findIndex(function (o) { return !o.disabled; });
    var selected = opts[selIndex];

    var openState = React.useState(false), open = openState[0], setOpen = openState[1];
    var activeState = React.useState(-1), active = activeState[0], setActive = activeState[1];
    var posState = React.useState(null), pos = posState[0], setPos = posState[1];
    var btnRef = React.useRef(null), listRef = React.useRef(null), typed = React.useRef({ s: '', t: 0 });
    var listId = React.useId();

    function place() {
      var b = btnRef.current;
      if (!b) return;
      var r = b.getBoundingClientRect(), cs = getComputedStyle(b);
      var below = window.innerHeight - r.bottom, above = r.top;
      var up = below < 240 && above > below;
      var s = {
        left: Math.max(8, Math.min(r.left, window.innerWidth - 8 - r.width)),
        minWidth: r.width,
        maxHeight: Math.max(120, Math.min(300, (up ? above : below) - 14)),
        fontFamily: cs.fontFamily,
      };
      if (up) s.bottom = window.innerHeight - r.top + 6; else s.top = r.bottom + 6;
      TOKENS.forEach(function (t) { var v = cs.getPropertyValue(t).trim(); if (v) s[t] = v; });
      setPos(s);
    }

    function step(from, dir) {
      for (var i = from + dir; i >= 0 && i < opts.length; i += dir) if (!opts[i].disabled) return i;
      return from;
    }
    function openList() { place(); setActive(selIndex); setOpen(true); }
    function close(focus) { setOpen(false); if (focus && btnRef.current) btnRef.current.focus(); }
    function choose(i) {
      var o = opts[i];
      if (!o || o.disabled) return;
      close(true);
      if (o.value !== value && props.onChange) {
        props.onChange({ target: { value: o.value, name: props.name, id: props.id }, preventDefault: function () {}, stopPropagation: function () {} });
      }
    }

    React.useEffect(function () {
      if (!open) return undefined;
      var inside = function (t) { return (btnRef.current && btnRef.current.contains(t)) || (listRef.current && listRef.current.contains(t)); };
      var onDown = function (e) { if (!inside(e.target)) close(false); };
      var onScroll = function (e) { if (!(listRef.current && listRef.current.contains(e.target))) place(); };
      document.addEventListener('mousedown', onDown);
      document.addEventListener('touchstart', onDown);
      window.addEventListener('scroll', onScroll, true);
      window.addEventListener('resize', place);
      return function () {
        document.removeEventListener('mousedown', onDown);
        document.removeEventListener('touchstart', onDown);
        window.removeEventListener('scroll', onScroll, true);
        window.removeEventListener('resize', place);
      };
    }, [open]);

    React.useEffect(function () {
      if (!open || active < 0 || !listRef.current) return;
      var el = listRef.current.querySelector('[data-i="' + active + '"]');
      if (el) el.scrollIntoView({ block: 'nearest' });
    }, [open, active]);

    function onKeyDown(e) {
      var k = e.key;
      if (k === 'ArrowDown' || k === 'ArrowUp') {
        e.preventDefault();
        if (!open) openList(); else setActive(step(active, k === 'ArrowDown' ? 1 : -1));
      } else if ((k === 'Home' || k === 'End') && open) {
        e.preventDefault();
        setActive(k === 'Home' ? step(-1, 1) : step(opts.length, -1));
      } else if (k === 'Enter' || k === ' ') {
        e.preventDefault();
        if (open && active >= 0) choose(active); else openList();
      } else if (k === 'Escape' && open) {
        e.preventDefault();
        e.stopPropagation();
        close(true);
      } else if (k === 'Tab' && open) {
        close(false);
      } else if (k.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
        // Type a letter or two to jump to an option.
        var now = Date.now();
        typed.current.s = (now - typed.current.t < 700 ? typed.current.s : '') + k.toLowerCase();
        typed.current.t = now;
        var from = open ? active : selIndex;
        for (var n = 1; n <= opts.length; n++) {
          var i = (from + n + (typed.current.s.length > 1 ? opts.length - 1 : 0)) % opts.length;
          if (!opts[i].disabled && opts[i].label.toLowerCase().indexOf(typed.current.s) === 0) {
            if (open) setActive(i); else choose(i);
            break;
          }
        }
      }
    }

    var layout = {}, own = {};
    Object.keys(props.style || {}).forEach(function (k) { (LAYOUT.indexOf(k) >= 0 ? layout : own)[k] = props.style[k]; });

    var n = -1;
    var items = rows.map(function (r, key) {
      if (r.group) return h('li', { key: 'g' + key, role: 'presentation', className: 'hms-select__group' }, r.group);
      var i = ++n;
      return h('li', {
        key: key, id: listId + '-' + i, 'data-i': i, role: 'option',
        'aria-selected': i === selIndex, 'aria-disabled': r.disabled || undefined,
        className: 'hms-select__opt' + (i === active ? ' is-active' : '') + (r.disabled ? ' is-disabled' : ''),
        onMouseDown: function (e) { e.preventDefault(); },
        onMouseEnter: function () { if (!r.disabled) setActive(i); },
        onClick: function () { choose(i); },
      }, h('span', null, r.label), i === selIndex ? h('i', { className: 'fa-solid fa-check', 'aria-hidden': 'true' }) : null);
    });
    if (!opts.length) items.push(h('li', { key: 'none', className: 'hms-select__opt is-disabled', role: 'presentation' }, 'Nothing to choose'));

    return h('div', { className: 'hms-select' + (open ? ' is-open' : ''), style: layout },
      h('button', {
        type: 'button', id: props.id, ref: btnRef, style: own, disabled: props.disabled, title: props.title,
        className: 'hms-select__btn ' + (props.className || ''),
        role: 'combobox', 'aria-haspopup': 'listbox', 'aria-expanded': open,
        'aria-controls': open ? listId : undefined,
        'aria-activedescendant': open && active >= 0 ? listId + '-' + active : undefined,
        'aria-label': props['aria-label'], 'aria-labelledby': props['aria-labelledby'], 'aria-invalid': props['aria-invalid'],
        onClick: function () { if (open) close(false); else openList(); },
        onKeyDown: onKeyDown,
      },
        h('span', { className: 'hms-select__value' + (selected && selected.value !== '' ? '' : ' is-empty') }, selected ? selected.label : (props.placeholder || 'Choose…')),
        h('i', { className: 'fa-solid fa-chevron-down hms-select__chev', 'aria-hidden': 'true' })),
      open && pos ? ReactDOM.createPortal(
        h('ul', { id: listId, role: 'listbox', ref: listRef, className: 'hms-select__list', style: pos, 'aria-label': props['aria-label'] }, items),
        document.body) : null);
  };
})();
