# Wingu Box Timesheet Update Procedure

## Purpose

This procedure records how Toolkit project work is translated into Wingu Box
timesheet rows and safely submitted. It does not store passwords, browser
cookies, API tokens, or other authentication material.

## Sources of truth

Use the following evidence, in order:

1. Attendance exports in `reports/logs/` for recorded check-in and check-out
   values.
2. Dated milestone documents and indexes in `reports/weekly-milestones/`.
3. Other dated reports and implementation notes in `reports/` and `docs/`.
4. Git commit dates and validated release records.
5. Dated project artefacts, such as visual-design work under the Toolkit
   `imgs/` directory.
6. Research documents supplied by the employee.

Never convert Git activity, file timestamps, or Codex session activity into
biometric attendance. Missing attendance times must be supplied or approved by
the employee. Research and design work should be described accurately without
inventing document titles, poster names, outcomes, or evidence.

## Preparing daily notes

Create one concise, point-form note per working date:

- Describe completed or materially progressed work.
- Use the milestone/report matching that date.
- Mention validation, deployment, documentation, or rollback work only when
  the source report supports it.
- Record visual-design work generally as poster or promotional-material
  creation; do not list individual poster names unless requested.
- Spread multi-day research across the actual research period and give the
  greatest emphasis to the date on which most research occurred.
- Clearly distinguish local, pending, or fail-closed work from deployed work.

## Wingu Box entry structure

1. Open **ESS → Time Sheet → Edit Time Sheet**.
2. Select the employee and target month.
3. Read the project choices presented by Wingu and select the approved Toolkit
   project for that employee and period. Wingu is the authority for the project
   identifier; never hard-code or substitute a project code in automation.
4. Refresh the grid.
5. For each working date, enter:
   - start time in 12-hour format;
   - end time in 12-hour format;
   - point-form report notes.
6. Leave weekends and designated off days unchanged unless work on that date
   has been explicitly authorised for entry.
7. Select **Update Time Sheet** only after reviewing every changed row.

When updating previously saved rows, submit only the intended dates where the
interface permits it. This avoids unrelated blank or historical rows failing
validation or being unintentionally changed.

## Background browser method

Background entry may use an isolated, temporary browser profile only after the
employee explicitly approves temporary use of the authenticated Wingu session.

The safe sequence is:

1. Create the temporary profile under `/tmp`.
2. Copy only the minimum browser state required for the approved authenticated
   session.
3. Start Brave in headless mode with a localhost-only debugging port.
4. Load the Wingu edit-timesheet URL.
5. Confirm the authenticated employee, month, and project before editing.
6. Populate only the approved dates, times, and notes.
7. Submit the intended rows.
8. Reload the page from the server and verify persistence.
9. Close the headless browser.
10. Delete the temporary profile and any local screenshots or debugging files.

Do not commit the temporary profile, authentication data, captured pages, or
credentials to the repository.

## Required verification

After submission, reload the Wingu page and confirm for every changed date:

- the date is correct;
- start and end times match the employee-supplied values;
- calculated hours are plausible;
- the complete note persisted;
- the project remains the approved value selected from Wingu's own project
  list;
- weekends/off days remain unchanged;
- no unrelated rows were modified.

Record the verified dates in the task handoff. If Wingu reports a validation
error, do not repeatedly submit. Inspect the affected rows, correct the payload,
and repeat the persistence check.

## Latest preparation checkpoint — 15 October 2026

The latest retained attendance export is `reports/logs/Thomas_recent.csv`
(108 records). Its final actual timestamp is **22 September 2026, 08:19:17,
check-in**; that date has no recorded checkout. The older August Wingu handoff
must not be used as the current preparation boundary. At the employee's
instruction, the new draft starts on **22 September**, includes that incomplete
day, and runs through 15 October. Later attendance times and all durations stay
blank pending new logs. See `reports/wingu-preparation-2026-10-15/` for the draft,
source hashes and dated work evidence.

The log's final date is the requested preparation start point, not proof that
Wingu saved every earlier date. Existing Wingu rows and current project choices
still need live reconciliation before any submission or overwrite. Weekends,
off days and work notes without supporting evidence remain held for review.

## Read-only reconciliation — 24 October 2026

The employee signed in personally using a separate temporary Brave profile.
The September and October Edit Time Sheet views confirm approved entries through
**21 September**; subsequent inspected working rows are blank. September required
selecting the displayed Toolkit project and using **Refresh Data** before the
grid appeared. An empty grid without a project is not evidence of an empty month.
No timesheet was updated or submitted.

The new attendance export, `reports/logs/Thomas 1 Sep - 23 Oct 2026.csv`, contains
65 observations and ends at **23 October, 08:08:25, check-in**, with no checkout.
The reconciled preparation period is therefore **22 September–23 October**.
Wingu marks **8, 9 and 12 October as Annual Leave**; keep these unchanged.
Weekends and Mashujaa Day remain held without explicit work/time approval.

The employee authorised estimates for missing working-day endpoints only:
08:00–08:15 and 17:12–17:30. The review pack labels these separately from recorded
times and preserves original seconds and exceptional late departures. Three
arrival and three departure fields are estimated; three work descriptions still
need employee confirmation (24/28 September and 2 October). Saturday 17 October
has evidenced work, but no attendance entry is authorised by that evidence alone.

Management and daily drafts, the Wingu review workbook/CSV, source registers and
minimised private saved-row evidence are under
`reports/report-preparation-2026-10-23/`. These are local preparation files, not
production deployment material. See `docs/reporting/REPORT-PACK-2026-10-24.md`
for the reproduction requirements and limitations.

## Authorised save — 24 October 2026

Following the employee's explicit “push wingu” instruction, **17 unique dates**
were saved and independently reloaded as **Draft**:

- September: 22, 23, 25, 29 and 30.
- October: 1, 5, 6, 7, 13, 14, 15, 16, 19, 21, 22 and 23.

The employee explicitly left 24/28 September and 2 October pending. Their rows
remain blank. All 15 previously approved September entries, Annual Leave,
holidays and off days remain unchanged. The current project was selected from
Wingu's displayed choices. All saved notes, times, hours and project values were
compared after an independent server reload for each month; 37 unrelated rows
were unchanged and no duplicate date was created. Four estimated endpoints in
the saved rows are labelled in their notes. Wingu uses minutes; original seconds
remain in the local attendance review. The separate **Submit for Approval**
action was not invoked; saved Draft does not mean manager approval.

Wingu's native handler stores Unicode bullets literally and removes newlines.
Use readable single-line ASCII point-form notes. The initial October notes were
corrected on the same 12 confirmed Draft records, then reloaded and checked.
The draft grid renders project/times as text while its save handler still
requires their form values. For the owned-Draft note-only correction, the exact
already-saved project/times were preserved as form metadata; no values,
approval restrictions or ownership controls were changed. Previewing with the
outgoing request intercepted exposed the missing-field issue without sending
an invalid update. Do not infer save success from HTTP 200 alone.

The private receipt, per-month comparisons and saved-date list are in
`reports/report-preparation-2026-10-23/push-2026-10-24/`. Earlier preparation
reports remain historical pre-save snapshots. Authentication state is temporary
and excluded from all handoff/source-control files.
