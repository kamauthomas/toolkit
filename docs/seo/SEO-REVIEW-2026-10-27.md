# Toolkit SEO: July–October review

Prepared by Thomas Kamau · 27 October 2026

**Release follow-up:** The findings below describe the initial assessment.
The catalogue sitemap, public-page canonical/course-schema integration, welding
links/prices and graduation resource notice were subsequently repaired and verified
on demo and production. Each passed 13 public checks; inventories now contain
40 page and 155 post URLs. See [the verified release](SEO-FIXES-2026-10-27.md).
These deployment results do not establish search-growth or indexing gains.

The website's technical search foundation has improved since the July work began.
The remaining weakness is connecting that foundation to the newer programme
pages and to consistent, authoritative public information. **We cannot quantify
organic-search growth:** Toolkit has no Search Console access/history available
for this review, as confirmed by the owner. No percentage growth, Google rank or
search-generated enquiry total is claimed.

## What the months show

| Period | Evidence of work and outcome | What it does not prove |
|---|---|---|
| July | Theme history begins on 1 July. The 31 July public SEO review identifies weak welding-course detail, overlapping topics, fragmented identity and insufficient first-hand evidence. | There is no measured July clicks/impressions baseline. |
| August | The 10 August release records 176/176 sitemap routes responding and metadata/index-policy repairs. The 13 August HTML-only records show 22/22 pages passing and two canonical mismatches among 154 posts. The 2026 catalogue and approved prices were published in August. | Passing technical checks is not a ranking or traffic increase. Different August audit scopes are not a growth series. |
| September | The reliable serial sample passed 13/13 priority pages. Welding copy, evidence links, two story snippets and mobile-hero loading were improved. The archived July weaknesses in generic welding visibility and old-domain migration remained open. | The broad September crawl's 132 timeouts were produced under concurrency; they are not a measured visitor outage rate. |
| October, checked today | The same 13 priority pages still pass their core rendered checks. The directory links 15 programme pages; two sampled welding programmes are indexable, self-canonical and contain approved course facts. Main-domain HTML checks returned 18/18 HTTP 200. | These are readiness/availability findings, not 18 indexed pages or a proven gain in organic reach. |

The improvement is concrete: clearer page identity, one parseable structured-data
graph, protected staging/utility indexing policy, current prices and richer course
information. The evidence supports **better search readiness**, while generic
search performance remains unmeasured. The July and September reviews already
reported weak generic-welding discovery. Today's public search sample does not
establish that this has changed; it is not a controlled Google ranking test.

## Where SEO is falling short now

### 1. The programme catalogue is missing from the public sitemap

The current sitemap lists **179 URLs: 154 posts and 25 pages**. Its URL set is
identical to the saved 15 September inventory. None of the **15 programme URLs**
linked from [Our Courses](https://toolkitafrica.ac.ke/our-ventures/) appears in the
fetched sitemap index or its two child sitemaps. The two sampled programme pages
respond normally and have self-canonicals, so their absence is a discovery gap,
not evidence that Google has refused to index them.

The source explains the gap: the custom sitemap extension in `functions.php`
adds Connect, Graduation, Testimonials and Footprint, but does not enumerate the
catalogue. This omission was already present in September's saved URL inventory;
it cannot reasonably be attributed to yesterday's Reception/admin UI release.

**Next:** include enabled, canonical programme URLs with meaningful modification
dates; test the generated XML on demo and compare it with the public directory.
Google says a sitemap helps discovery but does not guarantee indexing, and linked
pages can still be discovered without it.
[Google sitemap guidance](https://developers.google.com/search/docs/crawling-indexing/sitemaps/overview)

### 2. The graduation page lacks its canonical tag

[Graduation](https://toolkitafrica.ac.ke/graduation/) returns 200, is indexable and
has metadata/schema, but has **no `rel="canonical"`**. Source inspection shows
showcase canonicals are explicitly handled by the demo-to-production helper;
the general production canonical filter does not include those showcase routes.
The live evidence confirms the graduation gap. Testimonials and Footprint were
not fetched today, so their current output is not claimed to fail.

**Next:** cover public virtual routes consistently and verify their rendered
canonicals, not only Open Graph URLs. A missing canonical is not an automatic
ranking penalty; it leaves Google to choose the representative URL.
[Google canonical guidance](https://developers.google.com/search/docs/crawling-indexing/consolidate-duplicate-urls)

### 3. The established welding hub does not lead to the detailed programmes

The [MIG/MAG hub](https://toolkitafrica.ac.ke/our-ventures/construction-sector-skills/)
now has stronger explanatory content and current tuition, but still defers
duration, entry and intake to Admissions. It has no direct HTML link to either
new welding programme, even though those pages contain specific duration,
requirements and intake information. The directory does link to them.

**Next:** agree the roles of the broad welding hub, VR explanation, full programme
and upskilling programme. Add descriptive links and a clear programme comparison.
Confirm course equivalence with Admissions before replacing the hub's facts or
redirecting any established URL. Topic overlap is a potential issue to resolve;
actual keyword cannibalisation cannot be diagnosed without query/page data.

### 4. Old-domain authority is still not reliably consolidated

August/September records describe broken deep links on `toolkitiskills.com`.
Today the bare legacy homepage timed out on this machine. Checks stopped there,
without retry, IP rotation or further hosting requests. This does **not** show
that the current main host is down: its sampled pages had responded normally.

The [owned LinkedIn profile](https://ke.linkedin.com/company/the-toolkit-iskills-tti-ltd)
and a [partner's Toolkit page](https://www.philherndon.com/partners/toolkit-nairobi-kenya)
still advertise the old
domain in the public search material reviewed. Those links are existing evidence
of split destinations, not proof of newly earned backlinks or lost rankings.

**Next:** old-host/domain owner to verify page-to-page permanent redirects and
update owned profiles and important partner destinations. Do not send every old
page to an irrelevant homepage.
[Google site-move guidance](https://developers.google.com/search/docs/crawling-indexing/site-move-with-url-changes)

### 5. New work is not consistently becoming searchable editorial evidence

The public blog's latest featured story is dated **3 August 2026**, while later
programme, industry and graduation work exists elsewhere. Older useful stories
remain available, but there is no current blog-led trail explaining the latest
work. This is a publishing opportunity, not a claim that every page must be new.

**Next:** publish a small number of approved, first-hand updates, connect them to
the relevant programmes, and strengthen the best existing evidence pages. Avoid
generic bulk posts or unverified accreditation/employment claims.

## What happened differently

Earlier work concentrated on repairing the existing site's search plumbing.
Later work added programme catalogues, showcase pages, admissions and management
features. The new public routes did not receive a complete follow-through across
sitemap generation, production canonicals and contextual links. The code and
saved inventories support this explanation. They do **not** establish a ranking
drop caused by a particular release.

Measurement is the other continuous gap: without Search Console, we cannot tell
whether better pages produced more search impressions, higher click-through or
more non-brand enquiries. The site's first-party counters record aggregate
activity, not organic referrers or search queries; those are different measures.
No server aggregate extraction was attempted after the timeout.

## Proposed order of work

1. Add the 15 enabled programme URLs to the sitemap and repair virtual-route
   canonicals, with isolated tests and demo-first verification.
2. Connect welding hubs, programme details and existing evidence through an
   agreed page hierarchy and approved facts.
3. Have the domain owner resolve the old-host redirect path and owned-profile URLs.
4. Establish an owned Search Console property, submit the sitemap and retain the
   first available baseline. Do not promise to reconstruct missing July history.
5. Review search queries/pages and enquiry outcomes over comparable periods;
   publish targeted first-hand content and reassess after recrawling.

## Evidence and limits

- July comparison: `docs/WELDING-SEO-COMPETITIVE-AUDIT-2026-07-31.md`; Git history
  establishes work dates, not deployment or ranking dates.
- August: ignored `reports/WORK-REPORT-2026-08-10-SEO-RECOVERY.md` and HTML-only
  13 August page/post audit JSONs. The 492-entry broad audit also counted images
  as HTML pages, so its missing-title totals are not used as an SEO baseline.
- September: `docs/seo/SEO-FIXES-2026-09-15.md`, the saved 179-URL inventory and
  13-route serial audit. Its historic performance traces are not an October field
  Core Web Vitals measurement.
- Today: private `reports/seo-review-2026-10-27/` contains raw public responses,
  `public-audit.json` and `comparison-summary.json`. There were 23 completed
  responses, then one old-host transport failure. No production changes, account
  connection, plugin installation, deployment or form submission.
- An initial collector OG-image-key error was corrected using the saved HTML,
  with no further requests. Social images are present; no missing-image claim is
  made. Nine offline parser/fail-stop tests pass. Expected noindex utility pages
  are not marked as failed marketing pages.
- Two virtual programme graphs contain Course but lack WebPage/BreadcrumbList
  nodes: an enrichment opportunity, not proof of invalid JSON-LD or lost ranking.
  Some identical OG image tags are duplicated; this is lower priority hygiene.
- Search snapshots are time/provider/location dependent. Sitemap counts are not
  indexed-page counts. Current concurrency, verified Googlebot behavior, live
  backlink totals, numerical rankings and organic growth were not measured.

No website fix was deployed during this assessment.
