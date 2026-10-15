#!/usr/bin/env python3
"""Bounded read-only production review: 18 single GETs, three seconds apart.

No login attempts, credentials, form submissions, writes or automatic retries.
Response bodies/cookie values are not retained. Stop on throttling/challenges.
"""
from pathlib import Path
import datetime,json,re,time
from urllib.parse import urlsplit
import requests
BASE='https://toolkitafrica.ac.ke'
OUT=Path(__file__).resolve().parents[2]/'reports/security-review-2026-10-15'
CHECKS=[
 ('home','/'),('graduation','/graduation/'),('application-form','/apply/'),('contact-form','/contact/'),
 ('admin-dashboard','/wp-admin/admin.php?page=toolkit-control'),('graduands-admin','/wp-admin/admin.php?page=toolkit-graduands'),
 ('anonymous-display-control','/wp-admin/admin-post.php?action=toolkit_graduands_display'),
 ('private-rest-settings','/wp-json/wp/v2/settings'),('current-rest-user','/wp-json/wp/v2/users/me'),
 ('submit-endpoint-get','/wp-json/toolkit/v1/application/submit'),('enquiry-endpoint-get','/wp-json/toolkit/v1/support/enquiry'),
 ('dotenv-disclosure','/.env'),('git-disclosure','/.git/HEAD'),('debug-log-disclosure','/wp-content/debug.log'),
 ('upload-directory-index','/wp-content/uploads/'),('author-discovery','/wp-json/wp/v2/users?per_page=1'),
 ('search-escaping','/?s=toolkit-audit-20261015-%3Ctkprobe%3E'),('final-availability','/'),
]
HEADERS=['Content-Type','Strict-Transport-Security','Content-Security-Policy','Content-Security-Policy-Report-Only','X-Frame-Options','X-Content-Type-Options','Referrer-Policy','Permissions-Policy','Access-Control-Allow-Origin','Access-Control-Allow-Credentials','Server','X-Powered-By','X-Toolkit-Release','Retry-After']
OUT.mkdir(parents=True,exist_ok=True);results=[];stopped=None
session=requests.Session();session.headers.update({'User-Agent':'Toolkit-authorized-readonly-review/2026-10-15','Accept':'text/html,application/json;q=0.9,*/*;q=0.1'})
for index,(name,path) in enumerate(CHECKS):
 if index:time.sleep(3)
 try:
  # Read at most 96 KB. Discovery checks never save returned private content.
  with session.get(BASE+path,timeout=(15,30),allow_redirects=False,stream=True) as response:
   body=response.raw.read(98304,decode_content=True).decode('utf-8','replace')
   cookies=[{'name':c.name,'secure':c.secure,'httponly':'HttpOnly' in c._rest,'samesite':c._rest.get('SameSite')} for c in response.cookies]
   entry={'check':name,'path':path,'method':'GET','utc':datetime.datetime.now(datetime.timezone.utc).isoformat(),'status':response.status_code,'headers':{h:response.headers[h] for h in HEADERS if h in response.headers},'cookies_without_values':cookies,'location_path':urlsplit(response.headers.get('Location','')).path or None,'php_error_visible':bool(re.search(r'Fatal error:|Uncaught (?:Error|Exception)|Stack trace:',body)),'directory_listing':bool(re.search(r'<title>Index of|Directory listing for',body,re.I))}
   if name in ['dotenv-disclosure','git-disclosure','debug-log-disclosure']:
    entry['sensitive_content_marker']=bool(re.search(r'(?:^|\n)(?:APP_KEY=|DB_PASSWORD=|ref: refs/heads/)|PHP (?:Fatal|Warning|Notice)',body))
   if name=='author-discovery' and response.status_code==200:
    try:
     data=json.loads(body);entry['public_author_records']=len(data) if isinstance(data,list) else None;entry['public_fields']=sorted(data[0]) if isinstance(data,list) and data else []
    except ValueError:entry['json_complete']=False
   if name=='search-escaping':entry['raw_probe_tag_reflected']='<tkprobe>' in body;entry['escaped_probe_reflected']='&lt;tkprobe&gt;' in body
   results.append(entry);print(name,response.status_code,flush=True)
   challenge=bool(re.search(r'<title>Just a moment|cf-chl-|temporarily blocked|your ip (?:has been|is) blocked|rate limit exceeded',body,re.I))
   if response.status_code==429 or response.headers.get('Retry-After') or challenge:
    stopped='Throttling or challenge detected; no further requests sent';break
 except requests.RequestException as error:
  stopped='Network check stopped: '+type(error).__name__;break
 report={'target':BASE,'max_requests':len(CHECKS),'requests_sent':len(results),'minimum_pause_seconds':3,'automatic_retries':0,'login_attempts':0,'form_submissions':0,'writes':0,'response_bodies_saved':False,'stopped_reason':stopped,'results':results}
 (OUT/'live-results.json').write_text(json.dumps(report,indent=2)+'\n')
report={'target':BASE,'max_requests':len(CHECKS),'requests_sent':len(results),'minimum_pause_seconds':3,'automatic_retries':0,'login_attempts':0,'form_submissions':0,'writes':0,'response_bodies_saved':False,'stopped_reason':stopped,'results':results}
(OUT/'live-results.json').write_text(json.dumps(report,indent=2)+'\n');session.close();print('Finished:',len(results),'requests; stopped:',stopped,flush=True)
