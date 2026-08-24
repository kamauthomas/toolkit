# Approved 2026 pricing release

Release `2026.08.24.5` publishes the fees in Toolkit's approved admissions brochure dated 7 July 2026. The course directory shows a fee on every one of the 15 programme cards, and each course page shows the applicable standard, subsidised, package, or assessment note supplied by the brochure.

Admissions wording now distinguishes published prices from variable enrolment support. Visitors are asked to contact Admissions about installments, financing, subsidy eligibility, intake availability, and course-specific assessment costs; they are not asked to contact Admissions merely to discover the listed fee.

## Deployment

Run the dedicated browser-free release script from the repository root:

```bash
scripts/deployment/releases/deploy-approved-pricing-2026.08.24.5.sh demo
scripts/deployment/releases/deploy-approved-pricing-2026.08.24.5.sh production
```

The script reads `CPANEL_AUTH` from `.toolkit-deploy/secrets.env`, backs up each replaced remote file under `rollbacks/<environment>-pre-2026.08.24.5`, applies the fixed release commit to the remote copies, validates PHP, uploads through cPanel's API, and verifies the public release marker. It does not open or control a browser.

The production feature flags were enabled with a temporary CLI-only PHP runner executed by cPanel cron because WP-CLI is unavailable. The job set `toolkit_2026_catalog_enabled=1` and `toolkit_2026_pricing_enabled=1`, returned `{"catalog":1,"pricing":1}`, and then the cron entry, runner, and output file were removed.

## Verification

- Production returned `X-Toolkit-Release: 2026.08.24.5`.
- The public directory returned 15 course cards and 15 `2026 fees` labels.
- The French A1 page displayed KES 35,000 plus the KES 65,000 and KES 105,000 package options.
- The Electrical Installation page displayed KES 150,000 standard and KES 120,962 subsidised.
- The directory no longer contained the old instruction to contact Admissions for current fees.
