# Toolkit Africa Production Readiness Audit

**Audit date:** 20 July 2026

**Audited release:** `main` / demo child-theme release

**Demo:** <https://demo.toolkitafrica.ac.ke>

**Decision:** Conditional no-go until the production gates below are completed

## Executive Summary

The scoped demo patch is deployed and the principal public journeys are operational. The homepage, Notice Board, course directory, all eight currently published course pages, application route, security controls, retired-route redirects, and mobile header behavior passed the checks recorded below. The unused Students Portal is no longer part of the public journey; its record is retained for rollback, while its URL redirects permanently to the course directory and is excluded from the sitemap.

The main-domain rollout must not reinstall WordPress or import the demo database. It must synchronize only the approved child-theme release while all rollout switches are off, verify that the existing site still renders, and then enable the redesign. Production remains a conditional no-go until current backups, verified production access, legacy-route decisions, and the rollback rehearsal are complete.

## Verified Fixes

| Area | Result | Evidence |
|---|---|---|
| Homepage | Pass | HTTP 200; deployed hero content and logo render on mobile |
| Mobile navigation | Pass | Drawer is hidden before deferred parent CSS loads; no menu overlap in the final mobile capture |
| Notice Board | Pass | Search/filter UI renders at tablet width; unsupported intake and language claims removed |
| Current course routes | Pass | Eight of eight preserved course URLs return HTTP 200 with distinct H1 and populated images |
| MIG/MAG naming | Pass | Public welding page uses `MIG/MAG Welding`; no new fabrication label was introduced |
| Students Portal | Pass | Old URL returns 301 to `/our-ventures/`; excluded from Yoast page sitemap |
| Duplicate legacy routes | Pass | `/courses/` redirects to `/our-ventures/`; `/blog/` redirects to `/toolkit-blog/` |
| Hero video | Pass | Placeholder replaced with click-to-load YouTube no-cookie embed; iframe removed when closed |
| REST user privacy | Pass | Anonymous `/wp-json/wp/v2/users` returns 404 |
| XML-RPC | Pass | Valid XML-RPC method request returns 403 |
| First-party metrics | Pass | Originless POST returns 403; same-origin POST returns 204; path input is restricted |
| Security headers | Pass on demo | HSTS, nosniff, SAMEORIGIN, referrer policy and permissions policy present |
| Author discovery | Pass | Staff author sitemap entries excluded; author archives marked noindex |
| Rollback files | Pass | Fresh pre-patch demo child-theme files retained in ignored `rollbacks/latest-demo/` |
| Code validation | Pass | Modified PHP syntax checks and `git diff --check` pass |

## Current Course Route Verification

The following preserved routes returned HTTP 200 and rendered primary course content:

- `/our-ventures/construction-sector-skills/` - MIG/MAG Welding
- `/our-ventures/renewable-energy/` - Renewable Energy
- `/our-ventures/organic-farming-skills/` - Organic Farming Skills
- `/our-ventures/access-online-jobs/` - Digital Skills
- `/our-ventures/construction-sector-skills/recognition-of-prior-learning-rpl/`
- `/our-ventures/tti-consultancy-and-research/`
- `/our-ventures/online-training-portal-jielimishe/`
- `/our-ventures/construction-sector-skills/training-welders-with-virtual-reality/`

The separate 2026 catalogue and September pricing remain controlled by independent switches. They must not be enabled before owner approval and the effective date.

## Open Issues and Decisions

### Launch blockers

1. Take same-day production database and full-files backups and verify both archives are readable outside a temporary directory.
2. Confirm main-host SFTP/FTPS or hosting access with valid TLS verification. Do not reuse the demo certificate bypass for production.
3. Record the live `wp-config.php`, active theme, LiteSpeed rules, plugins, uploads, permalink structure, and current switch values before file transfer.
4. Synchronize only the reviewed child theme with redesign, 2026 catalogue, and September pricing switches off. Do not reinstall WordPress, import the demo database, replace core, or overwrite plugins/uploads.
5. Verify the unchanged legacy presentation after sync, then activate only `TOOLKIT_REDESIGN_ENABLED`, purge LiteSpeed, and execute the release smoke test.
6. Rehearse instant presentation rollback by disabling `TOOLKIT_REDESIGN_ENABLED` and purging cache before the maintenance window closes.

### Content and indexing decisions

- `/research/`, `/about-toolkit-africa/toolkit-in-brief/`, `/tti-media/`, and `/gallery-2/` remain legacy pages. Their content owner must decide whether each is modernized, consolidated with a tested 301, or retained and improved before production indexing.
- `/eventer-shortcode-preview-page/` is excluded from the sitemap and marked noindex, but its WordPress record remains available for plugin compatibility.
- Account and LearnPress utility routes need a production decision because the public Students Portal is retired. Unused utility pages should be noindexed or restricted without disrupting administrator workflows.
- Production canonical, Open Graph, sitemap, robots, `llms.txt`, and `llms-full.txt` must be rechecked after the hostname switch. Demo metadata cannot be accepted as proof for the main hostname.

### Infrastructure controls

- Rotate any credentials that have ever been stored in plaintext deployment configuration. The local ignored `.htaccess` environment secret block was removed; no such secret is tracked in the inspected Git history.
- Confirm SPF with the organization email provider. DNS changes cannot be inferred safely from WordPress and must be approved by the mail owner.
- `ads.txt` is unnecessary unless Toolkit operates advertising inventory; its absence is not a launch blocker.
- Validate contact-form mail delivery, application handoff to Mzizi, chatbot responses, and staff metrics access on the main hostname.

## Production Smoke Test

1. Home, About, Our Courses, every active course URL, Blog, Notice Board, Application, Contact, Privacy, login/account, and 404 return the expected status and layout.
2. Logo, course-specific images, testimonials, homepage video, chatbot, menus, footer, forms, and external links work on mobile, tablet, and desktop.
3. No PHP fatal errors, browser console errors, mixed content, broken images, duplicate parent footer, or parent-theme loading screen appear.
4. Canonical and Open Graph URLs use `https://toolkitafrica.ac.ke/`; sitemap and AI discovery files contain production URLs.
5. Anonymous REST users remain unavailable; XML-RPC remains blocked; security headers remain present.
6. Originless metrics are rejected and same-origin events are recorded; administrator metrics remain capability-protected.
7. LiteSpeed shows a healthy first request and cache hit on the second request without serving stale demo URLs.
8. Disable the redesign switch once, purge cache, confirm the previous site returns, then re-enable only after approval.

## Rollout Recommendation

Proceed to the production staging/synchronization phase only after the launch blockers are signed off. The correct deployment model preserves the existing WordPress installation and database, adds the reviewed child-theme release alongside it, and changes presentation through the existing switch. A new WordPress installation is neither required nor permitted for this cutover.
