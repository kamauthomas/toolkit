#!/usr/bin/env python3
"""Offline parser and stop-on-first-failure regression checks; no external requests."""
import importlib.util
import json
import tempfile
import types
import unittest
from pathlib import Path
from unittest.mock import patch
import requests

ROOT=Path(__file__).resolve().parents[2]
spec=importlib.util.spec_from_file_location('seo_review',ROOT/'scripts/seo-review-2026-10-27.py')
audit=importlib.util.module_from_spec(spec);spec.loader.exec_module(audit)
URL='https://toolkitafrica.ac.ke/'
HTML='''<html><head><title>Toolkit test</title><meta content="Description" name="description"><meta content="index,follow" name="robots"><link href="https://toolkitafrica.ac.ke/" rel="canonical"><meta content="https://toolkitafrica.ac.ke/image.webp" property="og:image"><script type="application/ld+json">{"@type":"WebPage"}</script></head><body><main><h1>Toolkit</h1><p>Public course content.</p></main></body></html>'''
def response(html=HTML,status=200,headers=None):
    return types.SimpleNamespace(text=html,status_code=status,headers=headers or {},content=html.encode(),elapsed=types.SimpleNamespace(total_seconds=lambda:0.1))

class ReviewTests(unittest.TestCase):
    def test_property_image_is_detected_regardless_of_attribute_order(self):
        result=audit.inspect_html(URL,response());self.assertEqual([],result['issues']);self.assertTrue(result['og_image'])
    def test_missing_social_image_is_actually_missing(self):
        self.assertIn('missing_og_image',audit.inspect_html(URL,response(HTML.replace('property="og:image"','property="og:other"')))['issues'])
    def test_missing_canonical_is_flagged_for_public_page(self):
        self.assertIn('canonical_count',audit.inspect_html(URL,response(HTML.replace('rel="canonical"','rel="other"')))['issues'])
    def test_intentionally_noindexed_utility_does_not_need_marketing_metadata(self):
        html='<html><head><title>Reset password</title><meta name="robots" content="noindex,follow"></head><body>Account utility</body></html>'
        self.assertEqual([],audit.inspect_html(URL,response(html),True)['issues'])
    def test_demo_must_remain_noindexed(self):
        self.assertIn('unexpected_index_policy',audit.inspect_html(URL,response(),True)['issues'])
    def test_schema_parse_failures_are_reported(self):
        self.assertIn('invalid_schema',audit.inspect_html(URL,response(HTML.replace('{"@type":"WebPage"}','not-json')))['issues'])
    def test_non_self_canonical_is_reported(self):
        self.assertIn('canonical_not_self',audit.inspect_html(URL+'/other',response())['issues'])
    def test_transport_waf_rate_limit_and_server_failure_stop_without_retry(self):
        for result in [response(status=401),response(status=403),response(status=429),response(status=503),requests.Timeout('synthetic')]:
            with self.subTest(result=type(result).__name__),tempfile.TemporaryDirectory() as temp:
                original_state=audit.state;original_out=audit.OUT;original_file=audit.STATE
                try:
                    audit.OUT=Path(temp);audit.STATE=Path(temp)/'audit.json';audit.state={'requests':[],'stopped':False}
                    options={'side_effect':result} if isinstance(result,Exception) else {'return_value':result}
                    with patch.object(requests,'get',**options) as get:
                        with self.assertRaises(RuntimeError):audit.fetch(URL,'html')
                        with self.assertRaises(RuntimeError):audit.fetch(URL,'html')
                        self.assertEqual(1,get.call_count);self.assertTrue(json.loads(audit.STATE.read_text())['stopped'])
                finally:audit.state=original_state;audit.OUT=original_out;audit.STATE=original_file
    def test_unapproved_target_is_rejected_before_network(self):
        with patch.object(requests,'get') as get:
            with self.assertRaises(AssertionError):audit.fetch('https://unapproved.example/','html')
            get.assert_not_called()

if __name__=='__main__':unittest.main()
