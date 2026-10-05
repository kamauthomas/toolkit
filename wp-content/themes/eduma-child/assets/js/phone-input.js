(function () {
    'use strict';
    var countries = window.ToolkitPhoneCountries || {};
    function normalize(value, country) {
        if (typeof value !== 'string' || value.length > 40 || !/^[+0-9\s()-]+$/.test(value)) return null;
        var metadata = countries[country];
        if (!metadata) return null;
        var compact = value.replace(/[\s()-]/g, '');
        var candidates;
        if (compact.startsWith('+')) {
            if (!compact.startsWith('+' + metadata.code)) return null;
            var national = compact.slice(metadata.code.length + 1);
            candidates = [national];
            if (metadata.prefix && national.startsWith(metadata.prefix)) candidates.push(national.slice(metadata.prefix.length));
        } else {
            if (!/^[0-9]+$/.test(compact)) return null;
            candidates = [compact];
            [metadata.prefix, metadata.code].forEach(function (prefix) {
                if (prefix && compact.startsWith(prefix)) candidates.push(compact.slice(prefix.length));
            });
        }
        if (!compact.startsWith('+') && compact.startsWith(metadata.code)) {
            var nationalWithPrefix = compact.slice(metadata.code.length);
            if (metadata.prefix && nationalWithPrefix.startsWith(metadata.prefix)) candidates.push(nationalWithPrefix.slice(metadata.prefix.length));
        }
        var pattern = new RegExp('^(?:' + metadata.pattern + ')$');
        for (var national of candidates) {
            var full = metadata.code + national;
            if (full.length <= 15 && pattern.test(national)) return '+' + full;
        }
        return null;
    }
    function countryFor(value, preferred) {
        if (normalize(value, preferred || 'KE')) return preferred || 'KE';
        if (typeof value !== 'string' || value.length > 40) return null;
        var digits = value.replace(/[\s()-]/g, '').replace(/^\+/, '');
        if (!/^[0-9]+$/.test(digits)) return null;
        return Object.keys(countries).sort(function (a, b) { return countries[b].code.length - countries[a].code.length || Number(countries[b].main) - Number(countries[a].main); }).find(function (country) { return digits.startsWith(countries[country].code) && !!normalize(value, country); }) || null;
    }
    function selector(field) { return field.form && field.form.elements[field.dataset.phoneCountryField || field.name + '_country']; }
    function validate(field) {
        var country = selector(field)?.value || 'KE';
        var metadata = countries[country];
        var normalized = normalize(field.value.trim(), country);
        var message = field.value.trim() && !normalized ? 'Enter a valid phone number for ' + (metadata ? metadata.name + ' (+' + metadata.code + ')' : 'the selected country') + '.' : '';
        field.setCustomValidity(message);
        field.setAttribute('aria-invalid', message ? 'true' : 'false');
        return {number: normalized, message: message};
    }
    window.ToolkitPhone = {normalize: normalize, countryFor: countryFor, validate: validate};
    function init() {
        document.querySelectorAll('[data-international-phone]').forEach(function (field, index) {
            var country = selector(field);
            if (!country) return;
            var preview = document.createElement('small');
            preview.className = 'toolkit-phone-preview';preview.id = 'phone-preview-' + index;preview.setAttribute('aria-live', 'polite');
            field.closest('.toolkit-phone-control').after(preview);
            field.setAttribute('aria-describedby', ((field.getAttribute('aria-describedby') || '') + ' ' + preview.id).trim());
            function update(convert, detect) {
                if (detect) {
                    var inferred = countryFor(field.value, country.value);
                    if (inferred) country.value = inferred;
                }
                var result = validate(field);
                var metadata = countries[country.value];
                field.placeholder = metadata ? '+' + metadata.code + metadata.example : 'Phone number';
                if (convert && result.number) field.value = result.number;
                preview.textContent = result.message || (result.number ? 'International format: ' + result.number : 'Choose a country; valid numbers are saved with its calling code.');
                field.dispatchEvent(new CustomEvent('toolkit-phone-change', {bubbles: true}));
            }
            field.addEventListener('input', function () { update(false, true); });
            field.addEventListener('blur', function () { update(true, true); });
            country.addEventListener('change', function () { update(false, false); });
            field.form.addEventListener('submit', function () { update(true, true); }, true);
            update(false, false);
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init); else init();
}());
