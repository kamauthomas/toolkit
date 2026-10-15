#!/usr/bin/env python3
"""Inspect synthetic Reception/roster fixtures and public graduands state via CDP."""
import argparse,base64,json,time
from pathlib import Path
import requests,websocket
p=argparse.ArgumentParser();p.add_argument('base');p.add_argument('output',type=Path);p.add_argument('--port',type=int,default=9340);a=p.parse_args();a.output.mkdir(parents=True,exist_ok=True)
origin=f'http://localhost:{a.port}';page=requests.get(origin+'/json/list',timeout=10).json()[0];ws=websocket.create_connection(page['webSocketDebuggerUrl'],origin=origin,timeout=25);seq=0;checks=[]
def call(method,params=None):
 global seq
 seq+=1;i=seq;ws.send(json.dumps({'id':i,'method':method,'params':params or {}}))
 while True:
  msg=json.loads(ws.recv())
  if msg.get('id')==i:
   if 'error'in msg:raise RuntimeError(msg['error'])
   return msg.get('result',{})
def js(expression):
 r=call('Runtime.evaluate',{'expression':expression,'returnByValue':True})
 if r.get('exceptionDetails'):raise RuntimeError(r['exceptionDetails'])
 return r.get('result',{}).get('value')
def check(ok,label):
 if not ok:raise AssertionError(label)
 checks.append(label)
def load(name,width=1440):
 call('Emulation.setDeviceMetricsOverride',{'width':width,'height':950 if width>620 else 844,'deviceScaleFactor':1,'mobile':width<620});call('Page.navigate',{'url':a.base.rstrip('/')+'/'+name});
 for attempt in range(100):
  if js("document.readyState==='complete' && Boolean(document.querySelector('.staff-main, #graduands, #main-content'))"):break
  time.sleep(.2)
 check(js("document.readyState==='complete' && Boolean(document.querySelector('.staff-main, #graduands, #main-content'))"),'Loaded '+name+' '+str(width))
def capture(name):
 result=call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':True});(a.output/name).write_bytes(base64.b64decode(result['data']))
call('Page.enable');call('Runtime.enable')
if a.base.startswith('http://127.0.0.1'):
 for page in ['overview.html','applicants.html','staff-access.html','staff-activity.html','returning-visitor.html']:
  for width in [1440,390]:
   load(page,width)
   check(js("document.documentElement.scrollWidth<=innerWidth+1"),'No horizontal page overflow '+page+' '+str(width))
   check(js("[...document.querySelectorAll('.staff-pagination a')].every(a=>getComputedStyle(a).cursor==='pointer'&&a.getBoundingClientRect().height>=40)"),'Pagination controls clickable '+page+' '+str(width))
   check(js("[...document.querySelectorAll('.staff-pagination svg')].length===0"),'No oversized pagination SVG '+page+' '+str(width))
   if page=='overview.html':check(js("document.querySelectorAll('.attendance-week meter').length===7"),'Seven daily arrival values '+str(width))
   capture(page.replace('.html','')+'-'+str(width)+'.png')
 for width in [1440,390]:
  load('graduands-published.html',width)
  check(js("document.documentElement.scrollWidth<=innerWidth+1"),'Roster mobile containment '+str(width))
  check(js("(()=>{const i=document.querySelector('[data-graduands-search]');i.value='elodie';i.dispatchEvent(new Event('input'));return document.querySelectorAll('[data-graduand]:not([hidden])').length===1})()"),'Accent-insensitive name search '+str(width))
  check(js("(()=>{document.querySelector('[data-graduands-reset]').click();const c=document.querySelector('[data-graduands-course]');c.value='Digital Skills';c.dispatchEvent(new Event('change'));return document.querySelectorAll('[data-graduand]:not([hidden])').length===2})()"),'Course filter '+str(width))
  check(js("(()=>{const i=document.querySelector('[data-graduands-search]');i.value='unmatched';i.dispatchEvent(new Event('input'));return !document.querySelector('[data-graduands-empty]').hidden})()"),'No-match feedback '+str(width))
  js("document.querySelector('[data-graduands-reset]').click()")
  capture('graduands-published-'+str(width)+'.png')
  js("const i=document.querySelector('[data-graduands-search]');i.value='elodie';i.dispatchEvent(new Event('input'))")
  call('Emulation.setEmulatedMedia',{'media':'print'})
  check(js("[...document.querySelectorAll('[data-graduand]')].every(row=>getComputedStyle(row).display==='table-row')"),'Print includes complete roster '+str(width))
  result=call('Page.printToPDF',{'printBackground':True});(a.output/('graduands-print-'+str(width)+'.pdf')).write_bytes(base64.b64decode(result['data']));call('Emulation.setEmulatedMedia',{'media':''})
 load('graduands-pending.html',390);check(js("document.body.innerText.includes('confirmed list is being prepared')&&!document.querySelector('[data-graduand]')"),'Pending source has no test names');capture('graduands-pending.png')
else:
 load('graduation/#graduands',390)
 check(js("Boolean(document.querySelector('#graduands'))"),'Public QR anchor exists')
 check(js("document.querySelector('#graduands').innerText.includes('confirmed list is being prepared')"),'Published pending roster state')
 check(js("!document.querySelector('[data-graduand]')&&!document.body.innerText.includes('Synthetic')"),'No synthetic roster published')
 check(js("document.documentElement.scrollWidth<=innerWidth+1"),'Public mobile layout contained')
 js("document.querySelector('dialog[open] [data-graduation-close]')?.click(); document.querySelector('#graduands').scrollIntoView()")
 capture('public-graduands-mobile.png')
 # Render a labelled client-only test roster under the actual deployed theme.
 # This changes only the disposable browser DOM, never server options or records.
 fixture=Path('/tmp/toolkit-review-20261015/graduands-published.html').read_text()
 js("document.querySelector('#graduands').outerHTML=new DOMParser().parseFromString("+json.dumps(fixture)+",'text/html').querySelector('#graduands').outerHTML")
 script=requests.get(a.base+'/wp-content/themes/eduma-child/assets/js/graduands.js?ver=2026.10.15.1',timeout=30).text
 entry="document.addEventListener('DOMContentLoaded', () => {"
 assert script.count(entry)==1
 script=script.replace(entry,'(() => {',1)
 script=script[:script.rfind('});')]+'})();'
 js(script)
 js("document.querySelector('#graduands .toolkit-kicker').textContent='SYNTHETIC PREVIEW · not published'")
 check(js("document.documentElement.scrollWidth<=innerWidth+1"),'Approved-roster mobile containment under deployed theme')
 check(js("(()=>{const i=document.querySelector('[data-graduands-search]');i.value='elodie';i.dispatchEvent(new Event('input'));return document.querySelectorAll('[data-graduand]:not([hidden])').length===1})()"),'Deployed roster script filters synthetic accented name')
 js("document.querySelector('[data-graduands-reset]').click(); document.querySelector('#graduands').scrollIntoView()")
 capture('client-only-roster-preview.png')
(a.output/'browser-results.json').write_text(json.dumps({'checks_passed':len(checks),'checks':checks,'base':a.base,'form_submissions':0},indent=2)+'\n');print('PASS:',len(checks),'browser checks; no forms submitted');ws.close()
