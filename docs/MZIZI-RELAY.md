# Mzizi delivery and course availability

Prepared by Thomas · 17 October 2026 · release 2026.10.17.1

## Evidence and scope

The operator supplied `StudentInfo.asmx/SubmitOnlineApplication` HTTP 409 and
confirmed that it appears in Toolkit Control. Read-only production diagnostics
found 27 saved applications: 11 marked delivered, 14 unconfirmed, one relay
failure and one stuck relaying. Three unconfirmed records retain HTTP 409.
This is distinct from the public form's local duplicate-detection 409.

Mzizi's generic error does not identify its internal cause. No production test
application or retry of an uncertain historical application was sent. Vendor
logs or an authorized Mzizi-side record check are still needed to establish why
those submissions failed and whether each was accepted.

Confirmed local flaws corrected:

- Saved study mode and sponsorship choice were referenced through undefined
  variables during relay. Their saved values now reach the submission payload.
  This is a verified defect, not proof of the cause of every upstream 409.
- Gender was lowercased on storage and relay, whereas the current public Mzizi
  form sends uppercase F/M/I. New relay payloads preserve the published casing,
  including for older lowercased records. The field names and study/sponsorship
  values were checked against the JavaScript linked by the public Mzizi form,
  using GET requests only.
- Failed course/session lookups did not consistently count/log attempts; failed
  records were excluded from the automatic queue.
- A read-then-write status check allowed concurrent releases to submit twice;
  interrupted workers could remain relaying indefinitely.
- Any nonempty Mzizi `Message` could be treated as success, including a rejection.
  New confirmations require an affirmative response without rejection wording.
  All 11 existing production confirmation messages matched the affirmative
  pattern, checked without exporting their text.
- Two simultaneous public submissions could pass the local duplicate check.
  A database advisory lock now serializes identical contact/course fingerprints.
- Transient-only course data expired after 15 minutes; an outage could then
  remove choices from the form. Durable public catalogue snapshots replace that
  failure mode.

## Delivery rules

The encrypted local application is saved first. Immediate relay remains active
where already configured; a persisted watchdog can recover a newly saved
application if PHP ends before its shutdown callback. Earlier received/failed
records without an explicit due time are not silently replayed.

Each attempt claims the record atomically, increments its attempt count and
holds a 15-minute lease. It records a submission boundary before the actual
Mzizi POST. Successful completion requires that the same worker still owns the
record. An administrator's confirmation cannot race an active worker.

| Outcome | Action |
|---|---|
| Temporary failure before submission | Retry after 5, 15, 60, then 240 minutes; five total attempts |
| Permanent validation or configuration problem | Hold for Admissions review |
| Submission timeout, 409, other HTTP error, unreadable/rejection response | Hold as delivery unconfirmed; no automatic resend |
| Expired lease before submission | Schedule a safe retry within the remaining budget |
| Expired lease after submission, or legacy relaying without phase proof | Hold for Mzizi confirmation |
| Affirmative Mzizi confirmation | Mark delivered, save confirmation time and audit event |

The queue checks at most three due applications per run. Existing queued
applications still require the previous explicit automatic-release setting.
The relay settings remain the kill switch. Manual re-send requires an
administrator, a valid form nonce, and confirmation that the exact applicant
and course are absent in Mzizi. Manual confirmation records the administrator
and changes local status without sending anything upstream.

Attempt events, failure stage/code, upstream HTTP status when available, retry
time and exhausted/review state are retained in the application tables and
shown in Toolkit Control → Applications → Open → Delivery history. New logs
omit upstream response bodies, cookies, credentials and applicant contacts.
Older log text is retained as historical evidence, not rewritten.

There is no verified scoped Mzizi duplicate-lookup/idempotency contract in this
integration. A scheduled job cannot safely confirm an uncertain POST by sending
it again. Obtain the vendor's supported lookup/idempotency mechanism before
automating that reconciliation. HTTP POST retry constraints are described in
[RFC 9110 §9.2.2](https://www.rfc-editor.org/rfc/rfc9110.html#section-9.2.2);
409 itself is defined in [§15.5.10](https://www.rfc-editor.org/rfc/rfc9110.html#section-15.5.10).

## Course cache

Campuses, courses, referral choices and intakes use last-known-good WordPress
options with autoload disabled. A still-valid legacy transient is promoted
before expiry. Cached requests return immediately without contacting Mzizi.
Snapshots do not expire during an outage; a failed refresh retains the old data.

The private five-minute worker walks known public campus/course IDs and refreshes
at most two due catalogue entries per run, normally checking each every six
hours. It also preloads intakes before someone selects them. Failures receive a
30-minute cooldown. A named database lock prevents concurrent refreshes of the
same entry. Unknown, malformed and excluded campus/course IDs cannot create
arbitrary snapshot keys. No application submission runs as part of a refresh.

An uncached choice needs one successful initial lookup; previously unseen
options cannot be invented during an outage. Initial full preloading takes
multiple bounded background runs. Genuine upstream course changes replace the
corresponding snapshot after a successful refresh. Relay always validates the
selected course/intake against Mzizi before submission, even when the form used
saved choices. This may leave an application awaiting a safe retry during an
outage; it remains saved locally.

## Scheduling and deployment

Existing hosting jobs invoke general WordPress cron only every two hours. The
release adds a dedicated private CLI job every five minutes, staggered between
demo and production; existing general jobs are preserved. WordPress's own queue
hook remains a fallback. General WP-Cron is visit-dependent unless backed by a
server scheduler: [WordPress scheduler guidance](https://developer.wordpress.org/plugins/cron/hooking-wp-cron-into-the-system-task-scheduler/).

The CLI entry point lives outside all webroots, accepts only fixed demo or
production targets, locks overlapping runs and writes only a bounded last-run
log. It never exposes a public worker URL or secret URL token. The Application
screen shows the queue heartbeat and next retry; check private cron output if
the heartbeat stops. Inspect mode verifies schema/wiring without running relay.

Deploy with `scripts/deployment/releases/deploy-mzizi-relay-2026.10.17.1.py`
demo first, then production, using the local temporary MariaDB socket for its
synthetic tests. It patches fetched code with zero fuzz, preserves the deployed
header and other server changes, validates/tests the exact payload, uploads
only four theme PHP files plus the private runtime worker, checks byte identity,
release markers and schema, then installs one job per environment. README,
AGENTS, reports, tests and the patch are never uploaded. The additive database
columns may remain if code is rolled back; do not drop application data.

Production retains its existing private-file behaviour; the earlier demo-only
16 October change is not implicitly promoted by this focused release.

## Security/flaw review and tests

The review covered admin authorization and form nonces; SQL placeholders and
fixed destinations; output escaping; private payload/log handling; atomic
claims and deduplication; stale workers and reconciliation races; bounded
retries, catalogue refresh and cache-key creation; and the explicit runtime
deployment allowlist. Source and meaningful negative tests are used, without
live attack probes or IP rotation. No new public privileged route is added.
The normal catalogue smoke check is `scripts/tests/mzizi-public-catalogue.py`:
one form-page GET plus three ordinary catalogue lookups, stopping on the first
error. It never calls the submission endpoint or rotates IPs.

Local verification: 73 relay/catalogue integration checks against an isolated
MariaDB database with synthetic WordPress boundaries and mocked HTTP, plus 524
existing application contact/validation checks. PHP syntax and diff whitespace
checks pass. The test database is created with a random fixture name and dropped
afterwards; no real applicant database or live Mzizi request is used.

```bash
php scripts/tests/mzizi-relay.php /tmp/toolkit-mzizi-test.sock
php scripts/tests/system-audit-application.php
```

Residual limits: upstream internal 409 cause and historical delivery remain
unverified; success detection relies on existing affirmative response wording
until Mzizi provides a stable machine-readable contract; no SMS/email provider
is installed for applicant OTP. Administrators must review uncertain records.
Broader historical parent-theme/vendor risks remain as recorded in the previous
checkpoint. Security review is required before production commits and again
before push/deployment by the scoped AGENTS and deployment README.

## Verified release result

Demo and production both passed release **2026.10.17.1** verification: four
runtime theme files matched their exact prepared bytes, the private worker
matched, Apply returned HTTP 200 with the release/asset marker, the additive
schema and registered queue hook were confirmed, and one five-minute job per
site was installed. Each exact fetched-and-patched payload passed all 73
synthetic relay/catalogue checks before upload.

Three normal catalogue lookups passed on each host: campuses, one listed
campus's courses and one listed course's intakes. All returned HTTP 200 without
any application submission. The scheduled demo worker completed at 10:07:04 UTC
and production at 10:10:08 UTC on 17 October, each processing zero submissions.
Both sites retained five successful catalogue snapshots at the final check.

The final read-only production count is 11 delivered, 15 unconfirmed and one
failed. The old August relaying record was recovered into the unconfirmed
review list, with an audit event and no resend. The three saved 409 outcomes
still need Mzizi-side reconciliation; no new upstream delivery is claimed.
No attack-shaped live requests or IP rotation were used.

Private local evidence is under `reports/mzizi-relay-2026-10-17/` (ignored by
Git): source baselines, deployment byte hashes, synthetic test output, public
catalogue status/counts, scheduled completion markers and read-only aggregate
status/snapshot evidence. New reporting material and development files were
not deployed to public/application roots.
