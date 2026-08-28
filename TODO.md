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

## Next implementation work

* Create normalized database tables for incentives, notification rules, and
  notification delivery logs.
* Add CRUD routes and forms for incentive records.
* Add role-specific dashboards for Principal, Administrator, Department Head, and Employee once those roles are mapped to the current role model.
* Add date range, employee, department, status, and report category filters to the dashboard and export routes.
* Add Excel export support in addition to the existing CSV and PDF outputs.
* Add reminder scheduling and email delivery using production-safe SMTP settings and delivery logs.
* Add automated tests for dashboard analytics, module route access and incentive calculations.
* Add seed/demo data for showcasing the new module dashboards during stakeholder review.
