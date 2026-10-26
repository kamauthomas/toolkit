#!/usr/bin/env python3
"""Test --no-dev dependency removal in isolated copies, with Composer networking disabled."""
import base64
import hashlib
import json
import os
import shutil
import subprocess
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/runtime-minimum-audit-2026-10-26'
SOURCES = {'reception': ROOT.parent / 'reception-system',
           'campus': ROOT.parent / 'SmartLecturer_VirtualCampus/virtual-campus'}
REMOTE = {'reception': 'reception-system', 'campus': 'virtual-campus-test'}


def versions(data): return {(p['name'], p['version']) for p in data['packages']}
def sha(data): return hashlib.sha256(data).hexdigest()


def run():
    out = CASE / 'dependency-preparation'; out.mkdir(mode=0o700, exist_ok=True)
    result = {}
    with tempfile.TemporaryDirectory(prefix='toolkit-runtime-deps-', dir='/tmp') as temp:
        temp = Path(temp)
        for kind, source in SOURCES.items():
            before = json.loads((source / 'vendor/composer/installed.json').read_text())
            remote = json.loads((CASE / 'final-runtime-checks' / (REMOTE[kind] + '-installed.private.json')).read_text())
            assert versions(before) == versions(remote), 'Local/server dependency drift; review before preparing'
            stage = temp / kind; stage.mkdir()
            for name in ['vendor', 'app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'public', 'lang', 'artisan', 'composer.json', 'composer.lock']:
                entry = source / name
                if not entry.exists(): continue
                if entry.is_dir(): shutil.copytree(entry, stage / name, symlinks=True)
                else: shutil.copy2(entry, stage / name)
            shutil.rmtree(stage / 'bootstrap/cache', ignore_errors=True)
            for name in ['bootstrap/cache', 'storage/app/private', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs']:
                (stage / name).mkdir(parents=True, exist_ok=True)
            database = stage / 'synthetic.sqlite'; database.touch()
            env = dict(os.environ, COMPOSER_DISABLE_NETWORK='1', COMPOSER_AUTH='{}', COMPOSER_HOME=str(temp / 'composer-home'), COMPOSER_CACHE_DIR=str(temp / 'composer-cache'), COMPOSER_ALLOW_SUPERUSER='1',
                       APP_ENV='testing', APP_DEBUG='false', APP_URL='http://localhost', APP_KEY='base64:' + base64.b64encode(b'x' * 32).decode(),
                       DB_CONNECTION='sqlite', DB_URL='', DB_DATABASE=str(database), CACHE_STORE='array', SESSION_DRIVER='array', QUEUE_CONNECTION='sync', LOG_CHANNEL='stderr', MAIL_MAILER='array')
            command = ['composer', 'install', '--no-dev', '--no-scripts', '--no-plugins', '--no-interaction', '--no-progress', '--prefer-dist', '--optimize-autoloader']
            check = subprocess.run(command, cwd=stage, env=env, capture_output=True, timeout=120)
            (out / (kind + '-composer.private.log')).write_bytes(check.stdout + check.stderr)
            if check.returncode: raise RuntimeError(kind + ' isolated dependency preparation failed; inspect private log')
            after = json.loads((stage / 'vendor/composer/installed.json').read_text())
            expected = {p for p in versions(before) if p[0] not in before['dev-package-names']}
            assert versions(after) == expected and after['dev'] is False and after['dev-package-names'] == []
            assert not any((stage / 'vendor' / name).exists() for name in before['dev-package-names'])
            checks = []
            for args in [['migrate', '--force'], ['package:discover', '--no-ansi'], ['route:list', '--json'], ['about', '--only=environment']]:
                completed = subprocess.run(['php', 'artisan', *args], cwd=stage, env=env, capture_output=True, timeout=60)
                if completed.returncode:
                    (out / (kind + '-boot-failure.private.log')).write_bytes(completed.stdout + completed.stderr)
                    raise RuntimeError(kind + ' runtime boot check failed')
                checks.append('artisan ' + ' '.join(args))
            php = '''require 'vendor/autoload.php'; $app=require 'bootstrap/app.php'; $kernel=$app->make(Illuminate\\Contracts\\Http\\Kernel::class); $response=$kernel->handle(Illuminate\\Http\\Request::create('/login','GET')); if($response->getStatusCode()!==200)exit(2); echo "LOCAL_LOGIN_OK\\n";'''
            completed = subprocess.run(['php', '-r', php], cwd=stage, env=env, capture_output=True, timeout=60)
            if completed.returncode: raise RuntimeError(kind + ' synthetic login render failed')
            checks.append('synthetic GET /login => 200')
            (out / (kind + '-installed-production.private.json')).write_text(json.dumps(after, indent=2) + '\n')
            result[kind] = {'before_packages': len(before['packages']), 'removed_development_packages': sorted(before['dev-package-names']),
                            'after_runtime_packages': len(after['packages']), 'runtime_versions_unchanged': True, 'network_disabled': True,
                            'source_composer_json_sha256': sha((source / 'composer.json').read_bytes()), 'source_composer_lock_sha256': sha((source / 'composer.lock').read_bytes()),
                            'checks': checks, 'existing_source_vendor_untouched': True}
            print(kind, len(before['dev-package-names']), 'dev packages removed in isolated copy;', len(after['packages']), 'unchanged runtime packages; five boot/schema/login checks passed', flush=True)
    (out / 'verification.json').write_text(json.dumps(result, indent=2) + '\n')


if __name__ == '__main__': run()
