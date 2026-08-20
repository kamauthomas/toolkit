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
