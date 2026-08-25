# Reception deployment

Production was provisioned on 25 August 2026 at
`https://reception.toolkitafrica.ac.ke` using reception commit `f16790c`.
The private application root is `/home/bfyigiln/reception-system`; the public
document root is `/home/bfyigiln/reception-public`; the vhost uses `ea-php84`.
Production has a fresh database, application/HMAC secrets and an administrator
at `office@toolkitafrica.ac.ke`. Values remain only in the ignored, mode-600
`.toolkit-deploy/` directory; demo secrets and data were not copied.

Use `../lib/cpanel-session.sh` for cPanel calls. It obtains a short-lived `cpsess`
token and private cookie jar because Basic UAPI authentication is rejected.
Never print or commit credentials, cookies, tokens, environments or DB responses.

Release order: test locally; verify demo; back up roots/database/`wp-config.php`;
upload a secret-free archive; install a fresh private environment; publish only
`public/`; migrate/cache with PHP 8.4; verify backend and QR routes; enable the
WordPress signed relay last; then remove archives, jobs and test records.

Production verification passed for backend, staff login, WordPress reception,
visitor QR and applicant QR, including desktop/mobile headless checks. Authorized
staff must delete labelled acceptance record `WEB-260825-JBJXCB`; it is a website
follow-up and does not count as physical attendance.

## Release 2026.08.25.1

`deploy-reception-2026.08.25.1.sh` deploys Reception commit `86b7293` demo
first and production second. It preserves remote files in the ignored rollback
tree, uploads only the reviewed private/public delta, assigns a per-environment
unadvertised staff path in the mode-600 deployment store, runs migrations and
cache clearing through a temporary cPanel cron entry, removes that entry, and
verifies the public page, old-path 404, QR output, private login headers and
administrator-settings authentication boundary. It never prints credentials,
environment contents or the private staff path.

The environment update is staged in a private temporary directory under the
literal filename `.env`; this is required because cPanel's upload API preserves
the local basename. An exit trap removes both that temporary directory and the
exact one-time cron entry if any later deployment or verification step fails.

Deployment completed on 25 August 2026: demo passed first, then the identical
commit passed production. Both logs ended in `RECEPTION_RELEASE_OK` and all
scripted HTTP/security checks passed. Rollback snapshots remain ignored and
owner-restricted on the deployment workstation.
