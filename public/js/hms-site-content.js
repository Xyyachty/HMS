/**
 * Editable hotel site content: navigation, room cards, restaurant menus.
 * Persisted inside template customizations (__navLinks / __rooms / __menus).
 */
(function (window) {
  'use strict';

  const NAV_KEY = '__navLinks';
  const ROOMS_KEY = '__rooms';
  const MENUS_KEY = '__menus';
  const CARD_IMAGES_KEY = '__cardImages';
  const CONTENT_KEYS = [NAV_KEY, ROOMS_KEY, MENUS_KEY, CARD_IMAGES_KEY];

  const DEFAULT_NAV = [
    { id: 'nav-home', key: 'home', label: 'Home' },
    { id: 'nav-rooms', key: 'rooms', label: 'Rooms' },
    { id: 'nav-restaurant', key: 'restaurant', label: 'Restaurant' },
    { id: 'nav-experience', key: 'experience', label: 'Experience' },
  ];

  const DEFAULT_MENUS = [
    { id: 'menu-1', name: 'Hokkaido Scallop Tartare', sub: 'yuzu, sea urchin, micro herbs', price: '$32', category: 'Dining' },
    { id: 'menu-2', name: 'Wagyu A5 Carpaccio', sub: 'truffle jus, parmesan crisp, rocket', price: '$48', category: 'Dining' },
    { id: 'menu-3', name: 'Pan-Seared Dover Sole', sub: 'brown butter, capers, lemon beurre blanc', price: '$54', category: 'Dining' },
    { id: 'menu-4', name: 'Roasted Rhubarb Souffle', sub: 'vanilla bean creme anglaise, pistachio', price: '$22', category: 'Dining' },
    { id: 'menu-5', name: 'The SPC Old Fashioned', sub: '25yr bourbon, demerara, aromatic bitters', price: '$26', category: 'Bar' },
    { id: 'menu-6', name: 'Gold Leaf Negroni', sub: 'gin, Campari, sweet vermouth, 24k gold leaf', price: '$28', category: 'Bar' },
  ];

  const listeners = [];

  function uid(prefix) {
    return (prefix || 'item') + '-' + Math.random().toString(36).slice(2, 9);
  }

  function getCustomizations() {
    if (window.HMSTemplateEditor && typeof window.HMSTemplateEditor.getCustomizations === 'function') {
      return window.HMSTemplateEditor.getCustomizations() || {};
    }
    return Object.assign({}, window.__HMS_CUSTOMIZATIONS__ || {});
  }

  function persist(next) {
    window.__HMS_CUSTOMIZATIONS__ = next;
    if (window.HMSTemplateEditor && typeof window.HMSTemplateEditor.setCustomizations === 'function') {
      window.HMSTemplateEditor.setCustomizations(next);
    }
    if (window.HMSTemplateEditor && typeof window.HMSTemplateEditor.notifyChanged === 'function') {
      window.HMSTemplateEditor.notifyChanged();
    } else if (window.parent && window.parent !== window) {
      window.parent.postMessage({
        source: 'hms-template',
        type: 'customizations-changed',
        customizations: next,
      }, '*');
    }
    listeners.forEach((fn) => {
      try { fn(getSnapshot()); } catch (e) { /* ignore */ }
    });
    window.dispatchEvent(new CustomEvent('hms-site-content-changed', { detail: getSnapshot() }));
  }

  function patch(key, value) {
    const c = Object.assign({}, getCustomizations());
    c[key] = value;
    persist(c);
  }

  function editablePages() {
    if (Array.isArray(window.__HMS_EDITABLE_PAGES__)) return window.__HMS_EDITABLE_PAGES__;
    const auth = window.__HMS_HOTEL_AUTH__;
    if (auth && Array.isArray(auth.editable_pages)) return auth.editable_pages;
    return [];
  }

  function canEdit() {
    return window.__HMS_CAN_EDIT__ === true;
  }

  function canEditNav() {
    return canEdit() && editablePages().indexOf('home') !== -1;
  }

  function canEditRooms() {
    // Room Management owns the Rooms page; Front Desk can also manage
    // room cards from the Home "Available Rooms" section.
    const pages = editablePages();
    return canEdit() && (pages.indexOf('rooms') !== -1 || pages.indexOf('home') !== -1);
  }

  function canEditMenus() {
    return canEdit() && editablePages().indexOf('restaurant') !== -1;
  }

  function canEditExperiences() {
    return canEdit() && editablePages().indexOf('experience') !== -1;
  }

  function getNav() {
    const c = getCustomizations();
    const entry = c[NAV_KEY];
    if (entry && Array.isArray(entry.items) && entry.items.length) {
      return entry.items.map((item) => Object.assign({}, item));
    }
    return DEFAULT_NAV.map((item) => Object.assign({}, item));
  }

  function setNav(items) {
    if (!canEditNav()) return false;
    patch(NAV_KEY, {
      page: 'home',
      items: (items || []).map((item) => ({
        id: item.id || uid('nav'),
        key: item.key || 'home',
        label: item.label || 'Link',
      })),
    });
    return true;
  }

  function getRooms(fallback) {
    const c = getCustomizations();
    const entry = c[ROOMS_KEY];
    if (entry && Array.isArray(entry.items) && entry.items.length) {
      return entry.items.map((item) => Object.assign({}, item));
    }
    return (fallback || []).map((item) => Object.assign({}, item));
  }

  function setRooms(items) {
    if (!canEditRooms()) return false;
    patch(ROOMS_KEY, {
      page: 'rooms',
      items: (items || []).map((item) => Object.assign({}, item, {
        id: item.id || uid('room'),
      })),
    });
    return true;
  }

  function getMenus(fallback) {
    const c = getCustomizations();
    const entry = c[MENUS_KEY];
    if (entry && Array.isArray(entry.items) && entry.items.length) {
      return entry.items.map((item) => Object.assign({}, item));
    }
    return (fallback || DEFAULT_MENUS).map((item) => Object.assign({}, item));
  }

  function setMenus(items) {
    if (!canEditMenus()) return false;
    patch(MENUS_KEY, {
      page: 'restaurant',
      items: (items || []).map((item) => Object.assign({}, item, {
        id: item.id || uid('menu'),
      })),
    });
    return true;
  }

  function addNavLink(partial) {
    const list = getNav();
    const item = {
      id: uid('nav'),
      key: (partial && partial.key) || 'home',
      label: (partial && partial.label) || 'New Link',
    };
    list.push(item);
    setNav(list);
    return item;
  }

  function updateNavLink(id, patchData) {
    const list = getNav().map((item) => (item.id === id ? Object.assign({}, item, patchData) : item));
    setNav(list);
  }

  function removeNavLink(id) {
    setNav(getNav().filter((item) => item.id !== id));
  }

  function moveNavLink(id, direction) {
    const list = getNav();
    const idx = list.findIndex((item) => item.id === id);
    if (idx < 0) return;
    const target = direction === 'left' ? idx - 1 : idx + 1;
    if (target < 0 || target >= list.length) return;
    const tmp = list[idx];
    list[idx] = list[target];
    list[target] = tmp;
    setNav(list);
  }

  function addRoom(partial, fallbackDefaults) {
    const list = getRooms(fallbackDefaults);
    const n = list.length + 1;
    const item = Object.assign({
      id: uid('room'),
      name: 'New Room ' + n,
      label: 'Standard',
      category: 'Classic',
      price: 200,
      img: 'https://picsum.photos/seed/newroom' + n + '/800/600.jpg',
      desc: 'Add a short description for this room.',
      amenities: [
        { icon: 'fa-bed', text: 'Bed' },
        { icon: 'fa-wifi', text: 'WiFi' },
      ],
    }, partial || {});
    list.push(item);
    setRooms(list);
    return item;
  }

  function updateRoom(id, patchData, fallbackDefaults) {
    const list = getRooms(fallbackDefaults).map((item) => (
      item.id === id ? Object.assign({}, item, patchData) : item
    ));
    setRooms(list);
  }

  function removeRoom(id, fallbackDefaults) {
    setRooms(getRooms(fallbackDefaults).filter((item) => item.id !== id));
  }

  function addMenu(partial, fallbackDefaults) {
    const list = getMenus(fallbackDefaults);
    const n = list.length + 1;
    const item = Object.assign({
      id: uid('menu'),
      name: 'New Menu Item ' + n,
      sub: 'Add a short description',
      price: '$24',
      category: 'Dining',
    }, partial || {});
    list.push(item);
    setMenus(list);
    return item;
  }

  function updateMenu(id, patchData, fallbackDefaults) {
    const list = getMenus(fallbackDefaults).map((item) => (
      item.id === id ? Object.assign({}, item, patchData) : item
    ));
    setMenus(list);
  }

  function removeMenu(id, fallbackDefaults) {
    setMenus(getMenus(fallbackDefaults).filter((item) => item.id !== id));
  }

  function getCardImages() {
    const c = getCustomizations();
    const entry = c[CARD_IMAGES_KEY];
    if (entry && entry.map && typeof entry.map === 'object') {
      return Object.assign({}, entry.map);
    }
    return {};
  }

  function cardImageKey(kind, id) {
    return String(kind || 'card') + ':' + String(id || '');
  }

  function getCardImage(kind, id, fallback) {
    const map = getCardImages();
    const url = map[cardImageKey(kind, id)];
    return url || fallback || '';
  }

  function setCardImage(kind, id, url) {
    if (!id || !url) return;
    const map = getCardImages();
    map[cardImageKey(kind, id)] = String(url);
    patch(CARD_IMAGES_KEY, { map: map });
  }

  /** Open a file picker and return an image data-URL (works inside the builder iframe). */
  function pickImageFile(onPicked) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.style.display = 'none';
    input.setAttribute('data-hms-no-edit', '1');
    document.body.appendChild(input);
    input.addEventListener('change', function () {
      const file = input.files && input.files[0];
      if (!file) {
        if (input.parentNode) input.parentNode.removeChild(input);
        return;
      }
      const reader = new FileReader();
      reader.onload = function () {
        if (typeof onPicked === 'function') onPicked(String(reader.result || ''));
        if (input.parentNode) input.parentNode.removeChild(input);
      };
      reader.onerror = function () {
        if (input.parentNode) input.parentNode.removeChild(input);
      };
      reader.readAsDataURL(file);
    });
    input.click();
  }

  function getSnapshot() {
    return {
      navLinks: getNav(),
      rooms: getRooms(),
      menus: getMenus(),
      cardImages: getCardImages(),
      canEditNav: canEditNav(),
      canEditRooms: canEditRooms(),
      canEditMenus: canEditMenus(),
      canEditExperiences: canEditExperiences(),
    };
  }

  function subscribe(fn) {
    if (typeof fn === 'function') listeners.push(fn);
    return function unsubscribe() {
      const i = listeners.indexOf(fn);
      if (i >= 0) listeners.splice(i, 1);
    };
  }

  function safePrompt(message, defaultValue) {
    const fallback = defaultValue == null ? '' : String(defaultValue);
    try {
      // Prompts inside the template iframe are often blocked; use the parent frame.
      const host = (window.top && window.top.prompt) ? window.top : window;
      return host.prompt(message, fallback);
    } catch (err) {
      return fallback;
    }
  }

  function safeConfirm(message) {
    try {
      const host = (window.top && window.top.confirm) ? window.top : window;
      return !!host.confirm(message);
    } catch (err) {
      return true;
    }
  }

  window.HMSSiteContent = {
    NAV_KEY,
    ROOMS_KEY,
    MENUS_KEY,
    CARD_IMAGES_KEY,
    CONTENT_KEYS,
    DEFAULT_NAV,
    DEFAULT_MENUS,
    getNav,
    setNav,
    addNavLink,
    updateNavLink,
    removeNavLink,
    moveNavLink,
    getRooms,
    setRooms,
    addRoom,
    updateRoom,
    removeRoom,
    getMenus,
    setMenus,
    addMenu,
    updateMenu,
    removeMenu,
    getCardImages,
    getCardImage,
    setCardImage,
    pickImageFile,
    canEditNav,
    canEditRooms,
    canEditMenus,
    canEditExperiences,
    getSnapshot,
    subscribe,
    safePrompt,
    safeConfirm,
    refreshFromEditor: function () {
      listeners.forEach((fn) => {
        try { fn(getSnapshot()); } catch (e) { /* ignore */ }
      });
    },
  };

  window.addEventListener('hms-hotel-auth', function () {
    window.HMSSiteContent.refreshFromEditor();
  });

  window.addEventListener('message', function (event) {
    const data = event.data || {};
    if (!data || data.source !== 'hms-parent') return;
    if (data.type === 'load-customizations' || data.type === 'set-can-edit' || data.type === 'set-editable-pages') {
      setTimeout(function () { window.HMSSiteContent.refreshFromEditor(); }, 50);
    }
  });
})(window);
