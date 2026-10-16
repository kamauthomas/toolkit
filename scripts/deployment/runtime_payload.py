"""Deployment boundary: source repositories are not server payloads."""
import re
from pathlib import PurePosixPath

PUBLIC_ROOTS=('/home/bfyigiln/public_html/','/home/bfyigiln/demo.toolkitafrica.ac.ke/','/home/bfyigiln/reception-public/','/home/bfyigiln/reception-public-demo/','/home/bfyigiln/virtual-campus-test/public/','/home/bfyigiln/reports.toolkitafrica.ac.ke/')
PRIVATE_APPS=('/home/bfyigiln/reception-system/','/home/bfyigiln/reception-system-demo/','/home/bfyigiln/virtual-campus-test/')
FORBIDDEN_DIRS={'.git','.github','.codex','.agents','docs','reports','tests','__pycache__','node_modules'}
DOC_NAME=re.compile(r'^(readme|changelog|changes|contributing|worklog|agents|claude|todo|deployment|hardening)(?:[._-]|$)',re.I)
FORBIDDEN_SUFFIXES={'.md','.doc','.docx','.xls','.xlsx','.ppt','.pptx','.sql','.bak','.old','.orig','.patch','.zip','.tar','.gz','.tgz','.7z','.log','.map','.pyc','.pem','.key','.p12','.pfx'}
LICENSE_NAMES={'license','license.txt','license.md','licence','licence.txt','copyright','copyright.txt','notice','notice.txt'}

def assert_runtime_upload(path):
    """Private incident/rollback storage is transient, separate from app payloads."""
    if '\\' in path or '\x00' in path or '..' in PurePosixPath(path).parts:
        raise ValueError('Unsafe deployment path')
    # Private evidence is never served. Its lifecycle is handled by audit cleanup.
    if '/storage/app/private/' in path or '/toolkit-private-storage/' in path:
        return
    prefix=next((root for root in PUBLIC_ROOTS+PRIVATE_APPS if path.startswith(root)),None)
    if prefix is None:
        return  # Other historic integrations retain their own explicit file lists.
    relative=PurePosixPath(path[len(prefix):]);name=relative.name.lower()
    if any(part in FORBIDDEN_DIRS for part in relative.parts):
        raise ValueError('Development directories are excluded from deployment')
    if name in LICENSE_NAMES:
        return
    if DOC_NAME.match(name) or relative.suffix.lower() in FORBIDDEN_SUFFIXES or re.search(r'\.(?:bak|old|orig|save)(?:[._-]|$)',name):
        raise ValueError('Documents, source maps, archives and backups are excluded from application deployment')
    if name=='.env' and prefix in PUBLIC_ROOTS:
        raise ValueError('Environment secrets cannot be deployed inside a public root')
    if (name.endswith('.env') and name!='.env'):
        raise ValueError('Secret configuration must be provisioned separately')
    if name in {'.env.example','dockerfile','docker-compose.yml','docker-compose.yaml','phpunit.xml','package-lock.json'}:
        raise ValueError('Development configuration is excluded from deployment')
