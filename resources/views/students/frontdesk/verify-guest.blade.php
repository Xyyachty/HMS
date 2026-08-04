@extends('students.builder.ops-shell')

@php $backRoute = 'students.frontdesk'; @endphp

@section('page-title', 'Verify Guest')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: 'Outfit', sans-serif; }
  .font-display { font-family: 'Playfair Display', serif; }
  .room-status-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent;
  }
  .room-status-badge.status-available { background: rgba(34,197,94,0.18); color: #4ade80; border-color: rgba(34,197,94,0.35); }
  .room-status-badge.status-reserved { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .room-status-badge.status-occupied { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
  .room-status-badge.status-cleaning { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35); }
  .room-status-badge.status-maintenance { background: rgba(244,63,94,0.18); color: #fb7185; border-color: rgba(244,63,94,0.35); }
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
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_FRONTDESK_URL = @json(route('students.frontdesk'));
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef } = React;

const ROOM_STATUSES = ['Available', 'Reserved', 'Occupied', 'Cleaning', 'Maintenance'];
const BLOCK_HOURS = 12;

function normalizeRoomStatus(value) {
  const raw = String(value || 'Available').trim().toLowerCase();
  const match = ROOM_STATUSES.find(s => s.toLowerCase() === raw);
  return match || 'Available';
}

function roomStatusClass(status) {
  return 'status-' + normalizeRoomStatus(status).toLowerCase();
}

/* Arrival lifecycle of a reservation: Reserved -> Arrived.
   Stored inside the reservation JSON so no schema change is needed. */
function reservationArrivalStatus(reservation) {
  const raw = String((reservation && reservation.arrivalStatus) || 'Reserved').trim().toLowerCase();
  return raw === 'arrived' ? 'Arrived' : 'Reserved';
}

function todayIsoDate() {
  return new Date().toISOString().split('T')[0];
}

/* Front Desk may only mark arrival on or after the reserved check-in date. */
function canMarkArrived(reservation) {
  if (!reservation) return false;
  if (reservationArrivalStatus(reservation) === 'Arrived') return false;
  const checkIn = String(reservation.checkIn || '').trim();
  if (!checkIn) return true;
  return todayIsoDate() >= checkIn;
}

function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0';
  return '₱' + n.toLocaleString();
}

function stayBlocks(checkIn, checkOut, checkInTime) {
  if (!checkIn || !checkOut) return 1;
  const clock = /^\d{1,2}:\d{2}/.test(String(checkInTime || '')) ? checkInTime : '00:00';
  const start = new Date(`${checkIn}T${clock}`);
  const end = new Date(`${checkOut}T${clock}`);
  const hours = (end - start) / 3600000;
  if (!Number.isFinite(hours) || hours <= 0) return 1;
  return Math.max(1, Math.ceil(hours / BLOCK_HOURS));
}

function formatClockTime(value) {
  const raw = String(value || '').trim();
  const match = /^(\d{1,2}):(\d{2})/.exec(raw);
  if (!match) return '';
  const hours = Number(match[1]);
  if (!Number.isFinite(hours)) return '';
  const suffix = hours >= 12 ? 'PM' : 'AM';
  const display = hours % 12 === 0 ? 12 : hours % 12;
  return `${display}:${match[2]} ${suffix}`;
}

function formatCheckIn(date, time) {
  const day = String(date || '').trim();
  const clock = formatClockTime(time);
  if (!day) return clock || '—';
  return clock ? `${day} · ${clock}` : day;
}

function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function VerifyGuestPage({ rooms, onBack, onEditRoom, onToast }) {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const PER_PAGE = 8;

  const markArrived = (room, reservation) => {
    if (!canMarkArrived(reservation)) return;
    if (typeof onEditRoom === 'function') {
      onEditRoom(room.id, {
        reservation: Object.assign({}, reservation, {
          arrivalStatus: 'Arrived',
          arrivedAt: new Date().toISOString(),
        }),
      });
    }
    if (onToast) onToast(`${reservation.fullName || 'Guest'} marked as Arrived — Room Management can now set ${room.name} to Occupied.`);
  };

  const allReservations = (rooms || []).reduce((acc, room) => {
    if (room && room.reservation) acc.push({ room, reservation: room.reservation });
    return acc;
  }, []);

  const q = search.trim().toLowerCase();
  const filtered = q
    ? allReservations.filter(({ room, reservation }) =>
        (reservation.fullName || '').toLowerCase().includes(q) ||
        (room.name || '').toLowerCase().includes(q) ||
        (reservation.email || '').toLowerCase().includes(q) ||
        (reservation.contactNo || '').toLowerCase().includes(q) ||
        (reservation.idNumber || '').toLowerCase().includes(q)
      )
    : allReservations;

  const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageRows = filtered.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  const thStyle = {
    padding: '0.6rem 0.85rem', fontSize: '0.62rem', fontWeight: 700,
    letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)',
    borderBottom: '1px solid var(--border)', whiteSpace: 'nowrap',
    textAlign: 'left', background: 'rgba(255,255,255,0.02)',
  };
  const tdStyle = {
    padding: '0.75rem 0.85rem', fontSize: '0.8rem', color: 'var(--fg-muted)',
    borderBottom: '1px solid rgba(42,38,33,0.5)', verticalAlign: 'middle', whiteSpace: 'nowrap',
  };

  return (
    <div data-hms-no-edit="1" style={{ maxWidth: 1100, margin: '0 auto', padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Front Desk</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Verify Guest</h1>
        </div>
        <button type="button" className="btn-outline" onClick={onBack} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back to Front Desk
        </button>
      </div>

      <div style={{ background: 'var(--card)', border: '1px solid var(--border)', borderRadius: 14, padding: '1.5rem 1.6rem 1.75rem' }}>
          <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', marginBottom: '1rem' }}>
            {allReservations.length > 0
              ? `${allReservations.length} active reservation${allReservations.length > 1 ? 's' : ''}`
              : 'No active reservations at this time.'}
          </p>

          <div style={{ position: 'relative', marginBottom: '1.1rem' }}>
            <i className="fa-solid fa-magnifying-glass" style={{ position: 'absolute', left: '0.75rem', top: '50%', transform: 'translateY(-50%)', color: 'var(--fg-muted)', fontSize: '0.75rem', pointerEvents: 'none' }}></i>
            <input
              type="text"
              className="booking-input"
              placeholder="Search by name, room, email, contact, or ID…"
              value={search}
              onChange={e => { setSearch(e.target.value); setPage(1); }}
              style={{ paddingLeft: '2.1rem' }}
            />
          </div>

          {allReservations.length === 0 ? (
            <div style={{ border: '1px solid rgba(244,63,94,0.35)', background: 'rgba(244,63,94,0.08)', borderRadius: 10, padding: '2rem', textAlign: 'center' }}>
              <i className="fa-solid fa-door-open" style={{ fontSize: '1.8rem', color: '#fb7185', opacity: 0.45, display: 'block', marginBottom: '0.65rem' }}></i>
              <p style={{ margin: 0, color: '#fb7185', fontWeight: 600, fontSize: '0.9rem' }}>No reservations found</p>
              <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.8rem' }}>No guests have been registered yet.</p>
            </div>
          ) : filtered.length === 0 ? (
            <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center' }}>
              <i className="fa-solid fa-magnifying-glass" style={{ fontSize: '1.5rem', color: 'var(--fg-muted)', opacity: 0.35, display: 'block', marginBottom: '0.65rem' }}></i>
              <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.88rem' }}>No results for "{search}"</p>
            </div>
          ) : (
            <>
              <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
                <table style={{ width: '100%', borderCollapse: 'collapse', fontFamily: 'Outfit, sans-serif' }}>
                  <thead>
                    <tr>
                      <th style={thStyle}>Guest Name</th>
                      <th style={thStyle}>Room</th>
                      <th style={thStyle}>Check-In</th>
                      <th style={thStyle}>Check-Out</th>
                      <th style={thStyle}>Rate / 12 hrs</th>
                      <th style={thStyle}>Total</th>
                      <th style={thStyle}>Payment</th>
                      <th style={thStyle}>Status</th>
                      <th style={thStyle}>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    {pageRows.map(({ room, reservation }, idx) => {
                      const payment = reservation.payment || null;
                      const total = stayBlocks(reservation.checkIn, reservation.checkOut, reservation.checkInTime) * (Number(room.price) || 0);
                      const rowBg = idx % 2 === 0 ? 'transparent' : 'rgba(255,255,255,0.015)';
                      const arrival = reservationArrivalStatus(reservation);
                      const arrivable = canMarkArrived(reservation);
                      return (
                        <tr key={room.id} style={{ background: rowBg }}>
                          <td style={{ ...tdStyle, color: 'var(--fg)', fontWeight: 600 }}>{reservation.fullName || '—'}</td>
                          <td style={tdStyle}>
                            <span style={{ display: 'block', fontSize: '0.62rem', color: 'var(--accent)', letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 2 }}>{room.label || room.category}</span>
                            <span style={{ color: 'var(--fg)', fontWeight: 500 }}>{room.name}</span>
                          </td>
                          <td style={tdStyle}>{formatCheckIn(reservation.checkIn, reservation.checkInTime)}</td>
                          <td style={tdStyle}>{reservation.checkOut || '—'}</td>
                          <td style={{ ...tdStyle, color: 'var(--accent)' }}>{formatPeso(room.price)}</td>
                          <td style={{ ...tdStyle, color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontWeight: 700 }}>{formatPeso(total)}</td>
                          <td style={tdStyle}>
                            {payment ? (
                              <>
                                <span style={{ display: 'block', color: 'var(--fg)', fontWeight: 500 }}>{payment.type} · {payment.method}</span>
                                <span style={{ display: 'block', fontSize: '0.75rem' }}>{formatPeso(payment.amountPaid)}</span>
                                {payment.balance > 0 && <span style={{ display: 'block', fontSize: '0.72rem', color: '#fb7185' }}>Bal: {formatPeso(payment.balance)}</span>}
                              </>
                            ) : <span style={{ opacity: 0.4 }}>—</span>}
                          </td>
                          <td style={tdStyle}>
                            <span className={`room-status-badge ${roomStatusClass(room.status)}`} style={{ position: 'static' }}>
                              {normalizeRoomStatus(room.status)}
                            </span>
                          </td>
                          <td style={tdStyle}>
                            {arrival === 'Arrived' ? (
                              <span style={{ display: 'inline-flex', alignItems: 'center', gap: '0.4rem', color: '#4ade80', fontSize: '0.78rem', fontWeight: 600 }}>
                                <i className="fa-solid fa-circle-check" style={{ fontSize: '0.8rem' }}></i> Arrived
                              </span>
                            ) : (
                              <button
                                type="button"
                                onClick={() => markArrived(room, reservation)}
                                disabled={!arrivable}
                                title={arrivable ? 'Mark this guest as arrived' : `Available on check-in date (${reservation.checkIn || 'n/a'})`}
                                style={{
                                  display: 'inline-flex', alignItems: 'center', gap: '0.4rem',
                                  padding: '0.4rem 0.8rem', borderRadius: 6,
                                  border: '1px solid ' + (arrivable ? 'var(--accent)' : 'var(--border)'),
                                  background: arrivable ? 'var(--accent)' : 'transparent',
                                  color: arrivable ? 'var(--bg)' : 'var(--fg-muted)',
                                  cursor: arrivable ? 'pointer' : 'not-allowed',
                                  opacity: arrivable ? 1 : 0.5,
                                  fontFamily: 'Outfit, sans-serif', fontSize: '0.72rem', fontWeight: 600,
                                  letterSpacing: '0.06em', textTransform: 'uppercase',
                                }}
                              >
                                <i className="fa-solid fa-user-check" style={{ fontSize: '0.7rem' }}></i> Mark Arrived
                              </button>
                            )}
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              </div>

              {totalPages > 1 && (
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: '0.85rem', gap: '0.5rem', flexWrap: 'wrap' }}>
                  <span style={{ fontSize: '0.75rem', color: 'var(--fg-muted)' }}>
                    Showing {(safePage - 1) * PER_PAGE + 1}–{Math.min(safePage * PER_PAGE, filtered.length)} of {filtered.length}
                  </span>
                  <div style={{ display: 'flex', gap: '0.35rem' }}>
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
            </>
          )}
      </div>
    </div>
  );
}

function App() {
  const [rooms, setRooms] = useState([]);
  const pendingWrites = useRef(0);

  const fetchRooms = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/rooms', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => { if (pendingWrites.current > 0) return; if (Array.isArray(data.rooms)) setRooms(data.rooms); })
      .catch(() => {});
  }, []);

  useEffect(() => {
    fetchRooms();
    const id = setInterval(fetchRooms, 8000);
    window.addEventListener('focus', fetchRooms);
    return () => { clearInterval(id); window.removeEventListener('focus', fetchRooms); };
  }, [fetchRooms]);

  const editRoom = useCallback((id, patch) => {
    setRooms(prev => prev.map(r => (r.id === id ? Object.assign({}, r, patch) : r)));
    const body = {};
    if (patch.status !== undefined) body.status = patch.status;
    if (patch.reservation !== undefined) body.reservation = patch.reservation;
    if (Object.keys(body).length === 0) return;
    pendingWrites.current += 1;
    fetch('/students/hotel/rooms/' + String(id).replace(/^db-/, ''), {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(body),
    })
      .then(r => (r.ok ? r.json() : null))
      .then(data => { if (data && data.room) setRooms(prev => prev.map(r => (r.id === data.room.id ? data.room : r))); })
      .catch(() => {})
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  return (
    <VerifyGuestPage
      rooms={rooms}
      onBack={() => { window.location.href = window.HMS_FRONTDESK_URL; }}
      onEditRoom={editRoom}
      onToast={(msg) => window.toast && window.toast(msg)}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
