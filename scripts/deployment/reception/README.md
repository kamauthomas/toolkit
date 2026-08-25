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
