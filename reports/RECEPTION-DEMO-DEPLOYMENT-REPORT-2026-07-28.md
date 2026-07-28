# Reception and Media Demo Deployment Report

**Date:** 28 July 2026  
**Environment:** Demo only  
**Production decision:** Hold pending user acceptance

## Outcome

The reception backend, dedicated demo database, staff portal, WordPress
reception form and redesigned image/video galleries are operational on demo.
The earlier reception HTTP 500 is resolved.

## Verification

| Check | Result |
|---|---|
| Reception public page | HTTP 200 |
| Reception staff login | HTTP 200 |
| Dedicated database migration/seed | Passed |
| Laravel automated suite | 18 tests, 92 assertions passed |
| Invalid website signature | HTTP 401 |
| Valid signed backend request | HTTP 201 with `WEB-` reference |
| WordPress same-origin relay | HTTP 200 with `WEB-` reference |
| Staff dashboard visibility | Both labelled review records visible |
| Public setup runner after use | HTTP 404 |
| WordPress reception route | HTTP 200 |
| Image gallery route and assets | HTTP 200; visual check passed |
| Video gallery route/embeds | HTTP 200; visual check passed |

## Release identifiers

- Reception implementation baseline: `e8bf6cc`
- MariaDB compatibility: `7ed75ae`, `f16790c`
- WordPress reception/media release: `34ce395`, `7b08ae7`

## Security and rollback

- Laravel application and `.env` remain outside the public document root.
- Demo database access is limited to a dedicated demo user.
- The website HMAC and administrator password are excluded from Git and reports.
- WordPress relays submissions and does not persist a second PII copy.
- The one-time setup runner was deleted immediately after use.
- A private pre-deployment WordPress rollback archive was created locally.
- Production was not configured or deployed.

## Acceptance evidence

- `wordpress-demo-reception.png`
- `wordpress-demo-image-gallery.png`
- `wordpress-demo-video-gallery.png`
- `reception-demo-staff-login.png`

All evidence is under `reports/deployment-evidence/2026-07-28/`.

## Remaining work

Obtain user and reception-owner acceptance, test mobile wording and workflow,
delete labelled demo records, rehearse rollback, establish retention/backup
ownership, create fresh production-only secrets/database, take production
backups, deploy backend before WordPress, verify, monitor and rotate cPanel
credentials. Production requires a separate explicit go-ahead.
