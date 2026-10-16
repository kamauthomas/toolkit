#!/usr/bin/env python3
"""Run a reviewed CLI-only PHP audit privately, with a unique completion log."""
import argparse,importlib.util,time
from pathlib import Path
ROOT=Path(__file__).resolve().parents[2]
def run(helper,outputs,args):
 spec=importlib.util.spec_from_file_location('release',ROOT/'scripts/deployment/releases/deploy-reception-graduands-2026.10.15.1.py');m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m);p=m.Panel()
 base='/home/bfyigiln/reception-system/storage/app/private/platform-security-2026-10-16';nonce=str(time.time_ns());remote=base+'/helper-'+nonce+'.php';log=base+'/run-'+nonce+'.log'
 p.upload(remote,helper.read_bytes())
 assert all(a.replace('-','').replace('_','').isalnum() for a in args)
 cmd='/usr/local/bin/ea-php84 '+remote+(' '+' '.join(args) if args else '')+' > '+log+' 2>&1'
 added=p.api2call('Cron','add_line',command=cmd,minute='*',hour='*',day='*',month='*',weekday='*');assert added.get('event',{}).get('result')==1
 try:
  for i in range(55):
   raw=p.fetch(log,optional=True)
   if raw:
    if b'TOOLKIT_AUDIT_FAILED' in raw:raise RuntimeError(raw.decode())
    if b'TOOLKIT_AUDIT_OK' in raw:break
   time.sleep(5)
  else:raise RuntimeError('Private helper timeout; inspect before retry')
 finally:
  for row in p.api2call('Cron','listcron').get('data',[]):
   if row.get('command')==cmd:
    removed=p.api2call('Cron','remove_line',line=row['count']);assert removed.get('event',{}).get('result')==1
 out=ROOT/'reports/platform-security-2026-10-16';out.mkdir(parents=True,exist_ok=True,mode=0o700)
 for name in outputs:
  raw=p.fetch(base+'/'+name);dest=out/name;dest.parent.mkdir(parents=True,exist_ok=True);dest.write_bytes(raw);dest.chmod(0o600);print('Preserved',name,len(raw),'bytes',flush=True)
 print('Private audit complete; temporary cron removed',flush=True)
if __name__=='__main__':
 parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('helper',type=Path);parser.add_argument('--output',action='append',default=[]);parser.add_argument('--arg',action='append',default=[]);a=parser.parse_args();run(a.helper,a.output,a.arg)
