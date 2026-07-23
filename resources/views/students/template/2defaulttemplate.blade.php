<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPC HOTEL</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://unpkg.com/react@18/umd/react.production.min.js" crossorigin></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js" crossorigin></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
<style>
  :root {
    --bg: #f7f4ef;
    --bg-alt: #efe9e0;
    --fg: #1a1a1a;
    --fg-muted: #7a7570;
    --accent: #1b4332;
    --accent-light: #2d6a4f;
    --warm: #c17849;
    --warm-light: #d4956a;
    --card: #ffffff;
    --border: #e2ddd5;
  }
  * { margin: 0; padding: 0; box-sizing: border-box; }
  html { scroll-behavior: auto; }
  body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--fg);
    line-height: 1.6;
    overflow-x: hidden;
  }
  .font-display { font-family: 'Cormorant Garamond', serif; }
  ::-webkit-scrollbar { width: 6px; }
  ::-webkit-scrollbar-track { background: var(--bg); }
  ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

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
  .nav-item {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
  .nav-links-desktop { position: relative; }
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
    border: 1px dashed #e11d48;
    background: rgba(225,29,72,0.08);
    color: #e11d48;
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
  .nav-bar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
    padding: 0 2rem; height: 64px;
    background: var(--card);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
  }
  .nav-link {
    color: var(--fg-muted); font-size: 0.82rem; font-weight: 500;
    letter-spacing: 0.06em; text-transform: uppercase;
    cursor: pointer; background: none; border: none;
    font-family: 'DM Sans', sans-serif; padding: 0;
    transition: color 0.2s; position: relative;
  }
  .nav-link::after {
    content: ''; position: absolute; bottom: -4px; left: 0;
    width: 0; height: 2px; background: var(--accent);
    transition: width 0.25s; border-radius: 1px;
  }
  .nav-link:hover, .nav-link.active { color: var(--accent); }
  .nav-link:hover::after, .nav-link.active::after { width: 100%; }

  .hero-split {
    display: flex;
    flex-direction: row;
    align-items: stretch;
    min-height: 72vh;
    margin-top: 64px;
  }
  .hero-img {
    flex: 0 0 55%;
    position: relative;
    overflow: hidden;
    min-height: 72vh;
    align-self: stretch;
    background: #ddd;
  }
  .hero-img img {
    width: 100%;
    height: 100%;
    min-height: 72vh;
    object-fit: cover;
    display: block;
  }
  .hero-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 4rem 3.5rem;
    background: var(--bg-alt);
    min-width: 0;
  }

  .page-header {
    padding: 7.5rem 1.5rem 2rem;
    text-align: center; max-width: 640px; margin: 0 auto;
  }
  .page-header h1 { font-size: 2.8rem; font-weight: 600; margin-bottom: 0.6rem; line-height: 1.15; }
  .page-header p { color: var(--fg-muted); font-weight: 400; font-size: 0.95rem; }
  .section-num {
    font-family: 'DM Sans', sans-serif; font-size: 0.7rem; font-weight: 600;
    letter-spacing: 0.15em; color: var(--warm); text-transform: uppercase;
    margin-bottom: 0.5rem; display: block;
  }

  /* â”€â”€ Tabs â”€â”€ */
  .tab-bar {
    display: flex; align-items: center; justify-content: center;
    gap: 0.35rem; padding: 0 1.5rem; margin-bottom: 2.5rem;
    flex-wrap: wrap;
  }
  .tab-btn {
    font-family: 'DM Sans', sans-serif; font-size: 0.78rem; font-weight: 600;
    letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.55rem 1.2rem; border-radius: 100px;
    border: 1.5px solid var(--border); background: transparent;
    color: var(--fg-muted); cursor: pointer;
    transition: all 0.2s;
  }
  .tab-btn:hover { border-color: var(--accent); color: var(--accent); }
  .tab-btn.active {
    background: var(--accent); border-color: var(--accent); color: #fff;
  }
  .tab-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 20px; border-radius: 10px;
    font-size: 0.65rem; font-weight: 700;
    margin-left: 0.4rem; padding: 0 0.35rem;
    background: rgba(0,0,0,0.06); color: var(--fg-muted);
    transition: all 0.2s;
  }
  .tab-btn.active .tab-count {
    background: rgba(255,255,255,0.25); color: #fff;
  }

  /* â”€â”€ Room Cards â”€â”€ */
  .room-card {
    background: var(--card); border-radius: 10px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    cursor: pointer; transition: box-shadow 0.25s, transform 0.25s;
  }
  .room-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.08); transform: translateY(-3px); }
  .room-card-img { height: 220px; overflow: hidden; }
  .room-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
  .room-card:hover .room-card-img img { transform: scale(1.04); }
  .room-tag {
    display: inline-block; font-size: 0.65rem; font-weight: 600;
    letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--accent); background: rgba(27,67,50,0.08);
    padding: 0.2rem 0.6rem; border-radius: 3px; margin-bottom: 0.5rem;
  }
  .room-tag-luxe {
    color: var(--warm); background: rgba(193,120,73,0.1);
  }
  .room-amenity {
    display: inline-flex; align-items: center; gap: 0.3rem;
    font-size: 0.72rem; color: var(--fg-muted);
    padding: 0.2rem 0.5rem; background: var(--bg);
    border-radius: 4px;
  }

  /* â”€â”€ Restaurant Cards â”€â”€ */
  .rest-card {
    background: var(--card); border-radius: 10px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    transition: box-shadow 0.25s, transform 0.25s;
  }
  .rest-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.08); transform: translateY(-3px); }
  .rest-card-img { height: 200px; overflow: hidden; }
  .rest-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
  .rest-card:hover .rest-card-img img { transform: scale(1.04); }
  .rest-badge {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.7rem; font-weight: 500; color: #2d7a4f;
    background: rgba(45,122,79,0.1); padding: 0.2rem 0.55rem;
    border-radius: 20px;
  }
  .rest-badge-dot { width: 5px; height: 5px; border-radius: 50%; background: #2d7a4f; }

  .menu-item {
    display: flex; justify-content: space-between; align-items: baseline;
    padding: 0.7rem 0; border-bottom: 1px solid var(--border);
  }
  .menu-item:last-child { border-bottom: none; }

  .exp-card {
    background: var(--card); border-radius: 10px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    transition: box-shadow 0.25s, transform 0.25s;
  }
  .exp-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.08); transform: translateY(-3px); }
  .exp-card-img { height: 140px; overflow: hidden; }
  .exp-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
  .exp-card:hover .exp-card-img img { transform: scale(1.05); }

  .testimonial-box {
    background: var(--accent); border-radius: 12px; padding: 3rem;
    color: var(--bg); position: relative; overflow: hidden;
  }
  .testimonial-box::before {
    content: '\201C'; position: absolute; top: -20px; left: 20px;
    font-size: 12rem; font-family: 'Cormorant Garamond', serif;
    color: rgba(255,255,255,0.06); line-height: 1;
  }

  .booking-card {
    background: var(--card); border-radius: 12px; overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
  }
  .booking-sidebar {
    background: var(--accent); padding: 2.5rem; color: var(--bg);
    display: flex; flex-direction: column; justify-content: center;
  }
  .booking-input {
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: 'DM Sans', sans-serif; font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus { border-color: var(--accent); }
  .booking-input::placeholder { color: var(--fg-muted); opacity: 0.6; }

  .btn-primary {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: var(--accent); color: #fff;
    font-family: 'DM Sans', sans-serif; font-weight: 600;
    font-size: 0.82rem; letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.8rem 1.8rem; border: none; border-radius: 6px;
    cursor: pointer; transition: background 0.2s, transform 0.2s;
  }
  .btn-primary:hover { background: var(--accent-light); transform: translateY(-1px); }
  .btn-warm {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: var(--warm); color: #fff;
    font-family: 'DM Sans', sans-serif; font-weight: 600;
    font-size: 0.82rem; letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.8rem 1.8rem; border: none; border-radius: 6px;
    cursor: pointer; transition: background 0.2s, transform 0.2s;
  }
  .btn-warm:hover { background: var(--warm-light); transform: translateY(-1px); }
  .btn-ghost {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: transparent; color: var(--accent);
    font-family: 'DM Sans', sans-serif; font-weight: 600;
    font-size: 0.82rem; letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.75rem 1.5rem; border: 2px solid var(--accent); border-radius: 6px;
    cursor: pointer; transition: all 0.2s;
  }
  .btn-ghost:hover { background: var(--accent); color: #fff; transform: translateY(-1px); }
  .btn-sm {
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: var(--accent); color: #fff;
    font-family: 'DM Sans', sans-serif; font-weight: 600;
    font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.5rem 1rem; border: none; border-radius: 5px;
    cursor: pointer; transition: background 0.2s;
  }
  .btn-sm:hover { background: var(--accent-light); }

  .toast-el {
    position: fixed; bottom: 1.5rem; right: 1.5rem;
    background: var(--card); border-left: 3px solid var(--accent);
    border-radius: 0 8px 8px 0; padding: 0.9rem 1.3rem; color: var(--fg);
    font-size: 0.85rem; z-index: 9999; max-width: 360px;
    display: flex; align-items: center; gap: 0.65rem;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    opacity: 0; transform: translateX(20px);
    transition: opacity 0.3s, transform 0.3s; pointer-events: none;
  }
  .toast-el.show { opacity: 1; transform: translateX(0); pointer-events: auto; }

  .mobile-menu {
    position: fixed; inset: 0; background: var(--card);
    z-index: 999; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 1.5rem;
    opacity: 0; pointer-events: none; transition: opacity 0.25s;
  }
  .mobile-menu.open { opacity: 1; pointer-events: all; }
  .mobile-menu button {
    font-family: 'Cormorant Garamond', serif; font-size: 2rem; font-weight: 600;
    color: var(--fg); background: none; border: none; cursor: pointer;
    transition: color 0.2s;
  }
  .mobile-menu button:hover { color: var(--accent); }

  .site-footer {
    background: var(--accent); color: rgba(247,244,239,0.8);
    padding: 4rem 1.5rem 2rem;
  }
  .site-footer a { color: rgba(247,244,239,0.65); text-decoration: none; transition: color 0.2s; }
  .site-footer a:hover { color: #fff; }
  .footer-heading {
    font-size: 0.7rem; font-weight: 600; letter-spacing: 0.15em;
    text-transform: uppercase; color: rgba(247,244,239,0.4); margin-bottom: 1rem;
  }

  .hamburger {
    display: none; flex-direction: column; gap: 4px; cursor: pointer;
    z-index: 1001; background: none; border: none; padding: 4px;
  }
  .hamburger span { display: block; width: 20px; height: 2px; background: var(--fg); transition: all 0.2s; border-radius: 1px; }
  .hamburger.active span:nth-child(1) { transform: rotate(45deg) translate(4px, 4px); }
  .hamburger.active span:nth-child(2) { opacity: 0; }
  .hamburger.active span:nth-child(3) { transform: rotate(-45deg) translate(4px, -4px); }

  .divider {
    display: flex; align-items: center; justify-content: center;
    gap: 1rem; margin: 0 auto; max-width: 120px; padding: 0.5rem 0;
  }
  .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
  .divider i { color: var(--warm); font-size: 0.55rem; }

  .hl-card {
    background: var(--card); border-radius: 10px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    cursor: pointer; transition: box-shadow 0.25s, transform 0.25s;
    border-top: 3px solid var(--accent);
  }
  .hl-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.08); transform: translateY(-3px); }

  .empty-state {
    text-align: center; padding: 4rem 1.5rem; color: var(--fg-muted);
  }
  .empty-state i { font-size: 2.5rem; opacity: 0.25; margin-bottom: 1rem; display: block; }

  /* Desktop layout stays side-by-side in the builder iframe (often < 900px wide). */
  html.hms-in-builder .hero-split {
    flex-direction: row !important;
  }
  html.hms-in-builder .hero-img {
    flex: 0 0 55% !important;
    height: auto !important;
    min-height: 72vh !important;
  }
  html.hms-in-builder .hero-img img {
    min-height: 72vh !important;
  }
  html.hms-in-builder .hero-content {
    padding: 3rem 2.5rem !important;
  }

  @media (max-width: 768px) {
    .hero-split { flex-direction: column; }
    .hero-img { flex: none; height: 45vh; min-height: 300px; }
    .hero-img img { min-height: 300px; }
    .hero-content { padding: 3rem 2rem; }
    .booking-layout { flex-direction: column !important; }
    .booking-sidebar { padding: 2rem; }
    .hamburger { display: flex; }
    .nav-links-desktop { display: none !important; }
    .grid-2 { grid-template-columns: 1fr !important; }
    .grid-3 { grid-template-columns: 1fr !important; }
    .grid-4 { grid-template-columns: 1fr !important; }
    .page-header { padding: 6.5rem 1.5rem 2rem; }
    .page-header h1 { font-size: 2.2rem; }
    .footer-grid { grid-template-columns: 1fr 1fr !important; }
    .testi-flex { flex-direction: column !important; text-align: center; }
    .testi-nav { justify-content: center !important; }
    .tab-bar { gap: 0.25rem; }
    .tab-btn { font-size: 0.72rem; padding: 0.45rem 0.9rem; }
  }
  /* Outside the builder, allow stacking a bit earlier on tablets. */
  @media (max-width: 900px) {
    html:not(.hms-in-builder) .hero-split { flex-direction: column; }
    html:not(.hms-in-builder) .hero-img { flex: none; height: 45vh; min-height: 300px; }
    html:not(.hms-in-builder) .hero-img img { min-height: 300px; }
    html:not(.hms-in-builder) .hero-content { padding: 3rem 2rem; }
    html:not(.hms-in-builder) .booking-layout { flex-direction: column !important; }
    html:not(.hms-in-builder) .booking-sidebar { padding: 2rem; }
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
const { useState, useCallback, useRef, useMemo, useEffect } = React;

/* â•â•â•â•â•â•â•â•â•â•â• DATA â•â•â•â•â•â•â•â•â•â•â• */
const ROOMS = [
  { id: 'presidential', category: 'Luxe', name: 'The Presidential Suite', price: 890,
    img: 'https://picsum.photos/seed/hotelroom3/800/600.jpg',
    desc: '120m\u00B2 of uncompromising luxury with a private terrace, dining room, butler service, and grand piano.',
    amenities: [{ i: 'fa-bed', t: 'Emperor Bed' },{ i: 'fa-umbrella-beach', t: 'Terrace' },{ i: 'fa-bell-concierge', t: 'Butler' },{ i: 'fa-music', t: 'Piano' }]
  },
  { id: 'penthouse', category: 'Luxe', name: 'Penthouse Loft', price: 750,
    img: 'https://picsum.photos/seed/penthouse1/800/600.jpg',
    desc: 'A two-story loft penthouse with double-height ceilings, a private rooftop jacuzzi, and panoramic city views.',
    amenities: [{ i: 'fa-bed', t: 'King Bed' },{ i: 'fa-hot-tub-person', t: 'Jacuzzi' },{ i: 'fa-city', t: '360\u00B0 View' },{ i: 'fa-stairs', t: 'Two-Story' }]
  },
  { id: 'premium', category: 'Suites', name: 'Premium Suite', price: 450,
    img: 'https://picsum.photos/seed/hotelroom2/800/600.jpg',
    desc: '68m\u00B2 suite with separate living area, walk-in closet, soaking tub, and panoramic floor-to-ceiling windows.',
    amenities: [{ i: 'fa-bed', t: 'King Bed' },{ i: 'fa-couch', t: 'Living Area' },{ i: 'fa-bath', t: 'Soaking Tub' },{ i: 'fa-city', t: 'City View' }]
  },
  { id: 'junior', category: 'Suites', name: 'Junior Suite', price: 360,
    img: 'https://picsum.photos/seed/juniorsuite/800/600.jpg',
    desc: '55m\u00B2 suite with a cozy sitting area, premium bedding, and a marble bathroom with dual vanities.',
    amenities: [{ i: 'fa-bed', t: 'King Bed' },{ i: 'fa-couch', t: 'Sitting Area' },{ i: 'fa-bath', t: 'Marble Bath' },{ i: 'fa-wifi', t: 'WiFi' }]
  },
  { id: 'deluxe', category: 'Classic', name: 'Deluxe King Room', price: 280,
    img: 'https://picsum.photos/seed/hotelroom1/800/600.jpg',
    desc: 'Spacious 42m\u00B2 room with king bed, city views, and a marble-appointed bathroom with rain shower.',
    amenities: [{ i: 'fa-bed', t: 'King Bed' },{ i: 'fa-wifi', t: 'WiFi' },{ i: 'fa-bath', t: 'Rain Shower' },{ i: 'fa-mug-saucer', t: 'Minibar' }]
  },
  { id: 'superior', category: 'Classic', name: 'Superior Twin Room', price: 240,
    img: 'https://picsum.photos/seed/twinroom/800/600.jpg',
    desc: '38m\u00B2 room with two single beds, a work desk, and views of the courtyard garden.',
    amenities: [{ i: 'fa-bed', t: 'Twin Beds' },{ i: 'fa-laptop', t: 'Work Desk' },{ i: 'fa-tree', t: 'Garden View' },{ i: 'fa-wifi', t: 'WiFi' }]
  }
];

const ROOM_TABS = ['All', 'Luxe', 'Suites', 'Classic'];

const RESTAURANTS = [
  { name: 'Lumiere', category: 'Fine Dining', img: 'https://picsum.photos/seed/finedining/800/500.jpg',
    desc: 'Contemporary French fine dining with a 12-course tasting menu. Michelin-starred excellence.',
    hours: '6:00 PM \u2014 11:00 PM', menu: 'lumiere' },
  { name: 'Sakura', category: 'Japanese', img: 'https://picsum.photos/seed/sushibar/800/500.jpg',
    desc: 'Omakase sushi bar with imported Japanese ingredients. Intimate 12-seat counter experience.',
    hours: '12:00 PM \u2014 10:00 PM', menu: 'japanese' },
  { name: 'The Gilded Bar', category: 'Bar & Lounge', img: 'https://picsum.photos/seed/cocktailbar/800/500.jpg',
    desc: 'Artisan cocktails and live jazz in a 1920s-inspired setting. The perfect nightcap destination.',
    hours: '5:00 PM \u2014 1:00 AM', menu: 'bar' },
  { name: 'Veranda', category: 'Fine Dining', img: 'https://picsum.photos/seed/verandarest/800/500.jpg',
    desc: 'Mediterranean-inspired cuisine served on our open-air veranda with views of the courtyard fountain.',
    hours: '7:00 AM \u2014 11:00 PM', menu: null },
  { name: 'Tatami Room', category: 'Japanese', img: 'https://picsum.photos/seed/tatamiroom/800/500.jpg',
    desc: 'Private traditional Japanese dining room for up to 8 guests, featuring seasonal kaiseki cuisine.',
    hours: '6:00 PM \u2014 10:00 PM', menu: null },
  { name: 'The Library Bar', category: 'Bar & Lounge', img: 'https://picsum.photos/seed/librarybar/800/500.jpg',
    desc: 'An intimate, book-lined bar specializing in rare whiskies, cognacs, and hand-rolled cigars.',
    hours: '4:00 PM \u2014 12:00 AM', menu: null }
];

const REST_TABS = ['All', 'Fine Dining', 'Japanese', 'Bar & Lounge'];

const EXPERIENCES = [
  { icon: 'fa-spa', title: 'Spa & Wellness', desc: 'Full-service spa with thermal pools, Hammam, and bespoke treatment rituals.', img: 'https://picsum.photos/seed/spahotel/600/400.jpg' },
  { icon: 'fa-person-swimming', title: 'Infinity Pool', desc: 'Rooftop heated pool with skyline views, private cabanas, and poolside service.', img: 'https://picsum.photos/seed/poolhotel/600/400.jpg' },
  { icon: 'fa-dumbbell', title: 'Fitness Center', desc: 'State-of-the-art equipment, personal trainers, and sunrise yoga sessions.', img: 'https://picsum.photos/seed/gymspa/600/400.jpg' },
  { icon: 'fa-car', title: 'Concierge & Transport', desc: 'Private chauffeur, airport transfers, and curated city experiences on demand.', img: 'https://picsum.photos/seed/luxurycar/600/400.jpg' }
];

const TESTIMONIALS = [
  { text: 'SPC Hotel redefines what luxury hospitality means. From the moment we arrived, every interaction felt personal and every detail was impeccable.', name: 'Catherine Morel', role: 'Travel Editor, Conde Nast', img: 'https://picsum.photos/seed/guest1/100/100.jpg' },
  { text: 'I have stayed at hundreds of hotels worldwide, and SPC Hotel stands apart. The Presidential Suite is a masterpiece of design.', name: 'Alexander Reinhardt', role: 'CEO, Meridian Group', img: 'https://picsum.photos/seed/guest2/100/100.jpg' },
  { text: 'Dinner at Lumiere was one of the most extraordinary culinary experiences of my life. The tasting menu was poetry on a plate.', name: 'Isabelle Fontaine', role: 'Michelin Guide Inspector', img: 'https://picsum.photos/seed/guest3/100/100.jpg' },
  { text: 'We chose SPC Hotel for our anniversary and it exceeded every expectation. The spa, the rooftop pool, the Gilded Bar \u2014 pure magic.', name: 'David & Sarah Chen', role: 'Returning Guests', img: 'https://picsum.photos/seed/guest4/100/100.jpg' }
];

const LUMIERE_MENU = [
  { name: 'Hokkaido Scallop Tartare', sub: 'yuzu, sea urchin, micro herbs' },
  { name: 'Wagyu A5 Carpaccio', sub: 'truffle jus, parmesan crisp, rocket' },
  { name: 'Pan-Seared Dover Sole', sub: 'brown butter, capers, lemon beurre blanc' },
  { name: 'Roasted Rhubarb Souffle', sub: 'vanilla bean creme anglaise, pistachio' }
];
const BAR_MENU = [
  { name: 'The SPC Old Fashioned', sub: '25yr bourbon, demerara, aromatic bitters', price: '$26' },
  { name: 'Gold Leaf Negroni', sub: 'gin, Campari, sweet vermouth, 24k gold leaf', price: '$28' },
  { name: 'Garden of Babylon', sub: 'gin, elderflower, cucumber, lime, tonic mist', price: '$22' },
  { name: 'Smoked Espresso Martini', sub: 'vodka, cold brew, kahlua, applewood smoke', price: '$24' }
];

const HIGHLIGHTS = [
  { icon: 'fa-bed', title: 'Rooms & Suites', desc: 'Six room categories from Classic to Luxe, each meticulously designed.', page: 'rooms' },
  { icon: 'fa-utensils', title: 'Dining', desc: 'Six venues spanning French, Japanese, and cocktail experiences.', page: 'restaurant' },
  { icon: 'fa-leaf', title: 'Experience', desc: 'Spa, rooftop pool, fitness center, and personalized concierge services.', page: 'experience' }
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
  const base = { width: 28, height: 28, borderRadius: 8, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', border: '1px solid var(--border)', background: '#fff' };
  if (kind === 'danger') return Object.assign({}, base, { background: '#fff1f2', color: '#e11d48', borderColor: '#fecaca' });
  if (kind === 'image') return Object.assign({}, base, { color: '#0284c7' });
  return Object.assign({}, base, { color: 'var(--accent)' });
}


/* â•â•â•â•â•â•â•â•â•â•â• REUSABLE TAB BAR â•â•â•â•â•â•â•â•â•â•â• */
function TabBar({ tabs, active, onChange, items }) {
  const counts = useMemo(() => {
    const map = { All: items.length };
    tabs.forEach(t => { if (t !== 'All') map[t] = items.filter(it => it.category === t).length; });
    return map;
  }, [tabs, items]);

  return (
    <div className="tab-bar" role="tablist">
      {tabs.map(tab => (
        <button
          key={tab}
          className={`tab-btn${active === tab ? ' active' : ''}`}
          onClick={() => onChange(tab)}
          role="tab"
          aria-selected={active === tab}
        >
          {tab}
          <span className="tab-count">{counts[tab] || 0}</span>
        </button>
      ))}
    </div>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â• SHARED COMPONENTS â•â•â•â•â•â•â•â•â•â•â• */
function Toast({ message, visible }) {
  return (
    <div className={`toast-el${visible ? ' show' : ''}`}>
      <i className="fa-solid fa-circle-check" style={{ color: 'var(--accent)', fontSize: '1.1rem' }}></i>
      <span>{message}</span>
    </div>
  );
}

function MobileMenu({ open, onClose, onNav, links }) {
  const items = [
    ...(links || []),
    { key: 'booking', label: 'Book Now' },
  ];
  return (
    <div className={`mobile-menu${open ? ' open' : ''}`}>
      {items.map(i => <button key={i.id || i.key} onClick={() => { onNav(i.key); onClose(); }}>{i.label}</button>)}
    </div>
  );
}

function NavBar({ currentPage, onNav, onToggle, mobileOpen, links, canEditNav, onAddNav, onEditNav, onRemoveNav }) {
  const PAGE_OPTIONS = ['home', 'rooms', 'restaurant', 'experience', 'booking'];
  const handleAdd = (e) => {
    e.preventDefault();
    e.stopPropagation();
    const label = hmsPrompt('New navigation label', 'New Page');
    if (!label) return;
    const key = hmsPrompt('Link to page (' + PAGE_OPTIONS.join(', ') + ')', 'home');
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
      <div style={{ maxWidth: 1200, margin: '0 auto', width: '100%', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '1rem' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.65rem' }}>
          <button onClick={() => onNav('home')} style={{ background: 'none', border: 'none', cursor: 'pointer', padding: 0 }}>
            <span style={{ color: 'var(--accent)', fontSize: '1.05rem', fontWeight: 700, letterSpacing: '0.18em', textTransform: 'uppercase' }}>SPC HOTEL</span>
          </button>
        </div>
        <div className="nav-links-desktop" style={{ display: 'flex', alignItems: 'center', gap: '1rem', flexWrap: 'wrap', justifyContent: 'flex-end' }}>
          {canEditNav && (
            <button type="button" className="nav-add-btn" title="Add navigation link" onClick={handleAdd} data-hms-no-edit="1">+</button>
          )}
          {(links || []).map(l => (
            <div key={l.id || l.key} className="nav-item">
              <button className={`nav-link${currentPage === l.key ? ' active' : ''}`} onClick={() => onNav(l.key)}>{l.label}</button>
              {canEditNav && (
                <span className="nav-edit-tools" data-hms-no-edit="1">
                  <button type="button" title="Edit link" onClick={(e) => handleEdit(e, l)} style={{ border: 'none', background: 'transparent', color: 'var(--accent)', cursor: 'pointer', fontSize: 11, padding: '0 2px', lineHeight: 1 }}><i className="fa-solid fa-pen" style={{fontSize:10}}></i></button>
                  <button type="button" title="Remove link" onClick={(e) => handleRemove(e, l.id)} style={{ border: 'none', background: 'transparent', color: '#e11d48', cursor: 'pointer', fontSize: 12, fontWeight: 700, padding: '0 2px', lineHeight: 1 }}><i className="fa-solid fa-xmark" style={{fontSize:12}}></i></button>
                </span>
              )}
            </div>
          ))}
          <button className="btn-primary" onClick={() => onNav('booking')} style={{ fontSize: '0.72rem', padding: '0.5rem 1.2rem' }}>
            <i className="fa-regular fa-calendar" style={{ fontSize: '0.7rem' }}></i> Book Now
          </button>
        </div>
        <button className={`hamburger${mobileOpen ? ' active' : ''}`} onClick={onToggle} aria-label="Toggle menu" data-hms-no-edit="1">
          <span></span><span></span><span></span>
        </button>
      </div>
    </nav>
  );
}

function Divider() {
  return <div className="divider"><i className="fa-solid fa-diamond"></i></div>;
}

function EmptyState({ text }) {
  return (
    <div className="empty-state">
      <i className="fa-regular fa-folder-open"></i>
      <p>{text}</p>
    </div>
  );
}


/* â•â•â•â•â•â•â• HOME â•â•â•â•â•â•â• */
function HomePage({ onNav, onToast, rooms, menus, canEditRooms, onAddRoom, onEditRoom, onRemoveRoom }) {
  const roomList = (rooms && rooms.length) ? rooms : ROOMS;
  const menuList = (menus && menus.length) ? menus : (window.HMSSiteContent ? window.HMSSiteContent.DEFAULT_MENUS : []);

  const handleAddRoom = (e) => {
    if (e && e.stopPropagation) e.stopPropagation();
    if (onAddRoom) {
      onAddRoom({
        name: 'New Suite',
        category: 'Classic',
        label: 'Classic',
        price: 250,
        desc: 'Add a short description for this room.',
        img: 'https://picsum.photos/seed/room' + Date.now() + '/800/600.jpg',
        amenities: [{ i: 'fa-bed', t: 'Bed' }, { i: 'fa-wifi', t: 'WiFi' }],
      });
    }
    if (onToast) onToast('Room card added — click the pencil to edit');
  };

  const handleEditRoom = (room) => {
    const name = hmsPrompt('Room name', room.name);
    if (name == null || !name.trim()) return;
    const priceRaw = hmsPrompt('Price per night', String(room.price || 200));
    const price = Math.max(1, parseInt(priceRaw || String(room.price || 200), 10) || room.price || 200);
    const category = hmsPrompt('Category', room.category || room.label || 'Classic');
    if (category == null) return;
    const desc = hmsPrompt('Description', room.desc || '');
    if (desc == null) return;
    if (onEditRoom) onEditRoom(room.id, { name: name.trim(), price, category: category.trim(), label: category.trim(), desc: desc.trim() });
  };

  return (
    <>
      <div className="hero-split" data-hms-section="hero">
        <div className="hero-img" data-hms-bg-target="1">
          <img src="https://picsum.photos/seed/resortlux/1200/900.jpg" alt="SPC Hotel" />
        </div>
        <div className="hero-content">
          <span className="section-num">Est. 1923</span>
          <h1 className="font-display" data-hms-move-root="1" style={{ fontSize: '3.2rem', fontWeight: 600, lineHeight: 1.1, marginBottom: '1.25rem' }}>
            <span style={{ display: 'block' }}>A Sanctuary of</span>
            <em style={{ display: 'block', color: 'var(--warm)' }}>Timeless Luxury</em>
          </h1>
          <p style={{ color: 'var(--fg-muted)', fontSize: '0.95rem', fontWeight: 400, lineHeight: 1.7, marginBottom: '2rem', maxWidth: 400 }}>
            Nestled in the heart of the city, SPC Hotel offers an unparalleled experience of refined hospitality, curated dining, and timeless sophistication.
          </p>
          <div style={{ display: 'flex', gap: '0.75rem', flexWrap: 'wrap' }}>
            <button className="btn-primary" onClick={() => onNav('rooms')}>
              Explore Rooms <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
            </button>
            <button className="btn-ghost" onClick={() => onNav('booking')}>Book Now</button>
          </div>
        </div>
      </div>

      <section data-hms-section="rooms" data-hms-bg-target="1" style={{ padding: '5rem 1.5rem 3rem', maxWidth: 1100, margin: '0 auto' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'end', gap: '1rem', marginBottom: '1.75rem', flexWrap: 'wrap' }}>
          <div>
            <span className="section-num">Available Rooms</span>
            <h2 className="font-display" style={{ fontSize: '2rem', margin: '0.35rem 0 0' }}>Rooms & Suites</h2>
          </div>
          <button className="btn-ghost" onClick={() => onNav('rooms')} style={{ fontSize: '0.72rem' }}>View all</button>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(250px, 1fr))', gap: '1.15rem' }}>
          {roomList.map(room => (
            <div key={room.id} className="room-card" style={{ cursor: 'pointer', position: 'relative' }} onClick={() => onNav('rooms')}>
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
              <div className="room-card-img">
                <img src={room.img} alt={room.name} loading="lazy" />
              </div>
              <div style={{ padding: '1rem 1.1rem 1.2rem' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', gap: 8 }}>
                  <h3 style={{ fontWeight: 600, fontSize: '1rem', margin: 0 }}>{room.name}</h3>
                  <span style={{ color: 'var(--accent)', fontWeight: 700 }}>${room.price}</span>
                </div>
                <p style={{ color: 'var(--fg-muted)', fontSize: '0.78rem', margin: '0.45rem 0 0', lineHeight: 1.5 }}>
                  {(room.desc || '').slice(0, 80)}{(room.desc || '').length > 80 ? 'â€¦' : ''}
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
                minHeight: 260, borderRadius: 14, border: '2px dashed #e11d48',
                background: 'rgba(225,29,72,0.05)', color: '#e11d48', cursor: 'pointer',
                display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 10,
              }}
            >
              <span style={{ width: 52, height: 52, borderRadius: 14, border: '1.5px solid #e11d48', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 28 }}>+</span>
              <span style={{ fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', fontSize: 12 }}>Add Room Card</span>
              <span style={{ fontSize: 11, opacity: 0.75 }}>Cards auto-organize in the grid</span>
            </button>
          )}
        </div>
      </section>

      <section data-hms-section="dining" data-hms-bg-target="1" style={{ padding: '2.5rem 1.5rem 5rem', maxWidth: 1100, margin: '0 auto' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'end', gap: '1rem', marginBottom: '1.75rem', flexWrap: 'wrap' }}>
          <div>
            <span className="section-num">Restaurant Menu</span>
            <h2 className="font-display" style={{ fontSize: '2rem', margin: '0.35rem 0 0' }}>From Our Kitchen & Bar</h2>
          </div>
          <button className="btn-ghost" onClick={() => onNav('restaurant')} style={{ fontSize: '0.72rem' }}>View dining</button>
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '0.85rem' }}>
          {menuList.map(item => (
            <div key={item.id || item.name} className="menu-item" style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
              <div>
                <span style={{ fontWeight: 600, fontSize: '0.9rem' }}>{item.name}</span>
                <p style={{ fontSize: '0.72rem', color: 'var(--fg-muted)', margin: '0.3rem 0 0' }}>{item.sub}</p>
                {item.category ? <p style={{ fontSize: '0.65rem', color: 'var(--warm)', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', margin: '0.4rem 0 0' }}>{item.category}</p> : null}
              </div>
              <span style={{ color: 'var(--accent)', fontWeight: 600, whiteSpace: 'nowrap' }}>{item.price || 'â€”'}</span>
            </div>
          ))}
        </div>
      </section>
    </>
  );
}


/* â•â•â•â•â•â•â• ROOMS â•â•â•â•â•â•â• */
function RoomCard({ room, onToast, canEdit, onEdit, onRemove, onChangeImage }) {
  const isLuxe = room.category === 'Luxe' || room.label === 'Luxe';
  const amenities = room.amenities || [];
  return (
    <div className="room-card" style={{ position: 'relative' }} onClick={() => onToast(`${room.name} selected â€” go to Book Now to confirm.`)}>
      {canEdit && (
        <div style={{ position: 'absolute', top: 10, right: 10, zIndex: 3, display: 'flex', gap: 6 }} data-hms-no-edit="1" onClick={e => e.stopPropagation()}>
          <button type="button" title="Change image" onClick={() => onChangeImage && onChangeImage(room)} style={toolBtnStyle('image')}><i className="fa-solid fa-image" style={{fontSize:11}}></i></button>
          <button type="button" title="Edit room" onClick={() => onEdit(room)} style={toolBtnStyle('edit')}><i className="fa-solid fa-pen" style={{fontSize:10}}></i></button>
          <button type="button" title="Remove room" onClick={() => onRemove(room.id)} style={toolBtnStyle('danger')}><i className="fa-solid fa-xmark" style={{fontSize:12}}></i></button>
        </div>
      )}
      <div className="room-card-img">
        <img src={room.img} alt={room.name} loading="lazy" />
      </div>
      <div style={{ padding: '1.25rem' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.5rem' }}>
          <span className={`room-tag${isLuxe ? ' room-tag-luxe' : ''}`}>{room.category || room.label || 'Room'}</span>
        </div>
        <h3 className="font-display" style={{ fontSize: '1.15rem', fontWeight: 600, marginBottom: '0.3rem' }}>{room.name}</h3>
        <p style={{ color: 'var(--fg-muted)', fontSize: '0.8rem', fontWeight: 400, marginBottom: '0.85rem', lineHeight: 1.5 }}>{room.desc}</p>
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.3rem', marginBottom: '0.85rem' }}>
          {amenities.map(a => (
            <span key={a.t || a.text} className="room-amenity">
              <i className={`fa-solid ${a.i || a.icon}`} style={{ fontSize: '0.6rem', color: isLuxe ? 'var(--warm)' : 'var(--accent)' }}></i> {a.t || a.text}
            </span>
          ))}
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <span style={{ fontSize: '0.82rem', color: 'var(--fg-muted)' }}>
            From <strong className="font-display" style={{ color: isLuxe ? 'var(--warm)' : 'var(--accent)', fontSize: '1.2rem', fontWeight: 700 }}>${room.price}</strong><span style={{ fontSize: '0.72rem' }}>/night</span>
          </span>
          <button className="btn-sm" onClick={e => { e.stopPropagation(); onToast(`${room.name} selected â€” go to Book Now to confirm.`); }}>Select</button>
        </div>
      </div>
    </div>
  );
}

function RoomsPage({ onNav, onToast, rooms, canEditRooms, onAddRoom, onEditRoom, onRemoveRoom }) {
  const list = rooms && rooms.length ? rooms : ROOMS;
  const [tab, setTab] = useState('All');
  const categories = ['All', ...Array.from(new Set(list.map(r => r.category || r.label || 'Classic')))];
  const filtered = tab === 'All' ? list : list.filter(r => (r.category || r.label) === tab);

  const handleAdd = (e) => {
    if (e && e.stopPropagation) e.stopPropagation();
    onAddRoom({
      name: 'New Suite',
      category: 'Classic',
      label: 'Classic',
      price: 250,
      desc: 'Add a short description for this room.',
      img: 'https://picsum.photos/seed/room' + Date.now() + '/800/600.jpg',
      amenities: [{ i: 'fa-bed', t: 'Bed' }, { i: 'fa-wifi', t: 'WiFi' }],
    });
    onToast('Room card added — click the pencil to edit');
  };

  const handleEdit = (room) => {
    const name = hmsPrompt('Room name', room.name);
    if (name == null || !name.trim()) return;
    const priceRaw = hmsPrompt('Price per night', String(room.price || 200));
    const price = Math.max(1, parseInt(priceRaw || String(room.price || 200), 10) || room.price || 200);
    const category = hmsPrompt('Category', room.category || room.label || 'Classic');
    if (category == null) return;
    const desc = hmsPrompt('Description', room.desc || '');
    if (desc == null) return;
    onEditRoom(room.id, { name: name.trim(), price, category: category.trim(), label: category.trim(), desc: desc.trim() });
  };

  return (
    <>
      <div className="page-header">
        <span className="section-num">01 â€” Accommodations</span>
        <h1 className="font-display">Our Rooms & Suites</h1>
        <p>Each room is a sanctuary of design, blending modern luxury with artisanal craftsmanship and sweeping views.</p>
      </div>
      <TabBar tabs={categories} active={tab} onChange={setTab} items={list} />
      <section style={{ padding: '0 1.5rem 2rem', maxWidth: 1100, margin: '0 auto' }}>
        {filtered.length === 0 && !canEditRooms ? (
          <EmptyState text="No rooms found in this category." />
        ) : (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '1.25rem' }}>
            {filtered.map(r => (
              <RoomCard key={r.id} room={r} onToast={onToast} canEdit={canEditRooms}
                onEdit={handleEdit} onRemove={onRemoveRoom}
                onChangeImage={(room) => pickImageFile((url) => {
                  if (!url) return;
                  onEditRoom(room.id, { img: url });
                  onToast('Room image updated');
                })} />
            ))}
            {canEditRooms && (
              <button type="button" onClick={handleAdd} onMouseDown={(e) => e.stopPropagation()} title="Add room card" data-hms-no-edit="1" data-hms-action="add-room"
                style={{
                  minHeight: 300, borderRadius: 14, border: '2px dashed #e11d48',
                  background: 'rgba(225,29,72,0.05)', color: '#e11d48', cursor: 'pointer',
                  display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 10,
                }}>
                <span style={{ width: 52, height: 52, borderRadius: 14, border: '1.5px solid #e11d48', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 28 }}>+</span>
                <span style={{ fontWeight: 700, letterSpacing: '0.08em', textTransform: 'uppercase', fontSize: 12 }}>Add Room Card</span>
                <span style={{ fontSize: 11, opacity: 0.75 }}>Cards auto-organize in the grid</span>
              </button>
            )}
          </div>
        )}
      </section>
      <Divider />
      <div style={{ textAlign: 'center', padding: '2.5rem 1.5rem 5rem' }}>
        <button className="btn-warm" onClick={() => onNav('booking')}>
          Book Your Stay <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
        </button>
      </div>
    </>
  );
}


/* â•â•â•â•â•â•â• RESTAURANT â•â•â•â•â•â•â• */
function RestCard({ r, onToast, canEdit }) {
  const imgSrc = resolveCardImg('venue', r.name, r.img);
  return (
    <div className="rest-card" style={{ position: 'relative' }}>
      {canEdit && (
        <div style={{ position: 'absolute', top: 10, right: 10, zIndex: 3, display: 'flex', gap: 6 }}
          data-hms-no-edit="1" onClick={e => e.stopPropagation()}>
          <button type="button" title="Change image" onClick={() => changeCardImg('venue', r.name, () => onToast && onToast('Venue image updated'))}
            style={toolBtnStyle('image')}><i className="fa-solid fa-image" style={{fontSize:11}}></i></button>
        </div>
      )}
      <div className="rest-card-img"><img src={imgSrc} alt={r.name} loading="lazy" /></div>
      <div style={{ padding: '1.25rem' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.15rem' }}>
          <h3 className="font-display" style={{ fontSize: '1.15rem', fontWeight: 600 }}>{r.name}</h3>
          <span className="rest-badge"><span className="rest-badge-dot"></span> Open</span>
        </div>
        <span style={{ fontSize: '0.68rem', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--accent)', opacity: 0.7, display: 'block', marginBottom: '0.5rem' }}>{r.category}</span>
        <p style={{ color: 'var(--fg-muted)', fontSize: '0.78rem', fontWeight: 400, marginBottom: '0.85rem', lineHeight: 1.5 }}>{r.desc}</p>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <span style={{ fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
            <i className="fa-regular fa-clock" style={{ color: 'var(--accent)', marginRight: '0.3rem' }}></i>{r.hours}
          </span>
          <button className="btn-sm" onClick={() => onToast(`Table at ${r.name} noted \u2014 go to Book Now to confirm.`)}>Book Now</button>
        </div>
      </div>
    </div>
  );
}

function RestaurantPage({ onNav, onToast, menus, canEditMenus, cardImages }) {
  const [tab, setTab] = useState('All');
  void cardImages;
  const filtered = tab === 'All' ? RESTAURANTS : RESTAURANTS.filter(r => r.category === tab);
  const menuList = (menus && menus.length) ? menus : (window.HMSSiteContent ? window.HMSSiteContent.DEFAULT_MENUS : []);

  const handleAdd = () => {
    const name = hmsPrompt('Menu item name', 'New Dish');
    if (!name) return;
    const sub = hmsPrompt('Short description', 'Add a short description') || 'Add a short description';
    const price = hmsPrompt('Price', '$24') || '$24';
    const category = hmsPrompt('Category (Dining / Bar)', 'Dining') || 'Dining';
    if (window.HMSSiteContent) window.HMSSiteContent.addMenu({ name: name.trim(), sub: sub.trim(), price: price.trim(), category: category.trim() });
    onToast('Menu item added');
  };

  const handleEdit = (item) => {
    const name = hmsPrompt('Menu item name', item.name);
    if (name == null || !name.trim()) return;
    const sub = hmsPrompt('Short description', item.sub || '');
    if (sub == null) return;
    const price = hmsPrompt('Price', item.price || '$24');
    if (price == null) return;
    const category = hmsPrompt('Category', item.category || 'Dining');
    if (category == null) return;
    if (window.HMSSiteContent) window.HMSSiteContent.updateMenu(item.id, {
      name: name.trim(), sub: sub.trim(), price: price.trim(), category: category.trim(),
    });
  };

  const handleMenuImage = (item) => {
    pickImageFile((url) => {
      if (!url || !window.HMSSiteContent) return;
      window.HMSSiteContent.updateMenu(item.id, { img: url });
      onToast('Menu image updated');
    });
  };

  return (
    <>
      <div className="page-header">
        <span className="section-num">02 \u2014 Culinary Arts</span>
        <h1 className="font-display">Restaurant & Bar</h1>
        <p>Six distinct dining venues, each offering a unique journey through flavors crafted by award-winning chefs.</p>
      </div>
      <TabBar tabs={REST_TABS} active={tab} onChange={setTab} items={RESTAURANTS} />
      <section style={{ padding: '0 1.5rem 3rem', maxWidth: 1100, margin: '0 auto' }}>
        {filtered.length === 0 ? (
          <EmptyState text="No restaurants found in this category." />
        ) : (
          <div className="grid-3" style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '1.25rem' }}>
            {filtered.map(r => <RestCard key={r.name} r={r} onToast={onToast} canEdit={canEditMenus} />)}
          </div>
        )}
      </section>
      <Divider />
      <section style={{ padding: '2.5rem 1.5rem 4rem', maxWidth: 1100, margin: '0 auto' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '1rem', marginBottom: '1.25rem', flexWrap: 'wrap' }}>
          <div>
            <h3 className="font-display" style={{ fontSize: '1.4rem', fontWeight: 600, margin: 0 }}>Restaurant Menu</h3>
            <p style={{ color: 'var(--warm)', fontSize: '0.78rem', fontWeight: 600, margin: '0.35rem 0 0' }}>Synced across the team hotel website</p>
          </div>
          {canEditMenus && (
            <button type="button" className="btn-ghost" data-hms-no-edit="1" onClick={handleAdd} style={{ fontSize: '0.72rem' }}>+ Add menu item</button>
          )}
        </div>
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '0.85rem' }}>
          {menuList.map(item => (
            <div key={item.id || item.name} className="menu-item" style={{ position: 'relative' }}>
              {canEditMenus && (
                <div style={{ position: 'absolute', top: 0, right: 0, display: 'flex', gap: 6 }} data-hms-no-edit="1">
                  <button type="button" title="Change image" onClick={() => handleMenuImage(item)} style={Object.assign({}, toolBtnStyle('image'), { width: 26, height: 26, borderRadius: 7 })}><i className="fa-solid fa-image" style={{fontSize:10}}></i></button>
                  <button type="button" onClick={() => handleEdit(item)} style={{ width: 26, height: 26, borderRadius: 7, border: '1px solid var(--border)', background: '#fff', color: 'var(--accent)', cursor: 'pointer' }}><i className="fa-solid fa-pen" style={{fontSize:10}}></i></button>
                  <button type="button" onClick={() => window.HMSSiteContent && window.HMSSiteContent.removeMenu(item.id)} style={{ width: 26, height: 26, borderRadius: 7, border: '1px solid #fecaca', background: '#fff1f2', color: '#e11d48', cursor: 'pointer' }}><i className="fa-solid fa-xmark" style={{fontSize:12}}></i></button>
                </div>
              )}
              <div style={{ display: 'flex', gap: 10, paddingRight: canEditMenus ? 88 : 0, minWidth: 0 }}>
                {item.img ? (
                  <img src={item.img} alt="" style={{ width: 52, height: 52, borderRadius: 8, objectFit: 'cover', flexShrink: 0 }} />
                ) : null}
                <div style={{ minWidth: 0, flex: 1 }}>
                  <span style={{ fontWeight: 600, fontSize: '0.88rem' }}>{item.name}</span>
                  <p style={{ fontSize: '0.72rem', color: 'var(--fg-muted)' }}>{item.sub}</p>
                  {item.category ? <p style={{ fontSize: '0.65rem', color: 'var(--warm)', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', marginTop: 4 }}>{item.category}</p> : null}
                </div>
                <span style={{ color: 'var(--accent)', fontWeight: 600, fontSize: '0.88rem', whiteSpace: 'nowrap' }}>{item.price || 'â€”'}</span>
              </div>
            </div>
          ))}
        </div>
      </section>
      <div style={{ textAlign: 'center', paddingBottom: '5rem' }}>
        <button className="btn-warm" onClick={() => onNav('booking')}>Book a Table <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i></button>
      </div>
    </>
  );
}


/* â•â•â•â•â•â•â• EXPERIENCE â•â•â•â•â•â•â• */
function ExperiencePage({ onNav, canEdit, onToast, cardImages }) {
  const [idx, setIdx] = useState(0);
  const t = TESTIMONIALS[idx];
  void cardImages;
  const guestImg = resolveCardImg('testimonial', String(idx), t.img);
  return (
    <>
      <div className="page-header">
        <span className="section-num">03 \u2014 Beyond the Room</span>
        <h1 className="font-display">The SPC Experience</h1>
        <p>Every detail is designed to elevate your stay from memorable to extraordinary.</p>
      </div>
      <section style={{ padding: '0 1.5rem 4rem', maxWidth: 1100, margin: '0 auto' }}>
        <div className="grid-4" style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: '1.25rem' }}>
          {EXPERIENCES.map(ex => (
            <div key={ex.title} className="exp-card" style={{ position: 'relative' }}>
              {canEdit && (
                <div style={{ position: 'absolute', top: 10, right: 10, zIndex: 3, display: 'flex', gap: 6 }}
                  data-hms-no-edit="1" onClick={e => e.stopPropagation()}>
                  <button type="button" title="Change image" onClick={() => changeCardImg('exp', ex.title, () => onToast && onToast('Experience image updated'))}
                    style={toolBtnStyle('image')}><i className="fa-solid fa-image" style={{fontSize:11}}></i></button>
                </div>
              )}
              <div className="exp-card-img"><img src={resolveCardImg('exp', ex.title, ex.img)} alt={ex.title} loading="lazy" /></div>
              <div style={{ padding: '1.25rem' }}>
                <div style={{ width: 36, height: 36, borderRadius: '50%', background: 'rgba(27,67,50,0.08)', display: 'flex', alignItems: 'center', justifyContent: 'center', marginBottom: '0.75rem' }}>
                  <i className={`fa-solid ${ex.icon}`} style={{ color: 'var(--accent)', fontSize: '0.85rem' }}></i>
                </div>
                <h4 style={{ fontWeight: 600, fontSize: '0.95rem', marginBottom: '0.3rem' }}>{ex.title}</h4>
                <p style={{ fontSize: '0.78rem', color: 'var(--fg-muted)', fontWeight: 400, lineHeight: 1.5 }}>{ex.desc}</p>
              </div>
            </div>
          ))}
        </div>
      </section>
      <Divider />
      <section style={{ padding: '2.5rem 1.5rem 4rem', maxWidth: 860, margin: '0 auto' }}>
        <div className="testimonial-box" style={{ position: 'relative' }}>
          {canEdit && (
            <div style={{ position: 'absolute', top: 12, right: 12, zIndex: 3 }} data-hms-no-edit="1">
              <button type="button" title="Change image" onClick={() => changeCardImg('testimonial', String(idx), () => onToast && onToast('Guest photo updated'))}
                style={toolBtnStyle('image')}><i className="fa-solid fa-image" style={{fontSize:11}}></i></button>
            </div>
          )}
          <div className="testi-flex" style={{ display: 'flex', alignItems: 'center', gap: '2rem', flexWrap: 'wrap', position: 'relative', zIndex: 1 }}>
            <img src={guestImg} alt="Guest" style={{ width: 68, height: 68, borderRadius: '50%', border: '2px solid rgba(255,255,255,0.3)', objectFit: 'cover', flexShrink: 0 }} />
            <div style={{ flex: 1, minWidth: 220 }}>
              <p className="font-display" style={{ fontSize: '1.2rem', fontStyle: 'italic', lineHeight: 1.6, marginBottom: '0.85rem', color: 'rgba(247,244,239,0.95)' }}>{t.text}</p>
              <div>
                <span style={{ fontWeight: 600, fontSize: '0.88rem' }}>{t.name}</span>
                <span style={{ opacity: 0.6, fontSize: '0.78rem', marginLeft: '0.4rem' }}>{t.role}</span>
              </div>
            </div>
            <div className="testi-nav" style={{ display: 'flex', gap: '0.4rem', flexShrink: 0 }}>
              {['fa-chevron-left', 'fa-chevron-right'].map((icon, i) => (
                <button key={icon}
                  style={{ width: 36, height: 36, borderRadius: '50%', border: '1px solid rgba(255,255,255,0.2)', background: 'transparent', color: '#fff', cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center', transition: 'background 0.2s' }}
                  onMouseEnter={e => e.currentTarget.style.background = 'rgba(255,255,255,0.15)'}
                  onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
                  onClick={() => setIdx(i === 0 ? (idx - 1 + TESTIMONIALS.length) % TESTIMONIALS.length : (idx + 1) % TESTIMONIALS.length)}
                  aria-label={i === 0 ? 'Previous' : 'Next'}
                >
                  <i className={`fa-solid ${icon}`} style={{ fontSize: '0.65rem' }}></i>
                </button>
              ))}
            </div>
          </div>
        </div>
      </section>
      <div style={{ textAlign: 'center', paddingBottom: '5rem' }}>
        <button className="btn-warm" onClick={() => onNav('booking')}>Book Now <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i></button>
      </div>
    </>
  );
}


/* â•â•â•â•â•â•â• BOOKING â•â•â•â•â•â•â• */
function BookingPage({ onToast, rooms }) {
  const roomList = rooms && rooms.length ? rooms : ROOMS;
  const [form, setForm] = useState({ checkIn: '', checkOut: '', guests: '', roomType: '', name: '', email: '' });
  const today = new Date().toISOString().split('T')[0];
  const update = (f, v) => setForm(p => { const n = { ...p, [f]: v }; if (f === 'checkIn' && v) n.checkOut = ''; return n; });

  const getEst = () => {
    if (!form.checkIn || !form.checkOut || !form.roomType) return null;
    const days = Math.max(1, Math.ceil((new Date(form.checkOut) - new Date(form.checkIn)) / 86400000));
    if (days <= 0) return null;
    const room = roomList.find(r => r.id === form.roomType);
    return room ? { days, price: room.price, total: days * room.price, name: room.name } : null;
  };
  const est = getEst();

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!isSiteInteractive()) return;
    onToast(`Thank you, ${form.name}! Your booking for the ${est ? est.name : 'room'} has been submitted.`);
    setForm({ checkIn: '', checkOut: '', guests: '', roomType: '', name: '', email: '' });
  };

  return (
    <>
      <div className="page-header">
        <span className="section-num">04 \u2014 Reservations</span>
        <h1 className="font-display">Book Your Stay</h1>
        <p>Select your dates and preferences, and our concierge team will confirm your booking within the hour.</p>
      </div>
      <section style={{ padding: '0 1.5rem 6rem', maxWidth: 1000, margin: '0 auto' }}>
        <div className="booking-card">
          <div className="booking-layout" style={{ display: 'flex' }}>
            <div className="booking-sidebar" style={{ flex: '0 0 280px' }}>
              <h3 className="font-display" style={{ fontSize: '1.5rem', fontWeight: 600, marginBottom: '1rem' }}>Why SPC Hotel</h3>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
                {[
                  { icon: 'fa-shield-halved', text: 'Free cancellation up to 48h before check-in' },
                  { icon: 'fa-tag', text: 'Best price guarantee on direct bookings' },
                  { icon: 'fa-champagne-glasses', text: 'Complimentary welcome drink on arrival' },
                  { icon: 'fa-wifi', text: 'High-speed WiFi throughout the property' }
                ].map((item, i) => (
                  <div key={i} style={{ display: 'flex', gap: '0.75rem', alignItems: 'flex-start' }}>
                    <i className={`fa-solid ${item.icon}`} style={{ color: 'rgba(255,255,255,0.5)', fontSize: '0.85rem', marginTop: '0.15rem', width: 16, flexShrink: 0 }}></i>
                    <span style={{ fontSize: '0.82rem', lineHeight: 1.45, color: 'rgba(247,244,239,0.85)' }}>{item.text}</span>
                  </div>
                ))}
              </div>
              <div style={{ marginTop: '2rem', paddingTop: '1.5rem', borderTop: '1px solid rgba(255,255,255,0.12)' }}>
                <p style={{ fontSize: '0.72rem', color: 'rgba(247,244,239,0.4)', fontWeight: 500, letterSpacing: '0.05em', textTransform: 'uppercase', marginBottom: '0.3rem' }}>Need help?</p>
                <p style={{ fontSize: '0.85rem', color: 'rgba(247,244,239,0.8)' }}>
                  <i className="fa-solid fa-phone" style={{ fontSize: '0.7rem', marginRight: '0.4rem' }}></i>+33 1 42 60 00 00
                </p>
              </div>
            </div>
            <div style={{ flex: 1, padding: '2.5rem' }}>
              <form onSubmit={handleSubmit}>
                <div className="grid-2" style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem', marginBottom: '1rem' }}>
                  <div>
                    <label style={{ fontSize: '0.7rem', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' }}>Check-in</label>
                    <input type="date" className="booking-input" value={form.checkIn} min={today} onChange={e => update('checkIn', e.target.value)} required />
                  </div>
                  <div>
                    <label style={{ fontSize: '0.7rem', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' }}>Check-out</label>
                    <input type="date" className="booking-input" value={form.checkOut} min={form.checkIn || today} onChange={e => update('checkOut', e.target.value)} required />
                  </div>
                  <div>
                    <label style={{ fontSize: '0.7rem', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' }}>Guests</label>
                    <select className="booking-input" value={form.guests} onChange={e => update('guests', e.target.value)} required>
                      <option value="">Select</option>
                      {[1,2,3,4].map(n => <option key={n} value={n}>{n} Guest{n > 1 ? 's' : ''}</option>)}
                    </select>
                  </div>
                  <div>
                    <label style={{ fontSize: '0.7rem', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' }}>Room Type</label>
                    <select className="booking-input" value={form.roomType} onChange={e => update('roomType', e.target.value)} required>
                      <option value="">Select</option>
                      {roomList.map(r => <option key={r.id} value={r.id}>{r.name} â€” ${r.price}/night</option>)}
                    </select>
                  </div>
                </div>
                <div className="grid-2" style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem', marginBottom: '1.5rem' }}>
                  <div>
                    <label style={{ fontSize: '0.7rem', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' }}>Full Name</label>
                    <input type="text" className="booking-input" placeholder="James Whitfield" value={form.name} onChange={e => update('name', e.target.value)} required />
                  </div>
                  <div>
                    <label style={{ fontSize: '0.7rem', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' }}>Email</label>
                    <input type="email" className="booking-input" placeholder="james@example.com" value={form.email} onChange={e => update('email', e.target.value)} required />
                  </div>
                </div>
                {est && (
                  <div style={{ background: 'var(--bg)', borderRadius: 8, padding: '1rem 1.25rem', marginBottom: '1.5rem', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '0.5rem' }}>
                    <div>
                      <span style={{ fontSize: '0.72rem', color: 'var(--fg-muted)', fontWeight: 500, textTransform: 'uppercase', letterSpacing: '0.08em' }}>Estimated Total</span>
                      <div><strong className="font-display" style={{ fontSize: '1.6rem', color: 'var(--accent)', fontWeight: 700 }}>${est.total.toLocaleString()}</strong></div>
                    </div>
                    <span style={{ fontSize: '0.82rem', color: 'var(--fg-muted)' }}>{est.days} night{est.days > 1 ? 's' : ''} &times; ${est.price}/night</span>
                  </div>
                )}
                <button type="submit" className="btn-warm" style={{ width: '100%', justifyContent: 'center' }}>
                  <i className="fa-solid fa-paper-plane" style={{ fontSize: '0.7rem' }}></i> Book Now
                </button>
              </form>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}


/* â•â•â•â•â•â•â• FOOTER â•â•â•â•â•â•â• */
function Footer({ onNav }) {
  return (
    <footer className="site-footer" data-hms-section="footer" data-hms-bg-target="1">
      <div style={{ maxWidth: 1100, margin: '0 auto' }}>
        <div className="footer-grid" style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr 1fr', gap: '2.5rem', marginBottom: '3rem' }}>
          <div>
            <span style={{ fontSize: '1.05rem', fontWeight: 700, letterSpacing: '0.18em', textTransform: 'uppercase', display: 'block', marginBottom: '0.85rem', color: '#fff' }}>SPC HOTEL</span>
            <p style={{ fontSize: '0.82rem', fontWeight: 400, lineHeight: 1.65, maxWidth: 280, marginBottom: '1.25rem', color: 'rgba(247,244,239,0.6)' }}>A sanctuary of refined hospitality. Where every guest becomes part of our story.</p>
            <div style={{ display: 'flex', gap: '0.65rem' }}>
              {['fa-instagram', 'fa-facebook-f', 'fa-x-twitter'].map(icon => (
                <a key={icon} href="#" aria-label={icon}
                  style={{ width: 34, height: 34, borderRadius: '50%', border: '1px solid rgba(255,255,255,0.15)', display: 'flex', alignItems: 'center', justifyContent: 'center', transition: 'border-color 0.2s, color 0.2s' }}
                  onMouseEnter={e => { e.currentTarget.style.borderColor = '#fff'; e.currentTarget.style.color = '#fff'; }}
                  onMouseLeave={e => { e.currentTarget.style.borderColor = 'rgba(255,255,255,0.15)'; e.currentTarget.style.color = 'rgba(247,244,239,0.5)'; }}
                >
                  <i className={`fa-brands ${icon}`} style={{ fontSize: '0.8rem' }}></i>
                </a>
              ))}
            </div>
          </div>
          <div>
            <h4 className="footer-heading">Hotel</h4>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.55rem' }}>
              <a href="javascript:void(0)" onClick={() => onNav('rooms')} style={{ fontSize: '0.82rem' }}>Rooms & Suites</a>
              <a href="javascript:void(0)" onClick={() => onNav('restaurant')} style={{ fontSize: '0.82rem' }}>Dining</a>
              <a href="javascript:void(0)" onClick={() => onNav('experience')} style={{ fontSize: '0.82rem' }}>Spa & Wellness</a>
              <a href="javascript:void(0)" onClick={() => onNav('experience')} style={{ fontSize: '0.82rem' }}>Events</a>
            </div>
          </div>
          <div>
            <h4 className="footer-heading">Services</h4>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.55rem' }}>
              <a href="javascript:void(0)" onClick={() => onNav('booking')} style={{ fontSize: '0.82rem' }}>Book Now</a>
              <a href="#" style={{ fontSize: '0.82rem' }}>Concierge</a>
              <a href="#" style={{ fontSize: '0.82rem' }}>Airport Transfer</a>
              <a href="#" style={{ fontSize: '0.82rem' }}>Gift Vouchers</a>
            </div>
          </div>
          <div>
            <h4 className="footer-heading">Contact</h4>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.55rem' }}>
              <span style={{ fontSize: '0.82rem' }}><i className="fa-solid fa-location-dot" style={{ width: 14, marginRight: '0.35rem', opacity: 0.5 }}></i>42 Rivoli Blvd, Paris</span>
              <span style={{ fontSize: '0.82rem' }}><i className="fa-solid fa-phone" style={{ width: 14, marginRight: '0.35rem', opacity: 0.5 }}></i>+33 1 42 60 00 00</span>
              <span style={{ fontSize: '0.82rem' }}><i className="fa-solid fa-envelope" style={{ width: 14, marginRight: '0.35rem', opacity: 0.5 }}></i>stay@spchotel.com</span>
            </div>
          </div>
        </div>
        <div style={{ borderTop: '1px solid rgba(255,255,255,0.1)', paddingTop: '1.25rem', display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '0.75rem' }}>
          <span style={{ fontSize: '0.72rem', color: 'rgba(247,244,239,0.35)' }}>2024 SPC Hotel. All rights reserved.</span>
          <div style={{ display: 'flex', gap: '1.25rem' }}>
            <a href="#" style={{ fontSize: '0.72rem' }}>Privacy Policy</a>
            <a href="#" style={{ fontSize: '0.72rem' }}>Terms of Service</a>
          </div>
        </div>
      </div>
    </footer>
  );
}


/* â•â•â•â•â•â•â• APP â•â•â•â•â•â•â• */
function App() {
  const [page, setPage] = useState('home');
  const [mobileOpen, setMobileOpen] = useState(false);
  const [toast, setToast] = useState({ message: '', visible: false });
  const timer = useRef(null);
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
  const [canEditExperiences, setCanEditExperiences] = useState(false);
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
    if (window.HMSSiteContent.canEditExperiences) setCanEditExperiences(window.HMSSiteContent.canEditExperiences());
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
    if (timer.current) clearTimeout(timer.current);
    setToast({ message: msg, visible: true });
    timer.current = setTimeout(() => setToast(p => ({ ...p, visible: false })), 3000);
  }, []);

  const pages = {
    home: (
      <HomePage
        onNav={navigateTo}
        onToast={showToast}
        rooms={rooms}
        menus={menus}
        canEditRooms={canEditRooms}
        onAddRoom={(partial) => window.HMSSiteContent && window.HMSSiteContent.addRoom(partial, ROOMS)}
        onEditRoom={(id, patch) => window.HMSSiteContent && window.HMSSiteContent.updateRoom(id, patch, ROOMS)}
        onRemoveRoom={(id) => window.HMSSiteContent && window.HMSSiteContent.removeRoom(id, ROOMS)}
      />
    ),
    rooms: (
      <RoomsPage
        onNav={navigateTo}
        onToast={showToast}
        rooms={rooms}
        canEditRooms={canEditRooms}
        onAddRoom={(partial) => window.HMSSiteContent && window.HMSSiteContent.addRoom(partial, ROOMS)}
        onEditRoom={(id, patch) => window.HMSSiteContent && window.HMSSiteContent.updateRoom(id, patch, ROOMS)}
        onRemoveRoom={(id) => window.HMSSiteContent && window.HMSSiteContent.removeRoom(id, ROOMS)}
      />
    ),
    restaurant: <RestaurantPage onNav={navigateTo} onToast={showToast} menus={menus} canEditMenus={canEditMenus} cardImages={cardImages} />,
    experience: <ExperiencePage onNav={navigateTo} onToast={showToast} canEdit={canEditExperiences} cardImages={cardImages} />,
    booking: <BookingPage onToast={showToast} rooms={rooms} />,
  };

  return (
    <>
      <NavBar
        currentPage={page}
        onNav={navigateTo}
        onToggle={() => setMobileOpen(v => !v)}
        mobileOpen={mobileOpen}
        links={navLinks}
        canEditNav={canEditNav}
        onAddNav={(partial) => window.HMSSiteContent && window.HMSSiteContent.addNavLink(partial)}
        onEditNav={(id, patch) => window.HMSSiteContent && window.HMSSiteContent.updateNavLink(id, patch)}
        onRemoveNav={(id) => window.HMSSiteContent && window.HMSSiteContent.removeNavLink(id)}
      />
      <MobileMenu open={mobileOpen} onClose={() => setMobileOpen(false)} onNav={navigateTo} links={navLinks} />
      <main data-hms-page={page}>{pages[page] || pages.home}</main>
      <Footer onNav={navigateTo} />
      <Toast message={toast.message} visible={toast.visible} />
    </>
  );
}

ReactDOM.createRoot(document.getElementById('root')).render(<App />);
</script>
@endverbatim

@include('students.template.partials.editor-bridge')
</body>
</html>
