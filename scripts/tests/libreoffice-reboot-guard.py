#!/usr/bin/python3
"""Regression checks for previous-boot cleanup and live-lock preservation."""
import importlib.util
import os
from pathlib import Path
import pwd
import tempfile
import time
import unittest

spec = importlib.util.spec_from_file_location("guard", Path(__file__).parents[1] / "desktop/libreoffice-reboot-guard.py")
guard = importlib.util.module_from_spec(spec)
spec.loader.exec_module(guard)


class RebootGuardChecks(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.root = Path(self.temp.name)
        self.profile = self.root / "profile"
        self.profile.mkdir()
        self.lock = self.profile / ".lock"
        self.payload = ("[Lockdata]\nUser=" + pwd.getpwuid(os.getuid()).pw_name
                        + "\nHost=previous-boot-host\nIPCServer=true\n").encode()
        self.lock.write_bytes(self.payload)
        self.archive = self.root / "archive"
        # Model the next boot: both creation and write precede this boundary.
        self.next_boot = time.time() + 10

    def tearDown(self):
        self.temp.cleanup()

    def clean(self, **kwargs):
        options = dict(running=lambda: False, filesystem=lambda p: "btrfs")
        options.update(kwargs)
        return guard.clean_profile_lock(self.profile, self.archive, self.next_boot, **options)

    def test_previous_boot_other_hostname_is_archived_exactly(self):
        recovery = self.profile / "user/backup/recovered.odt"
        recovery.parent.mkdir(parents=True)
        recovery.write_bytes(b"unsaved-document-recovery")
        settings = self.profile / "user/registrymodifications.xcu"
        settings.write_bytes(b"existing-user-settings")
        self.assertEqual(self.clean(), "previous-boot-lock-archived")
        self.assertFalse(self.lock.exists())
        self.assertEqual(next(self.archive.iterdir()).read_bytes(), self.payload)
        self.assertEqual(recovery.read_bytes(), b"unsaved-document-recovery")
        self.assertEqual(settings.read_bytes(), b"existing-user-settings")

    def test_current_boot_old_hostname_is_preserved(self):
        self.next_boot = time.time() - 60
        self.assertEqual(self.clean(), "current-boot-lock-preserved")
        self.assertEqual(self.lock.read_bytes(), self.payload)

    def test_backdated_current_boot_lock_is_preserved(self):
        os.utime(self.lock, (1, 1))
        self.next_boot = time.time() - 60
        self.assertEqual(self.clean(), "current-boot-lock-preserved")

    def test_running_office_is_preserved(self):
        self.assertEqual(self.clean(running=lambda: True), "active-office-preserved")
        self.assertTrue(self.lock.exists())

    def test_office_starting_during_backup_is_preserved(self):
        calls = iter([False, True])
        self.assertEqual(self.clean(running=lambda: next(calls)), "changed-or-active-lock-preserved")
        self.assertTrue(self.lock.exists())

    def test_lock_changed_during_backup_is_preserved(self):
        calls = 0
        def running():
            nonlocal calls
            calls += 1
            if calls == 2:
                self.lock.write_bytes(b"new-instance-lock")
            return False
        self.assertEqual(self.clean(running=running), "changed-or-active-lock-preserved")
        self.assertEqual(self.lock.read_bytes(), b"new-instance-lock")

    def test_foreign_user_is_preserved(self):
        self.assertEqual(self.clean(username="another-user"), "unrecognized-lock-preserved")
        self.assertTrue(self.lock.exists())

    def test_network_profile_is_preserved(self):
        self.assertEqual(self.clean(filesystem=lambda p: "nfs"), "shared-or-unknown-filesystem-preserved")
        self.assertTrue(self.lock.exists())

    def test_unknown_mount_is_preserved(self):
        self.assertEqual(self.clean(filesystem=lambda p: "unknown"), "shared-or-unknown-filesystem-preserved")

    def test_symlink_is_preserved(self):
        target = self.root / "other-lock"
        self.lock.rename(target)
        self.lock.symlink_to(target)
        self.assertEqual(self.clean(), "untrusted-lock-preserved")
        self.assertEqual(target.read_bytes(), self.payload)

    def test_hardlink_is_preserved(self):
        os.link(self.lock, self.root / "other-lock")
        self.assertEqual(self.clean(), "untrusted-lock-preserved")

    def test_malformed_lock_is_preserved(self):
        self.lock.write_text("not a LibreOffice lock")
        self.assertEqual(self.clean(), "unrecognized-lock-preserved")

    def test_non_ipc_lock_is_preserved(self):
        self.lock.write_bytes(self.payload.replace(b"true", b"false"))
        self.assertEqual(self.clean(), "unrecognized-lock-preserved")

    def test_absent_lock_needs_no_cleanup(self):
        self.lock.unlink()
        self.assertEqual(self.clean(), "no-lock")
        self.assertFalse(self.archive.exists())

    def test_alternate_profile_not_touched(self):
        self.assertIsNone(guard.selected_profile(["-env:UserInstallation=file:///tmp/other"], self.root))


if __name__ == "__main__":
    unittest.main()
