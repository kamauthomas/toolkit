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

for name in ['resources/js/app.js', 'resources/css/app.css', 'database/factories/UserFactory.php']:
    assert not builder.allowed(Path(name)), name
for name in ['resources/views/layouts/app.blade.php', 'public/build/assets/app-reviewed.js', 'public/build/assets/app-reviewed.css']:
    assert builder.allowed(Path(name)), name


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

    source = root / 'wordpress'; theme = source / 'wp-content/themes/eduma-child'
    theme.mkdir(parents=True); (theme / 'functions.php').write_text('<?php // runtime fixture')
    (theme / 'LICENSE.md').write_text('Required license fixture')
    for name in ['README.md', 'report.docx', 'backup.zip']:
        (theme / name).write_text('Excluded fixture')
    (source / 'favicon.ico').write_bytes(b'\x00\x00\x01\x00favicon-fixture')
    (source / '.env').write_text('Excluded secret fixture')
    archive = root / 'wordpress.tgz'; builder.build('wordpress', source, archive)
    with tarfile.open(archive) as packaged:
        assert set(packaged.getnames()) == {'favicon.ico', 'wp-content/themes/eduma-child/functions.php', 'wp-content/themes/eduma-child/LICENSE.md'}
    (source / 'favicon.ico').unlink(); (root / 'outside.ico').write_bytes(b'private fixture')
    (source / 'favicon.ico').symlink_to(root / 'outside.ico')
    refused('wordpress', source, root / 'favicon-symlink.tgz')
print('PASS: document/secret/archive/map exclusion, required license retention, path/symlink boundaries and production-only Composer dependencies; no existing dependencies changed.')
