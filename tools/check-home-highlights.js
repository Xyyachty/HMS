// Home page Selected Highlights store: run with `node tools/check-home-highlights.js`.
const assert = require('assert');
const fs = require('fs');
const src = fs.readFileSync(require('path').join(__dirname, '../public/js/hms-site-content.js'), 'utf8');
const window = {
  __HMS_CUSTOMIZATIONS__: {},
  __HMS_CAN_EDIT__: true,
  __HMS_EDITABLE_PAGES__: ['home'],
  dispatchEvent() {},
  addEventListener() {},
  location: { search: '' },
};
window.parent = window;
global.CustomEvent = class { constructor(t, o) { this.type = t; this.detail = o && o.detail; } };
global.document = { addEventListener() {}, querySelector() { return null; }, querySelectorAll() { return []; } };
new Function('window', src)(window);
const c = window.HMSSiteContent;
const exps = ['a', 'b', 'c', 'd', 'e', 'f'].map(id => ({ id, title: id.toUpperCase() }));
const ids = list => list.map(x => x.id).join('');

assert.strictEqual(ids(c.getHomeHighlights(exps)), 'abcd', 'unset shows the first four');
assert.ok(c.setHomeHighlights([exps[4], exps[1], exps[4]]), 'front desk may set');
assert.strictEqual(ids(c.getHomeHighlights(exps)), 'eb', 'picked order, duplicates dropped');
const saved = window.__HMS_CUSTOMIZATIONS__.__homeHighlights;
assert.strictEqual(saved.page, 'home');
assert.deepStrictEqual(saved.items[0], { id: 'e', title: 'E' });
assert.strictEqual(ids(c.getHomeHighlights(exps.filter(x => x.id !== 'e'))), 'b', 'deleted tile drops out');
assert.strictEqual(ids(c.getHomeHighlights(exps.filter(x => 'eb'.indexOf(x.id) === -1))), 'acdf', 'all picks gone falls back');
assert.ok(c.setHomeHighlights(exps), 'over four is trimmed');
assert.strictEqual(ids(c.getHomeHighlights(exps)), 'abcd');
assert.strictEqual(c.setHomeHighlights([]), false, 'empty refused');
window.__HMS_EDITABLE_PAGES__ = ['experience'];
assert.strictEqual(c.setHomeHighlights([exps[5]]), false, 'non-home role refused');
console.log('store ok');
