<?php
// CLI-only, read-only admission diagnostics. Never decrypt or export applicants.
if ( PHP_SAPI !== 'cli' ) { http_response_code(404); exit; }
try {
	$result = array('utc' => gmdate('c'), 'sites' => array());
	foreach (array('demo' => '/home/bfyigiln/demo.toolkitafrica.ac.ke', 'production' => '/home/bfyigiln/public_html') as $name => $root) {
		$config = file_get_contents($root . '/wp-config.php'); $constants = array();
		preg_match_all('~define\s*\(\s*([\'"])(DB_NAME|DB_USER|DB_PASSWORD|DB_HOST)\1\s*,\s*([\'"])((?:\\\\.|(?!\3).)*)\3\s*\)~s', $config, $matches, PREG_SET_ORDER);
		foreach ($matches as $m) $constants[$m[2]] = $m[3] === "'" ? str_replace(array("\\'", "\\\\"), array("'", "\\"), $m[4]) : stripcslashes($m[4]);
		if (count($constants) !== 4 || !preg_match('/\$table_prefix\s*=\s*([\'"])([A-Za-z0-9_]+)\1/', $config, $prefix)) throw new RuntimeException('Configuration parse failed');
		$automatic = (bool) preg_match('/define\s*\(\s*[\'"]TOOLKIT_MZIZI_AUTOMATIC_RELEASE_ENABLED[\'"]\s*,\s*true\s*\)/i', $config);
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
		$db = new mysqli($constants['DB_HOST'], $constants['DB_USER'], $constants['DB_PASSWORD'], $constants['DB_NAME']); unset($config, $constants);
		$db->begin_transaction(MYSQLI_TRANS_START_READ_ONLY); $table = $prefix[2] . 'toolkit_applications';
		$site = array('automatic_release_configured' => $automatic, 'counts' => array(), 'non_delivered' => array(), 'event_counts' => array(), 'options' => array());
		foreach ($db->query("SELECT status,COUNT(*) AS total FROM {$table} GROUP BY status") as $row) $site['counts'][$row['status']] = (int) $row['total'];
		foreach ($db->query("SELECT id,status,created_at,updated_at,relay_attempts,last_error FROM {$table} WHERE status <> 'delivered' ORDER BY id DESC LIMIT 100") as $row) {
			$error = $row['last_error'] ?? ''; unset($row['last_error']);
			$row['http_status'] = preg_match('/HTTP\s+(\d{3})/', $error, $m) ? (int) $m[1] : null;
			$row['error_class'] = stripos($error, 'session') !== false ? 'session' : (stripos($error, 'availability') !== false ? 'catalogue' : ($error ? 'other' : 'none'));
			$site['non_delivered'][] = $row;
		}
		foreach ($db->query("SELECT event_type,COUNT(*) AS total FROM {$table}_events GROUP BY event_type") as $row) $site['event_counts'][$row['event_type']] = (int) $row['total'];
		foreach ($db->query("SELECT option_name,option_value FROM {$prefix[2]}options WHERE option_name IN ('toolkit_mzizi_relay_enabled','toolkit_application_settings_managed','toolkit_application_storage_version','toolkit_application_resolver_last_run','toolkit_application_relay_last_run','toolkit_application_catalog_last_run')") as $row) $site['options'][$row['option_name']] = $row['option_value'];
		$site['catalogue_snapshots'] = array();
		foreach ($db->query("SELECT option_name,option_value FROM {$prefix[2]}options WHERE option_name LIKE 'toolkit\\_mzizi\\_snapshot\\_%' LIMIT 500") as $row) {
			$value = unserialize($row['option_value'], array('allowed_classes' => false));
			if (!is_array($value)) continue;
			$site['catalogue_snapshots'][] = array('key' => $row['option_name'], 'updated_at' => $value['updated_at'] ?? null, 'checked_at' => $value['checked_at'] ?? null, 'has_payload' => isset($value['payload']), 'items' => array_map('count', array_filter($value['payload'] ?? array(), 'is_array')));
		}
		$site['confirmation_shapes'] = array();
		foreach ($db->query("SELECT mzizi_message FROM {$table} WHERE status = 'delivered' LIMIT 50") as $row) {
			$message = $row['mzizi_message'] ?? '';
			$site['confirmation_shapes'][] = array('bytes' => strlen($message), 'affirmative' => (bool) preg_match('/\\bsuccess(?:ful|fully)?\\b/i', $message), 'rejection' => (bool) preg_match('/\\b(error|fail(?:ed|ure)?|invalid|duplicate|already|unsuccessful)\\b/i', $message));
		}
		$db->rollback(); $db->close(); $result['sites'][$name] = $site;
	}
	$destination = dirname(__FILE__) . '/diagnostics.json';
	file_put_contents($destination, json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); chmod($destination, 0600);
	echo "TOOLKIT_AUDIT_OK\n";
} catch (Throwable $e) { fwrite(STDERR, 'TOOLKIT_AUDIT_FAILED ' . get_class($e) . "\n"); exit(1); }
