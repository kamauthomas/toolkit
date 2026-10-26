#!/usr/bin/env python3
"""Remove locked development dependencies using Composer, demo first, with private rollback."""
import argparse
import hashlib
import importlib.util
import json
import re
import shlex
import subprocess
import time
import uuid
from pathlib import Path

import requests

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/runtime-minimum-audit-2026-10-26'
TARGETS = {
    'demo': ('reception', 'reception-system-demo', 'reception-public-demo', 'https://reception-demo.toolkitafrica.ac.ke'),
    'production': ('reception', 'reception-system', 'reception-public', 'https://reception.toolkitafrica.ac.ke'),
    'campus': ('campus', 'virtual-campus-test', 'virtual-campus-test/public', 'https://campus-test.toolkitafrica.ac.ke'),
}


def sha(data): return hashlib.sha256(data).hexdigest()
def versions(data): return {(p['name'], p['version']) for p in data['packages']}


def repair_index(raw, private_name):
    assert private_name in {'reception-system', 'reception-system-demo'}
    old = b"__DIR__.'/../storage/framework/maintenance.php'"
    new = ("__DIR__.'/../" + private_name + "/storage/framework/maintenance.php'").encode()
    assert raw.count(old) == 1 and ("/../" + private_name + "/vendor/autoload.php").encode() in raw
    return raw.replace(old, new, 1)


def maintenance_script(private, backup, php='/usr/local/bin/ea-php84', candidates=('/opt/cpanel/composer/bin/composer', '/usr/local/bin/composer')):
    # Complete shell statements keep the cPanel cron command on one inspectable line.
    finish = ('finish() { code=$?; trap - EXIT; set +e; '
              'if test "$code" -ne 0 && test "$restore_needed" -eq 1; then '
              'if test -d "$app/vendor"; then mv "$app/vendor" "$backup/failed-vendor"; fi; '
              'cp -a "$backup/vendor" "$app/vendor"; '
              'cp -a "$backup/bootstrap-cache/." "$app/bootstrap/cache/"; '
              'echo PRIVATE_VENDOR_ROLLBACK_ATTEMPTED; fi; '
              'if test "$maintenance_started" -eq 1; then "$php" "$app/artisan" up; fi; '
              'echo TOOLKIT_DEPENDENCIES_EXIT=$code; exit "$code"; }')
    return '; '.join([
        'set -eu', 'umask 077', 'app=' + shlex.quote(private), 'backup=' + shlex.quote(backup),
        'php=' + shlex.quote(php), 'composer=',
        'for candidate in ' + ' '.join(shlex.quote(p) for p in candidates) + '; do if test -f "$candidate" && test -r "$candidate"; then composer=$candidate; break; fi; done',
        'if test -z "$composer"; then echo COMPOSER_NOT_AVAILABLE; exit 42; fi',
        'test ! -e "$backup"', 'mkdir -p "$backup"', 'chmod 700 "$backup"',
        'cp -a "$app/vendor" "$backup/vendor"', 'cp -a "$app/bootstrap/cache" "$backup/bootstrap-cache"',
        'maintenance_started=0', 'restore_needed=0', finish, 'trap finish EXIT',
        'maintenance_started=1', '\"$php\" \"$app/artisan\" down --render=errors::503 --retry=60',
        'restore_needed=1', 'cd "$app"',
        'COMPOSER_DISABLE_NETWORK=1 COMPOSER_ALLOW_SUPERUSER=1 COMPOSER_AUTH=\'{}\' COMPOSER_CACHE_DIR="$backup/composer-cache" "$php" -d memory_limit=512M "$composer" install --no-dev --no-scripts --no-plugins --no-interaction --no-progress --prefer-dist --optimize-autoloader',
        # Only regenerable framework caches; no business/configuration source is removed.
        'rm -f "$app/bootstrap/cache/packages.php" "$app/bootstrap/cache/services.php" "$app/bootstrap/cache/config.php"',
        '\"$php\" \"$app/artisan\" package:discover --no-ansi',
        '\"$php\" \"$app/artisan\" about --only=environment --no-ansi',
        '\"$php\" \"$app/artisan\" up', 'maintenance_started=0', 'restore_needed=0',
        'echo RUNTIME_DEPENDENCIES_READY',
    ])


def run(target, execute=False):
    kind, name, public, base = TARGETS[target]
    prep = json.loads((CASE / 'dependency-preparation/verification.json').read_text())[kind]
    before_raw = (CASE / 'final-runtime-checks' / (name + '-installed.private.json')).read_bytes()
    before = json.loads(before_raw)
    assert before['dev'] is True and len(before['dev-package-names']) == 33
    assert prep['runtime_versions_unchanged'] and len(prep['removed_development_packages']) == 33
    if not execute:
        print(target, 'prepared: remove 33 development packages only; no network action. Use --execute after review.')
        return
    if target != 'demo':
        assert json.loads((CASE / 'dependency-deployment/demo/verification.json').read_text())['verified']
    out = CASE / 'dependency-deployment' / target; out.mkdir(parents=True, mode=0o700, exist_ok=True)
    attempt_path = out / 'attempt.json'
    if attempt_path.exists(): raise RuntimeError('Existing dependency attempt; reconcile it before retry')
    spec = importlib.util.spec_from_file_location('existing_panel', Path(__file__).with_name('deploy-reception-graduands-2026.10.15.1.py'))
    module = importlib.util.module_from_spec(spec); spec.loader.exec_module(module)
    panel = module.Panel(); original = panel.s.request; last = [0.0]; blocked = [False]

    def paced(method, url, **kwargs):
        if blocked[0]: raise RuntimeError('Remote work has stopped')
        time.sleep(max(0, 4 - (time.monotonic() - last[0])))
        kwargs.update(timeout=(10, 40), allow_redirects=False)
        try: response = original(method, url, **kwargs)
        except requests.RequestException:
            blocked[0] = True; raise RuntimeError('Hosting request failed; stopped') from None
        last[0] = time.monotonic()
        if response.status_code != 200 or response.headers.get('Retry-After'):
            blocked[0] = True; raise RuntimeError('Hosting response refused; stopped')
        return response
    panel.s.request = paced
    private = '/home/bfyigiln/' + name; index = '/home/bfyigiln/' + public + '/index.php'
    assert panel.fetch(private + '/vendor/composer/installed.json') == before_raw
    for file in ('composer.json', 'composer.lock'):
        assert sha(panel.fetch(private + '/' + file)) == prep['source_' + file.replace('.', '_') + '_sha256']
    for file in ('down', 'maintenance.php'):
        assert panel.fetch(private + '/storage/framework/' + file, optional=True) is None, 'Existing maintenance state; leave it untouched'
    raw = panel.fetch(index); (out / 'index-before.private.php').write_bytes(raw)
    if kind == 'reception':
        patched = repair_index(raw, name); (out / 'index-runtime.php').write_bytes(patched)
        subprocess.run(['php', '-l', str(out / 'index-runtime.php')], check=True, stdout=subprocess.DEVNULL)
    else:
        assert b"__DIR__.'/../storage/framework/maintenance.php'" in raw
        patched = raw
    attempt = {'target': target, 'verified': False, 'cron_removed': None, 'runtime_versions_unchanged': None}
    def save(): attempt_path.write_text(json.dumps(attempt, indent=2) + '\n')
    save()
    if patched != raw:
        assert panel.fetch(index) == raw
        panel.upload(index, patched)
        assert panel.fetch(index) == patched
        attempt['maintenance_front_controller_repaired'] = True; save()
    nonce = uuid.uuid4().hex; marker = '/tmp/toolkit-dependencies-' + nonce
    backup = '/home/bfyigiln/toolkit-private-storage/dependency-rollback-2026-10-26/' + name + '-' + nonce
    log = private + '/storage/logs/toolkit-dependencies-' + nonce + '.log'
    body = maintenance_script(private, backup)
    subprocess.run(['/bin/sh', '-n'], input=body.encode(), check=True)
    command = '/bin/sh -c ' + shlex.quote('umask 077; if mkdir ' + shlex.quote(marker) + ' 2>/dev/null; then { /bin/sh -c ' + shlex.quote(body) + '; code=$?; echo TOOLKIT_DEPENDENCY_WORKER_EXIT=$code; } > ' + shlex.quote(log) + ' 2>&1; fi')
    assert '\n' not in command and '%' not in command
    (out / 'reviewed-maintenance-command.private.sh').write_text(command + '\n')
    added = panel.api2call('Cron', 'add_line', command=command, minute='*', hour='*', day='*', month='*', weekday='*')
    assert added.get('event', {}).get('result') == 1
    attempt.update(maintenance_command=command, private_rollback=backup, private_log=log, cron_removed=False); save()
    receipt = None
    try:
        for _ in range(24):
            receipt = panel.fetch(log, optional=True)
            if receipt and b'TOOLKIT_DEPENDENCY_WORKER_EXIT=' in receipt: break
            time.sleep(5)
        else: raise RuntimeError('Dependency completion not observed; inspect private state before retry')
    finally:
        if not blocked[0]:
            for row in panel.api2call('Cron', 'listcron').get('data', []):
                if row.get('command') == command:
                    assert panel.api2call('Cron', 'remove_line', line=row['count']).get('event', {}).get('result') == 1
            attempt['cron_removed'] = not any(r.get('command') == command for r in panel.api2call('Cron', 'listcron').get('data', [])); save()
    if receipt:
        file = out / 'maintenance.private.log'; file.write_bytes(receipt); file.chmod(0o600)
    if not receipt or b'TOOLKIT_DEPENDENCIES_EXIT=0' not in receipt or b'TOOLKIT_DEPENDENCY_WORKER_EXIT=0' not in receipt or b'RUNTIME_DEPENDENCIES_READY' not in receipt:
        raise RuntimeError('Dependency operation did not succeed; inspect private receipt/rollback state')
    after = json.loads(panel.fetch(private + '/vendor/composer/installed.json'))
    expected = {p for p in versions(before) if p[0] not in before['dev-package-names']}
    assert after['dev'] is False and after['dev-package-names'] == [] and versions(after) == expected
    for file in ('down', 'maintenance.php'):
        assert panel.fetch(private + '/storage/framework/' + file, optional=True) is None
    time.sleep(4)
    try: response = requests.get(base + '/login', timeout=(10, 40), allow_redirects=False)
    except requests.RequestException: raise RuntimeError('Normal login request failed; stopped') from None
    assert response.status_code == 200 and not response.headers.get('Retry-After')
    assert not re.search(r'PHP Fatal error|Fatal error:|Uncaught (?:Error|Exception)', response.text, re.I)
    attempt.update(verified=True, runtime_versions_unchanged=True, development_packages_removed=33,
                   remaining_runtime_packages=len(after['packages']), login_status=200, maintenance_removed=True)
    save(); (out / 'verification.json').write_text(json.dumps(attempt, indent=2) + '\n')
    print('VERIFIED', target, '33 development packages removed;', len(after['packages']), 'unchanged runtime packages; maintenance/setup job removed; login 200')


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__); parser.add_argument('target', choices=TARGETS); parser.add_argument('--execute', action='store_true'); args = parser.parse_args()
    try: run(args.target, args.execute)
    except Exception as exc:
        print('STOPPED:', str(exc) if isinstance(exc, RuntimeError) else type(exc).__name__, '— reconcile private receipt; never retry blindly')
        raise SystemExit(1)
