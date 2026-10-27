#!/usr/bin/env python3
"""Small serial public SEO sample; no retries, concurrency, forms or settings changes."""
import hashlib
import json
import time
import xml.etree.ElementTree as ET
from datetime import datetime, timezone
from pathlib import Path
from urllib.parse import urljoin,urlsplit
import requests
from bs4 import BeautifulSoup

ROOT=Path(__file__).resolve().parents[1]
OUT=ROOT/'reports/seo-review-2026-10-27'
STATE=OUT/'public-audit.json'
BASE='https://toolkitafrica.ac.ke'
ALLOWED={'toolkitafrica.ac.ke','demo.toolkitafrica.ac.ke','toolkitiskills.com','www.toolkitiskills.com'}
state={'started_utc':datetime.now(timezone.utc).isoformat(),'method':'serial GET, minimum 5-second spacing, no retries; stop on first transport/WAF/rate/server failure','requests':[],'pages':[],'sitemaps':[],'stopped':False}
def save():STATE.write_text(json.dumps(state,indent=2,ensure_ascii=False)+'\n')
def fetch(url,kind):
    assert urlsplit(url).hostname in ALLOWED and urlsplit(url).scheme=='https'
    if state['stopped']:raise RuntimeError('Requests already stopped')
    if state['requests']:time.sleep(5)
    try:r=requests.get(url,timeout=(8,25),allow_redirects=False,headers={'User-Agent':'ToolkitSEOReview/2026-10-27 (serial read-only)'})
    except requests.RequestException:
        state['stopped']=True;state['stop_reason']='transport_failure';state['requests'].append({'url':url,'kind':kind,'status':0});save();raise RuntimeError('Transport failure: stop live checks') from None
    row={'url':url,'kind':kind,'status':r.status_code,'location':r.headers.get('Location'),'headers_seconds':round(r.elapsed.total_seconds(),3),'html_bytes_decoded':len(r.content),'sha256':hashlib.sha256(r.content).hexdigest(),'x_robots_tag':r.headers.get('X-Robots-Tag'),'content_type':r.headers.get('Content-Type')}
    state['requests'].append(row)
    p=OUT/'public';p.mkdir(exist_ok=True,mode=0o700)
    (p/(hashlib.sha256(url.encode()).hexdigest()[:16]+'.txt')).write_text(r.text)
    text=r.text.lower()
    if r.status_code in {401,403,406,429} or r.status_code>=500 or r.headers.get('Retry-After') or any(s in text for s in ['access denied by cpguard','checking your browser before accessing','web application firewall has blocked']):
        state['stopped']=True;state['stop_reason']='refused_or_server_failure';save();raise RuntimeError('Refusal/server failure: stop live checks')
    save();return r

def inspect_html(url,r,expected_noindex=False):
    s=BeautifulSoup(r.text,'html.parser')
    def meta(key):
        tags=s.find_all('meta',attrs={'name':key}) or s.find_all('meta',attrs={'property':key})
        return [t.get('content','') for t in tags]
    main=s.select_one('main') or s.body or s
    for node in main.find_all(['script','style','nav','footer']):node.decompose()
    # Parse schema separately because main/body text cleaning must not discard head scripts.
    original=BeautifulSoup(r.text,'html.parser');graphs=[];schema_errors=0
    for node in original.select('script[type="application/ld+json"]'):
        try:graphs.append(json.loads(node.string or node.get_text()))
        except (ValueError,TypeError):schema_errors+=1
    nodes=[]
    for graph in graphs:nodes.extend(graph.get('@graph',[graph]) if isinstance(graph,dict) else graph if isinstance(graph,list) else [])
    titles=[t.get_text(' ',strip=True) for t in original.find_all('title')]
    canonical=[t.get('href','') for t in original.find_all('link',rel='canonical')]
    robots=meta('robots');desc=meta('description');h1=[t.get_text(' ',strip=True) for t in main.find_all('h1')]
    result={'url':url,'status':r.status_code,'titles':titles,'descriptions':desc,'canonicals':canonical,'robots':robots,'x_robots_tag':r.headers.get('X-Robots-Tag'),'h1':h1,'json_ld_blocks':len(graphs),'schema_parse_errors':schema_errors,'schema_types':[n.get('@type') for n in nodes if isinstance(n,dict)],'organizations':[{'id':n.get('@id'),'name':n.get('name'),'url':n.get('url'),'sameAs':n.get('sameAs')} for n in nodes if isinstance(n,dict) and 'Organization' in ([n.get('@type')] if isinstance(n.get('@type'),str) else n.get('@type',[]))],'og_title':meta('og:title'),'og_description':meta('og:description'),'og_image':meta('og:image'),'main_text_words':len(main.get_text(' ',strip=True).split()),'main_text':main.get_text(' ',strip=True),'links':[{'href':urljoin(url,t.get('href')),'text':t.get_text(' ',strip=True)} for t in main.find_all('a',href=True)],'images':[{'src':t.get('src'),'alt':t.get('alt'),'width':t.get('width'),'height':t.get('height'),'loading':t.get('loading'),'fetchpriority':t.get('fetchpriority')} for t in main.find_all('img')],'issues':[]}
    if r.status_code==200:
        for condition,label in [(len(titles)!=1,'title_count'),((not desc or len(desc)!=1) and not expected_noindex,'description_count'),(len(canonical)!=1 and not expected_noindex,'canonical_count'),(len(h1)!=1 and not expected_noindex,'h1_count'),(not graphs and not expected_noindex,'missing_schema'),(schema_errors>0,'invalid_schema'),(not meta('og:image') and not expected_noindex,'missing_og_image')]:
            if condition:result['issues'].append(label)
        noindex='noindex' in ','.join(robots+[r.headers.get('X-Robots-Tag','')]).lower()
        if noindex!=expected_noindex:result['issues'].append('unexpected_index_policy')
        if canonical and not expected_noindex and canonical[0].rstrip('/')!=url.rstrip('/'):result['issues'].append('canonical_not_self')
    return result

def page(url,expected_noindex=False):
    r=fetch(url,'html');result=inspect_html(url,r,expected_noindex)
    state['pages'].append(result);save();print('READ',r.status_code,url,'issues:',','.join(result['issues']) or 'none',flush=True)
    return result

def sitemap(url):
    r=fetch(url,'sitemap');assert r.status_code==200
    root=ET.fromstring(r.content);urls=[e.text.strip() for e in root.findall('./{*}url/{*}loc') if e.text];children=[e.text.strip() for e in root.findall('./{*}sitemap/{*}loc') if e.text]
    state['sitemaps'].append({'url':url,'urls':urls,'children':children});save();return children

def run():
    OUT.mkdir(parents=True,exist_ok=True,mode=0o700)
    if STATE.exists():raise RuntimeError('Existing public evidence: inspect it instead of repeating requests')
    save();page(BASE+'/')
    fetch(BASE+'/robots.txt','robots')
    children=sitemap(BASE+'/sitemap_index.xml')
    for url in children:
        if urlsplit(url).hostname=='toolkitafrica.ac.ke':sitemap(url)
    priority=json.loads((ROOT/'reports/seo-priority-urls-2026-09-15.json').read_text())
    for url in priority:
        if url!=BASE+'/':page(url)
    # Sample actual current catalogue links discovered on the directory.
    directory=next(r for r in state['pages'] if r['url']==BASE+'/our-ventures/')
    catalog=[x['href'] for x in directory['links'] if x['href'].startswith(BASE+'/courses/') and 'weld' in x['href']]
    for url in list(dict.fromkeys(catalog))[:2]:page(url)
    for url in [BASE+'/graduation/',BASE+'/instructors/',BASE+'/reset-password/']:
        page(url,expected_noindex=('instructors' in url or 'reset-password' in url))
    page('https://demo.toolkitafrica.ac.ke/',expected_noindex=True)
    # Migration checks last; stop on failure without further origin requests.
    for url in ['https://toolkitiskills.com/','https://www.toolkitiskills.com/us/']:
        page(url)
    state['completed_utc']=datetime.now(timezone.utc).isoformat();save()

if __name__=='__main__':
    try:run()
    except Exception as e:
        print('STOPPED:',str(e) if isinstance(e,RuntimeError) else type(e).__name__,'— saved evidence retained, no retry',flush=True);raise SystemExit(1)
