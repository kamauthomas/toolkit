<?php
/**
 * Plugin Name: Toolkit Runtime Hardening
 * Description: Restricted maintenance and uploads while the legacy parent awaits a licensed update.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) define( 'DISALLOW_FILE_EDIT', true );

/** Reject traversal before an application handler can interpret it. */
function toolkit_runtime_has_traversal( $path ) {
	for ( $i = 0; $i < 3; $i++ ) {
		if ( preg_match( '~(?:^|[/\\\\])\.\.(?:[/\\\\]|$)|\x00~', $path ) ) return true;
		$decoded = rawurldecode( $path );
		if ( $decoded === $path ) break;
		$path = $decoded;
	}
	return (bool) preg_match( '~(?:^|[/\\\\])\.\.(?:[/\\\\]|$)|\x00~', $path );
}

$request_path = explode( '?', (string) ( $_SERVER['REQUEST_URI'] ?? '' ), 2 )[0];
if ( toolkit_runtime_has_traversal( $request_path ) ) {
	status_header( 400 );
	exit( 'Invalid request path.' );
}

// This old theme migration changes settings from GET without authorization.
// It is unnecessary on an already migrated, Git-managed deployment.
if ( isset( $_REQUEST['thim-auto-compile-sass-to-css'] ) ) {
	status_header( 403 );
	exit( 'This maintenance operation is disabled.' );
}

/** Filenames are data; never accept executable, active HTML or SVG uploads. */
function toolkit_runtime_upload_error( $name ) {
	if ( '' === $name || str_contains( $name, "\0" ) || str_contains( $name, '/' ) || str_contains( $name, '\\' ) || toolkit_runtime_has_traversal( $name ) ) return 'Choose a file with a plain filename.';
	if ( preg_match( '/^\.|\.(?:php\d*|phtml|phar|pht|cgi|pl|py|sh|htaccess|user\.ini|html?|js|svgz?)(?:\.|$)/i', $name ) ) return 'This file type cannot be uploaded.';
	return '';
}

function toolkit_runtime_check_upload( $file ) {
	$error = toolkit_runtime_upload_error( (string) ( $file['name'] ?? '' ) );
	if ( $error ) {
		$file['error'] = $error;
		return $file;
	}
	// WordPress supplies its content/extension checks after this prefilter.
	// Also require real image bytes for raster names, including double extensions.
	$extension = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );
	$image_types = array( 'jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG, 'png' => IMAGETYPE_PNG, 'gif' => IMAGETYPE_GIF, 'webp' => IMAGETYPE_WEBP, 'avif' => IMAGETYPE_AVIF );
	if ( isset( $image_types[ $extension ] ) && empty( $file['error'] ) ) {
		$image = @getimagesize( (string) ( $file['tmp_name'] ?? '' ) );
		if ( ! $image || $image[2] !== $image_types[ $extension ] ) $file['error'] = 'The image contents do not match the filename.';
	}
	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'toolkit_runtime_check_upload' );
add_filter( 'wp_handle_sideload_prefilter', 'toolkit_runtime_check_upload' );
add_filter( 'upload_mimes', static function ( $mimes ) {
	foreach ( $mimes as $extensions => $mime ) {
		if ( preg_match( '/(?:^|\|)(?:svgz?|html?|js|php\d*|phtml|phar)(?:\||$)/i', $extensions ) ) unset( $mimes[ $extensions ] );
	}
	return $mimes;
} );
