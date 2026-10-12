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
    padding: 0.8rem 1.15rem; border-radius: 10px; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); background: transparent; color: var(--accent);
    transition: background 0.15s, transform 0.1s;
  }
  .rs-btn:hover { background: var(--rs-tint); }
  .rs-btn:active { transform: translateY(1px); }
  .rs-btn:focus-visible, .rs-view:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  /* The list panel */
  .rs-panel { background: var(--card); border: 1px solid var(--rs-line); border-radius: 14px; padding: 1.2rem 1.3rem 1.4rem; }
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
  .rs-live::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--rs-ok); }

  /* Order cards */
  .rs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(300px, 100%), 1fr)); gap: 1rem; }
  .rs-card { min-width: 0; border: 1px solid var(--rs-line); border-radius: 14px; background: var(--rs-soft); padding: 1.05rem 1.1rem 1rem; display: flex; flex-direction: column; gap: 0.85rem; }
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
    content: ''; position: absolute; top: 6px; left: -50%; width: 100%; height: 2px;
    background: var(--rs-line);
  }
  .rs-track li:first-child::before { display: none; }
  .rs-track li.is-done::before, .rs-track li.is-now::before { background: var(--accent); }
  .rs-dot { position: relative; z-index: 1; width: 14px; height: 14px; border-radius: 50%; background: var(--card); border: 2px solid var(--rs-line); }
  .rs-track li.is-done .rs-dot { background: var(--accent); border-color: var(--accent); }
  .rs-track li.is-now .rs-dot { background: var(--card); border-color: var(--accent); box-shadow: 0 0 0 4px var(--rs-tint); }
  .rs-track li.is-now, .rs-track li.is-done { color: var(--fg); }
  .rs-track li.is-now { font-weight: 700; }

  .rs-items { list-style: none; margin: 0; padding: 0.7rem 0.8rem; border-radius: 10px; background: var(--card); border: 1px solid var(--rs-line); display: grid; gap: 0.35rem; }
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
  .rs-empty { border: 1.5px dashed var(--rs-line); border-radius: 14px; padding: 2.4rem 1.5rem; text-align: center; }
  .rs-empty-icon {
    width: 56px; height: 56px; margin: 0 auto 0.9rem; border-radius: 16px;
    display: flex; align-items: center; justify-content: center; font-size: 1.35rem;
    background: var(--rs-tint); color: var(--accent);
  }
  .rs-empty h2 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .rs-empty p { margin: 0.4rem auto 0; max-width: 46ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }

  @media (max-width: 560px) {
    .rs { padding: 1.1rem 1rem 2.5rem; }
    .rs-views { width: 100%; border-radius: 14px; }
  }

  /* New order: the desk orders for a booked guest; the kitchen takes it from there. */
  .rs-btn-solid { background: var(--accent); color: var(--bg); }
  .rs-btn-solid:hover { background: var(--accent-light, var(--accent)); }
  .rs-btn:disabled { opacity: 0.5; cursor: not-allowed; }
  .rs-order { margin-bottom: 1.25rem; }
  .rs-order h2 { margin: 0 0 0.3rem; font-size: 1.2rem; }
  .rs-order .rs-sub { margin: 0 0 1.1rem; color: var(--fg-muted); font-size: 0.85rem; line-height: 1.5; }
  .rs-order-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 2fr); gap: 1.25rem; }
  @media (max-width: 760px) { .rs-order-grid { grid-template-columns: 1fr; } }
  .rs-label { display: block; margin: 0 0 0.4rem; font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase; color: var(--fg-muted); }
  .rs-select { width: 100%; padding: 0.7rem 0.8rem; border-radius: 10px; border: 1px solid var(--border); background: var(--bg); color: var(--fg); font: inherit; margin-bottom: 1rem; }
  .rs-menu { display: grid; gap: 0.45rem; margin-bottom: 1rem; }
  .rs-menu-cat { margin: 0.6rem 0 0.1rem; font-size: 0.72rem; letter-spacing: 0.12em; text-transform: uppercase; color: var(--accent); }
  .rs-dish { display: flex; align-items: center; gap: 0.75rem; padding: 0.55rem 0.7rem; border: 1px solid var(--border); border-radius: 10px; }
  .rs-dish-name { flex: 1; min-width: 0; }
  .rs-dish-name b { display: block; font-size: 0.9rem; }
  .rs-dish-name span { font-size: 0.75rem; color: var(--fg-muted); }
  .rs-qty { display: inline-flex; align-items: center; gap: 0.4rem; }
  .rs-qty button { width: 28px; height: 28px; border-radius: 8px; border: 1px solid var(--border); background: transparent; color: var(--fg); cursor: pointer; }
  .rs-qty button:disabled { opacity: 0.35; cursor: not-allowed; }
  .rs-qty output { min-width: 1.4rem; text-align: center; font-variant-numeric: tabular-nums; }
  .rs-total { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding-top: 0.9rem; border-top: 1px solid var(--border); flex-wrap: wrap; }
  .rs-error { margin: 0 0 0.8rem; color: #f87171; font-size: 0.85rem; }
  .rs-notice { margin: 0 0 1rem; padding: 0.7rem 0.9rem; border-radius: 10px; border: 1px solid var(--rs-ok); color: var(--fg); font-size: 0.86rem; }
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
  { key: 'all', label: 'All orders', icon: 'fa-list', match: () => true },
  ...STAGES.map(s => ({
    key: s.status,
    label: s.label,
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
          <span className="rs-room">{order.roomNumber ? `Room ${order.roomNumber}` : 'Room not set'}</span>
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

/* The desk orders food for a guest booked into a room. The order goes straight
   to Restaurant Services' kitchen queue (status Preparing) and is billed to the
   guest's stay; this page then follows it like any other. */
function NewOrderPanel({ onPlaced }) {
  const [rooms, setRooms] = useState(null);
  const [menu, setMenu] = useState(null);
  const [roomName, setRoomName] = useState('');
  const [qty, setQty] = useState({});
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

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

  const clear = () => { setRoomName(''); setQty({}); setError(''); };

  const OPEN = ['Booked', 'Arrived', 'Checked In'];
  const guests = (rooms || []).filter(r => r.reservation && OPEN.indexOf(r.reservation.status) !== -1);
  const chosen = guests.find(r => r.name === roomName) || null;
  const categories = [];
  (menu || []).forEach(m => { if (categories.indexOf(m.category) === -1) categories.push(m.category); });
  const lines = (menu || []).filter(m => (qty[m.dbId] || 0) > 0);
  const total = lines.reduce((sum, m) => sum + m.price * qty[m.dbId], 0);

  const step = (item, delta) => setQty(prev => {
    const next = Math.max(0, Math.min(item.stock, (prev[item.dbId] || 0) + delta));
    return Object.assign({}, prev, { [item.dbId]: next });
  });

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
      .then(() => { onPlaced(chosen); clear(); load(); })
      .catch(err => setError((err && err.message) || 'The order could not be placed. Try again.'))
      .finally(() => setSaving(false));
  };

  return (
    <section className="rs-panel rs-order" aria-label="Order room service for a guest">
        <h2 className="font-display">Order for a Guest</h2>
        <p className="rs-sub">Pick a guest booked into a room and the food. It goes straight to the Restaurant kitchen and is added to the guest&rsquo;s bill.</p>

        <div className="rs-order-grid">
        <div>
        <label className="rs-label" htmlFor="rs-guest">Guest</label>
        {rooms === null ? <p className="rs-sub">Loading guests&hellip;</p> : guests.length === 0 ? (
          <p className="rs-sub">No guest is booked into a room right now. Book one on Walk-in Guests or the website first.</p>
        ) : (
          <select id="rs-guest" className="rs-select" value={roomName} onChange={e => setRoomName(e.target.value)}>
            <option value="">Choose a guest&hellip;</option>
            {guests.map(r => (
              <option key={r.id} value={r.name}>{r.name} — {r.reservation.fullName || 'Guest'} ({r.reservation.status})</option>
            ))}
          </select>
        )}
        </div>

        <div>
        <span className="rs-label">Food</span>
        {menu === null ? <p className="rs-sub">Loading the menu&hellip;</p> : menu.length === 0 ? (
          <p className="rs-sub">The restaurant has no dishes on its menu yet.</p>
        ) : (
          <div className="rs-menu">
            {categories.map(cat => (
              <React.Fragment key={cat}>
                <p className="rs-menu-cat">{cat}</p>
                {menu.filter(m => m.category === cat).map(m => (
                  <div className="rs-dish" key={m.dbId}>
                    <div className="rs-dish-name">
                      <b>{m.name}</b>
                      <span>{formatPeso(m.price)} · {m.stock > 0 ? m.stock + ' left' : 'Sold out'}</span>
                    </div>
                    <div className="rs-qty">
                      <button type="button" aria-label={'One less ' + m.name} disabled={!(qty[m.dbId] > 0)} onClick={() => step(m, -1)}>&minus;</button>
                      <output>{qty[m.dbId] || 0}</output>
                      <button type="button" aria-label={'One more ' + m.name} disabled={(qty[m.dbId] || 0) >= m.stock} onClick={() => step(m, 1)}>+</button>
                    </div>
                  </div>
                ))}
              </React.Fragment>
            ))}
          </div>
        )}
        </div>
        </div>

        {error ? <p className="rs-error">{error}</p> : null}
        <div className="rs-total">
          <strong>Total {formatPeso(total)}</strong>
          <div style={{ display: 'flex', gap: '0.5rem' }}>
            <button type="button" className="rs-btn" onClick={clear} disabled={saving}>Clear</button>
            <button type="button" className="rs-btn rs-btn-solid" disabled={!chosen || !lines.length || saving} onClick={place}>
              <i className="fa-solid fa-paper-plane"></i> {saving ? 'Sending…' : 'Send to Kitchen'}
            </button>
          </div>
        </div>
    </section>
  );
}

function RoomServicePage({ orders, onBack, onOrdered }) {
  const [notice, setNotice] = useState('');
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
    emptyTitle = 'No room-service orders yet';
    emptyText = 'Order for a booked guest above, or wait for a guest to order from the hotel website’s Restaurant page.';
  } else if (tab === 'Completed') {
    emptyTitle = 'Nothing delivered yet';
    emptyText = 'Orders move here once the kitchen has brought them to the guest’s room.';
  }

  return (
    <div className="rs">
      <header className="rs-head">
        <div>
          <p className="rs-eyebrow">Front Desk</p>
          <h1 className="font-display">Room Service</h1>
          <p className="rs-lead">
            Food ordered to guests&rsquo; rooms. Order for a booked guest here and it goes
            straight to the Restaurant kitchen; the kitchen moves each order along. Use this
            page to see where an order is if a guest calls the desk.
          </p>
        </div>
      </header>
      {notice ? <p className="rs-notice" role="status">{notice}</p> : null}
      <NewOrderPanel
        onPlaced={(room) => {
          setNotice(`Order for ${room.reservation.fullName || 'the guest'} in ${room.name} sent to the Restaurant kitchen.`);
          if (onOrdered) onOrdered();
        }}
      />

      <section className="rs-panel">
        <div className="rs-toolbar">
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
                {t.label}
                <span className="rs-count">{roomServiceOrders.filter(t.match).length}</span>
              </button>
            ))}
          </div>
          <p className="rs-showing">
            <span className="rs-live">Updates on its own</span>
            <span>· {plural(notDelivered, 'order', 'orders')} not delivered yet</span>
          </p>
        </div>

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
      </section>
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
