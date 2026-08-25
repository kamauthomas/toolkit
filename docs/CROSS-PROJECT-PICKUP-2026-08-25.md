# Toolkit cross-project pickup checkpoint

**Checkpoint date:** 25 August 2026  
**WordPress branch:** `wordpress-modernisation`  
**WordPress head:** `308cdf9` before this documentation update  
**Virtual Campus head:** `5c26272` on local `main`

This checkpoint reconciles the public WordPress site, Reception, the Smart Lecturer presentation prototype, and the independent Laravel Virtual Campus. It distinguishes verified production work from local-only work and owner-controlled launch gates.

## Current state

| System | Complete and verified | Blank, disabled, or not working | Exact pickup point | Goal not yet achieved |
|---|---|---|---|---|
| WordPress public site | Production release `2026.08.24.5`; approved 15-course catalogue, distinct images and fees; applicant-encryption code deployed; all 16 eligible production applicant rows migrated to the current key with zero failures/conflicts | Toolkit Analytics figures have not been independently audited; authenticated applicant-detail viewing still needs a normal admin acceptance check; unified deployment CLI remains unfinished | Audit metrics collection versus consent, cache, filters, page coverage and server traffic; then perform the authorised applicant-detail/export/calling-letter acceptance without exposing data | Trustworthy analytics evidence and closure of the normal authenticated admissions acceptance check |
| Windows Toolkit workspace | Organised, secret-free `Toolkit_Workspace` and repeatable sync process exist | The mirror has not been refreshed through the latest WordPress/Reception commits | Run the existing controlled sync only when the NTFS destination is reliably writable, then re-run manifest and prohibited-path checks | Current, repeatable Windows working mirror |
| Reception | Fresh production application at `reception.toolkitafrica.ac.ke`; WordPress relay, QR entry points, office administrator identity, database, PHP 8.4 and scheduler configuration; public routes return HTTP 200 | Labelled acceptance record `WEB-260825-JBJXCB` remains pending deletion because new cPanel cron jobs did not execute and private SSH is unavailable; first-login password rotation and a retention-job execution check remain | Delete the exact labelled record through authenticated staff administration or a restored private CLI path; rotate the initial password; verify an actual scheduler run | Operational handoff with no test record, rotated credentials and evidenced retention execution |
| Smart Lecturer prototype | Controlled grounded answers, captions, local speech, licensed Meshy model and browser controller are implemented | Production RAG/LMS connection, final facial rig acceptance, approved production voice, three-person human-likeness review and final screenshots remain open | Resume from the parent repository's `docs/PROGRESS.md`; finish the Meshy rig/acceptance evidence before connecting it to real campus content | A bounded, approved lecturer pilot for one or two real modules |
| Laravel Virtual Campus | 54 tests / 211 assertions; role and finance access, authoring, verified video progress, assessments, records, support, notifications, LiveKit tokens and signed attendance ingestion are implemented locally | Not deployed; no remote is configured; real catalogue/rosters/policies are absent; public registration, certificates, recording and live rooms are gated; mail recovery, MFA, production database/restore, edge controls, external audit retention and payment integration remain unaccepted; live captions remain disabled | Extend the learner-first UI to lessons, assignments, live classes and support at desktop/mobile/tablet; re-run the full suite; then establish remote/staging and execute the production acceptance list using owner-approved data and policies | A production-operated Virtual Campus with real learners, reliable video lessons, interactive LiveKit classes, captions, recovery and retained acceptance evidence |

## Recommended order on return

1. Finish the Virtual Campus learner-facing UI pass across the remaining core journeys.
2. Re-run tests, production build and authenticated browser journeys at phone, tablet and desktop widths.
3. Resolve the Git remote and deploy an isolated staging environment; do not deploy seeded local accounts or SQLite.
4. Supply and record the owner decisions in `virtual-campus/docs/OWNER_INPUT_TRACKER.md`, beginning with production topology, real academic data, assessment policy, privacy, mail, recording and live captions.
5. Run multi-user LiveKit, webhook, TURN/restrictive-network, mobile-data, caption, outage and load acceptance before enabling live rooms.
6. Return to the public-site analytics audit and the small authenticated operations checks without reopening the completed encryption migration.

## Safety boundary

- Do not copy `.env` files, provider keys, database data, applicant information or production configuration into Git, reports or the Windows mirror.
- Do not describe the local Virtual Campus as production-ready until its owner gates and staging/production acceptance evidence are complete.
- Keep Reception cleanup private. Do not publish a one-time maintenance runner merely to remove a test row.
