# 05 — Research & File Retrieval Best Practices

Do this BEFORE writing or editing any code. Goal: understand the existing theme well enough that your changes look native to it, not bolted on.

## 1. Identify the active theme

- Find `wp-content/themes/` and determine which theme is active (check `wp-content/themes/*/style.css` header comments, or if WP-CLI is available: `wp theme list`, `wp option get template` / `wp option get stylesheet`).
- If a child theme is active, your edits go in the child theme. Never edit the parent theme directly if a child theme exists — that breaks the update path.

## 2. Locate the homepage template

- Check `wp-content/themes/<theme>/front-page.php` first (highest priority for the homepage in WP's template hierarchy), then `home.php`, then `page-*.php` if a static front page is set to a specific page template, then `index.php` as last resort.
- Confirm with the site's Reading Settings concept in mind: if unsure which file actually renders, search the theme for a hero-related string already visible on the live site (e.g. search for existing hero copy, class names like `hero`, `banner`, `slider`, `carousel`) using `grep -ril` across the theme directory. The file(s) that match are your real target.
- If a page builder (Elementor, Divi, Beaver Builder, WPBakery, Bricks, etc.) is active and owns the homepage (its shortcodes/JSON config appear in the page content or a `_elementor_data` postmeta), do not hand-build raw PHP — this changes your approach. Log this in `PROGRESS.md` and follow the theme/builder's existing content pattern instead of fighting it (see the failure conditions in file 01).

## 3. Map existing conventions before introducing new ones

- **Colors:** search for existing CSS custom properties (`--` variables) or SCSS variables or Customizer/theme.json color definitions. Reuse them if they already match the palette in file 02. Don't create parallel/duplicate color tokens.
- **Buttons:** find the theme's existing button classes (search for `.btn`, `.button`, common naming). Reuse the base class and only add modifiers if the existing style doesn't already match file 02's spec.
- **Typography:** find the existing heading font stack (search `h1`, `h2` base styles or `theme.json` typography settings). Do not import a new font unless nothing suitable exists.
- **Asset enqueue pattern:** open `functions.php`, search for `wp_enqueue_style`/`wp_enqueue_script` calls, note the hook used (`wp_enqueue_scripts` typically), the handle naming convention, and versioning method used elsewhere in the file. Match it exactly for consistency.
- **Existing slider/carousel library:** search the theme and its enqueued assets for Swiper, Slick, Owl Carousel, or similar (check `package.json`, enqueued script `src` filenames, or `/assets/` or `/js/vendor/` directories). If one is already loaded site-wide, prefer using it over hand-rolling JS — but only if it can cleanly support the synced image+text behavior in file 02 without excessive workarounds. If it can't, use vanilla JS instead and note why in PROGRESS.md.
- **Container/grid classes:** find how other full-width sections in the theme set their horizontal padding/max-width and reuse that wrapper class rather than inventing a new one.

## 4. Search technique

- Prefer targeted `grep -rn "keyword" wp-content/themes/<theme>` over opening files one by one blindly.
- Useful keyword passes: `hero`, `banner`, `carousel`, `slider`, `enqueue_scripts`, `--color`, `btn`, `button`.
- When a search returns many hits, read the smallest/most specific file first (e.g. a dedicated `_hero.scss` partial beats a 3000-line `style.css`).
- Check file modification recency (`ls -la` / `git log -1 --format=%cd -- <file>`) as a signal for which template is actually in active use versus legacy/unused.

## 5. Record findings

Before starting Step 1 of `03-BUILD-STEPS.md`, write a short "Environment Notes" block into `PROGRESS.md` covering:
- Active theme name + path, child theme yes/no
- Homepage template file path
- Existing color variable names to reuse (or "none found, defining new scoped ones")
- Existing button base class to reuse (or "none found")
- Existing slider library present: yes/no, name
- Enqueue hook + versioning pattern to match
- Page builder in play: yes/no

This block is what lets you (or a different session/agent) resume correctly without re-doing this research.
