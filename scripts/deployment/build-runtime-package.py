#!/usr/bin/env python3
"""Build allowlisted runtime archives; never package the whole repository."""
import argparse,json,re,tarfile
from pathlib import Path
PROFILES={
 'campus':['app','bootstrap','config','database','public','resources','routes','vendor','artisan','composer.json','composer.lock'],
 'reception':['app','bootstrap','config','database','public','resources','routes','vendor','artisan','composer.json','composer.lock'],
 'reporting':['app.py','safe_files.py','passenger_wsgi.py','wsgi.py','requirements.txt','templates','static','data'],
 'chatbot':['chatbot.py','database.py','faq_matcher.py','logger.py','session_manager.py','state_machine.py','web_app.py','webhook.py','templates','requirements.txt'],
 'wordpress':['wp-content/themes/eduma-child','wp-content/mu-plugins/toolkit-runtime-hardening.php'],
}
FORBIDDEN_DIRS={'.git','.github','.codex','.agents','docs','reports','tests','test','__pycache__','node_modules','logs','sessions','cache'}
LICENSE_NAMES={'license.md','licence.md','copyright.md','notice.md'}
def allowed(path):
 name=path.name.lower()
 if path.parts[:2] in {('resources','js'),('resources','css'),('database','factories')}:return False
 if any(p.lower() in FORBIDDEN_DIRS for p in path.parts):return False
 if len(path.parts)>=2 and path.parts[:2]==('vendor','bin'):return False
 if name in LICENSE_NAMES:return True
 if path.suffix.lower()=='.txt' and name not in {'requirements.txt','robots.txt','security.txt','llms.txt','license.txt','licence.txt','copyright.txt','notice.txt'}:return False
 if re.match(r'^(readme|changelog|changes|contributing|worklog|agents|claude|todo|deployment|hardening)(?:[._-]|$)',name):return False
 if name in {'review-router.php','router.php'}:return False
 if name.startswith('.env') or name in ['dockerfile','docker-compose.yml','docker-compose.yaml','phpunit.xml'] or re.search(r'\.(?:md|rst|pdf|docx?|xlsx?|pptx?|sql|sqlite3?|db|bak|old|orig|patch|zip|tgz|tar|gz|7z|log|map|py[co]|env|pem|key|p12|pfx)(?:[._-].*)?$',name):return False
 return True

def build(profile,source,destination):
 source=source.resolve();destination=destination.resolve()
 if destination.is_relative_to(source):raise ValueError('Package output must be outside the source tree')
 if profile in ('campus','reception') and (source/'vendor').exists():
  metadata=source/'vendor/composer/installed.json'
  if any((source/part).is_symlink() for part in ('vendor','vendor/composer','vendor/composer/installed.json')) or not metadata.is_file():raise ValueError('A regular Composer production-install manifest is required')
  installed=json.loads(metadata.read_text())
  if not isinstance(installed,dict) or installed.get('dev') is not False or installed.get('dev-package-names') != []:raise ValueError('Development dependencies are not runtime payloads; prepare a separate clean composer install --no-dev staging tree')
 entries=[]
 for relative in PROFILES[profile]:
  path=source/relative
  if not path.exists():continue
  if path.is_symlink():raise ValueError('Symlink runtime root requires explicit review: '+relative)
  for p in sorted(path.rglob('*')) if path.is_dir() else [path]:
   rel=p.relative_to(source)
   if p.is_file() and allowed(rel):
    if p.is_symlink() or not p.resolve().is_relative_to(source) or any((source/Path(*rel.parts[:i])).is_symlink() for i in range(1,len(rel.parts))):raise ValueError('Symlink requires explicit runtime review: '+str(rel))
    entries.append((p,rel))
 if not entries:raise ValueError('No runtime files selected')
 with tarfile.open(destination,'w:gz') as archive:
  for p,rel in entries:archive.add(p,arcname=str(rel),recursive=False)
  if profile in ('campus','reception'):
   for name in ['storage/app/private','storage/app/public','storage/framework/cache/data','storage/framework/sessions','storage/framework/views','storage/logs','bootstrap/cache']:
    directory=tarfile.TarInfo(name);directory.type=tarfile.DIRTYPE;directory.mode=0o755;archive.addfile(directory)
 with tarfile.open(destination) as archive:
  assert all((member.isdir() and member.name in {'storage/app/private','storage/app/public','storage/framework/cache/data','storage/framework/sessions','storage/framework/views','storage/logs','bootstrap/cache'}) or (member.isfile() and allowed(Path(member.name))) for member in archive.getmembers())
 print('Packaged',len(entries),'allowlisted runtime files; no development documents, secrets, database copies or archives')
if __name__=='__main__':
 p=argparse.ArgumentParser(description=__doc__);p.add_argument('profile',choices=PROFILES);p.add_argument('source',type=Path);p.add_argument('output',type=Path);a=p.parse_args();build(a.profile,a.source,a.output)
