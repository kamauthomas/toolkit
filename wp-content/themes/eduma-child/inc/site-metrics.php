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
	$referer = $request->get_header( 'referer' );
	$source  = $origin ? $origin : $referer;
	if ( ! $source || wp_parse_url( $source, PHP_URL_HOST ) !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
		return new WP_Error( 'invalid_origin', 'Invalid metric origin.', array( 'status' => 403 ) );
	}

	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	$rate_key = 'toolkit_metric_' . substr( wp_hash( $ip ), 0, 20 );
	$count    = (int) get_transient( $rate_key );
	if ( $count >= 60 ) {
		return new WP_Error( 'rate_limited', 'Metric rate limit reached.', array( 'status' => 429 ) );
	}
	set_transient( $rate_key, $count + 1, MINUTE_IN_SECONDS );

	$allowed_events = array( 'page_view', 'engaged_time', 'scroll_depth', 'performance', 'outbound_click', 'interaction' );
	$event          = sanitize_key( $request->get_param( 'event' ) );
	if ( ! in_array( $event, $allowed_events, true ) ) {
		return new WP_Error( 'invalid_event', 'Invalid metric event.', array( 'status' => 400 ) );
	}

	$path = '/' . ltrim( sanitize_text_field( (string) $request->get_param( 'path' ) ), '/' );
	$path = strtok( $path, '?' );
	if ( strlen( $path ) > 180 || ! preg_match( '#^/[A-Za-z0-9/_-]*$#', $path ) ) {
		return new WP_Error( 'invalid_path', 'Invalid metric path.', array( 'status' => 400 ) );
	}
	$value = max( 0, min( 600000, absint( $request->get_param( 'value' ) ) ) );
	$label = sanitize_key( (string) $request->get_param( 'label' ) );
	if ( 'interaction' === $event && $label ) $event .= ':' . substr( $label, 0, 48 );
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
	add_menu_page( 'Toolkit Control', 'Toolkit Control', 'manage_options', 'toolkit-control', 'toolkit_render_metrics_page', 'dashicons-chart-area', 3 );
} );

add_action( 'admin_post_toolkit_save_controls', function() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You do not have permission to update Toolkit controls.' );
	check_admin_referer( 'toolkit_save_controls' );
	$catalog_enabled = isset( $_POST['catalog'] );
	$controls        = array(
		'toolkit_redesign_enabled'     => isset( $_POST['redesign'] ),
		'toolkit_2026_catalog_enabled' => $catalog_enabled,
		// Pricing cannot remain armed behind a disabled catalogue switch.
		'toolkit_2026_pricing_enabled' => $catalog_enabled && isset( $_POST['pricing'] ),
	);
	foreach ( $controls as $option => $enabled ) update_option( $option, $enabled ? 1 : 0, false );
	wp_safe_redirect( add_query_arg( array( 'page' => 'toolkit-control', 'updated' => '1' ), admin_url( 'admin.php' ) ) );
	exit;
} );

function toolkit_render_metrics_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to view Toolkit controls.' );
	}

	$data  = get_option( 'toolkit_site_metrics', array() );
	$since = gmdate( 'Y-m-d', strtotime( '-30 days' ) );
	$rows  = array();
	foreach ( (array) $data as $date => $paths ) {
		if ( $date < $since ) continue;
		foreach ( $paths as $path => $events ) {
			if ( ! isset( $rows[ $path ] ) ) $rows[ $path ] = array( 'views' => 0, 'engaged' => 0, 'engaged_count' => 0, 'scroll' => 0, 'load' => 0, 'load_count' => 0, 'interactions' => 0 );
			$rows[ $path ]['views'] += $events['page_view']['count'] ?? 0;
			$rows[ $path ]['engaged'] += $events['engaged_time']['total'] ?? 0;
			$rows[ $path ]['engaged_count'] += $events['engaged_time']['count'] ?? 0;
			$rows[ $path ]['scroll'] = max( $rows[ $path ]['scroll'], $events['scroll_depth']['max'] ?? 0 );
			$rows[ $path ]['load'] += $events['performance']['total'] ?? 0;
			$rows[ $path ]['load_count'] += $events['performance']['count'] ?? 0;
			foreach ( $events as $event_name => $metric ) if ( 0 === strpos( $event_name, 'interaction:' ) ) $rows[ $path ]['interactions'] += $metric['count'];
		}
	}
	uasort( $rows, function( $a, $b ) { return $b['views'] <=> $a['views']; } );
	echo '<div class="wrap"><h1>Toolkit Control</h1>';
	if ( isset( $_GET['updated'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Toolkit controls updated.</p></div>';
	echo '<p>Manage rollout switches and monitor aggregate site activity from one operational view.</p>';
	echo '<div style="display:grid;grid-template-columns:minmax(340px,.75fr) minmax(0,1.25fr);gap:20px;align-items:start;margin:22px 0">';
	echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="padding:22px;background:#fff;border:1px solid #dcdcde">';
	echo '<input type="hidden" name="action" value="toolkit_save_controls">';
	wp_nonce_field( 'toolkit_save_controls' );
	echo '<h2 style="margin-top:0">Release controls</h2><p>Constants in <code>wp-config.php</code> take priority over these settings.</p>';
	toolkit_render_control_toggle( 'redesign', 'Modern redesign', eduma_child_redesign_enabled(), 'Activates child-theme pages, navigation, footer, chatbot and metrics.' );
	toolkit_render_control_toggle( 'catalog', '2026 course catalogue', eduma_child_2026_catalog_enabled(), 'Switches from preserved legacy courses to the separately managed 2026 catalogue.' );
	toolkit_render_control_toggle( 'pricing', 'September 2026 pricing', eduma_child_2026_pricing_enabled(), 'Must remain off until the approved September pricing effective date.' );
	echo '<p><button class="button button-primary" type="submit">Save controls</button></p></form>';
	echo '<div style="padding:22px;background:#fff;border:1px solid #dcdcde"><h2 style="margin-top:0">System status</h2><table class="widefat striped"><tbody>';
	printf( '<tr><th>Site</th><td>%s</td></tr>', esc_html( home_url() ) );
	printf( '<tr><th>Theme</th><td>%s</td></tr>', esc_html( wp_get_theme()->get( 'Name' ) ) );
	printf( '<tr><th>WordPress</th><td>%s</td></tr>', esc_html( get_bloginfo( 'version' ) ) );
	printf( '<tr><th>Metrics retention</th><td>90 days</td></tr>' );
	echo '</tbody></table></div></div>';
	echo '<h2>Site metrics</h2><p>Aggregate, first-party activity for the last 30 days. No raw IP addresses, cookies, or visitor profiles are stored.</p><table class="widefat striped"><thead><tr><th>Page</th><th>Views</th><th>Interactions</th><th>Avg. engaged time</th><th>Max scroll</th><th>Avg. load</th></tr></thead><tbody>';
	foreach ( $rows as $path => $row ) {
		printf( '<tr><td>%s</td><td>%s</td><td>%s</td><td>%ss</td><td>%s%%</td><td>%sms</td></tr>', esc_html( $path ), number_format_i18n( $row['views'] ), number_format_i18n( $row['interactions'] ), number_format_i18n( $row['engaged_count'] ? $row['engaged'] / $row['engaged_count'] : 0 ), number_format_i18n( $row['scroll'] ), number_format_i18n( $row['load_count'] ? $row['load'] / $row['load_count'] : 0 ) );
	}
	if ( ! $rows ) echo '<tr><td colspan="6">No metrics recorded yet.</td></tr>';
	echo '</tbody></table></div>';
}

function toolkit_render_control_toggle( $name, $label, $enabled, $description ) {
	printf( '<label style="display:block;padding:14px 0;border-top:1px solid #eee"><input type="checkbox" name="%1$s" value="1" %2$s> <strong>%3$s</strong><span style="display:block;margin:5px 0 0 24px;color:#646970">%4$s</span></label>', esc_attr( $name ), checked( $enabled, true, false ), esc_html( $label ), esc_html( $description ) );
}
