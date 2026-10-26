<?php
/** Private PDF exchange; native Reception rendering never creates website applications. */
function toolkit_reception_letters_enabled() {
	$enabled = defined( 'TOOLKIT_RECEPTION_CALLING_LETTERS_ENABLED' )
		? true === TOOLKIT_RECEPTION_CALLING_LETTERS_ENABLED
		: (bool) get_option( 'toolkit_reception_calling_letters_enabled', false );
	return $enabled && toolkit_reception_application_sync_enabled();
}

function toolkit_reception_letter_permission( WP_REST_Request $request ) {
	if ( ! toolkit_reception_letters_enabled() ) return new WP_Error( 'letters_unavailable', 'Calling letters are unavailable.', array( 'status' => 503 ) );
	$method = $request->get_method();
	$id = $request->get_url_params()['id'] ?? null;
	if ( $request->get_query_params() || strlen( $request->get_body() ) > 8192
		|| ( 'GET' === $method && ( ! is_string( $id ) || ! ctype_digit( $id ) || strlen( $id ) > 15 || (int) $id < 1 || '' !== $request->get_body() ) )
		|| ! in_array( $method, array( 'GET', 'POST' ), true ) ) {
		return new WP_Error( 'letters_request', 'Invalid calling-letter request.', array( 'status' => 400 ) );
	}
	$resource = 'GET' === $method ? 'application:' . (int) $id : 'render-native';
	$timestamp = $request->get_header( 'x_toolkit_timestamp' );
	$nonce = $request->get_header( 'x_toolkit_nonce' );
	$signature = $request->get_header( 'x_toolkit_signature' );
	if ( ! ctype_digit( (string) $timestamp ) || strlen( (string) $timestamp ) > 11 || abs( time() - (int) $timestamp ) > 300
		|| ! preg_match( '/^[a-f0-9]{32}$/D', (string) $nonce ) || ! preg_match( '/^[a-f0-9]{64}$/D', (string) $signature ) ) {
		return new WP_Error( 'letters_auth', 'Calling-letter authentication failed.', array( 'status' => 401 ) );
	}
	$canonical = $timestamp . "\n" . $nonce . "\ncalling-letters-v1\n" . $method . "\n" . $resource . "\n" . hash( 'sha256', $request->get_body() );
	if ( ! hash_equals( hash_hmac( 'sha256', $canonical, toolkit_reception_api_secret() ), $signature ) ) {
		return new WP_Error( 'letters_auth', 'Calling-letter authentication failed.', array( 'status' => 401 ) );
	}
	global $wpdb;
	$prefix = 'toolkit_reception_letter_nonce_';
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND CAST(option_value AS UNSIGNED) < %d LIMIT 100", $wpdb->esc_like( $prefix ) . '%', time() ) );
	if ( ! add_option( $prefix . hash_hmac( 'sha256', $nonce, toolkit_reception_api_secret() ), time() + 600, '', false ) ) {
		return new WP_Error( 'letters_replay', 'Use a fresh calling-letter request.', array( 'status' => 409 ) );
	}
	return true;
}

function toolkit_reception_pdf_response( $path, $reference, array $metadata = array() ) {
	$bytes = is_readable( $path ) ? @file_get_contents( $path, false, null, 0, 1048577 ) : false;
	if ( false === $bytes || strlen( $bytes ) > 1048576 || ! str_starts_with( $bytes, '%PDF-' ) || ! str_ends_with( rtrim( $bytes ), '%%EOF' ) ) {
		return new WP_Error( 'letters_not_ready', 'A PDF calling letter is not available.', array( 'status' => 404 ) );
	}
	$response = new WP_REST_Response( array_merge( array(
		'version' => 1, 'source' => toolkit_is_demo_environment() ? 'demo' : 'production',
		'reference' => $reference, 'sha256' => hash( 'sha256', $bytes ), 'pdf' => base64_encode( $bytes ),
	), $metadata ), 200 );
	$response->header( 'Cache-Control', 'no-store, private, max-age=0' );
	$response->header( 'X-Robots-Tag', 'noindex, nofollow, noarchive' );
	return $response;
}

function toolkit_reception_existing_letter( WP_REST_Request $request ) {
	global $wpdb;
	$id = (int) $request->get_url_params()['id'];
	$row = $wpdb->get_row( $wpdb->prepare( 'SELECT l.pdf_path,l.status,a.reference FROM ' . toolkit_calling_letter_table_name() . ' l JOIN ' . toolkit_application_table_name() . ' a ON a.id=l.application_id WHERE a.id=%d', $id ) );
	if ( ! $row || 'generated' !== $row->status || ! preg_match( '/^[A-Za-z0-9-]{1,64}$/D', $row->reference ) ) {
		return new WP_Error( 'letters_not_ready', 'A PDF calling letter is not available.', array( 'status' => 404 ) );
	}
	$expected = basename( toolkit_calling_letter_pdf_file_path( $id, $row->reference ) );
	$path = toolkit_confined_private_file( $row->pdf_path, array( toolkit_calling_letter_output_dir(), toolkit_calling_letter_private_dir() ), $expected );
	if ( false === $path ) return new WP_Error( 'letters_not_ready', 'A PDF calling letter is not available.', array( 'status' => 404 ) );
	return toolkit_reception_pdf_response( $path, $row->reference, array( 'application_id' => $id ) );
}

function toolkit_reception_render_native_letter( WP_REST_Request $request ) {
	$data = $request->get_json_params();
	$fields = array( 'reference' => 64, 'name' => 120, 'phone' => 16, 'course' => 120, 'intake' => 120 );
	if ( ! is_array( $data ) || array_diff( array_keys( $data ), array_keys( $fields ) ) ) {
		return new WP_Error( 'letters_request', 'Invalid calling-letter details.', array( 'status' => 422 ) );
	}
	foreach ( $fields as $field => $maximum ) {
		$value = $data[ $field ] ?? null;
		if ( ! is_string( $value ) || ! mb_check_encoding( $value, 'UTF-8' ) || '' === trim( $value ) || mb_strlen( $value ) > $maximum || preg_match( '/[\x00-\x1f\x7f<>]/u', $value ) ) {
			return new WP_Error( 'letters_request', 'Invalid calling-letter details.', array( 'status' => 422 ) );
		}
		$data[ $field ] = trim( $value );
	}
	if ( ! preg_match( '/^APP-[A-Za-z0-9-]{1,59}$/D', $data['reference'] ) || ! preg_match( '/^\+[1-9][0-9]{7,14}$/D', $data['phone'] ) ) {
		return new WP_Error( 'letters_request', 'Invalid calling-letter details.', array( 'status' => 422 ) );
	}
	foreach ( array( 'name', 'course', 'intake' ) as $field ) {
		foreach ( preg_split( '/\s+/u', $data[ $field ] ) as $word ) {
			if ( toolkit_pdf_text_width( strtoupper( $word ), true, 10.5 ) > 430 ) return new WP_Error( 'letters_layout', 'A calling-letter detail is too long to print safely.', array( 'status' => 422 ) );
		}
	}
	$path = false;
	try {
		$template = toolkit_calling_letter_template_path();
		if ( ! is_readable( $template ) ) throw new RuntimeException( 'Template unavailable' );
		$revision = hash( 'sha256', hash_file( 'sha256', $template ) . "\n" . hash_file( 'sha256', __DIR__ . '/calling-letter-pdf.php' ) );
		$root = realpath( toolkit_calling_letter_private_dir() );
		$path = @tempnam( toolkit_calling_letter_private_dir(), '.reception-letter-' );
		if ( false === $path || false === $root || realpath( dirname( $path ) ) !== $root || ! chmod( $path, 0600 ) ) throw new RuntimeException( 'Private output unavailable' );
		$result = toolkit_calling_letter_render_pdf( $path, array(
			'full_name' => $data['name'], 'first_name' => explode( ' ', $data['name'] )[0],
			'phone' => $data['phone'], 'course' => $data['course'], 'letter_date' => toolkit_calling_letter_ordinal_date(),
		), array( 'intake' => $data['intake'], 'strict_layout' => true ) );
		$current_revision = hash( 'sha256', hash_file( 'sha256', $template ) . "\n" . hash_file( 'sha256', __DIR__ . '/calling-letter-pdf.php' ) );
		if ( true !== $result || $current_revision !== $revision ) throw new RuntimeException( 'Renderer unavailable' );
		return toolkit_reception_pdf_response( $path, $data['reference'], array( 'template_revision' => $revision ) );
	} catch ( Throwable $error ) {
		// Do not expose renderer paths, applicant data or exception bodies.
		return new WP_Error( 'letters_render', 'The calling letter could not be prepared. Please try again later.', array( 'status' => 503 ) );
	} finally {
		if ( false !== $path && is_file( $path ) ) unlink( $path );
	}
}

add_action( 'rest_api_init', static function () {
	register_rest_route( 'toolkit/v1', '/reception/applications/(?P<id>[0-9]+)/calling-letter', array(
		'methods' => 'GET', 'permission_callback' => 'toolkit_reception_letter_permission', 'callback' => 'toolkit_reception_existing_letter',
	) );
	register_rest_route( 'toolkit/v1', '/reception/calling-letters/render', array(
		'methods' => 'POST', 'permission_callback' => 'toolkit_reception_letter_permission', 'callback' => 'toolkit_reception_render_native_letter',
	) );
} );
