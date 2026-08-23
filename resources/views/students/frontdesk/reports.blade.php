@extends('students.builder.ops-shell')

@php $backRoute = 'students.frontdesk'; @endphp

@section('page-title', 'Reports')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }
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
  .rp-tile {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 0.9rem 1.05rem;
  }
  .rp-tile-label {
    display: block; font-size: 0.6rem; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--fg-muted); margin-bottom: 0.35rem;
  }
  .rp-tile-value {
    font-family: var(--font-display, 'Playfair Display', serif); font-size: 1.5rem; font-weight: 700; color: var(--fg);
  }
  .rp-tile-sub { display: block; font-size: 0.66rem; color: var(--fg-muted); margin-top: 0.25rem; }
  .rp-tile-grand {
    border-color: var(--accent);
    background: linear-gradient(135deg, rgba(201,168,76,0.1), var(--card) 60%);
  }
  .rp-table { width: 100%; border-collapse: collapse; font-family: var(--font-body, 'Outfit', sans-serif); }
  .rp-table th {
    padding: 0.6rem 0.85rem; font-size: 0.62rem; font-weight: 700;
    letter-spacing: 0.1em; text-transform: uppercase; color: var(--fg-muted);
    border-bottom: 1px solid var(--border); white-space: nowrap;
    text-align: left; background: rgba(255,255,255,0.02);
  }
  .rp-table td {
    padding: 0.7rem 0.85rem; font-size: 0.8rem; color: var(--fg-muted);
    border-bottom: 1px solid rgba(42,38,33,0.5); vertical-align: middle; white-space: nowrap;
  }
  .rp-table tfoot td {
    border-top: 1px solid var(--border); border-bottom: none;
    color: var(--fg); font-weight: 600; background: rgba(255,255,255,0.02);
  }
  .rp-strong { color: var(--fg); font-weight: 600; }
  .rp-money { color: var(--accent-light); font-family: var(--font-display, 'Playfair Display', serif); font-weight: 700; }
  .rp-muted-money { color: var(--fg-muted); font-family: var(--font-display, 'Playfair Display', serif); font-weight: 600; }
  .rp-zero { color: var(--fg-muted); opacity: 0.8; }
  .rp-num { text-align: right; font-variant-numeric: tabular-nums; }
  .rp-note { font-size: 0.72rem; color: var(--fg-muted); font-style: italic; margin: 0.6rem 0 0; }

  .rp-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.1rem; }
  .rp-tab {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.55rem 1.1rem; border-radius: 999px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600; font-size: 0.68rem;
    letter-spacing: 0.06em; text-transform: uppercase; cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
  }
  .rp-tab:hover { color: var(--fg); }
  .rp-tab.is-active { border-color: var(--accent); background: var(--accent); color: var(--bg); }
  .rp-tab-count { font-size: 0.62rem; opacity: 0.75; margin-left: 0.15rem; }

  .rp-presets { display: flex; gap: 0.4rem; flex-wrap: wrap; }
  .rp-preset {
    padding: 0.45rem 0.85rem; border-radius: 999px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600; font-size: 0.62rem;
    letter-spacing: 0.05em; text-transform: uppercase; cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s; white-space: nowrap;
  }
  .rp-preset:hover { color: var(--fg); }
  .rp-preset.is-active {
    border-color: var(--accent); background: rgba(201,168,76,0.18); color: var(--accent-light);
  }

  .rp-badge {
    display: inline-block; padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.62rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase;
    border: 1px solid transparent;
  }
  .rp-cat-room { background: rgba(201,168,76,0.18); color: var(--accent-light, #e2cc7a); border-color: rgba(201,168,76,0.35); }
  .rp-cat-dinein { background: rgba(56,189,248,0.18); color: #38bdf8; border-color: rgba(56,189,248,0.35); }
  .rp-cat-service { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Additive only — nothing above this block is touched, so a Template 1
     team (or one that hasn't chosen a template yet) renders unchanged. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d;
  }
  /* rgba() can't read a hex custom property's channels, so the accent-tinted
     fills below (originally rgba(201,168,76,X) — Template 1's gold accent as
     RGB) get an explicit forest-green (27,67,50) companion. */
  :root[data-ops-theme="2"] .rp-tile-grand { background: linear-gradient(135deg, rgba(27,67,50,0.08), var(--card) 60%); }
  :root[data-ops-theme="2"] .rp-preset.is-active { background: rgba(27,67,50,0.1); }
  :root[data-ops-theme="2"] .rp-cat-room { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
  :root[data-ops-theme="2"] .rp-cat-dinein { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
  :root[data-ops-theme="2"] .rp-cat-service { background: #f3e8ff; color: #7e22ce; border-color: #e9d5ff; }
  :root[data-ops-theme="2"] .rp-table th,
  :root[data-ops-theme="2"] .rp-table tfoot td,
  :root[data-ops-theme="2"] .booking-input { background: rgba(27,67,50,0.04); }
  :root[data-ops-theme="2"] .rp-table td { border-bottom-color: var(--border); }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_REPORTS = {
    backUrl: @json(route($backRoute)),
    bookingsUrl: @json(route('students.hotel.bookings.index')),
    ordersUrl: @json(route('students.hotel.orders.index')),
    tablesUrl: @json(route('students.hotel.tables.index')),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo } = React;

const CFG = window.HMS_REPORTS;
const BLOCK_HOURS = 12;
const PER_PAGE = 5;

function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0';
  return '₱' + n.toLocaleString(undefined, { maximumFractionDigits: 2 });
}

function formatWhen(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '—';
  return d.toLocaleString([], { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function formatDate(value) {
  const raw = String(value || '').trim();
  if (!raw) return '—';
  const [y, m, d] = raw.split('-').map(Number);
  if (!y || !m || !d) return raw;
  return new Date(y, m - 1, d).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatRange(from, to) {
  if (!from && !to) return 'all time';
  if (from && to && from === to) return formatDate(from);
  return `${from ? formatDate(from) : 'the start'} – ${to ? formatDate(to) : 'now'}`;
}

/* Charged 12-hour blocks, the same maths HotelBooking::stayBlocks() uses. Only a
   fallback for a payload that predates the server sending totals. */
function stayBlocks(checkIn, checkOut, checkInTime) {
  if (!checkIn || !checkOut) return 1;
  const clock = /^\d{1,2}:\d{2}/.test(String(checkInTime || '')) ? checkInTime : '00:00';
  const start = new Date(`${checkIn}T${clock}`);
  const end = new Date(`${checkOut}T${clock}`);
  const hours = (end - start) / 3600000;
  if (!Number.isFinite(hours) || hours <= 0) return 1;
  return Math.max(1, Math.ceil(hours / BLOCK_HOURS));
}

/* A local YYYY-MM-DD. Never toISOString(): that renders the UTC day, so in Manila
   anything before 08:00 comes back as yesterday and "Today" quietly loses the whole
   morning. Built from the local getters, which is what the staff's wall clock, the
   date inputs on this page, and formatWhen() all already agree on. */
function ymd(date) {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

/* Ranges run up to today, never past it: a week that has not happened yet would pad
   the range with empty days and make "This Week" and "Today" disagree about their
   upper bound for no reason.

   The week is Monday-to-today. A hotel week is read against a work week, and a Sunday
   start would split the weekend across two reports. getDay() returns 0 for Sunday,
   which is day 7 of the week that just ended, not day 1 of the next one. */
function presetRange(preset, now = new Date()) {
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  if (preset === 'today') {
    return { from: ymd(today), to: ymd(today) };
  }
  if (preset === 'week') {
    const dow = today.getDay() === 0 ? 7 : today.getDay();
    const monday = new Date(today.getFullYear(), today.getMonth(), today.getDate() - (dow - 1));
    return { from: ymd(monday), to: ymd(today) };
  }
  if (preset === 'month') {
    const first = new Date(today.getFullYear(), today.getMonth(), 1);
    return { from: ymd(first), to: ymd(today) };
  }
  return null; // 'custom' keeps whatever the two date inputs already hold
}

/* The one date rule for every row on this page. A timestamp is bucketed by the day it
   reads as on a local clock — the same day formatWhen() prints beside it — and a bare
   YYYY-MM-DD (checkOut carries no time) already is that day, so it passes straight
   through rather than going through Date and getting shifted. */
function localDayKey(value) {
  const raw = String(value || '').trim();
  if (!raw) return '';
  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;
  const d = new Date(raw);
  return Number.isNaN(d.getTime()) ? '' : ymd(d);
}

/* Both bounds inclusive; a row with no usable date is out of every window. */
function inWindow(day, from, to) {
  if (!day) return false;
  if (from && day < from) return false;
  if (to && day > to) return false;
  return true;
}

function itemsSummary(items) {
  const list = Array.isArray(items) ? items : [];
  const count = list.reduce((n, it) => n + (Number(it.qty) || 0), 0);
  const title = list.map(it => `${it.qty}× ${it.name}`).join(', ');
  return { count, title };
}

function Tile({ label, value, sub, tone, grand }) {
  return (
    <div className={'rp-tile' + (grand ? ' rp-tile-grand' : '')}>
      <span className="rp-tile-label">{label}</span>
      <span className="rp-tile-value" style={tone ? { color: tone } : undefined}>{value}</span>
      {sub ? <span className="rp-tile-sub">{sub}</span> : null}
    </div>
  );
}

function EmptyState({ icon, message }) {
  return (
    <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem', textAlign: 'center' }}>
      <i className={'fa-solid ' + icon} style={{ fontSize: '1.7rem', color: 'var(--fg-muted)', opacity: 0.3, display: 'block', marginBottom: '0.7rem' }}></i>
      <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.86rem' }}>{message}</p>
    </div>
  );
}

function Pager({ page, totalPages, total, perPage, onPage }) {
  if (totalPages <= 1) return null;
  return (
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: '0.85rem', gap: '0.5rem', flexWrap: 'wrap' }}>
      <span style={{ fontSize: '0.75rem', color: 'var(--fg-muted)' }}>
        Showing {(page - 1) * perPage + 1}–{Math.min(page * perPage, total)} of {total}
      </span>
      <div style={{ display: 'flex', gap: '0.35rem', flexWrap: 'wrap' }}>
        <button
          type="button"
          onClick={() => onPage(Math.max(1, page - 1))}
          disabled={page === 1}
          style={{ padding: '0.35rem 0.7rem', borderRadius: 6, border: '1px solid var(--border)', background: 'transparent', color: page === 1 ? 'var(--fg-muted)' : 'var(--fg)', cursor: page === 1 ? 'default' : 'pointer', fontSize: '0.78rem', opacity: page === 1 ? 0.4 : 1 }}
        >
          <i className="fa-solid fa-chevron-left" style={{ fontSize: '0.65rem' }}></i>
        </button>
        {Array.from({ length: totalPages }, (_, i) => i + 1).map(n => (
          <button
            key={n}
            type="button"
            onClick={() => onPage(n)}
            style={{ padding: '0.35rem 0.65rem', borderRadius: 6, border: '1px solid ' + (n === page ? 'var(--accent)' : 'var(--border)'), background: n === page ? 'var(--accent)' : 'transparent', color: n === page ? 'var(--bg)' : 'var(--fg-muted)', cursor: 'pointer', fontSize: '0.78rem', fontWeight: n === page ? 700 : 400 }}
          >
            {n}
          </button>
        ))}
        <button
          type="button"
          onClick={() => onPage(Math.min(totalPages, page + 1))}
          disabled={page === totalPages}
          style={{ padding: '0.35rem 0.7rem', borderRadius: 6, border: '1px solid var(--border)', background: 'transparent', color: page === totalPages ? 'var(--fg-muted)' : 'var(--fg)', cursor: page === totalPages ? 'default' : 'pointer', fontSize: '0.78rem', opacity: page === totalPages ? 0.4 : 1 }}
        >
          <i className="fa-solid fa-chevron-right" style={{ fontSize: '0.65rem' }}></i>
        </button>
      </div>
    </div>
  );
}

function TabBar({ tab, onTab, counts }) {
  const tabs = [
    { key: 'overview', label: 'Overview' },
    { key: 'room', label: 'Room', count: counts.room },
    { key: 'dinein', label: 'Dine-In', count: counts.dinein },
    { key: 'roomsvc', label: 'Room Service', count: counts.roomsvc },
  ];
  return (
    <div className="rp-tabs">
      {tabs.map(t => (
        <button
          key={t.key}
          type="button"
          className={'rp-tab' + (tab === t.key ? ' is-active' : '')}
          onClick={() => onTab(t.key)}
        >
          {t.label}
          {typeof t.count === 'number' && <span className="rp-tab-count">({t.count})</span>}
        </button>
      ))}
    </div>
  );
}

function PresetBar({ preset, from, to, onPreset, onFrom, onTo }) {
  const presets = [
    { key: 'today', label: 'Today' },
    { key: 'week', label: 'This Week' },
    { key: 'month', label: 'This Month' },
    { key: 'custom', label: 'Custom' },
  ];
  return (
    <div style={{ display: 'flex', gap: '0.7rem', flexWrap: 'wrap', alignItems: 'flex-end' }}>
      <div>
        <label className="rp-tile-label">Period</label>
        <div className="rp-presets">
          {presets.map(p => (
            <button
              key={p.key}
              type="button"
              className={'rp-preset' + (preset === p.key ? ' is-active' : '')}
              onClick={() => onPreset(p.key)}
            >
              {p.label}
            </button>
          ))}
        </div>
      </div>
      <div style={{ minWidth: 150 }}>
        <label className="rp-tile-label">From</label>
        <input type="date" className="booking-input" value={from} onChange={e => onFrom(e.target.value)} style={{ colorScheme: 'dark' }} />
      </div>
      <div style={{ minWidth: 150 }}>
        <label className="rp-tile-label">To</label>
        <input type="date" className="booking-input" value={to} onChange={e => onTo(e.target.value)} style={{ colorScheme: 'dark' }} />
      </div>
    </div>
  );
}

function OverviewPanel({ totals, counts, rangeLabel }) {
  const share = (n) => totals.grand > 0 ? Math.round((n / totals.grand) * 100) + '%' : '—';
  const categories = [
    { key: 'room', label: 'Room Revenue', cls: 'rp-cat-room', value: totals.room, count: counts.room },
    { key: 'dinein', label: 'Dine-In Revenue', cls: 'rp-cat-dinein', value: totals.dineIn, count: counts.dinein },
    { key: 'roomsvc', label: 'Room Service Revenue', cls: 'rp-cat-service', value: totals.roomService, count: counts.roomsvc },
  ];

  return (
    <>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(170px, 1fr))', gap: '0.75rem', marginBottom: '0.9rem' }}>
        <Tile label="Room Revenue" value={formatPeso(totals.room)} sub={share(totals.room) + ' of total'} />
        <Tile label="Dine-In Revenue" value={formatPeso(totals.dineIn)} sub={share(totals.dineIn) + ' of total'} />
        <Tile label="Room Service Revenue" value={formatPeso(totals.roomService)} sub={share(totals.roomService) + ' of total'} />
        <Tile label="Total Revenue" value={formatPeso(totals.grand)} grand={true} sub={rangeLabel} />
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(160px, 1fr))', gap: '0.75rem', marginBottom: '1.35rem' }}>
        <Tile label="Stays Completed" value={counts.room} />
        <Tile label="Dine-In Orders" value={counts.dinein} />
        <Tile label="Room Service Orders" value={counts.roomsvc} />
        <Tile label="Room Bills Collected" value={formatPeso(totals.collected)} tone="var(--accent-light)" />
        {totals.outstanding > 0 && <Tile label="Room Bills Outstanding" value={formatPeso(totals.outstanding)} tone="var(--danger, #fb7185)" />}
      </div>

      <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
        <table className="rp-table">
          <thead>
            <tr>
              <th>Category</th>
              <th className="rp-num">Count</th>
              <th className="rp-num">Revenue</th>
              <th className="rp-num">Share</th>
            </tr>
          </thead>
          <tbody>
            {categories.map(c => (
              <tr key={c.key}>
                <td><span className={'rp-badge ' + c.cls}>{c.label}</span></td>
                <td className="rp-num">{c.count}</td>
                <td className="rp-num rp-money">{formatPeso(c.value)}</td>
                <td className="rp-num">{share(c.value)}</td>
              </tr>
            ))}
          </tbody>
          <tfoot>
            <tr>
              <td>Total Revenue</td>
              <td className="rp-num">{counts.room + counts.dinein + counts.roomsvc}</td>
              <td className="rp-num rp-money">{formatPeso(totals.grand)}</td>
              <td className="rp-num">100%</td>
            </tr>
          </tfoot>
        </table>
      </div>
      {totals.svcOnOpenStay > 0 && (
        <p className="rp-note">{formatPeso(totals.svcOnOpenStay)} of Room Service revenue is on stays that have not checked out yet.</p>
      )}
    </>
  );
}

function RoomTable({ rows, totals, page, onPage, rangeLabel, noCloseCount }) {
  const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageRows = rows.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  if (rows.length === 0) {
    return <EmptyState icon="fa-bed" message={`No stays were checked out between ${rangeLabel}.`} />;
  }

  return (
    <>
      <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
        <table className="rp-table">
          <thead>
            <tr>
              <th>Guest</th>
              <th>Room</th>
              <th>Check-In</th>
              <th>Check-Out</th>
              <th className="rp-num">Blocks</th>
              <th className="rp-num">Room Charge</th>
              <th className="rp-num">Add-ons</th>
              <th className="rp-num">Other</th>
              <th className="rp-num">Room Revenue</th>
              <th className="rp-num" title="Billed to the stay, counted under Room Service — not included in Room Revenue.">Room Svc (excl.)</th>
              <th className="rp-num">Paid</th>
              <th>Closed</th>
            </tr>
          </thead>
          <tbody>
            {pageRows.map(b => {
              const roomCharge = Number(b.totalDue) || 0;
              const addons = Number(b.addonsTotal) || 0;
              const extras = Number(b.otherCharges) || 0;
              const service = Number(b.roomServiceTotal) || 0;
              const paid = Number(b.amountPaid) || 0;
              return (
                <tr key={b.bookingId}>
                  <td className="rp-strong">{b.fullName || '—'}</td>
                  <td>{b.roomName || '—'}</td>
                  <td>{formatDate(b.checkIn)}{b.checkInTime ? ` · ${b.checkInTime}` : ''}</td>
                  <td>{formatDate(b.checkOut)}</td>
                  <td className="rp-num">{stayBlocks(b.checkIn, b.checkOut, b.checkInTime)}</td>
                  <td className="rp-num">{formatPeso(roomCharge)}</td>
                  <td className="rp-num">{addons > 0 ? formatPeso(addons) : '—'}</td>
                  <td className="rp-num">{extras > 0 ? formatPeso(extras) : '—'}</td>
                  <td className="rp-num rp-money">{formatPeso(b.revenue)}</td>
                  <td className="rp-num rp-muted-money">{service > 0 ? formatPeso(service) : '—'}</td>
                  <td className="rp-num">{formatPeso(paid)}</td>
                  <td>{formatWhen(b.checkedOutAt)}</td>
                </tr>
              );
            })}
          </tbody>
          <tfoot>
            <tr>
              <td colSpan={5}>{rows.length} stay{rows.length === 1 ? '' : 's'}</td>
              <td className="rp-num">{formatPeso(totals.roomCharge)}</td>
              <td className="rp-num">{formatPeso(totals.addons)}</td>
              <td className="rp-num">{formatPeso(totals.extras)}</td>
              <td className="rp-num rp-money">{formatPeso(totals.room)}</td>
              <td className="rp-num"></td>
              <td className="rp-num rp-money">{formatPeso(totals.collected)}</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
      {noCloseCount > 0 && (
        <p className="rp-note">{noCloseCount} checked-out stay{noCloseCount === 1 ? '' : 's'} have no closing date and are not included in any range.</p>
      )}
      <Pager page={safePage} totalPages={totalPages} total={rows.length} perPage={PER_PAGE} onPage={onPage} />
    </>
  );
}

function DineInTable({ rows, total, page, onPage, rangeLabel, tableNameById }) {
  const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageRows = rows.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  if (rows.length === 0) {
    return <EmptyState icon="fa-utensils" message={`No dine-in orders were completed between ${rangeLabel}.`} />;
  }

  return (
    <>
      <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
        <table className="rp-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Table</th>
              <th>Guest</th>
              <th className="rp-num">Items</th>
              <th className="rp-num">Total</th>
              <th>Placed By</th>
              <th>Placed</th>
            </tr>
          </thead>
          <tbody>
            {pageRows.map(o => {
              const { count, title } = itemsSummary(o.items);
              return (
                <tr key={o.id}>
                  <td className="rp-strong">#{o.id}</td>
                  <td>{o.tableId ? (tableNameById[o.tableId] || `#${o.tableId}`) : '—'}</td>
                  <td>{o.guestName || '—'}</td>
                  <td className="rp-num" title={title}>{count}</td>
                  <td className="rp-num rp-money">{o.total > 0 ? formatPeso(o.total) : <span className="rp-zero">₱0</span>}</td>
                  <td>{o.placedBy || '—'}</td>
                  <td>{formatWhen(o.placedAt)}</td>
                </tr>
              );
            })}
          </tbody>
          <tfoot>
            <tr>
              <td colSpan={4}>{rows.length} order{rows.length === 1 ? '' : 's'}</td>
              <td className="rp-num rp-money">{formatPeso(total)}</td>
              <td colSpan={2}></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <Pager page={safePage} totalPages={totalPages} total={rows.length} perPage={PER_PAGE} onPage={onPage} />
    </>
  );
}

function RoomServiceTable({ rows, total, page, onPage, rangeLabel, openStayTotal }) {
  const totalPages = Math.max(1, Math.ceil(rows.length / PER_PAGE));
  const safePage = Math.min(page, totalPages);
  const pageRows = rows.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  if (rows.length === 0) {
    return <EmptyState icon="fa-bell-concierge" message={`No room service orders were completed between ${rangeLabel}.`} />;
  }

  return (
    <>
      <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
        <table className="rp-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Room</th>
              <th>Booking #</th>
              <th>Guest</th>
              <th className="rp-num">Items</th>
              <th className="rp-num">Total</th>
              <th>Placed By</th>
              <th>Placed</th>
            </tr>
          </thead>
          <tbody>
            {pageRows.map(o => {
              const { count, title } = itemsSummary(o.items);
              return (
                <tr key={o.id}>
                  <td className="rp-strong">#{o.id}</td>
                  <td>{o.roomNumber || '—'}</td>
                  <td>
                    {o.bookingId ? `#${o.bookingId}` : '—'}
                    {o.stayOpen && <span className="rp-badge rp-cat-room" style={{ marginLeft: '0.4rem' }}>Open stay</span>}
                  </td>
                  <td>{o.guestName || '—'}</td>
                  <td className="rp-num" title={title}>{count}</td>
                  <td className="rp-num rp-money">{o.total > 0 ? formatPeso(o.total) : <span className="rp-zero">₱0</span>}</td>
                  <td>{o.placedBy || '—'}</td>
                  <td>{formatWhen(o.placedAt)}</td>
                </tr>
              );
            })}
          </tbody>
          <tfoot>
            <tr>
              <td colSpan={5}>{rows.length} order{rows.length === 1 ? '' : 's'}</td>
              <td className="rp-num rp-money">{formatPeso(total)}</td>
              <td colSpan={2}></td>
            </tr>
          </tfoot>
        </table>
      </div>
      {openStayTotal > 0 && (
        <p className="rp-note">{formatPeso(openStayTotal)} of this is on stays that have not checked out yet.</p>
      )}
      <Pager page={safePage} totalPages={totalPages} total={rows.length} perPage={PER_PAGE} onPage={onPage} />
    </>
  );
}

function App() {
  const [bookings, setBookings] = useState([]);
  const [orders, setOrders] = useState([]);
  const [tables, setTables] = useState([]);
  const [loadedBookings, setLoadedBookings] = useState(false);
  const [loadedOrders, setLoadedOrders] = useState(false);
  const [loadedTables, setLoadedTables] = useState(false);

  const [tab, setTab] = useState('overview');
  const [preset, setPreset] = useState('month');
  const [from, setFrom] = useState(() => presetRange('month').from);
  const [to, setTo] = useState(() => presetRange('month').to);
  const [search, setSearch] = useState('');
  const [pages, setPages] = useState({ room: 1, dinein: 1, roomsvc: 1 });

  const load = useCallback(() => {
    fetch(CFG.bookingsUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : {}))
      .then(data => { setBookings(Array.isArray(data.bookings) ? data.bookings : []); setLoadedBookings(true); })
      .catch(() => setLoadedBookings(true));

    fetch(CFG.ordersUrl + '?status=Completed', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : {}))
      .then(data => { setOrders(Array.isArray(data.orders) ? data.orders : []); setLoadedOrders(true); })
      .catch(() => setLoadedOrders(true));

    fetch(CFG.tablesUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : {}))
      .then(data => { setTables(Array.isArray(data.tables) ? data.tables : []); setLoadedTables(true); })
      .catch(() => setLoadedTables(true));

    // A screen left open across a shift should not keep reporting yesterday's
    // "Today". Only touches state when the computed range actually differs, so
    // this does not thrash the memo chain on every 15s poll.
    setPreset(p => {
      if (p !== 'custom') {
        const r = presetRange(p);
        if (r) {
          setFrom(f => (f === r.from ? f : r.from));
          setTo(t => (t === r.to ? t : r.to));
        }
      }
      return p;
    });
  }, []);

  useEffect(() => {
    load();
    const id = setInterval(load, 15000);
    window.addEventListener('focus', load);
    return () => { clearInterval(id); window.removeEventListener('focus', load); };
  }, [load]);

  const resetPages = () => setPages({ room: 1, dinein: 1, roomsvc: 1 });

  const applyPreset = (p) => {
    setPreset(p);
    const r = presetRange(p);
    if (r) { setFrom(r.from); setTo(r.to); }
    resetPages();
  };
  const applyFrom = (v) => { setFrom(v); setPreset('custom'); resetPages(); };
  const applyTo = (v) => { setTo(v); setPreset('custom'); resetPages(); };
  const applySearch = (v) => { setSearch(v); resetPages(); };

  // Completed room-service is billed to the stay, but the room side excludes it
  // (see roomAll below) so it is counted exactly once, in this category. A
  // cancelled stay never happened and never billed anything.
  const cancelledIds = useMemo(() => {
    const s = new Set();
    bookings.forEach(b => { if (b.status === 'Cancelled') s.add(b.bookingId); });
    return s;
  }, [bookings]);

  // Stays still holding a room, keyed by id — a Completed room-service order on
  // one of these is real revenue already, just not settled into a final bill yet.
  const openStayIds = useMemo(() => {
    const s = new Set();
    bookings.forEach(b => { if (b.status && b.status !== 'Checked Out' && b.status !== 'Cancelled') s.add(b.bookingId); });
    return s;
  }, [bookings]);

  const roomAll = useMemo(() => bookings
    .filter(b => b.status === 'Checked Out')
    .map(b => {
      const roomCharge = Number(b.totalDue) || 0;
      const addons = Number(b.addonsTotal) || 0;
      const extras = Number(b.otherCharges) || 0;
      return {
        ...b,
        day: localDayKey(b.checkedOutAt || b.checkOut),
        // Room + add-ons + hand-added extras only — room service is billed to the
        // same stay but counted once, under its own category, not here.
        revenue: roomCharge + addons + extras,
      };
    }), [bookings]);

  const noCloseCount = useMemo(() => roomAll.filter(b => !b.day).length, [roomAll]);

  // Orders only carry the raw dine_in_table_id — there is no relation from a food
  // order to its table, so the name is joined here from the separate tables read.
  const tableNameById = useMemo(() => {
    const map = {};
    tables.forEach(t => { map[t.id] = t.name; });
    return map;
  }, [tables]);

  const foodAll = useMemo(() => orders
    .filter(o => o.status === 'Completed')
    .map(o => ({ ...o, day: localDayKey(o.placedAt), stayOpen: !!o.bookingId && openStayIds.has(o.bookingId) })),
    [orders, openStayIds]);

  const q = search.trim().toLowerCase();
  const matches = (fields) => !q || fields.some(f => String(f || '').toLowerCase().includes(q));

  const roomRows = useMemo(() => roomAll
    .filter(b => inWindow(b.day, from, to))
    .filter(b => matches([b.fullName, b.roomName, b.bookedBy, b.contactNo, b.email, b.idNumber]))
    .sort((a, b) => (a.bookingId < b.bookingId ? 1 : -1)),
    [roomAll, from, to, q]);

  const dineRows = useMemo(() => foodAll
    .filter(o => o.orderType === 'dine_in')
    .filter(o => inWindow(o.day, from, to))
    .filter(o => matches([o.id, o.tableId, o.guestName, o.placedBy, ...(Array.isArray(o.items) ? o.items.map(it => it.name) : [])]))
    .sort((a, b) => (a.id < b.id ? 1 : -1)),
    [foodAll, from, to, q]);

  const svcRows = useMemo(() => foodAll
    .filter(o => o.orderType === 'room_service')
    .filter(o => !cancelledIds.has(o.bookingId))
    .filter(o => inWindow(o.day, from, to))
    .filter(o => matches([o.id, o.roomNumber, o.bookingId, o.guestName, o.placedBy, ...(Array.isArray(o.items) ? o.items.map(it => it.name) : [])]))
    .sort((a, b) => (a.id < b.id ? 1 : -1)),
    [foodAll, cancelledIds, from, to, q]);

  const totals = useMemo(() => {
    const room = roomRows.reduce((acc, b) => ({
      roomCharge: acc.roomCharge + (Number(b.totalDue) || 0),
      addons: acc.addons + (Number(b.addonsTotal) || 0),
      extras: acc.extras + (Number(b.otherCharges) || 0),
      revenue: acc.revenue + b.revenue,
      collected: acc.collected + (Number(b.amountPaid) || 0),
      outstanding: acc.outstanding + Math.max(0, b.revenue - (Number(b.amountPaid) || 0)),
    }), { roomCharge: 0, addons: 0, extras: 0, revenue: 0, collected: 0, outstanding: 0 });

    const dineIn = dineRows.reduce((sum, o) => sum + (Number(o.total) || 0), 0);
    const roomService = svcRows.reduce((sum, o) => sum + (Number(o.total) || 0), 0);
    const svcOnOpenStay = svcRows.filter(o => o.stayOpen).reduce((sum, o) => sum + (Number(o.total) || 0), 0);

    return {
      roomCharge: room.roomCharge,
      addons: room.addons,
      extras: room.extras,
      room: room.revenue,
      collected: room.collected,
      outstanding: room.outstanding,
      dineIn,
      roomService,
      svcOnOpenStay,
      grand: room.revenue + dineIn + roomService,
    };
  }, [roomRows, dineRows, svcRows]);

  const counts = { room: roomRows.length, dinein: dineRows.length, roomsvc: svcRows.length };
  const rangeLabel = formatRange(from, to);
  const narrowed = !!search || preset === 'custom';
  const loaded = loadedBookings && loadedOrders;
  const svcLoaded = tab !== 'roomsvc' || (loadedBookings && loadedOrders);
  const dineLoaded = tab !== 'dinein' || loadedTables;

  return (
    <div data-hms-no-edit="1" style={{ padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Front Desk</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Reports</h1>
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem' }}>
            Completed transactions and revenue — room stays, dine-in, and room service.
          </p>
        </div>
        <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </a>
      </div>

      <TabBar tab={tab} onTab={setTab} counts={counts} />

      <div style={{ display: 'flex', gap: '0.7rem', flexWrap: 'wrap', alignItems: 'flex-end', marginBottom: '1.1rem' }}>
        <div style={{ position: 'relative', flex: 1, minWidth: 220 }}>
          <label className="rp-tile-label">Search</label>
          <div style={{ position: 'relative' }}>
            <i className="fa-solid fa-magnifying-glass" style={{ position: 'absolute', left: '0.75rem', top: '50%', transform: 'translateY(-50%)', color: 'var(--fg-muted)', fontSize: '0.75rem', pointerEvents: 'none' }}></i>
            <input
              type="text"
              className="booking-input"
              placeholder="Search by guest, room, order, or who handled it…"
              value={search}
              onChange={e => applySearch(e.target.value)}
              style={{ paddingLeft: '2.1rem' }}
            />
          </div>
        </div>
        <PresetBar preset={preset} from={from} to={to} onPreset={applyPreset} onFrom={applyFrom} onTo={applyTo} />
      </div>

      {!loaded ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.85rem' }}>
          Loading reports…
        </div>
      ) : !svcLoaded ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.85rem' }}>
          Loading room service…
        </div>
      ) : !dineLoaded ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.85rem' }}>
          Loading dine-in…
        </div>
      ) : tab === 'overview' ? (
        <OverviewPanel totals={totals} counts={counts} rangeLabel={rangeLabel} />
      ) : tab === 'room' ? (
        <RoomTable
          rows={roomRows}
          totals={totals}
          page={pages.room}
          onPage={n => setPages(p => ({ ...p, room: n }))}
          rangeLabel={rangeLabel}
          noCloseCount={noCloseCount}
        />
      ) : tab === 'dinein' ? (
        <DineInTable
          rows={dineRows}
          total={totals.dineIn}
          page={pages.dinein}
          onPage={n => setPages(p => ({ ...p, dinein: n }))}
          rangeLabel={rangeLabel}
          tableNameById={tableNameById}
        />
      ) : (
        <RoomServiceTable
          rows={svcRows}
          total={totals.roomService}
          page={pages.roomsvc}
          onPage={n => setPages(p => ({ ...p, roomsvc: n }))}
          rangeLabel={rangeLabel}
          openStayTotal={totals.svcOnOpenStay}
        />
      )}

      {loaded && narrowed && tab !== 'overview' && (
        <p className="rp-note">Filtered — {rangeLabel}{search ? `, matching "${search}"` : ''}.</p>
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
