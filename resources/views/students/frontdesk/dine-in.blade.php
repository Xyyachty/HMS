@extends('students.builder.ops-shell')

@php $backRoute = 'students.frontdesk'; @endphp

@section('page-title', 'Dine-in Tables')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }
  .dn-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; display: inline-block;
  }
  .dn-available { background: rgba(34,197,94,0.18); color: var(--success, #4ade80); border-color: rgba(34,197,94,0.35); }
  .dn-occupied  { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
  /* Held, nobody at it yet — amber reads as "pending" against Occupied's blue. */
  .dn-reserved  { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35); }
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
    display: inline-flex; align-items: center; gap: 0.45rem;
    background: var(--accent); color: var(--bg); border: 1px solid var(--accent);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600;
    font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.55rem 1.1rem; border-radius: 6px; cursor: pointer;
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
  /* Cards sit on an even grid and stretch to the tallest in their row, so a
     reserved card's extra detail cannot leave its neighbours ragged. */
  .dn-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 1rem;
    align-items: stretch;
  }
  .dn-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 14px; padding: 1.1rem 1.2rem 1.2rem;
    display: flex; flex-direction: column; gap: 0.85rem;
    transition: border-color 0.15s;
  }
  .dn-card:hover { border-color: rgba(201,168,76,0.4); }
  .dn-card-head {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 0.6rem;
  }
  .dn-card-name { margin: 0; color: var(--fg); font-weight: 700; font-size: 1.05rem; line-height: 1.2; }
  .dn-card-seats { margin: 0.2rem 0 0; color: var(--fg-muted); font-size: 0.74rem; }
  /* Pins the action to the bottom of the card whatever the body above it holds. */
  .dn-card-foot { margin-top: auto; }

  /* Reserved detail. A label/value grid rather than a stack of sentences: the
     desk scans down the values, and the labels stay out of the way. */
  .dn-meta {
    display: grid; grid-template-columns: auto 1fr; gap: 0.3rem 0.7rem;
    margin: 0; padding: 0.8rem 0 0; border-top: 1px solid var(--border);
  }
  .dn-meta dt {
    font-size: 0.58rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;
    color: var(--fg-muted); white-space: nowrap; padding-top: 0.15rem;
  }
  .dn-meta dd { margin: 0; color: var(--fg); font-size: 0.8rem; overflow-wrap: anywhere; }
  .dn-meta dd.is-when { color: var(--accent-light); font-weight: 600; }
  .dn-card-note {
    margin: 0; color: var(--fg-muted); font-size: 0.7rem;
    padding-top: 0.7rem; border-top: 1px solid var(--border);
  }

  /* Counts above the grid, so "how full is the floor" is one glance. */
  .dn-stats { display: flex; flex-wrap: wrap; gap: 0.5rem; }
  .dn-stat {
    display: inline-flex; align-items: baseline; gap: 0.4rem;
    border: 1px solid var(--border); border-radius: 100px;
    padding: 0.3rem 0.85rem; background: var(--card);
  }
  .dn-stat b { color: var(--fg); font-size: 0.9rem; font-variant-numeric: tabular-nums; }
  .dn-stat span { color: var(--fg-muted); font-size: 0.7rem; letter-spacing: 0.06em; text-transform: uppercase; }

  .dn-filters { display: flex; flex-wrap: wrap; gap: 0.4rem; }
  .dn-filter {
    font-family: var(--font-body, 'Outfit', sans-serif);
    font-size: 0.68rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.42rem 0.9rem; border-radius: 100px; border: 1.5px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer; transition: all 0.15s;
  }
  .dn-filter:hover { border-color: var(--accent); color: var(--accent); }
  .dn-filter.is-active { background: var(--accent); border-color: var(--accent); color: var(--bg); }

  .dn-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; flex-wrap: wrap; margin-bottom: 1.2rem;
  }
  .dn-search { position: relative; flex: 1 1 240px; max-width: 340px; }
  .dn-search i {
    position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%);
    color: var(--fg-muted); font-size: 0.75rem; pointer-events: none;
  }
  .dn-search .booking-input { padding-left: 2.2rem; }

  .dn-empty {
    border: 1px dashed var(--border); border-radius: 14px;
    padding: 2.75rem 1.5rem; text-align: center;
  }

  /* The reservation form. It has five fields and two of them are a date and a
     time picker, which is more than a card in a 270px grid column can hold —
     so it opens over the page instead of expanding in place. */
  .dn-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.65);
    display: flex; align-items: flex-start; justify-content: center;
    padding: 2rem 1.5rem; z-index: 300; overflow-y: auto;
  }
  .dn-modal {
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    width: 100%; max-width: 460px; margin: auto;
  }
  .dn-modal-head {
    padding: 1.3rem 1.5rem 1rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
  }
  .dn-modal-body { padding: 1.25rem 1.5rem 1.5rem; }
  .dn-modal-close {
    width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--border);
    background: rgba(255,255,255,0.03); color: var(--fg); cursor: pointer; flex-shrink: 0;
  }
  /* Set here rather than inline so the Template 2 override below can win — the
     native pickers render their own controls from this. */
  input[type="date"].booking-input,
  input[type="time"].booking-input { color-scheme: dark; }
  .dn-form { display: grid; gap: 0.9rem; }
  .dn-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem; }
  .dn-field-label {
    display: block; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--fg-muted); margin-bottom: 0.35rem;
  }
  .dn-step {
    width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--border);
    background: rgba(255,255,255,0.03); color: var(--fg); cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center; font-size: 1rem;
  }
  .dn-step:disabled { opacity: 0.35; cursor: not-allowed; }
  .dn-party {
    display: flex; align-items: center; gap: 0.6rem;
    border: 1px solid var(--border); border-radius: 6px; padding: 0.3rem 0.45rem;
    background: rgba(255,255,255,0.03); width: fit-content;
  }
  .dn-party output { color: var(--fg); min-width: 28px; text-align: center; font-size: 0.95rem; font-variant-numeric: tabular-nums; }
  @media (max-width: 420px) {
    .dn-form-row { grid-template-columns: 1fr; }
  }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Additive only — nothing above this block is touched, so a Template 1
     team (or one that hasn't chosen a template yet) renders unchanged. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d;
  }
  :root[data-ops-theme="2"] .dn-available { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
  :root[data-ops-theme="2"] .dn-occupied { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
  :root[data-ops-theme="2"] .dn-reserved { background: #fef3c7; color: #b45309; border-color: #fde68a; }
  :root[data-ops-theme="2"] .booking-input { background: rgba(27,67,50,0.03); }
  :root[data-ops-theme="2"] .dn-step,
  :root[data-ops-theme="2"] .dn-party,
  :root[data-ops-theme="2"] .dn-modal-close { background: rgba(27,67,50,0.03); }
  :root[data-ops-theme="2"] .dn-card:hover { border-color: rgba(27,67,50,0.35); }
  :root[data-ops-theme="2"] input[type="date"].booking-input,
  :root[data-ops-theme="2"] input[type="time"].booking-input { color-scheme: light; }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_DINE_IN = {
    backUrl: @json(route('students.frontdesk')),
    tablesUrl: @json(route('students.hotel.tables.index')),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo, useRef } = React;

const CFG = window.HMS_DINE_IN;

function csrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function pad2(n) { return String(n).padStart(2, '0'); }

function defaultReserveDate() {
  const d = new Date();
  return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
}

/* The next whole hour, so the prefilled time is never one already gone. */
function defaultReserveTime() {
  const d = new Date();
  d.setMinutes(0, 0, 0);
  d.setHours(d.getHours() + 1);
  return pad2(d.getHours()) + ':' + pad2(d.getMinutes());
}

function formatWhen(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

/* Clock only. Used for "taken at", where the date is almost always today and
   spelling it out just crowds the card. */
function formatClock(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

/*
 * Holding a table for a customer: who they are, how to reach them, when they are
 * due and how many are coming.
 *
 * A modal rather than a panel inside the table card — five fields, two of them
 * date and time pickers, do not fit in a 270px grid column without the pickers
 * being clipped to uselessness.
 */
function ReserveModal({ table, onReserve, onClose, busy }) {
  const [guestName, setGuestName] = useState('');
  const [contactNo, setContactNo] = useState('');
  // Today at the next whole hour: most reservations the desk takes are for later
  // the same day, so that is two fewer fields to think about than blank ones.
  const [onDate, setOnDate] = useState(defaultReserveDate);
  const [atTime, setAtTime] = useState(defaultReserveTime);
  const [partySize, setPartySize] = useState(Math.min(2, table.capacity));
  const [error, setError] = useState('');

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape' && !busy) onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose, busy]);

  const submit = (e) => {
    e.preventDefault();
    if (!guestName.trim()) { setError('Enter the customer’s name.'); return; }
    if (!contactNo.trim()) { setError('Enter a contact number for the customer.'); return; }
    if (!onDate || !atTime) { setError('Pick the date and time they are booked for.'); return; }
    if (partySize < 1 || partySize > table.capacity) {
      setError(`Party size must be between 1 and ${table.capacity}.`);
      return;
    }
    setError('');
    onReserve(table.id, {
      guest_name: guestName.trim(),
      contact_no: contactNo.trim(),
      // Local wall-clock, no timezone suffix — the server parses it in app time.
      reserved_for: `${onDate} ${atTime}:00`,
      party_size: partySize,
    });
  };

  return (
    <div className="dn-modal-overlay" onClick={() => { if (!busy) onClose(); }} role="dialog" aria-modal="true">
      <div className="dn-modal" onClick={e => e.stopPropagation()}>
        <div className="dn-modal-head">
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.2em', textTransform: 'uppercase', margin: '0 0 0.35rem' }}>Front Desk</p>
            <h2 className="font-display" style={{ fontSize: '1.4rem', margin: 0, color: 'var(--fg)' }}>Reserve {table.name}</h2>
            <p style={{ margin: '0.3rem 0 0', color: 'var(--fg-muted)', fontSize: '0.76rem' }}>
              Seats {table.capacity} · holds the table only, the restaurant takes the order
            </p>
          </div>
          <button type="button" className="dn-modal-close" onClick={onClose} disabled={busy} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div className="dn-modal-body">
          <form onSubmit={submit} className="dn-form" noValidate>
            <div>
              <label className="dn-field-label">Customer name</label>
              <input type="text" className="booking-input" placeholder="Who's dining?"
                value={guestName} onChange={e => setGuestName(e.target.value)} autoFocus />
            </div>

            <div>
              <label className="dn-field-label">Contact number</label>
              <input type="tel" className="booking-input" placeholder="09XX XXX XXXX"
                value={contactNo} onChange={e => setContactNo(e.target.value)} />
            </div>

            <div className="dn-form-row">
              <div>
                <label className="dn-field-label">Date</label>
                <input type="date" className="booking-input" value={onDate}
                  min={defaultReserveDate()} onChange={e => setOnDate(e.target.value)} />
              </div>
              <div>
                <label className="dn-field-label">Time</label>
                <input type="time" className="booking-input" value={atTime}
                  onChange={e => setAtTime(e.target.value)} />
              </div>
            </div>

            <div>
              <label className="dn-field-label">Party size · seats {table.capacity}</label>
              {/* Stepper rather than a number input, so it cannot be typed past
                  the table's own capacity. */}
              <div className="dn-party">
                <button type="button" className="dn-step" aria-label="Fewer guests"
                  disabled={partySize <= 1}
                  onClick={() => setPartySize(n => Math.max(1, n - 1))}>−</button>
                <output>{partySize}</output>
                <button type="button" className="dn-step" aria-label="More guests"
                  disabled={partySize >= table.capacity}
                  onClick={() => setPartySize(n => Math.min(table.capacity, n + 1))}>+</button>
              </div>
            </div>

            {error && <p style={{ margin: 0, color: 'var(--danger, #fb7185)', fontSize: '0.78rem' }}>{error}</p>}

            <div style={{ display: 'flex', gap: '0.6rem', marginTop: '0.2rem' }}>
              <button type="submit" className="btn-solid" disabled={busy} style={{ flex: 1, justifyContent: 'center' }}>
                <i className="fa-solid fa-chair" style={{ fontSize: '0.7rem' }}></i>
                {busy ? 'Reserving…' : 'Reserve Table'}
              </button>
              <button type="button" className="btn-outline" onClick={onClose} disabled={busy} style={{ padding: '0.55rem 1.1rem' }}>
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}

function TableCard({ table, canReserve, onOpenReserve, onSeat, busy }) {
  const available = table.status === 'Available';
  const reserved = table.status === 'Reserved';

  return (
    <div className="dn-card">
      <div className="dn-card-head">
        <div style={{ minWidth: 0 }}>
          <p className="dn-card-name">{table.name}</p>
          <p className="dn-card-seats">Seats {table.capacity}</p>
        </div>
        {/* The real status, not a relabel: Reserved means the desk is holding it,
            Occupied means the customer is actually sitting there. */}
        <span className={`dn-badge dn-${table.status.toLowerCase()}`}>{table.status}</span>
      </div>

      {!available && (
        <>
          <dl className="dn-meta">
            <dt>Guest</dt><dd>{table.guestName || 'Guest'}</dd>
            {table.contactNo ? (<><dt>Contact</dt><dd>{table.contactNo}</dd></>) : null}
            {/* When they are due, which is what the floor needs. Falls back to when
                the desk wrote it down, for a table reserved before that was kept. */}
            <dt>Booked</dt>
            <dd className="is-when">{formatWhen(table.reservedFor || table.assignedAt)}</dd>
            <dt>Party</dt><dd>{table.partySize || '—'}</dd>
          </dl>
          <p className="dn-card-note">
            {reserved ? 'Reserved' : 'Seated'} {formatClock(table.assignedAt)}{table.assignedBy ? ` by ${table.assignedBy}` : ''}
          </p>
        </>
      )}

      {reserved && canReserve && (
        <div className="dn-card-foot">
          {/* The desk greets the customer as often as the restaurant does, so it can
              seat them too — and nothing can be ordered until someone has. */}
          <button type="button" className="btn-solid" disabled={busy} onClick={() => onSeat(table)}
            style={{ width: '100%', justifyContent: 'center' }}>
            <i className="fa-solid fa-user-check" style={{ fontSize: '0.7rem' }}></i> Customer Arrived
          </button>
        </div>
      )}

      {available && canReserve && (
        <div className="dn-card-foot">
          <button type="button" className="btn-outline" onClick={() => onOpenReserve(table)}
            style={{ width: '100%', justifyContent: 'center', fontSize: '0.7rem', padding: '0.55rem' }}>
            <i className="fa-solid fa-chair" style={{ fontSize: '0.7rem' }}></i> Reserve Table
          </button>
        </div>
      )}
    </div>
  );
}

function App() {
  const [tables, setTables] = useState([]);
  const [canReserve, setCanReserve] = useState(false);
  const [search, setSearch] = useState('');
  const [filter, setFilter] = useState('All');
  const [busy, setBusy] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [reservingId, setReservingId] = useState(null);
  const pendingWrites = useRef(0);

  const load = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(CFG.tablesUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.tables)) setTables(data.tables);
        setCanReserve(!!data.can_assign);
        setLoaded(true);
      })
      .catch(() => setLoaded(true));
  }, []);

  useEffect(() => {
    load();
    const id = setInterval(load, 8000);
    window.addEventListener('focus', load);
    return () => { clearInterval(id); window.removeEventListener('focus', load); };
  }, [load]);

  const patchTable = (id, payload, fallbackError, onDone) => {
    setBusy(true);
    pendingWrites.current += 1;
    fetch(`${CFG.tablesUrl}/${id}`, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.message || fallbackError);
        return data;
      })
      .then(data => {
        if (data.table) setTables(prev => prev.map(t => (t.id === data.table.id ? data.table : t)));
        if (onDone) onDone(data.table);
      })
      .catch(e => { if (window.toast) window.toast(e.message); })
      .finally(() => {
        setBusy(false);
        pendingWrites.current = Math.max(0, pendingWrites.current - 1);
      });
  };

  const reserveTable = (id, payload) => patchTable(id, payload, 'Could not reserve this table.', (table) => {
    if (table && window.toast) window.toast(`Reserved ${table.name} for ${table.guestName}`);
    setReservingId(null);
  });

  const seatTable = (table) => patchTable(table.id, { arrive: true }, 'Could not seat this customer.', (updated) => {
    if (updated && window.toast) window.toast(`${updated.guestName || 'The customer'} is seated at ${updated.name}.`);
  });

  const availableCount = tables.filter(t => t.status === 'Available').length;
  const reservedCount = tables.filter(t => t.status === 'Reserved').length;
  const seatedCount = tables.filter(t => t.status === 'Occupied').length;

  const visible = useMemo(() => {
    const q = search.trim().toLowerCase();
    return tables.filter(t => {
      if (filter !== 'All' && t.status !== filter) return false;
      if (!q) return true;
      return [t.name, t.guestName, t.contactNo].some(field => String(field || '').toLowerCase().includes(q));
    });
  }, [tables, search, filter]);

  // Re-read from state so the modal follows a refresh rather than freezing at open time.
  const reservingTable = reservingId ? (tables.find(t => t.id === reservingId) || null) : null;

  const filters = [
    { key: 'All', count: tables.length },
    { key: 'Available', count: availableCount },
    { key: 'Reserved', count: reservedCount },
    { key: 'Occupied', count: seatedCount },
  ];

  return (
    <div data-hms-no-edit="1" style={{ padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.35rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Front Desk</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Dine-in Tables</h1>
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem', maxWidth: 460 }}>
            Reserving holds the table only — the restaurant takes the order and settles
            the bill once the customer arrives.
          </p>
        </div>
        <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </a>
      </div>

      {tables.length > 0 && (
        <>
          <div className="dn-stats" style={{ marginBottom: '1.1rem' }}>
            <span className="dn-stat"><b>{tables.length}</b><span>Tables</span></span>
            <span className="dn-stat"><b>{availableCount}</b><span>Available</span></span>
            <span className="dn-stat"><b>{reservedCount}</b><span>Reserved</span></span>
            <span className="dn-stat"><b>{seatedCount}</b><span>Seated</span></span>
          </div>

          <div className="dn-toolbar">
            <div className="dn-filters">
              {filters.map(f => (
                <button key={f.key} type="button"
                  className={`dn-filter${filter === f.key ? ' is-active' : ''}`}
                  onClick={() => setFilter(f.key)}>
                  {f.key} ({f.count})
                </button>
              ))}
            </div>
            <div className="dn-search">
              <i className="fa-solid fa-magnifying-glass"></i>
              <input type="text" className="booking-input" placeholder="Search table, customer or contact…"
                value={search} onChange={e => setSearch(e.target.value)} />
            </div>
          </div>
        </>
      )}

      {!loaded ? (
        <div className="dn-empty" style={{ color: 'var(--fg-muted)', fontSize: '0.85rem' }}>Loading tables…</div>
      ) : tables.length === 0 ? (
        <div className="dn-empty">
          <i className="fa-solid fa-utensils" style={{ fontSize: '1.8rem', color: 'var(--fg-muted)', opacity: 0.35, display: 'block', marginBottom: '0.7rem' }}></i>
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.88rem' }}>
            Restaurant Management hasn't added any tables yet.
          </p>
        </div>
      ) : visible.length === 0 ? (
        <div className="dn-empty">
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.88rem' }}>
            {search.trim() ? `No tables match "${search}"` : `No ${filter.toLowerCase()} tables right now.`}
          </p>
        </div>
      ) : (
        <div className="dn-grid">
          {visible.map(table => (
            <TableCard key={table.id} table={table} canReserve={canReserve} busy={busy}
              onOpenReserve={t => setReservingId(t.id)} onSeat={seatTable} />
          ))}
        </div>
      )}

      {reservingTable && (
        <ReserveModal
          table={reservingTable}
          busy={busy}
          onReserve={reserveTable}
          onClose={() => setReservingId(null)}
        />
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
