#!/usr/bin/env python3
"""Prepare only reviewed runtime files from captured live baselines; no network."""
import hashlib,importlib.util,json,shutil,subprocess
from pathlib import Path
ROOT=Path(__file__).resolve().parents[3]
CASE=ROOT/'reports/toolkit-admin-sync-2026-10-26'
BASES={'wordpress':'1f0d53f1e76afa0cb7be548b1c584759171b65a8','reception':'abf22ce6cef90e58e0fdc6a79987f1174c18efe1'}
WP_EXISTING=['functions.php','inc/site-metrics.php','inc/support-hub.php','inc/application-adapter.php','inc/graduands.php','assets/css/toolkit-admin.css']
WP_NEW=['inc/toolkit-admin.php','inc/reception-application-sync.php']
RECEPTION_EXISTING=['routes/web.php','routes/console.php','config/services.php','resources/views/layouts/staff.blade.php']
RECEPTION_NEW=['app/Models/WebsiteApplication.php','app/Services/WebsiteApplicationSync.php','app/Console/Commands/SyncWebsiteApplications.php','app/Http/Controllers/Staff/WebsiteApplicationController.php','database/migrations/2026_10_26_090000_create_website_applications_table.php','resources/views/staff/website-applications/index.blade.php','resources/views/staff/website-applications/show.blade.php']

def sha(raw):return hashlib.sha256(raw).hexdigest()
def prepare():
 spec=importlib.util.spec_from_file_location('runtime_policy',ROOT/'scripts/deployment/runtime_payload.py');policy=importlib.util.module_from_spec(spec);spec.loader.exec_module(policy)
 manifests={}
 for env in ['demo','production']:
  payload=CASE/'release-payload'/env
  if payload.exists():shutil.rmtree(payload)
  payload.mkdir(parents=True,mode=0o700)
  manifest=[]
  for component,existing,new in [('wordpress',WP_EXISTING,WP_NEW),('reception',RECEPTION_EXISTING,RECEPTION_NEW)]:
   repo=ROOT if component=='wordpress' else ROOT.parent/'reception-system'
   prefix='wp-content/themes/eduma-child/' if component=='wordpress' else ''
   target=payload/component;target.mkdir()
   for name in existing:
    baseline=CASE/'baseline'/env/component/name
    if not baseline.is_file():raise RuntimeError('Missing reviewed baseline: '+env+'/'+component+'/'+name)
    p=target/(prefix+name);p.parent.mkdir(parents=True,exist_ok=True);p.write_bytes(baseline.read_bytes())
   patched=[n for n in existing if n!='inc/application-adapter.php']
   diff=subprocess.run(['git','-C',str(repo),'diff',BASES[component],'--',*[prefix+n for n in patched]],capture_output=True,check=True).stdout
   result=subprocess.run(['patch','--batch','--fuzz=0','-p1','-d',str(target)],input=diff,capture_output=True)
   if result.returncode:raise RuntimeError('Focused patch failed: '+env+'/'+component)
   if component=='wordpress':
    p=target/(prefix+'inc/application-adapter.php');old=p.read_text();local=(ROOT/(prefix+'inc/application-adapter.php')).read_text();anchor='function toolkit_application_admin_header('
    if old.count(anchor)!=1 or local.count(anchor)!=1:raise RuntimeError('Admin header anchor changed')
    a=old.index(anchor);b=old.index('\nfunction ',a+1);c=local.index(anchor);d=local.index('\nfunction ',c+1)
    p.write_text(old[:a]+local[c:d]+old[b:])
   for name in new:
    p=target/(prefix+name);p.parent.mkdir(parents=True,exist_ok=True);p.write_bytes((repo/(prefix+name)).read_bytes())
   for name in existing+new:
    p=target/(prefix+name);raw=p.read_bytes()
    remote=('/home/bfyigiln/'+('demo.toolkitafrica.ac.ke' if env=='demo' else 'public_html')+'/wp-content/themes/eduma-child/' if component=='wordpress' else '/home/bfyigiln/reception-system'+('-demo' if env=='demo' else '')+'/')+name
    policy.assert_runtime_upload(remote)
    if p.suffix=='.php':subprocess.run(['php','-l',str(p)],check=True,stdout=subprocess.DEVNULL)
    manifest.append({'component':component,'file':name,'payload':str(p.relative_to(CASE)),'remote':remote,'sha256':sha(raw),'baseline_sha256':sha((CASE/'baseline'/env/component/name).read_bytes()) if name in existing else None})
  if len(manifest)!=19:raise RuntimeError('Unexpected runtime file count')
  destination=CASE/('release-manifest-'+env+'.json')
  doc={'environment':env,'release':'2026.10.26.1','prepared_from_live_baselines':True,'files':manifest,'deployed':False}
  destination.write_text(json.dumps(doc,indent=2)+'\n');manifests[env]=doc
 # Same code reaches both environments; environment configuration stays on-host.
 if {(r['component'],r['file'],r['sha256']) for r in manifests['demo']['files']}!={(r['component'],r['file'],r['sha256']) for r in manifests['production']['files']}:raise RuntimeError('Review environment-specific code drift before promotion')
 print('Prepared 19 runtime-only files per environment (8 WordPress, 11 Reception); no docs, archives, manifests, tests or secrets are deployment members.')

if __name__=='__main__':prepare()
