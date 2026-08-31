@extends('students.builder.ops-shell')

@php $backRoute = 'students.frontdesk'; @endphp

@section('page-title', 'Amenities')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
    --danger: #fb7185; --success: #4ade80; --warn: #fbbf24;
  }
  #opsContentWrap { font-family: 'Outfit', sans-serif; }
  .font-display { font-family: 'Playfair Display', serif; }

  .am-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(330px, 1fr)); gap: 1.25rem; }
  .am-card {
    display: flex; flex-direction: column;
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    overflow: hidden;
  }
  .am-media { position: relative; height: 150px; flex: 0 0 150px; overflow: hidden; }
  .am-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .am-card.is-shut .am-media img { filter: grayscale(0.7); }
  .am-body { flex: 1 1 auto; padding: 1rem 1.15rem 1.15rem; display: flex; flex-direction: column; gap: 0.55rem; }
  .am-meta { display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; color: var(--fg-muted); }

  .am-badge {
    padding: 0.22rem 0.65rem; border-radius: 4px;
    font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; white-space: nowrap; display: inline-block;
  }
  .am-badge.is-available   { background: rgba(74,222,128,0.16); color: var(--success); border-color: rgba(74,222,128,0.35); }
  .am-badge.is-closed      { background: rgba(251,191,36,0.14); color: var(--warn);    border-color: rgba(251,191,36,0.35); }
  .am-badge.is-maintenance { background: rgba(244,63,94,0.14);  color: var(--danger);  border-color: rgba(244,63,94,0.35); }
  .am-status-pin { position: absolute; top: 0.7rem; left: 0.7rem; }
  /* The access type is what the desk reads first — it decides which buttons exist. */
  .am-kind {
    position: absolute; top: 0.7rem; right: 0.7rem;
    padding: 0.22rem 0.6rem; border-radius: 4px;
    background: rgba(12,11,9,0.82); color: var(--accent);
    border: 1px solid rgba(201,168,76,0.3);
    font-size: 0.6rem; letter-spacing: 0.1em; text-transform: uppercase; font-weight: 600;
  }
  /* Open now vs shut for the night — separate from the amenity's own status, because a
     pool can be Available and still closed at midnight. */
  .am-now { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.72rem; }
  .am-now.is-on  { color: var(--success); }
  .am-now.is-off { color: var(--fg-muted); }
  .am-now .dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; display: inline-block; }

  .am-action { margin-top: auto; padding-top: 0.7rem; border-top: 1px solid var(--border); }
  .am-note { font-size: 0.74rem; color: var(--fg-muted); line-height: 1.5; margin: 0; }
  .am-inside { display: flex; flex-direction: column; gap: 0.4rem; margin-top: 0.6rem; }
  .am-inside-row {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.5rem 0.6rem; border-radius: 8px;
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    font-size: 0.75rem;
  }
  .am-inside-row .who { color: var(--fg); font-weight: 600; }
  .am-cap {
    font-size: 0.68rem; color: var(--fg-muted);
    letter-spacing: 0.06em; text-transform: uppercase;
  }

  .btn-primary {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: var(--accent); color: var(--bg);
    font-family: 'Outfit', sans-serif; font-weight: 600;
    font-size: 0.8rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.8rem 1.8rem; border: none; border-radius: 6px;
    cursor: pointer; transition: background 0.2s, transform 0.2s;
  }
  .btn-primary:hover:not(:disabled) { background: var(--accent-light); transform: translateY(-1px); }
  .btn-primary:disabled { opacity: 0.5; cursor: default; }
  .btn-outline {
    display: inline-flex; align-items: center; gap: 0.45rem;
    background: transparent; color: var(--accent);
    font-family: 'Outfit', sans-serif; font-weight: 500;
    font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.5rem 1rem; border: 1px solid var(--accent); border-radius: 6px;
    cursor: pointer; transition: background 0.2s, color 0.2s;
  }
  .btn-outline:hover:not(:disabled) { background: var(--accent); color: var(--bg); }
  .btn-outline:disabled { opacity: 0.45; cursor: default; }
  .btn-outline.is-exit { color: var(--fg-muted); border-color: var(--border); margin-left: auto; }
  .btn-outline.is-exit:hover:not(:disabled) { background: var(--fg-muted); color: var(--bg); }

  .booking-input {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: 'Outfit', sans-serif; font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus { border-color: var(--accent); }
  textarea.booking-input { resize: vertical; min-height: 70px; line-height: 1.5; }
  select.booking-input { appearance: none; cursor: pointer; }
  select.booking-input option { background: var(--card); color: var(--fg); }

  .rm-form-grid { display: grid; gap: 0.95rem; }
  .rm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }
  @media (max-width: 640px) { .rm-form-row { grid-template-columns: 1fr; } }

  .room-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 200;
    display: flex; align-items: center; justify-content: center; padding: 1.5rem;
  }
  .room-modal {
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    width: 100%; max-width: 460px; max-height: 90vh; overflow-y: auto;
  }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Additive only — nothing above this block is touched, so a Template 1
     team (or one that hasn't chosen a template yet) renders unchanged. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --danger: #e11d48; --success: #15803d; --warn: #b45309;
  }
  :root[data-ops-theme="2"] #opsContentWrap { font-family: 'DM Sans', sans-serif; }
  :root[data-ops-theme="2"] .font-display { font-family: 'Cormorant Garamond', serif; }
  :root[data-ops-theme="2"] select.booking-input { color-scheme: light; }
  :root[data-ops-theme="2"] .booking-input { background: rgba(27,67,50,0.03); }
  :root[data-ops-theme="2"] .am-badge.is-available   { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
  :root[data-ops-theme="2"] .am-badge.is-closed      { background: #fef3c7; color: #b45309; border-color: #fde68a; }
  :root[data-ops-theme="2"] .am-badge.is-maintenance { background: #ffe4e6; color: #be123c; border-color: #fecdd3; }
  :root[data-ops-theme="2"] .am-kind { background: rgba(255,255,255,0.92); border-color: rgba(27,67,50,0.2); }
  :root[data-ops-theme="2"] .am-inside-row { background: #faf8f5; }
  :root[data-ops-theme="2"] .am-card { box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_FD_AMENITIES = {
    backUrl: @json(route($backRoute)),
    amenitiesUrl: @json(route('students.hotel.amenities.index')),
    visitsUrl: @json(route('students.hotel.amenity-visits.index')),
    visitStoreUrl: @json(route('students.hotel.amenity-visits.store')),
    guestsUrl: @json(route('students.hotel.amenity-guests')),
    accessLabels: @json(\App\Models\HotelAmenity::ACCESS_LABELS),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef } = React;

const CONFIG = window.HMS_FD_AMENITIES || {};
const ACCESS_LABELS = CONFIG.accessLabels || {};

function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function amenityImg(amenity) {
  if (amenity && amenity.img) return amenity.img;
  const seed = encodeURIComponent((amenity && (amenity.id || amenity.name)) || 'amenity');
  return 'https://picsum.photos/seed/amenity-' + seed + '/800/600.jpg';
}

function statusClass(status) {
  if (status === 'Available') return 'is-available';
  if (status === 'Temporarily Closed') return 'is-closed';
  return 'is-maintenance';
}

/* A ticking clock, so the "in the pool for 34m" figures move without a refetch.
   Same helper the Guest Information screen uses for its stay countdown. */
function useNow(intervalMs) {
  const [now, setNow] = useState(() => Date.now());
  useEffect(() => {
    const id = setInterval(() => setNow(Date.now()), intervalMs);
    return () => clearInterval(id);
  }, [intervalMs]);
  return now;
}

function sinceLabel(iso, now) {
  if (!iso) return '';
  const mins = Math.max(0, Math.floor((now - new Date(iso).getTime()) / 60000));
  if (mins < 60) return mins + 'm';
  return Math.floor(mins / 60) + 'h ' + (mins % 60) + 'm';
}

function swal(icon, title, text) {
  if (!window.Swal) return;
  const dark = document.documentElement.getAttribute('data-ops-theme') !== '2';
  window.Swal.fire({
    icon, title, text,
    background: dark ? '#181714' : '#ffffff',
    color: dark ? '#f5f0e8' : '#1a1a1a',
    iconColor: icon === 'success' ? '#4ade80' : '#fb7185',
    confirmButtonColor: dark ? '#c9a84c' : '#1b4332',
    confirmButtonText: icon === 'success' ? 'Great!' : 'OK',
    timer: icon === 'success' ? 3000 : undefined,
    timerProgressBar: icon === 'success',
  });
}

const fieldLabel = {
  fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase',
  color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem',
};

/* Signing a guest in. The guest list is everyone currently checked in — a Booked stay
   holds a room but its guest is not here yet, so the server does not offer it. */
function RegisterEntryModal({ amenity, guests, onClose, onRegistered }) {
  const [bookingId, setBookingId] = useState('');
  const [partySize, setPartySize] = useState('1');
  const [notes, setNotes] = useState('');
  const [error, setError] = useState(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!bookingId) { setError('Choose which guest is going in.'); return; }

    setSaving(true);
    fetch(CONFIG.visitStoreUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({
        hotel_amenity_id: amenity.dbId,
        hotel_booking_id: Number(bookingId),
        party_size: Math.max(1, parseInt(partySize, 10) || 1),
        notes: notes.trim(),
      }),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        if (data.visit) onRegistered(data.visit);
        onClose();
        swal('success', 'Registered', data.visit.guestName + ' is now in the ' + amenity.name + '.');
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Could not register that guest.';
        if (window.Swal) swal('error', 'Cannot register', msg); else setError(msg);
      })
      .finally(() => setSaving(false));
  };

  return (
    <div className="room-modal-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" onClick={e => e.stopPropagation()}>
        <div style={{ padding: '1.5rem' }}>
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.35rem' }}>
            Register Entry
          </p>
          <h2 className="font-display" style={{ fontSize: '1.5rem', margin: '0 0 1.1rem', color: 'var(--fg)' }}>
            {amenity.name}
          </h2>

          <form onSubmit={handleSubmit} className="rm-form-grid" noValidate>
            <div>
              <label style={fieldLabel}>Guest *</label>
              <select
                className="booking-input" value={bookingId}
                onChange={e => { setBookingId(e.target.value); if (error) setError(null); }}
                style={error ? { borderColor: '#f43f5e' } : undefined}
              >
                <option value="">Select a checked-in guest…</option>
                {guests.map(g => (
                  <option key={g.bookingId} value={g.bookingId}>
                    {g.guestName}{g.roomName ? ' · Room ' + g.roomName : ''}
                  </option>
                ))}
              </select>
              {error && <p style={{ margin: '0.35rem 0 0', color: 'var(--danger)', fontSize: '0.72rem' }}>{error}</p>}
              {guests.length === 0 && (
                <p style={{ margin: '0.35rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem' }}>
                  Nobody is checked in right now.
                </p>
              )}
            </div>

            <div className="rm-form-row">
              <div>
                <label style={fieldLabel}>People</label>
                <input
                  type="number" min="1" max="999" className="booking-input" value={partySize}
                  onChange={e => setPartySize(e.target.value)}
                />
              </div>
              <div>
                <label style={fieldLabel}>Capacity</label>
                <input
                  className="booking-input" readOnly
                  value={amenity.capacity === null ? 'No limit' : String(amenity.capacity)}
                  style={{ opacity: 0.6 }}
                />
              </div>
            </div>
            <p style={{ margin: '-0.35rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem', lineHeight: 1.5 }}>
              One entry covers the whole party — sign them all in together, and sign them out
              together when they leave.
            </p>

            <div>
              <label style={fieldLabel}>Notes</label>
              <textarea
                className="booking-input" value={notes}
                placeholder="Two children with them, towels issued."
                onChange={e => setNotes(e.target.value)}
              />
            </div>

            <button type="submit" className="btn-primary" disabled={saving} style={{ justifyContent: 'center' }}>
              {saving ? 'Registering…' : 'Register Entry'}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

/* The action area is the whole point of this screen: it is different for each kind of
   facility, driven by accessType rather than by the amenity's name. */
function AmenityActions({ amenity, visits, guests, canRegister, now, onOpenEntry, onExit, exitingId }) {
  const kind = amenity.accessType || 'open';

  if (kind === 'open') {
    return (
      <div className="am-action">
        <p className="am-note">
          <i className="fa-solid fa-door-open" style={{ color: 'var(--accent)', marginRight: '0.4rem' }}></i>
          Open access — guests walk in during opening hours. Nothing to register.
        </p>
      </div>
    );
  }

  if (kind === 'registered') {
    const inside = visits.filter(v => v.amenityId === amenity.dbId);
    const heads = inside.reduce((sum, v) => sum + (v.partySize || 1), 0);
    const full = amenity.capacity !== null && heads >= amenity.capacity;
    const blocked = amenity.status !== 'Available' || !amenity.isOpenNow;

    return (
      <div className="am-action">
        <div style={{ display: 'flex', alignItems: 'center', gap: '0.6rem', flexWrap: 'wrap' }}>
          <button
            type="button" className="btn-outline"
            disabled={!canRegister || blocked || full}
            onClick={() => onOpenEntry(amenity)}
            title={blocked ? 'The facility is not open right now.' : (full ? 'At capacity.' : undefined)}
          >
            <i className="fa-solid fa-right-to-bracket" style={{ fontSize: '0.65rem' }}></i> Register Entry
          </button>
          <span className="am-cap">
            {heads} inside{amenity.capacity !== null ? ' / ' + amenity.capacity : ''}
          </span>
        </div>

        {inside.length > 0 && (
          <div className="am-inside">
            {inside.map(v => (
              <div key={v.id} className="am-inside-row">
                <span className="who">{v.guestName}</span>
                <span style={{ color: 'var(--fg-muted)' }}>
                  {v.roomName ? 'Room ' + v.roomName + ' · ' : ''}
                  {v.partySize > 1 ? v.partySize + ' people · ' : ''}
                  {sinceLabel(v.enteredAt, now)}
                </span>
                {canRegister && (
                  <button
                    type="button" className="btn-outline is-exit"
                    disabled={exitingId === v.id}
                    onClick={() => onExit(v)}
                  >
                    {exitingId === v.id ? 'Signing out…' : 'Exit'}
                  </button>
                )}
              </div>
            ))}
          </div>
        )}
      </div>
    );
  }

  // appointment / event — Phase 2 and Phase 3 fill these in. Said plainly rather than
  // shown as a dead button, so the desk knows the booking is not takeable here yet.
  return (
    <div className="am-action">
      <p className="am-note">
        <i className="fa-solid fa-calendar-day" style={{ color: 'var(--accent)', marginRight: '0.4rem' }}></i>
        {ACCESS_LABELS[kind] || 'By arrangement'} — booking for this facility is not available
        on this screen yet.
        {kind === 'event' && amenity.rate > 0 && (
          <span> Rate ₱{Number(amenity.rate).toLocaleString()} per event.</span>
        )}
      </p>
    </div>
  );
}

function AmenityCard(props) {
  const { amenity, now } = props;
  const shut = amenity.status !== 'Available' || !amenity.isOpenNow;

  return (
    <div className={'am-card' + (shut ? ' is-shut' : '')}>
      <div className="am-media">
        <img src={amenityImg(amenity)} alt={amenity.name} />
        <span className={'am-badge am-status-pin ' + statusClass(amenity.status)}>{amenity.status}</span>
        <span className="am-kind">{amenity.accessLabel}</span>
      </div>
      <div className="am-body">
        <h3 className="font-display" style={{ fontSize: '1.2rem', fontWeight: 700, margin: 0, color: 'var(--fg)' }}>
          {amenity.name}
        </h3>
        {amenity.location && (
          <div className="am-meta">
            <i className="fa-solid fa-location-dot" style={{ color: 'var(--accent)', fontSize: '0.7rem' }}></i>
            {amenity.location}
          </div>
        )}
        <div className="am-meta" style={{ justifyContent: 'space-between' }}>
          <span>
            <i className="fa-solid fa-clock" style={{ color: 'var(--accent)', fontSize: '0.7rem', marginRight: '0.4rem' }}></i>
            {amenity.hours || 'No posted hours'}
          </span>
          {/* Separate from status on purpose: a pool can be Available and still shut at 3am. */}
          <span className={'am-now ' + (amenity.isOpenNow ? 'is-on' : 'is-off')}>
            <span className="dot"></span>{amenity.isOpenNow ? 'Open now' : 'Closed now'}
          </span>
        </div>
        <AmenityActions {...props} now={now} />
      </div>
    </div>
  );
}

function App() {
  const [amenities, setAmenities] = useState([]);
  const [visits, setVisits] = useState([]);
  const [guests, setGuests] = useState([]);
  const [canRegister, setCanRegister] = useState(false);
  const [loading, setLoading] = useState(true);
  const [entryFor, setEntryFor] = useState(null);
  const [exitingId, setExitingId] = useState(null);
  const now = useNow(30000);

  // A poll landing mid-save would put a guest back in the pool they were just signed
  // out of. Fetches stand down while a write is in flight.
  const pendingWrites = useRef(0);

  const refresh = useCallback(() => {
    if (pendingWrites.current > 0) return;
    const opts = { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };

    fetch(CONFIG.amenitiesUrl, opts)
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => { if (pendingWrites.current === 0) setAmenities(d.items || []); })
      .catch(() => {})
      .finally(() => setLoading(false));

    fetch(CONFIG.visitsUrl + '?inside=1', opts)
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => {
        if (pendingWrites.current > 0) return;
        setVisits(d.visits || []);
        setCanRegister(!!d.can_register);
      })
      .catch(() => {});

    fetch(CONFIG.guestsUrl, opts)
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => { if (pendingWrites.current === 0) setGuests(d.guests || []); })
      .catch(() => {});
  }, []);

  // Polled, not loaded once: Room Management checks a guest in from their own session,
  // and this screen's guest list has to notice without anyone reloading the page.
  useEffect(() => {
    refresh();
    const timer = setInterval(refresh, 8000);
    const onFocus = () => refresh();
    window.addEventListener('focus', onFocus);
    return () => { clearInterval(timer); window.removeEventListener('focus', onFocus); };
  }, [refresh]);

  const handleRegistered = useCallback((visit) => {
    setVisits(prev => prev.concat([visit]));
  }, []);

  const handleExit = useCallback((visit) => {
    setExitingId(visit.id);
    pendingWrites.current += 1;
    fetch(CONFIG.visitStoreUrl + '/' + visit.id + '/exit', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(() => {
        setVisits(prev => prev.filter(v => v.id !== visit.id));
        swal('success', 'Signed out', visit.guestName + ' has left the ' + visit.amenityName + '.');
      })
      .catch(err => swal('error', 'Error', (err && err.message) ? err.message : 'Could not sign that guest out.'))
      .finally(() => {
        setExitingId(null);
        pendingWrites.current = Math.max(0, pendingWrites.current - 1);
      });
  }, []);

  return (
    <div style={{ padding: '1.5rem' }} data-hms-no-edit="1">
      <div style={{ marginBottom: '1.25rem' }}>
        <h3 className="font-display" style={{ fontSize: '1.35rem', fontWeight: 700, margin: '0 0 0.35rem', color: 'var(--fg)' }}>
          Amenities
        </h3>
        <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', margin: 0, lineHeight: 1.5, maxWidth: 620 }}>
          What each facility needs from the desk depends on the facility. Housekeeping sets that
          up and keeps the hours and condition current; you register guests in and out.
        </p>
        {!loading && !canRegister && (
          <p style={{ color: 'var(--warn)', fontSize: '0.76rem', marginTop: '0.6rem' }}>
            <i className="fa-solid fa-eye" style={{ marginRight: '0.4rem' }}></i>
            You are viewing this read-only — registering guests is Front Desk work.
          </p>
        )}
      </div>

      {loading ? (
        <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem' }}>Loading amenities…</p>
      ) : amenities.length === 0 ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem 1rem', textAlign: 'center', color: 'var(--fg-muted)' }}>
          <i className="fa-solid fa-person-swimming" style={{ fontSize: '1.6rem', opacity: 0.5, display: 'block', marginBottom: '0.6rem' }}></i>
          <p style={{ margin: 0, fontSize: '0.82rem' }}>No amenities yet.</p>
        </div>
      ) : (
        <div className="am-grid">
          {amenities.map(amenity => (
            <AmenityCard
              key={amenity.id}
              amenity={amenity}
              visits={visits}
              guests={guests}
              canRegister={canRegister}
              now={now}
              exitingId={exitingId}
              onOpenEntry={setEntryFor}
              onExit={handleExit}
            />
          ))}
        </div>
      )}

      {entryFor && (
        <RegisterEntryModal
          amenity={entryFor}
          guests={guests}
          onClose={() => setEntryFor(null)}
          onRegistered={handleRegistered}
        />
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
