@extends('students.builder.ops-shell')

@php $backRoute = 'students.roommanagement'; @endphp

@section('page-title', 'Room Management')

@section('head-extra')
<style>
  :root {
    --bg: #0c0b09; --bg-warm: #111110; --fg: #f5f0e8; --fg-muted: #9e978b;
    --accent: #c9a84c; --accent-light: #e2cc7a; --card: #181714; --border: #2a2621;
  }
  #opsContentWrap { font-family: 'Outfit', sans-serif; }
  .font-display { font-family: 'Playfair Display', serif; }
  .room-status-badge {
    padding: 0.25rem 0.7rem; border-radius: 4px;
    font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
    font-weight: 600; border: 1px solid transparent;
  }
  .room-status-badge.status-available { background: rgba(34,197,94,0.18); color: #4ade80; border-color: rgba(34,197,94,0.35); }
  .room-status-badge.status-reserved { background: rgba(168,85,247,0.18); color: #c084fc; border-color: rgba(168,85,247,0.35); }
  .room-status-badge.status-occupied { background: rgba(59,130,246,0.18); color: #60a5fa; border-color: rgba(59,130,246,0.35); }
  .room-status-badge.status-cleaning { background: rgba(245,158,11,0.18); color: #fbbf24; border-color: rgba(245,158,11,0.35); }
  .room-status-badge.status-maintenance { background: rgba(244,63,94,0.18); color: #fb7185; border-color: rgba(244,63,94,0.35); }
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
</style>
@endsection

@section('content')
<div id="ops-root"></div>
@endsection

@section('scripts')
<script>
  window.HMS_ROOMMANAGEMENT_URL = @json(route('students.roommanagement'));
  window.HMS_ROOM_MGMT_INITIAL_NAV = @json(request()->query('nav', 'manage-room'));
</script>
@verbatim
<script type="text/babel">
const { useState, useEffect, useCallback, useRef } = React;

const ROOM_STATUSES = ['Available', 'Reserved', 'Occupied', 'Cleaning', 'Maintenance'];
const ROOM_CATEGORIES = ['Classic', 'Superior', 'Deluxe', 'Premium', 'Family'];
const BLOCK_HOURS = 12;
const IMAGE_MAX_DIMENSION = 1280;
const IMAGE_MAX_BYTES = 600 * 1024;

function normalizeRoomStatus(value) {
  const raw = String(value || 'Available').trim().toLowerCase();
  const match = ROOM_STATUSES.find(s => s.toLowerCase() === raw);
  return match || 'Available';
}
function roomStatusClass(status) { return 'status-' + normalizeRoomStatus(status).toLowerCase(); }
function normalizeRoomCategory(value) {
  const raw = String(value || 'Classic').trim().toLowerCase();
  const match = ROOM_CATEGORIES.find(c => c.toLowerCase() === raw);
  return match || 'Classic';
}
function reservationArrivalStatus(reservation) {
  const raw = String((reservation && reservation.arrivalStatus) || 'Reserved').trim().toLowerCase();
  return raw === 'arrived' ? 'Arrived' : 'Reserved';
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
    reader.onerror = function () {
      if (input.parentNode) input.parentNode.removeChild(input);
    };
    reader.readAsDataURL(file);
  });
  input.click();
}

function createEmptyRoomForm() {
  return { name: '', category: '', status: 'Available', price: '', desc: '', img: '' };
}

function validateRoomForm(form) {
  const errors = {};
  if (!String(form.name || '').trim()) errors.name = 'Room name is required.';
  if (!String(form.category || '').trim()) errors.category = 'Room type is required.';
  const price = parseFloat(String(form.price || '').replace(/,/g, ''));
  if (!String(form.price || '').trim() || !Number.isFinite(price) || price <= 0) {
    errors.price = 'Enter a valid price.';
  }
  return errors;
}

function ManageRoomPanel({ onSubmit, onCancel, onCloseModal }) {
  const [form, setForm] = useState(createEmptyRoomForm);
  const [errors, setErrors] = useState({});
  const [imgPreview, setImgPreview] = useState('');
  const [saving, setSaving] = useState(false);

  const fieldLabel = {
    fontSize: '0.68rem', letterSpacing: '0.1em', textTransform: 'uppercase',
    color: 'var(--fg-muted)', display: 'block', marginBottom: '0.4rem',
  };

  const update = (field, value) => {
    setForm(prev => Object.assign({}, prev, { [field]: value }));
    if (errors[field]) setErrors(prev => Object.assign({}, prev, { [field]: null }));
  };

  const resetForm = () => {
    setForm(createEmptyRoomForm());
    setErrors({});
    setImgPreview('');
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const nextErrors = validateRoomForm(form);
    setErrors(nextErrors);
    if (Object.keys(nextErrors).length) return;

    setSaving(true);
    fetch('/students/hotel/rooms', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify({
        name: String(form.name).trim(),
        category: form.category,
        price: parseInt(String(form.price).replace(/,/g, ''), 10),
        description: String(form.desc || '').trim(),
        image: form.img || '',
      }),
    })
      .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e)))
      .then(data => {
        if (data.room && typeof onSubmit === 'function') onSubmit(data.room);
        resetForm();
        if (window.Swal) {
          if (typeof onCloseModal === 'function') onCloseModal();
          window.Swal.fire({
            icon: 'success',
            title: 'Room Added!',
            text: data.room.name + ' has been added to the inventory.',
            background: '#181714',
            color: '#f5f0e8',
            iconColor: '#4ade80',
            confirmButtonColor: '#c9a84c',
            confirmButtonText: 'Great!',
            timer: 3000,
            timerProgressBar: true,
          });
        } else if (typeof onCloseModal === 'function') {
          onCloseModal();
        }
      })
      .catch((err) => {
        const msg = (err && err.message) ? err.message : 'Failed to save. Please try again.';
        if (window.Swal) {
          window.Swal.fire({
            icon: 'error', title: 'Error', text: msg,
            background: '#181714', color: '#f5f0e8', iconColor: '#fb7185', confirmButtonColor: '#c9a84c',
          });
        } else {
          setErrors({ name: msg });
        }
      })
      .finally(() => setSaving(false));
  };

  const handleCancel = () => { resetForm(); if (typeof onCancel === 'function') onCancel(); };

  const handleImagePick = () => {
    pickImageFile((url) => {
      if (!url) return;
      update('img', url);
      setImgPreview(url);
    });
  };

  const errorText = (key) => (
    errors[key]
      ? <p style={{ margin: '0.35rem 0 0', color: '#fb7185', fontSize: '0.72rem' }}>{errors[key]}</p>
      : null
  );

  return (
    <div className="rm-panel">
      <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Inventory</p>
      <h3>Manage Room</h3>
      <p className="rm-panel-desc">Add a new room to the hotel inventory. It will appear in the Rooms section right away.</p>

      <form onSubmit={handleSubmit} className="rm-form-grid" noValidate>
        <div>
          <label style={fieldLabel}>Room Name *</label>
          <input
            type="text" className="booking-input" placeholder="e.g. Deluxe King Room"
            value={form.name} onChange={e => update('name', e.target.value)}
            style={errors.name ? { borderColor: '#f43f5e' } : undefined}
          />
          {errorText('name')}
        </div>

        <div className="rm-form-row">
          <div>
            <label style={fieldLabel}>Room Type *</label>
            <select
              className="booking-input" value={form.category} onChange={e => update('category', e.target.value)}
              style={Object.assign({ colorScheme: 'dark', background: 'rgba(255,255,255,0.03)', color: form.category ? 'var(--fg)' : 'var(--fg-muted)' }, errors.category ? { borderColor: '#f43f5e' } : {})}
            >
              <option value="" style={{ background: '#181714', color: 'var(--fg-muted)' }}>Select type</option>
              {ROOM_CATEGORIES.map(c => <option key={c} value={c} style={{ background: '#181714', color: 'var(--fg)' }}>{c}</option>)}
            </select>
            {errorText('category')}
          </div>
          <div>
            <label style={fieldLabel}>Status</label>
            <div className="booking-input" style={{ color: '#4ade80', fontWeight: 600, cursor: 'default', display: 'flex', alignItems: 'center', gap: 6 }}>
              <span style={{ width: 7, height: 7, borderRadius: '50%', background: '#4ade80', display: 'inline-block', flexShrink: 0 }}></span>
              Available
            </div>
          </div>
        </div>

        <div>
          <label style={fieldLabel}>Price *</label>
          <input
            type="number" min="1" step="1" className="booking-input" placeholder="e.g. 4500"
            value={form.price} onChange={e => update('price', e.target.value)}
            style={errors.price ? { borderColor: '#f43f5e' } : undefined}
          />
          {errorText('price')}
        </div>

        <div>
          <label style={fieldLabel}>Description</label>
          <textarea
            className="booking-input" rows={3} placeholder="Short description of the room..."
            value={form.desc} onChange={e => update('desc', e.target.value)}
            style={{ resize: 'vertical', minHeight: 88 }}
          />
        </div>

        <div>
          <label style={fieldLabel}>Room Image</label>
          <div
            onClick={handleImagePick}
            style={{ border: '1.5px dashed var(--border)', borderRadius: 8, cursor: 'pointer', overflow: 'hidden', background: 'rgba(255,255,255,0.02)', transition: 'border-color 0.2s' }}
            onMouseEnter={e => e.currentTarget.style.borderColor = 'var(--accent)'}
            onMouseLeave={e => e.currentTarget.style.borderColor = 'var(--border)'}
          >
            {imgPreview ? (
              <img src={imgPreview} alt="Room preview" style={{ width: '100%', height: 140, objectFit: 'cover', display: 'block' }} />
            ) : (
              <div style={{ height: 100, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 8, color: 'var(--fg-muted)' }}>
                <i className="fa-solid fa-cloud-arrow-up" style={{ fontSize: '1.4rem', color: 'var(--accent)', opacity: 0.7 }}></i>
                <span style={{ fontSize: '0.75rem' }}>Click to upload image</span>
              </div>
            )}
          </div>
          {imgPreview && (
            <button type="button" onClick={() => { update('img', ''); setImgPreview(''); }}
              style={{ marginTop: '0.4rem', background: 'none', border: 'none', color: '#fb7185', fontSize: '0.72rem', cursor: 'pointer', padding: 0, fontFamily: 'Outfit, sans-serif' }}>
              <i className="fa-solid fa-xmark" style={{ marginRight: 4 }}></i>Remove image
            </button>
          )}
        </div>

        <div style={{ display: 'flex', gap: '0.75rem', marginTop: '0.35rem', flexWrap: 'wrap' }}>
          <button type="submit" className="btn-primary" disabled={saving}>
            <i className="fa-solid fa-plus" style={{ fontSize: '0.7rem' }}></i> {saving ? 'Saving…' : 'Add Room'}
          </button>
          <button type="button" className="btn-outline" onClick={handleCancel} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>Cancel</button>
        </div>
      </form>
    </div>
  );
}

function GuestDetailsPanel({ rooms, onEditRoom, onToast }) {
  const occupied = (rooms || []).filter(r => {
    const status = normalizeRoomStatus(r.status);
    return (status === 'Occupied' || status === 'Reserved') && r.reservation;
  });
  const awaitingCheckIn = occupied.filter(r => normalizeRoomStatus(r.status) === 'Reserved').length;

  const checkInGuest = (room) => {
    if (typeof onEditRoom !== 'function') return;
    onEditRoom(room.id, {
      status: 'Occupied',
      reservation: Object.assign({}, room.reservation, { checkedInAt: new Date().toISOString() }),
    });
    if (onToast) onToast(`${room.name} checked in — room is now Occupied.`);
  };

  const thStyle = {
    padding: '0.6rem 0.85rem', fontSize: '0.62rem', fontWeight: 700,
    letterSpacing: '0.1em', textTransform: 'uppercase', color: 'var(--fg-muted)',
    borderBottom: '1px solid var(--border)', whiteSpace: 'nowrap',
    textAlign: 'left', background: 'rgba(255,255,255,0.02)',
  };
  const tdStyle = {
    padding: '0.7rem 0.85rem', fontSize: '0.8rem', color: 'var(--fg-muted)',
    borderBottom: '1px solid rgba(42,38,33,0.5)', verticalAlign: 'middle', whiteSpace: 'nowrap',
  };

  if (!occupied.length) {
    return (
      <div className="rm-panel" style={{ maxWidth: '100%' }}>
        <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Occupancy</p>
        <h3>Guest Details</h3>
        <p className="rm-panel-desc">No reserved or occupied rooms with registered guests at this time.</p>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', padding: '2.5rem 0' }}>
          <div style={{ textAlign: 'center', color: 'var(--fg-muted)' }}>
            <i className="fa-solid fa-door-open" style={{ fontSize: '2rem', opacity: 0.25, display: 'block', marginBottom: '0.75rem' }}></i>
            <p style={{ fontSize: '0.82rem' }}>All rooms are currently vacant.</p>
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="rm-panel" style={{ maxWidth: '100%' }}>
      <p style={{ color: 'var(--accent)', fontSize: '0.68rem', letterSpacing: '0.14em', textTransform: 'uppercase', margin: '0 0 0.4rem' }}>Occupancy</p>
      <h3>Guest Details</h3>
      <p className="rm-panel-desc">
        {occupied.length} room{occupied.length > 1 ? 's' : ''} with a registered guest
        {awaitingCheckIn > 0 ? ` · ${awaitingCheckIn} awaiting check-in` : ''}.
      </p>
      <div style={{ overflowX: 'auto', borderRadius: 10, border: '1px solid var(--border)' }}>
        <table style={{ width: '100%', borderCollapse: 'collapse', fontFamily: 'Outfit, sans-serif' }}>
          <thead>
            <tr>
              <th style={thStyle}>Guest Name</th>
              <th style={thStyle}>Room</th>
              <th style={thStyle}>Contact</th>
              <th style={thStyle}>Check-In</th>
              <th style={thStyle}>Check-Out</th>
              <th style={thStyle}>Total</th>
              <th style={thStyle}>Payment</th>
              <th style={thStyle}>Status</th>
              <th style={thStyle}>Action</th>
            </tr>
          </thead>
          <tbody>
            {occupied.map((room, idx) => {
              const res = room.reservation;
              const payment = res.payment || null;
              const total = stayBlocks(res.checkIn, res.checkOut, res.checkInTime) * (Number(room.price) || 0);
              const rowBg = idx % 2 === 0 ? 'transparent' : 'rgba(255,255,255,0.015)';
              const status = normalizeRoomStatus(room.status);
              const arrival = reservationArrivalStatus(res);
              const canCheckIn = status !== 'Occupied' && arrival === 'Arrived';
              return (
                <tr key={room.id} style={{ background: rowBg }}>
                  <td style={{ ...tdStyle, color: 'var(--fg)', fontWeight: 600 }}>
                    <span style={{ display: 'block' }}>{res.fullName || '—'}</span>
                    <span style={{ display: 'block', fontSize: '0.7rem', color: 'var(--fg-muted)', fontWeight: 400 }}>{res.idNumber || ''}</span>
                  </td>
                  <td style={tdStyle}>
                    <span style={{ display: 'block', fontSize: '0.62rem', color: 'var(--accent)', letterSpacing: '0.08em', textTransform: 'uppercase', marginBottom: 2 }}>{room.label || room.category}</span>
                    <span style={{ color: 'var(--fg)', fontWeight: 500 }}>{room.name}</span>
                  </td>
                  <td style={tdStyle}>
                    <span style={{ display: 'block' }}>{res.contactNo || '—'}</span>
                    <span style={{ display: 'block', fontSize: '0.72rem' }}>{res.email || ''}</span>
                  </td>
                  <td style={tdStyle}>{formatCheckIn(res.checkIn, res.checkInTime)}</td>
                  <td style={tdStyle}>{res.checkOut || '—'}</td>
                  <td style={{ ...tdStyle, color: 'var(--accent-light)', fontFamily: 'Playfair Display, serif', fontWeight: 700 }}>{formatPeso(total)}</td>
                  <td style={tdStyle}>
                    {payment ? (
                      <>
                        <span style={{ display: 'block', color: 'var(--fg)', fontWeight: 500 }}>{payment.type} · {payment.method}</span>
                        <span style={{ display: 'block', fontSize: '0.75rem' }}>{formatPeso(payment.amountPaid)}</span>
                        {payment.balance > 0 && <span style={{ display: 'block', fontSize: '0.72rem', color: '#fb7185' }}>Bal: {formatPeso(payment.balance)}</span>}
                      </>
                    ) : <span style={{ opacity: 0.4 }}>—</span>}
                  </td>
                  <td style={tdStyle}>
                    <span className={`room-status-badge ${roomStatusClass(status)}`} style={{ position: 'static' }}>{status}</span>
                  </td>
                  <td style={tdStyle}>
                    {status === 'Occupied' ? (
                      <span style={{ display: 'inline-flex', alignItems: 'center', gap: '0.4rem', color: '#60a5fa', fontSize: '0.78rem', fontWeight: 600 }}>
                        <i className="fa-solid fa-circle-check" style={{ fontSize: '0.8rem' }}></i> Checked in
                      </span>
                    ) : (
                      <button
                        type="button"
                        onClick={() => canCheckIn && checkInGuest(room)}
                        disabled={!canCheckIn}
                        title={canCheckIn ? 'Check the guest in and set this room to Occupied' : 'Front Desk must mark the guest as Arrived first'}
                        style={{
                          display: 'inline-flex', alignItems: 'center', gap: '0.4rem',
                          padding: '0.4rem 0.8rem', borderRadius: 6,
                          border: '1px solid ' + (canCheckIn ? 'var(--accent)' : 'var(--border)'),
                          background: canCheckIn ? 'var(--accent)' : 'transparent',
                          color: canCheckIn ? 'var(--bg)' : 'var(--fg-muted)',
                          cursor: canCheckIn ? 'pointer' : 'not-allowed',
                          opacity: canCheckIn ? 1 : 0.5,
                          fontFamily: 'Outfit, sans-serif', fontSize: '0.72rem', fontWeight: 600,
                          letterSpacing: '0.06em', textTransform: 'uppercase',
                        }}
                      >
                        <i className="fa-solid fa-right-to-bracket" style={{ fontSize: '0.7rem' }}></i> Check In
                      </button>
                    )}
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}

function RoomManagementPage({ initialNav, rooms, onBack, onAddRoom, onEditRoom, onToast }) {
  const activeNav = initialNav || 'manage-room';

  const handleAddRoom = (payload) => {
    if (typeof onAddRoom === 'function') onAddRoom(payload);
    if (onToast) onToast(`${payload.name} added to Rooms.`);
  };

  return (
    <div style={{ maxWidth: 1100, margin: '0 auto', padding: '1.5rem' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: '1rem', marginBottom: '1.1rem' }}>
        <div>
          <p style={{ color: 'var(--accent)', fontSize: '0.72rem', letterSpacing: '0.25em', textTransform: 'uppercase', marginBottom: '0.5rem' }}>Staff Tools</p>
          <h1 className="font-display" style={{ fontSize: '1.9rem', margin: 0, color: 'var(--fg)' }}>Room Management</h1>
        </div>
        <button type="button" className="btn-outline" onClick={onBack} style={{ fontSize: '0.72rem', padding: '0.55rem 1rem' }}>
          <i className="fa-solid fa-arrow-left" style={{ fontSize: '0.75rem' }}></i> Back
        </button>
      </div>

      <div className="rm-row">
        <div className="rm-content">
          {activeNav === 'manage-room' && (
            <ManageRoomPanel onSubmit={handleAddRoom} onCancel={onBack} onCloseModal={() => {}} />
          )}
          {activeNav === 'guest-details' && (
            <GuestDetailsPanel rooms={rooms} onEditRoom={onEditRoom} onToast={onToast} />
          )}
        </div>
      </div>
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

  const addRoom = useCallback((roomFromDb) => {
    setRooms(prev => [...prev, roomFromDb]);
  }, []);

  const editRoom = useCallback((id, patch) => {
    setRooms(prev => prev.map(r => (r.id === id ? Object.assign({}, r, patch) : r)));
    const body = {};
    if (patch.status !== undefined) body.status = patch.status;
    if (patch.reservation !== undefined) body.reservation = patch.reservation;
    if (Object.keys(body).length === 0) return;
    pendingWrites.current += 1;
    fetch('/students/hotel/rooms/' + String(id).replace(/^db-/, ''), {
      method: 'PATCH',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': hmsCsrfToken(), 'Accept': 'application/json' },
      body: JSON.stringify(body),
    })
      .then(r => (r.ok ? r.json() : null))
      .then(data => { if (data && data.room) setRooms(prev => prev.map(r => (r.id === data.room.id ? data.room : r))); })
      .catch(() => {})
      .finally(() => { pendingWrites.current = Math.max(0, pendingWrites.current - 1); });
  }, []);

  return (
    <RoomManagementPage
      initialNav={window.HMS_ROOM_MGMT_INITIAL_NAV}
      rooms={rooms}
      onBack={() => { window.location.href = window.HMS_ROOMMANAGEMENT_URL; }}
      onAddRoom={addRoom}
      onEditRoom={editRoom}
      onToast={(msg) => window.toast && window.toast(msg)}
    />
  );
}

ReactDOM.createRoot(document.getElementById('ops-root')).render(<App />);
</script>
@endverbatim
@endsection
