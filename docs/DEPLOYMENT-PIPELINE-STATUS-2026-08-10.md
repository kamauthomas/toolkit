# Deployment pipeline review — 10 August 2026

## Current finding

The repository contains an approved design specification and a detailed
implementation plan for a stdlib-only Python deployment CLI. The implementation
has not started yet: there is no `scripts/toolkit_deploy/` package, test suite,
TOML configuration, local deployment state, or hosted CI workflow in this
branch. The current branch is therefore a documented implementation starting
point, not an operational CI/CD pipeline.

## Current verified deployment baseline

After the SEO recovery deployment on 10 August 2026, the first pipeline
bootstrap must seed both environments with:

```text
commit: 91cf32b
version: 2026.08.10.4
verified: true
```

This is the exact release verified on demo first and then production. The
immediately preceding live baseline was `dec1b6e` / `2026.08.07.2`; the earlier
`3635019` / `2026.08.04.21` documentation correction was inaccurate and must
not be used to initialize deployment state.

## Required implementation order

1. Add `.toolkit-deploy/` configuration/state handling and keep credentials
   outside Git.
2. Implement and test state/version logic, including the demo-before-production
   gate.
3. Implement cPanel backup/upload calls with Basic authentication and explicit
   failure handling.
4. Implement release-marker rewriting and bare-URL verification after cache
   purge.
5. Run the complete mocked test suite, then exercise `diff` and `deploy demo`
   against the live demo only.
6. Exercise rollback on demo before permitting production.
7. Update this status document and the master handoff with actual evidence;
   only then use the production command.

## Safety decisions retained

- Deletions abort a normal deploy; the tool never silently deletes remote files.
- `functions.php` is backed up and uploaded last on every non-empty deploy.
- State is written only after verification succeeds.
- Production requires demo at the same HEAD and verified.
- The Basic-auth credential is read from a gitignored local secrets file and is
  never copied into reports, rollback snapshots, or state.
- This is a manually invoked CLI pipeline in v1; hosted workflow/webhook
  automation remains explicitly out of scope until the local engine is proven.

## Review result

The design is coherent and the rename-detection correction is sound. The major
remaining gap is implementation plus isolated tests; no live deployment should
be described as pipeline-driven until those files and the demo exercise exist.

## Test and remote audit — 10 August 2026

- `python3 -m pytest scripts/toolkit_deploy -q` could not collect tests because
  `scripts/toolkit_deploy/` does not exist. This is a pipeline readiness failure,
  not a passing empty suite.
- No `.github/workflows/` pipeline exists in the repository.
- At the time of the pre-deployment remote audit, local `main` was 59 commits ahead of
  `origin/wordpress-modernisation` and zero commits behind it. The WordPress
  remote is therefore not up to date with the local consolidated source.
- `origin/main` belongs to a separate daily-report application lineage. It is
  eight commits ahead of its own merge base and must not be merged into or used
  as the WordPress deployment target.
- No push was performed during this audit.

## SEO recovery deployment — 10 August 2026

- Demo release `2026.08.10.4` passed all 176 sitemap URLs with HTTP 200 and
  zero measured title, description, canonical, H1, JSON-LD or duplicate-title
  defects. Demo retained `noindex,follow` and production canonicals.
- The identical accepted files were promoted to production only after the demo
  pass. Production release `2026.08.10.4` passed the same 176/176 audit and
  remained indexable.
- This was a manual cPanel deployment with fresh rollback snapshots, not a run
  of the proposed deployment CLI. The pipeline implementation remains pending.
