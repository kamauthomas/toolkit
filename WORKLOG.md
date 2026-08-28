# Worklog — Toolkit Report System

Newest first. One entry per meaningful change, written in the same commit as the
change itself. Format and rules: see `../AGENTS.md` section 1.

---

## 2026-08-28 — Operationalise intake, meetings and account unlocking
**Area:** admissions · targets · meetings · authentication · Wingu planning
**Environments:** local verification; not deployed
**Commit(s):** this commit

Implemented monthly intake targets backed by verified admission records and a
meeting repository with accountable action owners, deadlines, status updates
and notifications. Fixed the five-day inactivity reactivation defect: locks are
now enforced independently of role, department admins can unlock their own
employees through a reasoned action, and promotion no longer bypasses a lock.

Added a direct owner-input tracker and a credential-free Wingu Box integration
design. The design recommends an approved API/SSO path, server-side identity
mapping, idempotent queued dispatch after report approval, authoritative
attendance times and visible reconciliation; it does not connect to Wingu or
store credentials.

**Verified by:** Python compilation, full automated suite and authenticated
route/render checks for targets, meeting actions and unlock behaviour.
**Follow-ups:** review on demo; provide RPT-001–RPT-012 owner decisions; obtain
Wingu vendor documentation/sandbox before integration work.

## 2026-08-28 — Implement admissions capture and verification workflow
**Area:** admissions operations · reporting
**Environments:** local verification; not deployed
**Commit(s):** this commit

Added a scoped admissions record module. Authorised staff can capture an
applicant follow-up, view records within their role scope, and open a detail
page with the full verification history. Administrators can mark records
verified, rejected, or needing more information; explanatory notes are required
for negative/incomplete outcomes, and the record owner is notified when another
reviewer changes the status.

**Verified by:** automated tests cover capture, pending history, reason-required
rejection and verified decision history; the existing suite remains green.
**Follow-ups:** add intake targets, minutes/action items, incentives and
notification delivery logs as separate approved modules; review this module on
demo before any hosting change.

## 2026-08-21 — Add a plain-language staff quick guide
**Area:** reports · staff guidance
**Environments:** documentation only; not deployed
**Commit(s):** this commit

Added a short guide explaining why daily reports matter, how staff file a report,
the review states and role boundaries, and what a useful task description looks
like. It complements the detailed system and roles overview with a practical
one-page reference.

**Verified by:** checked against the current overview, roles, report fields, draft
workflow, and Submitted → Reviewed → Approved status flow.
**Follow-ups:** staff may adapt the guide into onboarding material or an in-app
help page after stakeholder review.
