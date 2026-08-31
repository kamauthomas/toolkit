# Toolkit OKRs — August 2026 to July 2027

**Prepared:** 31 August 2026  
**Scope:** Website, Reception, Report System, Smart Lecturer, Virtual Campus,
Chat-bot, security, deployment operations and the poster/communications library  
**Status:** Draft for management approval; live tracking module available locally

## How to use this document

These are outcome measures for the Toolkit portfolio, not a list of coding
tasks. The baseline records what is evidenced in the working repositories as of
31 August 2026. Targets become commitments only after management assigns an
owner, confirms the denominator and approves the policy behind each measure.

The Report System now provides the working register for these objectives. An
executive can explicitly load all eight objectives and thirty proposed key
results as idempotent drafts, then assign owners, approve baselines/targets and
keep append-only progress/evidence updates. Poster and campaign work is recorded
by referencing the approved asset or campaign in the key-result evidence trail.
Activation is blocked until the objective and all its key results have owners
and have been moved out of Draft, so proposed targets cannot silently become
staff commitments.

## Objective 1 — Make the public website a trusted digital front door

**Outcome:** Prospective learners can find accurate course information, prices,
stories and application paths quickly and confidently.

| Key result | Baseline / evidence | Target by July 2027 | Owner / decision |
|---|---|---|---|
| Availability of priority public pages | Current release history and route checks; no single portfolio SLO yet | ≥99.5% monthly availability, with every high-priority route checked after release | ICT + Communications; approve page list and monitoring owner |
| Course and price accuracy | Approved 2026 catalogue is available; controlled pricing switch exists | 100% of public course/price entries reconciled to the approved catalogue each release | Admissions + Finance; approve source-of-truth sign-off |
| Application journey reliability | Website-to-Reception relay and applicant encryption are implemented and verified | ≥98% of valid demo submissions reach the authorised destination; production mail remains fail-closed until authenticated delivery is approved | ICT + Admissions; approve mail provider |
| Security maintenance | Element Pack Lite incident contained; patched Eduma release still pending | Patched theme deployed through demo-first review; zero known critical/high findings after each release | ICT; approve maintenance window |

## Objective 2 — Replace paper-first reception with a consistent, secure welcome

**Outcome:** Visitors and applicants complete a guided check-in while staff retain
accurate records without manual filing.

| Key result | Baseline / evidence | Target by July 2027 | Owner / decision |
|---|---|---|---|
| Digital registration coverage | QR modes, autosave drafts and completion attribution are implemented | ≥90% of eligible on-site registrations captured digitally, measured monthly | Reception lead; confirm eligible-visit denominator |
| Data completeness | Required fields and `Other` explanation are enforced | ≥95% of completed records contain all required contact/course fields | Reception lead |
| Follow-up responsiveness | Staff records and exports exist; WhatsApp delivery is not enabled | 90% of records requiring follow-up have a logged outcome within two working days | Reception + Admissions; approve WhatsApp owner/template |
| Privacy and access | Private staff path, encryption, throttling and audit logs are deployed | Zero unauthorised staff-area access events; quarterly access review completed | ICT + Administration |

## Objective 3 — Deliver a safe, learner-friendly Virtual Campus pilot

**Outcome:** A small approved learner group can learn, submit evidence and join
live classes with clear support and reliable progress records.

| Key result | Baseline / evidence | Target by July 2027 | Owner / decision |
|---|---|---|---|
| Pilot launch | Local LiveKit rehearsal passed with one lecturer and two learners; production readiness is blocked on hosting/provider inputs | Run an approved pilot with a named lecturer and learner cohort, then publish a review decision | Academic lead + ICT; approve cohort, host and live-video service |
| Learning evidence | Video heartbeats/resume, checkpoints, assignments and assessment authoring are implemented locally | ≥90% of pilot learner progress events have verifiable evidence, not self-asserted completion | Registrar + Academic lead; approve evidence policy |
| Live-class experience | Two-way video/audio, captions, screen sharing, role labels and leave controls verified locally | ≥95% of scheduled pilot classes complete without a critical learner-blocking incident | Lecturer lead + ICT; approve support escalation |
| Accessibility and low-bandwidth support | Captions, reduced-motion/text fallbacks and lower-data media exist locally | 100% of pilot lesson videos have captions and a documented low-bandwidth option | Academic lead |

## Objective 4 — Turn Smart Lecturer into governed learner support

**Outcome:** Learners receive useful, consistent answers without the system
inventing policies, fees or admissions decisions.

| Key result | Baseline / evidence | Target by July 2027 | Owner / decision |
|---|---|---|---|
| Curated answer coverage | Prototype supports curated Toolkit questions with sources, captions and speech | 100% of published answers trace to an approved source; monthly content review completed | Academic + Communications |
| Human review and escalation | Prototype boundaries are documented; production authority is not granted | All uncertain/high-impact questions show a clear human-support route; zero autonomous admission decisions | Admissions lead; approve escalation wording |
| Learner usability | Text, speech, captions and fallbacks are demonstrated | ≥85% positive rating in a supervised learner review before any public pilot | Academic lead |

## Objective 5 — Make daily work, intake and decisions visible

**Outcome:** Managers can see what was done, what is verified, what is behind
target and who owns the next action.

| Key result | Baseline / evidence | Target by July 2027 | Owner / decision |
|---|---|---|---|
| Daily report discipline | Drafts, submissions, review states, dashboards and PDFs work; new filters/XLSX export are implemented locally | ≥90% of expected staff reports submitted by the agreed deadline each month | Department heads; approve deadline/denominator |
| Review turnaround | Submitted → Reviewed → Approved workflow exists | ≥95% of submitted reports reviewed within two working days | Department heads |
| Admissions and intake accountability | Fee-paid verified enrolments now drive intake actuals; targets are implemented locally | Monthly target review completed for every active intake officer; gaps have a recorded action | Admissions + Finance; confirm fee-paid source |
| Meeting action closure | Minutes, owners, due dates and status history are implemented locally | ≥85% of actions closed by due date, with blocked items carrying an explanation | Management; approve action policy |
| Wingu continuity | No API/SSO; approval-gated isolated browser workflow is documented | 100% of dispatched rows use Wingu-presented project values, approved attendance input and reload verification | ICT + employee; provide attendance-sheet format and rejected-row owner |

## Objective 6 — Protect institutional data and keep services recoverable

**Outcome:** Security incidents are contained quickly, sensitive information is
not exposed through convenience shortcuts, and the team can restore service.

| Key result | Baseline / evidence | Target by July 2027 | Owner / decision |
|---|---|---|---|
| High-risk exposure | WordPress incident contained; normal accounts preserved; scans reported zero infected files | Zero unresolved critical/high security findings older than 14 days | ICT; approve vulnerability reporting channel |
| Secret handling | Applicant encryption keyring and admin vault procedures exist; no secrets in committed docs | 100% of production secrets held outside Git and reviewed quarterly | ICT + Administration |
| Recovery readiness | Deployment and backup procedures are documented; restore ownership remains open in some systems | Quarterly restore exercise for website, Reception and Report System with recorded result | ICT; name restore owner and backup location |
| Access governance | Role-scoped campus/report/reception controls exist | Quarterly review of privileged accounts, staff paths and integrations; inactive access removed within two working days | Administration + ICT |

## Objective 7 — Use the Toolkit brand and poster library consistently

**Outcome:** Public, print and learning communications look like one institution
and approved campaign work can be reused without losing editable sources.

| Key result | Baseline / evidence | Target by July 2027 | Owner / decision |
|---|---|---|---|
| Asset organisation | `imgs/` contains 172 files (~622 MB), including 128 poster files and editable XCF sources | 100% of approved campaign assets indexed with purpose, status and source format | Communications; approve asset naming/index owner |
| Brand compliance | Brand registry defines orange, olive, teal, Tahoma and approved logo/photo paths | ≥95% of sampled public/print outputs pass the brand checklist before release | Communications |
| Reuse and release readiness | Catalogue, prospectus, posters, testimonials and campaign materials are retained | Every campaign release includes web, print/social and editable-source handoff where applicable | Communications + ICT |

## Objective 8 — Operate a calm, auditable delivery process

**Outcome:** Changes reach users through repeatable checks, with clear ownership
and no disruptive browser-click deployment.

| Key result | Baseline / evidence | Target by July 2027 | Owner / decision |
|---|---|---|---|
| Demo-first release discipline | Reception and WordPress deployment procedures are documented; Report System modules are local-only | 100% of production releases have a demo result, backup reference and post-release route check | ICT |
| Portfolio ownership | Owner trackers exist for Reception, Virtual Campus, Report System and Wingu decisions | 100% of open launch blockers have an owner and next action reviewed monthly | Management |
| Operational documentation | Whole-toolkit briefing, presentation summary, training guides and worklogs exist | Every production system has an operator guide, rollback note and current status page | ICT + system owners |

## First-quarter priorities (August–October 2026)

1. Approve this OKR set, owners and denominators.
2. Review the new Report System modules locally, then make a controlled demo
   deployment decision.
3. Provide the approved attendance-sheet format and rejected-Wingu-row owner;
   capture one authenticated Wingu review session without submitting data.
4. Approve the Virtual Campus pilot cohort, hosting and live-video arrangements.
5. Complete the patched WordPress theme demo review and the Reception WhatsApp
   delivery ownership decision.
6. Index the poster library and mark approved versus source-only assets.

## Portfolio evidence

- Whole-toolkit status: `wordpress/docs/TOOLKIT-BRIEFING-2026-08-28.md`
- Non-technical speaking notes: `wordpress/docs/TOOLKIT-PRESENTATION-SUMMARY-2026-08-28.md`
- Reception owner inputs: `reception-system/docs/OWNER_INPUT_TRACKER.md`
- Virtual Campus owner inputs: `SmartLecturer_VirtualCampus/virtual-campus/docs/OWNER_INPUT_TRACKER.md`
- Report System owner inputs: `report-system/OWNER_INPUT_TRACKER.md`
- Report System/Wingu design: `report-system/WINGU_BOX_INTEGRATION_DESIGN.md`
- Working OKR register: Report System `/okrs` module (local review currently at
  `http://127.0.0.1:5155/okrs`)
