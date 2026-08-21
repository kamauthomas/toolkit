# Toolkit Windows Workspace

This is a curated, updateable working mirror of Toolkit material from Linux. It
uses a stable location on the Windows system drive:

`C:\Users\user\Desktop\Toolkit_Workspace`

It is not a dated archive. Re-running the synchronization updates the same
organised folders as work progresses.

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

The transfer does not contain live `.env` files, WordPress configuration, SQL
or SQLite databases, private keys, credentials, private storage, Git metadata,
agent sessions/worktrees, generated dependency trees, caches, application logs,
large server rollback archives, or runtime hosting backups. Tracked
`.env.example` files contain placeholders only and are retained where a project
needs them for setup.

Applicant spreadsheets, generated calling letters, and their ZIP archives were
also excluded because they contain personal data. The reusable blank template
and generator were retained.

## Source snapshot rules

Tracked applications were exported from their recorded Git commits instead of
being copied wholesale. This avoids local secrets, caches, and unrelated
machine state. The untracked Virtual Campus application was copied separately
with its credentials, database, dependencies, and runtime storage excluded.

`SOURCE_COMMITS.tsv` records the exact branch and commit used for each tracked
project. `SYNC_HISTORY.tsv` records when the mirror was refreshed.
`FILES_SHA256.txt` records a checksum for every workspace file except the
checksum manifest itself and can be checked later from a shell with:

```bash
sha256sum -c 00_READ_ME/FILES_SHA256.txt
```

## Keeping it current

From Linux, mount the Windows system volume normally and run:

```bash
./scripts/data-transfer/sync-toolkit-to-windows.sh
```

Each managed subfolder is synchronized independently. Files removed from a
managed Linux source are removed from its Windows mirror, but the script never
deletes unrelated folders elsewhere on the Desktop. Git projects are rebuilt
from their current commits before synchronization, so stale tracked files do
not accumulate.

Do not treat this workspace as a live deployment or a backup of production
data. It is an organised, secret-free working mirror for the Windows desktop.
