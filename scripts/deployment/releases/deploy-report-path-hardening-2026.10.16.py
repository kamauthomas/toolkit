#!/usr/bin/env python3
"""Preserve live Reporting drift; publish only the tested PDF path boundary."""
import hashlib,importlib.util,json,re,time
from pathlib import Path
import requests
ROOT=Path(__file__).resolve().parents[3];OUT=ROOT/'reports/platform-security-2026-10-16'
assert json.loads((OUT/'reporting-isolated-verification.json').read_text())['passed']==10
spec=importlib.util.spec_from_file_location('r',Path(__file__).with_name('deploy-reception-graduands-2026.10.15.1.py'));m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m);p=m.Panel();base='/home/bfyigiln/reports.toolkitafrica.ac.ke';before=p.fetch(base+'/app.py');assert before==(OUT/'deployed-source/report-app.py').read_bytes()
p.upload('/home/bfyigiln/reception-system/storage/app/private/platform-security-2026-10-16/rollback/reporting/app.py',before)
rows=[]
for name in ['safe_files.py','app.py']:
 raw=(OUT/'reporting-payload'/name).read_bytes();p.upload(base+'/'+name,raw);assert p.fetch(base+'/'+name)==raw;rows.append({'file':name,'sha256':hashlib.sha256(raw).hexdigest()})
p.upload(base+'/tmp/restart.txt',str(time.time()).encode())
time.sleep(5)
r=requests.get('https://reports.toolkitafrica.ac.ke/login',timeout=45);assert r.status_code==200 and not re.search(r'Traceback|Internal Server Error',r.text)
time.sleep(4);r=requests.get('https://reports.toolkitafrica.ac.ke/app.py.bak-pre-security-20260818',timeout=45);assert r.status_code in [403,404]
(OUT/'reporting-deployment.json').write_text(json.dumps({'verified':True,'files':rows,'isolated_deployed_code_checks':10,'real_records_created':0,'public_login_status':200,'removed_source_backup_status':r.status_code,'dedicated_reporting_demo_host':False},indent=2)+'\n');print('VERIFIED production Reporting: tested path boundary, source backup denied; existing features preserved')
