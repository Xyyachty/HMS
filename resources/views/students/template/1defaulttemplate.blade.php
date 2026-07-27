<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPC HOTEL</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Outfit:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
<style>
  :root {
    --bg: #0c0b09;
    --bg-warm: #111110;
    --fg: #f5f0e8;
    --fg-muted: #9e978b;
    --accent: #c9a84c;
    --accent-light: #e2cc7a;
    --card: #181714;
    --border: #2a2621;
  }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  html { scroll-behavior: auto; }
  body {
    font-family: 'Outfit', sans-serif;
    background: var(--bg);
    color: var(--fg);
    line-height: 1.6;
    overflow-x: hidden;
  }
  .font-display { font-family: 'Playfair Display', serif; }
  ::-webkit-scrollbar { width: 5px; }
  ::-webkit-scrollbar-track { background: var(--bg); }
  ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

  .nav-bar {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 1000;
    padding: 0.9rem 2rem;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
  }
  .nav-item {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
  .nav-links-desktop {
    position: relative;
  }
  .nav-edit-tools {
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    margin-left: 2px;
    display: none;
    align-items: center;
    gap: 1px;
    z-index: 3;
    white-space: nowrap;
  }
  body.hms-design-mode .nav-item:hover .nav-edit-tools,
  body.hms-design-mode .nav-item:focus-within .nav-edit-tools {
    display: inline-flex;
  }
  .nav-add-btn {
    position: absolute;
    right: calc(100% + 6px);
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    border-radius: 6px;
    border: 1px dashed #f43f5e;
    background: rgba(244,63,94,0.12);
    color: #fb7185;
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    line-height: 1;
    padding: 0;
    z-index: 3;
  }
  body.hms-design-mode .nav-add-btn { display: inline-flex; }
  .nav-link {
    color: var(--fg-muted);
    text-decoration: none;
    font-size: 0.8rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    transition: color 0.2s;
    cursor: pointer;
    background: none;
    border: none;
    font-family: 'Outfit', sans-serif;
    padding: 0;
  }
  .nav-link:hover, .nav-link.active { color: var(--accent); }

  .hero {
    position: relative;
    min-height: 72vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible;
    margin-top: 64px;
  }
  .hero-bg {
    position: absolute;
    inset: 0;
    background: url('https://picsum.photos/seed/luxuryhotel/1920/1080.jpg') center/cover no-repeat;
    overflow: hidden;
  }
  .hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(12,11,9,0.25) 0%, rgba(12,11,9,0.55) 45%, rgba(12,11,9,0.9) 80%, var(--bg) 100%);
    pointer-events: none;
  }

  .page-header {
    padding: 8rem 1.5rem 3rem;
    text-align: center;
    max-width: 700px;
    margin: 0 auto;
  }
  .page-header h1 { font-size: 2.5rem; font-weight: 700; margin-bottom: 0.75rem; }
  .page-header p { color: var(--fg-muted); font-weight: 300; font-size: 1rem; }

  .room-card {
    border-radius: 10px;
    overflow: hidden;
    background: var(--card);
    border: 1px solid var(--border);
    transition: border-color 0.2s, transform 0.2s;
    cursor: pointer;
  }
  .room-card:hover { border-color: var(--accent); transform: translateY(-4px); }
  .room-card-img { position: relative; height: 240px; overflow: hidden; }
  .room-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
  .room-card:hover .room-card-img img { transform: scale(1.05); }
  .room-card-badge {
    position: absolute; top: 0.85rem; left: 0.85rem;
    background: rgba(12,11,9,0.75); padding: 0.2rem 0.65rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--accent); border: 1px solid rgba(201,168,76,0.2);
  }
  .room-card-price {
    position: absolute; bottom: 0.85rem; right: 0.85rem;
    background: rgba(12,11,9,0.8); padding: 0.35rem 0.75rem; border-radius: 5px;
    font-family: 'Playfair Display', serif; font-size: 1.05rem; color: var(--accent-light);
  }
  .room-amenity {
    display: inline-flex; align-items: center; gap: 0.3rem;
    font-size: 0.72rem; color: var(--fg-muted);
    padding: 0.2rem 0.45rem; border: 1px solid var(--border); border-radius: 3px;
  }

  .tab-bar {
    display: flex; align-items: center; justify-content: center;
    gap: 0.35rem; padding: 0 1.5rem; margin-bottom: 2.5rem;
    flex-wrap: wrap;
  }
  .tab-btn {
    font-family: 'Outfit', sans-serif; font-size: 0.78rem; font-weight: 500;
    letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.55rem 1.2rem; border-radius: 100px;
    border: 1.5px solid var(--border); background: transparent;
    color: var(--fg-muted); cursor: pointer;
    transition: all 0.2s;
  }
  .tab-btn:hover { border-color: var(--accent); color: var(--accent); }
  .tab-btn.active {
    background: var(--accent); border-color: var(--accent); color: #0c0b09;
  }
  .tab-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 20px; border-radius: 10px;
    font-size: 0.65rem; font-weight: 700;
    margin-left: 0.4rem; padding: 0 0.35rem;
    background: rgba(255,255,255,0.06); color: var(--fg-muted);
    transition: all 0.2s;
  }
  .tab-btn.active .tab-count {
    background: rgba(12,11,9,0.2); color: #0c0b09;
  }

  .room-status-badge {
    position: absolute; top: 0.85rem; left: 0.85rem;
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent;
  }
  .room-status-badge.status-available {
    background: rgba(34,197,94,0.18); color: #4ade80; border-color: rgba(34,197,94,0.35);
  }
  .room-status-badge.status-occupied {
    background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35);
  }
  .room-status-badge.status-cleaning {
    background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35);
  }
  .room-status-badge.status-maintenance {
    background: rgba(244,63,94,0.18); color: #fb7185; border-color: rgba(244,63,94,0.35);
  }

  .room-modal-overlay {
    position: fixed; inset: 0; z-index: 2000;
    background: rgba(0,0,0,0.72);
    display: flex; align-items: center; justify-content: center;
    padding: 1.25rem;
    animation: roomModalFade 0.2s ease;
  }
  @keyframes roomModalFade {
    from { opacity: 0; }
    to { opacity: 1; }
  }
  .room-modal {
    width: min(560px, 100%);
    max-height: min(90vh, 720px);
    overflow: auto;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.45);
    animation: roomModalRise 0.22s ease;
  }
  @keyframes roomModalRise {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to { opacity: 1; transform: none; }
  }
  .room-modal-img {
    position: relative; height: 220px; overflow: hidden;
  }
  .room-modal-img img {
    width: 100%; height: 100%; object-fit: cover;
  }
  .room-modal-close {
    position: absolute; top: 0.75rem; right: 0.75rem;
    width: 34px; height: 34px; border-radius: 8px;
    border: 1px solid var(--border);
    background: rgba(12,11,9,0.85); color: var(--fg);
    cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
  }
  .room-status-picker {
    display: flex; flex-wrap: wrap; gap: 0.45rem;
  }
  .room-status-option {
    font-family: 'Outfit', sans-serif; font-size: 0.72rem; font-weight: 600;
    letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.45rem 0.85rem; border-radius: 100px;
    border: 1.5px solid var(--border); background: transparent;
    color: var(--fg-muted); cursor: pointer; transition: all 0.15s;
  }
  .room-status-option:hover { border-color: var(--accent); color: var(--accent); }
  .room-status-option.active {
    background: var(--accent); border-color: var(--accent); color: #0c0b09;
  }

  .rm-shell {
    display: flex; min-height: 420px; max-height: min(78vh, 640px);
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    overflow: hidden;
  }
  .rm-sidebar {
    width: 220px; flex-shrink: 0;
    background: #12110f;
    border-right: 1px solid var(--border);
    padding: 1.25rem 0.85rem;
    display: flex; flex-direction: column; gap: 0.35rem;
  }
  .rm-sidebar-title {
    font-size: 0.65rem; letter-spacing: 0.14em; text-transform: uppercase;
    color: var(--fg-muted); padding: 0 0.65rem 0.75rem; margin: 0;
  }
  .rm-nav-item {
    display: flex; align-items: center; gap: 0.55rem;
    width: 100%; text-align: left;
    font-family: 'Outfit', sans-serif; font-size: 0.82rem; font-weight: 500;
    color: var(--fg-muted); background: transparent;
    border: 1px solid transparent; border-radius: 8px;
    padding: 0.7rem 0.75rem; cursor: pointer; transition: all 0.15s;
  }
  .rm-nav-item:hover { color: var(--fg); background: rgba(255,255,255,0.03); }
  .rm-nav-item.active {
    color: var(--bg); background: var(--accent); border-color: var(--accent);
  }
  .rm-content {
    flex: 1; min-width: 0; padding: 1.5rem 1.6rem 1.75rem;
    overflow: auto; position: relative;
  }
  .rm-panel {
    max-width: 520px;
  }
  .rm-panel h3 {
    font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700;
    margin: 0 0 0.35rem;
  }
  .rm-panel-desc {
    color: var(--fg-muted); font-size: 0.82rem; margin: 0 0 1.35rem; line-height: 1.5;
  }
  .rm-form-grid {
    display: grid; gap: 0.95rem;
  }
  .rm-form-row {
    display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem;
  }
  @media (max-width: 640px) {
    .rm-shell { flex-direction: column; max-height: min(85vh, 720px); }
    .rm-sidebar { width: 100%; border-right: none; border-bottom: 1px solid var(--border); }
    .rm-form-row { grid-template-columns: 1fr; }
  }

  .rest-card {
    border-radius: 10px; overflow: hidden;
    background: var(--card); border: 1px solid var(--border);
    transition: border-color 0.2s, transform 0.2s;
  }
  .rest-card:hover { border-color: var(--accent); transform: translateY(-4px); }
  .rest-card-img { height: 200px; overflow: hidden; }
  .rest-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
  .rest-card:hover .rest-card-img img { transform: scale(1.05); }
  .rest-dot { width: 5px; height: 5px; border-radius: 50%; background: #4ade80; display: inline-block; }

  .menu-item {
    display: flex; justify-content: space-between; align-items: baseline;
    padding: 0.75rem 0; border-bottom: 1px dashed rgba(42,38,33,0.6);
  }
  .menu-item:last-child { border-bottom: none; }

  .menu-food-card {
    border-radius: 12px; overflow: hidden;
    background: var(--card); border: 1px solid var(--border);
    transition: border-color 0.2s, transform 0.2s;
    display: flex; flex-direction: column;
  }
  .menu-food-card:hover { border-color: var(--accent); transform: translateY(-3px); }
  .menu-food-img {
    position: relative; height: 180px; overflow: hidden; background: #12110f;
  }
  .menu-food-img img {
    width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s;
  }
  .menu-food-card:hover .menu-food-img img { transform: scale(1.04); }
  .menu-food-img-fallback {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    color: var(--fg-muted); background: linear-gradient(145deg, #1a1814, #12100e);
  }
  .menu-food-price {
    position: absolute; bottom: 0.75rem; right: 0.75rem;
    background: rgba(12,11,9,0.82); padding: 0.3rem 0.65rem; border-radius: 5px;
    font-family: 'Playfair Display', serif; font-size: 0.95rem; color: var(--accent-light);
  }
  .menu-food-body { padding: 1.1rem 1.15rem 1.25rem; flex: 1; }

  .exp-item {
    padding: 1.75rem; border-radius: 10px;
    background: var(--card); border: 1px solid var(--border);
    transition: border-color 0.2s;
  }
  .exp-item:hover { border-color: rgba(201,168,76,0.3); }

  .testimonial-box {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 2.5rem;
  }

  .booking-bar {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 2.25rem; position: relative;
  }
  .booking-bar::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0;
    height: 1px; background: linear-gradient(90deg, transparent, var(--accent), transparent); opacity: 0.4;
  }
  .booking-input {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: 'Outfit', sans-serif; font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus { border-color: var(--accent); }
  .booking-input::placeholder { color: var(--fg-muted); opacity: 0.5; }

  .btn-primary {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: var(--accent); color: var(--bg);
    font-family: 'Outfit', sans-serif; font-weight: 600;
    font-size: 0.8rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.8rem 1.8rem; border: none; border-radius: 6px;
    cursor: pointer; transition: background 0.2s, transform 0.2s;
  }
  .btn-primary:hover { background: var(--accent-light); transform: translateY(-1px); }

  .btn-outline {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: transparent; color: var(--accent);
    font-family: 'Outfit', sans-serif; font-weight: 500;
    font-size: 0.75rem; letter-spacing: 0.1em; text-transform: uppercase;
    padding: 0.6rem 1.3rem; border: 1px solid var(--accent); border-radius: 6px;
    cursor: pointer; transition: background 0.2s, color 0.2s, transform 0.2s;
    text-decoration: none;
  }
  .btn-outline:hover { background: var(--accent); color: var(--bg); transform: translateY(-1px); }

  .toast-el {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    background: var(--card); border: 1px solid var(--accent);
    border-radius: 10px; padding: 0.9rem 1.3rem; color: var(--fg);
    font-size: 0.85rem; z-index: 9999; max-width: 360px;
    display: flex; align-items: center; gap: 0.65rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.4);
    opacity: 0; transform: translateY(20px);
    transition: opacity 0.3s, transform 0.3s;
    pointer-events: none;
  }
  .toast-el.show { opacity: 1; transform: translateY(0); pointer-events: auto; }

  .mobile-menu {
    position: fixed; inset: 0; background: rgba(12,11,9,0.97);
    z-index: 999; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 1.75rem;
    opacity: 0; pointer-events: none; transition: opacity 0.25s;
  }
  .mobile-menu.open { opacity: 1; pointer-events: all; }
  .mobile-menu button {
    font-family: 'Playfair Display', serif; font-size: 1.8rem;
    color: var(--fg); background: none; border: none; cursor: pointer;
    transition: color 0.2s;
  }
  .mobile-menu button:hover { color: var(--accent); }

  footer a { color: var(--fg-muted); text-decoration: none; transition: color 0.2s; }
  footer a:hover { color: var(--accent); }

  .hamburger {
    display: none; flex-direction: column; gap: 4px; cursor: pointer;
    z-index: 1001; background: none; border: none; padding: 4px;
  }
  .hamburger span { display: block; width: 20px; height: 1.5px; background: var(--fg); transition: all 0.2s; }
  .hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(4px, 4.5px); }
  .hamburger.active span:nth-child(2) { opacity: 0; }
  .hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(4px, -4.5px); }

  .highlight-card {
    text-align: center; padding: 2.5rem 1.5rem;
    border: 1px solid var(--border); border-radius: 10px;
    cursor: pointer; transition: border-color 0.2s;
  }
  .highlight-card:hover { border-color: var(--accent); }

  @media (max-width: 768px) {
    .hamburger { display: flex; }
    .nav-links-desktop { display: none !important; }
    .hero-title { font-size: 2.6rem !important; }
    .grid-3 { grid-template-columns: 1fr !important; }
    .grid-4 { grid-template-columns: 1fr 1fr !important; }
    .grid-2 { grid-template-columns: 1fr !important; }
    .booking-flex { flex-direction: column !important; }
    .footer-grid { grid-template-columns: 1fr 1fr !important; }
    .testimonial-flex { flex-direction: column !important; text-align: center; }
    .testimonial-nav { justify-content: center; }
    .page-header { padding: 7rem 1.5rem 2rem; }
    .page-header h1 { font-size: 2rem; }
  }
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
  }
</style>
<script>
  try {
    if (window.parent && window.parent !== window) {
      document.documentElement.classList.add('hms-in-builder');
    }
  } catch (e) { /* cross-origin */ }
</script>
</head>
<body>
<div id="root"></div>

<script src="{{ asset('js/hms-site-content.js') }}"></script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef, useMemo } = React;

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• DATA â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
const ROOM_CATEGORIES = ['Classic', 'Superior', 'Deluxe', 'Premium', 'Family'];
const ROOM_STATUSES = ['Available', 'Occupied', 'Cleaning', 'Maintenance'];
const ROOM_TABS = ROOM_CATEGORIES;
const MENU_CATEGORIES = ['Main Dishes', 'Appetizers', 'Soups', 'Desserts', 'Beverages'];
const MENU_TABS = MENU_CATEGORIES;

function normalizeRoomCategory(value) {
  const raw = String(value || 'Classic').trim().toLowerCase();
  const match = ROOM_CATEGORIES.find(c => c.toLowerCase() === raw);
  return match || 'Classic';
}

function normalizeMenuCategory(value) {
  const raw = String(value || 'Main Dishes').trim().toLowerCase();
  const match = MENU_CATEGORIES.find(c => c.toLowerCase() === raw);
  if (match) return match;
  // Map legacy Dining/Bar labels into the new menu groups
  if (raw === 'dining' || raw === 'main' || raw === 'mains') return 'Main Dishes';
  if (raw === 'bar' || raw === 'drinks' || raw === 'beverage') return 'Beverages';
  if (raw === 'dessert' || raw === 'sweets') return 'Desserts';
  if (raw === 'appetizer' || raw === 'starter' || raw === 'starters') return 'Appetizers';
  if (raw === 'soup') return 'Soups';
  return 'Main Dishes';
}

function normalizeRoomStatus(value) {
  const raw = String(value || 'Available').trim().toLowerCase();
  const match = ROOM_STATUSES.find(s => s.toLowerCase() === raw);
  return match || 'Available';
}

function roomStatusClass(status) {
  return 'status-' + normalizeRoomStatus(status).toLowerCase();
}

function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '\u20B10';
  return '\u20B1' + n.toLocaleString();
}

function menuFoodImg(item) {
  if (item && item.img) return item.img;
  const seed = encodeURIComponent((item && (item.id || item.name)) || 'menu');
  return 'https://picsum.photos/seed/' + seed + '/800/600.jpg';
}

const ROOMS = [
  {
    id: 'classic', label: 'Classic', category: 'Classic', status: 'Available', name: 'Classic Queen Room', price: 180,
    img: 'https://picsum.photos/seed/hotelroom3/800/600.jpg',
    desc: 'Cozy 28m\u00B2 room with a queen bed, soft lighting, and thoughtful amenities for a restful stay.',
    badgeStyle: {},
    amenities: [
      { icon: 'fa-bed', text: 'Queen Bed' },
      { icon: 'fa-wifi', text: 'WiFi' },
      { icon: 'fa-shower', text: 'Shower' },
      { icon: 'fa-mug-saucer', text: 'Tea Set' },
    ]
  },
  {
    id: 'superior', label: 'Superior', category: 'Superior', status: 'Occupied', name: 'Superior Twin Room', price: 240,
    img: 'https://picsum.photos/seed/twinroom/800/600.jpg',
    desc: '38m\u00B2 room with two single beds, a work desk, and views of the courtyard garden.',
    badgeStyle: {},
    amenities: [
      { icon: 'fa-bed', text: 'Twin Beds' },
      { icon: 'fa-laptop', text: 'Work Desk' },
      { icon: 'fa-tree', text: 'Garden View' },
      { icon: 'fa-wifi', text: 'WiFi' },
    ]
  },
  {
    id: 'premium', label: 'Premium', category: 'Premium', status: 'Cleaning', name: 'Premium Suite', price: 450,
    img: 'https://picsum.photos/seed/hotelroom2/800/600.jpg',
    desc: '68m\u00B2 suite with separate living area, walk-in closet, soaking tub, and panoramic floor-to-ceiling windows.',
    badgeStyle: { background: 'rgba(201,168,76,0.15)', borderColor: 'var(--accent)' },
    amenities: [
      { icon: 'fa-bed', text: 'King Bed' },
      { icon: 'fa-couch', text: 'Living Area' },
      { icon: 'fa-bath', text: 'Soaking Tub' },
      { icon: 'fa-city', text: 'City View' },
    ]
  },
  {
    id: 'deluxe', label: 'Deluxe', category: 'Deluxe', status: 'Available', name: 'Deluxe King Room', price: 280,
    img: 'https://picsum.photos/seed/hotelroom1/800/600.jpg',
    desc: 'Spacious 42m\u00B2 room with king bed, city views, and a marble-appointed bathroom with rain shower.',
    badgeStyle: {},
    amenities: [
      { icon: 'fa-bed', text: 'King Bed' },
      { icon: 'fa-wifi', text: 'WiFi' },
      { icon: 'fa-bath', text: 'Rain Shower' },
      { icon: 'fa-mug-saucer', text: 'Minibar' },
    ]
  },
  {
    id: 'family', label: 'Family', category: 'Family', status: 'Maintenance', name: 'Family Suite', price: 520,
    img: 'https://picsum.photos/seed/juniorsuite/800/600.jpg',
    desc: '85m\u00B2 connecting suite with two bedrooms, a living lounge, and space for the whole family.',
    badgeStyle: { background: 'rgba(201,168,76,0.2)', borderColor: 'var(--accent-light)', color: 'var(--accent-light)' },
    amenities: [
      { icon: 'fa-bed', text: '2 Bedrooms' },
      { icon: 'fa-couch', text: 'Living Lounge' },
      { icon: 'fa-child', text: 'Kids Friendly' },
      { icon: 'fa-wifi', text: 'WiFi' },
    ]
  }
];

const RESTAURANTS = [
  {
    name: 'Lumiere', img: 'https://picsum.photos/seed/finedining/800/500.jpg',
    desc: 'Contemporary French fine dining with a 12-course tasting menu. Michelin-starred excellence.',
    hours: '6:00 PM \u2014 11:00 PM'
  },
  {
    name: 'Kuro', img: 'https://picsum.photos/seed/sushibar/800/500.jpg',
    desc: 'Omakase sushi bar with imported Japanese ingredients. Intimate 12-seat counter experience.',
    hours: '12:00 PM \u2014 10:00 PM'
  },
  {
    name: 'The Gilded Bar', img: 'https://picsum.photos/seed/cocktailbar/800/500.jpg',
    desc: 'Artisan cocktails and live jazz in a 1920s-inspired setting. The perfect nightcap destination.',
    hours: '5:00 PM \u2014 1:00 AM'
  }
];

const EXPERIENCES = [
  { icon: 'fa-spa', title: 'Spa & Wellness', desc: 'Full-service spa with thermal pools, Hammam, and bespoke treatment rituals.' },
  { icon: 'fa-person-swimming', title: 'Infinity Pool', desc: 'Rooftop heated pool with skyline views, private cabanas, and poolside service.' },
  { icon: 'fa-dumbbell', title: 'Fitness Center', desc: 'State-of-the-art equipment, personal trainers, and sunrise yoga sessions.' },
  { icon: 'fa-car', title: 'Concierge & Transport', desc: 'Private chauffeur, airport transfers, and curated city experiences on demand.' },
];

const TESTIMONIALS = [
  { text: '"SPC Hotel redefines what luxury hospitality means. From the moment we arrived, every interaction felt personal and every detail was impeccable."', name: 'Catherine Morel', role: 'Travel Editor, Conde Nast', img: 'https://picsum.photos/seed/guest1/100/100.jpg' },
  { text: '"I have stayed at hundreds of hotels worldwide, and SPC Hotel stands apart. The Presidential Suite is a masterpiece of design."', name: 'Alexander Reinhardt', role: 'CEO, Meridian Group', img: 'https://picsum.photos/seed/guest2/100/100.jpg' },
  { text: '"Dinner at Lumiere was one of the most extraordinary culinary experiences of my life. The tasting menu was poetry on a plate."', name: 'Isabelle Fontaine', role: 'Michelin Guide Inspector', img: 'https://picsum.photos/seed/guest3/100/100.jpg' },
  { text: '"We chose SPC Hotel for our anniversary and it exceeded every expectation. The spa, the rooftop pool, the Gilded Bar \u2014 pure magic."', name: 'David & Sarah Chen', role: 'Returning Guests', img: 'https://picsum.photos/seed/guest4/100/100.jpg' }
];

const LUMIERE_MENU = [
  { name: 'Hokkaido Scallop Tartare', sub: 'yuzu, sea urchin, micro herbs', price: '\u2014' },
  { name: 'Wagyu A5 Carpaccio', sub: 'truffle jus, parmesan crisp, rocket', price: '\u2014' },
  { name: 'Pan-Seared Dover Sole', sub: 'brown butter, capers, lemon beurre blanc', price: '\u2014' },
  { name: 'Roasted Rhubarb Souffle', sub: 'vanilla bean creme anglaise, pistachio', price: '\u2014' },
];

const BAR_MENU = [
  { name: 'The SPC Old Fashioned', sub: '25yr bourbon, demerara, aromatic bitters', price: '\u20B11,450' },
  { name: 'Gold Leaf Negroni', sub: 'gin, Campari, sweet vermouth, 24k gold leaf', price: '\u20B11,550' },
  { name: 'Garden of Babylon', sub: 'gin, elderflower, cucumber, lime, tonic mist', price: '\u20B11,200' },
  { name: 'Smoked Espresso Martini', sub: 'vodka, cold brew, kahlua, applewood smoke', price: '\u20B11,350' },
];

/** Preview = site functions on; Design = editing only */
function isSiteInteractive() {
  if (window.HMSTemplateEditor && typeof window.HMSTemplateEditor.isSiteInteractive === 'function') {
    return window.HMSTemplateEditor.isSiteInteractive();
  }
  if (typeof window.__HMS_SITE_INTERACTIVE__ === 'boolean') return window.__HMS_SITE_INTERACTIVE__;
  return !document.body.classList.contains('hms-design-mode');
}

function hmsPrompt(message, defaultValue) {
  if (window.HMSSiteContent && typeof window.HMSSiteContent.safePrompt === 'function') {
    return window.HMSSiteContent.safePrompt(message, defaultValue);
  }
  try {
    const host = (window.top && window.top.prompt) ? window.top : window;
    return host.prompt(message, defaultValue == null ? '' : String(defaultValue));
  } catch (e) {
    return defaultValue == null ? null : String(defaultValue);
  }
}

function hmsConfirm(message) {
  if (window.HMSSiteContent && typeof window.HMSSiteContent.safeConfirm === 'function') {
    return window.HMSSiteContent.safeConfirm(message);
  }
  try {
    const host = (window.top && window.top.confirm) ? window.top : window;
    return !!host.confirm(message);
  } catch (e) {
    return true;
  }
}

/** Open a file picker and return an image data-URL (works inside the builder iframe). */
function pickImageFile(onPicked) {
  if (window.HMSSiteContent && typeof window.HMSSiteContent.pickImageFile === 'function') {
    window.HMSSiteContent.pickImageFile(onPicked);
    return;
  }
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

function resolveCardImg(kind, id, fallback) {
  if (window.HMSSiteContent && typeof window.HMSSiteContent.getCardImage === 'function') {
    return window.HMSSiteContent.getCardImage(kind, id, fallback) || fallback;
  }
  return fallback;
}

function changeCardImg(kind, id, onDone) {
  pickImageFile(function (url) {
    if (!url) return;
    if (window.HMSSiteContent && typeof window.HMSSiteContent.setCardImage === 'function') {
      window.HMSSiteContent.setCardImage(kind, id, url);
    }
    if (typeof onDone === 'function') onDone(url);
  });
}

function toolBtnStyle(kind) {
  const base = { width: 28, height: 28, borderRadius: 8, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', border: '1px solid var(--border)' };
  if (kind === 'danger') return Object.assign({}, base, { background: 'rgba(127,29,29,0.85)', color: '#fecaca', borderColor: '#7f1d1d' });
  if (kind === 'image') return Object.assign({}, base, { background: 'rgba(12,11,9,0.85)', color: '#38bdf8' });
  return Object.assign({}, base, { background: 'rgba(12,11,9,0.85)', color: 'var(--accent)' });
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• TOAST COMPONENT â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function Toast({ message, visible }) {
  return (
    <div className={`toast-el${visible ? ' show' : ''}`}>
      <i className="fa-solid fa-circle-check" style={{ color: 'var(--accent)', fontSize: '1.1rem' }}></i>
      <span>{message}</span>
    </div>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• MOBILE MENU â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function MobileMenu({ open, onClose, onNavigate, links }) {
  const items = [...(links || [])];
  return (
    <div className={`mobile-menu${open ? ' open' : ''}`}>
      {items.map(item => (
        <button key={item.id || item.key} onClick={() => { onNavigate(item.key); onClose(); }}>
          {item.label}
        </button>
      ))}
    </div>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• NAVBAR â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function NavBar({ currentPage, onNavigate, onToggleMobile, mobileOpen, links, canEditNav, onAddNav, onEditNav, onRemoveNav }) {
  const PAGE_OPTIONS = [
    { key: 'home', label: 'Home' },
    { key: 'rooms', label: 'Rooms' },
    { key: 'restaurant', label: 'Restaurant' },
    { key: 'experience', label: 'Experience' },
    { key: 'booking', label: 'Book Now' },
  ];

  const handleAdd = (e) => {
    e.preventDefault();
    e.stopPropagation();
    const label = hmsPrompt('New navigation label', 'New Page');
    if (!label) return;
    const keys = PAGE_OPTIONS.map(p => p.key).join(', ');
    const key = hmsPrompt('Link to page (' + keys + ')', 'home');
    if (!key) return;
    onAddNav({ label: label.trim(), key: key.trim() });
  };

  const handleEdit = (e, link) => {
    e.preventDefault();
    e.stopPropagation();
    const label = hmsPrompt('Navigation label', link.label);
    if (label == null || !label.trim()) return;
    const key = hmsPrompt('Link to page', link.key);
    if (key == null || !key.trim()) return;
    onEditNav(link.id, { label: label.trim(), key: key.trim() });
  };

  const handleRemove = (e, id) => {
    e.preventDefault();
    e.stopPropagation();
    if (hmsConfirm('Remove this navigation link?')) onRemoveNav(id);
  };

  return (
    <nav className="nav-bar" role="navigation" aria-label="Main navigation">
      <div style={{ maxWidth: 1200, margin: '0 auto', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '1rem' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.65rem' }}>
          <button onClick={() => onNavigate('home')} style={{ background: 'none', border: 'none', cursor: 'pointer', padding: 0 }}>
            <span style={{ color: 'var(--fg)', fontSize: '1.05rem', fontWeight: 600, letterSpacing: '0.18em', textTransform: 'uppercase' }}>SPC HOTEL</span>
          </button>
        </div>
        <div className="nav-links-desktop" style={{ display: 'flex', alignItems: 'center', gap: '1.1rem', flexWrap: 'wrap', justifyContent: 'flex-end' }}>
          {canEditNav && (
            <button
              type="button"
              className="nav-add-btn"
              title="Add navigation link"
              onClick={handleAdd}
              data-hms-no-edit="1"
            >+</button>
          )}
          {(links || []).map(link => (
            <div key={link.id || link.key} className="nav-item">
              <button
                className={`nav-link${currentPage === link.key ? ' active' : ''}`}
                onClick={() => onNavigate(link.key)}
              >
                {link.label}
              </button>
              {canEditNav && (
                <span className="nav-edit-tools" data-hms-no-edit="1">
                  <button type="button" title="Edit link" onClick={(e) => handleEdit(e, link)}
                    style={{ border: 'none', background: 'transparent', color: 'var(--accent)', cursor: 'pointer', fontSize: 11, padding: '0 2px', lineHeight: 1 }}><i className="fa-solid fa-pen" style={{fontSize:10}}></i></button>
                  <button type="button" title="Remove link" onClick={(e) => handleRemove(e, link.id)}
                    style={{ border: 'none', background: 'transparent', color: '#f87171', cursor: 'pointer', fontSize: 12, padding: '0 2px', fontWeight: 700, lineHeight: 1 }}><i className="fa-solid fa-xmark" style={{fontSize:12}}></i></button>
                </span>
              )}
            </div>
          ))}
        </div>
        <button className={`hamburger${mobileOpen ? ' active' : ''}`} onClick={onToggleMobile} aria-label="Toggle menu" data-hms-no-edit="1">
          <span></span><span></span><span></span>
        </button>
      </div>
    </nav>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• HOME PAGE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function HomePage({ onNavigate, onToast, rooms, menus, canEditRooms, onAddRoom, onEditRoom, onRemoveRoom }) {
  const roomList = (rooms && rooms.length) ? rooms : ROOMS;
  const menuList = (menus && menus.length) ? menus : (window.HMSSiteContent ? window.HMSSiteContent.DEFAULT_MENUS : []);

  const handleAddRoom = (e) => {
    if (e && e.stopPropagation) e.stopPropagation();
    // No iframe prompt — add immediately so Design mode always works.
    if (onAddRoom) {
      onAddRoom({
        name: 'New Suite',
        label: 'Classic',
        category: 'Classic',
        price: 250,
        desc: 'Add a short description for this room.',
        img: 'https://picsum.photos/seed/room' + Date.now() + '/800/600.jpg',
        amenities: [
          { icon: 'fa-bed', text: 'Bed' },
          { icon: 'fa-wifi', text: 'WiFi' },
        ],
      });
    }
    if (onToast) onToast('Room card added — click the pencil to edit');
  };

  const handleEditRoom = (room) => {
    const name = hmsPrompt('Room name', room.name);
    if (name == null || !String(name).trim()) return;
    const priceRaw = hmsPrompt('Price per night', String(room.price || 200));
    if (priceRaw == null) return;
    const price = Math.max(1, parseInt(priceRaw || String(room.price || 200), 10) || room.price || 200);
    const desc = hmsPrompt('Description', room.desc || '');
    if (desc == null) return;
    if (onEditRoom) onEditRoom(room.id, { name: String(name).trim(), price, desc: String(desc).trim() });
  };

  return (
    <>
      <section className="hero" data-hms-section="hero" data-hms-bg-target="1">
        <div className="hero-bg" data-hms-bg-target="1"></div>
        <div className="hero-overlay"></div>
        <div style={{ position: 'relative', zIndex: 2, textAlign: 'center', padding: '0 1.5rem', maxWidth: 760 }}>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '1.25rem' }}>Boutique Luxury</p>
          <h1 className="font-display hero-title" data-hms-move-root="1" style={{ fontSize: '4.2rem', fontWeight: 900, lineHeight: 1.08, marginBottom: '1.25rem', textAlign: 'center' }}>
            <span style={{ display: 'block' }}>Where Elegance</span>
            <span style={{ display: 'block', color: 'var(--accent)', fontStyle: 'italic', fontWeight: 400 }}>Meets Comfort</span>
          </h1>
          <p style={{ color: 'var(--fg-muted)', fontSize: '1.05rem', fontWeight: 300, maxWidth: 480, margin: '0 auto 2.25rem', lineHeight: 1.7 }}>
            Nestled in the heart of the city, SPC Hotel offers an unparalleled experience of refined hospitality, curated dining, and timeless sophistication.
          </p>
          <div style={{ display: 'flex', gap: '1rem', justifyContent: 'center', flexWrap: 'wrap' }}>
            <button className="btn-primary" onClick={() => onNavigate('rooms')}>
              Explore Rooms <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
            </button>
            <button className="btn-outline" onClick={() => onNavigate('booking')}>Book Now</button>
          </div>
        </div>
      </section>

      <section data-hms-section="rooms" data-hms-bg-target="1" style={{ padding: '5rem 1.5rem 3rem', maxWidth: 1200, margin: '0 auto' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'end', gap: '1rem', marginBottom: '2rem', flexWrap: 'wrap' }}>
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.6rem' }}>Accommodations</p>
            <h2 className="font-display" style={{ fontSize: '2.2rem', margin: 0 }}>Available Rooms</h2>
          </div>
          <button className="btn-outline" onClick={() => onNavigate('rooms')} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>View all rooms</button>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: '1.25rem' }}>
          {roomList.map(room => (
            <div key={room.id} className="room-card" style={{ cursor: 'pointer', position: 'relative' }} onClick={() => onNavigate('rooms')}>
              {canEditRooms && (
                <div style={{ position: 'absolute', top: 10, right: 10, zIndex: 3, display: 'flex', gap: 6 }}
                  data-hms-no-edit="1"
                  onClick={e => e.stopPropagation()}>
                  <button type="button" title="Change image" onClick={() => pickImageFile((url) => { if (url && onEditRoom) onEditRoom(room.id, { img: url }); if (onToast) onToast('Room image updated'); })}
                    style={toolBtnStyle('image')}><i className="fa-solid fa-image" style={{fontSize:11}}></i></button>
                  <button type="button" title="Edit room" onClick={() => handleEditRoom(room)}
                    style={toolBtnStyle('edit')}><i className="fa-solid fa-pen" style={{fontSize:10}}></i></button>
                  <button type="button" title="Remove room" onClick={() => onRemoveRoom && onRemoveRoom(room.id)}
                    style={toolBtnStyle('danger')}><i className="fa-solid fa-xmark" style={{fontSize:12}}></i></button>
                </div>
              )}
              <div style={{ height: 180, overflow: 'hidden', borderRadius: '12px 12px 0 0' }}>
                <img src={room.img} alt={room.name} style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block' }} />
              </div>
              <div style={{ padding: '1.1rem 1.15rem 1.25rem' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', gap: 8, alignItems: 'start' }}>
                  <h3 className="font-display" style={{ fontSize: '1.15rem', margin: 0 }}>{room.name}</h3>
                  <span style={{ color: 'var(--accent)', fontWeight: 700, whiteSpace: 'nowrap' }}>{formatPeso(room.price)}</span>
                </div>
                <p style={{ color: 'var(--fg-muted)', fontSize: '0.8rem', margin: '0.55rem 0 0', lineHeight: 1.55 }}>
                  {(room.desc || '').slice(0, 90)}{(room.desc || '').length > 90 ? 'â€¦' : ''}
                </p>
              </div>
            </div>
          ))}
          {canEditRooms && (
            <button
              type="button"
              onClick={handleAddRoom}
              onMouseDown={(e) => e.stopPropagation()}
              title="Add room card"
              data-hms-no-edit="1"
              data-hms-action="add-room"
              style={{
                minHeight: 280, borderRadius: 12, border: '2px dashed #f43f5e',
                background: 'rgba(244,63,94,0.06)', color: '#fb7185', cursor: 'pointer',
                display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 10,
                fontFamily: 'Outfit, sans-serif', transition: 'transform .15s ease, background .15s ease',
              }}
              onMouseEnter={e => { e.currentTarget.style.background = 'rgba(244,63,94,0.12)'; e.currentTarget.style.transform = 'translateY(-2px)'; }}
              onMouseLeave={e => { e.currentTarget.style.background = 'rgba(244,63,94,0.06)'; e.currentTarget.style.transform = 'none'; }}
            >
              <span style={{ width: 52, height: 52, borderRadius: 14, border: '1.5px solid #f43f5e', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 28, lineHeight: 1 }}>+</span>
              <span style={{ fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', fontSize: 12 }}>Add Room Card</span>
              <span style={{ fontSize: 11, opacity: 0.75, maxWidth: 180, textAlign: 'center' }}>Cards auto-organize in the grid</span>
            </button>
          )}
        </div>
      </section>

      <section data-hms-section="dining" data-hms-bg-target="1" style={{ padding: '3rem 1.5rem 5rem', maxWidth: 1200, margin: '0 auto' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'end', gap: '1rem', marginBottom: '2rem', flexWrap: 'wrap' }}>
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.6rem' }}>Dining</p>
            <h2 className="font-display" style={{ fontSize: '2.2rem', margin: 0 }}>Restaurant Menu</h2>
          </div>
          <button className="btn-outline" onClick={() => onNavigate('restaurant')} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>View dining</button>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '0.85rem' }}>
          {menuList.slice(0, 6).map(item => (
            <div key={item.id || item.name} style={{ display: 'flex', gap: '0.85rem', padding: '0.85rem 1rem', border: '1px solid var(--border)', borderRadius: 12, background: 'var(--card)', alignItems: 'center' }}>
              <img src={menuFoodImg(item)} alt={item.name} loading="lazy" style={{ width: 64, height: 64, borderRadius: 8, objectFit: 'cover', flexShrink: 0 }} />
              <div style={{ flex: 1, minWidth: 0 }}>
                <p style={{ margin: 0, fontWeight: 700, fontSize: '0.95rem' }}>{item.name}</p>
                <p style={{ margin: '0.35rem 0 0', color: 'var(--fg-muted)', fontSize: '0.78rem', lineHeight: 1.45 }}>{item.sub}</p>
                {item.category ? <p style={{ margin: '0.45rem 0 0', color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.12em', textTransform: 'uppercase' }}>{item.category}</p> : null}
              </div>
              <span style={{ fontWeight: 700, color: 'var(--accent)', whiteSpace: 'nowrap' }}>{item.price || 'â€”'}</span>
            </div>
          ))}
        </div>
      </section>
    </>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• ROOMS PAGE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function RoomTabBar({ tabs, active, onChange, items, getKey }) {
  const keyFn = getKey || ((it) => normalizeRoomCategory(it.category || it.label));
  const counts = useMemo(() => {
    const map = {};
    tabs.forEach(t => {
      map[t] = items.filter(it => keyFn(it) === t).length;
    });
    return map;
  }, [tabs, items, keyFn]);

  return (
    <div className="tab-bar" role="tablist">
      {tabs.map(tab => (
        <button
          key={tab}
          type="button"
          className={`tab-btn${active === tab ? ' active' : ''}`}
          onClick={() => onChange(tab)}
          role="tab"
          aria-selected={active === tab}
          data-hms-no-edit="1"
        >
          {tab}
          <span className="tab-count">{counts[tab] || 0}</span>
        </button>
      ))}
    </div>
  );
}

function nightsBetween(checkIn, checkOut) {
  if (!checkIn || !checkOut) return 1;
  const days = Math.ceil((new Date(checkOut) - new Date(checkIn)) / 86400000);
  return Math.max(1, days);
}

function RoomDetailModal({ room, onClose, onChangeStatus, canEditStatus, onReserve, onToast }) {
  if (!room) return null;
  const status = normalizeRoomStatus(room.status);
  const [step, setStep] = useState('details');
  const today = new Date().toISOString().split('T')[0];
  const [guestForm, setGuestForm] = useState({
    fullName: '',
    contactNo: '',
    email: '',
    idNumber: '',
    checkIn: '',
    checkOut: '',
  });
  const [paymentForm, setPaymentForm] = useState({
    type: 'full',
    amount: '',
    method: 'Cash',
    reference: '',
    payerName: '',
    notes: '',
  });

  useEffect(() => {
    setStep('details');
    setGuestForm({ fullName: '', contactNo: '', email: '', idNumber: '', checkIn: '', checkOut: '' });
    setPaymentForm({ type: 'full', amount: '', method: 'Cash', reference: '', payerName: '', notes: '' });
  }, [room.id]);

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const nights = nightsBetween(guestForm.checkIn, guestForm.checkOut);
  const totalDue = nights * (Number(room.price) || 0);

  const updateGuest = (field, value) => {
    setGuestForm(prev => {
      const next = { ...prev, [field]: value };
      if (field === 'checkIn') next.checkOut = '';
      return next;
    });
  };

  const updatePayment = (field, value) => {
    setPaymentForm(prev => Object.assign({}, prev, { [field]: value }));
  };

  const handleRegisterSubmit = (e) => {
    e.preventDefault();
    if (!isSiteInteractive()) return;
    if (guestForm.checkOut && guestForm.checkIn && guestForm.checkOut <= guestForm.checkIn) {
      if (onToast) onToast('Check-Out must be after Check-In.');
      return;
    }
    setPaymentForm(prev => Object.assign({}, prev, {
      type: 'full',
      amount: String(totalDue),
      payerName: guestForm.fullName || prev.payerName,
    }));
    setStep('payment');
  };

  const handlePaymentSubmit = (e) => {
    e.preventDefault();
    if (!isSiteInteractive()) return;

    const payAmount = paymentForm.type === 'full'
      ? totalDue
      : Math.max(0, parseFloat(paymentForm.amount) || 0);

    if (paymentForm.type === 'partial' && payAmount <= 0) {
      if (onToast) onToast('Enter a valid partial payment amount.');
      return;
    }
    if (paymentForm.type === 'partial' && payAmount >= totalDue) {
      if (onToast) onToast('Partial payment must be less than the total due.');
      return;
    }

    const payment = {
      type: paymentForm.type === 'full' ? 'Full' : 'Partial',
      amountPaid: payAmount,
      totalDue,
      balance: Math.max(0, totalDue - payAmount),
      method: paymentForm.method,
      reference: paymentForm.reference.trim(),
      payerName: paymentForm.payerName.trim() || guestForm.fullName,
      notes: paymentForm.notes.trim(),
      paidAt: new Date().toISOString(),
      nights,
    };

    if (typeof onReserve === 'function') {
      onReserve(room, { ...guestForm }, payment);
    } else if (onToast) {
      onToast(`Payment received for ${room.name}.`);
    }
    onClose();
  };

  const canReserve = status === 'Available';
  const fieldLabel = { fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' };

  return (
    <div className="room-modal-overlay" data-hms-no-edit="1" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" onClick={e => e.stopPropagation()}>
        <div className="room-modal-img">
          <img src={room.img} alt={room.name} />
          <button type="button" className="room-modal-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div style={{ padding: '1.5rem 1.5rem 1.75rem' }}>
          {step === 'details' && (
            <>
              <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
                {room.label || room.category || 'Room'}
              </p>
              <h2 className="font-display" style={{ fontSize: '1.65rem', marginBottom: '1.25rem' }}>{room.name}</h2>

              <div style={{ display: 'grid', gap: '1rem' }}>
                <div>
                  <p style={{ fontSize: '0.68rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.4rem' }}>Status</p>
                  <span className={`room-status-badge ${roomStatusClass(status)}`} style={{ position: 'static' }}>{status}</span>
                </div>
                <div>
                  <p style={{ fontSize: '0.68rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.4rem' }}>Price</p>
                  <p style={{ color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontSize: '1.25rem', margin: 0 }}>
                    {formatPeso(room.price)}
                  </p>
                </div>
                <div>
                  <p style={{ fontSize: '0.68rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.4rem' }}>Description</p>
                  <p style={{ color: 'var(--fg-muted)', fontSize: '0.88rem', lineHeight: 1.6, margin: 0 }}>{room.desc}</p>
                </div>
              </div>

              {canEditStatus && (
                <div style={{ marginTop: '1.35rem' }}>
                  <p style={{ fontSize: '0.7rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.65rem' }}>Update Status</p>
                  <div className="room-status-picker">
                    {ROOM_STATUSES.map(s => (
                      <button
                        key={s}
                        type="button"
                        className={`room-status-option${status === s ? ' active' : ''}`}
                        onClick={() => onChangeStatus(s)}
                      >
                        {s}
                      </button>
                    ))}
                  </div>
                </div>
              )}

              <div style={{ marginTop: '1.5rem' }}>
                {canReserve ? (
                  <button type="button" className="btn-primary" style={{ width: '100%', justifyContent: 'center' }}
                    onClick={() => setStep('register')}>
                    Reserve Now <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
                  </button>
                ) : (
                  <p style={{ textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.82rem', margin: 0 }}>
                    This room is {status.toLowerCase()} and cannot be reserved right now.
                  </p>
                )}
              </div>
            </>
          )}

          {step === 'register' && (
            <>
              <button type="button" onClick={() => setStep('details')}
                style={{ background: 'none', border: 'none', color: 'var(--fg-muted)', cursor: 'pointer', fontSize: '0.78rem', padding: 0, marginBottom: '0.85rem', fontFamily: 'Outfit, sans-serif' }}>
                <i className="fa-solid fa-arrow-left" style={{ marginRight: 6, fontSize: '0.7rem' }}></i> Back
              </button>
              <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
                Reservation
              </p>
              <h2 className="font-display" style={{ fontSize: '1.55rem', marginBottom: '0.35rem' }}>Register Guest</h2>
              <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', marginBottom: '1.25rem' }}>
                Completing reservation for <strong style={{ color: 'var(--fg)' }}>{room.name}</strong>
              </p>

              <form onSubmit={handleRegisterSubmit}>
                <div style={{ display: 'grid', gap: '0.85rem' }}>
                  <div>
                    <label style={fieldLabel}>Full Name</label>
                    <input type="text" className="booking-input" placeholder="e.g. James Whitfield" value={guestForm.fullName}
                      onChange={e => updateGuest('fullName', e.target.value)} required />
                  </div>
                  <div>
                    <label style={fieldLabel}>Contact No.</label>
                    <input type="tel" className="booking-input" placeholder="e.g. +63 912 345 6789" value={guestForm.contactNo}
                      onChange={e => updateGuest('contactNo', e.target.value)} required />
                  </div>
                  <div>
                    <label style={fieldLabel}>Email</label>
                    <input type="email" className="booking-input" placeholder="james@example.com" value={guestForm.email}
                      onChange={e => updateGuest('email', e.target.value)} required />
                  </div>
                  <div>
                    <label style={fieldLabel}>ID</label>
                    <input type="text" className="booking-input" placeholder="Government / passport ID" value={guestForm.idNumber}
                      onChange={e => updateGuest('idNumber', e.target.value)} required />
                  </div>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.85rem' }}>
                    <div>
                      <label style={fieldLabel}>Check-In</label>
                      <input type="date" className="booking-input" value={guestForm.checkIn} min={today}
                        onChange={e => updateGuest('checkIn', e.target.value)} required style={{ colorScheme: 'dark' }} />
                    </div>
                    <div>
                      <label style={fieldLabel}>Check-Out</label>
                      <input type="date" className="booking-input" value={guestForm.checkOut} min={guestForm.checkIn || today}
                        onChange={e => updateGuest('checkOut', e.target.value)} required style={{ colorScheme: 'dark' }} />
                    </div>
                  </div>
                </div>
                <button type="submit" className="btn-primary" style={{ width: '100%', justifyContent: 'center', marginTop: '1.35rem' }}>
                  Proceed Payment <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
                </button>
              </form>
            </>
          )}

          {step === 'payment' && (
            <>
              <button type="button" onClick={() => setStep('register')}
                style={{ background: 'none', border: 'none', color: 'var(--fg-muted)', cursor: 'pointer', fontSize: '0.78rem', padding: 0, marginBottom: '0.85rem', fontFamily: 'Outfit, sans-serif' }}>
                <i className="fa-solid fa-arrow-left" style={{ marginRight: 6, fontSize: '0.7rem' }}></i> Back
              </button>
              <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
                Payment
              </p>
              <h2 className="font-display" style={{ fontSize: '1.55rem', marginBottom: '0.35rem' }}>Process Payment</h2>
              <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', marginBottom: '1rem' }}>
                Guest <strong style={{ color: 'var(--fg)' }}>{guestForm.fullName}</strong> · {room.name}
              </p>

              <div style={{ background: 'rgba(255,255,255,0.03)', border: '1px solid var(--border)', borderRadius: 10, padding: '0.9rem 1rem', marginBottom: '1.15rem' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', marginBottom: '0.35rem' }}>
                  <span style={{ color: 'var(--fg-muted)', fontSize: '0.8rem' }}>{nights} night{nights > 1 ? 's' : ''} × {formatPeso(room.price)}</span>
                  <strong style={{ color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontSize: '1.15rem' }}>{formatPeso(totalDue)}</strong>
                </div>
                <p style={{ margin: 0, fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                  {guestForm.checkIn} → {guestForm.checkOut}
                </p>
              </div>

              <form onSubmit={handlePaymentSubmit}>
                <div style={{ display: 'grid', gap: '0.85rem' }}>
                  <div>
                    <label style={fieldLabel}>Payment Type</label>
                    <div className="room-status-picker">
                      <button type="button" className={`room-status-option${paymentForm.type === 'full' ? ' active' : ''}`}
                        onClick={() => setPaymentForm(prev => Object.assign({}, prev, { type: 'full', amount: String(totalDue) }))}>
                        Full Payment
                      </button>
                      <button type="button" className={`room-status-option${paymentForm.type === 'partial' ? ' active' : ''}`}
                        onClick={() => {
                          const half = Math.round(totalDue / 2);
                          setPaymentForm(prev => Object.assign({}, prev, { type: 'partial', amount: String(half > 0 ? half : '') }));
                        }}>
                        Partial Payment
                      </button>
                    </div>
                  </div>

                  <div>
                    <label style={fieldLabel}>Amount to Pay</label>
                    <input
                      type="number"
                      className="booking-input"
                      min="1"
                      max={paymentForm.type === 'partial' ? totalDue - 1 : totalDue}
                      step="0.01"
                      value={paymentForm.type === 'full' ? totalDue : paymentForm.amount}
                      onChange={e => updatePayment('amount', e.target.value)}
                      readOnly={paymentForm.type === 'full'}
                      required
                    />
                    {paymentForm.type === 'partial' && (
                      <p style={{ margin: '0.4rem 0 0', fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                        Remaining balance: {formatPeso(Math.max(0, totalDue - (parseFloat(paymentForm.amount) || 0)))}
                      </p>
                    )}
                  </div>

                  <div>
                    <label style={fieldLabel}>Payment Method</label>
                    <select className="booking-input" value={paymentForm.method} onChange={e => updatePayment('method', e.target.value)} required>
                      <option value="Cash">Cash</option>
                      <option value="Credit Card">Credit Card</option>
                      <option value="Debit Card">Debit Card</option>
                      <option value="Bank Transfer">Bank Transfer</option>
                      <option value="GCash">GCash</option>
                      <option value="PayMaya">PayMaya</option>
                    </select>
                  </div>

                  <div>
                    <label style={fieldLabel}>Payer Name</label>
                    <input type="text" className="booking-input" placeholder="Name on payment" value={paymentForm.payerName}
                      onChange={e => updatePayment('payerName', e.target.value)} required />
                  </div>

                  <div>
                    <label style={fieldLabel}>Reference / Transaction ID</label>
                    <input type="text" className="booking-input" placeholder="Receipt no., card last 4, or ref #" value={paymentForm.reference}
                      onChange={e => updatePayment('reference', e.target.value)} required={paymentForm.method !== 'Cash'} />
                  </div>

                  <div>
                    <label style={fieldLabel}>Payment Notes</label>
                    <input type="text" className="booking-input" placeholder="Optional notes" value={paymentForm.notes}
                      onChange={e => updatePayment('notes', e.target.value)} />
                  </div>
                </div>

                <button type="submit" className="btn-primary" style={{ width: '100%', justifyContent: 'center', marginTop: '1.35rem' }}>
                  Complete Reservation <i className="fa-solid fa-check" style={{ fontSize: '0.7rem' }}></i>
                </button>
              </form>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

function normalizeLookup(value) {
  return String(value || '').trim().toLowerCase();
}

function findGuestBookings(rooms, query) {
  const nameQ = normalizeLookup(query.fullName);
  const idQ = normalizeLookup(query.idNumber);
  const emailQ = normalizeLookup(query.email);
  const contactQ = normalizeLookup(query.contactNo).replace(/\s+/g, '');

  if (!nameQ && !idQ && !emailQ && !contactQ) return [];

  return (rooms || []).reduce((matches, room) => {
    const res = room && room.reservation;
    if (!res) return matches;

    const resName = normalizeLookup(res.fullName);
    const resId = normalizeLookup(res.idNumber);
    const resEmail = normalizeLookup(res.email);
    const resContact = normalizeLookup(res.contactNo).replace(/\s+/g, '');

    if (nameQ && !resName.includes(nameQ)) return matches;
    if (idQ && resId !== idQ) return matches;
    if (emailQ && resEmail !== emailQ) return matches;
    if (contactQ && resContact !== contactQ) return matches;

    // At least one provided field must actually match something meaningful
    const anyHit =
      (nameQ && resName.includes(nameQ)) ||
      (idQ && resId === idQ) ||
      (emailQ && resEmail === emailQ) ||
      (contactQ && resContact === contactQ);

    if (!anyHit) return matches;
    matches.push({ room, reservation: res });
    return matches;
  }, []);
}

function VerifyGuestModal({ open, rooms, onClose }) {
  const [form, setForm] = useState({ fullName: '', idNumber: '', email: '', contactNo: '' });
  const [searched, setSearched] = useState(false);
  const [matches, setMatches] = useState([]);
  const fieldLabel = { fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' };

  useEffect(() => {
    if (!open) return;
    setForm({ fullName: '', idNumber: '', email: '', contactNo: '' });
    setSearched(false);
    setMatches([]);
  }, [open]);

  useEffect(() => {
    if (!open) return undefined;
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [open, onClose]);

  if (!open) return null;

  const update = (field, value) => setForm(prev => Object.assign({}, prev, { [field]: value }));

  const handleVerify = (e) => {
    e.preventDefault();
    if (!isSiteInteractive()) return;
    const found = findGuestBookings(rooms, form);
    setMatches(found);
    setSearched(true);
  };

  return (
    <div className="room-modal-overlay" data-hms-no-edit="1" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" onClick={e => e.stopPropagation()}>
        <div style={{ padding: '1.5rem 1.5rem 1.75rem', position: 'relative' }}>
          <button type="button" className="room-modal-close" onClick={onClose} aria-label="Close" style={{ position: 'absolute', top: '1rem', right: '1rem' }}>
            <i className="fa-solid fa-xmark"></i>
          </button>

          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
            Front Desk
          </p>
          <h2 className="font-display" style={{ fontSize: '1.55rem', marginBottom: '0.35rem', paddingRight: '2.5rem' }}>Verify Guest</h2>
          <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', marginBottom: '1.25rem' }}>
            Enter guest details to check if they have an existing reservation.
          </p>

          <form onSubmit={handleVerify}>
            <div style={{ display: 'grid', gap: '0.85rem' }}>
              <div>
                <label style={fieldLabel}>Full Name</label>
                <input type="text" className="booking-input" placeholder="e.g. James Whitfield" value={form.fullName}
                  onChange={e => update('fullName', e.target.value)} />
              </div>
              <div>
                <label style={fieldLabel}>ID</label>
                <input type="text" className="booking-input" placeholder="Government / passport ID" value={form.idNumber}
                  onChange={e => update('idNumber', e.target.value)} />
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.85rem' }}>
                <div>
                  <label style={fieldLabel}>Email</label>
                  <input type="email" className="booking-input" placeholder="Optional" value={form.email}
                    onChange={e => update('email', e.target.value)} />
                </div>
                <div>
                  <label style={fieldLabel}>Contact No.</label>
                  <input type="tel" className="booking-input" placeholder="Optional" value={form.contactNo}
                    onChange={e => update('contactNo', e.target.value)} />
                </div>
              </div>
            </div>
            <button type="submit" className="btn-primary" style={{ width: '100%', justifyContent: 'center', marginTop: '1.25rem' }}
              disabled={!form.fullName.trim() && !form.idNumber.trim() && !form.email.trim() && !form.contactNo.trim()}>
              <i className="fa-solid fa-magnifying-glass" style={{ fontSize: '0.75rem' }}></i> Search Booking
            </button>
          </form>

          {searched && (
            <div style={{ marginTop: '1.35rem' }}>
              {matches.length === 0 ? (
                <div style={{ border: '1px solid rgba(244,63,94,0.35)', background: 'rgba(244,63,94,0.08)', borderRadius: 10, padding: '1rem 1.1rem' }}>
                  <p style={{ margin: 0, color: '#fb7185', fontWeight: 600, fontSize: '0.9rem' }}>
                    <i className="fa-solid fa-circle-xmark" style={{ marginRight: 8 }}></i>
                    No booking found
                  </p>
                  <p style={{ margin: '0.45rem 0 0', color: 'var(--fg-muted)', fontSize: '0.8rem' }}>
                    This guest does not have a registered reservation.
                  </p>
                </div>
              ) : (
                <div style={{ display: 'grid', gap: '0.85rem' }}>
                  <p style={{ margin: 0, color: '#4ade80', fontWeight: 600, fontSize: '0.9rem' }}>
                    <i className="fa-solid fa-circle-check" style={{ marginRight: 8 }}></i>
                    {matches.length} booking{matches.length > 1 ? 's' : ''} found
                  </p>
                  {matches.map(({ room, reservation }) => {
                    const payment = reservation.payment || null;
                    return (
                      <div key={room.id} style={{ border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', borderRadius: 10, padding: '1rem 1.1rem' }}>
                        <div style={{ display: 'flex', justifyContent: 'space-between', gap: '0.75rem', alignItems: 'start', marginBottom: '0.65rem' }}>
                          <div>
                            <p style={{ margin: 0, color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.12em', textTransform: 'uppercase' }}>
                              {room.label || room.category || 'Room'}
                            </p>
                            <h3 className="font-display" style={{ margin: '0.2rem 0 0', fontSize: '1.15rem' }}>{room.name}</h3>
                          </div>
                          <span className={`room-status-badge ${roomStatusClass(room.status)}`} style={{ position: 'static' }}>
                            {normalizeRoomStatus(room.status)}
                          </span>
                        </div>
                        <div style={{ display: 'grid', gap: '0.35rem', fontSize: '0.82rem', color: 'var(--fg-muted)' }}>
                          <p style={{ margin: 0 }}><strong style={{ color: 'var(--fg)' }}>Guest:</strong> {reservation.fullName}</p>
                          <p style={{ margin: 0 }}><strong style={{ color: 'var(--fg)' }}>ID:</strong> {reservation.idNumber || '—'}</p>
                          <p style={{ margin: 0 }}><strong style={{ color: 'var(--fg)' }}>Contact:</strong> {reservation.contactNo || '—'}</p>
                          <p style={{ margin: 0 }}><strong style={{ color: 'var(--fg)' }}>Email:</strong> {reservation.email || '—'}</p>
                          <p style={{ margin: 0 }}><strong style={{ color: 'var(--fg)' }}>Stay:</strong> {reservation.checkIn} → {reservation.checkOut}</p>
                          {payment && (
                            <p style={{ margin: 0 }}>
                              <strong style={{ color: 'var(--fg)' }}>Payment:</strong>{' '}
                              {payment.type} {formatPeso(payment.amountPaid)} via {payment.method}
                              {payment.balance > 0 ? ` · Balance ${formatPeso(payment.balance)}` : ''}
                            </p>
                          )}
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}

function createEmptyRoomForm() {
  return {
    name: '',
    category: '',
    status: 'Available',
    price: '',
    floor: '',
    desc: '',
  };
}

function validateRoomForm(form) {
  const errors = {};
  if (!String(form.name || '').trim()) errors.name = 'Room name is required.';
  if (!String(form.category || '').trim()) errors.category = 'Room type is required.';
  const price = parseFloat(String(form.price || '').replace(/,/g, ''));
  if (!String(form.price || '').trim() || !Number.isFinite(price) || price <= 0) {
    errors.price = 'Enter a valid price.';
  }
  return errors;
}

function buildRoomPayload(form) {
  const category = normalizeRoomCategory(form.category);
  const status = normalizeRoomStatus(form.status || 'Available');
  const price = Math.max(1, Math.round(parseFloat(String(form.price || '0').replace(/,/g, '')) || 0));
  const floor = String(form.floor || '').trim();
  const desc = String(form.desc || '').trim() || 'Add a short description for this room.';
  return {
    name: String(form.name || '').trim(),
    label: category,
    category,
    status,
    price,
    floor: floor || null,
    desc,
    img: 'https://picsum.photos/seed/room' + Date.now() + '/800/600.jpg',
    amenities: [
      { icon: 'fa-bed', text: 'Bed' },
      { icon: 'fa-wifi', text: 'WiFi' },
    ],
  };
}

function ManageRoomPanel({ onSubmit, onCancel }) {
  const [form, setForm] = useState(createEmptyRoomForm);
  const [errors, setErrors] = useState({});

  const fieldLabel = {
    fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase',
    color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem',
  };

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  const resetForm = () => {
    setForm(createEmptyRoomForm());
    setErrors({});
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!isSiteInteractive()) return;
    const nextErrors = validateRoomForm(form);
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length) {
      return;
    }
    const payload = buildRoomPayload(form);
    if (typeof onSubmit === 'function') onSubmit(payload);
    resetForm();
  };

  const handleCancel = () => {
    resetForm();
    if (typeof onCancel === 'function') onCancel();
  };

  const errorText = (key) => (
    errors[key]
      ? <p style={{ margin: '0.35rem 0 0', color: '#fb7185', fontSize: '0.72rem' }}>{errors[key]}</p>
      : null
  );

  return (
    <div className="rm-panel">
      <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>
        Inventory
      </p>
      <h3>Manage Room</h3>
      <p className="rm-panel-desc">
        Add a new room to the hotel inventory. It will appear in the Rooms section right away.
      </p>

      <form onSubmit={handleSubmit} className="rm-form-grid" noValidate>
        <div>
          <label style={fieldLabel}>Room Name *</label>
          <input
            type="text"
            className="booking-input"
            placeholder="e.g. Deluxe King Room"
            value={form.name}
            onChange={e => update('name', e.target.value)}
            style={errors.name ? { borderColor: '#f43f5e' } : undefined}
          />
          {errorText('name')}
        </div>

        <div className="rm-form-row">
          <div>
            <label style={fieldLabel}>Room Type *</label>
            <select
              className="booking-input"
              value={form.category}
              onChange={e => update('category', e.target.value)}
              style={errors.category ? { borderColor: '#f43f5e' } : undefined}
            >
              <option value="">Select type</option>
              {ROOM_CATEGORIES.map(c => <option key={c} value={c}>{c}</option>)}
            </select>
            {errorText('category')}
          </div>
          <div>
            <label style={fieldLabel}>Status</label>
            <select className="booking-input" value={form.status} onChange={e => update('status', e.target.value)}>
              {ROOM_STATUSES.map(s => <option key={s} value={s}>{s}</option>)}
            </select>
          </div>
        </div>

        <div className="rm-form-row">
          <div>
            <label style={fieldLabel}>Price / Night *</label>
            <input
              type="number"
              min="1"
              step="1"
              className="booking-input"
              placeholder="e.g. 4500"
              value={form.price}
              onChange={e => update('price', e.target.value)}
              style={errors.price ? { borderColor: '#f43f5e' } : undefined}
            />
            {errorText('price')}
          </div>
          <div>
            <label style={fieldLabel}>Floor</label>
            <input
              type="text"
              className="booking-input"
              placeholder="e.g. 3"
              value={form.floor}
              onChange={e => update('floor', e.target.value)}
            />
          </div>
        </div>

        <div>
          <label style={fieldLabel}>Description</label>
          <textarea
            className="booking-input"
            rows={3}
            placeholder="Short description of the room..."
            value={form.desc}
            onChange={e => update('desc', e.target.value)}
            style={{ resize: 'vertical', minHeight: 88 }}
          />
        </div>

        <div style={{ display: 'flex', gap: '0.75rem', marginTop: '0.35rem', flexWrap: 'wrap' }}>
          <button type="submit" className="btn-primary">
            <i className="fa-solid fa-plus" style={{ fontSize: '0.7rem' }}></i> Add Room
          </button>
          <button type="button" className="btn-outline" onClick={handleCancel} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
            Cancel
          </button>
        </div>
      </form>
    </div>
  );
}

function RoomManagementModal({ open, rooms, onClose, onAddRoom, onEditRoom, onRemoveRoom, onToast }) {
  const [activeNav, setActiveNav] = useState('manage-room');

  useEffect(() => {
    if (!open) return;
    setActiveNav('manage-room');
  }, [open]);

  useEffect(() => {
    if (!open) return undefined;
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [open, onClose]);

  if (!open) return null;

  void rooms;
  void onEditRoom;
  void onRemoveRoom;

  const handleAddRoom = (payload) => {
    if (typeof onAddRoom === 'function') onAddRoom(payload);
    if (onToast) onToast(`${payload.name} added to Rooms.`);
  };

  return (
    <div className="room-modal-overlay" data-hms-no-edit="1" onClick={onClose} role="dialog" aria-modal="true">
      <div className="rm-shell" style={{ width: 'min(920px, 100%)' }} onClick={e => e.stopPropagation()}>
        <aside className="rm-sidebar">
          <p className="rm-sidebar-title">Room Management</p>
          <button
            type="button"
            className={`rm-nav-item${activeNav === 'manage-room' ? ' active' : ''}`}
            onClick={() => setActiveNav('manage-room')}
          >
            <i className="fa-solid fa-bed" style={{ fontSize: '0.78rem', width: 14 }}></i>
            Manage Room
          </button>
        </aside>

        <div className="rm-content">
          <button type="button" className="room-modal-close" onClick={onClose} aria-label="Close" style={{ position: 'absolute', top: '1rem', right: '1rem' }}>
            <i className="fa-solid fa-xmark"></i>
          </button>

          {activeNav === 'manage-room' && (
            <ManageRoomPanel
              onSubmit={handleAddRoom}
              onCancel={onClose}
            />
          )}
        </div>
      </div>
    </div>
  );
}

function RoomsPage({ onNavigate, onToast, rooms, canEditRooms, canManageRooms, onAddRoom, onEditRoom, onRemoveRoom }) {
  const list = rooms && rooms.length ? rooms : ROOMS;
  const [tab, setTab] = useState('Classic');
  const [selectedRoomId, setSelectedRoomId] = useState(null);
  const [verifyOpen, setVerifyOpen] = useState(false);
  const [roomMgmtOpen, setRoomMgmtOpen] = useState(false);
  const filtered = list.filter(r => normalizeRoomCategory(r.category || r.label) === tab);
  const selectedRoom = list.find(r => r.id === selectedRoomId) || null;
  const showRoomManagement = !!canManageRooms;

  const handleAdd = (e) => {
    if (e && e.stopPropagation) e.stopPropagation();
    onAddRoom({
      name: 'New Suite',
      label: tab,
      category: tab,
      status: 'Available',
      price: 250,
      desc: 'Add a short description for this room.',
      img: 'https://picsum.photos/seed/room' + Date.now() + '/800/600.jpg',
      amenities: [
        { icon: 'fa-bed', text: 'Bed' },
        { icon: 'fa-wifi', text: 'WiFi' },
      ],
    });
    onToast('Room card added — click the pencil to edit');
  };

  const handleManageAddRoom = (payload) => {
    if (typeof onAddRoom === 'function') onAddRoom(payload);
    setTab(normalizeRoomCategory(payload.category || payload.label));
  };

  const handleEdit = (room) => {
    const name = hmsPrompt('Room name', room.name);
    if (name == null || !String(name).trim()) return;
    const priceRaw = hmsPrompt('Price per night', String(room.price || 200));
    if (priceRaw == null) return;
    const price = Math.max(1, parseInt(priceRaw || String(room.price || 200), 10) || room.price || 200);
    const categoryHint = ROOM_CATEGORIES.join(' / ');
    const categoryRaw = hmsPrompt('Category (' + categoryHint + ')', room.category || room.label || 'Classic');
    if (categoryRaw == null) return;
    const category = normalizeRoomCategory(categoryRaw);
    const statusRaw = hmsPrompt('Status (' + ROOM_STATUSES.join(' / ') + ')', normalizeRoomStatus(room.status));
    if (statusRaw == null) return;
    const status = normalizeRoomStatus(statusRaw);
    const desc = hmsPrompt('Description', room.desc || '');
    if (desc == null) return;
    onEditRoom(room.id, {
      name: String(name).trim(),
      price,
      category,
      label: category,
      status,
      desc: String(desc).trim(),
    });
  };

  const handleStatusChange = (status) => {
    if (!selectedRoom || !onEditRoom) return;
    onEditRoom(selectedRoom.id, { status });
    if (onToast) onToast(`${selectedRoom.name} marked as ${status}`);
  };

  const handleReserve = (room, guest, payment) => {
    if (onEditRoom) {
      onEditRoom(room.id, {
        status: 'Occupied',
        reservation: {
          fullName: guest.fullName,
          contactNo: guest.contactNo,
          email: guest.email,
          idNumber: guest.idNumber,
          checkIn: guest.checkIn,
          checkOut: guest.checkOut,
          reservedAt: new Date().toISOString(),
          payment: payment || null,
        },
      });
    }
    if (onToast) {
      const paid = payment ? ` · ${payment.type} ${formatPeso(payment.amountPaid)} via ${payment.method}` : '';
      onToast(`${guest.fullName} registered for ${room.name}${paid}.`);
    }
  };

  return (
    <>
      {showRoomManagement && (
        <div style={{ maxWidth: 1200, margin: '0 auto', padding: '7.25rem 1.5rem 0', display: 'flex', justifyContent: 'flex-start' }}>
          <button
            type="button"
            className="btn-outline"
            data-hms-no-edit="1"
            onClick={() => setRoomMgmtOpen(true)}
            style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}
          >
            <i className="fa-solid fa-screwdriver-wrench" style={{ fontSize: '0.75rem' }}></i> Room Management
          </button>
        </div>
      )}
      <div className="page-header" style={{ paddingTop: showRoomManagement ? '1.75rem' : undefined }}>
        <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.75rem' }}>Accommodations</p>
        <h1 className="font-display">Our Rooms & Suites</h1>
        <p>Each room is a sanctuary of design, blending modern luxury with artisanal craftsmanship and sweeping views.</p>
        <div style={{ marginTop: '1.5rem' }}>
          <button type="button" className="btn-outline" data-hms-no-edit="1" onClick={() => setVerifyOpen(true)}>
            <i className="fa-solid fa-user-check" style={{ fontSize: '0.75rem' }}></i> Verify Guest
          </button>
        </div>
      </div>
      <RoomTabBar tabs={ROOM_TABS} active={tab} onChange={setTab} items={list} />
      <section style={{ padding: '0 1.5rem 6rem', maxWidth: 1200, margin: '0 auto' }}>
        {filtered.length === 0 && !canEditRooms ? (
          <p style={{ textAlign: 'center', color: 'var(--fg-muted)', padding: '3rem 1rem' }}>No rooms found in this category.</p>
        ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '1.5rem' }}>
          {filtered.map(room => {
            const status = normalizeRoomStatus(room.status);
            return (
            <div key={room.id} className="room-card" style={{ position: 'relative' }}
              onClick={() => setSelectedRoomId(room.id)}>
              {canEditRooms && (
                <div style={{ position: 'absolute', top: 10, right: 10, zIndex: 3, display: 'flex', gap: 6 }}
                  data-hms-no-edit="1"
                  onClick={e => e.stopPropagation()}>
                  <button type="button" title="Change image" onClick={() => pickImageFile((url) => { if (url) onEditRoom(room.id, { img: url }); onToast('Room image updated'); })}
                    style={toolBtnStyle('image')}><i className="fa-solid fa-image" style={{fontSize:11}}></i></button>
                  <button type="button" title="Edit room" onClick={() => handleEdit(room)}
                    style={toolBtnStyle('edit')}><i className="fa-solid fa-pen" style={{fontSize:10}}></i></button>
                  <button type="button" title="Remove room" onClick={() => onRemoveRoom(room.id)}
                    style={toolBtnStyle('danger')}><i className="fa-solid fa-xmark" style={{fontSize:12}}></i></button>
                </div>
              )}
              <div className="room-card-img">
                <img src={room.img} alt={room.name} loading="lazy" />
                <div className={`room-status-badge ${roomStatusClass(status)}`}>{status}</div>
              </div>
              <div style={{ padding: '1.15rem 1.25rem 1.25rem' }}>
                <p style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.12em', textTransform: 'uppercase', marginBottom: '0.35rem' }}>
                  {room.label || room.category || 'Room'}
                </p>
                <h3 className="font-display" style={{ fontSize: '1.15rem', fontWeight: 700, margin: 0 }}>{room.name}</h3>
              </div>
            </div>
            );
          })}
              {canEditRooms && (
            <button
              type="button"
              onClick={handleAdd}
              onMouseDown={(e) => e.stopPropagation()}
              title="Add room card"
              data-hms-no-edit="1"
              data-hms-action="add-room"
              style={{
                minHeight: 320, borderRadius: 14, border: '2px dashed #f43f5e',
                background: 'rgba(244,63,94,0.06)', color: '#fb7185', cursor: 'pointer',
                display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 10,
                fontFamily: 'Outfit, sans-serif', transition: 'transform .15s ease, background .15s ease',
              }}
              onMouseEnter={e => { e.currentTarget.style.background = 'rgba(244,63,94,0.12)'; e.currentTarget.style.transform = 'translateY(-2px)'; }}
              onMouseLeave={e => { e.currentTarget.style.background = 'rgba(244,63,94,0.06)'; e.currentTarget.style.transform = 'none'; }}
            >
              <span style={{ width: 52, height: 52, borderRadius: 14, border: '1.5px solid #f43f5e', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 28, lineHeight: 1 }}>+</span>
              <span style={{ fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', fontSize: 12 }}>Add Room Card</span>
              <span style={{ fontSize: 11, opacity: 0.75, maxWidth: 180, textAlign: 'center' }}>Added under {tab}</span>
            </button>
          )}
        </div>
        )}
      </section>
      <RoomDetailModal
        room={selectedRoom}
        onClose={() => setSelectedRoomId(null)}
        onChangeStatus={handleStatusChange}
        canEditStatus={!!canEditRooms}
        onReserve={handleReserve}
        onToast={onToast}
      />
      <VerifyGuestModal
        open={verifyOpen}
        rooms={list}
        onClose={() => setVerifyOpen(false)}
      />
      {showRoomManagement && (
        <RoomManagementModal
          open={roomMgmtOpen}
          rooms={list}
          onClose={() => setRoomMgmtOpen(false)}
          onAddRoom={handleManageAddRoom}
          onEditRoom={onEditRoom}
          onRemoveRoom={onRemoveRoom}
          onToast={onToast}
        />
      )}
    </>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• RESTAURANT PAGE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function RestaurantPage({ onNavigate, onToast, menus, canEditMenus, cardImages }) {
  const menuList = (menus && menus.length) ? menus : (window.HMSSiteContent ? window.HMSSiteContent.DEFAULT_MENUS : []);
  const [menuTab, setMenuTab] = useState('Main Dishes');
  const filteredMenus = menuList.filter(item => normalizeMenuCategory(item.category) === menuTab);
  void cardImages;

  const handleAdd = () => {
    const name = hmsPrompt('Menu item name', 'New Dish');
    if (!name) return;
    const sub = hmsPrompt('Short description', 'Add a short description') || 'Add a short description';
    const price = hmsPrompt('Price', '\u20B11,350') || '\u20B11,350';
    const categoryHint = MENU_CATEGORIES.join(' / ');
    const categoryRaw = hmsPrompt('Category (' + categoryHint + ')', menuTab) || menuTab;
    const category = normalizeMenuCategory(categoryRaw);
    if (window.HMSSiteContent) {
      window.HMSSiteContent.addMenu({
        name: name.trim(),
        sub: sub.trim(),
        price: price.trim(),
        category,
        img: 'https://picsum.photos/seed/menu' + Date.now() + '/800/600.jpg',
      });
    }
    onToast('Menu item added — click the image icon to change the photo');
  };

  const handleEdit = (item) => {
    const name = hmsPrompt('Menu item name', item.name);
    if (name == null || !name.trim()) return;
    const sub = hmsPrompt('Short description', item.sub || '');
    if (sub == null) return;
    const price = hmsPrompt('Price', item.price || '\u20B11,350');
    if (price == null) return;
    const categoryHint = MENU_CATEGORIES.join(' / ');
    const categoryRaw = hmsPrompt('Category (' + categoryHint + ')', normalizeMenuCategory(item.category));
    if (categoryRaw == null) return;
    const category = normalizeMenuCategory(categoryRaw);
    if (window.HMSSiteContent) window.HMSSiteContent.updateMenu(item.id, {
      name: name.trim(), sub: sub.trim(), price: price.trim(), category,
    });
  };

  const handleMenuImage = (item) => {
    pickImageFile((url) => {
      if (!url || !window.HMSSiteContent) return;
      window.HMSSiteContent.updateMenu(item.id, { img: url });
      onToast('Food image updated');
    });
  };

  return (
    <>
      <div className="page-header">
        <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.75rem' }}>Culinary Arts</p>
        <h1 className="font-display">Restaurant Menu</h1>
        <p>Browse our courses — Main Dishes, Appetizers, Soups, Desserts, and Beverages.</p>
        {canEditMenus && (
          <div style={{ marginTop: '1.35rem' }}>
            <button type="button" className="btn-outline" data-hms-no-edit="1" onClick={handleAdd} style={{ fontSize: '0.72rem', padding: '0.5rem 0.9rem' }}>
              + Add menu item
            </button>
          </div>
        )}
      </div>

      <RoomTabBar
        tabs={MENU_TABS}
        active={menuTab}
        onChange={setMenuTab}
        items={menuList}
        getKey={(item) => normalizeMenuCategory(item.category)}
      />

      <section style={{ padding: '0 1.5rem 5rem', maxWidth: 1200, margin: '0 auto' }}>
        {filteredMenus.length === 0 && !canEditMenus ? (
          <p style={{ textAlign: 'center', color: 'var(--fg-muted)', padding: '2rem 1rem' }}>
            No items in {menuTab} yet.
          </p>
        ) : (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: '1.25rem' }}>
            {filteredMenus.map(item => (
              <div key={item.id || item.name} className="menu-food-card" style={{ position: 'relative' }}>
                {canEditMenus && (
                  <div style={{ position: 'absolute', top: 10, right: 10, zIndex: 3, display: 'flex', gap: 6 }} data-hms-no-edit="1">
                    <button type="button" title="Change food image" onClick={() => handleMenuImage(item)} style={toolBtnStyle('image')}><i className="fa-solid fa-image" style={{fontSize:11}}></i></button>
                    <button type="button" title="Edit item" onClick={() => handleEdit(item)} style={toolBtnStyle('edit')}><i className="fa-solid fa-pen" style={{fontSize:10}}></i></button>
                    <button type="button" title="Remove item" onClick={() => window.HMSSiteContent && window.HMSSiteContent.removeMenu(item.id)} style={toolBtnStyle('danger')}><i className="fa-solid fa-xmark" style={{fontSize:12}}></i></button>
                  </div>
                )}
                <div className="menu-food-img">
                  <img
                    src={menuFoodImg(item)}
                    alt={item.name}
                    loading="lazy"
                    onError={(e) => {
                      e.currentTarget.style.display = 'none';
                      const fallback = e.currentTarget.nextElementSibling;
                      if (fallback) fallback.style.display = 'flex';
                    }}
                  />
                  <div className="menu-food-img-fallback" style={{ display: 'none' }}>
                    <i className="fa-solid fa-utensils" style={{ fontSize: '1.6rem', color: 'var(--accent)' }}></i>
                  </div>
                  <div className="menu-food-price">{item.price || 'â€”'}</div>
                </div>
                <div className="menu-food-body">
                  <p style={{ margin: '0 0 0.35rem', color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.12em', textTransform: 'uppercase' }}>
                    {normalizeMenuCategory(item.category)}
                  </p>
                  <h3 className="font-display" style={{ fontSize: '1.15rem', fontWeight: 700, margin: '0 0 0.4rem' }}>{item.name}</h3>
                  <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.8rem', fontWeight: 300, lineHeight: 1.5 }}>{item.sub}</p>
                </div>
              </div>
            ))}
            {canEditMenus && (
              <button
                type="button"
                onClick={handleAdd}
                data-hms-no-edit="1"
                style={{
                  minHeight: 280, borderRadius: 12, border: '2px dashed #f43f5e',
                  background: 'rgba(244,63,94,0.06)', color: '#fb7185', cursor: 'pointer',
                  display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 8,
                  fontFamily: 'Outfit, sans-serif',
                }}
              >
                <span style={{ width: 48, height: 48, borderRadius: 12, border: '1.5px solid #f43f5e', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 26 }}>+</span>
                <span style={{ fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', fontSize: 12 }}>Add to {menuTab}</span>
              </button>
            )}
          </div>
        )}
      </section>
    </>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• EXPERIENCE PAGE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function ExperiencePage({ onNavigate }) {
  const [idx, setIdx] = useState(0);
  const t = TESTIMONIALS[idx];

  return (
    <>
      <div className="page-header">
        <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.75rem' }}>Beyond the Room</p>
        <h1 className="font-display">The SPC Experience</h1>
        <p>Every detail is designed to elevate your stay from memorable to extraordinary.</p>
      </div>
      <section style={{ padding: '0 1.5rem 4rem', maxWidth: 1200, margin: '0 auto' }}>
        <div className="grid-4" style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: '1.25rem', marginBottom: '5rem' }}>
          {EXPERIENCES.map(ex => (
            <div key={ex.title} className="exp-item">
              <i className={`fa-solid ${ex.icon}`} style={{ fontSize: '1.4rem', color: 'var(--accent)', marginBottom: '0.85rem', display: 'block' }}></i>
              <h4 style={{ fontWeight: 600, fontSize: '0.95rem', marginBottom: '0.35rem' }}>{ex.title}</h4>
              <p style={{ fontSize: '0.78rem', color: 'var(--fg-muted)', fontWeight: 300, lineHeight: 1.55 }}>{ex.desc}</p>
            </div>
          ))}
        </div>

        <div className="testimonial-box" style={{ maxWidth: 860, margin: '0 auto 4rem' }}>
          <div className="testimonial-flex" style={{ display: 'flex', alignItems: 'center', gap: '2rem', flexWrap: 'wrap' }}>
            <img src={t.img} alt="Guest" style={{ width: 72, height: 72, borderRadius: '50%', border: '2px solid var(--accent)', objectFit: 'cover', flexShrink: 0 }} />
            <div style={{ flex: 1, minWidth: 220 }}>
              <i className="fa-solid fa-quote-left" style={{ color: 'var(--accent)', opacity: 0.35, fontSize: '1.3rem', marginBottom: '0.6rem', display: 'block' }}></i>
              <p className="font-display" style={{ fontSize: '1.05rem', fontStyle: 'italic', lineHeight: 1.6, marginBottom: '0.75rem' }}>{t.text}</p>
              <div>
                <span style={{ fontWeight: 600, fontSize: '0.85rem' }}>{t.name}</span>
                <span style={{ color: 'var(--fg-muted)', fontSize: '0.75rem', marginLeft: '0.4rem' }}>{t.role}</span>
              </div>
            </div>
            <div className="testimonial-nav" style={{ display: 'flex', gap: '0.4rem', flexShrink: 0 }}>
              <button className="btn-outline" style={{ padding: '0.4rem', width: 36, height: 36, justifyContent: 'center' }} onClick={() => setIdx((idx - 1 + TESTIMONIALS.length) % TESTIMONIALS.length)} aria-label="Previous">
                <i className="fa-solid fa-chevron-left" style={{ fontSize: '0.65rem' }}></i>
              </button>
              <button className="btn-outline" style={{ padding: '0.4rem', width: 36, height: 36, justifyContent: 'center' }} onClick={() => setIdx((idx + 1) % TESTIMONIALS.length)} aria-label="Next">
                <i className="fa-solid fa-chevron-right" style={{ fontSize: '0.65rem' }}></i>
              </button>
            </div>
          </div>
        </div>

        <div style={{ textAlign: 'center' }}>
          <button className="btn-primary" onClick={() => onNavigate('booking')}>
            Book Now <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
          </button>
        </div>
      </section>
    </>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• BOOKING PAGE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function BookingPage({ onToast, rooms }) {
  const roomList = rooms && rooms.length ? rooms : ROOMS;
  const [form, setForm] = useState({ checkIn: '', checkOut: '', guests: '', roomType: '', name: '', email: '' });
  const today = new Date().toISOString().split('T')[0];

  const update = (field, value) => {
    setForm(prev => {
      const next = { ...prev, [field]: value };
      if (field === 'checkIn' && value) {
        next.checkOut = '';
      }
      return next;
    });
  };

  const getEstimate = () => {
    if (!form.checkIn || !form.checkOut || !form.roomType) return null;
    const days = Math.max(1, Math.ceil((new Date(form.checkOut) - new Date(form.checkIn)) / 86400000));
    if (days <= 0) return null;
    const room = roomList.find(r => r.id === form.roomType);
    if (!room) return null;
    return { days, price: room.price, total: days * room.price };
  };

  const estimate = getEstimate();

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!isSiteInteractive()) return;
    const room = roomList.find(r => r.id === form.roomType);
    onToast(`Thank you, ${form.name}! Your booking for the ${room ? room.name : 'room'} has been submitted.`);
    setForm({ checkIn: '', checkOut: '', guests: '', roomType: '', name: '', email: '' });
  };

  const minCheckOut = form.checkIn || today;

  return (
    <>
      <div className="page-header" data-hms-page="booking">
        <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.75rem' }}>Book Now</p>
        <h1 className="font-display">Book Your Stay</h1>
        <p>Select your dates and preferences, and our concierge team will confirm your booking within the hour.</p>
      </div>
      <section style={{ padding: '0 1.5rem 6rem', maxWidth: 880, margin: '0 auto' }}>
        <div className="booking-bar">
          <form onSubmit={handleSubmit}>
            <div className="booking-flex" style={{ display: 'flex', gap: '1rem', marginBottom: '1rem', flexWrap: 'wrap' }}>
              <div style={{ flex: 1, minWidth: 170 }}>
                <label style={{ fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.45rem' }}>Check-in Date</label>
                <input type="date" className="booking-input" value={form.checkIn} min={today} onChange={e => update('checkIn', e.target.value)} required />
              </div>
              <div style={{ flex: 1, minWidth: 170 }}>
                <label style={{ fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.45rem' }}>Check-out Date</label>
                <input type="date" className="booking-input" value={form.checkOut} min={minCheckOut} onChange={e => update('checkOut', e.target.value)} required />
              </div>
              <div style={{ flex: 1, minWidth: 130 }}>
                <label style={{ fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.45rem' }}>Guests</label>
                <select className="booking-input" value={form.guests} onChange={e => update('guests', e.target.value)} required>
                  <option value="">Select</option>
                  {[1,2,3,4].map(n => <option key={n} value={n}>{n} Guest{n > 1 ? 's' : ''}</option>)}
                </select>
              </div>
              <div style={{ flex: 1, minWidth: 130 }}>
                <label style={{ fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.45rem' }}>Room Type</label>
                <select className="booking-input" value={form.roomType} onChange={e => update('roomType', e.target.value)} required>
                  <option value="">Select</option>
                  {roomList.map(r => <option key={r.id} value={r.id}>{r.name} â€” {formatPeso(r.price)}/night</option>)}
                </select>
              </div>
            </div>
            <div style={{ display: 'flex', gap: '1rem', marginBottom: '1.25rem', flexWrap: 'wrap' }}>
              <div style={{ flex: 1, minWidth: 190 }}>
                <label style={{ fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.45rem' }}>Full Name</label>
                <input type="text" className="booking-input" placeholder="e.g. James Whitfield" value={form.name} onChange={e => update('name', e.target.value)} required />
              </div>
              <div style={{ flex: 1, minWidth: 190 }}>
                <label style={{ fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.45rem' }}>Email Address</label>
                <input type="email" className="booking-input" placeholder="james@example.com" value={form.email} onChange={e => update('email', e.target.value)} required />
              </div>
            </div>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '1rem' }}>
              <div style={{ fontSize: '0.85rem', color: 'var(--fg-muted)', fontWeight: 300 }}>
                {estimate ? (
                  <>
                    <i className="fa-solid fa-calculator" style={{ color: 'var(--accent)', marginRight: '0.35rem' }}></i>
                    Estimated total:{' '}
                    <strong style={{ color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontSize: '1.15rem' }}>{formatPeso(estimate.total)}</strong>
                    <span style={{ opacity: 0.55 }}> ({estimate.days} night{estimate.days > 1 ? 's' : ''} x {formatPeso(estimate.price)}/night)</span>
                  </>
                ) : (
                  <>
                    <i className="fa-solid fa-calculator" style={{ color: 'var(--accent)', marginRight: '0.35rem' }}></i>
                    Select dates and room type to see estimated total
                  </>
                )}
              </div>
              <button type="submit" className="btn-primary">
                <i className="fa-solid fa-paper-plane" style={{ fontSize: '0.7rem' }}></i>
                Book Now
              </button>
            </div>
          </form>
        </div>
      </section>
    </>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• FOOTER â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function Footer({ onNavigate }) {
  return (
    <footer data-hms-section="footer" data-hms-bg-target="1" style={{ padding: '3.5rem 1.5rem 1.75rem', borderTop: '1px solid var(--border)' }}>
      <div style={{ maxWidth: 1200, margin: '0 auto' }}>
        <div className="footer-grid" style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr 1fr', gap: '2.5rem', marginBottom: '2.5rem' }}>
          <div>
            <span style={{ fontSize: '1.05rem', fontWeight: 600, letterSpacing: '0.18em', textTransform: 'uppercase', display: 'block', marginBottom: '0.85rem' }}>SPC HOTEL</span>
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', fontWeight: 300, lineHeight: 1.65, maxWidth: 280, marginBottom: '1.25rem' }}>A sanctuary of refined hospitality. Where every guest becomes part of our story.</p>
            <div style={{ display: 'flex', gap: '0.65rem' }}>
              {['fa-instagram', 'fa-facebook-f', 'fa-x-twitter'].map((icon, i) => (
                <a key={icon} href="#" aria-label={icon}
                  style={{ width: 34, height: 34, border: '1px solid var(--border)', borderRadius: '50%', display: 'flex', alignItems: 'center', justifyContent: 'center', transition: 'border-color 0.2s, color 0.2s' }}
                  onMouseEnter={e => { e.currentTarget.style.borderColor = 'var(--accent)'; e.currentTarget.style.color = 'var(--accent)'; }}
                  onMouseLeave={e => { e.currentTarget.style.borderColor = 'var(--border)'; e.currentTarget.style.color = 'var(--fg-muted)'; }}
                >
                  <i className={`fa-brands ${icon}`} style={{ fontSize: '0.8rem' }}></i>
                </a>
              ))}
            </div>
          </div>
          <div>
            <h4 style={{ fontSize: '0.7rem', letterSpacing: '0.15em', textTransform: 'uppercase', color: 'var(--accent)', marginBottom: '1rem' }}>Hotel</h4>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.6rem' }}>
              <a href="javascript:void(0)" onClick={() => onNavigate('rooms')} style={{ fontSize: '0.82rem', fontWeight: 300 }}>Rooms & Suites</a>
              <a href="javascript:void(0)" onClick={() => onNavigate('restaurant')} style={{ fontSize: '0.82rem', fontWeight: 300 }}>Dining</a>
              <a href="javascript:void(0)" onClick={() => onNavigate('experience')} style={{ fontSize: '0.82rem', fontWeight: 300 }}>Spa & Wellness</a>
              <a href="javascript:void(0)" onClick={() => onNavigate('experience')} style={{ fontSize: '0.82rem', fontWeight: 300 }}>Events</a>
            </div>
          </div>
          <div>
            <h4 style={{ fontSize: '0.7rem', letterSpacing: '0.15em', textTransform: 'uppercase', color: 'var(--accent)', marginBottom: '1rem' }}>Services</h4>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.6rem' }}>
              <a href="javascript:void(0)" onClick={() => onNavigate('booking')} style={{ fontSize: '0.82rem', fontWeight: 300 }}>Book Now</a>
              <a href="#" style={{ fontSize: '0.82rem', fontWeight: 300 }}>Concierge</a>
              <a href="#" style={{ fontSize: '0.82rem', fontWeight: 300 }}>Airport Transfer</a>
              <a href="#" style={{ fontSize: '0.82rem', fontWeight: 300 }}>Gift Vouchers</a>
            </div>
          </div>
          <div>
            <h4 style={{ fontSize: '0.7rem', letterSpacing: '0.15em', textTransform: 'uppercase', color: 'var(--accent)', marginBottom: '1rem' }}>Contact</h4>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.6rem' }}>
              <span style={{ fontSize: '0.82rem', fontWeight: 300, color: 'var(--fg-muted)' }}><i className="fa-solid fa-location-dot" style={{ color: 'var(--accent)', width: 14, marginRight: '0.35rem' }}></i>42 Rivoli Blvd, Paris</span>
              <span style={{ fontSize: '0.82rem', fontWeight: 300, color: 'var(--fg-muted)' }}><i className="fa-solid fa-phone" style={{ color: 'var(--accent)', width: 14, marginRight: '0.35rem' }}></i>+33 1 42 60 00 00</span>
              <span style={{ fontSize: '0.82rem', fontWeight: 300, color: 'var(--fg-muted)' }}><i className="fa-solid fa-envelope" style={{ color: 'var(--accent)', width: 14, marginRight: '0.35rem' }}></i>stay@spchotel.com</span>
            </div>
          </div>
        </div>
        <div style={{ borderTop: '1px solid var(--border)', paddingTop: '1.25rem', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '0.75rem' }}>
          <span style={{ fontSize: '0.72rem', color: 'var(--fg-muted)', fontWeight: 300 }}>2024 SPC Hotel. All rights reserved.</span>
          <div style={{ display: 'flex', gap: '1.25rem' }}>
            <a href="#" style={{ fontSize: '0.72rem', fontWeight: 300 }}>Privacy Policy</a>
            <a href="#" style={{ fontSize: '0.72rem', fontWeight: 300 }}>Terms of Service</a>
          </div>
        </div>
      </div>
    </footer>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• APP â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function App() {
  const [page, setPage] = useState('home');
  const [mobileOpen, setMobileOpen] = useState(false);
  const [toast, setToast] = useState({ message: '', visible: false });
  const toastTimer = useRef(null);
  const [navLinks, setNavLinks] = useState(() => (
    window.HMSSiteContent ? window.HMSSiteContent.getNav() : [
      { id: 'nav-home', key: 'home', label: 'Home' },
      { id: 'nav-rooms', key: 'rooms', label: 'Rooms' },
      { id: 'nav-restaurant', key: 'restaurant', label: 'Restaurant' },
      { id: 'nav-experience', key: 'experience', label: 'Experience' },
    ]
  ));
  const [rooms, setRooms] = useState(() => (
    window.HMSSiteContent ? window.HMSSiteContent.getRooms(ROOMS) : ROOMS
  ));
  const [menus, setMenus] = useState(() => (
    window.HMSSiteContent ? window.HMSSiteContent.getMenus() : []
  ));
  const [canEditNav, setCanEditNav] = useState(false);
  const [canEditRooms, setCanEditRooms] = useState(false);
  const [canEditMenus, setCanEditMenus] = useState(false);
  const [canManageRooms, setCanManageRooms] = useState(false);
  const [cardImages, setCardImages] = useState(() => (
    window.HMSSiteContent && window.HMSSiteContent.getCardImages ? window.HMSSiteContent.getCardImages() : {}
  ));

  const syncSiteContent = useCallback(() => {
    if (!window.HMSSiteContent) return;
    // Must sync even in Design mode so Add Room / menu / nav tools update the UI.
    setNavLinks(window.HMSSiteContent.getNav());
    setRooms(window.HMSSiteContent.getRooms(ROOMS));
    setMenus(window.HMSSiteContent.getMenus());
    if (window.HMSSiteContent.getCardImages) setCardImages(window.HMSSiteContent.getCardImages());
    setCanEditNav(window.HMSSiteContent.canEditNav());
    setCanEditRooms(window.HMSSiteContent.canEditRooms());
    setCanEditMenus(window.HMSSiteContent.canEditMenus());
    setCanManageRooms(
      typeof window.HMSSiteContent.canUseRoomManagementUi === 'function'
        ? window.HMSSiteContent.canUseRoomManagementUi()
        : false
    );
  }, []);

  useEffect(() => {
    syncSiteContent();
    const unsub = window.HMSSiteContent ? window.HMSSiteContent.subscribe(syncSiteContent) : null;
    window.addEventListener('hms-site-content-changed', syncSiteContent);
    window.addEventListener('hms-hotel-auth', syncSiteContent);
    const t = setTimeout(syncSiteContent, 700);
    return () => {
      if (unsub) unsub();
      window.removeEventListener('hms-site-content-changed', syncSiteContent);
      window.removeEventListener('hms-hotel-auth', syncSiteContent);
      clearTimeout(t);
    };
  }, [syncSiteContent]);

  const navigateTo = useCallback((target, opts) => {
    if (!(opts && opts.force) && !isSiteInteractive()) return;
    const next = (target === 'login' || target === 'signup') ? 'home' : target;
    setPage(next);
    window.__HMS_CURRENT_PAGE__ = next;
    window.scrollTo({ top: 0 });
    setMobileOpen(false);
    window.dispatchEvent(new CustomEvent('hms-page-change', { detail: { page: next } }));
  }, []);

  useEffect(() => {
    window.__HMS_NAVIGATE__ = (page) => navigateTo(page, { force: true });
    window.__HMS_CURRENT_PAGE__ = page;
    return () => {
      if (window.__HMS_NAVIGATE__) delete window.__HMS_NAVIGATE__;
    };
  }, [navigateTo, page]);

  const showToast = useCallback((msg) => {
    if (toastTimer.current) clearTimeout(toastTimer.current);
    setToast({ message: msg, visible: true });
    toastTimer.current = setTimeout(() => {
      setToast(prev => ({ ...prev, visible: false }));
    }, 3000);
  }, []);

  const editRoom = useCallback((id, patch) => {
    setRooms(prev => {
      const list = (prev && prev.length) ? prev : ROOMS;
      return list.map(r => (r.id === id ? Object.assign({}, r, patch) : r));
    });
    if (window.HMSSiteContent) window.HMSSiteContent.updateRoom(id, patch, ROOMS);
  }, []);

  const addRoom = useCallback((partial) => {
    const id = 'room-' + Math.random().toString(36).slice(2, 9);
    const item = Object.assign({
      id,
      name: 'New Room',
      label: 'Classic',
      category: 'Classic',
      status: 'Available',
      price: 200,
      img: 'https://picsum.photos/seed/room' + Date.now() + '/800/600.jpg',
      desc: 'Add a short description for this room.',
      amenities: [
        { icon: 'fa-bed', text: 'Bed' },
        { icon: 'fa-wifi', text: 'WiFi' },
      ],
    }, partial || {});
    if (!item.id) item.id = id;

    setRooms(prev => {
      const list = (prev && prev.length) ? prev.slice() : ROOMS.map(r => Object.assign({}, r));
      list.push(item);
      if (window.HMSSiteContent && window.HMSSiteContent.canEditRooms()) {
        window.HMSSiteContent.setRooms(list);
      }
      return list;
    });
    return item;
  }, []);

  const removeRoom = useCallback((id) => {
    setRooms(prev => {
      const list = ((prev && prev.length) ? prev : ROOMS).filter(r => r.id !== id);
      if (window.HMSSiteContent && window.HMSSiteContent.canEditRooms()) {
        window.HMSSiteContent.setRooms(list);
      }
      return list;
    });
  }, []);

  const pages = {
    home: (
      <HomePage
        onNavigate={navigateTo}
        onToast={showToast}
        rooms={rooms}
        menus={menus}
        canEditRooms={canEditRooms}
        onAddRoom={addRoom}
        onEditRoom={editRoom}
        onRemoveRoom={removeRoom}
      />
    ),
    rooms: (
      <RoomsPage
        onNavigate={navigateTo}
        onToast={showToast}
        rooms={rooms}
        canEditRooms={canEditRooms}
        canManageRooms={canManageRooms}
        onAddRoom={addRoom}
        onEditRoom={editRoom}
        onRemoveRoom={removeRoom}
      />
    ),
    restaurant: <RestaurantPage onNavigate={navigateTo} onToast={showToast} menus={menus} canEditMenus={canEditMenus} cardImages={cardImages} />,
    experience: <ExperiencePage onNavigate={navigateTo} />,
    booking: <BookingPage onToast={showToast} rooms={rooms} />,
  };

  return (
    <>
      <NavBar
        currentPage={page}
        onNavigate={navigateTo}
        onToggleMobile={() => setMobileOpen(v => !v)}
        mobileOpen={mobileOpen}
        links={navLinks}
        canEditNav={canEditNav}
        onAddNav={(partial) => window.HMSSiteContent && window.HMSSiteContent.addNavLink(partial)}
        onEditNav={(id, patch) => window.HMSSiteContent && window.HMSSiteContent.updateNavLink(id, patch)}
        onRemoveNav={(id) => window.HMSSiteContent && window.HMSSiteContent.removeNavLink(id)}
      />
      <MobileMenu
        open={mobileOpen}
        onClose={() => setMobileOpen(false)}
        onNavigate={navigateTo}
        links={navLinks}
      />
      <main data-hms-page={page}>{pages[page] || pages.home}</main>
      <Footer onNavigate={navigateTo} />
      <Toast message={toast.message} visible={toast.visible} />
    </>
  );
}

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(<App />);
 </script>
@endverbatim

@include('students.template.partials.editor-bridge')
</body>
</html>
