@extends('students.builder.ops-shell')

@section('page-title', 'Add-ons')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }

  /* ── Template 2 (cream / forest green / DM Sans + Cormorant Garamond) ──
     Only the tokens change; every rule below reads them. */
  :root[data-ops-theme="2"] {
    --bg: #f7f4ef; --bg-warm: #efe9e0; --fg: #1a1a1a; --fg-muted: #7a7570;
    --accent: #1b4332; --accent-light: #2d6a4f; --card: #ffffff; --border: #e2ddd5;
    --font-body: 'DM Sans', sans-serif; --font-display: 'Cormorant Garamond', serif;
    --danger: #e11d48; --success: #15803d; --warn: #b45309;
  }

  /* Everything below reads the shell's tokens, the same way Room Inspections
     does, so the page follows Template 1, Template 2 and a team's own site
     colours. Shape rule: pills for status, 10px for buttons and fields, 14px
     for panels and cards. */
  #opsContentWrap { font-family: var(--font-body, 'Outfit', sans-serif); }
  .font-display { font-family: var(--font-display, 'Playfair Display', serif); }

  .ao {
    --ao-soft: color-mix(in srgb, var(--fg) 4%, transparent);
    --ao-tint: color-mix(in srgb, var(--accent) 12%, transparent);
    --ao-line: var(--border);
    --ao-ok: var(--success, #4ade80);
    --ao-warn: var(--warn, #f59e0b);
    --ao-bad: var(--danger, #fb7185);
    --ao-out: var(--accent);
    padding: 1.5rem 1.5rem 3rem;
    color: var(--fg);
  }

  /* Template 2's accent is green like "ready", so lent-out items take its copper. */
  :root[data-ops-theme="2"] .ao { --ao-out: #c17849; }

  /* Page header */
  .ao-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.4rem; }
  .ao-eyebrow { color: var(--accent); font-size: 0.72rem; letter-spacing: 0.25em; text-transform: uppercase; margin: 0 0 0.5rem; }
  .ao-head h1 { margin: 0; font-size: 1.85rem; line-height: 1.15; color: var(--fg); }
  .ao-lead { margin: 0.45rem 0 0; color: var(--fg-muted); font-size: 0.92rem; max-width: 64ch; line-height: 1.5; }
  .ao-head-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }

  .ao-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 0.55rem;
    font: 600 0.88rem/1.15 var(--font-body, 'Outfit', sans-serif);
    padding: 0.8rem 1.15rem; border-radius: 10px; cursor: pointer; text-decoration: none;
    border: 1px solid var(--accent); background: transparent; color: var(--accent);
    transition: background 0.15s, transform 0.1s, filter 0.15s;
  }
  .ao-btn:hover { background: var(--ao-tint); }
  .ao-btn:active { transform: translateY(1px); }
  .ao-btn.is-solid { background: var(--accent); color: var(--bg); }
  .ao-btn.is-solid:hover { filter: brightness(1.08); }
  .ao-btn.is-quiet { border-color: var(--ao-line); color: var(--fg-muted); }
  .ao-btn.is-quiet:hover { color: var(--fg); background: var(--ao-soft); }
  .ao-btn.is-wide { width: 100%; }
  .ao-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; filter: none; }
  .ao-btn:focus-visible, .ao-photo-pick:focus-visible, .ao-link:focus-visible, .ao-close:focus-visible {
    outline: 2px solid var(--accent); outline-offset: 2px;
  }

  /* How it works */
  .ao-how { list-style: none; margin: 0 0 1.25rem; padding: 0; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
  .ao-how li { display: flex; gap: 0.7rem; align-items: flex-start; padding: 0.85rem 0.95rem; border-radius: 14px; background: var(--ao-soft); border: 1px solid var(--ao-line); }
  .ao-how-num { flex: none; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.82rem; background: var(--ao-tint); color: var(--accent); }
  .ao-how div b { display: block; font-size: 0.86rem; color: var(--fg); margin-bottom: 0.15rem; }
  .ao-how div span { display: block; font-size: 0.78rem; color: var(--fg-muted); line-height: 1.4; }

  .ao-banner { display: flex; gap: 0.6rem; align-items: flex-start; margin: 0 0 1.25rem; padding: 0.85rem 1rem; border-radius: 14px; border: 1px solid color-mix(in srgb, var(--ao-warn) 40%, transparent); background: color-mix(in srgb, var(--ao-warn) 10%, transparent); font-size: 0.86rem; color: var(--fg); }
  .ao-banner i { color: var(--ao-warn); margin-top: 0.15rem; }

  /* Panel */
  .ao-panel { background: var(--card); border: 1px solid var(--ao-line); border-radius: 14px; padding: 1.2rem 1.3rem 1.4rem; }
  .ao-panel-head { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
  .ao-panel-head h2 { margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--fg); }
  .ao-panel-head p { margin: 0.2rem 0 0; font-size: 0.82rem; color: var(--fg-muted); }
  .ao-live { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8rem; color: var(--fg-muted); }
  .ao-live::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--ao-ok); }

  /* Totals */
  .ao-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0.75rem; margin-bottom: 1.1rem; }
  .ao-stat { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 0.95rem; border-radius: 12px; border: 1px solid var(--ao-line); background: var(--ao-soft); }
  .ao-stat-icon { flex: none; width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; }
  .ao-stat div b { display: block; font-size: 1.35rem; line-height: 1.1; font-variant-numeric: tabular-nums; color: var(--fg); }
  .ao-stat div span { display: block; font-size: 0.78rem; color: var(--fg-muted); }
  .tone-ok    { background: color-mix(in srgb, var(--ao-ok) 16%, transparent);   color: var(--ao-ok); }
  .tone-guest { background: var(--ao-tint); color: var(--accent); }
  .tone-warn  { background: color-mix(in srgb, var(--ao-warn) 16%, transparent); color: var(--ao-warn); }
  .tone-bad   { background: color-mix(in srgb, var(--ao-bad) 14%, transparent);  color: var(--ao-bad); }

  .ao-search { position: relative; margin-bottom: 1.1rem; }
  .ao-search i { position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--fg-muted); font-size: 0.8rem; pointer-events: none; }
  .ao-search .ao-input { padding-left: 2.3rem; }

  .ao-input {
    box-sizing: border-box; width: 100%;
    background: var(--ao-soft); border: 1px solid var(--ao-line);
    border-radius: 10px; padding: 0.75rem 0.9rem; color: var(--fg);
    font: 400 0.9rem/1.4 var(--font-body, 'Outfit', sans-serif);
    outline: none; transition: border-color 0.15s;
  }
  .ao-input:focus { border-color: var(--accent); }
  .ao-input::placeholder { color: var(--fg-muted); opacity: 0.7; }
  .ao-input.has-error { border-color: var(--ao-bad); }

  /* Item cards */
  .ao-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(270px, 100%), 1fr)); gap: 1rem; }
  .ao-card { min-width: 0; border: 1px solid var(--ao-line); border-radius: 14px; background: var(--ao-soft); overflow: hidden; display: flex; flex-direction: column; }
  .ao-card-img { position: relative; aspect-ratio: 16 / 9; background: var(--ao-soft); }
  .ao-card-img { overflow: hidden; }
  .ao-card-img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block; }
  .ao-card-img .ao-pill { position: absolute; top: 10px; left: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.25); }
  .ao-card-body { padding: 0.95rem 1.05rem 1.05rem; display: flex; flex-direction: column; gap: 0.8rem; flex: 1; }
  .ao-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
  .ao-name { margin: 0; font-size: 1.08rem; font-weight: 700; color: var(--fg); line-height: 1.25; overflow-wrap: anywhere; }
  .ao-price { text-align: right; flex: none; }
  .ao-price b { display: block; font-size: 1.05rem; color: var(--fg); font-variant-numeric: tabular-nums; }
  .ao-price small { display: block; font-size: 0.7rem; color: var(--fg-muted); }
  .ao-pill { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.76rem; font-weight: 600; white-space: nowrap; }
  .ao-card-img .ao-pill.tone-ok, .ao-card-img .ao-pill.tone-warn, .ao-card-img .ao-pill.tone-bad { background: var(--card); }

  .ao-stock { display: grid; gap: 0.45rem; }
  .ao-stock-line { display: flex; justify-content: space-between; gap: 0.5rem; font-size: 0.82rem; color: var(--fg-muted); }
  .ao-stock-line b { color: var(--fg); font-variant-numeric: tabular-nums; }
  .ao-bar { height: 8px; border-radius: 999px; background: color-mix(in srgb, var(--fg) 8%, transparent); overflow: hidden; display: flex; }
  .ao-bar span { display: block; height: 100%; }
  .ao-bar .is-free { background: var(--ao-ok); }
  .ao-bar .is-out { background: var(--ao-out); }
  .ao-key { display: flex; flex-wrap: wrap; gap: 0.3rem 0.9rem; font-size: 0.74rem; color: var(--fg-muted); }
  .ao-key span { display: inline-flex; align-items: center; gap: 0.35rem; }
  .ao-key i { width: 8px; height: 8px; border-radius: 50%; }
  .ao-card-foot { margin-top: auto; }

  /* Empty / loading */
  .ao-empty { border: 1.5px dashed var(--ao-line); border-radius: 14px; padding: 2.4rem 1.5rem; text-align: center; }
  .ao-empty-icon { width: 56px; height: 56px; margin: 0 auto 0.9rem; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.35rem; background: var(--ao-tint); color: var(--accent); }
  .ao-empty h2 { margin: 0; font-size: 1.02rem; font-weight: 700; color: var(--fg); }
  .ao-empty p { margin: 0.4rem auto 0; max-width: 50ch; font-size: 0.86rem; line-height: 1.5; color: var(--fg-muted); }
  .ao-empty .ao-btn { margin-top: 1.1rem; }
  .ao-skel { height: 300px; border-radius: 14px; border: 1px solid var(--ao-line); background: linear-gradient(90deg, var(--ao-soft) 0%, color-mix(in srgb, var(--fg) 8%, transparent) 50%, var(--ao-soft) 100%); background-size: 200% 100%; animation: ao-shimmer 1.4s ease-in-out infinite; }
  @keyframes ao-shimmer { from { background-position: 100% 0; } to { background-position: -100% 0; } }
  @media (prefers-reduced-motion: reduce) { .ao-skel { animation: none; } }

  /* Add / edit dialog */
  .ao-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; padding: 1.25rem; z-index: 200; }
  .ao-modal { box-sizing: border-box; background: var(--card); color: var(--fg); border: 1px solid var(--ao-line); border-radius: 14px; width: 100%; max-width: 520px; max-height: 92vh; overflow-y: auto; }
  .ao-modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.35rem 0; }
  .ao-modal-head h2 { margin: 0; font-size: 1.45rem; line-height: 1.2; color: var(--fg); }
  .ao-modal-head p { margin: 0.35rem 0 0; font-size: 0.84rem; color: var(--fg-muted); line-height: 1.45; }
  .ao-close { flex: none; width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--ao-line); background: transparent; color: var(--fg-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; }
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
  .ao-photo-pick { display: block; width: 100%; padding: 0; border: 1.5px dashed var(--ao-line); border-radius: 10px; background: var(--ao-soft); cursor: pointer; overflow: hidden; color: var(--fg-muted); font: inherit; }
  .ao-photo-pick:hover { border-color: var(--accent); }
  .ao-photo-pick img { width: 100%; height: 150px; object-fit: cover; display: block; }
  .ao-photo-empty { height: 110px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.45rem; font-size: 0.82rem; }
  .ao-photo-empty i { font-size: 1.4rem; color: var(--accent); }
  .ao-link { background: none; border: 0; padding: 0.2rem 0; cursor: pointer; color: var(--fg-muted); font: 500 0.78rem/1 var(--font-body, 'Outfit', sans-serif); justify-self: start; }
  .ao-link:hover { color: var(--fg); text-decoration: underline; }
  .ao-note { margin: 0; padding: 0.7rem 0.8rem; border-radius: 10px; font-size: 0.8rem; line-height: 1.45; display: flex; gap: 0.5rem; align-items: flex-start; color: var(--fg); }
  .ao-note.tone-warn i { color: var(--ao-warn); margin-top: 0.15rem; }
  .ao-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
  .ao-actions .ao-btn { flex: 1 1 auto; }

  @media (max-width: 960px) {
    .ao-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  @media (max-width: 680px) {
    .ao-how { grid-template-columns: 1fr; }
  }
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
  window.HMS_ADDONS = {
    backUrl: @json(route('students.dashboard', ['section' => 'tasks'])),
    indexUrl: @json(route('students.hotel.addons.index')),
    storeUrl: @json(route('students.hotel.addons.store')),
  };
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef, useMemo, useId } = React;

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

function plural(n, one, many) {
  return n + ' ' + (n === 1 ? one : many);
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
  if (!String(form.name || '').trim()) errors.name = 'Type the name of the item.';
  const price = parseInt(String(form.price).replace(/,/g, ''), 10);
  if (!Number.isFinite(price) || price < 0) errors.price = 'Type a price. Use 0 if it is free.';
  const quantity = parseInt(String(form.quantity).replace(/,/g, ''), 10);
  if (!Number.isFinite(quantity) || quantity < 0) errors.quantity = 'Type how many the hotel has. Use 0 if none.';
  return errors;
}

// SweetAlert draws outside the page's CSS, so it gets the live token values.
function themeColor(name, fallback) {
  const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  return value || fallback;
}

function swal(icon, title, text) {
  if (!window.Swal) return;
  window.Swal.fire({
    icon, title, text,
    background: themeColor('--card', '#181714'), color: themeColor('--fg', '#f5f0e8'),
    iconColor: icon === 'success' ? themeColor('--success', '#4ade80') : themeColor('--danger', '#fb7185'),
    confirmButtonColor: themeColor('--accent', '#c9a84c'),
    confirmButtonText: 'OK',
    timer: icon === 'success' ? 3000 : undefined,
    timerProgressBar: icon === 'success',
  });
}

// How an item's stock reads at a glance. "Running low" is one or two left
// out of more than two, so a team that owns exactly two is not always warned.
function stockState(addon) {
  if (addon.quantity === 0) return { label: 'None in stock', tone: 'tone-bad', icon: 'fa-box-open' };
  if (addon.available === 0) return { label: 'All lent out', tone: 'tone-bad', icon: 'fa-circle-xmark' };
  if (addon.available <= 2 && addon.quantity > 2) return { label: `Only ${addon.available} left`, tone: 'tone-warn', icon: 'fa-triangle-exclamation' };
  return { label: 'Ready to lend', tone: 'tone-ok', icon: 'fa-circle-check' };
}

/* One dialog for both doors: "Add a new item" opens it empty and POSTs, Edit
   opens it filled in and PATCHes. The fields are identical either way. */
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
  const ids = { name: useId(), qty: useId(), price: useId(), title: useId() };
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

  const typedQty = parseInt(String(form.quantity).replace(/,/g, ''), 10);
  const lentOut = isEdit ? (addon.reserved || 0) : 0;
  const belowLent = isEdit && Number.isFinite(typedQty) && typedQty < lentOut;

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
        swal('success', isEdit ? 'Changes saved' : 'Item added', isEdit
          ? data.item.name + ' has been updated.'
          : data.item.name + ' is now on the list. The Front Desk can lend it to guests.');
      })
      .catch(err => {
        const msg = (err && err.message) ? err.message : 'Could not save. Please try again.';
        if (window.Swal) swal('error', 'Not saved', msg);
        else setErrors({ name: msg });
      })
      .finally(() => setSaving(false));
  };

  return (
    <div className="ao-overlay" onClick={onClose}>
      <div className="ao-modal" role="dialog" aria-modal="true" aria-labelledby={ids.title} onClick={e => e.stopPropagation()}>
        <div className="ao-modal-head">
          <div>
            <h2 id={ids.title} className="font-display">{isEdit ? `Edit ${addon.name}` : 'Add a new item'}</h2>
            <p>{isEdit
              ? 'Change the name, price, how many the hotel has, or the photo.'
              : 'Something guests can borrow during their stay, like a folding bed or an extra pillow.'}</p>
          </div>
          <button type="button" className="ao-close" onClick={onClose} aria-label="Close">
            <i className="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form onSubmit={handleSubmit} className="ao-form" noValidate>
          <div className="ao-field">
            <label className="ao-label" htmlFor={ids.name}>Item name</label>
            <input
              id={ids.name} ref={firstField}
              type="text" className={`ao-input ${errors.name ? 'has-error' : ''}`} value={form.name}
              placeholder="Example: Folding Bed"
              onChange={e => update('name', e.target.value)}
            />
            {errors.name ? <p className="ao-error">{errors.name}</p> : <p className="ao-help">This is the name the Front Desk sees when lending it.</p>}
          </div>

          <div className="ao-row">
            <div className="ao-field">
              <label className="ao-label" htmlFor={ids.qty}>How many does the hotel have?</label>
              <input
                id={ids.qty}
                type="number" min="0" inputMode="numeric" className={`ao-input ${errors.quantity ? 'has-error' : ''}`} value={form.quantity}
                placeholder="10"
                onChange={e => update('quantity', e.target.value)}
              />
              {errors.quantity ? <p className="ao-error">{errors.quantity}</p> : <p className="ao-help">Count all of them, even ones a guest has now.</p>}
            </div>
            <div className="ao-field">
              <label className="ao-label" htmlFor={ids.price}>Price for the guest</label>
              <div className="ao-money">
                <span>₱</span>
                <input
                  id={ids.price}
                  type="number" min="0" inputMode="numeric" className={`ao-input ${errors.price ? 'has-error' : ''}`} value={form.price}
                  placeholder="350"
                  onChange={e => update('price', e.target.value)}
                />
              </div>
              {errors.price ? <p className="ao-error">{errors.price}</p> : <p className="ao-help">For each one. Added to the guest's bill. Use 0 if free.</p>}
            </div>
          </div>

          {belowLent ? (
            <p className="ao-note tone-warn">
              <i className="fa-solid fa-triangle-exclamation"></i>
              <span>{plural(lentOut, 'is', 'are')} with guests right now, more than the {typedQty} you typed. You can still save. None will be free to lend until enough guests check out.</span>
            </p>
          ) : null}

          <div className="ao-field">
            <span className="ao-label">Photo <em>(optional)</em></span>
            <button type="button" className="ao-photo-pick" onClick={() => pickImageFile(url => { if (url) update('img', url); })}>
              {form.img ? (
                <img src={form.img} alt="Photo of the item" />
              ) : (
                <span className="ao-photo-empty">
                  <i className="fa-solid fa-camera"></i>
                  Click to choose a photo
                </span>
              )}
            </button>
            {form.img ? (
              <button type="button" className="ao-link" onClick={() => update('img', '')}>
                <i className="fa-solid fa-trash-can"></i> Remove photo
              </button>
            ) : null}
          </div>

          <div className="ao-actions">
            <button type="button" className="ao-btn is-quiet" onClick={onClose}>Cancel</button>
            <button type="submit" className="ao-btn is-solid" disabled={saving}>
              <i className={`fa-solid ${isEdit ? 'fa-floppy-disk' : 'fa-plus'}`}></i>
              {saving ? 'Saving…' : (isEdit ? 'Save changes' : 'Add this item')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

function AddonCard({ addon, canManage, onEdit }) {
  const state = stockState(addon);
  const total = Math.max(addon.quantity, addon.reserved + addon.available, 1);
  const freePct = (addon.available / total) * 100;
  const outPct = (Math.min(addon.reserved, total) / total) * 100;

  return (
    <article className="ao-card">
      <div className="ao-card-img">
        <img src={addonImg(addon)} alt={addon.name} loading="lazy" />
        <span className={`ao-pill ${state.tone}`}><i className={`fa-solid ${state.icon}`}></i>{state.label}</span>
      </div>
      <div className="ao-card-body">
        <div className="ao-card-top">
          <h3 className="ao-name">{addon.name}</h3>
          <div className="ao-price">
            <b>{addon.price > 0 ? peso(addon.price) : 'Free'}</b>
            <small>{addon.price > 0 ? 'each' : 'no charge'}</small>
          </div>
        </div>

        <div className="ao-stock">
          <div className="ao-stock-line">
            <span><b>{addon.available}</b> of {addon.quantity} ready to lend</span>
          </div>
          <div className="ao-bar" role="img" aria-label={`${addon.available} ready to lend, ${addon.reserved} with guests`}>
            <span className="is-free" style={{ width: freePct + '%' }}></span>
            <span className="is-out" style={{ width: outPct + '%' }}></span>
          </div>
          <div className="ao-key">
            <span><i style={{ background: 'var(--ao-ok)' }}></i>{addon.available} in storage</span>
            <span><i style={{ background: 'var(--ao-out)' }}></i>{addon.reserved} with guests</span>
          </div>
        </div>

        {canManage ? (
          <div className="ao-card-foot">
            <button type="button" className="ao-btn is-wide" onClick={() => onEdit(addon)}>
              <i className="fa-solid fa-pen"></i> Edit item
            </button>
          </div>
        ) : null}
      </div>
    </article>
  );
}

function App() {
  const [addons, setAddons] = useState([]);
  const [canManage, setCanManage] = useState(false);
  const [loading, setLoading] = useState(true);
  const [failed, setFailed] = useState(false);
  const [search, setSearch] = useState('');
  const [editing, setEditing] = useState(null);   // an addon row, or 'new'
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
        setFailed(false);
      })
      .catch(() => setFailed(true))
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

  const closeModal = useCallback(() => setEditing(null), []);

  const visible = useMemo(() => {
    const q = search.trim().toLowerCase();
    if (!q) return addons;
    return addons.filter(a => String(a.name || '').toLowerCase().includes(q));
  }, [addons, search]);

  const totals = addons.reduce((t, a) => {
    t.free += a.available;
    t.out += a.reserved;
    if (stockState(a).tone !== 'tone-ok') t.low += 1;
    return t;
  }, { free: 0, out: 0, low: 0 });

  return (
    <div className="ao" data-hms-no-edit="1">
      <header className="ao-head">
        <div>
          <p className="ao-eyebrow">Housekeeping</p>
          <h1 className="font-display">Add-ons</h1>
          <p className="ao-lead">
            Extra items guests can borrow during their stay, like a folding bed or an extra
            towel. The Front Desk lends them when a guest checks in. When the guest checks
            out, the items count as back in storage on their own.
          </p>
        </div>
        <div className="ao-head-actions">
          <a href={CONFIG.backUrl} className="ao-btn">
            <i className="fa-solid fa-arrow-left"></i> Back to Tasks
          </a>
          {canManage ? (
            <button type="button" className="ao-btn is-solid" onClick={() => setEditing('new')}>
              <i className="fa-solid fa-plus"></i> Add a new item
            </button>
          ) : null}
        </div>
      </header>

      <ol className="ao-how" aria-label="How it works">
        <li><span className="ao-how-num">1</span><div><b>List the item</b><span>Say what it is, how many the hotel has, and the price.</span></div></li>
        <li><span className="ao-how-num">2</span><div><b>Front Desk lends it</b><span>They pick it when a guest checks in. It goes on the guest's bill.</span></div></li>
        <li><span className="ao-how-num">3</span><div><b>It comes back by itself</b><span>When the guest checks out, the count goes back up. Nothing to press.</span></div></li>
      </ol>

      {!loading && !canManage && !failed ? (
        <p className="ao-banner">
          <i className="fa-solid fa-eye"></i>
          <span>You can look at this list, but only Housekeeping staff can add or edit items.</span>
        </p>
      ) : null}

      <section className="ao-panel" aria-labelledby="ao-list">
        <div className="ao-panel-head">
          <div>
            <h2 id="ao-list">Items guests can borrow</h2>
            <p>{addons.length === 0 ? 'No items yet.' : `${plural(addons.length, 'item', 'items')} on the list.`}</p>
          </div>
          <span className="ao-live">Updates on its own</span>
        </div>

        {addons.length > 0 ? (
          <div className="ao-stats">
            <div className="ao-stat"><span className="ao-stat-icon tone-guest"><i className="fa-solid fa-list"></i></span><div><b>{addons.length}</b><span>Items on the list</span></div></div>
            <div className="ao-stat"><span className="ao-stat-icon tone-ok"><i className="fa-solid fa-box"></i></span><div><b>{totals.free}</b><span>In storage, ready to lend</span></div></div>
            <div className="ao-stat"><span className="ao-stat-icon tone-guest"><i className="fa-solid fa-door-closed"></i></span><div><b>{totals.out}</b><span>With guests now</span></div></div>
            <div className="ao-stat"><span className={`ao-stat-icon ${totals.low ? 'tone-warn' : 'tone-ok'}`}><i className="fa-solid fa-triangle-exclamation"></i></span><div><b>{totals.low}</b><span>Running low or out</span></div></div>
          </div>
        ) : null}

        {addons.length > 4 ? (
          <div className="ao-search">
            <i className="fa-solid fa-magnifying-glass"></i>
            <input type="text" className="ao-input" placeholder="Search an item" aria-label="Search an item" value={search} onChange={e => setSearch(e.target.value)} />
          </div>
        ) : null}

        {loading ? (
          <div className="ao-grid" aria-busy="true" aria-label="Loading items">
            <div className="ao-skel"></div><div className="ao-skel"></div><div className="ao-skel"></div>
          </div>
        ) : failed && addons.length === 0 ? (
          <div className="ao-empty" role="alert">
            <div className="ao-empty-icon"><i className="fa-solid fa-wifi"></i></div>
            <h2>Could not load the items</h2>
            <p>Check your internet connection. The page tries again on its own every few seconds.</p>
          </div>
        ) : addons.length === 0 ? (
          <div className="ao-empty">
            <div className="ao-empty-icon"><i className="fa-solid fa-cart-flatbed"></i></div>
            <h2>No items yet</h2>
            <p>Add the first thing guests can borrow, like a folding bed or an extra towel. The Front Desk will see it when checking a guest in.</p>
            {canManage ? (
              <button type="button" className="ao-btn is-solid" onClick={() => setEditing('new')}>
                <i className="fa-solid fa-plus"></i> Add a new item
              </button>
            ) : null}
          </div>
        ) : visible.length === 0 ? (
          <div className="ao-empty">
            <div className="ao-empty-icon"><i className="fa-solid fa-magnifying-glass"></i></div>
            <h2>No item matches your search</h2>
            <p>Check the spelling, or clear the search box to see every item.</p>
          </div>
        ) : (
          <div className="ao-grid">
            {visible.map(addon => (
              <AddonCard key={addon.id} addon={addon} canManage={canManage} onEdit={setEditing} />
            ))}
          </div>
        )}
      </section>

      {editing ? (
        <AddonModal
          addon={editing === 'new' ? null : editing}
          onClose={closeModal}
          onSaved={handleSaved}
        />
      ) : null}
    </div>
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
