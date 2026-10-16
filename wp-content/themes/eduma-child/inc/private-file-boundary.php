<?php
/** A database pathname must still resolve to the expected private artifact. */
function toolkit_confined_private_file( $path, array $roots, $expected_filename ) {
	if ( ! is_string( $path ) || '' === $path || str_contains( $path, "\0" ) || str_contains( $path, '\\' ) || preg_match( '~(?:^|/)\.\.(?:/|$)~', $path ) || is_link( $path ) ) return false;
	$resolved = realpath( $path );
	if ( false === $resolved || ! is_file( $resolved ) || ! is_readable( $resolved ) || basename( $resolved ) !== $expected_filename ) return false;
	foreach ( $roots as $root ) {
		$allowed = realpath( $root );
		if ( false !== $allowed && dirname( $resolved ) === $allowed ) return $resolved;
	}
	return false;
}
