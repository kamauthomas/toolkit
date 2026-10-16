<?php
if (PHP_SAPI !== 'cli') exit(1);
umask(0077);
$out='/home/bfyigiln/reception-system/storage/app/private/asset-incident-2026-10-16';
$mode=$argv[1]??'';
try {
 if ($mode==='collect') {
  $rows=[];
  foreach (['toolkitafrica.ac.ke-Oct-2026.gz','toolkitafrica.ac.ke-ssl_log-Oct-2026.gz'] as $name) {
   $from=$out.'/archive-'.$name;$to=$out.'/expanded-'.$name.'.txt';
   if (!is_file($to)) {
    $in=gzopen($from,'rb');if(!$in)throw new RuntimeException('Archive read failed');$dest=fopen($to,'xb');$n=0;
    while(!gzeof($in)) { $s=gzread($in,65536);if($s===false)throw new RuntimeException('gzip read failed');$n+=strlen($s);if($n>40000000)throw new RuntimeException('Archive bound exceeded');fwrite($dest,$s); }
    gzclose($in);fclose($dest);
   }
   $chunks=[];
   if(filesize($to)>1000000) {
    $input=fopen($to,'rb');$index=0;
    while(!feof($input)) {
     $bytes=fread($input,800000);if($bytes==='')break;
     $chunk=$to.'.part-'.str_pad((string)$index++,2,'0',STR_PAD_LEFT);file_put_contents($chunk,$bytes);$chunks[]=['name'=>basename($chunk),'bytes'=>strlen($bytes),'sha256'=>hash('sha256',$bytes)];
    }
    fclose($input);
   }
   $rows[]=['name'=>basename($to),'bytes'=>filesize($to),'sha256'=>hash_file('sha256',$to),'chunks'=>$chunks];
  }
  $suspect='/home/bfyigiln/public_html/lib/terminal-xhr.php';
  $check=['path'=>'lib/terminal-xhr.php','exists'=>file_exists($suspect),'symlink'=>is_link($suspect)];
  if(is_file($suspect)) { $check['sha256']=hash_file('sha256',$suspect);$check['size']=filesize($suspect);copy($suspect,$out.'/terminal-xhr-evidence.txt'); }
  file_put_contents($out.'/followup-collection.json',json_encode(['utc'=>gmdate('c'),'archives'=>$rows,'log_request_file'=>$check],JSON_PRETTY_PRINT));
 } elseif(in_array($mode,['contain-demo','contain-production'],true)) {
  $env=substr($mode,8);$root=$env==='demo'?'/home/bfyigiln/demo.toolkitafrica.ac.ke':'/home/bfyigiln/public_html';
  if($env==='production') { $demo=json_decode(file_get_contents($out.'/backup-containment-demo.json'),true);if(!is_array($demo)||($demo['public_source_exists']??true)!==false)throw new RuntimeException('Verified demo containment required'); }
  $from=$root.'/wp-content/uploads/toolkit-deploy-backups';$to=$out.'/quarantine/'.$env.'/toolkit-deploy-backups';
  $manifest=$out.'/backup-containment-'.$env.'.json';
  if(is_file($manifest)) {echo 'ASSET_FOLLOWUP_OK already recorded'.PHP_EOL;exit;}
  if(is_link($from))throw new RuntimeException('Unexpected source symlink');
  if(is_dir($from)) {
   if(file_exists($to))throw new RuntimeException('Destination already exists');
   $before=[];
   foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from,FilesystemIterator::SKIP_DOTS)) as $f) {
    if($f->isLink()||!$f->isFile())throw new RuntimeException('Unexpected backup entry');
    if(count($before)>500||$f->getSize()>50000000)throw new RuntimeException('Backup bound exceeded');
    $before[substr($f->getPathname(),strlen($from)+1)]=['sha256'=>hash_file('sha256',$f->getPathname()),'bytes'=>$f->getSize()];
   }
   if($env==='production') {
    $snapshot=json_decode(file_get_contents($out.'/filesystem.json'),true);
    foreach($snapshot['upload_executables'] as $row) {
     $relative=substr($row['path'],strlen('wp-content/uploads/toolkit-deploy-backups/'));
     if(!isset($before[$relative])||$before[$relative]['sha256']!==$row['sha256'])throw new RuntimeException('Backup evidence changed');
    }
   }
   if(!is_dir(dirname($to)))mkdir(dirname($to),0700,true);
   if(!rename($from,$to))throw new RuntimeException('Private move failed');chmod($to,0700);
   foreach($before as $rel=>$row)if(hash_file('sha256',$to.'/'.$rel)!==$row['sha256'])throw new RuntimeException('Moved backup integrity failed');
   if(file_exists($from))throw new RuntimeException('Public source remains');
   file_put_contents($manifest,json_encode(['utc'=>gmdate('c'),'environment'=>$env,'moved'=>true,'public_source_exists'=>false,'private_destination'=>$to,'files'=>$before],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
  } else {
   file_put_contents($manifest,json_encode(['utc'=>gmdate('c'),'environment'=>$env,'moved'=>false,'public_source_exists'=>false,'reason'=>'Directory absent'],JSON_PRETTY_PRINT));
  }
 } else throw new RuntimeException('Invalid mode');
 echo 'ASSET_FOLLOWUP_OK'.PHP_EOL;
} catch(Throwable $e) {fwrite(STDERR,'ASSET_FOLLOWUP_FAILED: '.$e->getMessage().PHP_EOL);exit(1);}
