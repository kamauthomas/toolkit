# Gallery Design Review

**Status:** Approved and deployed to demo on 28 July 2026

**Review server:** port `8097`

## Stakeholder correction

The earlier photo-zine/watch-room release was judged overworked and outside
Toolkit's established brand. The replacement deliberately returns to the
approved palette:

- orange `#ff6600`;
- olive `#969e2a` and strong olive `#70771f`;
- peach tint `#fde8d0`;
- olive tint `#f3f4e9`;
- dark text `#333333`; and
- white/neutral surfaces.

## Image gallery concept

The gallery is a vintage journey wall rather than a conventional grid.
Photographs appear mounted on a warm textured board with physical pins,
field-note captions, numbered stops and a subtle dashed journey path. The hero
uses the Toolkit olive overlay and orange accent. The layout remains
keyboard-accessible through native buttons and a dialog lightbox.

## Video gallery concept

The video page is visually separate but still brand-consistent. Its revised
local interaction uses one primary 16:9 player and an adjacent playlist rather
than loading six equally dominant players. Selecting a thumbnail updates the
main player, title, episode number, active state and live status. Buttons are
keyboard operable, initial playback is user initiated, mobile selection returns
the viewer to the player, and the YouTube player retains captions, playback,
volume and full-screen controls.

This direction follows:

- [YouTube's embedded-player guidance](https://developers.google.com/youtube/player_parameters),
  including a sufficiently large 16:9 player, inline playback and native
  controls; and
- [W3C accessible-media guidance](https://www.w3.org/WAI/media/av/), which
  emphasizes captions and an accessibility-supporting player.

The working design therefore prioritizes one clear viewing task, rapid
thumbnail-based discovery, an unmistakable active selection and less scrolling.

## Review and demo release

- The replacement was approved after local review and deployed to demo as
  release `2026.07.28.6`. Production remains unchanged.
- The local harness intentionally uses a small review navigation. The actual
  WordPress templates retain `get_header()` and `get_footer()`, so the approved
  galleries will use the complete Toolkit site header and footer on demo.
- Local screenshots:
  - `review/gallery-preview/screenshots/vintage-image-journey.png`
  - `review/gallery-preview/screenshots/featured-video-journey.png`
- Demo verification confirmed the single primary player, six playlist choices,
  versioned assets, the complete Toolkit footer and a LiteSpeed cache miss.
- The final video hero was tightened by removing the forced minimum height and
  reducing vertical padding.
- A theme-level button rule found only on the real WordPress demo was corrected
  so inactive playlist choices remain neutral and the selected choice alone
  carries the olive active treatment.
- Production promotion remains separately approval-gated.

## Caption correction

Demo review found that the gallery forced YouTube captions through
`cc_load_policy=1`, producing duplicate subtitles where the source already
contained visible text or the viewer had captions enabled. Release
`2026.07.28.7` removes that forced policy from the initial player and
playlist-selection code. Native YouTube caption controls remain available and
caption preference returns to the viewer.
