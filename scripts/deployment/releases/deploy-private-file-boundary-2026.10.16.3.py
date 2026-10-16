#!/usr/bin/env python3
"""Publish private-artifact confinement without replacing unrelated live code."""
import argparse,hashlib,importlib.util,json,re,time
from pathlib import Path
import requests
ROOT=Path(__file__).resolve().parents[3];OUT=ROOT/'reports/platform-security-2026-10-16';spec=importlib.util.spec_from_file_location('r',Path(__file__).with_name('deploy-reception-graduands-2026.10.15.1.py'));m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m)
def run(env):
 if env=='production':assert json.loads((OUT/'private-boundary-demo.json').read_text())['verified']
 p=m.Panel();remote='/home/bfyigiln/'+('demo.toolkitafrica.ac.ke' if env=='demo' else 'public_html')+'/wp-content/themes/eduma-child';payload={};before={}
 for name in ['inc/private-file-boundary.php','inc/calling-letters.php','inc/calling-letter-pdf.php','functions.php']:
  raw=p.fetch(remote+'/'+name,optional=name=='inc/private-file-boundary.php');before[name]=raw
  if name=='inc/private-file-boundary.php':assert raw is None
  elif name=='functions.php':assert raw.count(b"return '2026.10.16.2';")==1
  else:assert raw==(OUT/'deployed-source'/Path(name).name).read_bytes(),'Live baseline changed: '+name
  payload[name]=raw.replace(b"return '2026.10.16.2';",b"return '2026.10.16.3';") if name=='functions.php' else (ROOT/'wp-content/themes/eduma-child'/name).read_bytes()
  if raw is not None:p.upload('/home/bfyigiln/reception-system/storage/app/private/platform-security-2026-10-16/rollback/private-boundary/'+env+'/'+name,raw)
 for name,data in payload.items():
  assert p.fetch(remote+'/'+name,optional=before[name] is None)==before[name]
  p.upload(remote+'/'+name,data);assert p.fetch(remote+'/'+name)==data
 base='https://'+('demo.' if env=='demo' else '')+'toolkitafrica.ac.ke'
 time.sleep(4);r=requests.get(base+'/?private_boundary=2026.10.16.3',timeout=45);assert r.status_code==200
 observations=[]
 for path in ['/','/apply/']:
  time.sleep(4);r=requests.get(base+path,timeout=45);assert r.status_code==200 and r.headers.get('X-Toolkit-Release')=='2026.10.16.3';assert not re.search(r'Fatal error|Uncaught Error|Stack trace:',r.text);observations.append({'path':path,'status':r.status_code,'release':r.headers.get('X-Toolkit-Release')})
 (OUT/('private-boundary-'+env+'.json')).write_text(json.dumps({'verified':True,'release':'2026.10.16.3','files':{name:hashlib.sha256(data).hexdigest() for name,data in payload.items()},'public_checks':observations,'test_business_records':0},indent=2)+'\n');print('VERIFIED',env,'private artifact boundary .3',flush=True)
if __name__=='__main__':
 a=argparse.ArgumentParser();a.add_argument('environment',choices=['demo','production']);run(a.parse_args().environment)
