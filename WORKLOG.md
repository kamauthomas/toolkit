# Worklog — Toolkit WordPress site

Newest first. One entry per meaningful change, written in the same commit as the
change itself. Format and rules: see `../AGENTS.md` §1.

---

## 2026-09-02 — Extend the finalized OKR baseline to 26 May
**Area:** portfolio governance · OKRs · historical evidence
**Environments:** documentation and local Report System register; no deployment
**Commit(s):** this commit

Extended the approved portfolio measurement period from August back to 26 May
2026 and reconciled the baseline against retained repository history. The
chronology now credits the Report System foundation from 27 May, June website
analysis/report controls, July WordPress/Reception/Smart Lecturer work, August
portfolio expansion and the 1 September hosted Campus rehearsal.

No completion is invented for 26 May itself: it is the requested period start,
while the earliest retained implementation evidence is dated 27 May. Updated
the Word handoff and Report System template to use the same period.

**Verified by:** Git history across WordPress, Report System, Reception and Smart
Lecturer/Virtual Campus plus retained June/July progress and deployment records.
**Follow-ups:** named-owner assignment and first measured operating month remain
required before live OKR activation.

## 2026-09-01 — Deploy the temporary Virtual Campus for human testing
**Area:** Virtual Campus · cPanel · LiveKit · staging handoff
**Environments:** `campus-test.toolkitafrica.ac.ke` staging ✅ · production unchanged
**Commit(s):** Campus `ea8c2fe`; deployment scripts in this commit

Created the temporary Campus subdomain with Laravel's `public/` directory as the
document root, provisioned an isolated MySQL database/user and deployed the
private application non-interactively through maintained cPanel scripts. Demo
registration is closed; the generated account password, database values,
application key and reused LiveKit secrets remain in ignored private files and
were not printed or committed.

The initial host migration exposed legacy MySQL timestamp and identifier limits.
Those were fixed and pushed in Campus commits `987a81c` and `c89fa98`, then the
locked clean bootstrap succeeded. A non-destructive `2026.09.01.4` increment
added and verified HSTS without resetting accepted test records. Both scripts
remove their temporary cron entries; the header rollback is retained under
`rollbacks/campus-test-pre-2026.09.01.4`.

**Verified by:** 60 tests/232 assertions; production asset build; real HTTPS
home/login and CSS/JS 200; registration 404; CSP/frame/HSTS headers; lecturer and
learner login/class scoping; and three concurrent LiveKit participants with
camera/microphone controls, screen sharing, captions and signed join/leave
attendance events. Human checklist:
`SmartLecturer_VirtualCampus/virtual-campus/docs/HUMAN-TEST-HANDOFF-2026-09-01.md`.
**Follow-ups:** complete the human device/network checklist; approve automated
captions, recording/retention, mail and pilot cohort before any production move.

## 2026-09-01 — Publish the finalized OKRs as a Word handoff
**Area:** portfolio governance · OKRs · meeting documents
**Environments:** documentation; no deployment
**Commit(s):** this commit

Generated an A4 Word version of the finalized and backdated Toolkit OKRs beside
the Markdown source. The document retains all eight objectives, thirty key
results, evidence checkpoint, targets, ownership decisions and first-quarter
priorities in meeting-ready tables.

**Verified by:** reopened through an isolated headless LibreOffice profile,
exported to an eight-page A4 PDF, checked the first/final page visually and
confirmed the backdated checkpoint, Objective 8, priorities and evidence
sections remain present.
**Follow-ups:** assign named users in the Report System register.

## 2026-09-01 — Finalize and backdate the Toolkit portfolio OKRs
**Area:** portfolio governance · OKRs · evidence
**Environments:** documentation and local Report System register; no deployment
**Commit(s):** this commit

Finalized the eight-objective, thirty-key-result portfolio as the working
August 2026–July 2027 OKR set. Added an evidence-backed checkpoint covering work
completed from 20–31 August across the website, Reception, Virtual Campus, Smart
Lecturer, Report System, security, posters and delivery operations. Outcome
percentages that require a real cohort or reporting month were deliberately not
invented.

Clarified that Draft in the Report System now represents pending named-user
assignment, not unapproved portfolio targets. Activation remains blocked until
each objective and key result has an accountable user and an operational
denominator.

**Verified by:** reconciled the checkpoint with repository worklogs, deployment
records, the 2026 catalogue work and the 172-file/128-poster asset inventory.
**Follow-ups:** management names owners; system owners record the first measured
month/pilot as append-only OKR evidence.

## 2026-08-31 — Reconcile OKR draft with the operational loader
**Area:** portfolio governance · reporting · posters/communications
**Environments:** documentation and local Report System; not deployed
**Commit(s):** this commit

Reconciled the portfolio document with the completed operational loader. The
Report System now loads eight objectives and thirty key results idempotently as
drafts, including the poster/brand objective, and blocks activation until every
objective and result has accountable ownership and approved status.

**Verified by:** Report System commit `38a78c4` and 52 passing automated tests.
**Follow-ups:** management reviews the draft targets and assigns owners before
activation; external Wingu dispatch still requires authenticated discovery.

## 2026-08-31 — Connect the portfolio OKRs to the working register
**Area:** portfolio governance · reports · posters/communications
**Environments:** documentation and local Report System; not deployed
**Commit(s):** this commit

Updated the portfolio OKR draft after the Report System gained a working OKR
register. Approved objectives can now be assigned, measured and supported by an
append-only evidence trail, including references to approved posters and
campaign assets. The source draft remains intentionally unseeded until
management approves owners, baselines and targets.

**Verified by:** Report System commit `b058b43`, 49 passing tests and healthy
local `/okrs` and `/wingu` routes.
**Follow-ups:** management assigns the eight objective owners and approves the
targets/denominators before activation.

## 2026-08-31 — Add cross-project OKR draft and Wingu policy correction
**Area:** portfolio governance · reports · Wingu · posters/communications
**Environments:** documentation only; no deployment
**Commit(s):** this commit

Added a management-ready OKR draft covering every Toolkit workstream, including
the website, Reception, Report System, Smart Lecturer, Virtual Campus, chatbot,
security, release operations and the `imgs` poster library. Reconciled the
Wingu procedure so project identity is selected from Wingu's own values rather
than a hard-coded project code, and updated the briefing with the new outcome
measures.

**Verified by:** cross-checked against current repository worklogs, deployment
handoffs, module trackers, poster inventory and the Report System's 45-test
result.
**Follow-ups:** management approval of OKR owners/denominators; regenerate the
long-form briefing PDF/DOCX after approved changes.

## 2026-08-28 — Reconcile briefing with operational reporting modules
**Area:** cross-project briefing · reports · management communication
**Environments:** documentation only; Report System changes remain local
**Commit(s):** this commit

Updated the whole-Toolkit briefing and non-technical presentation summary after
the Report System gained working intake targets, meeting/action tracking and a
reasoned inactivity-unlock flow. The documents also preserve the boundary that
Wingu Box is a design only until its supported integration method, identity
mapping, project code and attendance source are approved.

**Verified by:** reconciled against Report System commit `07a19c6`, its registered
routes and 38-test passing result.
**Follow-ups:** use the Report System owner tracker for the demo and Wingu
decisions; regenerate the long-form PDF/DOCX briefing after management approval.

## 2026-08-28 — Add non-technical presentation summary
**Area:** cross-project briefing · management communication
**Environments:** documentation only; no deployment
**Commit(s):** this commit

Added a concise, meeting-ready summary of the Toolkit portfolio for non-technical
stakeholders. It covers the website, Reception, staff reporting, Smart Lecturer,
Virtual Campus, security posture, communications assets and the decisions needed
for the next pilot phase.

**Verified by:** cross-checked against the dated whole-Toolkit briefing and
current project handoff/status documents; no credentials or unsupported
production claims included.
**Follow-ups:** use the summary as speaking notes and keep the owner trackers
aligned with decisions made in the meeting.

## 2026-08-28 — Refresh cross-project reports after admissions module work
**Area:** cross-project briefing · reporting status
**Environments:** documentation only; no deployment
**Commit(s):** this commit

Updated the whole-Toolkit briefing and presentation summary to identify the
Report System's newly implemented local admissions capture and verification
slice, while keeping the demo-review and hosting boundary explicit.

**Verified by:** reconciled against Report System commit `36b92e7` and its
29-test passing result.
**Follow-ups:** update the management materials again after demo review or
additional reporting modules are approved.

## 2026-08-28 — Add whole-Toolkit briefing report
**Area:** cross-project briefing · website · Reception · Virtual Campus · Smart
Lecturer · reporting · chatbot · graphics/posters · operations
**Environments:** documentation only; no deployment
**Commit(s):** this commit

Added a dated management briefing covering the current state of every major
project under the Toolkit directory, including the poster/image library,
security containment, deployed website/Reception boundaries, local Virtual
Campus/live-class evidence, Smart Lecturer prototype limits, reporting and
chatbot status, Windows transfer controls, and direct decisions still needed
from Toolkit.

**Verified by:** cross-repository file inventory, current worklogs, deployment
handoffs, module trackers, test/build output and poster asset counts collected
on 28 August 2026.
**Follow-ups:** provide the briefing to management; keep owner trackers and
system-specific worklogs current as decisions are made.

## 2026-08-25 — Preserve Reception release automation
**Area:** reception · deployment · operations
**Environments:** demo ✅ · production ✅
**Commit(s):** this commit

Added the browser-free demo-first deployment script for Reception release
`2026.08.25.1` / Reception commit `86b7293`. It backs up each remote file, separates the
private Laravel root from public assets, maintains per-environment private staff
paths outside Git, runs migrations and cache clearing through a temporary cPanel
job, removes the job and verifies public, QR, legacy-path and staff-header
boundaries without printing secrets.

The first demo migration exposed a hosted-MariaDB `TIMESTAMP` compatibility
issue and stopped before production. The corrected `DATETIME` migration then
completed on demo. Route verification revealed that cPanel preserves an upload's
local basename, so two randomly named environment copies had been created
instead of updating `.env`. Both exact copies were moved to recoverable cPanel
trash, production remained untouched, and the script now stages the payload as
the literal `.env`. Its exit trap also removes the exact temporary cron entry
and local secret staging directory on every failure path.

The corrected release then passed the complete guarded demo verification and
was promoted unchanged to production. Separate ignored rollback snapshots and
private staff-path records were retained for both environments.

**Verified by:** Bash syntax and Git whitespace checks; demo and production
migration logs ended in `RECEPTION_RELEASE_OK`; both passed homepage, QR,
legacy-path 404, private-login header and settings-authentication checks;
misplaced copies were enumerated and contained.
**Follow-ups:** implement and separately test the WhatsApp delivery pipeline;
the deployed administrator vault is configuration storage, not message delivery.

## 2026-08-25 — Prepare evidence-backed reporting backfill
**Area:** reports · Wingu Box · operational continuity
**Environments:** local reporting draft; live Wingu updated and reloaded
**Commit(s):** this commit

Prepared Wingu notes and complete Report System drafts for 18, 19, 20, 21, 24
and 25 August using commit history, worklogs and deployment evidence. Thomas
authorised the bounded start/end times, and the per-task allocations reconcile
exactly to each authorised daily duration.

Wingu was opened in a dedicated browser profile and controlled through a
localhost-only browser port after Thomas logged in himself. The automation did
not read or write credentials. Report System remains a prepared draft and no
Report System entry has been filed.

Thomas subsequently authorised varied Wingu times within 08:00–08:15 for
arrival and 17:10–17:30 for departure. The live grid confirmed 17 August as the
last completed row and 22–23 August as off days; the worksheet now provides
approved times for 18, 19, 20, 21, 24 and 25 August. Visible X11 automation was
abandoned after it interfered with terminal focus; its clipboard content,
scripts and processes were cleared. The isolated workflow persisted and
reload-verified 18, 19, 20, 21 and 24 August as Draft under TTI0224 / TTI 2024.
Wingu returned HTTP 200 but did not persist 25 August, so that row remains the
only pending Wingu item. Off days 22–23 August remained unchanged.

**Verified by:** cross-repository dated commit history, deployment worklogs,
successful Wingu update response, project refresh and row-by-row reload check.
**Follow-ups:** retry only 25 August when Wingu permits it and reload to confirm;
file the prepared Report System drafts through the employee's own account.

## 2026-08-25 — Reconcile cross-project pickup state
**Area:** continuity · WordPress · Reception · Smart Lecturer · Virtual Campus
**Environments:** documentation; live Reception health read-only verified ✅
**Commit(s):** this commit

Reconciled the older WordPress handoff with the completed production applicant
encryption migration, the new Reception production deployment, the independent
Smart Lecturer prototype and the local-only Laravel Virtual Campus. Added one
pickup map that identifies verified work, disabled or missing capabilities, the
exact resumption point and the remaining outcome for each system.

A guarded private Reception cleanup was attempted for the labelled acceptance
record, but cPanel did not execute the temporary minute cron and private SSH is
unavailable. The cron and uploaded private artifacts were removed; no public
maintenance endpoint was introduced. Reception home, staff login and the
WordPress Reception page continued to return HTTP 200.

**Follow-ups:** delete `WEB-260825-JBJXCB` through authenticated staff operations
or a restored private CLI path; resume the Virtual Campus at its remaining
learner-facing UI journeys, then staging and production acceptance gates.

## 2026-08-25 — Provision Reception production and repair cPanel authentication
**Area:** Reception · production infrastructure · WordPress relay · QR workflows
**Environments:** demo verified ✅ · production deployed ✅
**Reception commit:** `f16790c`

Provisioned `reception.toolkitafrica.ac.ke` with isolated private/public roots,
a fresh least-privilege database, fresh application and relay secrets, PHP 8.4,
the `office@toolkitafrica.ac.ke` administrator and the Laravel scheduler. Backed
up production `wp-config.php` before enabling the signed WordPress reception
relay. Demo configuration, secrets and records were not promoted.

Diagnosed cPanel HTTP 401 responses: the credential remained valid, but the host
now rejects HTTP Basic UAPI authentication. Added a reusable private-cookie and
short-lived-session-token helper and documented the reception deployment path.

**Verified by:** 18 tests and 92 assertions; Pint; live HTTP 200 for production
home, staff login, both QR SVGs and the main-site reception form; desktop/mobile
headless checks with no overflow or browser errors; successful signed WordPress
relay producing a website/follow-up reference with no physical check-in.
**Follow-up:** authorized staff must delete labelled acceptance record
`WEB-260825-JBJXCB`; rotate the generated initial administrator password after
first login and confirm the scheduled retention job in the next operations check.

## 2026-08-24 — Record WordPress handoff before Virtual Campus recovery
**Area:** operations · handoff · continuity
**Environments:** documentation only; production remains on `2026.08.24.5`
**Commit(s):** this commit

Recorded the exact public-site checkpoint, completed releases, outstanding
applicant-encryption migration, analytics audit, Windows refresh and deployment
engine follow-ups before moving active work to the independent Virtual Campus.
The handoff names the production release, repository pickup point, safety rules
and new Virtual Campus objective so unfinished WordPress work is not lost.

**Verified by:** clean branch comparison with `origin/wordpress-modernisation`,
recent release history, deployment records and the approved-pricing public
verification completed for release `2026.08.24.5`.
**Follow-ups:** use the checkpoint document when returning to WordPress; begin
Virtual Campus recovery without treating its untracked Claude implementation as
accepted or production-ready.

## 2026-08-24 — Version application encryption and restore legacy decryption
**Area:** admissions · applicant-data encryption · recovery
**Environments:** local ✅ · demo/prod pending keyring configuration
**Commit(s):** this commit

Reworked application payload encryption so dedicated application keys are
independent of rotating WordPress authentication salts. New records use a
version-2 AES-256-GCM envelope with an explicit key ID when the private keyring
is configured. Existing version-1 records can be opened with the current auth
key or explicitly configured legacy K2/K3 keys. Added a WP-CLI batch migration
with dry-run mode, round-trip verification, compare-and-swap updates, and
counts-only output. CSV exports now record selected, written and encrypted-row
skips instead of silently hiding omissions.

**Verified by:** PHP lint, `git diff --check`, dedicated-key v2 round trip,
legacy v1 round trip using the pre-rotation production configuration, and GCM
tamper detection. No applicant fields, ciphertext or key material were emitted.
**Follow-ups:** configure the private current/legacy keyring, run the count-only
production audit and dry run, then migrate and verify authorised applicant
details before removing legacy key material.

## 2026-08-21 — Simplify Footprint to match the supplied poster
**Area:** public website · institutional footprint · corrective release
**Environments:** local ✅ · demo/production pending
**Commit(s):** this commit

Replaced the rejected editorial redesign with a direct responsive interpretation
of the supplied poster: seven chronological desktop columns, red and green year
pills, dashed programme paths, partner/work descriptions and connected footprint
markers. Mobile stacks the same chronology into one legible vertical path, and
restrained line, column, dot and step motion never hides content.

Added a separate browser-free corrective deployment script for release
`2026.08.21.2`, based exactly on the deployed `2026.08.21.1` payload.

**Verified by:** PHP lint, CSS marker review, `git diff --check`, and isolated
headless desktop (1440 px) and mobile (390 px) renders against the supplied
poster.
**Follow-ups:** deploy and visually verify demo, then promote the byte-identical
payload to production and update this entry.

## 2026-08-21 — Rebuild the institutional Footprint page
**Area:** public website · institutional impact · deployment operations
**Environments:** demo ✅ · production ✅ · superseded by corrective release
**Commit(s):** this commit

Replaced the oversized alternating footprint trail with an editorial page that
uses genuine Toolkit photography, three evidence-based areas of work, and four
readable chronological chapters. All 28 supplied programme records and their
partner attributions remain intact, while the displayed counts are explicitly
labelled as source-record figures rather than participant or outcome totals.

Added a release-specific, browser-free cPanel deployment script that patches
the three fetched live files with zero fuzz, activates `functions.php` last,
retains rollback copies, and verifies the public route and image assets.

**Verified by:** PHP lint for the template and release file; Bash syntax and
ShellCheck for the deployment script; `git diff --check`; isolated headless
desktop (1440 px) and mobile (390 px) renders of the complete responsive layout.
**Follow-ups:** the result was rejected as over-engineered after production
promotion; corrective release `2026.08.21.2` restores the supplied poster's
simple visual structure.

## 2026-08-21 — Activate the stable Windows Toolkit workspace
**Area:** workstation organisation · reporting continuity
**Environments:** Windows NVMe Desktop ✅; not a website deployment
**Commit(s):** this commit

Renamed the dated Windows folder in place to `Toolkit_Workspace` and ran the
first ongoing synchronization. The stable seven-section layout now holds the
current project commits, reports and briefing notes, media/design work, 27 XCF
sources, safe admissions tools, and reference material without creating another
dated copy.

**Verified by:** 1,936 files and 457 directories totalling 1.3 GB; all 1,935
manifest checksums passed; all 27 XCF files matched Linux byte-for-byte; the
briefing note is present; prohibited secret/database and applicant-output path
scans returned no matches; the former dated Desktop folder no longer exists.
**Follow-ups:** scheduling can be added later once the Windows volume has a
reliable non-interactive read/write mount policy.

## 2026-08-21 — Convert the Windows copy into an ongoing workspace mirror
**Area:** workstation organisation · reporting continuity
**Environments:** local workflow; Windows synchronization pending remount
**Commit(s):** this commit

Changed the dated one-time export into a stable `Toolkit_Workspace` mirror that
can be refreshed as work continues. Controlled subfolders now remove stale
mirrored files, Git projects are rebuilt from exact commits, sync history and
checksums are regenerated, and prohibited credential/database/applicant paths
stop the run.

Prepared briefing notes covering the website releases, security incident
closure, reporting practice, current operational risks, and decisions required
from management.

**Verified by:** shell syntax and whitespace checks plus three successive syncs
to a temporary destination; all 1,933 checksum entries passed, a deliberately
stale file inside a managed project was removed, and an unmanaged root note was
preserved. Every source, destination, exclusion, and deletion boundary was
reviewed.
**Follow-ups:** run the first in-place synchronization when the Windows volume
can be mounted without interrupting the active desktop session.

## 2026-08-21 — Reconcile the Claude/Codex handoff and incident reports
**Area:** handoff · security reporting · deployment operations
**Environments:** documentation only; demo and production read-only checks ✅
**Commit(s):** this commit

Reconciled Claude's final checkpoint, the subsequent Codex deployment and
incident work, Git history, retained browser-free scripts, and the master
handoff. Corrected stale report sections that still described deployed pages
as demo-only and completed containment actions as open, without changing the
historical incident evidence.

**Verified by:** current clean/synchronised branch state, release tags, worklog,
Claude's final session checkpoint, Codex scan/deployment evidence, the final
cPGuard scan record, and a local search confirming only Eduma 5.3.0 is available;
six public routes on both demo and production returned 200 without fatal-error
markers, while all five known exposed/payload indicator URLs returned 404.
**Follow-ups:** obtain a licensed Eduma 5.7.7+ package for demo-first testing;
staff must handle personal password/device checks and approved secret rotation.

## 2026-08-21 — Complete and verify the Windows work archive
**Area:** workstation organisation · continuity · security hygiene
**Environments:** local Linux → Windows NVMe ✅; not deployed
**Commit(s):** this commit

Created the organised Toolkit archive on the Windows Desktop with clean project
snapshots, reports, posters/media, 27 requested XCF sources, safe admissions
tools, and reference material. Added a durable verification log and daily-report
draft, and clarified that tracked placeholder `.env.example` templates are safe
to retain while live environment files remain excluded.

**Verified by:** 1,929-file initial inventory (1.3 GB); 1,927 SHA-256 entries
passed with zero failures; all 27 XCF files matched byte-for-byte; prohibited
secret/database and applicant-output path checks were clear; token signatures
were clear; TruffleHog returned zero findings on the six content folders after
excluding its checksum-manifest false positive.
**Follow-ups:** open the archive from Windows and retain the manifest with the
files; resume the website/repository review and deployment-script work.

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

## 2026-08-24 — Migrate production applicant encryption with temporary PHP CLI
**Area:** admissions security
**Environments:** production ✅
**Commit(s):** pending documentation commit

Used the documented CLI-only fallback because WP-CLI was unavailable. The
runner stayed outside `public_html`, defaulted to dry-run, required two explicit
guards for live execution, and emitted aggregate counts only. A completed cPanel
backup was confirmed before execution.

**Verified by:** pre-run 16/16 eligible with zero failures/conflicts; execute
migrated 16/16 with exit code 0; post-run reported 16/16 already on
`application-2026-08-24`. All temporary cron entries, runner/probe files and
aggregate output files were removed and verified absent.
**Follow-ups:** retain the legacy key until the normal key-retention window has
passed; verify an applicant detail screen during the next authenticated admin
review.
