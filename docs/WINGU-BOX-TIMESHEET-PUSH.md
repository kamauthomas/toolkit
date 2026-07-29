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
3. Select the applicable project. For the current Toolkit workstream, confirm
   that the displayed project is `TTI2024`.
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
- the project remains `TTI2024`;
- weekends/off days remain unchanged;
- no unrelated rows were modified.

Record the verified dates in the task handoff. If Wingu reports a validation
error, do not repeatedly submit. Inspect the affected rows, correct the payload,
and repeat the persistence check.
