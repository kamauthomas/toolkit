# WordPress Cache Release Procedure

## Issue resolved

The theme previously removed WordPress `?ver=` query strings from CSS and
JavaScript. Browsers therefore treated changed files as the same long-lived
resource, so some machines continued displaying an older release after
deployment.

## Current control

`toolkit_theme_release()` provides a release identifier. Theme asset URLs use
the release plus file modification time, for example:

`page-redesign.css?ver=2026.07.28.4.<file-time>`

On the first WordPress request after a release change, the theme:

1. flushes the WordPress object cache;
2. requests a LiteSpeed full-page purge;
3. requests a LiteSpeed CSS/JavaScript purge;
4. stores the applied release identifier; and
5. returns `X-Toolkit-Release` on redesigned surfaces.

## Deployment sequence

1. Increment `toolkit_theme_release()` in `functions.php`.
2. Upload new templates, CSS, JavaScript and images first.
3. Upload `functions.php` last.
4. Request a changed page with a one-time query, such as
   `?toolkit_release=<release>`.
5. Confirm:
   - HTTP 200;
   - `X-Toolkit-Release` matches;
   - `X-LiteSpeed-Cache: miss` on the release request; and
   - CSS/JavaScript URLs include the new `?ver=` value.
6. Request the clean public URL and visually verify it in a private browser
   window and on a second device/network.

Do not advise users to clear their entire browser cache as the primary release
process. Correct versioned URLs and server-side invalidation should deliver the
new files automatically.
