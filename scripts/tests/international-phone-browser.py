#!/usr/bin/env python3
"""Exercise deployed staff markup/assets with synthetic fixtures; never submit forms."""
import argparse, base64, json, re, time
from pathlib import Path
import requests, websocket
p=argparse.ArgumentParser();p.add_argument('root',type=Path);p.add_argument('--legacy-approved',action='store_true');p.add_argument('--base',default='https://reception.toolkitafrica.ac.ke');args=p.parse_args();root=args.root.resolve();out=root/'screenshots';out.mkdir(exist_ok=True)
page=requests.get('http://localhost:9337/json/list',timeout=10).json()[0];ws=websocket.create_connection(page['webSocketDebuggerUrl'],origin='http://localhost:9337',timeout=40);seq=0

def call(method,params=None):
 global seq
 seq+=1;ws.send(json.dumps({'id':seq,'method':method,'params':params or {}}))
 while True:
  result=json.loads(ws.recv())
  if result.get('id')==seq:
   if 'error' in result:raise RuntimeError(result['error'])
   return result.get('result',{})
def js(expr):
 result=call('Runtime.evaluate',{'expression':expr,'returnByValue':True,'awaitPromise':True})
 if result.get('exceptionDetails'):raise RuntimeError(result['exceptionDetails']['text'])
 return result['result'].get('value')
def open_fixture(name):
 text=(root/'reception/fixtures'/name).read_text().replace('http://localhost',args.base)
 file=out/name;file.write_text(text);call('Page.navigate',{'url':file.as_uri()})
 for _ in range(80):
  time.sleep(.15)
  if js('document.readyState')=='complete':break
 time.sleep(.8)
 assert js("document.querySelectorAll('script[src]').length > 0")
def capture(name):
 js("(()=>{if(!document.querySelector('#evidence-banner')){const b=document.createElement('div');b.id='evidence-banner';b.textContent='DEPLOYED SOURCE · SYNTHETIC INPUT/DATA · NO FORM SUBMISSION · 5 OCTOBER 2026';b.style.cssText='position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#173f37;color:#fff;padding:9px;text-align:center;font:12px Arial';document.body.appendChild(b)}})()")
 (out/name).write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':False})['data']))
call('Page.enable');call('Runtime.enable');call('Emulation.setDeviceMetricsOverride',{'width':1440,'height':1050,'deviceScaleFactor':1,'mobile':False})
checks=[]
for path in ['/check-in','/apply']:
 call('Page.navigate',{'url':args.base+path+'?phone_release=20261005.7'})
 for _ in range(80):
  time.sleep(.15)
  if js('document.readyState')=='complete':break
 assert js("!!window.ToolkitPhone && document.querySelector('[name=phone_country]').value==='KE' && document.querySelector('[name=phone_country]').options.length===245")
 js("(()=>{const e=document.querySelector('[name=phone]');e.value='0712345678';e.dispatchEvent(new Event('input'));e.dispatchEvent(new Event('blur'));})()")
 assert js("document.querySelector('[name=phone]').value==='+254712345678' && !document.querySelector('[name=phone]').validationMessage")
 js("(()=>{const c=document.querySelector('[name=phone_country]');c.value='GB';c.dispatchEvent(new Event('change'));const e=document.querySelector('[name=phone]');e.value='07400123456';e.dispatchEvent(new Event('input'));e.dispatchEvent(new Event('blur'));e.scrollIntoView({block:'center'});})()")
 assert js("document.querySelector('[name=phone]').value==='+447400123456' && !document.querySelector('[name=phone]').validationMessage")
 assert js("document.querySelector('[name=phone_country] option[value=KE]').textContent.startsWith('🇰🇪') && document.querySelector('[name=phone_country] option[value=GB]').textContent.startsWith('🇬🇧')")
 for raw,country,expected in [('254712345678','KE','+254712345678'),('2540712345678','KE','+254712345678'),('+2540712345678','KE','+254712345678'),('447400123456','GB','+447400123456'),('4407400123456','GB','+447400123456'),('12015550123','US','+12015550123')]:
  js("(()=>{const e=document.querySelector('[name=phone]');e.value="+json.dumps(raw)+";e.dispatchEvent(new Event('input'));e.dispatchEvent(new Event('blur'));})()")
  assert js("document.querySelector('[name=phone_country]').value")==country
  assert js("document.querySelector('[name=phone]').value")==expected
 js("(()=>{const e=document.querySelector('[name=phone]');e.value='447400123456';e.dispatchEvent(new Event('input'));e.dispatchEvent(new Event('blur'));})()")
 capture('reception-country-'+path.strip('/')+'.png')
 js("document.querySelector('[name=phone_country]').value='KE';document.querySelector('[name=phone_country]').dispatchEvent(new Event('change'))")
 assert js("document.querySelector('[name=phone]').validationMessage.length>0")
 js("(()=>{const e=document.querySelector('[name=phone]');e.value='+12015550123';e.dispatchEvent(new Event('input'));e.dispatchEvent(new Event('blur'));})()")
 assert js("document.querySelector('[name=phone_country]').value==='US' && document.querySelector('[name=phone]').value==='+12015550123'")
 js("(()=>{const e=document.querySelector('[name=phone]');e.value='123456789012345678';e.dispatchEvent(new Event('input'));})()")
 assert js("document.querySelector('[name=phone]').validationMessage.length>0")
 call('Emulation.setDeviceMetricsOverride',{'width':390,'height':844,'deviceScaleFactor':1,'mobile':True})
 assert js("document.documentElement.scrollWidth<=document.documentElement.clientWidth")
 capture('reception-country-'+path.strip('/')+'-mobile.png')
 call('Emulation.setDeviceMetricsOverride',{'width':1440,'height':1050,'deviceScaleFactor':1,'mobile':False})
 checks.append({'page':path,'checks':'Default Kenya; 245 flagged countries; bare/plus calling codes detected; trunk-prefix normalization; domestic/foreign canonical formats; mismatches/overlong inputs rejected; pasted calling code selects country; 390px containment','passed':True,'source':'Live public GET; synthetic input; no submission'})
for fixture in ['manual-visitor-registration.html','manual-student-registration.html','applicant-contact-correction.html']:
 open_fixture(fixture)
 assert js("document.querySelector('[name=phone_country]').options.length===245")
 if fixture.startswith('manual'):assert js("document.querySelector('[name=phone_country]').value==='KE'")
 else:assert js("document.querySelector('[name=phone_country]').value==='GB' && document.querySelector('[name=phone]').value==='+447400123456'")
 js("(()=>{const c=document.querySelector('[name=phone_country]');c.value='KE';c.dispatchEvent(new Event('change'));const e=document.querySelector('[name=phone]');e.value='0712345678';e.dispatchEvent(new Event('input'));e.dispatchEvent(new Event('blur'));e.scrollIntoView({block:'center'});})()")
 assert js("document.querySelector('[name=phone]').value==='+254712345678'")
 capture('reception-country-'+fixture.replace('.html','.png'))
 checks.append({'fixture':fixture,'passed':True,'source':'Deployed synthetic server fixture and live assets; no submission'})
result={'base':args.base,'result':'INTERNATIONAL_PHONE_BROWSER_OK','checks':checks,'production_records_changed':0}
(out/'international-phone-browser-results.json').write_text(json.dumps(result,indent=2));print(json.dumps(result));ws.close()
