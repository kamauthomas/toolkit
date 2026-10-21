#!/usr/bin/env python3
"""Apply the operator's exact nine removals, retaining the already-listed Cindy once."""
import argparse
import base64
import csv
import hashlib
import importlib.util
import json
import time
from pathlib import Path

import requests
from bs4 import BeautifulSoup

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/graduation-2026-10-21/amendment'
PRIVATE = '/home/bfyigiln/reception-system/storage/app/private'


def run(environment):
    csv_body = (CASE / 'approved-285.csv').read_bytes()
    manifest = json.loads((CASE / 'approved-amendment.json').read_text())
    assert hashlib.sha256(csv_body).hexdigest() == manifest['source_sha256']
    with (CASE / 'approved-285.csv').open() as stream:
        expected = {(r['display_name'], r['course']) for r in csv.DictReader(stream)}
    assert len(expected) == 285 and ('CINDY MALIKA GALANA', 'Welding') in expected
    if environment == 'production':
        demo = json.loads((CASE / 'demo/verification.json').read_text())
        assert demo['source_sha256'] == manifest['source_sha256'] and demo['rows'] == 285
    spec = importlib.util.spec_from_file_location('panel_release', ROOT / 'scripts/deployment/releases/deploy-reception-graduands-2026.10.15.1.py')
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    panel = module.Panel()
    public = '/home/bfyigiln/' + ('demo.toolkitafrica.ac.ke' if environment == 'demo' else 'public_html')
    # The correction changes options only. Verify the deployed validation code before using it.
    assert panel.fetch(public + '/wp-content/themes/eduma-child/inc/graduands.php') == (ROOT / 'wp-content/themes/eduma-child/inc/graduands.php').read_bytes()
    helper = (ROOT / 'scripts/deployment/audit/publish-graduation-2026.10.21.php').read_text()
    assert helper.count('!== 294') == 2 and helper.count('20261021.json') == 2
    helper = helper.replace('!== 294', '!== 285').replace('20261021.json', '20261021-amendment-nine.json')
    helper = helper.replace('__APPROVED_CSV_BASE64__', base64.b64encode(csv_body).decode()).replace('__APPROVED_CSV_SHA256__', manifest['source_sha256'])
    anchor = '        $before = array();'
    assert helper.count(anchor) == 1
    baseline_revision = manifest['expected_baseline_revision']
    assert len(baseline_revision) == 64 and all(c in '0123456789abcdef' for c in baseline_revision)
    guard = "        $current = toolkit_graduands_published(false);\n        if (hash('sha256', wp_json_encode($current['rows'] ?? array())) !== '" + baseline_revision + "') throw new RuntimeException('Published list changed since review; do not overwrite');\n"
    helper = helper.replace(anchor, guard + anchor, 1)
    out = CASE / environment
    out.mkdir(parents=True, exist_ok=True, mode=0o700)
    (out / 'compiled-import.private.php').write_text(helper)
    nonce = str(time.time_ns())
    remote = PRIVATE + '/graduands-amend-' + nonce + '.php'
    log = PRIVATE + '/graduands-amend-' + nonce + '.log'
    panel.upload(remote, helper.encode())
    command = f'/usr/local/bin/ea-php84 {remote} {environment} > {log} 2>&1'
    added = panel.api2call('Cron', 'add_line', command=command, minute='*', hour='*', day='*', month='*', weekday='*')
    assert added.get('event', {}).get('result') == 1
    try:
        for _ in range(24):
            raw = panel.fetch(log, optional=True)
            if raw:
                if b'GRADUATION_PUBLISH_FAILED' in raw:
                    raise RuntimeError('Private amendment failed; inspect before retrying')
                if b'GRADUATION_PUBLISH_OK' in raw:
                    result = next(json.loads(line) for line in raw.decode().splitlines() if line.startswith('{"environment":'))
                    assert result['rows'] == 285 and result['source_sha256'] == manifest['source_sha256']
                    break
            time.sleep(5)
        else:
            raise RuntimeError('Amendment timeout; inspect before retrying')
    finally:
        for row in panel.api2call('Cron', 'listcron').get('data', []):
            if row.get('command') == command:
                removed = panel.api2call('Cron', 'remove_line', line=row['count'])
                assert removed.get('event', {}).get('result') == 1
    assert panel.fetch(remote, optional=True) is None, 'Temporary importer was not removed'
    rollback = panel.fetch(PRIVATE + '/graduation-before-' + environment + '-20261021-amendment-nine.json')
    dest = out / 'before.private.json'
    dest.write_bytes(rollback)
    dest.chmod(0o600)
    base = 'https://' + ('demo.' if environment == 'demo' else '') + 'toolkitafrica.ac.ke'
    response = requests.get(base + '/graduation/?roster_revision=' + manifest['source_sha256'][:12], timeout=35)
    response.raise_for_status()
    soup = BeautifulSoup(response.text, 'html.parser')
    rows = soup.select('[data-graduand]')
    actual = {(row['data-name'], row['data-course']) for row in rows}
    assert len(rows) == 285 and actual == expected
    assert not ({(row['display_name'], row['course']) for row in manifest['removals']} & actual)
    assert sum(row['data-name'] == 'CINDY MALIKA GALANA' for row in rows) == 1
    assert len(soup.select('[data-graduands-course] option')) == 6
    canonical = requests.get(base + '/graduation/', timeout=35)
    canonical.raise_for_status()
    canonical_soup = BeautifulSoup(canonical.text, 'html.parser')
    canonical_rows = canonical_soup.select('[data-graduand]')
    assert len(canonical_rows) == 285 and {(row['data-name'], row['data-course']) for row in canonical_rows} == expected, 'Canonical QR destination is stale or changed'
    (out / 'graduation-canonical.html').write_text(canonical.text)
    result.update({'http': response.status_code, 'canonical_http': canonical.status_code, 'all_public_pairs_verified': True, 'removals_verified': 9, 'cindy_occurrences': 1, 'theme_files_changed': 0, 'live_test_applications': 0})
    (out / 'graduation.html').write_text(response.text)
    (out / 'verification.json').write_text(json.dumps(result, indent=2) + '\n')
    print('VERIFIED', environment, '285 names, nine removals absent, Cindy present once, all source pairs match', flush=True)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('environment', choices=['demo', 'production'])
    args = parser.parse_args()
    try:
        run(args.environment)
    except Exception as error:
        print('Amendment stopped:', type(error).__name__, '; inspect private evidence before retry', flush=True)
        raise SystemExit(1)
