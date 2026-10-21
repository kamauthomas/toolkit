# Class of 2026 website publication — 21 October 2026

Prepared by Thomas.

**Latest public total: 285**, following the nine-name correction below. The
294-row figures describe the original workbook publication.

## Supplied sources and decisions

Source folder: `/home/t316/Desktop/Projects_father/toolkit/docs/graduation/`.

- `PROGRAM - GRADUATION DAY GUIDE EVENT FOR TOOLKIT TRAINEES -OCTOBER-2026.md`:
  12th ceremony, Thursday 22 October 2026; formal programme 9 AM–1 PM EAT;
  arrival/registration/gown fitting 8–9 AM; theme, MC, activities and facilitators.
- `Toolkit_Graduation_Invitation_Final_Print.pdf`: confirms arrival from 8 AM
  and the venue address, 7219 Karen–Kikuyu Highway (Southern Bypass), Kikuyu.
- `Names for Graduation Booklet.xlsx`: 294 numbered entries across eight sheets.
  Only display names and course titles are imported. Whitespace is normalized;
  spellings and initials are retained. No duplicate name/course pairs were found.
- The operator confirmed Welding for Hadiid and Class of 2025 and inclusion of
  the Class of 2025 names in this ceremony. The two Welding Upskilling campus
  sheets share one course filter; campus and intake/stream fields stay private.
- The programme's `11.40 AM – 12:30 AM` entry is displayed as
  **11:40 AM–12:30 PM**, consistent with the surrounding morning/afternoon
  sequence and 1 PM finish. No unnamed guest or speaker is invented.
- Review-edition booklets contain placeholders and pending approvals. Their
  draft messages, incomplete registers, production notes and unrelated staff
  images are not published or uploaded.

| Public course | Entries |
|---|---:|
| Welding | 178 |
| Welding Upskilling | 79 |
| Solar Electrician | 24 |
| Solar Upskilling | 11 |
| Organic Farming | 2 |
| Total | 294 |

The graduation page retains its gallery, adds a nine-row programme and publishes
the names at the existing `/graduation/#graduands` QR destination. The Notice
Board receives an Events card linking to the programme and searchable list.
Browser verification exposed existing author styles overriding hidden cards;
a scoped hidden-state rule restores category/search filtering and the empty state.
The homepage popup retains its existing repeat limits and official logo, and
adds the theme and arrival time. The calendar keeps the 9 AM formal start and
1 PM finish, adds 8 AM arrival instructions and increments its revision.

## Security/flaw review

The existing roster parser enforces exactly two columns, rejects markup,
duplicates and invalid encodings, and sorts the validated rows. Existing admin
capability, nonce, preview/approval, visibility and withdrawal controls remain.
Names and programme text are escaped on output; searches stay in the browser.
No student numbers, school/stream fields, contact details or original workbook
are exposed. The operator's direct publication instruction authorizes this
one-time import; no account is impersonated and no login credentials are typed.

The importer is CLI-only in private storage outside all webroots. It accepts
only fixed demo/production targets, verifies the embedded CSV checksum, saves
the previous options for recovery, uses the deployed validator, verifies the
saved publication and deletes its executable after success. Its scheduler job
is removed in a finally block. Logs contain counts/checksums, not names or
credentials. The existing published list changes as one option value.

Only the six runtime files go to the theme. The temporary private importer
contains the two authorized public fields; raw XLSX/PDF/ZIP files, README,
AGENTS, reports and tests stay off the public/application trees. No new public
upload or privileged endpoint is introduced. Fetched source is checked for drift
twice and patched before deployment; demo and production results are separate.
No application submission, live attack probe or IP rotation is part of testing.

Local checks: 294 actual source rows pass the deployed CSV parser, 35 existing
graduands permission/approval/visibility checks pass, and all 26 popup repeat
scenarios pass. PHP syntax checks and diff whitespace checks pass.

## Verified publication

Demo and production both serve release **2026.10.21.1**. Their six runtime
files matched the prepared byte checksums. Graduation, Notice Board and homepage
returned HTTP 200 with the current release marker; programme CSS and calendar
bytes matched. The temporary importers removed themselves and their temporary
scheduler jobs were removed.

All 294 public name/course pairs match the approved CSV exactly on each site,
with five course filters and nine programme rows. The two environments passed
17 browser checks each: desktop/mobile layout, arrival guidance, official logo,
name search, course filtering, no-match/reset behaviour, printing all names even
with a filter active, Notice Board Events filtering and the stable QR link.
Screenshots were visually reviewed; the live mobile checks use a 390-pixel
viewport. The calendar's CRLF line endings and 75-octet folding were checked.
The existing popup-repeat and roster/admin checks remain passing.

Only authorized public names and courses reached the WordPress roster; zero
private workbook fields or raw documents were published. The source CSV checksum
is pinned against verified demo evidence before production, and the complete
production runtime payload must match verified demo hashes. Repeated private
verification rechecks the current list and purges cached markup without replacing
the approved publication again. No live test application or negative probe ran.
Private source checksums, baseline/rollback files, publication and verification
records are retained under `reports/graduation-2026-10-21/` (ignored by Git).


## Operator correction — nine removals

The operator subsequently requested nine existing entries be removed. The
shortened/spelling variants matched unique existing records. Cindy Malika
Galana was already present under Welding and is retained exactly once, so the
correct revised total is **285**, not 290. No extra person or duplicate is
introduced to force an arithmetic target. The original 294-row workbook import
above remains historical source evidence; original documents are not modified.

The correction is data-only and uses a separate private source/rollback record.
The reviewed original publication hash must match before a replacement; an
unexpected live edit stops the amendment. The deployed strict CSV parser is
verified against the repository, and production requires the exact source
checksum verified on demo. The temporary private importer removes itself and
its scheduler entry; page caches are purged. All rendered name/course pairs
and the normal QR destination are checked against the corrected source.

Corrected course totals: Welding 174; Welding Upskilling 74; Solar Electrician
24; Solar Upskilling 11; Organic Farming 2. Cindy is counted within Welding.
Demo and production both returned HTTP 200 with all 285 displayed pairs
matching the corrected source exactly, including the normal QR destination.
All nine removed pairs were absent and Cindy appeared once. Temporary importers
and jobs were removed; private recovery copies were retained. No theme files
changed. The earlier 17-per-site browser checks describe the initial publication;
this data-only correction was verified by exact rendered-pair comparisons.
