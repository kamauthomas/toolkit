# Toolkit Social Landing Page

## Purpose

`/connect/` is Toolkit Africa's mobile-first profile landing page. It gives visitors arriving from social profiles one fast route to applications, courses, notices, admissions support, and verified official channels. It is not a replacement for the homepage and is intentionally absent from the primary navigation.

## Verified channels

| Channel | Destination | Verification note |
|---|---|---|
| Facebook | `https://www.facebook.com/toolkitafrica` | Resolves and is referenced by Toolkit/GIZ material |
| Instagram | `https://www.instagram.com/thetoolkitafrika` | Existing Toolkit website destination; resolves |
| LinkedIn | `https://www.linkedin.com/company/the-toolkit-iskills-tti-ltd` | Search result identifies The Toolkit for Skills and Innovation in Kikuyu, Kenya |
| YouTube | `https://www.youtube.com/@toolkitafrica` | Existing Toolkit website destination; resolves |
| X | `https://x.com/toolkitafrica` | Existing Toolkit handle; resolves from the former Twitter URL |
| WhatsApp Channel | `https://whatsapp.com/channel/0029Vb6PfqR5Ejy79JAJlb1f` | Existing Toolkit website destination; resolves |

No TikTok account is linked until its exact official profile is confirmed by the account owner. TikTok visitors can still reach this page through the tracked website link in the TikTok bio.

## Profile URLs

Use the production hostname after cutover:

```text
Instagram: https://toolkitafrica.ac.ke/connect/?utm_source=instagram&utm_medium=social&utm_campaign=profile
Facebook:  https://toolkitafrica.ac.ke/connect/?utm_source=facebook&utm_medium=social&utm_campaign=profile
LinkedIn:  https://toolkitafrica.ac.ke/connect/?utm_source=linkedin&utm_medium=social&utm_campaign=profile
YouTube:   https://toolkitafrica.ac.ke/connect/?utm_source=youtube&utm_medium=social&utm_campaign=profile
X:         https://toolkitafrica.ac.ke/connect/?utm_source=x&utm_medium=social&utm_campaign=profile
WhatsApp:  https://toolkitafrica.ac.ke/connect/?utm_source=whatsapp&utm_medium=social&utm_campaign=profile
TikTok:    https://toolkitafrica.ac.ke/connect/?utm_source=tiktok&utm_medium=social&utm_campaign=profile
```

For current demo review, replace the hostname with `demo.toolkitafrica.ac.ke`.

## Tracking

The existing first-party collector records aggregate `arrival_<source>` and `connect_*` interaction counters. Full query strings, raw referrers, cookies, names, email addresses, and visitor profiles are not stored. Browsers with Do Not Track enabled are excluded.

The page includes explicit tracking for application, courses, notices, WhatsApp admissions, YouTube feature, each official social channel, phone, and email actions. Administrators can review these aggregates in Toolkit Control.

## Release controls

The route is database-independent but is registered only when `TOOLKIT_REDESIGN_ENABLED` is active. This prevents the new route appearing during a main-domain file synchronization while the legacy presentation is still selected. Its canonical URL, Open Graph URL, WebPage schema, breadcrumb, and Yoast page-sitemap entry all use `/connect/`.
