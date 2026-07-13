# 03 — Build Steps (ordered checklist)

Do these in order. Check off each one in `PROGRESS.md` as you complete it. Do not batch multiple steps into one uncommitted change — commit after each numbered step per `07-DEPLOYMENT-SAFETY.md`.

## Step 0 — Prerequisites
- [ ] Completed `05-RESEARCH-FILE-RETRIEVAL.md` and identified: active theme path, homepage template file, existing enqueue pattern, existing button/color CSS variables, whether a page builder owns the homepage.
- [ ] Created `PROGRESS.md` from the template in file 08.
- [ ] Confirmed working tree is clean before starting (`git status`). If not clean, stop and log why in PROGRESS.md before touching anything.
- [ ] Created a feature branch (see file 07) before any edit.

## Step 1 — Slide data
- [ ] Create a single small PHP file holding the slide data array (e.g. `inc/hero-slides.php` or inline at the top of the template part — pick whichever matches how the theme already handles similar repeatable content, e.g. ACF fields, theme mod, or a plain array).
- [ ] Populate it with the 3 slides per `02-HERO-SPEC.md`.
- [ ] Do not hardcode slide content inline in the markup loop — always loop over this data source. This is what makes it a real "slider," not 3 copy-pasted static blocks.

## Step 2 — Markup
- [ ] Build the hero template part with one wrapper element and one `<li>`/`<div>` per slide (image + text together, per file 02's "synced" model — one slide element, not two parallel loops).
- [ ] Add all 11 components from the "Component inventory" list in file 02.
- [ ] Use semantic HTML: `<section aria-roledescription="carousel">`, each slide `role="group" aria-roledescription="slide" aria-label="Slide N of 3"`, buttons are real `<button>` elements (not `<div onclick>`), links are real `<a>`.
- [ ] Reuse the theme's existing header/container markup conventions (class naming, wrapper divs) so it inherits site-wide spacing correctly.

## Step 3 — Styling
- [ ] New CSS file scoped to this component (e.g. `hero-slider.css`), namespaced class prefix (e.g. `.hero-slider__*`) to avoid leaking styles into the rest of the theme.
- [ ] Implement layout, color tokens, and responsive breakpoints exactly as specified in file 02.
- [ ] Do not use `!important` unless overriding a theme style that cannot otherwise be reached — if you do, comment why.
- [ ] Do not inline styles in the PHP/HTML except for dynamically-set background-image URLs (that one is acceptable and normal).

## Step 4 — Animation & interaction JS
- [ ] Read `04-ANIMATION-BEST-PRACTICES.md` fully before writing this.
- [ ] New JS file scoped to this component (e.g. `hero-slider.js`), vanilla JS, no new library dependency unless file 05's research says the theme already loads one that fits (e.g. Swiper already present) — in that case use what's already loaded, don't add a second one.
- [ ] Implement: autoplay w/ 6s interval, pause on hover/focus-within, manual pause/play toggle, dot navigation, next-arrow navigation, swipe support, keyboard support (arrow keys when the slider has focus), `prefers-reduced-motion` handling.
- [ ] Implement the video badge's modal/lightbox open behavior.
- [ ] No console errors, no global variable leaks (wrap in an IIFE or module).

## Step 5 — Enqueue
- [ ] Register and enqueue the new CSS/JS in `functions.php` following the theme's exact existing pattern (hook name, version strategy, dependency array).
- [ ] Version the assets using `filemtime()` or the theme's existing versioning convention (not a hardcoded `1.0`) so cache-busting works automatically on future edits.
- [ ] Only enqueue on the homepage template, not site-wide, unless the theme's existing pattern enqueues everything globally (match existing convention either way).

## Step 6 — Below-hero static section
- [ ] Build the 4-column feature strip per file 02. Static, no slider JS needed.

## Step 7 — Verification
- [ ] Run every item in `06-VERIFICATION-CHECKLIST.md`. Fix anything that fails before moving on.

## Step 8 — Cleanup & commit
- [ ] Remove any debug code, `console.log`, commented-out old markup, or temp files.
- [ ] Confirm `git status` is clean except intended files.
- [ ] Follow `07-DEPLOYMENT-SAFETY.md` for commit/push.
- [ ] Update `PROGRESS.md` to status DONE with final verification results.
