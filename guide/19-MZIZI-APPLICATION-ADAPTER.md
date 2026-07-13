# Mzizi Application Adapter

## Objective

Applicants complete a modern Toolkit form at `toolkitafrica.ac.ke`. WordPress validates the application and submits it to Mzizi without exposing the Mzizi interface or its browser session to the applicant.

## Confirmed Mzizi contract

The current application page is an Angular application. Its public JSON service becomes available after opening the tenant-specific application URL and establishing its short-lived session.

- `StudentInfo.asmx/GetSchools` returns Toolkit campuses.
- The selected campus is sent to `StudentInfo.asmx/SetApplicationSchoolIDParam`.
- `StudentInfo.asmx/GetApplicationCoursesNoAlumni` returns that campus's courses.
- `OrganizationProcesses.asmx/GetCourseIntakeMonths` returns valid intakes for a selected course.
- `StudentInfo.asmx/SubmitOnlineApplication` receives the final application payload.

The form currently requires first name, surname, email, primary and secondary phone numbers, campus, course, and intake. It also accepts county, gender, qualification and study details where applicable.

## Architecture

1. Add a dedicated child-theme integration module with REST endpoints under `toolkit/v1/application`.
2. Each request initializes a fresh Mzizi tenant session server-side, retaining cookies only for the duration of that request.
3. The public form calls only Toolkit REST endpoints. Mzizi URLs, cookies, and response internals never reach the browser.
4. The Toolkit endpoint validates and normalizes input, applies rate limiting, verifies a human challenge, and forwards an allow-listed payload to Mzizi.
5. The submit endpoint returns only a neutral success or recoverable validation response. It must not store applicant data, except short-lived technical logs with personal data removed.
6. The front end is a multi-step, accessible form with progressive disclosure. It loads campus, course, and intake options from the adapter and keeps users on the Toolkit domain throughout.

## Required safeguards

- Confirm with Mzizi that the public service contract is approved for this integration and obtain a supported API credential if one is available.
- Add a privacy notice and explicit consent immediately before submission.
- Use WordPress nonces, server-side validation, honeypot/rate limits, and a verified CAPTCHA token.
- Keep request timeouts short, redact phone numbers and email addresses from logs, and send failures to an admissions support route rather than retrying blindly.
- Treat the Mzizi API response as untrusted and map only known success and validation states.

## Rollout

1. Build the adapter and test it against a non-production Mzizi tenant using test applications.
2. Build the form and exercise every field mapping, including empty optional fields and unavailable intakes.
3. Run admissions acceptance testing and confirm created records inside Mzizi.
4. Enable production submission behind a feature flag, monitor redacted failures, then retire the external-form handoff.

No Mzizi credentials, applicant records, or endpoint cookies are stored in this repository.
