#!/usr/bin/env python3
"""Inspect locally rendered synthetic workspace pages in an isolated Brave CDP session."""
import base64
import json
import time
from pathlib import Path
import requests
import websocket

OUT=Path(__file__).resolve().parents[2]/'reports/calling-letter-workspace-2026-10-26/browser'
OUT.mkdir(parents=True,exist_ok=True,mode=0o700)
origin='http://localhost:9341'
page=requests.get(origin+'/json/list',timeout=5).json()[0]
ws=websocket.create_connection(page['webSocketDebuggerUrl'],origin=origin,timeout=15)
sequence=0; checks=[]
def call(method,params=None):
    global sequence
    sequence+=1; current=sequence;ws.send(json.dumps({'id':current,'method':method,'params':params or {}}))
    while True:
        message=json.loads(ws.recv())
        if message.get('id')==current:
            assert 'error' not in message
            return message.get('result',{})
def js(code):
    result=call('Runtime.evaluate',{'expression':code,'returnByValue':True})
    assert not result.get('exceptionDetails'), 'Browser evaluation failed'
    return result.get('result',{}).get('value')
def check(ok,label):
    assert ok,label
    checks.append(label)
call('Page.enable');call('Runtime.enable')
for name in ['workspace','create','review','manual','native','register']:
    for width in [1440,390]:
        call('Emulation.setDeviceMetricsOverride',{'width':width,'height':1000 if width>600 else 844,'deviceScaleFactor':1,'mobile':width<600})
        call('Page.navigate',{'url':'http://127.0.0.1:8876/'+name+'.html'})
        for _ in range(80):
            if js("document.readyState==='complete' && Boolean(document.querySelector('.letters-workspace'))"):break
            time.sleep(.1)
        check(js("document.documentElement.scrollWidth<=innerWidth+1"),name+' contains mobile/desktop width '+str(width))
        check(js("[...document.images].every(i=>i.complete && i.naturalWidth>0)"),name+' official logos load '+str(width))
        check(js("getComputedStyle(document.querySelector('.letters-hero')).borderRadius==='16px'"),name+' dedicated stylesheet loaded '+str(width))
        check(js("[...document.querySelectorAll('.letter-button:not(:disabled)')].every(b=>getComputedStyle(b).cursor==='pointer' && b.getBoundingClientRect().height>=43)"),name+' usable action targets '+str(width))
        data=call('Page.captureScreenshot',{'format':'png','captureBeyondViewport':True})
        (OUT/(name+'-'+str(width)+'.png')).write_bytes(base64.b64decode(data['data']))
        if name=='workspace' and width==1440:
            js("window.fetch=async(url,options)=>{window.searchRequest={method:options.method,page:options.body.get('page'),query:options.body.get('search')};return new Response(document.documentElement.outerHTML.replace('Sample Recipient 5','Filtered Recipient'));};const i=document.querySelector('#letter-search-input');i.value='Sample';i.dispatchEvent(new Event('input'));true")
            time.sleep(.6)
            check(js("window.searchRequest.method==='POST' && window.searchRequest.query==='Sample' && window.searchRequest.page==='1' && document.body.innerText.includes('Filtered Recipient')"),'search applies without click and keeps contact query out of URL')
            js("window.fetch=async()=>{throw new Error('Synthetic unavailable');};document.querySelector('#letter-search').requestSubmit();true")
            time.sleep(.2)
            check(js("document.querySelector('[data-letter-search-status]').innerText.includes('could not be completed') && document.body.innerText.includes('Filtered Recipient')"),'failed search preserves results and reports failure')
(OUT/'checks.json').write_text(json.dumps({'passed':len(checks),'checks':checks},indent=2)+'\n')
print('PASS:',len(checks),'desktop/mobile layout, logos, action sizes and progressive-search checks; synthetic local pages only.')
ws.close()
