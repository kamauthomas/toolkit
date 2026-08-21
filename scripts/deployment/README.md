# Browser-free theme deployments

This directory retains the terminal-driven cPanel Fileman deployment scripts
used for the Toolkit WordPress child theme. They do not attach to an existing
browser session and therefore do not interrupt an operator using the browser.

## Credentials

Credentials never belong in these scripts or Git. Supply them either as an
existing `CPANEL_AUTH` environment variable or in the ignored file:

```text
.toolkit-deploy/secrets.env
CPANEL_AUTH=cpanel-user:password
```

The scripts read that one value without printing it. Keep the secrets file mode
restricted to the local operator account.

## Release scripts

`releases/deploy-footprint-poster-2026.08.21.2.sh` is the corrective Footprint
release requested after the first redesign proved too elaborate. It applies the
small `2026.08.21.1..2026.08.21.2` patch to fetched live files, rejects remnants
of the superseded editorial layout, and verifies the poster-style chronology,
release marker, public route, and byte identity. Use it demo first and
production second.

`releases/deploy-footprint-ui-2026.08.21.1.sh` preserves the complete Footprint
UI redesign. It rebuilds the payload from each environment's fetched live
files using the exact `d8b61c7..toolkit-release-2026.08.21.1` patch, keeps
`functions.php` last, rejects the retired trail markup, checks byte identity,
and verifies the public page, release marker, copy markers, PHP-error absence,
and all four photographic assets. Use it demo first and production second.

`releases/deploy-claude-review-fixes-2026.08.20.2.sh` preserves the review
release that disables misleading calling-letter email delivery, relabels old
mail attempts as unverified, and promotes the pending story Yoast metadata.
It uses each live file's actual Git baseline, patches with zero fuzz, uploads
all included modules before `functions.php`, verifies byte identity, and
checks Apply plus all seven affected story routes. The storage migration keeps
letter generation and secure downloads live while forcing email off until an
authenticated SPF-aligned transport is installed and tested.
If a run stops before upload, `TOOLKIT_DEPLOY_RESUME=1` first requires every
remote file to remain identical to the saved rollback. After upload,
`TOOLKIT_DEPLOY_VERIFY_ONLY=1` rebuilds the expected payload from that rollback
and performs byte-identity plus route verification without uploading again.

`releases/deploy-admissions-safety-2026.08.20.1.sh` preserves the demo-first
release that removed unsafe Mzizi HTTP-status assumptions and made calling
letter upserts atomic. It backs up and patches the three live files, uploads
both included modules before `functions.php`, verifies byte identity, and
checks the cache-busted Apply route and release marker.

`releases/deploy-footprint-2026.08.12.12.sh` is the preserved release procedure
for the `/footprint/` page. It:

1. accepts only `demo` or `production`;
2. fetches the current remote `functions.php` and CSS into an ignored rollback;
3. applies the reviewed Git patch to those remote copies with zero fuzz, so
   unrelated server-only edits survive;
4. refuses to overwrite an unexpected pre-existing new template;
5. lints and checks the generated payload;
6. uploads CSS and the template before activating `functions.php`;
7. fetches every deployed file again and requires byte-for-byte equality.

The release was run demo first and production second. Its rollback directories
are `rollbacks/demo-pre-2026.08.12.12/` and
`rollbacks/production-pre-2026.08.12.12/`.

The script is release-specific evidence, not a generic deploy command. Its
rollback-exists and new-file guards intentionally prevent casual re-execution.
Its immutable Git range (`e9c1fe3..toolkit-release-2026.08.12.12`) identifies
the exact theme payload that was promoted. Review it manually before adapting
it for another release; update the release, Git range, expected markers, file
list, smoke routes and rollback name.

## Required sequence

For every release: commit locally, deploy demo, verify uncached and bare URLs,
review an isolated desktop/mobile render when layout changed, then deploy the
same reviewed payload to production. Never submit a test application on
production. Keep `functions.php` last because it activates routes and triggers
the theme release/cache transition.
