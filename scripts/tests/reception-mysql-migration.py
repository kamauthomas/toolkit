#!/usr/bin/env python3
"""Reproduce legacy TIMESTAMP defaults and test the migration on disposable MariaDB."""
import os
import shutil
import subprocess
import tempfile
import time
from pathlib import Path

APP = Path(__file__).resolve().parents[3] / 'reception-system'
temp = Path(tempfile.mkdtemp(prefix='toolkit-migration-db-', dir='/tmp'))
server = None
try:
    subprocess.run(['mariadb-install-db', '--no-defaults', '--datadir=' + str(temp / 'data'), '--auth-root-authentication-method=normal', '--skip-test-db'], check=True, stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    socket = str(temp / 'db.sock')
    server = subprocess.Popen(['mysqld', '--no-defaults', '--datadir=' + str(temp / 'data'), '--socket=' + socket, '--pid-file=' + str(temp / 'db.pid'), '--skip-networking', '--skip-grant-tables', '--explicit-defaults-for-timestamp=0', '--sql-mode=STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE', '--log-error=' + str(temp / 'db.log')], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
    client = ['mariadb', '--no-defaults', '--socket=' + socket, '--user=root', '--batch', '--skip-column-names']
    for _ in range(100):
        probe = subprocess.run(client + ['-e', 'SELECT 1'], capture_output=True)
        if probe.returncode == 0: break
        if server.poll() is not None: raise RuntimeError('Disposable database failed to start')
        time.sleep(.1)
    else: raise RuntimeError('Disposable database startup timeout')
    subprocess.run(client + ['-e', 'CREATE DATABASE toolkit_migration_check'], check=True, capture_output=True)
    old = subprocess.run(client + ['toolkit_migration_check', '-e', 'CREATE TABLE legacy_probe (a TIMESTAMP NOT NULL,b TIMESTAMP NOT NULL)'], capture_output=True)
    assert old.returncode != 0 and b'1067' in old.stderr, 'Legacy host failure not reproduced'
    env = dict(os.environ, APP_ENV='testing', DB_CONNECTION='mysql', DB_URL='', DB_DATABASE='toolkit_migration_check', DB_USERNAME='root', DB_PASSWORD='', DB_SOCKET=socket, CACHE_STORE='array', SESSION_DRIVER='array')
    subprocess.run(['php', 'artisan', 'migrate', '--force', '--path=database/migrations/2026_10_26_090000_create_website_applications_table.php'], cwd=APP, env=env, check=True, capture_output=True)
    types = subprocess.check_output(client + ['-e', "SELECT DATA_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='toolkit_migration_check' AND TABLE_NAME='website_applications' AND COLUMN_NAME IN ('submitted_at','source_updated_at','delivered_at','synced_at')"]).decode().splitlines()
    assert types == ['datetime'] * 4
    assert subprocess.check_output(client + ['toolkit_migration_check', '-e', 'SELECT COUNT(*) FROM website_applications']).strip() == b'0'
    print('PASS: legacy TIMESTAMP failure reproduced; additive migration succeeds with four DATETIME source/sync fields and no application rows. Socket-only disposable database.')
finally:
    if server and server.poll() is None:
        server.terminate()
        try: server.wait(timeout=15)
        except subprocess.TimeoutExpired: server.kill(); server.wait()
    shutil.rmtree(temp)
