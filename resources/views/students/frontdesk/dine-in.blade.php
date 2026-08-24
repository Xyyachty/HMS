@extends('students.builder.ops-shell')

@php $backRoute = 'students.frontdesk'; @endphp

@section('page-title', 'Dine-in Tables')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }
  .dn-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; display: inline-block;
  }
  .dn-available { background: rgba(34,197,94,0.18); color: var(--success, #4ade80); border-color: rgba(34,197,94,0.35); }
  .dn-occupied  { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
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
  .btn-outline:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }
  .btn-solid {
    display: inline-flex; align-items: center; gap: 0.45rem;
    background: var(--accent); color: var(--bg); border: 1px solid var(--accent);
    font-family: var(--font-body, 'Outfit', sans-serif); font-weight: 600;
    font-size: 0.72rem; letter-spacing: 0.08em; text-transform: uppercase;
    padding: 0.55rem 1.1rem; border-radius: 6px; cursor: pointer;
    transition: filter 0.2s;
  }
  .btn-solid:hover { filter: brightness(1.1); }
  .btn-solid:disabled { opacity: 0.45; cursor: not-allowed; filter: none; }
  .booking-input {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: var(--font-body, 'Outfit', sans-serif); font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus { border-color: var(--accent); }
  .booking-input::placeholder { color: var(--fg-muted); opacity: 0.5; }
  .dn-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 0.9rem;
  }
  .dn-card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: 12px; padding: 1.1rem 1.2rem;
  }
  .dn-field-label {
    display: block; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: var(--fg-muted); margin-bottom: 0.35rem;
  }

  .dn-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.65);
    display: flex; align-items: flex-start; justify-content: center;
    padding: 2rem 1.5rem; z-index: 300; overflow-y: auto;
  }
  .dn-modal {
    background: var(--card); border: 1px solid var(--border); border-radius: 14px;
    width: 100%; max-width: 560px; margin: auto;
  }
  .dn-modal-head {
    padding: 1.2rem 1.4rem 0.9rem; border-bottom: 1px solid var(--border);
    display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
  }
  .dn-modal-body { padding: 1.1rem 1.4rem 1.4rem; }
  .dn-tab {
    font-family: var(--font-body, 'Outfit', sans-serif);
    font-size: 0.68rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;
    padding: 0.4rem 0.8rem; border-radius: 100px; border: 1.5px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer; transition: all 0.15s;
  }
  .dn-tab:hover { border-color: var(--accent); color: var(--accent); }
  .dn-tab.is-active { background: var(--accent); border-color: var(--accent); color: var(--bg); }
  .dn-menu-row {
    display: flex; align-items: center; gap: 0.6rem;
    border: 1px solid var(--border); border-radius: 8px; padding: 0.45rem 0.6rem;
  }
  .dn-step {
    width: 28px; height: 28px; border-radius: 8px; border: 1px solid var(--border);
    background: rgba(255,255,255,0.03); color: var(--accent); cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
  }
  .dn-step:disabled { opacity: 0.35; cursor: not-allowed; }
  .dn-orderline {
    display: flex; justify-content: space-between; gap: 0.75rem;
    font-size: 0.78rem; color: var(--fg-muted); padding: 0.15rem 0;
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
  :root[data-ops-theme="2"] .dn-available { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
  :root[data-ops-theme="2"] .dn-occupied { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
  :root[data-ops-theme="2"] .booking-input { background: rgba(27,67,50,0.03); }
  :root[data-ops-theme="2"] .dn-step { background: rgba(27,67,50,0.03); }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_DINE_IN = {
    backUrl: @json(route('students.frontdesk')),
    tablesUrl: @json(route('students.hotel.tables.index')),
    menusUrl: @json(route('students.hotel.menus.index')),
    ordersUrl: @json(route('students.hotel.orders.index')),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useMemo, useRef } = React;

const CFG = window.HMS_DINE_IN;

/* A ticket the kitchen still owes the table. Completed and Cancelled ones are
   history and stop counting against the table. */
const ACTIVE_ORDER_STATUSES = ['Preparing', 'Ready', 'Delivering'];

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

function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0';
  return '₱' + n.toLocaleString();
}

/* A dish with no photo of its own would render an empty box — same stand-in the
   restaurant's own menu list uses, seeded by the dish so it stays put. */
function menuFoodImg(item) {
  if (item && item.img) return item.img;
  const seed = encodeURIComponent((item && (item.id || item.name)) || 'menu');
  return 'https://picsum.photos/seed/' + seed + '/800/600.jpg';
}

/* Holding a table for a customer. The server calls this seating the guest and
   flips the table to Occupied; from the desk's side it is the reservation made
   before the order is taken. */
function ReserveForm({ table, onReserve, onCancel, busy }) {
  const [guestName, setGuestName] = useState('');
  const [partySize, setPartySize] = useState(Math.min(2, table.capacity));
  const [error, setError] = useState('');
  const partyStepBtn = {
    width: 32, height: 32, borderRadius: 8, border: '1px solid var(--border)',
    background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: 'pointer',
    display: 'inline-flex', alignItems: 'center', justifyContent: 'center', fontSize: '1rem',
  };

  const submit = (e) => {
    e.preventDefault();
    if (!guestName.trim()) { setError('Enter the customer’s name.'); return; }
    if (partySize < 1 || partySize > table.capacity) {
      setError(`Party size must be between 1 and ${table.capacity}.`);
      return;
    }
    setError('');
    onReserve(table.id, { guest_name: guestName.trim(), party_size: partySize });
  };

  return (
    <form onSubmit={submit} style={{ marginTop: '0.9rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)' }}>
      <label className="dn-field-label">Customer name</label>
      <input
        type="text"
        className="booking-input"
        placeholder="Who's dining?"
        value={guestName}
        onChange={e => setGuestName(e.target.value)}
        style={{ marginBottom: '0.6rem' }}
      />
      <label className="dn-field-label">Party size (seats {table.capacity})</label>
      {/* Stepper rather than a number input, matching the menu's quantity control —
          and it cannot be typed past the table's own capacity. */}
      <div style={{ display: 'inline-flex', alignItems: 'center', gap: '0.6rem', marginBottom: '0.6rem' }}>
        <button type="button" style={partyStepBtn} aria-label="Fewer guests"
          disabled={partySize <= 1}
          onClick={() => setPartySize(n => Math.max(1, n - 1))}>−</button>
        <span style={{ color: 'var(--fg)', minWidth: 24, textAlign: 'center', fontSize: '0.95rem', fontVariantNumeric: 'tabular-nums' }}>{partySize}</span>
        <button type="button" style={partyStepBtn} aria-label="More guests"
          disabled={partySize >= table.capacity}
          onClick={() => setPartySize(n => Math.min(table.capacity, n + 1))}>+</button>
      </div>
      {error && <p style={{ margin: '0 0 0.6rem', color: 'var(--danger, #fb7185)', fontSize: '0.78rem' }}>{error}</p>}
      <div style={{ display: 'flex', gap: '0.5rem' }}>
        <button type="submit" className="btn-solid" disabled={busy} style={{ flex: 1, justifyContent: 'center' }}>
          {busy ? 'Reserving…' : 'Reserve Table'}
        </button>
        <button type="button" className="btn-outline" onClick={onCancel} style={{ padding: '0.55rem 1rem' }}>
          Cancel
        </button>
      </div>
    </form>
  );
}

/* The order the desk takes for a table it has already reserved. Scoped to one
   table on purpose — there is no table picker, because the card it opened from
   already answered that question. */
function DineInOrderModal({ table, menus, onPlaceOrder, onClose }) {
  const [cart, setCart] = useState({});
  const [category, setCategory] = useState('All');
  const [placing, setPlacing] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape' && !placing) onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose, placing]);

  // Read off the menu rather than hard-coded, so a team that renames or adds a
  // category sees its own tabs here without this page being touched.
  const categories = useMemo(() => {
    const seen = [];
    (menus || []).forEach(m => {
      const c = String(m.category || '').trim();
      if (c && seen.indexOf(c) === -1) seen.push(c);
    });
    return seen;
  }, [menus]);

  const menuList = (menus || []).filter(m => category === 'All' || String(m.category || '').trim() === category);

  const addToCart = (item) => {
    setError('');
    setCart(prev => {
      const qty = (prev[item.id] && prev[item.id].qty) || 0;
      // Never past what the kitchen has on hand — the server rejects it anyway.
      if (qty >= Number(item.stock)) return prev;
      return Object.assign({}, prev, { [item.id]: { item, qty: qty + 1 } });
    });
  };

  const removeFromCart = (id) => {
    setCart(prev => {
      const next = Object.assign({}, prev);
      const line = next[id];
      if (!line) return prev;
      if (line.qty <= 1) { delete next[id]; return next; }
      next[id] = Object.assign({}, line, { qty: line.qty - 1 });
      return next;
    });
  };

  const cartLines = Object.values(cart);
  const cartTotal = cartLines.reduce((sum, l) => sum + (Number(l.item.price) || 0) * l.qty, 0);
  const cartCount = cartLines.reduce((sum, l) => sum + l.qty, 0);

  const submit = () => {
    if (!cartLines.length) { setError('Add at least one dish to the order.'); return; }
    setError('');
    setPlacing(true);
    const items = cartLines.map(l => ({ menu_item_id: l.item.dbId, name: l.item.name, price: l.item.price, qty: l.qty }));
    Promise.resolve(onPlaceOrder(table.id, items))
      .then(() => { setCart({}); onClose(); })
      .catch(err => setError((err && err.message) || 'Could not send this order to the kitchen.'))
      .finally(() => setPlacing(false));
  };

  return (
    <div className="dn-modal-overlay" onClick={() => { if (!placing) onClose(); }} role="dialog" aria-modal="true">
      <div className="dn-modal" onClick={e => e.stopPropagation()}>
        <div className="dn-modal-head">
          <div>
            <p style={{ color: 'var(--accent)', fontSize: '0.65rem', letterSpacing: '0.2em', textTransform: 'uppercase', margin: '0 0 0.35rem' }}>Dine-in Order</p>
            <h2 className="font-display" style={{ fontSize: '1.35rem', margin: 0, color: 'var(--fg)' }}>{table.name}</h2>
            <p style={{ margin: '0.3rem 0 0', color: 'var(--fg-muted)', fontSize: '0.78rem' }}>
              {table.guestName || 'Guest'} · party of {table.partySize || '—'}
            </p>
          </div>
          <button type="button" onClick={onClose} disabled={placing} aria-label="Close"
            style={{ width: 34, height: 34, borderRadius: 8, border: '1px solid var(--border)', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)', cursor: placing ? 'not-allowed' : 'pointer', flexShrink: 0 }}>
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div className="dn-modal-body">
          <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap', marginBottom: '0.85rem' }}>
            <button type="button" className={`dn-tab ${category === 'All' ? 'is-active' : ''}`} onClick={() => setCategory('All')}>All</button>
            {categories.map(c => (
              <button key={c} type="button" className={`dn-tab ${category === c ? 'is-active' : ''}`} onClick={() => setCategory(c)}>{c}</button>
            ))}
          </div>

          <div style={{ display: 'grid', gap: '0.4rem', maxHeight: 320, overflowY: 'auto', marginBottom: '0.9rem' }}>
            {menuList.length === 0 && (
              <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.8rem' }}>
                {(menus || []).length === 0 ? 'The restaurant has no menu items yet.' : 'No dishes in this category.'}
              </p>
            )}
            {menuList.map(item => {
              const inCart = cart[item.id] ? cart[item.id].qty : 0;
              const soldOut = Number(item.stock) <= 0;
              return (
                <div key={item.id} className="dn-menu-row">
                  <img src={menuFoodImg(item)} alt="" style={{ width: 52, height: 40, objectFit: 'cover', borderRadius: 6, flexShrink: 0, background: 'var(--bg-warm, #12110f)' }} />
                  <div style={{ minWidth: 0, flex: 1 }}>
                    <p style={{ margin: 0, color: 'var(--fg)', fontSize: '0.82rem', fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{item.name}</p>
                    {item.sub ? (
                      <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.7rem', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{item.sub}</p>
                    ) : null}
                    <p style={{ margin: 0, color: 'var(--accent-light)', fontSize: '0.74rem' }}>{formatPeso(item.price)} · {item.stock} left</p>
                  </div>
                  {soldOut ? (
                    <span style={{ fontSize: '0.68rem', color: 'var(--danger, #fb7185)' }}>Out of stock</span>
                  ) : inCart ? (
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.4rem' }}>
                      <button type="button" className="dn-step" onClick={() => removeFromCart(item.id)} aria-label={`One less ${item.name}`}>−</button>
                      <span style={{ color: 'var(--fg)', fontSize: '0.82rem', minWidth: 16, textAlign: 'center' }}>{inCart}</span>
                      <button type="button" className="dn-step" onClick={() => addToCart(item)} disabled={inCart >= Number(item.stock)} aria-label={`One more ${item.name}`}>+</button>
                    </div>
                  ) : (
                    <button type="button" className="btn-outline" style={{ fontSize: '0.66rem', padding: '0.35rem 0.7rem' }} onClick={() => addToCart(item)}>
                      Add
                    </button>
                  )}
                </div>
              );
            })}
          </div>

          {cartLines.length > 0 && (
            <div style={{ borderTop: '1px solid var(--border)', paddingTop: '0.7rem', marginBottom: '0.8rem' }}>
              {cartLines.map(l => (
                <div className="dn-orderline" key={l.item.id}>
                  <span>{l.item.name} × {l.qty}</span>
                  <span style={{ color: 'var(--fg)' }}>{formatPeso((Number(l.item.price) || 0) * l.qty)}</span>
                </div>
              ))}
            </div>
          )}

          {error && <p style={{ margin: '0 0 0.7rem', color: 'var(--danger, #fb7185)', fontSize: '0.78rem' }}>{error}</p>}

          <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '0.75rem', flexWrap: 'wrap' }}>
            <span style={{ color: 'var(--fg-muted)', fontSize: '0.8rem' }}>
              {cartCount} item{cartCount === 1 ? '' : 's'} · <span style={{ color: 'var(--fg)' }}>{formatPeso(cartTotal)}</span>
            </span>
            <button type="button" className="btn-solid" disabled={placing || !cartLines.length} onClick={submit}>
              <i className="fa-solid fa-utensils" style={{ fontSize: '0.7rem' }}></i>
              {placing ? 'Sending…' : 'Send to Kitchen'}
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

function TableCard({ table, canReserve, canOrder, orders, onReserve, onOpenOrder, busy }) {
  const [reserving, setReserving] = useState(false);
  const available = table.status === 'Available';

  const tableOrders = (orders || []).filter(o => (
    String(o.tableId) === String(table.id) && ACTIVE_ORDER_STATUSES.indexOf(o.status) !== -1
  ));
  const openTotal = tableOrders.reduce((sum, o) => sum + (Number(o.total) || 0), 0);

  return (
    <div className="dn-card">
      <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '0.75rem' }}>
        <div>
          <p style={{ margin: 0, color: 'var(--fg)', fontWeight: 700, fontSize: '1rem' }}>{table.name}</p>
          <p style={{ margin: '0.25rem 0 0', color: 'var(--fg-muted)', fontSize: '0.75rem' }}>Seats {table.capacity}</p>
        </div>
        <span className={`dn-badge dn-${table.status.toLowerCase()}`}>{available ? 'Available' : 'Reserved'}</span>
      </div>

      {!available && (
        <div style={{ marginTop: '0.85rem', fontSize: '0.8rem', color: 'var(--fg)' }}>
          <p style={{ margin: 0 }}>{table.guestName || 'Guest'} · party of {table.partySize || '—'}</p>
          <p style={{ margin: '0.25rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem' }}>
            Reserved {formatWhen(table.assignedAt)}{table.assignedBy ? ` by ${table.assignedBy}` : ''}
          </p>
          {tableOrders.length > 0 && (
            <p style={{ margin: '0.45rem 0 0', color: 'var(--accent-light)', fontSize: '0.72rem' }}>
              <i className="fa-solid fa-fire-burner" style={{ fontSize: '0.68rem', marginRight: 5 }}></i>
              {tableOrders.length} order{tableOrders.length === 1 ? '' : 's'} with the kitchen · {formatPeso(openTotal)}
            </p>
          )}
        </div>
      )}

      {available && canReserve && !reserving && (
        <button
          type="button"
          className="btn-outline"
          onClick={() => setReserving(true)}
          style={{ marginTop: '0.9rem', width: '100%', justifyContent: 'center', fontSize: '0.7rem', padding: '0.5rem' }}
        >
          <i className="fa-solid fa-chair" style={{ fontSize: '0.7rem' }}></i> Reserve Table
        </button>
      )}

      {available && canReserve && reserving && (
        <ReserveForm
          table={table}
          busy={busy}
          onCancel={() => setReserving(false)}
          onReserve={(id, payload) => onReserve(id, payload, () => setReserving(false))}
        />
      )}

      {!available && canOrder && (
        <button
          type="button"
          className="btn-solid"
          onClick={() => onOpenOrder(table)}
          style={{ marginTop: '0.9rem', width: '100%', justifyContent: 'center' }}
        >
          <i className="fa-solid fa-utensils" style={{ fontSize: '0.7rem' }}></i> Place Order
        </button>
      )}
    </div>
  );
}

function App() {
  const [tables, setTables] = useState([]);
  const [menus, setMenus] = useState([]);
  const [orders, setOrders] = useState([]);
  const [canReserve, setCanReserve] = useState(false);
  const [canOrder, setCanOrder] = useState(false);
  const [search, setSearch] = useState('');
  const [busy, setBusy] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [orderingTable, setOrderingTable] = useState(null);
  const pendingWrites = useRef(0);

  const load = useCallback(() => {
    if (pendingWrites.current > 0) return;
    const json = (url) => fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .catch(() => null);

    Promise.all([json(CFG.tablesUrl), json(CFG.menusUrl), json(CFG.ordersUrl + '?type=dine_in')])
      .then(([tableData, menuData, orderData]) => {
        if (pendingWrites.current > 0) return;
        if (tableData) {
          if (Array.isArray(tableData.tables)) setTables(tableData.tables);
          setCanReserve(!!tableData.can_assign);
        }
        if (menuData && Array.isArray(menuData.items)) setMenus(menuData.items);
        if (orderData) {
          if (Array.isArray(orderData.orders)) setOrders(orderData.orders);
          setCanOrder(!!orderData.can_place_dine_in);
        }
      })
      .finally(() => setLoaded(true));
  }, []);

  useEffect(() => {
    load();
    const id = setInterval(load, 8000);
    window.addEventListener('focus', load);
    return () => { clearInterval(id); window.removeEventListener('focus', load); };
  }, [load]);

  const reserveTable = (id, payload, done) => {
    setBusy(true);
    pendingWrites.current += 1;
    fetch(`${CFG.tablesUrl}/${id}`, {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(payload),
    })
      .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.message || 'Could not reserve this table.');
        return data;
      })
      .then(data => {
        if (data.table) {
          setTables(prev => prev.map(t => (t.id === data.table.id ? data.table : t)));
          if (window.toast) window.toast(`Reserved ${data.table.name} for ${data.table.guestName}`);
        }
        if (done) done();
      })
      .catch(e => { if (window.toast) window.toast(e.message); })
      .finally(() => {
        setBusy(false);
        pendingWrites.current = Math.max(0, pendingWrites.current - 1);
      });
  };

  /* Rejects rather than toasting on failure: the modal shows the reason inline,
     next to the cart the desk still has to fix. */
  const placeOrder = (tableId, items) => {
    pendingWrites.current += 1;
    return fetch(CFG.ordersUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ order_type: 'dine_in', dine_in_table_id: tableId, items }),
    })
      .then(async r => {
        const data = await r.json().catch(() => ({}));
        if (!r.ok) throw new Error(data.message || 'Could not send this order to the kitchen.');
        return data;
      })
      .then(data => {
        if (data.order) {
          setOrders(prev => [data.order, ...prev]);
          const table = tables.find(t => String(t.id) === String(tableId));
          if (window.toast) window.toast(`Order sent to the kitchen for ${table ? table.name : 'the table'}.`);
        }
        // Stock moved with the order, so the menu the next order is built from
        // has to come back from the server rather than be patched here.
        pendingWrites.current = Math.max(0, pendingWrites.current - 1);
        load();
        return data;
      })
      .catch(e => {
        pendingWrites.current = Math.max(0, pendingWrites.current - 1);
        throw e;
      });
  };

  const visible = useMemo(() => {
    const q = search.trim().toLowerCase();
    if (!q) return tables;
    return tables.filter(t => [t.name, t.guestName].some(field => String(field || '').toLowerCase().includes(q)));
  }, [tables, search]);

  const availableCount = tables.filter(t => t.status === 'Available').length;
  // The card the modal was opened from, re-read from state so the party details
  // it shows follow a refresh instead of freezing at open time.
  const activeTable = orderingTable ? tables.find(t => t.id === orderingTable.id) || orderingTable : null;

  return (
    <div data-hms-no-edit="1" style={{ padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.5rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Front Desk</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Dine-in Tables</h1>
          <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.82rem' }}>
            {tables.length === 0
              ? 'No tables set up yet.'
              : `${availableCount} of ${tables.length} available · reserve a table, then order for the customer at it`}
          </p>
        </div>
        <a href={CFG.backUrl} className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </a>
      </div>

      {tables.length > 0 && (
        <div style={{ position: 'relative', marginBottom: '1.1rem' }}>
          <i className="fa-solid fa-magnifying-glass" style={{ position: 'absolute', left: '0.75rem', top: '50%', transform: 'translateY(-50%)', color: 'var(--fg-muted)', fontSize: '0.75rem', pointerEvents: 'none' }}></i>
          <input
            type="text"
            className="booking-input"
            placeholder="Search by table or customer…"
            value={search}
            onChange={e => setSearch(e.target.value)}
            style={{ paddingLeft: '2.1rem' }}
          />
        </div>
      )}

      {!loaded ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center', color: 'var(--fg-muted)', fontSize: '0.85rem' }}>
          Loading tables…
        </div>
      ) : tables.length === 0 ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem', textAlign: 'center' }}>
          <i className="fa-solid fa-utensils" style={{ fontSize: '1.8rem', color: 'var(--fg-muted)', opacity: 0.35, display: 'block', marginBottom: '0.7rem' }}></i>
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.88rem' }}>
            Restaurant Management hasn't added any tables yet.
          </p>
        </div>
      ) : visible.length === 0 ? (
        <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2rem', textAlign: 'center' }}>
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.88rem' }}>No tables match "{search}"</p>
        </div>
      ) : (
        <div className="dn-grid">
          {visible.map(table => (
            <TableCard
              key={table.id}
              table={table}
              canReserve={canReserve}
              canOrder={canOrder}
              orders={orders}
              onReserve={reserveTable}
              onOpenOrder={setOrderingTable}
              busy={busy}
            />
          ))}
        </div>
      )}

      {activeTable && (
        <DineInOrderModal
          table={activeTable}
          menus={menus}
          onPlaceOrder={placeOrder}
          onClose={() => setOrderingTable(null)}
        />
      )}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
