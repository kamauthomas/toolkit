(function () {
  'use strict';

  var root = document.querySelector('.toolkit-chat');
  if (!root || !window.toolkitSupport) return;

  var toggle = root.querySelector('.toolkit-chat__toggle');
  var panel = root.querySelector('.toolkit-chat__panel');
  var close = root.querySelector('[data-chat-close]');
  var messages = root.querySelector('.toolkit-chat__messages');
  var actions = root.querySelector('.toolkit-chat__choices');
  var config = null;
  var initialConfig = window.toolkitSupport.config || null;

  function escapeHtml(value) {
    var node = document.createElement('div');
    node.textContent = value == null ? '' : String(value);
    return node.innerHTML;
  }

  function message(text, role, link) {
    var item = document.createElement('p');
    item.className = role === 'user' ? 'is-user' : 'is-assistant';
    item.textContent = text;
    if (link && link.url) {
      var anchor = document.createElement('a');
      anchor.href = link.url;
      anchor.textContent = link.label;
      item.appendChild(anchor);
    }
    messages.appendChild(item);
    messages.scrollTop = messages.scrollHeight;
  }

  function setOpen(open) {
    panel.hidden = !open;
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      loadConfig();
      close.focus();
    }
  }

  function showActions() {
    actions.innerHTML = '';
    Object.keys(config.topics).forEach(function (key) {
      var button = document.createElement('button');
      button.type = 'button';
      button.textContent = config.topics[key].label;
      button.addEventListener('click', function () {
        var topic = config.topics[key];
        message(topic.label, 'user');
        message(topic.reply, 'assistant', { url: topic.url, label: topic.linkLabel });
      });
      actions.appendChild(button);
    });
    addAction('Send an enquiry', showEnquiry);
    if (config.poll.enabled) addAction('Rate the new website', showPoll);
  }

  function addAction(label, handler) {
    var button = document.createElement('button');
    button.type = 'button';
    button.textContent = label;
    button.addEventListener('click', handler);
    actions.appendChild(button);
  }

  function loadConfig() {
    if (config) return Promise.resolve(config);
    if (initialConfig) {
      config = initialConfig;
      messages.innerHTML = '';
      message(config.greeting, 'assistant');
      showActions();
      return Promise.resolve(config);
    }
    return fetch(window.toolkitSupport.configEndpoint, { credentials: 'same-origin' })
      .then(function (response) {
        if (!response.ok) throw new Error('Support configuration unavailable.');
        return response.json();
      })
      .then(function (data) {
        config = data;
        messages.innerHTML = '';
        message(config.greeting, 'assistant');
        showActions();
        return config;
      })
      .catch(function () {
        messages.innerHTML = '';
        message('The website assistant is temporarily unavailable. Call +254 709 549 200 or email office@toolkitafrica.ac.ke.', 'assistant');
      });
  }

  function showEnquiry() {
    actions.innerHTML =
      '<form class="toolkit-chat__form" data-support-form="enquiry">' +
      '<label>Name<input name="name" autocomplete="name" maxlength="100" required></label>' +
      '<label>Email<input name="email" type="email" autocomplete="email" maxlength="160"></label>' +
      '<label>Phone<input name="phone" type="tel" autocomplete="tel" maxlength="40"></label>' +
      '<label>Subject<input name="subject" maxlength="140"></label>' +
      '<label>How can we help?<textarea name="message" rows="4" minlength="10" maxlength="2000" required></textarea></label>' +
      '<label class="toolkit-chat__consent"><input name="consent" type="checkbox" value="1" required> I agree that Toolkit may use these details to respond to this enquiry.</label>' +
      '<input class="toolkit-chat__trap" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">' +
      '<div class="toolkit-chat__form-actions"><button type="button" data-support-back>Back</button><button class="is-primary" type="submit">Send enquiry</button></div>' +
      '<p class="toolkit-chat__status" role="status"></p></form>';
    bindForm('enquiry');
  }

  function showPoll() {
    actions.innerHTML =
      '<form class="toolkit-chat__form" data-support-form="poll">' +
      '<strong>' + escapeHtml(config.poll.title) + '</strong><p>' + escapeHtml(config.poll.prompt) + '</p>' +
      '<fieldset><legend>Your overall rating</legend><div class="toolkit-chat__rating">' +
      [1, 2, 3, 4, 5].map(function (rating) {
        return '<label><input type="radio" name="rating" value="' + rating + '" required><span>' + rating + '</span></label>';
      }).join('') + '</div></fieldset>' +
      '<fieldset><legend>What has improved?</legend><div class="toolkit-chat__aspects">' +
      ['design', 'navigation', 'content', 'speed', 'mobile'].map(function (aspect) {
        return '<label><input type="checkbox" name="aspects[]" value="' + aspect + '"> ' + aspect.charAt(0).toUpperCase() + aspect.slice(1) + '</label>';
      }).join('') + '</div></fieldset>' +
      '<label>What should we improve next?<textarea name="comment" rows="3" maxlength="1000"></textarea></label>' +
      '<input class="toolkit-chat__trap" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">' +
      '<div class="toolkit-chat__form-actions"><button type="button" data-support-back>Back</button><button class="is-primary" type="submit">Submit rating</button></div>' +
      '<p class="toolkit-chat__status" role="status"></p></form>';
    bindForm('poll');
  }

  function bindForm(type) {
    var form = actions.querySelector('form');
    form.querySelector('[data-support-back]').addEventListener('click', showActions);
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var submit = form.querySelector('[type="submit"]');
      var status = form.querySelector('.toolkit-chat__status');
      var payload = {};
      new FormData(form).forEach(function (value, key) {
        if (key === 'aspects[]') {
          if (!payload.aspects) payload.aspects = [];
          payload.aspects.push(value);
        } else {
          payload[key] = value;
        }
      });
      payload.page = window.location.pathname;
      payload.consent = payload.consent === '1';
      submit.disabled = true;
      status.textContent = 'Sending...';
      fetch(type === 'poll' ? window.toolkitSupport.pollEndpoint : window.toolkitSupport.enquiryEndpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      }).then(function (response) {
        return response.json().then(function (body) {
          if (!response.ok) throw new Error(body.message || 'The response could not be saved.');
          return body;
        });
      }).then(function (body) {
        actions.innerHTML = '';
        message(body.message, 'assistant');
        addAction('Continue', showActions);
      }).catch(function (error) {
        status.textContent = error.message;
        submit.disabled = false;
      });
    });
  }

  toggle.addEventListener('click', function () { setOpen(panel.hidden); });
  close.addEventListener('click', function () { setOpen(false); toggle.focus(); });
}());
