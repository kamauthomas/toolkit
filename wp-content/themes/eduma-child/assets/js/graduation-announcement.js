(function () {
  'use strict';
  var dialog = document.querySelector('[data-graduation-announcement]');
  if (!dialog || typeof dialog.showModal !== 'function') return;

  var campaign = dialog.getAttribute('data-campaign').replace(/[^0-9-]/g, '');
  var starts = Date.parse(dialog.getAttribute('data-start'));
  var ends = Date.parse(dialog.getAttribute('data-end'));
  if (!campaign || !Number.isFinite(starts) || !Number.isFinite(ends)) return;
  var hour = 60 * 60 * 1000;
  var day = 24 * hour;
  var key = 'toolkit:graduation:' + campaign;
  var cookieKey = 'toolkit_graduation_' + campaign.replace(/-/g, '');
  var previousFocus = null;
  var completed = false;

  function cookie(name) {
    var prefix = name + '=';
    var parts = document.cookie.split(';');
    for (var i = 0; i < parts.length; i++) {
      var part = parts[i].trim();
      if (part.indexOf(prefix) === 0) return part.slice(prefix.length);
    }
    return '';
  }

  function storage(kind, operation, name, value) {
    try { return window[kind][operation](name, value); } catch (error) { return null; }
  }

  function timestamp(value, now) {
    var number = Number(value);
    return Number.isFinite(number) && number > 0 && number <= now ? number : 0;
  }

  function cooldown(now) {
    if (starts - now <= 2 * day) return 2 * hour;
    if (starts - now <= 7 * day) return 6 * hour;
    return day;
  }

  function seenInSession(now) {
    var active = Math.max(timestamp(storage('sessionStorage', 'getItem', key + ':session'), now), timestamp(cookie(cookieKey + '_session'), now));
    return active > 0 && now - active < 30 * 60 * 1000;
  }

  function rememberSession(now) {
    storage('sessionStorage', 'setItem', key + ':session', String(now));
    var attributes = '; Path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    document.cookie = cookieKey + '_session=' + now + '; Max-Age=1800' + attributes;
  }

  function latestSeen(now) {
    return Math.max(timestamp(storage('localStorage', 'getItem', key + ':seen'), now), timestamp(cookie(cookieKey + '_seen'), now));
  }

  function canOpen(now) {
    return now < ends && !seenInSession(now) && now - latestSeen(now) >= cooldown(now);
  }

  function remember(now) {
    storage('localStorage', 'setItem', key + ':seen', String(now));
    // First-party cookies cover storage-restricted browsers and simultaneous tabs.
    var attributes = '; Path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    document.cookie = cookieKey + '_seen=' + now + '; Max-Age=' + Math.max(1, Math.ceil((ends - now) / 1000)) + attributes;
    rememberSession(now);
    // If all persistence is blocked, keep automatic display off, rather than
    // showing an unremembered popup on every page. Manual opening still works.
    return seenInSession(now) && latestSeen(now) === now;
  }

  function busy() {
    var active = document.activeElement;
    return document.hidden || document.querySelector('dialog[open], [role="dialog"]:not([hidden]), .toolkit-chat__toggle[aria-expanded="true"]') ||
      (active && (active.matches('input, textarea, select') || active.isContentEditable));
  }

  function open(manual) {
    var now = Date.now();
    if (dialog.open || now >= ends || (!manual && (completed || !canOpen(now) || busy()))) return;
    if (!manual && !remember(now)) { completed = true; return; }
    var photo = dialog.querySelector('[data-graduation-photo]');
    if (photo && !photo.getAttribute('src')) photo.src = photo.getAttribute('data-src');
    previousFocus = document.activeElement;
    dialog.showModal();
    document.documentElement.classList.add('toolkit-graduation-open');
    completed = true;
    if (manual) remember(now);
  }

  dialog.querySelectorAll('[data-graduation-close]').forEach(function (button) {
    button.addEventListener('click', function () { dialog.close(); });
  });
  dialog.addEventListener('close', function () {
    document.documentElement.classList.remove('toolkit-graduation-open');
    if (previousFocus && previousFocus.isConnected && typeof previousFocus.focus === 'function') previousFocus.focus({ preventScroll: true });
  });
  dialog.addEventListener('keydown', function (event) {
    if (event.key !== 'Tab') return;
    var controls = Array.from(dialog.querySelectorAll('button:not([disabled]), a[href]')).filter(function (control) { return control.getClientRects().length > 0; });
    if (!controls.length) return;
    var first = controls[0];
    var last = controls[controls.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault(); last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault(); first.focus();
    }
  });
  var backdropPress = false;
  dialog.addEventListener('pointerdown', function (event) { backdropPress = event.target === dialog; });
  dialog.addEventListener('click', function (event) {
    if (backdropPress && event.target === dialog) dialog.close();
    backdropPress = false;
  });
  document.querySelectorAll('[data-graduation-open]').forEach(function (button) {
    button.addEventListener('click', function () { open(true); });
  });
  // A second tab can consume the allowance while this page's delay is running.
  // Re-read shared state at the moment of opening, not just on initial load.
  // A visit ends after 30 idle minutes. Browsing more pages renews that visit,
  // so the increasing reminder frequency never becomes a page-change popup.
  if (seenInSession(Date.now())) rememberSession(Date.now());
  window.setTimeout(function () { open(false); }, 2500);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) open(false); });
  // Cached markup or a tab left open must not keep promoting a finished event.
  var remaining = ends - Date.now();
  if (remaining > 0) window.setTimeout(function () { if (dialog.open) dialog.close(); }, Math.min(remaining, 2147483647));
})();
