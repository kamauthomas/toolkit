#!/usr/bin/env python3
"""Exercise the exact release acceptance source against disposable SQLite and mocked PDFs."""
import base64
import json
import os
import subprocess
import tempfile
from pathlib import Path

ROOT=Path(__file__).resolve().parents[2]
REPO=ROOT.parent/'reception-system'
assert not (REPO/'bootstrap/cache/config.php').exists(),'Local cached config requires separate review'
source=(ROOT/'scripts/deployment/audit/letter-workspace-runtime-check.php').read_text()[5:]
anchor='$app->make(Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap();'
assert source.count(anchor)==1
fake='''
    Illuminate\\Support\\Facades\\Http::preventStrayRequests();
    Illuminate\\Support\\Facades\\Http::fake(function($r){
        $d=json_decode($r->body(),true);$pdf=chr(37)."PDF-1.4\\nSynthetic local release fixture\\n".chr(37).chr(37)."EOF";
        return Illuminate\\Support\\Facades\\Http::response(['version'=>1,'source'=>'demo','reference'=>$d['reference'],'pdf'=>base64_encode($pdf),'sha256'=>hash('sha256',$pdf),'template_revision'=>str_repeat('a',64)]);
    });
'''
# This injected fixture copy is never used by deployment or uploaded.
fixture=source.replace(anchor,anchor+fake,1).replace('usleep(4000000)','usleep(1)')
with tempfile.TemporaryDirectory(prefix='toolkit-workspace-release-') as temporary:
    for environment in ['demo','production']:
        database=Path(temporary)/(environment+'.sqlite');database.touch(mode=0o600)
        task_env=dict(os.environ)
        task_env.update(APP_ENV='testing',APP_URL='https://reception'+('-demo' if environment=='demo' else '')+'.toolkitafrica.ac.ke',APP_KEY='base64:'+base64.b64encode(os.urandom(32)).decode(),DB_CONNECTION='sqlite',DB_DATABASE=str(database),DB_URL='',CACHE_STORE='array',SESSION_DRIVER='array',LOG_CHANNEL='errorlog',CALLING_LETTERS_ENABLED='true',RECEPTION_WEBSITE_SHARED_SECRET='s'*40,WEBSITE_APPLICATIONS_SOURCE_URL='https://'+('demo.' if environment=='demo' else '')+'toolkitafrica.ac.ke')
        migration=subprocess.run(['php','artisan','migrate','--force'],cwd=REPO,env=task_env,capture_output=True)
        assert migration.returncode==0,'Disposable local schema creation failed'
        result=subprocess.run(['php','-r',fixture,'--',str(REPO),environment],cwd=REPO,env=task_env,capture_output=True)
        receipt=next((json.loads(line) for line in result.stdout.decode().splitlines() if line.startswith('{')),None)
        if result.returncode or not receipt or receipt.get('workspace_acceptance')!='passed':
            print('FAIL:',environment,receipt or {'phase':'acceptance_source_bootstrap'},flush=True)
            raise SystemExit(1)
        print('PASS:',environment,len(receipt['checks']),'release-source checks; disposable DB and mock upstream; no hosting requests.',flush=True)
