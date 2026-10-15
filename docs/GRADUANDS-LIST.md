# Class of 2026 graduands list

Use the permanent address `https://toolkitafrica.ac.ke/graduation/#graduands` for printed QR artwork. It opens the graduands section of the existing graduation page. The same QR continues to work when approved names are added or corrected.

The primary list is HTML: readable on phones, searchable by name, filterable by course and usable without JavaScript. This follows the W3C table guidance on captions and column headings: https://www.w3.org/WAI/tutorials/tables/. Search runs in the page and does not send typed names to a service. The print/save-as-PDF button produces the complete approved list even when screen filters are active. A fixed PDF can be issued later as an approved snapshot, without changing the QR destination.

## Publishing when the source arrives

1. Prepare a UTF-8 CSV with exactly the header `display_name,course`. Include only the approved public display name and course title for each graduand. Do not include telephone numbers, email addresses, student numbers or other private fields. No student-system connection is assumed.
2. In WordPress, an administrator opens **Toolkit Control → Graduands 2026** and uploads the CSV for a private preview.
3. Review every name and course. The parser rejects missing/extra fields, repeated name-and-course pairs, invalid encoding, markup, more than 2,000 names or a file larger than 1 MB. Names shared by different people need review before approval; spelling differences are not guessed or merged.
4. Confirm the names and courses are approved for public display, then publish that exact preview. A newer preview makes an older publish action invalid. Uploading alone never changes the public list.
5. Reload the public QR destination on a phone, search several names, check course filters and print the complete list. The administrator can replace the list through another preview or withdraw it while retaining the draft.

Until an approved source is published, visitors see “The confirmed list is being prepared.” No sample names or invented course totals are published. Draft and published values are separate private WordPress options; only approved display names, courses and publication date are rendered publicly. Uploads are read from PHP's temporary upload rather than placed in the public media library. The current feature is specific to the 2026 cohort; a later cohort should have its own options and year before reuse.

## Release evidence

The release uses the established demo-first, baseline-checked cPanel workflow. The QR images, import template, parser/admin checks, browser checks and deployment records are retained privately under `reports/graduands-2026-10-15/` and `reports/reception-graduands-2026-10-15/`. No real roster was provided during preparation. PNG artwork includes a white quiet zone; SVG should be printed on white.

The Toolkit Control dashboard now includes the graduands QR beside the existing QR cards, PNG/SVG downloads, the permanent live link, publication status, a link to manage names/courses, and a nonce-protected public-display switch. Hiding preserves the approved list and shows the pending message at the same QR destination.
