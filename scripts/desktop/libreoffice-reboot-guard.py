#!/usr/bin/python3
"""Archive a local, previous-boot profile lock before starting LibreOffice.

Do not disable LibreOffice locking or recovery. Desktop launchers and a login
check share this guard; a second launch still forwards to the running office.
"""
import configparser
import fcntl
import json
import os
from pathlib import Path
import pwd
import stat
import subprocess
import sys
import time

LOCAL_FILESYSTEMS = {"btrfs", "ext2", "ext3", "ext4", "xfs", "f2fs", "tmpfs", "zfs"}


def filesystem_type(path):
    target = str(path.resolve())
    matches = []
    for line in Path("/proc/self/mountinfo").read_text().splitlines():
        left, right = line.split(" - ", 1)
        mount = left.split()[4].replace("\\040", " ").replace("\\134", "\\")
        if target == mount or target.startswith(mount.rstrip("/") + "/"):
            matches.append((len(mount), right.split()[0]))
    return max(matches)[1] if matches else "unknown"


def office_running():
    # An unreadable same-user process is uncertainty, not permission to remove
    # its lock. Inspect names only, never application arguments or environment.
    for process in Path("/proc").iterdir():
        if not process.name.isdigit():
            continue
        try:
            if process.stat().st_uid != os.getuid():
                continue
            if (process / "comm").read_text().strip() in {"soffice.bin", "oosplash"}:
                return True
        except (FileNotFoundError, ProcessLookupError):
            continue
    return False


def boot_time():
    for line in Path("/proc/stat").read_text().splitlines():
        if line.startswith("btime "):
            return int(line.split()[1])
    raise RuntimeError("Cannot determine this boot's start time")


def snapshot(path):
    info = path.lstat()
    return (info.st_dev, info.st_ino, info.st_size, info.st_mtime_ns, info.st_ctime_ns)


def clean_profile_lock(profile, archive, boot_epoch, running=office_running,
                       username=None, filesystem=filesystem_type):
    lock = profile / ".lock"
    if not lock.exists() and not lock.is_symlink():
        return "no-lock"
    info = lock.lstat()
    if (not stat.S_ISREG(info.st_mode) or info.st_uid != os.getuid()
            or info.st_nlink != 1):
        return "untrusted-lock-preserved"
    if filesystem(profile) not in LOCAL_FILESYSTEMS:
        return "shared-or-unknown-filesystem-preserved"
    # A hostname mismatch alone cannot establish that an instance is stale.
    # Previous-boot timestamps plus no local office process establish that
    # this *local* profile cannot still belong to a live previous-boot process.
    if info.st_mtime >= boot_epoch or info.st_ctime >= boot_epoch:
        return "current-boot-lock-preserved"
    if running():
        return "active-office-preserved"
    before = snapshot(lock)
    payload = lock.read_bytes()
    parsed = configparser.ConfigParser(interpolation=None)
    try:
        parsed.read_string(payload.decode("utf-8"))
        data = parsed["Lockdata"]
        owner = username or pwd.getpwuid(os.getuid()).pw_name
        if (data.get("User") != owner or not data.get("Host")
                or data.get("IPCServer", "").lower() != "true"):
            return "unrecognized-lock-preserved"
    except (UnicodeError, configparser.Error, KeyError):
        return "unrecognized-lock-preserved"
    archive.mkdir(parents=True, exist_ok=True, mode=0o700)
    saved = archive / (str(time.time_ns()) + ".profile.lock")
    with saved.open("xb") as handle:
        handle.write(payload)
        handle.flush()
        os.fsync(handle.fileno())
    saved.chmod(0o600)
    # Recheck after the durable backup, so a launch or changed lock is retained.
    if running() or snapshot(lock) != before or lock.read_bytes() != payload:
        return "changed-or-active-lock-preserved"
    lock.unlink()
    return "previous-boot-lock-archived"


def selected_profile(arguments, config):
    # Explicit alternate profiles keep their own locking and recovery policy.
    if any(arg.startswith("-env:UserInstallation=") for arg in arguments):
        return None
    return config / "libreoffice" / "4"


def main(arguments):
    cleanup_only = arguments == ["--cleanup-only"]
    config = Path(os.environ.get("XDG_CONFIG_HOME") or Path.home() / ".config")
    state = Path(os.environ.get("XDG_STATE_HOME") or Path.home() / ".local/state")
    state = state / "libreoffice-reboot-guard"
    profile = selected_profile(arguments, config)
    guard = None
    try:
        state.mkdir(parents=True, exist_ok=True, mode=0o700)
        guard = os.open(state / "launch.lock", os.O_CREAT | os.O_RDWR | os.O_NOFOLLOW, 0o600)
        fcntl.flock(guard, fcntl.LOCK_EX | fcntl.LOCK_NB)
        result = clean_profile_lock(profile, state / "archived-locks", boot_time()) if profile else "alternate-profile-preserved"
        (state / "last-check.json").write_text(json.dumps({
            "boot_id": Path("/proc/sys/kernel/random/boot_id").read_text().strip(),
            "checked_at": time.time(), "result": result,
        }, indent=2) + "\n")
        if cleanup_only:
            print(result)
    except BlockingIOError:
        # Another guarded launch is active. Forward without touching its lock.
        if cleanup_only:
            print("guarded-launch-active")
    except (OSError, RuntimeError) as error:
        # A failed check must never silently weaken LibreOffice's own safeguards.
        print("LibreOffice reboot check skipped: " + str(error), file=sys.stderr)
    if cleanup_only:
        return 0
    try:
        return subprocess.call(["/usr/bin/libreoffice", *arguments])
    finally:
        if guard is not None:
            os.close(guard)


if __name__ == "__main__":
    sys.exit(main(sys.argv[1:]))
