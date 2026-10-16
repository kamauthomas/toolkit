<?php
// CLI-only, read-only collection. Do not bootstrap WordPress or load plugins.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = '/home/bfyigiln/public_html';
$out = '/home/bfyigiln/reception-system/storage/app/private/asset-incident-2026-10-16';
if (!is_dir($out)) mkdir($out,0700,true);
umask(0077);
if (is_file($out.'/summary.json')) { echo 'ASSET_FORENSICS_OK already collected'.PHP_EOL; exit; }
function saveEvidence($name,$data) { global $out; file_put_contents($out.'/'.$name,json_encode($data,JSON_PRETTY_PRINT|JSON_INVALID_UTF8_SUBSTITUTE|JSON_UNESCAPED_SLASHES)."\n"); }
function describeFile($path,$root) {
 $s=lstat($path);return ['path'=>substr($path,strlen($root)+1),'size'=>$s['size'],'mtime_utc'=>gmdate('c',$s['mtime']),'ctime_utc'=>gmdate('c',$s['ctime']),'mode'=>sprintf('%04o',$s['mode']&07777),'uid'=>$s['uid'],'gid'=>$s['gid'],'symlink'=>is_link($path)];
}
try {
 $started=time();$since=strtotime('2026-09-16T00:00:00Z');$metadata=[];$recent=[];$executables=[];$world=[];$indicators=[];$targets=[];$scanned=0;$limit=false;
 $known=['emer-run.php','w2.js','wp-smart-thumbnails'];
 foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::SELF_FIRST,RecursiveIteratorIterator::CATCH_GET_CHILD) as $file) {
  if (++$scanned>120000 || time()-$started>75) {$limit=true;break;}
  $path=$file->getPathname();$relative=substr($path,strlen($root)+1);
  if(is_link($path))continue;
  if(in_array($file->getFilename(),$known,true))$indicators[]=describeFile($path,$root);
  if($file->isDir()) {if(($file->getPerms()&0002)&&count($world)<300)$world[]=describeFile($path,$root);continue;}
  $isImage=preg_match('/\.(jpe?g|png|webp|avif|gif|svg)$/i',$path);
  if($isImage && (str_starts_with($relative,'wp-content/uploads/')||str_starts_with($relative,'wp-content/themes/eduma-child/assets/images/'))) {
   $row=describeFile($path,$root);
   if($file->getMTime()>=$since && count($metadata)<1800) {if($file->getSize()<20000000)$row['sha256']=hash_file('sha256',$path);$metadata[]=$row;}
   if(in_array($file->getFilename(),['toolkit-logo.png','kmm-2143-jpg.webp','graduands-2026-qr.png','about.jpg','digital.jpg'],true)||str_contains($relative,'/team/')) {$row['sha256']=hash_file('sha256',$path);$targets[]=$row;}
  }
  if(($file->getPerms()&0002)&&count($world)<300)$world[]=describeFile($path,$root);
  if(preg_match('/\.(php[0-9]?|phtml|phar)$/i',$path)) {
   if(str_starts_with($relative,'wp-content/uploads/')&&$file->getFilename()!=='index.php') {
    $row=describeFile($path,$root);$row['sha256']=hash_file('sha256',$path);$executables[]=$row;
    if($file->getSize()<1000000) { $dest=$out.'/executable-evidence/'.hash('sha256',$relative).'.txt';if(!is_dir(dirname($dest)))mkdir(dirname($dest),0700,true);copy($path,$dest); }
   }
   if($file->getMTime()>=$since && count($recent)<1500) {$row=describeFile($path,$root);$row['sha256']=hash_file('sha256',$path);$recent[]=$row;}
  }
 }
 saveEvidence('filesystem.json',['utc'=>gmdate('c'),'scanned_entries'=>$scanned,'limit_reached'=>$limit,'recent_images'=>$metadata,'target_images'=>$targets,'recent_php'=>$recent,'upload_executables'=>$executables,'world_writable'=>$world,'known_indicators'=>$indicators]);
 $pluginInventory=[];$pluginRoot=$root.'/wp-content/plugins';
 foreach(glob($pluginRoot.'/*') as $dir) {
  $candidates=is_dir($dir)?glob($dir.'/*.php'):[$dir];
  foreach($candidates as $path) {
   $head=file_get_contents($path,false,null,0,16000);
   if(preg_match('/^[ \t*#]*Plugin Name:\s*(.+)$/mi',$head,$name)) {
    preg_match('/^[ \t*#]*Version:\s*(.+)$/mi',$head,$version);$pluginInventory[]=['file'=>substr($path,strlen($pluginRoot)+1),'name'=>trim($name[1]),'version'=>trim($version[1]??''),'sha256'=>hash_file('sha256',$path)];
   }
  }
 }
 saveEvidence('plugins.json',$pluginInventory);
 $themes=[];foreach(glob($root.'/wp-content/themes/*/style.css') as $path) { $head=file_get_contents($path,false,null,0,16000);preg_match('/Theme Name:\s*(.+)/i',$head,$name);preg_match('/Version:\s*(.+)/i',$head,$v);$themes[]=['directory'=>basename(dirname($path)),'name'=>trim($name[1]??''),'version'=>trim($v[1]??'')]; }saveEvidence('themes.json',$themes);
 $config=file_get_contents($root.'/wp-config.php');$constants=[];
 preg_match_all('~define\s*\(\s*([\'"])(DB_NAME|DB_USER|DB_PASSWORD|DB_HOST)\1\s*,\s*([\'"])((?:\\\\.|(?!\3).)*)\3\s*\)~s',$config,$matches,PREG_SET_ORDER);
 foreach($matches as $m)$constants[$m[2]]=$m[3]==="'"?str_replace(["\\'","\\\\"],["'","\\"],$m[4]):stripcslashes($m[4]);
 if(count($constants)!==4 || !preg_match('/\$table_prefix\s*=\s*([\'"])([A-Za-z0-9_]+)\1/',$config,$prefix))throw new RuntimeException('Database configuration cannot be parsed safely');
 mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
 $db=new mysqli($constants['DB_HOST'],$constants['DB_USER'],$constants['DB_PASSWORD'],$constants['DB_NAME']);unset($constants,$config);$db->set_charset('utf8mb4');$db->begin_transaction(MYSQLI_TRANS_START_READ_ONLY);$prefix=$prefix[2];
 $options=[];$result=$db->query("SELECT option_name,option_value FROM {$prefix}options WHERE option_name IN ('active_plugins','users_can_register','default_role','template','stylesheet','upload_path','upload_url_path')");
 foreach($result as $row)$options[$row['option_name']]=$row['option_name']==='active_plugins'?unserialize($row['option_value'],['allowed_classes'=>false]):$row['option_value'];
 saveEvidence('options.json',$options);
 $users=$db->query("SELECT ID,user_login,user_registered,user_status FROM {$prefix}users ORDER BY ID")->fetch_all(MYSQLI_ASSOC);
 $caps=[];foreach($db->query("SELECT user_id,meta_value FROM {$prefix}usermeta WHERE meta_key='{$prefix}capabilities'") as $row)$caps[$row['user_id']]=unserialize($row['meta_value'],['allowed_classes'=>false]);
 foreach($users as &$user)$user['roles']=array_keys(array_filter($caps[$user['ID']]??[]));unset($user);saveEvidence('users.json',$users);
 $media=$db->query("SELECT ID,post_author,post_date_gmt,post_modified_gmt,post_mime_type FROM {$prefix}posts WHERE post_type='attachment' ORDER BY post_modified_gmt DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);saveEvidence('recent-media.json',$media);
 $db->rollback();$db->close();
 $logFiles=[];
 foreach(glob('/home/bfyigiln/access-logs/*') as $path) {
  if(!str_contains(basename($path),'toolkitafrica.ac.ke')||!is_readable($path)||is_dir($path))continue;
  $size=filesize($path);$handle=fopen($path,'rb');$start=max(0,$size-12000000);fseek($handle,$start);if($start)fgets($handle);
  $dest=$out.'/access-'.preg_replace('/[^A-Za-z0-9._-]/','_',basename($path)).'.tail.log';$copy=fopen($dest,'wb');stream_copy_to_stream($handle,$copy,12000000);fclose($handle);fclose($copy);
  $logFiles[]=['name'=>basename($path),'source_size'=>$size,'tail_start_offset'=>$start,'collected_bytes'=>filesize($dest),'sha256'=>hash_file('sha256',$dest)];
 }
 $archives=array_filter(glob('/home/bfyigiln/logs/*')?:[],fn($p)=>str_contains(basename($p),'toolkitafrica')&&is_file($p));
 usort($archives,fn($a,$b)=>filemtime($b)<=>filemtime($a));
 foreach(array_slice($archives,0,6) as $path) { if(filesize($path)>50000000)continue;$dest=$out.'/archive-'.basename($path);copy($path,$dest);$logFiles[]=['name'=>basename($path),'source_size'=>filesize($path),'collected_bytes'=>filesize($dest),'sha256'=>hash_file('sha256',$dest)]; }
 saveEvidence('logs.json',$logFiles);saveEvidence('summary.json',['utc'=>gmdate('c'),'elapsed_seconds'=>time()-$started,'scanned_entries'=>$scanned,'limit_reached'=>$limit,'recent_images'=>count($metadata),'target_images'=>count($targets),'recent_php'=>count($recent),'upload_executables'=>count($executables),'world_writable'=>count($world),'known_indicators'=>count($indicators),'plugins'=>count($pluginInventory),'users'=>count($users),'logs'=>count($logFiles),'wordpress_bootstrapped'=>false,'database_transaction'=>'READ ONLY','existing_files_modified'=>0]);
 echo 'ASSET_FORENSICS_OK'.PHP_EOL;
} catch(Throwable $e) {saveEvidence('collection-error.json',['type'=>get_class($e),'stage'=>'collection','utc'=>gmdate('c')]);fwrite(STDERR,'ASSET_FORENSICS_FAILED: '.get_class($e).PHP_EOL);exit(1);}
