#!/usr/bin/env python3
"""Check only local synthetic Toolkit admin fixtures; no production login/actions."""
import argparse,base64,json,time
from pathlib import Path
import requests,websocket
p=argparse.ArgumentParser();p.add_argument('output',type=Path);a=p.parse_args();a.output.mkdir(parents=True,exist_ok=True)
tabs=requests.get('http://127.0.0.1:9346/json/list',timeout=5).json();tab=next(t for t in tabs if t.get('type')=='page');w=websocket.create_connection(tab['webSocketDebuggerUrl'],timeout=15,suppress_origin=True);seq=0;checks=[]
def call(method,params):
 global seq
 seq+=1;n=seq;w.send(json.dumps({'id':n,'method':method,'params':params}))
 while True:
  r=json.loads(w.recv())
  if r.get('id')==n:
   assert 'error' not in r;return r.get('result',{})
def js(s):
 r=call('Runtime.evaluate',{'expression':s,'returnByValue':True});assert not r.get('exceptionDetails'),r.get('exceptionDetails');return r['result'].get('value')
def check(ok,label):
 assert ok,label;checks.append(label)
try:
 for page in ['overview','applications']:
  for width in [1440,390]:
   call('Emulation.setDeviceMetricsOverride',{'width':width,'height':950 if width>500 else 844,'deviceScaleFactor':1,'mobile':width<500})
   call('Page.navigate',{'url':f'http://127.0.0.1:8826/{page}.html'})
   for _ in range(50):
    if js("document.readyState==='complete'&&Boolean(document.querySelector('.toolkit-admin'))"):break
    time.sleep(.1)
   check(js("document.documentElement.scrollWidth<=innerWidth+1"),f'{page} {width}: no page overflow')
   check(js("document.querySelector('.toolkit-admin__workspace-nav [aria-current=page]')!==null"),f'{page} {width}: active navigation')
   check(js("document.querySelector('.toolkit-admin__identity img').complete&&document.querySelector('.toolkit-admin__identity img').naturalWidth>0"),f'{page} {width}: logo loaded')
   check(js("[...document.querySelectorAll('form[action]')].every(f=>!String(f.getAttribute('action')).includes('SubmitOnlineApplication'))"),f'{page} {width}: no upstream business action')
   if page=='overview':check(js("document.querySelectorAll('.toolkit-admin__stats>a').length===8"),f'{page} {width}: all metrics visible')
   image=call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':True});(a.output/f'{page}-{width}.png').write_bytes(base64.b64decode(image['data']))
 (a.output/'admin-browser-checks.json').write_text(json.dumps({'checks_passed':len(checks),'checks':checks,'synthetic_only':True,'business_submissions':0},indent=2)+'\n');print('PASS',len(checks),'local desktop/mobile admin checks')
finally:w.close()
