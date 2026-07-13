# 14 — "Who We Are" Bug Fixes: Round 1

Compares the current render against `13-Who_we_are.md`. Fix in order, verify each visually before moving to the next.

## Bug 1: Video card (Column 1) is missing entirely — highest priority
- **Observed:** the top row shows only 2 columns (text, Quick Links). No video card.
- **Expected:** 3 columns per file 13 — video card is column 1, leftmost.
- **Diagnosis:** check the DOM first. If the column 1 markup doesn't exist at all, it was never added. If it exists but isn't visible, check for `display: none`, `width: 0`, or a grid/flex container still configured for 2 tracks instead of 3 (`grid-template-columns` not updated when this component was built, or a flex container missing the third child's flex-basis).
- **Fix:** add/restore the video card exactly per file 13's "Column 1 — Video card" spec (channel-icon overlay bar, center play button, "Watch on YouTube" bottom pill, top-right logo badge).

## Bug 2: Quick Links icon badges render empty
- **Observed:** the circular badges on each pill are plain olive-tinted circles with no graduation-cap/headset glyph inside, and low contrast against the pill background.
- **Expected (file 13):** white circular badge, icon glyph clearly visible inside, sitting on the olive pill.
- **Likely cause:** see the Eduma-specific note in file 15 — this is almost certainly an icon-font/class issue, not a color issue. Check whether an icon class is even present in the markup, and whether that icon font is actually loaded on this page.
- **Fix:** confirm the icon renders in isolation (test the exact icon class elsewhere on a known-working page) before touching colors. Once the glyph renders, set badge background to white/light and icon color to a shade that reads clearly against olive.

## Bug 3: Impact stat icons render as small solid bars
- **Observed:** each stat's icon badge shows a small solid vertical rectangle instead of a people/hardhat/laptop/document/leaf icon.
- **Expected (file 13):** distinct line icon per stat, matching its subject.
- **Likely cause:** same category as Bug 2 — broken/missing icon glyph, not a styling problem. A solid box in place of a glyph is the typical fallback rendering when an icon-font character code has no matching glyph loaded (wrong font-family applied to the icon element, or the icon font file 404ing).
- **Fix:** verify in devtools what `font-family` is actually being applied to these icon elements, and whether it matches an icon font that's actually enqueued on this page. See file 15 before guessing further.

## Bug 4: Quick Links pill text wraps awkwardly
- **Observed:** "TOOLKIT COURSES:" and "APPLY NOW" render as two separate lines with a large vertical gap between them (same issue on "CONTACT" / "US"), making the two pills uneven heights and the text look broken rather than intentionally two-line.
- **Expected:** compact single-line pills where the text fits normally, OR — if two lines is intentional for the longer label — tight, normal line-height between the two lines (not the exaggerated gap currently shown).
- **Likely cause:** either the pill's text container is too narrow for the font-size at hand (forcing an early wrap), or `line-height` is set much larger than intended for stacked text.
- **Fix:** check computed `line-height` on the pill label text first — if it's inherited from a much larger base value, override it locally. If wrapping is due to container width, widen the pill or reduce font-size slightly, don't just increase line-height as a workaround.

## Bug 5: Stat numbers not comma-formatted
- **Observed:** `11190`, `4987`, `3537` etc.
- **Expected (file 13):** `11,190`, `4,987`, `3,537`.
- **Fix:** format at the data/template level (PHP `number_format()` or equivalent), not by hand-editing each number as a string — these are likely dynamic values (from ACF fields, theme options, or a stats data array), so the formatting needs to happen wherever they're output, not just in the array literal.

## Minor: vertical divider height inconsistency
- The divider between the Vision/Mission zone and the stat row doesn't appear to span a consistent height. Low priority — recheck after Bugs 1–5 are fixed, since the missing video card and broken pills may be affecting the row's overall height calculation and could resolve this incidentally.

## After fixing
- [ ] Re-render and compare directly against file 13's spec, section by section.
- [ ] Confirm no other custom icon elsewhere on the page you've touched in this project shares the same broken-glyph issue — if the root cause is a missing icon font enqueue, it likely affects every custom icon added in this build, not just this section. Check the hero's icons (Vision/Mission-adjacent feature icons, stat icons everywhere) while you're in there.
- [ ] Log the root cause and fix in `PROGRESS.md` once confirmed, since it likely explains icon issues elsewhere too.
