# Calling letters in Reception — deployed workflow

The user confirmed **administrators only** may issue letters for Reception's own
applicants. Website applications and their letters remain read-only in Reception.
The module is deployed, enabled and verified on demo and production as of
26 October 2026. New installations still default to disabled.

## Workflow and decisions

| Record | Reception action | Authoritative copy |
| --- | --- | --- |
| Website application | Authorised staff download the current website PDF | Website; no edit, regeneration or duplicate application |
| Reception applicant | Administrator confirms applicant details, course and intake, then issues a PDF | Immutable, encrypted Reception artifact |
| Existing Reception letter | Admissions, manager and administrator roles can download within their applicant access | The original issued PDF, not a new render |
| Replacement letter | Administrator explicitly confirms replacement | New version; prior versions retained |

Use one approved template/renderer on the website instead of maintaining two
copies of the official logo, stamp, signature, bank details and wording. Reception
starts the native generation workflow, but the website only renders the PDF. It
does not create or change a website application, register admission, relay to
Mzizi, send email/SMS or queue delivery. A calling letter remains an invitation,
not confirmation of final admission. Intake must be explicitly supplied; the old
website default is not silently reused for native Reception letters.

Website PDFs are retrieved on demand. The already scheduled application mirror
is unchanged and receives no extra document fields. This avoids storing stale
website copies or increasing every scheduled refresh. A website outage prevents
a new download/render; previously issued native PDFs remain available in
Reception. If offline access to website letters becomes necessary, a later phase
can add encrypted cached copies, visible generation/sync dates and withdrawal
handling. That cache is not implemented or claimed here.

Begin on the applicant detail pages, with clear Website/Reception labels and a
per-applicant issue history. Possible later additions are a central letter
register, approval queue, verified delivery tracking and controlled template
administration. They are not prerequisites for this first version. Sending must
wait for a properly configured email/SMS provider and separate delivery consent;
PDF generation is never described as successful inbox delivery.

## Boundaries and storage

Two separately purpose-signed website operations provide an existing PDF and a
stateless native render. Timestamp, single-use nonce, method, application resource
and exact request-body hash are covered by the signature. Existing application
sharing and the new letter control must both be enabled. The server accepts no
document upload or client-supplied file path. Existing downloads use the confined
private-file resolver and expected filename. PDFs and JSON envelopes are bounded;
the client refuses redirects, other hosts, mismatched environments/references/IDs,
unexpected fields, malformed PDF envelopes and digest mismatches. HTTPS verification
remains enabled. No automatic retry or fallback host is configured.

Only reference, name, primary phone, confirmed course and intake reach the native
renderer. Guardian/contact history, qualifications, notes and email are excluded.
Temporary PDF output is private 0600, checked against the intended private
directory (including a tempnam fallback check), then removed in a finally block.
Unprintable/oversized input is rejected and native output must fit the A4 page.
Existing website rendering keeps its previous defaults and layout behavior.

Reception encrypts native PDF data in its database, hides it from model JSON,
records the combined template-and-renderer digest/issuer/date/version, and requires authenticated
downloads with no-store/nosniff headers. Native artifacts are tied to applicants
by a cascading foreign key, so approved applicant cleanup/retention removes the
encrypted PDFs instead of leaving orphan files. Issuance/download audit events
contain IDs, versions and safe codes, not recipient details or document bytes.

Issuance is administrator-only in both route middleware and controller, with
ordinary web CSRF protection and throttling. A per-applicant lock and a keyed
daily input fingerprint prevent double-click duplicates; an interrupted database
transaction rolls back the letter and its issue audit together and releases the
lock. Contact details are rechecked under a database row lock after rendering.
Replacements require confirmation and history is limited to ten versions per
applicant. No website record receives an issue/edit action.

## Verification and deployment

Reception: **112 tests / 1,505 assertions**, including 17 new letter tests for
roles, guest/inactive/password gates, CSRF, encryption, duplicate/replacement
requests, lock contention, version limits, interrupted storage, changed contacts,
source boundaries, response limits, safe failure, cross-applicant downloads and
cascading cleanup. All external responses are mocked and applicants are synthetic.
WordPress: **47 isolated checks**, plus a private synthetic A4 preview using the
approved DOCX's actual logo/stamp/signature (48 checks with that preview). The PDF
was visually inspected and is one-page A4. These checks exercise the real PDF
renderer, signatures/replay/body tampering, private path confinement, malformed
and oversized output, explicit intake and temporary-file cleanup. Existing export,
runtime-path and runtime-packaging regressions also pass. No production applicant
was submitted and no live negative probe or Mzizi replay was used.

The verified runtime set has **14 code/view files**: five WordPress and nine
Reception, including the explicit private-file dependency and one additive
DATETIME migration. It contains no reports,
README/AGENTS, tests, source maps, vendor rebuild, archive or template upload.
All 28 corresponding demo/production destinations pass the runtime upload policy.
The approved template is an existing server-side runtime input, not a project
report to upload. No new Composer dependency is introduced.

Fresh live baselines were patched without overwriting unrelated source. All 14
final runtime hashes match between verified demo and production. Reception's
`CALLING_LETTERS_ENABLED=true` and Toolkit Control's **Enable calling letters for
Reception** are enabled; application sharing and the shared secret remain required.

Demo passed 15 checks on native EA-PHP 8.4.27/MySQL, including a real HTTPS website
PDF and renderer, administrator issue/download, encrypted storage, duplicate
prevention, issue history, manager denial and complete rollback of synthetic rows.
Authenticated routes were dispatched privately through the real application
kernel/session/CSRF stack from CLI, rather than through an interactive browser.
The generated 90,809-byte PDF is one-page A4 and uses the approved brand assets.
Production acceptance read an existing website PDF and verified its mirror,
configuration and schema; it created no test applicant or issued letter. Sync
processed seven demo and 28 production applications. Both environments' normal
website homepages and Reception logins returned HTTP 200. Setup crons were removed.
Staff should still review the workflow in their own normal signed-in sessions.

The first production acceptance failed because its older installation lacked the
private-file resolver. The failure trap disabled the new controls; the module was
corrected to load its dependency explicitly, the missing guard was installed first,
and demo passed again before the identical production reconciliation. Original
failed and successful receipts are preserved privately, rather than overwritten.

The user explicitly authorised the existing missing-fileinfo prerequisite exception
**for this release run only**. Native paths above were verified without changing
PHP configuration, switching interpreters or altering dependencies. The exception
is now closed; ordinary platform checks remain required for future releases. The
33 development packages per Laravel installation were not removed: cleanup still
needs hosting to provision fileinfo and successful target platform checks. CLI
patch information is not evidence of a web-worker restart or vendor build attestation.
No production test application, Mzizi replay or email/SMS was sent.

Official implementation references:
[Laravel HTTP client](https://laravel.com/docs/13.x/http-client),
[Laravel encrypted attributes](https://laravel.com/docs/13.x/eloquent-mutators#encrypted-casting).
Private evidence: `reports/calling-letters-2026-10-26/`; synthetic preview and
deployment plan are excluded from Git/runtime upload.
