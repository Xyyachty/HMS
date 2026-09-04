@extends('students.builder.ops-shell')

@php $backRoute = 'students.roommanagement'; @endphp

@section('page-title', 'Room Management')

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
  /* Only the two booking-lifecycle tones are used now — see bookingStatusClass(). */
  .room-status-badge.status-reserved { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .room-status-badge.status-occupied { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
  .rm-table { width: 100%; border-collapse: collapse; font-family: var(--font-body, 'Outfit', sans-serif); }
  .rm-table th {
    padding: 0.6rem 0.85rem; font-size: 0.62rem; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase; color: var(--fg-muted);
    border-bottom: 1px solid var(--border); white-space: nowrap;
    text-align: left; background: rgba(255,255,255,0.02);
  }
  .rm-table td {
    padding: 0.7rem 0.85rem; font-size: 0.82rem; color: var(--fg-muted);
    border-bottom: 1px solid rgba(42,38,33,0.5); vertical-align: middle; white-space: nowrap;
  }
  .rm-table tr:last-child td { border-bottom: none; }
  .btn-primary {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: var(--accent); color: var(--bg);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600;
    font-size: 0.8rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.8rem 1.8rem; border: none; border-radius: 6px;
    cursor: pointer; transition: background 0.2s, transform 0.2s;
  }
  .btn-primary:hover { background: var(--accent-light); transform: translateY(-1px); }
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
  .rm-row {
    display: flex; align-items: stretch; width: 100%;
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    overflow: hidden;
  }
  .rm-content { flex: 1; min-width: 0; padding: 1.25rem 1.6rem; position: relative; color: var(--fg); }
  .rm-panel { max-width: 520px; }
  .rm-panel h3 { font-family: var(--font-display, 'Playfair Display', serif); font-size: 1.35rem; font-weight: 700; margin: 0 0 0.35rem; color: var(--fg); }
  .rm-panel-desc { color: var(--fg-muted); font-size: 0.82rem; margin: 0 0 1.35rem; line-height: 1.5; }
  .rm-form-grid { display: grid; gap: 0.95rem; }
  .rm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }
  @media (max-width: 640px) {
    .rm-row { flex-direction: column; }
    .rm-form-row { grid-template-columns: 1fr; }
  }

  /* Room Availability tab — card grid + detail modal, same look as the hotel
     site's Rooms page so the calendar reads the same way in both places. */
  .room-card-tab {
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.74rem; font-weight: 600;
    letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.5rem 0.9rem; border-radius: 100px;
    border: 1.5px solid var(--border); background: transparent;
    color: var(--fg-muted); cursor: pointer; transition: all 0.15s;
  }
  .room-card-tab:hover { border-color: var(--accent); color: var(--accent); }
  .room-card-tab.active { background: var(--accent); border-color: var(--accent); color: var(--bg, #0c0b09); }
  .room-browse-card {
    text-align: left; border: 1px solid var(--border); border-radius: 10px;
    overflow: hidden; background: var(--bg-warm); cursor: pointer; padding: 0;
    transition: border-color 0.15s, transform 0.15s;
  }
  .room-browse-card:hover { border-color: var(--accent); transform: translateY(-2px); }

  .room-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.6);
    display: flex; align-items: center; justify-content: center;
    padding: 1.5rem; z-index: 200;
  }
  .room-modal {
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    width: 100%; max-width: 480px; max-height: 90vh; overflow-y: auto;
  }
  .room-modal-img { position: relative; height: 200px; }
  .room-modal-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .room-modal-close {
    position: absolute; top: 10px; right: 10px; width: 32px; height: 32px;
    border-radius: 8px; border: none; background: rgba(0,0,0,0.55); color: #fff;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
  }

  .room-cal {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 12px; padding: 0.9rem 1rem 1rem;
  }
  .room-cal-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 0.75rem;
  }
  .room-cal-title {
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.82rem; font-weight: 600;
    color: var(--fg);
  }
  .room-cal-nav {
    width: 26px; height: 26px; border-radius: 8px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all 0.15s;
  }
  .room-cal-nav:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
  .room-cal-nav:disabled { opacity: 0.3; cursor: not-allowed; }
  .room-cal-weekdays, .room-cal-grid {
    display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.25rem;
  }
  .room-cal-weekdays { margin-bottom: 0.35rem; }
  .room-cal-weekdays span {
    font-size: 0.62rem; letter-spacing: 0.06em; text-transform: uppercase;
    color: var(--fg-muted); text-align: center;
  }
  .room-cal-day {
    aspect-ratio: 1; border-radius: 8px; border: 1px solid transparent;
    background: rgba(255,255,255,0.02); color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.74rem; cursor: default;
    display: flex; align-items: center; justify-content: center;
  }
  .room-cal-day.is-blank { visibility: hidden; }
  .room-cal-day.is-past { color: var(--fg-muted); opacity: 0.35; }
  .room-cal-day.is-booked { background: rgba(244,63,94,0.14); color: var(--danger, #fb7185); }
  .room-cal-legend { display: flex; flex-wrap: wrap; gap: 0.9rem; margin-top: 0.75rem; }
  .room-cal-legend span {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.68rem; color: var(--fg-muted);
  }
  .room-cal-swatch { width: 10px; height: 10px; border-radius: 3px; display: inline-block; background: rgba(255,255,255,0.08); }
  .room-cal-swatch.is-booked { background: var(--danger, #fb7185); }
  .room-cal-swatch.is-past { background: var(--fg-muted); opacity: 0.5; }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Additive only — nothing above this block is touched, so a Template 1
     team (or one that hasn't chosen a template yet) renders unchanged. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d;
  }
  :root[data-ops-theme="2"] .room-status-badge.status-reserved { background: #f3e8ff; color: #7e22ce; border-color: #e9d5ff; }
  :root[data-ops-theme="2"] .room-status-badge.status-occupied { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
  :root[data-ops-theme="2"] .rm-table th { background: rgba(27,67,50,0.04); }
  :root[data-ops-theme="2"] .rm-table td { border-bottom-color: var(--border); }
  :root[data-ops-theme="2"] .booking-input,
  :root[data-ops-theme="2"] .room-cal { background: rgba(27,67,50,0.03); }
  :root[data-ops-theme="2"] .room-cal-day { background: rgba(27,67,50,0.035); }
  :root[data-ops-theme="2"] .room-cal-day.is-booked { background: rgba(225,29,72,0.1); }
  :root[data-ops-theme="2"] .room-cal-swatch { background: rgba(27,67,50,0.12); }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_ROOMMANAGEMENT_URL = @json(route('students.roommanagement'));
  window.HMS_ROOM_MGMT_INITIAL_NAV = @json(request()->query('nav', 'manage-room'));
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef, useMemo } = React;

// What every team starts with. Room Management can add categories of its own while
// customising the Rooms section of the site; the server sends the team's full list
// down with the rooms, and these five only stand in until it arrives.
const DEFAULT_ROOM_CATEGORIES = ['Classic', 'Superior', 'Deluxe', 'Premium', 'Family'];
let ROOM_CATEGORIES = DEFAULT_ROOM_CATEGORIES.slice();
/* Module-level rather than a prop: normalizeRoomCategory() is called from several
   render paths. The App holds the same list in state, so a change re-renders. */
function setRoomCategoryNames(names) {
  if (Array.isArray(names) && names.length) ROOM_CATEGORIES = names.slice();
}
const BLOCK_HOURS = 12;
const IMAGE_MAX_DIMENSION = 1280;
const IMAGE_MAX_BYTES = 600 * 1024;

/* hotel_rooms.status is not read on this page at all now: it is a housekeeping
   condition (Available / Cleaning / Maintenance) that Housekeeping owns, and it
   answered none of the questions Room Management asks here. Occupancy comes off
   the booking (see bookingStatusClass) and availability off the calendar. */

/* Booking-lifecycle badge (Booked / Checked In) — reuses the room-status-badge
   colours (purple/blue) for a different meaning now that hotel_rooms.status no
   longer tracks occupancy. */
function bookingStatusClass(status) {
  return String(status || '').trim() === 'Checked In' ? 'status-occupied' : 'status-reserved';
}
/* Falls back to the team's first category, not to a literal "Classic" — the Rooms tab
   bar can rename that one, so it stops being an answer once somebody does. */
function normalizeRoomCategory(value) {
  const raw = String(value || '').trim().toLowerCase();
  const match = ROOM_CATEGORIES.find(c => c.toLowerCase() === raw);
  return match || ROOM_CATEGORIES[0] || 'Classic';
}
function reservationArrivalStatus(reservation) {
  const raw = String((reservation && reservation.arrivalStatus) || 'Booked').trim().toLowerCase();
  return raw === 'arrived' ? 'Arrived' : 'Booked';
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

/* ── Room availability calendar — same shape as the hotel site's Rooms page,
   read-only here since Room Management doesn't take reservations. ──────── */

function todayStr() {
  const d = new Date();
  return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
}

/* 'to' is the checkout date and is exclusive — the guest is gone by morning,
   so that day is free for the next booking. */
function bookedDateSet(ranges) {
  const set = new Set();
  (ranges || []).forEach(r => {
    if (!r || !r.from || !r.to) return;
    let cursor = r.from;
    let guard = 0;
    while (cursor < r.to && guard < 800) {
      set.add(cursor);
      const [y, m, d] = cursor.split('-').map(Number);
      const next = new Date(y, m - 1, d + 1);
      cursor = next.getFullYear() + '-' + String(next.getMonth() + 1).padStart(2, '0') + '-' + String(next.getDate()).padStart(2, '0');
      guard += 1;
    }
  });
  return set;
}

function monthCells(year, month) {
  const first = new Date(year, month, 1);
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  const cells = [];
  for (let i = 0; i < first.getDay(); i++) cells.push(null);
  for (let day = 1; day <= daysInMonth; day++) {
    cells.push(year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0'));
  }
  return cells;
}

const MONTH_NAMES = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

function RoomAvailabilityCalendar({ ranges }) {
  const today = todayStr();
  const bookedSet = useMemo(() => bookedDateSet(ranges), [ranges]);
  const [cursor, setCursor] = useState(() => {
    const [y, m] = today.split('-').map(Number);
    return { year: y, month: m - 1 };
  });

  const isCurrentMonth = (() => {
    const [y, m] = today.split('-').map(Number);
    return cursor.year === y && cursor.month === m - 1;
  })();

  const cells = useMemo(() => monthCells(cursor.year, cursor.month), [cursor]);

  const goPrev = () => setCursor(prev => {
    const month = prev.month === 0 ? 11 : prev.month - 1;
    const year = prev.month === 0 ? prev.year - 1 : prev.year;
    return { year, month };
  });
  const goNext = () => setCursor(prev => {
    const month = prev.month === 11 ? 0 : prev.month + 1;
    const year = prev.month === 11 ? prev.year + 1 : prev.year;
    return { year, month };
  });

  return (
    <div className="room-cal">
      <div className="room-cal-header">
        <button type="button" className="room-cal-nav" onClick={goPrev} disabled={isCurrentMonth} aria-label="Previous month">
          <i className="fa-solid fa-chevron-left" style={{ fontSize: 11 }}></i>
        </button>
        <span className="room-cal-title">{MONTH_NAMES[cursor.month]} {cursor.year}</span>
        <button type="button" className="room-cal-nav" onClick={goNext} aria-label="Next month">
          <i className="fa-solid fa-chevron-right" style={{ fontSize: 11 }}></i>
        </button>
      </div>
      <div className="room-cal-weekdays">
        {['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'].map(d => <span key={d}>{d}</span>)}
      </div>
      <div className="room-cal-grid">
        {cells.map((day, i) => {
          if (!day) return <span key={'blank' + i} className="room-cal-day is-blank"></span>;
          const isPast = day < today;
          const isBooked = bookedSet.has(day);
          const cls = ['room-cal-day'];
          if (isPast) cls.push('is-past');
          if (isBooked) cls.push('is-booked');
          return <span key={day} className={cls.join(' ')}>{Number(day.slice(8, 10))}</span>;
        })}
      </div>
      <div className="room-cal-legend">
        <span><i className="room-cal-swatch is-available"></i> Available</span>
        <span><i className="room-cal-swatch is-booked"></i> Booked</span>
        <span><i className="room-cal-swatch is-past"></i> Past</span>
      </div>
    </div>
  );
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

function compressImageDataUrl(dataUrl, done) {
  const src = String(dataUrl || '');
  if (!src.startsWith('data:image/')) { done(src); return; }
  const img = new Image();
  img.onload = function () {
    try {
      const scale = Math.min(1, IMAGE_MAX_DIMENSION / Math.max(img.width, img.height));
      const canvas = document.createElement('canvas');
      canvas.width = Math.max(1, Math.round(img.width * scale));
      canvas.height = Math.max(1, Math.round(img.height * scale));
      const ctx = canvas.getContext('2d');
      ctx.fillStyle = '#181714';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
      let quality = 0.82;
      let out = canvas.toDataURL('image/jpeg', quality);
      while (out.length > IMAGE_MAX_BYTES && quality > 0.4) {
        quality -= 0.12;
        out = canvas.toDataURL('image/jpeg', quality);
      }
      done(out.length < src.length ? out : src);
    } catch (e) { done(src); }
  };
  img.onerror = function () { done(src); };
  img.src = src;
}

/** Open a file picker and return an image data-URL. */
function pickImageFile(onPicked) {
  const handle = (url) => {
    if (typeof onPicked !== 'function') return;
    compressImageDataUrl(url, onPicked);
  };
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.style.display = 'none';
  document.body.appendChild(input);
  input.addEventListener('change', function () {
    const file = input.files && input.files[0];
    if (!file) {
      if (input.parentNode) input.parentNode.removeChild(input);
      return;
    }
    const reader = new FileReader();
    reader.onload = function () {
      handle(String(reader.result || ''));
      if (input.parentNode) input.parentNode.removeChild(input);
    };
    reader.onerror = function () {
      if (input.parentNode) input.parentNode.removeChild(input);
    };
    reader.readAsDataURL(file);
  });
  input.click();
}

// No name: adding a room derives it from the category (see nextRoomNameFor).
function createEmptyRoomForm() {
  return { category: '', status: 'Available', price: '', desc: '', img: '' };
}

/* Mirrors App\Support\HotelRoomDefaults: each category numbers from its own hundreds
   block, and the next room takes the highest number already used there plus one. Only
   a preview — the server recomputes it on save, so the two cannot disagree on disk. */
/* Replaced by the blocks the server sends with the categories, which is what a team's
   own categories number from — these five only stand in until that lands. */
let CATEGORY_FLOORS = { Classic: 1, Superior: 2, Deluxe: 3, Premium: 4, Family: 5 };

function setCategoryFloors(map) {
  if (map && Object.keys(map).length) CATEGORY_FLOORS = map;
}

function nextRoomNameFor(rooms, category) {
  const cat = normalizeRoomCategory(category);
  const floor = CATEGORY_FLOORS[cat] || 1;
  let highest = floor * 100;
  const pattern = new RegExp('^' + cat + '\\s+(\\d+)$', 'i');

  (rooms || []).forEach(room => {
    if (normalizeRoomCategory(room.category || room.label) !== cat) return;
    const match = pattern.exec(String(room.name || '').trim());
    if (match) highest = Math.max(highest, parseInt(match[1], 10));
  });

  return cat + ' ' + (highest + 1);
}

/* requireName is false when adding: the name is derived from the category there, so
   there is no field for anyone to leave blank. The edit form still takes one. */
/* A room with no photo of its own — every seeded room starts that way — would render
   <img src=""> and leave a blank box in the table. Same stand-in the hotel site uses,
   seeded by the room so it keeps the same photo between renders. */
function roomCardImg(room) {
  if (room && room.img) return room.img;
  const seed = encodeURIComponent((room && (room.id || room.name)) || 'room');
  return 'https://picsum.photos/seed/room-' + seed + '/800/600.jpg';
}

function validateRoomForm(form, requireName = true) {
  const errors = {};
  if (requireName && !String(form.name || '').trim()) errors.name = 'Room name is required.';
  if (!String(form.category || '').trim()) errors.category = 'Room category is required.';
  const price = parseFloat(String(form.price || '').replace(/,/g, ''));
  if (!String(form.price || '').trim() || !Number.isFinite(price) || price <= 0) {
    errors.price = 'Enter a valid price.';
  }
  return errors;
}

/* Adding a room is the rare move; looking one up is the common one — so the form
   lives in a modal and the page leads with the inventory table. Same POST, same
   validation the inline form used: only where it renders changed. */
function AddRoomModal({ rooms, categories, onClose, onAdded }) {
  const [form, setForm] = useState(createEmptyRoomForm);
  const [errors, setErrors] = useState({});
  const [imgPreview, setImgPreview] = useState('');
  const [saving, setSaving] = useState(false);

  // Preview only — HotelRoomDefaults::nextNameFor() decides the real one on save.
  const nextRoomName = form.category ? nextRoomNameFor(rooms || [], form.category) : '';

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const fieldLabel = {
    fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase',
    color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem',
  };

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  const resetForm = () => {
    setForm(createEmptyRoomForm());
    setErrors({});
    setImgPreview('');
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const nextErrors = validateRoomForm(form, false);
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length) return;

    setSaving(true);
    fetch('/students/hotel/rooms', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      // No name: the server numbers the room from its category (Classic 110 -> 111).
      body: JSON.stringify({
        category: form.category,
        price: parseInt(String(form.price).replace(/,/g, ''), 10),
        description: String(form.desc || '').trim(),
        image: form.img || '',
      }),
    })
      .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e)))
      .then(data => {
        if (data.room && typeof onAdded === 'function') onAdded(data.room);
        resetForm();
        onClose();
        if (window.Swal) {
          window.Swal.fire({
            icon: 'success',
            title: 'Room Added!',
            text: data.room.name + ' has been added to the inventory.',
            background: 'var(--card, #181714)',
            color: 'var(--fg, #f5f0e8)',
            iconColor: 'var(--success, #4ade80)',
            confirmButtonColor: 'var(--accent, #c9a84c)',
            confirmButtonText: 'Great!',
            timer: 3000,
            timerProgressBar: true,
          });
        }
      })
      .catch((err) => {
        const msg = (err && err.message) ? err.message : 'Failed to save. Please try again.';
        if (window.Swal) {
          window.Swal.fire({
            icon: 'error', title: 'Error', text: msg,
            background: 'var(--card, #181714)', color: 'var(--fg, #f5f0e8)', iconColor: 'var(--danger, #fb7185)', confirmButtonColor: 'var(--accent, #c9a84c)',
          });
        } else {
          // Category, not name: the add form has no name field to show it under.
          setErrors({ category: msg });
        }
      })
      .finally(() => setSaving(false));
  };

  // Reset before closing so reopening never shows a stale draft.
  const handleCancel = () => { resetForm(); onClose(); };

  const handleImagePick = () => {
    pickImageFile((url) => {
      if (!url) return;
      update('img', url);
      setImgPreview(url);
    });
  };

  const errorText = (key) => (
    errors[key]
      ? <p style={{ margin: '0.35rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.72rem' }}>{errors[key]}</p>
      : null
  );

  return (
    <div className="room-modal-overlay" onClick={onClose} role="dialog" aria-modal="true">
      {/* Wider than the shared 480px: the two-column row and the upload box are
          cramped at that width. */}
      <div className="room-modal" style={{ maxWidth: 560 }} onClick={e => e.stopPropagation()}>
        <div style={{ padding: '1.5rem', position: 'relative' }}>
          <button type="button" className="room-modal-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Inventory</p>
          <h2 className="font-display" style={{ fontSize: '1.5rem', margin: '0 0 0.35rem', color: 'var(--fg)' }}>Add Room</h2>
          <p className="rm-panel-desc">A new room joins the inventory as soon as you save it.</p>

          <form onSubmit={handleSubmit} className="rm-form-grid" noValidate>
            <div>
              <label style={fieldLabel}>Room Name</label>
              {/* Not typed: the server numbers a new room from its category. This only
                  previews what it will be called, so the name cannot drift from the
                  sequence. The server recomputes it on save either way. */}
              <div className="booking-input" style={{ color: form.category ? 'var(--fg)' : 'var(--fg-muted)', cursor: 'default', display: 'flex', alignItems: 'center', gap: 8 }}>
                {form.category
                  ? <><i className="fa-solid fa-hashtag" style={{ fontSize: '0.7rem', color: 'var(--accent)' }}></i>{nextRoomName}</>
                  : 'Pick a category to see the room number'}
              </div>
            </div>

            <div className="rm-form-row">
              <div>
                <label style={fieldLabel}>Room Category *</label>
                <select
                  className="booking-input" value={form.category} onChange={e => update('category', e.target.value)}
                  style={Object.assign({ colorScheme: 'dark', background: 'rgba(255,255,255,0.03)', color: form.category ? 'var(--fg)' : 'var(--fg-muted)' }, errors.category ? { borderColor: '#f43f5e' } : {})}
                >
                  <option value="" style={{ background: 'var(--card, #181714)', color: 'var(--fg-muted)' }}>Select category</option>
                  {(categories && categories.length ? categories : DEFAULT_ROOM_CATEGORIES).map(c => <option key={c} value={c} style={{ background: 'var(--card, #181714)', color: 'var(--fg)' }}>{c}</option>)}
                </select>
                {errorText('category')}
              </div>
              <div>
                <label style={fieldLabel}>Status</label>
                <div className="booking-input" style={{ color: 'var(--success, #4ade80)', fontWeight: 600, cursor: 'default', display: 'flex', alignItems: 'center', gap: 6 }}>
                  <span style={{ width: 7, height: 7, borderRadius: '50%', background: 'var(--success, #4ade80)', display: 'inline-block', flexShrink: 0 }}></span>
                  Available
                </div>
              </div>
            </div>

            <div>
              <label style={fieldLabel}>Price *</label>
              <input
                type="number" min="1" step="1" className="booking-input" placeholder="e.g. 4500"
                value={form.price} onChange={e => update('price', e.target.value)}
                style={errors.price ? { borderColor: '#f43f5e' } : undefined}
              />
              {errorText('price')}
            </div>

            <div>
              <label style={fieldLabel}>Description</label>
              <textarea
                className="booking-input" rows={3} placeholder="Short description of the room..."
                value={form.desc} onChange={e => update('desc', e.target.value)}
                style={{ resize: 'vertical', minHeight: 88 }}
              />
            </div>

            <div>
              <label style={fieldLabel}>Room Image</label>
              <div
                onClick={handleImagePick}
                style={{ border: '1.5px dashed var(--border)', borderRadius: 8, cursor: 'pointer', overflow: 'hidden', background: 'rgba(255,255,255,0.02)', transition: 'border-color 0.2s' }}
                onMouseEnter={e => e.currentTarget.style.borderColor = 'var(--accent)'}
                onMouseLeave={e => e.currentTarget.style.borderColor = 'var(--border)'}
              >
                {imgPreview ? (
                  <img src={imgPreview} alt="Room preview" style={{ width: '100%', height: 140, objectFit: 'cover', display: 'block' }} />
                ) : (
                  <div style={{ height: 100, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 8, color: 'var(--fg-muted)' }}>
                    <i className="fa-solid fa-cloud-arrow-up" style={{ fontSize: '1.4rem', color: 'var(--accent)', opacity: 0.7 }}></i>
                    <span style={{ fontSize: '0.75rem' }}>Click to upload image</span>
                  </div>
                )}
              </div>
              {imgPreview && (
                <button type="button" onClick={() => { update('img', ''); setImgPreview(''); }}
                  style={{ marginTop: '0.4rem', background: 'none', border: 'none', color: 'var(--danger, #fb7185)', fontSize: '0.72rem', cursor: 'pointer', padding: 0, fontFamily: 'var(--font-body, Outfit, sans-serif)' }}>
                  <i className="fa-solid fa-xmark" style={{ marginRight: 4 }}></i>Remove image
                </button>
              )}
            </div>

            <div style={{ display: 'flex', gap: '0.75rem', marginTop: '0.35rem', flexWrap: 'wrap' }}>
              <button type="submit" className="btn-primary" disabled={saving}>
                <i className="fa-solid fa-plus" style={{ fontSize: '0.7rem' }}></i> {saving ? 'Saving…' : 'Add Room'}
              </button>
              <button type="button" className="btn-outline" onClick={handleCancel} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}

function ManageRoomPanel({ rooms, categories, onSubmit, onRoomUpdated, onRenameCategory, onToast }) {
  // The inventory list that used to be its own Room Availability section. Adding a
  // room and looking one up are the same job, so they share a screen now.
  const [tab, setTab] = useState('All');
  const [selectedRoomId, setSelectedRoomId] = useState(null);
  const [page, setPage] = useState(1);
  const [addOpen, setAddOpen] = useState(false);

  const list = rooms || [];
  const categoryNames = (categories && categories.length) ? categories : DEFAULT_ROOM_CATEGORIES;
  const tabs = ['All', ...categoryNames];
  const filtered = tab === 'All' ? list : list.filter(r => normalizeRoomCategory(r.category || r.label) === tab);
  const selectedRoom = list.find(r => r.id === selectedRoomId) || null;

  // Fifty rooms is a long scroll, so the table pages. safePage rather than page so
  // switching to a shorter category tab cannot strand the view past the last page.
  const PER_PAGE = 5;
  const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageRooms = filtered.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') setSelectedRoomId(null); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, []);

  /* Renaming a tab renames the category for the whole team — the rooms in it are
     renamed with it, and the hotel site's own Rooms tabs follow on their next poll. */
  const handleRenameCategory = (from) => {
    if (typeof onRenameCategory !== 'function') return;
    const next = window.prompt(`Rename "${from}" to`, from);
    if (next == null) return;
    const clean = String(next).trim();
    if (!clean || clean === from) return;

    Promise.resolve(onRenameCategory(from, clean)).then((renamed) => {
      if (!renamed) {
        if (onToast) onToast('That name is already taken. Pick another.');
        return;
      }
      // Follow the rename: the tab this panel was filtering on is gone by that name.
      setTab(prev => (prev === from ? renamed : prev));
      if (onToast) onToast(`${from} is now ${renamed}`);
    });
  };

  return (
    <div className="rm-panel" style={{ maxWidth: '100%' }}>
      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '1rem', flexWrap: 'wrap' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Inventory</p>
          <h3>Manage Room</h3>
          <p className="rm-panel-desc">Every room in the hotel. Add one, or update a room to edit its details and check its booked dates.</p>
        </div>
        <button type="button" className="btn-primary" onClick={() => setAddOpen(true)}>
          <i className="fa-solid fa-plus" style={{ fontSize: '0.7rem' }}></i> Add Room
        </button>
      </div>

      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.5rem', marginBottom: '1.15rem' }}>
        {tabs.map(t => {
          const count = t === 'All' ? list.length : list.filter(r => normalizeRoomCategory(r.category || r.label) === t).length;
          return (
            <button key={t} type="button" onClick={() => { setTab(t); setPage(1); }} className={`room-card-tab${tab === t ? ' active' : ''}`}>
              {t} ({count})
              {/* "All" is not a category, so it is the one tab with nothing to rename. */}
              {onRenameCategory && t !== 'All' && (
                <span
                  role="button"
                  tabIndex={0}
                  title={`Rename ${t}`}
                  aria-label={`Rename ${t}`}
                  onClick={(e) => { e.stopPropagation(); handleRenameCategory(t); }}
                  onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); e.stopPropagation(); handleRenameCategory(t); } }}
                  style={{ marginLeft: 7, opacity: 0.75, cursor: 'pointer' }}
                >
                  <i className="fa-solid fa-pen" style={{ fontSize: '0.72em' }}></i>
                </span>
              )}
            </button>
          );
        })}
      </div>

      {filtered.length === 0 ? (
        <p style={{ color: 'var(--fg-muted)', fontSize: '0.85rem', padding: '1.5rem 0', textAlign: 'center' }}>
          {list.length === 0 ? 'No rooms yet. Use Add Room to create the first one.' : 'No rooms in this category yet.'}
        </p>
      ) : (
        <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
          <table className="rm-table">
            <thead>
              <tr>
                <th style={{ width: 92 }}>Image</th>
                <th>Room</th>
                <th>Room Category</th>
                <th>Description</th>
                <th style={{ width: 110 }}>Action</th>
              </tr>
            </thead>
            <tbody>
              {pageRooms.map(room => (
                <tr key={room.id}>
                  <td>
                    <img
                      src={roomCardImg(room)}
                      alt={room.name}
                      style={{ width: 72, height: 52, objectFit: 'cover', borderRadius: 6, display: 'block', background: 'var(--bg-warm, #12110f)' }}
                    />
                  </td>
                  <td>
                    <span style={{ display: 'block', color: 'var(--fg)', fontWeight: 600 }}>{room.name}</span>
                    <span style={{ display: 'block', color: 'var(--accent-light)', fontFamily: 'var(--font-display, Playfair Display, serif)', fontSize: '0.82rem', marginTop: 2 }}>
                      {formatPeso(room.price)}
                    </span>
                  </td>
                  <td>{room.label || room.category}</td>
                  <td style={{ whiteSpace: 'normal', minWidth: 220, maxWidth: 380 }}>
                    {room.desc || <span style={{ opacity: 0.45 }}>No description yet.</span>}
                  </td>
                  <td>
                    <button
                      type="button"
                      className="btn-outline"
                      style={{ fontSize: '0.68rem', padding: '0.4rem 0.8rem' }}
                      onClick={() => setSelectedRoomId(room.id)}
                    >
                      <i className="fa-solid fa-pen" style={{ fontSize: '0.65rem' }}></i> Update
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {totalPages > 1 && (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: '0.85rem', gap: '0.5rem', flexWrap: 'wrap' }}>
          <span style={{ fontSize: '0.75rem', color: 'var(--fg-muted)' }}>
            Showing {(safePage - 1) * PER_PAGE + 1}–{Math.min(safePage * PER_PAGE, filtered.length)} of {filtered.length}
          </span>
          <div style={{ display: 'flex', gap: '0.35rem', flexWrap: 'wrap' }}>
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

      {addOpen && (
        <AddRoomModal
          rooms={list}
          categories={categoryNames}
          onClose={() => setAddOpen(false)}
          onAdded={onSubmit}
        />
      )}

      {selectedRoom && (
        <EditRoomModal
          room={selectedRoom}
          categories={categoryNames}
          onClose={() => setSelectedRoomId(null)}
          onSaved={onRoomUpdated}
        />
      )}
    </div>
  );
}

/* The Update action from the rooms table. Edits the room's own fields; the booked
   dates below it stay read-only — they belong to bookings, not to the room. */
function EditRoomModal({ room, categories, onClose, onSaved }) {
  const [form, setForm] = useState(() => ({
    name: room.name || '',
    category: normalizeRoomCategory(room.category || room.label),
    price: String(room.price || ''),
    desc: room.desc || '',
    img: room.img || '',
  }));
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);

  const fieldLabel = {
    fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase',
    color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem',
  };

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const handleSubmit = (e) => {
    e.preventDefault();
    const nextErrors = validateRoomForm(form);
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length) return;

    setSaving(true);
    // room.dbId is the hotel_rooms primary key; room.id is the front-end's "db-N".
    fetch('/students/hotel/rooms/' + room.dbId, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({
        name: String(form.name).trim(),
        category: form.category,
        price: parseInt(String(form.price).replace(/,/g, ''), 10),
        description: String(form.desc || '').trim(),
        // Handed back as-is when untouched: the server collapses an existing
        // /storage/... URL to the path it already holds rather than re-uploading.
        image: form.img || '',
      }),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        if (data.room && typeof onSaved === 'function') onSaved(data.room);
        onClose();
        if (window.Swal) {
          window.Swal.fire({
            icon: 'success',
            title: 'Room Updated!',
            text: data.room.name + ' has been saved.',
            background: 'var(--card, #181714)', color: 'var(--fg, #f5f0e8)', iconColor: 'var(--success, #4ade80)',
            confirmButtonColor: 'var(--accent, #c9a84c)', confirmButtonText: 'Great!',
            timer: 3000, timerProgressBar: true,
          });
        }
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Failed to save. Please try again.';
        if (window.Swal) {
          window.Swal.fire({
            icon: 'error', title: 'Error', text: msg,
            background: 'var(--card, #181714)', color: 'var(--fg, #f5f0e8)', iconColor: 'var(--danger, #fb7185)', confirmButtonColor: 'var(--accent, #c9a84c)',
          });
        } else {
          setErrors({ name: msg });
        }
      })
      .finally(() => setSaving(false));
  };

  const errorText = (key) => (
    errors[key]
      ? <p style={{ margin: '0.35rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.72rem' }}>{errors[key]}</p>
      : null
  );

  return (
    <div className="room-modal-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" onClick={e => e.stopPropagation()}>
        <div className="room-modal-img">
          <img src={form.img || roomCardImg(room)} alt={room.name} />
          <button type="button" className="room-modal-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div style={{ padding: '1.5rem' }}>
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
            Update Room
          </p>
          <h2 className="font-display" style={{ fontSize: '1.5rem', marginBottom: '1.1rem', color: 'var(--fg)' }}>{room.name}</h2>

          <form onSubmit={handleSubmit} className="rm-form-grid" noValidate>
            <div>
              <label style={fieldLabel}>Room Name *</label>
              <input
                type="text" className="booking-input" value={form.name}
                onChange={e => update('name', e.target.value)}
                style={errors.name ? { borderColor: '#f43f5e' } : undefined}
              />
              {errorText('name')}
            </div>

            <div className="rm-form-row">
              <div>
                <label style={fieldLabel}>Room Category *</label>
                <select
                  className="booking-input" value={form.category} onChange={e => update('category', e.target.value)}
                  style={Object.assign({ colorScheme: 'dark', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)' }, errors.category ? { borderColor: '#f43f5e' } : {})}
                >
                  <option value="" style={{ background: 'var(--card, #181714)', color: 'var(--fg-muted)' }}>Select category</option>
                  {(categories && categories.length ? categories : DEFAULT_ROOM_CATEGORIES).map(c => <option key={c} value={c} style={{ background: 'var(--card, #181714)', color: 'var(--fg)' }}>{c}</option>)}
                </select>
                {errorText('category')}
              </div>
              <div>
                <label style={fieldLabel}>Price *</label>
                <input
                  type="number" min="1" step="1" className="booking-input" value={form.price}
                  onChange={e => update('price', e.target.value)}
                  style={errors.price ? { borderColor: '#f43f5e' } : undefined}
                />
                {errorText('price')}
              </div>
            </div>

            <div>
              <label style={fieldLabel}>Description</label>
              <textarea
                className="booking-input" rows={3} value={form.desc}
                onChange={e => update('desc', e.target.value)}
                style={{ resize: 'vertical', minHeight: 88 }}
              />
            </div>

            <div>
              <label style={fieldLabel}>Room Image</label>
              <button
                type="button"
                onClick={() => pickImageFile(url => { if (url) update('img', url); })}
                className="btn-outline"
                style={{ fontSize: '0.7rem', padding: '0.5rem 0.9rem' }}
              >
                <i className="fa-solid fa-cloud-arrow-up" style={{ fontSize: '0.7rem' }}></i> Replace image
              </button>
            </div>

            <div style={{ display: 'flex', gap: '0.75rem', marginTop: '0.35rem', flexWrap: 'wrap' }}>
              <button type="submit" className="btn-primary" disabled={saving}>
                <i className="fa-solid fa-floppy-disk" style={{ fontSize: '0.7rem' }}></i> {saving ? 'Saving…' : 'Save Changes'}
              </button>
              <button type="button" className="btn-outline" onClick={onClose} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>Cancel</button>
            </div>
          </form>

          <p style={{ ...fieldLabel, margin: '1.35rem 0 0.5rem' }}>Availability</p>
          <RoomAvailabilityCalendar ranges={room.bookedRanges} />
        </div>
      </div>
    </div>
  );
}

/* 'Aug 12, 2026 - 2:00 PM' from a full ISO timestamp. */
function formatStamp(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
    + ' · ' + d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

/*
 * Read-only view of one stay. The table carries only what Room Management scans by
 * (who, where, when, status), so the money detail the Total and Payment columns used
 * to abbreviate lives here in full instead.
 */
function GuestDetailsModal({ room, onClose }) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const res = room && room.reservation;
  if (!res) return null;

  const label = { fontSize: '0.6rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', marginBottom: '0.15rem' };
  const value = { margin: 0, color: 'var(--fg)', fontSize: '0.85rem' };
  const sectionTitle = {
    fontSize: '0.62rem', fontWeight: 700, letterSpacing: '0.14em', textTransform: 'uppercase',
    color: 'var(--accent)', margin: '1.15rem 0 0.55rem', paddingBottom: '0.3rem',
    borderBottom: '1px solid var(--border)',
  };
  const line = { display: 'flex', justifyContent: 'space-between', gap: '1rem', fontSize: '0.83rem', color: 'var(--fg-muted)', padding: '0.22rem 0' };
  const amt = { color: 'var(--fg)', fontVariantNumeric: 'tabular-nums', whiteSpace: 'nowrap' };

  const roomTotal = Number(res.totalDue) || 0;
  const service = Number(res.roomServiceTotal) || 0;
  const serviceCount = Number(res.roomServiceCount) || 0;
  const extras = Number(res.otherCharges) || 0;
  const addonsTotal = Number(res.addonsTotal) || 0;
  const addonsCount = Number(res.addonsCount) || 0;
  const grand = Number(res.grandTotal) || (roomTotal + service + addonsTotal + extras);
  const paid = Number(res.amountPaid) || 0;
  const outstanding = res.outstanding != null ? Number(res.outstanding) : Math.max(0, grand - paid);
  const payments = res.payments || [];

  return (
    <div className="room-modal-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" onClick={e => e.stopPropagation()} style={{ maxWidth: 560 }}>
        <div style={{ padding: '1.4rem 1.6rem 1rem', borderBottom: '1px solid var(--border)', display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '1rem' }}>
          <div>
            <p style={{ margin: 0, color: 'var(--accent)', fontSize: '0.62rem', letterSpacing: '0.14em', textTransform: 'uppercase' }}>Guest</p>
            <h2 className="font-display" style={{ margin: '0.25rem 0 0.4rem', fontSize: '1.35rem', color: 'var(--fg)' }}>
              {res.fullName || 'Guest'}
            </h2>
            <span className={`room-status-badge ${bookingStatusClass(res.status)}`} style={{ position: 'static' }}>{res.status}</span>
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

        <div style={{ padding: '0.25rem 1.6rem 1.6rem' }}>
          <p style={sectionTitle}>Guest</p>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '0.55rem 1rem' }}>
            <div><p style={label}>Full name</p><p style={value}>{res.fullName || '—'}</p></div>
            <div><p style={label}>Contact no.</p><p style={value}>{res.contactNo || '—'}</p></div>
            <div><p style={label}>Email</p><p style={{ ...value, overflowWrap: 'anywhere' }}>{res.email || '—'}</p></div>
            <div><p style={label}>ID number</p><p style={value}>{res.idNumber || '—'}</p></div>
          </div>

          <p style={sectionTitle}>Stay</p>
          <div style={{ display: 'grid', gridTemplateColumns: 'repeat(2, 1fr)', gap: '0.55rem 1rem' }}>
            <div><p style={label}>Room</p><p style={value}>{room.name} · {room.label || room.category}</p></div>
            <div><p style={label}>Rate / 12 hrs</p><p style={value}>{formatPeso(res.roomRate || room.price)}</p></div>
            <div><p style={label}>Check-in</p><p style={value}>{formatCheckIn(res.checkIn, res.checkInTime)}</p></div>
            <div><p style={label}>Check-out</p><p style={value}>{res.checkOut || '—'}</p></div>
            <div><p style={label}>Booked</p><p style={value}>{formatStamp(res.reservedAt)}</p></div>
            <div><p style={label}>Arrived</p><p style={value}>{formatStamp(res.arrivedAt)}</p></div>
            <div><p style={label}>Checked in</p><p style={value}>{formatStamp(res.checkedInAt)}</p></div>
            <div><p style={label}>Booked by</p><p style={value}>{res.bookedBy || '—'}</p></div>
          </div>

          {res.notes && (
            <>
              <p style={sectionTitle}>Notes</p>
              <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.84rem', lineHeight: 1.6, whiteSpace: 'pre-wrap' }}>{res.notes}</p>
            </>
          )}

          <p style={sectionTitle}>Charges</p>
          <div style={line}><span>Room charge</span><span style={amt}>{formatPeso(roomTotal)}</span></div>
          <div style={line}>
            <span>Room service{serviceCount > 0 ? ` (${serviceCount} order${serviceCount === 1 ? '' : 's'})` : ''}</span>
            <span style={amt}>{formatPeso(service)}</span>
          </div>
          {addonsCount > 0 && (
            <div style={line}>
              <span>Add-ons ({addonsCount} item{addonsCount === 1 ? '' : 's'})</span>
              <span style={amt}>{formatPeso(addonsTotal)}</span>
            </div>
          )}
          <div style={line}><span>Other charges</span><span style={amt}>{formatPeso(extras)}</span></div>
          <div style={{ ...line, borderTop: '2px solid var(--accent)', marginTop: '0.6rem', paddingTop: '0.6rem', color: 'var(--fg)', fontWeight: 700 }}>
            <span>Total</span>
            <span style={{ ...amt, color: 'var(--accent-light)', fontFamily: 'var(--font-display, Playfair Display, serif)', fontSize: '1.1rem' }}>{formatPeso(grand)}</span>
          </div>
          <div style={{ ...line, color: 'var(--fg)', fontWeight: 600 }}><span>Paid</span><span style={amt}>{formatPeso(paid)}</span></div>
          <div style={line}>
            <span>Balance</span>
            <span style={{ ...amt, color: outstanding > 0 ? 'var(--danger, #fb7185)' : 'var(--fg)', fontWeight: 700 }}>{formatPeso(outstanding)}</span>
          </div>

          <p style={sectionTitle}>Payments</p>
          {payments.length === 0 ? (
            <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.82rem' }}>Nothing has been paid on this stay yet.</p>
          ) : (
            payments.map(p => (
              <div key={p.id} style={{ padding: '0.5rem 0', borderBottom: '1px solid rgba(42,38,33,0.5)' }}>
                <div style={{ ...line, padding: 0 }}>
                  <span style={{ color: 'var(--fg)' }}>{p.type} · {p.method}</span>
                  <span style={amt}>{formatPeso(p.amountPaid)}</span>
                </div>
                <p style={{ margin: '0.2rem 0 0', fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                  {formatStamp(p.paidAt)}
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

function GuestDetailsPanel({ rooms, onBookingAction, onToast }) {
  const now = useNow(1000);
  // The View action. Read-only, so it needs no fetch: the row already holds the whole
  // reservation payload the modal renders. Keyed by room id so a poll refresh swaps
  // in the updated row rather than leaving a stale snapshot open.
  const [detailsRoomId, setDetailsRoomId] = useState(null);
  // `reservation` is only ever projected from an open booking (see
  // HotelRoom::activeBooking()), so its presence alone means the room has a live guest
  // — hotel_rooms.status is housekeeping-only now and no longer part of this filter.
  const occupied = (rooms || []).filter(r => r.reservation);
  const awaitingCheckIn = occupied.filter(r => r.reservation.status !== 'Checked In').length;
  // Resolved from the live list, so an open modal follows the 8s poll instead of
  // holding the row as it looked when it was opened.
  const detailsRoom = occupied.find(r => r.id === detailsRoomId) || null;

  // Check-in moves the booking only — the room's own status is untouched.
  const checkInGuest = (room) => {
    if (typeof onBookingAction !== 'function' || !room.reservation) return;
    onBookingAction(room.reservation.bookingId, 'check_in');
    if (onToast) onToast(`${room.name} checked in.`);
  };

  /* No nowrap and no fixed widths: the table has to fit the page rather than force a
     horizontal scrollbar, so long headers wrap instead of pushing the table wider.
     Cells that must stay on one line (dates, countdown) opt in via tdTight. */
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

  if (!occupied.length) {
    return (
      <div className="rm-panel" style={{ maxWidth: '100%' }}>
        <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Occupancy</p>
        <h3>Guest Details</h3>
        <p className="rm-panel-desc">No rooms with registered guests at this time.</p>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '2.5rem 0' }}>
          <div style={{ textAlign: 'center', color: 'var(--fg-muted)' }}>
            <i className="fa-solid fa-door-open" style={{ fontSize: '2rem', opacity: 0.25, display: 'block', marginBottom: '0.75rem' }}></i>
            <p style={{ fontSize: '0.82rem' }}>All rooms are currently vacant.</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="rm-panel" style={{ maxWidth: '100%' }}>
      <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Occupancy</p>
      <h3>Guest Details</h3>
      <p className="rm-panel-desc">
        {occupied.length} room{occupied.length > 1 ? 's' : ''} with a registered guest
        {awaitingCheckIn > 0 ? ` · ${awaitingCheckIn} awaiting check-in` : ''}.
      </p>
      <div style={{ borderRadius: 10, border: '1px solid var(--border)' }}>
        <table style={{ width: '100%', tableLayout: 'fixed', borderCollapse: 'collapse', fontFamily: 'var(--font-body, Outfit, sans-serif)' }}>
          <thead>
            <tr>
              <th style={{ ...thStyle, width: '16%' }}>Guest Name</th>
              <th style={{ ...thStyle, width: '13%' }}>Room</th>
              <th style={{ ...thStyle, width: '16%' }}>Contact</th>
              <th style={{ ...thStyle, width: '13%' }}>Check-In</th>
              <th style={{ ...thStyle, width: '10%' }}>Check-Out</th>
              <th style={{ ...thStyle, width: '11%' }}>Time Remaining</th>
              <th style={{ ...thStyle, width: '10%' }}>Status</th>
              <th style={{ ...thStyle, width: '11%' }}>Action</th>
            </tr>
          </thead>
          <tbody>
            {occupied.map((room, idx) => {
              const res = room.reservation;
              const rowBg = idx % 2 === 0 ? 'transparent' : 'rgba(255,255,255,0.015)';
              const canCheckIn = res.status !== 'Checked In';
              const remaining = remainingStay(res, now);
              return (
                <tr key={room.id} style={{ background: rowBg }}>
                  <td style={{ ...tdStyle, color: 'var(--fg)', fontWeight: 600, overflowWrap: 'anywhere' }}>
                    <span style={{ display: 'block' }}>{res.fullName || '—'}</span>
                    <span style={{ display: 'block', fontSize: '0.7rem', color: 'var(--fg-muted)', fontWeight: 400 }}>{res.idNumber || ''}</span>
                  </td>
                  <td style={tdStyle}>
                    <span style={{ display: 'block', fontSize: '0.6rem', color: 'var(--accent)', letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 2 }}>{room.label || room.category}</span>
                    <span style={{ color: 'var(--fg)', fontWeight: 500 }}>{room.name}</span>
                  </td>
                  <td style={{ ...tdStyle, overflowWrap: 'anywhere' }}>
                    <span style={{ display: 'block' }}>{res.contactNo || '—'}</span>
                    <span style={{ display: 'block', fontSize: '0.72rem' }}>{res.email || ''}</span>
                  </td>
                  <td style={tdStyle}>{formatCheckIn(res.checkIn, res.checkInTime)}</td>
                  <td style={tdTight}>{res.checkOut || '—'}</td>
                  <td style={{
                    ...tdTight,
                    color: STAY_TONE_COLORS[remaining.tone],
                    fontWeight: remaining.tone === 'idle' ? 400 : 600,
                    opacity: remaining.tone === 'idle' ? 0.6 : 1,
                    fontVariantNumeric: 'tabular-nums',
                  }}>
                    {remaining.text}
                  </td>
                  <td style={tdStyle}>
                    <span className={`room-status-badge ${bookingStatusClass(res.status)}`} style={{ position: 'static' }}>{res.status}</span>
                  </td>
                  <td style={tdStyle}>
                    <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.35rem' }}>
                      <button
                        type="button"
                        onClick={() => setDetailsRoomId(room.id)}
                        title="See everything recorded for this guest"
                        style={{ ...rowBtn, border: '1px solid var(--border)', background: 'transparent', color: 'var(--fg)' }}
                      >
                        <i className="fa-solid fa-eye" style={{ fontSize: '0.68rem' }}></i> View
                      </button>
                      {canCheckIn && (
                        <button
                          type="button"
                          onClick={() => checkInGuest(room)}
                          title="Check the guest in"
                          style={{ ...rowBtn, border: '1px solid var(--accent)', background: 'var(--accent)', color: 'var(--bg)' }}
                        >
                          <i className="fa-solid fa-right-to-bracket" style={{ fontSize: '0.68rem' }}></i> Check In
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

      {detailsRoom && (
        <GuestDetailsModal room={detailsRoom} onClose={() => setDetailsRoomId(null)} />
      )}
    </div>
  );
}

function RoomManagementPage({ initialNav, rooms, categories, onBack, onAddRoom, onRoomUpdated, onRenameCategory, onBookingAction, onToast }) {
  const activeNav = initialNav || 'manage-room';

  const handleAddRoom = (payload) => {
    if (typeof onAddRoom === 'function') onAddRoom(payload);
    if (onToast) onToast(`${payload.name} added to Rooms.`);
  };

  return (
    <div style={{ padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.1rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Staff Tools</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Room Management</h1>
        </div>
        <button type="button" className="btn-outline" onClick={onBack} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </button>
      </div>

      <div className="rm-row">
        <div className="rm-content">
          {activeNav === 'guest-details' ? (
            <GuestDetailsPanel rooms={rooms} onBookingAction={onBookingAction} onToast={onToast} />
          ) : (
            // Manage Room is the fallback: ?nav=rooms was the old Room Availability
            // section, whose room list lives here now, so an old link still lands
            // somewhere sensible instead of on a blank panel.
            <ManageRoomPanel rooms={rooms} categories={categories} onSubmit={handleAddRoom} onRoomUpdated={onRoomUpdated} onRenameCategory={onRenameCategory} onToast={onToast} />
          )}
        </div>
      </div>
    </div>
  );
}

function App() {
  const [rooms, setRooms] = useState([]);
  // The team's categories ride along with the rooms, so a category added while
  // customising the site's Rooms section shows up here on the next poll.
  const [categories, setCategories] = useState(DEFAULT_ROOM_CATEGORIES);
  const pendingWrites = useRef(0);

  const fetchRooms = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/rooms', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.rooms)) setRooms(data.rooms);
        if (Array.isArray(data.categories) && data.categories.length) {
          const names = data.categories.map(c => (typeof c === 'string' ? c : c.name)).filter(Boolean);
          const floors = {};
          data.categories.forEach(c => { if (c && c.name && c.floor) floors[c.name] = c.floor; });
          setRoomCategoryNames(names);
          setCategoryFloors(floors);
          setCategories(names);
        }
      })
      .catch(() => {});
  }, []);

  useEffect(() => {
    fetchRooms();
    // Not while the tab is in the background: every poll takes one of the database
    // connections Supabase's pooler shares between all of us (see render.yaml), and
    // nobody is reading a screen they cannot see. Focus brings it straight back.
    const id = setInterval(() => { if (!document.hidden) fetchRooms(); }, 12000);
    window.addEventListener('focus', fetchRooms);
    return () => { clearInterval(id); window.removeEventListener('focus', fetchRooms); };
  }, [fetchRooms]);

  const addRoom = useCallback((roomFromDb) => {
    setRooms(prev => [...prev, roomFromDb]);
  }, []);

  const replaceRoom = useCallback((roomFromDb) => {
    setRooms(prev => prev.map(r => (r.id === roomFromDb.id ? roomFromDb : r)));
  }, []);

  /* Renames a category for the whole team. The rooms in it are renamed with it on the
     server ("Classic 101" becomes "Standard 101"), so both come back here and on the
     hotel site's own Rooms tabs. Resolves to the stored spelling, or null when taken. */
  const renameCategory = useCallback((from, to) => {
    pendingWrites.current += 1;
    return fetch('/students/hotel/room-categories', {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ from, to }),
    })
      .then(r => r.json().then(data => (r.ok ? data : Promise.reject(data))))
      .then(data => {
        if (data && Array.isArray(data.categories)) {
          const names = data.categories.map(c => (typeof c === 'string' ? c : c.name)).filter(Boolean);
          const floors = {};
          data.categories.forEach(c => { if (c && c.name && c.floor) floors[c.name] = c.floor; });
          setRoomCategoryNames(names);
          setCategoryFloors(floors);
          setCategories(names);
        }
        if (data && Array.isArray(data.rooms)) setRooms(data.rooms);
        return data && data.category ? data.category.name : null;
      })
      .catch(() => null)
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  // Check-in and check-out are booking moves. The room status follows on the server,
  // and the response hands the updated room back so this page never has to infer it.
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

  return (
    <RoomManagementPage
      initialNav={window.HMS_ROOM_MGMT_INITIAL_NAV}
      rooms={rooms}
      categories={categories}
      onBack={() => { window.location.href = window.HMS_ROOMMANAGEMENT_URL; }}
      onAddRoom={addRoom}
      onRoomUpdated={replaceRoom}
      onRenameCategory={renameCategory}
      onBookingAction={bookingAction}
      onToast={(msg) => window.toast && window.toast(msg)}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
