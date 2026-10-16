#!/usr/bin/env python3
"""Small baseline: known inventory paths only; no response bodies retained."""
import datetime,json,re,time
from pathlib import Path
import requests
OUT=Path(__file__).resolve().parents[2]/'reports/platform-security-2026-10-16'
checks=[('website','https://toolkitafrica.ac.ke','/wp-content/themes/eduma.zip'),('website','https://toolkitafrica.ac.ke','/error_log'),('website','https://toolkitafrica.ac.ke','/readme.html'),('website-demo','https://demo.toolkitafrica.ac.ke','/readme.html'),('reports','https://reports.toolkitafrica.ac.ke','/app.py.bak-pre-security-20260818'),('reports','https://reports.toolkitafrica.ac.ke','/app.py'),('reception','https://reception.toolkitafrica.ac.ke','/error_log')]
rows=[]
for i,(name,base,path) in enumerate(checks):
 if i:time.sleep(4)
 with requests.get(base+path,timeout=(15,30),allow_redirects=False,stream=True,headers={'User-Agent':'Toolkit-authorized-security-review/2026-10-16'}) as r:
  body=r.raw.read(2048,decode_content=True);text=body.decode(errors='replace')
  rows.append({'system':name,'path':path,'status':r.status_code,'archive_signature':body.startswith(b'PK\x03\x04'),'source_marker':bool(re.search(r'from flask import|import os|def [a-z_]+\(|<\?php',text)),'error_log_marker':bool(re.search(r'PHP (?:Warning|Fatal|Notice)|Stack trace|\[.*(?:UTC|Africa/Nairobi)\]',text)),'utc':datetime.datetime.now(datetime.timezone.utc).isoformat()});print(name,path,r.status_code,flush=True)
  if r.status_code==429 or r.headers.get('Retry-After') or re.search(r'cf-chl-|your ip (?:has been|is) blocked|temporarily blocked',text,re.I):break
(OUT/'baseline-exposure-checks.json').write_text(json.dumps({'results':rows,'response_bodies_saved':False,'minimum_pause_seconds':4},indent=2)+'\n')
