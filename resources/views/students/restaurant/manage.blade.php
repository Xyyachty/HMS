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
    padding: 0.8rem 1.8rem; border: none; border-radius: 0.375rem;
    cursor: pointer; transition: background 0.2s, transform 0.2s;
  }
  .btn-primary:hover { background: var(--accent-light); transform: translateY(-1px); }
  .btn-outline {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: transparent; color: var(--accent);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 500;
    font-size: 0.75rem; letter-spacing: 0.1em; text-transform: uppercase;
    padding: 0.6rem 1.3rem; border: 1px solid var(--accent); border-radius: 0.375rem;
    cursor: pointer; transition: background 0.2s, color 0.2s, transform 0.2s;
    text-decoration: none;
  }
  .btn-outline:hover { background: var(--accent); color: var(--bg); transform: translateY(-1px); }
  .booking-input {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 0.375rem; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus { border-color: var(--accent); }
  .booking-input::placeholder { color: var(--fg-muted); opacity: 0.5; }
  .rm-row {
    display: flex; align-items: stretch; width: 100%;
    background: var(--card); border: 1px solid var(--border); border-radius: 0.875rem;
    overflow: hidden;
  }
  .rm-content { flex: 1; min-width: 0; padding: 1.25rem 1.6rem; position: relative; color: var(--fg); }
  .rm-panel { max-width: 32.5rem; }
  .rm-panel h3 { font-family: var(--font-display, 'Playfair Display', serif); font-size: 1.35rem; font-weight: 700; margin: 0 0 0.35rem; color: var(--fg); }
  .rm-panel-desc { color: var(--fg-muted); font-size: 0.82rem; margin: 0 0 1.35rem; line-height: 1.5; }
  .rm-form-grid { display: grid; gap: 0.95rem; }
  .rm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }
  @media (max-width: 640px) {
    .rm-row { flex-direction: column; }
    .rm-form-row { grid-template-columns: 1fr; }
  }
  .tb-badge {
    padding: 0.22rem 0.6rem; border-radius: 0.25rem;
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
    border: 1px solid var(--border); border-radius: 0.75rem;
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
  .order-field dt { color: var(--fg-muted); min-width: 3.625rem; flex-shrink: 0; }
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
    background: var(--card); border: 1px solid var(--border); border-radius: 0.875rem;
    width: 100%; max-width: 32.5rem; margin: auto;
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
    padding: 0.8rem 1.15rem; border-radius: 0.625rem; cursor: pointer; text-decoration: none;
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
  .mn-how li { display: flex; gap: 0.7rem; align-items: flex-start; padding: 0.85rem 0.95rem; border-radius: 0.875rem; background: var(--mn-soft); border: 1px solid var(--mn-line); }
  .mn-how-num { flex: none; width: 1.875rem; height: 1.875rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.78rem; background: var(--mn-tint); color: var(--accent); }
  .mn-how div b { display: block; font-size: 0.86rem; color: var(--fg); margin-bottom: 0.15rem; }
  .mn-how div span { display: block; font-size: 0.78rem; color: var(--fg-muted); line-height: 1.4; }

  .mn-panel { background: var(--card); border: 1px solid var(--mn-line); border-radius: 0.875rem; padding: 1.2rem 1.3rem 1.4rem; }
  .mn-panel-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .mn-panel-head h2 { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--fg); }
  .mn-panel-head p { margin: 0.25rem 0 0; font-size: 0.84rem; color: var(--fg-muted); }
  .mn-live { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--fg-muted); }
  .mn-live::before { content: ''; width: 0.4375rem; height: 0.4375rem; border-radius: 50%; background: var(--mn-ok); }

  .mn .tone-ok, .mn-modal .tone-ok       { background: color-mix(in srgb, var(--mn-ok) 16%, transparent);   color: var(--mn-ok); }
  .mn .tone-warn, .mn-modal .tone-warn   { background: color-mix(in srgb, var(--mn-warn) 16%, transparent); color: var(--mn-warn); }
  .mn .tone-brand, .mn-modal .tone-brand { background: var(--mn-tint); color: var(--accent); }

  .mn-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; margin-bottom: 1.1rem; }
  .mn-stat { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 0.95rem; border-radius: 0.75rem; border: 1px solid var(--mn-line); background: var(--mn-soft); }
  .mn-stat-icon { flex: none; width: 2.375rem; height: 2.375rem; border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; }
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
  .mn-search { position: relative; flex: 1 1 240px; max-width: 21.25rem; }
  .mn-search i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.8rem; pointer-events: none; }
  .mn-search .mn-input { padding-left: 2.3rem; }

  .mn-input {
    box-sizing: border-box; width: 100%;
    background: var(--mn-soft); border: 1px solid var(--mn-line);
    border-radius: 0.625rem; padding: 0.75rem 0.9rem; color: var(--fg);
    font: 400 0.9rem/1.4 var(--font-body, 'Outfit', sans-serif);
    outline: none; transition: border-color 0.15s;
  }
  .mn-input:focus { border-color: var(--accent); }
  .mn-input::placeholder { color: var(--fg-muted); opacity: 0.7; }
  .mn-input.has-error { border-color: var(--mn-bad); }
  textarea.mn-input { resize: vertical; min-height: 4.6rem; }

  /* Room cards: one height per row, button at the bottom. */
  .mn-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(270px, 100%), 1fr)); gap: 1rem; align-items: stretch; }
  .mn-card { min-width: 0; border: 1px solid var(--mn-line); border-radius: 0.875rem; background: var(--mn-soft); overflow: hidden; display: flex; flex-direction: column; }
  .mn-card-img { position: relative; aspect-ratio: 16 / 9; background: var(--mn-soft); overflow: hidden; }
  .mn-card-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; }
  .mn-card-img .mn-pill { position: absolute; top: 0.625rem; left: 0.625rem; background: var(--card); box-shadow: 0 2px 10px rgba(0,0,0,0.25); }
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
  .mn-next { margin: auto 0 0; display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--fg); padding: 0.55rem 0.7rem; border-radius: 0.625rem; background: var(--card); border: 1px solid var(--mn-line); }
  .mn-next i { color: var(--fg-muted); }
  .mn-more { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; margin-top: 1rem; font-size: 0.82rem; color: var(--fg-muted); }

  .mn-empty { border: 1.5px dashed var(--mn-line); border-radius: 0.875rem; padding: 2.2rem 1.5rem; text-align: center; }
  .mn-empty-icon { width: 3.5rem; height: 3.5rem; margin: 0 auto 0.9rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; background: var(--mn-tint); color: var(--accent); }
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
    box-sizing: border-box; background: var(--card); color: var(--fg); border: 1px solid var(--mn-line); border-radius: 0.875rem; width: 100%; max-width: 36.25rem; max-height: 92vh; overflow-y: auto;
  }
  .mn-modal.is-small { max-width: 28.75rem; }
  .mn-modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.35rem 0; }
  .mn-modal-head h2 { margin: 0; font-size: 1.45rem; line-height: 1.2; color: var(--fg); }
  .mn-modal-head p { margin: 0.35rem 0 0; font-size: 0.84rem; color: var(--fg-muted); line-height: 1.45; }
  .mn-close { flex: none; width: 2.125rem; height: 2.125rem; border-radius: 0.625rem; border: 1px solid var(--mn-line); background: transparent; color: var(--fg-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; }
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
  .mn-note { margin: 0; display: flex; gap: 0.55rem; align-items: flex-start; padding: 0.75rem 0.85rem; border-radius: 0.625rem; border: 1px solid var(--mn-line); background: var(--mn-soft); font-size: 0.84rem; line-height: 1.45; color: var(--fg-muted); }
  .mn-note i { margin-top: 0.2rem; }
  .mn-note.is-ok { color: var(--fg); border-color: color-mix(in srgb, var(--accent) 45%, transparent); background: var(--mn-tint); }
  .mn-note.is-ok i { color: var(--accent); }
  .mn-photos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.5rem; padding: 0; border: 0; background: none; cursor: pointer; }
  .mn-photo { position: relative; height: 5.25rem; border-radius: 0.625rem; border: 1px solid var(--mn-line); background-color: var(--mn-soft); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; color: var(--accent); }
  .mn-photo.is-empty { border-style: dashed; }
  .mn-photos:hover .mn-photo { border-color: var(--accent); }
  .mn-photo small { position: absolute; top: 0.3125rem; left: 0.3125rem; padding: 0.12rem 0.45rem; border-radius: 999px; background: rgba(0,0,0,0.65); color: #fff; font-size: 0.62rem; }
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
  .mn-photo-pick { display: block; width: 100%; padding: 0; border: 1.5px dashed var(--mn-line); border-radius: 0.625rem; background: var(--mn-soft); cursor: pointer; overflow: hidden; color: var(--fg-muted); font: inherit; }
  .mn-photo-pick:hover { border-color: var(--accent); }
  .mn-photo-pick img { width: 100%; height: 10rem; object-fit: cover; display: block; }
  .mn-photo-empty { height: 6.875rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.45rem; font-size: 0.82rem; }
  .mn-photo-empty i { font-size: 1.4rem; color: var(--accent); }
  .mn-modal { --mn-warn: var(--warn, #f59e0b); }
  @media (max-width: 560px) { .mn-row2 { grid-template-columns: 1fr; } }
  /* Orders (ord-), on top of the mn- look */
  .mn .tone-muted { background: color-mix(in srgb, var(--fg) 8%, transparent); color: var(--fg-muted); }
  .ord-types { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
  .ord-type { display: flex; align-items: center; gap: 0.8rem; text-align: left; padding: 0.95rem 1rem; border-radius: 0.875rem; border: 1px solid var(--mn-line); background: var(--card); color: var(--fg); cursor: pointer; font: inherit; transition: border-color 0.15s, background 0.15s; }
  .ord-type:hover { border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .ord-type.is-on { border-color: var(--accent); background: var(--mn-tint); box-shadow: inset 0 0 0 1px var(--accent); }
  .ord-type:focus-visible, .ord-step:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
  .ord-type-icon { flex: none; width: 2.625rem; height: 2.625rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; background: var(--mn-tint); color: var(--accent); }
  .ord-type-text { flex: 1; min-width: 0; }
  .ord-type-text b { display: block; font-size: 0.98rem; }
  .ord-type-text small { display: block; font-size: 0.76rem; color: var(--fg-muted); margin-top: 0.15rem; line-height: 1.35; }
  .ord-type-count { flex: none; font-size: 0.76rem; font-weight: 700; padding: 0.3rem 0.6rem; border-radius: 999px; background: color-mix(in srgb, var(--mn-ok) 14%, transparent); color: var(--mn-ok); white-space: nowrap; }
  .ord-type-count.has-work { background: color-mix(in srgb, var(--mn-warn) 16%, transparent); color: var(--mn-warn); }

  .ord-steps { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .ord-step { flex: 1 1 130px; display: flex; align-items: center; gap: 0.6rem; text-align: left; padding: 0.65rem 0.8rem; border-radius: 0.75rem; border: 1px solid var(--mn-line); background: var(--mn-soft); color: var(--fg); cursor: pointer; font: inherit; }
  .ord-step:hover { border-color: color-mix(in srgb, var(--accent) 50%, transparent); }
  .ord-step.is-on { border-color: var(--accent); background: var(--mn-tint); }
  .ord-step-num { flex: none; width: 1.5rem; height: 1.5rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.74rem; font-weight: 700; background: var(--card); border: 1px solid var(--mn-line); color: var(--fg-muted); }
  .ord-step-text b { display: block; font-size: 1.1rem; line-height: 1.1; font-variant-numeric: tabular-nums; }
  .ord-step-text small { display: block; font-size: 0.74rem; color: var(--fg-muted); line-height: 1.3; }

  .ord-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(320px, 100%), 1fr)); gap: 1rem; align-items: stretch; }
  .ord-card { min-width: 0; display: flex; flex-direction: column; gap: 0.8rem; padding: 1rem 1.05rem 1.05rem; border-radius: 0.875rem; border: 1px solid var(--mn-line); background: var(--mn-soft); }
  .ord-card.is-new { border-color: color-mix(in srgb, var(--mn-warn) 55%, transparent); }
  .ord-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
  .ord-sub { display: block; font-size: 0.82rem; color: var(--fg-muted); margin-top: 0.2rem; }
  .ord-track { list-style: none; margin: 0; padding: 0; display: grid; }
  .ord-track li { position: relative; display: flex; flex-direction: column; align-items: center; gap: 0.35rem; font-size: 0.66rem; color: var(--fg-muted); text-align: center; line-height: 1.2; }
  .ord-track li::before { content: ''; position: absolute; top: 0.375rem; left: -50%; width: 100%; height: 2px; background: var(--mn-line); }
  .ord-track li:first-child::before { display: none; }
  .ord-track li.is-done::before, .ord-track li.is-now::before { background: var(--accent); }
  .ord-dot { position: relative; z-index: 1; width: 0.875rem; height: 0.875rem; border-radius: 50%; background: var(--card); border: 2px solid var(--mn-line); }
  .ord-track li.is-done .ord-dot { background: var(--accent); border-color: var(--accent); }
  .ord-track li.is-now .ord-dot { border-color: var(--accent); box-shadow: 0 0 0 4px var(--mn-tint); }
  .ord-track li.is-now, .ord-track li.is-done { color: var(--fg); }
  .ord-track li.is-now { font-weight: 700; }
  .ord-when { margin: 0; display: flex; gap: 0.5rem; align-items: center; font-size: 0.82rem; color: var(--fg-muted); }
  .ord-items { list-style: none; margin: 0; padding: 0.65rem 0.75rem; border-radius: 0.625rem; background: var(--card); border: 1px solid var(--mn-line); display: grid; gap: 0.35rem; }
  .ord-items li { display: flex; gap: 0.55rem; align-items: baseline; font-size: 0.86rem; color: var(--fg); }
  .ord-qty { flex: none; min-width: 1.9rem; font-weight: 700; color: var(--accent); font-variant-numeric: tabular-nums; }
  .ord-item { flex: 1; min-width: 0; }
  .ord-line { color: var(--fg-muted); font-size: 0.8rem; font-variant-numeric: tabular-nums; }
  .ord-items .ord-total { justify-content: space-between; border-top: 1px dashed var(--mn-line); padding-top: 0.45rem; margin-top: 0.15rem; color: var(--fg-muted); }
  .ord-items .ord-total b { color: var(--fg); font-variant-numeric: tabular-nums; }
  .ord-note { margin: 0; display: flex; gap: 0.5rem; align-items: flex-start; font-size: 0.82rem; color: var(--fg); font-style: italic; }
  .ord-note i { color: var(--accent); margin-top: 0.2rem; font-style: normal; }
  .ord-actions { margin-top: auto; display: grid; gap: 0.45rem; }
  .ord-done { margin: 0; display: flex; gap: 0.5rem; align-items: center; font-size: 0.84rem; color: var(--fg-muted); padding: 0.65rem 0.75rem; border-radius: 0.625rem; background: var(--card); border: 1px solid var(--mn-line); }
  .ord-cancel { justify-self: center; color: var(--mn-bad); }

  .mn-modal.ord-modal { max-width: 47.5rem; }
  .ord-menu { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(300px, 100%), 1fr)); gap: 0.5rem; max-height: 22.5rem; overflow-y: auto; padding: 0.15rem; }
  .ord-dish { display: flex; align-items: center; gap: 0.65rem; padding: 0.5rem 0.6rem; border-radius: 0.625rem; border: 1px solid var(--mn-line); background: var(--mn-soft); }
  .ord-dish.is-on { border-color: var(--accent); background: var(--mn-tint); }
  .ord-dish.is-out { opacity: 0.55; }
  .ord-dish img { width: 3.25rem; height: 2.625rem; object-fit: cover; border-radius: 0.5rem; flex: none; }
  .ord-dish-text { flex: 1; min-width: 0; }
  .ord-dish-text b { display: block; font-size: 0.86rem; color: var(--fg); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .ord-dish-text small { display: block; font-size: 0.76rem; color: var(--fg-muted); }
  .ord-stepper { display: flex; align-items: center; gap: 0.4rem; }
  .ord-stepper button { width: 2rem; height: 2rem; border-radius: 0.5625rem; border: 1px solid var(--accent); background: var(--card); color: var(--accent); font-size: 1.05rem; line-height: 1; cursor: pointer; }
  .ord-stepper span { min-width: 1.4rem; text-align: center; font-weight: 700; color: var(--fg); }
  .ord-cart { padding: 0.75rem 0.85rem; border-radius: 0.625rem; background: var(--mn-tint); color: var(--fg); font-size: 0.9rem; }
  @media (max-width: 860px) { .ord-types { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_RESTAURANT_URL = @json(route('students.dashboard', ['section' => 'tasks']));
  window.HMS_RESTAURANT_INITIAL_NAV = @json(request()->query('nav', 'manage-menu'));
  // The top of a printed dine-in bill: the team's own hotel name, address, contact,
  // logo and design colour, from their saved customization (HotelTemplateBuilder::printIdentity).
  @php
    $printIdentity = \App\Support\HotelTemplateBuilder::printIdentity(
        $templateCustomizations ?? [], $groupName ?? null, isset($facultyId) ? (int) $facultyId : null, $selectedTemplate ?? null
    );
  @endphp
  window.HMS_HOTEL_IDENTITY = @json($printIdentity);
  window.HMS_HOTEL_NAME = window.HMS_HOTEL_IDENTITY.name;
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
        <p className={`mn-desc ${item.sub ? '' : 'is-empty'}`}>{item.sub || 'No description.'}</p>
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
            <p>{list.length ? `${list.length} ${list.length === 1 ? 'dish' : 'dishes and drinks'} on the menu.` : 'Nothing on the menu.'}</p>
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
            <h3>{q ? 'No dish matches your search' : list.length === 0 ? 'No dishes' : stockFilter === 'attention' ? 'Nothing is running low' : `Nothing in ${filter}`}</h3>
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
function DineInBillModal({ table, onFetchBill, onSettle, onClose, onToast, onReceipt }) {
  const [bill, setBill] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [method, setMethod] = useState('Cash');
  const [amount, setAmount] = useState('');
  const [reference, setReference] = useState('');
  const [busy, setBusy] = useState(false);
  // With / Without Discount: the Senior Citizen and PWD guests at the table, each
  // with a name and ID number. The server prices their share (VAT exemption + 20%).
  const [withDiscount, setWithDiscount] = useState(false);
  const [claims, setClaims] = useState([]);
  const filled = withDiscount
    ? claims.filter(c => c.full_name.trim() && c.id_number.trim())
        .map(c => ({ type: c.type, full_name: c.full_name.trim(), id_number: c.id_number.trim() }))
    : [];
  const claimsKey = JSON.stringify(filled);

  useEffect(() => {
    let alive = true;
    // Re-priced shortly after the cashier stops typing a name or ID.
    const id = setTimeout(() => {
      Promise.resolve(onFetchBill(table.id, filled))
        .then(data => {
          if (!alive) return;
          setBill(data);
          setError('');
          // Settling in full is the common case; the field stays editable for a
          // customer handing over something else.
          setAmount(String(data && data.total != null ? data.total : ''));
        })
        .catch(err => { if (alive) setError((err && err.message) || 'Could not load this bill.'); })
        .finally(() => { if (alive) setLoading(false); });
    }, bill ? 350 : 0);
    return () => { alive = false; clearTimeout(id); };
  }, [table.id, onFetchBill, claimsKey]);

  const setClaim = (i, patch) => setClaims(list => list.map((c, k) => (k === i ? Object.assign({}, c, patch) : c)));
  const addClaim = () => setClaims(list => [...list, { type: 'Senior Citizen', full_name: '', id_number: '' }]);
  const removeClaim = (i) => setClaims(list => list.filter((c, k) => k !== i));
  const money2 = (n) => '₱' + (Number(n) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape' && !busy) onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose, busy]);

  const total = bill ? Number(bill.total) || 0 : 0;
  const paid = Math.max(0, parseFloat(amount) || 0);
  const shortBy = Math.max(0, total - paid);
  const unserved = (bill && bill.unserved) || [];

  // Cash: the field is what the customer hands over; only the bill is recorded as
  // paid and the rest goes back as change. Other methods record what was typed.
  const isCash = method === 'Cash';
  const recorded = isCash ? Math.min(paid, total) : paid;
  const change = isCash ? Math.max(0, paid - total) : 0;
  const names = bill ? bill.tableName : table.name;
  const isParty = !!(bill && bill.tables && bill.tables.length > 1);

  const settle = (e) => {
    e.preventDefault();
    if (shortBy > 0 && !window.confirm(
      `${formatPeso(shortBy)} of this bill will go uncollected. Close ${isParty ? 'the tables' : 'the table'} anyway?`
    )) return;
    if (withDiscount && filled.length !== claims.length) {
      setError('Enter the name and ID number of every Senior Citizen / PWD guest, or remove the empty row.');
      return;
    }
    setBusy(true);
    Promise.resolve(onSettle(table.id, { amount_paid: recorded, method, reference: reference.trim(), discounts: filled }))
      .then((payment) => {
        if (onToast) onToast(`${names} settled — ${formatPeso(recorded)} by ${method}.`);
        // The receipt for what was just paid, one for the whole party.
        if (payment && onReceipt) onReceipt({ payment, tendered: paid, change, contactNo: table.contactNo });
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
            <h2 className="font-display" style={{ fontSize: '1.4rem', margin: 0, color: 'var(--fg)' }}>Final Bill · {names}</h2>
            <p style={{ margin: '0.3rem 0 0', color: 'var(--fg-muted)', fontSize: '0.78rem' }}>
              {table.guestName || 'Guest'} · party of {(bill && bill.partySize) || table.partySize || '—'}
              {table.contactNo ? ` · ${table.contactNo}` : ''}
            </p>
            {isParty && (
              <p style={{ margin: '0.25rem 0 0', color: 'var(--accent)', fontSize: '0.74rem' }}>
                <i className="fa-solid fa-link" style={{ fontSize: '0.65rem', marginRight: 5 }}></i>
                {bill.tables.length} tables reserved together · one bill, one payment, one receipt
              </p>
            )}
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
                  Nothing has been ordered at this table. Close it out instead of billing it.
                </p>
              ) : (
                <>
                  <p className="tb-bill-title">Ordered</p>
                  {bill.items.map((line, i) => (
                    <div className="tb-bill-line" key={line.orderId + '-' + i}>
                      <span>{line.name} × {line.qty} <span style={{ opacity: 0.55 }}>· {isParty && line.tableName ? line.tableName + ' · ' : ''}#{line.orderId}</span></span>
                      <span className="tb-bill-amt">{formatPeso(line.line)}</span>
                    </div>
                  ))}
                  {/* Senior Citizen / PWD: With or Without Discount. */}
                  <p className="tb-bill-title" style={{ marginTop: '1rem' }}>Senior Citizen / PWD</p>
                  <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap' }}>
                    {[[false, 'Without Discount'], [true, 'With Discount']].map(([on, label]) => (
                      <button key={label} type="button" aria-pressed={withDiscount === on}
                        className={withDiscount === on ? 'btn-primary' : 'btn-outline'}
                        style={{ fontSize: '0.66rem', padding: '0.4rem 0.8rem' }}
                        onClick={() => { setWithDiscount(on); if (on && claims.length === 0) addClaim(); }}>
                        {label}
                      </button>
                    ))}
                  </div>
                  {withDiscount && (
                    <div style={{ display: 'grid', gap: '0.45rem', marginTop: '0.6rem' }}>
                      {claims.map((c, i) => (
                        <div key={i} style={{ display: 'flex', flexWrap: 'wrap', gap: '0.4rem', alignItems: 'center' }}>
                          <select className="booking-input" value={c.type} onChange={e => setClaim(i, { type: e.target.value })}
                            aria-label="Discount type" style={{ padding: '0.45rem 1.6rem 0.45rem 0.5rem', fontSize: '0.76rem', flex: '1 0 8.5rem', width: 'auto' }}>
                            <option value="Senior Citizen">Senior Citizen</option>
                            <option value="PWD">PWD</option>
                          </select>
                          <input className="booking-input" placeholder="Full name" value={c.full_name}
                            onChange={e => setClaim(i, { full_name: e.target.value })} style={{ padding: '0.45rem 0.6rem', fontSize: '0.76rem', flex: '2 1 9rem', width: 'auto' }} />
                          <input className="booking-input" placeholder="ID number" value={c.id_number}
                            onChange={e => setClaim(i, { id_number: e.target.value })} style={{ padding: '0.45rem 0.6rem', fontSize: '0.76rem', flex: '1 1 7rem', width: 'auto' }} />
                          <button type="button" onClick={() => removeClaim(i)} aria-label="Remove this guest" style={toolBtnStyle('danger')}>
                            <i className="fa-solid fa-xmark" style={{ fontSize: 11 }}></i>
                          </button>
                        </div>
                      ))}
                      {claims.length < ((bill && bill.partySize) || 1) && (
                        <button type="button" onClick={addClaim}
                          style={{ justifySelf: 'start', background: 'none', border: 'none', color: 'var(--accent)', cursor: 'pointer', fontSize: '0.74rem', padding: '0.2rem 0' }}>
                          <i className="fa-solid fa-plus" style={{ fontSize: '0.62rem', marginRight: 5 }}></i> Add another Senior Citizen / PWD
                        </button>
                      )}
                      <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.7rem', lineHeight: 1.45 }}>
                        Each one's share is the food total split over the party of {(bill && bill.partySize) || '—'}; that share is VAT-exempt, then 20% off. Check the ID before giving it.
                      </p>
                    </div>
                  )}

                  <div style={{ marginTop: '0.9rem' }}>
                    <div className="tb-bill-line"><span>Subtotal</span><span className="tb-bill-amt">{money2(bill.subtotal)}</span></div>
                    {(bill.discounts || []).map((d, i) => (
                      <React.Fragment key={'d' + i}>
                        <div className="tb-bill-line"><span>Less VAT exemption · {d.type} {d.fullName}<span style={{ opacity: 0.55 }}> · on {money2(d.eligible)} share</span></span><span className="tb-bill-amt">−{money2(d.vat)}</span></div>
                        <div className="tb-bill-line"><span>Less 20% {d.type} discount · ID {d.idNumber}</span><span className="tb-bill-amt">−{money2(d.discount)}</span></div>
                      </React.Fragment>
                    ))}
                    {Number(bill.serviceCharge) > 0 && (
                      <div className="tb-bill-line"><span>Service charge ({bill.serviceChargeRate}%)<span style={{ opacity: 0.55 }}> · after discounts</span></span><span className="tb-bill-amt">{money2(bill.serviceCharge)}</span></div>
                    )}
                  </div>
                  <div className="tb-bill-line is-total">
                    <span>Total Bill</span>
                    <span className="tb-bill-amt">{money2(total)}</span>
                  </div>
                  {/* The printed Final Bill for the guest to check before paying. */}
                  {onReceipt && (
                    <button type="button" className="btn-outline" onClick={() => onReceipt({ bill })}
                      style={{ marginTop: '0.8rem', fontSize: '0.7rem', padding: '0.5rem 0.9rem' }}>
                      <i className="fa-solid fa-file-invoice" style={{ marginRight: 6 }}></i>View / Print Final Bill
                    </button>
                  )}
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
                      <label style={fieldLabel}>{isCash ? 'Cash Received' : 'Amount Paid'}</label>
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
                  {change > 0 && (
                    <p style={{ margin: '0.35rem 0 0', fontSize: '0.85rem', fontWeight: 700, color: 'var(--fg)' }}>
                      Change: {formatPeso(change)}
                    </p>
                  )}
                  {error && <p style={{ margin: '0.55rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.78rem' }}>{error}</p>}
                  <button type="submit" className="btn-primary" disabled={busy} style={{ width: '100%', marginTop: '1rem', justifyContent: 'center' }}>
                    <i className="fa-solid fa-cash-register" style={{ fontSize: '0.7rem' }}></i>
                    {busy ? 'Settling…' : (isParty ? 'Mark Paid & Close Tables' : 'Mark Paid & Close Table')}
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

/* The printed Dine-in Final Bill, in the formal hotel layout: the team's own hotel
   header and design colour (window.HMS_HOTEL_IDENTITY, from their saved
   customization), the bill details, every order line with the table it came from,
   then the subtotal, payment applied and balance due, and a guest signature line.

   Two sources, one sheet. Before payment it is drawn from the bill the server priced
   (HotelDineInBill) and the whole total is still due. After payment it is drawn from
   the payment the server recorded, which kept the same lines, so the receipt shows
   what was actually paid. Senior Citizen / PWD discounts and the service charge show
   when the bill has them; there is no tax line and no room charge. Its own <style>
   travels with it into the print frame. */
function DineInBillSheet({ bill, payment, tendered, change }) {
  const hotel = window.HMS_HOTEL_IDENTITY || { name: window.HMS_HOTEL_NAME || 'Restaurant' };
  const src = payment || bill || {};
  const items = src.items || [];
  const orderIds = (src.orderIds || []).map(Number).filter(Boolean);
  const billNo = 'BIL-' + String(orderIds.length ? Math.min(...orderIds) : (src.tableId || src.id || '')).padStart(6, '0');
  const tableNames = String(src.tableName || '').split(' + ').filter(Boolean);
  const multi = tableNames.length > 1;
  // The food, then any Senior Citizen / PWD discounts and the service charge, as the
  // server priced them. A payment from before those existed has only its total.
  const totalDue = payment ? Number(payment.totalDue) || 0 : Number(bill && bill.total) || 0;
  const subtotal = src.subtotal != null ? Number(src.subtotal) || 0 : totalDue;
  const discounts = src.discounts || [];
  const serviceCharge = Number(src.serviceCharge) || 0;
  const paid = payment ? Number(payment.amountPaid) || 0 : 0;
  const balance = payment ? Math.max(0, Number(payment.balance) || 0) : totalDue;
  const printed = new Date();
  const money = (n) => (Number(n) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const day = (iso) => {
    const d = iso ? new Date(iso) : (payment && payment.paidAt ? new Date(payment.paidAt) : printed);
    return isNaN(d) ? '' : String(d.getDate()).padStart(2, '0') + '/' + String(d.getMonth() + 1).padStart(2, '0') + '/' + d.getFullYear();
  };
  const longDay = (d) => String(d.getDate()).padStart(2, '0') + ' ' + d.toLocaleString('en-US', { month: 'short' }) + ' ' + d.getFullYear();
  const contact = [hotel.phone && 'Tel: ' + hotel.phone, hotel.email].filter(Boolean).join(' | ');
  // "Table 10" reads as 10 in the Tables row, like the paper form; any other name stays whole.
  const tableLabel = tableNames.map(n => n.replace(/^Table\s+/i, '')).join(', ');

  return (
    <div className="dn-sheet" style={{ '--ink': hotel.ink || '#1f2a44' }}>
      <style>{`
        .dn-sheet { background: #fff; color: #1a1a1a; border-radius: 10px; padding: 1.5rem 1.6rem 1.4rem; font-family: Georgia, 'Times New Roman', serif; font-size: 13.5px; line-height: 1.45; font-variant-numeric: lining-nums; }
        .dn-sheet * { box-sizing: border-box; }
        .dn-sheet .h-top { text-align: center; }
        .dn-sheet .h-logo { max-height: 56px; max-width: 160px; object-fit: contain; display: block; margin: 0 auto 0.4rem; }
        .dn-sheet .h-name { margin: 0; color: var(--ink); font-size: 1.55rem; font-weight: 700; letter-spacing: 0.02em; text-transform: uppercase; }
        .dn-sheet .h-line { margin: 0.15rem 0 0; color: #2b2b2b; }
        .dn-sheet .rule { border: 0; border-top: 2px solid var(--ink); margin: 0.8rem 0 0; }
        .dn-sheet .rule + .rule { border-top-width: 1px; margin-top: 3px; }
        .dn-sheet .title { text-align: center; color: var(--ink); font-size: 1.45rem; font-weight: 700; margin: 0.9rem 0 0.7rem; letter-spacing: 0.02em; }
        .dn-sheet .meta { display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; }
        .dn-sheet .meta dl { margin: 0; display: grid; grid-template-columns: auto auto 1fr; column-gap: 0.6rem; row-gap: 0.15rem; }
        .dn-sheet .meta dt { margin: 0; }
        .dn-sheet .meta dd { margin: 0; }
        .dn-sheet .meta .printed { white-space: nowrap; }
        .dn-sheet table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-family: Georgia, serif; }
        .dn-sheet thead th { background: color-mix(in srgb, var(--ink) 9%, #fff); color: var(--ink); font-family: Arial, sans-serif; font-size: 0.72rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; text-align: left; padding: 0.55rem 0.4rem; border-top: 2px solid var(--ink); border-bottom: 1px solid var(--ink); }
        .dn-sheet td { padding: 0.4rem 0.4rem; vertical-align: top; }
        .dn-sheet tbody tr:first-child td { padding-top: 0.7rem; }
        .dn-sheet tbody tr:last-child td { padding-bottom: 0.7rem; border-bottom: 1.5px solid var(--ink); }
        .dn-sheet .num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .dn-sheet .qty { text-align: center; }
        .dn-sheet .from { display: block; font-family: Arial, sans-serif; font-size: 0.7rem; color: #6b6b6b; }
        .dn-sheet .sum { display: flex; justify-content: flex-end; gap: 2rem; align-items: baseline; padding: 0.65rem 0.4rem 0; color: var(--ink); font-weight: 700; }
        .dn-sheet .sum b { font-size: 1.05rem; }
        .dn-sheet .adj { display: flex; justify-content: space-between; gap: 1rem; padding: 0.15rem 0.4rem 0 1.6rem; }
        .dn-sheet .adj + .sum { margin-top: 0.3rem; border-top: 1px solid color-mix(in srgb, var(--ink) 35%, #fff); }
        .dn-sheet .sec { margin: 1rem 0 0.3rem; color: var(--ink); font-weight: 700; text-transform: uppercase; letter-spacing: 0.02em; }
        .dn-sheet .pay { display: flex; justify-content: space-between; gap: 1rem; padding: 0.1rem 0.4rem 0.1rem 1.6rem; }
        .dn-sheet .pay .muted { color: #6b6b6b; }
        .dn-sheet .due { display: flex; justify-content: flex-end; gap: 2rem; align-items: baseline; margin-top: 0.6rem; padding: 0.55rem 0.4rem; border-top: 1.5px solid var(--ink); border-bottom: 1.5px solid var(--ink); color: var(--ink); font-weight: 700; }
        .dn-sheet .due b { font-size: 1.5rem; color: #1a1a1a; }
        .dn-sheet .due.is-owed b { color: #b91c1c; }
        .dn-sheet .sign { margin-top: 1.8rem; display: flex; align-items: flex-end; gap: 0.5rem; }
        .dn-sheet .sign span:last-child { flex: 0 1 18rem; border-bottom: 1px solid #1a1a1a; height: 1.2rem; }
        .dn-sheet .sign-name { margin: 0.25rem 0 0; padding-left: 9.5rem; font-style: italic; color: var(--ink); }
        @media (max-width: 520px) {
          .dn-sheet { padding: 1rem; font-size: 12.5px; }
          .dn-sheet .h-name { font-size: 1.2rem; }
          .dn-sheet .meta { flex-direction: column; }
          .dn-sheet .unit { display: none; }
          .dn-sheet .sign-name { padding-left: 0; }
        }
      `}</style>

      <div className="h-top">
        {hotel.logo ? <img className="h-logo" src={hotel.logo} alt="" /> : null}
        <h3 className="h-name">{hotel.name || 'Restaurant'}</h3>
        {hotel.address ? <p className="h-line">{hotel.address}</p> : null}
        {contact ? <p className="h-line">{contact}</p> : null}
      </div>
      <hr className="rule" /><hr className="rule" />

      <p className="title">FINAL BILL – DINE-IN</p>

      <div className="meta">
        <dl>
          <dt>Bill No.</dt><dd>:</dd><dd>{billNo}</dd>
          <dt>Guest Name</dt><dd>:</dd><dd>{src.guestName || 'Guest'}</dd>
          <dt>Party Size</dt><dd>:</dd><dd>{src.partySize ? src.partySize + (Number(src.partySize) === 1 ? ' Guest' : ' Guests') : '—'}</dd>
          <dt>{multi ? 'Tables' : 'Table'}</dt><dd>:</dd><dd>{tableLabel || '—'}</dd>
        </dl>
        <span className="printed">Date Printed: {longDay(printed)}</span>
      </div>

      <table>
        <thead>
          <tr>
            <th>Date</th><th>Description</th><th className="qty">Qty</th>
            <th className="num unit">Unit Price (PHP)</th><th className="num">Amount (PHP)</th>
          </tr>
        </thead>
        <tbody>
          {items.length === 0 ? (
            <tr><td colSpan="5" style={{ color: '#6b6b6b' }}>Nothing ordered.</td></tr>
          ) : items.map((i, k) => (
            <tr key={k}>
              <td style={{ whiteSpace: 'nowrap' }}>{day(i.placedAt)}</td>
              <td>{i.name}{multi && i.tableName ? <span className="from">{i.tableName}</span> : null}</td>
              <td className="qty">{i.qty}</td>
              <td className="num unit">{money(i.price != null ? i.price : (Number(i.line) || 0) / (Number(i.qty) || 1))}</td>
              <td className="num">{money(i.line)}</td>
            </tr>
          ))}
        </tbody>
      </table>

      <div className="sum"><span>SUBTOTAL</span><b>₱{money(subtotal)}</b></div>
      {(discounts.length > 0 || serviceCharge > 0) && (
        <>
          {discounts.map((d, k) => (
            <React.Fragment key={k}>
              <div className="adj"><span>Less: VAT exemption – {d.type} ({d.fullName}, ID {d.idNumber})</span><span className="num">-{money(d.vat)}</span></div>
              <div className="adj"><span>Less: 20% {d.type} discount</span><span className="num">-{money(d.discount)}</span></div>
            </React.Fragment>
          ))}
          {serviceCharge > 0 && (
            <div className="adj"><span>Service charge ({src.serviceChargeRate}%)</span><span className="num">{money(serviceCharge)}</span></div>
          )}
          <div className="sum"><span>TOTAL AMOUNT DUE</span><b>₱{money(totalDue)}</b></div>
        </>
      )}

      <p className="sec">Payments &amp; Credits Applied:</p>
      {payment && paid > 0 ? (
        <>
          <div className="pay">
            <span>{day(payment.paidAt)} &nbsp; {payment.method}{payment.reference ? ' · ' + payment.reference : ''}</span>
            <span className="num">-{money(paid)}</span>
          </div>
          {payment.method === 'Cash' && tendered > 0 ? (
            <div className="pay muted"><span>Cash received ₱{money(tendered)}{change > 0 ? ' · Change ₱' + money(change) : ''}</span><span></span></div>
          ) : null}
        </>
      ) : (
        <div className="pay muted"><span>None yet</span><span className="num">0.00</span></div>
      )}
      <div className="pay"><span>Payment Method</span><span>{payment ? payment.method : '—'}</span></div>

      <div className={'due' + (balance > 0 ? ' is-owed' : '')}><span>TOTAL BALANCE DUE</span><b>{money(balance)}</b></div>

      <div className="sign"><span>Guest Signature:</span><span></span></div>
      <p className="sign-name">{src.guestName || 'Guest'}</p>
    </div>
  );
}

// Prints the sheet next to the button, alone, from a hidden frame, on white.
function printDineInSheet(e) {
  const sheet = e.currentTarget.closest('.tb-modal-body').querySelector('.dn-sheet');
  const frame = document.createElement('iframe');
  frame.setAttribute('aria-hidden', 'true');
  frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
  document.body.appendChild(frame);
  frame.onload = () => { frame.contentWindow.focus(); frame.contentWindow.print(); setTimeout(() => frame.remove(), 1000); };
  frame.contentDocument.open();
  frame.contentDocument.write('<!doctype html><html><head><meta charset="utf-8"><title>Final Bill</title>'
    + '<style>@page{margin:12mm}html,body{margin:0;background:#fff}.dn-sheet{border-radius:0!important;padding:0!important}</style></head><body>'
    + sheet.outerHTML + '</body></html>');
  frame.contentDocument.close();
}

/* The Final Bill on screen, with Print. receipt is { payment, tendered, change } after
   payment; bill is the priced bill before it, for a preview the guest can check. */
function DineInReceiptModal({ receipt, bill, onClose }) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const paidNow = !!(receipt && receipt.payment);

  return (
    <div className="tb-modal-overlay" onClick={onClose} role="dialog" aria-modal="true" aria-label="Final bill">
      <div className="tb-modal" onClick={e => e.stopPropagation()} style={{ maxWidth: '46rem' }}>
        <div className="tb-modal-head">
          <div>
            <p style={{ color: paidNow ? 'var(--success, #4ade80)' : 'var(--accent)', fontSize: '0.72rem', fontWeight: 700, margin: '0 0 0.35rem' }}>
              <i className={'fa-solid ' + (paidNow ? 'fa-circle-check' : 'fa-receipt')} style={{ marginRight: 6 }}></i>{paidNow ? 'Paid' : 'Not paid yet'}
            </p>
            <h2 className="font-display" style={{ fontSize: '1.4rem', margin: 0, color: 'var(--fg)' }}>{paidNow ? 'Receipt' : 'Final Bill'}</h2>
          </div>
          <button type="button" onClick={onClose} aria-label="Close"
            style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: 'pointer', flexShrink: 0 }}>
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div className="tb-modal-body">
          <DineInBillSheet bill={bill} payment={receipt && receipt.payment}
            tendered={receipt ? receipt.tendered : 0} change={receipt ? receipt.change : 0} />
          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.6rem', marginTop: '1rem' }}>
            <button type="button" className="btn-outline" onClick={printDineInSheet}>
              <i className="fa-solid fa-print" style={{ marginRight: 6 }}></i>Print
            </button>
            <button type="button" className="btn-primary" onClick={onClose}>{paidNow ? 'Done' : 'Close'}</button>
          </div>
        </div>
      </div>
    </div>
  );
}

function ManageTablesPanel({ tables, orders, canManage, onAddTable, onEditTable, onCloseTable, onRemoveTable, onSeatTable, onToast }) {
  const [form, setForm] = useState(createEmptyTableForm);
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);
  const [editingId, setEditingId] = useState(null);
  const [editForm, setEditForm] = useState(null);

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
    // A party's tables are closed together, so the question names them all.
    const names = table.partyRef
      ? (tables || []).filter(t => t.partyRef === table.partyRef).map(t => t.name).join(' + ')
      : table.name;
    if (!hmsConfirm(held
      ? `Cancel the reservation on ${names} and free ${table.partyRef ? 'them' : 'it'} up?`
      : `Close ${names} and free ${table.partyRef ? 'them' : 'it'} up?`)) return;
    Promise.resolve(onCloseTable(table.id))
      .then(() => { if (onToast) onToast(`${names} ${table.partyRef ? 'are' : 'is'} now available.`); })
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
  // The tables reserved together with this one (its party_ref), in list order. Grouped
  // by that shared id only, never by a matching name or number.
  const partyOf = (table) => (table.partyRef && table.status !== 'Available'
    ? list.filter(t => t.partyRef === table.partyRef && t.status !== 'Available')
    : [table]);

  return (
    <div className="rm-panel" style={{ maxWidth: '100%' }}>
      <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Dine-in</p>
      <h3>Manage Tables</h3>
      <p className="rm-panel-desc">
        Add tables for Front Desk to reserve for a customer. A Reserved table is being
        held for someone who has not arrived yet — seat them here when they do, which
        is what lets the Dine-In tab in Orders take their order. Once they have been
        served, bill them in Orders, Dine-In: tables reserved together get one bill.
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
        <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', margin: 0 }}>No tables.</p>
      ) : (
        <div style={{ display: 'grid', gap: '0.6rem' }}>
          {list.map(table => {
            /* One card for a party: the guest once, every table of it together, and
               one Bill for the consolidated bill. Each table keeps its own row, status
               and orders; the card is drawn where the party's first table sits. */
            const members = partyOf(table);
            if (members.length > 1) {
              if (members[0].id !== table.id) return null;
              const lead = members[0];
              const names = members.map(t => t.name).join(' + ');
              const allSeated = members.every(t => t.status === 'Occupied');
              const anyReserved = members.some(t => t.status === 'Reserved');
              const sameStatus = members.every(t => t.status === lead.status);
              const guests = members.reduce((n, t) => n + (Number(t.partySize) || 0), 0);
              return (
                <div key={'party-' + lead.partyRef} style={{ border: '1px solid var(--accent)', borderRadius: 10, padding: '0.75rem 0.9rem' }}>
                  <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '0.75rem', flexWrap: 'wrap' }}>
                    <div style={{ minWidth: 0 }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.3rem', flexWrap: 'wrap' }}>
                        <span style={{ color: 'var(--accent)', fontSize: '0.62rem', fontWeight: 700, letterSpacing: '0.12em', textTransform: 'uppercase' }}>
                          <i className="fa-solid fa-link" style={{ fontSize: '0.6rem', marginRight: 5 }}></i>Dine-In Party
                        </span>
                        {sameStatus && <span className={`tb-badge tb-${lead.status.toLowerCase()}`}>{lead.status}</span>}
                      </div>
                      <p style={{ margin: 0, color: 'var(--fg)', fontWeight: 700 }}>{lead.guestName || 'Guest'}</p>
                      <p style={{ margin: '0.15rem 0 0', color: 'var(--fg-muted)', fontSize: '0.76rem' }}>
                        Party of {guests || '—'} · {members.length} tables · one bill
                      </p>
                      {(lead.contactNo || lead.reservedFor) && (
                        <p style={{ margin: '0.2rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem' }}>
                          {lead.contactNo ? lead.contactNo : ''}
                          {lead.contactNo && lead.reservedFor ? ' · ' : ''}
                          {lead.reservedFor ? 'booked for ' + formatBookedFor(lead.reservedFor) : ''}
                        </p>
                      )}
                      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.4rem', marginTop: '0.55rem' }}>
                        {members.map(t => (
                          <span key={t.id} style={{ display: 'inline-flex', alignItems: 'center', gap: '0.4rem', border: '1px solid var(--border)', borderRadius: 8, padding: '0.3rem 0.55rem', fontSize: '0.74rem', color: 'var(--fg)' }}>
                            <b>{t.name}</b>
                            <span style={{ color: 'var(--fg-muted)' }}>seats {t.capacity}{t.partySize ? ` · ${t.partySize} here` : ''}</span>
                            {!sameStatus && <span className={`tb-badge tb-${t.status.toLowerCase()}`}>{t.status}</span>}
                          </span>
                        ))}
                      </div>
                    </div>
                    {canManage && (
                      <div style={{ display: 'flex', gap: 6, flexShrink: 0 }}>
                        {anyReserved ? (
                          <>
                            {/* Seating one seats the whole party (server side). */}
                            <button type="button" className="btn-primary"
                              style={{ fontSize: '0.66rem', padding: '0.4rem 0.75rem' }}
                              onClick={() => seatTable(Object.assign({}, members.find(t => t.status === 'Reserved'), { name: names }))}>
                              <i className="fa-solid fa-chair" style={{ fontSize: '0.66rem' }}></i> Seat
                            </button>
                            <button type="button" title="Cancel the party's reservation and free its tables"
                              onClick={() => closeTable(lead)} style={toolBtnStyle('danger')}>
                              <i className="fa-solid fa-door-closed" style={{ fontSize: 11 }}></i>
                            </button>
                          </>
                        ) : allSeated ? (
                          <>
                            {/* Billing is in Orders > Dine-In. */}
                            <span style={{ alignSelf: 'center', color: 'var(--fg-muted)', fontSize: '0.7rem' }}>Bill in Orders</span>
                            <button type="button" title="Close the party's tables without billing" disabled={members.some(hasOpenOrder)}
                              onClick={() => closeTable(lead)} style={toolBtnStyle('danger')}>
                              <i className="fa-solid fa-door-closed" style={{ fontSize: 11 }}></i>
                            </button>
                          </>
                        ) : null}
                      </div>
                    )}
                  </div>
                </div>
              );
            }

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
                      {taken && table.partyRef && list.some(t => t.partyRef === table.partyRef && t.id !== table.id) && (
                        <p style={{ margin: '0.2rem 0 0', color: 'var(--accent)', fontSize: '0.72rem' }}>
                          <i className="fa-solid fa-link" style={{ fontSize: '0.62rem', marginRight: 5 }}></i>
                          Same party as {list.filter(t => t.partyRef === table.partyRef && t.id !== table.id).map(t => t.name).join(', ')} · seated, billed and closed together
                        </p>
                      )}
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
                            {/* Billing is the way an occupied table normally ends, and
                                it is done in Orders > Dine-In. Closing it out here
                                without one is for a party that never ordered, or a
                                ticket written off. */}
                            <span style={{ alignSelf: 'center', color: 'var(--fg-muted)', fontSize: '0.7rem' }}>Bill in Orders</span>
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
          <p style={{ margin: 0, fontSize: '0.85rem' }}>No catering packages.</p>
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
/* ── Orders: plain-language cards, one button per step ──────────────────────
   Uses the mn- look Manage Menu set up. Stored statuses, the forward-only rule
   (HotelFoodOrder::isForwardTransition) and the requests are unchanged; only
   the words and the layout are new. */

const ORDER_TYPES = [
  { key: 'room_service', label: 'Room service', icon: 'fa-bell-concierge',  hint: 'Food sent up to a guest’s room' },
  { key: 'dine_in',      label: 'Dine-in',      icon: 'fa-utensils',        hint: 'Customers eating at a table' },
  { key: 'catering',     label: 'Catering',     icon: 'fa-champagne-glasses', hint: 'Food for events in the function room' },
];

/* Each stored status, named for what is happening to the food, per order type. */
const STEP_TEXT = {
  room_service: {
    Preparing: { label: 'Being cooked', short: 'Cooking' },
    Ready: { label: 'Ready to send up', short: 'Ready', action: 'Food is ready' },
    Delivering: { label: 'On the way to the room', short: 'On the way', action: 'Send it up to the room' },
    Completed: { label: 'Delivered', short: 'Done', action: 'The guest has it' },
  },
  dine_in: {
    Preparing: { label: 'Being cooked', short: 'Cooking' },
    Ready: { label: 'Ready to serve', short: 'Ready', action: 'Food is ready' },
    Delivering: { label: 'Bringing it to the table', short: 'To table', action: 'Bring it to the table' },
    Completed: { label: 'Served', short: 'Done', action: 'Served, all done' },
  },
  catering: {
    Pending: { label: 'New, not accepted yet', short: 'New' },
    Confirmed: { label: 'Accepted', short: 'Accepted', action: 'Accept this catering' },
    Preparing: { label: 'Being cooked', short: 'Cooking', action: 'Start cooking' },
    Ready: { label: 'Food is ready', short: 'Ready', action: 'Food is ready' },
    Serving: { label: 'Being served at the event', short: 'Serving', action: 'Start serving' },
    Completed: { label: 'Event done', short: 'Done', action: 'Event is over, all done' },
  },
};

function formatEventDay(iso) {
  const d = new Date(String(iso) + 'T00:00:00');
  return Number.isNaN(d.getTime()) ? iso : d.toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
}
function orderTypeOf(o) { return o.orderType || 'room_service'; }
function isFinished(o) { return o.status === 'Completed' || o.status === 'Cancelled'; }
function stepText(o, status) {
  const map = STEP_TEXT[orderTypeOf(o)] || STEP_TEXT.room_service;
  return map[status || o.status] || { label: status || o.status, short: status || o.status };
}

function OrderTracker({ order }) {
  const flow = flowFor(orderTypeOf(order));
  const at = flow.indexOf(order.status);
  if (at < 0) return null;
  return (
    <ol className="ord-track" style={{ gridTemplateColumns: `repeat(${flow.length}, minmax(0, 1fr))` }} aria-label={`Step ${at + 1} of ${flow.length}`}>
      {flow.map((s, i) => (
        <li key={s} className={i < at || order.status === 'Completed' ? 'is-done' : i === at ? 'is-now' : ''}>
          <span className="ord-dot"></span>
          {stepText(order, s).short}
        </li>
      ))}
    </ol>
  );
}

function OrderCard({ order, table, onMove, busy }) {
  const type = orderTypeOf(order);
  const next = nextKitchenStatus(order.status, type);
  const finished = isFinished(order);
  const canCancel = !finished && (type === 'dine_in' || type === 'catering');
  const step = stepText(order);

  let title = 'Order #' + order.id;
  let sub = order.guestName || '';
  // roomNumber is the room's name ("Classic 102"); only a bare number needs "Room" in front.
  if (type === 'room_service') title = order.roomNumber ? (/^\d+$/.test(String(order.roomNumber)) ? `Room ${order.roomNumber}` : order.roomNumber) : 'Room not set';
  if (type === 'dine_in') title = (table && table.name) || 'Table';
  if (type === 'catering') { title = order.eventVenue || 'Function Room'; sub = [order.guestName, order.eventType].filter(Boolean).join(' · '); }

  const tone = order.status === 'Cancelled' ? 'tone-muted' : order.status === 'Completed' ? 'tone-ok'
    : (order.status === 'Pending' ? 'tone-warn' : 'tone-brand');

  return (
    <article className={`ord-card ${order.status === 'Pending' ? 'is-new' : ''}`}>
      <div className="ord-top">
        <div style={{ minWidth: 0 }}>
          <h3 className="mn-name">{title}</h3>
          <span className="ord-sub">{sub || 'No name given'}</span>
        </div>
        <span className={`mn-pill ${tone}`}>
          <i className={`fa-solid ${order.status === 'Cancelled' ? 'fa-ban' : order.status === 'Completed' ? 'fa-circle-check' : 'fa-fire-burner'}`}></i>
          {order.status === 'Cancelled' ? 'Cancelled' : step.label}
        </span>
      </div>

      {order.status !== 'Cancelled' ? <OrderTracker order={order} /> : null}

      {type === 'catering' ? (
        <p className="ord-when">
          <i className="fa-solid fa-calendar-day"></i>
          <span>{order.eventDate ? formatEventDay(order.eventDate) : 'Date not set'}{order.eventTime ? ` · ${order.eventTime}` : ''}{order.guestCount ? ` · ${order.guestCount} guests` : ''}</span>
        </p>
      ) : (
        <p className="ord-when">
          <i className="fa-regular fa-clock"></i>
          <span>Ordered at {formatOrderTime(order.placedAt)} · Order #{order.id}</span>
        </p>
      )}

      <ul className="ord-items" aria-label="What was ordered">
        {(order.items || []).length === 0 ? <li style={{ color: 'var(--fg-muted)' }}>No items listed</li> : null}
        {(order.items || []).map((item, i) => (
          <li key={i}>
            <span className="ord-qty">{item.qty}×</span>
            <span className="ord-item">{item.name}</span>
            <span className="ord-line">{formatPeso((Number(item.price) || 0) * (Number(item.qty) || 0))}</span>
          </li>
        ))}
        <li className="ord-total"><span>Total</span><b>{formatPeso(order.total)}</b></li>
      </ul>

      {order.eventRequests ? <p className="ord-note"><i className="fa-solid fa-comment"></i><span>{order.eventRequests}</span></p> : null}

      <div className="ord-actions">
        {!finished && next ? (
          <button type="button" className="mn-btn is-solid is-wide" disabled={busy} onClick={() => onMove(order, next)}>
            <i className="fa-solid fa-arrow-right"></i>
            {busy ? 'Saving…' : (stepText(order, next).action || ORDER_ACTION_LABEL[next] || next)}
          </button>
        ) : (
          <p className="ord-done">
            <i className={`fa-solid ${order.status === 'Cancelled' ? 'fa-ban' : 'fa-circle-check'}`}></i>
            {order.status === 'Cancelled' ? 'This order was cancelled. Nothing was charged.' : 'Finished. Nothing more to do.'}
          </p>
        )}
        {canCancel ? (
          <button type="button" className="mn-link ord-cancel" disabled={busy} onClick={() => onMove(order, 'Cancelled')}>
            <i className="fa-solid fa-xmark"></i> Cancel this order
          </button>
        ) : null}
      </div>
    </article>
  );
}

/*
 * Taking a dine-in order. The cart logic is the one the old inline form had; it now
 * lives in a dialog that opens from the Dine-in view, with the seated tables as big
 * buttons and the dishes as cards with + and −.
 */
function NewDineInOrderModal({ tables, menus, onPlaceOrder, onToast, onClose }) {
  const [tableId, setTableId] = useState('');
  const [cart, setCart] = useState({});
  const [category, setCategory] = useState('All');
  const [placing, setPlacing] = useState(false);
  const [error, setError] = useState('');
  const uid = useId();

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const seated = (tables || []).filter(t => t.status === 'Occupied');
  const menuList = (menus || []).filter(m => category === 'All' || normalizeMenuCategory(m.category) === category);

  const addToCart = (item) => {
    setError('');
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
  const cartCount = cartLines.reduce((n, l) => n + l.qty, 0);
  const cartTotal = cartLines.reduce((sum, l) => sum + (Number(l.item.price) || 0) * l.qty, 0);

  const placeOrder = () => {
    if (!tableId) { setError('Pick the table first.'); return; }
    if (!cartLines.length) { setError('Add at least one dish.'); return; }
    setPlacing(true);
    const items = cartLines.map(l => ({ menu_item_id: l.item.dbId, name: l.item.name, price: l.item.price, qty: l.qty }));
    const table = seated.find(t => String(t.id) === String(tableId));
    Promise.resolve(onPlaceOrder(tableId, items))
      .then(() => {
        if (onToast) onToast(`Order sent to the kitchen for ${table ? table.name : 'the table'}.`);
        onClose();
      })
      .catch(err => setError((err && err.message) || 'Could not send this order. Please try again.'))
      .finally(() => setPlacing(false));
  };

  return (
    <div className="mn-overlay" onClick={onClose}>
      <div className="mn-modal ord-modal" role="dialog" aria-modal="true" aria-labelledby={uid + '-t'} onClick={e => e.stopPropagation()}>
        <div className="mn-modal-head">
          <div>
            <h2 id={uid + '-t'} className="font-display">Take a dine-in order</h2>
            <p>Pick the table, add the dishes, then send it to the kitchen.</p>
          </div>
          <button type="button" className="mn-close" onClick={onClose} aria-label="Close"><i className="fa-solid fa-xmark"></i></button>
        </div>

        <div className="mn-form">
          <div className="mn-field">
            <span className="mn-label">1. Which table?</span>
            {seated.length ? (
              <div className="mn-chips" role="radiogroup" aria-label="Table">
                {seated.map(t => (
                  <button key={t.id} type="button" role="radio" aria-checked={String(tableId) === String(t.id)} className={`mn-chip ${String(tableId) === String(t.id) ? 'is-on' : ''}`} onClick={() => { setTableId(t.id); setError(''); }}>
                    <i className="fa-solid fa-chair"></i> {t.name}{t.guestName ? ` · ${t.guestName}` : ''}
                  </button>
                ))}
              </div>
            ) : (
              <p className="mn-note"><i className="fa-solid fa-circle-info"></i><span>Nobody is seated yet. In Manage Tables, press Seat on the customer's table first.</span></p>
            )}
          </div>

          <div className="mn-field">
            <span className="mn-label">2. What would they like?</span>
            <div className="mn-tabs">
              {['All', ...MENU_CATEGORIES].map(c => (
                <button key={c} type="button" aria-pressed={category === c} className={`mn-tab ${category === c ? 'is-on' : ''}`} onClick={() => setCategory(c)}>
                  {c === 'All' ? 'Everything' : c}
                </button>
              ))}
            </div>
            <div className="ord-menu">
              {menuList.length === 0 ? <p className="mn-help">Nothing in this part of the menu.</p> : null}
              {menuList.map(item => {
                const inCart = cart[item.id];
                const out = item.stock <= 0;
                return (
                  <div key={item.id} className={`ord-dish ${inCart ? 'is-on' : ''} ${out ? 'is-out' : ''}`}>
                    {/* Picked by sight, so the photo comes before the name. */}
                    <img src={menuFoodImg(item)} alt="" />
                    <div className="ord-dish-text">
                      <b>{item.name}</b>
                      <small>{formatPeso(item.price)}{out ? ' · Sold out' : ''}</small>
                    </div>
                    {out ? null : inCart ? (
                      <div className="ord-stepper">
                        <button type="button" aria-label={`One less ${item.name}`} onClick={() => removeFromCart(item.id)}>−</button>
                        <span>{inCart.qty}</span>
                        <button type="button" aria-label={`One more ${item.name}`} onClick={() => addToCart(item)}>+</button>
                      </div>
                    ) : (
                      <button type="button" className="mn-btn is-small" onClick={() => addToCart(item)}><i className="fa-solid fa-plus"></i> Add</button>
                    )}
                  </div>
                );
              })}
            </div>
          </div>

          <div className="ord-cart">
            <span>{cartCount ? `${cartCount} ${cartCount === 1 ? 'dish' : 'dishes'} · ` : 'Nothing added'}{cartCount ? <b>{formatPeso(cartTotal)}</b> : null}</span>
          </div>
          {error ? <p className="mn-error">{error}</p> : null}

          <div className="mn-actions">
            <button type="button" className="mn-btn is-quiet" onClick={onClose}>Cancel</button>
            <button type="button" className="mn-btn is-solid" disabled={placing} onClick={placeOrder}>
              <i className="fa-solid fa-paper-plane"></i> {placing ? 'Sending…' : 'Send to the kitchen'}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

const ORD_PAGE = 12;

/* Dine-in billing, in Orders: one card per seated table, or one per party of tables
   reserved together (grouped by their shared party_ref, never by name), with what it
   has ordered so far and one Bill button. The bill, the payment and the receipt are
   the existing ones (DineInBillModal, HotelDineInBill, /settle), so a party still gets
   one consolidated bill with each line naming its table. */
function DineInBillsSection({ tables, orders, canBill, onFetchBill, onSettle, onToast }) {
  const [billingId, setBillingId] = useState(null);
  const [receipt, setReceipt] = useState(null);
  const list = tables || [];

  const groups = [];
  list.filter(t => t.status === 'Occupied').forEach(t => {
    const key = t.partyRef ? 'p-' + t.partyRef : 't-' + t.id;
    let g = groups.find(x => x.key === key);
    if (!g) { g = { key, tables: [] }; groups.push(g); }
    g.tables.push(t);
  });
  // This seating's orders only: a table is reused, and the party before has paid.
  const ordersOf = (t) => (orders || []).filter(o => o.orderType === 'dine_in' && o.tableId === t.id && o.status !== 'Cancelled'
    && (!t.assignedAt || !o.placedAt || new Date(o.placedAt) >= new Date(t.assignedAt)));
  // Re-read from state so the bill follows a refresh rather than freezing at open time.
  const billingTable = billingId ? (list.find(t => t.id === billingId) || null) : null;

  return (
    <>
      <section className="mn-panel" aria-labelledby="ord-bills" style={{ marginBottom: '1rem' }}>
        <div className="mn-panel-head">
          <div>
            <h2 id="ord-bills">Bills to settle</h2>
            <p>{groups.length
              ? `${groups.length} seated ${groups.length === 1 ? 'party' : 'parties'}. Bill them once every order is served.`
              : 'Seated dine-in tables show here, ready to bill.'}</p>
          </div>
        </div>
        {groups.length > 0 && (
          <div className="ord-grid">
            {groups.map(g => {
              const lead = g.tables[0];
              const own = g.tables.flatMap(ordersOf);
              const cooking = own.filter(o => !isFinished(o)).length;
              const soFar = own.reduce((n, o) => n + (Number(o.total) || 0), 0);
              const party = g.tables.length > 1;
              return (
                <div key={g.key} className="mn-panel" style={{ padding: '0.9rem 1rem', border: party ? '1px solid var(--accent)' : undefined }}>
                  {party && (
                    <p style={{ margin: '0 0 0.3rem', color: 'var(--accent)', fontSize: '0.62rem', fontWeight: 700, letterSpacing: '0.12em', textTransform: 'uppercase' }}>
                      <i className="fa-solid fa-link" style={{ fontSize: '0.6rem', marginRight: 5 }}></i>Dine-In Party · one bill
                    </p>
                  )}
                  <p style={{ margin: 0, color: 'var(--fg)', fontWeight: 700 }}>{lead.guestName || 'Guest'}</p>
                  <p style={{ margin: '0.15rem 0 0', color: 'var(--fg-muted)', fontSize: '0.78rem' }}>
                    {g.tables.map(t => t.name).join(' + ')} · party of {g.tables.reduce((n, t) => n + (Number(t.partySize) || 0), 0) || '—'}
                  </p>
                  {party && (
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.35rem', marginTop: '0.45rem' }}>
                      {g.tables.map(t => {
                        const n = ordersOf(t).length;
                        return (
                          <span key={t.id} style={{ border: '1px solid var(--border)', borderRadius: 8, padding: '0.2rem 0.5rem', fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                            <b style={{ color: 'var(--fg)' }}>{t.name}</b> · {n} {n === 1 ? 'order' : 'orders'}
                          </span>
                        );
                      })}
                    </div>
                  )}
                  <p style={{ margin: '0.5rem 0 0', fontSize: '0.8rem', color: cooking ? 'var(--danger, #fb7185)' : 'var(--fg-muted)' }}>
                    {own.length === 0 ? 'Nothing ordered yet.'
                      : cooking ? `${cooking} of ${own.length} ${own.length === 1 ? 'order' : 'orders'} still with the kitchen.`
                        : `${own.length} ${own.length === 1 ? 'order' : 'orders'}, all served.`}
                    {own.length > 0 && <b style={{ color: 'var(--fg)', marginLeft: 6 }}>{formatPeso(soFar)}</b>}
                  </p>
                  {canBill && (
                    <button type="button" className="mn-btn is-small is-solid" style={{ marginTop: '0.7rem', width: '100%', justifyContent: 'center' }}
                      onClick={() => setBillingId(lead.id)}>
                      <i className="fa-solid fa-receipt"></i> {party ? 'Bill the party' : 'Bill ' + lead.name}
                    </button>
                  )}
                </div>
              );
            })}
          </div>
        )}
      </section>

      {billingTable && (
        <DineInBillModal
          table={billingTable}
          onFetchBill={onFetchBill}
          onSettle={onSettle}
          onClose={() => setBillingId(null)}
          onToast={onToast}
          onReceipt={setReceipt}
        />
      )}
      {receipt && (
        <DineInReceiptModal receipt={receipt.payment ? receipt : null} bill={receipt.bill} onClose={() => setReceipt(null)} />
      )}
    </>
  );
}

function OrdersPanel({ orders, tables, menus, canPlaceDineIn, onPlaceOrder, onUpdateOrderStatus, onFetchBill, onSettleTable, onToast }) {
  const [orderType, setOrderType] = useState('room_service');
  const [view, setView] = useState('todo');   // todo | done | all | a stored status
  const [shown, setShown] = useState(ORD_PAGE);
  const [busyId, setBusyId] = useState(null);
  const [taking, setTaking] = useState(false);

  const all = orders || [];
  // Explicit equality on all three. "Not dine_in" would quietly drop catering orders
  // into the Room Service view.
  const ofType = (t) => all.filter(o => (t === 'room_service' ? (o.orderType === 'room_service' || !o.orderType) : o.orderType === t));
  const typed = ofType(orderType).sort((a, b) => (a.id < b.id ? 1 : -1));
  const flow = flowFor(orderType);

  const matches = (o) => (view === 'all' ? true : view === 'todo' ? !isFinished(o) : view === 'done' ? isFinished(o) : o.status === view);
  const visible = typed.filter(matches);
  const page = visible.slice(0, shown);
  const todo = typed.filter(o => !isFinished(o)).length;

  const pickType = (t) => { setOrderType(t); setView('todo'); setShown(ORD_PAGE); };
  const pickView = (v) => { setView(v); setShown(ORD_PAGE); };
  const tableFor = (id) => (tables || []).find(t => t.id === id);

  const send = (order, status) => {
    setBusyId(order.id);
    Promise.resolve(onUpdateOrderStatus(order.id, status))
      .then(() => { if (onToast) onToast(status === 'Cancelled' ? 'Order cancelled.' : `${stepText(order, status).label}.`); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not update this order.'); })
      .finally(() => setBusyId(null));
  };

  /* Cancelling is asked first: it takes the dish off the bill and puts it back in stock. */
  const move = (order, status) => {
    if (status !== 'Cancelled') { send(order, status); return; }
    if (!window.Swal) { if (hmsConfirm('Cancel this order?')) send(order, status); return; }
    window.Swal.fire({
      title: 'Cancel this order?',
      text: 'It comes off the bill and the servings go back on the menu. You cannot undo this.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Yes, cancel it',
      cancelButtonText: 'Keep it',
      background: themeColor('--card', '#181714'),
      color: themeColor('--fg', '#f5f0e8'),
      confirmButtonColor: '#be123c',
      cancelButtonColor: '#71717a',
    }).then(r => { if (r.isConfirmed) send(order, status); });
  };

  const typeInfo = ORDER_TYPES.find(t => t.key === orderType) || ORDER_TYPES[0];
  let emptyTitle = 'Nothing to cook right now';
  let emptyText = orderType === 'dine_in'
    ? 'Dine-in orders show here once you take one for a seated table.'
    : orderType === 'catering'
      ? 'Catering shows here on its own when the Front Desk books the function room with a catering package.'
      : 'Room-service orders show here on their own when a checked-in guest orders food to their room.';
  if (typed.length && view === 'done') { emptyTitle = 'Nothing finished'; emptyText = 'Orders move here once they are delivered, served or cancelled.'; }
  else if (typed.length && view !== 'todo' && view !== 'all') { emptyTitle = `No orders at "${stepText({ orderType }, view).label}"`; emptyText = 'Pick another step to see the rest.'; }
  else if (!typed.length) emptyTitle = `No ${typeInfo.label.toLowerCase()} orders`;

  return (
    <div className="mn">
      <header className="mn-head">
        <div>
          <p className="mn-eyebrow">Restaurant</p>
          <h1 className="font-display">Orders</h1>
          <p className="mn-lead">
            Every food order the kitchen has to make. Pick the kind of order, then press the
            big button on each one as the food moves along: cooked, ready, delivered.
          </p>
        </div>
        <div className="mn-head-actions">
          {canPlaceDineIn ? (
            <button type="button" className="mn-btn is-solid" onClick={() => { pickType('dine_in'); setTaking(true); }}>
              <i className="fa-solid fa-plus"></i> Take a dine-in order
            </button>
          ) : null}
        </div>
      </header>

      <div className="ord-types" role="group" aria-label="Kind of order">
        {ORDER_TYPES.map(t => {
          const list = ofType(t.key);
          const left = list.filter(o => !isFinished(o)).length;
          return (
            <button key={t.key} type="button" aria-pressed={orderType === t.key} className={`ord-type ${orderType === t.key ? 'is-on' : ''}`} onClick={() => pickType(t.key)}>
              <span className="ord-type-icon"><i className={`fa-solid ${t.icon}`}></i></span>
              <span className="ord-type-text">
                <b>{t.label}</b>
                <small>{t.hint}</small>
              </span>
              <span className={`ord-type-count ${left ? 'has-work' : ''}`}>{left ? `${left} to do` : 'All done'}</span>
            </button>
          );
        })}
      </div>

      {orderType === 'dine_in' && (
        <DineInBillsSection tables={tables} orders={orders} canBill={canPlaceDineIn}
          onFetchBill={onFetchBill} onSettle={onSettleTable} onToast={onToast} />
      )}

      <section className="mn-panel" aria-labelledby="ord-list">
        <div className="mn-panel-head">
          <div>
            <h2 id="ord-list">{typeInfo.label} orders</h2>
            <p>{typed.length ? `${todo} still to do · ${typed.length} in all.` : typeInfo.hint + '.'}</p>
          </div>
          <span className="mn-live">Updates on its own</span>
        </div>

        {/* The steps an order goes through, with a count each; press one to see
            only those orders. */}
        <div className="ord-steps" role="group" aria-label="Show orders at a step">
          {flow.map((s, i) => {
            const n = typed.filter(o => o.status === s).length;
            return (
              <button key={s} type="button" aria-pressed={view === s} className={`ord-step ${view === s ? 'is-on' : ''}`} onClick={() => pickView(view === s ? 'todo' : s)}>
                <span className="ord-step-num">{i + 1}</span>
                <span className="ord-step-text"><b>{n}</b><small>{stepText({ orderType }, s).label}</small></span>
              </button>
            );
          })}
        </div>

        <div className="mn-toolbar">
          <div className="mn-tabs" role="group" aria-label="Show orders">
            {[['todo', 'Still to do', 'fa-fire-burner'], ['done', 'Finished', 'fa-circle-check'], ['all', 'All orders', 'fa-list']].map(([k, label, icon]) => (
              <button key={k} type="button" aria-pressed={view === k} className={`mn-tab ${view === k ? 'is-on' : ''}`} onClick={() => pickView(k)}>
                <i className={`fa-solid ${icon}`}></i>{label}
                <span className="mn-count">{typed.filter(o => (k === 'all' ? true : k === 'todo' ? !isFinished(o) : isFinished(o))).length}</span>
              </button>
            ))}
          </div>
          {orderType === 'dine_in' && canPlaceDineIn ? (
            <button type="button" className="mn-btn is-small" onClick={() => setTaking(true)}>
              <i className="fa-solid fa-plus"></i> Take a dine-in order
            </button>
          ) : null}
        </div>

        {visible.length === 0 ? (
          <div className="mn-empty">
            <div className="mn-empty-icon"><i className={`fa-solid ${view === 'done' ? 'fa-circle-check' : typeInfo.icon}`}></i></div>
            <h3>{emptyTitle}</h3>
            <p>{emptyText}</p>
            {orderType === 'dine_in' && canPlaceDineIn && view !== 'done' ? (
              <button type="button" className="mn-btn is-solid" onClick={() => setTaking(true)}>
                <i className="fa-solid fa-plus"></i> Take a dine-in order
              </button>
            ) : null}
          </div>
        ) : (
          <>
            <div className="ord-grid">
              {page.map(order => (
                <OrderCard key={order.id} order={order} table={tableFor(order.tableId)} onMove={move} busy={busyId === order.id} />
              ))}
            </div>
            <div className="mn-more">
              <span>Showing {page.length} of {visible.length}</span>
              {page.length < visible.length ? (
                <button type="button" className="mn-btn is-small" onClick={() => setShown(s => s + ORD_PAGE)}>
                  <i className="fa-solid fa-chevron-down"></i> Show {Math.min(ORD_PAGE, visible.length - page.length)} more
                </button>
              ) : null}
            </div>
          </>
        )}
      </section>

      {taking ? (
        <NewDineInOrderModal tables={tables} menus={menus} onPlaceOrder={onPlaceOrder} onToast={onToast} onClose={() => setTaking(false)} />
      ) : null}
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

  // Orders draws its own header, kinds of order and cards, like Manage Menu below.
  if (activeNav === 'orders') {
    return (
      <div style={{ padding: '1.5rem' }} data-hms-no-edit="1">
        <OrdersPanel
          orders={orders}
          tables={tables}
          menus={menus}
          canPlaceDineIn={canManageTables}
          onPlaceOrder={onPlaceOrder}
          onUpdateOrderStatus={onUpdateOrderStatus}
          onFetchBill={onFetchBill}
          onSettleTable={onSettleTable}
          onToast={onToast}
        />
      </div>
    );
  }

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

  // Every table a call changed: a party's other tables move with the one clicked.
  const mergeTables = useCallback((data) => {
    const changed = [...((data && Array.isArray(data.tables)) ? data.tables : []), ...((data && data.table) ? [data.table] : [])];
    if (changed.length) setTables(prev => prev.map(t => changed.find(c => c.id === t.id) || t));
  }, []);

  const closeTable = useCallback((id) => (
    menuRequest('/students/hotel/tables/' + id, 'PATCH', { close: true }).then(data => {
      mergeTables(data);
      return data && data.table;
    })
  ), [menuRequest, mergeTables]);

  const removeTable = useCallback((id) => (
    menuRequest('/students/hotel/tables/' + id, 'DELETE').then(data => {
      setTables(prev => prev.filter(t => t.id !== id));
      return data;
    })
  ), [menuRequest]);

  const seatTable = useCallback((id) => (
    menuRequest('/students/hotel/tables/' + id, 'PATCH', { arrive: true }).then(data => {
      mergeTables(data);
      return data && data.table;
    })
  ), [menuRequest, mergeTables]);

  // discounts: the Senior Citizen / PWD guests entered so far, priced by the server.
  const fetchTableBill = useCallback((id, discounts) => (
    menuRequest('/students/hotel/tables/' + id + '/bill'
      + (discounts && discounts.length ? '?discounts=' + encodeURIComponent(JSON.stringify(discounts)) : ''), 'GET')
      .then(data => data && data.bill)
  ), [menuRequest]);

  /* Settling frees the table server-side, so the row that comes back is already
     Available. The orders it billed are refetched rather than patched: the ticket
     list is what the bill was built from and must not be guessed at here. */
  const settleTable = useCallback((id, payload) => (
    menuRequest('/students/hotel/tables/' + id + '/settle', 'POST', payload).then(data => {
      mergeTables(data);
      fetchOrders();
      return data && data.payment;
    })
  ), [menuRequest, fetchOrders, mergeTables]);

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
