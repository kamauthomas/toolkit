<?php
// Pure parser/publish-boundary checks without WordPress writes or a network.
define('ABSPATH', __DIR__);
class WP_Error { public function __construct(public $code, public $message) {} }
function add_action(...$args) {}
function is_wp_error($value) { return $value instanceof WP_Error; }
$options = [];
function get_option($key, $default = null) { global $options; return $options[$key] ?? $default; }
require __DIR__.'/../../wp-content/themes/eduma-child/inc/graduands.php';
require __DIR__.'/../../wp-content/themes/eduma-child/inc/toolkit-admin.php';
function toolkit_is_demo_environment() { return true; }
$count = 0;
function check($condition, $description) { global $count; if (!$condition) { throw new RuntimeException($description); } $count++; }
$valid = "display_name,course\nSynthetic Zeta,Electrical Installation\nSynthetic Alpha,Digital Skills\n";
$rows = toolkit_graduands_parse_csv($valid);
check(is_array($rows) && count($rows) === 2, 'Valid roster');
check($rows[0]['display_name'] === 'Synthetic Alpha', 'Course then name sorting');
check(is_array(toolkit_graduands_parse_csv("\xEF\xBB\xBF".$valid)), 'UTF8 BOM');
check(is_wp_error(toolkit_graduands_parse_csv("name,course\nA,B\n")), 'Unknown headers rejected');
check(is_wp_error(toolkit_graduands_parse_csv("display_name,course,email\nA,B,c@example.com\n")), 'Personal extra columns rejected');
check(is_wp_error(toolkit_graduands_parse_csv("display_name,course\nOnly Name\n")), 'Missing course rejected');
check(is_wp_error(toolkit_graduands_parse_csv("display_name,course\nA,B,extra\n")), 'Extra row fields rejected');
check(is_wp_error(toolkit_graduands_parse_csv("display_name,course\nA,B\na,b\n")), 'Casefolded duplicate rejected');
check(is_wp_error(toolkit_graduands_parse_csv("display_name,course\n<script>,B\n")), 'Markup rejected');
check(is_wp_error(toolkit_graduands_parse_csv("display_name,course\nA,\n")), 'Empty course rejected');
check(is_wp_error(toolkit_graduands_parse_csv("display_name,course\n")), 'Empty roster rejected');
check(is_wp_error(toolkit_graduands_parse_csv(str_repeat('x', 1048577))), 'Size bound');
check(is_wp_error(toolkit_graduands_parse_csv("display_name,course\n\xFF,B\n")), 'Invalid UTF8 rejected');
check(is_wp_error(toolkit_graduands_validate_rows(array_fill(0,2001,['display_name'=>'A','course'=>'B']))), 'Row count bound');
$options['toolkit_graduands_2026_draft'] = ['rows'=>$rows];
check(toolkit_graduands_published() === [], 'Draft never publicly exposed');
$options['toolkit_graduands_2026_published'] = ['rows'=>$rows, 'published_at'=>gmdate('c')];
check(toolkit_graduands_published() === [], 'Approval required');
$options['toolkit_graduands_2026_published']['approved'] = true;
check(count(toolkit_graduands_published()['rows']) === 2, 'Approved roster visible');
$options['toolkit_graduands_2026_published']['rows'] = [['display_name'=>'A','course'=>'B','phone'=>'private']];
check(toolkit_graduands_published() === [], 'Malformed saved public data fails closed');
echo "PASS: $count graduands checks\n";
function current_user_can($cap) { global $allowed; return $allowed; }
function wp_die($message) { throw new RuntimeException('denied'); }
function check_admin_referer($action) { global $nonce_valid; if (!$nonce_valid) { throw new RuntimeException('nonce'); } }
function sanitize_key($s) { return preg_replace('/[^a-z_]/','',$s); }
function sanitize_text_field($s) { return strip_tags($s); }
function wp_unslash($s) { return $s; }
function wp_json_encode($s) { return json_encode($s); }
function update_option($k,$v,$autoload=false) { global $options; $options[$k]=$v; }
function delete_option($k) { global $options; unset($options[$k]); }
function do_action(...$args) {} function wp_cache_flush() {}
function esc_html($s) { return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8'); }
function esc_attr($s) { return esc_html($s); }function esc_url($s) { return esc_html($s); }
function home_url($s) { return 'https://example.test'.$s; }function wp_nonce_field(...$args) {}function submit_button(...$args) {}
function manageRoster() { ob_start();try { toolkit_graduands_admin(); } finally { ob_end_clean(); } }
$options=[];$allowed=false;$nonce_valid=true;$_SERVER['REQUEST_METHOD']='GET';
try { manageRoster(); check(false,'Nonadmin blocked'); } catch (RuntimeException $e) { check($e->getMessage()==='denied','Nonadmin blocked'); }
$allowed=true;$nonce_valid=false;$_SERVER['REQUEST_METHOD']='POST';$_POST=['graduands_action'=>'publish'];
try { manageRoster(); check(false,'CSRF blocked'); } catch (RuntimeException $e) { check($e->getMessage()==='nonce','CSRF blocked'); }
$nonce_valid=true;$rows=toolkit_graduands_parse_csv($valid);$revision=hash('sha256',json_encode($rows));$options['toolkit_graduands_2026_draft']=['rows'=>$rows,'revision'=>$revision];
$_POST=['graduands_action'=>'publish','revision'=>$revision];manageRoster();check(!isset($options['toolkit_graduands_2026_published']),'Public approval checkbox required');
$_POST=['graduands_action'=>'publish','revision'=>'stale-revision','approved'=>'1'];manageRoster();check(!isset($options['toolkit_graduands_2026_published']),'Stale preview rejected');
$_POST=['graduands_action'=>'publish','revision'=>$revision,'approved'=>'1'];manageRoster();check(count(toolkit_graduands_published()['rows'])===2,'Reviewed approved draft published');
$_POST=['graduands_action'=>'withdraw'];manageRoster();check(count(toolkit_graduands_published()['rows'])===2,'Withdrawal confirmation required');
$_POST=['graduands_action'=>'withdraw','withdraw_confirmed'=>'1'];manageRoster();check(toolkit_graduands_published()===[] && isset($options['toolkit_graduands_2026_draft']),'Confirmed withdrawal preserves draft');
echo "PASS: $count total graduands checks, including permissions/CSRF/approval/revision/withdrawal\n";
function admin_url($s) { return 'https://example.test/wp-admin/'.$s; }
function get_stylesheet_directory_uri() { return 'https://example.test/wp-content/themes/eduma-child'; }
function checked($value) { if ($value) echo 'checked'; }
$options['toolkit_graduands_2026_published']=['rows'=>$rows,'approved'=>true,'published_at'=>gmdate('c')];
$_POST=[];toolkit_graduands_update_display();
check(toolkit_graduands_published()===[],'Display switch hides public roster');
check(count(toolkit_graduands_published(false)['rows'])===2,'Hidden approved names retained for management');
$_POST=['public_display'=>'1'];toolkit_graduands_update_display();check(count(toolkit_graduands_published()['rows'])===2,'Display switch restores approved roster');
$nonce_valid=false;$_POST=[];try {toolkit_graduands_update_display();check(false,'Display CSRF rejected');}catch(RuntimeException $e){check($e->getMessage()==='nonce','Display CSRF rejected');}
check(count(toolkit_graduands_published()['rows'])===2,'Rejected display request does not hide roster');
$nonce_valid=true;$allowed=false;try {toolkit_graduands_update_display();check(false,'Display admin required');}catch(RuntimeException $e){check($e->getMessage()==='denied','Display admin required');}
ob_start();toolkit_graduands_qr_panel();$panel=ob_get_clean();check($panel==='','QR controls hidden from nonadmin');
$allowed=true;ob_start();toolkit_graduands_qr_panel();$panel=ob_get_clean();check(str_contains($panel,'graduands-2026-qr.png')&&str_contains($panel,'graduands-2026-qr.svg'),'Both QR downloads present');check(str_contains($panel,'https://toolkitafrica.ac.ke/graduation/#graduands'),'QR control has stable production URL');check(str_contains($panel,'Manage names and courses')&&str_contains($panel,'public_display'),'Management and display controls present');
echo "PASS: $count total graduands and dashboard QR checks\n";
