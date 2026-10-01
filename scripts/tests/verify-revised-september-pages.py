#!/usr/bin/env python3
"""Fetch and check bare and uncached public pricing pages against the poster manifest."""
import argparse
from concurrent.futures import ThreadPoolExecutor, as_completed
from html import unescape
import json
from pathlib import Path
import re
import urllib.request

parser=argparse.ArgumentParser()
parser.add_argument('manifest', type=Path)
parser.add_argument('output', type=Path)
parser.add_argument('--fetch', help='Public base URL; omit to validate saved pages')
args=parser.parse_args()
data=json.loads(args.manifest.read_text())
args.output.mkdir(parents=True,exist_ok=True)
def filename(path,variant):
    return args.output / ((path.strip('/').replace('/','__') or 'home')+'.'+variant+'.html')
def check(path,variant):
    target=filename(path,variant)
    if args.fetch:
        url=args.fetch.rstrip('/')+path+('' if variant=='bare' else '?fee_check=2026.10.01.1')
        with urllib.request.urlopen(urllib.request.Request(url,headers={'User-Agent':'Toolkit pricing release verification'}),timeout=45) as result:
            assert result.status==200, (path,result.status)
            target.write_bytes(result.read())
    html=target.read_text()
    text=re.sub(r'\s+',' ',unescape(re.sub(r'<[^>]*>',' ',html)))
    for expected in data['pages'][path]:
        assert expected in text or expected in html, f'{variant} {path}: missing {expected}'
    for old in data['obsolete']:
        assert old not in text, f'{variant} {path}: obsolete price/source {old}'
    assert not re.search(r'fatal error|critical error|uncaught error',text,re.I), path
    if 'recognition-of-prior-learning-rpl' in path:
        assert not re.search(r'KES\s+[0-9]',text), 'Price leaked onto RPL'
    if path=='/our-ventures/':
        assert html.count('toolkit-course-directory__fee')==15, 'Directory does not display all 15 course fees'
    return path,variant
with ThreadPoolExecutor(max_workers=4) as pool:
    futures=[pool.submit(check,path,variant) for path in data['pages'] for variant in ('bare','fresh')]
    for future in as_completed(futures):
        future.result()
print(f"PASS: {len(data['pages'])} routes x bare/uncached; all current fees, welding categories, separate exams and unpriced RPL verified")
