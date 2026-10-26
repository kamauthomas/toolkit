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
