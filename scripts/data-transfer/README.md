# Toolkit Windows workspace synchronization

Use `sync-toolkit-to-windows.sh` to refresh the stable Windows Desktop working
copy after meaningful Toolkit changes:

```bash
./scripts/data-transfer/sync-toolkit-to-windows.sh
```

The default destination is:

`C:\Users\user\Desktop\Toolkit_Workspace`

The Windows volume must already be mounted read/write. The script does not open
a browser, request credentials, mount disks, or deploy a website.

## Safety boundaries

- Only named subfolders inside `Toolkit_Workspace` are mirrored with stale-file
  deletion. Unrelated Desktop folders and unmanaged files at the workspace root
  are left alone.
- Git projects are recreated from their current commit before mirroring.
- The untracked Virtual Campus source is included through explicit exclusions.
- Live environment files, databases, secrets, private storage, runtime
  dependencies/caches/logs, applicant spreadsheets, and generated calling
  letters are excluded.
- The run stops if a prohibited secret/database/applicant-output path appears.
- Source commits, synchronization history, inventory, and SHA-256 checksums are
  regenerated on every successful run.

The older `export-toolkit-to-windows.sh` filename remains as the implementation
entry point for compatibility. New automation should call
`sync-toolkit-to-windows.sh`.

Run the synchronization only after commits intended for the Windows project
snapshots are complete. This mirror supports workstation continuity; it does
not replace Git, production backups, or the separate demo-first deployment
scripts under `scripts/deployment/`.
