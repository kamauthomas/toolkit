#!/usr/bin/env python3
from pathlib import Path
from xml.etree import ElementTree as E
from babel import Locale
import json,re,hashlib
import argparse
p=argparse.ArgumentParser();p.add_argument('metadata',type=Path);p.add_argument('output',type=Path);args=p.parse_args();args.output.mkdir(parents=True,exist_ok=True)
source=args.metadata;loc=Locale('en');data={}
for t in E.parse(source).getroot().findall('.//territory'):
 region=t.get('id')
 if len(region)!=2:continue
 patterns=[re.sub(r'\s+','',t.findtext(kind+'/nationalNumberPattern') or '') for kind in ['mobile','fixedLine']]
 patterns=list(dict.fromkeys(p for p in patterns if p))
 if not patterns:continue
 prefix=t.get('nationalPrefix','');prefix=prefix if prefix.isdigit() else ''
 example=t.findtext('mobile/exampleNumber') or t.findtext('fixedLine/exampleNumber') or ''
 data[region]={'flag':''.join(chr(0x1F1E6+ord(c)-ord('A')) for c in region),'name':loc.territories.get(region,region),'code':t.get('countryCode'),'prefix':prefix,'pattern':'|'.join('(?:'+p+')' for p in patterns),'example':example,'main':t.get('mainCountryForCode')=='true'}
data=dict(sorted(data.items(),key=lambda i:(i[0]!='KE',i[1]['name'])))
encoded=json.dumps(data,separators=(',',':'),ensure_ascii=False)
header='Generated from Google libphonenumber PhoneNumberMetadata.xml (Apache-2.0), retrieved 5 October 2026. Source SHA256: '+hashlib.sha256(source.read_bytes()).hexdigest()
(args.output/'PhoneCountries.php').write_text('<?php\n// '+header+"\nreturn json_decode(<<<'TOOLKIT_PHONE_COUNTRIES'\n"+encoded+"\nTOOLKIT_PHONE_COUNTRIES\n, true, 512, JSON_THROW_ON_ERROR);\n")
(args.output/'phone-countries.js').write_text('// '+header+'\nwindow.ToolkitPhoneCountries = '+encoded+';\n')
(args.output/'source.json').write_text(json.dumps({'source':'https://github.com/google/libphonenumber/blob/master/resources/PhoneNumberMetadata.xml','source_sha256':hashlib.sha256(source.read_bytes()).hexdigest(),'territories':len(data)},indent=2))
print('Generated',len(data),'countries,',len(encoded),'bytes each; English labels from CLDR/Babel')
