/*
 * The printed Dine-in Final Bill, shared by the Restaurant's Orders page and the
 * guest's own reservation page, so both show the same paper.
 *
 * HmsDineInBill.html({ hotel, bill, payment, tendered, change }) returns the sheet
 * as an HTML string (every value typed by a person is escaped):
 *   hotel   { name, address, phone, email, logo, ink } from the team's saved design
 *           (HotelTemplateBuilder::printIdentity)
 *   bill    the bill the server priced, before payment: the whole total is still due
 *   payment the payment the server recorded, after: what was actually paid
 *   tendered, change  cash handed over and handed back, known only right after paying
 * HmsDineInBill.print(sheetElement) prints that sheet alone, on white, from a frame.
 */
(function () {
  function esc(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function money(n) {
    return (Number(n) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function pad2(n) { return String(n).padStart(2, '0'); }
  function shortDay(d) { return isNaN(d) ? '' : pad2(d.getDate()) + '/' + pad2(d.getMonth() + 1) + '/' + d.getFullYear(); }
  function longDay(d) { return pad2(d.getDate()) + ' ' + d.toLocaleString('en-US', { month: 'short' }) + ' ' + d.getFullYear(); }
  // Only a colour value reaches the stylesheet.
  function safeColor(v) { return /^(#[0-9a-f]{3,8}|rgba?\([\d\s.,%]+\))$/i.test(String(v || '').trim()) ? String(v).trim() : '#1f2a44'; }
  function safeSrc(v) { v = String(v || ''); return /^(https?:)?\/\/|^\/|^data:image\//i.test(v) ? v : ''; }

  var STYLE = ''
    + '.dn-sheet{background:#fff;color:#1a1a1a;border-radius:10px;padding:1.5rem 1.6rem 1.4rem;font-family:Georgia,"Times New Roman",serif;font-size:13.5px;line-height:1.45;font-variant-numeric:lining-nums;text-align:left}'
    + '.dn-sheet *{box-sizing:border-box}'
    + '.dn-sheet .h-top{text-align:center}'
    + '.dn-sheet .h-logo{max-height:56px;max-width:160px;object-fit:contain;display:block;margin:0 auto .4rem}'
    + '.dn-sheet .h-name{margin:0;color:var(--ink);font-size:1.55rem;font-weight:700;letter-spacing:.02em;text-transform:uppercase;font-family:Georgia,serif}'
    + '.dn-sheet .h-line{margin:.15rem 0 0;color:#2b2b2b}'
    + '.dn-sheet .rule{border:0;border-top:2px solid var(--ink);margin:.8rem 0 0}'
    + '.dn-sheet .rule+.rule{border-top-width:1px;margin-top:3px}'
    + '.dn-sheet .title{text-align:center;color:var(--ink);font-size:1.45rem;font-weight:700;margin:.9rem 0 .7rem;letter-spacing:.02em}'
    + '.dn-sheet .meta{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start}'
    + '.dn-sheet .meta dl{margin:0;display:grid;grid-template-columns:auto auto 1fr;column-gap:.6rem;row-gap:.15rem}'
    + '.dn-sheet .meta dt,.dn-sheet .meta dd{margin:0}'
    + '.dn-sheet .printed{white-space:nowrap}'
    + '.dn-sheet table{width:100%;border-collapse:collapse;margin-top:1rem;font-family:Georgia,serif}'
    + '.dn-sheet thead th{background:color-mix(in srgb,var(--ink) 9%,#fff);color:var(--ink);font-family:Arial,sans-serif;font-size:.72rem;font-weight:700;letter-spacing:.04em;text-transform:uppercase;text-align:left;padding:.55rem .4rem;border-top:2px solid var(--ink);border-bottom:1px solid var(--ink)}'
    + '.dn-sheet td{padding:.4rem;vertical-align:top}'
    + '.dn-sheet tbody tr:first-child td{padding-top:.7rem}'
    + '.dn-sheet tbody tr:last-child td{padding-bottom:.7rem;border-bottom:1.5px solid var(--ink)}'
    + '.dn-sheet .num{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}'
    + '.dn-sheet .qty{text-align:center}'
    + '.dn-sheet .from{display:block;font-family:Arial,sans-serif;font-size:.7rem;color:#6b6b6b}'
    + '.dn-sheet .sum{display:flex;justify-content:flex-end;gap:2rem;align-items:baseline;padding:.65rem .4rem 0;color:var(--ink);font-weight:700}'
    + '.dn-sheet .sum b{font-size:1.05rem}'
    + '.dn-sheet .adj{display:flex;justify-content:space-between;gap:1rem;padding:.15rem .4rem 0 1.6rem}'
    + '.dn-sheet .adj+.sum{margin-top:.3rem;border-top:1px solid color-mix(in srgb,var(--ink) 35%,#fff)}'
    + '.dn-sheet .sec{margin:1rem 0 .3rem;color:var(--ink);font-weight:700;text-transform:uppercase;letter-spacing:.02em}'
    + '.dn-sheet .pay{display:flex;justify-content:space-between;gap:1rem;padding:.1rem .4rem .1rem 1.6rem}'
    + '.dn-sheet .muted{color:#6b6b6b}'
    + '.dn-sheet .due{display:flex;justify-content:flex-end;gap:2rem;align-items:baseline;margin-top:.6rem;padding:.55rem .4rem;border-top:1.5px solid var(--ink);border-bottom:1.5px solid var(--ink);color:var(--ink);font-weight:700}'
    + '.dn-sheet .due b{font-size:1.5rem;color:#1a1a1a}'
    + '.dn-sheet .due.is-owed b{color:#b91c1c}'
    + '.dn-sheet .note{margin:.5rem .4rem 0;color:#6b6b6b;font-size:.8rem;font-style:italic}'
    + '.dn-sheet .sign{margin-top:1.8rem;display:flex;align-items:flex-end;gap:.5rem}'
    + '.dn-sheet .sign span:last-child{flex:0 1 18rem;border-bottom:1px solid #1a1a1a;height:1.2rem}'
    + '.dn-sheet .sign-name{margin:.25rem 0 0;padding-left:9.5rem;font-style:italic;color:var(--ink)}'
    + '@media (max-width:520px){.dn-sheet{padding:1rem;font-size:12.5px}.dn-sheet .h-name{font-size:1.2rem}.dn-sheet .meta{flex-direction:column}.dn-sheet .unit{display:none}.dn-sheet .sign-name{padding-left:0}}';

  function html(o) {
    o = o || {};
    var hotel = o.hotel || {};
    var payment = o.payment || null;
    var bill = o.bill || null;
    var src = payment || bill || {};
    var items = src.items || [];
    var orderIds = (src.orderIds || []).map(Number).filter(Boolean);
    var billNo = 'BIL-' + String(orderIds.length ? Math.min.apply(null, orderIds) : (src.tableId || src.id || '')).padStart(6, '0');
    var tableNames = String(src.tableName || '').split(' + ').filter(Boolean);
    var multi = tableNames.length > 1;
    var totalDue = payment ? Number(payment.totalDue) || 0 : Number(bill && bill.total) || 0;
    var subtotal = src.subtotal != null ? Number(src.subtotal) || 0 : totalDue;
    var discounts = src.discounts || [];
    var serviceCharge = Number(src.serviceCharge) || 0;
    var paid = payment ? Number(payment.amountPaid) || 0 : 0;
    var balance = payment ? Math.max(0, Number(payment.balance) || 0) : totalDue;
    var tendered = Number(o.tendered) || 0;
    var change = Number(o.change) || 0;
    var printed = new Date();
    var paidOn = payment && payment.paidAt ? new Date(payment.paidAt) : printed;
    var day = function (iso) { return shortDay(iso ? new Date(iso) : paidOn); };
    var contact = [hotel.phone ? 'Tel: ' + hotel.phone : '', hotel.email || ''].filter(Boolean).join(' | ');
    // "Table 10" reads as 10 in the Tables row, like the paper form; any other name stays whole.
    var tableLabel = tableNames.map(function (n) { return n.replace(/^Table\s+/i, ''); }).join(', ');
    var guests = src.partySize ? src.partySize + (Number(src.partySize) === 1 ? ' Guest' : ' Guests') : '—';
    var logo = safeSrc(hotel.logo);

    var rows = items.length ? items.map(function (i) {
      var unit = i.price != null ? i.price : (Number(i.line) || 0) / (Number(i.qty) || 1);
      return '<tr><td style="white-space:nowrap">' + esc(day(i.placedAt)) + '</td>'
        + '<td>' + esc(i.name) + (multi && i.tableName ? '<span class="from">' + esc(i.tableName) + '</span>' : '') + '</td>'
        + '<td class="qty">' + esc(i.qty) + '</td>'
        + '<td class="num unit">' + money(unit) + '</td>'
        + '<td class="num">' + money(i.line) + '</td></tr>';
    }).join('') : '<tr><td colspan="5" class="muted">Nothing ordered.</td></tr>';

    var adjustments = '';
    if (discounts.length || serviceCharge > 0) {
      adjustments = discounts.map(function (d) {
        return '<div class="adj"><span>Less: VAT exemption – ' + esc(d.type) + ' (' + esc(d.fullName) + ', ID ' + esc(d.idNumber) + ')</span><span class="num">-' + money(d.vat) + '</span></div>'
          + '<div class="adj"><span>Less: 20% ' + esc(d.type) + ' discount</span><span class="num">-' + money(d.discount) + '</span></div>';
      }).join('')
        + (serviceCharge > 0 ? '<div class="adj"><span>Service charge (' + esc(src.serviceChargeRate) + '%)</span><span class="num">' + money(serviceCharge) + '</span></div>' : '')
        + '<div class="sum"><span>TOTAL AMOUNT</span><b>₱' + money(totalDue) + '</b></div>';
    }

    var payments = payment && paid > 0
      ? '<div class="pay"><span>' + esc(day(payment.paidAt)) + ' &nbsp; ' + esc(payment.method) + '</span><span class="num">-' + money(paid) + '</span></div>'
      : '<div class="pay muted"><span>None yet</span><span class="num">0.00</span></div>';
    payments += '<div class="pay"><span>Payment Method</span><span>' + esc(payment ? payment.method : '—') + '</span></div>';
    // Cash only: card and e-wallet payments are for the exact amount. A receipt opened
    // later than the payment does not know the cash handed over, so it shows the paid amount.
    if (payment && payment.method === 'Cash') {
      payments += '<div class="pay"><span>Cash Received</span><span class="num">₱' + money(tendered > 0 ? tendered : paid) + '</span></div>'
        + '<div class="pay"><span>Change</span><span class="num">₱' + money(tendered > 0 ? change : 0) + '</span></div>';
    }

    return '<div class="dn-sheet" style="--ink:' + safeColor(hotel.ink) + '"><style>' + STYLE + '</style>'
      + '<div class="h-top">'
      +   (logo ? '<img class="h-logo" src="' + esc(logo) + '" alt="" referrerpolicy="no-referrer">' : '')
      +   '<h3 class="h-name">' + esc(hotel.name || 'Restaurant') + '</h3>'
      +   (hotel.address ? '<p class="h-line">' + esc(hotel.address) + '</p>' : '')
      +   (contact ? '<p class="h-line">' + esc(contact) + '</p>' : '')
      + '</div><hr class="rule"><hr class="rule">'
      + '<p class="title">FINAL BILL – DINE-IN</p>'
      + '<div class="meta"><dl>'
      +   '<dt>Bill No.</dt><dd>:</dd><dd>' + esc(billNo) + '</dd>'
      +   '<dt>Guest Name</dt><dd>:</dd><dd>' + esc(src.guestName || 'Guest') + '</dd>'
      +   '<dt>No. of Guests</dt><dd>:</dd><dd>' + esc(guests) + '</dd>'
      +   '<dt>' + (multi ? 'Tables' : 'Table') + '</dt><dd>:</dd><dd>' + esc(tableLabel || '—') + '</dd>'
      + '</dl><span class="printed">Date Printed: ' + esc(longDay(printed)) + '</span></div>'
      + '<table><thead><tr><th>Date</th><th>Description</th><th class="qty">Qty</th><th class="num unit">Unit Price (PHP)</th><th class="num">Amount (PHP)</th></tr></thead>'
      + '<tbody>' + rows + '</tbody></table>'
      + '<div class="sum"><span>SUBTOTAL</span><b>₱' + money(subtotal) + '</b></div>'
      + adjustments
      + '<p class="sec">Payments &amp; Credits Applied:</p>' + payments
      + '<div class="due' + (balance > 0 ? ' is-owed' : '') + '"><span>TOTAL BALANCE</span><b>' + money(balance) + '</b></div>'
      + (o.note ? '<p class="note">' + esc(o.note) + '</p>' : '')
      + '<div class="sign"><span>Guest Signature:</span><span></span></div>'
      + '<p class="sign-name">' + esc(src.guestName || 'Guest') + '</p>'
      + '</div>';
  }

  function print(sheet) {
    if (!sheet) return;
    var frame = document.createElement('iframe');
    frame.setAttribute('aria-hidden', 'true');
    frame.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;';
    document.body.appendChild(frame);
    // Set after appending: the empty frame's own load has already fired by then.
    frame.onload = function () {
      frame.contentWindow.focus();
      frame.contentWindow.print();
      setTimeout(function () { frame.remove(); }, 1000);
    };
    frame.contentDocument.open();
    frame.contentDocument.write('<!doctype html><html><head><meta charset="utf-8"><title>Final Bill</title>'
      + '<style>@page{margin:12mm}html,body{margin:0;background:#fff}.dn-sheet{border-radius:0!important;padding:0!important}</style></head><body>'
      + sheet.outerHTML + '</body></html>');
    frame.contentDocument.close();
  }

  window.HmsDineInBill = { html: html, print: print };
})();
