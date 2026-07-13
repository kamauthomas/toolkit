# 18 - Demo Deployment

## Confirmed target

- Host: `demo.toolkitafrica.ac.ke`
- FTP account root: the demo WordPress document root
- Theme target: `wp-content/themes/eduma-child/`

## Deployment procedure

1. Verify the local branch is committed and the working tree contains no unrelated changes.
2. Download backups of every existing remote file that will be replaced.
3. Upload only changed child-theme files. Do not upload WordPress core, plugins, uploads, `wp-config.php`, the SQL dump, or local logs.
4. Fetch the affected public URLs and confirm HTTP 200, the expected child-template marker, and the intended asset URLs.
5. Record the deployed commit and affected routes in `PROGRESS.md`.

## Current deployed route set

- `/our-ventures/`
- `/our-ventures/construction-sector-skills/`
- `/notice-board/`
- `/our-ventures/toolkit-courses-apply-today/`

## Security requirement

The current FTPS certificate does not match `demo.toolkitafrica.ac.ke`. This must be corrected before production deployment. Do not store FTP credentials in this repository or deployment scripts.
