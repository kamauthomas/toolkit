#!/usr/bin/env python3
"""Render server-generated synthetic evidence from byte-verified deployed source."""
import argparse,base64,json,time
from pathlib import Path
import requests,websocket
p=argparse.ArgumentParser();p.add_argument('root',type=Path);args=p.parse_args();root=args.root.resolve();out=root/'screenshots';out.mkdir(exist_ok=True)
page=requests.get('http://localhost:9337/json/list',timeout=10).json()[0];ws=websocket.create_connection(page['webSocketDebuggerUrl'],origin='http://localhost:9337',timeout=45);seq=0
def call(method,params=None):
 global seq;seq+=1;ws.send(json.dumps({'id':seq,'method':method,'params':params or {}}))
 while True:
  r=json.loads(ws.recv())
  if r.get('id')==seq:
   if 'error' in r:raise RuntimeError(r['error'])
   return r.get('result',{})
def js(expr):return call('Runtime.evaluate',{'expression':expr,'returnByValue':True,'awaitPromise':True})['result'].get('value')
call('Page.enable');call('Runtime.enable');call('Emulation.setDeviceMetricsOverride',{'width':1440,'height':1050,'deviceScaleFactor':1,'mobile':False})
items=[]
for system,folder,base in [('reception','reception/fixtures','https://reception.toolkitafrica.ac.ke'),('reporting','reporting/staged','https://reports.toolkitafrica.ac.ke')]:
 for source in sorted((root/folder).glob('*.html')):
  text=source.read_text();text=text.replace('http://localhost',base)
  # Match the CSS fetched from production; the fixture does not authenticate to live staff pages.
  if system=='reporting':
   import re
   css=(root/'reporting/source/static/styles.css').read_text();text=re.sub(r'<link[^>]+href=["\'][^"\']*styles\.css[^"\']*["\'][^>]*>',lambda m:'<style>'+css+'</style>',text)
  label='DEPLOYED '+system.upper()+' SOURCE · SYNTHETIC DATABASE · 5 OCTOBER 2026'
  text=text.replace('<body', '<body',1)
  file=out/(system+'-'+source.name);file.write_text(text)
  call('Page.navigate',{'url':file.as_uri()})
  for _ in range(70):
   time.sleep(.2)
   if js('document.readyState')=='complete':break
  js("(()=>{const label=document.createElement('div');label.textContent="+json.dumps(label)+";label.style.cssText='position:fixed;bottom:0;left:0;right:0;z-index:99999;padding:10px;background:#173f37;color:white;font:14px Arial;text-align:center';document.body.appendChild(label);return true})()")
  # Capture the relevant administrative control without entering login credentials.
  if source.stem=='visitor-edit-archive':js("document.querySelector('#edit-details').scrollIntoView({block:'start'})")
  time.sleep(2)
  data=call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':False})['data'];image=out/(system+'-'+source.stem+'.png');image.write_bytes(base64.b64decode(data));items.append({'image':image.name,'source':str(source.relative_to(root)),'provenance':label})
# Public Reception validation uses production GET and client input only; no form submission.
call('Page.navigate',{'url':'https://reception.toolkitafrica.ac.ke/check-in?audit_evidence=2026.10.05.1'})
for _ in range(70):
 time.sleep(.2)
 if js('document.readyState')=='complete':break
js("(()=>{const f=document.querySelector('form');for(const [name,value] of Object.entries({name:'Synthetic Audit Visitor',phone:'12345678901234567890',email:'test@test'})){const e=f.elements[name];e.value=value;e.dispatchEvent(new Event('input',{bubbles:true}));}f.scrollIntoView({block:'center'});return true})()")
assert js("document.querySelector('[name=phone]').validationMessage.length>0 && document.querySelector('[name=email]').validationMessage.length>0")
data=call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':False})['data'];(out/'reception-public-invalid-contacts.png').write_bytes(base64.b64decode(data));items.append({'image':'reception-public-invalid-contacts.png','source':'Production GET /check-in; synthetic client inputs only; no POST'})
(out/'screenshot-provenance.json').write_text(json.dumps(items,indent=2));print('Captured',len(items),'evidence screenshots; no production records changed');ws.close()
