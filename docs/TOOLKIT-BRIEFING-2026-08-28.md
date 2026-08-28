# Toolkit for Skills & Innovation — Whole-Toolkit Briefing

**Date:** 28 August 2026  
**Scope:** `/home/t316/Desktop/Projects_father/toolkit` and its working
projects, operational documents, reports and poster/image library.  
**Audience:** management, ICT, admissions, finance, academic and communications
leads.

## Executive position

Toolkit now has a live public website, a deployed Reception system, a local
Virtual Campus pilot with verified live video, a Smart Lecturer presentation
prototype, a staff reporting platform, a guarded chatbot, a graphics workspace,
prospectus/course materials and a documented strategic ICT plan.

The strongest operational systems are the public website and Reception. The
Virtual Campus is functioning locally and its live-class path has passed a real
multi-user browser rehearsal, but it is not yet deployed because the production
host, LiveKit/TURN, captions, mail, academic data and policy gates are still
awaiting approval. The remaining work is therefore a controlled transition from
tested foundations to governed production operation—not a claim that every
prototype is already a live institutional service.

## Workspace inventory

The Toolkit directory contains the following major areas:

| Area | Purpose | Current position |
|---|---|---|
| `wordpress/` | Public website, SEO, admissions/reception relay, reports, deployment and security records | Production website line with extensive verified release history; local tree contains ongoing transfer-script edits |
| `reception-system/` | Visitor check-in, applicant guidance, QR attribution and staff Reception Control | Release `2026.08.25.1` deployed to demo and production; training and owner-input guides are documentation |
| `SmartLecturer_VirtualCampus/` | Smart Lecturer prototype and independent Laravel Virtual Campus | Prototype remains bounded; Virtual Campus is a local pilot with live video and assessment authoring verified |
| `report-system/` | Daily staff reporting, review/approval, dashboards, PDFs and role controls | Working Flask platform; proposed admissions/intake/minutes/incentive modules remain follow-up work |
| `Chat-bot/` | Toolkit FAQ, guarded assistant and application lead flow | Functional code with FAQ matching, application state machine, admin/API and metrics; production hardening remains required |
| `graphics/` | Brand-locked Photopea/graphics workspace | Brand configuration, approved logo/photo paths, working/completed artwork and health checks are present |
| `imgs/` | Posters, raw design sources, social assets, visitor passes, map and blog media | 172 files, approximately 622.35 MB; editable XCF and print-ready outputs retained |
| `prospectus/`, `documents/` | Approved catalogue, prospectus and admissions documents | 2026 catalogue/prospectus outputs available in DOCX/PDF form |
| `web-redesign/` | Separate Laravel redesign exploration | Scaffold and UI specification exist; application backend and content wiring remain incomplete |
| `strategic-ict-roadmap/` | August 2026–July 2027 portfolio roadmap | Draft planning envelope and sequencing for governance, recruitment, campus, HR and security |
| `sms/`, `toolkit-private-storage/` | Calling letters, private operational outputs and templates | Keep outside public webroots; do not mirror secrets or personal output to shared media folders |

Inventory totals include installed dependencies, caches and generated artifacts
where those exist; they are not a measure of production code size.

## 1. Public website and admissions surface

### What is working

- Toolkit-branded WordPress child theme with responsive homepage, institutional
  pages, course pathways, stories, testimonials, gallery, footprint, graduation
  and contact experiences.
- Approved course catalogue and pricing work is kept behind controlled switches
  where appropriate; the public site retains the external Mzizi application as
  the authoritative application system.
- Technical SEO baseline includes canonical URLs, titles/descriptions, schema,
  sitemaps, demo no-index behavior, production indexing, AI discovery files and
  first-party aggregate metrics.
- Website-to-Reception enquiries use a signed server-to-server relay. Applicant
  details use versioned authenticated encryption with legacy decryption support
  and a dry-run migration path.
- Browser-free, demo-first deployment scripts preserve server drift, back up
  remote files and verify routes/assets after deployment.

### Security containment already completed

The documented Element Pack Lite incident created nine unauthorized WordPress
Administrator accounts through hijacked authenticated sessions. The containment
work removed only those identified accounts, revoked affected sessions and
application passwords, rotated WordPress salts/keys, quarantined the abandoned
Eventer/Old Sites/addon artifacts and removed the abandoned cron/maintenance
surface. Normal user accounts were preserved.

The recorded cPGuard scans reported zero infected files, including the post-
containment scan. Livemesh Addons was found active with stored-XSS findings and
was reversibly deactivated. Production remained live during containment.

### Website items still requiring action

- Replace the active Eduma 5.3.0 parent theme with an approved patched package
  (5.7.7+), testing on demo first.
- Keep calling-letter email disabled until authenticated SPF-aligned SMTP or a
  transactional provider is configured and delivery is verified.
- Complete authorised Site Kit/Search Console connection and retain a baseline.
- Resolve legacy `toolkitiskills.com` subpage HTTP 500 responses with access to
  the old host/DNS; the current cPanel account cannot fix that old document root.
- Continue editorial review of legacy posts and consolidate only confirmed
  duplicates; technical SEO cleanliness is not an editorial approval.
- Keep the local transfer-script changes reviewed before any Windows sync or
  release automation is enabled.

## 2. Reception system

### Functionality now available

- Visitor check-in with server-generated reference and authoritative date/time.
- Applicant course-guidance form kept separate from the official Mzizi
  application.
- On-site and external QR modes, pseudonymous scan counts and one-record
  completion attribution.
- Encrypted, expiring QR drafts with autosave; drafts never count as physical
  attendance.
- Required explanation when a visitor/applicant selects `Other`.
- Private staff path, active-account enforcement, roles, throttling, audit logs,
  encrypted fields, manager exports and no-store/no-index staff responses.
- Administrator-only encrypted integration settings with write-only secrets.

The system handbook states that release `2026.08.25.1` is deployed to demo and
production. The current Reception feature suite passes 28 tests and 165
assertions. The operator training handout is available as both Markdown and PDF.

### Not yet operational

WhatsApp delivery is not enabled. Host directory, approved utility template,
queued delivery records, webhook verification, retries, acknowledgement and a
fallback channel still need implementation and approval. Production CIDR
whitelisting needs the server-visible egress address. True offline submission
needs an institution-owned kiosk decision, encrypted outbox and sync policy.

Direct owner inputs are recorded in
[`reception-system/docs/OWNER_INPUT_TRACKER.md`](../reception-system/docs/OWNER_INPUT_TRACKER.md).

## 3. Smart Lecturer and Virtual Campus

### Smart Lecturer prototype

The original Smart Lecturer project is a bounded presentation prototype. It
demonstrates curated Toolkit question-and-answer content, visible sources,
captions, bundled speech, portrait/3D presentation adapters, reduced-motion and
text-only fallbacks. It is not yet a production retrieval-augmented lecturer,
student-record system or autonomous academic authority.

Natural voice/viseme timing, a final licensed avatar/rig, provider selection and
human review remain explicit prototype gates. The text, captions and sources are
the authoritative output.

### Virtual Campus now available locally

- Account/session controls and role-scoped navigation.
- Course catalogue, enrolment, finance-linked access and registrar lifecycle.
- Modules, lessons, verified knowledge checkpoints and evidence-based progress.
- Captioned on-demand video with resume position, watched-time evidence and a
  low-bandwidth fallback rendition.
- Written assignments, lecturer marking, feedback, announcements, discussions,
  calendar, notifications, results, certificates and support queue.
- LiveKit token flow, two-way video/audio, screen sharing, captions, network
  status, Host/Learner identification and Leave classroom controls.
- Lecturer/admin assessment authoring: draft or published server-scored checks
  with pass mark, attempts, question options, points and explanations.

The disposable LiveKit browser rehearsal passed all ten checks with one
lecturer and two learners, including role visibility, lecturer-only captions,
camera, microphone, screen share, signed attendance join/leave events and
positive attendance duration. The test now uses a temporary database and cannot
wipe the normal local campus database.

The current Campus suite passes 57 tests and 226 assertions, and the Vite
production build succeeds.

### Production boundary

`php artisan campus:readiness --json` remains **not ready** because live media
requires production LiveKit credentials and approved live captions. No campus
hostname or deployment environment is currently provisioned in the checked
cPanel inventory. The Campus must stay closed to real learner intake until the
owner action tracker is satisfied.

The direct tracker is
[`virtual-campus/docs/OWNER_INPUT_TRACKER.md`](../SmartLecturer_VirtualCampus/virtual-campus/docs/OWNER_INPUT_TRACKER.md),
and the module-by-module map is
[`virtual-campus/docs/MODULE_STATUS.md`](../SmartLecturer_VirtualCampus/virtual-campus/docs/MODULE_STATUS.md).

## 4. Staff Report System

The Flask reporting platform is the institutional daily-report record. It
supports employee drafts and submissions, branded PDF output, review states
(Submitted → Reviewed → Approved), role-scoped dashboards, notifications,
profiles, password changes, account lockout/unlock and executive visibility.

The first operational expansion is now implemented locally: authorised staff
can capture an admissions follow-up record, see records within their scope,
assign a verification outcome, and retain a time-stamped decision history. A
record owner is notified when another authorised reviewer changes the outcome.
This module is not deployed yet; it needs a demo review before hosting changes.

The existing proposal also identifies future normalized modules for admissions
monthly intake targets, incentives, meeting minutes/action items, notification
rules/delivery logs, Excel export, reminders and richer department dashboards.
Those remain proposals/TODO items, not all current live workflows.

The deployment postmortem records a prior Passenger/template mismatch that took
the reports and main domains offline. Future releases must deploy the application,
templates and static files atomically, retain a safe error fallback and verify
the cPanel WSGI/restart configuration after any Python App recreation.

## 5. Chatbot and assistant

The Chat-bot repository contains:

- FAQ matching with aliases/fuzzy scoring for courses, fees, location,
  accreditation, payments, duration, requirements and intakes.
- A controlled application-conversation state machine that captures a lead and
  can relay it through a signed webhook when explicitly enabled.
- Session management, guarded prompts, blocked technical/unsafe requests,
  conversation logging, admin application listing/status changes and health/
  metrics endpoints.

Before production use, it needs a named hosting owner, authenticated/admin-only
application endpoints, restricted CORS origins, protected secrets, an approved
CRM/Mzizi handoff, error monitoring without PII capture and a vendor-independent
knowledge refresh process. It must never invent fees, intakes or admission
decisions.

## 6. Brand, graphics and poster library

The brand registry in `graphics/config/brand.json` defines:

- Toolkit for Skills & Innovation identity;
- orange `#ff4c00`, olive `#969e2a` and teal `#006a68`;
- Tahoma heading/body typography;
- approved logo and photo locations.

The `imgs/posters/` library contains 128 files, approximately 603.3 MB:

| Collection | Contents |
|---|---|
| `prods/` | 19 production poster exports for welding, solar, French, HR, farming, TVET and related programmes |
| `raw/` | 9 editable XCF/source files |
| `refers/` | 11 referral and campaign assets, including XCF sources |
| `samples/` | 45 design samples across courses, intakes, visitor and outreach themes |
| `visitors/` | visitor-pass artwork, approvals, print-ready PDFs and ZIP packages |
| root/social files | Facebook, Instagram, LinkedIn, TikTok, X/Twitter, WhatsApp and YouTube assets |

Across `imgs/posters`, the file mix is 78 PNG, 7 JPEG, 13 XCF, 27 PDF, 2 ZIP
and one text manifest. Raw XCF files are the editable masters; production PNG/
JPEG/PDF files are the delivery outputs. Do not publish raw sources or visitor
pass packs without the intended approval and distribution context.

Other image groups include four footprint references, 17 map images and 20 blog
asset files. The graphics workspace contains additional approved exports,
working files and brand assets; its health check should be run before design
changes.

## 7. Prospectus, course and admissions materials

The workspace retains the approved 2026 course catalogue, admissions forms,
admission requirements, consent material, Toolkit prospectus outputs and calling-
letter templates. These are source materials for website course pages, Reception
guidance, chatbot answers, Virtual Campus seed content and campaign creatives.

The single-source rule remains important: fee, intake, accreditation and entry-
requirement claims should be confirmed against the approved catalogue/Finance or
academic owner before being copied into a public page, poster, chatbot answer or
course assessment.

## 8. Windows workspace and transfer operation

The previous Windows transfer work created a stable `Toolkit_Workspace` layout
on the Windows NVMe desktop, with project repositories, reports, briefing notes,
media/design work, XCF sources and safe admissions tools separated from secrets.
The recorded transfer contained 1,936 files and 457 directories, with all 27 XCF
files matching byte-for-byte and prohibited secret/database/applicant-output
scans passing.

The ongoing sync remains dependent on a reliable writable NTFS mount. Keep
`.env`, credentials, database dumps, cookies, applicant exports and private
deployment records out of the Windows mirror. The transfer scripts currently
have uncommitted local edits and must be reviewed before scheduling automation.

## 9. Cross-toolkit security and governance priorities

1. Establish one named owner per system and one approved source for courses,
   fees, intakes, identities and retention.
2. Deploy MFA/step-up protection, authenticated mail/recovery and backup/restore
   drills before real learner or staff scale-up.
3. Keep private files outside public webroots and use separate demo/production
   secrets and databases.
4. Make external notifications opt-in, minimal and auditable; do not treat
   stored credentials as proof that WhatsApp or email delivery works.
5. Keep production deployments browser-free, demo-first, backed up and verified
   by actual route/asset/runtime evidence.
6. Do not use normal user deletion as a troubleshooting shortcut. Disable or
   remove an account only under an authorised, documented process.
7. Treat local/demo credentials as fixtures only; never reuse them in staging or
   production.

## 10. Decisions requested from management

The following decisions unblock the largest amount of work:

- approve a Virtual Campus hostname/host and LiveKit Cloud or self-hosted
  capacity;
- approve captions, recording, transcript handling and retention;
- provide the authoritative course/fee/intake/lecturer/roster data;
- approve mail, MFA, privacy, safeguarding and certificate policies;
- approve Reception host directory, WhatsApp template and notification fallback;
- provide production backup/restore ownership and acceptance windows;
- approve the patched Eduma parent package and website migration schedule;
- decide whether the Report System's proposed admissions/intake/minutes/incentive
  expansion is funded and owned;
- approve a governed chatbot CRM/Mzizi handoff and production hosting boundary.

## Briefing conclusion

The Toolkit directory contains a substantial, documented foundation rather than
one single application. Website and Reception operations are the current live
anchors. Smart Lecturer and the Virtual Campus now provide a credible local
teaching pilot, including tested multi-user video, but production activation is
correctly held behind explicit owner, infrastructure and policy gates. The next
step is to answer the trackers, complete the controlled gates, and promote only
the evidence-backed builds.
