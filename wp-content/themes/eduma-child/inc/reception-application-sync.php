<?php
/** A minimal, authenticated read-only view for Reception's local mirror. */

function toolkit_reception_application_sync_enabled() {
	$enabled = defined( 'TOOLKIT_RECEPTION_APPLICATION_SYNC_ENABLED' )
		? true === TOOLKIT_RECEPTION_APPLICATION_SYNC_ENABLED
		: (bool) get_option( 'toolkit_reception_application_sync_enabled', false );
	return $enabled
		&& strlen( toolkit_reception_api_secret() ) >= 32;
}

function toolkit_reception_application_sync_permission( WP_REST_Request $request ) {
	if ( ! toolkit_reception_application_sync_enabled() ) {
		return new WP_Error( 'sync_unavailable', 'Application sync is unavailable.', array( 'status' => 503 ) );
	}
	$query = $request->get_query_params();
	$after = $query['after'] ?? '0';
	$limit = $query['limit'] ?? '50';
	if ( array_diff( array_keys( $query ), array( 'after', 'limit' ) )
		|| ! is_string( $after ) || ! ctype_digit( $after ) || strlen( $after ) > 15
		|| ! is_string( $limit ) || ! ctype_digit( $limit ) || (int) $limit < 1 || (int) $limit > 50 ) {
		return new WP_Error( 'sync_request', 'Invalid sync request.', array( 'status' => 400 ) );
	}
	$timestamp = $request->get_header( 'x_toolkit_timestamp' );
	$nonce = $request->get_header( 'x_toolkit_nonce' );
	$signature = $request->get_header( 'x_toolkit_signature' );
	if ( ! ctype_digit( (string) $timestamp ) || abs( time() - (int) $timestamp ) > 300
		|| ! preg_match( '/^[a-f0-9]{32}$/D', (string) $nonce )
		|| ! preg_match( '/^[a-f0-9]{64}$/D', (string) $signature ) ) {
		return new WP_Error( 'sync_auth', 'Sync authentication failed.', array( 'status' => 401 ) );
	}
	$canonical = $timestamp . "\n" . $nonce . "\napplications-read-v1\n" . (int) $after . "\n" . (int) $limit;
	if ( ! hash_equals( hash_hmac( 'sha256', $canonical, toolkit_reception_api_secret() ), $signature ) ) {
		return new WP_Error( 'sync_auth', 'Sync authentication failed.', array( 'status' => 401 ) );
	}
	global $wpdb;
	// add_option uses a unique database key, including without an object cache.
	$prefix = 'toolkit_reception_sync_nonce_';
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND CAST(option_value AS UNSIGNED) < %d LIMIT 100", $wpdb->esc_like( $prefix ) . '%', time() ) );
	$key = $prefix . hash_hmac( 'sha256', $nonce, toolkit_reception_api_secret() );
	if ( ! add_option( $key, time() + 600, '', false ) ) {
		return new WP_Error( 'sync_replay', 'Use a fresh sync request.', array( 'status' => 409 ) );
	}
	return true;
}

function toolkit_reception_application_sync_page( WP_REST_Request $request ) {
	global $wpdb;
	$after = (int) ( $request->get_param( 'after' ) ?? 0 );
	$limit = (int) ( $request->get_param( 'limit' ) ?? 50 );
	$table = toolkit_application_table_name();
	$records = $wpdb->get_results( $wpdb->prepare( "SELECT id,reference,status,workflow_status,payload,created_at,updated_at,relayed_at FROM {$table} WHERE id > %d ORDER BY id ASC LIMIT %d", $after, $limit + 1 ) );
	if ( null === $records || $wpdb->last_error ) {
		return new WP_Error( 'sync_storage', 'Application sync could not be read.', array( 'status' => 503 ) );
	}
	$has_more = count( $records ) > $limit;
	$rows = array();
	foreach ( array_slice( $records, 0, $limit ) as $record ) {
		$data = toolkit_application_decrypt_payload( $record->payload );
		if ( is_wp_error( $data ) ) {
			return new WP_Error( 'sync_storage', 'An application needs review on the website before sync can continue.', array( 'status' => 503 ) );
		}
		foreach ( array( 'first_name' => 120, 'middle_name' => 120, 'surname' => 120, 'email' => 254, 'primary_phone' => 64, 'course_name' => 240, 'intake_name' => 160, 'school_name' => 160 ) as $field => $maximum ) {
			$value = $data[ $field ] ?? '';
			if ( ! is_string( $value ) || mb_strlen( $value ) > $maximum ) {
				return new WP_Error( 'sync_storage', 'An application needs review on the website before sync can continue.', array( 'status' => 503 ) );
			}
		}
		$rows[] = array(
			'id' => (int) $record->id,
			'reference' => (string) $record->reference,
			'name' => trim( implode( ' ', array_filter( array( $data['first_name'] ?? '', $data['middle_name'] ?? '', $data['surname'] ?? '' ) ) ) ),
			'email' => (string) ( $data['email'] ?? '' ),
			'phone' => (string) ( $data['primary_phone'] ?? '' ),
			'course' => (string) ( ! empty( $data['course_name'] ) ? $data['course_name'] : 'Course ' . ( $data['course_id'] ?? '' ) ),
			'intake' => (string) ( $data['intake_name'] ?? '' ),
			'campus' => (string) ( $data['school_name'] ?? '' ),
			'delivery_status' => (string) $record->status,
			'delivery_label' => toolkit_application_status_label( $record->status ),
			'review_status' => (string) $record->workflow_status,
			'submitted_at' => str_replace( ' ', 'T', $record->created_at ) . 'Z',
			'updated_at' => str_replace( ' ', 'T', $record->updated_at ) . 'Z',
			'delivered_at' => $record->relayed_at ? str_replace( ' ', 'T', $record->relayed_at ) . 'Z' : null,
		);
	}
	$response = new WP_REST_Response( array(
		'version' => 1,
		'source' => toolkit_is_demo_environment() ? 'demo' : 'production',
		'applications' => $rows,
		'next_cursor' => $rows ? (int) end( $rows )['id'] : $after,
		'has_more' => $has_more,
	), 200 );
	$response->header( 'Cache-Control', 'no-store, private, max-age=0' );
	$response->header( 'X-Robots-Tag', 'noindex, nofollow, noarchive' );
	return $response;
}

add_action( 'rest_api_init', static function () {
	register_rest_route( 'toolkit/v1', '/reception/applications', array(
		'methods' => WP_REST_Server::READABLE,
		'permission_callback' => 'toolkit_reception_application_sync_permission',
		'callback' => 'toolkit_reception_application_sync_page',
	) );
} );
