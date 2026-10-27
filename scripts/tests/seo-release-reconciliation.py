#!/usr/bin/env python3
"""Exercise the actual partial-demo recovery through a fake Fileman, never hosting."""
import importlib.util,json,tempfile,unittest
from pathlib import Path
from unittest.mock import patch

ROOT=Path(__file__).resolve().parents[2]
spec=importlib.util.spec_from_file_location('seo_release',ROOT/'scripts/deployment/releases/deploy-seo-discovery-2026.10.27.py')
release=importlib.util.module_from_spec(spec);spec.loader.exec_module(release)
class ReachedCron(Exception):pass
class FakePanel:
    def __init__(self,files):self.files=dict(files);self.uploaded=[]
    def fetch(self,path,optional=False):return self.files.get(path)
    def upload(self,path,data):self.uploaded.append(path);self.files[path]=data
    def api2call(self,*args,**kwargs):raise ReachedCron('Mock boundary reached; no job created')

class VerificationPanel(FakePanel):
    def __init__(self,files):super().__init__(files);self.calls=[]
    def api2call(self,module,action,**kwargs):
        self.calls.append((module,action));assert (module,action)==('Cron','listcron')
        return {'event':{'result':1},'data':[]}

class RecoveryTests(unittest.TestCase):
    def fixture(self,root,catalogue_applied=False):
        out=root/'demo';out.mkdir();rows=[];remote={}
        done=['inc/seo-discovery.php','template-parts/pages/course-detail.php','inc/cultural-week-stories.php']
        for name in release.EXISTING+release.NEW:
            old=None if name in release.NEW else ('<?php /* old '+name+' */').encode()
            new=('<?php /* new '+name+' */').encode();path=out/'payload'/name;path.parent.mkdir(parents=True,exist_ok=True);path.write_bytes(new)
            row={'file':name,'remote':release.remote('demo',name),'baseline_sha256':None if old is None else release.sha(old),'sha256':release.sha(new)};rows.append(row)
            remote[row['remote']]=new if name in done or (name=='inc/course-catalog.php' and catalogue_applied) else old
        plan={'environment':'demo','source':'c648685594f0c2b3a305f6f816fc584190b45d21','prepared':True,'files':rows}
        previous={'environment':'demo','source':plan['source'],'files':rows,'verified':False,'uploaded':done,'upload_intent':'inc/course-catalog.php','cron_removed':None}
        (out/'preparation.json').write_text(json.dumps(plan));(out/'attempt.json').write_text(json.dumps(previous))
        return rows,previous,FakePanel(remote)
    def test_old_or_completed_catalogue_can_finish_without_reuploading_verified_files(self):
        for applied in [False,True]:
            with self.subTest(catalogue_applied=applied),tempfile.TemporaryDirectory() as temp:
                root=Path(temp);rows,prior,panel=self.fixture(root,applied);original=(root/'demo/attempt.json').read_bytes()
                with patch.object(release,'CASE',root),patch.object(release,'connection',return_value=panel):
                    with self.assertRaises(ReachedCron):release.deploy('demo',True)
                names=[r['file'] for p in panel.uploaded for r in rows if r['remote']==p]
                self.assertEqual(['functions.php'] if applied else ['inc/course-catalog.php','functions.php'],names)
                self.assertEqual(original,(root/'demo/attempt.json').read_bytes())
                self.assertTrue((root/'demo/reconciliation-1/attempt.json').exists())
    def test_verified_file_drift_stops_before_any_mutation(self):
        with tempfile.TemporaryDirectory() as temp:
            root=Path(temp);rows,prior,panel=self.fixture(root);panel.files[release.remote('demo','template-parts/pages/course-detail.php')]=b'Unrelated live edit'
            with patch.object(release,'CASE',root),patch.object(release,'connection',return_value=panel):
                with self.assertRaises(RuntimeError):release.deploy('demo',True)
            self.assertEqual([],panel.uploaded)
    def test_unknown_partial_catalogue_bytes_are_not_overwritten(self):
        with tempfile.TemporaryDirectory() as temp:
            root=Path(temp);rows,prior,panel=self.fixture(root);panel.files[release.remote('demo','inc/course-catalog.php')]=b'Unknown partial or human edit'
            with patch.object(release,'CASE',root),patch.object(release,'connection',return_value=panel):
                with self.assertRaises(RuntimeError):release.deploy('demo',True)
            self.assertEqual([],panel.uploaded)
    def test_a_started_publication_worker_cannot_be_replayed_by_this_recovery(self):
        with tempfile.TemporaryDirectory() as temp:
            root=Path(temp);rows,prior,panel=self.fixture(root);prior['cron_submission']='accepted'
            (root/'demo/attempt.json').write_text(json.dumps(prior))
            with patch.object(release,'CASE',root),patch.object(release,'connection') as connection:
                with self.assertRaises(AssertionError):release.deploy('demo',True)
                connection.assert_not_called()
    def test_original_deploy_mode_refuses_existing_attempt(self):
        with tempfile.TemporaryDirectory() as temp:
            root=Path(temp);self.fixture(root)
            with patch.object(release,'CASE',root),patch.object(release,'connection') as connection:
                with self.assertRaises(RuntimeError):release.deploy('demo')
                connection.assert_not_called()

    def activation_fixture(self,root,applied):
        rows,prior,panel=self.fixture(root,True)
        original=root/'demo/attempt.json';out=root/'demo/reconciliation-1';out.mkdir()
        state={'environment':'demo','source':prior['source'],'files':rows,'verified':False,'uploaded':[],
               'cron_removed':None,'parent_attempt_sha256':release.sha(original.read_bytes()),
               'observed':{r['file']:(r['baseline_sha256'] if r['file']=='functions.php' else r['sha256']) for r in rows},
               'remaining_runtime_files':['functions.php'],'upload_intent':'functions.php'}
        (out/'attempt.json').write_text(json.dumps(state))
        if applied:panel.files[release.remote('demo','functions.php')]=(root/'demo/payload/functions.php').read_bytes()
        return rows,state,panel

    def test_activation_uncertainty_reads_first_and_never_reuploads_completed_files(self):
        for applied in [False,True]:
            with self.subTest(activation_applied=applied),tempfile.TemporaryDirectory() as temp:
                root=Path(temp);rows,prior,panel=self.activation_fixture(root,applied)
                original=(root/'demo/reconciliation-1/attempt.json').read_bytes()
                with patch.object(release,'CASE',root),patch.object(release,'connection',return_value=panel):
                    with self.assertRaises(ReachedCron):release.deploy('demo',True,True)
                self.assertEqual([] if applied else [release.remote('demo','functions.php')],panel.uploaded)
                self.assertEqual(original,(root/'demo/reconciliation-1/attempt.json').read_bytes())
                observed=json.loads((root/'demo/reconciliation-2/attempt.json').read_text())['observed']
                self.assertEqual('functions.php',next(iter(observed)))

    def test_activation_recovery_refuses_unknown_bytes_or_started_job(self):
        for unsafe in ['functions.php','inc/course-catalog.php','worker']:
            with self.subTest(unsafe=unsafe),tempfile.TemporaryDirectory() as temp:
                root=Path(temp);rows,prior,panel=self.activation_fixture(root,True)
                if unsafe=='worker':
                    prior['cron_submission']='intent';(root/'demo/reconciliation-1/attempt.json').write_text(json.dumps(prior))
                else:panel.files[release.remote('demo',unsafe)]=b'Unreviewed edit'
                with patch.object(release,'CASE',root),patch.object(release,'connection',return_value=panel) as connect:
                    with self.assertRaises((RuntimeError,AssertionError)):release.deploy('demo',True,True)
                    if unsafe=='worker':connect.assert_not_called()
                self.assertEqual([],panel.uploaded)

    def test_activation_recovery_refuses_tampered_parent_chain(self):
        with tempfile.TemporaryDirectory() as temp:
            root=Path(temp);rows,prior,panel=self.activation_fixture(root,True)
            prior['parent_attempt_sha256']='0'*64;(root/'demo/reconciliation-1/attempt.json').write_text(json.dumps(prior))
            with patch.object(release,'CASE',root),patch.object(release,'connection') as connect:
                with self.assertRaises(AssertionError):release.deploy('demo',True,True)
                connect.assert_not_called()

    def completed_fixture(self,root):
        rows,prior,panel=self.activation_fixture(root,True)
        out=root/'demo/reconciliation-2';out.mkdir()
        publication={'seo_release':'passed','post_id':123,'created':False,'url':'https://demo.toolkitafrica.ac.ke/resource/','catalogue_slugs':[]}
        state=dict(prior);state.update(cron_submission='accepted',cron_removed=True,publication=publication,command='fixed synthetic job')
        (out/'attempt.json').write_text(json.dumps(state));(out/'runtime.private.log').write_text(json.dumps(publication)+'\nTOOLKIT_SEO_EXIT=0\n')
        return state,VerificationPanel(panel.files)

    def test_verification_continuation_never_uploads_or_republishes(self):
        with tempfile.TemporaryDirectory() as temp:
            root=Path(temp);prior,panel=self.completed_fixture(root)
            with patch.object(release,'CASE',root),patch.object(release,'connection',return_value=panel),patch.object(release,'verify_public') as verify:
                release.resume_verification('demo');verify.assert_called_once()
            self.assertEqual([],panel.uploaded);self.assertEqual([('Cron','listcron')],panel.calls)
            with patch.object(release,'CASE',root),patch.object(release,'connection') as connect:
                with self.assertRaises(RuntimeError):release.resume_verification('demo')
                connect.assert_not_called()

    def test_verification_continuation_refuses_runtime_drift(self):
        with tempfile.TemporaryDirectory() as temp:
            root=Path(temp);prior,panel=self.completed_fixture(root);panel.files[release.remote('demo','functions.php')]=b'unknown live edit'
            with patch.object(release,'CASE',root),patch.object(release,'connection',return_value=panel),patch.object(release,'verify_public') as verify:
                with self.assertRaises(AssertionError):release.resume_verification('demo')
                verify.assert_not_called()
            self.assertEqual([],panel.uploaded)

    def test_verification_continuation_requires_successful_removed_job(self):
        for unsafe in ['cron','receipt']:
            with self.subTest(unsafe=unsafe),tempfile.TemporaryDirectory() as temp:
                root=Path(temp);prior,panel=self.completed_fixture(root);out=root/'demo/reconciliation-2'
                if unsafe=='cron':
                    prior['cron_removed']=False;(out/'attempt.json').write_text(json.dumps(prior))
                else:(out/'runtime.private.log').write_text('TOOLKIT_SEO_EXIT=1\n')
                with patch.object(release,'CASE',root),patch.object(release,'connection') as connect:
                    with self.assertRaises(AssertionError):release.resume_verification('demo')
                    connect.assert_not_called()

    def test_public_verification_stops_on_first_transport_failure(self):
        with tempfile.TemporaryDirectory() as temp:
            root=Path(temp);prior,panel=self.completed_fixture(root);out=root/'demo/reconciliation-2'
            with patch.object(release.time,'sleep'),patch.object(release.requests,'get',side_effect=release.requests.Timeout) as get:
                with self.assertRaisesRegex(RuntimeError,'timed out; stopped'):release.verify_public('demo',out,prior,root/'demo')
                get.assert_called_once()
            self.assertFalse((root/'demo/verification.json').exists())
            self.assertFalse((out/'verification.json').exists())

if __name__=='__main__':unittest.main()
