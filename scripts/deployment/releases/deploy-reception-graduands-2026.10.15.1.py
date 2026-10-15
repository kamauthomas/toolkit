#!/usr/bin/env python3
"""Guarded demo-first deployment; source identity and private rollback required."""
import argparse,hashlib,json,re,subprocess,time
from pathlib import Path
import requests
ROOT=Path(__file__).resolve().parents[3]
EVIDENCE=ROOT/'reports/reception-graduands-2026-10-15'
RECEPTION_FILES=['app/Support/VisitHistory.php','resources/views/staff/partials/pagination.blade.php','public/assets/css/staff-overview.css','public/assets/js/staff.js','app/Http/Controllers/Staff/VisitController.php','app/Http/Controllers/Staff/DashboardController.php','app/Providers/AppServiceProvider.php','resources/views/staff/partials/visits-table.blade.php','resources/views/staff/visits/show.blade.php','resources/views/staff/dashboard.blade.php','resources/views/layouts/staff.blade.php']
ROSTER_FILES=['inc/graduands.php','template-parts/graduands.php','assets/css/graduands.css','assets/js/graduands.js','functions.php','template-parts/pages/graduation.php']
NEW={'app/Support/VisitHistory.php','resources/views/staff/partials/pagination.blade.php','public/assets/css/staff-overview.css',*ROSTER_FILES[:4]}
class Panel:
 def __init__(self):
  entries={l.split('=',1)[0]:l.split('=',1)[1].strip().strip('\"\'') for l in (ROOT/'.toolkit-deploy/secrets.env').read_text().splitlines() if '=' in l and not l.startswith('#')}
  user,password=entries['CPANEL_AUTH'].split(':',1);self.user=user;self.s=requests.Session();base='https://toolkitafrica.ac.ke:2083'
  data=self.s.post(base+'/login/?login_only=1',data={'user':user,'pass':password},timeout=40).json()
  if data.get('status')!=1:raise RuntimeError('cPanel session not accepted')
  self.api=base+data['security_token']+'/execute/';self.api2=base+data['security_token']+'/json-api/cpanel'
 def fetch(self,path,optional=False):
  p=Path(path);d=self.s.get(self.api+'Fileman/get_file_content',params={'dir':str(p.parent),'file':p.name,'update_html_document_encoding':0,'to_charset':'utf-8'},timeout=60).json()
  if d.get('status')==1 and 'content' in (d.get('data') or {}):return d['data']['content'].encode()
  if optional and re.search(r'does not exist|doesn.t exist|No such file|not found',str(d.get('errors',[])),re.I):return None
  raise RuntimeError('Cannot establish file state: '+str(p))
 def upload(self,path,data):
  p=Path(path);d=self.s.post(self.api+'Fileman/upload_files',data={'dir':str(p.parent),'overwrite':1},files={'file-1':(p.name,data)},timeout=90).json()
  if d.get('status')!=1 or int((d.get('data') or {}).get('succeeded',0))!=1:raise RuntimeError('Upload failed: '+str(p))
 def api2call(self,module,func,**args):
  return self.s.get(self.api2,params={'cpanel_jsonapi_user':self.user,'cpanel_jsonapi_apiversion':2,'cpanel_jsonapi_module':module,'cpanel_jsonapi_func':func,**args},timeout=60).json()['cpanelresult']

def run(component,environment,verify_only=False):
 out=EVIDENCE/'deployment'/environment/component;out.mkdir(parents=True,exist_ok=True)
 if environment=='production' and not (EVIDENCE/'deployment/demo'/component/'verification.json').exists():raise RuntimeError('Verified demo release required')
 rollback=ROOT/'rollbacks'/('2026.10.15.1-'+component+'-'+environment)
 if verify_only:
  if component!='wordpress' or not rollback.exists():raise RuntimeError('Verification resume requires an existing WordPress rollback')
 else:
  if rollback.exists():raise RuntimeError('Rollback already exists; inspect partial run before retry')
  rollback.mkdir(parents=True,mode=0o700)
 demo=environment=='demo';panel=Panel()
 if component=='reception':
  private='/home/bfyigiln/reception-system'+('-demo' if demo else '');public='/home/bfyigiln/reception-public'+('-demo' if demo else '');base='https://reception'+('-demo' if demo else '')+'.toolkitafrica.ac.ke';source=ROOT.parent/'reception-system';files=RECEPTION_FILES
  remote=lambda f:public+'/'+f[7:] if f.startswith('public/') else private+'/'+f
  baseline=lambda f:EVIDENCE/'baseline'/environment/f
 else:
  theme=('/home/bfyigiln/demo.toolkitafrica.ac.ke' if demo else '/home/bfyigiln/public_html')+'/wp-content/themes/eduma-child';base='https://'+('demo.' if demo else '')+'toolkitafrica.ac.ke';source=ROOT/'wp-content/themes/eduma-child';files=ROSTER_FILES
  remote=lambda f:theme+'/'+f
  baseline=lambda f:EVIDENCE/'baseline'/environment/'wordpress'/f
 if not verify_only:
  for f in files:
   before=panel.fetch(remote(f),optional=f in NEW)
   if f in NEW:
    if before is not None:raise RuntimeError('Unexpected existing file: '+f)
    (rollback/'NEW-FILES.txt').open('a').write(f+'\n')
   else:
    if before!=baseline(f).read_bytes():raise RuntimeError('Deployed baseline drift: '+f)
    dest=rollback/f;dest.parent.mkdir(parents=True,exist_ok=True);dest.write_bytes(before)
   dest=out/'payload'/f;dest.parent.mkdir(parents=True,exist_ok=True);dest.write_bytes((source/f).read_bytes())
   if f.endswith('.php') and '.blade.php' not in f:subprocess.run(['php','-l',str(dest)],check=True,stdout=subprocess.DEVNULL)
   if f.endswith('.js'):subprocess.run(['node','--check',str(dest)],check=True)
  print(environment,component,'preflight complete:',len(files),'files',flush=True)
  for f in files:panel.upload(remote(f),(out/'payload'/f).read_bytes())
 rows=[]
 for f in files:
  raw=panel.fetch(remote(f));expected=(out/'payload'/f).read_bytes()
  if raw!=expected:raise RuntimeError('Post-upload mismatch: '+f)
  dest=out/'verified'/f;dest.parent.mkdir(parents=True,exist_ok=True);dest.write_bytes(raw)
  rows.append({'file':f,'sha256':hashlib.sha256(raw).hexdigest()})
 if component=='reception':
  helper=private+'/storage/app/private/review-check-2026.10.15.php';log=private+'/storage/logs/review-2026.10.15.1.log'
  panel.upload(helper,(ROOT/'scripts/deployment/audit/reception-2026.10.15-check.php').read_bytes())
  command=f'/usr/local/bin/ea-php84 {private}/artisan optimize:clear > {log} 2>&1 && /usr/local/bin/ea-php84 {helper} {private} >> {log} 2>&1'
  added=panel.api2call('Cron','add_line',command=command,minute='*',hour='*',day='*',month='*',weekday='*')
  if (added.get('event') or {}).get('result')!=1:raise RuntimeError('Cannot schedule private verification')
  cron=None
  try:
   cron=next(row['count'] for row in panel.api2call('Cron','listcron').get('data',[]) if row.get('command')==command)
   for attempt in range(18):
    result=panel.fetch(log,optional=True)
    if result is not None:
     (out/'runtime.log').write_bytes(result)
     if b'RECEPTION_REVIEW_FAILED' in result:raise RuntimeError('Deployed synthetic verification failed; inspect private runtime log')
     if b'RECEPTION_REVIEW_OK' in result:break
    time.sleep(5)
   else:raise RuntimeError('Private runtime verification did not finish')
  finally:
   if cron is None:cron=next((row['count'] for row in panel.api2call('Cron','listcron').get('data',[]) if row.get('command')==command),None)
   if cron is not None:
    removed=panel.api2call('Cron','remove_line',line=cron)
    if (removed.get('event') or {}).get('result')!=1:raise RuntimeError('Remove temporary verification cron manually')
  for name in ['overview','applicants','applicants-page-2','staff-access','staff-activity','visitors','returning-visitor','results']:
   ext='json' if name=='results' else 'html';raw=panel.fetch(private+'/storage/app/private/review-2026.10.15.1/'+name+'.'+ext)
   dest=out/'fixtures'/(name+'.'+ext);dest.parent.mkdir(exist_ok=True);dest.write_bytes(raw)
  for path in ['/','/check-in','/apply','/assets/css/staff-overview.css?v=20261015.1']:
   response=requests.get(base+path,timeout=45);response.raise_for_status()
   if path.startswith('/assets'):assert response.content==(source/'public/assets/css/staff-overview.css').read_bytes()
 else:
  # Warm PHP once through a versioned URL so the release hook can purge old page caches.
  warm=requests.get(base+'/graduation/?graduands_check=2026.10.15.1',timeout=45);warm.raise_for_status()
  for path in ['/graduation/','/','/apply/','/contact/']:
   response=requests.get(base+path,timeout=45);response.raise_for_status()
   assert response.headers.get('X-Toolkit-Release')=='2026.10.15.1',(path,'wrong release')
   assert not re.search('fatal error|critical error|uncaught error',response.text,re.I)
   (out/(path.strip('/').replace('/','-') or 'home')).with_suffix('.html').write_text(response.text)
   if path=='/graduation/':assert 'id="graduands"' in response.text and 'confirmed list is being prepared' in response.text and 'data-graduand ' not in response.text
  for f in ['assets/css/graduands.css','assets/js/graduands.js']:
   response=requests.get(base+'/wp-content/themes/eduma-child/'+f+'?ver=2026.10.15.1',timeout=45);response.raise_for_status();assert response.content==(source/f).read_bytes()
 result={'environment':environment,'component':component,'files':rows,'verified':True,'private_rollback':str(rollback),'real_records_created_or_changed':0}
 (out/'verification.json').write_text(json.dumps(result,indent=2)+'\n');print('VERIFIED',environment,component,len(files),'byte-matched files; no live test registration',flush=True)
if __name__=='__main__':
 parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('component',choices=['reception','wordpress']);parser.add_argument('environment',choices=['demo','production']);parser.add_argument('--verify-only',action='store_true');args=parser.parse_args();run(args.component,args.environment,args.verify_only)
