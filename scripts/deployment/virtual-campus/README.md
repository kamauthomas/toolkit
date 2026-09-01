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
Registration remains disabled, mail stays on the local log driver and the
non-production rehearsal flag is explicit. Production ignores that override.

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
