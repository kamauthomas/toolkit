#!/usr/bin/env python3
"""Build management/daily-report drafts and an internal Wingu review workbook."""
import csv
import datetime as dt
import json
from collections import Counter
from pathlib import Path

from openpyxl import Workbook
from openpyxl.styles import Alignment, Font, PatternFill
from openpyxl.utils import get_column_letter

ROOT = Path(__file__).resolve().parents[2]
OUT = ROOT / 'reports/report-preparation-2026-10-23'


def main():
    summary = json.loads((OUT / 'RECONCILIATION.json').read_text())
    if not summary.get('report_start_verified'):
        raise ValueError('Read-only Wingu reconciliation is required before assembling the final pack')
    activities = json.loads((ROOT / 'docs/reporting/WORK-EVIDENCE-2026-09-22-TO-2026-10-23.json').read_text())['entries']
    rows = list(csv.DictReader((OUT / 'WINGU-DRAFT.csv').open()))
    start, end = summary['report_start'], summary['attendance_cutoff']
    if (start, end) != ('2026-09-22', '2026-10-23'):
        raise ValueError('Review the dated management narrative before using a different period')
    period = f"{dt.date.fromisoformat(start).strftime('%d %B')}–{dt.date.fromisoformat(end).strftime('%d %B %Y')}"
    boundary = summary.get('live_wingu_boundary', 'Not yet reconciled')
    gaps = ', '.join(summary['missing_task_detail_dates'])
    gap_count = len(summary['missing_task_detail_dates'])
    prepared = dt.datetime.fromisoformat(summary['prepared_at_nairobi']).strftime('%d %B %Y')
    management = f'''# ICT and communications work report

**Prepared by:** Thomas<br>
**Evidence period:** {period}<br>
**Prepared:** {prepared}<br>
**Status:** Internal draft for management and employee review. Not filed in Wingu or the Report System.

## Reporting boundary and attendance

The new attendance export contains 65 observations from 1 September through
23 October. Its final actual record is 23 October at 08:08:25, a check-in without
a checkout. The signed-in Wingu check confirms approved entries through
21 September; subsequent working rows are blank. This pack therefore starts on
22 September and ends on the latest log date, 23 October. Saved-row evidence:
The check was completed on 24 October using the employee's signed-in session.
No saved row was edited and no timesheet was submitted.

Recorded times, including late departures, are preserved exactly. Missing
working-day endpoints use the employee's approved estimates: 8:00–8:15 AM for
arrival and 5:12–5:30 PM for departure. Estimates are labelled and are not
biometric observations. Task durations were not separately tracked and are not
invented from attendance spans. Weekends and off days without records remain
held. Wingu marks 8, 9 and 12 October as Annual Leave; no work times are estimated
for those dates. The missing 23 October checkout is a labelled estimate requiring
employee review, not a recorded departure.

## Work completed and progressed

The period combined maintenance of Toolkit's operating systems with substantial
communications and graduation work. Verified website and Reception releases
improved access to services, staff controls, applicant handling and public
information. Management reporting and planning documents were prepared. Poster
and event artwork was organised and revised, while the graduation booklet
progressed through review editions to a verified 67-page handoff.

| Period | Work and resulting benefit | Evidence position |
|---|---|---|
| 22–30 September | Improved Reception QR administration, institutional information and directions; reviewed management findings; introduced application/link QR access and associated reporting. Prepared recruitment and photographic-documentation layouts. | Releases and reports verified; artwork approval/distribution not assumed. |
| 1–7 October | Updated course fees, addressed verified system-audit issues, prepared management reporting, consolidated 108 distinct poster images, published the controlled graduation announcement and repaired recurring document-opening problems. | System deployments and archive/desktop checks retained. |
| 13–17 October | Prepared Q4 ICT objectives and graduation materials; improved returning-contact history and administration; investigated exposed files; strengthened application delivery tracking and retained course choices during service interruptions. | Deployed work distinguished from local drafts and unresolved controls. |
| 19–21 October | Revised invitations and promotional material; published the programme and searchable register; corrected the public list to 285 names; developed booklet pages, portraits, messages, guest artwork and 30 numbered usher cards. | Website verified live; booklet/artwork and print files prepared locally. |
| 22 October | Completed the final booklet handoff with matching print/review copies; checked reported hosting security concerns and available records. | Booklet exports verified; provider confirmation of security updates and recurring failures remains open. |
| 23 October | Reconciled the new attendance export and traced work into reporting drafts, artwork evidence and follow-up actions. | Preparation work continued with a read-only Wingu check on 24 October; no external report submission. |

## System delivery and remaining limits

| Workstream | What was handled | What remains |
|---|---|---|
| Public website and admissions | Updated fees, directions, institutional pages, QR access, graduation content and application-delivery records/retries. Course choices retain their last successful data during outages. | Admissions must reconcile uncertain Mzizi deliveries. Mzizi must explain the reported application-processing error. |
| Reception | Improved staff-access administration, restricted record changes and cleanup, repaired pagination, refreshed overview figures and added private returning-contact history. | Staff acceptance and any on-site automatic-checkout boundary trial. Automatic checkout was disabled in the verified 15 October state. |
| Reporting | Corrected dashboard navigation, protected report downloads and restricted access to an exposed old system copy. | Confirm which newer locally prepared features are available on the live system and complete staff acceptance. |
| Virtual Campus and assistant | Included deployed Campus test-environment protections in the account review; prepared assistant administration restrictions locally. | Campus wider rollout/acceptance and an identified assistant deployment. No new Campus feature rollout or assistant production deployment is claimed here. |
| Desktop support and planning | Addressed LibreOffice lock/restart behaviour and terminal scrolling; prepared six Q4 objectives and 25 key results. | Confirm behaviour after the next relevant restart and assign plan owners. |

## Posters and event materials

The review inspected 40 dated image files from Documents and the project image
folder. These comprise 37 Toolkit promotional/event/cover outputs in 23 reviewed
content groups, plus one supporting portrait, one external reference and one
supplied popup reference. Versions and layout variants are grouped, not counted
as separately approved posters. File dates support the design chronology but do
not prove attendance, final approval or public distribution.

The work includes course recruitment, language programmes, alumni and October
communications, customer-service and teacher/environmental awareness, partner
training promotion, graduation posters/covers/invitations, guest cards and usher
artwork. The PCEA sheets are photographic documentation of damage; they do not
establish that physical repairs were completed. One partner poster still has
incomplete price/logo details and needs confirmation before use. Editable
sources are preserved. The earlier 108-image ZIP is a reusable collection, not
108 new designs produced during this reporting window.

## Graduation booklet and public information

Booklet work produced one publication through successive revisions. Early drafts
progressed to the final 67-page original, smaller print-quality copy and lighter
review copy on 22 October.
The supplied cover was retained, the 66 following pages were preserved in the
last revision, and the retained checks cover text, page order, fonts, image
colour and complete render reviews. Preparation of print files does not prove
that a printer produced or distributed the booklet.

On 21 October the website first published 294 names and then removed nine
specified entries, leaving 285. Cindy was already present and retained once.
The booklet's internal record also describes 285 across current/historical
sections, while academic classification and the earlier expected-total question
remain for Registry to settle. No names were invented to force a total. Thirty
usher cards, 01–30, were prepared as three A4 sheets and a separate 30-page PDF.

## Follow-up actions

| Owner | Required follow-up | Position |
|---|---|---|
| Employee / reporting owner | Review labelled estimates, the missing 23 October checkout and {gap_count} missing task descriptions. | Wingu approved through 21 September; project confirmed from the displayed choices; drafts not submitted. |
| Admissions / Mzizi contact | Check unconfirmed applications before any resend and obtain the supported delivery-check method. | Uncertain submissions must not be replayed blindly. |
| ICT / hosting provider | Confirm security updates are fully applied, improve failure records and explain recurring service failures; preserve investigation records. | Available checks found no confirmed successful intrusion; they do not establish complete clearance. |
| ICT / institution | Complete vendor-theme update decisions, private incident-data retention/cleanup and appropriate staff acceptance. | Earlier compensating controls do not replace vendor patches. |
| Registry / Communications | Confirm remaining class/intake questions, missing portraits, final booklet sign-off and any final partner-poster fields. | Use the separate internal issues records; do not print speculative totals. |
| Communications / website owner | Confirm final artwork usage and review post-event notices/theme consistency against the latest approved booklet. | Files alone do not establish approval, printing or distribution. |
| Management / ICT | Assign Q4 owners and confirm an approved email/SMS provider for verification messages. | Provider-dependent verification remains unimplemented. |

## Reporting exceptions

{gap_count} weekday task descriptions are not independently supported in this window:
{gaps}. Their attendance fields are prepared where permitted, but their work
notes are left for employee confirmation. Work on Saturday 17 October is
supported by the release record; weekend attendance entry still needs explicit
approval. 20 October is Mashujaa Day and remains held without work approval.
The holiday basis is the [Constitution, Article 9, published by KLRC](https://www.klrc.go.ke/index.php/constitution-of-kenya/108-chapter-two-the-republic/175-9-national-symbols-and-national-days).

Detailed source paths/checksums, time labels, raw-status exceptions and dated
notes are in the accompanying internal review files. This report contains no
student contact lists, passwords or private applicant records.
'''
    (OUT / 'ICT-WORK-REPORT.md').write_text(management)
    daily = [f'# Daily work report drafts\n\n**Prepared by:** Thomas  \n**Period:** {period}  \n**Status:** Employee review drafts; no Wingu or Report System submission.\n\nRecorded times and employee-authorized estimates are labelled separately.\nTask time was not separately tracked. Source/file dates support work notes,\nnever biometric attendance. Wingu is approved through 21 September; later\ninspected working rows are blank. Annual Leave and off days remain unchanged.\nTask status refers to the activity described; pending approvals and deployment\nlimits are stated separately. Next actions are proposals for owner review.\n']
    for row in rows:
        evidence = activities.get(row['date'])
        if row['held_reason'] and not evidence:
            continue
        label = dt.date.fromisoformat(row['date']).strftime('%A, %d %B %Y')
        daily.append(f'## {label}\n')
        daily.append(f"**Attendance draft:** {row['start_time'] or 'held'} ({row['start_basis'] or row['held_reason']}) to {row['end_time'] or 'held'} ({row['end_basis'] or row['held_reason']}). Elapsed span: {row['elapsed_hhmmss'] or 'not assigned'}.\n")
        if evidence:
            daily.append('**Summary:** ' + evidence['summary'] + '\n')
            daily.append('| Task | Time spent | Status |\n|---|---|---|')
            for task in evidence['tasks']:
                status = 'Completed'
                daily.append(f'| {task} | Not separately tracked | {status} |')
            daily.append('\n**Challenges / decisions:** ' + evidence['state'] + '\n')
            daily.append('**Next action / owner review:** ' + evidence['follow_up'] + '\n')
            daily.append('**Resources / expected outcome:** Existing source material, institutional records and the responsible owner\'s confirmation; an approved decision or checked deliverable before the next action proceeds.\n')
            daily.append('**Metrics / evidence:** ' + evidence['basis'] + '. Refer to the source register for dated outcomes and verified counts; activity rows do not imply separate production deployments.\n')
        else:
            daily.append('**Summary:** Attendance is recorded or an estimate was authorized, but the specific work description is not independently documented. Add the actual work before filing.\n')
            daily.append('**Tasks / time:** To be confirmed by Thomas; no completed task or allocation is invented.\n\n**Challenges / decisions:** The gap is work-note evidence, not permission to overwrite recorded attendance.\n\n**Next action:** Employee to supply the activities and supporting material.\n\n**Metrics:** Unverified; no delivery count assigned.\n')
        if row['review_flags'] or row['held_reason']:
            daily.append('**Attendance review:** ' + '; '.join(x for x in [row['review_flags'], row['held_reason']] if x) + '.\n')
        daily.append('**Submission:** Not submitted. Employee review required; saved boundary and displayed project were checked read-only on 24 October.\n')
    (OUT / 'DAILY-REPORT-DRAFTS.md').write_text('\n'.join(daily))
    book = Workbook()
    cover = book.active
    cover.title = 'Read me'
    for line in [
        ['Prepared by', 'Thomas'], ['Status', 'Internal preparation only; no Wingu submission'],
        ['Verified reporting period', period], ['Latest actual log', summary['latest_actual_timestamp'] + ' ' + summary['latest_actual_status']],
        ['Wingu boundary', boundary], ['Approved missing start range', '08:00–08:15'], ['Approved missing end range', '17:12–17:30'],
        ['Actual times', 'Retained including seconds; never replaced by estimates'],
        ['Off days / leave', 'Weekends, 20 October and Wingu Annual Leave on 8, 9 and 12 October held'],
        ['Latest log date', '23 October checkout is estimated, not an actual observation; employee review needed'],
        ['Task times', 'Not separately tracked; elapsed attendance is not task allocation'],
        ['Project', 'Displayed choice confirmed: TTI0224 / TTI 2024. No project identifier hard-coded for submission.'],
        ['Missing work detail', gaps], ['Source evidence', 'See WORK-EVIDENCE-REGISTER.csv and visual review sheets'],
    ]:
        cover.append(line)
    daily_sheet = book.create_sheet('Daily entries')
    fields = list(rows[0])
    daily_sheet.append(fields)
    for row in rows:
        daily_sheet.append([row[k] for k in fields])
    punches = book.create_sheet('Recorded observations')
    observations = list(csv.DictReader((OUT / 'ATTENDANCE-OBSERVATIONS.csv').open()))
    punches.append(list(observations[0]))
    for row in observations:
        punches.append(list(row.values()))
    evidence_sheet = book.create_sheet('Evidence register')
    sources = list(csv.DictReader((OUT / 'WORK-EVIDENCE-REGISTER.csv').open()))
    evidence_sheet.append(list(sources[0]))
    for row in sources:
        evidence_sheet.append(list(row.values()))
    for sheet in book:
        sheet.freeze_panes = 'A2'
        sheet.auto_filter.ref = sheet.dimensions
        for cell in sheet[1]:
            cell.fill = PatternFill('solid', fgColor='174A43')
            cell.font = Font(color='FFFFFF', bold=True)
        for index, col in enumerate(sheet.columns, 1):
            header = str(sheet.cell(1, index).value or '')
            sheet.column_dimensions[get_column_letter(index)].width = 62 if header in {'work_note', 'source_references', 'delivery_state', 'path'} else 25
            for cell in col:
                cell.alignment = Alignment(vertical='top', wrap_text=True)
                if isinstance(cell.value, str) and cell.value.startswith(('=', '+', '-', '@')):
                    cell.value = "'" + cell.value
        if sheet.title == 'Read me':
            sheet.column_dimensions['A'].width = 32
            sheet.column_dimensions['B'].width = 95
        if sheet.title == 'Daily entries':
            for index in range(2, sheet.max_row + 1):
                sheet.row_dimensions[index].height = 85
    book.save(OUT / 'WINGU-REVIEW.xlsx')
    print('Prepared management report, daily drafts and Wingu review workbook for', period)


if __name__ == '__main__':
    main()
