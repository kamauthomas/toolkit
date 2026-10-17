# Daily report draft — 17 October 2026

Prepared by Thomas · ICT · ready for review and entry by the account owner.
No report has been filed automatically. Enter actual time spent before filing.

## Summary

Investigated applications that were saved by Toolkit but not confirmed in
Mzizi. Confirmed three saved applications with the reported 409 error and
identified weaknesses in delivery tracking, repeat attempts and course-list
availability. Deployed corrections after checks using sample data, and verified normal course lookups on both sites.

## Tasks completed

| Activity | Time spent | Status |
|---|---|---|
| Checked saved application outcomes and the reported Mzizi error without sending applications again | To enter | Completed |
| Corrected missing study and sponsorship information in the delivery process | To enter | Completed |
| Added delivery history, scheduled safe retries and protection against repeated applications | To enter | Completed |
| Retained course choices during service interruptions, with periodic checks for changes | To enter | Completed |
| Tested delivery failures, repeated actions, interruptions and course availability using sample data | To enter | Completed |
| Required a security and flaw review before production changes are committed or released | To enter | Completed |
| Verified demo and production release and scheduled operation | To enter | Completed |

## Challenges

Mzizi returns a general error that does not explain its internal cause. Its
records or support logs are needed to confirm the outcome of affected
applications. Sending those applications again without checking could create
duplicates, so uncertain deliveries remain for Admissions review.

Previously saved course choices can remain available during an outage; a choice
that has never been loaded still needs an initial successful connection.

## Decisions and comments

Retry automatically only when no application submission has been sent. Keep
uncertain submissions visible for Admissions to confirm in Mzizi. Retain a
clear history without copying applicants' personal details into new logs.
No production test application was submitted. No live security attack probes
were run. Email and SMS verification remain pending provider configuration.

## Plan for the next working day

| Task | Owner | Resources | Expected outcome |
|---|---|---|---|
| Check affected applicants and courses in Mzizi before confirming or resending | Admissions administrator | Mzizi access and Toolkit application references | Accurate delivery status without duplicate applications |
| Obtain the reason for the three recorded 409 outcomes and a supported automated delivery-check method | ICT / Mzizi support, through the authorized contact | Mzizi processing logs and integration guidance | Confirmed cause and safer future reconciliation |
| Review scheduled delivery checks and retained course choices | ICT | Toolkit Control and private scheduler status | Timely recovery and reliable form choices |

## Department metrics

Systems reviewed: Toolkit admissions and its Mzizi connection. Baseline records
reviewed: 27; 11 marked delivered, 14 unconfirmed, one failed and one stuck in
progress. Three unconfirmed records retained the reported 409. Local automated
checks passed: 73 delivery/course checks and 524 existing application checks.
Deployments verified: two, demo and production, version 2026.10.17.1. Both
scheduled checks completed successfully without sending applications. Six normal
course-choice lookups passed across the two sites. Five saved course-information
collections were confirmed on each site. The old application stuck in progress
was moved to the review list without resending it: the final production counts
are 11 delivered, 15 unconfirmed and one failed. The three recorded 409 cases
still require a check in Mzizi.
