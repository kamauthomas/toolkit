<?php
/** The supplied Class of 2026 announcement, with browser-side repeat limits. */
function toolkit_graduation_announcement_available() {
	return eduma_child_is_custom_surface()
		&& ! get_query_var( 'toolkit_reception' )
		&& ! get_query_var( 'toolkit_speak_up' )
		&& ! is_page( array( 'toolkit-courses-apply-today', 'contact' ) )
		&& time() < strtotime( '2026-10-22T10:00:00Z' )
		&& eduma_child_switch( 'TOOLKIT_GRADUATION_ANNOUNCEMENT_ENABLED', 'toolkit_graduation_announcement_enabled', true );
}

add_action( 'wp_enqueue_scripts', function() {
	if ( ! toolkit_graduation_announcement_available() ) {
		return;
	}
	$base = get_stylesheet_directory_uri();
	foreach ( array( 'css', 'js' ) as $type ) {
		$file = '/assets/' . $type . '/graduation-announcement.' . $type;
		$version = toolkit_asset_version( get_stylesheet_directory() . $file );
		if ( 'css' === $type ) {
			wp_enqueue_style( 'toolkit-graduation-announcement', $base . $file, array( 'eduma-child-brand-tokens' ), $version );
		} else {
			wp_enqueue_script( 'toolkit-graduation-announcement', $base . $file, array(), $version, true );
		}
	}
}, 30 );

add_action( 'wp_footer', function() {
	if ( toolkit_graduation_announcement_available() ) {
		include get_stylesheet_directory() . '/template-parts/graduation-announcement.php';
	}
}, 10 );
