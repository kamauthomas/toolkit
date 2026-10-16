#!/usr/bin/env python3
"""Replace only the About Us illustration, preserving deployed-file drift."""
import argparse,hashlib,importlib.util,json,re,subprocess,time
from pathlib import Path
import requests
ROOT=Path(__file__).resolve().parents[3]
spec=importlib.util.spec_from_file_location('release_panel',Path(__file__).with_name('deploy-reception-graduands-2026.10.15.1.py'))
module=importlib.util.module_from_spec(spec);spec.loader.exec_module(module)
EVIDENCE=ROOT/'reports/asset-incident-2026-10-16'
REL='2026.10.16.1'
THEME='wp-content/themes/eduma-child/'
OLD="""<img src="<?php echo esc_url( $courses . 'digital.jpg' ); ?>" width="720" height="520" loading="lazy" alt="Learners reviewing course pathways and progression options">"""
NEW="""<img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/hero-slide-1.jpg' ); ?>" width="720" height="520" loading="lazy" alt="The Toolkit Skills and Innovation Hub campus">"""
def run(env):
 if env=='production':
  demo=json.loads((EVIDENCE/'deployment/demo/verification.json').read_text());assert demo['verified'] and demo['release']==REL
 out=EVIDENCE/'deployment'/env
 if (out/'verification.json').exists():raise RuntimeError('Already verified; do not repeat deployment')
 out.mkdir(parents=True,exist_ok=True)
 panel=module.Panel();root='/home/bfyigiln/'+('demo.toolkitafrica.ac.ke' if env=='demo' else 'public_html')
 base='https://'+('demo.' if env=='demo' else '')+'toolkitafrica.ac.ke'
 payload={};before={}
 for name,old,new in [('template-parts/pages/institutional.php',OLD,NEW),('functions.php',"return '2026.10.15.2';","return '2026.10.16.1';")]:
  source=panel.fetch(root+'/'+THEME+name)
  baseline=EVIDENCE/('baseline/demo' if env=='demo' else 'deployed-source/'+THEME)/name
  assert source==baseline.read_bytes(),'Baseline changed: '+name
  text=source.decode();assert text.count(old)==1,'Anchor not unique: '+name
  data=text.replace(old,new).encode();before[name]=source;payload[name]=data
  dest=out/'payload'/name;dest.parent.mkdir(parents=True,exist_ok=True);dest.write_bytes(data)
  subprocess.run(['php','-l',str(dest)],check=True,stdout=subprocess.DEVNULL)
  panel.upload('/home/bfyigiln/reception-system/storage/app/private/asset-incident-2026-10-16/rollback/'+env+'/'+name,source)
 # Each replacement is checked again immediately before writing.
 for name,data in payload.items():
  assert panel.fetch(root+'/'+THEME+name)==before[name],'Concurrent edit: '+name
  panel.upload(root+'/'+THEME+name,data)
 for name,data in payload.items():assert panel.fetch(root+'/'+THEME+name)==data,'Upload mismatch: '+name
 observations=[]
 def get(path):
  time.sleep(3);r=requests.get(base+path,timeout=50);r.raise_for_status();observations.append({'path':path,'status':r.status_code,'release':r.headers.get('X-Toolkit-Release')});return r
 get('/about-toolkit-africa/?image_review='+REL)
 response=get('/about-toolkit-africa/')
 assert response.headers.get('X-Toolkit-Release')==REL,'Stale release'
 assert 'alt="The Toolkit Skills and Innovation Hub campus"' in response.text
 assert '/courses/digital.jpg' not in response.text
 assert '/assets/images/hero-slide-1.jpg' in response.text
 assert len(re.findall(r'/assets/images/team/[^"<> ]+',response.text))==14,'Staff portrait count changed'
 assert not re.search(r'fatal error|uncaught error|critical error',response.text,re.I)
 (out/'about.html').write_text(response.text)
 asset=get('/'+THEME+'assets/images/hero-slide-1.jpg')
 assert asset.content==(ROOT/THEME/'assets/images/hero-slide-1.jpg').read_bytes()
 home=get('/');assert home.headers.get('X-Toolkit-Release')==REL
 assert not re.search(r'fatal error|uncaught error|critical error',home.text,re.I)
 result={'environment':env,'release':REL,'verified':True,'files':{n:hashlib.sha256(d).hexdigest() for n,d in payload.items()},'observations':observations,'staff_portraits_in_about':14,'image_sha256':hashlib.sha256(asset.content).hexdigest(),'old_image_overwritten':False}
 (out/'verification.json').write_text(json.dumps(result,indent=2)+'\n');print('VERIFIED',env,REL,'About Us uses campus image; 14 staff portraits preserved',flush=True)
if __name__=='__main__':
 parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('environment',choices=['demo','production']);run(parser.parse_args().environment)
