#!/usr/bin/env python3
"""Review/finish independent maintenance-index and runtime-file cleanup; never bypass fileinfo."""
import argparse
import hashlib
import importlib.util
import json
import re
import subprocess
import time
from pathlib import Path

import requests

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/fileinfo-followup-2026-10-26'
HOME = '/home/bfyigiln/'
SOURCE = HOME + 'reception-system/storage/app/private/platform-security-2026-10-16'
PARENT = HOME + 'toolkit-private-storage'
DEST = PARENT + '/platform-security-2026-10-16'
PLUGIN = HOME + 'public_html/wp-content/plugins/team-press/admin/inc/cmb2/'
BACKUP = PLUGIN + 'languages/cmb2-pt_PT.mo.orig'
INDEX = HOME + 'reception-public/index.php'


def sha(data): return hashlib.sha256(data).hexdigest()


def load_module(name, filename):
    spec = importlib.util.spec_from_file_location(name, Path(__file__).with_name(filename))
    module = importlib.util.module_from_spec(spec); spec.loader.exec_module(module)
    return module


def connection():
    panel = load_module('panel', 'deploy-reception-graduands-2026.10.15.1.py').Panel()
    original = panel.s.request; last = [0.0]; stopped = [False]
    def paced(method, url, **kwargs):
        if stopped[0]: raise RuntimeError('Requests stopped')
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
    return panel


def save(name, value):
    path = CASE / name; path.write_text(json.dumps(value, indent=2) + '\n'); path.chmod(0o600)


def search(panel, path):
    result = panel.api2call('Fileman', 'search', dir=path, recursive=1, mimeinfo=0,
                            attributes='size|type|mode|mtime', regex='.')
    assert result.get('event', {}).get('result') == 1 and isinstance(result.get('data'), list)
    rows = [r for r in result['data'] if 'file' in r]
    save('search-last-' + sha(path.encode())[:12] + '.private.json', rows)
    rows = [r for r in rows if not (r['file'] == path and r.get('type') == 'dir')]
    assert all(r['file'].startswith(path + '/') and r.get('type') in {'file', 'dir'} for r in rows), 'Unexpected path or symlink in recovery tree'
    return rows


def stat(panel, parent, files):
    result = panel.api2call('Fileman', 'statfiles', dir=parent, files='|'.join(files))
    assert result.get('event', {}).get('result') == 1 and isinstance(result.get('data'), list)
    return result['data']


def normal_page(url):
    time.sleep(4)
    try: response = requests.get(url, timeout=(10, 40), allow_redirects=False)
    except requests.RequestException: raise RuntimeError('Normal page request failed; stopped') from None
    if response.status_code != 200 or response.headers.get('Retry-After'):
        raise RuntimeError('Normal page response refused; stopped')
    assert not re.search(r'PHP Fatal error|Fatal error:|Uncaught (?:Error|Exception)', response.text, re.I)
    return {'url': url, 'status': response.status_code, 'release': response.headers.get('X-Toolkit-Release')}


def prepare():
    assert not (CASE / 'followup-plan.json').exists(), 'Existing preparation; inspect before replacing'
    panel = connection()
    repair = load_module('dependencies', 'deploy-runtime-dependencies-2026.10.26.py').repair_index
    demo = panel.fetch(HOME + 'reception-public-demo/index.php')
    (CASE / 'demo-index-fresh.private.php').write_bytes(demo)
    expected_demo = (ROOT / 'reports/runtime-minimum-audit-2026-10-26/dependency-deployment/demo/index-runtime.php').read_bytes()
    assert demo == expected_demo, 'Demo index differs from prior verified baseline; inspect saved fresh copy'
    assert repair(demo, 'reception-system-demo') == demo, 'Demo maintenance-path repair is absent'
    prod = panel.fetch(INDEX); patched = repair(prod, 'reception-system')
    for label, raw in [('index-before.private.php', prod), ('index-runtime.php', patched)]:
        (CASE / label).write_bytes(raw)
    subprocess.run(['php', '-l', str(CASE / 'index-runtime.php')], check=True, stdout=subprocess.DEVNULL)
    installed = panel.fetch(HOME + 'reception-system/vendor/composer/installed.json')
    baseline = (ROOT / 'reports/runtime-minimum-audit-2026-10-26/final-runtime-checks/reception-system-installed.private.json').read_bytes()
    assert installed == baseline, 'Dependency baseline changed'
    for name in ['down', 'maintenance.php']:
        assert panel.fetch(HOME + 'reception-system/storage/framework/' + name, optional=True) is None
    init = panel.fetch(PLUGIN + 'init.php')
    local_init = (ROOT / 'wp-content/plugins/team-press/admin/inc/cmb2/init.php').read_bytes()
    assert init == local_init, 'Translation loader drift requires review'
    assert b"'/languages/cmb2-' . $locale . '.mo'" in init and b'.mo.orig' not in init
    translations = stat(panel, PLUGIN + 'languages', ['cmb2-pt_PT.mo', 'cmb2-pt_PT.mo.orig'])
    assert len(translations) == 2 and all(r.get('type') == 'file' and int(r.get('exists', 0)) == 1 for r in translations)
    save('translation-metadata.private.json', translations)
    holding = search(panel, SOURCE)
    save('recovery-before.private.json', holding)
    assert holding, 'Recovery tree is empty or unavailable'
    cron = panel.api2call('Cron', 'listcron')
    assert cron.get('event', {}).get('result') == 1
    assert not any(SOURCE in r.get('command', '') for r in cron.get('data', [])), 'Active job still uses historical recovery path'
    parent = stat(panel, HOME.rstrip('/'), ['toolkit-private-storage'])
    save('destination-parent-metadata.private.json', parent)
    assert len(parent) == 1 and parent[0]['type'] == 'dir' and int(parent[0]['mode']) & 0o777 in {0o700, 0o755}, 'Unexpected destination parent permissions'
    source_stat = stat(panel, str(Path(SOURCE).parent), [Path(SOURCE).name])
    save('source-parent-metadata.private.json', source_stat)
    assert len(source_stat) == 1 and source_stat[0]['type'] == 'dir' and int(source_stat[0]['mode']) & 0o777 in {0o700, 0o755}, 'Unexpected recovery source permissions'
    destination = stat(panel, PARENT, [Path(DEST).name])
    assert not any(int(r.get('exists', 0)) for r in destination), 'Recovery destination already exists'
    checks = [normal_page('https://reception-demo.toolkitafrica.ac.ke/login')]
    plan = {'index_before_sha256': sha(prod), 'index_after_sha256': sha(patched),
            'index_already_corrected': prod == patched,
            'demo_index_sha256': sha(demo), 'demo_check': checks[0], 'vendor_sha256': sha(installed),
            'backup_file': next(r for r in translations if r['file'] == Path(BACKUP).name),
            'translation_loader_sha256': sha(init), 'recovery_source': SOURCE, 'recovery_destination': DEST,
            'recovery_entries': len(holding), 'recovery_regular_files': sum(r['type'] == 'file' for r in holding),
            'recovery_bytes': sum(int(r.get('size', 0)) for r in holding if r['type'] == 'file'),
            'recovery_source_mode': source_stat[0]['mode'], 'destination_parent_mode': parent[0]['mode'],
            'active_jobs_using_recovery_source': 0, 'php_settings_changed': False}
    save('followup-plan.json', plan)
    print('Prepared production index verification (already corrected:', prod == patched, '), one unused translation backup, and private recovery relocation:', plan['recovery_regular_files'], 'files; no mutation.')


def verify_index(execute=False):
    plan = json.loads((CASE / 'followup-plan.json').read_text())
    if not execute:
        print('Prepared existing maintenance index verification; no upload. Use --execute after review.'); return
    journal = CASE / 'index-verification.json'
    assert not journal.exists(), 'Existing verification; reconcile before retry'
    panel = connection()
    assert sha(panel.fetch(HOME + 'reception-public-demo/index.php')) == plan['demo_index_sha256']
    demo = normal_page('https://reception-demo.toolkitafrica.ac.ke/login')
    before = panel.fetch(INDEX); after = (CASE / 'index-runtime.php').read_bytes()
    assert sha(before) == plan['index_before_sha256'] and sha(after) == plan['index_after_sha256']
    assert before == after, 'Maintenance correction is not already present; a separate release review is required'
    assert sha(panel.fetch(HOME + 'reception-system/vendor/composer/installed.json')) == plan['vendor_sha256']
    for name in ['down', 'maintenance.php']:
        assert panel.fetch(HOME + 'reception-system/storage/framework/' + name, optional=True) is None
    state = {'verified': False, 'destination': INDEX, 'before_sha256': sha(before), 'after_sha256': sha(after),
             'local_rollback': str(CASE / 'index-before.private.php'), 'demo_check': demo}
    save(journal.name, state)
    page = normal_page('https://reception.toolkitafrica.ac.ke/login')
    state.update(verified=True, production_check=page, dependency_versions_unchanged=True, already_applied=before == after, uploaded_files=int(before != after))
    save(journal.name, state)
    print('VERIFIED: correct production Reception maintenance path; uploaded files:', state['uploaded_files'], '; demo/production login 200; dependencies untouched.')


def cleanup(execute=False):
    plan = json.loads((CASE / 'followup-plan.json').read_text())
    if not execute:
        print('Prepared reversible private recovery move and one obsolete translation backup; use --execute after review.'); return
    journal = CASE / 'additional-cleanup.json'
    assert not journal.exists(), 'Existing cleanup; reconcile before retry'
    panel = connection()
    current = search(panel, SOURCE)
    before = json.loads((CASE / 'recovery-before.private.json').read_text())
    def signature(rows, prefix):
        return sorted((r['file'][len(prefix):], r['type'], int(r.get('size', 0)), int(r.get('mtime', 0)), int(r.get('mode', 0))) for r in rows)
    assert signature(current, SOURCE) == signature(before, SOURCE), 'Recovery baseline drift'
    assert not any(int(r.get('exists', 0)) for r in stat(panel, PARENT, [Path(DEST).name]))
    parent = stat(panel, HOME.rstrip('/'), ['toolkit-private-storage'])
    assert len(parent) == 1 and parent[0]['type'] == 'dir' and int(parent[0]['mode']) & 0o777 in {0o700, 0o755}
    source_stat = stat(panel, str(Path(SOURCE).parent), [Path(SOURCE).name])
    assert len(source_stat) == 1 and source_stat[0]['type'] == 'dir' and source_stat[0]['mode'] == plan['recovery_source_mode'], 'Recovery root permission baseline changed'
    cron = panel.api2call('Cron', 'listcron')
    assert cron.get('event', {}).get('result') == 1 and not any(SOURCE in r.get('command', '') for r in cron.get('data', []))
    state = {'verified': False, 'operation': 'private recovery relocation; one obsolete backup moved to recovery trash', 'source': SOURCE, 'destination': DEST, 'business_records_deleted': 0}
    save(journal.name, state)
    # Restrict only this non-runtime recovery tree, not the shared private-storage parent.
    if int(source_stat[0]['mode']) & 0o777 != 0o700:
        permission = panel.api2call('Fileman', 'fileop', op='chmod', sourcefiles=SOURCE[len(HOME):], metadata='0700', doubledecode=0)
        state['permission_receipt'] = permission; save(journal.name, state)
        assert permission.get('event', {}).get('result') == 1 and len(permission.get('data', [])) == 1 and str(permission['data'][0].get('result')) == '1'
    restricted = stat(panel, str(Path(SOURCE).parent), [Path(SOURCE).name])
    assert len(restricted) == 1 and restricted[0]['type'] == 'dir' and int(restricted[0]['mode']) & 0o777 == 0o700, 'Recovery restriction not verified; do not move'
    state['recovery_mode_verified'] = '0700'; save(journal.name, state)
    result = panel.api2call('Fileman', 'fileop', op='move', sourcefiles=SOURCE[len(HOME):], destfiles=PARENT[len(HOME):], doubledecode=0)
    state['move_receipt'] = result; save(journal.name, state)
    assert result.get('event', {}).get('result') == 1 and len(result.get('data', [])) == 1 and str(result['data'][0].get('result')) == '1'
    after = search(panel, DEST)
    assert signature(after, DEST) == signature(before, SOURCE), 'Relocated recovery metadata mismatch'
    assert not any(int(r.get('exists', 0)) for r in stat(panel, str(Path(SOURCE).parent), [Path(SOURCE).name]))
    moved = stat(panel, PARENT, [Path(DEST).name])
    assert len(moved) == 1 and moved[0]['type'] == 'dir' and int(moved[0]['mode']) & 0o777 == 0o700
    state['recovery_move_verified'] = True; state['preserved_recovery_files'] = plan['recovery_regular_files']; save(journal.name, state)
    assert sha(panel.fetch(PLUGIN + 'init.php')) == plan['translation_loader_sha256']
    current_backup = stat(panel, PLUGIN + 'languages', [Path(BACKUP).name])
    expected = plan['backup_file']
    assert len(current_backup) == 1 and all(current_backup[0].get(k) == expected.get(k) for k in ['type', 'size', 'mtime', 'mode', 'exists'])
    result = panel.api2call('Fileman', 'fileop', op='trash', sourcefiles=BACKUP[len(HOME):], doubledecode=0)
    state['translation_backup_receipt'] = result; save(journal.name, state)
    assert result.get('event', {}).get('result') == 1 and len(result.get('data', [])) == 1 and str(result['data'][0].get('result')) == '1'
    assert not any(int(r.get('exists', 0)) for r in stat(panel, PLUGIN + 'languages', [Path(BACKUP).name]))
    checks = [normal_page('https://toolkitafrica.ac.ke/'), normal_page('https://reception.toolkitafrica.ac.ke/login')]
    state.update(verified=True, removed_obsolete_backups=1, recovery_bytes_preserved=plan['recovery_bytes'], checks=checks, trash_emptied=False)
    save(journal.name, state)
    print('VERIFIED:', plan['recovery_regular_files'], 'historical files moved outside Reception; metadata/permissions preserved; one unused .orig removed; normal pages 200.')


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('action', choices=['prepare', 'verify-index', 'cleanup'])
    parser.add_argument('--execute', action='store_true')
    args = parser.parse_args()
    try:
        if args.action == 'prepare': prepare() if args.execute else print('Use prepare --execute for read-only authenticated baseline collection.')
        elif args.action == 'verify-index': verify_index(args.execute)
        else: cleanup(args.execute)
    except Exception as exc:
        trace = exc.__traceback__
        while trace.tb_next: trace = trace.tb_next
        print('Stopped at source line:', trace.tb_lineno)
        print('STOPPED:', str(exc) if isinstance(exc, (RuntimeError, AssertionError)) and str(exc) else type(exc).__name__, '— reconcile saved private state; never retry blindly')
        raise SystemExit(1)
