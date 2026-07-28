(function () {
  'use strict';

  var form = document.getElementById('toolkit-application-form');
  if (!form) return;

  var config = window.toolkitApplication || {};
  var panels = Array.prototype.slice.call(form.querySelectorAll('[data-step-panel]'));
  var stepButtons = Array.prototype.slice.call(document.querySelectorAll('[data-step-target]'));
  var progressBars = document.querySelectorAll('[data-progress-bar]');
  var progressLabels = document.querySelectorAll('[data-progress-label]');
  var nextButton = form.querySelector('[data-next]');
  var previousButton = form.querySelector('[data-previous]');
  var submitButton = form.querySelector('[data-submit]');
  var message = form.querySelector('.application-message');
  var schoolSelect = form.elements.school_id;
  var courseSelect = form.elements.course_id;
  var intakeSelect = form.elements.intake_id;
  var countySelect = form.elements.county;
  var sourceSelect = form.elements.referral_source;
  var currentStep = 0;
  var stepCopy = [
    ['1. Personal Details', 'Tell us who you are.'],
    ['2. Contact Details', 'How Admissions can reach you.'],
    ['3. Course Selection', 'Choose your preferred learning pathway.'],
    ['4. Background', 'Share relevant education and experience.'],
    ['5. Documents', 'Prepare supporting information safely.'],
    ['6. Review & Submit', 'Confirm your details before submission.']
  ];

  function api(url, payload) {
    var options = { credentials: 'same-origin', headers: { 'X-WP-Nonce': config.nonce } };
    if (payload) {
      options.method = 'POST';
      options.headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(payload);
    }
    return fetch(url, options).then(function (response) {
      return response.json().then(function (body) {
        if (!response.ok) throw new Error(body.message || 'Admissions options are temporarily unavailable.');
        return body;
      });
    });
  }

  function fillSelect(select, items, placeholder) {
    select.innerHTML = '';
    var first = document.createElement('option');
    first.value = '';
    first.textContent = placeholder;
    select.appendChild(first);
    (items || []).forEach(function (item) {
      var option = document.createElement('option');
      option.value = item.id;
      option.textContent = item.label;
      select.appendChild(option);
    });
    select.disabled = !(items && items.length);
  }

  function showMessage(text, type) {
    message.hidden = false;
    message.className = 'application-message ' + (type || 'is-info');
    message.textContent = text;
  }

  function loadOptions() {
    return api(config.optionsEndpoint).then(function (data) {
      fillSelect(schoolSelect, data.schools, 'Select campus');
      fillSelect(countySelect, data.counties, 'Select county');
      fillSelect(sourceSelect, data.sources, 'Select source');
    }).catch(function (error) {
      fillSelect(schoolSelect, [], 'Campuses unavailable');
      fillSelect(countySelect, [], 'Counties unavailable');
      fillSelect(sourceSelect, [], 'Sources unavailable');
      showMessage(error.message + ' You can still use the official Mzizi portal.', 'is-error');
    });
  }

  function loadCourses() {
    fillSelect(courseSelect, [], schoolSelect.value ? 'Loading courses…' : 'Select a campus first');
    fillSelect(intakeSelect, [], 'Select a course first');
    updateCourseGuide();
    if (!schoolSelect.value) return Promise.resolve();
    return api(config.coursesEndpoint, { school_id: schoolSelect.value }).then(function (data) {
      fillSelect(courseSelect, data.courses, data.courses.length ? 'Select course' : 'No courses available');
    }).catch(function (error) {
      fillSelect(courseSelect, [], 'Courses unavailable');
      showMessage(error.message, 'is-error');
    });
  }

  function loadIntakes() {
    fillSelect(intakeSelect, [], courseSelect.value ? 'Loading intakes…' : 'Select a course first');
    updateCourseGuide();
    if (!courseSelect.value) return Promise.resolve();
    return api(config.intakesEndpoint, { course_id: courseSelect.value }).then(function (data) {
      fillSelect(intakeSelect, data.intakes, data.intakes.length ? 'Select intake' : 'No open intake; contact Admissions');
      document.getElementById('toolkit-course-duration').textContent = data.intakes.length ? 'Intake dates loaded from Mzizi' : 'Contact Admissions';
    }).catch(function (error) {
      fillSelect(intakeSelect, [], 'Intakes unavailable');
      showMessage(error.message, 'is-error');
    });
  }

  function fieldsIn(panel) {
    return Array.prototype.slice.call(panel.querySelectorAll('input, select, textarea'));
  }

  function validateStep(index) {
    var invalid = fieldsIn(panels[index]).find(function (field) { return !field.checkValidity(); });
    if (invalid) {
      invalid.reportValidity();
      invalid.focus();
      return false;
    }
    return true;
  }

  function setStep(index, shouldScroll) {
    currentStep = Math.max(0, Math.min(index, panels.length - 1));
    panels.forEach(function (panel, panelIndex) { panel.hidden = panelIndex !== currentStep; });
    stepButtons.forEach(function (button, buttonIndex) {
      button.classList.toggle('is-active', buttonIndex === currentStep);
      button.classList.toggle('is-complete', buttonIndex < currentStep);
      button.removeAttribute('aria-current');
      if (buttonIndex === currentStep) button.setAttribute('aria-current', 'step');
    });
    var percent = Math.floor(((currentStep + 1) / panels.length) * 100);
    progressBars.forEach(function (bar) { bar.style.width = percent + '%'; });
    progressLabels.forEach(function (label) { label.textContent = percent + '% complete'; });
    document.querySelector('[data-step-number]').textContent = String(currentStep + 1);
    form.querySelector('[data-step-title]').textContent = stepCopy[currentStep][0];
    form.querySelector('[data-step-help]').textContent = stepCopy[currentStep][1];
    previousButton.hidden = currentStep === 0;
    nextButton.hidden = currentStep === panels.length - 1;
    submitButton.hidden = currentStep !== panels.length - 1;
    if (currentStep === panels.length - 1) renderReview();
    if (shouldScroll !== false) form.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function selectedText(name) {
    var select = form.elements[name];
    return select && select.selectedIndex >= 0 ? select.options[select.selectedIndex].textContent : '';
  }

  function renderReview() {
    var data = new FormData(form);
    var rows = [
      ['First name', data.get('first_name')], ['Middle name', data.get('middle_name')], ['Surname', data.get('surname')],
      ['Email', data.get('email')], ['Primary phone', data.get('primary_phone')], ['Secondary phone', data.get('secondary_phone')],
      ['Campus', selectedText('school_id')], ['Course', selectedText('course_id')], ['Intake', selectedText('intake_id')],
      ['County', selectedText('county')], ['Study mode', selectedText('study_mode')], ['Fee payment', selectedText('sponsorship_type')], ['Qualifications', data.get('qualifications')]
    ];
    var html = '<h3>Review your application</h3><dl>';
    rows.forEach(function (row) {
      if (row[1]) html += '<div><dt>' + escapeHtml(row[0]) + '</dt><dd>' + escapeHtml(String(row[1])) + '</dd></div>';
    });
    form.querySelector('#application-review').innerHTML = html + '</dl>';
    form.querySelector('[data-submit-note]').textContent = config.integrationActive ? 'Your application will be sent securely to Mzizi.' : 'Direct Mzizi submission is awaiting final approval. Continue to the official portal to apply.';
    submitButton.innerHTML = config.integrationActive ? 'Submit application <i class="fas fa-arrow-right" aria-hidden="true"></i>' : 'Continue to official portal <i class="fas fa-external-link-alt" aria-hidden="true"></i>';
  }

  function escapeHtml(value) {
    var div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
  }

  function updateCourseGuide() {
    var option = courseSelect.options[courseSelect.selectedIndex];
    var title = option && option.value ? option.textContent : 'Choose your course';
    document.getElementById('toolkit-course-title').textContent = title;
    document.getElementById('toolkit-course-description').textContent = option && option.value ? 'This programme is currently available in the selected Mzizi campus catalogue. Admissions will confirm delivery details, fees and entry requirements.' : 'Select a programme to see its current learning focus and admissions guidance.';
    document.getElementById('toolkit-course-duration').textContent = 'Confirmed by Admissions';
  }

  nextButton.addEventListener('click', function () { if (validateStep(currentStep)) setStep(currentStep + 1); });
  previousButton.addEventListener('click', function () { setStep(currentStep - 1); });
  stepButtons.forEach(function (button, index) { button.addEventListener('click', function () { if (index < currentStep || validateStep(currentStep)) setStep(index); }); });
  schoolSelect.addEventListener('change', loadCourses);
  courseSelect.addEventListener('change', loadIntakes);

  form.addEventListener('submit', function (event) {
    event.preventDefault();
    if (!validateStep(currentStep)) return;
    if (!config.integrationActive) {
      window.open(config.mziziHandoff, '_blank', 'noopener');
      showMessage('The official Mzizi application portal has opened in a new tab. Your Toolkit form data was not transmitted.', 'is-info');
      return;
    }
    var payload = {};
    new FormData(form).forEach(function (value, key) { payload[key] = value; });
    submitButton.disabled = true;
    showMessage('Submitting your application securely…', 'is-info');
    api(config.endpoint, payload).then(function (result) {
      showMessage(result.message || 'Application submitted successfully.', 'is-success');
      form.reset();
      setStep(0);
      if (window.turnstile) window.turnstile.reset();
    }).catch(function (error) {
      showMessage(error.message + ' Contact Admissions if the problem continues.', 'is-error');
    }).finally(function () { submitButton.disabled = false; });
  });

  setStep(0, false);
  updateCourseGuide();
  loadOptions();
}());
