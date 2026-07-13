# 07 — Deployment Safety

Applies to every `git` action and any action that touches a live/staging server. Read this before your first commit, not after.

## Branching

- [ ] Never commit directly to `main`/`master`/`production`. Create a branch first: `feature/hero-slider` (or the repo's existing naming convention if different — check recent branch names first).
- [ ] One branch for this entire task. Do not create multiple overlapping branches for the same work.

## Commits

- [ ] Commit after each numbered step in `03-BUILD-STEPS.md`, not one giant commit at the end. Small, reviewable, revertible commits.
- [ ] Commit message format: `hero-slider: <short description of this step>` (e.g. `hero-slider: add markup and slide data source`, `hero-slider: add crossfade + autoplay JS`, `hero-slider: enqueue assets in functions.php`).
- [ ] Never use `git commit -am` blindly — review the actual diff (`git diff --staged`) before committing to ensure no unintended files are included.
- [ ] Never `git add .` without checking `git status` first for stray/unrelated files.

## Before touching anything

- [ ] Confirm `git status` is clean at the very start (see Step 0 in file 03). If it isn't, do not proceed — that's pre-existing uncommitted work that isn't yours to overwrite or lose.
- [ ] Note the current commit hash (`git rev-parse HEAD`) in `PROGRESS.md` as the rollback point before your first change.

## Never do these

- [ ] Never force-push (`git push --force`) to any shared branch.
- [ ] Never run destructive WP-CLI or database commands (`wp db reset`, `wp site empty`, direct SQL deletes, etc.) — this task requires none of that.
- [ ] Never edit WordPress core files (`wp-admin/`, `wp-includes/`) or `wp-config.php`.
- [ ] Never disable/deactivate an unrelated plugin or theme "just to test" without reverting it immediately in the same session.
- [ ] Never merge your branch into `main`/production yourself. Open it for review (PR, or explicit handoff note in PROGRESS.md) and stop — merging/deploying to production requires explicit human go-ahead unless you have been explicitly told this specific task includes autonomous deploy authority.

## If you ARE authorized to deploy (explicit instruction only)

- [ ] Deploy to staging first if a staging environment exists. Run the full `06-VERIFICATION-CHECKLIST.md` there before touching production.
- [ ] Take/confirm a backup exists (files + database) immediately before production deploy. If you cannot confirm a backup mechanism exists, stop and flag it in PROGRESS.md rather than deploying blind.
- [ ] Deploy the smallest complete unit (the finished, verified feature branch), not partial work.
- [ ] Immediately after deploy: reload the live homepage, visually confirm the hero renders, check browser console for errors. This is a fast smoke test, not the full checklist, but do not skip it.
- [ ] Record the deployed commit hash and timestamp in `PROGRESS.md`.

## Rollback plan (write this down before you need it)

- Rollback = `git revert <commit-range>` on the branch, or redeploy the last-known-good commit hash recorded in PROGRESS.md before this task started.
- If a production issue appears after deploy, do not attempt a live hotfix under pressure — revert to the last-known-good hash first, stabilize, then re-diagnose calmly.

## End-of-task state

- [ ] Feature branch pushed, not merged (unless explicitly authorized to merge/deploy).
- [ ] `PROGRESS.md` updated with: final commit hash, branch name, verification results, and rollback hash.
- [ ] Working tree clean.
