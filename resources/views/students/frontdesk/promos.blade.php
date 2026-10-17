@extends('students.builder.ops-shell')

@section('page-title', 'Promos & Packages')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }

  /* Template 2 (cream / forest green / DM Sans + Cormorant Garamond). Only the
     tokens change; every rule below reads them, as on the Add-ons page. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d; --warn: #b45309;
  }

  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }

  .ao {
    --ao-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --ao-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --ao-line: var(--border);
    --ao-ok: var(--success, #4ade80);
    --ao-warn: var(--warn, #f59e0b);
    --ao-bad: var(--danger, #fb7185);
    padding: 1.5rem 1.5rem 3rem;
    color: var(--fg);
  }

  .ao-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.4rem; }
  .ao-eyebrow { color: var(--accent); font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; margin: 0 0 0.5rem; }
  .ao-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .ao-lead { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 64ch; line-height: 1.5; }
  .ao-head-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

  .ao-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem;
    font: 600 0.88rem/1.15 var(--font-body, 'Outfit', sans-serif);
    padding: 0.8rem 1.15rem; border-radius: 0.625rem; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); background: transparent; color: var(--accent);
    transition: background 0.15s, transform 0.1s, filter 0.15s;
  }
  .ao-btn:hover { background: var(--ao-tint); }
  .ao-btn:active { transform: translateY(1px); }
  .ao-btn.is-solid { background: var(--accent); color: var(--bg); }
  .ao-btn.is-solid:hover { filter: brightness(1.08); }
  .ao-btn.is-quiet { border-color: var(--ao-line); color: var(--fg-muted); }
  .ao-btn.is-quiet:hover { color: var(--fg); background: var(--ao-soft); }
  .ao-btn.is-danger { border-color: color-mix(in srgb, var(--ao-bad) 45%, transparent); color: var(--ao-bad); }
  .ao-btn.is-danger:hover { background: color-mix(in srgb, var(--ao-bad) 10%, transparent); }
  .ao-btn.is-small { padding: 0.6rem 0.8rem; font-size: 0.8rem; }
  .ao-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; filter: none; }
  .ao-btn:focus-visible, .ao-photo-pick:focus-visible, .ao-close:focus-visible, .ao-seg button:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  .ao-banner { display: flex; gap: 0.6rem; align-items: flex-start; margin: 0 0 1.25rem; padding: 0.85rem 1rem; border-radius: 0.875rem; border: 1px solid color-mix(in srgb, var(--ao-warn) 40%, transparent); background: color-mix(in srgb, var(--ao-warn) 10%, transparent); font-size: 0.86rem; color: var(--fg); }
  .ao-banner i { color: var(--ao-warn); margin-top: 0.15rem; }

  .ao-panel { background: var(--card); border: 1px solid var(--ao-line); border-radius: 0.875rem; padding: 1.2rem 1.3rem 1.4rem; }
  .ao-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .ao-panel-head h2 { margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--fg); }
  .ao-panel-head p { margin: 0.2rem 0 0; font-size: 0.82rem; color: var(--fg-muted); }
  .ao-live { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--fg-muted); }
  .ao-live::before { content: ''; width: 0.4375rem; height: 0.4375rem; border-radius: 50%; background: var(--ao-ok); }

  .ao-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem; margin-bottom: 1.1rem; }
  .ao-stat { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 0.95rem; border-radius: 0.75rem; border: 1px solid var(--ao-line); background: var(--ao-soft); }
  .ao-stat-icon { flex: none; width: 2.375rem; height: 2.375rem; border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; }
  .ao-stat div b { display: block; font-size: 1.35rem; line-height: 1.1; font-variant-numeric: tabular-nums; color: var(--fg); }
  .ao-stat div span { display: block; font-size: 0.78rem; color: var(--fg-muted); }
  .tone-ok    { background: color-mix(in srgb, var(--ao-ok) 16%, transparent);   color: var(--ao-ok); }
  .tone-guest { background: var(--ao-tint); color: var(--accent); }
  .tone-warn  { background: color-mix(in srgb, var(--ao-warn) 16%, transparent); color: var(--ao-warn); }
  .tone-bad   { background: color-mix(in srgb, var(--ao-bad) 14%, transparent);  color: var(--ao-bad); }
  .tone-muted { background: var(--ao-soft); color: var(--fg-muted); }

  .ao-input {
    box-sizing: border-box; width: 100%;
    background: var(--ao-soft); border: 1px solid var(--ao-line);
    border-radius: 0.625rem; padding: 0.75rem 0.9rem; color: var(--fg);
    font: 400 0.9rem/1.4 var(--font-body, 'Outfit', sans-serif);
    outline: none; transition: border-color 0.15s;
  }
  .ao-input:focus { border-color: var(--accent); }
  .ao-input::placeholder { color: var(--fg-muted); opacity: 0.7; }
  .ao-input.has-error { border-color: var(--ao-bad); }
  textarea.ao-input { resize: vertical; min-height: 5.5rem; }
  :root:not([data-ops-theme="2"]) input[type="date"].ao-input { color-scheme: dark; }

  .ao-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(280px, 100%), 1fr)); gap: 1rem; }
  .ao-card { min-width: 0; border: 1px solid var(--ao-line); border-radius: 0.875rem; background: var(--ao-soft); overflow: hidden; display: flex; flex-direction: column; }
  .ao-card-img { position: relative; aspect-ratio: 16 / 9; background: var(--ao-soft); overflow: hidden; }
  .ao-card-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; }
  .ao-card-img .ao-pill { position: absolute; top: 0.625rem; left: 0.625rem; box-shadow: 0 2px 10px rgba(0,0,0,0.25); background: var(--card); }
  .ao-card-body { padding: 0.95rem 1.05rem 1.05rem; display: flex; flex-direction: column; gap: 0.7rem; flex: 1; }
  .ao-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
  .ao-name { margin: 0; font-size: 1.08rem; font-weight: 700; color: var(--fg); line-height: 1.25; overflow-wrap: anywhere; }
  .ao-price { text-align: right; flex: none; }
  .ao-price b { display: block; font-size: 1.05rem; color: var(--fg); font-variant-numeric: tabular-nums; }
  .ao-price s { display: block; font-size: 0.74rem; color: var(--fg-muted); }
  .ao-desc { margin: 0; font-size: 0.84rem; color: var(--fg-muted); line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; overflow-wrap: anywhere; }
  .ao-dates { display: flex; align-items: center; gap: 0.45rem; font-size: 0.8rem; color: var(--fg); }
  .ao-dates i { color: var(--accent); }
  .ao-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.76rem; font-weight: 600; white-space: nowrap; }
  .ao-card-foot { margin-top: auto; display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.5rem; }

  .ao-empty { border: 1.5px dashed var(--ao-line); border-radius: 0.875rem; padding: 2.4rem 1.5rem; text-align: center; }
  .ao-empty-icon { width: 3.5rem; height: 3.5rem; margin: 0 auto 0.9rem; border-radius: 1rem; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; background: var(--ao-tint); color: var(--accent); }
  .ao-empty h2 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .ao-empty p { margin: 0.4rem auto 0; max-width: 50ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }
  .ao-empty .ao-btn { margin-top: 1.1rem; }
  .ao-skel { height: 20rem; border-radius: 0.875rem; border: 1px solid var(--ao-line); background: linear-gradient(90deg, var(--ao-soft) 0%, color-mix(in srgb, var(--fg) 8%, transparent) 50%, var(--ao-soft) 100%); background-size: 200% 100%; animation: ao-shimmer 1.4s ease-in-out infinite; }
  @keyframes ao-shimmer { from { background-position: 100% 0; } to { background-position: -100% 0; } }
  @media (prefers-reduced-motion: reduce) { .ao-skel { animation: none; } }

  .ao-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; padding: 1.25rem; z-index: 200; }
  .ao-modal { box-sizing: border-box; background: var(--card); color: var(--fg); border: 1px solid var(--ao-line); border-radius: 0.875rem; width: 100%; max-width: 35rem; max-height: 92vh; overflow-y: auto; }
  .ao-modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.35rem 0; }
  .ao-modal-head h2 { margin: 0; font-size: 1.45rem; line-height: 1.2; color: var(--fg); }
  .ao-modal-head p { margin: 0.35rem 0 0; font-size: 0.84rem; color: var(--fg-muted); line-height: 1.45; }
  .ao-close { flex: none; width: 2.125rem; height: 2.125rem; border-radius: 0.625rem; border: 1px solid var(--ao-line); background: transparent; color: var(--fg-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; }
  .ao-close:hover { color: var(--fg); background: var(--ao-soft); }
  .ao-form { padding: 1.1rem 1.35rem 1.35rem; display: grid; gap: 1rem; }
  .ao-field { display: grid; gap: 0.4rem; align-content: start; }
  .ao-label { font-size: 0.86rem; font-weight: 600; color: var(--fg); }
  .ao-label em { font-style: normal; font-weight: 400; color: var(--fg-muted); }
  .ao-help { margin: 0; font-size: 0.76rem; color: var(--fg-muted); line-height: 1.45; }
  .ao-error { margin: 0; font-size: 0.78rem; color: var(--ao-bad); }
  .ao-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.85rem; }
  .ao-money { position: relative; }
  .ao-money span { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.9rem; pointer-events: none; }
  .ao-money .ao-input { padding-left: 1.8rem; }
  .ao-photo-pick { display: block; width: 100%; padding: 0; border: 1.5px dashed var(--ao-line); border-radius: 0.625rem; background: var(--ao-soft); cursor: pointer; overflow: hidden; color: var(--fg-muted); font: inherit; }
  .ao-photo-pick:hover { border-color: var(--accent); }
  .ao-photo-pick.has-error { border-color: var(--ao-bad); }
  .ao-photo-pick img { width: 100%; height: 10rem; object-fit: cover; display: block; }
  .ao-photo-empty { height: 6.875rem; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.45rem; font-size: 0.82rem; }
  .ao-photo-empty i { font-size: 1.4rem; color: var(--accent); }
  .ao-seg { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; }
  .ao-seg button { padding: 0.7rem; border-radius: 0.625rem; border: 1px solid var(--ao-line); background: var(--ao-soft); color: var(--fg-muted); font: 600 0.86rem/1 var(--font-body, 'Outfit', sans-serif); cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.45rem; }
  .ao-seg button[aria-pressed="true"] { border-color: var(--accent); background: var(--ao-tint); color: var(--fg); }
  .ao-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
  .ao-actions .ao-btn { flex: 1 1 auto; }

  @media (max-width: 960px) { .ao-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 560px) {
    .ao { padding: 1.1rem 1rem 2.5rem; }
    .ao-stats, .ao-row { grid-template-columns: 1fr; }
    .ao-head-actions, .ao-head-actions .ao-btn { width: 100%; }
  }
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_PROMOS = {
    indexUrl: @json(route('students.hotel.promos.index')),
    storeUrl: @json(route('students.hotel.promos.store')),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef, useId } = React;

const IMAGE_MAX_DIMENSION = 1280;
const IMAGE_MAX_BYTES = 600 * 1024;

const CONFIG = window.HMS_PROMOS || {};

function hmsCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : '';
}

function peso(value) {
  return '₱' + Number(value || 0).toLocaleString();
}

// Users are in Manila; the browser's own date is theirs.
function todayIso() {
  return new Date().toLocaleDateString('en-CA');
}

function niceDate(iso) {
  const d = new Date(iso + 'T00:00:00');
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
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
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  input.style.display = 'none';
  document.body.appendChild(input);
  input.addEventListener('change', function () {
    const file = input.files && input.files[0];
    if (!file) { input.remove(); return; }
    const reader = new FileReader();
    reader.onload = function () { compressImageDataUrl(String(reader.result || ''), onPicked); input.remove(); };
    reader.onerror = function () { input.remove(); };
    reader.readAsDataURL(file);
  });
  input.click();
}

function toInt(value) {
  const s = String(value == null ? '' : value).replace(/,/g, '').trim();
  return /^\d+$/.test(s) ? parseInt(s, 10) : NaN;
}

function validatePromoForm(form) {
  const errors = {};
  if (!form.name.trim()) errors.name = 'Type the name of the promo or package.';
  if (!form.description.trim()) errors.description = 'Say what the guest gets.';
  if (!form.img) errors.img = 'Choose a promo image.';
  const promo = toInt(form.promo_price);
  if (!Number.isFinite(promo)) errors.promo_price = 'Type the promo price in whole pesos.';
  if (String(form.original_price).trim() !== '') {
    const original = toInt(form.original_price);
    if (!Number.isFinite(original) || original < 1) errors.original_price = 'Type a price in whole pesos, or leave it blank.';
    else if (Number.isFinite(promo) && original <= promo) errors.original_price = 'Must be higher than the promo price.';
  }
  if (!form.start_date) errors.start_date = 'Choose the first day of the promo.';
  if (!form.end_date) errors.end_date = 'Choose the last day of the promo.';
  else if (form.start_date && form.end_date < form.start_date) errors.end_date = 'Cannot be before the start date.';
  return errors;
}

function themeColor(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

function swalTheme() {
  return { background: themeColor('--card', '#181714'), color: themeColor('--fg', '#f5f0e8'), confirmButtonColor: themeColor('--accent', '#c9a84c') };
}

function swal(icon, title, text) {
  if (!window.Swal) { if (icon === 'error') window.alert(text); return; }
  window.Swal.fire(Object.assign(swalTheme(), {
    icon, title, text,
    iconColor: icon === 'success' ? themeColor('--success', '#4ade80') : themeColor('--danger', '#fb7185'),
    confirmButtonText: 'OK',
    timer: icon === 'success' ? 3000 : undefined,
    timerProgressBar: icon === 'success',
  }));
}

function confirmDelete(name) {
  if (!window.Swal) return Promise.resolve(window.confirm('Delete ' + name + '? This cannot be undone.'));
  return window.Swal.fire(Object.assign(swalTheme(), {
    icon: 'warning',
    iconColor: themeColor('--danger', '#fb7185'),
    title: 'Delete this promo?',
    text: name + ' will be removed from the hotel website. This cannot be undone.',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    confirmButtonColor: themeColor('--danger', '#e11d48'),
    cancelButtonText: 'Keep it',
    focusCancel: true,
  })).then(r => !!r.isConfirmed);
}

// Laravel's 422 carries per-field errors; the first one is the useful message.
function errorMessage(err, fallback) {
  if (err && err.errors) {
    const first = Object.values(err.errors)[0];
    if (Array.isArray(first) && first[0]) return first[0];
  }
  return (err && err.message) || fallback;
}

function send(url, method, body) {
  return fetch(url, {
    method,
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
    body: body ? JSON.stringify(body) : undefined,
  }).then(r => (r.ok ? r.json() : r.json().catch(() => ({})).then(err => Promise.reject(err))));
}

const STATE_LOOK = {
  Running:   { tone: 'tone-ok',    icon: 'fa-circle-check', label: 'On the website' },
  Scheduled: { tone: 'tone-guest', icon: 'fa-clock',        label: 'Starts soon' },
  Expired:   { tone: 'tone-bad',   icon: 'fa-calendar-xmark', label: 'Expired' },
  Inactive:  { tone: 'tone-muted', icon: 'fa-eye-slash',    label: 'Inactive' },
};

function PromoModal({ promo, onClose, onSaved }) {
  const isEdit = !!promo;
  const [form, setForm] = useState(() => ({
    name: (promo && promo.name) || '',
    description: (promo && promo.description) || '',
    img: (promo && promo.img) || '',
    original_price: promo && promo.original_price ? String(promo.original_price) : '',
    promo_price: promo ? String(promo.promo_price) : '',
    start_date: (promo && promo.start_date) || todayIso(),
    end_date: (promo && promo.end_date) || '',
    status: (promo && promo.status) || 'active',
  }));
  const [errors, setErrors] = useState({});
  const [saving, setSaving] = useState(false);
  const ids = { name: useId(), desc: useId(), orig: useId(), price: useId(), start: useId(), end: useId(), title: useId() };
  const firstField = useRef(null);

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  useEffect(() => {
    if (firstField.current) firstField.current.focus();
    const onKey = (e) => { if (e.key === 'Escape') onClose(); };
    document.addEventListener('keydown', onKey);
    return () => document.removeEventListener('keydown', onKey);
  }, [onClose]);

  const handleSubmit = (e) => {
    e.preventDefault();
    const nextErrors = validatePromoForm(form);
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length) return;

    setSaving(true);
    send(isEdit ? CONFIG.storeUrl + '/' + promo.dbId : CONFIG.storeUrl, isEdit ? 'PATCH' : 'POST', {
      name: form.name.trim(),
      description: form.description.trim(),
      // An untouched image goes back as its stored URL, which the server keeps.
      image: form.img,
      original_price: String(form.original_price).trim() === '' ? null : toInt(form.original_price),
      promo_price: toInt(form.promo_price),
      start_date: form.start_date,
      end_date: form.end_date,
      status: form.status,
    })
      .then(data => {
        onSaved(data.item);
        onClose();
        swal('success', isEdit ? 'Changes saved' : 'Promo added', data.item.live
          ? data.item.name + ' is on the hotel website.'
          : data.item.name + ' is saved. It is not on the website while it is ' + data.item.state.toLowerCase() + '.');
      })
      .catch(err => swal('error', 'Not saved', errorMessage(err, 'Could not save. Please try again.')))
      .finally(() => setSaving(false));
  };

  const money = (key, id, label, optional, help, placeholder) => (
    <div className="ao-field">
      <label className="ao-label" htmlFor={id}>{label} {optional ? <em>(optional)</em> : null}</label>
      <div className="ao-money">
        <span>₱</span>
        <input id={id} type="number" min="0" step="1" inputMode="numeric" placeholder={placeholder}
          className={`ao-input ${errors[key] ? 'has-error' : ''}`} value={form[key]}
          onChange={e => update(key, e.target.value)} />
      </div>
      {errors[key] ? <p className="ao-error">{errors[key]}</p> : <p className="ao-help">{help}</p>}
    </div>
  );

  return (
    <div className="ao-overlay" onClick={onClose}>
      <div className="ao-modal" role="dialog" aria-modal="true" aria-labelledby={ids.title} onClick={e => e.stopPropagation()}>
        <div className="ao-modal-head">
          <div>
            <h2 id={ids.title} className="font-display">{isEdit ? `Edit ${promo.name}` : 'Add a promo or package'}</h2>
            <p>Active promos show in the Promos and Packages section of the hotel website until their end date.</p>
          </div>
          <button type="button" className="ao-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form onSubmit={handleSubmit} className="ao-form" noValidate>
          <div className="ao-field">
            <label className="ao-label" htmlFor={ids.name}>Promo / Package name</label>
            <input id={ids.name} ref={firstField} type="text" maxLength={120} placeholder="Example: Weekend Escape"
              className={`ao-input ${errors.name ? 'has-error' : ''}`} value={form.name}
              onChange={e => update('name', e.target.value)} />
            {errors.name ? <p className="ao-error">{errors.name}</p> : null}
          </div>

          <div className="ao-field">
            <label className="ao-label" htmlFor={ids.desc}>Description</label>
            <textarea id={ids.desc} maxLength={600} placeholder="Two nights in a Deluxe Room with breakfast for two."
              className={`ao-input ${errors.description ? 'has-error' : ''}`} value={form.description}
              onChange={e => update('description', e.target.value)} />
            {errors.description ? <p className="ao-error">{errors.description}</p> : null}
          </div>

          <div className="ao-field">
            <span className="ao-label">Promo image</span>
            <button type="button" className={`ao-photo-pick ${errors.img ? 'has-error' : ''}`}
              onClick={() => pickImageFile(url => { if (url) update('img', url); })}>
              {form.img ? (
                <img src={form.img} alt="Promo image" />
              ) : (
                <span className="ao-photo-empty"><i className="fa-solid fa-camera"></i>Click to choose an image</span>
              )}
            </button>
            {errors.img ? <p className="ao-error">{errors.img}</p> : <p className="ao-help">{form.img ? 'Click the image to replace it.' : 'This is the picture guests see on the website.'}</p>}
          </div>

          <div className="ao-row">
            {money('original_price', ids.orig, 'Original price', true, 'The usual price, shown as the regular rate.', '2500')}
            {money('promo_price', ids.price, 'Promo price', false, 'What the guest pays with this promo.', '1999')}
          </div>

          <div className="ao-row">
            <div className="ao-field">
              <label className="ao-label" htmlFor={ids.start}>Start date</label>
              <input id={ids.start} type="date" className={`ao-input ${errors.start_date ? 'has-error' : ''}`}
                value={form.start_date} onChange={e => update('start_date', e.target.value)} />
              {errors.start_date ? <p className="ao-error">{errors.start_date}</p> : null}
            </div>
            <div className="ao-field">
              <label className="ao-label" htmlFor={ids.end}>End date</label>
              <input id={ids.end} type="date" min={form.start_date || undefined} className={`ao-input ${errors.end_date ? 'has-error' : ''}`}
                value={form.end_date} onChange={e => update('end_date', e.target.value)} />
              {errors.end_date ? <p className="ao-error">{errors.end_date}</p> : null}
            </div>
          </div>

          <div className="ao-field" role="group" aria-label="Status">
            <span className="ao-label">Status</span>
            <div className="ao-seg">
              <button type="button" aria-pressed={form.status === 'active'} onClick={() => update('status', 'active')}>
                <i className="fa-solid fa-eye"></i> Active
              </button>
              <button type="button" aria-pressed={form.status === 'inactive'} onClick={() => update('status', 'inactive')}>
                <i className="fa-solid fa-eye-slash"></i> Inactive
              </button>
            </div>
            <p className="ao-help">Inactive promos are saved but hidden from the website.</p>
          </div>

          <div className="ao-actions">
            <button type="button" className="ao-btn is-quiet" onClick={onClose}>Cancel</button>
            <button type="submit" className="ao-btn is-solid" disabled={saving}>
              <i className={`fa-solid ${isEdit ? 'fa-floppy-disk' : 'fa-plus'}`}></i>
              {saving ? 'Saving…' : (isEdit ? 'Save changes' : 'Add this promo')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function PromoCard({ promo, canManage, busy, onEdit, onToggle, onDelete }) {
  const look = STATE_LOOK[promo.state] || STATE_LOOK.Inactive;
  return (
    <article className="ao-card">
      <div className="ao-card-img">
        {promo.img ? <img src={promo.img} alt={promo.name} loading="lazy" /> : null}
        <span className={`ao-pill ${look.tone}`}><i className={`fa-solid ${look.icon}`}></i>{look.label}</span>
      </div>
      <div className="ao-card-body">
        <div className="ao-card-top">
          <h3 className="ao-name">{promo.name}</h3>
          <div className="ao-price">
            <b>{peso(promo.promo_price)}</b>
            {promo.original_price ? <s>{peso(promo.original_price)}</s> : null}
          </div>
        </div>
        <p className="ao-desc">{promo.description}</p>
        <p className="ao-dates" style={{ margin: 0 }}>
          <i className="fa-regular fa-calendar"></i>
          {niceDate(promo.start_date)} – {niceDate(promo.end_date)}
        </p>
        {canManage ? (
          <div className="ao-card-foot">
            <button type="button" className="ao-btn is-small" onClick={() => onEdit(promo)} disabled={busy}>
              <i className="fa-solid fa-pen"></i> Edit
            </button>
            <button type="button" className="ao-btn is-small is-quiet" onClick={() => onToggle(promo)} disabled={busy}>
              <i className={`fa-solid ${promo.status === 'active' ? 'fa-eye-slash' : 'fa-eye'}`}></i>
              {promo.status === 'active' ? 'Deactivate' : 'Activate'}
            </button>
            <button type="button" className="ao-btn is-small is-danger" onClick={() => onDelete(promo)} disabled={busy} aria-label={'Delete ' + promo.name} title="Delete">
              <i className="fa-solid fa-trash-can"></i>
            </button>
          </div>
        ) : null}
      </div>
    </article>
  );
}

function App() {
  const [promos, setPromos] = useState([]);
  const [canManage, setCanManage] = useState(false);
  const [loading, setLoading] = useState(true);
  const [failed, setFailed] = useState(false);
  const [editing, setEditing] = useState(null);   // a promo row, or 'new'
  const [busyId, setBusyId] = useState(null);
  // A poll landing mid-write would put back the list as it was before it.
  const pendingWrites = useRef(0);

  const fetchPromos = useCallback(() => {
    if (pendingWrites.current > 0) return;
    fetch(CONFIG.indexUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
      .then(r => (r.ok ? r.json() : Promise.reject(r)))
      .then(data => {
        if (pendingWrites.current > 0) return;
        setPromos(data.items || []);
        setCanManage(!!data.can_manage);
        setFailed(false);
      })
      .catch(() => setFailed(true))
      .finally(() => setLoading(false));
  }, []);

  // Teammates' changes come in on the poll.
  useEffect(() => {
    fetchPromos();
    const timer = setInterval(() => { if (!document.hidden) fetchPromos(); }, 8000);
    window.addEventListener('focus', fetchPromos);
    return () => { clearInterval(timer); window.removeEventListener('focus', fetchPromos); };
  }, [fetchPromos]);

  const handleSaved = useCallback((item) => {
    setPromos(prev => prev.some(p => p.dbId === item.dbId)
      ? prev.map(p => (p.dbId === item.dbId ? item : p))
      : prev.concat([item]));
  }, []);

  const write = (promo, run) => {
    pendingWrites.current += 1;
    setBusyId(promo.dbId);
    return run().finally(() => { pendingWrites.current -= 1; setBusyId(null); });
  };

  const toggle = (promo) => write(promo, () =>
    send(CONFIG.storeUrl + '/' + promo.dbId, 'PATCH', { status: promo.status === 'active' ? 'inactive' : 'active' })
      .then(data => {
        handleSaved(data.item);
        swal('success', data.item.status === 'active' ? 'Promo activated' : 'Promo deactivated', data.item.live
          ? data.item.name + ' is on the hotel website.'
          : data.item.name + ' is hidden from the hotel website.');
      })
      .catch(err => swal('error', 'Not changed', errorMessage(err, 'Could not change the status. Please try again.'))));

  const remove = (promo) => confirmDelete(promo.name).then(ok => {
    if (!ok) return;
    return write(promo, () =>
      send(CONFIG.storeUrl + '/' + promo.dbId, 'DELETE')
        .then(() => {
          setPromos(prev => prev.filter(p => p.dbId !== promo.dbId));
          swal('success', 'Promo deleted', promo.name + ' has been removed.');
        })
        .catch(err => swal('error', 'Not deleted', errorMessage(err, 'Could not delete. Please try again.'))));
  });

  const closeModal = useCallback(() => setEditing(null), []);

  const count = (state) => promos.filter(p => p.state === state).length;

  return (
    <div className="ao" data-hms-no-edit="1">
      <header className="ao-head">
        <div>
          <p className="ao-eyebrow">Front Desk</p>
          <h1 className="font-display">Promos &amp; Packages</h1>
          <p className="ao-lead">
            Offers for guests, like a weekend stay or a dine-and-stay package. Active promos show on
            your hotel website until their end date. Inactive or expired ones are hidden on their own.
          </p>
        </div>
        <div className="ao-head-actions">
          {canManage ? (
            <button type="button" className="ao-btn is-solid" onClick={() => setEditing('new')}>
              <i className="fa-solid fa-plus"></i> Add promo / package
            </button>
          ) : null}
        </div>
      </header>

      {!loading && !canManage && !failed ? (
        <p className="ao-banner">
          <i className="fa-solid fa-eye"></i>
          <span>You can look at this list, but only Front Desk staff can add or change promos.</span>
        </p>
      ) : null}

      <section className="ao-panel" aria-labelledby="ao-list">
        <div className="ao-panel-head">
          <div>
            <h2 id="ao-list">Your promos and packages</h2>
            <p>{promos.length === 0 ? 'None yet.' : `${promos.length} saved.`}</p>
          </div>
          <span className="ao-live">Updates on its own</span>
        </div>

        {promos.length > 0 ? (
          <div className="ao-stats">
            <div className="ao-stat"><span className="ao-stat-icon tone-ok"><i className="fa-solid fa-globe"></i></span><div><b>{count('Running')}</b><span>On the website now</span></div></div>
            <div className="ao-stat"><span className="ao-stat-icon tone-guest"><i className="fa-solid fa-clock"></i></span><div><b>{count('Scheduled')}</b><span>Starting later</span></div></div>
            <div className="ao-stat"><span className="ao-stat-icon tone-muted"><i className="fa-solid fa-eye-slash"></i></span><div><b>{count('Inactive')}</b><span>Inactive</span></div></div>
            <div className="ao-stat"><span className="ao-stat-icon tone-bad"><i className="fa-solid fa-calendar-xmark"></i></span><div><b>{count('Expired')}</b><span>Expired</span></div></div>
          </div>
        ) : null}

        {loading ? (
          <div className="ao-grid" aria-busy="true" aria-label="Loading promos">
            <div className="ao-skel"></div><div className="ao-skel"></div><div className="ao-skel"></div>
          </div>
        ) : failed && promos.length === 0 ? (
          <div className="ao-empty" role="alert">
            <div className="ao-empty-icon"><i className="fa-solid fa-wifi"></i></div>
            <h2>Could not load the promos</h2>
            <p>Check your internet connection. The page tries again on its own every few seconds.</p>
          </div>
        ) : promos.length === 0 ? (
          <div className="ao-empty">
            <div className="ao-empty-icon"><i className="fa-solid fa-tags"></i></div>
            <h2>No promos yet</h2>
            <p>Until you add one, the website keeps the promo cards from your customized template.</p>
            {canManage ? (
              <button type="button" className="ao-btn is-solid" onClick={() => setEditing('new')}>
                <i className="fa-solid fa-plus"></i> Add promo / package
              </button>
            ) : null}
          </div>
        ) : (
          <div className="ao-grid">
            {promos.map(promo => (
              <PromoCard key={promo.id} promo={promo} canManage={canManage} busy={busyId === promo.dbId}
                onEdit={setEditing} onToggle={toggle} onDelete={remove} />
            ))}
          </div>
        )}
      </section>

      {editing ? (
        <PromoModal promo={editing === 'new' ? null : editing} onClose={closeModal} onSaved={handleSaved} />
      ) : null}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
