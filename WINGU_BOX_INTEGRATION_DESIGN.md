# Report System → Wingu Box Integration Design

**Status:** Brainstorm and engineering proposal only  
**Updated:** 28 August 2026  
**Current production connection:** None

## Intended outcome

An employee writes one accurate daily report in the Toolkit Report System. Once
the institutionally approved trigger is reached, the report is queued for Wingu
Box and its delivery result becomes visible without the employee retyping the
same narrative.

This should not mean sharing passwords between the two systems. Toolkit login
credentials must remain valid only for Toolkit, and Wingu credentials must
remain valid only for Wingu.

## Recommended flow

1. The employee saves and submits a report.
2. A manager reviews and approves it.
3. Approval creates one idempotent Wingu dispatch record for that report
   revision.
4. A server-side adapter sends the approved fields through a supported Wingu
   API using OAuth, a vendor service account, or another vendor-approved method.
5. Wingu's response/reference is stored without storing the employee password.
6. Success, rejection or retry status is shown to the employee and authorised
   manager.
7. Corrections create a new controlled revision; they do not silently overwrite
   an already accepted timesheet row.

## Identity model

Use a mapping such as:

| Toolkit record | Wingu meaning |
|---|---|
| Report System user ID | Internal identity anchor |
| Wingu employee ID | Vendor-side staff identity |
| Approved project code | Timesheet project selection |
| Provider connection reference | Server-side credential/vault reference, never a password column |

Preferred authentication order:

1. Institution SSO through OIDC or SAML, if Wingu supports it.
2. Per-user OAuth authorisation with revocable tokens.
3. A restricted integration service account approved by Wingu and Toolkit.
4. If no supported API exists, use a reviewed export/import file rather than
   unattended browser automation.

Browser-session copying is not suitable as a permanent integration: sessions
expire, interface changes are hard to detect, and it creates unclear ownership
for credentials and submissions.

## Data and delivery records

The future implementation should add:

- `external_identities`: Toolkit user, provider, Wingu employee ID and a secret
  manager reference;
- `wingu_dispatches`: report/revision, idempotency key, project code, state,
  attempt count, external reference and timestamps;
- `wingu_dispatch_events`: append-only, sanitised success/rejection/retry events;
- an administrator mapping screen that never reveals provider secrets;
- a worker/queue so a slow Wingu response cannot slow or lose a Report System
  submission.

Suggested dispatch states are `queued`, `sending`, `accepted`, `rejected`,
`retry_wait`, `needs_attention` and `cancelled`.

## What can be sent

- approved report date;
- concise approved activity notes;
- confirmed Wingu employee ID;
- approved project code;
- attendance start/end values from the institution's authoritative attendance
  source;
- an idempotency key that prevents duplicate rows.

The Report System must not invent working hours from report text, Git commits,
login time or file timestamps. A report proves work narrative, not biometric or
attendance time.

## Safety and accuracy controls

- Default to dispatch after manager approval, not on draft autosave.
- Preview the exact Wingu payload before the first live submission.
- Require an explicit mapping for every employee and project.
- Fail closed when a mapping, time source or provider response is missing.
- Retry only errors that are known to be temporary; never repeatedly submit a
  validation failure.
- Reload/query Wingu after acceptance and reconcile the stored external
  reference.
- Keep tokens in an encrypted server-side secret store and logs free of
  passwords, tokens and unnecessary personal data.
- Provide a per-report audit view and an administrator “needs attention” queue.

## Delivery phases

1. **Vendor discovery:** obtain Wingu API/SSO documentation and a sandbox.
2. **Mapping prototype:** build employee/project mapping and a dry-run payload
   preview with no external writes.
3. **Sandbox dispatch:** test idempotency, validation failures, retries and
   corrections.
4. **Controlled pilot:** enable a small approved staff group with administrator
   review.
5. **Production operation:** enable monitoring, reconciliation, support and
   documented rollback.

The exact owner decisions are maintained in `OWNER_INPUT_TRACKER.md`, especially
RPT-006 through RPT-010.

