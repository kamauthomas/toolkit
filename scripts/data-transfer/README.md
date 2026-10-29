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
- Virtual Campus now comes from its committed subtree; no working-tree runtime
  storage is copied. The old untracked-source behavior has been replaced.
- Live environment files, databases, secrets, private storage, runtime
  dependencies/caches/logs, applicant spreadsheets, and generated calling
  letters are excluded.
- The run stops if a prohibited secret/database/applicant-output path appears.
- All sources are filtered in private local staging before any Windows write.
  A read/write Windows mount and non-symlinked managed destination are required.
- Source commits, synchronization history, inventory, and SHA-256 checksums are
  regenerated on every successful run.

The older `export-toolkit-to-windows.sh` filename remains as the implementation
entry point for compatibility. New automation should call
`sync-toolkit-to-windows.sh`.

Run the synchronization only after commits intended for the Windows project
snapshots are complete. This mirror supports workstation continuity; it does
not replace Git, production backups, or the separate demo-first deployment
scripts under `scripts/deployment/`.

With the disk unavailable, use `--prepare-only OUTPUT_DIRECTORY` to build a
labelled, checksum-verified local handoff. Within the Toolkit tree, only
`toolkit-private-storage/windows-sync-preview/` is permitted as a preview
destination. This mode records preparation rather than a completed Windows sync.
