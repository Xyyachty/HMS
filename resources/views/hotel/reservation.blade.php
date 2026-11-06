<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<meta name="referrer" content="no-referrer">
<title>My Table Reservation · {{ $brandName }}</title>
@include('hotel.partials.guest-page-head')
</head>
<body>
@include('hotel.partials.guest-page-header')

<main class="wrap">
@if ($error)
  <div class="panel empty" role="alert">
    <i class="fa-solid fa-link-slash"></i>
    <h1>Reservation not available</h1>
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
{{-- The Dine-in Final Bill sheet, the same one the restaurant prints. --}}
<script src="{{ asset('js/hms-date-input.js') }}?v={{ filemtime(public_path('js/hms-date-input.js')) }}"></script>
<script src="{{ asset('js/hms-dinein-bill.js') }}?v={{ filemtime(public_path('js/hms-dinein-bill.js')) }}"></script>
<script>
(function () {
  var DATA_URL = @json($dataUrl);
  var PRINT_IDENTITY = @json($printIdentity ?? null);
  var app = document.getElementById('app');
  var state = { data: null, category: 'All', gone: null };

  // Guest names and menu text are typed by people: everything goes in as text.
  function esc(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function peso(n) { return '₱' + Number(n || 0).toLocaleString('en-PH'); }
  function when(iso, opts) {
    return iso ? new Date(iso).toLocaleString('en-US', Object.assign({ timeZone: 'Asia/Manila' }, opts)) : '—';
  }

  var STATUS = {
    Confirmed: ['confirmed', 'Your table is reserved and waiting for you. Give your name to the restaurant staff when you arrive.'],
    Seated: ['seated', 'You have arrived and are seated. Enjoy your meal.'],
    Completed: ['completed', 'Your visit has ended. Thank you for dining with us.'],
    Cancelled: ['cancelled', 'This reservation was cancelled by the restaurant. Please contact the front desk if you think this is a mistake.'],
    'No-Show': ['no-show', 'This reservation was released because nobody arrived within the grace period. Please contact the front desk to book again.'],
    Closed: ['closed', 'This reservation is no longer active.'],
  };
  var STEPS = ['Confirmed', 'Seated', 'Completed'];

  function badge(status) {
    var s = STATUS[status] || STATUS.Closed;
    return '<span class="badge s-' + s[0] + '">' + esc(status) + '</span>';
  }

  function reservationHtml(r) {
    var s = STATUS[r.status] || STATUS.Closed;
    var step = STEPS.indexOf(r.status);
    var steps = step < 0 ? '' : '<ol class="steps" aria-label="Reservation progress">' + STEPS.map(function (name, i) {
      return '<li class="' + (i <= step ? 'done' : '') + '"' + (i === step ? ' aria-current="step"' : '') + '>' + name + '</li>';
    }).join('') + '</ol>';

    return ''
      + '<p class="eyebrow">Restaurant reservation · ' + esc(r.reference) + '</p>'
      + '<h1>My Restaurant Reservation</h1>'
      + '<section class="section" aria-labelledby="h-details">'
      +   '<div class="section-head"><h2 id="h-details">Reservation details</h2></div>'
      +   '<div class="panel details">'
      +     '<div class="table-hero"><span class="muted">Your table</span><b>' + esc(r.tableName) + '</b></div>'
      +     '<dl class="facts">'
      +       '<div><dt>Reference</dt><dd>' + esc(r.reference) + '</dd></div>'
      +       '<div><dt>Guest name</dt><dd>' + esc(r.guestName) + '</dd></div>'
      +       '<div><dt>Date</dt><dd>' + esc(HmsDate.date(r.reservedFor, { timeZone: 'Asia/Manila' }) || '—') + '</dd></div>'
      +       '<div><dt>Time</dt><dd>' + esc(when(r.reservedFor, { hour: 'numeric', minute: '2-digit' })) + '</dd></div>'
      +       '<div><dt>Guests</dt><dd>' + esc(r.partySize) + (r.partySize === 1 ? ' person' : ' persons') + '</dd></div>'
      +       '<div><dt>Status</dt><dd>' + badge(r.status) + '</dd></div>'
      +     '</dl>'
      +   '</div>'
      + '</section>'
      + '<section class="section" aria-labelledby="h-status">'
      +   '<div class="section-head"><h2 id="h-status">Reservation status</h2><span class="updated" id="updated"></span></div>'
      +   '<div class="panel">'
      +     '<div class="status-row">' + badge(r.status) + '</div>'
      +     '<p class="status-note">' + esc(s[1]) + '</p>'
      +     steps
      +   '</div>'
      + '</section>';
  }

  /* The guest's bill: what they have ordered so far while seated, then the receipt
     once the restaurant has settled it. Drawn by hms-dinein-bill.js. */
  function billHtml(b) {
    if (!b || !window.HmsDineInBill) return '';
    var paid = b.state === 'paid';
    var sheet = window.HmsDineInBill.html({
      hotel: PRINT_IDENTITY || {},
      bill: paid ? null : b.bill,
      payment: paid ? b.payment : null,
      note: paid ? '' : 'Your bill so far. Senior Citizen / PWD discounts are applied by the cashier when you pay.',
    });
    return '<section class="section" aria-labelledby="h-bill">'
      + '<div class="section-head"><h2 id="h-bill">' + (paid ? 'My Receipt' : 'My Bill') + '</h2>'
      + '<button type="button" class="tab" id="print-bill"><i class="fa-solid fa-print"></i> Print</button></div>'
      + '<div id="bill-sheet">' + sheet + '</div>'
      + '</section>';
  }

  function menuHtml(menu) {
    var items = (menu && menu.items) || [];
    if (!items.length) {
      return '<div class="panel empty"><i class="fa-solid fa-utensils"></i><p>The restaurant has not published its menu yet.</p></div>';
    }
    // The team's own course order, then any course only an item names.
    var order = (menu.categories || []).slice();
    items.forEach(function (it) { if (it.category && order.indexOf(it.category) < 0) order.push(it.category); });
    var cats = order.filter(function (c) { return items.some(function (it) { return it.category === c; }); });
    if (state.category !== 'All' && cats.indexOf(state.category) < 0) state.category = 'All';

    var shown = items
      .filter(function (it) { return state.category === 'All' || it.category === state.category; })
      .sort(function (a, b) { return order.indexOf(a.category) - order.indexOf(b.category); });

    return '<div class="tabs" role="group" aria-label="Menu courses">'
      + ['All'].concat(cats).map(function (c) {
        return '<button type="button" class="tab" data-cat="' + esc(c) + '" aria-pressed="' + (c === state.category) + '">' + esc(c) + '</button>';
      }).join('')
      + '</div><div class="menu">'
      + shown.map(function (it) {
        var out = !(it.stock > 0);
        return '<article class="dish' + (out ? ' is-out' : '') + '">'
          + (it.img ? '<img src="' + esc(it.img) + '" alt="' + esc(it.name) + '" loading="lazy" referrerpolicy="no-referrer">'
                    : '<div class="noimg" aria-hidden="true"><i class="fa-solid fa-bowl-food"></i></div>')
          + '<div><div class="dish-top"><span class="dish-name">' + esc(it.name) + '</span>'
          + '<span class="dish-price">' + peso(it.price) + '</span></div>'
          + (it.sub ? '<p class="dish-sub">' + esc(it.sub) + '</p>' : '')
          + (out ? '<span class="badge s-cancelled">Not available</span>' : '<span class="badge s-seated">Available</span>')
          + '</div></article>';
      }).join('')
      + '</div>';
  }

  function render() {
    if (state.gone) {
      app.innerHTML = '<div class="panel empty" role="alert"><i class="fa-solid fa-link-slash"></i><h1>Reservation not available</h1><p>'
        + esc(state.gone) + '</p></div>';
      return;
    }
    var d = state.data;
    app.innerHTML = reservationHtml(d.reservation)
      + billHtml(d.bill)
      + '<section class="section" aria-labelledby="h-menu">'
      +   '<div class="section-head"><h2 id="h-menu">Restaurant menu</h2><span class="muted" style="font-size:0.85rem">For viewing. Order at your table.</span></div>'
      +   menuHtml(d.menu)
      + '</section>';
    var up = document.getElementById('updated');
    if (up) up.textContent = 'Updated ' + new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', second: '2-digit' });
  }

  app.addEventListener('click', function (e) {
    if (e.target.closest('#print-bill')) {
      var sheet = document.querySelector('#bill-sheet .dn-sheet');
      if (sheet && window.HmsDineInBill) window.HmsDineInBill.print(sheet);
      return;
    }
    var tab = e.target.closest('.tab');
    if (!tab) return;
    state.category = tab.getAttribute('data-cat');
    render();
  });

  function load() {
    if (state.gone) return;
    fetch(DATA_URL, { headers: { Accept: 'application/json' }, cache: 'no-store', referrerPolicy: 'no-referrer' })
      .then(function (r) {
        return r.json().then(function (body) {
          if (r.status === 404 || r.status === 410) { state.gone = body.message || 'This reservation link is no longer valid.'; return; }
          if (!r.ok) throw new Error();
          // Redraw only on a change, so a poll does not reset focus or the scroll.
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
  // Front Desk and Restaurant Management change the status; the site's own poll rate.
  setInterval(function () { if (!document.hidden) load(); }, 8000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) load(); });
})();
</script>
@endunless
</body>
</html>
