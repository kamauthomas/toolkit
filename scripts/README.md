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

Use `php scripts/seed-demo.php --dry-run` to inspect the actions first.
