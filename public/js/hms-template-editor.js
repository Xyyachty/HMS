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

  let designMode = false;
  let selectedEl = null;
  let customizations = normalizeCustomizations(window.__HMS_CUSTOMIZATIONS__ || {});
  let canEdit = window.__HMS_CAN_EDIT__ === true;
  let applying = false;
  let reapplyTimer = null;
  let fileInput = null;
  let dragState = null;
  let resizeState = null;
  let handlesEl = null;
  let userCanvas = null;
  let zCounter = 10;

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
    const hmsId = el.getAttribute('data-hms-id');
    if (hmsId) return '[data-hms-id="' + hmsId + '"]';
    if (el.id && !String(el.id).startsWith('hms-')) return '#' + cssEscape(el.id);

    const parts = [];
    let node = el;
    while (node && node.nodeType === 1 && node !== document.body) {
      let part = node.tagName.toLowerCase();
      if (node.getAttribute('data-hms-id')) {
        parts.unshift('[data-hms-id="' + node.getAttribute('data-hms-id') + '"]');
        break;
      }
      if (node.id && !String(node.id).startsWith('hms-')) {
        parts.unshift('#' + cssEscape(node.id));
        break;
      }
      const parent = node.parentElement;
      if (parent) {
        const same = Array.from(parent.children).filter((c) => c.tagName === node.tagName);
        if (same.length > 1) part += ':nth-of-type(' + (same.indexOf(node) + 1) + ')';
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
    return !!(el && (el.id === 'hms-editor-ui' || el.closest('#hms-editor-ui') || el.closest('#hms-editor-file')));
  }

  function isEditableTarget(el) {
    if (!el || el === document.body || el === document.documentElement) return false;
    if (isEditorChrome(el)) return false;
    if (el.id === 'hms-user-canvas') return false;

    const tag = el.tagName;
    if (['SCRIPT', 'STYLE', 'LINK', 'META', 'NOSCRIPT', 'HTML', 'HEAD'].includes(tag)) return false;

    if (el.hasAttribute('data-hms-user') || el.closest('[data-hms-user]')) {
      return el.hasAttribute('data-hms-user') ? el : el.closest('[data-hms-user]');
    }

    if (tag === 'IMG' || tag === 'BUTTON' || tag === 'A' || tag === 'I' || tag === 'SVG' || tag === 'INPUT' || tag === 'TEXTAREA') return true;
    if (/^H[1-6]$/.test(tag) || tag === 'P' || tag === 'SPAN' || tag === 'LABEL' || tag === 'LI' || tag === 'UL' || tag === 'OL') return true;
    if (tag === 'DIV' || tag === 'SECTION' || tag === 'HEADER' || tag === 'FOOTER' || tag === 'NAV' || tag === 'ARTICLE' || tag === 'MAIN') {
      return true;
    }
    return false;
  }

  function findEditable(start) {
    let el = start;
    while (el && el !== document.body) {
      if (isEditorChrome(el)) return null;
      const hit = isEditableTarget(el);
      if (hit === true) return el;
      if (hit && hit.nodeType === 1) return hit;
      el = el.parentElement;
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
    if (tag === 'input' || tag === 'textarea') return 'Text field';
    if (tag === 'i' || el.querySelector(':scope > i.fa, :scope > i.fas, :scope > i.far')) return 'Icon';
    if (/^h[1-6]$/.test(tag)) return 'Heading (' + tag.toUpperCase() + ')';
    if (tag === 'p' || tag === 'span') return 'Text';
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

  function ensureHandles() {
    if (handlesEl && document.body.contains(handlesEl)) return handlesEl;
    handlesEl = document.createElement('div');
    handlesEl.id = 'hms-editor-ui';
    handlesEl.innerHTML = [
      '<div class="hms-sel-box"></div>',
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
    return handlesEl;
  }

  function updateHandles() {
    if (!designMode || !selectedEl) {
      if (handlesEl) handlesEl.style.display = 'none';
      return;
    }
    const ui = ensureHandles();
    const r = selectedEl.getBoundingClientRect();
    ui.style.display = 'block';
    const box = ui.querySelector('.hms-sel-box');
    box.style.left = r.left + 'px';
    box.style.top = r.top + 'px';
    box.style.width = r.width + 'px';
    box.style.height = r.height + 'px';

    const corners = {
      nw: [r.left, r.top], n: [r.left + r.width / 2, r.top], ne: [r.right, r.top],
      e: [r.right, r.top + r.height / 2], se: [r.right, r.bottom], s: [r.left + r.width / 2, r.bottom],
      sw: [r.left, r.bottom], w: [r.left, r.top + r.height / 2],
    };
    Object.keys(corners).forEach((dir) => {
      const h = ui.querySelector('.hms-handle.' + dir);
      if (!h) return;
      h.style.left = (corners[dir][0] - 5) + 'px';
      h.style.top = (corners[dir][1] - 5) + 'px';
    });
  }

  function clearSelection() {
    if (selectedEl) selectedEl.classList.remove('hms-edit-selected');
    selectedEl = null;
    document.querySelectorAll('.hms-edit-selected').forEach((n) => n.classList.remove('hms-edit-selected'));
    if (handlesEl) handlesEl.style.display = 'none';
  }

  function selectElement(el) {
    clearSelection();
    if (!el) {
      postToParent({ type: 'element-deselected' });
      return;
    }
    selectedEl = el;
    ensureStableId(el);
    el.classList.add('hms-edit-selected');
    const id = getSelector(el);
    el.setAttribute('data-edit-id', id);

    const cs = window.getComputedStyle(el);
    const isImg = el.tagName === 'IMG';
    const bgImage = cs.backgroundImage;
    const bgMatch = bgImage && bgImage !== 'none' ? bgImage.match(/url\(["']?(.*?)["']?\)/) : null;
    const z = parseInt(cs.zIndex, 10);
    if (!isNaN(z) && z > zCounter) zCounter = z;

    postToParent({
      type: 'element-selected',
      id: id,
      label: describeElement(el),
      tag: el.tagName.toLowerCase(),
      text: isImg ? '' : (el.innerText || '').trim().slice(0, 500),
      html: isImg ? '' : el.innerHTML,
      src: isImg ? el.getAttribute('src') : (bgMatch ? bgMatch[1] : ''),
      isImage: isImg || !!bgMatch,
      isIcon: el.tagName === 'I' || !!el.querySelector('i.fa, i.fas, i.far, i.fab'),
      iconClass: el.tagName === 'I' ? el.className.replace(/\bhms-edit-selected\b/g, '').trim()
        : (el.querySelector('i') ? el.querySelector('i').className : ''),
      isUser: el.hasAttribute('data-hms-user'),
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

  function saveElementState(el) {
    if (!el) return;
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

    customizations[id] = entry;
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
        el.style.setProperty(prop, entry[prop]);
      }
    });
    if (entry.hmsId) el.setAttribute('data-hms-id', entry.hmsId);
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

  function findByKey(id) {
    if (!id || id === USER_KEY || id === DELETED_KEY) return null;
    try {
      let el = document.querySelector(id);
      if (el) return el;
    } catch (e) { /* ignore */ }
    const entry = customizations[id];
    if (entry && entry.hmsId) {
      try {
        return document.querySelector('[data-hms-id="' + cssEscape(entry.hmsId) + '"]');
      } catch (e2) { /* ignore */ }
    }
    try {
      return document.querySelector('[data-edit-id="' + id.replace(/"/g, '\\"') + '"]');
    } catch (e3) {
      return null;
    }
  }

  function persistUserElements() {
    const canvas = ensureUserCanvas();
    const list = [];
    canvas.querySelectorAll('[data-hms-user]').forEach((el) => {
      list.push({
        id: el.getAttribute('data-hms-id'),
        type: el.getAttribute('data-hms-type') || 'element',
        tag: el.tagName.toLowerCase(),
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
    customizations[USER_KEY] = list;
  }

  function renderUserElements() {
    const canvas = ensureUserCanvas();
    const keepSelected = selectedEl && selectedEl.hasAttribute('data-hms-user')
      ? selectedEl.getAttribute('data-hms-id') : null;
    canvas.innerHTML = '';
    (customizations[USER_KEY] || []).forEach((item) => {
      const el = buildUserElement(item);
      canvas.appendChild(el);
      if (keepSelected && item.id === keepSelected) {
        selectedEl = el;
        el.classList.add('hms-edit-selected');
      }
    });
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
    (customizations[DELETED_KEY] || []).forEach((id) => {
      const el = findByKey(id);
      if (el) el.style.display = 'none';
    });
  }

  function applyAllCustomizations() {
    applying = true;
    Object.keys(customizations).forEach((id) => {
      if (id === USER_KEY || id === DELETED_KEY) return;
      const el = findByKey(id);
      if (el) {
        el.setAttribute('data-edit-id', id);
        applyEntry(el, customizations[id]);
      }
    });
    applyDeleted();
    renderUserElements();
    applying = false;
    updateHandles();
  }

  function scheduleReapply() {
    if (applying) return;
    clearTimeout(reapplyTimer);
    reapplyTimer = setTimeout(function () {
      if (dragState || resizeState) return;
      applyAllCustomizations();
    }, 150);
  }

  function promoteToAbsolute(el) {
    const cs = window.getComputedStyle(el);
    if (cs.position === 'absolute' || cs.position === 'fixed') return;
    const parent = el.offsetParent || document.body;
    const er = el.getBoundingClientRect();
    const pr = parent.getBoundingClientRect();
    const width = er.width;
    const height = er.height;
    el.style.position = 'absolute';
    el.style.left = (er.left - pr.left + (parent.scrollLeft || 0)) + 'px';
    el.style.top = (er.top - pr.top + (parent.scrollTop || 0)) + 'px';
    el.style.width = width + 'px';
    el.style.height = height + 'px';
    el.style.margin = '0';
    if (!el.style.zIndex || el.style.zIndex === 'auto') el.style.zIndex = String(++zCounter);
  }

  function onPointerDownMove(e) {
    if (!designMode || !canEdit || !selectedEl) return;
    if (e.button !== 0) return;
    if (e.target.closest && e.target.closest('.hms-handle')) return;
    if (findEditable(e.target) !== selectedEl && !selectedEl.contains(e.target)) return;
    if (e.target.isContentEditable) return;

    e.preventDefault();
    promoteToAbsolute(selectedEl);
    const startLeft = parseFloat(selectedEl.style.left) || 0;
    const startTop = parseFloat(selectedEl.style.top) || 0;
    dragState = {
      el: selectedEl,
      startX: e.clientX,
      startY: e.clientY,
      left: startLeft,
      top: startTop,
    };
  }

  function onResizeStart(e) {
    if (!designMode || !canEdit || !selectedEl) return;
    e.preventDefault();
    e.stopPropagation();
    promoteToAbsolute(selectedEl);
    const r = selectedEl.getBoundingClientRect();
    const parent = selectedEl.offsetParent || document.body;
    const pr = parent.getBoundingClientRect();
    resizeState = {
      el: selectedEl,
      dir: e.currentTarget.getAttribute('data-dir'),
      startX: e.clientX,
      startY: e.clientY,
      left: r.left - pr.left,
      top: r.top - pr.top,
      width: r.width,
      height: r.height,
    };
  }

  function onPointerMove(e) {
    if (dragState) {
      const dx = e.clientX - dragState.startX;
      const dy = e.clientY - dragState.startY;
      dragState.el.style.left = (dragState.left + dx) + 'px';
      dragState.el.style.top = (dragState.top + dy) + 'px';
      updateHandles();
      return;
    }
    if (resizeState) {
      const dx = e.clientX - resizeState.startX;
      const dy = e.clientY - resizeState.startY;
      let { left, top, width, height } = resizeState;
      const dir = resizeState.dir;
      if (dir.includes('e')) width = Math.max(24, resizeState.width + dx);
      if (dir.includes('s')) height = Math.max(24, resizeState.height + dy);
      if (dir.includes('w')) {
        width = Math.max(24, resizeState.width - dx);
        left = resizeState.left + dx;
      }
      if (dir.includes('n')) {
        height = Math.max(24, resizeState.height - dy);
        top = resizeState.top + dy;
      }
      resizeState.el.style.left = left + 'px';
      resizeState.el.style.top = top + 'px';
      resizeState.el.style.width = width + 'px';
      resizeState.el.style.height = height + 'px';
      updateHandles();
    }
  }

  function onPointerUp() {
    if (dragState) {
      saveElementState(dragState.el);
      dragState = null;
    }
    if (resizeState) {
      saveElementState(resizeState.el);
      resizeState = null;
    }
  }

  function onClick(e) {
    if (!designMode) return;
    if (isEditorChrome(e.target)) return;
    const el = findEditable(e.target);
    if (!el) {
      clearSelection();
      postToParent({ type: 'element-deselected' });
      return;
    }
    e.preventDefault();
    e.stopPropagation();
    selectElement(el);
  }

  function onDblClick(e) {
    if (!designMode || !canEdit) return;
    const el = findEditable(e.target);
    if (!el || ['IMG', 'I', 'SVG', 'INPUT', 'TEXTAREA', 'SELECT'].includes(el.tagName)) return;
    e.preventDefault();
    e.stopPropagation();
    selectElement(el);
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

  function applyImageToSelected(url) {
    if (!selectedEl || !url) return;
    if (selectedEl.tagName === 'IMG') {
      selectedEl.setAttribute('src', url);
    } else {
      selectedEl.style.backgroundImage = "url('" + url + "')";
      selectedEl.style.backgroundSize = selectedEl.style.backgroundSize || 'cover';
      selectedEl.style.backgroundPosition = selectedEl.style.backgroundPosition || 'center';
    }
    saveElementState(selectedEl);
  }

  function setDesignMode(on) {
    if (on && !canEdit) {
      designMode = false;
      document.body.classList.remove('hms-design-mode');
      clearSelection();
      postToParent({ type: 'mode-ack', designMode: false, canEdit: false });
      return;
    }
    designMode = !!on;
    document.body.classList.toggle('hms-design-mode', designMode);
    if (!designMode) {
      clearSelection();
      document.querySelectorAll('[contenteditable="true"]').forEach((n) => {
        n.contentEditable = 'false';
      });
    } else {
      ensureHandles();
      ensureUserCanvas();
    }
    postToParent({ type: 'mode-ack', designMode: designMode, canEdit: canEdit });
  }

  function applyFromParent(data) {
    if (!canEdit) return;
    if (!selectedEl && data.id) selectedEl = findByKey(data.id);
    if (!selectedEl) return;

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
    if (data.src) applyImageToSelected(data.src);
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
      if (/^h[1-6]$|^p$/.test(selectedEl.tagName.toLowerCase()) && !selectedEl.hasAttribute('data-hms-user')) {
        const neu = document.createElement(tag);
        neu.innerHTML = selectedEl.innerHTML;
        neu.className = selectedEl.className;
        neu.style.cssText = selectedEl.style.cssText;
        const id = selectedEl.getAttribute('data-edit-id') || getSelector(selectedEl);
        const hmsId = selectedEl.getAttribute('data-hms-id');
        selectedEl.replaceWith(neu);
        selectedEl = neu;
        if (hmsId) selectedEl.setAttribute('data-hms-id', hmsId);
        selectedEl.setAttribute('data-edit-id', id);
        selectedEl.classList.add('hms-edit-selected');
      }
    }
    saveElementState(selectedEl);
    updateHandles();
  }

  function addElement(type) {
    if (!canEdit || !designMode) return;
    ensureUserCanvas();
    const defaults = {
      text: { type: 'text', text: 'Double-click to edit', styles: { left: '100px', top: '140px', width: '280px', color: '#111827', 'font-size': '20px', 'font-weight': '600' } },
      button: { type: 'button', text: 'Book Now', styles: { left: '100px', top: '200px', padding: '12px 24px', 'background-color': '#0ea5e9', color: '#fff', 'border-radius': '8px', border: 'none', 'font-weight': '700', cursor: 'pointer' } },
      image: { type: 'image', styles: { left: '100px', top: '160px', width: '280px', height: '180px', 'object-fit': 'cover', 'border-radius': '12px' } },
      textfield: { type: 'textfield', text: 'Enter text…', styles: { left: '100px', top: '220px', width: '260px', padding: '10px 12px', 'border-radius': '8px', border: '1px solid #cbd5e1', 'background-color': '#fff' } },
      icon: { type: 'icon', className: 'fas fa-hotel', styles: { left: '120px', top: '160px', 'font-size': '42px', color: '#0ea5e9' } },
      card: {
        type: 'card',
        html: '<h3 style="margin:0 0 8px;font-size:18px;">Suite</h3><p style="margin:0;opacity:.8;">Comfortable rooms with city views.</p>',
        styles: { left: '100px', top: '160px', width: '260px', padding: '20px', 'background-color': '#ffffff', color: '#0f172a', 'border-radius': '14px', 'box-shadow': '0 12px 30px rgba(0,0,0,.18)' },
      },
      container: {
        type: 'container',
        html: '',
        styles: { left: '80px', top: '120px', width: '320px', height: '200px', padding: '16px', 'background-color': 'rgba(255,255,255,.12)', 'border-radius': '12px', border: '1px dashed rgba(148,163,184,.6)' },
      },
    };
    const base = defaults[type] || defaults.text;
    const item = Object.assign({ id: uid('user') }, base);
    if (!customizations[USER_KEY]) customizations[USER_KEY] = [];
    customizations[USER_KEY].push(item);
    renderUserElements();
    const el = document.querySelector('[data-hms-id="' + item.id + '"]');
    if (el) selectElement(el);
    notifyChanged();
  }

  function deleteSelected() {
    if (!canEdit || !selectedEl) return;
    if (selectedEl.hasAttribute('data-hms-user')) {
      const id = selectedEl.getAttribute('data-hms-id');
      customizations[USER_KEY] = (customizations[USER_KEY] || []).filter((x) => x.id !== id);
      clearSelection();
      renderUserElements();
      notifyChanged();
      postToParent({ type: 'element-deselected' });
      return;
    }
    const key = selectedEl.getAttribute('data-edit-id') || getSelector(selectedEl);
    selectedEl.style.display = 'none';
    if (!customizations[DELETED_KEY]) customizations[DELETED_KEY] = [];
    if (!customizations[DELETED_KEY].includes(key)) customizations[DELETED_KEY].push(key);
    if (customizations[key]) customizations[key].display = 'none';
    else customizations[key] = { display: 'none' };
    clearSelection();
    notifyChanged();
    postToParent({ type: 'element-deselected' });
  }

  function duplicateSelected() {
    if (!canEdit || !selectedEl) return;
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

  // Styles
  const style = document.createElement('style');
  style.textContent = `
    #hms-user-canvas {
      position: absolute; left: 0; top: 0; width: 100%; min-height: 100%;
      pointer-events: none; z-index: 50;
    }
    body.hms-design-mode #hms-user-canvas [data-hms-user] {
      pointer-events: auto; cursor: move;
    }
    #hms-user-canvas [data-hms-user] { pointer-events: none; }
    body.hms-design-mode [data-hms-id],
    body.hms-design-mode h1, body.hms-design-mode h2, body.hms-design-mode h3,
    body.hms-design-mode h4, body.hms-design-mode h5, body.hms-design-mode h6,
    body.hms-design-mode p, body.hms-design-mode span, body.hms-design-mode button,
    body.hms-design-mode a, body.hms-design-mode img, body.hms-design-mode i,
    body.hms-design-mode input, body.hms-design-mode textarea,
    body.hms-design-mode div, body.hms-design-mode section {
      cursor: pointer;
    }
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
    body.hms-design-mode::after {
      content: 'Build mode — click to select · drag to move · handles to resize · double-click text';
      position: fixed; left: 50%; bottom: 16px; transform: translateX(-50%);
      background: rgba(6,182,212,0.95); color: #042f2e; font: 600 11px/1 Inter, system-ui, sans-serif;
      padding: 8px 14px; border-radius: 999px; z-index: 99999; pointer-events: none;
      box-shadow: 0 8px 24px rgba(0,0,0,0.35); white-space: nowrap;
    }
  `;
  document.head.appendChild(style);

  document.addEventListener('click', onClick, true);
  document.addEventListener('dblclick', onDblClick, true);
  document.addEventListener('mousedown', onPointerDownMove, true);
  document.addEventListener('mousemove', onPointerMove, true);
  document.addEventListener('mouseup', onPointerUp, true);
  window.addEventListener('scroll', updateHandles, true);
  window.addEventListener('resize', updateHandles);

  document.addEventListener('keydown', function (e) {
    if (!designMode || !canEdit || !selectedEl) return;
    if (e.target && (e.target.isContentEditable || /INPUT|TEXTAREA|SELECT/.test(e.target.tagName))) return;
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
        break;
      case 'apply-edit':
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
        ensureFileInput().click();
        break;
      case 'image-uploaded':
        if (data.url) applyImageToSelected(data.url);
        break;
      case 'reset-styles':
        if (selectedEl) {
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
      case 'add-element':
        addElement(data.elementType || 'text');
        break;
      case 'delete-element':
        deleteSelected();
        break;
      case 'duplicate-element':
        duplicateSelected();
        break;
      case 'layer-element':
        changeLayer(data.direction || 'front');
        break;
      default:
        break;
    }
  });

  const root = document.getElementById('root') || document.body;
  const mo = new MutationObserver(function () {
    if (applying || dragState || resizeState) return;
    if (designMode || Object.keys(customizations).length) scheduleReapply();
  });
  mo.observe(root, { childList: true, subtree: true });

  function boot() {
    ensureUserCanvas();
    applyAllCustomizations();
    if (!canEdit) setDesignMode(false);
    postToParent({ type: 'editor-ready', customizations: customizations, canEdit: canEdit });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { setTimeout(boot, 400); });
  } else {
    setTimeout(boot, 400);
  }

  window.HMSTemplateEditor = {
    getCustomizations: () => customizations,
    setCustomizations: (c) => { customizations = normalizeCustomizations(c); applyAllCustomizations(); },
    selectElement,
    setDesignMode,
    addElement,
    deleteSelected,
    duplicateSelected,
    changeLayer,
  };
})();
