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
    facade.addEventListener('click', function () {
      var videoId = facade.getAttribute('data-video-id');
      var iframe = document.createElement('iframe');
      iframe.src = 'https://www.youtube-nocookie.com/embed/' + encodeURIComponent(videoId) + '?autoplay=1&rel=0';
      iframe.title = facade.getAttribute('aria-label').replace(/^Play /, '');
      iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
      iframe.allowFullscreen = true;
      facade.replaceWith(iframe);
    });
  });

  var chat = document.querySelector('.toolkit-chat');
  if (!chat) return;
  var toggle = chat.querySelector('.toolkit-chat__toggle');
  var panel = chat.querySelector('.toolkit-chat__panel');
  var close = chat.querySelector('[data-chat-close]');
  var messages = chat.querySelector('.toolkit-chat__messages');
  var responses = {
    courses: 'Toolkit currently presents Welding Sector, Renewable Sector, Organic Farming Skills, Digital Skills, Recognition of Prior Learning, and Consultancy and Research.',
    fees: 'Course fees and schedules can change. Use the current course catalogue, then confirm the selected programme with Admissions before payment.',
    apply: 'Start with the guided application page. It will help you choose a course before continuing to the application form.',
    contact: 'Call +254 709 549 200 or email office@toolkitafrica.ac.ke. Toolkit is on the Karen-Kikuyu Southern Bypass in Kikuyu, Kenya.'
  };
  var links = {
    courses: ['/our-ventures/', 'View current courses'],
    fees: ['/our-ventures/', 'Check course details'],
    apply: ['/our-ventures/toolkit-courses-apply-today/', 'Start application'],
    contact: ['/contact/', 'Contact Toolkit']
  };
  function setOpen(open) {
    panel.hidden = !open;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) panel.querySelector('button').focus();
  }
  toggle.addEventListener('click', function () { setOpen(panel.hidden); });
  close.addEventListener('click', function () { setOpen(false); toggle.focus(); });
  chat.querySelectorAll('[data-chat-topic]').forEach(function (button) {
    button.addEventListener('click', function () {
      var topic = button.getAttribute('data-chat-topic');
      messages.innerHTML = '<p class="is-user">' + button.textContent + '</p><p class="is-assistant">' + responses[topic] + ' <a href="' + links[topic][0] + '">' + links[topic][1] + '</a></p>';
    });
  });
}());
