# Phone country metadata

PhoneCountries.php and phone-countries.js share a generated, local snapshot of Google libphonenumber mobile/fixed-line patterns for 245 countries/territories. English labels use CLDR through Python Babel. Kenya is first/default. Numbering metadata validates formatting/ranges, not subscriber ownership; OTP needs a configured sending provider.

Source: https://github.com/google/libphonenumber/blob/master/resources/PhoneNumberMetadata.xml — Apache License 2.0. The exact XML SHA256 is retained in source.json and both generated headers. Google's metadata is bundled locally; no runtime CDN request is required.

Rebuild with Python 3 and Babel:

```bash
python3 scripts/data/phone-numbers/generate.py PhoneNumberMetadata.xml /tmp/toolkit-phone-data
```

Review changes, then copy generated PHP/JS to the WordPress child theme's inc/assets/js and Reception app/Support/public/assets/js. Keep the shared PhoneNumbers.php / phone-input.js formatter in sync across both systems. Country options include Unicode regional flags. Calling codes typed with or without + are detected from the complete metadata; a redundant local trunk prefix is stripped only if the national pattern matches. Country selector values are validated at the server and only canonical international numbers are stored. Literal trunk prefixes are stripped only when the resulting number matches that country's pattern; meaningful Italian leading zeroes are preserved. Carrier-selection prefixes, extensions and non-geographic service numbers are not supported; use an ordinary full international contact number instead.

Run international-phone-parity.js, system-audit-application.php and Reception InternationalPhoneTest before deployment. Verify the demo before production and retain deployed byte identity.
