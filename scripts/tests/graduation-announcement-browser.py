#!/usr/bin/env python3
"""Read-only popup acceptance in a dedicated CDP browser, never submit a form."""
import argparse
import base64
import datetime
import json
import time
from pathlib import Path

import requests
import websocket

parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('base')
parser.add_argument('output', type=Path)
parser.add_argument('--port', type=int, default=9338)
args = parser.parse_args()
args.output.mkdir(parents=True, exist_ok=True)
origin = f'http://localhost:{args.port}'
page = requests.get(origin + '/json/list', timeout=10).json()[0]
ws = websocket.create_connection(page['webSocketDebuggerUrl'], origin=origin, timeout=40)
seq = 0
results = []


def call(method, params=None):
    global seq
    seq += 1
    ws.send(json.dumps({'id': seq, 'method': method, 'params': params or {}}))
    while True:
        response = json.loads(ws.recv())
        if response.get('id') == seq:
            if 'error' in response:
                raise RuntimeError(response['error'])
            return response.get('result', {})


def js(expression):
    result = call('Runtime.evaluate', {'expression': expression, 'returnByValue': True, 'awaitPromise': True})
    if 'exceptionDetails' in result:
        raise RuntimeError(result['exceptionDetails'])
    return result['result'].get('value')


def check(name, condition):
    assert condition, name
    results.append({'check': name, 'passed': True})


def navigate(path):
    target = args.base.rstrip('/') + path + '?graduation_browser=2026.10.07.3'
    call('Page.navigate', {'url': target})
    time.sleep(.5)
    for _ in range(100):
        if js('location.href') == target and js('document.readyState') == 'complete':
            break
        time.sleep(.2)
    assert js('document.readyState') == 'complete'
    time.sleep(2.8)


def screenshot(name):
    data = call('Page.captureScreenshot', {'format': 'png', 'captureBeyondViewport': False})['data']
    (args.output / name).write_bytes(base64.b64decode(data))


def seed(now=None, previous=None):
    now = now or datetime.datetime.now(datetime.timezone.utc).isoformat()
    script = """(() => {
      const key='toolkit:graduation:2026-10-22';
      Date.now=()=>Date.parse(NOW);
      localStorage.removeItem(key+':seen');sessionStorage.removeItem(key+':session');
      document.cookie='toolkit_graduation_20261022_seen=; Path=/; Max-Age=0';
      document.cookie='toolkit_graduation_20261022_session=; Path=/; Max-Age=0';
      if(PREVIOUS) localStorage.setItem(key+':seen', String(Date.parse(PREVIOUS)));
    })();""".replace('NOW', json.dumps(now)).replace('PREVIOUS', json.dumps(previous))
    return call('Page.addScriptToEvaluateOnNewDocument', {'source': script})['identifier']


call('Page.enable')
call('Runtime.enable')
call('Emulation.setDeviceMetricsOverride', {'width': 1440, 'height': 1000, 'deviceScaleFactor': 1, 'mobile': False})
seed_id = seed()
navigate('/')
call('Page.removeScriptToEvaluateOnNewDocument', {'identifier': seed_id})
check('First eligible visit opens one accessible dialog', js("document.querySelectorAll('[data-graduation-announcement][open]').length===1 && document.activeElement.matches('[data-graduation-close]')"))
check('Official logo, supplied photo and release asset loaded', js("document.querySelector('.toolkit-graduation__brand img').src.includes('/assets/images/toolkit-logo.png') && document.querySelector('[data-graduation-photo]').naturalWidth===1800 && [...document.scripts].some(s=>s.src.includes('graduation-announcement.js?ver=2026.10.07.3'))"))
check('Poster has event date, time and calendar download', js("document.querySelector('dialog.toolkit-graduation').textContent.includes('22nd October 2026') && document.querySelector('.toolkit-graduation__save').hasAttribute('download') && document.querySelector('.toolkit-graduation__save').href.endsWith('toolkit-graduation-2026.ics')"))
check('Desktop fits viewport', js("document.documentElement.scrollWidth<=innerWidth && document.querySelector('dialog.toolkit-graduation').scrollWidth<=document.querySelector('dialog.toolkit-graduation').clientWidth"))
check('Popup is centred and close controls preserve Toolkit design', js("(()=>{let r=document.querySelector('dialog.toolkit-graduation').getBoundingClientRect();return Math.abs((r.left+r.width/2)-innerWidth/2)<2 && Math.abs((r.top+r.height/2)-innerHeight/2)<2 && getComputedStyle(document.querySelector('.toolkit-graduation__close')).backgroundColor==='rgb(255, 255, 255)' && getComputedStyle(document.querySelector('.toolkit-graduation__dismiss')).backgroundColor==='rgb(255, 255, 255)';})()"))
screenshot('graduation-desktop.png')
for _ in range(8):
    call('Input.dispatchKeyEvent', {'type': 'keyDown', 'key': 'Tab', 'code': 'Tab', 'windowsVirtualKeyCode': 9})
    call('Input.dispatchKeyEvent', {'type': 'keyUp', 'key': 'Tab', 'code': 'Tab', 'windowsVirtualKeyCode': 9})
    assert js("document.querySelector('dialog.toolkit-graduation').contains(document.activeElement)")
check('Keyboard focus stays inside the open announcement', True)
call('Input.dispatchKeyEvent', {'type': 'keyDown', 'key': 'Escape', 'code': 'Escape', 'windowsVirtualKeyCode': 27})
call('Input.dispatchKeyEvent', {'type': 'keyUp', 'key': 'Escape', 'code': 'Escape', 'windowsVirtualKeyCode': 27})
time.sleep(.2)  # Native dialog close events run after the key event.
check('Escape dismisses and unlocks the page', js("!document.querySelector('dialog.toolkit-graduation').open && !document.documentElement.classList.contains('toolkit-graduation-open')"))
navigate('/our-ventures/')
check('Changing pages does not reopen the announcement or fetch its photo', js("!document.querySelector('dialog.toolkit-graduation').open && !document.querySelector('[data-graduation-photo]').hasAttribute('src')"))
navigate('/apply/')
check('Application form excludes the popup and its script', js("!document.querySelector('[data-graduation-announcement]') && ![...document.scripts].some(s=>s.src.includes('graduation-announcement.js'))"))
navigate('/contact/')
check('Contact form excludes the popup', js("!document.querySelector('[data-graduation-announcement]')"))
# Future clocks test the published client code without changing server time.
for name, now, previous, expected in [
    ('Normal day rejects another visit before 24 hours', '2026-10-08T08:59:59Z', '2026-10-07T09:00:00Z', False),
    ('Final-week new visit opens at six hours', '2026-10-16T09:00:00Z', '2026-10-16T03:00:00Z', True),
    ('Final-two-day new visit opens at two hours', '2026-10-21T09:00:00Z', '2026-10-21T07:00:00Z', True),
    ('Finished ceremony never opens cached popup markup', '2026-10-22T10:00:00Z', None, False),
]:
    seed_id = seed(now, previous)
    navigate('/')
    call('Page.removeScriptToEvaluateOnNewDocument', {'identifier': seed_id})
    check(name, js("document.querySelector('dialog.toolkit-graduation').open") is expected)
    if expected:
        js("document.querySelector('.toolkit-graduation__close').click()")
        assert js("!document.querySelector('dialog.toolkit-graduation').open")
seed_id = seed()
call('Emulation.setDeviceMetricsOverride', {'width': 390, 'height': 844, 'deviceScaleFactor': 1, 'mobile': True})
navigate('/')
call('Page.removeScriptToEvaluateOnNewDocument', {'identifier': seed_id})
check('390px phone layout contains all content without horizontal scrolling', js("document.querySelector('dialog.toolkit-graduation').open && document.documentElement.scrollWidth<=innerWidth && document.querySelector('dialog.toolkit-graduation').scrollWidth<=document.querySelector('dialog.toolkit-graduation').clientWidth"))
screenshot('graduation-mobile-top.png')
js("document.querySelector('dialog.toolkit-graduation').scrollTop=9999")
screenshot('graduation-mobile-details.png')
check('Phone date, venue and close/save controls remain reachable', js("(()=>{let d=document.querySelector('dialog.toolkit-graduation'),b=d.getBoundingClientRect();return [...d.querySelectorAll('.toolkit-graduation__actions a, .toolkit-graduation__actions button')].every(e=>{let r=e.getBoundingClientRect();return r.top>=b.top&&r.bottom<=b.bottom;});})()"))
check('Clickable text and icons use a pointer cursor', js("getComputedStyle(document.querySelector('.toolkit-graduation__save svg')).cursor==='pointer' && getComputedStyle(document.querySelector('.toolkit-graduation__close')).cursor==='pointer'"))
call('Emulation.setDeviceMetricsOverride', {'width': 320, 'height': 700, 'deviceScaleFactor': 1, 'mobile': True})
check('320px phone layout remains contained', js("document.querySelector('dialog.toolkit-graduation').scrollWidth<=document.querySelector('dialog.toolkit-graduation').clientWidth"))
js("document.querySelector('.toolkit-graduation__close').click()")
check('Close button dismisses without page navigation', js("!document.querySelector('dialog.toolkit-graduation').open"))
calendar = requests.get(args.base.rstrip('/') + '/wp-content/themes/eduma-child/assets/calendar/toolkit-graduation-2026.ics', timeout=30)
check('Downloaded calendar preserves 9am–1pm Nairobi and location', calendar.status_code == 200 and 'DTSTART:20261022T060000Z' in calendar.text and 'DTEND:20261022T100000Z' in calendar.text and 'Karen-Kikuyu Highway' in calendar.text)
report = {'base': args.base, 'release': '2026.10.07.3', 'checks': results, 'passed': len(results), 'forms_submitted': 0, 'test_method': 'Isolated browser, actual deployed pages/assets, synthetic client clock/storage for reminder boundaries'}
(args.output / 'browser-results.json').write_text(json.dumps(report, indent=2) + '\n')
print(json.dumps(report))
ws.close()
