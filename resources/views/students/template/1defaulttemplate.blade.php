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
    height: 100vh;
    min-height: 640px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
  }
  .hero-bg {
    position: absolute;
    inset: 0;
    background: url('https://picsum.photos/seed/luxuryhotel/1920/1080.jpg') center/cover no-repeat;
  }
  .hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(12,11,9,0.25) 0%, rgba(12,11,9,0.55) 45%, rgba(12,11,9,0.9) 80%, var(--bg) 100%);
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
</head>
<body>
<div id="root"></div>

@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef } = React;

/* ═══════════════ DATA ═══════════════ */
const ROOMS = [
  {
    id: 'deluxe', label: 'Deluxe', name: 'Deluxe King Room', price: 280,
    img: 'https://picsum.photos/seed/hotelroom1/600/400.jpg',
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
    id: 'premium', label: 'Premium', name: 'Premium Suite', price: 450,
    img: 'https://picsum.photos/seed/hotelroom2/600/400.jpg',
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
    id: 'presidential', label: 'Presidential', name: 'The Presidential Suite', price: 890,
    img: 'https://picsum.photos/seed/hotelroom3/600/400.jpg',
    desc: '120m\u00B2 of uncompromising luxury with a private terrace, dining room, butler service, and grand piano.',
    badgeStyle: { background: 'rgba(201,168,76,0.25)', borderColor: 'var(--accent-light)', color: 'var(--accent-light)' },
    amenities: [
      { icon: 'fa-bed', text: 'Emperor Bed' },
      { icon: 'fa-terrace', text: 'Terrace' },
      { icon: 'fa-bell-concierge', text: 'Butler' },
      { icon: 'fa-music', text: 'Piano' },
    ]
  }
];

const RESTAURANTS = [
  {
    name: 'Lumiere', img: 'https://picsum.photos/seed/finedining/600/400.jpg',
    desc: 'Contemporary French fine dining with a 12-course tasting menu. Michelin-starred excellence.',
    hours: '6:00 PM \u2014 11:00 PM'
  },
  {
    name: 'Kuro', img: 'https://picsum.photos/seed/sushibar/600/400.jpg',
    desc: 'Omakase sushi bar with imported Japanese ingredients. Intimate 12-seat counter experience.',
    hours: '12:00 PM \u2014 10:00 PM'
  },
  {
    name: 'The Gilded Bar', img: 'https://picsum.photos/seed/cocktailbar/600/400.jpg',
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
  { name: 'The SPC Old Fashioned', sub: '25yr bourbon, demerara, aromatic bitters', price: '$26' },
  { name: 'Gold Leaf Negroni', sub: 'gin, Campari, sweet vermouth, 24k gold leaf', price: '$28' },
  { name: 'Garden of Babylon', sub: 'gin, elderflower, cucumber, lime, tonic mist', price: '$22' },
  { name: 'Smoked Espresso Martini', sub: 'vodka, cold brew, kahlua, applewood smoke', price: '$24' },
];


/* ═══════════════ TOAST COMPONENT ═══════════════ */
function Toast({ message, visible }) {
  return (
    <div className={`toast-el${visible ? ' show' : ''}`}>
      <i className="fa-solid fa-circle-check" style={{ color: 'var(--accent)', fontSize: '1.1rem' }}></i>
      <span>{message}</span>
    </div>
  );
}


/* ═══════════════ MOBILE MENU ═══════════════ */
function MobileMenu({ open, onClose, onNavigate }) {
  const items = [
    { key: 'home', label: 'Home' },
    { key: 'rooms', label: 'Rooms' },
    { key: 'restaurant', label: 'Restaurant' },
    { key: 'experience', label: 'Experience' },
    { key: 'booking', label: 'Book Now' },
  ];
  return (
    <div className={`mobile-menu${open ? ' open' : ''}`}>
      {items.map(item => (
        <button key={item.key} onClick={() => { onNavigate(item.key); onClose(); }}>
          {item.label}
        </button>
      ))}
    </div>
  );
}


/* ═══════════════ NAVBAR ═══════════════ */
function NavBar({ currentPage, onNavigate, onToggleMobile, mobileOpen }) {
  const links = [
    { key: 'home', label: 'Home' },
    { key: 'rooms', label: 'Rooms' },
    { key: 'restaurant', label: 'Restaurant' },
    { key: 'experience', label: 'Experience' },
  ];
  return (
    <nav className="nav-bar" role="navigation" aria-label="Main navigation">
      <div style={{ maxWidth: 1200, margin: '0 auto', display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <button onClick={() => onNavigate('home')} style={{ background: 'none', border: 'none', cursor: 'pointer', padding: 0 }}>
          <span style={{ color: 'var(--fg)', fontSize: '1.05rem', fontWeight: 600, letterSpacing: '0.18em', textTransform: 'uppercase' }}>SPC HOTEL</span>
        </button>
        <div className="nav-links-desktop" style={{ display: 'flex', alignItems: 'center', gap: '2.2rem' }}>
          {links.map(link => (
            <button
              key={link.key}
              className={`nav-link${currentPage === link.key ? ' active' : ''}`}
              onClick={() => onNavigate(link.key)}
            >
              {link.label}
            </button>
          ))}
          <button className="btn-outline" onClick={() => onNavigate('booking')} style={{ fontSize: '0.72rem', padding: '0.5rem 1.1rem' }}>
            <i className="fa-regular fa-calendar"></i> Book Now
          </button>
        </div>
        <button className={`hamburger${mobileOpen ? ' active' : ''}`} onClick={onToggleMobile} aria-label="Toggle menu">
          <span></span><span></span><span></span>
        </button>
      </div>
    </nav>
  );
}


/* ═══════════════ HOME PAGE ═══════════════ */
function HomePage({ onNavigate }) {
  const highlights = [
    { icon: 'fa-bed', title: 'Rooms & Suites', desc: 'Three categories of meticulously designed accommodations for every preference.', page: 'rooms' },
    { icon: 'fa-utensils', title: 'Dining', desc: 'Three distinct venues offering French, Japanese, and artisan cocktail experiences.', page: 'restaurant' },
    { icon: 'fa-spa', title: 'Experience', desc: 'Spa, rooftop pool, fitness center, and personalized concierge services.', page: 'experience' },
  ];
  return (
    <>
      <section className="hero">
        <div className="hero-bg"></div>
        <div className="hero-overlay"></div>
        <div style={{ position: 'relative', zIndex: 2, textAlign: 'center', padding: '0 1.5rem', maxWidth: 760 }}>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '1.25rem' }}>Boutique Luxury</p>
          <h1 className="font-display hero-title" style={{ fontSize: '4.2rem', fontWeight: 900, lineHeight: 1.08, marginBottom: '1.25rem' }}>
            Where Elegance<br />
            <span style={{ color: 'var(--accent)', fontStyle: 'italic', fontWeight: 400 }}>Meets Comfort</span>
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
      <section style={{ padding: '5rem 1.5rem', maxWidth: 1200, margin: '0 auto' }}>
        <div className="grid-3" style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '1.5rem' }}>
          {highlights.map(h => (
            <div key={h.page} className="highlight-card" onClick={() => onNavigate(h.page)}>
              <i className={`fa-solid ${h.icon}`} style={{ fontSize: '1.5rem', color: 'var(--accent)', marginBottom: '1rem', display: 'block' }}></i>
              <h3 className="font-display" style={{ fontSize: '1.2rem', fontWeight: 700, marginBottom: '0.4rem' }}>{h.title}</h3>
              <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', fontWeight: 300 }}>{h.desc}</p>
            </div>
          ))}
        </div>
      </section>
    </>
  );
}


/* ═══════════════ ROOMS PAGE ═══════════════ */
function RoomsPage({ onNavigate, onToast }) {
  return (
    <>
      <div className="page-header">
        <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.75rem' }}>Accommodations</p>
        <h1 className="font-display">Our Rooms & Suites</h1>
        <p>Each room is a sanctuary of design, blending modern luxury with artisanal craftsmanship and sweeping views.</p>
      </div>
      <section style={{ padding: '0 1.5rem 6rem', maxWidth: 1200, margin: '0 auto' }}>
        <div className="grid-3" style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '1.5rem' }}>
          {ROOMS.map(room => (
            <div key={room.id} className="room-card" onClick={() => onToast(`${room.name} selected \u2014 go to Book Now to confirm.`)}>
              <div className="room-card-img">
                <img src={room.img} alt={room.name} loading="lazy" />
                <div className="room-card-badge" style={room.badgeStyle}>{room.label}</div>
                <div className="room-card-price">
                  ${room.price}<span style={{ fontFamily: 'Outfit', fontSize: '0.65rem', color: 'var(--fg-muted)', fontWeight: 300 }}> /night</span>
                </div>
              </div>
              <div style={{ padding: '1.4rem' }}>
                <h3 className="font-display" style={{ fontSize: '1.2rem', fontWeight: 700, marginBottom: '0.35rem' }}>{room.name}</h3>
                <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', fontWeight: 300, marginBottom: '1rem', lineHeight: 1.5 }}>{room.desc}</p>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.35rem' }}>
                  {room.amenities.map(a => (
                    <span key={a.text} className="room-amenity">
                      <i className={`fa-solid ${a.icon}`} style={{ fontSize: '0.6rem', color: 'var(--accent)' }}></i> {a.text}
                    </span>
                  ))}
                </div>
              </div>
            </div>
          ))}
        </div>
        <div style={{ textAlign: 'center', marginTop: '3rem' }}>
          <button className="btn-primary" onClick={() => onNavigate('booking')}>
            Book Now <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
          </button>
        </div>
      </section>
    </>
  );
}


/* ═══════════════ RESTAURANT PAGE ═══════════════ */
function RestaurantPage({ onNavigate, onToast }) {
  return (
    <>
      <div className="page-header">
        <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.75rem' }}>Culinary Arts</p>
        <h1 className="font-display">Restaurant & Bar</h1>
        <p>Three distinct dining venues, each offering a unique journey through flavors crafted by award-winning chefs.</p>
      </div>
      <section style={{ padding: '0 1.5rem 4rem', maxWidth: 1200, margin: '0 auto' }}>
        <div className="grid-3" style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '1.5rem', marginBottom: '4rem' }}>
          {RESTAURANTS.map(r => (
            <div key={r.name} className="rest-card">
              <div className="rest-card-img">
                <img src={r.img} alt={r.name} loading="lazy" />
              </div>
              <div style={{ padding: '1.4rem' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.4rem' }}>
                  <h3 className="font-display" style={{ fontSize: '1.15rem', fontWeight: 700 }}>{r.name}</h3>
                  <span style={{ display: 'flex', alignItems: 'center', gap: '0.35rem', fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                    <span className="rest-dot"></span> Open Now
                  </span>
                </div>
                <p style={{ color: 'var(--fg-muted)', fontSize: '0.78rem', fontWeight: 300, marginBottom: '1rem', lineHeight: 1.5 }}>{r.desc}</p>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                  <span style={{ fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                    <i className="fa-regular fa-clock" style={{ color: 'var(--accent)', marginRight: '0.3rem' }}></i>{r.hours}
                  </span>
                  <button className="btn-outline" style={{ padding: '0.35rem 0.8rem', fontSize: '0.68rem' }} onClick={() => onToast(`Table at ${r.name} noted \u2014 go to Book Now to confirm.`)}>
                    Book Now
                  </button>
                </div>
              </div>
            </div>
          ))}
        </div>

        {/* Menus */}
        <div className="grid-2" style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '3rem', maxWidth: 920, margin: '0 auto 4rem' }}>
          <div>
            <h3 className="font-display" style={{ fontSize: '1.35rem', fontWeight: 700, marginBottom: '0.25rem' }}>Lumiere Tasting Menu</h3>
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.78rem', fontWeight: 300, marginBottom: '1.25rem' }}>Chef's seasonal selection \u2014 $185 per person</p>
            <div>
              {LUMIERE_MENU.map(item => (
                <div key={item.name} className="menu-item">
                  <div>
                    <span style={{ fontWeight: 500 }}>{item.name}</span>
                    <p style={{ fontSize: '0.72rem', color: 'var(--fg-muted)', fontWeight: 300 }}>{item.sub}</p>
                  </div>
                  <span style={{ color: 'var(--accent)', fontFamily: 'Playfair Display, serif' }}>{item.price}</span>
                </div>
              ))}
            </div>
          </div>
          <div>
            <h3 className="font-display" style={{ fontSize: '1.35rem', fontWeight: 700, marginBottom: '0.25rem' }}>The Gilded Bar</h3>
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.78rem', fontWeight: 300, marginBottom: '1.25rem' }}>Signature cocktails \u2014 $22\u2013$28</p>
            <div>
              {BAR_MENU.map(item => (
                <div key={item.name} className="menu-item">
                  <div>
                    <span style={{ fontWeight: 500 }}>{item.name}</span>
                    <p style={{ fontSize: '0.72rem', color: 'var(--fg-muted)', fontWeight: 300 }}>{item.sub}</p>
                  </div>
                  <span style={{ color: 'var(--accent)', fontFamily: 'Playfair Display, serif' }}>{item.price}</span>
                </div>
              ))}
            </div>
          </div>
        </div>

        <div style={{ textAlign: 'center' }}>
          <button className="btn-primary" onClick={() => onNavigate('booking')}>
            Book a Table <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
          </button>
        </div>
      </section>
    </>
  );
}


/* ═══════════════ EXPERIENCE PAGE ═══════════════ */
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


/* ═══════════════ BOOKING PAGE ═══════════════ */
function BookingPage({ onToast }) {
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
    const room = ROOMS.find(r => r.id === form.roomType);
    if (!room) return null;
    return { days, price: room.price, total: days * room.price };
  };

  const estimate = getEstimate();

  const handleSubmit = (e) => {
    e.preventDefault();
    const room = ROOMS.find(r => r.id === form.roomType);
    onToast(`Thank you, ${form.name}! Your booking for the ${room.name} has been submitted.`);
    setForm({ checkIn: '', checkOut: '', guests: '', roomType: '', name: '', email: '' });
  };

  const minCheckOut = form.checkIn || today;

  return (
    <>
      <div className="page-header">
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
                  {ROOMS.map(r => <option key={r.id} value={r.id}>{r.name} \u2014 ${r.price}/night</option>)}
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
                    <strong style={{ color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontSize: '1.15rem' }}>${estimate.total.toLocaleString()}</strong>
                    <span style={{ opacity: 0.55 }}> ({estimate.days} night{estimate.days > 1 ? 's' : ''} x ${estimate.price}/night)</span>
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


/* ═══════════════ FOOTER ═══════════════ */
function Footer({ onNavigate }) {
  return (
    <footer style={{ padding: '3.5rem 1.5rem 1.75rem', borderTop: '1px solid var(--border)' }}>
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


/* ═══════════════ APP ═══════════════ */
function App() {
  const [page, setPage] = useState('home');
  const [mobileOpen, setMobileOpen] = useState(false);
  const [toast, setToast] = useState({ message: '', visible: false });
  const toastTimer = useRef(null);

  const navigateTo = useCallback((target) => {
    setPage(target);
    window.scrollTo({ top: 0 });
    setMobileOpen(false);
  }, []);

  const showToast = useCallback((msg) => {
    if (toastTimer.current) clearTimeout(toastTimer.current);
    setToast({ message: msg, visible: true });
    toastTimer.current = setTimeout(() => {
      setToast(prev => ({ ...prev, visible: false }));
    }, 3000);
  }, []);

  const pages = {
    home: <HomePage onNavigate={navigateTo} />,
    rooms: <RoomsPage onNavigate={navigateTo} onToast={showToast} />,
    restaurant: <RestaurantPage onNavigate={navigateTo} onToast={showToast} />,
    experience: <ExperiencePage onNavigate={navigateTo} />,
    booking: <BookingPage onToast={showToast} />,
  };

  return (
    <>
      <NavBar
        currentPage={page}
        onNavigate={navigateTo}
        onToggleMobile={() => setMobileOpen(v => !v)}
        mobileOpen={mobileOpen}
      />
      <MobileMenu
        open={mobileOpen}
        onClose={() => setMobileOpen(false)}
        onNavigate={navigateTo}
      />
      <main>{pages[page]}</main>
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
