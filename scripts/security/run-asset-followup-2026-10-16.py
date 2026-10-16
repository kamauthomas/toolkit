#!/usr/bin/env python3
"""Incident-specific private archive collection or demo-first backup containment."""
import importlib.util,json,sys,time
from pathlib import Path
ROOT=Path(__file__).resolve().parents[2]
spec=importlib.util.spec_from_file_location('release',ROOT/'scripts/deployment/releases/deploy-reception-graduands-2026.10.15.1.py');m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m)
mode=sys.argv[1];assert mode in ['collect','contain-demo','contain-production']
p=m.Panel();base='/home/bfyigiln/reception-system/storage/app/private/asset-incident-2026-10-16';helper=base+'/followup.php';log=base+'/followup-'+mode+'-'+str(time.time_ns())+'.log'
p.upload(helper,(ROOT/'scripts/security/followup-asset-incident-2026-10-16.php').read_bytes())
cmd='/usr/local/bin/ea-php84 '+helper+' '+mode+' > '+log+' 2>&1'
r=p.api2call('Cron','add_line',command=cmd,minute='*',hour='*',day='*',month='*',weekday='*');assert r.get('event',{}).get('result')==1
try:
 for i in range(19):
  raw=p.fetch(log,optional=True)
  if raw:
   if b'ASSET_FOLLOWUP_FAILED' in raw:raise RuntimeError(raw.decode())
   if b'ASSET_FOLLOWUP_OK' in raw:break
  time.sleep(5)
 else:raise RuntimeError('Private helper timed out')
finally:
 for row in p.api2call('Cron','listcron').get('data',[]):
  if row.get('command')==cmd:
   r=p.api2call('Cron','remove_line',line=row['count']);assert r.get('event',{}).get('result')==1
out=ROOT/'reports/asset-incident-2026-10-16'
out.mkdir(parents=True,exist_ok=True,mode=0o700);(out/'raw-logs').mkdir(exist_ok=True,mode=0o700)
files=['followup-collection.json'] if mode=='collect' else ['backup-containment-'+mode[8:]+'.json']
if mode=='collect':
 manifest=json.loads(p.fetch(base+'/followup-collection.json'))
 for row in manifest['archives']:
  if row.get('chunks'):
   raw=b''.join(p.fetch(base+'/'+chunk['name']) for chunk in row['chunks'])
  else:raw=p.fetch(base+'/'+row['name'])
  import hashlib
  assert hashlib.sha256(raw).hexdigest()==row['sha256']
  dest=out/'raw-logs'/row['name'];dest.write_bytes(raw);dest.chmod(0o600);print('Preserved archive',row['bytes'],'bytes; hash verified',flush=True)
for name in files:
 raw=p.fetch(base+'/'+name);dest=out/('raw-logs' if name.startswith('expanded') else '')/name;dest.write_bytes(raw);dest.chmod(0o600);print('Preserved',name,len(raw),'bytes',flush=True)
print('Completed',mode,'temporary cron removed',flush=True)
