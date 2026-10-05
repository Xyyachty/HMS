@extends('students.builder.ops-shell')

@section('page-title', 'Walk-in Guests')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }

  /* Everything below reads the shell's tokens (--bg, --card, --border, --fg,
     --fg-muted, --accent), so the page follows Template 1, Template 2 and a
     team's own site colours without a palette of its own. Tints are mixed from
     those tokens rather than hard-coded.

     Shape rule: choice chips are pills; inputs and buttons are 10px; panels and
     cards are 14px. */
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }

  .wi {
    --wi-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --wi-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --wi-line: var(--border);
    --wi-danger: var(--danger, #f87171);
    --wi-ok: var(--success, #4ade80);
    padding: 1.5rem 1.5rem 3rem;
    color: var(--fg);
  }

  /* Page header */
  .wi-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
  .wi-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .wi-head p { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 56ch; line-height: 1.5; }

  /* Buttons */
  .wi-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem;
    font: 600 0.9rem/1 var(--font-body, 'Outfit', sans-serif);
    padding: 0.85rem 1.2rem; border-radius: 10px; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); transition: filter 0.15s, background 0.15s, transform 0.1s;
  }
  .wi-btn:active { transform: translateY(1px); }
  .wi-btn-primary { background: var(--accent); color: var(--bg); width: 100%; padding: 1rem 1.2rem; font-size: 0.95rem; }
  .wi-btn-primary:hover { filter: brightness(1.08); }
  .wi-btn-primary:disabled { opacity: 0.5; cursor: progress; filter: none; }
  .wi-btn-ghost { background: transparent; color: var(--accent); }
  .wi-btn-ghost:hover { background: var(--wi-tint); }
  .wi-btn:focus-visible, .wi-mode:focus-visible, .wi-chip:focus-visible, .wi-card:focus-visible, .wi-step-btn:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  /* What is the guest here for: two large choices */
  .wi-modes { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.85rem; margin-bottom: 1.5rem; }
  .wi-mode {
    display: flex; align-items: center; gap: 0.95rem; text-align: left;
    padding: 1rem 1.1rem; border-radius: 14px; cursor: pointer;
    background: var(--card); border: 1.5px solid var(--wi-line); color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif); transition: border-color 0.15s, background 0.15s;
  }
  .wi-mode:hover { border-color: color-mix(in srgb, var(--accent) 55%, var(--wi-line)); }
  .wi-mode.is-on { border-color: var(--accent); background: var(--wi-tint); }
  .wi-mode-icon {
    flex: none; width: 46px; height: 46px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
    background: var(--wi-soft); color: var(--accent);
  }
  .wi-mode.is-on .wi-mode-icon { background: var(--accent); color: var(--bg); }
  .wi-mode-text b { display: block; font-size: 1rem; }
  .wi-mode-text small { display: block; color: var(--fg-muted); font-size: 0.82rem; margin-top: 0.15rem; }

  /* Form beside its summary; one column on a narrow screen */
  .wi-layout { display: grid; grid-template-columns: minmax(0, 1fr) clamp(320px, 26vw, 400px); gap: 1.25rem; align-items: start; }
  .wi-steps { display: grid; gap: 1rem; min-width: 0; }

  .wi-section { background: var(--card); border: 1px solid var(--wi-line); border-radius: 14px; padding: 1.2rem 1.3rem 1.35rem; }
  .wi-section-head { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
  .wi-num {
    flex: none; width: 30px; height: 30px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem; font-weight: 700; font-variant-numeric: tabular-nums;
    border: 1.5px solid var(--wi-line); color: var(--fg-muted);
  }
  .wi-section.is-done .wi-num { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .wi-section-head h2 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .wi-section-head p { margin: 0.15rem 0 0; font-size: 0.8rem; color: var(--fg-muted); }
  .wi-section-head .wi-spacer { flex: 1; }

  .wi-fields { display: grid; gap: 0.95rem; }
  .wi-two { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.85rem; }
  .wi-field label { display: block; font-size: 0.84rem; font-weight: 600; color: var(--fg); margin-bottom: 0.4rem; }
  .wi-field label small { font-weight: 400; color: var(--fg-muted); font-size: 0.78rem; margin-left: 0.3rem; }
  .wi-field .wi-help { margin: 0.35rem 0 0; font-size: 0.78rem; color: var(--fg-muted); }
  .wi-field .wi-bad { margin: 0.35rem 0 0; font-size: 0.78rem; color: var(--wi-danger); }

  .wi-input {
    width: 100%; box-sizing: border-box;
    background: var(--wi-soft); border: 1px solid var(--wi-line); border-radius: 10px;
    padding: 0.8rem 0.9rem; color: var(--fg); outline: none;
    font: 400 0.95rem/1.3 var(--font-body, 'Outfit', sans-serif);
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .wi-input::placeholder { color: var(--fg-muted); opacity: 0.75; }
  .wi-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--wi-tint); }
  .wi-input.is-bad { border-color: var(--wi-danger); }
  .wi-input[readonly] { color: var(--fg-muted); }
  input[type="date"].wi-input, input[type="time"].wi-input { color-scheme: dark; }

  /* Bigger + / - for touch */
  .wi-stepper { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.3rem; border-radius: 10px; background: var(--wi-soft); border: 1px solid var(--wi-line); }
  .wi-step-btn {
    width: 42px; height: 42px; border-radius: 8px; border: 0; cursor: pointer;
    background: var(--card); color: var(--fg); font-size: 0.95rem;
    display: inline-flex; align-items: center; justify-content: center;
  }
  .wi-step-btn:hover:not(:disabled) { color: var(--accent); }
  .wi-step-btn:disabled { opacity: 0.35; cursor: not-allowed; }
  .wi-stepper output { min-width: 3.2rem; text-align: center; font-size: 1.05rem; font-weight: 700; font-variant-numeric: tabular-nums; }
  .wi-stepper output small { display: block; font-size: 0.7rem; font-weight: 500; color: var(--fg-muted); }

  /* Pill choices */
  .wi-chips { display: flex; flex-wrap: wrap; gap: 0.45rem; }
  .wi-chip {
    display: inline-flex; align-items: center; gap: 0.45rem;
    font: 600 0.84rem/1 var(--font-body, 'Outfit', sans-serif);
    padding: 0.6rem 0.95rem; border-radius: 999px; cursor: pointer;
    border: 1.5px solid var(--wi-line); background: transparent; color: var(--fg-muted);
    transition: border-color 0.15s, color 0.15s, background 0.15s;
  }
  .wi-chip:hover { border-color: var(--accent); color: var(--fg); }
  .wi-chip.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); }

  .wi-dates {
    display: flex; align-items: center; gap: 0.7rem; flex-wrap: wrap;
    padding: 0.75rem 0.9rem; border-radius: 10px; background: var(--wi-soft); font-size: 0.86rem;
  }
  .wi-dates i { color: var(--accent); }
  .wi-dates b { color: var(--fg); font-weight: 600; }
  .wi-dates span { color: var(--fg-muted); }

  /* Rooms and tables to pick from */
  .wi-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(190px, 100%), 1fr)); gap: 0.8rem; }
  .wi-card {
    position: relative; text-align: left; padding: 0; overflow: hidden; cursor: pointer;
    background: var(--wi-soft); border: 1.5px solid var(--wi-line); border-radius: 14px; color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif);
    display: flex; flex-direction: column; transition: border-color 0.15s, transform 0.15s;
  }
  .wi-card:hover { border-color: color-mix(in srgb, var(--accent) 55%, var(--wi-line)); transform: translateY(-1px); }
  .wi-card.is-on { border-color: var(--accent); box-shadow: 0 0 0 3px var(--wi-tint); }
  .wi-card-photo { aspect-ratio: 16 / 10; background: var(--wi-soft); display: flex; align-items: center; justify-content: center; color: var(--fg-muted); font-size: 1.4rem; }
  .wi-card-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .wi-card-body { padding: 0.75rem 0.85rem 0.85rem; display: grid; gap: 0.2rem; }
  .wi-card-name { font-weight: 700; font-size: 0.98rem; }
  .wi-card-sub { color: var(--fg-muted); font-size: 0.78rem; }
  .wi-card-price { color: var(--fg); font-size: 0.86rem; font-weight: 600; margin-top: 0.25rem; }
  .wi-card-price small { color: var(--fg-muted); font-weight: 400; }
  .wi-card-tick {
    position: absolute; top: 0.55rem; right: 0.55rem; width: 26px; height: 26px; border-radius: 50%;
    background: var(--accent); color: var(--bg); display: flex; align-items: center; justify-content: center; font-size: 0.75rem;
  }
  .wi-table-icon { aspect-ratio: auto; padding: 1.1rem 0 0.4rem; background: transparent; color: var(--accent); }

  .wi-empty {
    border: 1.5px dashed var(--wi-line); border-radius: 14px; padding: 2rem 1.25rem;
    text-align: center; color: var(--fg-muted); font-size: 0.9rem; line-height: 1.5;
  }
  .wi-empty i { display: block; font-size: 1.4rem; margin-bottom: 0.6rem; color: var(--accent); }
  .wi-skeleton { border-radius: 14px; background: var(--wi-soft); aspect-ratio: 16 / 13; animation: wi-pulse 1.4s ease-in-out infinite; }
  @keyframes wi-pulse { 50% { opacity: 0.5; } }
  @media (prefers-reduced-motion: reduce) { .wi-skeleton { animation: none; } .wi-card:hover { transform: none; } }

  /* Summary: stays in view while the form scrolls */
  .wi-summary { position: sticky; top: 1rem; background: var(--card); border: 1px solid var(--wi-line); border-radius: 14px; padding: 1.2rem 1.25rem 1.3rem; display: grid; gap: 1rem; }
  .wi-summary h3 { margin: 0; font-size: 1rem; font-weight: 700; color: var(--fg); }
  .wi-sum-rows { display: grid; gap: 0.55rem; margin: 0; }
  .wi-sum-rows div { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.86rem; }
  .wi-sum-rows dt { color: var(--fg-muted); }
  .wi-sum-rows dd { margin: 0; text-align: right; color: var(--fg); font-weight: 500; }
  .wi-sum-total { display: flex; justify-content: space-between; align-items: baseline; padding-top: 0.9rem; border-top: 1px solid var(--wi-line); }
  .wi-sum-total span { color: var(--fg-muted); font-size: 0.86rem; }
  .wi-sum-total b { font-size: 1.45rem; font-variant-numeric: tabular-nums; color: var(--fg); }
  .wi-todo { list-style: none; margin: 0; padding: 0; display: grid; gap: 0.45rem; }
  .wi-todo li { display: flex; align-items: center; gap: 0.55rem; font-size: 0.84rem; color: var(--fg-muted); }
  .wi-todo li i { width: 1rem; text-align: center; }
  .wi-todo li.is-ok { color: var(--fg); }
  .wi-todo li.is-ok i { color: var(--wi-ok); }
  .wi-alert {
    display: flex; gap: 0.6rem; align-items: flex-start; margin: 0;
    padding: 0.75rem 0.85rem; border-radius: 10px; font-size: 0.84rem; line-height: 1.45;
    color: var(--wi-danger); background: color-mix(in srgb, var(--wi-danger) 10%, transparent);
  }
  .wi-note { margin: 0; font-size: 0.78rem; color: var(--fg-muted); line-height: 1.5; }

  /* Booked */
  .wi-done { max-width: 560px; margin: 0 auto; background: var(--card); border: 1px solid var(--wi-line); border-radius: 14px; padding: 2rem 1.6rem 1.7rem; text-align: center; }
  .wi-done-icon {
    width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 1rem;
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
    background: color-mix(in srgb, var(--wi-ok) 16%, transparent); color: var(--wi-ok);
  }
  .wi-done h2 { margin: 0; font-size: 1.55rem; color: var(--fg); }
  .wi-done dl { margin: 1.3rem 0; text-align: left; background: var(--wi-soft); border-radius: 10px; padding: 0.9rem 1rem; }
  .wi-done-actions { display: flex; gap: 0.6rem; justify-content: center; flex-wrap: wrap; }
  .wi-done-actions .wi-btn-primary { width: auto; }

  @media (max-width: 1000px) {
    .wi-layout { grid-template-columns: minmax(0, 1fr); }
    .wi-summary { position: static; min-width: 0; }
  }
  @media (max-width: 640px) {
    .wi { padding: 1rem 1rem 2.5rem; }
    .wi-modes, .wi-two { grid-template-columns: 1fr; }
    .wi-head h1 { font-size: 1.5rem; }
  }

  /* Template 2 (cream / forest green / DM Sans + Cormorant Garamond) */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #6b6560;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #c81e3a; --success: #15803d;
  }
  :root[data-ops-theme="2"] input[type="date"].wi-input,
  :root[data-ops-theme="2"] input[type="time"].wi-input { color-scheme: light; }
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
const PAYMENT_METHODS = [
  ['Cash', 'fa-money-bill-wave'],
  ['Card', 'fa-credit-card'],
  ['GCash', 'fa-mobile-screen'],
  ['Bank Transfer', 'fa-building-columns'],
];

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
function niceTime(hhmm) {
  if (!hhmm) return '';
  const [h, m] = hhmm.split(':').map(Number);
  return new Date(2000, 0, 1, h, m).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}
function plural(n, word) { return n + ' ' + word + (n === 1 ? '' : 's'); }

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
  }).catch(() => { throw new Error('You seem to be offline. Nothing was booked. Try again.'); });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const first = data.errors && Object.values(data.errors)[0];
    throw new Error((Array.isArray(first) && first[0]) || data.message || 'That did not go through. Nothing was booked. Try again.');
  }
  return data;
}

/* ── Small building blocks ─────────────────────────────────────────────── */

function Section({ n, title, hint, done, aside, children }) {
  return (
    <section className={'wi-section' + (done ? ' is-done' : '')}>
      <div className="wi-section-head">
        <span className="wi-num" aria-hidden="true">{done ? <i className="fa-solid fa-check"></i> : n}</span>
        <div>
          <h2>{title}</h2>
          {hint && <p>{hint}</p>}
        </div>
        <span className="wi-spacer"></span>
        {aside}
      </div>
      {children}
    </section>
  );
}

function Field({ id, label, optional, help, bad, children }) {
  return (
    <div className="wi-field">
      <label htmlFor={id}>{label}{optional && <small>(optional)</small>}</label>
      {children}
      {bad ? <p className="wi-bad" role="alert">{bad}</p> : help ? <p className="wi-help">{help}</p> : null}
    </div>
  );
}

function Stepper({ value, min, max, onChange, unit }) {
  return (
    <div className="wi-stepper">
      <button type="button" className="wi-step-btn" aria-label={'Fewer ' + unit + 's'} disabled={value <= min}
        onClick={() => onChange(Math.max(min, value - 1))}><i className="fa-solid fa-minus"></i></button>
      <output aria-live="polite">{value}<small>{value === 1 ? unit : unit + 's'}</small></output>
      <button type="button" className="wi-step-btn" aria-label={'More ' + unit + 's'} disabled={value >= max}
        onClick={() => onChange(Math.min(max, value + 1))}><i className="fa-solid fa-plus"></i></button>
    </div>
  );
}

function Chips({ options, value, onChange, label }) {
  return (
    <div className="wi-chips" role="radiogroup" aria-label={label}>
      {options.map(([key, text, icon]) => (
        <button key={key} type="button" role="radio" aria-checked={value === key}
          className={'wi-chip' + (value === key ? ' is-on' : '')} onClick={() => onChange(key)}>
          {icon && <i className={'fa-solid ' + icon}></i>}{text}
        </button>
      ))}
    </div>
  );
}

function CardSkeletons() {
  return <div className="wi-cards">{[0, 1, 2].map(i => <div key={i} className="wi-skeleton"></div>)}</div>;
}

/* The right-hand panel: what is being booked, what is still missing, and the button. */
function Summary({ title, rows, total, todo, error, busy, button, note }) {
  return (
    <aside className="wi-summary" aria-label="Summary">
      <h3>{title}</h3>
      <dl className="wi-sum-rows">
        {rows.map(([k, v]) => (
          <div key={k}><dt>{k}</dt><dd>{v || <span style={{ color: 'var(--fg-muted)' }}>Not set</span>}</dd></div>
        ))}
      </dl>
      {total && (
        <div className="wi-sum-total"><span>{total[0]}</span><b>{total[1]}</b></div>
      )}
      <ul className="wi-todo">
        {todo.map(([text, ok]) => (
          <li key={text} className={ok ? 'is-ok' : ''}>
            <i className={ok ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle'}></i>{text}
          </li>
        ))}
      </ul>
      {error && <p className="wi-alert" role="alert"><i className="fa-solid fa-circle-exclamation" style={{ marginTop: 2 }}></i>{error}</p>}
      <button type="submit" className="wi-btn wi-btn-primary" disabled={busy}>
        <i className={'fa-solid ' + (busy ? 'fa-spinner fa-spin' : button[1])}></i>{busy ? 'Saving…' : button[0]}
      </button>
      {note && <p className="wi-note">{note}</p>}
    </aside>
  );
}

function Done({ title, rows, note, primary, onAgain }) {
  return (
    <div className="wi-done">
      <div className="wi-done-icon"><i className="fa-solid fa-check"></i></div>
      <h2 className="font-display">{title}</h2>
      <dl className="wi-sum-rows">
        {rows.map(([k, v]) => (<div key={k}><dt>{k}</dt><dd>{v}</dd></div>))}
      </dl>
      {note && <p className="wi-note" style={{ marginBottom: '1.3rem' }}>{note}</p>}
      <div className="wi-done-actions">
        <button type="button" className="wi-btn wi-btn-primary" onClick={onAgain}>
          <i className="fa-solid fa-user-plus"></i> Next walk-in guest
        </button>
        <a className="wi-btn wi-btn-ghost" href={primary.href}>{primary.label}</a>
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
  const [tried, setTried] = useState(false);
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
      .catch(() => { setLoadError('Could not load the rooms. Trying again…'); setLoaded(true); });
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
  const paidNow = payType === 'Full' ? total : payType === 'Partial' ? Number(amount) || 0 : 0;

  // A picked room that stopped being free (a teammate booked it) is dropped.
  useEffect(() => { if (roomId && !room) setRoomId(null); }, [roomId, room]);
  useEffect(() => { if (!categories.includes(category)) setCategory('All'); }, [categories, category]);

  const nameOk = !!fullName.trim();
  const contactOk = !!contactNo.trim();
  const emailOk = !email.trim() || /^\S+@\S+\.\S+$/.test(email.trim());
  const payOk = payType !== 'Partial' || (Number(amount) > 0 && Number(amount) <= total);

  const reset = () => {
    setFullName(''); setContactNo(''); setEmail(''); setIdNumber('');
    setNights(1); setCheckInTime(nowClock()); setRoomId(null);
    setPayType('Full'); setAmount(''); setMethod('Cash'); setNotes('');
    setError(''); setTried(false); setDone(null); load();
  };

  const submit = async (e) => {
    e.preventDefault();
    setTried(true);
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
          ['Room', done.room.name + (done.room.category ? ', ' + done.room.category : '')],
          ['Check-in', niceDate(checkIn) + ', ' + niceTime(checkInTime)],
          ['Check-out', niceDate(checkOut)],
          ['Length of stay', plural(nights, 'night')],
          ['Status', 'Arrived'],
          ['Paid now', peso(done.paid)],
          ['Still to pay', peso(balance)],
        ]}
        note="Room Management hands over the room and checks the guest in. Anything still to pay is settled at check-out from Guest Information."
        primary={{ href: CFG.guestInfoUrl, label: 'Open Guest Information' }}
        onAgain={reset}
      />
    );
  }

  return (
    <form className="wi-layout" onSubmit={submit} noValidate>
      <div className="wi-steps">
        <Section n={1} title="Who is the guest?" hint="Copy the name exactly as it appears on their ID." done={nameOk && contactOk && emailOk}>
          <div className="wi-fields">
            <Field id="wi-name" label="Full name" bad={tried && !nameOk ? 'Enter the guest’s full name.' : ''}>
              <input id="wi-name" className={'wi-input' + (tried && !nameOk ? ' is-bad' : '')} value={fullName}
                onChange={e => setFullName(e.target.value)} placeholder="e.g. Maria Santos" autoComplete="off" autoFocus />
            </Field>
            <div className="wi-two">
              <Field id="wi-contact" label="Contact number" bad={tried && !contactOk ? 'Enter a number we can reach them on.' : ''}>
                <input id="wi-contact" type="tel" inputMode="tel" className={'wi-input' + (tried && !contactOk ? ' is-bad' : '')}
                  value={contactNo} onChange={e => setContactNo(e.target.value)} placeholder="09XX XXX XXXX" autoComplete="off" />
              </Field>
              <Field id="wi-email" label="Email" optional bad={!emailOk ? 'That email address does not look right.' : ''}>
                <input id="wi-email" type="email" className={'wi-input' + (!emailOk ? ' is-bad' : '')}
                  value={email} onChange={e => setEmail(e.target.value)} placeholder="name@example.com" autoComplete="off" />
              </Field>
            </div>
            <Field id="wi-id" label="ID number" optional help="Any valid ID the guest shows you, such as a driver’s license or passport.">
              <input id="wi-id" className="wi-input" value={idNumber} onChange={e => setIdNumber(e.target.value)} autoComplete="off" />
            </Field>
          </div>
        </Section>

        <Section n={2} title="How long are they staying?" hint="Walk-in guests always check in today." done>
          <div className="wi-fields">
            <div className="wi-two">
              <Field id="wi-nights" label="Number of nights">
                <Stepper value={nights} min={1} max={30} onChange={setNights} unit="night" />
              </Field>
              <Field id="wi-time" label="Check-in time" help="Set to the time right now. Change it if needed.">
                <input id="wi-time" type="time" className="wi-input" value={checkInTime} onChange={e => setCheckInTime(e.target.value)} />
              </Field>
            </div>
            <div className="wi-dates">
              <i className="fa-regular fa-calendar"></i>
              <span>Check-in</span><b>{niceDate(checkIn)}</b>
              <i className="fa-solid fa-arrow-right" style={{ color: 'var(--fg-muted)', fontSize: '0.75rem' }}></i>
              <span>Check-out</span><b>{niceDate(checkOut)}</b>
            </div>
          </div>
        </Section>

        <Section n={3} title="Choose a room"
          hint={loaded ? (free.length === 1 ? '1 room is clean and free for this stay.' : free.length + ' rooms are clean and free for this stay.') : 'Finding rooms that are ready…'}
          done={!!room}>
          {categories.length > 2 && (
            <div style={{ marginBottom: '0.9rem' }}>
              <Chips label="Room type" value={category} onChange={setCategory}
                options={categories.map(c => [c, c === 'All' ? 'All types' : c])} />
            </div>
          )}
          {loadError && <p className="wi-alert" style={{ marginBottom: '0.8rem' }}>{loadError}</p>}
          {tried && !room && loaded && shown.length > 0 && <p className="wi-alert" style={{ marginBottom: '0.8rem' }}>Pick a room for the guest.</p>}
          {!loaded ? <CardSkeletons /> : shown.length === 0 ? (
            <div className="wi-empty">
              <i className="fa-solid fa-bed"></i>
              No room is clean and free for {nights === 1 ? 'tonight' : 'those ' + nights + ' nights'}.<br />
              {nights > 1 ? 'Try a shorter stay.' : 'Rooms appear here as soon as Housekeeping clears them.'}
            </div>
          ) : (
            <div className="wi-cards">
              {shown.map(r => {
                const on = r.dbId === roomId;
                return (
                  <button key={r.dbId} type="button" className={'wi-card' + (on ? ' is-on' : '')}
                    onClick={() => setRoomId(r.dbId)} aria-pressed={on}>
                    <div className="wi-card-photo">
                      {r.img ? <img src={r.img} alt="" loading="lazy" /> : <i className="fa-solid fa-bed"></i>}
                    </div>
                    <div className="wi-card-body">
                      <span className="wi-card-name">{r.name}</span>
                      <span className="wi-card-sub">{r.category || 'Room'}</span>
                      <span className="wi-card-price">{peso(r.price * BLOCKS_PER_NIGHT)} <small>per night</small></span>
                    </div>
                    {on && <span className="wi-card-tick"><i className="fa-solid fa-check"></i></span>}
                  </button>
                );
              })}
            </div>
          )}
        </Section>

        <Section n={4} title="Payment" hint="How much is the guest paying right now?" done={!!room && payOk}>
          <div className="wi-fields">
            <Chips label="Payment now" value={payType} onChange={setPayType} options={[
              ['Full', 'Pay in full', 'fa-check-double'],
              ['Partial', 'Pay a deposit', 'fa-coins'],
              ['None', 'Pay at check-out', 'fa-clock'],
            ]} />
            {payType !== 'None' && (
              <>
                <Field id="wi-amount" label={payType === 'Full' ? 'Amount to collect' : 'Deposit amount'}
                  bad={tried && payType === 'Partial' && !payOk ? (Number(amount) > total && room ? 'A deposit cannot be more than ' + peso(total) + '.' : 'Enter how much they are paying now.') : ''}
                  help={payType === 'Partial' && room ? 'The rest (' + peso(Math.max(0, total - (Number(amount) || 0))) + ') is paid at check-out.' : ''}>
                  {payType === 'Full'
                    ? <input id="wi-amount" className="wi-input" value={room ? peso(total) : 'Choose a room first'} readOnly />
                    : <input id="wi-amount" type="number" min="1" step="0.01" inputMode="decimal"
                        className={'wi-input' + (tried && !payOk ? ' is-bad' : '')}
                        value={amount} onChange={e => setAmount(e.target.value)} placeholder="0.00" />}
                </Field>
                <Field id="wi-method" label="Paid by">
                  <Chips label="Payment method" value={method} onChange={setMethod}
                    options={PAYMENT_METHODS.map(([m, icon]) => [m, m, icon])} />
                </Field>
              </>
            )}
            <Field id="wi-notes" label="Notes for the team" optional help="For example: asked for a late check-out, needs an extra pillow.">
              <input id="wi-notes" className="wi-input" value={notes} onChange={e => setNotes(e.target.value)} autoComplete="off" />
            </Field>
          </div>
        </Section>
      </div>

      <Summary
        title="Booking summary"
        rows={[
          ['Guest', fullName.trim()],
          ['Room', room ? room.name : ''],
          ['Stay', plural(nights, 'night') + ', until ' + niceDate(checkOut)],
          ['Paying now', room ? (payType === 'None' ? 'Nothing yet' : peso(paidNow) + ' by ' + method) : ''],
        ]}
        total={['Total for the stay', room ? peso(total) : peso(0)]}
        todo={[
          ['Guest name', nameOk],
          ['Contact number', contactOk],
          ['Room chosen', !!room],
          ['Payment set', !!room && payOk],
        ]}
        error={error}
        busy={busy}
        button={['Book room and mark arrived', 'fa-key']}
        note="The guest is marked as arrived straight away. Room Management then hands over the key."
      />
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
  const [tried, setTried] = useState(false);
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
      .catch(() => { setLoadError('Could not load the tables. Trying again…'); setLoaded(true); });
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

  const nameOk = !!guestName.trim();
  const contactOk = !!contactNo.trim();
  const whenOk = when === 'now' || (!!onDate && !!atTime && new Date(onDate + 'T' + atTime) >= new Date(Date.now() - 60000));

  const reset = () => {
    setGuestName(''); setContactNo(''); setParty(2); setWhen('now');
    setOnDate(today()); setAtTime(nowClock()); setTableId(null);
    setError(''); setTried(false); setDone(null); load();
  };

  const submit = async (e) => {
    e.preventDefault();
    setTried(true);
    if (!guestName.trim()) return setError('Enter the guest’s name.');
    if (!contactNo.trim()) return setError('Enter a contact number for the guest.');
    if (!table) return setError('Pick a table that fits the group.');
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
      if (window.toast) window.toast(when === 'now' ? guestName.trim() + ' is dining in at ' + table.name : 'Reserved ' + table.name + ' for ' + guestName.trim());
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
        title={when === 'now' ? guestName.trim() + ' is dining in' : 'Table held for ' + guestName.trim()}
        rows={[
          ['Table', done.table.name],
          ['Seats', String(done.table.capacity)],
          ['Guests', String(party)],
          ['When', when === 'now' ? 'Now' : niceDate(onDate) + ', ' + niceTime(atTime)],
          ['Status', when === 'now' ? 'Occupied' : 'Reserved'],
        ]}
        note={when === 'now'
          ? 'The restaurant takes their order from here and settles the bill.'
          : 'When they come back, press Customer Arrived on Dine-in Tables to start their meal.'}
        primary={{ href: CFG.dineInUrl, label: 'Open Dine-in Tables' }}
        onAgain={reset}
      />
    );
  }

  if (loaded && !canAssign) {
    return (
      <div className="wi-empty">
        <i className="fa-solid fa-lock"></i>
        Only Front Desk staff can seat or reserve a table for a guest.
      </div>
    );
  }

  return (
    <form className="wi-layout" onSubmit={submit} noValidate>
      <div className="wi-steps">
        <Section n={1} title="Who is dining?" hint="The name the table is held under." done={nameOk && contactOk}>
          <div className="wi-two">
            <Field id="wt-name" label="Guest name" bad={tried && !nameOk ? 'Enter the guest’s name.' : ''}>
              <input id="wt-name" className={'wi-input' + (tried && !nameOk ? ' is-bad' : '')} value={guestName}
                onChange={e => setGuestName(e.target.value)} placeholder="e.g. Juan Dela Cruz" autoComplete="off" autoFocus />
            </Field>
            <Field id="wt-contact" label="Contact number" bad={tried && !contactOk ? 'Enter a number we can reach them on.' : ''}>
              <input id="wt-contact" type="tel" inputMode="tel" className={'wi-input' + (tried && !contactOk ? ' is-bad' : '')}
                value={contactNo} onChange={e => setContactNo(e.target.value)} placeholder="09XX XXX XXXX" autoComplete="off" />
            </Field>
          </div>
        </Section>

        <Section n={2} title="How many people, and when?" done={whenOk}>
          <div className="wi-fields">
            <Field id="wt-party" label="Number of guests">
              <Stepper value={party} min={1} max={Math.max(1, biggest)} onChange={setParty} unit="guest" />
            </Field>
            <Chips label="When" value={when} onChange={setWhen} options={[
              ['now', 'Seat them now', 'fa-utensils'],
              ['later', 'Reserve for later', 'fa-calendar-day'],
            ]} />
            {when === 'later' && (
              <div className="wi-two">
                <Field id="wt-date" label="Date">
                  <input id="wt-date" type="date" className="wi-input" value={onDate} min={today()} onChange={e => setOnDate(e.target.value)} />
                </Field>
                <Field id="wt-time" label="Time" bad={tried && !whenOk ? 'That time has already passed.' : ''}>
                  <input id="wt-time" type="time" className={'wi-input' + (tried && !whenOk ? ' is-bad' : '')} value={atTime} onChange={e => setAtTime(e.target.value)} />
                </Field>
              </div>
            )}
          </div>
        </Section>

        <Section n={3} title="Choose a table"
          hint={loaded ? (fits.length === 1 ? '1 free table fits ' + plural(party, 'guest') + '. Smallest first.' : fits.length + ' free tables fit ' + plural(party, 'guest') + '. Smallest first.') : 'Finding free tables…'}
          done={!!table}>
          {loadError && <p className="wi-alert" style={{ marginBottom: '0.8rem' }}>{loadError}</p>}
          {tried && !table && loaded && fits.length > 0 && <p className="wi-alert" style={{ marginBottom: '0.8rem' }}>Pick a table that fits the group.</p>}
          {!loaded ? <CardSkeletons /> : tables.length === 0 ? (
            <div className="wi-empty">
              <i className="fa-solid fa-chair"></i>
              Restaurant Management has not added any tables yet.
            </div>
          ) : fits.length === 0 ? (
            <div className="wi-empty">
              <i className="fa-solid fa-chair"></i>
              No free table seats {plural(party, 'guest')} right now.<br />Try a smaller group, or reserve for later.
            </div>
          ) : (
            <div className="wi-cards">
              {fits.map(t => {
                const on = t.id === tableId;
                return (
                  <button key={t.id} type="button" className={'wi-card' + (on ? ' is-on' : '')}
                    onClick={() => setTableId(t.id)} aria-pressed={on}>
                    <div className="wi-card-photo wi-table-icon"><i className="fa-solid fa-chair"></i></div>
                    <div className="wi-card-body" style={{ textAlign: 'center' }}>
                      <span className="wi-card-name">{t.name}</span>
                      <span className="wi-card-sub">Seats {t.capacity}</span>
                    </div>
                    {on && <span className="wi-card-tick"><i className="fa-solid fa-check"></i></span>}
                  </button>
                );
              })}
            </div>
          )}
        </Section>
      </div>

      <Summary
        title={when === 'now' ? 'Seating summary' : 'Reservation summary'}
        rows={[
          ['Name', guestName.trim()],
          ['Party size', plural(party, 'guest')],
          ['When', when === 'now' ? 'Now' : (onDate && atTime ? niceDate(onDate) + ', ' + niceTime(atTime) : '')],
          ['Table', table ? table.name + ' (seats ' + table.capacity + ')' : ''],
        ]}
        todo={[
          ['Guest name', nameOk],
          ['Contact number', contactOk],
          ['Table chosen', !!table],
        ]}
        error={error}
        busy={busy}
        button={when === 'now' ? ['Seat guests now', 'fa-utensils'] : ['Reserve this table', 'fa-calendar-check']}
        note={when === 'now' ? 'The table shows as Occupied and the restaurant takes it from there.' : 'The table is held for them until they arrive.'}
      />
    </form>
  );
}

function App() {
  const [tab, setTab] = useState(CFG.startTab);
  return (
    <div className="wi" data-hms-no-edit="1">
      <header className="wi-head">
        <div>
          <h1 className="font-display">Walk-in Guests</h1>
          <p>A guest has arrived without a booking. Choose what they need, then follow the steps.</p>
        </div>
        <a href={CFG.backUrl} className="wi-btn wi-btn-ghost">
          <i className="fa-solid fa-arrow-left"></i> Back to tasks
        </a>
      </header>

      <div className="wi-modes" role="tablist" aria-label="What does the guest need?">
        <button type="button" role="tab" aria-selected={tab === 'room'} className={'wi-mode' + (tab === 'room' ? ' is-on' : '')} onClick={() => setTab('room')}>
          <span className="wi-mode-icon"><i className="fa-solid fa-bed"></i></span>
          <span className="wi-mode-text"><b>A room to stay in</b><small>Book a room from today and mark them arrived.</small></span>
        </button>
        <button type="button" role="tab" aria-selected={tab === 'table'} className={'wi-mode' + (tab === 'table' ? ' is-on' : '')} onClick={() => setTab('table')}>
          <span className="wi-mode-icon"><i className="fa-solid fa-utensils"></i></span>
          <span className="wi-mode-text"><b>A table to eat at</b><small>Seat them in the restaurant now, or hold a table.</small></span>
        </button>
      </div>

      {tab === 'room' ? <RoomWalkIn key="room" /> : <TableWalkIn key="table" />}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
