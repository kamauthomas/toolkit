#!/usr/bin/env python3
"""Add the graduands QR and display control to the existing Toolkit Control."""
import argparse,hashlib,importlib.util,json,subprocess
from pathlib import Path
import requests
ROOT=Path(__file__).resolve().parents[3]
spec=importlib.util.spec_from_file_location('previous_release',Path(__file__).with_name('deploy-reception-graduands-2026.10.15.1.py'));release=importlib.util.module_from_spec(spec);spec.loader.exec_module(release)
parser=argparse.ArgumentParser();parser.add_argument('environment',choices=['demo','production']);args=parser.parse_args();env=args.environment
out=ROOT/'reports/graduands-2026-10-15/admin-control'/env;out.mkdir(parents=True,exist_ok=True)
if env=='production':assert (out.parent/'demo/verification.json').exists(),'Demo verification required'
rollback=ROOT/'rollbacks'/('graduands-admin-'+env+'-2026.10.15.2');assert not rollback.exists(),'Inspect existing rollback before a repeated deploy';rollback.mkdir(mode=0o700,parents=True)
base='https://'+('demo.' if env=='demo' else '')+'toolkitafrica.ac.ke';remote=('/home/bfyigiln/demo.toolkitafrica.ac.ke' if env=='demo' else '/home/bfyigiln/public_html')+'/wp-content/themes/eduma-child';source=ROOT/'wp-content/themes/eduma-child';panel=release.Panel()
files=['assets/images/graduands-2026-qr.png','assets/images/graduands-2026-qr.svg','inc/graduands.php','inc/site-metrics.php','functions.php']
for rel in files:
 before=panel.fetch(remote+'/'+rel,optional=rel.startswith('assets/'))
 if rel.startswith('assets/'):
  assert before is None,'Unexpected existing QR file'
 else:
  assert before==(out.parent/'baseline'/env/rel).read_bytes(),rel+' drifted'
  dest=rollback/rel;dest.parent.mkdir(parents=True,exist_ok=True);dest.write_bytes(before)
 dest=out/'payload'/rel;dest.parent.mkdir(parents=True,exist_ok=True);dest.write_bytes((source/rel).read_bytes())
 if rel.endswith('.php'):subprocess.run(['php','-l',str(dest)],check=True,stdout=subprocess.DEVNULL)
for rel in files:panel.upload(remote+'/'+rel,(source/rel).read_bytes())
rows=[]
for rel in files:
 if rel.startswith('assets/'):
  response=requests.get(base+'/wp-content/themes/eduma-child/'+rel+'?v=2026.10.15.2',timeout=40);response.raise_for_status();raw=response.content
 else:raw=panel.fetch(remote+'/'+rel)
 assert raw==(source/rel).read_bytes(),rel+' mismatched'
 dest=out/'verified'/rel;dest.parent.mkdir(parents=True,exist_ok=True);dest.write_bytes(raw);rows.append({'file':rel,'sha256':hashlib.sha256(raw).hexdigest()})
response=requests.get(base+'/graduation/?qr_admin_check=2026.10.15.2',timeout=45);response.raise_for_status()
for path in ['/graduation/','/']:
 response=requests.get(base+path,timeout=45);response.raise_for_status();assert response.headers.get('X-Toolkit-Release')=='2026.10.15.2'
 if path=='/graduation/':assert 'confirmed list is being prepared' in response.text
(out/'verification.json').write_text(json.dumps({'environment':env,'release':'2026.10.15.2','files':rows,'public_roster':'pending','roster_records_modified':0,'admin_signed_in_acceptance':'not performed'},indent=2)+'\n')
print('VERIFIED',env,'Toolkit Control QR release; five byte-matched files; public list remains pending')
