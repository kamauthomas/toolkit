<?php
/**
 * Server-side adapter for Toolkit's public Mzizi application services.
 * Applicant data is validated and forwarded without being stored in WordPress.
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
	if ( ! toolkit_mzizi_submission_enabled() ) {
		return new WP_REST_Response( array( 'code' => 'handoff_required', 'message' => 'Direct submission is awaiting final integration approval.', 'handoff_url' => toolkit_mzizi_application_url() ), 503 );
	}
	if ( ! toolkit_application_verify_turnstile( isset( $data['cf-turnstile-response'] ) ? $data['cf-turnstile-response'] : '' ) ) {
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

	$school_id = preg_replace( '/\D/', '', (string) $data['school_id'] );
	$course_id = preg_replace( '/\D/', '', (string) $data['course_id'] );
	$intake_id = preg_replace( '/\D/', '', (string) $data['intake_id'] );
	$cookies   = toolkit_mzizi_session();
	if ( is_wp_error( $cookies ) ) {
		return $cookies;
	}
	$set = toolkit_mzizi_post( 'StudentInfo.asmx/SetApplicationSchoolIDParam', array( 'SchoolID' => $school_id ), $cookies );
	if ( is_wp_error( $set ) || empty( $set['Success'] ) ) {
		return new WP_Error( 'invalid_school', 'The selected campus is no longer available.', array( 'status' => 422 ) );
	}
	$courses = toolkit_mzizi_post( 'StudentInfo.asmx/GetApplicationCoursesNoAlumni', array(), $cookies );
	$intakes = toolkit_mzizi_post( 'OrganizationProcesses.asmx/GetCourseIntakeMonths', array( 'CourseID' => $course_id ), $cookies );
	$sources = toolkit_mzizi_post( 'StudentInfo.asmx/GetCustomerSources', array(), $cookies );
	if ( is_wp_error( $courses ) || is_wp_error( $intakes ) || is_wp_error( $sources ) ) {
		return new WP_Error( 'mzizi_validation', 'Course availability could not be confirmed.', array( 'status' => 503 ) );
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
		return new WP_Error( 'stale_selection', 'The selected course or intake is no longer available. Please select again.', array( 'status' => 422 ) );
	}
	$source_names = array_column( toolkit_application_source_items( $sources ), 'id' );
	$source       = isset( $data['referral_source'] ) ? sanitize_text_field( $data['referral_source'] ) : '';
	if ( $source && ! in_array( $source, $source_names, true ) ) {
		return new WP_Error( 'invalid_source', 'Select a valid referral source.', array( 'status' => 422 ) );
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
		'ClassApplied'       => sanitize_text_field( $course['Name'] ),
		'Gender'             => sanitize_key( $data['gender'] ),
	);
	$result = toolkit_mzizi_post( 'StudentInfo.asmx/SubmitOnlineApplication', $payload, $cookies );
	if ( is_wp_error( $result ) || empty( $result['Message'] ) ) {
		return new WP_Error( 'submission_failed', 'Mzizi did not confirm the application. No automatic retry was made.', array( 'status' => 502 ) );
	}
	return rest_ensure_response( array( 'message' => sanitize_text_field( $result['Message'] ) ) );
}
