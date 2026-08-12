@extends('students.builder.ops-shell')

@php $backRoute = 'students.frontdesk'; @endphp

@section('page-title', 'Reports')

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
  .booking-input {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: 'Outfit', sans-serif; font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus { border-color: var(--accent); }
  .booking-input::placeholder { color: var(--fg-muted); opacity: 0.5; }
  .rp-tab {
    padding: 0.4rem 0.9rem; border-radius: 999px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer;
    font-family: 'Outfit', sans-serif; font-size: 0.72rem; font-weight: 600;
    letter-spacing: 0.06em; transition: all 0.15s;
  }
  .rp-tab:hover { color: var(--fg); }
  .rp-tab.is-active { border-color: var(--accent); background: var(--accent); color: var(--bg); }
  .rp-tile {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 0.9rem 1.05rem;
  }
  .rp-tile-label {
    display: block; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--fg-muted); margin-bottom: 0.35rem;
  }
  .rp-tile-value {
    font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: var(--fg);
  }
  .rp-table { width: 100%; border-collapse: collapse; font-family: 'Outfit', sans-serif; }
  .rp-table th {
    padding: 0.6rem 0.85rem; font-size: 0.62rem; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase; color: var(--fg-muted);
    border-bottom: 1px solid var(--border); white-space: nowrap;
    text-align: left; background: rgba(255,255,255,0.02);
  }
  .rp-table td {
    padding: 0.7rem 0.85rem; font-size: 0.8rem; color: var(--fg-muted);
    border-bottom: 1px solid rgba(42,38,33,0.5); vertical-align: middle;
  }
  .rp-strong { color: var(--fg); font-weight: 600; }
  .rp-money { color: var(--accent-light); font-family: 'Playfair Display', serif; font-weight: 700; white-space: nowrap; }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_REPORTS = {
    backUrl: @json(route($backRoute)),
    bookingsUrl: @json(route('students.hotel.bookings.index')),
    ordersUrl: @json(route('students.hotel.orders.index')),
    complaintsUrl: @json(route('students.hotel.complaints.index')),
    inspectionsUrl: @json(route('students.hotel.inspections.index')),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo, useRef } = React;

const CFG = window.HMS_REPORTS;

/* Every section here is the finished end of one pipeline: a stay that was checked
   out, an order the kitchen closed, a complaint a department resolved, a room
   housekeeping cleared. Nothing on this page writes — it reads the same endpoints
   the working screens do and keeps only the terminal rows. */
const TABS = [
  { key: 'stays', label: 'Completed Stays' },
  { key: 'orders', label: 'Completed Orders' },
  { key: 'complaints', label: 'Resolved Complaints' },
  { key: 'inspections', label: 'Completed Inspections' },
];

function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0';
  return '₱' + n.toLocaleString(undefined, { maximumFractionDigits: 2 });
}

function formatWhen(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString([], { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function formatDate(value) {
  const raw = String(value || '').trim();
  if (!raw) return '—';
  const [y, m, d] = raw.split('-').map(Number);
  if (!y || !m || !d) return raw;
  return new Date(y, m - 1, d).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function getJson(url) {
  return fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
    .then(r => (r.ok ? r.json() : {}))
    .catch(() => ({}));
}

function Tile({ label, value }) {
  return (
    <div className="rp-tile">
      <span className="rp-tile-label">{label}</span>
      <span className="rp-tile-value">{value}</span>
    </div>
  );
}

function Table({ head, rows, empty }) {
  if (!rows.length) {
    return (
      <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem', textAlign: 'center' }}>
        <i className="fa-solid fa-clipboard-list" style={{ fontSize: '1.7rem', color: 'var(--fg-muted)', opacity: 0.3, display: 'block', marginBottom: '0.7rem' }}></i>
        <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.86rem' }}>{empty}</p>
      </div>
    );
  }
  return (
    <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
      <table className="rp-table">
        <thead><tr>{head.map(h => <th key={h}>{h}</th>)}</tr></thead>
        <tbody>{rows}</tbody>
      </table>
    </div>
  );
}

function App() {
  const [bookings, setBookings] = useState([]);
  const [orders, setOrders] = useState([]);
  const [complaints, setComplaints] = useState([]);
  const [inspections, setInspections] = useState([]);
  const [tab, setTab] = useState('stays');
  const [search, setSearch] = useState('');
  const [loaded, setLoaded] = useState(false);

  const load = useCallback(() => {
    Promise.all([
      getJson(CFG.bookingsUrl),
      getJson(CFG.ordersUrl),
      getJson(CFG.complaintsUrl),
      getJson(CFG.inspectionsUrl),
    ]).then(([b, o, c, i]) => {
      setBookings(Array.isArray(b.bookings) ? b.bookings : []);
      setOrders(Array.isArray(o.orders) ? o.orders : []);
      setComplaints(Array.isArray(c.complaints) ? c.complaints : []);
      setInspections(Array.isArray(i.inspections) ? i.inspections : []);
      setLoaded(true);
    });
  }, []);

  useEffect(() => {
    load();
    const id = setInterval(load, 15000);
    window.addEventListener('focus', load);
    return () => { clearInterval(id); window.removeEventListener('focus', load); };
  }, [load]);

  const doneStays = useMemo(
    () => bookings.filter(b => b.status === 'Checked Out').sort((a, b) => (a.bookingId < b.bookingId ? 1 : -1)),
    [bookings]
  );
  const doneOrders = useMemo(
    () => orders.filter(o => o.status === 'Completed').sort((a, b) => (a.id < b.id ? 1 : -1)),
    [orders]
  );
  const doneComplaints = useMemo(
    () => complaints.filter(c => c.status === 'Resolved').sort((a, b) => (a.id < b.id ? 1 : -1)),
    [complaints]
  );
  const doneInspections = useMemo(
    () => inspections.filter(i => i.status === 'Completed').sort((a, b) => (a.id < b.id ? 1 : -1)),
    [inspections]
  );

  // Revenue is counted from what guests actually paid on closed stays, not from what
  // was billed — an unsettled balance is not money the hotel took.
  const revenue = useMemo(
    () => doneStays.reduce((sum, b) => sum + (Number(b.amountPaid) || 0), 0),
    [doneStays]
  );

  const match = (fields) => {
    const q = search.trim().toLowerCase();
    if (!q) return true;
    return fields.some(f => String(f || '').toLowerCase().includes(q));
  };

  const stayRows = doneStays
    .filter(b => match([b.fullName, b.roomName, b.bookedBy, b.checkIn, b.checkOut]))
    .map(b => (
      <tr key={b.bookingId}>
        <td className="rp-strong">{b.fullName || '—'}</td>
        <td>{b.roomName || '—'}</td>
        <td>{formatDate(b.checkIn)}</td>
        <td>{formatDate(b.checkOut)}</td>
        <td>{formatWhen(b.checkedOutAt)}</td>
        <td className="rp-money">{formatPeso(b.grandTotal)}</td>
        <td className="rp-money">{formatPeso(b.amountPaid)}</td>
      </tr>
    ));

  const orderRows = doneOrders
    .filter(o => match([o.guestName, o.roomNumber, o.placedBy, (o.items || []).map(i => i.name).join(' ')]))
    .map(o => (
      <tr key={o.id}>
        <td className="rp-strong">#{o.id}</td>
        <td>{o.orderType === 'dine_in' ? 'Dine-In' : 'Room Service'}</td>
        <td>{o.guestName || '—'}</td>
        <td>{o.orderType === 'dine_in' ? '—' : (o.roomNumber || '—')}</td>
        <td style={{ whiteSpace: 'normal', minWidth: 200 }}>
          {(o.items || []).map(i => `${i.name} ×${i.qty}`).join(', ') || '—'}
        </td>
        <td>{formatWhen(o.updatedAt)}</td>
        <td className="rp-money">{formatPeso(o.total)}</td>
      </tr>
    ));

  const complaintRows = doneComplaints
    .filter(c => match([c.roomNumber, c.guestName, c.category, c.details, c.handledBy]))
    .map(c => (
      <tr key={c.id}>
        <td className="rp-strong">{c.roomNumber || '—'}</td>
        <td>{c.guestName || '—'}</td>
        <td>{c.category}</td>
        <td>{c.departmentLabel}</td>
        <td style={{ whiteSpace: 'normal', minWidth: 200 }}>{c.resolutionNote || '—'}</td>
        <td>{c.handledBy || '—'}</td>
        <td>{formatWhen(c.resolvedAt)}</td>
      </tr>
    ));

  const inspectionRows = doneInspections
    .filter(i => match([i.roomName, i.guestName, i.finding, i.completedBy]))
    .map(i => (
      <tr key={i.id}>
        <td className="rp-strong">{i.roomName || '—'}</td>
        <td>{i.guestName || '—'}</td>
        <td>{i.finding || '—'}</td>
        <td>{i.issues.length ? `${i.issues.length} reported` : 'None'}</td>
        <td>{i.completedBy || '—'}</td>
        <td>{formatWhen(i.completedAt)}</td>
      </tr>
    ));

  return (
    <div data-hms-no-edit="1" style={{ maxWidth: 1100, margin: '0 auto', padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Front Desk</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Reports</h1>
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem' }}>
            Everything the team has finished — closed stays, completed orders, resolved complaints and cleared rooms.
          </p>
        </div>
        <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </a>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(170px, 1fr))', gap: '0.75rem', marginBottom: '1.35rem' }}>
        <Tile label="Revenue Collected" value={formatPeso(revenue)} />
        <Tile label="Completed Stays" value={doneStays.length} />
        <Tile label="Completed Orders" value={doneOrders.length} />
        <Tile label="Resolved Complaints" value={doneComplaints.length} />
        <Tile label="Rooms Cleared" value={doneInspections.length} />
      </div>

      <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap', marginBottom: '1rem' }}>
        {TABS.map(t => (
          <button key={t.key} type="button" className={`rp-tab ${tab === t.key ? 'is-active' : ''}`} onClick={() => setTab(t.key)}>
            {t.label}
          </button>
        ))}
      </div>

      <div style={{ position: 'relative', marginBottom: '1.1rem' }}>
        <i className="fa-solid fa-magnifying-glass" style={{ position: 'absolute', left: '0.75rem', top: '50%', transform: 'translateY(-50%)', color: 'var(--fg-muted)', fontSize: '0.75rem', pointerEvents: 'none' }}></i>
        <input
          type="text"
          className="booking-input"
          placeholder="Search these records…"
          value={search}
          onChange={e => setSearch(e.target.value)}
          style={{ paddingLeft: '2.1rem' }}
        />
      </div>

      {!loaded ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.85rem' }}>
          Loading reports…
        </div>
      ) : tab === 'stays' ? (
        <Table
          head={['Guest', 'Room', 'Check-In', 'Check-Out', 'Closed', 'Total Billed', 'Paid']}
          rows={stayRows}
          empty="No stays have been checked out yet."
        />
      ) : tab === 'orders' ? (
        <Table
          head={['Order', 'Type', 'Guest', 'Room', 'Items', 'Completed', 'Total']}
          rows={orderRows}
          empty="No orders have been completed yet."
        />
      ) : tab === 'complaints' ? (
        <Table
          head={['Room', 'Guest', 'Category', 'Department', 'Resolution', 'Handled By', 'Resolved']}
          rows={complaintRows}
          empty="No complaints have been resolved yet."
        />
      ) : (
        <Table
          head={['Room', 'Previous Guest', 'Finding', 'Issues', 'Completed By', 'Completed']}
          rows={inspectionRows}
          empty="No room inspections have been completed yet."
        />
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
