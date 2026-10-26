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
def matching_scheduler(command,private):
 try:tokens=shlex.split(command)
 except ValueError:return False
 direct=any(tokens[i]==private+'/artisan' and tokens[i+1]=='schedule:run' for i in range(len(tokens)-1))
 relative=any(tokens[i]=='cd' and tokens[i+1]==private for i in range(len(tokens)-1)) and any(tokens[i]=='artisan' and tokens[i+1]=='schedule:run' for i in range(len(tokens)-1))
 return direct or relative

def ensure_schedule(panel,private):
 jobs=panel.api2call('Cron','listcron');assert jobs.get('event',{}).get('result')==1
 cadence=('minute','hour','day','month','weekday')
 if any(matching_scheduler(r.get('command',''),private) and all(str(r.get(k))=='*' for k in cadence) for r in jobs.get('data',[])):
  return 'Existing every-minute Reception scheduler; sync task every five minutes.'
 command=shlex.join(['/usr/local/bin/ea-php84',private+'/artisan','reception:sync-website-applications'])+' > /dev/null 2>&1'
 matches=[r for r in jobs.get('data',[]) if r.get('command')==command]
 if not matches:
  added=panel.api2call('Cron','add_line',command=command,minute='*/5',hour='*',day='*',month='*',weekday='*');assert added.get('event',{}).get('result')==1
 jobs=panel.api2call('Cron','listcron');assert jobs.get('event',{}).get('result')==1
 matches=[r for r in jobs.get('data',[]) if r.get('command')==command]
 assert len(matches)==1 and matches[0].get('minute')=='*/5' and all(str(matches[0].get(k))=='*' for k in cadence[1:])
 return 'Dedicated read-only sync cron every five minutes; one sync worker at a time.'

def run(env,resume_setup=False,finish=False):
 manifest=json.loads((CASE/f'release-manifest-{env}.json').read_text());assert manifest['release']==RELEASE and len(manifest['files'])==19
 if env=='production':
  demo=json.loads((CASE/'deployment/demo/verification.json').read_text());assert demo['verified'] and demo['environment']=='demo' and demo['release']==RELEASE
  assert {(r['component'],r['file'],r['sha256']) for r in demo['files']}=={(r['component'],r['file'],r['sha256']) for r in manifest['files']}
 out=CASE/'deployment'/env;out.mkdir(parents=True,mode=0o700,exist_ok=True)
 previous=None
 if (out/'attempt.json').exists():
  if not (resume_setup or finish):raise RuntimeError('Existing attempt: reconcile its saved state before any retry')
  previous=json.loads((out/'attempt.json').read_text());receipt=(out/'maintenance.private.log').read_bytes()
  assert env=='demo' and previous['cron_removed'] is True and previous['verified'] is False
  if finish:
   assert not resume_setup and len(previous['uploaded'])==19 and previous.get('initial_sync',{}).get('status')=='synced' and previous['initial_sync']['source']=='demo' and b'TOOLKIT_ADMIN_SYNC_EXIT=0' in receipt
  else:assert b"Invalid default value for 'source_updated_at'" in receipt and b'TOOLKIT_ADMIN_SYNC_EXIT=1' in receipt
 elif resume_setup or finish:raise RuntimeError('No known partial deployment to reconcile')
 allowed={'wordpress':set(PREP.WP_EXISTING+PREP.WP_NEW),'reception':set(PREP.RECEPTION_EXISTING+PREP.RECEPTION_NEW)}
 expected={(c,n) for c,names in allowed.items() for n in names};assert {(r['component'],r['file']) for r in manifest['files']}==expected
 rows={(r['component'],r['file']):r for r in manifest['files']}
 migration='database/migrations/2026_10_26_090000_create_website_applications_table.php'
 expected_before={key:r['baseline_sha256'] for key,r in rows.items()}
 if previous:
  done={c+'/'+n for c,n in rows if finish or n!='routes/console.php'}
  assert set(previous['uploaded'])==done and len(previous['uploaded'])==(19 if finish else 18)
  for key,r in rows.items():
   if key[0]+'/'+key[1] in done:expected_before[key]=r['sha256']
  # This is the exact failed CREATE TABLE version, not an arbitrary retry baseline.
  if not finish:
   original_migration=subprocess.check_output(['git','-C',str(ROOT.parent/'reception-system'),'show','eb5508a:'+migration])
   expected_before['reception',migration]=digest(original_migration)
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
  if (None if raw is None else digest(raw))!=expected_before[r['component'],r['file']]:raise RuntimeError('Fresh server baseline drift: '+r['component']+'/'+r['file'])
  if raw is not None:
   p=out/('reconciled-before' if previous else 'before')/r['component']/r['file'];p.parent.mkdir(parents=True,exist_ok=True);p.write_bytes(raw);p.chmod(0o600)
 print(env,'all 19 fresh baseline checks passed',flush=True)
 attempt=previous or {'environment':env,'release':RELEASE,'uploaded':[],'cron_removed':None,'verified':False};attempt_path=out/'attempt.json'
 if resume_setup:
  (out/'failed-timestamp-setup.private.log').write_bytes(receipt)
  attempt['reconciled_failure']='Failed CREATE TABLE with MySQL legacy timestamp defaults; all 18 uploaded files rechecked; only DATETIME migration corrected.'
 def journal():attempt_path.write_text(json.dumps(attempt,indent=2)+'\n')
 journal()
 def upload(component,name):
  r=rows[component,name];data=(CASE/r['payload']).read_bytes();before=panel.fetch(r['remote'],optional=r['baseline_sha256'] is None)
  if (None if before is None else digest(before))!=expected_before[component,name]:raise RuntimeError('Concurrent server change: '+name)
  panel.upload(r['remote'],data)
  if panel.fetch(r['remote'])!=data:raise RuntimeError('Uploaded bytes differ: '+name)
  if component+'/'+name not in attempt['uploaded']:attempt['uploaded'].append(component+'/'+name)
  journal();print(env,'verified runtime file',component+'/'+name,flush=True)
 if not finish:
  wporder=['inc/toolkit-admin.php','inc/reception-application-sync.php','inc/site-metrics.php','inc/support-hub.php','inc/application-adapter.php','inc/graduands.php','assets/css/toolkit-admin.css','functions.php']
  if not previous:
   for name in wporder:upload('wordpress',name)
  # Hold the periodic schedule until the first successful migration and sync.
  for name in ([migration] if previous else PREP.RECEPTION_NEW+[n for n in PREP.RECEPTION_EXISTING if n!='routes/console.php']):upload('reception',name)
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
 private='/home/bfyigiln/reception-system'+('-demo' if env=='demo' else '')
 schedule=ensure_schedule(panel,private);attempt['recurring_sync']=schedule;journal()
 # Only ordinary public release requests; never submit a business form or probe paths.
 base='https://'+('demo.' if env=='demo' else '')+'toolkitafrica.ac.ke';checks=[]
 for url in [base+'/?toolkit_release='+RELEASE,base+'/',base+'/wp-content/themes/eduma-child/assets/css/toolkit-admin.css', 'https://reception'+('-demo' if env=='demo' else '')+'.toolkitafrica.ac.ke/login']:
  # Never send the cPanel session cookies to a public application/asset request.
  time.sleep(4)
  try:r=requests.get(url,timeout=(10,40),allow_redirects=False)
  except requests.RequestException:blocked[0]=True;raise RuntimeError('Public release request failed; stopped') from None
  if r.status_code!=200 or r.headers.get('Retry-After'):blocked[0]=True;raise RuntimeError('Public release response refused; stopped')
  if url==base+'/':assert r.headers.get('X-Toolkit-Release')==RELEASE and 'ver='+RELEASE in r.text
  assert not re.search(r'PHP Fatal error|Fatal error:|Uncaught (?:Error|Exception)|There has been a critical error on this website',r.text,re.I)
  if url.endswith('toolkit-admin.css'):assert digest(r.content)==rows['wordpress','assets/css/toolkit-admin.css']['sha256']
  checks.append({'url':url,'status':r.status_code})
 attempt.update({'verified':True,'files':[{k:r[k] for k in ['component','file','sha256']} for r in rows.values()],'public_checks':checks,'test_applications_submitted':0,'recurring_sync':schedule});journal()
 (out/'verification.json').write_text(json.dumps(attempt,indent=2)+'\n');print('VERIFIED',env,'19 runtime files, schema, first read-only sync and recurring schedule; no test applications',flush=True)

if __name__=='__main__':
 parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('environment',choices=['demo','production']);parser.add_argument('--execute',action='store_true');parser.add_argument('--resume-setup',action='store_true',help='Only reconcile the recorded demo TIMESTAMP migration failure');parser.add_argument('--finish',action='store_true',help='Verify an already successful demo setup and finish scheduler/public checks without reimport');args=parser.parse_args()
 if not args.execute:raise SystemExit('No remote action. Prepare/review payload and tests, then explicitly use --execute.')
 try:run(args.environment,args.resume_setup,args.finish)
 except Exception as exc:print('STOPPED:',str(exc) if isinstance(exc,RuntimeError) else type(exc).__name__,'— inspect the private attempt/receipt; do not retry blindly',flush=True);raise SystemExit(1)
