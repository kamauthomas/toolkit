#!/usr/bin/env python3
"""Prepare/deploy the calling-letter workspace from fresh Reception files, demo first."""
import argparse
import hashlib
import importlib.util
import json
import shlex
import subprocess
import tempfile
import time
import uuid
from pathlib import Path
import requests

ROOT=Path(__file__).resolve().parents[3]
REPO=ROOT.parent/'reception-system'
CASE=ROOT/'reports/calling-letter-workspace-2026-10-26'
BASE='fbea7f2'
EXISTING=['app/Http/Controllers/Staff/CallingLetterController.php','app/Http/Middleware/EnsureStaffRole.php','resources/views/layouts/staff.blade.php','resources/views/staff/calling-letters/index.blade.php','routes/web.php']
NEW=['app/Http/Controllers/Staff/CallingLetterWorkspaceController.php','app/Models/ManualCallingLetter.php','database/migrations/2026_10_26_170000_create_manual_calling_letters_table.php','public/assets/css/calling-letters.css','public/assets/js/calling-letters.js',*[f'resources/views/staff/calling-letters/{n}.blade.php' for n in ['assets','create','manual','review','workspace']]]

def load(name,path):
    spec=importlib.util.spec_from_file_location(name,path);m=importlib.util.module_from_spec(spec);spec.loader.exec_module(m);return m
def sha(raw):return hashlib.sha256(raw).hexdigest()
def save(path,data):path.write_text(json.dumps(data,indent=2)+'\n');path.chmod(0o600)
def connection():return load('connection',Path(__file__).with_name('finish-runtime-cleanup-2026.10.26.py')).connection()
def remote(environment,name):
    suffix='-demo' if environment=='demo' else ''
    return '/home/bfyigiln/reception-public'+suffix+'/'+name[7:] if name.startswith('public/') else '/home/bfyigiln/reception-system'+suffix+'/'+name

def prepare(environment,revision):
    assert len(revision)==40 and all(c in '0123456789abcdef' for c in revision)
    output=CASE/environment;output.mkdir(parents=True,exist_ok=True,mode=0o700)
    journal=output/'preparation.json'
    if journal.exists():raise RuntimeError('Existing preparation; inspect saved state instead of repeating')
    state={'environment':environment,'source':revision,'base':BASE,'prepared':False,'files':[]};save(journal,state)
    panel=connection();policy=load('policy',ROOT/'scripts/deployment/runtime_payload.py')
    with tempfile.TemporaryDirectory(prefix='calling-letter-workspace-') as temporary:
        stage=Path(temporary)
        for name in EXISTING+NEW:
            path=remote(environment,name);policy.assert_runtime_upload(path)
            raw=panel.fetch(path,optional=name in NEW)
            if name in NEW:assert raw is None,'Unexpected existing new runtime file'
            else:
                assert raw is not None
                target=stage/name;target.parent.mkdir(parents=True,exist_ok=True);target.write_bytes(raw)
                before=output/'before'/name;before.parent.mkdir(parents=True,exist_ok=True);before.write_bytes(raw);before.chmod(0o600)
            state['files'].append({'file':name,'remote':path,'baseline_sha256':None if raw is None else sha(raw)});save(journal,state)
        diff=subprocess.check_output(['git','-C',str(REPO),'diff','--no-ext-diff',BASE,revision,'--',*EXISTING,*NEW])
        patched=subprocess.run(['patch','--batch','--fuzz=0','-p1','-d',str(stage)],input=diff,capture_output=True)
        if patched.returncode:raise RuntimeError('Focused patch did not match fresh runtime sources')
        assert {str(p.relative_to(stage)) for p in stage.rglob('*') if p.is_file()}==set(EXISTING+NEW)
        for row in state['files']:
            raw=(stage/row['file']).read_bytes();path=output/'payload'/row['file'];path.parent.mkdir(parents=True,exist_ok=True);path.write_bytes(raw)
            if row['file'] in NEW:assert raw==subprocess.check_output(['git','-C',str(REPO),'show',revision+':'+row['file']])
            subprocess.run(['php','-l',str(path)] if row['file'].endswith('.php') else ['node','--check',str(path)] if row['file'].endswith('.js') else ['true'],check=True,stdout=subprocess.DEVNULL)
            row['sha256']=sha(raw)
    state['prepared']=True;save(journal,state)
    print('PREPARED',environment,len(state['files']),'runtime files, fresh zero-fuzz patches; no uploads',flush=True)

def deploy(environment):
    output=CASE/environment;plan=json.loads((output/'preparation.json').read_text());assert plan['prepared'] and plan['environment']==environment
    rows=plan['files'];assert {r['file'] for r in rows}==set(EXISTING+NEW) and len(rows)==15
    policy=load('policy',ROOT/'scripts/deployment/runtime_payload.py')
    for r in rows:
        assert r['remote']==remote(environment,r['file']);policy.assert_runtime_upload(r['remote']);assert sha((output/'payload'/r['file']).read_bytes())==r['sha256']
    if environment=='production':
        demo=json.loads((CASE/'demo/verification.json').read_text());assert demo['verified'] and demo['source']==plan['source']
        assert {(r['file'],r['sha256']) for r in rows}=={(r['file'],r['sha256']) for r in demo['files']}
    journal=output/'attempt.json'
    if journal.exists():raise RuntimeError('Existing attempt; reconcile it, never blindly replay')
    panel=connection();state={'environment':environment,'source':plan['source'],'verified':False,'files':rows,'uploaded':[],'cron_removed':None,'php_configuration_or_dependencies_changed':False};save(journal,state)
    for row in rows:
        raw=panel.fetch(row['remote'],optional=row['baseline_sha256'] is None)
        assert (None if raw is None else sha(raw))==row['baseline_sha256'],'Fresh runtime drift'
    print(environment,'15 current baseline hashes match',flush=True)
    # Dependencies/assets first; expose routes before updated views, navigation last.
    def order(r):
        if r['file'] in NEW:return 0
        if r['file']=='routes/web.php':return 2
        if r['file']=='resources/views/layouts/staff.blade.php':return 4
        return 3
    for row in sorted(rows,key=order):
        raw=panel.fetch(row['remote'],optional=row['baseline_sha256'] is None)
        assert (None if raw is None else sha(raw))==row['baseline_sha256'],'Concurrent runtime edit'
        state['upload_intent']=row['file'];save(journal,state)
        payload=(output/'payload'/row['file']).read_bytes();panel.upload(row['remote'],payload);assert panel.fetch(row['remote'])==payload
        state['uploaded'].append(state.pop('upload_intent'));save(journal,state);print(environment,'verified',row['file'],flush=True)
    helper=load('letters',Path(__file__).with_name('deploy-calling-letters-2026.10.26.py'))
    php='/usr/local/bin/ea-php84';app='/home/bfyigiln/reception-system'+('-demo' if environment=='demo' else '')
    nonce=uuid.uuid4().hex;marker='/tmp/toolkit-letter-workspace-'+nonce;log=app+'/storage/logs/letter-workspace-'+nonce+'.log'
    code=helper.inline_php('letter-workspace-runtime-check.php')
    body='; '.join(['set -eu','umask 077',shlex.join([php,app+'/artisan','migrate','--force','--path=database/migrations/2026_10_26_170000_create_manual_calling_letters_table.php']),shlex.join([php,app+'/artisan','route:clear']),shlex.join([php,app+'/artisan','view:clear']),helper.php_command(php,code,app,environment),'echo LETTER_WORKSPACE_READY'])
    subprocess.run(['/bin/sh','-n'],input=body.encode(),check=True)
    command='/bin/sh -c '+shlex.quote('umask 077; if mkdir '+shlex.quote(marker)+' 2>/dev/null; then { /bin/sh -c '+shlex.quote(body)+'; code=$?; echo TOOLKIT_WORKSPACE_EXIT=$code; } > '+shlex.quote(log)+' 2>&1; fi')
    assert '\n' not in command and '%' not in command
    state.update(command=command,private_log=log,cron_removed=False,cron_submission='intent');save(journal,state)
    result=panel.api2call('Cron','add_line',command=command,minute='*',hour='*',day='*',month='*',weekday='*');assert result.get('event',{}).get('result')==1
    state['cron_submission']='accepted';save(journal,state)
    receipt=None;stop=False
    try:
        for _ in range(30):
            receipt=panel.fetch(log,optional=True)
            if receipt and b'TOOLKIT_WORKSPACE_EXIT=' in receipt:break
            time.sleep(5)
        else:raise RuntimeError('Completion not observed; reconcile saved state')
        for line in receipt.decode().splitlines():
            if line.startswith('{') and json.loads(line).get('network_stop'):
                stop=True;raise RuntimeError('Runtime connection refused; stop live requests')
    except RuntimeError as error:
        if str(error) in {'Hosting request failed; stopped','Hosting response refused; stopped','Requests stopped'}:stop=True
        raise
    except Exception:
        stop=True;raise
    finally:
        if receipt:(output/'runtime.private.log').write_bytes(receipt)
        if not stop:
            for cron in panel.api2call('Cron','listcron').get('data',[]):
                if cron.get('command')==command:assert panel.api2call('Cron','remove_line',line=cron['count']).get('event',{}).get('result')==1
            state['cron_removed']=not any(r.get('command')==command for r in panel.api2call('Cron','listcron').get('data',[]));save(journal,state)
    if not receipt or b'TOOLKIT_WORKSPACE_EXIT=0' not in receipt or b'LETTER_WORKSPACE_READY' not in receipt:raise RuntimeError('Runtime acceptance failed; do not promote; reconcile saved state')
    state['acceptance']=next(json.loads(line) for line in receipt.decode().splitlines() if line.startswith('{') and json.loads(line).get('workspace_acceptance')=='passed')
    base='https://reception'+('-demo' if environment=='demo' else '')+'.toolkitafrica.ac.ke'
    checks=[]
    for path,name in [('/login',None),('/assets/css/calling-letters.css?v=20261026.2','public/assets/css/calling-letters.css'),('/assets/js/calling-letters.js?v=20261026.2','public/assets/js/calling-letters.js')]:
        time.sleep(4)
        try:r=requests.get(base+path,timeout=(10,40),allow_redirects=False)
        except requests.RequestException:raise RuntimeError('Normal HTTP check failed; stopped') from None
        if r.status_code!=200 or r.headers.get('Retry-After'):raise RuntimeError('Normal HTTP refused; stopped')
        if name:assert sha(r.content)==next(row['sha256'] for row in rows if row['file']==name)
        checks.append({'path':path,'status':200})
    state.update(verified=True,public_checks=checks);save(journal,state);save(output/'verification.json',state)
    print('VERIFIED',environment,'workspace, manual-letter flow, 15 runtime hashes, normal login/assets, cron removed; no PHP/dependency change',flush=True)

if __name__=='__main__':
    parser=argparse.ArgumentParser(description=__doc__);parser.add_argument('environment',choices=['demo','production']);parser.add_argument('--prepare',metavar='FULL_SOURCE_COMMIT');parser.add_argument('--deploy',action='store_true');args=parser.parse_args()
    try:
        assert bool(args.prepare)!=args.deploy
        prepare(args.environment,args.prepare) if args.prepare else deploy(args.environment)
    except Exception as error:
        print('STOPPED:',str(error) if isinstance(error,RuntimeError) else type(error).__name__,'— inspect private state before retry',flush=True);raise SystemExit(1)
