#!/usr/bin/env python3
"""Demo-first exact runtime deployment; no public test application or audit helper."""
import argparse,hashlib,importlib.util,json,re,shlex,subprocess,time,uuid
from pathlib import Path
import requests
ROOT=Path(__file__).resolve().parents[3];CASE=ROOT/'reports/toolkit-admin-sync-2026-10-26';RELEASE='2026.10.26.1'
def load(path,name):
 spec=importlib.util.spec_from_file_location(name,path);module=importlib.util.module_from_spec(spec);spec.loader.exec_module(module);return module
PREP=load(Path(__file__).with_name('prepare-admin-sync-2026.10.26.1.py'),'prepare_release')
POLICY=load(ROOT/'scripts/deployment/runtime_payload.py','payload_policy')
def digest(data):return hashlib.sha256(data).hexdigest()
def run(env):
 manifest=json.loads((CASE/f'release-manifest-{env}.json').read_text());assert manifest['release']==RELEASE and len(manifest['files'])==19
 if env=='production':assert json.loads((CASE/'deployment/demo/verification.json').read_text())['verified']
 out=CASE/'deployment'/env;out.mkdir(parents=True,mode=0o700,exist_ok=True)
 if (out/'attempt.json').exists():raise RuntimeError('Existing attempt: reconcile its saved state before any retry')
 allowed={'wordpress':set(PREP.WP_EXISTING+PREP.WP_NEW),'reception':set(PREP.RECEPTION_EXISTING+PREP.RECEPTION_NEW)}
 expected={(c,n) for c,names in allowed.items() for n in names};assert {(r['component'],r['file']) for r in manifest['files']}==expected
 rows={(r['component'],r['file']):r for r in manifest['files']}
 for r in rows.values():
  POLICY.assert_runtime_upload(r['remote']);p=(CASE/r['payload']).resolve();assert p.is_relative_to((CASE/'release-payload'/env/r['component']).resolve()) and digest(p.read_bytes())==r['sha256']
  actual=('/home/bfyigiln/'+('demo.toolkitafrica.ac.ke' if env=='demo' else 'public_html')+'/wp-content/themes/eduma-child/' if r['component']=='wordpress' else '/home/bfyigiln/reception-system'+('-demo' if env=='demo' else '')+'/')+r['file'];assert r['remote']==actual
 panel_module=load(Path(__file__).with_name('deploy-reception-graduands-2026.10.15.1.py'),'existing_panel')
 panel=panel_module.Panel();original=panel.s.request;last=[0.0];blocked=[False]
 def paced(method,url,**kwargs):
  if blocked[0]:raise RuntimeError('Remote work stopped after refused/failed request')
  time.sleep(max(0,4-(time.monotonic()-last[0])));kwargs['allow_redirects']=False;kwargs['timeout']=(10,40)
  try:r=original(method,url,**kwargs)
  except requests.RequestException:blocked[0]=True;raise RuntimeError('Hosting request failed; stopped') from None
  last[0]=time.monotonic()
  if r.status_code!=200 or r.headers.get('Retry-After'):blocked[0]=True;raise RuntimeError('Hosting response refused; stopped')
  return r
 panel.s.request=paced
 # No mutation before every existing/new file has a matching fresh baseline.
 for r in rows.values():
  raw=panel.fetch(r['remote'],optional=r['baseline_sha256'] is None)
  if (None if raw is None else digest(raw))!=r['baseline_sha256']:raise RuntimeError('Fresh server baseline drift: '+r['component']+'/'+r['file'])
  if raw is not None:
   p=out/'before'/r['component']/r['file'];p.parent.mkdir(parents=True,exist_ok=True);p.write_bytes(raw);p.chmod(0o600)
 print(env,'all 19 fresh baseline checks passed',flush=True)
 attempt={'environment':env,'release':RELEASE,'uploaded':[],'cron_removed':None,'verified':False};attempt_path=out/'attempt.json'
 def journal():attempt_path.write_text(json.dumps(attempt,indent=2)+'\n')
 journal()
 def upload(component,name):
  r=rows[component,name];data=(CASE/r['payload']).read_bytes();before=panel.fetch(r['remote'],optional=r['baseline_sha256'] is None)
  if (None if before is None else digest(before))!=r['baseline_sha256']:raise RuntimeError('Concurrent server change: '+name)
  panel.upload(r['remote'],data)
  if panel.fetch(r['remote'])!=data:raise RuntimeError('Uploaded bytes differ: '+name)
  attempt['uploaded'].append(component+'/'+name);journal();print(env,'verified runtime file',component+'/'+name,flush=True)
 wporder=['inc/toolkit-admin.php','inc/reception-application-sync.php','inc/site-metrics.php','inc/support-hub.php','inc/application-adapter.php','inc/graduands.php','assets/css/toolkit-admin.css','functions.php']
 for name in wporder:upload('wordpress',name)
 # Hold the periodic schedule until the first successful migration and sync.
 for name in PREP.RECEPTION_NEW+[n for n in PREP.RECEPTION_EXISTING if n!='routes/console.php']:upload('reception',name)
 private='/home/bfyigiln/reception-system'+('-demo' if env=='demo' else '');site='/home/bfyigiln/'+('demo.toolkitafrica.ac.ke' if env=='demo' else 'public_html')
 nonce=uuid.uuid4().hex;marker='/tmp/toolkit-admin-sync-'+nonce;log=private+'/storage/logs/toolkit-admin-sync-'+nonce+'.log'
 wpcode='define("DISABLE_WP_CRON",true);require '+json.dumps(site+'/wp-load.php')+';update_option("toolkit_reception_application_sync_enabled",1,false);if(!toolkit_reception_application_sync_enabled())exit(3);echo "WEBSITE_SYNC_ENABLED\\n";'
 php='/usr/local/bin/ea-php84';wpphp='/usr/local/bin/ea-php84' if env=='demo' else '/usr/local/bin/ea-php83'
 tasks=[shlex.join([php,private+'/artisan','config:clear']),shlex.join([php,private+'/artisan','route:clear']),shlex.join([php,private+'/artisan','view:clear']),shlex.join([php,private+'/artisan','migrate','--force','--path=database/migrations/2026_10_26_090000_create_website_applications_table.php']),shlex.join([wpphp,'-d','memory_limit=512M','-r',wpcode]),shlex.join([php,private+'/artisan','reception:sync-website-applications'])]
 inner='umask 077; if mkdir '+shlex.quote(marker)+' 2>/dev/null; then { '+ ' && '.join(tasks)+'; code=$?; echo TOOLKIT_ADMIN_SYNC_EXIT=$code; } > '+shlex.quote(log)+' 2>&1; fi'
 command='/bin/sh -c '+shlex.quote(inner);assert '\n' not in command and '%' not in command
 added=panel.api2call('Cron','add_line',command=command,minute='*',hour='*',day='*',month='*',weekday='*');assert added.get('event',{}).get('result')==1
 attempt.update({'maintenance_command':command,'maintenance_log':log,'one_attempt_guard':marker,'cron_removed':False});journal()
 raw=None
 try:
  for _ in range(18):
   raw=panel.fetch(log,optional=True)
   if raw and b'TOOLKIT_ADMIN_SYNC_EXIT=' in raw:break
   time.sleep(5)
  else:raise RuntimeError('Maintenance completion not observed; reconcile before retry')
 finally:
  if not blocked[0]:
   for row in panel.api2call('Cron','listcron').get('data',[]):
    if row.get('command')==command:assert panel.api2call('Cron','remove_line',line=row['count']).get('event',{}).get('result')==1
   attempt['cron_removed']=not any(row.get('command')==command for row in panel.api2call('Cron','listcron').get('data',[]));journal()
 if raw is not None:(out/'maintenance.private.log').write_bytes(raw);(out/'maintenance.private.log').chmod(0o600)
 if not raw or b'TOOLKIT_ADMIN_SYNC_EXIT=0' not in raw:raise RuntimeError('Migration/activation/sync failed; inspect private maintenance receipt')
 summaries=[]
 for line in raw.decode(errors='replace').splitlines():
  try:value=json.loads(line)
  except ValueError:continue
  if isinstance(value,dict) and 'status'in value:summaries.append(value)
 expected_source='demo' if env=='demo' else 'production'
 if not summaries or summaries[-1].get('status')!='synced' or summaries[-1].get('source')!=expected_source:raise RuntimeError('Initial sync was not confirmed')
 attempt['initial_sync']=summaries[-1];journal();upload('reception','routes/console.php')
 jobs=panel.api2call('Cron','listcron').get('data',[])
 if not any(private+'/artisan schedule:run' in row.get('command','') for row in jobs):raise RuntimeError('Reception scheduler is not installed; do not claim recurring sync')
 # Only ordinary public release requests; never submit a business form or probe paths.
 base='https://'+('demo.' if env=='demo' else '')+'toolkitafrica.ac.ke';checks=[]
 for url in [base+'/?toolkit_release='+RELEASE,base+'/',base+'/wp-content/themes/eduma-child/assets/css/toolkit-admin.css', 'https://reception'+('-demo' if env=='demo' else '')+'.toolkitafrica.ac.ke/login']:
  # Never send the cPanel session cookies to a public application/asset request.
  time.sleep(4)
  try:r=requests.get(url,timeout=(10,40),allow_redirects=False)
  except requests.RequestException:blocked[0]=True;raise RuntimeError('Public release request failed; stopped') from None
  if r.status_code!=200 or r.headers.get('Retry-After'):blocked[0]=True;raise RuntimeError('Public release response refused; stopped')
  if url==base+'/':assert r.headers.get('X-Toolkit-Release')==RELEASE
  if url.endswith('toolkit-admin.css'):assert digest(r.content)==rows['wordpress','assets/css/toolkit-admin.css']['sha256']
  checks.append({'url':url,'status':r.status_code})
 attempt.update({'verified':True,'files':[{k:r[k] for k in ['component','file','sha256']} for r in rows.values()],'public_checks':checks,'test_applications_submitted':0,'recurring_sync':'Existing Reception scheduler; new task every five minutes; one sync worker at a time.'});journal()
 (out/'verification.json').write_text(json.dumps(attempt,indent=2)+'\n');print('VERIFIED',env,'19 runtime files, schema, first read-only sync and recurring schedule; no test applications',flush=True)

if __name__=='__main__':
 parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('environment',choices=['demo','production']);parser.add_argument('--execute',action='store_true');args=parser.parse_args()
 if not args.execute:raise SystemExit('No remote action. Prepare/review payload and tests, then explicitly use --execute.')
 try:run(args.environment)
 except Exception as exc:print('STOPPED:',str(exc) if isinstance(exc,RuntimeError) else type(exc).__name__,'— inspect the private attempt/receipt; do not retry blindly',flush=True);raise SystemExit(1)
