# 02 — Hero Slider Spec (source of truth for visuals/content)

Extracted from the approved reference image. Use these values directly. Do not eyeball the image yourself — this file is the extraction.

## Layout

- Full-width hero, height ≈ 90vh on desktop (min-height: 640px), min-height: 520px on mobile.
- Background: full-bleed slide image, `object-fit: cover`, with a dark gradient overlay (left-to-right, darkest on the left ~60% opacity black fading to transparent by ~55% width) so the left-aligned text stays readable over any photo.
- Content block is left-aligned, vertically centered, max-width ~600px, left padding matches the site's global container padding (check theme's existing container class — do not hardcode a new value, reuse it).

## Per-slide content model

Each slide is a data object with these fields:

```
slide = {
  image: <url>,
  eyebrow: <short uppercase label>,
  heading: <headline, can contain a manual line break>,
  description: <1-2 sentence paragraph>,
  primary_cta: { label, url },
  secondary_cta: { label, url } | null,   // only slide 1 has one in the reference
}
```

Reference slide 1 content (use as real default content, not placeholder lorem ipsum):

- eyebrow: `TOOLKIT FOR SKILLS AND INNOVATION`
- heading: `Empowering Africa Through Skills, Innovation & Leadership`
- description: `Equipping young Africans with hands-on skills and entrepreneurial mindset to create sustainable communities and global impact.`
- primary_cta: label `EXPLORE COURSES`, icon: right-arrow, style: solid orange button
- secondary_cta: label `OUR IMPACT`, style: outline/ghost button, dark background, white border/text

Slides 2 and 3: reuse the same structural pattern with the same button styling. If real copy for slides 2–3 isn't available from any content source, use short, on-theme placeholder text (do not leave lorem ipsum — write something plausibly on-brand about "Our Courses" and "Our Community/Foundation") and flag it clearly in `PROGRESS.md` under Open Questions as "needs real copy from client."

## Color tokens

Check the theme's existing CSS variables/SCSS variables/Customizer color settings FIRST (see file 05) and reuse them if they already match. If none exist, define these as new custom properties scoped to the hero component (do not overwrite global theme variables):

```css
--hero-accent-orange: #ED6E0D;   /* primary CTA fill, eyebrow text, active dot, logo accent */
--hero-overlay-dark: rgba(10, 10, 10, 0.55);   /* gradient overlay over images */
--hero-text-light: #FFFFFF;
--hero-chip-dark-bg: rgba(20, 20, 20, 0.85);   /* video badge, ghost button bg */
```

## Component inventory (every element that must exist)

1. **Eyebrow label** — small, bold, uppercase, orange, above the headline.
2. **Headline** — large, bold, white, 3 lines max, sans-serif (use theme's existing heading font-family).
3. **Description** — regular weight, white/90% opacity, max 2 lines, below headline.
4. **Primary CTA button** — solid orange background, white text, right-pointing arrow icon, pill/rounded corners (~4-8px radius, match theme's existing button radius if defined).
5. **Secondary CTA button** (slide 1 only) — dark/transparent background, white border, white text, no icon.
6. **Slide counter** — vertical mini-widget on the left edge, mid-height: shows current slide number over total (e.g. `01` / thin vertical line / `03`), updates as slides change.
7. **Scroll-down cue** — circular white button with a down-chevron, below the slide counter, anchors/scrolls to the content immediately below the hero on click.
8. **Next-slide arrow** — circular white button, right edge, vertically centered, right-chevron, advances to next slide on click.
9. **Pagination dots** — horizontally centered near the bottom: one dot per slide, active dot solid orange, inactive dots white/semi-transparent, clickable to jump to that slide.
10. **Play/pause toggle** — small icon immediately right of the dots, toggles slider autoplay on/off. Icon swaps between pause (⏸) and play (▶) based on state.
11. **Video badge** — bottom-right corner, dark rounded rectangle containing: circular orange play-button icon, two lines of text ("Watch Our Story" bold, "Play Video" regular), and a duration label (e.g. `02:45`) right-aligned inside the badge with a vertical divider before it. Opens a video (modal or lightbox — reuse theme's existing modal pattern if one exists, else a simple accessible modal).

## Slider mechanics

- 3 slides, autoplay every 6 seconds, pauses on hover and on focus-within (keyboard users), resumes when hover/focus leaves — unless the user has explicitly clicked pause, in which case it stays paused until they click play.
- Transition: crossfade the image (opacity) + slight parallax/scale (subtle Ken Burns, see file 04) while text block fades/slides up on entry. Full detail in `04-ANIMATION-BEST-PRACTICES.md`.
- Dot click, next-arrow click, and swipe (touch) all trigger the same "go to slide N" function — do not duplicate logic per trigger.
- `prefers-reduced-motion: reduce` → disable Ken Burns scale and swap crossfade duration to a near-instant cut; keep dot/arrow functionality.

## Below-hero static section (not part of the slider)

Light gray background, 4-column row (stacks to 2x2 on tablet, 1 column on mobile), each column:

- Circular icon badge (alternating light-green and light-orange tinted backgrounds), simple line icon inside.
- Bold two-line title.
- One-sentence description below.

Content:
1. Industry-Aligned Courses — "Practical training tailored to real-world needs."
2. Hands-on Learning — "Learn by doing through modern workshops & labs."
3. Innovation Driven — "Fostering creativity and problem-solving."
4. Community Impact — "Building sustainable communities across Africa."

## Responsive behavior

- ≥1024px: spec as described above.
- 768–1023px: reduce headline font-size ~25%, video badge moves to stack below dots instead of floating bottom-right, hero min-height 560px.
- <768px: headline font-size ~40% of desktop, description hidden or truncated to 1 line, secondary CTA stacks below primary (full width buttons), slide counter and scroll-cue hidden (low value on small screens, reclaim vertical space), next-arrow hidden (rely on swipe + dots), video badge becomes a small floating pill, not full text.
