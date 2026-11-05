@extends('students.builder.ops-shell')

@section('page-title', 'Guest Folio')

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
  /* A bill line with its unit price under the name: "3 × ₱1,300". */
  .bill-item { align-items: flex-start; }
  .bill-item .bill-item-text { display: flex; flex-direction: column; gap: 0.15rem; min-width: 0; }
  .bill-item .bill-item-name { color: var(--fg); }
  .bill-item .bill-unit { font-size: 0.72rem; color: var(--fg-muted); font-variant-numeric: tabular-nums; }
  .rc-unit { display: block; font-size: 0.72rem; color: var(--fg-muted); }
  .bill-meta { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.55rem 1rem; }
  .bill-meta dt {
    font-size: 0.6rem; letter-spacing: 0.12em; text-transform: uppercase;
    color: var(--fg-muted); margin-bottom: 0.15rem;
  }
  .bill-meta dd { margin: 0; color: var(--fg); font-size: 0.85rem; }
  @media (max-width: 520px) { .bill-meta { grid-template-columns: 1fr; } }

  /* Bill and guest Details open wide (landscape) so the desk reads them without
     scrolling: charges on the left, money on the right. The receipt stays narrow
     like a printed slip. Below 820px the two columns stack again. */
  .bill-modal.is-wide { max-width: 64rem; }
  .bill-cols { display: grid; gap: 0 2.25rem; }
  .bill-cols > * { min-width: 0; }
  .bill-modal .booking-input { box-sizing: border-box; }
  @media (min-width: 820px) {
    .bill-cols { grid-template-columns: repeat(2, minmax(0, 1fr)); margin-top: 0.4rem; }
    .bill-cols > * > .bill-section-title:first-child { margin-top: 0.75rem; }
    .bill-meta.is-four { grid-template-columns: repeat(4, minmax(0, 1fr)); }
  }

  /* Extend Stay: a − / + stepper for the extra nights. */
  .xs-stepper { display: flex; align-items: center; gap: 0.6rem; }
  .xs-step {
    width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; border: 1px solid var(--border);
    background: transparent; color: var(--fg); cursor: pointer; transition: border-color 0.15s, color 0.15s;
  }
  .xs-step:hover:not(:disabled) { border-color: var(--accent); color: var(--accent); }
  .xs-step:disabled { opacity: 0.4; cursor: not-allowed; }
  .xs-step:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
  .xs-count { min-width: 6rem; text-align: center; font-size: 1.05rem; font-weight: 700; color: var(--fg); font-variant-numeric: tabular-nums; }

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

  .vg-actions {
    display: grid; grid-template-columns: 7.2rem 7.2rem 4.8rem; gap: 0.4rem;
    justify-content: center; align-items: center;
  }
  .vg-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; white-space: nowrap; cursor: pointer;
    padding: 0.5rem 0.8rem; border-radius: 0.5rem; border: 1px solid var(--border); background: transparent; color: var(--fg);
    font: 600 0.78rem/1 var(--font-body, 'Outfit', sans-serif); text-decoration: none; transition: border-color 0.15s, color 0.15s, background 0.15s, filter 0.15s;
  }
  /* A guest with several bookings has one button, its own size, centred. */
  .vg-actions.is-one { display: flex; justify-content: center; }
  .vg-actions.is-one > .vg-btn:not(.vg-lifecycle-btn) { width: auto; }
  .vg-actions .vg-lifecycle-btn { width: 100%; box-sizing: border-box; }
  .vg-actions > .vg-btn:not(.vg-lifecycle-btn) { width: 100%; box-sizing: border-box; }
  .vg-btn i { font-size: 0.72rem; }
  .vg-btn:hover { border-color: var(--accent); color: var(--accent); }
  .vg-btn-main { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .vg-btn-main:hover { filter: brightness(1.08); color: var(--bg); }
  .vg-btn-line { border-color: var(--accent); color: var(--accent); }
  .vg-btn-line:hover { background: var(--vg-tint); }
  .vg-btn:disabled { opacity: 0.6; cursor: default; background: transparent; color: var(--fg-muted); border-color: var(--border); }
  .vg-btn:focus-visible, .vg-filter:focus-visible, .vg-page:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

  /* A room's several bookings, and a guest's other ones, in Details: one card each. */
  .vg-bk-tag { color: var(--accent); font-weight: 600; }
  .vg-bk-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(13rem, 100%), 1fr)); gap: 0.5rem; }
  .vg-bk {
    display: flex; flex-direction: column; gap: 0.15rem; text-align: left; cursor: pointer; min-width: 0;
    padding: 0.6rem 0.75rem; border-radius: 0.5rem; border: 1px solid var(--border); background: transparent; color: var(--fg-muted);
    font: 400 0.76rem/1.35 var(--font-body, 'Outfit', sans-serif); transition: border-color 0.15s, background 0.15s;
  }
  .vg-bk:hover { border-color: var(--accent); }
  .vg-bk:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
  .vg-bk.is-on { border-color: var(--accent); background: color-mix(in srgb, var(--accent) 12%, transparent); }
  .vg-bk-top b { color: var(--accent); }
  .vg-bk-name { color: var(--fg); font-weight: 600; font-size: 0.85rem; overflow-wrap: anywhere; }
  .vg-bk-sub { overflow-wrap: anywhere; }
  .vg-bk-section { margin-top: 1.4rem; padding-top: 1rem; border-top: 1px solid var(--border); scroll-margin-top: 1rem; }
  .vg-bk-head { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; flex-wrap: wrap; }
  .vg-bk-head h3 { margin: 0; font-size: 1.05rem; color: var(--fg); }
  .vg-bk-actions { display: flex; flex-wrap: wrap; gap: 0.4rem; margin-top: 0.75rem; }
  /* Senior Citizen / PWD entries in Details: hairline-separated, no boxes */
  .vg-disc { padding: 0.55rem 0; border-bottom: 1px solid var(--border); }
  .vg-disc-foot { display: flex; align-items: center; gap: 0.4rem 0.7rem; flex-wrap: wrap; }
  .vg-status-list { display: flex; flex-wrap: wrap; gap: 0.3rem; }

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
    .vg-actions, .vg-actions.is-one { display: flex; justify-content: flex-start; flex-wrap: wrap; }
    .vg-actions-gap { display: none; }
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
  /* The folio and receipt sheets carry their own styles (public/js/hms-guest-folio.js). */
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
  // The top of the folio and receipt: the team's own hotel name, address and contact,
  // the same as the dine-in bill prints (HotelTemplateBuilder::printIdentity). A field
  // the team has not filled in is left off.
  @php
    $printIdentity = \App\Support\HotelTemplateBuilder::printIdentity(
        $templateCustomizations ?? [], $groupName ?? null, isset($facultyId) ? (int) $facultyId : null, $selectedTemplate ?? null
    );
  @endphp
  window.HMS_HOTEL_IDENTITY = @json($printIdentity);
  window.HMS_HOTEL_NAME = window.HMS_HOTEL_IDENTITY.name;
  window.HMS_HOTEL_INFO = window.HMS_HOTEL_IDENTITY;
</script>
{{-- The folio and receipt sheet, shared with the guest's My Stay page. --}}
<script src="{{ asset('js/hms-guest-folio.js') }}?v={{ filemtime(public_path('js/hms-guest-folio.js')) }}"></script>
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
  if (s === 'Arrived' || s === 'Pending Room Inspection') return 'status-cleaning';
  return 'status-reserved';
}

function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0';
  // A refund is stored as a negative payment.
  if (n < 0) return '−₱' + (-n).toLocaleString();
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
  if (!reservation || (reservation.status !== 'Checked In' && reservation.status !== 'Pending Room Inspection')) {
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

/* Where a check-out stands. Housekeeping inspects the room first ('waiting'); any
   damage it reports is Front Desk's to charge or waive ('review'); then the final
   bill can close the stay ('ready'). Null for a stay not being checked out. */
function checkoutStage(reservation) {
  if (!reservation || reservation.status !== 'Pending Room Inspection') return null;
  const inspection = reservation.inspection;
  if (!inspection || inspection.status !== 'Reported') return 'waiting';
  return (inspection.damages || []).some(d => d.status === 'Pending Review') ? 'review' : 'ready';
}

function statusHint(reservation) {
  const stage = checkoutStage(reservation);
  if (!stage) return STATUS_HINTS[reservation.status];
  if (stage === 'waiting') return 'Housekeeping is checking the room';
  const damages = reservation.inspection.damages || [];
  const left = damages.filter(d => d.status === 'Pending Review').length;
  if (stage === 'review') return `${left} damage item${left === 1 ? '' : 's'} to review`;
  return damages.length ? 'Damage reviewed, ready to bill' : 'No damage, ready to bill';
}

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

/* The guest's folio on paper, in the client's statement-of-account layout. Drawn by
   public/js/hms-guest-folio.js, the same sheet the guest sees on their My Stay page;
   every value is escaped there. official: the receipt handed over at check-out;
   otherwise the running Statement of Account. */
function GuestFolio({ bill, bills, cash, official }) {
  // bills: rooms booked together, drawn on one sheet (Group Folio / combined receipt).
  const html = window.HmsGuestFolio
    ? window.HmsGuestFolio.html({ hotel: window.HMS_HOTEL_IDENTITY || { name: window.HMS_HOTEL_NAME }, bill, bills, cash, official })
    : '';
  return <div dangerouslySetInnerHTML={{ __html: html }} />;
}

// Prints the folio next to the button, alone, from a hidden frame.
function printFolio(e) {
  if (window.HmsGuestFolio) window.HmsGuestFolio.print(e.currentTarget.closest('.bill-body').querySelector('.receipt-print'));
}

/* The guest's receipt, shown once the stay is checked out. receipt.cash, set when
   the check-out was paid in cash, carries the cash handed over and the change; it
   is not stored, so only this receipt has it. */
function ReceiptModal({ receipt, onClose }) {
  useEffect(() => {
    if (!receipt) return;
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [receipt, onClose]);

  if (!receipt) return null;

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
          <GuestFolio bill={receipt.bill} cash={receipt.cash} official />

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.6rem', marginTop: '1.2rem' }}>
            <button type="button" className="btn-outline" onClick={printFolio}>
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
 * Rooms booked together for one guest (one group_ref), shown on each room's Guest Bill:
 * every room with its balance, the Group Folio (one Statement of Account for all of
 * them), and one payment for the rooms settled together, spread over what each owes,
 * with a combined receipt. Each room still checks out on its own, on its own date.
 */
function GroupBillPanel({ bill, onPaid, onToast }) {
  const [bills, setBills] = useState(null);
  const [error, setError] = useState('');
  const [paying, setPaying] = useState(false);
  const [picked, setPicked] = useState([]);
  const [method, setMethod] = useState('Cash');
  const [amount, setAmount] = useState('');
  const [reference, setReference] = useState('');
  const [busy, setBusy] = useState(false);
  const [sheet, setSheet] = useState(null);   // { bills, official, cash, title }

  const request = (url, method, body) => fetch(url, {
    method, credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
    body: body ? JSON.stringify(body) : undefined,
  }).then(r => r.json().catch(() => ({})).then(d => (r.ok ? d : Promise.reject(d))));

  const load = () => request('/students/hotel/bookings/' + bill.bookingId + '/group', 'GET')
    .then(d => { setBills(d.bills || []); setError(''); })
    .catch(e => setError((e && e.message) || 'Could not load the rooms booked together.'));

  // Re-read when this room's bill changes, so the group's balances follow it.
  useEffect(() => { load(); }, [bill.bookingId, bill.balance, bill.total]);

  if (!bills) return error ? <p className="bill-section-note" style={{ color: 'var(--danger, #fb7185)' }}>{error}</p> : null;
  if (bills.length < 2) return null;

  const isOpen = (b) => !['Checked Out', 'Cancelled'].includes(b.status);
  const owing = bills.filter(b => isOpen(b) && Number(b.balance) > 0);
  const chosen = owing.filter(b => picked.includes(b.bookingId));
  const owed = Math.round(chosen.reduce((n, b) => n + (Number(b.balance) || 0), 0) * 100) / 100;
  const entered = Math.max(0, parseFloat(amount) || 0);
  const isCash = method === 'Cash';
  const paid = isCash ? Math.min(entered, owed) : entered;
  const change = isCash ? Math.max(0, entered - owed) : 0;
  const groupBalance = bills.reduce((n, b) => n + (Number(b.balance) || 0), 0);

  const startPaying = () => {
    const ids = owing.map(b => b.bookingId);
    setPicked(ids);
    const total = owing.reduce((n, b) => n + (Number(b.balance) || 0), 0);
    setAmount(String(Math.round(total * 100) / 100));
    setPaying(true);
  };
  const toggle = (id) => setPicked(ids => (ids.includes(id) ? ids.filter(x => x !== id) : [...ids, id]));

  const pay = (e) => {
    e.preventDefault();
    if (!chosen.length) { setError('Pick the rooms this payment is for.'); return; }
    if (!(paid > 0)) { setError('Enter the amount received.'); return; }
    if (!isCash && paid > owed + 0.005) { setError('That is more than these rooms owe (' + formatPeso(owed) + ').'); return; }
    setBusy(true);
    setError('');
    const settledIds = chosen.map(b => b.bookingId);
    request('/students/hotel/bookings/' + bill.bookingId + '/group-payments', 'POST', {
      amount_paid: Math.round(paid * 100) / 100, method, reference: reference.trim(), booking_ids: settledIds,
    })
      .then(d => {
        const fresh = d.bills || [];
        setBills(fresh);
        setPaying(false);
        if (onToast) onToast(formatPeso(paid) + ' received by ' + method + ' for ' + settledIds.length + ' rooms.' + (change > 0 ? ' Change: ' + formatPeso(change) + '.' : ''));
        // One receipt for the rooms just settled together.
        setSheet({
          bills: fresh.filter(b => settledIds.includes(b.bookingId)),
          official: true,
          cash: isCash ? { tendered: entered, change } : null,
          title: 'Receipt · ' + settledIds.length + ' rooms',
        });
        if (onPaid) onPaid();
      })
      .catch(err => setError((err && err.message) || 'Could not record that payment.'))
      .finally(() => setBusy(false));
  };

  return (
    <div style={{ border: '1px solid var(--accent)', borderRadius: 10, padding: '0.8rem 0.95rem', margin: '0 0 1rem' }}>
      <p style={{ margin: 0, color: 'var(--accent)', fontSize: '0.62rem', fontWeight: 700, letterSpacing: '0.14em', textTransform: 'uppercase' }}>
        <i className="fa-solid fa-link" style={{ marginRight: 6 }}></i>Booked together · {bills.length} rooms
      </p>
      <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.4rem', marginTop: '0.5rem' }}>
        {bills.map(b => (
          <span key={b.bookingId} style={{ border: '1px solid var(--border)', borderRadius: 8, padding: '0.25rem 0.55rem', fontSize: '0.74rem', color: 'var(--fg)' }}>
            <b>{b.roomName || 'Room'}</b>
            <span style={{ color: 'var(--fg-muted)' }}> · {b.status} · {Number(b.balance) > 0 ? formatPeso(b.balance) + ' due' : 'paid'}</span>
          </span>
        ))}
      </div>
      <p style={{ margin: '0.5rem 0 0', fontSize: '0.78rem', color: 'var(--fg-muted)' }}>
        Group balance <b style={{ color: groupBalance > 0 ? 'var(--danger, #fb7185)' : 'var(--success, #4ade80)' }}>{formatPeso(groupBalance)}</b>.
        Each room checks out on its own date; rooms leaving together can be paid together.
      </p>
      <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap', marginTop: '0.6rem' }}>
        <button type="button" className="btn-outline" style={{ padding: '0.45rem 0.8rem', fontSize: '0.66rem' }}
          onClick={() => setSheet({ bills, official: false, title: 'Group Folio' })}>
          <i className="fa-solid fa-file-invoice" style={{ marginRight: 6 }}></i>Group Folio
        </button>
        {owing.length > 0 && !paying && (
          <button type="button" className="btn-solid" style={{ padding: '0.45rem 0.8rem', fontSize: '0.66rem' }} onClick={startPaying}>
            <i className="fa-solid fa-money-bill-wave" style={{ marginRight: 6 }}></i>Pay for rooms together
          </button>
        )}
      </div>

      {paying && (
        <form onSubmit={pay} style={{ marginTop: '0.8rem', display: 'grid', gap: '0.55rem' }}>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.4rem' }}>
            {owing.map(b => (
              <label key={b.bookingId} style={{ display: 'inline-flex', alignItems: 'center', gap: 6, fontSize: '0.76rem', color: 'var(--fg)', border: '1px solid var(--border)', borderRadius: 8, padding: '0.3rem 0.55rem', cursor: 'pointer' }}>
                <input type="checkbox" checked={picked.includes(b.bookingId)} onChange={() => toggle(b.bookingId)} />
                {b.roomName} · {formatPeso(b.balance)}
              </label>
            ))}
          </div>
          <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.4rem' }}>
            <select className="booking-input" value={method} onChange={e => setMethod(e.target.value)} style={{ flex: '1 0 7rem', width: 'auto' }}>
              {PAYMENT_METHODS.map(m => <option key={m} value={m}>{m}</option>)}
            </select>
            <input type="number" className="booking-input" min="0" step="0.01" value={amount} onChange={e => setAmount(e.target.value)}
              placeholder={isCash ? 'Cash received' : 'Amount'} aria-label={isCash ? 'Cash received' : 'Amount received'} style={{ flex: '1 1 7rem', width: 'auto' }} />
            {!isCash && (
              <input type="text" className="booking-input" placeholder="Ref # / card last 4" value={reference}
                onChange={e => setReference(e.target.value)} style={{ flex: '1 1 7rem', width: 'auto' }} />
            )}
          </div>
          <p style={{ margin: 0, fontSize: '0.76rem', color: paid + 0.005 >= owed ? 'var(--success, #4ade80)' : 'var(--danger, #fb7185)' }}>
            {chosen.length} {chosen.length === 1 ? 'room owes' : 'rooms owe'} {formatPeso(owed)}.
            {paid + 0.005 >= owed ? ' This pays them in full.' : ' ' + formatPeso(Math.max(0, owed - paid)) + ' will still be unpaid.'}
            {change > 0 ? ' Change: ' + formatPeso(change) + '.' : ''}
          </p>
          <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
            <button type="submit" className="btn-solid" disabled={busy} style={{ flex: '1 1 10rem' }}>
              {busy ? 'Working…' : 'Take payment for ' + chosen.length + ' rooms'}
            </button>
            <button type="button" className="btn-outline" disabled={busy} onClick={() => setPaying(false)} style={{ padding: '0.5rem 0.9rem', fontSize: '0.68rem' }}>Cancel</button>
          </div>
        </form>
      )}
      {error && <p style={{ margin: '0.5rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.76rem' }}>{error}</p>}

      {sheet && (
        <FolioModal bills={sheet.bills} official={sheet.official} cash={sheet.cash} title={sheet.title} onClose={() => setSheet(null)} />
      )}
    </div>
  );
}

/* The Statement of Account for a stay still open, opened from the Guest Bill. With
   bills, rooms booked together on one sheet: the Group Folio, or (official, with the
   cash) the combined receipt for rooms settled together. */
function FolioModal({ bill, bills, official, cash, title, onClose }) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  return (
    <div className="bill-overlay" onClick={onClose} role="dialog" aria-modal="true" aria-label="Guest folio">
      <div className="bill-modal" onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.2em', textTransform: 'uppercase', margin: '0 0 0.35rem' }}>Front Desk</p>
            <h2 className="font-display" style={{ fontSize: '1.5rem', margin: 0, color: 'var(--fg)' }}>{title || 'Guest Folio'}</h2>
          </div>
          <button type="button" onClick={onClose} aria-label="Close"
            style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: 'pointer', flexShrink: 0 }}>
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div className="bill-body">
          <GuestFolio bill={bill} bills={bills} official={!!official} cash={cash || null} />

          <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.6rem', marginTop: '1.2rem' }}>
            <button type="button" className="btn-outline" onClick={printFolio}>
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
function FinalBillModal({ open, bill, loading, error, canCheckOut, addonCatalog, onClose, onAddCharge, onRemoveCharge, onAddAddon, onPay, onSettle, onToast, onGroupPaid }) {
  const [method, setMethod] = useState('Cash');
  const [amount, setAmount] = useState('');
  const [reference, setReference] = useState('');
  const [chargeDesc, setChargeDesc] = useState('');
  const [chargeAmount, setChargeAmount] = useState('');
  const [showChargeForm, setShowChargeForm] = useState(false);
  const [addonId, setAddonId] = useState('');
  const [addonQty, setAddonQty] = useState('1');
  const [showAddonForm, setShowAddonForm] = useState(false);
  const [busy, setBusy] = useState(false);
  const [showFolio, setShowFolio] = useState(false);

  const balance = bill ? Number(bill.balance) || 0 : 0;
  const refundDue = bill ? Number(bill.refundDue) || 0 : 0;

  // Opening the bill pre-fills the balance: settling it in full is the common case,
  // and the field stays editable for a guest paying part of it.
  useEffect(() => {
    if (!open) return;
    setMethod('Cash');
    setReference('');
    setChargeDesc('');
    setChargeAmount('');
    setShowChargeForm(false);
    setAddonId('');
    setAddonQty('1');
    setShowAddonForm(false);
    setShowFolio(false);
  }, [open]);

  // Re-filled whenever the balance moves (a charge added, a payment taken).
  useEffect(() => {
    if (open && bill) setAmount(balance > 0 ? String(balance) : '0');
  }, [open, bill && bill.bookingId, balance]);

  useEffect(() => {
    // While the folio is open on top, Escape closes only the folio.
    if (!open || showFolio) return;
    const onKey = (e) => { if (e.key === 'Escape' && !busy) onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [open, onClose, busy, showFolio]);

  if (!open) return null;

  // Cash: the field is what the guest hands over. Only the balance is recorded as
  // paid; the rest goes back as change. Other methods record the amount as typed.
  const entered = Math.max(0, parseFloat(amount) || 0);
  const isCash = method === 'Cash';
  const paid = isCash ? Math.min(entered, Math.max(0, balance)) : entered;
  const change = isCash ? Math.max(0, entered - Math.max(0, balance)) : 0;
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

  const submitAddon = (e) => {
    e.preventDefault();
    const qty = parseInt(addonQty, 10);
    if (!addonId) { if (onToast) onToast('Pick an extra first.'); return; }
    if (!(qty > 0)) { if (onToast) onToast('Enter how many.'); return; }
    setBusy(true);
    Promise.resolve(onAddAddon(Number(addonId), qty))
      .then(() => { setAddonId(''); setAddonQty('1'); setShowAddonForm(false); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not add that extra.'); })
      .finally(() => setBusy(false));
  };

  // A payment during the stay (a deposit, an upgrade difference) without checking out.
  const payOnly = () => {
    if (!(paid > 0)) { if (onToast) onToast('Enter the amount received.'); return; }
    setBusy(true);
    Promise.resolve(onPay({ amount: paid, method, reference: reference.trim(), full: remainingAfter <= 0 }))
      .then(() => { setReference(''); if (onToast) onToast(formatPeso(paid) + ' received by ' + method + '.' + (change > 0 ? ' Change: ' + formatPeso(change) + '.' : '')); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not record that payment.'); })
      .finally(() => setBusy(false));
  };

  // Hands back what the guest overpaid, by the method chosen below.
  const refund = () => {
    if (!window.confirm(`Give ${formatPeso(refundDue)} back to the guest by ${method}?`)) return;
    setBusy(true);
    Promise.resolve(onPay({ type: 'Refund', amount: refundDue, method, reference: reference.trim() }))
      .then(() => { if (onToast) onToast(formatPeso(refundDue) + ' refunded by ' + method + '.'); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not record that refund.'); })
      .finally(() => setBusy(false));
  };

  const settle = (e) => {
    e.preventDefault();
    if (!canCheckOut) { payOnly(); return; }
    // A short payment is allowed — a written-off or disputed stay still has to close —
    // but the desk is told what is being left behind before it does.
    if (remainingAfter > 0 && !window.confirm(
      `${formatPeso(remainingAfter)} will still be unpaid after this. Check out anyway?`
    )) return;
    setBusy(true);
    Promise.resolve(onSettle({ amount: paid, method, reference: reference.trim(), tendered: entered, change }))
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not complete check-out.'); })
      .finally(() => setBusy(false));
  };

  const roomLines = bill ? bill.room : null;
  const service = bill ? bill.roomService : null;
  const extras = bill ? bill.otherCharges : null;
  const addons = bill ? bill.addons : null;

  return (
    <div className="bill-overlay" onClick={() => { if (!busy) onClose(); }} role="dialog" aria-modal="true">
      <div className="bill-modal is-wide" onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.2em', textTransform: 'uppercase', margin: '0 0 0.35rem' }}>Front Desk</p>
            <h2 className="font-display" style={{ fontSize: '1.5rem', margin: 0, color: 'var(--fg)' }}>{canCheckOut ? 'Final Guest Bill' : 'Guest Bill'}</h2>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', flexShrink: 0 }}>
            {bill && !loading && !error && (
              <button type="button" className="btn-outline" onClick={() => setShowFolio(true)} disabled={busy}
                title="Statement of Account (Guest Folio), ready to print"
                style={{ padding: '0.45rem 0.8rem', fontSize: '0.66rem' }}>
                <i className="fa-solid fa-print" style={{ marginRight: 6 }}></i>Guest Folio
              </button>
            )}
            <button type="button" onClick={onClose} disabled={busy} aria-label="Close"
              style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: busy ? 'not-allowed' : 'pointer', flexShrink: 0 }}>
              <i className="fa-solid fa-xmark"></i>
            </button>
          </div>
        </div>
        {showFolio && bill && <FolioModal bill={bill} onClose={() => setShowFolio(false)} />}

        <div className="bill-body">
          {loading ? (
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.85rem', margin: 0 }}>Loading the bill…</p>
          ) : error ? (
            <p style={{ color: 'var(--danger, #fb7185)', fontSize: '0.85rem', margin: 0 }}>{error}</p>
          ) : !bill ? null : (
            <>
              <dl className="bill-meta is-four">
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

              {bill.groupRef && <GroupBillPanel bill={bill} onPaid={onGroupPaid} onToast={onToast} />}

              <div className="bill-cols">
              <div>
              <p className="bill-section-title">Room Charges</p>
              <BillItem name={'Room ' + (bill.roomName || '')} detail={roomUnit(roomLines)} amount={formatPeso(roomLines.subtotal)} />
              {/* Each child as the child policy priced them at booking, free ones too. */}
              {((bill.children && bill.children.items) || []).map((c, i) => (
                <BillItem key={'ch' + i} name={`Child, age ${c.age}`}
                  detail={c.rate ? `${c.nights} night${c.nights === 1 ? '' : 's'} × ${formatPeso(c.rate)}` : c.label}
                  amount={c.line ? formatPeso(c.line) : 'Free'} />
              ))}
              {/* Senior Citizen / PWD, applied at booking: shown, never applied again here.
                  The room line above stays at full price. */}
              {((bill.discounts && bill.discounts.items) || []).map(d => (
                d.verified ? (
                  <React.Fragment key={'d' + d.id}>
                    <BillItem name={'VAT exemption · ' + d.fullName} detail={`${d.type} · 12% VAT on their ${formatPeso(d.eligible)} share`} amount={formatPeso(-d.vat)} />
                    <BillItem name={d.type + ' discount · ' + d.fullName} detail={`20% of ${formatPeso(d.vatExemptPrice)}`} amount={formatPeso(-d.discount)} />
                  </React.Fragment>
                ) : (
                  <BillItem key={'d' + d.id} name={d.type + ' · ' + d.fullName}
                    detail="Pending ID verification. Verify it under Details to apply." amount="Not applied" />
                )
              ))}

              {service.orders.length > 0 && (
                <>
                  <p className="bill-section-title">Room Service</p>
                  {service.orders.map(order => (
                    order.items.map((item, i) => (
                      <BillItem key={order.orderId + '-' + i} name={item.name}
                        detail={`${item.qty} × ${formatPeso(item.price)} · ${order.status}`} amount={formatPeso(item.line)} />
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
                    <BillItem key={addon.id} name={addon.name} detail={addonUnit(addon)} amount={formatPeso(addon.line)} />
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
              ) : showAddonForm ? (
                <form onSubmit={submitAddon} style={{ display: 'flex', gap: '0.4rem', marginTop: '0.6rem', flexWrap: 'wrap' }}>
                  <select className="booking-input" value={addonId} onChange={e => setAddonId(e.target.value)} style={{ flex: '2 1 160px', width: 'auto' }}>
                    <option value="">Pick an extra…</option>
                    {(addonCatalog || []).map(a => (
                      <option key={a.dbId} value={String(a.dbId)} disabled={a.available <= 0}>
                        {a.name} · {a.price ? formatPeso(a.price) + (a.extraBed ? ' / night' : '') : 'Free'} · {a.available} left
                      </option>
                    ))}
                  </select>
                  <input type="number" className="booking-input" min="1" max="99" value={addonQty}
                    onChange={e => setAddonQty(e.target.value)} style={{ flex: '0 1 70px', width: 'auto' }} aria-label="How many" />
                  <button type="submit" className="btn-solid" disabled={busy} style={{ padding: '0.5rem 0.9rem' }}>Add</button>
                  <button type="button" className="btn-outline" disabled={busy} onClick={() => setShowAddonForm(false)}
                    style={{ padding: '0.5rem 0.9rem', fontSize: '0.68rem' }}>Cancel</button>
                </form>
              ) : (
                <div style={{ display: 'flex', gap: '1.1rem', flexWrap: 'wrap' }}>
                  <button type="button" onClick={() => setShowChargeForm(true)} disabled={busy}
                    style={{ background: 'none', border: 'none', color: 'var(--accent)', cursor: busy ? 'not-allowed' : 'pointer', fontSize: '0.75rem', padding: '0.5rem 0 0', fontFamily: 'var(--font-body, Outfit, sans-serif)' }}>
                    <i className="fa-solid fa-plus" style={{ fontSize: '0.65rem', marginRight: 5 }}></i> Add charge
                  </button>
                  {onAddAddon && (
                    <button type="button" onClick={() => setShowAddonForm(true)} disabled={busy}
                      title="Pillows, towels, an extra bed from Housekeeping"
                      style={{ background: 'none', border: 'none', color: 'var(--accent)', cursor: busy ? 'not-allowed' : 'pointer', fontSize: '0.75rem', padding: '0.5rem 0 0', fontFamily: 'var(--font-body, Outfit, sans-serif)' }}>
                      <i className="fa-solid fa-bell-concierge" style={{ fontSize: '0.65rem', marginRight: 5 }}></i> Add extras
                    </button>
                  )}
                </div>
              )}

              {/* On every charge above, after the Senior Citizen / PWD discounts. Priced
                  by the server; a stay booked before there was one shows none. */}
              {bill.serviceCharge && bill.serviceCharge.amount > 0 && (
                <>
                  <p className="bill-section-title">Service Charge</p>
                  <BillItem name={`Service charge (${bill.serviceCharge.rate}%)`}
                    detail={`On ${formatPeso(bill.serviceCharge.base)}, after discounts`}
                    amount={formatPeso(bill.serviceCharge.amount)} />
                </>
              )}
              </div>

              <div>
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
                      <span>{p.type === 'Refund' ? 'Refund to guest' : p.type + ' payment'}</span>
                      <span className="bill-amt">{p.amountPaid < 0 ? '+ ' + formatPeso(-p.amountPaid) : '− ' + formatPeso(p.amountPaid)}</span>
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
              {/* A discount verified after the guest paid full price leaves money to give
                  back. Recorded as its own refund line; the earlier payment stays as taken. */}
              {refundDue > 0 && (
                <div className="bill-line is-subtotal is-balance">
                  <span>
                    Refund Due to Guest
                    <button type="button" className="btn-outline" disabled={busy} onClick={refund}
                      style={{ marginLeft: '0.6rem', padding: '0.3rem 0.7rem', fontSize: '0.66rem' }}>
                      Record refund
                    </button>
                  </span>
                  <span className="bill-amt">{formatPeso(refundDue)}</span>
                </div>
              )}

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
                    <label style={{ fontSize: '0.6rem', letterSpacing: '0.12em', textTransform: 'uppercase', color: 'var(--fg-muted)', display: 'block', marginBottom: '0.35rem' }}>{isCash ? 'Cash Received' : 'Amount Received'}</label>
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
                {change > 0 && (
                  <p style={{ margin: '0.35rem 0 0', fontSize: '0.85rem', fontWeight: 700, color: 'var(--fg)' }}>
                    Change: {formatPeso(change)}
                  </p>
                )}
                <div style={{ display: 'flex', gap: '0.6rem', marginTop: '1rem', flexWrap: 'wrap' }}>
                  <button type="button" className={canCheckOut ? 'btn-outline' : 'btn-solid'} disabled={busy || paid <= 0} onClick={payOnly}
                    style={{ flex: '1 1 10rem' }} title="Record the money without checking the guest out">
                    <i className="fa-solid fa-money-bill-wave" style={{ fontSize: '0.7rem' }}></i>
                    {busy ? 'Working…' : 'Take Payment'}
                  </button>
                  {canCheckOut && (
                    <button type="submit" className="btn-solid" disabled={busy} style={{ flex: '1 1 12rem' }}>
                      <i className="fa-solid fa-right-from-bracket" style={{ fontSize: '0.7rem' }}></i>
                      {busy ? 'Working…' : 'Take Payment & Check Out'}
                    </button>
                  )}
                </div>
              </form>
              </div>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

/* The Senior Citizens and PWDs on a stay, the booker or companions, each with their
   ID check. Verifying a pending one takes their discount off the bill from then on. */
function DiscountList({ discounts, open, onVerify }) {
  const [busyId, setBusyId] = useState(null);
  const verify = (d) => {
    const go = () => {
      setBusyId(d.id);
      Promise.resolve(onVerify(d)).finally(() => setBusyId(null));
    };
    const text = `Confirm you have seen ${d.fullName}'s valid ${d.type} ID (${d.idNumber}). The discount comes off the bill from now on.`;
    if (!window.Swal) { if (window.confirm(text)) go(); return; }
    window.Swal.fire({
      title: 'Verify ID?', text, icon: 'question', showCancelButton: true,
      confirmButtonText: 'Yes, ID verified', cancelButtonText: 'Not yet',
      background: fdThemeColor('--card', '#181714'), color: fdThemeColor('--fg', '#f5f0e8'),
      confirmButtonColor: fdThemeColor('--accent', '#c9a84c'), cancelButtonColor: '#71717a', heightAuto: false,
    }).then(r => { if (r.isConfirmed) go(); });
  };
  return (
    <>
      <p className="bill-section-title">Senior Citizen / PWD</p>
      {discounts.map(d => (
        <div key={d.id} className="vg-disc">
          <div className="bill-line" style={{ padding: 0 }}>
            <span style={{ color: 'var(--fg)' }}>{d.fullName} <small style={{ color: 'var(--fg-muted)' }}>· {d.who}</small></span>
            <span className="bill-amt">{d.verified ? '− ' + formatPeso(d.less) : '—'}</span>
          </div>
          <p className="bill-subnote">
            {d.type} · ID {d.idNumber}{d.birthDate ? ' · born ' + formatBillDate(d.birthDate) : ''} · share {formatPeso(d.eligible)}
          </p>
          <div className="vg-disc-foot">
            <span className={'room-status-badge ' + (d.verified ? 'status-available' : 'status-cleaning')} style={{ position: 'static' }}>
              {d.status}
            </span>
            {d.verified ? (
              <span className="bill-subnote" style={{ margin: 0 }}>
                VAT exemption {formatPeso(d.vat)} + 20% {formatPeso(d.discount)}{d.verifiedBy ? ' · checked by ' + d.verifiedBy : ''}
              </span>
            ) : open && onVerify ? (
              <button type="button" className="vg-btn vg-btn-line" disabled={busyId === d.id} onClick={() => verify(d)}
                title="You have seen the guest's valid ID">
                <i className="fa-solid fa-id-card"></i> {busyId === d.id ? 'Saving…' : 'Mark ID verified'}
              </button>
            ) : (
              <span className="bill-subnote" style={{ margin: 0 }}>Not applied: the ID was never verified.</span>
            )}
          </div>
        </div>
      ))}
    </>
  );
}

/* Applied Senior Citizen / PWD adjustments on a bill: the VAT removed and the 20%,
   each its own line under the full-price room, as the receipt must show them. */
function discountBillLines(bill) {
  return ((bill.discounts && bill.discounts.items) || []).filter(d => d.verified);
}

/* One bill line: the name, its unit price under it, the amount on the right. */
function BillItem({ name, detail, amount, children }) {
  return (
    <div className="bill-line bill-item">
      <span className="bill-item-text">
        <span className="bill-item-name">{name}{children}</span>
        {detail && <span className="bill-unit">{detail}</span>}
      </span>
      <span className="bill-amt">{amount}</span>
    </div>
  );
}

// "3 × ₱1,300", "2 × ₱500 × 3 nights": what each bill line is made of.
function roomUnit(room) {
  return room.promo ? room.promo + ' package' : `${room.blocks} × ${formatPeso(room.rate)} per ${room.blockHours} hrs`;
}
function addonUnit(a) {
  return `${a.qty} × ${formatPeso(a.unitPrice)}` + (a.perNight ? ` × ${a.nights} night${a.nights === 1 ? '' : 's'}` : '');
}

/* 'YYYY-MM-DD' plus n days, built from the parts like formatBillDate() so the date
   is not shifted by a UTC read. */
function addDays(dateStr, days) {
  const [y, m, d] = String(dateStr || '').split('-').map(Number);
  if (!y || !m || !d) return '';
  const next = new Date(y, m - 1, d + days);
  const pad = n => String(n).padStart(2, '0');
  return `${next.getFullYear()}-${pad(next.getMonth() + 1)}-${pad(next.getDate())}`;
}

const MAX_EXTRA_NIGHTS = 30;

/*
 * A checked-in guest staying longer. The desk picks how many more nights; the new
 * check-out keeps the same clock time, and the extra room charge is shown before
 * saving so the student sees what it does to the bill. Nothing is paid here — the
 * balance is settled at check-out like the rest of the stay.
 */
function ExtendStayModal({ room, reservation, onClose, onSave }) {
  const [nights, setNights] = useState(1);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape' && !busy) onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose, busy]);

  const rate = Number(reservation.roomRate || (room && room.price)) || 0;
  const newCheckOut = addDays(reservation.checkOut, nights);
  const addedBlocks = stayBlocks(reservation.checkIn, newCheckOut, reservation.checkInTime)
    - stayBlocks(reservation.checkIn, reservation.checkOut, reservation.checkInTime);
  const extraCharge = addedBlocks * rate;
  const balanceNow = Number(reservation.outstanding) || 0;

  const change = (by) => {
    setError('');
    setNights(n => Math.min(MAX_EXTRA_NIGHTS, Math.max(1, n + by)));
  };

  const submit = (e) => {
    e.preventDefault();
    setBusy(true);
    setError('');
    Promise.resolve(onSave(newCheckOut))
      .catch(err => { setError((err && err.message) || 'Could not extend this stay.'); setBusy(false); });
  };

  return (
    <div className="bill-overlay" onClick={() => { if (!busy) onClose(); }} role="dialog" aria-modal="true" aria-labelledby="xs-title">
      <div className="bill-modal" style={{ maxWidth: '28rem' }} onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.2em', textTransform: 'uppercase', margin: '0 0 0.35rem' }}>Front Desk</p>
            <h2 id="xs-title" className="font-display" style={{ fontSize: '1.5rem', margin: 0, color: 'var(--fg)' }}>Extend Stay</h2>
            <p style={{ margin: '0.3rem 0 0', color: 'var(--fg-muted)', fontSize: '0.84rem' }}>
              {reservation.fullName || 'Guest'} · {room ? room.name : 'Room'}
            </p>
          </div>
          <button type="button" onClick={onClose} disabled={busy} aria-label="Close"
            style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: busy ? 'not-allowed' : 'pointer', flexShrink: 0 }}>
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form className="bill-body" onSubmit={submit}>
          <dl className="bill-meta">
            <div><dt>Check-out now</dt><dd>{formatBillDateTime(reservation.checkOut, reservation.checkInTime)}</dd></div>
            <div><dt>New check-out</dt><dd style={{ color: 'var(--accent-light)', fontWeight: 600 }}>{formatBillDateTime(newCheckOut, reservation.checkInTime)}</dd></div>
          </dl>

          <p className="bill-section-title">How many more nights?</p>
          <div className="xs-stepper">
            <button type="button" className="xs-step" onClick={() => change(-1)} disabled={busy || nights <= 1} aria-label="One night less">
              <i className="fa-solid fa-minus"></i>
            </button>
            <span className="xs-count" aria-live="polite">{nights} {nights === 1 ? 'night' : 'nights'}</span>
            <button type="button" className="xs-step" onClick={() => change(1)} disabled={busy || nights >= MAX_EXTRA_NIGHTS} aria-label="One night more">
              <i className="fa-solid fa-plus"></i>
            </button>
          </div>

          <p className="bill-section-title">What it adds to the bill</p>
          <div className="bill-line">
            <span>{nights} more {nights === 1 ? 'night' : 'nights'} · {addedBlocks} × {BLOCK_HOURS} hrs × {formatPeso(rate)}</span>
            <span className="bill-amt">+ {formatPeso(extraCharge)}</span>
          </div>
          <div className="bill-line"><span>Balance now</span><span className="bill-amt">{formatPeso(balanceNow)}</span></div>
          <div className="bill-line is-subtotal is-balance">
            <span>Balance after extending</span><span className="bill-amt">{formatPeso(balanceNow + extraCharge)}</span>
          </div>
          <p className="bill-section-note" style={{ margin: '0.5rem 0 0' }}>
            The guest pays this at check-out, with the rest of the bill.
          </p>

          {error && (
            <p role="alert" style={{ margin: '0.9rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.82rem', lineHeight: 1.5 }}>
              <i className="fa-solid fa-circle-exclamation" style={{ marginRight: 6 }}></i>{error}
            </p>
          )}

          <div style={{ display: 'flex', gap: '0.6rem', justifyContent: 'flex-end', marginTop: '1.3rem', flexWrap: 'wrap' }}>
            <button type="button" className="btn-outline" onClick={onClose} disabled={busy}>Cancel</button>
            <button type="submit" className="btn-solid" disabled={busy}>
              <i className="fa-solid fa-calendar-plus" style={{ fontSize: '0.7rem' }}></i>
              {busy ? 'Saving…' : 'Extend Stay'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

/*
 * Moving a guest who has no key yet, an upgrade at the desk say. Lists the rooms free
 * for the whole stay; the server checks again, reprices the stay at the new room's
 * rate and leaves the payments as they are, so the difference shows as owing.
 */
function ChangeRoomModal({ room, reservation, rooms, onClose, onSave }) {
  const [pickedId, setPickedId] = useState(null);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape' && !busy) onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose, busy]);

  const blocks = stayBlocks(reservation.checkIn, reservation.checkOut, reservation.checkInTime);
  const arrived = reservation.status === 'Arrived';
  // Free for the whole stay; a guest at the desk needs one ready now.
  const options = (rooms || []).filter(r => r.id !== room.id
    && normalizeRoomStatus(r.status) !== 'Maintenance'
    && (!arrived || normalizeRoomStatus(r.status) === 'Available')
    && !(r.bookedRanges || []).some(b => b.from < reservation.checkOut && b.to > reservation.checkIn));
  const picked = options.find(r => r.dbId === pickedId) || null;
  const current = Number(reservation.totalDue) || 0;
  const newCharge = picked ? blocks * (Number(picked.price) || 0) : 0;
  const difference = newCharge - current;

  const submit = (e) => {
    e.preventDefault();
    if (!picked) { setError('Pick the room to move the guest to.'); return; }
    setBusy(true);
    setError('');
    Promise.resolve(onSave(picked))
      .catch(err => { setError((err && err.message) || 'Could not move this guest.'); setBusy(false); });
  };

  return (
    <div className="bill-overlay" onClick={() => { if (!busy) onClose(); }} role="dialog" aria-modal="true" aria-labelledby="cr-title">
      <div className="bill-modal" style={{ maxWidth: '32rem' }} onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.2em', textTransform: 'uppercase', margin: '0 0 0.35rem' }}>Front Desk</p>
            <h2 id="cr-title" className="font-display" style={{ fontSize: '1.5rem', margin: 0, color: 'var(--fg)' }}>Change Room</h2>
            <p style={{ margin: '0.3rem 0 0', color: 'var(--fg-muted)', fontSize: '0.84rem' }}>
              {reservation.fullName || 'Guest'} · now in {room.name}
            </p>
          </div>
          <button type="button" onClick={onClose} disabled={busy} aria-label="Close"
            style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: busy ? 'not-allowed' : 'pointer', flexShrink: 0 }}>
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form className="bill-body" onSubmit={submit}>
          <p className="bill-section-title" style={{ marginTop: 0 }}>Rooms free for this stay</p>
          {options.length === 0 ? (
            <p style={{ color: 'var(--fg-muted)', fontSize: '0.84rem', margin: 0 }}>
              No other room is free from {formatBillDate(reservation.checkIn)} to {formatBillDate(reservation.checkOut)}.
            </p>
          ) : (
            <select className="booking-input" value={pickedId || ''} onChange={e => { setPickedId(Number(e.target.value) || null); setError(''); }}>
              <option value="">Pick a room…</option>
              {options.map(r => (
                <option key={r.id} value={r.dbId}>{r.name} · {r.label || r.category} · {formatPeso(r.price)} / 12 hrs</option>
              ))}
            </select>
          )}

          {picked && (
            <>
              <p className="bill-section-title">What it does to the bill</p>
              <div className="bill-line"><span>Room charge now ({room.name})</span><span className="bill-amt">{formatPeso(current)}</span></div>
              <div className="bill-line"><span>{picked.name} · {blocks} × {BLOCK_HOURS} hrs × {formatPeso(picked.price)}</span><span className="bill-amt">{formatPeso(newCharge)}</span></div>
              <div className="bill-line is-subtotal is-balance">
                <span>{difference >= 0 ? 'Difference to pay' : 'Cheaper by'}</span><span className="bill-amt">{formatPeso(Math.abs(difference))}</span>
              </div>
              {reservation.promoName && (
                <p className="bill-section-note" style={{ margin: '0.5rem 0 0' }}>The {reservation.promoName} promo comes off: it was for {room.name}.</p>
              )}
              <p className="bill-section-note" style={{ margin: '0.5rem 0 0' }}>Take the difference from the guest with Bill → Take Payment.</p>
            </>
          )}

          {error && (
            <p role="alert" style={{ margin: '0.9rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.82rem', lineHeight: 1.5 }}>
              <i className="fa-solid fa-circle-exclamation" style={{ marginRight: 6 }}></i>{error}
            </p>
          )}

          <div style={{ display: 'flex', gap: '0.6rem', justifyContent: 'flex-end', marginTop: '1.3rem', flexWrap: 'wrap' }}>
            <button type="button" className="btn-outline" onClick={onClose} disabled={busy}>Cancel</button>
            <button type="submit" className="btn-solid" disabled={busy || !picked}>
              <i className="fa-solid fa-right-left" style={{ fontSize: '0.7rem' }}></i>
              {busy ? 'Moving…' : 'Move guest'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

/* "Nov 3 → Nov 5": a booking's dates, short enough for a list line. */
function stayRange(reservation) {
  return `${formatShortDate(reservation.checkIn) || '—'} → ${formatShortDate(reservation.checkOut) || '—'}`;
}

/*
 * Everything recorded for one guest. The table lists the guest once, however many
 * bookings they hold; here each booking is its own numbered section (room, dates,
 * charges, payments) with its own buttons, so several bookings stay apart instead of
 * being merged into one record. The cards at the top jump to each section.
 */
function GuestDetailsModal({ stays, renderActions, onVerifyDiscount, onClose }) {
  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  if (!stays || stays.length === 0) return null;

  const guest = stays[0].reservation;
  const many = stays.length > 1;

  // One payment split over several rooms carries one transactionRef on every share:
  // each booking still lists its own share, with a note naming the whole payment.
  const transactions = {};
  stays.forEach(({ room, reservation }) => (reservation.payments || []).forEach(p => {
    if (!p.transactionRef) return;
    const t = transactions[p.transactionRef] || (transactions[p.transactionRef] = { total: 0, rooms: [] });
    t.total += Number(p.amountPaid) || 0;
    t.rooms.push(room ? room.name : 'a room');
  }));

  const sum = (key) => stays.reduce((n, { reservation }) => n + (Number(reservation[key]) || 0), 0);
  const jumpTo = (id) => {
    const el = document.getElementById('vg-bk-' + id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  return (
    <div className="bill-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="bill-modal is-wide" onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ margin: 0, color: 'var(--accent)', fontSize: '0.62rem', letterSpacing: '0.14em', textTransform: 'uppercase' }}>
              {many ? `${stays.length} bookings` : `Booking #${guest.bookingId}`}
            </p>
            <h2 className="font-display" style={{ margin: '0.25rem 0 0.4rem', fontSize: '1.35rem', color: 'var(--fg)' }}>
              {guest.fullName || 'Guest'}
            </h2>
            {!many && (
              <span className={`room-status-badge ${bookingStatusClass(guest.status)}`} style={{ position: 'static' }}>
                {guest.status}
              </span>
            )}
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
          <dl className="bill-meta is-four">
            <div><dt>Full name</dt><dd>{guest.fullName || '—'}</dd></div>
            <div><dt>Contact no.</dt><dd>{guest.contactNo || '—'}</dd></div>
            <div><dt>Email</dt><dd style={{ overflowWrap: 'anywhere' }}>{guest.email || '—'}</dd></div>
            <div><dt>ID number</dt><dd>{guest.idNumber || '—'}</dd></div>
          </dl>

          {many && (
            <>
              <p className="bill-section-title">Bookings</p>
              <div className="vg-bk-list">
                {stays.map(({ room, reservation: r }, i) => (
                  <button key={r.bookingId} type="button" className="vg-bk" onClick={() => jumpTo(r.bookingId)}>
                    <span className="vg-bk-top"><b>{i + 1}.</b> Booking #{r.bookingId}</span>
                    <span className="vg-bk-name">{room.name}</span>
                    <span className="vg-bk-sub">{stayRange(r)} · {r.status}</span>
                  </button>
                ))}
              </div>
            </>
          )}

          {stays.map(({ room, reservation }, i) => (
            <section key={reservation.bookingId} id={'vg-bk-' + reservation.bookingId} className={many ? 'vg-bk-section' : undefined}>
              {many && (
                <div className="vg-bk-head">
                  <h3 className="font-display">{i + 1}. Booking #{reservation.bookingId} · {room.name}</h3>
                  <span className={`room-status-badge ${bookingStatusClass(reservation.status)}`} style={{ position: 'static' }}>
                    {reservation.status}
                  </span>
                </div>
              )}
              {renderActions && <div className="vg-bk-actions">{renderActions(room, reservation)}</div>}
              <StayDetails room={room} reservation={reservation} transactions={transactions} onVerifyDiscount={onVerifyDiscount} />
            </section>
          ))}

          {many && (
            <>
              <p className="bill-section-title">All {stays.length} bookings</p>
              <div className="bill-line is-total"><span>Total</span><span className="bill-amt">{formatPeso(sum('grandTotal'))}</span></div>
              <div className="bill-line is-subtotal"><span>Paid</span><span className="bill-amt">{formatPeso(sum('amountPaid'))}</span></div>
              <div className={`bill-line${sum('outstanding') > 0 ? ' is-balance' : ''}`}>
                <span>Balance</span><span className="bill-amt">{formatPeso(sum('outstanding'))}</span>
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}

/* One booking inside GuestDetailsModal: its room, dates, charges and payments. */
function StayDetails({ room, reservation, transactions, onVerifyDiscount }) {
  const childCharges = Number(reservation.childCharges) || 0;
  // totalDue is after the verified Senior Citizen / PWD discounts; the room line shows
  // the full price and the discount sits under it as its own line.
  const statutoryDiscount = Number(reservation.statutoryDiscount) || 0;
  const discounts = reservation.discounts || [];
  const refundDue = Number(reservation.refundDue) || 0;
  const roomTotal = (Number(reservation.totalDue) || 0) - childCharges + statutoryDiscount;
  const foodTotal = Number(reservation.roomServiceTotal) || 0;
  const foodCount = Number(reservation.roomServiceCount) || 0;
  const extras = Number(reservation.otherCharges) || 0;
  const addonsTotal = Number(reservation.addonsTotal) || 0;
  const addonsCount = Number(reservation.addonsCount) || 0;
  const grandTotal = Number(reservation.grandTotal) || (roomTotal + childCharges - statutoryDiscount + foodTotal + addonsTotal + extras);
  const paid = Number(reservation.amountPaid) || 0;
  const outstanding = reservation.outstanding != null
    ? Number(reservation.outstanding)
    : Math.max(0, grandTotal - paid);
  const payments = reservation.payments || [];

  return (
    <>
      <div className="bill-cols">
      <div>
      <p className="bill-section-title">Stay</p>
      <dl className="bill-meta">
        <div><dt>Booking no.</dt><dd>#{reservation.bookingId}</dd></div>
        <div><dt>Room</dt><dd>{room ? `${room.name} · ${room.label || room.category}` : '—'}</dd></div>
        <div><dt>Rate / 12 hrs</dt><dd>{formatPeso(reservation.roomRate || (room && room.price))}</dd></div>
        {reservation.occupancy && (
          <div><dt>Guests</dt><dd>{reservation.occupancy}{(reservation.children || []).length > 0 ? ' · ages ' + reservation.children.map(c => c.age).join(', ') : ''}</dd></div>
        )}
        {reservation.promoName && (
          <div><dt>Promo / package</dt><dd>{reservation.promoName} · {formatPeso(reservation.promoPrice)}</dd></div>
        )}
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

      {discounts.length > 0 && (
        <DiscountList discounts={discounts} open={['Booked', 'Arrived', 'Checked In', 'Pending Room Inspection'].includes(reservation.status)}
          onVerify={onVerifyDiscount ? (d) => onVerifyDiscount(reservation, d) : null} />
      )}

      {reservation.inspection && (
        <>
          <p className="bill-section-title">Room inspection</p>
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.82rem', lineHeight: 1.6 }}>
            {reservation.inspection.status !== 'Reported'
              ? 'Housekeeping is checking the room.'
              : (reservation.inspection.damages || []).length === 0
                ? `No damage found${reservation.inspection.inspectedBy ? ` · ${reservation.inspection.inspectedBy}` : ''}.`
                : (reservation.inspection.damages || []).map(d => `${d.item}: ${d.status === 'Charged' ? formatPeso(d.amount) : d.status === 'Waived' ? 'no charge' : 'to review'}`).join(' · ')}
          </p>
        </>
      )}
      </div>

      <div>
      <p className="bill-section-title">Charges</p>
      <div className="bill-line"><span>Room charge</span><span className="bill-amt">{formatPeso(roomTotal)}</span></div>
      {(reservation.children || []).length > 0 && (
        <div className="bill-line">
          <span>Additional child charges{childCharges ? '' : ' (children stay free)'}</span>
          <span className="bill-amt">{formatPeso(childCharges)}</span>
        </div>
      )}
      {statutoryDiscount > 0 && (
        <div className="bill-line">
          <span>Senior Citizen / PWD discount <small style={{ color: 'var(--fg-muted)' }}>(VAT exemption + 20%)</small></span>
          <span className="bill-amt">− {formatPeso(statutoryDiscount)}</span>
        </div>
      )}
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
      {refundDue > 0 && (
        <div className="bill-line is-balance">
          <span>Refund due to guest <small style={{ color: 'var(--fg-muted)' }}>(record it under Bill)</small></span>
          <span className="bill-amt">{formatPeso(refundDue)}</span>
        </div>
      )}

      <p className="bill-section-title">Payments</p>
      {payments.length === 0 ? (
        <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.82rem' }}>Nothing has been paid on this stay.</p>
      ) : (
        payments.map(p => {
          const shared = p.transactionRef && transactions[p.transactionRef];
          return (
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
              {shared && shared.rooms.length > 1 && (
                <p style={{ margin: '0.2rem 0 0', fontSize: '0.72rem', color: 'var(--accent)' }}>
                  Share of one {formatPeso(shared.total)} payment for {shared.rooms.join(', ')}
                </p>
              )}
            </div>
          );
        })
      )}
      </div>
      </div>
    </>
  );
}

/*
 * Front Desk's review of what Housekeeping found before check-out. Each item is
 * charged at the amount the desk types, or waived; nothing reaches the bill without
 * this. Reads the stay live, so an item reviewed on another screen updates here.
 */
function DamageReviewModal({ room, reservation, onReview, onClose }) {
  const [amounts, setAmounts] = useState({});
  const [busy, setBusy] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const inspection = reservation.inspection || {};
  const damages = inspection.damages || [];
  const left = damages.filter(d => d.status === 'Pending Review').length;

  const decide = (damage, decision) => {
    const amount = Number(amounts[damage.id]);
    if (decision === 'charge' && !(amount > 0)) { setError(`Enter what to charge for ${damage.item}.`); return; }
    setError('');
    setBusy(damage.id);
    Promise.resolve(onReview(damage.id, decision, decision === 'charge' ? amount : null))
      .catch(err => setError((err && err.message) || 'Could not save this review.'))
      .finally(() => setBusy(null));
  };

  return (
    <div className="bill-overlay" onClick={onClose} role="dialog" aria-modal="true" aria-label="Review damage">
      <div className="bill-modal" onClick={e => e.stopPropagation()}>
        <div className="bill-head">
          <div>
            <p style={{ margin: 0, color: 'var(--accent)', fontSize: '0.62rem', letterSpacing: '0.14em', textTransform: 'uppercase' }}>
              Room inspection
            </p>
            <h2 className="font-display" style={{ margin: '0.25rem 0 0.2rem', fontSize: '1.35rem', color: 'var(--fg)' }}>
              {room ? room.name : 'Room'}
            </h2>
            <p style={{ margin: 0, fontSize: '0.78rem', color: 'var(--fg-muted)' }}>
              {reservation.fullName || 'Guest'}{inspection.inspectedBy ? ` · checked by ${inspection.inspectedBy}` : ''}
            </p>
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
          {inspection.notes && (
            <p style={{ margin: '0 0 0.5rem', color: 'var(--fg-muted)', fontSize: '0.84rem', lineHeight: 1.6, whiteSpace: 'pre-wrap' }}>
              {inspection.notes}
            </p>
          )}

          {damages.map(d => (
            <div key={d.id} style={{ padding: '0.75rem 0', borderBottom: '1px solid var(--border)' }}>
              <div className="bill-line" style={{ padding: 0 }}>
                <span style={{ color: 'var(--fg)', fontWeight: 600 }}>{d.item}</span>
                <span className="bill-amt" style={{ fontSize: '0.76rem' }}>
                  {d.status === 'Charged' ? formatPeso(d.amount) : d.status === 'Waived' ? 'No charge' : 'To review'}
                </span>
              </div>
              {d.description && (
                <p style={{ margin: '0.25rem 0 0', color: 'var(--fg-muted)', fontSize: '0.8rem', lineHeight: 1.5 }}>{d.description}</p>
              )}
              {d.evidence && (
                <a href={d.evidence} target="_blank" rel="noopener noreferrer">
                  <img src={d.evidence} alt={`Photo of ${d.item}`} style={{ display: 'block', maxWidth: '100%', maxHeight: 180, borderRadius: 8, marginTop: '0.5rem' }} />
                </a>
              )}
              {d.status === 'Pending Review' ? (
                <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap', alignItems: 'center', marginTop: '0.6rem' }}>
                  <input
                    type="number"
                    min="1"
                    step="0.01"
                    className="booking-input"
                    placeholder="Charge (PHP)"
                    aria-label={`Charge for ${d.item}`}
                    style={{ width: '9rem' }}
                    value={amounts[d.id] || ''}
                    onChange={e => setAmounts(prev => ({ ...prev, [d.id]: e.target.value }))}
                  />
                  <button type="button" className="vg-btn vg-btn-main" disabled={busy === d.id} onClick={() => decide(d, 'charge')}>
                    <i className="fa-solid fa-receipt"></i> Add to bill
                  </button>
                  <button type="button" className="vg-btn" disabled={busy === d.id} onClick={() => decide(d, 'waive')}>
                    No charge
                  </button>
                </div>
              ) : (
                <p style={{ margin: '0.3rem 0 0', fontSize: '0.72rem', color: 'var(--fg-muted)' }}>
                  {d.status === 'Charged' ? 'Added to the bill' : 'Not charged'}{d.reviewedBy ? ` · ${d.reviewedBy}` : ''}
                </p>
              )}
            </div>
          ))}

          {error && <p role="alert" style={{ margin: '0.75rem 0 0', color: 'var(--danger, #fb7185)', fontSize: '0.82rem' }}>{error}</p>}
          <p style={{ margin: '0.9rem 0 0', color: 'var(--fg-muted)', fontSize: '0.8rem' }}>
            {left
              ? `${left} item${left === 1 ? '' : 's'} still to review. The final bill opens when every item is done.`
              : 'Every item is reviewed. Close this and press Check out to finish the final bill.'}
          </p>
        </div>
      </div>
    </div>
  );
}

function fdThemeColor(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

function VerifyGuestPage({ rooms, addonCatalog, onBack, onBookingAction, onToast, onFetchBill, onAddCharge, onRemoveCharge, onAddAddon, onPay, onSettleAndCheckOut, onExtendStay, onChangeRoom, onRequestInspection, onReviewDamage, onVerifyDiscount }) {
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

  // The Details action: the key of the guest whose bookings it shows. It needs
  // no fetch: the rooms already hold the whole reservation payload the modal renders.
  const [detailsFor, setDetailsFor] = useState(null);

  const [extendFor, setExtendFor] = useState(null);
  const [changeFor, setChangeFor] = useState(null);
  // Review damage: the booking id, read live from the rooms like Details.
  const [reviewFor, setReviewFor] = useState(null);

  /* Check-out starts with Housekeeping, not the bill: they inspect the room so any
     damage can still be charged before the guest leaves. */
  const startCheckOut = (room, reservation) => {
    const go = () => Promise.resolve(onRequestInspection(reservation.bookingId))
      .then(() => { if (onToast) onToast(`Housekeeping has been asked to inspect ${room.name}.`); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not start the check-out.'); });
    if (!window.Swal) { if (window.confirm('Request Room Inspection?')) go(); return; }
    window.Swal.fire({
      title: 'Request Room Inspection?',
      text: `Housekeeping will inspect ${room.name} for any damages or missing items before the final bill and checkout are completed.`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Request Inspection',
      cancelButtonText: 'Cancel',
      background: fdThemeColor('--card', '#181714'),
      color: fdThemeColor('--fg', '#f5f0e8'),
      confirmButtonColor: fdThemeColor('--accent', '#c9a84c'),
      cancelButtonColor: '#71717a',
      heightAuto: false,
    }).then(r => { if (r.isConfirmed) go(); });
  };
  const changeRoom = (newRoom) => {
    const { room, reservation } = changeFor;
    return Promise.resolve(onChangeRoom(reservation.bookingId, newRoom.dbId)).then(() => {
      setChangeFor(null);
      if (onToast) onToast(`${reservation.fullName || 'Guest'} moved from ${room.name} to ${newRoom.name}.`);
    });
  };
  const verifyDiscount = (reservation, discount) => (
    Promise.resolve(onVerifyDiscount(reservation.bookingId, discount.id))
      .then(data => {
        const refund = data && data.booking ? Number(data.booking.refundDue) || 0 : 0;
        if (onToast) onToast(`${discount.fullName}'s ${discount.type} discount is applied.`
          + (refund > 0 ? ` The guest has overpaid: refund ${formatPeso(refund)} from the Bill.` : ''));
      })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not verify that ID.'); })
  );
  const extendStay = (checkOut) => {
    const { room, reservation } = extendFor;
    return Promise.resolve(onExtendStay(reservation.bookingId, checkOut)).then(() => {
      setExtendFor(null);
      if (onToast) onToast(`${reservation.fullName || 'Guest'} now checks out of ${room.name} on ${formatBillDate(checkOut)}.`);
    });
  };

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
    const cash = payment.method === 'Cash' && payment.amount > 0
      ? { tendered: payment.tendered, change: payment.change, paidNow: payment.amount }
      : null;
    return Promise.resolve(onSettleAndCheckOut(bookingId, payment)).then(() => {
      if (onToast) {
        onToast(`${billFor.reservation.fullName || 'Guest'} checked out of ${billFor.room.name}.`);
      }
      closeBill();
      return Promise.resolve(onFetchBill(bookingId))
        .then(finalBill => setReceipt({ bill: finalBill || lastBill, cash }))
        .catch(() => { if (lastBill) setReceipt({ bill: lastBill, cash }); });
    });
  };

  // Every open booking, a room's several stays included (today's guest and later ones).
  const allReservations = (rooms || []).reduce((acc, room) => {
    if (!room) return acc;
    const list = Array.isArray(room.reservations) ? room.reservations : (room.reservation ? [room.reservation] : []);
    list.forEach(reservation => acc.push({ room, reservation }));
    return acc;
  }, []);

  // One row per guest, however many bookings they hold, so the table never repeats a
  // person. Their bookings stay separate records, earliest first, and Details shows
  // each one on its own with its own buttons.
  const guests = (() => {
    const groups = [];
    const index = {};
    allReservations.forEach(stay => {
      const r = stay.reservation;
      const key = r.guestId != null ? 'g' + r.guestId : 'b' + r.bookingId;
      if (!index[key]) groups.push(index[key] = { key, stays: [] });
      index[key].stays.push(stay);
    });
    groups.forEach(g => g.stays.sort((a, b) =>
      String(a.reservation.checkIn || '').localeCompare(String(b.reservation.checkIn || ''))
      || a.reservation.bookingId - b.reservation.bookingId));
    return groups;
  })();

  const q = search.trim().toLowerCase();
  const stayMatches = ({ room, reservation }) => !q ||
    (reservation.fullName || '').toLowerCase().includes(q) ||
    (room.name || '').toLowerCase().includes(q) ||
    String(reservation.bookingId).includes(q.replace(/^#/, '')) ||
    (reservation.email || '').toLowerCase().includes(q) ||
    (reservation.contactNo || '').toLowerCase().includes(q) ||
    (reservation.idNumber || '').toLowerCase().includes(q);
  const hasStatus = (g, key) => key === 'All' || g.stays.some(s => s.reservation.status === key);
  const filtered = guests.filter(g => hasStatus(g, statusFilter) && g.stays.some(stayMatches));

  const totalPages = Math.max(1, Math.ceil(filtered.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageGuests = filtered.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  // Details and Review read live from the rooms, so a booking moved on another screen
  // shows in the open window on the next poll.
  const reviewStay = reviewFor ? allReservations.find(({ reservation }) => reservation.bookingId === reviewFor) : null;
  const detailsStays = detailsFor ? (guests.find(g => g.key === detailsFor) || {}).stays : null;

  const statusTabs = [['All', 'All'], ['Booked', 'Booked'], ['Arrived', 'Arrived'], ['Checked In', 'Checked in'], ['Pending Room Inspection', 'Checking out']];
  const countFor = (key) => guests.filter(g => hasStatus(g, key)).length;

  // One booking's next-step buttons: on the row when the guest has a single booking,
  // and on each booking's own section in Details when they have several.
  const renderActions = (room, reservation) => {
    const status = reservation.status;
    return (
      <>
        {/* Extend sits first so the lifecycle and Details buttons line up
            in the same columns on every row; other rows keep the slot empty. */}
        {status === 'Checked In' ? (
          <button type="button" className="vg-btn vg-btn-line"
            onClick={() => setExtendFor({ room, reservation })}
            title="The guest wants to stay more nights">
            <i className="fa-solid fa-calendar-plus"></i> Extend stay
          </button>
        ) : status === 'Pending Room Inspection' ? (
          checkoutStage(reservation) === 'waiting' ? (
            <button type="button" className="vg-btn vg-btn-line" disabled
              title="Housekeeping is inspecting the room. Their report shows up here.">
              <i className="fa-solid fa-hourglass-half"></i> Inspecting
            </button>
          ) : checkoutStage(reservation) === 'review' ? (
            <button type="button" className="vg-btn vg-btn-main"
              onClick={() => setReviewFor(reservation.bookingId)}
              title="Charge or waive each item Housekeeping found">
              <i className="fa-solid fa-triangle-exclamation"></i> Review damage
            </button>
          ) : (
            <button type="button" className="vg-btn vg-btn-main"
              onClick={() => openBill(room, reservation)}
              title="Show the final bill and check the guest out">
              <i className="fa-solid fa-right-from-bracket"></i> Check out
            </button>
          )
        ) : (
          <button type="button" className="vg-btn vg-btn-line"
            onClick={() => setChangeFor({ room, reservation })}
            title="Move the guest to another room, an upgrade say">
            <i className="fa-solid fa-right-left"></i> Change room
          </button>
        )}
        {status === 'Booked' && (
          <button type="button" className="vg-btn vg-lifecycle-btn vg-btn-line"
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
          <button type="button" className="vg-btn vg-lifecycle-btn vg-btn-main"
            onClick={() => startCheckOut(room, reservation)}
            title="Housekeeping inspects the room, then you finish the final bill">
            <i className="fa-solid fa-right-from-bracket"></i> Check out
          </button>
        )}
        {status !== 'Checked In' && (
          <button type="button" className="vg-btn"
            onClick={() => openBill(room, reservation)}
            title="Charges, extras and payments so far: a deposit, an upgrade difference">
            <i className="fa-solid fa-receipt"></i> Bill
          </button>
        )}
      </>
    );
  };
  const clearFilters = () => { setSearch(''); setStatusFilter('All'); setPage(1); };

  return (
    <div className="vg" data-hms-no-edit="1">
      <header className="vg-head">
        <div>
          <h1 className="font-display">Guest Folio</h1>
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
                    <th style={{ textAlign: 'center' }}>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {pageGuests.map(({ key, stays }) => {
                    const first = stays[0];
                    const guest = first.reservation;
                    const many = stays.length > 1;
                    // The stay with a running clock leads; otherwise the earliest booking.
                    const clocks = stays.map(s => remainingStay(s.reservation, now));
                    const remaining = clocks.find(c => c.tone !== 'idle') || clocks[0];
                    const lastOut = stays.reduce((m, s) => (String(s.reservation.checkOut || '') > m ? String(s.reservation.checkOut) : m), '');
                    const roomNames = [...new Set(stays.map(s => s.room.name))];
                    const statusCounts = stays.reduce((acc, s) => { acc[s.reservation.status] = (acc[s.reservation.status] || 0) + 1; return acc; }, {});
                    return (
                      <tr key={key}>
                        <td className="vg-wide vg-guest">
                          <span className="vg-main">{guest.fullName || '—'}</span>
                          {guest.contactNo && <span className="vg-sub">{guest.contactNo}</span>}
                          {many && <span className="vg-sub vg-bk-tag">{stays.length} bookings</span>}
                        </td>
                        <td data-label={many ? 'Rooms' : 'Room'}>
                          <span className="vg-main">{roomNames.join(', ')}</span>
                          {many ? (
                            <span className="vg-sub">{stays.map(s => '#' + s.reservation.bookingId).join(', ')}</span>
                          ) : (
                            <span className="vg-sub">Booking #{guest.bookingId} · {formatPeso(guest.roomRate || first.room.price)} / 12 hrs</span>
                          )}
                        </td>
                        <td data-label="Stay">
                          <span className="vg-num" style={{ color: 'var(--fg)' }}>In {formatShortDateTime(guest.checkIn, guest.checkInTime)}</span>
                          <span className="vg-sub vg-num">Out {formatShortDate(lastOut) || '—'}</span>
                        </td>
                        <td data-label="Time left" className="vg-num" style={{
                          color: STAY_TONE_COLORS[remaining.tone],
                          fontWeight: remaining.tone === 'idle' ? 400 : 600,
                          opacity: remaining.tone === 'idle' ? 0.7 : 1,
                        }}>
                          {remaining.text}
                        </td>
                        <td data-label="Status">
                          <span className="vg-status-list">
                            {Object.keys(statusCounts).map(s => (
                              <span key={s} className={`room-status-badge vg-status ${bookingStatusClass(s)}`}>
                                {many && statusCounts[s] > 1 ? `${statusCounts[s]} ${s}` : s}
                              </span>
                            ))}
                          </span>
                          {!many && statusHint(guest) && <span className="vg-sub">{statusHint(guest)}</span>}
                        </td>
                        <td className="vg-wide">
                          <div className={'vg-actions' + (many ? ' is-one' : '')}>
                            {many ? (
                              <button type="button" className="vg-btn vg-btn-line"
                                onClick={() => setDetailsFor(key)}
                                title="Open each booking on its own, with its own buttons">
                                <i className="fa-solid fa-layer-group"></i> View {stays.length} bookings
                              </button>
                            ) : (
                              <>
                                {renderActions(first.room, guest)}
                                <button type="button" className="vg-btn"
                                  onClick={() => setDetailsFor(key)}
                                  title="See everything recorded for this booking">
                                  <i className="fa-solid fa-eye"></i> Details
                                </button>
                              </>
                            )}
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

      {/* Details sits under the bill, change-room and other windows its buttons open. */}
      {detailsStays && (
        <GuestDetailsModal
          stays={detailsStays}
          renderActions={renderActions}
          onVerifyDiscount={verifyDiscount}
          onClose={() => setDetailsFor(null)}
        />
      )}

      <FinalBillModal
        open={!!billFor}
        bill={bill}
        loading={billLoading}
        error={billError}
        onClose={closeBill}
        onAddCharge={(description, amount) => refreshBill(onAddCharge(billFor.reservation.bookingId, description, amount))}
        onRemoveCharge={(chargeId) => refreshBill(onRemoveCharge(billFor.reservation.bookingId, chargeId))}
        canCheckOut={!!billFor && checkoutStage(billFor.reservation) === 'ready'}
        addonCatalog={addonCatalog}
        onAddAddon={(addonId, qty) => refreshBill(onAddAddon(billFor.reservation.bookingId, addonId, qty))}
        onPay={(payment) => refreshBill(onPay(billFor.reservation.bookingId, payment))}
        onSettle={settleAndCheckOut}
        onToast={onToast}
        onGroupPaid={() => refreshBill(onFetchBill(billFor.reservation.bookingId))}
      />

      <ReceiptModal receipt={receipt} onClose={() => setReceipt(null)} />

      {reviewStay && (
        <DamageReviewModal
          room={reviewStay.room}
          reservation={reviewStay.reservation}
          onReview={onReviewDamage}
          onClose={() => setReviewFor(null)}
        />
      )}

      {changeFor && (
        <ChangeRoomModal
          room={changeFor.room}
          reservation={changeFor.reservation}
          rooms={rooms}
          onClose={() => setChangeFor(null)}
          onSave={changeRoom}
        />
      )}

      {extendFor && (
        <ExtendStayModal
          room={extendFor.room}
          reservation={extendFor.reservation}
          onClose={() => setExtendFor(null)}
          onSave={extendStay}
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

  // Housekeeping's catalogue, for Add extras. Re-read after each add: stock moves.
  const [addonCatalog, setAddonCatalog] = useState([]);
  const fetchAddons = useCallback(() => {
    fetch('/students/hotel/addons', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => { if (Array.isArray(data.items)) setAddonCatalog(data.items); })
      .catch(() => {});
  }, []);
  useEffect(() => { fetchAddons(); }, [fetchAddons]);

  const addAddon = useCallback((bookingId, addonId, qty) => (
    billRequest('/students/hotel/bookings/' + bookingId + '/addons', 'POST', { addons: [{ addon_id: addonId, qty }] })
      .then(d => { fetchAddons(); return d.bill; })
  ), [billRequest, fetchAddons]);

  // A pending Senior Citizen / PWD discount whose ID the desk has now seen.
  const verifyDiscount = useCallback((bookingId, discountId) => (
    billRequest('/students/hotel/bookings/' + bookingId + '/discounts/' + discountId, 'PATCH', { verified: true })
      .then(data => { putRoom(data); fetchRooms(); return data; })
  ), [billRequest, fetchRooms]);

  // Money taken during the stay, without checking out. A refund is money handed back.
  const pay = useCallback((bookingId, payment) => (
    billRequest('/students/hotel/bookings/' + bookingId + '/payments', 'POST', {
      type: payment.type || (payment.full ? 'Full' : 'Partial'),
      amount_paid: payment.amount,
      method: payment.method,
      reference: payment.reference,
    }).then(d => { fetchRooms(); return d.bill; })
  ), [billRequest, fetchRooms]);

  // Both rooms change: the guest leaves one and lands in the other, so read them all again.
  const changeRoom = useCallback((bookingId, roomId) => (
    billRequest('/students/hotel/bookings/' + bookingId, 'PATCH', { room_id: roomId })
      .then(data => { fetchRooms(); return data; })
  ), [billRequest, fetchRooms]);

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

  const putRoom = (data) => {
    if (data && data.room) setRooms(prev => prev.map(r => (r.id === data.room.id ? data.room : r)));
    return data;
  };

  // Starts a check-out: the stay goes Pending Room Inspection and Housekeeping is told.
  const requestInspection = useCallback((bookingId) => (
    billRequest('/students/hotel/bookings/' + bookingId, 'PATCH', { action: 'request_inspection' }).then(putRoom)
  ), [billRequest]);

  // Front Desk charges or waives one item Housekeeping reported.
  const reviewDamage = useCallback((damageId, decision, amount) => (
    billRequest('/students/hotel/inspection-damages/' + damageId, 'PATCH', { decision, amount }).then(putRoom)
  ), [billRequest]);

  // A later check-out on the same booking; the server refuses nights already
  // promised to another guest, and the modal shows that message.
  const extendStay = useCallback((bookingId, checkOut) => (
    billRequest('/students/hotel/bookings/' + bookingId, 'PATCH', { check_out: checkOut })
      .then(data => {
        if (data && data.room) setRooms(prev => prev.map(r => (r.id === data.room.id ? data.room : r)));
        return data;
      })
  ), [billRequest]);

  return (
    <VerifyGuestPage
      rooms={rooms}
      onBack={() => { window.location.href = window.HMS_FRONTDESK_URL; }}
      onBookingAction={bookingAction}
      onFetchBill={fetchBill}
      onAddCharge={addCharge}
      onRemoveCharge={removeCharge}
      addonCatalog={addonCatalog}
      onAddAddon={addAddon}
      onPay={pay}
      onSettleAndCheckOut={settleAndCheckOut}
      onExtendStay={extendStay}
      onChangeRoom={changeRoom}
      onRequestInspection={requestInspection}
      onReviewDamage={reviewDamage}
      onVerifyDiscount={verifyDiscount}
      onToast={(msg) => window.toast && window.toast(msg)}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
