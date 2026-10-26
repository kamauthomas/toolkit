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
