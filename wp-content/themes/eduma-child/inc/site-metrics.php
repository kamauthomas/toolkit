<?php
/** Privacy-conscious, aggregate first-party site metrics. */

require_once get_stylesheet_directory() . '/inc/support-hub.php';

function toolkit_metrics_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'toolkit_metrics_daily';
}

/** Daily aggregates are intentionally retained without an automatic expiry. */
function toolkit_metrics_install_storage() {
	global $wpdb;
	$version = '2.0.0';
	if ( $version === get_option( 'toolkit_metrics_storage_version' ) ) return;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = toolkit_metrics_table_name();
	$charset = $wpdb->get_charset_collate();
	dbDelta( "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		metric_date date NOT NULL,
		path varchar(190) NOT NULL,
		event varchar(80) NOT NULL,
		metric_count bigint(20) unsigned NOT NULL DEFAULT 0,
		metric_total bigint(20) unsigned NOT NULL DEFAULT 0,
		metric_max bigint(20) unsigned NOT NULL DEFAULT 0,
		updated_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY daily_metric (metric_date,path,event),
		KEY metric_date (metric_date),
		KEY path (path)
	) {$charset};" );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) return;

	/* One-time, non-destructive migration from the earlier 90-day option. */
	if ( ! get_option( 'toolkit_metrics_option_migrated' ) ) {
		$legacy = get_option( 'toolkit_site_metrics', array() );
		foreach ( (array) $legacy as $date => $paths ) {
			foreach ( (array) $paths as $path => $events ) {
				foreach ( (array) $events as $event => $metric ) {
					$wpdb->query( $wpdb->prepare(
						"INSERT INTO {$table} (metric_date,path,event,metric_count,metric_total,metric_max,updated_at)
						 VALUES (%s,%s,%s,%d,%d,%d,%s)
						 ON DUPLICATE KEY UPDATE metric_count=GREATEST(metric_count,VALUES(metric_count)), metric_total=GREATEST(metric_total,VALUES(metric_total)), metric_max=GREATEST(metric_max,VALUES(metric_max)), updated_at=VALUES(updated_at)",
						$date, $path, $event, (int) ( $metric['count'] ?? 0 ), (int) ( $metric['total'] ?? 0 ), (int) ( $metric['max'] ?? 0 ), current_time( 'mysql', true )
					) );
				}
			}
		}
		update_option( 'toolkit_metrics_option_migrated', 1, false );
	}
	update_option( 'toolkit_metrics_storage_version', $version, false );
}
add_action( 'after_switch_theme', 'toolkit_metrics_install_storage' );
add_action( 'init', 'toolkit_metrics_install_storage', 2 );

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
	global $wpdb;
	toolkit_metrics_install_storage();
	$table = toolkit_metrics_table_name();
	$saved = $wpdb->query( $wpdb->prepare(
		"INSERT INTO {$table} (metric_date,path,event,metric_count,metric_total,metric_max,updated_at)
		 VALUES (%s,%s,%s,1,%d,%d,%s)
		 ON DUPLICATE KEY UPDATE metric_count=metric_count+1, metric_total=metric_total+VALUES(metric_total), metric_max=GREATEST(metric_max,VALUES(metric_max)), updated_at=VALUES(updated_at)",
		gmdate( 'Y-m-d' ), $path, $event, $value, $value, current_time( 'mysql', true )
	) );
	if ( false === $saved ) {
		return new WP_Error( 'metric_storage', 'The metric could not be recorded.', array( 'status' => 500 ) );
	}
	return new WP_REST_Response( null, 204 );
}

add_action( 'admin_menu', function() {
	add_menu_page( 'Toolkit Control', 'Toolkit Control', 'manage_options', 'toolkit-control', 'toolkit_render_metrics_page', 'dashicons-chart-area', 3 );
	add_submenu_page( 'toolkit-control', 'Site analytics', 'Site analytics', 'manage_options', 'toolkit-analytics', 'toolkit_render_analytics_page' );
} );

add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( false === strpos( $hook, 'toolkit' ) ) {
		return;
	}
	$path = get_stylesheet_directory() . '/assets/css/toolkit-admin.css';
	wp_enqueue_style( 'toolkit-admin', get_stylesheet_directory_uri() . '/assets/css/toolkit-admin.css', array(), filemtime( $path ) );
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

function toolkit_metrics_summary( $days = 30 ) {
	global $wpdb;
	toolkit_metrics_install_storage();
	$table = toolkit_metrics_table_name();
	$since = $days ? gmdate( 'Y-m-d', strtotime( '-' . max( 0, $days - 1 ) . ' days' ) ) : '1970-01-01';
	$rows  = $wpdb->get_results( $wpdb->prepare(
		"SELECT path,
		 SUM(CASE WHEN event='page_view' THEN metric_count ELSE 0 END) views,
		 SUM(CASE WHEN event LIKE 'interaction:%%' OR event='outbound_click' THEN metric_count ELSE 0 END) interactions,
		 SUM(CASE WHEN event='engaged_time' THEN metric_total ELSE 0 END) engaged,
		 SUM(CASE WHEN event='engaged_time' THEN metric_count ELSE 0 END) engaged_count,
		 MAX(CASE WHEN event='scroll_depth' THEN metric_max ELSE 0 END) scroll_depth,
		 SUM(CASE WHEN event='performance' THEN metric_total ELSE 0 END) load_total,
		 SUM(CASE WHEN event='performance' THEN metric_count ELSE 0 END) load_count
		 FROM {$table} WHERE metric_date >= %s GROUP BY path ORDER BY views DESC, interactions DESC", $since
	), ARRAY_A );
	return array( 'since' => $since, 'rows' => $rows ?: array() );
}

function toolkit_dashboard_counts() {
	global $wpdb;
	$app_table = function_exists( 'toolkit_application_table_name' ) ? toolkit_application_table_name() : '';
	$app_exists = $app_table && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $app_table ) ) === $app_table;
	return array(
		'applications' => $app_exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$app_table}" ) : 0,
		'app_new'      => $app_exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$app_table} WHERE workflow_status='new'" ) : 0,
		'app_followup' => $app_exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$app_table} WHERE status IN ('handoff_required','relay_failed','validation_failed')" ) : 0,
		'enquiries'    => count( get_posts( array( 'post_type' => 'toolkit_enquiry', 'post_status' => 'private', 'posts_per_page' => 1000, 'fields' => 'ids', 'meta_key' => '_toolkit_status', 'meta_value' => 'new' ) ) ),
		'speak_up'     => count( get_posts( array( 'post_type' => 'toolkit_speakup', 'post_status' => 'private', 'posts_per_page' => 1000, 'fields' => 'ids', 'meta_key' => '_toolkit_status', 'meta_value' => 'new' ) ) ),
	);
}

function toolkit_render_metrics_page() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You do not have permission to view Toolkit controls.' );
	$counts  = toolkit_dashboard_counts();
	$summary = toolkit_metrics_summary( 30 );
	$views   = array_sum( array_map( function( $row ) { return (int) $row['views']; }, $summary['rows'] ) );
	$top     = $summary['rows'][0]['path'] ?? 'No activity yet';
	echo '<div class="wrap toolkit-admin"><header class="toolkit-admin__hero"><div><p>Website operations centre</p><h1>Toolkit Control</h1><span>Admissions, visitor support, risk reports, content performance and rollout controls.</span></div><span class="toolkit-admin__state">' . esc_html( toolkit_is_demo_environment() ? 'Demo review' : 'Production' ) . '</span></header>';
	if ( isset( $_GET['updated'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Toolkit controls updated.</p></div>';
	echo '<nav class="toolkit-admin__quick" aria-label="Toolkit operations"><a href="' . esc_url( admin_url( 'admin.php?page=toolkit-applications' ) ) . '">Applications</a><a href="' . esc_url( admin_url( 'admin.php?page=toolkit-enquiries' ) ) . '">Enquiries</a><a href="' . esc_url( admin_url( 'admin.php?page=toolkit-speak-up' ) ) . '">Speak-up</a><a href="' . esc_url( admin_url( 'admin.php?page=toolkit-analytics' ) ) . '">Analytics</a><a href="' . esc_url( admin_url( 'admin.php?page=toolkit-chatbot' ) ) . '">Assistant</a></nav>';
	echo '<section class="toolkit-admin__stats">';
	printf( '<a href="%s"><span>Applications</span><strong>%s</strong><small>%s awaiting review</small></a>', esc_url( admin_url( 'admin.php?page=toolkit-applications' ) ), number_format_i18n( $counts['applications'] ), number_format_i18n( $counts['app_new'] ) );
	printf( '<a href="%s"><span>Admissions follow-up</span><strong>%s</strong><small>Handoff or delivery review</small></a>', esc_url( admin_url( 'admin.php?page=toolkit-applications&status=relay_failed' ) ), number_format_i18n( $counts['app_followup'] ) );
	printf( '<a href="%s"><span>New enquiries</span><strong>%s</strong><small>Open visitor requests</small></a>', esc_url( admin_url( 'admin.php?page=toolkit-enquiries' ) ), number_format_i18n( $counts['enquiries'] ) );
	printf( '<a href="%s"><span>Restricted reports</span><strong>%s</strong><small>New speak-up items</small></a>', esc_url( admin_url( 'admin.php?page=toolkit-speak-up' ) ), number_format_i18n( $counts['speak_up'] ) );
	printf( '<a href="%s"><span>30-day page views</span><strong>%s</strong><small>Aggregate first-party metric</small></a>', esc_url( admin_url( 'admin.php?page=toolkit-analytics' ) ), number_format_i18n( $views ) );
	printf( '<a href="%s"><span>Top path</span><strong class="toolkit-admin__path">%s</strong><small>Most viewed in 30 days</small></a>', esc_url( admin_url( 'admin.php?page=toolkit-analytics' ) ), esc_html( $top ) );
	echo '</section><section class="toolkit-admin__grid">';
	echo '<form class="toolkit-admin__panel" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="toolkit_save_controls">';
	wp_nonce_field( 'toolkit_save_controls' );
	echo '<div class="toolkit-admin__panel-heading"><div><small>Controlled rollout</small><h2>Release controls</h2></div><span class="toolkit-pill">' . esc_html( toolkit_theme_release() ) . '</span></div><p>Constants in <code>wp-config.php</code> take priority over these settings.</p>';
	toolkit_render_control_toggle( 'redesign', 'Modern redesign', eduma_child_redesign_enabled(), 'Activates the reviewed child-theme experience.' );
	toolkit_render_control_toggle( 'catalog', '2026 course catalogue', eduma_child_2026_catalog_enabled(), 'Activates the separately verified 2026 catalogue.' );
	toolkit_render_control_toggle( 'pricing', '2026 brochure pricing', eduma_child_2026_pricing_enabled(), 'Publishes the approved 7 July 2026 brochure fees on course pages. Keep off until the effective date is confirmed.' );
	echo '<p><button class="button button-primary" type="submit">Save release controls</button></p></form>';
	echo '<div class="toolkit-admin__panel"><div class="toolkit-admin__panel-heading"><div><small>Live health</small><h2>System status</h2></div><span class="toolkit-health toolkit-health--good">Operational</span></div><dl class="toolkit-admin__status">';
	printf( '<div><dt>Site</dt><dd>%s</dd></div>', esc_html( home_url() ) );
	printf( '<div><dt>Theme release</dt><dd>%s</dd></div>', esc_html( toolkit_theme_release() ) );
	printf( '<div><dt>WordPress</dt><dd>%s</dd></div>', esc_html( get_bloginfo( 'version' ) ) );
	printf( '<div><dt>Mzizi direct relay</dt><dd>%s</dd></div>', toolkit_mzizi_submission_enabled() ? 'Enabled' : 'Local storage + portal handoff' );
	echo '<div><dt>Application records</dt><dd>Retained; encrypted at rest</dd></div><div><dt>Metrics retention</dt><dd>Indefinite daily aggregates</dd></div><div><dt>Visitor profiling</dt><dd>Disabled — no raw IP or cookies stored</dd></div></dl></div>';
	echo '</section></div>';
}

function toolkit_render_analytics_page() {
	if ( ! current_user_can( 'manage_options' ) ) wp_die( 'You do not have permission to view Toolkit analytics.' );
	$range   = sanitize_key( $_GET['range'] ?? '30' );
	$allowed = array( '30' => 30, '90' => 90, '365' => 365, 'all' => 0 );
	$days    = $allowed[ $range ] ?? 30;
	$summary = toolkit_metrics_summary( $days );
	$views   = array_sum( array_map( function( $row ) { return (int) $row['views']; }, $summary['rows'] ) );
	$actions = array_sum( array_map( function( $row ) { return (int) $row['interactions']; }, $summary['rows'] ) );
	echo '<div class="wrap toolkit-admin"><header class="toolkit-admin__hero"><div><p>Privacy-conscious analytics</p><h1>Site performance</h1><span>Daily aggregate behaviour without raw IP addresses, cookies, or visitor profiles.</span></div><a class="toolkit-admin__state" href="' . esc_url( admin_url( 'admin.php?page=toolkit-control' ) ) . '">Dashboard</a></header>';
	echo '<form class="toolkit-admin__range" method="get"><input type="hidden" name="page" value="toolkit-analytics"><span>Reporting window</span>';
	foreach ( $allowed as $key => $value ) printf( '<button name="range" value="%s" class="%s">%s</button>', esc_attr( $key ), $range === $key ? 'is-active' : '', 'all' === $key ? 'All time' : esc_html( $key . ' days' ) );
	echo '</form><section class="toolkit-admin__stats">';
	printf( '<span class="toolkit-admin__stat"><span>Page views</span><strong>%s</strong><small>Selected period</small></span>', number_format_i18n( $views ) );
	printf( '<span class="toolkit-admin__stat"><span>Interactions</span><strong>%s</strong><small>Actions and outbound clicks</small></span>', number_format_i18n( $actions ) );
	printf( '<span class="toolkit-admin__stat"><span>Tracked paths</span><strong>%s</strong><small>Distinct site destinations</small></span>', number_format_i18n( count( $summary['rows'] ) ) );
	printf( '<span class="toolkit-admin__stat"><span>Retention</span><strong>All time</strong><small>No automatic aggregate deletion</small></span>' );
	echo '</section><section class="toolkit-admin__panel"><div class="toolkit-admin__panel-heading"><div><small>Page-level detail</small><h2>Content and journey performance</h2></div><span class="toolkit-pill">Since ' . esc_html( $summary['since'] ) . '</span></div><div class="toolkit-admin__table-wrap"><table class="widefat striped toolkit-admin__table"><thead><tr><th>Page</th><th>Views</th><th>Interactions</th><th>Avg. engaged</th><th>Max scroll</th><th>Avg. load</th></tr></thead><tbody>';
	foreach ( $summary['rows'] as $row ) {
		printf( '<tr><td><strong>%s</strong></td><td>%s</td><td>%s</td><td>%ss</td><td>%s%%</td><td>%sms</td></tr>', esc_html( $row['path'] ), number_format_i18n( $row['views'] ), number_format_i18n( $row['interactions'] ), number_format_i18n( $row['engaged_count'] ? $row['engaged'] / $row['engaged_count'] : 0 ), number_format_i18n( $row['scroll_depth'] ), number_format_i18n( $row['load_count'] ? $row['load_total'] / $row['load_count'] : 0 ) );
	}
	if ( ! $summary['rows'] ) echo '<tr><td colspan="6">No aggregate metrics have been recorded in this period.</td></tr>';
	echo '</tbody></table></div></section></div>';
}

function toolkit_render_control_toggle( $name, $label, $enabled, $description ) {
	printf( '<label style="display:block;padding:14px 0;border-top:1px solid #eee"><input type="checkbox" name="%1$s" value="1" %2$s> <strong>%3$s</strong><span style="display:block;margin:5px 0 0 24px;color:#646970">%4$s</span></label>', esc_attr( $name ), checked( $enabled, true, false ), esc_html( $label ), esc_html( $description ) );
}
