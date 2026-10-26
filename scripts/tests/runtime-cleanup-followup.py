#!/usr/bin/env python3
"""Verify exact cleanup targets, failure stops and idempotent index verification offline."""
import copy
import importlib.util
import json
import tempfile
from pathlib import Path

script = Path(__file__).resolve().parents[1] / 'deployment/releases/finish-runtime-cleanup-2026.10.26.py'
spec = importlib.util.spec_from_file_location('followup', script)
release = importlib.util.module_from_spec(spec); spec.loader.exec_module(release)
source = release.SOURCE; dest = release.DEST
rows = [{'file': source + '/removed-files/demo/README.md', 'type': 'file', 'size': 21, 'mtime': 9}]
backup = {'file': Path(release.BACKUP).name, 'type': 'file', 'size': 7, 'mtime': 8, 'mode': 33152, 'exists': 1}
loader = b'synthetic translation loader'
plan = {'recovery_regular_files': 1, 'recovery_bytes': 21, 'backup_file': backup,
        'translation_loader_sha256': release.sha(loader), 'recovery_source_mode': 0o40755}


class Panel:
    def __init__(self, mode):
        self.mode = mode; self.calls = []; self.moved = False; self.trashed = False; self.restricted = False

    def fetch(self, path, optional=False):
        assert path == release.PLUGIN + 'init.php'
        return loader

    def api2call(self, module, function, **kwargs):
        self.calls.append((module, function, kwargs))
        if function == 'listcron':
            return {'event': {'result': 1}, 'data': [{'command': source + '/old-helper.php'}] if self.mode == 'active_job' else []}
        assert function == 'fileop' and kwargs['doubledecode'] == 0
        if kwargs['op'] == 'chmod':
            assert kwargs['sourcefiles'] == source[len(release.HOME):] and kwargs['metadata'] == '0700'
            if self.mode == 'chmod_failure': return {'event': {'result': 1}, 'data': [{'result': 0}]}
            self.restricted = True
        elif kwargs['op'] == 'move':
            assert self.restricted
            assert kwargs['sourcefiles'] == source[len(release.HOME):]
            assert kwargs['destfiles'] == dest and kwargs['destfiles'].startswith('/')
            if self.mode == 'move_failure': return {'event': {'result': 1}, 'data': [{'result': 0}]}
            self.moved = True
        else:
            assert kwargs['op'] == 'trash' and self.moved
            assert kwargs['sourcefiles'] == release.BACKUP[len(release.HOME):]
            self.trashed = True
        return {'event': {'result': 1}, 'data': [{'result': 1, 'dest': dest}]}


with tempfile.TemporaryDirectory(prefix='toolkit-followup-tests-') as directory:
    for mode in ['success', 'parent_755', 'stale_source', 'existing_destination', 'writable_parent', 'active_job', 'chmod_failure', 'move_failure', 'move_mismatch', 'changed_backup']:
        release.CASE = Path(directory) / mode; release.CASE.mkdir()
        release.save('followup-plan.json', plan)
        release.save('recovery-before.private.json', rows)
        panel = Panel(mode)
        release.connection = lambda: panel
        def search(unused, path):
            result = copy.deepcopy(rows)
            if path == dest:
                for row in result: row['file'] = dest + row['file'][len(source):]
                if mode == 'move_mismatch': result[0]['size'] += 1
            elif mode == 'stale_source': result[0]['mtime'] += 1
            return result
        release.search = search
        def stat(unused, parent, names):
            if parent == release.HOME.rstrip('/'):
                return [{'type': 'dir', 'mode': 0o40777 if mode == 'writable_parent' else (0o40755 if mode == 'parent_755' else 0o40700)}]
            if parent == release.PARENT:
                return [{'type': 'dir', 'mode': 0o40700, 'exists': 1}] if panel.moved or mode == 'existing_destination' else []
            if parent == str(Path(source).parent): return [] if panel.moved else [{'exists': 1, 'type': 'dir', 'mode': 0o40700 if panel.restricted else 0o40755}]
            assert parent == release.PLUGIN + 'languages'
            item = copy.deepcopy(backup)
            if mode == 'changed_backup': item['mtime'] += 1
            return [] if panel.trashed else [item]
        release.stat = stat
        release.normal_page = lambda url: {'url': url, 'status': 200}
        try: release.cleanup(True)
        except AssertionError:
            assert mode not in ['success', 'parent_755']
            assert not panel.trashed
            journal = release.CASE / 'additional-cleanup.json'
            if journal.exists(): assert not json.loads(journal.read_text())['verified']
        else:
            assert mode in ['success', 'parent_755'] and panel.trashed and panel.restricted
            assert json.loads((release.CASE / 'additional-cleanup.json').read_text())['verified']
        if mode in ['stale_source', 'existing_destination', 'writable_parent', 'active_job']:
            assert not any(call[1] == 'fileop' for call in panel.calls)

    # An already corrected index verifies without an upload; fresh drift prevents mutation.
    for mode in ['already_applied', 'index_drift']:
        release.CASE = Path(directory) / mode; release.CASE.mkdir()
        current = b'corrected synthetic production front controller'; demo = b'corrected synthetic demo front controller'
        vendor = b'unchanged synthetic vendor metadata'
        release.save('followup-plan.json', {'demo_index_sha256': release.sha(demo),
                     'index_before_sha256': release.sha(current), 'index_after_sha256': release.sha(current),
                     'vendor_sha256': release.sha(vendor)})
        (release.CASE / 'index-runtime.php').write_bytes(current)
        class IndexPanel:
            uploaded = False
            def fetch(self, path, optional=False):
                if 'reception-public-demo' in path: return demo
                if path == release.INDEX: return current if mode == 'already_applied' else b'changed'
                if 'installed.json' in path: return vendor
                assert optional; return None
            def upload(self, *args): self.uploaded = True; raise AssertionError('Unnecessary upload')
        panel = IndexPanel(); release.connection = lambda: panel
        try: release.verify_index(True)
        except AssertionError: assert mode == 'index_drift'
        else:
            assert mode == 'already_applied'
            assert json.loads((release.CASE / 'index-verification.json').read_text())['uploaded_files'] == 0
        assert not panel.uploaded

    for mode in ['reconcile_success', 'unknown_receipt', 'stale_intermediate', 'duplicate_reconciliation', 'unexpected_destination']:
        release.CASE = Path(directory) / mode; release.CASE.mkdir()
        release.save('followup-plan.json', plan); release.save('recovery-before.private.json', rows)
        state = {'verified': False, 'recovery_mode_verified': '0700',
                 'move_receipt': {'event': {'result': 1}, 'data': [{'result': 1, 'src': source, 'dest': release.INTERMEDIATE}]}}
        if mode == 'unknown_receipt': state['move_receipt']['data'][0]['dest'] = '/unknown'
        if mode == 'duplicate_reconciliation': state['relocation_reconciliation'] = {'status': 'uncertain'}
        release.save('additional-cleanup.json', state)
        release.save('relocation-reconciliation-preflight.private.json', {'metadata_equal': True})
        calls = []; finished = []
        class ReconcilePanel:
            def api2call(self, module, function, **kwargs):
                if function == 'listcron': return {'event': {'result': 1}, 'data': []}
                assert function == 'fileop' and kwargs['op'] == 'move'
                assert kwargs['sourcefiles'] == release.INTERMEDIATE[len(release.HOME):]
                assert kwargs['destfiles'] == dest and kwargs['destfiles'].startswith('/')
                calls.append(kwargs)
                return {'event': {'result': 1}, 'data': [{'result': 1, 'dest': '/unexpected' if mode == 'unexpected_destination' else dest}]}
        panel = ReconcilePanel(); release.connection = lambda: panel
        def reconcile_search(unused, path):
            assert path == release.INTERMEDIATE
            result = copy.deepcopy(rows)
            for row in result: row['file'] = release.INTERMEDIATE + row['file'][len(source):]
            if mode == 'stale_intermediate': result[0]['mtime'] += 1
            return result
        release.search = reconcile_search
        def reconcile_stat(unused, parent, names):
            if parent == release.HOME.rstrip('/'): return [{'type': 'dir', 'mode': 0o40755}]
            if names == [Path(release.INTERMEDIATE).name]: return [{'type': 'dir', 'mode': 0o40700, 'exists': 1}]
            return []
        release.stat = reconcile_stat
        release.finish_cleanup = lambda *args: finished.append(args)
        try: release.reconcile_relocation(True)
        except AssertionError:
            assert mode != 'reconcile_success' and not finished
            if mode != 'unexpected_destination': assert not calls
        else:
            assert mode == 'reconcile_success' and len(calls) == 1 and len(finished) == 1
            assert finished[0][-1] == release.INTERMEDIATE

print('PASS: 10 cleanup, 2 existing-index/drift and 5 narrowly gated reconciliation scenarios; no live requests.')
