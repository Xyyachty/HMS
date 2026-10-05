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
  // 'branding', 'home', 'promos', 'partners', 'team', 'footer' or 'highlights': the page check run for that task. See run().
  let stockReview = null;
  let booted = false;

  function setData(data) {
    added = (data && Array.isArray(data.added)) ? data.added : [];
    modified = (data && Array.isArray(data.modified)) ? data.modified : [];
    // Either source can turn it on; neither turns it back off.
    stockReview = stockReview || (data && data.stock_review) || null;
    if (!added.length && !modified.length && !stockReview) return;
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
    // The name the site shows untouched: the approved concept's, else the stock one.
    const stockName = normalise(typeof site.hotelDefaults === 'function'
      ? site.hotelDefaults().name
      : (site.DEFAULT_BRAND_NAME || 'SPC HOTEL'));
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

  /* Customize the Home Page: the five slides, the hero's three lines of text and
     its two button labels.
     The templates mark each slide that is not its stock photograph, and give
     each hero line the text it shows when untouched (data-hms-stock) — the
     approved concept's words where there are some, else the template's. */
  function homeChanges() {
    const hero = '[data-hms-section="hero"]';
    const out = [];
    const slides = queryAll(hero + ' [data-hms-slide-changed]').length;
    const band = queryAll(hero + ' .hero-bg, ' + hero + ' .hero-img')[0];
    // One box for the band the photographs rotate in, counted once per slide.
    if (slides && band) out.push({ el: band, count: slides });
    queryAll(hero + ' [data-hms-stock]').forEach(function (el) {
      // innerText, not textContent: the heading's two lines are block spans
      // with no space between them in the markup.
      if (normalise(el.innerText || el.textContent) !== normalise(el.getAttribute('data-hms-stock'))) {
        out.push({ el: el, count: 1 });
      }
    });
    styledIn(hero).forEach(function (el) { out.push({ el: el, count: 1 }); });
    return out;
  }

  // Entry fields that are not a visible change. Kept in step with
  // TemplateDiff::IGNORED_PROPERTIES, plus the text the checks judge.
  const NOT_STYLE = ['hmsId', 'page', 'freePosition', 'moveMode', 'keepFixed', 'position', 'text', 'value'];

  function savedEntries() {
    return (window.HMSTemplateEditor && window.HMSTemplateEditor.getCustomizations
      && window.HMSTemplateEditor.getCustomizations()) || window.__HMS_CUSTOMIZATIONS__ || {};
  }

  /* Restyled or moved in Design mode - a button given a new colour keeps its
     label, so the text check passes it. Any saved entry on an element inside
     the section carrying more than its text and bookkeeping is a change. */
  function styledIn(scope) {
    const saved = savedEntries();
    const out = [];
    Object.keys(saved).forEach(function (key) {
      const entry = saved[key];
      if (key.indexOf('__') === 0 || !entry || typeof entry !== 'object') return;
      const styled = Object.keys(entry).some(function (prop) {
        const value = entry[prop];
        return NOT_STYLE.indexOf(prop) === -1 && value != null && value !== '' && value !== false;
      });
      if (!styled) return;
      resolveElements({ key: key }).forEach(function (el) {
        if (el.closest(scope)) out.push(el);
      });
    });
    return out;
  }

  /* The check every stock-reviewed section shares: a picture that is not its
     stock photograph (data-hms-img-changed), a line whose text differs from the
     template's (data-hms-stock), and anything restyled in Design mode. */
  function sectionChanges(section) {
    const out = [];
    queryAll(section + ' img[data-hms-img-changed]').forEach(function (el) {
      out.push({ el: el, count: 1 });
    });
    queryAll(section + ' [data-hms-stock]').forEach(function (el) {
      if (normalise(el.innerText || el.textContent) !== normalise(el.getAttribute('data-hms-stock'))) {
        out.push({ el: el, count: 1 });
      }
    });
    styledIn(section).forEach(function (el) { out.push({ el: el, count: 1 }); });
    return out;
  }

  /* Customize Promos and Packages: the shared check, plus one thing. A listed
     promo only shows its offer and title, so an edited description or terms
     line of a promo not featured is boxed on its row in the list instead. */
  function promosChanges() {
    const section = '[data-hms-section="promos"]';
    const out = sectionChanges(section);
    const saved = savedEntries();
    Object.keys(saved).forEach(function (key) {
      const m = /data-hms-category="(promo-[\w-]+?)-(?:offer|title|desc|terms)"/.exec(key);
      if (!m || resolveElements({ key: key }).length) return;
      queryAll(section + ' [data-hms-promo="' + cssEscapeSafe(m[1]) + '"]').forEach(function (el) {
        out.push({ el: el, count: 1 });
      });
    });
    return out;
  }

  /* Customize Partner Brands: the template marks each card that is new or
     changed (a new name or a logo) and says how many of its own brands were
     removed. Headings and restyles are judged as on the other sections. */
  function partnersChanges() {
    const section = '[data-hms-section="partners"]';
    const out = [];
    queryAll(section + ' [data-hms-partner-state]').forEach(function (el) {
      out.push({ el: el, count: 1, type: el.getAttribute('data-hms-partner-state') === 'added' ? 'added' : 'modified' });
    });
    Array.prototype.push.apply(out, sectionChanges(section));
    const grid = queryAll(section + ' [data-hms-partners-removed]')[0];
    removedCount = grid ? (parseInt(grid.getAttribute('data-hms-partners-removed'), 10) || 0) : 0;
    return out;
  }

  /* Customize Hotel Highlights: the same reading as the partner strip, on the
     Home page's Selected Highlights and on the Highlights page alike. Each
     highlight the team made is outlined as added, a stock one with a new
     photo or new words as changed, and the legend counts the ones removed. */
  function highlightsChanges() {
    const out = [];
    queryAll('[data-hms-exp-state]').forEach(function (el) {
      out.push({ el: el, count: 1, type: el.getAttribute('data-hms-exp-state') === 'added' ? 'added' : 'modified' });
    });
    Array.prototype.push.apply(out, sectionChanges('[data-hms-section="highlights"]'));
    Array.prototype.push.apply(out, sectionChanges('[data-hms-highlights-header]'));
    const grid = queryAll('[data-hms-exps-removed]')[0];
    removedCount = grid ? (parseInt(grid.getAttribute('data-hms-exps-removed'), 10) || 0) : 0;
    return out;
  }

  /* The Housekeeping tasks, on the Amenities page. The header (HK TASK 1) is
     judged by its text like any section. The facilities (HK TASK 2-4) live in
     the database, so the faculty feed marks each one added or changed against
     the template's starting five and says how many of those were removed.
     HK TASK 5 reviews the whole page, so it gets both. */
  function amenityHeaderChanges() {
    return sectionChanges('[data-hms-amenities-header]');
  }

  function amenityCardChanges() {
    const out = [];
    queryAll('[data-hms-amenity-state]').forEach(function (el) {
      out.push({ el: el, count: 1, type: el.getAttribute('data-hms-amenity-state') === 'added' ? 'added' : 'modified' });
    });
    const grid = queryAll('[data-hms-amenities-removed]')[0];
    removedCount = grid ? (parseInt(grid.getAttribute('data-hms-amenities-removed'), 10) || 0) : 0;
    return out;
  }

  /* The Room Management tasks, on the Rooms page. The header (RM TASK 1) is
     judged by its text. Categories and rooms live in the database, so the
     faculty feed marks each category tab - and, on Template 1, its card - with
     what changed against its starting slot, and each room added after the
     starting set or given a photo. A category counts once, on its tab; its
     card is boxed beside it without being counted twice. */
  function roomCategoryFlags(flags, type) {
    const out = [];
    flags.forEach(function (flag) {
      queryAll('.tab-btn[data-hms-cat-' + flag + ']').forEach(function (el) {
        out.push({ el: el, count: 1, type: type });
      });
      queryAll('[data-hms-cat-card][data-hms-cat-' + flag + ']').forEach(function (el) {
        out.push({ el: el, count: 0, type: type });
      });
    });
    return out;
  }

  function roomChanges(check) {
    if (check === 'rooms-header') return sectionChanges('[data-hms-rooms-header]');
    if (check === 'room-categories') {
      const bar = queryAll('[data-hms-cats-removed]')[0];
      removedCount = bar ? (parseInt(bar.getAttribute('data-hms-cats-removed'), 10) || 0) : 0;
      return roomCategoryFlags(['added'], 'added').concat(roomCategoryFlags(['renamed'], 'modified'));
    }
    if (check === 'room-details') return roomCategoryFlags(['renamed', 'details'], 'modified');
    if (check === 'room-photos') {
      return roomCategoryFlags(['photos'], 'modified').concat(queryAll('[data-hms-room-photos]').map(function (el) {
        return { el: el, count: 1 };
      }));
    }
    return queryAll('[data-hms-room-added]').map(function (el) {
      return { el: el, count: 1, type: 'added' };
    });
  }

  /* The Restaurant tasks, on the Restaurant page. The first section (RS TASK
     1) is judged by its words, its picture and its plate's outline. Dishes
     and courses live in the database, so the faculty feed marks each dish
     added or changed against the house menu, says which courses were added
     or renamed, and how many dishes and courses were removed. A dish shown in
     Best Seller is also on the menu below it; it is counted in Best Seller
     for RS TASK 2 and on the menu for RS TASK 4, never twice in one. */
  function restaurantChanges(check) {
    const out = [];
    let removed = 0;
    const take = function (list) { Array.prototype.push.apply(out, list); };
    const cardType = function (el) {
      return el.getAttribute('data-hms-menu-state') === 'added' ? 'added' : 'modified';
    };
    if (check === 'restaurant-intro' || check === 'restaurant-all') {
      take(sectionChanges('[data-hms-restaurant-hero]'));
      take(queryAll('[data-hms-restaurant-hero] [data-hms-shape-changed]').map(function (el) {
        return { el: el, count: 1 };
      }));
    }
    if (check === 'best-sellers' || check === 'restaurant-all') {
      take(sectionChanges('[data-hms-best-sellers]'));
      // In the full review a featured dish is counted where the menu lists it.
      take(queryAll('[data-hms-best-sellers] [data-hms-menu-state], [data-hms-best-sellers] [data-hms-best-picked]').map(function (el) {
        return { el: el, count: check === 'restaurant-all' ? 0 : 1, type: cardType(el) };
      }));
    }
    if (check === 'menu-categories' || check === 'restaurant-all') {
      const bar = queryAll('[data-hms-cats-removed]')[0];
      removed += bar ? (parseInt(bar.getAttribute('data-hms-cats-removed'), 10) || 0) : 0;
      take(roomCategoryFlags(['added'], 'added').concat(roomCategoryFlags(['renamed'], 'modified')));
    }
    if (check === 'menu-items' || check === 'restaurant-all') {
      removed += parseInt(window.__HMS_MENU_ITEMS_REMOVED__, 10) || 0;
      take(queryAll('[data-hms-menu-card][data-hms-menu-state]').filter(function (el) {
        return !el.closest('[data-hms-best-sellers]');
      }).map(function (el) {
        return { el: el, count: 1, type: cardType(el) };
      }));
    }
    removedCount = removed;
    return out;
  }

  // Brands or highlights removed: nothing left on the page to box, so the
  // legend counts them instead.
  let removedCount = 0;

  function stockChanges() {
    if (stockReview === 'branding') {
      return brandingChanges().map(function (el) { return { el: el, count: 1 }; });
    }
    if (stockReview === 'home') return homeChanges();
    if (stockReview === 'partners') return partnersChanges();
    if (stockReview === 'highlights') return highlightsChanges();
    if (/^room/.test(stockReview)) return roomChanges(stockReview);
    if (['restaurant-intro', 'best-sellers', 'menu-categories', 'menu-items', 'restaurant-all'].indexOf(stockReview) !== -1) {
      return restaurantChanges(stockReview);
    }
    if (stockReview === 'amenities-header') return amenityHeaderChanges();
    if (stockReview === 'amenities') return amenityCardChanges();
    if (stockReview === 'amenities-all') return amenityHeaderChanges().concat(amenityCardChanges());
    // Customize Our Team and Customize the Footer: the shared check alone.
    if (stockReview === 'team' || stockReview === 'footer') {
      return sectionChanges('[data-hms-section="' + stockReview + '"]');
    }
    return stockReview === 'promos' ? promosChanges() : [];
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

    /* The branding and Home Page tasks are judged against the stock template,
       so their change list holds every edit the team ever made to Home, and
       boxing those buried what the task is about. For those tasks the page
       check below is the whole answer. */
    if (!stockReview) {
      added.forEach(function (entry) { if (paint(entry, 'added')) addedCount++; });
      modified.forEach(function (entry) { if (paint(entry, 'modified')) modifiedCount++; });
    }

    if (stockReview) {
      try {
        stockChanges().forEach(function (change) {
          if (drawn.has(change.el)) return;
          const type = change.type || 'modified';
          box(change.el, type, null);
          if (type === 'added') addedCount += change.count;
          else modifiedCount += change.count;
        });
      } catch (e) { /* the diff outlines above still stand */ }
    }

    const parts = [];
    const swatch = '<i style="display:inline-block;width:8px;height:8px;border-radius:2px;background:' + COLORS.added + '"></i> ';
    if (addedCount) parts.push('<span>' + swatch + addedCount + ' added</span>');
    if (modifiedCount) parts.push('<span>' + swatch + modifiedCount + ' changed</span>');
    if (removedCount) parts.push('<span>' + removedCount + ' removed</span>');
    reportStatus(addedCount + modifiedCount + removedCount, added.length + modified.length + (stockReview ? 1 : 0));

    // Changes exist but none of them is on this page: say so, rather than
    // leaving faculty to wonder whether the outlines failed.
    if (!parts.length) parts.push('<span>No changes on this page</span>');
    const bar = ensureLegend();
    bar.innerHTML = parts.join('');
    bar.style.display = 'flex';
  }

  /* A throttle, not a debounce. The hero carousel and the other live parts of
     the page change the DOM several times a second, and a debounce restarted on
     every change never fired: the only pass that ran was the one at load,
     before React had drawn anything, so the review said "No changes on this
     page" over a page full of them. */
  let timer = null;
  function scheduleRun() {
    if (timer) return;
    timer = setTimeout(function () { timer = null; run(); }, 150);
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
    if (!msg || (e.source !== window.parent && e.origin !== window.location.origin)) return;
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
