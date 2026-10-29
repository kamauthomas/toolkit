#!/usr/bin/env python3
"""Exercise the curated exporter against disposable repositories and sentinel files."""
import os
from pathlib import Path
import subprocess
import tempfile
import unittest

SCRIPT = Path(__file__).resolve().parents[1] / 'data-transfer/sync-toolkit-to-windows.sh'
PROJECTS = ['wordpress', 'report-system', 'reception-system', 'Chat-bot', 'web-redesign/toolkit-platform', 'SmartLecturer_VirtualCampus']


class WindowsWorkspaceSyncTest(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix='toolkit-sync-test-')
        self.addCleanup(self.temp.cleanup)
        self.base = Path(self.temp.name)
        self.root = self.base / 'toolkit'
        self.root.mkdir()
        self.documents = self.base / 'Documents'
        self.documents.mkdir()
        self.out = self.base / 'Toolkit_Workspace'
        for project in PROJECTS:
            directory = self.root / project
            directory.mkdir(parents=True)
            self.run_git(directory, 'init', '-q')
            self.write(directory / 'source.txt', 'committed source')
            self.write(directory / '.env', 'PRIVATE_FIXTURE=excluded')
            self.write(directory / '.env.example', 'APP_KEY=\n')
            self.write(directory / 'composer.lock', '{}')
            for name in ['storage/app/private/deployments/session.txt', 'vendor/generated.php', 'node_modules/cache.js', 'database.sqlite', 'wp-config.php']:
                self.write(directory / name, 'must not ship')
        campus = self.root / 'SmartLecturer_VirtualCampus/virtual-campus'
        self.write(campus / 'source.txt', 'committed campus')
        self.write(campus / '.env.example', 'APP_KEY=\n')
        self.write(campus / '.env', 'PRIVATE_FIXTURE=excluded')
        self.write(campus / 'storage/app/private/local-review/config.json', '{}')
        for name in ['wordpress/reports/public-report.md', 'wordpress/docs/WINDOWS-WORK-TRANSFER.md', 'report-system/REPORT-SYSTEM-QUICK-GUIDE.md', 'report-system/generated_reports/report.pdf', 'documents/plan.md', 'docs/reference.md', 'strategic-ict-roadmap/plan.md', 'imgs/poster.png', 'graphics/source.svg', 'prospectus/source.docx', 'web-redesign/research.md', 'redesign/notes.md', 'plugins/reference.php', 'sms/calling/Calling Letter 2026 UPDATED.docx', 'sms/calling/generate_calling_letters.py', 'SmartLecturer_VirtualCampus/docs/reporting/CAMPUS-REPORT.md', 'TOOLKIT_90_DAY_EXECUTION_PLAN.md', 'Poster Social Media 2025 Comms (1080 x 1080 px) (727 x 911 px).pdf']:
            self.write(self.root / name, 'approved fixture')
        self.write(self.root / 'wordpress/reports/report-preparation-2026-10-23/raw-evidence.json', 'private fixture')
        self.write(self.root / 'wordpress/reports/report-preparation-2026-10-23/ICT-WORK-REPORT.pdf', 'final report')
        self.write(self.root / 'docs/Calling Letters - 2026.zip', 'personal data fixture')
        self.write(self.documents / 'editable.xcf', 'editable artwork')
        for project in PROJECTS:
            self.commit(self.root / project)
        self.write(campus / 'source.txt', 'uncommitted campus edit')
        self.write(campus / 'untracked.txt', 'must not ship')
        self.env = os.environ.copy()
        self.env.update(TOOLKIT_SYNC_SOURCE_ROOT=str(self.root), TOOLKIT_SYNC_DOCUMENTS=str(self.documents))

    @staticmethod
    def write(path, data):
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(data)

    @staticmethod
    def run_git(directory, *args):
        subprocess.run(['git', '-C', str(directory), *args], check=True, capture_output=True)

    def commit(self, directory):
        self.run_git(directory, 'add', '-f', '.')
        self.run_git(directory, '-c', 'user.name=Fixture', '-c', 'user.email=fixture@example.invalid', 'commit', '-qm', 'fixture')

    def export(self, *args):
        return subprocess.run(['bash', str(SCRIPT), *map(str, args)], env=self.env, capture_output=True, text=True)

    def test_curated_committed_source_checksums_and_managed_deletion(self):
        self.write(self.out / 'unmanaged-note.txt', 'keep this')
        self.write(self.out / '01_Projects/stale.txt', 'remove stale managed file')
        result = self.export('--prepare-only', self.out)
        self.assertEqual(result.returncode, 0, result.stderr)
        campus = self.out / '01_Projects/Smart_Lecturer_Virtual_Campus'
        self.assertEqual((campus / 'source.txt').read_text(), 'committed campus')
        self.assertTrue((campus / '.env.example').exists())
        self.assertFalse((campus / 'untracked.txt').exists())
        self.assertTrue((self.out / '01_Projects/WordPress_Website/composer.lock').exists())
        for path in self.out.rglob('*'):
            if path.is_file():
                self.assertNotIn(path.name, ['.env', 'wp-config.php', 'database.sqlite', 'session.txt', 'raw-evidence.json', 'Calling Letters - 2026.zip', 'generated.php', 'cache.js'])
        self.assertTrue((self.out / '02_Reports_and_Planning/Website_Reports/Prepared_Management_Reports/ICT-WORK-REPORT.pdf').exists())
        self.assertTrue((self.out / '02_Reports_and_Planning/Campus_Progress_Reports/CAMPUS-REPORT.md').exists())
        self.assertFalse((self.out / '01_Projects/stale.txt').exists())
        self.assertEqual((self.out / 'unmanaged-note.txt').read_text(), 'keep this')
        self.assertIn('not synchronized to Windows', (self.out / '00_READ_ME/PREPARATION.tsv').read_text())
        self.assertIn('Windows synchronization performed: no', (self.out / '00_READ_ME/INVENTORY.txt').read_text())
        check = subprocess.run(['sha256sum', '--check', '--quiet', '00_READ_ME/FILES_SHA256.txt'], cwd=self.out, capture_output=True)
        self.assertEqual(check.returncode, 0, check.stderr)

    def test_secret_signature_stops_before_existing_destination_is_changed(self):
        self.write(self.out / '01_Projects/sentinel.txt', 'unchanged')
        marker = '-' * 5 + 'BEGIN PRIVATE' + ' KEY' + '-' * 5
        self.write(self.root / 'wordpress/leak.txt', marker)
        self.commit(self.root / 'wordpress')
        result = self.export('--prepare-only', self.out)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn('signature was found in staging', result.stderr)
        self.assertEqual((self.out / '01_Projects/sentinel.txt').read_text(), 'unchanged')
        self.assertFalse((self.out / '00_READ_ME').exists())

    def test_sync_refuses_a_non_windows_filesystem(self):
        result = self.export(self.out)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn('mounted read/write Windows volume', result.stderr)
        self.assertFalse(self.out.exists())

    def test_source_destination_and_managed_symlink_are_rejected(self):
        result = self.export('--prepare-only', self.root)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn('outside source folders', result.stderr)
        self.out.mkdir()
        unrelated = self.base / 'unrelated'
        unrelated.mkdir()
        (self.out / '01_Projects').symlink_to(unrelated, target_is_directory=True)
        result = self.export('--prepare-only', self.out)
        self.assertNotEqual(result.returncode, 0)
        self.assertIn('must not be symlinks', result.stderr)
        self.assertEqual(list(unrelated.iterdir()), [])


if __name__ == '__main__':
    unittest.main()
