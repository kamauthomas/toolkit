(function () {
  'use strict';
  if (!window.toolkitMetrics || !window.toolkitMetrics.endpoint || navigator.doNotTrack === '1') return;

  var endpoint = window.toolkitMetrics.endpoint;
  var path = window.location.pathname || '/';
  var started = Date.now();
  var deepest = 0;
  var sentScroll = {};

  function send(event, value) {
    var body = JSON.stringify({ event: event, path: path, value: Math.round(value || 0) });
    if (navigator.sendBeacon) {
      navigator.sendBeacon(endpoint, new Blob([body], { type: 'application/json' }));
    } else {
      fetch(endpoint, { method: 'POST', body: body, headers: { 'Content-Type': 'application/json' }, credentials: 'same-origin', keepalive: true });
    }
  }

  var viewKey = 'toolkit-view:' + path;
  if (!sessionStorage.getItem(viewKey)) {
    send('page_view', 1);
    sessionStorage.setItem(viewKey, '1');
  }

  function measureScroll() {
    var scrollable = document.documentElement.scrollHeight - window.innerHeight;
    var depth = scrollable > 0 ? Math.min(100, Math.round((window.scrollY / scrollable) * 100)) : 100;
    [25, 50, 75, 100].forEach(function (bucket) {
      if (depth >= bucket && !sentScroll[bucket]) {
        sentScroll[bucket] = true;
        deepest = bucket;
      }
    });
  }
  window.addEventListener('scroll', measureScroll, { passive: true });

  window.addEventListener('load', function () {
    var navigation = performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
    send('performance', navigation ? navigation.loadEventEnd : Date.now() - performance.timeOrigin);
    measureScroll();
  });

  document.addEventListener('click', function (event) {
    var link = event.target.closest && event.target.closest('a[href]');
    if (!link) return;
    try { if (new URL(link.href, location.href).host !== location.host) send('outbound_click', 1); } catch (ignore) {}
  });

  function finish() {
    send('engaged_time', Math.min(600, Math.round((Date.now() - started) / 1000)));
    if (deepest) send('scroll_depth', deepest);
  }
  window.addEventListener('pagehide', finish, { once: true });
}());
