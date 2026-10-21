#!/usr/bin/env python3
"""Publish the supplied graduation programme and approved name/course roster, demo first."""
import argparse
import base64
import csv
import hashlib
import importlib.util
import json
import re
import subprocess
import time
from pathlib import Path

import requests
from bs4 import BeautifulSoup

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/graduation-2026-10-21'
RELEASE = '2026.10.21.1'
PRIVATE = '/home/bfyigiln/reception-system/storage/app/private'
EXISTING = ['template-parts/pages/graduation.php', 'template-parts/pages/notice-board.php', 'template-parts/graduation-announcement.php', 'assets/calendar/toolkit-graduation-2026.ics', 'functions.php']
CSS = 'assets/css/graduation-programme.css'


def import_roster(panel, environment, out):
    csv = (CASE / 'graduands-approved.csv').read_bytes()
    helper = (ROOT / 'scripts/deployment/audit/publish-graduation-2026.10.21.php').read_text()
    helper = helper.replace('__APPROVED_CSV_BASE64__', base64.b64encode(csv).decode()).replace('__APPROVED_CSV_SHA256__', hashlib.sha256(csv).hexdigest())
    nonce = str(time.time_ns())
    remote = PRIVATE + '/graduation-import-' + nonce + '.php'
    log = PRIVATE + '/graduation-import-' + nonce + '.log'
    panel.upload(remote, helper.encode())
    command = f'/usr/local/bin/ea-php84 {remote} {environment} > {log} 2>&1'
    added = panel.api2call('Cron', 'add_line', command=command, minute='*', hour='*', day='*', month='*', weekday='*')
    assert added.get('event', {}).get('result') == 1, 'Cannot schedule private import'
    try:
        for _ in range(24):
            raw = panel.fetch(log, optional=True)
            if raw:
                if b'GRADUATION_PUBLISH_FAILED' in raw:
                    raise RuntimeError('Private import failed; inspect rollback before retry')
                if b'GRADUATION_PUBLISH_OK' in raw:
                    result = next(json.loads(line) for line in raw.decode().splitlines() if line.startswith('{"environment":'))
                    assert result['rows'] == 294 and result['release'] == RELEASE
                    (out / 'roster-publication.json').write_text(json.dumps(result, indent=2) + '\n')
                    break
            time.sleep(5)
        else:
            raise RuntimeError('Import timeout; inspect state before retry')
    finally:
        for row in panel.api2call('Cron', 'listcron').get('data', []):
            if row.get('command') == command:
                removed = panel.api2call('Cron', 'remove_line', line=row['count'])
                assert removed.get('event', {}).get('result') == 1, 'Temporary import cron removal failed'
    before = panel.fetch(PRIVATE + '/graduation-before-' + environment + '-20261021.json')
    dest = out / 'roster-before.private.json'
    dest.write_bytes(before)
    dest.chmod(0o600)
    assert panel.fetch(remote, optional=True) is None, 'One-time importer did not remove itself'
    return result


def run(environment, verify_only=False, refresh_demo=False):
    if environment == 'production':
        assert (CASE / 'deployment/demo/verification.json').exists(), 'Verified demo required'
        assert (CASE / 'deployment/demo/browser-checks.json').exists(), 'Demo browser acceptance required'
        demo_verified = json.loads((CASE / 'deployment/demo/verification.json').read_text())
        assert hashlib.sha256((CASE / 'graduands-approved.csv').read_bytes()).hexdigest() == demo_verified['roster']['source_sha256'], 'Approved source changed after demo review'
    spec = importlib.util.spec_from_file_location('panel_release', ROOT / 'scripts/deployment/releases/deploy-reception-graduands-2026.10.15.1.py')
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    panel = module.Panel()
    out = CASE / 'deployment' / environment
    out.mkdir(parents=True, exist_ok=True, mode=0o700)
    source = ROOT / 'wp-content/themes/eduma-child'
    public = '/home/bfyigiln/' + ('demo.toolkitafrica.ac.ke' if environment == 'demo' else 'public_html')
    remote = public + '/wp-content/themes/eduma-child/'
    base = 'https://' + ('demo.' if environment == 'demo' else '') + 'toolkitafrica.ac.ke'
    payload = out / 'payload'
    staged = payload / 'wp-content/themes/eduma-child'
    if refresh_demo:
        assert environment == 'demo' and not verify_only, 'Only verified demo can be refined'
        prior = json.loads((out / 'verification.json').read_text())
        for row in prior['files']:
            assert hashlib.sha256(panel.fetch(remote + row['file'])).hexdigest() == row['sha256'], 'Verified demo source drifted'
    if not verify_only:
        assert refresh_demo or not (out / 'upload-started.json').exists(), 'Existing upload: inspect and use verify-only'
        for file in EXISTING:
            raw = panel.fetch(remote + file)
            if not refresh_demo:
                assert raw == (CASE / 'baseline' / environment / file).read_bytes(), 'Live source drift: ' + file
                dest = out / 'rollback' / file
                dest.parent.mkdir(parents=True, exist_ok=True)
                dest.write_bytes(raw)
            baseline = (out / 'rollback' / file).read_bytes() if refresh_demo else raw
            dest = staged / file
            dest.parent.mkdir(parents=True, exist_ok=True)
            dest.write_bytes(baseline)
        if not refresh_demo:
            assert panel.fetch(remote + CSS, optional=True) is None, 'Unexpected existing stylesheet'
        subprocess.run(['patch', '--batch', '--fuzz=0', '-p1', '-d', str(payload), '-i', str(ROOT / 'scripts/deployment/releases/patches/graduation-details-2026.10.21.1.patch')], check=True)
        for file in [CSS, 'assets/calendar/toolkit-graduation-2026.ics']:
            dest = staged / file
            dest.parent.mkdir(parents=True, exist_ok=True)
            dest.write_bytes((source / file).read_bytes())
        for file in EXISTING:
            if file.endswith('.php'):
                subprocess.run(['php', '-l', str(staged / file)], check=True, stdout=subprocess.DEVNULL)
        # Re-run existing authorization, approval and popup-frequency checks before mutation.
        subprocess.run(['php', str(ROOT / 'scripts/tests/graduands.php')], check=True)
        subprocess.run(['node', str(ROOT / 'scripts/tests/graduation-announcement.cjs')], check=True)
        if not refresh_demo:
            for file in EXISTING:
                assert panel.fetch(remote + file) == (out / 'rollback' / file).read_bytes(), 'Concurrent source change'
        if refresh_demo:
            for row in prior['files']:
                assert hashlib.sha256(panel.fetch(remote + row['file'])).hexdigest() == row['sha256'], 'Concurrent demo source change'
        if environment == 'production':
            for row in demo_verified['files']:
                assert hashlib.sha256((staged / row['file']).read_bytes()).hexdigest() == row['sha256'], 'Production payload differs from verified demo'
        (out / 'upload-started.json').write_text(json.dumps({'environment': environment, 'release': RELEASE}))
        for file in [CSS] + EXISTING:
            panel.upload(remote + file, (staged / file).read_bytes())
    hashes = []
    for file in EXISTING + [CSS]:
        raw = panel.fetch(remote + file)
        assert raw == (staged / file).read_bytes(), 'Uploaded bytes differ: ' + file
        hashes.append({'file': file, 'sha256': hashlib.sha256(raw).hexdigest()})
    roster = import_roster(panel, environment, out)
    checks = []
    for path in ['/graduation/', '/notice-board/', '/']:
        response = requests.get(base + path + '?graduation_check=' + RELEASE, timeout=40)
        assert response.status_code == 200 and response.headers.get('X-Toolkit-Release') == RELEASE
        assert not re.search(r'fatal error|critical error|uncaught error', response.text, re.I)
        soup = BeautifulSoup(response.text, 'html.parser')
        if path == '/graduation/':
            rows = soup.select('[data-graduand]')
            assert len(rows) == 294 and len(soup.select('#programme tbody tr')) == 9
            with (CASE / 'graduands-approved.csv').open() as stream:
                expected_rows = {(row['display_name'], row['course']) for row in csv.DictReader(stream)}
            assert {(row['data-name'], row['data-course']) for row in rows} == expected_rows, 'Rendered roster differs from approved source'
            assert len(soup.select('[data-graduands-course] option')) == 6
            assert '8:00' in soup.select_one('.graduation-date').get_text()
            assert 'PM' in soup.select('#programme tbody tr')[-2].get_text()
            assert 'graduation-programme.css?ver=' + RELEASE in response.text
        if path == '/notice-board/':
            card = soup.select_one('article[data-category="event"]')
            assert card and '22 October 2026' in card.get_text() and card.select_one('a[href$="#graduands"]')
        if path == '/':
            popup = soup.select_one('[data-graduation-announcement]')
            assert popup and 'Arrival from 8:00 AM' in popup.get_text()
        (out / ((path.strip('/') or 'home') + '.html')).write_text(response.text)
        checks.append({'path': path, 'http': response.status_code, 'release': response.headers.get('X-Toolkit-Release')})
    for file in [CSS, 'assets/calendar/toolkit-graduation-2026.ics']:
        response = requests.get(base + '/wp-content/themes/eduma-child/' + file + '?ver=' + RELEASE, timeout=30)
        response.raise_for_status()
        assert response.content == (staged / file).read_bytes(), 'Public asset bytes differ'
    result = {'environment': environment, 'release': RELEASE, 'files': hashes, 'roster': roster, 'checks': checks, 'raw_documents_uploaded': 0, 'private_columns_published': 0, 'live_test_applications': 0}
    (out / 'verification.json').write_text(json.dumps(result, indent=2) + '\n')
    print('VERIFIED', environment, RELEASE, '294 public names; 9 programme rows; notice, homepage, calendar and runtime bytes', flush=True)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('environment', choices=['demo', 'production'])
    parser.add_argument('--verify-only', action='store_true')
    parser.add_argument('--refresh-demo', action='store_true')
    args = parser.parse_args()
    try:
        run(args.environment, args.verify_only, args.refresh_demo)
    except Exception as error:
        # Network exceptions can contain authenticated cPanel URLs. Print no exception message.
        print('Release stopped:', type(error).__name__, '; inspect private local evidence before continuing', flush=True)
        raise SystemExit(1)
