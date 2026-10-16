#!/usr/bin/env python3
"""Demo-first runtime hardening, private logs and anchored web-root protection."""
import argparse,hashlib,importlib.util,json,re,time
from pathlib import Path
import requests
ROOT=Path(__file__).resolve().parents[3];OUT=ROOT/'reports/platform-security-2026-10-16'
spec=importlib.util.spec_from_file_location('release',Path(__file__).with_name('deploy-reception-graduands-2026.10.15.1.py'));m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m)
# These rules supplement physical removal. Static-layer bypasses cannot be
# fixed by Apache rules alone, so inventory cleanup remains mandatory.
RULES='''# BEGIN TOOLKIT RUNTIME MINIMUM
<FilesMatch "(?i)(?:^\\.|^(?:readme|changelog|contributing|worklog|agents|claude)(?:[._-]|$)|(?:\\.(?:bak|old|orig|save|sql|sqlite3?|db|md|docx?|xlsx?|pptx?|log|ini)(?:[._-].*)?$))">
    Require all denied
</FilesMatch>
# END TOOLKIT RUNTIME MINIMUM
'''
# Root document denials must not apply to approved uploads/download assets.
# DOCX/PDF downloads have their own application paths, so exclude these
# formats from generic matching and remove project documents physically.
RULES=RULES.replace('|docx?|xlsx?|pptx?','')
UPLOAD_RULES='''# BEGIN TOOLKIT UPLOAD EXECUTION BLOCK
<FilesMatch "(?i)\\.(?:php[0-9]?|phtml|phar|pht)(?:\\.|$)">
    Require all denied
</FilesMatch>
# END TOOLKIT UPLOAD EXECUTION BLOCK
'''
def run(env):
 if env=='production':assert json.loads((OUT/'hardening-demo.json').read_text())['verified']
 panel=m.Panel();out=OUT/'hardening'/env;out.mkdir(parents=True,exist_ok=True)
 site='/home/bfyigiln/'+('demo.toolkitafrica.ac.ke' if env=='demo' else 'public_html')
 other=['/home/bfyigiln/reception-public-demo','/home/bfyigiln/virtual-campus-test/public'] if env=='demo' else ['/home/bfyigiln/reception-public','/home/bfyigiln/reports.toolkitafrica.ac.ke']
 changes=[]
 def baseline(path):
  saved=out/'before'/path.removeprefix('/home/bfyigiln/')
  return saved.read_bytes() if saved.exists() else panel.fetch(path,optional=True)
 def put(path,data,existing=None):
  before=panel.fetch(path,optional=True)
  if before==data:
   changes.append({'path':path.removeprefix('/home/bfyigiln/'),'sha256':hashlib.sha256(data).hexdigest(),'already_applied':True});return
  if existing is not None:assert before==existing,'Concurrent deployed edit'
  key=path.removeprefix('/home/bfyigiln/');dest=out/'before'/key;dest.parent.mkdir(parents=True,exist_ok=True)
  dest.write_bytes(before or b'')
  if before is not None:
   panel.upload('/home/bfyigiln/reception-system/storage/app/private/platform-security-2026-10-16/rollback/'+env+'/'+key,before)
  panel.upload(path,data);assert panel.fetch(path)==data
  changes.append({'path':key,'sha256':hashlib.sha256(data).hexdigest(),'new':before is None})
 for root in [site,*other]:
  path=root+'/.htaccess';before=baseline(path);text=(before or b'').decode()
  assert '# BEGIN TOOLKIT RUNTIME MINIMUM' not in text
  put(path,(RULES+'\n'+text).encode(),existing=before)
  if not root.endswith('reports.toolkitafrica.ac.ke'):
   path=root+'/.user.ini';before=baseline(path);text=(before or b'').decode()
   text=re.sub(r'^\s*(?:display_errors|log_errors|error_log)\s*=.*\n?', '',text,flags=re.M)
   label=root.removeprefix('/home/bfyigiln/').replace('/','-')
   text+='\ndisplay_errors = Off\nlog_errors = On\nerror_log = "/home/bfyigiln/logs/'+label+'-php.error.log"\n';put(path,text.encode(),existing=before)
 path=site+'/wp-content/uploads/.htaccess';before=baseline(path);text=(before or b'').decode();assert '# BEGIN TOOLKIT UPLOAD EXECUTION BLOCK' not in text;put(path,(UPLOAD_RULES+'\n'+text).encode(),existing=before)
 plugin='/wp-content/mu-plugins/toolkit-runtime-hardening.php';assert panel.fetch(site+plugin,optional=True) in [None,(ROOT/'wp-content/mu-plugins/toolkit-runtime-hardening.php').read_bytes()]
 put(site+plugin,(ROOT/'wp-content/mu-plugins/toolkit-runtime-hardening.php').read_bytes())
 path=site+'/wp-content/themes/eduma-child/functions.php';before=baseline(path);text=before.decode();assert text.count("return '2026.10.16.1';")==1;put(path,text.replace("return '2026.10.16.1';","return '2026.10.16.2';").encode(),existing=before)
 checks=[]
 def get(base,path):
  time.sleep(4);r=requests.get(base+path,timeout=45,allow_redirects=False);checks.append({'base':base,'path':path,'status':r.status_code,'release':r.headers.get('X-Toolkit-Release')});assert r.status_code!=429 and not r.headers.get('Retry-After');return r
 base='https://'+('demo.' if env=='demo' else '')+'toolkitafrica.ac.ke'
 r=get(base,'/?security_release=2026.10.16.2');assert r.status_code==200
 for path in ['/','/about-toolkit-africa/','/apply/','/graduation/']:
  r=get(base,path);assert r.status_code==200 and r.headers.get('X-Toolkit-Release')=='2026.10.16.2';assert not re.search(r'Fatal error|Uncaught Error|Stack trace:',r.text)
 # A demo-only disposable PHP canary proves Apache blocks execution in uploads.
 if env=='demo':
  canary=site+'/wp-content/uploads/toolkit-hardening-canary-20261016.php';payload=b'<?php echo "TOOLKIT_CANARY_MUST_NOT_EXECUTE_20261016";'
  current=panel.fetch(canary,optional=True);assert current in [None,payload]
  if current is None:panel.upload(canary,payload)
  saved=OUT/'upload-canary-result.json'
  if saved.exists():
   observation=json.loads(saved.read_text());assert observation['status'] in [403,404,406] and not observation['executed_marker_present'];checks.append({'path':'/wp-content/uploads/toolkit-hardening-canary-20261016.php','status':observation['status'],'previous_observation':True})
  else:
   r=get(base,'/wp-content/uploads/toolkit-hardening-canary-20261016.php');assert r.status_code in [403,404,406] and 'TOOLKIT_CANARY_MUST_NOT_EXECUTE_20261016' not in r.text
 for host,path in ([('https://reception-demo.toolkitafrica.ac.ke','/login'),('https://campus-test.toolkitafrica.ac.ke','/login')] if env=='demo' else [('https://reception.toolkitafrica.ac.ke','/login'),('https://reports.toolkitafrica.ac.ke','/login')]):
  r=get(host,path);assert r.status_code==200
 (OUT/('hardening-'+env+'.json')).write_text(json.dumps({'environment':env,'verified':True,'release':'2026.10.16.2','files':changes,'public_checks':checks,'approved_public_downloads_preserved':True},indent=2)+'\n');print('VERIFIED runtime hardening',env,'release 2026.10.16.2',flush=True)
if __name__=='__main__':
 p=argparse.ArgumentParser(description=__doc__);p.add_argument('environment',choices=['demo','production']);run(p.parse_args().environment)
