## 2026-10-29 — Separate curated Windows handoff from Campus runtime deployment
**Area:** workstation continuity · Virtual Campus release tooling · file privacy
**Environments:** local tooling verified; Windows volume unavailable; no application-host upload
**Commit(s):** this commit

Updated the Windows exporter to use committed Campus source and shared exclusions across every copied source. It now builds and scans the whole handoff in private local staging before updating managed Windows folders, verifies checksums after copying, rejects unmounted/non-Windows destinations and symlinked managed folders, and supports an explicitly labelled local preparation mode. Final management reports and Campus progress reports remain available; private runtime/deployment and Wingu/attendance preparation records do not travel.

Reviewed the provisioned deployment process: Campus uses guarded Fileman/Cron deltas; the initial-provisioning script resets its isolated test database and must not be reused for this update. Added an exact Campus runtime-delta checker. Only the 11 reviewed PHP/Blade/CSS files, Vite manifest and validated hashed assets can enter this candidate; documentation, reports, tests, source CSS/JS and standalone helpers are rejected. The checker itself runs locally and is not uploaded.

**Verified/security review:** four disposable-repository sync tests pass, including secret exclusion, committed-vs-working Campus source, preservation of composer.lock, pre-copy signature failure, stale managed deletion, unmanaged-file preservation, local-only preparation labels, absent mount and symlink rejection. Three runtime-delta tests pass; existing runtime package tests pass and 202 runtime destination checks pass. Shell syntax and diff checks pass. Git remotes were refreshed without incoming divergence. No live application, payment, applicant or Mzizi request was submitted.

**Follow-ups:** prepare the full local handoff after committing source snapshots; mount the actual Windows volume normally and rerun sync when its device is available. UDisks and the kernel currently expose only the 256 GB Linux SSD; recorded Windows UUID/device are absent. Campus hosted updates require the provisioned deployment session and fresh drift/readiness checks; no production deployment is claimed here. Preserve the user's unrelated editor swap file.

## 2026-10-28 — Document the complete application architecture and trim the weekly plan
**Area:** admissions architecture · Mzizi · Reception · calling letters · management reporting
**Environments:** local source analysis and report artifacts only; no runtime deployment or live mutation
**Commit(s):** this technical-reference and revised-plan handoff

Traced the full website application journey beyond the submission handler: routing/assets, six-step UI, international phones, public guards and request/response shapes, 24-key encrypted payload, schema/key rotation/duplicates, exact 18-key Mzizi mapping, sessions/response classification, leases/recovery/retries, persistent catalogues, cron/private worker, admin review/export/settings, website DOCX/PDF, signed Reception mirror/render/download, separate visitor bridge, QR/metrics, configuration and runtime packaging. Added a 61-file digest/function/hook/configuration/error reference. Recorded static validation/label/intake, retention, concurrency, logging, UX and provider limits without claiming new live exploit evidence, vendor root cause or fixes. Historical deployment evidence is dated separately from current source verification; no production application, Mzizi replay, letter issuance, fresh private-data query or live negative probe.

Prepared the branded technical Word/PDF with Thomas Kamau credited, exact official imgs logo, two diagrams and all code examples preserved. The PDF is 24 A4 pages; all 473 source content blocks survive in Word, all headings survive PDF, text stays inside page bounds and all linked local sources resolve. Source-index hashes confirm all 61 inventoried implementation/tooling files remain unchanged. Visually reviewed the full document via page sheets. Word/PDF and private test/source receipts remain ignored local reports, not runtime payloads.

On the user's follow-up, removed the entire final Completion evidence section and both following paragraphs from the weekly plan. Regenerated matching Markdown/Word/PDF; the 56 remaining source blocks, six daily/coverage rows, Thomas author and exact logo remain. Main PDF and independent isolated-profile Word render are still two pages; both pairs of pages visually checked. Detailed OKR reconciliation remains in the separate alignment note; no original OKR/asset/attendance change.

**Verified/security review:** current isolated checks pass: 524 form validation/contact cases, 759 browser phone-format cases, 28 signed export/auth/privacy cases, 47 real-renderer/signed letter cases, QR/session/DNT fixtures, and 73 relay/catalogue cases against a disposable MariaDB 11.8.8 with every HTTP response mocked. Initial sandboxed Unix-socket binding was denied; the permitted local rerun passed, server stopped and temporary database removed. No live Mzizi request. Checked actual documentation/plan/reporting diff for accurate trust boundaries, private data, credentials and production claims; no runtime source, dependency, PHP, AGENTS or allowlist changed. Existing source findings are documented follow-ups, not an undisclosed runtime release. Both evidence directories are Git-ignored; no report/README/helper/archive uploaded. Outgoing source review confirms exactly five documentation/reporting files, no runtime payload or private evidence, valid links/unchanged implementation digests, passing artifact checks and no credential-pattern matches. Remote fetch confirms no incoming branch changes; perform the same scoped check again before push. Unrelated stashes preserved.

**Handoff:** docs/architecture/APPLICATION-FORM-TECHNICAL-ANALYSIS-2026-10-28.md and SOURCE-REFERENCE companion; technical Word/PDF under reports/application-architecture-2026-10-28/. Revised weekly Word/PDF under reports/weekly-plans/2026-10-26-to-31/. Updated Thomas's ready-to-paste daily draft; no external report filed. Runtime follow-ups need their own scoped implementation, regression/security review and demo-first release.

## 2026-10-28 — Prepare a bounded weekly work plan aligned to the current OKRs
**Area:** weekly planning · cross-system priorities · management reporting
**Environments:** local planning/report artifacts only; no runtime or external system changes
**Commit(s):** this source plan and alignment handoff

Prepared Thomas Kamau's 26–31 October plan in the recent October report format, using the exact official imgs logo and matching A4 Word/PDF. Both render as two pages. Monday/Tuesday record verified calling-letter and SEO work; the remaining days target two staff acceptance reviews, a fee-source decision, known-risk ownership, five shared AI/source questions, five communications assets and a weekly handover. Cap new delivery at one small approved correction, with 13 focused planning hours and a separate four-hour support/rework allowance; Saturday is limited and conditional on availability. These are estimates, not attendance or new submissions.

Crosschecked all 25 Q4 result texts across Markdown, Word and the newer workbook. The six-objective Q4 document remains proposed; the eight-objective finalized portfolio remains the baseline. Mapped all portfolio objectives and Q4 areas without silently activating targets. Prioritised the 31 October staff-review/risk-owner milestones; November/December pilot, module rollout, provider, restore and infrastructure outcomes are not promised this week. Reconciled stale owner notes against newer deployment and Wingu records; no duplicate completed deployment or host-provisioning task was invented.

**Verified/security review:** dates are Monday 26 through Saturday 31; both Word/PDF retain all 59 source content blocks, six daily rows and six coverage rows. Exact embedded logo digest and Thomas author verified; all source/OKR hashes unchanged. Both PDF pages and the independent two-page LibreOffice Word render were visually reviewed. Initial sandboxed LibreOffice launch could not start; an isolated temporary profile outside that restriction rendered successfully, without touching the personal profile. Source documents, attendance and existing stashes are unchanged. Reviewed report/notes for factual limits, private data and runtime exclusion; no network, production check, account action, code change or deployment. No regression suite needed for a planning document.

**Handoff:** docs/reporting/WEEKLY-PLAN-2026-10-26-TO-31.md plus the detailed ALIGNMENT.md; local Word/PDF/Markdown, source register and validation under reports/weekly-plans/2026-10-26-to-31/. Daily draft for 28 October prepared. No report filed externally and no staff/hosting message sent. The user's next request is a comprehensive technical analysis of the entire application-form journey; that analysis is separate from this completed weekly plan.

## 2026-10-27 — Countercheck course-fee DOCX against the live website, excluding welding
**Area:** course information · pricing source reconciliation · reporting
**Environments:** production read-only comparison verified; no runtime deployment or price change
**Commit(s):** this comparison/reporting handoff

Located the 23 September fees Course.docx in Downloads/Documents and compared its seven digital courses against the current directory and both digital detail pages. Three paced normal GETs returned 200/release 2026.10.27.1. All show the old suite range 18,000–65,000 and no individual digital fee table; the DOCX maximum is Cyber Security 68,000 and contains exact prices/durations/prerequisites for all seven courses. The page's coarse duration bounds agree, but individual details are missing and generic open-entry wording does not convey the five basic-computer-knowledge requirements. Do not invent individual website prices where only the suite range is shown, or add exam fees the newer sheet does not specify.

Checked the remaining non-welding directory cards and exam table against the approved September poster: Electrical/Solar skilling 120,962; Solar Upskilling 30,684/15 days; Smart Agriculture 73,568/from 4,000; Entrepreneurship 8,000–35,000; French/German all levels 35,000/three months and all five exam rates match. The June full DOCX has superseded subsidy/exam/duration values and additional named agriculture/entrepreneurship offerings not itemised on the site. Those older differences are not automatic price errors and must not override the later approved poster. The newer digital sheet conflicts with the poster's digital upper limit, so source reconciliation is documented rather than silently changing fees. Welding was excluded as instructed.

**Verification/privacy review:** DOCX ZIP/XML metadata and table extraction; duplicate catalogue hashes; inspected source/approval history and actual three response bodies. Local current source matches live facts; initially stale/inaccessible external browser material was not used for current claims. Fixed public destinations, serial five-second spacing, user's four-attempt timeout budget available with zero retries; no production application, Mzizi call, personal record, credential or server mutation. No runtime payload/README/report/document uploaded. Retained public HTML and fee-source digest remain in ignored private reports/course-price-comparison-2026-10-27/. No code regression tests warranted for a read-only comparison. Reviewed outgoing documentation for source accuracy and private-data boundaries before publication; no new security blocker identified in this documentation-only scope.

**Handoff:** docs/pricing/COURSE-PRICE-COMPARISON-2026-10-27.md details confirmed mismatch, missing granular fees, superseded source values and a concrete digital-update scope. Thomas daily draft updated; no external report filed. No website fee changed and no existing stash touched.

## 2026-10-27 — Record Search Console recovery and explain the 40-page discovery count
**Area:** sitemap fetch follow-up · Console submission handoff
**Environments:** production read-only follow-up; owner reports Console fetch recovered; no runtime change
**Commit(s):** this reporting follow-up

The owner showed General HTTP error for the correctly submitted sitemap index, then confirmed it loaded after waiting/refreshing with 40 discovered pages. One normal fresh index GET returned 200 in 0.89 seconds and matched the earlier XML hash exactly. No sitemap/robots, application, cache or firewall setting was changed. A bounded follow-up native log summary found both current main-site access logs empty; its temporary cron completed and removal was verified. Therefore neither the failed request nor recovery cause can be correlated from that sample. Do not substitute earlier genuine Googlebot successes for proof of this exact Console fetch, or claim a code fix resolved it.

Clarified that the existing index references both 40-page and 155-post children; submitting all files separately is unnecessary. The reported count matches the page child, while the post child's processing status remains unconfirmed. Blog posts count as pages in Console and discovery is separate from indexing. Supplementary Yoast schema resources are outside the main content inventory. The correct full URL was used; the owner's shorthand was not evidence of user error.

**Review/verification:** actual retained XML hash/status, public DNS lookup and native receipt/cleanup checked; four-total timeout read budget available, no timeout retry needed. Exact fixed hosts/log basenames, bounded private aggregates, no raw log/query/referrer/UA or public IP publication, no WordPress bootstrap or helper upload, no application/form/Mzizi write. Collector lint/compilation passed; no runtime regression tests warranted for a read-only investigation. Follow-up evidence stays ignored under reports/sitemap-fetch-followup-2026-10-27/. Reviewed reporting diff for accurate limits/privacy before publication. Google official guidance supports live URL inspection if the error recurs; no console action or external staff report filed. Updated Thomas daily draft and sitemap handoff; unrelated stashes preserved.

## 2026-10-27 — Verify sitemap submission readiness and genuine Googlebot access
**Area:** SEO monitoring · Search Console handoff · private access-log evidence
**Environments:** production read-only checks verified; no runtime deployment or configuration edit
**Commit(s):** this evidence handoff and Thomas daily-draft update

The owner requested sitemap health and Google access checks. Five paced normal GETs returned 200: robots, sitemap index, 155-post sitemap, 40-page sitemap and supplementary five-URL Yoast schema sitemap. Primary inventory is 195 unique canonical-host HTTPS locations with valid sitemap namespace and parseable, non-future-date lastmods; two children, no duplicate/admin/utility/upload-image entries. The actual robots groups allow Googlebot/Googlebot-Image to access the index, children and all primary URLs; the specific schema allow overrides the broader REST disallow. Root live .htaccess contains no Googlebot/user-agent blocking rule. The XML noindex header is expected Yoast behavior, confirmed against its official specification, and does not block sitemap processing. Handed off the main sitemap_index.xml URL for Console submission.

Collected a read-only native summary from the two current main-site access logs, capped at 2MB each; neither cap was reached. Parsed 4,669 observations spanning 26–27 October. Eight Googlebot-labelled IPs all match Google's official current common-crawler ranges. Their 29 observations contain 25 status 200, one 304, one 301 and two unclassified 404s; no 401/403, 429 or 5xx. Verified Googlebot fetched robots.txt successfully, but no sitemap-file request appears in the sample. Latest verified Googlebot activity is 27 October 08:24 Nairobi, before the updated sitemap handoff; do not claim Google has already consumed the new inventory. Hosting-wide WAF policy and future access remain outside this evidence. Two 404s retain no arbitrary request path and are not evidence of sitemap blocking.

**Security/method review:** exact fixed HTTPS first-party reads only; five-second spacing, user's four-total timeout budget available, zero public timeout retries, no impersonated Googlebot/UA-negative probe, full content crawl, form submission or application mutation. Native helper reads fixed log basenames and only emits bounded status/time aggregates plus candidate crawler IPs privately for official-range matching; no raw log, query, referrer, user-agent, account or application data copied. Read-only .htaccess retained privately. No WordPress bootstrap, PHP/configuration/dependency change or diagnostic endpoint. The one-attempt private inline CLI job completed and its exact cron was removed and absence verified. PHP lint/Python compilation, actual XML parsing, robots-group inspection, official-range verification and whitespace checks pass; no runtime regression tests added for a read-only check. Reviewed evidence/report diff for privacy and outbound/path boundaries before publication. Initial automatic review expired before execution; its explicitly permitted single retry succeeded. No IP rotation, WAF refusal or unresolved source security blocker in this documentation-only scope.

**Handoff:** docs/seo/SITEMAP-CONSOLE-HEALTH-2026-10-27.md records results, limitations, references and submission steps. Thomas daily draft updated; no Console submission or external staff report filed. All private evidence/collector copies under ignored reports/sitemap-health-2026-10-27/; no report/README/document/runtime file uploaded. Preserve unrelated stashes and clean source publication.

## 2026-10-27 — Verify the SEO and welding-price release on demo and production
**Area:** website discovery · tuition · resource notice · bounded deployment reads
**Environments:** demo verified; production verified — release 2026.10.27.1
**Commit(s):** runtime source c648685; scoped retry tooling and final verified handoff in this change

Completed the five-file SEO release after the user explicitly authorised production deployment and clarified “four total attempts”. The demo verification-only continuation rechecked all runtime hashes and the removed publishing job without uploads or publication replay. All 13 normal public acceptance checks passed. Fresh production baselines matched the reviewed Git base; zero-fuzz focused patches produced exactly the same five hashes as verified demo. Production uploads were byte-verified, functions.php last. The one-time native publisher/cache worker succeeded, creating resource post 14483; its temporary cron was removed and absence confirmed. Demo reused its matching owned post 14480 without a duplicate.

Both environments passed 13 public checks each: page/post sitemaps, homepage, application page, graduation, testimonials, footprint, course directory, welding hub, both detailed welding programmes, blog and the resource article. Page inventories now contain 40 URLs including all 15 enabled programmes exactly once; post inventories contain 155 URLs including the graduation notice. Single correct canonical, one heading/schema graph and demo noindex/production index policy passed on sampled pages; course WebPage/Breadcrumb pieces, direct welding links, approved imagery/section links and live KES 55,017 upskilling/KES 127,895 skilling prices passed. Separate previously approved industry/TVET rates remain unchanged. The 195 combined URLs are an inventory, not proof of Google indexing or search growth.

**Bounded timeout handling:** exact read-only Fileman content requests, Cron listings and normal public GETs may use four total attempts with 15-second pauses for this run. Login, uploads and Cron add/remove remain single-attempt, including mutations carried over GET. Demo directory read had two ReadTimeout events and succeeded on attempt three; production had one hosting ReadTimeout event and recovered on attempt two. No write retry, rate-limit/WAF refusal or IP rotation. The CLI default remains one attempt; --timeout-attempts 4 is opt-in. Neither AGENTS file changed. All original failure journals remain intact; verification receipts supersede their earlier pending status.

**Verified/security review:** 17 isolated release/recovery tests pass, including exact four-attempt exhaustion, eventual read success, non-timeout stop, hosting read allowlist, upload/GET-mutation no-retry, WAF/rate-limit stop, source drift, duplicate/interrupted-worker and safe verification-only boundaries. The unchanged runtime source retains 133 SEO, 14 publisher, nine audit, 35 graduands and ten saved local desktop/mobile checks. PHP lint, Python compilation and diff checks pass. Retained HTML/XML from all 26 successful public checks contains no PHP fatal/uncaught or rendered warning markers; native workers exited zero. Re-reviewed actual runtime/publisher/recovery diff and freshly fetched production baseline before promotion for permissions, fixed public-route/path/outbound handling, validation/escaping, owned metadata/content, duplicate/lock/failure behavior and secret/PII minimisation. No new unresolved source security blocker identified within this scope; this is not a blanket audit guarantee.

Only five allowlisted PHP runtime files reached production. No README/AGENTS, documentation, report, development/test file, source map, backup, archive, diagnostic helper, dependency or PHP configuration upload. Publisher source ran inline through the existing private CLI mechanism. No production test application, Mzizi replay, applicant modification or email/SMS. Site Kit and PHP/dependency configuration remain unchanged; the earlier fileinfo exception remains closed.

**Evidence/handoff:** private reports/seo-fixes-2026-10-27/{demo,production}/verification.json plus native/public receipts and transport counters; demo continuation evidence is under demo/verification-resume-1/. Updated final release notes, original-audit follow-up and Thomas's ready-to-paste daily draft; no external report filed. Legacy-domain/profile ownership and actual search-performance measurement remain follow-ups. Pre-push review includes outgoing diff/credential-pattern scan and normal fast-forward ancestry; unrelated stashes preserved.

## 2026-10-27 — Authorise Git publication and trace the AGENTS policy history
**Area:** source publication · policy provenance · release reporting
**Environments:** local/GitHub source only; production deployment remains pending
**Commit(s):** pending source c648685, c2aef14, f6e1378 and this publication record

The user explicitly instructed pushing the pending work despite incomplete live demo acceptance and requested examination of AGENTS.md. This instruction authorises Git publication for this run; it does not claim demo acceptance passed or authorise bypassing production verification. No hosting requests, deployment, policy rewrite or unrelated stash changes are part of this action. The earlier checkpoint statements about withholding Git publication describe the earlier decision and are superseded by this instruction.

Git shows wordpress/AGENTS.md was introduced in eeae31d on 17 October during the Mzizi relay/course-cache work, including the push/deploy restriction and stop-on-timeout guidance. The conversation had requested ongoing flaw/security checks in README or AGENTS; the detailed wording was added as repository guidance rather than being a verbatim user instruction. Commit 50e7453 on 26 October added the PHP runtime/advisory paragraph. Both commits use the configured kamauthomas author/committer identity, which does not establish that the human personally edited them. The file is byte-identical to the freshly fetched remote and none of today's pending commits changes it. The parent AGENTS file has a 20 August filesystem modification time; that alone does not establish editor identity or version history. Leave both files unchanged for the user's review.

**Pre-push review:** fetched origin and confirmed its branch remains the ancestor of the pending work; reviewed actual runtime diff and source-only publishing/recovery tooling again. Existing fixed routes/paths, escaping, owned publication checks, duplicate/interruption safeguards and runtime allowlist remain intact. The scoped local checks passed previously on these unchanged source bytes (133 SEO, 14 publisher, 12 recovery, nine audit, 35 graduands); ten saved layout checks remain recorded. No new runtime change or unresolved new source security blocker identified. The demo graduation-page transport failure and incomplete public acceptance are explicitly retained as deployment limitations. Recheck outgoing tracked blobs for credential patterns, exact five-file runtime payload identity, whitespace and clean status before the normal fast-forward GitHub push. Documentation is Git content, never a server payload.

**Handoff:** Thomas's daily report and SEO checkpoint now distinguish authorised source publication from the still-pending production release. No report filed externally. Existing unrelated stashes retained.

## 2026-10-27 — Reconcile demo SEO activation; stop safely on public verification failure
**Area:** deployment recovery · SEO verification · reporting
**Environments:** demo runtime and native publisher verified; public acceptance incomplete; production unchanged
**Commit(s):** runtime source c648685; guarded continuation and checkpoint in this change

On the user's resumed deployment instruction, one normal fresh read confirmed the interrupted functions.php upload had arrived. Added and exercised a narrow second recovery: verifies the original journal chain and precise pre-worker state, checks all five runtime hashes and skips completed writes. All five matched; zero repeat uploads. The demo publishing/cache worker completed for matching owned resource post 14480 (`created: false`, no new duplicate). Its temporary cron was removed and absence verified. Demo XML checks passed: 40 page URLs including all 15 catalogue programmes once, and 155 post URLs including the resource article. Homepage and application-page rendered release/canonical/heading/schema/noindex checks passed.

The next normal demo /graduation/ request failed with a transport exception reported as a timeout; no response was saved. Stopped all live requests immediately without retry/IP rotation. This is not proof of WAF blocking, a PHP error or general outage. Remaining page/price/link/blog acceptance is incomplete. No production preparation or mutation, production test application, Mzizi replay or email/SMS occurred. Remote publication remains withheld because a required demo check did not complete.

Prepared a verification-only continuation for checked/restored access. It requires the saved successful native receipt and removed-job state, freshly rechecks the five runtime hashes and exact cron absence, then performs normal public checks. It cannot upload, add jobs or republish. Existing-attempt, drift, incomplete-worker and receipt guards fail closed. Original failed journals remain unchanged; private CHECKPOINT.json and tracked release notes record exact progress and next command.

**Verified/security review:** 12 isolated release/recovery tests pass, including old/new catalogue and activation, unrelated drift, unknown bytes, started-worker refusal, tampered parent, no-write/no-publication verification, unsafe native receipt, replay refusal and first-transport-failure stop. Also passed 133 SEO checks, 14 publisher checks, nine audit tests, 35 graduands checks; ten prior local desktop/mobile layout checks remain recorded. Python compilation and diff checks pass. Re-reviewed actual runtime/publisher/recovery diff for fixed routes/destinations, output escaping, ownership/content/metadata and collision handling, no client-write/upload/SQL endpoint, no applicant/secret output, exact fresh hashes and interruption handling. Five runtime PHP files only: no README, document, report, test, archive or helper upload. PHP/dependencies/Site Kit unchanged; no closed fileinfo exemption reused. No new unresolved source security blocker identified; live demo acceptance remains the deployment blocker.

**Handoff:** updated docs/seo/SEO-FIXES-2026-10-27.md and Thomas's ready-to-paste daily draft; no external report filed. Primary upskilling KES 55,017 on campus/skilling KES 127,895 are in demo source, rendered price checks and production remain pending. Other approved delivery rates retained pending optional clarification. Source/checkpoint committed locally only; prior unrelated stashes preserved.

## 2026-10-27 — Checkpoint the partial demo SEO upload after transport failure
**Area:** deployment incident · safe source reconciliation · reporting
**Environments:** demo partially transferred and unverified; production unchanged
**Commit(s):** source c648685 and this checkpoint/recovery tooling

Fresh demo preparation produced five linted runtime files identical to the reviewed source. Three uploads were byte-verified: SEO module, welding template and story-image resolver. Hosting transport failed with upload intent at inc/course-catalog.php; its final state is unknown. functions.php was not attempted, so this release has not activated the new SEO hooks. No publishing/cache cron, draft/article, production mutation or application submission occurred. The old wrapper retained a generic transport failure rather than the underlying exception type: WAF/IP blacklisting is not established. Stopped all hosting requests immediately, requested access confirmation, and did not retry/rotate IPs. Site Kit settings were not touched.

Prepared a narrow recovery path, not a blind replay: catalogue read first; all three previously verified hashes must match; the uncertain catalogue may be old or intended bytes; untouched functions must match its baseline. Any unknown edit stops before mutation. Missing writes only, functions last, separate journal, original failed receipt preserved, and a begun publication job is explicitly ineligible. Five isolated scenarios exercise the actual deploy path through fake Fileman: old/new catalogue completion, unrelated verified-file drift, unknown catalogue bytes, started-worker rejection and original-command replay rejection. No live recovery request has been made while awaiting confirmation.

**Verified/security review:** recovery five scenarios pass, along with 133 SEO checks, 14 publisher checks, nine audit tests, 35 graduands checks and ten local desktop/mobile template checks (visual screenshots reviewed). Actual new recovery diff reviewed for fixed runtime destinations, exact preconditions, fresh CAS reads, preserved evidence, failure handling and no repeat publishing. No new runtime behavior in this checkpoint commit. Required native/demo verification is incomplete, so production and remote publication of the pending release are withheld. No unresolved new security issue in the prepared scope; access/verification remains the blocker. No PHP/dependency change or reuse of the closed fileinfo exception.

**Handoff:** docs/seo/SEO-FIXES-2026-10-27.md and private reports/seo-fixes-2026-10-27/CHECKPOINT.json record the exact state and gated recovery command. Thomas daily draft updated; no report filed externally. Primary upskilling KES 55,017 and skilling KES 127,895 are prepared; separate previously approved delivery rates are retained pending optional clarification. Source remains local until required release checks pass. Existing unrelated stashes preserved.

## 2026-10-27 — Prepare programme discovery, canonical and welding-price repairs
**Area:** website SEO · programme links · approved tuition · editorial resources
**Environments:** local verified; one normal demo source read passed; deployment pending
**Commit(s):** this release

Added enabled catalogue routes to Yoast's existing page sitemap with duplicate protection and modification dates based on visible course sources. Public virtual course/showcase pages have one explicitly owned canonical; demo still identifies production and keeps noindex. Filled missing course WebPage/BreadcrumbList pieces within Yoast's single graph. The welding hub now links to the detailed skilling and upskilling programmes using their existing approved duration/entry/intake facts, retaining VR-methodology and RPL access plus a catalogue-off fallback. Owner specified KES 55,017 upskilling and KES 127,895 skilling: main upskilling fee/legacy note updated to the on-campus rate; skilling already matches. Separate previously approved industry/TVET categories are retained pending the optional clarification; no alternative rate is invented. The owner confirmed no access to the separate legacy host, so no legacy/DNS/profile mutation or retry is planned. Site Kit is collecting data per owner; no account/plugin setting change or immediate ranking gain is claimed.

Prepared one new institutional resource article pointing to the already-published graduation programme/graduands, without an invented event recap, backdating, copied names or unsupported outcomes. The deployment-only publisher stages an owned draft, validates exact slug/content/metadata ownership, publishes once and refuses collisions/human edits. Failed/concurrent publication leaves a private owned draft rather than overwriting another record; repeated complete publication is idempotent. Existing approved graduation imagery is reused. No runtime publication hook or live diagnostic helper is introduced.

**Verified/security review:** 133 isolated real-catalogue/hook/template checks; 14 publisher ownership/idempotence/draft/failure/concurrent-collision fixtures; nine audit parser/fail-stop tests; existing 35 graduands/control checks; modified PHP lint/diff checks pass. Actual diff reviewed for validation/escaping, public-route allowlists/router precedence, disabled course/redesign behavior, canonical single ownership, bound WordPress APIs and collision/failure handling. No new upload/download endpoint, SQL from client input, secret/PII logging, external destination, application/Mzizi change or missing runtime dependency. Five runtime PHP files only; no README/docs/reports/tests/archives/assets upload. Publisher source runs inline from private one-shot CLI, not a server file; wp_mail is suppressed in that worker and recognised subscriber plugins require separate review. Native worker/site verification, private source baselines and identical demo-first promotion remain required; no production deployment has yet occurred. No unresolved security blocker in this reviewed scope. PHP/Composer/configuration unchanged; prior fileinfo exception remains closed.

**Handoff:** runtime file allowlist and one-attempt rollout are in scripts/deployment/releases/deploy-seo-discovery-2026.10.27.py; private evidence under reports/seo-fixes-2026-10-27/. Thomas daily draft will be updated with actual receipts. No external report submitted; unrelated prior stashes preserved.

## 2026-10-27 — Compare July–October SEO evidence and inspect the live public site
**Area:** SEO assessment · search measurement · discovery and content integration
**Environments:** production/demo read-only sample verified; no deployment or settings changes
**Commit(s):** this audit tooling, evidence summary and Thomas daily draft

The owner confirmed there is no Search Console. Compared the July start/history, July competitive review, August HTML-only audits and September serial priority/inventory evidence with a fresh bounded public sample. All 13 like-for-like priority routes still pass core rendered checks; 18 main-domain HTML responses returned 200, demo remains noindex with production canonical, and sampled utility pages retain expected noindex. Sitemap inventory is 179 URLs (154 posts/25 pages), identical to September, while all 15 directory-linked catalogue URLs remain absent. Two sampled programme URLs work and are self-canonical. Graduation is indexable but lacks rel=canonical. Full saved hub HTML contains no direct link to either detailed new welding programme; its course facts remain less specific. The public blog's latest featured story is dated 3 August. Code inspection ties the sitemap omission and showcase canonical gap to incomplete virtual-route integration, rather than proving any ranking regression from the latest operations releases.

The 24th request, to the legacy domain after 23 normal completed responses, timed out. Stopped immediately without retry, IP change, origin follow-up, cPanel request, server query or authenticated probing. Main-domain responses preceding it were normal; this is not evidence that the main site is down. The old-domain redirect path remains unverified from this machine. Public owned-profile/first-hand partner search material still points to that domain. No numerical search ranking, organic growth, indexed-page count, backlink growth or conversion attribution is claimed. First-party metrics store daily aggregate events without search queries/referrer attribution; no extraction was attempted after the stop. Historical concurrency timeouts and image-as-HTML audit errors are excluded from the comparison.

**Verified/security review:** nine offline parser/fail-stop tests cover OG property detection, noindex scope, missing/cross canonical, JSON-LD parsing and first-failure transport/auth/WAF/rate/server refusal with no retry. Fixed the initial collector OG key typo offline against retained raw responses; images were present and no missing-image finding is reported. No additional HTTP requests for reanalysis. Tool uses fixed HTTPS hosts, serial five-second spacing, no form/session/account mutation and private ignored raw evidence; refuses reruns over existing evidence. Reviewed actual diff for outbound destinations, paths, data minimisation and failure handling. No credentials, personal application data or server payload added, no production runtime file change and no unresolved security blocker in this read-only tooling/report scope. No broader application regression suite needed for an audit-only change.

**Handoff:** docs/seo/SEO-REVIEW-2026-10-27.md records measured readiness gains, confirmed discovery/canonical/link/content gaps, source-level explanations, caveats and next priorities. Private evidence: reports/seo-review-2026-10-27/public-audit.json and comparison-summary.json. Thomas's ready-to-paste daily draft is docs/reporting/DAILY-DRAFT-2026-10-27.md; no external report filed. Proposed fixes require their own isolated tests and demo-first verification; none were deployed during assessment. All documents remain in Git/local evidence, outside hosting payloads. Prior unrelated stashed work preserved.

## 2026-10-26 — Verify the sidebar calling-letter workspace on demo and production
**Area:** release tooling · deployment verification · reports
**Environments:** demo enabled and verified; production enabled and verified
**Commit(s):** Reception c977bea; WordPress 26fa583 and this verified handoff

The dedicated sidebar workspace and direct manual workflow are enabled on demo and production. All 15 runtime hashes match between the environments. Demo passed 31 actual EA-PHP/MySQL checks through private session/CSRF kernel dispatch, including three paced real HTTPS renders: manual issue/download/encryption/normalisation, duplicate prevention, replacement/history, native guided issue, manager denial, explicit fixture cleanup and complete transaction rollback. Production passed 16 read-only schema/routes/real-view render checks using an unsaved in-memory actor, with no account/applicant/manual-letter creation or issue/audit dispatch. Normal login and versioned CSS/JS returned 200 and asset hashes matched. Both one-time setup crons removed. No production test recipient, application, issued letter, email/SMS or Mzizi replay.

**Security/publication review:** actual fresh payload and feature diff rechecked for active/role/password permissions, CSRF, validation/escaping, fixed SQL/IDs, private downloads/encryption/errors/audit, exact upstream/minimal fields, bounded PDFs, keyed locks/uniqueness/version limits, interruption and duplicate handling. Reception 129 tests / 1,673 assertions; isolated Brave 50 desktop/mobile/logo/action/search checks with visual review; exact acceptance-helper fixtures 31 demo / 16 production pass. Outgoing changed tracked blobs scanned for high-confidence credential patterns (19 Reception / 7 WordPress): none found, with the usual focused-scan limitation. No README/report/doc/test/archive/helper upload, PHP/configuration/Composer change, or new unresolved security blocker. No dependency rebuild and no reuse of the closed fileinfo exception; separate cleanup remains pending hosting. The scheduled retention command is unchanged after automatic review rejected that optional destructive expansion; manual test cleanup requires explicit administrator reference/checkbox confirmation. Staff acceptance in their usual signed-in sessions remains a follow-up because private kernel tests are not browser sign-in verification.

**Evidence/handoff:** WordPress reports/calling-letter-workspace-2026-10-26/{demo,production}/verification.json and browser/checks.json are private and excluded from Git/runtime payloads. Updated workflow and Thomas's ready-to-paste daily draft; no report filed externally. Original unrelated drafts remain in the named local stash in both repositories. Reviewed source/report publication follows normal fast-forward remotes, without rewriting history.

## 2026-10-26 — Prepare the calling-letter workspace release
**Area:** Reception navigation/manual issuance · demo-first runtime payload and acceptance
**Environments:** local verified; fresh demo preparation complete; no workspace deployment yet
**Commit(s):** Reception c977bea and this release tooling

The new workspace gives Calling letters its own sidebar page, source registers, private automatic search, guided review and direct administrator-only standalone issuance. Fresh demo sources were collected through paced normal Fileman reads. All 15 zero-fuzz patched runtime outputs equal the reviewed Reception commit; five existing files and ten new runtime files, including one additive manual-letter table, two public assets and branded views. Every demo/production destination passes the runtime allowlist. No document/report/test/archive or diagnostic helper upload, WordPress mutation, configuration/Composer/PHP change or production fixture is planned. The prior fileinfo exception remains closed; unchanged dependency cleanup stays separate.

**Verified/security review:** Reception 129 tests / 1,673 assertions and 50 local Brave desktop/mobile/logo/action/search checks pass, screenshots visually reviewed. The exact inline acceptance source passes 31 demo / 16 production checks against disposable local SQLite/mock PDFs. Demo acceptance will exercise real native PHP/MySQL and HTTPS rendering through private session/CSRF route dispatch, with manual issue/download/encryption/idempotence/replacement/cleanup, native guided issue, role boundaries and complete fixture rollback. Production acceptance is read-only: unsaved in-memory view actor, schema/routes and rendered source registers/create form, with no saved account, applicant, manual letter or issue/audit dispatch. Normal login and exact CSS/JS hash checks follow; no live negative probes. Runtime/file baselines and source/permissions/CSRF, validation/escaping, SQL/ID, encryption/download privacy, minimal pinned upstream data, duplicate/lock/version/failure boundaries reviewed before deployment. Unknown active roles now fail closed. Search is limited to the latest 5,000 when a term is entered and shows that limit; full unfiltered pagination remains available.

**Failure/operational handling:** fresh hashes and per-file rechecks refuse drift; source backups/journals remain private and attempts cannot be blindly replayed. Production requires successful demo receipt and identical final hashes. One-shot private cron executes inline helpers and is removed after completion; WAF/timeouts/rate limits stop requests. No scheduled deletion of manual letters was added: automatic approval review rejected that optional data-destruction expansion, so the existing retention scheduler is unchanged and cleanup requires explicit administrator confirmation. No unresolved new security blocker in the final scoped payload. Source docs stay in Git. Actual environment verification will be reported before final publication.

## 2026-10-26 — Complete demo and production calling-letter activation
**Area:** calling letters · read-only website sharing · release verification and handoff
**Environments:** demo enabled and verified; production enabled and verified
**Commit(s):** Reception 47e017e; WordPress 819ed94 / 7631319 and this deployment report

Reconciled the confirmed missing dependency without replaying or erasing the original failed attempt. Fresh matching baselines, guard-first uploads and all 14 final payload hashes verified; production matches the newly verified demo byte-for-byte. The other 12 files were not re-uploaded during reconciliation. Additive schema and both feature switches are ready. Read-only application sync processed seven demo and 28 production entries. No documentation, report, test, archive, diagnostic helper, PHP configuration or dependency payload was uploaded. Required private template assets were reused.

**Verified:** demo 15 native PHP/MySQL acceptance checks passed, including real HTTPS website download/render, administrator issue/download, encrypted storage, duplicate prevention, history, manager issuance denial and fixture rollback. These authenticated routes used private CLI kernel/session/CSRF dispatch, not an interactive browser session; normal HTTP homepage/login checks returned 200. Production five read-only checks passed for canonical source, enabled configuration, additive table, mirror match and existing website PDF/digest. Production homepage and Reception login both returned 200. No production test application, issued test letter, email/SMS or Mzizi replay. Both one-time setup crons removed. Synthetic demo output is 90,809 bytes, one-page A4; approved logo/stamp/signature layout already visually inspected. Private evidence is under reports/calling-letters-2026-10-26/deployment/{demo,production}/guard-reconciliation/verification.json and excluded from Git/runtime payload.

**Security/push review:** reviewed the actual feature/dependency and fresh server payload differences again. Existing role/CSRF/signature/nonce, validation/output, SQL/path/download confinement, exact outbound hosts, bounded PDFs, encryption/privacy, duplicate locks/limits and interrupted-worker disable handling remain intact. Reception full suite 112 tests / 1,505 assertions; WordPress 47 isolated checks, 28 signed-export/privacy checks, 202 runtime boundaries, packaging tests and ten config/failure fixtures pass. Outgoing changed tracked blobs scanned for high-confidence credential patterns (15 WordPress / 12 Reception files): none found; focused scan is not a universal secret guarantee. No unresolved new security blocker in this scope. Release source and reports reviewed before final publication; no additional server mutation is needed.

**Exception/follow-ups:** the user's explicit missing-fileinfo exception applied only to this urgent run and is now closed in the private authorization record; AGENTS rules are unchanged. Target fileinfo is still absent, PHP/dependencies unchanged, and separate 33-package development cleanup per Laravel installation remains pending hosting support and passing native platform checks. CLI patch evidence is not web-worker restart/build attestation. Staff acceptance in their own normal sessions remains a follow-up. Prior unrelated drafts remain in the named local stash in each repository. Thomas's ready-to-paste daily report and workflow notes updated; no report filed externally.

## 2026-10-26 — Make the calling-letter private-file dependency explicit
**Area:** calling-letter rollout · production acceptance reconciliation
**Environments:** demo previously verified; production feature automatically disabled after acceptance failure; correction not yet deployed
**Commit(s):** this correction and release reconciliation tooling

The first production attempt installed and verified all 13 code files, ran the additive migration and refreshed 28 read-only website applications. Existing-PDF acceptance failed before success was declared; its failure trap disabled the new feature on both systems and the temporary cron was removed. A private, read-only native WordPress diagnostic identified the missing toolkit_confined_private_file function. Fresh reads confirm production lacks private-file-boundary.php, while demo has it. No production test application, native letter, email or Mzizi replay was created.

The new exchange module now explicitly loads its private-file guard. Its standalone tests no longer preload that dependency, so they exercise the actual module-loading contract. A narrow reconciliation plan preserves original failed receipts, requires exact installed-source hashes and the identified failure, adds the guard before updating the module, and skips the other unchanged runtime files. The resulting 14-file set must pass actual demo acceptance before identical production promotion. All normal security gates and the user's one-run-only missing-fileinfo exception remain scoped as recorded; PHP and dependencies remain unchanged.

**Verified/security review:** 47 isolated authentication/path/rendering/privacy/cache checks and ten config-preservation/unsafe-state/worker-disable fixtures pass. Reviewed the actual require, private resolver and deployment diff: no broadened file roots, role/CSRF/signature permissions, outbound hosts or data disclosure. New payload is only the previously reviewed 702-byte runtime guard plus the explicit include; no report, README, test, archive or diagnostic helper is uploaded. Fresh baseline matching and guarded dependency-first ordering prevent overwriting unrelated changes or loading a nonexistent include. Actual corrected demo/production acceptance remains pending and will be recorded separately; original failure evidence is retained privately.

## 2026-10-26 — Verify demo calling letters and reconcile production cache hardening
**Area:** demo acceptance · production baseline security review
**Environments:** demo enabled and verified; production preparation only
**Commit(s):** this change and 2cdfb05 rollout tooling

Demo verified all 13 runtime hashes, additive schema and enabled switches on actual EA-PHP 8.4.27 with the explicitly excepted missing fileinfo. Fifteen private runtime checks passed: existing website PDF matches its mirror and downloads, administrator native issue/audit, encrypted PDF storage/download, duplicate prevention, history/manager role boundaries and complete rollback of synthetic fixture rows. The native one-page A4 PDF was visually inspected with the approved logo/stamp/signature. Normal demo website/login returned 200, the application sync processed seven entries, and the temporary setup cron was removed. No uploaded helper or test file; PHP and dependencies unchanged.

Fresh production preparation stopped before any mutation because its existing PDF cache unserialize lacked the allowed_classes=false guard already present in Git and verified demo. Added only that exact reconciled hardening line to the focused preparation, retaining the original baseline privately. All 13 production payload hashes now exactly match verified demo. Added an isolated serialized-object cache test: wakeup is not executed and the malformed cache is rebuilt as safe image data. This test is local only; no live poisoned-cache or attack request was used.

**Verified/security review:** 47 renderer/auth/path/privacy/object-cache checks pass, deployment config/failure fixtures pass, focused payload destinations/lint and identical demo hashes pass. Reviewed the actual one-line production reconciliation and tool/test diff again; no unresolved new security blocker for promotion. The missing platform requirement remains documented under the user's one-run exception, without dependency/engine modification or an ignored Composer check. Production verification will read existing PDFs/mirror/configuration and normal pages only; no production test applications. Proceed under the recorded exception, then close it after this run.

## 2026-10-26 — Authorise a scoped calling-letter rollout and prepare live-baseline payload
**Area:** urgent calling-letter deployment · one-run fileinfo exception
**Environments:** fresh demo preparation verified; deployment not yet performed
**Commit(s):** this release tooling and earlier 819ed94 / Reception 47e017e feature code

Fresh native EA-PHP 8.4.27 preflight confirms fileinfo remains absent; DOM/zlib/mbstring/OpenSSL/MySQL support is present and the installed approved template matches the locally reviewed DOCX hash. Inspection cron removed. The user explicitly instructed: “yeah we shall exclude that rule for this run only the calling letters and features done today are urgently followed up on for deployment and functionality”. Record this as a one-run exception to the missing-fileinfo deployment prerequisite only. Other security/privacy/permissions checks, demo-first promotion, runtime-only payload, no production test applications/Mzizi replays and stop-on-WAF/timeout remain in force. No permanent AGENTS relaxation, PHP binary/configuration switch or dependency change; the missing requirement remains recorded as failed and vendor cleanup is outside this exception.

Fetched the 13 exact demo code baselines through normal paced Fileman reads, prepared zero-fuzz focused patches, and verified all output bytes equal the previously reviewed feature commits. New tooling preserves live drift, refuses unknown existing new files/changed anchors, rechecks every destination before mutation, journals upload/cron intent and refuses blind reruns. Runtime payload remains four WordPress/nine Reception files, with no docs/tests/archive/helper upload. Private local source rollback and private 0700/0600 server configuration recovery are retained. The one-key .env switch preserves all other settings and original permissions, refuses links/ambiguous or writable config, and atomically replaces only the feature flag. Configuration cache is cleared, one additive migration runs, and only the approved feature switches are enabled.

**Security review / verified:** actual tool/helper diff and 13-file payload reviewed. Ten local config/private-backup/unsafe-state and worker success/failure rollback fixtures pass; both component patch sets reproduce pinned feature bytes and refuse conflicting new files/unmatched anchors; 46 real-renderer/auth/privacy checks still pass. Prior 112 Reception tests / 1,505 assertions remain applicable to unchanged application code. Helpers are executed inline through existing native CLI/one-attempt cron and are never uploaded. Failure trap disables the new feature on both sides; any uncertain attempt must be reconciled, not repeated. Request pacing and single-use worker guards retained.

Demo acceptance uses real native PHP/MySQL and a rolled-back synthetic fixture transaction to exercise administrator issuance/download, repeat idempotency, encrypted artifact/audit attribution and manager read-only/issuance denial through private route dispatch with CSRF/session handling; it does not send live negative HTTP probes. Production checks only existing records/configuration and normal pages, creating no production test application. A normal read-only application sync confirms today's mirror functionality. The prepared worker emits only safe metadata, with an optional synthetic demo PDF saved privately. Production promotion requires a verified demo receipt and identical 13-file payload hashes. Actual deployment/activation remains pending its receipt.

## 2026-10-26 — Prepare shared-template calling letters for Reception
**Area:** calling letters · read-only website integration · administrator issuance
**Environments:** local synthetic verification only; feature off; no hosting deployment
**Commit(s):** this feature change (review branch feat/reception-calling-letters)

The user confirmed administrators only may issue native Reception calling letters. Added protected on-demand website PDF downloads without application edits/regeneration, plus a per-native-applicant issue/download/history module. The website provides the existing approved renderer; native requests contain only reference/name/phone/course/intake and never create a website application, trigger Mzizi or send email/SMS. Explicit intake avoids stale defaults. Reception stores immutable encrypted PDFs with issuer/date/template revision/version; replacement confirmation and a ten-version cap preserve history. Applicant deletion/retention cascades encrypted artifacts. The new flags default off and existing application sync is unchanged.

**Verified/security review:** Reception 112 tests / 1,505 assertions (17 new letter tests); WordPress 46 isolated signature/path/privacy/actual-renderer/temporary-cleanup checks, 47 when using approved-template assets for the visually reviewed synthetic one-page A4 preview. Existing 28 export, 202 upload-boundary and runtime-package checks pass. PHP syntax/diff checks pass. Actual code/view diff reviewed for role/active/password checks, CSRF and throttle, single-use purpose/method/resource/body-bound HMAC, exact HTTPS sources/no redirects/no retry, bounded request/response/PDF bytes, source/reference/ID/digest validation, private path/symlink constraints, tempnam fallback rejection/0600/finally cleanup, print-length/page limits and no PII in errors/audit metadata. Native issue lock/keyed daily idempotency, unique version/input constraints, transaction rollback with audit, changed-contact row-lock check and cascading cleanup are exercised, including interrupted storage/retry. No upload endpoint, SQL interpolation from client values, new Composer dependency or public PDF URL. Runtime plan: 13 code/view files (4 WordPress, 9 Reception), all 26 demo/prod destinations allowlisted; no docs/tests/archive/template payload uploaded.

**Publication/release state:** completed earlier history pushed to WordPress wordpress-modernisation at 00c42f8 and Reception main at b1d1a99. Existing unrelated drafts remain in the named local stash recorded previously; they were not published. New module is source-only on review branches, not production-ready approval or a live deployment. Demo still requires fresh server/template baselines, actual target platform checks (including the known missing fileinfo prerequisite), additive migration, normal staff UI and synthetic demo issuance before promotion. Previously issued native PDFs work without the renderer, while new renders/website downloads need website availability; no website-PDF offline cache is claimed. Private preview/evidence remains outside Git/runtime payloads. No live security probes or production test applications used.

**Handoff:** workflow, tradeoffs, open later-phase options and activation gates are documented in the calling-letter design note; Thomas's daily draft is updated in WordPress. No report was filed through another person's account.

## 2026-10-26 — Review completed releases before remote publication
**Area:** Git publication · security review · preserved drafts
**Environments:** Git-only publication; existing verified deployment unchanged
**Commit(s):** this pre-push review and outgoing reviewed history

Fetched origin/wordpress-modernisation; no remote divergence. Reviewed the outgoing runtime/permission/download/sync diff and previous release evidence before pushing. 524 contact checks, 28 signed export/privacy, 8 artifact boundary, 30 upload/traversal, 35 graduands/permission, 202 deployment-path checks plus runtime packaging and 17 cleanup/reconciliation scenarios pass. The outgoing historical-blob scan found no high-confidence token/private-key patterns, known local hosting secret values or tracked secret configuration (WordPress 375 blobs; Reception 143). This focused scan is not a guarantee against every possible secret format. No reports/private evidence, vendor tree, credentials or data snapshot is added to the runtime payload; this push is Git publication only, not a server redeploy.

The user's clean-tree request is handled by preserving unrelated pre-existing report/transfer drafts in named local stash c85f130ba152231bb363fd081f3a90e6c100b1c6 (preserved-preexisting-drafts-before-calling-letters-2026-10-26), including untracked files. It is recoverable locally and is not pushed. No reset/clean or unrelated draft publication. Target missing fileinfo still blocks the separate vendor cleanup; no platform check bypass. Existing UI/read-only sync and runtime cleanup remain verified as previously recorded; PHP/web-worker attestation is still a hosting follow-up. No new production security blocker found in this reviewed publication scope.

**Next:** Calling-letter module design: website letters remain read-only; native Reception issuance is administrators only, as confirmed by the user. Reuse the approved website template and keep delivery/Mzizi outside generation. Implementation will be tested separately before any demo promotion.

## 2026-10-26 — Verify recovery cleanup and hand off the module prerequisite
**Area:** runtime file cleanup · deployment verification · daily reporting
**Environments:** WordPress/Reception normal checks verified; no new runtime uploads; vendors/PHP unchanged
**Commit(s):** this evidence/report update and 48a9fa5/f047eae tooling

Completed the reconciled absolute-path recovery move. All 882 files and 707 descendant directories match the original relative-path/type/size/mtime/mode metadata at toolkit-private-storage/platform-security-2026-10-16; root verified 0700 and both old/intermediate paths absent. This is metadata preservation, not a downloaded content/hash audit. File bytes total 128,026,614 by metadata. Removed the single exact unused CMB2 .mo.orig backup to reversible recovery trash after fresh loader/file checks; its old path is absent. Earlier 279 plus this one gives 280 obsolete active-code files removed. No recovery copy was erased; the 882 relocated files are counted separately. Downloads, licenses, actual plugin resources and bundled template ZIPs are retained.

**Verified/security review:** final additional-cleanup.json verified true; relocation signature/private-mode/absence checks passed; WordPress homepage and production Reception login 200, homepage release 2026.10.26.1. Existing demo/production Reception maintenance paths and production vendor metadata verified, both login 200, index uploads zero. Module-inspection cron removed. Seventeen isolated cleanup/index/reconciliation cases and prior dependency rollback fixtures passed. Actual final diff is evidence/reporting only: no new runtime payload, permissions/SQL/nonce/upload/relay change. No attack requests, application submissions, production dependency removal or PHP changes. Original user edits remain untouched and nothing pushed.

**Remaining:** actual target EA-PHP 8.4 fileinfo module absent; 33 development packages per Laravel installation remain pending hosting provisioning and successful demo platform checks. Laravel Tinker is additionally declared under production requirements and therefore remains outside this no-dev set; no custom app/routes usage was found, but removal needs separate dependency/usage review. Web-worker patch/restart attestation remains hosting work. Ready-to-paste Thomas daily report updated at docs/reporting/DAILY-DRAFT-2026-10-26.md; not submitted externally. Exact package plan: docs/RUNTIME-DEVELOPMENT-PACKAGES-2026-10-26.md.

## 2026-10-26 — Reconcile cPanel move semantics before finishing cleanup
**Area:** private recovery relocation · failure handling
**Environments:** historical recovery root restricted to 0700; runtime/business files unchanged
**Commit(s):** this change (see Git history)

The first recovery move returned success but interpreted a relative destination as a rename within Reception private storage. Post-move metadata verification stopped the operation before any obsolete-backup removal. Read-only reconciliation verified all 1,589 descendant entries (882 files / 707 directories) still match original relative paths/types/sizes/mtimes/modes at the exact receipt-reported intermediate location; root is 0700 and original/intended destinations are absent. No files were deleted and no application files or PHP settings changed. Existing demo/production maintenance paths and vendor metadata were independently verified, login pages 200, uploads zero.

Corrected the operation to an absolute full destination and require the API receipt to name it exactly. Added a one-use recovery path gated to this known failed receipt and independently recorded preflight, with fresh full metadata/private-mode/absence/cron checks and journal-before-move. It cannot replay an existing reconciliation or a later backup-removal action. The planned backup removal still requires live loader/metadata checks after successful relocation.

**Verified/security review:** reviewed actual path/lifecycle diff; 17 isolated cleanup/index/reconciliation cases pass, including wrong receipts, stale intermediate data, duplicate reconciliation and unexpected returned destinations. Syntax/diff checks pass. Exact fixed targets, no new execution path/upload/source document, no destructive business operation or admission relay; no same-commit unrelated user edits. Missing fileinfo still blocks vendor cleanup. Finish and page verification require actual final receipt; no completion inferred from accepted move.

## 2026-10-26 — Investigate missing module and scope remaining runtime cleanup
**Area:** target PHP requirements · runtime file policy · private recovery
**Environments:** read-only hosting evidence collected; cleanup prepared; deployed UI/sync unchanged
**Commit(s):** this change (see Git history)

At the user's request, listed the exact 33 Composer development packages selected per Reception/Campus installation (test/debug/development tools and their supporting libraries). Runtime versions remain unchanged. Read-only inspection of actual EA-PHP 8.4.27 finds a readable configured module directory but no matching fileinfo.so, no fileinfo INI and no built-in fileinfo without INI. No module was available to test-load; package queries are unavailable in the account. No PHP setting/version change, uploaded helper, platform bypass or new Composer attempt. Temporary inspection cron removed. Dependency cleanup remains blocked by hosting provisioning.

Fresh eight-root metadata inventory keeps licenses, real plugin resources and bundled template ZIPs. Reviewed the one unused .mo.orig translation backup against the exact live/local CMB2 loader and the existing .mo translation. The historical Reception security-recovery folder holds 882 files / 707 descendant directories, with no symlink/special entries or referencing cron. Its root and shared storage parent are 0755: prepared an exact reversible 0700 restriction on this recovery root only, then relocation outside the application; the shared parent is untouched. Before/after full metadata and absent source/private destination checks are required. The obsolete translation backup goes to reversible trash, never emptied. No business records/downloads/runtime inputs are selected.

**Verified/security review:** reviewed actual new tool/test/doc diff, fresh live index/loader/vendor baselines and exact mutation targets. Twelve isolated success/negative scenarios pass, as do the prior dependency rollback/index fixtures and Python/PHP/shell syntax checks. Initial read-only preflights stopped on already-correct production index, API root inclusion and the actual 0755 permissions; all were reconciled before any mutation, not blindly retried as deployments. Both Reception maintenance paths are already corrected: the follow-up only verifies hashes/pages, uploads nothing and leaves vendors untouched. Fixed authenticated API targets, fresh drift checks, no traversal/symlink selection, no secrets/document-content output, no new endpoint, no SQL/relay/business-data operation, bounded calls and journal-before-mutation; stop on timeout/WAF/rate-limit. No unresolved blocker for this narrow recovery cleanup; missing fileinfo remains a blocker for the distinct vendor removal. Runtime payload is empty. User's original dirty edits are preserved; no push.

**Follow-ups:** execute reviewed cleanup, record exact receipts/page results and refresh the daily report. Hosting must provision matching fileinfo before demo-first vendor cleanup. PHP web-worker restart/version attestation still needs hosting; no exploit probes used.

## 2026-10-26 — Complete PHP security assessment and release handoff
**Area:** PHP2Shell assessment · deployed UI/read-only sync · runtime file policy
**Environments:** demo/production UI and sync verified; active-code cleanup verified; PHP versions unchanged
**Commit(s):** this handoff and the earlier source changes (see Git history)

Confirmed configured PHP branches and installed interpreters: production WordPress EA-PHP 8.3.36; demo WordPress/Reception/Campus EA-PHP 8.4.27; internal cPanel CLI 8.4.27. These match official fixed upstream releases for the two main PHP2Shell CVEs. Installed WordPress is 7.1.3 on both environments; PHP 8.5.12 is latest stable but not installed/offered. Locked runtime constraints permit 8.5.12 for all 79 Reception/76 Campus packages; actual PHP 8.5 execution/theme/plugin testing was unavailable, so no production switch is approved. Current versions were left unchanged. Four ordinary web requests returned 200; WordPress cache misses report theme release 2026.10.26.1. Web responses omit PHP headers, so worker restart/loaded web-binary versions remain a hosting confirmation, not claimed proof. The assessment cron was removed; no public diagnostics or exploit probes used.

Main release receipts verify identical 19-file runtime payloads on demo and production, seven demo/28 production read-only website application copies, recurring five-minute refresh, removed setup jobs and normal release/CSS/login checks. Scoped reversible cleanup verified 279 files removed from active code folders, preserving downloads/licenses/runtime inputs. Private recovery holdings remain; server-wide minimality is not claimed. The Git tree retains exactly the original ten tracked dirty entries, including the user's 139-line WORKLOG additions/two blank deletions, plus original untracked reports/scripts; own changes are committed separately and no Git push was made.

**Remaining hosting prerequisite:** 33 development dependencies per Laravel installation remain because EA-PHP 8.4 lacks fileinfo. The rollback was independently verified byte-for-byte at dependency metadata, absent maintenance/temporary cron and normal demo login 200; temporary Composer was removed. ALT's selector flag does not enable native EA extensions. No alternate interpreter or ignored platform requirement was used to mask the failure. PHP's 23 October upload-progress guidance and worker restart should also be reviewed by hosting; custom source has no serializer switches, without claiming every third-party plugin audited.

**Verified/security review:** final local Reception suite 95 tests / 1,364 assertions; 202 upload-boundary, 28 export/auth/privacy, 35 graduands/permission, 18 desktop/mobile and 11 scheduler checks; disposable legacy-MySQL migration, runtime packaging/Composer production-manifest and rollback/tool-lifecycle fixtures pass. Isolated --no-dev copies passed five schema/boot/login checks per app without touching source vendors. Actual final diff is reporting/policy only; no new server payload, permission/data/SQL/upload/relay change. Updated AGENTS PHP guidance requires target-interpreter checks, exact patch evidence, demo compatibility and correct pre-request hardening. No unresolved blocker for the already verified UI/sync; dependency removal remains blocked, explicitly documented. One automatic approval review expired before a normal check started; its allowed single retry succeeded. All original user edits preserved.

**Handoff:** docs/PHP-RUNTIME-SECURITY-ASSESSMENT-2026-10-26.md contains the evidence, limits and ready-to-send hosting request; docs/TOOLKIT-ADMIN-RECEPTION-SYNC-2026-10-26.md and docs/RUNTIME-FILE-REVIEW-2026-10-26.md describe the delivered changes. Ready-to-paste Thomas daily report: docs/reporting/DAILY-DRAFT-2026-10-26.md; not filed externally. Local UI preview/profile and temporary cPanel authentication/page/resource snapshots were removed. Private receipts remain under reports/ and are excluded from deployment.

## 2026-10-26 — Verify demo rollback and prepare PHP2Shell version assessment
**Area:** runtime dependency safety · PHP hosting security
**Environments:** UI/sync and 279-file cleanup verified; dependency cleanup blocked before promotion
**Commit(s):** this change (see Git history)

Composer refused demo's PHP 8.4 platform because ext-fileinfo is not enabled. Verified exact original dependency metadata restored, both maintenance files absent, temporary Composer removed, temporary cron absent and normal demo login 200. Production/Campus dependencies were not changed. Added an early fileinfo precondition before backup/maintenance and a narrowly gated resume path requiring the known failure plus verified rollback; no platform-check bypass or blind replay. Local rollback/early-fileinfo fixtures pass.

At the user's request, checked extension controls through existing cPanel APIs and its linked PHP Selector implementation. The applications use EA-PHP 8.4 (production WordPress uses EA-PHP 8.3); the selected selector version is native (8.4), whose extension controls are disabled. ALT-PHP 8.4 already has fileinfo enabled, which does not prove EA-PHP has it. No extension or PHP-version setting was changed.

The user additionally requested latest-PHP safety assessment for PHP2Shell. Official PHP/cPanel/CloudLinux advisories confirm fixes exist within current supported branches and vendor backports require package-build evidence, not version-string assumptions. Added a read-only one-attempt existing-CLI assessment of exact PHP patch/module/configuration facts and specific vendor package/CVE changelog entries, plus installed WordPress version reads. No public phpinfo, uploaded execution helper, exploit requests, app changes, package updates or credentials are involved. Syntax/no-upgrade boundaries pass; actual assessment results and PHP 8.5 compatibility remain pending. Review covers fixed paths/commands, private minimal outputs, bounded polling, journal-before-cron, single-attempt marker and cron removal; stop on timeout/WAF/rate limit. Sources: PHP archive 2026-10-21/23, cPanel Critical PHP Security Release and CloudLinux PHP package-status advisory.

## 2026-10-26 — Handle hosting without a Composer installation
**Area:** safe runtime-dependency cleanup tooling
**Environments:** demo maintenance-path repair verified; vendor state unchanged; production untouched by dependency cleanup
**Commit(s):** this change (see Git history)

Demo's first dependency worker reported COMPOSER_NOT_AVAILABLE and exited 42 before backup, maintenance or dependency changes. Its temporary cron was removed; the exact public early-maintenance path repair remains verified. The locally installed Composer 2.9.7 PHAR used by both successful isolated preparations is now an explicit temporary deployment-tool option: fixed private nonce path, SHA256 pinned to a recorded 3,377,213-byte binary with PHAR SHA512 integrity, hash check before execution, networking/plugins/scripts disabled and unconditional tool removal after worker completion. It is the unmodified dependency manager, not an uploaded inventory helper.

**Verified/security review:** local syntax and shell tests pass, including idempotent exact index repair, missing/ambiguous anchors, unchanged runtime files, Composer failure restoring vendor/cache and clearing maintenance, unavailable Composer, existing-backup refusal and bad tool checksum. Added worker fixtures confirm tool removal on success/failure and single-attempt marker suppression. Upload/cron intents are journalled before uncertain network operations, a pending temporary-tool attempt cannot be blindly replayed, and any hosting timeout stops further requests. Reviewed the actual tool/lifecycle diff and fixed targets; no package upgrades, project-document/archive payload, business-data deletion, source rewrite, secret output, external download or production test application. Only the known no-Composer demo failure is eligible for the explicit reconciliation path; demo must verify before production/Campus.

## 2026-10-26 — Prepare runtime-only Laravel dependencies with rollback
**Area:** server runtime policy · safe dependency maintenance
**Environments:** UI/sync verified demo and production; vendor cleanup prepared locally
**Commit(s):** this change (see Git history)

Verified the completed UI/read-only sync release on both environments: 19 matching runtime files each, seven demo and 28 production website applications mirrored, setup jobs removed and recurring refresh configured. Reversible cleanup removed 279 reviewed files from active code directories (52 demo; 227 production/Campus), with homepage/inventory receipts. Private recovery holdings and genuine runtime plugin resources are explicitly retained rather than described as absent.

Final normal inventory reads confirmed 33 development dependencies in each Reception/Campus installation. A later ordinary availability check timed out after metadata reads; stopped all requests. The user subsequently confirmed restored access and authorised proceeding; one normal front-controller read succeeded. Prepared isolated Composer --no-dev copies with networking/plugins/scripts disabled: Reception 112→79 and Campus 109→76, no runtime version changes. All five local migration/discovery/routes/environment/login checks pass for each; real working vendors unchanged.

Hardened runtime packaging to reject development/unknown Composer manifests and manifest symlinks; reproducible package boundary tests pass. The demo separated front controller retains a legacy early-maintenance path to the wrong storage directory. Added an exact anchored repair and an existing-tool Composer deployment command with private rollback, pre-rendered maintenance, offline locked removal only, provider-cache rebuild, failure restoration and one-attempt cron cleanup. No uploaded executable helper, archive, project document or credential is needed.

**Security/flaw review:** reviewed actual diff/operation for fixed targets/paths/commands, fresh metadata/root-lock checks, original maintenance-state preservation, no dependency upgrades/network/plugins/scripts, private 0700 rollback and 0600 logging, no business database/files/source changes, immutable native versions, bounded worker/polling, automatic failure restoration/unmaintenance, exact cron removal and normal login verification. Local shell fixtures cover success, Composer failure with rollback, missing Composer and an existing backup; maintenance clears and runtime fixture files survive. Exact index repair rejects missing/ambiguous anchors. Current UI and sync remain live; dependency cleanup requires demo verification before production/Campus. No known release security blocker; private holdings need a separate retention lifecycle. Existing user edits preserved; no Git push.

## 2026-10-26 — Correct vendor-document metadata search coverage
**Area:** scoped server cleanup preflight
**Environments:** no cleanup mutations yet; demo runtime release verified
**Commit(s):** this change (see Git history)

Cleanup stopped before any mutation because its filtered inventory omitted five vendor README/changelog TXT basenames. A read-only comparison confirmed the search-pattern coverage issue; no changed-file baseline was accepted or cleanup attempt reset. Corrected the pattern to match both basenames and full paths and retain a private preflight metadata snapshot.

**Verified/security review:** ten positive basename/full-path cases and three unrelated runtime-name negatives pass locally. The exact 279-file cleanup plan and all path/license/download/business-storage protections are unchanged. Reviewed the two-line execution-boundary diff; preflight still checks every file type/size/mtime before reversible private trash, with no blanket deletion or archive/helper upload. Actual cleanup remains pending its receipt; demo release remains verified and production promotion still uses its exact hashes.

## 2026-10-26 — Verify actual scheduler availability and exact demo promotion
**Area:** read-only sync rollout orchestration
**Environments:** demo migration/first sync verified; public release checks pending; production unchanged
**Commit(s):** this change (see Git history)

The corrected demo migration and first read-only import succeeded (seven demo entries), and the temporary setup cron was removed. A read-only cron inventory confirmed demo has no Reception scheduler; production has an existing every-minute scheduler using a working-directory command. The release now recognises both established command formats and provisions only a dedicated five-minute read-only sync job when no effective scheduler exists. An explicit finish mode rechecks all 19 deployed files and the successful setup receipt without re-running migration or import. Production additionally requires all payload hashes to match the verified demo receipt.

**Verified/security review:** 11 isolated scheduler selection/scope/idempotency/cadence checks pass; the real production command format is recognised from the private snapshot. Reviewed actual orchestration diff, exact cron commands/cadence, no unrelated cron edits or dormant-job activation, duplicate avoidance, no blind setup replay and failure/timeout stop behaviour. Ordinary public checks now require the release header/asset version, exact admin CSS and absence of fatal-error markers. Application payload unchanged from the corrected reviewed 19-file set; no docs/helper/secret uploads or production test applications. No known security blocker; finish demo verification before promotion.

## 2026-10-26 — Reconcile demo database setup before promotion
**Area:** demo-first rollout verification
**Environments:** demo files uploaded; setup stopped; production unchanged
**Commit(s):** this change (see Git history)

Demo verification caught MySQL error 1067 while creating the new mirror table: the host's legacy implicit TIMESTAMP defaults reject a second required timestamp. The temporary setup cron was removed. Reception now uses DATETIME for its four source/sync dates, matching existing migrations. Added a narrowly gated reconciliation path: require that exact failure receipt, verify every uploaded/pending file against its expected hash, replace only the corrected migration, then perform a fresh one-attempt additive setup. No original attempt is silently reset or blindly replayed.

**Verified/security review:** reproduced error 1067 in disposable socket-only MariaDB with legacy timestamp defaults/strict zero-date rules, then ran the real corrected Laravel migration successfully and verified all four DATETIME columns and zero application rows. The temporary database/process were removed. Final Reception suite: 95 tests / 1,364 assertions; focused schema-fix sync rerun: 13 / 113. Reviewed the exact runner/schema diff, immutable failed-migration baseline, demo-only resume gate, cron cleanup, no destructive schema/data operation and unchanged runtime allowlist. No production test submission or unresolved security blocker. Reconciled demo and production verification still pending their receipts.

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
