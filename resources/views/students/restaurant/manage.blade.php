@extends('students.builder.ops-shell')

@php $backRoute = 'students.restaurant'; @endphp

@section('page-title', 'Restaurant Management')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: 'Outfit', sans-serif; }
  .font-display { font-family: 'Playfair Display', serif; }
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
  .tb-badge {
    padding: 0.22rem 0.6rem; border-radius: 4px;
    font-size: 0.62rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; display: inline-block;
  }
  .tb-available { background: rgba(34,197,94,0.18); color: #4ade80; border-color: rgba(34,197,94,0.35); }
  .tb-occupied  { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
  .tb-pending   { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35); }
  .tb-preparing { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .tb-delivered { background: rgba(34,197,94,0.18); color: #4ade80; border-color: rgba(34,197,94,0.35); }
  .tb-cancelled { background: rgba(148,163,184,0.15); color: #94a3b8; border-color: rgba(148,163,184,0.3); }
  .tb-tab {
    padding: 0.35rem 0.8rem; border-radius: 999px; border: 1px solid var(--border);
    background: transparent; color: var(--fg-muted); cursor: pointer;
    font-family: 'Outfit', sans-serif; font-size: 0.68rem; font-weight: 600;
    letter-spacing: 0.06em; transition: all 0.15s;
  }
  .tb-tab:hover { color: var(--fg); }
  .tb-tab.is-active { border-color: var(--accent); background: var(--accent); color: var(--bg); }
  .tb-tab:disabled { opacity: 0.4; cursor: not-allowed; }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_RESTAURANT_URL = @json(route('students.restaurant'));
  window.HMS_RESTAURANT_INITIAL_NAV = @json(request()->query('nav', 'manage-menu'));
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef } = React;

const MENU_CATEGORIES = ['Main Dishes', 'Appetizers', 'Soups', 'Desserts', 'Beverages'];
const IMAGE_MAX_DIMENSION = 1280;
const IMAGE_MAX_BYTES = 600 * 1024;

function normalizeMenuCategory(value) {
  const raw = String(value || 'Main Dishes').trim().toLowerCase();
  const match = MENU_CATEGORIES.find(c => c.toLowerCase() === raw);
  if (match) return match;
  if (raw === 'dining' || raw === 'main' || raw === 'mains') return 'Main Dishes';
  if (raw === 'bar' || raw === 'drinks' || raw === 'beverage') return 'Beverages';
  if (raw === 'dessert' || raw === 'sweets') return 'Desserts';
  if (raw === 'appetizer' || raw === 'starter' || raw === 'starters') return 'Appetizers';
  if (raw === 'soup') return 'Soups';
  return 'Main Dishes';
}
function formatPeso(amount) {
  const n = Number(amount);
  if (!Number.isFinite(n)) return '₱0';
  return '₱' + n.toLocaleString();
}
function menuFoodImg(item) {
  if (item && item.img) return item.img;
  const seed = encodeURIComponent((item && (item.id || item.name)) || 'menu');
  return 'https://picsum.photos/seed/' + seed + '/800/600.jpg';
}
function toolBtnStyle(kind) {
  const base = { width: 28, height: 28, borderRadius: 8, cursor: 'pointer', display: 'inline-flex', alignItems: 'center', justifyContent: 'center', border: '1px solid var(--border)' };
  if (kind === 'danger') return Object.assign({}, base, { background: 'rgba(127,29,29,0.85)', color: '#fecaca', borderColor: '#7f1d1d' });
  if (kind === 'image') return Object.assign({}, base, { background: 'rgba(12,11,9,0.85)', color: '#38bdf8' });
  return Object.assign({}, base, { background: 'rgba(12,11,9,0.85)', color: 'var(--accent)' });
}
function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.getAttribute('content') : '';
}
function hmsConfirm(message) {
  try { return !!window.confirm(message); } catch (e) { return true; }
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

function createEmptyMenuForm(category) {
  return { name: '', category: category || 'Main Dishes', price: '', stock: '', sub: '', img: '' };
}

function ManageMenuPanel({ menus, onAddMenu, onEditMenu, onRemoveMenu, onToast, onCancel }) {
  const [form, setForm] = useState(createEmptyMenuForm);
  const [editingId, setEditingId] = useState(null);
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);
  const [filter, setFilter] = useState('All');

  const fieldLabel = {
    fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase',
    color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem',
  };

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  const resetForm = () => {
    setForm(createEmptyMenuForm());
    setEditingId(null);
    setErrors({});
  };

  const startEdit = (item) => {
    setEditingId(item.id);
    setErrors({});
    setForm({
      name: item.name || '',
      category: normalizeMenuCategory(item.category),
      price: String(item.price || ''),
      stock: item.stock != null ? String(item.stock) : '',
      sub: item.sub || '',
      img: item.img || '',
    });
  };

  const handleSubmit = (e) => {
    e.preventDefault();

    const next = {};
    if (!String(form.name).trim()) next.name = 'Item name is required.';
    const price = parseInt(String(form.price).replace(/[^0-9]/g, ''), 10);
    if (!Number.isFinite(price) || price <= 0) next.price = 'Enter a valid price.';
    const stockRaw = String(form.stock).trim();
    const stock = stockRaw === '' ? 0 : parseInt(stockRaw.replace(/[^0-9]/g, ''), 10);
    if (!Number.isFinite(stock) || stock < 0) next.stock = 'Enter a valid stock count.';
    setErrors(next);
    if (Object.keys(next).length) return;

    const payload = {
      name: String(form.name).trim(),
      category: normalizeMenuCategory(form.category),
      price,
      stock,
      description: String(form.sub || '').trim(),
      image: form.img || '',
    };

    setSaving(true);
    const action = editingId ? onEditMenu(editingId, payload) : onAddMenu(payload);
    Promise.resolve(action)
      .then(() => {
        if (onToast) onToast(editingId ? `${payload.name} updated.` : `${payload.name} added to ${payload.category}.`);
        resetForm();
      })
      .catch((err) => {
        setErrors({ form: (err && err.message) || 'Could not save this item.' });
      })
      .finally(() => setSaving(false));
  };

  const handleRemove = (item) => {
    if (!hmsConfirm(`Remove "${item.name}" from the menu?`)) return;
    Promise.resolve(onRemoveMenu(item.id))
      .then(() => {
        if (editingId === item.id) resetForm();
        if (onToast) onToast(`${item.name} removed.`);
      })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not remove that item.'); });
  };

  const errorText = (key) => (
    errors[key]
      ? <p style={{ margin: '0.35rem 0 0', color: '#fb7185', fontSize: '0.72rem' }}>{errors[key]}</p>
      : null
  );

  const list = menus || [];
  const visible = filter === 'All' ? list : list.filter(m => normalizeMenuCategory(m.category) === filter);

  return (
    <div className="rm-panel" style={{ maxWidth: '100%' }}>
      <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Kitchen</p>
      <h3>Manage Menu</h3>
      <p className="rm-panel-desc">
        {editingId
          ? 'Editing an existing item. Save to publish the change to the Restaurant page.'
          : 'Add a food item to the restaurant menu. It appears on the Restaurant page right away.'}
      </p>

      <form onSubmit={handleSubmit} className="rm-form-grid" noValidate style={{ maxWidth: 520 }}>
        <div>
          <label style={fieldLabel}>Item Name *</label>
          <input
            type="text" className="booking-input" placeholder="e.g. Grilled Angus Ribeye"
            value={form.name} onChange={e => update('name', e.target.value)}
            style={errors.name ? { borderColor: '#f43f5e' } : undefined}
          />
          {errorText('name')}
        </div>

        <div className="rm-form-row">
          <div>
            <label style={fieldLabel}>Category *</label>
            <select
              className="booking-input" value={form.category} onChange={e => update('category', e.target.value)}
              style={{ colorScheme: 'dark', background: 'rgba(255,255,255,0.03)', color: 'var(--fg)' }}
            >
              {MENU_CATEGORIES.map(c => (
                <option key={c} value={c} style={{ background: '#181714', color: 'var(--fg)' }}>{c}</option>
              ))}
            </select>
          </div>
          <div>
            <label style={fieldLabel}>Price *</label>
            <input
              type="number" min="1" step="1" className="booking-input" placeholder="e.g. 1350"
              value={form.price} onChange={e => update('price', e.target.value)}
              style={errors.price ? { borderColor: '#f43f5e' } : undefined}
            />
            {errorText('price')}
          </div>
        </div>

        <div>
          <label style={fieldLabel}>Stock</label>
          <input
            type="number" min="0" step="1" className="booking-input" placeholder="e.g. 25"
            value={form.stock} onChange={e => update('stock', e.target.value)}
            style={errors.stock ? { borderColor: '#f43f5e' } : undefined}
          />
          {errorText('stock')}
        </div>

        <div>
          <label style={fieldLabel}>Short Description</label>
          <textarea
            className="booking-input" rows={2} placeholder="garlic butter, roasted vegetables, jus"
            value={form.sub} onChange={e => update('sub', e.target.value)}
            style={{ resize: 'vertical', minHeight: 68 }}
          />
        </div>

        <div>
          <label style={fieldLabel}>Food Photo</label>
          <div
            onClick={() => pickImageFile(url => { if (url) update('img', url); })}
            style={{ border: '1.5px dashed var(--border)', borderRadius: 8, cursor: 'pointer', overflow: 'hidden', background: 'rgba(255,255,255,0.02)', transition: 'border-color 0.2s' }}
            onMouseEnter={e => e.currentTarget.style.borderColor = 'var(--accent)'}
            onMouseLeave={e => e.currentTarget.style.borderColor = 'var(--border)'}
          >
            {form.img ? (
              <img src={form.img} alt="Food preview" style={{ width: '100%', height: 130, objectFit: 'cover', display: 'block' }} />
            ) : (
              <div style={{ height: 92, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 8, color: 'var(--fg-muted)' }}>
                <i className="fa-solid fa-cloud-arrow-up" style={{ fontSize: '1.4rem', color: 'var(--accent)', opacity: 0.7 }}></i>
                <span style={{ fontSize: '0.75rem' }}>Click to upload image</span>
              </div>
            )}
          </div>
          {form.img && (
            <button type="button" onClick={() => update('img', '')}
              style={{ marginTop: '0.4rem', background: 'none', border: 'none', color: '#fb7185', fontSize: '0.72rem', cursor: 'pointer', padding: 0, fontFamily: 'Outfit, sans-serif' }}>
              <i className="fa-solid fa-xmark" style={{ marginRight: 4 }}></i>Remove image
            </button>
          )}
        </div>

        {errors.form && <p style={{ margin: 0, color: '#fb7185', fontSize: '0.78rem' }}>{errors.form}</p>}

        <div style={{ display: 'flex', gap: '0.75rem', marginTop: '0.35rem', flexWrap: 'wrap' }}>
          <button type="submit" className="btn-primary" disabled={saving}>
            <i className={`fa-solid ${editingId ? 'fa-floppy-disk' : 'fa-plus'}`} style={{ fontSize: '0.7rem' }}></i>
            {saving ? 'Saving...' : (editingId ? 'Save Changes' : 'Add Menu Item')}
          </button>
          <button type="button" className="btn-outline" style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}
            onClick={() => (editingId ? resetForm() : (typeof onCancel === 'function' ? onCancel() : resetForm()))}>
            Cancel
          </button>
        </div>
      </form>

      <div style={{ marginTop: '2rem' }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '1rem', flexWrap: 'wrap', marginBottom: '0.85rem' }}>
          <h3 style={{ margin: 0, fontSize: '1.1rem' }}>Current Menu</h3>
          <select
            className="booking-input" value={filter} onChange={e => setFilter(e.target.value)}
            style={{ width: 'auto', minWidth: 150, colorScheme: 'dark', fontSize: '0.78rem', padding: '0.45rem 0.7rem' }}
          >
            <option value="All" style={{ background: '#181714' }}>All categories</option>
            {MENU_CATEGORIES.map(c => (
              <option key={c} value={c} style={{ background: '#181714' }}>{c}</option>
            ))}
          </select>
        </div>

        {visible.length === 0 ? (
          <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', margin: 0 }}>
            No items{filter !== 'All' ? ` in ${filter}` : ''} yet.
          </p>
        ) : (
          <div style={{ display: 'grid', gap: '0.5rem' }}>
            {visible.map(item => (
              <div key={item.id} style={{
                display: 'flex', alignItems: 'center', gap: '0.85rem',
                border: '1px solid ' + (editingId === item.id ? 'var(--accent)' : 'var(--border)'),
                borderRadius: 10, padding: '0.6rem 0.75rem',
                background: editingId === item.id ? 'rgba(201,168,76,0.06)' : 'transparent',
              }}>
                <img src={menuFoodImg(item)} alt="" style={{ width: 52, height: 40, objectFit: 'cover', borderRadius: 6, flexShrink: 0, background: '#12110f' }} />
                <div style={{ minWidth: 0, flex: 1 }}>
                  <p style={{ margin: 0, fontSize: '0.62rem', letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--accent)' }}>
                    {normalizeMenuCategory(item.category)}
                  </p>
                  <p style={{ margin: '2px 0 0', color: 'var(--fg)', fontWeight: 600, fontSize: '0.88rem', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                    {item.name}
                  </p>
                  {item.sub && (
                    <p style={{ margin: '2px 0 0', color: 'var(--fg-muted)', fontSize: '0.74rem', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                      {item.sub}
                    </p>
                  )}
                </div>
                <span style={{ color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontWeight: 700, whiteSpace: 'nowrap' }}>
                  {typeof item.price === 'number' ? formatPeso(item.price) : (item.price || '—')}
                </span>
                <span style={{
                  fontSize: '0.68rem', fontWeight: 700, whiteSpace: 'nowrap', padding: '3px 8px', borderRadius: 999,
                  color: item.stock > 0 ? 'var(--fg-muted)' : '#fb7185',
                  background: item.stock > 0 ? 'rgba(255,255,255,0.05)' : 'rgba(244,63,94,0.12)',
                }}>
                  {item.stock > 0 ? `${item.stock} in stock` : 'Out of stock'}
                </span>
                <div style={{ display: 'flex', gap: 6, flexShrink: 0 }}>
                  <button type="button" title="Edit item" onClick={() => startEdit(item)} style={toolBtnStyle('edit')}>
                    <i className="fa-solid fa-pen" style={{ fontSize: 10 }}></i>
                  </button>
                  <button type="button" title="Remove item" onClick={() => handleRemove(item)} style={toolBtnStyle('danger')}>
                    <i className="fa-solid fa-xmark" style={{ fontSize: 12 }}></i>
                  </button>
                </div>
              </div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}

const ORDER_STATUSES = ['Pending', 'Preparing', 'Delivered', 'Cancelled'];
const OPEN_ORDER_STATUSES = ['Pending', 'Preparing'];

function createEmptyTableForm() {
  return { name: '', capacity: '2' };
}

function TableOrderPanel({ table, menus, orders, onPlaceOrder, onUpdateOrderStatus, onToast }) {
  const [cart, setCart] = useState({});
  const [category, setCategory] = useState('All');
  const [placing, setPlacing] = useState(false);

  const tableOrders = (orders || [])
    .filter(o => o.tableId === table.id)
    .sort((a, b) => (a.id < b.id ? 1 : -1));

  const menuList = (menus || []).filter(m => category === 'All' || normalizeMenuCategory(m.category) === category);

  const addToCart = (item) => {
    setCart(prev => {
      const qty = (prev[item.id] && prev[item.id].qty) || 0;
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

  const placeOrder = () => {
    if (!cartLines.length) { if (onToast) onToast('Add at least one item to the order.'); return; }
    setPlacing(true);
    const items = cartLines.map(l => ({ menu_item_id: l.item.id, name: l.item.name, price: l.item.price, qty: l.qty }));
    Promise.resolve(onPlaceOrder(table.id, items))
      .then(() => { setCart({}); if (onToast) onToast(`Order sent to the kitchen for ${table.name}.`); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not place this order.'); })
      .finally(() => setPlacing(false));
  };

  return (
    <div style={{ marginTop: '0.9rem', paddingTop: '0.9rem', borderTop: '1px solid var(--border)' }}>
      <div style={{ display: 'flex', gap: '0.4rem', flexWrap: 'wrap', marginBottom: '0.75rem' }}>
        <button type="button" className={`tb-tab ${category === 'All' ? 'is-active' : ''}`} onClick={() => setCategory('All')}>All</button>
        {MENU_CATEGORIES.map(c => (
          <button key={c} type="button" className={`tb-tab ${category === c ? 'is-active' : ''}`} onClick={() => setCategory(c)}>{c}</button>
        ))}
      </div>

      <div style={{ display: 'grid', gap: '0.4rem', maxHeight: 220, overflowY: 'auto', marginBottom: '0.85rem' }}>
        {menuList.length === 0 && (
          <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.78rem' }}>No menu items in this category.</p>
        )}
        {menuList.map(item => (
          <div key={item.id} style={{ display: 'flex', alignItems: 'center', gap: '0.6rem', border: '1px solid var(--border)', borderRadius: 8, padding: '0.45rem 0.6rem' }}>
            <div style={{ minWidth: 0, flex: 1 }}>
              <p style={{ margin: 0, color: 'var(--fg)', fontSize: '0.82rem', fontWeight: 600, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{item.name}</p>
              <p style={{ margin: 0, color: 'var(--accent-light)', fontSize: '0.74rem' }}>{formatPeso(item.price)}</p>
            </div>
            {item.stock <= 0 ? (
              <span style={{ fontSize: '0.68rem', color: '#fb7185' }}>Out of stock</span>
            ) : cart[item.id] ? (
              <div style={{ display: 'flex', alignItems: 'center', gap: '0.4rem' }}>
                <button type="button" onClick={() => removeFromCart(item.id)} style={toolBtnStyle('edit')}>−</button>
                <span style={{ color: 'var(--fg)', fontSize: '0.82rem', minWidth: 16, textAlign: 'center' }}>{cart[item.id].qty}</span>
                <button type="button" onClick={() => addToCart(item)} style={toolBtnStyle('edit')}>+</button>
              </div>
            ) : (
              <button type="button" className="btn-outline" style={{ fontSize: '0.68rem', padding: '0.4rem 0.7rem' }} onClick={() => addToCart(item)}>
                Add
              </button>
            )}
          </div>
        ))}
      </div>

      {cartLines.length > 0 && (
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: '0.85rem' }}>
          <span style={{ color: 'var(--fg-muted)', fontSize: '0.78rem' }}>{cartLines.length} item(s) · {formatPeso(cartTotal)}</span>
          <button type="button" className="btn-primary" disabled={placing} onClick={placeOrder} style={{ fontSize: '0.7rem', padding: '0.5rem 1rem' }}>
            {placing ? 'Sending…' : 'Send to Kitchen'}
          </button>
        </div>
      )}

      {tableOrders.length > 0 && (
        <div style={{ display: 'grid', gap: '0.5rem' }}>
          {tableOrders.map(order => (
            <div key={order.id} style={{ border: '1px solid var(--border)', borderRadius: 8, padding: '0.6rem 0.75rem' }}>
              <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: '0.5rem', marginBottom: '0.4rem' }}>
                <span className={`tb-badge tb-${order.status.toLowerCase()}`}>{order.status}</span>
                <span style={{ color: 'var(--accent-light)', fontSize: '0.78rem', fontWeight: 700 }}>{formatPeso(order.total)}</span>
              </div>
              <p style={{ margin: '0 0 0.5rem', color: 'var(--fg-muted)', fontSize: '0.74rem' }}>
                {(order.items || []).map(i => `${i.qty}× ${i.name}`).join(', ')}
              </p>
              <div style={{ display: 'flex', gap: '0.35rem', flexWrap: 'wrap' }}>
                {ORDER_STATUSES.map(status => (
                  <button
                    key={status}
                    type="button"
                    className={`tb-tab ${order.status === status ? 'is-active' : ''}`}
                    disabled={order.status === status}
                    onClick={() => Promise.resolve(onUpdateOrderStatus(order.id, status)).catch(err => { if (onToast) onToast((err && err.message) || 'Could not update this order.'); })}
                  >
                    {status}
                  </button>
                ))}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

function ManageTablesPanel({ tables, orders, menus, canManage, onAddTable, onEditTable, onCloseTable, onRemoveTable, onPlaceOrder, onUpdateOrderStatus, onToast }) {
  const [form, setForm] = useState(createEmptyTableForm);
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);
  const [expandedId, setExpandedId] = useState(null);
  const [editingId, setEditingId] = useState(null);
  const [editForm, setEditForm] = useState(null);

  const fieldLabel = {
    fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase',
    color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem',
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const next = {};
    if (!String(form.name).trim()) next.name = 'Table name is required.';
    const capacity = parseInt(form.capacity, 10);
    if (!Number.isFinite(capacity) || capacity < 1) next.capacity = 'Enter how many seats this table has.';
    setErrors(next);
    if (Object.keys(next).length) return;

    setSaving(true);
    Promise.resolve(onAddTable({ name: String(form.name).trim(), capacity }))
      .then(() => { if (onToast) onToast(`${form.name} added.`); setForm(createEmptyTableForm()); })
      .catch(err => setErrors({ form: (err && err.message) || 'Could not add this table.' }))
      .finally(() => setSaving(false));
  };

  const saveEdit = (table) => {
    const capacity = parseInt(editForm.capacity, 10);
    if (!String(editForm.name).trim() || !Number.isFinite(capacity) || capacity < 1) {
      if (onToast) onToast('Enter a valid name and capacity.');
      return;
    }
    Promise.resolve(onEditTable(table.id, { name: String(editForm.name).trim(), capacity }))
      .then(() => { setEditingId(null); if (onToast) onToast(`${editForm.name} updated.`); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not update this table.'); });
  };

  const closeTable = (table) => {
    if (!hmsConfirm(`Close ${table.name} and free it up?`)) return;
    Promise.resolve(onCloseTable(table.id))
      .then(() => { if (onToast) onToast(`${table.name} is now available.`); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not close this table.'); });
  };

  const removeTable = (table) => {
    if (!hmsConfirm(`Remove ${table.name}?`)) return;
    Promise.resolve(onRemoveTable(table.id))
      .then(() => { if (onToast) onToast(`${table.name} removed.`); })
      .catch(err => { if (onToast) onToast((err && err.message) || 'Could not remove this table.'); });
  };

  const list = tables || [];
  const hasOpenOrder = (table) => (orders || []).some(o => o.tableId === table.id && OPEN_ORDER_STATUSES.includes(o.status));

  return (
    <div className="rm-panel" style={{ maxWidth: '100%' }}>
      <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Dine-in</p>
      <h3>Manage Tables</h3>
      <p className="rm-panel-desc">
        Add tables for Front Desk to seat guests at. Once a guest is seated, take their order here.
      </p>

      {canManage && (
        <form onSubmit={handleSubmit} className="rm-form-row" noValidate style={{ maxWidth: 520, marginBottom: '1.5rem' }}>
          <div>
            <label style={fieldLabel}>Table Name *</label>
            <input
              type="text" className="booking-input" placeholder="e.g. Table 5"
              value={form.name} onChange={e => setForm(p => Object.assign({}, p, { name: e.target.value }))}
              style={errors.name ? { borderColor: '#f43f5e' } : undefined}
            />
            {errors.name && <p style={{ margin: '0.35rem 0 0', color: '#fb7185', fontSize: '0.72rem' }}>{errors.name}</p>}
          </div>
          <div>
            <label style={fieldLabel}>Seats *</label>
            <input
              type="number" min="1" step="1" className="booking-input" placeholder="e.g. 4"
              value={form.capacity} onChange={e => setForm(p => Object.assign({}, p, { capacity: e.target.value }))}
              style={errors.capacity ? { borderColor: '#f43f5e' } : undefined}
            />
            {errors.capacity && <p style={{ margin: '0.35rem 0 0', color: '#fb7185', fontSize: '0.72rem' }}>{errors.capacity}</p>}
          </div>
          <div style={{ gridColumn: '1 / -1' }}>
            {errors.form && <p style={{ margin: '0 0 0.6rem', color: '#fb7185', fontSize: '0.78rem' }}>{errors.form}</p>}
            <button type="submit" className="btn-primary" disabled={saving}>
              <i className="fa-solid fa-plus" style={{ fontSize: '0.7rem' }}></i> {saving ? 'Adding…' : 'Add Table'}
            </button>
          </div>
        </form>
      )}

      {list.length === 0 ? (
        <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem', margin: 0 }}>No tables yet.</p>
      ) : (
        <div style={{ display: 'grid', gap: '0.6rem' }}>
          {list.map(table => {
            const isEditing = editingId === table.id;
            const isExpanded = expandedId === table.id;
            const occupied = table.status === 'Occupied';

            return (
              <div key={table.id} style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '0.75rem 0.9rem' }}>
                {isEditing ? (
                  <div className="rm-form-row" style={{ alignItems: 'end' }}>
                    <input
                      type="text" className="booking-input" value={editForm.name}
                      onChange={e => setEditForm(p => Object.assign({}, p, { name: e.target.value }))}
                    />
                    <input
                      type="number" min="1" className="booking-input" value={editForm.capacity}
                      onChange={e => setEditForm(p => Object.assign({}, p, { capacity: e.target.value }))}
                    />
                    <div style={{ gridColumn: '1 / -1', display: 'flex', gap: '0.5rem' }}>
                      <button type="button" className="btn-primary" style={{ fontSize: '0.7rem', padding: '0.5rem 1rem' }} onClick={() => saveEdit(table)}>Save</button>
                      <button type="button" className="btn-outline" style={{ fontSize: '0.7rem', padding: '0.5rem 1rem' }} onClick={() => setEditingId(null)}>Cancel</button>
                    </div>
                  </div>
                ) : (
                  <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '0.75rem', flexWrap: 'wrap' }}>
                    <div>
                      <div style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', marginBottom: '0.3rem' }}>
                        <span style={{ color: 'var(--fg)', fontWeight: 700 }}>{table.name}</span>
                        <span className={`tb-badge tb-${table.status.toLowerCase()}`}>{table.status}</span>
                      </div>
                      <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.76rem' }}>
                        Seats {table.capacity}
                        {occupied && ` · ${table.guestName || 'Guest'} (party of ${table.partySize || '—'})`}
                      </p>
                    </div>
                    {canManage && (
                      <div style={{ display: 'flex', gap: 6, flexShrink: 0 }}>
                        {occupied && (
                          <button type="button" className="btn-outline" style={{ fontSize: '0.68rem', padding: '0.4rem 0.75rem' }}
                            onClick={() => setExpandedId(isExpanded ? null : table.id)}>
                            {isExpanded ? 'Hide Order' : 'Take Order'}
                          </button>
                        )}
                        {occupied ? (
                          <button type="button" title="Close table" disabled={hasOpenOrder(table)}
                            onClick={() => closeTable(table)} style={toolBtnStyle('danger')}>
                            <i className="fa-solid fa-door-closed" style={{ fontSize: 11 }}></i>
                          </button>
                        ) : (
                          <>
                            <button type="button" title="Edit table" onClick={() => { setEditingId(table.id); setEditForm({ name: table.name, capacity: String(table.capacity) }); }} style={toolBtnStyle('edit')}>
                              <i className="fa-solid fa-pen" style={{ fontSize: 10 }}></i>
                            </button>
                            <button type="button" title="Remove table" onClick={() => removeTable(table)} style={toolBtnStyle('danger')}>
                              <i className="fa-solid fa-xmark" style={{ fontSize: 12 }}></i>
                            </button>
                          </>
                        )}
                      </div>
                    )}
                  </div>
                )}

                {occupied && isExpanded && (
                  <TableOrderPanel
                    table={table}
                    menus={menus}
                    orders={orders}
                    onPlaceOrder={onPlaceOrder}
                    onUpdateOrderStatus={onUpdateOrderStatus}
                    onToast={onToast}
                  />
                )}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}

function RestaurantManagementPage({
  initialNav, menus, tables, orders, canManageTables,
  onBack, onAddMenu, onEditMenu, onRemoveMenu,
  onAddTable, onEditTable, onCloseTable, onRemoveTable, onPlaceOrder, onUpdateOrderStatus,
  onToast,
}) {
  const activeNav = initialNav || 'manage-menu';

  return (
    <div style={{ maxWidth: 1100, margin: '0 auto', padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.1rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Staff Tools</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Restaurant Management</h1>
        </div>
        <button type="button" className="btn-outline" onClick={onBack} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </button>
      </div>

      <div className="rm-row">
        <div className="rm-content">
          {activeNav === 'manage-menu' && (
            <ManageMenuPanel
              menus={menus}
              onAddMenu={onAddMenu}
              onEditMenu={onEditMenu}
              onRemoveMenu={onRemoveMenu}
              onToast={onToast}
              onCancel={onBack}
            />
          )}
          {activeNav === 'manage-tables' && (
            <ManageTablesPanel
              tables={tables}
              orders={orders}
              menus={menus}
              canManage={canManageTables}
              onAddTable={onAddTable}
              onEditTable={onEditTable}
              onCloseTable={onCloseTable}
              onRemoveTable={onRemoveTable}
              onPlaceOrder={onPlaceOrder}
              onUpdateOrderStatus={onUpdateOrderStatus}
              onToast={onToast}
            />
          )}
        </div>
      </div>
    </div>
  );
}

function App() {
  const [menus, setMenus] = useState([]);
  const [tables, setTables] = useState([]);
  const [orders, setOrders] = useState([]);
  const [canManageTables, setCanManageTables] = useState(false);
  const pendingWrites = useRef(0);

  const fetchMenus = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/menus', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => { if (pendingWrites.current > 0) return; if (Array.isArray(data.items)) setMenus(data.items); })
      .catch(() => {});
  }, []);

  const fetchTables = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/tables', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (pendingWrites.current > 0) return;
        if (Array.isArray(data.tables)) setTables(data.tables);
        setCanManageTables(!!data.can_manage);
      })
      .catch(() => {});
  }, []);

  const fetchOrders = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch('/students/hotel/orders', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(data => { if (pendingWrites.current > 0) return; if (Array.isArray(data.orders)) setOrders(data.orders); })
      .catch(() => {});
  }, []);

  useEffect(() => {
    fetchMenus();
    fetchTables();
    fetchOrders();
    const id = setInterval(() => { fetchMenus(); fetchTables(); fetchOrders(); }, 8000);
    const onFocus = () => { fetchMenus(); fetchTables(); fetchOrders(); };
    window.addEventListener('focus', onFocus);
    return () => { clearInterval(id); window.removeEventListener('focus', onFocus); };
  }, [fetchMenus, fetchTables, fetchOrders]);

  const menuRequest = useCallback((url, method, body) => {
    pendingWrites.current += 1;
    return fetch(url, {
      method,
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: body ? JSON.stringify(body) : undefined,
    })
      .then(r => r.json().then(data => (r.ok ? data : Promise.reject(data))))
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  const addMenuItem = useCallback((payload) => (
    menuRequest('/students/hotel/menus', 'POST', payload).then(data => {
      if (data && data.item) setMenus(prev => [...prev, data.item]);
      return data && data.item;
    })
  ), [menuRequest]);

  const editMenuItem = useCallback((id, patch) => (
    menuRequest('/students/hotel/menus/' + String(id).replace(/^db-/, ''), 'PATCH', patch).then(data => {
      if (data && data.item) setMenus(prev => prev.map(m => (m.id === data.item.id ? data.item : m)));
      return data && data.item;
    })
  ), [menuRequest]);

  const removeMenuItem = useCallback((id) => (
    menuRequest('/students/hotel/menus/' + String(id).replace(/^db-/, ''), 'DELETE').then(data => {
      setMenus(prev => prev.filter(m => m.id !== id));
      return data;
    })
  ), [menuRequest]);

  const addTable = useCallback((payload) => (
    menuRequest('/students/hotel/tables', 'POST', payload).then(data => {
      if (data && data.table) setTables(prev => [...prev, data.table]);
      return data && data.table;
    })
  ), [menuRequest]);

  const editTable = useCallback((id, patch) => (
    menuRequest('/students/hotel/tables/' + id, 'PATCH', patch).then(data => {
      if (data && data.table) setTables(prev => prev.map(t => (t.id === data.table.id ? data.table : t)));
      return data && data.table;
    })
  ), [menuRequest]);

  const closeTable = useCallback((id) => (
    menuRequest('/students/hotel/tables/' + id, 'PATCH', { close: true }).then(data => {
      if (data && data.table) setTables(prev => prev.map(t => (t.id === data.table.id ? data.table : t)));
      return data && data.table;
    })
  ), [menuRequest]);

  const removeTable = useCallback((id) => (
    menuRequest('/students/hotel/tables/' + id, 'DELETE').then(data => {
      setTables(prev => prev.filter(t => t.id !== id));
      return data;
    })
  ), [menuRequest]);

  const placeDineInOrder = useCallback((tableId, items) => (
    menuRequest('/students/hotel/orders', 'POST', { order_type: 'dine_in', dine_in_table_id: tableId, items }).then(data => {
      if (data && data.order) setOrders(prev => [data.order, ...prev]);
      // Stock changed underneath the menu list this order was built from.
      fetchMenus();
      return data && data.order;
    })
  ), [menuRequest, fetchMenus]);

  const updateOrderStatus = useCallback((id, status) => (
    menuRequest('/students/hotel/orders/' + id, 'PATCH', { status }).then(data => {
      if (data && data.order) setOrders(prev => prev.map(o => (o.id === data.order.id ? data.order : o)));
      if (status === 'Cancelled') fetchMenus();
      return data && data.order;
    })
  ), [menuRequest, fetchMenus]);

  return (
    <RestaurantManagementPage
      initialNav={window.HMS_RESTAURANT_INITIAL_NAV}
      menus={menus}
      tables={tables}
      orders={orders}
      canManageTables={canManageTables}
      onBack={() => { window.location.href = window.HMS_RESTAURANT_URL; }}
      onAddMenu={addMenuItem}
      onEditMenu={editMenuItem}
      onRemoveMenu={removeMenuItem}
      onAddTable={addTable}
      onEditTable={editTable}
      onCloseTable={closeTable}
      onRemoveTable={removeTable}
      onPlaceOrder={placeDineInOrder}
      onUpdateOrderStatus={updateOrderStatus}
      onToast={(msg) => window.toast && window.toast(msg)}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
