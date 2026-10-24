# Reporting handoff — 24 October 2026

Prepared for Thomas. Reporting period: **22 September–23 October 2026**.

The employee personally signed into an isolated Brave session. Read-only Wingu
inspection found 15 September working entries approved through **21 September**;
later inspected working rows were blank. Selecting the displayed Toolkit project
and using Refresh Data was necessary to display September. No Update Time Sheet,
approval submission or Report System filing was performed.

The new attendance export contains 65 observations, ending **23 October,
08:08:25, check-in**, without a recorded checkout. Actual seconds and late
departures are preserved. The employee authorised missing working-day estimates
in the ranges 08:00–08:15 and 17:12–17:30. Six endpoints use labelled estimates:
three arrivals and three departures. Wingu Annual Leave on 8, 9 and 12 October,
weekends and 20 October remain held. Saturday 17 October has work evidence but
no authorised attendance entry. Work details for 24/28 September and 2 October
remain pending employee confirmation.

## Local deliverables

All generated outputs are ignored/private in
`reports/report-preparation-2026-10-23/`, outside any deployment payload:

- `ICT-WORK-REPORT.md`, `.docx`, `.pdf`: management report, prepared by Thomas.
- `DAILY-REPORT-DRAFTS.md`, `.docx`, `.pdf`: daily report drafts, with unsupported
  work descriptions left pending rather than invented.
- `WINGU-REVIEW.xlsx` and `WINGU-DRAFT.csv`: preparation only, no submission.
- `ATTENDANCE-OBSERVATIONS.csv`: dates/status/times only; person/device identifiers
  excluded. Original supplied logs remain unchanged.
- Work/visual/commit source registers, booklet source verification and follow-up
  action register. Source dates do not establish attendance or publication.
- Minimised live Wingu evidence: dates, time/status/leave labels and note hashes
  and lengths. No credentials, cookies, original saved note text or account URL.

Reviewed artwork includes 40 images: 37 Toolkit outputs in 23 content groups,
one portrait and two references. Variants are grouped; approval/distribution is
not assumed. The earlier 108-image archive is not claimed as 108 new designs in
this period. All three retained booklet copies have 67 pages and match their
handoff hashes. Printing/distribution and Registry decisions remain separate.

## Reproduction and checks

Use the local Python environment with python-docx, openpyxl, Markdown,
BeautifulSoup, Pillow, pycairo and PyGObject/Pango. The existing official logo at
`docs/assets/toolkit-logo.png` is a required local asset. Original evidence files
and the private live reconciliation snapshot are required; they are deliberately
not bundled into source control or uploaded to a server.

```sh
python3 scripts/docs/prepare-wingu-reports-2026-10-23.py
python3 scripts/docs/assemble-wingu-reports-2026-10-23.py
python3 scripts/docs/render-work-report.py reports/report-preparation-2026-10-23/ICT-WORK-REPORT.md
python3 scripts/docs/render-work-report.py reports/report-preparation-2026-10-23/DAILY-REPORT-DRAFTS.md
```

The assembler refuses a final narrative without verified Wingu reconciliation or
for a different date range. The preparation script rejects a conflicting start
date when a verified boundary exists. Reused estimates remain stable on rerun;
held dates do not retain estimated time fields.

Verification covered all 65 supplied observations, 32 calendar rows, 34 recorded
and six estimated endpoints, 12 held dates, the three missing work descriptions,
the exceptional late checkout/duplicate punches, negative boundary handling,
spreadsheet formula exclusion, source hashes, booklet page counts/hashes and
complete Word-to-PDF text retention. PDF pages were visually reviewed. These
are local reporting checks, not new production deployment or security probes.

Security/flaw review: report tools perform local reads/writes only. Browser
inspection was limited to the employee's dated timesheet rows and used no save
or submission control. Authentication state and raw saved notes are excluded
from the handoff. No runtime routes, database queries, uploads, relays or server
files changed; no production test application or live attack request was sent.
Private report outputs and documentation must remain outside runtime payloads.

## Ready-to-paste daily report — 24 October

**Summary:** Confirmed the saved Wingu boundary and completed evidence-backed
management, daily-report and attendance-preparation drafts through 23 October.

| Task | Time spent | Status |
|---|---|---|
| Checked signed-in September/October Wingu rows without editing; preserved approved dates and Annual Leave. | Not separately tracked | Completed |
| Reconciled supplied attendance, labelled authorised estimates and traced system, poster, booklet and event-card work. | Not separately tracked | Completed |
| Prepared and checked management/daily reports, review workbook, source registers and follow-up actions. | Not separately tracked | Completed |

**Challenges:** Three days lack specific work descriptions; the final log has no
checkout. Those details remain for Thomas to review. No external filing occurred.

**Decisions/comments:** Start after 21 September's approved Wingu entry; retain
leave/off days and original times. Do not infer task durations or printing,
publication or deployments from attendance/file dates.

**Next workday plan:** Thomas to confirm the three descriptions and estimated
endpoints using institutional records; supervisors to decide weekend entry.
Admissions, Registry, Communications and hosting owners to resolve their listed
follow-ups. Expected outcome: reviewed entries and approved next actions.

**ICT metrics:** Two months inspected read-only; 32 calendar rows prepared;
65 attendance observations retained; two report documents in Word/PDF; zero
timesheets submitted and zero system deployments in this reporting session.
