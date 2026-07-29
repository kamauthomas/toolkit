(function () {
	'use strict';

	var form = document.querySelector('[data-reception-form]');
	if (!form || typeof window.toolkitReception !== 'object') {
		return;
	}

	var submit = form.querySelector('[type="submit"]');
	var status = form.querySelector('[data-form-status]');

	form.addEventListener('submit', function (event) {
		event.preventDefault();
		if (!form.reportValidity()) {
			return;
		}

		submit.disabled = true;
		status.className = 'reception-form-status';
		status.textContent = 'Sending securely to reception…';

		var payload = Object.fromEntries(new FormData(form).entries());
		payload.consent = form.elements.consent.checked;

		fetch(window.toolkitReception.endpoint, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.toolkitReception.nonce
			},
			body: JSON.stringify(payload)
		})
			.then(function (response) {
				return response.json().then(function (body) {
					if (!response.ok) {
						throw new Error(body.message || 'Reception could not accept the request.');
					}
					return body;
				});
			})
			.then(function (body) {
				form.reset();
				status.className = 'reception-form-status is-success';
				status.textContent = body.message + ' Reference: ' + body.reference;
			})
			.catch(function (error) {
				status.className = 'reception-form-status is-error';
				status.textContent = error.message + ' You can also call +254 709 549 200 or WhatsApp +254 711 802 855.';
			})
			.finally(function () {
				submit.disabled = false;
			});
	});
}());
