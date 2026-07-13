# 09 — Bug Fixes: Round 1

This is a defect list from comparing the actual rendered output against `02-HERO-SPEC.md`. Work through these in order. After each fix, re-check that specific item visually before moving to the next — do not batch all fixes into one unverified commit.

Read this alongside `06-VERIFICATION-CHECKLIST.md` — these are the specific failures found when that checklist was actually run against the real render.

## Priority 1 — Broken/misplaced elements (functional bugs, not just styling)

### Bug 1: Slide counter widget is split apart
- **Observed:** `01` renders at top, then the eyebrow label text renders on top of/through the widget, then `03` appears floating below the eyebrow — disconnected from the divider line between them.
- **Expected (spec item 6):** `01` / thin vertical divider / `03` as one compact, self-contained widget.
- **Likely cause:** the current-number, divider, and total-number are not wrapped in a single component. The total-number element is in normal document flow and getting pushed down by the eyebrow paragraph instead of being part of the same positioned widget as the current-number.
- **Fix:** wrap all three pieces (`01`, divider line, `03`) in one container, e.g. `.hero-slider__counter`, styled as a flex column, positioned with `position: absolute; left: <container-padding>; top: 50%; transform: translateY(-50%);` — independent of the text content column's normal flow. Verify the eyebrow/heading/description text column does NOT share a parent that pushes this widget around.

### Bug 2: Orphan circle over the headline text
- **Observed:** a solid orange circle floats directly on top of the word "Through" in the headline, with no icon or label.
- **Expected:** no element should render there at all.
- **Likely cause:** an element with `position: absolute` and no correctly positioned ancestor (`position: relative`/`absolute`/`fixed` on a parent) — it is likely computing its offset against the wrong containing block (e.g. `<body>` or the whole hero section instead of its intended button wrapper), or a coordinate value was copy-pasted from another element without adjusting.
- **Fix:** Identify which control this actually is (most likely the pause/play toggle, since that's the other item missing from the dot row — see Bug 4). Move it into its correct parent (next to the pagination dots) and give that parent `position: relative` if it doesn't already have it. Do not just delete it if it's meant to be the play/pause toggle — relocate it correctly.

### Bug 3: Next-arrow button has no icon and wrong color
- **Observed:** solid orange circle on the right edge, no chevron glyph visible.
- **Expected (spec item 8):** white circular button with a right-chevron icon, vertically centered on the right edge.
- **Likely cause:** either (a) the icon markup/SVG/icon-font glyph was never added inside the button, or (b) it was added but is the same color as the button background, making it invisible.
- **Fix:** confirm the icon element actually exists in the DOM (inspect, don't assume). Set button background to white (or the theme's equivalent light surface color), icon color to a dark/neutral tone with sufficient contrast against white.

### Bug 4: Play/pause toggle missing from the dot row
- **Observed:** only dots, no play/pause icon beside them.
- **Expected (spec item 10):** small icon immediately right of the dots that toggles autoplay.
- **Fix:** likely the same element as Bug 2 — relocate it here instead of building a new one. If it truly doesn't exist, add it per spec.

### Bug 5: Scroll-down cue missing entirely
- **Observed:** not present anywhere in the render.
- **Expected (spec item 7):** circular white button with a down-chevron, near the counter widget, scrolls to the section below the hero on click.
- **Fix:** check the DOM first — if it exists but isn't visible, check `display`, `opacity`, `z-index`, and whether it's being clipped by an `overflow: hidden` on a parent that's too small. If it doesn't exist, build it.

## Priority 2 — Sizing/consistency bugs

### Bug 6: Pagination dots inconsistent size (one renders as a stretched oval)
- **Observed:** three small round dots plus one noticeably larger, non-circular (oval) dot.
- **Expected:** all dots identical `width`/`height`, `border-radius: 50%`; only color/opacity should differ between active and inactive states.
- **Likely cause:** the active-state CSS class overrides `width`/`height` independently (or only one of them) instead of only changing `background-color`/`opacity`.
- **Fix:** make active/inactive dot states differ ONLY in color/opacity (and optionally a subtle `transform: scale()` if a size emphasis is wanted — but if so, scale must be uniform on both axes, never stretched).

## Priority 3 — Missing content, not missing elements

### Bug 7: Feature strip below hero shows icons but no text
- **Observed:** the 4 icon badges render, but no titles or descriptions appear beneath them.
- **Expected:** bold two-line title + one-sentence description under each icon, per `02-HERO-SPEC.md`'s "Below-hero static section."
- **Fix:** check the DOM first — if the title/description elements exist but aren't visible, check for zero `height`/`font-size`, white-on-white color, or a leftover `display: none` from a copied component. If they don't exist in the markup at all, add them.

## Priority 4 — Polish (fix after all of the above pass)

### Bug 8: Overlay gradient looks weaker/patchier than spec
- Re-check gradient direction, opacity value, and confirm it's applied to the correct layer (should sit between the image and the text content, not blended into the photo itself in a way that creates uneven patches).

### Bug 9: Headline font doesn't match the theme's heading font
- Likely a CSS specificity conflict or the font-family isn't cascading to this component. Check computed styles in devtools on the live headline element to see which font-family is actually winning, and trace why the intended one isn't.

## After fixing everything above

- [ ] Re-run `06-VERIFICATION-CHECKLIST.md` in full, not just the items listed here — these bugs may have masked other issues that weren't visible while the layout was broken.
- [ ] Update `PROGRESS.md`: log each bug fixed, with the commit hash for each fix (small, separate commits per bug — do not combine all 9 fixes into one commit).
- [ ] Take a fresh screenshot/render after all fixes and do a direct side-by-side against the reference image before marking this round closed.
