# Worklog — Toolkit WordPress site

Newest first. One entry per meaningful change, written in the same commit as the
change itself. Format and rules: see `../AGENTS.md` §1.

---

## 2026-08-21 — Prepare a curated Windows work transfer
**Area:** workstation organisation · continuity
**Environments:** local Linux and Windows NVMe only; not deployed
**Commit(s):** this commit

Added a repeatable, browser-free export that organises clean project snapshots,
reports, planning records, design/poster assets, editable XCF sources, and safe
admissions tooling on the Windows Desktop. Documented the archive layout and
explicitly excluded credentials, databases, private storage, applicant records,
generated letters, dependencies, caches, logs, rollback archives, and agent
worktrees.

**Verified by:** shell syntax validation and a reviewed source/exclusion inventory;
the completed copy and checksum results will be recorded after the one-time run.
**Follow-ups:** run the export against the writable Windows NVMe, scan the result
for secret-bearing paths/content, and verify the checksum manifest.

## 2026-08-20 — Close the post-containment full-account scan
**Area:** security incident cleanup
**Environments:** production hosting account ✅
**Commit(s):** operational result recorded in this commit

Retrieved and classified the historical cPGuard log, then ran a new HOME scan
after account cleanup and webroot quarantine. The historical report's “3” was
three outdated CMS installations, not malware. Post-containment scan #79
completed successfully after 30 minutes 59 seconds with 99,101 files tested,
zero infected files, and zero malware-detail rows.

**Verified by:** cPGuard scan #79 terminal report and its server-side detailed
log (`iTotalRecords: 0`); production public smoke routes remained available.
**Follow-ups:** patch the active Eduma 5.3.0 parent theme with a licensed 5.7.7+
package, demo first.

## 2026-08-20 — Deactivate unused vulnerable Livemesh add-on
**Area:** security operations
**Environments:** production ✅
**Commit(s):** server-side containment recorded in this commit

cPGuard's historical CMS report identified Livemesh Addons for WPBakery 3.9.4
as active with four no-patch stored-XSS findings. A read-only database check
found zero references in post content, post metadata, or relevant options, so
the exact active-plugin list was backed up to private storage and only that
plugin was deactivated. Its files remain available for rollback; nothing was
deleted.

**Verified by:** the mutation returned active-before and inactive-after with a
private rollback record; its endpoint self-deleted to 404. Home, Apply, About,
Graduation, Testimonials, and Footprint remained HTTP 200 with no fatal-error
markers.
**Follow-ups:** obtain Eduma 5.7.7 or newer from the licensed source and test
the parent-theme upgrade on demo before production; the local 5.3.0 archive is
not a fix.

## 2026-08-20 — Correct calling-letter delivery claims and finish story metadata
**Area:** admissions reliability · SEO
**Environments:** demo ✅ · production ✅
**Commit(s):** this commit

Disabled calling-letter email by default because this host's unauthenticated
mail transport is not authorised by Toolkit's Microsoft 365 SPF policy. Prior
"sent" rows are now labelled delivery-unverified, future `wp_mail()` success is
recorded only as mail-server submission, and secure PDF/Word generation remains
automatic. Reviewed the pending story Yoast publisher change, made its Cultural
Week migration stop cleanly on a WordPress write error, and retained a guarded,
browser-free deployment script for the release.

**Verified by:** all 33 child-theme PHP files linted; guarded release
`2026.08.20.2` fetched all five deployed files back byte-identical and returned
200 across Apply and seven affected story routes on demo then production. A
self-deleting aggregate verifier confirmed schema 1.2.0, email off, zero legacy
"sent" rows (four prior attempts relabelled on demo, eight on production), and
all seven Yoast metadata records exact on both environments. No application or
email was submitted.
**Follow-ups:** configure and test authenticated SMTP or a transactional mail
transport before staff enable the Email channel.

## 2026-08-20 — Remove confirmed rogue access and quarantine attack surface
**Area:** security incident cleanup
**Environments:** production ✅
**Commit(s):** this commit

Took a fresh full production database backup (135 tables; compressed SHA-256
recorded privately), then verified and deleted only the nine malicious
Administrator accounts IDs 7–15. Preserved legitimate users IDs 1, 3, 4, 5
and 6, revoked legitimate administrators' sessions/application passwords,
rotated all eight WordPress keys/salts atomically, kept registration disabled,
and removed the abandoned install's cron entry. Moved Eventer 3.9.6, the
abandoned `Old Sites` install, `local.tar`, `uploads/addon.zip`, and the
one-time maintenance endpoint outside every webroot into private quarantine.

**Verified by:** authenticated post-cleanup query returned only the five
legitimate users; all eight salts differ from the backed-up configuration;
Eventer is inactive and absent from production; private quarantine contains all
five moved targets; production home, Apply and admin-login routes remain live.
**Follow-ups:** legitimate administrators should choose new personal passwords
at their next login; obtain and demo-test a licensed Eduma 5.7.7+ package.

## 2026-08-20 — Make admissions retries and calling-letter writes safe
**Area:** admissions reliability
**Environments:** demo ✅ · production ✅
**Commit(s):** this commit

Removed the unsupported assumption that every Mzizi HTTP 4xx proves no record
was stored. All uncertain submission outcomes now require a human to check
Mzizi; staff can then explicitly confirm an existing record or re-send only
after the existing mandatory duplicate check. Replaced calling letters'
select-then-insert race with one atomic `INSERT ... ON DUPLICATE KEY UPDATE`.

**Verified by:** PHP lint, focused source assertions and full child-theme
whitespace checks; demo then production release `2026.08.20.1` returned 200 on
cache-busted Apply routes and all three deployed files fetched back
byte-identical. No production application was submitted.
**Follow-ups:** none.

## 2026-08-20 — Contain confirmed WordPress compromise exposure
**Area:** security operations
**Environments:** production ✅
**Commit(s):** this commit

Reviewed the 14 August Element Pack supply-chain incident evidence and current
server state without attaching to an operator's browser. Eventer 3.9.6 and the
abandoned `Old Sites` WordPress tree remained present; the latter's
`readme.html` and 180 MB `toolkitiskills.zip` were publicly downloadable.
Added a retained, browser-free cPanel containment script, backed up the original
production root `.htaccess`, blocked both abandoned-site artifacts, and placed a
deny-all quarantine rule in Eventer's directory. This is reversible containment
only: no users or files were deleted, and credential/session rotation is still
required.

**Verified by:** remote rules fetched back byte-identical; the two abandoned
artifacts and Eventer's main PHP file return 403; production home and Apply
remain 200. Known `wp-smart-thumbnails/emer-run.php` and `/w2.js` indicators
remain 404, while the previously protected `local.tar` and `uploads/addon.zip`
remain 403.
**Follow-ups:** completed by the cleanup entry above, except for staff-selected
password changes and retrieval of the completed cPGuard full-account scan.

## 2026-08-20 — Publish the institutional programme footprint
**Area:** website content
**Environments:** demo ✅ · prod ✅
**Commit(s):** this commit

Added a theme-owned `/footprint/` page that presents 28 dated programme
milestones and their named partners from 2014 through current work extending to
2026. The route includes canonical metadata, schema and sitemap integration,
with a responsive timeline that does not depend on a database page builder. The
browser-free, drift-preserving release script and operating notes are retained
under `scripts/deployment/` for manual inspection and future reuse.

**Verified by:** full child-theme PHP lint, JavaScript syntax and whitespace
checks; demo desktop/mobile headless renders; demo then production returned 200
uncached and from LiteSpeed cache on release `2026.08.12.12`, with one H1, 13
eras, 28 milestones, canonical/robots/schema signals and sitemap inclusion.
Home, Apply, Graduation and Testimonials remained 200 on production.
**Follow-ups:** none.

## 2026-08-20 — Make a rejected Mzizi submission re-sendable
**Area:** admissions relay
**Environments:** demo ✅ · prod ✅
**Commit(s):** ae081f4

Mzizi answers `SubmitOnlineApplication` with HTTP 409 when it declines an
application. Every non-2xx result was being treated as `delivery_unconfirmed`,
and the admin hid the release form for failed records — so a 409 left the
application with no way to re-send it at all; staff could only mark it
delivered. Now a 4xx (Mzizi explicitly did not store it, so a re-send cannot
duplicate the applicant) marks the record `relay_failed` and retryable, and the
Applications screen offers "Re-send to Mzizi" with the last error shown inline.
Ambiguous outcomes (timeouts, 5xx, unreadable bodies) still become
`delivery_unconfirmed` and must be checked in Mzizi first.

**Verified by:** anchored deploy confirmed on both servers (4xx branch and
re-send control present); `toolkitafrica.ac.ke/apply/` → 200 with release
`2026.08.12.11`, form present, no PHP errors.
**Follow-ups:** open a `relay_failed` record in the demo admin and confirm the
new "Re-send to Mzizi" button renders. No relay was exercised on production, per
the no-test-applications rule.

## 2026-08-20 — Pair each About long-form subsection with its own image
**Area:** website content
**Environments:** demo ✅ · prod ✅
**Commit(s):** 5d5c6f1

The About page's "A learner-centred approach" section reused the compact
two-column intro layout, so eight paragraphs formed one tall block of text
beside a single short image, leaving a large empty gap. Split it into a lead
paragraph plus one image/text row per subsection, alternating sides down the
page and stacking on small screens, using topic-appropriate course photography
rather than repeating the hero image.

**Verified by:** `demo…/about-toolkit-africa/` — 3 rows, images alternate sides
(measured), course images return 200; prod returns 200 with 3 rows, no PHP errors.
**Follow-ups:** none.

## 2026-08-20 — Refresh Toolkit Control admin into a branded console
**Area:** admin UI
**Environments:** demo ✅ · prod ✅
**Commit(s):** 7918e67

Rebuilt `assets/css/toolkit-admin.css` as one design system: instrument-style
header band, legible eyebrow and state badge (the reported low-contrast header
text), branded admin notices, a shared status-chip language, consistent radii,
shadows, hover and focus states. All existing class names preserved, so no
render markup changed.

**Verified by:** Toolkit Control and Applications screens loaded on demo;
contrast issue resolved; prod file verified byte-identical after copy.
**Follow-ups:** keep this UI plain — no further visual iteration unless asked.

## 2026-08-20 — Publish graduation and testimonials showcase to production
**Area:** website content
**Environments:** prod ✅ (was demo-only)
**Commit(s):** b41c546 (deployed, not newly authored)

Copied the graduation and testimonials page templates, 14 graduation images, and
the supporting `footer.php` / `front-page.php` / `page-redesign.css` changes from
demo to production, where they had been committed but never deployed. Routes are
rewrite-driven, and the rules already existed on prod.

**Verified by:** `/graduation/` and `/testimonials/` both 200 on production with
correct titles and images present, no PHP errors.
**Follow-ups:** none.

## 2026-08-20 — Applicant success reliability + Mzizi catalog caching
**Area:** admissions
**Environments:** demo ✅ · prod ✅
**Commit(s):** 5c47631

Applicants now get an immediate success confirmation once their application is
stored locally; the Mzizi relay runs after the response is flushed, so a slow or
failing upstream can never turn a saved application into an applicant-facing
error. The confirmation auto-dismisses after 12s. Mzizi catalog lookups
(campuses, courses, intakes) are cached in 15-minute transients, fixing the slow
course dropdowns.

**Verified by:** `/apply/` 200 on both environments with the new asset version;
options endpoint returned 200 with 3 campuses.
**Follow-ups:** none.

## 2026-08-20 — Housekeeping: removed exposed theme backups
**Area:** security
**Environments:** demo ✅
**Commit(s):** n/a (server-side only)

Moved `functions.php.pre-calling-letters-20260817.bak` and the matching
`inc/application-adapter.php` backup to cPanel trash. This host serves static
extensions directly, bypassing `.htaccess`, so a `.bak` in the webroot was
readable source.

**Verified by:** no `.bak` files remain in the demo theme root or `inc/`.
**Follow-ups:** empty cPanel trash to remove them permanently.
