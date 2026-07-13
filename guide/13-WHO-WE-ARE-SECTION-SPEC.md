# 13 — Who We Are Section Spec

Implements `12-SECTION-LAYOUT-SPECS.md` for the **Who We Are** block + the IIW banner image immediately above it. All tokens referenced come from `brand-tokens.css`. Do not invent one-off values.

## Inventory (Elementor post 4519, verified via curl + `_elementor_data`)

### Section A — IIW banner image (id `cc89460`, widget `30fed85`)
- A single linked image: `toolkit-at-the-International-Institute-of-welding-2025-2.png` (1600×200 → rendered 640×80), wrapped in an `<a>` to the Canva IIW general-assembly presentation.
- Currently full-width, no padding, no rounded corners, no caption. Reads as a thin, bare progress-bar-like strip jammed between the hero-features grid and the Who We Are section.

### Section B — Who We Are (id `a0c8726`, section, structure 22 / 1300px content)
- **Outer** `a0c8726`: 2 columns — left col `f737ca6` (66%, inline 69.1%); right col `34de0bc` (33%, inline 30%).
- **Left col `f737ca6`** contains an inner section `2c489ce` (gap=no, structure 20) split into:
  - Sub-col `58415e4` (50%): YouTube video widget `a5c311a` (`LmZhEabXyUc`, aspect 4:3) → "Our Vision" heading `e6a8b17` → vision paragraph `676f728` → "Our Mission" heading `69004c7` → mission paragraph `87fe158`.
  - Sub-col `bf32b68` (50%): "Who We Are" heading `11bd7c0` → icon-box `38da0e4` with the about paragraph.
- **Right col `34de0bc`** contains the Quick Links panel (inner section `c3ca692`, 500px min-height, dark bg). Holds: "Quick Links" heading → "Toolkit Courses: Apply Now" button → "Contact Us" button.

## What's wrong (observed)
1. **IIW image (`cc89460` / `30fed85`)** has no container padding, no border radius, no constrained width — it brestches edge-to-edge and disappears visually as a banner; reads as a misplaced progress bar rather than a marquee/award strip.
2. **Video `a5c311a`** is square 4:3 with no rounded corners; sits flush against the headings.
3. **"Our Vision" / "Our Mission" / "Who We Are" headings** use the parent `.sc_heading` style which renders the `.line` span as a full-width thin line — there is **no shared orange underline token** (`--brand-heading-underline-width: 40px`), so they don't match the hero CTA / section heading treatment.
4. **Right column `bf32b68`** (Who We Are paragraph) ends right after the about copy — vertical dead space below the paragraph while the left column carries video + 2 h3/p pairs. Spec `12` calls this out: the column imbalance reads as awkward.
5. **Quick Links panel `c3ca692`** already restyled to charcoal (`#1e1e2a`) in an earlier pass — confirm still on-brand. Background element `iskill-banner-04.jpg` is an overlay at 0.7 dark opacity; visually fine.
6. No section padding above/below the outer `a0c8726` beyond `30px 0 30px 0` — visually cramped against neighbors (hero-features above, Our Impact below).

## Target (build against `12-SECTION-LAYOUT-SPECS.md`)

### IIW banner image block (`cc89460` / `30fed85`)
- Constrain image width: `max-width: var(--brand-container-max, 1170px)` centered, with `margin: 24px auto`.
- Apply shared border-radius token to the image: `border-radius: var(--brand-border-radius, 6px)`.
- Add a subtle border and shadow so the strip reads as a card/marquee, not a bare image: `border: 1px solid var(--brand-border-light); box-shadow: 0 1px 4px rgba(0,0,0,0.06)`.
- Make it visually a banner: constrain height (~60–80px), `object-fit: contain` so the IIW wordmark stays legible.
- Add `padding: 8px` inside the wrapper so the rounded border doesn't crop the image.

### Who We Are section (`a0c8726`)
- Section padding: `40px 0 48px 0` (token-friendly vertical rhythm).
- Inner section `2c489ce` keeps the 50/50 split (matches spec's "two-column grid, ~60/40 — we have ~55/45 due to the right-col Quick Links panel, which is fine).

### Video (`a5c311a`)
- Rounded corners: `.elementor-widget-video .elementor-wrapper { border-radius: var(--brand-border-radius, 6px); overflow: hidden; }`. No extra frame.
- 16:9 target — the widget's `aspect_ratio` setting is `43`; ideally flip to `169` via Elementor data, but spec says "16:9" — CSS `aspect-ratio: 16/9` on the wrapper as a no-DB fix.

### Headings (`e6a8b17`, `69004c7`, `11bd7c0`)
- Apply the shared heading-underline token to all `.sc_heading .title` already drafted in `style.css` (`padding-bottom: 12px; ::after { ... width: var(--brand-heading-underline-width); ... }`). Verify these specific ids inherit it (they use `.sc_heading` with `.line` span — the `.line` span should be hidden or replaced by the orange `::after`).
- Hide the parent theme's `.line` span: `.sc_heading .line { display: none; }`.
- Heading color: `var(--brand-text-dark, #333)`; font-size consistent across all three.

### Right column imbalance (`bf32b68`)
- Per spec `12`: **fill the dead space below the "Who We Are" paragraph with a compact 2×2 impact mini-stat grid** (numbers 11190 / 4987 / 3537 / 554 / 2112). This requires inserting new Elementor widgets — a structural change, not just CSS.
- **Decision needed:** add the mini-stat grid (structural change via `_elementor_data`) vs. accept the imbalance. Default action: do NOT fabricate new widgets blind without a visual review — propose the markup, then review with a live render. For now, apply a lighter interim fix: vertically align both columns to `align-items: stretch` so the Quick Links column anchors the height, and the right column text block sits at the top.

### Quick Links panel (`c3ca692`)
- Already restyled in earlier pass. Re-confirm: dark `#1e1e2a` or `--brand-bg-dark`, link text white, arrow icon orange (`--brand-accent-orange`), rounded corners via `--brand-border-radius`.
- Add explicit border-radius and slight inner padding so the buttons don't kiss the panel edge.

## Implementation notes
- **CSS-only first** — changes to `style.css` only, targeting the Elementor element ids above. No DB mutation needed for the IIW image, video rounding, or heading underline.
- **No structural widget insertion** for the right-column mini-stat grid without an explicit go-ahead — that needs a JSON edit to `_elementor_data` and a render review, not a blind insert.
- After editing, bust `_elementor_element_cache` and `_elementor_css` for post 4519 (the stale rendered-snapshot cache bit us last round).

## Verification
- curl the served homepage and assert:
  - IIW image `.wp-image-14088` is wrapped in a rounded, padded, centered container.
  - `.elementor-widget-video .elementor-wrapper` has the rounded-radius rule applied.
  - `.sc_heading .line` is `display: none`.
  - The three "Our Vision" / "Our Mission" / "Who We Are" headings inherit the orange `::after` underline.
  - Quick Links panel and its anchor buttons carry the brand radius/color rules.