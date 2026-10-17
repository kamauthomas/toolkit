<?php
/** Last-known-good public catalogues; never store applicant data in this cache. */

function toolkit_application_catalog_option( $key ) {
	return 'toolkit_mzizi_snapshot_' . $key;
}

function toolkit_application_catalog_entry( $key ) {
	$entry = get_option( toolkit_application_catalog_option( $key ), array() );
	if ( is_array( $entry ) && isset( $entry['payload'] ) && is_array( $entry['payload'] ) ) return $entry;
	// Preserve a still-valid legacy transient before its short expiry erases it.
	$legacy = get_transient( 'toolkit_mzizi_' . $key );
	if ( is_array( $legacy ) ) {
		$payload = str_starts_with( $key, 'courses_' ) ? array( 'courses' => $legacy ) : $legacy;
		$entry = array( 'payload' => $payload, 'updated_at' => time(), 'checked_at' => time(), 'retry_after' => 0 );
		update_option( toolkit_application_catalog_option( $key ), $entry, false );
		return $entry;
	}
	return is_array( $entry ) ? $entry : array();
}

function toolkit_application_catalog_fetch( $key ) {
	$cookies = toolkit_mzizi_session();
	if ( is_wp_error( $cookies ) ) return $cookies;
	if ( 'options' === $key ) {
		$schools = toolkit_mzizi_post( 'StudentInfo.asmx/GetSchools', array(), $cookies );
		if ( is_wp_error( $schools ) ) return $schools;
		$sources = toolkit_mzizi_post( 'StudentInfo.asmx/GetCustomerSources', array(), $cookies );
		if ( is_wp_error( $sources ) ) return $sources;
		$public = toolkit_application_public_items( $schools );
		if ( ! $public ) return new WP_Error( 'invalid_catalogue', 'Mzizi returned no usable campuses.' );
		return array( 'schools' => $public, 'counties' => toolkit_application_counties(), 'sources' => toolkit_application_source_items( $sources ) );
	}
	if ( preg_match( '/^courses_([0-9]{1,10})$/D', $key, $matches ) ) {
		$set = toolkit_mzizi_post( 'StudentInfo.asmx/SetApplicationSchoolIDParam', array( 'SchoolID' => $matches[1] ), $cookies );
		if ( is_wp_error( $set ) ) return $set;
		if ( empty( $set['Success'] ) ) return new WP_Error( 'invalid_school', 'Campus catalogue unavailable.' );
		$items = toolkit_mzizi_post( 'StudentInfo.asmx/GetApplicationCoursesNoAlumni', array(), $cookies );
		if ( is_wp_error( $items ) ) return $items;
		$public = toolkit_application_public_items( $items, 'Description' );
		if ( $items && ! $public ) return new WP_Error( 'invalid_catalogue', 'Course catalogue unreadable.' );
		return array( 'courses' => $public );
	}
	if ( preg_match( '/^intakes_([0-9]{1,10})$/D', $key, $matches ) ) {
		$items = toolkit_application_available_intakes( $matches[1], $cookies );
		if ( is_wp_error( $items ) ) return $items;
		$public = toolkit_application_public_items( $items['items'], 'LevelName' );
		if ( $items['items'] && ! $public ) return new WP_Error( 'invalid_catalogue', 'Intake catalogue unreadable.' );
		return array( 'intakes' => $public, 'source' => $items['source'] );
	}
	return new WP_Error( 'invalid_catalogue_key', 'Invalid catalogue request.' );
}

function toolkit_application_catalog_refresh( $key, $force = false ) {
	global $wpdb;
	if ( ! preg_match( '/^(options|(?:courses|intakes)_[0-9]{1,10})$/D', $key ) ) return new WP_Error( 'invalid_catalogue_key', 'Invalid catalogue request.' );
	$lock = 'toolkit_catalog_' . substr( hash( 'sha256', toolkit_application_table_name() . '|' . $key ), 0, 44 );
	if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 0)', $lock ) ) ) return new WP_Error( 'catalogue_busy', 'Catalogue refresh already running.' );
	try {
		$entry = toolkit_application_catalog_entry( $key );
		$interval = max( HOUR_IN_SECONDS, (int) apply_filters( 'toolkit_application_catalog_refresh_interval', 6 * HOUR_IN_SECONDS ) );
		if ( ! $force && isset( $entry['payload'] ) && ( $entry['checked_at'] ?? 0 ) > time() - $interval ) return $entry['payload'];
		if ( ! empty( $entry['retry_after'] ) && $entry['retry_after'] > time() ) return new WP_Error( 'catalogue_cooldown', 'Catalogue refresh is paused briefly after a service failure.' );
		$result = toolkit_application_catalog_fetch( $key );
		$entry['checked_at'] = time();
		if ( is_wp_error( $result ) ) {
			$entry['retry_after'] = time() + 30 * MINUTE_IN_SECONDS;
			$entry['last_error'] = sanitize_key( $result->get_error_code() );
		} else {
			$entry['payload'] = $result; $entry['updated_at'] = time(); $entry['retry_after'] = 0; unset( $entry['last_error'] );
		}
		update_option( toolkit_application_catalog_option( $key ), $entry, false );
		return $result;
	} finally {
		$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $lock ) );
	}
}

function toolkit_application_catalog_get( $key ) {
	$entry = toolkit_application_catalog_entry( $key );
	if ( isset( $entry['payload'] ) ) return $entry['payload'];
	return toolkit_application_catalog_refresh( $key );
}

function toolkit_application_catalog_response( $request, $type ) {
	$guard = toolkit_application_request_guard( $request );
	if ( is_wp_error( $guard ) ) return $guard;
	$options = toolkit_application_catalog_get( 'options' );
	if ( is_wp_error( $options ) ) return $options;
	$options = toolkit_application_visible_options( $options );
	if ( 'options' === $type ) return rest_ensure_response( $options );
	$id = $request->get_param( 'courses' === $type ? 'school_id' : 'course_id' );
	if ( ! is_scalar( $id ) || ! preg_match( '/^[0-9]{1,10}$/D', (string) $id ) ) return new WP_Error( 'invalid_selection', 'Select a valid campus or course.', array( 'status' => 422 ) );
	$allowed = array_column( $options['schools'], 'id' );
	if ( 'intakes' === $type ) {
		$allowed = array();
		foreach ( $options['schools'] as $school ) {
			$entry = toolkit_application_catalog_entry( 'courses_' . $school['id'] );
			$allowed = array_merge( $allowed, array_column( $entry['payload']['courses'] ?? array(), 'id' ) );
		}
	}
	if ( ! in_array( (string) $id, array_map( 'strval', $allowed ), true ) ) return new WP_Error( 'invalid_selection', 'Select a listed campus or course.', array( 'status' => 422 ) );
	$result = toolkit_application_catalog_get( $type . '_' . $id );
	return is_wp_error( $result ) ? $result : rest_ensure_response( $result );
}

function toolkit_application_refresh_catalogues() {
	// Walk only published catalogue IDs. Arbitrary public parameters cannot grow the cache.
	$options = toolkit_application_catalog_entry( 'options' );
	$keys = array( 'options' );
	foreach ( toolkit_application_visible_options( $options['payload'] ?? array() )['schools'] ?? array() as $school ) {
		if ( ! preg_match( '/^[0-9]{1,10}$/D', (string) $school['id'] ) ) continue;
		$key = 'courses_' . $school['id']; $keys[] = $key;
		$entry = toolkit_application_catalog_entry( $key );
		foreach ( $entry['payload']['courses'] ?? array() as $course ) if ( preg_match( '/^[0-9]{1,10}$/D', (string) $course['id'] ) ) $keys[] = 'intakes_' . $course['id'];
	}
	$due = array();
	$interval = max( HOUR_IN_SECONDS, (int) apply_filters( 'toolkit_application_catalog_refresh_interval', 6 * HOUR_IN_SECONDS ) );
	foreach ( array_slice( array_unique( $keys ), 0, 500 ) as $key ) {
		$entry = toolkit_application_catalog_entry( $key );
		if ( ( $entry['retry_after'] ?? 0 ) > time() ) continue;
		if ( empty( $entry['payload'] ) || ( $entry['checked_at'] ?? 0 ) < time() - $interval ) $due[ $key ] = $entry['checked_at'] ?? 0;
	}
	asort( $due );
	$checked = 0;
	foreach ( array_slice( array_keys( $due ), 0, 2 ) as $key ) { toolkit_application_catalog_refresh( $key ); $checked++; }
	update_option( 'toolkit_application_catalog_last_run', array( 'at' => current_time( 'mysql', true ), 'checked' => $checked ), false );
}
