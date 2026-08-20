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
