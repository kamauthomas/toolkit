#!/usr/bin/env python3
"""Exercise release config safety and shell failure traps in disposable local fixtures."""
import importlib.util
import json
import subprocess
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
source = (ROOT / 'scripts/deployment/audit/calling-letter-env-switch.php').read_text()[5:]
path = ROOT / 'scripts/deployment/releases/deploy-calling-letters-2026.10.26.py'
spec = importlib.util.spec_from_file_location('release', path)
release = importlib.util.module_from_spec(spec); spec.loader.exec_module(release)
checks = 0
with tempfile.TemporaryDirectory(prefix='toolkit-letter-release-fixtures-') as temporary:
    base = Path(temporary)
    for mode, flag in [('absent',''),('false','CALLING_LETTERS_ENABLED=false\n'),('exported',' \texport CALLING_LETTERS_ENABLED = false\n'),('duplicate','CALLING_LETTERS_ENABLED=false\nCALLING_LETTERS_ENABLED=true\n'),('symlink',''),('writable',''),('backup_exists','')]:
        case = base / mode; app = case / 'reception-system-demo'; app.mkdir(parents=True)
        (app / 'bootstrap/cache').mkdir(parents=True)
        env = app / '.env'; original = 'APP_KEY=synthetic-only\nDB_DATABASE=synthetic_fixture\n' + flag
        env.write_text(original); env.chmod(0o644)
        backup = case / ('toolkit-private-storage/calling-letters-release-demo-' + 'a'*32)
        backup.parent.mkdir()
        if mode == 'symlink':
            target = case / 'other.env'; env.rename(target); env.symlink_to(target)
        if mode == 'writable': env.chmod(0o666)
        if mode == 'backup_exists': backup.mkdir()
        fixture = source.replace('/home/bfyigiln', str(case))
        result = subprocess.run(['php','-r',fixture,'--',str(app),str(backup),'enable'], capture_output=True)
        if mode in {'duplicate','symlink','writable','backup_exists'}:
            assert result.returncode != 0 and env.read_text() == original
            assert not list(app.glob('.env-letter-*'))
        else:
            assert result.returncode == 0 and 'CALLING_LETTERS_ENABLED=true' in env.read_text()
            assert env.read_text().startswith('APP_KEY=synthetic-only\nDB_DATABASE=synthetic_fixture\n')
            assert (backup / 'env.before').read_text() == original
            assert backup.stat().st_mode & 0o777 == 0o700 and (backup / 'env.before').stat().st_mode & 0o777 == 0o600
            assert env.stat().st_mode & 0o777 == 0o644
            result = subprocess.run(['php','-r',fixture,'--',str(app),str(backup),'disable'], capture_output=True)
            assert result.returncode == 0 and 'CALLING_LETTERS_ENABLED=false' in env.read_text()
        checks += 1
    for mode in ['success','migration_failure','acceptance_failure']:
        case = base / mode; case.mkdir(); fake = case / 'php'
        fake.write_text('''#!/bin/sh
if test "$1" = -d; then shift 2; fi
if test "$1" = -r; then
 code=$2
 case "$code" in
  *configuration_switch*) for value in "$@"; do last=$value; done; echo ENV_FLAG_$last ;;
  *releaseCheck*) if test "''' + mode + '''" = acceptance_failure; then echo ACCEPTANCE_FAILED; exit 7; fi; echo ACCEPTANCE_PASSED ;;
  *WORDPRESS_LETTERS_DISABLED*) echo WORDPRESS_DISABLED ;;
  *) echo WORDPRESS_ENABLED ;;
 esac
else
 if test "$2" = migrate && test "''' + mode + '''" = migration_failure; then exit 6; fi
fi
'''); fake.chmod(0o700)
        body = release.worker_body('demo','/home/bfyigiln/reception-system-demo','/home/bfyigiln/demo.toolkitafrica.ac.ke','/home/bfyigiln/toolkit-private-storage/calling-letters-release-demo-'+'a'*32,'/tmp/synthetic-release.log')
        body = body.replace('/usr/local/bin/ea-php84', str(fake)).replace('/usr/local/bin/ea-php83', str(fake))
        subprocess.run(['/bin/sh','-n'], input=body.encode(), check=True)
        result = subprocess.run(['/bin/sh','-c',body], capture_output=True)
        if mode == 'success': assert result.returncode == 0 and b'CALLING_LETTERS_READY' in result.stdout and b'ENV_FLAG_disable' not in result.stdout
        else: assert result.returncode != 0 and b'ENV_FLAG_disable' in result.stdout and b'WORDPRESS_DISABLED' in result.stdout and b'CALLING_LETTERS_READY' not in result.stdout
        checks += 1
print('PASS:', checks, 'config preservation/private backup/unsafe-state and worker success/failure rollback scenarios; no hosting requests.')
