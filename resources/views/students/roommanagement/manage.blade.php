@extends('students.builder.ops-shell')

@php $backRoute = 'students.roommanagement'; @endphp

@section('page-title', 'Room Management')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: 'Outfit', sans-serif; }
  .font-display { font-family: 'Playfair Display', serif; }
  .room-status-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent;
  }
  /* Only the two booking-lifecycle tones are used now — see bookingStatusClass(). */
  .room-status-badge.status-reserved { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .room-status-badge.status-occupied { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
  .rm-table { width: 100%; border-collapse: collapse; font-family: 'Outfit', sans-serif; }
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
    font-family: 'Outfit', sans-serif; font-weight: 600;
    font-size: 0.8rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.8rem 1.8rem; border: none; border-radius: 6px;
    cursor: pointer; transition: background 0.2s, transform 0.2s;
  }
  .btn-primary:hover { background: var(--accent-light); transform: translateY(-1px); }
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
  .booking-input {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: 'Outfit', sans-serif; font-size: 0.85rem;
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
  .rm-panel h3 { font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; margin: 0 0 0.35rem; color: var(--fg); }
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
    font-family: 'Outfit', sans-serif; font-size: 0.74rem; font-weight: 600;
    letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.5rem 0.9rem; border-radius: 100px;
    border: 1.5px solid var(--border); background: transparent;
    color: var(--fg-muted); cursor: pointer; transition: all 0.15s;
  }
  .room-card-tab:hover { border-color: var(--accent); color: var(--accent); }
  .room-card-tab.active { background: var(--accent); border-color: var(--accent); color: #0c0b09; }
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
    font-family: 'Outfit', sans-serif; font-size: 0.82rem; font-weight: 600;
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
    font-family: 'Outfit', sans-serif; font-size: 0.74rem; cursor: default;
    display: flex; align-items: center; justify-content: center;
  }
  .room-cal-day.is-blank { visibility: hidden; }
  .room-cal-day.is-past { color: var(--fg-muted); opacity: 0.35; }
  .room-cal-day.is-booked { background: rgba(244,63,94,0.14); color: #fb7185; }
  .room-cal-legend { display: flex; flex-wrap: wrap; gap: 0.9rem; margin-top: 0.75rem; }
  .room-cal-legend span {
    display: inline-flex; align-items: center; gap: 0.35rem;
    font-size: 0.68rem; color: var(--fg-muted);
  }
  .room-cal-swatch { width: 10px; height: 10px; border-radius: 3px; display: inline-block; background: rgba(255,255,255,0.08); }
  .room-cal-swatch.is-booked { background: #fb7185; }
  .room-cal-swatch.is-past { background: var(--fg-muted); opacity: 0.5; }
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

const ROOM_CATEGORIES = ['Classic', 'Superior', 'Deluxe', 'Premium', 'Family'];
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
function normalizeRoomCategory(value) {
  const raw = String(value || 'Classic').trim().toLowerCase();
  const match = ROOM_CATEGORIES.find(c => c.toLowerCase() === raw);
  return match || 'Classic';
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

const STAY_TONE_COLORS = { ok: 'var(--fg)', soon: '#fbbf24', over: '#fb7185', idle: 'var(--fg-muted)' };

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
const CATEGORY_FLOORS = { Classic: 1, Superior: 2, Deluxe: 3, Premium: 4, Family: 5 };

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

function ManageRoomPanel({ rooms, onSubmit, onCancel, onCloseModal, onRoomUpdated }) {
  const [form, setForm] = useState(createEmptyRoomForm);
  const [errors, setErrors] = useState({});
  const [imgPreview, setImgPreview] = useState('');
  const [saving, setSaving] = useState(false);
  // The inventory list that used to be its own Room Availability section. Adding a
  // room and looking one up are the same job, so they share a screen now.
  const [tab, setTab] = useState('All');
  const [selectedRoomId, setSelectedRoomId] = useState(null);

  const list = rooms || [];
  const tabs = ['All', ...ROOM_CATEGORIES];
  const filtered = tab === 'All' ? list : list.filter(r => normalizeRoomCategory(r.category || r.label) === tab);
  const selectedRoom = list.find(r => r.id === selectedRoomId) || null;
  // Preview only — HotelRoomDefaults::nextNameFor() decides the real one on save.
  const nextRoomName = form.category ? nextRoomNameFor(list, form.category) : '';

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') setSelectedRoomId(null); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, []);

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
        if (data.room && typeof onSubmit === 'function') onSubmit(data.room);
        resetForm();
        if (window.Swal) {
          if (typeof onCloseModal === 'function') onCloseModal();
          window.Swal.fire({
            icon: 'success',
            title: 'Room Added!',
            text: data.room.name + ' has been added to the inventory.',
            background: '#181714',
            color: '#f5f0e8',
            iconColor: '#4ade80',
            confirmButtonColor: '#c9a84c',
            confirmButtonText: 'Great!',
            timer: 3000,
            timerProgressBar: true,
          });
        } else if (typeof onCloseModal === 'function') {
          onCloseModal();
        }
      })
      .catch((err) => {
        const msg = (err && err.message) ? err.message : 'Failed to save. Please try again.';
        if (window.Swal) {
          window.Swal.fire({
            icon: 'error', title: 'Error', text: msg,
            background: '#181714', color: '#f5f0e8', iconColor: '#fb7185', confirmButtonColor: '#c9a84c',
          });
        } else {
          // Category, not name: the add form has no name field to show it under.
          setErrors({ category: msg });
        }
      })
      .finally(() => setSaving(false));
  };

  const handleCancel = () => { resetForm(); if (typeof onCancel === 'function') onCancel(); };

  const handleImagePick = () => {
    pickImageFile((url) => {
      if (!url) return;
      update('img', url);
      setImgPreview(url);
    });
  };

  const errorText = (key) => (
    errors[key]
      ? <p style={{ margin: '0.35rem 0 0', color: '#fb7185', fontSize: '0.72rem' }}>{errors[key]}</p>
      : null
  );

  return (
    <div className="rm-panel" style={{ maxWidth: '100%' }}>
      <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Inventory</p>
      <h3>Manage Room</h3>
      <p className="rm-panel-desc">Add a new room to the hotel inventory, and browse every room already in it.</p>

      <form onSubmit={handleSubmit} className="rm-form-grid" noValidate style={{ maxWidth: 520 }}>
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
              <option value="" style={{ background: '#181714', color: 'var(--fg-muted)' }}>Select category</option>
              {ROOM_CATEGORIES.map(c => <option key={c} value={c} style={{ background: '#181714', color: 'var(--fg)' }}>{c}</option>)}
            </select>
            {errorText('category')}
          </div>
          <div>
            <label style={fieldLabel}>Status</label>
            <div className="booking-input" style={{ color: '#4ade80', fontWeight: 600, cursor: 'default', display: 'flex', alignItems: 'center', gap: 6 }}>
              <span style={{ width: 7, height: 7, borderRadius: '50%', background: '#4ade80', display: 'inline-block', flexShrink: 0 }}></span>
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
              style={{ marginTop: '0.4rem', background: 'none', border: 'none', color: '#fb7185', fontSize: '0.72rem', cursor: 'pointer', padding: 0, fontFamily: 'Outfit, sans-serif' }}>
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

      <div style={{ marginTop: '2.25rem', paddingTop: '1.5rem', borderTop: '1px solid var(--border)' }}>
        <h3 style={{ margin: '0 0 0.35rem' }}>All Rooms</h3>
        <p className="rm-panel-desc">Every room in the hotel. Update one to edit its details or check its booked dates.</p>

        <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.5rem', marginBottom: '1.15rem' }}>
          {tabs.map(t => {
            const count = t === 'All' ? list.length : list.filter(r => normalizeRoomCategory(r.category || r.label) === t).length;
            return (
              <button key={t} type="button" onClick={() => setTab(t)} className={`room-card-tab${tab === t ? ' active' : ''}`}>
                {t} ({count})
              </button>
            );
          })}
        </div>

        {filtered.length === 0 ? (
          <p style={{ color: 'var(--fg-muted)', fontSize: '0.85rem', padding: '1.5rem 0', textAlign: 'center' }}>
            {list.length === 0 ? 'No rooms yet. Add the first one above.' : 'No rooms in this category yet.'}
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
                {filtered.map(room => (
                  <tr key={room.id}>
                    <td>
                      <img
                        src={room.img}
                        alt={room.name}
                        style={{ width: 72, height: 52, objectFit: 'cover', borderRadius: 6, display: 'block', background: '#12110f' }}
                      />
                    </td>
                    <td>
                      <span style={{ display: 'block', color: 'var(--fg)', fontWeight: 600 }}>{room.name}</span>
                      <span style={{ display: 'block', color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontSize: '0.82rem', marginTop: 2 }}>
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
      </div>

      {selectedRoom && (
        <EditRoomModal
          room={selectedRoom}
          onClose={() => setSelectedRoomId(null)}
          onSaved={onRoomUpdated}
        />
      )}
    </div>
  );
}

/* The Update action from the rooms table. Edits the room's own fields; the booked
   dates below it stay read-only — they belong to bookings, not to the room. */
function EditRoomModal({ room, onClose, onSaved }) {
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
            background: '#181714', color: '#f5f0e8', iconColor: '#4ade80',
            confirmButtonColor: '#c9a84c', confirmButtonText: 'Great!',
            timer: 3000, timerProgressBar: true,
          });
        }
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Failed to save. Please try again.';
        if (window.Swal) {
          window.Swal.fire({
            icon: 'error', title: 'Error', text: msg,
            background: '#181714', color: '#f5f0e8', iconColor: '#fb7185', confirmButtonColor: '#c9a84c',
          });
        } else {
          setErrors({ name: msg });
        }
      })
      .finally(() => setSaving(false));
  };

  const errorText = (key) => (
    errors[key]
      ? <p style={{ margin: '0.35rem 0 0', color: '#fb7185', fontSize: '0.72rem' }}>{errors[key]}</p>
      : null
  );

  return (
    <div className="room-modal-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" onClick={e => e.stopPropagation()}>
        <div className="room-modal-img">
          <img src={form.img || room.img} alt={room.name} />
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
                  <option value="" style={{ background: '#181714', color: 'var(--fg-muted)' }}>Select category</option>
                  {ROOM_CATEGORIES.map(c => <option key={c} value={c} style={{ background: '#181714', color: 'var(--fg)' }}>{c}</option>)}
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

function GuestDetailsPanel({ rooms, onBookingAction, onToast }) {
  const now = useNow(1000);
  // `reservation` is only ever projected from an open booking (see
  // HotelRoom::activeBooking()), so its presence alone means the room has a live guest
  // — hotel_rooms.status is housekeeping-only now and no longer part of this filter.
  const occupied = (rooms || []).filter(r => r.reservation);
  const awaitingCheckIn = occupied.filter(r => r.reservation.status !== 'Checked In').length;

  // Check-in moves the booking only — the room's own status is untouched.
  const checkInGuest = (room) => {
    if (typeof onBookingAction !== 'function' || !room.reservation) return;
    onBookingAction(room.reservation.bookingId, 'check_in');
    if (onToast) onToast(`${room.name} checked in.`);
  };

  const thStyle = {
    padding: '0.6rem 0.85rem', fontSize: '0.62rem', fontWeight: 700,
    letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)',
    borderBottom: '1px solid var(--border)', whiteSpace: 'nowrap',
    textAlign: 'left', background: 'rgba(255,255,255,0.02)',
  };
  const tdStyle = {
    padding: '0.7rem 0.85rem', fontSize: '0.8rem', color: 'var(--fg-muted)',
    borderBottom: '1px solid rgba(42,38,33,0.5)', verticalAlign: 'middle', whiteSpace: 'nowrap',
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
      <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', fontFamily: 'Outfit, sans-serif' }}>
          <thead>
            <tr>
              <th style={thStyle}>Guest Name</th>
              <th style={thStyle}>Room</th>
              <th style={thStyle}>Contact</th>
              <th style={thStyle}>Check-In</th>
              <th style={thStyle}>Check-Out</th>
              <th style={thStyle}>Time Remaining</th>
              <th style={thStyle}>Total</th>
              <th style={thStyle}>Payment</th>
              <th style={thStyle}>Status</th>
              <th style={thStyle}>Action</th>
            </tr>
          </thead>
          <tbody>
            {occupied.map((room, idx) => {
              const res = room.reservation;
              const payment = res.payment || null;
              const total = stayBlocks(res.checkIn, res.checkOut, res.checkInTime) * (Number(room.price) || 0);
              const rowBg = idx % 2 === 0 ? 'transparent' : 'rgba(255,255,255,0.015)';
              const canCheckIn = res.status !== 'Checked In';
              const remaining = remainingStay(res, now);
              return (
                <tr key={room.id} style={{ background: rowBg }}>
                  <td style={{ ...tdStyle, color: 'var(--fg)', fontWeight: 600 }}>
                    <span style={{ display: 'block' }}>{res.fullName || '—'}</span>
                    <span style={{ display: 'block', fontSize: '0.7rem', color: 'var(--fg-muted)', fontWeight: 400 }}>{res.idNumber || ''}</span>
                  </td>
                  <td style={tdStyle}>
                    <span style={{ display: 'block', fontSize: '0.62rem', color: 'var(--accent)', letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 2 }}>{room.label || room.category}</span>
                    <span style={{ color: 'var(--fg)', fontWeight: 500 }}>{room.name}</span>
                  </td>
                  <td style={tdStyle}>
                    <span style={{ display: 'block' }}>{res.contactNo || '—'}</span>
                    <span style={{ display: 'block', fontSize: '0.72rem' }}>{res.email || ''}</span>
                  </td>
                  <td style={tdStyle}>{formatCheckIn(res.checkIn, res.checkInTime)}</td>
                  <td style={tdStyle}>{res.checkOut || '—'}</td>
                  <td style={{
                    ...tdStyle,
                    color: STAY_TONE_COLORS[remaining.tone],
                    fontWeight: remaining.tone === 'idle' ? 400 : 600,
                    opacity: remaining.tone === 'idle' ? 0.6 : 1,
                    fontVariantNumeric: 'tabular-nums',
                  }}>
                    {remaining.text}
                  </td>
                  <td style={{ ...tdStyle, color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontWeight: 700 }}>{formatPeso(total)}</td>
                  <td style={tdStyle}>
                    {payment ? (
                      <>
                        <span style={{ display: 'block', color: 'var(--fg)', fontWeight: 500 }}>{payment.type} · {payment.method}</span>
                        <span style={{ display: 'block', fontSize: '0.75rem' }}>{formatPeso(payment.amountPaid)}</span>
                        {payment.balance > 0 && <span style={{ display: 'block', fontSize: '0.72rem', color: '#fb7185' }}>Bal: {formatPeso(payment.balance)}</span>}
                      </>
                    ) : <span style={{ opacity: 0.4 }}>—</span>}
                  </td>
                  <td style={tdStyle}>
                    <span className={`room-status-badge ${bookingStatusClass(res.status)}`} style={{ position: 'static' }}>{res.status}</span>
                  </td>
                  <td style={tdStyle}>
                    {!canCheckIn ? (
                      <span style={{ display: 'inline-flex', alignItems: 'center', gap: '0.4rem', color: '#60a5fa', fontSize: '0.78rem', fontWeight: 600 }}>
                        <i className="fa-solid fa-circle-check" style={{ fontSize: '0.8rem' }}></i> Checked in
                      </span>
                    ) : (
                      <button
                        type="button"
                        onClick={() => checkInGuest(room)}
                        title="Check the guest in"
                        style={{
                          display: 'inline-flex', alignItems: 'center', gap: '0.4rem',
                          padding: '0.4rem 0.8rem', borderRadius: 6,
                          border: '1px solid var(--accent)', background: 'var(--accent)', color: 'var(--bg)',
                          cursor: 'pointer',
                          fontFamily: 'Outfit, sans-serif', fontSize: '0.72rem', fontWeight: 600,
                          letterSpacing: '0.06em', textTransform: 'uppercase',
                        }}
                      >
                        <i className="fa-solid fa-right-to-bracket" style={{ fontSize: '0.7rem' }}></i> Check In
                      </button>
                    )}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function RoomManagementPage({ initialNav, rooms, onBack, onAddRoom, onRoomUpdated, onBookingAction, onToast }) {
  const activeNav = initialNav || 'manage-room';

  const handleAddRoom = (payload) => {
    if (typeof onAddRoom === 'function') onAddRoom(payload);
    if (onToast) onToast(`${payload.name} added to Rooms.`);
  };

  return (
    <div style={{ maxWidth: 1100, margin: '0 auto', padding: '1.5rem' }}>
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
            <ManageRoomPanel rooms={rooms} onSubmit={handleAddRoom} onCancel={onBack} onCloseModal={() => {}} onRoomUpdated={onRoomUpdated} />
          )}
        </div>
      </div>
    </div>
  );
}

function App() {
  const [rooms, setRooms] = useState([]);
  const pendingWrites = useRef(0);

  const fetchRooms = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/rooms', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => { if (pendingWrites.current > 0) return; if (Array.isArray(data.rooms)) setRooms(data.rooms); })
      .catch(() => {});
  }, []);

  useEffect(() => {
    fetchRooms();
    const id = setInterval(fetchRooms, 8000);
    window.addEventListener('focus', fetchRooms);
    return () => { clearInterval(id); window.removeEventListener('focus', fetchRooms); };
  }, [fetchRooms]);

  const addRoom = useCallback((roomFromDb) => {
    setRooms(prev => [...prev, roomFromDb]);
  }, []);

  const replaceRoom = useCallback((roomFromDb) => {
    setRooms(prev => prev.map(r => (r.id === roomFromDb.id ? roomFromDb : r)));
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
      onBack={() => { window.location.href = window.HMS_ROOMMANAGEMENT_URL; }}
      onAddRoom={addRoom}
      onRoomUpdated={replaceRoom}
      onBookingAction={bookingAction}
      onToast={(msg) => window.toast && window.toast(msg)}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
