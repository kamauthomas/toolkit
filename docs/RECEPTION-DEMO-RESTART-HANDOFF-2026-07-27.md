# Reception Demo Review Handoff

**Updated:** 28 July 2026

**Demo result:** Healthy and ready for user acceptance review

**Production status:** Hold — unchanged pending explicit approval

## Review points

- Website reception form: <https://demo.toolkitafrica.ac.ke/reception/>
- Redesigned image gallery: <https://demo.toolkitafrica.ac.ke/gallery-2/>
- Redesigned video gallery: <https://demo.toolkitafrica.ac.ke/tti-media/>
- Reception Control login: <https://reception-demo.toolkitafrica.ac.ke/staff/login>

The administrator credentials and website HMAC secret are not recorded in Git
or this report. They are retained in the private deployment handoff.

## Completed on demo

- Created the dedicated least-privilege demo database and database user.
- Uploaded a private production-style Laravel environment outside the public
  document root.
- Applied all migrations, seeded the authorized administrator and built
  configuration, route and view caches.
- Corrected hosted MariaDB datetime compatibility in reception commits
  `7ed75ae` and `f16790c`; 18 tests and 92 assertions pass.
- Confirmed reception public and staff login surfaces return HTTP 200.
- Confirmed a valid signed website request returns HTTP 201 and appears as a
  `website` / `follow_up` record in Reception Control.
- Confirmed an invalid signature is rejected with HTTP 401.
- Removed the one-time public setup runner and confirmed it returns HTTP 404.
- Created a private rollback archive before changing the WordPress demo.
- Added the demo-only API URL and HMAC secret to private WordPress
  configuration; WordPress stores no visitor submission copy.
- Deployed the reception form, application dependency and redesigned image and
  video galleries to the WordPress demo.
- Confirmed the WordPress relay returns a generated `WEB-` reference and the
  labelled review record appears in the staff dashboard.
- Recorded the WordPress code in commits `34ce395` and `7b08ae7`.
- Captured headless browser evidence under
  `reports/deployment-evidence/2026-07-28/`.
- Completed a second gallery iteration: the image gallery is now an editorial
  photo zine, while the video gallery is a separate dark watch-room experience.
- Fixed stale releases by retaining WordPress asset version keys, adding a
  release-aware LiteSpeed/object-cache purge and exposing the active release in
  the `X-Toolkit-Release` header. Current demo release: `2026.07.28.4`.

## Review data

Two clearly labelled non-personal verification records remain in the demo
dashboard so the reviewer can confirm staff visibility:

- `Demo Review Record`
- `Website Demo Review`

Delete them after acceptance and before any production data exercise.

## Remaining scope and production gate

1. User reviews all four demo URLs on desktop and mobile.
2. Reception owner signs off field wording, purposes, staff workflow and access.
3. Confirm outbound follow-up procedure, retention owner and backup schedule.
4. Remove the two labelled demo records.
5. Rehearse the documented rollback and record the result.
6. Create fresh production database/application/HMAC/admin secrets; never reuse
   demo secrets.
7. Take same-day production WordPress and database backups.
8. Deploy backend first, migrate and verify it, then deploy the reviewed child
   theme and enable the production form last.
9. Run production smoke tests, remove the labelled production test record,
   monitor errors and rotate the supplied cPanel credentials.

For every future change, increment `toolkit_theme_release()`, upload assets
before `functions.php`, trigger one uncached request and verify the new release
header and cache miss before asking users to review.

### Gallery review qualification

Stakeholder feedback received after release `2026.07.28.4` rejected the current
demo gallery styling as off-brand and overworked. The replacement vintage
journey wall and single-player video gallery were approved locally and deployed
to demo as release `2026.07.28.6`. Both retain the full Toolkit site footer.
Production remains unchanged pending its separate approval gate.

No production configuration, database or public page was changed during this
demo deployment.
