#!/usr/bin/env python3
"""Adversarial file-boundary checks against the staged deployed Reporting app."""
import importlib.util,os,tempfile
from pathlib import Path
root=Path(__file__).resolve().parents[2];payload=root/'reports/platform-security-2026-10-16/reporting-payload'
spec=importlib.util.spec_from_file_location('safe_files',payload/'safe_files.py');m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m)
checks=0
with tempfile.TemporaryDirectory(prefix='toolkit-report-path-test-') as work:
 base=Path(work)/'generated_reports';base.mkdir();secret=Path(work)/'private.pdf';secret.write_bytes(b'outside-boundary');(base/'link.pdf').symlink_to(secret);(base/'loop.pdf').symlink_to(base/'loop.pdf')
 for name in ['../private.pdf','/etc/passwd','/tmp/private.pdf','..\\private.pdf','link.pdf','loop.pdf','name.php','name.pdf/../private.pdf','.hidden.pdf',None,'bad\x00.pdf']:
  assert m.confined_pdf_path(base,name) is None,name;checks+=1
 assert m.confined_pdf_path(base,'report-2026.pdf')==base/'report-2026.pdf';checks+=1
 assert secret.read_bytes()==b'outside-boundary';checks+=1
print(checks,'Reporting filename confinement checks passed')
