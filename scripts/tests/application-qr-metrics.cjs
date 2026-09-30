const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const code = fs.readFileSync('wp-content/themes/eduma-child/assets/js/toolkit-metrics.js', 'utf8');
const tag = '?utm_source=application_qr&utm_medium=qr&utm_campaign=admissions';
function visit({path = '/apply/', query = tag, storage = new Map(), dnt = '0'} = {}) {
  const events = [];
  const context = {
    window: {toolkitMetrics: {endpoint: '/wp-json/toolkit/v1/metrics'}, location: {pathname: path, search: query}, addEventListener() {}},
    navigator: {doNotTrack: dnt, sendBeacon: (_, body) => events.push(JSON.parse(body.data))},
    sessionStorage: {getItem: key => storage.get(key), setItem: (key, value) => storage.set(key, value)},
    document: {addEventListener() {}},
    URLSearchParams, Blob: class {constructor(parts) {this.data = parts.join('');}}, Date
  };
  vm.runInNewContext(code, context);
  return events.filter(e => e.label === 'application_qr_scan');
}
const storage = new Map([['toolkit-view:/apply/', '1']]);
assert.equal(visit({storage}).length, 1, 'QR arrival counts even after an ordinary form visit');
assert.equal(visit({storage}).length, 0, 'Refresh does not count again');
assert.equal(visit().length, 1, 'New browser session counts');
assert.equal(visit({query: ''}).length, 0, 'Ordinary form visit is excluded');
assert.equal(visit({query: '?utm_source=application_qr&utm_medium=social'}).length, 0, 'Wrong medium is excluded');
assert.equal(visit({path: '/connect/'}).length, 0, 'Wrong destination is excluded');
assert.equal(visit({dnt: '1'}).length, 0, 'Do Not Track is respected');
console.log('PASS: QR attribution, session deduplication, ordinary traffic exclusion and Do Not Track');
