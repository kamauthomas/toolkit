(function () {
  'use strict';
  if (!window.toolkitMetrics || !window.toolkitMetrics.endpoint || navigator.doNotTrack === '1') return;

  var endpoint = window.toolkitMetrics.endpoint;
  var path = window.location.pathname || '/';
  var started = Date.now();
  var deepest = 0;
  var sentScroll = {};

  function send(event, value, label) {
    var body = JSON.stringify({ event: event, path: path, value: Math.round(value || 0), label: label || '' });
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

	var source = new URLSearchParams(window.location.search).get('utm_source');
	if (source && /^[a-z0-9_-]{1,32}$/i.test(source)) {
	  send('interaction', 1, 'arrival_' + source.toLowerCase());
	}
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
    var control = event.target.closest && event.target.closest('[data-metric]');
    if (control) send('interaction', 1, control.getAttribute('data-metric'));
    if (!link) return;
    try {
      var target = new URL(link.href, location.href);
      if (target.host !== location.host) send('outbound_click', 1);
      if (target.pathname.indexOf('toolkit-courses-apply-today') !== -1) send('interaction', 1, 'application_start');
      else if (target.pathname.indexOf('our-ventures') !== -1) send('interaction', 1, 'course_navigation');
      else if (/youtu|youtube/.test(target.host)) send('interaction', 1, 'testimonial_video');
    } catch (ignore) {}
  });

  function finish() {
    send('engaged_time', Math.min(600, Math.round((Date.now() - started) / 1000)));
    if (deepest) send('scroll_depth', deepest);
  }
  window.addEventListener('pagehide', finish, { once: true });
}());
