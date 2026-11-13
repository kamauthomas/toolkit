# Approved welding training costs — November 2026

The user supplied **Student Notice Approved Training Costs_1794547363.pdf**,
issued by the Office of the Principal on **10 November 2026**, and instructed
publication. Both PDF pages were read; the first-page table was visually checked.
Source SHA-256: `8cdae8595d798690fa18eea22af9adf97ca4b187bc2ddb2de249dc8cdd7005c4`.
The owner explicitly instructed that the notice remain pending locally. The PDF, notice announcement and its dated attribution are not published; only the approved course costs and appropriate course guidance are updated.

| Programme | Approved training cost | Duration |
|---|---:|---|
| Advanced MIG/MAG Welding with VR — Skilling | KES 176,276 | 6 months: 3 months at the Hub and 3 months in industry |
| Advanced MIG/MAG Welding with VR — Upskilling | KES 66,300 | 1 month |

The notice takes immediate effect and says these are the official fees. It
supersedes the previous welding prices and the older location-specific upskilling
rate table. No alternative category, subsidy, guaranteed financing, included exam
fee or additional exam total is inferred. Financing depends on partner eligibility
and approval. Fee/financing questions go to the Youth Skills Manager’s Office.
Other programmes and their published exam-fee table retain their existing amounts.

The updated catalogue supplies both programme pages, the course directory and
the established welding hub. The shared detail template labels welding values
“Approved training cost”, uses approved-cost guidance and removes obsolete category
and September-source statements for welding. Other course templates retain their
September provenance. The assistant guidance in llms.txt and Toolkit Control’s
pricing description also recognise the new authority. Established course names,
URLs, admissions selection identifiers and Mzizi submission code are unchanged.

## Verification and release

Release target: **2026.11.13.1**. Run the retained pricing and SEO regression
checks, lint the five changed PHP files, and apply the runtime policy. The pricing
suite covers all 15 fees, unchanged non-welding/exam values, both modern welding
pages, the legacy hub, unpriced RPL and pricing-off behaviour. Negative SEO route
checks remain in the existing isolated suite.

The focused release script prepares a zero-fuzz patch against each fresh server
baseline, retains private originals, lints payloads, guards write intent and
concurrent local runs, byte-verifies uploads and activates functions.php last.
Production requires the same verified demo source/payload. Normal public checks
cover both programmes, directory, welding hub, llms.txt and unpriced RPL. Stop on
transport/WAF failures; never replay uncertain writes. No production test
application, Mzizi replay, account change or document upload is required.

Deployment results are recorded in WORKLOG and the private release receipts;
a target release number alone does not claim live completion.
