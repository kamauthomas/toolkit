<?php
/** Isolated export/auth/privacy contract, with synthetic applications only. */
class WP_Error { public function __construct(public $code, public $message, public $data=[]) {} }
class WP_REST_Response { public array $headers=[]; public function __construct(public $data,public $status=200){} public function header($k,$v){$this->headers[$k]=$v;} }
class WP_REST_Server { const READABLE='GET'; }
class WP_REST_Request {
 public function __construct(public array $query=[],public array $headers=[]){} public function get_query_params(){return $this->query;}public function get_param($k){return $this->query[$k]??null;}public function get_header($k){return $this->headers[$k]??'';}
}
define('TOOLKIT_RECEPTION_APPLICATION_SYNC_ENABLED',true);
$secret=str_repeat('s',40);$nonces=[];$routes=[];$query_args=[];$fail_decrypt=false;
function toolkit_reception_api_secret(){return $GLOBALS['secret'];} function is_wp_error($v){return $v instanceof WP_Error;}
function add_option($k,$v,...$rest){if(isset($GLOBALS['nonces'][$k]))return false;$GLOBALS['nonces'][$k]=$v;return true;}
function add_action($name,$callback){if($name==='rest_api_init')$callback();} function register_rest_route($ns,$route,$config){$GLOBALS['routes'][$route]=$config;}
function toolkit_application_table_name(){return 'synthetic_applications';}function toolkit_is_demo_environment(){return true;}
function toolkit_application_status_label($s){return $s==='delivered'?'Delivered':'Received';}
function toolkit_application_decrypt_payload($s){return $GLOBALS['fail_decrypt']?new WP_Error('private_key','Do not disclose decryption details'):json_decode($s,true);}
class FakeWpdb {
 public $options='synthetic_options';public $last_error='';public $records=[];
 function prepare($s,...$a){$GLOBALS['query_args']=$a;return $s;}function esc_like($s){return addcslashes($s,'_%\\');}function query($s){return 0;}
 function get_results($s){$a=$GLOBALS['query_args'];return array_slice(array_values(array_filter($this->records,fn($r)=>$r->id>$a[0])),0,$a[1]);}
}
$wpdb=new FakeWpdb();require __DIR__.'/../../wp-content/themes/eduma-child/inc/reception-application-sync.php';
$count=0;function check($ok,$why){if(!$ok)throw new RuntimeException($why);$GLOBALS['count']++;}
function signed(array $q=[],array $h=[]): WP_REST_Request {
 $t=(string)time();$n=bin2hex(random_bytes(16));$s=hash_hmac('sha256',$t."\n".$n."\napplications-read-v1\n".(int)($q['after']??0)."\n".(int)($q['limit']??50),$GLOBALS['secret']);
 return new WP_REST_Request($q,array_replace(['x_toolkit_timestamp'=>$t,'x_toolkit_nonce'=>$n,'x_toolkit_signature'=>$s],$h));
}
check($routes['/reception/applications']['methods']==='GET','No mutation method registered');
check(toolkit_reception_application_sync_permission(new WP_REST_Request()) instanceof WP_Error,'Missing signature denied');
foreach ([['after'=>'-1'],['after'=>['1']],['limit'=>'51'],['limit'=>'0'],['extra'=>'x'],['after'=>'1 OR 1=1']] as $q)check(toolkit_reception_application_sync_permission(new WP_REST_Request($q)) instanceof WP_Error,'Malformed query rejected');
check(toolkit_reception_application_sync_permission(signed([],['x_toolkit_timestamp'=>'1'])) instanceof WP_Error,'Old signature rejected');
check(toolkit_reception_application_sync_permission(signed([],['x_toolkit_signature'=>str_repeat('0',64)])) instanceof WP_Error,'Wrong signature rejected');
$r=signed(['after'=>'0','limit'=>'2']);check(toolkit_reception_application_sync_permission($r)===true,'Signed request allowed');check(toolkit_reception_application_sync_permission($r) instanceof WP_Error,'Replay denied');
$secret='';check(toolkit_reception_application_sync_permission(new WP_REST_Request()) instanceof WP_Error,'Missing provider secret fails closed');$secret=str_repeat('s',40);
$payload=['first_name'=>'<script>synthetic</script>','middle_name'=>'Only','surname'=>'Applicant','email'=>'synthetic@example.test','primary_phone'=>'+254712345678','course_id'=>'1','course_name'=>'Welding','intake_name'=>'October','school_name'=>'Kikuyu','birth_date'=>'private','guardian'=>'private','qualifications'=>'private','secondary_phone'=>'private'];
for($i=1;$i<=3;$i++)$wpdb->records[]=(object)['id'=>$i,'reference'=>'TTI-TEST-'.$i,'status'=>'received','workflow_status'=>'new','payload'=>json_encode($payload),'created_at'=>'2026-10-26 08:00:00','updated_at'=>'2026-10-26 08:01:00','relayed_at'=>null];
$result=toolkit_reception_application_sync_page(new WP_REST_Request(['after'=>'0','limit'=>'2']));
check($result->status===200&&count($result->data['applications'])===2&&$result->data['has_more']&&$result->data['next_cursor']===2,'Bounded monotonic page');
check($result->data['source']==='demo','Environment labelled');check($result->headers['Cache-Control']==='no-store, private, max-age=0','Personal data never cached');
$row=$result->data['applications'][0];check(count($row)===14&&!array_diff(array_keys($row),['id','reference','name','email','phone','course','intake','campus','delivery_status','delivery_label','review_status','submitted_at','updated_at','delivered_at']),'Only minimal fields exported');
foreach(['birth_date','guardian','qualifications','secondary_phone','payload','mzizi_message','last_error'] as $key)check(!array_key_exists($key,$row),'Sensitive field excluded: '.$key);
check($row['submitted_at']==='2026-10-26T08:00:00Z','UTC dates explicit');
$end=toolkit_reception_application_sync_page(new WP_REST_Request(['after'=>'3','limit'=>'2']));check(!$end->data['has_more']&&$end->data['next_cursor']===3&&!$end->data['applications'],'Empty page keeps cursor');
$fail_decrypt=true;$bad=toolkit_reception_application_sync_page(new WP_REST_Request(['after'=>'0','limit'=>'2']));check($bad instanceof WP_Error&&!str_contains($bad->message,'private_key'),'Decryption errors fail closed without disclosure');
$fail_decrypt=false;$wpdb->records[0]->payload=json_encode(array_replace($payload,['email'=>['malformed']]));
check(toolkit_reception_application_sync_page(new WP_REST_Request(['after'=>'0','limit'=>'2'])) instanceof WP_Error,'Malformed saved scalar fields fail closed');
echo $count." Reception export/auth/privacy checks passed\n";
