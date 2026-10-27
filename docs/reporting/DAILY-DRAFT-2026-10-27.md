# Daily report draft — Thomas Kamau — 27 October 2026

**Summary:** Reviewed the website's search-readiness work since July and completed
improvements that help people find current programmes. Published clearer welding
pathways, confirmed the requested prices and added a graduation resource notice.
The release passed checks on the demo site before being verified on production.

| Task | Time spent | Status |
|---|---|---|
| Check priority pages and trace July–October work against saved records | Not separately recorded | Completed |
| Identify and repair gaps affecting discovery of current programmes | Not separately recorded | Completed |
| Improve welding programme links and verify requested headline prices | Not separately recorded | Completed on demo and production |
| Publish a graduation resource notice linking the programme and graduands | Not separately recorded | Completed; one production notice created |
| Complete demo checks, deploy to production and verify the public pages | Not separately recorded | Completed; 13 checks per environment |
| Trace changes to the agent instructions for the owner's review | Not separately recorded | Completed; instruction files left unchanged |
| Check sitemap readiness and recent genuine Googlebot access | Not separately recorded | Completed; sitemap ready for Search Console submission |
| Follow up the Search Console fetch error and discovery count | Not separately recorded | Owner confirms recovery after refresh; 40 discovered pages reported |
| Compare current course prices with the available fee documents, excluding welding | Not separately recorded | Completed; digital upper-limit conflict and missing individual course details documented |
| Compare actual search clicks, impressions and rankings | Not separately recorded | Pending measurement; no verified comparison baseline |

**Challenges:** Intermittent connection timeouts interrupted earlier checks.
With the owner's approval, timed-out reads used a maximum of four total attempts
with pauses; the affected demo read succeeded on attempt three and one production
read recovered on attempt two. Changes and publishing jobs were not repeated.
Earlier search reviews used different scopes, so their totals cannot support a
reliable traffic or ranking trend. Access to the separate legacy domain remains
unavailable.

**Decisions/comments:** The verified live main welding prices are KES 55,017 for
on-campus upskilling and KES 127,895 for skilling. Other previously approved
delivery categories remain unchanged. The graduation notice points to approved
public resources without claiming unverified event results. Site Kit settings
were left unchanged while it gathers data. Agent instructions were left unchanged
for the owner's review. Source publication is separate from server deployment;
only the five required website files reached production. No production test
applications or external report submission.

**Next working day:** Thomas/ICT to submit sitemap_index.xml in Search Console,
confirm its processing result and review search-performance data when available;
Admissions to maintain approved course facts and clarify any delivery-category
price changes; domain/profile owners to address old links when access is available.
Expected outcome: consistent programme information and a measurable future search
baseline.

**ICT metrics:** One website maintained across demo and production. Five matching
release files per environment; 26 successful public checks in total. Fifteen
programme links confirmed in each updated page sitemap; inventories contain
40 page/programme and 155 news links per environment. One new production resource
notice and one production release completed. Both temporary publishing schedules
were removed. Local checks: 133 SEO, 14 publisher, 17 recovery, nine audit,
35 graduands and ten layout checks passed. These checks measure website readiness,
not Google rankings or traffic growth. A further five sitemap/robots reads passed;
recent verified Googlebot requests show successful access and no access-denial,
rate-limit or server-error responses in the inspected sample. Google's fetch of
the updated sitemap remains to be confirmed in Search Console.

Findings: `docs/seo/SEO-REVIEW-2026-10-27.md`.
Verified release: `docs/seo/SEO-FIXES-2026-10-27.md`.
Submit through the employee's own Report System session; this draft has not been
filed externally.

Sitemap submission handoff: `docs/seo/SITEMAP-CONSOLE-HEALTH-2026-10-27.md`.

**Console follow-up:** The owner confirmed the sitemap loaded after waiting and
refreshing. Its 40 discovered pages match the page sitemap; the separate post
child contains 155 posts and its processing status still needs checking. The
index alone is sufficient for submission. One fresh fetch returned the same
healthy XML; no website configuration was changed. The follow-up current logs
were empty, so the original error cause remains unconfirmed.

**Course-pricing follow-up:** The September digital fee sheet lists seven courses
and a maximum tuition of KES 68,000; the live site still shows KES 18,000–65,000
without individual course prices. Other non-welding directory prices match the
approved September poster. Older full-catalogue differences must not reverse
those approved updates. Comparison completed; no new prices published. Details:
`docs/pricing/COURSE-PRICE-COMPARISON-2026-10-27.md`.
