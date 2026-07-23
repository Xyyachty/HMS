/**
 * HMS Template Editor — Canva-like inline editor inside Template 1 / Template 2.
 * Design mode: select, move, resize, style, add/delete/duplicate. Preview: live site only.
 */
(function () {
  'use strict';

  const STYLE_PROPS = [
    'color', 'background-color', 'font-family', 'font-weight', 'font-style',
    'text-decoration', 'font-size', 'text-align', 'line-height', 'letter-spacing',
    'background-image', 'background-size', 'background-position', 'background-repeat',
    'padding', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
    'margin', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
    'border', 'border-radius', 'box-shadow', 'opacity',
    'position', 'top', 'left', 'right', 'bottom', 'width', 'height', 'max-width',
    'min-width', 'min-height', 'z-index', 'transform', 'display', 'overflow',
  ];

  const USER_KEY = '__userElements';
  const DELETED_KEY = '__deleted';
  const SITE_CONTENT_KEYS = ['__navLinks', '__rooms', '__menus', '__cardImages'];

  let designMode = false;
  let selectedEl = null;
  let customizations = normalizeCustomizations(window.__HMS_CUSTOMIZATIONS__ || {});
  let canEdit = window.__HMS_CAN_EDIT__ === true;
  let editablePages = Array.isArray(window.__HMS_EDITABLE_PAGES__)
    ? window.__HMS_EDITABLE_PAGES__.slice()
    : null; // null = no page lock (legacy)
  let currentPage = window.__HMS_CURRENT_PAGE__ || 'home';
  let applying = false;
  let reapplyTimer = null;
  let fileInput = null;
  let dragState = null;
  let resizeState = null;
  let suppressNextClick = false;
  let handlesEl = null;
  let guideXEl = null;
  let guideYEl = null;
  let userCanvas = null;
  let zCounter = 10;
  let freePosSheetEl = null;
  const originalStates = new Map();
  const undoStack = [];
  const redoStack = [];
  const HISTORY_LIMIT = 50;

  function cloneCustomizations(value) {
    return JSON.parse(JSON.stringify(normalizeCustomizations(value || {})));
  }

  function publishHistoryState() {
    postToParent({
      type: 'history-state',
      canUndo: undoStack.length > 0,
      canRedo: redoStack.length > 0,
    });
  }

  function beginHistoryStep() {
    undoStack.push(cloneCustomizations(customizations));
    if (undoStack.length > HISTORY_LIMIT) undoStack.shift();
    redoStack.length = 0;
    publishHistoryState();
  }

  function getCurrentPage() {
    return window.__HMS_CURRENT_PAGE__ || currentPage || 'home';
  }

  function canEditCurrentPage() {
    if (!canEdit) return false;
    if (!Array.isArray(editablePages)) return true;
    if (editablePages.length === 0) return false;
    return editablePages.indexOf(getCurrentPage()) !== -1;
  }

  function blockEditToast() {
    const pages = Array.isArray(editablePages) && editablePages.length
      ? editablePages.join(', ')
      : 'your assigned pages';
    postToParent({
      type: 'edit-blocked',
      page: getCurrentPage(),
      message: 'You can only redesign: ' + pages + '. Teammates still see your synced work.',
    });
  }

  function normalizeCustomizations(raw) {
    const c = Object.assign({}, raw || {});
    if (!Array.isArray(c[USER_KEY])) c[USER_KEY] = [];
    if (!Array.isArray(c[DELETED_KEY])) c[DELETED_KEY] = [];
    return c;
  }

  function cssEscape(value) {
    if (window.CSS && CSS.escape) return CSS.escape(value);
    return String(value).replace(/([^\w-])/g, '\\$1');
  }

  function uid(prefix) {
    return (prefix || 'hms') + '-' + Math.random().toString(36).slice(2, 10);
  }

  function getSelector(el) {
    if (!el || el === document.documentElement) return 'html';
    if (el === document.body) return 'body';
    // User-added free elements keep a stable runtime id.
    if (el.hasAttribute('data-hms-user')) {
      const hmsId = ensureStableId(el);
      return '[data-hms-id="' + hmsId + '"]';
    }
    // Template elements must use structural selectors so styles survive refresh
    // (random data-hms-id attributes are wiped when React remounts the DOM).
    return getStructuralSelector(el);
  }

  function getStructuralSelector(el) {
    if (!el || el === document.documentElement) return 'html';
    if (el === document.body) return 'body';
    if (el.id && !String(el.id).startsWith('hms-') && el.id !== 'root') {
      return '#' + cssEscape(el.id);
    }

    const parts = [];
    let node = el;
    while (node && node.nodeType === 1 && node !== document.body) {
      if (node.id === 'root') {
        parts.unshift('#root');
        break;
      }
      if (node.id && !String(node.id).startsWith('hms-')) {
        parts.unshift('#' + cssEscape(node.id));
        break;
      }
      let part = node.tagName.toLowerCase();
      const parent = node.parentElement;
      if (parent) {
        const ofType = Array.from(parent.children).filter((c) => c.tagName === node.tagName);
        if (ofType.length > 1) {
          part += ':nth-of-type(' + (ofType.indexOf(node) + 1) + ')';
        }
      }
      parts.unshift(part);
      node = parent;
    }
    return parts.join(' > ');
  }

  function ensureStableId(el) {
    if (!el) return '';
    let id = el.getAttribute('data-hms-id');
    if (!id) {
      id = uid('el');
      el.setAttribute('data-hms-id', id);
    }
    return id;
  }

  function isEditorChrome(el) {
    return !!(el && (
      el.id === 'hms-editor-ui'
      || el.closest('#hms-editor-ui')
      || el.closest('#hms-editor-file')
      || el.closest('#hms-hotel-auth-bar')
      || el.closest('#hms-section-rail')
      || el.closest('[data-hms-no-edit]')
      || el.hasAttribute('data-hms-no-edit')
      || el.hasAttribute('data-hms-spacer')
      || el.tagName === 'HMS-SPACER'
    ));
  }

  function isLayoutContainer(el) {
    return !!(el && ['DIV', 'SECTION', 'HEADER', 'FOOTER', 'NAV', 'ARTICLE', 'MAIN'].includes(el.tagName));
  }

  function hasEditableBackground(el) {
    if (!el) return false;
    const cs = window.getComputedStyle(el);
    if (cs.backgroundImage && cs.backgroundImage !== 'none') return true;
    const bg = cs.backgroundColor;
    return !!(bg && bg !== 'transparent' && bg !== 'rgba(0, 0, 0, 0)');
  }

  function isInlineTextTag(tag) {
    return ['SPAN', 'EM', 'STRONG', 'SMALL', 'I', 'B', 'U', 'LABEL'].includes(tag);
  }

  function isBlockTextTag(tag) {
    return /^H[1-6]$/.test(tag) || ['P', 'BUTTON', 'A', 'LI', 'DIV', 'LABEL'].includes(tag);
  }

  /**
   * Prefer moving/styling the whole heading/paragraph instead of nested spans.
   * Selecting only "Where Elegance" was splitting the hero title and misplacing handles.
   */
  function resolveEditableRoot(el) {
    if (!el || el.nodeType !== 1) return el;
    if (el.hasAttribute('data-hms-user') || el.hasAttribute('data-hms-bg-target') || el.hasAttribute('data-hms-section') || el.hasAttribute('data-hms-move-root')) {
      return el;
    }
    if (el.classList.contains('nav-bar') || el.classList.contains('hero-bg')) return el;

    // Prefer whole header/nav when clicking brand or nav links.
    const nav = el.closest && el.closest('.nav-bar');
    if (nav && (el.tagName === 'SPAN' || el.tagName === 'BUTTON' || el.tagName === 'A' || el.tagName === 'I')) {
      // Keep individual link text editable when it's clearly a nav-link label;
      // otherwise select the whole header bar.
      if (el.classList.contains('nav-link') || (el.closest && el.closest('.nav-link'))) {
        return el.classList.contains('nav-link') ? el : el.closest('.nav-link');
      }
      if (el.closest('.nav-links-desktop') && (el.tagName === 'BUTTON' || el.tagName === 'A')) {
        return el;
      }
      // Brand / empty header chrome → whole nav
      if (!el.closest('.nav-links-desktop') || el.tagName === 'SPAN') {
        return nav;
      }
    }

    let node = el;
    if (isInlineTextTag(node.tagName) || node.tagName === 'SPAN') {
      const moveRoot = node.closest('[data-hms-move-root]');
      if (moveRoot) return moveRoot;
      let parent = node.parentElement;
      while (parent && parent !== document.body) {
        if (isEditorChrome(parent) || parent.hasAttribute('data-hms-no-edit')) break;
        if (/^H[1-6]$/.test(parent.tagName) || parent.tagName === 'P' || parent.tagName === 'BUTTON' || parent.tagName === 'A') {
          return parent;
        }
        if (parent.hasAttribute('data-hms-user') || parent.hasAttribute('data-hms-move-root')) return parent;
        parent = parent.parentElement;
      }
    }
    return node;
  }

  function getVisualRect(el) {
    if (!el) return { left: 0, top: 0, right: 0, bottom: 0, width: 0, height: 0 };
    const box = el.getBoundingClientRect();
    let left = box.left;
    let top = box.top;
    let right = box.right;
    let bottom = box.bottom;
    try {
      const range = document.createRange();
      range.selectNodeContents(el);
      const rects = range.getClientRects();
      for (let i = 0; i < rects.length; i++) {
        const cr = rects[i];
        if (!cr.width && !cr.height) continue;
        left = Math.min(left, cr.left);
        top = Math.min(top, cr.top);
        right = Math.max(right, cr.right);
        bottom = Math.max(bottom, cr.bottom);
      }
    } catch (e) { /* ignore */ }
    return {
      left: left,
      top: top,
      right: right,
      bottom: bottom,
      width: Math.max(0, right - left),
      height: Math.max(0, bottom - top),
    };
  }

  function isEditableTarget(el, allowContainers) {
    if (!el || el === document.body || el === document.documentElement) return false;
    if (isEditorChrome(el)) return false;
    if (el.id === 'hms-user-canvas' || el.id === 'hms-section-rail') return false;
    if (el.closest('[data-hms-no-edit]')) return false;

    const tag = el.tagName;
    if (['SCRIPT', 'STYLE', 'LINK', 'META', 'NOSCRIPT', 'HTML', 'HEAD'].includes(tag)) return false;

    if (el.hasAttribute('data-hms-user')) return true;

    if (el.classList.contains('nav-bar') || el.hasAttribute('data-hms-bg-target') || el.hasAttribute('data-hms-section')) {
      return true;
    }

    if (tag === 'IMG' || tag === 'BUTTON' || tag === 'A' || tag === 'I' || tag === 'SVG' || tag === 'INPUT' || tag === 'TEXTAREA') return true;
    if (/^H[1-6]$/.test(tag) || ['P', 'SPAN', 'EM', 'STRONG', 'SMALL', 'LABEL', 'LI', 'UL', 'OL'].includes(tag)) return true;
    if (isLayoutContainer(el)) {
      if (allowContainers) return true;
      return hasEditableBackground(el);
    }
    return false;
  }

  function findEditable(start, allowContainers) {
    let el = start;
    while (el && el !== document.body) {
      if (isEditorChrome(el) || el.id === 'hms-user-canvas' || el.id === 'hms-section-rail') {
        el = el.parentElement;
        continue;
      }
      // Skip locked tool chrome, but keep walking so the parent header/nav stays selectable.
      if (el.hasAttribute('data-hms-no-edit')) {
        el = el.parentElement;
        continue;
      }
      const hit = isEditableTarget(el, allowContainers === true);
      if (hit === true) return resolveEditableRoot(el);
      if (hit && hit.nodeType === 1) return resolveEditableRoot(hit);
      el = el.parentElement;
    }
    return null;
  }

  function findEditableAtPoint(event) {
    // Prefer topmost painted element (fixes header clicks blocked by caret hitting content underneath).
    if (typeof document.elementsFromPoint === 'function') {
      const stack = document.elementsFromPoint(event.clientX, event.clientY) || [];
      for (let i = 0; i < stack.length; i++) {
        const node = stack[i];
        if (!node || node === document.documentElement || node === document.body) continue;
        if (isEditorChrome(node)) continue;
        if (node.id === 'hms-user-canvas' || node.id === 'hms-section-rail') continue;

        if (event.altKey) {
          let container = node;
          while (container && container !== document.body) {
            if (container.hasAttribute('data-hms-no-edit')) {
              container = container.parentElement;
              continue;
            }
            if (isLayoutContainer(container) && !isEditorChrome(container)) {
              return resolveEditableRoot(container) || container;
            }
            container = container.parentElement;
          }
        }

        const hit = findEditable(node, false);
        if (hit) return hit;
      }
    }

    let textParent = null;
    if (document.caretRangeFromPoint) {
      const range = document.caretRangeFromPoint(event.clientX, event.clientY);
      const node = range && range.startContainer;
      textParent = node && node.nodeType === Node.TEXT_NODE ? node.parentElement : null;
    } else if (document.caretPositionFromPoint) {
      const position = document.caretPositionFromPoint(event.clientX, event.clientY);
      const node = position && position.offsetNode;
      textParent = node && node.nodeType === Node.TEXT_NODE ? node.parentElement : null;
    }

    if (event.altKey) {
      let container = textParent || event.target;
      while (container && container !== document.body) {
        if (container.hasAttribute('data-hms-no-edit')) {
          container = container.parentElement;
          continue;
        }
        if (isLayoutContainer(container) && !isEditorChrome(container)) {
          return resolveEditableRoot(container) || container;
        }
        container = container.parentElement;
      }
    }

    const textTarget = findEditable(textParent, false);
    if (textTarget && textTarget !== document.body) return textTarget;
    return findEditable(event.target, false);
  }

  function findEditableParent(el) {
    let parent = el && el.parentElement;
    while (parent && parent !== document.body) {
      if (isEditorChrome(parent)) return null;
      const hit = isEditableTarget(parent, true);
      if (hit === true) return parent;
      if (hit && hit.nodeType === 1 && hit !== el) return hit;
      parent = parent.parentElement;
    }
    return null;
  }

  function describeElement(el) {
    if (el.getAttribute('data-hms-type')) {
      const t = el.getAttribute('data-hms-type');
      return t.charAt(0).toUpperCase() + t.slice(1);
    }
    const tag = el.tagName.toLowerCase();
    if (tag === 'img') return 'Image';
    if (tag === 'button' || (tag === 'a' && el.getAttribute('role') === 'button')) return 'Button';
    if (tag === 'a') return 'Link';
    if (tag === 'input' || tag === 'textarea') return 'Text field';
    if (tag === 'i' || el.querySelector(':scope > i.fa, :scope > i.fas, :scope > i.far')) return 'Icon';
    if (/^h[1-6]$/.test(tag)) return 'Heading (' + tag.toUpperCase() + ')';
    if (['p', 'span', 'em', 'strong', 'small', 'label', 'li'].includes(tag)) return 'Text';
    const bg = window.getComputedStyle(el).backgroundImage;
    if (bg && bg !== 'none') return 'Background / Media';
    if (tag === 'div' || tag === 'section') return 'Container';
    return tag.toUpperCase() + ' element';
  }

  function postToParent(payload) {
    if (window.parent && window.parent !== window) {
      window.parent.postMessage(Object.assign({ source: 'hms-template' }, payload), '*');
    }
  }

  function notifyChanged() {
    postToParent({ type: 'customizations-changed', customizations: customizations });
  }

  function rgbToHex(rgb) {
    if (!rgb || rgb === 'transparent' || rgb === 'rgba(0, 0, 0, 0)') return 'transparent';
    if (rgb.startsWith('#')) return rgb;
    const m = rgb.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/);
    if (!m) return rgb;
    return '#' + [m[1], m[2], m[3]].map((n) => ('0' + parseInt(n, 10).toString(16)).slice(-2)).join('');
  }

  function ensureUserCanvas() {
    if (getComputedStyle(document.body).position === 'static') {
      document.body.style.position = 'relative';
    }
    if (userCanvas && document.body.contains(userCanvas)) return userCanvas;
    userCanvas = document.getElementById('hms-user-canvas');
    if (!userCanvas) {
      userCanvas = document.createElement('div');
      userCanvas.id = 'hms-user-canvas';
      document.body.appendChild(userCanvas);
    }
    return userCanvas;
  }

  function isFreePosition(el) {
    if (!el) return false;
    if (el.hasAttribute('data-hms-user') || el.getAttribute('data-hms-free-position') === '1') return true;
    const key = el.getAttribute('data-edit-id') || getSelector(el);
    return !!(customizations[key] && customizations[key].freePosition === true);
  }

  function ensureGuides() {
    if (guideXEl && document.body.contains(guideXEl)) return;
    guideXEl = document.createElement('div');
    guideXEl.className = 'hms-smart-guide vertical';
    guideYEl = document.createElement('div');
    guideYEl.className = 'hms-smart-guide horizontal';
    document.body.appendChild(guideXEl);
    document.body.appendChild(guideYEl);
  }

  function hideSmartGuides() {
    if (guideXEl) guideXEl.style.display = 'none';
    if (guideYEl) guideYEl.style.display = 'none';
  }

  function ensureHandles() {
    if (handlesEl && document.body.contains(handlesEl)) return handlesEl;
    handlesEl = document.createElement('div');
    handlesEl.id = 'hms-editor-ui';
    handlesEl.innerHTML = [
      '<div class="hms-sel-box"></div>',
      '<button type="button" class="hms-move-handle" title="Drag to move (Alt disables snapping)">',
      '<span aria-hidden="true">✥</span> Move</button>',
      '<div class="hms-handle nw" data-dir="nw"></div>',
      '<div class="hms-handle n" data-dir="n"></div>',
      '<div class="hms-handle ne" data-dir="ne"></div>',
      '<div class="hms-handle e" data-dir="e"></div>',
      '<div class="hms-handle se" data-dir="se"></div>',
      '<div class="hms-handle s" data-dir="s"></div>',
      '<div class="hms-handle sw" data-dir="sw"></div>',
      '<div class="hms-handle w" data-dir="w"></div>',
    ].join('');
    document.body.appendChild(handlesEl);

    handlesEl.querySelectorAll('.hms-handle').forEach((h) => {
      h.addEventListener('mousedown', onResizeStart);
    });
    handlesEl.querySelector('.hms-move-handle').addEventListener('mousedown', onMoveHandleDown);
    return handlesEl;
  }

  function updateHandles(rectOverride) {
    const el = (dragState && dragState.el) || (resizeState && resizeState.el) || selectedEl;
    if (!designMode || !el) {
      if (handlesEl) handlesEl.style.display = 'none';
      return;
    }
    const ui = ensureHandles();
    const r = rectOverride || getVisualRect(el);
    if (!rectOverride && r.width < 2 && r.height < 2) {
      ui.style.display = 'none';
      return;
    }
    const width = Math.max(8, r.width || 0);
    const height = Math.max(8, r.height || 0);
    const left = r.left;
    const top = r.top;
    const right = r.right != null ? r.right : (left + width);
    const bottom = r.bottom != null ? r.bottom : (top + height);
    ui.style.display = 'block';
    const box = ui.querySelector('.hms-sel-box');
    box.style.left = left + 'px';
    box.style.top = top + 'px';
    box.style.width = width + 'px';
    box.style.height = height + 'px';
    const moveHandle = ui.querySelector('.hms-move-handle');
    moveHandle.style.display = 'flex';
    moveHandle.style.left = Math.max(8, Math.min(window.innerWidth - 80, left + width / 2)) + 'px';
    moveHandle.style.top = Math.max(28, top) + 'px';

    const corners = {
      nw: [left, top], n: [left + width / 2, top], ne: [right, top],
      e: [right, top + height / 2], se: [right, bottom], s: [left + width / 2, bottom],
      sw: [left, bottom], w: [left, top + height / 2],
    };
    Object.keys(corners).forEach((dir) => {
      const h = ui.querySelector('.hms-handle.' + dir);
      if (!h) return;
      h.style.display = 'block';
      h.style.left = (corners[dir][0] - 5) + 'px';
      h.style.top = (corners[dir][1] - 5) + 'px';
    });
  }

  function setPosImportant(el, prop, value) {
    if (!el) return;
    if (value == null || value === '') el.style.removeProperty(prop);
    else el.style.setProperty(prop, value, 'important');
  }

  function ensureFreePosSheet() {
    if (freePosSheetEl && freePosSheetEl.parentNode) return freePosSheetEl;
    freePosSheetEl = document.getElementById('hms-free-pos-sheet');
    if (!freePosSheetEl) {
      freePosSheetEl = document.createElement('style');
      freePosSheetEl.id = 'hms-free-pos-sheet';
      document.head.appendChild(freePosSheetEl);
    }
    return freePosSheetEl;
  }

  function parseTranslateValue(transform) {
    if (!transform || transform === 'none') return { x: 0, y: 0 };
    const s = String(transform);
    let m = s.match(/translate3d\(\s*([-\d.]+)px\s*,\s*([-\d.]+)px/i);
    if (m) return { x: parseFloat(m[1]) || 0, y: parseFloat(m[2]) || 0 };
    m = s.match(/translate\(\s*([-\d.]+)px\s*,\s*([-\d.]+)px\s*\)/i);
    if (m) return { x: parseFloat(m[1]) || 0, y: parseFloat(m[2]) || 0 };
    m = s.match(/translate\(\s*([-\d.]+)px\s*\)/i);
    if (m) return { x: parseFloat(m[1]) || 0, y: 0 };
    m = s.match(/matrix\(([^)]+)\)/i);
    if (m) {
      const parts = m[1].split(',').map(function (v) { return parseFloat(v.trim()); });
      return { x: parts[4] || 0, y: parts[5] || 0 };
    }
    return { x: 0, y: 0 };
  }

  function getElementTranslate(el) {
    if (!el) return { x: 0, y: 0 };
    const inline = el.style.getPropertyValue('transform');
    if (inline && inline !== 'none' && /translate|matrix/i.test(inline)) {
      return parseTranslateValue(inline);
    }
    return parseTranslateValue(window.getComputedStyle(el).transform);
  }

  function setElementTranslate(el, x, y) {
    if (!el) return;
    const tx = Math.round(x);
    const ty = Math.round(y);
    setPosImportant(el, 'transform', 'translate(' + tx + 'px, ' + ty + 'px)');
    el.setAttribute('data-hms-free-position', '1');
    el.setAttribute('data-hms-move-mode', 'transform');
  }

  /** Convert broken absolute/fixed free-pos back to in-flow + translate (keeps visual place). */
  function normalizeToTransformMove(el) {
    if (!el || el.hasAttribute('data-hms-user')) return getElementTranslate(el);
    const before = getVisualRect(el);
    const cs = window.getComputedStyle(el);
    const outOfFlow = cs.position === 'absolute' || cs.position === 'fixed';
    if (outOfFlow) {
      ['position', 'left', 'top', 'right', 'bottom', 'margin', 'margin-top', 'margin-left'].forEach(function (p) {
        el.style.removeProperty(p);
      });
      removeFlowSpacer(el);
    }
    el.style.removeProperty('transform');
    // Force reflow then measure natural in-flow box.
    void el.offsetWidth;
    const natural = getVisualRect(el);
    const tx = before.left - natural.left;
    const ty = before.top - natural.top;
    setElementTranslate(el, tx, ty);
    return { x: tx, y: ty };
  }

  function rebuildFreePosSheet() {
    const sheet = ensureFreePosSheet();
    const parts = [];
    Object.keys(customizations).forEach(function (id) {
      if (id === USER_KEY || id === DELETED_KEY || SITE_CONTENT_KEYS.indexOf(id) !== -1) return;
      const entry = customizations[id];
      if (!entry || entry.freePosition !== true) return;
      let sel = id;
      try {
        document.querySelector(sel);
      } catch (err) {
        return;
      }
      const decls = [];
      if (entry.moveMode === 'transform' || (entry.transform && !entry.keepFixed && entry.position !== 'fixed')) {
        if (entry.transform) decls.push('transform:' + entry.transform + ' !important');
      }
      if (entry.keepFixed || entry.position === 'fixed') {
        decls.push('position:fixed !important');
        if (entry.left) decls.push('left:' + entry.left + ' !important');
        if (entry.top) decls.push('top:' + entry.top + ' !important');
        decls.push('right:auto !important');
        decls.push('bottom:auto !important');
        decls.push('margin:0 !important');
      }
      if (entry['font-size']) decls.push('font-size:' + entry['font-size'] + ' !important');
      if (entry.width) decls.push('width:' + entry.width + ' !important');
      if (entry.height) decls.push('height:' + entry.height + ' !important');
      if (entry['max-width']) decls.push('max-width:' + entry['max-width'] + ' !important');
      if (entry['z-index']) decls.push('z-index:' + entry['z-index'] + ' !important');
      if (entry['white-space']) decls.push('white-space:' + entry['white-space'] + ' !important');
      if (!decls.length) return;
      parts.push(sel + '{' + decls.join(';') + '}');
    });
    sheet.textContent = parts.join('\n');
  }

  function clearNestedFreePositions(root) {
    if (!root) return;
    root.querySelectorAll('[data-hms-free-position="1"]').forEach((child) => {
      if (child === root) return;
      child.removeAttribute('data-hms-free-position');
      ['position', 'left', 'top', 'right', 'bottom', 'transform'].forEach((p) => {
        child.style.removeProperty(p);
      });
      const childKey = child.getAttribute('data-edit-id');
      if (childKey && customizations[childKey]) {
        const entry = customizations[childKey];
        delete entry.freePosition;
        delete entry.position;
        delete entry.left;
        delete entry.top;
        delete entry.right;
        delete entry.bottom;
        delete entry.transform;
      }
    });
  }

  function getFreePositionParent(el) {
    if (!el) return document.body;
    // Prefer a large section/main so moves aren't clamped back into a tiny text wrapper.
    const section = el.closest('[data-hms-section], .hero, .hero-split, main, [data-hms-page]');
    if (section) return section;
    if (el.classList.contains('nav-bar')) return document.body;
    let node = el.parentElement;
    while (node && node !== document.body) {
      if (node.id === 'root' || node === document.body) break;
      const cs = window.getComputedStyle(node);
      const w = node.getBoundingClientRect().width;
      // Skip tiny centered wrappers (e.g. max-width:760 hero text column).
      if (w >= Math.min(window.innerWidth * 0.7, 900) && cs.position !== 'static') {
        return node;
      }
      if (w >= Math.min(window.innerWidth * 0.7, 900)) {
        return node;
      }
      node = node.parentElement;
    }
    return el.parentElement || document.body;
  }

  function ensurePositioningContext(el) {
    const parent = getFreePositionParent(el);
    if (!parent || parent === document.documentElement) return document.body;
    if (parent !== document.body) {
      const pcs = window.getComputedStyle(parent);
      if (pcs.position === 'static') {
        parent.style.position = 'relative';
      }
    } else if (getComputedStyle(document.body).position === 'static') {
      document.body.style.position = 'relative';
    }
    return parent;
  }

  function commitFixedToAbsolute(el) {
    if (!el) return;
    const er = getVisualRect(el);
    const parent = ensurePositioningContext(el) || document.body;
    const pr = parent.getBoundingClientRect();
    el.style.removeProperty('transform');
    setPosImportant(el, 'position', 'absolute');
    setPosImportant(el, 'left', (er.left - pr.left + (parent.scrollLeft || 0)) + 'px');
    setPosImportant(el, 'top', (er.top - pr.top + (parent.scrollTop || 0)) + 'px');
    setPosImportant(el, 'right', 'auto');
    setPosImportant(el, 'bottom', 'auto');
    setPosImportant(el, 'margin', '0');
    el.setAttribute('data-hms-free-position', '1');
  }

  function clearSelection() {
    if (selectedEl) selectedEl.classList.remove('hms-edit-selected');
    selectedEl = null;
    document.querySelectorAll('.hms-edit-selected').forEach((n) => n.classList.remove('hms-edit-selected'));
    if (handlesEl) handlesEl.style.display = 'none';
  }

  function selectElement(el) {
    clearSelection();
    el = resolveEditableRoot(el);
    if (!el) {
      postToParent({ type: 'element-deselected' });
      return;
    }
    selectedEl = el;
    ensureStableId(el);
    el.classList.add('hms-edit-selected');
    const id = getSelector(el);
    el.setAttribute('data-edit-id', id);
    captureOriginalState(el, id);

    const cs = window.getComputedStyle(el);
    const isImg = el.tagName === 'IMG';
    const bgImage = cs.backgroundImage;
    const bgMatch = bgImage && bgImage !== 'none' ? bgImage.match(/url\(["']?(.*?)["']?\)/) : null;
    const z = parseInt(cs.zIndex, 10);
    if (!isNaN(z) && z > zCounter) zCounter = z;
    const editableParent = findEditableParent(el);

    postToParent({
      type: 'element-selected',
      id: id,
      label: describeElement(el),
      tag: el.tagName.toLowerCase(),
      canSelectParent: !!editableParent,
      parentLabel: editableParent ? describeElement(editableParent) : '',
      freePosition: isFreePosition(el),
      isUser: el.hasAttribute('data-hms-user'),
      text: isImg ? '' : (el.innerText || '').trim().slice(0, 500),
      html: isImg ? '' : el.innerHTML,
      src: isImg ? el.getAttribute('src') : (bgMatch ? bgMatch[1] : ''),
      isImage: isImg || !!bgMatch,
      isIcon: el.tagName === 'I' || !!el.querySelector('i.fa, i.fas, i.far, i.fab'),
      iconClass: el.tagName === 'I' ? el.className.replace(/\bhms-edit-selected\b/g, '').trim()
        : (el.querySelector('i') ? el.querySelector('i').className : ''),
      styles: {
        color: rgbToHex(cs.color),
        'background-color': rgbToHex(cs.backgroundColor),
        'font-family': cs.fontFamily,
        'font-weight': cs.fontWeight,
        'font-style': cs.fontStyle,
        'text-decoration': cs.textDecorationLine || cs.textDecoration,
        'font-size': cs.fontSize,
        'text-align': cs.textAlign,
        padding: cs.padding,
        margin: cs.margin,
        width: cs.width,
        height: cs.height,
        'z-index': cs.zIndex,
        'border-radius': cs.borderRadius,
        opacity: cs.opacity,
      },
    });
    updateHandles();
  }

  function selectParentElement() {
    if (!selectedEl) return;
    const parent = findEditableParent(selectedEl);
    if (parent) selectElement(parent);
  }

  function captureOriginalState(el, id) {
    if (!el || el.hasAttribute('data-hms-user')) return;
    const key = id || el.getAttribute('data-edit-id') || getSelector(el);
    if (!key || originalStates.has(key)) return;
    originalStates.set(key, {
      tag: el.tagName.toLowerCase(),
      style: el.getAttribute('style'),
      className: (el.getAttribute('class') || '').replace(/\bhms-edit-selected\b/g, '').trim(),
      innerHTML: el.innerHTML,
      src: el.tagName === 'IMG' ? el.getAttribute('src') : null,
      freePosition: el.getAttribute('data-hms-free-position'),
    });
  }

  function restoreOriginalStates() {
    originalStates.forEach((state, key) => {
      let el = findByKey(key);
      if (!el) return;

      if (el.tagName.toLowerCase() !== state.tag) {
        const replacement = document.createElement(state.tag);
        Array.from(el.attributes).forEach((attr) => {
          if (!['class', 'style'].includes(attr.name)) replacement.setAttribute(attr.name, attr.value);
        });
        el.replaceWith(replacement);
        el = replacement;
      }

      if (state.style == null) el.removeAttribute('style');
      else el.setAttribute('style', state.style);
      if (state.className) el.setAttribute('class', state.className);
      else el.removeAttribute('class');
      el.innerHTML = state.innerHTML;
      if (el.tagName === 'IMG') {
        if (state.src == null) el.removeAttribute('src');
        else el.setAttribute('src', state.src);
      }
      if (state.freePosition == null) el.removeAttribute('data-hms-free-position');
      else el.setAttribute('data-hms-free-position', state.freePosition);
      el.setAttribute('data-edit-id', key);
    });
  }

  function restoreHistorySnapshot(snapshot) {
    clearSelection();
    applying = true;
    restoreOriginalStates();
    customizations = cloneCustomizations(snapshot);
    applying = false;
    applyAllCustomizations();
    notifyChanged();
    postToParent({ type: 'element-deselected' });
    publishHistoryState();
  }

  function undo() {
    if (!undoStack.length) return;
    redoStack.push(cloneCustomizations(customizations));
    restoreHistorySnapshot(undoStack.pop());
  }

  function redo() {
    if (!redoStack.length) return;
    undoStack.push(cloneCustomizations(customizations));
    restoreHistorySnapshot(redoStack.pop());
  }

  function saveElementState(el) {
    if (!el) return;
    if (!canEditCurrentPage()) {
      blockEditToast();
      return;
    }
    if (el.hasAttribute('data-hms-user')) {
      persistUserElements();
      notifyChanged();
      return;
    }

    const id = el.getAttribute('data-edit-id') || getSelector(el);
    el.setAttribute('data-edit-id', id);
    const entry = customizations[id] || {};
    const cs = el.style;

    STYLE_PROPS.forEach((prop) => {
      const val = cs.getPropertyValue(prop);
      if (val) entry[prop] = val;
    });

    const hmsId = el.getAttribute('data-hms-id');
    if (hmsId) entry.hmsId = hmsId;
    entry.freePosition = isFreePosition(el);
    entry.moveMode = el.getAttribute('data-hms-move-mode') || entry.moveMode || '';
    if (el.getAttribute('data-hms-keep-fixed') === '1') entry.keepFixed = true;

    // Transform-move: drop absolute coords so React layout stays intact.
    if (entry.moveMode === 'transform') {
      const t = getElementTranslate(el);
      entry.transform = 'translate(' + Math.round(t.x) + 'px, ' + Math.round(t.y) + 'px)';
      delete entry.position;
      delete entry.left;
      delete entry.top;
      delete entry.right;
      delete entry.bottom;
      entry.freePosition = true;
    }

    if (el.tagName === 'IMG' && el.getAttribute('src')) {
      entry.src = el.getAttribute('src');
    } else if (cs.backgroundImage && cs.backgroundImage !== 'none') {
      entry['background-image'] = cs.backgroundImage;
    }

    if (el.tagName === 'I') {
      entry.iconClass = el.className.replace(/\bhms-edit-selected\b/g, '').trim();
    }

    if (!['IMG', 'I', 'SVG'].includes(el.tagName)) {
      if (el.childElementCount === 0 || el.getAttribute('data-hms-text') === '1') {
        entry.text = el.innerText;
      }
    }

    entry.page = getCurrentPage();
    customizations[id] = entry;
    rebuildFreePosSheet();
    notifyChanged();
  }

  function applyEntry(el, entry) {
    if (!el || !entry) return;
    if (entry.deleted || entry.display === 'none') {
      el.style.display = 'none';
      return;
    }
    STYLE_PROPS.forEach((prop) => {
      if (entry[prop] != null && entry[prop] !== '') {
        const important = entry.freePosition === true;
        el.style.setProperty(prop, entry[prop], important ? 'important' : '');
      }
    });
    if (entry.hmsId) el.setAttribute('data-hms-id', entry.hmsId);
    if (entry.freePosition === true || entry.position === 'absolute' || entry.position === 'fixed') {
      el.setAttribute('data-hms-free-position', '1');
      if (entry.moveMode === 'transform' || (entry.transform && !entry.keepFixed && entry.position !== 'fixed' && entry.position !== 'absolute')) {
        el.setAttribute('data-hms-move-mode', 'transform');
        // Prefer transform path — clear conflicting absolute placement.
        if (entry.transform) setPosImportant(el, 'transform', entry.transform);
        ['position', 'left', 'top', 'right', 'bottom'].forEach(function (p) {
          if (!entry.keepFixed) el.style.removeProperty(p);
        });
      } else if (entry.keepFixed || entry.position === 'fixed') {
        el.setAttribute('data-hms-keep-fixed', '1');
        ensurePositioningContext(el);
      } else {
        ensurePositioningContext(el);
      }
    } else {
      el.removeAttribute('data-hms-free-position');
      el.removeAttribute('data-hms-move-mode');
    }
    if (entry.src && el.tagName === 'IMG') el.setAttribute('src', entry.src);
    if (entry.iconClass && el.tagName === 'I') {
      const selected = el.classList.contains('hms-edit-selected');
      el.className = entry.iconClass;
      if (selected) el.classList.add('hms-edit-selected');
    }
    if (entry.text != null && !['IMG', 'I', 'SVG', 'INPUT', 'TEXTAREA'].includes(el.tagName)) {
      if (el.childElementCount === 0 || el.getAttribute('data-hms-text') === '1') {
        el.textContent = entry.text;
      } else {
        const walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, null);
        const textNode = walker.nextNode();
        if (textNode) textNode.nodeValue = entry.text;
      }
    }
  }

  function findByTextFingerprint(entry) {
    if (!entry || entry.text == null) return null;
    const page = entry.page || getCurrentPage();
    if (page && getCurrentPage() && page !== getCurrentPage()) return null;
    const needle = String(entry.text).trim();
    if (!needle) return null;
    const tags = ['H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'P', 'SPAN', 'BUTTON', 'A', 'LABEL', 'LI', 'DIV'];
    const nodes = Array.from(document.querySelectorAll(tags.join(',').toLowerCase()));
    for (let i = 0; i < nodes.length; i++) {
      const el = nodes[i];
      if (isEditorChrome(el) || el.hasAttribute('data-hms-user')) continue;
      if (el.childElementCount > 0 && el.getAttribute('data-hms-text') !== '1') continue;
      if ((el.innerText || '').trim() === needle) return el;
    }
    return null;
  }

  function findByKey(id) {
    if (!id || id === USER_KEY || id === DELETED_KEY) return null;
    try {
      let el = document.querySelector(id);
      if (el) return el;
    } catch (e) { /* ignore */ }
    const entry = customizations[id];
    if (entry && entry.hmsId) {
      try {
        const byHms = document.querySelector('[data-hms-id="' + cssEscape(entry.hmsId) + '"]');
        if (byHms) return byHms;
      } catch (e2) { /* ignore */ }
    }
    try {
      const byEdit = document.querySelector('[data-edit-id="' + id.replace(/"/g, '\\"') + '"]');
      if (byEdit) return byEdit;
    } catch (e3) { /* ignore */ }

    // Recover legacy saves that used ephemeral [data-hms-id="el-…"] keys.
    if (entry && String(id).indexOf('data-hms-id') !== -1) {
      return findByTextFingerprint(entry);
    }
    return null;
  }

  function persistUserElements() {
    const canvas = ensureUserCanvas();
    const page = getCurrentPage();
    const others = (customizations[USER_KEY] || []).filter((item) => (item.page || 'home') !== page);
    const list = [];
    canvas.querySelectorAll('[data-hms-user]').forEach((el) => {
      list.push({
        id: el.getAttribute('data-hms-id'),
        type: el.getAttribute('data-hms-type') || 'element',
        tag: el.tagName.toLowerCase(),
        page: page,
        html: el.innerHTML,
        outerHint: el.getAttribute('data-hms-type'),
        styles: STYLE_PROPS.reduce((acc, prop) => {
          const v = el.style.getPropertyValue(prop);
          if (v) acc[prop] = v;
          return acc;
        }, {}),
        src: el.tagName === 'IMG' ? el.getAttribute('src') : null,
        text: el.getAttribute('data-hms-text') === '1' ? el.textContent : null,
        className: el.className.replace(/\bhms-edit-selected\b/g, '').trim(),
      });
    });
    customizations[USER_KEY] = others.concat(list);
  }

  function renderUserElements() {
    const canvas = ensureUserCanvas();
    const page = getCurrentPage();
    const keepSelected = selectedEl && selectedEl.hasAttribute('data-hms-user')
      ? selectedEl.getAttribute('data-hms-id') : null;
    canvas.innerHTML = '';
    let restored = null;
    (customizations[USER_KEY] || []).forEach((item) => {
      const itemPage = item.page || 'home';
      if (itemPage !== page) return;
      const el = buildUserElement(item);
      canvas.appendChild(el);
      if (keepSelected && item.id === keepSelected) restored = el;
    });
    if (restored) {
      selectElement(restored);
    } else if (keepSelected) {
      clearSelection();
    }
  }

  function buildUserElement(item) {
    const type = item.type || 'text';
    let el;
    if (type === 'image') {
      el = document.createElement('img');
      el.src = item.src || 'https://placehold.co/320x200/1e293b/94a3b8?text=Image';
      el.alt = 'Image';
    } else if (type === 'button') {
      el = document.createElement('button');
      el.type = 'button';
      el.textContent = item.text || 'Button';
      el.setAttribute('data-hms-text', '1');
    } else if (type === 'input' || type === 'textfield') {
      el = document.createElement('input');
      el.type = 'text';
      el.placeholder = item.text || 'Text field';
    } else if (type === 'icon') {
      el = document.createElement('i');
      el.className = item.className || 'fas fa-star';
    } else if (type === 'card') {
      el = document.createElement('div');
      el.innerHTML = item.html || '<h3 data-hms-text="1">Card title</h3><p data-hms-text="1">Card description goes here.</p>';
    } else if (type === 'container') {
      el = document.createElement('div');
      el.innerHTML = item.html || '';
    } else {
      el = document.createElement(item.tag === 'h2' ? 'h2' : 'p');
      el.textContent = item.text || 'New text';
      el.setAttribute('data-hms-text', '1');
    }

    el.setAttribute('data-hms-user', '1');
    el.setAttribute('data-hms-type', type);
    el.setAttribute('data-hms-id', item.id || uid('user'));
    if (item.className && type !== 'icon') el.className = item.className;
    if (item.styles) {
      Object.keys(item.styles).forEach((prop) => el.style.setProperty(prop, item.styles[prop]));
    }
    if (!el.style.position) el.style.position = 'absolute';
    if (!el.style.left) el.style.left = item.styles?.left || '80px';
    if (!el.style.top) el.style.top = item.styles?.top || '120px';
    if (!el.style.zIndex) el.style.zIndex = String(++zCounter);
    return el;
  }

  function applyDeleted() {
    (customizations[DELETED_KEY] || []).forEach((entry) => {
      const id = typeof entry === 'string' ? entry : (entry && entry.id);
      if (!id) return;
      const el = findByKey(id);
      if (el) el.style.display = 'none';
    });
  }

  function applyAllCustomizations() {
    applying = true;
    let migrated = false;
    Object.keys(customizations).forEach((id) => {
      if (id === USER_KEY || id === DELETED_KEY || SITE_CONTENT_KEYS.indexOf(id) !== -1) return;
      let el = findByKey(id);
      if (!el) return;

      // Re-key legacy ephemeral [data-hms-id="el-…"] entries to structural selectors.
      let key = id;
      if (String(id).indexOf('data-hms-id') !== -1 && !el.hasAttribute('data-hms-user')) {
        const stable = getStructuralSelector(el);
        if (stable && stable !== id) {
          customizations[stable] = Object.assign({}, customizations[id], customizations[stable] || {});
          delete customizations[id];
          key = stable;
          migrated = true;
        }
      }

      captureOriginalState(el, key);
      el.setAttribute('data-edit-id', key);
      applyEntry(el, customizations[key]);
    });
    applyDeleted();
    renderUserElements();
    cleanOrphanSpacers();
    rebuildFreePosSheet();
    applying = false;
    updateHandles();
    if (migrated) notifyChanged();
  }

  function scheduleReapply() {
    if (applying) return;
    clearTimeout(reapplyTimer);
    reapplyTimer = setTimeout(function () {
      if (dragState || resizeState) return;
      applyAllCustomizations();
      if (designMode) syncSectionMode();
    }, 150);
  }

  function ensureFlowSpacer(el) {
    // Spacers break React-managed template DOM and scramble the page.
    // Transform-based moves keep elements in normal flow — no spacer needed.
    return null;
  }

  function removeFlowSpacer(el) {
    if (!el) return;
    const spacerId = el.getAttribute('data-hms-spacer-id');
    if (!spacerId) return;
    try {
      const spacer = document.querySelector('hms-spacer[data-hms-id="' + cssEscape(spacerId) + '"]');
      if (spacer && spacer.parentElement) spacer.parentElement.removeChild(spacer);
    } catch (e) { /* ignore */ }
    el.removeAttribute('data-hms-spacer-id');
  }

  function cleanOrphanSpacers() {
    document.querySelectorAll('hms-spacer').forEach((spacer) => {
      if (spacer.parentElement) spacer.parentElement.removeChild(spacer);
    });
    document.querySelectorAll('[data-hms-spacer-id]').forEach((el) => {
      el.removeAttribute('data-hms-spacer-id');
    });
  }

  function promoteToAbsolute(el) {
    if (!el) return;
    el = resolveEditableRoot(el) || el;
    clearNestedFreePositions(el);
    const cs = window.getComputedStyle(el);
    const isTextBlock = isBlockTextTag(el.tagName) || isInlineTextTag(el.tagName);
    const er = getVisualRect(el);
    const hasExplicitWidth = !!(el.style.width && el.style.width !== 'max-content' && el.style.width !== 'auto');

    // Fixed during drag uses viewport coords; keep sticky/fixed chrome in viewport space.
    if (cs.position === 'fixed' || cs.position === 'sticky') {
      el.setAttribute('data-hms-free-position', '1');
      setPosImportant(el, 'position', 'fixed');
      setPosImportant(el, 'left', er.left + 'px');
      setPosImportant(el, 'top', er.top + 'px');
      setPosImportant(el, 'right', 'auto');
      setPosImportant(el, 'bottom', 'auto');
      if (isTextBlock && !hasExplicitWidth) {
        setPosImportant(el, 'width', Math.max(24, er.width) + 'px');
        setPosImportant(el, 'height', 'auto');
      } else if (!el.style.width) {
        setPosImportant(el, 'width', er.width + 'px');
      }
      return;
    }

    const parent = ensurePositioningContext(el) || document.body;
    const pr = parent.getBoundingClientRect();
    ensureFlowSpacer(el);

    setPosImportant(el, 'position', 'absolute');
    setPosImportant(el, 'left', (er.left - pr.left + (parent.scrollLeft || 0)) + 'px');
    setPosImportant(el, 'top', (er.top - pr.top + (parent.scrollTop || 0)) + 'px');
    setPosImportant(el, 'right', 'auto');
    setPosImportant(el, 'bottom', 'auto');
    setPosImportant(el, 'margin', '0');
    if (isTextBlock) {
      if (!hasExplicitWidth) setPosImportant(el, 'width', Math.max(24, er.width) + 'px');
      setPosImportant(el, 'max-width', 'none');
      setPosImportant(el, 'height', 'auto');
    } else {
      setPosImportant(el, 'width', Math.min(er.width, pr.width || er.width) + 'px');
      setPosImportant(el, 'height', er.height + 'px');
      setPosImportant(el, 'max-width', '100%');
    }
    el.setAttribute('data-hms-free-position', '1');
    if (!el.style.zIndex || el.style.zIndex === 'auto') {
      setPosImportant(el, 'z-index', String(++zCounter));
    }
  }

  function onMoveHandleDown(e) {
    if (!designMode || !canEdit || !selectedEl) return;
    if (!canEditCurrentPage()) return;
    if (e.button !== 0) return;

    e.preventDefault();
    e.stopPropagation();
    const el = resolveEditableRoot(selectedEl) || selectedEl;
    selectedEl = el;
    const startRect = getVisualRect(el);
    dragState = {
      el: el,
      startX: e.clientX,
      startY: e.clientY,
      left: startRect.left,
      top: startRect.top,
      historyStarted: false,
      startRect: startRect,
      useFixed: true,
    };
  }

  function getSnapCandidates(el) {
    const xs = [0, window.innerWidth / 2, window.innerWidth - 1];
    const ys = [0, window.innerHeight / 2, window.innerHeight - 1];
    document.querySelectorAll('[data-edit-id], [data-hms-user], h1, h2, h3, h4, p, button, a, img').forEach((other) => {
      if (other === el || el.contains(other) || !other.getClientRects().length || isEditorChrome(other)) return;
      const r = other.getBoundingClientRect();
      xs.push(r.left, r.left + r.width / 2, r.right);
      ys.push(r.top, r.top + r.height / 2, r.bottom);
    });
    return { xs, ys };
  }

  function closestSnap(values, targets, threshold) {
    let best = null;
    values.forEach((value) => {
      targets.forEach((target) => {
        const delta = target - value;
        if (Math.abs(delta) <= threshold && (!best || Math.abs(delta) < Math.abs(best.delta))) {
          best = { delta, target };
        }
      });
    });
    return best;
  }

  function snapDragPosition(state, dx, dy, disableSnap) {
    let left = state.left + dx;
    let top = state.top + dy;
    if (disableSnap) {
      hideSmartGuides();
      return { left, top, dx: left - state.left, dy: top - state.top };
    }

    const r = state.startRect;
    const desiredX = [r.left + dx, r.left + dx + r.width / 2, r.right + dx];
    const desiredY = [r.top + dy, r.top + dy + r.height / 2, r.bottom + dy];
    const candidates = getSnapCandidates(state.el);
    const sx = closestSnap(desiredX, candidates.xs, 6);
    const sy = closestSnap(desiredY, candidates.ys, 6);

    if (sx) {
      left += sx.delta;
      guideXEl.style.left = sx.target + 'px';
      guideXEl.style.display = 'block';
    } else {
      left = Math.round(left / 8) * 8;
      if (guideXEl) guideXEl.style.display = 'none';
    }
    if (sy) {
      top += sy.delta;
      guideYEl.style.top = sy.target + 'px';
      guideYEl.style.display = 'block';
    } else {
      top = Math.round(top / 8) * 8;
      if (guideYEl) guideYEl.style.display = 'none';
    }
    return { left, top, dx: left - state.left, dy: top - state.top };
  }

  function onResizeStart(e) {
    if (!designMode || !canEdit || !selectedEl) return;
    e.preventDefault();
    e.stopPropagation();
    const el = resolveEditableRoot(selectedEl) || selectedEl;
    selectedEl = el;
    const r = getVisualRect(el);
    const cs = window.getComputedStyle(el);
    const isText = isBlockTextTag(el.tagName) || isInlineTextTag(el.tagName) || el.getAttribute('data-hms-text') === '1';
    resizeState = {
      el: el,
      dir: e.currentTarget.getAttribute('data-dir'),
      startX: e.clientX,
      startY: e.clientY,
      left: 0,
      top: 0,
      width: Math.max(8, r.width),
      height: Math.max(8, r.height),
      startFontSize: parseFloat(cs.fontSize) || 16,
      isText: isText,
      isCorner: false,
      historyStarted: false,
    };
    resizeState.isCorner = ['nw', 'ne', 'sw', 'se'].indexOf(resizeState.dir) !== -1;
  }

  function onPointerMove(e) {
    if (dragState) {
      const dx = e.clientX - dragState.startX;
      const dy = e.clientY - dragState.startY;
      if (!dragState.historyStarted) {
        if (Math.hypot(dx, dy) < 5) return;
        beginHistoryStep();
        const el = resolveEditableRoot(dragState.el) || dragState.el;
        dragState.el = el;
        selectedEl = el;
        ensureStableId(el);
        clearNestedFreePositions(el);

        if (el.hasAttribute('data-hms-user') || el.classList.contains('nav-bar')) {
          dragState.mode = 'fixed';
          const er = getVisualRect(el);
          setPosImportant(el, 'position', 'fixed');
          setPosImportant(el, 'left', er.left + 'px');
          setPosImportant(el, 'top', er.top + 'px');
          setPosImportant(el, 'right', 'auto');
          setPosImportant(el, 'bottom', 'auto');
          setPosImportant(el, 'margin', '0');
          setPosImportant(el, 'transform', 'none');
          el.setAttribute('data-hms-free-position', '1');
          if (el.classList.contains('nav-bar')) el.setAttribute('data-hms-keep-fixed', '1');
          dragState.left = er.left;
          dragState.top = er.top;
          dragState.startRect = er;
        } else {
          dragState.mode = 'transform';
          const base = normalizeToTransformMove(el);
          dragState.baseX = base.x;
          dragState.baseY = base.y;
          dragState.left = 0;
          dragState.top = 0;
          dragState.startRect = getVisualRect(el);
        }
        dragState.historyStarted = true;
        suppressNextClick = true;
      }

      const snapped = snapDragPosition(dragState, dx, dy, e.altKey);
      const el = dragState.el;
      if (dragState.mode === 'transform') {
        setElementTranslate(el, dragState.baseX + snapped.dx, dragState.baseY + snapped.dy);
        updateHandles();
      } else {
        setPosImportant(el, 'position', 'fixed');
        setPosImportant(el, 'left', snapped.left + 'px');
        setPosImportant(el, 'top', snapped.top + 'px');
        updateHandles({
          left: snapped.left,
          top: snapped.top,
          width: dragState.startRect.width,
          height: dragState.startRect.height,
          right: snapped.left + dragState.startRect.width,
          bottom: snapped.top + dragState.startRect.height,
        });
      }
      return;
    }
    if (resizeState) {
      const dx = e.clientX - resizeState.startX;
      const dy = e.clientY - resizeState.startY;
      if (!resizeState.historyStarted) {
        if (Math.hypot(dx, dy) < 3) return;
        beginHistoryStep();
        const el = resizeState.el;
        if (!el.hasAttribute('data-hms-user')) {
          normalizeToTransformMove(el);
        } else {
          promoteToAbsolute(el);
        }
        setPosImportant(el, 'max-width', 'none');
        setPosImportant(el, 'min-width', '0');
        setPosImportant(el, 'min-height', '0');
        setPosImportant(el, 'width', resizeState.width + 'px');
        if (!resizeState.isText || resizeState.isCorner) {
          setPosImportant(el, 'height', resizeState.height + 'px');
        }
        const r = getVisualRect(el);
        resizeState.viewportLeft = r.left;
        resizeState.viewportTop = r.top;
        resizeState.width = Math.max(8, r.width);
        resizeState.height = Math.max(8, r.height);
        resizeState.historyStarted = true;
      }
      const snap = e.altKey ? 1 : 8;
      let width = resizeState.width;
      let height = resizeState.height;
      const dir = resizeState.dir;
      if (dir.includes('e')) width = Math.max(16, resizeState.width + dx);
      if (dir.includes('s')) height = Math.max(16, resizeState.height + dy);
      if (dir.includes('w')) width = Math.max(16, resizeState.width - dx);
      if (dir.includes('n')) height = Math.max(16, resizeState.height - dy);
      width = Math.round(width / snap) * snap;
      height = Math.round(height / snap) * snap;

      const el = resizeState.el;
      if (resizeState.isText && resizeState.isCorner) {
        const scaleW = width / Math.max(1, resizeState.width);
        const scaleH = height / Math.max(1, resizeState.height);
        const scale = Math.max(0.15, Math.min(scaleW, scaleH));
        const fontSize = Math.max(8, Math.min(220, resizeState.startFontSize * scale));
        setPosImportant(el, 'font-size', fontSize.toFixed(2) + 'px');
        setPosImportant(el, 'width', 'max-content');
        setPosImportant(el, 'height', 'auto');
        setPosImportant(el, 'max-width', 'none');
        setPosImportant(el, 'min-width', '0');
        setPosImportant(el, 'white-space', 'nowrap');
      } else if (resizeState.isText) {
        setPosImportant(el, 'width', width + 'px');
        setPosImportant(el, 'height', height + 'px');
        setPosImportant(el, 'max-width', 'none');
        setPosImportant(el, 'min-width', '0');
        setPosImportant(el, 'white-space', 'normal');
        setPosImportant(el, 'overflow', 'hidden');
      } else {
        setPosImportant(el, 'width', width + 'px');
        setPosImportant(el, 'height', height + 'px');
      }
      updateHandles();
    }
  }

  function convertFreePositionToPercent(el) {
    if (!el || !isFreePosition(el)) return;
    if (el.getAttribute('data-hms-move-mode') === 'transform') return;
    el = resolveEditableRoot(el) || el;
    const cs = window.getComputedStyle(el);
    if (cs.position !== 'fixed' && cs.position !== 'absolute') return;
    const parent = (cs.position === 'fixed') ? null : (el.offsetParent || el.parentElement);
    const pr = parent ? parent.getBoundingClientRect() : { left: 0, top: 0, width: window.innerWidth, height: window.innerHeight };
    const er = getVisualRect(el);
    if (pr.width < 8 || pr.height < 8) return;
    if (cs.position === 'fixed') {
      setPosImportant(el, 'left', er.left + 'px');
      setPosImportant(el, 'top', er.top + 'px');
      return;
    }
    let left = er.left - pr.left;
    let top = er.top - pr.top;
    const pad = 8;
    left = Math.min(Math.max(-pad, left), pr.width - pad);
    top = Math.min(Math.max(-pad, top), pr.height - pad);
    setPosImportant(el, 'position', 'absolute');
    setPosImportant(el, 'left', ((left / pr.width) * 100).toFixed(3) + '%');
    setPosImportant(el, 'top', ((top / pr.height) * 100).toFixed(3) + '%');
    setPosImportant(el, 'right', 'auto');
    setPosImportant(el, 'bottom', 'auto');
  }

  function normalizeFreePositions() {
    document.querySelectorAll('[data-hms-free-position="1"]').forEach((el) => {
      if (el.style.display === 'none') return;
      if (el.getAttribute('data-hms-move-mode') === 'transform') return;
      convertFreePositionToPercent(el);
    });
    rebuildFreePosSheet();
  }

  function onPointerUp() {
    if (dragState) {
      if (dragState.historyStarted) {
        const el = dragState.el;
        if (dragState.mode === 'transform') {
          const t = getElementTranslate(el);
          setElementTranslate(el, t.x, t.y);
        } else if (el && (el.classList.contains('nav-bar') || el.getAttribute('data-hms-keep-fixed') === '1')) {
          const er = getVisualRect(el);
          setPosImportant(el, 'position', 'fixed');
          setPosImportant(el, 'left', er.left + 'px');
          setPosImportant(el, 'top', er.top + 'px');
          setPosImportant(el, 'right', 'auto');
          setPosImportant(el, 'bottom', 'auto');
          el.setAttribute('data-hms-free-position', '1');
          el.setAttribute('data-hms-keep-fixed', '1');
        } else if (el && el.hasAttribute('data-hms-user')) {
          commitFixedToAbsolute(el);
          convertFreePositionToPercent(el);
        }
        saveElementState(el);
        updateHandles();
        setTimeout(function () { suppressNextClick = false; }, 0);
      }
      dragState = null;
      hideSmartGuides();
    }
    if (resizeState) {
      if (resizeState.historyStarted) {
        const el = resizeState.el;
        if (el && !el.hasAttribute('data-hms-user')) {
          el.setAttribute('data-hms-move-mode', 'transform');
        }
        saveElementState(el);
        updateHandles();
      }
      resizeState = null;
      hideSmartGuides();
    }
  }

  function stopSiteEvent(e) {
    e.preventDefault();
    e.stopPropagation();
    if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
  }

  function onClick(e) {
    if (!designMode) return;
    // Allow site content actions (Add Room, edit/delete tools) to run in Design mode.
    if (e.target && e.target.closest && e.target.closest('[data-hms-no-edit]')) {
      return;
    }
    if (isEditorChrome(e.target)) return;
    // Design mode: block hotel site functions (nav, booking, forms); only editing runs.
    stopSiteEvent(e);
    if (suppressNextClick) {
      suppressNextClick = false;
      return;
    }
    const el = findEditableAtPoint(e);
    if (!el) {
      clearSelection();
      postToParent({ type: 'element-deselected' });
      return;
    }
    if (!canEditCurrentPage()) {
      clearSelection();
      blockEditToast();
      return;
    }
    selectElement(el);
  }

  function onSubmit(e) {
    if (!designMode) return;
    if (isEditorChrome(e.target)) return;
    stopSiteEvent(e);
  }

  function onDblClick(e) {
    if (!designMode || !canEdit) return;
    if (!canEditCurrentPage()) {
      e.preventDefault();
      e.stopPropagation();
      blockEditToast();
      return;
    }
    const el = findEditableAtPoint(e);
    if (!el || ['IMG', 'I', 'SVG', 'INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName)) return;
    e.preventDefault();
    e.stopPropagation();
    selectElement(el);
    beginHistoryStep();
    el.contentEditable = 'true';
    el.focus();
    const finish = () => {
      el.contentEditable = 'false';
      el.removeEventListener('blur', finish);
      saveElementState(el);
      selectElement(el);
    };
    el.addEventListener('blur', finish);
  }

  function ensureFileInput() {
    if (fileInput) return fileInput;
    fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/*';
    fileInput.style.display = 'none';
    fileInput.id = 'hms-editor-file';
    document.body.appendChild(fileInput);
    fileInput.addEventListener('change', function () {
      const file = fileInput.files && fileInput.files[0];
      if (!file || !selectedEl) return;
      const reader = new FileReader();
      reader.onload = function () {
        applyImageToSelected(reader.result);
      };
      reader.readAsDataURL(file);
      postToParent({
        type: 'upload-image-needed',
        editId: selectedEl.getAttribute('data-edit-id') || getSelector(selectedEl),
        name: file.name,
        mime: file.type,
      });
    });
    return fileInput;
  }

  function applyImageToSelected(url, recordHistory) {
    if (!selectedEl || !url) return;
    if (recordHistory !== false) beginHistoryStep();
    if (selectedEl.tagName === 'IMG') {
      selectedEl.setAttribute('src', url);
    } else {
      selectedEl.style.backgroundImage = "url('" + url + "')";
      selectedEl.style.backgroundSize = selectedEl.style.backgroundSize || 'cover';
      selectedEl.style.backgroundPosition = selectedEl.style.backgroundPosition || 'center';
    }
    saveElementState(selectedEl);
  }

  function publishModeState() {
    window.__HMS_DESIGN_MODE__ = designMode;
    window.__HMS_SITE_INTERACTIVE__ = !designMode;
    window.dispatchEvent(new CustomEvent('hms-mode-change', {
      detail: { designMode: designMode, interactive: !designMode, canEdit: canEdit },
    }));
  }

  function setDesignMode(on) {
    if (on && !canEdit) {
      designMode = false;
      document.body.classList.remove('hms-design-mode');
      clearSelection();
      hideSmartGuides();
      clearSectionFocus();
      updateEditHint();
      publishModeState();
      postToParent({ type: 'mode-ack', designMode: false, canEdit: false });
      return;
    }
    designMode = !!on;
    document.body.classList.toggle('hms-design-mode', designMode);
    if (!designMode) {
      clearSelection();
      hideSmartGuides();
      clearSectionFocus();
      document.querySelectorAll('[contenteditable="true"]').forEach((n) => {
        n.contentEditable = 'false';
      });
      // Re-fit free-positioned nodes so Design coords stay visible in Preview.
      requestAnimationFrame(function () {
        normalizeFreePositions();
        document.querySelectorAll('[data-hms-free-position="1"]').forEach((el) => {
          if (el.hasAttribute('data-hms-user')) return;
          const id = el.getAttribute('data-edit-id') || getSelector(el);
          if (!id) return;
          const entry = customizations[id] || {};
          entry.freePosition = true;
          entry.position = el.style.position || 'absolute';
          if (el.style.left) entry.left = el.style.left;
          if (el.style.top) entry.top = el.style.top;
          if (el.style.maxWidth) entry['max-width'] = el.style.maxWidth;
          if (el.style.width) entry.width = el.style.width;
          entry.page = getCurrentPage();
          customizations[id] = entry;
          el.setAttribute('data-edit-id', id);
        });
        persistUserElements();
        notifyChanged();
      });
    } else {
      ensureHandles();
      ensureUserCanvas();
      ensureGuides();
      syncSectionMode();
    }
    updateEditHint();
    publishModeState();
    postToParent({ type: 'mode-ack', designMode: designMode, canEdit: canEdit });
  }

  function applyFromParent(data) {
    if (!canEdit) return;
    if (!selectedEl && data.id) selectedEl = findByKey(data.id);
    if (!selectedEl) return;
    beginHistoryStep();

    if (data.style) {
      Object.keys(data.style).forEach((prop) => {
        selectedEl.style.setProperty(prop, data.style[prop]);
      });
    }
    if (data.text != null && !['IMG', 'I', 'SVG'].includes(selectedEl.tagName)) {
      if (selectedEl.tagName === 'INPUT' || selectedEl.tagName === 'TEXTAREA') {
        selectedEl.placeholder = data.text;
      } else if (selectedEl.childElementCount === 0 || selectedEl.getAttribute('data-hms-text') === '1') {
        selectedEl.textContent = data.text;
      } else {
        const walker = document.createTreeWalker(selectedEl, NodeFilter.SHOW_TEXT, null);
        const textNode = walker.nextNode();
        if (textNode) textNode.nodeValue = data.text;
      }
    }
    if (data.src) applyImageToSelected(data.src, false);
    if (data.iconClass) {
      const icon = selectedEl.tagName === 'I' ? selectedEl : selectedEl.querySelector('i');
      if (icon) {
        const keep = icon.classList.contains('hms-edit-selected');
        icon.className = data.iconClass;
        if (keep) icon.classList.add('hms-edit-selected');
      }
    }
    if (data.heading && /^h[1-6]|p$/i.test(data.heading)) {
      const tag = data.heading.toLowerCase();
      if (/^h[1-6]$|^p$/.test(selectedEl.tagName.toLowerCase())) {
        const neu = document.createElement(tag);
        neu.innerHTML = selectedEl.innerHTML;
        neu.className = selectedEl.className;
        neu.style.cssText = selectedEl.style.cssText;
        Array.from(selectedEl.attributes).forEach((attr) => {
          if (attr.name === 'class' || attr.name === 'style') return;
          neu.setAttribute(attr.name, attr.value);
        });
        const id = selectedEl.getAttribute('data-edit-id') || getSelector(selectedEl);
        const hmsId = selectedEl.getAttribute('data-hms-id');
        selectedEl.replaceWith(neu);
        selectedEl = neu;
        if (hmsId) selectedEl.setAttribute('data-hms-id', hmsId);
        if (id) selectedEl.setAttribute('data-edit-id', id);
        selectedEl.classList.add('hms-edit-selected');
        if (selectedEl.hasAttribute('data-hms-user')) {
          selectedEl.setAttribute('data-hms-type', 'text');
          selectedEl.setAttribute('data-hms-text', '1');
        }
      }
    }
    saveElementState(selectedEl);
    updateHandles();
  }

  function addElement(type) {
    if (!canEdit || !designMode) return;
    if (!canEditCurrentPage()) {
      blockEditToast();
      return;
    }
    beginHistoryStep();
    ensureUserCanvas();
    const scrollY = window.scrollY || document.documentElement.scrollTop || 0;
    const baseTop = Math.max(90, Math.round(scrollY + 120));
    const defaults = {
      text: { type: 'text', text: 'Double-click to edit', styles: { left: '100px', top: baseTop + 'px', width: '280px', color: '#111827', 'font-size': '20px', 'font-weight': '600', 'z-index': '60' } },
      button: { type: 'button', text: 'Book Now', styles: { left: '100px', top: (baseTop + 60) + 'px', padding: '12px 24px', 'background-color': '#0ea5e9', color: '#fff', 'border-radius': '8px', border: 'none', 'font-weight': '700', cursor: 'pointer', 'z-index': '60' } },
      image: { type: 'image', styles: { left: '100px', top: baseTop + 'px', width: '280px', height: '180px', 'object-fit': 'cover', 'border-radius': '12px', 'z-index': '60' } },
      textfield: { type: 'textfield', text: 'Enter text…', styles: { left: '100px', top: (baseTop + 80) + 'px', width: '260px', padding: '10px 12px', 'border-radius': '8px', border: '1px solid #cbd5e1', 'background-color': '#fff', 'z-index': '60' } },
      icon: { type: 'icon', className: 'fas fa-hotel', styles: { left: '120px', top: baseTop + 'px', 'font-size': '42px', color: '#0ea5e9', 'z-index': '60' } },
      card: {
        type: 'card',
        html: '<h3 style="margin:0 0 8px;font-size:18px;">Suite</h3><p style="margin:0;opacity:.8;">Comfortable rooms with city views.</p>',
        styles: { left: '100px', top: baseTop + 'px', width: '260px', padding: '20px', 'background-color': '#ffffff', color: '#0f172a', 'border-radius': '14px', 'box-shadow': '0 12px 30px rgba(0,0,0,.18)', 'z-index': '60' },
      },
      container: {
        type: 'container',
        html: '<img src="https://placehold.co/280x160/1e293b/94a3b8?text=Room+Photo" alt="Room" style="width:100%;height:140px;object-fit:cover;border-radius:8px;display:block;margin-bottom:10px;"/><h3 style="margin:0 0 6px;font-size:16px;">Room Name</h3><p style="margin:0;opacity:.8;font-size:13px;">Add a short description for this room.</p>',
        styles: { left: '80px', top: baseTop + 'px', width: '320px', height: 'auto', minHeight: '220px', padding: '16px', 'background-color': 'rgba(255,255,255,.92)', color: '#0f172a', 'border-radius': '12px', border: '1px dashed rgba(148,163,184,.6)', 'z-index': '60' },
      },
    };
    const base = defaults[type] || defaults.text;
    const item = Object.assign({ id: uid('user'), page: getCurrentPage() }, base);
    if (!customizations[USER_KEY]) customizations[USER_KEY] = [];
    customizations[USER_KEY].push(item);
    renderUserElements();
    const el = document.querySelector('[data-hms-id="' + item.id + '"]');
    if (el) selectElement(el);
    notifyChanged();
  }

  function deleteSelected() {
    if (!canEdit || !selectedEl) return;
    if (!canEditCurrentPage()) {
      blockEditToast();
      return;
    }
    beginHistoryStep();
    if (selectedEl.hasAttribute('data-hms-user')) {
      const id = selectedEl.getAttribute('data-hms-id');
      customizations[USER_KEY] = (customizations[USER_KEY] || []).filter((x) => x.id !== id);
      clearSelection();
      renderUserElements();
      notifyChanged();
      postToParent({ type: 'element-deselected' });
      return;
    }
    removeFlowSpacer(selectedEl);
    const key = selectedEl.getAttribute('data-edit-id') || getSelector(selectedEl);
    selectedEl.style.display = 'none';
    if (!customizations[DELETED_KEY]) customizations[DELETED_KEY] = [];
    const deletedEntry = { id: key, page: getCurrentPage() };
    const exists = customizations[DELETED_KEY].some((d) => (typeof d === 'string' ? d === key : d.id === key));
    if (!exists) customizations[DELETED_KEY].push(deletedEntry);
    if (customizations[key]) {
      customizations[key].display = 'none';
      customizations[key].page = getCurrentPage();
    } else {
      customizations[key] = { display: 'none', page: getCurrentPage() };
    }
    clearSelection();
    cleanOrphanSpacers();
    notifyChanged();
    postToParent({ type: 'element-deselected' });
  }

  function duplicateSelected() {
    if (!canEdit || !selectedEl) return;
    if (!canEditCurrentPage()) {
      blockEditToast();
      return;
    }
    beginHistoryStep();
    const r = selectedEl.getBoundingClientRect();
    const type = selectedEl.getAttribute('data-hms-type')
      || (selectedEl.tagName === 'IMG' ? 'image'
        : selectedEl.tagName === 'BUTTON' ? 'button'
          : selectedEl.tagName === 'INPUT' ? 'textfield'
            : selectedEl.tagName === 'I' ? 'icon' : 'text');

    const styles = {
      position: 'absolute',
      left: (r.left + 24 + window.scrollX) + 'px',
      top: (r.top + 24 + window.scrollY) + 'px',
      width: r.width + 'px',
      height: r.height + 'px',
      'z-index': String(++zCounter),
    };
    const cs = window.getComputedStyle(selectedEl);
    ['color', 'background-color', 'font-size', 'font-weight', 'font-family', 'text-align', 'padding', 'border-radius', 'border'].forEach((p) => {
      styles[p] = cs.getPropertyValue(p);
    });

    const item = {
      id: uid('user'),
      type: type,
      page: getCurrentPage(),
      text: (selectedEl.innerText || selectedEl.placeholder || '').trim().slice(0, 200) || 'Copy',
      html: selectedEl.innerHTML,
      src: selectedEl.tagName === 'IMG' ? selectedEl.getAttribute('src') : null,
      className: selectedEl.tagName === 'I' ? selectedEl.className.replace(/\bhms-edit-selected\b/g, '').trim() : '',
      styles: styles,
    };
    if (!customizations[USER_KEY]) customizations[USER_KEY] = [];
    customizations[USER_KEY].push(item);
    renderUserElements();
    const el = document.querySelector('[data-hms-id="' + item.id + '"]');
    if (el) selectElement(el);
    notifyChanged();
  }

  function changeLayer(direction) {
    if (!canEdit || !selectedEl) return;
    beginHistoryStep();
    promoteToAbsolute(selectedEl);
    const cur = parseInt(window.getComputedStyle(selectedEl).zIndex, 10) || 1;
    if (direction === 'front') {
      zCounter += 1;
      selectedEl.style.zIndex = String(zCounter);
    } else if (direction === 'back') {
      selectedEl.style.zIndex = String(Math.max(0, cur - 1));
    } else if (direction === 'forward') {
      selectedEl.style.zIndex = String(cur + 1);
      if (cur + 1 > zCounter) zCounter = cur + 1;
    } else if (direction === 'backward') {
      selectedEl.style.zIndex = String(Math.max(0, cur - 1));
    }
    saveElementState(selectedEl);
    updateHandles();
  }

  function nudgeSelected(dx, dy) {
    if (!selectedEl || !canEditCurrentPage()) return false;
    beginHistoryStep();
    selectedEl = resolveEditableRoot(selectedEl) || selectedEl;
    if (selectedEl.hasAttribute('data-hms-user') || selectedEl.classList.contains('nav-bar')) {
      promoteToAbsolute(selectedEl);
      const parent = selectedEl.offsetParent || selectedEl.parentElement || document.body;
      const pr = parent.getBoundingClientRect();
      const er = getVisualRect(selectedEl);
      setPosImportant(selectedEl, 'left', (er.left - pr.left + dx) + 'px');
      setPosImportant(selectedEl, 'top', (er.top - pr.top + dy) + 'px');
    } else {
      const base = normalizeToTransformMove(selectedEl);
      setElementTranslate(selectedEl, base.x + dx, base.y + dy);
    }
    saveElementState(selectedEl);
    updateHandles();
    return true;
  }

  function alignSelected(direction) {
    if (!selectedEl || !canEditCurrentPage()) return;
    selectedEl = resolveEditableRoot(selectedEl) || selectedEl;
    beginHistoryStep();

    // For in-flow text, horizontal center/left/right should use text-align
    // so preview layout stays correct (no off-canvas absolute coords).
    const tag = selectedEl.tagName;
    const isTextish = /^H[1-6]$/.test(tag) || ['P', 'SPAN', 'EM', 'STRONG', 'SMALL', 'LABEL', 'LI', 'A', 'BUTTON', 'DIV'].includes(tag);
    const alreadyFree = isFreePosition(selectedEl);
    if (isTextish && !alreadyFree && ['left', 'center', 'right', 'justify'].includes(direction)) {
      selectedEl.style.textAlign = direction === 'middle' ? 'center' : direction;
      if (direction === 'center') {
        selectedEl.style.marginLeft = 'auto';
        selectedEl.style.marginRight = 'auto';
        if (!selectedEl.style.width && !selectedEl.style.maxWidth) {
          selectedEl.style.maxWidth = '100%';
        }
      }
      saveElementState(selectedEl);
      updateHandles();
      return;
    }

    // Transform-based align for template elements (keeps page layout stable).
    if (!selectedEl.hasAttribute('data-hms-user')) {
      normalizeToTransformMove(selectedEl);
      selectedEl.style.removeProperty('transform');
      void selectedEl.offsetWidth;
      const natural = getVisualRect(selectedEl);
      const box = { width: natural.width, height: natural.height };
      const section = selectedEl.closest('[data-hms-section], .hero, main, [data-hms-page]') || document.body;
      const pr = section.getBoundingClientRect();
      let targetLeft = natural.left;
      let targetTop = natural.top;
      if (direction === 'left') targetLeft = pr.left + 12;
      if (direction === 'center') targetLeft = pr.left + Math.max(0, (pr.width - box.width) / 2);
      if (direction === 'right') targetLeft = pr.right - box.width - 12;
      if (direction === 'top') targetTop = pr.top + 12;
      if (direction === 'middle') targetTop = pr.top + Math.max(0, (pr.height - box.height) / 2);
      if (direction === 'bottom') targetTop = pr.bottom - box.height - 12;
      setElementTranslate(selectedEl, targetLeft - natural.left, targetTop - natural.top);
      saveElementState(selectedEl);
      updateHandles();
      return;
    }

    promoteToAbsolute(selectedEl);
    const parent = ensurePositioningContext(selectedEl) || selectedEl.parentElement || document.body;
    const pr = parent.getBoundingClientRect();
    const er = selectedEl.getBoundingClientRect();
    const width = er.width;
    const height = er.height;
    const parentWidth = Math.max(1, pr.width);
    const parentHeight = Math.max(1, pr.height);

    let left = er.left - pr.left + (parent.scrollLeft || 0);
    let top = er.top - pr.top + (parent.scrollTop || 0);

    if (direction === 'left') left = 0;
    if (direction === 'center') left = Math.max(0, (parentWidth - width) / 2);
    if (direction === 'right') left = Math.max(0, parentWidth - width);
    if (direction === 'top') top = 0;
    if (direction === 'middle') top = Math.max(0, (parentHeight - height) / 2);
    if (direction === 'bottom') top = Math.max(0, parentHeight - height);

    // Keep inside parent so preview cannot clip the element away.
    left = Math.min(Math.max(0, left), Math.max(0, parentWidth - width));
    top = Math.min(Math.max(0, top), Math.max(0, parentHeight - height));

    selectedEl.style.left = ((left / parentWidth) * 100).toFixed(3) + '%';
    selectedEl.style.top = ((top / parentHeight) * 100).toFixed(3) + '%';
    selectedEl.style.right = 'auto';
    selectedEl.style.bottom = 'auto';
    selectedEl.style.maxWidth = '100%';
    if (/^H[1-6]$/.test(tag) || tag === 'P' || tag === 'SPAN') {
      selectedEl.style.height = 'auto';
    }
    saveElementState(selectedEl);
    updateHandles();
  }

  // Styles
  const style = document.createElement('style');
  style.textContent = `
    #hms-user-canvas {
      position: absolute; left: 0; top: 0; width: 100%; min-height: 100%;
      pointer-events: none; z-index: 50;
    }
    hms-spacer {
      display: block;
      visibility: hidden !important;
      pointer-events: none !important;
      overflow: hidden;
    }
    body.hms-design-mode #hms-user-canvas [data-hms-user] {
      pointer-events: auto; cursor: pointer;
    }
    #hms-user-canvas [data-hms-user] { pointer-events: none; }
    body.hms-design-mode .nav-bar {
      z-index: 2500 !important;
      pointer-events: auto !important;
    }
    body.hms-design-mode .nav-bar,
    body.hms-design-mode .nav-bar * {
      cursor: pointer;
    }
    body.hms-design-mode [data-hms-id],
    body.hms-design-mode h1, body.hms-design-mode h2, body.hms-design-mode h3,
    body.hms-design-mode h4, body.hms-design-mode h5, body.hms-design-mode h6,
    body.hms-design-mode p, body.hms-design-mode span, body.hms-design-mode em,
    body.hms-design-mode strong, body.hms-design-mode button,
    body.hms-design-mode a, body.hms-design-mode img, body.hms-design-mode i,
    body.hms-design-mode input, body.hms-design-mode textarea,
    body.hms-design-mode .nav-bar,
    body.hms-design-mode [data-hms-section],
    body.hms-design-mode [data-hms-bg-target] {
      cursor: pointer;
    }
    body.hms-design-mode [data-hms-section] {
      position: relative;
      outline: 1px dashed rgba(34,211,238,.35);
      outline-offset: -1px;
    }
    body.hms-design-mode [data-hms-section]::before {
      content: attr(data-hms-section);
      position: absolute; top: 10px; left: 10px; z-index: 40;
      background: rgba(8,145,178,.92); color: #fff; font: 700 10px/1 Inter, system-ui, sans-serif;
      letter-spacing: .08em; text-transform: uppercase; padding: 6px 10px; border-radius: 999px;
      pointer-events: none;
    }
    body.hms-design-mode [data-hms-free-position="1"] {
      box-sizing: border-box;
      overflow: visible !important;
    }
    #hms-section-rail {
      display: none; position: fixed; left: 50%; bottom: 52px; transform: translateX(-50%);
      z-index: 99997; gap: 6px; padding: 6px; border-radius: 999px;
      background: rgba(15,23,42,.88); border: 1px solid rgba(148,163,184,.35);
      box-shadow: 0 10px 30px rgba(0,0,0,.35); pointer-events: auto;
    }
    body.hms-design-mode #hms-section-rail { display: inline-flex; }
    #hms-section-rail button {
      border: 0; border-radius: 999px; padding: 7px 12px; cursor: pointer;
      background: transparent; color: #e2e8f0; font: 700 11px/1 Inter, system-ui, sans-serif;
      letter-spacing: .04em; text-transform: uppercase;
    }
    #hms-section-rail button.active { background: #0891b2; color: #fff; }
    .hms-edit-selected { outline: none !important; }
    #hms-editor-ui { display: none; position: fixed; inset: 0; pointer-events: none; z-index: 99998; }
    #hms-editor-ui .hms-sel-box {
      position: fixed; border: 2px solid #22d3ee; box-shadow: 0 0 0 1px rgba(8,145,178,.35);
      pointer-events: none; box-sizing: border-box;
    }
    #hms-editor-ui .hms-handle {
      position: fixed; width: 10px; height: 10px; background: #fff; border: 2px solid #0891b2;
      border-radius: 2px; pointer-events: auto; box-sizing: border-box;
    }
    #hms-editor-ui .hms-handle.nw, #hms-editor-ui .hms-handle.se { cursor: nwse-resize; }
    #hms-editor-ui .hms-handle.ne, #hms-editor-ui .hms-handle.sw { cursor: nesw-resize; }
    #hms-editor-ui .hms-handle.n, #hms-editor-ui .hms-handle.s { cursor: ns-resize; }
    #hms-editor-ui .hms-handle.e, #hms-editor-ui .hms-handle.w { cursor: ew-resize; }
    #hms-editor-ui .hms-move-handle {
      position: fixed; transform: translate(-50%, -100%); height: 24px;
      align-items: center; gap: 5px; padding: 0 9px; border: 0; border-radius: 6px 6px 0 0;
      background: #0891b2; color: white; font: 700 10px/1 Inter, system-ui, sans-serif;
      pointer-events: auto; cursor: grab; box-shadow: 0 4px 12px rgba(8,145,178,.35);
    }
    #hms-editor-ui .hms-move-handle:active { cursor: grabbing; }
    .hms-smart-guide {
      display: none; position: fixed; z-index: 99997; pointer-events: none;
      background: #ec4899; box-shadow: 0 0 0 1px rgba(236,72,153,.35);
    }
    .hms-smart-guide.vertical { top: 0; bottom: 0; width: 1px; }
    .hms-smart-guide.horizontal { left: 0; right: 0; height: 1px; }
    body.hms-design-mode::after {
      content: attr(data-hms-edit-hint);
      position: fixed; left: 50%; bottom: 16px; transform: translateX(-50%);
      background: rgba(6,182,212,0.95); color: #042f2e; font: 600 11px/1 Inter, system-ui, sans-serif;
      padding: 8px 14px; border-radius: 999px; z-index: 99999; pointer-events: none;
      box-shadow: 0 8px 24px rgba(0,0,0,0.35); white-space: nowrap;
      max-width: 90vw; overflow: hidden; text-overflow: ellipsis;
    }
  `;
  document.head.appendChild(style);

  let activeSectionId = null;
  let sectionRailEl = null;

  function getSections() {
    return Array.from(document.querySelectorAll('[data-hms-section]'));
  }

  function ensureSectionRail() {
    if (sectionRailEl && document.body.contains(sectionRailEl)) return sectionRailEl;
    sectionRailEl = document.getElementById('hms-section-rail');
    if (!sectionRailEl) {
      sectionRailEl = document.createElement('div');
      sectionRailEl.id = 'hms-section-rail';
      sectionRailEl.setAttribute('data-hms-no-edit', '1');
      document.body.appendChild(sectionRailEl);
    }
    return sectionRailEl;
  }

  function focusSection(sectionId, opts) {
    const sections = getSections();
    if (!sections.length) return;
    const target = sections.find((s) => s.getAttribute('data-hms-section') === sectionId) || sections[0];
    activeSectionId = target.getAttribute('data-hms-section');
    // Keep all sections in the document flow so Design layout matches Preview.
    document.body.classList.remove('hms-section-focus');
    sections.forEach((section) => {
      section.classList.toggle('hms-section-active', section === target);
    });
    rebuildSectionRail();
    if (!opts || opts.scroll !== false) {
      const navOffset = 80;
      const y = target.getBoundingClientRect().top + window.scrollY - navOffset;
      window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
    }
    updateEditHint();
  }

  function clearSectionFocus() {
    document.body.classList.remove('hms-section-focus');
    getSections().forEach((section) => section.classList.remove('hms-section-active'));
    activeSectionId = null;
    if (sectionRailEl) sectionRailEl.style.display = 'none';
  }

  function rebuildSectionRail() {
    const sections = getSections();
    const rail = ensureSectionRail();
    if (!designMode || !sections.length) {
      rail.style.display = 'none';
      return;
    }
    rail.innerHTML = '';
    sections.forEach((section) => {
      const id = section.getAttribute('data-hms-section') || 'section';
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = id;
      btn.className = id === activeSectionId ? 'active' : '';
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        focusSection(id);
      });
      rail.appendChild(btn);
    });
    rail.style.display = 'inline-flex';
  }

  function syncSectionMode() {
    if (!designMode) {
      clearSectionFocus();
      return;
    }
    const sections = getSections();
    if (!sections.length) {
      clearSectionFocus();
      return;
    }
    if (!activeSectionId || !sections.some((s) => s.getAttribute('data-hms-section') === activeSectionId)) {
      focusSection(sections[0].getAttribute('data-hms-section'), { scroll: false });
    } else {
      focusSection(activeSectionId, { scroll: false });
    }
  }

  function updateEditHint() {
    const pages = Array.isArray(editablePages) ? editablePages : [];
    const sectionBit = activeSectionId ? (' · slide "' + activeSectionId + '"') : '';
    const hint = canEditCurrentPage()
      ? ('Editing "' + getCurrentPage() + '"' + sectionBit + ' · click to style · Move handle to drag · Alt+click selects container/background')
      : (canEdit
        ? ('View only on "' + getCurrentPage() + '" — you may redesign: ' + (pages.join(', ') || 'none'))
        : 'Preview — team synced site');
    document.body.setAttribute('data-hms-edit-hint', hint);
  }

  document.addEventListener('click', onClick, true);
  document.addEventListener('submit', onSubmit, true);
  document.addEventListener('dblclick', onDblClick, true);
  document.addEventListener('mousemove', onPointerMove, true);
  document.addEventListener('mouseup', onPointerUp, true);
  window.addEventListener('scroll', updateHandles, true);
  window.addEventListener('resize', updateHandles);

  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && !e.shiftKey && (e.key === 's' || e.key === 'S')) {
      e.preventDefault();
      postToParent({ type: 'request-save-draft' });
      return;
    }
    if (!designMode || !canEdit) return;
    if (e.target && (e.target.isContentEditable || /INPUT|TEXTAREA|SELECT/.test(e.target.tagName))) return;
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
      e.preventDefault();
      if (e.shiftKey) redo();
      else undo();
      return;
    }
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
      e.preventDefault();
      redo();
      return;
    }
    if (!selectedEl) return;
    if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(e.key)) {
      e.preventDefault();
      const step = e.shiftKey ? 10 : 1;
      if (e.key === 'ArrowLeft') nudgeSelected(-step, 0);
      if (e.key === 'ArrowRight') nudgeSelected(step, 0);
      if (e.key === 'ArrowUp') nudgeSelected(0, -step);
      if (e.key === 'ArrowDown') nudgeSelected(0, step);
      return;
    }
    if (e.key === 'Delete' || e.key === 'Backspace') {
      e.preventDefault();
      deleteSelected();
    }
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'd') {
      e.preventDefault();
      duplicateSelected();
    }
  });

  window.addEventListener('message', function (event) {
    const data = event.data || {};
    if (!data || data.source !== 'hms-parent') return;

    switch (data.type) {
      case 'set-mode':
        setDesignMode(data.mode === 'design' || data.mode === 'build');
        break;
      case 'set-can-edit':
        canEdit = data.canEdit === true;
        window.__HMS_CAN_EDIT__ = canEdit;
        if (!canEdit) setDesignMode(false);
        else updateEditHint();
        break;
      case 'set-editable-pages':
        editablePages = Array.isArray(data.pages) ? data.pages.slice() : [];
        window.__HMS_EDITABLE_PAGES__ = editablePages;
        updateEditHint();
        clearSelection();
        break;
      case 'navigate-page':
        if (typeof window.__HMS_NAVIGATE__ === 'function' && data.page) {
          window.__HMS_NAVIGATE__(data.page);
        }
        break;
      case 'apply-edit':
        if (!canEditCurrentPage()) {
          blockEditToast();
          break;
        }
        applyFromParent(data);
        break;
      case 'load-customizations':
        customizations = normalizeCustomizations(data.customizations || {});
        applyAllCustomizations();
        break;
      case 'request-customizations':
        persistUserElements();
        notifyChanged();
        break;
      case 'clear-selection':
        clearSelection();
        break;
      case 'trigger-image-upload':
        if (!canEditCurrentPage()) {
          blockEditToast();
          break;
        }
        ensureFileInput().click();
        break;
      case 'image-uploaded':
        if (data.url) applyImageToSelected(data.url);
        break;
      case 'reset-styles':
        if (!canEditCurrentPage()) {
          blockEditToast();
          break;
        }
        if (selectedEl) {
          beginHistoryStep();
          const id = selectedEl.getAttribute('data-edit-id');
          selectedEl.removeAttribute('style');
          if (id && customizations[id]) {
            delete customizations[id];
            notifyChanged();
          }
          if (selectedEl.hasAttribute('data-hms-user')) {
            persistUserElements();
            notifyChanged();
          }
          updateHandles();
        }
        break;
      case 'undo':
        undo();
        break;
      case 'redo':
        redo();
        break;
      case 'add-element':
        addElement(data.elementType || 'text');
        break;
      case 'delete-element':
        deleteSelected();
        break;
      case 'duplicate-element':
        duplicateSelected();
        break;
      case 'select-parent':
        selectParentElement();
        break;
      case 'align-element':
        alignSelected(data.direction || 'center');
        break;
      case 'layer-element':
        if (!canEditCurrentPage()) {
          blockEditToast();
          break;
        }
        changeLayer(data.direction || 'front');
        break;
      default:
        break;
    }
  });

  window.addEventListener('hms-page-change', function (e) {
    currentPage = (e.detail && e.detail.page) || getCurrentPage();
    window.__HMS_CURRENT_PAGE__ = currentPage;
    clearSelection();
    activeSectionId = null;
    renderUserElements();
    setTimeout(syncSectionMode, 80);
    updateEditHint();
    postToParent({ type: 'page-changed', page: currentPage, canEditPage: canEditCurrentPage() });
  });

  const root = document.getElementById('root') || document.body;
  const mo = new MutationObserver(function () {
    if (applying || dragState || resizeState) return;
    if (designMode || Object.keys(customizations).length) scheduleReapply();
  });
  mo.observe(root, { childList: true, subtree: true });

  function boot() {
    ensureUserCanvas();
    cleanOrphanSpacers();
    applyAllCustomizations();
    rebuildFreePosSheet();
    if (!canEdit) setDesignMode(false);
    else publishModeState();
    updateEditHint();
    postToParent({
      type: 'editor-ready',
      customizations: customizations,
      canEdit: canEdit,
      page: getCurrentPage(),
      editablePages: editablePages,
    });
    publishHistoryState();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { setTimeout(boot, 400); });
  } else {
    setTimeout(boot, 400);
  }

  window.__HMS_DESIGN_MODE__ = designMode;
  window.__HMS_SITE_INTERACTIVE__ = !designMode;

  window.HMSTemplateEditor = {
    getCustomizations: () => customizations,
    setCustomizations: (c) => { customizations = normalizeCustomizations(c); applyAllCustomizations(); },
    notifyChanged: () => notifyChanged(),
    selectElement,
    setDesignMode,
    isDesignMode: () => designMode,
    isSiteInteractive: () => !designMode,
    addElement,
    deleteSelected,
    duplicateSelected,
    changeLayer,
    alignSelected,
    selectParentElement,
    undo,
    redo,
  };
})();
