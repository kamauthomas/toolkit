# Deployment pipeline engine — design

Date: 2026-08-07
Status: Approved for planning

## Problem

Deploys to demo and production are currently a fully manual, ad hoc process:

- The cPanel HTTP Basic credential is recovered by grepping old AI-session
  transcripts for a password typed in a prior chat — fragile, and not a
  practice worth relying on going forward.
- File lists to back up and upload are hand-assembled per deploy, not derived
  from an actual diff.
- The theme's release marker (`toolkit_theme_release()` in `functions.php`)
  is bumped by hand and must be remembered on every deploy; forgetting it
  means the automatic `litespeed_purge_all` cache purge never fires.
- Verification is whatever ad hoc `curl` checks happen to get run. On
  2026-08-07 this gap was concrete: a production admin toggle correctly
  changed WordPress's state, but the LiteSpeed page cache kept serving a
  stale 404 to real visitors because nothing purged it and nothing checked
  the *bare* URL (only a cache-busted one) after the change.
- There's no enforced "demo before production" gate beyond convention.

## Goals

- Turn today's manual `curl`/cPanel-UAPI choreography into a reusable,
  scriptable tool.
- Remove credential archaeology: store the cPanel credential in one
  gitignored local file.
- Derive the deploy file set from git diff against the last-deployed commit
  per environment, not a hand-maintained list.
- Make the release-marker bump and cache purge automatic on every deploy, and
  make verification check the *bare* URL post-purge (the exact check that
  would have caught the 2026-08-07 stale-cache bug).
- Enforce demo-before-production as a hard gate.
- Keep it scoped to this repo's WordPress/cPanel setup — organized well
  enough that a second target later doesn't require a rewrite, but without
  building a generic plugin system before a second real use case exists.

## Non-goals

- This tool does not address cache/state bugs triggered by wp-admin actions
  (e.g. a settings toggle) rather than a file deploy — those need the
  app-level convention (flush + purge on any state-changing admin action),
  documented separately in `guide/07-DEPLOYMENT-SAFETY.md`, not tooling.
- No GitHub Actions / webhook-triggered automation. Deploys stay manually
  invoked via CLI command.
- No generic multi-target plugin architecture in v1.

## Architecture

A Python package, stdlib-only (no third-party dependencies to install),
invoked as `python3 -m toolkit_deploy <command>`:

```
scripts/toolkit_deploy/
  __main__.py      # CLI entry point, argument parsing, command dispatch
  config.py        # loads config.yaml + secrets.env
  cpanel.py         # cPanel UAPI client: get_file_content, upload_files
  release.py        # reads/bumps toolkit_theme_release() in functions.php
  state.py           # reads/writes .toolkit-deploy/state.json
  verify.py          # post-deploy HTTP verification (cache-bust + bare recheck)
  deploy.py           # orchestrates backup -> upload -> release bump -> verify
  config.toml          # committed: environments, remote paths, smoke routes
```

Config uses TOML (stdlib `tomllib`, Python 3.11+) rather than YAML, to keep
the "stdlib-only, no dependencies to install" goal honest — the standard
library has no YAML parser.

```
.toolkit-deploy/
  secrets.env      # gitignored: CPANEL_AUTH=user:pass
  state.json       # gitignored: last deployed commit + verification status per env
```

### Commands

- **`diff <env>`** — dry run. Computes `git diff --name-only <last-deployed-commit-for-env> HEAD -- wp-content/themes/eduma-child`, prints the file list. No network calls, no writes.
- **`deploy demo`**:
  1. Compute changed files via the same git diff as `diff`.
  2. Fetch each changed file's *current remote* content via
     `Fileman/get_file_content` and write it to
     `rollbacks/demo-pre-<version>/<path>` (mirrors today's manual backup
     step, but automatic and complete).
  3. Compute the next release version (`YYYY.MM.DD.N`, auto-incrementing `N`
     for additional deploys same day) and rewrite the `return '...';` line in
     the local working copy of `functions.php` (staged for upload, not
     committed automatically — see Error handling).
  4. Upload changed files via `Fileman/upload_files`, non-`functions.php`
     files first, `functions.php` last (matches existing convention: assets
     and templates land before the file that activates them).
  5. Verify (see below).
  6. On success, record `{commit, version, verified: true, timestamp}` in
     `state.json` for `demo`.
- **`deploy production`**:
  - Same steps 1–5, but step 0 checks `state.json`: if `demo.commit !=
    HEAD` or `demo.verified != true`, refuse with an explicit error naming
    what's missing ("demo is on <x>, HEAD is <y> — run `deploy demo` first"
    or "demo deploy exists but did not pass verification").
  - On success, records the same shape under `production` in `state.json`.
- **`rollback <env> <version>`** — reads `rollbacks/<env>-pre-<version>/`,
  re-uploads every file found there to its original relative path (functions.php
  last, same ordering rule), then re-verifies.

### Verification (`verify.py`)

For each configured smoke route in `config.yaml` for the environment:

1. Request with a cache-busting query parameter. Assert HTTP status is in the
   expected set (200 for normal routes; a route can declare `expect_status`
   explicitly, e.g. 404 for an intentionally-disabled page) and, if the route
   declares `expect_release: true`, that the `X-Toolkit-Release` header
   matches the version just deployed.
2. Wait briefly, then request the **bare** URL (no cache-busting) and assert
   the same status/release. This is the check that catches "WordPress is
   correct but the cache is stale" — exactly today's bug class.
3. Any failure aborts verification (deploy is left uploaded but marked
   `verified: false` in state — see Error handling) rather than silently
   passing.

`config.toml` ships a starter route list: homepage, `/our-ventures/`,
`/our-ventures/toolkit-courses-apply-today/`, and `/speak-up/` (with
`expect_status` set per environment, since production and demo can
legitimately differ on this one while the flag rollout is in progress).

### Error handling

- If any upload in a deploy fails partway through, the command stops
  immediately, prints exactly which files succeeded vs failed, and does
  **not** update `state.json` (so a retry/rollback decision is left to a
  human, matching this project's existing "a timeout is never treated as
  success" principle).
- The release-marker bump is applied to the **local working tree file**
  before upload (so what's uploaded matches what's on disk), and the CLI
  prints a reminder to commit that bump — it does not auto-commit on the
  user's behalf.
- `rollback` re-verifies after restoring, so a rollback that doesn't
  actually fix things is visible immediately rather than assumed to have
  worked.

### Credentials

`.toolkit-deploy/secrets.env`:
```
CPANEL_AUTH=bfyigiln:<password>
```
Read once at process start via `config.py`; never logged, never written to
`state.json` or any file under `rollbacks/`/`reports/`. `.toolkit-deploy/` is
added to `.gitignore` alongside the existing `/rollbacks/` and `/reports/`
entries.

## Testing

No live-network CI here (shared cPanel hosting, no runner). Testable in
isolation without touching the network:

- `release.py`'s version-bump logic (given today's date + existing marker,
  produce the correct next version) — pure function, unit-testable.
- `config.py`'s TOML/env loading and validation (missing required fields
  raise a clear error).
- `state.py`'s read/write round-trip and the demo-before-production gate
  logic in `deploy.py` (given a mocked state.json, does it correctly
  allow/refuse production deploys).

`cpanel.py` and `verify.py` (the actual network-calling pieces) are exercised
manually against demo before every real production deploy — which is exactly
what the tool itself enforces as a gate, so this mirrors production usage.

## Bootstrap

Demo and production are already at commit `dec1b6e` / release `2026.08.07.2`
as of this spec, deployed manually before this tool existed. `state.json`
does not start empty: the first thing the tool does on first run (or a
one-off `toolkit_deploy bootstrap` command) is seed both environments'
entries with `{commit: "dec1b6e", version: "2026.08.07.2", verified: true,
timestamp: <now>}`, sourced from `PROGRESS.md`/`reports/MASTER-HANDOFF.md`'s
existing manual records. Without this, the tool would compute a diff against
the beginning of history on its first real run.

## Rollout

1. Bootstrap `state.json` as above.
2. Build the tool against demo only first, using it for the *next* real demo
   deploy as a live test.
3. Once a demo deploy and a demo `rollback` have both been exercised
   successfully, use it for a production deploy behind the enforced gate.
4. Retire the manual `curl`/session-transcript workflow once the tool has
   handled a handful of real deploys.
