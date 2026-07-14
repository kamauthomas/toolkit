# Main-Domain Cutover Inventory

## Fresh crawl

Source: `https://toolkitafrica.ac.ke/wp-sitemap.xml` and `page-sitemap.xml`, fetched 14 July 2026. The sitemap index exposes six sitemaps. The page sitemap contains 39 URLs. The June 2026 research in `web-redesign/SITE_ANALYSIS.md` remains useful for posts, media, duplicate team systems, and historical problems, but this inventory controls the cutover.

## Instant switches

Set these in the target environment configuration, preferably `wp-config.php`. Constants override WordPress options.

```php
define( 'TOOLKIT_REDESIGN_ENABLED', false );
define( 'TOOLKIT_2026_CATALOG_ENABLED', false );
define( 'TOOLKIT_2026_PRICING_ENABLED', false );
```

- `TOOLKIT_REDESIGN_ENABLED`: switches custom page templates, homepage, header/menu, footer, and redesigned assets. It defaults on only for `demo.toolkitafrica.ac.ke` and local port 8001; it defaults off on the main domain.
- `TOOLKIT_2026_CATALOG_ENABLED`: switches the course directory from legacy programmes to the prospectus catalog and enables `/courses/{slug}/`. Default is off.
- `TOOLKIT_2026_PRICING_ENABLED`: reveals prospectus prices only when the 2026 catalog is also enabled. Default is off.
- A course can be hidden independently with `TOOLKIT_COURSE_{SLUG}_ENABLED`. Example: `define( 'TOOLKIT_COURSE_ELECTRICAL_INSTALLATION_ENABLED', false );`.

Equivalent WordPress options are `toolkit_redesign_enabled`, `toolkit_2026_catalog_enabled`, `toolkit_2026_pricing_enabled`, and `toolkit_course_{slug}_enabled`.

## Page URL preservation contract

The following routes must remain available or receive an explicitly tested 301 redirect. Do not delete their database records during rollout.

```text
/
/account/
/research/
/user-register/
/user-login/
/forgot-password/
/reset-password/
/user-account/
/my-account/
/lp-profile/
/lp-term-conditions/
/lp-become-a-teacher/
/lp-checkout/
/instructors/
/instructor/
/privacy-policy/
/our-ventures/organic-farming-skills/building-young-female-farmers-of-tomorrow/
/our-ventures/construction-sector-skills/training-welders-with-virtual-reality/
/our-ventures/construction-sector-skills/recognition-of-prior-learning-rpl/
/students-portal/
/our-ventures/access-online-jobs/
/our-ventures/organic-farming-skills/
/our-ventures/online-training-portal-jielimishe/
/our-ventures/tti-consultancy-and-research/
/our-ventures/toolkit-courses-apply-today/
/eventer-shortcode-preview-page/
/the-toolkit-foundation/
/our-ventures/construction-sector-skills/
/our-ventures/renewable-energy/
/contact/
/the-toolkit-foundation-copy/
/our-ventures/
/toolkit-blog/
/about-toolkit-africa/toolkit-in-brief/
/tti-media/
/gallery-2/
/about-toolkit-africa/
/courses/
/notice-board/
```

The Eventer preview and duplicate Foundation route should be retained during acceptance testing but excluded from navigation and search indexing. Consolidation redirects require a separate approved redirect map.

## Rollout gate

1. Keep all three switches false on the main domain after file sync.
2. Verify legacy pages, forms, login/account flows, LearnPress routes, posts, team profiles, media, and the 39-page sitemap contract.
3. Enable only `TOOLKIT_REDESIGN_ENABLED`; purge LiteSpeed and repeat the route, asset, SEO, form, desktop, and mobile tests.
4. Roll back instantly by setting `TOOLKIT_REDESIGN_ENABLED` to false and purging LiteSpeed.
5. Enable the 2026 catalog only after admissions approval. Keep pricing false until the new fee schedule takes effect.
6. Enable pricing in a separate release with written admissions approval and a dated cache purge.

No main-domain FTP sync, switch activation, DNS change, or database cleanup is authorised until every demo acceptance check passes.
