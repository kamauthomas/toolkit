# Toolkit Site Metrics

The child theme includes a lightweight first-party metrics system for the redesigned site.

## What it records

- Aggregate page views, once per page per browser session
- Engaged time, capped at 10 minutes
- Maximum scroll-depth bucket: 25, 50, 75, or 100 percent
- Page load duration
- Aggregate outbound-link clicks
- Aggregate interactions with applications, course navigation, testimonial videos, chatbot topics, and `/connect/` actions
- Aggregate social landing-page arrivals when an approved `utm_source` value is present

It does not store raw IP addresses, cookies, names, email addresses, full referrers, or individual visitor profiles. Browsers with Do Not Track enabled are excluded. A short-lived hashed rate-limit key is used only to protect the public endpoint.

## Dashboard

Administrators can open **Tools > Toolkit Metrics** in WordPress. The dashboard summarizes the previous 30 days by page. Raw aggregates are retained for 90 days.

## Operations

Metrics are enabled whenever the redesign switch is enabled. The collection endpoint is `/wp-json/toolkit/v1/metrics`. Data is stored in the `toolkit_site_metrics` WordPress option, allowing it to move with the database backup.

Before switching the main domain, confirm the redesign switch and REST endpoint on that host. The demo and main installations keep separate aggregates unless their databases are deliberately synchronized.

## Social profile links

Use the canonical landing page with a lowercase source tag in each profile:

```text
https://toolkitafrica.ac.ke/connect/?utm_source=instagram&utm_medium=social&utm_campaign=profile
https://toolkitafrica.ac.ke/connect/?utm_source=facebook&utm_medium=social&utm_campaign=profile
https://toolkitafrica.ac.ke/connect/?utm_source=linkedin&utm_medium=social&utm_campaign=profile
https://toolkitafrica.ac.ke/connect/?utm_source=youtube&utm_medium=social&utm_campaign=profile
https://toolkitafrica.ac.ke/connect/?utm_source=x&utm_medium=social&utm_campaign=profile
https://toolkitafrica.ac.ke/connect/?utm_source=whatsapp&utm_medium=social&utm_campaign=profile
https://toolkitafrica.ac.ke/connect/?utm_source=tiktok&utm_medium=social&utm_campaign=profile
```

The collector stores the source as an aggregate `interaction:arrival_<source>` counter. It does not retain the full query string, referrer, or a visitor identity. Outbound actions use explicit `connect_*` interaction labels so administrators can compare applications, courses, notices, contact actions, and official-channel visits.
