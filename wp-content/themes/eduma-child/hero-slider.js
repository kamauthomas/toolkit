(function () {
  'use strict';

  var slider = document.getElementById('hero-slider');
  if (!slider) return;

  var slidesEl = slider.querySelector('.hero-slider__slides');
  var slides = slider.querySelectorAll('.hero-slider__slide');
  var dots = slider.querySelectorAll('.hero-slider__dot');
  var nextBtn = slider.querySelector('.hero-slider__arrow--next');
  var pauseBtn = slider.querySelector('.hero-slider__pause');
  var counter = slider.querySelector('.hero-slider__counter-current');
  var scrollCue = slider.querySelector('.hero-slider__scroll-cue');
  var videoBadge = slider.querySelector('.hero-slider__video-badge');
  var modal = slider.querySelector('.hero-slider__modal');
  var modalClose = slider.querySelector('.hero-slider__modal-close');
  var pauseIcon = slider.querySelector('.hero-slider__pause-icon');
  var playIcon = slider.querySelector('.hero-slider__play-icon');

  var slideCount = slides.length;
  var current = 0;
  var isAnimating = false;
  var isPaused = false;
  var interval = null;
  var autoplayDelay = 6000;

  function updateSlide(index) {
    if (isAnimating) return;
    if (index === current) return;
    isAnimating = true;

    slides[current].classList.remove('is-active');
    slides[current].setAttribute('aria-hidden', 'true');
    slides[index].classList.add('is-active');
    slides[index].setAttribute('aria-hidden', 'false');

    dots[current].classList.remove('is-active');
    dots[current].setAttribute('aria-selected', 'false');
    dots[index].classList.add('is-active');
    dots[index].setAttribute('aria-selected', 'true');

    var num = (index + 1).toString().padStart(2, '0');
    counter.textContent = num;

    current = index;

    var onTransitionEnd = function () {
      slides[current].removeEventListener('transitionend', onTransitionEnd);
      isAnimating = false;
    };
    slides[current].addEventListener('transitionend', onTransitionEnd);
  }

  function goTo(index) {
    if (isAnimating) return;
    if (index < 0) index = slideCount - 1;
    if (index >= slideCount) index = 0;
    updateSlide(index);
  }

  function nextSlide() {
    goTo(current + 1);
  }

  function prevSlide() {
    goTo(current - 1);
  }

  function startAutoplay() {
    if (interval) clearInterval(interval);
    if (isPaused) return;
    interval = setInterval(nextSlide, autoplayDelay);
  }

  function stopAutoplay() {
    if (interval) {
      clearInterval(interval);
      interval = null;
    }
  }

  function togglePause() {
    isPaused = !isPaused;
    if (isPaused) {
      stopAutoplay();
      pauseIcon.style.display = 'none';
      playIcon.style.display = '';
      pauseBtn.setAttribute('aria-label', 'Play autoplay');
    } else {
      playIcon.style.display = 'none';
      pauseIcon.style.display = '';
      pauseBtn.setAttribute('aria-label', 'Pause autoplay');
      startAutoplay();
    }
  }

  /* Dots */
  for (var d = 0; d < dots.length; d++) {
    dots[d].addEventListener('click', function () {
      var idx = parseInt(this.getAttribute('data-slide'), 10) - 1;
      goTo(idx);
    });
  }

  /* Next arrow */
  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      nextSlide();
    });
  }

  /* Pause */
  if (pauseBtn) {
    pauseBtn.addEventListener('click', togglePause);
  }

  /* Scroll cue */
  if (scrollCue) {
    scrollCue.addEventListener('click', function () {
      var target = document.querySelector('.hero-features');
      if (!target) target = document.getElementById('main-content');
      if (target) {
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }
    });
  }

  /* Hover/focus pause */
  slider.addEventListener('mouseenter', function () {
    if (!isPaused) stopAutoplay();
  });
  slider.addEventListener('mouseleave', function () {
    if (!isPaused) startAutoplay();
  });
  slider.addEventListener('focusin', function () {
    if (!isPaused) stopAutoplay();
  });
  slider.addEventListener('focusout', function () {
    if (!isPaused) startAutoplay();
  });

  /* Keyboard */
  slider.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowRight') {
      e.preventDefault();
      nextSlide();
    }
    if (e.key === 'ArrowLeft') {
      e.preventDefault();
      prevSlide();
    }
  });

  /* Touch/swipe */
  var touchStartX = 0;
  var touchEndX = 0;
  var touchStartY = 0;

  slider.addEventListener('touchstart', function (e) {
    touchStartX = e.changedTouches[0].screenX;
    touchStartY = e.changedTouches[0].screenY;
  }, { passive: true });

  slider.addEventListener('touchend', function (e) {
    touchEndX = e.changedTouches[0].screenX;
    var diffX = touchStartX - touchEndX;
    var diffY = touchStartY - e.changedTouches[0].screenY;
    if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
      if (diffX > 0) nextSlide();
      else prevSlide();
    }
  }, { passive: true });

  /* Video badge / modal */
  if (videoBadge && modal) {
    videoBadge.addEventListener('click', function () {
      modal.removeAttribute('hidden');
      setTimeout(function () { modal.classList.add('is-open'); }, 10);
      modalClose.focus();
    });
    videoBadge.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        videoBadge.click();
      }
    });
  }

  if (modalClose && modal) {
    modalClose.addEventListener('click', function () {
      modal.classList.remove('is-open');
      var onTransition = function () {
        modal.setAttribute('hidden', '');
        modal.removeEventListener('transitionend', onTransition);
        videoBadge.focus();
      };
      modal.addEventListener('transitionend', onTransition);
    });

    modal.addEventListener('click', function (e) {
      if (e.target === modal || e.target.classList.contains('hero-slider__modal-backdrop')) {
        modalClose.click();
      }
    });

    modal.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        modalClose.click();
      }
    });
  }

  /* Click-to-play YouTube card below the hero. Keeps the initial page lighter. */
  var whoVideoCard = document.querySelector('.home-who__video-card');
  if (whoVideoCard) {
    whoVideoCard.addEventListener('click', function () {
      var youtubeId = whoVideoCard.getAttribute('data-youtube-id');
      if (!youtubeId) return;

      var iframe = document.createElement('iframe');
      iframe.setAttribute('src', 'https://www.youtube.com/embed/' + youtubeId + '?autoplay=1&rel=0');
      iframe.setAttribute('title', 'The Toolkit Skills and Innovation Hub video');
      iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
      iframe.setAttribute('allowfullscreen', '');
      iframe.setAttribute('loading', 'lazy');
      iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');

      if (whoVideoCard.parentElement) {
        whoVideoCard.parentElement.classList.add('is-playing');
      }
      whoVideoCard.replaceWith(iframe);
    });
  }

  /* Start */
  startAutoplay();
})();
