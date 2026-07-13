# 13 — "Who We Are" Section Spec

This supersedes the improvised "Who We Are" guidance in `12-SECTION-LAYOUT-SPECS.md` (the suggestion to pull the impact stats up to fill empty space). A real reference design now exists for this section — build against this file, not against file 12's guesswork. File 12 still applies to every other section it covers.

Sits directly below the existing promotional banner (the welding-conference banner at the top of the page). That banner is unchanged/out of scope for this file.

## Overall structure

Two stacked blocks:

1. **Top block** — 3-column row: video card / text column / Quick Links card.
2. **Bottom block** — one full-width light-gray rounded panel containing: Our Vision + Our Mission (left) and a 5-item stat row (right), separated by a vertical divider line.

Both blocks share the same outer container width/padding as the rest of the page's content sections.

---

## Top block

### Column 1 — Video card
- Black rounded-rectangle container (shared radius token), 16:9-ish video thumbnail as background.
- Top overlay bar (semi-transparent dark strip across the top of the thumbnail): small circular channel-logo icon on the left, two lines of white text next to it — bold title line ("The Toolkit iSkills TTI Ltd") and a smaller regular-weight subtitle line ("The Toolkit Skills & Innovation Hub").
- Small square logo badge, white rounded-square background, pinned to the top-right corner of the video card (a secondary/alternate logo mark, distinct from the channel icon on the left).
- Center: large circular solid-orange play button with a white play-triangle icon, centered over the thumbnail.
- Bottom-left overlay: a dark semi-transparent rounded pill containing "Watch on ▶ YouTube" (YouTube's red play-icon badge inline before "YouTube"), sitting on top of the thumbnail near the bottom edge.
- This is a real YouTube embed (click-to-play thumbnail), not a static image — clicking anywhere on the card should play the video (either inline or via YouTube's standard lightbox behavior).

### Column 2 — Text column
- Eyebrow label: `WHO WE ARE` — bold, uppercase, small, shared accent orange, with the shared short-underline-accent beneath it (same treatment used elsewhere on the page).
- Heading, two lines, large and bold:
  - Line 1: `Empowering Youth.` — dark/near-black text.
  - Line 2: `Building Futures.` — shared accent orange text.
  - Both lines same font-size/weight, only color differs. This is a single heading element with two `<span>`s for color, not two separate heading tags.
- Body paragraph below, regular weight, dark gray, using the existing "Who We Are" copy:
  > The Toolkit for Skills and Innovation is a Kenya-based social enterprise founded in 2014 with the goal of disrupting youth unemployment. The Toolkit trains vulnerable youth and women, certifies their skills with regulatory bodies, and then links them to employment or entrepreneurship.
  - Note: "Toolkit" is bolded inline once within the paragraph in the reference — replicate that inline emphasis, not the whole paragraph bolded.

### Column 3 — Quick Links card
- Rounded-rectangle card, light neutral background with a low-opacity background photo behind it (subtle, not distracting — a workshop/sparks-type image at low opacity tinted toward the card's base color, not full brightness).
- Heading: `Quick Links` — bold, dark, with the shared short-underline-accent beneath it.
- Two stacked pill-shaped link buttons, each spanning most of the card width:
  1. White circular icon badge (graduation-cap icon) on the left, olive-green pill body, bold white label text `TOOLKIT COURSES: APPLY NOW`, orange right-arrow icon at the pill's right end.
  2. Same structure: white circular icon badge (headset/support icon), olive-green pill, label `CONTACT US`, orange right-arrow icon.
- Each pill is a full clickable link/button, not decorative — icon, label, and arrow all sit inside one clickable area.

---

## Bottom block — light-gray panel

One full-width rounded panel (shared radius token, light neutral-gray fill) containing two zones side by side, separated by a thin vertical divider line:

### Left zone — Our Vision / Our Mission (2 columns within this zone)
Each column:
- Circular icon badge, light olive/tan tint background, olive-green line icon inside (eye icon for Vision, target/bullseye icon for Mission).
- Heading below the icon: bold, olive-green text, with the shared short-underline-accent beneath it.
- Short paragraph below the heading, regular weight, dark gray:
  - Our Vision: "A leader in powering Africa with skilled, confident, and productive youth."
  - Our Mission: "We transform vulnerable youth to prosperity through innovation and skills for current and future labour markets."

### Right zone — Impact stat row (5 columns)
Each column:
- Circular icon badge, light peach/orange tint background, orange line icon inside (people icon, hardhat icon, laptop icon, document icon, leaf icon — one per stat, matching its subject).
- Large bold number below the icon, shared accent orange (e.g. `11,190`, `4,987`, `3,537`, `554`, `2,112` — use comma-formatted numbers as shown, not raw unformatted digits).
- Small bold uppercase two-line label below the number, muted gray, centered (e.g. `TOTAL YOUTH IMPACTED`, `CONSTRUCTION SECTOR`, `DIGITAL SKILLS & ONLINE JOBS`, `CONSULTANCY & RESEARCH`, `ORGANIC FARMING`).

---

## Color tokens needed for this section

Add these to the shared token file from `10-PHASE-2-HEADER-AND-SITE-WIDE-CONSISTENCY.md`, Part B, rather than defining them locally in this section's stylesheet:

```css
--olive-green: <sample from reference>;       /* Quick Links pill fill, Vision/Mission heading text */
--olive-green-tint-bg: <light tint>;           /* Vision/Mission icon badge background */
--peach-tint-bg: <light tint>;                 /* impact stat icon badge background */
```

Reuse existing tokens for everything else: shared accent orange (pill arrows, stat numbers, eyebrow label, underline accents), shared radius token (video card, Quick Links card, bottom panel), shared muted-gray for body/label text.

Note: this section is what makes the olive-green color legitimate as a real brand token (it wasn't, in the earlier flagged "Quick Links" olive panel from file 10 — that flag assumed olive was an accidental one-off. It isn't; it's an intentional secondary accent used consistently here for Vision/Mission + Quick Links. Update file 10's guidance: keep olive-green in the token set as a defined secondary color, do not strip it out.)

---

## Responsive behavior

- **≥1024px:** 3-column top row and 2-zone bottom panel as described.
- **768–1023px:** top row becomes 2 columns (video + text stacked full-width on top, Quick Links card full-width below them) or stacks fully — choose based on which reads cleaner at this width, test both. Bottom panel: Vision/Mission stays 2-column, stat row wraps to a 3+2 or 2x3 grid instead of 5-across.
- **<768px:** everything stacks to a single column, in document order: video → eyebrow/heading/paragraph → Quick Links card → Our Vision → Our Mission → stat row (stats stack 2-column or single-column, whichever avoids cramped number/label text).

---

## Verification additions

Add to this phase's checklist (alongside `06-VERIFICATION-CHECKLIST.md`):
- [ ] Two-tone heading renders as one heading element with correct per-line color, not two separate misaligned headings.
- [ ] Quick Links pills are fully clickable (whole pill area, not just the text) and link to the correct destinations.
- [ ] Video card's YouTube embed actually plays on click, not just a static styled image.
- [ ] Olive-green and peach-tint tokens added to the shared token file, not hardcoded locally.
- [ ] Vertical divider in the bottom panel aligns cleanly between the two zones at every tested breakpoint down to the point where the panel switches to single-column (divider should disappear/become a horizontal rule once zones stack, not remain as a vertical line with nothing to divide).
