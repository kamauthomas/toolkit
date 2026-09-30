# Application QR code

The production homepage and `/apply/` were checked on 30 September 2026. The
page renders `toolkit-application-form` and uses Toolkit's same-origin
`toolkit/v1/application` endpoints. The QR opens this custom form; the existing
server-side admissions and Mzizi integration is unchanged.

Encoded destination:

https://toolkitafrica.ac.ke/apply/?utm_source=application_qr&utm_medium=qr&utm_campaign=admissions

Assets: `wp-content/themes/eduma-child/assets/images/application-qr.png` (980 px)
and `application-qr.svg` (scalable print asset). Preserve the white quiet zone;
use black on white and verify a printed sample before printing a batch.

Toolkit Control shows **Application QR scans** for 30 days and all time, plus a
preview and PNG/SVG download links. **Site analytics** shows the count for its
selected 30/90/365-day or all-time window. Both use the existing indefinite daily
aggregate table, event `interaction:application_qr_scan`, path `/apply/`.

A scan means a tagged form opening reported by the browser, once per browser
session. It is independent of page-view deduplication, so a previous untagged
form visit does not suppress a QR arrival. Refreshes are excluded when session
storage works. Do Not Track, blocked JavaScript/analytics, and scans that do not
open the page are not counted. A shared QR URL is indistinguishable from a scan;
these are aggregate arrivals, not unique people or submitted applications.
Demo and production keep separate counters. The printable QR always opens
production. Test demo tracking by applying the same query to demo's `/apply/`;
never submit a test application on production.

Verification: `node scripts/tests/application-qr-metrics.cjs`; PHP and JavaScript
syntax checks; decode the generated PNG; deploy demo first, verify the form,
collector and byte identity, then production. No historical scan counts exist
before this release.

## Deployment evidence — 30 September 2026

Release `2026.09.30.1` was deployed to demo, checked, then promoted to production.
The three patched code files and two QR assets were verified byte-identically.
Bare and uncached Apply routes returned 200 with the Toolkit form and versioned
metrics script; both environments returned the expected release header.
Demo's collector returned 204 for one QR test event and 403 without an origin.
No production application or synthetic production scan event was submitted.
Dashboard/analytics output was checked locally with WordPress stubs; the
authenticated live dashboard still needs staff acceptance.

Rollbacks: `rollbacks/demo-pre-2026.09.30.1/` and
`rollbacks/production-pre-2026.09.30.1/`.
