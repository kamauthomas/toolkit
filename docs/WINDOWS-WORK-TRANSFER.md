# Toolkit Windows Work Archive

This folder is a curated working archive copied from the Toolkit Linux machine
on 21 August 2026. It is organised for use from the Windows system drive at:

`C:\Users\user\Desktop\Toolkit_Work_Archive_2026-08-21`

## Folder map

- `00_READ_ME` — this guide, source commit references, file inventory, and
  SHA-256 checksums.
- `01_Projects` — clean source snapshots of the active Toolkit systems.
- `02_Reports_and_Planning` — work reports, Report System exports, planning,
  roadmaps, and the Report System quick guide.
- `03_Design_Posters_and_Media` — posters, images, graphics work, and prospectus
  source material.
- `04_XCF_Source_Files` — editable GIMP XCF files copied from Linux Documents.
- `05_Admissions_Templates_and_Tools` — the blank calling-letter template and
  its reusable generator.
- `06_Reference_Material` — redesign research and reference plugin files.

## Deliberately excluded

The transfer does not contain `.env` files, WordPress configuration, SQL or
SQLite databases, private keys, credentials, private storage, Git metadata,
agent sessions/worktrees, dependency folders, caches, application logs, large
server rollback archives, or runtime hosting backups.

Applicant spreadsheets, generated calling letters, and their ZIP archives were
also excluded because they contain personal data. The reusable blank template
and generator were retained.

## Source snapshot rules

Tracked applications were exported from their recorded Git commits instead of
being copied wholesale. This avoids local secrets, caches, and unrelated
machine state. The untracked Virtual Campus application was copied separately
with its credentials, database, dependencies, and runtime storage excluded.

`SOURCE_COMMITS.tsv` records the exact branch and commit used for each tracked
project. `FILES_SHA256.txt` records a checksum for every copied file and can be
checked later from a shell with:

```bash
sha256sum -c 00_READ_ME/FILES_SHA256.txt
```

Do not treat this archive as a live deployment or a backup of production data.
It is an organised, secret-free working copy for the Windows desktop.
