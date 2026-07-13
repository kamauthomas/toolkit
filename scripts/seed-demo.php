<?php
/**
 * Applies the project-owned WordPress settings after importing bfyigiln_new.sql.
 *
 * Usage: php scripts/seed-demo.php [--dry-run]
 *
 * This intentionally does not set siteurl/home. Those values differ between
 * local, demo, and production environments and are configured outside the seed.
 */

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "This script must be run from the command line.\n" );
	exit( 1 );
}

$dry_run = in_array( '--dry-run', $argv, true );

require_once dirname( __DIR__ ) . '/wp-load.php';

function toolkit_seed_update_option( $name, $value, $dry_run ) {
	if ( $dry_run ) {
		printf( "Would set option %s.\n", $name );
		return;
	}

	update_option( $name, $value );
	printf( "Set option %s.\n", $name );
}

function toolkit_seed_menu_matches( $menu ) {
	$expected = array( 'home', 'about us', 'our courses', 'contact us' );
	$items    = wp_get_nav_menu_items( $menu->term_id );
	$labels   = array();

	foreach ( (array) $items as $item ) {
		$labels[] = strtolower( trim( wp_strip_all_tags( html_entity_decode( $item->title ) ) ) );
	}

	return count( array_intersect( $expected, $labels ) ) >= 3;
}

$home_page = get_page_by_path( 'home', OBJECT, 'page' );

if ( ! $home_page ) {
	fwrite( STDERR, "Cannot find the published Home page. Database seed was not applied.\n" );
	exit( 1 );
}

toolkit_seed_update_option( 'stylesheet', 'eduma-child', $dry_run );
toolkit_seed_update_option( 'template', 'eduma', $dry_run );
toolkit_seed_update_option( 'current_theme', 'Eduma Child', $dry_run );
toolkit_seed_update_option( 'show_on_front', 'page', $dry_run );
toolkit_seed_update_option( 'page_on_front', (int) $home_page->ID, $dry_run );

if ( $dry_run ) {
	printf( "Would set page %d to the default template and clear Elementor render caches.\n", $home_page->ID );
} else {
	update_post_meta( $home_page->ID, '_wp_page_template', 'default' );
	delete_post_meta( $home_page->ID, '_elementor_element_cache' );
	delete_post_meta( $home_page->ID, '_elementor_css' );
	delete_post_meta( $home_page->ID, '_elementor_page_assets' );
	clean_post_cache( $home_page->ID );
	printf( "Configured Home page %d.\n", $home_page->ID );
}

$selected_menu = null;
foreach ( wp_get_nav_menus() as $menu ) {
	if ( toolkit_seed_menu_matches( $menu ) ) {
		$selected_menu = $menu;
		break;
	}
}

if ( ! $selected_menu ) {
	fwrite( STDERR, "No primary navigation menu matching the expected Toolkit pages was found.\n" );
	exit( 1 );
}

if ( $dry_run ) {
	printf( "Would assign menu '%s' to the primary location.\n", $selected_menu->name );
	exit( 0 );
}

$locations            = get_theme_mod( 'nav_menu_locations', array() );
$locations['primary'] = (int) $selected_menu->term_id;
set_theme_mod( 'nav_menu_locations', $locations );

printf( "Assigned menu '%s' to primary.\n", $selected_menu->name );
printf( "Seed complete.\n" );
