# 11 — Header Nav Fix: Round 2 (Overflow/Wrap + Floating Search)

Phase 2 (file 10, Part A) fixed the missing logo and menu text. This round fixes why the nav now looks crowded: it's wrapping onto two rows and has a floating, disconnected search element.

## Bug 1: Nav wraps onto two rows instead of one

**Observed:** `HOME / ABOUT US / OUR COURSES / IMPACT AND INSIGHTS / TOOLKIT BLOG / NOTICE BOARD` render on row one; `THE TOOLKIT FOUNDATION` and the `CONTACT US` button drop to a second row below. This makes the header roughly twice as tall as it should be and reads as cluttered.

**Reference behavior:** all of the above, plus a search icon, language selector, and CTA button, fit on a single row at desktop width.

**Diagnosis steps (do this before changing values blindly):**
1. Inspect the nav container in devtools. Check whether it's one `<ul>` with all items, or two separate menu renders stacked vertically (a primary menu + a second "utility" menu) — if it's two separate `wp_nav_menu()` calls in two separate rows by design, that's a structural decision, not a wrap bug, and needs to be merged into one row intentionally rather than "fixed" as a wrap issue.
2. If it is one flex/nav container: check `flex-wrap` on the nav's direct child list — if it's `wrap` (or unset, which defaults to `nowrap` but something is forcing wrap), that's the cause once total item width exceeds container width.
3. Check the computed width being consumed by each nav item — likely culprits are: `padding` or `margin` between items being larger than intended, `font-size` larger than the reference design, or `letter-spacing` inflating each label's width.
4. Check the nav container's `max-width` — if it's inheriting a narrower content `max-width` (e.g. the site's article/content container) instead of a full-bleed header width, that alone would force wrapping even with correct item spacing.

**Fix:**
- [ ] Set `flex-wrap: nowrap` on the desktop nav row (only allow wrap/collapse-to-hamburger below the tablet breakpoint, per existing responsive convention elsewhere in the theme).
- [ ] Reduce inter-item spacing/padding to the minimum needed for readability — audit against the reference image's visual density, don't guess a number.
- [ ] Confirm header container width is full-width (or matches the site's widest section width, e.g. the hero's container), not the narrower body-content width.
- [ ] If after tightening spacing it still doesn't fit at common desktop widths (1280–1440px), reduce nav font-size by 1 step rather than letting it wrap — a slightly smaller single-row nav reads more professional than a wrapped two-row one.

## Bug 2: Floating "Search" element outside the header

**Observed:** a magnifying-glass icon + "Search" text renders above the header bar entirely, disconnected from the nav row, near the browser chrome edge of the page.

**Diagnosis:** this points to the search element being injected in the wrong template location — either hooked to `wp_head`/before the header opens, or it's a leftover admin-bar-adjacent element, or it's using `position: fixed`/`position: absolute` with coordinates computed against the wrong ancestor (same category of bug as the hero's Round 1 orphan-circle issue in file 09).

**Fix:**
- [ ] Locate where this search element is actually enqueued/output (search the theme for the markup containing "Search" placeholder text or the search icon class).
- [ ] Move it into the header's nav row as a proper icon button, positioned after the last menu item and before the language selector/CTA button (per the reference layout).
- [ ] Clicking it should open the theme's existing search UI (modal, dropdown, or expand-in-place — check what the theme already has rather than building a new search experience).

## Bug 3: Missing language selector

**Observed:** no "EN" language dropdown, present in the reference design.

**Fix:** only add this if the site has an actual i18n/translation setup (check for WPML, Polylang, or similar). If none exists, do not fabricate a non-functional dropdown — log in `PROGRESS.md` that the reference design assumed multilingual support that isn't present, and flag it as a decision needed (remove from design vs. add real i18n).

## Bug 4: CTA button label mismatch

**Observed:** button reads "Contact Us." Reference design (original screenshot) read "Apply Now."

**Fix:** do not silently change this either direction. Log in `PROGRESS.md` under Open Questions — this is a content/intent decision (is this button meant to drive applications or contact inquiries?), not a styling bug.

## After fixing

- [ ] Header renders as a single row at desktop widths (1280px and up minimum — test at 1280, 1440, 1920).
- [ ] Search icon is inline in the header row, opens working search UI, no floating/disconnected elements anywhere on the page.
- [ ] Re-run the header checklist from `10-PHASE-2-HEADER-AND-SITE-WIDE-CONSISTENCY.md` Part A in full, not just these new items — confirm nothing regressed.
- [ ] Screenshot the header alone and compare directly against the original reference image side-by-side before marking this closed.
