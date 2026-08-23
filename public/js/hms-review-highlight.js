/**
 * Faculty review overlay: outlines elements the student added or changed in
 * the "After" preview iframe. Read-only — never touches customizations, only
 * decorates whatever hms-template-editor.js / hms-site-content.js already
 * rendered. Loaded only when window.__HMS_REVIEW_HIGHLIGHT__ is set (see
 * editor-bridge.blade.php), which is only true inside the faculty Before/
 * After preview, never on a real student/customer visit.
 */
(function () {
  const data = window.__HMS_REVIEW_HIGHLIGHT__;
  if (!data || (!data.added || !data.added.length) && (!data.modified || !data.modified.length)) return;

  const STYLE_ID = 'hms-review-highlight-style';
  const LEGEND_ID = 'hms-review-highlight-legend';

  function ensureStyle() {
    if (document.getElementById(STYLE_ID)) return;
    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = [
      '.hms-diff-added, .hms-diff-modified { position: relative !important; }',
      '.hms-diff-added { outline: 2px solid #22c55e !important; outline-offset: 2px !important; }',
      '.hms-diff-modified { outline: 2px solid #f59e0b !important; outline-offset: 2px !important; }',
      '.hms-diff-pulse { animation: hms-diff-pulse 1.1s ease-out 2; }',
      '@keyframes hms-diff-pulse { 0% { outline-color: #6366f1; } 50% { outline-color: transparent; } 100% { outline-color: inherit; } }',
      '.hms-diff-badge {',
      '  position: absolute; top: -0.6rem; left: -0.4rem; z-index: 2147483000;',
      '  font: 700 9px/1.6 system-ui, sans-serif; letter-spacing: .02em; text-transform: uppercase;',
      '  padding: 0 5px; border-radius: 999px; color: #fff; pointer-events: none;',
      '  box-shadow: 0 1px 2px rgba(0,0,0,.25);',
      '}',
      '.hms-diff-badge.added { background: #16a34a; }',
      '.hms-diff-badge.modified { background: #d97706; }',
      '#' + LEGEND_ID + ' {',
      '  position: fixed; right: 10px; bottom: 10px; z-index: 2147483000;',
      '  font: 600 11px/1.4 system-ui, sans-serif; background: rgba(15,23,42,.92); color: #fff;',
      '  padding: 6px 10px; border-radius: 8px; display: flex; gap: 10px; pointer-events: none;',
      '}',
      '#' + LEGEND_ID + ' span { display: inline-flex; align-items: center; gap: 4px; }',
      '#' + LEGEND_ID + ' i { width: 8px; height: 8px; border-radius: 2px; display: inline-block; }',
    ].join('\n');
    document.head.appendChild(style);
  }

  function ensureLegend(counts) {
    let el = document.getElementById(LEGEND_ID);
    if (!el) {
      el = document.createElement('div');
      el.id = LEGEND_ID;
      document.body.appendChild(el);
    }
    const parts = [];
    if (counts.added) parts.push('<span><i style="background:#22c55e"></i>' + counts.added + ' added</span>');
    if (counts.modified) parts.push('<span><i style="background:#f59e0b"></i>' + counts.modified + ' changed</span>');
    el.innerHTML = parts.join('');
    el.style.display = parts.length ? 'flex' : 'none';
  }

  function currentPage() {
    return window.__HMS_CURRENT_PAGE__ || 'home';
  }

  function cssEscapeSafe(value) {
    if (window.CSS && CSS.escape) return CSS.escape(value);
    return String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
  }

  function resolveElement(entry) {
    if (entry.key) {
      try {
        const el = document.querySelector('[data-edit-id="' + entry.key.replace(/"/g, '\\"') + '"]');
        if (el) return el;
      } catch (e) { /* ignore */ }
      try {
        const el = document.querySelector(entry.key);
        if (el) return el;
      } catch (e) { /* ignore */ }
    }
    if (entry.hms_id) {
      try {
        const el = document.querySelector('[data-hms-id="' + cssEscapeSafe(entry.hms_id) + '"]');
        if (el) return el;
      } catch (e) { /* ignore */ }
    }
    return null;
  }

  function clearDecorations() {
    document.querySelectorAll('.hms-diff-added, .hms-diff-modified').forEach(function (el) {
      el.classList.remove('hms-diff-added', 'hms-diff-modified');
      const badge = el.querySelector(':scope > .hms-diff-badge');
      if (badge) badge.remove();
    });
  }

  function decorate(el, type) {
    if (!el || el.__hmsDiffDone) return;
    el.classList.add(type === 'added' ? 'hms-diff-added' : 'hms-diff-modified');
    const badge = document.createElement('span');
    badge.className = 'hms-diff-badge ' + type;
    badge.textContent = type === 'added' ? 'added' : 'changed';
    el.appendChild(badge);
    el.__hmsDiffDone = true;
  }

  function run() {
    clearDecorations();
    document.querySelectorAll('.hms-diff-added, .hms-diff-modified').forEach(function (el) {
      el.__hmsDiffDone = false;
    });

    const page = currentPage();
    let added = 0;
    let modified = 0;

    (data.added || []).forEach(function (entry) {
      if (entry.page && entry.page !== page) return;
      const el = resolveElement(entry);
      if (el) {
        decorate(el, 'added');
        added++;
      }
    });
    (data.modified || []).forEach(function (entry) {
      if (entry.page && entry.page !== page) return;
      const el = resolveElement(entry);
      if (el) {
        decorate(el, 'modified');
        modified++;
      }
    });

    ensureLegend({ added: added, modified: modified });
  }

  function scheduleRun() {
    clearTimeout(scheduleRun._t);
    scheduleRun._t = setTimeout(run, 200);
  }

  function focusEntry(key) {
    if (!key) return;
    let el = null;
    try {
      el = document.querySelector('[data-edit-id="' + key.replace(/"/g, '\\"') + '"]') || document.querySelector(key);
    } catch (e) { /* ignore */ }
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el.classList.add('hms-diff-pulse');
    setTimeout(function () { el.classList.remove('hms-diff-pulse'); }, 2400);
  }

  window.addEventListener('message', function (e) {
    const msg = e.data;
    if (msg && msg.type === 'hms-diff-focus') {
      focusEntry(msg.key);
    }
  });

  function boot() {
    ensureStyle();
    run();
    const observer = new MutationObserver(scheduleRun);
    observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class'] });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
