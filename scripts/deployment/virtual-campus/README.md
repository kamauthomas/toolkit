# Virtual Campus temporary deployment

This directory holds the non-browser cPanel deployment used for the temporary
human-test instance at `campus-test.toolkitafrica.ac.ke`.

`deploy-campus-test-2026.09.01.3.sh` deploys the committed Campus application to
`/home/bfyigiln/virtual-campus-test`, whose `public/` directory is the subdomain
document root. It packages locked PHP dependencies and compiled assets locally,
uploads only an explicit environment assembled from whitelisted values, takes
an exclusive deployment lock, cleanly bootstraps the brand-new test database
and demo data through a short-lived cPanel cron, removes that cron and verifies
public routes/assets. It does not use browser clicks.

Required private files, both ignored by Git:

- `.toolkit-deploy/secrets.env` — existing cPanel session authorization.
- `.toolkit-deploy/virtual-campus-test.env` — generated database, application
  key and temporary demo password handoff.

The deployment copies only the four LiveKit settings required for the test from
the Campus repository’s local `.env`; no unrelated local secrets are carried.
The initial release kept registration disabled; release `2026.09.02.3` enables
it only for supervised staging. Mail stays on the local log driver and both
non-production rehearsal flags are explicit. Production ignores those overrides.

Run from the WordPress repository:

```bash
bash scripts/deployment/virtual-campus/deploy-campus-test-2026.09.01.3.sh
```

This is an initial-provisioning script and deliberately resets only the isolated
`bfyigiln_vcampus` test database. Do not rerun it after human testing starts. A
later release requires its own reviewed, non-destructive migration script and
worklog entry.

`deploy-campus-test-2026.09.01.4.sh` is that first non-destructive increment. It
backs up and uploads only the HTTPS security-header middleware, clears cached
application state under an exclusive lock, removes its temporary cron and
verifies HSTS on the real login page. It does not touch the database or demo
records created during acceptance testing.

`deploy-campus-test-readiness-2026.09.02.2.sh` non-destructively reconciles the
secret-safe readiness command with the explicit staging rehearsal gate. It
backs up/uploads only `routes/console.php`, refreshes caches, requires the real
host to report `ready: true` while retaining the production-caption warning,
creates Laravel's missing public-storage link, and leaves the database and
human-test records unchanged. The initial bootstrap script also retains that
storage-link step for any clean future test provision.

`deploy-campus-test-signup-2026.09.02.3.sh` is the reviewed, non-destructive
student sign-up increment. It backs up every replaced file and the private
environment, uploads the exact committed account-policy/controllers/views/CSS,
enables the non-production registration acknowledgement, refreshes caches and
requires readiness to pass. It then creates one named staging acceptance learner,
proves the account has no course, proves the role is Student and proves `/admin`
returns 403. It neither migrates nor resets the database and never prints a
password or environment secret.
