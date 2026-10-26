#!/usr/bin/env python3
"""Read installed PHP/package patch evidence without public diagnostics or exploit requests."""
import argparse
import importlib.util
import json
import re
import shlex
import time
import uuid
from pathlib import Path

import requests

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/php-upgrade-assessment-2026-10-26'


def command_body():
    info = 'echo json_encode(["version"=>PHP_VERSION,"sapi"=>PHP_SAPI,"fileinfo"=>extension_loaded("fileinfo"),"upload_progress"=>ini_get("session.upload_progress.enabled"),"session_serializer"=>ini_get("session.serialize_handler")]),"\\n";'
    rows = ['umask 077']
    for name, path in [('ea-php83', '/usr/local/bin/ea-php83'), ('ea-php84', '/usr/local/bin/ea-php84'), ('alt-php84', '/opt/alt/php84/usr/bin/php'), ('cpanel-internal', '/usr/local/cpanel/3rdparty/bin/php')]:
        rows.append('echo RUNTIME=' + name)
        rows.append('if test -x ' + shlex.quote(path) + '; then ' + shlex.quote(path) + ' -r ' + shlex.quote(info) + '; else echo RUNTIME_UNAVAILABLE; fi')
    packages = ['ea-php83-php-cli', 'ea-php84-php-cli', 'alt-php84-php-cli', 'ea-php83-php-litespeed', 'ea-php84-php-litespeed', 'cpanel-php84']
    rows.append('if command -v rpm >/dev/null 2>&1; then echo RPM_PACKAGES; rpm -q ' + ' '.join(packages) + '; fi')
    for name in packages:
        rows.append('echo PATCHLOG=' + name)
        rows.append('if command -v rpm >/dev/null 2>&1; then rpm -q --changelog ' + name + ' 2>/dev/null | sed -n \'/CVE-2026-109618\\|CVE-2026-109619\\|CVE-2026-115560/p\' | head -6; fi')
    rows.append('echo TOOLKIT_PHP_ASSESSMENT_DONE')
    return '; '.join(rows)


def run(execute=False):
    body = command_body(); assert '\n' not in body and '%' not in body
    if not execute:
        print('Read-only assessment prepared; no live action. Use --execute after review.'); return
    CASE.mkdir(mode=0o700, parents=True, exist_ok=True)
    journal = CASE / 'attempt.json'
    if journal.exists(): raise RuntimeError('Existing assessment; reconcile before any retry')
    spec = importlib.util.spec_from_file_location('panel', Path(__file__).with_name('deploy-reception-graduands-2026.10.15.1.py'))
    module = importlib.util.module_from_spec(spec); spec.loader.exec_module(module)
    panel = module.Panel(); original = panel.s.request; last = [0.0]; blocked = [False]
    def normal(method, url, **kwargs):
        if blocked[0]: raise RuntimeError('Requests have stopped')
        time.sleep(max(0, 4 - (time.monotonic() - last[0])))
        kwargs.update(timeout=(10, 40), allow_redirects=False)
        try: response = original(method, url, **kwargs)
        except requests.RequestException:
            blocked[0] = True; raise RuntimeError('Hosting request failed; stopped') from None
        last[0] = time.monotonic()
        if response.status_code != 200 or response.headers.get('Retry-After'):
            blocked[0] = True; raise RuntimeError('Hosting response refused; stopped')
        return response
    panel.s.request = normal
    cores = {}
    for label, root in [('production', 'public_html'), ('demo', 'demo.toolkitafrica.ac.ke')]:
        raw = panel.fetch('/home/bfyigiln/' + root + '/wp-includes/version.php')
        match = re.search(rb'\$wp_version\s*=\s*[\'\"]([^\'\"]+)', raw)
        if not match: raise RuntimeError('Cannot establish installed WordPress version')
        cores[label] = match.group(1).decode()
    nonce = uuid.uuid4().hex; marker = '/tmp/toolkit-php-assessment-' + nonce
    log = '/home/bfyigiln/reception-system-demo/storage/logs/toolkit-php-assessment-' + nonce + '.log'
    command = '/bin/sh -c ' + shlex.quote('umask 077; if mkdir ' + shlex.quote(marker) + ' 2>/dev/null; then { /bin/sh -c ' + shlex.quote(body) + '; } > ' + shlex.quote(log) + ' 2>&1; fi')
    state = {'command': command, 'private_log': log, 'cron_removed': False, 'wordpress_versions': cores}
    def save(): journal.write_text(json.dumps(state, indent=2) + '\n')
    save()
    added = panel.api2call('Cron', 'add_line', command=command, minute='*', hour='*', day='*', month='*', weekday='*')
    assert added.get('event', {}).get('result') == 1
    raw = None
    try:
        for _ in range(18):
            raw = panel.fetch(log, optional=True)
            if raw and b'TOOLKIT_PHP_ASSESSMENT_DONE' in raw: break
            time.sleep(5)
        else: raise RuntimeError('Assessment completion not observed')
    finally:
        if not blocked[0]:
            for row in panel.api2call('Cron', 'listcron').get('data', []):
                if row.get('command') == command:
                    assert panel.api2call('Cron', 'remove_line', line=row['count']).get('event', {}).get('result') == 1
            state['cron_removed'] = not any(row.get('command') == command for row in panel.api2call('Cron', 'listcron').get('data', [])); save()
    if raw:
        file = CASE / 'runtime-package-evidence.private.log'; file.write_bytes(raw); file.chmod(0o600)
    print('Read-only PHP runtime/package evidence captured; temporary job removed:', state['cron_removed'], '; WordPress:', cores)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__); parser.add_argument('--execute', action='store_true'); args = parser.parse_args()
    try: run(args.execute)
    except Exception as exc:
        print('STOPPED:', str(exc) if isinstance(exc, RuntimeError) else type(exc).__name__); raise SystemExit(1)
