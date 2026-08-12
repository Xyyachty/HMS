@extends('students.builder.ops-shell')

@php
    $complaintRole = $builderRole ?? 'front_desk';
    $backRoute = \App\Support\HotelTemplateBuilder::ROLE_ROUTES[$complaintRole] ?? 'students.dashboard';
    $pageTitle = $complaintRole === 'front_desk' ? 'Complaints' : 'Complaints / Concerns';
@endphp

@section('page-title', $pageTitle)

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: 'Outfit', sans-serif; }
  .font-display { font-family: 'Playfair Display', serif; }
  .cx-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; display: inline-block;
  }
  .cx-open        { background: rgba(244,63,94,0.18);  color: #fb7185; border-color: rgba(244,63,94,0.35); }
  .cx-in-progress { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35); }
  .cx-resolved    { background: rgba(34,197,94,0.18);  color: #4ade80; border-color: rgba(34,197,94,0.35); }
  .cx-cancelled   { background: rgba(148,163,184,0.15); color: #94a3b8; border-color: rgba(148,163,184,0.3); }
  .cx-maintenance { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .cx-housekeeping{ background: rgba(16,185,129,0.18); color: #34d399; border-color: rgba(16,185,129,0.35); }
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
  .booking-input, .booking-select {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: 'Outfit', sans-serif; font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus, .booking-select:focus { border-color: var(--accent); }
  .booking-input::placeholder { color: var(--fg-muted); opacity: 0.5; }
  .booking-select option { background: #181714; color: var(--fg); }
  .cx-field-label {
    display: block; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--fg-muted); margin-bottom: 0.4rem;
  }
  .cx-tab {
    padding: 0.4rem 0.9rem; border-radius: 999px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer;
    font-family: 'Outfit', sans-serif; font-size: 0.72rem; font-weight: 600;
    letter-spacing: 0.06em; transition: all 0.15s;
  }
  .cx-tab:hover { color: var(--fg); }
  .cx-tab.is-active { border-color: var(--accent); background: var(--accent); color: var(--bg); }
  .cx-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 1.1rem 1.2rem;
  }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_COMPLAINTS = {
    role: @json($complaintRole),
    backUrl: @json(route($backRoute)),
    indexUrl: @json(route('students.hotel.complaints.index')),
    storeUrl: @json(route('students.hotel.complaints.store')),
    roomsUrl: @json(route('students.hotel.rooms.index')),
    departments: @json(\App\Models\HotelComplaint::DEPARTMENTS),
    statuses: @json(\App\Models\HotelComplaint::STATUSES),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo, useRef } = React;

const CFG = window.HMS_COMPLAINTS;
const DEPARTMENT_LABELS = CFG.departments;
const STATUSES = CFG.statuses;
const OPEN_STATUSES = ['Open', 'In Progress'];
const COMPLAINT_FLOW = STATUSES.filter(s => s !== 'Cancelled');

/* Mirrors HotelComplaint::isForwardTransition() — status only moves forward here
   too, so a disabled pill in the UI matches what the server would refuse anyway. */
function canMoveComplaintTo(from, to) {
  if (from === to || from === 'Resolved' || from === 'Cancelled') return false;
  if (to === 'Cancelled') return true;
  const fromAt = COMPLAINT_FLOW.indexOf(from);
  const toAt = COMPLAINT_FLOW.indexOf(to);
  return fromAt !== -1 && toAt !== -1 && toAt > fromAt;
}

function slug(value) {
  return String(value || '').trim().toLowerCase().replace(/\s+/g, '-');
}

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

/* The department page a member arrives from decides what they see first; the
   Front Desk records for both, so it starts unfiltered. */
function defaultDepartmentFilter(role) {
  return DEPARTMENT_LABELS[role] ? role : 'all';
}

function Badge({ className, children }) {
  return <span className={`cx-badge ${className}`}>{children}</span>;
}

function ComplaintForm({ rooms, categories, onSubmit, busy }) {
  const categoryNames = Object.keys(categories);
  const [roomNumber, setRoomNumber] = useState('');
  const [guestName, setGuestName] = useState('');
  const [category, setCategory] = useState(categoryNames[0] || 'Other');
  // Touched once the staffer overrides the category's suggestion, after which
  // changing the category must stop moving the department under them.
  const [departmentTouched, setDepartmentTouched] = useState(false);
  const [department, setDepartment] = useState(categories[categoryNames[0]] || 'maintenance');
  const [details, setDetails] = useState('');
  const [error, setError] = useState('');

  /* Only rooms with a guest actually in them. A complaint comes from someone staying
     in the room, so a vacant or merely-booked room is not a room to file against. */
  const occupiedRooms = (rooms || []).filter(
    r => r.reservation && r.reservation.status === 'Checked In'
  );

  const pickCategory = (value) => {
    setCategory(value);
    if (!departmentTouched) setDepartment(categories[value] || 'maintenance');
  };

  const pickRoom = (value) => {
    setRoomNumber(value);
    const room = (rooms || []).find(r => r.name === value);
    const reservedName = room && room.reservation ? room.reservation.fullName : '';
    if (reservedName) setGuestName(reservedName);
  };

  const submit = (e) => {
    e.preventDefault();
    if (!roomNumber.trim()) { setError('Choose the room the guest is calling about.'); return; }
    if (!details.trim()) { setError('Describe the complaint so the department knows what to bring.'); return; }
    setError('');
    onSubmit({
      room_number: roomNumber.trim(),
      guest_name: guestName.trim(),
      category,
      department,
      details: details.trim(),
    }, () => {
      setDetails('');
      setDepartmentTouched(false);
    });
  };

  return (
    <form onSubmit={submit} className="cx-card" style={{ marginBottom: '1.5rem' }}>
      <h2 className="font-display" style={{ fontSize: '1.1rem', margin: '0 0 1rem', color: 'var(--fg)' }}>
        Record a complaint
      </h2>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(190px, 1fr))', gap: '0.9rem' }}>
        <div>
          <label className="cx-field-label">Room</label>
          {occupiedRooms.length > 0 ? (
            <select className="booking-select" value={roomNumber} onChange={e => pickRoom(e.target.value)}>
              <option value="">Select a room…</option>
              {occupiedRooms.map(room => (
                <option key={room.id} value={room.name}>
                  {room.name} · {room.reservation.fullName || 'Guest'}
                </option>
              ))}
            </select>
          ) : (
            <>
              {/* Nobody is checked in, but the desk can still take a complaint from a
                  walk-in or a diner, so the field stays usable as free text. */}
              <input
                type="text"
                className="booking-input"
                placeholder="Room number"
                value={roomNumber}
                onChange={e => setRoomNumber(e.target.value)}
              />
              <p style={{ margin: '0.35rem 0 0', fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                No guests are checked in right now.
              </p>
            </>
          )}
        </div>

        <div>
          <label className="cx-field-label">Guest</label>
          <input
            type="text"
            className="booking-input"
            placeholder="Guest name (optional)"
            value={guestName}
            onChange={e => setGuestName(e.target.value)}
          />
        </div>

        <div>
          <label className="cx-field-label">Category</label>
          <select className="booking-select" value={category} onChange={e => pickCategory(e.target.value)}>
            {categoryNames.map(name => <option key={name} value={name}>{name}</option>)}
          </select>
        </div>

        <div>
          <label className="cx-field-label">
            Department
            {!departmentTouched && <span style={{ color: 'var(--accent)', marginLeft: 6, letterSpacing: 0 }}>auto</span>}
          </label>
          <select
            className="booking-select"
            value={department}
            onChange={e => { setDepartment(e.target.value); setDepartmentTouched(true); }}
          >
            {Object.entries(DEPARTMENT_LABELS).map(([key, label]) => (
              <option key={key} value={key}>{label}</option>
            ))}
          </select>
        </div>

      </div>

      <div style={{ marginTop: '0.9rem' }}>
        <label className="cx-field-label">What did the guest report?</label>
        <textarea
          className="booking-input"
          rows={3}
          placeholder="e.g. Air conditioner is not cooling, guest reported it at 9pm."
          value={details}
          onChange={e => setDetails(e.target.value)}
          style={{ resize: 'vertical' }}
        />
      </div>

      {error && (
        <p style={{ margin: '0.75rem 0 0', color: '#fb7185', fontSize: '0.8rem' }}>{error}</p>
      )}

      <div style={{ marginTop: '1rem', display: 'flex', justifyContent: 'flex-end' }}>
        <button type="submit" className="btn-solid" disabled={busy}>
          <i className="fa-solid fa-paper-plane" style={{ fontSize: '0.7rem' }}></i>
          {busy ? 'Sending…' : `Send to ${DEPARTMENT_LABELS[department]}`}
        </button>
      </div>
    </form>
  );
}

function ComplaintCard({ complaint, canHandle, canCancel, onUpdate }) {
  const [note, setNote] = useState(complaint.resolutionNote || '');
  const [noteOpen, setNoteOpen] = useState(false);
  const otherDepartment = complaint.department === 'maintenance' ? 'housekeeping' : 'maintenance';
  const isClosed = complaint.status === 'Resolved' || complaint.status === 'Cancelled';

  useEffect(() => { setNote(complaint.resolutionNote || ''); }, [complaint.resolutionNote]);

  return (
    <div className="cx-card">
      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '1rem', flexWrap: 'wrap' }}>
        <div style={{ minWidth: 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', flexWrap: 'wrap', marginBottom: '0.4rem' }}>
            <span style={{ color: 'var(--fg)', fontWeight: 600, fontSize: '0.95rem' }}>Room {complaint.roomNumber}</span>
            {complaint.guestName && (
              <span style={{ color: 'var(--fg-muted)', fontSize: '0.82rem' }}>· {complaint.guestName}</span>
            )}
          </div>
          <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap' }}>
            <Badge className={`cx-${slug(complaint.status)}`}>{complaint.status}</Badge>
            <Badge className={`cx-${complaint.department}`}>{complaint.departmentLabel}</Badge>
          </div>
        </div>
        <div style={{ textAlign: 'right', fontSize: '0.72rem', color: 'var(--fg-muted)', whiteSpace: 'nowrap' }}>
          <div>{complaint.category}</div>
          <div style={{ opacity: 0.7 }}>{formatWhen(complaint.filedAt)}</div>
        </div>
      </div>

      <p style={{ margin: '0.85rem 0 0', color: 'var(--fg)', fontSize: '0.86rem', lineHeight: 1.55, whiteSpace: 'pre-wrap' }}>
        {complaint.details}
      </p>

      <p style={{ margin: '0.6rem 0 0', fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
        Filed by {complaint.filedBy || 'Front Desk'}
        {complaint.handledBy ? ` · Handled by ${complaint.handledBy}` : ''}
        {complaint.resolvedAt ? ` · Closed ${formatWhen(complaint.resolvedAt)}` : ''}
      </p>

      {complaint.resolutionNote && (
        <div style={{ marginTop: '0.75rem', padding: '0.65rem 0.8rem', borderRadius: 8, background: 'rgba(34,197,94,0.07)', border: '1px solid rgba(34,197,94,0.2)' }}>
          <span style={{ display: 'block', fontSize: '0.6rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: '#4ade80', marginBottom: 4 }}>Resolution</span>
          <span style={{ fontSize: '0.82rem', color: 'var(--fg)' }}>{complaint.resolutionNote}</span>
        </div>
      )}

      {(canHandle || canCancel) && (
        <div style={{ marginTop: '1rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)', display: 'flex', gap: '0.4rem', flexWrap: 'wrap', alignItems: 'center' }}>
          {canHandle && STATUSES.map(status => (
            <button
              key={status}
              type="button"
              className={`cx-tab ${complaint.status === status ? 'is-active' : ''}`}
              disabled={!canMoveComplaintTo(complaint.status, status)}
              onClick={() => onUpdate(complaint.id, { status })}
            >
              {status}
            </button>
          ))}

          {!canHandle && canCancel && canMoveComplaintTo(complaint.status, 'Cancelled') && (
            <button type="button" className="cx-tab" onClick={() => onUpdate(complaint.id, { status: 'Cancelled' })}>
              Cancel complaint
            </button>
          )}

          {canHandle && (
            <>
              <button type="button" className="cx-tab" onClick={() => setNoteOpen(v => !v)}>
                <i className="fa-solid fa-pen" style={{ fontSize: '0.65rem', marginRight: 5 }}></i>
                {complaint.resolutionNote ? 'Edit note' : 'Add note'}
              </button>
              {/* A closed complaint cannot be handed over — that would reopen it,
                  the same backward move the status pills forbid. */}
              {!isClosed && (
                <button
                  type="button"
                  className="cx-tab"
                  title={`Hand this complaint to ${DEPARTMENT_LABELS[otherDepartment]}`}
                  onClick={() => onUpdate(complaint.id, { department: otherDepartment })}
                >
                  <i className="fa-solid fa-right-left" style={{ fontSize: '0.65rem', marginRight: 5 }}></i>
                  Send to {DEPARTMENT_LABELS[otherDepartment]}
                </button>
              )}
            </>
          )}
        </div>
      )}

      {canHandle && noteOpen && (
        <div style={{ marginTop: '0.75rem', display: 'flex', gap: '0.5rem', alignItems: 'flex-start' }}>
          <input
            type="text"
            className="booking-input"
            placeholder="What was done to fix it?"
            value={note}
            onChange={e => setNote(e.target.value)}
          />
          <button
            type="button"
            className="btn-solid"
            onClick={() => { onUpdate(complaint.id, { resolution_note: note }); setNoteOpen(false); }}
          >
            Save
          </button>
        </div>
      )}
    </div>
  );
}

function App() {
  const [complaints, setComplaints] = useState([]);
  const [rooms, setRooms] = useState([]);
  const [categories, setCategories] = useState({});
  const [canFile, setCanFile] = useState(false);
  const [handled, setHandled] = useState([]);
  const [statusFilter, setStatusFilter] = useState('open');
  const [departmentFilter, setDepartmentFilter] = useState(defaultDepartmentFilter(CFG.role));
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
        if (Array.isArray(data.complaints)) setComplaints(data.complaints);
        if (data.categories) setCategories(data.categories);
        setCanFile(!!data.can_file);
        setHandled(data.handled_departments || []);
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

  const fileComplaint = (payload, reset) => {
    setBusy(true);
    pendingWrites.current += 1;
    fetch(CFG.storeUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.message || 'Could not record the complaint.');
        return data;
      })
      .then(data => {
        if (data.complaint) setComplaints(prev => [data.complaint, ...prev]);
        if (window.toast) window.toast(`Sent to ${DEPARTMENT_LABELS[payload.department]} — Room ${payload.room_number}`);
        if (reset) reset();
      })
      .catch(e => fail(e.message))
      .finally(() => {
        setBusy(false);
        pendingWrites.current = Math.max(0, pendingWrites.current - 1);
      });
  };

  const updateComplaint = (id, patch) => {
    pendingWrites.current += 1;
    fetch(`${CFG.indexUrl}/${id}`, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(patch),
    })
      .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.message || 'Could not update the complaint.');
        return data;
      })
      .then(data => {
        if (data.complaint) {
          setComplaints(prev => prev.map(c => (c.id === data.complaint.id ? data.complaint : c)));
          if (patch.department) window.toast && window.toast(`Handed to ${data.complaint.departmentLabel}`);
          else if (patch.status) window.toast && window.toast(`Marked ${data.complaint.status}`);
          else window.toast && window.toast('Note saved');
        }
      })
      .catch(e => fail(e.message))
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  };

  const visible = useMemo(() => {
    const q = search.trim().toLowerCase();
    return complaints.filter(c => {
      if (departmentFilter !== 'all' && c.department !== departmentFilter) return false;
      if (statusFilter === 'open' && !OPEN_STATUSES.includes(c.status)) return false;
      if (statusFilter === 'closed' && OPEN_STATUSES.includes(c.status)) return false;
      if (!q) return true;
      return [c.roomNumber, c.guestName, c.category, c.details, c.filedBy]
        .some(field => String(field || '').toLowerCase().includes(q));
    });
  }, [complaints, departmentFilter, statusFilter, search]);

  const openCount = complaints.filter(c =>
    OPEN_STATUSES.includes(c.status) &&
    (departmentFilter === 'all' || c.department === departmentFilter)
  ).length;

  const isDepartmentView = !!DEPARTMENT_LABELS[CFG.role];
  const eyebrow = isDepartmentView ? DEPARTMENT_LABELS[CFG.role] : 'Front Desk';
  const heading = isDepartmentView ? 'Complaints & Concerns' : 'Guest Complaints';

  return (
    <div data-hms-no-edit="1" style={{ maxWidth: 1100, margin: '0 auto', padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>{eyebrow}</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>{heading}</h1>
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem' }}>
            {openCount === 0 ? 'Nothing open right now.' : `${openCount} still open`}
          </p>
        </div>
        <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </a>
      </div>

      {canFile && (
        <ComplaintForm rooms={rooms} categories={categories} onSubmit={fileComplaint} busy={busy} />
      )}

      <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap', alignItems: 'center', marginBottom: '1rem' }}>
        {[['open', 'Open'], ['closed', 'Closed'], ['all', 'All']].map(([key, label]) => (
          <button key={key} type="button" className={`cx-tab ${statusFilter === key ? 'is-active' : ''}`} onClick={() => setStatusFilter(key)}>
            {label}
          </button>
        ))}
        <span style={{ width: 1, height: 20, background: 'var(--border)', margin: '0 0.3rem' }}></span>
        <button type="button" className={`cx-tab ${departmentFilter === 'all' ? 'is-active' : ''}`} onClick={() => setDepartmentFilter('all')}>
          Both departments
        </button>
        {Object.entries(DEPARTMENT_LABELS).map(([key, label]) => (
          <button key={key} type="button" className={`cx-tab ${departmentFilter === key ? 'is-active' : ''}`} onClick={() => setDepartmentFilter(key)}>
            {label}
          </button>
        ))}
      </div>

      <div style={{ position: 'relative', marginBottom: '1.1rem' }}>
        <i className="fa-solid fa-magnifying-glass" style={{ position: 'absolute', left: '0.75rem', top: '50%', transform: 'translateY(-50%)', color: 'var(--fg-muted)', fontSize: '0.75rem', pointerEvents: 'none' }}></i>
        <input
          type="text"
          className="booking-input"
          placeholder="Search by room, guest, category, or details…"
          value={search}
          onChange={e => setSearch(e.target.value)}
          style={{ paddingLeft: '2.1rem' }}
        />
      </div>

      {!loaded ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.85rem' }}>
          Loading complaints…
        </div>
      ) : visible.length === 0 ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem', textAlign: 'center' }}>
          <i className="fa-solid fa-clipboard-check" style={{ fontSize: '1.8rem', color: 'var(--fg-muted)', opacity: 0.35, display: 'block', marginBottom: '0.7rem' }}></i>
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.88rem' }}>
            {complaints.length === 0
              ? (canFile ? 'No complaints yet. Record one above when a guest reports a problem.' : 'No complaints have been sent to your department yet.')
              : 'Nothing matches these filters.'}
          </p>
        </div>
      ) : (
        <div style={{ display: 'grid', gap: '0.85rem' }}>
          {visible.map(complaint => (
            <ComplaintCard
              key={complaint.id}
              complaint={complaint}
              canHandle={handled.includes(complaint.department)}
              canCancel={canFile}
              onUpdate={updateComplaint}
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
