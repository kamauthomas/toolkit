# Wingu Authenticated Discovery

**Status:** Tooling verified; one human-authenticated Wingu session required

This is the only approved way to inspect Wingu's current timesheet fields before
the external dispatcher is implemented. It does not attach to the normal Brave
profile, copy cookies, accept a password, read field values, click controls or
submit a timesheet.

## When the owner is ready

From the Report System repository:

```bash
scripts/wingu-session.sh start 'https://THE-APPROVED-WINGU-URL'
```

A separate temporary Brave app window opens. The employee signs in personally
and opens **ESS → Time Sheet → Edit Time Sheet**. Then run:

```bash
scripts/wingu-session.sh discover
```

Discovery writes `/tmp/toolkit-wingu-field-discovery.json`. It contains only:

- page/form paths without query strings;
- field names, IDs, labels, types and required/disabled states;
- select names/counts, with option text/value only for the project selector;
- button names and labels.

It deliberately does not read input values, cookies, local/session storage,
network responses, screenshots or credentials. Output outside `/tmp` is refused.

After the field map has been reviewed:

```bash
scripts/wingu-session.sh stop
```

Stop validates that the recorded process owns the exact temporary profile, then
removes that process group, profile and discovery JSON. It never acts on the
normal browser profile.

## Verified local fixture

The discovery engine was exercised against a temporary headless Brave page with
dummy start-time, notes and password values. It reported form structure, field
names, select options and the submit label; none of the dummy values appeared in
the discovery output. The temporary browser profile and JSON were deleted after
the check.

## Boundary after discovery

Discovery does not authorise submission. The resulting field map will be used
to implement a dry-run payload preview first. Live submission will remain
explicit, approval-gated, limited to `ready` queue rows, followed by a server
reload and persistence comparison before a row is marked `accepted`.
