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
    border-bottom: 1px solid rgba(42,38,33,0.5); vertical-align: middle; white-space: nowrap;
  }
  .rp-table tfoot td {
    border-top: 1px solid var(--border); border-bottom: none;
    color: var(--fg); font-weight: 600; background: rgba(255,255,255,0.02);
  }
  .rp-strong { color: var(--fg); font-weight: 600; }
  .rp-money { color: var(--accent-light); font-family: 'Playfair Display', serif; font-weight: 700; }
  .rp-num { text-align: right; font-variant-numeric: tabular-nums; }
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
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo } = React;

const CFG = window.HMS_REPORTS;
const BLOCK_HOURS = 12;

/* Completed bookings only: a stay that was actually checked out. Cancelled ones
   never happened and a stay still open is not a result yet, so neither belongs in
   a report of what the hotel did. Read-only — nothing on this page writes. */
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

/* Charged 12-hour blocks, the same maths HotelBooking::stayBlocks() uses. Only a
   fallback for a payload that predates the server sending totals. */
function stayBlocks(checkIn, checkOut, checkInTime) {
  if (!checkIn || !checkOut) return 1;
  const clock = /^\d{1,2}:\d{2}/.test(String(checkInTime || '')) ? checkInTime : '00:00';
  const start = new Date(`${checkIn}T${clock}`);
  const end = new Date(`${checkOut}T${clock}`);
  const hours = (end - start) / 3600000;
  if (!Number.isFinite(hours) || hours <= 0) return 1;
  return Math.max(1, Math.ceil(hours / BLOCK_HOURS));
}

function Tile({ label, value, tone }) {
  return (
    <div className="rp-tile">
      <span className="rp-tile-label">{label}</span>
      <span className="rp-tile-value" style={tone ? { color: tone } : undefined}>{value}</span>
    </div>
  );
}

function App() {
  const [bookings, setBookings] = useState([]);
  const [search, setSearch] = useState('');
  const [from, setFrom] = useState('');
  const [to, setTo] = useState('');
  const [page, setPage] = useState(1);
  const [loaded, setLoaded] = useState(false);

  const load = useCallback(() => {
    fetch(CFG.bookingsUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : {}))
      .then(data => { setBookings(Array.isArray(data.bookings) ? data.bookings : []); setLoaded(true); })
      .catch(() => setLoaded(true));
  }, []);

  useEffect(() => {
    load();
    const id = setInterval(load, 15000);
    window.addEventListener('focus', load);
    return () => { clearInterval(id); window.removeEventListener('focus', load); };
  }, [load]);

  const rows = useMemo(() => {
    const q = search.trim().toLowerCase();
    return bookings
      .filter(b => b.status === 'Checked Out')
      .filter(b => {
        // Filtered on when the stay actually closed — that is the date the
        // report is about, not the date it was booked for.
        if (!from && !to) return true;
        const closed = b.checkedOutAt ? String(b.checkedOutAt).slice(0, 10) : (b.checkOut || '');
        if (!closed) return false;
        if (from && closed < from) return false;
        if (to && closed > to) return false;
        return true;
      })
      .filter(b => !q || [b.fullName, b.roomName, b.bookedBy, b.contactNo, b.email, b.idNumber]
        .some(f => String(f || '').toLowerCase().includes(q)))
      .sort((a, b) => (a.bookingId < b.bookingId ? 1 : -1));
  }, [bookings, search, from, to]);

  const totals = useMemo(() => rows.reduce((acc, b) => {
    const roomCharge = Number(b.totalDue) || 0;
    const service = Number(b.roomServiceTotal) || 0;
    const extras = Number(b.otherCharges) || 0;
    const grand = Number(b.grandTotal) || (roomCharge + service + extras);
    const paid = Number(b.amountPaid) || 0;
    return {
      room: acc.room + roomCharge,
      service: acc.service + service,
      extras: acc.extras + extras,
      grand: acc.grand + grand,
      paid: acc.paid + paid,
      balance: acc.balance + Math.max(0, grand - paid),
    };
  }, { room: 0, service: 0, extras: 0, grand: 0, paid: 0, balance: 0 }), [rows]);

  const clearFilters = () => { setSearch(''); setFrom(''); setTo(''); setPage(1); };
  const filtering = !!(search || from || to);

  // safePage rather than page: narrowing the filters must not strand the view on a
  // page that no longer exists. The tiles and the totals row stay on the whole
  // filtered set, not the page — a report totals what it reports on.
  const PER_PAGE = 5;
  const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageRows = rows.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  return (
    <div data-hms-no-edit="1" style={{ maxWidth: 1180, margin: '0 auto', padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Front Desk</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Reports</h1>
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem' }}>
            Every completed booking — stays that were checked out, with what they earned.
          </p>
        </div>
        <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </a>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: '0.75rem', marginBottom: '1.35rem' }}>
        <Tile label="Completed Bookings" value={rows.length} />
        <Tile label="Revenue Collected" value={formatPeso(totals.paid)} tone="var(--accent-light)" />
        <Tile label="Room Charges" value={formatPeso(totals.room)} />
        <Tile label="Room Service" value={formatPeso(totals.service)} />
        <Tile label="Other Charges" value={formatPeso(totals.extras)} />
        {totals.balance > 0 && <Tile label="Uncollected" value={formatPeso(totals.balance)} tone="#fb7185" />}
      </div>

      <div style={{ display: 'flex', gap: '0.7rem', flexWrap: 'wrap', alignItems: 'flex-end', marginBottom: '1.1rem' }}>
        <div style={{ position: 'relative', flex: 1, minWidth: 220 }}>
          <i className="fa-solid fa-magnifying-glass" style={{ position: 'absolute', left: '0.75rem', top: '50%', transform: 'translateY(-50%)', color: 'var(--fg-muted)', fontSize: '0.75rem', pointerEvents: 'none' }}></i>
          <input
            type="text"
            className="booking-input"
            placeholder="Search by guest, room, contact, ID, or who booked it…"
            value={search}
            onChange={e => { setSearch(e.target.value); setPage(1); }}
            style={{ paddingLeft: '2.1rem' }}
          />
        </div>
        <div style={{ minWidth: 150 }}>
          <label className="rp-tile-label">Closed From</label>
          <input type="date" className="booking-input" value={from} onChange={e => { setFrom(e.target.value); setPage(1); }} style={{ colorScheme: 'dark' }} />
        </div>
        <div style={{ minWidth: 150 }}>
          <label className="rp-tile-label">Closed To</label>
          <input type="date" className="booking-input" value={to} onChange={e => { setTo(e.target.value); setPage(1); }} style={{ colorScheme: 'dark' }} />
        </div>
        {filtering && (
          <button type="button" className="btn-outline" onClick={clearFilters} style={{ fontSize: '0.68rem', padding: '0.6rem 0.9rem' }}>
            Clear
          </button>
        )}
      </div>

      {!loaded ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.85rem' }}>
          Loading completed bookings…
        </div>
      ) : rows.length === 0 ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem', textAlign: 'center' }}>
          <i className="fa-solid fa-clipboard-list" style={{ fontSize: '1.7rem', color: 'var(--fg-muted)', opacity: 0.3, display: 'block', marginBottom: '0.7rem' }}></i>
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.86rem' }}>
            {filtering ? 'No completed bookings match these filters.' : 'No bookings have been checked out yet.'}
          </p>
        </div>
      ) : (
        <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
          <table className="rp-table">
            <thead>
              <tr>
                <th>Guest</th>
                <th>Room</th>
                <th>Check-In</th>
                <th>Check-Out</th>
                <th className="rp-num">Blocks</th>
                <th className="rp-num">Room</th>
                <th className="rp-num">Room Svc</th>
                <th className="rp-num">Extras</th>
                <th className="rp-num">Total</th>
                <th className="rp-num">Paid</th>
                <th>Booked By</th>
                <th>Closed</th>
              </tr>
            </thead>
            <tbody>
              {pageRows.map(b => {
                const roomCharge = Number(b.totalDue) || 0;
                const service = Number(b.roomServiceTotal) || 0;
                const extras = Number(b.otherCharges) || 0;
                const grand = Number(b.grandTotal) || (roomCharge + service + extras);
                const paid = Number(b.amountPaid) || 0;
                const short = Math.max(0, grand - paid);
                return (
                  <tr key={b.bookingId}>
                    <td className="rp-strong">{b.fullName || '—'}</td>
                    <td>{b.roomName || '—'}</td>
                    <td>{formatDate(b.checkIn)}{b.checkInTime ? ` · ${b.checkInTime}` : ''}</td>
                    <td>{formatDate(b.checkOut)}</td>
                    <td className="rp-num">{stayBlocks(b.checkIn, b.checkOut, b.checkInTime)}</td>
                    <td className="rp-num">{formatPeso(roomCharge)}</td>
                    <td className="rp-num">{service > 0 ? formatPeso(service) : '—'}</td>
                    <td className="rp-num">{extras > 0 ? formatPeso(extras) : '—'}</td>
                    <td className="rp-num rp-money">{formatPeso(grand)}</td>
                    <td className="rp-num">
                      {formatPeso(paid)}
                      {short > 0 && (
                        <span style={{ display: 'block', fontSize: '0.7rem', color: '#fb7185' }}>Short {formatPeso(short)}</span>
                      )}
                    </td>
                    <td>{b.bookedBy || '—'}</td>
                    <td>{formatWhen(b.checkedOutAt)}</td>
                  </tr>
                );
              })}
            </tbody>
            <tfoot>
              <tr>
                <td colSpan={5}>{rows.length} booking{rows.length === 1 ? '' : 's'}</td>
                <td className="rp-num">{formatPeso(totals.room)}</td>
                <td className="rp-num">{formatPeso(totals.service)}</td>
                <td className="rp-num">{formatPeso(totals.extras)}</td>
                <td className="rp-num rp-money">{formatPeso(totals.grand)}</td>
                <td className="rp-num rp-money">{formatPeso(totals.paid)}</td>
                <td colSpan={2}></td>
              </tr>
            </tfoot>
          </table>
        </div>
      )}

      {loaded && totalPages > 1 && (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: '0.85rem', gap: '0.5rem', flexWrap: 'wrap' }}>
          <span style={{ fontSize: '0.75rem', color: 'var(--fg-muted)' }}>
            Showing {(safePage - 1) * PER_PAGE + 1}–{Math.min(safePage * PER_PAGE, rows.length)} of {rows.length}
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
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
