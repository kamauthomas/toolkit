# Report System → Wingu Box Integration Design

**Status:** Internal queue implemented locally; browser bridge not yet connected
**Updated:** 1 September 2026
**Current production connection:** None

## Intended outcome

An employee writes one accurate daily report in the Toolkit Report System. Once
the institutionally approved trigger is reached, the report is queued for Wingu
Box and its delivery result becomes visible without the employee retyping the
same narrative.

Wingu has no available API or SSO for this use. The approved direction is the
existing isolated authenticated-browser method: the employee signs in to Wingu,
the bridge uses that bounded session, and no password is copied into the Report
System or its database.

## Recommended flow

1. The employee saves and submits a report.
2. A manager reviews and approves it.
3. The employee or authorised manager attaches attendance and queues the
   approved report; the report ID uniqueness rule prevents duplicate queue rows.
4. Attendance start/end values are attached from an approved Excel import or
   explicit manual entry.
5. The dispatch remains queued until the employee provides an authenticated
   Wingu browser session.
6. The isolated browser bridge loads the Wingu timesheet, reads the projects
   Wingu itself provides, and uses the approved displayed selection. The Report
   System never dictates or hard-codes a project code.
7. The bridge fills only the approved report date, time and notes, submits once,
   reloads Wingu, and verifies that the row persisted.
8. Wingu's visible result is stored without storing the employee password or
   browser session.
9. Success, rejection or retry status is shown to the employee and authorised
   manager.
10. Corrections create a new controlled revision; they do not silently overwrite
   an already accepted timesheet row.

## Identity model

Use a mapping such as:

| Toolkit record | Wingu meaning |
|---|---|
| Report System user ID | Internal identity anchor |
| Wingu employee ID | Vendor-side staff identity |
| Wingu-presented project identifier/label | Timesheet selection read from the active Wingu page |
| Dispatch session reference | Ephemeral local handoff only; never a password or committed cookie |

The bridge follows the documented background-browser procedure: use an isolated
temporary profile, a localhost-only control port, the minimum approved
authenticated state, exact row scoping, a persistence reload, and deletion of
temporary browser data after the run. A human login is required whenever the
session is absent or expired.

## Data and delivery records

The local implementation now provides:

- `wingu_dispatches`: report/revision, idempotency key, Wingu-provided project
  identifier/label, approved attendance source, state,
  external reference and timestamps;
- `wingu_dispatch_events`: append-only, sanitised success/rejection/retry events;
- employee and authorised-manager queue forms for approved reports;
- a reconciliation screen that never reveals provider secrets.

The deterministic Excel parser is now operational with a downloadable canonical
template, preview-only validation, exact approved-report matching and a
250-populated-row limit. Still pending are external identity confirmation and
the isolated local browser dispatcher. The dispatcher requires RPT-014; the
system does not guess Wingu page fields.

The credential-safe discovery tooling is implemented in
`scripts/wingu-session.sh` and `scripts/wingu-discover.py`. It starts a separate
temporary profile on a localhost-only debugging port and inventories form
structure without field values, cookies, storage, screenshots or submission.
The fixture verification and owner steps are documented in
`WINGU_AUTHENTICATED_DISCOVERY.md`.

Implemented dispatch states are `ready`, `dispatching`, `accepted`, `rejected`,
`needs_attention` and `cancelled`.

## What can be sent

- approved report date;
- concise approved activity notes;
- confirmed Wingu employee ID;
- the project selected from values presented by Wingu in that session;
- attendance start/end values from the institution's authoritative attendance
  source;
- an idempotency key that prevents duplicate rows.

The Report System must not invent working hours from report text, Git commits,
login time or file timestamps. A report proves work narrative, not biometric or
attendance time.

## Safety and accuracy controls

- Default to dispatch after manager approval, not on draft autosave.
- Preview the exact Wingu payload before the first live submission.
- Require an explicit employee mapping; discover and verify project choices
  from Wingu during the dispatch session rather than hard-coding one.
- Fail closed when a mapping, time source or provider response is missing.
- Retry only errors that are known to be temporary; never repeatedly submit a
  validation failure.
- Reload/query Wingu after acceptance and reconcile the stored external
  reference.
- Never persist passwords or browser sessions in the database or Git; delete
  temporary browser state after verified completion.
- Provide a per-report audit view and an administrator “needs attention” queue.

## Delivery phases

1. **Authenticated discovery:** after the employee signs in, record the current
   timesheet fields, Wingu-provided project control and validation behavior
   without submitting.
2. **Mapping prototype:** **partially complete locally** — Excel/manual
   attendance input, validation preview and approved-report queuing work with no
   external writes. Wingu identity confirmation remains part of discovery.
3. **Controlled browser dispatch:** test idempotency, persistence reload,
   validation failures and corrections on explicitly approved rows.
4. **Controlled pilot:** enable a small approved staff group with administrator
   review.
5. **Production operation:** enable monitoring, reconciliation, support and
   documented rollback.

The exact owner decisions are maintained in `OWNER_INPUT_TRACKER.md`, especially
RPT-006 through RPT-010.
