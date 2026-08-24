<?php
/**
 * Temporary CLI-only application encryption migration runner.
 *
 * Dry-run is the default. Live writes require both --execute and the
 * TOOLKIT_MIGRATION_EXECUTE=YES environment guard.
 */

if ( PHP_SAPI !== 'cli' ) {
	http_response_code( 404 );
	exit;
}

ini_set( 'memory_limit', '512M' );

$result = array( 'ok' => false, 'stage' => 'bootstrap' );
register_shutdown_function( function() use ( &$result ) {
	$error = error_get_last();
	if ( $error && in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR ), true ) ) {
		$result = array(
			'ok'            => false,
			'stage'         => 'fatal',
			'error_type'    => (int) $error['type'],
			'error_message' => substr( strip_tags( (string) $error['message'] ), 0, 240 ),
			'error_file'    => basename( (string) $error['file'] ),
			'error_line'    => (int) $error['line'],
		);
		echo json_encode( $result ), PHP_EOL;
	}
} );

require '/home/bfyigiln/public_html/wp-load.php';

$execute = in_array( '--execute', $argv, true ) && 'YES' === getenv( 'TOOLKIT_MIGRATION_EXECUTE' );
$current = toolkit_application_current_key();
if ( empty( $current['dedicated'] ) ) {
	echo wp_json_encode( array( 'ok' => false, 'stage' => 'configuration', 'reason' => 'dedicated_key_required' ) ), PHP_EOL;
	exit( 2 );
}

global $wpdb;
$table = toolkit_application_table_name();
$rows = $wpdb->get_results( "SELECT id, payload FROM {$table} ORDER BY id ASC" );
$stats = array( 'scanned' => 0, 'already_current' => 0, 'eligible' => 0, 'migrated' => 0, 'failed' => 0, 'conflicts' => 0 );

foreach ( $rows as $row ) {
	$stats['scanned']++;
	$data = json_decode( (string) $row->payload, true );
	if ( is_array( $data ) && 2 === (int) ( $data['v'] ?? 0 ) && $current['id'] === (string) ( $data['kid'] ?? '' ) ) {
		$stats['already_current']++;
		continue;
	}
	$payload = toolkit_application_decrypt_payload( $row->payload );
	if ( is_wp_error( $payload ) ) { $stats['failed']++; continue; }
	$encrypted = toolkit_application_encrypt_payload( $payload );
	$roundtrip = is_wp_error( $encrypted ) ? $encrypted : toolkit_application_decrypt_payload( $encrypted );
	if ( is_wp_error( $roundtrip ) || $roundtrip !== $payload ) { $stats['failed']++; continue; }
	$stats['eligible']++;
	if ( ! $execute ) continue;
	$updated = $wpdb->query( $wpdb->prepare(
		"UPDATE {$table} SET payload = %s, updated_at = %s WHERE id = %d AND payload = %s",
		$encrypted,
		current_time( 'mysql', true ),
		(int) $row->id,
		$row->payload
	) );
	if ( 1 === (int) $updated ) $stats['migrated']++; else $stats['conflicts']++;
}

$result = array( 'ok' => 0 === $stats['failed'] && 0 === $stats['conflicts'], 'mode' => $execute ? 'execute' : 'dry-run', 'key_id' => $current['id'], 'stats' => $stats );
echo wp_json_encode( $result ), PHP_EOL;
exit( $result['ok'] ? 0 : 3 );
