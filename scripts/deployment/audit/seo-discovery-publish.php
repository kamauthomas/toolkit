<?php
/** Local CLI release source only; never upload this as a public/private endpoint. */
function toolkit_seo_publish_resource() {
	$data = toolkit_seo_graduation_update();
	$content = wp_kses_post( $data['content'] );
	$digest = hash( 'sha256', json_encode( array( $data['title'], $data['excerpt'], $content, $data['image'] ), JSON_THROW_ON_ERROR ) );
	$meta = array(
		'_toolkit_seo_release' => '2026.10.27.1', '_toolkit_seo_resource_digest' => $digest,
		'_yoast_wpseo_title' => $data['seo_title'], '_yoast_wpseo_metadesc' => $data['excerpt'],
		'_yoast_wpseo_opengraph-image' => $data['image'], '_yoast_wpseo_twitter-image' => $data['image'],
	);
	$existing = get_page_by_path( $data['slug'], OBJECT, array( 'post', 'page', 'attachment' ) );
	$created = false;
	if ( $existing ) {
		if ( $existing->post_type !== 'post' || ! in_array( $existing->post_status, array( 'draft', 'publish' ), true )
			|| $existing->post_title !== $data['title'] || $existing->post_excerpt !== $data['excerpt'] || $existing->post_content !== $content ) throw new RuntimeException( 'resource_slug_or_content_conflict' );
		foreach ( $meta as $key => $value ) {
			if ( get_post_meta( $existing->ID, $key, true ) !== $value ) throw new RuntimeException( 'resource_ownership_or_metadata_conflict' );
		}
		$id = $existing->ID;
	} else {
		$id = wp_insert_post( wp_slash( array(
			'post_type' => 'post', 'post_status' => 'draft', 'post_name' => $data['slug'], 'post_title' => $data['title'],
			'post_excerpt' => $data['excerpt'], 'post_content' => $content, 'post_author' => 0,
			'comment_status' => 'closed', 'ping_status' => 'closed', 'meta_input' => $meta,
		) ), true );
		if ( is_wp_error( $id ) || ! $id ) throw new RuntimeException( 'resource_draft_failed' );
		$created = true;
	}
	if ( get_post_field( 'post_status', $id ) !== 'publish' ) {
		$published = wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ), true );
		if ( is_wp_error( $published ) || ! $published ) throw new RuntimeException( 'resource_publish_failed' );
	}
	if ( get_post_field( 'post_name', $id ) !== $data['slug'] ) {
		// A simultaneous human publication must not produce a public duplicate.
		wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ), true );
		throw new RuntimeException( 'resource_concurrent_slug_conflict_retained_draft' );
	}
	return array( 'post_id' => (int) $id, 'created' => $created, 'url' => get_permalink( $id ) );
}

if ( defined( 'TOOLKIT_SEO_PUBLISH_LIBRARY_ONLY' ) ) return;
try {
	$site = $argv[1] ?? '';
	if ( ! in_array( $site, array( '/home/bfyigiln/public_html', '/home/bfyigiln/demo.toolkitafrica.ac.ke' ), true ) ) throw new RuntimeException( 'invalid_release_root' );
	define( 'DISABLE_WP_CRON', true );
	require $site . '/wp-load.php';
	if ( toolkit_theme_release() !== '2026.10.27.1' || ! eduma_child_redesign_enabled() || ! eduma_child_2026_catalog_enabled() ) throw new RuntimeException( 'release_or_catalogue_not_ready' );
	foreach ( (array) get_option( 'active_plugins', array() ) as $plugin ) {
		if ( preg_match( '~(?:mailpoet|mailster|newsletter|subscribe2|jetpack|onesignal|pushengage)~i', $plugin ) ) throw new RuntimeException( 'subscriber_plugin_requires_separate_review' );
	}
	add_filter( 'pre_wp_mail', '__return_false', PHP_INT_MAX );
	$result = toolkit_seo_publish_resource();
	if ( class_exists( 'WPSEO_Sitemaps_Cache' ) ) {
		WPSEO_Sitemaps_Cache::invalidate( 'page' );
		WPSEO_Sitemaps_Cache::invalidate( 'post' );
	}
	wp_cache_flush();
	do_action( 'litespeed_purge_all' );
	$result['seo_release'] = 'passed';
	$result['catalogue_slugs'] = array_keys( eduma_child_course_catalog() );
	echo json_encode( $result, JSON_THROW_ON_ERROR ), "\n";
} catch ( Throwable $error ) {
	echo json_encode( array( 'seo_release' => 'failed', 'error_type' => get_class( $error ), 'code' => $error instanceof RuntimeException ? $error->getMessage() : 'runtime_failed' ) ), "\n";
	exit( 1 );
}
