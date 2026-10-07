// Acceptance scenarios for repeat rules and blocked browser persistence.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync('wp-content/themes/eduma-child/assets/js/graduation-announcement.js', 'utf8');
const key = 'toolkit:graduation:2026-10-22';
const ckey = 'toolkit_graduation_20261022';
const hour = 3600000;
const state = () => ({ local: new Map(), session: new Map(), cookies: new Map() });
let checks = 0;
function visit(at, data, options = {}) {
  let clock = Date.parse(at), opened = false, photo = false;
  const timers = [], attrs = { 'data-campaign': '2026-10-22', 'data-start': '2026-10-22T06:00:00Z', 'data-end': '2026-10-22T10:00:00Z' };
  const store = (map, blocked) => ({ getItem(k) { if (blocked) throw Error('Blocked'); return map.get(k) || null; }, setItem(k, v) { if (blocked) throw Error('Blocked'); map.set(k, v); } });
  const dialog = { open: false, getAttribute(k) { return attrs[k]; }, showModal() { opened = this.open = true; }, close() { this.open = false; }, querySelectorAll() { return []; }, addEventListener() {}, querySelector() { return { getAttribute(k) { return k === 'src' ? (photo ? '/photo.webp' : '') : '/photo.webp'; }, set src(v) { photo = true; } }; } };
  const document = { hidden: !!options.hidden, activeElement: { matches() { return !!options.input; } }, documentElement: { classList: { add() {} } }, querySelector(s) { return s === '[data-graduation-announcement]' ? dialog : options.modal ? {} : null; }, querySelectorAll() { return []; }, addEventListener() {} };
  Object.defineProperty(document, 'cookie', { get() { if (options.cookiesBlocked) return ''; return [...data.cookies].map(([k, v]) => `${k}=${v}`).join('; '); }, set(v) { if (!options.cookiesBlocked) { const [k, val] = v.split(';')[0].split('='); data.cookies.set(k, val); } } });
  class Clock extends Date { static now() { return clock; } }
  const window = { localStorage: store(data.local, options.storageBlocked), sessionStorage: store(data.session, options.storageBlocked), setTimeout(fn, ms) { timers.push({ fn, ms }); } };
  vm.runInNewContext(source, { document, window, Date: Clock, Number, Math, String, location: { protocol: 'https:' } });
  const timer = timers.find(x => x.ms === 2500); if (timer) timer.fn();
  return { opened, photo };
}
function expect(at, data, expected, options) { const result = visit(at, data, options); assert.equal(result.opened, expected, at); assert.equal(result.photo, expected, 'Hidden campaigns must not fetch the photo'); checks++; }
function previous(at) { const s = state(); s.local.set(key + ':seen', String(Date.parse(at))); return s; }
let s = state();
expect('2026-10-07T06:00:00Z', s, true);
expect('2026-10-07T06:02:00Z', s, false); // Page navigation in the visit.
expect('2026-10-07T20:00:00Z', s, false); // Later visit, still the daily limit.
expect('2026-10-08T06:00:00Z', s, true); // A day later, another visit.
expect('2026-10-08T06:01:00Z', s, false);
expect('2026-10-14T06:00:00Z', previous('2026-10-14T00:00:01Z'), false); // Before final week.
expect('2026-10-15T06:00:00Z', previous('2026-10-15T00:00:00Z'), true); // Final week: six hours.
expect('2026-10-16T06:00:00Z', previous('2026-10-16T00:00:01Z'), false);
expect('2026-10-20T06:00:00Z', previous('2026-10-20T04:00:00Z'), true); // Final two days: two hours.
expect('2026-10-20T06:00:00Z', previous('2026-10-20T04:00:01Z'), false);
expect('2026-10-22T09:59:59Z', state(), true);
expect('2026-10-22T10:00:00Z', state(), false); // Event ends at 1pm Nairobi.
expect('2026-10-23T06:00:00Z', state(), false);
// A long visit remains suppressed even when a shorter near-event limit expires.
s = previous('2026-10-21T01:00:00Z'); s.session.set(key + ':session', String(Date.parse('2026-10-21T05:50:00Z')));
expect('2026-10-21T06:00:00Z', s, false);
expect('2026-10-21T06:29:59Z', s, false); // Page load renewed the visit.
expect('2026-10-21T07:00:00Z', s, true); // 30 idle minutes starts a new visit.
s = state(); expect('2026-10-07T06:00:00Z', s, true, { storageBlocked: true });
expect('2026-10-07T06:02:00Z', s, false, { storageBlocked: true });
s = state(); expect('2026-10-07T06:00:00Z', s, true, { cookiesBlocked: true });
expect('2026-10-07T06:02:00Z', s, false, { cookiesBlocked: true });
expect('2026-10-07T06:00:00Z', state(), false, { storageBlocked: true, cookiesBlocked: true });
expect('2026-10-07T06:00:00Z', state(), false, { hidden: true });
expect('2026-10-07T06:00:00Z', state(), false, { input: true });
expect('2026-10-07T06:00:00Z', state(), false, { modal: true });
s = state(); s.local.set(key + ':seen', 'broken'); expect('2026-10-07T06:00:00Z', s, true);
s = previous('2030-01-01T00:00:00Z'); expect('2026-10-07T06:00:00Z', s, true);
console.log(`GRADUATION_REPEAT_RULES_OK ${checks} acceptance scenarios`);
