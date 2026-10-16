<?php
if(PHP_SAPI!=='cli')exit(1);umask(0077);
$out='/home/bfyigiln/reception-system/storage/app/private/platform-security-2026-10-16';
function classifyArchiveName($name,&$counts,&$samples){
 $groups=[];$base=basename($name);
 if(preg_match('~(^|/)(?:\.env(?:\.[^/]*)?|wp-config\.php|id_rsa|id_ed25519|config\.toml)$~i',$name))$groups[]='configuration_or_secret_candidate';
 if(preg_match('/\.(sql|sqlite3?|db)$/i',$name))$groups[]='database';
 if(preg_match('~(?:^|/)(?:README[^/]*|WORKLOG[^/]*|DEPLOYMENT[^/]*|AGENTS[^/]*|CLAUDE[^/]*|docs|\.git)(?:/|$)|\.(md|docx?|xlsx?|pptx?)$~i',$name))$groups[]='documentation_or_development';
 if(preg_match('/\.(php|py|js|sh)$/i',$name))$groups[]='executable_or_source';
 if(preg_match('~^/|^[A-Za-z]:|(?:^|/)\.\.(?:/|$)~',$name))$groups[]='unsafe_entry_path';
 foreach($groups as $g){$counts[$g]=($counts[$g]??0)+1;if(count($samples[$g]??[])<8)$samples[$g][]=$name;}
}
try{
 $inventory=json_decode(file_get_contents($out.'/server-files.json'),true);$results=[];
 foreach($inventory['archives'] as $item){$path=$item['path'];if(($argv[1]??'')==='vendor-only'&&!str_contains($path,'/bdthemes-element-pack-lite/'))continue;
  if(!is_file($path)||is_link($path))continue;$counts=[];$samples=[];$entries=0;$complete=false;$note=null;
  $total=null;$unpacked=null;
  if(preg_match('/\.zip$/i',$path)){
   try {
    $z=new PharData($path);$unpacked=0;
    foreach(new RecursiveIteratorIterator($z) as $key=>$entry){if(++$entries>50000){$note='Entry limit';break;}$name=substr($key,strlen('phar://'.$path.'/'));classifyArchiveName($name,$counts,$samples);$unpacked+=$entry->getSize();}
    $complete=$note===null;
   }catch(Throwable $e){$note='ZIP listing unavailable: '.get_class($e);}
  }else{
   $in=gzopen($path,'rb');$consumed=0;$begin=time();$longName=null;
   if($in){
    while(!gzeof($in)){
     $header=gzread($in,512);$consumed+=strlen($header);if(strlen($header)<512||$header===str_repeat("\0",512)){$complete=true;break;}
     $name=rtrim(substr($header,0,100),"\0");$prefix=rtrim(substr($header,345,155),"\0");if($prefix!=='')$name=$prefix.'/'.$name;
     $size=octdec(trim(substr($header,124,12)," \0"));$type=substr($header,156,1);$padded=(int)(ceil($size/512)*512);
     if($longName!==null){$name=$longName;$longName=null;}
     $entries++;classifyArchiveName($name,$counts,$samples);
     if($padded+$consumed>80000000||$entries>50000||time()-$begin>12){$note='Bounded partial tar listing (80 MB / 12 seconds)';break;}
     if($type==='L'){$longName=rtrim(gzread($in,$padded),"\0\n");$consumed+=$padded;}
     else {while($padded>0){$chunk=gzread($in,min(65536,$padded));if($chunk===false||$chunk==='')break 2;$padded-=strlen($chunk);$consumed+=strlen($chunk);}}
    }
    gzclose($in);
   }else $note='Archive stream unavailable';
  }
  $results[]=['path'=>$path,'archive_bytes'=>filesize($path),'entries_inspected'=>$entries,'complete'=>$complete,'total_entries'=>$total,'uncompressed_bytes'=>$unpacked,'categories'=>$counts,'sample_names'=>$samples,'note'=>$note,'extracted'=>false];
 }
 file_put_contents($out.'/'.((($argv[1]??'')==='vendor-only')?'archive-vendor-contents.json':'archive-contents.json'),json_encode(['utc'=>gmdate('c'),'archives'=>$results,'archive_entries_executed'=>0,'archive_entries_extracted'=>0],JSON_PRETTY_PRINT|JSON_INVALID_UTF8_SUBSTITUTE|JSON_UNESCAPED_SLASHES));echo 'TOOLKIT_AUDIT_OK'.PHP_EOL;
}catch(Throwable $e){fwrite(STDERR,'TOOLKIT_AUDIT_FAILED '.get_class($e).PHP_EOL);exit(1);}
