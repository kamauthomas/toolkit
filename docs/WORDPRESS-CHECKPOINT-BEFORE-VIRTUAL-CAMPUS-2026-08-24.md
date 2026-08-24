# WordPress checkpoint before Virtual Campus work

**Checkpoint date:** 24 August 2026  
**Branch:** `wordpress-modernisation`  
**Checkpoint commit:** `3740e3a`  
**Production release:** `2026.08.24.5`

This checkpoint marks the point at which active work moves from the public WordPress site to the independent Toolkit Virtual Campus application.

## Complete and verified

- Production publishes the approved 7 July 2026 catalogue: 15 course cards, 15 distinct images, and the approved fees on listing and detail pages.
- The Footprint UI uses the simpler poster-led implementation requested after the first redesign was rejected as over-engineered.
- The floating WhatsApp contact is restored.
- Admissions retry/calling-letter safety and versioned application-encryption code are deployed.
- The 20 August production incident was contained and its browser-free containment procedure retained.
- Release-specific browser-free cPanel deployment scripts and rollback folders exist for the recent releases.
- The organised, secret-free Windows `Toolkit_Workspace` mirror and repeatable sync script exist.
- Production returned `X-Toolkit-Release: 2026.08.24.5`; the pricing verification found 15 course cards and 15 fee labels.
- The repository was clean and fully pushed at commit `3740e3a` when this checkpoint was recorded.

## Open WordPress follow-ups

1. **Applicant encryption migration:** configure/confirm the private current and legacy keyring, take a fresh private database backup, run the counts-only dry run, migrate version-1 records, and verify an authorised applicant detail view, CSV export, and calling-letter generation. Do not remove legacy key material until failures and conflicts are zero.
2. **Analytics audit:** independently validate the low Toolkit Analytics figures against collection-tag coverage, consent behaviour, caching, filtering, and server traffic. Completed SEO work does not by itself validate analytics collection.
3. **Windows refresh:** rerun `scripts/data-transfer/sync-toolkit-to-windows.sh` when the NTFS volume is reliably writable so the workspace receives commits through this checkpoint.
4. **Unified deployment CLI:** the release-specific scripts are operational, but the proposed general deployment engine remains unfinished and is not a current blocker.

## Safety and pickup notes

- Never test-submit an application on production because it relays to Mzizi immediately; use demo.
- Keep real encryption keys, credentials, applicant data, databases, and WordPress configuration out of Git, reports, and Windows transfers.
- Use the dedicated browser-free release scripts; demo first, verify, then production.
- Resume WordPress from commit `3740e3a` and read `WORKLOG.md`, `docs/APPLICATION-ENCRYPTION-KEYRING.md`, and `docs/APPROVED-PRICING-RELEASE-2026.08.24.5.md` before changing the open items.

## New active objective

Bring `/home/t316/Desktop/Projects_father/toolkit/SmartLecturer_VirtualCampus/virtual-campus` to a locally running, testable state, review the untracked implementation left by Claude, preserve valid work, repair failures, and document every accepted change in that repository's `WORKLOG.md`.
