<?php
/** Privacy-conscious, aggregate first-party site metrics. */

add_action( 'wp_enqueue_scripts', function() {
	if ( is_admin() || ! eduma_child_redesign_enabled() ) {
		return;
	}
	$path = get_stylesheet_directory() . '/assets/js/toolkit-metrics.js';
	wp_enqueue_script( 'toolkit-site-metrics', get_stylesheet_directory_uri() . '/assets/js/toolkit-metrics.js', array(), filemtime( $path ), true );
	wp_localize_script( 'toolkit-site-metrics', 'toolkitMetrics', array(
		'endpoint' => esc_url_raw( rest_url( 'toolkit/v1/metrics' ) ),
	) );
}, 1200 );

add_action( 'rest_api_init', function() {
	register_rest_route( 'toolkit/v1', '/metrics', array(
		'methods'             => 'POST',
		'permission_callback' => '__return_true',
		'callback'            => 'toolkit_record_site_metric',
	) );
} );

function toolkit_record_site_metric( WP_REST_Request $request ) {
	$origin = $request->get_header( 'origin' );
	if ( $origin && wp_parse_url( $origin, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		return new WP_Error( 'invalid_origin', 'Invalid metric origin.', array( 'status' => 403 ) );
	}

	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	$rate_key = 'toolkit_metric_' . substr( wp_hash( $ip ), 0, 20 );
	$count    = (int) get_transient( $rate_key );
	if ( $count >= 60 ) {
		return new WP_Error( 'rate_limited', 'Metric rate limit reached.', array( 'status' => 429 ) );
	}
	set_transient( $rate_key, $count + 1, MINUTE_IN_SECONDS );

	$allowed_events = array( 'page_view', 'engaged_time', 'scroll_depth', 'performance', 'outbound_click' );
	$event          = sanitize_key( $request->get_param( 'event' ) );
	if ( ! in_array( $event, $allowed_events, true ) ) {
		return new WP_Error( 'invalid_event', 'Invalid metric event.', array( 'status' => 400 ) );
	}

	$path = '/' . ltrim( sanitize_text_field( (string) $request->get_param( 'path' ) ), '/' );
	$path = strtok( $path, '?' );
	if ( strlen( $path ) > 180 ) {
		$path = substr( $path, 0, 180 );
	}
	$value = max( 0, min( 600000, absint( $request->get_param( 'value' ) ) ) );
	$day   = gmdate( 'Y-m-d' );
	$data  = get_option( 'toolkit_site_metrics', array() );
	$data  = is_array( $data ) ? $data : array();

	if ( ! isset( $data[ $day ][ $path ][ $event ] ) ) {
		$data[ $day ][ $path ][ $event ] = array( 'count' => 0, 'total' => 0, 'max' => 0 );
	}
	$data[ $day ][ $path ][ $event ]['count']++;
	$data[ $day ][ $path ][ $event ]['total'] += $value;
	$data[ $day ][ $path ][ $event ]['max'] = max( $data[ $day ][ $path ][ $event ]['max'], $value );

	$cutoff = gmdate( 'Y-m-d', strtotime( '-90 days' ) );
	$data   = array_filter( $data, function( $date ) use ( $cutoff ) { return $date >= $cutoff; }, ARRAY_FILTER_USE_KEY );
	update_option( 'toolkit_site_metrics', $data, false );
	return new WP_REST_Response( null, 204 );
}

add_action( 'admin_menu', function() {
	add_management_page( 'Toolkit Metrics', 'Toolkit Metrics', 'manage_options', 'toolkit-metrics', 'toolkit_render_metrics_page' );
} );

function toolkit_render_metrics_page() {
	$data  = get_option( 'toolkit_site_metrics', array() );
	$since = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
	$rows  = array();
	foreach ( (array) $data as $date => $paths ) {
		if ( $date < $since ) continue;
		foreach ( $paths as $path => $events ) {
			if ( ! isset( $rows[ $path ] ) ) $rows[ $path ] = array( 'views' => 0, 'engaged' => 0, 'engaged_count' => 0, 'scroll' => 0, 'load' => 0, 'load_count' => 0 );
			$rows[ $path ]['views'] += $events['page_view']['count'] ?? 0;
			$rows[ $path ]['engaged'] += $events['engaged_time']['total'] ?? 0;
			$rows[ $path ]['engaged_count'] += $events['engaged_time']['count'] ?? 0;
			$rows[ $path ]['scroll'] = max( $rows[ $path ]['scroll'], $events['scroll_depth']['max'] ?? 0 );
			$rows[ $path ]['load'] += $events['performance']['total'] ?? 0;
			$rows[ $path ]['load_count'] += $events['performance']['count'] ?? 0;
		}
	}
	uasort( $rows, function( $a, $b ) { return $b['views'] <=> $a['views']; } );
	echo '<div class="wrap"><h1>Toolkit Metrics</h1><p>Aggregate, first-party activity for the last 30 days. No raw IP addresses, cookies, or visitor profiles are stored.</p><table class="widefat striped"><thead><tr><th>Page</th><th>Views</th><th>Avg. engaged time</th><th>Max scroll</th><th>Avg. load</th></tr></thead><tbody>';
	foreach ( $rows as $path => $row ) {
		printf( '<tr><td>%s</td><td>%s</td><td>%ss</td><td>%s%%</td><td>%sms</td></tr>', esc_html( $path ), number_format_i18n( $row['views'] ), number_format_i18n( $row['engaged_count'] ? $row['engaged'] / $row['engaged_count'] : 0 ), number_format_i18n( $row['scroll'] ), number_format_i18n( $row['load_count'] ? $row['load'] / $row['load_count'] : 0 ) );
	}
	if ( ! $rows ) echo '<tr><td colspan="5">No metrics recorded yet.</td></tr>';
	echo '</tbody></table></div>';
}
