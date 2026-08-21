# Windows Work Transfer — Verification Log

**Date:** 21 August 2026

**Destination:** `C:\Users\user\Desktop\Toolkit_Work_Archive_2026-08-21`

**Linux device:** Windows system NVMe, `/dev/nvme0n1p3` (`ntfs3`)

**Method:** browser-free scripted export from curated Linux sources

## Scope copied

- Clean Git snapshots of six active Toolkit projects.
- Sanitised untracked Virtual Campus source.
- Website reports, Report System exports, plans, and technical documents.
- Posters, graphics, photos, prospectus material, and editable design sources.
- All 27 top-level XCF files requested from Linux Documents.
- Blank admissions calling-letter template and generator only.
- Redesign research and WordPress plugin reference material.

## Exclusions checked

The export rules excluded live environment files, WordPress configuration,
databases, private keys, credentials, private storage, Git and agent state,
runtime dependencies/caches/logs, hosting backups, server rollbacks, applicant
spreadsheets, generated calling letters, and calling-letter ZIP archives.

The filename scan found no prohibited secret/database paths. The applicant-data
filename check returned only Reception System source-code files whose class names
contain “Applicant”; no applicant spreadsheet, letter, or ZIP output was found.

## Verification results

- Initial archive inventory: 1,929 files, 457 directories, approximately 1.3 GB.
- SHA-256 manifest: 1,927 entries checked, zero failures.
- Linux Documents XCF comparison: 27 source and 27 destination files, zero
  checksum differences.
- Private-key and common token signature scan: zero matches.
- TruffleHog filesystem scan: the initial scan identified only a SHA-256 string
  in `FILES_SHA256.txt` as a Phrase access-token pattern. A second scan covering
  all six content folders and excluding the checksum manifest returned zero
  findings.

The checksum manifest is regenerated after final report/log updates, so the
archive's `INVENTORY.txt` is the authoritative final file count.

## Mount handling

The NTFS partition was remounted through UDisks in normal read/write mode; no
force or hibernation-removal option was used. The Windows Desktop folder's owner
write bit was enabled only for the transfer and restored to its original mode
after final verification.
