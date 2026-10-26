#!/usr/bin/env python3
import importlib.util
from pathlib import Path
root=Path(__file__).resolve().parents[2];spec=importlib.util.spec_from_file_location('policy',root/'scripts/deployment/runtime_payload.py');m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m)
checks=0
for base in m.PUBLIC_ROOTS+m.PRIVATE_APPS:
 for name in ['README.md','docs/architecture.md','WORKLOG.md','report.docx','report.pdf','infra-notes.txt','app.py.bak-old','release.zip','assets/site.js.map','tests/test_auth.py','.git/HEAD','.Git/HEAD','.env.example','.env.backup','release.sqlite','assets/style.css.map.old']:
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
for path in ['/home/bfyigiln/unknown-app/README.md','/home/bfyigiln/public_html/wp-content/uploads/storage/app/private/README.md','/home/bfyigiln/toolkit-private-storage/README.md','relative/index.php','/home/bfyigiln/public_html//index.php']:
 try:m.assert_runtime_upload(path)
 except ValueError:checks+=1
 else:raise AssertionError('Boundary bypass accepted: '+path)
print(checks,'runtime payload boundary checks passed')
