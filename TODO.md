# Feature Follow-up Todo

## Completed in this branch

* Create `features.md` with the module expansion proposal.
* Add a glass sidebar application shell and responsive navigation.
* Redesign the dashboard into an executive operations overview.
* Add role-aware dashboard KPIs for reports, pending reviews, approvals, departments, staff, admissions proxy activity, and marketing activity.
* Add module preview surfaces for Admissions, Monthly Intake, Incentives, Minutes, Notifications, and Analytics.
* Add an Operations Modules page for the proposed expansion areas.
* Add an admissions record capture and verification workflow with scoped access,
  decision notes, notifications, and immutable status history.
* Add monthly intake targets with officer scope, verified-admission actuals,
  remaining totals, progress percentages and target notifications.
* Add meeting minutes with responsible action owners, due dates, status updates,
  blocked explanations and completion timestamps.
* Fix inactivity-lock reactivation so department admins can unlock their own
  employees with a reason and role changes cannot bypass a lock.
* Document the proposed Report System to Wingu Box integration without storing
  or sharing credentials.
* Add incentives proposals with server-side calculations, independent approval,
  payment references and audit events.
* Add report filters and true XLSX export alongside CSV export.
* Add configurable in-app reminder rules, idempotent delivery logs and a
  scheduler-safe `flask reminders-run` command.
* Apply owner policy decisions: fee-paid intake actuals and 14-day inactivity
  locking.
* Add role-scoped OKR objectives, measurable key results, owners, lifecycle
  states, calculated progress and append-only evidence updates.
* Add an approval-gated Wingu queue with manual/Excel attendance provenance,
  duplicate prevention and append-only reconciliation events.
* Add an idempotent loader for all eight Toolkit portfolio draft objectives and
  thirty proposed key results, including brand/poster outcomes.
* Add a canonical attendance workbook download, preview-only validation and
  exact approved-report Excel import.

## Next implementation work

* Add email delivery after an authenticated provider is approved.
* Add role-specific dashboards for Principal, Administrator, Department Head, and Employee once those roles are mapped to the current role model.
* Add browser-assisted Wingu dispatch after the authenticated review session,
  attendance-sheet format and rejected-row owner are supplied.
* Obtain owner approval or requested changes for the canonical attendance Excel
  columns and formats.
* Add automated tests for remaining dashboard analytics and the external Wingu
  browser dispatcher once its authenticated field map is approved.
* Add seed/demo data for showcasing the new module dashboards during stakeholder review.
