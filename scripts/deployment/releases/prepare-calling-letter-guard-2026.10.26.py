#!/usr/bin/env python3
"""Reconcile the confirmed missing private-file guard; keep original release receipts."""
import argparse
import importlib.util
import json
import subprocess
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]


def load(name, path):
    spec = importlib.util.spec_from_file_location(name, path)
    module = importlib.util.module_from_spec(spec); spec.loader.exec_module(module)
    return module


def run(environment):
    release = load('release', Path(__file__).with_name('deploy-calling-letters-2026.10.26.py'))
    case = release.CASE
    original = json.loads((case / 'deployment' / environment / 'attempt.json').read_text())
    assert original['cron_removed'] and len(original['uploaded']) == 13
    output = case / (environment + '-guard-preparation')
    if output.exists(): raise RuntimeError('Existing reconciliation preparation; inspect saved state, never replay')
    output.mkdir(mode=0o700)
    state = {'environment': environment, 'prepared': False, 'deployed': False, 'files': []}
    journal = output / 'manifest.json'
    def save(): journal.write_text(json.dumps(state, indent=2) + '\n')
    save()
    panel = load('connection', Path(__file__).with_name('finish-runtime-cleanup-2026.10.26.py')).connection()
    policy = load('policy', ROOT / 'scripts/deployment/runtime_payload.py')
    rows = list(original['files'])
    prefix = next(r['remote'].rsplit('/', 1)[0] for r in rows if r['file'] == 'inc/reception-calling-letters.php')
    rows.append({'component':'wordpress','file':'inc/private-file-boundary.php','remote':prefix + '/private-file-boundary.php'})
    guard = (ROOT / 'wp-content/themes/eduma-child/inc/private-file-boundary.php').read_bytes()
    api = (ROOT / 'wp-content/themes/eduma-child/inc/reception-calling-letters.php').read_bytes()
    for row in rows:
        name = row['file']; raw = panel.fetch(row['remote'], optional=name == 'inc/private-file-boundary.php')
        if name == 'inc/private-file-boundary.php':
            assert raw is None or raw == guard, 'Unexpected existing private-file dependency'
            payload = guard
        else:
            assert raw is not None and release.sha(raw) == row['sha256'], 'Original deployment has drifted'
            payload = raw
            if row['component'] == 'wordpress' and name == 'inc/reception-calling-letters.php':
                addition = b"require_once __DIR__ . '/private-file-boundary.php';\n\n"
                anchor = b'function toolkit_reception_letters_enabled() {'
                assert raw.count(anchor) == 1 and addition not in raw
                payload = raw.replace(anchor, addition + anchor, 1)
                assert payload == api, 'Unexpected source changes beyond the dependency include'
        policy.assert_runtime_upload(row['remote'])
        for folder, data in [('baseline', raw), ('payload', payload)]:
            if data is not None:
                path = output / folder / row['component'] / name
                path.parent.mkdir(parents=True, exist_ok=True); path.write_bytes(data); path.chmod(0o600)
        subprocess.run(['php', '-l', str(output / 'payload' / row['component'] / name)], check=True, stdout=subprocess.DEVNULL)
        state['files'].append({'component':row['component'],'file':name,'remote':row['remote'],
                               'baseline_sha256':None if raw is None else release.sha(raw),'sha256':release.sha(payload)})
        save()
    state['prepared'] = True; save()
    print(environment, '14 fresh allowlisted/linted runtime files; only missing guard and explicit include may change; no uploads', flush=True)


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__); parser.add_argument('environment', choices=['demo','production']); args=parser.parse_args()
    try: run(args.environment)
    except Exception as error:
        print('STOPPED:', str(error) if isinstance(error, RuntimeError) else type(error).__name__, '— inspect saved state before retry', flush=True)
        raise SystemExit(1)
