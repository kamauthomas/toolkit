#!/usr/bin/env python3
"""Prepare a focused calling-letter demo payload from fresh live files; never deploy it."""
import argparse
import hashlib
import importlib.util
import json
import subprocess
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[3]
CASE = ROOT / 'reports/calling-letters-2026-10-26/demo-preparation'
COMPONENTS = {
    'wordpress': {'repo': ROOT, 'base': '00c42f8', 'source': '819ed94', 'prefix': 'wp-content/themes/eduma-child/',
                  'remote': '/home/bfyigiln/demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child/',
                  'existing': ['functions.php', 'inc/site-metrics.php', 'inc/calling-letter-pdf.php'],
                  'new': ['inc/reception-calling-letters.php']},
    'reception': {'repo': ROOT.parent / 'reception-system', 'base': 'b1d1a99', 'source': '47e017e', 'prefix': '',
                  'remote': '/home/bfyigiln/reception-system-demo/',
                  'existing': ['config/services.php', 'routes/web.php', 'resources/views/staff/website-applications/show.blade.php', 'resources/views/staff/applicants/show.blade.php'],
                  'new': ['app/Services/CallingLetterGateway.php', 'app/Models/CallingLetter.php', 'app/Http/Controllers/Staff/CallingLetterController.php',
                          'database/migrations/2026_10_26_120000_create_calling_letters_table.php', 'resources/views/staff/calling-letters/index.blade.php']},
}


def digest(data): return hashlib.sha256(data).hexdigest()


def load(name, path):
    spec = importlib.util.spec_from_file_location(name, path)
    module = importlib.util.module_from_spec(spec); spec.loader.exec_module(module)
    return module


def blob(config, revision, name):
    return subprocess.check_output(['git', '-C', str(config['repo']), 'show', revision + ':' + config['prefix'] + name])


def build_component(config, baselines, output):
    """Apply only the pinned feature diff, preserving other live-source bytes."""
    names = config['existing'] + config['new']
    assert set(baselines) == set(names)
    for name in config['existing']:
        assert isinstance(baselines[name], bytes)
        path = output / (config['prefix'] + name); path.parent.mkdir(parents=True, exist_ok=True)
        path.write_bytes(baselines[name])
    for name in config['new']:
        assert baselines[name] is None, 'Unexpected existing module file; reconcile before preparing'
    diff = subprocess.check_output(['git', '-C', str(config['repo']), 'diff', '--no-ext-diff', config['base'], config['source'], '--', *[config['prefix'] + n for n in names]])
    result = subprocess.run(['patch', '--batch', '--fuzz=0', '-p1', '-d', str(output)], input=diff, capture_output=True)
    if result.returncode: raise RuntimeError('Focused patch did not match fresh live code; source review required')
    actual = {str(path.relative_to(output)) for path in output.rglob('*') if path.is_file()}
    assert actual == {config['prefix'] + name for name in names}, 'Unexpected patch output'
    for name in config['new']:
        assert (output / (config['prefix'] + name)).read_bytes() == blob(config, config['source'], name)


def select_environment(environment):
    global CASE, COMPONENTS
    assert environment in {'demo', 'production'}
    if environment == 'production':
        CASE = ROOT / 'reports/calling-letters-2026-10-26/production-preparation'
        for config in COMPONENTS.values():
            config['remote'] = config['remote'].replace('/demo.toolkitafrica.ac.ke/', '/public_html/').replace('/reception-system-demo/', '/reception-system/')


def collect():
    CASE.mkdir(parents=True, exist_ok=True, mode=0o700)
    journal = CASE / 'fetch-attempt.json'
    if journal.exists(): raise RuntimeError('Existing baseline collection; inspect before repeating')
    panel = load('followup', Path(__file__).with_name('finish-runtime-cleanup-2026.10.26.py')).connection()
    state = {'read_only': True, 'fetched': [], 'application_uploads': 0}
    def save(): journal.write_text(json.dumps(state, indent=2) + '\n')
    save()
    for component, config in COMPONENTS.items():
        for name in config['existing'] + config['new']:
            raw = panel.fetch(config['remote'] + name, optional=name in config['new'])
            if raw is not None:
                path = CASE / 'baseline' / component / name; path.parent.mkdir(parents=True, exist_ok=True); path.write_bytes(raw); path.chmod(0o600)
            state['fetched'].append({'component': component, 'file': name, 'sha256': None if raw is None else digest(raw)})
            save()
        print('Fetched exact environment baselines:', component, flush=True)
    state['complete'] = True; save()


def prepare():
    state = json.loads((CASE / 'fetch-attempt.json').read_text()); assert state.get('complete') is True
    if (CASE / 'manifest.json').exists(): raise RuntimeError('Existing prepared package; inspect before replacing')
    baseline_rows = {(r['component'], r['file']): r for r in state['fetched']}
    policy = load('policy', ROOT / 'scripts/deployment/runtime_payload.py')
    rows = []
    for component, config in COMPONENTS.items():
        baselines = {}
        for name in config['existing'] + config['new']:
            row = baseline_rows[component, name]
            raw = None if row['sha256'] is None else (CASE / 'baseline' / component / name).read_bytes()
            assert (None if raw is None else digest(raw)) == row['sha256']
            baselines[name] = raw
        with tempfile.TemporaryDirectory(prefix='toolkit-letter-package-') as temporary:
            staging = Path(temporary); build_component(config, baselines, staging)
            for name in config['existing'] + config['new']:
                raw = (staging / (config['prefix'] + name)).read_bytes()
                if component == 'wordpress' and name == 'inc/calling-letter-pdf.php':
                    # Fresh production drift: retain the exact object-cache guard already
                    # reviewed in Git and exercised by the verified demo renderer.
                    legacy = b'$cached = @unserialize( (string) file_get_contents( $cache_file ) );'
                    guarded = b"$cached = @unserialize( (string) file_get_contents( $cache_file ), array( 'allowed_classes' => false ) );"
                    if legacy in raw:
                        assert raw.count(legacy) == 1 and guarded not in raw
                        raw = raw.replace(legacy, guarded, 1)
                    assert guarded in raw
                destination = CASE / 'payload' / component / name; destination.parent.mkdir(parents=True, exist_ok=True); destination.write_bytes(raw)
                policy.assert_runtime_upload(config['remote'] + name)
                subprocess.run(['php', '-l', str(destination)], check=True, stdout=subprocess.DEVNULL)
                rows.append({'component': component, 'file': name, 'remote': config['remote'] + name,
                             'sha256': digest(raw), 'baseline_sha256': baseline_rows[component, name]['sha256']})
    assert len(rows) == 13
    preflight = json.loads((ROOT / 'reports/calling-letters-2026-10-26/deployment-preflight/attempt.json').read_text())
    result = {'environment': 'production' if 'production' in CASE.name else 'demo', 'prepared': True, 'deployed': False, 'enabled': False, 'files': rows,
              'target_extension_preflight_passed': preflight['extension_preflight_passed'],
              'blocker': preflight.get('blocker'), 'activation_or_config_changes': 0,
              'release_gate': 'Fresh target platform success and demo verification required before production.'}
    (CASE / 'manifest.json').write_text(json.dumps(result, indent=2) + '\n')
    print('Prepared 13 linted, allowlisted runtime files from fresh demo baselines; uploaded 0; activation remains gated.')


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__); parser.add_argument('--fetch-demo', action='store_true'); parser.add_argument('--environment', choices=['demo','production'], default='demo'); args = parser.parse_args()
    try:
        select_environment(args.environment)
        if args.fetch_demo: collect()
        else: prepare()
    except Exception as error:
        print('STOPPED:', str(error) if isinstance(error, RuntimeError) else type(error).__name__, '— inspect private preparation state; no deployment was attempted')
        raise SystemExit(1)
