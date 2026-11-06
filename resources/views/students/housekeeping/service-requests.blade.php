@extends('students.builder.ops-shell')

@section('page-title', 'Service Requests')

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

  /* Everything below reads the shell's tokens, the same way Room Inspections
     does, so the page follows Template 1, Template 2 and a team's own site
     colours. Shape rule: pills for status, 10px for buttons and fields, 14px
     for panels and cards. */
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }

  .ao {
    --ao-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --ao-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --ao-line: var(--border);
    --ao-ok: var(--success, #4ade80);
    --ao-warn: var(--warn, #f59e0b);
    --ao-bad: var(--danger, #fb7185);
    --ao-out: var(--accent);
    padding: 1.5rem 1.5rem 3rem;
    color: var(--fg);
  }

  /* Template 2's accent is green like "ready", so lent-out items take its copper. */
  :root[data-ops-theme="2"] .ao { --ao-out: #c17849; }

  /* Page header */
  .ao-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.4rem; }
  .ao-eyebrow { color: var(--accent); font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; margin: 0 0 0.5rem; }
  .ao-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .ao-lead { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 64ch; line-height: 1.5; }
  .ao-head-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

  .ao-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem;
    font: 600 0.88rem/1.15 var(--font-body, 'Outfit', sans-serif);
    padding: 0.8rem 1.15rem; border-radius: 0.625rem; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); background: transparent; color: var(--accent);
    transition: background 0.15s, transform 0.1s, filter 0.15s;
  }
  .ao-btn:hover { background: var(--ao-tint); }
  .ao-btn:active { transform: translateY(1px); }
  .ao-btn.is-solid { background: var(--accent); color: var(--bg); }
  .ao-btn.is-solid:hover { filter: brightness(1.08); }
  .ao-btn.is-quiet { border-color: var(--ao-line); color: var(--fg-muted); }
  .ao-btn.is-quiet:hover { color: var(--fg); background: var(--ao-soft); }
  .ao-btn.is-wide { width: 100%; }
  .ao-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; filter: none; }
  .ao-btn:focus-visible, .ao-photo-pick:focus-visible, .ao-link:focus-visible, .ao-close:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  /* How it works */
  .ao-how { list-style: none; margin: 0 0 1.25rem; padding: 0; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
  .ao-how li { display: flex; gap: 0.7rem; align-items: flex-start; padding: 0.85rem 0.95rem; border-radius: 0.875rem; background: var(--ao-soft); border: 1px solid var(--ao-line); }
  .ao-how-num { flex: none; width: 1.75rem; height: 1.75rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.82rem; background: var(--ao-tint); color: var(--accent); }
  .ao-how div b { display: block; font-size: 0.86rem; color: var(--fg); margin-bottom: 0.15rem; }
  .ao-how div span { display: block; font-size: 0.78rem; color: var(--fg-muted); line-height: 1.4; }

  .ao-banner { display: flex; gap: 0.6rem; align-items: flex-start; margin: 0 0 1.25rem; padding: 0.85rem 1rem; border-radius: 0.875rem; border: 1px solid color-mix(in srgb, var(--ao-warn) 40%, transparent); background: color-mix(in srgb, var(--ao-warn) 10%, transparent); font-size: 0.86rem; color: var(--fg); }
  .ao-banner i { color: var(--ao-warn); margin-top: 0.15rem; }

  /* Panel */
  .ao-panel { background: var(--card); border: 1px solid var(--ao-line); border-radius: 0.875rem; padding: 1.2rem 1.3rem 1.4rem; }
  .ao-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .ao-panel-head h2 { margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--fg); }
  .ao-panel-head p { margin: 0.2rem 0 0; font-size: 0.82rem; color: var(--fg-muted); }
  .ao-live { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--fg-muted); }
  .ao-live::before { content: ''; width: 0.4375rem; height: 0.4375rem; border-radius: 50%; background: var(--ao-ok); }

  /* Totals */
  .ao-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem; margin-bottom: 1.1rem; }
  .ao-stat { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 0.95rem; border-radius: 0.75rem; border: 1px solid var(--ao-line); background: var(--ao-soft); }
  .ao-stat-icon { flex: none; width: 2.375rem; height: 2.375rem; border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; }
  .ao-stat div b { display: block; font-size: 1.35rem; line-height: 1.1; font-variant-numeric: tabular-nums; color: var(--fg); }
  .ao-stat div span { display: block; font-size: 0.78rem; color: var(--fg-muted); }
  .tone-ok    { background: color-mix(in srgb, var(--ao-ok) 16%, transparent);   color: var(--ao-ok); }
  .tone-guest { background: var(--ao-tint); color: var(--accent); }
  .tone-warn  { background: color-mix(in srgb, var(--ao-warn) 16%, transparent); color: var(--ao-warn); }
  .tone-bad   { background: color-mix(in srgb, var(--ao-bad) 14%, transparent);  color: var(--ao-bad); }

  .ao-search { position: relative; margin-bottom: 1.1rem; }
  .ao-search i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.8rem; pointer-events: none; }
  .ao-search .ao-input { padding-left: 2.3rem; }

  .ao-input {
    box-sizing: border-box; width: 100%;
    background: var(--ao-soft); border: 1px solid var(--ao-line);
    border-radius: 0.625rem; padding: 0.75rem 0.9rem; color: var(--fg);
    font: 400 0.9rem/1.4 var(--font-body, 'Outfit', sans-serif);
    outline: none; transition: border-color 0.15s;
  }
  .ao-input:focus { border-color: var(--accent); }
  .ao-input::placeholder { color: var(--fg-muted); opacity: 0.7; }
  .ao-input.has-error { border-color: var(--ao-bad); }

  /* Item cards */
  .ao-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(270px, 100%), 1fr)); gap: 1rem; }
  .ao-card { min-width: 0; border: 1px solid var(--ao-line); border-radius: 0.875rem; background: var(--ao-soft); overflow: hidden; display: flex; flex-direction: column; }
  .ao-card-img { position: relative; aspect-ratio: 16 / 9; background: var(--ao-soft); }
  .ao-card-img { overflow: hidden; }
  .ao-card-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; }
  .ao-card-img .ao-pill { position: absolute; top: 0.625rem; left: 0.625rem; box-shadow: 0 2px 10px rgba(0,0,0,0.25); }
  .ao-card-body { padding: 0.95rem 1.05rem 1.05rem; display: flex; flex-direction: column; gap: 0.8rem; flex: 1; }
  .ao-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
  .ao-name { margin: 0; font-size: 1.08rem; font-weight: 700; color: var(--fg); line-height: 1.25; overflow-wrap: anywhere; }
  .ao-price { text-align: right; flex: none; }
  .ao-price b { display: block; font-size: 1.05rem; color: var(--fg); font-variant-numeric: tabular-nums; }
  .ao-price small { display: block; font-size: 0.7rem; color: var(--fg-muted); }
  .ao-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.76rem; font-weight: 600; white-space: nowrap; }
  .ao-card-img .ao-pill.tone-ok, .ao-card-img .ao-pill.tone-warn, .ao-card-img .ao-pill.tone-bad { background: var(--card); }

  .ao-stock { display: grid; gap: 0.45rem; }
  .ao-stock-line { display: flex; justify-content: space-between; gap: 0.5rem; font-size: 0.82rem; color: var(--fg-muted); }
  .ao-stock-line b { color: var(--fg); font-variant-numeric: tabular-nums; }
  .ao-bar { height: 0.5rem; border-radius: 999px; background: color-mix(in srgb, var(--fg) 8%, transparent); overflow: hidden; display: flex; }
  .ao-bar span { display: block; height: 100%; }
  .ao-bar .is-free { background: var(--ao-ok); }
  .ao-bar .is-out { background: var(--ao-out); }
  .ao-key { display: flex; flex-wrap: wrap; gap: 0.3rem 0.9rem; font-size: 0.74rem; color: var(--fg-muted); }
  .ao-key span { display: inline-flex; align-items: center; gap: 0.35rem; }
  .ao-key i { width: 0.5rem; height: 0.5rem; border-radius: 50%; }
  .ao-card-foot { margin-top: auto; }

  /* Empty / loading */
  .ao-empty { border: 1.5px dashed var(--ao-line); border-radius: 0.875rem; padding: 2.4rem 1.5rem; text-align: center; }
  .ao-empty-icon { width: 3.5rem; height: 3.5rem; margin: 0 auto 0.9rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; background: var(--ao-tint); color: var(--accent); }
  .ao-empty h2 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .ao-empty p { margin: 0.4rem auto 0; max-width: 50ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }
  .ao-empty .ao-btn { margin-top: 1.1rem; }
  .ao-skel { height: 18.75rem; border-radius: 0.875rem; border: 1px solid var(--ao-line); background: linear-gradient(90deg, var(--ao-soft) 0%, color-mix(in srgb, var(--fg) 8%, transparent) 50%, var(--ao-soft) 100%); background-size: 200% 100%; animation: ao-shimmer 1.4s ease-in-out infinite; }
  @keyframes ao-shimmer { from { background-position: 100% 0; } to { background-position: -100% 0; } }
  @media (prefers-reduced-motion: reduce) { .ao-skel { animation: none; } }

  /* Add / edit dialog */
  .ao-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; padding: 1.25rem; z-index: 200; }
  .ao-modal { box-sizing: border-box; background: var(--card); color: var(--fg); border: 1px solid var(--ao-line); border-radius: 0.875rem; width: 100%; max-width: 32.5rem; max-height: 92vh; overflow-y: auto; }
  .ao-modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.35rem 0; }
  .ao-modal-head h2 { margin: 0; font-size: 1.45rem; line-height: 1.2; color: var(--fg); }
  .ao-modal-head p { margin: 0.35rem 0 0; font-size: 0.84rem; color: var(--fg-muted); line-height: 1.45; }
  .ao-close { flex: none; width: 2.125rem; height: 2.125rem; border-radius: 0.625rem; border: 1px solid var(--ao-line); background: transparent; color: var(--fg-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; }
  .ao-close:hover { color: var(--fg); background: var(--ao-soft); }
  .ao-form { padding: 1.1rem 1.35rem 1.35rem; display: grid; gap: 1rem; }
  .ao-field { display: grid; gap: 0.4rem; align-content: start; }
  .ao-label { font-size: 0.86rem; font-weight: 600; color: var(--fg); }
  .ao-label em { font-style: normal; font-weight: 400; color: var(--fg-muted); }
  .ao-help { margin: 0; font-size: 0.76rem; color: var(--fg-muted); line-height: 1.45; }
  .ao-error { margin: 0; font-size: 0.78rem; color: var(--ao-bad); }
  .ao-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }
  .ao-money { position: relative; }
  .ao-money span { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.9rem; pointer-events: none; }
  .ao-money .ao-input { padding-left: 1.8rem; }
  .ao-photo-pick { display: block; width: 100%; padding: 0; border: 1.5px dashed var(--ao-line); border-radius: 0.625rem; background: var(--ao-soft); cursor: pointer; overflow: hidden; color: var(--fg-muted); font: inherit; }
  .ao-photo-pick:hover { border-color: var(--accent); }
  .ao-photo-pick img { width: 100%; height: 9.375rem; object-fit: cover; display: block; }
  .ao-photo-empty { height: 6.875rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.45rem; font-size: 0.82rem; }
  .ao-photo-empty i { font-size: 1.4rem; color: var(--accent); }
  .ao-link { background: none; border: 0; padding: 0.2rem 0; cursor: pointer; color: var(--fg-muted); font: 500 0.78rem/1 var(--font-body, 'Outfit', sans-serif); justify-self: start; }
  .ao-link:hover { color: var(--fg); text-decoration: underline; }
  .ao-note { margin: 0; padding: 0.7rem 0.8rem; border-radius: 0.625rem; font-size: 0.8rem; line-height: 1.45; display: flex; gap: 0.5rem; align-items: flex-start; color: var(--fg); }
  .ao-note.tone-warn i { color: var(--ao-warn); margin-top: 0.15rem; }
  .ao-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
  .ao-actions .ao-btn { flex: 1 1 auto; }

  @media (max-width: 960px) {
    .ao-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  @media (max-width: 680px) {
    .ao-how { grid-template-columns: 1fr; }
  }
  @media (max-width: 560px) {
    .ao { padding: 1.1rem 1rem 2.5rem; }
    .ao-stats, .ao-row { grid-template-columns: 1fr; }
    .ao-head-actions, .ao-head-actions .ao-btn { width: 100%; }
  }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
@php
  // What Maintenance repairs: the complaint categories a defect found on the job can be filed under.
  $srDefectCategories = array_keys(array_filter(\App\Models\HotelComplaint::CATEGORY_DEPARTMENTS, fn ($d) => $d === 'maintenance'));
@endphp
<script>
  window.HMS_SERVICE_REQUESTS = {
    indexUrl: @json(route('students.hotel.service-requests.index')),
    defectCategories: @json($srDefectCategories),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef, useMemo } = React;

const CONFIG = window.HMS_SERVICE_REQUESTS || {};

function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : '';
}

function send(url, method, body) {
  return fetch(url, {
    method, credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
    body: body ? JSON.stringify(body) : undefined,
  }).then(r => r.json().catch(() => ({})).then(d => (r.ok ? d : Promise.reject(d))));
}

function when(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  return isNaN(d) ? '' : d.toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

// The status and the one button that moves a request on, in Housekeeping's words.
const STEP = {
  'Pending':     { tone: 'tone-warn',  icon: 'fa-hourglass-half', next: 'Accepted',    action: 'Accept',        actionIcon: 'fa-hand' },
  'Accepted':    { tone: 'tone-guest', icon: 'fa-hand',           next: 'In Progress', action: 'Start the job', actionIcon: 'fa-broom' },
  'In Progress': { tone: 'tone-guest', icon: 'fa-broom',          next: 'Completed',   action: 'Mark as done',  actionIcon: 'fa-circle-check' },
  'Completed':   { tone: 'tone-ok',    icon: 'fa-circle-check' },
  'Cancelled':   { tone: 'tone-bad',   icon: 'fa-ban' },
};
const FLOW = ['Pending', 'Accepted', 'In Progress', 'Completed'];

// SweetAlert draws outside the page's CSS, so it gets the live token values.
function themeColor(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}
function toast(text, icon) {
  if (!window.Swal) return;
  window.Swal.fire({
    toast: true, position: 'top-end', timer: 2600, showConfirmButton: false, icon: icon || 'success', title: text,
    background: themeColor('--card', '#181714'), color: themeColor('--fg', '#f5f0e8'),
  });
}

/* A defect found on the job (a broken light, a leaking tap) goes to Maintenance as a
   complaint for the room; "room cannot be used" also takes it out of order. */
function DefectModal({ request, onClose, onSent }) {
  const cats = CONFIG.defectCategories || [];
  const [category, setCategory] = useState(cats[0] || 'Other Maintenance Problems');
  const [details, setDetails] = useState('');
  const [unusable, setUnusable] = useState(false);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const submit = (e) => {
    e.preventDefault();
    setBusy(true);
    setError('');
    send(CONFIG.indexUrl + '/' + request.id + '/defect', 'POST', { category, details: details.trim(), unusable })
      .then(d => { onSent(d.request); onClose(); toast('Sent to Maintenance'); })
      .catch(err => setError((err && err.message) || 'Could not send it to Maintenance.'))
      .finally(() => setBusy(false));
  };

  return (
    <div className="ao-overlay" onClick={onClose}>
      <div className="ao-modal" role="dialog" aria-modal="true" aria-label="Report a defect" onClick={e => e.stopPropagation()}>
        <div className="ao-modal-head">
          <div>
            <h2 className="font-display">Report a defect</h2>
            <p>Something broken in {request.roomName || 'the room'}? Maintenance gets it in their list.</p>
          </div>
          <button type="button" className="ao-close" onClick={onClose} aria-label="Close"><i className="fa-solid fa-xmark"></i></button>
        </div>
        <form className="ao-form" onSubmit={submit}>
          <div className="ao-field">
            <label className="ao-label">What is wrong?</label>
            <select className="ao-input" value={category} onChange={e => setCategory(e.target.value)}>
              {cats.map(c => <option key={c} value={c}>{c}</option>)}
            </select>
          </div>
          <div className="ao-field">
            <label className="ao-label">Details <em>(optional)</em></label>
            <textarea className="ao-input" rows="3" value={details} onChange={e => setDetails(e.target.value)} placeholder="Example: the bathroom light flickers and goes out." />
          </div>
          <label className="ao-label" style={{ display: 'flex', gap: '0.5rem', alignItems: 'center', fontWeight: 400 }}>
            <input type="checkbox" checked={unusable} onChange={e => setUnusable(e.target.checked)} />
            The room cannot be used until it is fixed (marks it Maintenance)
          </label>
          {error ? <p className="ao-error">{error}</p> : null}
          <div className="ao-actions">
            <button type="button" className="ao-btn is-quiet" onClick={onClose}>Cancel</button>
            <button type="submit" className="ao-btn is-solid" disabled={busy}>
              <i className="fa-solid fa-screwdriver-wrench"></i>{busy ? 'Sending…' : 'Send to Maintenance'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function RequestCard({ request, canManage, busy, onMove, onDefect }) {
  const step = STEP[request.status] || STEP.Pending;
  const at = FLOW.indexOf(request.status);
  const open = at >= 0 && at < 3;

  return (
    <article className="ao-card">
      <div className="ao-card-body">
        <div className="ao-card-top">
          <div style={{ minWidth: 0 }}>
            <h3 className="ao-name">{request.type}</h3>
            <p className="ao-help" style={{ marginTop: '0.2rem' }}>
              <b style={{ color: 'var(--fg)' }}>{request.roomName || 'Room'}</b> · {request.guestName || 'Guest'}
            </p>
          </div>
          <span className={`ao-pill ${step.tone}`}><i className={`fa-solid ${step.icon}`}></i>{request.status}</span>
        </div>

        <div className="ao-stock">
          {request.preferredTime ? <div className="ao-stock-line"><span>When</span><b>{request.preferredTime}</b></div> : null}
          {request.notes ? <div className="ao-stock-line"><span>Note</span><b style={{ textAlign: 'right', overflowWrap: 'anywhere' }}>{request.notes}</b></div> : null}
          <div className="ao-stock-line"><span>Asked by</span><b>{request.source === 'Guest' ? 'Guest (website)' : 'Front Desk'} · {when(request.createdAt)}</b></div>
          {request.handledBy ? <div className="ao-stock-line"><span>Handled by</span><b>{request.handledBy}</b></div> : null}
          {request.completedAt ? <div className="ao-stock-line"><span>Done</span><b>{when(request.completedAt)}</b></div> : null}
          {request.completionNotes ? <div className="ao-stock-line"><span>Done note</span><b style={{ textAlign: 'right' }}>{request.completionNotes}</b></div> : null}
          {request.complaintId ? <div className="ao-stock-line"><span>Defect</span><b>Sent to Maintenance</b></div> : null}
        </div>

        {/* Where the request is along Pending > Accepted > In Progress > Completed. */}
        {request.status !== 'Cancelled' ? (
          <div className="ao-bar" role="img" aria-label={`Step ${Math.max(1, at + 1)} of 4`}>
            <span className="is-free" style={{ width: (Math.max(0, at) / 3) * 100 + '%' }}></span>
          </div>
        ) : null}

        {canManage && open ? (
          <div className="ao-card-foot" style={{ display: 'grid', gap: '0.5rem' }}>
            <button type="button" className="ao-btn is-solid is-wide" disabled={busy} onClick={() => onMove(request, step.next)}>
              <i className={`fa-solid ${step.actionIcon}`}></i> {step.action}
            </button>
            <div style={{ display: 'flex', gap: '0.5rem' }}>
              {!request.complaintId ? (
                <button type="button" className="ao-btn is-quiet" style={{ flex: 1 }} disabled={busy} onClick={() => onDefect(request)}>
                  <i className="fa-solid fa-screwdriver-wrench"></i> Report a defect
                </button>
              ) : null}
              {request.status !== 'In Progress' ? (
                <button type="button" className="ao-btn is-quiet" style={{ flex: 1 }} disabled={busy} onClick={() => onMove(request, 'Cancelled')}>
                  <i className="fa-solid fa-ban"></i> Cancel
                </button>
              ) : null}
            </div>
          </div>
        ) : null}
      </div>
    </article>
  );
}

function App() {
  const [requests, setRequests] = useState([]);
  const [canManage, setCanManage] = useState(false);
  const [loading, setLoading] = useState(true);
  const [failed, setFailed] = useState(false);
  const [view, setView] = useState('todo');
  const [busyId, setBusyId] = useState(null);
  const [defectFor, setDefectFor] = useState(null);
  const pendingWrites = useRef(0);

  const load = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(CONFIG.indexUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => { setRequests(d.requests || []); setCanManage(!!d.can_manage); setFailed(false); })
      .catch(() => setFailed(true))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    load();
    const id = setInterval(() => { if (!document.hidden) load(); }, 8000);
    window.addEventListener('focus', load);
    return () => { clearInterval(id); window.removeEventListener('focus', load); };
  }, [load]);

  const replace = (row) => setRequests(prev => prev.map(r => (r.id === row.id ? row : r)));

  const move = (request, status) => {
    let notes = null;
    if (status === 'Cancelled' && !window.confirm(`Cancel the ${request.type} request for ${request.roomName}?`)) return;
    if (status === 'Completed') {
      notes = window.prompt('Anything to note about the job? (optional)', '');
      if (notes === null) return;
    }
    setBusyId(request.id);
    pendingWrites.current += 1;
    send(CONFIG.indexUrl + '/' + request.id, 'PATCH', { status, notes })
      .then(d => { replace(d.request); toast(`${d.request.type} · ${d.request.roomName}: ${d.request.status}`); })
      .catch(err => toast((err && err.message) || 'Could not update this request.', 'error'))
      .finally(() => { setBusyId(null); pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  };

  const count = (s) => requests.filter(r => r.status === s).length;
  const visible = useMemo(() => requests.filter(r => (
    view === 'all' ? true : view === 'done' ? (r.status === 'Completed' || r.status === 'Cancelled') : ['Pending', 'Accepted', 'In Progress'].includes(r.status)
  )), [requests, view]);

  return (
    <div className="ao" data-hms-no-edit="1">
      <header className="ao-head">
        <div>
          <p className="ao-eyebrow">Housekeeping</p>
          <h1 className="font-display">Service Requests</h1>
          <p className="ao-lead">
            What staying guests ask for: room cleaning, fresh towels and linen, toiletries and
            the like. They come from the guest's My Stay page on the hotel website or from the
            Front Desk. Accept a request, start it, and mark it done; the guest and the Front
            Desk see where it stands. Found something broken? Report it to Maintenance.
          </p>
        </div>
      </header>

      <section className="ao-panel" aria-labelledby="sr-list">
        <div className="ao-panel-head">
          <div>
            <h2 id="sr-list">Requests</h2>
            <p>{requests.length ? `${count('Pending') + count('Accepted') + count('In Progress')} still to do.` : 'No requests.'}</p>
          </div>
          <span className="ao-live">Updates on its own</span>
        </div>

        <div className="ao-stats">
          <div className="ao-stat"><span className="ao-stat-icon tone-warn"><i className="fa-solid fa-hourglass-half"></i></span><div><b>{count('Pending')}</b><span>Pending</span></div></div>
          <div className="ao-stat"><span className="ao-stat-icon tone-guest"><i className="fa-solid fa-hand"></i></span><div><b>{count('Accepted')}</b><span>Accepted</span></div></div>
          <div className="ao-stat"><span className="ao-stat-icon tone-guest"><i className="fa-solid fa-broom"></i></span><div><b>{count('In Progress')}</b><span>In progress</span></div></div>
          <div className="ao-stat"><span className="ao-stat-icon tone-ok"><i className="fa-solid fa-circle-check"></i></span><div><b>{count('Completed')}</b><span>Completed</span></div></div>
        </div>

        <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap', marginBottom: '1rem' }}>
          {[['todo', 'Still to do'], ['done', 'Finished'], ['all', 'All']].map(([k, label]) => (
            <button key={k} type="button" className={`ao-btn ${view === k ? 'is-solid' : 'is-quiet'}`} aria-pressed={view === k}
              style={{ padding: '0.5rem 0.9rem', fontSize: '0.82rem' }} onClick={() => setView(k)}>{label}</button>
          ))}
        </div>

        {!loading && !canManage && !failed ? (
          <p className="ao-banner"><i className="fa-solid fa-eye"></i><span>You can look at this list, but only Housekeeping staff can work the requests.</span></p>
        ) : null}

        {loading ? (
          <div className="ao-grid" aria-busy="true"><div className="ao-skel"></div><div className="ao-skel"></div></div>
        ) : failed && requests.length === 0 ? (
          <div className="ao-empty" role="alert">
            <div className="ao-empty-icon"><i className="fa-solid fa-wifi"></i></div>
            <h2>Could not load the requests</h2>
            <p>Check your internet connection. The page tries again on its own every few seconds.</p>
          </div>
        ) : visible.length === 0 ? (
          <div className="ao-empty">
            <div className="ao-empty-icon"><i className="fa-solid fa-broom"></i></div>
            <h2>{view === 'todo' ? 'Nothing to do right now' : 'No requests here'}</h2>
            <p>Requests show up here when a staying guest asks on the hotel website, or the Front Desk takes one.</p>
          </div>
        ) : (
          <div className="ao-grid">
            {visible.map(r => (
              <RequestCard key={r.id} request={r} canManage={canManage} busy={busyId === r.id} onMove={move} onDefect={setDefectFor} />
            ))}
          </div>
        )}
      </section>

      {defectFor ? <DefectModal request={defectFor} onClose={() => setDefectFor(null)} onSent={replace} /> : null}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
