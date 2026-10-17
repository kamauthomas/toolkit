<?php
// Run the actual adapter with synthetic WordPress boundaries; never send network traffic.
function add_action(...$args) {}
function add_filter(...$args) {}
function get_option($key, $default = false) { return $key === 'toolkit_application_storage_version' ? '1.3.0' : $default; }
function wp_salt($key) { return 'synthetic-audit-salt'; }
function wp_hash($value) { return hash('sha256', $value); }
function home_url($path = '') { return 'https://audit.example.test'.$path; }
function untrailingslashit($value) { return rtrim($value, '/'); }
function nocache_headers() {}
function sanitize_key($value) { return strtolower(preg_replace('/[^a-zA-Z0-9_-]/', '', $value)); }
function sanitize_text_field($value) { return trim(strip_tags($value)); }
function wp_unslash($value) { return $value; }
function get_transient($key) { return 0; }
function set_transient(...$args) {}
function sanitize_email($value) { return trim($value); }
function sanitize_textarea_field($value) { return trim(strip_tags($value)); }
function wp_json_encode($value, $flags = 0) { return json_encode($value, $flags); }
function current_time(...$args) { return gmdate('Y-m-d H:i:s'); }
function wp_generate_password(...$args) { return 'SYNTH1'; }
class AuditStorageBoundary extends RuntimeException {}
class AuditDatabase {
 public string $prefix = 'synthetic_';
 public array $captured = [];
 public function prepare($query, ...$args) { return $query; }
 public function get_row(...$args) { return null; }
 public function get_var(...$args) { return 1; }
 public function insert($table, $row, $formats) { $this->captured = $row; throw new AuditStorageBoundary('Stopped before any database write'); }
}
$wpdb = new AuditDatabase;
function is_email($value) { return filter_var($value, FILTER_VALIDATE_EMAIL); }
function is_wp_error($value) { return $value instanceof WP_Error; }
function wp_safe_remote_post(...$args) { throw new RuntimeException('Network forbidden in audit tests'); }
function wp_safe_remote_get(...$args) { throw new RuntimeException('Network forbidden in audit tests'); }
class WP_Error { public function __construct(public $code, public $message, public $data = []) {} }
class WP_REST_Request {
    public function __construct(private array $data) {}
    public function get_json_params() { return $this->data; }
    public function get_header($key) { return $key === 'x_toolkit_form_token' ? toolkit_application_form_token() : ''; }
    public function get_route() { return '/toolkit/v1/application'; }
}
define('HOUR_IN_SECONDS', 3600); define('MINUTE_IN_SECONDS', 60);
$adapter = $argv[1] ?? __DIR__.'/../../wp-content/themes/eduma-child/inc/application-adapter.php';
require $adapter;
$count = 0;
function verify($condition, $label) { global $count; if (!$condition) throw new RuntimeException($label); $count++; }
foreach (['0712345678'=>'+254712345678','0112345678'=>'+254112345678','+254 712 345 678'=>'+254712345678','254112345678'=>'+254112345678','2540712345678'=>'+254712345678','+2540712345678'=>'+254712345678'] as $raw=>$expected) verify(toolkit_application_normalize_mobile((string)$raw)===$expected, 'Valid mobile normalization');
$base=['first_name'=>'Synthetic','surname'=>'Fixture','email'=>'fixture@example.com','primary_phone'=>'0712345678','secondary_phone'=>'0112345678','school_id'=>'1','course_id'=>'1','intake_id'=>'1','county'=>toolkit_application_counties()[0]['id'],'gender'=>'F','consent'=>'1'];
foreach (['1234567890','12345678901234','12345678901234567890','0712345678abc','07123', ['0712345678']] as $raw) {
 foreach (['primary_phone','secondary_phone'] as $field) {
  $data=$base;$data[$field]=$raw;$result=toolkit_application_submit(new WP_REST_Request($data));
  verify(is_wp_error($result) && in_array($result->code,['invalid_phone','missing_field']), 'Invalid contact rejected before storage or relay');
 }
}
foreach (['test@test','audit@com','fixture@example..com','fixture@example.com.invalid-','test name@example.com', ['fixture@example.com']] as $raw) {
 $data=$base;$data['email']=$raw;$result=toolkit_application_submit(new WP_REST_Request($data));
 verify(is_wp_error($result) && in_array($result->code,['invalid_email','missing_field']), 'Invalid email rejected before storage or relay');
}
verify((bool)toolkit_application_qualified_email('fixture@example.com'), 'Valid qualified email');
foreach (\Toolkit\Contacts\PhoneNumbers::countries() as $region=>$metadata) {
 if (!$metadata['example']) continue;
 $expected='+'.$metadata['code'].$metadata['example'];
 verify(toolkit_application_normalize_mobile($metadata['example'],$region)===$expected,'Official country example '.$region);
 verify(toolkit_application_normalize_mobile($expected,$region)===$expected,'International round trip '.$region);
}
foreach ([['KE','254712345678','+254712345678'],['KE','2540712345678','+254712345678'],['GB','447400123456','+447400123456'],['GB','07400123456','+447400123456'],['US','2015550123','+12015550123'],['IT','0212345678','+390212345678']] as [$country,$raw,$expected]) {
 $data=[...$base,'primary_phone'=>$raw,'primary_phone_country'=>$country];
 try { toolkit_application_submit(new WP_REST_Request($data)); throw new RuntimeException('Valid country did not reach safe storage boundary'); }
 catch (AuditStorageBoundary $error) { $payload=toolkit_application_decrypt_payload($wpdb->captured['payload']);verify($payload['primary_phone']===$expected,'Actual handler stores normalized international contact'); }
}
foreach ([['KE','+447400123456'],['INVALID','0712345678'],[['KE'],'0712345678']] as [$country,$raw]) {
 $result=toolkit_application_submit(new WP_REST_Request([...$base,'primary_phone'=>$raw,'primary_phone_country'=>$country]));verify(is_wp_error($result)&&$result->code==='invalid_phone','Country mismatch/tampering rejected before storage');
}
echo "PASS: $count application contact checks; official country examples/round trips and actual handler contacts verified; database writes/network blocked\n";
