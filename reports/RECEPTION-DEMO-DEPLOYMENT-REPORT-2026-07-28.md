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

## Local gallery review hold

**Updated after stakeholder feedback:** 28 July 2026

The bright photo-zine and dark watch-room treatment deployed in release
`2026.07.28.4` was judged insufficiently aligned to Toolkit's established
orange, olive, teal and neutral palette. No further gallery change is
authorized for demo or production until the replacement is reviewed locally.

The replacement working concept is:

- an image gallery presented as a restrained vintage journey wall, using
  pinned photographs, a guide path and field-note captions; and
- a separate video gallery with a playable featured video in the hero,
  olive/orange brand treatment and a clear chapter library.

After UX research, the local video concept was refined to one large player with
an adjacent thumbnail playlist. This avoids multiple competing embeds, reduces
scrolling and makes the active story clear. Selection updates the player,
episode and title; native YouTube controls retain captions, keyboard playback
and full-screen access. Research references and the approval gate are recorded
in `docs/GALLERY-DESIGN-REVIEW-2026-07-28.md`.

A database-independent local review harness lives in
`review/gallery-preview/`. It reads existing public media, writes no data and
does not change WordPress.

The replacement was approved and deployed to demo as release `2026.07.28.6`.
The final release uses the vintage pinned journey wall, one primary video
player with six adjacent playlist controls, a reduced-height video hero and the
complete WordPress header/footer. Demo verification recorded HTTP 200,
`X-Toolkit-Release: 2026.07.28.6`, a LiteSpeed cache miss, versioned assets and
the expected player/playlist/footer markers. Production remains unchanged.
