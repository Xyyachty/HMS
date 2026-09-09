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
  const HOTEL_INFO_KEY = '__hotelInfo';
  const SOCIAL_LINKS_KEY = '__socialLinks';
  const TYPOGRAPHY_KEY = '__typography';
  const PARTNERS_KEY = '__partners';
  const CONTENT_KEYS = [NAV_KEY, BRAND_NAME_KEY, ROOM_CARD_STYLE_KEY, MENU_CARD_STYLE_KEY, SITE_COLORS_KEY, ROOMS_KEY, MENUS_KEY, CARD_IMAGES_KEY, HERO_SLIDES_KEY, HOTEL_INFO_KEY, SOCIAL_LINKS_KEY, TYPOGRAPHY_KEY, PARTNERS_KEY, RESERVATION_NOTIFICATIONS_KEY, ROOM_RESERVATIONS_KEY];

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
  const HOTEL_INFO_ID = 'hotel';
  const TYPOGRAPHY_ID = 'type';

  /** Per-field caps: a tagline is a line, a description is a paragraph. */
  const HOTEL_INFO_FIELDS = ['tagline', 'description', 'phone', 'email', 'address', 'hours'];
  const HOTEL_INFO_MAX = {
    tagline: 140,
    description: 1200,
    phone: 40,
    email: 120,
    address: 200,
    hours: 120,
  };

  /** Networks the footer knows an icon for; mirrors HotelTemplateBuilder::SOCIAL_NETWORKS. */
  const SOCIAL_NETWORKS = {
    facebook: 'Facebook',
    instagram: 'Instagram',
    x: 'X',
    tiktok: 'TikTok',
    youtube: 'YouTube',
    linkedin: 'LinkedIn',
    website: 'Website',
  };
  const SOCIAL_LINKS_MAX = 8;

  const TYPOGRAPHY_FIELDS = ['family', 'size', 'color', 'headingColor'];

  /** Offered in the builder; any CSS stack still works if one is typed in. */
  const FONT_FAMILIES = [
    { id: '', label: 'Template default' },
    { id: "'Inter', system-ui, sans-serif", label: 'Inter' },
    { id: "'Playfair Display', Georgia, serif", label: 'Playfair Display' },
    { id: "'Montserrat', system-ui, sans-serif", label: 'Montserrat' },
    { id: "'Lora', Georgia, serif", label: 'Lora' },
    { id: "'Poppins', system-ui, sans-serif", label: 'Poppins' },
    { id: "Georgia, 'Times New Roman', serif", label: 'Georgia' },
    { id: "system-ui, -apple-system, 'Segoe UI', sans-serif", label: 'System' },
  ];

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
    // Room Management owns the Rooms page exclusively. Front Desk sees room
    // cards on the Home "Available Rooms" section too, but may not edit them.
    return canEdit() && editablePages().indexOf('rooms') !== -1;
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
  const SITE_COLOR_AREAS = ['site', 'header', 'footer', 'rooms', 'roomModal', 'dining', 'amenities', 'experience'];

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

  /**
   * 'site' / 'header' / 'footer' / 'roomModal' / 'experience' stay shared chrome
   * (see the comment above SITE_COLOR_AREAS) — any editor may recolour them.
   * 'rooms', 'dining' and 'amenities' are a specific role's own page, so only
   * that role's editor may recolour them; everyone else still sees the colour
   * applied, they just can't change it from the Background Colours dialog.
   */
  const SITE_COLOR_AREA_PAGES = { dining: 'restaurant', amenities: 'amenities' };

  function canEditSiteColorArea(area) {
    if (!canEditSiteColors()) return false;
    const page = SITE_COLOR_AREA_PAGES[area];
    if (!page) return true;
    return editablePages().indexOf(page) !== -1;
  }

  function setSiteColor(area, bg) {
    if (!canEditSiteColorArea(area)) return false;
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
    // Restaurant Services owns the Menu cards exclusively, same as Room
    // Management owns room cards (see canEditRooms).
    return canEdit() && editablePages().indexOf('restaurant') !== -1;
  }

  /**
   * What the team's approved hotel concept says, handed down by the server as
   * HMS_HOTEL_DEFAULTS. Every identity field falls back to this, so a site the
   * team has not touched already reads as the concept faculty approved, and a
   * field cleared in the builder goes back to tracking it.
   */
  function hotelDefaults() {
    const d = window.HMS_HOTEL_DEFAULTS;
    return {
      name: d && typeof d.name === 'string' && d.name.trim() ? d.name.trim() : DEFAULT_BRAND_NAME,
      tagline: d && typeof d.tagline === 'string' ? d.tagline.trim() : '',
      description: d && typeof d.description === 'string' ? d.description.trim() : '',
    };
  }

  function getBrandName() {
    const c = getCustomizations();
    const entry = c[BRAND_NAME_KEY];
    const item = entry && Array.isArray(entry.items) ? entry.items[0] : null;
    const name = item && typeof item.label === 'string' ? item.label.trim() : '';
    return name || hotelDefaults().name;
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

  /* ── Hotel information ──────────────────────────────────────────────────
     One record for the whole site: the words under the hotel's name and the
     contact block the landing page and every footer show. Any site-owning role
     may write it, like the logo and the background colours, because the hotel
     has one phone number no matter whose page you are looking at. */

  function canEditSiteIdentity() {
    // Site-wide identity, not a page: anyone who may edit any page of the site
    // may set it. The server re-checks on save (SITE_OWNING_ROLES).
    return canEdit() && editablePages().length > 0;
  }

  function getHotelInfo() {
    const c = getCustomizations();
    const entry = c[HOTEL_INFO_KEY];
    const item = entry && Array.isArray(entry.items) ? entry.items[0] : null;
    const defaults = hotelDefaults();
    const read = (field) => {
      const value = item && typeof item[field] === 'string' ? item[field].trim() : '';
      return value;
    };

    return {
      // The name lives in __brandName, where the header and footer already read
      // it; it is surfaced here so the builder can show one Hotel Information
      // form rather than two.
      name: getBrandName(),
      tagline: read('tagline') || defaults.tagline,
      description: read('description') || defaults.description,
      phone: read('phone'),
      email: read('email'),
      address: read('address'),
      hours: read('hours'),
    };
  }

  /**
   * Merge a partial edit into the stored record. Only the fields passed are
   * touched, so the builder can save one input as the student leaves it
   * without carrying the rest of the form along.
   *
   * A field set to the empty string is stored empty on purpose — that is how a
   * team goes back to showing the approved concept's own words.
   */
  function setHotelInfo(patchFields) {
    if (!canEditSiteIdentity()) return false;
    if (!patchFields || typeof patchFields !== 'object') return false;

    if (typeof patchFields.name === 'string') {
      setBrandName(patchFields.name);
    }

    const c = getCustomizations();
    const entry = c[HOTEL_INFO_KEY];
    const current = (entry && Array.isArray(entry.items) ? entry.items[0] : null) || {};
    const next = { id: HOTEL_INFO_ID };

    HOTEL_INFO_FIELDS.forEach((field) => {
      const incoming = Object.prototype.hasOwnProperty.call(patchFields, field)
        ? patchFields[field]
        : current[field];
      next[field] = String(incoming == null ? '' : incoming).trim().slice(0, HOTEL_INFO_MAX[field] || 300);
    });

    patch(HOTEL_INFO_KEY, { page: 'home', items: [next] });
    return true;
  }

  /* ── Social profiles ────────────────────────────────────────────────────
     A list rather than a field per network, so a team shows only the networks
     it actually uses instead of a row of dead icons. */

  function getSocialLinks() {
    const c = getCustomizations();
    const entry = c[SOCIAL_LINKS_KEY];
    const items = entry && Array.isArray(entry.items) ? entry.items : [];

    return items
      .map((item) => ({
        id: item && item.id ? String(item.id) : uid('social'),
        network: item && SOCIAL_NETWORKS[String(item.network)] ? String(item.network) : 'website',
        url: item && typeof item.url === 'string' ? item.url.trim() : '',
      }))
      .filter((item) => item.url !== '');
  }

  function setSocialLinks(items) {
    if (!canEditSiteIdentity()) return false;
    const list = (Array.isArray(items) ? items : [])
      .map((item) => ({
        id: item && item.id ? String(item.id) : uid('social'),
        network: item && SOCIAL_NETWORKS[String(item.network)] ? String(item.network) : 'website',
        url: String(item && item.url != null ? item.url : '').trim().slice(0, 300),
      }))
      .filter((item) => item.url !== '')
      .slice(0, SOCIAL_LINKS_MAX);

    patch(SOCIAL_LINKS_KEY, { page: 'home', items: list });
    return true;
  }

  /* ── Site typography ────────────────────────────────────────────────────
     Applied as CSS custom properties on every page rather than per element, so
     it reaches text nobody has selected — including sections this role cannot
     edit. An empty field means "leave the template's own type alone". */

  function getTypography() {
    const c = getCustomizations();
    const entry = c[TYPOGRAPHY_KEY];
    const item = (entry && Array.isArray(entry.items) ? entry.items[0] : null) || {};
    const read = (field) => (typeof item[field] === 'string' ? item[field].trim() : '');

    return {
      family: read('family'),
      size: read('size'),
      color: read('color'),
      headingColor: read('headingColor'),
    };
  }

  function setTypography(patchFields) {
    if (!canEditSiteIdentity()) return false;
    if (!patchFields || typeof patchFields !== 'object') return false;

    const current = getTypography();
    const next = { id: TYPOGRAPHY_ID };

    TYPOGRAPHY_FIELDS.forEach((field) => {
      const incoming = Object.prototype.hasOwnProperty.call(patchFields, field)
        ? patchFields[field]
        : current[field];
      next[field] = String(incoming == null ? '' : incoming).trim().slice(0, 120);
    });

    patch(TYPOGRAPHY_KEY, { page: 'home', items: [next] });
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

  /* ── Partner brands ───────────────────────────────────────────────────────
     The strip of names on the Home page, below the promos. Front Desk owns Home,
     so it owns these; a logo for one is an ordinary card image keyed by the id
     here, which is why a brand carries a name and nothing else.

     The template's own six are passed in as the fallback rather than written to
     the row at load: a team that has never touched the strip stores nothing, and
     still sees a finished section. The first edit writes the whole list. */
  const PARTNER_NAME_MAX = 40;

  function canEditPartners() {
    return canEdit() && editablePages().indexOf('home') !== -1;
  }

  function getPartners(fallback) {
    const c = getCustomizations();
    const entry = c[PARTNERS_KEY];
    if (entry && Array.isArray(entry.items) && entry.items.length) {
      return entry.items.map((item) => Object.assign({}, item));
    }
    return (fallback || []).map((item) => Object.assign({}, item));
  }

  function setPartners(items) {
    if (!canEditPartners()) return false;
    patch(PARTNERS_KEY, {
      page: 'home',
      items: (items || []).map((item) => ({
        id: item.id || uid('partner'),
        // "label", not "name": TemplateDiff::itemTitle() prints this field in the
        // faculty review, and an id there tells a reviewer nothing.
        label: String(item.label || item.name || '').trim().slice(0, PARTNER_NAME_MAX),
      })),
    });
    return true;
  }

  function addPartner(name, fallbackDefaults) {
    if (!canEditPartners()) return null;
    const entry = { id: uid('partner'), label: String(name || 'New Brand').trim().slice(0, PARTNER_NAME_MAX) };
    const list = getPartners(fallbackDefaults).concat([entry]);
    return setPartners(list) ? entry : null;
  }

  function updatePartner(id, patchData, fallbackDefaults) {
    const list = getPartners(fallbackDefaults).map((item) => (
      item.id === id ? Object.assign({}, item, patchData) : item
    ));
    return setPartners(list);
  }

  function removePartner(id, fallbackDefaults) {
    const list = getPartners(fallbackDefaults).filter((item) => item.id !== id);
    return setPartners(list);
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
      hotelInfo: getHotelInfo(),
      socialLinks: getSocialLinks(),
      typography: getTypography(),
      rooms: getRooms(),
      menus: getMenus(),
      cardImages: getCardImages(),
      canEditNav: canEditNav(),
      canEditSiteIdentity: canEditSiteIdentity(),
      canEditBrandName: canEditBrandName(),
      canEditLogo: canEditLogo(),
      canEditRooms: canEditRooms(),
      canEditMenus: canEditMenus(),
      canEditExperiences: canEditExperiences(),
      canEditPartners: canEditPartners(),
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
    HOTEL_INFO_KEY,
    SOCIAL_LINKS_KEY,
    TYPOGRAPHY_KEY,
    HOTEL_INFO_FIELDS,
    SOCIAL_NETWORKS,
    FONT_FAMILIES,
    hotelDefaults,
    getHotelInfo,
    setHotelInfo,
    getSocialLinks,
    setSocialLinks,
    getTypography,
    setTypography,
    canEditSiteIdentity,
    getRoomCardBg,
    setRoomCardBg,
    canEditRoomCardStyle,
    getMenuCardBg,
    setMenuCardBg,
    canEditMenuCardStyle,
    getSiteColors,
    setSiteColor,
    canEditSiteColors,
    canEditSiteColorArea,
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
    canEditPartners,
    getPartners,
    setPartners,
    addPartner,
    updatePartner,
    removePartner,
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
