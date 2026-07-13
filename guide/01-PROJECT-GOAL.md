# 01 — Project Goal & Scope

## Goal (one sentence)

Rebuild the site's homepage hero section as a synced image + text slider that matches the approved reference design, inside the existing WordPress theme, without breaking anything else.

## What "synced image + text slider" means

- There are 3 slides.
- Each slide has: a full-bleed background image, an eyebrow label, a headline, a description paragraph, a primary CTA button, and (slide 1 only) a secondary CTA button.
- When the slide advances (auto, arrow-click, or dot-click), the background image AND the text block change together as one unit. There is no independent "image slider" and "text slider" running separately — they are one slider with per-slide content.
- Full content spec is in `02-HERO-SPEC.md`.

## In scope

- One new/replaced template part for the hero section (e.g. `template-parts/hero-slider.php` or the theme's equivalent hero file — determined during research phase, see file 05).
- Associated CSS file for the hero slider.
- Associated JS file for slider behavior.
- Enqueue registration in `functions.php` (or the theme's existing asset-loading pattern).
- The static feature-icon strip immediately below the hero (4 columns: Industry-Aligned Courses / Hands-on Learning / Innovation Driven / Community Impact) — this is NOT part of the slider, it is a static section directly under it. Build it once, no animation required beyond a simple fade-in on scroll if the theme already does that elsewhere.

## Out of scope — do not touch

- Site navigation/header markup (only reference it to match spacing/colors, do not edit it).
- Any other page template, post type, plugin, or unrelated section.
- Database content, WordPress core files, other themes, mu-plugins.
- Adding new PHP/JS libraries or dependencies unless `05-RESEARCH-FILE-RETRIEVAL.md` confirms none of the theme's existing tooling can do the job.
- SEO, analytics, or tracking code.

## Content source of truth

- Reference image: hero design screenshot (colors, layout, copy all extracted into `02-HERO-SPEC.md`). Do not re-derive values from the image yourself — use the spec file, it is already the extraction.

## Failure conditions — stop and log in PROGRESS.md, do not guess

- The theme has no clear homepage template (multiple candidates, unclear which is live).
- The theme already uses a page builder (Elementor, Divi, WPBakery, etc.) that owns the homepage — in that case the hero may need to be built inside the builder's system, not as raw PHP. Log this and follow the theme's existing pattern rather than fighting it.
- Editing functions.php would conflict with an existing enqueue for the same handle name.

In any failure condition: stop, write the situation clearly under "Open Questions / Blockers" in `PROGRESS.md`, and continue only with unambiguous, in-scope work.
