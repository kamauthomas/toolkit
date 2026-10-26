# Runtime file review — 26 October 2026

Reviewed Git status, ignore rules, deployment scripts and authenticated cPanel
Fileman metadata for production/demo WordPress and Reception, Reporting and the
virtual-campus test application. No browser or live attack probes were used.
After a hosting timeout, requests stopped until the user checked access and
authorised one normal retry; that retry succeeded.

The working trees were already dirty. WordPress started 42 commits ahead of its
cached upstream with ten tracked changes and additional untracked files; Reception
already had a removed report and two untracked reports. Those edits are preserved.
No Git fetch/push or whole-repository deployment is part of this review.

Ignore rules now cover local caches, sessions and backup/editor files. Documents
can remain versioned in Git: deployment is governed by runtime allowlists, not
`.gitignore`. The shared Python upload guard rejects unknown roots, noncanonical
paths, source maps, document/database/archive extensions, disguised backups and
public environment files. Package creation rejects runtime-directory symlinks and
files resolving outside the source. Required license notices and approved public
downloads remain intentional exceptions; public downloads are published separately
from application payloads. Older shell release scripts use explicit file lists,
but are not all wired through the shared Python guard.

The metadata search returned 1,337 candidates across eight roots. These are
**search matches, not confirmed leaks or a deletion list**. Broad matches include
runtime files whose names contain “agents”, “hardening” or “changelog”, plugin SQL
schema inputs, built-in template archives and runtime Markdown resources. They
must not be removed merely because their names matched. Other matches include
vendor documentation/source maps, required license notices, private operational
storage/old audit holdings and three generated Reporting bytecode files. No
project reports or README files were found in the child-theme release payload.

Vendor updates can reintroduce documentation. Inspect exact paths and usage before
cleanup; preserve runtime assets, student downloads, legal notices and business
storage. A previous harmless demo upload canary also remains an identified cleanup
item; its URL must not be executed again. Inventory does not prove a public URL
can be fetched. Private evidence is under `reports/runtime-minimum-audit-2026-10-26/`.

Automatic approval rejected an uploaded PHP/cron inventory helper. It was never
deployed; the existing read-only Fileman search API supplied the inventory instead.
No new remote execution path was introduced for inventory. Server cleanup and
environment release verification must be recorded from their actual receipts,
rather than inferred from this inventory or a prepared upload manifest.

## Reviewed cleanup scope

Exact-path review narrows the list to 278 standalone vendor documents/source maps
and the prior harmless demo canary: 52 files in demo application roots and 227 in
production/Campus roots. Twelve Elementor Markdown files are actual plugin runtime
resources and are retained pending an explicit module decision; 49 other broad
name matches are directories, executable code, schema/template inputs or uncertain
binary files. Required licenses, approved downloads and private business storage
are excluded. Generated Reporting bytecode is a separate runtime cache/exposure
review item, not proof of an accidentally uploaded report.

The cleanup command defaults to a local dry run. Execution requires matching fresh
file type/size/mtime metadata, uses an exact per-environment allowlist and moves
files to private cPanel recovery trash without emptying it. Production requires a
verified demo receipt. Both plans and nine isolated unsafe-path/duplicate cases
passed local checks. No PHP helper or cron is used for inventory or cleanup. The
private cleanup receipts record actual removal/verification; this prepared plan
alone is not a completed server cleanup. API behaviour follows the documented
[cPanel Fileman file operation API](https://api.docs.cpanel.net/cpanel-api-2/cpanel-api-2-modules-fileman/cpanel-api-2-functions-fileman-fileop).

## Completed cleanup and remaining dependency work

Receipts verify removal of 52 demo and 227 production/Campus files from active
code directories: 79 Markdown, 28 text, 111 reStructuredText documents, 60 source
maps and the old harmless demo canary. Every selected path disappeared from the
post-cleanup inventory and both normal homepage checks returned 200. Recovery
trash was not emptied. No approved download or license notice was selected.

Private older holdings comprise 815 previous removed-file matches, two database
snapshots and six incident-log archives (metadata only; about 20.5 MB). These were
not downloaded or erased. Twelve actual plugin Markdown resources and three
normal Reporting bytecode files remain separately classified. Reporting's fetched
configuration contains explicit Python/bytecode/source/secret denial rules; no
public bytecode request was made, so configuration is not presented as an HTTP
exposure test.

Fetched Composer metadata confirms 33 development dependencies in each Reception
and Campus installation. The new package builder rejects a dev-installed vendor
tree; use a separate clean staging tree, preserving the working copy's test tools.
Isolated offline Composer removal passed for Reception (79 unchanged runtime
packages) and Campus (76), with migration, discovery, routes, environment and
synthetic login checks. Server cleanup uses the existing Composer/app tools with
private rollback and maintenance, preserving runtime package code and versions.
Actual vendor-cleanup receipts must establish its final completion.

A later ordinary app-availability check timed out after the metadata/config reads;
requests stopped immediately. The failing public host was not journalled, so no
specific outage or IP block is inferred. The user confirmed access restored and a
single normal cPanel retry succeeded. No IP rotation or attack probes were used.

The demo vendor removal was blocked by the host's missing EA-PHP 8.4 fileinfo
extension. Verified rollback restored the exact original installed metadata,
removed maintenance/tool/cron state and returned login 200. Production/Campus
vendor state was not changed. PHP Selector controls ALT-PHP: fileinfo is already
enabled there for 8.4, while the actual applications use native EA-PHP 8.4.
Native extension controls are disabled; no PHP-version or extension setting was
changed. Further cleanup must pass the required platform check after provisioning.

## Follow-up review and prepared recovery relocation

A fresh eight-root metadata review found no newly selected README/source-map
files in active code. Remaining matches include required legal notices, actual
Elementor runtime Markdown resources, eight bundled plugin template ZIPs and an
unused CMB2 `.mo.orig` translation backup. The fetched CMB2 loader exactly matches
the local reviewed file and loads `.mo`, with no `.mo.orig` reference. Preserve
the actual `.mo` translation and template ZIPs; only the obsolete backup is selected.

Historical security recovery inside Reception contains 882 regular files and
707 descendant directories. The search also returns the queried root itself;
it is explicitly excluded from descendant comparisons. No symlink/special-file
entry was returned. Fresh cron metadata has no job referencing that historical
folder. The recovery root and shared private-storage parent are both 0755.
The prepared cleanup restricts only this historical recovery root to 0700 before
moving it to `toolkit-private-storage/platform-security-2026-10-16`; it does not
change permissions on the shared parent or delete the last recovery copy.
Full relative-path/type/size/mtime/mode metadata must agree before/after the move,
the old path must disappear, and the destination must retain 0700. The single
obsolete translation backup goes to private recovery trash, which is not emptied.

Fresh front-controller reads establish that the correct early-maintenance path
is already present on both Reception environments. The production file needs
verification, not another upload. Its vendor metadata is unchanged. Module
investigation confirms no matching EA-PHP 8.4 fileinfo module is available, so the
33-package-per-install dependency cleanup still cannot proceed. No Composer
attempt or PHP configuration change is part of this follow-up. The existing UI
and read-only sync release remains the deployed application release.

Local follow-up fixtures pass ten recovery/cleanup cases (including stale state,
writable destination parent, active job, chmod failure, move failure/mismatch and
changed backup) and two existing-index/drift cases. No files are uploaded by the
verification/cleanup path. Actual cleanup completion must come from
`reports/fileinfo-followup-2026-10-26/additional-cleanup.json`, not this prepared plan.

Execution first restricted the recovery root to 0700, then cPanel interpreted the
relative move destination as a rename within Reception's private storage. The
post-move check stopped the cleanup before the translation backup was touched.
Read-only reconciliation located all 1,589 descendant entries there with matching
relative paths/types/sizes/mtimes/modes, verified root 0700 and both original and
intended paths absent. No runtime/business files were affected. The corrected
command uses an absolute full destination and checks the returned destination
before continuing. A one-use reconciliation accepts only this exact failed receipt,
requires fresh intermediate metadata/private permissions and absent destinations,
and records intent before moving. Five additional isolated reconciliation cases
cover unknown receipts, stale content, duplicate attempts and unexpected destinations.

## Verified follow-up completion

The corrective move and cleanup are complete. The final receipt verifies all
882 files / 707 descendant directories (128,026,614 file bytes by metadata) at
the intended recovery destination outside Reception, matching original relative
paths/types/sizes/mtimes/modes. The destination root is 0700; both the original
and intermediate locations are absent. This is metadata verification, not a
download or content-hash audit of the recovery files. Historical scripts referring
to the original audit path must not be rerun as current deployment instructions;
use this relocation record when consulting their recovery artifacts.

The exact unused `cmb2-pt_PT.mo.orig` backup was moved to recovery trash and its
old path is absent. No recovery trash was emptied. Together with the earlier
279-file cleanup, 280 obsolete files have been removed from active code folders;
the 882 recovery files are a separate relocation, not additional deletions.
Approved downloads, licenses, template ZIPs and runtime resources remain.

Final ordinary WordPress homepage and production Reception login requests returned
200; the homepage still reports release 2026.10.26.1. Existing Reception demo and
production maintenance paths were separately verified with normal login 200 and
unchanged vendor metadata; no index upload was needed. This follow-up uploaded
zero runtime/source/document files and made no PHP or dependency changes.
Seventeen isolated cleanup/index/reconciliation scenarios pass. Missing fileinfo
still blocks the distinct 33-package-per-install vendor cleanup. Exact private
receipts: `reports/fileinfo-followup-2026-10-26/additional-cleanup.json` and
`index-verification.json`; investigation cron removed.
