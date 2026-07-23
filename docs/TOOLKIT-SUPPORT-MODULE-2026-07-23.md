# Toolkit Website Support Module

**Released:** 23 July 2026  
**Environments:** Demo and main domain  
**Administration:** WordPress Dashboard > Toolkit Control

## Delivered modules

- **Enquiry inbox:** Visitors can submit an enquiry from any redesigned page. Toolkit administrators can view contact details, source page, message, date, and progress status.
- **Website improvement poll:** Visitors rank the redesigned website from 1 to 5, identify improved areas (design, navigation, content, speed, and mobile), and optionally suggest the next improvement.
- **Poll reporting:** Toolkit Control reports response count, average rating, rating distribution, and improvement-area mentions.
- **Chatbot controls:** Administrators can enable or disable the assistant and poll, and update the greeting, course, fee, application, contact, poll question, and poll guidance text.
- **Support overview:** Toolkit Control displays new-enquiry count, poll-response count, assistant status, and direct links to each operational screen.
- **Faster interaction:** Public chatbot configuration is embedded in the page payload, removing the first-open configuration round trip. Enquiry email notification is scheduled after storage instead of delaying the visitor response.
- **Faster administration:** Toolkit Control now opens as a lightweight operational overview. Thirty-day metric aggregation moved to a dedicated Site analytics screen.
- **Administration layout:** The overview uses responsive status cards, clear module links, release controls, and system state based on the approved Toolkit brand palette.

## Security and privacy

- Enquiries and poll responses are private WordPress records and are not exposed through the public WordPress REST post endpoints.
- Submission endpoints enforce same-site Origin or Referer validation, per-IP rate limiting, strict field limits, sanitization, and a hidden honeypot.
- Enquiries require explicit response consent and either an email address or phone number.
- Administrator pages and actions require `manage_options`; configuration and status updates use WordPress nonces.
- No raw IP address, chatbot transcript, advertising identifier, or cross-site tracking profile is stored.

## Validation record

- PHP syntax: passed for the support module, Toolkit Control module, and footer.
- JavaScript syntax: passed for the support client and homepage experience.
- Demo configuration endpoint: passed.
- Demo invalid-origin rejection: HTTP 403, passed.
- Demo enquiry persistence: HTTP 201, passed.
- Demo poll persistence: HTTP 201, passed after correcting the internal post-type key to comply with WordPress's 20-character limit.
- Production deployed-file comparison: five dependency and interface files matched exactly; the public stylesheet contains the released support controls.
- Production configuration endpoint: passed with four managed information topics and active poll configuration.
- Production page coverage: assistant markup confirmed on the homepage and Contact page.
- Production invalid-origin rejection: HTTP 403, passed.
- Production browser interaction: panel, six actions, enquiry fields, five ratings, five improvement areas, and poll comment all passed.
- Production desktop and mobile screenshots are stored under ignored rollback evidence at `rollbacks/production-20260723-support/screenshots/`.
- HSTS, nosniff, frame protection, and LiteSpeed cache response remained present after deployment.

## Application layer status

The local release candidate contains a separate six-step application experience and Mzizi adapter. It has not been included in this support-module deployment.

- Implemented locally: branded six-step form, campus/course/intake option loading, field allow-list, same-origin request guard, rate limiting, honeypot, server-side course/intake revalidation, Turnstile verification hooks, and explicit Mzizi feature flag.
- Safe current behavior: while direct submission is disabled, the form opens the official Mzizi portal and states that locally entered data was not transmitted.
- Still required before production activation: approved Mzizi submission authorization, environment-managed Turnstile keys, endpoint contract validation against a non-production applicant, complete desktop/mobile accessibility testing, failure-path testing, privacy approval, and monitored rollback.

## Rollback

The pre-release production copies and checksums are stored under ignored `rollbacks/production-20260723-support/`. Restore those files and remove `inc/support-hub.php` plus `assets/js/toolkit-support.js`, then purge LiteSpeed. No database rollback is required; private support records can remain dormant.
