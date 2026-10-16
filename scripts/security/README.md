# Browser-free security operations

This directory retains narrowly scoped, reviewable scripts for remote security
containment. They use cPanel's API from the terminal and never attach to an
operator's browser session.

Credentials must not be committed. Supply `CPANEL_AUTH` in the environment or
in the ignored `.toolkit-deploy/secrets.env` file, using the same format as the
scripts under `scripts/deployment/`.

`contain-production-2026-08-20.sh` is an incident-specific operation. It backs
up production's current root `.htaccess`, prepends filename rules denying the
abandoned install's exposed `readme.html` and `toolkitiskills.zip`, and installs
a deny-all `.htaccess` inside the vulnerable Eventer plugin directory. It then
fetches both rulesets back for byte comparisons and checks affected and
unaffected public routes. It does not delete files, deactivate plugins in the
database, remove users, or rotate credentials. Review the ignored rollback
snapshot before any manual restoration. The Eventer `.htaccess` was newly
created during containment and therefore has no pre-incident file to restore.

The 16 October 2026 asset-disclosure investigation is retained separately:

- `collect-asset-incident-2026-10-16.php` is CLI-only. It preserves filesystem
  metadata/checksums, plugin versions, a read-only account/media inventory and
  bounded access-log copies without bootstrapping WordPress. It writes only to
  the specified private incident directory and refuses to overwrite its summary.
- `run-asset-followup-2026-10-16.py collect` expands the two already-preserved
  main-site October archives privately and retrieves bounded chunks with a final
  checksum comparison. Each cron run has a unique completion log and is removed
  in `finally`; an older successful run cannot satisfy a new run's completion.
- `contain-demo` and then `contain-production` move only the identified
  `uploads/toolkit-deploy-backups` directory outside the webroots. The private
  manifest retains every filename, byte count and checksum. Production requires
  the demo containment manifest and the initial forensic PHP checksums to match.
  These operations have already completed; inspect evidence rather than rerun.
- `analyze-asset-access-logs.py EVIDENCE_DIRECTORY` summarizes saved main-site
  logs locally. It omits IPs and query secrets and labels overlapping logs as
  observations rather than unique requests. Logs do not establish authorization
  or show POST bodies; a 200 response is not proof of code execution.

These are incident-specific operations, not a general vulnerability scanner.
Raw logs, account inventories and rollback files remain ignored/private. No
production write payloads or trial visitor/admissions records were submitted.
