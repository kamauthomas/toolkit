#!/usr/bin/env python3
"""Exercise a staged copy of deployed Reporting with synthetic SQLite data only."""
import json,os,sys
from pathlib import Path
root=Path(sys.argv[1]).resolve();os.environ.update({'ENV':'test','SECRET_KEY':'synthetic-audit-runtime-key','DATABASE_URL':''});sys.path.insert(0,str(root));os.chdir(root)
import app as module
app=module.app;app.config.update(TESTING=True,WTF_CSRF_ENABLED=False,SERVER_NAME='localhost')
# Refuse to use any database beyond the staged private workspace.
assert str(module.DB_PATH).startswith(str(root)+os.sep)
assert module.DATABASE_URL == ''
out=root/'audit-evidence';out.mkdir(exist_ok=True)
checks=[]
def check(condition,label):
 if not condition:raise AssertionError(label)
 checks.append(label)
with app.app_context():
 db=module.get_db()
 db.execute('DELETE FROM reports')
 db.execute('UPDATE users SET full_name = ? WHERE id = 1',('Synthetic Audit Administrator',))
 def staff(name,email,role='employee'):
  cursor=db.execute('''INSERT INTO users (full_name,email,department,position,branch,password_hash,role,is_active,created_at) VALUES (?,?,'Admissions','Officer','Main Office','unused-synthetic-hash',?,1,?)''',(name,email,role,module.now()));return cursor.lastrowid
 employee=staff('Synthetic Staff One','fixture-one@example.test');outside=staff('Synthetic Outside Staff','fixture-outside@example.test');manager=staff('Synthetic Department Admin','fixture-manager@example.test','admin')
 db.execute('INSERT INTO report_access(admin_id,employee_id,created_at) VALUES(?,?,?)',(manager,employee,module.now()))
 def report(uid,summary,status,metrics):
  db.execute('''INSERT INTO reports(user_id,report_date,reporting_period,branch,department,position,day_summary,tasks_json,challenges_json,decisions,tomorrow_json,comments,metrics_json,status,archived,created_at) VALUES(?,'2026-10-05','Daily','Main Office','Admissions','Officer',?,'[]','[]','','[]','',?,?,0,?)''',(uid,summary,json.dumps(metrics),status,module.now()))
 report(employee,'Synthetic admissions fixture','submitted',{'applications_processed':2});report(employee,'Synthetic marketing fixture','approved',{'leads_generated':3});report(employee,'Synthetic reviewed fixture','reviewed',{});report(outside,'Synthetic outside fixture','submitted',{'leads_generated':8});db.commit()
client=app.test_client()
def login(uid):
 with client.session_transaction() as session:
  session.clear();session['user_id']=uid;session['_created_at']=module.now();session['_csrf_token']='synthetic-audit-token';session.permanent=True
login(1)
for path in ['/dashboard','/modules','/staff','/dashboard?status=submitted','/dashboard?status=approved','/dashboard?activity=admissions','/dashboard?activity=marketing']:
 response=client.get(path);check(response.status_code==200,'HTTP 200 '+path)
 if path in ['/dashboard','/modules','/staff']:(out/(path.strip('/')+'.html')).write_bytes(response.data)
response=client.get('/dashboard?activity=admissions');check(b'Synthetic admissions fixture' in response.data and b'Synthetic marketing fixture' not in response.data,'Admissions card matches report signals')
response=client.get('/dashboard?activity=marketing');check(b'Synthetic marketing fixture' in response.data and b'Synthetic reviewed fixture' not in response.data,'Marketing card matches report signals')
login(employee)
for path in ['/dashboard?activity=marketing','/dashboard?activity=marketing&employee_id='+str(outside),'/modules','/staff']:
 response=client.get(path);check(response.status_code==200 and b'Synthetic Outside Staff' not in response.data and b'Synthetic outside fixture' not in response.data,'Employee scope '+path)
login(manager)
response=client.get('/staff');check(response.status_code==200 and b'Synthetic Staff One' in response.data and b'Synthetic Outside Staff' not in response.data,'Admin staff scope matches assigned employee')
response=client.get('/reports/export.csv?activity=marketing');check(response.status_code==200 and b'Synthetic marketing fixture' in response.data and b'Synthetic admissions fixture' not in response.data and b'Synthetic outside fixture' not in response.data,'Export matches filtered scope')
# Parse native card hrefs without optional HTML dependencies.
from html.parser import HTMLParser
class Links(HTMLParser):
 def __init__(self):super().__init__();self.links=[]
 def handle_starttag(self,tag,attrs):
  data=dict(attrs)
  if tag=='a' and any(x in data.get('class','').split() for x in ['stat-card','module-card']):self.links.append(data['href'])
login(1)
for path in ['/dashboard','/modules']:
 parser=Links();parser.feed(client.get(path).get_data(as_text=True));check(bool(parser.links),'Native card links '+path)
 for href in parser.links:check(client.get(href).status_code==200,'Card destination '+href)
(out/'runtime-results.json').write_text(json.dumps({'checks':checks,'passed':len(checks),'database':'synthetic staging SQLite only','production_records_changed':0},indent=2))
print(json.dumps({'result':'REPORTING_AUDIT_RUNTIME_OK','checks_passed':len(checks),'database':'isolated synthetic SQLite'}))
