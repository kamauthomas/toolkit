# 12 — Section Layout Specs (Who We Are → Footer)

This is the detailed target layout for every homepage section below the hero, written at the same level of specificity as `02-HERO-SPEC.md`. File 10 flagged *what's* inconsistent; this file defines *what it should become*. Build against this, not against guesswork.

All color/spacing/typography tokens referenced here come from the shared token file established in file 10, Part B. Do not redefine one-off values per section.

---

## Section: "Who We Are"

**Purpose:** orient a first-time visitor — who the org is, what they believe, proof (video), and a way to act (quick links).

**Structure:** two-column grid on desktop (roughly 60/40 split), single column stacked on mobile.

- **Left column, top to bottom:**
  1. YouTube video embed, 16:9, rounded corners (shared radius token), no extra frame/border needed.
  2. "Our Vision" — heading with the shared orange-underline-accent style, one short paragraph below.
  3. "Our Mission" — same heading style, one short paragraph below.
- **Right column, top to bottom:**
  1. "Who We Are" heading, same underline-accent style, one paragraph (existing copy is fine).
  2. **Fix the imbalance:** do not leave this column mostly empty below the paragraph. Add a compact stat/highlight block directly under the paragraph — reuse the "Our Impact" numbers (11190 / 4987 / 3537 / 554 / 2112 etc.) as a smaller 2-column or 2x2 mini-stat grid here, rather than only showing them much further down the page in isolation with a large empty gap above them. This solves both problems at once: fills the dead space AND gives the impact numbers proper visual context near the "Who We Are" narrative instead of floating alone.
  3. Below that, the "Quick Links" panel (see restyle below) sits at the bottom of this column, roughly aligned to the bottom edge of the video in the left column so both columns terminate at a similar height.

**Quick Links panel restyle:**
- Replace the solid olive-green background with the shared dark-overlay token (same dark tone used in the hero's gradient overlay, or a dark charcoal/navy — not olive, which doesn't appear as a brand color anywhere else on the page).
- Keep the two-row link layout ("Toolkit Courses: Apply Now" / "Contact Us"), each with a right-arrow icon.
- Link text: white, arrow icon: shared accent orange, so the panel visually ties back to the hero's orange accent instead of introducing a third unrelated color.
- Rounded corners matching the shared radius token.

**If "Our Impact" numbers are moved up into this section:** remove the now-redundant duplicate lower on the page, or if the design intends to show them twice (once contextually here, once as a dedicated full-width "Our Impact" section further down), make that an explicit decision and note it in `PROGRESS.md` rather than leaving an accidental duplicate.

---

## Section: "Our Impact" (if kept as its own full-width section)

**Structure:** centered heading with underline accent, then a 5-column stat row (2-column or single-column stack on mobile), even spacing between columns, no card/box background — numbers sit directly on the page background per the reference.

**Typography:** large bold number in shared accent orange, small bold uppercase label directly below in a muted olive/green tone (this existing color pairing is fine to keep — it's the one place olive-green already reads intentional, since it's used as a secondary accent, not a full background).

---

## Section: "Strategic Sectors" + video

**Structure:** two-column grid — left column: heading + intro line + a stacked list of sector buttons; right column: supporting video/image.

**Sector buttons ("Construction Sector," "Renewable Sector," etc.):**
- Full-width within their column (this part of the current layout is fine).
- Apply the shared button border-radius token (currently flat/square — needs rounding to match the hero's pill-style CTAs).
- Keep solid orange fill, white text, right-chevron icon — just round the corners and apply consistent padding with other buttons site-wide.
- Consistent vertical gap between stacked buttons (shared spacing token).

**Right column video/image:** rounded corners matching shared radius token, no other changes needed.

---

## Section: "Testimonials"

**Structure:** centered heading with underline accent, then a 3-column card grid (stacks to 1 column on mobile, 2+1 on tablet).

**Background:** replace the current pale-yellow tint with either plain white/light-gray, or — if a tint is wanted for visual separation from the white sections above/below — reuse one of the two tint tokens already established in the feature-icon strip (light green or light orange), not a new unrelated color.

**Each card:**
- Video thumbnail (16:9), rounded corners matching shared radius token.
- Name — bold, dark text.
- Role/title — accent orange, directly under the name (this pairing already exists and works well, keep it).
- Quote paragraph below, regular weight, muted dark-gray.
- Even card spacing/gutters (shared spacing token), equal card heights within a row regardless of quote-text length (use grid/flex alignment so uneven text length doesn't create a jagged bottom edge across the row).

---

## Section: "Our Memberships" + "Our Key Partners"

**Structure:** two stacked logo rows under their own sub-headings, each a horizontal carousel/track.

**Fix the arrow-overlap bug (also flagged in file 10):**
- Arrows must sit fully outside the logo track's bounding box — either in the container's side padding/gutter (increase container padding so there's dedicated space for the arrows that never overlaps a logo), or below the row as simple prev/next controls instead of overlaid left/right arrows, whichever requires less structural change to the existing carousel component.
- Restyle both arrows from flat orange squares to the shared circular arrow-button style used in the hero's next-slide control (Part B token reuse) — same size, same white/orange color logic.

**Logo sizing:** normalize all partner/membership logos to a consistent bounding height (they currently vary — some logos read visually larger than others purely due to their source image proportions) so the row reads as one consistent set rather than mismatched sizes.

---

## Section: Footer

**Structure:** dark background (already consistent with the hero's dark-overlay language — keep as-is), three-ish content zones: logo + brand mark, contact details (address/phone/email with icon prefixes), and the embedded map.

**Fixes needed:**
- Apply the shared border-radius token to the map embed's corners.
- Confirm icon-to-text alignment (icon vertical-center against the first line of multi-line address text) is consistent across all three contact rows.
- No major structural change needed otherwise — this section is already the closest to on-brand.

---

## Cross-section consistency pass (do this last, after every section above is individually correct)

- [ ] Every section heading ("Who We Are," "Our Vision," "Our Mission," "Our Impact," "Testimonials," "Strategic Sectors," "Our Memberships," "Our Key Partners") uses the identical heading font-size, weight, and orange-underline-accent treatment.
- [ ] Every button on the page (hero CTAs, sector buttons, quick-links arrows, any others) shares the same corner-radius token — do a visual sweep scrolling top to bottom checking corner rounding specifically.
- [ ] Every rounded-corner media element (videos, images, map) uses the same radius token.
- [ ] No section introduces a color not present in the shared token file from file 10, Part B — the olive Quick Links panel and pale-yellow testimonials background were the two found so far; do a full-page scroll to check for any others before closing this phase.
- [ ] Vertical spacing between sections (padding above/below each section) is consistent — measure, don't eyeball.
