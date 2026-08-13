@extends('students.builder.ops-shell')

@php $backRoute = 'students.housekeeping'; @endphp

@section('page-title', 'Add-ons')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: 'Outfit', sans-serif; }
  .font-display { font-family: 'Playfair Display', serif; }
  .addon-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; white-space: nowrap;
  }
  .addon-badge.is-available { background: rgba(74,222,128,0.16); color: #4ade80; border-color: rgba(74,222,128,0.35); }
  .addon-badge.is-out { background: rgba(244,63,94,0.14); color: #fb7185; border-color: rgba(244,63,94,0.35); }
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
  .btn-primary:hover:not(:disabled) { background: var(--accent-light); transform: translateY(-1px); }
  .btn-primary:disabled { opacity: 0.5; cursor: default; }
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
  .rm-panel h3 { font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; margin: 0 0 0.35rem; color: var(--fg); }
  .rm-panel-desc { color: var(--fg-muted); font-size: 0.82rem; margin: 0 0 1.35rem; line-height: 1.5; }
  .rm-form-grid { display: grid; gap: 0.95rem; }
  .rm-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }
  @media (max-width: 640px) {
    .rm-row { flex-direction: column; }
    .rm-form-row { grid-template-columns: 1fr; }
  }

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
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_ADDONS = {
    backUrl: @json(route($backRoute)),
    indexUrl: @json(route('students.hotel.addons.index')),
    storeUrl: @json(route('students.hotel.addons.store')),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef, useMemo } = React;

const PER_PAGE = 5;
const IMAGE_MAX_DIMENSION = 1280;
const IMAGE_MAX_BYTES = 600 * 1024;

const CONFIG = window.HMS_ADDONS || {};

function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : '';
}

function peso(value) {
  return '₱' + Number(value || 0).toLocaleString();
}

/* No stored image means a stable stand-in rather than an empty box — the same
   deterministic seed the rooms and menu screens use. */
function addonImg(addon) {
  if (addon && addon.img) return addon.img;
  const seed = encodeURIComponent((addon && (addon.id || addon.name)) || 'addon');
  return 'https://picsum.photos/seed/addon-' + seed + '/800/600.jpg';
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
    reader.onerror = function () { if (input.parentNode) input.parentNode.removeChild(input); };
    reader.readAsDataURL(file);
  });
  input.click();
}

function validateAddonForm(form) {
  const errors = {};
  if (!String(form.name || '').trim()) errors.name = 'Name is required.';
  const price = parseInt(String(form.price).replace(/,/g, ''), 10);
  if (!Number.isFinite(price) || price < 0) errors.price = 'Enter a price of 0 or more.';
  const quantity = parseInt(String(form.quantity).replace(/,/g, ''), 10);
  if (!Number.isFinite(quantity) || quantity < 0) errors.quantity = 'Enter a quantity of 0 or more.';
  return errors;
}

function swal(icon, title, text) {
  if (!window.Swal) return;
  window.Swal.fire({
    icon, title, text,
    background: '#181714', color: '#f5f0e8',
    iconColor: icon === 'success' ? '#4ade80' : '#fb7185',
    confirmButtonColor: '#c9a84c',
    confirmButtonText: icon === 'success' ? 'Great!' : 'OK',
    timer: icon === 'success' ? 3000 : undefined,
    timerProgressBar: icon === 'success',
  });
}

/* One modal for both doors: "+ Add Add-on" opens it empty and POSTs, the Update
   action opens it seeded and PATCHes. The fields are identical either way. */
function AddonModal({ addon, onClose, onSaved }) {
  const isEdit = !!addon;
  const [form, setForm] = useState(() => ({
    name: (addon && addon.name) || '',
    price: addon ? String(addon.price) : '',
    quantity: addon ? String(addon.quantity) : '',
    img: (addon && addon.img) || '',
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
    const nextErrors = validateAddonForm(form);
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length) return;

    setSaving(true);
    // addon.dbId is the hotel_addons primary key; addon.id is the front-end's "db-N".
    const url = isEdit ? (CONFIG.storeUrl + '/' + addon.dbId) : CONFIG.storeUrl;
    fetch(url, {
      method: isEdit ? 'PATCH' : 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({
        name: String(form.name).trim(),
        price: parseInt(String(form.price).replace(/,/g, ''), 10),
        quantity: parseInt(String(form.quantity).replace(/,/g, ''), 10),
        // Handed back as-is when untouched: the server collapses an existing
        // storage path to the one it already holds rather than re-uploading.
        image: form.img || '',
      }),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        if (data.item && typeof onSaved === 'function') onSaved(data.item);
        onClose();
        swal('success', isEdit ? 'Add-on Updated!' : 'Add-on Added!', data.item.name + ' has been saved.');
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Failed to save. Please try again.';
        if (window.Swal) swal('error', 'Error', msg);
        else setErrors({ name: msg });
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
          <img src={form.img || addonImg(addon || { name: form.name })} alt={form.name || 'Add-on'} />
          <button type="button" className="room-modal-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div style={{ padding: '1.5rem' }}>
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
            {isEdit ? 'Update Add-on' : 'New Add-on'}
          </p>
          <h2 className="font-display" style={{ fontSize: '1.5rem', marginBottom: '1.1rem', color: 'var(--fg)' }}>
            {isEdit ? addon.name : 'Add an Add-on'}
          </h2>

          <form onSubmit={handleSubmit} className="rm-form-grid" noValidate>
            <div>
              <label style={fieldLabel}>Name *</label>
              <input
                type="text" className="booking-input" value={form.name}
                placeholder="Folding Bed"
                onChange={e => update('name', e.target.value)}
                style={errors.name ? { borderColor: '#f43f5e' } : undefined}
              />
              {errorText('name')}
            </div>

            <div className="rm-form-row">
              <div>
                <label style={fieldLabel}>Quantity *</label>
                <input
                  type="number" min="0" className="booking-input" value={form.quantity}
                  placeholder="10"
                  onChange={e => update('quantity', e.target.value)}
                  style={errors.quantity ? { borderColor: '#f43f5e' } : undefined}
                />
                {errorText('quantity')}
              </div>
              <div>
                <label style={fieldLabel}>Price *</label>
                <input
                  type="number" min="0" className="booking-input" value={form.price}
                  placeholder="350"
                  onChange={e => update('price', e.target.value)}
                  style={errors.price ? { borderColor: '#f43f5e' } : undefined}
                />
                {errorText('price')}
              </div>
            </div>

            <p style={{ margin: '-0.35rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem', lineHeight: 1.5 }}>
              Quantity is how many the hotel owns. What is out with guests comes back on its
              own when they check out.
            </p>

            <div>
              <label style={fieldLabel}>Photo</label>
              <div
                onClick={() => pickImageFile(url => { if (url) update('img', url); })}
                style={{ border: '1.5px dashed var(--border)', borderRadius: 8, cursor: 'pointer', overflow: 'hidden' }}
              >
                {form.img ? (
                  <img src={form.img} alt="Add-on preview" style={{ width: '100%', height: 130, objectFit: 'cover', display: 'block' }} />
                ) : (
                  <div style={{ height: 92, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: '0.4rem', color: 'var(--fg-muted)' }}>
                    <i className="fa-solid fa-cloud-arrow-up" style={{ fontSize: '1.4rem', color: 'var(--accent)', opacity: 0.7 }}></i>
                    <span style={{ fontSize: '0.75rem' }}>Click to upload image</span>
                  </div>
                )}
              </div>
              {form.img && (
                <button
                  type="button"
                  onClick={() => update('img', '')}
                  style={{ background: 'none', border: 'none', color: 'var(--fg-muted)', cursor: 'pointer', fontSize: '0.72rem', padding: '0.4rem 0 0', fontFamily: 'Outfit, sans-serif' }}
                >
                  Remove image
                </button>
              )}
            </div>

            <button type="submit" className="btn-primary" disabled={saving} style={{ justifyContent: 'center' }}>
              {saving ? 'Saving…' : (isEdit ? 'Save Changes' : 'Add Add-on')}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

function AddonsPanel({ addons, loading, canManage, onSaved }) {
  const [page, setPage] = useState(1);
  const [editing, setEditing] = useState(null);   // an addon row, or 'new'
  const totalPages = Math.max(1, Math.ceil(addons.length / PER_PAGE));
  // Deleting or filtering can leave `page` past the end; clamp for the slice rather
  // than resetting it, so the number stays put while the list is only reordered.
  const safePage = Math.min(page, totalPages);
  const pageAddons = addons.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  const handleSaved = (item) => {
    setEditing(null);
    onSaved(item);
  };

  return (
    <div className="rm-row">
      <div className="rm-content">
        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '1rem', flexWrap: 'wrap' }}>
          <div className="rm-panel">
            <h3>Add-ons</h3>
            <p className="rm-panel-desc">
              What Housekeeping lends a guest on top of the room. Front Desk picks from this
              list while registering a guest, and what is out comes back at check-out.
            </p>
          </div>
          {canManage && (
            <button type="button" className="btn-outline" onClick={() => setEditing('new')}>
              <i className="fa-solid fa-plus" style={{ fontSize: '0.65rem' }}></i> Add Add-on
            </button>
          )}
        </div>

        {loading ? (
          <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem' }}>Loading add-ons…</p>
        ) : addons.length === 0 ? (
          <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem 1rem', textAlign: 'center', color: 'var(--fg-muted)' }}>
            <i className="fa-solid fa-cart-flatbed" style={{ fontSize: '1.6rem', opacity: 0.5, display: 'block', marginBottom: '0.6rem' }}></i>
            <p style={{ margin: 0, fontSize: '0.82rem' }}>No add-ons yet.</p>
          </div>
        ) : (
          <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
            <table className="rm-table">
              <thead>
                <tr>
                  <th style={{ width: 92 }}>Image</th>
                  <th>Name</th>
                  <th>Quantity</th>
                  <th>Price</th>
                  <th>Status</th>
                  <th style={{ width: 110 }}>Action</th>
                </tr>
              </thead>
              <tbody>
                {pageAddons.map(addon => (
                  <tr key={addon.id}>
                    <td>
                      <img
                        src={addonImg(addon)}
                        alt={addon.name}
                        style={{ width: 72, height: 52, objectFit: 'cover', borderRadius: 6, display: 'block' }}
                      />
                    </td>
                    <td style={{ color: 'var(--fg)', fontWeight: 600 }}>{addon.name}</td>
                    <td>
                      <span style={{ color: 'var(--fg)', fontWeight: 600 }}>{addon.available}</span>
                      <span style={{ opacity: 0.7 }}> of {addon.quantity}</span>
                      {addon.reserved > 0 && (
                        <div style={{ fontSize: '0.68rem', opacity: 0.7, marginTop: 2 }}>
                          {addon.reserved} with guests
                        </div>
                      )}
                    </td>
                    <td>{peso(addon.price)}</td>
                    <td>
                      <span className={'addon-badge ' + (addon.available > 0 ? 'is-available' : 'is-out')}>
                        {addon.status}
                      </span>
                    </td>
                    <td>
                      {canManage ? (
                        <button
                          type="button"
                          className="btn-outline"
                          style={{ fontSize: '0.68rem', padding: '0.4rem 0.8rem' }}
                          onClick={() => setEditing(addon)}
                        >
                          <i className="fa-solid fa-pen" style={{ fontSize: '0.65rem' }}></i> Update
                        </button>
                      ) : (
                        <span style={{ opacity: 0.5, fontSize: '0.72rem' }}>&mdash;</span>
                      )}
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
              Showing {(safePage - 1) * PER_PAGE + 1}&ndash;{Math.min(safePage * PER_PAGE, addons.length)} of {addons.length}
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
      </div>

      {editing && (
        <AddonModal
          addon={editing === 'new' ? null : editing}
          onClose={() => setEditing(null)}
          onSaved={handleSaved}
        />
      )}
    </div>
  );
}

function App() {
  const [addons, setAddons] = useState([]);
  const [canManage, setCanManage] = useState(false);
  const [loading, setLoading] = useState(true);
  // A poll landing mid-save would overwrite the row the user just changed with the
  // list as it was before. Fetches stand down while a write is in flight.
  const pendingWrites = useRef(0);

  const fetchAddons = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(CONFIG.indexUrl, {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(data => {
        setAddons(data.items || []);
        setCanManage(!!data.can_manage);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    fetchAddons();
    const timer = setInterval(fetchAddons, 8000);
    const onFocus = () => fetchAddons();
    window.addEventListener('focus', onFocus);
    return () => { clearInterval(timer); window.removeEventListener('focus', onFocus); };
  }, [fetchAddons]);

  /* Splice the saved row in rather than refetching: the response already carries the
     recomputed availability, and a refetch here would race the poll. */
  const handleSaved = useCallback((item) => {
    setAddons(prev => {
      const exists = prev.some(a => a.dbId === item.dbId);
      return exists ? prev.map(a => (a.dbId === item.dbId ? item : a)) : prev.concat([item]);
    });
  }, []);

  return (
    <div style={{ padding: '1.5rem' }}>
      <AddonsPanel
        addons={addons}
        loading={loading}
        canManage={canManage}
        onSaved={handleSaved}
      />
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
