#!/usr/bin/env python3
"""Publish the approved November welding costs using fresh, anchored runtime patches."""
import argparse
import fcntl
import hashlib
import importlib.util
import json
from pathlib import Path
import re
import subprocess
import tempfile
import time

import requests
from bs4 import BeautifulSoup

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/approved-welding-costs-2026-11-13'
BASE = '66a2971'
PREFIX = 'wp-content/themes/eduma-child/'
FILES = ['inc/course-catalog.php', 'template-parts/pages/course-detail.php',
         'template-parts/pages/courses.php', 'inc/site-metrics.php', 'functions.php']
RELEASE = '2026.11.13.1'
OLD = ['KES 127,895', 'KES 55,017', 'KES 34,375', 'KES 39,575', 'KES 39,315', 'KES 44,515']


def module(name, path):
    spec = importlib.util.spec_from_file_location(name, path)
    value = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(value)
    return value


def digest(raw):
    return hashlib.sha256(raw).hexdigest()


def save(path, value):
    temporary = path.with_suffix('.pending')
    temporary.write_text(json.dumps(value, indent=2) + '\n')
    temporary.chmod(0o600)
    temporary.replace(path)


def remote(env, name):
    assert env in ('demo', 'production') and name in FILES
    site = 'demo.toolkitafrica.ac.ke' if env == 'demo' else 'public_html'
    path = '/home/bfyigiln/' + site + '/' + PREFIX + name
    module('runtime_policy', ROOT / 'scripts/deployment/runtime_payload.py').assert_runtime_upload(path)
    return path


def connection():
    # Existing provisioned credentials are read privately; no browser/login fields.
    # This adapter paces requests and stops at any timeout, refusal or rate limit.
    return module('seo_transport', Path(__file__).with_name('deploy-seo-discovery-2026.10.27.py')).connection()


def prepare(env, source):
    assert re.fullmatch('[a-f0-9]{40}', source)
    assert subprocess.check_output(['git', 'diff', source, '--', *[PREFIX + f for f in FILES]], cwd=ROOT) == b''
    out = CASE / env
    out.mkdir(mode=0o700, parents=True, exist_ok=True)
    journal = out / 'preparation.json'
    assert not journal.exists(), 'Preparation exists; inspect it instead of replacing evidence'
    state = dict(environment=env, source=source, prepared=False, files=[])
    save(journal, state)
    panel = connection()
    with tempfile.TemporaryDirectory(prefix='toolkit-approved-costs-') as tmp:
        stage = Path(tmp)
        for name in FILES:
            raw = panel.fetch(remote(env, name))
            target = stage / PREFIX / name
            target.parent.mkdir(parents=True, exist_ok=True)
            target.write_bytes(raw)
            before = out / 'before' / name
            before.parent.mkdir(parents=True, exist_ok=True)
            before.write_bytes(raw)
            before.chmod(0o600)
            state['files'].append(dict(file=name, baseline_sha256=digest(raw)))
            save(journal, state)
        patch = subprocess.check_output(['git', 'diff', '--no-ext-diff', BASE, source, '--', *[PREFIX + f for f in FILES]], cwd=ROOT)
        assert patch, 'Empty runtime change'
        result = subprocess.run(['patch', '--batch', '--fuzz=0', '-p1', '-d', str(stage)], input=patch, capture_output=True)
        assert result.returncode == 0, 'Fetched code drift: inspect private baselines'
        assert {str(p.relative_to(stage)) for p in stage.rglob('*') if p.is_file()} == {PREFIX + f for f in FILES}
        for row in state['files']:
            payload = (stage / PREFIX / row['file']).read_bytes()
            dest = out / 'payload' / row['file']
            dest.parent.mkdir(parents=True, exist_ok=True)
            dest.write_bytes(payload)
            dest.chmod(0o600)
            subprocess.run(['php', '-l', str(dest)], check=True, stdout=subprocess.DEVNULL)
            row['sha256'] = digest(payload)
    state['prepared'] = True
    save(journal, state)
    print('PREPARED', env, 'five allowlisted/linted files; no uploads', flush=True)


def public_check(env, out):
    base = 'https://' + ('demo.' if env == 'demo' else '') + 'toolkitafrica.ac.ke'
    checks = []
    routes = [('/our-ventures/', ['KES 176,276', 'KES 66,300', 'KES 120,962']),
              ('/courses/advanced-welding-vr/', ['KES 176,276', '3 months']),
              ('/courses/advanced-welding-upskilling/', ['KES 66,300', '1 month']),
              ('/our-ventures/construction-sector-skills/', ['KES 176,276', 'KES 66,300']),
              ('/llms.txt', ['KES 176,276', 'KES 66,300']),
              ('/our-ventures/construction-sector-skills/recognition-of-prior-learning-rpl/', ['Contact admissions for current fees'])]
    # Warm the release hook once, then verify canonical cached pages normally.
    for index, (path, expected) in enumerate([(routes[0][0] + '?training_costs=' + RELEASE, routes[0][1])] + routes):
        time.sleep(5)
        response = requests.get(base + path, timeout=(10, 40), allow_redirects=False)
        assert response.status_code == 200 and not response.headers.get('Retry-After'), 'Public read refused; stop'
        assert response.headers.get('X-Toolkit-Release') == RELEASE, 'Cached/incorrect release; inspect before retry'
        html = response.text
        soup = BeautifulSoup(html, 'html.parser')
        main = soup.find('main')
        text = re.sub(r'\s+', ' ', (main or soup).get_text(' ', strip=True)) if path != '/llms.txt' else html
        assert all(value in text for value in expected), 'Published cost/source missing'
        assert not any(value in text for value in OLD), 'Superseded welding cost remains'
        assert not re.search(r'fatal error|critical error|uncaught error', html, re.I)
        if 'recognition-of-prior-learning-rpl' in path:
            assert not re.search(r'KES\s+[0-9]', text), 'Price leaked onto RPL'
        if '/courses/advanced-welding-' in path:
            assert 'Youth Skills Manager' in text and 'Approved training cost' in text
            assert '10 November 2026' not in text and 'Principal' not in text
            assert 'revised September 2026' not in text and 'All five upskilling categories' not in text
        dest = out / ('public-' + str(index) + '.html')
        dest.write_text(html)
        dest.chmod(0o600)
        checks.append(dict(path=path, status=200, sha256=digest(response.content)))
    return checks


def deploy(env, verify_only=False):
    out = CASE / env
    plan = json.loads((out / 'preparation.json').read_text())
    assert plan['prepared'] and plan['environment'] == env
    rows = plan['files']
    assert [row['file'] for row in rows] == FILES
    for row in rows:
        remote(env, row['file'])
        assert digest((out / 'payload' / row['file']).read_bytes()) == row['sha256']
    if env == 'production':
        demo = json.loads((CASE / 'demo/verification.json').read_text())
        assert demo['verified'] and demo['source'] == plan['source']
        assert [(r['file'], r['sha256']) for r in demo['files']] == [(r['file'], r['sha256']) for r in rows], 'Review environment payload difference'
    journal = out / 'attempt.json'
    if verify_only:
        state = json.loads(journal.read_text())
        assert state['files'] == rows and state['source'] == plan['source']
    else:
        assert not journal.exists(), 'Existing attempt: reconcile intent; never replay uncertain writes'
        state = dict(environment=env, source=plan['source'], files=rows, uploaded=[], verified=False)
        save(journal, state)
    panel = connection()
    for row in rows:
        actual = digest(panel.fetch(remote(env, row['file'])))
        expected = row['sha256'] if verify_only else row['baseline_sha256']
        assert actual == expected, 'Fresh runtime drift; no upload'
    if not verify_only:
        for row in rows:  # functions.php activates the release last.
            name = row['file']
            assert digest(panel.fetch(remote(env, name))) == row['baseline_sha256'], 'Concurrent edit; stop'
            state['upload_intent'] = name
            save(journal, state)
            raw = (out / 'payload' / name).read_bytes()
            panel.upload(remote(env, name), raw)
            assert panel.fetch(remote(env, name)) == raw, 'Upload not verified; reconcile saved intent'
            state['uploaded'].append(state.pop('upload_intent'))
            save(journal, state)
            print('BYTE-VERIFIED', env, name, flush=True)
    state['public_checks'] = public_check(env, out)
    state['verified'] = True
    save(journal, state)
    save(out / 'verification.json', state)
    print('VERIFIED', env, 'five exact runtime files and seven normal public reads; no submissions', flush=True)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('environment', choices=['demo', 'production'])
    mode = parser.add_mutually_exclusive_group(required=True)
    mode.add_argument('--prepare', metavar='FULL_COMMIT')
    mode.add_argument('--deploy', action='store_true')
    mode.add_argument('--verify-only', action='store_true')
    args = parser.parse_args()
    CASE.mkdir(mode=0o700, parents=True, exist_ok=True)
    lock = (CASE / 'operation.lock').open('a')
    (CASE / 'operation.lock').chmod(0o600)
    try:
        fcntl.flock(lock, fcntl.LOCK_EX | fcntl.LOCK_NB)
        if args.prepare:
            prepare(args.environment, args.prepare)
        else:
            deploy(args.environment, args.verify_only)
    except Exception as error:
        print('STOPPED:', type(error).__name__, '(inspect private state; no automatic retry)', flush=True)
        raise SystemExit(1)
