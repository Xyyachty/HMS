/**
 * One set of SweetAlert colours for the whole system.
 *
 * Every screen wrote its own iconColor and confirmButtonColor, so the same kind of
 * message was green on one page, gold on another and wine on a third. This wraps
 * Swal.fire and paints those three colours from the dialog's own status instead, so
 * a student, a faculty member and the dean all read the same colour for the same
 * kind of news. Nothing else about a dialog is touched: its title, text, buttons,
 * timers, callbacks, dark background and custom classes all pass straight through.
 *
 * Load it directly after the SweetAlert2 script on any page that uses it.
 *
 *   success  green   — saved, created, approved, sent, published, completed
 *   error    red     — failed, invalid, rejected, could not
 *   warning  orange  — are you sure, unsaved changes, send back for revision
 *   info     blue    — information, instructions, nothing-to-show notices
 *
 * A confirmation whose confirm button carries out a destructive action — delete,
 * remove, deactivate, reject — keeps the orange warning icon but turns that button
 * red, because the colour of the button is what the person is about to press.
 * One whose button activates or reactivates an account turns its icon and button
 * green, the colour of the Active status it is about to set.
 */
(function (window) {
  'use strict';

  var STATUS = {
    success:  { icon: '#16A34A', confirm: '#16A34A' },
    error:    { icon: '#DC2626', confirm: '#DC2626' },
    warning:  { icon: '#D97706', confirm: '#D97706' },
    info:     { icon: '#2563EB', confirm: '#2563EB' },
    // A question is a confirmation — "are you sure?" — so it reads as a warning
    // rather than as information.
    question: { icon: '#D97706', confirm: '#D97706' },
  };

  var CANCEL_COLOR = '#6B7280';
  var DANGER_COLOR = STATUS.error.confirm;

  /* What the confirm button is about to do, read from its own label. Only the
     button's wording counts: a title says what is being asked about, which is why
     "Remove this room?" with a "Keep it" button must not turn that button red. */
  var DESTRUCTIVE_BUTTON = /(delete|remove|deactivate|deny|reject|discard|revoke|unpublish)/i;
  // Checked only after DESTRUCTIVE_BUTTON, so "Deactivate" never lands here.
  var CONSTRUCTIVE_BUTTON = /activate/i;

  function statusFor(options) {
    var icon = options && typeof options.icon === 'string' ? options.icon.toLowerCase() : '';
    return STATUS[icon] || STATUS.info;
  }

  function buttonLabel(options) {
    return options && typeof options.confirmButtonText === 'string' ? options.confirmButtonText : '';
  }

  function isDestructive(options) {
    return DESTRUCTIVE_BUTTON.test(buttonLabel(options));
  }

  function isConstructive(options) {
    return !isDestructive(options) && CONSTRUCTIVE_BUTTON.test(buttonLabel(options));
  }

  function themed(options) {
    if (!options || typeof options !== 'object') return options;

    var status = statusFor(options);
    var next = Object.assign({}, options);

    next.iconColor = status.icon;
    next.confirmButtonColor = isDestructive(options) ? DANGER_COLOR : status.confirm;
    if (isConstructive(options)) {
      next.iconColor = STATUS.success.icon;
      next.confirmButtonColor = STATUS.success.confirm;
    }
    if (next.showCancelButton || next.cancelButtonText) {
      next.cancelButtonColor = CANCEL_COLOR;
    }

    return next;
  }

  function wrap(Swal) {
    if (!Swal || Swal.__hmsThemed) return false;

    var fire = Swal.fire;

    Swal.fire = function (options) {
      if (arguments.length === 1 && options && typeof options === 'object') {
        return fire.call(this, themed(options));
      }
      // Swal.fire(title, text, icon) — the shorthand form.
      if (typeof options === 'string') {
        return fire.call(this, themed({ title: options, text: arguments[1], icon: arguments[2] }));
      }
      return fire.apply(this, arguments);
    };

    Swal.__hmsThemed = true;
    return true;
  }

  if (!wrap(window.Swal)) {
    // Loaded before SweetAlert2 finished parsing: take it as soon as it appears
    // rather than leaving the page on unthemed dialogs.
    var tries = 0;
    var timer = window.setInterval(function () {
      if (wrap(window.Swal) || ++tries > 100) window.clearInterval(timer);
    }, 50);
  }
})(window);
