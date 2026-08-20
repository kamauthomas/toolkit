# CLAUDE.md — Toolkit WordPress site

Public site `toolkitafrica.ac.ke` (staging: `demo.toolkitafrica.ac.ke`).
All work happens in the child theme `wp-content/themes/eduma-child/`.

**The workspace-wide guide is `../AGENTS.md`. Read it — it is binding.**
The points below are the ones most often needed in this repo.

---

## Report your work — every time

Append an entry to `WORKLOG.md` **in the same commit as the change**: what
changed, which environments it reached, how you verified it, what's left. Newest
entry at the top. See `../AGENTS.md` §1 for the format.

At the end of a session, hand over a ready-to-paste daily report draft for
`reports.toolkitafrica.ac.ke` (summary, tasks + time + status, challenges,
decisions, plan for tomorrow, ICT metrics). Offer it without being asked. Do not
file into the Report System using someone else's account.

---

## Hard rules

- **Never submit a test application on production.** Production relays to Mzizi
  the instant an application is made. Test on demo only.
- **Never type credentials** into a login form. Ask the human to sign in.
- **Deploy demo → verify → production.**
- **Never overwrite a whole file with the git version.** The live servers carry
  edits that were never committed. Deploy by replacing an exact, unique anchor
  block in the file you fetched from the server.

## Deploying

No CI. Use cPanel's authenticated Fileman API from a logged-in cPanel tab:
`get_file_content` → anchored replace → `save_file_content`. Confirm each anchor
occurs exactly once before saving.

Bump `toolkit_theme_release()` in `functions.php` when the page cache must be
purged. Verify afterwards by loading the live URL and checking the new release
string appears in the asset query strings, with no PHP errors.

## Layout of the theme

- `functions.php` — release constant, rewrite rules for virtual pages
  (`/graduation/`, `/testimonials/`, `/apply/`, …), SEO metadata, asset enqueues.
- `inc/application-adapter.php` — admissions: validation, encrypted local
  storage, Mzizi relay, delivery statuses, Applications admin screen.
- `inc/calling-letters.php`, `inc/support-hub.php`, `inc/site-metrics.php` —
  calling letters, enquiries/speak-up/chatbot, analytics + Toolkit Control.
- `template-parts/pages/*.php` — page templates mapped by rewrite rules.
- `page-redesign.css` — front-end styles. `assets/css/toolkit-admin.css` — admin.

Virtual pages are wired in three places in `functions.php`: the rewrite rule,
the template map, and the SEO metadata block. Change all three together.

## Admissions delivery statuses

`received` → `queued` → `relaying` → `delivered`, with failure states
`relay_failed` (Mzizi rejected it, e.g. HTTP 409 — **retryable**),
`validation_failed`, and `delivery_unconfirmed` (outcome genuinely unknown —
**must be checked in Mzizi before any retry**). The Applications screen shows a
"Re-send to Mzizi" action for retryable records only.

## Style

Match the surrounding code — this codebase favours comments that explain *why*.
Make the smallest change that fixes the real problem. The Toolkit Control admin
UI is intentionally plain: fix legibility issues, don't add flourish.
