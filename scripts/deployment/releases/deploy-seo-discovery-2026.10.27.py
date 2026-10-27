#!/usr/bin/env python3
"""Focused five-file SEO release and one owned resource post; demo before production."""
import argparse,hashlib,importlib.util,json,shlex,subprocess,tempfile,time,uuid
from pathlib import Path
import requests
from bs4 import BeautifulSoup
import xml.etree.ElementTree as ET

ROOT=Path(__file__).resolve().parents[3]
CASE=ROOT/'reports/seo-fixes-2026-10-27'
PREFIX='wp-content/themes/eduma-child/'
BASE='d51d76c'
EXISTING=['functions.php','template-parts/pages/course-detail.php','inc/cultural-week-stories.php','inc/course-catalog.php']
NEW=['inc/seo-discovery.php']
RELEASE='2026.10.27.1'
TIMEOUT_ATTEMPTS=1
TIMEOUT_EVENTS=[]
RUN_JOURNAL=None

def load(name,path):
    s=importlib.util.spec_from_file_location(name,path);m=importlib.util.module_from_spec(s);s.loader.exec_module(m);return m
def sha(raw):return hashlib.sha256(raw).hexdigest()
def save(path,data):path.write_text(json.dumps(data,indent=2)+'\n');path.chmod(0o600)
def timeout_request(send,*args,read_only=False,label='request',**kwargs):
    """Opt-in timeout retries for exact read operations; never replay a write."""
    limit=TIMEOUT_ATTEMPTS if read_only else 1
    for attempt in range(1,limit+1):
        try:return send(*args,**kwargs)
        except requests.Timeout as error:
            TIMEOUT_EVENTS.append({'operation':label,'attempt':attempt,'limit':limit,'error_type':type(error).__name__})
            print(label,'timeout',attempt,'of',limit,flush=True)
            if attempt==limit:raise
            time.sleep(15)

def connection():
    panel=load('panel',Path(__file__).with_name('deploy-reception-graduands-2026.10.15.1.py')).Panel()
    original=panel.s.request;last=[0.0];stopped=[False]
    def paced(method,url,**kwargs):
        if stopped[0]:raise RuntimeError('Requests stopped')
        time.sleep(max(0,4-(time.monotonic()-last[0])))
        kwargs.update(timeout=(10,40),allow_redirects=False)
        params=kwargs.get('params',{})
        read_only=method.upper()=='GET' and (url.endswith('/Fileman/get_file_content') or (
            url==panel.api2 and params.get('cpanel_jsonapi_module')=='Cron' and params.get('cpanel_jsonapi_func')=='listcron'))
        try:response=timeout_request(original,method,url,read_only=read_only,label='hosting read' if read_only else 'hosting write',**kwargs)
        except requests.RequestException:
            stopped[0]=True;raise RuntimeError('Hosting request failed; stopped') from None
        last[0]=time.monotonic()
        if response.status_code!=200 or response.headers.get('Retry-After'):
            stopped[0]=True;raise RuntimeError('Hosting response refused; stopped')
        return response
    panel.s.request=paced;return panel
def site(env):return '/home/bfyigiln/'+('demo.toolkitafrica.ac.ke' if env=='demo' else 'public_html')
def remote(env,name):return site(env)+'/'+PREFIX+name

def validate_partial_previous(previous,activation=False):
    assert not previous['verified'] and not previous.get('cron_submission') and not previous.get('publication')
    if activation:
        assert previous['uploaded']==[] and previous.get('remaining_runtime_files')==['functions.php']
        assert previous.get('upload_intent')=='functions.php' and previous.get('parent_attempt_sha256')
        assert previous['observed']=={r['file']:(r['baseline_sha256'] if r['file']=='functions.php' else r['sha256']) for r in previous['files']}
        return
    assert set(previous['uploaded'])=={'inc/seo-discovery.php','template-parts/pages/course-detail.php','inc/cultural-week-stories.php'}
    assert previous.get('upload_intent')=='inc/course-catalog.php'

def reconcile_observed(previous,rows,observed,activation=False):
    """Only reconcile the saved pre-worker catalogue-upload uncertainty."""
    validate_partial_previous(previous,activation)
    assert set(observed)=={r['file'] for r in rows}
    for row in rows:
        name=row['file'];actual=observed[name]
        allowed={row['sha256']} if activation or name in previous['uploaded'] else {row['baseline_sha256']}
        if activation and name=='functions.php':allowed.add(row['baseline_sha256'])
        if name==previous['upload_intent']:allowed.add(row['sha256'])
        if actual not in allowed:raise RuntimeError('Reconciliation source drift; review the saved fresh file')
    return [r['file'] for r in rows if observed[r['file']]!=r['sha256']]

def prepare(env,source):
    global RUN_JOURNAL
    assert len(source)==40 and all(c in 'abcdef0123456789' for c in source)
    out=CASE/env;out.mkdir(parents=True,exist_ok=True,mode=0o700);journal=out/'preparation.json'
    if journal.exists():raise RuntimeError('Existing preparation; reconcile instead of replacing it')
    state={'environment':env,'source':source,'prepared':False,'files':[]};save(journal,state)
    RUN_JOURNAL=journal
    panel=connection();policy=load('policy',ROOT/'scripts/deployment/runtime_payload.py')
    with tempfile.TemporaryDirectory(prefix='toolkit-seo-discovery-') as tmp:
        stage=Path(tmp)
        for name in EXISTING+NEW:
            policy.assert_runtime_upload(remote(env,name));raw=panel.fetch(remote(env,name),optional=name in NEW)
            if name in NEW:assert raw is None,'Unexpected existing SEO module'
            else:
                path=stage/PREFIX/name;path.parent.mkdir(parents=True,exist_ok=True);path.write_bytes(raw)
                before=out/'before'/name;before.parent.mkdir(parents=True,exist_ok=True);before.write_bytes(raw);before.chmod(0o600)
            state['files'].append({'file':name,'remote':remote(env,name),'baseline_sha256':None if raw is None else sha(raw)});save(journal,state)
        diff=subprocess.check_output(['git','diff','--no-ext-diff',BASE,source,'--',*[PREFIX+n for n in EXISTING+NEW]],cwd=ROOT)
        patched=subprocess.run(['patch','--batch','--fuzz=0','-p1','-d',str(stage)],input=diff,capture_output=True)
        if patched.returncode:raise RuntimeError('Fresh-source patch did not match; review drift')
        assert {str(p.relative_to(stage)) for p in stage.rglob('*') if p.is_file()}=={PREFIX+n for n in EXISTING+NEW}
        for row in state['files']:
            raw=(stage/PREFIX/row['file']).read_bytes();path=out/'payload'/row['file'];path.parent.mkdir(parents=True,exist_ok=True);path.write_bytes(raw)
            subprocess.run(['php','-l',str(path)],check=True,stdout=subprocess.DEVNULL);row['sha256']=sha(raw)
            expected=subprocess.check_output(['git','show',source+':'+PREFIX+row['file']],cwd=ROOT)
            if row['file'] in NEW:assert raw==expected
    state['prepared']=True;save(journal,state);print('PREPARED',env,'5 allowlisted/linted runtime files; no uploads',flush=True)

def deploy(env,reconcile=False,activation=False):
    global RUN_JOURNAL
    assert not activation or reconcile
    prepared=CASE/env;out=prepared;plan=json.loads((prepared/'preparation.json').read_text());assert plan['prepared'] and plan['environment']==env
    rows=plan['files'];assert {r['file'] for r in rows}==set(EXISTING+NEW) and len(rows)==5
    policy=load('policy',ROOT/'scripts/deployment/runtime_payload.py')
    for row in rows:policy.assert_runtime_upload(row['remote']);assert row['remote']==remote(env,row['file']) and sha((prepared/'payload'/row['file']).read_bytes())==row['sha256']
    if env=='production':
        demo=json.loads((CASE/'demo/verification.json').read_text());assert demo['verified'] and demo['source']==plan['source']
        assert {(r['file'],r['sha256']) for r in rows}=={(r['file'],r['sha256']) for r in demo['files']}
    previous=None
    if reconcile:
        assert env=='demo','Reconciliation is only for the identified demo failure'
        parent=prepared/('reconciliation-1/attempt.json' if activation else 'attempt.json')
        previous=json.loads(parent.read_text());assert previous['source']==plan['source'] and previous['files']==rows
        validate_partial_previous(previous,activation)
        if activation:
            original=json.loads((prepared/'attempt.json').read_text());validate_partial_previous(original)
            assert previous['parent_attempt_sha256']==sha((prepared/'attempt.json').read_bytes())
        assert not (prepared/'verification.json').exists()
        out=prepared/('reconciliation-2' if activation else 'reconciliation-1');out.mkdir(mode=0o700,exist_ok=True)
    journal=out/'attempt.json'
    if journal.exists():raise RuntimeError('Existing attempt; reconcile saved state before any retry')
    state={'environment':env,'source':plan['source'],'files':rows,'verified':False,'uploaded':[],'cron_removed':None};save(journal,state)
    RUN_JOURNAL=journal
    if previous:state['parent_attempt_sha256']=sha(parent.read_bytes());save(journal,state)
    panel=connection()
    observed={}
    for row in sorted(rows,key=lambda r:r['file']!=('functions.php' if activation else 'inc/course-catalog.php')):
        raw=panel.fetch(row['remote'],optional=row['baseline_sha256'] is None)
        observed[row['file']]=None if raw is None else sha(raw)
        if previous:
            path=out/'observed'/row['file'];path.parent.mkdir(parents=True,exist_ok=True)
            if raw is not None:path.write_bytes(raw);path.chmod(0o600)
        elif observed[row['file']]!=row['baseline_sha256']:raise RuntimeError('Fresh deployment drift')
        state['observed']=observed;save(journal,state)
    if previous:state['remaining_runtime_files']=reconcile_observed(previous,rows,observed,activation);save(journal,state)
    for row in sorted(rows,key=lambda r:(r['file'] not in NEW,r['file']=='functions.php')):
        if previous and observed[row['file']]==row['sha256']:continue
        raw=panel.fetch(row['remote'],optional=observed[row['file']] is None);assert (None if raw is None else sha(raw))==observed[row['file']],'Concurrent live edit'
        state['upload_intent']=row['file'];save(journal,state)
        payload=(prepared/'payload'/row['file']).read_bytes();panel.upload(row['remote'],payload);assert panel.fetch(row['remote'])==payload
        state['uploaded'].append(state.pop('upload_intent'));save(journal,state);print(env,'byte-verified',row['file'],flush=True)
    helper=load('letters',Path(__file__).with_name('deploy-calling-letters-2026.10.26.py'))
    php='/usr/local/bin/ea-php84' if env=='demo' else '/usr/local/bin/ea-php83'
    code=helper.inline_php('seo-discovery-publish.php');nonce=uuid.uuid4().hex
    marker='/tmp/toolkit-seo-discovery-'+nonce;log='/home/bfyigiln/toolkit-private-storage/seo-discovery-'+env+'-'+nonce+'.log'
    body=helper.php_command(php,code,site(env))
    command='/bin/sh -c '+shlex.quote('umask 077; if mkdir '+shlex.quote(marker)+' 2>/dev/null; then { '+body+'; code=$?; echo TOOLKIT_SEO_EXIT=$code; } > '+shlex.quote(log)+' 2>&1; fi')
    assert '%' not in command and '\n' not in command
    subprocess.run(['/bin/sh','-n'],input=command.encode(),check=True)
    state.update(command=command,private_log=log,cron_submission='intent',cron_removed=False);save(journal,state)
    result=panel.api2call('Cron','add_line',command=command,minute='*',hour='*',day='*',month='*',weekday='*');assert result.get('event',{}).get('result')==1
    state['cron_submission']='accepted';save(journal,state);receipt=None;stop=False
    try:
        for _ in range(24):
            receipt=panel.fetch(log,optional=True)
            if receipt and b'TOOLKIT_SEO_EXIT=' in receipt:break
            time.sleep(5)
        else:raise RuntimeError('Completion not observed; inspect the private one-attempt state')
    except RuntimeError as e:
        if str(e) in {'Hosting request failed; stopped','Hosting response refused; stopped','Requests stopped'}:stop=True
        raise
    except Exception:stop=True;raise
    finally:
        if receipt:(out/'runtime.private.log').write_bytes(receipt)
        if not stop:
            for r in panel.api2call('Cron','listcron').get('data',[]):
                if r.get('command')==command:assert panel.api2call('Cron','remove_line',line=r['count']).get('event',{}).get('result')==1
            state['cron_removed']=not any(r.get('command')==command for r in panel.api2call('Cron','listcron').get('data',[]));save(journal,state)
    if not receipt or b'TOOLKIT_SEO_EXIT=0' not in receipt:raise RuntimeError('Publication/cache worker failed; reconcile private state, do not replay')
    publication=next(json.loads(line) for line in receipt.decode().splitlines() if line.startswith('{') and json.loads(line).get('seo_release')=='passed')
    state['publication']=publication;save(journal,state)
    verify_public(env,out,state,prepared)

def resume_verification(env):
    global RUN_JOURNAL
    """Recheck completed runtime/job state without uploads or publication replay."""
    prepared=CASE/env
    parent=prepared/('reconciliation-2/attempt.json' if env=='demo' else 'attempt.json')
    previous=json.loads(parent.read_text());plan=json.loads((prepared/'preparation.json').read_text())
    assert not previous['verified'] and previous['cron_removed'] is True
    assert previous['cron_submission']=='accepted' and previous['publication']['seo_release']=='passed'
    assert previous['files']==plan['files'] and previous['source']==plan['source']
    assert len(plan['files'])==5 and {r['file'] for r in plan['files']}==set(EXISTING+NEW)
    receipt=(parent.parent/'runtime.private.log').read_bytes()
    assert b'TOOLKIT_SEO_EXIT=0' in receipt
    assert previous['publication'] in [json.loads(line) for line in receipt.decode().splitlines() if line.startswith('{')]
    assert not (prepared/'verification.json').exists()
    if env=='production':
        demo=json.loads((CASE/'demo/verification.json').read_text());assert demo['verified'] and demo['source']==plan['source']
        assert {(r['file'],r['sha256']) for r in plan['files']}=={(r['file'],r['sha256']) for r in demo['files']}
    policy=load('policy',ROOT/'scripts/deployment/runtime_payload.py')
    for row in plan['files']:
        assert row['remote']==remote(env,row['file']);policy.assert_runtime_upload(row['remote'])
        assert sha((prepared/'payload'/row['file']).read_bytes())==row['sha256']
    out=prepared/'verification-resume-1';out.mkdir(mode=0o700,exist_ok=True)
    journal=out/'attempt.json'
    if journal.exists():raise RuntimeError('Existing verification continuation; inspect before retry')
    state=dict(previous);state['verification_parent_sha256']=sha(parent.read_bytes());state['verification_only']=True;save(journal,state)
    RUN_JOURNAL=journal
    panel=connection()
    for row in plan['files']:
        assert sha(panel.fetch(row['remote']))==row['sha256'],'Runtime drift; no verification promotion'
    cron=panel.api2call('Cron','listcron');assert cron.get('event',{}).get('result')==1
    assert not any(r.get('command')==previous['command'] for r in cron.get('data',[])),'Publication cron is still present'
    verify_public(env,out,state,prepared)

def verify_public(env,out,state,prepared):
    journal=out/'attempt.json';publication=state['publication']
    base='https://'+('demo.' if env=='demo' else '')+'toolkitafrica.ac.ke';checks=[]
    inspector=load('inspect_seo',ROOT/'scripts/seo-review-2026-10-27.py')
    def get(path):
        time.sleep(5)
        try:r=timeout_request(requests.get,base+path,read_only=True,label='public read '+path,timeout=(8,25),allow_redirects=False)
        except requests.RequestException:raise RuntimeError('Normal public verification timed out; stopped') from None
        if r.status_code!=200 or r.headers.get('Retry-After'):raise RuntimeError('Normal public verification refused; stopped')
        (out/('http-'+sha(path.encode())[:12]+'.private.txt')).write_text(r.text)
        checks.append({'path':path,'status':r.status_code});return r
    sitemap=get('/page-sitemap.xml');xml=ET.fromstring(sitemap.content);urls=[n.text for n in xml.findall('./{*}url/{*}loc')]
    assert len(urls)==len(set(urls)),'Duplicate sitemap URLs'
    for slug in publication['catalogue_slugs']:assert urls.count(base+'/courses/'+slug+'/')==1,'Missing enabled programme'
    assert not any(p in u for u in urls for p in ['/instructors/','/reset-password/','/wp-content/uploads/'])
    posts=get('/post-sitemap.xml');post_urls=[n.text for n in ET.fromstring(posts.content).findall('./{*}url/{*}loc')];assert post_urls.count(publication['url'])==1
    paths=['/','/apply/','/graduation/','/testimonials/','/footprint/','/our-ventures/','/our-ventures/construction-sector-skills/','/courses/advanced-welding-vr/','/courses/advanced-welding-upskilling/','/toolkit-blog/','/class-of-2026-programme-and-graduands/']
    for path in paths:
        r=get(path);assert r.headers.get('X-Toolkit-Release')==RELEASE,(path,'release header')
        data=inspector.inspect_html(base+path,r,expected_noindex=env=='demo')
        assert not data['issues'],(path,data['issues'])
        assert len(data['canonicals'])==1 and data['canonicals'][0]=='https://toolkitafrica.ac.ke'+path,(path,'canonical missing/duplicate/wrong')
        assert len(data['h1'])==1 and data['json_ld_blocks']==1,(path,'heading/schema owner')
        assert ('noindex' in ','.join(data['robots']))==(env=='demo')
        if path=='/our-ventures/construction-sector-skills/':
            for slug in ['advanced-welding-vr','advanced-welding-upskilling']:assert any(l['href']==base+'/courses/'+slug+'/' for l in data['links'])
        if path=='/courses/advanced-welding-vr/':assert 'KES 127,895' in data['main_text']
        if path=='/courses/advanced-welding-upskilling/':assert 'KES 55,017' in data['main_text']
        if path.startswith('/courses/'):
            types=[t for x in data['schema_types'] for t in (x if isinstance(x,list) else [x])];assert all(t in types for t in ['Course','WebPage','BreadcrumbList'])
        if path=='/toolkit-blog/':assert 'Class of 2026: programme and graduands' in r.text
        if path=='/class-of-2026-programme-and-graduands/':
            assert 'kmm-2071-jpg.webp' in r.text and '/graduation/#programme' in r.text and '/graduation/#graduands' in r.text
    state.update(verified=True,public_checks=checks,page_sitemap_count=len(urls),post_sitemap_count=len(post_urls),production_test_applications=0,php_or_dependencies_changed=False,timeout_attempt_limit=TIMEOUT_ATTEMPTS,timeout_events=list(TIMEOUT_EVENTS))
    save(journal,state);save(out/'verification.json',state)
    if out!=prepared:save(prepared/'verification.json',state)
    print('VERIFIED',env,'5 runtime hashes, sitemap/canonicals/schema/welding links/blog resource, private job removed; no application submissions',flush=True)

if __name__=='__main__':
    p=argparse.ArgumentParser(description=__doc__);p.add_argument('environment',choices=['demo','production']);p.add_argument('--prepare',metavar='FULL_SOURCE_COMMIT');p.add_argument('--deploy',action='store_true');p.add_argument('--verify-only',action='store_true');p.add_argument('--reconcile-partial-demo',action='store_true');p.add_argument('--reconcile-activation-demo',action='store_true');p.add_argument('--timeout-attempts',type=int,choices=[1,4],default=1);a=p.parse_args()
    TIMEOUT_ATTEMPTS=a.timeout_attempts
    try:
        assert sum([bool(a.prepare),a.deploy,a.verify_only])==1
        assert not (a.reconcile_partial_demo and a.reconcile_activation_demo)
        assert not (a.reconcile_partial_demo or a.reconcile_activation_demo) or (a.deploy and a.environment=='demo')
        if a.prepare:prepare(a.environment,a.prepare)
        elif a.verify_only:resume_verification(a.environment)
        else:deploy(a.environment,a.reconcile_partial_demo or a.reconcile_activation_demo,a.reconcile_activation_demo)
    except Exception as e:
        print('STOPPED:',str(e) if isinstance(e,RuntimeError) else type(e).__name__,'— reconcile saved state before retry',flush=True);raise SystemExit(1)
    finally:
        if RUN_JOURNAL:save(RUN_JOURNAL.parent/(RUN_JOURNAL.stem+'-transport.json'),{'timeout_attempt_limit':TIMEOUT_ATTEMPTS,'timeout_events':TIMEOUT_EVENTS,'write_retries':0})
