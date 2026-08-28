# Worklog — Toolkit Report System

Newest first. One entry per meaningful change, written in the same commit as the
change itself. Format and rules: see `../AGENTS.md` section 1.

---

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
