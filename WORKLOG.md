## 2026-10-26 — Scope reversible cleanup of older vendor documentation
**Area:** runtime-only server file policy
**Environments:** reviewed local cleanup plan; execution pending demo-first verification
**Commit(s):** this change (see Git history)

Narrowed broad server-search matches to 278 standalone vendor documents/source maps and one old harmless demo upload canary. Excluded runtime code, schema/template inputs, twelve actual plugin Markdown resources, required licenses, public downloads and private business storage. Added a dry-run-first exact-path cleanup command using existing Fileman APIs and reversible private trash; it never empties recovery storage or uploads an executable helper.

**Verified/security review:** validated both environment plans (52 demo files; 227 production/Campus files) and refused nine isolated unsafe-path/duplicate cases. Reviewed exact diff and mutation boundary: per-environment roots, regular files only, no traversal/commas/encoding ambiguity, no business storage/downloads/licenses, fresh type/size/mtime comparison, recorded per-file API receipts, demo gate and post-cleanup inventory. Requests are paced and stop on timeout/refusal without retry/IP rotation. Existing search inventory is not proof of public exposure. No unresolved security blocker for this scoped reversible cleanup; actual receipts remain pending. Private recovery holdings intentionally remain off application roots for rollback.

## 2026-10-26 — Refresh Toolkit Control and add read-only Reception application export
**Area:** website administration · Reception integration · runtime deployment policy
**Environments:** locally verified; demo/production deployment pending
**Commit(s):** this change (see Git history)

Added shared branded navigation, responsive metrics, tables and controls across Toolkit Control. Added a minimal signed GET-only website application export and administrator-controlled sharing switch for Reception's separate read-only register. Hardened deployment path/package boundaries and ignore rules; preserved existing unrelated edits and approved downloads/license notices.

**Verified by:** 202 deployment-boundary checks, 28 synthetic export/auth/privacy cases, 35 graduands/permission checks and 18 desktop/mobile synthetic browser checks. Reception's 95-test suite / 1,364 assertions passed; final focused sync rerun 13 tests / 113 assertions passed. Exact runtime package preparation and PHP syntax checks pass: 19 files per environment, eight website and eleven Reception. Live baselines captured through established cPanel Fileman APIs; no production test applications, Mzizi replays or live attack probes.

**Security/flaw review:** reviewed actual source diff and exact baseline-derived payload for permissions/CSRF, escaping, bounded prepared SQL, strict signing/replay protection, minimal encrypted contact data, fixed outbound hosts/no redirects, duplicate-safe updates, concurrency locks, persisted pagination and bounded failure cooldown. Uploads reject document/archive/map/backup/secret payloads and unsafe/unknown destinations; no source docs/tests/manifests deployed. Automatic review rejected an uploaded inventory helper; it was not used and existing read-only APIs supplied the inventory. A proposed automatic mirror deletion was rejected before implementation; missing entries are retained and labelled instead. Metadata matches include runtime files and are not proven leaks or a bulk cleanup list. No known release security blocker; authenticated staff acceptance and actual demo-first verification remain pending.

**Follow-ups:** see docs/TOOLKIT-ADMIN-RECEPTION-SYNC-2026-10-26.md, docs/RUNTIME-FILE-REVIEW-2026-10-26.md and the ready-to-paste daily draft. Private source/inventory/test receipts remain under reports/ and outside deployment. Stopped on hosting timeout; the user checked access and authorised one retry, which succeeded. No Git push.

## 2026-10-24 — Shorten October report attention items
**Area:** management report editorial revision
**Environments:** local Word/PDF only; no deployment or external filing
**Commit(s):** this report handoff revision (see Git history)

Removed the original second and last points under Items requiring attention as requested. Retained Admissions/Mzizi and hosting items and regenerated both October report copies. Verified one PDF page, the two remaining points, full Word-to-PDF text retention and the exact supplied logo/name; visually checked the final PDF. Private validation/manifest updated. No application/runtime change or server payload; existing working-tree edits preserved.


## 2026-10-24 — Save Wingu drafts and prepare the concise October report
**Area:** employee-authorised timesheet save · management reporting
**Environments:** Wingu employee session; local Word/PDF report preparation; no website deployment or Report System filing
**Commit(s):** this handoff and report-format change (see Git history)

Saved 17 documented dates in Wingu at the employee's instruction: five September dates and twelve October dates. All persisted as Draft after independent server reloads; the separate approval action was not invoked. Kept 24/28 September and 2 October blank as explicitly requested, preserving the 15 existing approved dates, Annual Leave and off/holiday days. Prepared a concise one-page October general report with Thomas Kamau's name and the exact supplied Toolkit logo from imgs; no reporting cutoff is displayed. Includes posters, graduation booklet/event materials, system improvements, reporting, support and remaining decisions.

**Verified by:** exact saved note/time/project checks, plausible hours and no duplicate dates across both reloaded months; 37 unrelated month rows unchanged. Four estimated endpoints in the saved rows are labelled. Initial October save exposed native Unicode/newline handling; corrected the same 12 known Draft records to readable ASCII point-form notes, preserving their existing identifiers/times/project, then reloaded and verified. Intercepted previews caught missing metadata and local comparison issues without sending invalid update requests. Final Wingu receipt is ignored/private under reports/report-preparation-2026-10-23/push-2026-10-24/.

**Report checks:** one A4 PDF page; 284 words in the Word body/tables; exact supplied logo embedded in Word; Thomas Kamau author metadata; full Word-to-PDF text retention; no visible reporting cutoff/date range; all text within page bounds; final PDF visually reviewed. Report renderer retains prior defaults while allowing a supplied local logo and author. Python compilation and staged diff checks pass. Original logo/artwork/logs and earlier preparation snapshots preserved.

**Security/flaw review:** employee identity, displayed project, row status/ownership, dates and exact outgoing payload reviewed before saves; baseline/receipt journals and reload comparisons prevented duplicate or uncertain replay. Owned-Draft note correction carried unchanged displayed project/time values into the native save handler, without changing approval/ownership controls. No credentials read or entered; isolated browser/authentication state removed after verification. Local-only renderer change has no runtime route, SQL, upload, relay or server payload; reports/private evidence remain excluded from deployment. No Git push, live security probe, production test application or external message.

**Follow-ups:** three work descriptions remain pending; normal approver to review saved Drafts. Institutional/hosting/Registry/Admissions decisions remain listed in the October report. See docs/reporting/OCTOBER-REPORT-AND-WINGU-HANDOFF-2026-10-24.md. Unrelated working-tree edits preserved.


## 2026-10-24 — Reconcile Wingu and prepare evidence-backed work reports
**Area:** reporting · attendance preparation · communications and graduation evidence
**Environments:** local report generation; Wingu read-only inspection in an employee-authenticated temporary Brave session; no deployment or external filing
**Commit(s):** this reporting change (see Git history)

Confirmed Wingu approval through 21 September and blank subsequent working rows, establishing 22 September–23 October as the new reporting window. Preserved Wingu Annual Leave on 8, 9 and 12 October. Prepared Thomas's management and daily-report drafts, Wingu review workbook/CSV, source inventories and follow-up actions. Traced documented system releases, poster collection/design work, event cards and the final 67-page graduation booklet while distinguishing local preparation, live verification and pending approval.

**Verified by:** 65 attendance observations preserved without person/device identifiers; 32 calendar rows; 34 recorded and six employee-authorised estimated endpoints; 12 held leave/off/holiday dates; original late checkout and duplicate-punch flags; three unsupported work descriptions explicitly pending. Conflicting-boundary negative check, stable estimate rerun, spreadsheet formula exclusion, source hashes, three booklet-copy hashes/67-page counts, complete Word-to-PDF text retention and visual PDF review. Local Python compilation and diff checks pass.

**Security/flaw review:** local report-only scripts and actual changes reviewed; no production runtime payload, routes, database, upload or relay change. Wingu used displayed project selection/Refresh Data for inspection only; no Update Time Sheet or approval submission. Credentials, cookies, account URLs and original saved notes excluded from report/source-control outputs. Reports and minimised private evidence remain ignored outside deployment payloads; no live attack probes or production test application.

**Follow-ups:** Thomas to confirm 24/28 September and 2 October work descriptions and six estimated endpoints, including the missing 23 October checkout. Saturday 17 October attendance requires explicit approval. Admissions, Registry, Communications and hosting follow-ups are listed separately; prior deployments/security conclusions were traced to retained evidence, not re-tested live. See docs/reporting/REPORT-PACK-2026-10-24.md. Unrelated working-tree edits preserved; no push or deployment.


## 2026-10-21 — Apply named graduands removals and retain Cindy once
**Area:** public graduands corrections
**Environments:** demo ✅ · production ✅ — data-only correction; theme release remains 2026.10.21.1
**Commit(s):** this correction (see Git history)

Matched the operator's nine requested removals to exact existing name/course records, accounting for unique spelling and shortened-name variants. Cindy Malika Galana was already published under Welding and is retained once. The accurate result is 285 (294 minus nine), explained to the operator; no duplicate or invented entry is added to force the earlier suggested total.

Prepared a data-only, demo-first amendment through the existing strict CSV parser. The private CLI importer pins the reviewed baseline revision, saves recovery options, publishes names/courses only, verifies all saved rows, purges page caches and removes itself and its temporary job. Security/flaw review: exact source matching, duplicate rejection, baseline drift protection, fixed CLI targets, private rollback/logging, two-field publication and no public upload/privileged route. No theme change, original-workbook edit, test application or security probe is needed.

**Verified by:** all nine existing targets match once; Cindy exists once in the 294-name source and on the live page; the proposed 285-row list passes the deployed PHP CSV validator. Demo and production each returned HTTP 200 with all 285 public name/course pairs exactly matching the corrected source; all nine removed pairs are absent and Cindy appears once. The normal canonical QR destination was also verified on each host, confirming cache invalidation. Temporary importers and scheduler jobs were removed; private recovery snapshots retained. No theme files changed.
**Follow-ups:** public totals follow verified people rather than an arithmetic target; intake/cohort details remain excluded from the public two-column list. Existing unrelated work preserved.

## 2026-10-21 — Publish supplied graduation programme and approved graduands
**Area:** graduation · Notice Board · public roster
**Environments:** demo ✅ · production ✅ — release 2026.10.21.1
**Commit(s):** this change (see Git history)

Reviewed the supplied programme, final invitation, workbook and draft booklets. Published 294 unique approved name/course rows on demo and production, including Hadiid and Class of 2025 as Welding following the operator's clarification. Only names and courses are public; school/stream/row-number fields and original documents remain local. Added the nine-activity programme and theme to Graduation, an Events notice linking to the timetable/list, and 8 AM arrival guidance on the existing controlled homepage popup. The formal ceremony and calendar remain 9 AM–1 PM EAT on 22 October. Corrected the programme's noon 12:30 AM typo to PM from the surrounding sequence; unnamed speakers remain unnamed.

The stable QR destination and admin publication/visibility controls remain. The importer uses the existing strict parser, saves private rollback options, verifies the saved list and deletes its temporary executable; its cron job is removed. Only six runtime theme assets were deployed. Browser testing identified existing styles overriding hidden Notice Board cards; a scoped rule now restores category/search filtering and empty-state visibility. Security/flaw review covered output escaping, two-field privacy, source integrity, private CLI-only publication, authorized targets, scheduler cleanup, baseline drift and runtime payloads. No new public privileged/upload route, credentials in UI, live test application or attack probe.

**Verified by:** all 294 actual rows pass the production CSV parser; 35 existing graduands permission/approval/visibility tests and 26 popup repeat scenarios pass; PHP syntax and diff checks pass. Demo runtime bytes, public Graduation/Notice Board/homepage HTTP 200 and release/asset markers, 294 rendered names, five course filters, nine programme rows and calendar bytes verified. Production passed the same runtime byte, HTTP/release/assets, roster and programme checks. All 294 public name/course pairs match the approved source exactly on each site. Both environments passed 17 desktop/mobile browser checks, including name search, the 178-name Welding filter, full-list print despite active filters, reset/no-match, Notice Board Events filtering, stable QR links and mobile overflow checks. Screenshots reviewed; calendar CRLF/75-octet folding checked. Temporary importer executables and jobs were removed; private recovery snapshots retained.
**Follow-ups:** retain administrative name/correction controls; institution can update approved names through the same preview/publish workflow. Source checksums, private rollback and publication evidence are under reports/graduation-2026-10-21/. Earlier unrelated working-tree edits preserved.

## 2026-10-17 — Investigate Mzizi 409, retain course choices and add safe relay recovery
**Area:** admissions relay · course availability · production security review
**Environments:** demo ✅ · production ✅ — release 2026.10.17.1
**Commit(s):** this change (see Git history)

Read-only production evidence found 27 saved applications: 11 delivered, 14 unconfirmed (three with upstream submission HTTP 409), one failed and one stuck relaying. The supplied error and saved statuses confirm the upstream endpoint, but its generic response does not establish the internal cause or whether an affected application exists in Mzizi. No historical uncertain application was resent and no production test application was submitted.

Corrected undefined study/sponsorship variables and lowercased gender in relay payloads, checking the current public Mzizi form script using GET only. Added atomic record claims, local duplicate-submission locking, a durable pre-hook watchdog, interruption recovery, complete attempt/failure histories, five-attempt bounded backoff for safe pre-submission failures, and a five-minute private CLI scheduler. Uncertain POST/409 outcomes require an administrator's Mzizi check; confirmation and re-send cannot race an active worker. New logs exclude upstream bodies and applicant contacts. Success requires affirmative confirmation and a durable saved outcome.

Public campuses/courses/intakes now retain their last successfully loaded choices independently of transient expiry. Background checks refresh at most two known catalogue entries per run, normally every six hours, with failure cooldown and no deletion of good data during outages. Unknown IDs cannot grow the cache. Unseen choices still need an initial successful lookup, and relay always revalidates the selected course/intake. Added scoped AGENTS and deployment README rules requiring recorded flaw/security review before production commits and push/deployment. Only runtime PHP reaches the server; documentation, tests and reports stay local/in Git.

**Security review:** checked admin permissions/nonces, SQL/locks, output escaping, fixed upstream destinations, private logs, bounded retry/refresh behaviour, worker crash/concurrency boundaries and runtime payload allowlists. No live attack probes or IP rotation. No new public privileged route. Earlier demo-only private-file behaviour is not promoted implicitly.
**Verified by:** 73 isolated real-MariaDB relay/catalogue checks with mocked HTTP, 524 existing contact/validation checks, PHP syntax and diff checks; exact demo fetched/patched payload passes the same 73 checks; demo runtime bytes, HTTP 200/release/assets, additive schema, worker wiring and five-minute job verified. Exact production payload also passed 73 synthetic checks; production runtime bytes, Apply HTTP 200/release/assets, schema and worker hook passed. Three normal catalogue lookups returned 200 on each host. Dedicated jobs completed at 10:07:04 UTC demo and 10:10:08 UTC production, processing zero submissions. Final read-only evidence confirms five successful catalogue snapshots per site and production 11 delivered / 15 unconfirmed / one failed: the old August stuck worker was recovered to review without resend. No live application submission or attack probe was used.
**Follow-ups:** Admissions/Mzizi-side reconciliation and vendor processing logs for historical failures; a supported vendor idempotency/lookup contract before automatic uncertain-delivery reconciliation; SMS/email OTP provider still absent. Existing unrelated working-tree edits preserved.

## 2026-10-16 — Checkpoint security containment and pause remote work after hosting lockout
**Area:** website security · deployment hygiene · multi-system audit checkpoint
**Environments:** runtime hardening 2026.10.16.2 demo ✅ · production ✅; private-artifact boundary 2026.10.16.3 demo ✅ · production pending; server work paused by the user
**Commit(s):** this checkpoint commit (see Git history)

Inventoried six deployed hosts and 126,490 account filesystem entries. Confirmed a public Eduma installer ZIP and an obsolete publicly readable Reporting source backup. Preserved/moved the source copy privately, contained backup filename variants and moved 834 documentation/development/installer/log files out of the active application trees (432 demo, 402 production), preserving approved public downloads and runtime data. These files remain in temporary private holding; off-host preservation and final removal have not finished.

Deployed a must-use upload/traversal guard, disabled browser source editing and the legacy GET settings migration, added upload PHP denials, moved PHP logging configuration toward private files and verified .2 demo then production. Added private calling-letter filename/site/symlink confinement and disabled object deserialization in the image cache; these extra changes are verified on demo .3 only. The local release now reflects the demo code, not a completed .3 production release. The retained parent Eduma version remains 5.3.0 by user instruction after no licensed update package was available; compensating controls do not replace a vendor patch.

Inspected metadata for 26 archives without extraction or execution: 20 complete listings, five bounded partial listings and one unreadable ZIP. Private account backups contain database dumps; legacy copies also contain source, configuration and documentation. Added runtime-only packaging and a shared Python upload policy; the historical Campus bootstrap now uses the packager but must not be rerun on existing data. Other historical shell uploaders have not all been retrofitted.

**Verified by:** 143 upload-policy checks, 30 WordPress request/upload checks, eight private-artifact checks; Reporting and Chat-bot results are in their own worklogs. Runtime archives built locally for WordPress, Reporting, Reception and Campus, plus an adversarial packaging fixture. Live review completed 26 observations before a timeout, with no saved response bodies or live business submissions; only one invalid upload was attempted on demo. Attack-shaped live tests were too aggressive for this host and must not be replayed. Ordinary homepage and cPanel subsequently timed out. Trying a saved alternate Wi-Fi route also timed out and the original HR connection was restored.
**Follow-ups:** all remote work is paused. The tracked handoff is `docs/checkpoints/CHECKPOINT-2026-10-16-SECURITY-AND-LOCATION-QR.md`; raw evidence and Thomas's daily draft remain local under `reports/platform-security-2026-10-16/`. Restore access before any server action, start with one ordinary availability check, preserve pending private artifacts off-host and remove temporary helpers/canary. Complete remaining checks through isolated deployed-code tests, not repeated production probes. The requested location QR (central location pin, tracked link openings, Toolkit Control records) is recorded as pending; no artwork, route or tracking has been deployed. No daily report was submitted automatically.

## 2026-10-16 — Investigate image disclosure, contain public deployment backups and update About Us
**Area:** website security investigation · website content
**Environments:** demo ✅ · production ✅; initial collection read-only
**Commit(s):** this commit (see Git history)

Preserved filesystem metadata, hashes, account/plugin inventories and access records without bootstrapping WordPress. The supplied laptop image and all 18 staff portrait files match tracked local copies, with July modification times. Reviewed 43,344 overlapping main-site request observations covering 30 September–16 October; no direct writes to image paths were observed, but POST bodies and hosting-account activity are unavailable. The claimed ability to modify images remains unverified.

Found historical deployment backups inside public uploads. Demo-first containment preserved all 16 files per environment with before/after hashes and moved them outside the webroots, including 15 production PHP files. This is a confirmed exposure addressed separately from the unproven claim. Replaced only the About Us laptop-photo placement with the Toolkit campus exterior, preserving the original course image. Child-theme release 2026.10.16.1 was verified demo then production; the 14 displayed staff portraits remain.

**Verified by:** PHP syntax checks and Python compilation, deployed-file readback, ordinary About Us/home HTTP 200 + release header, campus-image checksum, sampled removed static-backup URL HTTP 404 per environment, unchanged original-image checksum, private move manifests and zero remaining incident cron jobs. Private evidence and Thomas’s management note/daily draft: `reports/asset-incident-2026-10-16/`. No business/test registration records or live write payloads were submitted.
**Follow-ups:** official licensed Eduma parent-theme update (live parent still 5.3.0 within published advisory ranges), reporter reproduction details, broader review beyond this bounded investigation. The child-theme release does not resolve parent-theme advisories; do not claim the whole site is secure. No staff report was automatically submitted.

## 2026-10-15 — Integrate graduands QR in Toolkit Control and review live website security
**Area:** custom website administration · graduation QR · controlled security review
**Environments:** demo ✅ · production ✅; read-only security checks on production
**Commit(s):** this commit (see Git history)

Added the graduands QR beside existing Toolkit Control QR cards, PNG/SVG downloads, publication status, list management, the Graduands 2026 submenu and a protected public-display switch. Hiding preserves approved names and the permanent QR destination; real names remain unpublished pending an approved source. Demo then production release 2026.10.15.2 was checked against five payload files per environment.

Completed the explicitly requested low-traffic live security review: 18 GETs at least three seconds apart, no retries/logins/form submissions, stop-on-challenge behavior and no retained response bodies/cookie values. Anonymous administration/private REST boundaries denied access, sensitive-file probes disclosed no markers and the harmless search tag was escaped. Final availability remained 200. Confirmed one hardening gap: no CSP response header on the checked public pages; recommend demo report-only testing before enforcement. No critical/high issue was demonstrated within this limited scope.

**Verified by:** 35 isolated graduands/QR permission/publication checks, demo/production source and QR asset hashes, ordinary URLs showing .2, 18 production HTTP observations and five deployed-source reviews. Admissions has only a harmless admin-header UTF-8 meta-tag drift, preserved. Protected signed-in acceptance and intrusive/high-volume/plugin-exploit testing were not performed. Private handovers: `reports/graduands-2026-10-15/admin-control/` and `reports/security-review-2026-10-15/`; daily addendum provided. The temporary browser and localhost fixture server were closed.
**Follow-ups:** demo CSP plan, signed-in administrator acceptance, approved names/courses, new attendance logs and live Wingu row reconciliation. No daily report was automatically filed.

## 2026-10-15 — Prepare Wingu backfill and publish graduands QR and Reception improvements
**Area:** reporting preparation · graduation communications · Reception deployment assurance
**Environments:** website and Reception demo ✅ · production ✅; Wingu draft only, not submitted
**Commit(s):** this commit (see Git history)

Parsed all three retained attendance exports and corrected the stale August handoff boundary: Thomas_recent.csv ends on 22 September at 08:19:17, check-in only. Prepared 24 calendar rows from that date through 15 October with no invented attendance, flagged off-day/evidence gaps and updated the Wingu procedure. Live Wingu rows still need reconciliation when new logs arrive.

Published the graduands section at the existing graduation page's permanent #graduands anchor, with a searchable/course-filtered HTML list and complete-list printing. Added administrator-only CSV preview, explicit publication approval, stale-preview protection and confirmed withdrawal. No names are published until an approved source arrives. Generated PNG/SVG QR artwork and an import template privately; ZBar independently decoded the permanent target. Demo-first deployment also verified Reception's compact shared pagination, responsive real-data overview and private returning-contact visit history; the Reception code/worklog live in its own repository.

**Verified by:** 25 roster parser/publication checks; 82 isolated Reception tests / 1,251 assertions; 13 deployed-memory checks per environment; 17 runtime file identities per environment; 56 desktop/390px synthetic-browser checks per environment, public QR destination and seven production public/client-only preview checks. Versioned warming resolved the first old cached production graduation response; subsequent ordinary routes returned website release 2026.10.15.1. No real visitor/applicant registrations, production roster publication or Wingu submission occurred. Private evidence: `reports/reception-graduands-2026-10-15/`, `reports/graduands-2026-10-15/`, `reports/wingu-preparation-2026-10-15/`.
**Follow-ups:** new attendance logs/live Wingu reconciliation, approved public names/courses and staff acceptance. Location checkout remains disabled pending physical boundary testing; phone matches are not verified identities. Thomas should enter actual task times and file the ready-to-paste daily draft himself.

## 2026-10-13 — Restore normal scrollback for Codex in Kitty
**Area:** desktop support · terminal usability
**Environments:** local workstation configuration; no website deployment needed
**Commit(s):** this commit (see Git history)

Confirmed the affected Codex pane was using Kitty's alternate screen while normal wheel and scrollback settings were enabled. Backed up the user's Codex configuration and set `tui.alternate_screen = "never"`, preserving every other configuration value. Recorded the diagnosis and ready-to-paste daily draft under `docs/desktop-support/`.

**Verified by:** parsed before/after comparison, successful installed-Codex configuration load and isolated strict-config TUI startup with no alternate-screen entry or startup error. Earlier socket/validation limitations are recorded in the diagnosis note; physical wheel use after restart is not claimed verified.
**Follow-ups:** restart/resume the current Codex session, then confirm mouse scrolling; staff enter actual time and submit the daily draft themselves. The active session remains running and still requires the restart.

## 2026-10-13 — Prepare Q4 ICT and Digital Support OKRs
**Area:** quarterly planning · reporting · continuous communications support
**Environments:** local documents only; not deployed because this is a planning handover
**Commit(s):** this commit (see Git history)

Prepared six Q4 2026 objectives and 25 key results using the supplied Excel template, recent worklogs, system audits and owner trackers. The plan covers existing systems, pending requirements and improvement areas, with poster creation described as continuous assigned work. Provided matching Excel, Word and PDF copies, supporting baseline notes and a ready-to-paste daily report under `docs/q4-2026/`.

**Verified by:** exact comparison of all 25 Excel/Word rows; complete text checks on both rendered PDF exports; visual review of all three Word PDF pages and the spreadsheet first page; original template logo retained byte-for-byte. Conversion and text-extraction retries are recorded honestly in `docs/q4-2026/VALIDATION.md`.
**Follow-ups:** management confirms the individual owner, adopts targets and resolves listed dependencies; staff enter actual time and submit the daily draft themselves. No operational system changes or deployment were made.

## 2026-10-07 — Prevent recurring LibreOffice startup locks after hostname rotation
**Area:** desktop support · local maintenance tooling
**Environments:** installed on the local user desktop; no website deployment needed
**Commit(s):** this commit (see Git history)

Confirmed the boot-recovery script generates a new hostname on every reboot. Added and installed a per-user login check plus guarded LibreOffice desktop launches that archive only a confirmed previous-boot local profile lock before opening the application. Preserved current/active locks, settings, recovery files, document locks, hostname rotation and Writer's existing rendering workaround; installation backups and a reversible installer are retained.

**Verified by:** 15 regression checks; seven installed desktop entries and 18 launch commands validated/resolved through Gio; live Writer desktop launch, byte-identical active profile lock and concurrent-launch protection; real isolated LibreOffice PDF conversion after simulated prior-boot/hostname cleanup with exact lock backup and recovery preservation. An initial sandbox-limited conversion was retried successfully with a disposable profile on the host. No actual reboot was performed. Private evidence and daily-report addendum: `reports/libreoffice-repair-2026-10-07/recurrence/`.
**Follow-ups:** check normal opening after the next planned reboot; staff enter actual task time and submit the draft themselves. Explicit system-binary launches before desktop login bypass the user guard; normal login and desktop launch paths are covered.

## 2026-10-07 — Revise the management report and repair LibreOffice startup
**Area:** management reporting · desktop support
**Environments:** local desktop and private documents; no website deployment needed
**Commit(s):** this commit (see Git history)

Removed only the first two paragraphs under Items requiring attention from Thomas's management update and regenerated its Word/PDF copies. The two remaining graduation paragraphs and author attribution are preserved.

Confirmed LibreOffice was waiting at a warning about a profile lock belonging to the computer's previous hostname. Backed up the complete settings/recovery profile, closed the startup warning normally, checked that LibreOffice had exited and removed only the byte-matched stale profile lock. Reopened the revised report successfully without resetting settings or disabling locking.

**Verified by:** Markdown/Word paragraph equality, PDF text and page review, Thomas author metadata; LibreOffice's loaded report window/document, absence of startup alerts and replacement lock using the current hostname. Private backups, evidence and a daily-report addendum are under `reports/libreoffice-repair-2026-10-07/`; document checks are under `reports/management-update-2026-10-07/`.
**Follow-ups:** staff enter actual time and file the provided daily draft themselves. The backup is retained privately; no report was submitted automatically.

## 2026-10-07 — Prepare Thomas's management update and publish controlled graduation announcement
**Area:** management reporting · website announcement · graduation
**Environments:** demo ✅ · production ✅; management documents retained privately
**Commit(s):** this commit (see Git history)

Prepared a management report credited to Thomas, covering recent system improvements and the unique poster collection in plain language, with PDF/Word copies and a daily handover. Published the supplied Class of 2026 announcement using Toolkit's unchanged official logo and graduation archive photograph, the supplied 22 October event details, a calendar download and a matching graduation-page date.

Normal reminders have a 24-hour limit. The final week shortens this to six hours and the final two days to two hours; active visits remain limited to one announcement, renewed across page navigation and ending after 30 idle minutes. Registration, contact and Speak Up flows are excluded. Native dismissal, keyboard focus containment, blocked-storage fallback and expiry at 1pm Nairobi on ceremony day are handled; display timestamps contain no personal identifiers. Demo testing corrected inherited Eduma centering/button styles and keyboard focus before production promotion.

**Verified by:** release 2026.10.07.3; seven byte-matched deployed files per environment, unchanged logo/photo hashes and five public routes plus ten ordinary bare-URL checks; 20 public browser checks each on demo and production, including 320/390px containment, navigation suppression, calendar and synthetic future-clock reminder/expiry boundaries; 26 repeat-rule scenarios and eight route-gate checks. No application or contact form was submitted. Management report attribution, text and rendered pages were reviewed. Private evidence is under `reports/graduation-popup-2026-10-07/` and `reports/management-update-2026-10-07/`; recovery files remain outside the webroot.
**Follow-ups:** staff enter actual task time and submit the daily draft under their own account. Earlier audit follow-ups (approved email/SMS providers and signed-in staff acceptance) remain stated in Thomas's report. No report was filed automatically.

## 2026-10-06 — Bundle the unique Toolkit posters
**Area:** communications · poster collection · local inventory tooling
**Environments:** local archive only; no deployment needed
**Commit(s):** this commit (see Git history)

Reviewed poster artwork in `~/Documents` and the parent project's `imgs/`, including editable sources, and created one private ZIP with 108 distinct poster images grouped by theme. Removed 59 repeat/equivalent source representations and excluded 64 non-poster assets or empty files. Meaningful artwork/content variants remain distinct; nine designs found only in XCF were exported at native size, and two screenshot/printed-poster references are clearly labelled.

Added a bundler that consumes an explicit visual-review selection, verifies source/payload hashes and rejects repeated binary or decoded pixel content. The ZIP includes a file index and repeat-source mapping; the private source review, verification record and ready-to-paste daily draft are under `reports/poster-collection-2026-10-06/`. No originals were moved, deleted or modified.

**Verified by:** 231 reviewed source hashes unchanged; ZIP CRC and member/payload SHA-256 checks passed; all 108 images decoded successfully with unique bytes/pixels/names. GIMP rendered all 55 editable sources for visual comparison; only nine otherwise unrepresented designs were packaged. Final archive size: 252,134,107 bytes.
**Follow-ups:** staff enter actual task duration and submit the provided daily draft under their own account. No daily report was submitted automatically.

## 2026-10-05 — Verify and deploy system audit corrections
**Area:** admissions form · testimonials · cross-system audit evidence
**Environments:** website demo ✅ · production ✅; Reception demo/production and private Reporting staging verified
**Commit(s):** this commit (see Git history)

Verified the supplied eight findings before making focused changes. Website contact validation now blocks invalid/mismatched international contacts with Kenya as default, incomplete email domains and forward-step bypasses; testimonial controls activate correctly sized players on their actual page. Completed the illustrated remediation report and daily handover under the private `reports/system-audit-2026-10-05/` directory; Reception's staff redesign and administrator record restrictions and Reporting's deployed-version drilldowns are recorded in their repositories.

**Verified by:** website release 2026.10.05.4 on public routes, 11 affected byte-matched production files, production browser checks with no application submission, 524 adapter checks and 759 browser formatter checks per system and existing QR/pricing regressions. The cross-system register contains 66 deployed file hashes; protected screen evidence is explicitly synthetic, generated from deployed source. Report rendering and source evidence are retained privately.
**Follow-ups:** OTP lacks an email/SMS provider. The latest staff check has six active accounts, one approved legacy mailbox and zero disallowed emails; signed-in staff acceptance remains outstanding. Honeypot work is deferred. No daily report was submitted under another person's account.

# Worklog — Toolkit WordPress site

Newest first. One entry per meaningful change, written in the same commit as the
change itself. Format and rules: see `../AGENTS.md` §1.

---

## 2026-10-01 — Create a QR for the supplied Canva link
**Area:** communications · QR assets
**Environments:** local downloadable assets; no website deployment requested
**Commit(s):** this commit

Created PNG and white-background print SVG QR codes for exactly
`https://canva.link/hndn85fj1vl72os`. Added no tracking or redirect.

**Verified by:** independent ZBar decoding of full-size and 220 px PNGs to the
exact supplied URL.
**Follow-ups:** none for asset creation; retain the white border in print layouts.

---

## 2026-10-01 — Apply the revised September course fees
**Area:** course catalogue · admissions pricing · website content
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** this commit

Transcribed and checked the user-supplied revised September 2026 Kikuyu Campus
poster, then updated modern programmes and legacy hubs. Published the five
welding upskilling categories and separate exam fees; corrected Smart
Agriculture to KES 73,568, language levels to KES 35,000, and changed durations.
Removed superseded July standard/subsidised and language-package rates.

**Verified by:** two visual source checks; PHP syntax; source-based amount,
category, duration and legacy mapping assertions; real-template rendering and
pricing-off checks. Source image and transcription are retained in docs/pricing.
Release `2026.10.01.1` deployed demo first, then production. Five patched files
were verified byte-identically; 23 routes per environment passed in bare and
uncached forms (46 checks each). Verified all 15 course cards, legacy hubs,
welding categories, exam rates, current source labels, RPL remaining unpriced,
Apply and llms controls, and release headers. Demo desktop/390 px mobile renders
of welding, exams, French B1 and agriculture were checked with no horizontal
overflow. Screenshots and public-page verification copies are in each rollback.
No production application was submitted.
**Follow-ups:** Admissions can use the retained source/transcription for normal
fee-quotation review; no release work remains outstanding.

---

## 2026-09-30 — Produce the QR change report PDF
**Area:** documentation · communications handover
**Environments:** local artifact only; no website deployment
**Commit(s):** this commit

Produced a two-page A4 PDF covering the application QR, untracked Lnk.Bio QR,
Toolkit Control additions, and transition from the legacy Canva-hosted socials
entry point. Includes both scannable QR codes, download links, deployment checks
and the distinction between new artwork and previously printed legacy codes.
The Canva background is based on the project owner's description.

**Verified by:** two-page PDF metadata/text extraction; visual review of both
pages; independent decoding of both QR codes from the rendered PDF.
**Follow-ups:** hand over the PDF for communications use; no deployment needed.

---

## 2026-09-30 — Add an untracked Toolkit Lnk.Bio QR
**Area:** QR assets · Toolkit Control
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** this commit

Created PNG/SVG QR codes encoding exactly `https://lnk.bio/The_Toolkit_Africa`,
with no tracking parameters or Toolkit redirect. Added a separate preview and
PNG/SVG downloads to Toolkit Control. Existing application scan metrics remain.

**Verified by:** PHP/shell syntax; independent QR decoding at 888/220 px;
local dashboard rendering; existing application tracking regression checks;
demo-first release `2026.09.30.2`; byte-identical code/assets, QR downloads and
bare/uncached Apply HTTP 200, correct release headers on demo and production.
**Follow-ups:** check the dashboard and phone-scan a printed sample. Lnk.Bio
returned Cloudflare HTTP 403 to the automated destination check; the encoded
URL is exact, but the destination needs a normal browser check.

---

## 2026-09-30 — Add the live application QR and scan counters
**Area:** admissions · QR assets · Toolkit Control analytics
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** this commit

Created printable PNG/SVG QR codes for the verified live custom Toolkit form at
`/apply/`, tagged for admissions QR arrivals. Added 30-day/all-time QR counters,
downloads in Toolkit Control, and a selected-period counter in Site analytics.
The existing admissions/Mzizi integration is unchanged. Counts are browser-
reported tagged openings, once per session, with Do Not Track respected.

**Verified by:** live homepage/application markup; PHP/JS/shell syntax; focused
QR attribution, refresh deduplication, prior-visit and Do Not Track checks;
independent QR decoding at 980/220 px; local dashboard/analytics render checks;
demo collector 204 and originless 403; demo-first release `2026.09.30.1`;
byte-identical remote files/assets, bare and uncached Apply HTTP 200, release
headers and public JavaScript in both environments. One synthetic scan event
was sent to demo only; no application was submitted.
**Follow-ups:** staff should check the authenticated dashboard and scan a printed
sample before mass printing. No production test scan events were injected.

---

## 2026-09-22 — Restore Toolkit in Brief page styling
**Area:** website UI · institutional pages · regression repair
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** this commit

Added the nested Toolkit in Brief page to every theme-owned page allowlist so
its institutional template receives the same responsive stylesheet, modern
surface treatment and plugin cleanup as the working About page. Bumped the theme
release to invalidate cached unstyled output.

**Verified by:** PHP syntax and focused allowlist/release assertions; guarded
demo-first deployment; HTTP 200, versioned stylesheet, modern-surface class and
parent About controls; byte-identical remote files; desktop and 390 px mobile
Firefox screenshots in both environments; and LiteSpeed cache hit checks.
**Follow-ups:** centralize theme-owned page registration before expanding page
or story management through Toolkit Control.

---

## 2026-09-22 — Add the verified Toolkit location map to Contact
**Area:** website content · contact page · visitor directions
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** 48a946b

Replaced the broad place-name directions link with the established Toolkit map
pin at `-1.2704916, 36.6644851`, added a responsive lazy-loaded Google map to
the Contact page and aligned the visible address with the published Thogoto,
No. 7219 Karen-Kikuyu Highway location. The same centre point now anchors the
disabled-by-default Reception geofence configuration.

**Verified by:** matching the indexed Toolkit contact address to the organization
map listing, PHP syntax and theme source assertions; guarded demo-first rollout;
HTTP 200 and release-header checks; rendered address, exact coordinate and embed
markers; separate demo/production rollbacks; and byte-identical remote files.
**Follow-ups:** physically test the Reception 100 m boundary before enabling
automatic checkout; the public map itself is live in both environments.

---

## 2026-09-21 — Remove four former staff profiles from About Us
**Area:** website content · staff directory
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** 34546d8

Removed the About Us team cards, portrait references, roles and biographies for
Beatrice Mutinda, Esther Liberatta, Gladwell Mumbi and Lucy Wanjiru. The source
portrait files remain packaged for safe rollback but are no longer requested or
displayed by the page.

**Verified by:** PHP syntax and focused source assertions; guarded zero-fuzz
demo-first deployment; HTTP 200 checks of `/about-toolkit-africa/` in both
environments; absence checks for all four names and portrait references;
retained-profile controls; release-header `2026.09.21.1`; separate demo and
production rollbacks; and byte-identical remote-file verification.
**Follow-ups:** none.

---

## 2026-09-16 — Keep course decisions and actions visible while scrolling
**Area:** course UX · admissions conversion · mobile hero
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** f3b6395

Added an in-flow course action dock after every course hero. It becomes sticky at
the top only after users reach it, keeps the current course, primary fee or
schedule, details anchor and Apply action available through long pages, and
collapses to a compact three-part row on mobile instead of competing with the
existing bottom-right WhatsApp and help controls.

The site sweep also found the homepage mobile video badge sharing that
bottom-right control zone. Moved the badge to the left at the mobile breakpoint,
without changing desktop composition or the deferred video-loading behaviour.

**Verified by:** PHP syntax, CSS balance and focused source checks; guarded
zero-fuzz demo-first deployment with separate demo/production rollbacks and
byte-identity verification; five HTTP 200 smoke routes in each environment;
desktop/mobile screenshots before and after the sticky threshold; browser
geometry confirming a 68px mobile dock at `top: 0`, a clear details target and
no overlap with WhatsApp/help; a valid course-specific Apply URL without
submitting the form; and a final production mobile visual check.
**Follow-ups:** measure Apply and enquiry interactions once analytics ownership
is confirmed; shorten only course titles that still truncate ambiguously in the
compact mobile row.

---

## 2026-09-16 — Distinguish solar and welding upskilling fees
**Area:** website content · admissions pricing
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** 93cea4d

Clarified the approved brochure pricing before production promotion. Solar
Upskilling is KES 30,684, while Advanced Welding Upskilling is KES 66,300
standard or KES 30,684 subsidised; these are now labelled separately from the
full solar and welding programme prices on their legacy course hubs.

**Verified by:** direct comparison with pages 9 and 12 of the approved 7 July
2026 brochure; PHP syntax and focused catalogue assertions; guarded demo-first
deployment; desktop/mobile visual confirmation of both labels; four priced hubs
plus an unpriced RPL control; six HTTP 200 routes per environment; separate
rollbacks; and byte-identical production verification.
**Follow-ups:** Admissions still needs to confirm installment terms, subsidy
eligibility, assessment charges and intake availability.

---

## 2026-09-16 — Publish approved brochure fees on legacy course hubs
**Area:** website content · admissions pricing · SEO
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** a819514 · 93cea4d

Confirmed the approved 7 July 2026 Toolkit brochure against the current 15-course
catalogue, including visual checks of its price tables. The catalogue prices were
already correct, but the older welding, renewable-energy, organic-farming and
digital-skills URLs still hid them behind a route-only condition; those four hubs
now carry explicit brochure-backed fee records and can display them under the
existing pricing feature flag.

Updated the welding fact register and AI guidance to distinguish published fees
from conditions that Admissions must still confirm, including installments,
subsidy eligibility, assessment costs and intake availability. Unpriced services
and pathways remain unchanged rather than receiving inferred prices.

**Verified by:** source-PDF metadata and SHA-256; extracted and visually checked
brochure price tables; direct comparison with all current catalogue fee fields;
PHP syntax and focused source assertions; guarded demo-first deployment;
desktop/mobile visual review of welding and solar prices; four priced hubs plus
an unpriced RPL control; six HTTP 200 routes per environment; separate rollback
snapshots; and byte-identical production verification.
**Follow-ups:** retain the approved brochure as the price source of truth and do
not infer prices for services or pathways that it does not price.

---

## 2026-09-16 — Strengthen the welding evidence cluster
**Area:** website SEO · welding content · internal linking
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** this commit

Current search comparisons still did not surface Toolkit for the tested
high-intent MIG/MAG and welding-course queries. Rather than publish another
generic article, improved two existing first-hand evidence pages: the Kakuma VR
welding story and the Kenyan welding-instructor programme now have concise,
intent-specific titles and descriptions based only on their published facts.

Welding stories now select related reading from a curated welding evidence set
instead of arbitrary same-category posts. The section identifies itself as
`More welding insights` and links to the canonical MIG/MAG training hub, creating
a clearer reciprocal topic cluster around the commercial page. Theme release
`2026.09.16.3` activates the metadata and cache transition.

**Verified by:** PHP syntax checks on `functions.php` and `single.php`; focused
source assertions for both metadata records, all four evidence slugs and the
welding-specific hub link; `git diff --check`; guarded demo-first and production
deployments with separate rollback snapshots and byte-identity verification;
four HTTP 200 smoke routes; and fresh sandboxed-browser visual review of desktop
and mobile welding pages plus a non-welding control.
**Follow-ups:** obtain approved duration, fee, entry and assessment facts, which
current competing results expose and Toolkit still cannot state. Connect the
organization-owned Google account to Site Kit/Search Console so query, indexing
and position changes can be measured rather than inferred from public searches.

---

## 2026-09-16 — Make the agentic schema map discoverable
**Area:** website SEO · AI/search discovery · robots policy
**Environments:** local ✅ · demo ✅ · production ✅ · Yoast setting ✅
**Commit(s):** this commit

Enabled Yoast's opt-in Schema aggregation endpoint after confirming that the
existing Schema Framework remains active and the resulting public schemamap
returns HTTP 200. Kept Yoast's `llms.txt` generator disabled because the child
theme already serves a curated `/llms.txt` and `/llms-full.txt` pair.

The current `robots.txt` deliberately blocks the general WordPress REST API,
which also hides the new schema endpoint. Added a production-only, narrow
`Allow` rule for Yoast's schema-aggregator path and advertised its XML schemamap
without opening the rest of `/wp-json/`. Bumped the theme release to
`2026.09.16.2` so the production cache transition is explicit.

**Verified by:** public HTTP 200 response from Yoast's XML schemamap; persisted
Yoast toggle state after a fresh settings reload; PHP syntax; demo-first and
production byte-identity deployments with separate rollbacks; unchanged demo
crawler policy; production release header `2026.09.16.2`; and live production
`robots.txt` retaining the broad REST block while exposing only the aggregator
path and its schemamap.
**Follow-ups:** monitor schema endpoint use and align the Yoast organization/site
names after the legal/trading-name owner confirms the authoritative wording.

---

## 2026-09-16 — Remove the reproduced mobile hero LCP bottleneck
**Area:** website SEO · homepage performance · Yoast review
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** this commit

Reproduced the homepage slowdown with a throttled mobile trace and found that
carousel autoplay replaced the preloaded first hero with slide two before LCP
settled. The homepage now renders an explicit responsive hero image, defers later
slides until requested, disables mobile/reduced-motion autoplay, removes a 260 KB
decorative background and unused legacy assets, and reduces the combined mobile
hero files from 400,016 to 278,126 bytes without changing the visible copy.

Documented the retained Yoast dashboard baseline and a priority recommendation
queue. A subsequent human-authenticated review confirmed zero serious Yoast
problems, one default-title notification, current analysis counts and the active
technical features without retrieving or entering credentials.

**Verified by:** PHP lint on `functions.php`, `front-page.php` and
`inc/hero-slides.php`; `node --check` on `hero-slider.js`; image-dimension and
size checks; `git diff --check`; and a sandboxed mobile browser fixture that kept
slide one active with zero fixture CLS, left slides two and three without `src`,
and loaded the requested slide successfully through manual navigation; guarded
demo/production byte-identity deployment; desktop/mobile visual review; and a
demo mobile trace recording `0.0016` CLS, approximately `2.6 s` LCP, one mobile
hero transfer and no retired Sina/Owl scripts.
**Follow-ups:** collect field performance data after the release has real traffic.

---

## 2026-09-15 — Connect welding evidence articles to the course hub
**Area:** website SEO · internal linking · welding content
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** this commit

Welding-related articles now end with a topic-specific call to action linking
back to the canonical MIG/MAG welding hub instead of the generic course
directory. The detection uses the story title, standfirst and category names,
so existing first-hand welding evidence strengthens the intended commercial
page without adding keyword-only posts or changing unrelated stories.

**Verified by:** PHP lint on `single.php` and `functions.php`; focused regex
checks for MIG/MAG/welding and unrelated story contexts; `git diff --check`.
**Follow-ups:** verify the contextual CTA on representative demo articles before
production deployment, then improve the two strongest welding articles with
trainer-reviewed original detail and reciprocal links.

---

## 2026-09-15 — Start evidence-led welding SEO remediation
**Area:** website SEO · welding content · AI/search discovery
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** this commit

Compared the September forensic audit with the July competitive audit and
roadmap, verified the current production metadata/index state, and created one
prioritized remediation map plus a claim-controlled welding fact register. The
first implementation slice removes a live course-fee contradiction, gives the
primary welding hub a Kenya-specific H1 and substantially deeper MIG/MAG,
pathway, evidence and admissions content, adds descriptive links to the distinct
VR and RPL routes, and expands the optional AI-readable reference map without
treating `llms.txt` as a Google ranking mechanism.

**Verified by:** PHP lint on `functions.php`, `inc/course-catalog.php` and
`template-parts/pages/course-detail.php`; `git diff --check`; JSON validation;
live HTTP inspection of robots, sitemap, AI guides, the welding hub and legacy
domain; serial public SEO audit passed all 13 priority URLs. A separate
six-worker audit found 132 transport timeouts among 179 sitemap URLs, while
sampled legacy-domain URLs returned HTTP 500. The supplied SEO Site Checkup
archive was retrieved and its 9 failures and 4 warnings were triaged against the
site implementation.

**Follow-ups:** deploy the patch to demo and visually validate desktop/mobile;
obtain Admissions, Finance, Compliance and Head of Welding approval for pending
facts; fix the legacy domain at hosting level; diagnose concurrent request
timeouts; reproduce homepage CLS/LCP in a browser trace; then improve the
strongest existing welding evidence articles before publishing new material.

---

## 2026-09-14 — Hide Kakuma from the public application form
**Area:** website admissions
**Environments:** local ✅ · demo ✅ · production ✅
**Commit(s):** `9919265`

Removed Kakuma from the campus choices shown by the public application form;
because courses are loaded only after a campus selection, Kakuma-related courses
are no longer exposed through the normal application journey. The change filters
only outgoing form options (including already-cached responses) and does not edit
Mzizi, cached source records, submitted applications, or the relay payload.

**Verified by:** PHP and JavaScript syntax checks; focused option-filter checks for
mixed case, unrelated campuses, missing school lists, and preservation of the
original cached response; guarded byte-identity deployments to demo and production;
real HTTP 200 public-options requests returned only Mombasa and Nairobi; browser
screenshots on both environments visibly confirmed Kakuma absent from the selector.
No application was submitted during verification.
**Follow-ups:** none.

---

## 2026-09-02 — Deploy bounded student sign-up to Campus staging
**Area:** Virtual Campus · cPanel deployment · acceptance automation
**Environments:** local verified ✅ · temporary staging page/readiness ✅ · hosted account acceptance pending host recovery
**Commit(s):** Campus `3596b87`; deployment script in this commit

Deployed the Toolkit-styled Virtual Campus student self-registration increment
through retained non-browser cPanel automation. The script backed up all replaced
application files and the private environment, enabled sign-up only under the
explicit non-production acknowledgement, refreshed cached configuration and
passed the staging readiness gate. `/register` returned the new page over HTTPS.
No database reset, migration or production change was included.

The first hosted acceptance request correctly exposed two test-harness issues:
the older hexadecimal demo password does not satisfy the new mixed-case/symbol
policy, and cPanel WAF rejects a synthetic `role=administrator` form field with
HTTP 406. The retained script now derives a compliant test-only password, leaves
the adversarial role check to Laravel tests and still requires `/admin` to return
403 after normal registration. Final account creation is pending because the host
subsequently stopped accepting connections from this machine on both the staging
subdomain and main Toolkit site.

**Verified by:** Bash syntax; Campus commit `3596b87` with 66 tests and 262
assertions; production asset build; desktop/mobile browser review; hosted
registration page HTTP 200; staging readiness pass; rollback retained at
`rollbacks/campus-test-pre-2026.09.02.3`.
**Follow-ups:** retry the bounded hosted account acceptance after the host/IP path
recovers; keep production sign-up closed until privacy and verified-mail gates are
approved.

## 2026-09-02 — Reconcile the deployed Campus staging readiness gate
**Area:** Virtual Campus · cPanel · readiness · non-destructive release
**Environments:** `campus-test.toolkitafrica.ac.ke` staging ✅ · production unchanged
**Commit(s):** Campus `300fcff`; deployment script in this commit

Deployed the revised secret-safe Campus readiness command without touching the
database or accepted test records. The first run exposed the missing Laravel
public-storage link; release `2026.09.02.2` created it. The hosted command now
reports the explicit LiveKit rehearsal as staging-ready while preserving the
statement that production captions remain blocked. Retained the previous remote
file and deployment log under `rollbacks/campus-test-pre-2026.09.02.2` and
removed the temporary cron after verification.

**Verified by:** hosted `campus:readiness --json` returned `ready: true`, the
production-caption warning remained present, HTTPS login retained HSTS and the
public route returned 200.
**Follow-ups:** complete human mobile/constrained-network testing and provide
the direct owner inputs listed in the Campus owner tracker before production.

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
