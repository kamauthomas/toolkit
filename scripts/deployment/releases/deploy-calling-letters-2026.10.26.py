#!/usr/bin/env python3
"""Deploy the reviewed calling-letter files, demo first, under the recorded one-run exception."""
import argparse
import base64
import hashlib
import importlib.util
import json
import shlex
import subprocess
import time
import uuid
from pathlib import Path

import requests

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/calling-letters-2026-10-26'


def sha(data): return hashlib.sha256(data).hexdigest()


def load(name, path):
    spec = importlib.util.spec_from_file_location(name, path)
    module = importlib.util.module_from_spec(spec); spec.loader.exec_module(module)
    return module


def inline_php(name):
    raw = subprocess.check_output(['php', '-w', str(ROOT / 'scripts/deployment/audit' / name)]).decode()
    assert raw.startswith('<?php')
    code = raw[5:].strip()
    assert '\n' not in code and '%' not in code
    return code


def php_command(binary, code, *args):
    return shlex.join([binary, '-d', 'memory_limit=512M', '-r', code, '--', *args])


def wordpress_code(enable):
    return ('define("DISABLE_WP_CRON",true);require $argv[1]."/wp-load.php";'
            'update_option("toolkit_reception_calling_letters_enabled",' + ('1' if enable else '0') + ',false);'
            + ('if(!toolkit_reception_letters_enabled())exit(3);'
               '$id=$wpdb->get_var("SELECT l.application_id FROM ".toolkit_calling_letter_table_name()." l JOIN ".toolkit_application_table_name()." a ON a.id=l.application_id WHERE l.status=CHAR(103,101,110,101,114,97,116,101,100) AND l.pdf_path IS NOT NULL ORDER BY l.id ASC LIMIT 1");'
               'echo json_encode(["wordpress_letters"=>true,"candidate_id"=>$id?(int)$id:null,"template_readable"=>is_readable(toolkit_calling_letter_template_path())]),"\\n";' if enable else 'echo "WORDPRESS_LETTERS_DISABLED\\n";'))


def worker_body(environment, app, site, backup, log):
    php = '/usr/local/bin/ea-php84'
    wpphp = php if environment == 'demo' else '/usr/local/bin/ea-php83'
    env_code = inline_php('calling-letter-env-switch.php')
    check_code = inline_php('calling-letter-runtime-check.php')
    disable = php_command(php, env_code, app, backup, 'disable')
    wp_disable = php_command(wpphp, wordpress_code(False), site)
    config_clear = shlex.join([php, app + '/artisan', 'config:clear'])
    finish = 'finish() { code=$?; trap - EXIT; if test "$code" -ne 0; then ' + disable + ' || true; ' + config_clear + ' || true; ' + wp_disable + ' || true; echo CALLING_LETTERS_DISABLED_AFTER_FAILURE; fi; exit "$code"; }'
    tasks = [
        'set -eu', 'umask 077', finish, 'trap finish EXIT',
        php_command(php, env_code, app, backup, 'enable'), config_clear,
        shlex.join([php, app + '/artisan', 'route:clear']), shlex.join([php, app + '/artisan', 'view:clear']),
        shlex.join([php, app + '/artisan', 'migrate', '--force', '--path=database/migrations/2026_10_26_120000_create_calling_letters_table.php']),
        php_command(wpphp, wordpress_code(True), site),
        shlex.join([php, app + '/artisan', 'reception:sync-website-applications']),
        php_command(php, check_code, app, environment, log),
        'echo CALLING_LETTERS_READY',
    ]
    return '; '.join(tasks)


def run(environment, execute=False):
    authorization = json.loads((CASE / 'deployment-authorization.json').read_text())
    assert authorization['authorized_by'] == 'user' and authorization['status'] == 'active'
    assert authorization['one_run_only'] and authorization['allow_existing_fileinfo_failure']
    prep = CASE / (environment + '-preparation')
    manifest = json.loads((prep / 'manifest.json').read_text())
    assert manifest['environment'] == environment and manifest['prepared'] and not manifest['deployed']
    rows = manifest['files']; assert len(rows) == 13
    expected = {('wordpress', n) for n in ['functions.php','inc/site-metrics.php','inc/calling-letter-pdf.php','inc/reception-calling-letters.php']}
    expected |= {('reception', n) for n in ['config/services.php','routes/web.php','resources/views/staff/website-applications/show.blade.php','resources/views/staff/applicants/show.blade.php','app/Services/CallingLetterGateway.php','app/Models/CallingLetter.php','app/Http/Controllers/Staff/CallingLetterController.php','database/migrations/2026_10_26_120000_create_calling_letters_table.php','resources/views/staff/calling-letters/index.blade.php']}
    assert {(r['component'],r['file']) for r in rows} == expected
    policy = load('policy', ROOT / 'scripts/deployment/runtime_payload.py')
    for row in rows:
        policy.assert_runtime_upload(row['remote'])
        assert sha((prep / 'payload' / row['component'] / row['file']).read_bytes()) == row['sha256']
    if environment == 'production':
        demo = json.loads((CASE / 'deployment/demo/verification.json').read_text()); assert demo['verified']
        assert {(r['component'],r['file'],r['sha256']) for r in demo['files']} == {(r['component'],r['file'],r['sha256']) for r in rows}, 'Production payload differs from verified demo; review required'
    if not execute:
        print('Reviewed 13-file', environment, 'release; scoped fileinfo exception recorded; no remote action.'); return
    output = CASE / 'deployment' / environment; output.mkdir(mode=0o700, parents=True, exist_ok=True)
    journal = output / 'attempt.json'
    if journal.exists(): raise RuntimeError('Existing deployment attempt; reconcile saved state, never replay')
    panel = load('followup', Path(__file__).with_name('finish-runtime-cleanup-2026.10.26.py')).connection()
    state = {'environment': environment, 'verified': False, 'uploaded': [], 'fileinfo_exception': authorization,
             'php_and_dependencies_changed': False, 'cron_removed': None, 'files': rows}
    def save(): journal.write_text(json.dumps(state, indent=2) + '\n')
    # Fresh match on every destination before any upload, followed by per-file rechecks.
    for row in rows:
        raw = panel.fetch(row['remote'], optional=row['baseline_sha256'] is None)
        assert (None if raw is None else sha(raw)) == row['baseline_sha256'], 'Fresh live-source drift'
        if raw is not None:
            path = output / 'before' / row['component'] / row['file']; path.parent.mkdir(parents=True, exist_ok=True); path.write_bytes(raw); path.chmod(0o600)
    save(); print(environment, '13 fresh baseline checks passed', flush=True)
    ordered = sorted(rows, key=lambda r: (r['component'] != 'wordpress', r['file'] in {'functions.php','routes/web.php'}, r['baseline_sha256'] is not None))
    for row in ordered:
        raw = panel.fetch(row['remote'], optional=row['baseline_sha256'] is None)
        assert (None if raw is None else sha(raw)) == row['baseline_sha256'], 'Concurrent live edit'
        state['upload_intent'] = row['component'] + '/' + row['file']; save()
        payload = (prep / 'payload' / row['component'] / row['file']).read_bytes(); panel.upload(row['remote'], payload)
        assert panel.fetch(row['remote']) == payload, 'Uploaded bytes differ'
        state['uploaded'].append(state.pop('upload_intent')); save()
        print(environment, 'verified runtime file', row['component'] + '/' + row['file'], flush=True)
    app = '/home/bfyigiln/reception-system' + ('-demo' if environment == 'demo' else '')
    site = '/home/bfyigiln/' + ('demo.toolkitafrica.ac.ke' if environment == 'demo' else 'public_html')
    nonce = uuid.uuid4().hex
    backup = '/home/bfyigiln/toolkit-private-storage/calling-letters-release-' + environment + '-' + nonce
    log = app + '/storage/logs/calling-letters-release-' + nonce + '.log'
    marker = '/tmp/toolkit-calling-release-' + nonce
    body = worker_body(environment, app, site, backup, log)
    subprocess.run(['/bin/sh','-n'], input=body.encode(), check=True)
    command = '/bin/sh -c ' + shlex.quote('umask 077; if mkdir ' + shlex.quote(marker) + ' 2>/dev/null; then { /bin/sh -c ' + shlex.quote(body) + '; code=$?; echo TOOLKIT_CALLING_EXIT=$code; } > ' + shlex.quote(log) + ' 2>&1; fi')
    assert '\n' not in command and '%' not in command
    (output / 'reviewed-command.private.sh').write_text(command + '\n')
    state.update(command=command, private_log=log, private_config_backup=backup, cron_removed=False, cron_submission='intent-recorded'); save()
    result = panel.api2call('Cron','add_line',command=command,minute='*',hour='*',day='*',month='*',weekday='*')
    assert result.get('event',{}).get('result') == 1
    state['cron_submission'] = 'accepted'; save()
    receipt = None; stop = False
    try:
        for _ in range(30):
            receipt = panel.fetch(log, optional=True)
            if receipt and b'TOOLKIT_CALLING_EXIT=' in receipt: break
            time.sleep(5)
        else: raise RuntimeError('Release completion not observed; inspect private state before further action')
        for line in receipt.decode().splitlines():
            if line.startswith('{'):
                value = json.loads(line)
                if value.get('network_stop'): stop = True; raise RuntimeError('Runtime HTTP was refused/failed; stop requests and inspect the one-attempt job')
    except RuntimeError as error:
        if str(error) in {'Hosting request failed; stopped','Hosting response refused; stopped','Requests stopped'}: stop = True
        raise
    except Exception:
        stop = True
        raise
    finally:
        if receipt:
            (output / 'runtime.private.log').write_bytes(receipt)
        if not stop:
            for row in panel.api2call('Cron','listcron').get('data',[]):
                if row.get('command') == command:
                    assert panel.api2call('Cron','remove_line',line=row['count']).get('event',{}).get('result') == 1
            state['cron_removed'] = not any(row.get('command') == command for row in panel.api2call('Cron','listcron').get('data',[])); save()
    if not receipt or b'TOOLKIT_CALLING_EXIT=0' not in receipt or b'CALLING_LETTERS_READY' not in receipt:
        raise RuntimeError('Runtime activation/acceptance failed; feature-disable trap ran; inspect private receipt')
    acceptance = next(json.loads(line) for line in receipt.decode().splitlines() if line.startswith('{') and json.loads(line).get('acceptance') == 'passed')
    if acceptance.pop('demo_pdf', None):
        # Only demo acceptance can emit a synthetic preview, never production documents.
        original = next(json.loads(line) for line in receipt.decode().splitlines() if line.startswith('{') and json.loads(line).get('acceptance') == 'passed')
        assert environment == 'demo'
        (output / 'issued-preview.pdf').write_bytes(base64.b64decode(original['demo_pdf'],validate=True))
    state['runtime_acceptance'] = acceptance; save()
    checks = []
    for url in ['https://' + ('demo.' if environment == 'demo' else '') + 'toolkitafrica.ac.ke/',
                'https://reception' + ('-demo' if environment == 'demo' else '') + '.toolkitafrica.ac.ke/login']:
        time.sleep(4)
        try: response = requests.get(url,timeout=(10,40),allow_redirects=False)
        except requests.RequestException: raise RuntimeError('Normal release page check failed; stopped') from None
        if response.status_code != 200 or response.headers.get('Retry-After'): raise RuntimeError('Normal release page refused; stopped')
        checks.append({'url':url,'status':200})
    state.update(verified=True, enabled=True, public_checks=checks, production_test_applications=0)
    save(); (output / 'verification.json').write_text(json.dumps(state,indent=2)+'\n')
    print('VERIFIED',environment,'13 runtime files; enabled module, schema, private runtime checks, normal pages and removed setup job; dependencies/PHP unchanged',flush=True)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__); parser.add_argument('environment',choices=['demo','production']);parser.add_argument('--execute',action='store_true');args=parser.parse_args()
    try: run(args.environment,args.execute)
    except Exception as error:
        print('STOPPED:',str(error) if isinstance(error,RuntimeError) else type(error).__name__,'— reconcile saved private state before retry',flush=True)
        raise SystemExit(1)
