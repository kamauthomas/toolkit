# Reception Demo Restart Handoff

**Checkpoint:** 27 July 2026

**Current public result:** `https://reception-demo.toolkitafrica.ac.ke/` returns HTTP 500

**Acceptance status:** Not ready for review

## Completed

- Laravel signed website-request implementation committed as `e8bf6cc`.
- Independent cPanel demo and production application/public directories created
  outside WordPress.
- Demo hostname created and mapped to the independent demo public directory.
- Reviewed application/public archives uploaded and extracted.
- Temporary deployment archives, public installers and cron entries removed.
- Automated Laravel tests passed: 18 tests and 92 assertions.
- Automated local WordPress-to-Laravel request path passed and test records were
  removed.
- Local reception form, image-gallery and video-gallery templates implemented.

## Important qualification

No persistent local review environment was provided. The local PHP, WordPress
and reception processes used for automated checks were stopped. Therefore there
is no locally accessible form or browser-review URL.

The reception form and redesigned `/gallery-2/` and `/tti-media/` templates have
not been deployed to the demo WordPress site.

## Why demo returns HTTP 500

The code files exist, but the demo Laravel runtime has not been completed. The
remaining causes to address are:

- missing private production-style `.env`;
- missing demo `APP_KEY`;
- missing demo database and least-privilege user;
- unapplied migrations and staff seed;
- unconfirmed writable `storage/` and `bootstrap/cache/`;
- uncached/unchecked production configuration; and
- no demo-only website HMAC secret shared privately with WordPress.

## Resume in this order

1. Create the demo database and database user through cPanel.
2. Generate private demo keys locally and upload `.env` outside the public root.
3. Set correct ownership/permissions for Laravel runtime directories.
4. Run migrations and create the authorized demo staff account through a
   private one-time account-owner process.
5. Confirm homepage/login HTTP 200 and no sensitive error output.
6. Test valid signed requests, invalid signatures, stale timestamps and replay.
7. Verify website requests appear as follow-ups and do not count as attendance.
8. Commit the scoped WordPress changes.
9. Back up and deploy the child-theme reception and gallery files to demo.
10. Configure WordPress privately, enable the form last, purge LiteSpeed and
    provide these review points:
    - `https://demo.toolkitafrica.ac.ke/reception/`
    - `https://demo.toolkitafrica.ac.ke/gallery-2/`
    - `https://demo.toolkitafrica.ac.ke/tti-media/`
    - `https://reception-demo.toolkitafrica.ac.ke/staff/login`
11. Record owner acceptance and actual evidence in WMR-13.
12. Do not configure or deploy production before demo acceptance.

## Git and reporting state

- Reception backend checkpoint: `e8bf6cc`.
- WordPress reception, gallery and reporting changes remain uncommitted because
  the WordPress worktree also contains unrelated prior edits that must not be
  absorbed accidentally.
- `PROGRESS.md`, WMR-13, the weekly work plan and the reception integration
  report must continue to distinguish automated checks from accessible review
  environments.
