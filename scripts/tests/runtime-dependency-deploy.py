#!/usr/bin/env python3
"""Exercise dependency rollback/maintenance guards in disposable shell fixtures."""
import importlib.util
import subprocess
import tempfile
from pathlib import Path

script = Path(__file__).resolve().parents[1] / 'deployment/releases/deploy-runtime-dependencies-2026.10.26.py'
spec = importlib.util.spec_from_file_location('deploy', script)
deploy = importlib.util.module_from_spec(spec); spec.loader.exec_module(deploy)
original = b"<?php if(file_exists($m=__DIR__.'/../storage/framework/maintenance.php'))require $m;require __DIR__.'/../reception-system-demo/vendor/autoload.php';"
fixed = deploy.repair_index(original, 'reception-system-demo')
assert b"__DIR__.'/../reception-system-demo/storage/framework/maintenance.php'" in fixed
assert fixed.replace(b'/../reception-system-demo/storage/', b'/../storage/') == original
assert deploy.repair_index(fixed, 'reception-system-demo') == fixed
for unsafe in [original.replace(b'maintenance.php', b'unknown.php'), original + original]:
    try: deploy.repair_index(unsafe, 'reception-system-demo')
    except AssertionError: pass
    else: raise AssertionError('Ambiguous index patch accepted')

with tempfile.TemporaryDirectory(prefix='toolkit-dependency-rollback-') as directory:
    root = Path(directory)
    for mode in ['success', 'composer_failure', 'no_composer', 'existing_backup', 'bad_checksum', 'fileinfo_failure']:
        case = root / mode; app = case / 'application'; cache = app / 'bootstrap/cache'; vendor = app / 'vendor'
        cache.mkdir(parents=True); vendor.mkdir(); (app / 'artisan').touch()
        (vendor / 'runtime.php').write_text('runtime'); (vendor / 'developer.php').write_text('developer')
        for name in ['packages.php', 'services.php', 'config.php']: (cache / name).write_text('old cache')
        composer = case / 'composer.phar'
        if mode != 'no_composer': composer.touch()
        php = case / 'fake-php'; php.write_text('''#!/bin/sh
if test "$1" = "-r"; then
  if test "''' + mode + '''" = fileinfo_failure; then exit 45; fi
  exit 0
fi
if test "$1" = "-d"; then
  test "$COMPOSER_DISABLE_NETWORK" = 1 || exit 89
  rm -f "$PWD/vendor/developer.php"
  if test "''' + mode + '''" = composer_failure; then exit 7; fi
  exit 0
fi
case "$2" in
  down) touch "$(dirname "$1")/maintenance-on" ;;
  up) rm -f "$(dirname "$1")/maintenance-on" ;;
  package:discover|about) exit 0 ;;
  *) exit 88 ;;
esac
'''); php.chmod(0o700)
        backup = case / 'rollback'
        if mode == 'existing_backup': backup.mkdir(); (backup / 'sentinel').write_text('preserve')
        body = deploy.maintenance_script(str(app), str(backup), str(php), (str(composer),), tool_hash='0' * 64 if mode == 'bad_checksum' else None)
        assert '\n' not in body and '%' not in body
        subprocess.run(['/bin/sh', '-n'], input=body.encode(), check=True)
        result = subprocess.run(['/bin/sh', '-c', body], capture_output=True)
        assert not (app / 'maintenance-on').exists()
        assert (vendor / 'runtime.php').read_text() == 'runtime'
        if mode == 'success':
            assert result.returncode == 0 and b'RUNTIME_DEPENDENCIES_READY' in result.stdout
            assert not (vendor / 'developer.php').exists() and (backup / 'vendor/developer.php').exists()
            assert not (cache / 'config.php').exists()
        else:
            assert result.returncode != 0 and (vendor / 'developer.php').exists()
            assert (cache / 'config.php').read_text() == 'old cache'
        if mode == 'composer_failure': assert b'PRIVATE_VENDOR_ROLLBACK_ATTEMPTED' in result.stdout
        if mode == 'existing_backup': assert (backup / 'sentinel').read_text() == 'preserve'
    for status in [0, 7]:
        tool = root / ('temporary-tool-' + str(status)); tool.write_text('tool fixture')
        marker = root / ('marker-' + str(status)); log = root / ('worker-' + str(status) + '.log')
        command = deploy.worker_command('exit ' + str(status), str(marker), str(log), str(tool))
        subprocess.run(['/bin/sh', '-c', command], check=True)
        saved = log.read_bytes()
        assert not tool.exists() and ('TOOLKIT_DEPENDENCY_WORKER_EXIT=' + str(status)).encode() in saved
        tool.write_text('must not replay'); subprocess.run(['/bin/sh', '-c', command], check=True)
        assert tool.exists() and log.read_bytes() == saved
print('PASS: exact maintenance-path repair; success, failed Composer rollback, unavailable Composer and existing-backup guards; maintenance removed and runtime data preserved in isolated fixtures.')
