#!/usr/bin/env python3
"""Summarize saved main-site access logs without copying IPs or query secrets."""
import argparse,collections,datetime,json,re
from pathlib import Path
from urllib.parse import parse_qs,unquote,urlsplit

def analyze(directory):
 rows=[]
 for path in sorted((directory/'raw-logs').glob('*')):
  # Scope is the main website, not other subdomains captured during preservation.
  if not (path.name.startswith('access-toolkitafrica.ac.ke') or path.name.startswith('expanded-toolkitafrica.ac.ke')):continue
  for line in path.read_text(errors='replace').splitlines():
   match=re.search(r'\[([^]]+)\] "([A-Z]+) ([^ ]+) HTTP/[^" ]+" (\d{3})',line)
   if not match:continue
   date,method,url,status=match.groups()
   try:
    parsed=urlsplit(url);stamp=datetime.datetime.strptime(date,'%d/%b/%Y:%H:%M:%S %z')
   except ValueError:continue
   action=parse_qs(parsed.query).get('action',[''])[0]
   # Action names alone can be useful; never retain arbitrary query values.
   action=action if re.fullmatch(r'[a-zA-Z0-9_-]{1,80}',action) else ''
   rows.append({'date':stamp,'method':method,'path':unquote(parsed.path),'status':int(status),'action':action})
 if not rows:raise RuntimeError('No usable requests')
 groups={
  'target_image':lambda r:r['path'].endswith('/courses/digital.jpg'),
  'staff_images':lambda r:'/assets/images/team/' in r['path'],
  'write_methods':lambda r:r['method'] in ('PUT','PATCH','DELETE'),
  'image_posts':lambda r:r['method']=='POST' and '/assets/images/' in r['path'],
  'upload_php':lambda r:'/uploads/' in r['path'] and bool(re.search(r'\.(php\d?|phtml|phar)',r['path'],re.I)),
  'upload_interface':lambda r:any(t in r['path'] for t in ('async-upload','upload.php','upload-handler','terminal-xhr')),
  'team_plugin':lambda r:'/wps-team/' in r['path'] or '/team-free/' in r['path'],
  'ajax':lambda r:'admin-ajax.php' in r['path'],
  'auth':lambda r:'wp-login.php' in r['path'],
  'rest_media':lambda r:'/wp-json/wp/v2/media' in r['path'],
 }
 result={'observations':len(rows),'earliest':min(r['date'] for r in rows).isoformat(),'latest':max(r['date'] for r in rows).isoformat(),'methods':dict(collections.Counter(r['method'] for r in rows)),'groups':{}}
 for name,predicate in groups.items():
  matches=[r for r in rows if predicate(r)];counts=collections.Counter((r['method'],r['path'],r['status'],r['action']) for r in matches)
  result['groups'][name]={'observations':len(matches),'top_paths':[dict(zip(('method','path','status','action','count'),(*key,count))) for key,count in counts.most_common(20)]}
 result['notes']='Archived and current logs overlap. Counts are observations, not deduplicated requests. Request bodies, response bodies and authorization state are not available; absence of direct writes does not exclude exploitation through POST handlers or the hosting control plane.'
 (directory/'access-log-summary.json').write_text(json.dumps(result,indent=2)+'\n')
 print('Summarized',len(rows),'observations; no IPs or query secrets retained in summary')
if __name__=='__main__':
 parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('evidence_directory',type=Path);analyze(parser.parse_args().evidence_directory)
