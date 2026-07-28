# Toolkit Africa Strategic ICT Roadmap and Implementation Plan

**Planning date:** 28 July 2026

**Horizon:** August 2026–July 2027

**Currency:** Kenya shillings (KES), exclusive of VAT

**Status:** Draft for management review

## Executive recommendation

Deliver the eleven initiatives as one governed portfolio, not eleven isolated
procurements. Existing code already provides major foundations: the production
WordPress redesign and SEO controls, Smart Lecturer prototype, Laravel reception
system, Flask reporting platform, chatbot prototype, website assistant, and a
fail-closed Mzizi adapter.

Sequence the programme as follows:

1. Governance, data protection, cybersecurity, identity and shared data model.
2. Stabilise reception, reporting, website/SEO and admissions integration.
3. Connect SEO, Google Ads and chatbot to one enquiry-to-enrolment pipeline.
4. Pilot Smart Lecturer and Smart Farm tours.
5. Add HR recruitment, reminders and proportionate endpoint management.
6. Scale only after adoption, security and outcome gates pass.

**Year-one planning envelope:** KES 10.8m–19.7m implementation, KES
1.8m–6.0m ring-fenced Google Ads media, plus 12–15% contingency. Total planning
range: approximately **KES 14.1m–29.6m**. These are ROM estimates, not quotations.

## Current-state evidence

| Asset | Evidence and maturity | Direction |
|---|---|---|
| Website/SEO | Modern WordPress child theme is live with metadata, schema, security headers, sitemaps, AI discovery and first-party metrics; some legacy media/research routes remain. | Optimise existing platform; do not replatform. |
| Smart Lecturer | Vite prototype has controlled grounded answers, captions, speech, 3D controller and licensed Meshy model; production RAG/LMS/final rig remain. | Bounded academic pilot. |
| Reception | Laravel app has encrypted PII, roles, audit, retention and signed website relay tests. Demo files exist but demo currently returns HTTP 500 pending environment/database. | Stabilise before Smart Farm extension. |
| Staff reporting | Flask app has authentication, roles, reports, notifications, principal/admin views and tests. | Harden and extend. |
| Admissions | Accessible WordPress/Mzizi adapter exists but is fail-closed pending vendor authority, sandbox tests, CAPTCHA and privacy acceptance. | Keep Mzizi as system of record. |
| Chatbot | Python chatbot and WordPress assistant exist with FAQ, flow and lead capture. | Consolidate knowledge and handoff. |
| HR/endpoint/reminders | No mature portfolio code identified. | Configure suitable products; avoid unnecessary bespoke builds. |

## Target architecture

- WordPress: public content, SEO landing pages and same-origin forms.
- Mzizi: authoritative application, admission and registration records.
- CRM/lead layer: source, assignment, follow-up and conversion.
- Reception Laravel: visits, follow-ups and Smart Farm reservations.
- Reporting platform: staff reports, approvals, targets and aggregates.
- HR platform: vacancies, candidates, contracts, reminders and access lifecycle.
- Virtual Campus: governed learning content and LMS links.
- Named identity, MFA, least privilege and joiner/mover/leaver process.
- Signed/versioned APIs, idempotency, queues where needed and redacted logs.
- Consent-aware analytics linked to CRM milestones with generated lead IDs.

No initiative should create another master copy of applicant, student, visitor or
staff PII without approved purpose, owner, access and retention.

## Twelve-month roadmap

### Phase 0 — Mobilise and govern (August 2026)

- Appoint sponsor, programme manager, technical lead, DPO/compliance lead and
  business owners.
- Approve data inventory, purposes, retention, processors, incident response and
  minimum security baseline.
- Confirm Mzizi authority, course/fee/intake source, CRM decision, Google accounts
  and HR policies.
- Establish Git, environments, backups, secrets, monitoring and change control.

**Gate:** no broad advertising or production PII integration before ownership,
privacy, retention and access controls are approved.

### Phase 1 — Stabilise existing assets (September–October 2026)

- Resolve reception demo HTTP 500, complete UAT and controlled rollout.
- Security-review/deploy reporting with daily, weekly and monthly workflows.
- Finish media/gallery and technical SEO gaps; establish Search Console/Bing baseline.
- Complete Mzizi sandbox/contract testing.
- Consolidate chatbot knowledge.
- Deploy MFA, password manager, endpoint inventory and recovery monitoring.

### Phase 2 — Recruitment and admissions (November 2026–January 2027)

- Deploy CRM pipeline, assignment and response SLAs.
- Publish SEO topic clusters and course/intake landing pages.
- Start controlled Google Search campaigns with offline conversion outcomes.
- Deploy chatbot-to-human/CRM handoff.
- Enable approved admission status, checklist and payment handoff.
- Pilot HR recruitment and contract reminders.

### Phase 3 — Smart experiences (February–April 2027)

- Pilot Smart Lecturer with one or two approved modules.
- Add Smart Farm tour availability, capacity, confirmations, reminders and check-in.
- Add aggregate executive reporting.
- Pilot transparent endpoint security/device-health monitoring.

### Phase 4 — Scale and assure (May–July 2027)

- Expand only successful pilots.
- Conduct penetration test, processor review and disaster-recovery exercise.
- Optimise Ads to cost per enrolled student, not clicks.
- Review adoption, data quality, support burden and year-two value.

## Initiative plan

### 1. Virtual Campus and Smart Lecturer

Evolve the prototype into a controlled LMS-linked learning assistant. Text,
captions and cited sources remain authoritative; avatar/voice are optional.

- **Timeline:** content/final rig 6–8 weeks; LMS/RAG pilot 8–12 weeks; cohort review 8 weeks.
- **Resources:** academic owner 0.3 FTE, two lecturers 0.2 each, AI/full-stack engineer 1.0, 3D specialist 0.5 temporarily, instructional designer 0.5, QA/accessibility 0.25.
- **Cost:** KES 1.8m–4.5m; recurring KES 50k–250k/month.
- **KPIs:** grounded accuracy, citation coverage, completion, satisfaction, accessibility, preparation time and cost/learner.

### 2. SEO

Continue current technical foundations; add local SEO, governed content clusters,
Search Console/Bing, course schema, digital PR and conversion measurement.

- **Timeline:** baseline 4 weeks; priority pages 8–12 weeks; monthly optimisation.
- **Resources:** SEO lead 0.5, content owner 0.5, developer 0.2, subject approvers.
- **Cost:** KES 600k–1.5m/year.
- **KPIs:** valid indexed pages, non-brand traffic, local visibility, qualified leads, applications and Core Web Vitals.

### 3. Google Ads for recruitment

Start with high-intent Search by course/intake/location. Add remarketing/YouTube
only after consent and lead quality pass. Import qualified/application/enrolment
outcomes from CRM.

- **Timeline:** readiness 3–4 weeks; KES 150k–250k pilot over 6–8 weeks; monthly gates.
- **Resources:** performance marketer 0.5, admissions lead, analytics developer 0.2, creative and finance owner.
- **Cost:** setup/management KES 360k–900k; media KES 1.8m–6.0m/year.
- **KPIs:** qualified lead rate, response SLA, application/admission/enrolment, cost/enrolled student and ROAS.

### 4. Reception and Smart Farm tours

Complete existing Laravel deployment, then add individual/group reservations,
capacity, host assignment, safeguarding, confirmations, reminders, check-in and
cancellation using the same security model.

- **Timeline:** stabilise 3–5 weeks; discovery 2; build/pilot 6–8; review 4.
- **Resources:** Laravel engineer 0.7, reception owner, farm coordinator, QA/security 0.2.
- **Cost:** KES 650k–1.6m; messaging KES 20k–120k/year.
- **KPIs:** completion, no-shows, handling time, utilisation, check-in accuracy and satisfaction.

### 5. HR recruitment automation

Configure an applicant-tracking solution: approved vacancies, minimum candidate
data, scoring, interviews, communication, retention/deletion and audit.

- **Timeline:** process/DPIA 4 weeks; select/configure 6–8; one-vacancy pilot 4.
- **Resources:** HR owner 0.4, implementation specialist 0.5, DPO/legal, IT/QA.
- **Cost:** KES 800k–2.0m; recurring KES 25k–120k/month.
- **KPIs:** time to hire, completion, panel turnaround, communication SLA, cost/hire and timely deletion.

### 6. AI chatbot

Consolidate current assistants around one approved knowledge base, retrieval
service and human handoff. It may guide and create a lead but must not invent
fees/intakes, decide admission or silently submit.

- **Timeline:** governance 4 weeks; secure RAG/CRM 6–8; pilot 4–6.
- **Resources:** AI/full-stack engineer 0.7, admissions owner 0.3, conversation QA, security/DPO.
- **Cost:** KES 900k–2.4m; recurring KES 30k–180k/month.
- **KPIs:** containment, answer audit score, handoff, qualified leads, unsafe-answer rate, latency and cost/conversation.

### 7. Data protection, cybersecurity and compliance

Continuous foundation aligned to Kenya’s Data Protection Act and risk-based
security. Obtain qualified Kenyan DPO/legal advice; this roadmap is not legal advice.

Controls: inventory/classification, notices, DPIAs, processor contracts, retention,
rights handling, MFA, password manager, encryption, patching, EDR, backups,
logging, incident response, secure development, awareness, recovery and penetration tests.

- **Timeline:** gap assessment 4–6 weeks; priorities 90 days; maturity programme 12 months.
- **Resources:** DPO 0.5, security lead/MSP, sysadmin, legal and owners.
- **Cost:** KES 1.2m–3.0m/year.
- **KPIs:** MFA/EDR coverage, patch SLA, restore tests, access reviews, DPIAs, incidents and findings closed.

### 8. Staff reporting platform

Harden/extend the Flask system with standard frequencies, approvals, departmental
targets, reminders, evidence, executive summaries and exports. Avoid hidden surveillance.

- **Timeline:** security 3 weeks; requirements 3; enhancements 6–8; pilot 4.
- **Resources:** Python engineer 0.6, process owner, departments, QA/security 0.2.
- **Cost:** KES 450k–1.2m; recurring KES 15k–60k/month.
- **KPIs:** on-time reports, approval turnaround, completeness, active use and reduced duplication.

### 9. Online admission and registration

Complete supported Mzizi integration. Add status, document checklist, controlled
payment handoff, duplicate detection and human exceptions. Do not store full
applications in WordPress/chatbot.

- **Timeline:** vendor/sandbox 4–6 weeks; mapping/security 4–6; UAT 4; rollout 4.
- **Resources:** admissions owner 0.5, vendor, engineer 0.8, finance/academics, DPO/security/QA.
- **Cost:** KES 1.2m–3.5m subject to vendor/API/licensing.
- **KPIs:** completion, successful submissions, errors/duplicates, processing time and enrolment conversion.

### 10. Staff productivity monitoring

Reject covert screenshots, keylogging and indiscriminate capture. Begin with asset
inventory, uptime, patching, approved software and device health. Any productivity
pilot requires purpose, DPIA, consultation, proportionality, short retention and appeal.

- **Timeline:** policy/DPIA/consultation 6–8 weeks; 10–20 endpoint pilot 8; review.
- **Resources:** HR, DPO, IT 0.5, security provider, staff representatives and legal.
- **Cost:** KES 1.5m–4.0m for roughly 50–100 endpoints; validate quotations.
- **KPIs:** managed devices, patch/EDR health, downtime, support resolution and complaints—not uncontextualised individual scores.

### 11. Contract expiry and HR reminders

Implement within the selected HR platform where possible: restricted contract
metadata, 90/60/30/14/7-day reminders, acknowledgement, escalation, probation,
leave/document reminders and audit.

- **Timeline:** policy/data 3 weeks; configure 3–5; parallel run 4.
- **Resources:** HR owner 0.2, engineer 0.2, approvers and DPO/security.
- **Cost:** KES 200k–650k, lower if bundled with HR.
- **KPIs:** metadata completeness, delivered/acknowledged reminders, overdue actions and missed expiries.

## Resource model

Core team: sponsor 0.1 FTE; programme manager 1.0; technical lead 0.7; DPO 0.5;
sysadmin 0.5–1.0; admissions/marketing owner 0.5; HR 0.3; academic 0.3;
reception/farm 0.2; reporting owner 0.3. Peak delivery needs approximately 3–5
technical FTE across PHP/Python, integration/data, front end/accessibility, AI and
QA/security. Use time-bound specialists for 3D, paid media, legal and penetration tests.

Infrastructure: separate demo/production, managed backups, secrets/password
management, MFA, monitoring, transactional messaging, CRM, HR/ATS, EDR and
possibly LMS licences; institutional Google/Search accounts with least privilege.

## Cost scenarios

| Scenario | Implementation | Ads media | Contingency | Total |
|---|---:|---:|---:|---:|
| Lean reuse/pilot | KES 10.8m | KES 1.8m | KES 1.5m | KES 14.1m |
| Recommended controlled growth | KES 15.2m | KES 3.6m | KES 2.5m | KES 21.3m |
| Accelerated/vendor-heavy | KES 19.7m | KES 6.0m | KES 3.9m | KES 29.6m |

Do not add initiative maxima mechanically because shared staff, CRM, HR, hosting
and security overlap. Obtain three comparable quotations, confirm VAT/FX, and
separate implementation, licence, usage and media.

## First 90 days

**Days 1–30:** approve governance/budget; security/data baseline; resolve reception
demo HTTP 500; review reporting deployment; confirm Mzizi sandbox; decide CRM;
finish gallery/video/SEO gaps.

**Days 31–60:** pilot reporting; reception UAT/production decision; configure CRM
and SLAs; prepare SEO/Ads pilot; consolidate chatbot; select HR/ATS and EDR;
define Smart Lecturer pilot.

**Days 61–90:** launch controlled Ads with attribution; complete Mzizi UAT;
deploy approved chatbot/CRM handoff; start HR/reminder pilots; define Smart Farm
booking; conduct restore/access/vulnerability checks; present next-quarter decision.

## Governance and decisions

Every initiative requires a named owner/outcome, approved data/retention/access,
demo acceptance, accessibility/security tests, backup/monitoring/rollback, training,
and 30-day benefits review.

Management must approve: sponsor and owners; budget scenario; Mzizi vendor/sandbox;
CRM and HR selection; Ads pilot and enrolment capacity; endpoint-monitoring policy;
Smart Lecturer modules/reviewers; and mandatory privacy/security controls.

Stop or redesign work that lacks ownership, duplicates PII, cannot be restored,
depends on unsupported scraping/session automation, or measures people
disproportionately.

## Assumptions

ROM estimates are at July 2026 and exclude VAT. Existing code/hosting is reused
where safe. Major hardware/network refresh, biometrics, payment transaction fees and
full video production are excluded. Costs depend on volumes, Mzizi/LMS/API terms,
cloud region and procurement. Google Ads media may be paused monthly.
