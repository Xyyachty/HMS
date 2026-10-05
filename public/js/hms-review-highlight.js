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
  /* The outlines come from one of two places. The server embeds them when the
     After link names the task (window.__HMS_REVIEW_HIGHLIGHT__), and the review
     panel posts the same Changes list in once the preview has loaded
     (hms-review-highlight message). The panel's copy is the one faculty are
     already looking at, so the outlines no longer depend on the preview
     recomputing it — that second computation is where they went missing. */
  let added = [];
  let modified = [];
  let stockBranding = false;
  let booted = false;

  function setData(data) {
    added = (data && Array.isArray(data.added)) ? data.added : [];
    modified = (data && Array.isArray(data.modified)) ? data.modified : [];
    stockBranding = !!(data && data.stock_branding);
    if (!added.length && !modified.length && !stockBranding) return;
    if (booted) {
      scheduleRun();
    } else if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', boot);
    } else {
      boot();
    }
  }

  const LAYER_ID = 'hms-review-highlight-layer';
  const LEGEND_ID = 'hms-review-highlight-legend';
  /* One green for everything the student did, added or changed: the faculty
     question is "what did they touch", and the badge still says which. */
  const COLORS = { added: '#22c55e', modified: '#22c55e' };
  const BADGES = { added: 'added', modified: 'changed' };

  let layer = null;
  let legend = null;
  let focusKey = null;

  function ensureLayer() {
    if (layer && document.body.contains(layer)) return layer;
    layer = document.createElement('div');
    layer.id = LAYER_ID;
    layer.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;z-index:2147483000;pointer-events:none;';
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
    /* A key is not always a string: a customization saved under a numeric key
       comes back from PHP as a JSON number, and calling .replace on it threw
       inside run() — one such entry stopped every box and the legend from being
       drawn, so the whole review looked as if nothing had changed. */
    const key = entry.key == null ? '' : String(entry.key);
    if (key) {
      let els = queryAll('[data-edit-id="' + key.replace(/"/g, '\\"') + '"]');
      if (els.length) return els;
      els = queryAll(key);
      if (els.length) return els;
    }
    if (entry.hms_id) {
      const els = queryAll('[data-hms-id="' + cssEscapeSafe(String(entry.hms_id)) + '"]');
      if (els.length) return els;
    }
    return [];
  }

  /**
   * Laid out, on screen, and actually painted.
   *
   * "Painted" has to include what an ancestor is doing. The closed mobile menu is
   * a full-screen overlay held at opacity 0, and the logo inside it is opacity 1
   * on its own — so checking the element alone passed it, and a second CHANGED
   * box was drawn over blank hero where an invisible menu happens to sit.
   */
  function isVisibleThroughAncestors(el) {
    // Chrome answers this properly; the walk below is for everything else.
    if (typeof el.checkVisibility === 'function') {
      try {
        return el.checkVisibility({ opacityProperty: true, visibilityProperty: true, contentVisibilityAuto: true });
      } catch (e) { /* older signature — fall through to the walk */ }
    }

    let node = el;
    while (node && node.nodeType === 1 && node !== document.documentElement) {
      const style = window.getComputedStyle(node);
      if (style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity) === 0) {
        return false;
      }
      node = node.parentElement;
    }

    return true;
  }

  function isPaintable(el) {
    const rect = el.getBoundingClientRect();
    if (rect.width <= 0 || rect.height <= 0) return false;

    if (!isVisibleThroughAncestors(el)) return false;

    // Off-screen entirely: boxes are drawn in viewport space, so there is
    // nothing to show until it scrolls in.
    return rect.bottom > 0 && rect.right > 0
      && rect.top < (window.innerHeight || 0) && rect.left < (window.innerWidth || 0);
  }

  /**
   * Boxes are positioned in viewport coordinates, straight from the rect, with
   * no scroll offset added. Mapping back to document space breaks the moment an
   * element is fixed or sticky — a pinned header keeps a viewport-relative rect,
   * so adding scrollY pushed its box down the page by exactly the scroll amount,
   * which is how the site logo ended up boxed below the navbar it sits in.
   */
  function drawBox(el, type, key) {
    const rect = el.getBoundingClientRect();
    const color = COLORS[type];

    const box = document.createElement('div');
    box.style.cssText = 'position:fixed;box-sizing:border-box;pointer-events:none;border-radius:10px;'
      + 'outline:2px solid ' + color + ';outline-offset:4px;'
      + 'top:' + rect.top + 'px;left:' + rect.left + 'px;width:' + rect.width + 'px;height:' + rect.height + 'px;';
    if (key != null && focusKey != null && String(key) === String(focusKey)) {
      box.style.boxShadow = '0 0 0 4px rgba(99,102,241,.55)';
      box.style.background = 'rgba(99,102,241,.12)';
    }

    const badge = document.createElement('span');
    badge.textContent = BADGES[type];
    badge.style.cssText = 'position:absolute;top:-15px;left:-6px;white-space:nowrap;'
      + 'font:700 9px/1.6 system-ui,sans-serif;letter-spacing:.02em;text-transform:uppercase;'
      + 'padding:0 5px;border-radius:999px;color:#fff;box-shadow:0 1px 2px rgba(0,0,0,.25);'
      + 'background:' + color + ';';
    box.appendChild(badge);

    ensureLayer().appendChild(box);
  }

  /* Customize Your Hotel Branding: the page itself is compared with the stock
     template, so the outline does not depend on any snapshot. A logo that is
     not the default logo, a hotel name that is not the default name, a link
     whose label is not its default label — each of those is the student's
     work, whatever was saved when. */
  function normalise(text) {
    return String(text == null ? '' : text).replace(/\s+/g, ' ').trim().toLowerCase();
  }

  function isDefaultLogo(img) {
    if (!img || img.tagName !== 'IMG') return true;
    if (img.getAttribute('data-logo-fallback') === '1') return true;
    const src = img.getAttribute('src') || '';
    const stock = window.HMS_DEFAULT_LOGO || '/new_logo_in_chtm....png';
    return src === '' || src === stock || src.endsWith(stock) || src.endsWith('new_logo_in_chtm....png');
  }

  function brandingChanges() {
    const site = window.HMSSiteContent || {};
    const stockName = normalise(site.DEFAULT_BRAND_NAME || 'SPC HOTEL');
    const stockNav = {};
    (Array.isArray(site.DEFAULT_NAV) ? site.DEFAULT_NAV : [
      { key: 'home', label: 'Home' }, { key: 'rooms', label: 'Rooms' },
      { key: 'restaurant', label: 'Restaurant' }, { key: 'amenities', label: 'Amenities' },
      { key: 'experience', label: 'Highlights' },
    ]).forEach(function (link) { stockNav[link.key] = [normalise(link.label)]; });
    // A label the nav used to ship with is still the stock label, not a rename.
    (stockNav.experience = stockNav.experience || []).push('experience');

    const out = [];
    queryAll('img[data-hms-content-kind="brand"][data-hms-content-id="logo"]').forEach(function (img) {
      if (!isDefaultLogo(img)) out.push(img);
    });
    queryAll('[data-hms-brand-name]').forEach(function (el) {
      if (normalise(el.textContent) !== stockName) out.push(el);
    });
    queryAll('.nav-bar [data-hms-nav-link]').forEach(function (el) {
      const stock = stockNav[el.getAttribute('data-hms-nav-link')] || [];
      if (stock.indexOf(normalise(el.textContent)) === -1) out.push(el);
    });
    // The phone menu's copies are never on screen in the review; counting them
    // would tell faculty about outlines they cannot see.
    return out.filter(function (el) { return !el.closest('.mobile-menu'); });
  }

  function run() {
    const host = ensureLayer();
    host.innerHTML = '';

    const page = currentPage();
    let addedCount = 0;
    let modifiedCount = 0;

    // Counted on presence, drawn on visibility: the tally is what this page
    // contains, so it does not tick up and down as the preview is scrolled.
    // Each element is boxed once, however many entries point at it.
    const drawn = new Set();
    function box(el, type, key) {
      if (drawn.has(el)) return;
      drawn.add(el);
      if (isPaintable(el)) drawBox(el, type, key);
    }

    // One entry that cannot be drawn is skipped, never allowed to take the rest
    // of the boxes down with it.
    function paint(entry, type) {
      try {
        if (!entry || (entry.page && entry.page !== page)) return false;
        const els = resolveElements(entry);
        if (!els.length) return false;
        els.forEach(function (el) { box(el, type, entry.key); });
        return true;
      } catch (e) {
        return false;
      }
    }

    added.forEach(function (entry) { if (paint(entry, 'added')) addedCount++; });
    modified.forEach(function (entry) { if (paint(entry, 'modified')) modifiedCount++; });

    if (stockBranding) {
      try {
        brandingChanges().forEach(function (el) {
          if (drawn.has(el)) return;
          box(el, 'modified', null);
          modifiedCount++;
        });
      } catch (e) { /* the diff outlines above still stand */ }
    }

    const parts = [];
    const swatch = '<i style="display:inline-block;width:8px;height:8px;border-radius:2px;background:' + COLORS.added + '"></i> ';
    if (addedCount) parts.push('<span>' + swatch + addedCount + ' added</span>');
    if (modifiedCount) parts.push('<span>' + swatch + modifiedCount + ' changed</span>');
    reportStatus(addedCount + modifiedCount, added.length + modified.length + (stockBranding ? 1 : 0));

    // Changes exist but none of them is on this page: say so, rather than
    // leaving faculty to wonder whether the outlines failed.
    if (!parts.length) parts.push('<span>No changes on this page</span>');
    const bar = ensureLegend();
    bar.innerHTML = parts.join('');
    bar.style.display = 'flex';
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
      if (String(all[i].key) === String(key)) target = resolveElements(all[i])[0] || null;
    }
    if (!target) target = queryAll(key)[0] || null;
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    run();
    setTimeout(function () { focusKey = null; run(); }, 2600);
  }

  window.addEventListener('message', function (e) {
    const msg = e.data;
    if (!msg || e.source !== window.parent) return;
    if (msg.type === 'hms-diff-focus') focusEntry(msg.key);
    if (msg.type === 'hms-review-highlight') setData(msg);
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
    if (booted) return;
    booted = true;
    run();
    // The templates paint through React after first paint, and images resize the
    // boxes as they load, so the overlay is redrawn on anything that moves.
    new MutationObserver(onMutations).observe(document.body, { childList: true, subtree: true });
    // Capture phase: the hero and the page body scroll in their own containers,
    // and a scroll event on those does not bubble to window.
    window.addEventListener('scroll', scheduleRun, { passive: true, capture: true });
    window.addEventListener('resize', scheduleRun);
    window.addEventListener('load', scheduleRun);
  }

  /* Tell the review panel what was outlined, so it can say so beside the After
     label — an outline that silently fails to appear looks exactly like a
     student who changed nothing. */
  let lastStatus = '';
  function reportStatus(outlined, total) {
    const key = outlined + '/' + total;
    if (key === lastStatus || window.parent === window) return;
    lastStatus = key;
    window.parent.postMessage({ type: 'hms-review-highlight-status', outlined: outlined, total: total }, '*');
  }

  setData(window.__HMS_REVIEW_HIGHLIGHT__);
  // Ask the panel for its Changes list: it may have missed this page's load.
  if (window.parent !== window) window.parent.postMessage({ type: 'hms-review-highlight-ready' }, '*');
})();
