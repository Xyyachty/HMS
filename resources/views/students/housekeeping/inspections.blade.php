@extends('students.builder.ops-shell')

@php
    $backRoute = 'students.housekeeping';
@endphp

@section('page-title', 'Room Inspections')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: 'Outfit', sans-serif; }
  .font-display { font-family: 'Playfair Display', serif; }
  .hk-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; display: inline-block;
  }
  .hk-pending    { background: rgba(148,163,184,0.15); color: #cbd5e1; border-color: rgba(148,163,184,0.3); }
  .hk-inspecting { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35); }
  .hk-repair     { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .hk-reinspect  { background: rgba(56,189,248,0.18); color: #38bdf8; border-color: rgba(56,189,248,0.35); }
  .hk-completed  { background: rgba(34,197,94,0.18);  color: #4ade80; border-color: rgba(34,197,94,0.35); }
  .hk-room-available   { background: rgba(34,197,94,0.15);  color: #4ade80; border-color: rgba(34,197,94,0.3); }
  .hk-room-cleaning    { background: rgba(245,158,11,0.15); color: #fbbf24; border-color: rgba(245,158,11,0.3); }
  .hk-room-maintenance { background: rgba(168,85,247,0.15); color: #c084fc; border-color: rgba(168,85,247,0.3); }
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
  .btn-outline:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
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
  .hk-input, .hk-select {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: 'Outfit', sans-serif; font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .hk-input:focus, .hk-select:focus { border-color: var(--accent); }
  .hk-input::placeholder { color: var(--fg-muted); opacity: 0.5; }
  .hk-select option { background: #181714; color: var(--fg); }
  .hk-field-label {
    display: block; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--fg-muted); margin-bottom: 0.4rem;
  }
  .hk-tab {
    padding: 0.4rem 0.9rem; border-radius: 999px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer;
    font-family: 'Outfit', sans-serif; font-size: 0.72rem; font-weight: 600;
    letter-spacing: 0.06em; transition: all 0.15s;
  }
  .hk-tab:hover { color: var(--fg); }
  .hk-tab.is-active { border-color: var(--accent); background: var(--accent); color: var(--bg); }
  .hk-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 1.1rem 1.2rem;
  }
  .hk-chip {
    padding: 0.35rem 0.7rem; border-radius: 999px; border: 1px solid var(--border);
    background: rgba(255,255,255,0.02); font-size: 0.72rem; color: var(--fg-muted);
    white-space: nowrap;
  }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_INSPECTIONS = {
    backUrl: @json(route($backRoute)),
    indexUrl: @json(route('students.hotel.inspections.index')),
    roomsUrl: @json(route('students.hotel.rooms.index')),
    findings: @json(\App\Models\HotelRoomInspection::FINDINGS),
    statuses: @json(\App\Models\HotelRoomInspection::STATUSES),
    categories: @json(\App\Models\HotelComplaint::CATEGORY_DEPARTMENTS),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo, useRef } = React;

const CFG = window.HMS_INSPECTIONS;
const FINDINGS = CFG.findings;
const STATUS_CLASS = {
  'Pending': 'hk-pending',
  'Inspecting': 'hk-inspecting',
  'Awaiting Repair': 'hk-repair',
  'Awaiting Re-inspection': 'hk-reinspect',
  'Completed': 'hk-completed',
};
const OPEN_STATUSES = ['Pending', 'Inspecting', 'Awaiting Repair', 'Awaiting Re-inspection'];
const ROOM_STATUS_CLASS = {
  'Available': 'hk-room-available',
  'Cleaning': 'hk-room-cleaning',
  'Maintenance': 'hk-room-maintenance',
};

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

function Badge({ className, children }) {
  return <span className={`hk-badge ${className}`}>{children}</span>;
}

function RoomStrip({ rooms }) {
  if (!rooms.length) return null;
  return (
    <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap', marginBottom: '1.3rem' }}>
      {rooms.map(room => (
        <span key={room.id} className={`hk-chip ${ROOM_STATUS_CLASS[room.status] || ''}`}>
          {room.name} · {room.status}
        </span>
      ))}
    </div>
  );
}

function IssueForm({ categories, defaultCategory, onSubmit, onCancel, busy }) {
  const categoryNames = Object.keys(categories);
  const [category, setCategory] = useState(defaultCategory || categoryNames[0] || 'Other');
  const [details, setDetails] = useState('');
  const [error, setError] = useState('');

  const submit = () => {
    if (!details.trim()) { setError('Describe what you found so Maintenance knows what to bring.'); return; }
    setError('');
    onSubmit({ category, details: details.trim() });
  };

  return (
    <div style={{ marginTop: '0.9rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)' }}>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '0.75rem' }}>
        <div>
          <label className="hk-field-label">Category</label>
          <select className="hk-select" value={category} onChange={e => setCategory(e.target.value)}>
            {categoryNames.map(name => <option key={name} value={name}>{name}</option>)}
          </select>
        </div>
      </div>
      <div style={{ marginTop: '0.75rem' }}>
        <label className="hk-field-label">What did you find?</label>
        <textarea
          className="hk-input"
          rows={2}
          placeholder="e.g. Bathroom faucet leaking, needs a plumber."
          value={details}
          onChange={e => setDetails(e.target.value)}
          style={{ resize: 'vertical' }}
        />
      </div>
      {error && <p style={{ margin: '0.6rem 0 0', color: '#fb7185', fontSize: '0.8rem' }}>{error}</p>}
      <div style={{ marginTop: '0.75rem', display: 'flex', gap: '0.5rem', justifyContent: 'flex-end' }}>
        <button type="button" className="hk-tab" onClick={onCancel}>Cancel</button>
        <button type="button" className="btn-solid" disabled={busy} onClick={submit}>
          {busy ? 'Sending…' : 'Send to Maintenance'}
        </button>
      </div>
    </div>
  );
}

function InspectionCard({ inspection, canInspect, onAction, onReportIssue, busy }) {
  const [findingChoice, setFindingChoice] = useState(inspection.finding || Object.keys(FINDINGS)[0]);
  const [notes, setNotes] = useState(inspection.notes || '');
  const [reportingIssue, setReportingIssue] = useState(false);

  useEffect(() => { setNotes(inspection.notes || ''); }, [inspection.notes]);

  const status = inspection.status;
  const suggestedCategory = FINDINGS[findingChoice];
  const needsIssue = findingChoice && FINDINGS[findingChoice] !== null;

  const saveFinding = () => {
    onAction(inspection.id, { action: 'record', finding: findingChoice, notes });
  };

  return (
    <div className="hk-card">
      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '1rem', flexWrap: 'wrap' }}>
        <div style={{ minWidth: 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', flexWrap: 'wrap', marginBottom: '0.4rem' }}>
            <span style={{ color: 'var(--fg)', fontWeight: 600, fontSize: '0.95rem' }}>Room {inspection.roomName}</span>
            {inspection.guestName && (
              <span style={{ color: 'var(--fg-muted)', fontSize: '0.82rem' }}>· previously {inspection.guestName}</span>
            )}
          </div>
          <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap' }}>
            <Badge className={STATUS_CLASS[status] || ''}>{status}</Badge>
            {inspection.roomStatus && (
              <Badge className={ROOM_STATUS_CLASS[inspection.roomStatus] || ''}>Room: {inspection.roomStatus}</Badge>
            )}
          </div>
        </div>
        <div style={{ textAlign: 'right', fontSize: '0.72rem', color: 'var(--fg-muted)', whiteSpace: 'nowrap' }}>
          <div>Checked out {formatWhen(inspection.checkedOutAt)}</div>
        </div>
      </div>

      {inspection.finding && (
        <p style={{ margin: '0.85rem 0 0', color: 'var(--fg)', fontSize: '0.86rem' }}>
          <strong>Finding:</strong> {inspection.finding}
          {inspection.notes ? ` — ${inspection.notes}` : ''}
        </p>
      )}

      {inspection.issues.length > 0 && (
        <div style={{ marginTop: '0.85rem', display: 'grid', gap: '0.5rem' }}>
          {inspection.issues.map(issue => (
            <div key={issue.id} style={{ padding: '0.6rem 0.75rem', borderRadius: 8, background: 'rgba(255,255,255,0.02)', border: '1px solid var(--border)' }}>
              <div style={{ display: 'flex', justifyContent: 'space-between', gap: '0.5rem', flexWrap: 'wrap' }}>
                <span style={{ fontSize: '0.8rem', color: 'var(--fg)' }}>{issue.category} · {issue.details}</span>
                <span style={{ fontSize: '0.68rem', color: 'var(--fg-muted)' }}>{issue.status} · {issue.departmentLabel}</span>
              </div>
            </div>
          ))}
        </div>
      )}

      {!canInspect ? null : status === 'Pending' ? (
        <div style={{ marginTop: '1rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)' }}>
          <button type="button" className="btn-solid" disabled={busy} onClick={() => onAction(inspection.id, { action: 'start' })}>
            Start inspection
          </button>
        </div>
      ) : status === 'Inspecting' ? (
        <div style={{ marginTop: '1rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)' }}>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(180px, 1fr))', gap: '0.75rem' }}>
            <div>
              <label className="hk-field-label">What did you find?</label>
              <select className="hk-select" value={findingChoice} onChange={e => { setFindingChoice(e.target.value); setReportingIssue(false); }}>
                {Object.keys(FINDINGS).map(f => <option key={f} value={f}>{f}</option>)}
              </select>
            </div>
          </div>
          <div style={{ marginTop: '0.75rem' }}>
            <label className="hk-field-label">Notes (optional)</label>
            <textarea className="hk-input" rows={2} value={notes} onChange={e => setNotes(e.target.value)} style={{ resize: 'vertical' }} />
          </div>
          <div style={{ marginTop: '0.85rem', display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
            <button type="button" className="hk-tab" disabled={busy} onClick={saveFinding}>Save finding</button>
            {needsIssue ? (
              <button type="button" className="btn-solid" disabled={busy} onClick={() => { saveFinding(); setReportingIssue(true); }}>
                Report to Maintenance
              </button>
            ) : (
              <button type="button" className="btn-solid" disabled={busy} onClick={() => onAction(inspection.id, { action: 'complete' })}>
                Inspection completed
              </button>
            )}
          </div>
          {reportingIssue && needsIssue && (
            <IssueForm
              categories={CFG.categories}
              defaultCategory={suggestedCategory}
              busy={busy}
              onCancel={() => setReportingIssue(false)}
              onSubmit={(payload) => { onReportIssue(inspection.id, payload); setReportingIssue(false); }}
            />
          )}
        </div>
      ) : status === 'Awaiting Repair' ? (
        <p style={{ marginTop: '1rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)', color: 'var(--fg-muted)', fontSize: '0.82rem' }}>
          Waiting on Maintenance to close the issue above before this room can be re-inspected.
        </p>
      ) : status === 'Awaiting Re-inspection' ? (
        <div style={{ marginTop: '1rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)', display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
          <button type="button" className="hk-tab" disabled={busy} onClick={() => setReportingIssue(v => !v)}>
            Report another issue
          </button>
          <button type="button" className="btn-solid" disabled={busy} onClick={() => onAction(inspection.id, { action: 'complete' })}>
            Inspection completed
          </button>
          {reportingIssue && (
            <div style={{ width: '100%' }}>
              <IssueForm
                categories={CFG.categories}
                defaultCategory={null}
                busy={busy}
                onCancel={() => setReportingIssue(false)}
                onSubmit={(payload) => { onReportIssue(inspection.id, payload); setReportingIssue(false); }}
              />
            </div>
          )}
        </div>
      ) : (
        <p style={{ marginTop: '1rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)', color: 'var(--fg-muted)', fontSize: '0.78rem' }}>
          Completed by {inspection.completedBy || '—'} · {formatWhen(inspection.completedAt)}
        </p>
      )}
    </div>
  );
}

function App() {
  const [inspections, setInspections] = useState([]);
  const [rooms, setRooms] = useState([]);
  const [canInspect, setCanInspect] = useState(false);
  const [statusFilter, setStatusFilter] = useState('open');
  const [search, setSearch] = useState('');
  const [busy, setBusy] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const pendingWrites = useRef(0);

  const load = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(CFG.indexUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.inspections)) setInspections(data.inspections);
        setCanInspect(!!data.can_inspect);
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

  useEffect(() => {
    fetch(CFG.roomsUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => { if (Array.isArray(data.rooms)) setRooms(data.rooms); })
      .catch(() => {});
  }, []);

  const fail = (message) => window.toast && window.toast(message);

  const runAction = (id, payload) => {
    setBusy(true);
    pendingWrites.current += 1;
    fetch(`${CFG.indexUrl}/${id}`, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.message || 'Could not update the inspection.');
        return data;
      })
      .then(data => {
        if (data.inspection) {
          setInspections(prev => prev.map(i => (i.id === data.inspection.id ? data.inspection : i)));
          if (window.toast) window.toast('Saved.');
        }
      })
      .catch(e => fail(e.message))
      .finally(() => {
        setBusy(false);
        pendingWrites.current = Math.max(0, pendingWrites.current - 1);
      });
  };

  const reportIssue = (id, payload) => {
    setBusy(true);
    pendingWrites.current += 1;
    fetch(`${CFG.indexUrl}/${id}/issues`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.message || 'Could not send this to Maintenance.');
        return data;
      })
      .then(data => {
        if (data.inspection) {
          setInspections(prev => prev.map(i => (i.id === data.inspection.id ? data.inspection : i)));
          if (window.toast) window.toast('Sent to Maintenance.');
        }
      })
      .catch(e => fail(e.message))
      .finally(() => {
        setBusy(false);
        pendingWrites.current = Math.max(0, pendingWrites.current - 1);
      });
  };

  const visible = useMemo(() => {
    const q = search.trim().toLowerCase();
    return inspections.filter(i => {
      if (statusFilter === 'open' && !OPEN_STATUSES.includes(i.status)) return false;
      if (statusFilter === 'completed' && i.status !== 'Completed') return false;
      if (!q) return true;
      return [i.roomName, i.guestName, i.finding].some(field => String(field || '').toLowerCase().includes(q));
    });
  }, [inspections, statusFilter, search]);

  const openCount = inspections.filter(i => OPEN_STATUSES.includes(i.status)).length;

  return (
    <div data-hms-no-edit="1" style={{ maxWidth: 1100, margin: '0 auto', padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Housekeeping</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Room Inspections</h1>
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem' }}>
            {openCount === 0 ? 'Nothing waiting right now.' : `${openCount} room${openCount === 1 ? '' : 's'} need attention`}
          </p>
        </div>
        <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </a>
      </div>

      <RoomStrip rooms={rooms} />

      <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap', alignItems: 'center', marginBottom: '1rem' }}>
        {[['open', 'Needs attention'], ['completed', 'Completed'], ['all', 'All']].map(([key, label]) => (
          <button key={key} type="button" className={`hk-tab ${statusFilter === key ? 'is-active' : ''}`} onClick={() => setStatusFilter(key)}>
            {label}
          </button>
        ))}
      </div>

      <div style={{ position: 'relative', marginBottom: '1.1rem' }}>
        <i className="fa-solid fa-magnifying-glass" style={{ position: 'absolute', left: '0.75rem', top: '50%', transform: 'translateY(-50%)', color: 'var(--fg-muted)', fontSize: '0.75rem', pointerEvents: 'none' }}></i>
        <input
          type="text"
          className="hk-input"
          placeholder="Search by room, guest, or finding…"
          value={search}
          onChange={e => setSearch(e.target.value)}
          style={{ paddingLeft: '2.1rem' }}
        />
      </div>

      {!loaded ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.85rem' }}>
          Loading inspections…
        </div>
      ) : visible.length === 0 ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem', textAlign: 'center' }}>
          <i className="fa-solid fa-broom" style={{ fontSize: '1.8rem', color: 'var(--fg-muted)', opacity: 0.35, display: 'block', marginBottom: '0.7rem' }}></i>
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.88rem' }}>
            {inspections.length === 0 ? 'No rooms have been checked out yet.' : 'Nothing matches these filters.'}
          </p>
        </div>
      ) : (
        <div style={{ display: 'grid', gap: '0.85rem' }}>
          {visible.map(inspection => (
            <InspectionCard
              key={inspection.id}
              inspection={inspection}
              canInspect={canInspect}
              busy={busy}
              onAction={runAction}
              onReportIssue={reportIssue}
            />
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
