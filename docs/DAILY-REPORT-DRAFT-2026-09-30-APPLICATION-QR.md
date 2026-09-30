# Daily report draft — 30 September 2026

**Summary:** Created and deployed an application QR code that opens the live
Toolkit custom form, with scan counters and downloads in Toolkit Control. Also added an untracked
Lnk.Bio QR with a separate dashboard preview and downloads. Produced a two-page
PDF recording both changes and the legacy Canva-to-Lnk.Bio transition.

| Task | Time spent | Status |
|---|---|---|
| Verify live form, generate QR PNG/SVG, implement scan tracking and dashboard counters, test and deploy demo → production | Approximately 11 minutes | Completed |
| Generate untracked Lnk.Bio QR PNG/SVG and add dashboard panel; verify and deploy demo → production | Approximately 5 minutes | Completed |
| Produce and verify the two-page QR change report PDF | Approximately 4 minutes | Completed |

**Challenges:** Sandbox networking required approved network access; resolved.
Lnk.Bio blocks automated URL checks with Cloudflare HTTP 403; the QR independently
decodes to the exact requested URL, and a normal phone-browser check is pending.
Authenticated live dashboard acceptance remains with staff; local render checks
and deployed file verification passed.

**Decisions and comments:** Keep the existing custom-form/Mzizi integration.
Use existing first-party daily aggregates for the application QR. The Lnk.Bio
QR encodes the supplied URL directly with no Toolkit tracking added. Count tagged form openings once per
browser session, respect Do Not Track, and label measurement limits. One test
scan event was recorded on demo only; no test applications were submitted.

**Plan for tomorrow:** Admissions/communications staff: check Toolkit Control,
scan the QR on a printed sample, then use the supplied PNG/SVG in materials.
Resources: camera phone, printer, WordPress admin access. Expected outcome:
confirmed print readability and visible scan counts.

**ICT department metrics:** 1 system maintained; 1 admissions access/tracking
request resolved; 4 environment deployments (two releases, each demo and production); 4 QR assets
produced; latest release 2026.09.30.2.
