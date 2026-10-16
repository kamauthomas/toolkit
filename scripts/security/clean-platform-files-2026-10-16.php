<?php
if(PHP_SAPI!=='cli')exit(1);umask(0077);
$home='/home/bfyigiln';$out=$home.'/reception-system/storage/app/private/platform-security-2026-10-16';$env=$argv[1]??'';
try{
 if(!in_array($env,['demo','production'],true))throw new RuntimeException('Invalid environment');
 if($env==='production'&&!is_file($out.'/cleanup-demo.json'))throw new RuntimeException('Demo cleanup required');
 $manifest=$out.'/cleanup-'.$env.'.json';if(is_file($manifest)){echo 'TOOLKIT_AUDIT_OK already recorded'.PHP_EOL;exit;}
 $plan=json_decode(file_get_contents($out.'/cleanup-plan.json'),true);$moved=[];
 foreach($plan['files'] as $row){$rel=$row['path'];
  $demo=str_starts_with($rel,'demo.toolkitafrica.ac.ke/')||str_starts_with($rel,'reception-public-demo/')||str_starts_with($rel,'reception-system-demo/')||str_starts_with($rel,'virtual-campus-test/');
  if(($env==='demo')!==$demo)continue;
  if(!preg_match('~^(?:public_html|demo\.toolkitafrica\.ac\.ke|reception-public(?:-demo)?|reception-system(?:-demo)?|virtual-campus-test)/~',$rel)||str_contains($rel,'..')||str_contains($rel,'/storage/app/'))throw new RuntimeException('Unsafe cleanup plan');
  $from=$home.'/'.$rel;$to=$out.'/removed-files/'.$env.'/'.$rel;
  if(!is_file($from))continue;if(is_link($from)||file_exists($to))throw new RuntimeException('Unexpected cleanup file state');
  $hash=hash_file('sha256',$from);$size=filesize($from);
  if(!empty($row['sha256'])&&$hash!==$row['sha256']&&basename($rel)!=='error_log')throw new RuntimeException('Cleanup baseline changed');
  if(!is_dir(dirname($to)))mkdir(dirname($to),0700,true);
  if(!rename($from,$to)||hash_file('sha256',$to)!==$hash||file_exists($from))throw new RuntimeException('Private move verification failed');
  $moved[]=['path'=>$rel,'bytes'=>$size,'sha256'=>$hash];
 }
 file_put_contents($manifest,json_encode(['utc'=>gmdate('c'),'environment'=>$env,'moved_files'=>$moved,'total_files'=>count($moved),'total_bytes'=>array_sum(array_column($moved,'bytes')),'deleted_business_records'=>0],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));echo 'TOOLKIT_AUDIT_OK'.PHP_EOL;
}catch(Throwable $e){fwrite(STDERR,'TOOLKIT_AUDIT_FAILED '.$e->getMessage().PHP_EOL);exit(1);}
