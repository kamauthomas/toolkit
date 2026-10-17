#!/usr/bin/env python3
"""Bounded normal form lookups only: no application submissions or negative probes."""
import argparse
import json
import re
import time
from pathlib import Path
from urllib.parse import urlsplit
import requests

ROOT = Path(__file__).resolve().parents[2]


def check(environment):
    base = 'https://' + ('demo.' if environment == 'demo' else '') + 'toolkitafrica.ac.ke'
    out = ROOT / 'reports/mzizi-relay-2026-10-17/deployment' / environment
    out.mkdir(parents=True, exist_ok=True, mode=0o700)
    session = requests.Session()
    response = session.get(base + '/apply/?catalogue_check=2026.10.17.1', timeout=30)
    response.raise_for_status()
    match = re.search(r'var toolkitApplication = (\{.*?\});', response.text)
    if not match:
        raise RuntimeError('Application configuration missing')
    config = json.loads(match[1])
    headers = {'X-Toolkit-Form-Token': config['formToken'], 'Origin': base}
    rows = []
    selected = None
    for key, parameter, field, route in [
        ('optionsEndpoint', None, 'schools', 'options'),
        ('coursesEndpoint', 'school_id', 'courses', 'courses'),
        ('intakesEndpoint', 'course_id', 'intakes', 'intakes'),
    ]:
        url = config[key]
        parsed = urlsplit(url)
        if parsed.netloc != urlsplit(base).netloc or not parsed.path.endswith('/toolkit/v1/application/' + route):
            raise RuntimeError('Unexpected catalogue endpoint')
        started = time.monotonic()
        response = session.get(url, headers=headers, timeout=30) if parameter is None else session.post(url, headers=headers, json={parameter: selected}, timeout=30)
        # Stop on the first error, including WAF/timeouts; never retry or change IPs.
        response.raise_for_status()
        data = response.json()
        if not isinstance(data.get(field), list) or not data[field]:
            raise RuntimeError('Empty or unreadable catalogue')
        selected = data[field][0]['id']
        rows.append({'route': key, 'http': response.status_code, 'items': len(data[field]), 'seconds': round(time.monotonic() - started, 2)})
        time.sleep(1)
    (out / 'public-catalogue-check.json').write_text(json.dumps({'environment': environment, 'checks': rows, 'applications_submitted': 0}, indent=2) + '\n')
    print(json.dumps(rows))


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('environment', choices=['demo', 'production'])
    args = parser.parse_args()
    check(args.environment)
