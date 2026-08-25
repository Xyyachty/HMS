{{-- Shared report chrome: tiles, tables, tabs, period presets and the
     Template 2 palette swap. Front Desk reports the whole hotel and
     Restaurant reports its own two order types, so the styling lives here
     rather than being kept in step in two files. --}}
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }
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
  .rp-tile {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 0.9rem 1.05rem;
  }
  .rp-tile-label {
    display: block; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--fg-muted); margin-bottom: 0.35rem;
  }
  .rp-tile-value {
    font-family: var(--font-display, 'Playfair Display', serif); font-size: 1.5rem; font-weight: 700; color: var(--fg);
  }
  .rp-tile-sub { display: block; font-size: 0.66rem; color: var(--fg-muted); margin-top: 0.25rem; }
  .rp-tile-grand {
    border-color: var(--accent);
    background: linear-gradient(135deg, rgba(201,168,76,0.1), var(--card) 60%);
  }
  .rp-table { width: 100%; border-collapse: collapse; font-family: var(--font-body, 'Outfit', sans-serif); }
  .rp-table th {
    padding: 0.6rem 0.85rem; font-size: 0.62rem; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase; color: var(--fg-muted);
    border-bottom: 1px solid var(--border); white-space: nowrap;
    text-align: left; background: rgba(255,255,255,0.02);
  }
  .rp-table td {
    padding: 0.7rem 0.85rem; font-size: 0.8rem; color: var(--fg-muted);
    border-bottom: 1px solid rgba(42,38,33,0.5); vertical-align: middle; white-space: nowrap;
  }
  .rp-table tfoot td {
    border-top: 1px solid var(--border); border-bottom: none;
    color: var(--fg); font-weight: 600; background: rgba(255,255,255,0.02);
  }
  .rp-strong { color: var(--fg); font-weight: 600; }
  .rp-money { color: var(--accent-light); font-family: var(--font-display, 'Playfair Display', serif); font-weight: 700; }
  .rp-muted-money { color: var(--fg-muted); font-family: var(--font-display, 'Playfair Display', serif); font-weight: 600; }
  .rp-zero { color: var(--fg-muted); opacity: 0.8; }
  .rp-num { text-align: right; font-variant-numeric: tabular-nums; }
  .rp-note { font-size: 0.72rem; color: var(--fg-muted); font-style: italic; margin: 0.6rem 0 0; }

  .rp-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.1rem; }
  .rp-tab {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.55rem 1.1rem; border-radius: 999px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600; font-size: 0.68rem;
    letter-spacing: 0.06em; text-transform: uppercase; cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
  }
  .rp-tab:hover { color: var(--fg); }
  .rp-tab.is-active { border-color: var(--accent); background: var(--accent); color: var(--bg); }
  .rp-tab-count { font-size: 0.62rem; opacity: 0.75; margin-left: 0.15rem; }

  .rp-presets { display: flex; gap: 0.4rem; flex-wrap: wrap; }
  .rp-preset {
    padding: 0.45rem 0.85rem; border-radius: 999px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600; font-size: 0.62rem;
    letter-spacing: 0.05em; text-transform: uppercase; cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s; white-space: nowrap;
  }
  .rp-preset:hover { color: var(--fg); }
  .rp-preset.is-active {
    border-color: var(--accent); background: rgba(201,168,76,0.18); color: var(--accent-light);
  }

  .rp-badge {
    display: inline-block; padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.62rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
    border: 1px solid transparent;
  }
  .rp-cat-room { background: rgba(201,168,76,0.18); color: var(--accent-light, #e2cc7a); border-color: rgba(201,168,76,0.35); }
  .rp-cat-dinein { background: rgba(56,189,248,0.18); color: #38bdf8; border-color: rgba(56,189,248,0.35); }
  .rp-cat-service { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Additive only — nothing above this block is touched, so a Template 1
     team (or one that hasn't chosen a template yet) renders unchanged. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d;
  }
  /* rgba() can't read a hex custom property's channels, so the accent-tinted
     fills below (originally rgba(201,168,76,X) — Template 1's gold accent as
     RGB) get an explicit forest-green (27,67,50) companion. */
  :root[data-ops-theme="2"] .rp-tile-grand { background: linear-gradient(135deg, rgba(27,67,50,0.08), var(--card) 60%); }
  :root[data-ops-theme="2"] .rp-preset.is-active { background: rgba(27,67,50,0.1); }
  :root[data-ops-theme="2"] .rp-cat-room { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
  :root[data-ops-theme="2"] .rp-cat-dinein { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
  :root[data-ops-theme="2"] .rp-cat-service { background: #f3e8ff; color: #7e22ce; border-color: #e9d5ff; }
  :root[data-ops-theme="2"] .rp-table th,
  :root[data-ops-theme="2"] .rp-table tfoot td,
  :root[data-ops-theme="2"] .booking-input { background: rgba(27,67,50,0.04); }
  :root[data-ops-theme="2"] .rp-table td { border-bottom-color: var(--border); }
</style>
