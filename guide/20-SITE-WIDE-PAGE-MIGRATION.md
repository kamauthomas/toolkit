# Site-wide Page Migration

## Rule

Every public page must use the child-theme header, compact footer, responsive spacing, accessible controls, curated SEO metadata, and the site-wide cache/preloader fixes. Course content remains under `/our-ventures/`.

## Completed modern routes

- `/` - homepage hero and Who We Are sections
- `/our-ventures/` - course directory
- `/our-ventures/construction-sector-skills/` - welding course page
- `/our-ventures/toolkit-courses-apply-today/` - application entry
- `/notice-board/` - announcement directory
- `/about-toolkit-africa/` - About Toolkit
- `/the-toolkit-foundation-copy/` - Impact and Insights
- `/the-toolkit-foundation/` - Toolkit Foundation
- `/contact/` - contact information, working form, and location

## Remaining public content routes

- About: `/about-toolkit-africa/toolkit-in-brief/`
- Courses: online training, online jobs, organic farming, renewable energy, consultancy and research, VR welding, RPL, and young female farmers
- Insights: `/research/`, `/toolkit-blog/`, `/gallery-2/`, and `/tti-media/`
- Legal: `/privacy-policy/` and `/lp-term-conditions/`
- Student route: `/students-portal/`

## Functional and duplicate routes

LearnPress account, checkout, profile, instructor, login, registration, and password pages must retain plugin functionality while receiving the shared visual shell and form styling.

The duplicate `/courses/` and `/blog/` routes should redirect to `/our-ventures/` and `/toolkit-blog/`. The Eventer preview page should not be indexable or appear in navigation.

The course directory now follows the 2026 admissions prospectus and links to theme-owned detail routes under `/courses/{course-slug}/`. The old construction, organic farming, RPL, online jobs, and consultancy records remain in WordPress for review and rollback, but are not part of the active catalog. See `21-COURSE-CATALOG-CUTOVER-AND-ASSISTANT.md` before changing their status.

## Content policy

- Remove expired application dates, empty links, old app-store badges, preview content, and duplicated sections.
- Do not publish impact totals unless the organisation confirms the reporting period and current figure.
- Preserve verified history, programme purpose, contact details, partner attribution, and learner stories.
- Use responsive derivatives rather than full-size uploads where available.
