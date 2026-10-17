<?php
/** Real MariaDB + deployed adapter with synthetic HTTP only. Usage: php mzizi-relay.php /tmp/socket [adapter] */
if (PHP_SAPI !== 'cli' || !isset($argv[1]) || !str_starts_with($argv[1], '/tmp/')) throw new RuntimeException('A temporary /tmp MariaDB socket is required');
set_error_handler(function($severity, $message, $file, $line) { throw new ErrorException($message, 0, $severity, $file, $line); });
$pdo = new PDO('mysql:unix_socket='.$argv[1], 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$name = 'toolkit_relay_test_'.bin2hex(random_bytes(5));
$pdo->exec("CREATE DATABASE `$name`"); $pdo->exec("USE `$name`");
register_shutdown_function(function() use ($pdo,$name) { $pdo->exec("DROP DATABASE IF EXISTS `$name`"); });
class WP_Error {
 public function __construct(public $code, public $message, public $data=[]) {}
 public function get_error_code(){return $this->code;} public function get_error_message(){return $this->message;} public function get_error_data(){return $this->data;}
}
class WP_REST_Response {public function __construct(public $data,public $status){}}
function is_wp_error($v){return $v instanceof WP_Error;}
function add_action(...$args){} function add_filter(...$args){} function do_action(...$args){if($GLOBALS['throw_after_store']??false)throw new RuntimeException('Synthetic post-save interruption');}
function home_url($path=''){return 'https://synthetic.example.test'.$path;}
function wp_generate_password(...$args){return 'SYNTH1';}
function wp_salt($k){return 'synthetic-test-key';} function wp_json_encode($v,$f=0){return json_encode($v,$f);}
function sanitize_key($v){return strtolower(preg_replace('/[^a-zA-Z0-9_-]/','',$v));}
function sanitize_text_field($v){return trim(strip_tags($v));} function sanitize_textarea_field($v){return sanitize_text_field($v);} function sanitize_email($v){return trim($v);}
function current_time(...$args){return gmdate('Y-m-d H:i:s');} function wp_generate_uuid4(){return bin2hex(random_bytes(16));}
function get_current_user_id(){return 7;} function current_user_can(...$args){return $GLOBALS['is_admin'];}
function get_option($k,$d=false){return $GLOBALS['options'][$k]??$d;} function update_option($k,$v,...$args){$GLOBALS['options'][$k]=$v;}
function wp_remote_retrieve_response_code($v){return $v['status'];} function wp_remote_retrieve_body($v){return json_encode($v['body']);}
function wp_remote_retrieve_cookies($v){return ['synthetic-cookie'];}
function wp_safe_remote_get(...$args){$GLOBALS['lookup_calls']++;return $GLOBALS['session_failure'] ? new WP_Error('timeout','sensitive network detail') : ['status'=>200];}
function wp_safe_remote_post($url,$args){
 $GLOBALS['lookup_calls']++;
 $service=basename(parse_url($url,PHP_URL_PATH));
 if ($GLOBALS['during_preflight']) { $fn=$GLOBALS['during_preflight'];$GLOBALS['during_preflight']=null;$fn(); }
 if ($GLOBALS['lookup_failure'] && $service==='GetApplicationCoursesNoAlumni') return ['status'=>$GLOBALS['lookup_failure']->data['upstream_status'],'body'=>['Message'=>$GLOBALS['lookup_failure']->message]];
 $body=match($service){
 'SetApplicationSchoolIDParam'=>['Success'=>true],
 'GetSchools'=>[['ID'=>'1','Name'=>'Synthetic campus'],['ID'=>'2','Name'=>'Kakuma campus']],
 'GetApplicationCoursesNoAlumni'=>[['ID'=>'1','Name'=>'Synthetic course','Description'=>$GLOBALS['course_label']]],
 'GetCourseIntakeMonths'=>[['ID'=>'1','LevelName'=>'October']],
 'GetCustomerSources'=>[['ID'=>'1','Name'=>'Website']],
 'SubmitOnlineApplication'=>$GLOBALS['submit_result'],
 default=>throw new RuntimeException('Unmocked service '.$service),};
 if($service==='SubmitOnlineApplication') {
  $GLOBALS['submits']++;$GLOBALS['sent_payload']=json_decode($args['body'],true);
  if($GLOBALS['during_submit']){$fn=$GLOBALS['during_submit'];$GLOBALS['during_submit']=null;$fn();}
  if($body instanceof WP_Error)return $body;
  return ['status'=>$GLOBALS['submit_status'],'body'=>$body];
 }
 return ['status'=>200,'body'=>$body];
}
class FixtureWpdb {
 public $prefix='fixture_';public $insert_id=0;public $fail_finish=false;public $fail_submit_marker=false;
 public function __construct(public PDO $db){}
 public function prepare($sql,...$args){$i=0;return preg_replace_callback('/%[ds]/',function($m)use(&$i,$args){$v=$args[$i++];return $m[0]==='%d'?(string)(int)$v:$this->db->quote((string)$v);},$sql);}
 public function get_charset_collate(){return 'DEFAULT CHARACTER SET utf8mb4';}
 public function query($sql){if($this->fail_submit_marker&&str_contains($sql,"SET relay_phase = 'submitting'"))return false;return $this->db->exec($sql);}
 public function get_var($sql){return $this->db->query($sql)->fetchColumn();}
 public function get_row($sql){return $this->db->query($sql)->fetchObject()?:null;}
 public function get_col($sql){return $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);}
 public function get_results($sql){return $this->db->query($sql)->fetchAll(PDO::FETCH_OBJ);}
 public function insert($table,$values,...$args){$columns=implode(',',array_keys($values));$stmt=$this->db->prepare("INSERT INTO $table ($columns) VALUES (".implode(',',array_fill(0,count($values),'?')).')');$stmt->execute(array_values($values));$this->insert_id=(int)$this->db->lastInsertId();return $stmt->rowCount();}
 public function update($table,$values,$where,...$args){
  if($this->fail_finish&&isset($values['relay_lease'])===false&&array_key_exists('relay_lease',$values))return false;
  $set=[];$clauses=[];$args=[];foreach($values as $k=>$v){$set[]="$k = ?";$args[]=$v;}
  foreach($where as $k=>$v){if($v===null)$clauses[]="$k IS NULL";else{$clauses[]="$k = ?";$args[]=$v;}}
  $q=$this->db->prepare("UPDATE $table SET ".implode(',',$set).' WHERE '.implode(' AND ',$clauses));$q->execute($args);return $q->rowCount();
 }
}
$wpdb=new FixtureWpdb($pdo);$options=[];$is_admin=true;
define('MINUTE_IN_SECONDS',60);define('HOUR_IN_SECONDS',3600);define('DAY_IN_SECONDS',86400);
$fixtureRoot=sys_get_temp_dir().'/'.$name;mkdir($fixtureRoot.'/wp-admin/includes',0700,true);
file_put_contents($fixtureRoot.'/wp-admin/includes/upgrade.php','<?php function dbDelta($sql){return $GLOBALS["wpdb"]->query($sql);}');
register_shutdown_function(function()use($fixtureRoot){unlink($fixtureRoot.'/wp-admin/includes/upgrade.php');rmdir($fixtureRoot.'/wp-admin/includes');rmdir($fixtureRoot.'/wp-admin');rmdir($fixtureRoot);});
define('ABSPATH',$fixtureRoot.'/');
require $argv[2]??__DIR__.'/../../wp-content/themes/eduma-child/inc/application-adapter.php';
toolkit_application_install_storage();
class WP_REST_Request {
 public function __construct(private array $data=[]){} public function get_param($k){return $this->data[$k]??null;}
 public function get_header($k){return $k==='x_toolkit_form_token'?toolkit_application_form_token():'';} public function get_route(){return '/catalogue';}
}
function nocache_headers(){} function untrailingslashit($v){return rtrim($v,'/');} function wp_unslash($v){return $v;}
function wp_hash($v){return hash('sha256',$v);} function apply_filters($k,$v){return $v;}
function get_transient($k){return $GLOBALS['transients'][$k]??false;} function set_transient($k,$v,...$args){$GLOBALS['transients'][$k]=$v;}
function rest_ensure_response($v){return new WP_REST_Response($v,200);}
$lookup_calls=0;$course_label='Synthetic course';$transients=[];
$count=0;
function check($ok,$label){global $count;if(!$ok)throw new RuntimeException($label);$count++;}
function record(){return $GLOBALS['wpdb']->get_row('SELECT * FROM fixture_toolkit_applications WHERE id=1');}
function history(){return json_encode($GLOBALS['wpdb']->get_results('SELECT * FROM fixture_toolkit_applications_events'));}
function fixture($status='received',$attempts=0,$phase=null,$lease=null,$next='2000-01-01 00:00:00'){
 global $wpdb,$options,$is_admin,$session_failure,$lookup_failure,$submit_result,$submit_status,$during_preflight,$during_submit,$submits,$sent_payload;
 $wpdb->query('TRUNCATE fixture_toolkit_applications');$wpdb->query('TRUNCATE fixture_toolkit_applications_events');
 $options=['toolkit_application_settings_managed'=>true,'toolkit_mzizi_relay_enabled'=>'1','toolkit_application_storage_version'=>'1.3.0'];$is_admin=true;
 $_POST=['duplicate_checked'=>'1'];$session_failure=false;$lookup_failure=null;$submit_result=['Message'=>'Your application was submitted successfully.'];$submit_status=200;$during_preflight=null;$during_submit=null;$submits=0;$sent_payload=null;$wpdb->fail_finish=false;$wpdb->fail_submit_marker=false;
 $data=['school_id'=>'1','course_id'=>'1','intake_id'=>'1','first_name'=>'Synthetic','middle_name'=>'','surname'=>'Fixture','nationality'=>'Kenya','county'=>'NAIROBI','email'=>'synthetic@example.test','primary_phone'=>'+254712345678','secondary_phone'=>'+254112345678','gender'=>'F','study_mode'=>'Physical','sponsorship_type'=>'Self-Sponsored','referral_source'=>'Website'];
 $wpdb->insert('fixture_toolkit_applications',['id'=>1,'reference'=>'SYNTHETIC-ONLY','payload'=>toolkit_application_encrypt_payload($data),'status'=>$status,'relay_attempts'=>$attempts,'relay_phase'=>$phase,'relay_lease'=>$lease,'relay_lease_until'=>$lease?'2000-01-01 00:00:00':null,'next_relay_at'=>$next,'created_at'=>'2000-01-01 00:00:00','updated_at'=>'2000-01-01 00:00:00']);
}
check(count($wpdb->get_results("SHOW COLUMNS FROM fixture_toolkit_applications LIKE 'relay_lease'"))===1,'Installer creates lease schema');
fixture();$result=toolkit_application_relay_record(1,'public');check($result instanceof WP_REST_Response && record()->status==='delivered' && $submits===1,'Successful relay confirmed exactly once');
check($sent_payload['Gender']==='F','Gender case matches Mzizi published form');
check($sent_payload['ModeOfStudy']==='Physical'&&$sent_payload['SponsorshipType']==='Self-Sponsored','Saved study and sponsorship reach Mzizi');
check(record()->relay_attempts===1&&record()->relay_lease===null,'Attempt counted and lease released');
check(!str_contains(history(),'synthetic@example.test'),'No applicant contacts in new audit events');
check(is_wp_error(toolkit_application_relay_record(1,'manual'))&&$submits===1,'Delivered record cannot resend');
fixture();$session_failure=true;toolkit_application_relay_record(1,'public');check(record()->status==='retry_wait'&&$submits===0&&record()->next_relay_at!==null,'Session failure schedules safe retry');
check(str_contains(history(),'retry_scheduled')&&str_contains(history(),'relay_failed'),'Failed attempt and next retry are logged');
check(abs(strtotime(record()->next_relay_at.' UTC')-time()-300)<4,'First retry delay is five minutes');
check(!str_contains(history(),'sensitive network detail'),'Transport error details omitted from logs');
fixture('retry_wait',1);toolkit_application_process_relay_queue();check(record()->status==='delivered'&&record()->relay_attempts===2,'Scheduled worker delivers due safe retry without broad automatic gate');
fixture('retry_wait',1,null,null,'2099-01-01 00:00:00');toolkit_application_process_relay_queue();check($submits===0&&record()->relay_attempts===1,'Future retries are not run early');
foreach([1=>900,2=>3600,3=>14400] as $previous=>$delay){fixture('retry_wait',$previous);$session_failure=true;toolkit_application_process_relay_queue();check(abs(strtotime(record()->next_relay_at.' UTC')-time()-$delay)<4,'Retry backoff increases');}
fixture('retry_wait',4);$session_failure=true;toolkit_application_process_relay_queue();check(record()->status==='relay_failed'&&record()->next_relay_at===null&&record()->relay_attempts===5,'Five attempt budget stops automatic retries');
fixture();$lookup_failure=new WP_Error('mzizi_response','secret fixture error',['upstream_status'=>503]);toolkit_application_relay_record(1,'public');check(record()->status==='retry_wait'&&record()->relay_attempts===1&&str_contains(history(),'HTTP 503'),'Catalogue outage logged with HTTP and attempt');
fixture();$lookup_failure=new WP_Error('mzizi_response','secret fixture error',['upstream_status'=>403]);toolkit_application_relay_record(1,'public');check(record()->status==='relay_failed'&&record()->next_relay_at===null,'Permanent preflight failure requires review');
fixture();$submit_status=409;$submit_result=['Message'=>'sensitive-person@example.test application error'];toolkit_application_relay_record(1,'public');
check(record()->status==='delivery_unconfirmed'&&record()->relay_http_status===409&&record()->next_relay_at===null,'Mzizi 409 held for confirmation');
check(str_contains(history(),'HTTP 409')&&!str_contains(history(),'sensitive-person'),'409 metadata recorded without response body');
toolkit_application_process_relay_queue();check($submits===1,'Queue never replays a 409 submission');
fixture();$submit_result=new WP_Error('http_request_failed','Timeout, secret');toolkit_application_relay_record(1,'public');toolkit_application_process_relay_queue();check(record()->status==='delivery_unconfirmed'&&$submits===1,'Ambiguous timeout never automatically resent');
foreach([['Message'=>'An error occurred'],['Message'=>'Application failed'],['Success'=>false,'Message'=>'Successfully processed'],['d'=>null],['Message'=>'Unsuccessful application'],['Message'=>'Your application was not successful'],['Message'=>'Application was processed unsuccessfully']] as $response){fixture();$submit_result=$response;toolkit_application_relay_record(1,'public');check(record()->status==='delivery_unconfirmed','Nonempty rejection is not delivery confirmation');}
fixture();$during_preflight=function(){check(is_wp_error(toolkit_application_relay_record(1,'manual')),'Concurrent second claimant rejected');};toolkit_application_relay_record(1,'public');check($submits===1&&record()->relay_attempts===1,'Concurrent manual/automatic release sends once');
fixture('relaying',1,'preflight','expired');$options['toolkit_mzizi_relay_enabled']='0';toolkit_application_process_relay_queue();check(record()->status==='retry_wait'&&$submits===0,'Stale preflight recovered safely while relay paused');
fixture('relaying',1,'submitting','expired');toolkit_application_process_relay_queue();check(record()->status==='delivery_unconfirmed'&&$submits===0,'Stale post-boundary worker requires confirmation');
fixture('relaying',1);toolkit_application_process_relay_queue();check(record()->status==='delivery_unconfirmed'&&$submits===0,'Legacy stale relay never automatically replayed');
fixture();$during_preflight=function(){global $wpdb;$wpdb->query("UPDATE fixture_toolkit_applications SET relay_lease_until='2000-01-01 00:00:00'");toolkit_application_process_relay_queue();};toolkit_application_relay_record(1,'public');check($submits===0&&record()->status==='retry_wait','Expired worker cannot cross submission boundary after recovery');
fixture();$during_submit=function(){global $wpdb;$wpdb->query("UPDATE fixture_toolkit_applications SET relay_lease_until='2000-01-01 00:00:00'");toolkit_application_process_relay_queue();};$result=toolkit_application_relay_record(1,'public');check(is_wp_error($result)&&record()->status==='delivery_unconfirmed'&&$submits===1,'Recovered worker cannot overwrite uncertainty with stale success');
fixture();$wpdb->fail_submit_marker=true;toolkit_application_relay_record(1,'public');check($submits===0,'Failed durable submission marker prevents POST');
fixture();$wpdb->fail_finish=true;$result=toolkit_application_relay_record(1,'public');check(is_wp_error($result)&&record()->status==='relaying'&&record()->relay_phase==='submitting','Outcome persistence failure cannot claim success or replay');
fixture();$is_admin=false;check(is_wp_error(toolkit_application_relay_record(1,'manual'))&&$submits===0,'Manual release checks administrator permission inside worker');
fixture();$_POST=[];check(is_wp_error(toolkit_application_relay_record(1,'manual'))&&$submits===0,'Manual release requires duplicate check');
fixture('queued');toolkit_application_process_relay_queue();check($submits===0,'Existing queued records preserve automatic-release gate');
fixture('relay_failed',1,null,null,null);toolkit_application_process_relay_queue();check($submits===0,'Historical failures are not silently replayed');
fixture('received',0,null,null,null);toolkit_application_process_relay_queue();check($submits===0,'Historical received records require explicit watchdog eligibility');
fixture('delivery_unconfirmed',1);check(is_wp_error(toolkit_application_relay_record(1,'public'))&&$submits===0,'Public callback cannot replay uncertain delivery');
fixture('retry_wait',1);$options['toolkit_mzizi_relay_enabled']='0';toolkit_application_process_relay_queue();check($submits===0,'Relay kill switch pauses retries');
fixture();$wpdb->query('TRUNCATE fixture_toolkit_applications');
$data=['first_name'=>'Synthetic','surname'=>'Fixture','email'=>'duplicate@example.test','primary_phone'=>'+254712345678','course_id'=>'1'];
$stored=toolkit_application_store($data);$repeat=toolkit_application_store($data);
check(is_array($stored)&&is_wp_error($repeat)&&$repeat->get_error_data()['status']===409,'Local duplicate returns its own 409 without relay');
check((int)$wpdb->get_var('SELECT COUNT(*) FROM fixture_toolkit_applications')===1,'Duplicate application stored once');
$lock='toolkit_app_'.substr(hash('sha256',toolkit_application_table_name().'|'.toolkit_application_fingerprint($data)),0,48);
$other=new PDO('mysql:unix_socket='.$argv[1], 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$other->query('SELECT GET_LOCK('.$other->quote($lock).',0)');
$blocked=toolkit_application_store($data);
check(is_wp_error($blocked)&&$blocked->get_error_code()==='application_busy','Competing connection cannot bypass deduplication');
$other->query('SELECT RELEASE_LOCK('.$other->quote($lock).')');
fixture();$wpdb->query('TRUNCATE fixture_toolkit_applications');
$options['toolkit_application_turnstile_enabled']='1';
$options['toolkit_application_security_keys']=toolkit_application_encrypt_payload(['site_key'=>'synthetic','secret_key'=>'synthetic']);
$throw_after_store=true;
try{toolkit_application_store($data);throw new RuntimeException('Expected synthetic interruption');}catch(RuntimeException $error){check($error->getMessage()==='Synthetic post-save interruption','Synthetic post-save hook interruption exercised');}
$throw_after_store=false;
check(record()->next_relay_at!==null&&record()->status==='received','Watchdog survives interruption in post-save calling-letter hooks');
check(str_contains(history(),'stored'),'Every new saved application has a local audit event');
// Last-known-good catalogue survives an outage, independently of relay settings.
fixture();$lookup_calls=0;
$optionsResult=toolkit_application_options(new WP_REST_Request());
check($optionsResult instanceof WP_REST_Response&&count($optionsResult->data['schools'])===1,'Catalogue bootstrap hides excluded campus');
$courses=toolkit_application_courses(new WP_REST_Request(['school_id'=>'1']));
$intakes=toolkit_application_intakes(new WP_REST_Request(['course_id'=>'1']));
check($courses->data['courses'][0]['label']==='Synthetic course'&&$intakes->data['intakes'][0]['id']==='1','Courses and intakes snapshotted');
$session_failure=true;$before=$lookup_calls;
check(toolkit_application_options(new WP_REST_Request())->data['schools'][0]['id']==='1','Saved campuses available during outage');
check(toolkit_application_courses(new WP_REST_Request(['school_id'=>'1']))->data===$courses->data,'Saved courses available during outage');
check(toolkit_application_intakes(new WP_REST_Request(['course_id'=>'1']))->data===$intakes->data,'Saved intakes available during outage');
check($lookup_calls===$before,'Cached public form performs no upstream lookup');
check(is_wp_error(toolkit_application_catalog_refresh('courses_1',true)),'Background outage recorded');
check(toolkit_application_catalog_get('courses_1')===$courses->data,'Failed refresh never erases last good course list');
$before=$lookup_calls;toolkit_application_catalog_refresh('courses_1',true);check($lookup_calls===$before,'Failure cooldown prevents repeated upstream calls');
$session_failure=false;$entry=get_option(toolkit_application_catalog_option('courses_1'));$entry['retry_after']=0;update_option(toolkit_application_catalog_option('courses_1'),$entry);
$course_label='Updated synthetic course';toolkit_application_catalog_refresh('courses_1',true);
check(toolkit_application_catalog_get('courses_1')['courses'][0]['label']==='Updated synthetic course','Successful refresh publishes course changes');
$before=$lookup_calls;
foreach([['school_id'=>'9999'],['school_id'=>'../1'],['school_id'=>['1']],['school_id'=>'2']] as $data)check(is_wp_error(toolkit_application_courses(new WP_REST_Request($data))),'Unknown/tampered/excluded campus cannot grow cache');
check(is_wp_error(toolkit_application_intakes(new WP_REST_Request(['course_id'=>'9999']))),'Unknown intake course rejected');
check($lookup_calls===$before,'Rejected arbitrary IDs never reach upstream');
$options=[];$transients=['toolkit_mzizi_courses_1'=>[['id'=>'1','label'=>'Legacy course']]];
check(toolkit_application_catalog_get('courses_1')['courses'][0]['label']==='Legacy course','Legacy transient promoted to durable snapshot');
$transients=[];$session_failure=true;check(toolkit_application_catalog_get('courses_1')['courses'][0]['label']==='Legacy course','Snapshot survives transient deletion');
$options=[];$session_failure=false;$lookup_calls=0;toolkit_application_refresh_catalogues();
check(get_option('toolkit_application_catalog_last_run')['checked']===1,'Background job can bootstrap options');
toolkit_application_refresh_catalogues();toolkit_application_refresh_catalogues();
check(isset(get_option(toolkit_application_catalog_option('intakes_1'))['payload']['intakes']),'Background job preloads intake choices before visitor selection');
foreach(['options','courses_1','intakes_1'] as $key){$entry=get_option(toolkit_application_catalog_option($key));$entry['checked_at']=1;update_option(toolkit_application_catalog_option($key),$entry);}
toolkit_application_refresh_catalogues();check(get_option('toolkit_application_catalog_last_run')['checked']<=2,'Background refresh batch is bounded');
echo "PASS: $count relay/catalogue checks; real isolated MariaDB, synthetic HTTP, zero live Mzizi requests\n";
