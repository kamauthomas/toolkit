<?php
// Private CLI runtime entry point. No web route, request token or applicant output.
if ( PHP_SAPI !== 'cli' ) { http_response_code(404); exit; }
ini_set('display_errors', '0');
$roots = array('demo' => '/home/bfyigiln/demo.toolkitafrica.ac.ke', 'production' => '/home/bfyigiln/public_html');
$environment = $argv[1] ?? '';
if (!isset($roots[$environment])) { fwrite(STDERR, "Invalid environment\n"); exit(2); }
define('WP_USE_THEMES', false);
define('DOING_CRON', true);
// The CLI job and WP-Cron may overlap; record leases protect writes and this lock bounds job load.
$lock = fopen(__DIR__ . '/mzizi-relay-' . $environment . '.lock', 'c');
if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) { echo "MZIZI_WORKER_BUSY\n"; exit; }
try {
	require $roots[$environment] . '/wp-load.php';
	if (!function_exists('toolkit_application_process_relay_queue')) throw new RuntimeException('Relay worker unavailable');
	toolkit_application_install_storage();
	if (($argv[2] ?? '') === 'inspect') {
		global $wpdb;
		$table = toolkit_application_table_name();
		$columns = $wpdb->get_col("SHOW COLUMNS FROM {$table}");
		$required = array('next_relay_at', 'relay_phase', 'relay_lease', 'relay_lease_until', 'relay_http_status');
		$schema = !array_diff($required, $columns);
		if (!$schema) throw new RuntimeException('Relay schema incomplete');
		$result = array('environment' => $environment, 'release' => toolkit_theme_release(), 'schema' => $schema, 'relay_enabled' => toolkit_mzizi_relay_enabled(), 'queue_hook' => (bool) has_action('toolkit_application_process_queue', 'toolkit_application_process_relay_queue'), 'last_run' => get_option('toolkit_application_relay_last_run', null));
	} else {
		toolkit_application_process_relay_queue();
		toolkit_application_refresh_catalogues();
		$result = array('environment' => $environment, 'last_run' => get_option('toolkit_application_relay_last_run', null));
	}
	echo json_encode($result, JSON_UNESCAPED_SLASHES) . "\nMZIZI_WORKER_OK\n";
} catch (Throwable $error) {
	// Exceptions can contain database credentials or submitted data: report only their class.
	fwrite(STDERR, 'MZIZI_WORKER_FAILED ' . get_class($error) . "\n"); exit(1);
}
