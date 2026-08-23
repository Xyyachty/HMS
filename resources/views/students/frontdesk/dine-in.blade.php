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
  .dn-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 0.9rem;
  }
  .dn-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 1.1rem 1.2rem;
  }
  .dn-field-label {
    display: block; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--fg-muted); margin-bottom: 0.35rem;
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
  :root[data-ops-theme="2"] .booking-input { background: rgba(27,67,50,0.03); }
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

function formatWhen(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString([], { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function AssignForm({ table, onAssign, onCancel, busy }) {
  const [guestName, setGuestName] = useState('');
  const [partySize, setPartySize] = useState(Math.min(2, table.capacity));
  const [error, setError] = useState('');
  const partyStepBtn = {
    width: 32, height: 32, borderRadius: 8, border: '1px solid var(--border)',
    background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: 'pointer',
    display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: '1rem',
  };

  const submit = (e) => {
    e.preventDefault();
    if (!guestName.trim()) { setError('Enter the guest’s name.'); return; }
    if (partySize < 1 || partySize > table.capacity) {
      setError(`Party size must be between 1 and ${table.capacity}.`);
      return;
    }
    setError('');
    onAssign(table.id, { guest_name: guestName.trim(), party_size: partySize });
  };

  return (
    <form onSubmit={submit} style={{ marginTop: '0.9rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)' }}>
      <label className="dn-field-label">Guest name</label>
      <input
        type="text"
        className="booking-input"
        placeholder="Who's dining?"
        value={guestName}
        onChange={e => setGuestName(e.target.value)}
        style={{ marginBottom: '0.6rem' }}
      />
      <label className="dn-field-label">Party size (seats {table.capacity})</label>
      {/* Stepper rather than a number input, matching the menu's quantity control —
          and it cannot be typed past the table's own capacity. */}
      <div style={{ display: 'inline-flex', alignItems: 'center', gap: '0.6rem', marginBottom: '0.6rem' }}>
        <button type="button" style={partyStepBtn} aria-label="Fewer guests"
          disabled={partySize <= 1}
          onClick={() => setPartySize(n => Math.max(1, n - 1))}>−</button>
        <span style={{ color: 'var(--fg)', minWidth: 24, textAlign: 'center', fontSize: '0.95rem', fontVariantNumeric: 'tabular-nums' }}>{partySize}</span>
        <button type="button" style={partyStepBtn} aria-label="More guests"
          disabled={partySize >= table.capacity}
          onClick={() => setPartySize(n => Math.min(table.capacity, n + 1))}>+</button>
      </div>
      {error && <p style={{ margin: '0 0 0.6rem', color: 'var(--danger, #fb7185)', fontSize: '0.78rem' }}>{error}</p>}
      <div style={{ display: 'flex', gap: '0.5rem' }}>
        <button type="submit" className="btn-solid" disabled={busy} style={{ flex: 1, justifyContent: 'center' }}>
          {busy ? 'Seating…' : 'Assign Table'}
        </button>
        <button type="button" className="btn-outline" onClick={onCancel} style={{ padding: '0.55rem 1rem' }}>
          Cancel
        </button>
      </div>
    </form>
  );
}

function TableCard({ table, canAssign, onAssign, busy }) {
  const [assigning, setAssigning] = useState(false);
  const available = table.status === 'Available';

  return (
    <div className="dn-card">
      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '0.75rem' }}>
        <div>
          <p style={{ margin: 0, color: 'var(--fg)', fontWeight: 700, fontSize: '1rem' }}>{table.name}</p>
          <p style={{ margin: '0.25rem 0 0', color: 'var(--fg-muted)', fontSize: '0.75rem' }}>Seats {table.capacity}</p>
        </div>
        <span className={`dn-badge dn-${table.status.toLowerCase()}`}>{table.status}</span>
      </div>

      {!available && (
        <div style={{ marginTop: '0.85rem', fontSize: '0.8rem', color: 'var(--fg)' }}>
          <p style={{ margin: 0 }}>{table.guestName || 'Guest'} · party of {table.partySize || '—'}</p>
          <p style={{ margin: '0.25rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem' }}>
            Seated {formatWhen(table.assignedAt)}{table.assignedBy ? ` by ${table.assignedBy}` : ''}
          </p>
        </div>
      )}

      {available && canAssign && !assigning && (
        <button
          type="button"
          className="btn-outline"
          onClick={() => setAssigning(true)}
          style={{ marginTop: '0.9rem', width: '100%', justifyContent: 'center', fontSize: '0.7rem', padding: '0.5rem' }}
        >
          <i className="fa-solid fa-chair" style={{ fontSize: '0.7rem' }}></i> Assign Table
        </button>
      )}

      {available && canAssign && assigning && (
        <AssignForm
          table={table}
          busy={busy}
          onCancel={() => setAssigning(false)}
          onAssign={(id, payload) => onAssign(id, payload, () => setAssigning(false))}
        />
      )}
    </div>
  );
}

function App() {
  const [tables, setTables] = useState([]);
  const [canAssign, setCanAssign] = useState(false);
  const [search, setSearch] = useState('');
  const [busy, setBusy] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const pendingWrites = useRef(0);

  const load = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(CFG.tablesUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.tables)) setTables(data.tables);
        setCanAssign(!!data.can_assign);
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

  const assignTable = (id, payload, done) => {
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
        if (!r.ok) throw new Error(data.message || 'Could not assign this table.');
        return data;
      })
      .then(data => {
        if (data.table) {
          setTables(prev => prev.map(t => (t.id === data.table.id ? data.table : t)));
          if (window.toast) window.toast(`Seated ${data.table.guestName} at ${data.table.name}`);
        }
        if (done) done();
      })
      .catch(e => { if (window.toast) window.toast(e.message); })
      .finally(() => {
        setBusy(false);
        pendingWrites.current = Math.max(0, pendingWrites.current - 1);
      });
  };

  const visible = useMemo(() => {
    const q = search.trim().toLowerCase();
    if (!q) return tables;
    return tables.filter(t => [t.name, t.guestName].some(field => String(field || '').toLowerCase().includes(q)));
  }, [tables, search]);

  const availableCount = tables.filter(t => t.status === 'Available').length;

  return (
    <div data-hms-no-edit="1" style={{ maxWidth: 1100, margin: '0 auto', padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Front Desk</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Dine-in Tables</h1>
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem' }}>
            {tables.length === 0 ? 'No tables set up yet.' : `${availableCount} of ${tables.length} available`}
          </p>
        </div>
        <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </a>
      </div>

      {tables.length > 0 && (
        <div style={{ position: 'relative', marginBottom: '1.1rem' }}>
          <i className="fa-solid fa-magnifying-glass" style={{ position: 'absolute', left: '0.75rem', top: '50%', transform: 'translateY(-50%)', color: 'var(--fg-muted)', fontSize: '0.75rem', pointerEvents: 'none' }}></i>
          <input
            type="text"
            className="booking-input"
            placeholder="Search by table or guest…"
            value={search}
            onChange={e => setSearch(e.target.value)}
            style={{ paddingLeft: '2.1rem' }}
          />
        </div>
      )}

      {!loaded ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.85rem' }}>
          Loading tables…
        </div>
      ) : tables.length === 0 ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem', textAlign: 'center' }}>
          <i className="fa-solid fa-utensils" style={{ fontSize: '1.8rem', color: 'var(--fg-muted)', opacity: 0.35, display: 'block', marginBottom: '0.7rem' }}></i>
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.88rem' }}>
            Restaurant Management hasn't added any tables yet.
          </p>
        </div>
      ) : visible.length === 0 ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center' }}>
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.88rem' }}>No tables match "{search}"</p>
        </div>
      ) : (
        <div className="dn-grid">
          {visible.map(table => (
            <TableCard key={table.id} table={table} canAssign={canAssign} onAssign={assignTable} busy={busy} />
          ))}
        </div>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
