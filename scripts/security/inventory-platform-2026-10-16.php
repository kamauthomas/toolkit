<?php
// Incident inventory only; do not load application code or read document bodies.
if(PHP_SAPI!=='cli')exit(1);
umask(0077);$home='/home/bfyigiln';$out=$home.'/reception-system/storage/app/private/platform-security-2026-10-16';if(!is_dir($out))mkdir($out,0700,true);
function saveInventory($name,$data){global $out;file_put_contents($out.'/'.$name,json_encode($data,JSON_PRETTY_PRINT|JSON_INVALID_UTF8_SUBSTITUTE|JSON_UNESCAPED_SLASHES)."\n");}
try {
 $start=time();$roots=['public_html','demo.toolkitafrica.ac.ke','reports.toolkitafrica.ac.ke','reception-public','reception-public-demo','reception-system','reception-system-demo','virtual-campus-test','toolkitafrica.ac.ke','domains','wordpress-backups','private_backups','directadmin_backups','softaculous_backups','toolkit-private-storage','toolkit-audit-reporting-20261005'];
 $entries=0;$candidates=[];$archives=[];$symlinks=[];$counts=[];$limit=false;
 foreach(glob($home.'/*') as $p)if(is_file($p)&&preg_match('/\.(zip|tar|tar\.gz|tgz|tar\.bz2|7z)$/i',$p))$archives[]=['path'=>$p,'bytes'=>filesize($p),'public'=>false,'mtime_utc'=>gmdate('c',filemtime($p))];
 foreach($roots as $name){$base=$home.'/'.$name;if(!is_dir($base))continue;$count=0;$bytes=0;
  $public=in_array($name,['public_html','demo.toolkitafrica.ac.ke','reports.toolkitafrica.ac.ke','reception-public','reception-public-demo','toolkitafrica.ac.ke','domains'],true);
  $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST,RecursiveIteratorIterator::CATCH_GET_CHILD);
  foreach($it as $f){if(++$entries>400000||time()-$start>150){$limit=true;break 2;}$p=$f->getPathname();$rel=substr($p,strlen($home)+1);if($f->isLink()){if(count($symlinks)<250)$symlinks[]=['path'=>$rel,'target'=>readlink($p)];continue;}if(!$f->isFile())continue;$count++;$bytes+=$f->getSize();$file=$f->getFilename();
   if(preg_match('/\.(zip|tar|tar\.gz|tgz|tar\.bz2|7z)$/i',$file)&&count($archives)<400)$archives[]=['path'=>$p,'bytes'=>$f->getSize(),'public'=>$public,'mtime_utc'=>gmdate('c',$f->getMTime())];
   $category=null;
   if(preg_match('/^(?:README|CHANGELOG|CHANGES|CONTRIBUTING|AGENTS|CLAUDE|WORKLOG|TODO|DEPLOYMENT|SECURITY|HARDENING)(?:[._-].*)?$/i',$file)||preg_match('/\.(md|docx?|xlsx?|pptx?|sql|bak|patch)$/i',$file))$category='documentation-or-backup';
   if(preg_match('~/(?:\.git|docs|reports|rollbacks|\.toolkit-deploy|\.github|tests)/~',$p))$category='development-directory';
   if(preg_match('/^(?:\.env(?:\..+)?|docker-compose.*|Dockerfile|composer\.json|package\.json|phpunit.*|error_log|debug\.log)$/i',$file))$category='configuration-or-log';
   if($category&&count($candidates)<12000)$candidates[]=['path'=>$rel,'bytes'=>$f->getSize(),'public_root'=>$public,'category'=>$category,'sha256'=>$f->getSize()<4000000?hash_file('sha256',$p):null];
  }
  $counts[$name]=['files'=>$count,'bytes'=>$bytes];
 }
 saveInventory('server-files.json',['utc'=>gmdate('c'),'entries'=>$entries,'elapsed_seconds'=>time()-$start,'limit_reached'=>$limit,'roots'=>$counts,'candidates'=>$candidates,'candidate_limit'=>count($candidates)>=12000,'archives'=>$archives,'symlinks'=>$symlinks]);
 $config=file_get_contents($home.'/public_html/wp-config.php');$constants=[];preg_match_all('~define\s*\(\s*([\'"])(DB_NAME|DB_USER|DB_PASSWORD|DB_HOST)\1\s*,\s*([\'"])((?:\\\\.|(?!\3).)*)\3\s*\)~s',$config,$matches,PREG_SET_ORDER);
 foreach($matches as $m)$constants[$m[2]]=$m[3]==="'"?str_replace(["\\'","\\\\"],["'","\\"],$m[4]):stripcslashes($m[4]);
 if(count($constants)!==4||!preg_match('/\$table_prefix\s*=\s*([\'"])([A-Za-z0-9_]+)\1/',$config,$prefix))throw new RuntimeException('Configuration parse failed');
 mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);$db=new mysqli($constants['DB_HOST'],$constants['DB_USER'],$constants['DB_PASSWORD'],$constants['DB_NAME']);unset($config,$constants);$db->begin_transaction(MYSQLI_TRANS_START_READ_ONLY);$options=[];$prefix=$prefix[2];
 foreach($db->query("SELECT option_name,option_value FROM {$prefix}options WHERE option_name IN ('_site_transient_update_themes','thim_core_product_registration_themes','thim_core_check_update_themes')") as $row)$options[$row['option_name']]=unserialize($row['option_value'],['allowed_classes'=>['stdClass']]);$db->rollback();$db->close();
 $registration=(array)($options['thim_core_product_registration_themes']??[]);$license=[];foreach($registration as $theme=>$data){if(!is_array($data))continue;$license[$theme]=array_map(fn($k)=>!empty($data[$k]),array_combine(['purchase_code','purchase_token','site_key','envato_personal_token'],['purchase_code','purchase_token','site_key','envato_personal_token']));}
 $update=(array)($options['_site_transient_update_themes']??[]);$response=(array)($update['response']??[]);$eduma=(array)($response['eduma']??[]);$package=$eduma['package']??'';
 saveInventory('theme-update-status.json',['utc'=>gmdate('c'),'license_fields_present'=>$license,'update_cached'=>(bool)$eduma,'new_version'=>$eduma['new_version']??null,'package_available'=>(bool)$package,'package_host'=>$package?parse_url($package,PHP_URL_HOST):null,'cached_thim_theme_keys'=>array_keys((array)($options['thim_core_check_update_themes']??[]))]);
 if($package)saveInventory('theme-update-source.private.json',['package'=>$package,'new_version'=>$eduma['new_version']??null]);
 echo 'TOOLKIT_AUDIT_OK'.PHP_EOL;
}catch(Throwable $e){fwrite(STDERR,'TOOLKIT_AUDIT_FAILED '.get_class($e).PHP_EOL);exit(1);}
