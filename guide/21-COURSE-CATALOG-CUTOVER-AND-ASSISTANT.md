# Course Catalog, Cutover, and Registration Assistant

## Source of truth

Public course data is maintained in `wp-content/themes/eduma-child/inc/course-catalog.php` and was transcribed from `Toolkit Admissions Prospectus 2026.docx` on 14 July 2026. The active catalog contains only:

- Electrical Installation
- Solar Technician Pathway
- Advanced Welding with VR
- Smart Agriculture
- Entrepreneurship Suite
- Digital Skills & Technology
- French & German Languages

Do not publish payment account details or a NITA centre number from the prospectus: both fields are incomplete. Admissions must approve later changes to fees, intakes, examining bodies, and entry requirements before deployment.

## Legacy page disposition

Legacy initiatives and service pages are not courses and must not appear in the course directory. Keep their database records during acceptance testing so rollback remains possible. After stakeholder review:

- Redirect `/courses/` to `/our-ventures/` and `/blog/` to `/toolkit-blog/`.
- Remove the Eventer preview and duplicate pages from navigation and apply `noindex` while pending review.
- Move legacy course pages outside the 2026 catalog to draft only after their owners confirm they are no longer required.
- Do not delete page records during the redesign cutover.

## Main-domain cutover and rollback

1. Confirm a same-day files and database backup and record the current production commit.
2. Deploy the accepted child theme without changing DNS or the WordPress site URL.
3. Smoke-test the packaged logo, catalog, all seven course routes, application entry, contact page, canonical tags, and mobile layout.
4. Purge LiteSpeed only after the files are complete, then verify two requests per route and confirm the second is a cache hit.
5. Switch the main domain only after demo acceptance. Keep the previous child-theme release archive and database backup available.
6. Roll back by restoring the last-known-good theme release first. Restore the database only if the cutover changed data.

The redesign keeps original Elementor content in WordPress. The custom child-theme templates can therefore be removed or the prior theme release redeployed without deleting source content.

## Registration assistant boundary

The first assistant release should be a grounded admissions guide, not an unrestricted chatbot. It should:

- Answer only from the approved catalog, admissions FAQs, location, contacts, and current intake data.
- Compare courses using stated entry requirements, duration, fees, schedule, and learner goals.
- Never invent discounts, payment details, accreditation numbers, placement guarantees, or eligibility decisions.
- Ask for consent before collecting applicant information and collect only fields required by the application.
- Hand structured application data to the server-side Mzizi adapter described in `19-MZIZI-APPLICATION-ADAPTER.md`; never expose Mzizi sessions or endpoints in the browser.
- Offer an admissions handoff when confidence is low or a question falls outside approved content.
- Log consent, source version, validation result, and submission status without storing chat transcripts containing unnecessary personal data.

Implementation order: curated knowledge and course matcher, accessible chat UI, server-side session and rate limits, Mzizi test-tenant submission, admissions acceptance testing, then production activation.
