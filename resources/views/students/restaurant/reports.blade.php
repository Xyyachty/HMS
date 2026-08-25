@extends('students.builder.ops-shell')

@php $backRoute = 'students.restaurant'; @endphp

@section('page-title', 'Reports')

@section('head-extra')
@include('students.partials.reports-styles')
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_REPORTS = {
    backUrl: @json(route($backRoute)),
    ordersUrl: @json(route('students.hotel.orders.index')),
    tablesUrl: @json(route('students.hotel.tables.index')),
    // Only to tell a stay that was cancelled from one that is still running:
    // a room-service order billed to a cancelled stay never earned anything.
    bookingsUrl: @json(route('students.hotel.bookings.index')),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo } = React;

const CFG = window.HMS_REPORTS;
const PER_PAGE = 5;

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

function formatRange(from, to) {
  if (!from && !to) return 'all time';
  if (from && to && from === to) return formatDate(from);
  return `${from ? formatDate(from) : 'the start'} – ${to ? formatDate(to) : 'now'}`;
}

/* A local YYYY-MM-DD. Never toISOString(): that renders the UTC day, so in Manila
   anything before 08:00 comes back as yesterday and "Today" quietly loses the whole
   morning. Built from the local getters, which is what the kitchen's wall clock, the
   date inputs on this page, and formatWhen() all already agree on. */
function ymd(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

/* Ranges run up to today, never past it: a week that has not happened yet would pad
   the range with empty days and make "This Week" and "Today" disagree about their
   upper bound for no reason.

   The week is Monday-to-today. A service week is read against a work week, and a
   Sunday start would split the weekend across two reports. getDay() returns 0 for
   Sunday, which is day 7 of the week that just ended, not day 1 of the next one. */
function presetRange(preset, now = new Date()) {
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  if (preset === 'today') {
    return { from: ymd(today), to: ymd(today) };
  }
  if (preset === 'week') {
    const dow = today.getDay() === 0 ? 7 : today.getDay();
    const monday = new Date(today.getFullYear(), today.getMonth(), today.getDate() - (dow - 1));
    return { from: ymd(monday), to: ymd(today) };
  }
  if (preset === 'month') {
    const first = new Date(today.getFullYear(), today.getMonth(), 1);
    return { from: ymd(first), to: ymd(today) };
  }
  return null; // 'custom' keeps whatever the two date inputs already hold
}

/* An order is bucketed by the day it reads as on a local clock — the same day
   formatWhen() prints beside it. */
function localDayKey(value) {
  const raw = String(value || '').trim();
  if (!raw) return '';
  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;
  const d = new Date(raw);
  return Number.isNaN(d.getTime()) ? '' : ymd(d);
}

/* Both bounds inclusive; a row with no usable date is out of every window. */
function inWindow(day, from, to) {
  if (!day) return false;
  if (from && day < from) return false;
  if (to && day > to) return false;
  return true;
}

function itemsSummary(items) {
  const list = Array.isArray(items) ? items : [];
  const count = list.reduce((n, it) => n + (Number(it.qty) || 0), 0);
  const title = list.map(it => `${it.qty}× ${it.name}`).join(', ');
  return { count, title };
}

function Tile({ label, value, sub, grand }) {
  return (
    <div className={'rp-tile' + (grand ? ' rp-tile-grand' : '')}>
      <span className="rp-tile-label">{label}</span>
      <span className="rp-tile-value">{value}</span>
      {sub ? <span className="rp-tile-sub">{sub}</span> : null}
    </div>
  );
}

function EmptyState({ icon, message }) {
  return (
    <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem', textAlign: 'center' }}>
      <i className={'fa-solid ' + icon} style={{ fontSize: '1.7rem', color: 'var(--fg-muted)', opacity: 0.3, display: 'block', marginBottom: '0.7rem' }}></i>
      <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.86rem' }}>{message}</p>
    </div>
  );
}

function Pager({ page, totalPages, total, perPage, onPage }) {
  if (totalPages <= 1) return null;
  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: '0.85rem', gap: '0.5rem', flexWrap: 'wrap' }}>
      <span style={{ fontSize: '0.75rem', color: 'var(--fg-muted)' }}>
        Showing {(page - 1) * perPage + 1}–{Math.min(page * perPage, total)} of {total}
      </span>
      <div style={{ display: 'flex', gap: '0.35rem', flexWrap: 'wrap' }}>
        <button
          type="button"
          onClick={() => onPage(Math.max(1, page - 1))}
          disabled={page === 1}
          style={{ padding: '0.35rem 0.7rem', borderRadius: 6, border: '1px solid var(--border)', background: 'transparent', color: page === 1 ? 'var(--fg-muted)' : 'var(--fg)', cursor: page === 1 ? 'default' : 'pointer', fontSize: '0.78rem', opacity: page === 1 ? 0.4 : 1 }}
        >
          <i className="fa-solid fa-chevron-left" style={{ fontSize: '0.65rem' }}></i>
        </button>
        {Array.from({ length: totalPages }, (_, i) => i + 1).map(n => (
          <button
            key={n}
            type="button"
            onClick={() => onPage(n)}
            style={{ padding: '0.35rem 0.65rem', borderRadius: 6, border: '1px solid ' + (n === page ? 'var(--accent)' : 'var(--border)'), background: n === page ? 'var(--accent)' : 'transparent', color: n === page ? 'var(--bg)' : 'var(--fg-muted)', cursor: 'pointer', fontSize: '0.78rem', fontWeight: n === page ? 700 : 400 }}
          >
            {n}
          </button>
        ))}
        <button
          type="button"
          onClick={() => onPage(Math.min(totalPages, page + 1))}
          disabled={page === totalPages}
          style={{ padding: '0.35rem 0.7rem', borderRadius: 6, border: '1px solid var(--border)', background: 'transparent', color: page === totalPages ? 'var(--fg-muted)' : 'var(--fg)', cursor: page === totalPages ? 'default' : 'pointer', fontSize: '0.78rem', opacity: page === totalPages ? 0.4 : 1 }}
        >
          <i className="fa-solid fa-chevron-right" style={{ fontSize: '0.65rem' }}></i>
        </button>
      </div>
    </div>
  );
}

/* The two kinds of order this department serves. Front Desk's report adds room
   stays on top of these; the kitchen only ever cooked these two. */
function TabBar({ tab, onTab, counts }) {
  const tabs = [
    { key: 'dinein', label: 'Dine-In', count: counts.dinein },
    { key: 'roomsvc', label: 'Room Service', count: counts.roomsvc },
  ];
  return (
    <div className="rp-tabs">
      {tabs.map(t => (
        <button
          key={t.key}
          type="button"
          className={'rp-tab' + (tab === t.key ? ' is-active' : '')}
          onClick={() => onTab(t.key)}
        >
          {t.label}
          <span className="rp-tab-count">({t.count})</span>
        </button>
      ))}
    </div>
  );
}

function PresetBar({ preset, from, to, onPreset, onFrom, onTo }) {
  const presets = [
    { key: 'today', label: 'Today' },
    { key: 'week', label: 'This Week' },
    { key: 'month', label: 'This Month' },
    { key: 'custom', label: 'Custom' },
  ];
  return (
    <>
      <div>
        <label className="rp-tile-label">Period</label>
        <div className="rp-presets">
          {presets.map(p => (
            <button
              key={p.key}
              type="button"
              className={'rp-preset' + (preset === p.key ? ' is-active' : '')}
              onClick={() => onPreset(p.key)}
            >
              {p.label}
            </button>
          ))}
        </div>
      </div>
      <div>
        <label className="rp-tile-label">From</label>
        <input type="date" className="booking-input" value={from} onChange={e => onFrom(e.target.value)} style={{ minWidth: 150 }} />
      </div>
      <div>
        <label className="rp-tile-label">To</label>
        <input type="date" className="booking-input" value={to} onChange={e => onTo(e.target.value)} style={{ minWidth: 150 }} />
      </div>
    </>
  );
}

function DineInTable({ rows, total, page, onPage, rangeLabel, tableNameById }) {
  const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageRows = rows.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  if (rows.length === 0) {
    return <EmptyState icon="fa-utensils" message={`No dine-in orders were completed between ${rangeLabel}.`} />;
  }

  return (
    <>
      <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
        <table className="rp-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Table</th>
              <th>Guest</th>
              <th className="rp-num">Items</th>
              <th className="rp-num">Total</th>
              <th>Placed By</th>
              <th>Placed</th>
            </tr>
          </thead>
          <tbody>
            {pageRows.map(o => {
              const { count, title } = itemsSummary(o.items);
              return (
                <tr key={o.id}>
                  <td className="rp-strong">#{o.id}</td>
                  <td>{o.tableId ? (tableNameById[o.tableId] || `#${o.tableId}`) : '—'}</td>
                  <td>{o.guestName || '—'}</td>
                  <td className="rp-num" title={title}>{count}</td>
                  <td className="rp-num rp-money">{o.total > 0 ? formatPeso(o.total) : <span className="rp-zero">₱0</span>}</td>
                  <td>{o.placedBy || '—'}</td>
                  <td>{formatWhen(o.placedAt)}</td>
                </tr>
              );
            })}
          </tbody>
          <tfoot>
            <tr>
              <td colSpan={4}>{rows.length} order{rows.length === 1 ? '' : 's'}</td>
              <td className="rp-num rp-money">{formatPeso(total)}</td>
              <td colSpan={2}></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <Pager page={safePage} totalPages={totalPages} total={rows.length} perPage={PER_PAGE} onPage={onPage} />
    </>
  );
}

function RoomServiceTable({ rows, total, page, onPage, rangeLabel, openStayTotal }) {
  const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageRows = rows.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  if (rows.length === 0) {
    return <EmptyState icon="fa-bell-concierge" message={`No room service orders were completed between ${rangeLabel}.`} />;
  }

  return (
    <>
      <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
        <table className="rp-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Room</th>
              <th>Guest</th>
              <th className="rp-num">Items</th>
              <th className="rp-num">Total</th>
              <th>Placed By</th>
              <th>Placed</th>
            </tr>
          </thead>
          <tbody>
            {pageRows.map(o => {
              const { count, title } = itemsSummary(o.items);
              return (
                <tr key={o.id}>
                  <td className="rp-strong">#{o.id}</td>
                  <td>
                    {o.roomNumber || '—'}
                    {o.stayOpen && <span className="rp-badge rp-cat-room" style={{ marginLeft: '0.4rem' }}>Open stay</span>}
                  </td>
                  <td>{o.guestName || '—'}</td>
                  <td className="rp-num" title={title}>{count}</td>
                  <td className="rp-num rp-money">{o.total > 0 ? formatPeso(o.total) : <span className="rp-zero">₱0</span>}</td>
                  <td>{o.placedBy || '—'}</td>
                  <td>{formatWhen(o.placedAt)}</td>
                </tr>
              );
            })}
          </tbody>
          <tfoot>
            <tr>
              <td colSpan={4}>{rows.length} order{rows.length === 1 ? '' : 's'}</td>
              <td className="rp-num rp-money">{formatPeso(total)}</td>
              <td colSpan={2}></td>
            </tr>
          </tfoot>
        </table>
      </div>
      {openStayTotal > 0 && (
        <p className="rp-note">{formatPeso(openStayTotal)} of this is on stays that have not checked out yet.</p>
      )}
      <Pager page={safePage} totalPages={totalPages} total={rows.length} perPage={PER_PAGE} onPage={onPage} />
    </>
  );
}

function App() {
  const [orders, setOrders] = useState([]);
  const [tables, setTables] = useState([]);
  const [bookings, setBookings] = useState([]);
  const [loadedOrders, setLoadedOrders] = useState(false);
  const [loadedTables, setLoadedTables] = useState(false);
  const [loadedBookings, setLoadedBookings] = useState(false);

  const [tab, setTab] = useState('dinein');
  const [preset, setPreset] = useState('month');
  const [from, setFrom] = useState(() => presetRange('month').from);
  const [to, setTo] = useState(() => presetRange('month').to);
  const [search, setSearch] = useState('');
  const [pages, setPages] = useState({ dinein: 1, roomsvc: 1 });

  const load = useCallback(() => {
    // Asked for Completed only: this page reports what the kitchen finished, not
    // what is still on the pass.
    fetch(CFG.ordersUrl + '?status=Completed', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : {}))
      .then(data => { setOrders(Array.isArray(data.orders) ? data.orders : []); setLoadedOrders(true); })
      .catch(() => setLoadedOrders(true));

    fetch(CFG.tablesUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : {}))
      .then(data => { setTables(Array.isArray(data.tables) ? data.tables : []); setLoadedTables(true); })
      .catch(() => setLoadedTables(true));

    fetch(CFG.bookingsUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : {}))
      .then(data => { setBookings(Array.isArray(data.bookings) ? data.bookings : []); setLoadedBookings(true); })
      .catch(() => setLoadedBookings(true));

    // A screen left open across a shift should not keep reporting yesterday's
    // "Today". Only touches state when the computed range actually differs, so
    // this does not thrash the memo chain on every 15s poll.
    setPreset(p => {
      if (p !== 'custom') {
        const r = presetRange(p);
        if (r) {
          setFrom(f => (f === r.from ? f : r.from));
          setTo(t => (t === r.to ? t : r.to));
        }
      }
      return p;
    });
  }, []);

  useEffect(() => {
    load();
    const id = setInterval(load, 15000);
    window.addEventListener('focus', load);
    return () => { clearInterval(id); window.removeEventListener('focus', load); };
  }, [load]);

  const resetPages = () => setPages({ dinein: 1, roomsvc: 1 });

  const applyPreset = (p) => {
    setPreset(p);
    const r = presetRange(p);
    if (r) { setFrom(r.from); setTo(r.to); }
    resetPages();
  };
  const applyFrom = (v) => { setFrom(v); setPreset('custom'); resetPages(); };
  const applyTo = (v) => { setTo(v); setPreset('custom'); resetPages(); };
  const applySearch = (v) => { setSearch(v); resetPages(); };

  // A cancelled stay never happened and never billed anything, so the food
  // charged to it is not revenue however finished the order was.
  const cancelledIds = useMemo(() => {
    const s = new Set();
    bookings.forEach(b => { if (b.status === 'Cancelled') s.add(b.bookingId); });
    return s;
  }, [bookings]);

  // Stays still holding a room. A Completed order on one of these is real money
  // already, just not settled into a final bill yet — worth flagging, not excluding.
  const openStayIds = useMemo(() => {
    const s = new Set();
    bookings.forEach(b => { if (b.status && b.status !== 'Checked Out' && b.status !== 'Cancelled') s.add(b.bookingId); });
    return s;
  }, [bookings]);

  // Orders only carry the raw dine_in_table_id — there is no relation from a food
  // order to its table, so the name is joined here from the separate tables read.
  const tableNameById = useMemo(() => {
    const map = {};
    tables.forEach(t => { map[t.id] = t.name; });
    return map;
  }, [tables]);

  // Belt and braces: the endpoint is already asked for Completed, and this keeps
  // the page honest if that filter ever loosens.
  const foodAll = useMemo(() => orders
    .filter(o => o.status === 'Completed')
    .map(o => ({ ...o, day: localDayKey(o.placedAt), stayOpen: !!o.bookingId && openStayIds.has(o.bookingId) })),
    [orders, openStayIds]);

  const q = search.trim().toLowerCase();
  const matches = (fields) => !q || fields.some(f => String(f || '').toLowerCase().includes(q));

  const dineRows = useMemo(() => foodAll
    .filter(o => o.orderType === 'dine_in')
    .filter(o => inWindow(o.day, from, to))
    .filter(o => matches([o.id, o.tableId, o.guestName, o.placedBy, ...(Array.isArray(o.items) ? o.items.map(it => it.name) : [])]))
    .sort((a, b) => (a.id < b.id ? 1 : -1)),
    [foodAll, from, to, q]);

  const svcRows = useMemo(() => foodAll
    .filter(o => o.orderType === 'room_service')
    .filter(o => !cancelledIds.has(o.bookingId))
    .filter(o => inWindow(o.day, from, to))
    .filter(o => matches([o.id, o.roomNumber, o.guestName, o.placedBy, ...(Array.isArray(o.items) ? o.items.map(it => it.name) : [])]))
    .sort((a, b) => (a.id < b.id ? 1 : -1)),
    [foodAll, cancelledIds, from, to, q]);

  const totals = useMemo(() => {
    const dineIn = dineRows.reduce((sum, o) => sum + (Number(o.total) || 0), 0);
    const roomService = svcRows.reduce((sum, o) => sum + (Number(o.total) || 0), 0);
    const svcOnOpenStay = svcRows.filter(o => o.stayOpen).reduce((sum, o) => sum + (Number(o.total) || 0), 0);
    return { dineIn, roomService, svcOnOpenStay, grand: dineIn + roomService };
  }, [dineRows, svcRows]);

  const counts = { dinein: dineRows.length, roomsvc: svcRows.length };
  const rangeLabel = formatRange(from, to);
  const narrowed = !!search || preset === 'custom';
  const loaded = loadedOrders && loadedTables && loadedBookings;
  const orderCount = counts.dinein + counts.roomsvc;

  return (
    <div data-hms-no-edit="1" style={{ padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Restaurant</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Reports</h1>
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem' }}>
            Orders the kitchen has completed — dine-in and room service.
          </p>
        </div>
        <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </a>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '0.7rem', marginBottom: '1.2rem' }}>
        <Tile label="Dine-In Revenue" value={formatPeso(totals.dineIn)} sub={`${counts.dinein} order${counts.dinein === 1 ? '' : 's'}`} />
        <Tile label="Room Service Revenue" value={formatPeso(totals.roomService)} sub={`${counts.roomsvc} order${counts.roomsvc === 1 ? '' : 's'}`} />
        <Tile label="Total Completed" value={formatPeso(totals.grand)} sub={`${orderCount} order${orderCount === 1 ? '' : 's'} · ${rangeLabel}`} grand />
      </div>

      <TabBar tab={tab} onTab={setTab} counts={counts} />

      <div style={{ display: 'flex', gap: '0.7rem', flexWrap: 'wrap', alignItems: 'flex-end', marginBottom: '1.1rem' }}>
        <div style={{ position: 'relative', flex: 1, minWidth: 220 }}>
          <label className="rp-tile-label">Search</label>
          <div style={{ position: 'relative' }}>
            <i className="fa-solid fa-magnifying-glass" style={{ position: 'absolute', left: '0.75rem', top: '50%', transform: 'translateY(-50%)', color: 'var(--fg-muted)', fontSize: '0.75rem', pointerEvents: 'none' }}></i>
            <input
              type="text"
              className="booking-input"
              placeholder="Search by order, table, room, guest, dish, or who placed it…"
              value={search}
              onChange={e => applySearch(e.target.value)}
              style={{ paddingLeft: '2.1rem' }}
            />
          </div>
        </div>
        <PresetBar preset={preset} from={from} to={to} onPreset={applyPreset} onFrom={applyFrom} onTo={applyTo} />
      </div>

      {!loaded ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.85rem' }}>
          Loading reports…
        </div>
      ) : tab === 'dinein' ? (
        <DineInTable
          rows={dineRows}
          total={totals.dineIn}
          page={pages.dinein}
          onPage={n => setPages(p => ({ ...p, dinein: n }))}
          rangeLabel={rangeLabel}
          tableNameById={tableNameById}
        />
      ) : (
        <RoomServiceTable
          rows={svcRows}
          total={totals.roomService}
          page={pages.roomsvc}
          onPage={n => setPages(p => ({ ...p, roomsvc: n }))}
          rangeLabel={rangeLabel}
          openStayTotal={totals.svcOnOpenStay}
        />
      )}

      {loaded && narrowed && (
        <p className="rp-note">Filtered — {rangeLabel}{search ? `, matching "${search}"` : ''}.</p>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
