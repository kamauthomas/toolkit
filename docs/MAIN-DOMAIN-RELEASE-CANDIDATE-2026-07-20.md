# Main-Domain Release Candidate

**Prepared:** 20 July 2026

**Target:** `https://toolkitafrica.ac.ke`

**Release source:** `main`, deployed from `origin/wordpress-modernisation`

**Activation decision:** Explicitly authorized and completed after production database and access validation; full-files backup gap retained as a recorded residual risk

**Production result:** Activated and verified on 20 July 2026

## Production Activation Record

The reviewed 74-file `eduma-child` release was synchronized to `public_html/wp-content/themes/eduma-child` over certificate-verified FTPS. The existing parent theme and database content were retained; WordPress was not reinstalled and the demo database was not imported.

The host was positively identified as cPanel on TLS port 2083. Authenticated cPanel API access confirmed account `bfyigiln`, home `/home/bfyigiln`, WP Toolkit availability, Cron availability, and a `noshell` account shell. Because external SSH and interactive shell access were unavailable, activation ran through a CLI-only PHP file stored outside every document root and a temporary authenticated cPanel cron entry. The cron entry, result, script, and log were removed immediately after use; the final cron and private-home inventory found no `toolkit-release` artifact.

Activation set `template=eduma`, `stylesheet=eduma-child`, enabled `toolkit_redesign_enabled`, and kept both `toolkit_2026_catalog_enabled` and `toolkit_2026_pricing_enabled` disabled. Home page ID 4519 remains the static front page. LiteSpeed and rewrite state were purged/refreshed as part of activation.

Post-activation verification passed:

- Homepage, About, Courses, Notice Board, Blog, Contact, Connect, `llms.txt`, and `llms-full.txt` return HTTP 200.
- All eight preserved course routes return HTTP 200 with their distinct redesigned H1 and child-theme surface.
- Anonymous `/wp-json/wp/v2/users` returns HTTP 404.
- HSTS, nosniff, frame, referrer, and permissions headers are present.
- Child stylesheet, Toolkit logo, and responsive hero image return HTTP 200.
- Homepage contains the child-theme and Toolkit surface signatures, Apply actions, chatbot references, and YouTube media references.
- Homepage HTML contains no demo-domain reference and no insecure `http://toolkitafrica.ac.ke` reference.
- The saved production configuration defines both `WP_HOME` and `WP_SITEURL` with HTTPS. A non-HTTPS `site_url()` observed only during the cron/CGI execution context did not appear in public HTML and required no production configuration change.

Production screenshot evidence is stored persistently under ignored `rollbacks/production-20260720/screenshots/`: Home, About, and Courses were captured at 1440 by 1000 and 390 by 844. The first desktop capture exposed legacy submenu items rendering visibly over the hero. The child-theme dropdown CSS was corrected and redeployed; `home-desktop-postfix.png` confirms the submenu is hidden at rest while the modern header, hero, Apply actions, media prompt, and chatbot render together without that overlap. These screenshots supplement, rather than replace, the HTTP and HTML assertions above.

An end-user screenshot subsequently proved that the unparameterized homepage still served a stale pre-redesign LiteSpeed object even though cache-busted checks rendered the modern theme. Exact desktop, mobile, and curl probes reproduced the split: the plain URL returned a 283 KB legacy cache hit while query-string probes returned a 157 KB modern cache miss. The account-owned `/home/bfyigiln/lscache` contents were purged through a private one-time cPanel cron; the cron and completion marker were then removed. Final acceptance used the exact `https://toolkitafrica.ac.ke/` URL with no query string: the first desktop request was a modern cache miss and subsequent desktop/mobile requests were modern cache hits of the same 157 KB response. `home-plain-desktop-final.png` and `home-plain-mobile-final.png` are the definitive post-purge screenshots.

Review of that screenshot then identified a desktop navigation alignment defect: Eduma computed `.width-logo` as absolutely positioned, removing it from the header flex row and allowing navigation to begin beneath the logo. A rejected grid experiment was reverted before acceptance. Browser DevTools geometry confirmed the cause, after which the child theme restored the logo to normal flex flow and disabled the legacy desktop floats. The exact plain URL was purged and rechecked at 1440, 1366, and 390 pixels. `home-nav-corrected-desktop.png` and `home-nav-corrected-mobile.png` supersede the earlier homepage evidence and show the accepted header layout.

## Integrity Result

- Demo sitemap: 24 public/indexable pages after unused student/account/LearnPress utilities were removed from discovery.
- Demo routes: every indexed URL returned HTTP 200 during the integrity crawl.
- Priority assets: 45 of 45 same-domain assets returned a successful response.
- Priority internal navigation: no broken user-facing destination was found. The XML-RPC discovery URL is intentionally blocked.
- Current courses: eight preserved course routes return HTTP 200 with a distinct H1 and populated media.
- Course naming: public welding content uses MIG/MAG Welding; public digital content uses Digital Skills.
- Pricing: the September catalogue and prices remain disabled. The current Meta knowledge file passed its six-course pricing/duration validator; future data remains quarantined.
- Contacts: `+254 709 549 200` and `office@toolkitafrica.ac.ke` match between main and demo.
- Social destinations: Facebook, Instagram, LinkedIn, YouTube, X, WhatsApp Channel, and TikTok `@thetoolkitafrika` are represented on demo.
- Application: the guided route continues to the existing HTTPS Mzizi application URL.
- SPF: `v=spf1 include:spf.protection.outlook.com -all` is currently published for the root domain.
- Demo security: anonymous REST users and XML-RPC are blocked; metrics reject originless requests; HSTS and browser security headers are present.

## Retained Legacy Content

Research, Toolkit in Brief, TTI Videos, Gallery, and the young-women-in-agriculture page remain database-backed legacy layouts. Accurate titles, descriptions, and local preview images are now supplied, but Research still needs a visual/content rebuild because its Elementor body has no semantic H1 and contains empty image alternatives. These records must not be deleted during cutover.

## Production Preservation Model

The main-domain WordPress installation is not reinstalled. Do not import the demo database, replace WordPress core, overwrite plugins/uploads, or change DNS/site URLs. The fresh production export may be restored into an isolated local or staging database for validation, but it must not be replaced with the demo database. Synchronize only the reviewed `eduma-child` package while the three constants below are false:

```php
define( 'TOOLKIT_REDESIGN_ENABLED', false );
define( 'TOOLKIT_2026_CATALOG_ENABLED', false );
define( 'TOOLKIT_2026_PRICING_ENABLED', false );
```

With these values, main continues serving the existing presentation. After the legacy-state check, enable only `TOOLKIT_REDESIGN_ENABLED`, purge LiteSpeed, and execute the smoke test. The other two constants remain false.

## Required Preflight Evidence

1. Verified production transfer/hosting credentials and a trusted TLS connection.
2. Same-day full-files backup stored persistently, with archive listing checked.
3. Same-day database export stored persistently, with SQL header/table inventory checked.
4. Copies of live `wp-config.php`, `.htaccess`, `.user.ini`, active theme, plugin list, upload path, permalink structure, and LiteSpeed configuration.
5. Recorded production file ownership and permissions for `wp-content/themes/eduma-child`.
6. Named release operator, checker, rollback owner, and maintenance window.

## Production Access Preflight

The credentials supplied on 20 July 2026 authenticate successfully over explicit FTPS only when the certificate-valid hosting name `wp46.host-ww.net` is used. The certificate is valid for that hostname and not for `toolkitafrica.ac.ke`; production transfer commands must not bypass certificate verification.

The FTP account is currently jailed to an empty directory containing only `.ftpquota`. It cannot access `public_html`, `www`, `htdocs`, `domains/toolkitafrica.ac.ke/public_html`, or `wp-content`. No production file was uploaded or modified during this preflight. The hosting administrator must remap the account to the main-domain document root, or issue a least-privilege account rooted at that directory, before backup or deployment can proceed.

A second, non-jailed deployment account was subsequently supplied and successfully validated through `wp46.host-ww.net`. It can read the main WordPress document root through both `public_html/` and `domains/toolkitafrica.ac.ke/public_html/`. No write operation has been performed. The existing production child theme contains only `functions.php`, `style.css`, and `screenshot.png`; persistent preflight copies of those files and the critical root configuration are stored under ignored `rollbacks/latest-main-preflight/` with checksums.

Checksum comparison confirms the two document-root paths expose the same `wp-config.php`; use `public_html/` as the deployment path and do not upload twice. SSH port 22 is unavailable and the DirectAdmin endpoint did not respond during the preflight. The public WordPress settings API correctly requires authentication, so FTP cannot confirm or change the active theme. The supplied production export now records the active `template`/`stylesheet`; WordPress administrator access or a working hosting/database control channel is still required to activate the child theme and manage the rollout switches safely.

The server contains a 1.9 GB `local.tar` dated 30 June 2026, but this is not accepted as the required same-day files backup. `wordpress-backups/` is currently empty. A fresh server-side files archive remains mandatory.

## Production Database Snapshot

The user supplied `bfyigiln_new.sql2` as a local backup of the live production database on 20 July 2026. It is excluded from Git by the repository's root SQL-dump ignore rule and must not be deployed into a public directory.

- phpMyAdmin export timestamp: 20 July 2026, 13:19
- Source database: `bfyigiln_new`
- Source server: MariaDB 10.6.27
- SHA-256: `707cac4fe906dbbc004623e16d226ec5c6f2fbe1b248a76aeec103f769b95746`
- Isolated import: passed with 131 tables, all three checked core tables, and zero import errors
- Content inventory: 40 published pages, 150 published posts, 18 published products, and five WordPress users; no personal records were printed or copied into the report
- Active theme settings: `template=eduma`, `stylesheet=eduma`, `current_theme=Eduma`
- Permalink structure: `/%postname%/`

The snapshot exposes a configuration inconsistency: `home` is `https://toolkitafrica.ac.ke`, while `siteurl` is `https://mail.toolkitafrica.ac.ke`. Confirm the intended WordPress core location before changing it. If WordPress is installed at the root domain, correct `siteurl` to `https://toolkitafrica.ac.ke` during the approved maintenance window, then purge LiteSpeed and test login, admin assets, REST, uploads, and permalinks. Do not apply a blind search-and-replace to serialized data.

The public HTTP baseline currently shows:

- Eduma parent-theme assets are active; the redesign has not been accidentally activated.
- LiteSpeed is serving cached responses.
- Anonymous `/wp-json/wp/v2/users` is available and must return 404 after activation.
- `readme.html` and `license.txt` are publicly readable and should be denied by server configuration or removed after a full backup.
- `wp-config.php` and `.htaccess` are not publicly readable; XML-RPC is blocked by the server.
- HSTS, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, and `Permissions-Policy` are absent and must appear after activation.
- `WP_DEBUG` is false and dashboard file editing is disabled; no Toolkit rollout constants are currently defined.
- The production `.htaccess` contains plaintext `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ENV`, and `SECRET_KEY` environment directives. Remove the block from the live file during the approved maintenance procedure and rotate the password/secret values; never copy them into Git, reports, release archives, or chat output.
- The 106 MB production error log is active. Its latest 64 KB contains 18 memory-exhaustion fatal/uncaught entries at the 128 MB PHP limit and 303 Eduma undefined-option warnings/notices between 14 and 20 July. The fatal components observed most often were Elementor and WP Carousel; this baseline must be compared immediately after activation.

## Deployment Sequence

1. Build the archive with `bash scripts/build-main-release.sh 20260720-rc1`.
2. Verify `ARCHIVE-SHA256SUM`, `SHA256SUMS`, `GIT-COMMIT`, and the 74-file theme inventory.
   The build aborts if `.agents`, `.codex`, `AGENT_TRACE*`, `AGENTS.md`, office locks, or operating-system metadata enter the staged archive.
3. Upload the archive outside the web root or into a private deployment directory.
4. Extract into a new sibling directory, verify checksums, ownership, permissions, and PHP syntax.
5. Preserve the current production child theme as the latest persistent rollback.
6. Replace/synchronize only `wp-content/themes/eduma-child`; leave all switches false.
7. Purge LiteSpeed and verify the legacy homepage, form, login, posts, uploads, plugins, and sitemap contract.
8. Enable only `TOOLKIT_REDESIGN_ENABLED`, purge cache, then run the full smoke test.
9. Disable the switch once, purge cache, and prove the previous presentation returns. Re-enable only after approval.

## Production Smoke Test

- Home, About, courses, eight active course routes, Blog, Notice Board, guided application, Contact, Privacy, Connect, login/admin, and 404 return expected status/layout.
- Students Portal and duplicate Courses route return their approved 301 destination.
- Logo, course-specific media, testimonials, homepage video, chatbot, menus, compact footer, forms, and social links work at 390, 768, and 1440 pixel widths.
- Canonical, Open Graph, schema, sitemap, robots, `llms.txt`, and `llms-full.txt` contain the main hostname only.
- No PHP fatal, browser console error, mixed content, empty critical image, duplicate parent footer, or preloader stall appears.
- REST users remain unavailable, XML-RPC remains blocked, metrics accept only same-origin input, and all five browser security headers appear.
- Contact form mail and the Mzizi application handoff complete successfully.
- First request is healthy and the second request produces the expected LiteSpeed cache hit without stale demo URLs.

## Immediate Rollback

Set `TOOLKIT_REDESIGN_ENABLED` to `false` and purge LiteSpeed. This restores the previous database-backed presentation without moving or deleting content. If a file-level defect persists with the switch off, restore the latest child-theme archive and verify its checksums. Database restoration is reserved for a verified database-caused incident.

## Hold Points

- Do not activate main without the required backups and access evidence.
- Do not enable the 2026 catalogue without written Admissions approval.
- Do not enable September pricing based on date alone; the explicit approval flag and release authorization are required.
- Do not claim Research is modernized until its body layout, H1, image alternatives, and external legacy image references are rebuilt.
