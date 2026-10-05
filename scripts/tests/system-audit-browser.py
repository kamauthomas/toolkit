#!/usr/bin/env python3
"""Verify public deployed pages through an isolated CDP browser; never submit an application."""
import argparse,base64,json,time
from pathlib import Path
import requests,websocket

p=argparse.ArgumentParser();p.add_argument('base');p.add_argument('output',type=Path);args=p.parse_args();args.output.mkdir(parents=True,exist_ok=True)
page=requests.get('http://localhost:9337/json/list',timeout=10).json()[0]
ws=websocket.create_connection(page['webSocketDebuggerUrl'],origin='http://localhost:9337',timeout=45)
seq=0
def call(method,params=None):
 global seq;seq+=1;ws.send(json.dumps({'id':seq,'method':method,'params':params or {}}))
 while True:
  msg=json.loads(ws.recv())
  if msg.get('id')==seq:
   if 'error' in msg:raise RuntimeError(msg['error'])
   return msg.get('result',{})
def js(expr):
 result=call('Runtime.evaluate',{'expression':expr,'returnByValue':True,'awaitPromise':True})
 if 'exceptionDetails' in result:raise RuntimeError(result['exceptionDetails'])
 return result['result'].get('value')
def navigate(path):
 call('Page.navigate',{'url':args.base+path+'?audit_browser=2026.10.05.4'})
 for _ in range(60):
  time.sleep(.3)
  if js('document.readyState')=='complete':break
 assert js('document.readyState')=='complete'
def screenshot(name):
 data=call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':False})['data'];(args.output/name).write_bytes(base64.b64decode(data))
call('Page.enable');call('Runtime.enable');call('Emulation.setDeviceMetricsOverride',{'width':1440,'height':1000,'deviceScaleFactor':1,'mobile':False})
results=[]
navigate('/apply/')
for _ in range(80):
 if js("Array.from(document.querySelector('[name=county]').options).some(x=>x.value)"):break
 time.sleep(.3)
assert js("Array.from(document.querySelector('[name=county]').options).some(x=>x.value)")
assert js("Array.from(document.scripts).some(s=>s.src.includes('application-form.js')&&s.src.includes('2026.10.05.4'))")
js("""(()=>{const f=document.getElementById('toolkit-application-form');f.elements.first_name.value='Synthetic';f.elements.surname.value='Audit Fixture';f.elements.gender.value='F';document.querySelector('[data-step-target="5"]').click();return true})()""")
assert js("document.querySelectorAll('[data-step-panel]')[1].hidden") is False
js("""(()=>{const f=document.getElementById('toolkit-application-form');for(const [name,value] of Object.entries({email:'test@test',primary_phone:'12345678901234567890',secondary_phone:'12345678901234'})){f.elements[name].value=value;f.elements[name].dispatchEvent(new Event('input',{bubbles:true}));}f.querySelector('[data-next]').click();f.scrollIntoView();return true})()""")
assert js("document.querySelectorAll('[data-step-panel]')[1].hidden") is False
errors=js("Array.from(document.querySelectorAll('.application-field-error')).map(x=>x.textContent).filter(Boolean)")
assert len(errors)==3
screenshot('website-invalid-contacts-desktop.png');results.append({'check':'deployed invalid contacts block progression','passed':True,'errors':errors})
assert js("(()=>{const f=document.getElementById('toolkit-application-form');return ['primary_phone','secondary_phone'].every(n=>f.elements[n+'_country'].value==='KE'&&f.elements[n+'_country'].options.length===245)})()")
js("(()=>{const f=document.getElementById('toolkit-application-form');for(const [n,v] of Object.entries({primary_phone:'0712345678',secondary_phone:'0112345678'})){const e=f.elements[n];e.value=v;e.dispatchEvent(new Event('input',{bubbles:true}));e.dispatchEvent(new Event('blur'));}})()")
assert js("(()=>{const f=document.getElementById('toolkit-application-form');return f.elements.primary_phone.value==='+254712345678'&&f.elements.secondary_phone.value==='+254112345678'})()")
js("(()=>{const f=document.getElementById('toolkit-application-form');for(const [n,c,v] of [['primary_phone','GB','07400123456'],['secondary_phone','US','2015550123']]){f.elements[n+'_country'].value=c;f.elements[n+'_country'].dispatchEvent(new Event('change'));f.elements[n].value=v;f.elements[n].dispatchEvent(new Event('input'));f.elements[n].dispatchEvent(new Event('blur'));}})()")
assert js("(()=>{const f=document.getElementById('toolkit-application-form');return f.elements.primary_phone.value==='+447400123456'&&f.elements.secondary_phone.value==='+12015550123'})()")
js("document.getElementById('toolkit-application-form').elements.primary_phone.scrollIntoView({block:'center'})")
assert js("document.querySelector('[name=primary_phone_country] option[value=KE]').textContent.startsWith('🇰🇪')")
for raw,country,expected in [('254712345678','KE','+254712345678'),('2540712345678','KE','+254712345678'),('+2540712345678','KE','+254712345678'),('447400123456','GB','+447400123456'),('12015550123','US','+12015550123')]:
 js("(()=>{const e=document.getElementById('toolkit-application-form').elements.primary_phone;e.value="+json.dumps(raw)+";e.dispatchEvent(new Event('input'));e.dispatchEvent(new Event('blur'));})()")
 assert js("document.getElementById('toolkit-application-form').elements.primary_phone_country.value")==country
 assert js("document.getElementById('toolkit-application-form').elements.primary_phone.value")==expected
js("(()=>{const e=document.getElementById('toolkit-application-form').elements.primary_phone;e.value='447400123456';e.dispatchEvent(new Event('input'));e.dispatchEvent(new Event('blur'));})()")
screenshot('website-country-phone-selection.png')
js("(()=>{const f=document.getElementById('toolkit-application-form');f.elements.primary_phone_country.value='KE';f.elements.primary_phone_country.dispatchEvent(new Event('change'));})()")
assert js("document.getElementById('toolkit-application-form').elements.primary_phone.validationMessage.length>0")
js("(()=>{const f=document.getElementById('toolkit-application-form');const e=f.elements.primary_phone;e.value='+447400123456';e.dispatchEvent(new Event('input'));e.dispatchEvent(new Event('blur'));})()")
assert js("document.getElementById('toolkit-application-form').elements.primary_phone_country.value==='GB'")
js("(()=>{const f=document.getElementById('toolkit-application-form');for(const n of ['primary_phone','secondary_phone']){f.elements[n+'_country'].value='KE';f.elements[n].value='';f.elements[n+'_country'].dispatchEvent(new Event('change'));}})()")
results.append({'check':'245 flagged country choices; typed bare/plus calling code detection and trunk normalization; default Kenya; +254 conversion; independent overseas contacts; mismatches blocked; pasted international prefix selects country','passed':True})

js("""(()=>{const f=document.getElementById('toolkit-application-form');f.elements.email.value='fixture@example.com';f.elements.primary_phone.value='0712345678';f.elements.secondary_phone.value='0112345678';const county=Array.from(f.elements.county.options).find(x=>x.value);if(county)f.elements.county.value=county.value;document.querySelector('[data-step-target="5"]').click();return true})()""")
assert js("document.querySelectorAll('[data-step-panel]')[2].hidden") is False
results.append({'check':'direct jump validates intervening course selection','passed':True})
# Revisit invalid contacts at a phone-sized viewport. No submit action is used.
call('Emulation.setDeviceMetricsOverride',{'width':390,'height':844,'deviceScaleFactor':1,'mobile':True})
js("document.querySelector('[data-step-target=\"1\"]').click();const e=document.getElementById('toolkit-application-form').elements.email;e.value='audit@com';e.dispatchEvent(new Event('input'));e.scrollIntoView({block:'center'});")
assert js("document.documentElement.scrollWidth<=document.documentElement.clientWidth")
screenshot('website-invalid-email-mobile.png')
call('Emulation.setDeviceMetricsOverride',{'width':1440,'height':1000,'deviceScaleFactor':1,'mobile':False})
navigate('/testimonials/')
assert js("Array.from(document.scripts).some(s=>s.src.includes('home-experience.js')&&s.src.includes('2026.10.05.4'))")
assert js("document.querySelectorAll('.toolkit-video-facade[href^=\"https://www.youtube.com/watch\"]').length")==3
ids=js("Array.from(document.querySelectorAll('.toolkit-video-facade')).map(x=>x.dataset.videoId)")
js("document.querySelector('.toolkit-video-facade').scrollIntoView({block:'center'});");screenshot('testimonials-play-control.png')
js("document.querySelector('.toolkit-video-facade').click();")
assert js("document.querySelector('.testimonial-story__media iframe').src").startswith('https://www.youtube-nocookie.com/embed/'+ids[0])
time.sleep(2);screenshot('testimonials-activated-player.png')
for video_id in ids[1:]:
 js("document.querySelector('.toolkit-video-facade').click();")
 assert js("Array.from(document.querySelectorAll('.testimonial-story__media iframe')).some(x=>x.src.includes('/embed/"+video_id+"'))")
results.append({'check':'all three deployed testimonial links activate embedded players','passed':True,'video_ids':ids})
(args.output/'browser-results.json').write_text(json.dumps({'base':args.base,'release':'2026.10.05.4','checks':results,'production_submissions':0,'note':'Video iframe activation is verified; external playback availability is assessed separately.'},indent=2))
print('PASS:',len(results),'deployed browser checks;',len(list(args.output.glob('*.png'))),'screenshots; no application submission')
ws.close()
