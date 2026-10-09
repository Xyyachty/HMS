@extends('students.builder.ops-shell')

@section('page-title', 'Room Inspections')

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

  /* Everything below reads the shell's tokens (--bg, --card, --border, --fg,
     --fg-muted, --accent), the same way Room Service does, so the page follows
     Template 1, Template 2 and a team's own site colours. Tints are mixed from
     those tokens rather than hard-coded.

     Shape rule: pills for tabs and status, 10px for buttons and fields, 14px
     for panels and cards. */
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }

  .hk {
    --hk-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --hk-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --hk-line: var(--border);
    --hk-ok: var(--success, #4ade80);
    --hk-warn: var(--warn, #f59e0b);
    --hk-fix: #a78bfa;
    --hk-bad: var(--danger, #fb7185);
    padding: 1.5rem 1.5rem 3rem;
    color: var(--fg);
  }
  :root[data-ops-theme="2"] .hk { --hk-fix: #7e22ce; }

  /* Page header */
  .hk-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.4rem; }
  .hk-eyebrow { color: var(--accent); font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; margin: 0 0 0.5rem; }
  .hk-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .hk-lead { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 64ch; line-height: 1.5; }

  .hk-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem;
    font: 600 0.88rem/1.15 var(--font-body, 'Outfit', sans-serif);
    padding: 0.8rem 1.15rem; border-radius: 10px; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); background: transparent; color: var(--accent);
    transition: background 0.15s, transform 0.1s, filter 0.15s;
  }
  .hk-btn:hover { background: var(--hk-tint); }
  .hk-btn:active { transform: translateY(1px); }
  .hk-btn.is-solid { background: var(--accent); color: var(--bg); }
  .hk-btn.is-solid:hover { filter: brightness(1.08); }
  .hk-btn.is-quiet { border-color: var(--hk-line); color: var(--fg-muted); }
  .hk-btn.is-quiet:hover { color: var(--fg); background: var(--hk-soft); }
  .hk-btn.is-wide { width: 100%; }
  .hk-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; filter: none; }
  .hk-btn:focus-visible, .hk-view:focus-visible, .hk-choice:focus-within, .hk-chip:focus-visible, .hk-link:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  /* How it works */
  .hk-how { list-style: none; margin: 0 0 1.25rem; padding: 0; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem; }
  .hk-how li { display: flex; gap: 0.7rem; align-items: flex-start; padding: 0.85rem 0.95rem; border-radius: 14px; background: var(--hk-soft); border: 1px solid var(--hk-line); }
  .hk-how-num {
    flex: none; width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.82rem; background: var(--hk-tint); color: var(--accent);
  }
  .hk-how div b { display: block; font-size: 0.86rem; color: var(--fg); margin-bottom: 0.15rem; }
  .hk-how div span { display: block; font-size: 0.78rem; color: var(--fg-muted); line-height: 1.4; }

  .hk-banner { display: flex; gap: 0.6rem; align-items: flex-start; margin: 0 0 1.25rem; padding: 0.85rem 1rem; border-radius: 14px; border: 1px solid color-mix(in srgb, var(--hk-warn) 40%, transparent); background: color-mix(in srgb, var(--hk-warn) 10%, transparent); font-size: 0.86rem; color: var(--fg); }
  .hk-banner i { color: var(--hk-warn); margin-top: 0.15rem; }

  /* Panels */
  .hk-panel { background: var(--card); border: 1px solid var(--hk-line); border-radius: 14px; padding: 1.2rem 1.3rem 1.4rem; margin-bottom: 1.25rem; }
  .hk-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .hk-panel-head h2 { margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--fg); }
  .hk-panel-head p { margin: 0.2rem 0 0; font-size: 0.82rem; color: var(--fg-muted); }

  /* Rooms at a glance */
  .hk-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem; }
  .hk-stat { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 0.95rem; border-radius: 12px; border: 1px solid var(--hk-line); background: var(--hk-soft); }
  .hk-stat-icon { flex: none; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; }
  .hk-stat div b { display: block; font-size: 1.35rem; line-height: 1.1; font-variant-numeric: tabular-nums; color: var(--fg); }
  .hk-stat div span { display: block; font-size: 0.78rem; color: var(--fg-muted); }
  .tone-ready  { background: color-mix(in srgb, var(--hk-ok) 16%, transparent);   color: var(--hk-ok); }
  .tone-guest  { background: var(--hk-tint); color: var(--accent); }
  .tone-clean  { background: color-mix(in srgb, var(--hk-warn) 16%, transparent); color: var(--hk-warn); }
  .tone-fix    { background: color-mix(in srgb, var(--hk-fix) 16%, transparent);  color: var(--hk-fix); }
  .tone-done   { background: color-mix(in srgb, var(--hk-ok) 16%, transparent);   color: var(--hk-ok); }
  .tone-muted  { background: color-mix(in srgb, var(--fg) 8%, transparent);       color: var(--fg-muted); }

  .hk-link { display: inline-flex; align-items: center; gap: 0.4rem; background: none; border: 0; padding: 0.3rem 0; cursor: pointer; color: var(--accent); font: 600 0.84rem/1 var(--font-body, 'Outfit', sans-serif); }
  .hk-link:hover { text-decoration: underline; }

  .hk-legend { display: flex; flex-wrap: wrap; gap: 0.4rem 1rem; margin: 1rem 0 0.9rem; font-size: 0.78rem; color: var(--fg-muted); }
  .hk-legend span { display: inline-flex; align-items: center; gap: 0.4rem; }
  .hk-legend i { width: 10px; height: 10px; border-radius: 3px; background: currentColor; }
  .hk-floor { margin-top: 0.85rem; }
  .hk-floor h3 { margin: 0 0 0.45rem; font-size: 0.74rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--fg-muted); }
  .hk-tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(92px, 1fr)); gap: 0.45rem; }
  .hk-tile { min-width: 0; padding: 0.5rem 0.55rem; border-radius: 10px; border: 1px solid transparent; }
  .hk-tile b { display: block; font-size: 0.86rem; color: var(--fg); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .hk-tile small { display: block; font-size: 0.68rem; margin-top: 0.1rem; }
  .hk-tile.tone-ready { border-color: color-mix(in srgb, var(--hk-ok) 30%, transparent); }
  .hk-tile.tone-guest { border-color: color-mix(in srgb, var(--accent) 30%, transparent); }
  .hk-tile.tone-clean { border-color: color-mix(in srgb, var(--hk-warn) 35%, transparent); }
  .hk-tile.tone-fix   { border-color: color-mix(in srgb, var(--hk-fix) 35%, transparent); }

  /* Toolbar */
  .hk-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 0.9rem; }
  .hk-views { box-sizing: border-box; max-width: 100%; display: inline-flex; flex-wrap: wrap; gap: 0.3rem; padding: 0.3rem; border-radius: 999px; background: var(--hk-soft); border: 1px solid var(--hk-line); }
  .hk-view {
    display: inline-flex; align-items: center; gap: 0.5rem;
    font: 600 0.84rem/1 var(--font-body, 'Outfit', sans-serif);
    padding: 0.6rem 0.95rem; border-radius: 999px; cursor: pointer;
    border: 0; background: transparent; color: var(--fg-muted);
    transition: background 0.15s, color 0.15s;
  }
  .hk-view:hover { color: var(--fg); }
  .hk-view.is-on { background: var(--accent); color: var(--bg); }
  .hk-view i { font-size: 0.78rem; }
  .hk-count {
    min-width: 1.45rem; padding: 0.2rem 0.4rem; border-radius: 999px; text-align: center;
    font-size: 0.74rem; font-variant-numeric: tabular-nums;
    background: color-mix(in srgb, var(--fg) 8%, transparent);
  }
  .hk-view.is-on .hk-count { background: color-mix(in srgb, var(--bg) 22%, transparent); }
  .hk-live { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--fg-muted); }
  .hk-live::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--hk-ok); }

  .hk-search { position: relative; margin-bottom: 1.1rem; }
  .hk-search i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.8rem; pointer-events: none; }

  .hk-input {
    box-sizing: border-box; width: 100%;
    background: var(--hk-soft); border: 1px solid var(--hk-line);
    border-radius: 10px; padding: 0.75rem 0.9rem; color: var(--fg);
    font: 400 0.9rem/1.4 var(--font-body, 'Outfit', sans-serif);
    outline: none; transition: border-color 0.15s;
  }
  .hk-input:focus { border-color: var(--accent); }
  .hk-input::placeholder { color: var(--fg-muted); opacity: 0.7; }
  .hk-search .hk-input { padding-left: 2.3rem; }
  textarea.hk-input { resize: vertical; min-height: 4.2rem; }

  /* Room cards */
  .hk-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(360px, 100%), 1fr)); gap: 1rem; align-items: start; }
  .hk-card { min-width: 0; border: 1px solid var(--hk-line); border-radius: 14px; background: var(--hk-soft); padding: 1.05rem 1.1rem 1.1rem; display: flex; flex-direction: column; gap: 0.9rem; }
  .hk-card.is-mine { border-color: color-mix(in srgb, var(--accent) 55%, transparent); }
  .hk-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
  .hk-room { display: block; font-size: 1.15rem; font-weight: 700; color: var(--fg); line-height: 1.2; }
  .hk-sub { display: block; font-size: 0.82rem; color: var(--fg-muted); margin-top: 0.25rem; line-height: 1.4; }
  .hk-pill { flex: none; display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.76rem; font-weight: 600; white-space: nowrap; }

  /* The steps a room goes through, with this room's place on them */
  .hk-track { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .hk-track li { position: relative; display: flex; flex-direction: column; align-items: center; gap: 0.35rem; font-size: 0.68rem; color: var(--fg-muted); text-align: center; }
  .hk-track li::before { content: ''; position: absolute; top: 6px; left: -50%; width: 100%; height: 2px; background: var(--hk-line); }
  .hk-track li:first-child::before { display: none; }
  .hk-track li.is-done::before, .hk-track li.is-now::before { background: var(--accent); }
  .hk-dot { position: relative; z-index: 1; width: 14px; height: 14px; border-radius: 50%; background: var(--card); border: 2px solid var(--hk-line); }
  .hk-track li.is-done .hk-dot { background: var(--accent); border-color: var(--accent); }
  .hk-track li.is-now .hk-dot { background: var(--card); border-color: var(--accent); box-shadow: 0 0 0 4px var(--hk-tint); }
  .hk-track li.is-skip .hk-dot { border-style: dashed; }
  .hk-track li.is-skip { text-decoration: line-through; opacity: 0.7; }
  .hk-track li.is-now, .hk-track li.is-done { color: var(--fg); }
  .hk-track li.is-now { font-weight: 700; }

  .hk-found { margin: 0; padding: 0.7rem 0.8rem; border-radius: 10px; background: var(--card); border: 1px solid var(--hk-line); font-size: 0.85rem; color: var(--fg); line-height: 1.45; }
  .hk-found small { display: block; font-size: 0.7rem; color: var(--fg-muted); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.2rem; }

  .hk-issues { list-style: none; margin: 0; padding: 0; display: grid; gap: 0.5rem; }
  .hk-issue { padding: 0.7rem 0.8rem; border-radius: 10px; background: var(--card); border: 1px solid var(--hk-line); }
  .hk-issue-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 0.6rem; }
  .hk-issue b { font-size: 0.85rem; color: var(--fg); }
  .hk-issue p { margin: 0.25rem 0 0; font-size: 0.82rem; color: var(--fg-muted); line-height: 1.4; }
  .hk-issue .hk-pill { font-size: 0.7rem; padding: 0.25rem 0.55rem; }

  .hk-step { padding-top: 0.9rem; border-top: 1px dashed var(--hk-line); display: grid; gap: 0.75rem; }
  .hk-step-title { margin: 0; font-size: 0.92rem; font-weight: 700; color: var(--fg); }
  .hk-step-hint { margin: -0.45rem 0 0; font-size: 0.8rem; color: var(--fg-muted); line-height: 1.45; }
  .hk-label { display: block; font-size: 0.82rem; font-weight: 600; color: var(--fg); margin-bottom: 0.4rem; }
  .hk-label em { font-style: normal; font-weight: 400; color: var(--fg-muted); }
  .hk-error { margin: 0.4rem 0 0; color: var(--hk-bad); font-size: 0.8rem; }
  .hk-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
  .hk-actions .hk-btn { flex: 1 1 auto; }

  /* What did you find? — four big choices */
  .hk-choices { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem; }
  .hk-choice { position: relative; display: flex; gap: 0.6rem; align-items: flex-start; padding: 0.7rem 0.75rem; border-radius: 10px; border: 1px solid var(--hk-line); background: var(--card); cursor: pointer; transition: border-color 0.15s, background 0.15s; }
  .hk-choice:hover { border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .hk-choice input { position: absolute; opacity: 0; pointer-events: none; }
  .hk-choice > i { flex: none; width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
  .hk-choice b { display: block; font-size: 0.84rem; color: var(--fg); line-height: 1.25; }
  .hk-choice span { display: block; font-size: 0.74rem; color: var(--fg-muted); margin-top: 0.15rem; line-height: 1.35; }
  .hk-choice.is-on { border-color: var(--accent); background: var(--hk-tint); }

  .hk-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; }
  .hk-chip { padding: 0.5rem 0.8rem; border-radius: 999px; border: 1px solid var(--hk-line); background: var(--card); color: var(--fg); cursor: pointer; font: 500 0.8rem/1.2 var(--font-body, 'Outfit', sans-serif); transition: border-color 0.15s, background 0.15s; }
  .hk-chip:hover { border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .hk-chip.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .hk-chip-group + .hk-chip-group { margin-top: 0.6rem; }
  .hk-chip-group small { display: block; font-size: 0.72rem; color: var(--fg-muted); margin-bottom: 0.35rem; }

  .hk-note { margin: 0; padding: 0.75rem 0.85rem; border-radius: 10px; font-size: 0.84rem; line-height: 1.45; display: flex; gap: 0.55rem; align-items: flex-start; }
  .hk-note i { margin-top: 0.15rem; }
  .hk-note.tone-fix, .hk-note.tone-done, .hk-note.tone-muted, .hk-note.tone-guest { color: var(--fg); }
  .hk-note.tone-fix i { color: var(--hk-fix); }
  .hk-note.tone-done i { color: var(--hk-ok); }
  .hk-note.tone-guest i { color: var(--accent); }
  .hk-meta { font-size: 0.76rem; color: var(--fg-muted); }

  /* Empty / loading */
  .hk-empty { border: 1.5px dashed var(--hk-line); border-radius: 14px; padding: 2.4rem 1.5rem; text-align: center; }
  .hk-empty-icon { width: 56px; height: 56px; margin: 0 auto 0.9rem; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; background: var(--hk-tint); color: var(--accent); }
  .hk-empty h2 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .hk-empty p { margin: 0.4rem auto 0; max-width: 50ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }
  .hk-skel { height: 220px; border-radius: 14px; border: 1px solid var(--hk-line); background: linear-gradient(90deg, var(--hk-soft) 0%, color-mix(in srgb, var(--fg) 8%, transparent) 50%, var(--hk-soft) 100%); background-size: 200% 100%; animation: hk-shimmer 1.4s ease-in-out infinite; }
  @keyframes hk-shimmer { from { background-position: 100% 0; } to { background-position: -100% 0; } }
  @media (prefers-reduced-motion: reduce) { .hk-skel { animation: none; } }

  @media (max-width: 960px) {
    .hk-how, .hk-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  @media (max-width: 560px) {
    .hk { padding: 1.1rem 1rem 2.5rem; }
    .hk-how, .hk-stats, .hk-choices { grid-template-columns: 1fr; }
    .hk-views { width: 100%; border-radius: 14px; }
  }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_INSPECTIONS = {
    backUrl: @json(route('students.dashboard', ['section' => 'tasks'])),
    indexUrl: @json(route('students.hotel.inspections.index')),
    roomsUrl: @json(route('students.hotel.rooms.index')),
    findings: @json(\App\Models\HotelRoomInspection::FINDINGS),
    statuses: @json(\App\Models\HotelRoomInspection::STATUSES),
    categories: @json(array_intersect_key(\App\Models\HotelComplaint::CATEGORY_DEPARTMENTS, array_flip(\App\Models\HotelComplaint::categoriesFor('housekeeping', 'maintenance')))),
    serviceCategories: @json(\App\Models\HotelComplaint::SERVICE_CATEGORIES),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo, useRef, useId } = React;

const CFG = window.HMS_INSPECTIONS;
const FINDINGS = CFG.findings;
const OPEN_STATUSES = ['Pending', 'Inspecting', 'Awaiting Repair', 'Awaiting Re-inspection'];

// Mirrors App\Models\HotelRoomInspection::STATUSES, each named for what the
// housekeeper sees and does rather than for the stored status.
const STAGES = {
  'Pending':                { label: 'Waiting to be checked',  icon: 'fa-hourglass-half', tone: 'tone-clean' },
  'Inspecting':             { label: 'Being checked',          icon: 'fa-magnifying-glass', tone: 'tone-guest' },
  'Awaiting Repair':        { label: 'With Maintenance',       icon: 'fa-screwdriver-wrench', tone: 'tone-fix' },
  'Awaiting Re-inspection': { label: 'Fixed, check it again',  icon: 'fa-rotate', tone: 'tone-clean' },
  'Completed':              { label: 'Ready for next guest',   icon: 'fa-circle-check', tone: 'tone-done' },
};

const TABS = [
  { key: 'todo',     label: 'To do',            icon: 'fa-list-check', match: i => i.status === 'Pending' || i.status === 'Inspecting' || i.status === 'Awaiting Re-inspection' },
  { key: 'repair',   label: 'With Maintenance', icon: 'fa-screwdriver-wrench', match: i => i.status === 'Awaiting Repair' },
  { key: 'done',     label: 'Done',             icon: 'fa-circle-check', match: i => i.status === 'Completed' },
  { key: 'all',      label: 'All rooms',        icon: 'fa-layer-group', match: () => true },
];

// The four keys of App\Models\HotelRoomInspection::FINDINGS, in plain words.
const FINDING_TEXT = {
  'Cleaning Only':     { title: 'All good, only needed cleaning', hint: 'Nothing broken or missing.', icon: 'fa-broom', tone: 'tone-done' },
  'Damaged Equipment': { title: 'Something is broken',            hint: 'TV, aircon, lamp, appliance.', icon: 'fa-plug-circle-xmark', tone: 'tone-fix' },
  'Needs Repair':      { title: 'Something needs fixing',         hint: 'Furniture, door, faucet, wall.', icon: 'fa-hammer', tone: 'tone-fix' },
  'Missing Items':     { title: 'Something is missing',           hint: 'A hotel item is gone from the room.', icon: 'fa-box-open', tone: 'tone-clean' },
};

// What a complaint's stored status means to the housekeeper waiting on it.
const ISSUE_STATUS = {
  'Pending':     { label: 'Not started yet', tone: 'tone-clean' },
  'In Progress': { label: 'Being fixed',     tone: 'tone-fix' },
  'Resolved':    { label: 'Fixed',           tone: 'tone-done' },
  'Closed':      { label: 'Fixed',           tone: 'tone-done' },
  'Cancelled':   { label: 'Cancelled',       tone: 'tone-muted' },
};

// Room status, as the room list and the floor map show it. A room marked
// Available with a checked-in stay on it has a guest inside.
const ROOM_STATE = {
  ready: { label: 'Ready for guests', short: 'Ready',     icon: 'fa-bed',              tone: 'tone-ready' },
  guest: { label: 'Guest staying',    short: 'Guest in',  icon: 'fa-user',             tone: 'tone-guest' },
  clean: { label: 'Needs cleaning',   short: 'Cleaning',  icon: 'fa-broom',            tone: 'tone-clean' },
  fix:   { label: 'Being repaired',   short: 'Repairs',   icon: 'fa-screwdriver-wrench', tone: 'tone-fix' },
};

function roomState(room) {
  if (room.status === 'Maintenance') return 'fix';
  if (room.status === 'Cleaning') return 'clean';
  if (room.reservation && room.reservation.status === 'Checked In') return 'guest';
  return 'ready';
}

// Problems a room inspection can turn up. The service categories (rude staff,
// slow service) belong to guest complaints, not to a look around an empty room.
const PROBLEM_GROUPS = (() => {
  const service = new Set(CFG.serviceCategories || []);
  const groups = { maintenance: [], housekeeping: [] };
  Object.entries(CFG.categories || {}).forEach(([name, dept]) => {
    if (!service.has(name) && groups[dept]) groups[dept].push(name);
  });
  return groups;
})();

function csrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function formatWhen(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function plural(n, one, many) {
  return n + ' ' + (n === 1 ? one : many);
}

function Pill({ tone, icon, children }) {
  return (
    <span className={`hk-pill ${tone}`}>
      {icon ? <i className={`fa-solid ${icon}`}></i> : null}
      {children}
    </span>
  );
}

function RoomsAtAGlance({ rooms }) {
  const [showMap, setShowMap] = useState(false);

  const counts = { ready: 0, guest: 0, clean: 0, fix: 0 };
  rooms.forEach(r => { counts[roomState(r)] += 1; });

  // Grouped by category in the order the rooms come back (by id), so the
  // floor map reads Classic, Superior, Deluxe… the same as the room grid.
  const floors = [];
  rooms.forEach(room => {
    const name = room.category || 'Other rooms';
    let floor = floors.find(f => f.name === name);
    if (!floor) { floor = { name, rooms: [] }; floors.push(floor); }
    floor.rooms.push(room);
  });

  if (!rooms.length) return null;

  return (
    <section className="hk-panel" aria-labelledby="hk-glance">
      <div className="hk-panel-head">
        <div>
          <h2 id="hk-glance">All rooms right now</h2>
          <p>{plural(rooms.length, 'room', 'rooms')} in the hotel.</p>
        </div>
        <button type="button" className="hk-link" aria-expanded={showMap} onClick={() => setShowMap(v => !v)}>
          <i className={`fa-solid ${showMap ? 'fa-chevron-up' : 'fa-table-cells'}`}></i>
          {showMap ? 'Hide the room map' : 'Show every room'}
        </button>
      </div>

      <div className="hk-stats">
        {['clean', 'fix', 'guest', 'ready'].map(key => (
          <div key={key} className="hk-stat">
            <span className={`hk-stat-icon ${ROOM_STATE[key].tone}`}><i className={`fa-solid ${ROOM_STATE[key].icon}`}></i></span>
            <div>
              <b>{counts[key]}</b>
              <span>{ROOM_STATE[key].label}</span>
            </div>
          </div>
        ))}
      </div>

      {showMap ? (
        <div>
          <div className="hk-legend">
            {Object.values(ROOM_STATE).map(s => (
              <span key={s.label}><i className={s.tone}></i>{s.label}</span>
            ))}
          </div>
          {floors.map(floor => (
            <div key={floor.name} className="hk-floor">
              <h3>{floor.name}</h3>
              <div className="hk-tiles">
                {floor.rooms.map(room => {
                  const state = ROOM_STATE[roomState(room)];
                  // "Classic 101" under the Classic heading reads as just "101".
                  const prefix = floor.name + ' ';
                  const short = room.name.startsWith(prefix) ? room.name.slice(prefix.length) : room.name;
                  return (
                    <div key={room.id} className={`hk-tile ${state.tone}`} title={`${room.name}: ${state.label}`}>
                      <b>{short}</b>
                      <small>{state.short}</small>
                    </div>
                  );
                })}
              </div>
            </div>
          ))}
        </div>
      ) : null}
    </section>
  );
}

function Track({ inspection }) {
  const status = inspection.status;
  const hadRepairs = inspection.issues.length > 0;
  const steps = [
    { key: 'wait',  label: 'Guest left' },
    { key: 'check', label: 'Check room' },
    { key: 'fix',   label: 'Repairs' },
    { key: 'ready', label: 'Ready' },
  ];
  const nowIndex = {
    'Pending': 1,
    'Inspecting': 1,
    'Awaiting Repair': 2,
    'Awaiting Re-inspection': 3,
    'Completed': 4,
  }[status] ?? 1;

  return (
    <ol className="hk-track" aria-label={`Where this room is: ${STAGES[status] ? STAGES[status].label : status}`}>
      {steps.map((s, i) => {
        const skipped = s.key === 'fix' && !hadRepairs && nowIndex > 2;
        let cls = i < nowIndex ? 'is-done' : i === nowIndex ? 'is-now' : '';
        if (skipped) cls = 'is-skip';
        return (
          <li key={s.key} className={cls}>
            <span className="hk-dot"></span>
            {skipped ? 'No repairs' : s.label}
          </li>
        );
      })}
    </ol>
  );
}

function IssueForm({ suggested, busy, title, onSubmit, onCancel }) {
  const [category, setCategory] = useState(suggested || PROBLEM_GROUPS.maintenance[0] || 'Other Maintenance Problems');
  const [details, setDetails] = useState('');
  const [error, setError] = useState('');

  const detailsId = useId();

  useEffect(() => { if (suggested) setCategory(suggested); }, [suggested]);

  const team = (CFG.categories || {})[category] === 'housekeeping' ? 'Housekeeping' : 'Maintenance';

  const submit = () => {
    if (!details.trim()) { setError('Write what you saw so the team knows what to bring.'); return; }
    setError('');
    onSubmit({ category, details: details.trim() });
  };

  return (
    <div className="hk-step">
      {title ? <p className="hk-step-title">{title}</p> : null}
      <div>
        <span className="hk-label">What kind of problem?</span>
        <div className="hk-chip-group">
          <small>Maintenance fixes these</small>
          <div className="hk-chips">
            {PROBLEM_GROUPS.maintenance.map(name => (
              <button key={name} type="button" className={`hk-chip ${category === name ? 'is-on' : ''}`} aria-pressed={category === name} onClick={() => setCategory(name)}>
                {name}
              </button>
            ))}
          </div>
        </div>
        {PROBLEM_GROUPS.housekeeping.length ? (
          <div className="hk-chip-group">
            <small>Housekeeping handles these</small>
            <div className="hk-chips">
              {PROBLEM_GROUPS.housekeeping.map(name => (
                <button key={name} type="button" className={`hk-chip ${category === name ? 'is-on' : ''}`} aria-pressed={category === name} onClick={() => setCategory(name)}>
                  {name}
                </button>
              ))}
            </div>
          </div>
        ) : null}
      </div>
      <div>
        <label className="hk-label" htmlFor={detailsId}>What exactly did you see?</label>
        <textarea
          id={detailsId}
          className="hk-input"
          rows={2}
          placeholder="Example: The bathroom faucet keeps dripping."
          value={details}
          onChange={e => { setDetails(e.target.value); if (error) setError(''); }}
        />
        {error ? <p className="hk-error">{error}</p> : null}
      </div>
      <div className="hk-actions">
        {onCancel ? <button type="button" className="hk-btn is-quiet" onClick={onCancel}>Cancel</button> : null}
        <button type="button" className="hk-btn is-solid" disabled={busy} onClick={submit}>
          <i className="fa-solid fa-paper-plane"></i>
          {busy ? 'Sending…' : `Send to ${team}`}
        </button>
      </div>
    </div>
  );
}

function CheckingStep({ inspection, busy, onRecord, onFinishClean, onReport }) {
  const [choice, setChoice] = useState(inspection.finding || '');
  const [notes, setNotes] = useState(inspection.notes || '');
  const [error, setError] = useState('');

  useEffect(() => { setNotes(inspection.notes || ''); }, [inspection.notes]);

  const isProblem = choice && FINDINGS[choice] !== null;

  const finishClean = () => {
    if (!choice) { setError('Pick what you found first.'); return; }
    onFinishClean(choice, notes);
  };

  return (
    <div className="hk-step">
      <p className="hk-step-title">What did you find in the room?</p>
      <div className="hk-choices" role="radiogroup" aria-label="What did you find?">
        {Object.keys(FINDINGS).map(key => {
          const f = FINDING_TEXT[key] || { title: key, hint: '', icon: 'fa-circle', tone: 'tone-muted' };
          return (
            <label key={key} className={`hk-choice ${choice === key ? 'is-on' : ''}`}>
              <input type="radio" name={`finding-${inspection.id}`} value={key} checked={choice === key} onChange={() => { setChoice(key); setError(''); }} />
              <i className={`fa-solid ${f.icon} ${f.tone}`}></i>
              <div><b>{f.title}</b><span>{f.hint}</span></div>
            </label>
          );
        })}
      </div>
      {error ? <p className="hk-error">{error}</p> : null}

      <div>
        <label className="hk-label" htmlFor={`hk-notes-${inspection.id}`}>Notes <em>(optional)</em></label>
        <textarea id={`hk-notes-${inspection.id}`} className="hk-input" rows={2} placeholder="Anything worth remembering about this room." value={notes} onChange={e => setNotes(e.target.value)} />
      </div>

      {isProblem ? (
        <IssueForm
          title="Tell the team what to fix"
          suggested={FINDINGS[choice]}
          busy={busy}
          onSubmit={payload => onReport(choice, notes, payload)}
        />
      ) : (
        <div className="hk-actions">
          <button type="button" className="hk-btn is-quiet" disabled={busy || !choice} onClick={() => onRecord(choice, notes)}>
            <i className="fa-regular fa-floppy-disk"></i> Save, finish later
          </button>
          <button type="button" className="hk-btn is-solid" disabled={busy} onClick={finishClean}>
            <i className="fa-solid fa-circle-check"></i> Room is clean, mark it ready
          </button>
        </div>
      )}
    </div>
  );
}

function RoomCard({ inspection, canInspect, busy, onAction, onReportIssue }) {
  const [reportingAgain, setReportingAgain] = useState(false);
  const status = inspection.status;
  const stage = STAGES[status] || { label: status, icon: 'fa-circle', tone: 'tone-muted' };
  const finding = inspection.finding ? (FINDING_TEXT[inspection.finding] || { title: inspection.finding }) : null;

  // Record the finding first, then do the next thing — so the card always
  // keeps what the housekeeper chose, even if the second call fails.
  const recordThen = (choice, notes, next) =>
    onAction(inspection.id, { action: 'record', finding: choice, notes }).then(ok => (ok && next ? next() : ok));

  return (
    <article className={`hk-card ${canInspect && (status === 'Pending' || status === 'Inspecting' || status === 'Awaiting Re-inspection') ? 'is-mine' : ''}`}>
      <div className="hk-card-top">
        <div style={{ minWidth: 0 }}>
          <span className="hk-room">Room {inspection.roomName}</span>
          <span className="hk-sub">
            {inspection.guestName ? <>Guest who left: {inspection.guestName}<br /></> : null}
            Checked out {formatWhen(inspection.checkedOutAt)}
          </span>
        </div>
        <Pill tone={stage.tone} icon={stage.icon}>{stage.label}</Pill>
      </div>

      <Track inspection={inspection} />

      {finding && status !== 'Inspecting' ? (
        <p className="hk-found">
          <small>What was found</small>
          {finding.title}{inspection.notes ? `: ${inspection.notes}` : ''}
        </p>
      ) : null}

      {inspection.issues.length > 0 ? (
        <ul className="hk-issues" aria-label="Problems sent to other teams">
          {inspection.issues.map(issue => {
            const s = ISSUE_STATUS[issue.status] || { label: issue.status, tone: 'tone-muted' };
            return (
              <li key={issue.id} className="hk-issue">
                <div className="hk-issue-top">
                  <b>{issue.category}</b>
                  <Pill tone={s.tone}>{s.label}</Pill>
                </div>
                <p>{issue.details}</p>
                <p>Sent to {issue.departmentLabel}{issue.resolutionNote ? `. They wrote: ${issue.resolutionNote}` : ''}</p>
              </li>
            );
          })}
        </ul>
      ) : null}

      {!canInspect ? null : status === 'Pending' ? (
        <div className="hk-step">
          <p className="hk-step-title">This room is waiting for you</p>
          <p className="hk-step-hint">Press the button when you go into the room to look it over.</p>
          <button type="button" className="hk-btn is-solid is-wide" disabled={busy} onClick={() => onAction(inspection.id, { action: 'start' })}>
            <i className="fa-solid fa-door-open"></i> Start checking this room
          </button>
        </div>
      ) : status === 'Inspecting' ? (
        <CheckingStep
          inspection={inspection}
          busy={busy}
          onRecord={(choice, notes) => recordThen(choice, notes)}
          onFinishClean={(choice, notes) => recordThen(choice, notes, () => onAction(inspection.id, { action: 'complete' }))}
          onReport={(choice, notes, payload) => recordThen(choice, notes, () => onReportIssue(inspection.id, payload))}
        />
      ) : status === 'Awaiting Repair' ? (
        <p className="hk-note tone-fix">
          <i className="fa-solid fa-screwdriver-wrench"></i>
          <span>Maintenance is fixing this room. You do not need to do anything yet. The room comes back to your To do list when they finish.</span>
        </p>
      ) : status === 'Awaiting Re-inspection' ? (
        reportingAgain ? (
          <IssueForm
            title="What is still wrong?"
            suggested={null}
            busy={busy}
            onCancel={() => setReportingAgain(false)}
            onSubmit={payload => onReportIssue(inspection.id, payload).then(ok => { if (ok) setReportingAgain(false); })}
          />
        ) : (
          <div className="hk-step">
            <p className="hk-step-title">Maintenance says it is fixed</p>
            <p className="hk-step-hint">Look at the room one more time before the next guest gets it.</p>
            <div className="hk-actions">
              <button type="button" className="hk-btn is-quiet" disabled={busy} onClick={() => setReportingAgain(true)}>
                <i className="fa-solid fa-triangle-exclamation"></i> Still a problem
              </button>
              <button type="button" className="hk-btn is-solid" disabled={busy} onClick={() => onAction(inspection.id, { action: 'complete' })}>
                <i className="fa-solid fa-circle-check"></i> All fixed, mark it ready
              </button>
            </div>
          </div>
        )
      ) : (
        <p className="hk-note tone-done">
          <i className="fa-solid fa-circle-check"></i>
          <span>Ready for the next guest. Checked by {inspection.completedBy || 'Housekeeping'} on {formatWhen(inspection.completedAt)}.</span>
        </p>
      )}
    </article>
  );
}

function App() {
  const [inspections, setInspections] = useState([]);
  const [rooms, setRooms] = useState([]);
  const [canInspect, setCanInspect] = useState(false);
  const [tab, setTab] = useState('todo');
  const [search, setSearch] = useState('');
  const [busy, setBusy] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [failed, setFailed] = useState(false);
  const pendingWrites = useRef(0);

  const load = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(CFG.indexUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => { if (!r.ok) throw new Error(); return r.json(); })
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.inspections)) setInspections(data.inspections);
        setCanInspect(!!data.can_inspect);
        setFailed(false);
        setLoaded(true);
      })
      .catch(() => { setFailed(true); setLoaded(true); });
  }, []);

  // Rooms poll with the inspections so the counts move when a room is marked
  // ready, instead of staying frozen at whatever the page first loaded.
  const loadRooms = useCallback(() => {
    fetch(CFG.roomsUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => { if (Array.isArray(data.rooms)) setRooms(data.rooms); })
      .catch(() => {});
  }, []);

  useEffect(() => {
    load();
    loadRooms();
    const id = setInterval(() => { load(); loadRooms(); }, 8000);
    const onFocus = () => { load(); loadRooms(); };
    window.addEventListener('focus', onFocus);
    return () => { clearInterval(id); window.removeEventListener('focus', onFocus); };
  }, [load, loadRooms]);

  const fail = (message) => window.toast && window.toast(message);

  // Both writes resolve to true on success, so a card can chain the next step.
  const send = (url, method, payload, errorText, okText) => {
    setBusy(true);
    pendingWrites.current += 1;
    return fetch(url, {
      method,
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.message || errorText);
        return data;
      })
      .then(data => {
        if (data.inspection) {
          setInspections(prev => prev.map(i => (i.id === data.inspection.id ? data.inspection : i)));
          if (okText && window.toast) window.toast(okText);
        }
        return true;
      })
      .catch(e => { fail(e.message); return false; })
      .finally(() => {
        setBusy(false);
        pendingWrites.current = Math.max(0, pendingWrites.current - 1);
        loadRooms();
      });
  };

  const runAction = (id, payload) => send(
    `${CFG.indexUrl}/${id}`, 'PATCH', payload, 'Could not update this room.',
    payload.action === 'complete' ? 'Room marked ready for the next guest.' : payload.action === 'start' ? 'Started. Look the room over.' : 'Saved.'
  );

  const reportIssue = (id, payload) => send(
    `${CFG.indexUrl}/${id}/issues`, 'POST', payload, 'Could not send this problem.', 'Problem sent. The team has been told.'
  );

  const current = TABS.find(t => t.key === tab) || TABS[0];

  const visible = useMemo(() => {
    const q = search.trim().toLowerCase();
    return inspections.filter(i => {
      if (!current.match(i)) return false;
      if (!q) return true;
      return [i.roomName, i.guestName, i.finding].some(field => String(field || '').toLowerCase().includes(q));
    });
  }, [inspections, current, search]);

  const todoCount = inspections.filter(TABS[0].match).length;
  const openCount = inspections.filter(i => OPEN_STATUSES.includes(i.status)).length;

  let emptyTitle = 'Nothing here';
  let emptyText = 'Pick another tab to see the other rooms.';
  let emptyIcon = current.icon;
  if (search.trim()) {
    emptyTitle = 'No room matches your search';
    emptyText = 'Check the room number or guest name, or clear the search box.';
    emptyIcon = 'fa-magnifying-glass';
  } else if (inspections.length === 0) {
    emptyTitle = 'No guest has checked out yet';
    emptyText = 'When the Front Desk checks a guest out, their room appears here so you can clean and check it.';
    emptyIcon = 'fa-broom';
  } else if (tab === 'todo') {
    emptyTitle = 'All caught up';
    emptyText = 'No room is waiting for you. New rooms show up here on their own when a guest checks out.';
    emptyIcon = 'fa-mug-hot';
  } else if (tab === 'repair') {
    emptyTitle = 'No room is with Maintenance';
    emptyText = 'If you find something broken or missing while checking a room, it will wait here until Maintenance fixes it.';
  } else if (tab === 'done') {
    emptyTitle = 'No room finished yet';
    emptyText = 'Rooms you mark ready for the next guest will be listed here.';
  }

  return (
    <div className="hk" data-hms-no-edit="1">
      <header className="hk-head">
        <div>
          <p className="hk-eyebrow">Housekeeping</p>
          <h1 className="font-display">Room Inspections</h1>
          <p className="hk-lead">
            When a guest checks out, their room shows up here. Go to the room, look it over,
            and tell the system what you found. If everything is fine, mark it ready for the
            next guest. If something is broken or missing, send it to Maintenance.
          </p>
        </div>
        <a href={CFG.backUrl} className="hk-btn">
          <i className="fa-solid fa-arrow-left"></i> Back to Tasks
        </a>
      </header>

      <ol className="hk-how" aria-label="How it works">
        <li><span className="hk-how-num">1</span><div><b>Guest checks out</b><span>The room appears in your To do list.</span></div></li>
        <li><span className="hk-how-num">2</span><div><b>Check the room</b><span>Press Start, then look around the room.</span></div></li>
        <li><span className="hk-how-num">3</span><div><b>Say what you found</b><span>All good, or send a problem to Maintenance.</span></div></li>
        <li><span className="hk-how-num">4</span><div><b>Mark it ready</b><span>The room is free for the next guest.</span></div></li>
      </ol>

      {loaded && !canInspect && !failed ? (
        <p className="hk-banner">
          <i className="fa-solid fa-eye"></i>
          <span>You can look at this page, but only Housekeeping staff can check rooms and mark them ready.</span>
        </p>
      ) : null}

      <RoomsAtAGlance rooms={rooms} />

      <section className="hk-panel" aria-labelledby="hk-list">
        <div className="hk-panel-head">
          <div>
            <h2 id="hk-list">Rooms to inspect</h2>
            <p>{todoCount === 0 ? 'Nothing waiting for you right now.' : `${plural(todoCount, 'room needs', 'rooms need')} you now.`}{openCount > todoCount ? ` ${plural(openCount - todoCount, 'room is', 'rooms are')} with Maintenance.` : ''}</p>
          </div>
          <span className="hk-live">Updates on its own</span>
        </div>

        <div className="hk-toolbar">
          <div className="hk-views" role="group" aria-label="Show rooms by step">
            {TABS.map(t => (
              <button key={t.key} type="button" className={`hk-view ${tab === t.key ? 'is-on' : ''}`} aria-pressed={tab === t.key} onClick={() => setTab(t.key)}>
                <i className={`fa-solid ${t.icon}`}></i>
                {t.label}
                <span className="hk-count">{inspections.filter(t.match).length}</span>
              </button>
            ))}
          </div>
        </div>

        <div className="hk-search">
          <i className="fa-solid fa-magnifying-glass"></i>
          <input type="text" className="hk-input" placeholder="Search a room number or guest name" aria-label="Search a room number or guest name" value={search} onChange={e => setSearch(e.target.value)} />
        </div>

        {!loaded ? (
          <div className="hk-grid" aria-busy="true" aria-label="Loading rooms">
            <div className="hk-skel"></div><div className="hk-skel"></div>
          </div>
        ) : failed && inspections.length === 0 ? (
          <div className="hk-empty" role="alert">
            <div className="hk-empty-icon"><i className="fa-solid fa-wifi"></i></div>
            <h2>Could not load the rooms</h2>
            <p>Check your internet connection. The page tries again on its own every few seconds.</p>
          </div>
        ) : visible.length === 0 ? (
          <div className="hk-empty">
            <div className="hk-empty-icon"><i className={`fa-solid ${emptyIcon}`}></i></div>
            <h2>{emptyTitle}</h2>
            <p>{emptyText}</p>
          </div>
        ) : (
          <div className="hk-grid">
            {visible.map(inspection => (
              <RoomCard
                key={inspection.id}
                inspection={inspection}
                canInspect={canInspect}
                busy={busy}
                onAction={runAction}
                onReportIssue={reportIssue}
              />
            ))}
          </div>
        )}
      </section>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
