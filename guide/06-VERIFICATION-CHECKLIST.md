# 06 — Verification Checklist

Run this after Step 7 of `03-BUILD-STEPS.md`, and re-run the whole thing before marking `PROGRESS.md` as DONE. Do not self-report success without actually checking each box — visually render/screenshot or otherwise inspect real output for the visual items, don't infer from reading your own code.

## A. Visual match (compare rendered output against `02-HERO-SPEC.md`)
- [ ] All 11 components from the "Component inventory" are present on slide 1.
- [ ] Eyebrow, headline, description, and buttons match the exact copy specified for slide 1.
- [ ] Orange accent color, dark overlay, and button styles match the color tokens in file 02.
- [ ] Slide counter shows `01` on load and updates to `02`, `03` correctly as slides advance.
- [ ] Video badge shows correct text and duration format, bottom-right on desktop.
- [ ] Below-hero 4-column feature strip renders with correct icons/titles/text.

## B. Functional behavior
- [ ] Autoplay advances slides automatically at the specified interval.
- [ ] Hover over the hero pauses autoplay; moving away resumes it (unless manually paused).
- [ ] Clicking the pause/play toggle actually stops/starts autoplay and the icon swaps correctly.
- [ ] Clicking a dot jumps directly to that slide, and the correct dot becomes active.
- [ ] Clicking the next-arrow advances exactly one slide, wraps from slide 3 back to slide 1.
- [ ] Swiping on a touch device (or emulated touch in devtools) changes slides.
- [ ] Keyboard: tabbing reaches the controls in a logical order; arrow keys move slides when the slider has focus; focus is visible (no removed outline without a replacement focus style).
- [ ] Rapid repeated clicks on next-arrow do not break the slide state (see file 04's debounce rule).
- [ ] Video badge opens the modal/lightbox; modal traps focus; closing returns focus to the badge.
- [ ] Scroll-down cue scrolls to the section immediately below the hero.

## C. Accessibility
- [ ] Slider container has `aria-roledescription="carousel"`, each slide has `role="group"` + `aria-label` with slide position.
- [ ] All interactive controls are real `<button>`/`<a>` elements with accessible labels (not icon-only with no `aria-label`).
- [ ] Images have meaningful `alt` text (not empty, not filename).
- [ ] Color contrast of text over images meets at least WCAG AA (check against the darkest and lightest expected overlay areas).
- [ ] `prefers-reduced-motion: reduce` actually changes behavior when toggled in devtools.

## D. Responsive
- [ ] Test at 375px, 768px, 1024px, 1440px widths. Layout matches the responsive rules in file 02 at each breakpoint (no overlapping text, no overflow, no horizontal scrollbar introduced).
- [ ] No layout shift (CLS) when images load — hero height should not jump.

## E. Performance / optimization
- [ ] No new render-blocking resources: CSS/JS enqueued correctly with proper dependencies, not blocking `<head>` unnecessarily.
- [ ] Slide images are appropriately sized/compressed for hero display (not multi-MB originals) and use `srcset`/responsive images if the theme's existing image pipeline supports it (check WordPress's built-in `wp_get_attachment_image` responsive output).
- [ ] Only the active/adjacent slide images are eagerly loaded if lazy-loading is otherwise the theme's convention; first slide should NOT be lazy-loaded (it's above the fold).
- [ ] No console errors or warnings in the browser devtools console on page load or during interaction.
- [ ] No new external library added unless justified and logged (per file 04/05).
- [ ] Total added CSS/JS payload is reasonable for a single homepage component (rough sanity check, not a hard number — flag if it feels bloated).

## F. Code quality
- [ ] PHP has no syntax errors (`php -l <file>` on every touched PHP file).
- [ ] No debug code (`console.log`, `var_dump`, `print_r`, commented-out dead code blocks) left in.
- [ ] CSS/JS is scoped/namespaced to this component, doesn't leak selectors that could affect unrelated parts of the site (spot-check a couple of other pages visually).
- [ ] Enqueue versioning is dynamic (`filemtime()` or theme's existing convention), not a hardcoded static version string.

## G. Git / working tree
- [ ] `git status` shows a clean tree except intentional, expected changed/new files.
- [ ] No stray temp files, `.bak` files, editor swap files, or duplicate "v2" copies of edited files left behind.
- [ ] Commit history is legible (see file 07 for commit message format).

If ANY box fails, fix it and re-run this full checklist before proceeding — do not mark `PROGRESS.md` DONE with known failing items.
