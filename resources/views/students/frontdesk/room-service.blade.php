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

     Shape rule: pills for the view switch and status, 10px for buttons, 14px for
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
  .rs-btn:focus-visible, .rs-stage:focus-visible, .rs-view:focus-visible, .rs-link:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  /* Where the orders are: the four steps an order goes through, left to right */
  .rs-flow-title { margin: 0 0 0.6rem; font-size: 0.84rem; font-weight: 600; color: var(--fg); }
  .rs-flow { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.85rem; margin-bottom: 1.5rem; }
  .rs-stage {
    position: relative; display: flex; align-items: center; gap: 0.85rem; text-align: left;
    padding: 1rem 1.05rem; border-radius: 14px; cursor: pointer;
    background: var(--card); border: 1.5px solid var(--rs-line); color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif); transition: border-color 0.15s, background 0.15s;
  }
  .rs-stage:hover { border-color: color-mix(in srgb, var(--accent) 55%, var(--rs-line)); }
  .rs-stage.is-on { border-color: var(--accent); background: var(--rs-tint); }
  /* Arrow to the next step, on screens wide enough to show the row */
  .rs-stage:not(:last-child)::after {
    content: ''; position: absolute; top: 50%; right: -0.62rem; z-index: 1;
    width: 0.5rem; height: 0.5rem; transform: translateY(-50%) rotate(45deg);
    border-top: 2px solid var(--fg-muted); border-right: 2px solid var(--fg-muted); opacity: 0.5;
  }
  .rs-stage-icon {
    flex: none; width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 1.05rem;
    background: var(--rs-soft); color: var(--accent);
  }
  .rs-stage.is-on .rs-stage-icon { background: var(--accent); color: var(--bg); }
  .rs-stage-text { min-width: 0; }
  .rs-stage-text b { display: block; font-size: 1.45rem; line-height: 1; font-variant-numeric: tabular-nums; }
  .rs-stage-text span { display: block; font-size: 0.9rem; font-weight: 600; margin-top: 0.3rem; }
  .rs-stage-text small { display: block; color: var(--fg-muted); font-size: 0.76rem; margin-top: 0.15rem; line-height: 1.35; }

  /* The list panel */
  .rs-panel { background: var(--card); border: 1px solid var(--rs-line); border-radius: 14px; padding: 1.2rem 1.3rem 1.4rem; }
  .rs-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1.1rem; }
  .rs-views { display: inline-flex; flex-wrap: wrap; gap: 0.3rem; padding: 0.3rem; border-radius: 999px; background: var(--rs-soft); border: 1px solid var(--rs-line); }
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
  .rs-showing { margin: 0; color: var(--fg-muted); font-size: 0.8rem; display: flex; align-items: center; gap: 0.45rem; flex-wrap: wrap; }
  .rs-live { display: inline-flex; align-items: center; gap: 0.35rem; }
  .rs-live::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--rs-ok); }
  .rs-link {
    background: none; border: 0; padding: 0; cursor: pointer; color: var(--accent);
    font: 600 0.8rem/1 var(--font-body, 'Outfit', sans-serif); text-decoration: underline; text-underline-offset: 2px;
  }

  /* Order cards */
  .rs-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
  .rs-card { border: 1px solid var(--rs-line); border-radius: 14px; background: var(--rs-soft); padding: 1.05rem 1.1rem 1rem; display: flex; flex-direction: column; gap: 0.85rem; }
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

  @media (max-width: 1100px) {
    .rs-flow { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .rs-stage::after { display: none; }
  }
  @media (max-width: 560px) {
    .rs { padding: 1.1rem 1rem 2.5rem; }
    .rs-flow { grid-template-columns: minmax(0, 1fr); }
    .rs-views { width: 100%; }
    .rs-view { flex: 1; justify-content: center; }
  }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_ROOM_SERVICE = {
    backUrl: @json(route('students.dashboard', ['section' => 'tasks'])),
    ordersUrl: @json(route('students.hotel.orders.index')),
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
  { status: 'Preparing',  label: 'Being cooked',     short: 'Cooking',    icon: 'fa-fire-burner',                hint: 'The kitchen is making it.' },
  { status: 'Ready',      label: 'Ready to send up', short: 'Ready',      icon: 'fa-bell-concierge',             hint: 'Plated and waiting to go up.' },
  { status: 'Delivering', label: 'On the way',       short: 'On the way', icon: 'fa-person-walking-arrow-right', hint: 'Heading to the guest’s room.' },
  { status: 'Completed',  label: 'Delivered',        short: 'Delivered',  icon: 'fa-circle-check',               hint: 'With the guest and on their bill.' },
];
const STAGE_BY_STATUS = STAGES.reduce((map, s) => { map[s.status] = s; return map; }, {});
const ACTIVE_STATUSES = ['Preparing', 'Ready', 'Delivering'];
// Cancelled is kept for the one order cancelled under the old rule; it counts as
// finished, never as active.
const FINISHED_STATUSES = ['Completed', 'Cancelled'];

// The three views a desk clerk switches between. A stage card narrows the list
// further to just that step.
const VIEWS = [
  { key: 'active', label: 'Active orders', match: o => ACTIVE_STATUSES.indexOf(o.status) !== -1 },
  { key: 'done',   label: 'Delivered',     match: o => FINISHED_STATUSES.indexOf(o.status) !== -1 },
  { key: 'all',    label: 'All orders',    match: () => true },
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

function RoomServicePage({ orders, onBack }) {
  // 'active' | 'done' | 'all', or a stage's status when a step card is picked.
  const [view, setView] = useState('active');

  const roomServiceOrders = (orders || [])
    .filter(o => o.orderType !== 'dine_in')
    .sort((a, b) => (a.id < b.id ? 1 : -1));

  const countOf = status => roomServiceOrders.filter(o => o.status === status).length;
  const viewDef = VIEWS.find(v => v.key === view);
  const pickedStage = viewDef ? null : STAGE_BY_STATUS[view];

  const visible = roomServiceOrders.filter(o => (viewDef ? viewDef.match(o) : o.status === view));
  const activeCount = roomServiceOrders.filter(VIEWS[0].match).length;

  // Picking the step that is already picked goes back to every active order.
  const pickStage = status => setView(current => (current === status ? 'active' : status));

  let emptyTitle = 'Nothing here right now';
  let emptyText = 'There are no orders in this list.';
  if (roomServiceOrders.length === 0) {
    emptyTitle = 'No room-service orders yet';
    emptyText = 'When a checked-in guest orders food to their room from the hotel website’s Restaurant page, the order will appear here.';
  } else if (pickedStage) {
    emptyTitle = `No orders are ${pickedStage.label.toLowerCase()}`;
    emptyText = 'Pick another step above, or show every active order.';
  } else if (view === 'active') {
    emptyTitle = 'All caught up';
    emptyText = 'Every order has been delivered. New orders will appear here as guests place them.';
  } else if (view === 'done') {
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
            Food that checked-in guests have ordered to their rooms. The kitchen moves each
            order along, so there is nothing to press here. Use this page to see where an
            order is if a guest calls the desk.
          </p>
        </div>
        <button type="button" className="rs-btn" onClick={onBack}>
          <i className="fa-solid fa-arrow-left"></i> Back to Front Desk
        </button>
      </header>

      <p className="rs-flow-title">Where the orders are now</p>
      <div className="rs-flow">
        {STAGES.map(s => (
          <button
            key={s.status}
            type="button"
            className={`rs-stage ${view === s.status ? 'is-on' : ''}`}
            aria-pressed={view === s.status}
            title={`Show only orders that are ${s.label.toLowerCase()}`}
            onClick={() => pickStage(s.status)}
          >
            <span className="rs-stage-icon"><i className={`fa-solid ${s.icon}`}></i></span>
            <span className="rs-stage-text">
              <b>{countOf(s.status)}</b>
              <span>{s.label}</span>
              <small>{s.hint}</small>
            </span>
          </button>
        ))}
      </div>

      <section className="rs-panel">
        <div className="rs-toolbar">
          <div className="rs-views" role="group" aria-label="Which orders to show">
            {VIEWS.map(v => (
              <button
                key={v.key}
                type="button"
                className={`rs-view ${view === v.key ? 'is-on' : ''}`}
                aria-pressed={view === v.key}
                onClick={() => setView(v.key)}
              >
                {v.label}
                <span className="rs-count">{roomServiceOrders.filter(v.match).length}</span>
              </button>
            ))}
          </div>
          <p className="rs-showing">
            {pickedStage ? (
              <>
                Showing only orders that are <b style={{ color: 'var(--fg)' }}>{pickedStage.label.toLowerCase()}</b>.
                <button type="button" className="rs-link" onClick={() => setView('active')}>Show all active orders</button>
              </>
            ) : (
              <>
                <span className="rs-live">Updates on its own</span>
                <span>· {plural(activeCount, 'order', 'orders')} not delivered yet</span>
              </>
            )}
          </p>
        </div>

        {visible.length === 0 ? (
          <div className="rs-empty">
            <div className="rs-empty-icon"><i className="fa-solid fa-bell-concierge"></i></div>
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

  // Nothing on this page writes, so the poll never has to worry about racing an
  // in-flight update of its own — whatever the kitchen last saved is the truth.
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
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
