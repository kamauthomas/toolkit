<?php
require dirname(__DIR__,2).'/wp-content/themes/eduma-child/inc/private-file-boundary.php';
$root=sys_get_temp_dir().'/toolkit-boundary-'.bin2hex(random_bytes(6));mkdir($root);mkdir($root.'/site');mkdir($root.'/other');file_put_contents($root.'/site/1-REF.pdf','approved');file_put_contents($root.'/outside.pdf','private');file_put_contents($root.'/1-OLD.docx','legacy');symlink($root.'/outside.pdf',$root.'/site/evil.pdf');file_put_contents($root.'/other/1-REF.pdf','other-site');$roots=[$root.'/site',$root];$checks=0;
try {
 foreach([$root.'/outside.pdf',$root.'/other/1-REF.pdf',$root.'/site/evil.pdf',$root.'/site/../outside.pdf',"bad\0path",$root.'/site/1-REF.pdf'] as $path){$expected='1-REF.pdf';$result=toolkit_confined_private_file($path,$roots,$expected);if($path===$root.'/site/1-REF.pdf'){if($result!==realpath($path))throw new RuntimeException('Valid file refused');}elseif($result!==false)throw new RuntimeException('Boundary escaped');$checks++;}
 if(toolkit_confined_private_file($root.'/1-OLD.docx',$roots,'1-OLD.docx')!==realpath($root.'/1-OLD.docx'))throw new RuntimeException('Legacy letter refused');$checks++;
 if(toolkit_confined_private_file($root.'/site/1-REF.pdf',$roots,'2-OTHER.pdf')!==false)throw new RuntimeException('Wrong artifact returned');$checks++;
 echo $checks.' private artifact boundary checks passed'.PHP_EOL;
}finally{unlink($root.'/site/evil.pdf');unlink($root.'/site/1-REF.pdf');unlink($root.'/other/1-REF.pdf');unlink($root.'/outside.pdf');unlink($root.'/1-OLD.docx');rmdir($root.'/site');rmdir($root.'/other');rmdir($root);}
