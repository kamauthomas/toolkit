# 15 — Eduma Theme Notes

The site being modified runs the **Eduma** WordPress theme (by ThimPress). This is a confirmed fact, not a guess — treat it as a correction/addition to `05-RESEARCH-FILE-RETRIEVAL.md`'s generic process, not a replacement for it. Do the research steps in file 05, but apply this Eduma-specific context while doing so.

## Why this matters right now

The broken icon glyphs in `14-WHO-WE-ARE-BUG-FIXES-ROUND-1.md` (Bugs 2 and 3 — empty circles, solid-bar placeholders instead of real icons) are very likely an Eduma-specific issue: Eduma ships its own bundled icon font(s) and its own shortcode/element system for icon boxes, buttons, and pill-links. If the custom markup built for this project used a generic icon class name (e.g. a random FontAwesome or Themify class guessed from general knowledge) instead of one that's actually loaded by this theme, the glyph will silently fail to render — which matches exactly what's being observed.

## What to check before writing more custom markup

1. **Confirm the theme/child theme:** `wp-content/themes/eduma/` and check for a paired child theme (e.g. `eduma-child`). If a child theme exists, all edits belong there — same rule as file 05, just confirming it applies here.

2. **Identify Eduma's bundled icon font(s):** search the theme directory for `/fonts/` or `/css/` folders containing icon-font files (Eduma has historically bundled Themify Icons and/or FontAwesome — confirm which, and which version, rather than assuming). Find one icon that's already working correctly elsewhere on the live site (e.g. in the existing header or an existing widget) and inspect its exact class name/markup pattern in devtools. Copy that pattern exactly for any new icon rather than writing a class name from memory or general web convention.

3. **Check for a page builder in use.** Eduma is commonly paired with WPBakery Page Builder (Visual Composer) — check the homepage's raw content in the WP admin editor for `[vc_row]`/`[vc_column]` shortcodes or a WPBakery-style block editor UI. If present, this changes the build approach per the failure condition already noted in `01-PROJECT-GOAL.md`: don't hand-roll raw PHP/HTML fighting the builder — use the builder's own icon-box / button / row elements where possible. Eduma likely has a native "Icon Box" or similar element that already wires up the correct icon font automatically — using that instead of custom markup would resolve the Bug 2/3 icon issue as a side effect, for free.

4. **Locate Eduma's theme options / global color settings.** Eduma typically exposes a Theme Options panel (often Redux Framework-based) for global colors, fonts, and layout settings, separate from `theme.json` or plain CSS variables. Check there for the shared color tokens referenced in `10-PHASE-2-HEADER-AND-SITE-WIDE-CONSISTENCY.md` Part B before assuming none exist — Eduma may already define an accent-orange or similar token that should be reused rather than duplicated.

5. **Check Eduma's existing button/pill components.** The Quick Links pills (file 13) and the Strategic Sectors buttons (file 12) may already have a near-match component built into Eduma's shortcode library. Reusing it is preferable to custom-building an equivalent from scratch, both for consistency and because it sidesteps icon-font mismatches like the one currently causing Bug 2/3.

## Working assumption for this bug round

Try the following first, in order, before any other fix for Bugs 2/3 in file 14:
1. Find a working icon elsewhere on the site, confirm its exact class/markup.
2. Check whether Eduma has a native icon-box or button element that could replace the hand-built Quick Links/stat markup entirely.
3. Only if neither of the above resolves it, debug the custom markup's icon-font enqueue directly (confirm the font file loads with a 200 status in the network tab, confirm the class name matches an entry in that font's stylesheet).

## Record findings

Add an "Eduma-specific notes" block to `PROGRESS.md`'s Environment Notes section once these are confirmed:
- Child theme confirmed: `<yes/no + path>`
- Icon font(s) bundled: `<name(s) + version>`
- Page builder in use: `<yes/no + which>`
- Theme Options location for global colors: `<path/menu location>`
- Existing icon-box / button shortcode found: `<yes/no + name>`
