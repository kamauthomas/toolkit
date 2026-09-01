# Worklog — Toolkit Report System

Newest first. One entry per meaningful change, written in the same commit as the
change itself. Format and rules: see `../AGENTS.md` section 1.

---

## 2026-09-01 — Align the OKR register with the finalized portfolio
**Area:** OKRs · governance · owner assignment
**Environments:** local verification; not deployed
**Commit(s):** this commit

Updated the OKR loader and management copy after approval of the eight-objective,
thirty-key-result portfolio. The targets are now described as finalized while
records remain safely in Draft until named users are assigned; the owner tracker
now separates the completed target decision from the outstanding accountability
assignment.

**Verified by:** full automated Report System suite and an idempotent portfolio
loader check.
**Follow-ups:** management assigns named owners and confirms denominators before
activating objectives.

## 2026-09-01 — Add credential-safe Wingu field discovery
**Area:** Wingu · isolated browser operations · security
**Environments:** local fixture verification; no Wingu submission
**Commit(s):** this commit

Added a managed temporary Brave session and a discovery-only CDP tool for the
remaining Wingu field-mapping step. It binds only to localhost, uses a separate
profile, refuses non-temporary output, requires exactly one Wingu tab, strips
query strings and inventories form names/labels/options without reading input
values, cookies, storage, responses or screenshots. Stop validates the exact
temporary profile before terminating and deleting it.

**Verified by:** Python compilation, Bash syntax, the full Report System test
suite, and a real headless-Brave fixture containing dummy time, notes and
password values. The form structure was discovered and none of those values
appeared in output; the temporary profile and JSON were removed.
**Follow-ups:** RPT-014 requires the employee to sign into the isolated window
and open Edit Time Sheet. Use that field map to implement and verify dry-run,
submission and reload reconciliation; do not guess selectors.

## 2026-08-31 — Load portfolio drafts and validate Excel attendance
**Area:** OKRs · posters/communications · attendance · Wingu
**Environments:** local verification; not deployed
**Commit(s):** this commit

Added an explicit, idempotent loader for the prepared Toolkit portfolio: eight
draft objectives and thirty proposed key results covering the website,
Reception, Virtual Campus, Smart Lecturer, reporting/Wingu, security, the
brand/poster library and delivery governance. Records remain drafts and
unassigned until management approval. Activation is blocked until the objective
and every key result have accountable owners and the proposed targets have been
moved out of Draft.

Added a generated Excel attendance template, preview-only row validation and a
controlled import that matches exactly one approved report by staff email and
date. Valid rows retain their workbook/row source reference and enter the Wingu
queue without a guessed project; invalid, duplicate or out-of-scope rows fail
closed.

**Verified by:** Python compilation and 52 automated tests, including portfolio
completeness/idempotency, a real XLSX download, non-mutating preview and approved
Excel queue import.
**Follow-ups:** owner responses RPT-010, RPT-013, RPT-014 and RPT-015; browser
dispatch remains disabled until authenticated Wingu field discovery.

## 2026-08-31 — Add portfolio OKR tracking and approved Wingu queue
**Area:** strategy · reporting · Wingu · auditability
**Environments:** local verification; not deployed
**Commit(s):** this commit

Implemented role-scoped objectives and measurable key results with assigned
owners, dates, lifecycle states, server-calculated progress and append-only
evidence updates. The workflow can track evidence from every Toolkit project,
including campaign and poster deliverables, while the portfolio OKR document
remains the draft management source until owners and targets are approved.

Implemented the internal Wingu handoff: only approved reports can be queued,
attendance provenance is explicit (manual or approved Excel reference), repeat
queue rows are prevented, Wingu project selection remains unset until read from
Wingu itself, and reconciliation changes create audit events. No external Wingu
submission or credential/session storage was introduced.

**Verified by:** Python compilation, 49 automated tests, authenticated rendering
of `/okrs` and `/wingu`, and local health checks.
**Follow-ups:** management assigns/approves OKR owners and targets (RPT-015);
provide the attendance spreadsheet format (RPT-013), rejected-row owner
(RPT-010), and one human-authenticated Wingu discovery session (RPT-014) before
the external dispatcher is connected.

## 2026-08-31 — Apply intake, Wingu and reporting policy decisions
**Area:** admissions · authentication · incentives · exports · reminders · Wingu
**Environments:** local verification; not deployed
**Commit(s):** this commit

Applied the owner's decisions: intake actuals now require a verified, fee-paid
enrolment; employee inactivity locks occur after 14 days; and Wingu project
selection is always read from Wingu's own project list. Incentive proposals,
report filters/XLSX export, and configurable in-app reminder rules are now
operational, with audit/delivery records and no outbound Wingu/email writes.

**Verified by:** 45 automated tests passed after the policy update; the new
fee-payment, intake, export, incentive, reminder and lock routes are covered by
authenticated tests. The Wingu procedure was reconciled to the same dynamic
project-selection rule.
**Follow-ups:** review locally; provide the approved attendance-sheet format,
rejected-row owner and authenticated Wingu review session before browser
dispatch work.

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
design. The approved direction is an isolated authenticated-browser bridge with
Wingu-provided project selection, idempotent queued dispatch after report
approval, Excel/manual attendance times and visible reconciliation; it does not
connect to Wingu or store credentials.

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
