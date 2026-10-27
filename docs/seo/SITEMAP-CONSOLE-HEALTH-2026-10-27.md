# Sitemap health and Search Console handoff — 27 October 2026

Prepared by Thomas Kamau.

**The primary sitemap is healthy and ready to submit:**
https://toolkitafrica.ac.ke/sitemap_index.xml

Submit that URL in Search Console's Sitemaps report. If a URL-prefix property
already displays the site's base URL, enter sitemap_index.xml. The index lists
both child sitemaps, so a separate submission for each child is unnecessary.

| Resource | Live result | Contents |
|---|---|---|
| /robots.txt | 200, plain text | Main index and supplementary Yoast discovery sitemap advertised |
| /sitemap_index.xml | 200, valid sitemap-index XML | Two child sitemaps |
| /post-sitemap.xml | 200, valid URL-set XML | 155 URLs |
| /page-sitemap.xml | 200, valid URL-set XML | 40 URLs, including the 15 catalogue programmes |
| /wp-json/yoast/v1/schema-aggregator/get-xml | 200, valid supplementary XML | Five schema-resource URLs |

The primary inventory contains 195 unique URLs. XML uses the sitemap namespace;
listed locations are canonical-host HTTPS URLs without fragments or duplicates.
All 197 primary/index last-modification values parse and have no future date.
Public utilities/admin/upload-image routes are absent from the primary inventory.
The supplementary schema sitemap is separate from the 195 page/post inventory;
use the main index as the Search Console submission.

## Google access findings

The live robots file has a general user-agent group, an explicit allow for the
Yoast schema-aggregator route, and restrictions for other REST routes. Under the
actual groups, none of the primary index, children or 195 listed URLs is blocked
for Googlebot or Googlebot-Image. The schema exception is more specific than the
broader REST restriction. An AdsBot disallow group is also present; it is not an
ordinary Google Search Googlebot restriction. No robots setting was changed.
The live root .htaccess contains no Googlebot/user-agent blocking rules. This is
not visibility into every hosting firewall configuration.

The robots and sitemap responses carry noindex headers. Yoast deliberately sets
noindex, follow on its XML sitemaps so the XML itself is not a search-result page;
this does not prevent sitemap consumption or processing. It is not evidence that
the pages listed in the sitemap are noindexed. The immediately preceding release
verified index policy on its 11 sampled public HTML routes.

Current HTTP/HTTPS access-log summaries cover 26–27 October. Both logs were below
the two-megabyte-per-file cap, with 4,669 parsed observations in total. Eight IPs
claiming Googlebot all matched Google's current official common-crawler IP ranges.
Their 29 observed requests returned 25 HTTP 200, one 304, one 301 and two 404.
There were no 401/403, 429 or 5xx responses in this verified Googlebot sample.
Robots.txt has a verified Googlebot 200 observation and an HTTP redirect observation.
The Googlebot observations span 26 October 20:56 to 27 October 08:24 Nairobi time;
the latest HTTPS log entry inspected was 27 October 14:52 Nairobi time.

This supports successful recent genuine Googlebot access and no observed general
Googlebot block. It does not prove that Google has fetched the newly updated
sitemaps: no Googlebot sitemap-file fetch appears in this sample. The two 404s
are unclassified other requests; the summary deliberately retains no arbitrary
request paths, query strings or referrers. They are not evidence of sitemap denial.
Current live sitemap fetches succeeded from this machine, not from Google's IPs.
Search Console's successful fetch/processing result is the next confirmation.

## Method and limits

Five normal production GETs, serial five-second spacing, four total timeout
attempts permitted but no timeout retry needed. No impersonated Googlebot request,
full 195-page crawl, live negative test, form submission or site configuration edit.
The server inspection used read-only .htaccess access and a private inline CLI
summary of at most two megabytes per main-site access log, without booting
WordPress or copying raw logs. The one-time schedule was removed and confirmed
absent. Official Google ranges were downloaded separately; IPs remain private.
An initial automatic permission review expired before execution; its permitted
single retry succeeded, without duplicate site requests.

Private evidence: reports/sitemap-health-2026-10-27/health.json, retained public
responses, production-htaccess.private.txt, google-access-verified-summary.json,
Google range snapshot and server completion/cleanup receipts. No runtime payload
or report/document upload. No claim of guaranteed indexing, future access or
complete WAF-policy visibility.

## Source references

- [Google: build and submit a sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap).
- [Google: robots matching and precedence](https://developers.google.com/crawling/docs/robots-txt/robots-txt-spec).
- [Google: verify crawler requests](https://developers.google.com/crawling/docs/crawlers-fetchers/verify-google-requests).
- [Yoast: sitemap HTTP headers](https://developer.yoast.com/features/http-headers/functional-specification/).
