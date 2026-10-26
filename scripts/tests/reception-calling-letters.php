<?php
/** Isolated real PDF renderer and signed gateway boundaries; no WordPress/network/database. */
class WP_Error {
 public function __construct(public $code,public $message,public $data=[]){}
 public function get_error_message(){return $this->message;}
}
class WP_REST_Response {
 public array $headers=[];public function __construct(public $data,public $status){}
 public function header($key,$value){$this->headers[$key]=$value;}
}
class WP_REST_Request {
 public function __construct(public string $method='GET',public array $url=[],public string $body='',public array $headers=[],public array $query=[]){}
 public function get_method(){return $this->method;}public function get_url_params(){return $this->url;}
 public function get_body(){return $this->body;}public function get_query_params(){return $this->query;}
 public function get_header($key){return $this->headers[$key]??'';}public function get_json_params(){return json_decode($this->body,true);}
}
$enabled=false;$sync=true;$secret=str_repeat('s',40);$nonces=[];$routes=[];$count=0;
if(isset($argv[1],$argv[2]))$asset_archive=$argv[1];
$root=sys_get_temp_dir().'/toolkit-letter-test-'.bin2hex(random_bytes(6));mkdir($root,0700);mkdir($root.'/demo',0700);mkdir($root.'/production',0700);
file_put_contents($root.'/template.docx','synthetic-template-revision');
function get_option($key,$default=false){return $key==='toolkit_reception_calling_letters_enabled'?$GLOBALS['enabled']:$default;}
function toolkit_reception_application_sync_enabled(){return $GLOBALS['sync']&&strlen($GLOBALS['secret'])>=32;}
function toolkit_reception_api_secret(){return $GLOBALS['secret'];}
function toolkit_is_demo_environment(){return true;}
function toolkit_calling_letter_private_dir(){return $GLOBALS['root'];}
function toolkit_calling_letter_template_path(){return $GLOBALS['template_override']??$GLOBALS['root'].'/template.docx';}
function toolkit_calling_letter_output_dir(){return $GLOBALS['root'].'/demo';}
function toolkit_calling_letter_pdf_file_path($id,$ref){return toolkit_calling_letter_output_dir().'/'.$id.'-'.$ref.'.pdf';}
function toolkit_calling_letter_table_name(){return 'fixture_letters';}function toolkit_application_table_name(){return 'fixture_applications';}
function toolkit_calling_letter_ordinal_date(){return '26th October 2026';}
function is_wp_error($value){return $value instanceof WP_Error;}
function add_option($key,$value,...$rest){if(isset($GLOBALS['nonces'][$key]))return false;$GLOBALS['nonces'][$key]=$value;return true;}
function add_action($name,$callback){if($name==='rest_api_init')$callback();}
function register_rest_route($namespace,$route,$options){$GLOBALS['routes'][$route]=$options;}
function toolkit_calling_letter_zip_read($path,$entry){
 if(isset($GLOBALS['asset_archive'])){$zip=new ZipArchive;$zip->open($GLOBALS['asset_archive']);$bytes=$zip->getFromName($entry);$zip->close();return $bytes;}
 $chunk=fn($type,$body)=>pack('N',strlen($body)).$type.$body.pack('N',crc32($type.$body));
 return "\x89PNG\r\n\x1a\n".$chunk('IHDR',pack('NNCCCCC',1,1,8,6,0,0,0)).$chunk('IDAT',gzcompress("\0\x00\x80\x80\xff")).$chunk('IEND','');
}
class FixtureDatabase {
 public $options='fixture_options'; public $row=null;public $arguments=[];
 public function prepare($sql,...$args){$this->arguments=$args;return $sql;}
 public function esc_like($s){return addcslashes($s,'_%\\');}
 public function query($sql){if(!str_starts_with($sql,'DELETE FROM fixture_options'))throw new RuntimeException('Application writes forbidden');return 0;}
 public function get_row($sql){if(!str_contains($sql,'WHERE a.id=%d'))throw new RuntimeException('Unbounded query');return $this->row;}
}
$wpdb=new FixtureDatabase;
require __DIR__.'/../../wp-content/themes/eduma-child/inc/private-file-boundary.php';
require __DIR__.'/../../wp-content/themes/eduma-child/inc/calling-letter-pdf.php';
require __DIR__.'/../../wp-content/themes/eduma-child/inc/reception-calling-letters.php';
function check($ok,$message){if(!$ok)throw new RuntimeException($message);$GLOBALS['count']++;}
function signed($method='GET',$id='31',$data=null){
 $body=$data===null?'':json_encode($data);$t=(string)time();$n=bin2hex(random_bytes(16));$resource=$method==='GET'?'application:'.(int)$id:'render-native';
 $sig=hash_hmac('sha256',$t."\n".$n."\ncalling-letters-v1\n".$method."\n".$resource."\n".hash('sha256',$body),$GLOBALS['secret']);
 return new WP_REST_Request($method,$method==='GET'?['id'=>$id]:[],$body,['x_toolkit_timestamp'=>$t,'x_toolkit_nonce'=>$n,'x_toolkit_signature'=>$sig]);
}
function streams($pdf){preg_match_all('/stream\n(.*?)\nendstream/s',$pdf,$parts);$result='';foreach($parts[1] as $part)$result.=@gzuncompress($part);return $result;}
try {
 check(toolkit_reception_letter_permission(signed()) instanceof WP_Error,'Disabled by default');$enabled=true;
 foreach([false,true] as $value){$sync=$value;check(toolkit_reception_letters_enabled()===$value,'Application sharing also gates letters');}$sync=true;
 check(count($routes)===2,'Only read and stateless render routes');
 check($routes['/reception/applications/(?P<id>[0-9]+)/calling-letter']['methods']==='GET','Website route is GET only');
 $r=signed();check(toolkit_reception_letter_permission($r)===true,'Valid signature');check(toolkit_reception_letter_permission($r) instanceof WP_Error,'Replay rejected');
 foreach(['-1','0','1 OR 1=1',str_repeat('9',16)] as $id)check(toolkit_reception_letter_permission(signed('GET',$id)) instanceof WP_Error,'Invalid ID rejected');
 foreach(['x_toolkit_timestamp'=>'1','x_toolkit_signature'=>str_repeat('0',64),'x_toolkit_nonce'=>'bad'] as $key=>$value){$r=signed();$r->headers[$key]=$value;check(toolkit_reception_letter_permission($r) instanceof WP_Error,'Malformed auth rejected');}
 $r=signed();$r->url['id']='32';check(toolkit_reception_letter_permission($r) instanceof WP_Error,'Signature bound to application');
 $r=signed();$r->query=['path'=>'/etc/passwd'];check(toolkit_reception_letter_permission($r) instanceof WP_Error,'No arbitrary query paths');
 $r=signed('POST',null,['name'=>'original']);$r->body='{"name":"changed"}';check(toolkit_reception_letter_permission($r) instanceof WP_Error,'Body tampering rejected');
 $r=signed('POST',null,['name'=>str_repeat('a',9000)]);check(toolkit_reception_letter_permission($r) instanceof WP_Error,'Bounded request');
 $r=signed('POST',null,[]);$r->method='DELETE';check(toolkit_reception_letter_permission($r) instanceof WP_Error,'Other methods rejected');
 $secret='';check(toolkit_reception_letter_permission(signed()) instanceof WP_Error,'Missing secret fails closed');$secret=str_repeat('s',40);
 $path=toolkit_calling_letter_pdf_file_path(31,'TTI-TEST');$pdf="%PDF-1.4\nsynthetic\n%%EOF\n";file_put_contents($path,$pdf);
 $wpdb->row=(object)['pdf_path'=>$path,'status'=>'generated','reference'=>'TTI-TEST'];
 $r=toolkit_reception_existing_letter(signed());check($r->status===200&&base64_decode($r->data['pdf'],true)===$pdf,'Existing PDF returned');
 check($r->data['application_id']===31&&$r->data['sha256']===hash('sha256',$pdf),'Identity and digest bound');
 check($r->headers['Cache-Control']==='no-store, private, max-age=0','No personal-data cache');check(count($r->data)===6,'No paths or raw application fields');
 $wpdb->row->status='failed';check(toolkit_reception_existing_letter(signed()) instanceof WP_Error,'Failed letter unavailable');$wpdb->row->status='generated';
 file_put_contents($root.'/production/'.basename($path),$pdf);$wpdb->row->pdf_path=$root.'/production/'.basename($path);check(toolkit_reception_existing_letter(signed()) instanceof WP_Error,'Other environment folder rejected');
 $wpdb->row->pdf_path=$path;file_put_contents($path,'<?php synthetic');check(toolkit_reception_existing_letter(signed()) instanceof WP_Error,'Disguised PDF rejected');
 file_put_contents($path,str_repeat('a',1048577));check(toolkit_reception_existing_letter(signed()) instanceof WP_Error,'Oversized file rejected');
 unlink($path);symlink($root.'/production/'.basename($path),$path);check(toolkit_reception_existing_letter(signed()) instanceof WP_Error,'Symlink rejected');unlink($path);
 $data=['reference'=>'APP-TEST-LETTER','name'=>'Synthetic Applicant','phone'=>'+254712345678','course'=>'Welding','intake'=>'January 2027'];
 class SyntheticCacheObject {public function __wakeup(){$GLOBALS['cache_object_instantiated']=true;}}
 $cache_object_instantiated=false;$entry='word/media/image5.png';$binary=toolkit_calling_letter_zip_read(toolkit_calling_letter_template_path(),$entry);
 $poisoned=$root.'/_pdfimg-'.md5($entry.'|'.strlen($binary).'|'.md5($binary)).'.cache';file_put_contents($poisoned,serialize(new SyntheticCacheObject));
 foreach([['name'=>['array']],['phone'=>'0712345678'],['course'=>'<script>'],['intake'=>"January\nInjected"],['intake'=>''],['reference'=>'../file'],['name'=>str_repeat('W',120)],['guardian'=>'unneeded'],['intake'=>str_repeat('a',121)]] as $bad)check(toolkit_reception_render_native_letter(signed('POST',null,array_replace($data,$bad))) instanceof WP_Error,'Malformed/unneeded render input rejected');
 $r=toolkit_reception_render_native_letter(signed('POST',null,$data));check($r instanceof WP_REST_Response&&$r->status===200,'Actual native PDF renderer succeeds');
 check(!$cache_object_instantiated&&str_starts_with(file_get_contents($poisoned),'a:'),'Cached objects are never instantiated; invalid cache rebuilt as safe image data');
 $bytes=base64_decode($r->data['pdf'],true);$text=streams($bytes);check(str_contains($text,'January')&&str_contains($text,'2027')&&!str_contains($text,'August/September'),'Explicit intake without stale default');
 check(str_contains($text,'SYNTHETIC')&&str_contains($text,'APPLICANT')&&str_contains($text,'WELDING'),'Applicant/course printed');
 $revision=hash('sha256',hash_file('sha256',$root.'/template.docx')."\n".hash_file('sha256',__DIR__.'/../../wp-content/themes/eduma-child/inc/calling-letter-pdf.php'));
 check($r->data['template_revision']===$revision,'Template and renderer revision recorded');
 check(!glob($root.'/.reception-letter-*'),'Temporary output removed after success');
 $template_override=$root.'/missing-template';$bad=toolkit_reception_render_native_letter(signed('POST',null,$data));
 check($bad instanceof WP_Error&&!str_contains($bad->message,$root),'Renderer failure has no private paths');check(!glob($root.'/.reception-letter-*'),'No output left after failure');unset($template_override);
 $overflow=toolkit_calling_letter_render_pdf($root.'/overflow.pdf',['full_name'=>str_repeat('Synthetic ',200),'first_name'=>'Synthetic','phone'=>'+254712345678','course'=>'Welding','letter_date'=>'26th October 2026'],['intake'=>'January 2027','strict_layout'=>true]);
 check($overflow!==true&&!is_file($root.'/overflow.pdf'),'Overfull page refused before writing an official PDF');
 // Optional private preview uses approved template assets and synthetic recipient details only.
 if(isset($argv[1],$argv[2])){
  $asset_archive=$argv[1];foreach(glob($root.'/_pdfimg-*') as $cache)unlink($cache);
  $result=toolkit_calling_letter_render_pdf($argv[2],['full_name'=>'SYNTHETIC PREVIEW — NOT FOR ISSUE','first_name'=>'Synthetic','phone'=>'+254712345678','course'=>'Welding','letter_date'=>'26th October 2026'],['intake'=>'January 2027','strict_layout'=>true]);
  check($result===true,'Approved template asset preview fits one page');
 }
 echo $count." calling-letter signature, read-only PDF, real-renderer, privacy and cleanup checks passed\n";
} finally {
 $iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
 foreach($iterator as $file){if($file->isDir()&&!$file->isLink())rmdir($file->getPathname());else unlink($file->getPathname());}rmdir($root);
}
