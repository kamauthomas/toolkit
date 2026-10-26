#!/usr/bin/env python3
"""Isolated scheduler selection/idempotency checks; no cPanel connection."""
import importlib.util
from pathlib import Path

script = Path(__file__).resolve().parents[1] / 'deployment/releases/deploy-admin-sync-2026.10.26.1.py'
spec = importlib.util.spec_from_file_location('release', script)
release = importlib.util.module_from_spec(spec); spec.loader.exec_module(release)
app = '/home/bfyigiln/reception-system'
assert release.matching_scheduler('/usr/local/bin/ea-php84 ' + app + '/artisan schedule:run > /dev/null 2>&1', app)
assert release.matching_scheduler('cd ' + app + ' && /usr/local/bin/ea-php84 artisan schedule:run > /dev/null 2>&1', app)
assert not release.matching_scheduler('cd ' + app + '-demo && php artisan schedule:run', app)
assert not release.matching_scheduler('echo "cd ' + app + ' && php artisan schedule:run"', app)
assert not release.matching_scheduler("invalid ' quote", app)


class Cron:
    def __init__(self, rows): self.rows = rows; self.added = 0
    def api2call(self, module, function, **data):
        assert module == 'Cron'
        if function == 'add_line': self.added += 1; self.rows.append(data)
        else: assert function == 'listcron'
        return {'event': {'result': 1}, 'data': self.rows}


cadence = dict(minute='*', hour='*', day='*', month='*', weekday='*')
existing = Cron([{**cadence, 'command': 'cd ' + app + ' && php artisan schedule:run'}])
assert release.ensure_schedule(existing, app).startswith('Existing') and existing.added == 0
missing = Cron([])
assert release.ensure_schedule(missing, app).startswith('Dedicated') and missing.added == 1
assert release.ensure_schedule(missing, app).startswith('Dedicated') and missing.added == 1
assert missing.rows[0]['minute'] == '*/5' and 'reception:sync-website-applications' in missing.rows[0]['command']
wrong_app = Cron([{**cadence, 'command': 'cd ' + app + '-demo && php artisan schedule:run'}])
release.ensure_schedule(wrong_app, app); assert wrong_app.added == 1
wrong_cadence = Cron([{**missing.rows[0], 'minute': '0'}])
try: release.ensure_schedule(wrong_cadence, app)
except AssertionError: pass
else: raise AssertionError('Wrong existing cadence silently accepted')
print('PASS: 11 isolated scheduler selection, scope, idempotency and cadence checks; no network action.')
