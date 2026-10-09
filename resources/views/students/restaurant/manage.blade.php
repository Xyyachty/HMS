@extends('students.builder.ops-shell')

@section('page-title', 'Restaurant Management')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }
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
  .tb-badge {
    padding: 0.22rem 0.6rem; border-radius: 4px;
    font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; display: inline-block;
  }
  .tb-available { background: rgba(34,197,94,0.18); color: var(--success, #4ade80); border-color: rgba(34,197,94,0.35); }
  .tb-occupied  { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
  /* Held, nobody at it yet — amber reads as "pending" against Occupied's blue. */
  .tb-reserved  { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35); }
  .tb-preparing { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .tb-ready     { background: rgba(56,189,248,0.18); color: #38bdf8; border-color: rgba(56,189,248,0.35); }
  .tb-delivering { background: rgba(34,197,94,0.18); color: var(--success, #4ade80); border-color: rgba(34,197,94,0.35); }
  .tb-completed { background: rgba(20,148,80,0.18); color: #34d399; border-color: rgba(20,148,80,0.35); }
  .tb-cancelled { background: rgba(148,163,184,0.15); color: #94a3b8; border-color: rgba(148,163,184,0.3); }
  /* Catering-only statuses. Pending is amber because it is waiting on the kitchen to
     accept it; Serving is green because the food is out at the event. */
  .tb-pending   { background: rgba(251,191,36,0.16); color: #fbbf24; border-color: rgba(251,191,36,0.35); }
  .tb-confirmed { background: rgba(129,140,248,0.18); color: #a5b4fc; border-color: rgba(129,140,248,0.35); }
  .tb-serving   { background: rgba(34,197,94,0.18); color: var(--success, #4ade80); border-color: rgba(34,197,94,0.35); }
  .order-card {
    border: 1px solid var(--border); border-radius: 12px;
    background: rgba(255,255,255,0.02); padding: 1rem 1.1rem;
  }
  .order-card-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 0.75rem; margin-bottom: 0.75rem;
  }
  .order-card-id {
    font-family: var(--font-display, 'Playfair Display', serif); font-size: 1.05rem;
    font-weight: 700; color: var(--fg);
  }
  .order-field { display: flex; gap: 0.5rem; font-size: 0.82rem; margin-bottom: 0.3rem; }
  .order-field dt { color: var(--fg-muted); min-width: 58px; flex-shrink: 0; }
  .order-field dd { color: var(--fg); margin: 0; }
  .tb-tab {
    padding: 0.35rem 0.8rem; border-radius: 999px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer;
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.68rem; font-weight: 600;
    letter-spacing: 0.06em; transition: all 0.15s;
  }
  .tb-tab:hover { color: var(--fg); }
  .tb-tab.is-active { border-color: var(--accent); background: var(--accent); color: var(--bg); }
  .tb-tab:disabled { opacity: 0.4; cursor: not-allowed; }

  .tb-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.65);
    display: flex; align-items: flex-start; justify-content: center;
    padding: 2rem 1.5rem; z-index: 300; overflow-y: auto;
  }
  .tb-modal {
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    width: 100%; max-width: 520px; margin: auto;
  }
  .tb-modal-head {
    padding: 1.3rem 1.5rem 1rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
  }
  .tb-modal-body { padding: 1.2rem 1.5rem 1.5rem; }
  .tb-bill-title {
    font-size: 0.62rem; font-weight: 700; letter-spacing: 0.14em;
    text-transform: uppercase; color: var(--accent);
    margin: 1.15rem 0 0.5rem; padding-bottom: 0.3rem;
    border-bottom: 1px solid var(--border);
  }
  .tb-bill-line {
    display: flex; justify-content: space-between; gap: 1rem;
    font-size: 0.83rem; color: var(--fg-muted); padding: 0.22rem 0;
  }
  .tb-bill-line .tb-bill-amt { color: var(--fg); font-variant-numeric: tabular-nums; white-space: nowrap; }
  .tb-bill-line.is-total {
    border-top: 2px solid var(--accent); margin-top: 0.6rem; padding-top: 0.6rem;
    font-size: 1rem; color: var(--fg); font-weight: 700;
  }
  .tb-bill-line.is-total .tb-bill-amt {
    color: var(--accent-light); font-family: var(--font-display, 'Playfair Display', serif); font-size: 1.15rem;
  }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Additive only — nothing above this block is touched, so a Template 1
     team (or one that hasn't chosen a template yet) renders unchanged. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d;
  }
  :root[data-ops-theme="2"] .tb-available,
  :root[data-ops-theme="2"] .tb-delivering { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
  :root[data-ops-theme="2"] .tb-occupied { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
  :root[data-ops-theme="2"] .tb-reserved { background: #fef3c7; color: #b45309; border-color: #fde68a; }
  :root[data-ops-theme="2"] .tb-preparing { background: #f3e8ff; color: #7e22ce; border-color: #e9d5ff; }
  :root[data-ops-theme="2"] .tb-ready { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
  :root[data-ops-theme="2"] .tb-completed { background: #d1fae5; color: #047857; border-color: #a7f3d0; }
  :root[data-ops-theme="2"] .tb-pending { background: #fef3c7; color: #b45309; border-color: #fde68a; }
  :root[data-ops-theme="2"] .tb-confirmed { background: #e0e7ff; color: #4338ca; border-color: #c7d2fe; }
  :root[data-ops-theme="2"] .tb-serving { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
  :root[data-ops-theme="2"] .tb-cancelled { background: #f1f5f9; color: #475569; border-color: #e2e8f0; }
  :root[data-ops-theme="2"] .booking-input { background: rgba(27,67,50,0.03); }
  :root[data-ops-theme="2"] .order-card { background: rgba(27,67,50,0.03); }
  /* ── Manage Menu (mn-) ──────────────────────────────────────────────────
     Reads the shell's tokens, the same way the Housekeeping pages do, so it
     follows Template 1, Template 2 and a team's own site colours. Shape rule:
     pills for status and tabs, 10px for buttons and fields, 14px for panels
     and cards. Tables, Catering and Orders keep their own rm-/tb- styles. */
  :root[data-ops-theme="2"] { --warn: #b45309; }
  .mn {
    --mn-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --mn-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --mn-line: var(--border);
    --mn-ok: var(--success, #4ade80);
    --mn-warn: var(--warn, #f59e0b);
    --mn-bad: var(--danger, #fb7185);
    color: var(--fg);
    display: grid; gap: 1.25rem;
  }
  .mn-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
  .mn-eyebrow { color: var(--accent); font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; margin: 0 0 0.5rem; }
  .mn-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .mn-lead { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 66ch; line-height: 1.5; }
  .mn-head-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

  .mn-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem;
    font: 600 0.88rem/1.15 var(--font-body, 'Outfit', sans-serif);
    padding: 0.8rem 1.15rem; border-radius: 10px; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); background: transparent; color: var(--accent);
    transition: background 0.15s, transform 0.1s, filter 0.15s;
  }
  .mn-btn:hover { background: var(--mn-tint); }
  .mn-btn:active { transform: translateY(1px); }
  .mn-btn.is-solid { background: var(--accent); color: var(--bg); }
  .mn-btn.is-solid:hover { filter: brightness(1.08); }
  .mn-btn.is-quiet { border-color: var(--mn-line); color: var(--fg-muted); }
  .mn-btn.is-quiet:hover { color: var(--fg); background: var(--mn-soft); }
  .mn-btn.is-small { padding: 0.62rem 0.95rem; font-size: 0.84rem; }
  .mn-btn.is-wide { width: 100%; }
  .mn-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; filter: none; }
  .mn-btn:focus-visible, .mn-tab:focus-visible, .mn-chip:focus-visible, .mn-link:focus-visible, .mn-close:focus-visible, .mn-photos:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  .mn-how { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
  .mn-how li { display: flex; gap: 0.7rem; align-items: flex-start; padding: 0.85rem 0.95rem; border-radius: 14px; background: var(--mn-soft); border: 1px solid var(--mn-line); }
  .mn-how-num { flex: none; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.78rem; background: var(--mn-tint); color: var(--accent); }
  .mn-how div b { display: block; font-size: 0.86rem; color: var(--fg); margin-bottom: 0.15rem; }
  .mn-how div span { display: block; font-size: 0.78rem; color: var(--fg-muted); line-height: 1.4; }

  .mn-panel { background: var(--card); border: 1px solid var(--mn-line); border-radius: 14px; padding: 1.2rem 1.3rem 1.4rem; }
  .mn-panel-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .mn-panel-head h2 { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--fg); }
  .mn-panel-head p { margin: 0.25rem 0 0; font-size: 0.84rem; color: var(--fg-muted); }
  .mn-live { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--fg-muted); }
  .mn-live::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--mn-ok); }

  .mn .tone-ok, .mn-modal .tone-ok       { background: color-mix(in srgb, var(--mn-ok) 16%, transparent);   color: var(--mn-ok); }
  .mn .tone-warn, .mn-modal .tone-warn   { background: color-mix(in srgb, var(--mn-warn) 16%, transparent); color: var(--mn-warn); }
  .mn .tone-brand, .mn-modal .tone-brand { background: var(--mn-tint); color: var(--accent); }

  .mn-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; margin-bottom: 1.1rem; }
  .mn-stat { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 0.95rem; border-radius: 12px; border: 1px solid var(--mn-line); background: var(--mn-soft); }
  .mn-stat-icon { flex: none; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; }
  .mn-stat div b { display: block; font-size: 1.35rem; line-height: 1.1; font-variant-numeric: tabular-nums; color: var(--fg); }
  .mn-stat div span { display: block; font-size: 0.78rem; color: var(--fg-muted); }

  .mn-types { display: grid; gap: 0.5rem; margin-bottom: 0.9rem; }
  .mn-tabs { display: flex; flex-wrap: wrap; gap: 0.4rem; }
  .mn-tab { display: inline-flex; align-items: center; gap: 0.5rem; font: 600 0.84rem/1 var(--font-body, 'Outfit', sans-serif); padding: 0.6rem 0.9rem; border-radius: 999px; cursor: pointer; border: 1px solid var(--mn-line); background: var(--mn-soft); color: var(--fg-muted); transition: background 0.15s, color 0.15s, border-color 0.15s; }
  .mn-tab:hover { color: var(--fg); border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .mn-tab.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .mn-tab.is-new { border-style: dashed; background: transparent; color: var(--accent); }
  .mn-count { min-width: 1.45rem; padding: 0.2rem 0.4rem; border-radius: 999px; text-align: center; font-size: 0.74rem; font-variant-numeric: tabular-nums; background: color-mix(in srgb, var(--fg) 8%, transparent); }
  .mn-tab.is-on .mn-count { background: color-mix(in srgb, var(--bg) 22%, transparent); }

  .mn-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .mn-showing { margin: 0; font-size: 0.86rem; color: var(--fg-muted); display: flex; align-items: center; gap: 0.4rem 0.9rem; flex-wrap: wrap; }
  .mn-showing b { color: var(--fg); }
  .mn-link { display: inline-flex; align-items: center; gap: 0.4rem; background: none; border: 0; padding: 0.25rem 0; cursor: pointer; color: var(--accent); font: 600 0.84rem/1 var(--font-body, 'Outfit', sans-serif); }
  .mn-link:hover { text-decoration: underline; }
  .mn-search { position: relative; flex: 1 1 240px; max-width: 340px; }
  .mn-search i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.8rem; pointer-events: none; }
  .mn-search .mn-input { padding-left: 2.3rem; }

  .mn-input {
    box-sizing: border-box; width: 100%;
    background: var(--mn-soft); border: 1px solid var(--mn-line);
    border-radius: 10px; padding: 0.75rem 0.9rem; color: var(--fg);
    font: 400 0.9rem/1.4 var(--font-body, 'Outfit', sans-serif);
    outline: none; transition: border-color 0.15s;
  }
  .mn-input:focus { border-color: var(--accent); }
  .mn-input::placeholder { color: var(--fg-muted); opacity: 0.7; }
  .mn-input.has-error { border-color: var(--mn-bad); }
  textarea.mn-input { resize: vertical; min-height: 4.6rem; }

  /* Room cards: one height per row, button at the bottom. */
  .mn-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(270px, 100%), 1fr)); gap: 1rem; align-items: stretch; }
  .mn-card { min-width: 0; border: 1px solid var(--mn-line); border-radius: 14px; background: var(--mn-soft); overflow: hidden; display: flex; flex-direction: column; }
  .mn-card-img { position: relative; aspect-ratio: 16 / 9; background: var(--mn-soft); overflow: hidden; }
  .mn-card-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; }
  .mn-card-img .mn-pill { position: absolute; top: 10px; left: 10px; background: var(--card); box-shadow: 0 2px 10px rgba(0,0,0,0.25); }
  .mn-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.76rem; font-weight: 600; white-space: nowrap; }
  .mn-card-body { padding: 0.9rem 1rem 1rem; display: flex; flex-direction: column; gap: 0.7rem; flex: 1; }
  .mn-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
  .mn-name { margin: 0; font-size: 1.08rem; font-weight: 700; color: var(--fg); line-height: 1.25; overflow-wrap: anywhere; }
  .mn-type { display: block; font-size: 0.78rem; font-weight: 600; color: var(--accent); margin-top: 0.2rem; }
  .mn-price { text-align: right; flex: none; }
  .mn-price b { display: block; font-size: 1.02rem; color: var(--fg); font-variant-numeric: tabular-nums; }
  .mn-price small { display: block; font-size: 0.7rem; color: var(--fg-muted); }
  .mn-desc { margin: 0; font-size: 0.83rem; line-height: 1.5; color: var(--fg-muted); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 3em; }
  .mn-desc.is-empty { font-style: italic; opacity: 0.7; }
  .mn-next { margin: auto 0 0; display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--fg); padding: 0.55rem 0.7rem; border-radius: 10px; background: var(--card); border: 1px solid var(--mn-line); }
  .mn-next i { color: var(--fg-muted); }
  .mn-more { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem; font-size: 0.82rem; color: var(--fg-muted); }

  .mn-empty { border: 1.5px dashed var(--mn-line); border-radius: 14px; padding: 2.2rem 1.5rem; text-align: center; }
  .mn-empty-icon { width: 56px; height: 56px; margin: 0 auto 0.9rem; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; background: var(--mn-tint); color: var(--accent); }
  .mn-empty h3 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .mn-empty p { margin: 0.4rem auto 0; max-width: 52ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }
  .mn-empty .mn-btn { margin-top: 1.1rem; }

  /* Dialogs. Below .room-image-overlay (260), which opens on top of them. */
  .mn-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; padding: 1.25rem; z-index: 200; }
  .mn-modal {
    --mn-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --mn-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --mn-line: var(--border);
    --mn-ok: var(--success, #4ade80);
    --mn-bad: var(--danger, #fb7185);
    box-sizing: border-box; background: var(--card); color: var(--fg); border: 1px solid var(--mn-line); border-radius: 14px; width: 100%; max-width: 580px; max-height: 92vh; overflow-y: auto;
  }
  .mn-modal.is-small { max-width: 460px; }
  .mn-modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.35rem 0; }
  .mn-modal-head h2 { margin: 0; font-size: 1.45rem; line-height: 1.2; color: var(--fg); }
  .mn-modal-head p { margin: 0.35rem 0 0; font-size: 0.84rem; color: var(--fg-muted); line-height: 1.45; }
  .mn-close { flex: none; width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--mn-line); background: transparent; color: var(--fg-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; }
  .mn-close:hover { color: var(--fg); background: var(--mn-soft); }
  .mn-form { padding: 1.1rem 1.35rem 1.35rem; display: grid; gap: 1.05rem; }
  .mn-field { display: grid; gap: 0.4rem; align-content: start; }
  .mn-label { font-size: 0.86rem; font-weight: 600; color: var(--fg); }
  .mn-label em { font-style: normal; font-weight: 400; color: var(--fg-muted); }
  .mn-help { margin: 0; font-size: 0.76rem; color: var(--fg-muted); line-height: 1.45; }
  .mn-error { margin: 0; font-size: 0.78rem; color: var(--mn-bad); }
  .mn-money { position: relative; }
  .mn-money span { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.9rem; pointer-events: none; }
  .mn-money .mn-input { padding-left: 1.8rem; }
  .mn-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; }
  .mn-chip { padding: 0.55rem 0.9rem; border-radius: 999px; border: 1px solid var(--mn-line); background: var(--mn-soft); color: var(--fg); cursor: pointer; font: 500 0.84rem/1.2 var(--font-body, 'Outfit', sans-serif); transition: border-color 0.15s, background 0.15s; }
  .mn-chip:hover { border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .mn-chip.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .mn-note { margin: 0; display: flex; gap: 0.55rem; align-items: flex-start; padding: 0.75rem 0.85rem; border-radius: 10px; border: 1px solid var(--mn-line); background: var(--mn-soft); font-size: 0.84rem; line-height: 1.45; color: var(--fg-muted); }
  .mn-note i { margin-top: 0.2rem; }
  .mn-note.is-ok { color: var(--fg); border-color: color-mix(in srgb, var(--accent) 45%, transparent); background: var(--mn-tint); }
  .mn-note.is-ok i { color: var(--accent); }
  .mn-photos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; padding: 0; border: 0; background: none; cursor: pointer; }
  .mn-photo { position: relative; height: 84px; border-radius: 10px; border: 1px solid var(--mn-line); background-color: var(--mn-soft); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; color: var(--accent); }
  .mn-photo.is-empty { border-style: dashed; }
  .mn-photos:hover .mn-photo { border-color: var(--accent); }
  .mn-photo small { position: absolute; top: 5px; left: 5px; padding: 0.12rem 0.45rem; border-radius: 999px; background: rgba(0,0,0,0.65); color: #fff; font-size: 0.62rem; }
  .mn-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
  .mn-actions .mn-btn { flex: 1 1 auto; }

  @media (max-width: 860px) {
    .mn-how, .mn-stats { grid-template-columns: 1fr; }
  }
  @media (max-width: 560px) {
    .mn-head-actions, .mn-head-actions .mn-btn, .mn-search { width: 100%; max-width: none; }
  }
  .mn .tone-bad, .mn-modal .tone-bad { background: color-mix(in srgb, var(--mn-bad) 14%, transparent); color: var(--mn-bad); }
  .mn-stat { font: inherit; text-align: left; cursor: pointer; transition: border-color 0.15s; }
  .mn-stat:hover { border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .mn-stat.is-on { border-color: var(--accent); }
  .mn-stat:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
  .mn-tab i { font-size: 0.78rem; }
  .mn-chip i { margin-right: 0.25rem; }
  .mn-btn.is-danger { border-color: color-mix(in srgb, var(--mn-bad) 45%, transparent); color: var(--mn-bad); }
  .mn-btn.is-danger:hover { background: color-mix(in srgb, var(--mn-bad) 10%, transparent); }
  .mn-card.is-out .mn-card-img img { filter: grayscale(0.7); opacity: 0.75; }
  .mn-card-actions { display: flex; gap: 0.5rem; margin-top: auto; }
  .mn-card-actions .mn-btn { flex: 1 1 0; }
  .mn-type i { margin-right: 0.2rem; }
  .mn-price { font-size: 1.05rem; color: var(--fg); font-variant-numeric: tabular-nums; white-space: nowrap; }
  .mn-note { display: flex; align-items: center; flex-wrap: wrap; gap: 0.5rem 0.75rem; }
  .mn-panel > .mn-note { margin-bottom: 0.9rem; }
  .mn-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }
  .mn-photo-pick { display: block; width: 100%; padding: 0; border: 1.5px dashed var(--mn-line); border-radius: 10px; background: var(--mn-soft); cursor: pointer; overflow: hidden; color: var(--fg-muted); font: inherit; }
  .mn-photo-pick:hover { border-color: var(--accent); }
  .mn-photo-pick img { width: 100%; height: 160px; object-fit: cover; display: block; }
  .mn-photo-empty { height: 110px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.45rem; font-size: 0.82rem; }
  .mn-photo-empty i { font-size: 1.4rem; color: var(--accent); }
  .mn-modal { --mn-warn: var(--warn, #f59e0b); }
  @media (max-width: 560px) { .mn-row2 { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_RESTAURANT_URL = @json(route('students.dashboard', ['section' => 'tasks']));
  window.HMS_RESTAURANT_INITIAL_NAV = @json(request()->query('nav', 'manage-menu'));
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef, useId } = React;

const MENU_CATEGORIES = ['Main Dishes', 'Appetizers', 'Soups', 'Desserts', 'Beverages'];
const IMAGE_MAX_DIMENSION = 1280;
const IMAGE_MAX_BYTES = 600 * 1024;

function normalizeMenuCategory(value) {
  const raw = String(value || 'Main Dishes').trim().toLowerCase();
  const match = MENU_CATEGORIES.find(c => c.toLowerCase() === raw);
  if (match) return match;
  if (raw === 'dining' || raw === 'main' || raw === 'mains') return 'Main Dishes';
  if (raw === 'bar' || raw === 'drinks' || raw === 'beverage') return 'Beverages';
  if (raw === 'dessert' || raw === 'sweets') return 'Desserts';
  if (raw === 'appetizer' || raw === 'starter' || raw === 'starters') return 'Appetizers';
  if (raw === 'soup') return 'Soups';
  return 'Main Dishes';
}
function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0';
  return '₱' + n.toLocaleString();
}
/* A dish with no photo must look like it has none. This used to hand back a random
   picsum photo, so clearing a dish's picture swapped one food photo for another and
   the clear read as if it had never saved. Grey on a mostly transparent tile, so the
   card's own background shows through and it suits either template palette. */
const MENU_NO_PHOTO = 'data:image/svg+xml,' + encodeURIComponent(
  '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300">'
  + '<rect width="400" height="300" fill="#8a8a8a" fill-opacity="0.1"/>'
  + '<circle cx="200" cy="132" r="46" fill="none" stroke="#8a8a8a" stroke-opacity="0.5" stroke-width="6"/>'
  + '<circle cx="200" cy="132" r="26" fill="none" stroke="#8a8a8a" stroke-opacity="0.35" stroke-width="4"/>'
  + '<text x="200" y="224" fill="#8a8a8a" fill-opacity="0.75" font-family="sans-serif" font-size="26" text-anchor="middle">No photo</text>'
  + '</svg>'
);

function menuFoodImg(item) {
  return (item && item.img) ? item.img : MENU_NO_PHOTO;
}
function toolBtnStyle(kind) {
  const base = { width: 28, height: 28, borderRadius: 8, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', border: '1px solid var(--border)' };
  if (kind === 'danger') return Object.assign({}, base, { background: 'rgba(127,29,29,0.85)', color: '#fecaca', borderColor: '#7f1d1d' });
  if (kind === 'image') return Object.assign({}, base, { background: 'rgba(12,11,9,0.85)', color: '#38bdf8' });
  return Object.assign({}, base, { background: 'rgba(12,11,9,0.85)', color: 'var(--accent)' });
}
function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}
function hmsConfirm(message) {
  try { return !!window.confirm(message); } catch (e) { return true; }
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

function createEmptyMenuForm(category) {
  return { name: '', category: category || 'Main Dishes', price: '', stock: '', sub: '', img: '' };
}

/* ── Manage Menu: plain-language cards and one dialog ───────────────────────
   Reads the shell's tokens through the mn- classes, the same way Manage Rooms
   does. Requests, fields and categories are unchanged. */

// SweetAlert draws outside the page's CSS, so it gets the live token values.
function themeColor(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

const MENU_ICONS = {
  'Main Dishes': 'fa-drumstick-bite',
  'Appetizers':  'fa-cheese',
  'Soups':       'fa-bowl-food',
  'Desserts':    'fa-ice-cream',
  'Beverages':   'fa-mug-hot',
};

// "Running low" at five or fewer: about one busy table's worth.
const LOW_STOCK = 5;

function stockState(item) {
  const n = Number(item.stock) || 0;
  if (n <= 0) return { key: 'out', label: 'Sold out', icon: 'fa-circle-xmark', tone: 'tone-bad' };
  if (n <= LOW_STOCK) return { key: 'low', label: `Only ${n} left`, icon: 'fa-triangle-exclamation', tone: 'tone-warn' };
  return { key: 'ok', label: `${n} left`, icon: 'fa-circle-check', tone: 'tone-ok' };
}

function MenuItemModal({ item, defaultCategory, onClose, onAddMenu, onEditMenu, onToast }) {
  const isEdit = !!item;
  const [form, setForm] = useState(() => (isEdit ? {
    name: item.name || '',
    category: normalizeMenuCategory(item.category),
    price: String(item.price || ''),
    stock: item.stock != null ? String(item.stock) : '',
    sub: item.sub || '',
    img: item.img || '',
  } : createEmptyMenuForm(defaultCategory)));
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);
  const uid = useId();
  const firstField = useRef(null);

  // Once, on open — not on every render, or typing in another field would jump back.
  useEffect(() => { if (firstField.current) firstField.current.focus(); }, []);
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    const next = {};
    if (!String(form.name).trim()) next.name = 'Type the name of the dish.';
    const price = parseInt(String(form.price).replace(/[^0-9]/g, ''), 10);
    if (!Number.isFinite(price) || price <= 0) next.price = 'Type a price above 0.';
    const stockRaw = String(form.stock).trim();
    const stock = stockRaw === '' ? 0 : parseInt(stockRaw.replace(/[^0-9]/g, ''), 10);
    if (!Number.isFinite(stock) || stock < 0) next.stock = 'Type how many, or 0.';
    setErrors(next);
    if (Object.keys(next).length) return;

    const payload = {
      name: String(form.name).trim(),
      category: normalizeMenuCategory(form.category),
      price,
      stock,
      description: String(form.sub || '').trim(),
      image: form.img || '',
    };

    setSaving(true);
    const action = isEdit ? onEditMenu(item.id, payload) : onAddMenu(payload);
    Promise.resolve(action)
      .then(() => {
        if (onToast) onToast(isEdit ? `${payload.name} saved.` : `${payload.name} added to ${payload.category}.`);
        onClose();
      })
      .catch((err) => {
        setErrors({ form: (err && err.message) || 'Could not save this dish. Please try again.' });
      })
      .finally(() => setSaving(false));
  };

  const err = (key) => (errors[key] ? <p className="mn-error">{errors[key]}</p> : null);

  return (
    <div className="mn-overlay" onClick={onClose}>
      <div className="mn-modal" role="dialog" aria-modal="true" aria-labelledby={uid + '-t'} onClick={e => e.stopPropagation()}>
        <div className="mn-modal-head">
          <div>
            <h2 id={uid + '-t'} className="font-display">{isEdit ? `Edit ${item.name}` : 'Add a dish'}</h2>
            <p>Guests see it on the Restaurant page of your hotel website as soon as you save.</p>
          </div>
          <button type="button" className="mn-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form onSubmit={handleSubmit} className="mn-form" noValidate>
          <div className="mn-field">
            <label className="mn-label" htmlFor={uid + '-n'}>Dish name</label>
            <input
              id={uid + '-n'} ref={firstField} type="text"
              className={`mn-input ${errors.name ? 'has-error' : ''}`} placeholder="Example: Grilled Pork Belly"
              value={form.name} onChange={e => update('name', e.target.value)}
            />
            {err('name')}
          </div>

          <div className="mn-field">
            <span className="mn-label">Which part of the menu?</span>
            <div className="mn-chips" role="radiogroup" aria-label="Menu section">
              {MENU_CATEGORIES.map(c => (
                <button key={c} type="button" role="radio" aria-checked={form.category === c} className={`mn-chip ${form.category === c ? 'is-on' : ''}`} onClick={() => update('category', c)}>
                  <i className={`fa-solid ${MENU_ICONS[c] || 'fa-utensils'}`}></i> {c}
                </button>
              ))}
            </div>
          </div>

          <div className="mn-row2">
            <div className="mn-field">
              <label className="mn-label" htmlFor={uid + '-p'}>Price</label>
              <div className="mn-money"><span>₱</span>
                <input
                  id={uid + '-p'} type="number" min="1" step="1" inputMode="numeric"
                  className={`mn-input ${errors.price ? 'has-error' : ''}`} placeholder="350"
                  value={form.price} onChange={e => update('price', e.target.value)}
                />
              </div>
              {err('price') || <p className="mn-help">For one serving.</p>}
            </div>
            <div className="mn-field">
              <label className="mn-label" htmlFor={uid + '-s'}>How many servings are ready?</label>
              <input
                id={uid + '-s'} type="number" min="0" step="1" inputMode="numeric"
                className={`mn-input ${errors.stock ? 'has-error' : ''}`} placeholder="25"
                value={form.stock} onChange={e => update('stock', e.target.value)}
              />
              {err('stock') || <p className="mn-help">Goes down by itself with each order. At 0 guests cannot order it.</p>}
            </div>
          </div>

          <div className="mn-field">
            <label className="mn-label" htmlFor={uid + '-d'}>Short description <em>(optional)</em></label>
            <textarea
              id={uid + '-d'} className="mn-input" rows={2} placeholder="Example: Crispy skin, garlic rice and atchara."
              value={form.sub} onChange={e => update('sub', e.target.value)}
            />
          </div>

          <div className="mn-field">
            <span className="mn-label">Photo <em>(optional)</em></span>
            <button type="button" className="mn-photo-pick" onClick={() => pickImageFile(url => { if (url) update('img', url); })}>
              {form.img ? (
                <img src={form.img} alt="Photo of the dish" />
              ) : (
                <span className="mn-photo-empty"><i className="fa-solid fa-camera"></i>Click to choose a photo</span>
              )}
            </button>
            {form.img ? (
              <button type="button" className="mn-link" onClick={() => update('img', '')}>
                <i className="fa-solid fa-trash-can"></i> Remove photo
              </button>
            ) : null}
          </div>

          {errors.form ? <p className="mn-error">{errors.form}</p> : null}

          <div className="mn-actions">
            <button type="button" className="mn-btn is-quiet" onClick={onClose}>Cancel</button>
            <button type="submit" className="mn-btn is-solid" disabled={saving}>
              <i className={`fa-solid ${isEdit ? 'fa-floppy-disk' : 'fa-plus'}`}></i>
              {saving ? 'Saving…' : (isEdit ? 'Save changes' : 'Add this dish')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function MenuCard({ item, onEdit, onRemove }) {
  const stock = stockState(item);
  const category = normalizeMenuCategory(item.category);
  return (
    <article className={`mn-card ${stock.key === 'out' ? 'is-out' : ''}`}>
      <div className="mn-card-img">
        <img src={menuFoodImg(item)} alt={item.name} loading="lazy" />
        <span className={`mn-pill ${stock.tone}`}><i className={`fa-solid ${stock.icon}`}></i>{stock.label}</span>
      </div>
      <div className="mn-card-body">
        <div className="mn-card-top">
          <div style={{ minWidth: 0 }}>
            <h3 className="mn-name">{item.name}</h3>
            <span className="mn-type"><i className={`fa-solid ${MENU_ICONS[category] || 'fa-utensils'}`}></i> {category}</span>
          </div>
          <b className="mn-price">{typeof item.price === 'number' ? formatPeso(item.price) : (item.price || '—')}</b>
        </div>
        <p className={`mn-desc ${item.sub ? '' : 'is-empty'}`}>{item.sub || 'No description yet.'}</p>
        <div className="mn-card-actions">
          <button type="button" className="mn-btn is-small" onClick={() => onEdit(item)}>
            <i className="fa-solid fa-pen"></i> Edit
          </button>
          <button type="button" className="mn-btn is-small is-danger" onClick={() => onRemove(item)}>
            <i className="fa-solid fa-trash-can"></i> Remove
          </button>
        </div>
      </div>
    </article>
  );
}

const MN_PAGE = 12;

function ManageMenuPanel({ menus, onAddMenu, onEditMenu, onRemoveMenu, onToast }) {
  const [filter, setFilter] = useState('All');
  const [stockFilter, setStockFilter] = useState('all');
  const [search, setSearch] = useState('');
  const [shown, setShown] = useState(MN_PAGE);
  const [editing, setEditing] = useState(null); // a menu item, or 'new'

  const list = menus || [];
  const q = search.trim().toLowerCase();
  const visible = list
    .filter(m => filter === 'All' || normalizeMenuCategory(m.category) === filter)
    .filter(m => stockFilter === 'all' || (stockFilter === 'attention' ? stockState(m).key !== 'ok' : true))
    .filter(m => !q || [m.name, m.sub].some(v => String(v || '').toLowerCase().includes(q)));
  const page = visible.slice(0, shown);

  const counts = list.reduce((t, m) => { t[stockState(m).key] += 1; return t; }, { ok: 0, low: 0, out: 0 });
  const needs = counts.low + counts.out;

  const pick = (fn) => (v) => { fn(v); setShown(MN_PAGE); };

  /* Asked first: the dish leaves the hotel website for every guest at once. */
  const handleRemove = (item) => {
    const go = () => Promise.resolve(onRemoveMenu(item.id))
      .then(() => { if (onToast) onToast(`${item.name} removed from the menu.`); })
      .catch(e => { if (onToast) onToast((e && e.message) || 'Could not remove that dish.'); });
    if (!window.Swal) { if (hmsConfirm(`Remove "${item.name}" from the menu?`)) go(); return; }
    window.Swal.fire({
      title: `Remove ${item.name}?`,
      text: 'Guests will no longer see it or be able to order it. You cannot undo this.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, remove it',
      cancelButtonText: 'Keep it',
      background: themeColor('--card', '#181714'),
      color: themeColor('--fg', '#f5f0e8'),
      confirmButtonColor: '#be123c',
      cancelButtonColor: '#71717a',
    }).then(r => { if (r.isConfirmed) go(); });
  };

  return (
    <div className="mn">
      <header className="mn-head">
        <div>
          <p className="mn-eyebrow">Restaurant</p>
          <h1 className="font-display">Manage Menu</h1>
          <p className="mn-lead">
            The dishes and drinks guests can order. Everything here shows on the Restaurant
            page of your hotel website. Keep the number of servings up to date so guests
            cannot order what the kitchen has run out of.
          </p>
        </div>
        <div className="mn-head-actions">
          <a href={window.HMS_RESTAURANT_URL} className="mn-btn">
            <i className="fa-solid fa-arrow-left"></i> Back to Tasks
          </a>
          <button type="button" className="mn-btn is-solid" onClick={() => setEditing('new')}>
            <i className="fa-solid fa-plus"></i> Add a dish
          </button>
        </div>
      </header>

      <ol className="mn-how" aria-label="Good to know">
        <li><span className="mn-how-num"><i className="fa-solid fa-globe"></i></span><div><b>Guests see changes right away</b><span>A new dish or price shows on the website as soon as you save.</span></div></li>
        <li><span className="mn-how-num"><i className="fa-solid fa-boxes-stacked"></i></span><div><b>Servings count down by themselves</b><span>Each order takes one away. Cancelled orders give it back.</span></div></li>
        <li><span className="mn-how-num"><i className="fa-solid fa-ban"></i></span><div><b>At 0, it is sold out</b><span>Guests cannot order it until you add more servings.</span></div></li>
      </ol>

      <section className="mn-panel" aria-labelledby="mn-list">
        <div className="mn-panel-head">
          <div>
            <h2 id="mn-list">Your menu</h2>
            <p>{list.length ? `${list.length} ${list.length === 1 ? 'dish' : 'dishes and drinks'} on the menu.` : 'Nothing on the menu yet.'}</p>
          </div>
          <span className="mn-live">Updates on its own</span>
        </div>

        {list.length ? (
          <div className="mn-stats">
            <button type="button" className="mn-stat" onClick={() => pick(setStockFilter)('all')}>
              <span className="mn-stat-icon tone-ok"><i className="fa-solid fa-utensils"></i></span>
              <div><b>{counts.ok}</b><span>Ready to order</span></div>
            </button>
            <button type="button" className={`mn-stat ${stockFilter === 'attention' ? 'is-on' : ''}`} onClick={() => pick(setStockFilter)(stockFilter === 'attention' ? 'all' : 'attention')}>
              <span className={`mn-stat-icon ${needs ? 'tone-warn' : 'tone-ok'}`}><i className="fa-solid fa-triangle-exclamation"></i></span>
              <div><b>{counts.low}</b><span>Running low (5 or fewer)</span></div>
            </button>
            <button type="button" className={`mn-stat ${stockFilter === 'attention' ? 'is-on' : ''}`} onClick={() => pick(setStockFilter)(stockFilter === 'attention' ? 'all' : 'attention')}>
              <span className={`mn-stat-icon ${counts.out ? 'tone-bad' : 'tone-ok'}`}><i className="fa-solid fa-circle-xmark"></i></span>
              <div><b>{counts.out}</b><span>Sold out</span></div>
            </button>
          </div>
        ) : null}

        {stockFilter === 'attention' ? (
          <p className="mn-note">
            <i className="fa-solid fa-filter"></i>
            <span>Showing only dishes that are running low or sold out.</span>
            <button type="button" className="mn-link" onClick={() => pick(setStockFilter)('all')}>Show all</button>
          </p>
        ) : null}

        <div className="mn-toolbar">
          <div className="mn-tabs" role="group" aria-label="Show part of the menu">
            {['All', ...MENU_CATEGORIES].map(c => (
              <button key={c} type="button" aria-pressed={filter === c} className={`mn-tab ${filter === c ? 'is-on' : ''}`} onClick={() => pick(setFilter)(c)}>
                {c !== 'All' ? <i className={`fa-solid ${MENU_ICONS[c] || 'fa-utensils'}`}></i> : null}
                {c === 'All' ? 'Everything' : c}
                <span className="mn-count">{c === 'All' ? list.length : list.filter(m => normalizeMenuCategory(m.category) === c).length}</span>
              </button>
            ))}
          </div>
          {list.length ? (
            <div className="mn-search">
              <i className="fa-solid fa-magnifying-glass"></i>
              <input type="text" className="mn-input" placeholder="Search a dish" aria-label="Search a dish" value={search} onChange={e => pick(setSearch)(e.target.value)} />
            </div>
          ) : null}
        </div>

        {visible.length === 0 ? (
          <div className="mn-empty">
            <div className="mn-empty-icon"><i className={`fa-solid ${q ? 'fa-magnifying-glass' : (MENU_ICONS[filter] || 'fa-utensils')}`}></i></div>
            <h3>{q ? 'No dish matches your search' : list.length === 0 ? 'No dishes yet' : stockFilter === 'attention' ? 'Nothing is running low' : `Nothing in ${filter} yet`}</h3>
            <p>{q ? 'Check the spelling, or clear the search box.' : stockFilter === 'attention' ? 'Every dish has more than 5 servings ready.' : 'Use "Add a dish" to put something on the menu. Guests can order it right away.'}</p>
            {!q && stockFilter !== 'attention' ? (
              <button type="button" className="mn-btn is-solid" onClick={() => setEditing('new')}>
                <i className="fa-solid fa-plus"></i> Add a dish
              </button>
            ) : null}
          </div>
        ) : (
          <>
            <div className="mn-grid">
              {page.map(item => <MenuCard key={item.id} item={item} onEdit={setEditing} onRemove={handleRemove} />)}
            </div>
            <div className="mn-more">
              <span>Showing {page.length} of {visible.length}</span>
              {page.length < visible.length ? (
                <button type="button" className="mn-btn is-small" onClick={() => setShown(s => s + MN_PAGE)}>
                  <i className="fa-solid fa-chevron-down"></i> Show {Math.min(MN_PAGE, visible.length - page.length)} more
                </button>
              ) : null}
            </div>
          </>
        )}
      </section>

      {editing ? (
        <MenuItemModal
          key={editing === 'new' ? 'new' : editing.id}
          item={editing === 'new' ? null : editing}
          defaultCategory={filter === 'All' ? 'Main Dishes' : filter}
          onClose={() => setEditing(null)}
          onAddMenu={onAddMenu}
          onEditMenu={onEditMenu}
          onToast={onToast}
        />
      ) : null}
    </div>
  );
}

// Mirrors App\Models\HotelFoodOrder::STATUSES / ::FLOW. Cancelled sits off the flow.
const ORDER_FLOW = ['Preparing', 'Ready', 'Delivering', 'Completed'];
const ORDER_STATUSES = [...ORDER_FLOW, 'Cancelled'];
const OPEN_ORDER_STATUSES = ['Preparing', 'Ready'];

/* Mirrors HotelFoodOrder::CATERING_FLOW. Catering is agreed days ahead and served over
   hours, so it starts before the kitchen has accepted it and ends with Serving rather
   than a runner's Delivering. */
const CATERING_FLOW = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Serving', 'Completed'];
const CATERING_OPEN_STATUSES = ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Serving'];

function flowFor(orderType) {
  return orderType === 'catering' ? CATERING_FLOW : ORDER_FLOW;
}
function openStatusesFor(orderType) {
  return orderType === 'catering' ? CATERING_OPEN_STATUSES : OPEN_ORDER_STATUSES;
}

/* The button the kitchen presses next, given where the order is now. Every step of
   the flow is theirs, delivery included, so none of them is held back. */
function nextKitchenStatus(status, orderType) {
  const flow = flowFor(orderType);
  const at = flow.indexOf(status);
  if (at < 0 || at >= flow.length - 1) return null;
  return flow[at + 1];
}

const ORDER_ACTION_LABEL = {
  Confirmed: 'Accept Order',
  Preparing: 'Start Preparing',
  Ready: 'Mark Ready',
  Delivering: 'Start Delivery',
  Serving: 'Start Serving',
  Completed: 'Complete Order',
};

/* Mirrors HotelFoodOrder::isForwardTransition() — status only moves forward here
   too, so a disabled pill in the UI matches what the server would refuse anyway. */
function canMoveOrderTo(from, to, orderType) {
  if (from === to || from === 'Completed' || from === 'Cancelled') return false;
  if (to === 'Cancelled') return true;
  const flow = flowFor(orderType);
  const fromAt = flow.indexOf(from);
  const toAt = flow.indexOf(to);
  return fromAt !== -1 && toAt !== -1 && toAt > fromAt;
}

function formatOrderTime(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

/* When the customer is due. Carries the date as well as the clock, because a
   reservation taken today can be for tomorrow. */
function formatBookedFor(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function createEmptyTableForm() {
  return { name: '', capacity: '2' };
}

const BILL_METHODS = ['Cash', 'GCash', 'Card', 'Other'];

/*
 * The last two steps of the dine-in flow: present the bill, take the money.
 *
 * Priced by the server (HotelDineInBill) rather than added up here, so what the
 * customer is charged cannot drift from what gets written down. Settling frees the
 * table in the same call — a table left Occupied after the customer has paid and
 * walked out is a table nobody can reserve.
 */
function DineInBillModal({ table, onFetchBill, onSettle, onClose, onToast }) {
  const [bill, setBill] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [method, setMethod] = useState('Cash');
  const [amount, setAmount] = useState('');
  const [reference, setReference] = useState('');
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    Promise.resolve(onFetchBill(table.id))
      .then(data => {
        if (!alive) return;
        setBill(data);
        // Settling in full is the common case; the field stays editable for a
        // customer handing over something else.
        setAmount(String(data && data.total != null ? data.total : ''));
      })
      .catch(err => { if (alive) setError((err && err.message) || 'Could not load this bill.'); })
      .finally(() => { if (alive) setLoading(false); });
    return () => { alive = false; };
  }, [table.id, onFetchBill]);

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape' && !busy) onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose, busy]);

  const total = bill ? Number(bill.total) || 0 : 0;
  const paid = Math.max(0, parseFloat(amount) || 0);
  const shortBy = Math.max(0, total - paid);
  const unserved = (bill && bill.unserved) || [];

  const settle = (e) => {
    e.preventDefault();
    if (shortBy > 0 && !window.confirm(
      `${formatPeso(shortBy)} of this bill will go uncollected. Close the table anyway?`
    )) return;
    setBusy(true);
    Promise.resolve(onSettle(table.id, { amount_paid: paid, method, reference: reference.trim() }))
      .then(() => {
        if (onToast) onToast(`${table.name} settled — ${formatPeso(paid)} by ${method}.`);
        onClose();
      })
      .catch(err => setError((err && err.message) || 'Could not settle this table.'))
      .finally(() => setBusy(false));
  };

  const fieldLabel = {
    fontSize: '0.6rem', letterSpacing: '0.12em', textTransform: 'uppercase',
    color: 'var(--fg-muted)', display: 'block', marginBottom: '0.35rem',
  };

  return (
    <div className="tb-modal-overlay" onClick={() => { if (!busy) onClose(); }} role="dialog" aria-modal="true">
      <div className="tb-modal" onClick={e => e.stopPropagation()}>
        <div className="tb-modal-head">
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.2em', textTransform: 'uppercase', margin: '0 0 0.35rem' }}>Dine-in</p>
            <h2 className="font-display" style={{ fontSize: '1.4rem', margin: 0, color: 'var(--fg)' }}>Final Bill · {table.name}</h2>
            <p style={{ margin: '0.3rem 0 0', color: 'var(--fg-muted)', fontSize: '0.78rem' }}>
              {table.guestName || 'Guest'} · party of {table.partySize || '—'}
              {table.contactNo ? ` · ${table.contactNo}` : ''}
            </p>
          </div>
          <button type="button" onClick={onClose} disabled={busy} aria-label="Close"
            style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: busy ? 'not-allowed' : 'pointer', flexShrink: 0 }}>
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div className="tb-modal-body">
          {loading ? (
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.85rem', margin: 0 }}>Loading the bill…</p>
          ) : !bill ? (
            <p style={{ color: 'var(--danger, #fb7185)', fontSize: '0.85rem', margin: 0 }}>{error || 'No bill to show.'}</p>
          ) : (
            <>
              {bill.items.length === 0 ? (
                <p style={{ color: 'var(--fg-muted)', fontSize: '0.85rem', margin: '0 0 1rem' }}>
                  Nothing has been ordered at this table yet. Close it out instead of billing it.
                </p>
              ) : (
                <>
                  <p className="tb-bill-title">Ordered</p>
                  {bill.items.map((line, i) => (
                    <div className="tb-bill-line" key={line.orderId + '-' + i}>
                      <span>{line.name} × {line.qty} <span style={{ opacity: 0.55 }}>· #{line.orderId}</span></span>
                      <span className="tb-bill-amt">{formatPeso(line.line)}</span>
                    </div>
                  ))}
                  <div className="tb-bill-line is-total">
                    <span>Total Bill</span>
                    <span className="tb-bill-amt">{formatPeso(total)}</span>
                  </div>
                </>
              )}

              {unserved.length > 0 && (
                <p style={{ margin: '0.9rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.78rem' }}>
                  <i className="fa-solid fa-triangle-exclamation" style={{ marginRight: 6, fontSize: '0.72rem' }}></i>
                  {unserved.map(o => '#' + o.orderId + ' (' + o.status + ')').join(', ')} still with the kitchen —
                  serve the customer before billing the table.
                </p>
              )}

              {bill.items.length > 0 && unserved.length === 0 && (
                <form onSubmit={settle} style={{ marginTop: '1.3rem' }}>
                  <p className="tb-bill-title" style={{ marginTop: 0 }}>Payment</p>
                  <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.7rem' }}>
                    <div>
                      <label style={fieldLabel}>Payment Method</label>
                      <select className="booking-input" value={method} onChange={e => setMethod(e.target.value)}
                        style={{ colorScheme: 'dark', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)' }}>
                        {BILL_METHODS.map(m => <option key={m} value={m} style={{ background: 'var(--card, #181714)' }}>{m}</option>)}
                      </select>
                    </div>
                    <div>
                      <label style={fieldLabel}>Amount Paid</label>
                      <input type="number" className="booking-input" min="0" step="0.01" value={amount}
                        onChange={e => setAmount(e.target.value)} />
                    </div>
                  </div>
                  {method !== 'Cash' && (
                    <div style={{ marginTop: '0.7rem' }}>
                      <label style={fieldLabel}>Reference</label>
                      <input type="text" className="booking-input" placeholder="Receipt no., card last 4, or ref #"
                        value={reference} onChange={e => setReference(e.target.value)} />
                    </div>
                  )}
                  <p style={{ margin: '0.65rem 0 0', fontSize: '0.76rem', color: shortBy > 0 ? 'var(--danger, #fb7185)' : 'var(--success, #4ade80)' }}>
                    {shortBy > 0
                      ? `${formatPeso(shortBy)} of this bill will go uncollected.`
                      : 'This settles the bill in full.'}
                  </p>
                  {error && <p style={{ margin: '0.55rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.78rem' }}>{error}</p>}
                  <button type="submit" className="btn-primary" disabled={busy} style={{ width: '100%', marginTop: '1rem', justifyContent: 'center' }}>
                    <i className="fa-solid fa-cash-register" style={{ fontSize: '0.7rem' }}></i>
                    {busy ? 'Settling…' : 'Mark Paid & Close Table'}
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

function ManageTablesPanel({ tables, orders, canManage, onAddTable, onEditTable, onCloseTable, onRemoveTable, onSeatTable, onFetchBill, onSettleTable, onToast }) {
  const [form, setForm] = useState(createEmptyTableForm);
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [editForm, setEditForm] = useState(null);
  const [billingTableId, setBillingTableId] = useState(null);

  const fieldLabel = {
    fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase',
    color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem',
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const next = {};
    if (!String(form.name).trim()) next.name = 'Table name is required.';
    const capacity = parseInt(form.capacity, 10);
    if (!Number.isFinite(capacity) || capacity < 1) next.capacity = 'Enter how many seats this table has.';
    setErrors(next);
    if (Object.keys(next).length) return;

    setSaving(true);
    Promise.resolve(onAddTable({ name: String(form.name).trim(), capacity }))
      .then(() => { if (onToast) onToast(`${form.name} added.`); setForm(createEmptyTableForm()); })
      .catch(err => setErrors({ form: (err && err.message) || 'Could not add this table.' }))
      .finally(() => setSaving(false));
  };

  const saveEdit = (table) => {
    const capacity = parseInt(editForm.capacity, 10);
    if (!String(editForm.name).trim() || !Number.isFinite(capacity) || capacity < 1) {
      if (onToast) onToast('Enter a valid name and capacity.');
      return;
    }
    Promise.resolve(onEditTable(table.id, { name: String(editForm.name).trim(), capacity }))
      .then(() => { setEditingId(null); if (onToast) onToast(`${editForm.name} updated.`); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not update this table.'); });
  };

  const seatTable = (table) => {
    Promise.resolve(onSeatTable(table.id))
      .then(() => { if (onToast) onToast(`${table.guestName || 'The customer'} is seated at ${table.name}.`); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not seat this table.'); });
  };

  const closeTable = (table) => {
    const held = table.status === 'Reserved';
    if (!hmsConfirm(held
      ? `Cancel the reservation on ${table.name} and free it up?`
      : `Close ${table.name} and free it up?`)) return;
    Promise.resolve(onCloseTable(table.id))
      .then(() => { if (onToast) onToast(`${table.name} is now available.`); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not close this table.'); });
  };

  const removeTable = (table) => {
    if (!hmsConfirm(`Remove ${table.name}?`)) return;
    Promise.resolve(onRemoveTable(table.id))
      .then(() => { if (onToast) onToast(`${table.name} removed.`); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not remove this table.'); });
  };

  const list = tables || [];
  const hasOpenOrder = (table) => (orders || []).some(o => o.tableId === table.id && OPEN_ORDER_STATUSES.includes(o.status));
  // Re-read from state so the modal follows a refresh rather than freezing at open time.
  const billingTable = billingTableId ? (list.find(t => t.id === billingTableId) || null) : null;

  return (
    <div className="rm-panel" style={{ maxWidth: '100%' }}>
      <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Dine-in</p>
      <h3>Manage Tables</h3>
      <p className="rm-panel-desc">
        Add tables for Front Desk to reserve for a customer. A Reserved table is being
        held for someone who has not arrived yet — seat them here when they do, which
        is what lets the Dine-In tab in Orders take their order. Bill the table here
        once they have been served.
      </p>

      {canManage && (
        <form onSubmit={handleSubmit} className="rm-form-row" noValidate style={{ maxWidth: 520, marginBottom: '1.5rem' }}>
          <div>
            <label style={fieldLabel}>Table Name *</label>
            <input
              type="text" className="booking-input" placeholder="e.g. Table 5"
              value={form.name} onChange={e => setForm(p => Object.assign({}, p, { name: e.target.value }))}
              style={errors.name ? { borderColor: '#f43f5e' } : undefined}
            />
            {errors.name && <p style={{ margin: '0.35rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.72rem' }}>{errors.name}</p>}
          </div>
          <div>
            <label style={fieldLabel}>Seats *</label>
            <input
              type="number" min="1" step="1" className="booking-input" placeholder="e.g. 4"
              value={form.capacity} onChange={e => setForm(p => Object.assign({}, p, { capacity: e.target.value }))}
              style={errors.capacity ? { borderColor: '#f43f5e' } : undefined}
            />
            {errors.capacity && <p style={{ margin: '0.35rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.72rem' }}>{errors.capacity}</p>}
          </div>
          <div style={{ gridColumn: '1 / -1' }}>
            {errors.form && <p style={{ margin: '0 0 0.6rem', color: 'var(--danger, #fb7185)', fontSize: '0.78rem' }}>{errors.form}</p>}
            <button type="submit" className="btn-primary" disabled={saving}>
              <i className="fa-solid fa-plus" style={{ fontSize: '0.7rem' }}></i> {saving ? 'Adding…' : 'Add Table'}
            </button>
          </div>
        </form>
      )}

      {list.length === 0 ? (
        <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', margin: 0 }}>No tables yet.</p>
      ) : (
        <div style={{ display: 'grid', gap: '0.6rem' }}>
          {list.map(table => {
            const isEditing = editingId === table.id;
            const occupied = table.status === 'Occupied';
            const reserved = table.status === 'Reserved';
            // Both hold a party, so both show its details and neither can be edited.
            const taken = occupied || reserved;

            return (
              <div key={table.id} style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '0.75rem 0.9rem' }}>
                {isEditing ? (
                  <div className="rm-form-row" style={{ alignItems: 'end' }}>
                    <input
                      type="text" className="booking-input" value={editForm.name}
                      onChange={e => setEditForm(p => Object.assign({}, p, { name: e.target.value }))}
                    />
                    <input
                      type="number" min="1" className="booking-input" value={editForm.capacity}
                      onChange={e => setEditForm(p => Object.assign({}, p, { capacity: e.target.value }))}
                    />
                    <div style={{ gridColumn: '1 / -1', display: 'flex', gap: '0.5rem' }}>
                      <button type="button" className="btn-primary" style={{ fontSize: '0.7rem', padding: '0.5rem 1rem' }} onClick={() => saveEdit(table)}>Save</button>
                      <button type="button" className="btn-outline" style={{ fontSize: '0.7rem', padding: '0.5rem 1rem' }} onClick={() => setEditingId(null)}>Cancel</button>
                    </div>
                  </div>
                ) : (
                  <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '0.75rem', flexWrap: 'wrap' }}>
                    <div>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.3rem' }}>
                        <span style={{ color: 'var(--fg)', fontWeight: 700 }}>{table.name}</span>
                        <span className={`tb-badge tb-${table.status.toLowerCase()}`}>{table.status}</span>
                      </div>
                      <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.76rem' }}>
                        Seats {table.capacity}
                        {taken && table.guestName ? ` · ${table.guestName}, party of ${table.partySize || '—'}` : ''}
                      </p>
                      {taken && (table.contactNo || table.reservedFor) && (
                        <p style={{ margin: '0.2rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem' }}>
                          {table.contactNo ? table.contactNo : ''}
                          {table.contactNo && table.reservedFor ? ' · ' : ''}
                          {table.reservedFor ? 'booked for ' + formatBookedFor(table.reservedFor) : ''}
                        </p>
                      )}
                    </div>
                    {canManage && (
                      <div style={{ display: 'flex', gap: 6, flexShrink: 0 }}>
                        {reserved ? (
                          <>
                            {/* Nothing can be ordered at a held table until the
                                customer is actually sitting at it. */}
                            <button type="button" className="btn-primary"
                              style={{ fontSize: '0.66rem', padding: '0.4rem 0.75rem' }}
                              onClick={() => seatTable(table)}>
                              <i className="fa-solid fa-chair" style={{ fontSize: '0.66rem' }}></i> Seat
                            </button>
                            <button type="button" title="Cancel reservation and free the table"
                              onClick={() => closeTable(table)} style={toolBtnStyle('danger')}>
                              <i className="fa-solid fa-door-closed" style={{ fontSize: 11 }}></i>
                            </button>
                          </>
                        ) : occupied ? (
                          <>
                            {/* Billing is the way an occupied table normally ends —
                                closing it out without one is for a party that never
                                ordered, or a ticket written off. */}
                            <button type="button" className="btn-primary"
                              style={{ fontSize: '0.66rem', padding: '0.4rem 0.75rem' }}
                              onClick={() => setBillingTableId(table.id)}>
                              <i className="fa-solid fa-receipt" style={{ fontSize: '0.66rem' }}></i> Bill
                            </button>
                            <button type="button" title="Close table without billing" disabled={hasOpenOrder(table)}
                              onClick={() => closeTable(table)} style={toolBtnStyle('danger')}>
                              <i className="fa-solid fa-door-closed" style={{ fontSize: 11 }}></i>
                            </button>
                          </>
                        ) : (
                          <>
                            <button type="button" title="Edit table" onClick={() => { setEditingId(table.id); setEditForm({ name: table.name, capacity: String(table.capacity) }); }} style={toolBtnStyle('edit')}>
                              <i className="fa-solid fa-pen" style={{ fontSize: 10 }}></i>
                            </button>
                            <button type="button" title="Remove table" onClick={() => removeTable(table)} style={toolBtnStyle('danger')}>
                              <i className="fa-solid fa-xmark" style={{ fontSize: 12 }}></i>
                            </button>
                          </>
                        )}
                      </div>
                    )}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}

      {billingTable && (
        <DineInBillModal
          table={billingTable}
          onFetchBill={onFetchBill}
          onSettle={onSettleTable}
          onClose={() => setBillingTableId(null)}
          onToast={onToast}
        />
      )}
    </div>
  );
}

/*
 * One ticket queue for the kitchen, split by order type. Restaurant Management runs
 * both kinds end to end — Front Desk places a room-service order against a checked-in
 * guest's stay and then only watches it, and dine-in orders are taken here from Manage
 * Tables. A dine-in ticket is worked table-side, so it may jump straight to any status
 * and it may still be cancelled. A room-service one steps through the flow one button
 * at a time and has no cancel: it is already on the guest's bill, so it runs all the
 * way to Completed with the runner carrying it up to the room.
 */
function RoomServiceOrderCard({ order, onMove }) {
  const next = nextKitchenStatus(order.status, order.orderType);
  const finished = order.status === 'Completed' || order.status === 'Cancelled';

  return (
    <div className="order-card">
      <div className="order-card-head">
        <span className="order-card-id">Order #{order.id}</span>
        <span className={`tb-badge tb-${order.status.toLowerCase()}`}>{order.status}</span>
      </div>

      <dl style={{ margin: '0 0 0.75rem' }}>
        <div className="order-field"><dt>Guest</dt><dd>{order.guestName || '—'}</dd></div>
        <div className="order-field"><dt>Room</dt><dd>{order.roomNumber || '—'}</dd></div>
        <div className="order-field">
          <dt>Order</dt>
          <dd>{(order.items || []).map(i => `${i.name} ×${i.qty}`).join(', ') || '—'}</dd>
        </div>
        <div className="order-field"><dt>Time</dt><dd>{formatOrderTime(order.placedAt)}</dd></div>
        <div className="order-field">
          <dt>Total</dt>
          <dd style={{ color: 'var(--accent-light)', fontWeight: 700 }}>{formatPeso(order.total)}</dd>
        </div>
      </dl>

      {order.status === 'Delivering' && (
        <p style={{ margin: '0 0 0.6rem', color: 'var(--fg-muted)', fontSize: '0.74rem' }}>
          On the way to room {order.roomNumber || '—'}. Complete it once the guest has it.
        </p>
      )}

      {!finished && next && (
        <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
          <button type="button" className="btn-primary" style={{ fontSize: '0.7rem', padding: '0.5rem 1rem' }}
            onClick={() => onMove(order, next)}>
            {ORDER_ACTION_LABEL[next] || next}
          </button>
        </div>
      )}
    </div>
  );
}

function DineInOrderCard({ order, table, onMove }) {
  return (
    <div className="order-card">
      <div className="order-card-head">
        <span className="order-card-id">Order #{order.id}</span>
        <span className={`tb-badge tb-${order.status.toLowerCase()}`}>{order.status}</span>
      </div>

      <dl style={{ margin: '0 0 0.75rem' }}>
        <div className="order-field"><dt>Table</dt><dd>{(table && table.name) || '—'}</dd></div>
        <div className="order-field"><dt>Guest</dt><dd>{order.guestName || '—'}</dd></div>
        <div className="order-field"><dt>Assigned By</dt><dd>{(table && table.assignedBy) || '—'}</dd></div>
        <div className="order-field">
          <dt>Order</dt>
          <dd>{(order.items || []).map(i => `${i.name} ×${i.qty}`).join(', ') || '—'}</dd>
        </div>
        <div className="order-field"><dt>Time</dt><dd>{formatOrderTime(order.placedAt)}</dd></div>
        <div className="order-field">
          <dt>Total</dt>
          <dd style={{ color: 'var(--accent-light)', fontWeight: 700 }}>{formatPeso(order.total)}</dd>
        </div>
      </dl>

      <div style={{ display: 'flex', gap: '0.35rem', flexWrap: 'wrap' }}>
        {ORDER_STATUSES.map(status => (
          <button
            key={status}
            type="button"
            className={`tb-tab ${order.status === status ? 'is-active' : ''}`}
            disabled={!canMoveOrderTo(order.status, status, order.orderType)}
            onClick={() => onMove(order, status)}
          >
            {status}
          </button>
        ))}
      </div>
    </div>
  );
}

/*
 * Taking a dine-in order lives here now, not on the table card in Manage Tables —
 * Manage Tables only adds tables and shows their status. The cart-building logic is
 * the same as before, just keyed off a table picked from this form instead of the
 * table the card belonged to.
 */
function NewDineInOrderForm({ tables, menus, onPlaceOrder, onToast }) {
  const [tableId, setTableId] = useState('');
  const [cart, setCart] = useState({});
  const [category, setCategory] = useState('All');
  const [placing, setPlacing] = useState(false);

  const fieldLabel = {
    fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase',
    color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem',
  };

  const occupiedTables = (tables || []).filter(t => t.status === 'Occupied');
  const menuList = (menus || []).filter(m => category === 'All' || normalizeMenuCategory(m.category) === category);

  const addToCart = (item) => {
    setCart(prev => {
      const qty = (prev[item.id] && prev[item.id].qty) || 0;
      return Object.assign({}, prev, { [item.id]: { item, qty: qty + 1 } });
    });
  };
  const removeFromCart = (id) => {
    setCart(prev => {
      const next = Object.assign({}, prev);
      const line = next[id];
      if (!line) return prev;
      if (line.qty <= 1) { delete next[id]; return next; }
      next[id] = Object.assign({}, line, { qty: line.qty - 1 });
      return next;
    });
  };

  const cartLines = Object.values(cart);
  const cartTotal = cartLines.reduce((sum, l) => sum + (Number(l.item.price) || 0) * l.qty, 0);

  const placeOrder = () => {
    if (!tableId) { if (onToast) onToast('Choose a table first.'); return; }
    if (!cartLines.length) { if (onToast) onToast('Add at least one item to the order.'); return; }
    setPlacing(true);
    const items = cartLines.map(l => ({ menu_item_id: l.item.dbId, name: l.item.name, price: l.item.price, qty: l.qty }));
    const table = occupiedTables.find(t => String(t.id) === String(tableId));
    Promise.resolve(onPlaceOrder(tableId, items))
      .then(() => {
        setCart({});
        setTableId('');
        if (onToast) onToast(`Order sent to the kitchen for ${table ? table.name : 'the table'}.`);
      })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not place this order.'); })
      .finally(() => setPlacing(false));
  };

  return (
    <div className="order-card" style={{ marginBottom: '1.2rem' }}>
      <h4 style={{ margin: '0 0 0.85rem', fontFamily: 'var(--font-display, Playfair Display, serif)', fontSize: '1rem', color: 'var(--fg)' }}>
        New Dine-In Order
      </h4>

      <div style={{ marginBottom: '0.9rem', maxWidth: 320 }}>
        <label style={fieldLabel}>Table</label>
        <select
          className="booking-input" value={tableId} onChange={e => setTableId(e.target.value)}
          style={{ colorScheme: 'dark', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)' }}
        >
          <option value="" style={{ background: 'var(--card, #181714)' }}>Select a seated table…</option>
          {/* The guest's name, not assignedBy — whoever is taking this order needs to
              know which customer they are ordering for, not which staffer seated them. */}
          {occupiedTables.map(t => (
            <option key={t.id} value={t.id} style={{ background: 'var(--card, #181714)' }}>
              {t.name}{t.guestName ? ` — ${t.guestName}` : ''}
            </option>
          ))}
        </select>
        {occupiedTables.length === 0 && (
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.74rem' }}>
            No customer is seated right now — seat a reserved table in Manage Tables first.
          </p>
        )}
      </div>

      <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap', marginBottom: '0.75rem' }}>
        <button type="button" className={`tb-tab ${category === 'All' ? 'is-active' : ''}`} onClick={() => setCategory('All')}>All</button>
        {MENU_CATEGORIES.map(c => (
          <button key={c} type="button" className={`tb-tab ${category === c ? 'is-active' : ''}`} onClick={() => setCategory(c)}>{c}</button>
        ))}
      </div>

      <div style={{ display: 'grid', gap: '0.4rem', maxHeight: 340, overflowY: 'auto', marginBottom: '0.85rem' }}>
        {menuList.length === 0 && (
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.78rem' }}>No menu items in this category.</p>
        )}
        {menuList.map(item => (
          <div key={item.id} style={{ display: 'flex', alignItems: 'center', gap: '0.6rem', border: '1px solid var(--border)', borderRadius: 8, padding: '0.45rem 0.6rem' }}>
            {/* Whoever is taking the order picks the dish by sight, so the photo comes
                before the name — same thumbnail the Manage Menu list uses. */}
            <img src={menuFoodImg(item)} alt="" style={{ width: 52, height: 40, objectFit: 'cover', borderRadius: 6, flexShrink: 0, background: 'var(--bg-warm, #12110f)' }} />
            <div style={{ minWidth: 0, flex: 1 }}>
              <p style={{ margin: 0, color: 'var(--fg)', fontSize: '0.82rem', fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{item.name}</p>
              {item.sub ? (
                <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.7rem', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{item.sub}</p>
              ) : null}
              <p style={{ margin: 0, color: 'var(--accent-light)', fontSize: '0.74rem' }}>{formatPeso(item.price)}</p>
            </div>
            {item.stock <= 0 ? (
              <span style={{ fontSize: '0.68rem', color: 'var(--danger, #fb7185)' }}>Out of stock</span>
            ) : cart[item.id] ? (
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.4rem' }}>
                <button type="button" onClick={() => removeFromCart(item.id)} style={toolBtnStyle('edit')}>−</button>
                <span style={{ color: 'var(--fg)', fontSize: '0.82rem', minWidth: 16, textAlign: 'center' }}>{cart[item.id].qty}</span>
                <button type="button" onClick={() => addToCart(item)} style={toolBtnStyle('edit')}>+</button>
              </div>
            ) : (
              <button type="button" className="btn-outline" style={{ fontSize: '0.68rem', padding: '0.4rem 0.7rem' }} onClick={() => addToCart(item)}>
                Add
              </button>
            )}
          </div>
        ))}
      </div>

      {cartLines.length > 0 && (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <span style={{ color: 'var(--fg-muted)', fontSize: '0.78rem' }}>{cartLines.length} item(s) · {formatPeso(cartTotal)}</span>
          <button type="button" className="btn-primary" disabled={placing} onClick={placeOrder} style={{ fontSize: '0.7rem', padding: '0.5rem 1rem' }}>
            {placing ? 'Sending…' : 'Order Now'}
          </button>
        </div>
      )}
    </div>
  );
}

/* Catering packages — Restaurant Services' rate card for function room events.
 *
 * Not menu items, and the difference is the whole reason this panel exists: a menu item
 * is one dish with a stock count that the order pipeline decrements per unit, while a
 * package is priced per head and has no shelf to come off. Front Desk picks from this
 * list when booking a hall and cannot add to it — which is what keeps catering actually
 * coming out of this module rather than being typed into a booking form.
 */
function CateringPackageModal({ pkg, onClose, onSaved }) {
  const isEdit = !!pkg;
  const [form, setForm] = useState(() => ({
    name: (pkg && pkg.name) || '',
    description: (pkg && pkg.description) || '',
    inclusions: (pkg && pkg.inclusions) || '',
    pricePerHead: pkg ? String(pkg.pricePerHead) : '',
    minGuests: pkg ? String(pkg.minGuests) : '20',
    isActive: pkg ? pkg.isActive : true,
  }));
  const [error, setError] = useState(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const update = (k, v) => setForm(prev => Object.assign({}, prev, { [k]: v }));

  const submit = (e) => {
    e.preventDefault();
    if (!form.name.trim()) { setError('Name the package.'); return; }
    const price = parseInt(form.pricePerHead, 10);
    if (!Number.isFinite(price) || price < 0) { setError('Enter a price per head.'); return; }

    setSaving(true);
    fetch('/students/hotel/catering-packages' + (isEdit ? '/' + pkg.dbId : ''), {
      method: isEdit ? 'PATCH' : 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({
        name: form.name.trim(),
        description: form.description.trim(),
        inclusions: form.inclusions.trim(),
        price_per_head: price,
        min_guests: Math.max(1, parseInt(form.minGuests, 10) || 1),
        is_active: !!form.isActive,
      }),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => { onSaved(data.package); onClose(); })
      .catch(err => setError((err && err.message) ? err.message : 'Could not save that package.'))
      .finally(() => setSaving(false));
  };

  const label = { fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem' };

  return (
    <div className="room-modal-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" style={{ maxWidth: 460 }} onClick={e => e.stopPropagation()}>
        <div style={{ padding: '1.5rem' }}>
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
            {isEdit ? 'Update Package' : 'New Package'}
          </p>
          <h2 className="font-display" style={{ fontSize: '1.4rem', margin: '0 0 1.1rem', color: 'var(--fg)' }}>
            {isEdit ? pkg.name : 'Add a Catering Package'}
          </h2>

          <form onSubmit={submit} style={{ display: 'grid', gap: '0.95rem' }} noValidate>
            <div>
              <label style={label}>Name *</label>
              <input type="text" className="booking-input" value={form.name}
                placeholder="Premium Buffet" onChange={e => update('name', e.target.value)} />
            </div>
            <div>
              <label style={label}>Description</label>
              <input type="text" className="booking-input" value={form.description}
                placeholder="Wider spread with a carving station."
                onChange={e => update('description', e.target.value)} />
            </div>
            <div>
              <label style={label}>Inclusions</label>
              <textarea className="booking-input" value={form.inclusions}
                placeholder="Rice · 3 main dishes · Carving station · Salad bar · Dessert · Drinks"
                onChange={e => update('inclusions', e.target.value)} />
              <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem', lineHeight: 1.5 }}>
                What Front Desk reads out to the customer when they pick this package.
              </p>
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.85rem' }}>
              <div>
                <label style={label}>Price per head (₱) *</label>
                <input type="number" min="0" className="booking-input" value={form.pricePerHead}
                  placeholder="750" onChange={e => update('pricePerHead', e.target.value)} />
              </div>
              <div>
                <label style={label}>Minimum guests</label>
                <input type="number" min="1" className="booking-input" value={form.minGuests}
                  placeholder="50" onChange={e => update('minGuests', e.target.value)} />
              </div>
            </div>
            <p style={{ margin: '-0.35rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem', lineHeight: 1.5 }}>
              Priced per head, not per portion — there is no stock to keep. An event of 100
              at ₱{form.pricePerHead || '0'} bills {formatPeso((parseInt(form.pricePerHead, 10) || 0) * 100)}.
            </p>

            {isEdit && (
              <label style={{ display: 'flex', alignItems: 'center', gap: '0.55rem', fontSize: '0.78rem', color: 'var(--fg-muted)', cursor: 'pointer' }}>
                <input type="checkbox" checked={form.isActive} onChange={e => update('isActive', e.target.checked)} />
                <span>Offered to Front Desk. Turn off to retire it — booked events keep working.</span>
              </label>
            )}

            {error && <p style={{ margin: 0, color: '#fb7185', fontSize: '0.72rem' }}>{error}</p>}

            <button type="submit" className="btn-primary" disabled={saving} style={{ justifyContent: 'center' }}>
              {saving ? 'Saving…' : (isEdit ? 'Save Changes' : 'Add Package')}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

function CateringPackagesPanel({ packages, canManage, onSaved, onBack }) {
  const [editing, setEditing] = useState(null);

  return (
    <div className="rm-panel" style={{ maxWidth: '100%' }}>
      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '1rem', flexWrap: 'wrap' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Events</p>
          <h3>Catering Packages</h3>
          <p className="rm-panel-desc">
            What you sell to a function room booking. Front Desk picks one and enters the
            headcount; the order lands on your board under Catering. These are priced per
            head and carry no stock, which is why they are not on the menu.
          </p>
        </div>
        {canManage && (
          <button type="button" className="btn-outline" onClick={() => setEditing('new')}>
            <i className="fa-solid fa-plus" style={{ fontSize: '0.65rem' }}></i> Add Package
          </button>
        )}
      </div>

      {packages.length === 0 ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem 1rem', textAlign: 'center', color: 'var(--fg-muted)' }}>
          <i className="fa-solid fa-champagne-glasses" style={{ fontSize: '1.6rem', opacity: 0.4, display: 'block', marginBottom: '0.6rem' }}></i>
          <p style={{ margin: 0, fontSize: '0.85rem' }}>No catering packages yet.</p>
        </div>
      ) : (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: '1rem' }}>
          {packages.map(p => (
            <div key={p.dbId} className="order-card" style={{ opacity: p.isActive ? 1 : 0.55 }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', gap: '0.75rem', alignItems: 'flex-start' }}>
                <div>
                  <p style={{ margin: 0, fontWeight: 700, color: 'var(--fg)', fontSize: '0.95rem' }}>{p.name}</p>
                  <p style={{ margin: '0.15rem 0 0', color: 'var(--accent-light)', fontWeight: 700, fontSize: '0.9rem' }}>
                    {formatPeso(p.pricePerHead)} <span style={{ color: 'var(--fg-muted)', fontWeight: 400, fontSize: '0.72rem' }}>/ head</span>
                  </p>
                </div>
                {!p.isActive && <span className="tb-badge tb-cancelled">Retired</span>}
              </div>

              {p.description && (
                <p style={{ margin: '0.6rem 0 0', fontSize: '0.78rem', color: 'var(--fg-muted)', lineHeight: 1.5 }}>{p.description}</p>
              )}
              {p.inclusions && (
                <p style={{ margin: '0.5rem 0 0', fontSize: '0.74rem', color: 'var(--fg-muted)', lineHeight: 1.55 }}>{p.inclusions}</p>
              )}

              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '0.75rem', borderTop: '1px solid var(--border)', paddingTop: '0.6rem', marginTop: '0.7rem' }}>
                <span style={{ fontSize: '0.72rem', color: 'var(--fg-muted)' }}>Minimum {p.minGuests} guests</span>
                {canManage && (
                  <button type="button" className="btn-outline"
                    style={{ fontSize: '0.68rem', padding: '0.35rem 0.75rem' }}
                    onClick={() => setEditing(p)}>
                    <i className="fa-solid fa-pen" style={{ fontSize: '0.62rem' }}></i> Update
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      <button type="button" className="btn-outline" onClick={onBack} style={{ marginTop: '1.25rem', fontSize: '0.72rem' }}>
        <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.7rem' }}></i> Back
      </button>

      {editing && (
        <CateringPackageModal
          pkg={editing === 'new' ? null : editing}
          onClose={() => setEditing(null)}
          onSaved={onSaved}
        />
      )}
    </div>
  );
}

/* A catering ticket. Unlike a room-service order it is not one trip upstairs — it is
   agreed days ahead, prepared, then served across an event — so the card shows the whole
   flow as pills and the kitchen jumps to wherever they actually are.

   Everything here about the event (venue, date, headcount, requests) comes from the
   reservation Front Desk booked. Restaurant Services never type it in. */
function CateringOrderCard({ order, onMove }) {
  const finished = order.status === 'Completed' || order.status === 'Cancelled';

  return (
    <div className="order-card">
      <div style={{ display: 'flex', justifyContent: 'space-between', gap: '0.75rem', alignItems: 'flex-start' }}>
        <div style={{ minWidth: 0 }}>
          <p style={{ margin: 0, fontWeight: 700, color: 'var(--fg)', fontSize: '0.95rem' }}>
            {order.eventVenue || 'Function Room'}
          </p>
          <p style={{ margin: '0.15rem 0 0', color: 'var(--fg-muted)', fontSize: '0.74rem' }}>
            {order.guestName}
            {order.eventType ? ' · ' + order.eventType : ''}
          </p>
        </div>
        <span className={`tb-badge tb-${order.status.toLowerCase()}`}>{order.status}</span>
      </div>

      <div style={{ margin: '0.6rem 0', display: 'flex', flexWrap: 'wrap', gap: '0.35rem 0.9rem', fontSize: '0.74rem', color: 'var(--fg-muted)' }}>
        {order.eventDate && (
          <span><i className="fa-solid fa-calendar-day" style={{ marginRight: '0.35rem', color: 'var(--accent)' }}></i>{order.eventDate}</span>
        )}
        {order.eventTime && (
          <span><i className="fa-solid fa-clock" style={{ marginRight: '0.35rem', color: 'var(--accent)' }}></i>{order.eventTime}</span>
        )}
        {order.guestCount ? (
          <span><i className="fa-solid fa-users" style={{ marginRight: '0.35rem', color: 'var(--accent)' }}></i>{order.guestCount} guests</span>
        ) : null}
      </div>

      <ul style={{ listStyle: 'none', margin: '0 0 0.6rem', padding: 0, display: 'grid', gap: '0.2rem' }}>
        {(order.items || []).map((item, i) => (
          <li key={i} style={{ display: 'flex', justifyContent: 'space-between', gap: '0.75rem', fontSize: '0.78rem', color: 'var(--fg-muted)' }}>
            <span>{item.name} × {item.qty}</span>
            <span>{formatPeso((item.price || 0) * (item.qty || 0))}</span>
          </li>
        ))}
      </ul>

      {order.eventRequests && (
        <p style={{ margin: '0 0 0.6rem', fontSize: '0.74rem', color: 'var(--fg-muted)', fontStyle: 'italic', lineHeight: 1.5 }}>
          &ldquo;{order.eventRequests}&rdquo;
        </p>
      )}

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '0.75rem', borderTop: '1px solid var(--border)', paddingTop: '0.55rem' }}>
        <span style={{ fontSize: '0.72rem', color: 'var(--fg-muted)' }}>Total</span>
        <span style={{ fontWeight: 700, color: 'var(--fg)' }}>{formatPeso(order.total)}</span>
      </div>

      {!finished && (
        <div className="tb-tabs" style={{ marginTop: '0.6rem', display: 'flex', flexWrap: 'wrap', gap: '0.3rem' }}>
          {[...CATERING_FLOW, 'Cancelled'].map(status => (
            <button
              key={status}
              type="button"
              className={`tb-tab ${order.status === status ? 'is-active' : ''}`}
              disabled={!canMoveOrderTo(order.status, status, 'catering')}
              onClick={() => onMove(order, status)}
            >
              {status}
            </button>
          ))}
        </div>
      )}
    </div>
  );
}

function OrdersPanel({ orders, tables, menus, canPlaceDineIn, onPlaceOrder, onUpdateOrderStatus, onToast }) {
  const [orderType, setOrderType] = useState('room_service');
  const [filter, setFilter] = useState('Open');
  const [page, setPage] = useState(1);

  const changeOrderType = (next) => { setOrderType(next); setFilter('Open'); setPage(1); };
  const changeFilter = (next) => { setFilter(next); setPage(1); };

  // Explicit equality on all three. The old test was "not dine_in", which would have
  // quietly dropped catering orders into the Room Service tab.
  const typedOrders = (orders || [])
    .filter(o => (orderType === 'room_service'
      ? (o.orderType === 'room_service' || !o.orderType)
      : o.orderType === orderType))
    .sort((a, b) => (a.id < b.id ? 1 : -1));

  const visible = typedOrders.filter(o => (
    filter === 'All' ? true
      : filter === 'Open' ? openStatusesFor(orderType).indexOf(o.status) !== -1
      : o.status === filter
  ));

  const openCount = typedOrders.filter(o => openStatusesFor(orderType).indexOf(o.status) !== -1).length;
  // Room service cannot be cancelled — it is already on a stay's bill — so only the tabs
  // that can offer the filter; on room service it would never match anything.
  const filters = ['Open', 'All', ...flowFor(orderType),
    ...(orderType === 'dine_in' || orderType === 'catering' ? ['Cancelled'] : [])];
  const tableFor = (id) => (tables || []).find(t => t.id === id);

  // safePage rather than page: switching to a filter with fewer orders must not
  // strand the view on a page that no longer exists.
  const PER_PAGE = 5;
  const totalPages = Math.max(1, Math.ceil(visible.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageOrders = visible.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  const move = (order, status) => {
    Promise.resolve(onUpdateOrderStatus(order.id, status))
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not update this order.'); });
  };

  return (
    <div className="rm-panel" style={{ maxWidth: '100%' }}>
      <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Kitchen</p>
      <h3>Orders</h3>
      <p className="rm-panel-desc">
        {typedOrders.length === 0
          ? (orderType === 'dine_in'
              ? 'No dine-in orders yet. Take one below once a guest is seated.'
              : orderType === 'catering'
                ? 'No catering yet. These arrive on their own when Front Desk books a function room with a catering package.'
                : 'No room-service orders yet. Front Desk places them for checked-in guests.')
          : `${openCount} order${openCount === 1 ? '' : 's'} still in the kitchen · ${typedOrders.length} total.`}
      </p>

      <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap', marginBottom: '0.75rem' }}>
        <button type="button" className={`tb-tab ${orderType === 'room_service' ? 'is-active' : ''}`} onClick={() => changeOrderType('room_service')}>
          <i className="fa-solid fa-bell-concierge" style={{ fontSize: '0.65rem', marginRight: 5 }}></i> Room Service
        </button>
        <button type="button" className={`tb-tab ${orderType === 'dine_in' ? 'is-active' : ''}`} onClick={() => changeOrderType('dine_in')}>
          <i className="fa-solid fa-utensils" style={{ fontSize: '0.65rem', marginRight: 5 }}></i> Dine-In
        </button>
        <button type="button" className={`tb-tab ${orderType === 'catering' ? 'is-active' : ''}`} onClick={() => changeOrderType('catering')}>
          <i className="fa-solid fa-champagne-glasses" style={{ fontSize: '0.65rem', marginRight: 5 }}></i> Catering
        </button>
      </div>

      {orderType === 'dine_in' && canPlaceDineIn && (
        <NewDineInOrderForm tables={tables} menus={menus} onPlaceOrder={onPlaceOrder} onToast={onToast} />
      )}

      <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap', marginBottom: '1.1rem' }}>
        {filters.map(f => (
          <button key={f} type="button" className={`tb-tab ${filter === f ? 'is-active' : ''}`} onClick={() => changeFilter(f)}>{f}</button>
        ))}
      </div>

      {visible.length === 0 ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center' }}>
          <i className={`fa-solid ${orderType === 'dine_in' ? 'fa-utensils' : orderType === 'catering' ? 'fa-champagne-glasses' : 'fa-bell-concierge'}`} style={{ fontSize: '1.6rem', color: 'var(--fg-muted)', opacity: 0.3, display: 'block', marginBottom: '0.65rem' }}></i>
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.85rem' }}>No orders in this view.</p>
        </div>
      ) : (
        <>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: '1rem', alignItems: 'stretch' }}>
            {pageOrders.map(order => (
              orderType === 'catering'
                ? <CateringOrderCard key={order.id} order={order} onMove={move} />
                : orderType === 'dine_in'
                  ? <DineInOrderCard key={order.id} order={order} table={tableFor(order.tableId)} onMove={move} />
                  : <RoomServiceOrderCard key={order.id} order={order} onMove={move} />
            ))}
          </div>

          {totalPages > 1 && (
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: '1rem', gap: '0.5rem', flexWrap: 'wrap' }}>
              <span style={{ fontSize: '0.75rem', color: 'var(--fg-muted)' }}>
                Showing {(safePage - 1) * PER_PAGE + 1}–{Math.min(safePage * PER_PAGE, visible.length)} of {visible.length}
              </span>
              <div style={{ display: 'flex', gap: '0.35rem', flexWrap: 'wrap' }}>
                <button
                  type="button"
                  onClick={() => setPage(p => Math.max(1, p - 1))}
                  disabled={safePage === 1}
                  style={{ padding: '0.35rem 0.7rem', borderRadius: 6, border: '1px solid var(--border)', background: 'transparent', color: safePage === 1 ? 'var(--fg-muted)' : 'var(--fg)', cursor: safePage === 1 ? 'default' : 'pointer', fontSize: '0.78rem', opacity: safePage === 1 ? 0.4 : 1 }}
                >
                  <i className="fa-solid fa-chevron-left" style={{ fontSize: '0.65rem' }}></i>
                </button>
                {Array.from({ length: totalPages }, (_, i) => i + 1).map(n => (
                  <button
                    key={n}
                    type="button"
                    onClick={() => setPage(n)}
                    style={{ padding: '0.35rem 0.65rem', borderRadius: 6, border: '1px solid ' + (n === safePage ? 'var(--accent)' : 'var(--border)'), background: n === safePage ? 'var(--accent)' : 'transparent', color: n === safePage ? 'var(--bg)' : 'var(--fg-muted)', cursor: 'pointer', fontSize: '0.78rem', fontWeight: n === safePage ? 700 : 400 }}
                  >
                    {n}
                  </button>
                ))}
                <button
                  type="button"
                  onClick={() => setPage(p => Math.min(totalPages, p + 1))}
                  disabled={safePage === totalPages}
                  style={{ padding: '0.35rem 0.7rem', borderRadius: 6, border: '1px solid var(--border)', background: 'transparent', color: safePage === totalPages ? 'var(--fg-muted)' : 'var(--fg)', cursor: safePage === totalPages ? 'default' : 'pointer', fontSize: '0.78rem', opacity: safePage === totalPages ? 0.4 : 1 }}
                >
                  <i className="fa-solid fa-chevron-right" style={{ fontSize: '0.65rem' }}></i>
                </button>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  );
}

function RestaurantManagementPage({
  initialNav, menus, tables, orders, canManageTables,
  cateringPackages, canManageCatering, onSaveCateringPackage,
  onBack, onAddMenu, onEditMenu, onRemoveMenu,
  onAddTable, onEditTable, onCloseTable, onRemoveTable, onSeatTable, onFetchBill, onSettleTable,
  onPlaceOrder, onUpdateOrderStatus,
  onToast,
}) {
  const activeNav = initialNav || 'manage-menu';

  // Manage Menu draws its own header, numbers and cards; the other sections keep
  // the panel they have always had.
  if (activeNav === 'manage-menu') {
    return (
      <div style={{ padding: '1.5rem' }} data-hms-no-edit="1">
        <ManageMenuPanel
          menus={menus}
          onAddMenu={onAddMenu}
          onEditMenu={onEditMenu}
          onRemoveMenu={onRemoveMenu}
          onToast={onToast}
        />
      </div>
    );
  }

  return (
    <div style={{ padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.1rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Staff Tools</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Restaurant Management</h1>
        </div>
        <button type="button" className="btn-outline" onClick={onBack} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </button>
      </div>

      <div className="rm-row">
        <div className="rm-content">
          {activeNav === 'catering-packages' && (
            <CateringPackagesPanel
              packages={cateringPackages}
              canManage={canManageCatering}
              onSaved={onSaveCateringPackage}
              onBack={onBack}
            />
          )}
          {activeNav === 'manage-tables' && (
            <ManageTablesPanel
              tables={tables}
              orders={orders}
              canManage={canManageTables}
              onAddTable={onAddTable}
              onEditTable={onEditTable}
              onCloseTable={onCloseTable}
              onRemoveTable={onRemoveTable}
              onSeatTable={onSeatTable}
              onFetchBill={onFetchBill}
              onSettleTable={onSettleTable}
              onToast={onToast}
            />
          )}
          {activeNav === 'orders' && (
            <OrdersPanel
              orders={orders}
              tables={tables}
              menus={menus}
              canPlaceDineIn={canManageTables}
              onPlaceOrder={onPlaceOrder}
              onUpdateOrderStatus={onUpdateOrderStatus}
              onToast={onToast}
            />
          )}
        </div>
      </div>
    </div>
  );
}

function App() {
  const [menus, setMenus] = useState([]);
  const [cateringPackages, setCateringPackages] = useState([]);
  const [canManageCatering, setCanManageCatering] = useState(false);
  const [tables, setTables] = useState([]);
  const [orders, setOrders] = useState([]);
  const [canManageTables, setCanManageTables] = useState(false);
  const pendingWrites = useRef(0);

  const fetchMenus = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/menus', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => { if (pendingWrites.current > 0) return; if (Array.isArray(data.items)) setMenus(data.items); })
      .catch(() => {});
  }, []);

  const fetchTables = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/tables', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.tables)) setTables(data.tables);
        setCanManageTables(!!data.can_manage);
      })
      .catch(() => {});
  }, []);

  const fetchOrders = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/orders', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => { if (pendingWrites.current > 0) return; if (Array.isArray(data.orders)) setOrders(data.orders); })
      .catch(() => {});
  }, []);

  // Catering packages change far less often than tickets do, so this is loaded once
  // rather than joining the 8s poll.
  const fetchCateringPackages = useCallback(() => {
    fetch('/students/hotel/catering-packages', {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => { setCateringPackages(d.packages || []); setCanManageCatering(!!d.can_manage); })
      .catch(() => {});
  }, []);

  useEffect(() => {
    fetchMenus();
    fetchTables();
    fetchOrders();
    fetchCateringPackages();
    const id = setInterval(() => { fetchMenus(); fetchTables(); fetchOrders(); }, 8000);
    const onFocus = () => { fetchMenus(); fetchTables(); fetchOrders(); };
    window.addEventListener('focus', onFocus);
    return () => { clearInterval(id); window.removeEventListener('focus', onFocus); };
  }, [fetchMenus, fetchTables, fetchOrders, fetchCateringPackages]);

  const saveCateringPackage = useCallback((pkg) => {
    setCateringPackages(prev => {
      const exists = prev.some(x => x.dbId === pkg.dbId);
      return exists ? prev.map(x => (x.dbId === pkg.dbId ? pkg : x)) : prev.concat([pkg]);
    });
  }, []);

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

  const addMenuItem = useCallback((payload) => (
    menuRequest('/students/hotel/menus', 'POST', payload).then(data => {
      if (data && data.item) setMenus(prev => [...prev, data.item]);
      return data && data.item;
    })
  ), [menuRequest]);

  const editMenuItem = useCallback((id, patch) => (
    menuRequest('/students/hotel/menus/' + String(id).replace(/^db-/, ''), 'PATCH', patch).then(data => {
      if (data && data.item) setMenus(prev => prev.map(m => (m.id === data.item.id ? data.item : m)));
      return data && data.item;
    })
  ), [menuRequest]);

  const removeMenuItem = useCallback((id) => (
    menuRequest('/students/hotel/menus/' + String(id).replace(/^db-/, ''), 'DELETE').then(data => {
      setMenus(prev => prev.filter(m => m.id !== id));
      return data;
    })
  ), [menuRequest]);

  const addTable = useCallback((payload) => (
    menuRequest('/students/hotel/tables', 'POST', payload).then(data => {
      if (data && data.table) setTables(prev => [...prev, data.table]);
      return data && data.table;
    })
  ), [menuRequest]);

  const editTable = useCallback((id, patch) => (
    menuRequest('/students/hotel/tables/' + id, 'PATCH', patch).then(data => {
      if (data && data.table) setTables(prev => prev.map(t => (t.id === data.table.id ? data.table : t)));
      return data && data.table;
    })
  ), [menuRequest]);

  const closeTable = useCallback((id) => (
    menuRequest('/students/hotel/tables/' + id, 'PATCH', { close: true }).then(data => {
      if (data && data.table) setTables(prev => prev.map(t => (t.id === data.table.id ? data.table : t)));
      return data && data.table;
    })
  ), [menuRequest]);

  const removeTable = useCallback((id) => (
    menuRequest('/students/hotel/tables/' + id, 'DELETE').then(data => {
      setTables(prev => prev.filter(t => t.id !== id));
      return data;
    })
  ), [menuRequest]);

  const seatTable = useCallback((id) => (
    menuRequest('/students/hotel/tables/' + id, 'PATCH', { arrive: true }).then(data => {
      if (data && data.table) setTables(prev => prev.map(t => (t.id === data.table.id ? data.table : t)));
      return data && data.table;
    })
  ), [menuRequest]);

  const fetchTableBill = useCallback((id) => (
    menuRequest('/students/hotel/tables/' + id + '/bill', 'GET').then(data => data && data.bill)
  ), [menuRequest]);

  /* Settling frees the table server-side, so the row that comes back is already
     Available. The orders it billed are refetched rather than patched: the ticket
     list is what the bill was built from and must not be guessed at here. */
  const settleTable = useCallback((id, payload) => (
    menuRequest('/students/hotel/tables/' + id + '/settle', 'POST', payload).then(data => {
      if (data && data.table) setTables(prev => prev.map(t => (t.id === data.table.id ? data.table : t)));
      fetchOrders();
      return data && data.payment;
    })
  ), [menuRequest, fetchOrders]);

  const placeDineInOrder = useCallback((tableId, items) => (
    menuRequest('/students/hotel/orders', 'POST', { order_type: 'dine_in', dine_in_table_id: tableId, items }).then(data => {
      if (data && data.order) setOrders(prev => [data.order, ...prev]);
      // Stock changed underneath the menu list this order was built from.
      fetchMenus();
      return data && data.order;
    })
  ), [menuRequest, fetchMenus]);

  const updateOrderStatus = useCallback((id, status) => (
    menuRequest('/students/hotel/orders/' + id, 'PATCH', { status }).then(data => {
      if (data && data.order) setOrders(prev => prev.map(o => (o.id === data.order.id ? data.order : o)));
      if (status === 'Cancelled') fetchMenus();
      return data && data.order;
    })
  ), [menuRequest, fetchMenus]);

  return (
    <RestaurantManagementPage
      initialNav={window.HMS_RESTAURANT_INITIAL_NAV}
      cateringPackages={cateringPackages}
      canManageCatering={canManageCatering}
      onSaveCateringPackage={saveCateringPackage}
      menus={menus}
      tables={tables}
      orders={orders}
      canManageTables={canManageTables}
      onBack={() => { window.location.href = window.HMS_RESTAURANT_URL; }}
      onAddMenu={addMenuItem}
      onEditMenu={editMenuItem}
      onRemoveMenu={removeMenuItem}
      onAddTable={addTable}
      onEditTable={editTable}
      onCloseTable={closeTable}
      onRemoveTable={removeTable}
      onSeatTable={seatTable}
      onFetchBill={fetchTableBill}
      onSettleTable={settleTable}
      onPlaceOrder={placeDineInOrder}
      onUpdateOrderStatus={updateOrderStatus}
      onToast={(msg) => window.toast && window.toast(msg)}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
