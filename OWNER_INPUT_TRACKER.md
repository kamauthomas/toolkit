# Report System — Owner Input Tracker

**Updated:** 28 August 2026  
**Purpose:** The exact decisions or material needed from Toolkit before the
remaining Report System work can be enabled safely. Do not place passwords,
tokens or personal data in this file.

| ID | Needed from Toolkit | Why it is needed | Current safe default | Status |
|---|---|---|---|---|
| RPT-001 | Approve the five-day inactivity rule, or provide the preferred number of days | The rule currently locks an employee after more than five days without a successful login | Five days retained; administrators and executive roles remain exempt | Decision needed |
| RPT-002 | Name the department administrator(s) authorised to unlock employee accounts | Unlocking now requires a reason and is limited to employees in that administrator's department | Super administrators retain institution-wide oversight | Names needed before deployment |
| RPT-003 | Approve a demo review of Admissions, Monthly Intake and Meetings/Actions | These modules are implemented locally but have not been deployed | No production deployment | Approval needed |
| RPT-004 | Confirm whether an intake “actual” means a verified admission, fee-paid enrolment, or another official state | Target percentages must use one institutional definition | Verified admission records are counted | Decision needed |
| RPT-005 | Confirm who may create meeting minutes and who may reassign/close action items | This determines final role policy | Any active user may record a meeting; creators and authorised managers assign actions; owners/managers update status | Review needed |
| RPT-006 | Provide Wingu Box vendor/API documentation, sandbox details and the official support contact | A supported API or SSO mechanism must be confirmed before integration engineering | No connection and no credential capture | Needed for integration |
| RPT-007 | Confirm the official Wingu project code and employee identifier mapping | Existing records mention both `TTI2024` and `TTI0224 / TTI 2024` | No project code is assumed | Decision needed |
| RPT-008 | Choose when a report becomes eligible for Wingu: submission, manager review, or approval | Sending too early can export incomplete or later-corrected work | Manager approval is recommended | Decision needed |
| RPT-009 | Name the authoritative source of working time | Report descriptions do not prove attendance times | Never derive attendance from Git, reports or login activity | Decision/data source needed |
| RPT-010 | Approve Wingu retry, correction and failure-escalation ownership | Failed or rejected timesheet rows need a human-owned resolution path | Fail closed; retain a queued record and notify an administrator | Decision needed |
| RPT-011 | Provide approved SMTP or notification provider configuration | Reminders and failure notices require authenticated delivery | In-app notifications only | Provider needed |
| RPT-012 | Approve retention periods for reports, admissions, minutes, action histories and integration logs | Permanent retention may conflict with institutional privacy obligations | No automated deletion introduced | Policy needed |

## How to respond

Reply using the IDs, for example:

```text
RPT-001: Keep five days.
RPT-003: Approved for demo review.
RPT-004: Count fee-paid enrolments only.
```

Credentials must be added only through the approved server-side secret/config
process after the integration mechanism is selected. Do not paste them into a
chat, issue, Markdown file or Git commit.

