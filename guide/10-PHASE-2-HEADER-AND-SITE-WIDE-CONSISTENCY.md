# 10 — Phase 2: Header Nav Fix + Site-Wide Consistency Pass

## Scope update

`01-PROJECT-GOAL.md` originally scoped this task to the hero section only, with the header explicitly listed as "reference only, do not edit." That scope is now expanded: the header must be fixed, and the sections below the hero need a consistency pass so the rest of the homepage doesn't look like a different site glued onto the new hero. Update `PROGRESS.md`'s goal statement to reflect this before starting Phase 2 work.

Still out of scope unless separately requested: anything below the homepage (other page templates), the top promotional banner ("TOOLKIT AT THE INTERNATIONAL INSTITUTE OF WELDING 2025"), and page content/copy changes beyond what's needed for visual consistency.

---

## Part A — Header nav bar (fix first, this is the highest-severity item)

### Observed
- Only a graduation-cap icon renders on the left (not the "TOOLKIT" wordmark/logo from the reference design).
- One orange underline element floats in the middle of the header with no menu label above or near it.
- Only one button renders on the right ("Contact Us"), no search icon, no language selector.
- The full menu (Home, About Us, Our Courses, Impact & Insights, Toolkit Blog, Notice Board, The Toolkit Foundation) is completely absent.

### Diagnosis before fixing
The floating underline with nothing above it is the key clue — that's very likely the active-state underline pseudo-element (`::after` or a `.active` border) for a nav `<li>` whose anchor text isn't rendering, rather than the whole menu item being deleted. Before rebuilding anything:
1. Inspect the DOM in devtools. Confirm whether the full `<ul>` of menu items exists with empty/invisible text, or whether it's genuinely missing from the markup.
2. If markup exists but text is invisible: check for `color` matching the header background, `font-size: 0`, or a WP menu walker outputting the link without label text (check whether the WordPress menu location is even assigned — an unassigned `wp_nav_menu()` location can silently render empty `<a>` tags with no text if the fallback isn't handled).
3. If the markup genuinely doesn't exist: check `functions.php` for `register_nav_menus()` and confirm the theme location key used in the header template matches the one the menu is assigned to in the WP admin (a mismatched location slug is the most common cause of a nav silently disappearing).

### Fix checklist
- [ ] Logo renders as the actual wordmark ("TOOLKIT" text + sun-burst icon per the original brand mark), not a generic graduation-cap icon.
- [ ] All 7 menu items render with visible text, correct order, and the active-page underline only shows on the currently active item.
- [ ] Search icon present and functional (opens existing search UI if the theme has one, otherwise flag as needing a decision rather than inventing new search behavior).
- [ ] Language selector ("EN" + dropdown) present if the site is genuinely multilingual (check for an existing i18n plugin/setup — if none, log in PROGRESS.md as "language selector was present in reference but no i18n setup detected, needs a decision" rather than adding a fake dropdown).
- [ ] Right-side CTA button: confirm with `02-HERO-SPEC.md`/reference whether it should read "Apply Now" or "Contact Us" — do not silently guess, log the discrepancy in PROGRESS.md if unclear which is correct for this site.
- [ ] Header vertical spacing matches the density of the reference (current render has excessive empty vertical space around the sparse content — this may resolve on its own once all elements are present, re-check after the above fixes).

---

## Part B — Design tokens: consolidate before touching more sections

Before restyling anything below the hero, pull the hero's color/spacing/button tokens into one shared, reusable source (e.g. `brand-tokens.css` or theme.json entries) rather than letting each section define its own near-duplicate values. This is what makes "blending together" actually happen instead of every section drifting slightly.

- [ ] Extract from the hero CSS: accent orange, overlay dark, text colors, button border-radius, button padding, heading font-family, section heading underline-accent style (the small orange line under "Who We Are" / "Our Vision" / "Testimonials" headings — this pattern already exists site-wide and is good, keep it as the shared heading style).
- [ ] Put these in one file all sections import from. Do not copy-paste values into each section's stylesheet.

---

## Part C — Section-by-section fixes

### "Who We Are" section
- **Layout imbalance:** the right column (heading + paragraph) leaves a large empty white gap below the text while the left column (video + Our Vision + Our Mission) is fully populated — the two columns are visually unbalanced. Fix by either: reflowing to a single column on the text side that doesn't leave dead space, or filling the gap with content that's already elsewhere on the page (e.g. pull the "Our Impact" stat numbers up into this section instead of leaving them isolated further down with a large gap above them too).
- **"Quick Links" panel:** solid dark olive-green background doesn't match the palette established anywhere else on the page (the feature-icon strip uses light green/light orange tints, not solid olive). Restyle to use the shared token palette from Part B — suggest dark charcoal/navy background with orange accent link arrows to match the hero's dark-overlay + orange-accent language.
- **"Our Impact" stat numbers:** already reasonably on-brand (orange numbers). Keep as-is, just confirm spacing/margins match the shared token system once applied.

### Strategic Sectors + Testimonials section
- **"Construction Sector" / "Renewable Sector" buttons:** currently flat, square-cornered, full-width orange bars — visually inconsistent with the rounded pill-style buttons used in the hero ("Explore Courses"). Apply the same border-radius and padding token from Part B.
- **Section background color** (pale yellow): doesn't match the tint palette used elsewhere (light green/light orange). Swap to one of the established tint tokens, or to plain white/light gray, for consistency.
- **Testimonial video cards:** acceptable as-is structurally; apply the shared border-radius token to the video thumbnails so corner rounding is consistent with the rest of the site.

### Memberships / Partners carousel
- **Critical bug:** the left and right arrow buttons are positioned on top of the logos instead of beside/outside the logo track — left arrow currently covers roughly half the NCA logo, right arrow covers the "D" logo. This is a real layout bug, not a style preference — logos are being visually clipped/obscured by interactive controls. Fix by moving the arrow buttons outside the carousel track's bounding box (e.g. absolutely positioned at the far left/right edge of the carousel container, with enough container padding that they don't overlap the first/last item), or reduce the visible item count so there's natural clearance.
- Restyle the arrows from flat orange squares to the same rounded circular style as the hero's next-arrow control (Part B token reuse).

### Footer
- Dark background already matches the hero's dark-overlay language — no major issue.
- Apply the shared border-radius token to the embedded map's corners for consistency with the rest of the site's rounded-corner language.

---

## Verification for Phase 2

Extend `06-VERIFICATION-CHECKLIST.md`'s spirit to this phase — do not mark it done from reading code, actually render and look:
- [ ] Header: all menu items visible and clickable, correct active-state styling, logo correct.
- [ ] No section has a component visually overlapping/clipping another (recheck partner carousel arrows specifically).
- [ ] Button corner-radius, accent color, and heading-underline style are visually identical across hero, "Who We Are," Strategic Sectors, and Testimonials sections (do a side-by-side crop comparison, not a guess).
- [ ] No color appears in only one section that doesn't exist in the shared token file (spot-check the "Quick Links" panel and the testimonials background specifically, since those were the two clearest off-brand colors found).
- [ ] Responsive check at 375/768/1024/1440 for every section touched in this phase, same as required for the hero.

## Progress logging

Log Phase 2 as its own block in `PROGRESS.md` (don't overwrite the Phase 1/hero history) — new "Files touched," new commit log entries, and a fresh Environment Notes addendum if this phase reveals anything new about the menu-location setup or theme structure that Phase 1's research missed.
