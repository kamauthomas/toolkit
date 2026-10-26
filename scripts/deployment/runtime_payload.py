"""Deployment boundary: source repositories are not server payloads."""
import re
from pathlib import PurePosixPath

PUBLIC_ROOTS=('/home/bfyigiln/public_html/','/home/bfyigiln/demo.toolkitafrica.ac.ke/','/home/bfyigiln/reception-public/','/home/bfyigiln/reception-public-demo/','/home/bfyigiln/virtual-campus-test/public/','/home/bfyigiln/reports.toolkitafrica.ac.ke/')
PRIVATE_APPS=('/home/bfyigiln/reception-system/','/home/bfyigiln/reception-system-demo/','/home/bfyigiln/virtual-campus-test/')
FORBIDDEN_DIRS={'.git','.github','.codex','.agents','docs','reports','tests','test','__pycache__','node_modules'}
DOC_NAME=re.compile(r'^(readme|changelog|changes|contributing|worklog|agents|claude|todo|deployment|hardening)(?:[._-]|$)',re.I)
FORBIDDEN_SUFFIXES={'.md','.rst','.doc','.docx','.xls','.xlsx','.ppt','.pptx','.pdf','.sql','.sqlite','.sqlite3','.db','.bak','.old','.orig','.patch','.zip','.tar','.gz','.tgz','.7z','.log','.map','.pyc','.pyo','.pem','.key','.p12','.pfx'}
LICENSE_NAMES={'license','license.txt','license.md','licence','licence.txt','copyright','copyright.txt','notice','notice.txt'}

def assert_runtime_upload(path):
    """Private incident/rollback storage is transient, separate from app payloads."""
    if not isinstance(path,str) or '\\' in path or '\x00' in path or not path.startswith('/') or str(PurePosixPath(path)) != path or '..' in PurePosixPath(path).parts:
        raise ValueError('Unsafe deployment path')
    # These private DOCX files are required runtime inputs, not project reports.
    if path in {'/home/bfyigiln/toolkit-private-storage/calling-letters/_template.docx'}:
        return
    prefix=next((root for root in PUBLIC_ROOTS+PRIVATE_APPS if path.startswith(root)),None)
    if prefix is None:
        raise ValueError('Unknown deployment root; use a reviewed runtime destination')
    relative=PurePosixPath(path[len(prefix):]);name=relative.name.lower()
    if any(part.lower() in FORBIDDEN_DIRS for part in relative.parts):
        raise ValueError('Development directories are excluded from deployment')
    if name in LICENSE_NAMES:
        return
    if relative.suffix.lower() == '.txt' and name not in {'requirements.txt','robots.txt','security.txt','llms.txt'}:
        raise ValueError('Text documents require an explicit runtime use')
    if DOC_NAME.match(name) or relative.suffix.lower() in FORBIDDEN_SUFFIXES or re.search(r'\.(?:bak|old|orig|save|zip|tar|gz|sql|sqlite3?|db|map|md)(?:[._-]|$)',name):
        raise ValueError('Documents, source maps, archives and backups are excluded from application deployment')
    if name=='.env' and prefix in PUBLIC_ROOTS:
        raise ValueError('Environment secrets cannot be deployed inside a public root')
    if (name.startswith('.env') and name!='.env') or (name.endswith('.env') and name!='.env'):
        raise ValueError('Secret configuration must be provisioned separately')
    if name in {'.env.example','dockerfile','docker-compose.yml','docker-compose.yaml','phpunit.xml','package-lock.json'}:
        raise ValueError('Development configuration is excluded from deployment')
