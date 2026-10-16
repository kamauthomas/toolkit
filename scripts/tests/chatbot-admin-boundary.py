#!/usr/bin/env python3
"""Exercise actual Chat-bot routes with temporary data and no backend connection."""
import os,shutil,subprocess,sys,tempfile
from pathlib import Path
root=Path(__file__).resolve().parents[2];source=root.parent/'Chat-bot';stage=Path(tempfile.mkdtemp(prefix='toolkit-chatbot-security-'))
for p in source.glob('*.py'):shutil.copy2(p,stage/p.name)
shutil.copytree(source/'templates',stage/'templates')
(stage/'config.toml').write_text('[server]\nopencode_url="http://127.0.0.1:9"\n[database]\npath="'+str(stage/'data/test.db')+'"\n[admin]\nenabled=true\n')
program='''
import os
os.environ['CHATBOT_ADMIN_PASSWORD']='synthetic-test-only'
from fastapi import HTTPException
from fastapi.security import HTTPBasicCredentials
import web_app as m
checks=0
for path in ['/applications','/applications/{app_id}','/metrics','/admin']:
 for route in [r for r in m.app.routes if r.path==path]:
  assert any(d.call is m.require_admin for d in route.dependant.dependencies);checks+=1
for credentials,status in [(None,401),(HTTPBasicCredentials(username='toolkitadmin',password='wrong'),401)]:
 try:m.require_admin(credentials)
 except HTTPException as e:assert e.status_code==status;checks+=1
 else:raise AssertionError('Unauthorized credentials accepted')
m.require_admin(HTTPBasicCredentials(username='toolkitadmin',password='synthetic-test-only'));checks+=1
assert m.health()=={'status':'ok'};checks+=1
os.environ.pop('CHATBOT_ADMIN_PASSWORD')
try:m.require_admin(None)
except HTTPException as e:assert e.status_code==503;checks+=1
m.ADMIN_ENABLED=False
try:m.require_admin(None)
except HTTPException as e:assert e.status_code==404;checks+=1
assert m.app.openapi_url is None and m.app.docs_url is None;checks+=1
print(checks,'Chat-bot dependency/authentication/privacy checks passed; no backend connection or real records')
'''

r=subprocess.run([sys.executable,'-c',program],cwd=stage,env=dict(os.environ,PYTHONPATH=str(stage)),capture_output=True,text=True,timeout=30);(root/'reports/platform-security-2026-10-16/chatbot-tests.log').write_text(r.stdout+r.stderr);print((r.stdout+r.stderr)[-1600:]);assert r.returncode==0
