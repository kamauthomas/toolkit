(function () {
  'use strict';
  var board = document.querySelector('.toolkit-notice-page');
  if (board) {
    var cards = Array.prototype.slice.call(board.querySelectorAll('.toolkit-notice-grid article'));
    var filters = board.querySelectorAll('[data-filter]');
    var search = board.querySelector('.toolkit-notice-search input');
    var grid = board.querySelector('.toolkit-notice-grid');
    var sort = board.querySelector('#toolkit-notice-sort');
    var results = board.querySelector('.toolkit-notice-results');
    var empty = board.querySelector('.toolkit-notice-empty');
    function updateCards() {
      var active = board.querySelector('[data-filter].is-active');
      var filter = active ? active.getAttribute('data-filter') : 'all';
      var term = search ? search.value.toLowerCase().trim() : '';
      var visible = 0;
      cards.forEach(function (card) {
        var categories = (card.getAttribute('data-category') || '').split(/\s+/);
        var matchesFilter = filter === 'all' || categories.indexOf(filter) !== -1;
        var matchesSearch = !term || card.textContent.toLowerCase().indexOf(term) !== -1;
        card.hidden = !(matchesFilter && matchesSearch);
        if (!card.hidden) visible += 1;
      });
      if (results) results.textContent = visible + (visible === 1 ? ' notice found' : ' notices found');
      if (empty) empty.hidden = visible !== 0;
    }
    filters.forEach(function (button) { button.addEventListener('click', function () { filters.forEach(function (item) { item.classList.remove('is-active'); }); button.classList.add('is-active'); updateCards(); }); });
    var searchForm = board.querySelector('.toolkit-notice-search');
    if (search) search.addEventListener('input', updateCards);
    if (searchForm) {
      searchForm.addEventListener('submit', function (event) { event.preventDefault(); updateCards(); });
      searchForm.addEventListener('reset', function () { window.setTimeout(updateCards, 0); });
    }
    if (sort) sort.addEventListener('change', function () {
      cards.sort(function (a, b) {
        if (sort.value === 'title') return a.querySelector('h2').textContent.localeCompare(b.querySelector('h2').textContent);
        var comparison = (a.getAttribute('data-date') || '').localeCompare(b.getAttribute('data-date') || '');
        return sort.value === 'oldest' ? comparison : -comparison;
      });
      cards.forEach(function (card) { grid.appendChild(card); });
      updateCards();
    });
    board.querySelectorAll('[data-view]').forEach(function (button) { button.addEventListener('click', function () { board.querySelectorAll('[data-view]').forEach(function (item) { item.classList.remove('is-active'); }); button.classList.add('is-active'); grid.classList.toggle('is-list', button.getAttribute('data-view') === 'list'); }); });
    updateCards();
  }
  var courseChoice = document.getElementById('toolkit-course-choice');
  if (courseChoice) {
    var descriptions = { 'MIG/MAG Welding':'Build MIG/MAG welding technique through guided workshop practice and modern learning tools.', 'Renewable Energy':'Build practical foundations for work in renewable-energy and solar-focused environments.', 'Organic Farming Skills':'Develop practical skills for sustainable agriculture and enterprise.', 'Digital Skills':'Strengthen digital capabilities for changing work opportunities.', 'Consultancy and Research':'Explore Toolkit support for research and professional services.' };
    courseChoice.addEventListener('change', function () { document.getElementById('toolkit-course-title').textContent = courseChoice.value; document.getElementById('toolkit-course-description').textContent = descriptions[courseChoice.value]; });
  }

  var galleryDialog = document.querySelector('[data-gallery-dialog]');
  if (galleryDialog && typeof galleryDialog.showModal === 'function') {
    var dialogImage = galleryDialog.querySelector('img');
    var dialogCaption = galleryDialog.querySelector('p');
    document.querySelectorAll('[data-gallery-image]').forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        dialogImage.src = trigger.getAttribute('data-gallery-image');
        dialogImage.alt = trigger.getAttribute('data-gallery-alt');
        dialogCaption.textContent = trigger.getAttribute('data-gallery-alt');
        galleryDialog.showModal();
      });
    });
    galleryDialog.querySelector('[data-gallery-close]').addEventListener('click', function () { galleryDialog.close(); });
    galleryDialog.addEventListener('click', function (event) {
      if (event.target === galleryDialog) galleryDialog.close();
    });
  }
})();
