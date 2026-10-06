@extends('students.builder.ops-shell')

@php
    $complaintRole = $builderRole ?? 'front_desk';
    $pageTitle = $complaintRole === 'front_desk' ? 'Complaints' : 'Complaints / Concerns';
@endphp

@section('page-title', $pageTitle)

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }

  /* Everything below reads the shell's tokens (--bg, --card, --border, --fg,
     --fg-muted, --accent), the same way Walk-in Guests, Room Service and
     Amenities do, so the page follows Template 1, Template 2 and a team's own site
     colours. Tints are mixed from those tokens rather than hard-coded.
     Shape rule: pills for tabs, choices and status; 10px for inputs and buttons;
     14px for panels and cards. */
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }

  .cx {
    --cx-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --cx-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --cx-line: var(--border);
    --cx-danger: var(--danger, #f87171);
    --cx-ok: var(--success, #4ade80);
    --cx-warn: var(--warn, #fbbf24);
    padding: 1.5rem 1.5rem 3rem;
    color: var(--fg);
  }

  /* Header */
  .cx-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .cx-eyebrow { color: var(--accent); font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; margin: 0 0 0.5rem; }
  .cx-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .cx-lead { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 95ch; line-height: 1.5; }

  /* Buttons */
  .cx-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem;
    font: 600 0.88rem/1 var(--font-body, 'Outfit', sans-serif);
    padding: 0.8rem 1.15rem; border-radius: 10px; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); transition: filter 0.15s, background 0.15s, transform 0.1s;
  }
  .cx-btn:active:not(:disabled) { transform: translateY(1px); }
  .cx-btn-primary { background: var(--accent); color: var(--bg); }
  .cx-btn-primary:hover:not(:disabled) { filter: brightness(1.08); }
  .cx-btn-ghost { background: transparent; color: var(--accent); }
  .cx-btn-ghost:hover:not(:disabled) { background: var(--cx-tint); }
  .cx-btn:disabled { opacity: 0.5; cursor: not-allowed; }
  .cx-btn-wide { width: 100%; padding: 0.9rem 1.2rem; font-size: 0.95rem; }
  .cx-btn-sm {
    display: inline-flex; align-items: center; gap: 0.4rem;
    font: 600 0.8rem/1 var(--font-body, 'Outfit', sans-serif);
    padding: 0.55rem 0.8rem; border-radius: 8px; cursor: pointer;
    background: transparent; color: var(--fg); border: 1px solid var(--cx-line);
  }
  .cx-btn-sm:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
  .cx-btn-sm.is-danger:hover:not(:disabled) { border-color: var(--cx-danger); color: var(--cx-danger); }
  .cx-btn:focus-visible, .cx-btn-sm:focus-visible, .cx-tab:focus-visible, .cx-chip:focus-visible, .cx-team:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  /* Page layout: the form beside the list; one column on a narrow screen */
  .cx-layout { display: grid; grid-template-columns: clamp(380px, 34vw, 520px) minmax(0, 1fr); gap: 1.25rem; align-items: start; }
  .cx-layout.is-single { grid-template-columns: minmax(0, 1fr); }
  .cx-panel { background: var(--card); border: 1px solid var(--cx-line); border-radius: 14px; padding: 1.05rem 1.25rem 1.15rem; min-width: 0; }
  .cx-panel-title { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--fg); }
  .cx-panel-sub { margin: 0.25rem 0 0; font-size: 0.82rem; color: var(--fg-muted); line-height: 1.45; }

  /* The report form, as numbered steps */
  .cx-steps { display: grid; gap: 0.75rem; margin-top: 0.75rem; }
  .cx-step-head { display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.4rem; }
  .cx-num {
    flex: none; width: 24px; height: 24px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 700; border: 1.5px solid var(--cx-line); color: var(--fg-muted);
  }
  .cx-step.is-done .cx-num { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .cx-step-head label, .cx-step-head span.cx-q { font-size: 0.9rem; font-weight: 600; color: var(--fg); }
  .cx-help { margin: 0.35rem 0 0; font-size: 0.78rem; color: var(--fg-muted); line-height: 1.45; }

  .cx-input {
    width: 100%; box-sizing: border-box;
    background: var(--cx-soft); border: 1px solid var(--cx-line); border-radius: 10px;
    padding: 0.65rem 0.85rem; color: var(--fg); outline: none;
    font: 400 0.92rem/1.35 var(--font-body, 'Outfit', sans-serif);
    transition: border-color 0.15s, box-shadow 0.15s;
  }
  .cx-input::placeholder { color: var(--fg-muted); opacity: 0.75; }
  .cx-input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--cx-tint); }
  .cx-input:disabled { opacity: 0.6; cursor: not-allowed; }
  select.cx-input option { background: var(--card); color: var(--fg); }
  textarea.cx-input { resize: vertical; min-height: 58px; }
  .cx-two { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.6rem; }

  .cx-group-label { margin: 0.1rem 0 0.3rem; font-size: 0.72rem; font-weight: 600; color: var(--fg-muted); }
  .cx-chips { display: flex; flex-wrap: wrap; gap: 0.4rem; }
  .cx-chip {
    font: 600 0.8rem/1 var(--font-body, 'Outfit', sans-serif);
    padding: 0.42rem 0.7rem; border-radius: 999px; cursor: pointer;
    border: 1.5px solid var(--cx-line); background: transparent; color: var(--fg-muted);
    transition: border-color 0.15s, color 0.15s, background 0.15s;
  }
  .cx-chip:hover { border-color: var(--accent); color: var(--fg); }
  .cx-chip.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); }

  .cx-teams { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.55rem; }
  .cx-team {
    display: flex; align-items: center; gap: 0.6rem; text-align: left;
    padding: 0.6rem 0.8rem; border-radius: 10px; cursor: pointer;
    background: transparent; border: 1.5px solid var(--cx-line); color: var(--fg);
    font: 600 0.86rem/1.2 var(--font-body, 'Outfit', sans-serif);
  }
  .cx-team:hover { border-color: color-mix(in srgb, var(--accent) 55%, var(--cx-line)); }
  .cx-team.is-on { border-color: var(--accent); background: var(--cx-tint); }
  .cx-team i { color: var(--accent); width: 1rem; text-align: center; }
  .cx-team small { display: block; font-size: 0.7rem; font-weight: 600; color: var(--accent); margin-top: 0.2rem; }

  .cx-error { margin: 0; padding: 0.65rem 0.8rem; border-radius: 10px; font-size: 0.84rem; color: var(--fg);
    background: color-mix(in srgb, var(--cx-danger) 12%, transparent); border: 1px solid color-mix(in srgb, var(--cx-danger) 35%, transparent); }

  /* List toolbar */
  .cx-toolbar { display: grid; gap: 0.75rem; margin-bottom: 1.1rem; }
  .cx-toolbar-row { display: flex; align-items: center; justify-content: space-between; gap: 0.6rem 1rem; flex-wrap: wrap; }
  .cx-tabs { box-sizing: border-box; max-width: 100%; display: inline-flex; flex-wrap: wrap; gap: 0.3rem; padding: 0.3rem; border-radius: 999px; background: var(--cx-soft); border: 1px solid var(--cx-line); }
  .cx-tab {
    display: inline-flex; align-items: center; gap: 0.5rem;
    font: 600 0.84rem/1 var(--font-body, 'Outfit', sans-serif);
    padding: 0.6rem 0.95rem; border-radius: 999px; cursor: pointer;
    border: 0; background: transparent; color: var(--fg-muted);
    transition: background 0.15s, color 0.15s;
  }
  .cx-tab:hover { color: var(--fg); }
  .cx-tab.is-on { background: var(--accent); color: var(--bg); }
  .cx-count {
    min-width: 1.4rem; padding: 0.2rem 0.4rem; border-radius: 999px; text-align: center;
    font-size: 0.72rem; font-variant-numeric: tabular-nums;
    background: color-mix(in srgb, var(--fg) 8%, transparent);
  }
  .cx-tab.is-on .cx-count { background: color-mix(in srgb, var(--bg) 22%, transparent); }
  .cx-tabs.is-small .cx-tab { padding: 0.5rem 0.8rem; font-size: 0.8rem; }
  .cx-search { position: relative; }
  .cx-search i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.8rem; pointer-events: none; }
  .cx-search .cx-input { padding-left: 2.3rem; }

  /* Complaint cards */
  .cx-list { display: grid; gap: 0.9rem; }
  .cx-card { background: var(--card); border: 1px solid var(--cx-line); border-radius: 14px; padding: 1.05rem 1.15rem; display: grid; gap: 0.85rem; min-width: 0; }
  .cx-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; }
  .cx-room { display: block; font-size: 1.1rem; font-weight: 700; color: var(--fg); line-height: 1.2; }
  .cx-guest { display: block; font-size: 0.84rem; color: var(--fg-muted); margin-top: 0.2rem; }
  .cx-pills { display: flex; gap: 0.4rem; flex-wrap: wrap; }
  .cx-pill {
    display: inline-flex; align-items: center; gap: 0.4rem; white-space: nowrap;
    padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.76rem; font-weight: 600;
    background: var(--cx-soft); color: var(--fg); border: 1px solid var(--cx-line);
  }
  .cx-pill .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--fg-muted); }
  .cx-pill.is-Open .dot { background: var(--cx-danger); }
  .cx-pill.is-In-Progress .dot { background: var(--cx-warn); }
  .cx-pill.is-Resolved .dot { background: var(--cx-ok); }
  .cx-pill i { color: var(--accent); font-size: 0.75rem; }

  .cx-what { margin: 0; padding: 0.75rem 0.85rem; border-radius: 10px; background: var(--cx-soft); border: 1px solid var(--cx-line); }
  .cx-what small { display: block; font-size: 0.72rem; font-weight: 600; color: var(--fg-muted); margin-bottom: 0.3rem; }
  .cx-what p { margin: 0; font-size: 0.9rem; line-height: 1.55; color: var(--fg); white-space: pre-wrap; overflow-wrap: anywhere; }

  /* Where the complaint is: three steps */
  .cx-track { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); max-width: 420px; }
  .cx-track li { position: relative; display: flex; flex-direction: column; align-items: center; gap: 0.35rem; font-size: 0.72rem; color: var(--fg-muted); text-align: center; }
  .cx-track li::before { content: ''; position: absolute; top: 6px; left: -50%; width: 100%; height: 2px; background: var(--cx-line); }
  .cx-track li:first-child::before { display: none; }
  .cx-track li.is-done::before, .cx-track li.is-now::before { background: var(--accent); }
  .cx-dot { position: relative; z-index: 1; width: 14px; height: 14px; border-radius: 50%; background: var(--card); border: 2px solid var(--cx-line); }
  .cx-track li.is-done .cx-dot { background: var(--accent); border-color: var(--accent); }
  .cx-track li.is-now .cx-dot { border-color: var(--accent); box-shadow: 0 0 0 4px var(--cx-tint); }
  .cx-track li.is-done, .cx-track li.is-now { color: var(--fg); }
  .cx-track li.is-now { font-weight: 700; }

  .cx-meta { margin: 0; font-size: 0.78rem; color: var(--fg-muted); line-height: 1.5; }
  .cx-fix { padding: 0.7rem 0.85rem; border-radius: 10px; background: color-mix(in srgb, var(--cx-ok) 10%, transparent); border: 1px solid color-mix(in srgb, var(--cx-ok) 30%, transparent); }
  .cx-fix small { display: block; font-size: 0.72rem; font-weight: 600; color: var(--fg); margin-bottom: 0.25rem; }
  .cx-fix span { font-size: 0.86rem; color: var(--fg); }

  .cx-actions { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; padding-top: 0.85rem; border-top: 1px solid var(--cx-line); }
  .cx-actions .cx-spacer { flex: 1; }
  .cx-note-form { display: flex; gap: 0.5rem; align-items: stretch; flex-wrap: wrap; }
  .cx-note-form .cx-input { flex: 1 1 220px; }

  /* Empty / loading */
  .cx-empty { border: 1.5px dashed var(--cx-line); border-radius: 14px; padding: 2.4rem 1.5rem; text-align: center; }
  .cx-empty-icon {
    width: 56px; height: 56px; margin: 0 auto 0.9rem; border-radius: 16px;
    display: flex; align-items: center; justify-content: center; font-size: 1.35rem;
    background: var(--cx-tint); color: var(--accent);
  }
  .cx-empty h2 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .cx-empty p { margin: 0.4rem auto 0; max-width: 46ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }

  /* On a wide screen the page itself does not scroll: the header and the report
     form stay where they are and only the list of complaints scrolls, in its own
     column. The form scrolls on its own only if the screen is too short for it.
     A narrow screen stacks everything and scrolls as one page instead. */
  @media (min-width: 1101px) {
    #ops-root { height: 100%; }
    .cx { height: 100%; box-sizing: border-box; display: flex; flex-direction: column; padding-bottom: 1.25rem; }
    .cx-head { flex: none; }
    .cx-layout { flex: 1; min-height: 0; align-items: stretch; }
    .cx-layout > .cx-panel { min-height: 0; overflow-y: auto; }
    .cx-list-col { display: flex; flex-direction: column; min-height: 0; }
    .cx-list-col .cx-toolbar { flex: none; }
    .cx-scroll { flex: 1; min-height: 0; overflow-y: auto; padding-right: 0.35rem; }
  }
  .cx-scroll::-webkit-scrollbar, .cx-layout > .cx-panel::-webkit-scrollbar { width: 6px; }
  .cx-scroll::-webkit-scrollbar-track, .cx-layout > .cx-panel::-webkit-scrollbar-track { background: transparent; }
  .cx-scroll::-webkit-scrollbar-thumb, .cx-layout > .cx-panel::-webkit-scrollbar-thumb { background: color-mix(in srgb, var(--fg) 25%, transparent); border-radius: 10px; }

  @media (max-width: 1100px) {
    .cx-layout { grid-template-columns: minmax(0, 1fr); }
  }
  @media (max-width: 560px) {
    .cx { padding: 1.1rem 1rem 2.5rem; }
    .cx-tabs { width: 100%; border-radius: 14px; }
    .cx-teams { grid-template-columns: minmax(0, 1fr); }
  }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ── */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d; --warn: #b45309;
  }
  :root[data-ops-theme="2"] .cx-card, :root[data-ops-theme="2"] .cx-panel { box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_COMPLAINTS = {
    role: @json($complaintRole),
    backUrl: @json(route('students.dashboard', ['section' => 'tasks'])),
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

/* What each status means to someone at the desk. The stored value never changes —
   only the words on screen. */
const STATUS_WORDS = {
  'Open': 'Waiting for the team',
  'In Progress': 'Being fixed',
  'Resolved': 'Fixed',
  'Cancelled': 'Cancelled',
};
const TRACK = [
  { status: 'Open', label: 'Reported' },
  { status: 'In Progress', label: 'Being fixed' },
  { status: 'Resolved', label: 'Fixed' },
];
const TEAM_ICONS = { maintenance: 'fa-screwdriver-wrench', housekeeping: 'fa-broom' };

/* Mirrors HotelComplaint::isForwardTransition() — status only moves forward here
   too, so a button that is not offered matches what the server would refuse anyway. */
function canMoveComplaintTo(from, to) {
  if (from === to || from === 'Resolved' || from === 'Cancelled') return false;
  if (to === 'Cancelled') return true;
  const fromAt = COMPLAINT_FLOW.indexOf(from);
  const toAt = COMPLAINT_FLOW.indexOf(to);
  return fromAt !== -1 && toAt !== -1 && toAt > fromAt;
}

function slug(value) {
  return String(value || '').trim().replace(/\s+/g, '-');
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

function ComplaintForm({ rooms, categories, onSubmit, busy }) {
  const categoryNames = Object.keys(categories);
  const [roomNumber, setRoomNumber] = useState('');
  const [guestName, setGuestName] = useState('');
  const [category, setCategory] = useState('');
  // Touched once the staffer overrides the category's suggestion, after which
  // changing the category must stop moving the department under them.
  const [departmentTouched, setDepartmentTouched] = useState(false);
  const [department, setDepartment] = useState('maintenance');
  const [details, setDetails] = useState('');
  const [error, setError] = useState('');

  /* Only rooms with a guest actually in them. A complaint comes from someone staying
     in the room, so a vacant or merely-booked room is not a room to file against. */
  const occupiedRooms = (rooms || []).filter(
    r => r.reservation && r.reservation.status === 'Checked In'
  );

  const suggested = category ? (categories[category] || 'maintenance') : null;

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
    if (!category) { setError('Pick what kind of problem it is.'); return; }
    if (!details.trim()) { setError('Write down what the guest said, so the team knows what to bring.'); return; }
    setError('');
    onSubmit({
      room_number: roomNumber.trim(),
      guest_name: guestName.trim(),
      category,
      department,
      details: details.trim(),
    }, () => {
      setDetails('');
      setCategory('');
      setDepartmentTouched(false);
    });
  };

  // Problem types, grouped by the team they normally go to.
  const groups = Object.keys(DEPARTMENT_LABELS).map(key => ({
    key,
    label: DEPARTMENT_LABELS[key],
    names: categoryNames.filter(n => categories[n] === key),
  })).filter(g => g.names.length > 0);

  return (
    <form onSubmit={submit} className="cx-panel" noValidate>
      <h2 className="cx-panel-title">Report a problem</h2>

      <div className="cx-steps">
        <div className={'cx-step' + (roomNumber ? ' is-done' : '')}>
          <div className="cx-step-head"><span className="cx-num">1</span><label htmlFor="cxRoom">Which room?</label></div>
          {/* Always a dropdown. With nobody checked in there is no room to complain
              from, so it disables and says so rather than turning into a text box. */}
          <div className="cx-two">
            <select id="cxRoom" className="cx-input" value={roomNumber} onChange={e => pickRoom(e.target.value)} disabled={occupiedRooms.length === 0}>
              <option value="">{occupiedRooms.length === 0 ? 'No guests are checked in' : 'Choose a room…'}</option>
              {occupiedRooms.map(room => (
                <option key={room.id} value={room.name}>Room {room.name} · {room.reservation.fullName || 'Guest'}</option>
              ))}
            </select>
            <input type="text" className="cx-input" placeholder="Guest name" value={guestName} onChange={e => setGuestName(e.target.value)} aria-label="Guest name (filled in from the room)" title="Filled in from the room" />
          </div>
          {occupiedRooms.length === 0 && (
            <p className="cx-help">Only rooms with a checked-in guest are listed. Check a guest in first.</p>
          )}
        </div>

        <div className={'cx-step' + (category ? ' is-done' : '')}>
          <div className="cx-step-head"><span className="cx-num">2</span><span className="cx-q">What kind of problem?</span></div>
          {groups.map(group => (
            <div key={group.key} style={{ marginBottom: '0.35rem' }}>
              <p className="cx-group-label">Usually {group.label}</p>
              <div className="cx-chips">
                {group.names.map(name => (
                  <button key={name} type="button" className={'cx-chip' + (category === name ? ' is-on' : '')} aria-pressed={category === name} onClick={() => pickCategory(name)}>
                    {name}
                  </button>
                ))}
              </div>
            </div>
          ))}
        </div>

        <div className="cx-step is-done">
          <div className="cx-step-head"><span className="cx-num">3</span><span className="cx-q">Which team should fix it?</span></div>
          <div className="cx-teams">
            {Object.entries(DEPARTMENT_LABELS).map(([key, label]) => (
              <button key={key} type="button" className={'cx-team' + (department === key ? ' is-on' : '')} aria-pressed={department === key}
                onClick={() => { setDepartment(key); setDepartmentTouched(true); }}>
                <i className={'fa-solid ' + (TEAM_ICONS[key] || 'fa-users')}></i>
                <span>
                  {label}
                  {suggested === key && <small>Suggested</small>}
                </span>
              </button>
            ))}
          </div>
        </div>

        <div className={'cx-step' + (details.trim() ? ' is-done' : '')}>
          <div className="cx-step-head"><span className="cx-num">4</span><label htmlFor="cxDetails">What did the guest say?</label></div>
          <textarea id="cxDetails" className="cx-input" rows={3}
            placeholder="e.g. The air conditioner is not cooling. The guest called at 9 PM."
            value={details} onChange={e => setDetails(e.target.value)} />
        </div>

        {error && <p className="cx-error" role="alert">{error}</p>}

        <button type="submit" className="cx-btn cx-btn-primary cx-btn-wide" disabled={busy || occupiedRooms.length === 0}>
          <i className="fa-solid fa-paper-plane"></i>
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
  const stepAt = TRACK.findIndex(t => t.status === complaint.status);

  useEffect(() => { setNote(complaint.resolutionNote || ''); }, [complaint.resolutionNote]);

  const cancel = () => {
    if (window.confirm(`Cancel the complaint for Room ${complaint.roomNumber}? The team will stop working on it.`)) {
      onUpdate(complaint.id, { status: 'Cancelled' });
    }
  };

  return (
    <article className="cx-card">
      <div className="cx-card-top">
        <div style={{ minWidth: 0 }}>
          <span className="cx-room">Room {complaint.roomNumber}</span>
          <span className="cx-guest">{complaint.guestName || 'Guest name not given'} · {complaint.category}</span>
        </div>
        <div className="cx-pills">
          <span className={`cx-pill is-${slug(complaint.status)}`}><span className="dot"></span>{STATUS_WORDS[complaint.status] || complaint.status}</span>
          <span className="cx-pill"><i className={'fa-solid ' + (TEAM_ICONS[complaint.department] || 'fa-users')}></i>{complaint.departmentLabel}</span>
        </div>
      </div>

      <div className="cx-what">
        <small>What the guest said</small>
        <p>{complaint.details}</p>
      </div>

      {stepAt !== -1 && (
        <ol className="cx-track" aria-label={`Progress: ${STATUS_WORDS[complaint.status]}`}>
          {TRACK.map((t, i) => (
            <li key={t.status} className={i < stepAt || complaint.status === 'Resolved' ? 'is-done' : i === stepAt ? 'is-now' : ''}>
              <span className="cx-dot"></span>{t.label}
            </li>
          ))}
        </ol>
      )}

      <p className="cx-meta">
        Reported {formatWhen(complaint.filedAt)} by {complaint.filedBy || 'Front Desk'}
        {complaint.handledBy ? ` · Handled by ${complaint.handledBy}` : ''}
        {complaint.resolvedAt ? ` · Closed ${formatWhen(complaint.resolvedAt)}` : ''}
      </p>

      {complaint.resolutionNote && (
        <div className="cx-fix">
          <small>What was done</small>
          <span>{complaint.resolutionNote}</span>
        </div>
      )}

      {canHandle && !isClosed && (
        <div className="cx-actions">
          {complaint.status === 'Open' && (
            <button type="button" className="cx-btn cx-btn-primary" onClick={() => onUpdate(complaint.id, { status: 'In Progress' })}>
              <i className="fa-solid fa-person-digging"></i> Start working on it
            </button>
          )}
          {canMoveComplaintTo(complaint.status, 'Resolved') && (
            <button type="button" className={'cx-btn ' + (complaint.status === 'In Progress' ? 'cx-btn-primary' : 'cx-btn-ghost')}
              onClick={() => onUpdate(complaint.id, { status: 'Resolved' })}>
              <i className="fa-solid fa-circle-check"></i> Mark as fixed
            </button>
          )}
          <span className="cx-spacer"></span>
          <button type="button" className="cx-btn-sm" onClick={() => setNoteOpen(v => !v)}>
            <i className="fa-solid fa-pen"></i>{complaint.resolutionNote ? 'Edit what was done' : 'Write what was done'}
          </button>
          {/* A closed complaint cannot be handed over — that would reopen it, the
              same backward move the status flow forbids. */}
          <button type="button" className="cx-btn-sm" onClick={() => onUpdate(complaint.id, { department: otherDepartment })}>
            <i className="fa-solid fa-right-left"></i>Give to {DEPARTMENT_LABELS[otherDepartment]}
          </button>
          <button type="button" className="cx-btn-sm is-danger" onClick={cancel}>
            <i className="fa-solid fa-xmark"></i>Cancel
          </button>
        </div>
      )}

      {canHandle && isClosed && (
        <div className="cx-actions">
          <button type="button" className="cx-btn-sm" onClick={() => setNoteOpen(v => !v)}>
            <i className="fa-solid fa-pen"></i>{complaint.resolutionNote ? 'Edit what was done' : 'Write what was done'}
          </button>
        </div>
      )}

      {!canHandle && canCancel && canMoveComplaintTo(complaint.status, 'Cancelled') && (
        <div className="cx-actions">
          <span className="cx-meta">The {complaint.departmentLabel} team updates this as they work on it.</span>
          <span className="cx-spacer"></span>
          <button type="button" className="cx-btn-sm is-danger" onClick={cancel}>
            <i className="fa-solid fa-xmark"></i>Cancel complaint
          </button>
        </div>
      )}

      {canHandle && noteOpen && (
        <div className="cx-note-form">
          <input type="text" className="cx-input" placeholder="What was done to fix it?" value={note} onChange={e => setNote(e.target.value)} aria-label="What was done to fix it" />
          <button type="button" className="cx-btn cx-btn-primary" onClick={() => { onUpdate(complaint.id, { resolution_note: note }); setNoteOpen(false); }}>
            Save
          </button>
        </div>
      )}
    </article>
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
          if (patch.department) window.toast && window.toast(`Given to ${data.complaint.departmentLabel}`);
          else if (patch.status) window.toast && window.toast(`Marked as ${(STATUS_WORDS[data.complaint.status] || data.complaint.status).toLowerCase()}`);
          else window.toast && window.toast('Saved');
        }
      })
      .catch(e => fail(e.message))
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  };

  const inDepartment = c => departmentFilter === 'all' || c.department === departmentFilter;
  const STATUS_TABS = [
    { key: 'open',   label: 'Not fixed yet',  icon: 'fa-hourglass-half', match: c => OPEN_STATUSES.includes(c.status) },
    { key: 'closed', label: 'Done',            icon: 'fa-circle-check',  match: c => !OPEN_STATUSES.includes(c.status) },
    { key: 'all',    label: 'All complaints',  icon: 'fa-list',          match: () => true },
  ];
  const TEAM_TABS = [['all', 'All teams'], ...Object.entries(DEPARTMENT_LABELS)];

  const visible = useMemo(() => {
    const q = search.trim().toLowerCase();
    const tab = STATUS_TABS.find(t => t.key === statusFilter) || STATUS_TABS[0];
    return complaints.filter(c => {
      if (!inDepartment(c) || !tab.match(c)) return false;
      if (!q) return true;
      return [c.roomNumber, c.guestName, c.category, c.details, c.filedBy]
        .some(field => String(field || '').toLowerCase().includes(q));
    });
  }, [complaints, departmentFilter, statusFilter, search]);

  const openCount = complaints.filter(c => OPEN_STATUSES.includes(c.status) && inDepartment(c)).length;

  const isDepartmentView = !!DEPARTMENT_LABELS[CFG.role];
  const eyebrow = isDepartmentView ? DEPARTMENT_LABELS[CFG.role] : 'Front Desk';
  const heading = isDepartmentView ? 'Complaints & Concerns' : 'Guest Complaints';
  const lead = isDepartmentView
    ? 'Problems guests reported at the Front Desk that need your team. Start on each one, mark it fixed when it is done, and write down what you did.'
    : 'Write down a guest’s problem, send it to the team that can fix it, and follow it until it is fixed.';

  let emptyTitle = 'Nothing matches';
  let emptyText = 'Try another tab, another team, or clear the search.';
  if (complaints.length === 0) {
    emptyTitle = 'No complaints yet';
    emptyText = canFile
      ? 'When a guest reports a problem, use the form to send it to the right team.'
      : 'Nothing has been sent to your team yet. New complaints will show up here.';
  } else if (statusFilter === 'open' && !search.trim()) {
    emptyTitle = 'Nothing left to fix';
    emptyText = 'Every complaint here is done. You can see them under Done.';
  }

  const list = (
    <section className="cx-list-col" style={{ minWidth: 0 }}>
      <div className="cx-toolbar">
        <div className="cx-toolbar-row">
          <div className="cx-tabs" role="group" aria-label="Show complaints by status">
            {STATUS_TABS.map(t => (
              <button key={t.key} type="button" className={'cx-tab' + (statusFilter === t.key ? ' is-on' : '')} aria-pressed={statusFilter === t.key} onClick={() => setStatusFilter(t.key)}>
                <i className={'fa-solid ' + t.icon}></i>{t.label}
                <span className="cx-count">{complaints.filter(c => inDepartment(c) && t.match(c)).length}</span>
              </button>
            ))}
          </div>
          <div className="cx-tabs is-small" role="group" aria-label="Show complaints by team">
            {TEAM_TABS.map(([key, label]) => (
              <button key={key} type="button" className={'cx-tab' + (departmentFilter === key ? ' is-on' : '')} aria-pressed={departmentFilter === key} onClick={() => setDepartmentFilter(key)}>
                {key !== 'all' && <i className={'fa-solid ' + (TEAM_ICONS[key] || 'fa-users')}></i>}{label}
              </button>
            ))}
          </div>
        </div>
        <div className="cx-search">
          <i className="fa-solid fa-magnifying-glass"></i>
          <input type="text" className="cx-input" placeholder="Search by room, guest, problem or what was said…" value={search} onChange={e => setSearch(e.target.value)} aria-label="Search complaints" />
        </div>
      </div>

      <div className="cx-scroll">
      {!loaded ? (
        <div className="cx-empty"><p style={{ margin: 0 }}>Loading complaints…</p></div>
      ) : visible.length === 0 ? (
        <div className="cx-empty">
          <div className="cx-empty-icon"><i className="fa-solid fa-clipboard-check"></i></div>
          <h2>{emptyTitle}</h2>
          <p>{emptyText}</p>
        </div>
      ) : (
        <div className="cx-list">
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
    </section>
  );

  return (
    <div className="cx" data-hms-no-edit="1">
      <header className="cx-head">
        <div>
          <p className="cx-eyebrow">{eyebrow}</p>
          <h1 className="font-display">{heading}</h1>
          <p className="cx-lead">{lead}</p>
          <p className="cx-lead" style={{ marginTop: '0.35rem', color: 'var(--fg)', fontWeight: 600 }}>
            {openCount === 0 ? 'Nothing is waiting right now.' : `${openCount} ${openCount === 1 ? 'complaint is' : 'complaints are'} not fixed yet.`}
          </p>
        </div>
        <a href={CFG.backUrl} className="cx-btn cx-btn-ghost">
          <i className="fa-solid fa-arrow-left"></i> Back
        </a>
      </header>

      <div className={'cx-layout' + (canFile ? '' : ' is-single')}>
        {canFile && <ComplaintForm rooms={rooms} categories={categories} onSubmit={fileComplaint} busy={busy} />}
        {list}
      </div>
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
