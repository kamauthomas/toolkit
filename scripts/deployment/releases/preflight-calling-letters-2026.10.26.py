#!/usr/bin/env python3
"""Read native runtime prerequisites before a calling-letter deployment; no app changes."""
import argparse
import importlib.util
import json
import shlex
import subprocess
import time
import uuid
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/calling-letters-2026-10-26/deployment-preflight'


def load(name, filename):
    spec = importlib.util.spec_from_file_location(name, Path(__file__).with_name(filename))
    module = importlib.util.module_from_spec(spec); spec.loader.exec_module(module)
    return module


def command_body():
    code = 'echo json_encode(["version"=>PHP_VERSION,"sapi"=>PHP_SAPI,"fileinfo"=>extension_loaded("fileinfo"),"dom"=>extension_loaded("dom"),"zlib"=>extension_loaded("zlib"),"mbstring"=>extension_loaded("mbstring"),"openssl"=>extension_loaded("openssl"),"pdo_mysql"=>extension_loaded("pdo_mysql"),"zip"=>extension_loaded("zip")]),"\\n";'
    template = '/home/bfyigiln/toolkit-private-storage/calling-letters/_template.docx'
    asset = '$p=' + json.dumps(template) + ';echo json_encode(["template_readable"=>is_readable($p),"template_sha256"=>is_readable($p)?hash_file("sha256",$p):null,"template_bytes"=>is_readable($p)?filesize($p):null]),"\\n";'
    return '; '.join(['umask 077', 'echo EA_PHP84', '/usr/local/bin/ea-php84 -r ' + shlex.quote(code),
                      'echo EA_PHP83', '/usr/local/bin/ea-php83 -r ' + shlex.quote(code),
                      'echo APPROVED_TEMPLATE', '/usr/local/bin/ea-php84 -r ' + shlex.quote(asset),
                      'echo TOOLKIT_CALLING_PREFLIGHT_DONE'])


def run(execute=False):
    body = command_body()
    assert '\n' not in body and '%' not in body
    subprocess.run(['/bin/sh', '-n'], input=body.encode(), check=True)
    if not execute:
        print('Prepared read-only native PHP/template preflight; no deployment or settings change.'); return
    CASE.mkdir(parents=True, mode=0o700, exist_ok=True)
    journal = CASE / 'attempt.json'
    if journal.exists(): raise RuntimeError('Existing preflight; reconcile before repeating')
    panel = load('followup', 'finish-runtime-cleanup-2026.10.26.py').connection()
    # One ordinary authenticated read before scheduling the bounded inspection.
    raw = panel.fetch('/home/bfyigiln/reception-system-demo/vendor/composer/installed.json')
    (CASE / 'demo-installed.private.json').write_bytes(raw)
    nonce = uuid.uuid4().hex
    marker = '/tmp/toolkit-calling-preflight-' + nonce
    log = '/home/bfyigiln/reception-system-demo/storage/logs/toolkit-calling-preflight-' + nonce + '.log'
    command = '/bin/sh -c ' + shlex.quote('umask 077; if mkdir ' + shlex.quote(marker) + ' 2>/dev/null; then { /bin/sh -c ' + shlex.quote(body) + '; } > ' + shlex.quote(log) + ' 2>&1; fi')
    state = {'command': command, 'private_log': log, 'cron_removed': False, 'app_files_uploaded': 0,
             'php_settings_changed': False, 'deployment_ready': False}
    def save(): journal.write_text(json.dumps(state, indent=2) + '\n')
    save()
    result = panel.api2call('Cron', 'add_line', command=command, minute='*', hour='*', day='*', month='*', weekday='*')
    assert result.get('event', {}).get('result') == 1
    output = None
    stopped = False
    try:
        for _ in range(18):
            output = panel.fetch(log, optional=True)
            if output and b'TOOLKIT_CALLING_PREFLIGHT_DONE' in output: break
            time.sleep(5)
        else: raise RuntimeError('Preflight completion not observed; inspect its private state')
    except RuntimeError as error:
        stopped = str(error) in {'Hosting request failed; stopped', 'Hosting response refused; stopped', 'Requests stopped'}
        raise
    except Exception:
        # An unexpected/non-JSON control-panel response may be a WAF challenge.
        # Stop without further requests; the one-attempt marker prevents reruns.
        stopped = True
        raise
    finally:
        if not stopped:
            for row in panel.api2call('Cron', 'listcron').get('data', []):
                if row.get('command') == command:
                    assert panel.api2call('Cron', 'remove_line', line=row['count']).get('event', {}).get('result') == 1
            state['cron_removed'] = not any(r.get('command') == command for r in panel.api2call('Cron', 'listcron').get('data', [])); save()
    if not output: raise RuntimeError('Preflight output missing')
    (CASE / 'runtime.private.log').write_bytes(output)
    sections = {}; current = None
    for line in output.decode().splitlines():
        if line in {'EA_PHP84', 'EA_PHP83', 'APPROVED_TEMPLATE'}: current = line
        elif line.startswith('{') and current: sections[current] = json.loads(line)
    assert set(sections) == {'EA_PHP84', 'EA_PHP83', 'APPROVED_TEMPLATE'}
    ready = sections['EA_PHP84']['fileinfo'] is True
    state.update(runtime=sections, extension_preflight_passed=ready,
                 blocker=None if ready else 'Target EA-PHP 8.4 fileinfo is still absent; full platform check and deployment blocked.')
    save()
    print('Native PHP 8.4:', sections['EA_PHP84']['version'], '; fileinfo:', sections['EA_PHP84']['fileinfo'], '; template readable:', sections['APPROVED_TEMPLATE']['template_readable'], '; temporary cron removed:', state['cron_removed'])
    if not ready: raise RuntimeError(state['blocker'])
    print('Extension preflight passed; proceed to full target-platform and fresh-baseline checks before deployment.')


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__); parser.add_argument('--execute', action='store_true'); args = parser.parse_args()
    try: run(args.execute)
    except Exception as error:
        print('STOPPED:', str(error) if isinstance(error, RuntimeError) else type(error).__name__)
        raise SystemExit(1)
