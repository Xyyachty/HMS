@extends('students.builder.ops-shell')

@section('page-title', 'Room Service')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }

  /* Everything below reads the shell's tokens (--bg, --card, --border, --fg,
     --fg-muted, --accent), the same way Walk-in Guests does, so the page follows
     Template 1, Template 2 and a team's own site colours. Tints are mixed from
     those tokens rather than hard-coded.

     Shape rule: pills for the status tabs and status, 10px for buttons, 14px for
     panels and cards. */
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }

  .rs {
    --rs-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --rs-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --rs-line: var(--border);
    --rs-ok: var(--success, #4ade80);
    padding: 1.5rem 1.5rem 3rem;
    color: var(--fg);
  }

  /* Page header */
  .rs-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
  .rs-eyebrow { color: var(--accent); font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; margin: 0 0 0.5rem; }
  .rs-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .rs-head .rs-lead { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 60ch; line-height: 1.5; }

  .rs-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem;
    font: 600 0.88rem/1 var(--font-body, 'Outfit', sans-serif);
    padding: 0.8rem 1.15rem; border-radius: 0.625rem; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); background: transparent; color: var(--accent);
    transition: background 0.15s, transform 0.1s;
  }
  .rs-btn:hover { background: var(--rs-tint); }
  .rs-btn:active { transform: translateY(1px); }
  .rs-btn:focus-visible, .rs-view:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  /* The list panel */
  .rs-panel { background: var(--card); border: 1px solid var(--rs-line); border-radius: 0.875rem; padding: 1.2rem 1.3rem 1.4rem; }
  .rs-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1.1rem; }
  .rs-views { box-sizing: border-box; max-width: 100%; display: inline-flex; flex-wrap: wrap; gap: 0.3rem; padding: 0.3rem; border-radius: 999px; background: var(--rs-soft); border: 1px solid var(--rs-line); }
  .rs-view {
    display: inline-flex; align-items: center; gap: 0.5rem;
    font: 600 0.86rem/1 var(--font-body, 'Outfit', sans-serif);
    padding: 0.62rem 1rem; border-radius: 999px; cursor: pointer;
    border: 0; background: transparent; color: var(--fg-muted);
    transition: background 0.15s, color 0.15s;
  }
  .rs-view:hover { color: var(--fg); }
  .rs-view.is-on { background: var(--accent); color: var(--bg); }
  .rs-count {
    min-width: 1.45rem; padding: 0.2rem 0.4rem; border-radius: 999px; text-align: center;
    font-size: 0.74rem; font-variant-numeric: tabular-nums;
    background: color-mix(in srgb, var(--fg) 8%, transparent);
  }
  .rs-view.is-on .rs-count { background: color-mix(in srgb, var(--bg) 22%, transparent); }
  .rs-view i { font-size: 0.8rem; }
  .rs-showing { margin: 0; color: var(--fg-muted); font-size: 0.8rem; display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap; }
  .rs-live { display: inline-flex; align-items: center; gap: 0.35rem; }
  .rs-live::before { content: ''; width: 0.4375rem; height: 0.4375rem; border-radius: 50%; background: var(--rs-ok); }

  /* Order cards */
  .rs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(300px, 100%), 1fr)); gap: 1rem; }
  .rs-card { min-width: 0; border: 1px solid var(--rs-line); border-radius: 0.875rem; background: var(--rs-soft); padding: 1.05rem 1.1rem 1rem; display: flex; flex-direction: column; gap: 0.85rem; }
  .rs-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
  .rs-room { display: block; font-size: 1.15rem; font-weight: 700; color: var(--fg); line-height: 1.2; }
  .rs-guest { display: block; font-size: 0.85rem; color: var(--fg-muted); margin-top: 0.2rem; }
  .rs-pill {
    flex: none; display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.76rem; font-weight: 600;
    background: var(--rs-tint); color: var(--accent); white-space: nowrap;
  }
  .rs-pill.is-Completed { background: color-mix(in srgb, var(--rs-ok) 16%, transparent); color: var(--rs-ok); }
  .rs-pill.is-Cancelled { background: color-mix(in srgb, var(--fg) 8%, transparent); color: var(--fg-muted); }

  /* The four steps again, small, with this order's place on them */
  .rs-track { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
  .rs-track li { position: relative; display: flex; flex-direction: column; align-items: center; gap: 0.35rem; font-size: 0.68rem; color: var(--fg-muted); text-align: center; }
  .rs-track li::before {
    content: ''; position: absolute; top: 0.375rem; left: -50%; width: 100%; height: 2px;
    background: var(--rs-line);
  }
  .rs-track li:first-child::before { display: none; }
  .rs-track li.is-done::before, .rs-track li.is-now::before { background: var(--accent); }
  .rs-dot { position: relative; z-index: 1; width: 0.875rem; height: 0.875rem; border-radius: 50%; background: var(--card); border: 2px solid var(--rs-line); }
  .rs-track li.is-done .rs-dot { background: var(--accent); border-color: var(--accent); }
  .rs-track li.is-now .rs-dot { background: var(--card); border-color: var(--accent); box-shadow: 0 0 0 4px var(--rs-tint); }
  .rs-track li.is-now, .rs-track li.is-done { color: var(--fg); }
  .rs-track li.is-now { font-weight: 700; }

  .rs-items { list-style: none; margin: 0; padding: 0.7rem 0.8rem; border-radius: 0.625rem; background: var(--card); border: 1px solid var(--rs-line); display: grid; gap: 0.35rem; }
  .rs-items li { display: flex; gap: 0.55rem; font-size: 0.86rem; color: var(--fg); }
  .rs-qty { flex: none; min-width: 1.9rem; font-weight: 700; color: var(--accent); font-variant-numeric: tabular-nums; }
  .rs-card-foot { display: flex; align-items: flex-end; justify-content: space-between; gap: 0.75rem; }
  .rs-meta { font-size: 0.78rem; color: var(--fg-muted); line-height: 1.45; }
  .rs-total { text-align: right; }
  .rs-total small { display: block; font-size: 0.7rem; color: var(--fg-muted); }
  .rs-total b { font-size: 1.05rem; color: var(--fg); font-variant-numeric: tabular-nums; }
  .rs-note { margin: 0; padding-top: 0.75rem; border-top: 1px dashed var(--rs-line); font-size: 0.8rem; color: var(--fg-muted); display: flex; gap: 0.5rem; align-items: flex-start; }
  .rs-note i { color: var(--accent); margin-top: 0.15rem; }

  /* Empty */
  .rs-empty { border: 1.5px dashed var(--rs-line); border-radius: 0.875rem; padding: 2.4rem 1.5rem; text-align: center; }
  .rs-empty-icon {
    width: 3.5rem; height: 3.5rem; margin: 0 auto 0.9rem; border-radius: 1rem;
    display: flex; align-items: center; justify-content: center; font-size: 1.35rem;
    background: var(--rs-tint); color: var(--accent);
  }
  .rs-empty h2 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .rs-empty p { margin: 0.4rem auto 0; max-width: 46ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }

  @media (max-width: 560px) {
    .rs { padding: 1.1rem 1rem 2.5rem; }
    .rs-views { width: 100%; border-radius: 0.875rem; }
  }

  /* Template 2 (cream / forest green / DM Sans + Cormorant Garamond), the same
     set Walk-in Guests and Guest Information use. Without it this page kept
     Template 1's dark panels, and its heading vanished on the cream background. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #6b6560;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #c81e3a; --success: #15803d;
  }

  /* One screen, no page scroll: the shell's content area is the page height, the
     page fills it, and only the menu and the order list scroll inside their own
     panels. Below 1000px it stacks and the page scrolls as usual. */
  #ops-root { height: 100%; }
  .rs.rs-fit { height: 100%; box-sizing: border-box; display: flex; flex-direction: column; gap: 0.85rem; padding: 1rem 1.5rem 1.1rem; }
  .rs-fit .rs-head { margin: 0; align-items: flex-end; }
  .rs-fit .rs-head h1 { font-size: 1.45rem; }
  .rs-fit .rs-eyebrow { margin-bottom: 0.3rem; }
  .rs-fit .rs-head .rs-lead { font-size: 0.86rem; margin-top: 0.2rem; max-width: none; }
  .rs-body { flex: 1; min-height: 0; display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: 1rem; }
  .rs-col { min-height: 0; display: flex; flex-direction: column; background: var(--card); border: 1px solid var(--rs-line); border-radius: 0.875rem; padding: 1.1rem 1.2rem; }
  .rs-col-title { margin: 0 0 0.7rem; font-size: 1.1rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; }
  .rs-scroll { flex: 1; min-height: 0; overflow-y: auto; padding-right: 0.25rem; }
  @media (max-width: 1000px) {
    #ops-root { height: auto; }
    .rs.rs-fit { height: auto; }
    .rs-body { grid-template-columns: 1fr; }
    .rs-scroll { max-height: 60vh; }
  }

  /* Numbered steps, so the order of things is never a question. */
  .rs-step-head { display: flex; align-items: center; gap: 0.6rem; margin: 0 0 0.45rem; font-size: 0.92rem; font-weight: 700; color: var(--fg); }
  .rs-step-num { flex: none; width: 1.6rem; height: 1.6rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; background: var(--rs-tint); color: var(--accent); border: 1px solid var(--accent); }
  .rs-step-head.is-done .rs-step-num { background: var(--accent); color: var(--bg); }

  /* Step 1: guests as cards to tap, not a dropdown. */
  .rs-guests { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.5rem; max-height: 7.4rem; overflow-y: auto; margin-bottom: 0.8rem; }
  .rs-guest-btn { text-align: left; padding: 0.5rem 0.7rem; border-radius: 0.625rem; border: 1px solid var(--rs-line); background: var(--rs-soft); color: var(--fg); cursor: pointer; font: inherit; transition: border-color 0.15s, background 0.15s, transform 0.1s; }
  .rs-guest-btn:hover { border-color: var(--accent); }
  .rs-guest-btn:active { transform: scale(0.98); }
  .rs-guest-btn.is-on { border-color: var(--accent); background: var(--rs-tint); box-shadow: inset 0 0 0 1px var(--accent); }
  .rs-guest-btn b { display: block; font-size: 0.95rem; }
  .rs-guest-btn span { display: block; font-size: 0.78rem; color: var(--fg-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .rs-guest-btn small { display: inline-block; margin-top: 0.25rem; font-size: 0.68rem; padding: 0.1rem 0.45rem; border-radius: 999px; background: color-mix(in srgb, var(--fg) 8%, transparent); color: var(--fg-muted); }

  /* Step 2: the menu, filtered by course. */
  .rs-courses { display: flex; gap: 0.35rem; overflow-x: auto; margin-bottom: 0.6rem; padding-bottom: 2px; }
  .rs-course { padding: 0.3rem 0.75rem; border-radius: 999px; border: 1px solid var(--rs-line); background: transparent; color: var(--fg-muted); cursor: pointer; font-family: inherit; font-size: 0.76rem; font-weight: 600; line-height: 1.2; white-space: nowrap; }
  .rs-course.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .rs-dishes { display: grid; gap: 0.35rem; }
  .rs-dish { display: flex; align-items: center; gap: 0.75rem; padding: 0.4rem 0.7rem; border: 1px solid var(--rs-line); border-radius: 0.625rem; }
  .rs-dish.is-picked { border-color: var(--accent); background: var(--rs-tint); }
  .rs-dish-name { flex: 1; min-width: 0; }
  .rs-dish-name b { display: block; font-size: 0.9rem; }
  .rs-dish-name span { font-size: 0.75rem; color: var(--fg-muted); }
  .rs-stepper { display: inline-flex; align-items: center; gap: 0.45rem; }
  .rs-stepper button { width: 1.875rem; height: 1.875rem; border-radius: 0.5625rem; border: 1px solid var(--rs-line); background: transparent; color: var(--fg); cursor: pointer; font-size: 1rem; line-height: 1; }
  .rs-stepper button:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
  .rs-stepper button:disabled { opacity: 0.35; cursor: not-allowed; }
  .rs-stepper output { min-width: 1.4rem; text-align: center; font-weight: 700; font-variant-numeric: tabular-nums; }

  /* Step 3: what is about to be sent, and the one button that sends it. */
  .rs-send { margin-top: 0.7rem; padding-top: 0.7rem; border-top: 1px solid var(--rs-line); display: grid; gap: 0.6rem; }
  .rs-summary { margin: 0; font-size: 0.86rem; color: var(--fg-muted); line-height: 1.45; }
  .rs-summary b { color: var(--fg); }
  .rs-send-row { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; }
  .rs-send-total { font-size: 1.15rem; font-weight: 700; font-variant-numeric: tabular-nums; }
  .rs-btn-solid { background: var(--accent); color: var(--bg); }
  .rs-btn-solid:hover { background: var(--accent-light, var(--accent)); }
  .rs-btn:disabled { opacity: 0.5; cursor: not-allowed; }
  .rs-btn-big { padding: 0.9rem 1.4rem; font-size: 0.95rem; }
  .rs-error { margin: 0; color: #f87171; font-size: 0.85rem; }
  .rs-notice { margin: 0; padding: 0.6rem 0.8rem; border-radius: 0.625rem; border: 1px solid var(--rs-ok); font-size: 0.85rem; color: var(--fg); display: flex; gap: 0.5rem; align-items: center; }
  .rs-notice i { color: var(--rs-ok); }
  .rs-hint { margin: 0; padding: 0.8rem; border-radius: 0.625rem; border: 1.5px dashed var(--rs-line); font-size: 0.85rem; color: var(--fg-muted); line-height: 1.5; }

  /* Orders column: the cards sit one under another in their own scroll. */
  .rs-col .rs-views { margin-bottom: 0.75rem; flex-wrap: nowrap; overflow-x: auto; border-radius: 0.75rem; flex: none; }
  .rs-col .rs-view { white-space: nowrap; padding: 0.4rem 0.7rem; font-size: 0.78rem; }
  .rs-col .rs-grid { grid-template-columns: 1fr; gap: 0.75rem; }
  .rs-col .rs-card { padding: 0.85rem 0.95rem; gap: 0.65rem; }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_ROOM_SERVICE = {
    backUrl: @json(route('students.dashboard', ['section' => 'tasks'])),
    ordersUrl: @json(route('students.hotel.orders.index', [], false)),
    // Root-relative: placing an order and listing who can be ordered for.
    storeUrl: @json(route('students.hotel.orders.store', [], false)),
    roomsUrl: @json(route('students.hotel.rooms.index', [], false)),
    menusUrl: @json(route('students.hotel.menus.index', [], false)),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback } = React;

const CFG = window.HMS_ROOM_SERVICE;

// Mirrors App\Models\HotelFoodOrder::FLOW. Every one of these transitions belongs to
// Restaurant Services, delivery included — this page is a window onto their queue,
// so each step is named for what is happening to the food, not for the status code.
const STAGES = [
  { status: 'Preparing',  label: 'Being cooked',     short: 'Cooking',    icon: 'fa-fire-burner' },
  { status: 'Ready',      label: 'Ready to send up', short: 'Ready',      icon: 'fa-bell-concierge' },
  { status: 'Delivering', label: 'On the way',       short: 'On the way', icon: 'fa-person-walking-arrow-right' },
  { status: 'Completed',  label: 'Delivered',        short: 'Delivered',  icon: 'fa-circle-check' },
];
const STAGE_BY_STATUS = STAGES.reduce((map, s) => { map[s.status] = s; return map; }, {});
const ACTIVE_STATUSES = ['Preparing', 'Ready', 'Delivering'];

// One tab per step, after All orders. Cancelled is kept for the one order
// cancelled under the old rule; it sits with Delivered, as a finished order.
const TABS = [
  { key: 'all', label: 'All orders', short: 'All', icon: 'fa-list', match: () => true },
  ...STAGES.map(s => ({
    key: s.status,
    label: s.label,
    short: s.short,
    icon: s.icon,
    match: s.status === 'Completed'
      ? o => o.status === 'Completed' || o.status === 'Cancelled'
      : o => o.status === s.status,
  })),
];

const NOTES = {
  Preparing: 'The kitchen is cooking this order. If the guest calls, let them know it is being prepared.',
  Ready: 'The food is plated. The kitchen will bring it up to the room shortly.',
  Delivering: 'The order is on its way to the guest’s room now.',
  Completed: 'Delivered to the guest. The amount has been added to their bill.',
  Cancelled: 'This order was cancelled. Nothing was charged and the food went back to stock.',
};

function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0';
  return '₱' + n.toLocaleString();
}

function formatOrderTime(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

function plural(n, one, many) {
  return n + ' ' + (n === 1 ? one : many);
}

function OrderCard({ order }) {
  const stage = STAGE_BY_STATUS[order.status];
  const stepIndex = STAGES.findIndex(s => s.status === order.status);
  const items = order.items || [];

  return (
    <article className="rs-card">
      <div className="rs-card-top">
        <div style={{ minWidth: 0 }}>
          <span className="rs-room">{order.roomNumber ? (/^room\b/i.test(order.roomNumber) ? order.roomNumber : `Room ${order.roomNumber}`) : 'Room not set'}</span>
          <span className="rs-guest">{order.guestName || 'Guest name not given'}</span>
        </div>
        <span className={`rs-pill is-${order.status}`}>
          <i className={`fa-solid ${stage ? stage.icon : 'fa-ban'}`}></i>
          {stage ? stage.label : order.status}
        </span>
      </div>

      {stepIndex !== -1 ? (
        <ol className="rs-track" aria-label={`Step ${stepIndex + 1} of ${STAGES.length}: ${stage.label}`}>
          {STAGES.map((s, i) => (
            <li key={s.status} className={i < stepIndex || order.status === 'Completed' ? 'is-done' : i === stepIndex ? 'is-now' : ''}>
              <span className="rs-dot"></span>
              {s.short}
            </li>
          ))}
        </ol>
      ) : null}

      <ul className="rs-items" aria-label="What was ordered">
        {items.length === 0 ? (
          <li style={{ color: 'var(--fg-muted)' }}>No items listed</li>
        ) : items.map((item, i) => (
          <li key={i}><span className="rs-qty">{item.qty}×</span><span>{item.name}</span></li>
        ))}
      </ul>

      <div className="rs-card-foot">
        <div className="rs-meta">
          Order #{order.id}<br />
          Ordered at {formatOrderTime(order.placedAt)}
        </div>
        <div className="rs-total">
          <small>Total</small>
          <b>{formatPeso(order.total)}</b>
        </div>
      </div>

      <p className="rs-note">
        <i className="fa-solid fa-circle-info"></i>
        <span>{NOTES[order.status] || NOTES.Completed}</span>
      </p>
    </article>
  );
}

/* The desk orders food for a guest booked into a room, in three numbered steps.
   The order goes straight to Restaurant Services' kitchen queue (Preparing) and
   is billed to the guest's stay; the list beside it then follows the order. */
function NewOrderPanel({ onPlaced }) {
  const [rooms, setRooms] = useState(null);
  const [menu, setMenu] = useState(null);
  const [roomName, setRoomName] = useState('');
  const [course, setCourse] = useState('All');
  const [qty, setQty] = useState({});
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');

  // Guests and stock change while the desk works, so both are read again on the
  // same beat as the order list, and after every order placed here.
  const load = useCallback(() => {
    const get = (url) => fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } }).then(r => r.json());
    get(CFG.roomsUrl).then(d => setRooms(Array.isArray(d.rooms) ? d.rooms : [])).catch(() => setRooms(prev => prev || []));
    get(CFG.menusUrl).then(d => setMenu(Array.isArray(d.items) ? d.items : [])).catch(() => setMenu(prev => prev || []));
  }, []);
  useEffect(() => {
    load();
    const id = setInterval(load, 8000);
    return () => clearInterval(id);
  }, [load]);

  // The confirmation stays long enough to read, then clears itself.
  useEffect(() => {
    if (!notice) return;
    const id = setTimeout(() => setNotice(''), 6000);
    return () => clearTimeout(id);
  }, [notice]);

  const OPEN = ['Booked', 'Arrived', 'Checked In'];
  const guests = (rooms || []).filter(r => r.reservation && OPEN.indexOf(r.reservation.status) !== -1);
  const chosen = guests.find(r => r.name === roomName) || null;
  const courses = ['All'];
  (menu || []).forEach(m => { if (courses.indexOf(m.category) === -1) courses.push(m.category); });
  const shown = (menu || []).filter(m => course === 'All' || m.category === course);
  const lines = (menu || []).filter(m => (qty[m.dbId] || 0) > 0);
  const count = lines.reduce((sum, m) => sum + qty[m.dbId], 0);
  const total = lines.reduce((sum, m) => sum + m.price * qty[m.dbId], 0);

  const step = (item, delta) => setQty(prev => {
    const next = Math.max(0, Math.min(item.stock, (prev[item.dbId] || 0) + delta));
    return Object.assign({}, prev, { [item.dbId]: next });
  });
  const clear = () => { setRoomName(''); setQty({}); setError(''); };

  const place = () => {
    if (!chosen || !lines.length || saving) return;
    setSaving(true);
    setError('');
    fetch(CFG.storeUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json', 'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        order_type: 'room_service',
        room_number: chosen.name,
        guest_name: chosen.reservation.fullName || 'Guest',
        items: lines.map(m => ({ name: m.name, menu_item_id: m.dbId, price: m.price, qty: qty[m.dbId] })),
      }),
    })
      .then(r => r.json().then(d => (r.ok ? d : Promise.reject(d))))
      .then(() => {
        setNotice(`Sent. The kitchen is now cooking for ${chosen.reservation.fullName || 'the guest'} in ${chosen.name}.`);
        onPlaced();
        clear();
        load();
      })
      .catch(err => setError((err && err.message) || 'The order could not be sent. Please try again.'))
      .finally(() => setSaving(false));
  };

  let missing = '';
  if (!chosen) missing = 'Choose who the food is for.';
  else if (!lines.length) missing = 'Add at least one dish.';

  return (
    <section className="rs-col" aria-label="Order food for a guest">
      <h2 className="rs-col-title font-display">Order food for a guest</h2>

      <p className={`rs-step-head${chosen ? ' is-done' : ''}`}><span className="rs-step-num">1</span> Who is the food for?</p>
      {rooms === null ? <p className="rs-hint">Loading guests&hellip;</p> : guests.length === 0 ? (
        <p className="rs-hint" style={{ marginBottom: '1rem' }}>No guest is booked into a room. Book one on Walk-in Guests first, then come back here.</p>
      ) : (
        <div className="rs-guests" role="radiogroup" aria-label="Guest">
          {guests.map(r => (
            <button key={r.id} type="button" role="radio" aria-checked={roomName === r.name}
              className={`rs-guest-btn${roomName === r.name ? ' is-on' : ''}`}
              onClick={() => setRoomName(roomName === r.name ? '' : r.name)}>
              <b>{r.name}</b>
              <span>{r.reservation.fullName || 'Guest'}</span>
              <small>{r.reservation.status}</small>
            </button>
          ))}
        </div>
      )}

      <p className={`rs-step-head${lines.length ? ' is-done' : ''}`}><span className="rs-step-num">2</span> What would they like?</p>
      {menu === null ? <p className="rs-hint">Loading the menu&hellip;</p> : menu.length === 0 ? (
        <p className="rs-hint">The restaurant has not put any dishes on its menu.</p>
      ) : (
        <>
          <div className="rs-courses" role="group" aria-label="Course">
            {courses.map(c => (
              <button key={c} type="button" className={`rs-course${course === c ? ' is-on' : ''}`} onClick={() => setCourse(c)}>{c}</button>
            ))}
          </div>
          <div className="rs-scroll">
            <div className="rs-dishes">
              {shown.map(m => (
                <div className={`rs-dish${qty[m.dbId] ? ' is-picked' : ''}`} key={m.dbId}>
                  <div className="rs-dish-name">
                    <b>{m.name}</b>
                    <span>{formatPeso(m.price)} · {m.stock > 0 ? m.stock + ' left' : 'Sold out'}</span>
                  </div>
                  <div className="rs-stepper">
                    <button type="button" aria-label={'One less ' + m.name} disabled={!(qty[m.dbId] > 0)} onClick={() => step(m, -1)}>&minus;</button>
                    <output aria-label={m.name + ' quantity'}>{qty[m.dbId] || 0}</output>
                    <button type="button" aria-label={'One more ' + m.name} disabled={(qty[m.dbId] || 0) >= m.stock} onClick={() => step(m, 1)}>+</button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </>
      )}

      <div className="rs-send">
        <p className={`rs-step-head${chosen && lines.length ? ' is-done' : ''}`} style={{ margin: 0 }}><span className="rs-step-num">3</span> Send it to the kitchen</p>
        {notice ? <p className="rs-notice" role="status"><i className="fa-solid fa-circle-check"></i>{notice}</p> : null}
        {error ? <p className="rs-error" role="alert">{error}</p> : null}
        <p className="rs-summary">
          {chosen && lines.length
            ? <><b>{count} {count === 1 ? 'dish' : 'dishes'}</b> for <b>{chosen.reservation.fullName || 'the guest'}</b> in <b>{chosen.name}</b>. It is added to their bill.</>
            : missing}
        </p>
        <div className="rs-send-row">
          <span className="rs-send-total">{formatPeso(total)}</span>
          <div style={{ display: 'flex', gap: '0.5rem' }}>
            <button type="button" className="rs-btn" onClick={clear} disabled={saving || (!roomName && !lines.length)}>Start over</button>
            <button type="button" className="rs-btn rs-btn-solid rs-btn-big" disabled={!chosen || !lines.length || saving} onClick={place}>
              <i className="fa-solid fa-paper-plane"></i> {saving ? 'Sending…' : 'Send to Kitchen'}
            </button>
          </div>
        </div>
      </div>
    </section>
  );
}

function RoomServicePage({ orders, onBack, onOrdered }) {
  const [tab, setTab] = useState('all');

  const roomServiceOrders = (orders || [])
    .filter(o => o.orderType !== 'dine_in')
    .sort((a, b) => (a.id < b.id ? 1 : -1));

  const current = TABS.find(t => t.key === tab) || TABS[0];
  const visible = roomServiceOrders.filter(current.match);
  const notDelivered = roomServiceOrders.filter(o => ACTIVE_STATUSES.indexOf(o.status) !== -1).length;

  let emptyTitle = `No orders are ${current.label.toLowerCase()}`;
  let emptyText = 'Pick another tab to see the rest of the orders.';
  if (roomServiceOrders.length === 0) {
    emptyTitle = 'No orders';
    emptyText = 'Orders you send, and ones guests place on the hotel website, show up here.';
  } else if (tab === 'Completed') {
    emptyTitle = 'Nothing delivered';
    emptyText = 'Orders move here once the kitchen has brought them to the guest’s room.';
  }

  return (
    <div className="rs rs-fit">
      <header className="rs-head">
        <div>
          <p className="rs-eyebrow">Front Desk</p>
          <h1 className="font-display">Room Service</h1>
          <p className="rs-lead">Order food for a guest on the left. Follow every order on the right &mdash; the kitchen moves it along.</p>
        </div>
      </header>

      <div className="rs-body">
        <NewOrderPanel onPlaced={() => { if (onOrdered) onOrdered(); }} />

        <section className="rs-col" aria-label="Orders">
          <h2 className="rs-col-title font-display">
            Orders
            <span className="rs-showing" style={{ fontFamily: 'var(--font-body, Outfit, sans-serif)' }}>
              <span className="rs-live">Updates on its own</span>
              <span>· {plural(notDelivered, 'order', 'orders')} not delivered yet</span>
            </span>
          </h2>
          <div className="rs-views" role="group" aria-label="Show orders by status">
            {TABS.map(t => (
              <button
                key={t.key}
                type="button"
                className={`rs-view ${tab === t.key ? 'is-on' : ''}`}
                aria-pressed={tab === t.key}
                onClick={() => setTab(t.key)}
              >
                <i className={`fa-solid ${t.icon}`}></i>
                {t.short || t.label}
                <span className="rs-count">{roomServiceOrders.filter(t.match).length}</span>
              </button>
            ))}
          </div>
          <div className="rs-scroll">
            {visible.length === 0 ? (
              <div className="rs-empty">
                <div className="rs-empty-icon"><i className={`fa-solid ${current.key === 'all' ? 'fa-bell-concierge' : current.icon}`}></i></div>
                <h2>{emptyTitle}</h2>
                <p>{emptyText}</p>
              </div>
            ) : (
              <div className="rs-grid">
                {visible.map(order => <OrderCard key={order.id} order={order} />)}
              </div>
            )}
          </div>
        </section>
      </div>
    </div>
  );
}

function App() {
  const [orders, setOrders] = useState([]);

  // The only write here is a new order, after which the list is fetched again;
  // the kitchen owns every status change, so whatever it last saved is the truth.
  const fetchOrders = useCallback(() => {
    fetch(CFG.ordersUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (Array.isArray(data.orders)) setOrders(data.orders);
      })
      .catch(() => {});
  }, []);

  useEffect(() => {
    fetchOrders();
    const id = setInterval(fetchOrders, 8000);
    window.addEventListener('focus', fetchOrders);
    return () => { clearInterval(id); window.removeEventListener('focus', fetchOrders); };
  }, [fetchOrders]);

  return (
    <RoomServicePage
      orders={orders}
      onBack={() => { window.location.href = CFG.backUrl; }}
      onOrdered={fetchOrders}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
