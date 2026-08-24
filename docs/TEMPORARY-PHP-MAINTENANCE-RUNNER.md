# Temporary PHP maintenance runner

This procedure is the fallback when WP-CLI is unavailable on the cPanel host. It keeps applicant data on the server and emits aggregate counts only.

## Safety contract

- Confirm a completed cPanel backup before running.
- Upload the runner outside `public_html`; it refuses non-CLI execution.
- The runner raises only its own CLI process memory limit to 512 MB so a normal WordPress/plugin bootstrap can complete; it does not alter the website PHP configuration.
- Dry-run is the default and cannot update rows.
- Live migration requires both `--execute` and `TOOLKIT_MIGRATION_EXECUTE=YES`.
- Every candidate payload is decrypted, re-encrypted with the current dedicated key, and round-trip compared before an update.
- Updates use `WHERE id = ? AND payload = ?` compare-and-swap protection.
- Do not remove legacy keys until dry-run, execution, and a final dry-run all report zero failures/conflicts.
- Remove the temporary cron, runner, and output file immediately after verification.

## Procedure

1. Identify the host PHP CLI binary using a short-lived private cron probe.
2. Upload `scripts/maintenance/application-encryption-runner.php` to `/home/bfyigiln/`.
3. Run the runner without arguments and review its aggregate JSON output.
4. Proceed only when `ok` is true and `failed` and `conflicts` are zero.
5. Run the live migration with:

   ```sh
   TOOLKIT_MIGRATION_EXECUTE=YES PHP_BINARY /home/bfyigiln/application-encryption-runner.php --execute
   ```

6. Run the default dry-run again. All records should be counted under `already_current`.
7. Verify an applicant detail record in WordPress administration without exporting or logging personal data.
8. Remove all temporary server files and cron entries, then confirm their absence.

## Output fields

- `scanned`: encrypted application rows inspected.
- `already_current`: rows already using the configured current key ID.
- `eligible`: legacy rows successfully decrypted and round-trip verified.
- `migrated`: rows updated during execute mode.
- `failed`: rows that could not be decrypted or round-trip verified.
- `conflicts`: rows changed concurrently and therefore not overwritten.

The runner must never print decrypted applicant payloads, encryption keys, database credentials, or raw SQL data.

## Production execution record — 2026-08-24

- cPanel full-backup gate: complete before migration.
- Runtime: `/opt/cpanel/ea-php83/root/usr/bin/php`, matching the production `ea-php83` handler.
- Pre-migration dry-run: 16 scanned, 16 eligible, 0 failed, 0 conflicts.
- Guarded execute: 16 scanned, 16 migrated, 0 failed, 0 conflicts, exit code 0.
- Post-migration dry-run: 16 scanned, 16 already current, 0 failed, 0 conflicts, exit code 0.
- Current key ID: `application-2026-08-24`.
- Temporary cron entries, runner, probe and aggregate output files were removed and their absence verified.
- No applicant payloads, encryption keys or database contents were downloaded or included in logs.
