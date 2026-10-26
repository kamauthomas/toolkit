#!/usr/bin/env python3
"""Exercise package boundaries using disposable files; no deployment or dependency edits."""
import importlib.util
import json
import tarfile
import tempfile
from pathlib import Path

script = Path(__file__).resolve().parents[1] / 'deployment/build-runtime-package.py'
spec = importlib.util.spec_from_file_location('builder', script)
builder = importlib.util.module_from_spec(spec); spec.loader.exec_module(builder)


def refused(profile, source, destination):
    try: builder.build(profile, source, destination)
    except ValueError: pass
    else: raise AssertionError('Unsafe package accepted')
    assert not destination.exists()


with tempfile.TemporaryDirectory(prefix='toolkit-package-policy-') as directory:
    root = Path(directory); source = root / 'reporting'; source.mkdir()
    (source / 'app.py').write_text('# runtime fixture')
    templates = source / 'templates'; templates.mkdir()
    for name in ['home.html', 'LICENSE.md', 'README.md', 'report.pdf', '.env', 'source.js.map', 'backup.zip', 'database.sql']:
        (templates / name).write_text('fixture')
    archive = root / 'reporting.tgz'; builder.build('reporting', source, archive)
    with tarfile.open(archive) as packaged:
        assert set(packaged.getnames()) == {'app.py', 'templates/home.html', 'templates/LICENSE.md'}
    (root / 'outside.py').write_text('# private fixture')
    (source / 'app.py').unlink(); (source / 'app.py').symlink_to(root / 'outside.py')
    refused('reporting', source, root / 'symlink.tgz')
    (source / 'app.py').unlink(); (source / 'app.py').write_text('# runtime fixture')
    refused('reporting', source, source / 'recursive.tgz')
    templates.rename(source / 'real-templates'); templates.symlink_to(source / 'real-templates', target_is_directory=True)
    refused('reporting', source, root / 'root-symlink.tgz')

    source = root / 'reception'; metadata = source / 'vendor/composer/installed.json'
    metadata.parent.mkdir(parents=True); (source / 'artisan').write_text('<?php // fixture')
    for contents in [{'dev': True, 'dev-package-names': ['mockery/mockery']}, {'dev': False, 'dev-package-names': ['phpunit/phpunit']}, {'dev': False}, {}, []]:
        metadata.write_text(json.dumps(contents)); refused('reception', source, root / 'dev.tgz')
    metadata.write_text(json.dumps({'dev': False, 'dev-package-names': [], 'packages': []}))
    builder.build('reception', source, root / 'reception.tgz')
    metadata.unlink(); metadata.symlink_to(root / 'external.json'); (root / 'external.json').write_text('{"dev":false,"dev-package-names":[]}')
    refused('reception', source, root / 'metadata-symlink.tgz')
print('PASS: document/secret/archive/map exclusion, required license retention, path/symlink boundaries and production-only Composer dependencies; no existing dependencies changed.')
