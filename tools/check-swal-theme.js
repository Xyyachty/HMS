/*
 * What colours public/js/hms-swal-theme.js paints on a SweetAlert.
 *
 * Run it with:  node tools/check-swal-theme.js
 *
 * The theme runs in the browser, so this stands a fake window and a fake Swal in
 * front of it and reads back the options it would have fired with.
 */
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const vm = require('vm');

const GREEN = '#16A34A';
const RED = '#DC2626';
const ORANGE = '#D97706';
const BLUE = '#2563EB';
const GREY = '#6B7280';

let fired = null;
const fakeWindow = {
  Swal: { fire: (options) => { fired = options; } },
  setInterval: () => 0,
  clearInterval: () => {},
};
fakeWindow.window = fakeWindow;

vm.runInNewContext(
  fs.readFileSync(path.join(__dirname, '../public/js/hms-swal-theme.js'), 'utf8'),
  { window: fakeWindow, Object }
);

const fire = (options) => { fired = null; fakeWindow.Swal.fire(options); return fired; };

const cases = [
  ['a success is green', { icon: 'success', title: 'Saved' }, { iconColor: GREEN, confirmButtonColor: GREEN }],
  ['an error is red', { icon: 'error', title: 'Import Failed' }, { iconColor: RED, confirmButtonColor: RED }],
  ['a warning is orange', { icon: 'warning', title: 'Are you sure?' }, { iconColor: ORANGE, confirmButtonColor: ORANGE }],
  ['a question is a confirmation, so orange too', { icon: 'question' }, { iconColor: ORANGE, confirmButtonColor: ORANGE }],
  ['information is blue', { icon: 'info' }, { iconColor: BLUE, confirmButtonColor: BLUE }],
  ['no icon reads as information', { title: 'Nothing to show' }, { iconColor: BLUE, confirmButtonColor: BLUE }],
  [
    'a destructive confirm button is red under an orange warning',
    { icon: 'warning', title: 'Remove this add-on?', confirmButtonText: 'Remove' },
    { iconColor: ORANGE, confirmButtonColor: RED },
  ],
  [
    'a confirmation that is not destructive keeps the orange button',
    { icon: 'warning', title: 'Unsaved changes', confirmButtonText: 'Save now' },
    { iconColor: ORANGE, confirmButtonColor: ORANGE },
  ],
  [
    'a cancel button is neutral',
    { icon: 'warning', showCancelButton: true, confirmButtonText: 'Delete' },
    { iconColor: ORANGE, confirmButtonColor: RED, cancelButtonColor: GREY },
  ],
];

let failed = 0;
for (const [label, options, expected] of cases) {
  const out = fire(options);
  for (const key of Object.keys(expected)) {
    if (out[key] !== expected[key]) {
      failed++;
      console.log(`FAIL  ${label}: ${key} expected ${expected[key]}, got ${out[key]}`);
    }
  }
}

// Everything the caller passed has to survive untouched — a dark popup stays dark,
// a timer still runs, a callback is still there to be called.
const passthrough = fire({
  icon: 'success',
  title: 'Room Added!',
  background: '#181714',
  color: '#f5f0e8',
  timer: 3000,
  timerProgressBar: true,
  customClass: { popup: 'rounded-2xl' },
});
try {
  assert.strictEqual(passthrough.background, '#181714');
  assert.strictEqual(passthrough.color, '#f5f0e8');
  assert.strictEqual(passthrough.timer, 3000);
  assert.strictEqual(passthrough.timerProgressBar, true);
  assert.strictEqual(passthrough.customClass.popup, 'rounded-2xl');
  assert.strictEqual(passthrough.title, 'Room Added!');
} catch (e) {
  failed++;
  console.log('FAIL  other options must pass through untouched: ' + e.message);
}

if (failed) {
  console.error(`${failed} check(s) failed`);
  process.exit(1);
}
console.log(`swal theme: all ${cases.length + 1} checks pass`);
