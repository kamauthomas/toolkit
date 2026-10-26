# Daily report draft — Thomas Kamau — 26 October 2026

**Summary:** Refreshed the website administration workspace, made website
applications available as a clearly labelled view-only register in Reception,
added controlled calling-letter access and issuance, and tightened the handling
of files kept on the hosting account.

| Task | Time spent | Status |
| --- | --- | --- |
| Improve website administration layout and navigation | Not separately recorded | Completed on demo and production |
| Show website applications in Reception without edit or delete access | Not separately recorded | Completed; 28 production applications synced |
| Remove unnecessary vendor documents, source maps and an obsolete backup from active code folders | Not separately recorded | Completed; 280 reviewed files removed |
| Move historical security recovery material outside Reception and restrict access | Not separately recorded | Completed; 882 files preserved in restricted recovery storage |
| Investigate the missing hosting file-inspection module | Not separately recorded | Completed; matching module absent, hosting installation needed |
| Prepare removal of development tools from hosted applications | Not separately recorded | In progress; hosting prerequisite pending after verified demo rollback |
| Check access restrictions, privacy, page layouts and failure handling | Not separately recorded | Completed with sample data and verified release checks |
| Assess the latest PHP security releases and upgrade readiness | Not separately recorded | Completed; current installed releases match the main security fixes |
| Review and publish completed changes to the remote repositories | Not separately recorded | Completed; existing unrelated drafts preserved locally |
| Design and implement Reception calling-letter access and administrator issuance | Not separately recorded | Completed on demo and production; administrator issuance verified on demo |

**Challenges:** Hosting checks timed out and were stopped until access was
confirmed. Demo exposed a database compatibility issue that was corrected before
production. Demo also needed its own scheduled refresh. The remaining development-tool cleanup needs hosting to enable its file-inspection
module. Demo was restored and checked after this prerequisite failed; the temporary
maintenance tool and scheduled job were removed.
The follow-up confirmed the module is missing rather than simply disabled. An
initial recovery move used an unexpected destination; its checks stopped further
cleanup, all files were accounted for, and the move was corrected and verified.
The initial production letter check found a missing supporting file. The new
features were automatically switched off, the missing file was restored, and
demo and production verification then succeeded.

**Decisions/comments:** Staff can view website applications in Reception; changes
and admissions actions stay on the website. Refresh is scheduled every five
minutes. Failed refreshes keep previous copies available. No test applications
were submitted to production. Approved downloads and license notices were kept.
Older private recovery files were preserved, and existing uncommitted work remains
recoverable in named local Git stashes. Reviewed completed changes have been
pushed to the WordPress and Reception remotes; reports have not been filed externally.
The relocated recovery folder now permits access only to its owner. The final
website and Reception page checks succeeded. The recovery cleanup required no application upload or server software change.
The calling-letter design keeps website letters read-only and lets only
administrators issue letters for Reception applicants. It uses the approved
website template, preserves issued versions and records issuance/downloads.
Generation sends no email/SMS and submits nothing to Mzizi. The module is now
enabled on demo and production. Sample issuance, downloads, access restrictions
and duplicate prevention were verified on demo, and the sample records were
removed. Production checks used an existing website letter without creating
test applicants or issuing test letters.
The user authorised a one-run exception for the missing hosting module so this
urgent release could proceed. That exception is closed; future checks remain
required. Server software and installed libraries were not changed.

**Next working day:** Thomas and reception/admissions staff to review the updated
screens and register; ICT to monitor refresh results and review the retention of
older private recovery holdings. Resources: normal staff accounts and the private
release evidence. Expected outcome: staff acceptance and a clear recovery policy.
Hosting support to install the missing module, followed by ICT's demo-first
development-tool cleanup. Review the additional interactive developer tool that
is currently declared as a production dependency before removing it.
Admissions and Reception staff to review the enabled calling-letter workflow
in their normal accounts. Expected outcome: approved day-to-day use and feedback
on any wording or workflow adjustments.

**ICT metrics:** Two main systems improved; demo and production release checks
passed; 28 production website applications synced; 280 unnecessary files removed
from active code folders; 882 historical recovery files relocated and restricted.
Development-tool removal remains pending hosting assistance. PHP 8.5 is not yet
available for a real compatibility trial, so no live version switch was made.
Submit this draft through the employee's own Report System session.
