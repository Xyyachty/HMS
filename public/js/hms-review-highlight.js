/**
 * Faculty review overlay: outlines what the student added or changed in the
 * "After" preview iframe. Loaded only when window.__HMS_REVIEW_HIGHLIGHT__ is
 * set (see editor-bridge.blade.php), which is only true inside the faculty
 * Before/After preview, never on a real student or customer visit.
 *
 * Nothing here touches the site's own DOM. The boxes are drawn in a separate
 * overlay layer positioned over each element's bounding rect, because the
 * templates render through React: classes set on a managed node are dropped on
 * the next re-render, injected children are wiped (and an <img> — the site
 * logo, the single most common task — cannot hold children at all), and either
 * mutation would feed straight back into the observer below.
 */
(function () {
  const data = window.__HMS_REVIEW_HIGHLIGHT__;
  if (!data) return;

  const added = data.added || [];
  const modified = data.modified || [];
  if (!added.length && !modified.length) return;

  const LAYER_ID = 'hms-review-highlight-layer';
  const LEGEND_ID = 'hms-review-highlight-legend';
  const COLORS = { added: '#22c55e', modified: '#f59e0b' };
  const BADGES = { added: 'added', modified: 'changed' };

  let layer = null;
  let legend = null;
  let focusKey = null;

  function ensureLayer() {
    if (layer && document.body.contains(layer)) return layer;
    layer = document.createElement('div');
    layer.id = LAYER_ID;
    layer.style.cssText = 'position:absolute;top:0;left:0;width:0;height:0;z-index:2147483000;pointer-events:none;';
    document.body.appendChild(layer);
    return layer;
  }

  function ensureLegend() {
    if (legend && document.body.contains(legend)) return legend;
    legend = document.createElement('div');
    legend.id = LEGEND_ID;
    legend.style.cssText = 'position:fixed;right:10px;bottom:10px;z-index:2147483001;'
      + 'font:600 11px/1.4 system-ui,sans-serif;background:rgba(15,23,42,.92);color:#fff;'
      + 'padding:6px 10px;border-radius:8px;display:flex;gap:10px;pointer-events:none;';
    document.body.appendChild(legend);
    return legend;
  }

  function currentPage() {
    return window.__HMS_CURRENT_PAGE__ || 'home';
  }

  function cssEscapeSafe(value) {
    if (window.CSS && CSS.escape) return CSS.escape(value);
    return String(value).replace(/[^a-zA-Z0-9_-]/g, '\\$&');
  }

  function queryAll(selector) {
    try {
      const found = document.querySelectorAll(selector);
      return found.length ? Array.prototype.slice.call(found) : [];
    } catch (e) {
      return [];
    }
  }

  /**
   * All nodes this change applies to, not just the first. The site logo is one
   * stored value painted in the header, the footer and the mobile menu, so a
   * single match would leave the other copies looking untouched.
   */
  function resolveElements(entry) {
    if (entry.key) {
      let els = queryAll('[data-edit-id="' + entry.key.replace(/"/g, '\\"') + '"]');
      if (els.length) return els;
      els = queryAll(entry.key);
      if (els.length) return els;
    }
    if (entry.hms_id) {
      const els = queryAll('[data-hms-id="' + cssEscapeSafe(entry.hms_id) + '"]');
      if (els.length) return els;
    }
    return [];
  }

  function isVisible(el) {
    const rect = el.getBoundingClientRect();
    return rect.width > 0 && rect.height > 0;
  }

  function drawBox(el, type, key) {
    const rect = el.getBoundingClientRect();
    const top = rect.top + window.scrollY;
    const left = rect.left + window.scrollX;
    const color = COLORS[type];

    const box = document.createElement('div');
    box.style.cssText = 'position:absolute;box-sizing:border-box;pointer-events:none;border-radius:3px;'
      + 'outline:2px solid ' + color + ';outline-offset:2px;'
      + 'top:' + top + 'px;left:' + left + 'px;width:' + rect.width + 'px;height:' + rect.height + 'px;';
    if (key && key === focusKey) {
      box.style.boxShadow = '0 0 0 4px rgba(99,102,241,.55)';
      box.style.background = 'rgba(99,102,241,.12)';
    }

    const badge = document.createElement('span');
    badge.textContent = BADGES[type];
    badge.style.cssText = 'position:absolute;top:-9px;left:-4px;white-space:nowrap;'
      + 'font:700 9px/1.6 system-ui,sans-serif;letter-spacing:.02em;text-transform:uppercase;'
      + 'padding:0 5px;border-radius:999px;color:#fff;box-shadow:0 1px 2px rgba(0,0,0,.25);'
      + 'background:' + color + ';';
    box.appendChild(badge);

    ensureLayer().appendChild(box);
  }

  function run() {
    const host = ensureLayer();
    host.innerHTML = '';

    const page = currentPage();
    let addedCount = 0;
    let modifiedCount = 0;

    function paint(entry, type) {
      if (entry.page && entry.page !== page) return false;
      let drew = false;
      resolveElements(entry).forEach(function (el) {
        if (!isVisible(el)) return;
        drawBox(el, type, entry.key);
        drew = true;
      });
      return drew;
    }

    added.forEach(function (entry) { if (paint(entry, 'added')) addedCount++; });
    modified.forEach(function (entry) { if (paint(entry, 'modified')) modifiedCount++; });

    const parts = [];
    if (addedCount) parts.push('<span><i style="display:inline-block;width:8px;height:8px;border-radius:2px;background:' + COLORS.added + '"></i> ' + addedCount + ' added</span>');
    if (modifiedCount) parts.push('<span><i style="display:inline-block;width:8px;height:8px;border-radius:2px;background:' + COLORS.modified + '"></i> ' + modifiedCount + ' changed</span>');
    const bar = ensureLegend();
    bar.innerHTML = parts.join('');
    bar.style.display = parts.length ? 'flex' : 'none';
  }

  let timer = null;
  function scheduleRun() {
    clearTimeout(timer);
    timer = setTimeout(run, 150);
  }

  function focusEntry(key) {
    focusKey = key;
    const all = added.concat(modified);
    let target = null;
    for (let i = 0; i < all.length && !target; i++) {
      if (all[i].key === key) target = resolveElements(all[i])[0] || null;
    }
    if (!target) target = queryAll(key)[0] || null;
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    run();
    setTimeout(function () { focusKey = null; run(); }, 2600);
  }

  window.addEventListener('message', function (e) {
    const msg = e.data;
    if (msg && msg.type === 'hms-diff-focus') focusEntry(msg.key);
  });

  /** Our own boxes land in document.body too — reacting to them would spin forever. */
  function isOurs(node) {
    return node && node.nodeType === 1
      && (node.id === LAYER_ID || node.id === LEGEND_ID
        || (layer && layer.contains(node)) || (legend && legend.contains(node)));
  }

  function onMutations(records) {
    for (let i = 0; i < records.length; i++) {
      const r = records[i];
      if (isOurs(r.target)) continue;
      let ownAdded = r.addedNodes.length > 0;
      for (let j = 0; j < r.addedNodes.length; j++) {
        if (!isOurs(r.addedNodes[j])) { ownAdded = false; break; }
      }
      if (ownAdded) continue;
      scheduleRun();
      return;
    }
  }

  function boot() {
    run();
    // The templates paint through React after first paint, and images resize the
    // boxes as they load, so the overlay is redrawn on anything that moves.
    new MutationObserver(onMutations).observe(document.body, { childList: true, subtree: true });
    window.addEventListener('scroll', scheduleRun, { passive: true });
    window.addEventListener('resize', scheduleRun);
    window.addEventListener('load', scheduleRun);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
