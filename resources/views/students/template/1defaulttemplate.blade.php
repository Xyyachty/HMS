<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>SPC HOTEL</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Outfit:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    position: fixed !important;
    top: 0 !important; left: 0; right: 0;
    z-index: 1000;
    padding: 0.9rem 2rem;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
  }
  .hero {
    margin-top: 64px !important;
  }
  .nav-item {
    position: relative;
    display: inline-flex;
    align-items: center;
  }
  .nav-links-desktop {
    position: relative;
  }
  /* Edit tools sit inline inside their own nav item, so they can never overlap
     the neighbouring link the way an absolutely positioned overlay did. */
  .nav-edit-tools {
    display: none;
    align-items: center;
    gap: 2px;
    margin-left: 4px;
    white-space: nowrap;
  }
  body.hms-design-mode .nav-edit-tools { display: inline-flex; }
  body.hms-design-mode .nav-item {
    gap: 2px;
    padding: 3px 6px;
    border-radius: 6px;
    border: 1px dashed rgba(34,211,238,0.45);
  }
  body.hms-design-mode .nav-item:hover,
  body.hms-design-mode .nav-item:focus-within {
    border-color: #22d3ee;
    background: rgba(34,211,238,0.10);
  }
  .nav-edit-tools button {
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 2px 3px;
    line-height: 1;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
  }
  .nav-edit-tools button:hover { background: rgba(148,163,184,0.22); }
  .nav-edit-btn { color: #22d3ee; }
  .nav-remove-btn { color: #f87171; }
  /* Inline chip at the end of the row — reads as "add a link here" instead of
     floating in the blank space beside the brand. */
  .nav-add-btn {
    display: none;
    align-items: center;
    gap: 4px;
    height: 26px;
    padding: 0 9px;
    border-radius: 999px;
    border: 1px dashed #22d3ee;
    background: rgba(34,211,238,0.12);
    color: #67e8f9;
    cursor: pointer;
    font-family: 'Outfit', sans-serif;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    white-space: nowrap;
  }
  body.hms-design-mode .nav-add-btn { display: inline-flex; }
  .nav-add-btn:hover { background: rgba(34,211,238,0.22); color: #a5f3fc; }
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
    overflow: hidden;
  }
  .hero-slide {
    position: absolute;
    inset: 0;
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
    opacity: 0;
    transition: opacity 1.2s ease;
  }
  .hero-slide.is-active { opacity: 1; }
  .hero-dots {
    position: absolute; left: 0; right: 0; bottom: 1.5rem; z-index: 3;
    display: flex; align-items: center; justify-content: center; gap: 0.5rem;
  }
  .hero-dot {
    width: 8px; height: 8px; border-radius: 50%; border: none; padding: 0;
    background: rgba(245,240,232,0.4); cursor: pointer; transition: all 0.2s;
  }
  .hero-dot.is-active { background: var(--accent); width: 22px; border-radius: 4px; }
  .hero-edit-btn {
    position: absolute; right: 1.5rem; bottom: 1.4rem; z-index: 3;
    display: inline-flex; align-items: center; gap: 0.4rem;
    background: rgba(12,11,9,0.65); color: var(--fg);
    border: 1px solid var(--border); border-radius: 999px;
    padding: 0.4rem 0.9rem; font-size: 0.68rem; letter-spacing: 0.04em;
    cursor: pointer; backdrop-filter: blur(4px);
  }
  .hero-edit-btn:hover { border-color: var(--accent); color: var(--accent); }
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

  /* Every card is a flex column filling its grid cell, so the row's own equal-height
     stretch reaches the card itself. The image area is a fixed, non-shrinking band and
     the body takes the rest, which keeps the image/content split identical no matter
     how long a room's name or description happens to be. */
  .room-card {
    display: flex;
    flex-direction: column;
    height: 100%;
    border-radius: 10px;
    overflow: hidden;
    background: var(--card);
    border: 1px solid var(--border);
    transition: border-color 0.2s, transform 0.2s;
    cursor: pointer;
  }
  .room-card:hover { border-color: var(--accent); transform: translateY(-4px); }
  .room-card-img { position: relative; height: 240px; flex: 0 0 240px; overflow: hidden; }
  .room-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
  .room-card:hover .room-card-img img { transform: scale(1.05); }
  /* Shorter band for the home page's preview cards. */
  .room-card-media { position: relative; height: 180px; flex: 0 0 180px; overflow: hidden; }
  .room-card-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .room-card-body { flex: 1 1 auto; display: flex; flex-direction: column; }
  /* Clamped rather than wrapped: a long name must not buy itself a second line and
     push its card taller than the one beside it. */
  .room-card-name {
    display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical;
    overflow: hidden; overflow-wrap: anywhere;
  }
  /* Exactly two lines' worth of space whether the text fills them or not. */
  .room-card-desc {
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden; height: 2.48rem;
  }
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

  /* The facilities on the Amenities page — Housekeeping's hotel_amenities rows.
     Same card frame as a room, minus the hover lift: these are not clickable. */
  .facility-card {
    display: flex; flex-direction: column; height: 100%;
    border-radius: 10px; overflow: hidden;
    background: var(--card); border: 1px solid var(--border);
    transition: border-color 0.2s, opacity 0.2s;
  }
  .facility-card:hover { border-color: var(--accent); }
  /* Closed or broken still shows — a guest needs to know the pool exists and is shut
     today, not be left wondering whether the hotel has one. */
  .facility-card.is-unavailable { opacity: 0.55; }
  .facility-card-media { position: relative; height: 190px; flex: 0 0 190px; overflow: hidden; }
  .facility-card-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .facility-card.is-unavailable .facility-card-media img { filter: grayscale(0.7); }
  .facility-status {
    position: absolute; top: 0.85rem; left: 0.85rem;
    padding: 0.22rem 0.7rem; border-radius: 4px;
    font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase;
    background: rgba(12,11,9,0.82); border: 1px solid transparent;
  }
  .facility-status.is-available   { color: #7bd88f; border-color: rgba(123,216,143,0.35); }
  .facility-status.is-closed      { color: #e8c369; border-color: rgba(232,195,105,0.35); }
  .facility-status.is-maintenance { color: #f08a99; border-color: rgba(240,138,153,0.35); }
  .facility-card-body { flex: 1 1 auto; padding: 1.25rem 1.35rem 1.4rem; display: flex; flex-direction: column; gap: 0.5rem; }
  .facility-card-meta {
    display: flex; align-items: center; gap: 0.4rem;
    font-size: 0.75rem; color: var(--fg-muted); font-weight: 300;
  }
  .facility-card-desc {
    font-size: 0.82rem; color: var(--fg-muted); font-weight: 300;
    line-height: 1.6; margin-top: 0.2rem;
    /* Two lines on the card; the modal is where the whole thing is readable. */
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden;
  }
  .facility-card { cursor: pointer; }
  .facility-card:focus-visible { outline: 2px solid var(--accent); outline-offset: 3px; }

  /* Its own classes rather than .room-modal's: a facility modal is read-only and the
     two must stay free to diverge. Skinned to match this template, not template 2's. */
  .facility-modal-overlay {
    position: fixed; inset: 0; z-index: 2000;
    background: rgba(0,0,0,0.72);
    display: flex; align-items: center; justify-content: center;
    padding: 1.25rem;
    animation: roomModalFade 0.2s ease;
  }
  .facility-modal {
    width: min(560px, 100%);
    max-height: min(90vh, 720px);
    overflow: auto;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.45);
    animation: roomModalRise 0.22s ease;
  }
  .facility-modal-img { position: relative; height: 260px; overflow: hidden; }
  .facility-modal-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .facility-modal-close {
    position: absolute; top: 0.75rem; right: 0.75rem;
    width: 34px; height: 34px; border-radius: 8px;
    border: 1px solid var(--border);
    background: rgba(12,11,9,0.85); color: var(--fg);
    cursor: pointer; display: inline-flex; align-items: center; justify-content: center;
  }
  .facility-modal-body { padding: 1.5rem 1.6rem 1.75rem; }
  .facility-modal-row {
    display: flex; align-items: flex-start; gap: 0.7rem;
    padding: 0.7rem 0; border-top: 1px solid var(--border);
    font-size: 0.85rem; color: var(--fg-muted); font-weight: 300;
  }
  .facility-modal-row i { color: var(--accent); font-size: 0.8rem; margin-top: 0.2rem; }
  .facility-modal-row strong {
    display: block; color: var(--fg); font-weight: 500;
    font-size: 0.68rem; letter-spacing: 0.12em; text-transform: uppercase;
    margin-bottom: 0.2rem;
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
  .room-status-badge.status-reserved {
    background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35);
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

  .room-cal {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 12px; padding: 0.9rem 1rem 1rem;
  }
  .room-cal-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 0.75rem;
  }
  .room-cal-title {
    font-family: 'Outfit', sans-serif; font-size: 0.82rem; font-weight: 600;
    color: var(--fg);
  }
  .room-cal-nav {
    width: 26px; height: 26px; border-radius: 8px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer;
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
    background: rgba(255,255,255,0.02); color: var(--fg);
    font-family: 'Outfit', sans-serif; font-size: 0.74rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all 0.15s;
  }
  .room-cal-day:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
  .room-cal-day.is-blank { visibility: hidden; cursor: default; }
  .room-cal-day.is-past { color: var(--fg-muted); opacity: 0.35; cursor: not-allowed; }
  .room-cal-day.is-booked { background: rgba(244,63,94,0.14); color: #fb7185; cursor: not-allowed; }
  .room-cal-day.is-in-range { background: rgba(212,175,55,0.14); }
  .room-cal-day.is-selected {
    background: var(--accent); border-color: var(--accent); color: #0c0b09; font-weight: 700;
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
    background: rgba(255,255,255,0.08);
  }
  .room-cal-swatch.is-available { background: rgba(255,255,255,0.08); }
  .room-cal-swatch.is-booked { background: #fb7185; }
  .room-cal-swatch.is-past { background: var(--fg-muted); opacity: 0.5; }

  .rm-row {
    display: flex; align-items: stretch; width: 100%;
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
    flex: 1; min-width: 0; padding: 1.25rem 1.6rem;
    position: relative;
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
    .rm-row { flex-direction: column; }
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
  /* Browsers ignore the surrounding dark theme on a <select>'s dropdown list unless
     its <option>s are styled directly — colorScheme alone leaves it white. */
  select.booking-input { color-scheme: dark; }
  select.booking-input option { background: var(--card); color: var(--fg); }

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

<script src="{{ asset('js/hms-site-content.js') }}?v={{ filemtime(public_path('js/hms-site-content.js')) }}"></script>
<script>
  window.HMS_ROOM_MANAGEMENT_URL = @json(route('students.roommanagement.manage'));
  window.HMS_VERIFY_GUEST_URL = @json(route('students.frontdesk.verify-guest'));
  // Resolved out here: the raw block below is not compiled, so Blade never runs inside it.
  window.HMS_DEFAULT_LOGO = @json(asset('images/hotel-logo-default.svg'));
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef, useMemo } = React;

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• DATA â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
const ROOM_CATEGORIES = ['Classic', 'Superior', 'Deluxe', 'Premium', 'Family'];
// Housekeeping condition only — occupancy lives on the booking (see room.reservation
// and the Rooms page calendar), not on the room's own status.
const ROOM_STATUSES = ['Available', 'Cleaning', 'Maintenance'];
const ROOM_TABS = ROOM_CATEGORIES;
// The Rooms page also gets an "All" tab in front of the categories so Front Desk can
// see every room Room Management created without switching tabs.
const ROOM_PAGE_TABS = ['All', ...ROOM_CATEGORIES];
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
  if (!Number.isFinite(n)) return '\u20B10';
  return '\u20B1' + n.toLocaleString();
}

function menuFoodImg(item) {
  if (item && item.img) return item.img;
  const seed = encodeURIComponent((item && (item.id || item.name)) || 'menu');
  return 'https://picsum.photos/seed/' + seed + '/800/600.jpg';
}

/* A room with no photo of its own — every seeded room starts that way — would render
   <img src=""> and leave a blank hole where its neighbours show a picture. Same
   stand-in the menu uses, seeded by the room so each one keeps the same photo
   between renders instead of reshuffling. */
function roomCardImg(room) {
  if (room && room.img) return room.img;
  const seed = encodeURIComponent((room && (room.id || room.name)) || 'room');
  return 'https://picsum.photos/seed/room-' + seed + '/800/600.jpg';
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
    id: 'superior', label: 'Superior', category: 'Superior', status: 'Available', name: 'Superior Twin Room', price: 240,
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

/* Where the site's data lives. The builder points these at /students/hotel/*, which
   needs a login; the Mini Portfolio points them at its own slug-scoped, guest-safe
   mirrors. The literals are the fallback so nothing breaks if the bridge is absent. */
const HMS_API = window.__HMS_API__ || {};
function hmsApi(key, fallback) { return HMS_API[key] || fallback; }

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

/* Room images are stored as base64 in the DB. A raw camera/screenshot upload blows past
   MySQL's max_allowed_packet and the insert dies with "MySQL server has gone away", so
   every picked image is downscaled and re-encoded before it leaves the browser. */
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
      // Flatten onto the card background so transparent PNGs don't turn black as JPEG.
      ctx.fillStyle = '#181714';
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

/** Open a file picker and return an image data-URL (works inside the builder iframe). */
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

/* One logo for the whole site. It lives under a single card-image key, so the
   header, the footer, the mobile menu and every page all read the same value —
   changing it anywhere changes it everywhere. */
const DEFAULT_LOGO = window.HMS_DEFAULT_LOGO || '/images/hotel-logo-default.svg';
const LOGO_ID = 'logo';

/* Sites saved while the logo was stored per section still carry those entries
   and no shared one. Read them in a fixed order so such a site keeps showing a
   logo instead of snapping back to the default; the first change made after
   this writes the shared key, which then wins everywhere. */
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
        // Attribute guard, not a src comparison: the browser reports src as a
        // resolved absolute URL, so comparing it to the constant would loop.
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
function MobileMenu({ open, onClose, onNavigate, links, cardImages, page }) {
  const items = [...(links || [])];
  // Passed only so the menu re-renders when the shared logo changes.
  void cardImages;
  return (
    <div className={`mobile-menu${open ? ' open' : ''}`}>
      <BrandLogo size={54} />
      {items.map(item => (
        <button key={item.id || item.key} onClick={() => { onNavigate(item.key); onClose(); }}>
          {item.label}
        </button>
      ))}
    </div>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• NAVBAR â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function NavBar({ currentPage, onNavigate, onToggleMobile, mobileOpen, links, canEditNav, onAddNav, onEditNav, onRemoveNav, cardImages, onToast }) {
  // Passed only so the navigation re-renders when the shared logo changes.
  void cardImages;
  const canEditThisLogo = !!(window.HMSSiteContent && window.HMSSiteContent.canEditLogo && window.HMSSiteContent.canEditLogo());
  const PAGE_OPTIONS = [
    { key: 'home', label: 'Home' },
    { key: 'rooms', label: 'Rooms' },
    { key: 'restaurant', label: 'Restaurant' },
    { key: 'experience', label: 'Experience' },
    { key: 'amenities', label: 'Amenities' },
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

  const handleRemove = (e, link) => {
    e.preventDefault();
    e.stopPropagation();
    if (hmsConfirm('Remove "' + link.label + '" from the navigation menu?')) onRemoveNav(link.id);
  };

  return (
    <nav className="nav-bar" role="navigation" aria-label="Main navigation">
      <div style={{ maxWidth: 1200, margin: '0 auto', display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '1rem' }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.65rem' }}>
          <button onClick={() => onNavigate('home')} style={{ background: 'none', border: 'none', cursor: 'pointer', padding: 0, display: 'flex', alignItems: 'center', gap: '0.6rem' }}>
            <BrandLogo size={34} />
            <span style={{ color: 'var(--fg)', fontSize: '1.05rem', fontWeight: 600, letterSpacing: '0.18em', textTransform: 'uppercase' }}>SPC HOTEL</span>
          </button>
          {canEditThisLogo && <ChangeLogoButton onToast={onToast} />}
        </div>
        <div className="nav-links-desktop" style={{ display: 'flex', alignItems: 'center', gap: '1.1rem', flexWrap: 'wrap', justifyContent: 'flex-end' }}>
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
                  <button type="button" className="nav-edit-btn" title={'Rename "' + link.label + '" or change the page it opens'}
                    onClick={(e) => handleEdit(e, link)}><i className="fa-solid fa-pen" style={{fontSize:10}}></i></button>
                  <button type="button" className="nav-remove-btn" title={'Remove "' + link.label + '" from the menu'}
                    onClick={(e) => handleRemove(e, link)}><i className="fa-solid fa-xmark" style={{fontSize:12}}></i></button>
                </span>
              )}
            </div>
          ))}
          {canEditNav && (
            <button
              type="button"
              className="nav-add-btn"
              title="Add a new link to this menu"
              onClick={handleAdd}
              data-hms-no-edit="1"
            ><i className="fa-solid fa-plus" style={{ fontSize: 10 }}></i> Add link</button>
          )}
        </div>
        <button className={`hamburger${mobileOpen ? ' active' : ''}`} onClick={onToggleMobile} aria-label="Toggle menu" data-hms-no-edit="1">
          <span></span><span></span><span></span>
        </button>
      </div>
    </nav>
  );
}


/* Five-slide hero. Front Desk owns Home, so these follow the exact __navLinks
   pattern — page:'home', fixed count, per-slide image replace only. */
const DEFAULT_HERO_SLIDES = [
  { id: 'hero-slide-1', img: 'https://picsum.photos/seed/luxuryhotel/1920/1080.jpg' },
  { id: 'hero-slide-2', img: 'https://picsum.photos/seed/hotellobby/1920/1080.jpg' },
  { id: 'hero-slide-3', img: 'https://picsum.photos/seed/hotelpool/1920/1080.jpg' },
  { id: 'hero-slide-4', img: 'https://picsum.photos/seed/luxurysuite/1920/1080.jpg' },
  { id: 'hero-slide-5', img: 'https://picsum.photos/seed/hoteldining/1920/1080.jpg' },
];

/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• HOME PAGE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
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
        <div
          key={slide.id}
          className={`hero-slide${i === active ? ' is-active' : ''}`}
          style={{ backgroundImage: 'url(' + slide.img + ')' }}
        ></div>
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


function HomePage({ onNavigate, onToast, rooms, menus, canEditRooms, onAddRoom, onEditRoom, onRemoveRoom, heroSlides, canEditHeroSlides }) {
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
    const priceRaw = hmsPrompt('Price per 12 hrs', String(room.price || 200));
    if (priceRaw == null) return;
    const price = Math.max(1, parseInt(priceRaw || String(room.price || 200), 10) || room.price || 200);
    const desc = hmsPrompt('Description', room.desc || '');
    if (desc == null) return;
    if (onEditRoom) onEditRoom(room.id, { name: String(name).trim(), price, desc: String(desc).trim() });
  };

  return (
    <>
      <section className="hero" data-hms-section="hero" data-hms-bg-target="1">
        <div className="hero-bg" data-hms-bg-target="1">
          <HeroSlider slides={heroSlides} canEdit={canEditHeroSlides} />
        </div>
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
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: '1.25rem', alignItems: 'stretch' }}>
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
              <div className="room-card-media" style={{ borderRadius: '12px 12px 0 0' }}>
                <img src={roomCardImg(room)} alt={room.name} />
              </div>
              <div className="room-card-body" style={{ padding: '1.1rem 1.15rem 1.25rem' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', gap: 8, alignItems: 'start' }}>
                  <h3 className="font-display room-card-name" style={{ fontSize: '1.15rem', margin: 0 }}>{room.name}</h3>
                  <span style={{ color: 'var(--accent)', fontWeight: 700, whiteSpace: 'nowrap' }}>{formatPeso(room.price)}</span>
                </div>
                <p className="room-card-desc" style={{ color: 'var(--fg-muted)', fontSize: '0.8rem', margin: '0.55rem 0 0', lineHeight: 1.55 }}>
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
              <span style={{ fontWeight: 700, color: 'var(--accent)', whiteSpace: 'nowrap' }}>{typeof item.price === 'number' ? formatPeso(item.price) : (item.price || '—')}</span>
            </div>
          ))}
        </div>
      </section>
    </>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• ROOMS PAGE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function RoomTabBar({ tabs, active, onChange, items, getKey, allKey }) {
  const keyFn = getKey || ((it) => normalizeRoomCategory(it.category || it.label));
  const counts = useMemo(() => {
    const map = {};
    tabs.forEach(t => {
      // The "All" tab isn't a category any item's key ever equals, so it is counted
      // separately rather than falling through the per-category filter to zero.
      map[t] = (allKey && t === allKey) ? items.length : items.filter(it => keyFn(it) === t).length;
    });
    return map;
  }, [tabs, items, keyFn, allKey]);

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

/* Rooms are billed in 12-hour blocks. Check-out has no time field, so it is
   assumed to fall at the same clock time as check-in. */
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
    let guard = 0; // a stay can't run forever; caps a bad range at ~2 years of days
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

/* The add-on picker's − / + buttons, sized down from the room-service stepper because
   they sit inside a form row rather than on their own. */
function addonStepBtn(disabled) {
  return {
    width: 26, height: 26, borderRadius: 7, border: '1px solid var(--border)',
    background: 'rgba(255,255,255,0.03)', color: disabled ? 'var(--fg-muted)' : 'var(--fg)',
    cursor: disabled ? 'default' : 'pointer', opacity: disabled ? 0.45 : 1,
    display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: '0.9rem',
  };
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
              <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
                {room.label || room.category || 'Room'}
              </p>
              <h2 className="font-display" style={{ fontSize: '1.65rem', marginBottom: '1.25rem' }}>{room.name}</h2>

              <div style={{ display: 'grid', gap: '1rem' }}>
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
                        onChange={e => updateGuest('checkIn', e.target.value)} required style={{ colorScheme: 'dark' }} />
                    </div>
                    <div>
                      <label style={fieldLabel}>Check-In Time</label>
                      <input type="time" className="booking-input" value={guestForm.checkInTime}
                        onChange={e => updateGuest('checkInTime', e.target.value)} required style={{ colorScheme: 'dark' }} />
                    </div>
                  </div>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.85rem' }}>
                    <div>
                      <label style={fieldLabel}>Check-Out</label>
                      <input type="date" className="booking-input" value={guestForm.checkOut} min={minCheckOut}
                        onChange={e => updateGuest('checkOut', e.target.value)} required style={{ colorScheme: 'dark' }} />
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
                  style={{ background: 'none', border: 'none', color: 'var(--accent)', cursor: 'pointer', fontSize: '0.75rem', padding: '0.85rem 0 0', fontFamily: 'Outfit, sans-serif' }}>
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
                      <div key={addon.id} style={{ display: 'flex', alignItems: 'center', gap: '0.7rem', padding: '0.5rem 0', borderBottom: '1px solid rgba(255,255,255,0.04)' }}>
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
                          <span style={{ fontSize: '0.72rem', color: '#fb7185', flexShrink: 0 }}>Out of stock</span>
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
                  <span style={{ color: 'var(--fg-muted)', fontSize: '0.8rem' }}>
                    {blocks} × {BLOCK_HOURS} hrs × {formatPeso(room.price)}
                  </span>
                  <strong style={{ color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontSize: '1.15rem' }}>{formatPeso(totalDue)}</strong>
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
                    {/* Shown for both types: a full payment settles the stay, and saying
                        so as a plain 0.00 is what tells the desk it is settled. */}
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

                  {/* Cash has nothing to reference — no receipt no., card digits or
                      transaction id — so the field is not shown rather than sitting
                      there empty and optional. */}
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

function RoomsPage({ onNavigate, onToast, rooms, addons, canEditRooms, canManageRooms, canReserveRooms, onAddRoom, onEditRoom, onRemoveRoom, onCreateBooking, onRefreshAddons, onOpenRoomManagement }) {
  const list = rooms && rooms.length ? rooms : [];
  // Front Desk lands on "All" so every room Room Management created is visible on
  // one screen; the category tabs stay for narrowing it down.
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
      amenities: [
        { icon: 'fa-bed', text: 'Bed' },
        { icon: 'fa-wifi', text: 'WiFi' },
      ],
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
  // booking itself and hands back both rows. Everything below only runs once
  // that POST actually succeeds; onCreateBooking's own .catch already toasts the error,
  // so a failed booking (room already taken, bad dates, ...) no longer looks like a
  // success and silently drops the guest.
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
        <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.75rem' }}>Accommodations</p>
        <h1 className="font-display">Our Rooms & Suites</h1>
        <p>Each room is a sanctuary of design, blending modern luxury with artisanal craftsmanship and sweeping views.</p>
      </div>
      <RoomTabBar tabs={ROOM_PAGE_TABS} active={tab} onChange={setTab} items={list} allKey="All" />
      <section style={{ padding: '0 1.5rem 6rem', maxWidth: 1200, margin: '0 auto' }}>
        {filtered.length === 0 && !canEditRooms ? (
          <p style={{ textAlign: 'center', color: 'var(--fg-muted)', padding: '3rem 1rem' }}>No rooms found in this category.</p>
        ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '1.5rem', alignItems: 'stretch' }}>
          {filtered.map(room => {
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
                <img src={roomCardImg(room)} alt={room.name} loading="lazy" />
              </div>
              <div className="room-card-body" style={{ padding: '1.15rem 1.25rem 1.25rem' }}>
                <p className="room-card-name" style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.12em', textTransform: 'uppercase', marginBottom: '0.35rem' }}>
                  {room.label || room.category || 'Room'}
                </p>
                <h3 className="font-display room-card-name" style={{ fontSize: '1.15rem', fontWeight: 700, margin: 0 }}>{room.name}</h3>
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
              <span style={{ fontSize: 11, opacity: 0.75, maxWidth: 180, textAlign: 'center' }}>Added under {tab === 'All' ? 'Classic' : tab}</span>
            </button>
          )}
        </div>
        )}
      </section>
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


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• RESTAURANT PAGE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */

/*
 * Guests eligible for a room-service order: the room has a live booking whose guest
 * Front Desk confirmed at the desk via Verify Guest. A room only carries a
 * `reservation` while its booking is still open, so releasing the room closes the
 * booking and the departed guest drops out of this list on its own.
 */
function checkedInRoomsFor(rooms) {
  return (rooms || []).filter(r => (
    r.reservation && r.reservation.status === 'Checked In'
  ));
}

/*
 * A menu item's detail view only ever adds it to the order being built — it never
 * places an order itself. Guest and room are picked once in CartReviewModal, at
 * checkout, not per item; that is what lets an order carry several different dishes
 * instead of forcing one order per item.
 */
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
    background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: 'pointer',
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
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
            {normalizeMenuCategory(item.category)}
          </p>
          <h2 className="font-display" style={{ fontSize: '1.65rem', marginBottom: '1.25rem' }}>{item.name}</h2>

          <div style={{ display: 'grid', gap: '1rem' }}>
            <div>
              <p style={{ fontSize: '0.68rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.4rem' }}>Price</p>
              <p style={{ color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontSize: '1.25rem', margin: 0 }}>
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
                  {/* Stepper, not a number input: the same - / + the cart already uses,
                      and it cannot be typed into an out-of-stock or non-numeric value. */}
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
 * one line per dish — the server (HotelFoodOrder) already accepts a multi-line
 * items array; this is what actually gives Front Desk a way to fill it with more
 * than one line before placing the order was the missing piece.
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
    background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: 'pointer',
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
              <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>Room Service</p>
              <h2 className="font-display" style={{ fontSize: '1.55rem', margin: 0 }}>Review Order</h2>
            </div>
            <button type="button" onClick={onClose} aria-label="Close"
              style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: 'pointer', flexShrink: 0 }}>
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
                      <p style={{ margin: 0, color: 'var(--accent-light)', fontSize: '0.76rem' }}>{formatPeso(line.price)}</p>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.35rem' }}>
                      <button type="button" onClick={() => onUpdateQty(line.dbId, line.qty - 1)} style={stepBtn}>−</button>
                      <span style={{ color: 'var(--fg)', minWidth: 18, textAlign: 'center', fontSize: '0.85rem' }}>{line.qty}</span>
                      <button type="button" onClick={() => onUpdateQty(line.dbId, line.qty + 1)}
                        disabled={line.stock != null && line.qty >= line.stock} style={stepBtn}>+</button>
                    </div>
                    <button type="button" onClick={() => onRemove(line.dbId)} title="Remove"
                      style={{ background: 'none', border: 'none', color: '#fb7185', cursor: 'pointer', fontSize: '0.95rem', padding: '0.2rem' }}>
                      <i className="fa-solid fa-trash-can"></i>
                    </button>
                  </div>
                ))}
              </div>

              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: '1.1rem' }}>
                <span style={{ color: 'var(--fg-muted)', fontSize: '0.82rem' }}>Total</span>
                <span style={{ color: 'var(--accent-light)', fontWeight: 700, fontSize: '1.1rem', fontFamily: 'Playfair Display, serif' }}>{formatPeso(total)}</span>
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

function RestaurantPage({ onNavigate, onToast, menus, canManageMenus, canOrderMenu, onOrderMenu, cardImages, isDesignMode, rooms }) {
  const menuList = menus || [];
  const [selectedMenuId, setSelectedMenuId] = useState(null);
  const selectedMenu = menuList.find(m => m.id === selectedMenuId) || null;
  const [menuTab, setMenuTab] = useState('Main Dishes');
  const [cart, setCart] = useState([]);
  const [cartOpen, setCartOpen] = useState(false);
  const filteredMenus = menuList.filter(item => normalizeMenuCategory(item.category) === menuTab);
  void cardImages;

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

  return (
    <>
      <div className="page-header">
        <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.75rem' }}>Culinary Arts</p>
        <h1 className="font-display">Restaurant Menu</h1>
        <p>Browse our courses — Main Dishes, Appetizers, Soups, Desserts, and Beverages.</p>
      </div>

      <RoomTabBar
        tabs={MENU_TABS}
        active={menuTab}
        onChange={setMenuTab}
        items={menuList}
        getKey={(item) => normalizeMenuCategory(item.category)}
      />

      <section style={{ padding: '0 1.5rem 5rem', maxWidth: 1200, margin: '0 auto' }}>
        {filteredMenus.length === 0 ? (
          <p style={{ textAlign: 'center', color: 'var(--fg-muted)', padding: '2rem 1rem' }}>
            No items in {menuTab} yet.
          </p>
        ) : (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(260px, 1fr))', gap: '1.25rem' }}>
            {filteredMenus.map(item => (
              <div key={item.id || item.name} className="menu-food-card" style={{ position: 'relative', cursor: 'pointer' }}
                onClick={() => setSelectedMenuId(item.id)}>
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
                  <p style={{ margin: '0 0 0.35rem', color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.12em', textTransform: 'uppercase' }}>
                    {normalizeMenuCategory(item.category)}
                  </p>
                  <h3 className="font-display" style={{ fontSize: '1.15rem', fontWeight: 700, margin: '0 0 0.4rem' }}>{item.name}</h3>
                  <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.8rem', fontWeight: 300, lineHeight: 1.5 }}>{item.sub}</p>
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
          padding: '0.6rem 0.7rem 0.6rem 1.2rem', boxShadow: '0 12px 32px rgba(0,0,0,0.45)',
          maxWidth: 'calc(100vw - 2rem)',
        }}>
          <span style={{ color: 'var(--fg)', fontSize: '0.85rem', whiteSpace: 'nowrap' }}>
            {cartCount} item{cartCount === 1 ? '' : 's'}
            <span style={{ color: 'var(--fg-muted)' }}> · </span>
            <span style={{ color: 'var(--accent-light)', fontWeight: 700 }}>{formatPeso(cartTotal)}</span>
          </span>
          <button type="button" className="btn-primary" style={{ padding: '0.55rem 1.2rem', borderRadius: 999 }}
            onClick={() => setCartOpen(true)}>
            Review Order <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.7rem' }}></i>
          </button>
        </div>
      )}

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


/* The hotel's facilities, straight from Housekeeping's Amenities screen. Not add-ons —
   those are the things a guest borrows for their room and they live on their own page.

   The header is click-to-edit copy Housekeeping owns; the cards below are database rows
   and are deliberately NOT editable inline, the same call the rooms and menu lists make.
   Editing one happens on the Housekeeping screen, and this page follows within 8 seconds.

   Nothing is filtered out by status. A guest who cannot find the pool on this page will
   assume the hotel has none, so a closed or broken one stays listed and says so. */
function facilityStatusClass(status) {
  if (status === 'Available') return 'is-available';
  if (status === 'Temporarily Closed') return 'is-closed';
  return 'is-maintenance';
}

/* What a guest gets on tapping a card: the picture at full width and the details the
   card had to clamp or drop. Read-only — there is nothing to book on a facility.

   Deliberately NOT shown: the repair notes on the row. Those are Maintenance's working
   comments to Housekeeping, and the amenity's own status already tells a guest what
   they need to know. */
function FacilityModal({ facility, onClose }) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    // The page behind must not scroll under the overlay.
    const prev = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    return () => {
      document.removeEventListener('keydown', onKey);
      document.body.style.overflow = prev;
    };
  }, [onClose]);

  return (
    <div className="facility-modal-overlay" data-hms-no-edit="1" onClick={onClose} role="dialog" aria-modal="true" aria-label={facility.name}>
      <div className="facility-modal" onClick={e => e.stopPropagation()}>
        <div className="facility-modal-img">
          <img src={facility.img} alt={facility.name} />
          <button type="button" className="facility-modal-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
          <span className={'facility-status ' + facilityStatusClass(facility.status)}>{facility.status}</span>
        </div>
        <div className="facility-modal-body">
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.2em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>Amenity</p>
          <h2 className="font-display" style={{ fontSize: '1.8rem', fontWeight: 700, margin: '0 0 1.1rem' }}>{facility.name}</h2>

          {facility.description && (
            <p style={{ fontSize: '0.9rem', color: 'var(--fg-muted)', fontWeight: 300, lineHeight: 1.7, margin: '0 0 1.1rem' }}>
              {facility.description}
            </p>
          )}

          {facility.location && (
            <div className="facility-modal-row">
              <i className="fa-solid fa-location-dot"></i>
              <div><strong>Location</strong>{facility.location}</div>
            </div>
          )}
          <div className="facility-modal-row">
            <i className="fa-solid fa-clock"></i>
            <div><strong>Opening Hours</strong>{facility.hours || 'Ask the front desk'}</div>
          </div>
          <div className="facility-modal-row">
            <i className="fa-solid fa-circle-info"></i>
            <div>
              <strong>Status</strong>
              {facility.status === 'Available'
                ? 'Open to guests.'
                : facility.status === 'Temporarily Closed'
                  ? 'Closed for now. The front desk can tell you when it reopens.'
                  : 'Closed while it is being repaired. Sorry for the inconvenience.'}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function AmenitiesPage({ amenities }) {
  const list = Array.isArray(amenities) ? amenities : [];
  const [openId, setOpenId] = useState(null);
  // Read off the live list rather than held in state, so a poll that changes a
  // facility's status updates the open modal instead of showing a stale copy.
  const selected = list.find(item => item.id === openId) || null;

  return (
    <>
      <div className="page-header">
        <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.75rem' }}>Beyond the Stay</p>
        <h1 className="font-display">Hotel Amenities</h1>
        <p>Everything on hand to make your stay more comfortable, available on request at the front desk.</p>
      </div>
      <section style={{ padding: '0 1.5rem 5rem', maxWidth: 1200, margin: '0 auto' }}>
        {list.length === 0 ? (
          <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '4rem 1.5rem', textAlign: 'center', color: 'var(--fg-muted)' }}>
            <i className="fa-solid fa-person-swimming" style={{ fontSize: '1.8rem', color: 'var(--accent)', opacity: 0.5, display: 'block', marginBottom: '0.9rem' }}></i>
            <p style={{ margin: 0, fontWeight: 300 }}>Amenities coming soon.</p>
          </div>
        ) : (
          <div className="grid-3" style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: '1.5rem' }}>
            {list.map(item => (
              <div
                key={item.id}
                className={'facility-card' + (item.status === 'Available' ? '' : ' is-unavailable')}
                role="button"
                tabIndex={0}
                aria-label={'View ' + item.name}
                onClick={() => setOpenId(item.id)}
                onKeyDown={e => {
                  if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); setOpenId(item.id); }
                }}
              >
                <div className="facility-card-media">
                  <img src={item.img} alt={item.name} />
                  <span className={'facility-status ' + facilityStatusClass(item.status)}>{item.status}</span>
                </div>
                <div className="facility-card-body">
                  <h3 className="font-display" style={{ fontSize: '1.25rem', fontWeight: 700, margin: 0 }}>{item.name}</h3>
                  {item.location && (
                    <div className="facility-card-meta">
                      <i className="fa-solid fa-location-dot" style={{ color: 'var(--accent)', fontSize: '0.72rem' }}></i>
                      {item.location}
                    </div>
                  )}
                  {item.hours && (
                    <div className="facility-card-meta">
                      <i className="fa-solid fa-clock" style={{ color: 'var(--accent)', fontSize: '0.72rem' }}></i>
                      {item.hours}
                    </div>
                  )}
                  {item.description && <p className="facility-card-desc">{item.description}</p>}
                  <span style={{ marginTop: 'auto', paddingTop: '0.6rem', color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.12em', textTransform: 'uppercase' }}>
                    View details <i className="fa-solid fa-arrow-right" style={{ fontSize: '0.62rem' }}></i>
                  </span>
                </div>
              </div>
            ))}
          </div>
        )}
      </section>

      {selected && <FacilityModal facility={selected} onClose={() => setOpenId(null)} />}
    </>
  );
}


/* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• BOOKING PAGE â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */
function BookingPage({ onToast, rooms, onCreateBooking }) {
  const roomList = rooms && rooms.length ? rooms : [];
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
    const blocks = stayBlocks(form.checkIn, form.checkOut, '');
    const room = roomList.find(r => r.id === form.roomType);
    if (!room) return null;
    return { blocks, price: room.price, total: blocks * room.price };
  };

  const estimate = getEstimate();

  /* On the Mini Portfolio this really books. In the builder it stays the demo it has
     always been — a student clicking through their own design should not be filing
     reservations against their hotel. onCreateBooking is the same call the Rooms page
     makes, so both entry points go through one endpoint and one set of guards. */
  const [sending, setSending] = useState(false);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!isSiteInteractive() || sending) return;
    const room = roomList.find(r => r.id === form.roomType);

    if (!window.__HMS_PUBLIC__ || !onCreateBooking) {
      onToast(`Thank you, ${form.name}! Your booking for the ${room ? room.name : 'room'} has been submitted.`);
      setForm({ checkIn: '', checkOut: '', guests: '', roomType: '', name: '', email: '' });
      return;
    }

    if (!room) { onToast('Please choose a room type.'); return; }

    setSending(true);
    // createBooking carries the dates on the guest object, not a separate one.
    // No payment and no add-ons: the public endpoint refuses both anyway.
    Promise.resolve(onCreateBooking(
      room,
      {
        // The form asks for a name and an email; the desk takes the rest on arrival.
        fullName: form.name, contactNo: '', email: form.email, idNumber: '',
        checkIn: form.checkIn, checkOut: form.checkOut, checkInTime: '',
      },
      null,
      []
    ))
      .then(() => {
        onToast(`Thank you, ${form.name}! Your request for the ${room.name} is with the front desk.`);
        setForm({ checkIn: '', checkOut: '', guests: '', roomType: '', name: '', email: '' });
      })
      .catch(err => onToast((err && err.message) ? err.message : 'That booking could not be sent.'))
      .finally(() => setSending(false));
  };

  // Check-out must be a later date than check-in, so the day of check-in itself is
  // not selectable on the check-out calendar.
  const minCheckOut = addDays(form.checkIn || today, 1);

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
                  {roomList.map(r => <option key={r.id} value={r.id}>{r.name} â€” {formatPeso(r.price)} / {BLOCK_HOURS} hrs</option>)}
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
                    <span style={{ opacity: 0.55 }}> ({estimate.blocks} x {BLOCK_HOURS} hrs x {formatPeso(estimate.price)})</span>
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
function Footer({ onNavigate, cardImages, page }) {
  // Passed only so the footer re-renders when the shared logo changes.
  void cardImages;
  void page;
  return (
    <footer data-hms-section="footer" data-hms-bg-target="1" style={{ padding: '3.5rem 1.5rem 1.75rem', borderTop: '1px solid var(--border)' }}>
      <div style={{ maxWidth: 1200, margin: '0 auto' }}>
        <div className="footer-grid" style={{ display: 'grid', gridTemplateColumns: '2fr 1fr 1fr 1fr', gap: '2.5rem', marginBottom: '2.5rem' }}>
          <div>
            <div style={{ display: 'flex', alignItems: 'center', gap: '0.6rem', marginBottom: '0.85rem' }}>
              <BrandLogo size={38} />
              <span style={{ fontSize: '1.05rem', fontWeight: 600, letterSpacing: '0.18em', textTransform: 'uppercase' }}>SPC HOTEL</span>
            </div>
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
      { id: 'nav-amenities', key: 'amenities', label: 'Amenities' },
    ]
  ));
  const [rooms, setRooms] = useState([]);
  // Restaurant menu lives in the DB and is shared by the whole team.
  const [menus, setMenus] = useState([]);
  const [canManageMenus, setCanManageMenus] = useState(false);
  const [inRestaurantModule, setInRestaurantModule] = useState(true);
  // Restaurant menu CRUD only lives on the dedicated management page, and that
  // page is only reachable from Preview — Design mode is layout-editing only.
  const [isDesignMode, setIsDesignMode] = useState(() => window.__HMS_DESIGN_MODE__ === true);
  const [canEditNav, setCanEditNav] = useState(false);
  const [canEditRooms, setCanEditRooms] = useState(false);
  const [canManageRooms, setCanManageRooms] = useState(false);
  const [canReserveRooms, setCanReserveRooms] = useState(true);
  const [canOrderMenu, setCanOrderMenu] = useState(false);
  const [addons, setAddons] = useState([]);
  const [amenities, setAmenities] = useState([]);
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
    fetch(hmsApi('rooms', '/students/hotel/rooms'), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
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
    fetch(hmsApi('addons', '/students/hotel/addons'), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.items)) setAddons(data.items);
      })
      .catch(() => {});
  }, []);

  // Housekeeping's facilities list. Read-only here: what a guest sees is exactly what
  // the Housekeeping Amenities screen holds, closures and repairs included.
  const fetchAmenities = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(hmsApi('amenities', '/students/hotel/amenities'), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.items)) setAmenities(data.items);
      })
      .catch(() => {});
  }, []);

  // The server decides who may edit the menu — the client only mirrors that answer.
  const fetchMenus = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(hmsApi('menus', '/students/hotel/menus'), { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
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
    const refresh = () => { fetchRooms(); fetchMenus(); fetchAddons(); fetchAmenities(); };
    refresh();
    const id = setInterval(refresh, 8000);
    window.addEventListener('focus', refresh);
    return () => {
      clearInterval(id);
      window.removeEventListener('focus', refresh);
    };
  }, [fetchRooms, fetchMenus, fetchAddons, fetchAmenities]);

  const syncSiteContent = useCallback(() => {
    if (!window.HMSSiteContent) return;
    // Must sync even in Design mode so Add Room / menu / nav tools update the UI.
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

  useEffect(() => {
    const onModeChange = (e) => setIsDesignMode(!!(e && e.detail && e.detail.designMode));
    window.addEventListener('hms-mode-change', onModeChange);
    setIsDesignMode(window.__HMS_DESIGN_MODE__ === true);
    return () => window.removeEventListener('hms-mode-change', onModeChange);
  }, []);

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
    fetch(hmsApi('roomUpdate', '/students/hotel/rooms') + '/' + String(id).replace(/^db-/, ''), {
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
    return fetch(hmsApi('bookings', '/students/hotel/bookings'), {
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
        onNavigate={navigateTo}
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
        onNavigate={navigateTo}
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
        onNavigate={navigateTo}
        onToast={showToast}
        menus={menus}
        canManageMenus={canManageMenus && inRestaurantModule}
        canOrderMenu={canOrderMenu}
        onOrderMenu={placeOrder}
        cardImages={cardImages}
        isDesignMode={isDesignMode}
        rooms={rooms}
      />
    ),
    experience: <ExperiencePage onNavigate={navigateTo} />,
    amenities: <AmenitiesPage amenities={amenities} />,
    booking: <BookingPage onToast={showToast} rooms={rooms} onCreateBooking={createBooking} />,
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
        cardImages={cardImages}
        onToast={showToast}
      />
      <MobileMenu
        open={mobileOpen}
        onClose={() => setMobileOpen(false)}
        onNavigate={navigateTo}
        links={navLinks}
        cardImages={cardImages}
        page={page}
      />
      <main data-hms-page={page}>{pages[page] || pages.home}</main>
      <Footer onNavigate={navigateTo} cardImages={cardImages} page={page} />
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
