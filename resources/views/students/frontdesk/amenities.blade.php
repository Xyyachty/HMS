@extends('students.builder.ops-shell')

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

  /* Page, toolbar and cards read the shell's tokens, the same way Walk-in Guests and
     Room Service do, so the screen follows Template 1, Template 2 and a team's own
     site colours. Tints are mixed from those tokens rather than hard-coded.
     Shape rule: pills for tabs and status, 10px for buttons, 14px for cards. */
  .af {
    --af-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --af-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --af-line: var(--border);
    padding: 1.5rem 1.5rem 3rem;
    color: var(--fg);
  }

  /* Page header */
  .af-head { margin-bottom: 1.4rem; }
  .af-eyebrow { color: var(--accent); font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; margin: 0 0 0.5rem; }
  .af-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .af-lead { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 64ch; line-height: 1.5; }
  .af-readonly {
    display: flex; align-items: center; gap: 0.55rem; margin-top: 0.9rem; width: fit-content; max-width: 100%;
    padding: 0.6rem 0.85rem; border-radius: 10px; font-size: 0.82rem;
    background: color-mix(in srgb, var(--warn) 12%, transparent); color: var(--fg);
    border: 1px solid color-mix(in srgb, var(--warn) 35%, transparent);
  }
  .af-readonly i { color: var(--warn); }

  /* Tabs: what the desk does for a facility */
  .af-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1.2rem; }
  .af-tabs { box-sizing: border-box; max-width: 100%; display: inline-flex; flex-wrap: wrap; gap: 0.3rem; padding: 0.3rem; border-radius: 999px; background: var(--af-soft); border: 1px solid var(--af-line); }
  .af-tab {
    display: inline-flex; align-items: center; gap: 0.5rem;
    font: 600 0.86rem/1 'Outfit', sans-serif;
    padding: 0.62rem 1rem; border-radius: 999px; cursor: pointer;
    border: 0; background: transparent; color: var(--fg-muted);
    transition: background 0.15s, color 0.15s;
  }
  .af-tab:hover { color: var(--fg); }
  .af-tab.is-on { background: var(--accent); color: var(--bg); }
  .af-tab i { font-size: 0.8rem; }
  .af-count {
    min-width: 1.45rem; padding: 0.2rem 0.4rem; border-radius: 999px; text-align: center;
    font-size: 0.74rem; font-variant-numeric: tabular-nums;
    background: color-mix(in srgb, var(--fg) 8%, transparent);
  }
  .af-tab.is-on .af-count { background: color-mix(in srgb, var(--bg) 22%, transparent); }
  .af-summary { margin: 0; color: var(--fg-muted); font-size: 0.82rem; display: flex; align-items: center; gap: 0.45rem; }
  .af-summary b { color: var(--fg); font-variant-numeric: tabular-nums; }
  .af-summary i { color: var(--accent); }

  /* Cards */
  .af-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(320px, 100%), 1fr)); gap: 1.15rem; align-items: stretch; }
  /* Every card in a row is the same height, and its button sits on the bottom edge,
     so buttons and dividers line up across the row whatever each card holds. */
  .af-card { min-width: 0; height: 100%; display: flex; flex-direction: column; background: var(--card); border: 1px solid var(--af-line); border-radius: 14px; overflow: hidden; }
  .af-media { position: relative; flex: none; height: 160px; overflow: hidden; background: var(--af-soft); }
  .af-media img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .af-card.is-shut .af-media img { filter: grayscale(0.65); }
  /* One status on the photo, on a solid backing so it reads over any picture */
  .af-state {
    position: absolute; left: 0.75rem; bottom: 0.75rem;
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.38rem 0.7rem; border-radius: 999px;
    background: var(--card); color: var(--fg); font-size: 0.76rem; font-weight: 600;
    box-shadow: 0 2px 10px rgba(0,0,0,0.18);
  }
  .af-state .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--fg-muted); }
  .af-state.is-open .dot { background: var(--success); }
  .af-state.is-closed .dot { background: var(--warn); }
  .af-state.is-repair .dot { background: var(--danger); }

  .af-body { flex: 1; padding: 1rem 1.1rem 1.1rem; display: flex; flex-direction: column; gap: 0.75rem; }
  .af-title { margin: 0; font-size: 1.2rem; font-weight: 700; color: var(--fg); line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .af-does {
    display: inline-flex; align-items: center; gap: 0.45rem; width: fit-content;
    padding: 0.32rem 0.65rem; border-radius: 999px; font-size: 0.76rem; font-weight: 600;
    background: var(--af-tint); color: var(--accent); margin-top: 0.45rem;
  }
  .af-facts { display: grid; gap: 0.35rem; font-size: 0.84rem; color: var(--fg); }
  .af-facts div { display: flex; align-items: center; gap: 0.55rem; min-width: 0; }
  .af-facts i { width: 1rem; text-align: center; color: var(--fg-muted); font-size: 0.8rem; flex: none; }
  .af-facts span { min-width: 0; overflow-wrap: anywhere; }

  .af-action { flex: 1; display: flex; flex-direction: column; gap: 0.7rem; padding-top: 0.85rem; border-top: 1px solid var(--af-line); }
  .af-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem; width: 100%;
    font: 600 0.88rem/1 'Outfit', sans-serif;
    padding: 0.8rem 1rem; border-radius: 10px; cursor: pointer;
    background: var(--accent); color: var(--bg); border: 1px solid var(--accent);
    transition: filter 0.15s, transform 0.1s;
  }
  .af-action > .af-why { order: 9; margin-top: auto; }
  .af-action > .af-btn { order: 10; margin-top: auto; }
  .af-action > .af-why ~ .af-btn { margin-top: 0; }
  .af-btn:hover:not(:disabled) { filter: brightness(1.08); }
  .af-btn:active:not(:disabled) { transform: translateY(1px); }
  .af-btn:disabled { opacity: 0.45; cursor: not-allowed; }
  .af-btn-sm {
    flex: none; display: inline-flex; align-items: center; gap: 0.35rem;
    font: 600 0.78rem/1 'Outfit', sans-serif;
    padding: 0.5rem 0.75rem; border-radius: 8px; cursor: pointer;
    background: transparent; color: var(--fg); border: 1px solid var(--af-line);
  }
  .af-btn-sm:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
  .af-btn-sm:disabled { opacity: 0.5; cursor: progress; }
  .af-tab:focus-visible, .af-btn:focus-visible, .af-btn-sm:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
  .af-why { margin: 0; font-size: 0.78rem; color: var(--fg-muted); display: flex; gap: 0.45rem; align-items: flex-start; }
  .af-why i { color: var(--warn); margin-top: 0.12rem; }
  .af-note { margin: 0; font-size: 0.84rem; color: var(--fg-muted); line-height: 1.5; display: flex; gap: 0.55rem; align-items: flex-start; }
  .af-note i { color: var(--accent); margin-top: 0.18rem; }

  /* How full a sign-in facility is */
  .af-meter-head { display: flex; justify-content: space-between; align-items: baseline; font-size: 0.82rem; color: var(--fg-muted); }
  .af-meter-head b { color: var(--fg); font-size: 0.95rem; font-variant-numeric: tabular-nums; }
  .af-meter { height: 6px; border-radius: 999px; background: var(--af-soft); border: 1px solid var(--af-line); overflow: hidden; margin-top: 0.4rem; }
  .af-meter span { display: block; height: 100%; background: var(--accent); border-radius: 999px; }
  .af-meter.is-full span { background: var(--danger); }

  /* Who is inside / what is booked */
  .af-list { display: grid; gap: 0.45rem; }
  .af-list-title { margin: 0; font-size: 0.74rem; font-weight: 600; color: var(--fg-muted); letter-spacing: 0.04em; }
  .af-row {
    display: flex; align-items: center; gap: 0.65rem;
    padding: 0.6rem 0.7rem; border-radius: 10px;
    background: var(--af-soft); border: 1px solid var(--af-line);
  }
  .af-row-text { min-width: 0; flex: 1; }
  .af-row-text b { display: block; font-size: 0.86rem; color: var(--fg); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .af-row-text small { display: block; font-size: 0.75rem; color: var(--fg-muted); margin-top: 0.15rem; }
  .af-more { margin: 0; font-size: 0.78rem; color: var(--fg-muted); }

  /* Empty / loading */
  .af-empty { border: 1.5px dashed var(--af-line); border-radius: 14px; padding: 2.4rem 1.5rem; text-align: center; }
  .af-empty-icon {
    width: 56px; height: 56px; margin: 0 auto 0.9rem; border-radius: 16px;
    display: flex; align-items: center; justify-content: center; font-size: 1.35rem;
    background: var(--af-tint); color: var(--accent);
  }
  .af-empty h2 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .af-empty p { margin: 0.4rem auto 0; max-width: 46ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }

  @media (max-width: 560px) {
    .af { padding: 1.1rem 1rem 2.5rem; }
    .af-tabs { width: 100%; border-radius: 14px; }
  }

  /* Still used by the booking dialog */
  .am-badge {
    padding: 0.22rem 0.65rem; border-radius: 4px;
    font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; white-space: nowrap; display: inline-block;
  }
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
  :root[data-ops-theme="2"] .af-card { box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
  :root[data-ops-theme="2"] #opsContentWrap .af-tab, :root[data-ops-theme="2"] #opsContentWrap .af-btn, :root[data-ops-theme="2"] #opsContentWrap .af-btn-sm { font-family: 'DM Sans', sans-serif; }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_FD_AMENITIES = {
    backUrl: @json(route('students.dashboard', ['section' => 'tasks'])),
    amenitiesUrl: @json(route('students.hotel.amenities.index')),
    visitsUrl: @json(route('students.hotel.amenity-visits.index')),
    visitStoreUrl: @json(route('students.hotel.amenity-visits.store')),
    guestsUrl: @json(route('students.hotel.amenity-guests')),
    accessLabels: @json(\App\Models\HotelAmenity::ACCESS_LABELS),
    reservationsUrl: @json(route('students.hotel.amenity-reservations.index')),
    reservationStoreUrl: @json(route('students.hotel.amenity-reservations.store')),
    servicesUrl: @json(route('students.hotel.amenity-services.index')),
    packagesUrl: @json(route('students.hotel.catering-packages.index')),
    packages: @json(\App\Models\HotelAmenityReservation::PACKAGES),
    cateringPackages: @json(\App\Models\HotelAmenityReservation::CATERING_PACKAGES),
    setupPackages: @json(\App\Models\HotelAmenityReservation::SETUP_PACKAGES),
    reservationStatuses: @json(\App\Models\HotelAmenityReservation::STATUSES),
    payMethods: @json(\App\Models\HotelAmenityPayment::METHODS),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef } = React;

const CONFIG = window.HMS_FD_AMENITIES || {};
const ACCESS_LABELS = CONFIG.accessLabels || {};
const PACKAGES = CONFIG.packages || ['Hall Only', 'Hall + Setup', 'Hall + Catering', 'Custom Package'];
const CATERING_PACKAGES = CONFIG.cateringPackages || [];
const SETUP_PACKAGES = CONFIG.setupPackages || [];
const RES_STATUSES = CONFIG.reservationStatuses || [];
const PAY_METHODS = (CONFIG.payMethods || []).filter(m => m !== 'Room Account');

function peso(v) { return '₱' + Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function todayStr() { return new Date().toISOString().slice(0, 10); }

/* 'HH:MM' plus N minutes, so the form can show the end of an appointment the moment a
   service is picked. Mirrors HotelAmenityReservationDesk::addMinutes(). */
function addMinutes(time, minutes) {
  const parts = String(time || '').split(':');
  if (parts.length !== 2) return '';
  const total = Math.min(23 * 60 + 59, (parseInt(parts[0], 10) * 60) + parseInt(parts[1], 10) + (minutes || 0));
  return String(Math.floor(total / 60)).padStart(2, '0') + ':' + String(total % 60).padStart(2, '0');
}

function statusTone(status) {
  if (status === 'Completed') return 'var(--success)';
  if (status === 'Cancelled') return 'var(--danger)';
  if (status === 'Pending') return 'var(--warn)';
  return 'var(--accent)';
}

function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}

function amenityImg(amenity) {
  if (amenity && amenity.img) return amenity.img;
  const seed = encodeURIComponent((amenity && (amenity.id || amenity.name)) || 'amenity');
  return 'https://picsum.photos/seed/amenity-' + seed + '/800/600.jpg';
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

/* Taking a booking. One modal for both kinds: an appointment picks a service and gets its
   end time from that service's length, an event picks a package and a headcount. Everything
   else — who, when, whether it goes on a room — is the same question either way. */
function BookingModal({ amenity, guests, services, packages, onClose, onBooked }) {
  const isEvent = amenity.accessType === 'event';
  const amenityServices = services.filter(s => s.amenityId === amenity.dbId && s.isActive);

  const [form, setForm] = useState({
    customerName: '', contactNo: '', email: '',
    scheduledOn: todayStr(), startsAt: amenity.opensAt || '09:00', endsAt: '',
    specialRequests: '',
    serviceId: amenityServices.length ? String(amenityServices[0].id) : '',
    eventType: '', guestCount: '', packageName: 'Hall Only', cateringPackageId: '',
    additionalFee: '', additionalNote: '',
    bookingId: '', chargeToRoom: false,
  });
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const service = amenityServices.find(s => String(s.id) === String(form.serviceId)) || null;
  const cateringPack = packages.find(p => String(p.dbId) === String(form.cateringPackageId)) || null;
  const needsCatering = CATERING_PACKAGES.indexOf(form.packageName) !== -1;
  const needsSetup = SETUP_PACKAGES.indexOf(form.packageName) !== -1;

  // The end of an appointment follows the service, never a typed-in figure — the server
  // computes it the same way, so a 60 minute massage cannot be squeezed into a 30 minute gap.
  const derivedEnd = !isEvent && service ? addMinutes(form.startsAt, service.minutes) : form.endsAt;

  // Priced here only so the desk can see the figure before committing. The server prices
  // it again from its own rate card and that answer is the one that counts.
  const guestCount = Math.max(0, parseInt(form.guestCount, 10) || 0);
  const venueFee = isEvent ? Number(amenity.rate || 0) : 0;
  const setupFee = isEvent && needsSetup ? Number(amenity.setupFee || 0) : 0;
  const serviceFee = !isEvent && service ? Number(service.price || 0) : 0;
  const cateringFee = isEvent && needsCatering && cateringPack ? cateringPack.pricePerHead * guestCount : 0;
  const extraFee = Math.max(0, parseFloat(form.additionalFee) || 0);
  const estimate = venueFee + setupFee + serviceFee + cateringFee + extraFee;

  const handleSubmit = (e) => {
    e.preventDefault();
    const next = {};
    if (!form.customerName.trim()) next.customerName = 'Who is this booking for?';
    if (!form.scheduledOn) next.scheduledOn = 'Pick a date.';
    if (!form.startsAt) next.startsAt = 'Pick a start time.';
    if (isEvent && !form.endsAt) next.endsAt = 'Pick an end time.';
    if (isEvent && guestCount < 1) next.guestCount = 'How many guests?';
    if (!isEvent && !form.serviceId) next.serviceId = 'Choose a service.';
    if (isEvent && needsCatering && !form.cateringPackageId) next.cateringPackageId = 'Choose a catering package.';
    setErrors(next);
    if (Object.keys(next).length) return;

    setSaving(true);
    fetch(CONFIG.reservationStoreUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({
        hotel_amenity_id: amenity.dbId,
        kind: isEvent ? 'event' : 'appointment',
        customer_name: form.customerName.trim(),
        contact_no: form.contactNo.trim() || null,
        email: form.email.trim() || null,
        scheduled_on: form.scheduledOn,
        starts_at: form.startsAt,
        ends_at: isEvent ? form.endsAt : null,
        special_requests: form.specialRequests.trim() || null,
        hotel_booking_id: form.bookingId ? Number(form.bookingId) : null,
        charge_to_room: !!(form.bookingId && form.chargeToRoom),
        additional_fee: extraFee,
        additional_note: form.additionalNote.trim() || null,
        hotel_amenity_service_id: isEvent ? null : Number(form.serviceId),
        event_type: isEvent ? (form.eventType.trim() || null) : null,
        guest_count: isEvent ? guestCount : null,
        package: isEvent ? form.packageName : null,
        hotel_catering_package_id: isEvent && needsCatering ? Number(form.cateringPackageId) : null,
      }),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        onBooked(data.reservation);
        onClose();
        swal('success', 'Booked', data.reservation.reference + ' · ' + data.reservation.customerName + '.');
      })
      .catch(err => swal('error', 'Cannot book', (err && err.message) ? err.message : 'Could not take that booking.'))
      .finally(() => setSaving(false));
  };

  const errText = (k) => errors[k]
    ? <p style={{ margin: '0.35rem 0 0', color: 'var(--danger)', fontSize: '0.72rem' }}>{errors[k]}</p>
    : null;

  return (
    <div className="room-modal-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" style={{ maxWidth: 540 }} onClick={e => e.stopPropagation()}>
        <div style={{ padding: '1.5rem' }}>
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.35rem' }}>
            {isEvent ? 'Book Event' : 'Book Appointment'}
          </p>
          <h2 className="font-display" style={{ fontSize: '1.5rem', margin: '0 0 0.3rem', color: 'var(--fg)' }}>
            {amenity.name}
          </h2>
          <p style={{ color: 'var(--fg-muted)', fontSize: '0.75rem', margin: '0 0 1.1rem' }}>
            Open {amenity.hours || 'any time'}
            {amenity.capacity ? ' · seats ' + amenity.capacity : ''}
          </p>

          <form onSubmit={handleSubmit} className="rm-form-grid" noValidate>
            {/* Who. A staying guest can be picked from the in-house list, which is also
                what unlocks charging the booking to their room. */}
            <div>
              <label style={fieldLabel}>Hotel guest (optional)</label>
              <select
                className="booking-input" value={form.bookingId}
                onChange={e => {
                  const id = e.target.value;
                  const g = guests.find(x => String(x.bookingId) === String(id));
                  setForm(prev => Object.assign({}, prev, {
                    bookingId: id,
                    customerName: g ? g.guestName : prev.customerName,
                    contactNo: g && g.contactNo ? g.contactNo : prev.contactNo,
                    chargeToRoom: id ? prev.chargeToRoom : false,
                  }));
                }}
              >
                <option value="">Not a hotel guest / walk-in</option>
                {guests.map(g => (
                  <option key={g.bookingId} value={g.bookingId}>
                    {g.guestName}{g.roomName ? ' · Room ' + g.roomName : ''}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label style={fieldLabel}>{isEvent ? 'Customer / Organiser *' : 'Guest name *'}</label>
              <input
                type="text" className="booking-input" value={form.customerName}
                placeholder={isEvent ? 'Reyes Wedding' : 'Ana Cruz'}
                onChange={e => update('customerName', e.target.value)}
                style={errors.customerName ? { borderColor: '#f43f5e' } : undefined}
              />
              {errText('customerName')}
            </div>

            <div className="rm-form-row">
              <div>
                <label style={fieldLabel}>Contact Number</label>
                <input type="text" className="booking-input" value={form.contactNo}
                  placeholder="09171234567" onChange={e => update('contactNo', e.target.value)} />
              </div>
              <div>
                <label style={fieldLabel}>Email</label>
                <input type="email" className="booking-input" value={form.email}
                  placeholder="name@email.com" onChange={e => update('email', e.target.value)} />
              </div>
            </div>

            {/* Appointment: the service decides the price and the length. */}
            {!isEvent && (
              <div>
                <label style={fieldLabel}>Service *</label>
                <select
                  className="booking-input" value={form.serviceId}
                  onChange={e => update('serviceId', e.target.value)}
                  style={errors.serviceId ? { borderColor: '#f43f5e' } : undefined}
                >
                  <option value="">Choose a service…</option>
                  {amenityServices.map(s => (
                    <option key={s.id} value={s.id}>{s.name} · {s.duration} · {peso(s.price)}</option>
                  ))}
                </select>
                {errText('serviceId')}
                {amenityServices.length === 0 && (
                  <p style={{ margin: '0.35rem 0 0', color: 'var(--warn)', fontSize: '0.72rem' }}>
                    Housekeeping has not added any services for this facility yet.
                  </p>
                )}
              </div>
            )}

            {/* Event: what kind, and how many. */}
            {isEvent && (
              <div className="rm-form-row">
                <div>
                  <label style={fieldLabel}>Event Type</label>
                  <input type="text" className="booking-input" value={form.eventType}
                    placeholder="Wedding Reception" onChange={e => update('eventType', e.target.value)} />
                </div>
                <div>
                  <label style={fieldLabel}>Number of Guests *</label>
                  <input type="number" min="1" className="booking-input" value={form.guestCount}
                    placeholder="100" onChange={e => update('guestCount', e.target.value)}
                    style={errors.guestCount ? { borderColor: '#f43f5e' } : undefined} />
                  {errText('guestCount')}
                </div>
              </div>
            )}

            <div className="rm-form-row">
              <div>
                <label style={fieldLabel}>Date *</label>
                <input type="date" className="booking-input" value={form.scheduledOn}
                  min={todayStr()} onChange={e => update('scheduledOn', e.target.value)}
                  style={errors.scheduledOn ? { borderColor: '#f43f5e' } : undefined} />
                {errText('scheduledOn')}
              </div>
              <div>
                <label style={fieldLabel}>Start Time *</label>
                <input type="time" className="booking-input" value={form.startsAt}
                  onChange={e => update('startsAt', e.target.value)}
                  style={errors.startsAt ? { borderColor: '#f43f5e' } : undefined} />
                {errText('startsAt')}
              </div>
            </div>

            <div>
              <label style={fieldLabel}>End Time {isEvent ? '*' : ''}</label>
              <input
                type="time" className="booking-input"
                value={isEvent ? form.endsAt : derivedEnd}
                readOnly={!isEvent}
                onChange={e => update('endsAt', e.target.value)}
                style={Object.assign({}, !isEvent ? { opacity: 0.6 } : {}, errors.endsAt ? { borderColor: '#f43f5e' } : {})}
              />
              {errText('endsAt')}
              {!isEvent && (
                <p style={{ margin: '0.35rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem' }}>
                  Set by the service{service ? ' (' + service.duration + ')' : ''}.
                </p>
              )}
            </div>

            {/* Package. Setup puts the room on Housekeeping's list; Catering raises an
                order on Restaurant Services' board. Neither is typed in here. */}
            {isEvent && (
              <div>
                <label style={fieldLabel}>Package *</label>
                <select className="booking-input" value={form.packageName}
                  onChange={e => update('packageName', e.target.value)}>
                  {PACKAGES.map(p => <option key={p} value={p}>{p}</option>)}
                </select>
                <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem', lineHeight: 1.5 }}>
                  {needsSetup && needsCatering
                    ? 'Housekeeping will prepare the room and Restaurant Services will get the catering order.'
                    : needsSetup
                      ? 'Housekeeping will get a preparation request for tables, chairs and setup.'
                      : needsCatering
                        ? 'Restaurant Services will get the catering order automatically.'
                        : 'The hall only — no setup, no catering.'}
                </p>
              </div>
            )}

            {isEvent && needsCatering && (
              <div>
                <label style={fieldLabel}>Catering Package *</label>
                <select className="booking-input" value={form.cateringPackageId}
                  onChange={e => update('cateringPackageId', e.target.value)}
                  style={errors.cateringPackageId ? { borderColor: '#f43f5e' } : undefined}>
                  <option value="">Choose a package…</option>
                  {packages.filter(p => p.isActive).map(p => (
                    <option key={p.dbId} value={p.dbId}>
                      {p.name} · {peso(p.pricePerHead)}/head · min {p.minGuests}
                    </option>
                  ))}
                </select>
                {errText('cateringPackageId')}
                {cateringPack && cateringPack.inclusions && (
                  <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem', lineHeight: 1.5 }}>
                    {cateringPack.inclusions}
                  </p>
                )}
                {packages.length === 0 && (
                  <p style={{ margin: '0.35rem 0 0', color: 'var(--warn)', fontSize: '0.72rem' }}>
                    Restaurant Services have not published any catering packages yet.
                  </p>
                )}
              </div>
            )}

            <div>
              <label style={fieldLabel}>Special Requests</label>
              <textarea className="booking-input" value={form.specialRequests}
                placeholder={isEvent ? 'Round tables of 10, aisle down the middle.' : 'Prefers a female therapist.'}
                onChange={e => update('specialRequests', e.target.value)} />
            </div>

            <div className="rm-form-row">
              <div>
                <label style={fieldLabel}>Additional Services (₱)</label>
                <input type="number" min="0" step="0.01" className="booking-input" value={form.additionalFee}
                  placeholder="0" onChange={e => update('additionalFee', e.target.value)} />
              </div>
              <div>
                <label style={fieldLabel}>What for</label>
                <input type="text" className="booking-input" value={form.additionalNote}
                  placeholder="Projector, extra linens" onChange={e => update('additionalNote', e.target.value)} />
              </div>
            </div>

            {/* The running total the desk quotes. The server prices it again on save. */}
            <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '0.85rem 1rem', background: 'rgba(255,255,255,0.02)' }}>
              {venueFee > 0 && <div style={quoteRow}><span>Function room</span><span>{peso(venueFee)}</span></div>}
              {setupFee > 0 && <div style={quoteRow}><span>Setup</span><span>{peso(setupFee)}</span></div>}
              {serviceFee > 0 && <div style={quoteRow}><span>{service ? service.name : 'Service'}</span><span>{peso(serviceFee)}</span></div>}
              {cateringFee > 0 && (
                <div style={quoteRow}>
                  <span>{cateringPack.name} × {guestCount}</span><span>{peso(cateringFee)}</span>
                </div>
              )}
              {extraFee > 0 && <div style={quoteRow}><span>{form.additionalNote || 'Additional services'}</span><span>{peso(extraFee)}</span></div>}
              <div style={Object.assign({}, quoteRow, {
                borderTop: '1px solid var(--border)', marginTop: '0.5rem', paddingTop: '0.5rem',
                color: 'var(--fg)', fontWeight: 700,
              })}>
                <span>Estimated total</span><span>{peso(estimate)}</span>
              </div>
            </div>

            {/* Only offered when a checked-in guest is attached — there is no room to
                charge otherwise, and the server refuses it anyway. */}
            {form.bookingId && (
              <label style={{ display: 'flex', alignItems: 'flex-start', gap: '0.6rem', fontSize: '0.78rem', color: 'var(--fg-muted)', cursor: 'pointer' }}>
                <input type="checkbox" checked={form.chargeToRoom} style={{ marginTop: 3 }}
                  onChange={e => update('chargeToRoom', e.target.checked)} />
                <span>
                  Charge to the guest's room account. It joins their final bill at check-out —
                  you can also do this later from the booking.
                </span>
              </label>
            )}

            <button type="submit" className="btn-primary" disabled={saving} style={{ justifyContent: 'center' }}>
              {saving ? 'Booking…' : (isEvent ? 'Confirm Reservation' : 'Confirm Appointment')}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

const quoteRow = {
  display: 'flex', justifyContent: 'space-between', gap: '1rem',
  fontSize: '0.78rem', color: 'var(--fg-muted)', padding: '0.15rem 0',
};

/* An existing booking: what it costs, what has been paid, and the two things the desk
   can still do — take money, or push the balance onto a staying guest's room. */
function ReservationModal({ reservation, onClose, onChanged }) {
  const [amount, setAmount] = useState('');
  const [method, setMethod] = useState(PAY_METHODS[0] || 'Cash');
  const [busy, setBusy] = useState(false);
  const r = reservation;

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const post = (url, body) => {
    setBusy(true);
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: body === undefined ? undefined : JSON.stringify(body),
    })
      .then(res => (res.ok ? res.json() : res.json().then(err => Promise.reject(err))))
      .then(data => { onChanged(data.reservation); return data; })
      .catch(err => { swal('error', 'Error', (err && err.message) ? err.message : 'That did not work.'); return null; })
      .finally(() => setBusy(false));
  };

  const base = CONFIG.reservationStoreUrl + '/' + r.id;

  const takePayment = (e) => {
    e.preventDefault();
    const value = parseFloat(amount);
    if (!Number.isFinite(value) || value <= 0) { swal('error', 'Error', 'Enter how much was paid.'); return; }
    post(base + '/payments', {
      // Partial while anything is left over, Full when it clears the balance.
      type: value >= r.balance ? 'Full' : 'Partial',
      amount_paid: value,
      method: method,
    }).then(d => { if (d) { setAmount(''); swal('success', 'Payment recorded', peso(value) + ' received.'); } });
  };

  const advance = (status) => {
    setBusy(true);
    fetch(base, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ status: status }),
    })
      .then(res => (res.ok ? res.json() : res.json().then(err => Promise.reject(err))))
      .then(data => onChanged(data.reservation))
      .catch(err => swal('error', 'Error', (err && err.message) ? err.message : 'Could not update that booking.'))
      .finally(() => setBusy(false));
  };

  const line = (label, value) => (
    <div style={quoteRow}><span>{label}</span><span>{peso(value)}</span></div>
  );

  return (
    <div className="room-modal-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" style={{ maxWidth: 520 }} onClick={e => e.stopPropagation()}>
        <div style={{ padding: '1.5rem' }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', gap: '1rem' }}>
            <div>
              <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.35rem' }}>
                {r.reference || 'Booking'}
              </p>
              <h2 className="font-display" style={{ fontSize: '1.45rem', margin: '0 0 0.25rem', color: 'var(--fg)' }}>
                {r.customerName}
              </h2>
              <p style={{ color: 'var(--fg-muted)', fontSize: '0.76rem', margin: 0 }}>
                {r.amenityName} · {r.scheduledOn} · {r.timeLabel}
              </p>
            </div>
            <button type="button" onClick={onClose} aria-label="Close"
              style={{ background: 'none', border: 'none', color: 'var(--fg-muted)', cursor: 'pointer', fontSize: '1rem', alignSelf: 'flex-start' }}>
              <i className="fa-solid fa-xmark"></i>
            </button>
          </div>

          <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap', margin: '1rem 0' }}>
            <span className="am-badge" style={{ background: 'transparent', borderColor: statusTone(r.status), color: statusTone(r.status) }}>
              {r.status}
            </span>
            {r.package && <span className="am-cap">{r.package}</span>}
            {r.serviceName && <span className="am-cap">{r.serviceName}</span>}
            {r.guestCount ? <span className="am-cap">{r.guestCount} guests</span> : null}
            {r.housekeepingStatus && <span className="am-cap">Room: {r.housekeepingStatus}</span>}
            {r.cateringOrderStatus && <span className="am-cap">Kitchen: {r.cateringOrderStatus}</span>}
          </div>

          {(r.contactNo || r.email) && (
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.76rem', margin: '0 0 0.75rem' }}>
              {r.contactNo}{r.contactNo && r.email ? ' · ' : ''}{r.email}
            </p>
          )}
          {r.specialRequests && (
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.76rem', fontStyle: 'italic', margin: '0 0 0.9rem', lineHeight: 1.5 }}>
              “{r.specialRequests}”
            </p>
          )}

          <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '0.85rem 1rem', background: 'rgba(255,255,255,0.02)' }}>
            {r.venueFee > 0 && line('Function room', r.venueFee)}
            {r.setupFee > 0 && line('Setup', r.setupFee)}
            {r.serviceFee > 0 && line(r.serviceName || 'Service', r.serviceFee)}
            {r.cateringTotal > 0 && line('Catering' + (r.cateringPackageName ? ' · ' + r.cateringPackageName : ''), r.cateringTotal)}
            {r.additionalFee > 0 && line(r.additionalNote || 'Additional services', r.additionalFee)}
            <div style={Object.assign({}, quoteRow, { borderTop: '1px solid var(--border)', marginTop: '0.5rem', paddingTop: '0.5rem', color: 'var(--fg)', fontWeight: 700 })}>
              <span>Total</span><span>{peso(r.total)}</span>
            </div>
            {r.payments.map(p => (
              <div key={p.id} style={Object.assign({}, quoteRow, { color: 'var(--success)' })}>
                <span>{p.type} · {p.method}{p.paidAt ? ' · ' + p.paidAt.slice(0, 10) : ''}</span>
                <span>− {peso(p.amountPaid)}</span>
              </div>
            ))}
            <div style={Object.assign({}, quoteRow, { borderTop: '1px solid var(--border)', marginTop: '0.5rem', paddingTop: '0.5rem', color: r.balance > 0 ? 'var(--fg)' : 'var(--success)', fontWeight: 700 })}>
              <span>{r.balance > 0 ? 'Amount Due' : 'Settled'}</span><span>{peso(r.balance)}</span>
            </div>
          </div>

          {r.balance > 0 && r.status !== 'Cancelled' && (
            <form onSubmit={takePayment} style={{ marginTop: '1rem', display: 'grid', gap: '0.6rem' }}>
              <label style={fieldLabel}>Take a payment</label>
              <div className="rm-form-row">
                <input type="number" min="0.01" step="0.01" className="booking-input" value={amount}
                  placeholder={String(r.balance)} onChange={e => setAmount(e.target.value)} />
                <select className="booking-input" value={method} onChange={e => setMethod(e.target.value)}>
                  {PAY_METHODS.map(m => <option key={m} value={m}>{m}</option>)}
                </select>
              </div>
              <button type="submit" className="btn-primary" disabled={busy} style={{ justifyContent: 'center' }}>
                {busy ? 'Working…' : 'Record Payment'}
              </button>
            </form>
          )}

          {/* Only where there is a stay to charge and it has not already been pushed. */}
          {r.bookingId && r.balance > 0 && !r.postedToFolio && r.status !== 'Cancelled' && (
            <button type="button" className="btn-outline" disabled={busy}
              style={{ marginTop: '0.7rem', width: '100%', justifyContent: 'center' }}
              onClick={() => post(base + '/charge-to-room').then(d => {
                if (d) swal('success', 'Charged to room', 'It will appear on the guest’s final bill.');
              })}>
              <i className="fa-solid fa-receipt" style={{ fontSize: '0.65rem' }}></i> Charge {peso(r.balance)} to Room
            </button>
          )}
          {r.postedToFolio && (
            <p style={{ marginTop: '0.7rem', color: 'var(--success)', fontSize: '0.75rem' }}>
              <i className="fa-solid fa-circle-check" style={{ marginRight: '0.4rem' }}></i>
              On the guest's room account — it settles at check-out.
            </p>
          )}

          {/* Forward only, so only the legal next steps are offered. */}
          <div style={{ display: 'flex', gap: '0.45rem', flexWrap: 'wrap', marginTop: '1rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)' }}>
            {RES_STATUSES.filter(st => st !== r.status && st !== 'Pending').map(st => {
              const flow = ['Pending', 'Confirmed', 'In Progress', 'Completed'];
              const from = flow.indexOf(r.status);
              const to = flow.indexOf(st);
              const done = r.status === 'Completed' || r.status === 'Cancelled';
              const allowed = !done && (st === 'Cancelled' || (from !== -1 && to > from));
              if (!allowed) return null;
              return (
                <button key={st} type="button" className="btn-outline" disabled={busy}
                  style={st === 'Cancelled' ? { color: 'var(--danger)', borderColor: 'var(--danger)' } : undefined}
                  onClick={() => advance(st)}>
                  {st === 'Cancelled' ? 'Cancel Booking' : 'Mark ' + st}
                </button>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
}

/* What the desk does for each kind of facility, in plain words. The kind comes
   from accessType, set by Housekeeping, never from the amenity's name. */
const KINDS = {
  registered:  { tab: 'signin', does: 'Sign guests in and out', icon: 'fa-right-to-bracket' },
  appointment: { tab: 'book',   does: 'Book an appointment',    icon: 'fa-calendar-check' },
  event:       { tab: 'book',   does: 'Book an event',          icon: 'fa-calendar-days' },
  open:        { tab: 'open',   does: 'Guests walk in freely',  icon: 'fa-door-open' },
};
const kindOf = amenity => KINDS[amenity.accessType] || KINDS.open;

const TABS = [
  { key: 'all',    label: 'All facilities',  icon: 'fa-grip' },
  { key: 'signin', label: 'Sign guests in',  icon: 'fa-right-to-bracket' },
  { key: 'book',   label: 'Book ahead',      icon: 'fa-calendar-check' },
  { key: 'open',   label: 'Walk in freely',  icon: 'fa-door-open' },
];

/* One status for the photo. Housekeeping's own status wins; otherwise it is open or
   shut by the clock, because a pool can be Available and still closed at midnight. */
function amenityState(amenity) {
  if (amenity.status === 'Under Maintenance') return { cls: 'is-repair', label: 'Under maintenance' };
  if (amenity.status !== 'Available') return { cls: 'is-closed', label: amenity.status || 'Closed' };
  return amenity.isOpenNow ? { cls: 'is-open', label: 'Open now' } : { cls: 'is-closed', label: 'Closed right now' };
}

/* The action area is the whole point of this screen: it is different for each kind of
   facility, driven by accessType rather than by the amenity's name. */
function AmenityActions({ amenity, visits, reservations, canRegister, now, onOpenEntry, onExit, exitingId, onOpenBooking, onOpenReservation }) {
  const kind = amenity.accessType || 'open';

  if (kind === 'open') {
    return (
      <div className="af-action">
        <p className="af-note">
          <i className="fa-solid fa-circle-info"></i>
          <span>No sign-in needed. Guests can use it on their own during opening hours.</span>
        </p>
      </div>
    );
  }

  if (kind === 'registered') {
    const inside = visits.filter(v => v.amenityId === amenity.dbId);
    const heads = inside.reduce((sum, v) => sum + (v.partySize || 1), 0);
    const hasCap = amenity.capacity !== null && amenity.capacity !== undefined;
    const full = hasCap && heads >= amenity.capacity;
    const shut = amenity.status !== 'Available' || !amenity.isOpenNow;
    const why = !canRegister ? null
      : shut ? (amenity.status !== 'Available'
          ? 'It is ' + String(amenity.status).toLowerCase() + ', so no one can be signed in.'
          : 'It is closed right now, so no one can be signed in.')
      : full ? 'It is full. Sign someone out first.'
      : null;
    const pct = hasCap && amenity.capacity > 0 ? Math.min(100, Math.round((heads / amenity.capacity) * 100)) : 0;

    return (
      <div className="af-action">
        <div>
          <div className="af-meter-head">
            <span>Guests inside now</span>
            <b>{heads}{hasCap ? ' of ' + amenity.capacity : ''}</b>
          </div>
          {hasCap ? <div className={'af-meter' + (full ? ' is-full' : '')}><span style={{ width: pct + '%' }}></span></div> : null}
        </div>

        {why && <p className="af-why"><i className="fa-solid fa-circle-exclamation"></i><span>{why}</span></p>}
        {canRegister && (
          <button type="button" className="af-btn" disabled={shut || full} onClick={() => onOpenEntry(amenity)}>
            <i className="fa-solid fa-right-to-bracket"></i> Sign a guest in
          </button>
        )}

        {inside.length > 0 && (
          <div className="af-list">
            <p className="af-list-title">Inside now</p>
            {inside.map(v => (
              <div key={v.id} className="af-row">
                <div className="af-row-text">
                  <b>{v.guestName}</b>
                  <small>
                    {v.roomName ? 'Room ' + v.roomName + ' · ' : ''}
                    {v.partySize > 1 ? v.partySize + ' people · ' : ''}
                    in for {sinceLabel(v.enteredAt, now)}
                  </small>
                </div>
                {canRegister && (
                  <button type="button" className="af-btn-sm" disabled={exitingId === v.id} onClick={() => onExit(v)}>
                    <i className="fa-solid fa-right-from-bracket"></i>
                    {exitingId === v.id ? 'Signing out…' : 'Sign out'}
                  </button>
                )}
              </div>
            ))}
          </div>
        )}
      </div>
    );
  }

  // appointment / event — both are slot bookings, so they share one panel and differ
  // only in which modal opens and what the row shows.
  const booked = reservations
    .filter(r => r.amenityId === amenity.dbId && r.status !== 'Cancelled')
    .sort((a, b) => (a.scheduledOn + a.startsAt < b.scheduledOn + b.startsAt ? -1 : 1));
  const blocked = amenity.status !== 'Available';

  return (
    <div className="af-action">
      {kind === 'event' && amenity.rate > 0 && (
        <div className="af-meter-head">
          <span>Hall rate</span>
          <b>{peso(amenity.rate)} per event</b>
        </div>
      )}

      {canRegister && blocked && (
        <p className="af-why"><i className="fa-solid fa-circle-exclamation"></i><span>It is {String(amenity.status).toLowerCase()}, so it is not taking bookings.</span></p>
      )}
      {canRegister && (
        <button type="button" className="af-btn" disabled={blocked} onClick={() => onOpenBooking(amenity)}>
          <i className="fa-solid fa-calendar-plus"></i>
          {kind === 'event' ? 'Book an event' : 'Book an appointment'}
        </button>
      )}

      {booked.length > 0 ? (
        <div className="af-list">
          <p className="af-list-title">Coming up ({booked.length})</p>
          {booked.slice(0, 4).map(r => (
            <div key={r.id} className="af-row">
              <div className="af-row-text">
                <b>{r.customerName}</b>
                <small>
                  {r.scheduledOn} · {r.timeLabel}
                  {r.serviceName ? ' · ' + r.serviceName : ''}
                  {r.eventType ? ' · ' + r.eventType : ''}
                  {r.guestCount ? ' · ' + r.guestCount + ' guests' : ''}
                </small>
                <small>
                  <span style={{ color: statusTone(r.status), fontWeight: 600 }}>{r.status}</span>
                  {r.balance > 0
                    ? <span> · {peso(r.balance)} still to pay</span>
                    : <span style={{ color: 'var(--success)' }}> · fully paid</span>}
                </small>
              </div>
              <button type="button" className="af-btn-sm" onClick={() => onOpenReservation(r)}>
                View
              </button>
            </div>
          ))}
          {booked.length > 4 && <p className="af-more">and {booked.length - 4} more.</p>}
        </div>
      ) : (
        <p className="af-note">
          <i className="fa-solid fa-circle-info"></i>
          <span>No bookings yet.</span>
        </p>
      )}
    </div>
  );
}

function AmenityCard(props) {
  const { amenity, now } = props;
  const state = amenityState(amenity);
  const kind = kindOf(amenity);

  return (
    <article className={'af-card' + (state.cls !== 'is-open' ? ' is-shut' : '')}>
      <div className="af-media">
        <img src={amenityImg(amenity)} alt={amenity.name} />
        <span className={'af-state ' + state.cls}><span className="dot"></span>{state.label}</span>
      </div>
      <div className="af-body">
        <div>
          <h2 className="af-title font-display" title={amenity.name}>{amenity.name}</h2>
          <span className="af-does"><i className={'fa-solid ' + kind.icon}></i>{kind.does}</span>
        </div>
        <div className="af-facts">
          <div><i className="fa-solid fa-location-dot"></i><span>{amenity.location || 'No location given'}</span></div>
          <div><i className="fa-regular fa-clock"></i><span>{amenity.hours ? 'Open ' + amenity.hours : 'No opening hours posted'}</span></div>
        </div>
        <AmenityActions {...props} now={now} />
      </div>
    </article>
  );
}

function App() {
  const [amenities, setAmenities] = useState([]);
  const [visits, setVisits] = useState([]);
  const [reservations, setReservations] = useState([]);
  const [services, setServices] = useState([]);
  const [packages, setPackages] = useState([]);
  const [guests, setGuests] = useState([]);
  const [bookingFor, setBookingFor] = useState(null);
  const [openReservation, setOpenReservation] = useState(null);
  const [canRegister, setCanRegister] = useState(false);
  const [loading, setLoading] = useState(true);
  const [entryFor, setEntryFor] = useState(null);
  const [exitingId, setExitingId] = useState(null);
  const [tab, setTab] = useState('all');
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

    // Only what is still holding a slot: a cancelled or finished booking is history and
    // does not belong on a working screen.
    fetch(CONFIG.reservationsUrl + '?holding=1', opts)
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => { if (pendingWrites.current === 0) setReservations(d.reservations || []); })
      .catch(() => {});

    // Housekeeping's service list and Restaurant Services' packages. Read-only here —
    // Front Desk sells from them and cannot edit either.
    fetch(CONFIG.servicesUrl, opts)
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => { if (pendingWrites.current === 0) setServices(d.services || []); })
      .catch(() => {});

    fetch(CONFIG.packagesUrl, opts)
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => { if (pendingWrites.current === 0) setPackages(d.packages || []); })
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

  const handleBooked = useCallback((reservation) => {
    setReservations(prev => prev.concat([reservation]));
  }, []);

  /* Splice the changed booking in rather than refetching: the response already carries
     the recomputed total, and a refetch here would race the poll. A booking that has
     stopped holding its slot drops off the working list. */
  const handleReservationChanged = useCallback((reservation) => {
    setReservations(prev => {
      const rest = prev.filter(r => r.id !== reservation.id);
      const stillHolding = ['Pending', 'Confirmed', 'In Progress'].indexOf(reservation.status) !== -1;
      return stillHolding ? rest.concat([reservation]) : rest;
    });
    setOpenReservation(prev => (prev && prev.id === reservation.id ? reservation : prev));
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

  const inTab = key => amenities.filter(a => key === 'all' || kindOf(a).tab === key);
  const shown = inTab(tab);
  const insideNow = visits.reduce((sum, v) => sum + (v.partySize || 1), 0);
  const currentTab = TABS.find(t => t.key === tab) || TABS[0];

  return (
    <div className="af" data-hms-no-edit="1">
      <header className="af-head">
        <p className="af-eyebrow">Front Desk</p>
        <h1 className="font-display">Amenities</h1>
        <p className="af-lead">
          The hotel's facilities and what the desk does for each one: sign guests in and out
          of places like the pool and gym, and book the ones that need a reservation.
          Housekeeping keeps the opening hours and condition up to date.
        </p>
        {!loading && !canRegister && (
          <p className="af-readonly">
            <i className="fa-solid fa-eye"></i>
            You can look but not change anything here. Signing guests in and booking is Front Desk work.
          </p>
        )}
      </header>

      {loading ? (
        <p style={{ color: 'var(--fg-muted)', fontSize: '0.86rem' }}>Loading amenities…</p>
      ) : amenities.length === 0 ? (
        <div className="af-empty">
          <div className="af-empty-icon"><i className="fa-solid fa-person-swimming"></i></div>
          <h2>No amenities yet</h2>
          <p>Housekeeping adds the hotel's facilities. Once they do, they will show up here.</p>
        </div>
      ) : (
        <>
          <div className="af-toolbar">
            <div className="af-tabs" role="group" aria-label="Show facilities by what the desk does">
              {TABS.map(t => (
                <button
                  key={t.key}
                  type="button"
                  className={'af-tab' + (tab === t.key ? ' is-on' : '')}
                  aria-pressed={tab === t.key}
                  onClick={() => setTab(t.key)}
                >
                  <i className={'fa-solid ' + t.icon}></i>
                  {t.label}
                  <span className="af-count">{inTab(t.key).length}</span>
                </button>
              ))}
            </div>
            <p className="af-summary">
              <i className="fa-solid fa-users"></i>
              <span><b>{insideNow}</b> {insideNow === 1 ? 'guest' : 'guests'} signed in right now</span>
            </p>
          </div>

          {shown.length === 0 ? (
            <div className="af-empty">
              <div className="af-empty-icon"><i className={'fa-solid ' + currentTab.icon}></i></div>
              <h2>Nothing under {currentTab.label}</h2>
              <p>None of the hotel's facilities work this way. Pick another tab to see the rest.</p>
            </div>
          ) : (
            <div className="af-grid">
              {shown.map(amenity => (
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
                  reservations={reservations}
                  onOpenBooking={setBookingFor}
                  onOpenReservation={setOpenReservation}
                />
              ))}
            </div>
          )}
        </>
      )}

      {entryFor && (
        <RegisterEntryModal
          amenity={entryFor}
          guests={guests}
          onClose={() => setEntryFor(null)}
          onRegistered={handleRegistered}
        />
      )}

      {bookingFor && (
        <BookingModal
          amenity={bookingFor}
          guests={guests}
          services={services}
          packages={packages}
          onClose={() => setBookingFor(null)}
          onBooked={handleBooked}
        />
      )}

      {openReservation && (
        <ReservationModal
          reservation={openReservation}
          onClose={() => setOpenReservation(null)}
          onChanged={handleReservationChanged}
        />
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
