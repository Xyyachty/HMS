@extends('students.builder.ops-shell')

@php $backRoute = 'students.frontdesk'; @endphp

@section('page-title', 'Room Service')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: 'Outfit', sans-serif; }
  .font-display { font-family: 'Playfair Display', serif; }
  .btn-outline {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: transparent; color: var(--accent);
    font-family: 'Outfit', sans-serif; font-weight: 500;
    font-size: 0.75rem; letter-spacing: 0.1em; text-transform: uppercase;
    padding: 0.6rem 1.3rem; border: 1px solid var(--accent); border-radius: 6px;
    cursor: pointer; transition: background 0.2s, color 0.2s, transform 0.2s;
    text-decoration: none;
  }
  .btn-outline:hover { background: var(--accent); color: var(--bg); transform: translateY(-1px); }
  .btn-solid {
    display: inline-flex; align-items: center; gap: 0.45rem;
    background: var(--accent); color: var(--bg); border: 1px solid var(--accent);
    font-family: 'Outfit', sans-serif; font-weight: 600;
    font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.55rem 1.1rem; border-radius: 6px; cursor: pointer;
    transition: filter 0.2s;
  }
  .btn-solid:hover { filter: brightness(1.1); }
  .btn-solid:disabled { opacity: 0.45; cursor: not-allowed; filter: none; }
  .rs-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; display: inline-block;
  }
  .rs-pending   { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35); }
  .rs-preparing { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .rs-ready     { background: rgba(56,189,248,0.18); color: #38bdf8; border-color: rgba(56,189,248,0.35); }
  .rs-delivering { background: rgba(34,197,94,0.18); color: #4ade80; border-color: rgba(34,197,94,0.35); }
  .rs-completed { background: rgba(20,148,80,0.18); color: #34d399; border-color: rgba(20,148,80,0.35); }
  .rs-cancelled { background: rgba(148,163,184,0.15); color: #94a3b8; border-color: rgba(148,163,184,0.3); }
  .rs-tab {
    padding: 0.35rem 0.8rem; border-radius: 999px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer;
    font-family: 'Outfit', sans-serif; font-size: 0.68rem; font-weight: 600;
    letter-spacing: 0.06em; transition: all 0.15s;
  }
  .rs-tab:hover { color: var(--fg); }
  .rs-tab.is-active { border-color: var(--accent); background: var(--accent); color: var(--bg); }
  .rs-card {
    border: 1px solid var(--border); border-radius: 12px;
    background: rgba(255,255,255,0.02); padding: 1rem 1.1rem;
  }
  .rs-card-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 0.75rem; margin-bottom: 0.75rem;
  }
  .rs-card-id {
    font-family: 'Playfair Display', serif; font-size: 1.05rem;
    font-weight: 700; color: var(--fg);
  }
  .rs-field { display: flex; gap: 0.5rem; font-size: 0.82rem; margin-bottom: 0.3rem; }
  .rs-field dt { color: var(--fg-muted); min-width: 58px; flex-shrink: 0; }
  .rs-field dd { color: var(--fg); margin: 0; }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_ROOM_SERVICE = {
    backUrl: @json(route('students.frontdesk')),
    ordersUrl: @json(route('students.hotel.orders.index')),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback } = React;

const CFG = window.HMS_ROOM_SERVICE;

// Mirrors App\Models\HotelFoodOrder::FLOW. Every one of these transitions belongs to
// Restaurant Services, delivery included — this page is a window onto their queue.
const ORDER_FLOW = ['Pending', 'Preparing', 'Ready', 'Delivering', 'Completed'];
const OPEN_ORDER_STATUSES = ['Pending', 'Preparing', 'Ready'];

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

function RoomServicePage({ orders, onBack }) {
  const [filter, setFilter] = useState('Open');

  const roomServiceOrders = (orders || [])
    .filter(o => o.orderType !== 'dine_in')
    .sort((a, b) => (a.id < b.id ? 1 : -1));

  const visible = roomServiceOrders.filter(o => (
    filter === 'All' ? true
      : filter === 'Open' ? OPEN_ORDER_STATUSES.indexOf(o.status) !== -1
      : o.status === filter
  ));

  const movingCount = roomServiceOrders.filter(o => o.status === 'Ready' || o.status === 'Delivering').length;
  const filters = ['Open', 'All', ...ORDER_FLOW.slice(1), 'Cancelled'];

  return (
    <div style={{ maxWidth: 1100, margin: '0 auto', padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Front Desk</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Room Service</h1>
        </div>
        <button type="button" className="btn-outline" onClick={onBack} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back to Front Desk
        </button>
      </div>

      <div style={{ background: 'var(--card)', border: '1px solid var(--border)', borderRadius: 14, padding: '1.5rem 1.6rem 1.75rem' }}>
        <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', marginBottom: '1rem' }}>
          {roomServiceOrders.length === 0
            ? 'No room-service orders yet. Place one from the hotel site’s Restaurant page for a checked-in guest.'
            : movingCount > 0
              ? `${movingCount} order${movingCount === 1 ? '' : 's'} plated and heading up to the rooms.`
              : 'Nothing is moving right now. The kitchen updates this as it works.'}
        </p>

        <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap', marginBottom: '1.1rem' }}>
          {filters.map(f => (
            <button key={f} type="button" className={`rs-tab ${filter === f ? 'is-active' : ''}`} onClick={() => setFilter(f)}>{f}</button>
          ))}
        </div>

        {visible.length === 0 ? (
          <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center' }}>
            <i className="fa-solid fa-bell-concierge" style={{ fontSize: '1.6rem', color: 'var(--fg-muted)', opacity: 0.3, display: 'block', marginBottom: '0.65rem' }}></i>
            <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.85rem' }}>No orders in this view.</p>
          </div>
        ) : (
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))', gap: '1rem' }}>
            {visible.map(order => (
              <div key={order.id} className="rs-card">
                <div className="rs-card-head">
                  <span className="rs-card-id">Order #{order.id}</span>
                  <span className={`rs-badge rs-${order.status.toLowerCase()}`}>{order.status}</span>
                </div>

                <dl style={{ margin: '0 0 0.75rem' }}>
                  <div className="rs-field"><dt>Guest</dt><dd>{order.guestName || '—'}</dd></div>
                  <div className="rs-field"><dt>Room</dt><dd>{order.roomNumber || '—'}</dd></div>
                  <div className="rs-field">
                    <dt>Order</dt>
                    <dd>{(order.items || []).map(i => `${i.name} ×${i.qty}`).join(', ') || '—'}</dd>
                  </div>
                  <div className="rs-field"><dt>Time</dt><dd>{formatOrderTime(order.placedAt)}</dd></div>
                  <div className="rs-field">
                    <dt>Total</dt>
                    <dd style={{ color: 'var(--accent-light)', fontWeight: 700 }}>{formatPeso(order.total)}</dd>
                  </div>
                </dl>

                <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.74rem' }}>
                  {order.status === 'Pending' ? 'Waiting for the kitchen to accept it.'
                    : order.status === 'Preparing' ? 'The kitchen is cooking it.'
                    : order.status === 'Ready' ? 'Plated. The kitchen is about to bring it up.'
                    : order.status === 'Delivering' ? 'On the way to the guest’s room.'
                    : order.status === 'Completed' ? 'Delivered and closed. Charged to the guest’s bill.'
                    : 'Cancelled — the portions went back to stock.'}
                </p>
              </div>
            ))}
          </div>
        )}
      </div>
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
