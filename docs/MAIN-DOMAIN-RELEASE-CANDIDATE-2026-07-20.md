# Main-Domain Release Candidate

**Prepared:** 20 July 2026

**Target:** `https://toolkitafrica.ac.ke`

**Release source:** `main`, deployed from `origin/wordpress-modernisation`

**Activation decision:** Hold until backup and production-access gates pass

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

The main-domain WordPress installation is not reinstalled. Do not import the demo database, replace WordPress core, overwrite plugins/uploads, or change DNS/site URLs. Synchronize only the reviewed `eduma-child` package while the three constants below are false:

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

The public HTTP baseline currently shows:

- Eduma parent-theme assets are active; the redesign has not been accidentally activated.
- LiteSpeed is serving cached responses.
- Anonymous `/wp-json/wp/v2/users` is available and must return 404 after activation.
- `readme.html` and `license.txt` are publicly readable and should be denied by server configuration or removed after a full backup.
- `wp-config.php` and `.htaccess` are not publicly readable; XML-RPC is blocked by the server.
- HSTS, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, and `Permissions-Policy` are absent and must appear after activation.

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
