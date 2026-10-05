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
 js("(()=>{if(!document.querySelector('#evidence-banner')){const b=document.createElement('div');b.id='evidence-banner';b.textContent='DEPLOYED RECEPTION SOURCE · SYNTHETIC DATABASE · 5 OCTOBER 2026';b.style.cssText='position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#173f37;color:#fff;padding:9px;text-align:center;font:12px Arial';document.body.appendChild(b)}})()")
 (out/name).write_bytes(base64.b64decode(call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':False})['data']))
call('Page.enable');call('Runtime.enable');call('Emulation.setDeviceMetricsOverride',{'width':1440,'height':1050,'deviceScaleFactor':1,'mobile':False})
open_fixture('toolkit-staff-policy.html')
assert js("document.querySelectorAll('dialog[open]').length===0")
assert js("[4,5].includes(document.querySelectorAll('[data-staff-row]').length)")
assert js("getComputedStyle(document.querySelector('dialog')).display==='none'")
capture('reception-toolkit-staff-policy.png')
if args.legacy_approved:
 assert js("(()=>{const row=[...document.querySelectorAll('[data-staff-row]')].find(r=>r.dataset.search.includes('toolkit@toolkitiskills.com'));return !!row && row.dataset.attention==='0' && row.textContent.includes('Approved legacy account')})()")
 js("(()=>{const row=[...document.querySelectorAll('[data-staff-row]')].find(r=>r.dataset.search.includes('toolkit@toolkitiskills.com'));row.querySelector('[data-open-dialog^=staff-edit]').click()})()")
 assert js("document.querySelector('dialog[open] [name=email]').validationMessage==='' ")
 js("document.querySelector('dialog[open] [data-close-dialog]').click()")
assert js("(()=>{const s=document.querySelector('[data-staff-search]');s.value='operations';s.dispatchEvent(new Event('input'));return document.querySelectorAll('[data-staff-row]:not([hidden])').length===1})()")
assert js("(()=>{document.querySelector('[data-staff-reset]').click();const s=document.querySelector('[data-staff-status]');s.value='attention';s.dispatchEvent(new Event('change'));return document.querySelectorAll('[data-staff-row]:not([hidden])').length===1})()")
assert js("(()=>{document.querySelector('[data-staff-reset]').click();const r=document.querySelector('[data-staff-role]');r.value='admissions';r.dispatchEvent(new Event('change'));return document.querySelectorAll('[data-staff-row]:not([hidden])').length===1})()")
assert js("(()=>{document.querySelector('[data-staff-reset]').click();const s=document.querySelector('[data-staff-search]');s.value='no such staff';s.dispatchEvent(new Event('input'));return !document.querySelector('[data-staff-empty]').hidden})()")
js("document.querySelector('[data-staff-reset]').click();document.querySelector('[data-open-dialog=staff-create]').focus();document.querySelector('[data-open-dialog=staff-create]').click()")
assert js("document.querySelector('#staff-create').open && document.querySelector('#staff-create').contains(document.activeElement)")
capture('reception-staff-add-dialog.png')
call('Input.dispatchKeyEvent',{'type':'keyDown','key':'Escape','code':'Escape','windowsVirtualKeyCode':27});call('Input.dispatchKeyEvent',{'type':'keyUp','key':'Escape','code':'Escape','windowsVirtualKeyCode':27})
assert js("!document.querySelector('#staff-create').open")
assert js("document.activeElement.dataset.openDialog==='staff-create'")
js("document.querySelector('[data-open-dialog=staff-edit-2]').click()")
assert js("document.querySelector('#staff-edit-2').open")
capture('reception-staff-edit-dialog.png')
js("document.querySelector('#staff-edit-2 [data-close-dialog]').click();document.querySelector('[data-open-dialog=staff-archive-2]').click()")
assert js("document.querySelector('#staff-archive-2').open && !document.querySelector('#staff-archive-2 [name=confirmed]').checked")
assert js("!document.querySelector('[data-open-dialog=staff-archive-1]')")
js("document.querySelector('#staff-archive-2 [data-close-dialog]').click()")
call('Emulation.setDeviceMetricsOverride',{'width':390,'height':844,'deviceScaleFactor':1,'mobile':True})
assert js("document.documentElement.scrollWidth<=document.documentElement.clientWidth")
capture('reception-staff-directory-mobile.png')
js("document.querySelector('[data-open-dialog=staff-edit-2]').click()")
assert js("(()=>{const r=document.querySelector('#staff-edit-2').getBoundingClientRect();return r.left>=0&&r.right<=innerWidth})()")
capture('reception-staff-edit-mobile.png')
call('Emulation.setDeviceMetricsOverride',{'width':1440,'height':1050,'deviceScaleFactor':1,'mobile':False})
open_fixture('applicant-contacted-filter.html')
responses={status:(root/'reception/fixtures/api'/('applicants-'+status+'.html')).read_text() for status in ['new','contacted','closed']}
js("window.auditResponses="+json.dumps(responses)+";window.auditRequests=[];window.history.replaceState=(state,title,url)=>{window.auditFilterURL=String(url)};window.fetch=async(url)=>{const u=new URL(url);auditRequests.push(u.href);return {ok:true,text:async()=>auditResponses[u.searchParams.get('status')]}}")
for status in ['new','closed','contacted']:
 js("(()=>{const s=document.querySelector('#applicant-filter [name=status]');s.value="+json.dumps(status)+";s.dispatchEvent(new Event('change',{bubbles:true}))})()")
 time.sleep(.15)
 assert js("document.querySelector('#applicant-results').textContent.includes("+json.dumps('Synthetic '+status+' Applicant')+")")
assert js("auditRequests.length===3 && auditRequests.every(u=>new URL(u).searchParams.get('live')==='1')")
assert js("!document.querySelector('#applicant-filter [onchange]')")
for fixture, image in [('visitor-delete-test-data.html','reception-visitor-delete-test-data.png'),('applicant-delete-test-data.html','reception-applicant-delete-test-data.png')]:
 open_fixture(fixture)
 js("(()=>{const d=document.querySelector('#delete-test-data');d.open=true;d.scrollIntoView({block:'start'});})()")
 assert js("(()=>{const b=document.querySelector('#delete-test-data .danger-button');const c=getComputedStyle(b);return c.backgroundColor==='rgb(180, 35, 24)' && c.color==='rgb(255, 255, 255)' && !!b.querySelector('svg[aria-hidden=true]')})()")
 assert js("(()=>{const b=document.querySelector('#delete-test-data .danger-button');return [b,...b.querySelectorAll('*'),document.querySelector('#delete-test-data summary')].every(e=>getComputedStyle(e).cursor==='pointer')})()")
 point=js("(()=>{const r=document.querySelector('#delete-test-data .danger-button').getBoundingClientRect();return {x:r.left+r.width/2,y:r.top+r.height/2}})()")
 call('Input.dispatchMouseEvent',{'type':'mouseMoved',**point})
 time.sleep(.2)
 assert js("getComputedStyle(document.querySelector('#delete-test-data .danger-button')).backgroundColor==='rgb(145, 32, 24)' && getComputedStyle(document.querySelector('#delete-test-data .danger-button span')).cursor==='pointer'")
 call('Input.dispatchMouseEvent',{'type':'mouseMoved','x':10,'y':10})
 time.sleep(.2)
 assert js("(()=>{const f=document.querySelector('#delete-test-data form');return f.elements.reference_confirmation.required && f.elements.confirmed_test_data.required && !f.checkValidity()})()")
 assert js("(()=>{const f=document.querySelector('#delete-test-data form');f.elements.reference_confirmation.value=f.querySelector('label strong').textContent;f.elements.confirmed_test_data.checked=true;return f.checkValidity()})()")
 js("document.querySelector('#delete-test-data form').reset()")
 capture(image)
result={'result':'STAFF_MANAGEMENT_BROWSER_OK','groups':['Search, role/status filters, reset and empty state','Native dialogs, Escape and focus return','Archive confirmation and self-archive protection','390px mobile directory and dialog containment','Applicant selection updates results without submission','Styled visitor/applicant deletion controls and confirmations; no form submission'] + (['Approved legacy account is not flagged and passes browser validation'] if args.legacy_approved else []), 'source':'Deployed HTML and public assets, synthetic database; AJAX responses substituted with deployed server fixture responses','production_records_changed':0}
(out/'staff-management-browser-results.json').write_text(json.dumps(result,indent=2));print(json.dumps(result));ws.close()
