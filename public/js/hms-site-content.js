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
  const HERO_SLIDES_KEY = '__heroSlides';
  const RESERVATION_NOTIFICATIONS_KEY = '__reservationNotifications';
  const ROOM_RESERVATIONS_KEY = '__roomReservations';
  const BRAND_NAME_KEY = '__brandName';
  const ROOM_CARD_STYLE_KEY = '__roomCardStyle';
  const MENU_CARD_STYLE_KEY = '__menuCardStyle';
  const SITE_COLORS_KEY = '__siteColors';
  const CONTENT_KEYS = [NAV_KEY, BRAND_NAME_KEY, ROOM_CARD_STYLE_KEY, MENU_CARD_STYLE_KEY, SITE_COLORS_KEY, ROOMS_KEY, MENUS_KEY, CARD_IMAGES_KEY, HERO_SLIDES_KEY, RESERVATION_NOTIFICATIONS_KEY, ROOM_RESERVATIONS_KEY];

  /**
   * The hotel name shown in the header and the footer.
   *
   * Stored as a one-item collection rather than a plain string because
   * TemplateCustomizationStore writes a scalar customization to
   * template_elements.display_value and reads it back as an array — a string
   * would not survive one save/reload round trip. The field has to be `label`
   * so TemplateDiff::itemTitle() prints the name in the faculty review instead
   * of the item id.
   */
  const BRAND_NAME_ID = 'brand-name';
  const DEFAULT_BRAND_NAME = 'SPC HOTEL';
  const BRAND_NAME_MAX = 60;
  const NAV_LABEL_MAX = 24;

  /** Same one-item-collection shape, for the same round-trip reason. */
  const ROOM_CARD_STYLE_ID = 'room-card';
  const MENU_CARD_STYLE_ID = 'menu-card';

  const DEFAULT_NAV = [
    { id: 'nav-home', key: 'home', label: 'Home' },
    { id: 'nav-rooms', key: 'rooms', label: 'Rooms' },
    { id: 'nav-restaurant', key: 'restaurant', label: 'Restaurant' },
    { id: 'nav-amenities', key: 'amenities', label: 'Amenities' },
    { id: 'nav-experience', key: 'experience', label: 'Experience' },
  ];

  const DEFAULT_MENUS = [
    { id: 'menu-1', name: 'Hokkaido Scallop Tartare', sub: 'yuzu, sea urchin, micro herbs', price: '\u20B11,800', category: 'Appetizers', img: 'https://picsum.photos/seed/scalloptartare/800/600.jpg' },
    { id: 'menu-2', name: 'Wagyu A5 Carpaccio', sub: 'truffle jus, parmesan crisp, rocket', price: '\u20B12,700', category: 'Appetizers', img: 'https://picsum.photos/seed/wagyucarpaccio/800/600.jpg' },
    { id: 'menu-3', name: 'Roasted Tomato Bisque', sub: 'basil oil, grilled sourdough croutons', price: '\u20B1680', category: 'Soups', img: 'https://picsum.photos/seed/tomatobisque/800/600.jpg' },
    { id: 'menu-4', name: 'Seafood Chowder', sub: 'prawns, mussels, cream, chives', price: '\u20B1950', category: 'Soups', img: 'https://picsum.photos/seed/seafoodchowder/800/600.jpg' },
    { id: 'menu-5', name: 'Pan-Seared Dover Sole', sub: 'brown butter, capers, lemon beurre blanc', price: '\u20B13,000', category: 'Main Dishes', img: 'https://picsum.photos/seed/doversole/800/600.jpg' },
    { id: 'menu-6', name: 'Grilled Angus Ribeye', sub: 'garlic butter, roasted vegetables, jus', price: '\u20B13,800', category: 'Main Dishes', img: 'https://picsum.photos/seed/angusribeye/800/600.jpg' },
    { id: 'menu-7', name: 'Herb-Crusted Lamb Rack', sub: 'mint reduction, potato puree', price: '\u20B14,200', category: 'Main Dishes', img: 'https://picsum.photos/seed/lambrack/800/600.jpg' },
    { id: 'menu-8', name: 'Roasted Rhubarb Souffle', sub: 'vanilla bean creme anglaise, pistachio', price: '\u20B11,200', category: 'Desserts', img: 'https://picsum.photos/seed/rhubarbsouffle/800/600.jpg' },
    { id: 'menu-9', name: 'Dark Chocolate Fondant', sub: 'salted caramel ice cream', price: '\u20B11,100', category: 'Desserts', img: 'https://picsum.photos/seed/chocfondant/800/600.jpg' },
    { id: 'menu-10', name: 'The SPC Old Fashioned', sub: '25yr bourbon, demerara, aromatic bitters', price: '\u20B11,450', category: 'Beverages', img: 'https://picsum.photos/seed/oldfashioned/800/600.jpg' },
    { id: 'menu-11', name: 'Gold Leaf Negroni', sub: 'gin, Campari, sweet vermouth, 24k gold leaf', price: '\u20B11,550', category: 'Beverages', img: 'https://picsum.photos/seed/goldnegroni/800/600.jpg' },
    { id: 'menu-12', name: 'Fresh Calamansi Iced Tea', sub: 'house-brewed, lightly sweetened', price: '\u20B1280', category: 'Beverages', img: 'https://picsum.photos/seed/calamansitea/800/600.jpg' },
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

  /**
   * Feature → allowed role keys (extend here for future UI permissions).
   * room_management_ui: Room Manager / System Administrator only.
   * front_desk must never see room_management_ui.
   * order_ui: taking a food order is a Front Desk / Restaurant workflow.
   */
  const FEATURE_ROLES = {
    room_management_ui: ['room_management', 'administrator'],
    restaurant_ui: ['restaurant_management', 'administrator'],
    order_ui: ['front_desk', 'restaurant_management', 'administrator'],
  };

  function getBuilderRole() {
    return window.__HMS_BUILDER_ROLE__ || null;
  }

  /**
   * The Mini Portfolio — a team's hotel site opened by a guest with no login.
   *
   * Answered explicitly rather than by picking a builder role that happens to produce
   * the right booleans, because neither existing value does: 'front_desk' switches
   * Order Now on (room service needs a checked-in room and the server would refuse a
   * guest), and null switches the Restaurant staff tools on through the early return
   * in canUseRestaurantUi().
   */
  function isPublicSite() {
    return window.__HMS_PUBLIC__ === true;
  }

  function getAuthRoles() {
    const auth = window.__HMS_HOTEL_AUTH__;
    if (auth && Array.isArray(auth.roles)) return auth.roles.slice();
    return [];
  }

  /**
   * In the department builder, the active module role is authoritative
   * (so Front Desk never sees Room Management UI even if staff has multiple roles).
   * Outside the builder, fall back to hotel staff auth roles.
   */
  function currentRoles() {
    const builderRole = getBuilderRole();
    if (builderRole) return [builderRole];
    return getAuthRoles();
  }

  function canAccess(feature) {
    const allowed = FEATURE_ROLES[feature];
    if (!allowed || !allowed.length) return false;
    const roles = currentRoles();
    for (let i = 0; i < allowed.length; i += 1) {
      if (roles.indexOf(allowed[i]) !== -1) return true;
    }
    return false;
  }

  function canUseRoomManagementUi() {
    if (isPublicSite()) return false;
    return canAccess('room_management_ui');
  }

  /**
   * Restaurant staff tools belong to the Restaurant module. Inside the builder the
   * active module wins, so a member who also holds another role does not carry the
   * Restaurant tools into that module. Outside the builder there is no active
   * module, so the server's can_manage answer decides on its own.
   */
  function canUseRestaurantUi() {
    // Before the builder-role check: a guest has no builder role, and the early return
    // below would otherwise hand them the Restaurant staff tools.
    if (isPublicSite()) return false;
    if (!getBuilderRole()) return true;
    return canAccess('restaurant_ui');
  }

  /**
   * Reserve Now is a Front Desk workflow. Hide it whenever the user is in the
   * Room Management module so reservations stay exclusive to Front Desk.
   */
  function canReserveRooms() {
    // Booking is the whole point of the public site, so this is the one that stays on.
    if (isPublicSite()) return true;
    return !canUseRoomManagementUi();
  }

  /**
   * Order Now on a menu item is only taken by Front Desk or Restaurant staff.
   * Inside the builder the active module role decides; outside the builder the
   * staff member's own roles do. Guests and other modules only browse the menu.
   */
  function canOrderMenu() {
    // Room service is placed by Front Desk against a checked-in room. A visitor
    // browsing the menu has neither, and the server would 403 the attempt.
    if (isPublicSite()) return false;
    return canAccess('order_ui');
  }

  function canEditNav() {
    return canEdit() && editablePages().indexOf('home') !== -1;
  }

  /**
   * The hotel name belongs to Front Desk, like the navigation, not to every
   * role like the logo. Because only one role's row can ever hold the key,
   * filterCustomizationsForRole() strips it everywhere else and no
   * last-write-wins claim (the claimSharedLogo() treatment) is needed.
   */
  function canEditBrandName() {
    return canEdit() && editablePages().indexOf('home') !== -1;
  }

  function canEditHeroSlides() {
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

  /**
   * One logo for the whole site, under a single key — see cardImageKey('brand',
   * 'logo'). It is deliberately not gated on a particular page: there is no page
   * that owns it, and every role that can edit anything could already change the
   * logo on its own section before this became shared, so gating it on Home
   * would take that away rather than merely redirect it.
   *
   * A change therefore applies site-wide, and the most recent one wins — see
   * HotelTemplateBuilder::claimSharedLogo().
   */
  function canEditLogo() {
    return canEdit();
  }

  /**
   * The header carries exactly the five links in DEFAULT_NAV, in that order.
   * Students rename them; they cannot add, remove or repoint one.
   *
   * Saved lists from before that rule still exist, so every read and every
   * write goes through here: a saved label is kept when its `key` matches a
   * canonical link, and everything else — extra links, missing links, saved
   * ids — is discarded. Matching on `key` rather than `id` means a link that
   * was repointed at another page loses its label instead of carrying it onto
   * the wrong slot.
   */
  function reconcileNav(saved) {
    const byKey = {};
    (Array.isArray(saved) ? saved : []).forEach((item) => {
      if (!item) return;
      const key = String(item.key == null ? '' : item.key).trim();
      if (!key || Object.prototype.hasOwnProperty.call(byKey, key)) return;
      byKey[key] = item;
    });
    return DEFAULT_NAV.map((base) => {
      const match = byKey[base.key];
      const label = match && typeof match.label === 'string' ? match.label.trim() : '';
      return { id: base.id, key: base.key, label: label || base.label };
    });
  }

  function getNav() {
    const c = getCustomizations();
    const entry = c[NAV_KEY];
    return reconcileNav(entry && entry.items);
  }

  function setNav(items) {
    if (!canEditNav()) return false;
    patch(NAV_KEY, { page: 'home', items: reconcileNav(items) });
    return true;
  }

  /**
   * One colour for every room card, on the Home section and the Rooms page
   * alike. Empty means the template's own colour, so a team that never touches
   * this looks exactly as it does today.
   */
  function getRoomCardBg() {
    const c = getCustomizations();
    const entry = c[ROOM_CARD_STYLE_KEY];
    const item = entry && Array.isArray(entry.items) ? entry.items[0] : null;
    const bg = item && typeof item.bg === 'string' ? item.bg.trim() : '';
    return bg;
  }

  function setRoomCardBg(bg) {
    if (!canEditRoomCardStyle()) return false;
    const clean = String(bg == null ? '' : bg).trim().slice(0, 32);
    patch(ROOM_CARD_STYLE_KEY, {
      page: 'rooms',
      items: [{ id: ROOM_CARD_STYLE_ID, bg: clean }],
    });
    return true;
  }

  function canEditRoomCardStyle() {
    return canEditRooms();
  }

  /**
   * The menu card colour, like the room card colour. Front Desk owns the
   * Restaurant Menu preview on Home and Restaurant Management owns the
   * Restaurant page, and both show the same cards, so either may set it.
   */
  function getMenuCardBg() {
    const c = getCustomizations();
    const entry = c[MENU_CARD_STYLE_KEY];
    const item = entry && Array.isArray(entry.items) ? entry.items[0] : null;
    return item && typeof item.bg === 'string' ? item.bg.trim() : '';
  }

  function setMenuCardBg(bg) {
    if (!canEditMenuCardStyle()) return false;
    patch(MENU_CARD_STYLE_KEY, {
      page: 'restaurant',
      items: [{ id: MENU_CARD_STYLE_ID, bg: String(bg == null ? '' : bg).trim().slice(0, 32) }],
    });
    return true;
  }

  /**
   * Background colours for the areas of the site that are chrome rather than
   * anyone's page: the site background itself, the header, the footer, and the
   * Rooms / Restaurant / Amenities / Experience areas.
   *
   * Not gated on a page, for the same reason the logo is not (see canEditLogo):
   * no single role owns the site's background, and every role that can edit
   * anything could already recolour the section it works on. A change applies
   * site-wide and the most recent one wins.
   */
  const SITE_COLOR_AREAS = ['site', 'header', 'footer', 'rooms', 'dining', 'amenities', 'experience'];

  function getSiteColors() {
    const c = getCustomizations();
    const entry = c[SITE_COLORS_KEY];
    const items = entry && Array.isArray(entry.items) ? entry.items : [];
    const out = {};
    items.forEach((item) => {
      if (!item || SITE_COLOR_AREAS.indexOf(item.id) === -1) return;
      const bg = typeof item.bg === 'string' ? item.bg.trim() : '';
      if (bg) out[item.id] = bg;
    });
    return out;
  }

  function setSiteColor(area, bg) {
    if (!canEditSiteColors()) return false;
    if (SITE_COLOR_AREAS.indexOf(area) === -1) return false;
    const next = getSiteColors();
    const clean = String(bg == null ? '' : bg).trim().slice(0, 32);
    if (clean) next[area] = clean; else delete next[area];
    patch(SITE_COLORS_KEY, {
      page: 'home',
      items: SITE_COLOR_AREAS
        .filter((id) => next[id])
        .map((id) => ({ id: id, bg: next[id] })),
    });
    return true;
  }

  function canEditSiteColors() {
    return canEdit();
  }

  function canEditMenuCardStyle() {
    const pages = editablePages();
    return canEdit() && (pages.indexOf('restaurant') !== -1 || pages.indexOf('home') !== -1);
  }

  function getBrandName() {
    const c = getCustomizations();
    const entry = c[BRAND_NAME_KEY];
    const item = entry && Array.isArray(entry.items) ? entry.items[0] : null;
    const name = item && typeof item.label === 'string' ? item.label.trim() : '';
    return name || DEFAULT_BRAND_NAME;
  }

  function setBrandName(name) {
    if (!canEditBrandName()) return false;
    const clean = String(name == null ? '' : name).trim().slice(0, BRAND_NAME_MAX);
    if (!clean) return false;
    patch(BRAND_NAME_KEY, {
      page: 'home',
      items: [{ id: BRAND_NAME_ID, label: clean }],
    });
    return true;
  }

  function getHeroSlides(fallback) {
    const c = getCustomizations();
    const entry = c[HERO_SLIDES_KEY];
    if (entry && Array.isArray(entry.items) && entry.items.length) {
      return entry.items.map((item) => Object.assign({}, item));
    }
    return (fallback || []).map((item) => Object.assign({}, item));
  }

  function setHeroSlides(items) {
    if (!canEditHeroSlides()) return false;
    patch(HERO_SLIDES_KEY, {
      page: 'home',
      items: (items || []).map((item) => Object.assign({}, item, {
        id: item.id || uid('slide'),
      })),
    });
    return true;
  }

  function updateHeroSlide(id, patchData, fallbackDefaults) {
    const list = getHeroSlides(fallbackDefaults).map((item) => (
      item.id === id ? Object.assign({}, item, patchData) : item
    ));
    setHeroSlides(list);
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

  /** Rename one of the five fixed links. The page it points at never changes. */
  function renameNavLink(key, label) {
    const clean = String(label == null ? '' : label).trim().slice(0, NAV_LABEL_MAX);
    if (!clean) return false;
    return setNav(getNav().map((item) => (
      item.key === key ? Object.assign({}, item, { label: clean }) : item
    )));
  }

  /** Kept for older callers; only the label is honoured. */
  function updateNavLink(id, patchData) {
    const item = getNav().find((link) => link.id === id);
    if (!item || !patchData) return false;
    return renameNavLink(item.key, patchData.label);
  }

  function addRoom(partial, fallbackDefaults) {
    const list = getRooms(fallbackDefaults);
    const n = list.length + 1;
    const item = Object.assign({
      id: uid('room'),
      name: 'New Room ' + n,
      label: 'Standard',
      category: 'Classic',
      status: 'Available',
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
    // Persist status to DB so Room Management sees it via polling. Guest data is not
    // a room field any more — it lives in hotel_bookings, written via /hotel/bookings.
    if (patchData && patchData.status !== undefined) {
      var dbId = String(id).replace(/^db-/, '');
      var body = { status: patchData.status };
      fetch('/students/hotel/rooms/' + dbId, {
        method: 'PATCH',
        credentials: 'same-origin',
        headers: buildHeaders(),
        body: JSON.stringify(body),
      }).catch(function () { /* ignore — in-memory state already updated */ });
    }
  }

  function removeRoom(id, fallbackDefaults) {
    setRooms(getRooms(fallbackDefaults).filter((item) => item.id !== id));
  }

  /**
 * Cross-module notifications: when Front Desk completes a reservation, Room
   * Management receives a record that surfaces in their Rooms page banner.
   * Stored inside template customizations so it survives in the same backing
   * store as rooms/nav/menus and propagates via the existing change events.
   *
   * Persistence layers (in order):
   *   1. In-memory `__HMS_CUSTOMIZATIONS__` (already in customizations via patch())
   *   2. Server: POST /students/frontdesk/template/reservations (group-scoped)
   *   3. localStorage fallback (cross-tab broadcast via `storage` event)
   */
  function getReservationNotifications() {
    const c = getCustomizations();
    const memory = Array.isArray(c[RESERVATION_NOTIFICATIONS_KEY]) ? c[RESERVATION_NOTIFICATIONS_KEY].slice() : [];
    const fallback = readFallbackNotifications();
    const byId = new Map();
    for (const entry of memory) {
      if (entry && entry.id) byId.set(String(entry.id), entry);
    }
    for (const entry of fallback) {
      if (entry && entry.id && !byId.has(String(entry.id))) byId.set(String(entry.id), entry);
    }
    return Array.from(byId.values());
  }

  function storageFallbackKey() {
    const auth = window.__HMS_HOTEL_AUTH__ || {};
    const parts = [];
    if (auth.group_name) parts.push('g=' + String(auth.group_name));
    if (auth.faculty_id) parts.push('f=' + String(auth.faculty_id));
    if (!parts.length) parts.push('g=default', 'f=0');
    return 'hms.reservationNotifications.fallback.' + parts.join('&');
  }

  function readFallbackNotifications() {
    try {
      const raw = window.localStorage && window.localStorage.getItem(storageFallbackKey());
      if (!raw) return [];
      const parsed = JSON.parse(raw);
      return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
      return [];
    }
  }

  function writeFallbackNotifications(list) {
    try {
      if (!window.localStorage) return;
      window.localStorage.setItem(storageFallbackKey(), JSON.stringify(list));
    } catch (e) {
      /* quota / private mode — ignore */
    }
  }

  function syncFallbackFromMemory() {
    const c = getCustomizations();
    const memory = Array.isArray(c[RESERVATION_NOTIFICATIONS_KEY]) ? c[RESERVATION_NOTIFICATIONS_KEY] : [];
    if (memory.length) writeFallbackNotifications(memory);
  }

  /**
   * Room-keyed reservation map (one reservation per room). The Room Management
   * page reads this so it can render a "Booked" badge + guest details when
   * the room card is opened. Persisted to template customizations AND to a
   * dedicated localStorage key so it survives the post-reservation redirect.
   */
  function roomReservationsFallbackKey() {
    const auth = window.__HMS_HOTEL_AUTH__ || {};
    const parts = [];
    if (auth.group_name) parts.push('g=' + String(auth.group_name));
    if (auth.faculty_id) parts.push('f=' + String(auth.faculty_id));
    if (!parts.length) parts.push('g=default', 'f=0');
    return 'hms.roomReservations.fallback.' + parts.join('&');
  }

  function readRoomReservationsFallback() {
    try {
      const raw = window.localStorage && window.localStorage.getItem(roomReservationsFallbackKey());
      if (!raw) return {};
      const parsed = JSON.parse(raw);
      return (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) ? parsed : {};
    } catch (e) {
      return {};
    }
  }

  function writeRoomReservationsFallback(map) {
    try {
      if (!window.localStorage) return;
      window.localStorage.setItem(roomReservationsFallbackKey(), JSON.stringify(map || {}));
    } catch (e) {
      /* quota / private mode — ignore */
    }
  }

  function getRoomReservations() {
    const c = getCustomizations();
    const memory = (c[ROOM_RESERVATIONS_KEY] && typeof c[ROOM_RESERVATIONS_KEY] === 'object' && !Array.isArray(c[ROOM_RESERVATIONS_KEY]))
      ? Object.assign({}, c[ROOM_RESERVATIONS_KEY])
      : {};
    const fallback = readRoomReservationsFallback();
    const merged = Object.assign({}, fallback, memory);
    return merged;
  }

  function getRoomReservation(roomId) {
    if (!roomId) return null;
    const map = getRoomReservations();
    return map[String(roomId)] || null;
  }

  function setRoomReservation(roomId, payload) {
    if (!roomId || !payload) return null;
    const map = getRoomReservations();
    map[String(roomId)] = payload;
    patch(ROOM_RESERVATIONS_KEY, map);
    writeRoomReservationsFallback(map);
    return payload;
  }

  function clearRoomReservation(roomId) {
    if (!roomId) return;
    const map = getRoomReservations();
    if (!map[String(roomId)]) return;
    delete map[String(roomId)];
    patch(ROOM_RESERVATIONS_KEY, map);
    writeRoomReservationsFallback(map);
  }

  function syncRoomReservationsFromMemory() {
    const c = getCustomizations();
    const memory = (c[ROOM_RESERVATIONS_KEY] && typeof c[ROOM_RESERVATIONS_KEY] === 'object' && !Array.isArray(c[ROOM_RESERVATIONS_KEY]))
      ? c[ROOM_RESERVATIONS_KEY]
      : {};
    writeRoomReservationsFallback(memory);
  }

  function serverUrl() {
    if (typeof window.route === 'function') {
      try { return window.route('students.frontdesk.template.reservations'); } catch (e) { /* ignore */ }
    }
    const el = document.querySelector('meta[name="hms-reservation-url"]');
    if (el && el.content) return el.content;
    return '/students/frontdesk/template/reservations';
  }

  function serverIndexUrl() {
    if (typeof window.route === 'function') {
      try { return window.route('students.frontdesk.template.reservations'); } catch (e) { /* ignore */ }
    }
    return '/students/frontdesk/template/reservations';
  }

  function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    if (meta && meta.content) return meta.content;
    try {
      const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
      if (m) return decodeURIComponent(m[1]);
    } catch (e) { /* ignore */ }
    return '';
  }

  function xsrfCookieValue() {
    try {
      const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);
      return m ? decodeURIComponent(m[1]) : '';
    } catch (e) {
      return '';
    }
  }

  function buildHeaders(extra) {
    const headers = Object.assign({
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrfToken(),
      'X-XSRF-TOKEN': xsrfCookieValue(),
    }, extra || {});
    return headers;
  }

  function postReservation(payload) {
    try {
      return fetch(serverUrl(), {
        method: 'POST',
        credentials: 'same-origin',
        headers: buildHeaders(),
        body: JSON.stringify(payload || {}),
      }).then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      }).then(function (data) {
        if (data && Array.isArray(data.notifications)) {
          patch(RESERVATION_NOTIFICATIONS_KEY, data.notifications);
        }
        return data;
      });
    } catch (e) {
      return Promise.reject(e);
    }
  }

  let pendingSyncTimer = null;
  function scheduleServerSync(payload) {
    if (pendingSyncTimer) clearTimeout(pendingSyncTimer);
    pendingSyncTimer = setTimeout(function () {
      pendingSyncTimer = null;
      postReservation(payload).catch(function () {
        // Server unreachable — keep the localStorage fallback so other tabs
        // (and the next page load in this tab) still see the notification.
        syncFallbackFromMemory();
      });
    }, 220);
  }

  function recordReservationNotification(payload) {
    if (!payload || !payload.roomId) return null;
    const list = getReservationNotifications();
    const fullReservation = payload.fullReservation || null;
    const entry = {
      id: uid('notif'),
      roomId: payload.roomId,
      roomName: payload.roomName || '',
      guestName: payload.guestName || '',
      checkIn: payload.checkIn || null,
      checkOut: payload.checkOut || null,
      contactNo: (fullReservation && fullReservation.contactNo) || payload.contactNo || '',
      email: (fullReservation && fullReservation.email) || payload.email || '',
      idNumber: (fullReservation && fullReservation.idNumber) || payload.idNumber || '',
      fullReservation: fullReservation,
      createdAt: new Date().toISOString(),
      acknowledged: false,
    };
    list.push(entry);
    patch(RESERVATION_NOTIFICATIONS_KEY, list);
    writeFallbackNotifications(list);
    if (fullReservation) {
      setRoomReservation(payload.roomId, Object.assign({}, fullReservation, {
        roomId: payload.roomId,
        roomName: payload.roomName || fullReservation.roomName || '',
        notificationId: entry.id,
      }));
    }
    scheduleServerSync({ action: 'record', entry: entry });
    return entry;
  }

  function acknowledgeReservationNotification(id) {
    if (!id) return;
    const list = getReservationNotifications().map(function (n) {
      return (n && n.id === id) ? Object.assign({}, n, { acknowledged: true, acknowledgedAt: new Date().toISOString() }) : n;
    });
    patch(RESERVATION_NOTIFICATIONS_KEY, list);
    writeFallbackNotifications(list);
    scheduleServerSync({ action: 'acknowledge', id: id });
  }

  function dismissReservationNotification(id) {
    if (!id) return;
    const dismissed = getReservationNotifications().find(function (n) { return n && n.id === id; });
    const list = getReservationNotifications().filter(function (n) { return !(n && n.id === id); });
    patch(RESERVATION_NOTIFICATIONS_KEY, list);
    writeFallbackNotifications(list);
    if (dismissed && dismissed.roomId) {
      clearRoomReservation(dismissed.roomId);
    }
    scheduleServerSync({ action: 'dismiss', id: id });
  }

  function refreshFromServerNotifications() {
    try {
      const headers = buildHeaders();
      delete headers['Content-Type'];
      return fetch(serverIndexUrl(), {
        method: 'GET',
        credentials: 'same-origin',
        headers: headers,
      }).then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      }).then(function (data) {
        if (data && Array.isArray(data.notifications)) {
          patch(RESERVATION_NOTIFICATIONS_KEY, data.notifications);
          writeFallbackNotifications(data.notifications);
        }
        return data;
      });
    } catch (e) {
      return Promise.reject(e);
    }
  }

  function addMenu(partial, fallbackDefaults) {
    const list = getMenus(fallbackDefaults);
    const n = list.length + 1;
    const item = Object.assign({
      id: uid('menu'),
      name: 'New Menu Item ' + n,
      sub: 'Add a short description',
      price: '\u20B11,350',
      category: 'Main Dishes',
      img: 'https://picsum.photos/seed/menuitem' + Date.now() + '/800/600.jpg',
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
      brandName: getBrandName(),
      roomCardBg: getRoomCardBg(),
      menuCardBg: getMenuCardBg(),
      siteColors: getSiteColors(),
      rooms: getRooms(),
      menus: getMenus(),
      cardImages: getCardImages(),
      canEditNav: canEditNav(),
      canEditBrandName: canEditBrandName(),
      canEditLogo: canEditLogo(),
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
    BRAND_NAME_KEY,
    ROOM_CARD_STYLE_KEY,
    MENU_CARD_STYLE_KEY,
    SITE_COLORS_KEY,
    SITE_COLOR_AREAS,
    ROOMS_KEY,
    MENUS_KEY,
    CARD_IMAGES_KEY,
    RESERVATION_NOTIFICATIONS_KEY,
    CONTENT_KEYS,
    DEFAULT_NAV,
    DEFAULT_BRAND_NAME,
    DEFAULT_MENUS,
    NAV_LABEL_MAX,
    BRAND_NAME_MAX,
    getNav,
    setNav,
    renameNavLink,
    updateNavLink,
    getBrandName,
    setBrandName,
    getRoomCardBg,
    setRoomCardBg,
    canEditRoomCardStyle,
    getMenuCardBg,
    setMenuCardBg,
    canEditMenuCardStyle,
    getSiteColors,
    setSiteColor,
    canEditSiteColors,
    getRooms,
    setRooms,
    addRoom,
    updateRoom,
    removeRoom,
    getHeroSlides,
    setHeroSlides,
    updateHeroSlide,
    canEditHeroSlides,
    getReservationNotifications,
    recordReservationNotification,
    acknowledgeReservationNotification,
    dismissReservationNotification,
    refreshFromServerNotifications,
    getRoomReservations,
    getRoomReservation,
    setRoomReservation,
    clearRoomReservation,
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
    canEditBrandName,
    canEditRooms,
    canEditMenus,
    canEditExperiences,
    canEditLogo,
    canAccess,
    canUseRoomManagementUi,
    canUseRestaurantUi,
    canReserveRooms,
    canOrderMenu,
    getBuilderRole,
    currentRoles,
    FEATURE_ROLES,
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

  // Cross-tab broadcast: another open tab (Room Management / Front Desk)
  // wrote new notifications to the localStorage fallback. Merge them in.
  window.addEventListener('storage', function (event) {
    if (!event || !event.key) return;
    if (event.key === storageFallbackKey()) {
      const c = getCustomizations();
      const fallback = readFallbackNotifications();
      const memory = Array.isArray(c[RESERVATION_NOTIFICATIONS_KEY]) ? c[RESERVATION_NOTIFICATIONS_KEY] : [];
      const byId = new Map();
      for (const entry of memory) {
        if (entry && entry.id) byId.set(String(entry.id), entry);
      }
      for (const entry of fallback) {
        if (entry && entry.id && !byId.has(String(entry.id))) byId.set(String(entry.id), entry);
      }
      const merged = Array.from(byId.values());
      patch(RESERVATION_NOTIFICATIONS_KEY, merged);
      return;
    }
    if (event.key === roomReservationsFallbackKey()) {
      const c = getCustomizations();
      const memory = (c[ROOM_RESERVATIONS_KEY] && typeof c[ROOM_RESERVATIONS_KEY] === 'object' && !Array.isArray(c[ROOM_RESERVATIONS_KEY]))
        ? c[ROOM_RESERVATIONS_KEY]
        : {};
      const fallback = readRoomReservationsFallback();
      const merged = Object.assign({}, fallback, memory);
      patch(ROOM_RESERVATIONS_KEY, merged);
    }
  });

  window.addEventListener('hms-hotel-auth', function () {
    window.HMSSiteContent.refreshFromEditor();
  });

  window.addEventListener('message', function (event) {
    const data = event.data || {};
    if (!data || data.source !== 'hms-parent') return;
    if (data.type === 'set-builder-role') {
      window.__HMS_BUILDER_ROLE__ = data.role || null;
      setTimeout(function () { window.HMSSiteContent.refreshFromEditor(); }, 50);
      return;
    }
    if (data.type === 'load-customizations') {
      if (data.customizations && typeof data.customizations === 'object') {
        window.__HMS_CUSTOMIZATIONS__ = data.customizations;
      }
      setTimeout(function () { window.HMSSiteContent.refreshFromEditor(); }, 50);
      return;
    }
    if (data.type === 'set-can-edit' || data.type === 'set-editable-pages') {
      setTimeout(function () { window.HMSSiteContent.refreshFromEditor(); }, 50);
    }
  });
})(window);
