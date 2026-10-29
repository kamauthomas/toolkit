#!/usr/bin/env python3
"""Verify that the Campus delta cannot contain reports, helpers or private files."""
import importlib.util
import json
from pathlib import Path
import tempfile
import unittest

path = Path(__file__).resolve().parents[1] / 'deployment/check-campus-runtime-delta.py'
spec = importlib.util.spec_from_file_location('campus_delta', path)
policy = importlib.util.module_from_spec(spec)
spec.loader.exec_module(policy)


class CampusRuntimeDeltaTest(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix='campus-delta-policy-')
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name) / 'payload'
        for name in policy.RUNTIME_FILES:
            file = self.root / name
            file.parent.mkdir(parents=True, exist_ok=True)
            file.write_text('runtime fixture')
        (self.root / 'public/build/manifest.json').write_text(json.dumps({'resources/js/app.js': {'file': 'assets/app-reviewed.js'}}))

    def test_only_complete_reviewed_runtime_paths_are_allowed(self):
        self.assertEqual(len(policy.inspect(self.root)), len(policy.RUNTIME_FILES))
        (self.root / 'routes/web.php').unlink()
        with self.assertRaisesRegex(ValueError, 'Incomplete'):
            policy.inspect(self.root)

    def test_development_documents_unused_scripts_and_secrets_are_rejected(self):
        for name in ['README.md', 'public/notes.MD', 'public/summary.txt', 'reports/report.pdf', 'docs/report.docx', 'scripts/helper.php', 'tests/Feature/Test.php', 'resources/js/app.js', 'resources/css/app.css', 'public/build/assets/app.js.map', '.env', 'public/build/assets/README.txt']:
            with self.subTest(name=name):
                file = self.root / name
                file.parent.mkdir(parents=True, exist_ok=True)
                file.write_text('excluded fixture')
                with self.assertRaisesRegex(ValueError, 'unexpected payload'):
                    policy.inspect(self.root)
                file.unlink()

    def test_manifest_traversal_and_symlinks_are_rejected(self):
        manifest = self.root / 'public/build/manifest.json'
        manifest.write_text(json.dumps({'entry': {'file': 'assets/../../private.txt'}}))
        with self.assertRaisesRegex(ValueError, 'Unsafe'):
            policy.inspect(self.root)
        manifest.write_text('{}')
        file = self.root / 'routes/web.php'
        file.unlink()
        file.symlink_to(Path(self.temp.name) / 'outside')
        with self.assertRaisesRegex(ValueError, 'symlink'):
            policy.inspect(self.root)


if __name__ == '__main__':
    unittest.main()
