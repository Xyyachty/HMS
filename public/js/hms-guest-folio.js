/*
 * The room guest's folio on paper, in the client's statement-of-account layout,
 * shared by Front Desk (Guest Information) and the guest's own My Stay page so
 * both show the same sheet.
 *
 * HmsGuestFolio.html({ hotel, bill, cash, official }) returns the sheet as an HTML
 * string; every value typed by a person is escaped.
 *   hotel     { name, address, phone, email } from the team's saved design
 *             (HotelTemplateBuilder::printIdentity)
 *   bill      the bill the server priced (HotelBooking::toBillArray()); the sheet
 *             shows its charges, discounts and payments and never adds any
 *   official  the receipt handed over at check-out (receipt number, payment
 *             method, thank-you); otherwise the running Statement of Account
 *   cash      { tendered, change } for a cash check-out, known only right then
 * HmsGuestFolio.print(sheetElement) prints that sheet alone, on white, from a
 * hidden frame, so a long folio runs onto a second page instead of being cut off.
 */
(function () {
  function esc(v) {
    return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function pad2(n) { return String(n).padStart(2, '0'); }
  // Folio format: "06 Nov 2026" in the header, "04/11/2026" on each line.
  function peso(n) {
    return (Number(n) < 0 ? '−₱' : '₱') + Math.abs(Number(n) || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }
  function toDate(v) {
    if (!v) return null;
    var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(v);
    var d = m ? new Date(+m[1], +m[2] - 1, +m[3]) : new Date(v);
    return isNaN(d) ? null : d;
  }
  function longDay(d) { return d ? pad2(d.getDate()) + ' ' + d.toLocaleString('en-US', { month: 'short' }) + ' ' + d.getFullYear() : '—'; }
  function shortDay(v) { var d = toDate(v); return d ? pad2(d.getDate()) + '/' + pad2(d.getMonth() + 1) + '/' + d.getFullYear() : ''; }
  function hhmm(d) { return d ? pad2(d.getHours()) + ':' + pad2(d.getMinutes()) : ''; }

  var STYLE = ''
    + '.receipt-print{background:#fff;color:#1a1a1a;border-radius:.625rem;padding:1.3rem 1.4rem;font-size:.86rem;border:1px solid #e5e2dd;text-align:left;line-height:1.45}'
    + '.receipt-print *{box-sizing:border-box}'
    + '.receipt-print .rc-top{text-align:center;padding-bottom:.9rem;border-bottom:1px dashed #c9c3ba}'
    + '.receipt-print .rc-top h3{margin:0 0 .25rem;font-size:1.25rem;font-family:var(--font-display,"Playfair Display",Georgia,serif);color:#1a1a1a}'
    + '.receipt-print .rc-top p{margin:.1rem 0;color:#5f5a55;font-size:.8rem}'
    + '.receipt-print .rc-title{font-weight:700;color:#1a1a1a!important;letter-spacing:.06em;text-transform:uppercase}'
    + '.receipt-print .rc-block{padding:.8rem 0;border-bottom:1px dashed #c9c3ba}'
    + '.receipt-print .rc-head{margin:0 0 .4rem;font-size:.7rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#5f5a55}'
    + '.receipt-print .rc-line{display:flex;justify-content:space-between;gap:1rem;padding:.18rem 0;font-size:.8rem}'
    + '.receipt-print .rc-amt{flex:none;font-variant-numeric:tabular-nums}'
    + '.receipt-print .rc-unit{display:block;font-size:.72rem;color:#7a7570}'
    + '.receipt-print .rc-strong{font-weight:700;margin-top:.25rem}'
    + '.receipt-print .rc-due{color:#c81e3a}'
    + '.receipt-print .rc-thanks{margin:.9rem 0 0;text-align:center;color:#5f5a55;font-size:.8rem}'
    + '.receipt-print .rc-info{display:grid;grid-template-columns:1fr 1fr;gap:.2rem 1.2rem;margin:0;font-size:.78rem}'
    + '.receipt-print .rc-info div{display:flex;gap:.4rem;min-width:0}'
    + '.receipt-print .rc-info dt{flex:none;width:5.4rem;color:#5f5a55}'
    + '.receipt-print .rc-info dd{margin:0;font-weight:600;overflow-wrap:anywhere}'
    + '.receipt-print .rc-table{width:100%;border-collapse:collapse;font-size:.76rem}'
    + '.receipt-print .rc-table th{text-align:left;font-size:.62rem;letter-spacing:.08em;text-transform:uppercase;color:#5f5a55;padding:0 .35rem .35rem;border-bottom:1px solid #c9c3ba}'
    + '.receipt-print .rc-table td{padding:.3rem .35rem;vertical-align:top}'
    + '.receipt-print .rc-table th:first-child,.receipt-print .rc-table td:first-child{padding-left:0}'
    + '.receipt-print .rc-table th:last-child,.receipt-print .rc-table td:last-child{padding-right:0}'
    + '.receipt-print .rc-table .num{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}'
    + '.receipt-print .rc-table .rc-date{white-space:nowrap;color:#5f5a55}'
    + '.receipt-print .rc-sum .rc-line{padding-left:35%}'
    + '.receipt-print .rc-sign{margin:1.4rem 0 0;display:flex;flex-direction:column;align-items:flex-start;font-size:.78rem;color:#5f5a55}'
    + '.receipt-print .rc-sign span:first-child{width:14rem;max-width:100%;border-bottom:1px solid #1a1a1a;height:1.4rem}'
    + '@media (max-width:480px){.receipt-print .rc-info{grid-template-columns:1fr}.receipt-print .rc-table .rc-unitcol{display:none}.receipt-print .rc-sum .rc-line{padding-left:0}}';

  function line(label, amount, cls, unit) {
    return '<div class="rc-line' + (cls ? ' ' + cls : '') + '"><span>' + esc(label)
      + (unit ? '<span class="rc-unit">' + esc(unit) + '</span>' : '') + '</span><span class="rc-amt">' + esc(amount) + '</span></div>';
  }

  function html(o) {
    o = o || {};
    var hotel = o.hotel || {};
    var bill = o.bill || {};
    var official = !!o.official;
    var cash = o.cash || null;
    var issued = official && bill.checkedOutAt ? new Date(bill.checkedOutAt) : new Date();
    var ymd = issued.getFullYear() + pad2(issued.getMonth() + 1) + pad2(issued.getDate());
    var number = 'OR-' + ymd + '-' + String(bill.bookingId || '').padStart(5, '0');
    var service = bill.roomService || { orders: [] };
    var addons = bill.addons || { items: [] };
    var extras = bill.otherCharges || { items: [] };
    var balance = Number(bill.balance) || 0;
    var refundDue = Number(bill.refundDue) || 0;
    var room = bill.room || {};
    var nights = Math.max(1, Math.ceil((Number(room.blocks) || 1) / 2));
    var allPayments = bill.payments || [];
    var methods = allPayments.filter(function (p) { return p.amountPaid > 0; }).map(function (p) { return p.method; })
      .filter(function (m, i, a) { return m && a.indexOf(m) === i; });
    var checkOutAt = bill.checkedOutAt ? new Date(bill.checkedOutAt) : null;
    var checkInText = longDay(toDate(bill.checkIn)) + (bill.checkInTime ? ' (' + String(bill.checkInTime).slice(0, 5) + ')' : '');
    var checkOutText = checkOutAt ? longDay(checkOutAt) + ' (' + hhmm(checkOutAt) + ')' : longDay(toDate(bill.checkOut));

    // One row per charge, labelled by the department it came from.
    var rows = [];
    rows.push(room.promo
      ? { date: bill.checkIn, desc: 'Front Office - ' + room.promo + ' package', qty: 1, unit: room.subtotal, amount: room.subtotal }
      : { date: bill.checkIn, desc: 'Front Office - ' + (bill.roomCategory || 'Room') + ' Room', sub: 'per ' + room.blockHours + ' hrs', qty: room.blocks, unit: room.rate, amount: room.subtotal });
    ((bill.children && bill.children.items) || []).forEach(function (c) {
      rows.push({ date: bill.checkIn, desc: 'Front Office - Child, age ' + c.age, sub: c.rate ? 'per night' : c.label, qty: c.rate ? c.nights : 1, unit: c.rate || 0, amount: c.line || 0 });
    });
    (service.orders || []).forEach(function (order) {
      (order.items || []).forEach(function (item) {
        rows.push({ date: order.placedAt, desc: 'F&B - In-Room Dining (' + item.name + ')', qty: item.qty, unit: item.price, amount: item.line });
      });
    });
    (addons.items || []).forEach(function (a) {
      rows.push({ date: bill.checkIn, desc: 'Housekeeping - ' + a.name, sub: a.perNight ? '× ' + a.nights + ' night' + (a.nights === 1 ? '' : 's') : '', qty: a.qty, unit: a.unitPrice, amount: a.line });
    });
    (extras.items || []).forEach(function (c) {
      rows.push({ date: c.addedAt, desc: 'Front Office - ' + c.description, qty: 1, unit: c.amount, amount: c.amount });
    });

    // The VAT exemption comes off with the 20%, so both are shown or the totals would not add up.
    var discounts = ((bill.discounts && bill.discounts.items) || []).filter(function (d) { return d.verified; });
    var discountVat = discounts.reduce(function (s, d) { return s + (Number(d.vat) || 0); }, 0);
    var discount20 = discounts.reduce(function (s, d) { return s + (Number(d.discount) || 0); }, 0);
    var serviceCharge = (bill.serviceCharge && Number(bill.serviceCharge.amount)) || 0;
    var subtotal = (Number(bill.total) || 0) - serviceCharge + discountVat + discount20;
    var payments = allPayments.filter(function (p) { return Number(p.amountPaid) !== 0; });
    var folioNo = 'FL-' + String(bill.bookingId || '').padStart(6, '0');
    var contactLine = [hotel.phone ? 'Tel: ' + hotel.phone : '', hotel.email || ''].filter(Boolean).join(' | ');

    var info = (official ? '<div><dt>Receipt No.</dt><dd>' + esc(number) + '</dd></div><div><dt>Date Issued</dt><dd>' + esc(longDay(issued)) + '</dd></div>' : '')
      + '<div><dt>Folio No.</dt><dd>' + esc(folioNo) + '</dd></div>'
      + (official ? '' : '<div><dt>Date Printed</dt><dd>' + esc(longDay(issued)) + '</dd></div>')
      + '<div><dt>Room No.</dt><dd>' + esc((bill.roomName || '—') + (bill.roomCategory ? ' (' + bill.roomCategory + ')' : '')) + '</dd></div>'
      + '<div><dt>Check-In</dt><dd>' + esc(checkInText) + '</dd></div>'
      + '<div><dt>Guest Name</dt><dd>' + esc(bill.guestName || '—') + '</dd></div>'
      + '<div><dt>Check-Out</dt><dd>' + esc(checkOutText) + '</dd></div>'
      + '<div><dt>Contact No.</dt><dd>' + esc(bill.contactNo || '—') + '</dd></div>'
      + '<div><dt>Nights</dt><dd>' + nights + ' Night' + (nights === 1 ? '' : 's') + '</dd></div>'
      + '<div><dt>ID No.</dt><dd>' + esc(bill.idNumber || '—') + '</dd></div>'
      + '<div><dt>Email</dt><dd>' + esc(bill.email || '—') + '</dd></div>';

    var charges = rows.map(function (r) {
      return '<tr><td class="rc-date">' + esc(shortDay(r.date)) + '</td>'
        + '<td>' + esc(r.desc) + (r.sub ? '<span class="rc-unit">' + esc(r.sub) + '</span>' : '') + '</td>'
        + '<td class="num">' + esc(r.qty) + '</td>'
        + '<td class="num rc-unitcol">' + esc(peso(r.unit)) + '</td>'
        + '<td class="num">' + esc(r.amount ? peso(r.amount) : 'Free') + '</td></tr>';
    }).join('');

    var paid = payments.map(function (p) {
      var kind = Number(p.amountPaid) < 0 ? 'Refund' : p.type === 'Full' ? 'Full payment' : 'Payment';
      return '<tr><td class="rc-date">' + esc(shortDay(p.paidAt)) + '</td>'
        + '<td>' + esc(kind + ' (' + p.method + (p.reference ? ' · ' + p.reference : '') + ')') + '</td>'
        + '<td class="num">' + esc(peso(-p.amountPaid)) + '</td></tr>';
    }).join('');

    return '<div class="receipt-print"><style>' + STYLE + '</style>'
      + '<div class="rc-top"><h3>' + esc(hotel.name || 'Hotel') + '</h3>'
      +   (hotel.address ? '<p>' + esc(hotel.address) + '</p>' : '')
      +   (contactLine ? '<p>' + esc(contactLine) + '</p>' : '')
      +   (official ? '<p class="rc-title">Guest Statement of Account / Folio</p><p>(Official Receipt)</p>' : '<p class="rc-title">Statement of Account (Guest Folio)</p>')
      + '</div>'
      + '<div class="rc-block"><dl class="rc-info">' + info + '</dl></div>'
      + '<div class="rc-block"><table class="rc-table"><thead><tr><th>Date</th><th>Department / Description</th><th class="num">Qty</th>'
      +   '<th class="num rc-unitcol">Unit Price</th><th class="num">Amount</th></tr></thead><tbody>' + charges + '</tbody></table></div>'
      + '<div class="rc-block rc-sum">'
      +   line('Subtotal', peso(subtotal), 'rc-strong')
      +   (discounts.length ? line('Less: Senior Citizen / PWD VAT exemption', peso(-discountVat)) + line('Less: Senior Citizen / PWD discount (20%)', peso(-discount20)) : '')
      +   (serviceCharge > 0 ? line('Service Charge (' + bill.serviceCharge.rate + '%)', peso(serviceCharge), '', 'On ' + peso(bill.serviceCharge.base) + ', after discounts') : '')
      +   line('Total Charges', peso(bill.total), 'rc-strong')
      + '</div>'
      + '<div class="rc-block"><p class="rc-head">' + (official ? 'Payments / Deposits' : 'Payments &amp; Credits Applied') + '</p>'
      +   (payments.length ? '' : line('None', peso(0)))
      +   '<table class="rc-table"><tbody>' + paid + '</tbody></table>'
      +   (cash ? line('Cash received', peso(cash.tendered)) + line('Change', peso(cash.change)) : '')
      + '</div>'
      + '<div class="rc-block rc-sum">'
      +   line('Total Paid', peso(bill.amountPaid), 'rc-strong')
      +   line(official ? 'Balance Due' : 'Total Balance Due', peso(balance), 'rc-strong' + (balance > 0 ? ' rc-due' : ''))
      +   (refundDue > 0 ? line('Refund Due to Guest', peso(refundDue), 'rc-strong') : '')
      + '</div>'
      + '<div class="rc-block"><p class="rc-head">Senior Citizen / PWD Discount</p>'
      +   (discounts.length
            ? discounts.map(function (d) { return line(d.type + ' — ' + d.fullName, peso(d.discount), '', 'ID No: ' + (d.idNumber || 'N/A') + ' · 20% of ' + peso(d.vatExemptPrice)); }).join('')
            : line('Senior Citizen / PWD Discount', peso(0) + ' (ID No: N/A)'))
      +   (official ? line('Payment Method', methods.join(', ') || '—') : '')
      + '</div>'
      + '<p class="rc-sign"><span></span><span>Guest Signature — ' + esc(bill.guestName || 'Guest') + '</span></p>'
      + (official ? '<p class="rc-thanks">Thank you for staying with us! Mabuhay!</p>' : '')
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
    frame.contentDocument.write('<!doctype html><html><head><meta charset="utf-8"><title>Guest Folio</title>'
      + '<style>@page{margin:12mm}html,body{margin:0;background:#fff;font-family:"Outfit","DM Sans",Arial,sans-serif}'
      + '.receipt-print{border:0!important;border-radius:0!important;padding:0!important}</style></head><body>'
      + sheet.outerHTML + '</body></html>');
    frame.contentDocument.close();
  }

  window.HmsGuestFolio = { html: html, print: print };
})();
