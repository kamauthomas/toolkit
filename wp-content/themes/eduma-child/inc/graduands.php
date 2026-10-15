<?php
/** Approved public graduands list; unpublished uploads stay in private options. */
defined( 'ABSPATH' ) || exit;

function toolkit_graduands_validate_rows( $rows ) {
	if ( ! is_array( $rows ) || ! $rows || count( $rows ) > 2000 ) {
		return new WP_Error( 'rows', 'Supply between 1 and 2,000 graduands.' );
	}
	$clean = array();
	$seen  = array();
	foreach ( $rows as $index => $row ) {
		if ( ! is_array( $row ) || array_keys( $row ) !== array( 'display_name', 'course' ) ) {
			return new WP_Error( 'columns', 'Use only the display_name and course columns.' );
		}
		foreach ( $row as $key => $value ) {
			if ( ! is_string( $value ) || ! preg_match( '//u', $value ) || preg_match( '/[<>\x00-\x1F]/u', $value ) ) {
				return new WP_Error( 'text', 'Invalid text on data row ' . ( $index + 1 ) . '.' );
			}
			$row[ $key ] = trim( preg_replace( '/\s+/u', ' ', $value ) );
			if ( '' === $row[ $key ] || strlen( $row[ $key ] ) > 240 ) {
				return new WP_Error( 'length', 'Each name and course is required, up to 240 bytes, on data row ' . ( $index + 1 ) . '.' );
			}
		}
		$key = function_exists( 'mb_strtolower' ) ? mb_strtolower( $row['display_name'] . '|' . $row['course'], 'UTF-8' ) : strtolower( $row['display_name'] . '|' . $row['course'] );
		if ( isset( $seen[ $key ] ) ) {
			return new WP_Error( 'duplicate', 'Repeated name and course on data row ' . ( $index + 1 ) . '. Review the source before publishing.' );
		}
		$seen[ $key ] = true;
		$clean[]     = $row;
	}
	usort( $clean, static function ( $a, $b ) {
		return strnatcasecmp( $a['course'], $b['course'] ) ?: strnatcasecmp( $a['display_name'], $b['display_name'] );
	} );
	return $clean;
}

function toolkit_graduands_parse_csv( $body ) {
	if ( ! is_string( $body ) || strlen( $body ) > 1024 * 1024 || ! preg_match( '//u', $body ) ) {
		return new WP_Error( 'file', 'Supply a UTF-8 CSV no larger than 1 MB.' );
	}
	$stream = fopen( 'php://temp', 'r+' );
	fwrite( $stream, preg_replace( '/^\xEF\xBB\xBF/', '', $body ) );
	rewind( $stream );
	$header = fgetcsv( $stream, 0, ',', '"', '' );
	if ( array( 'display_name', 'course' ) !== $header ) {
		fclose( $stream );
		return new WP_Error( 'header', 'The first line must be exactly: display_name,course' );
	}
	$rows = array();
	while ( false !== ( $fields = fgetcsv( $stream, 0, ',', '"', '' ) ) ) {
		if ( array( null ) === $fields ) {
			continue;
		}
		if ( 2 !== count( $fields ) || count( $rows ) >= 2000 ) {
			fclose( $stream );
			return new WP_Error( 'columns', 'Each row needs exactly two columns, with no more than 2,000 rows.' );
		}
		$rows[] = array( 'display_name' => $fields[0], 'course' => $fields[1] );
	}
	fclose( $stream );
	return toolkit_graduands_validate_rows( $rows );
}

function toolkit_graduands_published( $respect_visibility = true ) {
	if ( $respect_visibility && ! get_option( 'toolkit_graduands_2026_visible', true ) ) {
		return array();
	}
	$published = get_option( 'toolkit_graduands_2026_published', array() );
	if ( ! is_array( $published ) || empty( $published['approved'] ) || empty( $published['published_at'] ) ) {
		return array();
	}
	$rows = toolkit_graduands_validate_rows( $published['rows'] ?? array() );
	return is_wp_error( $rows ) ? array() : array( 'rows' => $rows, 'published_at' => $published['published_at'] );
}

add_action( 'admin_menu', static function () {
	add_submenu_page( 'toolkit-control', 'Graduands 2026', 'Graduands 2026', 'manage_options', 'toolkit-graduands', 'toolkit_graduands_admin' );
} );

function toolkit_graduands_admin() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Administrator access required.' );
	}
	$message = '';
	$error   = '';
	if ( 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
		check_admin_referer( 'toolkit_graduands_manage' );
		$action = sanitize_key( wp_unslash( $_POST['graduands_action'] ?? '' ) );
		if ( 'preview' === $action ) {
			$file = $_FILES['graduands_csv'] ?? array();
			if ( UPLOAD_ERR_OK !== ( $file['error'] ?? UPLOAD_ERR_NO_FILE ) || empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) || ( $file['size'] ?? 0 ) > 1024 * 1024 || 'csv' !== strtolower( pathinfo( $file['name'] ?? '', PATHINFO_EXTENSION ) ) ) {
				$error = 'Choose a CSV file no larger than 1 MB.';
			} else {
				$rows = toolkit_graduands_parse_csv( file_get_contents( $file['tmp_name'] ) );
				if ( is_wp_error( $rows ) ) {
					$error = $rows->get_error_message();
				} else {
					update_option( 'toolkit_graduands_2026_draft', array( 'rows' => $rows, 'revision' => hash( 'sha256', wp_json_encode( $rows ) ) ), false );
					$message = 'Preview saved. The public list has not changed.';
				}
			}
		} elseif ( 'publish' === $action ) {
			$draft = get_option( 'toolkit_graduands_2026_draft', array() );
			$rows  = toolkit_graduands_validate_rows( $draft['rows'] ?? array() );
			$revision = sanitize_text_field( wp_unslash( $_POST['revision'] ?? '' ) );
			if ( is_wp_error( $rows ) || empty( $_POST['approved'] ) || empty( $draft['revision'] ) || ! hash_equals( $draft['revision'], $revision ) ) {
				$error = 'Review the latest preview and confirm that these names and courses are approved for public display.';
			} else {
				update_option( 'toolkit_graduands_2026_published', array( 'rows' => $rows, 'approved' => true, 'published_at' => gmdate( 'c' ) ), false );
				$message = get_option( 'toolkit_graduands_2026_visible', true ) ? 'The approved graduands list is published.' : 'The approved list is saved. Turn on public display in the QR controls to show it.';
				do_action( 'litespeed_purge_all' );
				wp_cache_flush();
			}
		} elseif ( 'withdraw' === $action && ! empty( $_POST['withdraw_confirmed'] ) ) {
			delete_option( 'toolkit_graduands_2026_published' );
			$message = 'The public list has been withdrawn. The preview is retained.';
			do_action( 'litespeed_purge_all' );
			wp_cache_flush();
		}
	}
	$draft = get_option( 'toolkit_graduands_2026_draft', array() );
	$published = toolkit_graduands_published( false );
	?>
	<div class="wrap toolkit-admin"><h1>Class of 2026 · Graduands</h1>
	<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=toolkit-control' ) ); ?>">← Toolkit Control</a></p>
	<?php toolkit_graduands_qr_panel(); ?>
	<p>Upload the approved list as UTF-8 CSV with exactly two columns: <code>display_name,course</code>. Use public display names and course titles only. Uploading creates a private preview; publishing is a separate confirmation.</p>
	<p><a href="<?php echo esc_url( home_url( '/graduation/#graduands' ) ); ?>" target="_blank" rel="noopener">Open the public graduands section</a> · The QR address stays the same when the list changes.</p>
	<?php if ( $message ) : ?><div class="notice notice-success"><p><?php echo esc_html( $message ); ?></p></div><?php endif; ?>
	<?php if ( $error ) : ?><div class="notice notice-error"><p><?php echo esc_html( $error ); ?></p></div><?php endif; ?>
	<h2>1. Prepare a preview</h2><form method="post" enctype="multipart/form-data"><?php wp_nonce_field( 'toolkit_graduands_manage' ); ?><input type="hidden" name="graduands_action" value="preview"><label for="graduands-csv">Approved CSV (up to 1 MB)</label> <input id="graduands-csv" type="file" name="graduands_csv" accept=".csv,text/csv" required><?php submit_button( 'Upload for preview' ); ?></form>
	<?php if ( ! empty( $draft['rows'] ) ) : ?>
	<h2>2. Review all <?php echo esc_html( count( $draft['rows'] ) ); ?> names and courses</h2><table class="widefat striped"><thead><tr><th scope="col">Display name</th><th scope="col">Course</th></tr></thead><tbody><?php foreach ( $draft['rows'] as $row ) : ?><tr><td><?php echo esc_html( $row['display_name'] ); ?></td><td><?php echo esc_html( $row['course'] ); ?></td></tr><?php endforeach; ?></tbody></table>
	<h2>3. Publish the reviewed list</h2><form method="post"><?php wp_nonce_field( 'toolkit_graduands_manage' ); ?><input type="hidden" name="graduands_action" value="publish"><input type="hidden" name="revision" value="<?php echo esc_attr( $draft['revision'] ); ?>"><label><input type="checkbox" name="approved" value="1" required> I confirm these names and courses are approved for public display.</label><?php submit_button( 'Publish approved list' ); ?></form>
	<?php endif; ?>
	<?php if ( $published ) : ?><h2>Published list</h2><p><?php echo esc_html( count( $published['rows'] ) ); ?> names are visible on the website.</p><form method="post"><?php wp_nonce_field( 'toolkit_graduands_manage' ); ?><input type="hidden" name="graduands_action" value="withdraw"><label><input type="checkbox" name="withdraw_confirmed" value="1" required> Withdraw the current public list.</label><?php submit_button( 'Withdraw list', 'secondary' ); ?></form><?php endif; ?>
	</div>
	<?php
}

add_action( 'wp_enqueue_scripts', static function () {
	if ( 'graduation' !== get_query_var( 'toolkit_showcase' ) ) {
		return;
	}
	$base = get_stylesheet_directory_uri();
	wp_enqueue_style( 'toolkit-graduands', $base . '/assets/css/graduands.css', array(), toolkit_theme_release() );
	wp_enqueue_script( 'toolkit-graduands', $base . '/assets/js/graduands.js', array(), toolkit_theme_release(), true );
} );

/** Shared with the existing Toolkit Control QR cards. */
function toolkit_graduands_qr_panel() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$published = toolkit_graduands_published( false );
	$visible = (bool) get_option( 'toolkit_graduands_2026_visible', true );
	$assets = get_stylesheet_directory_uri() . '/assets/images/graduands-2026-qr';
	$status = $published ? ( $visible ? 'Published · ' : 'Display off · ' ) . count( $published['rows'] ) . ' approved names' : 'Awaiting approved names';
	?>
	<section class="toolkit-admin__panel" aria-labelledby="graduands-qr-title"><h2 id="graduands-qr-title">Graduands 2026 QR code</h2><img src="<?php echo esc_url( $assets . '.png' ); ?>" alt="QR code for the Toolkit Class of 2026 graduands list" width="220" height="220">
	<p><strong><?php echo esc_html( $status ); ?></strong></p><p><a class="button" href="<?php echo esc_url( $assets . '.png' ); ?>" download="toolkit-graduands-2026-qr.png">Download QR PNG</a> <a class="button" href="<?php echo esc_url( $assets . '.svg' ); ?>" download="toolkit-graduands-2026-qr.svg">Download print SVG</a></p>
	<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=toolkit-graduands' ) ); ?>">Manage names and courses</a> <a class="button" href="https://toolkitafrica.ac.ke/graduation/#graduands" target="_blank" rel="noopener noreferrer">Open live list</a></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'toolkit_graduands_display' ); ?><input type="hidden" name="action" value="toolkit_graduands_display"><label><input type="checkbox" name="public_display" value="1" <?php checked( $visible ); ?>> Show the approved list at the QR destination</label><?php submit_button( 'Save display setting', 'secondary', 'submit', false ); ?></form>
	<p>The QR always points to the live graduation page. Hiding the list preserves the approved names; visitors see the awaiting-list message. Uploading a preview does not publish it. No scan tracking is added to this QR.</p></section>
	<?php
}

function toolkit_graduands_update_display() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Administrator access required.' );
	}
	check_admin_referer( 'toolkit_graduands_display' );
	update_option( 'toolkit_graduands_2026_visible', ! empty( $_POST['public_display'] ), false );
	do_action( 'litespeed_purge_all' );
	wp_cache_flush();
}

add_action( 'admin_post_toolkit_graduands_display', static function () {
	toolkit_graduands_update_display();
	wp_safe_redirect( admin_url( 'admin.php?page=toolkit-control&updated=1' ) );
	exit;
} );
