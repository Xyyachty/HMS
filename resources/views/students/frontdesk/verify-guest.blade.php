@extends('students.builder.ops-shell')

@section('page-title', 'Guest Information')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }
  .room-status-badge {
    padding: 0.25rem 0.7rem; border-radius: 0.25rem;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent;
  }
  .room-status-badge.status-available { background: rgba(34,197,94,0.18); color: var(--success, #4ade80); border-color: rgba(34,197,94,0.35); }
  .room-status-badge.status-reserved { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .room-status-badge.status-occupied { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
  .room-status-badge.status-cleaning { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35); }
  .room-status-badge.status-maintenance { background: rgba(244,63,94,0.18); color: var(--danger, #fb7185); border-color: rgba(244,63,94,0.35); }
  .btn-outline {
    display: inline-flex; align-items: center; gap: 0.5rem;
    background: transparent; color: var(--accent);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 500;
    font-size: 0.75rem; letter-spacing: 0.1em; text-transform: uppercase;
    padding: 0.6rem 1.3rem; border: 1px solid var(--accent); border-radius: 0.375rem;
    cursor: pointer; transition: background 0.2s, color 0.2s, transform 0.2s;
    text-decoration: none;
  }
  .btn-outline:hover { background: var(--accent); color: var(--bg); transform: translateY(-1px); }
  .booking-input {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 0.375rem; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus { border-color: var(--accent); }
  .booking-input::placeholder { color: var(--fg-muted); opacity: 0.5; }
  select.booking-input { color-scheme: dark; }
  select.booking-input option { background: var(--card); color: var(--fg); }
  .btn-solid {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem;
    background: var(--accent); color: var(--bg); border: 1px solid var(--accent);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600;
    font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.65rem 1.2rem; border-radius: 0.375rem; cursor: pointer;
    transition: filter 0.2s;
  }
  .btn-solid:hover { filter: brightness(1.1); }
  .btn-solid:disabled { opacity: 0.45; cursor: not-allowed; filter: none; }

  /* Final bill — a folio, so it is set in a monospaced grid where the amounts line
     up in a column the way a printed receipt does. */
  .bill-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.65);
    display: flex; align-items: flex-start; justify-content: center;
    padding: 2rem 1.5rem; z-index: 300; overflow-y: auto;
  }
  .bill-modal {
    background: var(--card); border: 1px solid var(--border); border-radius: 0.875rem;
    width: 100%; max-width: 35rem; margin: auto;
  }
  .bill-head {
    padding: 1.4rem 1.6rem 1rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
  }
  .bill-body { padding: 1.25rem 1.6rem 1.6rem; }
  .bill-section-title {
    font-size: 0.62rem; font-weight: 700; letter-spacing: 0.14em;
    text-transform: uppercase; color: var(--accent);
    margin: 1.15rem 0 0.5rem; padding-bottom: 0.3rem;
    border-bottom: 1px solid var(--border);
  }
  .bill-line {
    display: flex; justify-content: space-between; gap: 1rem;
    font-size: 0.83rem; color: var(--fg-muted); padding: 0.22rem 0;
  }
  .bill-line .bill-amt { color: var(--fg); font-variant-numeric: tabular-nums; white-space: nowrap; }
  .bill-line.is-subtotal {
    border-top: 1px solid var(--border); margin-top: 0.35rem; padding-top: 0.45rem;
    color: var(--fg); font-weight: 600;
  }
  .bill-line.is-total {
    border-top: 2px solid var(--accent); margin-top: 0.6rem; padding-top: 0.6rem;
    font-size: 1rem; color: var(--fg); font-weight: 700;
  }
  .bill-line.is-total .bill-amt {
    color: var(--accent-light); font-family: var(--font-display, 'Playfair Display', serif); font-size: 1.15rem;
  }
  .bill-line.is-balance .bill-amt { color: var(--danger, #fb7185); font-weight: 700; }
  .bill-section-note { font-size: 0.7rem; color: var(--fg-muted); margin: -0.25rem 0 0.6rem; }
  .bill-subnote { font-size: 0.7rem; color: var(--fg-muted); margin: -0.05rem 0 0.4rem; }
  .bill-meta { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.55rem 1rem; }
  .bill-meta dt {
    font-size: 0.6rem; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--fg-muted); margin-bottom: 0.15rem;
  }
  .bill-meta dd { margin: 0; color: var(--fg); font-size: 0.85rem; }
  @media (max-width: 520px) { .bill-meta { grid-template-columns: 1fr; } }

  /* Guest list. Reads the shell's tokens (--card, --border, --fg, --fg-muted,
     --accent), so it follows Template 1, Template 2 and a team's own colours.
     Every cell stays on one line. When the panel is too narrow for that
     (a container query, so the sidebar's width counts), each row becomes a
     small card instead, so nothing wraps crooked or scrolls sideways. */
  .room-status-badge { white-space: nowrap; }
  .vg {
    --vg-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --vg-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    padding: 1.1rem 1.5rem 1.5rem; color: var(--fg);
  }
  .vg-head { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .vg-head h1 { margin: 0; font-size: 1.6rem; line-height: 1.15; color: var(--fg); }
  .vg-head p { margin: 0.3rem 0 0; color: var(--fg-muted); font-size: 0.86rem; line-height: 1.45; }
  .vg-panel { container-type: inline-size; background: var(--card); border: 1px solid var(--border); border-radius: 0.875rem; padding: 1rem 1.1rem 1.1rem; }

  .vg-tools { display: flex; gap: 0.6rem 0.9rem; flex-wrap: wrap; align-items: center; margin-bottom: 0.9rem; }
  .vg-search { position: relative; flex: 1 1 280px; min-width: 0; }
  .vg-search i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.8rem; pointer-events: none; }
  .vg-search .booking-input { box-sizing: border-box; padding-left: 2.3rem; border-radius: 0.625rem; font-size: 0.88rem; }
  .vg-filters { display: flex; gap: 0.35rem; flex-wrap: wrap; }
  .vg-filter {
    display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.5rem 0.8rem; border-radius: 999px;
    border: 1.5px solid var(--border); background: transparent; color: var(--fg-muted); cursor: pointer;
    font: 600 0.8rem/1 var(--font-body, 'Outfit', sans-serif); transition: border-color 0.15s, color 0.15s, background 0.15s;
  }
  .vg-filter:hover { border-color: var(--accent); color: var(--fg); }
  .vg-filter.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .vg-filter b { font-variant-numeric: tabular-nums; font-size: 0.72rem; padding: 0.12rem 0.42rem; border-radius: 999px; background: var(--vg-soft); }
  .vg-filter.is-on b { background: color-mix(in srgb, var(--bg) 22%, transparent); }

  .vg-tablewrap { border: 1px solid var(--border); border-radius: 0.75rem; overflow: hidden; }
  .vg-table { width: 100%; border-collapse: collapse; font-family: var(--font-body, 'Outfit', sans-serif); }
  .vg-table th {
    padding: 0.6rem 0.85rem; font-size: 0.74rem; font-weight: 600; color: var(--fg-muted); text-align: left;
    white-space: nowrap; border-bottom: 1px solid var(--border); background: var(--vg-soft);
  }
  .vg-table td { padding: 0.75rem 0.85rem; font-size: 0.84rem; color: var(--fg-muted); border-bottom: 1px solid var(--border); vertical-align: middle; white-space: nowrap; }
  .vg-table td.vg-guest { white-space: normal; min-width: 9rem; }
  .vg-table tbody tr:last-child td { border-bottom: 0; }
  .vg-table tbody tr:hover td { background: var(--vg-soft); }
  .vg-main { display: block; color: var(--fg); font-weight: 600; overflow-wrap: anywhere; }
  .vg-sub { display: block; margin-top: 0.15rem; font-size: 0.74rem; color: var(--fg-muted); }
  .vg-num { font-variant-numeric: tabular-nums; white-space: nowrap; }
  .vg-status + .vg-sub { margin-top: 0.3rem; }
  .vg-status { border-radius: 999px; letter-spacing: 0; text-transform: none; font-size: 0.76rem; padding: 0.28rem 0.65rem; }

  .vg-actions { display: flex; gap: 0.4rem; justify-content: flex-end; flex-wrap: nowrap; }
  .vg-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; white-space: nowrap; cursor: pointer;
    padding: 0.5rem 0.8rem; border-radius: 0.5rem; border: 1px solid var(--border); background: transparent; color: var(--fg);
    font: 600 0.78rem/1 var(--font-body, 'Outfit', sans-serif); text-decoration: none; transition: border-color 0.15s, color 0.15s, background 0.15s, filter 0.15s;
  }
  .vg-btn i { font-size: 0.72rem; }
  .vg-btn:hover { border-color: var(--accent); color: var(--accent); }
  .vg-btn-main { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .vg-btn-main:hover { filter: brightness(1.08); color: var(--bg); }
  .vg-btn-line { border-color: var(--accent); color: var(--accent); }
  .vg-btn-line:hover { background: var(--vg-tint); }
  .vg-btn:focus-visible, .vg-filter:focus-visible, .vg-page:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

  .vg-empty { border: 1.5px dashed var(--border); border-radius: 0.875rem; padding: 2rem 1rem; text-align: center; color: var(--fg-muted); font-size: 0.86rem; line-height: 1.5; }
  .vg-empty i.vg-empty-icon { display: block; font-size: 1.5rem; margin-bottom: 0.6rem; color: var(--accent); }
  .vg-empty b { display: block; color: var(--fg); font-size: 0.95rem; margin-bottom: 0.2rem; }
  .vg-empty .vg-btn { margin-top: 0.9rem; }

  .vg-pager { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.85rem; font-size: 0.78rem; color: var(--fg-muted); }
  .vg-pager-btns { display: flex; gap: 0.3rem; flex-wrap: wrap; }
  .vg-page {
    min-width: 2rem; height: 2rem; padding: 0 0.55rem; border-radius: 0.5rem; cursor: pointer;
    border: 1px solid var(--border); background: transparent; color: var(--fg-muted); font-size: 0.78rem;
  }
  .vg-page.is-on { background: var(--accent); border-color: var(--accent); color: var(--bg); font-weight: 700; }
  .vg-page:disabled { opacity: 0.4; cursor: default; }

  @container (max-width: 58rem) {
    .vg-tablewrap { border: 0; border-radius: 0; overflow: visible; }
    .vg-table thead { display: none; }
    .vg-table, .vg-table tbody { display: block; }
    .vg-table tr {
      display: grid; grid-template-columns: repeat(auto-fill, minmax(min(8.5rem, 100%), 1fr)); gap: 0.65rem 1rem;
      padding: 0.85rem 0.9rem; margin-bottom: 0.6rem; border: 1px solid var(--border); border-radius: 0.75rem;
    }
    .vg-table td { display: block; padding: 0; border: 0; white-space: normal; }
    .vg-table tbody tr:hover td { background: none; }
    .vg-table td[data-label]::before { content: attr(data-label); display: block; margin-bottom: 0.15rem; font-size: 0.7rem; color: var(--fg-muted); }
    .vg-table td.vg-wide { grid-column: 1 / -1; }
    .vg-actions { justify-content: flex-start; flex-wrap: wrap; }
  }
  @media (max-width: 640px) {
    .vg { padding: 1rem 1rem 2.5rem; }
    .vg-head h1 { font-size: 1.4rem; }
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
  :root[data-ops-theme="2"] select.booking-input { color-scheme: light; }
  :root[data-ops-theme="2"] .room-status-badge.status-available { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
  :root[data-ops-theme="2"] .room-status-badge.status-reserved { background: #f3e8ff; color: #7e22ce; border-color: #e9d5ff; }
  :root[data-ops-theme="2"] .room-status-badge.status-occupied { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
  :root[data-ops-theme="2"] .room-status-badge.status-cleaning { background: #fef3c7; color: #b45309; border-color: #fde68a; }
  :root[data-ops-theme="2"] .room-status-badge.status-maintenance { background: #ffe4e6; color: #be123c; border-color: #fecdd3; }
  :root[data-ops-theme="2"] .booking-input { background: rgba(27,67,50,0.03); }
  /* The guest's receipt: a paper slip inside the dialog, printed on its own. */
  .receipt-print { background: #fff; color: #1a1a1a; border-radius: 0.625rem; padding: 1.3rem 1.4rem; font-size: 0.86rem; border: 1px solid #e5e2dd; }
  .rc-top { text-align: center; padding-bottom: 0.9rem; border-bottom: 1px dashed #c9c3ba; }
  .rc-top h3 { margin: 0 0 0.25rem; font-size: 1.25rem; font-family: var(--font-display, 'Playfair Display', serif); color: #1a1a1a; }
  .rc-top p { margin: 0.1rem 0; color: #5f5a55; font-size: 0.8rem; }
  .rc-top .rc-no { font-weight: 700; color: #1a1a1a; letter-spacing: 0.04em; }
  .rc-block { padding: 0.8rem 0; border-bottom: 1px dashed #c9c3ba; }
  .rc-head { margin: 0 0 0.4rem; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase; color: #5f5a55; }
  .rc-line { display: flex; justify-content: space-between; gap: 1rem; padding: 0.18rem 0; }
  .rc-amt { flex: none; font-variant-numeric: tabular-nums; }
  .rc-strong { font-weight: 700; margin-top: 0.25rem; }
  .rc-due { color: #c81e3a; }
  .rc-thanks { margin: 0.9rem 0 0; text-align: center; color: #5f5a55; font-size: 0.8rem; }
  @media print {
    body * { visibility: hidden !important; }
    .receipt-print, .receipt-print * { visibility: visible !important; }
    .receipt-print { position: fixed; left: 0; top: 0; width: 100%; border: 0; border-radius: 0; padding: 0; }
  }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_FRONTDESK_URL = @json(route('students.dashboard', ['section' => 'tasks']));
  window.HMS_WALKIN_URL = @json(route('students.frontdesk.walk-in'));
  // The name printed at the top of the guest's receipt: the team's approved hotel.
  window.HMS_HOTEL_NAME = @json(\App\Support\HotelTemplateBuilder::hotelDefaults($groupName ?? null, isset($facultyId) ? (int) $facultyId : null)['name']);
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef } = React;

// Housekeeping condition only — occupancy lives on the booking (reservation.status),
// not on the room's own status. See bookingStatusClass() below for that badge.
const ROOM_STATUSES = ['Available', 'Cleaning', 'Maintenance'];
const BLOCK_HOURS = 12;

function normalizeRoomStatus(value) {
  const raw = String(value || 'Available').trim().toLowerCase();
  const match = ROOM_STATUSES.find(s => s.toLowerCase() === raw);
  return match || 'Available';
}

function roomStatusClass(status) {
  return 'status-' + normalizeRoomStatus(status).toLowerCase();
}

/* Booking-lifecycle badge (Booked / Arrived / Checked In) — reuses the
   room-status-badge colours (purple/amber/blue) for a different meaning now that
   hotel_rooms.status no longer tracks occupancy. */
function bookingStatusClass(status) {
  const s = String(status || '').trim();
  if (s === 'Checked In') return 'status-occupied';
  if (s === 'Arrived') return 'status-cleaning';
  return 'status-reserved';
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

/* 'Aug 12, 2026' from a 'YYYY-MM-DD' string, built from the parts so the date is not
   shifted a day by being read as UTC midnight. */
function formatBillDate(dateStr) {
  const [y, m, d] = String(dateStr || '').split('-').map(Number);
  if (!y || !m || !d) return '';
  return new Date(y, m - 1, d).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

/* 'Aug 12, 2026 — 2:00 PM' for the bill header. */
function formatBillDateTime(dateStr, timeStr) {
  const day = formatBillDate(dateStr);
  const clock = formatClockTime(timeStr);
  if (!day) return '—';
  return clock ? `${day} — ${clock}` : day;
}

/* The same, from a full ISO timestamp — what an actual check-out is stamped with. */
function formatBillStamp(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
    + ' — ' + d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
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

/* One line under each status badge, so the desk knows what that status means. */
const STATUS_HINTS = { 'Booked': 'Not here yet', 'Arrived': 'Needs a key', 'Checked In': 'In the room' };

/* 'Oct 23' from 'YYYY-MM-DD', and 'Oct 23, 12:48 AM' with a clock time: the list's short form. */
function formatShortDate(dateStr) {
  const [y, m, d] = String(dateStr || '').split('-').map(Number);
  if (!y || !m || !d) return '';
  return new Date(y, m - 1, d).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}
function formatShortDateTime(dateStr, timeStr) {
  const day = formatShortDate(dateStr);
  const clock = formatClockTime(timeStr);
  if (!day) return clock || '—';
  return clock ? `${day}, ${clock}` : day;
}

const STAY_TONE_COLORS ={ ok: 'var(--fg)', soon: '#fbbf24', over: 'var(--danger, #fb7185)', idle: 'var(--fg-muted)' };

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

const PAYMENT_METHODS = ['Cash', 'GCash', 'Card', 'Other'];

/* The guest's receipt, shown once the stay is checked out. Built from the closed
   stay's final bill, so it lists the payment just taken with the earlier ones.
   Print prints the receipt alone (see .receipt-print in the styles). */
function ReceiptModal({ receipt, onClose }) {
  useEffect(() => {
    if (!receipt) return;
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [receipt, onClose]);

  if (!receipt) return null;
  const bill = receipt.bill;
  const issued = bill.checkedOutAt ? new Date(bill.checkedOutAt) : new Date();
  const ymd = issued.getFullYear() + String(issued.getMonth() + 1).padStart(2, '0') + String(issued.getDate()).padStart(2, '0');
  const number = 'OR-' + ymd + '-' + String(bill.bookingId || '').padStart(5, '0');
  const service = bill.roomService || { orders: [], subtotal: 0 };
  const addons = bill.addons || { items: [], subtotal: 0 };
  const extras = bill.otherCharges || { items: [], subtotal: 0 };
  const balance = Number(bill.balance) || 0;
  const line = (label, amount, key, cls) => (
    <div className={'rc-line' + (cls ? ' ' + cls : '')} key={key}>
      <span>{label}</span><span className="rc-amt">{amount}</span>
    </div>
  );

  return (
    <div className="bill-overlay" onClick={onClose} role="dialog" aria-modal="true" aria-label="Receipt">
      <div className="bill-modal" onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ color: 'var(--success, #4ade80)', fontSize: '0.72rem', fontWeight: 700, margin: '0 0 0.35rem' }}>
              <i className="fa-solid fa-circle-check" style={{ marginRight: 6 }}></i>Checked out
            </p>
            <h2 className="font-display" style={{ fontSize: '1.5rem', margin: 0, color: 'var(--fg)' }}>Receipt</h2>
          </div>
          <button type="button" onClick={onClose} aria-label="Close"
            style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: 'pointer', flexShrink: 0 }}>
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div className="bill-body">
          <div className="receipt-print">
            <div className="rc-top">
              <h3>{window.HMS_HOTEL_NAME || 'Hotel'}</h3>
              <p>Official Receipt</p>
              <p className="rc-no">{number}</p>
              <p>{issued.toLocaleDateString([], { month: 'long', day: 'numeric', year: 'numeric' })}, {issued.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })}</p>
            </div>

            <div className="rc-block">
              {line('Guest', bill.guestName || '—', 'g')}
              {line('Room', bill.roomName || '—', 'r')}
              {line('Check-in', formatBillDateTime(bill.checkIn, bill.checkInTime), 'ci')}
              {line('Check-out', formatBillStamp(bill.checkedOutAt) || formatBillDate(bill.checkOut) || '—', 'co')}
            </div>

            <div className="rc-block">
              <p className="rc-head">Charges</p>
              {line(`Room — ${bill.room.blocks} × ${bill.room.blockHours} hrs × ${formatPeso(bill.room.rate)}`, formatPeso(bill.room.subtotal), 'room')}
              {service.orders.map(order => order.items.map((item, i) => (
                line(`Room service — ${item.name} × ${item.qty}`, formatPeso(item.line), 'rs' + order.orderId + '-' + i)
              )))}
              {addons.items.map(a => line(`Add-on — ${a.name} × ${a.qty}`, formatPeso(a.line), 'ad' + a.id))}
              {extras.items.map(c => line(c.description, formatPeso(c.amount), 'ex' + c.id))}
              {line('Total', formatPeso(bill.total), 'total', 'rc-strong')}
            </div>

            <div className="rc-block">
              <p className="rc-head">Payments</p>
              {bill.payments.length === 0
                ? line('None', formatPeso(0), 'none')
                : bill.payments.map(pay => line(
                    `${pay.type} — ${[pay.method, formatBillStamp(pay.paidAt)].filter(Boolean).join(', ')}`,
                    formatPeso(pay.amountPaid),
                    'p' + pay.id
                  ))}
              {line('Total paid', formatPeso(bill.amountPaid), 'paid', 'rc-strong')}
              {balance > 0
                ? line('Unpaid balance', formatPeso(balance), 'bal', 'rc-strong rc-due')
                : line('Balance', formatPeso(0), 'bal', 'rc-strong')}
            </div>

            <p className="rc-thanks">Thank you for staying with us.</p>
          </div>

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.6rem', marginTop: '1.2rem' }}>
            <button type="button" className="btn-outline" onClick={() => window.print()}>
              <i className="fa-solid fa-print" style={{ marginRight: 6 }}></i>Print
            </button>
            <button type="button" className="btn-solid" onClick={onClose}>Done</button>
          </div>
        </div>
      </div>
    </div>
  );
}

/*
 * The final bill, and the one screen that closes a stay.
 *
 * Everything shown here is priced by the server (HotelBooking::toBillArray()) rather
 * than recomputed in the browser, so what the guest is charged cannot drift from what
 * the database will record. Adding an extra charge re-fetches the whole bill for the
 * same reason — the new subtotal comes back priced, it is not patched in locally.
 */
function FinalBillModal({ open, bill, loading, error, onClose, onAddCharge, onRemoveCharge, onSettle, onToast }) {
  const [method, setMethod] = useState('Cash');
  const [amount, setAmount] = useState('');
  const [reference, setReference] = useState('');
  const [chargeDesc, setChargeDesc] = useState('');
  const [chargeAmount, setChargeAmount] = useState('');
  const [showChargeForm, setShowChargeForm] = useState(false);
  const [busy, setBusy] = useState(false);

  const balance = bill ? Number(bill.balance) || 0 : 0;

  // Opening the bill pre-fills the balance: settling it in full is the common case,
  // and the field stays editable for a guest paying part of it.
  useEffect(() => {
    if (!open) return;
    setMethod('Cash');
    setReference('');
    setChargeDesc('');
    setChargeAmount('');
    setShowChargeForm(false);
  }, [open]);

  useEffect(() => {
    if (open && bill) setAmount(balance > 0 ? String(balance) : '0');
  }, [open, bill && bill.bookingId]);

  useEffect(() => {
    if (!open) return;
    const onKey = (e) => { if (e.key === 'Escape' && !busy) onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [open, onClose, busy]);

  if (!open) return null;

  const paid = Math.max(0, parseFloat(amount) || 0);
  const remainingAfter = Math.max(0, balance - paid);

  const submitCharge = (e) => {
    e.preventDefault();
    const value = parseFloat(chargeAmount);
    if (!chargeDesc.trim()) { if (onToast) onToast('Describe the charge first.'); return; }
    if (!Number.isFinite(value) || value <= 0) { if (onToast) onToast('Enter a valid charge amount.'); return; }
    setBusy(true);
    Promise.resolve(onAddCharge(chargeDesc.trim(), value))
      .then(() => { setChargeDesc(''); setChargeAmount(''); setShowChargeForm(false); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not add that charge.'); })
      .finally(() => setBusy(false));
  };

  const settle = (e) => {
    e.preventDefault();
    // A short payment is allowed — a written-off or disputed stay still has to close —
    // but the desk is told what is being left behind before it does.
    if (remainingAfter > 0 && !window.confirm(
      `${formatPeso(remainingAfter)} will still be unpaid after this. Check out anyway?`
    )) return;
    setBusy(true);
    Promise.resolve(onSettle({ amount: paid, method, reference: reference.trim() }))
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not complete check-out.'); })
      .finally(() => setBusy(false));
  };

  const roomLines = bill ? bill.room : null;
  const service = bill ? bill.roomService : null;
  const extras = bill ? bill.otherCharges : null;
  const addons = bill ? bill.addons : null;

  return (
    <div className="bill-overlay" onClick={() => { if (!busy) onClose(); }} role="dialog" aria-modal="true">
      <div className="bill-modal" onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.2em', textTransform: 'uppercase', margin: '0 0 0.35rem' }}>Front Desk</p>
            <h2 className="font-display" style={{ fontSize: '1.5rem', margin: 0, color: 'var(--fg)' }}>Final Bill</h2>
          </div>
          <button type="button" onClick={onClose} disabled={busy} aria-label="Close"
            style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: busy ? 'not-allowed' : 'pointer', flexShrink: 0 }}>
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div className="bill-body">
          {loading ? (
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.85rem', margin: 0 }}>Loading the bill…</p>
          ) : error ? (
            <p style={{ color: 'var(--danger, #fb7185)', fontSize: '0.85rem', margin: 0 }}>{error}</p>
          ) : !bill ? null : (
            <>
              <dl className="bill-meta">
                <div><dt>Guest</dt><dd>{bill.guestName}</dd></div>
                <div><dt>Room</dt><dd>{bill.roomName || '—'}</dd></div>
                <div>
                  <dt>Check-in</dt>
                  <dd>{formatBillDateTime(bill.checkIn, bill.checkInTime)}</dd>
                </div>
                <div>
                  <dt>Check-out</dt>
                  <dd>{formatBillStamp(bill.checkedOutAt) || formatBillDate(bill.checkOut) || '—'}</dd>
                </div>
              </dl>

              <p className="bill-section-title">Room Charges</p>
              <div className="bill-line">
                <span>{roomLines.blocks} × {roomLines.blockHours} hrs × {formatPeso(roomLines.rate)}</span>
                <span className="bill-amt">{formatPeso(roomLines.subtotal)}</span>
              </div>

              {service.orders.length > 0 && (
                <>
                  <p className="bill-section-title">Room Service</p>
                  {service.orders.map(order => (
                    order.items.map((item, i) => (
                      <div className="bill-line" key={order.orderId + '-' + i}>
                        <span>{item.name} × {item.qty}</span>
                        <span className="bill-amt">{formatPeso(item.line)}</span>
                      </div>
                    ))
                  ))}
                  <div className="bill-line is-subtotal">
                    <span>Room Service Subtotal</span>
                    <span className="bill-amt">{formatPeso(service.subtotal)}</span>
                  </div>
                </>
              )}

              {/* Housekeeping's add-ons. No remove button: they were lent at registration
                  and go back into stock when the stay closes, not when a line is deleted. */}
              {addons && addons.items.length > 0 && (
                <>
                  <p className="bill-section-title">Add-ons</p>
                  {addons.items.map(addon => (
                    <div className="bill-line" key={addon.id}>
                      <span>{addon.name} × {addon.qty}</span>
                      <span className="bill-amt">{formatPeso(addon.line)}</span>
                    </div>
                  ))}
                  <div className="bill-line is-subtotal">
                    <span>Add-ons Subtotal</span>
                    <span className="bill-amt">{formatPeso(addons.subtotal)}</span>
                  </div>
                </>
              )}

              <p className="bill-section-title">Other Charges</p>
              {extras.items.length === 0 ? (
                <div className="bill-line"><span style={{ opacity: 0.6 }}>None</span><span className="bill-amt">{formatPeso(0)}</span></div>
              ) : (
                <>
                  {extras.items.map(charge => (
                    <div className="bill-line" key={charge.id}>
                      <span>
                        {charge.description}
                        <button type="button" onClick={() => onRemoveCharge(charge.id)} disabled={busy} title="Remove this charge"
                          style={{ background: 'none', border: 'none', color: 'var(--danger, #fb7185)', cursor: busy ? 'not-allowed' : 'pointer', fontSize: '0.72rem', padding: '0 0 0 0.45rem' }}>
                          <i className="fa-solid fa-xmark"></i>
                        </button>
                      </span>
                      <span className="bill-amt">{formatPeso(charge.amount)}</span>
                    </div>
                  ))}
                  <div className="bill-line is-subtotal">
                    <span>Other Subtotal</span>
                    <span className="bill-amt">{formatPeso(extras.subtotal)}</span>
                  </div>
                </>
              )}

              {showChargeForm ? (
                <form onSubmit={submitCharge} style={{ display: 'flex', gap: '0.4rem', marginTop: '0.6rem', flexWrap: 'wrap' }}>
                  <input type="text" className="booking-input" placeholder="e.g. Minibar" value={chargeDesc}
                    onChange={e => setChargeDesc(e.target.value)} style={{ flex: '2 1 140px', width: 'auto' }} />
                  <input type="number" className="booking-input" placeholder="Amount" min="0.01" step="0.01" value={chargeAmount}
                    onChange={e => setChargeAmount(e.target.value)} style={{ flex: '1 1 90px', width: 'auto' }} />
                  <button type="submit" className="btn-solid" disabled={busy} style={{ padding: '0.5rem 0.9rem' }}>Add</button>
                  <button type="button" className="btn-outline" disabled={busy} onClick={() => setShowChargeForm(false)}
                    style={{ padding: '0.5rem 0.9rem', fontSize: '0.68rem' }}>Cancel</button>
                </form>
              ) : (
                <button type="button" onClick={() => setShowChargeForm(true)} disabled={busy}
                  style={{ background: 'none', border: 'none', color: 'var(--accent)', cursor: busy ? 'not-allowed' : 'pointer', fontSize: '0.75rem', padding: '0.5rem 0 0', fontFamily: 'var(--font-body, Outfit, sans-serif)' }}>
                  <i className="fa-solid fa-plus" style={{ fontSize: '0.65rem', marginRight: 5 }}></i> Add charge
                </button>
              )}

              {/* What the guest settled before today, itemised: the amount reads in
                  the money column with every other line, and the method and date sit
                  under it rather than being folded into the label. */}
              <p className="bill-section-title">Previous Payments</p>
              <p className="bill-section-note">Money the guest already paid during the stay.</p>
              {bill.payments.length === 0 ? (
                <div className="bill-line"><span style={{ opacity: 0.6 }}>None</span><span className="bill-amt">− {formatPeso(0)}</span></div>
              ) : (
                bill.payments.map(p => (
                  <div key={p.id}>
                    <div className="bill-line">
                      <span>{p.type} payment</span>
                      <span className="bill-amt">− {formatPeso(p.amountPaid)}</span>
                    </div>
                    <p className="bill-subnote">{[p.method, formatBillStamp(p.paidAt)].filter(Boolean).join(' · ')}</p>
                  </div>
                ))
              )}
              <div className="bill-line is-subtotal">
                <span>Total Paid Before</span>
                <span className="bill-amt">− {formatPeso(bill.amountPaid)}</span>
              </div>
              {/* The money to collect right now. Red only when something is actually
                  owed — a settled stay showing ₱0 in red reads as a problem. */}
              <div className={'bill-line is-subtotal' + (balance > 0 ? ' is-balance' : '')}>
                <span>Still to Pay Now</span>
                <span className="bill-amt" style={balance > 0 ? undefined : { color: 'var(--success, #4ade80)', fontWeight: 700 }}>
                  {formatPeso(balance)}
                </span>
              </div>

              <div className="bill-line is-total">
                <span>Total Bill</span>
                <span className="bill-amt">{formatPeso(bill.total)}</span>
              </div>

              <form onSubmit={settle} style={{ marginTop: '1.4rem' }}>
                <p className="bill-section-title" style={{ marginTop: 0 }}>Take Payment</p>
                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0.7rem' }}>
                  <div>
                    <label style={{ fontSize: '0.6rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.35rem' }}>Payment Method</label>
                    <select className="booking-input" value={method} onChange={e => setMethod(e.target.value)}>
                      {PAYMENT_METHODS.map(m => <option key={m} value={m}>{m}</option>)}
                    </select>
                  </div>
                  <div>
                    <label style={{ fontSize: '0.6rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.35rem' }}>Amount Received</label>
                    <input type="number" className="booking-input" min="0" step="0.01" value={amount}
                      onChange={e => setAmount(e.target.value)} />
                  </div>
                </div>
                {method !== 'Cash' && (
                  <div style={{ marginTop: '0.7rem' }}>
                    <label style={{ fontSize: '0.6rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.35rem' }}>Reference</label>
                    <input type="text" className="booking-input" placeholder="Receipt no., card last 4, or ref #"
                      value={reference} onChange={e => setReference(e.target.value)} />
                  </div>
                )}
                <p style={{ margin: '0.65rem 0 0', fontSize: '0.76rem', color: remainingAfter > 0 ? 'var(--danger, #fb7185)' : 'var(--success, #4ade80)' }}>
                  {remainingAfter > 0
                    ? `${formatPeso(remainingAfter)} will still be unpaid.`
                    : 'This pays the bill in full.'}
                </p>
                <button type="submit" className="btn-solid" disabled={busy} style={{ width: '100%', marginTop: '1rem' }}>
                  <i className="fa-solid fa-right-from-bracket" style={{ fontSize: '0.7rem' }}></i>
                  {busy ? 'Working…' : 'Take Payment & Check Out'}
                </button>
              </form>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

/*
 * Read-only view of one stay. The table now carries only what the desk scans by
 * (who, where, when, status), so everything it used to spell out in the Total and
 * Payment columns lives here instead, in full rather than abbreviated.
 */
function GuestDetailsModal({ room, reservation, onClose }) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  if (!reservation) return null;

  const roomTotal = Number(reservation.totalDue) || 0;
  const foodTotal = Number(reservation.roomServiceTotal) || 0;
  const foodCount = Number(reservation.roomServiceCount) || 0;
  const extras = Number(reservation.otherCharges) || 0;
  const addonsTotal = Number(reservation.addonsTotal) || 0;
  const addonsCount = Number(reservation.addonsCount) || 0;
  const grandTotal = Number(reservation.grandTotal) || (roomTotal + foodTotal + addonsTotal + extras);
  const paid = Number(reservation.amountPaid) || 0;
  const outstanding = reservation.outstanding != null
    ? Number(reservation.outstanding)
    : Math.max(0, grandTotal - paid);
  const payments = reservation.payments || [];

  return (
    <div className="bill-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="bill-modal" onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ margin: 0, color: 'var(--accent)', fontSize: '0.62rem', letterSpacing: '0.14em', textTransform: 'uppercase' }}>
              Reservation
            </p>
            <h2 className="font-display" style={{ margin: '0.25rem 0 0.4rem', fontSize: '1.35rem', color: 'var(--fg)' }}>
              {reservation.fullName || 'Guest'}
            </h2>
            <span className={`room-status-badge ${bookingStatusClass(reservation.status)}`} style={{ position: 'static' }}>
              {reservation.status}
            </span>
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

        <div className="bill-body">
          <p className="bill-section-title" style={{ marginTop: 0 }}>Guest</p>
          <dl className="bill-meta">
            <div><dt>Full name</dt><dd>{reservation.fullName || '—'}</dd></div>
            <div><dt>Contact no.</dt><dd>{reservation.contactNo || '—'}</dd></div>
            <div><dt>Email</dt><dd style={{ overflowWrap: 'anywhere' }}>{reservation.email || '—'}</dd></div>
            <div><dt>ID number</dt><dd>{reservation.idNumber || '—'}</dd></div>
          </dl>

          <p className="bill-section-title">Stay</p>
          <dl className="bill-meta">
            <div><dt>Room</dt><dd>{room ? `${room.name} · ${room.label || room.category}` : '—'}</dd></div>
            <div><dt>Rate / 12 hrs</dt><dd>{formatPeso(reservation.roomRate || (room && room.price))}</dd></div>
            <div><dt>Check-in</dt><dd>{formatBillDateTime(reservation.checkIn, reservation.checkInTime)}</dd></div>
            <div><dt>Check-out</dt><dd>{formatBillDate(reservation.checkOut) || '—'}</dd></div>
            <div><dt>Booked</dt><dd>{formatBillStamp(reservation.reservedAt) || '—'}</dd></div>
            <div><dt>Arrived</dt><dd>{formatBillStamp(reservation.arrivedAt) || '—'}</dd></div>
            <div><dt>Checked in</dt><dd>{formatBillStamp(reservation.checkedInAt) || '—'}</dd></div>
            <div><dt>Checked out</dt><dd>{formatBillStamp(reservation.checkedOutAt) || '—'}</dd></div>
            <div><dt>Booked by</dt><dd>{reservation.bookedBy || '—'}</dd></div>
          </dl>

          {reservation.notes && (
            <>
              <p className="bill-section-title">Notes</p>
              <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.84rem', lineHeight: 1.6, whiteSpace: 'pre-wrap' }}>
                {reservation.notes}
              </p>
            </>
          )}

          <p className="bill-section-title">Charges</p>
          <div className="bill-line"><span>Room charge</span><span className="bill-amt">{formatPeso(roomTotal)}</span></div>
          <div className="bill-line">
            <span>Room service{foodCount > 0 ? ` (${foodCount} order${foodCount === 1 ? '' : 's'})` : ''}</span>
            <span className="bill-amt">{formatPeso(foodTotal)}</span>
          </div>
          {addonsCount > 0 && (
            <div className="bill-line">
              <span>Add-ons ({addonsCount} item{addonsCount === 1 ? '' : 's'})</span>
              <span className="bill-amt">{formatPeso(addonsTotal)}</span>
            </div>
          )}
          <div className="bill-line"><span>Other charges</span><span className="bill-amt">{formatPeso(extras)}</span></div>
          <div className="bill-line is-total"><span>Total</span><span className="bill-amt">{formatPeso(grandTotal)}</span></div>
          <div className="bill-line is-subtotal"><span>Paid</span><span className="bill-amt">{formatPeso(paid)}</span></div>
          <div className={`bill-line${outstanding > 0 ? ' is-balance' : ''}`}>
            <span>Balance</span><span className="bill-amt">{formatPeso(outstanding)}</span>
          </div>

          <p className="bill-section-title">Payments</p>
          {payments.length === 0 ? (
            <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.82rem' }}>Nothing has been paid on this stay.</p>
          ) : (
            payments.map(p => (
              <div key={p.id} style={{ padding: '0.5rem 0', borderBottom: '1px solid rgba(42,38,33,0.5)' }}>
                <div className="bill-line" style={{ padding: 0 }}>
                  <span style={{ color: 'var(--fg)' }}>{p.type} · {p.method}</span>
                  <span className="bill-amt">{formatPeso(p.amountPaid)}</span>
                </div>
                <p style={{ margin: '0.2rem 0 0', fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                  {formatBillStamp(p.paidAt) || '—'}
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

function fdThemeColor(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

function VerifyGuestPage({ rooms, onBack, onBookingAction, onToast, onFetchBill, onAddCharge, onRemoveCharge, onSettleAndCheckOut }) {
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('All');
  const [page, setPage] = useState(1);
  const now = useNow(1000);
  const PER_PAGE = 8;

  // Check-out goes through the final bill: the desk sees every charge on the stay and
  // takes the closing payment there, rather than the booking simply being closed.
  const [billFor, setBillFor] = useState(null);
  const [bill, setBill] = useState(null);
  const [billLoading, setBillLoading] = useState(false);
  const [billError, setBillError] = useState('');

  // The View action. Read-only, so it needs no fetch: the row already holds the whole
  // reservation payload the modal renders.
  const [detailsFor, setDetailsFor] = useState(null);

  /* Front Desk checks an arrived guest in here too, not only Room Management -
     the same booking action. Asked first: it starts the guest's paid stay, and
     a mis-click on the wrong row is easy. */
  const checkInGuest = (room, reservation) => {
    if (typeof onBookingAction !== 'function') return;
    const name = reservation.fullName || 'this guest';
    const go = () => {
      onBookingAction(reservation.bookingId, 'check_in');
      if (onToast) onToast(`${reservation.fullName || 'Guest'} is checked in to ${room.name}.`);
    };
    if (!window.Swal) { if (window.confirm(`Check in ${name} to ${room.name}?`)) go(); return; }
    window.Swal.fire({
      title: `Check in ${name}?`,
      text: `They get the key to ${room.name}, and their stay time starts now.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Yes, check in',
      cancelButtonText: 'Not yet',
      background: fdThemeColor('--card', '#181714'),
      color: fdThemeColor('--fg', '#f5f0e8'),
      confirmButtonColor: fdThemeColor('--accent', '#c9a84c'),
      cancelButtonColor: '#71717a',
      heightAuto: false,
    }).then(r => { if (r.isConfirmed) go(); });
  };

  const openBill = (room, reservation) => {
    setBillFor({ room, reservation });
    setBill(null);
    setBillError('');
    setBillLoading(true);
    Promise.resolve(onFetchBill(reservation.bookingId))
      .then(data => setBill(data))
      .catch(err => setBillError((err && err.message) || 'Could not load this bill.'))
      .finally(() => setBillLoading(false));
  };

  const closeBill = () => { setBillFor(null); setBill(null); setBillError(''); };

  const refreshBill = (promise) => (
    Promise.resolve(promise).then(data => { if (data) setBill(data); return data; })
  );

  // After check-out the bill window gives way to the guest's receipt, read back
  // from the closed stay so it carries the payment just taken.
  const [receipt, setReceipt] = useState(null);

  const settleAndCheckOut = (payment) => {
    const bookingId = billFor.reservation.bookingId;
    const lastBill = bill;
    return Promise.resolve(onSettleAndCheckOut(bookingId, payment)).then(() => {
      if (onToast) {
        onToast(`${billFor.reservation.fullName || 'Guest'} checked out of ${billFor.room.name}.`);
      }
      closeBill();
      return Promise.resolve(onFetchBill(bookingId))
        .then(finalBill => setReceipt({ bill: finalBill || lastBill }))
        .catch(() => { if (lastBill) setReceipt({ bill: lastBill }); });
    });
  };

  const allReservations = (rooms || []).reduce((acc, room) => {
    if (room && room.reservation) acc.push({ room, reservation: room.reservation });
    return acc;
  }, []);

  const q = search.trim().toLowerCase();
  const filtered = allReservations.filter(({ room, reservation }) =>
    (statusFilter === 'All' || reservation.status === statusFilter) && (!q ||
      (reservation.fullName || '').toLowerCase().includes(q) ||
      (room.name || '').toLowerCase().includes(q) ||
      (reservation.email || '').toLowerCase().includes(q) ||
      (reservation.contactNo || '').toLowerCase().includes(q) ||
      (reservation.idNumber || '').toLowerCase().includes(q))
  );

  const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageRows = filtered.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  const statusTabs = [['All', 'All'], ['Booked', 'Booked'], ['Arrived', 'Arrived'], ['Checked In', 'Checked in']];
  const countFor = (key) => (key === 'All'
    ? allReservations.length
    : allReservations.filter(({ reservation }) => reservation.status === key).length);
  const clearFilters = () => { setSearch(''); setStatusFilter('All'); setPage(1); };

  return (
    <div className="vg" data-hms-no-edit="1">
      <header className="vg-head">
        <div>
          <h1 className="font-display">Guest Information</h1>
          <p>Everyone booked or staying right now. Use the button on each row for the next step.</p>
        </div>
        {window.HMS_WALKIN_URL && (
          <a className="vg-btn vg-btn-line" href={window.HMS_WALKIN_URL}>
            <i className="fa-solid fa-user-plus"></i> Book a walk-in guest
          </a>
        )}
      </header>

      <div className="vg-panel">
        <div className="vg-tools">
          <div className="vg-search">
            <i className="fa-solid fa-magnifying-glass"></i>
            <input
              type="text"
              className="booking-input"
              placeholder="Search name, room, phone, email or ID"
              aria-label="Search guests"
              value={search}
              onChange={e => { setSearch(e.target.value); setPage(1); }}
            />
          </div>
          <div className="vg-filters" role="radiogroup" aria-label="Show guests by status">
            {statusTabs.map(([key, label]) => (
              <button key={key} type="button" role="radio" aria-checked={statusFilter === key}
                className={'vg-filter' + (statusFilter === key ? ' is-on' : '')}
                onClick={() => { setStatusFilter(key); setPage(1); }}>
                {label} <b>{countFor(key)}</b>
              </button>
            ))}
          </div>
        </div>

        {allReservations.length === 0 ? (
          <div className="vg-empty">
            <i className="fa-solid fa-door-open vg-empty-icon"></i>
            <b>No guests right now</b>
            New bookings and walk-in guests show up here.
            {window.HMS_WALKIN_URL && (
              <div><a className="vg-btn vg-btn-main" href={window.HMS_WALKIN_URL}><i className="fa-solid fa-user-plus"></i> Book a walk-in guest</a></div>
            )}
          </div>
        ) : filtered.length === 0 ? (
          <div className="vg-empty">
            <i className="fa-solid fa-magnifying-glass vg-empty-icon"></i>
            <b>No guests match</b>
            {q ? `Nothing found for "${search}"` : 'No guests have this status right now'}{statusFilter !== 'All' && q ? ' with this status' : ''}.
            <div><button type="button" className="vg-btn" onClick={clearFilters}><i className="fa-solid fa-xmark"></i> Show all guests</button></div>
          </div>
        ) : (
          <>
            <div className="vg-tablewrap">
              <table className="vg-table">
                <thead>
                  <tr>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Stay</th>
                    <th>Time left</th>
                    <th>Status</th>
                    <th style={{ textAlign: 'right' }}>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {pageRows.map(({ room, reservation }) => {
                    const status = reservation.status;
                    const remaining = remainingStay(reservation, now);
                    return (
                      <tr key={room.id}>
                        <td className="vg-wide vg-guest">
                          <span className="vg-main">{reservation.fullName || '—'}</span>
                          {reservation.contactNo && <span className="vg-sub">{reservation.contactNo}</span>}
                        </td>
                        <td data-label="Room">
                          <span className="vg-main">{room.name}</span>
                          <span className="vg-sub">{room.label || room.category} · {formatPeso(room.price)} / 12 hrs</span>
                        </td>
                        <td data-label="Stay">
                          <span className="vg-num" style={{ color: 'var(--fg)' }}>In {formatShortDateTime(reservation.checkIn, reservation.checkInTime)}</span>
                          <span className="vg-sub vg-num">Out {formatShortDate(reservation.checkOut) || '—'}</span>
                        </td>
                        <td data-label="Time left" className="vg-num" style={{
                          color: STAY_TONE_COLORS[remaining.tone],
                          fontWeight: remaining.tone === 'idle' ? 400 : 600,
                          opacity: remaining.tone === 'idle' ? 0.7 : 1,
                        }}>
                          {remaining.text}
                        </td>
                        <td data-label="Status">
                          <span className={`room-status-badge vg-status ${bookingStatusClass(status)}`}>{status}</span>
                          {STATUS_HINTS[status] && <span className="vg-sub">{STATUS_HINTS[status]}</span>}
                        </td>
                        <td className="vg-wide">
                          <div className="vg-actions">
                            {status === 'Booked' && (
                              <button type="button" className="vg-btn vg-btn-line"
                                onClick={() => onBookingAction(reservation.bookingId, 'arrive')}
                                title="The guest is here at the desk">
                                <i className="fa-solid fa-door-open"></i> Mark arrived
                              </button>
                            )}
                            {status === 'Arrived' && (
                              <button type="button" className="vg-btn vg-btn-main"
                                onClick={() => checkInGuest(room, reservation)}
                                title="Give the guest the key and start their stay">
                                <i className="fa-solid fa-key"></i> Check in
                              </button>
                            )}
                            {status === 'Checked In' && (
                              <button type="button" className="vg-btn vg-btn-main"
                                onClick={() => openBill(room, reservation)}
                                title="Show the final bill and check the guest out">
                                <i className="fa-solid fa-right-from-bracket"></i> Check out
                              </button>
                            )}
                            <button type="button" className="vg-btn"
                              onClick={() => setDetailsFor({ room, reservation })}
                              title="See everything recorded for this stay">
                              <i className="fa-solid fa-eye"></i> Details
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>

            {totalPages > 1 && (
              <div className="vg-pager">
                <span>Showing {(safePage - 1) * PER_PAGE + 1}–{Math.min(safePage * PER_PAGE, filtered.length)} of {filtered.length}</span>
                <div className="vg-pager-btns">
                  <button type="button" className="vg-page" aria-label="Previous page"
                    onClick={() => setPage(p => Math.max(1, p - 1))} disabled={safePage === 1}>
                    <i className="fa-solid fa-chevron-left" style={{ fontSize: '0.65rem' }}></i>
                  </button>
                  {Array.from({ length: totalPages }, (_, i) => i + 1).map(n => (
                    <button key={n} type="button" className={'vg-page' + (n === safePage ? ' is-on' : '')}
                      aria-current={n === safePage ? 'page' : undefined} onClick={() => setPage(n)}>
                      {n}
                    </button>
                  ))}
                  <button type="button" className="vg-page" aria-label="Next page"
                    onClick={() => setPage(p => Math.min(totalPages, p + 1))} disabled={safePage === totalPages}>
                    <i className="fa-solid fa-chevron-right" style={{ fontSize: '0.65rem' }}></i>
                  </button>
                </div>
              </div>
            )}
          </>
        )}
      </div>

      <FinalBillModal
        open={!!billFor}
        bill={bill}
        loading={billLoading}
        error={billError}
        onClose={closeBill}
        onAddCharge={(description, amount) => refreshBill(onAddCharge(billFor.reservation.bookingId, description, amount))}
        onRemoveCharge={(chargeId) => refreshBill(onRemoveCharge(billFor.reservation.bookingId, chargeId))}
        onSettle={settleAndCheckOut}
        onToast={onToast}
      />

      <ReceiptModal receipt={receipt} onClose={() => setReceipt(null)} />

      {detailsFor && (
        <GuestDetailsModal
          room={detailsFor.room}
          reservation={detailsFor.reservation}
          onClose={() => setDetailsFor(null)}
        />
      )}
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

  // Lifecycle moves belong to the booking, not the room; the response carries the room
  // back with its projected reservation so the table re-renders from one source.
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

  /* The bill routes all answer with the whole re-priced bill, so each of these hands
     that straight back to the modal rather than patching a total locally. */
  const billRequest = useCallback((url, method, body) => {
    pendingWrites.current += 1;
    return fetch(url, {
      method,
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: body === undefined ? undefined : JSON.stringify(body),
    })
      .then(r => r.json().then(data => (r.ok ? data : Promise.reject(data))))
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  const fetchBill = useCallback((bookingId) => (
    billRequest('/students/hotel/bookings/' + bookingId + '/bill', 'GET').then(d => d.bill)
  ), [billRequest]);

  const addCharge = useCallback((bookingId, description, amount) => (
    billRequest('/students/hotel/bookings/' + bookingId + '/charges', 'POST', { description, amount }).then(d => d.bill)
  ), [billRequest]);

  const removeCharge = useCallback((bookingId, chargeId) => (
    billRequest('/students/hotel/bookings/' + bookingId + '/charges/' + chargeId, 'DELETE').then(d => d.bill)
  ), [billRequest]);

  /*
   * Settling is two writes: the closing payment, then the check-out itself. The payment
   * goes first — if it fails the stay stays open and can be retried, whereas closing
   * first would leave a checked-out guest with money unrecorded against them. A zero
   * payment (nothing left to settle) skips straight to the check-out.
   */
  const settleAndCheckOut = useCallback((bookingId, payment) => {
    const takePayment = payment.amount > 0
      ? billRequest('/students/hotel/bookings/' + bookingId + '/payments', 'POST', {
          type: 'Full',
          amount_paid: payment.amount,
          method: payment.method,
          reference: payment.reference,
        })
      : Promise.resolve(null);

    return takePayment.then(() => (
      billRequest('/students/hotel/bookings/' + bookingId, 'PATCH', { action: 'check_out' })
        .then(data => {
          if (data && data.room) setRooms(prev => prev.map(r => (r.id === data.room.id ? data.room : r)));
          // The checked-out room no longer carries this stay, so drop it from the table
          // even if the poll has not come back around yet.
          fetchRooms();
          return data;
        })
    ));
  }, [billRequest, fetchRooms]);

  return (
    <VerifyGuestPage
      rooms={rooms}
      onBack={() => { window.location.href = window.HMS_FRONTDESK_URL; }}
      onBookingAction={bookingAction}
      onFetchBill={fetchBill}
      onAddCharge={addCharge}
      onRemoveCharge={removeCharge}
      onSettleAndCheckOut={settleAndCheckOut}
      onToast={(msg) => window.toast && window.toast(msg)}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
