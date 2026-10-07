# LibreOffice reboot guard

This machine's `anonctl-boot-recover` rotates its hostname at boot. A profile lock
left by an interrupted LibreOffice session then names the preceding hostname and
blocks startup. The guard handles that previous-boot lock without disabling
LibreOffice's lock checks or recovery and without changing hostname rotation.

Run `python3 scripts/tests/libreoffice-reboot-guard.py` to check cleanup and
preservation cases. Install for the signed-in user with
`python3 scripts/desktop/install-libreoffice-reboot-guard.py`.

The installer backs up existing launchers under
`~/.local/state/libreoffice-reboot-guard/install-backups/`, installs the guard
under `~/.local/libexec/`, and connects all installed LibreOffice desktop entries
and their actions to it. Writer's existing GTK/GPU workaround is preserved.
A desktop autostart entry checks once on login, covering normal command-line
starts after login too. Explicit `/usr/bin/libreoffice` calls before that login
check bypass the guard; use the guard path when starting outside the desktop.

Only a regular, single-link, same-user lock in a known local filesystem is
eligible. Both modification and change times must predate the current kernel
boot. A same-user office process, a current-boot lock, an alternate explicit
profile, an unknown/network filesystem, or an unrecognized lock is preserved.
Checks that fail retain LibreOffice's original safeguards. Concurrent guarded
launches forward to the running application without cleaning its lock.

Before removal, the exact lock bytes are saved privately under
`~/.local/state/libreoffice-reboot-guard/archived-locks/`; the lock and process
state are rechecked. No document lock, settings file or recovery file is removed.
The latest check is recorded in `last-check.json`. The guard is a per-user desktop
fix, not a change to the distribution's LibreOffice packages or network services.

To revert, close LibreOffice normally, remove the autostart entry and guard, and
restore the launch files listed in the installation backup's `manifest.json`.
Entries with no original backup can be removed to use system defaults. Refresh
the user desktop database with `update-desktop-database ~/.local/share/applications`.
