# Daily report draft — 1 October 2026

**Summary:** Applied the revised September 2026 Kikuyu Campus poster fees to the
Toolkit course catalogue and legacy course pages, following source verification
and demo-first deployment.

| Task | Time spent | Status |
|---|---|---|
| Extract and visually verify all tuition and exam amounts; preserve source image and mapping record | Not separately logged | Completed |
| Update programme/legacy fees, welding categories, exam-fee display, changed durations and source wording; test public rendering and pricing-off controls | Not separately logged | Completed |
| Deploy demo, check desktop/mobile layouts and all pricing routes, then promote and verify production | Not separately logged | Completed |

**Challenges:** Transient cPanel request timeouts were retried. A browser
verification approval review timed out; the permitted retry succeeded.
Cloud/browser caching interrupted the first mobile review with HTTP 304; a fresh
browser-cache-disabled pass completed. No user input or additional access is
needed for these resolved checks.

**Decisions and comments:** Treat the user-supplied revised September poster as
the pricing source. Correct Smart Agriculture to KES 73,568. Keep Industry
Upcountry (39,575) distinct from TVET Nairobi Metropolitan (39,315). Show all five
welding upskilling categories, and list exams separately rather than inventing
combined totals. Remove obsolete July pricing tiers and language packages.
Preserve established solar pathway names under the poster's Solar Technology
pricing group. Keep admissions and Mzizi integration intact.

**Plan for tomorrow:** Admissions/communications staff: compare the public fee
pages with the revised poster during the normal publishing review; use this
revision for applicant guidance. Resources: retained poster and website URLs.
Expected outcome: consistent fee quotations across the website and intake
materials.

**ICT department metrics:** 1 system maintained; 1 course-pricing request
addressed; 15 programme fee cards checked; 5 welding upskilling categories and
5 separate exam rates published; 2 environment deployments; 46 route checks
per environment (92 total); 8 demo desktop/mobile section renders reviewed;
release 2026.10.01.1 verified in production.
