<?php
/** Durable relay scheduling. Only failures before the submission boundary are retryable. */

function toolkit_application_relay_error_summary( $error, $phase ) {
	$data = is_wp_error( $error ) ? (array) $error->get_error_data() : array();
	$http = (int) ( $data['upstream_status'] ?? 0 );
	$code = is_wp_error( $error ) ? sanitize_key( $error->get_error_code() ) : 'missing_confirmation';
	// Upstream bodies and transport messages can contain applicant details or secrets.
	return 'Mzizi ' . sanitize_key( $phase ) . ': ' . $code . ( $http ? ' (HTTP ' . $http . ')' : '' ) . '.';
}

function toolkit_application_relay_finish( $record, array $values, $event, $message ) {
	global $wpdb;
	$values = array_merge( $values, array( 'relay_lease' => null, 'relay_lease_until' => null, 'updated_at' => current_time( 'mysql', true ) ) );
	$changed = $wpdb->update( toolkit_application_table_name(), $values, array( 'id' => (int) $record->id, 'status' => 'relaying', 'relay_lease' => $record->relay_lease ) );
	if ( 1 !== $changed ) return new WP_Error( 'relay_storage', 'Relay outcome could not be saved. Refresh the record before taking further action.' );
	toolkit_application_log_event( $record->id, $event, $message, get_current_user_id() ?: null );
	return true;
}

function toolkit_application_relay_preflight_failure( $record, $error, $phase, $validation = false ) {
	$data = is_wp_error( $error ) ? (array) $error->get_error_data() : array();
	$http = (int) ( $data['upstream_status'] ?? 0 );
	$retryable = ! $validation && ( ! $http || in_array( $http, array( 408, 425, 429 ), true ) || $http >= 500 );
	$delays = array( 1 => 300, 2 => 900, 3 => 3600, 4 => 14400 );
	$delay = $retryable ? ( $delays[ (int) $record->relay_attempts ] ?? 0 ) : 0;
	$next = $delay ? gmdate( 'Y-m-d H:i:s', time() + $delay ) : null;
	$message = toolkit_application_relay_error_summary( $error, $phase );
	$status = $next ? 'retry_wait' : ( $validation ? 'validation_failed' : 'relay_failed' );
	$saved = toolkit_application_relay_finish( $record, array( 'status' => $status, 'relay_phase' => 'preflight', 'next_relay_at' => $next, 'relay_http_status' => $http ?: null, 'last_error' => $message ), 'relay_failed', $message );
	if ( is_wp_error( $saved ) ) return $saved;
	toolkit_application_log_event( $record->id, $next ? 'retry_scheduled' : 'review_required', $next ? 'Safe pre-submission retry scheduled for ' . $next . ' UTC.' : 'Automatic retry stopped; Admissions review required.', get_current_user_id() ?: null );
	return new WP_Error( 'relay_not_sent', 'The application is saved. ' . ( $next ? 'A safe retry is scheduled.' : 'Admissions review is required.' ), array( 'status' => 503, 'reference' => $record->reference ) );
}

function toolkit_application_relay_record( $record_id, $release_source = 'manual' ) {
	global $wpdb;
	if ( ! in_array( $release_source, array( 'manual', 'public', 'automatic' ), true ) ) return new WP_Error( 'invalid_release_source', 'Invalid relay source.' );
	if ( ! toolkit_mzizi_relay_enabled() ) return new WP_Error( 'relay_disabled', 'Mzizi relay is disabled in this environment.' );
	if ( 'manual' === $release_source && ( ! current_user_can( 'manage_options' ) || empty( $_POST['duplicate_checked'] ) ) ) return new WP_Error( 'duplicate_check_required', 'An administrator must check Mzizi for an existing application before manual release.' );
	$table = toolkit_application_table_name();
	$token = wp_generate_uuid4();
	$eligible = "status IN ('received','queued','retry_wait','relay_failed','validation_failed','delivery_unconfirmed')";
	if ( 'public' === $release_source ) $eligible = "status = 'received' AND next_relay_at IS NOT NULL";
	if ( 'automatic' === $release_source ) {
		$queued = defined( 'TOOLKIT_MZIZI_AUTOMATIC_RELEASE_ENABLED' ) && true === TOOLKIT_MZIZI_AUTOMATIC_RELEASE_ENABLED ? " OR (status = 'queued' AND relay_attempts = 0)" : '';
		$eligible = "relay_attempts < 5 AND ((status IN ('retry_wait','received') AND next_relay_at <= UTC_TIMESTAMP()){$queued})";
	}
	// Claim before any remote request. Concurrent cron, shutdown and manual releases cannot win together.
	$claimed = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET status = 'relaying', relay_phase = 'preflight', relay_lease = %s, relay_lease_until = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 15 MINUTE), relay_attempts = relay_attempts + 1, next_relay_at = NULL, relay_http_status = NULL, last_error = NULL, updated_at = UTC_TIMESTAMP() WHERE id = %d AND relay_lease IS NULL AND ({$eligible})", $token, (int) $record_id ) );
	if ( 1 !== $claimed ) return new WP_Error( 'relay_not_claimed', 'This record is delivered, held for review, changed, or already being processed. Refresh its delivery history.' );
	$record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d AND relay_lease = %s", (int) $record_id, $token ) );
	if ( ! $record ) return new WP_Error( 'relay_storage', 'The relay claim could not be read; it will be recovered by the queue.' );
	toolkit_application_log_event( $record->id, 'relay_started', ucfirst( $release_source ) . ' relay attempt ' . (int) $record->relay_attempts . '.', get_current_user_id() ?: null );
	$data = toolkit_application_decrypt_payload( $record->payload );
	if ( is_wp_error( $data ) ) return toolkit_application_relay_preflight_failure( $record, $data, 'decrypt', true );
	if ( ! in_array( $data['study_mode'] ?? '', array( '', 'Online', 'Physical' ), true ) || ! in_array( $data['sponsorship_type'] ?? '', array( '', 'Sponsored', 'Self-Sponsored' ), true ) ) return toolkit_application_relay_preflight_failure( $record, new WP_Error( 'invalid_stored_choices', 'Stored study or sponsorship choice requires review.' ), 'selection', true );
	$school_id = preg_replace( '/\D/', '', (string) $data['school_id'] );
	$course_id = preg_replace( '/\D/', '', (string) $data['course_id'] );
	$intake_id = preg_replace( '/\D/', '', (string) $data['intake_id'] );
	$cookies = toolkit_mzizi_session();
	if ( is_wp_error( $cookies ) ) return toolkit_application_relay_preflight_failure( $record, $cookies, 'session' );
	$set = toolkit_mzizi_post( 'StudentInfo.asmx/SetApplicationSchoolIDParam', array( 'SchoolID' => $school_id ), $cookies );
	if ( is_wp_error( $set ) ) return toolkit_application_relay_preflight_failure( $record, $set, 'campus' );
	if ( empty( $set['Success'] ) ) return toolkit_application_relay_preflight_failure( $record, new WP_Error( 'invalid_school', 'Selected campus unavailable.' ), 'campus', true );
	$courses = toolkit_mzizi_post( 'StudentInfo.asmx/GetApplicationCoursesNoAlumni', array(), $cookies );
	if ( is_wp_error( $courses ) ) return toolkit_application_relay_preflight_failure( $record, $courses, 'courses' );
	$intakes = toolkit_application_available_intakes( $course_id, $cookies );
	if ( is_wp_error( $intakes ) ) return toolkit_application_relay_preflight_failure( $record, $intakes, 'intakes' );
	$sources = toolkit_mzizi_post( 'StudentInfo.asmx/GetCustomerSources', array(), $cookies );
	if ( is_wp_error( $sources ) ) return toolkit_application_relay_preflight_failure( $record, $sources, 'sources' );
	$course = null; $intake = null;
	foreach ( $courses as $item ) if ( $course_id === (string) ( $item['ID'] ?? '' ) ) { $course = $item; break; }
	foreach ( $intakes['items'] as $item ) if ( $intake_id === (string) ( $item['ID'] ?? '' ) ) { $intake = $item; break; }
	if ( ! $course || ! $intake ) return toolkit_application_relay_preflight_failure( $record, new WP_Error( 'stale_selection', 'Selected course or intake unavailable.' ), 'selection', true );
	$referral_source = sanitize_text_field( $data['referral_source'] ?? '' );
	if ( $referral_source && ! in_array( $referral_source, array_column( toolkit_application_source_items( $sources ), 'id' ), true ) ) return toolkit_application_relay_preflight_failure( $record, new WP_Error( 'invalid_source', 'Referral source unavailable.' ), 'source', true );
	$named = toolkit_application_update_payload_names( $record, array( 'course_name' => $course['Name'] ?? $course['Description'] ?? '', 'intake_name' => $intake['LevelName'] ?? '' ) );
	if ( is_wp_error( $named ) ) return toolkit_application_relay_preflight_failure( $record, $named, 'storage', true );
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
		'ModeOfStudy'        => sanitize_text_field( $data['study_mode'] ?? '' ),
		'SponsorshipType'    => sanitize_text_field( $data['sponsorship_type'] ?? '' ),
		'Channel'            => $referral_source,
		'ClassApplied'       => sanitize_text_field( $course['Name'] ?? $course['Description'] ?? '' ),
		'Gender'             => strtoupper( sanitize_key( $data['gender'] ) ),
	);
	// Persist the irreversible boundary BEFORE sending. A crash here must never trigger an automatic resend.
	$marked = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET relay_phase = 'submitting', updated_at = UTC_TIMESTAMP() WHERE id = %d AND status = 'relaying' AND relay_phase = 'preflight' AND relay_lease = %s AND relay_lease_until > UTC_TIMESTAMP()", $record->id, $token ) );
	if ( 1 !== $marked ) return new WP_Error( 'relay_storage', 'The submission boundary could not be recorded. Nothing was sent by this worker.' );
	$result = toolkit_mzizi_post( 'StudentInfo.asmx/SubmitOnlineApplication', $payload, $cookies, 25 );
	// A nonempty Message can describe a rejection. Require an affirmative confirmation.
	$message = is_array( $result ) && is_string( $result['Message'] ?? null ) ? $result['Message'] : '';
	$confirmed = is_array( $result ) && ! ( isset( $result['Success'] ) && ! in_array( $result['Success'], array( true, 1, 'true', '1' ), true ) )
		&& ! preg_match( '/\b(error|fail(?:ed|ure)?|invalid|duplicate|already|unsuccessful(?:ly)?|not|never|cannot|unable)\b/i', $message )
		&& ( true === ( $result['Success'] ?? false ) || (bool) preg_match( '/\bsuccess(?:ful|fully)?\b/i', $message ) );
	if ( ! $confirmed ) {
		$error = toolkit_application_relay_error_summary( $result, 'submission' );
		$metadata = is_wp_error( $result ) ? (array) $result->get_error_data() : array();
		$saved = toolkit_application_relay_finish( $record, array( 'status' => 'delivery_unconfirmed', 'relay_phase' => 'submitting', 'next_relay_at' => null, 'relay_http_status' => $metadata['upstream_status'] ?? null, 'last_error' => $error ), 'delivery_unconfirmed', $error . ' Check Mzizi before confirmation or resend; automatic replay is blocked.' );
		if ( is_wp_error( $saved ) ) return $saved;
		return new WP_Error( 'delivery_unconfirmed', 'Application received by Toolkit; Admissions must confirm its Mzizi status before any resend.', array( 'status' => 202, 'reference' => $record->reference ) );
	}
	$message = 'Mzizi confirmed successful application submission.';
	$saved = toolkit_application_relay_finish( $record, array( 'status' => 'delivered', 'relay_phase' => 'complete', 'next_relay_at' => null, 'relayed_at' => current_time( 'mysql', true ), 'mzizi_message' => $message, 'last_error' => null ), 'delivered', $message );
	if ( is_wp_error( $saved ) ) return $saved;
	return new WP_REST_Response( array( 'message' => $message, 'reference' => $record->reference, 'delivery' => 'delivered' ), 201 );
}

function toolkit_application_process_relay_queue() {
	global $wpdb;
	$table = toolkit_application_table_name();
	// Recovery also runs while relay is paused; it only changes local state, never sends.
	$stale = $wpdb->get_results( "SELECT * FROM {$table} WHERE status = 'relaying' AND (relay_lease_until < UTC_TIMESTAMP() OR (relay_lease IS NULL AND updated_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 15 MINUTE))) LIMIT 5" );
	foreach ( $stale as $record ) {
		// Legacy relaying rows have no phase proof: treat their outcome as uncertain.
		$safe = 'preflight' === $record->relay_phase && ! empty( $record->relay_lease );
		$next = $safe && (int) $record->relay_attempts < 5 ? gmdate( 'Y-m-d H:i:s', time() + 300 ) : null;
		$state = $safe ? ( $next ? 'retry_wait' : 'relay_failed' ) : 'delivery_unconfirmed';
		$message = $safe ? 'Expired pre-submission worker recovered.' : 'Interrupted submission needs confirmation in Mzizi; automatic replay blocked.';
		$changed = $wpdb->update( $table, array( 'status' => $state, 'next_relay_at' => $next, 'relay_lease' => null, 'relay_lease_until' => null, 'updated_at' => current_time( 'mysql', true ), 'last_error' => $message ), array( 'id' => (int) $record->id, 'status' => 'relaying', 'relay_lease' => $record->relay_lease, 'relay_phase' => $record->relay_phase ) );
		if ( 1 === $changed ) toolkit_application_log_event( $record->id, $next ? 'retry_scheduled' : 'review_required', $message, null );
	}
	$processed = 0;
	if ( toolkit_mzizi_relay_enabled() ) {
		$queued = defined( 'TOOLKIT_MZIZI_AUTOMATIC_RELEASE_ENABLED' ) && true === TOOLKIT_MZIZI_AUTOMATIC_RELEASE_ENABLED ? " OR (status = 'queued' AND relay_attempts = 0)" : '';
		$ids = $wpdb->get_col( "SELECT id FROM {$table} WHERE relay_lease IS NULL AND relay_attempts < 5 AND ((status IN ('retry_wait','received') AND next_relay_at <= UTC_TIMESTAMP()){$queued}) ORDER BY next_relay_at ASC, id ASC LIMIT 3" );
		foreach ( $ids as $id ) { toolkit_application_relay_record( (int) $id, 'automatic' ); $processed++; }
	}
	update_option( 'toolkit_application_relay_last_run', array( 'at' => current_time( 'mysql', true ), 'processed' => $processed ), false );
}
