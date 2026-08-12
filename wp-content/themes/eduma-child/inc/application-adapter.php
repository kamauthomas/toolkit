<?php
/**
 * Server-side adapter for Toolkit's public Mzizi application services.
 * Applicant data is validated, stored locally, then forwarded to Mzizi when
 * the approved direct integration is active.
 */

function toolkit_mzizi_application_url() {
	return 'https://toolkit.mzizi.co.ke/portal/OnlineApplicationForm.aspx?q=d0d6b2f229d733c1e3156244805125a2';
}

function toolkit_mzizi_submission_enabled() {
	return toolkit_mzizi_relay_enabled()
		&& toolkit_application_security_enabled()
		&& '' !== toolkit_application_turnstile_site_key()
		&& '' !== toolkit_application_turnstile_secret_key();
}

function toolkit_mzizi_relay_enabled() {
	if ( get_option( 'toolkit_application_settings_managed', false ) ) return '1' === (string) get_option( 'toolkit_mzizi_relay_enabled', '0' );
	if ( defined( 'TOOLKIT_MZIZI_SUBMISSION_ENABLED' ) ) return true === TOOLKIT_MZIZI_SUBMISSION_ENABLED;
	return '1' === (string) get_option( 'toolkit_mzizi_relay_enabled', '0' );
}

function toolkit_application_turnstile_site_key() {
	if ( get_option( 'toolkit_application_settings_managed', false ) ) return toolkit_application_security_option( 'site_key' );
	if ( defined( 'TOOLKIT_APPLICATION_TURNSTILE_SITE_KEY' ) ) return (string) TOOLKIT_APPLICATION_TURNSTILE_SITE_KEY;
	return toolkit_application_security_option( 'site_key' );
}

function toolkit_application_turnstile_secret_key() {
	if ( get_option( 'toolkit_application_settings_managed', false ) ) return toolkit_application_security_option( 'secret_key' );
	if ( defined( 'TOOLKIT_APPLICATION_TURNSTILE_SECRET_KEY' ) ) return (string) TOOLKIT_APPLICATION_TURNSTILE_SECRET_KEY;
	return toolkit_application_security_option( 'secret_key' );
}

function toolkit_application_security_enabled() {
	if ( get_option( 'toolkit_application_settings_managed', false ) ) return '1' === (string) get_option( 'toolkit_application_turnstile_enabled', '0' );
	if ( defined( 'TOOLKIT_APPLICATION_TURNSTILE_ENABLED' ) ) return true === TOOLKIT_APPLICATION_TURNSTILE_ENABLED;
	return '1' === (string) get_option( 'toolkit_application_turnstile_enabled', '0' );
}

function toolkit_application_security_option( $field ) {
	$stored = get_option( 'toolkit_application_security_keys', '' );
	if ( ! $stored ) return '';
	$values = toolkit_application_decrypt_payload( $stored );
	return is_wp_error( $values ) ? '' : (string) ( $values[ $field ] ?? '' );
}

function toolkit_application_security_source( $field ) {
	if ( get_option( 'toolkit_application_settings_managed', false ) ) return toolkit_application_security_option( $field ) ? 'Encrypted dashboard setting' : 'Not configured';
	$constant = 'site_key' === $field ? 'TOOLKIT_APPLICATION_TURNSTILE_SITE_KEY' : 'TOOLKIT_APPLICATION_TURNSTILE_SECRET_KEY';
	return defined( $constant ) ? 'Deployment override' : ( toolkit_application_security_option( $field ) ? 'Encrypted dashboard setting' : 'Not configured' );
}

function toolkit_application_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'toolkit_applications';
}

function toolkit_application_install_storage() {
	global $wpdb;
	$version = '1.2.0';
	if ( $version === get_option( 'toolkit_application_storage_version' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = toolkit_application_table_name();
	$charset = $wpdb->get_charset_collate();
	$sql     = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		reference varchar(32) NOT NULL,
		status varchar(32) NOT NULL DEFAULT 'received',
		workflow_status varchar(24) NOT NULL DEFAULT 'new',
		payload longtext NOT NULL,
		created_at datetime NOT NULL,
		updated_at datetime NOT NULL,
		relayed_at datetime DEFAULT NULL,
		relay_attempts smallint(5) unsigned NOT NULL DEFAULT 0,
		last_error text DEFAULT NULL,
		mzizi_message text DEFAULT NULL,
		fingerprint char(64) DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY reference (reference),
		KEY status (status),
		KEY workflow_status (workflow_status),
		KEY created_at (created_at),
		KEY fingerprint (fingerprint)
	) {$charset};";
	dbDelta( $sql );
	$events = $table . '_events';
	dbDelta( "CREATE TABLE {$events} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		application_id bigint(20) unsigned NOT NULL,
		event_type varchar(32) NOT NULL,
		actor_id bigint(20) unsigned DEFAULT NULL,
		message text DEFAULT NULL,
		created_at datetime NOT NULL,
		PRIMARY KEY (id),
		KEY application_id (application_id),
		KEY event_type (event_type),
		KEY created_at (created_at)
	) {$charset};" );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		update_option( 'toolkit_application_storage_version', $version, false );
	}
}

function toolkit_application_log_event( $application_id, $event_type, $message = '', $actor_id = null ) {
	global $wpdb;
	return $wpdb->insert( toolkit_application_table_name() . '_events', array(
		'application_id' => (int) $application_id,
		'event_type'     => sanitize_key( $event_type ),
		'actor_id'      => null === $actor_id ? null : (int) $actor_id,
		'message'       => sanitize_textarea_field( $message ),
		'created_at'    => current_time( 'mysql', true ),
	), array( '%d', '%s', '%d', '%s', '%s' ) );
}
add_action( 'after_switch_theme', 'toolkit_application_install_storage' );
add_action( 'init', 'toolkit_application_install_storage', 3 );

add_filter( 'cron_schedules', function( $schedules ) {
	$schedules['toolkit_five_minutes'] = array( 'interval' => 5 * MINUTE_IN_SECONDS, 'display' => 'Every five minutes' );
	return $schedules;
} );
add_action( 'init', function() {
	if ( ! wp_next_scheduled( 'toolkit_application_process_queue' ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'toolkit_five_minutes', 'toolkit_application_process_queue' );
	}
}, 20 );
add_action( 'toolkit_application_process_queue', function() {
	if ( ! toolkit_mzizi_relay_enabled() || ! defined( 'TOOLKIT_MZIZI_AUTOMATIC_RELEASE_ENABLED' ) || true !== TOOLKIT_MZIZI_AUTOMATIC_RELEASE_ENABLED ) return;
	global $wpdb;
	$ids = $wpdb->get_col( 'SELECT id FROM ' . toolkit_application_table_name() . " WHERE status = 'queued' AND relay_attempts = 0 ORDER BY created_at ASC LIMIT 5" );
	foreach ( $ids as $id ) toolkit_application_relay_record( (int) $id, 'automatic' );
} );

function toolkit_application_encrypt_payload( array $payload ) {
	if ( ! function_exists( 'openssl_encrypt' ) ) {
		return new WP_Error( 'application_encryption', 'Application storage encryption is unavailable.' );
	}
	$key       = hash( 'sha256', wp_salt( 'auth' ) . '|toolkit-applications', true );
	$iv        = random_bytes( 12 );
	$tag       = '';
	$plaintext = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	$cipher    = openssl_encrypt( $plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag );
	if ( false === $cipher ) {
		return new WP_Error( 'application_encryption', 'The application could not be encrypted for storage.' );
	}
	return wp_json_encode( array(
		'v'    => 1,
		'iv'   => base64_encode( $iv ),
		'tag'  => base64_encode( $tag ),
		'data' => base64_encode( $cipher ),
	) );
}

function toolkit_application_decrypt_payload( $envelope ) {
	$data = json_decode( (string) $envelope, true );
	if ( ! is_array( $data ) || 1 !== (int) ( $data['v'] ?? 0 ) || ! function_exists( 'openssl_decrypt' ) ) {
		return new WP_Error( 'application_decryption', 'The stored application format is not supported.' );
	}
	$cipher = base64_decode( (string) ( $data['data'] ?? '' ), true );
	$iv     = base64_decode( (string) ( $data['iv'] ?? '' ), true );
	$tag    = base64_decode( (string) ( $data['tag'] ?? '' ), true );
	if ( false === $cipher || false === $iv || false === $tag || 12 !== strlen( $iv ) || 16 !== strlen( $tag ) ) {
		return new WP_Error( 'application_decryption', 'The stored application is damaged or incomplete.' );
	}
	$key   = hash( 'sha256', wp_salt( 'auth' ) . '|toolkit-applications', true );
	$plain = openssl_decrypt(
		$cipher,
		'aes-256-gcm',
		$key,
		OPENSSL_RAW_DATA,
		$iv,
		$tag
	);
	$payload = false === $plain ? null : json_decode( $plain, true );
	return is_array( $payload ) ? $payload : new WP_Error( 'application_decryption', 'The stored application could not be decrypted.' );
}

function toolkit_application_sanitized_storage_payload( array $data ) {
	return array(
		'first_name'       => sanitize_text_field( $data['first_name'] ?? '' ),
		'middle_name'      => sanitize_text_field( $data['middle_name'] ?? '' ),
		'surname'          => sanitize_text_field( $data['surname'] ?? '' ),
		'gender'           => sanitize_key( $data['gender'] ?? '' ),
		'nationality'      => sanitize_text_field( $data['nationality'] ?? 'Kenya' ),
		'email'            => sanitize_email( $data['email'] ?? '' ),
		'county'           => strtoupper( sanitize_text_field( $data['county'] ?? '' ) ),
		'primary_phone'    => preg_replace( '/[^0-9+]/', '', (string) ( $data['primary_phone'] ?? '' ) ),
		'secondary_phone'  => preg_replace( '/[^0-9+]/', '', (string) ( $data['secondary_phone'] ?? '' ) ),
		'school_id'        => preg_replace( '/\D/', '', (string) ( $data['school_id'] ?? '' ) ),
		'school_name'      => sanitize_text_field( $data['school_name'] ?? '' ),
		'course_id'        => preg_replace( '/\D/', '', (string) ( $data['course_id'] ?? '' ) ),
		'course_name'      => sanitize_text_field( $data['course_name'] ?? '' ),
		'intake_id'        => preg_replace( '/\D/', '', (string) ( $data['intake_id'] ?? '' ) ),
		'intake_name'      => sanitize_text_field( $data['intake_name'] ?? '' ),
		'study_mode'       => sanitize_text_field( $data['study_mode'] ?? '' ),
		'sponsorship_type' => sanitize_text_field( $data['sponsorship_type'] ?? '' ),
		'referral_source'  => sanitize_text_field( $data['referral_source'] ?? '' ),
		'mean_grade'       => sanitize_text_field( $data['mean_grade'] ?? '' ),
		'high_school'      => sanitize_text_field( $data['high_school'] ?? '' ),
		'qualifications'   => sanitize_textarea_field( $data['qualifications'] ?? '' ),
		'consent_at'       => gmdate( 'c' ),
		'source_page'      => home_url( '/apply/' ),
	);
}

function toolkit_application_store( array $data ) {
	global $wpdb;
	toolkit_application_install_storage();
	$encrypted = toolkit_application_encrypt_payload( toolkit_application_sanitized_storage_payload( $data ) );
	if ( is_wp_error( $encrypted ) ) {
		return $encrypted;
	}
	$table = toolkit_application_table_name();
	$now   = current_time( 'mysql', true );
	$fingerprint = hash_hmac( 'sha256', strtolower( sanitize_email( $data['email'] ?? '' ) ) . '|' . preg_replace( '/\D/', '', (string) ( $data['primary_phone'] ?? '' ) ) . '|' . preg_replace( '/\D/', '', (string) ( $data['course_id'] ?? '' ) ), wp_salt( 'nonce' ) );
	$existing = $wpdb->get_row( $wpdb->prepare( "SELECT id, reference, status, created_at FROM {$table} WHERE fingerprint = %s ORDER BY id DESC LIMIT 1", $fingerprint ) );
	if ( $existing && strtotime( current_time( 'mysql', true ) ) - strtotime( $existing->created_at ?? $now ) < 30 * DAY_IN_SECONDS ) {
		return new WP_Error( 'duplicate_application', 'An application with these contact and course details already exists as ' . $existing->reference . '.', array( 'status' => 409, 'reference' => $existing->reference ) );
	}
	for ( $attempt = 0; $attempt < 3; $attempt++ ) {
		$reference = 'TTI-' . gmdate( 'Ymd' ) . '-' . strtoupper( wp_generate_password( 6, false, false ) );
		$inserted  = $wpdb->insert( $table, array(
			'reference'  => $reference,
			'status'     => 'received',
			'payload'    => $encrypted,
			'created_at' => $now,
			'updated_at' => $now,
			'fingerprint'=> $fingerprint,
		), array( '%s', '%s', '%s', '%s', '%s', '%s' ) );
		if ( false !== $inserted ) {
			return array( 'id' => (int) $wpdb->insert_id, 'reference' => $reference );
		}
	}
	return new WP_Error( 'application_storage', 'The application could not be stored safely.', array( 'status' => 500 ) );
}

function toolkit_application_update_record( $id, array $values ) {
	global $wpdb;
	$allowed = array( 'status', 'relayed_at', 'relay_attempts', 'last_error', 'mzizi_message' );
	$update  = array( 'updated_at' => current_time( 'mysql', true ) );
	foreach ( $values as $key => $value ) {
		if ( in_array( $key, $allowed, true ) ) {
			$update[ $key ] = $value;
		}
	}
	return $wpdb->update( toolkit_application_table_name(), $update, array( 'id' => (int) $id ) );
}

function toolkit_application_update_payload_names( $record, array $names ) {
	global $wpdb;
	$payload = toolkit_application_decrypt_payload( $record->payload );
	if ( is_wp_error( $payload ) ) return $payload;
	foreach ( array( 'school_name', 'course_name', 'intake_name' ) as $key ) {
		if ( isset( $names[ $key ] ) && '' !== $names[ $key ] ) $payload[ $key ] = sanitize_text_field( $names[ $key ] );
	}
	$encrypted = toolkit_application_encrypt_payload( $payload );
	if ( is_wp_error( $encrypted ) ) return $encrypted;
	$wpdb->update( toolkit_application_table_name(), array( 'payload' => $encrypted, 'updated_at' => current_time( 'mysql', true ) ), array( 'id' => (int) $record->id ), array( '%s', '%s' ), array( '%d' ) );
	return true;
}

add_action( 'rest_api_init', function() {
	register_rest_route( 'toolkit/v1', '/application/options', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'toolkit_application_options',
		'permission_callback' => '__return_true',
	) );
	register_rest_route( 'toolkit/v1', '/application/courses', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'toolkit_application_courses',
		'permission_callback' => '__return_true',
	) );
	register_rest_route( 'toolkit/v1', '/application/intakes', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'toolkit_application_intakes',
		'permission_callback' => '__return_true',
	) );
	register_rest_route( 'toolkit/v1', '/application/submit', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'toolkit_application_submit',
		'permission_callback' => '__return_true',
	) );
} );

function toolkit_application_request_guard( WP_REST_Request $request, $limit = 30 ) {
	$origin = $request->get_header( 'origin' );
	if ( $origin && untrailingslashit( $origin ) !== untrailingslashit( home_url() ) ) {
		return new WP_Error( 'invalid_origin', 'The application request could not be verified.', array( 'status' => 403 ) );
	}

	$nonce = $request->get_header( 'x_wp_nonce' );
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'Please refresh the application and try again.', array( 'status' => 403 ) );
	}

	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$bucket   = sanitize_key( basename( $request->get_route() ) );
	$rate_key = 'toolkit_application_' . $bucket . '_' . substr( wp_hash( $ip ), 0, 20 );
	$attempts = (int) get_transient( $rate_key );
	if ( $attempts >= $limit ) {
		return new WP_Error( 'rate_limited', 'Too many application requests. Please wait before trying again.', array( 'status' => 429 ) );
	}
	set_transient( $rate_key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
	return true;
}

function toolkit_mzizi_session() {
	$response = wp_safe_remote_get( toolkit_mzizi_application_url(), array(
		'timeout'     => 8,
		'redirection' => 2,
		'user-agent'  => 'ToolkitAfrica-Admissions/1.0',
	) );
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return new WP_Error( 'mzizi_unavailable', 'Admissions options are temporarily unavailable.', array( 'status' => 503 ) );
	}
	$cookies = wp_remote_retrieve_cookies( $response );
	return $cookies ? $cookies : new WP_Error( 'mzizi_session', 'The admissions session could not be initialized.', array( 'status' => 503 ) );
}

function toolkit_mzizi_post( $service, $payload, $cookies, $timeout = 8 ) {
	$url = 'https://toolkit.mzizi.co.ke/PortalWebServices/' . ltrim( $service, '/' );
	$response = wp_safe_remote_post( $url, array(
		'timeout'     => max( 3, min( 30, (int) $timeout ) ),
		'redirection' => 0,
		'cookies'     => $cookies,
		'headers'     => array( 'Content-Type' => 'application/json' ),
		'body'        => wp_json_encode( (object) $payload ),
		'user-agent'  => 'ToolkitAfrica-Admissions/1.0',
	) );
	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'mzizi_unavailable', 'The admissions service is temporarily unavailable: ' . sanitize_text_field( $response->get_error_message() ), array( 'status' => 503, 'service' => sanitize_text_field( $service ) ) );
	}
	$status = wp_remote_retrieve_response_code( $response );
	$body   = wp_remote_retrieve_body( $response );
	$data   = json_decode( $body, true );
	/* GetSchools currently appends an ASP.NET {"d":null} envelope to its array. */
	if ( null === $data && preg_match( '/^(\[.*\])\s*\{"d":null\}\s*$/s', $body, $matches ) ) {
		$data = json_decode( $matches[1], true );
	}
	if ( $status < 200 || $status >= 300 || null === $data ) {
		$upstream_message = '';
		if ( is_array( $data ) ) $upstream_message = sanitize_text_field( $data['Message'] ?? $data['message'] ?? '' );
		if ( ! $upstream_message && is_string( $body ) && preg_match( '/"Message"\s*:\s*"([^"]{1,300})"/i', $body, $message_match ) ) $upstream_message = sanitize_text_field( $message_match[1] );
		$message = 'Mzizi ' . sanitize_text_field( $service ) . ' returned HTTP ' . (int) $status . ( $upstream_message ? ': ' . $upstream_message : ' with an unreadable response.' );
		return new WP_Error( 'mzizi_response', $message, array( 'status' => 502, 'upstream_status' => (int) $status, 'service' => sanitize_text_field( $service ) ) );
	}
	return $data;
}

function toolkit_application_public_items( $items, $label_key = 'Name' ) {
	if ( ! is_array( $items ) ) {
		return array();
	}
	$output = array();
	foreach ( $items as $item ) {
		if ( ! is_array( $item ) || empty( $item['ID'] ) || empty( $item[ $label_key ] ) || '0' === (string) $item['ID'] ) {
			continue;
		}
		$output[] = array(
			'id'    => sanitize_text_field( (string) $item['ID'] ),
			'label' => sanitize_text_field( (string) $item[ $label_key ] ),
		);
	}
	return $output;
}

function toolkit_application_source_items( $items ) {
	$sources = toolkit_application_public_items( $items );
	return array_map( function( $source ) {
		/* Mzizi's live form submits the source name, not its numeric ID. */
		return array( 'id' => $source['label'], 'label' => $source['label'] );
	}, $sources );
}

function toolkit_application_counties() {
	$names = array( 'BARINGO', 'BOMET', 'BUNGOMA', 'BUSIA', 'ELGEYO-MARAKWET', 'EMBU', 'GARISSA', 'HOMA BAY', 'ISIOLO', 'KAJIADO', 'KAKAMEGA', 'KERICHO', 'KIAMBU', 'KILIFI', 'KIRINYAGA', 'KISII', 'KISUMU', 'KITUI', 'KWALE', 'LAIKIPIA', 'LAMU', 'MACHAKOS', 'MAKUENI', 'MANDERA', 'MARSABIT', 'MERU', 'MIGORI', 'MOMBASA', "MURANG'A", 'NAIROBI', 'NAKURU', 'NANDI', 'NAROK', 'NYAMIRA', 'NYANDARUA', 'NYERI', 'SAMBURU', 'SIAYA', 'TAITA-TAVETA', 'TANA RIVER', 'THARAKA-NITHI', 'TRANS-NZOIA', 'TURKANA', 'UASIN GISHU', 'VIHIGA', 'WAJIR', 'WEST POKOT' );
	return array_map( function( $name ) {
		return array( 'id' => $name, 'label' => $name );
	}, $names );
}

/**
 * Prefer course mappings. Mzizi also publishes current-year intakes for the
 * cases where a current course has not yet been mapped to an intake row.
 */
function toolkit_application_available_intakes( $course_id, $cookies ) {
	$mapped = toolkit_mzizi_post( 'OrganizationProcesses.asmx/GetCourseIntakeMonths', array( 'CourseID' => $course_id ), $cookies );
	if ( is_wp_error( $mapped ) ) return $mapped;
	if ( toolkit_application_public_items( $mapped, 'LevelName' ) ) {
		return array( 'items' => $mapped, 'source' => 'course' );
	}
	$current = toolkit_mzizi_post( 'OrganizationProcesses.asmx/GetCurrentYearIntakeMonths', array(), $cookies );
	return is_wp_error( $current ) ? $current : array( 'items' => $current, 'source' => 'current_year' );
}

function toolkit_application_options( WP_REST_Request $request ) {
	$guard = toolkit_application_request_guard( $request );
	if ( is_wp_error( $guard ) ) {
		return $guard;
	}
	$cookies = toolkit_mzizi_session();
	if ( is_wp_error( $cookies ) ) {
		return $cookies;
	}
	$schools  = toolkit_mzizi_post( 'StudentInfo.asmx/GetSchools', array(), $cookies );
	$sources  = toolkit_mzizi_post( 'StudentInfo.asmx/GetCustomerSources', array(), $cookies );
	if ( is_wp_error( $schools ) || is_wp_error( $sources ) ) {
		return new WP_Error( 'mzizi_options', 'Admissions options are temporarily unavailable.', array( 'status' => 503 ) );
	}
	return rest_ensure_response( array(
		'schools'  => toolkit_application_public_items( $schools ),
		'counties' => toolkit_application_counties(),
		'sources'  => toolkit_application_source_items( $sources ),
	) );
}

function toolkit_application_courses( WP_REST_Request $request ) {
	$guard = toolkit_application_request_guard( $request );
	if ( is_wp_error( $guard ) ) {
		return $guard;
	}
	$school_id = preg_replace( '/\D/', '', (string) $request->get_param( 'school_id' ) );
	if ( ! $school_id ) {
		return new WP_Error( 'invalid_school', 'Select a valid campus.', array( 'status' => 422 ) );
	}
	$cookies = toolkit_mzizi_session();
	if ( is_wp_error( $cookies ) ) {
		return $cookies;
	}
	$set = toolkit_mzizi_post( 'StudentInfo.asmx/SetApplicationSchoolIDParam', array( 'SchoolID' => $school_id ), $cookies );
	if ( is_wp_error( $set ) || empty( $set['Success'] ) ) {
		return new WP_Error( 'invalid_school', 'Courses could not be loaded for that campus.', array( 'status' => 502 ) );
	}
	$courses = toolkit_mzizi_post( 'StudentInfo.asmx/GetApplicationCoursesNoAlumni', array(), $cookies );
	return is_wp_error( $courses ) ? $courses : rest_ensure_response( array( 'courses' => toolkit_application_public_items( $courses, 'Description' ) ) );
}

function toolkit_application_intakes( WP_REST_Request $request ) {
	$guard = toolkit_application_request_guard( $request );
	if ( is_wp_error( $guard ) ) {
		return $guard;
	}
	$course_id = preg_replace( '/\D/', '', (string) $request->get_param( 'course_id' ) );
	if ( ! $course_id ) {
		return new WP_Error( 'invalid_course', 'Select a valid course.', array( 'status' => 422 ) );
	}
	$cookies = toolkit_mzizi_session();
	if ( is_wp_error( $cookies ) ) {
		return $cookies;
	}
	$intakes = toolkit_application_available_intakes( $course_id, $cookies );
	return is_wp_error( $intakes ) ? $intakes : rest_ensure_response( array(
		'intakes' => toolkit_application_public_items( $intakes['items'], 'LevelName' ),
		'source'  => $intakes['source'],
	) );
}

function toolkit_application_verify_turnstile( $token ) {
	$secret = toolkit_application_turnstile_secret_key();
	if ( ! $secret || ! $token ) {
		return false;
	}
	$response = wp_safe_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
		'timeout' => 8,
		'body'    => array(
			'secret'   => $secret,
			'response' => $token,
			'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
		),
	) );
	$data = is_wp_error( $response ) ? null : json_decode( wp_remote_retrieve_body( $response ), true );
	return is_array( $data ) && ! empty( $data['success'] );
}

function toolkit_application_submit( WP_REST_Request $request ) {
	$guard = toolkit_application_request_guard( $request, 5 );
	if ( is_wp_error( $guard ) ) {
		return $guard;
	}
	$data = (array) $request->get_json_params();
	if ( ! empty( $data['website'] ) ) {
		return new WP_Error( 'invalid_submission', 'The application could not be verified.', array( 'status' => 400 ) );
	}
	$direct_enabled = toolkit_mzizi_submission_enabled();
	if ( $direct_enabled && ! toolkit_application_verify_turnstile( isset( $data['cf-turnstile-response'] ) ? $data['cf-turnstile-response'] : '' ) ) {
		return new WP_Error( 'captcha_failed', 'Complete the security check and try again.', array( 'status' => 422 ) );
	}

	$required = array( 'first_name', 'surname', 'email', 'primary_phone', 'secondary_phone', 'school_id', 'course_id', 'intake_id', 'county', 'gender', 'consent' );
	foreach ( $required as $field ) {
		if ( empty( $data[ $field ] ) ) {
			return new WP_Error( 'missing_field', 'Please complete all required fields.', array( 'status' => 422 ) );
		}
	}
	if ( ! is_email( $data['email'] ) ) {
		return new WP_Error( 'invalid_email', 'Enter a valid email address.', array( 'status' => 422 ) );
	}
	if ( ! in_array( (string) $data['gender'], array( 'F', 'M', 'I' ), true ) ) {
		return new WP_Error( 'invalid_gender', 'Select a valid gender.', array( 'status' => 422 ) );
	}
	if ( '1' !== (string) $data['consent'] ) {
		return new WP_Error( 'invalid_consent', 'Confirm the application consent before submitting.', array( 'status' => 422 ) );
	}
	$study_mode = isset( $data['study_mode'] ) ? sanitize_text_field( $data['study_mode'] ) : '';
	if ( ! in_array( $study_mode, array( '', 'Online', 'Physical' ), true ) ) {
		return new WP_Error( 'invalid_study_mode', 'Select a valid mode of study.', array( 'status' => 422 ) );
	}
	$sponsorship_type = isset( $data['sponsorship_type'] ) ? sanitize_text_field( $data['sponsorship_type'] ) : '';
	if ( ! in_array( $sponsorship_type, array( '', 'Sponsored', 'Self-Sponsored' ), true ) ) {
		return new WP_Error( 'invalid_sponsorship', 'Select a valid fee-payment option.', array( 'status' => 422 ) );
	}
	if ( ! in_array( strtoupper( sanitize_text_field( $data['county'] ) ), array_column( toolkit_application_counties(), 'id' ), true ) ) {
		return new WP_Error( 'invalid_county', 'Select a valid county.', array( 'status' => 422 ) );
	}
	foreach ( array( 'primary_phone', 'secondary_phone' ) as $phone_field ) {
		if ( ! preg_match( '/^\+?[0-9]{9,15}$/', preg_replace( '/[^0-9+]/', '', (string) $data[ $phone_field ] ) ) ) {
			return new WP_Error( 'invalid_phone', 'Enter valid primary and secondary phone numbers.', array( 'status' => 422 ) );
		}
	}
	$stored = toolkit_application_store( $data );
	if ( is_wp_error( $stored ) ) {
		return $stored;
	}
	$record_id = $stored['id'];
	$reference = $stored['reference'];
	if ( ! $direct_enabled ) {
		toolkit_application_update_record( $record_id, array( 'status' => 'queued' ) );
		toolkit_application_log_event( $record_id, 'queued', 'Stored locally and queued for Mzizi release.' );
		return new WP_REST_Response( array(
			'code'        => 'queued',
			'message'     => 'Application ' . $reference . ' was submitted successfully. Toolkit Admissions will review it and contact you about the next step.',
			'reference'   => $reference,
			'delivery'    => 'queued',
		), 201 );
	}
	return toolkit_application_relay_record( $record_id, 'public' );
}

function toolkit_application_relay_record( $record_id, $release_source = 'manual' ) {
	global $wpdb;
	$record = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . toolkit_application_table_name() . ' WHERE id = %d', (int) $record_id ) );
	if ( ! $record ) return new WP_Error( 'application_missing', 'Application record not found.' );
	if ( 'delivered' === $record->status ) return new WP_Error( 'already_delivered', 'This application is already marked as delivered to Mzizi.' );
	if ( 'relaying' === $record->status && strtotime( $record->updated_at . ' UTC' ) > time() - 10 * MINUTE_IN_SECONDS ) {
		return new WP_Error( 'relay_in_progress', 'This application is already being released to Mzizi.' );
	}
	if ( ! toolkit_mzizi_relay_enabled() ) {
		return new WP_Error( 'relay_disabled', 'Mzizi relay is disabled in this environment.' );
	}
	if ( 'manual' === $release_source && empty( $_POST['duplicate_checked'] ) ) {
		return new WP_Error( 'duplicate_check_required', 'Confirm that this applicant is not already present in Mzizi before manual release.' );
	}
	$data = toolkit_application_decrypt_payload( $record->payload );
	if ( is_wp_error( $data ) ) return $data;
	$reference = $record->reference;

	$school_id = preg_replace( '/\D/', '', (string) $data['school_id'] );
	$course_id = preg_replace( '/\D/', '', (string) $data['course_id'] );
	$intake_id = preg_replace( '/\D/', '', (string) $data['intake_id'] );
	$cookies   = toolkit_mzizi_session();
	if ( is_wp_error( $cookies ) ) {
		toolkit_application_update_record( $record_id, array( 'status' => 'relay_failed', 'last_error' => $cookies->get_error_message(), 'relay_attempts' => (int) $record->relay_attempts + 1 ) );
		toolkit_application_log_event( $record_id, 'relay_failed', $cookies->get_error_message(), get_current_user_id() ?: null );
		return new WP_Error( $cookies->get_error_code(), $cookies->get_error_message(), array( 'status' => 503, 'reference' => $reference ) );
	}
	$set = toolkit_mzizi_post( 'StudentInfo.asmx/SetApplicationSchoolIDParam', array( 'SchoolID' => $school_id ), $cookies );
	if ( is_wp_error( $set ) || empty( $set['Success'] ) ) {
		toolkit_application_update_record( $record_id, array( 'status' => 'validation_failed', 'last_error' => 'Selected campus unavailable.' ) );
		return new WP_Error( 'invalid_school', 'The selected campus is no longer available. Your application was saved as ' . $reference . '.', array( 'status' => 422, 'reference' => $reference ) );
	}
	$courses = toolkit_mzizi_post( 'StudentInfo.asmx/GetApplicationCoursesNoAlumni', array(), $cookies );
	$intakes = toolkit_application_available_intakes( $course_id, $cookies );
	$sources = toolkit_mzizi_post( 'StudentInfo.asmx/GetCustomerSources', array(), $cookies );
	if ( is_wp_error( $courses ) || is_wp_error( $intakes ) || is_wp_error( $sources ) ) {
		toolkit_application_update_record( $record_id, array( 'status' => 'relay_failed', 'last_error' => 'Mzizi course availability could not be confirmed.' ) );
		return new WP_Error( 'mzizi_validation', 'Course availability could not be confirmed. Your application was saved as ' . $reference . '.', array( 'status' => 503, 'reference' => $reference ) );
	}
	$course = null;
	foreach ( $courses as $item ) {
		if ( isset( $item['ID'] ) && $course_id === (string) $item['ID'] ) {
			$course = $item;
			break;
		}
	}
	$intake = null;
	foreach ( $intakes['items'] as $item ) {
		if ( isset( $item['ID'] ) && $intake_id === (string) $item['ID'] ) {
			$intake = $item;
			break;
		}
	}
	if ( ! $course || ! $intake ) {
		toolkit_application_update_record( $record_id, array( 'status' => 'validation_failed', 'last_error' => 'Selected course or intake unavailable.' ) );
		return new WP_Error( 'stale_selection', 'The selected course or intake is no longer available. Your application was saved as ' . $reference . '.', array( 'status' => 422, 'reference' => $reference ) );
	}
	toolkit_application_update_payload_names( $record, array(
		'course_name' => $course['Name'] ?? $course['Description'] ?? '',
		'intake_name' => $intake['LevelName'] ?? '',
	) );
	$source_names    = array_column( toolkit_application_source_items( $sources ), 'id' );
	$referral_source = isset( $data['referral_source'] ) ? sanitize_text_field( $data['referral_source'] ) : '';
	if ( $referral_source && ! in_array( $referral_source, $source_names, true ) ) {
		toolkit_application_update_record( $record_id, array( 'status' => 'validation_failed', 'last_error' => 'Referral source unavailable.' ) );
		return new WP_Error( 'invalid_source', 'The selected referral source is no longer available. Your application was saved as ' . $reference . '.', array( 'status' => 422, 'reference' => $reference ) );
	}

	$payload = array(
		'FirstName'          => sanitize_text_field( $data['first_name'] ),
		'SecondName'         => sanitize_text_field( isset( $data['middle_name'] ) ? $data['middle_name'] : '' ),
		'LastName'           => sanitize_text_field( $data['surname'] ),
		'Nationality'        => sanitize_text_field( isset( $data['nationality'] ) ? $data['nationality'] : 'Kenya' ),
		'County'             => sanitize_text_field( $data['county'] ),
		'Email'              => sanitize_email( $data['email'] ),
		'MobileNo'           => preg_replace( '/[^0-9+]/', '', (string) $data['primary_phone'] ),
		'altPhoneno'         => preg_replace( '/[^0-9+]/', '', (string) $data['secondary_phone'] ),
		'MeanGrade'          => sanitize_text_field( isset( $data['mean_grade'] ) ? $data['mean_grade'] : '' ),
		'Qualifications'     => sanitize_textarea_field( isset( $data['qualifications'] ) ? $data['qualifications'] : '' ),
		'SchoolID'           => $school_id,
		'HighSchoolAttended' => sanitize_text_field( isset( $data['high_school'] ) ? $data['high_school'] : '' ),
		'IntakeMonth'        => sanitize_text_field( $intake['LevelName'] ),
		'ModeOfStudy'        => $study_mode,
		'SponsorshipType'    => $sponsorship_type,
		'Channel'            => $referral_source,
		'ClassApplied'       => sanitize_text_field( $course['Name'] ?? $course['Description'] ?? '' ),
		'Gender'             => sanitize_key( $data['gender'] ),
	);
	$attempt = (int) $record->relay_attempts + 1;
	toolkit_application_update_record( $record_id, array( 'status' => 'relaying', 'relay_attempts' => $attempt, 'last_error' => null ) );
	toolkit_application_log_event( $record_id, 'relay_started', ucfirst( $release_source ) . ' Mzizi release attempt ' . $attempt . '.', get_current_user_id() ?: null );
	/* Mzizi submission performs more work than its lookup endpoints. */
	$result = toolkit_mzizi_post( 'StudentInfo.asmx/SubmitOnlineApplication', $payload, $cookies, 25 );
	if ( is_wp_error( $result ) || empty( $result['Message'] ) ) {
		$error = is_wp_error( $result ) ? $result->get_error_message() : 'Mzizi returned no confirmation message.';
		toolkit_application_update_record( $record_id, array( 'status' => 'delivery_unconfirmed', 'last_error' => $error ) );
		toolkit_application_log_event( $record_id, 'delivery_unconfirmed', 'Mzizi submission was sent, but its confirmation could not be verified. Do not retry before checking Mzizi. ' . $error, get_current_user_id() ?: null );
		return new WP_Error( 'delivery_unconfirmed', 'Your application was received by Toolkit as ' . $reference . ' and is being confirmed by Admissions. Please do not submit it again.', array( 'status' => 202, 'reference' => $reference ) );
	}
	$message = sanitize_text_field( $result['Message'] );
	toolkit_application_update_record( $record_id, array( 'status' => 'delivered', 'relayed_at' => current_time( 'mysql', true ), 'mzizi_message' => $message, 'last_error' => null ) );
	toolkit_application_log_event( $record_id, 'delivered', $message, get_current_user_id() ?: null );
	return new WP_REST_Response( array( 'message' => $message, 'reference' => $reference, 'delivery' => 'delivered' ), 201 );
}

function toolkit_application_resolve_names( $record_id ) {
	global $wpdb;
	$record = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . toolkit_application_table_name() . ' WHERE id = %d', (int) $record_id ) );
	if ( ! $record ) return new WP_Error( 'application_missing', 'Application record not found.' );
	$data = toolkit_application_decrypt_payload( $record->payload );
	if ( is_wp_error( $data ) ) return $data;
	$cookies = toolkit_mzizi_session();
	if ( is_wp_error( $cookies ) ) return $cookies;
	$set = toolkit_mzizi_post( 'StudentInfo.asmx/SetApplicationSchoolIDParam', array( 'SchoolID' => $data['school_id'] ), $cookies );
	if ( is_wp_error( $set ) || empty( $set['Success'] ) ) return new WP_Error( 'school_lookup', 'Mzizi could not resolve the stored campus.' );
	$courses = toolkit_mzizi_post( 'StudentInfo.asmx/GetApplicationCoursesNoAlumni', array(), $cookies );
	$intakes = toolkit_application_available_intakes( $data['course_id'], $cookies );
	if ( is_wp_error( $courses ) || is_wp_error( $intakes ) ) return new WP_Error( 'name_lookup', 'Mzizi could not resolve the stored course and intake.' );
	$course_name = '';
	foreach ( $courses as $course ) if ( (string) ( $course['ID'] ?? '' ) === (string) $data['course_id'] ) $course_name = $course['Name'] ?? $course['Description'] ?? '';
	$intake_name = '';
	foreach ( $intakes['items'] as $intake ) if ( (string) ( $intake['ID'] ?? '' ) === (string) $data['intake_id'] ) $intake_name = $intake['LevelName'] ?? '';
	if ( ! $course_name ) return new WP_Error( 'course_lookup', 'The stored course ID is no longer present in the selected Mzizi campus catalogue.' );
	$result = toolkit_application_update_payload_names( $record, array( 'course_name' => $course_name, 'intake_name' => $intake_name ) );
	if ( ! is_wp_error( $result ) ) toolkit_application_log_event( $record_id, 'names_resolved', 'Course and intake labels reconciled from Mzizi.', get_current_user_id() ?: null );
	return $result;
}

add_action( 'admin_menu', function() {
	add_submenu_page( 'toolkit-control', 'Applications', 'Applications', 'manage_options', 'toolkit-applications', 'toolkit_application_render_admin_page' );
	add_submenu_page( 'toolkit-control', 'Admissions settings', 'Admissions settings', 'manage_options', 'toolkit-admissions-settings', 'toolkit_application_render_settings_page' );
}, 20 );

add_action( 'admin_post_toolkit_application_save_settings', function() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You do not have permission to update admissions security.' );
	check_admin_referer( 'toolkit_application_save_settings' );
	$current       = array( 'site_key' => toolkit_application_turnstile_site_key(), 'secret_key' => toolkit_application_turnstile_secret_key() );
	$site_input    = trim( sanitize_text_field( wp_unslash( $_POST['turnstile_site_key'] ?? '' ) ) );
	$site          = $current['site_key'] && toolkit_application_mask_key( $current['site_key'] ) === $site_input ? $current['site_key'] : $site_input;
	$secret_input  = trim( sanitize_text_field( wp_unslash( $_POST['turnstile_secret_key'] ?? '' ) ) );
	$secret        = '' === $secret_input ? $current['secret_key'] : $secret_input;
	if ( isset( $_POST['clear_keys'] ) ) $site = $secret = '';
	if ( ( $site && ! $secret ) || ( $secret && ! $site ) ) wp_die( 'Both the Turnstile site key and secret key are required.' );
	if ( $site || $secret ) {
		$encrypted = toolkit_application_encrypt_payload( array( 'site_key' => $site, 'secret_key' => $secret ) );
		if ( is_wp_error( $encrypted ) ) wp_die( esc_html( $encrypted->get_error_message() ) );
		update_option( 'toolkit_application_security_keys', $encrypted, false );
	} else {
		delete_option( 'toolkit_application_security_keys' );
	}
	update_option( 'toolkit_application_turnstile_enabled', isset( $_POST['turnstile_enabled'] ) ? '1' : '0', false );
	update_option( 'toolkit_mzizi_relay_enabled', isset( $_POST['mzizi_relay_enabled'] ) ? '1' : '0', false );
	update_option( 'toolkit_application_settings_managed', '1', false );
	update_option( 'toolkit_application_security_audit', array(
		'user_id' => get_current_user_id(), 'changed_at' => current_time( 'mysql', true ),
		'action' => isset( $_POST['clear_keys'] ) ? 'Keys cleared' : 'Admissions security updated',
	), false );
	wp_safe_redirect( add_query_arg( array( 'page' => 'toolkit-admissions-settings', 'updated' => 1 ), admin_url( 'admin.php' ) ) ); exit;
} );

function toolkit_application_mask_key( $key ) {
	$length = strlen( $key );
	return $length < 9 ? str_repeat( '•', $length ) : substr( $key, 0, 4 ) . str_repeat( '•', max( 8, $length - 8 ) ) . substr( $key, -4 );
}

function toolkit_application_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You do not have permission to view admissions security.' );
	$site = toolkit_application_turnstile_site_key(); $secret = toolkit_application_turnstile_secret_key();
	$audit = get_option( 'toolkit_application_security_audit', array() );
	toolkit_application_admin_header( 'Admissions settings', 'Control application security and Mzizi delivery without exposing credentials.' );
	if ( isset( $_GET['updated'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Admissions security settings were saved.</p></div>';
	echo '<section class="toolkit-security-overview">';
	printf( '<article><span>Public protection</span><strong>%s</strong><small>%s</small></article>', toolkit_application_security_enabled() && $site && $secret ? 'Active' : 'Not active', $site && $secret ? 'Key pair configured' : 'Production keys required' );
	printf( '<article><span>Mzizi manual release</span><strong>%s</strong><small>Dashboard controlled</small></article>', toolkit_mzizi_relay_enabled() ? 'Enabled' : 'Disabled' );
	printf( '<article><span>Public direct delivery</span><strong>%s</strong><small>Requires both controls and a valid key pair</small></article>', toolkit_mzizi_submission_enabled() ? 'Enabled' : 'Locally retained' );
	echo '</section><section class="toolkit-security-layout"><form class="toolkit-admin__panel toolkit-security-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="toolkit_application_save_settings">';
	wp_nonce_field( 'toolkit_application_save_settings' );
	echo '<div class="toolkit-admin__panel-heading"><div><small>Cloudflare Turnstile</small><h2>Bot protection keys</h2></div><span class="toolkit-health toolkit-health--good">Encrypted storage</span></div><p>Enter the production widget keys from Cloudflare. Existing secrets are never returned to the browser.</p>';
	echo '<label>Site key <span>' . esc_html( toolkit_application_security_source( 'site_key' ) ) . '</span><input type="text" name="turnstile_site_key" value="' . esc_attr( $site ? toolkit_application_mask_key( $site ) : '' ) . '" placeholder="Paste production site key"></label>';
	echo '<label>Secret key <span>' . esc_html( toolkit_application_security_source( 'secret_key' ) ) . '</span><input type="password" name="turnstile_secret_key" value="" placeholder="' . esc_attr( $secret ? 'Stored securely — enter only to replace' : 'Paste production secret key' ) . '" autocomplete="new-password"></label>';
	echo '<div class="toolkit-security-switches"><label><input type="checkbox" name="turnstile_enabled" value="1" ' . checked( toolkit_application_security_enabled(), true, false ) . '> <span><strong>Activate Turnstile</strong><small>Require a valid challenge before public direct delivery.</small></span></label><label><input type="checkbox" name="mzizi_relay_enabled" value="1" ' . checked( toolkit_mzizi_relay_enabled(), true, false ) . '> <span><strong>Allow Mzizi release</strong><small>Enables reviewed manual delivery; duplicate confirmation remains mandatory.</small></span></label></div>';
	echo '<div class="toolkit-security-actions"><button class="button button-primary button-hero" type="submit">Save security settings</button>'; if ( $site || $secret ) echo '<button class="button" type="submit" name="clear_keys" value="1" onclick="return confirm(\'Clear the stored Turnstile keys and disable public direct delivery?\');">Clear stored keys</button>'; echo '</div></form>';
	echo '<aside class="toolkit-admin__panel toolkit-security-guide"><h2>Activation gate</h2><ol><li>Create a Turnstile widget for <code>toolkitafrica.ac.ke</code> in Cloudflare.</li><li>Paste both production keys and save.</li><li>Open the public application in a private browser and complete the widget.</li><li>Only then activate Turnstile. Existing applications remain locally retained throughout.</li></ol><h3>Last configuration change</h3>';
	if ( $audit ) { $user = get_userdata( (int) ( $audit['user_id'] ?? 0 ) ); printf( '<p><strong>%s</strong><br>%s by %s</p>', esc_html( $audit['action'] ?? 'Updated' ), esc_html( get_date_from_gmt( $audit['changed_at'] ?? '', 'd M Y H:i' ) ), esc_html( $user ? $user->display_name : 'Administrator' ) ); } else echo '<p>No dashboard changes recorded yet.</p>';
	echo '<p class="description">Automatic historical release remains deployment-disabled. This prevents unreviewed duplicates from reaching Mzizi.</p></aside></section></div>';
}

add_action( 'admin_post_toolkit_application_workflow', function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to update applications.' );
	}
	$record_id = absint( $_GET['application'] ?? 0 );
	check_admin_referer( 'toolkit_application_workflow_' . $record_id );
	$workflow = sanitize_key( $_GET['workflow'] ?? '' );
	if ( in_array( $workflow, array( 'new', 'in_review', 'resolved' ), true ) ) {
		global $wpdb;
		$wpdb->update(
			toolkit_application_table_name(),
			array( 'workflow_status' => $workflow, 'updated_at' => current_time( 'mysql', true ) ),
			array( 'id' => $record_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
	}
	wp_safe_redirect( admin_url( 'admin.php?page=toolkit-applications&view=' . $record_id . '&updated=1' ) );
	exit;
} );

add_action( 'admin_post_toolkit_application_release', function() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You do not have permission to release applications.' );
	$record_id = absint( $_POST['application'] ?? 0 );
	check_admin_referer( 'toolkit_application_release_' . $record_id );
	$result = toolkit_application_relay_record( $record_id, 'manual' );
	$args = array( 'page' => 'toolkit-applications', 'view' => $record_id );
	if ( is_wp_error( $result ) ) {
		$args['release_error'] = rawurlencode( $result->get_error_message() );
	} else {
		$args['released'] = 1;
	}
	wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	exit;
} );

add_action( 'admin_post_toolkit_application_confirm_delivery', function() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You do not have permission to reconcile delivery.' );
	$record_id = absint( $_POST['application'] ?? 0 );
	check_admin_referer( 'toolkit_application_confirm_delivery_' . $record_id );
	if ( empty( $_POST['confirmed_in_mzizi'] ) ) wp_die( 'Confirm that the application is visible in Mzizi.' );
	global $wpdb;
	$record = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . toolkit_application_table_name() . ' WHERE id = %d', $record_id ) );
	if ( ! $record ) wp_die( 'Application record not found.' );
	if ( 'delivered' !== $record->status ) {
		$message = 'Delivery confirmed manually against Mzizi by an authorized administrator.';
		toolkit_application_update_record( $record_id, array( 'status' => 'delivered', 'relayed_at' => current_time( 'mysql', true ), 'mzizi_message' => $message, 'last_error' => null ) );
		toolkit_application_log_event( $record_id, 'delivery_reconciled', $message, get_current_user_id() );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'toolkit-applications', 'view' => $record_id, 'reconciled' => 1 ), admin_url( 'admin.php' ) ) ); exit;
} );

add_action( 'admin_post_toolkit_application_resolve_names', function() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You do not have permission to reconcile applications.' );
	$record_id = absint( $_POST['application'] ?? 0 );
	check_admin_referer( 'toolkit_application_resolve_names_' . $record_id );
	$result = toolkit_application_resolve_names( $record_id );
	$args = array( 'page' => 'toolkit-applications', 'view' => $record_id );
	$args[ is_wp_error( $result ) ? 'resolve_error' : 'resolved_names' ] = is_wp_error( $result ) ? rawurlencode( $result->get_error_message() ) : 1;
	wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) ); exit;
} );

function toolkit_application_status_label( $status ) {
	$labels = array(
		'received'          => 'Received',
		'handoff_required'  => 'Portal handoff',
		'queued'            => 'Queued for Mzizi',
		'relaying'          => 'Relaying',
		'delivered'         => 'Delivered to Mzizi',
		'relay_failed'      => 'Relay failed',
		'delivery_unconfirmed' => 'Delivery unconfirmed',
		'delivery_reconciled'  => 'Delivery reconciled',
		'validation_failed' => 'Validation review',
	);
	return $labels[ $status ] ?? ucwords( str_replace( '_', ' ', $status ) );
}

function toolkit_application_admin_header( $title, $description ) {
	printf( '<div class="wrap toolkit-admin toolkit-applications-admin"><header class="toolkit-admin__hero"><div><p>Admissions operations</p><h1>%s</h1><span>%s</span></div><a class="toolkit-admin__state" href="%s">Dashboard</a></header>', esc_html( $title ), esc_html( $description ), esc_url( admin_url( 'admin.php?page=toolkit-control' ) ) );
}

function toolkit_application_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to view applications.' );
	}
	global $wpdb;
	toolkit_application_install_storage();
	$table = toolkit_application_table_name();
	toolkit_application_admin_header( 'Applications', 'Encrypted local records and Mzizi delivery status.' );
	if ( isset( $_GET['updated'] ) ) {
		echo '<div class="notice notice-success is-dismissible"><p>Application workflow updated.</p></div>';
	}
	if ( isset( $_GET['released'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Mzizi confirmed delivery. The confirmation is recorded below.</p></div>';
	if ( isset( $_GET['reconciled'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Delivery was reconciled against Mzizi without resending the application.</p></div>';
	if ( isset( $_GET['release_error'] ) ) echo '<div class="notice notice-error"><p><strong>Mzizi release was not confirmed:</strong> ' . esc_html( wp_unslash( $_GET['release_error'] ) ) . '</p></div>';
	if ( isset( $_GET['resolved_names'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Course and intake names were reconciled from Mzizi.</p></div>';
	if ( isset( $_GET['resolve_error'] ) ) echo '<div class="notice notice-error"><p><strong>Name reconciliation failed:</strong> ' . esc_html( wp_unslash( $_GET['resolve_error'] ) ) . '</p></div>';
	$view_id = absint( $_GET['view'] ?? 0 );
	if ( $view_id ) {
		$record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $view_id ) );
		if ( $record ) {
			toolkit_application_render_record( $record );
		} else {
			echo '<div class="notice notice-error"><p>Application record not found.</p></div>';
		}
		echo '</div>';
		return;
	}

	$counts = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status", OBJECT_K );
	$total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	$new    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE workflow_status = 'new'" );
	echo '<section class="toolkit-admin__stats">';
	printf( '<span class="toolkit-admin__stat"><span>All applications</span><strong>%s</strong><small>Retained local records</small></span>', number_format_i18n( $total ) );
	printf( '<span class="toolkit-admin__stat"><span>New review</span><strong>%s</strong><small>Awaiting admissions action</small></span>', number_format_i18n( $new ) );
	printf( '<span class="toolkit-admin__stat"><span>Mzizi delivered</span><strong>%s</strong><small>Confirmed direct relay</small></span>', number_format_i18n( isset( $counts['delivered'] ) ? $counts['delivered']->total : 0 ) );
	printf( '<span class="toolkit-admin__stat"><span>Queued</span><strong>%s</strong><small>Not yet confirmed by Mzizi</small></span>', number_format_i18n( ( isset( $counts['queued'] ) ? $counts['queued']->total : 0 ) + ( isset( $counts['handoff_required'] ) ? $counts['handoff_required']->total : 0 ) ) );
	printf( '<span class="toolkit-admin__stat"><span>Needs confirmation</span><strong>%s</strong><small>Check Mzizi before any retry</small></span>', number_format_i18n( ( isset( $counts['delivery_unconfirmed'] ) ? $counts['delivery_unconfirmed']->total : 0 ) + ( isset( $counts['relay_failed'] ) ? $counts['relay_failed']->total : 0 ) + ( isset( $counts['validation_failed'] ) ? $counts['validation_failed']->total : 0 ) ) );
	echo '</section>';
	$status = sanitize_key( $_GET['status'] ?? '' );
	$search = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
	$page   = max( 1, absint( $_GET['paged'] ?? 1 ) );
	$limit  = 40;
	$where  = array( '1=1' );
	$args   = array();
	if ( $status ) {
		$where[] = 'status = %s';
		$args[]  = $status;
	}
	if ( $search ) {
		$where[] = 'reference LIKE %s';
		$args[]  = '%' . $wpdb->esc_like( $search ) . '%';
	}
	$sql = "SELECT * FROM {$table} WHERE " . implode( ' AND ', $where ) . ' ORDER BY created_at DESC LIMIT %d OFFSET %d';
	$args[] = $limit;
	$args[] = ( $page - 1 ) * $limit;
	$items  = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );

	echo '<form class="toolkit-admin__toolbar" method="get"><input type="hidden" name="page" value="toolkit-applications"><label>Delivery <select name="status"><option value="">All statuses</option>';
	foreach ( array( 'queued', 'delivered', 'delivery_unconfirmed', 'relaying', 'handoff_required', 'relay_failed', 'validation_failed', 'received' ) as $choice ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $choice ), selected( $status, $choice, false ), esc_html( toolkit_application_status_label( $choice ) ) );
	}
	echo '</select></label><label class="toolkit-admin__search">Reference <input name="s" value="' . esc_attr( $search ) . '" placeholder="TTI-YYYYMMDD"></label><button class="button button-primary">Filter</button></form>';
	echo '<div class="toolkit-admin__table-wrap"><table class="widefat striped toolkit-admin__table"><thead><tr><th>Received</th><th>Reference</th><th>Applicant</th><th>Course IDs</th><th>Delivery</th><th>Workflow</th><th></th></tr></thead><tbody>';
	foreach ( $items as $record ) {
		$payload = toolkit_application_decrypt_payload( $record->payload );
		$name    = is_wp_error( $payload ) ? 'Encrypted record' : trim( $payload['first_name'] . ' ' . $payload['surname'] );
		$email   = is_wp_error( $payload ) ? '' : $payload['email'];
		$course  = is_wp_error( $payload ) ? '—' : ( ! empty( $payload['course_name'] ) ? $payload['course_name'] : 'Course ID ' . $payload['course_id'] );
		printf( '<tr><td>%s</td><td><code>%s</code></td><td><strong>%s</strong><small>%s</small></td><td>%s</td><td><span class="toolkit-status toolkit-status--%s">%s</span></td><td>%s</td><td><a class="button button-small" href="%s">Open</a></td></tr>', esc_html( get_date_from_gmt( $record->created_at, 'd M Y H:i' ) ), esc_html( $record->reference ), esc_html( $name ), esc_html( $email ), esc_html( $course ), esc_attr( $record->status ), esc_html( toolkit_application_status_label( $record->status ) ), esc_html( ucwords( str_replace( '_', ' ', $record->workflow_status ) ) ), esc_url( admin_url( 'admin.php?page=toolkit-applications&view=' . $record->id ) ) );
	}
	if ( ! $items ) {
		echo '<tr><td colspan="7">No applications match this view.</td></tr>';
	}
	echo '</tbody></table></div></div>';
}

function toolkit_application_render_record( $record ) {
	$payload = toolkit_application_decrypt_payload( $record->payload );
	echo '<p><a class="button" href="' . esc_url( admin_url( 'admin.php?page=toolkit-applications' ) ) . '">← All applications</a></p>';
	if ( is_wp_error( $payload ) ) {
		echo '<div class="notice notice-error"><p>' . esc_html( $payload->get_error_message() ) . '</p></div>';
		return;
	}
	echo '<section class="toolkit-application-record"><div class="toolkit-admin__panel"><div class="toolkit-record__heading"><div><p>Toolkit reference</p><h2>' . esc_html( $record->reference ) . '</h2></div><span class="toolkit-status toolkit-status--' . esc_attr( $record->status ) . '">' . esc_html( toolkit_application_status_label( $record->status ) ) . '</span></div><dl class="toolkit-record__facts">';
	$fields = array(
		'Applicant'           => trim( $payload['first_name'] . ' ' . $payload['middle_name'] . ' ' . $payload['surname'] ),
		'Email'               => $payload['email'],
		'Primary phone'       => $payload['primary_phone'],
		'Secondary phone'     => $payload['secondary_phone'],
		'Gender'              => $payload['gender'],
		'Nationality'         => $payload['nationality'],
		'County'              => $payload['county'],
		'Campus ID'           => $payload['school_id'],
		'Campus'              => $payload['school_name'] ?? '',
		'Course ID'           => $payload['course_id'],
		'Course'              => $payload['course_name'] ?? '',
		'Intake ID'           => $payload['intake_id'],
		'Intake'              => $payload['intake_name'] ?? '',
		'Study mode'          => $payload['study_mode'],
		'Fee payment'         => $payload['sponsorship_type'],
		'Referral source'     => $payload['referral_source'],
		'KCSE mean grade'     => $payload['mean_grade'],
		'High school'         => $payload['high_school'],
		'Other qualifications'=> $payload['qualifications'],
		'Consent recorded'    => $payload['consent_at'],
	);
	foreach ( $fields as $label => $value ) {
		printf( '<div><dt>%s</dt><dd>%s</dd></div>', esc_html( $label ), nl2br( esc_html( $value ?: '—' ) ) );
	}
	echo '</dl></div><aside class="toolkit-admin__panel"><h2>Admissions workflow</h2><dl class="toolkit-admin__status">';
	printf( '<div><dt>Received</dt><dd>%s</dd></div>', esc_html( get_date_from_gmt( $record->created_at, 'd M Y H:i' ) ) );
	printf( '<div><dt>Delivery</dt><dd>%s</dd></div>', esc_html( toolkit_application_status_label( $record->status ) ) );
	printf( '<div><dt>Relay attempts</dt><dd>%s</dd></div>', number_format_i18n( $record->relay_attempts ) );
	printf( '<div><dt>Mzizi confirmation</dt><dd>%s</dd></div>', esc_html( $record->mzizi_message ?: '—' ) );
	printf( '<div><dt>Last error</dt><dd>%s</dd></div>', esc_html( $record->last_error ?: '—' ) );
	echo '</dl>';
	if ( empty( $payload['course_name'] ) || empty( $payload['intake_name'] ) ) {
		echo '<form class="toolkit-resolve" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="toolkit_application_resolve_names"><input type="hidden" name="application" value="' . absint( $record->id ) . '">';
		wp_nonce_field( 'toolkit_application_resolve_names_' . $record->id );
		echo '<button class="button" type="submit">Resolve course names</button><p class="description">Looks up the stored IDs in Mzizi and appends verified labels without changing the applicant’s original choices.</p></form>';
	}
	if ( in_array( $record->status, array( 'delivery_unconfirmed', 'relay_failed' ), true ) ) {
		echo '<form class="toolkit-reconcile" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="toolkit_application_confirm_delivery"><input type="hidden" name="application" value="' . absint( $record->id ) . '">';
		wp_nonce_field( 'toolkit_application_confirm_delivery_' . $record->id );
		echo '<label class="toolkit-release__check"><input type="checkbox" name="confirmed_in_mzizi" value="1" required> I verified this exact applicant and course are present in Mzizi.</label><button class="button button-primary" type="submit">Confirm delivered in Mzizi</button><p class="description">Updates Toolkit status only. It does not resend the application.</p></form>';
	}
	if ( ! in_array( $record->status, array( 'delivered', 'delivery_unconfirmed', 'relay_failed' ), true ) ) {
		echo '<form class="toolkit-release" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" onsubmit="return confirm(\'Release this application to Mzizi now? Confirm first that it is not already present in Mzizi.\');"><input type="hidden" name="action" value="toolkit_application_release"><input type="hidden" name="application" value="' . absint( $record->id ) . '">';
		wp_nonce_field( 'toolkit_application_release_' . $record->id );
		echo '<label class="toolkit-release__check"><input type="checkbox" name="duplicate_checked" value="1" required> I checked Mzizi using this applicant’s email and phone and found no existing application.</label><button class="button button-primary button-hero" type="submit"' . disabled( ! toolkit_mzizi_relay_enabled(), true, false ) . '>Release to Mzizi</button><p class="description">Manual release is recorded as a new attempt. Mzizi does not publish a scoped duplicate-search API, so this confirmation is mandatory.</p></form>';
	}
	$events = $GLOBALS['wpdb']->get_results( $GLOBALS['wpdb']->prepare( 'SELECT * FROM ' . toolkit_application_table_name() . '_events WHERE application_id = %d ORDER BY created_at DESC LIMIT 50', $record->id ) );
	if ( $events ) {
		echo '<h3>Delivery history</h3><ol class="toolkit-delivery-history">';
		foreach ( $events as $event ) printf( '<li><strong>%s</strong><span>%s</span><small>%s</small></li>', esc_html( toolkit_application_status_label( $event->event_type ) ), esc_html( $event->message ), esc_html( get_date_from_gmt( $event->created_at, 'd M Y H:i' ) ) );
		echo '</ol>';
	}
	echo '<h3>Review status</h3><div class="toolkit-record__actions">';
	foreach ( array( 'in_review' => 'Start review', 'resolved' => 'Mark resolved', 'new' => 'Reopen' ) as $workflow => $label ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=toolkit_application_workflow&application=' . $record->id . '&workflow=' . $workflow ), 'toolkit_application_workflow_' . $record->id );
		echo '<a class="button" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a> ';
	}
	echo '</div><p class="description">Applications remain retained locally. Only confirmed Mzizi responses are marked delivered.</p></aside></section>';
}
