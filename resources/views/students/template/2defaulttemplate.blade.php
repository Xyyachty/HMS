<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
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
    position: fixed !important; top: 0 !important; left: 0; right: 0; z-index: 1000;
    padding: 0 2rem; height: 64px;
    background: var(--card);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
  }
  .hero-split {
    margin-top: 64px !important;
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
  .hero-slide-img {
    position: absolute; inset: 0;
    opacity: 0;
    transition: opacity 1.2s ease;
  }
  .hero-slide-img.is-active { opacity: 1; }
  .hero-dots {
    position: absolute; left: 0; right: 0; bottom: 1.5rem; z-index: 3;
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  }
  .hero-dot {
    width: 8px; height: 8px; border-radius: 50%; border: none; padding: 0;
    background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.2s;
  }
  .hero-dot.is-active { background: var(--accent); width: 22px; border-radius: 4px; }
  .hero-edit-btn {
    position: absolute; right: 1.25rem; bottom: 1.4rem; z-index: 3;
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: rgba(26,26,26,0.65); color: #fff;
    border: 1px solid rgba(255,255,255,0.3); border-radius: 999px;
    padding: 0.4rem 0.9rem; font-size: 0.68rem; letter-spacing: 0.04em;
    cursor: pointer; backdrop-filter: blur(4px);
  }
  .hero-edit-btn:hover { border-color: var(--accent); }
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

  /* -- Room status badge / picker, availability calendar, room + menu detail modal -- */
  .room-status-badge {
    position: absolute; top: 0.85rem; left: 0.85rem;
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent;
  }
  .room-status-badge.status-available {
    background: rgba(45,122,79,0.14); color: #1b4332; border-color: rgba(45,122,79,0.3);
  }
  .room-status-badge.status-cleaning {
    background: rgba(193,120,73,0.16); color: #94510f; border-color: rgba(193,120,73,0.35);
  }
  .room-status-badge.status-maintenance {
    background: rgba(225,29,72,0.12); color: #be123c; border-color: rgba(225,29,72,0.3);
  }

  .room-modal-overlay {
    position: fixed; inset: 0; z-index: 2000;
    background: rgba(26,26,26,0.6);
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
    border-radius: 12px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.25);
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
    background: rgba(255,255,255,0.9); color: var(--fg);
    cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
  }
  .room-status-picker {
    display: flex; flex-wrap: wrap; gap: 0.45rem;
  }
  .room-status-option {
    font-family: 'DM Sans', sans-serif; font-size: 0.72rem; font-weight: 600;
    letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.45rem 0.85rem; border-radius: 100px;
    border: 1.5px solid var(--border); background: transparent;
    color: var(--fg-muted); cursor: pointer; transition: all 0.15s;
  }
  .room-status-option:hover { border-color: var(--accent); color: var(--accent); }
  .room-status-option.active {
    background: var(--accent); border-color: var(--accent); color: #fff;
  }

  .room-cal {
    background: var(--bg-alt); border: 1px solid var(--border);
    border-radius: 12px; padding: 0.9rem 1rem 1rem;
  }
  .room-cal-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 0.75rem;
  }
  .room-cal-title {
    font-family: 'DM Sans', sans-serif; font-size: 0.82rem; font-weight: 600;
    color: var(--fg);
  }
  .room-cal-nav {
    width: 26px; height: 26px; border-radius: 8px; border: 1px solid var(--border);
    background: var(--card); color: var(--fg-muted); cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all 0.15s;
  }
  .room-cal-nav:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
  .room-cal-nav:disabled { opacity: 0.3; cursor: not-allowed; }
  .room-cal-weekdays, .room-cal-grid {
    display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.25rem;
  }
  .room-cal-weekdays {
    margin-bottom: 0.35rem;
  }
  .room-cal-weekdays span {
    font-size: 0.62rem; letter-spacing: 0.06em; text-transform: uppercase;
    color: var(--fg-muted); text-align: center;
  }
  .room-cal-day {
    aspect-ratio: 1; border-radius: 8px; border: 1px solid transparent;
    background: var(--bg); color: var(--fg);
    font-family: 'DM Sans', sans-serif; font-size: 0.74rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all 0.15s;
  }
  .room-cal-day:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
  .room-cal-day.is-blank { visibility: hidden; cursor: default; }
  .room-cal-day.is-past { color: var(--fg-muted); opacity: 0.4; cursor: not-allowed; }
  .room-cal-day.is-booked { background: rgba(225,29,72,0.1); color: #be123c; cursor: not-allowed; }
  .room-cal-day.is-in-range { background: rgba(193,120,73,0.14); }
  .room-cal-day.is-selected {
    background: var(--accent); border-color: var(--accent); color: #fff; font-weight: 700;
  }
  .room-cal-legend {
    display: flex; flex-wrap: wrap; gap: 0.9rem; margin-top: 0.75rem;
  }
  .room-cal-legend span {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.68rem; color: var(--fg-muted);
  }
  .room-cal-swatch {
    width: 10px; height: 10px; border-radius: 3px; display: inline-block;
    background: var(--border);
  }
  .room-cal-swatch.is-available { background: var(--border); }
  .room-cal-swatch.is-booked { background: #be123c; }
  .room-cal-swatch.is-past { background: var(--fg-muted); opacity: 0.5; }

  .menu-food-card {
    border-radius: 10px; overflow: hidden;
    background: var(--card); border: 1px solid var(--border);
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    transition: box-shadow 0.25s, transform 0.25s;
    display: flex; flex-direction: column;
  }
  .menu-food-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,0.08); transform: translateY(-3px); }
  .menu-food-img {
    position: relative; height: 180px; overflow: hidden; background: var(--bg-alt);
  }
  .menu-food-img img {
    width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s;
  }
  .menu-food-card:hover .menu-food-img img { transform: scale(1.04); }
  .menu-food-img-fallback {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    color: var(--fg-muted); background: var(--bg-alt);
  }
  .menu-food-price {
    position: absolute; bottom: 0.75rem; right: 0.75rem;
    background: rgba(255,255,255,0.92); padding: 0.3rem 0.65rem; border-radius: 5px;
    font-family: 'Cormorant Garamond', serif; font-size: 0.95rem; color: var(--accent);
  }
  .menu-food-body { padding: 1.1rem 1.15rem 1.25rem; flex: 1; }

  /* -- Restaurant Cards -- */
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

  /* Customer sign in / sign up. This is the hotel site's own visitor session —
     separate from the HMS login the student signed in with to open the builder.
     public/js/hms-hotel-auth.js is the bridge; it broadcasts hms-hotel-auth
     whenever the session changes. */
  .nav-auth {
    display: flex; align-items: center; gap: 0.5rem;
    margin-left: 0.35rem; padding-left: 0.9rem;
    border-left: 1px solid var(--border);
  }
  .nav-auth-btn {
    font-family: inherit; font-size: 0.7rem; font-weight: 600;
    letter-spacing: 0.08em; text-transform: uppercase; white-space: nowrap;
    padding: 0.52rem 1.05rem; border-radius: 6px; cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s, transform 0.2s;
  }
  .nav-auth-btn.is-ghost {
    background: transparent; color: var(--fg); border: 1px solid var(--border);
  }
  .nav-auth-btn.is-ghost:hover { border-color: var(--accent); color: var(--accent); }
  .nav-auth-btn.is-solid {
    background: var(--accent); color: var(--bg); border: 1px solid var(--accent);
  }
  .nav-auth-btn.is-solid:hover { background: var(--accent-light); border-color: var(--accent-light); transform: translateY(-1px); }
  .nav-auth-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
  .nav-auth-who {
    display: inline-flex; align-items: center; gap: 0.45rem;
    color: var(--fg); font-size: 0.78rem; max-width: 180px;
  }
  .nav-auth-who b { font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  /* Stacked full-width inside the mobile menu, where there is no room for a row. */
  .nav-auth.is-compact {
    flex-direction: column; gap: 0.6rem;
    border-left: none; border-top: 1px solid var(--border);
    margin: 0.9rem 0 0; padding: 1.3rem 0 0; width: min(260px, 72vw);
  }
  .nav-auth.is-compact .nav-auth-btn { width: 100%; }

  .auth-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.7);
    display: flex; align-items: flex-start; justify-content: center;
    padding: 2rem 1.5rem; z-index: 5000; overflow-y: auto;
  }
  .auth-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    width: 100%; max-width: 400px; margin: auto; padding: 1.75rem;
  }
  .auth-card-head {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 1rem; margin-bottom: 1.35rem;
  }
  .auth-eyebrow {
    color: var(--accent); font-size: 0.64rem; letter-spacing: 0.2em;
    text-transform: uppercase; margin-bottom: 0.4rem;
  }
  .auth-close {
    width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
    border: 1px solid var(--border); background: transparent;
    color: var(--fg); cursor: pointer; line-height: 1;
  }
  .auth-close:hover { border-color: var(--accent); color: var(--accent); }
  .auth-field { display: block; margin-bottom: 0.9rem; }
  .auth-field > span {
    display: block; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--fg-muted); margin-bottom: 0.35rem;
  }
  .auth-input {
    width: 100%; font-family: inherit; font-size: 0.88rem;
    background: transparent; color: var(--fg);
    border: 1px solid var(--border); border-radius: 6px;
    padding: 0.7rem 0.9rem; outline: none; transition: border-color 0.2s;
  }
  .auth-input:focus { border-color: var(--accent); }
  .auth-error { margin: 0 0 0.9rem; color: #e11d48; font-size: 0.78rem; }
  .auth-swap {
    margin-top: 1.1rem; text-align: center;
    color: var(--fg-muted); font-size: 0.78rem;
  }
  .auth-swap button {
    background: none; border: none; padding: 0; cursor: pointer;
    color: var(--accent); font-family: inherit; font-size: 0.78rem; font-weight: 600;
  }
  .auth-swap button:hover { text-decoration: underline; }

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

<script src="{{ asset('js/hms-site-content.js') }}?v={{ filemtime(public_path('js/hms-site-content.js')) }}"></script>
<script>
  window.HMS_ROOM_MANAGEMENT_URL = @json(route('students.roommanagement.manage'));
  window.HMS_VERIFY_GUEST_URL = @json(route('students.frontdesk.verify-guest'));
  // Resolved out here: the raw block below is not compiled, so Blade never runs inside it.
  window.HMS_DEFAULT_LOGO = @json(asset('images/hotel-logo-default.svg'));
</script>
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

/* Room categories, statuses and menu categories match the server's rules
   (app/Models/HotelRoom.php, HotelMenuItem.php) — rooms/menus are now shared
   with the DB-backed modules, so labels here must agree with what they write. */
const ROOM_CATEGORIES = ['Classic', 'Superior', 'Deluxe', 'Premium', 'Family'];
const ROOM_STATUSES = ['Available', 'Cleaning', 'Maintenance'];
const ROOM_TABS = ROOM_CATEGORIES;
const ROOM_PAGE_TABS = ['All', ...ROOM_CATEGORIES];
const MENU_CATEGORIES = ['Main Dishes', 'Appetizers', 'Soups', 'Desserts', 'Beverages'];
const MENU_TABS = MENU_CATEGORIES;

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

function normalizeRoomCategory(value) {
  const raw = String(value || 'Classic').trim().toLowerCase();
  const match = ROOM_CATEGORIES.find(c => c.toLowerCase() === raw);
  return match || 'Classic';
}
function normalizeMenuCategory(value) {
  const raw = String(value || 'Main Dishes').trim().toLowerCase();
  const match = MENU_CATEGORIES.find(c => c.toLowerCase() === raw);
  return match || 'Main Dishes';
}
function normalizeRoomStatus(value) {
  const raw = String(value || 'Available').trim().toLowerCase();
  const match = ROOM_STATUSES.find(s => s.toLowerCase() === raw);
  return match || 'Available';
}
function roomStatusClass(status) {
  return 'status-' + normalizeRoomStatus(status).toLowerCase();
}

/* Arrival lifecycle of a booking: Booked -> Arrived. The server derives it from
   hotel_bookings.arrived_at and sends it down on the room's `reservation`. */
function reservationArrivalStatus(reservation) {
  const raw = String((reservation && reservation.arrivalStatus) || 'Booked').trim().toLowerCase();
  return raw === 'arrived' ? 'Arrived' : 'Booked';
}
function todayIsoDate() {
  return new Date().toISOString().split('T')[0];
}
/* Front Desk may only mark arrival on or after the reserved check-in date. */
function canMarkArrived(reservation) {
  if (!reservation) return false;
  if (reservationArrivalStatus(reservation) === 'Arrived') return false;
  const checkIn = String(reservation.checkIn || '').trim();
  if (!checkIn) return true;
  return todayIsoDate() >= checkIn;
}
function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0';
  return '₱' + n.toLocaleString();
}
function menuFoodImg(item) {
  if (item && item.img) return item.img;
  const seed = encodeURIComponent((item && (item.id || item.name)) || 'menu');
  return 'https://picsum.photos/seed/' + seed + '/800/600.jpg';
}
function roomCardImg(room) {
  if (room && room.img) return room.img;
  const seed = encodeURIComponent((room && (room.id || room.name)) || 'room');
  return 'https://picsum.photos/seed/room-' + seed + '/800/600.jpg';
}

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

function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

/*
 * Leaves the template for a staff page. The template renders inside the builder's
 * iframe, so the whole top window has to move or the staff page would open inside
 * the canvas. Touching window.top can throw, and if it does the iframe still has to
 * go somewhere — a stuck page is worse than a page that lost its shell.
 */
function hmsNavigateTop(url) {
  if (!url) return;
  try {
    if (window.top && window.top !== window) {
      window.top.location.assign(url);
      return;
    }
  } catch (e) { /* top is out of reach — fall through to this frame */ }
  window.location.assign(url);
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

/* Room/menu images are stored as base64 in the DB. A raw camera/screenshot upload blows
   past MySQL's max_allowed_packet and the insert dies with "MySQL server has gone away",
   so every picked image is downscaled and re-encoded before it leaves the browser. */
const IMAGE_MAX_DIMENSION = 1280;
const IMAGE_MAX_BYTES = 600 * 1024;

function compressImageDataUrl(dataUrl, done) {
  const src = String(dataUrl || '');
  if (!src.startsWith('data:image/')) { done(src); return; }

  const img = new Image();
  img.onload = function () {
    try {
      const scale = Math.min(1, IMAGE_MAX_DIMENSION / Math.max(img.width, img.height));
      const canvas = document.createElement('canvas');
      canvas.width = Math.max(1, Math.round(img.width * scale));
      canvas.height = Math.max(1, Math.round(img.height * scale));
      const ctx = canvas.getContext('2d');
      // Flatten onto the card background so transparent PNGs don't turn dark as JPEG.
      ctx.fillStyle = '#ffffff';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);

      let quality = 0.82;
      let out = canvas.toDataURL('image/jpeg', quality);
      while (out.length > IMAGE_MAX_BYTES && quality > 0.4) {
        quality -= 0.12;
        out = canvas.toDataURL('image/jpeg', quality);
      }
      done(out.length < src.length ? out : src);
    } catch (e) {
      done(src);
    }
  };
  img.onerror = function () { done(src); };
  img.src = src;
}

function pickImageFile(onPicked) {
  const handle = (url) => {
    if (typeof onPicked !== 'function') return;
    compressImageDataUrl(url, onPicked);
  };
  if (window.HMSSiteContent && typeof window.HMSSiteContent.pickImageFile === 'function') {
    window.HMSSiteContent.pickImageFile(handle);
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
      handle(String(reader.result || ''));
      if (input.parentNode) input.parentNode.removeChild(input);
    };
    reader.onerror = function () {
      if (input.parentNode) input.parentNode.removeChild(input);
    };
    reader.readAsDataURL(file);
  });
  input.click();
}

function amenityImg(item) {
  if (item && item.img) return item.img;
  const seed = encodeURIComponent((item && (item.id || item.name)) || 'amenity');
  return 'https://picsum.photos/seed/' + seed + '/800/600.jpg';
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

/* One logo for the whole site. It lives under a single card-image key, so the
   header, the footer, the mobile menu and every page all read the same value —
   changing it anywhere changes it everywhere. */
const DEFAULT_LOGO = window.HMS_DEFAULT_LOGO || '/images/hotel-logo-default.svg';
const LOGO_ID = 'logo';

/* Five-slide hero. Front Desk owns Home, so these follow the exact __navLinks
   pattern — page:'home', fixed count, per-slide image replace only. */
const DEFAULT_HERO_SLIDES = [
  { id: 'hero-slide-1', img: 'https://picsum.photos/seed/resortlux/1200/900.jpg' },
  { id: 'hero-slide-2', img: 'https://picsum.photos/seed/resortlobby/1200/900.jpg' },
  { id: 'hero-slide-3', img: 'https://picsum.photos/seed/resortpool/1200/900.jpg' },
  { id: 'hero-slide-4', img: 'https://picsum.photos/seed/resortsuite/1200/900.jpg' },
  { id: 'hero-slide-5', img: 'https://picsum.photos/seed/resortdining/1200/900.jpg' },
];
const LEGACY_LOGO_IDS = ['logo-home', 'logo-rooms', 'logo-restaurant'];

function resolveLogo() {
  const shared = resolveCardImg('brand', LOGO_ID, '');
  if (shared) return shared;
  for (let i = 0; i < LEGACY_LOGO_IDS.length; i++) {
    const legacy = resolveCardImg('brand', LEGACY_LOGO_IDS[i], '');
    if (legacy) return legacy;
  }
  return DEFAULT_LOGO;
}

function BrandLogo({ size }) {
  const px = size || 34;
  return (
    <img
      src={resolveLogo()}
      alt="Hotel logo"
      data-hms-move-root="1"
      data-hms-dynamic-src="1"
      data-hms-content-kind="brand"
      data-hms-content-id={LOGO_ID}
      style={{ width: px, height: px, objectFit: 'contain', display: 'block', flexShrink: 0 }}
      onError={(e) => {
        if (e.target.getAttribute('data-logo-fallback') === '1') return;
        e.target.setAttribute('data-logo-fallback', '1');
        e.target.src = DEFAULT_LOGO;
      }}
    />
  );
}

function ChangeLogoButton({ onToast }) {
  return (
    <button
      type="button"
      title="Change logo"
      data-hms-no-edit="1"
      onClick={() => changeCardImg('brand', LOGO_ID, () => { if (onToast) onToast('Logo updated — applied across the whole site'); })}
      style={Object.assign({}, toolBtnStyle('image'), { width: 22, height: 22 })}
    ><i className="fa-solid fa-image" style={{ fontSize: 10 }}></i></button>
  );
}

const BLOCK_HOURS = 12;

function stayBlocks(checkIn, checkOut, checkInTime) {
  if (!checkIn || !checkOut) return 1;
  const clock = /^\d{1,2}:\d{2}/.test(String(checkInTime || '')) ? checkInTime : '00:00';
  const start = new Date(`${checkIn}T${clock}`);
  const end = new Date(`${checkOut}T${clock}`);
  const hours = (end - start) / 3600000;
  if (!Number.isFinite(hours) || hours <= 0) return 1;
  return Math.max(1, Math.ceil(hours / BLOCK_HOURS));
}

/* 'YYYY-MM-DD' + n days -> 'YYYY-MM-DD'. Built from the date parts, not by adding
   ms to a Date, so it can't drift across a DST boundary. */
function addDays(dateStr, n) {
  const [y, m, d] = String(dateStr || '').split('-').map(Number);
  if (!y || !m || !d) return dateStr;
  const dt = new Date(y, m - 1, d + n);
  return dt.getFullYear() + '-' + String(dt.getMonth() + 1).padStart(2, '0') + '-' + String(dt.getDate()).padStart(2, '0');
}

function formatClockTime(value) {
  const raw = String(value || '').trim();
  const match = /^(\d{1,2}):(\d{2})/.exec(raw);
  if (!match) return '';
  const hours = Number(match[1]);
  if (!Number.isFinite(hours)) return '';
  const suffix = hours >= 12 ? 'PM' : 'AM';
  const display = hours % 12 === 0 ? 12 : hours % 12;
  return `${display}:${match[2]} ${suffix}`;
}

function formatCheckIn(date, time) {
  const day = String(date || '').trim();
  const clock = formatClockTime(time);
  if (!day) return clock || '—';
  return clock ? `${day} · ${clock}` : day;
}

/* ── Room availability calendar helpers ──────────────────────────────────
   A room can hold several open bookings at once now (one in-house guest, plus
   stays booked for later), so availability is a set of blocked dates rather
   than a single status. */

function todayStr() {
  const d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

/* Expands each [from, to) range into a Set of 'YYYY-MM-DD' strings. `to` is the
   checkout date and is exclusive — the guest is gone by morning, so that day is
   free for the next booking. */
function bookedDateSet(ranges) {
  const set = new Set();
  (ranges || []).forEach(r => {
    if (!r || !r.from || !r.to) return;
    let cursor = r.from;
    let guard = 0;
    while (cursor < r.to && guard < 800) {
      set.add(cursor);
      cursor = addDays(cursor, 1);
      guard += 1;
    }
  });
  return set;
}

/* True if any night in [checkIn, checkOut) falls on a blocked date. */
function rangeHitsBooked(checkIn, checkOut, bookedSet) {
  if (!checkIn || !checkOut) return false;
  let cursor = checkIn;
  let guard = 0;
  while (cursor < checkOut && guard < 800) {
    if (bookedSet.has(cursor)) return true;
    cursor = addDays(cursor, 1);
    guard += 1;
  }
  return false;
}

/* Calendar cells for one month: null for the leading blanks before day 1, then
   'YYYY-MM-DD' for each day. */
function monthCells(year, month) {
  const first = new Date(year, month, 1);
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const cells = [];
  for (let i = 0; i < first.getDay(); i++) cells.push(null);
  for (let day = 1; day <= daysInMonth; day++) {
    cells.push(year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0'));
  }
  return cells;
}

const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

function addonStepBtn(disabled) {
  return {
    width: 26, height: 26, borderRadius: 6, border: '1px solid var(--border)',
    background: disabled ? 'var(--bg-alt)' : 'var(--card)', color: disabled ? 'var(--fg-muted)' : 'var(--accent)',
    cursor: disabled ? 'not-allowed' : 'pointer', fontSize: '0.9rem', display: 'inline-flex',
    alignItems: 'center', justifyContent: 'center', lineHeight: 1,
  };
}

/*
 * Front Desk / Restaurant staff hand a room-service order to a guest who is
 * physically in the hotel right now — anyone not carrying an open, checked-in
 * stay is not a valid destination for the tray. A room only carries a
 * `reservation` while its booking is still open, so releasing the room drops
 * the departed guest out of this list on its own.
 */
function checkedInRoomsFor(rooms) {
  return (rooms || []).filter(r => (
    r.reservation && r.reservation.status === 'Checked In'
  ));
}

function RoomAvailabilityCalendar({ ranges, checkIn, checkOut, onPick }) {
  const today = todayStr();
  const bookedSet = useMemo(() => bookedDateSet(ranges), [ranges]);
  const [cursor, setCursor] = useState(() => {
    const [y, m] = today.split('-').map(Number);
    return { year: y, month: m - 1 };
  });

  const isCurrentMonth = (() => {
    const [y, m] = today.split('-').map(Number);
    return cursor.year === y && cursor.month === m - 1;
  })();

  const cells = useMemo(() => monthCells(cursor.year, cursor.month), [cursor]);

  const goPrev = () => setCursor(prev => {
    const month = prev.month === 0 ? 11 : prev.month - 1;
    const year = prev.month === 0 ? prev.year - 1 : prev.year;
    return { year, month };
  });
  const goNext = () => setCursor(prev => {
    const month = prev.month === 11 ? 0 : prev.month + 1;
    const year = prev.month === 11 ? prev.year + 1 : prev.year;
    return { year, month };
  });

  return (
    <div className="room-cal" data-hms-no-edit="1">
      <div className="room-cal-header">
        <button type="button" className="room-cal-nav" onClick={goPrev} disabled={isCurrentMonth} aria-label="Previous month">
          <i className="fa-solid fa-chevron-left" style={{ fontSize: 11 }}></i>
        </button>
        <span className="room-cal-title">{MONTH_NAMES[cursor.month]} {cursor.year}</span>
        <button type="button" className="room-cal-nav" onClick={goNext} aria-label="Next month">
          <i className="fa-solid fa-chevron-right" style={{ fontSize: 11 }}></i>
        </button>
      </div>
      <div className="room-cal-weekdays">
        {['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'].map(d => <span key={d}>{d}</span>)}
      </div>
      <div className="room-cal-grid">
        {cells.map((day, i) => {
          if (!day) return <span key={'blank' + i} className="room-cal-day is-blank"></span>;
          const isPast = day < today;
          const isBooked = bookedSet.has(day);
          const isSelected = day === checkIn || day === checkOut;
          const isInRange = checkIn && checkOut && day > checkIn && day < checkOut;
          const disabled = isPast || (isBooked && day !== checkIn);
          const cls = ['room-cal-day'];
          if (isPast) cls.push('is-past');
          if (isBooked) cls.push('is-booked');
          if (isSelected) cls.push('is-selected');
          if (isInRange) cls.push('is-in-range');
          return (
            <button
              key={day}
              type="button"
              className={cls.join(' ')}
              disabled={disabled}
              onClick={() => onPick(day)}
            >
              {Number(day.slice(8, 10))}
            </button>
          );
        })}
      </div>
      <div className="room-cal-legend">
        <span><i className="room-cal-swatch is-available"></i> Available</span>
        <span><i className="room-cal-swatch is-booked"></i> Booked</span>
        <span><i className="room-cal-swatch is-past"></i> Past</span>
      </div>
    </div>
  );
}

function RoomDetailModal({ room, addons, onClose, onChangeStatus, canEditStatus, canReserve, onReserve, onToast }) {
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
    checkInTime: '',
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
  // Housekeeping add-ons ticked for this stay: [{ dbId, name, price, qty }].
  const [addonLines, setAddonLines] = useState([]);
  const [showAddons, setShowAddons] = useState(false);

  useEffect(() => {
    setStep('details');
    setGuestForm({ fullName: '', contactNo: '', email: '', idNumber: '', checkIn: '', checkInTime: '', checkOut: '' });
    setPaymentForm({ type: 'full', amount: '', method: 'Cash', reference: '', payerName: '', notes: '' });
    setAddonLines([]);
    setShowAddons(false);
  }, [room.id]);

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const blocks = stayBlocks(guestForm.checkIn, guestForm.checkOut, guestForm.checkInTime);
  const totalDue = blocks * (Number(room.price) || 0);
  // Check-out must be a later date than check-in — a same-day stay isn't a valid
  // booking here, so the day of check-in itself is not selectable on the calendar.
  const minCheckOut = addDays(guestForm.checkIn || today, 1);
  const bookedSet = useMemo(() => bookedDateSet(room.bookedRanges), [room.bookedRanges]);

  const updateGuest = (field, value) => {
    setGuestForm(prev => {
      const next = { ...prev, [field]: value };
      if (field === 'checkIn') next.checkOut = '';
      return next;
    });
  };

  // Clicking the calendar fills the same Check-In / Check-Out fields the form uses,
  // so either one can drive the other and the form stays the source of truth.
  const pickDate = (day) => {
    if (!guestForm.checkIn || guestForm.checkOut || day <= guestForm.checkIn) {
      updateGuest('checkIn', day);
      return;
    }
    if (rangeHitsBooked(guestForm.checkIn, day, bookedSet)) {
      if (onToast) onToast('That range crosses a booked date.');
      return;
    }
    updateGuest('checkOut', day);
  };

  const updatePayment = (field, value) => {
    setPaymentForm(prev => Object.assign({}, prev, { [field]: value }));
  };

  const addonList = Array.isArray(addons) ? addons : [];
  const addonQty = (dbId) => {
    const line = addonLines.find(l => l.dbId === dbId);
    return line ? line.qty : 0;
  };
  // Steps a line up or down, dropping it at zero so the payload carries only what was
  // actually picked. Availability is the server's number, not one counted here.
  const stepAddon = (addon, delta) => {
    const next = Math.max(0, Math.min(addon.available, addonQty(addon.dbId) + delta));
    setAddonLines(prev => {
      const rest = prev.filter(l => l.dbId !== addon.dbId);
      if (next === 0) return rest;
      return rest.concat([{ dbId: addon.dbId, name: addon.name, price: addon.price, qty: next }]);
    });
  };
  const addonsTotal = addonLines.reduce((sum, line) => sum + (Number(line.price) || 0) * line.qty, 0);

  const handleRegisterSubmit = (e) => {
    e.preventDefault();
    if (!isSiteInteractive()) return;
    if (guestForm.checkOut && guestForm.checkIn && guestForm.checkOut <= guestForm.checkIn) {
      if (onToast) onToast('Check-Out must be after Check-In.');
      return;
    }
    if (!String(guestForm.checkInTime || '').trim()) {
      if (onToast) onToast('Check-In Time is required.');
      return;
    }
    if (rangeHitsBooked(guestForm.checkIn, guestForm.checkOut, bookedSet)) {
      if (onToast) onToast('That range crosses a booked date.');
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
    };

    if (typeof onReserve === 'function') {
      onReserve(room, { ...guestForm }, payment, addonLines);
    } else if (onToast) {
      onToast(`Payment received for ${room.name}.`);
    }
    onClose();
  };

  const isAvailable = status === 'Available';
  // A room out for maintenance can't be sold at all; anything else is just today's
  // status and does not stop a stay being booked for a later, free date.
  const canBookRoom = canReserve !== false && status !== 'Maintenance';
  const fieldLabel = { fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' };

  return (
    <div className="room-modal-overlay" data-hms-no-edit="1" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" onClick={e => e.stopPropagation()}>
        <div className="room-modal-img">
          <img src={roomCardImg(room)} alt={room.name} />
          <button type="button" className="room-modal-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div style={{ padding: '1.5rem 1.5rem 1.75rem' }}>
          {step === 'details' && (
            <>
              <p style={{ color: 'var(--warm)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
                {room.label || room.category || 'Room'}
              </p>
              <h2 className="font-display" style={{ fontSize: '1.65rem', marginBottom: '1.25rem' }}>{room.name}</h2>

              <div style={{ display: 'grid', gap: '1rem' }}>
                <div>
                  <p style={{ fontSize: '0.68rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.4rem' }}>Price</p>
                  <p style={{ color: 'var(--accent)', fontFamily: 'Cormorant Garamond, serif', fontSize: '1.25rem', margin: 0 }}>
                    {formatPeso(room.price)}
                  </p>
                </div>
                <div>
                  <p style={{ fontSize: '0.68rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.4rem' }}>Description</p>
                  <p style={{ color: 'var(--fg-muted)', fontSize: '0.88rem', lineHeight: 1.6, margin: 0 }}>{room.desc}</p>
                </div>
                <div>
                  <p style={{ fontSize: '0.68rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.5rem' }}>Availability</p>
                  <RoomAvailabilityCalendar
                    ranges={room.bookedRanges}
                    checkIn={guestForm.checkIn}
                    checkOut={guestForm.checkOut}
                    onPick={pickDate}
                  />
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
                {canBookRoom ? (
                  <>
                    <button type="button" className="btn-primary" style={{ width: '100%', justifyContent: 'center' }}
                      onClick={() => setStep('register')}>
                      Reserve Now <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
                    </button>
                    {!isAvailable && (
                      <p style={{ textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.78rem', margin: '0.6rem 0 0' }}>
                        Currently {status.toLowerCase()} — you can still book a later, open date.
                      </p>
                    )}
                  </>
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
                style={{ background: 'none', border: 'none', color: 'var(--fg-muted)', cursor: 'pointer', fontSize: '0.78rem', padding: 0, marginBottom: '0.85rem', fontFamily: 'DM Sans, sans-serif' }}>
                <i className="fa-solid fa-arrow-left" style={{ marginRight: 6, fontSize: '0.7rem' }}></i> Back
              </button>
              <p style={{ color: 'var(--warm)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
                Reservation
              </p>
              <h2 className="font-display" style={{ fontSize: '1.55rem', marginBottom: '0.35rem' }}>Register Guest</h2>
              <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', marginBottom: '1.25rem' }}>
                Completing reservation for <strong style={{ color: 'var(--fg)' }}>{room.name}</strong>
              </p>

              <div style={{ marginBottom: '1.25rem' }}>
                <RoomAvailabilityCalendar
                  ranges={room.bookedRanges}
                  checkIn={guestForm.checkIn}
                  checkOut={guestForm.checkOut}
                  onPick={pickDate}
                />
              </div>

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
                        onChange={e => updateGuest('checkIn', e.target.value)} required />
                    </div>
                    <div>
                      <label style={fieldLabel}>Check-In Time</label>
                      <input type="time" className="booking-input" value={guestForm.checkInTime}
                        onChange={e => updateGuest('checkInTime', e.target.value)} required />
                    </div>
                  </div>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.85rem' }}>
                    <div>
                      <label style={fieldLabel}>Check-Out</label>
                      <input type="date" className="booking-input" value={guestForm.checkOut} min={minCheckOut}
                        onChange={e => updateGuest('checkOut', e.target.value)} required />
                    </div>
                    <div>
                      <label style={fieldLabel}>Check-out Time</label>
                      {/* Standard checkout time only — there is no check-out time column, and
                          billing already assumes checkout falls at the check-in clock time. */}
                      <div className="booking-input" style={{ display: 'flex', alignItems: 'center', color: 'var(--fg-muted)' }}>
                        12:00 PM
                      </div>
                    </div>
                  </div>
                </div>

                {/* Housekeeping's add-ons. Inline rather than a second overlay — the modal
                    already scrolls, and a nested dialog over a dialog reads badly. */}
                <button type="button" onClick={() => setShowAddons(v => !v)}
                  style={{ background: 'none', border: 'none', color: 'var(--accent)', cursor: 'pointer', fontSize: '0.75rem', padding: '0.85rem 0 0', fontFamily: 'DM Sans, sans-serif' }}>
                  <i className={'fa-solid ' + (showAddons ? 'fa-minus' : 'fa-plus')} style={{ fontSize: '0.65rem', marginRight: 5 }}></i>
                  Add-ons{addonLines.length > 0 ? ` (${addonLines.length})` : ''}
                </button>

                {showAddons && (
                  <div style={{ marginTop: '0.6rem', border: '1px solid var(--border)', borderRadius: 10, padding: '0.85rem' }}>
                    {addonList.length === 0 ? (
                      <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.78rem' }}>
                        Housekeeping has not added any add-ons yet.
                      </p>
                    ) : addonList.map(addon => (
                      <div key={addon.id} style={{ display: 'flex', alignItems: 'center', gap: '0.7rem', padding: '0.5rem 0', borderBottom: '1px solid var(--border)' }}>
                        <img src={addon.img} alt={addon.name}
                          style={{ width: 44, height: 34, objectFit: 'cover', borderRadius: 5, display: 'block', flexShrink: 0 }} />
                        <div style={{ flex: 1, minWidth: 0 }}>
                          <p style={{ margin: 0, fontSize: '0.82rem', color: 'var(--fg)' }}>{addon.name}</p>
                          <p style={{ margin: 0, fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                            {formatPeso(addon.price)} · {addon.available} available
                          </p>
                        </div>
                        {addon.available > 0 ? (
                          <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', flexShrink: 0 }}>
                            <button type="button" onClick={() => stepAddon(addon, -1)} disabled={addonQty(addon.dbId) === 0}
                              style={addonStepBtn(addonQty(addon.dbId) === 0)}>&minus;</button>
                            <span style={{ minWidth: 16, textAlign: 'center', fontSize: '0.82rem', color: 'var(--fg)' }}>
                              {addonQty(addon.dbId)}
                            </span>
                            <button type="button" onClick={() => stepAddon(addon, 1)} disabled={addonQty(addon.dbId) >= addon.available}
                              style={addonStepBtn(addonQty(addon.dbId) >= addon.available)}>+</button>
                          </div>
                        ) : (
                          <span style={{ fontSize: '0.72rem', color: '#b91c1c', flexShrink: 0 }}>Out of stock</span>
                        )}
                      </div>
                    ))}

                    {addonsTotal > 0 && (
                      <p style={{ margin: '0.7rem 0 0', fontSize: '0.75rem', color: 'var(--fg-muted)' }}>
                        Add-ons: <strong style={{ color: 'var(--fg)' }}>{formatPeso(addonsTotal)}</strong>
                        {' '}&mdash; settled with the final bill at check-out, like room service.
                      </p>
                    )}
                  </div>
                )}

                <button type="submit" className="btn-primary" style={{ width: '100%', justifyContent: 'center', marginTop: '1.35rem' }}>
                  Proceed Payment <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
                </button>
              </form>
            </>
          )}

          {step === 'payment' && (
            <>
              <button type="button" onClick={() => setStep('register')}
                style={{ background: 'none', border: 'none', color: 'var(--fg-muted)', cursor: 'pointer', fontSize: '0.78rem', padding: 0, marginBottom: '0.85rem', fontFamily: 'DM Sans, sans-serif' }}>
                <i className="fa-solid fa-arrow-left" style={{ marginRight: 6, fontSize: '0.7rem' }}></i> Back
              </button>
              <p style={{ color: 'var(--warm)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
                Payment
              </p>
              <h2 className="font-display" style={{ fontSize: '1.55rem', marginBottom: '0.35rem' }}>Process Payment</h2>
              <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', marginBottom: '1rem' }}>
                Guest <strong style={{ color: 'var(--fg)' }}>{guestForm.fullName}</strong> · {room.name}
              </p>

              <div style={{ background: 'var(--bg-alt)', border: '1px solid var(--border)', borderRadius: 10, padding: '0.9rem 1rem', marginBottom: '1.15rem' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem', marginBottom: '0.35rem' }}>
                  <span style={{ color: 'var(--fg-muted)', fontSize: '0.8rem' }}>
                    {blocks} × {BLOCK_HOURS} hrs × {formatPeso(room.price)}
                  </span>
                  <strong style={{ color: 'var(--accent)', fontFamily: 'Cormorant Garamond, serif', fontSize: '1.15rem' }}>{formatPeso(totalDue)}</strong>
                </div>
                <p style={{ margin: 0, fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                  {formatCheckIn(guestForm.checkIn, guestForm.checkInTime)} — {guestForm.checkOut}
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
                    <p style={{ margin: '0.4rem 0 0', fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                      Remaining balance: {formatPeso(Math.max(0, totalDue - (
                        paymentForm.type === 'full' ? totalDue : (parseFloat(paymentForm.amount) || 0)
                      )))}
                    </p>
                  </div>

                  <div>
                    <label style={fieldLabel}>Payment Method</label>
                    <select className="booking-input" value={paymentForm.method} onChange={e => {
                      const method = e.target.value;
                      updatePayment('method', method);
                      // Drop a reference typed under GCash before switching to Cash;
                      // the field is hidden then, so it must not submit unseen.
                      if (method === 'Cash') updatePayment('reference', '');
                    }} required>
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

                  {paymentForm.method !== 'Cash' && (
                    <div>
                      <label style={fieldLabel}>Reference / Transaction ID</label>
                      <input type="text" className="booking-input" placeholder="Receipt no., card last 4, or ref #" value={paymentForm.reference}
                        onChange={e => updatePayment('reference', e.target.value)} required />
                    </div>
                  )}

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

function MenuDetailModal({ item, onClose, canOrder, onAddToCart, onToast }) {
  if (!item) return null;
  const [qty, setQty] = useState(1);

  useEffect(() => { setQty(1); }, [item.id]);

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const inStock = item.stock == null || item.stock > 0;
  const maxQty = item.stock != null ? item.stock : 99;
  const fieldLabel = { fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' };
  const qtyStepBtn = {
    width: 32, height: 32, borderRadius: 8, border: '1px solid var(--border)',
    background: 'var(--bg-alt)', color: 'var(--fg)', cursor: 'pointer',
    display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: '1rem',
  };

  const handleAdd = () => {
    const clean = Math.max(1, Math.min(maxQty, parseInt(qty, 10) || 1));
    if (typeof onAddToCart === 'function') onAddToCart(item, clean);
    if (onToast) onToast(`${item.name} added to the order.`);
    onClose();
  };

  return (
    <div className="room-modal-overlay" data-hms-no-edit="1" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" onClick={e => e.stopPropagation()}>
        <div className="room-modal-img">
          <img src={menuFoodImg(item)} alt={item.name} />
          <button type="button" className="room-modal-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div style={{ padding: '1.5rem 1.5rem 1.75rem' }}>
          <p style={{ color: 'var(--warm)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
            {normalizeMenuCategory(item.category)}
          </p>
          <h2 className="font-display" style={{ fontSize: '1.65rem', marginBottom: '1.25rem' }}>{item.name}</h2>

          <div style={{ display: 'grid', gap: '1rem' }}>
            <div>
              <p style={{ fontSize: '0.68rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.4rem' }}>Price</p>
              <p style={{ color: 'var(--accent)', fontFamily: 'Cormorant Garamond, serif', fontSize: '1.25rem', margin: 0 }}>
                {typeof item.price === 'number' ? formatPeso(item.price) : (item.price || '—')}
              </p>
            </div>
            <div>
              <p style={{ fontSize: '0.68rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.4rem' }}>Description</p>
              <p style={{ color: 'var(--fg-muted)', fontSize: '0.88rem', lineHeight: 1.6, margin: 0 }}>{item.sub || 'No description yet.'}</p>
            </div>
          </div>

          <div style={{ marginTop: '1.5rem' }}>
            {canOrder && inStock ? (
              <>
                <div style={{ marginBottom: '1rem' }}>
                  <label style={fieldLabel}>Quantity</label>
                  <div style={{ display: 'inline-flex', alignItems: 'center', gap: '0.6rem' }}>
                    <button type="button" style={qtyStepBtn} aria-label="Decrease quantity"
                      disabled={qty <= 1}
                      onClick={() => setQty(q => Math.max(1, q - 1))}>−</button>
                    <span style={{ color: 'var(--fg)', minWidth: 24, textAlign: 'center', fontSize: '0.95rem', fontVariantNumeric: 'tabular-nums' }}>{qty}</span>
                    <button type="button" style={qtyStepBtn} aria-label="Increase quantity"
                      disabled={qty >= maxQty}
                      onClick={() => setQty(q => Math.min(maxQty, q + 1))}>+</button>
                  </div>
                </div>
                <button type="button" className="btn-primary" style={{ width: '100%', justifyContent: 'center' }} onClick={handleAdd}>
                  Add to Order <i className="fa-solid fa-cart-plus" style={{ fontSize: '0.7rem' }}></i>
                </button>
              </>
            ) : !inStock ? (
              <p style={{ textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.82rem', margin: 0 }}>
                Currently out of stock.
              </p>
            ) : null}
          </div>
        </div>
      </div>
    </div>
  );
}

/*
 * Everything added from the menu lands here first. One guest and one room are
 * chosen for the whole cart, then the entire cart goes out as a single order with
 * one line per dish.
 */
function CartReviewModal({ open, onClose, cart, onUpdateQty, onRemove, rooms, onPlaceOrder, onToast }) {
  const [roomId, setRoomId] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const checkedInRooms = checkedInRoomsFor(rooms);
  const selectedRoom = checkedInRooms.find(r => r.id === roomId) || null;

  useEffect(() => { if (open) setRoomId(''); }, [open]);

  useEffect(() => {
    if (!open) return;
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [open, onClose]);

  if (!open) return null;

  const total = cart.reduce((sum, line) => sum + line.price * line.qty, 0);
  const fieldLabel = { fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' };
  const stepBtn = {
    width: 26, height: 26, borderRadius: 6, border: '1px solid var(--border)',
    background: 'var(--bg-alt)', color: 'var(--fg)', cursor: 'pointer',
    display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: '0.9rem',
  };

  const submit = (e) => {
    e.preventDefault();
    if (!isSiteInteractive()) return;
    if (!cart.length) { if (onToast) onToast('Add at least one item to the order.'); return; }
    if (!selectedRoom) { if (onToast) onToast('Select which checked-in guest this order is for.'); return; }
    setSubmitting(true);
    Promise.resolve(onPlaceOrder(cart, {
      guestName: selectedRoom.reservation.fullName || 'Guest',
      roomNumber: selectedRoom.name,
    }))
      .then(() => onClose())
      .catch(() => { /* toast already shown by caller; keep the review open to retry */ })
      .finally(() => setSubmitting(false));
  };

  return (
    <div className="room-modal-overlay" data-hms-no-edit="1" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" onClick={e => e.stopPropagation()}>
        <div style={{ padding: '1.5rem 1.5rem 1.75rem' }}>
          <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', marginBottom: '1.1rem' }}>
            <div>
              <p style={{ color: 'var(--warm)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>Room Service</p>
              <h2 className="font-display" style={{ fontSize: '1.55rem', margin: 0 }}>Review Order</h2>
            </div>
            <button type="button" onClick={onClose} aria-label="Close"
              style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'var(--bg-alt)', color: 'var(--fg)', cursor: 'pointer', flexShrink: 0 }}>
              <i className="fa-solid fa-xmark"></i>
            </button>
          </div>

          {cart.length === 0 ? (
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.85rem' }}>No items yet — add dishes from the menu first.</p>
          ) : (
            <>
              <div style={{ display: 'grid', gap: '0.6rem', marginBottom: '1.1rem', maxHeight: 260, overflowY: 'auto' }}>
                {cart.map(line => (
                  <div key={line.dbId} style={{ display: 'flex', alignItems: 'center', gap: '0.6rem', border: '1px solid var(--border)', borderRadius: 8, padding: '0.55rem 0.7rem' }}>
                    <div style={{ flex: 1, minWidth: 0 }}>
                      <p style={{ margin: 0, color: 'var(--fg)', fontWeight: 600, fontSize: '0.85rem', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{line.name}</p>
                      <p style={{ margin: 0, color: 'var(--accent)', fontSize: '0.76rem' }}>{formatPeso(line.price)}</p>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.35rem' }}>
                      <button type="button" onClick={() => onUpdateQty(line.dbId, line.qty - 1)} style={stepBtn}>−</button>
                      <span style={{ color: 'var(--fg)', minWidth: 18, textAlign: 'center', fontSize: '0.85rem' }}>{line.qty}</span>
                      <button type="button" onClick={() => onUpdateQty(line.dbId, line.qty + 1)}
                        disabled={line.stock != null && line.qty >= line.stock} style={stepBtn}>+</button>
                    </div>
                    <button type="button" onClick={() => onRemove(line.dbId)} title="Remove"
                      style={{ background: 'none', border: 'none', color: '#e11d48', cursor: 'pointer', fontSize: '0.95rem', padding: '0.2rem' }}>
                      <i className="fa-solid fa-trash-can"></i>
                    </button>
                  </div>
                ))}
              </div>

              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: '1.1rem' }}>
                <span style={{ color: 'var(--fg-muted)', fontSize: '0.82rem' }}>Total</span>
                <span style={{ color: 'var(--accent)', fontWeight: 700, fontSize: '1.1rem', fontFamily: 'Cormorant Garamond, serif' }}>{formatPeso(total)}</span>
              </div>

              {checkedInRooms.length === 0 ? (
                <p style={{ color: 'var(--fg-muted)', fontSize: '0.85rem', lineHeight: 1.6 }}>
                  No guests are checked in right now. Room Management can check a guest in from Guest Details before a room-service order can be placed.
                </p>
              ) : (
                <form onSubmit={submit}>
                  <div style={{ display: 'grid', gap: '0.85rem' }}>
                    <div>
                      <label style={fieldLabel}>Guest</label>
                      <select className="booking-input" value={roomId}
                        onChange={e => setRoomId(e.target.value)} required>
                        <option value="">Select a checked-in guest…</option>
                        {checkedInRooms.map(r => (
                          <option key={r.id} value={r.id}>{r.reservation.fullName || 'Guest'} — {r.name}</option>
                        ))}
                      </select>
                    </div>
                    <div>
                      <label style={fieldLabel}>Room</label>
                      <div className="booking-input" style={{ opacity: 0.75, cursor: 'default' }}>
                        {selectedRoom ? selectedRoom.name : '—'}
                      </div>
                    </div>
                  </div>
                  <button type="submit" className="btn-primary" style={{ width: '100%', justifyContent: 'center', marginTop: '1.35rem' }} disabled={!selectedRoom || submitting}>
                    {submitting ? 'Placing…' : 'Place Order'} <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
                  </button>
                </form>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
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

function MobileMenu({ open, onClose, onNav, links, cardImages, onToast }) {
  const items = [
    ...(links || []),
    { key: 'booking', label: 'Book Now' },
  ];
  // Passed only so the menu re-renders when the shared logo changes.
  void cardImages;
  return (
    <div className={`mobile-menu${open ? ' open' : ''}`}>
      <BrandLogo size={54} />
      {items.map(i => <button key={i.id || i.key} onClick={() => { onNav(i.key); onClose(); }}>{i.label}</button>)}
      <AuthNav onToast={onToast} compact />
    </div>
  );
}

/* ═══════════════ CUSTOMER SIGN IN / SIGN UP ═══════════════ */

/*
 * The hotel site's own visitor session — a customer browsing and booking — which
 * is not the HMS login the student signed in with to open the builder.
 *
 * window.HMSHotelAuth (public/js/hms-hotel-auth.js) owns the session and fires
 * hms-hotel-auth whenever it changes, so every copy of this on the page stays in
 * step without any of them polling.
 */
function useHotelAuth() {
  const [auth, setAuth] = useState(() => window.__HMS_HOTEL_AUTH__ || { authenticated: false });

  useEffect(() => {
    const onAuth = (e) => setAuth((e && e.detail && e.detail.auth) || { authenticated: false });
    window.addEventListener('hms-hotel-auth', onAuth);
    // The bridge resolves the session shortly after load, which can land before
    // this mounts — so read whatever is already there rather than wait for an event.
    setAuth(window.__HMS_HOTEL_AUTH__ || { authenticated: false });
    return () => window.removeEventListener('hms-hotel-auth', onAuth);
  }, []);

  return auth;
}

function AuthModal({ mode, onMode, onClose, onToast }) {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const signingUp = mode === 'signup';

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape' && !busy) onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose, busy]);

  const submit = (e) => {
    e.preventDefault();
    const api = window.HMSHotelAuth;
    if (!api) { setError('The sign-in service is not available on this page.'); return; }
    if (signingUp && !name.trim()) { setError('Enter your name.'); return; }
    if (!email.trim()) { setError('Enter your email address.'); return; }
    if (!password) { setError('Enter your password.'); return; }

    setError('');
    setBusy(true);
    Promise.resolve(
      signingUp
        ? api.customerSignup(name.trim(), email.trim(), password)
        : api.customerLogin(email.trim(), password)
    )
      .then(() => {
        if (onToast) onToast(signingUp ? 'Welcome — your account is ready.' : 'Signed in.');
        onClose();
      })
      .catch(err => setError((err && err.message) || 'That did not work. Check your details and try again.'))
      .finally(() => setBusy(false));
  };

  return (
    <div className="auth-overlay" data-hms-no-edit="1" onClick={() => { if (!busy) onClose(); }} role="dialog" aria-modal="true">
      <div className="auth-card" onClick={e => e.stopPropagation()}>
        <div className="auth-card-head">
          <div>
            <p className="auth-eyebrow">{signingUp ? 'Join us' : 'Welcome back'}</p>
            <h2 className="font-display" style={{ fontSize: '1.5rem', margin: 0, color: 'var(--fg)' }}>
              {signingUp ? 'Create an account' : 'Sign in'}
            </h2>
          </div>
          <button type="button" className="auth-close" onClick={onClose} disabled={busy} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form onSubmit={submit} noValidate>
          {signingUp && (
            <label className="auth-field">
              <span>Full name</span>
              <input className="auth-input" type="text" value={name} autoComplete="name"
                onChange={e => setName(e.target.value)} />
            </label>
          )}
          <label className="auth-field">
            <span>Email</span>
            <input className="auth-input" type="email" value={email} autoComplete="email"
              onChange={e => setEmail(e.target.value)} />
          </label>
          <label className="auth-field">
            <span>Password</span>
            <input className="auth-input" type="password" value={password}
              autoComplete={signingUp ? 'new-password' : 'current-password'}
              onChange={e => setPassword(e.target.value)} />
          </label>

          {error && <p className="auth-error">{error}</p>}

          <button type="submit" className="nav-auth-btn is-solid" disabled={busy}
            style={{ width: '100%', padding: '0.75rem', fontSize: '0.74rem' }}>
            {busy ? 'Please wait…' : (signingUp ? 'Create account' : 'Sign in')}
          </button>
        </form>

        <p className="auth-swap">
          {signingUp ? 'Already have an account? ' : 'New here? '}
          <button type="button" onClick={() => { setError(''); onMode(signingUp ? 'signin' : 'signup'); }}>
            {signingUp ? 'Sign in' : 'Create one'}
          </button>
        </p>
      </div>
    </div>
  );
}

/* Sits at the right-hand end of the navigation. `compact` stacks it for the
   mobile menu, where a row of buttons has nowhere to go. */
function AuthNav({ onToast, compact }) {
  const auth = useHotelAuth();
  const [mode, setMode] = useState(null);
  const [busy, setBusy] = useState(false);

  // Inert while the student is redesigning the page — same rule the rest of the
  // site's controls follow, so a click in Design mode edits rather than signs in.
  const open = (next) => { if (isSiteInteractive()) setMode(next); };

  const signOut = () => {
    const api = window.HMSHotelAuth;
    if (!api || !isSiteInteractive()) return;
    setBusy(true);
    Promise.resolve(api.logout())
      .then(() => { if (onToast) onToast('Signed out.'); })
      .catch(() => { if (onToast) onToast('Could not sign out.'); })
      .finally(() => setBusy(false));
  };

  return (
    <div className={`nav-auth${compact ? ' is-compact' : ''}`} data-hms-no-edit="1">
      {auth && auth.authenticated ? (
        <>
          <span className="nav-auth-who" title={auth.email || ''}>
            <i className="fa-regular fa-circle-user" style={{ color: 'var(--accent)' }}></i>
            <b>{auth.name || 'Guest'}</b>
          </span>
          <button type="button" className="nav-auth-btn is-ghost" onClick={signOut} disabled={busy}>
            {busy ? 'Signing out…' : 'Sign out'}
          </button>
        </>
      ) : (
        <>
          <button type="button" className="nav-auth-btn is-ghost" onClick={() => open('signin')}>Sign in</button>
          <button type="button" className="nav-auth-btn is-solid" onClick={() => open('signup')}>Sign up</button>
        </>
      )}

      {mode && (
        <AuthModal mode={mode} onMode={setMode} onClose={() => setMode(null)} onToast={onToast} />
      )}
    </div>
  );
}

function NavBar({ currentPage, onNav, onToggle, mobileOpen, links, canEditNav, onAddNav, onEditNav, onRemoveNav, cardImages, onToast }) {
  // Passed only so the navigation re-renders when the shared logo changes.
  void cardImages;
  const canEditThisLogo = !!(window.HMSSiteContent && window.HMSSiteContent.canEditLogo && window.HMSSiteContent.canEditLogo());
  const PAGE_OPTIONS = [
    { key: 'home', label: 'Home' },
    { key: 'rooms', label: 'Rooms' },
    { key: 'restaurant', label: 'Restaurant' },
    { key: 'amenities', label: 'Amenities' },
    { key: 'experience', label: 'Experience' },
    { key: 'booking', label: 'Book Now' },
  ];

  /* Asks which page the link opens as a numbered menu. Typing a raw key by hand
     used to be accepted unvalidated, so a typo produced a link that went nowhere.
     Returns null when the student cancels or picks something that isn't a page. */
  const askPageKey = (currentKey) => {
    const menu = PAGE_OPTIONS.map((p, i) => (i + 1) + ') ' + p.label).join('\n');
    const currentIndex = PAGE_OPTIONS.findIndex(p => p.key === currentKey);
    const fallback = String(currentIndex >= 0 ? currentIndex + 1 : 1);
    const answer = hmsPrompt('Which page should this link open?\n\n' + menu + '\n\nType a number:', fallback);
    if (answer == null) return null;
    const typed = String(answer).trim().toLowerCase();
    if (!typed) return null;
    const byNumber = PAGE_OPTIONS[parseInt(typed, 10) - 1];
    if (byNumber) return byNumber.key;
    const byName = PAGE_OPTIONS.find(p => p.key === typed || p.label.toLowerCase() === typed);
    if (byName) return byName.key;
    if (onToast) onToast('"' + answer + '" is not a page — pick a number from the list');
    return null;
  };

  const handleAdd = (e) => {
    e.preventDefault();
    e.stopPropagation();
    const label = hmsPrompt('Name for the new navigation link', 'New Page');
    if (label == null || !label.trim()) return;
    const key = askPageKey('home');
    if (!key) return;
    onAddNav({ label: label.trim(), key });
    if (onToast) onToast('Navigation link added');
  };
  const handleEdit = (e, link) => {
    e.preventDefault();
    e.stopPropagation();
    const label = hmsPrompt('Name for this navigation link', link.label);
    if (label == null || !label.trim()) return;
    const key = askPageKey(link.key);
    if (!key) return;
    onEditNav(link.id, { label: label.trim(), key });
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
          <button onClick={() => onNav('home')} style={{ background: 'none', border: 'none', cursor: 'pointer', padding: 0, display: 'flex', alignItems: 'center', gap: '0.6rem' }}>
            <BrandLogo size={34} />
            <span data-hms-text="1" data-hms-brand-name="1" style={{ color: 'var(--accent)', fontSize: '1.05rem', fontWeight: 700, letterSpacing: '0.18em', textTransform: 'uppercase' }}>SPC HOTEL</span>
          </button>
          {canEditThisLogo && <ChangeLogoButton onToast={onToast} />}
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
          <AuthNav onToast={onToast} />
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
function HeroSlider({ slides, canEdit }) {
  const list = slides && slides.length ? slides : DEFAULT_HERO_SLIDES;
  const [active, setActive] = useState(0);

  useEffect(() => {
    if (list.length < 2) return undefined;
    const id = setInterval(() => setActive((i) => (i + 1) % list.length), 6000);
    return () => clearInterval(id);
  }, [list.length]);

  const handleChangeImage = () => {
    if (!window.HMSSiteContent) return;
    window.HMSSiteContent.pickImageFile((url) => {
      if (!url) return;
      window.HMSSiteContent.updateHeroSlide(list[active].id, { img: url }, DEFAULT_HERO_SLIDES);
    });
  };

  return (
    <>
      {list.map((slide, i) => (
        <img
          key={slide.id}
          className={`hero-slide-img${i === active ? ' is-active' : ''}`}
          src={slide.img}
          alt="SPC Hotel"
        />
      ))}
      <div className="hero-dots" data-hms-no-edit="1">
        {list.map((slide, i) => (
          <button
            key={slide.id}
            type="button"
            className={`hero-dot${i === active ? ' is-active' : ''}`}
            aria-label={'Slide ' + (i + 1)}
            onClick={() => setActive(i)}
          ></button>
        ))}
      </div>
      {canEdit && (
        <button type="button" className="hero-edit-btn" data-hms-no-edit="1" onClick={handleChangeImage}>
          <i className="fa-solid fa-image" style={{ fontSize: 10 }}></i> Change image
        </button>
      )}
    </>
  );
}

function HomePage({ onNav, onToast, rooms, menus, canEditRooms, onAddRoom, onEditRoom, onRemoveRoom, heroSlides, canEditHeroSlides }) {
  const roomList = rooms && rooms.length ? rooms : [];
  const menuList = menus || [];

  const handleAddRoom = (e) => {
    if (e && e.stopPropagation) e.stopPropagation();
    // No iframe prompt — add immediately so Design mode always works.
    if (onAddRoom) {
      onAddRoom({
        name: 'New Suite',
        label: 'Classic',
        category: 'Classic',
        status: 'Available',
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
    if (name == null || !String(name).trim()) return;
    const priceRaw = hmsPrompt('Price per 12 hrs', String(room.price || 200));
    if (priceRaw == null) return;
    const price = Math.max(1, parseInt(priceRaw || String(room.price || 200), 10) || room.price || 200);
    const desc = hmsPrompt('Description', room.desc || '');
    if (desc == null) return;
    if (onEditRoom) onEditRoom(room.id, { name: String(name).trim(), price, desc: String(desc).trim() });
  };

  return (
    <>
      <div className="hero-split" data-hms-section="hero">
        <div className="hero-img" data-hms-bg-target="1">
          <HeroSlider slides={heroSlides} canEdit={canEditHeroSlides} />
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
                <img src={roomCardImg(room)} alt={room.name} loading="lazy" />
              </div>
              <div style={{ padding: '1rem 1.1rem 1.2rem' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', gap: 8 }}>
                  <h3 style={{ fontWeight: 600, fontSize: '1rem', margin: 0 }}>{room.name}</h3>
                  <span style={{ color: 'var(--accent)', fontWeight: 700 }}>{formatPeso(room.price)}</span>
                </div>
                <p style={{ color: 'var(--fg-muted)', fontSize: '0.78rem', margin: '0.45rem 0 0', lineHeight: 1.5 }}>
                  {(room.desc || '').slice(0, 80)}{(room.desc || '').length > 80 ? '…' : ''}
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
          {menuList.slice(0, 6).map(item => (
            <div key={item.id || item.name} className="menu-item" style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
              <div>
                <span style={{ fontWeight: 600, fontSize: '0.9rem' }}>{item.name}</span>
                <p style={{ fontSize: '0.72rem', color: 'var(--fg-muted)', margin: '0.3rem 0 0' }}>{item.sub}</p>
                {item.category ? <p style={{ fontSize: '0.65rem', color: 'var(--warm)', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', margin: '0.4rem 0 0' }}>{normalizeMenuCategory(item.category)}</p> : null}
              </div>
              <span style={{ color: 'var(--accent)', fontWeight: 600, whiteSpace: 'nowrap' }}>{typeof item.price === 'number' ? formatPeso(item.price) : (item.price || '—')}</span>
            </div>
          ))}
        </div>
      </section>
    </>
  );
}


/* â•â•â•â•â•â•â• ROOMS â•â•â•â•â•â•â• */
function RoomCard({ room, onSelect, canEdit, onEdit, onRemove, onChangeImage }) {
  const category = normalizeRoomCategory(room.category || room.label);
  const isLuxe = category === 'Premium' || category === 'Family';
  const amenities = room.amenities || [];
  return (
    <div className="room-card" style={{ position: 'relative' }} onClick={() => onSelect(room.id)}>
      {canEdit && (
        <div style={{ position: 'absolute', top: 10, right: 10, zIndex: 3, display: 'flex', gap: 6 }} data-hms-no-edit="1" onClick={e => e.stopPropagation()}>
          <button type="button" title="Change image" onClick={() => onChangeImage && onChangeImage(room)} style={toolBtnStyle('image')}><i className="fa-solid fa-image" style={{fontSize:11}}></i></button>
          <button type="button" title="Edit room" onClick={() => onEdit(room)} style={toolBtnStyle('edit')}><i className="fa-solid fa-pen" style={{fontSize:10}}></i></button>
          <button type="button" title="Remove room" onClick={() => onRemove(room.id)} style={toolBtnStyle('danger')}><i className="fa-solid fa-xmark" style={{fontSize:12}}></i></button>
        </div>
      )}
      <div className="room-card-img">
        <img src={roomCardImg(room)} alt={room.name} loading="lazy" />
        <span className={`room-status-badge ${roomStatusClass(room.status)}`}>{normalizeRoomStatus(room.status)}</span>
      </div>
      <div style={{ padding: '1.25rem' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.5rem' }}>
          <span className={`room-tag${isLuxe ? ' room-tag-luxe' : ''}`}>{category}</span>
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
            From <strong className="font-display" style={{ color: isLuxe ? 'var(--warm)' : 'var(--accent)', fontSize: '1.2rem', fontWeight: 700 }}>{formatPeso(room.price)}</strong><span style={{ fontSize: '0.72rem' }}> / 12 hrs</span>
          </span>
          <button className="btn-sm" onClick={e => { e.stopPropagation(); onSelect(room.id); }}>View</button>
        </div>
      </div>
    </div>
  );
}

function RoomsPage({ onNav, onToast, rooms, addons, canEditRooms, canManageRooms, canReserveRooms, onAddRoom, onEditRoom, onRemoveRoom, onCreateBooking, onRefreshAddons, onOpenRoomManagement }) {
  // Front Desk lands on "All" so every room Room Management created is visible on
  // one screen; the category tabs stay for narrowing it down.
  const list = rooms && rooms.length ? rooms : [];
  const [tab, setTab] = useState('All');
  const [selectedRoomId, setSelectedRoomId] = useState(null);
  const filtered = tab === 'All' ? list : list.filter(r => normalizeRoomCategory(r.category || r.label) === tab);
  const selectedRoom = list.find(r => r.id === selectedRoomId) || null;
  const showRoomManagement = !!canManageRooms;

  const handleAdd = (e) => {
    if (e && e.stopPropagation) e.stopPropagation();
    // "All" isn't a real category — a card added while on that tab still needs one.
    const category = tab === 'All' ? 'Classic' : tab;
    onAddRoom({
      name: 'New Suite',
      label: category,
      category,
      status: 'Available',
      price: 250,
      desc: 'Add a short description for this room.',
      img: 'https://picsum.photos/seed/room' + Date.now() + '/800/600.jpg',
      amenities: [{ i: 'fa-bed', t: 'Bed' }, { i: 'fa-wifi', t: 'WiFi' }],
    });
    onToast('Room card added — click the pencil to edit');
  };

  const handleEdit = (room) => {
    const name = hmsPrompt('Room name', room.name);
    if (name == null || !String(name).trim()) return;
    const priceRaw = hmsPrompt('Price per 12 hrs', String(room.price || 200));
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

  // The stay is written to hotel_bookings, not onto the room — the server records the
  // booking itself and hands back both rows. Everything below only runs once that POST
  // actually succeeds; onCreateBooking's own .catch already toasts the error, so a
  // failed booking (room already taken, bad dates, ...) no longer looks like a success
  // and silently drops the guest.
  const handleReserve = (room, guest, payment, addonLines) => {
    if (!onCreateBooking) return;
    onCreateBooking(room, guest, payment, addonLines).then(() => {
      // What is free changed the moment those add-ons went out with the guest.
      if (typeof onRefreshAddons === 'function') onRefreshAddons();
      // The toast and the cross-module notification are both advisory, and the
      // notification writes customizations + localStorage. The booking is already saved
      // by the time either runs, so they are fenced off together: a failure in here must
      // not fall through to the catch below and eat the redirect that follows.
      try {
        if (onToast) {
          const paid = payment ? ` · ${payment.type} ${formatPeso(payment.amountPaid)} via ${payment.method}` : '';
          onToast(`${guest.fullName} reserved ${room.name}${paid}. Room Management checks the guest in on arrival.`);
        }
        // Lets Room Management auto-open Guest Details when the reservation lands.
        if (window.HMSSiteContent && typeof window.HMSSiteContent.recordReservationNotification === 'function') {
          window.HMSSiteContent.recordReservationNotification({
            roomId: room.id,
            roomName: room.name,
            guestName: guest.fullName,
            checkIn: guest.checkIn,
            checkOut: guest.checkOut,
            fullReservation: Object.assign({}, guest, {
              payment: payment || null,
              reservedAt: new Date().toISOString(),
              arrivalStatus: 'Booked',
              arrivedAt: null,
            }),
          });
        }
      } catch (e) { /* the guest list is read from the database, not from this */ }

      // Send the registering staff straight to whichever screen they'd use next: Room
      // Management owns Guest Details, everyone else (Front Desk included) works the
      // booking from Verify Guest.
      if (showRoomManagement && typeof onOpenRoomManagement === 'function') {
        onOpenRoomManagement('guest-details');
      } else {
        hmsNavigateTop(window.HMS_VERIFY_GUEST_URL);
      }
    }).catch(() => { /* onCreateBooking already toasted the failure */ });
  };

  return (
    <>
      <div className="page-header">
        <span className="section-num">01 — Accommodations</span>
        <h1 className="font-display">Our Rooms & Suites</h1>
        <p>Each room is a sanctuary of design, blending modern luxury with artisanal craftsmanship and sweeping views.</p>
      </div>
      <TabBar tabs={ROOM_PAGE_TABS} active={tab} onChange={setTab} items={list} />
      <section style={{ padding: '0 1.5rem 2rem', maxWidth: 1100, margin: '0 auto' }}>
        {filtered.length === 0 && !canEditRooms ? (
          <EmptyState text="No rooms found in this category." />
        ) : (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '1.25rem' }}>
            {filtered.map(r => (
              <RoomCard key={r.id} room={r} onSelect={setSelectedRoomId} canEdit={canEditRooms}
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
                <span style={{ fontSize: 11, opacity: 0.75 }}>Added under {tab === 'All' ? 'Classic' : tab}</span>
              </button>
            )}
          </div>
        )}
      </section>
      {showRoomManagement && (
        <div style={{ textAlign: 'center', padding: '0 1.5rem 2.5rem' }}>
          <button className="btn-ghost" onClick={() => onOpenRoomManagement && onOpenRoomManagement('manage-room')}>
            Open Room Management <i className="fa-solid fa-arrow-up-right-from-square" style={{ fontSize: '0.7rem' }}></i>
          </button>
        </div>
      )}
      <Divider />
      <div style={{ textAlign: 'center', padding: '2.5rem 1.5rem 5rem' }}>
        <button className="btn-warm" onClick={() => onNav('booking')}>
          Book Your Stay <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
        </button>
      </div>
      <RoomDetailModal
        room={selectedRoom}
        addons={addons}
        onClose={() => setSelectedRoomId(null)}
        onChangeStatus={handleStatusChange}
        canEditStatus={!!canManageRooms}
        canReserve={canReserveRooms !== false}
        onReserve={handleReserve}
        onToast={onToast}
      />
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

function RestaurantPage({ onNav, onToast, menus, canManageMenus, canOrderMenu, onOrderMenu, onAddMenu, onEditMenu, onRemoveMenu, cardImages, rooms }) {
  const [tab, setTab] = useState('All');
  void cardImages;
  const filtered = tab === 'All' ? RESTAURANTS : RESTAURANTS.filter(r => r.category === tab);
  const menuList = menus || [];
  const [selectedMenuId, setSelectedMenuId] = useState(null);
  const selectedMenu = menuList.find(m => m.id === selectedMenuId) || null;
  const [menuTab, setMenuTab] = useState('Main Dishes');
  const [cart, setCart] = useState([]);
  const [cartOpen, setCartOpen] = useState(false);
  const filteredMenus = menuList.filter(item => normalizeMenuCategory(item.category) === menuTab);

  // Keyed by dbId: adding the same dish twice bumps its quantity rather than
  // creating a second line the kitchen would read as two separate requests.
  const addToCart = (item, qty) => {
    setCart(prev => {
      const existing = prev.find(l => l.dbId === item.dbId);
      if (existing) {
        const ceiling = item.stock != null ? item.stock : 99;
        return prev.map(l => (l.dbId === item.dbId ? Object.assign({}, l, { qty: Math.min(ceiling, l.qty + qty) }) : l));
      }
      return [...prev, { dbId: item.dbId, name: item.name, price: item.price, qty, stock: item.stock }];
    });
  };

  const updateCartQty = (dbId, qty) => {
    setCart(prev => prev.reduce((acc, l) => {
      if (l.dbId !== dbId) { acc.push(l); return acc; }
      const ceiling = l.stock != null ? l.stock : 99;
      const next = Math.min(ceiling, qty);
      if (next >= 1) acc.push(Object.assign({}, l, { qty: next }));
      return acc;
    }, []));
  };

  const removeFromCart = (dbId) => setCart(prev => prev.filter(l => l.dbId !== dbId));

  const placeCartOrder = (lines, details) => (
    Promise.resolve(onOrderMenu(lines, details)).then(result => { setCart([]); return result; })
  );

  const cartCount = cart.reduce((sum, l) => sum + l.qty, 0);
  const cartTotal = cart.reduce((sum, l) => sum + l.price * l.qty, 0);

  const handleAdd = () => {
    const name = hmsPrompt('Menu item name', 'New Dish');
    if (name == null || !name.trim()) return;
    const sub = hmsPrompt('Short description', 'Add a short description') || 'Add a short description';
    const priceRaw = hmsPrompt('Price', '250');
    if (priceRaw == null) return;
    const price = Math.max(1, parseInt(priceRaw, 10) || 1);
    const categoryHint = MENU_CATEGORIES.join(' / ');
    const categoryRaw = hmsPrompt('Category (' + categoryHint + ')', 'Main Dishes');
    if (categoryRaw == null) return;
    const category = normalizeMenuCategory(categoryRaw);
    if (!onAddMenu) return;
    onAddMenu({ name: name.trim(), description: sub.trim(), price, category })
      .then(() => onToast('Menu item added'));
  };

  const handleEdit = (item) => {
    const name = hmsPrompt('Menu item name', item.name);
    if (name == null || !name.trim()) return;
    const sub = hmsPrompt('Short description', item.sub || '');
    if (sub == null) return;
    const priceRaw = hmsPrompt('Price', String(item.price || 250));
    if (priceRaw == null) return;
    const price = Math.max(1, parseInt(priceRaw, 10) || item.price || 1);
    const categoryHint = MENU_CATEGORIES.join(' / ');
    const categoryRaw = hmsPrompt('Category (' + categoryHint + ')', item.category || 'Main Dishes');
    if (categoryRaw == null) return;
    const category = normalizeMenuCategory(categoryRaw);
    if (!onEditMenu) return;
    onEditMenu(item.dbId, { name: name.trim(), description: sub.trim(), price, category })
      .then(() => onToast('Menu item updated'));
  };

  const handleMenuImage = (item) => {
    pickImageFile((url) => {
      if (!url || !onEditMenu) return;
      onEditMenu(item.dbId, { image: url }).then(() => onToast('Menu image updated'));
    });
  };

  const handleRemove = (item) => {
    if (!onRemoveMenu) return;
    if (!hmsConfirm('Remove "' + item.name + '" from the menu?')) return;
    onRemoveMenu(item.dbId).then(() => onToast('Menu item removed'));
  };

  return (
    <>
      <div className="page-header">
        <span className="section-num">02 — Culinary Arts</span>
        <h1 className="font-display">Restaurant & Bar</h1>
        <p>Six distinct dining venues, each offering a unique journey through flavors crafted by award-winning chefs.</p>
      </div>
      <TabBar tabs={REST_TABS} active={tab} onChange={setTab} items={RESTAURANTS} />
      <section style={{ padding: '0 1.5rem 3rem', maxWidth: 1100, margin: '0 auto' }}>
        {filtered.length === 0 ? (
          <EmptyState text="No restaurants found in this category." />
        ) : (
          <div className="grid-3" style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '1.25rem' }}>
            {filtered.map(r => <RestCard key={r.name} r={r} onToast={onToast} canEdit={canManageMenus} />)}
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
          {canManageMenus && (
            <button type="button" className="btn-ghost" data-hms-no-edit="1" onClick={handleAdd} style={{ fontSize: '0.72rem' }}>+ Add menu item</button>
          )}
        </div>
        <TabBar tabs={MENU_TABS} active={menuTab} onChange={setMenuTab} items={menuList} />
        {filteredMenus.length === 0 ? (
          <EmptyState text={`No items in ${menuTab} yet.`} />
        ) : (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: '1.15rem', marginTop: '1.25rem' }}>
            {filteredMenus.map(item => (
              <div key={item.id || item.name} className="menu-food-card" style={{ position: 'relative', cursor: 'pointer' }}
                onClick={() => setSelectedMenuId(item.id)}>
                {canManageMenus && (
                  <div style={{ position: 'absolute', top: 10, right: 10, zIndex: 3, display: 'flex', gap: 6 }}
                    data-hms-no-edit="1" onClick={e => e.stopPropagation()}>
                    <button type="button" title="Change image" onClick={() => handleMenuImage(item)} style={toolBtnStyle('image')}><i className="fa-solid fa-image" style={{fontSize:11}}></i></button>
                    <button type="button" title="Edit item" onClick={() => handleEdit(item)} style={toolBtnStyle('edit')}><i className="fa-solid fa-pen" style={{fontSize:10}}></i></button>
                    <button type="button" title="Remove item" onClick={() => handleRemove(item)} style={toolBtnStyle('danger')}><i className="fa-solid fa-xmark" style={{fontSize:12}}></i></button>
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
                  <div className="menu-food-price">{typeof item.price === 'number' ? formatPeso(item.price) : (item.price || '—')}</div>
                </div>
                <div className="menu-food-body">
                  <p style={{ margin: '0 0 0.35rem', color: 'var(--warm)', fontSize: '0.65rem', letterSpacing: '0.12em', textTransform: 'uppercase' }}>
                    {normalizeMenuCategory(item.category)}
                  </p>
                  <h3 className="font-display" style={{ fontSize: '1.15rem', fontWeight: 700, margin: '0 0 0.4rem' }}>{item.name}</h3>
                  <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.8rem', fontWeight: 400, lineHeight: 1.5 }}>{item.sub}</p>
                </div>
              </div>
            ))}
          </div>
        )}
      </section>
      {canOrderMenu && cartCount > 0 && (
        <div data-hms-no-edit="1" style={{
          position: 'fixed', left: '50%', bottom: '1.25rem', transform: 'translateX(-50%)',
          zIndex: 1500, display: 'flex', alignItems: 'center', gap: '1rem',
          background: 'var(--card)', border: '1px solid var(--accent)', borderRadius: 999,
          padding: '0.6rem 0.7rem 0.6rem 1.2rem', boxShadow: '0 12px 32px rgba(0,0,0,0.18)',
          maxWidth: 'calc(100vw - 2rem)',
        }}>
          <span style={{ color: 'var(--fg)', fontSize: '0.85rem', whiteSpace: 'nowrap' }}>
            {cartCount} item{cartCount === 1 ? '' : 's'}
            <span style={{ color: 'var(--fg-muted)' }}> · </span>
            <span style={{ color: 'var(--accent)', fontWeight: 700 }}>{formatPeso(cartTotal)}</span>
          </span>
          <button type="button" className="btn-primary" style={{ padding: '0.55rem 1.2rem', borderRadius: 999 }}
            onClick={() => setCartOpen(true)}>
            Review Order <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
          </button>
        </div>
      )}
      <div style={{ textAlign: 'center', paddingBottom: '5rem' }}>
        <button className="btn-warm" onClick={() => onNav('booking')}>Book a Table <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i></button>
      </div>
      <MenuDetailModal
        item={selectedMenu}
        onClose={() => setSelectedMenuId(null)}
        canOrder={!!canOrderMenu}
        onAddToCart={addToCart}
        onToast={onToast}
      />
      <CartReviewModal
        open={cartOpen}
        onClose={() => setCartOpen(false)}
        cart={cart}
        onUpdateQty={updateCartQty}
        onRemove={removeFromCart}
        rooms={rooms}
        onPlaceOrder={placeCartOrder}
        onToast={onToast}
      />
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
        <span className="section-num">03 — Beyond the Room</span>
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
/* 'YYYY-MM-DD' + n days -> 'YYYY-MM-DD'. Built from the date parts, not by adding
   ms to a Date, so it can't drift across a DST boundary. */
function AmenitiesPage({ onNav, addons }) {
  const items = addons || [];

  return (
    <>
      <div className="page-header">
        <span className="section-num">Beyond the Stay</span>
        <h1 className="font-display">Hotel Amenities</h1>
        <p>Everything on hand to make your stay more comfortable, available on request at the front desk.</p>
      </div>
      <section style={{ padding: '0 1.5rem 4rem', maxWidth: 1100, margin: '0 auto' }}>
        {items.length === 0 ? (
          <div style={{ textAlign: 'center', padding: '3rem 1rem', color: 'var(--fg-muted)' }}>
            <i className="fa-solid fa-concierge-bell" style={{ fontSize: '1.6rem', color: 'var(--warm)', marginBottom: '1rem', display: 'block' }}></i>
            <p style={{ fontSize: '0.85rem' }}>No amenities listed yet.</p>
          </div>
        ) : (
          <div className="grid-3" style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '1.25rem' }}>
            {items.map(item => {
              const available = item.status === 'Available';
              return (
                <div key={item.id} className="rest-card">
                  <div className="rest-card-img">
                    <img src={amenityImg(item)} alt={item.name} loading="lazy" />
                  </div>
                  <div style={{ padding: '1.25rem' }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.5rem' }}>
                      <h3 className="font-display" style={{ fontSize: '1.15rem', fontWeight: 600 }}>{item.name}</h3>
                      <span style={{
                        display: 'inline-block', fontSize: '0.65rem', fontWeight: 600, letterSpacing: '0.05em',
                        textTransform: 'uppercase', padding: '0.25rem 0.6rem', borderRadius: '999px',
                        color: available ? '#2f7a4d' : '#a33',
                        background: available ? 'rgba(47,122,77,0.1)' : 'rgba(170,51,51,0.1)',
                      }}>{item.status}</span>
                    </div>
                    <p style={{ color: 'var(--accent)', fontWeight: 600, fontSize: '0.9rem', margin: 0 }}>{'₱' + Number(item.price || 0).toLocaleString()}</p>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </section>
    </>
  );
}


function BookingPage({ onToast, rooms }) {
  const roomList = rooms && rooms.length ? rooms : [];
  const [form, setForm] = useState({ checkIn: '', checkOut: '', guests: '', roomType: '', name: '', email: '' });
  const today = new Date().toISOString().split('T')[0];
  // Check-out must be a later date than check-in, so the day of check-in itself is
  // not selectable on the check-out calendar.
  const minCheckOut = addDays(form.checkIn || today, 1);
  const update = (f, v) => setForm(p => { const n = { ...p, [f]: v }; if (f === 'checkIn' && v) n.checkOut = ''; return n; });

  const getEst = () => {
    if (!form.checkIn || !form.checkOut || !form.roomType) return null;
    const blocks = stayBlocks(form.checkIn, form.checkOut, '');
    const room = roomList.find(r => r.id === form.roomType);
    return room ? { blocks, price: room.price, total: blocks * room.price, name: room.name } : null;
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
        <span className="section-num">04 — Reservations</span>
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
                    <input type="date" className="booking-input" value={form.checkOut} min={minCheckOut} onChange={e => update('checkOut', e.target.value)} required />
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
                      {roomList.map(r => <option key={r.id} value={r.id}>{r.name} — {formatPeso(r.price)} / {BLOCK_HOURS} hrs</option>)}
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
                      <div><strong className="font-display" style={{ fontSize: '1.6rem', color: 'var(--accent)', fontWeight: 700 }}>{formatPeso(est.total)}</strong></div>
                    </div>
                    <span style={{ fontSize: '0.82rem', color: 'var(--fg-muted)' }}>{est.blocks} &times; {BLOCK_HOURS} hrs &times; {formatPeso(est.price)}</span>
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
function Footer({ onNav, cardImages, page }) {
  // Passed only so the footer re-renders when the shared logo changes.
  void cardImages;
  void page;
  return (
    <footer className="site-footer" data-hms-section="footer" data-hms-bg-target="1">
      <div style={{ maxWidth: 1100, margin: '0 auto' }}>
        <div className="footer-grid" style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr 1fr', gap: '2.5rem', marginBottom: '3rem' }}>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '0.6rem', marginBottom: '0.85rem' }}>
              <BrandLogo size={30} />
              <span data-hms-text="1" data-hms-brand-name="1" style={{ fontSize: '1.05rem', fontWeight: 700, letterSpacing: '0.18em', textTransform: 'uppercase', color: '#fff' }}>SPC HOTEL</span>
            </div>
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
  const toastTimer = useRef(null);
  const [navLinks, setNavLinks] = useState(() => (
    window.HMSSiteContent ? window.HMSSiteContent.getNav() : [
      { id: 'nav-home', key: 'home', label: 'Home' },
      { id: 'nav-rooms', key: 'rooms', label: 'Rooms' },
      { id: 'nav-restaurant', key: 'restaurant', label: 'Restaurant' },
      { id: 'nav-amenities', key: 'amenities', label: 'Amenities' },
      { id: 'nav-experience', key: 'experience', label: 'Experience' },
    ]
  ));
  const [rooms, setRooms] = useState([]);
  // Restaurant menu lives in the DB and is shared by the whole team.
  const [menus, setMenus] = useState([]);
  const [canManageMenus, setCanManageMenus] = useState(false);
  const [inRestaurantModule, setInRestaurantModule] = useState(true);
  const [canEditNav, setCanEditNav] = useState(false);
  const [canEditRooms, setCanEditRooms] = useState(false);
  const [canManageRooms, setCanManageRooms] = useState(false);
  const [canReserveRooms, setCanReserveRooms] = useState(true);
  const [canOrderMenu, setCanOrderMenu] = useState(false);
  const [canEditExperiences, setCanEditExperiences] = useState(false);
  const [addons, setAddons] = useState([]);
  const [cardImages, setCardImages] = useState(() => (
    window.HMSSiteContent && window.HMSSiteContent.getCardImages ? window.HMSSiteContent.getCardImages() : {}
  ));
  const [heroSlides, setHeroSlidesState] = useState(() => (
    window.HMSSiteContent ? window.HMSSiteContent.getHeroSlides(DEFAULT_HERO_SLIDES) : DEFAULT_HERO_SLIDES
  ));
  const [canEditHeroSlides, setCanEditHeroSlides] = useState(false);

  // In-flight room writes — a poll that lands mid-write would show stale data.
  const pendingWrites = useRef(0);

  // Fetch rooms from the database (shared between Room Management & Front Desk)
  const roomsHydrated = useRef(false);
  const fetchRooms = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/rooms', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.rooms)) setRooms(data.rooms);
        roomsHydrated.current = true;
      })
      .catch(() => {});
  }, []);

  // Housekeeping's add-ons catalogue. Front Desk only reads it — what is free right
  // now is computed server-side, so the picker never has to work it out itself.
  const fetchAddons = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/addons', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.items)) setAddons(data.items);
      })
      .catch(() => {});
  }, []);

  // The server decides who may edit the menu — the client only mirrors that answer.
  const fetchMenus = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/menus', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.items)) setMenus(data.items);
        setCanManageMenus(data.can_manage === true);
      })
      .catch(() => {});
  }, []);

  // Poll so Front Desk arrivals, room status changes, menu edits and Housekeeping's
  // add-on stock all cross over between sessions.
  useEffect(() => {
    const refresh = () => { fetchRooms(); fetchMenus(); fetchAddons(); };
    refresh();
    const id = setInterval(refresh, 8000);
    window.addEventListener('focus', refresh);
    return () => {
      clearInterval(id);
      window.removeEventListener('focus', refresh);
    };
  }, [fetchRooms, fetchMenus, fetchAddons]);

  const syncSiteContent = useCallback(() => {
    if (!window.HMSSiteContent) return;
    // Must sync even in Design mode so Add Room / nav tools update the UI.
    setNavLinks(window.HMSSiteContent.getNav());
    // Rooms and menus come from the DB API — do NOT overwrite with customizations
    if (window.HMSSiteContent.getCardImages) setCardImages(window.HMSSiteContent.getCardImages());
    setHeroSlidesState(window.HMSSiteContent.getHeroSlides(DEFAULT_HERO_SLIDES));
    setCanEditHeroSlides(
      typeof window.HMSSiteContent.canEditHeroSlides === 'function'
        ? window.HMSSiteContent.canEditHeroSlides()
        : false
    );
    setCanEditNav(window.HMSSiteContent.canEditNav());
    setCanEditRooms(window.HMSSiteContent.canEditRooms());
    setCanManageRooms(
      typeof window.HMSSiteContent.canUseRoomManagementUi === 'function'
        ? window.HMSSiteContent.canUseRoomManagementUi()
        : false
    );
    setCanReserveRooms(
      typeof window.HMSSiteContent.canReserveRooms === 'function'
        ? window.HMSSiteContent.canReserveRooms()
        : true
    );
    // Restaurant staff tools stay inside the Restaurant module.
    setInRestaurantModule(
      typeof window.HMSSiteContent.canUseRestaurantUi === 'function'
        ? window.HMSSiteContent.canUseRestaurantUi()
        : true
    );
    setCanOrderMenu(
      typeof window.HMSSiteContent.canOrderMenu === 'function'
        ? window.HMSSiteContent.canOrderMenu()
        : false
    );
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
    if (toastTimer.current) clearTimeout(toastTimer.current);
    setToast({ message: msg, visible: true });
    toastTimer.current = setTimeout(() => {
      setToast(prev => ({ ...prev, visible: false }));
    }, 3000);
  }, []);

  const editRoom = useCallback((id, patch) => {
    setRooms(prev => prev.map(r => (r.id === id ? Object.assign({}, r, patch) : r)));

    // status is the one shared workflow field left on the room — write it straight to
    // the DB and reconcile from the response so Front Desk and Room Management never
    // drift. Guest data goes to /hotel/bookings instead.
    if (!patch || patch.status === undefined) {
      if (window.HMSSiteContent) window.HMSSiteContent.updateRoom(id, patch, []);
      return;
    }

    const body = { status: patch.status };

    pendingWrites.current += 1;
    fetch('/students/hotel/rooms/' + String(id).replace(/^db-/, ''), {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(body),
    })
      .then(r => (r.ok ? r.json() : null))
      .then(data => {
        if (data && data.room) {
          setRooms(prev => prev.map(r => (r.id === data.room.id ? data.room : r)));
        }
      })
      .catch(() => { /* keep optimistic state; the next poll reconciles */ })
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  const addRoom = useCallback((roomFromDb) => {
    // roomFromDb is already the shaped object returned by the API
    setRooms(prev => [...prev, roomFromDb]);
    return roomFromDb;
  }, []);

  const removeRoom = useCallback((id) => {
    setRooms(prev => prev.filter(r => r.id !== id));
  }, []);

  /* ── Bookings (hotel_bookings, not a blob on the room) ───────────────── */

  // Takes the stay and the up-front payment in one POST. The room comes back with its
  // projected `reservation` already on it, so the grid needs no guesswork.
  const createBooking = useCallback((room, guest, payment, addonLines) => {
    pendingWrites.current += 1;
    return fetch('/students/hotel/bookings', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({
        room_id: String(room.id).replace(/^db-/, ''),
        guest: {
          full_name: guest.fullName,
          contact_no: guest.contactNo,
          email: guest.email,
          id_number: guest.idNumber,
        },
        check_in: guest.checkIn,
        check_in_time: guest.checkInTime || '',
        check_out: guest.checkOut,
        payment: payment ? {
          type: payment.type,
          amount_paid: payment.amountPaid,
          method: payment.method,
          reference: payment.reference,
          payer_name: payment.payerName,
          notes: payment.notes,
        } : null,
        // Attached in the same POST on purpose: an add-on that ran out takes the whole
        // reservation down with it rather than leaving a stay half-equipped.
        addons: (addonLines || []).map(line => ({ addon_id: line.dbId, qty: line.qty })),
      }),
    })
      .then(r => r.json().then(data => (r.ok ? data : Promise.reject(data))))
      .then(data => {
        if (data && data.room) setRooms(prev => prev.map(r => (r.id === data.room.id ? data.room : r)));
        return data && data.booking;
      })
      .catch(err => {
        showToast((err && err.message) || 'Could not save that booking.');
        return Promise.reject(err);
      })
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, [showToast]);

  /* ── Restaurant menu (DB-backed, Restaurant role only) ───────────────── */

  const menuRequest = useCallback((url, method, body) => {
    pendingWrites.current += 1;
    return fetch(url, {
      method,
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: body ? JSON.stringify(body) : undefined,
    })
      .then(r => r.json().then(data => (r.ok ? data : Promise.reject(data))))
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  // Room-service food order — Front Desk / Restaurant staff only (server enforces this too).
  // One order, one or many dishes. `lines` is the reviewed cart.
  const placeOrder = useCallback((lines, details) => (
    menuRequest('/students/hotel/orders', 'POST', {
      room_number: details.roomNumber,
      guest_name: details.guestName,
      // menu_item_id lets the server reconcile stock by row rather than by name,
      // so renaming a dish no longer breaks the order or its stock return.
      items: lines.map(l => ({ menu_item_id: l.dbId || null, name: l.name, price: l.price, qty: l.qty })),
    })
      .then(data => {
        const count = lines.reduce((sum, l) => sum + l.qty, 0);
        showToast(`Order placed for Room ${details.roomNumber} — ${count} item${count === 1 ? '' : 's'}.`);
        fetchMenus(); // stock changed
        return data && data.order;
      })
      .catch(err => {
        showToast((err && err.message) || 'Could not place order.');
        return Promise.reject(err);
      })
  ), [menuRequest, showToast, fetchMenus]);

  // Menu item CRUD — Restaurant staff only (server enforces this too). Each write
  // is followed by a re-fetch so every open tab sees the same menu.
  const addMenu = useCallback((payload) => (
    menuRequest('/students/hotel/menus', 'POST', payload)
      .then(data => { fetchMenus(); return data && data.item; })
      .catch(err => {
        showToast((err && err.message) || 'Could not add that menu item.');
        return Promise.reject(err);
      })
  ), [menuRequest, fetchMenus, showToast]);

  const editMenu = useCallback((id, payload) => (
    menuRequest('/students/hotel/menus/' + String(id).replace(/^db-/, ''), 'PATCH', payload)
      .then(data => { fetchMenus(); return data && data.item; })
      .catch(err => {
        showToast((err && err.message) || 'Could not update that menu item.');
        return Promise.reject(err);
      })
  ), [menuRequest, fetchMenus, showToast]);

  const removeMenu = useCallback((id) => (
    menuRequest('/students/hotel/menus/' + String(id).replace(/^db-/, ''), 'DELETE')
      .then(data => { fetchMenus(); return data; })
      .catch(err => {
        showToast((err && err.message) || 'Could not remove that menu item.');
        return Promise.reject(err);
      })
  ), [menuRequest, fetchMenus, showToast]);

  // Room Management now lives on its own dedicated page — break out of the iframe.
  const openRoomManagement = useCallback((nav) => {
    hmsNavigateTop(window.HMS_ROOM_MANAGEMENT_URL + '?nav=' + (nav || 'manage-room'));
  }, []);

  // Room Management: auto-open Guest Details only when a brand-new reservation arrives
  const seenReservationIds = useRef(new Set());
  const initialSeedDone = useRef(false);
  useEffect(() => {
    if (!canManageRooms) return;
    if (!roomsHydrated.current) return; // wait for the first real snapshot before seeding
    // `reservation` is only ever projected from an open booking (see
    // HotelRoom::activeBooking()), so its presence alone means the room has a live guest.
    const occupied = (rooms || []).filter(r => r.reservation);
    // On first run, just seed the set — don't auto-navigate
    if (!initialSeedDone.current) {
      occupied.forEach(r => {
        seenReservationIds.current.add(r.id + '|' + (r.reservation.reservedAt || r.reservation.checkIn || ''));
      });
      initialSeedDone.current = true;
      return;
    }
    let hasNew = false;
    occupied.forEach(r => {
      const key = r.id + '|' + (r.reservation.reservedAt || r.reservation.checkIn || '');
      if (!seenReservationIds.current.has(key)) {
        seenReservationIds.current.add(key);
        hasNew = true;
      }
    });
    if (hasNew && page === 'rooms') {
      openRoomManagement('guest-details');
    }
  }, [rooms, canManageRooms, page, openRoomManagement]);

  const pages = {
    home: (
      <HomePage
        onNav={navigateTo}
        onToast={showToast}
        rooms={rooms}
        menus={menus}
        canEditRooms={canEditRooms}
        heroSlides={heroSlides}
        canEditHeroSlides={canEditHeroSlides}
        onAddRoom={addRoom}
        onEditRoom={editRoom}
        onRemoveRoom={removeRoom}
      />
    ),
    rooms: (
      <RoomsPage
        onNav={navigateTo}
        onToast={showToast}
        rooms={rooms}
        addons={addons}
        canEditRooms={canEditRooms}
        canManageRooms={canManageRooms}
        canReserveRooms={canReserveRooms}
        onAddRoom={addRoom}
        onEditRoom={editRoom}
        onRemoveRoom={removeRoom}
        onCreateBooking={createBooking}
        onRefreshAddons={fetchAddons}
        onOpenRoomManagement={openRoomManagement}
      />
    ),
    restaurant: (
      <RestaurantPage
        onNav={navigateTo}
        onToast={showToast}
        menus={menus}
        canManageMenus={canManageMenus && inRestaurantModule}
        canOrderMenu={canOrderMenu}
        onOrderMenu={placeOrder}
        onAddMenu={addMenu}
        onEditMenu={editMenu}
        onRemoveMenu={removeMenu}
        cardImages={cardImages}
        rooms={rooms}
      />
    ),
    experience: <ExperiencePage onNav={navigateTo} onToast={showToast} canEdit={canEditExperiences} cardImages={cardImages} />,
    amenities: <AmenitiesPage onNav={navigateTo} addons={addons} />,
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
        cardImages={cardImages}
        onToast={showToast}
      />
      <MobileMenu
        open={mobileOpen}
        onClose={() => setMobileOpen(false)}
        onNav={navigateTo}
        links={navLinks}
        cardImages={cardImages}
        onToast={showToast}
      />
      <main data-hms-page={page}>{pages[page] || pages.home}</main>
      <Footer onNav={navigateTo} cardImages={cardImages} page={page} />
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
