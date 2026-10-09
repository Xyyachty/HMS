@extends('students.builder.ops-shell')

@section('page-title', 'Amenities')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Only the tokens change; every rule below reads them. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d; --warn: #b45309;
  }

  /* Everything below reads the shell's tokens, the same way Room Inspections and
     Add-ons do, so the page follows Template 1, Template 2 and a team's own site
     colours. Shape rule: pills for status and tabs, 10px for buttons and fields,
     14px for panels and cards. */
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }

  .am {
    --am-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --am-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --am-line: var(--border);
    --am-ok: var(--success, #4ade80);
    --am-warn: var(--warn, #f59e0b);
    --am-bad: var(--danger, #fb7185);
    --am-fix: #a78bfa;
    padding: 1.5rem 1.5rem 3rem;
    color: var(--fg);
    display: grid; gap: 1.25rem;
  }
  :root[data-ops-theme="2"] .am { --am-fix: #7e22ce; }

  /* Page header */
  .am-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
  .am-eyebrow { color: var(--accent); font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; margin: 0 0 0.5rem; }
  .am-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .am-lead { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 66ch; line-height: 1.5; }
  .am-head-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

  .am-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem;
    font: 600 0.88rem/1.15 var(--font-body, 'Outfit', sans-serif);
    padding: 0.8rem 1.15rem; border-radius: 10px; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); background: transparent; color: var(--accent);
    transition: background 0.15s, transform 0.1s, filter 0.15s;
  }
  .am-btn:hover { background: var(--am-tint); }
  .am-btn:active { transform: translateY(1px); }
  .am-btn.is-solid { background: var(--accent); color: var(--bg); }
  .am-btn.is-solid:hover { filter: brightness(1.08); }
  .am-btn.is-quiet { border-color: var(--am-line); color: var(--fg-muted); }
  .am-btn.is-quiet:hover { color: var(--fg); background: var(--am-soft); }
  .am-btn.is-danger { border-color: color-mix(in srgb, var(--am-bad) 45%, transparent); color: var(--am-bad); }
  .am-btn.is-danger:hover { background: color-mix(in srgb, var(--am-bad) 10%, transparent); }
  .am-btn.is-go { border-color: var(--am-ok); background: var(--am-ok); color: var(--bg); }
  .am-btn.is-go:hover { filter: brightness(1.08); }
  .am-btn.is-small { padding: 0.6rem 0.9rem; font-size: 0.82rem; }
  .am-btn.is-wide { width: 100%; }
  .am-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; filter: none; }
  .am-btn:focus-visible, .am-view:focus-visible, .am-choice:focus-within, .am-chip:focus-visible, .am-link:focus-visible, .am-close:focus-visible, .am-photo-pick:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  /* Notices */
  .am-notice { display: flex; gap: 0.6rem; align-items: flex-start; margin: 0; padding: 0.85rem 1rem; border-radius: 14px; font-size: 0.86rem; line-height: 1.5; color: var(--fg); border: 1px solid var(--am-line); background: var(--am-soft); }
  .am-notice i { margin-top: 0.2rem; }
  .am-notice.is-ok   { border-color: color-mix(in srgb, var(--am-ok) 40%, transparent);   background: color-mix(in srgb, var(--am-ok) 9%, transparent); }
  .am-notice.is-ok i { color: var(--am-ok); }
  .am-notice.is-warn { border-color: color-mix(in srgb, var(--am-warn) 40%, transparent); background: color-mix(in srgb, var(--am-warn) 10%, transparent); }
  .am-notice.is-warn i { color: var(--am-warn); }
  .am-notice.is-bad  { border-color: color-mix(in srgb, var(--am-bad) 40%, transparent);  background: color-mix(in srgb, var(--am-bad) 8%, transparent); }
  .am-notice.is-bad i { color: var(--am-bad); }
  .am-notice.is-muted i { color: var(--fg-muted); }

  /* Panels */
  .am-panel { background: var(--card); border: 1px solid var(--am-line); border-radius: 14px; padding: 1.2rem 1.3rem 1.4rem; }
  .am-panel-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .am-panel-head h2 { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--fg); }
  .am-panel-head p { margin: 0.25rem 0 0; font-size: 0.84rem; color: var(--fg-muted); max-width: 70ch; line-height: 1.5; }
  .am-live { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--fg-muted); }
  .am-live::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--am-ok); }

  /* Tones */
  .tone-ok    { background: color-mix(in srgb, var(--am-ok) 16%, transparent);   color: var(--am-ok); }
  .tone-warn  { background: color-mix(in srgb, var(--am-warn) 16%, transparent); color: var(--am-warn); }
  .tone-bad   { background: color-mix(in srgb, var(--am-bad) 14%, transparent);  color: var(--am-bad); }
  .tone-fix   { background: color-mix(in srgb, var(--am-fix) 16%, transparent);  color: var(--am-fix); }
  .tone-brand { background: var(--am-tint); color: var(--accent); }
  .tone-muted { background: color-mix(in srgb, var(--fg) 8%, transparent); color: var(--fg-muted); }

  /* Toolbar */
  .am-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .am-views { box-sizing: border-box; max-width: 100%; display: inline-flex; flex-wrap: wrap; gap: 0.3rem; padding: 0.3rem; border-radius: 999px; background: var(--am-soft); border: 1px solid var(--am-line); }
  .am-view { display: inline-flex; align-items: center; gap: 0.5rem; font: 600 0.84rem/1 var(--font-body, 'Outfit', sans-serif); padding: 0.6rem 0.95rem; border-radius: 999px; cursor: pointer; border: 0; background: transparent; color: var(--fg-muted); transition: background 0.15s, color 0.15s; }
  .am-view:hover { color: var(--fg); }
  .am-view.is-on { background: var(--accent); color: var(--bg); }
  .am-view i { font-size: 0.78rem; }
  .am-count { min-width: 1.45rem; padding: 0.2rem 0.4rem; border-radius: 999px; text-align: center; font-size: 0.74rem; font-variant-numeric: tabular-nums; background: color-mix(in srgb, var(--fg) 8%, transparent); }
  .am-view.is-on .am-count { background: color-mix(in srgb, var(--bg) 22%, transparent); }
  .am-search { position: relative; flex: 1 1 220px; max-width: 320px; }
  .am-search i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.8rem; pointer-events: none; }
  .am-search .am-input { padding-left: 2.3rem; }

  /* Inputs */
  .am-input {
    box-sizing: border-box; width: 100%;
    background: var(--am-soft); border: 1px solid var(--am-line);
    border-radius: 10px; padding: 0.75rem 0.9rem; color: var(--fg);
    font: 400 0.9rem/1.4 var(--font-body, 'Outfit', sans-serif);
    outline: none; transition: border-color 0.15s;
  }
  .am-input:focus { border-color: var(--accent); }
  .am-input::placeholder { color: var(--fg-muted); opacity: 0.7; }
  .am-input.has-error { border-color: var(--am-bad); }
  textarea.am-input { resize: vertical; min-height: 4.6rem; }
  select.am-input { cursor: pointer; }
  select.am-input option { background: var(--card); color: var(--fg); }
  /* The picker paints its own icon white-on-white in Chrome's dark form controls. */
  input[type="time"].am-input::-webkit-calendar-picker-indicator { filter: invert(1); opacity: 0.6; }
  :root[data-ops-theme="2"] input[type="time"].am-input::-webkit-calendar-picker-indicator { filter: none; }
  :root[data-ops-theme="2"] select.am-input { color-scheme: light; }

  /* Facility cards */
  .am-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(310px, 100%), 1fr)); gap: 1rem; align-items: stretch; }
  .am-card { min-width: 0; border: 1px solid var(--am-line); border-radius: 14px; background: var(--am-soft); overflow: hidden; display: flex; flex-direction: column; }
  .am-card.needs-you { border-color: color-mix(in srgb, var(--am-bad) 55%, transparent); }
  .am-card-img { position: relative; aspect-ratio: 16 / 9; background: var(--am-soft); overflow: hidden; }
  .am-card-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; }
  .am-card-img .am-pill { position: absolute; top: 10px; left: 10px; background: var(--card); box-shadow: 0 2px 10px rgba(0,0,0,0.25); }
  .am-card-body { padding: 0.95rem 1.05rem 1.05rem; display: flex; flex-direction: column; gap: 0.75rem; flex: 1; }
  .am-name { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--fg); line-height: 1.25; overflow-wrap: anywhere; }
  .am-how { display: inline-flex; align-items: center; gap: 0.4rem; margin-top: 0.3rem; font-size: 0.78rem; font-weight: 600; color: var(--accent); }
  .am-desc { margin: 0; font-size: 0.84rem; line-height: 1.5; color: var(--fg-muted); display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; min-height: 4.5em; }
  /* Same height in every card, so the facts and buttons line up across a row. */
  .am-desc.is-empty { font-style: italic; opacity: 0.7; }
  .am-facts { list-style: none; margin: 0; padding: 0.65rem 0.75rem; border-radius: 10px; background: var(--card); border: 1px solid var(--am-line); display: grid; gap: 0.4rem; }
  .am-facts li { display: flex; gap: 0.55rem; align-items: flex-start; font-size: 0.84rem; color: var(--fg); }
  .am-facts li i { width: 1rem; text-align: center; color: var(--fg-muted); margin-top: 0.2rem; font-size: 0.8rem; }
  .am-facts li span.is-empty { color: var(--fg-muted); }
  .am-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.76rem; font-weight: 600; white-space: nowrap; }
  .am-card-actions { display: flex; gap: 0.5rem; margin-top: auto; }
  .am-card-actions .am-btn { flex: 1 1 0; }

  .am-repair { display: grid; gap: 0.6rem; padding: 0.8rem 0.85rem; border-radius: 10px; border: 1px solid var(--am-line); }
  .am-repair p { margin: 0; font-size: 0.84rem; line-height: 1.5; color: var(--fg); }
  .am-repair small { display: block; font-size: 0.78rem; color: var(--fg-muted); line-height: 1.45; }
  .am-repair-title { display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.86rem; }
  .am-repair.is-bad  { border-color: color-mix(in srgb, var(--am-bad) 40%, transparent);  background: color-mix(in srgb, var(--am-bad) 7%, transparent); }
  .am-repair.is-bad .am-repair-title { color: var(--am-bad); }
  .am-repair.is-fix  { border-color: color-mix(in srgb, var(--am-fix) 40%, transparent);  background: color-mix(in srgb, var(--am-fix) 8%, transparent); }
  .am-repair.is-fix .am-repair-title { color: var(--am-fix); }
  .am-repair.is-ok   { border-color: color-mix(in srgb, var(--am-ok) 40%, transparent);   background: color-mix(in srgb, var(--am-ok) 8%, transparent); }
  .am-repair.is-ok .am-repair-title { color: var(--am-ok); }

  /* Rows for services and events */
  .am-rows { display: grid; gap: 0.6rem; }
  .am-rowcard { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 0.6rem 1rem; align-items: center; padding: 0.85rem 1rem; border-radius: 12px; background: var(--am-soft); border: 1px solid var(--am-line); }
  .am-rowcard b { display: block; font-size: 0.95rem; color: var(--fg); }
  .am-rowcard small { display: block; font-size: 0.8rem; color: var(--fg-muted); margin-top: 0.2rem; line-height: 1.45; }
  .am-rowcard .am-side { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; justify-content: flex-end; }
  .am-price { font-weight: 700; font-variant-numeric: tabular-nums; color: var(--fg); }
  .am-rowcard.is-event { grid-template-columns: 1fr; }
  .am-event-top { display: flex; justify-content: space-between; gap: 0.6rem 1rem; flex-wrap: wrap; align-items: flex-start; }
  .am-quote { font-style: italic; }

  /* Event set-up and clean-up steps */
  .am-track { list-style: none; margin: 0.2rem 0 0; padding: 0; display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
  .am-track li { position: relative; display: flex; flex-direction: column; align-items: center; gap: 0.35rem; font-size: 0.66rem; color: var(--fg-muted); text-align: center; line-height: 1.25; }
  .am-track li::before { content: ''; position: absolute; top: 6px; left: -50%; width: 100%; height: 2px; background: var(--am-line); }
  .am-track li:first-child::before { display: none; }
  .am-track li.is-done::before, .am-track li.is-now::before { background: var(--accent); }
  .am-dot { position: relative; z-index: 1; width: 14px; height: 14px; border-radius: 50%; background: var(--card); border: 2px solid var(--am-line); }
  .am-track li.is-done .am-dot { background: var(--accent); border-color: var(--accent); }
  .am-track li.is-now .am-dot { background: var(--card); border-color: var(--accent); box-shadow: 0 0 0 4px var(--am-tint); }
  .am-track li.is-now, .am-track li.is-done { color: var(--fg); }
  .am-track li.is-now { font-weight: 700; }

  /* Empty / loading */
  .am-empty { border: 1.5px dashed var(--am-line); border-radius: 14px; padding: 2.2rem 1.5rem; text-align: center; }
  .am-empty-icon { width: 56px; height: 56px; margin: 0 auto 0.9rem; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; background: var(--am-tint); color: var(--accent); }
  .am-empty h3 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .am-empty p { margin: 0.4rem auto 0; max-width: 52ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }
  .am-empty .am-btn { margin-top: 1.1rem; }
  .am-skel { height: 380px; border-radius: 14px; border: 1px solid var(--am-line); background: linear-gradient(90deg, var(--am-soft) 0%, color-mix(in srgb, var(--fg) 8%, transparent) 50%, var(--am-soft) 100%); background-size: 200% 100%; animation: am-shimmer 1.4s ease-in-out infinite; }
  @keyframes am-shimmer { from { background-position: 100% 0; } to { background-position: -100% 0; } }
  @media (prefers-reduced-motion: reduce) { .am-skel { animation: none; } }

  /* Dialogs */
  .am-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; padding: 1.25rem; z-index: 200; }
  .am-modal { box-sizing: border-box; background: var(--card); color: var(--fg); border: 1px solid var(--am-line); border-radius: 14px; width: 100%; max-width: 600px; max-height: 92vh; overflow-y: auto; }
  .am-modal.is-small { max-width: 480px; }
  .am-modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.35rem 0; }
  .am-modal-head h2 { margin: 0; font-size: 1.45rem; line-height: 1.2; color: var(--fg); }
  .am-modal-head p { margin: 0.35rem 0 0; font-size: 0.84rem; color: var(--fg-muted); line-height: 1.45; }
  .am-close { flex: none; width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--am-line); background: transparent; color: var(--fg-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; }
  .am-close:hover { color: var(--fg); background: var(--am-soft); }
  .am-form { padding: 1.1rem 1.35rem 1.35rem; display: grid; gap: 1.05rem; }
  .am-section-title { margin: 0.3rem 0 -0.35rem; font-size: 0.74rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--accent); }
  .am-field { display: grid; gap: 0.4rem; align-content: start; }
  .am-label { font-size: 0.86rem; font-weight: 600; color: var(--fg); }
  .am-label em { font-style: normal; font-weight: 400; color: var(--fg-muted); }
  .am-help { margin: 0; font-size: 0.76rem; color: var(--fg-muted); line-height: 1.45; }
  .am-error { margin: 0; font-size: 0.78rem; color: var(--am-bad); }
  .am-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }
  .am-money { position: relative; }
  .am-money span { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.9rem; pointer-events: none; }
  .am-money .am-input { padding-left: 1.8rem; }

  .am-choices { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem; }
  .am-choices.is-three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
  .am-choice { position: relative; display: flex; gap: 0.6rem; align-items: flex-start; padding: 0.7rem 0.75rem; border-radius: 10px; border: 1px solid var(--am-line); background: var(--card); cursor: pointer; transition: border-color 0.15s, background 0.15s; }
  .am-choice:hover { border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .am-choice input { position: absolute; opacity: 0; pointer-events: none; }
  .am-choice > i { flex: none; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
  .am-choice b { display: block; font-size: 0.84rem; color: var(--fg); line-height: 1.25; }
  .am-choice span { display: block; font-size: 0.74rem; color: var(--fg-muted); margin-top: 0.15rem; line-height: 1.35; }
  .am-choice.is-on { border-color: var(--accent); background: var(--am-tint); }
  .am-choice.is-off { opacity: 0.45; cursor: not-allowed; }

  .am-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; }
  .am-chip { padding: 0.5rem 0.8rem; border-radius: 999px; border: 1px solid var(--am-line); background: var(--card); color: var(--fg); cursor: pointer; font: 500 0.8rem/1.2 var(--font-body, 'Outfit', sans-serif); transition: border-color 0.15s, background 0.15s; }
  .am-chip:hover { border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .am-chip.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); }

  .am-photo-pick { display: block; width: 100%; padding: 0; border: 1.5px dashed var(--am-line); border-radius: 10px; background: var(--am-soft); cursor: pointer; overflow: hidden; color: var(--fg-muted); font: inherit; }
  .am-photo-pick:hover { border-color: var(--accent); }
  .am-photo-pick img { width: 100%; height: 160px; object-fit: cover; display: block; }
  .am-photo-empty { height: 110px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.45rem; font-size: 0.82rem; }
  .am-photo-empty i { font-size: 1.4rem; color: var(--accent); }
  .am-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 0.5rem; }
  .am-shot { position: relative; border-radius: 10px; overflow: hidden; border: 1px solid var(--am-line); }
  .am-shot img { width: 100%; height: 80px; object-fit: cover; display: block; }
  .am-shot button { position: absolute; top: 5px; right: 5px; width: 26px; height: 26px; border-radius: 8px; border: 0; background: rgba(12,11,9,0.8); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; }
  .am-shot-add { height: 82px; border-radius: 10px; border: 1.5px dashed var(--am-line); background: transparent; color: var(--fg-muted); cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.3rem; font: 500 0.78rem/1.2 var(--font-body, 'Outfit', sans-serif); }
  .am-shot-add:hover { border-color: var(--accent); color: var(--fg); }
  .am-shot-add i { color: var(--accent); }
  .am-link { background: none; border: 0; padding: 0.2rem 0; cursor: pointer; color: var(--fg-muted); font: 500 0.78rem/1 var(--font-body, 'Outfit', sans-serif); justify-self: start; }
  .am-link:hover { color: var(--fg); text-decoration: underline; }
  .am-check { display: flex; gap: 0.6rem; align-items: flex-start; padding: 0.75rem 0.85rem; border-radius: 10px; border: 1px solid var(--am-line); background: var(--am-soft); cursor: pointer; font-size: 0.84rem; line-height: 1.45; color: var(--fg); }
  .am-check input { margin-top: 0.2rem; accent-color: var(--accent); width: 16px; height: 16px; }
  .am-check small { display: block; color: var(--fg-muted); font-size: 0.76rem; }
  .am-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
  .am-actions .am-btn { flex: 1 1 auto; }

  @media (max-width: 760px) {
    .am-track { grid-template-columns: repeat(4, minmax(0, 1fr)); row-gap: 0.8rem; }
    .am-track li:nth-child(5)::before { display: none; }
  }
  @media (max-width: 560px) {
    .am { padding: 1.1rem 1rem 2.5rem; }
    .am-row2, .am-choices, .am-choices.is-three { grid-template-columns: 1fr; }
    .am-head-actions, .am-head-actions .am-btn, .am-search { width: 100%; max-width: none; }
    .am-views { width: 100%; border-radius: 14px; }
    .am-rowcard { grid-template-columns: 1fr; }
    .am-rowcard .am-side { justify-content: flex-start; }
  }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_AMENITIES = {
    backUrl: @json(route('students.dashboard', ['section' => 'tasks'])),
    indexUrl: @json(route('students.hotel.amenities.index')),
    storeUrl: @json(route('students.hotel.amenities.store')),
    statuses: @json(\App\Models\HotelAmenity::STATUSES),
    accessTypes: @json(\App\Models\HotelAmenity::ACCESS_TYPES),
    reservationsUrl: @json(route('students.hotel.amenity-reservations.index')),
    servicesUrl: @json(route('students.hotel.amenity-services.index')),
    housekeepingFlow: @json(\App\Models\HotelAmenityReservation::HOUSEKEEPING_FLOW),
    accessLabels: @json(\App\Models\HotelAmenity::ACCESS_LABELS),
    // Same list the Front Desk complaint form offers, so a repair request lands in
    // Maintenance's queue under a category they already sort by.
    categories: @json(\App\Models\HotelComplaint::categoriesFor('maintenance')),
    serviceCategories: @json(\App\Models\HotelComplaint::SERVICE_CATEGORIES),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef, useMemo, useId } = React;

const IMAGE_MAX_DIMENSION = 1280;
const IMAGE_MAX_BYTES = 600 * 1024;
/* The extras beside the main photo — three photographs in all, the same bound
   HotelAmenity::GALLERY_MAX keeps. */
const GALLERY_MAX = 2;

const CONFIG = window.HMS_AMENITIES || {};
const STATUSES = CONFIG.statuses || ['Available', 'Temporarily Closed', 'Under Maintenance'];
const ACCESS_TYPES = CONFIG.accessTypes || ['open', 'registered', 'appointment', 'event'];
const HK_FLOW = CONFIG.housekeepingFlow || [];
const ACCESS_LABELS = CONFIG.accessLabels || {};

/* The three stored statuses, as a guest would put them. */
const STATUS_TEXT = {
  'Available':          { label: 'Open to guests', hint: 'Guests can use it now.',               icon: 'fa-circle-check',       tone: 'tone-ok' },
  'Temporarily Closed': { label: 'Closed for now', hint: 'Shut for a while, nothing is broken.', icon: 'fa-door-closed',        tone: 'tone-warn' },
  'Under Maintenance':  { label: 'Under repair',   hint: 'Something is broken and needs fixing.', icon: 'fa-screwdriver-wrench', tone: 'tone-bad' },
};

/* What each access type means, in the words of the person who has to pick one. */
const ACCESS_TEXT = {
  open:        { label: 'Guests walk in freely',      hint: 'Nothing for the Front Desk to record. Example: playground.', icon: 'fa-person-walking' },
  registered:  { label: 'Front Desk signs guests in', hint: 'The desk writes down who goes in and out. Example: pool, gym.', icon: 'fa-clipboard-list' },
  appointment: { label: 'Guests book a time',          hint: 'Booked ahead for a service, like a spa massage.', icon: 'fa-calendar-check' },
  event:       { label: 'Booked for events',           hint: 'Booked for a date, with a package and a bill. Example: function room.', icon: 'fa-champagne-glasses' },
};

/* The event room's steps, named for what is happening in the room, plus the
   button that moves it to each step. Mirrors HotelAmenityReservation::HOUSEKEEPING_FLOW. */
const EVENT_STEP = {
  'For Preparation': { label: 'Waiting to be set up', short: 'To set up' },
  'Preparing':       { label: 'Being set up',          short: 'Setting up', action: 'Start setting up the room' },
  'Ready':           { label: 'Ready for the event',   short: 'Ready',      action: 'Room is set up and ready' },
  'In Use':          { label: 'Event is going on',     short: 'Event on',   action: 'The event has started' },
  'Needs Cleaning':  { label: 'Event over, needs cleaning', short: 'Dirty',  action: 'Event is over, needs cleaning' },
  'Cleaning':        { label: 'Being cleaned',         short: 'Cleaning',   action: 'Start cleaning the room' },
  'Inspected':       { label: 'Cleaned and checked',   short: 'Done',       action: 'Room is clean and checked' },
};

// Problems Maintenance fixes. Service complaints (rude staff, slow service) are
// for guest complaints, not for a broken pool pump.
const SERVICE_SET = new Set(CONFIG.serviceCategories || []);
const CATEGORIES = (CONFIG.categories || ['Damaged Furniture']).filter(c => !SERVICE_SET.has(c));
const DEFAULT_CATEGORY = CATEGORIES.indexOf('Damaged Furniture') >= 0 ? 'Damaged Furniture' : CATEGORIES[0];

function peso(v) { return '₱' + Number(v || 0).toLocaleString(); }

function plural(n, one, many) { return n + ' ' + (n === 1 ? one : many); }

function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : '';
}

/* No stored image means a stable stand-in rather than an empty box — the same
   deterministic seed the add-ons, rooms and menu screens use. */
function amenityImg(amenity) {
  if (amenity && amenity.img) return amenity.img;
  const seed = encodeURIComponent((amenity && (amenity.id || amenity.name)) || 'amenity');
  return 'https://picsum.photos/seed/amenity-' + seed + '/800/600.jpg';
}

function formatDay(iso) {
  if (!iso) return '—';
  const d = new Date(iso + 'T00:00:00');
  if (Number.isNaN(d.getTime())) return iso;
  return d.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
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
    reader.onerror = function () { if (input.parentNode) input.parentNode.removeChild(input); };
    reader.readAsDataURL(file);
  });
  input.click();
}

function validateAmenityForm(form) {
  const errors = {};
  if (!String(form.name || '').trim()) errors.name = 'Type the name of the facility.';
  if (STATUSES.indexOf(form.status) < 0) errors.status = 'Pick whether it is open.';
  // Both or neither: half a pair reads as a mistake on the public page.
  if (form.opensAt && !form.closesAt) errors.closesAt = 'Add the closing time too.';
  if (form.closesAt && !form.opensAt) errors.opensAt = 'Add the opening time too.';
  return errors;
}

// SweetAlert draws outside the page's CSS, so it gets the live token values.
function themeColor(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

function swal(icon, title, text) {
  if (!window.Swal) return;
  window.Swal.fire({
    icon, title, text,
    background: themeColor('--card', '#181714'), color: themeColor('--fg', '#f5f0e8'),
    iconColor: icon === 'success' ? themeColor('--success', '#4ade80') : themeColor('--danger', '#fb7185'),
    confirmButtonColor: themeColor('--accent', '#c9a84c'),
    confirmButtonText: 'OK',
    timer: icon === 'success' ? 3000 : undefined,
    timerProgressBar: icon === 'success',
  });
}

function useEscape(onClose) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);
}

function ModalHead({ titleId, title, text, onClose }) {
  return (
    <div className="am-modal-head">
      <div>
        <h2 id={titleId} className="font-display">{title}</h2>
        {text ? <p>{text}</p> : null}
      </div>
      <button type="button" className="am-close" onClick={onClose} aria-label="Close">
        <i className="fa-solid fa-xmark"></i>
      </button>
    </div>
  );
}

/* One dialog for both doors: "Add a facility" opens it empty and POSTs, Edit opens
   it filled in and PATCHes. The fields are identical either way. */
function AmenityModal({ amenity, onClose, onSaved }) {
  const isEdit = !!amenity;
  const [form, setForm] = useState(() => ({
    name: (amenity && amenity.name) || '',
    description: (amenity && amenity.description) || '',
    location: (amenity && amenity.location) || '',
    opensAt: (amenity && amenity.opensAt) || '',
    closesAt: (amenity && amenity.closesAt) || '',
    status: (amenity && amenity.status) || 'Available',
    accessType: (amenity && amenity.accessType) || 'open',
    rate: amenity && amenity.rate ? String(amenity.rate) : '',
    setupFee: amenity && amenity.setupFee ? String(amenity.setupFee) : '',
    capacity: amenity && amenity.capacity !== null && amenity.capacity !== undefined ? String(amenity.capacity) : '',
    img: (amenity && amenity.img) || '',
    /* The extra shots, as URLs for what is already stored and data-URLs for what
       has just been picked. The save route tells the two apart. */
    gallery: (amenity && Array.isArray(amenity.images) ? amenity.images.slice(1) : []),
  }));
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);
  const uid = useId();
  const id = (k) => uid + '-' + k;
  const firstField = useRef(null);

  // Maintenance still has this one. Letting the picker offer Available here would
  // only produce a 422 from the update route — the reopen button is the way back.
  const lockedFromAvailable = !!(amenity && amenity.repairInProgress);

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  useEscape(onClose);
  useEffect(() => { if (firstField.current) firstField.current.focus(); }, []);

  const handleSubmit = (e) => {
    e.preventDefault();
    const nextErrors = validateAmenityForm(form);
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length) return;

    setSaving(true);
    // amenity.dbId is the hotel_amenities primary key; amenity.id is the front-end's "db-N".
    const url = isEdit ? (CONFIG.storeUrl + '/' + amenity.dbId) : CONFIG.storeUrl;
    fetch(url, {
      method: isEdit ? 'PATCH' : 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({
        name: String(form.name).trim(),
        description: String(form.description || '').trim(),
        location: String(form.location || '').trim(),
        opens_at: form.opensAt || null,
        closes_at: form.closesAt || null,
        status: form.status,
        access_type: form.accessType,
        // Blank means zero for a fee and "no limit" for capacity — which is why capacity
        // goes back as null and the fees go back as 0.
        rate: parseInt(form.rate, 10) || 0,
        setup_fee: parseInt(form.setupFee, 10) || 0,
        capacity: String(form.capacity).trim() === '' ? null : (parseInt(form.capacity, 10) || 0),
        // Handed back as-is when untouched: the server collapses an existing
        // storage path to the one it already holds rather than re-uploading.
        image: form.img || '',
        gallery: form.gallery || [],
      }),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        if (data.item && typeof onSaved === 'function') onSaved(data.item);
        onClose();
        swal('success', isEdit ? 'Changes saved' : 'Facility added', isEdit
          ? data.item.name + ' has been updated on your hotel website.'
          : data.item.name + ' now shows on the Amenities page of your hotel website.');
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Could not save. Please try again.';
        if (window.Swal) swal('error', 'Not saved', msg);
        else setErrors({ name: msg });
      })
      .finally(() => setSaving(false));
  };

  const err = (key) => (errors[key] ? <p className="am-error">{errors[key]}</p> : null);

  return (
    <div className="am-overlay" onClick={onClose}>
      <div className="am-modal" role="dialog" aria-modal="true" aria-labelledby={id('title')} onClick={e => e.stopPropagation()}>
        <ModalHead
          titleId={id('title')}
          title={isEdit ? `Edit ${amenity.name}` : 'Add a facility'}
          text="Everything here shows on the Amenities page of your hotel website."
          onClose={onClose}
        />

        <form onSubmit={handleSubmit} className="am-form" noValidate>
          <p className="am-section-title">About the facility</p>
          <div className="am-field">
            <label className="am-label" htmlFor={id('name')}>Facility name</label>
            <input
              id={id('name')} ref={firstField}
              type="text" className={`am-input ${errors.name ? 'has-error' : ''}`} value={form.name}
              placeholder="Example: Swimming Pool"
              onChange={e => update('name', e.target.value)}
            />
            {err('name')}
          </div>

          <div className="am-field">
            <label className="am-label" htmlFor={id('desc')}>Short description <em>(optional)</em></label>
            <textarea
              id={id('desc')} className="am-input" value={form.description}
              placeholder="Example: Outdoor pool with sun loungers and poolside towels."
              onChange={e => update('description', e.target.value)}
            />
            <p className="am-help">One or two sentences guests will read on the website.</p>
          </div>

          <div className="am-field">
            <label className="am-label" htmlFor={id('loc')}>Where is it? <em>(optional)</em></label>
            <input
              id={id('loc')} type="text" className="am-input" value={form.location}
              placeholder="Example: Rooftop, 8th Floor"
              onChange={e => update('location', e.target.value)}
            />
          </div>

          <div className="am-row2">
            <div className="am-field">
              <label className="am-label" htmlFor={id('open')}>Opens at</label>
              <input
                id={id('open')} type="time" className={`am-input ${errors.opensAt ? 'has-error' : ''}`} value={form.opensAt}
                onChange={e => update('opensAt', e.target.value)}
              />
              {err('opensAt')}
            </div>
            <div className="am-field">
              <label className="am-label" htmlFor={id('close')}>Closes at</label>
              <input
                id={id('close')} type="time" className={`am-input ${errors.closesAt ? 'has-error' : ''}`} value={form.closesAt}
                onChange={e => update('closesAt', e.target.value)}
              />
              {err('closesAt')}
            </div>
          </div>
          <p className="am-help" style={{ marginTop: '-0.6rem' }}>Leave both empty if there are no set hours.</p>

          <p className="am-section-title">Right now</p>
          <div className="am-field">
            <span className="am-label">Can guests use it?</span>
            <div className="am-choices is-three" role="radiogroup" aria-label="Can guests use it?">
              {STATUSES.map(status => {
                const t = STATUS_TEXT[status] || { label: status, hint: '', icon: 'fa-circle', tone: 'tone-muted' };
                const off = lockedFromAvailable && status === 'Available';
                return (
                  <label key={status} className={`am-choice ${form.status === status ? 'is-on' : ''} ${off ? 'is-off' : ''}`}>
                    <input type="radio" name={id('status')} value={status} disabled={off} checked={form.status === status} onChange={() => update('status', status)} />
                    <i className={`fa-solid ${t.icon} ${t.tone}`}></i>
                    <div><b>{t.label}</b><span>{t.hint}</span></div>
                  </label>
                );
              })}
            </div>
            {err('status')}
            <p className="am-help">
              {lockedFromAvailable
                ? 'Maintenance is still fixing this. When they finish, use the "It is fixed, open it" button on the card.'
                : 'Picking Under repair lets you tell Maintenance what is broken from the card.'}
            </p>
          </div>

          {/* Access type decides what Front Desk can do with this facility. It is the
              one field on this form the other departments read. */}
          <p className="am-section-title">How guests use it</p>
          <div className="am-field">
            <div className="am-choices" role="radiogroup" aria-label="How guests use it">
              {ACCESS_TYPES.map(type => {
                const t = ACCESS_TEXT[type] || { label: ACCESS_LABELS[type] || type, hint: '', icon: 'fa-circle' };
                return (
                  <label key={type} className={`am-choice ${form.accessType === type ? 'is-on' : ''}`}>
                    <input type="radio" name={id('access')} value={type} checked={form.accessType === type} onChange={() => update('accessType', type)} />
                    <i className={`fa-solid ${t.icon} tone-brand`}></i>
                    <div><b>{t.label}</b><span>{t.hint}</span></div>
                  </label>
                );
              })}
            </div>
          </div>

          {/* Only asked for where it means something: a capacity on the playground and
              an event fee on the pool are questions with no useful answer. */}
          {(form.accessType === 'registered' || form.accessType === 'event') && (
            <div className="am-field">
              <label className="am-label" htmlFor={id('cap')}>How many people fit? <em>(optional)</em></label>
              <input
                id={id('cap')} type="number" min="0" max="9999" inputMode="numeric" className="am-input" value={form.capacity}
                placeholder="Leave empty for no limit"
                onChange={e => update('capacity', e.target.value)}
              />
              <p className="am-help">The Front Desk cannot let in more people than this.</p>
            </div>
          )}

          {form.accessType === 'event' && (
            <div className="am-row2">
              <div className="am-field">
                <label className="am-label" htmlFor={id('rate')}>Price to book an event</label>
                <div className="am-money"><span>₱</span>
                  <input id={id('rate')} type="number" min="0" inputMode="numeric" className="am-input" value={form.rate} placeholder="5000" onChange={e => update('rate', e.target.value)} />
                </div>
              </div>
              <div className="am-field">
                <label className="am-label" htmlFor={id('setup')}>Setup fee</label>
                <div className="am-money"><span>₱</span>
                  <input id={id('setup')} type="number" min="0" inputMode="numeric" className="am-input" value={form.setupFee} placeholder="1500" onChange={e => update('setupFee', e.target.value)} />
                </div>
              </div>
            </div>
          )}

          <p className="am-section-title">Photos</p>
          <div className="am-field">
            {/* The one the card shows and these staff lists print as a thumbnail.
                Everything else is the gallery below it. */}
            <span className="am-label">Main photo <em>(optional)</em></span>
            <button type="button" className="am-photo-pick" onClick={() => pickImageFile(url => { if (url) update('img', url); })}>
              {form.img ? (
                <img src={form.img} alt="Main photo of the facility" />
              ) : (
                <span className="am-photo-empty"><i className="fa-solid fa-camera"></i>Click to choose a photo</span>
              )}
            </button>
            {form.img ? (
              <button type="button" className="am-link" onClick={() => update('img', '')}>
                <i className="fa-solid fa-trash-can"></i> Remove main photo
              </button>
            ) : null}
          </div>

          <div className="am-field">
            <span className="am-label">More photos <em>(up to {GALLERY_MAX})</em></span>
            <div className="am-gallery">
              {form.gallery.map((shot, index) => (
                <div key={shot.slice(-40) + index} className="am-shot">
                  <img src={shot} alt={'Extra photo ' + (index + 1)} />
                  <button type="button" aria-label={'Remove extra photo ' + (index + 1)} onClick={() => update('gallery', form.gallery.filter((_, i) => i !== index))}>
                    <i className="fa-solid fa-xmark"></i>
                  </button>
                </div>
              ))}
              {form.gallery.length < GALLERY_MAX && (
                <button
                  type="button"
                  className="am-shot-add"
                  onClick={() => pickImageFile(url => {
                    if (!url) return;
                    // Read off the state rather than the closure: two pickers can
                    // finish in either order, and the second must not drop the first.
                    setForm(prev => (prev.gallery.length >= GALLERY_MAX
                      ? prev
                      : Object.assign({}, prev, { gallery: prev.gallery.concat([url]) })));
                  })}
                >
                  <i className="fa-solid fa-plus"></i>
                  Add photo
                </button>
              )}
            </div>
            <p className="am-help">Guests can flip through these on the website.</p>
          </div>

          <div className="am-actions">
            <button type="button" className="am-btn is-quiet" onClick={onClose}>Cancel</button>
            <button type="submit" className="am-btn is-solid" disabled={saving}>
              <i className={`fa-solid ${isEdit ? 'fa-floppy-disk' : 'fa-plus'}`}></i>
              {saving ? 'Saving…' : (isEdit ? 'Save changes' : 'Add this facility')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

/* Hands the facility to Maintenance. Files a complaint on their board rather than
   anything new, so it lands in the queue they already watch. */
function RepairModal({ amenity, onClose, onSaved }) {
  const [category, setCategory] = useState(DEFAULT_CATEGORY);
  const [details, setDetails] = useState('');
  const [error, setError] = useState(null);
  const [saving, setSaving] = useState(false);
  const uid = useId();

  useEscape(onClose);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!details.trim()) { setError('Write what is broken so Maintenance knows what to bring.'); return; }

    setSaving(true);
    fetch(CONFIG.storeUrl + '/' + amenity.dbId + '/repair-request', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ category: category, details: details.trim() }),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        if (data.item && typeof onSaved === 'function') onSaved(data.item);
        onClose();
        swal('success', 'Sent to Maintenance', amenity.name + ' is now with Maintenance. When they finish, check it and open it again for guests.');
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Could not send. Please try again.';
        if (window.Swal) swal('error', 'Not sent', msg);
        else setError(msg);
      })
      .finally(() => setSaving(false));
  };

  return (
    <div className="am-overlay" onClick={onClose}>
      <div className="am-modal is-small" role="dialog" aria-modal="true" aria-labelledby={uid + '-t'} onClick={e => e.stopPropagation()}>
        <ModalHead
          titleId={uid + '-t'}
          title={`Tell Maintenance: ${amenity.name}`}
          text={amenity.location ? `Location: ${amenity.location}` : null}
          onClose={onClose}
        />
        <form onSubmit={handleSubmit} className="am-form" noValidate>
          <div className="am-field">
            <span className="am-label">What kind of problem?</span>
            <div className="am-chips">
              {CATEGORIES.map(item => (
                <button key={item} type="button" className={`am-chip ${category === item ? 'is-on' : ''}`} aria-pressed={category === item} onClick={() => setCategory(item)}>
                  {item}
                </button>
              ))}
            </div>
          </div>

          <div className="am-field">
            <label className="am-label" htmlFor={uid + '-d'}>What exactly is broken?</label>
            <textarea
              id={uid + '-d'}
              className={`am-input ${error ? 'has-error' : ''}`} value={details}
              placeholder="Example: The pool pump stopped and the water is not moving."
              onChange={e => { setDetails(e.target.value); if (error) setError(null); }}
            />
            {error ? <p className="am-error">{error}</p> : null}
          </div>

          <p className="am-notice is-muted">
            <i className="fa-solid fa-circle-info"></i>
            <span>Maintenance gets this on their list. {amenity.name} stays Under repair until you check their work and open it again.</span>
          </p>

          <div className="am-actions">
            <button type="button" className="am-btn is-quiet" onClick={onClose}>Cancel</button>
            <button type="submit" className="am-btn is-solid" disabled={saving}>
              <i className="fa-solid fa-paper-plane"></i>
              {saving ? 'Sending…' : 'Send to Maintenance'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

/* What a broken facility shows on its card: nobody told yet, waiting on
   Maintenance, or back and needing a look. */
function RepairBox({ amenity, canManage, onRequest, onVerify, verifying }) {
  if (amenity.status !== 'Under Maintenance') return null;

  if (amenity.repairInProgress) {
    return (
      <div className="am-repair is-fix">
        <span className="am-repair-title"><i className="fa-solid fa-screwdriver-wrench"></i> Maintenance is fixing this</span>
        <p>
          {amenity.repairCategory || 'Repair'}
          <small>
            {amenity.repairStatus === 'In Progress' ? 'They are working on it now.' : 'They have not started yet.'}
            {amenity.repairHandledBy ? ` Handled by ${amenity.repairHandledBy}.` : ''}
            {' '}Nothing for you to do until they finish.
          </small>
        </p>
      </div>
    );
  }

  if (amenity.awaitingVerification) {
    return (
      <div className="am-repair is-ok">
        <span className="am-repair-title"><i className="fa-solid fa-circle-check"></i> Maintenance says it is fixed</span>
        <p>
          {amenity.repairNote ? <span className="am-quote">“{amenity.repairNote}”</span> : null}
          <small>Go and check it. If it works, open it again for guests.</small>
        </p>
        {canManage && (
          <button type="button" className="am-btn is-go is-small is-wide" disabled={verifying} onClick={() => onVerify(amenity)}>
            <i className="fa-solid fa-door-open"></i>
            {verifying ? 'Opening…' : 'It is fixed, open it to guests'}
          </button>
        )}
      </div>
    );
  }

  // Broken, and nobody has told Maintenance yet.
  return (
    <div className="am-repair is-bad">
      <span className="am-repair-title"><i className="fa-solid fa-triangle-exclamation"></i> Maintenance has not been told</span>
      <p><small>This is marked as broken, but no one has asked Maintenance to fix it yet.</small></p>
      {canManage && (
        <button type="button" className="am-btn is-danger is-small is-wide" onClick={() => onRequest(amenity)}>
          <i className="fa-solid fa-screwdriver-wrench"></i> Tell Maintenance what is broken
        </button>
      )}
    </div>
  );
}

function AmenityCard({ amenity, canManage, canCustomize, verifying, removing, onEdit, onRemove, onRequest, onVerify }) {
  const status = STATUS_TEXT[amenity.status] || { label: amenity.status, icon: 'fa-circle', tone: 'tone-muted' };
  const access = ACCESS_TEXT[amenity.accessType];
  const needsYou = amenity.status === 'Under Maintenance' && !amenity.repairInProgress;

  return (
    <article className={`am-card ${needsYou ? 'needs-you' : ''}`}>
      <div className="am-card-img">
        <img src={amenityImg(amenity)} alt={amenity.name} loading="lazy" />
        <span className={`am-pill ${status.tone}`}><i className={`fa-solid ${status.icon}`}></i>{status.label}</span>
      </div>
      <div className="am-card-body">
        <div>
          <h3 className="am-name">{amenity.name}</h3>
          {/* What Front Desk can do with it — the one field on this screen the
              other departments read. */}
          <span className="am-how">
            <i className={`fa-solid ${access ? access.icon : 'fa-circle-info'}`}></i>
            {access ? access.label : amenity.accessLabel}
          </span>
        </div>

        {amenity.description
          ? <p className="am-desc">{amenity.description}</p>
          : <p className="am-desc is-empty">No description yet.</p>}

        <ul className="am-facts">
          <li><i className="fa-solid fa-location-dot"></i>{amenity.location ? <span>{amenity.location}</span> : <span className="is-empty">No location given</span>}</li>
          <li><i className="fa-regular fa-clock"></i>{amenity.hours ? <span>{amenity.hours}</span> : <span className="is-empty">No set hours</span>}</li>
          {amenity.capacity ? <li><i className="fa-solid fa-users"></i><span>Up to {amenity.capacity} people</span></li> : null}
          {amenity.accessType === 'event' && amenity.rate ? (
            <li><i className="fa-solid fa-tag"></i><span>{peso(amenity.rate)} per event{amenity.setupFee ? ` + ${peso(amenity.setupFee)} setup` : ''}</span></li>
          ) : null}
        </ul>

        <RepairBox
          amenity={amenity}
          canManage={canManage}
          onRequest={onRequest}
          onVerify={onVerify}
          verifying={verifying}
        />

        {canCustomize ? (
          <div className="am-card-actions">
            <button type="button" className="am-btn is-small" onClick={() => onEdit(amenity)}>
              <i className="fa-solid fa-pen"></i> Edit
            </button>
            <button type="button" className="am-btn is-danger is-small" disabled={removing} onClick={() => onRemove(amenity)}>
              <i className="fa-solid fa-trash-can"></i> {removing ? 'Removing…' : 'Remove'}
            </button>
          </div>
        ) : null}
      </div>
    </article>
  );
}

/* Where the amenities task has got to. Not a refusal — the buttons are there for
   any Housekeeping member — so it is drawn as a note, in the colour of the state
   it is reporting. */
function TaskNotice({ task }) {
  if (!task || !task.message) return null;
  const tone = task.state === 'approved' ? ['is-ok', 'fa-circle-check']
    : task.state === 'submitted' ? ['is-warn', 'fa-paper-plane']
    : ['is-muted', 'fa-lock'];
  return (
    <p className={`am-notice ${tone[0]}`} style={{ marginBottom: '1rem' }}>
      <i className={`fa-solid ${tone[1]}`}></i>
      <span>{task.message}</span>
    </p>
  );
}

const FILTERS = [
  { key: 'all',    label: 'All',            icon: 'fa-layer-group',        match: () => true },
  { key: 'open',   label: 'Open to guests', icon: 'fa-circle-check',       match: a => a.status === 'Available' },
  { key: 'closed', label: 'Closed for now', icon: 'fa-door-closed',        match: a => a.status === 'Temporarily Closed' },
  { key: 'repair', label: 'Under repair',   icon: 'fa-screwdriver-wrench', match: a => a.status === 'Under Maintenance' },
];

function AmenitiesPanel({ amenities, loading, failed, canManage, canCustomize, task, editing, setEditing, onSaved, onRemoved }) {
  const [filter, setFilter] = useState('all');
  const [search, setSearch] = useState('');
  const [repairing, setRepairing] = useState(null); // an amenity row
  const [verifyingId, setVerifyingId] = useState(null);
  const [removingId, setRemovingId] = useState(null);

  const current = FILTERS.find(f => f.key === filter) || FILTERS[0];
  const visible = useMemo(() => {
    const q = search.trim().toLowerCase();
    return amenities.filter(a => current.match(a) && (!q || [a.name, a.location].some(v => String(v || '').toLowerCase().includes(q))));
  }, [amenities, current, search]);

  const handleSaved = (item) => {
    setEditing(null);
    setRepairing(null);
    onSaved(item);
  };

  /* Asked for first: a facility is a page on the team's site, and a mis-click
     here would take it off every teammate's copy of it too. */
  const handleRemove = (amenity) => {
    const ask = window.Swal
      ? window.Swal.fire({
          title: 'Remove ' + amenity.name + '?',
          text: 'It will disappear from the Amenities page of your hotel website. You cannot undo this.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, remove it',
          cancelButtonText: 'Keep it',
          background: themeColor('--card', '#181714'),
          color: themeColor('--fg', '#f5f0e8'),
          confirmButtonColor: '#be123c',
          cancelButtonColor: '#71717a',
        }).then(r => r.isConfirmed)
      : Promise.resolve(window.confirm('Remove ' + amenity.name + '?'));

    ask.then((ok) => {
      if (!ok) return;
      setRemovingId(amenity.dbId);
      fetch(CONFIG.storeUrl + '/' + amenity.dbId, {
        method: 'DELETE',
        credentials: 'same-origin',
        headers: { 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      })
        .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
        .then(() => {
          onRemoved(amenity);
          swal('success', 'Removed', amenity.name + ' is no longer on your hotel website.');
        })
        .catch(err => swal('error', 'Not removed', (err && err.message) ? err.message : 'Could not remove. Please try again.'))
        .finally(() => setRemovingId(null));
    });
  };

  const handleVerify = (amenity) => {
    setVerifyingId(amenity.dbId);
    fetch(CONFIG.storeUrl + '/' + amenity.dbId + '/verify', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        if (data.item) onSaved(data.item);
        swal('success', 'Open again', amenity.name + ' is open to guests again.');
      })
      .catch(err => swal('error', 'Not opened', (err && err.message) ? err.message : 'Could not open it. Please try again.'))
      .finally(() => setVerifyingId(null));
  };

  let emptyTitle = 'Nothing here';
  let emptyText = 'Pick another tab to see the other facilities.';
  if (search.trim()) {
    emptyTitle = 'No facility matches your search';
    emptyText = 'Check the spelling, or clear the search box.';
  } else if (filter === 'repair') {
    emptyTitle = 'Nothing is broken';
    emptyText = 'Facilities marked Under repair show here until they are fixed and opened again.';
  } else if (filter === 'closed') {
    emptyTitle = 'Nothing is closed';
    emptyText = 'Facilities you close for a while show here.';
  }

  return (
    <section className="am-panel" aria-labelledby="am-list">
      <div className="am-panel-head">
        <div>
          <h2 id="am-list">Your facilities</h2>
          <p>{amenities.length ? `${plural(amenities.length, 'facility', 'facilities')}. Guests see each one on your hotel website, with the same open or closed status.` : 'Guests see these on your hotel website.'}</p>
        </div>
        <span className="am-live">Updates on its own</span>
      </div>

      <TaskNotice task={task} />

      {amenities.length > 0 ? (
        <div className="am-toolbar">
          <div className="am-views" role="group" aria-label="Show facilities">
            {FILTERS.map(f => (
              <button key={f.key} type="button" className={`am-view ${filter === f.key ? 'is-on' : ''}`} aria-pressed={filter === f.key} onClick={() => setFilter(f.key)}>
                <i className={`fa-solid ${f.icon}`}></i>{f.label}
                <span className="am-count">{amenities.filter(f.match).length}</span>
              </button>
            ))}
          </div>
          {amenities.length > 6 ? (
            <div className="am-search">
              <i className="fa-solid fa-magnifying-glass"></i>
              <input type="text" className="am-input" placeholder="Search a facility" aria-label="Search a facility" value={search} onChange={e => setSearch(e.target.value)} />
            </div>
          ) : null}
        </div>
      ) : null}

      {loading ? (
        <div className="am-grid" aria-busy="true" aria-label="Loading facilities">
          <div className="am-skel"></div><div className="am-skel"></div><div className="am-skel"></div>
        </div>
      ) : failed && amenities.length === 0 ? (
        <div className="am-empty" role="alert">
          <div className="am-empty-icon"><i className="fa-solid fa-wifi"></i></div>
          <h3>Could not load the facilities</h3>
          <p>Check your internet connection. The page tries again on its own every few seconds.</p>
        </div>
      ) : amenities.length === 0 ? (
        <div className="am-empty">
          <div className="am-empty-icon"><i className="fa-solid fa-person-swimming"></i></div>
          <h3>No facilities yet</h3>
          <p>Add the first one, like a swimming pool or a gym. It will show on the Amenities page of your hotel website.</p>
          {canCustomize ? (
            <button type="button" className="am-btn is-solid" onClick={() => setEditing('new')}>
              <i className="fa-solid fa-plus"></i> Add a facility
            </button>
          ) : null}
        </div>
      ) : visible.length === 0 ? (
        <div className="am-empty">
          <div className="am-empty-icon"><i className={`fa-solid ${search.trim() ? 'fa-magnifying-glass' : current.icon}`}></i></div>
          <h3>{emptyTitle}</h3>
          <p>{emptyText}</p>
        </div>
      ) : (
        <div className="am-grid">
          {visible.map(amenity => (
            <AmenityCard
              key={amenity.id}
              amenity={amenity}
              canManage={canManage}
              canCustomize={canCustomize}
              verifying={verifyingId === amenity.dbId}
              removing={removingId === amenity.dbId}
              onEdit={setEditing}
              onRemove={handleRemove}
              onRequest={setRepairing}
              onVerify={handleVerify}
            />
          ))}
        </div>
      )}

      {editing && (
        <AmenityModal
          amenity={editing === 'new' ? null : editing}
          onClose={() => setEditing(null)}
          onSaved={handleSaved}
        />
      )}

      {repairing && (
        <RepairModal
          amenity={repairing}
          onClose={() => setRepairing(null)}
          onSaved={handleSaved}
        />
      )}
    </section>
  );
}

/* The treatments behind a by-appointment facility. Housekeeping's rate card — Front Desk
   reads it when selling a slot and cannot edit it, the same split the add-ons catalogue
   makes between the two desks. */
function ServiceModal({ service, amenities, onClose, onSaved }) {
  const isEdit = !!service;
  const appointmentAmenities = amenities.filter(a => a.accessType === 'appointment');
  const [form, setForm] = useState(() => ({
    amenityId: service ? String(service.amenityId) : (appointmentAmenities[0] ? String(appointmentAmenities[0].dbId) : ''),
    name: (service && service.name) || '',
    description: (service && service.description) || '',
    minutes: service ? String(service.minutes) : '60',
    price: service ? String(service.price) : '',
    isActive: service ? service.isActive : true,
  }));
  const [error, setError] = useState(null);
  const [saving, setSaving] = useState(false);
  const uid = useId();
  const id = (k) => uid + '-' + k;

  useEscape(onClose);

  const update = (k, v) => { setForm(prev => Object.assign({}, prev, { [k]: v })); if (error) setError(null); };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!form.name.trim()) { setError('Type the name of the service.'); return; }
    if (!form.amenityId) { setError('First set a facility to "Guests book a time", then add its services.'); return; }

    setSaving(true);
    const url = CONFIG.servicesUrl + (isEdit ? '/' + service.id : '');
    fetch(url, {
      method: isEdit ? 'PATCH' : 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({
        hotel_amenity_id: Number(form.amenityId),
        name: form.name.trim(),
        description: form.description.trim(),
        duration_minutes: Math.max(5, parseInt(form.minutes, 10) || 60),
        price: Math.max(0, parseInt(form.price, 10) || 0),
        is_active: !!form.isActive,
      }),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        onSaved(data.service);
        onClose();
        swal('success', isEdit ? 'Service saved' : 'Service added', data.service.name + ' has been saved.');
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Could not save that service.';
        if (window.Swal) swal('error', 'Not saved', msg); else setError(msg);
      })
      .finally(() => setSaving(false));
  };

  return (
    <div className="am-overlay" onClick={onClose}>
      <div className="am-modal is-small" role="dialog" aria-modal="true" aria-labelledby={id('t')} onClick={e => e.stopPropagation()}>
        <ModalHead
          titleId={id('t')}
          title={isEdit ? `Edit ${service.name}` : 'Add a service'}
          text="Something guests book a time for, like a massage at the spa."
          onClose={onClose}
        />
        <form onSubmit={handleSubmit} className="am-form" noValidate>
          <div className="am-field">
            <label className="am-label" htmlFor={id('fac')}>Which facility?</label>
            <select id={id('fac')} className="am-input" value={form.amenityId} onChange={e => update('amenityId', e.target.value)}>
              {appointmentAmenities.map(a => <option key={a.dbId} value={a.dbId}>{a.name}</option>)}
            </select>
            {appointmentAmenities.length === 0 && (
              <p className="am-error">No facility is set to "Guests book a time" yet. Edit one first.</p>
            )}
          </div>

          <div className="am-field">
            <label className="am-label" htmlFor={id('name')}>Service name</label>
            <input id={id('name')} type="text" className="am-input" value={form.name}
              placeholder="Example: Swedish Massage" onChange={e => update('name', e.target.value)} />
          </div>

          <div className="am-field">
            <label className="am-label" htmlFor={id('desc')}>Short description <em>(optional)</em></label>
            <input id={id('desc')} type="text" className="am-input" value={form.description}
              placeholder="Example: Full-body massage with warm oil."
              onChange={e => update('description', e.target.value)} />
          </div>

          <div className="am-row2">
            <div className="am-field">
              <label className="am-label" htmlFor={id('min')}>How long? (minutes)</label>
              <input id={id('min')} type="number" min="5" max="600" inputMode="numeric" className="am-input" value={form.minutes}
                onChange={e => update('minutes', e.target.value)} />
              <p className="am-help">The Front Desk books this much time.</p>
            </div>
            <div className="am-field">
              <label className="am-label" htmlFor={id('price')}>Price for the guest</label>
              <div className="am-money"><span>₱</span>
                <input id={id('price')} type="number" min="0" inputMode="numeric" className="am-input" value={form.price}
                  placeholder="1200" onChange={e => update('price', e.target.value)} />
              </div>
            </div>
          </div>

          {isEdit && (
            <label className="am-check">
              <input type="checkbox" checked={form.isActive} onChange={e => update('isActive', e.target.checked)} />
              <span>
                Guests can still book this
                <small>Untick to stop offering it. Bookings already made still go ahead.</small>
              </span>
            </label>
          )}

          {error ? <p className="am-error">{error}</p> : null}

          <div className="am-actions">
            <button type="button" className="am-btn is-quiet" onClick={onClose}>Cancel</button>
            <button type="submit" className="am-btn is-solid" disabled={saving}>
              <i className={`fa-solid ${isEdit ? 'fa-floppy-disk' : 'fa-plus'}`}></i>
              {saving ? 'Saving…' : (isEdit ? 'Save changes' : 'Add this service')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function ServicesPanel({ services, amenities, canManage, onSaved }) {
  const [editing, setEditing] = useState(null);
  const hasAppointmentFacility = amenities.some(a => a.accessType === 'appointment');

  if (!hasAppointmentFacility && services.length === 0) return null;

  return (
    <section className="am-panel" aria-labelledby="am-services">
      <div className="am-panel-head">
        <div>
          <h2 id="am-services">Services guests can book</h2>
          <p>
            For facilities where guests book a time, like the spa. The Front Desk picks from
            this list, and the length you set decides how long the booking is.
          </p>
        </div>
        {canManage && (
          <button type="button" className="am-btn is-small" onClick={() => setEditing('new')}>
            <i className="fa-solid fa-plus"></i> Add a service
          </button>
        )}
      </div>

      {services.length === 0 ? (
        <div className="am-empty">
          <div className="am-empty-icon"><i className="fa-solid fa-spa"></i></div>
          <h3>No services yet</h3>
          <p>Add what guests can book, like a 60-minute massage, so the Front Desk can sell it.</p>
        </div>
      ) : (
        <div className="am-rows">
          {services.map(s => {
            const owner = amenities.find(a => a.dbId === s.amenityId);
            return (
              <div key={s.id} className="am-rowcard">
                <div>
                  <b>{s.name}</b>
                  <small>
                    {owner ? owner.name : 'Facility removed'} · {s.duration}
                    {s.description ? <><br />{s.description}</> : null}
                  </small>
                </div>
                <div className="am-side">
                  <span className="am-price">{peso(s.price)}</span>
                  <span className={`am-pill ${s.isActive ? 'tone-ok' : 'tone-muted'}`}>
                    <i className={`fa-solid ${s.isActive ? 'fa-circle-check' : 'fa-pause'}`}></i>
                    {s.isActive ? 'Offered' : 'Not offered'}
                  </span>
                  {canManage ? (
                    <button type="button" className="am-btn is-small" onClick={() => setEditing(s)}>
                      <i className="fa-solid fa-pen"></i> Edit
                    </button>
                  ) : null}
                </div>
              </div>
            );
          })}
        </div>
      )}

      {editing && (
        <ServiceModal
          service={editing === 'new' ? null : editing}
          amenities={amenities}
          onClose={() => setEditing(null)}
          onSaved={onSaved}
        />
      )}
    </section>
  );
}

/* Housekeeping's turnaround for one booked event, step by step.

   Separate from the amenity's own Available / Temporarily Closed / Under Maintenance:
   that one is the condition of the hall, this is the state of one event's room. A hall
   being cleaned after a wedding is not a broken hall — and a hall that IS broken still
   goes to Maintenance through the repair request on the amenity itself. */
function EventTurnaroundPanel({ reservations, canManage, onChanged }) {
  const [busyId, setBusyId] = useState(null);

  const events = (reservations || [])
    .filter(r => r.kind === 'event' && r.status !== 'Cancelled')
    .sort((a, b) => (a.scheduledOn + a.startsAt < b.scheduledOn + b.startsAt ? -1 : 1));

  const advance = (r, next) => {
    setBusyId(r.id);
    fetch(CONFIG.reservationsUrl + '/' + r.id, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ housekeeping_status: next }),
    })
      .then(res => (res.ok ? res.json() : res.json().then(err => Promise.reject(err))))
      .then(data => onChanged(data.reservation))
      .catch(err => swal('error', 'Not saved', (err && err.message) ? err.message : 'Could not update that room.'))
      .finally(() => setBusyId(null));
  };

  if (events.length === 0) return null;

  return (
    <section className="am-panel" aria-labelledby="am-events">
      <div className="am-panel-head">
        <div>
          <h2 id="am-events">Events in the function room</h2>
          <p>
            Events the Front Desk has booked. Set the room up before each event and clean it
            after. If you find something broken, mark the facility Under repair above and tell
            Maintenance.
          </p>
        </div>
      </div>

      <div className="am-rows">
        {events.map(r => {
          const at = HK_FLOW.indexOf(r.housekeepingStatus);
          const next = at >= 0 && at < HK_FLOW.length - 1 ? HK_FLOW[at + 1] : null;
          const step = EVENT_STEP[r.housekeepingStatus];
          const done = !next && at >= 0;
          return (
            <div key={r.id} className="am-rowcard is-event">
              <div className="am-event-top">
                <div>
                  <b>{r.customerName}{r.eventType ? ` · ${r.eventType}` : ''}</b>
                  <small>
                    {formatDay(r.scheduledOn)} · {r.timeLabel}
                    {r.guestCount ? ` · ${r.guestCount} guests` : ''}
                    {r.reference ? ` · Ref ${r.reference}` : ''}
                    {r.package || r.cateringPackageName ? <><br />
                      {r.package ? `Package: ${r.package}` : ''}
                      {r.package && r.cateringPackageName ? ' · ' : ''}
                      {r.cateringPackageName ? `Food: ${r.cateringPackageName}${r.cateringOrderStatus ? ` (${r.cateringOrderStatus})` : ''}` : ''}
                    </> : null}
                    {r.specialRequests ? <><br /><span className="am-quote">Request: “{r.specialRequests}”</span></> : null}
                  </small>
                </div>
                <span className={`am-pill ${done ? 'tone-ok' : 'tone-brand'}`}>
                  <i className={`fa-solid ${done ? 'fa-circle-check' : 'fa-broom'}`}></i>
                  {step ? step.label : (r.housekeepingStatus || 'Not started')}
                </span>
              </div>

              {at >= 0 ? (
                <ol className="am-track" aria-label={`Room step ${at + 1} of ${HK_FLOW.length}`}>
                  {HK_FLOW.map((s, i) => (
                    <li key={s} className={i < at || done ? 'is-done' : i === at ? 'is-now' : ''}>
                      <span className="am-dot"></span>
                      {(EVENT_STEP[s] && EVENT_STEP[s].short) || s}
                    </li>
                  ))}
                </ol>
              ) : null}

              {canManage && next ? (
                <div>
                  <button type="button" className="am-btn is-solid is-small" disabled={busyId === r.id} onClick={() => advance(r, next)}>
                    <i className="fa-solid fa-arrow-right"></i>
                    {busyId === r.id ? 'Saving…' : ((EVENT_STEP[next] && EVENT_STEP[next].action) || 'Mark ' + next)}
                  </button>
                </div>
              ) : null}
            </div>
          );
        })}
      </div>
    </section>
  );
}

function App() {
  const [amenities, setAmenities] = useState([]);
  const [reservations, setReservations] = useState([]);
  const [services, setServices] = useState([]);
  const [canManage, setCanManage] = useState(false);
  /* Holding the Housekeeping role is what canManage answers; whether the design
     task that opens this section is assigned to you and still open is a separate
     question, and it is the one the Add / Edit / Remove buttons ask. */
  const [canCustomize, setCanCustomize] = useState(false);
  const [task, setTask] = useState(null);
  const [loading, setLoading] = useState(true);
  const [failed, setFailed] = useState(false);
  const [editing, setEditing] = useState(null);     // an amenity row, or 'new'
  // A poll landing mid-save would overwrite the row the user just changed with the
  // list as it was before. Fetches stand down while a write is in flight.
  const pendingWrites = useRef(0);

  const fetchAmenities = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(CONFIG.indexUrl, {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(data => {
        setAmenities(data.items || []);
        setCanManage(!!data.can_manage);
        setCanCustomize(!!data.can_customize);
        setTask(data.task || null);
        setFailed(false);
      })
      .catch(() => setFailed(true))
      .finally(() => setLoading(false));

    const opts = { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };

    // Events Front Desk booked. Only the ones still holding a slot — a cancelled or
    // finished booking is history and there is nothing left to turn round.
    fetch(CONFIG.reservationsUrl + '?kind=event&holding=1', opts)
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => { if (pendingWrites.current === 0) setReservations(d.reservations || []); })
      .catch(() => {});

    fetch(CONFIG.servicesUrl, opts)
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => { if (pendingWrites.current === 0) setServices(d.services || []); })
      .catch(() => {});
  }, []);

  // Polled, not just loaded once: Maintenance closing a repair happens in their
  // session, and this list has to notice without anyone reloading the page.
  useEffect(() => {
    fetchAmenities();
    const timer = setInterval(fetchAmenities, 8000);
    const onFocus = () => fetchAmenities();
    window.addEventListener('focus', onFocus);
    return () => { clearInterval(timer); window.removeEventListener('focus', onFocus); };
  }, [fetchAmenities]);

  /* Splice the saved row in rather than refetching: the response already carries the
     recomputed repair state, and a refetch here would race the poll. */
  const handleSaved = useCallback((item) => {
    setAmenities(prev => {
      const exists = prev.some(a => a.dbId === item.dbId);
      return exists ? prev.map(a => (a.dbId === item.dbId ? item : a)) : prev.concat([item]);
    });
  }, []);

  /* Dropped from the list here rather than refetched, for the same reason as
     handleSaved: the poll would race the delete and put the row back for a beat. */
  const handleRemoved = useCallback((amenity) => {
    setAmenities(prev => prev.filter(a => a.dbId !== amenity.dbId));
    setServices(prev => prev.filter(s => s.amenityId !== amenity.dbId));
  }, []);

  const handleReservationChanged = useCallback((reservation) => {
    setReservations(prev => prev.map(r => (r.id === reservation.id ? reservation : r)));
  }, []);

  const handleServiceSaved = useCallback((service) => {
    setServices(prev => {
      const exists = prev.some(s => s.id === service.id);
      return exists ? prev.map(s => (s.id === service.id ? service : s)) : prev.concat([service]);
    });
  }, []);

  const counts = {
    open: amenities.filter(a => a.status === 'Available').length,
    closed: amenities.filter(a => a.status === 'Temporarily Closed').length,
    repair: amenities.filter(a => a.status === 'Under Maintenance').length,
  };
  const needTelling = amenities.filter(a => a.status === 'Under Maintenance' && !a.repairInProgress && !a.awaitingVerification);
  const needChecking = amenities.filter(a => a.awaitingVerification);

  return (
    <div className="am" data-hms-no-edit="1">
      <header className="am-head">
        <div>
          <p className="am-eyebrow">Housekeeping</p>
          <h1 className="font-display">Amenities</h1>
          <p className="am-lead">
            The hotel's facilities, like the pool, gym and spa. Guests see them on your hotel
            website exactly as you set them here, including whether each one is open. When
            something breaks, tell Maintenance from here, then open it again once it is fixed.
          </p>
        </div>
        <div className="am-head-actions">
          <a href={CONFIG.backUrl} className="am-btn">
            <i className="fa-solid fa-arrow-left"></i> Back to Tasks
          </a>
          {canCustomize ? (
            <button type="button" className="am-btn is-solid" onClick={() => setEditing('new')}>
              <i className="fa-solid fa-plus"></i> Add a facility
            </button>
          ) : null}
        </div>
      </header>

      {!loading && amenities.length > 0 ? (
        <p className={`am-notice ${needTelling.length ? 'is-bad' : needChecking.length ? 'is-warn' : 'is-ok'}`}>
          <i className={`fa-solid ${needTelling.length || needChecking.length ? 'fa-bell' : 'fa-circle-check'}`}></i>
          <span>
            {counts.open} open to guests · {counts.closed} closed for now · {counts.repair} under repair.
            {needTelling.length ? ` ${needTelling.map(a => a.name).join(', ')} ${needTelling.length === 1 ? 'is' : 'are'} broken and Maintenance has not been told yet.` : ''}
            {needChecking.length ? ` Maintenance finished ${needChecking.map(a => a.name).join(', ')}. Check and open ${needChecking.length === 1 ? 'it' : 'them'} again.` : ''}
            {!needTelling.length && !needChecking.length ? ' Nothing needs you right now.' : ''}
          </span>
        </p>
      ) : null}

      {!loading && !canManage && !failed ? (
        <p className="am-notice is-warn">
          <i className="fa-solid fa-eye"></i>
          <span>You can look at this page, but only Housekeeping staff can change it.</span>
        </p>
      ) : null}

      <AmenitiesPanel
        amenities={amenities}
        loading={loading}
        failed={failed}
        canManage={canManage}
        canCustomize={canCustomize}
        task={task}
        editing={editing}
        setEditing={setEditing}
        onSaved={handleSaved}
        onRemoved={handleRemoved}
      />

      <ServicesPanel
        services={services}
        amenities={amenities}
        canManage={canManage}
        onSaved={handleServiceSaved}
      />

      <EventTurnaroundPanel
        reservations={reservations}
        canManage={canManage}
        onChanged={handleReservationChanged}
      />
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
