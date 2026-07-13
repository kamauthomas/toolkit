# 04 — Animation Best Practices

Read this before writing any CSS transition or JS animation for the hero slider. Applies specifically to the slide crossfade, text entrance, and any hover/focus micro-interactions.

## Core rules

1. **Animate only `transform` and `opacity`.** Never animate `width`, `height`, `top`, `left`, `margin`, or `box-shadow` directly on a slide-frequency loop — these trigger layout/paint and cause jank. Use `transform: translate/scale` and `opacity` only.
2. **Prefer CSS transitions/animations over JS-driven animation loops.** Only use JS to toggle classes or set custom properties (e.g. current slide index). Let the browser's compositor handle the actual animation.
3. **Always respect `prefers-reduced-motion: reduce`.** Wrap non-essential motion (Ken Burns zoom, staggered text entrance, parallax) in a media query and provide a near-instant, low-motion fallback. Functional transitions (slide change happening at all) may remain but should shorten to ~150ms opacity swap only.
4. **No layout shift.** The hero must reserve its final height before images load (fixed/min-height per file 02, not height driven by image aspect ratio) to avoid CLS.
5. **Timing:**
   - Slide crossfade: 600–800ms, ease: `cubic-bezier(0.4, 0, 0.2, 1)`.
   - Background Ken Burns (subtle scale 1.0 → 1.06): runs across the full slide duration (6s), `ease-out`, GPU-accelerated via `transform`.
   - Text entrance (heading/description/buttons): fade + translateY(16px → 0), 400–500ms, staggered 60–80ms between eyebrow → heading → description → buttons. Do not stagger per-letter or per-word — that's excessive for this component and hurts perceived performance.
   - Dot/arrow hover states: 150–200ms, no delay.
6. **Only animate the incoming and outgoing slide, never all slides at once.** Slides not currently transitioning should have no active animation properties — use `will-change: transform, opacity` only on the actively-transitioning element, then remove it after the transition ends (don't leave `will-change` on permanently, it wastes GPU memory).
7. **Debounce/guard rapid triggers.** If a user clicks the next-arrow or a dot repeatedly during an in-progress transition, ignore new triggers until the current transition's `transitionend` fires (or use a simple `isAnimating` boolean lock). Prevents overlapping/broken animation states.
8. **Autoplay pause logic is part of "animation," not just "interaction":** pausing must actually halt the interval AND not leave a transition mid-flight — always let an in-progress transition finish before honoring a pause.
9. **Video modal transitions:** simple fade+scale on open (200–250ms), no extra flourishes needed. Ensure it traps focus while open and returns focus to the triggering button on close.

## What NOT to do

- No third-party animation library (GSAP, Anime.js, etc.) unless file 05's research shows one is already loaded theme-wide for other purposes. Pure CSS + minimal vanilla JS is sufficient here and keeps page weight down.
- No autoplaying video or sound.
- No infinite continuous marquee-style motion anywhere in this component.
- No animating on scroll for the hero itself (the hero is above the fold — scroll-triggered entrance animations are for the feature strip below it only, and only if the theme already has an existing scroll-reveal utility to reuse).

## Self-check before moving to verification

- [ ] Only `transform`/`opacity` are transitioned.
- [ ] `prefers-reduced-motion` fallback implemented and manually tested (toggle it in devtools rendering tab).
- [ ] No `will-change` left permanently applied.
- [ ] Rapid-click on next arrow does not break slide state.
- [ ] No new external animation library added.
