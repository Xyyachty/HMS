{{--
  Shared building blocks for the department Reports screens.

  Front Desk's and Restaurant Services' reports predate this and carry their own copies,
  which have since drifted from each other in small ways. Rather than rewrite two screens
  that work, this holds the one copy the newer reports share — Room Management,
  Housekeeping and Maintenance — so a fourth, fifth and sixth copy never happened.

  Include it inside the @verbatim babel block, above the page's own components. It expects
  window.HMS_REPORTS to be set and the reports-styles partial to be included.
--}}

@verbatim

const CFG = window.HMS_REPORTS;
const PER_PAGE = 8;

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

/* "2h 40m" between two timestamps — how long a job actually took. */
function formatElapsed(fromIso, toIso) {
  if (!fromIso || !toIso) return '—';
  const a = new Date(fromIso).getTime();
  const b = new Date(toIso).getTime();
  if (!Number.isFinite(a) || !Number.isFinite(b) || b < a) return '—';
  const mins = Math.round((b - a) / 60000);
  if (mins < 60) return mins + 'm';
  const h = Math.floor(mins / 60);
  const d = Math.floor(h / 24);
  if (d >= 1) return d + 'd ' + (h % 24) + 'h';
  return h + 'h ' + (mins % 60) + 'm';
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

   The week is Monday-to-today. A work week read against a Sunday start would split the
   weekend across two reports. getDay() returns 0 for Sunday, which is day 7 of the week
   that just ended, not day 1 of the next one. */
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

/* The one date rule for every row on these pages. A timestamp is bucketed by the day it
   reads as on a local clock — the same day formatWhen() prints beside it — and a bare
   YYYY-MM-DD already is that day, so it passes straight through rather than going
   through Date and getting shifted. */
function localDayKey(value) {
  const raw = String(value || '').trim();
  if (!raw) return '';
  if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;
  const d = new Date(raw);
  return Number.isNaN(d.getTime()) ? '' : ymd(d);
}

/* Both bounds inclusive; a row with no usable date is out of every window. A completed
   job with no completion timestamp is not reportable, and silently counting it against
   "all time" would make the totals disagree with every filtered view. */
function inWindow(day, from, to) {
  if (!day) return false;
  if (from && day < from) return false;
  if (to && day > to) return false;
  return true;
}

function Tile({ label, value, sub, grand }) {
  return (
    <div className={'rp-tile' + (grand ? ' rp-tile-grand' : '')}>
      <span className="rp-tile-label">{label}</span>
      <span className="rp-tile-value">{value}</span>
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

function PresetBar({ preset, from, to, onPreset, onFrom, onTo }) {
  const presets = [
    { key: 'today', label: 'Today' },
    { key: 'week', label: 'This Week' },
    { key: 'month', label: 'This Month' },
    { key: 'all', label: 'All Time' },
    { key: 'custom', label: 'Custom' },
  ];
  return (
    <>
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
      <div>
        <label className="rp-tile-label">From</label>
        <input type="date" className="booking-input" value={from} onChange={e => onFrom(e.target.value)} style={{ minWidth: 150 }} />
      </div>
      <div>
        <label className="rp-tile-label">To</label>
        <input type="date" className="booking-input" value={to} onChange={e => onTo(e.target.value)} style={{ minWidth: 150 }} />
      </div>
    </>
  );
}

function TabBar({ tab, onTab, tabs }) {
  if (!tabs || tabs.length < 2) return null;
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
          <span className="rp-tab-count">({t.count})</span>
        </button>
      ))}
    </div>
  );
}

/* The page heading every Reports screen opens with — eyebrow, title, one line saying
   what is counted, and the way back to the department. */
function ReportHead({ eyebrow, title, blurb }) {
  return (
    <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.2rem' }}>
      <div>
        <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>{eyebrow}</p>
        <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Reports</h1>
        <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem' }}>{blurb}</p>
      </div>
      <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
        <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
      </a>
    </div>
  );
}

/* Date presets plus a search box. `extra` is whatever else that department needs. */
function FilterBar({ preset, from, to, onPreset, onFrom, onTo, search, onSearch, searchPlaceholder, extra }) {
  return (
    <div style={{ display: 'flex', gap: '0.7rem', flexWrap: 'wrap', alignItems: 'flex-end', marginBottom: '1.1rem' }}>
      <PresetBar preset={preset} from={from} to={to} onPreset={onPreset} onFrom={onFrom} onTo={onTo} />
      {onSearch ? (
        <div style={{ position: 'relative', flex: 1, minWidth: 220 }}>
          <label className="rp-tile-label">Search</label>
          <i className="fa-solid fa-magnifying-glass" style={{ position: 'absolute', left: '0.75rem', top: '70%', transform: 'translateY(-50%)', color: 'var(--fg-muted)', fontSize: '0.75rem', pointerEvents: 'none' }}></i>
          <input
            type="text"
            className="booking-input"
            value={search}
            placeholder={searchPlaceholder || 'Search…'}
            onChange={e => onSearch(e.target.value)}
            style={{ paddingLeft: '2rem' }}
          />
        </div>
      ) : null}
      {extra}
    </div>
  );
}

/* One place that turns the preset buttons and the two date inputs into a window, so a
   report only has to say what it counts. 'all' clears both bounds. */
function useReportRange() {
  const [preset, setPresetState] = useState('month');
  const initial = presetRange('month');
  const [from, setFrom] = useState(initial ? initial.from : '');
  const [to, setTo] = useState(initial ? initial.to : '');

  const setPreset = (next) => {
    setPresetState(next);
    if (next === 'all') { setFrom(''); setTo(''); return; }
    const range = presetRange(next);
    if (range) { setFrom(range.from); setTo(range.to); }
  };

  // Typing in either date box means the period is no longer one of the presets.
  const onFrom = (v) => { setFrom(v); setPresetState('custom'); };
  const onTo = (v) => { setTo(v); setPresetState('custom'); };

  return { preset, from, to, setPreset, onFrom, onTo, rangeLabel: formatRange(from, to) };
}

/* Fetch a list once, then on focus. Reports are a record of what happened, so they do
   not need the 8-second poll the working screens run. */
function useReportFeed(url, pick) {
  const [rows, setRows] = useState([]);
  const [loading, setLoading] = useState(true);

  const load = useCallback(() => {
    if (!url) { setLoading(false); return; }
    fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(d => setRows(pick(d) || []))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [url]);

  useEffect(() => {
    load();
    window.addEventListener('focus', load);
    return () => window.removeEventListener('focus', load);
  }, [load]);

  return { rows, loading, reload: load };
}
@endverbatim
