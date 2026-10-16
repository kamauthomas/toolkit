#!/usr/bin/env python3
"""Bounded anonymous review of inventoried Toolkit hosts; no saved bodies."""
import datetime,json,re,time,sys
from pathlib import Path
from urllib.parse import urlsplit,urljoin
import requests
if '--explicit-live-negative-tests' not in sys.argv:
 raise SystemExit('Archived audit: live negative tests are disabled by default after the hosting block. Use isolated tests; do not resume without a newly agreed scope and hosting coordination.')
ROOT=Path(__file__).resolve().parents[2];OUT=ROOT/'reports/platform-security-2026-10-16';rows=[];last=0
HEADERS=['Content-Type','X-Toolkit-Release','X-Content-Type-Options','X-Frame-Options','Strict-Transport-Security','Content-Security-Policy','Referrer-Policy','Access-Control-Allow-Origin','Access-Control-Allow-Credentials','Retry-After']
class StopReview(Exception):pass
def request(system,base,path,label,method='GET',files=None):
 global last
 delay=max(0,4-(time.monotonic()-last));time.sleep(delay);last=time.monotonic()
 with requests.request(method,base+path,files=files,timeout=(15,30),allow_redirects=False,stream=True,headers={'User-Agent':'Toolkit-authorized-bounded-review/2026-10-16'}) as r:
  body=r.raw.read(98304,decode_content=True);text=body.decode('utf-8','replace')
  markers={'source':bool(re.search(r'(?:^|\n)(?:from flask import|import os|<\?php|def [a-z_]+\()',text)),'environment':bool(re.search(r'(?:^|\n)(?:APP_KEY=|DB_PASSWORD=|SECRET_KEY=|DATABASE_URL=)',text)),'git':bool(re.search(r'^ref: refs/heads/',text)),'traceback':bool(re.search(r'Traceback \(most recent|Fatal error:|Stack trace:|Uncaught (?:Error|Exception)',text)),'directory_listing':bool(re.search(r'<title>Index of|Directory listing for',text,re.I)),'raw_test_tag':'<tk-audit-20261016>' in text}
  row={'system':system,'path':path,'label':label,'method':method,'utc':datetime.datetime.now(datetime.timezone.utc).isoformat(),'status':r.status_code,'headers':{k:r.headers[k] for k in HEADERS if k in r.headers},'redirect_path':urlsplit(r.headers.get('Location','')).path or None,'markers':markers,'archive_signature':body.startswith(b'PK\x03\x04')}
  rows.append(row);print(system,label,r.status_code,flush=True);save()
  if r.status_code==429 or r.headers.get('Retry-After') or re.search(r'cf-chl-|your ip (?:has been|is) blocked|temporarily blocked|rate limit exceeded',text,re.I):raise StopReview('Throttling/challenge: stop all probes')
  return row,text
def save(stopped=None):
 (OUT/'blackbox-results.json').write_text(json.dumps({'minimum_pause_seconds':4,'automatic_retries':0,'credential_attempts':0,'business_forms_submitted':0,'response_bodies_saved':False,'stopped_reason':stopped,'requests':len(rows),'results':rows},indent=2)+'\n')
try:
 for system,base in [('website-demo','https://demo.toolkitafrica.ac.ke'),('website','https://toolkitafrica.ac.ke')]:
  checks=[('/','availability'),('/readme.html','removed-readme'),('/wp-content/themes/eduma.zip','removed-theme-installer'),('/wp-content/plugins/elementor-pro.zip','removed-plugin-installer'),('/.git/HEAD','git-metadata'),('/wp-content/uploads/%252e%252e%252f%252e%252e%252fwp-config.php','encoded-traversal'),('/wp-json/wp/v2/media?context=edit','private-media-context'),('/wp-admin/async-upload.php','anonymous-upload-interface'),('/?thim-auto-compile-sass-to-css=1','legacy-settings-write-disabled'),('/?s=%3Ctk-audit-20261016%3E','search-escaping')]
  for path,label in checks:request(system,base,path,label)
  if system=='website-demo':
   request(system,base,'/wp-admin/async-upload.php','anonymous-invalid-upload','POST',{'async-upload':('../toolkit-audit.php.jpg',b'TOOLKIT_INVALID_IMAGE_FIXTURE','image/jpeg')})
 for system,base in [('reception-demo','https://reception-demo.toolkitafrica.ac.ke'),('reception','https://reception.toolkitafrica.ac.ke')]:
  row,html=request(system,base,'/login','login-availability');forms=re.findall(r'<form[^>]+action=["\']([^"\']+)',html,re.I);login=next((urlsplit(urljoin(base,x)).path for x in forms if urlsplit(urljoin(base,x)).path.endswith('/login')),None)
  assert login,'Cannot discover staff route from actual login form';prefix=login[:-6];assert prefix,'Staff prefix is not discoverable from the shortcut form; inspect deployed configuration rather than guess'
  for path,label in [('/.env','environment-file'),('/.git/HEAD','git-metadata'),('/error_log','removed-public-log'),('/assets/%252e%252e%252f.env','encoded-traversal'),(prefix+'/visits','private-visitors'),(prefix+'/applicants','private-applicants'),(prefix+'/settings/staff','private-staff-administration')]:request(system,base,path,label)
 base='https://reports.toolkitafrica.ac.ke';system='reports'
 for path,label in [('/login','login-availability'),('/app.py.bak-pre-security-20260818','removed-source-backup'),('/app.py','active-source-denied'),('/static/%252e%252e%252fapp.py','encoded-traversal'),('/generated_reports/','private-report-directory'),('/api/reports/drafts','private-drafts'),('/reports/1/pdf','private-report-download'),('/.git/HEAD','git-metadata')]:request(system,base,path,label)
 base='https://campus-test.toolkitafrica.ac.ke';system='campus-test'
 for path,label in [('/login','login-availability'),('/.env','environment-file'),('/.git/HEAD','git-metadata'),('/storage/%252e%252e%252f.env','encoded-traversal'),('/admin','private-administration'),('/finance','private-finance'),('/search','private-student-search'),('/composer.json','private-dependency-manifest')]:request(system,base,path,label)
 save();print('Finished',len(rows),'paced requests, no response bodies saved',flush=True)
except (StopReview,requests.RequestException,AssertionError) as e:
 save(type(e).__name__+': '+str(e));print('Review stopped:',type(e).__name__,flush=True);raise
