#!/usr/bin/env python3
"""Demo-first focused Mzizi relay/cache release; no live application submissions."""
import argparse, hashlib, importlib.util, json, re, subprocess, time
from pathlib import Path
import requests
ROOT = Path(__file__).resolve().parents[3]
RELEASE = '2026.10.17.1'
EVIDENCE = ROOT / 'reports/mzizi-relay-2026-10-17'
MODULES = ['inc/application-relay-queue.php', 'inc/application-catalog-cache.php']
PRIVATE = '/home/bfyigiln/reception-system/storage/app/private'
WORKER = PRIVATE + '/mzizi-relay-worker.php'


def panel():
    spec = importlib.util.spec_from_file_location('existing_release', ROOT / 'scripts/deployment/releases/deploy-reception-graduands-2026.10.15.1.py')
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module.Panel()


def inspect_worker(p, environment, out):
    # Inspection migrates additive columns and checks hook wiring; it never runs the relay queue.
    log = PRIVATE + '/mzizi-inspect-' + environment + '-' + str(time.time_ns()) + '.log'
    command = f'/usr/local/bin/ea-php84 {WORKER} {environment} inspect > {log} 2>&1'
    added = p.api2call('Cron', 'add_line', command=command, minute='*', hour='*', day='*', month='*', weekday='*')
    if added.get('event', {}).get('result') != 1:
        raise RuntimeError('Cannot schedule private inspection')
    try:
        for _ in range(24):
            raw = p.fetch(log, optional=True)
            if raw:
                if b'MZIZI_WORKER_FAILED' in raw:
                    raise RuntimeError('Private worker inspection failed')
                if b'MZIZI_WORKER_OK' in raw:
                    result = next(json.loads(line) for line in raw.decode().splitlines() if line.startswith('{"environment":'))
                    assert result['schema'] and result['queue_hook'] and result['release'] == RELEASE
                    (out / 'inspection.json').write_text(json.dumps(result, indent=2) + '\n')
                    return result
            time.sleep(5)
        raise RuntimeError('Private worker inspection timeout; inspect before retrying')
    finally:
        for row in p.api2call('Cron', 'listcron').get('data', []):
            if row.get('command') == command:
                removed = p.api2call('Cron', 'remove_line', line=row['count'])
                assert removed.get('event', {}).get('result') == 1


def run(environment, socket, verify_only=False, refresh_demo=False):
    out = EVIDENCE / 'deployment' / environment
    if environment == 'production':
        assert (EVIDENCE / 'deployment/demo/verification.json').exists(), 'Verified demo required'
    out.mkdir(parents=True, exist_ok=True, mode=0o700)
    root = '/home/bfyigiln/' + ('demo.toolkitafrica.ac.ke' if environment == 'demo' else 'public_html')
    remote = root + '/wp-content/themes/eduma-child/'
    base = 'https://' + ('demo.' if environment == 'demo' else '') + 'toolkitafrica.ac.ke'
    source = ROOT / 'wp-content/themes/eduma-child'
    p = panel()
    payload = out / 'payload'
    existing = ['inc/application-adapter.php', 'functions.php']
    if refresh_demo:
        assert environment == 'demo' and not verify_only, 'Refresh applies only to verified demo'
        prior = json.loads((out / 'verification.json').read_text())
        for row in prior['files']:
            assert hashlib.sha256(p.fetch(remote + row['file'])).hexdigest() == row['sha256'], 'Verified demo drifted'
    if not verify_only:
        if (out / 'upload-started.json').exists() and not refresh_demo:
            raise RuntimeError('Partial or completed upload exists; use verify-only after reviewing state')
        originals = {}
        for file in existing:
            raw = p.fetch(remote + file)
            originals[file] = raw
            dest = out / 'rollback' / file
            dest.parent.mkdir(parents=True, exist_ok=True)
            if not refresh_demo:
                dest.write_bytes(raw)
                dest.chmod(0o600)
            baseline = dest.read_bytes() if refresh_demo else raw
            dest = payload / 'wp-content/themes/eduma-child' / file
            dest.parent.mkdir(parents=True, exist_ok=True)
            dest.write_bytes(baseline)
        subprocess.run(['patch', '--batch', '--fuzz=0', '-p1', '-d', str(payload), '-i', str(ROOT / 'scripts/deployment/releases/patches/mzizi-relay-2026.10.17.1.patch')], check=True)
        staged = payload / 'wp-content/themes/eduma-child'
        f = staged / 'functions.php'
        before = f.read_text()
        expected_release = '2026.10.16.3' if environment == 'demo' else '2026.10.16.2'
        anchor = "return '" + expected_release + "';"
        assert before.count(anchor) == 1, 'Unexpected remote release baseline'
        f.write_text(before.replace(anchor, "return '" + RELEASE + "';", 1))
        for file in MODULES:
            if not refresh_demo:
                assert p.fetch(remote + file, optional=True) is None, 'Unexpected existing new module'
            (staged / file).write_bytes((source / file).read_bytes())
        # Test the exact fetched-and-patched adapter, including its unchanged deployed phone dependencies.
        for file in ['inc/PhoneNumbers.php', 'inc/PhoneCountries.php']:
            (staged / file).write_bytes(p.fetch(remote + file))
        for file in existing + MODULES:
            subprocess.run(['php', '-l', str(staged / file)], check=True, stdout=subprocess.DEVNULL)
        result = subprocess.run(['php', str(ROOT / 'scripts/tests/mzizi-relay.php'), socket, str(staged / 'inc/application-adapter.php')], check=True, text=True, capture_output=True)
        (out / 'synthetic-tests.txt').write_text(result.stdout)
        print(environment, result.stdout.strip(), flush=True)
        if environment == 'production':
            demo = EVIDENCE / 'deployment/demo/payload/wp-content/themes/eduma-child'
            for file in ['inc/application-adapter.php'] + MODULES:
                assert (staged / file).read_bytes() == (demo / file).read_bytes(), 'Demo/production relay payload differs'
        for file in existing:
            assert p.fetch(remote + file) == originals[file], 'Live source changed during preflight'
        (out / 'upload-started.json').write_text(json.dumps({'utc': time.time(), 'environment': environment}))
        for file in MODULES + existing:
            p.upload(remote + file, (staged / file).read_bytes())
        worker = (ROOT / 'scripts/deployment/mzizi-relay-worker.php').read_bytes()
        prior_worker = p.fetch(WORKER, optional=True)
        if prior_worker is not None and prior_worker != worker:
            raise RuntimeError('Existing private worker differs; do not overwrite')
        if prior_worker is None:
            p.upload(WORKER, worker)
    staged = payload / 'wp-content/themes/eduma-child'
    verified = []
    for file in existing + MODULES:
        raw = p.fetch(remote + file)
        assert raw == (staged / file).read_bytes(), 'Post-upload byte mismatch'
        verified.append({'file': file, 'sha256': hashlib.sha256(raw).hexdigest()})
    assert p.fetch(WORKER) == (ROOT / 'scripts/deployment/mzizi-relay-worker.php').read_bytes()
    # One normal Apply page check, no test submission, payload or security probe.
    response = requests.get(base + '/apply/?toolkit_release_check=' + RELEASE, timeout=35)
    assert response.status_code == 200 and response.headers.get('X-Toolkit-Release') == RELEASE
    assert not re.search(r'fatal error|critical error|uncaught error', response.text, re.I)
    assert 'ver=' + RELEASE in response.text, 'Asset release marker missing'
    result = inspect_worker(p, environment, out)
    # Separate five-minute CLI job; retain existing general WordPress cron jobs unchanged.
    command = f'/usr/local/bin/ea-php84 {WORKER} {environment} > {PRIVATE}/mzizi-worker-{environment}.log 2>&1'
    rows = [row for row in p.api2call('Cron', 'listcron').get('data', []) if row.get('command') == command]
    if not rows:
        added = p.api2call('Cron', 'add_line', command=command, minute='*/5' if environment == 'production' else '2-59/5', hour='*', day='*', month='*', weekday='*')
        assert added.get('event', {}).get('result') == 1
    rows = [row for row in p.api2call('Cron', 'listcron').get('data', []) if row.get('command') == command]
    assert len(rows) == 1 and rows[0]['minute'] == ('*/5' if environment == 'production' else '2-59/5')
    record = {'environment': environment, 'release': RELEASE, 'files': verified, 'private_worker_verified': True, 'inspection': result, 'five_minute_job': True, 'http': response.status_code, 'live_test_applications': 0, 'historical_uncertain_applications_replayed': 0}
    (out / 'verification.json').write_text(json.dumps(record, indent=2) + '\n')
    print('VERIFIED', environment, RELEASE, 'runtime bytes, page, additive schema, worker wiring and five-minute cron; no live test application', flush=True)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('environment', choices=['demo', 'production'])
    parser.add_argument('--socket', required=True)
    parser.add_argument('--verify-only', action='store_true')
    parser.add_argument('--refresh-demo', action='store_true', help='Rebuild a verified demo from its preserved original baseline after confirming no server drift')
    args = parser.parse_args()
    try:
        run(args.environment, args.socket, args.verify_only, args.refresh_demo)
    except Exception as error:
        # API exceptions can carry the authenticated cPanel URL; never print it.
        print('Release stopped:', type(error).__name__, '; inspect private local evidence before resuming', flush=True)
        raise SystemExit(1)
