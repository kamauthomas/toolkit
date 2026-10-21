#!/usr/bin/env python3
"""Normal desktop/mobile publication acceptance in an isolated CDP browser; no forms submitted."""
import argparse
import base64
import csv
import json
import time
from pathlib import Path

import requests
import websocket

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('environment', choices=['demo', 'production'])
parser.add_argument('--port', type=int, default=9341)
args = parser.parse_args()
root = Path(__file__).resolve().parents[2]
out = root / 'reports/graduation-2026-10-21/deployment' / args.environment
origin = 'http://localhost:' + str(args.port)
page = requests.get(origin + '/json/list', timeout=10).json()[0]
ws = websocket.create_connection(page['webSocketDebuggerUrl'], origin=origin, timeout=35)
sequence = 0
checks = []


def call(method, params=None):
    global sequence
    sequence += 1
    ws.send(json.dumps({'id': sequence, 'method': method, 'params': params or {}}))
    while True:
        message = json.loads(ws.recv())
        if message.get('id') == sequence:
            if 'error' in message:
                raise RuntimeError(message['error'])
            return message.get('result', {})


def js(expression):
    result = call('Runtime.evaluate', {'expression': expression, 'returnByValue': True, 'awaitPromise': True})
    if 'exceptionDetails' in result:
        raise RuntimeError('Browser expression failed')
    return result.get('result', {}).get('value')


def check(condition, label):
    if not condition:
        raise RuntimeError(label)
    checks.append(label)


def navigate(path):
    base = 'https://' + ('demo.' if args.environment == 'demo' else '') + 'toolkitafrica.ac.ke'
    call('Page.navigate', {'url': base + path})
    for _ in range(40):
        if js("document.readyState==='complete' && location.pathname===" + json.dumps(path)):
            break
        time.sleep(.5)
    time.sleep(2)
    js("document.querySelectorAll('dialog[open]').forEach(d=>d.close())")


def size(width, height):
    call('Emulation.setDeviceMetricsOverride', {'width': width, 'height': height, 'deviceScaleFactor': 1, 'mobile': width < 600})
    time.sleep(.7)
    js('new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))')


def shot(name, selector):
    js('document.querySelector(' + json.dumps(selector) + ').scrollIntoView({block:"start"})')
    time.sleep(.4)
    raw = call('Page.captureScreenshot', {'format': 'png', 'captureBeyondViewport': False})
    (out / name).write_bytes(base64.b64decode(raw['data']))


call('Page.enable')
size(1440, 1000)
navigate('/graduation/')
check(js("document.querySelectorAll('[data-graduand]').length===294"), 'All 294 approved names render')
check(js("document.querySelectorAll('#programme tbody tr').length===9"), 'Nine programme activities render')
check(js("document.querySelector('.graduation-date').textContent.includes('8:00–9:00 AM')"), 'Arrival is distinct from formal ceremony')
check(js("document.querySelectorAll('[data-graduands-course] option').length===6"), 'Five course filters plus all-courses')
check(js("document.querySelector('[data-graduation-announcement] img').src.endsWith('/images/toolkit-logo.png')"), 'Official logo retained')
shot('programme-desktop.png', '#programme')
check(js('document.documentElement.scrollWidth<=innerWidth+1'), 'Desktop graduation fits viewport')
with (root / 'reports/graduation-2026-10-21/graduands-approved.csv').open() as stream:
    name = next(csv.DictReader(stream))['display_name']
js("var s=document.querySelector('[data-graduands-search]');s.value=" + json.dumps(name) + ";s.dispatchEvent(new Event('input'))")
check(js("document.querySelectorAll('[data-graduand]:not([hidden])').length===1"), 'Name search finds approved entry')
js("document.querySelector('[data-graduands-reset]').click();var c=document.querySelector('[data-graduands-course]');c.value='Welding';c.dispatchEvent(new Event('change'))")
check(js("document.querySelectorAll('[data-graduand]:not([hidden])').length===178"), 'Welding filter returns 178 names')
call('Emulation.setEmulatedMedia', {'media': 'print'})
check(js("Array.from(document.querySelectorAll('[data-graduand]')).filter(r=>getComputedStyle(r).display!=='none').length===294"), 'Print includes all names despite active filter')
call('Emulation.setEmulatedMedia', {'media': 'screen'})
js("document.querySelector('[data-graduands-reset]').click();var s=document.querySelector('[data-graduands-search]');s.value='No matching graduation fixture';s.dispatchEvent(new Event('input'))")
check(js("!document.querySelector('[data-graduands-empty]').hidden"), 'No-match guidance visible')
js("document.querySelector('[data-graduands-reset]').click()")
check(js("document.querySelectorAll('[data-graduand]:not([hidden])').length===294"), 'Reset restores all names')
size(390, 844)
shot('graduands-mobile.png', '#graduands')
check(js('document.documentElement.scrollWidth<=innerWidth+1'), 'Mobile graduation has no page overflow')
shot('programme-mobile.png', '#programme')
check(js("document.querySelector('#programme .graduation-programme__table').clientWidth<=innerWidth"), 'Mobile programme has bounded table scroll')
size(1440, 1000)
navigate('/notice-board/')
check(js("Array.from(document.querySelectorAll('article[data-category=event]')).some(c=>c.textContent.includes('12th Toolkit graduation'))"), 'Graduation event appears on Notice Board')
js("document.querySelector('[data-filter=event]').click()")
check(js("Array.from(document.querySelectorAll('.toolkit-notice-grid article')).filter(c=>getComputedStyle(c).display!=='none').every(c=>c.dataset.category==='event')"), 'Events filter retains event cards only')
check(js("Boolean(document.querySelector('article[data-category=event] a[href$=\"#graduands\"]'))"), 'Notice links to stable QR list destination')
shot('notice-desktop.png', '.toolkit-notices')
size(390, 844)
shot('notice-mobile.png', '.toolkit-notices')
check(js('innerWidth===390 && document.documentElement.scrollWidth<=innerWidth+1'), 'Mobile Notice Board has no page overflow at 390 pixels')
(out / 'browser-checks.json').write_text(json.dumps({'environment': args.environment, 'passed': len(checks), 'checks': checks, 'application_submissions': 0}, indent=2) + '\n')
print('PASS', args.environment, len(checks), 'graduation publication desktop/mobile checks')
ws.close()
