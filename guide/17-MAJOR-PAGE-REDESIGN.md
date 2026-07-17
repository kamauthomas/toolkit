# 17 - Major Page Redesign

## Pages in this batch

- MIG/MAG Welding: `/our-ventures/construction-sector-skills/`
- Notice Board: `/notice-board/`
- Apply for a Course: `/our-ventures/toolkit-courses-apply-today/`

## Implementation

The child theme owns these page templates. The original Elementor content remains in the database for rollback but is not rendered on the public pages.

- `template-parts/pages/welding.php`
- `template-parts/pages/notice-board.php`
- `template-parts/pages/apply.php`
- `page-redesign.css`
- `page-redesign.js`

## Content policy

Historic intake dates, promotional posters, duplicated application links, and unverified impact claims are intentionally excluded. Replace the static notice cards only with approved, current information.

The application page is a guided handoff to the existing secure admissions portal. Do not add a local personal-data form without a confirmed privacy notice, data owner, retention policy, and admissions workflow.

## SEO

The child theme overrides the Yoast title, description, social title/image, and WebPage schema description for these pages. Re-check final canonical URLs and social previews after the deployment hostname is configured.
