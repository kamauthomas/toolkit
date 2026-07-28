# Website Reception Integration

**Prepared:** 27 July 2026

**Automated local status:** request path verified; no review server remains running

**Remote status:** isolated demo backend, database, website relay and staff portal operational; production is acceptance-gated

## Outcome

The public route `/reception/` collects the reception system's existing visitor
fields. The browser sends them to a same-origin WordPress REST endpoint.
WordPress validates and rate-limits the request, then signs the exact JSON body
and relays it to the reception application. Only the reception application
stores the record.

Website requests are deliberately recorded as:

- `source=website`
- `status=follow_up`
- `submitted_at=<server time>`
- `checked_in_at=null`

They appear in Reception Control but do not inflate physical attendance,
purpose, or QR check-in metrics.

## Security contract

The two servers share one random secret of at least 32 characters. WordPress
holds it only on the server. Each relay includes a Unix timestamp, a random
one-use nonce and:

```text
HMAC-SHA256(timestamp + "\n" + nonce + "\n" + raw JSON body)
```

Laravel rejects missing/incorrect signatures, stale timestamps and replayed
nonces. WordPress additionally enforces same-origin requests, a WordPress REST
nonce, a honeypot and a five-attempt-per-hour IP limit. The browser never
receives the shared secret. Request bodies and PII must not be added to logs or
analytics.

## Configuration

Reception application environment:

```dotenv
RECEPTION_WEBSITE_SHARED_SECRET=<random secret, at least 32 characters>
RECEPTION_WEBSITE_SIGNATURE_TOLERANCE=300
```

WordPress private configuration:

```php
define( 'TOOLKIT_RECEPTION_API_URL', 'https://<reception-host>' );
define( 'TOOLKIT_RECEPTION_API_SECRET', '<same random secret>' );
define( 'TOOLKIT_RECEPTION_FORM_ENABLED', true );
```

Keep these definitions outside the child theme. Demo and production must use
different secrets. If URL or secret is absent, the page remains available with
the phone fallback but submission is disabled.

## Local verification performed

1. Backed up the reception SQLite database to
   `/tmp/toolkit-reception-pre-website-2026-07-27.sqlite`.
2. Applied migration `2026_07_27_080000_add_website_submission_fields_to_visits`.
3. Ran 18 Laravel tests with 92 assertions: all passed.
4. Ran Laravel Pint: passed.
5. Rendered `http://127.0.0.1:8001/reception/` through WordPress: HTTP 200.
6. Submitted labelled test data through WordPress to Laravel: HTTP 200 from the
   relay and a generated `WEB-` reference.
7. Confirmed the reception record was a website follow-up with no check-in
   timestamp.
8. Deleted both labelled integration test records; no test PII remains.

## Safe deployment sequence

### Hosting precondition

Provision a PHP/Laravel application host whose document root is exactly the
reception application's `public/` directory. `.env`, the database, `storage/`,
vendor files and application source must not be exposed beneath either
WordPress document root. Use MySQL or PostgreSQL with encrypted backups for
production use.

### Demo

1. Create a demo reception host and database with a private application root.
2. Deploy the reception code and dependencies.
3. Configure production-style Laravel settings with a demo-only HMAC secret.
4. Back up the demo database, run `php artisan migrate --force`, cache
   configuration/routes/views, and configure the scheduler.
5. Verify staff login, signed submission, replay rejection, register visibility
   and exclusion from attendance.
6. Back up and deploy the reviewed child-theme files to
   `demo.toolkitafrica.ac.ke`.
7. Add the three WordPress constants using the demo API URL/secret, purge
   LiteSpeed and verify `/reception/` at desktop and mobile widths.
8. Submit one labelled demo record, verify it in Reception Control, then delete
   it.

### Main domain

Promote only after demo acceptance. Repeat the backups and checks using a new
production secret and the production reception API URL. Deploy the reception
backend first, then the WordPress theme and private constants. Keep
`TOOLKIT_RECEPTION_FORM_ENABLED` false until backend health, TLS and migration
checks pass; enable it last and purge LiteSpeed.

## Rollback

Disable the WordPress form immediately:

```php
define( 'TOOLKIT_RECEPTION_FORM_ENABLED', false );
```

The public page then gives the reception phone fallback and sends no data.
Restore the previous child theme only if the page itself is defective. Roll
back the Laravel release separately; do not reverse the nullable timestamp
migration while website follow-up records exist. Rotate the HMAC secret if
exposure is suspected.

## Current deployment hold

The known WordPress FTPS paths expose WordPress document roots, not a confirmed
private Laravel application root. Deploying the whole Laravel application
inside either public WordPress tree would expose sensitive files or rely on
fragile denial rules. Remote deployment therefore requires the hosting
administrator to provide:

- demo and production reception hostnames;
- private application paths with public roots mapped to `public/`;
- database credentials and backup access;
- a scheduler/cron facility; and
- certificate-valid HTTPS for both hosts.

## Deployment checkpoint — 28 July 2026

The demo deployment is complete and ready for acceptance. The independent
Laravel application has a private environment, dedicated least-privilege
database/user, applied migrations, seeded administrator, writable runtime and
cached production configuration. Public setup tooling was removed after use.

The WordPress demo now exposes the reception form and modern image/video
galleries. A same-origin WordPress submission returned a `WEB-` reference and
appeared in the authenticated Reception Control dashboard as a website
follow-up without increasing physical attendance. Review evidence is stored in
`reports/deployment-evidence/2026-07-28/`.

Production remains unchanged and gated on explicit acceptance. The exact
review URLs, cleanup items and promotion sequence are recorded in
`RECEPTION-DEMO-RESTART-HANDOFF-2026-07-27.md`.
