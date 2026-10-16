#!/usr/bin/env python3
"""Test deployed-code download authorization/confinement in private synthetic SQLite."""
import json,os,sys
from pathlib import Path
root=Path(__file__).resolve().parents[2];stage=root/'reports/platform-security-2026-10-16/reporting-isolated-demo';os.environ.update({'ENV':'test','SECRET_KEY':'synthetic-audit-key','DATABASE_URL':''});sys.path.insert(0,str(stage));import app
assert str(app.DB_PATH).startswith(str(stage))
from jinja2 import DictLoader
app.app.jinja_loader=DictLoader({'error.html':'{{ title }}: {{ message }}'})
checks=[]
with app.app.app_context():
 app.init_db();db=app.get_db();db.execute('DELETE FROM reports')
 cursor=db.execute('''INSERT INTO reports(user_id,report_date,reporting_period,branch,department,position,day_summary,tasks_json,challenges_json,decisions,tomorrow_json,comments,metrics_json,status,archived,created_at,pdf_filename) VALUES(1,'2026-10-16','Daily','Main','ICT','Officer','Synthetic report','[]','[]','','[]','','{}','submitted',0,?,'safe.pdf')''',(app.now(),));rid=cursor.lastrowid;db.commit()
file=app.REPORT_DIR/'safe.pdf';file.write_bytes(b'%PDF-1.4\nSynthetic bounded fixture\n%%EOF')
outside=stage/'outside.pdf';outside.write_bytes(b'PRIVATE_SENTINEL_MUST_NOT_BE_RETURNED')
link=app.REPORT_DIR/'outside-link.pdf';link.unlink(missing_ok=True);link.symlink_to(outside.resolve())
client=app.app.test_client()
for path in ['/dashboard','/api/reports/drafts','/reports/'+str(rid)+'/pdf','/admin/users']:
 r=client.get(path);assert r.status_code in (302,401,403);checks.append('Anonymous denied: '+path)
with client.session_transaction() as session:session['user_id']=1;session['_last_active']=app.now()
r=client.get('/reports/'+str(rid)+'/pdf');assert r.status_code==200 and r.data==file.read_bytes();checks.append('Authorized PDF download preserved')
for name in ['../outside.pdf',str(outside.resolve()),'outside-link.pdf','..\\outside.pdf']:
 with app.app.app_context():
  db=app.get_db();db.execute('UPDATE reports SET pdf_filename=? WHERE id=?',(name,rid));db.commit()
 r=client.get('/reports/'+str(rid)+'/pdf');assert r.status_code==404 and b'PRIVATE_SENTINEL' not in r.data;checks.append('Tainted metadata download denied')
assert outside.read_bytes()==b'PRIVATE_SENTINEL_MUST_NOT_BE_RETURNED';checks.append('Outside file unchanged')
(stage.parent/'reporting-isolated-verification.json').write_text(json.dumps({'checks':checks,'passed':len(checks),'production_records_touched':0},indent=2)+'\n');print(len(checks),'isolated deployed-Reporting download checks passed')
