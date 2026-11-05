<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="no-referrer">
<title>My Stay · {{ $brandName }}</title>
@include('hotel.partials.guest-page-head')
</head>
<body>
@include('hotel.partials.guest-page-header')

<main class="wrap">
@if ($error)
  <div class="panel empty" role="alert">
    <i class="fa-solid fa-link-slash"></i>
    <h1>Booking not available</h1>
    <p>{{ $error }}</p>
    <a class="btn" href="{{ $siteUrl }}">Go to the hotel website</a>
  </div>
@else
  <div id="app" aria-live="polite">
    <div class="skeleton" style="height: 2.4rem; width: 60%; max-width: 420px;"></div>
    <div class="skeleton" style="height: 190px; margin-top: 1.5rem;"></div>
    <div class="skeleton" style="height: 130px; margin-top: 1.25rem;"></div>
  </div>
@endif
</main>

@unless ($error)
{{-- The folio and receipt sheet, the same one the Front Desk prints. --}}
<script src="{{ asset('js/hms-guest-folio.js') }}?v={{ filemtime(public_path('js/hms-guest-folio.js')) }}"></script>
<script>
(function () {
  var DATA_URL = @json($dataUrl);
  var PRINT_IDENTITY = @json($printIdentity ?? null);
  var app = document.getElementById('app');
  var state = { data: null, gone: null, json: null };

  function esc(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function peso(n) { return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
  // 'YYYY-MM-DD' is a calendar day at the hotel, read as such, not as UTC midnight.
  function day(ymd) {
    var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(ymd || '');
    return m ? new Date(+m[1], +m[2] - 1, +m[3]).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : '—';
  }
  function clock(hhmm) {
    var m = /^(\d{1,2}):(\d{2})/.exec(hhmm || '');
    if (!m) return '';
    var h = +m[1];
    return ' · ' + ((h % 12) || 12) + ':' + m[2] + (h < 12 ? ' AM' : ' PM');
  }

  // The stay's own statuses, said for a guest.
  var STATUS = {
    'Booked': ['confirmed', 'Your room is booked. Check in at the Front Desk on your arrival date.'],
    'Arrived': ['confirmed', 'You are at the hotel. The Front Desk will hand you your key.'],
    'Checked In': ['seated', 'You are staying with us. Room service and extras you order show on your bill below.'],
    'Pending Room Inspection': ['seated', 'You are checking out. Housekeeping is checking the room before your final bill.'],
    'Checked Out': ['completed', 'Your stay has ended. Your official receipt is below. Thank you for staying with us.'],
    'Cancelled': ['cancelled', 'This booking was cancelled. Please contact the Front Desk if you think this is a mistake.'],
  };
  var STEPS = ['Booked', 'Checked In', 'Checking Out', 'Checked Out'];
  var STEP_OF = { 'Booked': 0, 'Arrived': 0, 'Checked In': 1, 'Pending Room Inspection': 2, 'Checked Out': 3 };

  function badge(status) {
    var s = STATUS[status] || ['closed', ''];
    return '<span class="badge s-' + s[0] + '">' + esc(status) + '</span>';
  }

  function stayHtml(st, bill) {
    var s = STATUS[st.status] || ['closed', 'This booking is no longer active.'];
    var step = STEP_OF[st.status];
    var steps = step == null ? '' : '<ol class="steps" style="grid-template-columns:repeat(4,1fr)" aria-label="Stay progress">' + STEPS.map(function (name, i) {
      return '<li class="' + (i <= step ? 'done' : '') + '"' + (i === step ? ' aria-current="step"' : '') + '>' + name + '</li>';
    }).join('') + '</ol>';
    var guests = st.occupancy || (st.guestCount ? st.guestCount + (st.guestCount === 1 ? ' guest' : ' guests') : '—');

    return ''
      + '<p class="eyebrow">Room booking · #' + esc(st.reference) + '</p>'
      + '<h1>My Stay</h1>'
      + '<section class="section" aria-labelledby="h-details">'
      +   '<div class="section-head"><h2 id="h-details">Stay details</h2></div>'
      +   '<div class="panel details">'
      +     '<div class="table-hero"><span class="muted">Your room</span><b>' + esc(st.roomName || 'Room') + '</b>'
      +       (st.roomCategory ? '<span class="muted">' + esc(st.roomCategory) + '</span>' : '') + '</div>'
      +     '<dl class="facts">'
      +       '<div><dt>Booking</dt><dd>#' + esc(st.reference) + '</dd></div>'
      +       '<div><dt>Guest name</dt><dd>' + esc((bill && bill.guestName) || '—') + '</dd></div>'
      +       '<div><dt>Check-in</dt><dd>' + esc(day(st.checkIn) + clock(st.checkInTime)) + '</dd></div>'
      +       '<div><dt>Check-out</dt><dd>' + esc(day(st.checkOut)) + '</dd></div>'
      +       '<div><dt>Guests</dt><dd>' + esc(guests) + '</dd></div>'
      +       '<div><dt>Status</dt><dd>' + badge(st.status) + '</dd></div>'
      +       (st.promoName ? '<div><dt>Package</dt><dd>' + esc(st.promoName) + '</dd></div>' : '')
      +       '<div><dt>Balance</dt><dd>' + esc(peso(st.balance)) + '</dd></div>'
      +     '</dl>'
      +   '</div>'
      + '</section>'
      + '<section class="section" aria-labelledby="h-status">'
      +   '<div class="section-head"><h2 id="h-status">Stay status</h2><span class="updated" id="updated"></span></div>'
      +   '<div class="panel">'
      +     '<div class="status-row">' + badge(st.status) + '</div>'
      +     '<p class="status-note">' + esc(s[1]) + '</p>'
      +     steps
      +   '</div>'
      + '</section>';
  }

  /* The whole bill as the Front Desk prints it: the room, every room-service dish,
     the extras and other charges, discounts, service charge and payments. The
     Statement of Account while the stay is open; the Official Receipt once out. */
  function billHtml(st, bill) {
    if (!bill || !window.HmsGuestFolio || st.status === 'Cancelled') return '';
    var official = st.status === 'Checked Out';
    return '<section class="section" aria-labelledby="h-bill">'
      + '<div class="section-head"><h2 id="h-bill">' + (official ? 'My Receipt' : 'My Bill') + '</h2>'
      + '<button type="button" class="tab" id="print-bill"><i class="fa-solid fa-print"></i> Print</button></div>'
      + '<div id="bill-sheet">' + window.HmsGuestFolio.html({ hotel: PRINT_IDENTITY || {}, bill: bill, official: official }) + '</div>'
      + '</section>';
  }

  function render() {
    if (state.gone) {
      app.innerHTML = '<div class="panel empty" role="alert"><i class="fa-solid fa-link-slash"></i><h1>Booking not available</h1><p>'
        + esc(state.gone) + '</p></div>';
      return;
    }
    var d = state.data;
    app.innerHTML = stayHtml(d.stay, d.bill) + billHtml(d.stay, d.bill);
    var up = document.getElementById('updated');
    if (up) up.textContent = 'Updated ' + new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit' });
  }

  app.addEventListener('click', function (e) {
    if (e.target.closest('#print-bill')) {
      var sheet = document.querySelector('#bill-sheet .receipt-print');
      if (sheet && window.HmsGuestFolio) window.HmsGuestFolio.print(sheet);
    }
  });

  function load() {
    if (state.gone) return;
    fetch(DATA_URL, { headers: { Accept: 'application/json' }, cache: 'no-store', credentials: 'same-origin' })
      .then(function (r) {
        return r.json().then(function (body) {
          if (r.status === 404) { state.gone = body.message || 'This booking is not available.'; return; }
          if (!r.ok) throw new Error();
          // Redraw only on a change, so a poll does not reset the scroll.
          var json = JSON.stringify(body);
          if (json === state.json) return 'same';
          state.json = json;
          state.data = body;
        });
      })
      .then(function (same) {
        if (same === 'same') {
          var up = document.getElementById('updated');
          if (up) up.textContent = 'Updated ' + new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit' });
        } else if (state.data || state.gone) {
          render();
        }
      })
      // A dropped connection keeps the last good view; the next poll tries again.
      .catch(function () {});
  }

  load();
  // The Front Desk, room service and housekeeping change the stay; the site's own poll rate.
  setInterval(function () { if (!document.hidden) load(); }, 8000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) load(); });
})();
</script>
@endunless
</body>
</html>
