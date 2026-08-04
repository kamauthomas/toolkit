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
	return defined( 'TOOLKIT_MZIZI_SUBMISSION_ENABLED' )
		&& true === TOOLKIT_MZIZI_SUBMISSION_ENABLED
		&& defined( 'TOOLKIT_APPLICATION_TURNSTILE_SITE_KEY' )
		&& '' !== TOOLKIT_APPLICATION_TURNSTILE_SITE_KEY
		&& defined( 'TOOLKIT_APPLICATION_TURNSTILE_SECRET_KEY' )
		&& '' !== TOOLKIT_APPLICATION_TURNSTILE_SECRET_KEY;
}

function toolkit_application_turnstile_site_key() {
	return defined( 'TOOLKIT_APPLICATION_TURNSTILE_SITE_KEY' ) ? (string) TOOLKIT_APPLICATION_TURNSTILE_SITE_KEY : '';
}

function toolkit_application_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'toolkit_applications';
}

function toolkit_application_install_storage() {
	global $wpdb;
	$version = '1.1.0';
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
		PRIMARY KEY  (id),
		UNIQUE KEY reference (reference),
		KEY status (status),
		KEY workflow_status (workflow_status),
		KEY created_at (created_at)
	) {$charset};";
	dbDelta( $sql );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		update_option( 'toolkit_application_storage_version', $version, false );
	}
}
add_action( 'after_switch_theme', 'toolkit_application_install_storage' );
add_action( 'init', 'toolkit_application_install_storage', 3 );

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
		'course_id'        => preg_replace( '/\D/', '', (string) ( $data['course_id'] ?? '' ) ),
		'intake_id'        => preg_replace( '/\D/', '', (string) ( $data['intake_id'] ?? '' ) ),
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
	for ( $attempt = 0; $attempt < 3; $attempt++ ) {
		$reference = 'TTI-' . gmdate( 'Ymd' ) . '-' . strtoupper( wp_generate_password( 6, false, false ) );
		$inserted  = $wpdb->insert( $table, array(
			'reference'  => $reference,
			'status'     => 'received',
			'payload'    => $encrypted,
			'created_at' => $now,
			'updated_at' => $now,
		), array( '%s', '%s', '%s', '%s', '%s' ) );
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

function toolkit_mzizi_post( $service, $payload, $cookies ) {
	$url = 'https://toolkit.mzizi.co.ke/PortalWebServices/' . ltrim( $service, '/' );
	$response = wp_safe_remote_post( $url, array(
		'timeout'     => 8,
		'redirection' => 0,
		'cookies'     => $cookies,
		'headers'     => array( 'Content-Type' => 'application/json' ),
		'body'        => wp_json_encode( (object) $payload ),
		'user-agent'  => 'ToolkitAfrica-Admissions/1.0',
	) );
	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'mzizi_unavailable', 'The admissions service is temporarily unavailable.', array( 'status' => 503 ) );
	}
	$status = wp_remote_retrieve_response_code( $response );
	$body   = wp_remote_retrieve_body( $response );
	$data   = json_decode( $body, true );
	/* GetSchools currently appends an ASP.NET {"d":null} envelope to its array. */
	if ( null === $data && preg_match( '/^(\[.*\])\s*\{"d":null\}\s*$/s', $body, $matches ) ) {
		$data = json_decode( $matches[1], true );
	}
	if ( $status < 200 || $status >= 300 || null === $data ) {
		return new WP_Error( 'mzizi_response', 'The admissions service returned an unexpected response.', array( 'status' => 502 ) );
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
	$intakes = toolkit_mzizi_post( 'OrganizationProcesses.asmx/GetCourseIntakeMonths', array( 'CourseID' => $course_id ), $cookies );
	return is_wp_error( $intakes ) ? $intakes : rest_ensure_response( array( 'intakes' => toolkit_application_public_items( $intakes, 'LevelName' ) ) );
}

function toolkit_application_verify_turnstile( $token ) {
	if ( ! defined( 'TOOLKIT_APPLICATION_TURNSTILE_SECRET_KEY' ) || ! $token ) {
		return false;
	}
	$response = wp_safe_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
		'timeout' => 8,
		'body'    => array(
			'secret'   => TOOLKIT_APPLICATION_TURNSTILE_SECRET_KEY,
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
		toolkit_application_update_record( $record_id, array( 'status' => 'handoff_required' ) );
		return new WP_REST_Response( array(
			'code'        => 'handoff_required',
			'message'     => 'Application ' . $reference . ' was saved securely. Continue to the official Mzizi portal to complete the admissions handoff.',
			'reference'   => $reference,
			'handoff_url' => toolkit_mzizi_application_url(),
		), 201 );
	}

	$school_id = preg_replace( '/\D/', '', (string) $data['school_id'] );
	$course_id = preg_replace( '/\D/', '', (string) $data['course_id'] );
	$intake_id = preg_replace( '/\D/', '', (string) $data['intake_id'] );
	$cookies   = toolkit_mzizi_session();
	if ( is_wp_error( $cookies ) ) {
		toolkit_application_update_record( $record_id, array( 'status' => 'relay_failed', 'last_error' => $cookies->get_error_message() ) );
		return new WP_Error( $cookies->get_error_code(), $cookies->get_error_message() . ' Your application was saved as ' . $reference . '.', array( 'status' => 503, 'reference' => $reference ) );
	}
	$set = toolkit_mzizi_post( 'StudentInfo.asmx/SetApplicationSchoolIDParam', array( 'SchoolID' => $school_id ), $cookies );
	if ( is_wp_error( $set ) || empty( $set['Success'] ) ) {
		toolkit_application_update_record( $record_id, array( 'status' => 'validation_failed', 'last_error' => 'Selected campus unavailable.' ) );
		return new WP_Error( 'invalid_school', 'The selected campus is no longer available. Your application was saved as ' . $reference . '.', array( 'status' => 422, 'reference' => $reference ) );
	}
	$courses = toolkit_mzizi_post( 'StudentInfo.asmx/GetApplicationCoursesNoAlumni', array(), $cookies );
	$intakes = toolkit_mzizi_post( 'OrganizationProcesses.asmx/GetCourseIntakeMonths', array( 'CourseID' => $course_id ), $cookies );
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
	foreach ( $intakes as $item ) {
		if ( isset( $item['ID'] ) && $intake_id === (string) $item['ID'] ) {
			$intake = $item;
			break;
		}
	}
	if ( ! $course || ! $intake ) {
		toolkit_application_update_record( $record_id, array( 'status' => 'validation_failed', 'last_error' => 'Selected course or intake unavailable.' ) );
		return new WP_Error( 'stale_selection', 'The selected course or intake is no longer available. Your application was saved as ' . $reference . '.', array( 'status' => 422, 'reference' => $reference ) );
	}
	$source_names = array_column( toolkit_application_source_items( $sources ), 'id' );
	$source       = isset( $data['referral_source'] ) ? sanitize_text_field( $data['referral_source'] ) : '';
	if ( $source && ! in_array( $source, $source_names, true ) ) {
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
		'Channel'            => $source,
		'ClassApplied'       => sanitize_text_field( $course['Name'] ?? $course['Description'] ?? '' ),
		'Gender'             => sanitize_key( $data['gender'] ),
	);
	toolkit_application_update_record( $record_id, array( 'status' => 'relaying', 'relay_attempts' => 1 ) );
	$result = toolkit_mzizi_post( 'StudentInfo.asmx/SubmitOnlineApplication', $payload, $cookies );
	if ( is_wp_error( $result ) || empty( $result['Message'] ) ) {
		$error = is_wp_error( $result ) ? $result->get_error_message() : 'Mzizi returned no confirmation message.';
		toolkit_application_update_record( $record_id, array( 'status' => 'relay_failed', 'last_error' => $error ) );
		return new WP_Error( 'submission_failed', 'Your application was saved as ' . $reference . ', but Mzizi did not confirm delivery. Admissions can follow it up without an automatic duplicate retry.', array( 'status' => 502, 'reference' => $reference ) );
	}
	$message = sanitize_text_field( $result['Message'] );
	toolkit_application_update_record( $record_id, array( 'status' => 'delivered', 'relayed_at' => current_time( 'mysql', true ), 'mzizi_message' => $message, 'last_error' => null ) );
	return new WP_REST_Response( array( 'message' => $message, 'reference' => $reference, 'delivery' => 'delivered' ), 201 );
}

add_action( 'admin_menu', function() {
	add_submenu_page( 'toolkit-control', 'Applications', 'Applications', 'manage_options', 'toolkit-applications', 'toolkit_application_render_admin_page' );
}, 20 );

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

function toolkit_application_status_label( $status ) {
	$labels = array(
		'received'          => 'Received',
		'handoff_required'  => 'Portal handoff',
		'relaying'          => 'Relaying',
		'delivered'         => 'Delivered to Mzizi',
		'relay_failed'      => 'Relay failed',
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
	printf( '<span class="toolkit-admin__stat"><span>Needs follow-up</span><strong>%s</strong><small>Handoff or failed relay</small></span>', number_format_i18n( ( isset( $counts['handoff_required'] ) ? $counts['handoff_required']->total : 0 ) + ( isset( $counts['relay_failed'] ) ? $counts['relay_failed']->total : 0 ) ) );
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
	foreach ( array( 'delivered', 'handoff_required', 'relay_failed', 'validation_failed', 'received' ) as $choice ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $choice ), selected( $status, $choice, false ), esc_html( toolkit_application_status_label( $choice ) ) );
	}
	echo '</select></label><label class="toolkit-admin__search">Reference <input name="s" value="' . esc_attr( $search ) . '" placeholder="TTI-YYYYMMDD"></label><button class="button button-primary">Filter</button></form>';
	echo '<div class="toolkit-admin__table-wrap"><table class="widefat striped toolkit-admin__table"><thead><tr><th>Received</th><th>Reference</th><th>Applicant</th><th>Course IDs</th><th>Delivery</th><th>Workflow</th><th></th></tr></thead><tbody>';
	foreach ( $items as $record ) {
		$payload = toolkit_application_decrypt_payload( $record->payload );
		$name    = is_wp_error( $payload ) ? 'Encrypted record' : trim( $payload['first_name'] . ' ' . $payload['surname'] );
		$email   = is_wp_error( $payload ) ? '' : $payload['email'];
		$course  = is_wp_error( $payload ) ? '—' : 'Campus ' . $payload['school_id'] . ' / Course ' . $payload['course_id'];
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
		'Course ID'           => $payload['course_id'],
		'Intake ID'           => $payload['intake_id'],
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
	echo '</dl><h3>Review status</h3><div class="toolkit-record__actions">';
	foreach ( array( 'in_review' => 'Start review', 'resolved' => 'Mark resolved', 'new' => 'Reopen' ) as $workflow => $label ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=toolkit_application_workflow&application=' . $record->id . '&workflow=' . $workflow ), 'toolkit_application_workflow_' . $record->id );
		echo '<a class="button" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a> ';
	}
	echo '</div><p class="description">Applications are retained locally. Relay failures are not retried automatically because Mzizi has no confirmed idempotency key.</p></aside></section>';
}
