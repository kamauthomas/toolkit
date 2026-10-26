#!/usr/bin/env python3
"""Move exact reviewed non-runtime files to cPanel trash; never empty trash."""
import argparse
import importlib.util
import json
import re
import time
from pathlib import Path, PurePosixPath

import requests

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/runtime-minimum-audit-2026-10-26'
HOME = '/home/bfyigiln/'
GROUPS = {'demo': {'demo.toolkitafrica.ac.ke', 'reception-system-demo'},
          'production': {'public_html', 'reception-system', 'virtual-campus-test'}}
LEGAL = {'license', 'license.md', 'license.txt', 'licence', 'licence.txt',
         'copyright', 'copyright.txt', 'notice', 'notice.txt'}
CANARY = HOME + 'demo.toolkitafrica.ac.ke/wp-content/uploads/toolkit-hardening-canary-20261016.php'
REGEX = r'(?i)(\.(md|rst|map)$|/(readme|changelog|changes|contributing)\.txt$|toolkit-hardening-canary-20261016\.php$)'


def validate(rows, environment):
    seen = set()
    for row in rows:
        name = row['file']; path = PurePosixPath(name)
        assert name.startswith(HOME + row['root'] + '/') and row['root'] in GROUPS[environment]
        assert str(path) == name and '..' not in path.parts and '\\' not in name
        assert not any(c in name for c in ',%\n\r\x00') and name not in seen
        assert row['type'] == 'file' and path.name.lower() not in LEGAL
        assert '/storage/' not in name and '/modules/mcp/static-resources/' not in name
        assert '/wp-content/uploads/' not in name or name == CANARY
        assert name == CANARY or '/vendor/' in name or '/wp-content/plugins/' in name
        assert name == CANARY or path.suffix.lower() in {'.md', '.rst', '.map'} or path.name.lower() in {'readme.txt', 'changelog.txt', 'changes.txt', 'contributing.txt'}
        seen.add(name)


def run(environment, execute=False):
    rows = [r for r in json.loads((CASE / 'cleanup-plan.private.json').read_text()) if r['root'] in GROUPS[environment]]
    validate(rows, environment)
    if not execute:
        print('Reviewed files:', len(rows), '; no remote action. Use --execute only after diff/plan review.')
        return
    if environment == 'production':
        assert json.loads((CASE / 'cleanup-demo.json').read_text())['verified']
    receipt = CASE / ('cleanup-' + environment + '.json')
    if receipt.exists():
        raise RuntimeError('Existing cleanup attempt; reconcile before retry')
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

    def inventory(root):
        result = panel.api2call('Fileman', 'search', dir=HOME + root, recursive=1,
                                mimeinfo=0, attributes='size|type|mode|mtime', regex=REGEX)
        if result.get('event', {}).get('result') != 1 or not isinstance(result.get('data'), list):
            raise RuntimeError('Inventory did not succeed')
        return {r['file']: r for r in result['data'] if 'file' in r}

    roots = sorted({r['root'] for r in rows})
    fresh = {root: inventory(root) for root in roots}
    for row in rows:
        current = fresh[row['root']].get(row['file'])
        if current is None or any(current.get(k) != row.get(k) for k in ('type', 'size', 'mtime')):
            raise RuntimeError('Cleanup baseline changed; review required')
    journal = {'environment': environment, 'verified': False, 'operation': 'reversible cPanel trash; never emptied', 'batches': []}
    def save(): receipt.write_text(json.dumps(journal, indent=2) + '\n')
    save()
    for offset in range(0, len(rows), 8):
        batch = rows[offset:offset + 8]
        result = panel.api2call('Fileman', 'fileop', op='trash',
                               sourcefiles=','.join(r['file'][len(HOME):] for r in batch), doubledecode=0)
        journal['batches'].append({'files': [r['file'] for r in batch], 'receipt': result}); save()
        if result.get('event', {}).get('result') != 1 or len(result.get('data', [])) != len(batch) or any(str(r.get('result')) != '1' for r in result['data']):
            raise RuntimeError('Cleanup batch incomplete; reconcile saved receipt')
        print(environment, 'moved to private recovery trash:', min(offset + 8, len(rows)), '/', len(rows), flush=True)
    remaining = {root: inventory(root) for root in roots}
    if any(r['file'] in remaining[r['root']] for r in rows):
        raise RuntimeError('A reviewed file remains in its application directory')
    base = 'https://' + ('demo.' if environment == 'demo' else '') + 'toolkitafrica.ac.ke/'
    time.sleep(4)
    try: response = requests.get(base, timeout=(10, 40), allow_redirects=False)
    except requests.RequestException: raise RuntimeError('Normal homepage check failed; stopped') from None
    if response.status_code != 200 or response.headers.get('Retry-After'):
        raise RuntimeError('Normal homepage response refused; stopped')
    journal.update(verified=True, files_removed_from_application_roots=len(rows), homepage_status=200,
                   runtime_resources_and_licenses_preserved=True, public_downloads_unchanged=True)
    save(); print('VERIFIED', environment, len(rows), 'non-runtime files removed from application roots; recovery copies remain private')


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('environment', choices=GROUPS)
    parser.add_argument('--execute', action='store_true')
    args = parser.parse_args()
    try: run(args.environment, args.execute)
    except Exception as exc:
        print('STOPPED:', str(exc) if isinstance(exc, RuntimeError) else type(exc).__name__)
        raise SystemExit(1)
