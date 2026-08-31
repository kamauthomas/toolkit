# Report System — Owner Input Tracker

**Updated:** 31 August 2026
**Purpose:** The exact decisions or material needed from Toolkit before the
remaining Report System work can be enabled safely. Do not place passwords,
tokens or personal data in this file.

| ID | Needed from Toolkit | Why it is needed | Current safe default | Status |
|---|---|---|---|---|
| RPT-001 | Approve the inactivity period | Controls automatic employee lock timing | Employee accounts lock after more than 14 days; privileged roles remain exempt | **Resolved: 14 days** |
| RPT-002 | Name the department administrator(s) authorised to unlock employee accounts | Unlocking now requires a reason and is limited to employees in that administrator's department | Super administrators retain institution-wide oversight | Names needed before deployment |
| RPT-003 | Approve review of Admissions, Monthly Intake, Meetings/Actions, Incentives and Reminders | Modules must be reviewed before deployment | Local service only | **Resolved: local review requested** |
| RPT-004 | Define the official intake actual | Target percentages need one institutional definition | Count only verified admissions with recorded fee-paid state | **Resolved: verified fee-paid enrolment** |
| RPT-005 | Confirm who may create meeting minutes and who may reassign/close action items | This determines final role policy | Any active user may record a meeting; creators and authorised managers assign actions; owners/managers update status | Review needed |
| RPT-006 | Select the Wingu integration mechanism | No API/SSO is available | Isolated authenticated-browser bridge; user signs in, no credentials stored | **Resolved: browser automation** |
| RPT-007 | Define Wingu project handling | Wingu owns the available project choices | Read projects from Wingu and verify the approved displayed selection; never hard-code a project | **Resolved: Wingu-provided value** |
| RPT-008 | Choose when a report becomes eligible for Wingu | Prevents incomplete reports being exported | Queue only after manager approval | **Resolved: approval** |
| RPT-009 | Name the authoritative source of working time | Report text does not prove attendance | Approved Excel sheet import or explicit manual entry | **Resolved: Excel/manual** |
| RPT-010 | Approve Wingu retry, correction and failure-escalation ownership | Failed or rejected timesheet rows need a human-owned resolution path | Fail closed; retain a queued record and notify an administrator | Decision needed |
| RPT-011 | Provide approved SMTP or notification provider configuration | Reminders and failure notices require authenticated delivery | In-app notifications only | Provider needed |
| RPT-012 | Approve retention periods for reports, admissions, minutes, action histories and integration logs | Permanent retention may conflict with institutional privacy obligations | No automated deletion introduced | Policy needed |
| RPT-013 | Provide one approved sample attendance Excel sheet or its exact column names/date/time formats | The importer must be deterministic and reject ambiguous rows | No Excel importer guesses | Needed for Wingu bridge |
| RPT-014 | Provide an authenticated Wingu review session when prompted | Stable fields, project selection and persistence behavior must be inspected before writing the dispatcher | Dry-run only; the human signs in | Needed for Wingu bridge |

## How to respond

Reply using the IDs, for example:

```text
RPT-001: Keep fourteen days.
RPT-003: Approved for demo review.
RPT-004: Count fee-paid enrolments only.
```

Credentials must be added only through the approved server-side secret/config
process after the integration mechanism is selected. Do not paste them into a
chat, issue, Markdown file or Git commit.
