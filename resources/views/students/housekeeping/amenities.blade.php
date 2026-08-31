@extends('students.builder.ops-shell')

@php $backRoute = 'students.housekeeping'; @endphp

@section('page-title', 'Amenities')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: 'Outfit', sans-serif; }
  .font-display { font-family: 'Playfair Display', serif; }
  .amenity-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent; white-space: nowrap;
    display: inline-block;
  }
  .amenity-badge.is-available   { background: rgba(74,222,128,0.16); color: #4ade80; border-color: rgba(74,222,128,0.35); }
  .amenity-badge.is-closed      { background: rgba(251,191,36,0.14); color: #fbbf24; border-color: rgba(251,191,36,0.35); }
  .amenity-badge.is-maintenance { background: rgba(244,63,94,0.14);  color: #fb7185; border-color: rgba(244,63,94,0.35); }
  /* The repair strip under a broken facility — amber while Maintenance holds it,
     emerald once it is back and only waiting on a housekeeper to look. */
  .repair-strip {
    margin-top: 0.5rem; padding: 0.6rem 0.75rem; border-radius: 8px;
    border: 1px solid var(--border); background: rgba(255,255,255,0.02);
    font-size: 0.72rem; line-height: 1.5; white-space: normal; max-width: 340px;
  }
  .repair-strip.is-waiting { border-color: rgba(251,191,36,0.3); }
  .repair-strip.is-ready   { border-color: rgba(74,222,128,0.3); }
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
  .btn-outline.is-alert { color: #fb7185; border-color: rgba(244,63,94,0.55); }
  .btn-outline.is-alert:hover { background: #fb7185; color: var(--bg); }
  .btn-outline.is-ready { color: #4ade80; border-color: rgba(74,222,128,0.55); }
  .btn-outline.is-ready:hover { background: #4ade80; color: var(--bg); }
  .booking-input {
    background: rgba(255,255,255,0.03); border: 1px solid var(--border);
    border-radius: 6px; padding: 0.7rem 0.9rem; color: var(--fg);
    font-family: 'Outfit', sans-serif; font-size: 0.85rem;
    outline: none; transition: border-color 0.2s; width: 100%;
  }
  .booking-input:focus { border-color: var(--accent); }
  .booking-input::placeholder { color: var(--fg-muted); opacity: 0.5; }
  textarea.booking-input { resize: vertical; min-height: 84px; line-height: 1.5; }
  /* The picker paints its own icon white-on-white in Chrome's dark form controls. */
  input[type="time"].booking-input::-webkit-calendar-picker-indicator { filter: invert(1); opacity: 0.6; }
  select.booking-input { appearance: none; cursor: pointer; }
  select.booking-input option { background: var(--card); color: var(--fg); }
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

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Additive only — nothing above this block is touched, so a Template 1
     team (or one that hasn't chosen a template yet) renders unchanged. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
  }
  :root[data-ops-theme="2"] #opsContentWrap { font-family: 'DM Sans', sans-serif; }
  :root[data-ops-theme="2"] .font-display { font-family: 'Cormorant Garamond', serif; }
  :root[data-ops-theme="2"] select.booking-input { color-scheme: light; }
  :root[data-ops-theme="2"] .booking-input { background: rgba(27,67,50,0.03); }
  :root[data-ops-theme="2"] input[type="time"].booking-input::-webkit-calendar-picker-indicator { filter: none; }
  :root[data-ops-theme="2"] .amenity-badge.is-available   { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
  :root[data-ops-theme="2"] .amenity-badge.is-closed      { background: #fef3c7; color: #b45309; border-color: #fde68a; }
  :root[data-ops-theme="2"] .amenity-badge.is-maintenance { background: #ffe4e6; color: #be123c; border-color: #fecdd3; }
  :root[data-ops-theme="2"] .repair-strip { background: #faf8f5; }
  :root[data-ops-theme="2"] .rm-row { box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_AMENITIES = {
    backUrl: @json(route($backRoute)),
    indexUrl: @json(route('students.hotel.amenities.index')),
    storeUrl: @json(route('students.hotel.amenities.store')),
    statuses: @json(\App\Models\HotelAmenity::STATUSES),
    accessTypes: @json(\App\Models\HotelAmenity::ACCESS_TYPES),
    accessLabels: @json(\App\Models\HotelAmenity::ACCESS_LABELS),
    // Same list the Front Desk complaint form offers, so a repair request lands in
    // Maintenance's queue under a category they already sort by.
    categories: @json(array_keys(\App\Models\HotelComplaint::CATEGORY_DEPARTMENTS)),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef } = React;

const PER_PAGE = 5;
const IMAGE_MAX_DIMENSION = 1280;
const IMAGE_MAX_BYTES = 600 * 1024;

const CONFIG = window.HMS_AMENITIES || {};
const STATUSES = CONFIG.statuses || ['Available', 'Temporarily Closed', 'Under Maintenance'];
const ACCESS_TYPES = CONFIG.accessTypes || ['open', 'registered', 'appointment', 'event'];
const ACCESS_LABELS = CONFIG.accessLabels || {};
/* What each access type means, in the words of the person who has to pick one. */
const ACCESS_HINTS = {
  open: 'Guests walk in during opening hours. Nothing for Front Desk to record.',
  registered: 'Front Desk signs each guest in and out, so you always know who is inside.',
  appointment: 'Booked ahead for a slot against a named service, like a spa treatment.',
  event: 'Booked for a date as an event, with a package, catering and a bill.',
};
const CATEGORIES = CONFIG.categories || ['Furniture / Fixtures'];
const DEFAULT_CATEGORY = CATEGORIES.indexOf('Furniture / Fixtures') >= 0 ? 'Furniture / Fixtures' : CATEGORIES[0];

function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : '';
}

/* No stored image means a stable stand-in rather than an empty box — the same
   deterministic seed the add-ons, rooms and menu screens use. */
function amenityImg(amenity) {
  if (amenity && amenity.img) return amenity.img;
  const seed = encodeURIComponent((amenity && (amenity.id || amenity.name)) || 'amenity');
  return 'https://picsum.photos/seed/amenity-' + seed + '/800/600.jpg';
}

function statusClass(status) {
  if (status === 'Available') return 'is-available';
  if (status === 'Temporarily Closed') return 'is-closed';
  return 'is-maintenance';
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

function validateAmenityForm(form) {
  const errors = {};
  if (!String(form.name || '').trim()) errors.name = 'Name is required.';
  if (STATUSES.indexOf(form.status) < 0) errors.status = 'Choose a status.';
  // Both or neither: half a pair reads as a mistake on the public page.
  if (form.opensAt && !form.closesAt) errors.closesAt = 'Add a closing time too.';
  if (form.closesAt && !form.opensAt) errors.opensAt = 'Add an opening time too.';
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

const fieldLabel = {
  fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase',
  color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem',
};

/* One modal for both doors: "+ Add Amenity" opens it empty and POSTs, the Update
   action opens it seeded and PATCHes. The fields are identical either way. */
function AmenityModal({ amenity, onClose, onSaved }) {
  const isEdit = !!amenity;
  const [form, setForm] = useState(() => ({
    name: (amenity && amenity.name) || '',
    description: (amenity && amenity.description) || '',
    location: (amenity && amenity.location) || '',
    opensAt: (amenity && amenity.opensAt) || '',
    closesAt: (amenity && amenity.closesAt) || '',
    status: (amenity && amenity.status) || 'Available',
    accessType: (amenity && amenity.accessType) || 'open',
    rate: amenity && amenity.rate ? String(amenity.rate) : '',
    setupFee: amenity && amenity.setupFee ? String(amenity.setupFee) : '',
    capacity: amenity && amenity.capacity !== null && amenity.capacity !== undefined ? String(amenity.capacity) : '',
    img: (amenity && amenity.img) || '',
  }));
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);

  // Maintenance still has this one. Letting the picker offer Available here would
  // only produce a 422 from the update route — the Verify button is the way back.
  const lockedFromAvailable = !!(amenity && amenity.repairInProgress);

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
    const nextErrors = validateAmenityForm(form);
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length) return;

    setSaving(true);
    // amenity.dbId is the hotel_amenities primary key; amenity.id is the front-end's "db-N".
    const url = isEdit ? (CONFIG.storeUrl + '/' + amenity.dbId) : CONFIG.storeUrl;
    fetch(url, {
      method: isEdit ? 'PATCH' : 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({
        name: String(form.name).trim(),
        description: String(form.description || '').trim(),
        location: String(form.location || '').trim(),
        opens_at: form.opensAt || null,
        closes_at: form.closesAt || null,
        status: form.status,
        access_type: form.accessType,
        // Blank means zero for a fee and "no limit" for capacity — which is why capacity
        // goes back as null and the fees go back as 0.
        rate: parseInt(form.rate, 10) || 0,
        setup_fee: parseInt(form.setupFee, 10) || 0,
        capacity: String(form.capacity).trim() === '' ? null : (parseInt(form.capacity, 10) || 0),
        // Handed back as-is when untouched: the server collapses an existing
        // storage path to the one it already holds rather than re-uploading.
        image: form.img || '',
      }),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        if (data.item && typeof onSaved === 'function') onSaved(data.item);
        onClose();
        swal('success', isEdit ? 'Amenity Updated!' : 'Amenity Added!', data.item.name + ' has been saved.');
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
          <img src={form.img || amenityImg(amenity || { name: form.name })} alt={form.name || 'Amenity'} />
          <button type="button" className="room-modal-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>
        <div style={{ padding: '1.5rem' }}>
          <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
            {isEdit ? 'Update Amenity' : 'New Amenity'}
          </p>
          <h2 className="font-display" style={{ fontSize: '1.5rem', marginBottom: '1.1rem', color: 'var(--fg)' }}>
            {isEdit ? amenity.name : 'Add an Amenity'}
          </h2>

          <form onSubmit={handleSubmit} className="rm-form-grid" noValidate>
            <div>
              <label style={fieldLabel}>Name *</label>
              <input
                type="text" className="booking-input" value={form.name}
                placeholder="Swimming Pool"
                onChange={e => update('name', e.target.value)}
                style={errors.name ? { borderColor: '#f43f5e' } : undefined}
              />
              {errorText('name')}
            </div>

            <div>
              <label style={fieldLabel}>Description</label>
              <textarea
                className="booking-input" value={form.description}
                placeholder="Outdoor infinity pool with sun loungers and poolside towels."
                onChange={e => update('description', e.target.value)}
              />
            </div>

            <div>
              <label style={fieldLabel}>Location</label>
              <input
                type="text" className="booking-input" value={form.location}
                placeholder="Rooftop, 8th Floor"
                onChange={e => update('location', e.target.value)}
              />
            </div>

            <div className="rm-form-row">
              <div>
                <label style={fieldLabel}>Opening Time</label>
                <input
                  type="time" className="booking-input" value={form.opensAt}
                  onChange={e => update('opensAt', e.target.value)}
                  style={errors.opensAt ? { borderColor: '#f43f5e' } : undefined}
                />
                {errorText('opensAt')}
              </div>
              <div>
                <label style={fieldLabel}>Closing Time</label>
                <input
                  type="time" className="booking-input" value={form.closesAt}
                  onChange={e => update('closesAt', e.target.value)}
                  style={errors.closesAt ? { borderColor: '#f43f5e' } : undefined}
                />
                {errorText('closesAt')}
              </div>
            </div>

            <div>
              <label style={fieldLabel}>Status *</label>
              <select
                className="booking-input" value={form.status}
                onChange={e => update('status', e.target.value)}
                style={errors.status ? { borderColor: '#f43f5e' } : undefined}
              >
                {STATUSES.map(status => (
                  <option
                    key={status}
                    value={status}
                    disabled={lockedFromAvailable && status === 'Available'}
                  >
                    {status}
                  </option>
                ))}
              </select>
              {errorText('status')}
              <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem', lineHeight: 1.5 }}>
                {lockedFromAvailable
                  ? 'Maintenance is still working on this one. Verify the repair from the list to reopen it.'
                  : 'Guests see this on the Amenities page of your site. Under Maintenance lets you send a repair request to Maintenance.'}
              </p>
            </div>

            {/* Access type decides what Front Desk can do with this facility. It is the
                one field on this form the other departments read. */}
            <div>
              <label style={fieldLabel}>Access Type *</label>
              <select
                className="booking-input" value={form.accessType}
                onChange={e => update('accessType', e.target.value)}
              >
                {ACCESS_TYPES.map(type => (
                  <option key={type} value={type}>{ACCESS_LABELS[type] || type}</option>
                ))}
              </select>
              <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem', lineHeight: 1.5 }}>
                {ACCESS_HINTS[form.accessType] || ''}
              </p>
            </div>

            {/* Only asked for where it means something: a capacity on the playground and
                an event fee on the pool are questions with no useful answer. */}
            {(form.accessType === 'registered' || form.accessType === 'event') && (
              <div>
                <label style={fieldLabel}>Capacity</label>
                <input
                  type="number" min="0" max="9999" className="booking-input" value={form.capacity}
                  placeholder="Leave blank for no limit"
                  onChange={e => update('capacity', e.target.value)}
                />
                <p style={{ margin: '0.4rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem', lineHeight: 1.5 }}>
                  How many people fit. Front Desk cannot sign in a party that would go over it.
                </p>
              </div>
            )}

            {form.accessType === 'event' && (
              <div className="rm-form-row">
                <div>
                  <label style={fieldLabel}>Event Rate (₱)</label>
                  <input
                    type="number" min="0" className="booking-input" value={form.rate}
                    placeholder="5000"
                    onChange={e => update('rate', e.target.value)}
                  />
                </div>
                <div>
                  <label style={fieldLabel}>Setup Fee (₱)</label>
                  <input
                    type="number" min="0" className="booking-input" value={form.setupFee}
                    placeholder="1500"
                    onChange={e => update('setupFee', e.target.value)}
                  />
                </div>
              </div>
            )}

            <div>
              <label style={fieldLabel}>Photo</label>
              <div
                onClick={() => pickImageFile(url => { if (url) update('img', url); })}
                style={{ border: '1.5px dashed var(--border)', borderRadius: 8, cursor: 'pointer', overflow: 'hidden' }}
              >
                {form.img ? (
                  <img src={form.img} alt="Amenity preview" style={{ width: '100%', height: 130, objectFit: 'cover', display: 'block' }} />
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
              {saving ? 'Saving…' : (isEdit ? 'Save Changes' : 'Add Amenity')}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

/* Hands the facility to Maintenance. Files a complaint on their board rather than
   anything new, so it lands in the queue they already watch. */
function RepairModal({ amenity, onClose, onSaved }) {
  const [category, setCategory] = useState(DEFAULT_CATEGORY);
  const [details, setDetails] = useState('');
  const [error, setError] = useState(null);
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!details.trim()) { setError('Describe what needs repairing.'); return; }

    setSaving(true);
    fetch(CONFIG.storeUrl + '/' + amenity.dbId + '/repair-request', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({ category: category, details: details.trim() }),
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        if (data.item && typeof onSaved === 'function') onSaved(data.item);
        onClose();
        swal('success', 'Sent to Maintenance', amenity.name + ' is now with Maintenance. You will verify the repair when they finish.');
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Failed to send. Please try again.';
        if (window.Swal) swal('error', 'Error', msg);
        else setError(msg);
      })
      .finally(() => setSaving(false));
  };

  return (
    <div className="room-modal-overlay" onClick={onClose} role="dialog" aria-modal="true">
      <div className="room-modal" style={{ maxWidth: 440 }} onClick={e => e.stopPropagation()}>
        <div style={{ padding: '1.5rem' }}>
          <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '1rem' }}>
            <div>
              <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', marginBottom: '0.4rem' }}>
                Repair Request
              </p>
              <h2 className="font-display" style={{ fontSize: '1.5rem', margin: '0 0 0.35rem', color: 'var(--fg)' }}>
                {amenity.name}
              </h2>
              {amenity.location && (
                <p style={{ margin: 0, color: 'var(--fg-muted)', fontSize: '0.78rem' }}>
                  <i className="fa-solid fa-location-dot" style={{ fontSize: '0.7rem', marginRight: '0.35rem' }}></i>
                  {amenity.location}
                </p>
              )}
            </div>
            <button
              type="button" onClick={onClose} aria-label="Close"
              style={{ background: 'none', border: 'none', color: 'var(--fg-muted)', cursor: 'pointer', fontSize: '1rem' }}
            >
              <i className="fa-solid fa-xmark"></i>
            </button>
          </div>

          <form onSubmit={handleSubmit} className="rm-form-grid" style={{ marginTop: '1.2rem' }} noValidate>
            <div>
              <label style={fieldLabel}>Category</label>
              <select className="booking-input" value={category} onChange={e => setCategory(e.target.value)}>
                {CATEGORIES.map(item => <option key={item} value={item}>{item}</option>)}
              </select>
            </div>

            <div>
              <label style={fieldLabel}>What needs repairing? *</label>
              <textarea
                className="booking-input" value={details}
                placeholder="The pool filter pump has stopped and the water is not circulating."
                onChange={e => { setDetails(e.target.value); if (error) setError(null); }}
                style={error ? { borderColor: '#f43f5e' } : undefined}
              />
              {error && <p style={{ margin: '0.35rem 0 0', color: '#fb7185', fontSize: '0.72rem' }}>{error}</p>}
            </div>

            <p style={{ margin: '-0.35rem 0 0', color: 'var(--fg-muted)', fontSize: '0.72rem', lineHeight: 1.5 }}>
              This goes to Maintenance as a complaint on their board. {amenity.name} stays Under
              Maintenance until you verify their work.
            </p>

            <button type="submit" className="btn-primary" disabled={saving} style={{ justifyContent: 'center' }}>
              {saving ? 'Sending…' : 'Send to Maintenance'}
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

/* What a broken facility shows under its status: nothing to do yet, waiting on
   Maintenance, or back and needing a look. */
function RepairCell({ amenity, canManage, onRequest, onVerify, verifying }) {
  if (amenity.status !== 'Under Maintenance') {
    return <span style={{ opacity: 0.5, fontSize: '0.72rem' }}>&mdash;</span>;
  }

  if (amenity.repairInProgress) {
    return (
      <div className="repair-strip is-waiting">
        <strong style={{ color: '#fbbf24' }}>
          <i className="fa-solid fa-screwdriver-wrench" style={{ marginRight: '0.35rem' }}></i>
          Maintenance is working on this
        </strong>
        <div style={{ marginTop: '0.25rem' }}>
          {amenity.repairCategory} &middot; {amenity.repairStatus}
          {amenity.repairHandledBy ? ' · ' + amenity.repairHandledBy : ''}
        </div>
      </div>
    );
  }

  if (amenity.awaitingVerification) {
    return (
      <div className="repair-strip is-ready">
        <strong style={{ color: '#4ade80' }}>
          <i className="fa-solid fa-circle-check" style={{ marginRight: '0.35rem' }}></i>
          Repair finished &mdash; verify it
        </strong>
        {amenity.repairNote && (
          <div style={{ marginTop: '0.25rem', fontStyle: 'italic' }}>&ldquo;{amenity.repairNote}&rdquo;</div>
        )}
        {canManage && (
          <button
            type="button" className="btn-outline is-ready"
            style={{ fontSize: '0.68rem', padding: '0.4rem 0.8rem', marginTop: '0.5rem' }}
            disabled={verifying}
            onClick={() => onVerify(amenity)}
          >
            <i className="fa-solid fa-clipboard-check" style={{ fontSize: '0.65rem' }}></i>
            {verifying ? 'Verifying…' : 'Verify & reopen'}
          </button>
        )}
      </div>
    );
  }

  // Broken, and nobody has told Maintenance yet.
  return (
    <div className="repair-strip">
      <span>No repair requested yet.</span>
      {canManage && (
        <button
          type="button" className="btn-outline is-alert"
          style={{ fontSize: '0.68rem', padding: '0.4rem 0.8rem', marginTop: '0.5rem' }}
          onClick={() => onRequest(amenity)}
        >
          <i className="fa-solid fa-screwdriver-wrench" style={{ fontSize: '0.65rem' }}></i> Send to Maintenance
        </button>
      )}
    </div>
  );
}

function AmenitiesPanel({ amenities, loading, canManage, onSaved }) {
  const [page, setPage] = useState(1);
  const [editing, setEditing] = useState(null);     // an amenity row, or 'new'
  const [repairing, setRepairing] = useState(null); // an amenity row
  const [verifyingId, setVerifyingId] = useState(null);

  const totalPages = Math.max(1, Math.ceil(amenities.length / PER_PAGE));
  // The list only ever grows or reorders; clamp for the slice rather than resetting
  // the page, so the number stays put while a poll lands.
  const safePage = Math.min(page, totalPages);
  const pageAmenities = amenities.slice((safePage - 1) * PER_PAGE, safePage * PER_PAGE);

  const handleSaved = (item) => {
    setEditing(null);
    setRepairing(null);
    onSaved(item);
  };

  const handleVerify = (amenity) => {
    setVerifyingId(amenity.dbId);
    fetch(CONFIG.storeUrl + '/' + amenity.dbId + '/verify', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
    })
      .then(r => (r.ok ? r.json() : r.json().then(err => Promise.reject(err))))
      .then(data => {
        if (data.item) onSaved(data.item);
        swal('success', 'Verified!', amenity.name + ' is open to guests again.');
      })
      .catch(err => swal('error', 'Error', (err && err.message) ? err.message : 'Failed to verify. Please try again.'))
      .finally(() => setVerifyingId(null));
  };

  return (
    <div className="rm-row">
      <div className="rm-content">
        <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: '1rem', flexWrap: 'wrap' }}>
          <div className="rm-panel">
            <h3>Amenities</h3>
            <p className="rm-panel-desc">
              The facilities your hotel offers. These appear on the Amenities page of your
              team&rsquo;s site exactly as you set them here &mdash; including whether each one is
              open, closed for now, or under repair.
            </p>
          </div>
          {canManage && (
            <button type="button" className="btn-outline" onClick={() => setEditing('new')}>
              <i className="fa-solid fa-plus" style={{ fontSize: '0.65rem' }}></i> Add Amenity
            </button>
          )}
        </div>

        {loading ? (
          <p style={{ color: 'var(--fg-muted)', fontSize: '0.82rem' }}>Loading amenities…</p>
        ) : amenities.length === 0 ? (
          <div style={{ border: '1px solid var(--border)', borderRadius: 10, padding: '2.5rem 1rem', textAlign: 'center', color: 'var(--fg-muted)' }}>
            <i className="fa-solid fa-person-swimming" style={{ fontSize: '1.6rem', opacity: 0.5, display: 'block', marginBottom: '0.6rem' }}></i>
            <p style={{ margin: 0, fontSize: '0.82rem' }}>No amenities yet.</p>
          </div>
        ) : (
          <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
            <table className="rm-table">
              <thead>
                <tr>
                  <th style={{ width: 92 }}>Image</th>
                  <th>Name</th>
                  <th>Location</th>
                  <th>Hours</th>
                  <th>Status</th>
                  <th>Repair</th>
                  <th style={{ width: 110 }}>Action</th>
                </tr>
              </thead>
              <tbody>
                {pageAmenities.map(amenity => (
                  <tr key={amenity.id}>
                    <td>
                      <img
                        src={amenityImg(amenity)}
                        alt={amenity.name}
                        style={{ width: 72, height: 52, objectFit: 'cover', borderRadius: 6, display: 'block' }}
                      />
                    </td>
                    <td style={{ color: 'var(--fg)', fontWeight: 600 }}>
                      {amenity.name}
                      {/* What Front Desk can do with it — the one field on this screen the
                          other departments read. */}
                      <div style={{ fontSize: '0.62rem', fontWeight: 600, letterSpacing: '0.08em', textTransform: 'uppercase', color: 'var(--accent)', marginTop: 3 }}>
                        {amenity.accessLabel}
                        {amenity.capacity ? ' · cap ' + amenity.capacity : ''}
                      </div>
                      {amenity.description && (
                        <div style={{ fontSize: '0.68rem', fontWeight: 400, opacity: 0.7, marginTop: 2, whiteSpace: 'normal', maxWidth: 260, lineHeight: 1.45 }}>
                          {amenity.description}
                        </div>
                      )}
                    </td>
                    <td>{amenity.location || <span style={{ opacity: 0.5 }}>&mdash;</span>}</td>
                    <td>{amenity.hours || <span style={{ opacity: 0.5 }}>&mdash;</span>}</td>
                    <td>
                      <span className={'amenity-badge ' + statusClass(amenity.status)}>
                        {amenity.status}
                      </span>
                    </td>
                    <td style={{ verticalAlign: 'top' }}>
                      <RepairCell
                        amenity={amenity}
                        canManage={canManage}
                        onRequest={setRepairing}
                        onVerify={handleVerify}
                        verifying={verifyingId === amenity.dbId}
                      />
                    </td>
                    <td style={{ verticalAlign: 'top' }}>
                      {canManage ? (
                        <button
                          type="button"
                          className="btn-outline"
                          style={{ fontSize: '0.68rem', padding: '0.4rem 0.8rem' }}
                          onClick={() => setEditing(amenity)}
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
              Showing {(safePage - 1) * PER_PAGE + 1}&ndash;{Math.min(safePage * PER_PAGE, amenities.length)} of {amenities.length}
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
        <AmenityModal
          amenity={editing === 'new' ? null : editing}
          onClose={() => setEditing(null)}
          onSaved={handleSaved}
        />
      )}

      {repairing && (
        <RepairModal
          amenity={repairing}
          onClose={() => setRepairing(null)}
          onSaved={handleSaved}
        />
      )}
    </div>
  );
}

function App() {
  const [amenities, setAmenities] = useState([]);
  const [canManage, setCanManage] = useState(false);
  const [loading, setLoading] = useState(true);
  // A poll landing mid-save would overwrite the row the user just changed with the
  // list as it was before. Fetches stand down while a write is in flight.
  const pendingWrites = useRef(0);

  const fetchAmenities = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(CONFIG.indexUrl, {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(data => {
        setAmenities(data.items || []);
        setCanManage(!!data.can_manage);
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  // Polled, not just loaded once: Maintenance closing a repair happens in their
  // session, and this list has to notice without anyone reloading the page.
  useEffect(() => {
    fetchAmenities();
    const timer = setInterval(fetchAmenities, 8000);
    const onFocus = () => fetchAmenities();
    window.addEventListener('focus', onFocus);
    return () => { clearInterval(timer); window.removeEventListener('focus', onFocus); };
  }, [fetchAmenities]);

  /* Splice the saved row in rather than refetching: the response already carries the
     recomputed repair state, and a refetch here would race the poll. */
  const handleSaved = useCallback((item) => {
    setAmenities(prev => {
      const exists = prev.some(a => a.dbId === item.dbId);
      return exists ? prev.map(a => (a.dbId === item.dbId ? item : a)) : prev.concat([item]);
    });
  }, []);

  return (
    <div style={{ padding: '1.5rem' }}>
      <AmenitiesPanel
        amenities={amenities}
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
