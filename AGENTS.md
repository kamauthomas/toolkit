# WordPress production changes

The parent `../AGENTS.md` continues to apply, including demo-first deployment,
private evidence, no production test applications, and a same-commit WORKLOG entry.

## Required flaw and security review

Before committing a change intended for production, and again before pushing or
deploying that commit, review the actual diff and deployment payload for flaws.
Record the review, tests and unresolved findings in the WORKLOG or release note.
Do not push/deploy with unresolved security blockers or a failed required check.
Recheck when the diff or fetched server baseline changes.

The review must cover the applicable risks: permissions and nonce/CSRF checks;
validation and output escaping; SQL and path handling; uploads and downloads;
secrets and personal data in responses/logs; outbound destinations; duplicate
processing, concurrency, retry limits, interrupted workers and failure handling.
Check the runtime file allowlist: no README/AGENTS, reports, development files,
source maps, backups or archives in public/application deployment payloads.
Preserve approved public downloads and required license notices.

Run meaningful local regression and negative tests for the affected boundaries.
Use synthetic data and mocked upstream submission responses for admissions;
never send a production test application or replay an uncertain Mzizi submission.
Inspect fetched live code for drift and verify demo before promotion. Record
what was tested, which environments were verified, and any residual limitations.

A security review does not require live attack probes. Use isolated tests for
malicious input. Keep live checks to the minimum normal requests needed for the
release. Stop on a WAF response, timeout or rate limit; do not rotate IPs to
continue probing. Coordinate any separately requested live negative testing.
