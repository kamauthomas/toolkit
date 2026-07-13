# 16 - Launch and SEO Handoff

## Current implementation

- The active work is isolated in `wp-content/themes/eduma-child`; the Eduma parent theme is not modified.
- The homepage has one semantic `h1`. Inactive carousel slides are hidden from assistive technologies until selected.
- The homepage header, hero, feature strip, IIW banner, Who We Are section, inline search, and video card are implemented in the child theme.
- Yoast SEO Premium is present in the local WordPress installation. When it is active, the child theme does not emit competing metadata.
- When no SEO plugin is active, the child theme emits a homepage meta-description fallback using the site's tagline.

## Before deployment

- Activate and configure Yoast SEO, including the organisation name, logo, default social image, homepage SEO title, and homepage meta description.
- Confirm WordPress Settings > Reading discourages search engines only on non-production environments.
- Verify the production canonical URL, XML sitemap, robots.txt, and Search Console ownership after DNS is live.
- Test the homepage and primary navigation at 390px, 768px, 1024px, and 1440px.
- Confirm the header logo image and every primary-menu destination resolve on the deployment environment.

## Known product decisions

- A functional language selector is intentionally not included because no multilingual plugin is configured.
- The fallback header CTA is `Apply Now`; the configured `menu_right` widget can replace it with the approved site CTA.
- The hero video modal remains a placeholder until an approved video URL is supplied.

## Git and release

- Work remains on `feature/hero-slider`; do not merge directly to a production branch without approval.
- Commit child-theme and documentation changes together with a verification note.
- Push requires a configured remote named `origin`; this local clone currently has no remotes configured.
