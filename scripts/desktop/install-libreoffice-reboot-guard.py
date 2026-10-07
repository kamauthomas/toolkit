#!/usr/bin/python3
"""Install the reboot guard for this user, preserving existing launch settings."""
from pathlib import Path
import hashlib
import json
import re
import shutil
import subprocess
import time

home = Path.home()
source = Path(__file__).with_name("libreoffice-reboot-guard.py")
guard = home / ".local/libexec/libreoffice-reboot-guard.py"
backup = home / ".local/state/libreoffice-reboot-guard/install-backups" / str(time.time_ns())
backup.mkdir(parents=True, mode=0o700)
changes = []


def install(path, content, mode=0o644):
    previous = None
    if path.exists():
        if path.is_symlink():
            raise RuntimeError("Refusing to replace symlink: " + str(path))
        previous = backup / path.relative_to(home)
        previous.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(path, previous)
    path.parent.mkdir(parents=True, exist_ok=True)
    temporary = path.with_name(path.name + ".reboot-guard.tmp")
    with temporary.open("x") as handle:
        handle.write(content)
    temporary.chmod(mode)
    temporary.replace(path)
    changes.append({"path": str(path), "backup": str(previous) if previous else None,
                    "sha256": hashlib.sha256(path.read_bytes()).hexdigest()})


install(guard, source.read_text(), 0o755)
writer = home / ".local/bin/libreoffice-writer-safe"
if writer.exists():
    old = writer.read_text()
    updated, count = re.subn(r"(?<= )libreoffice --writer", str(guard) + " --writer", old)
    if count == 0 and str(guard) + " --writer" in old:
        updated = old
    elif count != 1:
        raise RuntimeError("Unexpected Writer safe-launcher content; preserve it and review")
    install(writer, updated, 0o755)

for system in sorted(Path("/usr/share/applications").glob("libreoffice-*.desktop")):
    target = home / ".local/share/applications" / system.name
    old = target.read_text() if target.exists() else system.read_text()
    updated = re.sub(r"^Exec=libreoffice(?=\s|$)", "Exec=" + str(guard), old, flags=re.M)
    # Existing Writer GPU/GTK workaround still calls the guarded safe launcher.
    if system.name == "libreoffice-writer.desktop" and writer.exists():
        updated = re.sub(r"^Exec=" + re.escape(str(guard)) + r" --writer", "Exec=" + str(writer), updated, flags=re.M)
    install(target, updated)

install(home / ".config/autostart/libreoffice-reboot-guard.desktop", """[Desktop Entry]
Type=Application
Name=LibreOffice reboot lock check
Comment=Preserve recovery files and clear only a confirmed previous-boot local profile lock
Exec=""" + str(guard) + """ --cleanup-only
Terminal=false
NoDisplay=true
X-GNOME-Autostart-enabled=true
""")
subprocess.run(["update-desktop-database", str(home / ".local/share/applications")], check=True)
(backup / "manifest.json").write_text(json.dumps(changes, indent=2) + "\n")
print(json.dumps({"guard": str(guard), "install_backup": str(backup), "changes": changes}, indent=2))
