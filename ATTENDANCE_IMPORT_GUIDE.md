# Wingu Attendance Import Guide

**Status:** Operational locally
**Template:** Download from `/wingu/attendance-template.xlsx`

The workbook connects approved Report System entries to authoritative attendance
times before they enter the Wingu queue. It does not submit anything to Wingu.

## Required columns

Keep the first row and column order unchanged:

| Column | Required value |
|---|---|
| `email` | The staff email used by the Report System |
| `report_date` | `YYYY-MM-DD` or a real Excel date |
| `sign_in_time` | A real Excel time, `HH:MM`, `HH:MM:SS`, or `H:MM AM/PM` |
| `sign_out_time` | Same formats; must be later than sign-in |
| `attendance_reference` | The approved source sheet, register or row reference |

## Safe workflow

1. Open **Wingu Queue** and download the standard template.
2. Copy only approved attendance facts into the workbook. Do not add passwords,
   identity documents or unrelated personal data.
3. Upload it with **Preview only**. Every populated row will show either *Ready
   to queue* or an exact validation problem.
4. Correct invalid rows in the workbook.
5. Upload again with **Validate and queue valid rows**.
6. Confirm the queued report, attendance source and times. The Wingu project
   remains *Read during dispatch* until the isolated authenticated browser reads
   the options Wingu provides.

The importer accepts at most 250 populated rows per workbook, matches exactly
one approved report by employee email and report date within the current user's
access scope, and refuses a report already in the queue.
