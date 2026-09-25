@extends('students.builder.ops-shell')

@section('page-title', 'Walk-in Guests')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }
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
  .btn-outline:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
  .btn-solid {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;
    background: var(--accent); color: var(--bg); border: 1px solid var(--accent);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600;
    font-size: 0.75rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.75rem 1.2rem; border-radius: 6px; cursor: pointer;
    transition: filter 0.2s;
  }
  .btn-solid:hover { filter: brightness(1.1); }
  .btn-solid:disabled { opacity: 0.45; cursor: not-allowed; filter: none; }
  .booking-input {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus { border-color: var(--accent); }
  .booking-input::placeholder { color: var(--fg-muted); opacity: 0.5; }
  input[type="date"].booking-input,
  input[type="time"].booking-input { color-scheme: dark; }
  select.booking-input { appearance: auto; }
  select.booking-input option { background: var(--card); color: var(--fg); }

  /* Two jobs on one page, one at a time. */
  .wi-tabs { display: inline-flex; gap: 0.35rem; padding: 0.3rem; border: 1px solid var(--border); border-radius: 10px; background: var(--card); }
  .wi-tab {
    display: inline-flex; align-items: center; gap: 0.5rem;
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.78rem; font-weight: 600;
    padding: 0.55rem 1rem; border-radius: 7px; border: 0; background: transparent;
    color: var(--fg-muted); cursor: pointer; transition: background 0.15s, color 0.15s;
  }
  .wi-tab:hover { color: var(--fg); }
  .wi-tab.is-active { background: var(--accent); color: var(--bg); }

  /* Form on the left, what it books on the right; one column on a narrow screen. */
  .wi-layout { display: grid; grid-template-columns: minmax(280px, 380px) 1fr; gap: 1.25rem; align-items: start; }
  @media (max-width: 900px) { .wi-layout { grid-template-columns: 1fr; } }
  .wi-panel { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 1.2rem 1.25rem 1.3rem; }
  .wi-panel-title { margin: 0 0 0.9rem; color: var(--fg); font-size: 0.95rem; font-weight: 700; }
  .wi-form { display: grid; gap: 0.85rem; }
  .wi-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem; }
  @media (max-width: 420px) { .wi-row { grid-template-columns: 1fr; } }
  .wi-label {
    display: block; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--fg-muted); margin-bottom: 0.35rem;
  }
  .wi-label em { font-style: normal; color: var(--accent); }
  .wi-hint { margin: 0.3rem 0 0; color: var(--fg-muted); font-size: 0.7rem; }
  .wi-divider { border: 0; border-top: 1px solid var(--border); margin: 0.2rem 0; }

  .wi-step {
    width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--border);
    background: rgba(255,255,255,0.03); color: var(--fg); cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center; font-size: 1rem;
  }
  .wi-step:disabled { opacity: 0.35; cursor: not-allowed; }
  .wi-stepper {
    display: flex; align-items: center; gap: 0.6rem;
    border: 1px solid var(--border); border-radius: 6px; padding: 0.3rem 0.45rem;
    background: rgba(255,255,255,0.03); width: fit-content;
  }
  .wi-stepper output { color: var(--fg); min-width: 28px; text-align: center; font-size: 0.95rem; font-variant-numeric: tabular-nums; }

  .wi-seg { display: flex; flex-wrap: wrap; gap: 0.4rem; }
  .wi-seg button {
    font-family: var(--font-body, 'Outfit', sans-serif);
    font-size: 0.7rem; font-weight: 600; letter-spacing: 0.05em;
    padding: 0.45rem 0.85rem; border-radius: 100px; border: 1.5px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer; transition: all 0.15s;
  }
  .wi-seg button:hover { border-color: var(--accent); color: var(--accent); }
  .wi-seg button.is-active { background: var(--accent); border-color: var(--accent); color: var(--bg); }

  /* The rooms or tables the guest can have, as cards to click. */
  .wi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 0.75rem; }
  .wi-pick {
    text-align: left; background: rgba(255,255,255,0.02); border: 1.5px solid var(--border);
    border-radius: 12px; padding: 0.85rem 0.95rem; cursor: pointer; color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif);
    display: flex; flex-direction: column; gap: 0.3rem; transition: border-color 0.15s, background 0.15s;
  }
  .wi-pick:hover { border-color: rgba(201,168,76,0.5); }
  .wi-pick.is-picked { border-color: var(--accent); background: rgba(201,168,76,0.08); }
  .wi-pick-name { font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; }
  .wi-pick-name i { color: var(--accent); font-size: 0.8rem; }
  .wi-pick-sub { color: var(--fg-muted); font-size: 0.72rem; }
  .wi-pick-price { color: var(--accent-light); font-size: 0.8rem; font-weight: 600; }

  .wi-total {
    display: flex; justify-content: space-between; align-items: baseline;
    padding: 0.75rem 0.9rem; border: 1px solid var(--border); border-radius: 8px;
    background: rgba(255,255,255,0.02);
  }
  .wi-total span { color: var(--fg-muted); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; }
  .wi-total b { color: var(--fg); font-size: 1.05rem; font-variant-numeric: tabular-nums; }
  .wi-error { margin: 0; color: var(--danger, #fb7185); font-size: 0.78rem; }
  .wi-empty { border: 1px dashed var(--border); border-radius: 14px; padding: 2.25rem 1.25rem; text-align: center; color: var(--fg-muted); font-size: 0.85rem; }

  .wi-done { text-align: center; padding: 2rem 1.25rem; }
  .wi-done-icon {
    width: 54px; height: 54px; border-radius: 50%; margin: 0 auto 0.9rem;
    display: flex; align-items: center; justify-content: center;
    background: rgba(34,197,94,0.15); color: var(--success, #4ade80); font-size: 1.4rem;
  }
  .wi-done dl { display: grid; grid-template-columns: auto 1fr; gap: 0.35rem 0.9rem; text-align: left; max-width: 360px; margin: 1rem auto 1.25rem; }
  .wi-done dt { font-size: 0.6rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--fg-muted); padding-top: 0.15rem; }
  .wi-done dd { margin: 0; color: var(--fg); font-size: 0.85rem; }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ── */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d;
  }
  :root[data-ops-theme="2"] .booking-input,
  :root[data-ops-theme="2"] .wi-step,
  :root[data-ops-theme="2"] .wi-stepper,
  :root[data-ops-theme="2"] .wi-pick,
  :root[data-ops-theme="2"] .wi-total { background: rgba(27,67,50,0.03); }
  :root[data-ops-theme="2"] .wi-pick.is-picked { background: rgba(27,67,50,0.08); }
  :root[data-ops-theme="2"] .wi-pick:hover { border-color: rgba(27,67,50,0.35); }
  :root[data-ops-theme="2"] .wi-done-icon { background: #dcfce7; color: #15803d; }
  :root[data-ops-theme="2"] input[type="date"].booking-input,
  :root[data-ops-theme="2"] input[type="time"].booking-input { color-scheme: light; }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_WALK_IN = {
    backUrl: @json(route('students.dashboard', ['section' => 'tasks'])),
    roomsUrl: @json(route('students.hotel.rooms.index')),
    bookingsUrl: @json(route('students.hotel.bookings.store')),
    tablesUrl: @json(route('students.hotel.tables.index')),
    guestInfoUrl: @json(route('students.frontdesk.verify-guest')),
    dineInUrl: @json(route('students.frontdesk.dine-in')),
    startTab: @json(request('tab') === 'table' ? 'table' : 'room'),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo, useRef } = React;

const CFG = window.HMS_WALK_IN;
// Rooms are priced per 12-hour block (HotelBooking::BLOCK_HOURS), so a night is two.
const BLOCKS_PER_NIGHT = 2;
const PAYMENT_METHODS = ['Cash', 'Card', 'GCash', 'Bank Transfer'];

function csrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function pad2(n) { return String(n).padStart(2, '0'); }
function isoDate(d) { return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate()); }
function today() { return isoDate(new Date()); }
function addDays(iso, days) {
  const [y, m, d] = iso.split('-').map(Number);
  return isoDate(new Date(y, m - 1, d + days));
}
function nowClock() { const d = new Date(); return pad2(d.getHours()) + ':' + pad2(d.getMinutes()); }
function peso(n) { return '₱' + Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 }); }
function niceDate(iso) {
  const [y, m, d] = iso.split('-').map(Number);
  return new Date(y, m - 1, d).toLocaleDateString([], { weekday: 'short', month: 'short', day: 'numeric' });
}

/* Free for [from, to): no open booking on the room overlaps those dates. The
   server checks again under a lock; this only keeps taken rooms off the list. */
function freeFor(room, from, to) {
  return !(room.bookedRanges || []).some(r => r.from < to && r.to > from);
}

async function send(url, method, payload) {
  const res = await fetch(url, {
    method,
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
    body: JSON.stringify(payload),
  }).catch(() => { throw new Error('You seem to be offline. Nothing was booked — try again.'); });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const first = data.errors && Object.values(data.errors)[0];
    throw new Error((Array.isArray(first) && first[0]) || data.message || 'That did not go through. Nothing was booked — try again.');
  }
  return data;
}

function Stepper({ value, min, max, onChange, label }) {
  return (
    <div className="wi-stepper">
      <button type="button" className="wi-step" aria-label={'Fewer ' + label} disabled={value <= min}
        onClick={() => onChange(Math.max(min, value - 1))}>−</button>
      <output>{value}</output>
      <button type="button" className="wi-step" aria-label={'More ' + label} disabled={value >= max}
        onClick={() => onChange(Math.min(max, value + 1))}>+</button>
    </div>
  );
}

function Done({ title, rows, note, primary, onAgain }) {
  return (
    <div className="wi-panel wi-done">
      <div className="wi-done-icon"><i className="fa-solid fa-check"></i></div>
      <h2 className="font-display" style={{ margin: 0, fontSize: '1.5rem', color: 'var(--fg)' }}>{title}</h2>
      <dl>
        {rows.map(([k, v]) => (<React.Fragment key={k}><dt>{k}</dt><dd>{v}</dd></React.Fragment>))}
      </dl>
      {note && <p className="wi-hint" style={{ maxWidth: 420, margin: '0 auto 1.25rem' }}>{note}</p>}
      <div style={{ display: 'flex', gap: '0.6rem', justifyContent: 'center', flexWrap: 'wrap' }}>
        <button type="button" className="btn-solid" onClick={onAgain}>
          <i className="fa-solid fa-plus" style={{ fontSize: '0.7rem' }}></i> Next walk-in
        </button>
        <a className="btn-outline" href={primary.href}>{primary.label}</a>
      </div>
    </div>
  );
}

/* ── A room for a guest standing at the desk ─────────────────────────── */
function RoomWalkIn() {
  const [rooms, setRooms] = useState([]);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [fullName, setFullName] = useState('');
  const [contactNo, setContactNo] = useState('');
  const [email, setEmail] = useState('');
  const [idNumber, setIdNumber] = useState('');
  const [nights, setNights] = useState(1);
  const [checkInTime, setCheckInTime] = useState(nowClock);
  const [category, setCategory] = useState('All');
  const [roomId, setRoomId] = useState(null);
  const [payType, setPayType] = useState('Full');
  const [amount, setAmount] = useState('');
  const [method, setMethod] = useState('Cash');
  const [notes, setNotes] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const [done, setDone] = useState(null);
  const writing = useRef(false);

  const load = useCallback(() => {
    if (writing.current) return;
    fetch(CFG.roomsUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : Promise.reject()))
      .then(data => {
        if (writing.current) return;
        setRooms(Array.isArray(data.rooms) ? data.rooms : []);
        setLoadError('');
        setLoaded(true);
      })
      .catch(() => { setLoadError('Could not load the rooms. Retrying…'); setLoaded(true); });
  }, []);

  useEffect(() => {
    load();
    const id = setInterval(() => { if (!document.hidden) load(); }, 10000);
    window.addEventListener('focus', load);
    return () => { clearInterval(id); window.removeEventListener('focus', load); };
  }, [load]);

  const checkIn = today();
  const checkOut = addDays(checkIn, nights);

  /* Ready now and free for the whole stay. A room Housekeeping has not cleared
     can still be sold for next week, but not handed to someone standing here. */
  const free = useMemo(
    () => rooms.filter(r => r.status === 'Available' && freeFor(r, checkIn, checkOut)),
    [rooms, checkIn, checkOut]
  );
  const categories = useMemo(() => ['All', ...Array.from(new Set(free.map(r => r.category).filter(Boolean)))], [free]);
  const shown = category === 'All' ? free : free.filter(r => r.category === category);
  const room = free.find(r => r.dbId === roomId) || null;
  const total = room ? room.price * nights * BLOCKS_PER_NIGHT : 0;

  // A picked room that stopped being free (a teammate booked it) is dropped.
  useEffect(() => { if (roomId && !room) setRoomId(null); }, [roomId, room]);
  useEffect(() => { if (!categories.includes(category)) setCategory('All'); }, [categories, category]);

  const reset = () => {
    setFullName(''); setContactNo(''); setEmail(''); setIdNumber('');
    setNights(1); setCheckInTime(nowClock()); setRoomId(null);
    setPayType('Full'); setAmount(''); setMethod('Cash'); setNotes('');
    setError(''); setDone(null); load();
  };

  const submit = async (e) => {
    e.preventDefault();
    if (!fullName.trim()) return setError('Enter the guest’s full name.');
    if (!contactNo.trim()) return setError('Enter a contact number for the guest.');
    if (email.trim() && !/^\S+@\S+\.\S+$/.test(email.trim())) return setError('That email address does not look right.');
    if (!room) return setError('Pick a room for the guest.');
    let payment = null;
    if (payType !== 'None') {
      const paid = payType === 'Full' ? total : Number(amount);
      if (!(paid > 0)) return setError('Enter how much the guest is paying now.');
      if (paid > total) return setError('A deposit cannot be more than the stay costs (' + peso(total) + ').');
      payment = { type: payType === 'Full' ? 'Full' : 'Partial', amount_paid: paid, method, payer_name: fullName.trim() };
    }
    setError('');
    setBusy(true);
    writing.current = true;
    try {
      const data = await send(CFG.bookingsUrl, 'POST', {
        room_id: room.dbId,
        guest: {
          full_name: fullName.trim(),
          contact_no: contactNo.trim(),
          email: email.trim() || null,
          id_number: idNumber.trim() || null,
        },
        check_in: checkIn,
        check_in_time: checkInTime,
        check_out: checkOut,
        notes: ('Walk-in guest. ' + notes.trim()).trim(),
        payment,
        arrived: true,
      });
      setDone({ booking: data.booking, room, total, paid: payment ? payment.amount_paid : 0 });
      if (window.toast) window.toast('Booked ' + fullName.trim() + ' into ' + room.name);
    } catch (err) {
      setError(err.message);
      // The room may have gone to someone else: refresh the list either way.
      writing.current = false;
      load();
    } finally {
      writing.current = false;
      setBusy(false);
    }
  };

  if (done) {
    const balance = Math.max(0, done.total - done.paid);
    return (
      <Done
        title={'Welcome, ' + fullName.trim()}
        rows={[
          ['Room', done.room.name + (done.room.category ? ' · ' + done.room.category : '')],
          ['Stay', niceDate(checkIn) + ' → ' + niceDate(checkOut) + ' (' + nights + ' night' + (nights === 1 ? '' : 's') + ')'],
          ['Status', 'Arrived'],
          ['Paid', peso(done.paid)],
          ['Balance', peso(balance)],
        ]}
        note="Room Management hands over the room and checks the guest in. Any balance is settled at check-out from Guest Information."
        primary={{ href: CFG.guestInfoUrl, label: 'Open Guest Information' }}
        onAgain={reset}
      />
    );
  }

  return (
    <form className="wi-layout" onSubmit={submit} noValidate>
      <div className="wi-panel">
        <h2 className="wi-panel-title">Guest</h2>
        <div className="wi-form">
          <div>
            <label className="wi-label">Full name <em>*</em></label>
            <input className="booking-input" value={fullName} onChange={e => setFullName(e.target.value)} placeholder="As on their ID" autoFocus />
          </div>
          <div>
            <label className="wi-label">Contact number <em>*</em></label>
            <input type="tel" className="booking-input" value={contactNo} onChange={e => setContactNo(e.target.value)} placeholder="09XX XXX XXXX" />
          </div>
          <div className="wi-row">
            <div>
              <label className="wi-label">Email</label>
              <input type="email" className="booking-input" value={email} onChange={e => setEmail(e.target.value)} placeholder="Optional" />
            </div>
            <div>
              <label className="wi-label">ID number</label>
              <input className="booking-input" value={idNumber} onChange={e => setIdNumber(e.target.value)} placeholder="Optional" />
            </div>
          </div>

          <hr className="wi-divider" />

          <div className="wi-row">
            <div>
              <label className="wi-label">Nights</label>
              <Stepper value={nights} min={1} max={30} onChange={setNights} label="nights" />
            </div>
            <div>
              <label className="wi-label">Check-in time</label>
              <input type="time" className="booking-input" value={checkInTime} onChange={e => setCheckInTime(e.target.value)} />
            </div>
          </div>
          <p className="wi-hint" style={{ marginTop: '-0.4rem' }}>
            Today, {niceDate(checkIn)} → {niceDate(checkOut)}
          </p>

          <hr className="wi-divider" />

          <div>
            <label className="wi-label">Payment now</label>
            <div className="wi-seg">
              {[['Full', 'Pay in full'], ['Partial', 'Deposit'], ['None', 'Pay at check-out']].map(([key, text]) => (
                <button key={key} type="button" className={payType === key ? 'is-active' : ''} onClick={() => setPayType(key)}>{text}</button>
              ))}
            </div>
          </div>
          {payType !== 'None' && (
            <div className="wi-row">
              <div>
                <label className="wi-label">Amount</label>
                {payType === 'Full'
                  ? <input className="booking-input" value={room ? peso(total) : 'Pick a room'} readOnly />
                  : <input type="number" min="1" step="0.01" className="booking-input" value={amount} onChange={e => setAmount(e.target.value)} placeholder="0.00" />}
              </div>
              <div>
                <label className="wi-label">Method</label>
                <select className="booking-input" value={method} onChange={e => setMethod(e.target.value)}>
                  {PAYMENT_METHODS.map(m => <option key={m} value={m}>{m}</option>)}
                </select>
              </div>
            </div>
          )}

          <div>
            <label className="wi-label">Notes</label>
            <input className="booking-input" value={notes} onChange={e => setNotes(e.target.value)} placeholder="Optional — e.g. late check-out asked" />
          </div>

          <div className="wi-total">
            <span>{room ? nights + ' night' + (nights === 1 ? '' : 's') + ' · ' + room.name : 'Total'}</span>
            <b>{room ? peso(total) : '—'}</b>
          </div>

          {error && <p className="wi-error" role="alert">{error}</p>}

          <button type="submit" className="btn-solid" disabled={busy}>
            <i className="fa-solid fa-key" style={{ fontSize: '0.7rem' }}></i>
            {busy ? 'Booking…' : 'Book room & mark arrived'}
          </button>
        </div>
      </div>

      <div className="wi-panel">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '0.8rem', flexWrap: 'wrap', marginBottom: '0.9rem' }}>
          <h2 className="wi-panel-title" style={{ margin: 0 }}>Rooms ready now · {free.length}</h2>
          {categories.length > 2 && (
            <div className="wi-seg">
              {categories.map(c => (
                <button key={c} type="button" className={category === c ? 'is-active' : ''} onClick={() => setCategory(c)}>{c}</button>
              ))}
            </div>
          )}
        </div>
        {loadError && <p className="wi-error" style={{ marginBottom: '0.7rem' }}>{loadError}</p>}
        {!loaded ? (
          <div className="wi-empty">Loading rooms…</div>
        ) : shown.length === 0 ? (
          <div className="wi-empty">
            No room is clean and free for {nights === 1 ? 'tonight' : 'those ' + nights + ' nights'}.
            {nights > 1 ? ' Try a shorter stay.' : ' Rooms appear here once Housekeeping clears them.'}
          </div>
        ) : (
          <div className="wi-grid">
            {shown.map(r => (
              <button key={r.dbId} type="button" className={'wi-pick' + (r.dbId === roomId ? ' is-picked' : '')}
                onClick={() => setRoomId(r.dbId)} aria-pressed={r.dbId === roomId}>
                <span className="wi-pick-name">{r.name}{r.dbId === roomId && <i className="fa-solid fa-circle-check"></i>}</span>
                <span className="wi-pick-sub">{r.category || 'Room'}</span>
                <span className="wi-pick-price">{peso(r.price * BLOCKS_PER_NIGHT)} / night</span>
              </button>
            ))}
          </div>
        )}
      </div>
    </form>
  );
}

/* ── A table for a guest standing at the desk ────────────────────────── */
function TableWalkIn() {
  const [tables, setTables] = useState([]);
  const [canAssign, setCanAssign] = useState(true);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [guestName, setGuestName] = useState('');
  const [contactNo, setContactNo] = useState('');
  const [party, setParty] = useState(2);
  const [when, setWhen] = useState('now');
  const [onDate, setOnDate] = useState(today);
  const [atTime, setAtTime] = useState(nowClock);
  const [tableId, setTableId] = useState(null);
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const [done, setDone] = useState(null);
  const writing = useRef(false);

  const load = useCallback(() => {
    if (writing.current) return;
    fetch(CFG.tablesUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : Promise.reject()))
      .then(data => {
        if (writing.current) return;
        setTables(Array.isArray(data.tables) ? data.tables : []);
        setCanAssign(!!data.can_assign);
        setLoadError('');
        setLoaded(true);
      })
      .catch(() => { setLoadError('Could not load the tables. Retrying…'); setLoaded(true); });
  }, []);

  useEffect(() => {
    load();
    const id = setInterval(() => { if (!document.hidden) load(); }, 8000);
    window.addEventListener('focus', load);
    return () => { clearInterval(id); window.removeEventListener('focus', load); };
  }, [load]);

  const biggest = tables.reduce((n, t) => Math.max(n, t.capacity || 0), 1);
  // Smallest table that fits first, so a couple does not take the eight-seater.
  const fits = useMemo(
    () => tables.filter(t => t.status === 'Available' && t.capacity >= party).sort((a, b) => a.capacity - b.capacity),
    [tables, party]
  );
  const table = fits.find(t => t.id === tableId) || null;

  useEffect(() => { if (tableId && !table) setTableId(null); }, [tableId, table]);

  const reset = () => {
    setGuestName(''); setContactNo(''); setParty(2); setWhen('now');
    setOnDate(today()); setAtTime(nowClock()); setTableId(null);
    setError(''); setDone(null); load();
  };

  const submit = async (e) => {
    e.preventDefault();
    if (!guestName.trim()) return setError('Enter the guest’s name.');
    if (!contactNo.trim()) return setError('Enter a contact number for the guest.');
    if (!table) return setError('Pick a table for the party.');
    if (when === 'later') {
      if (!onDate || !atTime) return setError('Pick the date and time they are coming back.');
      if (new Date(onDate + 'T' + atTime) < new Date(Date.now() - 60000)) return setError('That time has already passed.');
    }
    setError('');
    setBusy(true);
    writing.current = true;
    try {
      const payload = { guest_name: guestName.trim(), contact_no: contactNo.trim(), party_size: party };
      if (when === 'now') payload.seat_now = true;
      else payload.reserved_for = onDate + ' ' + atTime + ':00';
      const data = await send(CFG.tablesUrl + '/' + table.id, 'PATCH', payload);
      setDone({ table: data.table || table });
      if (window.toast) window.toast((when === 'now' ? 'Seated ' : 'Reserved ' + table.name + ' for ') + guestName.trim() + (when === 'now' ? ' at ' + table.name : ''));
    } catch (err) {
      setError(err.message);
      writing.current = false;
      load();
    } finally {
      writing.current = false;
      setBusy(false);
    }
  };

  if (done) {
    return (
      <Done
        title={when === 'now' ? guestName.trim() + ' is seated' : 'Table held for ' + guestName.trim()}
        rows={[
          ['Table', done.table.name + ' · seats ' + done.table.capacity],
          ['Party', String(party)],
          ['When', when === 'now' ? 'Now' : niceDate(onDate) + ', ' + atTime],
          ['Status', when === 'now' ? 'Occupied' : 'Reserved'],
        ]}
        note={when === 'now'
          ? 'The restaurant takes their order from here and settles the bill.'
          : 'When they come back, press Customer Arrived on Dine-in Tables to seat them.'}
        primary={{ href: CFG.dineInUrl, label: 'Open Dine-in Tables' }}
        onAgain={reset}
      />
    );
  }

  if (loaded && !canAssign) {
    return <div className="wi-empty">Only Front Desk staff can seat or reserve a table for a guest.</div>;
  }

  return (
    <form className="wi-layout" onSubmit={submit} noValidate>
      <div className="wi-panel">
        <h2 className="wi-panel-title">Party</h2>
        <div className="wi-form">
          <div>
            <label className="wi-label">Guest name <em>*</em></label>
            <input className="booking-input" value={guestName} onChange={e => setGuestName(e.target.value)} placeholder="Who's dining?" autoFocus />
          </div>
          <div>
            <label className="wi-label">Contact number <em>*</em></label>
            <input type="tel" className="booking-input" value={contactNo} onChange={e => setContactNo(e.target.value)} placeholder="09XX XXX XXXX" />
          </div>
          <div>
            <label className="wi-label">Party size</label>
            <Stepper value={party} min={1} max={Math.max(1, biggest)} onChange={setParty} label="guests" />
          </div>

          <hr className="wi-divider" />

          <div>
            <label className="wi-label">When</label>
            <div className="wi-seg">
              <button type="button" className={when === 'now' ? 'is-active' : ''} onClick={() => setWhen('now')}>Seat now</button>
              <button type="button" className={when === 'later' ? 'is-active' : ''} onClick={() => setWhen('later')}>Reserve for later</button>
            </div>
          </div>
          {when === 'later' && (
            <div className="wi-row">
              <div>
                <label className="wi-label">Date</label>
                <input type="date" className="booking-input" value={onDate} min={today()} onChange={e => setOnDate(e.target.value)} />
              </div>
              <div>
                <label className="wi-label">Time</label>
                <input type="time" className="booking-input" value={atTime} onChange={e => setAtTime(e.target.value)} />
              </div>
            </div>
          )}

          {error && <p className="wi-error" role="alert">{error}</p>}

          <button type="submit" className="btn-solid" disabled={busy}>
            <i className="fa-solid fa-chair" style={{ fontSize: '0.7rem' }}></i>
            {busy ? 'Saving…' : (when === 'now' ? 'Seat guest now' : 'Reserve table')}
          </button>
        </div>
      </div>

      <div className="wi-panel">
        <h2 className="wi-panel-title">Free tables for {party} · {fits.length}</h2>
        {loadError && <p className="wi-error" style={{ marginBottom: '0.7rem' }}>{loadError}</p>}
        {!loaded ? (
          <div className="wi-empty">Loading tables…</div>
        ) : tables.length === 0 ? (
          <div className="wi-empty">Restaurant Management hasn't added any tables yet.</div>
        ) : fits.length === 0 ? (
          <div className="wi-empty">No free table seats a party of {party} right now.</div>
        ) : (
          <div className="wi-grid">
            {fits.map(t => (
              <button key={t.id} type="button" className={'wi-pick' + (t.id === tableId ? ' is-picked' : '')}
                onClick={() => setTableId(t.id)} aria-pressed={t.id === tableId}>
                <span className="wi-pick-name">{t.name}{t.id === tableId && <i className="fa-solid fa-circle-check"></i>}</span>
                <span className="wi-pick-sub">Seats {t.capacity}</span>
              </button>
            ))}
          </div>
        )}
      </div>
    </form>
  );
}

function App() {
  const [tab, setTab] = useState(CFG.startTab);
  return (
    <div data-hms-no-edit="1" style={{ padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.25rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Front Desk</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Walk-in Guests</h1>
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem', maxWidth: 520 }}>
            For a guest who arrives without a booking: give them a room for tonight, or a table in the restaurant.
          </p>
        </div>
        <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </a>
      </div>

      <div className="wi-tabs" role="tablist" style={{ marginBottom: '1.25rem' }}>
        <button type="button" role="tab" aria-selected={tab === 'room'} className={'wi-tab' + (tab === 'room' ? ' is-active' : '')} onClick={() => setTab('room')}>
          <i className="fa-solid fa-bed"></i> Book a Room
        </button>
        <button type="button" role="tab" aria-selected={tab === 'table'} className={'wi-tab' + (tab === 'table' ? ' is-active' : '')} onClick={() => setTab('table')}>
          <i className="fa-solid fa-utensils"></i> Reserve a Table
        </button>
      </div>

      {tab === 'room' ? <RoomWalkIn /> : <TableWalkIn />}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
