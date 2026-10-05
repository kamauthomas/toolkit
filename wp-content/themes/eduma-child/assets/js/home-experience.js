(function () {
  'use strict';

  var counters = document.querySelectorAll('[data-impact-count]');
  if (counters.length) {
    var animateCounter = function (element) {
      var target = parseInt(element.getAttribute('data-impact-count'), 10) || 0;
      var started = performance.now();
      var duration = 1200;
      var tick = function (now) {
        var progress = Math.min(1, (now - started) / duration);
        var eased = 1 - Math.pow(1 - progress, 3);
        element.textContent = Math.round(target * eased).toLocaleString('en-KE');
        if (progress < 1) requestAnimationFrame(tick);
      };
      requestAnimationFrame(tick);
    };
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.45 });
    counters.forEach(function (counter) { observer.observe(counter); });
  }

  document.querySelectorAll('.toolkit-video-facade').forEach(function (facade) {
    facade.addEventListener('click', function (event) {
      var videoId = facade.getAttribute('data-video-id');
      if (!/^[A-Za-z0-9_-]{11}$/.test(videoId || '')) return;
      event.preventDefault();
      var iframe = document.createElement('iframe');
      iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(videoId) + '?autoplay=1&rel=0';
      iframe.title = (facade.getAttribute('aria-label') || 'Toolkit testimonial').replace(/^Play /, '');
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      iframe.allowFullscreen = true;
      iframe.style.cssText = 'display:block;width:100%;height:100%;min-height:360px;border:0';
      iframe.setAttribute('tabindex', '0');
      facade.replaceWith(iframe);
      iframe.focus();
    });
  });

}());
