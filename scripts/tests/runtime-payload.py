#!/usr/bin/env python3
import importlib.util
from pathlib import Path
root=Path(__file__).resolve().parents[2];spec=importlib.util.spec_from_file_location('policy',root/'scripts/deployment/runtime_payload.py');m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m)
checks=0
for base in m.PUBLIC_ROOTS+m.PRIVATE_APPS:
 for name in ['README.md','docs/architecture.md','WORKLOG.md','report.docx','app.py.bak-old','release.zip','assets/site.js.map','tests/test_auth.py','.git/HEAD','.env.example']:
  try:m.assert_runtime_upload(base+name)
  except ValueError:checks+=1
  else:raise AssertionError(name)
 for name in ['app.py','index.php','assets/main.css','assets/logo.png','LICENSE.md']:
  m.assert_runtime_upload(base+name);checks+=1
for base in m.PUBLIC_ROOTS:
 try:m.assert_runtime_upload(base+'.env')
 except ValueError:checks+=1
 else:raise AssertionError('Public environment file accepted')
m.assert_runtime_upload('/home/bfyigiln/reception-system/.env');checks+=1
m.assert_runtime_upload('/home/bfyigiln/toolkit-private-storage/calling-letters/_template.docx');checks+=1
print(checks,'runtime payload boundary checks passed')
