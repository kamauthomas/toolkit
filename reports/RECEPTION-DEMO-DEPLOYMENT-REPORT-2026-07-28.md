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

## Gallery and cache iteration

**Updated:** 28 July 2026

- Reworked the image gallery as a bright editorial photo zine with an
  asymmetric collage, issue labels, playful stickers, image captions and an
  accessible keyboard-closeable lightbox.
- Reworked the video gallery independently as a dark cinematic watch room with
  a live-channel hero, animated play signal, numbered episodes and neon cards.
- Retained the image gallery's shared production media source and YouTube's
  privacy-enhanced embed host.
- Identified the stale-client root cause: legacy `script_loader_src` and
  `style_loader_src` filters removed WordPress `?ver=` cache keys from every
  static asset.
- Removed those filters and introduced release `2026.07.28.4`, which combines a
  release identifier with each asset's modification time.
- Added a once-per-release WordPress object-cache and LiteSpeed page/CSS/JS
  purge hook plus the `X-Toolkit-Release` response header.
- Confirmed demo HTML now references versioned `page-redesign.css` and
  `page-redesign.js` URLs and that the release request produced a LiteSpeed
  cache miss.
- Added updated screenshots:
  `wordpress-demo-image-gallery-genz.png` and
  `wordpress-demo-video-gallery-genz.png`.

For future releases, increment `toolkit_theme_release()` in `functions.php`.
Deploy changed assets before `functions.php`, then request one demo URL with a
unique query parameter and verify the new `X-Toolkit-Release`, versioned asset
URLs and a LiteSpeed cache miss.
