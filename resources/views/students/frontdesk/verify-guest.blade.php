@extends('students.builder.ops-shell')

@php $backRoute = 'students.frontdesk'; @endphp

@section('page-title', 'Verify Guest')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }
  .room-status-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent;
  }
  .room-status-badge.status-available { background: rgba(34,197,94,0.18); color: var(--success, #4ade80); border-color: rgba(34,197,94,0.35); }
  .room-status-badge.status-reserved { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .room-status-badge.status-occupied { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
  .room-status-badge.status-cleaning { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35); }
  .room-status-badge.status-maintenance { background: rgba(244,63,94,0.18); color: var(--danger, #fb7185); border-color: rgba(244,63,94,0.35); }
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
  select.booking-input { color-scheme: dark; }
  select.booking-input option { background: var(--card); color: var(--fg); }
  .btn-solid {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;
    background: var(--accent); color: var(--bg); border: 1px solid var(--accent);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600;
    font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.65rem 1.2rem; border-radius: 6px; cursor: pointer;
    transition: filter 0.2s;
  }
  .btn-solid:hover { filter: brightness(1.1); }
  .btn-solid:disabled { opacity: 0.45; cursor: not-allowed; filter: none; }

  /* Final bill — a folio, so it is set in a monospaced grid where the amounts line
     up in a column the way a printed receipt does. */
  .bill-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.65);
    display: flex; align-items: flex-start; justify-content: center;
    padding: 2rem 1.5rem; z-index: 300; overflow-y: auto;
  }
  .bill-modal {
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    width: 100%; max-width: 560px; margin: auto;
  }
  .bill-head {
    padding: 1.4rem 1.6rem 1rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
  }
  .bill-body { padding: 1.25rem 1.6rem 1.6rem; }
  .bill-section-title {
    font-size: 0.62rem; font-weight: 700; letter-spacing: 0.14em;
    text-transform: uppercase; color: var(--accent);
    margin: 1.15rem 0 0.5rem; padding-bottom: 0.3rem;
    border-bottom: 1px solid var(--border);
  }
  .bill-line {
    display: flex; justify-content: space-between; gap: 1rem;
    font-size: 0.83rem; color: var(--fg-muted); padding: 0.22rem 0;
  }
  .bill-line .bill-amt { color: var(--fg); font-variant-numeric: tabular-nums; white-space: nowrap; }
  .bill-line.is-subtotal {
    border-top: 1px solid var(--border); margin-top: 0.35rem; padding-top: 0.45rem;
    color: var(--fg); font-weight: 600;
  }
  .bill-line.is-total {
    border-top: 2px solid var(--accent); margin-top: 0.6rem; padding-top: 0.6rem;
    font-size: 1rem; color: var(--fg); font-weight: 700;
  }
  .bill-line.is-total .bill-amt {
    color: var(--accent-light); font-family: var(--font-display, 'Playfair Display', serif); font-size: 1.15rem;
  }
  .bill-line.is-balance .bill-amt { color: var(--danger, #fb7185); font-weight: 700; }
  .bill-meta { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.55rem 1rem; }
  .bill-meta dt {
    font-size: 0.6rem; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--fg-muted); margin-bottom: 0.15rem;
  }
  .bill-meta dd { margin: 0; color: var(--fg); font-size: 0.85rem; }
  @media (max-width: 520px) { .bill-meta { grid-template-columns: 1fr; } }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Additive only — nothing above this block is touched, so a Template 1
     team (or one that hasn't chosen a template yet) renders unchanged. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d;
  }
  :root[data-ops-theme="2"] select.booking-input { color-scheme: light; }
  :root[data-ops-theme="2"] .room-status-badge.status-available { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
  :root[data-ops-theme="2"] .room-status-badge.status-reserved { background: #f3e8ff; color: #7e22ce; border-color: #e9d5ff; }
  :root[data-ops-theme="2"] .room-status-badge.status-occupied { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
  :root[data-ops-theme="2"] .room-status-badge.status-cleaning { background: #fef3c7; color: #b45309; border-color: #fde68a; }
  :root[data-ops-theme="2"] .room-status-badge.status-maintenance { background: #ffe4e6; color: #be123c; border-color: #fecdd3; }
  :root[data-ops-theme="2"] .booking-input { background: rgba(27,67,50,0.03); }
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

// Housekeeping condition only — occupancy lives on the booking (reservation.status),
// not on the room's own status. See bookingStatusClass() below for that badge.
const ROOM_STATUSES = ['Available', 'Cleaning', 'Maintenance'];
const BLOCK_HOURS = 12;

function normalizeRoomStatus(value) {
  const raw = String(value || 'Available').trim().toLowerCase();
  const match = ROOM_STATUSES.find(s => s.toLowerCase() === raw);
  return match || 'Available';
}

function roomStatusClass(status) {
  return 'status-' + normalizeRoomStatus(status).toLowerCase();
}

/* Booking-lifecycle badge (Booked / Arrived / Checked In) — reuses the
   room-status-badge colours (purple/amber/blue) for a different meaning now that
   hotel_rooms.status no longer tracks occupancy. */
function bookingStatusClass(status) {
  const s = String(status || '').trim();
  if (s === 'Checked In') return 'status-occupied';
  if (s === 'Arrived') return 'status-cleaning';
  return 'status-reserved';
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

/* 'Aug 12, 2026' from a 'YYYY-MM-DD' string, built from the parts so the date is not
   shifted a day by being read as UTC midnight. */
function formatBillDate(dateStr) {
  const [y, m, d] = String(dateStr || '').split('-').map(Number);
  if (!y || !m || !d) return '';
  return new Date(y, m - 1, d).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

/* 'Aug 12, 2026 — 2:00 PM' for the bill header. */
function formatBillDateTime(dateStr, timeStr) {
  const day = formatBillDate(dateStr);
  const clock = formatClockTime(timeStr);
  if (!day) return '—';
  return clock ? `${day} — ${clock}` : day;
}

/* The same, from a full ISO timestamp — what an actual check-out is stamped with. */
function formatBillStamp(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
    + ' — ' + d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

/* End of a paid stay: the booked check-in datetime plus the 12-hour blocks
   booked. Built with the same local-time parsing stayBlocks() uses, so the
   countdown and the Total on the same row can never disagree. */
function stayEndsAt(reservation) {
  if (!reservation || !reservation.checkIn) return null;
  const clock = /^\d{1,2}:\d{2}/.test(String(reservation.checkInTime || '')) ? reservation.checkInTime : '00:00';
  const start = new Date(`${reservation.checkIn}T${clock}`);
  if (Number.isNaN(start.getTime())) return null;
  const blocks = stayBlocks(reservation.checkIn, reservation.checkOut, reservation.checkInTime);
  return new Date(start.getTime() + blocks * BLOCK_HOURS * 3600000);
}

/* How much of the stay is left, as a label plus a tone that drives colour only.
   Only a guest Room Management has actually checked in gets a running clock — a
   reservation nobody has moved into yet has no stay to count down. */
function remainingStay(reservation, now) {
  if ((reservation && reservation.status) !== 'Checked In') {
    return { text: 'Not checked in', tone: 'idle' };
  }
  const endsAt = stayEndsAt(reservation);
  if (!endsAt) return { text: '—', tone: 'idle' };

  const ms = endsAt.getTime() - now;
  const totalSeconds = Math.floor(Math.abs(ms) / 1000);
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;

  if (ms <= 0) return { text: `Overdue ${hours}h ${minutes}m`, tone: 'over' };
  // Inside the last hour the minutes alone barely move; seconds make it read as live.
  if (hours < 1) return { text: `${minutes}m ${seconds}s`, tone: 'soon' };
  return { text: `${hours}h ${minutes}m`, tone: hours < 2 ? 'soon' : 'ok' };
}

const STAY_TONE_COLORS = { ok: 'var(--fg)', soon: '#fbbf24', over: 'var(--danger, #fb7185)', idle: 'var(--fg-muted)' };

/* A one-second tick. Aligned to the next whole second so every row flips
   together, and repainted on visibilitychange because a backgrounded tab
   throttles timers — the same handling the header clock partial uses. */
function useNow(intervalMs) {
  const [now, setNow] = useState(() => Date.now());
  useEffect(() => {
    let timer = null;
    const align = setTimeout(() => {
      setNow(Date.now());
      timer = setInterval(() => setNow(Date.now()), intervalMs);
    }, intervalMs - (Date.now() % intervalMs));
    const onVisible = () => { if (!document.hidden) setNow(Date.now()); };
    document.addEventListener('visibilitychange', onVisible);
    return () => {
      clearTimeout(align);
      if (timer) clearInterval(timer);
      document.removeEventListener('visibilitychange', onVisible);
    };
  }, [intervalMs]);
  return now;
}

const PAYMENT_METHODS = ['Cash', 'GCash', 'Card', 'Other'];

/*
 * The final bill, and the one screen that closes a stay.
 *
 * Everything shown here is priced by the server (HotelBooking::toBillArray()) rather
 * than recomputed in the browser, so what the guest is charged cannot drift from what
 * the database will record. Adding an extra charge re-fetches the whole bill for the
 * same reason — the new subtotal comes back priced, it is not patched in locally.
 */
function FinalBillModal({ open, bill, loading, error, onClose, onAddCharge, onRemoveCharge, onSettle, onToast }) {
  const [method, setMethod] = useState('Cash');
  const [amount, setAmount] = useState('');
  const [reference, setReference] = useState('');
  const [chargeDesc, setChargeDesc] = useState('');
  const [chargeAmount, setChargeAmount] = useState('');
  const [showChargeForm, setShowChargeForm] = useState(false);
  const [busy, setBusy] = useState(false);

  const balance = bill ? Number(bill.balance) || 0 : 0;

  // Opening the bill pre-fills the balance: settling it in full is the common case,
  // and the field stays editable for a guest paying part of it.
  useEffect(() => {
    if (!open) return;
    setMethod('Cash');
    setReference('');
    setChargeDesc('');
    setChargeAmount('');
    setShowChargeForm(false);
  }, [open]);

  useEffect(() => {
    if (open && bill) setAmount(balance > 0 ? String(balance) : '0');
  }, [open, bill && bill.bookingId]);

  useEffect(() => {
    if (!open) return;
    const onKey = (e) => { if (e.key === 'Escape' && !busy) onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [open, onClose, busy]);

  if (!open) return null;

  const paid = Math.max(0, parseFloat(amount) || 0);
  const remainingAfter = Math.max(0, balance - paid);

  const submitCharge = (e) => {
    e.preventDefault();
    const value = parseFloat(chargeAmount);
    if (!chargeDesc.trim()) { if (onToast) onToast('Describe the charge first.'); return; }
    if (!Number.isFinite(value) || value <= 0) { if (onToast) onToast('Enter a valid charge amount.'); return; }
    setBusy(true);
    Promise.resolve(onAddCharge(chargeDesc.trim(), value))
      .then(() => { setChargeDesc(''); setChargeAmount(''); setShowChargeForm(false); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not add that charge.'); })
      .finally(() => setBusy(false));
  };

  const settle = (e) => {
    e.preventDefault();
    // A short payment is allowed — a written-off or disputed stay still has to close —
    // but the desk is told what is being left behind before it does.
    if (remainingAfter > 0 && !window.confirm(
      `${formatPeso(remainingAfter)} will still be unpaid after this. Check out anyway?`
    )) return;
    setBusy(true);
    Promise.resolve(onSettle({ amount: paid, method, reference: reference.trim() }))
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not complete check-out.'); })
      .finally(() => setBusy(false));
  };

  const roomLines = bill ? bill.room : null;
  const service = bill ? bill.roomService : null;
  const extras = bill ? bill.otherCharges : null;
  const addons = bill ? bill.addons : null;

  return (
    <div className="bill-overlay" onClick={() => { if (!busy) onClose(); }} role="dialog" aria-modal="true">
      <div className="bill-modal" onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.2em', textTransform: 'uppercase', margin: '0 0 0.35rem' }}>Front Desk</p>
            <h2 className="font-display" style={{ fontSize: '1.5rem', margin: 0, color: 'var(--fg)' }}>Final Bill</h2>
          </div>
          <button type="button" onClick={onClose} disabled={busy} aria-label="Close"
            style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: busy ? 'not-allowed' : 'pointer', flexShrink: 0 }}>
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div className="bill-body">
          {loading ? (
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.85rem', margin: 0 }}>Loading the bill…</p>
          ) : error ? (
            <p style={{ color: 'var(--danger, #fb7185)', fontSize: '0.85rem', margin: 0 }}>{error}</p>
          ) : !bill ? null : (
            <>
              <dl className="bill-meta">
                <div><dt>Guest</dt><dd>{bill.guestName}</dd></div>
                <div><dt>Room</dt><dd>{bill.roomName || '—'}</dd></div>
                <div>
                  <dt>Check-in</dt>
                  <dd>{formatBillDateTime(bill.checkIn, bill.checkInTime)}</dd>
                </div>
                <div>
                  <dt>Check-out</dt>
                  <dd>{formatBillStamp(bill.checkedOutAt) || formatBillDate(bill.checkOut) || '—'}</dd>
                </div>
              </dl>

              <p className="bill-section-title">Room Charges</p>
              <div className="bill-line">
                <span>{roomLines.blocks} × {roomLines.blockHours} hrs × {formatPeso(roomLines.rate)}</span>
                <span className="bill-amt">{formatPeso(roomLines.subtotal)}</span>
              </div>

              {service.orders.length > 0 && (
                <>
                  <p className="bill-section-title">Room Service</p>
                  {service.orders.map(order => (
                    order.items.map((item, i) => (
                      <div className="bill-line" key={order.orderId + '-' + i}>
                        <span>{item.name} × {item.qty}</span>
                        <span className="bill-amt">{formatPeso(item.line)}</span>
                      </div>
                    ))
                  ))}
                  <div className="bill-line is-subtotal">
                    <span>Room Service Subtotal</span>
                    <span className="bill-amt">{formatPeso(service.subtotal)}</span>
                  </div>
                </>
              )}

              {/* Housekeeping's add-ons. No remove button: they were lent at registration
                  and go back into stock when the stay closes, not when a line is deleted. */}
              {addons && addons.items.length > 0 && (
                <>
                  <p className="bill-section-title">Add-ons</p>
                  {addons.items.map(addon => (
                    <div className="bill-line" key={addon.id}>
                      <span>{addon.name} × {addon.qty}</span>
                      <span className="bill-amt">{formatPeso(addon.line)}</span>
                    </div>
                  ))}
                  <div className="bill-line is-subtotal">
                    <span>Add-ons Subtotal</span>
                    <span className="bill-amt">{formatPeso(addons.subtotal)}</span>
                  </div>
                </>
              )}

              <p className="bill-section-title">Other Charges</p>
              {extras.items.length === 0 ? (
                <div className="bill-line"><span style={{ opacity: 0.6 }}>None</span><span className="bill-amt">{formatPeso(0)}</span></div>
              ) : (
                <>
                  {extras.items.map(charge => (
                    <div className="bill-line" key={charge.id}>
                      <span>
                        {charge.description}
                        <button type="button" onClick={() => onRemoveCharge(charge.id)} disabled={busy} title="Remove this charge"
                          style={{ background: 'none', border: 'none', color: 'var(--danger, #fb7185)', cursor: busy ? 'not-allowed' : 'pointer', fontSize: '0.72rem', padding: '0 0 0 0.45rem' }}>
                          <i className="fa-solid fa-xmark"></i>
                        </button>
                      </span>
                      <span className="bill-amt">{formatPeso(charge.amount)}</span>
                    </div>
                  ))}
                  <div className="bill-line is-subtotal">
                    <span>Other Subtotal</span>
                    <span className="bill-amt">{formatPeso(extras.subtotal)}</span>
                  </div>
                </>
              )}

              {showChargeForm ? (
                <form onSubmit={submitCharge} style={{ display: 'flex', gap: '0.4rem', marginTop: '0.6rem', flexWrap: 'wrap' }}>
                  <input type="text" className="booking-input" placeholder="e.g. Minibar" value={chargeDesc}
                    onChange={e => setChargeDesc(e.target.value)} style={{ flex: '2 1 140px', width: 'auto' }} />
                  <input type="number" className="booking-input" placeholder="Amount" min="0.01" step="0.01" value={chargeAmount}
                    onChange={e => setChargeAmount(e.target.value)} style={{ flex: '1 1 90px', width: 'auto' }} />
                  <button type="submit" className="btn-solid" disabled={busy} style={{ padding: '0.5rem 0.9rem' }}>Add</button>
                  <button type="button" className="btn-outline" disabled={busy} onClick={() => setShowChargeForm(false)}
                    style={{ padding: '0.5rem 0.9rem', fontSize: '0.68rem' }}>Cancel</button>
                </form>
              ) : (
                <button type="button" onClick={() => setShowChargeForm(true)} disabled={busy}
                  style={{ background: 'none', border: 'none', color: 'var(--accent)', cursor: busy ? 'not-allowed' : 'pointer', fontSize: '0.75rem', padding: '0.5rem 0 0', fontFamily: 'var(--font-body, Outfit, sans-serif)' }}>
                  <i className="fa-solid fa-plus" style={{ fontSize: '0.65rem', marginRight: 5 }}></i> Add charge
                </button>
              )}

              <div className="bill-line is-total">
                <span>Total Bill</span>
                <span className="bill-amt">{formatPeso(bill.total)}</span>
              </div>

              {bill.payments.length > 0 && (
                <div style={{ marginTop: '0.5rem' }}>
                  {bill.payments.map(p => (
                    <div className="bill-line" key={p.id}>
                      <span>{p.type} payment · {p.method}</span>
                      <span className="bill-amt">− {formatPeso(p.amountPaid)}</span>
                    </div>
                  ))}
                </div>
              )}
              <div className="bill-line is-subtotal">
                <span>Previous Payments</span>
                <span className="bill-amt">− {formatPeso(bill.amountPaid)}</span>
              </div>
              <div className="bill-line is-subtotal is-balance">
                <span>Remaining Balance</span>
                <span className="bill-amt">{formatPeso(balance)}</span>
              </div>

              <form onSubmit={settle} style={{ marginTop: '1.4rem' }}>
                <p className="bill-section-title" style={{ marginTop: 0 }}>Settle</p>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.7rem' }}>
                  <div>
                    <label style={{ fontSize: '0.6rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.35rem' }}>Payment Method</label>
                    <select className="booking-input" value={method} onChange={e => setMethod(e.target.value)}>
                      {PAYMENT_METHODS.map(m => <option key={m} value={m}>{m}</option>)}
                    </select>
                  </div>
                  <div>
                    <label style={{ fontSize: '0.6rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.35rem' }}>Amount Paid</label>
                    <input type="number" className="booking-input" min="0" step="0.01" value={amount}
                      onChange={e => setAmount(e.target.value)} />
                  </div>
                </div>
                {method !== 'Cash' && (
                  <div style={{ marginTop: '0.7rem' }}>
                    <label style={{ fontSize: '0.6rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.35rem' }}>Reference</label>
                    <input type="text" className="booking-input" placeholder="Receipt no., card last 4, or ref #"
                      value={reference} onChange={e => setReference(e.target.value)} />
                  </div>
                )}
                <p style={{ margin: '0.65rem 0 0', fontSize: '0.76rem', color: remainingAfter > 0 ? 'var(--danger, #fb7185)' : 'var(--success, #4ade80)' }}>
                  {remainingAfter > 0
                    ? `${formatPeso(remainingAfter)} will still be unpaid.`
                    : 'This settles the bill in full.'}
                </p>
                <button type="submit" className="btn-solid" disabled={busy} style={{ width: '100%', marginTop: '1rem' }}>
                  <i className="fa-solid fa-right-from-bracket" style={{ fontSize: '0.7rem' }}></i>
                  {busy ? 'Working…' : 'Settle & Check Out'}
                </button>
              </form>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

/*
 * Read-only view of one stay. The table now carries only what the desk scans by
 * (who, where, when, status), so everything it used to spell out in the Total and
 * Payment columns lives here instead, in full rather than abbreviated.
 */
function GuestDetailsModal({ room, reservation, onClose }) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  if (!reservation) return null;

  const roomTotal = Number(reservation.totalDue) || 0;
  const foodTotal = Number(reservation.roomServiceTotal) || 0;
  const foodCount = Number(reservation.roomServiceCount) || 0;
  const extras = Number(reservation.otherCharges) || 0;
  const addonsTotal = Number(reservation.addonsTotal) || 0;
  const addonsCount = Number(reservation.addonsCount) || 0;
  const grandTotal = Number(reservation.grandTotal) || (roomTotal + foodTotal + addonsTotal + extras);
  const paid = Number(reservation.amountPaid) || 0;
  const outstanding = reservation.outstanding != null
    ? Number(reservation.outstanding)
    : Math.max(0, grandTotal - paid);
  const payments = reservation.payments || [];

  return (
    <div className="bill-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="bill-modal" onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ margin: 0, color: 'var(--accent)', fontSize: '0.62rem', letterSpacing: '0.14em', textTransform: 'uppercase' }}>
              Reservation
            </p>
            <h2 className="font-display" style={{ margin: '0.25rem 0 0.4rem', fontSize: '1.35rem', color: 'var(--fg)' }}>
              {reservation.fullName || 'Guest'}
            </h2>
            <span className={`room-status-badge ${bookingStatusClass(reservation.status)}`} style={{ position: 'static' }}>
              {reservation.status}
            </span>
          </div>
          <button
            type="button"
            onClick={onClose}
            aria-label="Close"
            style={{ width: 32, height: 32, borderRadius: 8, border: '1px solid var(--border)', background: 'transparent', color: 'var(--fg-muted)', cursor: 'pointer', flexShrink: 0 }}
          >
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div className="bill-body">
          <p className="bill-section-title" style={{ marginTop: 0 }}>Guest</p>
          <dl className="bill-meta">
            <div><dt>Full name</dt><dd>{reservation.fullName || '—'}</dd></div>
            <div><dt>Contact no.</dt><dd>{reservation.contactNo || '—'}</dd></div>
            <div><dt>Email</dt><dd style={{ overflowWrap: 'anywhere' }}>{reservation.email || '—'}</dd></div>
            <div><dt>ID number</dt><dd>{reservation.idNumber || '—'}</dd></div>
          </dl>

          <p className="bill-section-title">Stay</p>
          <dl className="bill-meta">
            <div><dt>Room</dt><dd>{room ? `${room.name} · ${room.label || room.category}` : '—'}</dd></div>
            <div><dt>Rate / 12 hrs</dt><dd>{formatPeso(reservation.roomRate || (room && room.price))}</dd></div>
            <div><dt>Check-in</dt><dd>{formatBillDateTime(reservation.checkIn, reservation.checkInTime)}</dd></div>
            <div><dt>Check-out</dt><dd>{formatBillDate(reservation.checkOut) || '—'}</dd></div>
            <div><dt>Booked</dt><dd>{formatBillStamp(reservation.reservedAt) || '—'}</dd></div>
            <div><dt>Arrived</dt><dd>{formatBillStamp(reservation.arrivedAt) || '—'}</dd></div>
            <div><dt>Checked in</dt><dd>{formatBillStamp(reservation.checkedInAt) || '—'}</dd></div>
            <div><dt>Checked out</dt><dd>{formatBillStamp(reservation.checkedOutAt) || '—'}</dd></div>
            <div><dt>Booked by</dt><dd>{reservation.bookedBy || '—'}</dd></div>
          </dl>

          {reservation.notes && (
            <>
              <p className="bill-section-title">Notes</p>
              <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.84rem', lineHeight: 1.6, whiteSpace: 'pre-wrap' }}>
                {reservation.notes}
              </p>
            </>
          )}

          <p className="bill-section-title">Charges</p>
          <div className="bill-line"><span>Room charge</span><span className="bill-amt">{formatPeso(roomTotal)}</span></div>
          <div className="bill-line">
            <span>Room service{foodCount > 0 ? ` (${foodCount} order${foodCount === 1 ? '' : 's'})` : ''}</span>
            <span className="bill-amt">{formatPeso(foodTotal)}</span>
          </div>
          {addonsCount > 0 && (
            <div className="bill-line">
              <span>Add-ons ({addonsCount} item{addonsCount === 1 ? '' : 's'})</span>
              <span className="bill-amt">{formatPeso(addonsTotal)}</span>
            </div>
          )}
          <div className="bill-line"><span>Other charges</span><span className="bill-amt">{formatPeso(extras)}</span></div>
          <div className="bill-line is-total"><span>Total</span><span className="bill-amt">{formatPeso(grandTotal)}</span></div>
          <div className="bill-line is-subtotal"><span>Paid</span><span className="bill-amt">{formatPeso(paid)}</span></div>
          <div className={`bill-line${outstanding > 0 ? ' is-balance' : ''}`}>
            <span>Balance</span><span className="bill-amt">{formatPeso(outstanding)}</span>
          </div>

          <p className="bill-section-title">Payments</p>
          {payments.length === 0 ? (
            <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.82rem' }}>Nothing has been paid on this stay yet.</p>
          ) : (
            payments.map(p => (
              <div key={p.id} style={{ padding: '0.5rem 0', borderBottom: '1px solid rgba(42,38,33,0.5)' }}>
                <div className="bill-line" style={{ padding: 0 }}>
                  <span style={{ color: 'var(--fg)' }}>{p.type} · {p.method}</span>
                  <span className="bill-amt">{formatPeso(p.amountPaid)}</span>
                </div>
                <p style={{ margin: '0.2rem 0 0', fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                  {formatBillStamp(p.paidAt) || '—'}
                  {p.reference ? ` · Ref ${p.reference}` : ''}
                  {p.payerName ? ` · ${p.payerName}` : ''}
                </p>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
}

function VerifyGuestPage({ rooms, onBack, onBookingAction, onToast, onFetchBill, onAddCharge, onRemoveCharge, onSettleAndCheckOut }) {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const now = useNow(1000);
  const PER_PAGE = 8;

  // Check-out goes through the final bill: the desk sees every charge on the stay and
  // takes the closing payment there, rather than the booking simply being closed.
  const [billFor, setBillFor] = useState(null);
  const [bill, setBill] = useState(null);
  const [billLoading, setBillLoading] = useState(false);
  const [billError, setBillError] = useState('');

  // The View action. Read-only, so it needs no fetch: the row already holds the whole
  // reservation payload the modal renders.
  const [detailsFor, setDetailsFor] = useState(null);

  const openBill = (room, reservation) => {
    setBillFor({ room, reservation });
    setBill(null);
    setBillError('');
    setBillLoading(true);
    Promise.resolve(onFetchBill(reservation.bookingId))
      .then(data => setBill(data))
      .catch(err => setBillError((err && err.message) || 'Could not load this bill.'))
      .finally(() => setBillLoading(false));
  };

  const closeBill = () => { setBillFor(null); setBill(null); setBillError(''); };

  const refreshBill = (promise) => (
    Promise.resolve(promise).then(data => { if (data) setBill(data); return data; })
  );

  const settleAndCheckOut = (payment) => (
    Promise.resolve(onSettleAndCheckOut(billFor.reservation.bookingId, payment)).then(() => {
      if (onToast) {
        onToast(`${billFor.reservation.fullName || 'Guest'} checked out of ${billFor.room.name}.`);
      }
      closeBill();
    })
  );

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

  /* No nowrap and no fixed widths: the table has to fit the page rather than force a
     horizontal scrollbar, so long headers wrap instead of pushing the table wider.
     Cells that must stay on one line (dates, countdown, rate) opt in via tdTight. */
  const thStyle = {
    padding: '0.6rem 0.7rem', fontSize: '0.6rem', fontWeight: 700,
    letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--fg-muted)',
    borderBottom: '1px solid var(--border)',
    textAlign: 'left', background: 'rgba(255,255,255,0.02)',
  };
  const tdStyle = {
    padding: '0.7rem', fontSize: '0.78rem', color: 'var(--fg-muted)',
    borderBottom: '1px solid rgba(42,38,33,0.5)', verticalAlign: 'middle',
  };
  const tdTight = { ...tdStyle, whiteSpace: 'nowrap' };
  const rowBtn = {
    display: 'inline-flex', alignItems: 'center', gap: '0.35rem',
    padding: '0.35rem 0.65rem', borderRadius: 6, cursor: 'pointer',
    fontFamily: 'var(--font-body, Outfit, sans-serif)', fontSize: '0.68rem', fontWeight: 600,
    letterSpacing: '0.05em', textTransform: 'uppercase', whiteSpace: 'nowrap',
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
              <i className="fa-solid fa-door-open" style={{ fontSize: '1.8rem', color: 'var(--danger, #fb7185)', opacity: 0.45, display: 'block', marginBottom: '0.65rem' }}></i>
              <p style={{ margin: 0, color: 'var(--danger, #fb7185)', fontWeight: 600, fontSize: '0.9rem' }}>No reservations found</p>
              <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.8rem' }}>No guests have been registered yet.</p>
            </div>
          ) : filtered.length === 0 ? (
            <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center' }}>
              <i className="fa-solid fa-magnifying-glass" style={{ fontSize: '1.5rem', color: 'var(--fg-muted)', opacity: 0.35, display: 'block', marginBottom: '0.65rem' }}></i>
              <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.88rem' }}>No results for "{search}"</p>
            </div>
          ) : (
            <>
              <div style={{ borderRadius: 10, border: '1px solid var(--border)' }}>
                <table style={{ width: '100%', tableLayout: 'fixed', borderCollapse: 'collapse', fontFamily: 'var(--font-body, Outfit, sans-serif)' }}>
                  <thead>
                    <tr>
                      <th style={{ ...thStyle, width: '17%' }}>Guest Name</th>
                      <th style={{ ...thStyle, width: '14%' }}>Room</th>
                      <th style={{ ...thStyle, width: '14%' }}>Check-In</th>
                      <th style={{ ...thStyle, width: '11%' }}>Check-Out</th>
                      <th style={{ ...thStyle, width: '12%' }}>Time Remaining</th>
                      <th style={{ ...thStyle, width: '10%' }}>Rate / 12 hrs</th>
                      <th style={{ ...thStyle, width: '10%' }}>Status</th>
                      <th style={{ ...thStyle, width: '12%' }}>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    {pageRows.map(({ room, reservation }, idx) => {
                      const rowBg = idx % 2 === 0 ? 'transparent' : 'rgba(255,255,255,0.015)';
                      const checkedIn = reservation.status === 'Checked In';
                      const remaining = remainingStay(reservation, now);
                      return (
                        <tr key={room.id} style={{ background: rowBg }}>
                          <td style={{ ...tdStyle, color: 'var(--fg)', fontWeight: 600, overflowWrap: 'anywhere' }}>
                            {reservation.fullName || '—'}
                          </td>
                          <td style={tdStyle}>
                            <span style={{ display: 'block', fontSize: '0.6rem', color: 'var(--accent)', letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 2 }}>{room.label || room.category}</span>
                            <span style={{ color: 'var(--fg)', fontWeight: 500 }}>{room.name}</span>
                          </td>
                          <td style={tdStyle}>{formatCheckIn(reservation.checkIn, reservation.checkInTime)}</td>
                          <td style={tdTight}>{reservation.checkOut || '—'}</td>
                          <td style={{
                            ...tdTight,
                            color: STAY_TONE_COLORS[remaining.tone],
                            fontWeight: remaining.tone === 'idle' ? 400 : 600,
                            opacity: remaining.tone === 'idle' ? 0.6 : 1,
                            fontVariantNumeric: 'tabular-nums',
                          }}>
                            {remaining.text}
                          </td>
                          <td style={{ ...tdTight, color: 'var(--accent)' }}>{formatPeso(room.price)}</td>
                          <td style={tdStyle}>
                            <span className={`room-status-badge ${bookingStatusClass(reservation.status)}`} style={{ position: 'static' }}>
                              {reservation.status}
                            </span>
                          </td>
                          <td style={tdStyle}>
                            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.35rem' }}>
                              <button
                                type="button"
                                onClick={() => setDetailsFor({ room, reservation })}
                                title="See everything recorded for this stay"
                                style={{ ...rowBtn, border: '1px solid var(--border)', background: 'transparent', color: 'var(--fg)' }}
                              >
                                <i className="fa-solid fa-eye" style={{ fontSize: '0.68rem' }}></i> View
                              </button>
                              {checkedIn && (
                                <button
                                  type="button"
                                  onClick={() => openBill(room, reservation)}
                                  title="Show the final bill and check the guest out"
                                  style={{ ...rowBtn, border: '1px solid var(--accent)', background: 'var(--accent)', color: 'var(--bg)' }}
                                >
                                  <i className="fa-solid fa-right-from-bracket" style={{ fontSize: '0.68rem' }}></i> Check Out
                                </button>
                              )}
                              {reservation.status === 'Booked' && (
                                <button
                                  type="button"
                                  onClick={() => onBookingAction(reservation.bookingId, 'arrive')}
                                  title="Confirm the guest has arrived at the desk"
                                  style={{ ...rowBtn, border: '1px solid var(--accent)', background: 'transparent', color: 'var(--accent)' }}
                                >
                                  <i className="fa-solid fa-door-open" style={{ fontSize: '0.68rem' }}></i> Arrive
                                </button>
                              )}
                            </div>
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

      <FinalBillModal
        open={!!billFor}
        bill={bill}
        loading={billLoading}
        error={billError}
        onClose={closeBill}
        onAddCharge={(description, amount) => refreshBill(onAddCharge(billFor.reservation.bookingId, description, amount))}
        onRemoveCharge={(chargeId) => refreshBill(onRemoveCharge(billFor.reservation.bookingId, chargeId))}
        onSettle={settleAndCheckOut}
        onToast={onToast}
      />

      {detailsFor && (
        <GuestDetailsModal
          room={detailsFor.room}
          reservation={detailsFor.reservation}
          onClose={() => setDetailsFor(null)}
        />
      )}
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

  // Lifecycle moves belong to the booking, not the room; the response carries the room
  // back with its projected reservation so the table re-renders from one source.
  const bookingAction = useCallback((bookingId, action) => {
    if (!bookingId) return;
    pendingWrites.current += 1;
    fetch('/students/hotel/bookings/' + bookingId, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ action }),
    })
      .then(r => (r.ok ? r.json() : null))
      .then(data => { if (data && data.room) setRooms(prev => prev.map(r => (r.id === data.room.id ? data.room : r))); })
      .catch(() => {})
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  /* The bill routes all answer with the whole re-priced bill, so each of these hands
     that straight back to the modal rather than patching a total locally. */
  const billRequest = useCallback((url, method, body) => {
    pendingWrites.current += 1;
    return fetch(url, {
      method,
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: body === undefined ? undefined : JSON.stringify(body),
    })
      .then(r => r.json().then(data => (r.ok ? data : Promise.reject(data))))
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  const fetchBill = useCallback((bookingId) => (
    billRequest('/students/hotel/bookings/' + bookingId + '/bill', 'GET').then(d => d.bill)
  ), [billRequest]);

  const addCharge = useCallback((bookingId, description, amount) => (
    billRequest('/students/hotel/bookings/' + bookingId + '/charges', 'POST', { description, amount }).then(d => d.bill)
  ), [billRequest]);

  const removeCharge = useCallback((bookingId, chargeId) => (
    billRequest('/students/hotel/bookings/' + bookingId + '/charges/' + chargeId, 'DELETE').then(d => d.bill)
  ), [billRequest]);

  /*
   * Settling is two writes: the closing payment, then the check-out itself. The payment
   * goes first — if it fails the stay stays open and can be retried, whereas closing
   * first would leave a checked-out guest with money unrecorded against them. A zero
   * payment (nothing left to settle) skips straight to the check-out.
   */
  const settleAndCheckOut = useCallback((bookingId, payment) => {
    const takePayment = payment.amount > 0
      ? billRequest('/students/hotel/bookings/' + bookingId + '/payments', 'POST', {
          type: 'Full',
          amount_paid: payment.amount,
          method: payment.method,
          reference: payment.reference,
        })
      : Promise.resolve(null);

    return takePayment.then(() => (
      billRequest('/students/hotel/bookings/' + bookingId, 'PATCH', { action: 'check_out' })
        .then(data => {
          if (data && data.room) setRooms(prev => prev.map(r => (r.id === data.room.id ? data.room : r)));
          // The checked-out room no longer carries this stay, so drop it from the table
          // even if the poll has not come back around yet.
          fetchRooms();
          return data;
        })
    ));
  }, [billRequest, fetchRooms]);

  return (
    <VerifyGuestPage
      rooms={rooms}
      onBack={() => { window.location.href = window.HMS_FRONTDESK_URL; }}
      onBookingAction={bookingAction}
      onFetchBill={fetchBill}
      onAddCharge={addCharge}
      onRemoveCharge={removeCharge}
      onSettleAndCheckOut={settleAndCheckOut}
      onToast={(msg) => window.toast && window.toast(msg)}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
