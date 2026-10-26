# Toolkit Control refresh and Reception website applications

Toolkit Control uses a shared branded header, navigation, responsive summary cards,
tables and controls. Application, calling-letter, support, analytics and graduands
screens keep their existing permissions and actions. The overview describes actual
configuration instead of claiming an unmeasured healthy system.

Reception has a separate **Website applications** register, available to signed-in,
active reception, admissions, manager and administrator accounts after the required
password change. Every entry is labelled **Website application · Read-only sync**.
There are no create, edit, delete, release-to-Mzizi or write-back routes. Changes
continue to be made in Toolkit Control. Native Reception applicants/visits are
unchanged and do not receive duplicate registrations from this mirror.

## Operation and privacy

Reception runs `reception:sync-website-applications` every five minutes.
Production uses its existing scheduler; demo uses a dedicated read-only sync cron
because no general Reception scheduler was configured there. A run reads at most five pages of 50 entries, saves its progress
transactionally, and resumes a larger scan on the next run. Completed scans restart
from the beginning to refresh existing details/statuses. A shared lock prevents
overlapping workers; failures preserve existing copies and delay another attempt
for 30 minutes. Missing source entries are labelled after a complete successful
scan; their copies are retained, never automatically erased.

The website's Overview control **Share website applications with Reception** gates
the export. It defaults off until enabled. The existing shared integration secret
must be at least 32 characters. `TOOLKIT_RECEPTION_APPLICATION_SYNC_ENABLED`, if
defined, overrides the option. Reception uses its canonical environment's website,
or an explicit `WEBSITE_APPLICATIONS_SOURCE_URL`. Only the exact HTTPS production
and demo Toolkit hosts are permitted; redirects are refused.

The GET-only export uses timestamped, single-use signed requests bound to the
pagination parameters and this specific read operation. Nonces use atomic database
keys. Responses are private/no-store. Shared fields are reference, name, email,
primary phone, course/intake/campus, delivery/review status and relevant dates.
Guardian details, birth dates, qualifications, secondary contacts, raw application
payloads and upstream error bodies are excluded. Reception encrypts name, email and
phone at rest; operational state/logs contain counts, timestamps and safe error
codes. Staff detail views create an audit event without contact data.

## Release checks

Local checks: 202 deployment-boundary cases, 28 export/auth/privacy cases, 35
graduands/permission cases, 18 synthetic desktop/mobile browser checks, and the
Reception suite (95 tests / 1,364 assertions; the final focused sync rerun passes
13 tests / 113 assertions). PHP syntax and focused deployment preparation pass.
No production test application or Mzizi replay is used.

The exact release has 19 runtime files per environment: eight WordPress and eleven
Reception files, including an additive mirror-table migration. Live baselines are
checked before upload; focused patches preserve unrelated server code. Demo must
pass schema, initial read-only sync, schedule, byte comparisons and ordinary public
release checks before production. No reports, README/AGENTS, test fixtures, source
maps, archives, manifests or secrets are uploaded. Private release receipts under
`reports/toolkit-admin-sync-2026-10-26/` record actual environment verification;
preparation alone does not mean deployed. Signed-in staff acceptance remains a
separate human check.

Demo initially rejected legacy MySQL TIMESTAMP defaults during the additive table
creation. The setup cron was removed and production was held. Source/sync dates
now use DATETIME, matching existing Reception migrations. A disposable socket-only
MariaDB reproduced the exact error with legacy/strict defaults and passed the real
corrected migration with four DATETIME columns and zero application rows. Resume
is explicitly gated to the saved failure and exact fresh server hashes.
