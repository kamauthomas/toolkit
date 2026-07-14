# INDEX — Hero Slider Build Docs

You are an autonomous coding agent modifying an existing WordPress theme to build a hero slider section. This folder is your entire instruction set. Do not seek instructions outside these files unless truly blocked.

## Read order (do not skip, do not reorder)

1. `01-PROJECT-GOAL.md` — what "done" means. Read first, re-read if confused.
2. `05-RESEARCH-FILE-RETRIEVAL.md` — how to find the right files BEFORE editing anything.
3. `02-HERO-SPEC.md` — the exact visual/functional spec to build.
4. `03-BUILD-STEPS.md` — the ordered implementation checklist.
5. `04-ANIMATION-BEST-PRACTICES.md` — read before writing any CSS/JS transitions.
6. `06-VERIFICATION-CHECKLIST.md` — run after every build step, and again at the end.
7. `07-DEPLOYMENT-SAFETY.md` — read before any `git commit`, `git push`, or deploy action.
21. `21-COURSE-CATALOG-CUTOVER-AND-ASSISTANT.md` — authoritative 2026 catalog, legacy-page disposition, main-domain rollback, and registration-assistant boundaries.
22. `22-MAIN-DOMAIN-CUTOVER-INVENTORY.md` — fresh production URL inventory, instant switches, rollout gates, and rollback order.
8. `08-PROGRESS-LOG-TEMPLATE.md` — copy this to `PROGRESS.md` in the repo root on step 1 and update it continuously.

## Hard rules (apply across all files)

- Never touch files unrelated to the hero section. If a file's purpose is unclear, read `05-RESEARCH-FILE-RETRIEVAL.md` before opening it.
- Never invent content. All copy, colors, and layout come from `02-HERO-SPEC.md`, which was reverse-engineered from the approved reference image.
- After every meaningful change, update `PROGRESS.md`. If you stop and resume later (new session), read `PROGRESS.md` FIRST, before any other file.
- If a checklist item in `06-VERIFICATION-CHECKLIST.md` fails, do not proceed to the next build step. Fix it first.
- If a git or deploy action is required, `07-DEPLOYMENT-SAFETY.md` overrides convenience. No shortcuts.
- If you are unsure whether something is in scope, it is out of scope. Log the question in `PROGRESS.md` under "Open Questions" and continue with what is in scope.

## Definition of Done (summary — full detail in file 01)

- [ ] Hero section renders and visually matches `02-HERO-SPEC.md`
- [ ] Image slides AND text slides transition together, in sync, per slide
- [ ] All items in `06-VERIFICATION-CHECKLIST.md` pass
- [ ] Working tree is clean (`git status` shows nothing uncommitted, no stray debug files)
- [ ] `PROGRESS.md` shows status = DONE with final verification results logged
- [ ] No performance regression per the checks in file 06
