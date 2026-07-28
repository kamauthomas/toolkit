<?php
/**
 * Website-to-reception relay.
 *
 * Visitor details are validated here and sent directly to the reception system.
 * WordPress deliberately does not retain a copy of the submission.
 */

function toolkit_reception_api_url() {
	$url = defined( 'TOOLKIT_RECEPTION_API_URL' ) ? TOOLKIT_RECEPTION_API_URL : getenv( 'TOOLKIT_RECEPTION_API_URL' );
	return $url ? esc_url_raw( untrailingslashit( $url ) ) : '';
}

function toolkit_reception_api_secret() {
	$secret = defined( 'TOOLKIT_RECEPTION_API_SECRET' ) ? TOOLKIT_RECEPTION_API_SECRET : getenv( 'TOOLKIT_RECEPTION_API_SECRET' );
	return is_string( $secret ) ? trim( $secret ) : '';
}

function toolkit_reception_submission_enabled() {
	$enabled = defined( 'TOOLKIT_RECEPTION_FORM_ENABLED' ) ? (bool) TOOLKIT_RECEPTION_FORM_ENABLED : true;
	return $enabled && toolkit_reception_api_url() && strlen( toolkit_reception_api_secret() ) >= 32;
}

function toolkit_reception_request_origin_is_valid( WP_REST_Request $request ) {
	$origin = $request->get_header( 'origin' );
	if ( ! $origin ) {
		$origin = $request->get_header( 'referer' );
	}
	if ( ! $origin ) {
		return false;
	}

	$expected = wp_parse_url( home_url( '/' ) );
	$actual   = wp_parse_url( $origin );
	return isset( $expected['host'], $actual['host'] )
		&& strtolower( $expected['host'] ) === strtolower( $actual['host'] )
		&& ( $expected['scheme'] ?? 'https' ) === ( $actual['scheme'] ?? 'https' );
}

function toolkit_reception_rate_limit_key() {
	$address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	return 'toolkit_reception_' . substr( hash_hmac( 'sha256', $address, wp_salt( 'nonce' ) ), 0, 32 );
}

function toolkit_reception_submit( WP_REST_Request $request ) {
	if ( ! toolkit_reception_submission_enabled() ) {
		return new WP_Error( 'reception_unavailable', 'Online reception is temporarily unavailable. Please call +254 709 549 200.', array( 'status' => 503 ) );
	}
	if ( ! toolkit_reception_request_origin_is_valid( $request ) ) {
		return new WP_Error( 'invalid_origin', 'The request could not be verified.', array( 'status' => 403 ) );
	}
	if ( ! wp_verify_nonce( $request->get_header( 'x-wp-nonce' ), 'wp_rest' ) ) {
		return new WP_Error( 'invalid_nonce', 'The form session has expired. Refresh the page and try again.', array( 'status' => 403 ) );
	}
	if ( trim( (string) $request->get_param( 'website' ) ) ) {
		return new WP_Error( 'invalid_submission', 'The submission could not be accepted.', array( 'status' => 400 ) );
	}

	$rate_key = toolkit_reception_rate_limit_key();
	$attempts = (int) get_transient( $rate_key );
	if ( $attempts >= 5 ) {
		return new WP_Error( 'rate_limited', 'Too many attempts. Please wait before trying again or call +254 709 549 200.', array( 'status' => 429 ) );
	}
	set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

	$name         = sanitize_text_field( (string) $request->get_param( 'name' ) );
	$phone        = sanitize_text_field( (string) $request->get_param( 'phone' ) );
	$email        = sanitize_email( (string) $request->get_param( 'email' ) );
	$organization = sanitize_text_field( (string) $request->get_param( 'organization' ) );
	$purpose      = sanitize_key( (string) $request->get_param( 'purpose' ) );
	$consent      = filter_var( $request->get_param( 'consent' ), FILTER_VALIDATE_BOOLEAN );
	$purposes     = array( 'course_enquiry', 'partnership', 'meeting', 'delivery', 'event', 'other' );

	if ( mb_strlen( $name ) < 2 || mb_strlen( $name ) > 120 ) {
		return new WP_Error( 'invalid_name', 'Enter your full name.', array( 'status' => 422 ) );
	}
	if ( ! preg_match( '/^[0-9+() .-]{7,24}$/', $phone ) ) {
		return new WP_Error( 'invalid_phone', 'Enter a valid phone number.', array( 'status' => 422 ) );
	}
	if ( $request->get_param( 'email' ) && ! is_email( $email ) ) {
		return new WP_Error( 'invalid_email', 'Enter a valid email address.', array( 'status' => 422 ) );
	}
	if ( mb_strlen( $organization ) > 120 || ! in_array( $purpose, $purposes, true ) || ! $consent ) {
		return new WP_Error( 'invalid_details', 'Review the highlighted details and confirm consent.', array( 'status' => 422 ) );
	}

	$payload = array(
		'name'         => $name,
		'phone'        => $phone,
		'email'        => $email ?: null,
		'organization' => $organization ?: null,
		'purpose'      => $purpose,
		'consent'      => true,
		'page'         => '/reception/',
	);
	$body      = wp_json_encode( $payload, JSON_UNESCAPED_SLASHES );
	$timestamp = (string) time();
	$nonce     = bin2hex( random_bytes( 16 ) );
	$signature = hash_hmac( 'sha256', $timestamp . "\n" . $nonce . "\n" . $body, toolkit_reception_api_secret() );
	$response  = wp_remote_post(
		toolkit_reception_api_url() . '/api/website/visits',
		array(
			'timeout'   => 12,
			'sslverify' => true,
			'headers'   => array(
				'Accept'              => 'application/json',
				'Content-Type'        => 'application/json',
				'X-Toolkit-Timestamp' => $timestamp,
				'X-Toolkit-Nonce'     => $nonce,
				'X-Toolkit-Signature' => $signature,
			),
			'body'      => $body,
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'reception_unreachable', 'We could not reach reception. Please call +254 709 549 200.', array( 'status' => 503 ) );
	}
	$status = wp_remote_retrieve_response_code( $response );
	$result = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( 201 !== $status || empty( $result['reference'] ) ) {
		return new WP_Error( 'reception_rejected', 'Reception could not accept the request. Please check your details or call +254 709 549 200.', array( 'status' => 502 ) );
	}

	delete_transient( $rate_key );
	return rest_ensure_response(
		array(
			'success'   => true,
			'reference' => sanitize_text_field( $result['reference'] ),
			'message'   => 'Your details reached reception. A team member will follow up.',
		)
	);
}

add_action( 'rest_api_init', function() {
	register_rest_route(
		'toolkit/v1',
		'/reception/submit',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'toolkit_reception_submit',
			'permission_callback' => '__return_true',
		)
	);
} );
