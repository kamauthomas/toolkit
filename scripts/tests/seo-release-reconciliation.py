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

if __name__=='__main__':unittest.main()
