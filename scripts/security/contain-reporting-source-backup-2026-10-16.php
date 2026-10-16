<?php
if(PHP_SAPI!=='cli')exit(1);umask(0077);
$out='/home/bfyigiln/reception-system/storage/app/private/platform-security-2026-10-16';$source='/home/bfyigiln/reports.toolkitafrica.ac.ke/app.py.bak-pre-security-20260818';$dest=$out.'/removed-public-source/app.py.bak-pre-security-20260818';
try {
 if(!is_dir(dirname($dest)))mkdir(dirname($dest),0700,true);
 if(is_file($source)){$hash=hash_file('sha256',$source);$size=filesize($source);if(is_file($dest))throw new RuntimeException('Destination exists');if(!rename($source,$dest)||hash_file('sha256',$dest)!==$hash)throw new RuntimeException('Move failed');file_put_contents($out.'/report-source-containment.json',json_encode(['utc'=>gmdate('c'),'source'=>'app.py.bak-pre-security-20260818','bytes'=>$size,'sha256'=>$hash,'public_exists'=>file_exists($source),'preserved_privately'=>true],JSON_PRETTY_PRINT));}
 if(file_exists($source)||!is_file($dest))throw new RuntimeException('Containment state invalid');echo 'TOOLKIT_AUDIT_OK'.PHP_EOL;
}catch(Throwable $e){fwrite(STDERR,'TOOLKIT_AUDIT_FAILED '.get_class($e).PHP_EOL);exit(1);}
