/* Date fields that read "Nov. 16, 2026" instead of the browser's 11/16/2026.
 *
 * The native <input type="date"> stays, so the calendar picker, min/max, the
 * form value (YYYY-MM-DD) and keyboard entry all work as before. Its own text is
 * made transparent and a label with the friendly date sits on top of it.
 *
 *   React (Babel pages):  <HmsDateInput type="date" value={v} onChange={...} />
 *   Server-rendered form: <input type="date" data-pretty-date ...>  (enhanced on load)
 *   Text anywhere:        HmsDate.format('2026-11-16')  // "Nov. 16, 2026"
 *                        HmsDate.date(anyDate) / HmsDate.dateTime(anyDate)  // "Nov. 16, 2026, 3:05 PM"
 */
(function () {
  /* HmsDate.date(value, opts) -> "Nov. 16, 2026"; with opts.hour/minute -> "Nov. 16, 2026, 3:05 PM".
     value: 'YYYY-MM-DD' (a calendar day, read in local time), any Date-parsable string,
     a timestamp or a Date. opts may add timeZone/hour/minute; month, day and year are
     always the house style, so a caller's weekday/month/day/year options are ignored. */
  function date(value, opts) {
    if (value == null || value === '') return '';
    var ymd = /^(\d{4})-(\d{2})-(\d{2})$/.exec(String(value).trim());
    var d = value instanceof Date ? value : ymd ? new Date(+ymd[1], +ymd[2] - 1, +ymd[3]) : new Date(value);
    if (isNaN(d.getTime())) return '';
    var o = { month: 'short', day: 'numeric', year: 'numeric' };
    ['timeZone', 'hour', 'minute', 'hour12'].forEach(function (k) {
      if (opts && opts[k] !== undefined && !(ymd && k === 'timeZone')) o[k] = opts[k];
    });
    return d.toLocaleString('en-US', o).replace(/\b(Jan|Feb|Mar|Apr|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\b(?!\.)/, '$1.');
  }
  function dateTime(value, opts) {
    return date(value, Object.assign({ hour: 'numeric', minute: '2-digit' }, opts || {}));
  }
  var format = function (iso) { return date(iso); };

  var css = ''
    + '.hms-date{position:relative;display:flex;flex-direction:column;min-width:0}'
    + '.hms-date>input::-webkit-datetime-edit{color:transparent}'
    + '.hms-date>input::-webkit-date-and-time-value{color:transparent}'
    + '@supports (-moz-appearance:none){.hms-date>input{color:transparent!important}}'
    + '.hms-date__text{position:absolute;display:flex;align-items:center;pointer-events:none;'
    + 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis}'
    + '.hms-date__text.is-empty{opacity:.55}';
  var style = document.createElement('style');
  style.textContent = css;
  (document.head || document.documentElement).appendChild(style);

  // Lines the label up with where the input draws its own text, in its font and colour.
  function sync(input, label) {
    if (!input || !label) return;
    // Computed styles only, no layout reads, so a field inside a closed modal lines up too.
    var cs = getComputedStyle(input);
    var px = function (v) { return (parseFloat(v) || 0) + 'px'; };
    label.style.left = px((parseFloat(cs.marginLeft) || 0) + (parseFloat(cs.borderLeftWidth) || 0) + (parseFloat(cs.paddingLeft) || 0));
    label.style.right = px((parseFloat(cs.marginRight) || 0) + (parseFloat(cs.paddingRight) || 0) + 28); // calendar icon stays clear
    label.style.top = px(cs.marginTop);
    label.style.bottom = px(cs.marginBottom);
    ['fontSize', 'fontWeight', 'fontStyle', 'letterSpacing'].forEach(function (k) { label.style[k] = cs[k]; });
    // Chrome gives date inputs a monospace font of their own; the page's font reads better.
    label.style.fontFamily = cs.fontFamily === 'monospace' ? '' : cs.fontFamily;
    if (cs.color !== 'rgba(0, 0, 0, 0)') label.style.color = cs.color;
  }

  // Click anywhere on the field opens the calendar, not just on the icon.
  function openPicker(input) {
    try { if (input.showPicker && !input.disabled && !input.readOnly) input.showPicker(); } catch (e) { /* not allowed here */ }
  }

  function pretty(value, type) { return type === "datetime-local" ? dateTime(value) : date(value); }
  function labelText(value, placeholder, type) {
    return pretty(value, type) || placeholder || 'Select date';
  }

  window.HmsDate = { format: format, date: date, dateTime: dateTime };

  if (window.React) {
    var h = React.createElement;
    window.HmsDateInput = function HmsDateInput(props) {
      var inputRef = React.useRef(null);
      var labelRef = React.useRef(null);
      var own = React.useState(props.defaultValue || '');
      var value = props.value !== undefined ? props.value : own[0];
      React.useLayoutEffect(function () { sync(inputRef.current, labelRef.current); });

      var rest = Object.assign({}, props, {
        type: props.type === "datetime-local" ? "datetime-local" : "date",
        ref: inputRef,
        onChange: function (e) {
          if (props.value === undefined) own[1](e.target.value);
          if (props.onChange) props.onChange(e);
        },
        onClick: function (e) {
          openPicker(e.currentTarget);
          if (props.onClick) props.onClick(e);
        },
      });
      return h('span', { className: 'hms-date' },
        h('input', rest),
        h('span', { ref: labelRef, className: 'hms-date__text' + (pretty(value, props.type) ? "" : " is-empty"), 'aria-hidden': 'true' },
          labelText(value, props.placeholder, props.type)));
    };
  }

  function enhance(input) {
    if (input.parentNode && input.parentNode.classList.contains('hms-date')) return;
    var wrap = document.createElement('span');
    wrap.className = 'hms-date';
    var label = document.createElement('span');
    label.className = 'hms-date__text';
    label.setAttribute('aria-hidden', 'true');
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    wrap.appendChild(label);
    var update = function () {
      label.textContent = labelText(input.value, input.getAttribute("placeholder"), input.type);
      label.classList.toggle('is-empty', !pretty(input.value, input.type));
      sync(input, label);
    };
    input.addEventListener('input', update);
    input.addEventListener('change', update);
    input.addEventListener('click', function () { openPicker(input); });
    window.addEventListener('resize', update);
    update();
  }

  function enhanceAll() {
    document.querySelectorAll('input[data-pretty-date]').forEach(enhance);
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', enhanceAll);
  else enhanceAll();
})();
