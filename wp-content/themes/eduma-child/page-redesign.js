(function () {
  'use strict';
  var board = document.querySelector('.toolkit-notice-page');
  if (board) {
    var cards = board.querySelectorAll('.toolkit-notice-grid article');
    var filters = board.querySelectorAll('[data-filter]');
    var search = board.querySelector('.toolkit-notice-search input');
    var grid = board.querySelector('.toolkit-notice-grid');
    function updateCards() {
      var active = board.querySelector('[data-filter].is-active');
      var filter = active ? active.getAttribute('data-filter') : 'all';
      var term = search ? search.value.toLowerCase().trim() : '';
      cards.forEach(function (card) {
        var matchesFilter = filter === 'all' || card.getAttribute('data-category') === filter;
        var matchesSearch = !term || card.textContent.toLowerCase().indexOf(term) !== -1;
        card.hidden = !(matchesFilter && matchesSearch);
      });
    }
    filters.forEach(function (button) { button.addEventListener('click', function () { filters.forEach(function (item) { item.classList.remove('is-active'); }); button.classList.add('is-active'); updateCards(); }); });
    if (search) search.addEventListener('input', updateCards);
    board.querySelectorAll('[data-view]').forEach(function (button) { button.addEventListener('click', function () { board.querySelectorAll('[data-view]').forEach(function (item) { item.classList.remove('is-active'); }); button.classList.add('is-active'); grid.classList.toggle('is-list', button.getAttribute('data-view') === 'list'); }); });
  }
  var courseChoice = document.getElementById('toolkit-course-choice');
  if (courseChoice) {
    var descriptions = { 'Welding and Fabrication':'Gain modern metal joining and fabrication skills in workshop and industry settings.', 'Renewable Energy':'Build practical foundations for work in renewable-energy and solar-focused environments.', 'Organic Farming Skills':'Develop practical skills for sustainable agriculture and enterprise.', 'Digital Skills and Online Jobs':'Strengthen digital capabilities for changing work opportunities.', 'Consultancy and Research':'Explore Toolkit support for research and professional services.' };
    courseChoice.addEventListener('change', function () { document.getElementById('toolkit-course-title').textContent = courseChoice.value; document.getElementById('toolkit-course-description').textContent = descriptions[courseChoice.value]; });
  }
})();
