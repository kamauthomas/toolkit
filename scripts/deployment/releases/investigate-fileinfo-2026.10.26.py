#!/usr/bin/env python3
"""Read matching PHP module/config metadata and runtime-file candidates; change no PHP settings."""
import argparse
import importlib.util
import json
import shlex
import subprocess
import time
import uuid
from pathlib import Path

import requests

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/fileinfo-followup-2026-10-26'
ROOTS = ('demo.toolkitafrica.ac.ke', 'reception-system-demo', 'reception-public-demo',
         'public_html', 'reception-system', 'reception-public',
         'virtual-campus-test', 'reports.toolkitafrica.ac.ke')
# Match directory basenames, not all descendants. Candidates need a separate review.
REGEX = r'(?i)(/(tests?|docs?|documentation|examples?|\.git|\.github|node_modules|__pycache__)$|\.(md|rst|map|bak|old|orig|zip|tar|tgz|7z)$|/(readme|changelog|changes|contributing)\.txt$|/(phpunit\.xml(?:\.dist)?|\.env\.example|package-lock\.json)$)'


def command_body():
    info = r'''echo json_encode(["version"=>PHP_VERSION,"sapi"=>PHP_SAPI,"fileinfo"=>extension_loaded("fileinfo"),"finfo_class"=>class_exists("finfo",false),"extension_dir"=>ini_get("extension_dir"),"loaded_ini"=>php_ini_loaded_file(),"scanned_ini"=>php_ini_scanned_files()]),"\n";'''
    paths = ['/opt/cpanel/ea-php84/root/usr/lib64/php/modules/fileinfo.so',
             '/opt/cpanel/ea-php84/root/usr/lib/php/modules/fileinfo.so']
    metadata = ('foreach(' + json.dumps(paths) + ' as $p){echo json_encode(["module"=>$p,"exists"=>file_exists($p),"readable"=>is_readable($p),"parent_readable"=>is_readable(dirname($p)),"realpath"=>realpath($p)]),"\\n";}')
    rows = ['umask 077', 'echo MATCHING_EA84_CONFIGURATION',
            '/usr/local/bin/ea-php84 -r ' + shlex.quote(info),
            'echo MATCHING_EA84_WITHOUT_INI',
            '/usr/local/bin/ea-php84 -n -r ' + shlex.quote(info),
            'echo MATCHING_MODULE_PATHS',
            '/usr/local/bin/ea-php84 -r ' + shlex.quote(metadata)]
    # Test only the vendor's module for this exact branch, in a disposable CLI process.
    # This does not change web/cron settings or satisfy a deployment platform gate.
    for path in paths:
        rows.append('if test -r ' + shlex.quote(path) + '; then echo ISOLATED_MATCHING_MODULE_LOAD; /usr/local/bin/ea-php84 -n -d ' + shlex.quote('extension=' + path) + ' -r ' + shlex.quote(info) + '; fi')
    rows += ['echo MATCHING_MODULE_PACKAGE',
             'if command -v rpm >/dev/null 2>&1; then rpm -q ea-php84-php-fileinfo; else echo PACKAGE_QUERY_UNAVAILABLE; fi',
             'echo TOOLKIT_FILEINFO_INVESTIGATION_DONE']
    return '; '.join(rows)


def run(execute=False):
    body = command_body()
    assert '\n' not in body and '%' not in body
    subprocess.run(['/bin/sh', '-n'], input=body.encode(), check=True)
    if not execute:
        print('Prepared: matching EA-PHP 8.4 module inspection; metadata inventory only; no PHP setting changes.')
        return
    CASE.mkdir(mode=0o700, parents=True, exist_ok=True)
    journal = CASE / 'attempt.json'
    if journal.exists(): raise RuntimeError('Existing investigation; reconcile before retry')
    spec = importlib.util.spec_from_file_location('existing_panel', Path(__file__).with_name('deploy-reception-graduands-2026.10.15.1.py'))
    module = importlib.util.module_from_spec(spec); spec.loader.exec_module(module)
    panel = module.Panel(); original = panel.s.request; last = [0.0]; stopped = [False]

    def paced(method, url, **kwargs):
        if stopped[0]: raise RuntimeError('Remote requests stopped')
        time.sleep(max(0, 4 - (time.monotonic() - last[0])))
        kwargs.update(timeout=(10, 40), allow_redirects=False)
        try: response = original(method, url, **kwargs)
        except requests.RequestException:
            stopped[0] = True; raise RuntimeError('Hosting request failed; stopped') from None
        last[0] = time.monotonic()
        if response.status_code != 200 or response.headers.get('Retry-After'):
            stopped[0] = True; raise RuntimeError('Hosting response refused; stopped')
        return response

    panel.s.request = paced
    # A single ordinary authenticated read establishes availability before a job is added.
    installed = panel.fetch('/home/bfyigiln/reception-system-demo/vendor/composer/installed.json')
    (CASE / 'demo-installed.private.json').write_bytes(installed)
    nonce = uuid.uuid4().hex
    marker = '/tmp/toolkit-fileinfo-inspection-' + nonce
    log = '/home/bfyigiln/reception-system-demo/storage/logs/toolkit-fileinfo-inspection-' + nonce + '.log'
    command = '/bin/sh -c ' + shlex.quote('umask 077; if mkdir ' + shlex.quote(marker) + ' 2>/dev/null; then { /bin/sh -c ' + shlex.quote(body) + '; } > ' + shlex.quote(log) + ' 2>&1; fi')
    state = {'private_log': log, 'command': command, 'cron_removed': False,
             'php_settings_changed': False, 'uploaded_helpers': 0, 'inventory_roots': []}
    def save(): journal.write_text(json.dumps(state, indent=2) + '\n')
    save()
    added = panel.api2call('Cron', 'add_line', command=command, minute='*', hour='*', day='*', month='*', weekday='*')
    assert added.get('event', {}).get('result') == 1
    raw = None
    try:
        for _ in range(18):
            raw = panel.fetch(log, optional=True)
            if raw and b'TOOLKIT_FILEINFO_INVESTIGATION_DONE' in raw: break
            time.sleep(5)
        else: raise RuntimeError('Module inspection completion not observed')
    finally:
        if not stopped[0]:
            for row in panel.api2call('Cron', 'listcron').get('data', []):
                if row.get('command') == command:
                    assert panel.api2call('Cron', 'remove_line', line=row['count']).get('event', {}).get('result') == 1
            state['cron_removed'] = not any(r.get('command') == command for r in panel.api2call('Cron', 'listcron').get('data', [])); save()
    if raw:
        dest = CASE / 'matching-module.private.log'; dest.write_bytes(raw); dest.chmod(0o600)
    print('Matching PHP module metadata captured; temporary job removed:', state['cron_removed'], flush=True)
    for root in ROOTS:
        result = panel.api2call('Fileman', 'search', dir='/home/bfyigiln/' + root,
                                recursive=1, mimeinfo=0, attributes='size|type|mode|mtime', regex=REGEX)
        assert result.get('event', {}).get('result') == 1 and isinstance(result.get('data'), list)
        rows = [r for r in result['data'] if 'file' in r]
        path = CASE / (root + '-candidates.private.json')
        path.write_text(json.dumps(rows, indent=2) + '\n'); path.chmod(0o600)
        state['inventory_roots'].append({'root': root, 'candidates': len(rows)}); save()
        print('Metadata inventory:', root, len(rows), 'candidates; no files removed', flush=True)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__); parser.add_argument('--execute', action='store_true')
    args = parser.parse_args()
    try: run(args.execute)
    except Exception as exc:
        print('STOPPED:', str(exc) if isinstance(exc, RuntimeError) else type(exc).__name__, '— inspect saved private state before further action')
        raise SystemExit(1)
