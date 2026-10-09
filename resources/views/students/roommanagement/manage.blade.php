@extends('students.builder.ops-shell')

@section('page-title', 'Room Management')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }
  .room-status-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent;
  }
  /* Only the two booking-lifecycle tones are used now — see bookingStatusClass(). */
  .room-status-badge.status-reserved { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .room-status-badge.status-occupied { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
  .rm-table { width: 100%; border-collapse: collapse; font-family: var(--font-body, 'Outfit', sans-serif); }
  .rm-table th {
    padding: 0.6rem 0.85rem; font-size: 0.62rem; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase; color: var(--fg-muted);
    border-bottom: 1px solid var(--border); white-space: nowrap;
    text-align: left; background: rgba(255,255,255,0.02);
  }
  .rm-table td {
    padding: 0.7rem 0.85rem; font-size: 0.82rem; color: var(--fg-muted);
    border-bottom: 1px solid rgba(42,38,33,0.5); vertical-align: middle; white-space: nowrap;
  }
  .rm-table tr:last-child td { border-bottom: none; }
  .btn-primary {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: var(--accent); color: var(--bg);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600;
    font-size: 0.8rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.8rem 1.8rem; border: none; border-radius: 6px;
    cursor: pointer; transition: background 0.2s, transform 0.2s;
  }
  .btn-primary:hover { background: var(--accent-light); transform: translateY(-1px); }
  .btn-outline {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: transparent; color: var(--accent);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 500;
    font-size: 0.75rem; letter-spacing: 0.1em; text-transform: uppercase;
    padding: 0.6rem 1.3rem; border: 1px solid var(--accent); border-radius: 6px;
    cursor: pointer; transition: background 0.2s, color 0.2s, transform 0.2s;
    text-decoration: none;
  }
  .btn-outline:hover { background: var(--accent); color: var(--bg); transform: translateY(-1px); }
  .booking-input {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus { border-color: var(--accent); }
  .booking-input::placeholder { color: var(--fg-muted); opacity: 0.5; }
  .rm-row {
    display: flex; align-items: stretch; width: 100%;
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    overflow: hidden;
  }
  .rm-content { flex: 1; min-width: 0; padding: 1.25rem 1.6rem; position: relative; color: var(--fg); }
  .rm-panel { max-width: 520px; }
  .rm-panel h3 { font-family: var(--font-display, 'Playfair Display', serif); font-size: 1.35rem; font-weight: 700; margin: 0 0 0.35rem; color: var(--fg); }
  .rm-panel-desc { color: var(--fg-muted); font-size: 0.82rem; margin: 0 0 1.35rem; line-height: 1.5; }
  .rm-form-grid { display: grid; gap: 0.95rem; }
  .rm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }
  @media (max-width: 640px) {
    .rm-row { flex-direction: column; }
    .rm-form-row { grid-template-columns: 1fr; }
  }

  /* Room Availability tab — card grid + detail modal, same look as the hotel
     site's Rooms page so the calendar reads the same way in both places. */
  .room-card-tab {
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.74rem; font-weight: 600;
    letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.5rem 0.9rem; border-radius: 100px;
    border: 1.5px solid var(--border); background: transparent;
    color: var(--fg-muted); cursor: pointer; transition: all 0.15s;
  }
  .room-card-tab:hover { border-color: var(--accent); color: var(--accent); }
  .room-card-tab.active { background: var(--accent); border-color: var(--accent); color: var(--bg, #0c0b09); }
  .room-browse-card {
    text-align: left; border: 1px solid var(--border); border-radius: 10px;
    overflow: hidden; background: var(--bg-warm); cursor: pointer; padding: 0;
    transition: border-color 0.15s, transform 0.15s;
  }
  .room-browse-card:hover { border-color: var(--accent); transform: translateY(-2px); }

  .room-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.6);
    display: flex; align-items: center; justify-content: center;
    padding: 1.5rem; z-index: 200;
  }
  .room-modal {
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto;
  }
  .room-modal-img { position: relative; height: 200px; }
  .room-modal-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .room-modal-close {
    position: absolute; top: 10px; right: 10px; width: 32px; height: 32px;
    border-radius: 8px; border: none; background: rgba(0,0,0,0.55); color: #fff;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
  }

  /* Photo chooser. It opens on top of the Add/Edit Room dialog, so it needs to
     stack above the overlay that owns it. */
  .room-image-overlay { z-index: 260; }
  .room-image-modal { max-width: 560px; padding: 1.5rem; }
  .room-image-title {
    font-family: var(--font-display, 'Playfair Display', serif);
    font-size: 1.35rem; color: var(--fg); margin: 0 0 0.3rem;
  }
  .room-image-hint {
    color: var(--fg-muted); font-size: 0.76rem; line-height: 1.5; margin: 0 0 1.1rem;
  }
  .room-image-stage {
    position: relative; border: 1px solid var(--border); border-radius: 10px;
    overflow: hidden; background: rgba(255,255,255,0.02);
  }
  .room-image-stage img {
    width: 100%; height: 260px; object-fit: cover; display: block;
  }
  .room-image-badge {
    position: absolute; top: 10px; left: 10px; padding: 0.25rem 0.6rem;
    border-radius: 999px; background: rgba(0,0,0,0.6); color: #fff;
    font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase;
  }
  .room-image-empty {
    height: 260px; display: flex; flex-direction: column; align-items: center;
    justify-content: center; gap: 10px; color: var(--fg-muted); font-size: 0.78rem;
  }
  .room-image-actions {
    display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; margin-top: 1.2rem;
  }
  .room-image-actions .room-image-done { margin-left: auto; }

  .room-slot-grid {
    display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem;
  }
  @media (max-width: 560px) {
    .room-slot-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  .room-slot {
    border: 1px solid var(--border); border-radius: 10px; overflow: hidden;
    background: rgba(255,255,255,0.02);
  }
  .room-slot.is-primary { border-color: var(--accent); }
  .room-slot-thumb {
    position: relative; height: 96px; background-size: cover; background-position: center;
    background-color: rgba(255,255,255,0.03); cursor: pointer;
  }
  .room-slot-thumb.is-empty {
    display: flex; align-items: center; justify-content: center;
    color: var(--fg-muted); font-size: 1.3rem;
  }
  .room-slot-badge {
    position: absolute; top: 6px; left: 6px; padding: 0.15rem 0.45rem; border-radius: 999px;
    background: rgba(0,0,0,0.65); color: #fff; font-size: 0.56rem;
    letter-spacing: 0.1em; text-transform: uppercase;
  }
  .room-slot-row {
    display: flex; align-items: center; justify-content: space-between; gap: 0.4rem;
    padding: 0.45rem 0.55rem;
  }
  .room-slot-name {
    color: var(--fg-muted); font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase;
  }
  .room-slot-btn {
    border: 1px solid var(--border); background: transparent; color: var(--fg);
    border-radius: 999px; padding: 0.2rem 0.55rem; font-size: 0.58rem;
    letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer;
    font-family: var(--font-body, 'Outfit', sans-serif); transition: all 0.15s;
  }
  .room-slot-btn:hover { border-color: var(--accent); color: var(--accent); }
  .room-slot-clear {
    border: none; background: none; color: var(--danger, #fb7185);
    font-size: 0.6rem; cursor: pointer; padding: 0;
    font-family: var(--font-body, 'Outfit', sans-serif);
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
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.82rem; font-weight: 600;
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
  .room-cal-weekdays { margin-bottom: 0.35rem; }
  .room-cal-weekdays span {
    font-size: 0.62rem; letter-spacing: 0.06em; text-transform: uppercase;
    color: var(--fg-muted); text-align: center;
  }
  .room-cal-day {
    aspect-ratio: 1; border-radius: 8px; border: 1px solid transparent;
    background: rgba(255,255,255,0.02); color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.74rem; cursor: default;
    display: flex; align-items: center; justify-content: center;
  }
  .room-cal-day.is-blank { visibility: hidden; }
  .room-cal-day.is-past { color: var(--fg-muted); opacity: 0.35; }
  .room-cal-day.is-booked { background: rgba(244,63,94,0.14); color: var(--danger, #fb7185); }
  .room-cal-legend { display: flex; flex-wrap: wrap; gap: 0.9rem; margin-top: 0.75rem; }
  .room-cal-legend span {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.68rem; color: var(--fg-muted);
  }
  .room-cal-swatch { width: 10px; height: 10px; border-radius: 3px; display: inline-block; background: rgba(255,255,255,0.08); }
  .room-cal-swatch.is-booked { background: var(--danger, #fb7185); }
  .room-cal-swatch.is-past { background: var(--fg-muted); opacity: 0.5; }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Additive only — nothing above this block is touched, so a Template 1
     team (or one that hasn't chosen a template yet) renders unchanged. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d;
  }
  :root[data-ops-theme="2"] .room-status-badge.status-reserved { background: #f3e8ff; color: #7e22ce; border-color: #e9d5ff; }
  :root[data-ops-theme="2"] .room-status-badge.status-occupied { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
  :root[data-ops-theme="2"] .rm-table th { background: rgba(27,67,50,0.04); }
  :root[data-ops-theme="2"] .rm-table td { border-bottom-color: var(--border); }
  :root[data-ops-theme="2"] .booking-input,
  :root[data-ops-theme="2"] .room-cal { background: rgba(27,67,50,0.03); }
  :root[data-ops-theme="2"] .room-cal-day { background: rgba(27,67,50,0.035); }
  :root[data-ops-theme="2"] .room-cal-day.is-booked { background: rgba(225,29,72,0.1); }
  :root[data-ops-theme="2"] .room-cal-swatch { background: rgba(27,67,50,0.12); }
  /* ── Manage Rooms (mr-) ──────────────────────────────────────────────────
     Reads the shell's tokens, the same way the Housekeeping pages do, so it
     follows Template 1, Template 2 and a team's own site colours. Shape rule:
     pills for status and tabs, 10px for buttons and fields, 14px for panels
     and cards. Guest Details below keeps its own rm- styles. */
  :root[data-ops-theme="2"] { --warn: #b45309; }
  .mr {
    --mr-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --mr-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --mr-line: var(--border);
    --mr-ok: var(--success, #4ade80);
    --mr-warn: var(--warn, #f59e0b);
    --mr-bad: var(--danger, #fb7185);
    color: var(--fg);
    display: grid; gap: 1.25rem;
  }
  .mr-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
  .mr-eyebrow { color: var(--accent); font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; margin: 0 0 0.5rem; }
  .mr-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .mr-lead { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 66ch; line-height: 1.5; }
  .mr-head-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

  .mr-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem;
    font: 600 0.88rem/1.15 var(--font-body, 'Outfit', sans-serif);
    padding: 0.8rem 1.15rem; border-radius: 10px; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); background: transparent; color: var(--accent);
    transition: background 0.15s, transform 0.1s, filter 0.15s;
  }
  .mr-btn:hover { background: var(--mr-tint); }
  .mr-btn:active { transform: translateY(1px); }
  .mr-btn.is-solid { background: var(--accent); color: var(--bg); }
  .mr-btn.is-solid:hover { filter: brightness(1.08); }
  .mr-btn.is-quiet { border-color: var(--mr-line); color: var(--fg-muted); }
  .mr-btn.is-quiet:hover { color: var(--fg); background: var(--mr-soft); }
  .mr-btn.is-small { padding: 0.62rem 0.95rem; font-size: 0.84rem; }
  .mr-btn.is-wide { width: 100%; }
  .mr-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; filter: none; }
  .mr-btn:focus-visible, .mr-tab:focus-visible, .mr-chip:focus-visible, .mr-link:focus-visible, .mr-close:focus-visible, .mr-photos:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  .mr-how { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
  .mr-how li { display: flex; gap: 0.7rem; align-items: flex-start; padding: 0.85rem 0.95rem; border-radius: 14px; background: var(--mr-soft); border: 1px solid var(--mr-line); }
  .mr-how-num { flex: none; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.78rem; background: var(--mr-tint); color: var(--accent); }
  .mr-how div b { display: block; font-size: 0.86rem; color: var(--fg); margin-bottom: 0.15rem; }
  .mr-how div span { display: block; font-size: 0.78rem; color: var(--fg-muted); line-height: 1.4; }

  .mr-panel { background: var(--card); border: 1px solid var(--mr-line); border-radius: 14px; padding: 1.2rem 1.3rem 1.4rem; }
  .mr-panel-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .mr-panel-head h2 { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--fg); }
  .mr-panel-head p { margin: 0.25rem 0 0; font-size: 0.84rem; color: var(--fg-muted); }
  .mr-live { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--fg-muted); }
  .mr-live::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--mr-ok); }

  .mr .tone-ok, .mr-modal .tone-ok       { background: color-mix(in srgb, var(--mr-ok) 16%, transparent);   color: var(--mr-ok); }
  .mr .tone-warn, .mr-modal .tone-warn   { background: color-mix(in srgb, var(--mr-warn) 16%, transparent); color: var(--mr-warn); }
  .mr .tone-brand, .mr-modal .tone-brand { background: var(--mr-tint); color: var(--accent); }

  .mr-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; margin-bottom: 1.1rem; }
  .mr-stat { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 0.95rem; border-radius: 12px; border: 1px solid var(--mr-line); background: var(--mr-soft); }
  .mr-stat-icon { flex: none; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; }
  .mr-stat div b { display: block; font-size: 1.35rem; line-height: 1.1; font-variant-numeric: tabular-nums; color: var(--fg); }
  .mr-stat div span { display: block; font-size: 0.78rem; color: var(--fg-muted); }

  .mr-types { display: grid; gap: 0.5rem; margin-bottom: 0.9rem; }
  .mr-tabs { display: flex; flex-wrap: wrap; gap: 0.4rem; }
  .mr-tab { display: inline-flex; align-items: center; gap: 0.5rem; font: 600 0.84rem/1 var(--font-body, 'Outfit', sans-serif); padding: 0.6rem 0.9rem; border-radius: 999px; cursor: pointer; border: 1px solid var(--mr-line); background: var(--mr-soft); color: var(--fg-muted); transition: background 0.15s, color 0.15s, border-color 0.15s; }
  .mr-tab:hover { color: var(--fg); border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .mr-tab.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .mr-tab.is-new { border-style: dashed; background: transparent; color: var(--accent); }
  .mr-count { min-width: 1.45rem; padding: 0.2rem 0.4rem; border-radius: 999px; text-align: center; font-size: 0.74rem; font-variant-numeric: tabular-nums; background: color-mix(in srgb, var(--fg) 8%, transparent); }
  .mr-tab.is-on .mr-count { background: color-mix(in srgb, var(--bg) 22%, transparent); }

  .mr-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .mr-showing { margin: 0; font-size: 0.86rem; color: var(--fg-muted); display: flex; align-items: center; gap: 0.4rem 0.9rem; flex-wrap: wrap; }
  .mr-showing b { color: var(--fg); }
  .mr-link { display: inline-flex; align-items: center; gap: 0.4rem; background: none; border: 0; padding: 0.25rem 0; cursor: pointer; color: var(--accent); font: 600 0.84rem/1 var(--font-body, 'Outfit', sans-serif); }
  .mr-link:hover { text-decoration: underline; }
  .mr-search { position: relative; flex: 1 1 240px; max-width: 340px; }
  .mr-search i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.8rem; pointer-events: none; }
  .mr-search .mr-input { padding-left: 2.3rem; }

  .mr-input {
    box-sizing: border-box; width: 100%;
    background: var(--mr-soft); border: 1px solid var(--mr-line);
    border-radius: 10px; padding: 0.75rem 0.9rem; color: var(--fg);
    font: 400 0.9rem/1.4 var(--font-body, 'Outfit', sans-serif);
    outline: none; transition: border-color 0.15s;
  }
  .mr-input:focus { border-color: var(--accent); }
  .mr-input::placeholder { color: var(--fg-muted); opacity: 0.7; }
  .mr-input.has-error { border-color: var(--mr-bad); }
  textarea.mr-input { resize: vertical; min-height: 4.6rem; }

  /* Room cards: one height per row, button at the bottom. */
  .mr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(270px, 100%), 1fr)); gap: 1rem; align-items: stretch; }
  .mr-card { min-width: 0; border: 1px solid var(--mr-line); border-radius: 14px; background: var(--mr-soft); overflow: hidden; display: flex; flex-direction: column; }
  .mr-card-img { position: relative; aspect-ratio: 16 / 9; background: var(--mr-soft); overflow: hidden; }
  .mr-card-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; }
  .mr-card-img .mr-pill { position: absolute; top: 10px; left: 10px; background: var(--card); box-shadow: 0 2px 10px rgba(0,0,0,0.25); }
  .mr-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.76rem; font-weight: 600; white-space: nowrap; }
  .mr-card-body { padding: 0.9rem 1rem 1rem; display: flex; flex-direction: column; gap: 0.7rem; flex: 1; }
  .mr-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
  .mr-name { margin: 0; font-size: 1.08rem; font-weight: 700; color: var(--fg); line-height: 1.25; overflow-wrap: anywhere; }
  .mr-type { display: block; font-size: 0.78rem; font-weight: 600; color: var(--accent); margin-top: 0.2rem; }
  .mr-price { text-align: right; flex: none; }
  .mr-price b { display: block; font-size: 1.02rem; color: var(--fg); font-variant-numeric: tabular-nums; }
  .mr-price small { display: block; font-size: 0.7rem; color: var(--fg-muted); }
  .mr-desc { margin: 0; font-size: 0.83rem; line-height: 1.5; color: var(--fg-muted); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 3em; }
  .mr-desc.is-empty { font-style: italic; opacity: 0.7; }
  .mr-next { margin: auto 0 0; display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--fg); padding: 0.55rem 0.7rem; border-radius: 10px; background: var(--card); border: 1px solid var(--mr-line); }
  .mr-next i { color: var(--fg-muted); }
  .mr-more { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem; font-size: 0.82rem; color: var(--fg-muted); }

  .mr-empty { border: 1.5px dashed var(--mr-line); border-radius: 14px; padding: 2.2rem 1.5rem; text-align: center; }
  .mr-empty-icon { width: 56px; height: 56px; margin: 0 auto 0.9rem; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; background: var(--mr-tint); color: var(--accent); }
  .mr-empty h3 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .mr-empty p { margin: 0.4rem auto 0; max-width: 52ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }
  .mr-empty .mr-btn { margin-top: 1.1rem; }

  /* Dialogs. Below .room-image-overlay (260), which opens on top of them. */
  .mr-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; padding: 1.25rem; z-index: 200; }
  .mr-modal {
    --mr-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --mr-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --mr-line: var(--border);
    --mr-ok: var(--success, #4ade80);
    --mr-bad: var(--danger, #fb7185);
    box-sizing: border-box; background: var(--card); color: var(--fg); border: 1px solid var(--mr-line); border-radius: 14px; width: 100%; max-width: 580px; max-height: 92vh; overflow-y: auto;
  }
  .mr-modal.is-small { max-width: 460px; }
  .mr-modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.35rem 0; }
  .mr-modal-head h2 { margin: 0; font-size: 1.45rem; line-height: 1.2; color: var(--fg); }
  .mr-modal-head p { margin: 0.35rem 0 0; font-size: 0.84rem; color: var(--fg-muted); line-height: 1.45; }
  .mr-close { flex: none; width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--mr-line); background: transparent; color: var(--fg-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; }
  .mr-close:hover { color: var(--fg); background: var(--mr-soft); }
  .mr-form { padding: 1.1rem 1.35rem 1.35rem; display: grid; gap: 1.05rem; }
  .mr-field { display: grid; gap: 0.4rem; align-content: start; }
  .mr-label { font-size: 0.86rem; font-weight: 600; color: var(--fg); }
  .mr-label em { font-style: normal; font-weight: 400; color: var(--fg-muted); }
  .mr-help { margin: 0; font-size: 0.76rem; color: var(--fg-muted); line-height: 1.45; }
  .mr-error { margin: 0; font-size: 0.78rem; color: var(--mr-bad); }
  .mr-money { position: relative; }
  .mr-money span { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.9rem; pointer-events: none; }
  .mr-money .mr-input { padding-left: 1.8rem; }
  .mr-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; }
  .mr-chip { padding: 0.55rem 0.9rem; border-radius: 999px; border: 1px solid var(--mr-line); background: var(--mr-soft); color: var(--fg); cursor: pointer; font: 500 0.84rem/1.2 var(--font-body, 'Outfit', sans-serif); transition: border-color 0.15s, background 0.15s; }
  .mr-chip:hover { border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .mr-chip.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .mr-note { margin: 0; display: flex; gap: 0.55rem; align-items: flex-start; padding: 0.75rem 0.85rem; border-radius: 10px; border: 1px solid var(--mr-line); background: var(--mr-soft); font-size: 0.84rem; line-height: 1.45; color: var(--fg-muted); }
  .mr-note i { margin-top: 0.2rem; }
  .mr-note.is-ok { color: var(--fg); border-color: color-mix(in srgb, var(--accent) 45%, transparent); background: var(--mr-tint); }
  .mr-note.is-ok i { color: var(--accent); }
  .mr-photos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; padding: 0; border: 0; background: none; cursor: pointer; }
  .mr-photo { position: relative; height: 84px; border-radius: 10px; border: 1px solid var(--mr-line); background-color: var(--mr-soft); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; color: var(--accent); }
  .mr-photo.is-empty { border-style: dashed; }
  .mr-photos:hover .mr-photo { border-color: var(--accent); }
  .mr-photo small { position: absolute; top: 5px; left: 5px; padding: 0.12rem 0.45rem; border-radius: 999px; background: rgba(0,0,0,0.65); color: #fff; font-size: 0.62rem; }
  .mr-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
  .mr-actions .mr-btn { flex: 1 1 auto; }

  @media (max-width: 860px) {
    .mr-how, .mr-stats { grid-template-columns: 1fr; }
  }
  @media (max-width: 560px) {
    .mr-head-actions, .mr-head-actions .mr-btn, .mr-search { width: 100%; max-width: none; }
  }
  /* Guest Details (shares the mr- look) */
  .mr-guests { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(320px, 100%), 1fr)); gap: 1rem; align-items: stretch; }
  .mr-guest { min-width: 0; display: flex; flex-direction: column; gap: 0.75rem; padding: 1rem 1.05rem 1.05rem; border-radius: 14px; border: 1px solid var(--mr-line); background: var(--mr-soft); }
  .mr-guest.is-over { border-color: color-mix(in srgb, var(--mr-bad) 55%, transparent); }
  .mr-guest-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
  .mr-guest-facts { list-style: none; margin: 0; padding: 0.65rem 0.75rem; border-radius: 10px; background: var(--card); border: 1px solid var(--mr-line); display: grid; gap: 0.4rem; }
  .mr-guest-facts li { display: flex; gap: 0.55rem; align-items: flex-start; font-size: 0.84rem; color: var(--fg); }
  .mr-guest-facts li i { width: 1rem; text-align: center; color: var(--fg-muted); margin-top: 0.2rem; font-size: 0.78rem; }
  .mr-time { margin: 0; display: flex; gap: 0.5rem; align-items: flex-start; padding: 0.6rem 0.75rem; border-radius: 10px; font-size: 0.84rem; line-height: 1.45; font-variant-numeric: tabular-nums; }
  .mr-time i { margin-top: 0.2rem; }
  .mr-time.is-ok   { background: color-mix(in srgb, var(--mr-ok) 10%, transparent); color: var(--fg); }
  .mr-time.is-ok i { color: var(--mr-ok); }
  .mr-time.is-soon { background: color-mix(in srgb, var(--mr-warn) 12%, transparent); color: var(--fg); font-weight: 600; }
  .mr-time.is-soon i { color: var(--mr-warn); }
  .mr-time.is-over { background: color-mix(in srgb, var(--mr-bad) 12%, transparent); color: var(--mr-bad); font-weight: 700; }
  .mr-time.is-idle { background: var(--card); border: 1px solid var(--mr-line); color: var(--fg-muted); }
  .mr-owed { margin: 0; font-size: 0.84rem; color: var(--fg-muted); display: flex; align-items: center; gap: 0.4rem; }
  .mr-owed b { color: var(--mr-bad); }
  .mr-guest-actions { display: flex; gap: 0.5rem; margin-top: auto; }
  .mr-guest-actions .mr-btn { flex: 1 1 0; }
  .mr-tab i { font-size: 0.78rem; }

  .mr-section { display: grid; gap: 0.55rem; }
  .mr-section h3 { margin: 0; padding-bottom: 0.35rem; border-bottom: 1px solid var(--mr-line); font-size: 0.74rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--accent); }
  .mr-facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.6rem 1rem; }
  .mr-fact small { display: block; font-size: 0.74rem; color: var(--fg-muted); }
  .mr-fact span { display: block; font-size: 0.88rem; color: var(--fg); overflow-wrap: anywhere; }
  .mr-notes { margin: 0; font-size: 0.86rem; line-height: 1.55; color: var(--fg); white-space: pre-wrap; }
  .mr-bill { display: grid; gap: 0.15rem; }
  .mr-bill div { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.86rem; color: var(--fg-muted); padding: 0.25rem 0; }
  .mr-bill b { color: var(--fg); font-weight: 600; font-variant-numeric: tabular-nums; white-space: nowrap; }
  .mr-bill .is-total { border-top: 2px solid var(--accent); margin-top: 0.35rem; padding-top: 0.55rem; color: var(--fg); font-weight: 700; }
  .mr-bill .is-total b { font-size: 1.05rem; font-weight: 700; }
  .mr-bill .is-owed { color: var(--mr-bad); font-weight: 700; }
  .mr-bill .is-owed b { color: var(--mr-bad); font-weight: 700; }
  .mr-bill .is-clear b { color: var(--mr-ok); }
  .mr-payments { display: grid; gap: 0.5rem; }
  .mr-payments > div { padding: 0.6rem 0.75rem; border-radius: 10px; border: 1px solid var(--mr-line); background: var(--mr-soft); }
  .mr-payments > div > div { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.86rem; color: var(--fg); }
  .mr-payments small { display: block; margin-top: 0.2rem; font-size: 0.76rem; color: var(--fg-muted); }
  .mr-modal { --mr-warn: var(--warn, #f59e0b); }
  @media (max-width: 560px) { .mr-facts { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_ROOMMANAGEMENT_URL = @json(route('students.dashboard', ['section' => 'tasks']));
  window.HMS_ROOM_MGMT_INITIAL_NAV = @json(request()->query('nav', 'manage-room'));
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef, useMemo, useId } = React;

// What every team starts with. Room Management can add categories of its own while
// customising the Rooms section of the site; the server sends the team's full list
// down with the rooms, and these five only stand in until it arrives.
const DEFAULT_ROOM_CATEGORIES = ['Classic', 'Superior', 'Deluxe', 'Premium', 'Family'];
let ROOM_CATEGORIES = DEFAULT_ROOM_CATEGORIES.slice();
/* Module-level rather than a prop: normalizeRoomCategory() is called from several
   render paths. The App holds the same list in state, so a change re-renders. */
/* A stored photo's address carries the team's name, so it can hold a space, and an
   unquoted url() ends at the first one: the whole declaration was dropped and the
   slot went black. Quoted, with the two characters that would end a quoted url
   escaped. */
function cssUrl(src) {
  return 'url("' + String(src == null ? '' : src).replace(/["\\]/g, '\\$&') + '")';
}

function setRoomCategoryNames(names) {
  if (Array.isArray(names) && names.length) ROOM_CATEGORIES = names.slice();
}
const BLOCK_HOURS = 12;
const IMAGE_MAX_DIMENSION = 1280;
const IMAGE_MAX_BYTES = 600 * 1024;

/* hotel_rooms.status is not read on this page at all now: it is a housekeeping
   condition (Available / Cleaning / Maintenance) that Housekeeping owns, and it
   answered none of the questions Room Management asks here. Occupancy comes off
   the booking (see bookingStatusClass) and availability off the calendar. */

/* Booking-lifecycle badge (Booked / Checked In) — reuses the room-status-badge
   colours (purple/blue) for a different meaning now that hotel_rooms.status no
   longer tracks occupancy. */
function bookingStatusClass(status) {
  return String(status || '').trim() === 'Checked In' ? 'status-occupied' : 'status-reserved';
}
/* Falls back to the team's first category, not to a literal "Classic" — the Rooms tab
   bar can rename that one, so it stops being an answer once somebody does. */
function normalizeRoomCategory(value) {
  const raw = String(value || '').trim().toLowerCase();
  const match = ROOM_CATEGORIES.find(c => c.toLowerCase() === raw);
  return match || ROOM_CATEGORIES[0] || 'Classic';
}
function reservationArrivalStatus(reservation) {
  const raw = String((reservation && reservation.arrivalStatus) || 'Booked').trim().toLowerCase();
  return raw === 'arrived' ? 'Arrived' : 'Booked';
}
function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0';
  return '₱' + n.toLocaleString();
}
function stayBlocks(checkIn, checkOut, checkInTime) {
  if (!checkIn || !checkOut) return 1;
  const clock = /^\d{1,2}:\d{2}/.test(String(checkInTime || '')) ? checkInTime : '00:00';
  const start = new Date(`${checkIn}T${clock}`);
  const end = new Date(`${checkOut}T${clock}`);
  const hours = (end - start) / 3600000;
  if (!Number.isFinite(hours) || hours <= 0) return 1;
  return Math.max(1, Math.ceil(hours / BLOCK_HOURS));
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
function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

/* ── Room availability calendar — same shape as the hotel site's Rooms page,
   read-only here since Room Management doesn't take reservations. ──────── */

function todayStr() {
  const d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

/* 'to' is the checkout date and is exclusive — the guest is gone by morning,
   so that day is free for the next booking. */
function bookedDateSet(ranges) {
  const set = new Set();
  (ranges || []).forEach(r => {
    if (!r || !r.from || !r.to) return;
    let cursor = r.from;
    let guard = 0;
    while (cursor < r.to && guard < 800) {
      set.add(cursor);
      const [y, m, d] = cursor.split('-').map(Number);
      const next = new Date(y, m - 1, d + 1);
      cursor = next.getFullYear() + '-' + String(next.getMonth() + 1).padStart(2, '0') + '-' + String(next.getDate()).padStart(2, '0');
      guard += 1;
    }
  });
  return set;
}

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

function RoomAvailabilityCalendar({ ranges }) {
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
    <div className="room-cal">
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
          const cls = ['room-cal-day'];
          if (isPast) cls.push('is-past');
          if (isBooked) cls.push('is-booked');
          return <span key={day} className={cls.join(' ')}>{Number(day.slice(8, 10))}</span>;
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

/* End of a paid stay: the booked check-in datetime plus the 12-hour blocks
   booked. Built with the same local-time parsing stayBlocks() uses, so the
   countdown and the Total on the same row can never disagree. */
function stayEndsAt(reservation) {
  if (!reservation || !reservation.checkIn) return null;
  const clock = /^\d{1,2}:\d{2}/.test(String(reservation.checkInTime || '')) ? reservation.checkInTime : '00:00';
  const start = new Date(`${reservation.checkIn}T${clock}`);
  if (Number.isNaN(start.getTime())) return null;
  const blocks = stayBlocks(reservation.checkIn, reservation.checkOut, reservation.checkInTime);
  return new Date(start.getTime() + blocks * BLOCK_HOURS * 3600000);
}

/* How much of the stay is left, as a label plus a tone that drives colour only.
   Only a guest Room Management has actually checked in gets a running clock — a
   reservation nobody has moved into yet has no stay to count down. */
function remainingStay(reservation, now) {
  if ((reservation && reservation.status) !== 'Checked In') {
    return { text: 'Not checked in', tone: 'idle' };
  }
  const endsAt = stayEndsAt(reservation);
  if (!endsAt) return { text: '—', tone: 'idle' };

  const ms = endsAt.getTime() - now;
  const totalSeconds = Math.floor(Math.abs(ms) / 1000);
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  if (ms <= 0) return { text: `Overdue ${hours}h ${minutes}m`, tone: 'over' };
  // Inside the last hour the minutes alone barely move; seconds make it read as live.
  if (hours < 1) return { text: `${minutes}m ${seconds}s`, tone: 'soon' };
  return { text: `${hours}h ${minutes}m`, tone: hours < 2 ? 'soon' : 'ok' };
}

const STAY_TONE_COLORS = { ok: 'var(--fg)', soon: '#fbbf24', over: 'var(--danger, #fb7185)', idle: 'var(--fg-muted)' };

/* A one-second tick. Aligned to the next whole second so every row flips
   together, and repainted on visibilitychange because a backgrounded tab
   throttles timers — the same handling the header clock partial uses. */
function useNow(intervalMs) {
  const [now, setNow] = useState(() => Date.now());
  useEffect(() => {
    let timer = null;
    const align = setTimeout(() => {
      setNow(Date.now());
      timer = setInterval(() => setNow(Date.now()), intervalMs);
    }, intervalMs - (Date.now() % intervalMs));
    const onVisible = () => { if (!document.hidden) setNow(Date.now()); };
    document.addEventListener('visibilitychange', onVisible);
    return () => {
      clearTimeout(align);
      if (timer) clearInterval(timer);
      document.removeEventListener('visibilitychange', onVisible);
    };
  }, [intervalMs]);
  return now;
}

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
    } catch (e) { done(src); }
  };
  img.onerror = function () { done(src); };
  img.src = src;
}

/** Open a file picker and return an image data-URL. */
function pickImageFile(onPicked) {
  const handle = (url) => {
    if (typeof onPicked !== 'function') return;
    compressImageDataUrl(url, onPicked);
  };
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.style.display = 'none';
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

// No name: adding a room derives it from the category (see nextRoomNameFor).
// When opened from a category tab, carry that category into the form instead of
// silently falling back to the first category in the inventory.
function createEmptyRoomForm(category) {
  return { category: String(category || '').trim(), status: 'Available', price: '', desc: '', imgs: toRoomSlots(null) };
}

/* Mirrors App\Support\HotelRoomDefaults: each category numbers from its own hundreds
   block, and the next room takes the highest number already used there plus one. Only
   a preview — the server recomputes it on save, so the two cannot disagree on disk. */
/* Replaced by the blocks the server sends with the categories, which is what a team's
   own categories number from — these five only stand in until that lands. */
let CATEGORY_FLOORS = { Classic: 1, Superior: 2, Deluxe: 3, Premium: 4, Family: 5 };

function setCategoryFloors(map) {
  if (map && Object.keys(map).length) CATEGORY_FLOORS = map;
}

function nextRoomNameFor(rooms, category) {
  const cat = normalizeRoomCategory(category);
  const floor = CATEGORY_FLOORS[cat] || 1;
  let highest = floor * 100;
  const pattern = new RegExp('^' + cat + '\\s+(\\d+)$', 'i');

  (rooms || []).forEach(room => {
    if (normalizeRoomCategory(room.category || room.label) !== cat) return;
    const match = pattern.exec(String(room.name || '').trim());
    if (match) highest = Math.max(highest, parseInt(match[1], 10));
  });

  return cat + ' ' + (highest + 1);
}

/* requireName is false when adding: the name is derived from the category there, so
   there is no field for anyone to leave blank. The edit form still takes one. */
/* A room with no photo of its own — every seeded room starts that way — would render
   <img src=""> and leave a blank box in the table. Same stand-in the hotel site uses,
   seeded by the room so it keeps the same photo between renders. */
function roomCardImg(room) {
  if (room && room.img) return room.img;
  const seed = encodeURIComponent((room && (room.id || room.name)) || 'room');
  return 'https://picsum.photos/seed/room-' + seed + '/800/600.jpg';
}

/* How many photographs one room keeps. Mirrors HotelRoom::GALLERY_MAX — the
   server bounds the list too, so the two cannot disagree on disk. */
const ROOM_GALLERY_MAX = 3;

/* The three slots as the form holds them: a fixed-length list, so slot 2 stays
   slot 2 when slot 1 is cleared and the server is handed the order on screen. */
function toRoomSlots(room) {
  const slots = new Array(ROOM_GALLERY_MAX).fill('');
  const saved = (room && Array.isArray(room.imgs) && room.imgs.length)
    ? room.imgs
    : [(room && room.img) || ''];
  saved.slice(0, ROOM_GALLERY_MAX).forEach((url, i) => { slots[i] = url || ''; });
  return slots;
}

/* What goes on the wire: the first slot is the room's primary photograph, the
   rest are its gallery. Blanks are dropped so a cleared middle slot does not
   reach the server as an empty string. */
function roomSlotsPayload(slots) {
  const list = (slots || []).map(u => String(u || '').trim()).filter(Boolean);
  return { image: list[0] || '', gallery: list.slice(1) };
}

/* Room photographs are chosen in their own dialog rather than straight from the
   file picker, so each one can be looked at before it is kept — the same three-up
   grid the home page's slider images are replaced through. It opens on top of the
   Add or Edit dialog, which is why it portals to the body instead of nesting. */
function RoomImageModal({ open, slots, onChange, onClose }) {
  if (!open) return null;

  const list = toRoomSlots({ imgs: slots });

  const replaceAt = (i) => pickImageFile((url) => {
    if (!url) return;
    const next = list.slice();
    next[i] = url;
    onChange(next);
  });

  const clearAt = (i) => {
    const next = list.slice();
    next[i] = '';
    onChange(next);
  };

  return ReactDOM.createPortal(
    <div
      className="room-modal-overlay room-image-overlay"
      role="dialog"
      aria-modal="true"
      aria-label="Room photos"
      /* React portals still bubble through the React tree, so without this a
         click in here would also reach the Add or Edit overlay behind it. */
      onClick={e => { e.stopPropagation(); onClose(); }}
    >
      <div className="room-modal room-image-modal" onClick={e => e.stopPropagation()}>
        <h3 className="room-image-title">Room photos</h3>
        <p className="room-image-hint">
          Guests flip through these on the room card. Click a photo to replace it. Photo 1 is
          the main one, shown wherever there is space for only one.
        </p>

        <div className="room-slot-grid">
          {list.map((url, i) => (
            <div key={i} className={`room-slot${i === 0 ? ' is-primary' : ''}`}>
              <div
                className={`room-slot-thumb${url ? '' : ' is-empty'}`}
                style={url ? { backgroundImage: cssUrl(url) } : undefined}
                onClick={() => replaceAt(i)}
                role="button"
                aria-label={(url ? 'Replace photo ' : 'Choose photo ') + (i + 1)}
              >
                {!url && <i className="fa-solid fa-plus"></i>}
                {i === 0 && url && <span className="room-slot-badge">Main</span>}
              </div>
              <div className="room-slot-row">
                <span className="room-slot-name">Photo {i + 1}</span>
                {url
                  ? <button type="button" className="room-slot-clear" onClick={() => clearAt(i)}>Remove</button>
                  : null}
                <button type="button" className="room-slot-btn" onClick={() => replaceAt(i)}>
                  {url ? 'Replace' : 'Add'}
                </button>
              </div>
            </div>
          ))}
        </div>

        <div className="room-image-actions">
          <span className="room-image-hint" style={{ margin: 0 }}>
            A room with no photo gets a sample picture.
          </span>
          <button type="button" className="btn-outline room-image-done" onClick={onClose} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>Done</button>
        </div>
      </div>
    </div>,
    document.body
  );
}

function validateRoomForm(form, requireName = true) {
  const errors = {};
  if (requireName && !String(form.name || '').trim()) errors.name = 'Room name is required.';
  if (!String(form.category || '').trim()) errors.category = 'Room category is required.';
  const price = parseFloat(String(form.price || '').replace(/,/g, ''));
  if (!String(form.price || '').trim() || !Number.isFinite(price) || price <= 0) {
    errors.price = 'Enter a valid price.';
  }
  return errors;
}

/* ── Manage Rooms: plain-language cards and dialogs ─────────────────────────
   Everything below reads the shell's tokens through the mr- classes, the same
   way the Housekeeping pages do. "Category" is called "room type" on screen;
   the requests and field names underneath are unchanged. */

// SweetAlert draws outside the page's CSS, so it gets the live token values.
function themeColor(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

function mrAlert(icon, title, text, opts) {
  if (!window.Swal) return;
  window.Swal.fire(Object.assign({
    icon, title, text,
    background: themeColor('--card', '#181714'), color: themeColor('--fg', '#f5f0e8'),
    iconColor: icon === 'success' ? themeColor('--success', '#4ade80')
      : icon === 'warning' ? themeColor('--warn', '#f59e0b')
      : themeColor('--danger', '#fb7185'),
    confirmButtonColor: themeColor('--accent', '#c9a84c'),
    confirmButtonText: 'OK',
    timer: icon === 'success' ? 3000 : undefined,
    timerProgressBar: icon === 'success',
  }, opts || {}));
}

function useEscapeKey(onClose) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);
}

function mrPlural(n, one, many) { return n + ' ' + (n === 1 ? one : many); }

/* Where a room stands today, read off its open booking. hotel_rooms.status is
   Housekeeping's and is still not read here — see the note at the top. */
function roomNow(room) {
  const r = room && room.reservation;
  if (r && r.status === 'Checked In') return { key: 'guest', label: 'Guest staying', icon: 'fa-user', tone: 'tone-brand' };
  if (r) return { key: 'booked', label: 'Booked', icon: 'fa-calendar-check', tone: 'tone-warn' };
  return { key: 'free', label: 'Free now', icon: 'fa-circle-check', tone: 'tone-ok' };
}

function formatShortDay(iso) {
  if (!iso) return '';
  const d = new Date(iso + 'T00:00:00');
  if (Number.isNaN(d.getTime())) return iso;
  return d.toLocaleDateString([], { month: 'short', day: 'numeric' });
}

/* The next booked stretch from today on, for the card's one-line summary. */
function nextBooking(room) {
  const today = todayStr();
  const ranges = (room && room.bookedRanges) || [];
  const upcoming = ranges
    .filter(r => r && r.from && r.to && r.to > today)
    .sort((a, b) => (a.from < b.from ? -1 : 1));
  return upcoming[0] || null;
}

function MrModalHead({ titleId, title, text, onClose }) {
  return (
    <div className="mr-modal-head">
      <div>
        <h2 id={titleId} className="font-display">{title}</h2>
        {text ? <p>{text}</p> : null}
      </div>
      <button type="button" className="mr-close" onClick={onClose} aria-label="Close">
        <i className="fa-solid fa-xmark"></i>
      </button>
    </div>
  );
}

/* Renaming a room type, in the page's own chrome. window.prompt() would work, but it
   announces the hostname above the question — "hms-….onrender.com says" — which reads
   like the site is talking to you from outside itself. */
function RenameCategoryModal({ from, saving, error, onSubmit, onCancel }) {
  const [name, setName] = useState(from || '');
  const uid = useId();
  useEscapeKey(onCancel);

  const clean = name.trim();
  const canSave = !!clean && clean !== from && !saving;
  const submit = (e) => {
    if (e) e.preventDefault();
    if (canSave) onSubmit(clean);
  };

  return (
    <div className="mr-overlay" onClick={onCancel}>
      <div className="mr-modal is-small" role="dialog" aria-modal="true" aria-labelledby={uid + '-t'} onClick={e => e.stopPropagation()}>
        <MrModalHead titleId={uid + '-t'} title={`Rename "${from}"`} text="The rooms of this type are renamed too, here and on your hotel website." onClose={onCancel} />
        <form onSubmit={submit} className="mr-form" noValidate>
          <div className="mr-field">
            <label className="mr-label" htmlFor={uid + '-n'}>New name</label>
            <input
              id={uid + '-n'} type="text" className={`mr-input ${error ? 'has-error' : ''}`}
              value={name} maxLength={60} autoFocus
              onChange={e => setName(e.target.value)}
            />
            {error
              ? <p className="mr-error">{error}</p>
              : <p className="mr-help">Example: "{from} 101" becomes "{clean || 'New name'} 101".</p>}
          </div>
          <div className="mr-actions">
            <button type="button" className="mr-btn is-quiet" onClick={onCancel}>Cancel</button>
            <button type="submit" className="mr-btn is-solid" disabled={!canSave}>
              <i className="fa-solid fa-pen"></i> {saving ? 'Renaming…' : 'Rename'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

/* A room type the team invents. The rate seeds the price of every room created under
   it, and a type stored at zero would price its rooms at nothing, so it is required
   here exactly as it is in the site's own Add Room Category dialog — same default,
   same floor. */
function AddCategoryModal({ saving, error, onSubmit, onCancel }) {
  const [name, setName] = useState('');
  const [rate, setRate] = useState('2000');
  const uid = useId();
  useEscapeKey(onCancel);

  const clean = name.trim();
  const parsedRate = parseInt(String(rate).replace(/[^0-9]/g, ''), 10) || 0;
  const canSave = !!clean && parsedRate > 0 && !saving;
  const submit = (e) => {
    if (e) e.preventDefault();
    if (canSave) onSubmit(clean, parsedRate);
  };

  return (
    <div className="mr-overlay" onClick={onCancel}>
      <div className="mr-modal is-small" role="dialog" aria-modal="true" aria-labelledby={uid + '-t'} onClick={e => e.stopPropagation()}>
        <MrModalHead titleId={uid + '-t'} title="Add a room type" text="A new group of rooms, like Executive or Suite. It also shows as a tab on the Rooms page of your hotel website." onClose={onCancel} />
        <form onSubmit={submit} className="mr-form" noValidate>
          <div className="mr-field">
            <label className="mr-label" htmlFor={uid + '-n'}>Room type name</label>
            <input
              id={uid + '-n'} type="text" className={`mr-input ${error ? 'has-error' : ''}`}
              value={name} maxLength={60} autoFocus placeholder="Example: Executive"
              onChange={e => setName(e.target.value)}
            />
            <p className="mr-help">Rooms you add to it are numbered for you, like "{clean || 'Executive'} 101".</p>
          </div>
          <div className="mr-field">
            <label className="mr-label" htmlFor={uid + '-r'}>Price for each 12-hour stay</label>
            <div className="mr-money"><span>₱</span>
              <input id={uid + '-r'} type="text" inputMode="numeric" className="mr-input" value={rate} maxLength={9} placeholder="2000" onChange={e => setRate(e.target.value)} />
            </div>
            <p className="mr-help">New rooms of this type start at this price. You can change each room later.</p>
          </div>
          {error ? <p className="mr-error">{error}</p> : null}
          <div className="mr-actions">
            <button type="button" className="mr-btn is-quiet" onClick={onCancel}>Cancel</button>
            <button type="submit" className="mr-btn is-solid" disabled={!canSave}>
              <i className="fa-solid fa-plus"></i> {saving ? 'Adding…' : 'Add room type'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

/* The room-type picker both room dialogs share: big chips, one per type. */
function TypeChips({ categories, value, onChange }) {
  return (
    <div className="mr-chips" role="radiogroup" aria-label="Room type">
      {categories.map(c => (
        <button key={c} type="button" role="radio" aria-checked={value === c} className={`mr-chip ${value === c ? 'is-on' : ''}`} onClick={() => onChange(c)}>
          {c}
        </button>
      ))}
    </div>
  );
}

/* The three photo slots, shown small in the dialog with one button to change them. */
function PhotoStrip({ imgs, onOpen }) {
  const slots = toRoomSlots({ imgs });
  const chosen = slots.filter(Boolean).length;
  return (
    <div className="mr-field">
      <span className="mr-label">Photos <em>(up to {ROOM_GALLERY_MAX})</em></span>
      <button type="button" className="mr-photos" onClick={onOpen}>
        {slots.map((url, i) => (
          <span key={i} className={`mr-photo ${url ? '' : 'is-empty'}`} style={url ? { backgroundImage: cssUrl(url) } : undefined}>
            {!url ? <i className="fa-solid fa-plus"></i> : null}
            {i === 0 && url ? <small>Main</small> : null}
          </span>
        ))}
      </button>
      <p className="mr-help">
        {chosen ? `${chosen} of ${ROOM_GALLERY_MAX} chosen. Click to change them.` : 'Click to add photos. Guests flip through them on the room card.'}
      </p>
    </div>
  );
}

/* Adding a room is the rare move; looking one up is the common one — so the form
   lives in a dialog and the page leads with the rooms. Same POST, same validation. */
function AddRoomModal({ rooms, categories, defaultCategory, onClose, onAdded }) {
  const [form, setForm] = useState(() => createEmptyRoomForm(defaultCategory));
  const [errors, setErrors] = useState({});
  const [imgModal, setImgModal] = useState(false);
  const [saving, setSaving] = useState(false);
  const uid = useId();
  const typeList = (categories && categories.length ? categories : DEFAULT_ROOM_CATEGORIES);

  // Preview only — HotelRoomDefaults::nextNameFor() decides the real one on save.
  const nextRoomName = form.category ? nextRoomNameFor(rooms || [], form.category) : '';

  useEscapeKey(onClose);

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const nextErrors = validateRoomForm(form, false);
    if (nextErrors.category) nextErrors.category = 'Pick a room type first.';
    if (nextErrors.price) nextErrors.price = 'Type a price above 0.';
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length) return;

    setSaving(true);
    fetch('/students/hotel/rooms', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      // No name: the server numbers the room from its category (Classic 110 -> 111).
      body: JSON.stringify(Object.assign({
        category: form.category,
        price: parseInt(String(form.price).replace(/,/g, ''), 10),
        description: String(form.desc || '').trim(),
      }, roomSlotsPayload(form.imgs))),
    })
      .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e)))
      .then(data => {
        if (data.room && typeof onAdded === 'function') onAdded(data.room);
        onClose();
        const warned = !!data.image_warning;
        // A warning waits to be read; a plain success does not.
        mrAlert(warned ? 'warning' : 'success',
          warned ? 'Room added without its photo' : 'Room added',
          warned ? data.image_warning : data.room.name + ' is now on the list and can be booked.');
      })
      .catch((err) => {
        const msg = (err && err.message) ? err.message : 'Could not save. Please try again.';
        if (window.Swal) mrAlert('error', 'Not saved', msg);
        // Category, not name: the add form has no name field to show it under.
        else setErrors({ category: msg });
      })
      .finally(() => setSaving(false));
  };

  return (
    <div className="mr-overlay" onClick={onClose}>
      <div className="mr-modal" role="dialog" aria-modal="true" aria-labelledby={uid + '-t'} onClick={e => e.stopPropagation()}>
        <MrModalHead titleId={uid + '-t'} title="Add a room" text="The room can be booked as soon as you save it." onClose={onClose} />

        <form onSubmit={handleSubmit} className="mr-form" noValidate>
          <div className="mr-field">
            <span className="mr-label">What type of room?</span>
            <TypeChips categories={typeList} value={form.category} onChange={c => update('category', c)} />
            {errors.category ? <p className="mr-error">{errors.category}</p> : null}
          </div>

          {/* Not typed: the server numbers a new room from its category. This only
              previews what it will be called, so the name cannot drift from the
              sequence. The server recomputes it on save either way. */}
          <p className={`mr-note ${form.category ? 'is-ok' : ''}`}>
            <i className={`fa-solid ${form.category ? 'fa-hashtag' : 'fa-circle-info'}`}></i>
            <span>{form.category
              ? <>This room will be called <b>{nextRoomName}</b>. The number is given for you.</>
              : 'Pick a room type and the room number is given for you.'}</span>
          </p>

          <div className="mr-field">
            <label className="mr-label" htmlFor={uid + '-p'}>Price for each 12-hour stay</label>
            <div className="mr-money"><span>₱</span>
              <input
                id={uid + '-p'} type="number" min="1" step="1" inputMode="numeric"
                className={`mr-input ${errors.price ? 'has-error' : ''}`} placeholder="4500"
                value={form.price} onChange={e => update('price', e.target.value)}
              />
            </div>
            {errors.price ? <p className="mr-error">{errors.price}</p> : <p className="mr-help">A guest staying 24 hours pays this twice.</p>}
          </div>

          <div className="mr-field">
            <label className="mr-label" htmlFor={uid + '-d'}>Short description <em>(optional)</em></label>
            <textarea
              id={uid + '-d'} className="mr-input" rows={3} placeholder="Example: Queen bed, city view and a work desk."
              value={form.desc} onChange={e => update('desc', e.target.value)}
            />
            <p className="mr-help">Guests read this on your hotel website.</p>
          </div>

          <PhotoStrip imgs={form.imgs} onOpen={() => setImgModal(true)} />

          <div className="mr-actions">
            <button type="button" className="mr-btn is-quiet" onClick={onClose}>Cancel</button>
            <button type="submit" className="mr-btn is-solid" disabled={saving}>
              <i className="fa-solid fa-plus"></i> {saving ? 'Saving…' : 'Add this room'}
            </button>
          </div>
        </form>
      </div>

      <RoomImageModal
        open={imgModal}
        slots={form.imgs}
        onChange={next => update('imgs', next)}
        onClose={() => setImgModal(false)}
      />
    </div>
  );
}

/* The Edit button on a room card. Edits the room's own fields; the booked dates
   below stay read-only — they belong to bookings, not to the room. */
function EditRoomModal({ room, categories, onClose, onSaved }) {
  const [form, setForm] = useState(() => ({
    name: room.name || '',
    category: normalizeRoomCategory(room.category || room.label),
    price: String(room.price || ''),
    desc: room.desc || '',
    imgs: toRoomSlots(room),
  }));
  const [errors, setErrors] = useState({});
  const [imgModal, setImgModal] = useState(false);
  const [saving, setSaving] = useState(false);
  const uid = useId();
  const typeList = (categories && categories.length ? categories : DEFAULT_ROOM_CATEGORIES);
  const now = roomNow(room);

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  useEscapeKey(onClose);

  const handleSubmit = (e) => {
    e.preventDefault();
    const nextErrors = validateRoomForm(form);
    if (nextErrors.name) nextErrors.name = 'Type the room name.';
    if (nextErrors.category) nextErrors.category = 'Pick a room type.';
    if (nextErrors.price) nextErrors.price = 'Type a price above 0.';
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length) return;

    setSaving(true);
    // room.dbId is the hotel_rooms primary key; room.id is the front-end's "db-N".
    fetch('/students/hotel/rooms/' + room.dbId, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      // The slots are handed back whole, the untouched ones as the /storage/...
      // URLs they arrived as: the server collapses those to the paths it already
      // holds rather than re-uploading them.
      body: JSON.stringify(Object.assign({
        name: String(form.name).trim(),
        category: form.category,
        price: parseInt(String(form.price).replace(/,/g, ''), 10),
        description: String(form.desc || '').trim(),
      }, roomSlotsPayload(form.imgs))),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        if (data.room && typeof onSaved === 'function') onSaved(data.room);
        onClose();
        const warned = !!data.image_warning;
        mrAlert(warned ? 'warning' : 'success',
          warned ? 'Saved without the new photo' : 'Changes saved',
          warned ? data.image_warning : data.room.name + ' has been updated.');
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Could not save. Please try again.';
        if (window.Swal) mrAlert('error', 'Not saved', msg);
        else setErrors({ name: msg });
      })
      .finally(() => setSaving(false));
  };

  return (
    <div className="mr-overlay" onClick={onClose}>
      <div className="mr-modal" role="dialog" aria-modal="true" aria-labelledby={uid + '-t'} onClick={e => e.stopPropagation()}>
        <MrModalHead
          titleId={uid + '-t'}
          title={`Edit ${room.name}`}
          text={now.key === 'guest' && room.reservation
            ? `${room.reservation.fullName || 'A guest'} is staying here now.`
            : now.key === 'booked' && room.reservation
              ? `Booked by ${room.reservation.fullName || 'a guest'}.`
              : 'Nobody is in this room right now.'}
          onClose={onClose}
        />

        <form onSubmit={handleSubmit} className="mr-form" noValidate>
          <div className="mr-field">
            <label className="mr-label" htmlFor={uid + '-n'}>Room name</label>
            <input
              id={uid + '-n'} type="text" className={`mr-input ${errors.name ? 'has-error' : ''}`} value={form.name}
              onChange={e => update('name', e.target.value)}
            />
            {errors.name ? <p className="mr-error">{errors.name}</p> : <p className="mr-help">Usually the type and number, like "Classic 101".</p>}
          </div>

          <div className="mr-field">
            <span className="mr-label">Room type</span>
            <TypeChips categories={typeList} value={form.category} onChange={c => update('category', c)} />
            {errors.category ? <p className="mr-error">{errors.category}</p> : null}
          </div>

          <div className="mr-field">
            <label className="mr-label" htmlFor={uid + '-p'}>Price for each 12-hour stay</label>
            <div className="mr-money"><span>₱</span>
              <input
                id={uid + '-p'} type="number" min="1" step="1" inputMode="numeric"
                className={`mr-input ${errors.price ? 'has-error' : ''}`} value={form.price}
                onChange={e => update('price', e.target.value)}
              />
            </div>
            {errors.price ? <p className="mr-error">{errors.price}</p> : <p className="mr-help">Bookings already made keep the price they were booked at.</p>}
          </div>

          <div className="mr-field">
            <label className="mr-label" htmlFor={uid + '-d'}>Short description <em>(optional)</em></label>
            <textarea id={uid + '-d'} className="mr-input" rows={3} value={form.desc} onChange={e => update('desc', e.target.value)} />
          </div>

          <PhotoStrip imgs={form.imgs} onOpen={() => setImgModal(true)} />

          <div className="mr-actions">
            <button type="button" className="mr-btn is-quiet" onClick={onClose}>Cancel</button>
            <button type="submit" className="mr-btn is-solid" disabled={saving}>
              <i className="fa-solid fa-floppy-disk"></i> {saving ? 'Saving…' : 'Save changes'}
            </button>
          </div>

          <div className="mr-field">
            <span className="mr-label">Booked dates</span>
            <p className="mr-help" style={{ marginTop: '-0.2rem' }}>Red days are already booked. Only the Front Desk can book or move a stay.</p>
            <RoomAvailabilityCalendar ranges={room.bookedRanges} />
          </div>
        </form>
      </div>

      <RoomImageModal
        open={imgModal}
        slots={form.imgs}
        onChange={next => update('imgs', next)}
        onClose={() => setImgModal(false)}
      />
    </div>
  );
}

function RoomCard({ room, onEdit }) {
  const now = roomNow(room);
  const next = nextBooking(room);
  const today = todayStr();

  let bookingLine = 'No upcoming bookings';
  if (next) {
    bookingLine = next.from <= today
      ? `Booked until ${formatShortDay(next.to)}`
      : `Next booking: ${formatShortDay(next.from)} to ${formatShortDay(next.to)}`;
  }

  return (
    <article className="mr-card">
      <div className="mr-card-img">
        <img src={roomCardImg(room)} alt={room.name} loading="lazy" />
        <span className={`mr-pill ${now.tone}`}><i className={`fa-solid ${now.icon}`}></i>{now.label}</span>
      </div>
      <div className="mr-card-body">
        <div className="mr-card-top">
          <div style={{ minWidth: 0 }}>
            <h3 className="mr-name">{room.name}</h3>
            <span className="mr-type">{room.label || room.category}</span>
          </div>
          <div className="mr-price">
            <b>{formatPeso(room.price)}</b>
            <small>per 12 hours</small>
          </div>
        </div>
        <p className={`mr-desc ${room.desc ? '' : 'is-empty'}`}>{room.desc || 'No description yet.'}</p>
        <p className="mr-next">
          <i className="fa-regular fa-calendar"></i>
          <span>{now.key === 'guest' && room.reservation ? `${room.reservation.fullName || 'Guest'} is staying` : bookingLine}</span>
        </p>
        <button type="button" className="mr-btn is-small is-wide" onClick={() => onEdit(room)}>
          <i className="fa-solid fa-pen"></i> Edit room
        </button>
      </div>
    </article>
  );
}

const MR_PAGE = 12;

function ManageRoomPanel({ rooms, categories, onSubmit, onRoomUpdated, onAddCategory, onRenameCategory, onToast }) {
  // The inventory list that used to be its own Room Availability section. Adding a
  // room and looking one up are the same job, so they share a screen.
  const [tab, setTab] = useState('All');
  const [search, setSearch] = useState('');
  const [shown, setShown] = useState(MR_PAGE);
  const [selectedRoomId, setSelectedRoomId] = useState(null);
  const [addOpen, setAddOpen] = useState(false);
  // The type the rename dialog is open on, or null when it is closed.
  const [renameFrom, setRenameFrom] = useState(null);
  const [renameSaving, setRenameSaving] = useState(false);
  const [renameError, setRenameError] = useState('');
  const [categoryOpen, setCategoryOpen] = useState(false);
  const [categorySaving, setCategorySaving] = useState(false);
  const [categoryError, setCategoryError] = useState('');

  const list = rooms || [];
  const categoryNames = (categories && categories.length) ? categories : DEFAULT_ROOM_CATEGORIES;
  const tabs = ['All', ...categoryNames];
  const q = search.trim().toLowerCase();
  const filtered = list
    .filter(r => tab === 'All' || normalizeRoomCategory(r.category || r.label) === tab)
    .filter(r => !q || [r.name, r.desc, r.reservation && r.reservation.fullName].some(v => String(v || '').toLowerCase().includes(q)));
  const visible = filtered.slice(0, shown);
  const selectedRoom = list.find(r => r.id === selectedRoomId) || null;

  const counts = list.reduce((t, r) => { t[roomNow(r).key] += 1; return t; }, { free: 0, booked: 0, guest: 0 });

  const pickTab = (t) => { setTab(t); setShown(MR_PAGE); };

  /* Renaming a type renames the category for the whole team — the rooms in it are
     renamed with it, and the hotel site's own Rooms tabs follow on their next poll. */
  const submitRename = (to) => {
    if (typeof onRenameCategory !== 'function' || !renameFrom) return;
    const from = renameFrom;
    setRenameSaving(true);
    setRenameError('');

    Promise.resolve(onRenameCategory(from, to)).then((renamed) => {
      setRenameSaving(false);
      if (!renamed) {
        // Kept open with the typed name still in it — the fix is usually one word.
        setRenameError('That name is already used. Pick another.');
        return;
      }
      setRenameFrom(null);
      // Follow the rename: the tab this panel was filtering on is gone by that name.
      setTab(prev => (prev === from ? renamed : prev));
      if (onToast) onToast(`${from} is now ${renamed}`);
    });
  };

  /* A new type is a write against the team, so the server decides whether the name
     is free and hands back the whole list; the tabs are redrawn from that rather than
     from what was typed. A duplicate comes back as null. */
  const handleAddCategory = (name, rate) => {
    if (typeof onAddCategory !== 'function') return;
    setCategorySaving(true);
    setCategoryError('');
    Promise.resolve(onAddCategory(name, rate)).then((created) => {
      if (!created) {
        setCategoryError('That room type already exists.');
        return;
      }
      setCategoryOpen(false);
      pickTab(created);
      if (onToast) onToast(`${created} added. You can now add rooms to it.`);
    }).finally(() => setCategorySaving(false));
  };

  return (
    <div className="mr">
      <header className="mr-head">
        <div>
          <p className="mr-eyebrow">Room Management</p>
          <h1 className="font-display">Manage Rooms</h1>
          <p className="mr-lead">
            Every room guests can book on your hotel website. Add new rooms, change a room's
            price, description or photos, and see which dates are already booked.
          </p>
        </div>
        <div className="mr-head-actions">
          <a href={window.HMS_ROOMMANAGEMENT_URL} className="mr-btn">
            <i className="fa-solid fa-arrow-left"></i> Back to Tasks
          </a>
          <button type="button" className="mr-btn is-solid" onClick={() => setAddOpen(true)}>
            <i className="fa-solid fa-plus"></i> Add a room
          </button>
        </div>
      </header>

      <ol className="mr-how" aria-label="Good to know">
        <li><span className="mr-how-num"><i className="fa-solid fa-layer-group"></i></span><div><b>Rooms are grouped by type</b><span>Like Classic or Deluxe. Pick a type below to see only those rooms.</span></div></li>
        <li><span className="mr-how-num"><i className="fa-solid fa-hashtag"></i></span><div><b>Numbers are given for you</b><span>A new Classic room after Classic 110 becomes Classic 111.</span></div></li>
        <li><span className="mr-how-num"><i className="fa-solid fa-clock"></i></span><div><b>Prices are per 12 hours</b><span>A guest staying a full day pays the price twice.</span></div></li>
      </ol>

      <section className="mr-panel" aria-labelledby="mr-list">
        <div className="mr-panel-head">
          <div>
            <h2 id="mr-list">Your rooms</h2>
            <p>{list.length ? `${mrPlural(list.length, 'room', 'rooms')} in the hotel.` : 'No rooms yet.'}</p>
          </div>
          <span className="mr-live">Updates on its own</span>
        </div>

        {list.length ? (
          <div className="mr-stats">
            <div className="mr-stat"><span className="mr-stat-icon tone-ok"><i className="fa-solid fa-circle-check"></i></span><div><b>{counts.free}</b><span>Free now</span></div></div>
            <div className="mr-stat"><span className="mr-stat-icon tone-warn"><i className="fa-solid fa-calendar-check"></i></span><div><b>{counts.booked}</b><span>Booked, guest not in yet</span></div></div>
            <div className="mr-stat"><span className="mr-stat-icon tone-brand"><i className="fa-solid fa-user"></i></span><div><b>{counts.guest}</b><span>Guest staying now</span></div></div>
          </div>
        ) : null}

        <div className="mr-types">
          <span className="mr-label">Room types</span>
          <div className="mr-tabs" role="group" aria-label="Show rooms by type">
            {tabs.map(t => {
              const count = t === 'All' ? list.length : list.filter(r => normalizeRoomCategory(r.category || r.label) === t).length;
              return (
                <button key={t} type="button" aria-pressed={tab === t} className={`mr-tab ${tab === t ? 'is-on' : ''}`} onClick={() => pickTab(t)}>
                  {t === 'All' ? 'All rooms' : t}
                  <span className="mr-count">{count}</span>
                </button>
              );
            })}
            {/* Dashed so it reads as "make a new one" rather than as another type. */}
            {onAddCategory && (
              <button type="button" className="mr-tab is-new" onClick={() => { setCategoryError(''); setCategoryOpen(true); }}>
                <i className="fa-solid fa-plus"></i> New room type
              </button>
            )}
          </div>
        </div>

        <div className="mr-toolbar">
          <p className="mr-showing">
            {tab === 'All' ? 'Showing all rooms' : <>Showing <b>{tab}</b> rooms</>}
            {/* "All" is not a type, so it is the one tab with nothing to rename. */}
            {onRenameCategory && tab !== 'All' ? (
              <button type="button" className="mr-link" onClick={() => { setRenameError(''); setRenameFrom(tab); }}>
                <i className="fa-solid fa-pen"></i> Rename {tab}
              </button>
            ) : null}
          </p>
          {list.length > 0 ? (
            <div className="mr-search">
              <i className="fa-solid fa-magnifying-glass"></i>
              <input type="text" className="mr-input" placeholder="Search a room or guest name" aria-label="Search a room or guest name" value={search} onChange={e => { setSearch(e.target.value); setShown(MR_PAGE); }} />
            </div>
          ) : null}
        </div>

        {filtered.length === 0 ? (
          <div className="mr-empty">
            <div className="mr-empty-icon"><i className={`fa-solid ${q ? 'fa-magnifying-glass' : 'fa-bed'}`}></i></div>
            <h3>{q ? 'No room matches your search' : list.length === 0 ? 'No rooms yet' : `No ${tab} rooms yet`}</h3>
            <p>{q ? 'Check the spelling, or clear the search box.' : 'Use "Add a room" to create one. Guests can book it right away.'}</p>
            {!q ? (
              <button type="button" className="mr-btn is-solid" onClick={() => setAddOpen(true)}>
                <i className="fa-solid fa-plus"></i> Add a room
              </button>
            ) : null}
          </div>
        ) : (
          <>
            <div className="mr-grid">
              {visible.map(room => <RoomCard key={room.id} room={room} onEdit={r => setSelectedRoomId(r.id)} />)}
            </div>
            <div className="mr-more">
              <span>Showing {visible.length} of {filtered.length}</span>
              {visible.length < filtered.length ? (
                <button type="button" className="mr-btn is-small" onClick={() => setShown(s => s + MR_PAGE)}>
                  <i className="fa-solid fa-chevron-down"></i> Show {Math.min(MR_PAGE, filtered.length - visible.length)} more
                </button>
              ) : null}
            </div>
          </>
        )}
      </section>

      {addOpen && (
        <AddRoomModal
          rooms={list}
          categories={categoryNames}
          defaultCategory={tab === 'All' ? '' : tab}
          onClose={() => setAddOpen(false)}
          onAdded={onSubmit}
        />
      )}

      {categoryOpen && (
        <AddCategoryModal
          saving={categorySaving}
          error={categoryError}
          onSubmit={handleAddCategory}
          onCancel={() => { setCategoryOpen(false); setCategoryError(''); }}
        />
      )}

      {renameFrom && (
        <RenameCategoryModal
          key={renameFrom}
          from={renameFrom}
          saving={renameSaving}
          error={renameError}
          onSubmit={submitRename}
          onCancel={() => { setRenameFrom(null); setRenameError(''); }}
        />
      )}

      {selectedRoom && (
        <EditRoomModal
          room={selectedRoom}
          categories={categoryNames}
          onClose={() => setSelectedRoomId(null)}
          onSaved={onRoomUpdated}
        />
      )}
    </div>
  );
}

function formatStamp(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
    + ' · ' + d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

/* "Fri, Oct 9 · 2:00 PM" from a stored date and clock time. */
function formatDayTime(date, time) {
  if (!date) return '—';
  const d = new Date(date + 'T00:00:00');
  const day = Number.isNaN(d.getTime()) ? date : d.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
  const clock = formatClockTime(time);
  return clock ? `${day} · ${clock}` : day;
}

function formatEndsAt(reservation) {
  const end = stayEndsAt(reservation);
  if (!end) return reservation && reservation.checkOut ? formatDayTime(reservation.checkOut) : '—';
  return end.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' })
    + ' · ' + end.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

/* The three booking states, as the person at the desk would say them. */
function guestState(reservation) {
  const status = String((reservation && reservation.status) || '').trim();
  if (status === 'Checked In') return { key: 'in', label: 'Staying now', icon: 'fa-bed', tone: 'tone-ok' };
  if (status === 'Arrived') return { key: 'arrived', label: 'At the hotel, not checked in', icon: 'fa-person-walking-luggage', tone: 'tone-warn' };
  return { key: 'booked', label: 'Booked, not here yet', icon: 'fa-calendar-check', tone: 'tone-brand' };
}

function guestBalance(res) {
  const grand = Number(res.grandTotal) || ((Number(res.totalDue) || 0) + (Number(res.roomServiceTotal) || 0) + (Number(res.addonsTotal) || 0) + (Number(res.otherCharges) || 0));
  const paid = Number(res.amountPaid) || 0;
  return res.outstanding != null ? Number(res.outstanding) : Math.max(0, grand - paid);
}

/*
 * Read-only view of one stay. The card carries only what Room Management scans by
 * (who, where, when, status), so the money detail lives here in full instead.
 */
function GuestDetailsModal({ room, onClose }) {
  const uid = useId();
  useEscapeKey(onClose);

  const res = room && room.reservation;
  if (!res) return null;

  const state = guestState(res);
  const roomTotal = Number(res.totalDue) || 0;
  const service = Number(res.roomServiceTotal) || 0;
  const serviceCount = Number(res.roomServiceCount) || 0;
  const extras = Number(res.otherCharges) || 0;
  const addonsTotal = Number(res.addonsTotal) || 0;
  const addonsCount = Number(res.addonsCount) || 0;
  const grand = Number(res.grandTotal) || (roomTotal + service + addonsTotal + extras);
  const paid = Number(res.amountPaid) || 0;
  const outstanding = res.outstanding != null ? Number(res.outstanding) : Math.max(0, grand - paid);
  const payments = res.payments || [];

  const Fact = ({ label, children }) => (
    <div className="mr-fact"><small>{label}</small><span>{children || '—'}</span></div>
  );

  return (
    <div className="mr-overlay" onClick={onClose}>
      <div className="mr-modal" role="dialog" aria-modal="true" aria-labelledby={uid + '-t'} onClick={e => e.stopPropagation()}>
        <MrModalHead
          titleId={uid + '-t'}
          title={res.fullName || 'Guest'}
          text={`${room.name} · ${room.label || room.category}`}
          onClose={onClose}
        />
        <div className="mr-form">
          <span className={`mr-pill ${state.tone}`} style={{ justifySelf: 'start' }}><i className={`fa-solid ${state.icon}`}></i>{state.label}</span>

          <section className="mr-section">
            <h3>About the guest</h3>
            <div className="mr-facts">
              <Fact label="Full name">{res.fullName}</Fact>
              <Fact label="Phone">{res.contactNo}</Fact>
              <Fact label="Email">{res.email}</Fact>
              <Fact label="ID number">{res.idNumber}</Fact>
            </div>
          </section>

          <section className="mr-section">
            <h3>The stay</h3>
            <div className="mr-facts">
              <Fact label="Room">{room.name}</Fact>
              <Fact label="Price per 12 hours">{formatPeso(res.roomRate || room.price)}</Fact>
              <Fact label="Check-in">{formatDayTime(res.checkIn, res.checkInTime)}</Fact>
              <Fact label="Check-out">{formatEndsAt(res)}</Fact>
              <Fact label="Booked on">{res.reservedAt ? formatStamp(res.reservedAt) : null}</Fact>
              <Fact label="Booked by">{res.bookedBy}</Fact>
              <Fact label="Arrived at the hotel">{res.arrivedAt ? formatStamp(res.arrivedAt) : null}</Fact>
              <Fact label="Checked in">{res.checkedInAt ? formatStamp(res.checkedInAt) : null}</Fact>
            </div>
          </section>

          {res.notes ? (
            <section className="mr-section">
              <h3>Notes</h3>
              <p className="mr-notes">{res.notes}</p>
            </section>
          ) : null}

          <section className="mr-section">
            <h3>Bill so far</h3>
            <div className="mr-bill">
              <div><span>Room</span><b>{formatPeso(roomTotal)}</b></div>
              <div><span>Food ordered to the room{serviceCount > 0 ? ` (${serviceCount})` : ''}</span><b>{formatPeso(service)}</b></div>
              {addonsCount > 0 ? <div><span>Borrowed items ({addonsCount})</span><b>{formatPeso(addonsTotal)}</b></div> : null}
              <div><span>Other charges</span><b>{formatPeso(extras)}</b></div>
              <div className="is-total"><span>Total</span><b>{formatPeso(grand)}</b></div>
              <div><span>Paid</span><b>{formatPeso(paid)}</b></div>
              <div className={outstanding > 0 ? 'is-owed' : 'is-clear'}>
                <span>{outstanding > 0 ? 'Still to pay' : 'Fully paid'}</span><b>{formatPeso(outstanding)}</b>
              </div>
            </div>
          </section>

          <section className="mr-section">
            <h3>Payments</h3>
            {payments.length === 0 ? (
              <p className="mr-help">Nothing has been paid on this stay yet.</p>
            ) : (
              <div className="mr-payments">
                {payments.map(p => (
                  <div key={p.id}>
                    <div><b>{p.type} · {p.method}</b><b>{formatPeso(p.amountPaid)}</b></div>
                    <small>
                      {formatStamp(p.paidAt)}
                      {p.reference ? ` · Ref ${p.reference}` : ''}
                      {p.payerName ? ` · Paid by ${p.payerName}` : ''}
                    </small>
                  </div>
                ))}
              </div>
            )}
          </section>

          <div className="mr-actions">
            <button type="button" className="mr-btn is-quiet" onClick={onClose}>Close</button>
          </div>
        </div>
      </div>
    </div>
  );
}

function GuestCard({ room, now, onView, onCheckIn }) {
  const res = room.reservation;
  const state = guestState(res);
  const remaining = remainingStay(res, now);
  const owed = guestBalance(res);
  const overdue = remaining.tone === 'over';

  return (
    <article className={`mr-guest ${overdue ? 'is-over' : ''}`}>
      <div className="mr-guest-top">
        <div style={{ minWidth: 0 }}>
          <h3 className="mr-name">{res.fullName || 'Guest'}</h3>
          <span className="mr-type">{room.name} · {room.label || room.category}</span>
        </div>
        <span className={`mr-pill ${state.tone}`}><i className={`fa-solid ${state.icon}`}></i>{state.key === 'in' ? 'Staying now' : state.key === 'arrived' ? 'At the hotel' : 'Not here yet'}</span>
      </div>

      <ul className="mr-guest-facts">
        <li><i className="fa-solid fa-phone"></i><span>{res.contactNo || 'No phone given'}</span></li>
        <li><i className="fa-solid fa-right-to-bracket"></i><span>Check-in: {formatDayTime(res.checkIn, res.checkInTime)}</span></li>
        <li><i className="fa-solid fa-right-from-bracket"></i><span>Check-out: {formatEndsAt(res)}</span></li>
      </ul>

      {state.key === 'in' ? (
        <p className={`mr-time is-${remaining.tone}`}>
          <i className={`fa-solid ${overdue ? 'fa-triangle-exclamation' : 'fa-hourglass-half'}`}></i>
          <span>{overdue ? `Stay time is up. ${remaining.text}.` : `Time left: ${remaining.text}`}</span>
        </p>
      ) : (
        <p className="mr-time is-idle">
          <i className="fa-solid fa-circle-info"></i>
          <span>{state.key === 'arrived' ? 'The guest is at the hotel. Check them in when the room is ready.' : 'Check the guest in when they arrive.'}</span>
        </p>
      )}

      {owed > 0 ? <p className="mr-owed"><i className="fa-solid fa-peso-sign"></i> Still to pay: <b>{formatPeso(owed)}</b></p> : null}

      <div className="mr-guest-actions">
        <button type="button" className="mr-btn is-small" onClick={() => onView(room)}>
          <i className="fa-solid fa-eye"></i> See details
        </button>
        {state.key !== 'in' ? (
          <button type="button" className="mr-btn is-solid is-small" onClick={() => onCheckIn(room)}>
            <i className="fa-solid fa-key"></i> Check in
          </button>
        ) : null}
      </div>
    </article>
  );
}

const GUEST_TABS = [
  { key: 'all',     label: 'All guests',          icon: 'fa-users',          match: () => true },
  { key: 'waiting', label: 'Waiting to check in', icon: 'fa-calendar-check', match: r => r.reservation.status !== 'Checked In' },
  { key: 'in',      label: 'Staying now',         icon: 'fa-bed',            match: r => r.reservation.status === 'Checked In' },
];

function GuestDetailsPanel({ rooms, onBookingAction, onToast }) {
  const now = useNow(1000);
  const [tab, setTab] = useState('all');
  const [search, setSearch] = useState('');
  // The See details action. Read-only, so it needs no fetch: the room already holds
  // the whole reservation payload the dialog renders. Keyed by room id so a poll
  // refresh swaps in the updated room rather than leaving a stale snapshot open.
  const [detailsRoomId, setDetailsRoomId] = useState(null);
  // `reservation` is only ever projected from an open booking (see
  // HotelRoom::activeBooking()), so its presence alone means the room has a guest —
  // hotel_rooms.status is housekeeping-only and no longer part of this filter.
  const occupied = (rooms || []).filter(r => r.reservation);
  const detailsRoom = occupied.find(r => r.id === detailsRoomId) || null;

  const current = GUEST_TABS.find(t => t.key === tab) || GUEST_TABS[0];
  const q = search.trim().toLowerCase();
  const visible = occupied
    .filter(current.match)
    .filter(r => !q || [r.name, r.reservation.fullName, r.reservation.contactNo].some(v => String(v || '').toLowerCase().includes(q)));

  const staying = occupied.filter(r => r.reservation.status === 'Checked In');
  const waiting = occupied.length - staying.length;
  const endingSoon = staying.filter(r => { const t = remainingStay(r.reservation, now).tone; return t === 'soon' || t === 'over'; }).length;

  // Check-in moves the booking only — the room's own status is untouched. Asked first:
  // it starts the guest's paid clock, and a mis-click on the wrong card is easy.
  const checkInGuest = (room) => {
    if (typeof onBookingAction !== 'function' || !room.reservation) return;
    const go = () => {
      onBookingAction(room.reservation.bookingId, 'check_in');
      if (onToast) onToast(`${room.reservation.fullName || 'Guest'} is checked in to ${room.name}.`);
    };
    if (!window.Swal) { if (window.confirm(`Check in ${room.reservation.fullName || 'this guest'} to ${room.name}?`)) go(); return; }
    window.Swal.fire({
      title: `Check in ${room.reservation.fullName || 'this guest'}?`,
      text: `They get the key to ${room.name}, and their stay time starts now.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, check in',
      cancelButtonText: 'Not yet',
      background: themeColor('--card', '#181714'),
      color: themeColor('--fg', '#f5f0e8'),
      confirmButtonColor: themeColor('--accent', '#c9a84c'),
      cancelButtonColor: '#71717a',
    }).then(r => { if (r.isConfirmed) go(); });
  };

  let emptyTitle = 'No guests right now';
  let emptyText = 'When the Front Desk books a guest into a room, they show up here.';
  if (q) { emptyTitle = 'No guest matches your search'; emptyText = 'Check the spelling, or clear the search box.'; }
  else if (occupied.length && tab === 'waiting') { emptyTitle = 'Nobody is waiting'; emptyText = 'Every booked guest is already checked in.'; }
  else if (occupied.length && tab === 'in') { emptyTitle = 'Nobody is staying yet'; emptyText = 'Guests show here once you check them in.'; }

  return (
    <div className="mr">
      <header className="mr-head">
        <div>
          <p className="mr-eyebrow">Room Management</p>
          <h1 className="font-display">Guest Details</h1>
          <p className="mr-lead">
            Guests staying in a room now, and guests who booked but are not checked in yet.
            Check a guest in when they arrive, and press See details for their contact
            information and bill.
          </p>
        </div>
        <div className="mr-head-actions">
          <a href={window.HMS_ROOMMANAGEMENT_URL} className="mr-btn">
            <i className="fa-solid fa-arrow-left"></i> Back to Tasks
          </a>
        </div>
      </header>

      <ol className="mr-how" aria-label="How it works">
        <li><span className="mr-how-num">1</span><div><b>Front Desk books the guest</b><span>The guest shows here as "Not here yet".</span></div></li>
        <li><span className="mr-how-num">2</span><div><b>You check the guest in</b><span>Press Check in when they arrive. Their stay time starts.</span></div></li>
        <li><span className="mr-how-num">3</span><div><b>Front Desk checks them out</b><span>The guest leaves this list and the room goes to Housekeeping.</span></div></li>
      </ol>

      <section className="mr-panel" aria-labelledby="gd-list">
        <div className="mr-panel-head">
          <div>
            <h2 id="gd-list">Guests</h2>
            <p>{occupied.length ? `${mrPlural(occupied.length, 'room has', 'rooms have')} a guest.` : 'No rooms have a guest right now.'}</p>
          </div>
          <span className="mr-live">Updates on its own</span>
        </div>

        {occupied.length ? (
          <div className="mr-stats">
            <div className="mr-stat"><span className="mr-stat-icon tone-ok"><i className="fa-solid fa-bed"></i></span><div><b>{staying.length}</b><span>Staying now</span></div></div>
            <div className="mr-stat"><span className="mr-stat-icon tone-brand"><i className="fa-solid fa-calendar-check"></i></span><div><b>{waiting}</b><span>Waiting to check in</span></div></div>
            <div className="mr-stat"><span className={`mr-stat-icon ${endingSoon ? 'tone-warn' : 'tone-ok'}`}><i className="fa-solid fa-hourglass-half"></i></span><div><b>{endingSoon}</b><span>Stay ends within 2 hours or is over</span></div></div>
          </div>
        ) : null}

        {occupied.length ? (
          <div className="mr-toolbar">
            <div className="mr-tabs" role="group" aria-label="Show guests">
              {GUEST_TABS.map(t => (
                <button key={t.key} type="button" aria-pressed={tab === t.key} className={`mr-tab ${tab === t.key ? 'is-on' : ''}`} onClick={() => setTab(t.key)}>
                  <i className={`fa-solid ${t.icon}`}></i>{t.label}
                  <span className="mr-count">{occupied.filter(t.match).length}</span>
                </button>
              ))}
            </div>
            <div className="mr-search">
              <i className="fa-solid fa-magnifying-glass"></i>
              <input type="text" className="mr-input" placeholder="Search a guest, phone or room" aria-label="Search a guest, phone or room" value={search} onChange={e => setSearch(e.target.value)} />
            </div>
          </div>
        ) : null}

        {visible.length === 0 ? (
          <div className="mr-empty">
            <div className="mr-empty-icon"><i className={`fa-solid ${q ? 'fa-magnifying-glass' : 'fa-door-open'}`}></i></div>
            <h3>{emptyTitle}</h3>
            <p>{emptyText}</p>
          </div>
        ) : (
          <div className="mr-guests">
            {visible.map(room => (
              <GuestCard key={room.id} room={room} now={now} onView={r => setDetailsRoomId(r.id)} onCheckIn={checkInGuest} />
            ))}
          </div>
        )}
      </section>

      {detailsRoom && (
        <GuestDetailsModal room={detailsRoom} onClose={() => setDetailsRoomId(null)} />
      )}
    </div>
  );
}

function RoomManagementPage({ initialNav, rooms, categories, onAddRoom, onRoomUpdated, onAddCategory, onRenameCategory, onBookingAction, onToast }) {
  const activeNav = initialNav || 'manage-room';

  const handleAddRoom = (payload) => {
    if (typeof onAddRoom === 'function') onAddRoom(payload);
    if (onToast) onToast(`${payload.name} added to Rooms.`);
  };

  return (
    <div style={{ padding: '1.5rem' }} data-hms-no-edit="1">
      {activeNav === 'guest-details' ? (
        <GuestDetailsPanel rooms={rooms} onBookingAction={onBookingAction} onToast={onToast} />
      ) : (
        // Manage Room is the fallback: ?nav=rooms was the old Room Availability
        // section, whose room list lives here now, so an old link still lands
        // somewhere sensible instead of on a blank panel.
        <ManageRoomPanel rooms={rooms} categories={categories} onSubmit={handleAddRoom} onRoomUpdated={onRoomUpdated} onAddCategory={onAddCategory} onRenameCategory={onRenameCategory} onToast={onToast} />
      )}
    </div>
  );
}

function App() {
  const [rooms, setRooms] = useState([]);
  // The team's categories ride along with the rooms, so a category added while
  // customising the site's Rooms section shows up here on the next poll.
  const [categories, setCategories] = useState(DEFAULT_ROOM_CATEGORIES);
  const pendingWrites = useRef(0);

  const fetchRooms = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/rooms', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.rooms)) setRooms(data.rooms);
        if (Array.isArray(data.categories) && data.categories.length) {
          const names = data.categories.map(c => (typeof c === 'string' ? c : c.name)).filter(Boolean);
          const floors = {};
          data.categories.forEach(c => { if (c && c.name && c.floor) floors[c.name] = c.floor; });
          setRoomCategoryNames(names);
          setCategoryFloors(floors);
          setCategories(names);
        }
      })
      .catch(() => {});
  }, []);

  useEffect(() => {
    fetchRooms();
    // Not while the tab is in the background: every poll takes one of the database
    // connections Supabase's pooler shares between all of us (see render.yaml), and
    // nobody is reading a screen they cannot see. Focus brings it straight back.
    const id = setInterval(() => { if (!document.hidden) fetchRooms(); }, 12000);
    window.addEventListener('focus', fetchRooms);
    return () => { clearInterval(id); window.removeEventListener('focus', fetchRooms); };
  }, [fetchRooms]);

  const addRoom = useCallback((roomFromDb) => {
    setRooms(prev => [...prev, roomFromDb]);
  }, []);

  const replaceRoom = useCallback((roomFromDb) => {
    setRooms(prev => prev.map(r => (r.id === roomFromDb.id ? roomFromDb : r)));
  }, []);

  /* Renames a category for the whole team. The rooms in it are renamed with it on the
     server ("Classic 101" becomes "Standard 101"), so both come back here and on the
     hotel site's own Rooms tabs. Resolves to the stored spelling, or null when taken. */
  /* Creates one of the team's categories. The server answers with the whole list,
     so the tabs here and the site's Rooms tab bar agree without a reload; null
     means the name was already taken. */
  const addCategory = useCallback((name, rate) => {
    pendingWrites.current += 1;
    return fetch('/students/hotel/room-categories', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ name: name, rate: rate || null }),
    })
      .then(r => r.json().then(data => (r.ok ? data : Promise.reject(data))))
      .then(data => {
        if (data && Array.isArray(data.categories)) {
          const names = data.categories.map(c => (typeof c === 'string' ? c : c.name)).filter(Boolean);
          const floors = {};
          data.categories.forEach(c => { if (c && c.name && c.floor) floors[c.name] = c.floor; });
          setRoomCategoryNames(names);
          setCategoryFloors(floors);
          setCategories(names);
        }
        return data && data.category ? data.category.name : null;
      })
      .catch(() => null)
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  const renameCategory = useCallback((from, to) => {
    pendingWrites.current += 1;
    return fetch('/students/hotel/room-categories', {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ from, to }),
    })
      .then(r => r.json().then(data => (r.ok ? data : Promise.reject(data))))
      .then(data => {
        if (data && Array.isArray(data.categories)) {
          const names = data.categories.map(c => (typeof c === 'string' ? c : c.name)).filter(Boolean);
          const floors = {};
          data.categories.forEach(c => { if (c && c.name && c.floor) floors[c.name] = c.floor; });
          setRoomCategoryNames(names);
          setCategoryFloors(floors);
          setCategories(names);
        }
        if (data && Array.isArray(data.rooms)) setRooms(data.rooms);
        return data && data.category ? data.category.name : null;
      })
      .catch(() => null)
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  // Check-in and check-out are booking moves. The room status follows on the server,
  // and the response hands the updated room back so this page never has to infer it.
  const bookingAction = useCallback((bookingId, action) => {
    if (!bookingId) return;
    pendingWrites.current += 1;
    fetch('/students/hotel/bookings/' + bookingId, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ action }),
    })
      .then(r => (r.ok ? r.json() : null))
      .then(data => { if (data && data.room) setRooms(prev => prev.map(r => (r.id === data.room.id ? data.room : r))); })
      .catch(() => {})
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  return (
    <RoomManagementPage
      initialNav={window.HMS_ROOM_MGMT_INITIAL_NAV}
      rooms={rooms}
      categories={categories}
      onBack={() => { window.location.href = window.HMS_ROOMMANAGEMENT_URL; }}
      onAddRoom={addRoom}
      onRoomUpdated={replaceRoom}
      onAddCategory={addCategory}
      onRenameCategory={renameCategory}
      onBookingAction={bookingAction}
      onToast={(msg) => window.toast && window.toast(msg)}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
