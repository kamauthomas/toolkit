# Database Seed Workflow

The shipped `bfyigiln_new.sql` is a content snapshot. It intentionally retains
environment-specific URLs and may have the parent Eduma theme active. After
importing it into an environment with this repository's files, run:

```bash
php scripts/seed-demo.php
```

The seed script is idempotent and only applies project-owned configuration:

- activates `eduma-child` while retaining `eduma` as its parent;
- sets the `Home` page as the static front page;
- forces the Home page to use the default template so the child `front-page.php`
  is selected;
- clears Elementor render caches for the Home page;
- assigns the verified Toolkit navigation menu to the `primary` location.

It does not change `home` or `siteurl`; set those per environment through
`wp-config.php`, WP-CLI, or the target environment's deployment configuration.

## Meta chatbot knowledge validation

Run this after changing current or scheduled admissions knowledge:

```bash
php scripts/validate-meta-chatbot-data.php
```

The validator checks the approved current fees and durations, confirms the September 2026 schedule remains quarantined, and prevents selected future catalog amounts from leaking into the current Meta training file.

Use `php scripts/seed-demo.php --dry-run` to inspect the actions first.

## Production readiness report

Generate the fresh 20 July 2026 audit milestone with:

```bash
python3 scripts/generate-production-readiness-report.py
```

The report records verified demo fixes separately from unresolved main-domain
release gates. Its responsive screenshots are captured audit evidence and are
not used as deployable site assets.

## Browser-free deployments

Guarded cPanel deployment scripts and their manual-review notes live in
[`scripts/deployment/`](deployment/README.md). They do not connect to an
interactive browser or store credentials in Git.
