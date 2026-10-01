# Revised September 2026 Kikuyu Campus fees

Source: the poster supplied by the user on 1 October 2026, headed **Revised
September 2026**, retained as `revised-september-2026-poster.png`. The user
instructed publication after checking the extraction. This revision supersedes
the 7 July brochure for the tuition, exam fees and changed durations below.
Amounts were visually checked twice against the attached original.
Source SHA-256: `ac254958d3fa1fc1874d8439a1e5f379d4c44b1353ddcff5f6eb9800bdd4ce84`.

| Course / category | Duration in poster | Tuition (KES) |
|---|---|---:|
| Advanced MIG/MAG Welding with VR — Skilling | 6 months | 127,895 |
| Welding upskilling — On-campus | 1 month | 55,017 |
| Welding upskilling — Industry (Nairobi Metropolitan) | 1 month | 34,375 |
| Welding upskilling — Industry (Upcountry) | 1 month | 39,575 |
| Welding upskilling — TVET (Nairobi Metropolitan) | 1 month | 39,315 |
| Welding upskilling — TVET (Upcountry) | 1 month | 44,515 |
| Solar Technology — Skilling | 6 months | 120,962 |
| Solar Technology — Upskilling | 15 days | 30,684 |
| Electrical Installation — Skilling | 6 months | 120,962 |
| Digital Skills & Tech | 2 weeks–6 months | 18,000–65,000 |
| Entrepreneurship | 2 weeks–3 months | 8,000–35,000 |
| Smart Agric | 6 months; short courses 1–2 days | 73,568; short courses from 4,000 |
| French A1, A2, B1 | 3 months per level | 35,000 per level |
| German A1, A2, B1 | 3 months per level | 35,000 per level |

| Exam listed separately | Fee (KES) |
|---|---:|
| Grade I | 9,950 |
| Grade II | 8,050 |
| Grade III | 6,750 |
| Solar T1 | 7,050 |
| Solar T2 | 8,450 |

## Accuracy and mapping decisions

- Smart Agriculture is **73,568**, not the previous website's **73,586**.
- Industry Upcountry is **39,575**; TVET Nairobi Metropolitan is **39,315**.
  These similar figures must not be interchanged.
- Exam fees are separate in the poster. Remove the old agriculture claim that
  examination is included; do not invent a combined total or assign an exam
  grade to a particular course without Admissions confirmation.
- The poster groups solar skilling as **Solar Technology**. Retain the existing
  Solar PV Installer and Solar Electrician routes as the established solar
  pathways, with the shared skilling tuition of 120,962. No new programme names
  or enrolment identifiers are created.
- The established Welding Upskilling route shows all five delivery categories,
  rather than giving a location-specific fee to all visitors. The legacy welding
  hub shows the same category breakdown. Directory cards show the exact range.
- French/German A1, A2 and B1 each show 35,000 and 3 months. Older language-package
  prices and standard/subsidised tiers are absent from this poster and removed
  from public pricing. No replacement discount is inferred.
- Digital and Entrepreneurship ranges already agree with the poster.
- Intakes, admissions validation and Mzizi integrations are outside this change.
- Existing source documents/release notes remain historical records. Website
  pricing descriptions and Toolkit Control now name the September revision.

## Verification

Use `php scripts/tests/revised-september-pricing.php` to check the catalogue,
all five welding categories, all five exam rates, corrected durations, legacy
hubs, pricing-off behaviour and RPL's unpriced status. Deploy demo first using
`deploy-revised-september-pricing-2026.10.01.1.sh`, then verify every course page,
the directory and legacy hubs before promoting the same patch to production.

## Release verification — 1 October 2026

Release **2026.10.01.1** is live on demo and production. Five patched code files
matched the reviewed payload byte-for-byte. On each environment, all 23 routes
passed for both bare and cache-busting URLs (46 checks): all 15 modern programmes,
the directory, four legacy hubs, RPL, Apply and llms.txt. Current source wording,
all amounts/categories, absence of obsolete prices, correct release headers and
the unpriced RPL control were checked.

Desktop (1440 px) and mobile (390 px) demo browser checks covered welding
upskilling, separate exams, French B1 and Smart Agriculture, with no horizontal
overflow. Demo screenshots and both environments' public-page snapshots are
retained in the ignored rollback verification directories. No application was
submitted. Transient network/browser approval and cache interruptions were
resolved before production promotion.

Rollbacks: `rollbacks/demo-pre-2026.10.01.1/` and
`rollbacks/production-pre-2026.10.01.1/`.
