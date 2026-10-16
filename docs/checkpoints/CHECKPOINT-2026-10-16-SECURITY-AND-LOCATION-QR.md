# Checkpoint — security containment, hosting lockout and location QR

Prepared for Thomas · 2026-10-16T16:51:19.404333+03:00

**REMOTE WORK IS PAUSED AT THE USER’S REQUEST. No more probes, retries, IP switches or deployments until the user resumes.** This checkpoint is local/Git material and must not be uploaded to a server.

## Verified deployment state

| System | State at pause | Evidence |
|---|---|---|
| Website demo | Runtime hardening .2 and private-artifact boundary **2026.10.16.3** verified | `hardening-demo.json`, `private-boundary-demo.json` |
| Website production | Runtime hardening **2026.10.16.2** verified. **.3 is NOT deployed** | `hardening-production.json` |
| Reporting production | Obsolete public source backup moved privately; denial broadened; PDF confinement helper and focused app patch verified | `report-source-containment.json`, `reporting-deployment.json` |
| Reporting demo | No dedicated domain found in cPanel; downloaded deployed code exercised in isolated synthetic SQLite staging | `reporting-isolated-verification.json` |
| Reception demo/live | Web-root backup/document protections and private PHP logging configuration verified; documentation moved out of app trees | hardening/cleanup manifests |
| Virtual Campus test | `campus-test.toolkitafrica.ac.ke`, Laravel public root; web-root protection/logging and ordinary login checked | `hardening-demo.json`, `domains.json` |
| Chat-bot | **Source-only fixes; NOT deployed.** No verified remote host identified | source commit `c27e3c6` and local test log |

The parent Eduma theme remains **5.3.0**. The installed updater had no active purchase token or package; the local ZIP was also 5.3.0. The user chose to retain it and use alternatives. Do not claim that the parent theme was upgraded or all vendor advisories were resolved.

Local `functions.php` now says .3 because the checked-in PHP includes the demo-only private-file change. Production remains .2. Never infer the production version from the working copy.

## Completed work

- Earlier image investigation: supplied `courses/digital.jpg` and all 18 staff portrait files matched tracked copies, with July metadata. Tampering claim remains unverified. About Us now uses the campus exterior; 14 displayed staff portraits remained. This earlier work is commit `4840fa7` and release .1.
- Confirmed current exposures: public old theme installer ZIP; publicly downloadable `app.py.bak-pre-security-20260818` on Reporting. The active `app.py` was already denied; backup variants bypassed its rule. The obsolete source copy is now outside the public root, and verified later returned 403.
- Moved **834 files** from active trees into private temporary holding: **432 demo / 402 production**, about **126 MB**. This includes README/development documents, old installer ZIPs and public-location PHP logs. Approved public downloads, required licenses, the private calling-letter template, generated business records and databases were preserved. The tested error-log URLs were already 403 before their files were moved; do not call them demonstrated public log leaks.
- Website hardening .2: must-use request-path and upload validation, unsafe active file types blocked, raster contents checked against extensions, browser source editor disabled, legacy GET-triggered migration blocked, upload execution denial and private error logging configuration. The harmless demo PHP canary returned **406 without its marker**; this demonstrates a host-level denial, not independent proof that Apache alone supplied it.
- Demo-only .3: private calling-letter files must match the expected application artifact, permitted site/legacy directory and real file; symlinks and cross-site/wrong files rejected. Image-cache unserialization disallows objects. Production promotion is outstanding.
- Reporting PDF paths: reject tainted metadata paths and symlinks for downloads/deletion cleanup. Local source is much newer than production; the live app received only the focused patch from its fetched baseline. Source commit `cd5585e` preserves the local newer features.
- Chat-bot source-only: administrative UI/list/update/delete/metrics require configured authentication; missing password fails closed, health omits the backend address/session totals, generated API documentation is disabled, bot HTML is escaped, Docker ports bind to loopback. Live destination, HTTPS/private secret provisioning and model-tool permissions still need review. No remote Chat-bot service was contacted.
- Deployment hygiene: explicit runtime packager profiles and upload-path guard added; archives were built/tested locally, not uploaded. Campus's initial bootstrap now calls the packager. **Do not rerun that bootstrap on existing data: it resets its test database.** Older shell deployment scripts are not all protected by the new Python uploader guard.
- Credential literals in an ignored historical local Reporting deployment note were redacted without printing them. Git history was not rewritten and credential validity/rotation was not tested.

## Archive findings

26 archive metadata inspections: **20 complete**, **five partial** under the 80 MB / 12-second per-tar bound, **one ZIP unreadable**. No archive was extracted or executed. Large account backups include database dumps. Old Softaculous/legacy-site copies include configuration, source and documentation. Some old plugin-installer archives are required/unused depending on their setup flow; do not restore quarantined packages casually.

Old account backups and the August security quarantine remain private. This work did **not** prove that every server archive contains only needed data, erase historical backups, or complete an encrypted off-host retention plan. No documents should be copied back into runtime trees to undo the cleanup.

## Live audit limit and network state

The review saved **26 observations**, mainly the two WordPress hosts and the start of Reception demo. Findings after containment showed no source/environment/Git/trace markers in those sampled responses. This does not cover all systems or prove absence of vulnerabilities.

One request to a private Reception demo route timed out; ordinary homepage and cPanel attempts later also timed out. External page retrieval returned content, but that is not proof that all services were healthy. The host's WAF/IP restriction is suspected; no firewall log was available to establish the precise cause. The four-second pacing was insufficient for attack-shaped requests; the operator acknowledged this and stopped. The failed Reception route inference from a shortcut login form must not be reused—inspect the real configured prefix privately rather than guess it.

An authorized switch to the saved “The Toolkit Hub” Wi-Fi connected, but the one normal homepage request still timed out. The script restored **HR**. A different public exit IP was not verified. There were no configured Tailscale exit nodes. Do not reconnect networks or install/use an unknown proxy on resume without the user's direction.

The archived negative-probe script is disabled by default. **Do not pass its override flag and replay it.** Remaining negative cases belong in isolated local/private staging. The incomplete live results must not be represented as a finished black-box audit of Reporting, Reception production or Campus.

## Resume order

1. Wait for the user to restore access/resume. Make at most one ordinary availability check first; do not start with a security payload.
2. Inspect actual live files/releases before any mutation. Confirm previous helper jobs completed and remove any remaining incident cron entries; each completed runner already removed its own job, but the final independent recount was blocked.
3. Preserve necessary evidence off-host and then remove temporary server audit material. **834 removed files are still held privately on the server**, not permanently deleted/offloaded. Temporary CLI helpers/manifests, rollback copies, and the harmless demo upload canary also remain. The canary is `wp-content/uploads/toolkit-hardening-canary-20261016.php`. Remove it without requesting it again. Do not delete private business data or the last necessary recovery copy.
4. Decide whether to promote demo-only private-file boundary .3 using its recorded baseline and verified demo. Do not overwrite entire server files from local Git; deployed source has drifted.
5. Finish remaining assurance with isolated deployed-source tests. Any later live review needs a much smaller scope and hosting coordination. Do not rotate IPs to continue attack payloads.
6. Proceed with the location QR below when server access permits. No additional server changes are authorized merely by reading this checkpoint while paused.

## Next requested feature — location QR (pending)

- Toolkit campus location already published on Contact: **-1.2704916, 36.6644851**, No. 7219 Karen–Kikuyu Highway, Thogoto, Kikuyu.
- Existing target: `https://www.google.com/maps/search/?api=1&query=-1.2704916%2C36.6644851`.
- User's meaning of “unique”: a **location pin icon in the centre** of the QR. Preserve the quiet zone, use high error correction, export PNG/SVG and independently decode the finished icon-bearing artwork at practical print/preview sizes.
- Proposed stable first-party redirect records openings before sending people to Maps; it must not be cached in a way that bypasses counting. Use a fixed approved destination, not an arbitrary redirect URL.
- Add artwork/downloads and administrator-only records/control to existing Toolkit Control. Keep reports local/Git; deploy only runtime code and QR assets.
- Count actual destination openings, with clear handling of repeat visits, bots/previews and HEAD/prefetch requests. Camera decoding without opening cannot be measured; counts cannot prove unique people or physical visits. Do not store raw IPs or unnecessary personal data for these records.
- **No QR artwork, route, scan database or admin control has been implemented/deployed yet.** The user paused server work and requested this checkpoint first.

## Verification and Git

Passed locally: 143 upload-policy checks; 30 WordPress request/upload checks; eight private-artifact checks; 13 Reporting filename checks; 10 staged deployed-Reporting download checks; 14 existing isolated Reporting security/auth tests; 12 Chat-bot dependency/authentication/privacy checks. Runtime archives built for WordPress, Reporting, Reception and Campus; an adversarial packaging fixture passed. The Chat-bot TestClient attempt hung and was stopped; the passing 12 checks exercised the actual dependency functions and route registration, not full browser dispatch.

Reporting commit: `cd5585e`. Chat-bot commit: `c27e3c6`. WordPress: this checkpoint commit/tag; see Git history.

**The project trees are not generally clean.** Preserve pre-existing document/report edits, deleted files, transfer-script changes and untracked material. Do not stash, reset, clean or bulk-commit them. Own checkpoint changes are committed separately with the appropriate worklogs. Raw evidence, logs and inventories are local ignored files under `reports/platform-security-2026-10-16/` and `reports/asset-incident-2026-10-16/`; never upload these directories.
