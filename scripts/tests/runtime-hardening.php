<?php
// Exercise adversarial inputs without loading WordPress or submitting live forms.
define('ABSPATH', __DIR__);
$filters=[];
function add_filter($name,$callback){global $filters;$filters[$name]=$callback;}
$_SERVER['REQUEST_URI']='/about-toolkit-africa/';
require dirname(__DIR__,2).'/wp-content/mu-plugins/toolkit-runtime-hardening.php';
$count=0;
function verifyHardening($condition,$label){global $count;if(!$condition)throw new RuntimeException($label);$count++;}
foreach(['/../wp-config.php','/assets/%2e%2e/wp-config.php','/assets/%252e%252e%252fwp-config.php','/assets/..\\wp-config.php',"/image\0.jpg"] as $name)verifyHardening(toolkit_runtime_has_traversal($name),'Reject traversal');
foreach(['/about-toolkit-africa/','/.well-known/security.txt','/photo..jpg','/course/?name=normal'] as $name)verifyHardening(!toolkit_runtime_has_traversal($name),'Allow ordinary path');
foreach(['shell.php','shell.PHP8.jpg','file.phtml.png','image.phar','logo.svg','page.html','../photo.jpg','..%2fphoto.jpg','.htaccess','photo.php.png','C:\\fakepath\\x.jpg'] as $name)verifyHardening(toolkit_runtime_upload_error($name)!=='','Reject executable/active/path filename');
foreach(['campus.jpg','brochure.pdf','approved form.docx','graduands.csv','photo..jpg'] as $name)verifyHardening(toolkit_runtime_upload_error($name)==='','Allow ordinary filename');
$photo=dirname(__DIR__,2).'/wp-content/themes/eduma-child/assets/images/hero-slide-1.jpg';
$valid=toolkit_runtime_check_upload(['name'=>'campus.jpg','tmp_name'=>$photo,'error'=>0]);verifyHardening($valid['error']===0,'Allow genuine JPEG');
$spoof=toolkit_runtime_check_upload(['name'=>'campus.png','tmp_name'=>$photo,'error'=>0]);verifyHardening(is_string($spoof['error']),'Reject renamed JPEG as PNG');
$code=toolkit_runtime_check_upload(['name'=>'campus.jpg','tmp_name'=>__FILE__,'error'=>0]);verifyHardening(is_string($code['error']),'Reject PHP bytes named JPG');
$mimes=$filters['upload_mimes'](['jpg|jpeg'=>'image/jpeg','svg|svgz'=>'image/svg+xml','pdf'=>'application/pdf','html'=>'text/html']);verifyHardening(isset($mimes['jpg|jpeg'],$mimes['pdf'])&&!isset($mimes['svg|svgz'],$mimes['html']),'Keep safe mimes and remove active formats');
verifyHardening(DISALLOW_FILE_EDIT===true,'Disable browser source editing');
echo $count.' hardening checks passed'.PHP_EOL;
