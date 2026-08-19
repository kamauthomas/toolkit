<?php
/**
 * Automated calling-letter generation for new admissions applications.
 * Generation is native PHP (ZipArchive + DOMDocument, no external library
 * or shell_exec) against the existing template's exact placeholder text, so
 * output matches the manually-produced letters byte-for-byte in layout.
 */

const TOOLKIT_CALLING_LETTER_WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

function toolkit_calling_letter_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'toolkit_calling_letters';
}

function toolkit_calling_letter_install_storage() {
	global $wpdb;
	$version = '1.1.0';
	if ( $version === get_option( 'toolkit_calling_letter_storage_version' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$table   = toolkit_calling_letter_table_name();
	$charset = $wpdb->get_charset_collate();
	/*
	 * application_id is UNIQUE: one letter row per application, upserted via
	 * REPLACE-style logic in toolkit_calling_letter_generate(). This is what
	 * keeps concurrent/duplicate submissions from racing each other into two
	 * rows for the same applicant.
	 */
	$sql = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		application_id bigint(20) unsigned NOT NULL,
		status varchar(24) NOT NULL DEFAULT 'pending',
		file_path varchar(255) DEFAULT NULL,
		pdf_path varchar(255) DEFAULT NULL,
		email_status varchar(16) NOT NULL DEFAULT 'disabled',
		sms_status varchar(16) NOT NULL DEFAULT 'disabled',
		print_status varchar(16) NOT NULL DEFAULT 'disabled',
		last_error text DEFAULT NULL,
		generated_at datetime DEFAULT NULL,
		updated_at datetime NOT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY application_id (application_id),
		KEY status (status)
	) {$charset};";
	dbDelta( $sql );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		update_option( 'toolkit_calling_letter_storage_version', $version, false );
	}
}
add_action( 'after_switch_theme', 'toolkit_calling_letter_install_storage' );
add_action( 'init', 'toolkit_calling_letter_install_storage', 3 );
/*
 * Unconditional (not version-gated like install_storage above) so the deny
 * rules exist on every request from the first page load, before any letter
 * or template file is ever written there. toolkit_calling_letter_private_dir()
 * no-ops past its file_exists() checks once the files are in place, so this
 * is cheap on every request.
 */
add_action( 'after_switch_theme', 'toolkit_calling_letter_private_dir' );
add_action( 'init', 'toolkit_calling_letter_private_dir', 3 );

/**
 * Private storage root for the template and generated letters, kept
 * entirely OUTSIDE the web-served document root (one level above ABSPATH,
 * i.e. the account home directory, a sibling of public_html/demo.*) rather
 * than under wp-content/uploads.
 *
 * This install's uploads directory is served by a static-file layer in
 * front of the app server that serves recognised static extensions
 * (.docx, .zip, etc.) directly from disk, bypassing .htaccess/mod_rewrite
 * entirely — verified by curl: a deny-all nested .htaccess AND a root
 * .htaccess RewriteRule with [F,L] both still returned 200 for a .docx
 * under wp-content/uploads. Because these letters carry applicant PII
 * (name, phone), a web-server rule that a front-end layer can silently
 * ignore is not an acceptable protection. A directory outside any vhost's
 * document root cannot be served by any web-server config, so it needs no
 * such rule to be correct. The .htaccess/index.php below are kept anyway
 * as harmless defense-in-depth in case this ever moves back in-webroot.
 */
function toolkit_calling_letter_private_dir() {
	$dir = trailingslashit( dirname( ABSPATH ) ) . 'toolkit-private-storage/calling-letters';
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	$htaccess = $dir . '/.htaccess';
	if ( ! file_exists( $htaccess ) ) {
		file_put_contents( $htaccess, "# Apache 2.4+\n<IfModule authz_core_module>\n    Require all denied\n</IfModule>\n\n# Apache 2.2\n<IfModule !authz_core_module>\n    Deny from all\n</IfModule>\n" );
	}
	$index = $dir . '/index.php';
	if ( ! file_exists( $index ) ) {
		file_put_contents( $index, "<?php\n// Silence is golden.\n" );
	}
	return $dir;
}

function toolkit_calling_letter_template_path() {
	return toolkit_calling_letter_private_dir() . '/_template.docx';
}

/**
 * Generated letters go in a per-site subdirectory of the private root.
 * dirname( ABSPATH ) is the account home, which is the SAME directory for
 * every site on this cPanel account — production lives in public_html/ and
 * demo in demo.toolkitafrica.ac.ke/, so both resolve their private root to
 * /home/<account>/toolkit-private-storage. Without this split, demo test
 * letters would be written alongside real applicants' letters, and a
 * reference collision between the two databases could overwrite a genuine
 * one. The template and the decoded-image cache stay shared: they are
 * identical for every site and contain no applicant data.
 */
function toolkit_calling_letter_output_dir() {
	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$slug = preg_replace( '/[^a-z0-9._-]/', '', strtolower( $host ) );
	$dir  = toolkit_calling_letter_private_dir() . '/' . ( '' !== $slug ? $slug : 'site' );
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	return $dir;
}

function toolkit_calling_letter_file_path( $application_id, $reference ) {
	$safe_reference = preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $reference );
	/* Filename is keyed by the application's own auto-increment ID, which is
	 * unique by definition — no risk of two concurrent submissions
	 * colliding on a name, unlike name-based filenames. */
	return toolkit_calling_letter_output_dir() . '/' . (int) $application_id . '-' . $safe_reference . '.docx';
}

/* The PDF is the artifact applicants and the registry actually use; the
 * .docx is kept alongside it as the editable copy. */
function toolkit_calling_letter_pdf_file_path( $application_id, $reference ) {
	return preg_replace( '/\.docx$/', '.pdf', toolkit_calling_letter_file_path( $application_id, $reference ) );
}

function toolkit_calling_letter_ordinal_date( $timestamp = null ) {
	$timestamp = $timestamp ?: current_time( 'timestamp' );
	$day       = (int) gmdate( 'j', $timestamp );
	$suffix    = 'th';
	if ( ! in_array( $day % 100, array( 11, 12, 13 ), true ) ) {
		$suffix = array( 1 => 'st', 2 => 'nd', 3 => 'rd' )[ $day % 10 ] ?? 'th';
	}
	return $day . $suffix . gmdate( ' F Y', $timestamp );
}

/* ---- ZIP access layer ----
 * A .docx is a ZIP. ext-zip (ZipArchive) is NOT available on every host — it
 * is missing on this project's shared hosting, which runs native PHP where
 * cPanel cannot add modules, and the host has disabled per-domain isolation
 * so demo cannot be configured separately from production. Rather than
 * change the account's PHP runtime for one extension, this falls back to
 * PclZip, the pure-PHP ZIP library WordPress bundles and uses itself for
 * exactly this case (see _unzip_file_pclzip() in wp-admin/includes/file.php).
 * ZipArchive is still preferred where present.
 */

function toolkit_calling_letter_use_ziparchive() {
	/* The constant lets the PclZip path be exercised in testing on machines
	 * that do have ext-zip. */
	return class_exists( 'ZipArchive' ) && ! defined( 'TOOLKIT_CALLING_LETTER_FORCE_PCLZIP' );
}

function toolkit_calling_letter_load_pclzip() {
	if ( ! class_exists( 'PclZip' ) ) {
		require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
	}
}

/** Entry names inside the archive. */
function toolkit_calling_letter_zip_entries( $path ) {
	if ( toolkit_calling_letter_use_ziparchive() ) {
		$zip = new ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			return array();
		}
		$names = array();
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$names[] = $zip->getNameIndex( $i );
		}
		$zip->close();
		return $names;
	}
	toolkit_calling_letter_load_pclzip();
	$archive = new PclZip( $path );
	$list    = $archive->listContent();
	return is_array( $list ) ? wp_list_pluck( $list, 'filename' ) : array();
}

/** Contents of one entry, or false. */
function toolkit_calling_letter_zip_read( $path, $entry ) {
	if ( toolkit_calling_letter_use_ziparchive() ) {
		$zip = new ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			return false;
		}
		$data = $zip->getFromName( $entry );
		$zip->close();
		return $data;
	}
	toolkit_calling_letter_load_pclzip();
	$archive = new PclZip( $path );
	$list    = $archive->extract( PCLZIP_OPT_EXTRACT_AS_STRING, PCLZIP_OPT_BY_NAME, $entry );
	if ( ! is_array( $list ) || ! isset( $list[0]['content'] ) ) {
		return false;
	}
	return $list[0]['content'];
}

function toolkit_calling_letter_rmdir( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	foreach ( array_diff( (array) scandir( $dir ), array( '.', '..' ) ) as $item ) {
		$path = $dir . '/' . $item;
		is_dir( $path ) ? toolkit_calling_letter_rmdir( $path ) : unlink( $path );
	}
	rmdir( $dir );
}

function toolkit_calling_letter_files_in( $dir ) {
	$found = array();
	foreach ( array_diff( (array) scandir( $dir ), array( '.', '..' ) ) as $item ) {
		$path = $dir . '/' . $item;
		if ( is_dir( $path ) ) {
			$found = array_merge( $found, toolkit_calling_letter_files_in( $path ) );
		} else {
			$found[] = $path;
		}
	}
	return $found;
}

/**
 * Copy $template to $destination with $replacements (entry name => new
 * contents) applied. Returns true, or a string error message.
 */
function toolkit_calling_letter_zip_write( $template, $destination, array $replacements ) {
	if ( file_exists( $destination ) ) {
		unlink( $destination );
	}

	if ( toolkit_calling_letter_use_ziparchive() ) {
		if ( ! copy( $template, $destination ) ) {
			return 'Could not create the calling-letter output file.';
		}
		$zip = new ZipArchive();
		if ( true !== $zip->open( $destination ) ) {
			return 'Could not open the calling-letter output file as a zip archive.';
		}
		foreach ( $replacements as $entry => $contents ) {
			$zip->deleteName( $entry );
			$zip->addFromString( $entry, $contents );
		}
		$zip->close();
		return true;
	}

	/* PclZip cannot rewrite a single entry in place, so the archive is
	 * expanded to a scratch directory, the changed parts are overwritten and
	 * the whole tree is re-zipped. The scratch directory lives inside the
	 * private storage root and is always removed. */
	toolkit_calling_letter_load_pclzip();
	$work = toolkit_calling_letter_private_dir() . '/.build-' . wp_generate_password( 10, false, false );
	if ( ! wp_mkdir_p( $work ) ) {
		return 'Could not create a temporary directory to build the calling letter.';
	}

	$archive = new PclZip( $template );
	if ( 0 === $archive->extract( PCLZIP_OPT_PATH, $work ) ) {
		toolkit_calling_letter_rmdir( $work );
		return 'Could not expand the calling-letter template: ' . $archive->errorInfo( true );
	}

	foreach ( $replacements as $entry => $contents ) {
		$target = $work . '/' . ltrim( $entry, '/' );
		wp_mkdir_p( dirname( $target ) );
		if ( false === file_put_contents( $target, $contents ) ) {
			toolkit_calling_letter_rmdir( $work );
			return 'Could not write ' . $entry . ' while building the calling letter.';
		}
	}

	$files = toolkit_calling_letter_files_in( $work );
	if ( ! $files ) {
		toolkit_calling_letter_rmdir( $work );
		return 'The expanded calling-letter template contained no files.';
	}

	$output = new PclZip( $destination );
	$added  = $output->create( $files, PCLZIP_OPT_REMOVE_PATH, $work );
	toolkit_calling_letter_rmdir( $work );
	if ( 0 === $added ) {
		return 'Could not repackage the calling letter: ' . $output->errorInfo( true );
	}
	return true;
}

/* ---- Header contact-box fix (DOMDocument, no external library) ----
 * The template's header contact textbox ("Textbox 6") is slightly too short
 * for its own text and clips the final contact line when rendered outside
 * Word (e.g. LibreOffice PDF export, used for the print channel). Ported
 * verbatim from the working expand_header_contact_box() in
 * sms/calling/generate_calling_letters.py so generated letters keep the same
 * fix the manual batch process already applied. */

function toolkit_calling_letter_find_header_entry( $path ) {
	foreach ( toolkit_calling_letter_zip_entries( $path ) as $name ) {
		if ( $name && preg_match( '#^word/header\d+\.xml$#', $name ) ) {
			$content = toolkit_calling_letter_zip_read( $path, $name );
			if ( false !== $content && false !== strpos( (string) $content, 'Textbox 6' ) ) {
				return array( $name, $content );
			}
		}
	}
	return null;
}

function toolkit_calling_letter_expand_header_contact_box( $header_xml ) {
	$dom = new DOMDocument();
	$dom->preserveWhiteSpace = true;
	if ( ! $dom->loadXML( $header_xml ) ) {
		return null;
	}
	$wp_ns = 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing';
	$xpath = new DOMXPath( $dom );
	$xpath->registerNamespace( 'w', TOOLKIT_CALLING_LETTER_WORD_NS );
	$xpath->registerNamespace( 'wp', $wp_ns );

	$doc_pr = $xpath->query( '//wp:docPr[@name="Textbox 6"]' )->item( 0 );
	$anchor = $doc_pr ? $doc_pr->parentNode : null;
	if ( ! $anchor ) {
		return null;
	}

	$pos_offset = $xpath->query( './wp:positionV/wp:posOffset', $anchor )->item( 0 );
	if ( $pos_offset ) {
		$pos_offset->textContent = '80000';
	}

	/*
	 * Widen the box as well as heightening it. At the template's original
	 * width (cx 3084195 at x 4141129) the contact line breaks mid-number,
	 * rendering "Mob: +254-" / "711-802 855" across two lines. The box is
	 * shifted left and widened while keeping the same right margin. A4 is
	 * 7559675 EMU wide, so 2850000 + 4375000 = 7225000 preserves the original
	 * right edge. The left edge stays clear of the logo, which ends around
	 * 1.8M EMU. This is ~42% wider than the template and is what keeps both
	 * the landline and the mobile on a single line each.
	 */
	$pos_h = $xpath->query( './wp:positionH/wp:posOffset', $anchor )->item( 0 );
	if ( $pos_h ) {
		$pos_h->textContent = '2850000';
	}
	$extent = $xpath->query( './wp:extent', $anchor )->item( 0 );
	if ( $extent ) {
		$extent->setAttribute( 'cx', '4375000' );
		$extent->setAttribute( 'cy', '1400000' );
	}
	$shape_ext = $xpath->query( ".//*[local-name()='spPr']/*[local-name()='xfrm']/*[local-name()='ext']", $anchor )->item( 0 );
	if ( $shape_ext ) {
		$shape_ext->setAttribute( 'cx', '4375000' );
		$shape_ext->setAttribute( 'cy', '1400000' );
	}

	/*
	 * Stop the contact numbers breaking across lines. In the template the
	 * numbers are split over several runs — "Kikuyu Tel: +254-7" / "31" /
	 * "-" / "802 855" / " / Mob: +254-7" / "11" / "-802-855" — and every
	 * hyphen and space in them is a valid break opportunity, so the line
	 * wrapped mid-number ("Mob: +254-" then "711-802-855"). Widening the box
	 * alone only moves which number breaks. The hyphens are not wanted in the
	 * numbers anyway, so every separator inside them becomes a U+00A0
	 * no-break space: this drops the hyphens and makes the numbers unbreakable
	 * in one step, giving "+254 731 802 855 / Mob: +254 711 802 855". Runs
	 * consisting only of digits, spaces and hyphens are number fragments by
	 * definition; the "+254-" prefix is handled explicitly because it shares
	 * a run with ordinary words.
	 */
	foreach ( $xpath->query( ".//*[local-name()='txbxContent']//w:t", $anchor ) as $text_run ) {
		$value = $text_run->textContent;
		if ( '' === trim( $value ) ) {
			continue;
		}
		if ( preg_match( '/^[0-9\s\-]+$/', $value ) ) {
			$value = str_replace( array( '-', ' ' ), "\u{00A0}", $value );
		}
		$value = str_replace( '+254-', "+254\u{00A0}", $value );
		/* Keep each label attached to its own number. */
		$value = str_replace( array( 'Tel: ', 'Mob: ' ), array( "Tel:\u{00A0}", "Mob:\u{00A0}" ), $value );
		if ( $value !== $text_run->textContent ) {
			$text_run->textContent = $value;
		}
	}

	$paragraphs     = $xpath->query( ".//*[local-name()='txbxContent']/*[local-name()='p']", $anchor );
	$spacing_values = array(
		array( 'before' => '0' ),
		array( 'before' => '30', 'line' => '280' ),
		array( 'line' => '200', 'lineRule' => 'exact' ),
	);
	foreach ( $paragraphs as $index => $paragraph ) {
		if ( ! isset( $spacing_values[ $index ] ) ) {
			continue;
		}
		$spacing = $xpath->query( './w:pPr/w:spacing', $paragraph )->item( 0 );
		if ( ! $spacing ) {
			continue;
		}
		foreach ( $spacing_values[ $index ] as $attr => $value ) {
			$spacing->setAttributeNS( TOOLKIT_CALLING_LETTER_WORD_NS, 'w:' . $attr, $value );
		}
	}

	$new_xml = $dom->saveXML( $dom->documentElement );
	return $new_xml ? '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . $new_xml : null;
}

/* ---- Low-level DOCX rendering (ZipArchive + DOMDocument, no library) ---- */

function toolkit_calling_letter_docx_paragraphs( DOMXPath $xpath ) {
	return $xpath->query( '/w:document/w:body/w:p' );
}

function toolkit_calling_letter_replace_run( DOMElement $paragraph, DOMXPath $xpath, $needle, $replacement ) {
	foreach ( $xpath->query( './/w:r', $paragraph ) as $run ) {
		foreach ( $xpath->query( './w:t', $run ) as $text_node ) {
			if ( false !== strpos( $text_node->textContent, $needle ) ) {
				$text_node->textContent = str_replace( $needle, $replacement, $text_node->textContent );
				return true;
			}
		}
	}
	return false;
}

function toolkit_calling_letter_paragraph_text( DOMElement $paragraph, DOMXPath $xpath ) {
	$out = '';
	foreach ( $xpath->query( './/w:t', $paragraph ) as $node ) {
		$out .= $node->textContent;
	}
	return $out;
}

/**
 * Render one calling letter from the template into $destination_path.
 * $tokens: full_name, phone, first_name, course, letter_date.
 * Returns true on success, or a string error message on failure.
 */
function toolkit_calling_letter_render_docx( $template_path, $destination_path, array $tokens ) {
	if ( ! is_readable( $template_path ) ) {
		return 'Calling-letter template is not readable at ' . $template_path . '.';
	}

	$document_xml = toolkit_calling_letter_zip_read( $template_path, 'word/document.xml' );
	if ( false === $document_xml || '' === (string) $document_xml ) {
		return 'Calling-letter template is missing word/document.xml.';
	}

	$dom = new DOMDocument();
	$dom->preserveWhiteSpace = true;
	$dom->formatOutput       = false;
	if ( ! $dom->loadXML( $document_xml ) ) {
		return 'Calling-letter template document.xml could not be parsed.';
	}
	$xpath = new DOMXPath( $dom );
	$xpath->registerNamespace( 'w', TOOLKIT_CALLING_LETTER_WORD_NS );

	$paragraphs = toolkit_calling_letter_docx_paragraphs( $xpath );
	if ( $paragraphs->length < 41 ) {
		return 'Calling-letter template layout has changed (only ' . $paragraphs->length . ' top-level paragraphs found).';
	}

	$date_paragraph = $paragraphs->item( 6 );
	if ( ! toolkit_calling_letter_replace_run( $date_paragraph, $xpath, '13th July 2026', $tokens['letter_date'] ) ) {
		return 'Could not locate the date placeholder in the template.';
	}
	foreach ( $xpath->query( './/w:r', $date_paragraph ) as $run ) {
		$text_node = $xpath->query( './w:t', $run )->item( 0 );
		if ( ! $text_node ) {
			continue;
		}
		$text_node->textContent = ( false === strpos( $text_node->textContent, $tokens['letter_date'] ) ) ? '' : ltrim( $text_node->textContent );
	}
	$ppr = $xpath->query( './w:pPr', $date_paragraph )->item( 0 );
	if ( ! $ppr ) {
		$ppr = $dom->createElementNS( TOOLKIT_CALLING_LETTER_WORD_NS, 'w:pPr' );
		$date_paragraph->insertBefore( $ppr, $date_paragraph->firstChild );
	}
	$jc = $xpath->query( './w:jc', $ppr )->item( 0 );
	if ( ! $jc ) {
		$jc = $dom->createElementNS( TOOLKIT_CALLING_LETTER_WORD_NS, 'w:jc' );
		$ppr->appendChild( $jc );
	}
	$jc->setAttributeNS( TOOLKIT_CALLING_LETTER_WORD_NS, 'w:val', 'right' );

	$contact_paragraph = $paragraphs->item( 7 );
	if ( ! toolkit_calling_letter_replace_run( $contact_paragraph, $xpath, 'Applicant Name', $tokens['full_name'] ) ) {
		return 'Could not locate the applicant-name placeholder in the template.';
	}
	if ( ! toolkit_calling_letter_replace_run( $contact_paragraph, $xpath, 'Phone Number', $tokens['phone'] ) ) {
		return 'Could not locate the phone placeholder in the template.';
	}

	$greeting_paragraph = $paragraphs->item( 9 );
	if ( ! toolkit_calling_letter_replace_run( $greeting_paragraph, $xpath, '………………………………...', $tokens['first_name'] ) ) {
		return 'Could not locate the greeting placeholder in the template.';
	}

	$course_paragraph = $paragraphs->item( 15 );
	if ( ! toolkit_calling_letter_replace_run( $course_paragraph, $xpath, 'Course Name', $tokens['course'] ) ) {
		return 'Could not locate the course placeholder in the template.';
	}

	foreach ( array( 40, 39, 36, 34, 32, 31, 12, 10, 8 ) as $index ) {
		$node = $paragraphs->item( $index );
		if ( $node && $node->parentNode ) {
			$node->parentNode->removeChild( $node );
		}
	}

	$paragraphs           = toolkit_calling_letter_docx_paragraphs( $xpath );
	$signature_paragraph = null;
	foreach ( $paragraphs as $paragraph ) {
		if ( false !== strpos( toolkit_calling_letter_paragraph_text( $paragraph, $xpath ), 'THEOPHILE SYLVESTER' ) ) {
			$signature_paragraph = $paragraph;
			break;
		}
	}
	if ( $signature_paragraph ) {
		$ppr = $xpath->query( './w:pPr', $signature_paragraph )->item( 0 );
		if ( ! $ppr ) {
			$ppr = $dom->createElementNS( TOOLKIT_CALLING_LETTER_WORD_NS, 'w:pPr' );
			$signature_paragraph->insertBefore( $ppr, $signature_paragraph->firstChild );
		}
		$spacing = $xpath->query( './w:spacing', $ppr )->item( 0 );
		if ( ! $spacing ) {
			$spacing = $dom->createElementNS( TOOLKIT_CALLING_LETTER_WORD_NS, 'w:spacing' );
			$ppr->appendChild( $spacing );
		}
		$spacing->setAttributeNS( TOOLKIT_CALLING_LETTER_WORD_NS, 'w:before', '893' ); // 0.62in in twips.
	}

	$new_xml = $dom->saveXML( $dom->documentElement );
	if ( ! $new_xml ) {
		return 'Could not serialize the rendered calling letter.';
	}
	$new_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . $new_xml;

	$replacements = array( 'word/document.xml' => $new_xml );

	$header_entry = toolkit_calling_letter_find_header_entry( $template_path );
	if ( $header_entry ) {
		list( $header_name, $header_xml ) = $header_entry;
		$new_header_xml = toolkit_calling_letter_expand_header_contact_box( $header_xml );
		if ( $new_header_xml ) {
			$replacements[ $header_name ] = $new_header_xml;
		}
	}

	return toolkit_calling_letter_zip_write( $template_path, $destination_path, $replacements );
}

/* ---- Channel toggles (admin-controlled, same on/off pattern as Mzizi relay) ---- */

function toolkit_calling_letter_channel_enabled( $channel ) {
	return '1' === (string) get_option( "toolkit_calling_letter_{$channel}_enabled", 'email' === $channel ? '1' : '0' );
}

/* ---- Delivery channels ---- */

function toolkit_calling_letter_send_email( $letter_row, $payload ) {
	if ( ! toolkit_calling_letter_channel_enabled( 'email' ) ) {
		return 'disabled';
	}
	if ( empty( $payload['email'] ) || ! is_email( $payload['email'] ) ) {
		return 'failed';
	}
	/* Attach the PDF: it is the format applicants can open on any phone
	 * without an Office app, and it cannot be edited in transit. */
	$attachment = ( ! empty( $letter_row->pdf_path ) && is_readable( $letter_row->pdf_path ) ) ? $letter_row->pdf_path : $letter_row->file_path;
	if ( empty( $attachment ) || ! is_readable( $attachment ) ) {
		return 'failed';
	}
	$subject = 'Your Toolkit for Skills & Innovation calling letter';
	$body    = sprintf(
		"Dear %s,\n\nCongratulations on your successful application to The Toolkit for Skills & Innovation. Your official calling letter is attached to this email.\n\nWe look forward to welcoming you.\n\nThe Toolkit for Skills & Innovation",
		$payload['first_name'] ?? 'Applicant'
	);
	$sent = wp_mail( $payload['email'], $subject, $body, array(), array( $attachment ) );
	return $sent ? 'sent' : 'failed';
}

/* SMS and print are intentionally not wired to a real gateway/workflow yet —
 * disbursement channel is still to be decided. Both stay 'disabled' unless
 * explicitly turned on, and turning them on today only marks records as
 * queued for a human/back-office process, they do not attempt delivery. */
function toolkit_calling_letter_send_sms( $letter_row, $payload ) {
	return toolkit_calling_letter_channel_enabled( 'sms' ) ? 'queued' : 'disabled';
}

function toolkit_calling_letter_send_print( $letter_row, $payload ) {
	return toolkit_calling_letter_channel_enabled( 'print' ) ? 'queued' : 'disabled';
}

/**
 * Deliver an already-generated letter through the enabled channels.
 *
 * Delivery is deliberately separate from generation. Regenerating a letter
 * (an admin rebuilding the file to pick up a corrected course label, say)
 * must never re-email the applicant, and clicking Regenerate three times must
 * not send three copies. So generation writes the files and this function —
 * called explicitly — is the only thing that ever contacts the applicant.
 *
 * $force controls resend: the automatic on-submission send passes false so a
 * letter already emailed is never sent twice; the admin "Resend" action
 * passes true because a human has deliberately chosen to send it again.
 */
function toolkit_calling_letter_deliver( $application_id, $force = false ) {
	global $wpdb;
	$application_id = (int) $application_id;
	$letter_row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . toolkit_calling_letter_table_name() . ' WHERE application_id = %d', $application_id ) );
	if ( ! $letter_row || 'generated' !== $letter_row->status ) {
		return new WP_Error( 'calling_letter_not_ready', 'No generated letter is available to send for this application.' );
	}
	/* Already delivered and not a deliberate resend: do nothing, so the
	 * on-submit path and a later course-name-resolver retry cannot double-send. */
	if ( ! $force && 'sent' === $letter_row->email_status ) {
		return true;
	}
	$record = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . toolkit_application_table_name() . ' WHERE id = %d', $application_id ) );
	if ( ! $record ) {
		return new WP_Error( 'calling_letter_no_application', 'Application record not found.' );
	}
	$payload = toolkit_application_decrypt_payload( $record->payload );
	if ( is_wp_error( $payload ) ) {
		return $payload;
	}
	$email_status = toolkit_calling_letter_send_email( $letter_row, $payload );
	$sms_status   = toolkit_calling_letter_send_sms( $letter_row, $payload );
	$print_status = toolkit_calling_letter_send_print( $letter_row, $payload );
	toolkit_calling_letter_upsert( $application_id, array(
		'email_status' => $email_status,
		'sms_status'   => $sms_status,
		'print_status' => $print_status,
	) );
	if ( 'sent' === $email_status ) {
		toolkit_application_log_event( $application_id, 'calling_letter_emailed', 'Calling letter emailed to the applicant.' );
	} elseif ( 'failed' === $email_status ) {
		toolkit_application_log_event( $application_id, 'calling_letter_email_failed', 'Calling letter email could not be sent.' );
	}
	return true;
}

/* ---- Orchestration ---- */

/**
 * DOCX rendering needs a ZIP implementation and DOMDocument. ext-zip is not
 * present on every host, so PclZip (bundled with WordPress) is used as the
 * fallback — see the ZIP access layer above. Checked before any generation
 * attempt rather than letting a missing class throw a fatal mid-submission.
 */
function toolkit_calling_letter_renderer_available() {
	if ( ! class_exists( 'DOMDocument' ) ) {
		return false;
	}
	if ( class_exists( 'ZipArchive' ) ) {
		return true;
	}
	return file_exists( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
}

/**
 * Entry point for the submission hook. Generation must NEVER be able to
 * break an application submission: the applicant's record is already saved
 * by the time this fires, so a fatal here would return an error page to an
 * applicant whose data was in fact stored — inviting a duplicate re-submit.
 * Everything is therefore contained: failures are recorded against the
 * letter row and logged, and the submission response proceeds regardless.
 */
function toolkit_calling_letter_generate_safely( $application_id ) {
	try {
		$result = toolkit_calling_letter_generate( $application_id, true );
		if ( is_wp_error( $result ) && 'calling_letter_deferred' !== $result->get_error_code() ) {
			error_log( 'Toolkit calling letter: ' . $result->get_error_message() );
		}
	} catch ( Throwable $e ) {
		/* Throwable, not Exception: PHP 7+ Errors (missing class, type
		 * errors) are the realistic failure mode here and do not extend
		 * Exception. */
		toolkit_calling_letter_upsert( (int) $application_id, array(
			'status'     => 'failed',
			'last_error' => 'Unexpected error: ' . $e->getMessage(),
		) );
		error_log( 'Toolkit calling letter fatal for application ' . (int) $application_id . ': ' . $e->getMessage() );
	}
}

/**
 * Generate (or regenerate) the calling letter for one application.
 * Safe to call multiple times for the same $application_id — upserts a
 * single row (UNIQUE application_id) rather than creating duplicates, so a
 * retry from the course-name resolver never conflicts with the original
 * on-submit attempt.
 */
function toolkit_calling_letter_generate( $application_id, $deliver = false ) {
	global $wpdb;
	toolkit_calling_letter_install_storage();

	$application_id = (int) $application_id;
	if ( ! toolkit_calling_letter_renderer_available() ) {
		$message = 'No ZIP support is available to write DOCX files: neither the PHP zip extension nor WordPress\'s bundled PclZip could be loaded.';
		toolkit_calling_letter_upsert( $application_id, array(
			'status'     => 'failed',
			'last_error' => $message,
		) );
		return new WP_Error( 'calling_letter_no_zip', $message );
	}
	$table          = toolkit_application_table_name();
	$record         = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $application_id ) );
	if ( ! $record ) {
		return new WP_Error( 'calling_letter_no_application', 'Application record not found.' );
	}
	$payload = toolkit_application_decrypt_payload( $record->payload );
	if ( is_wp_error( $payload ) ) {
		return $payload;
	}

	if ( empty( $payload['course_name'] ) ) {
		/* Course label not resolved yet — defer. The hourly resolver will
		 * fire toolkit_application_names_resolved once it is, which retries
		 * this exact function for the same application. Record the deferred
		 * state so admins can see why nothing generated yet. */
		toolkit_calling_letter_upsert( $application_id, array(
			'status'     => 'awaiting_course_name',
			'last_error' => null,
		) );
		return new WP_Error( 'calling_letter_deferred', 'Course name not yet resolved; will retry automatically.' );
	}

	$full_name  = trim( ( $payload['first_name'] ?? '' ) . ' ' . ( $payload['middle_name'] ?? '' ) . ' ' . ( $payload['surname'] ?? '' ) );
	$full_name  = preg_replace( '/\s+/', ' ', $full_name );
	$phone      = $payload['primary_phone'] ?? '';
	$tokens     = array(
		'full_name'   => $full_name,
		'phone'       => $phone,
		'first_name'  => $payload['first_name'] ?? '',
		'course'      => $payload['course_name'],
		'letter_date' => toolkit_calling_letter_ordinal_date(),
	);

	$destination = toolkit_calling_letter_file_path( $application_id, $record->reference );
	$result      = toolkit_calling_letter_render_docx( toolkit_calling_letter_template_path(), $destination, $tokens );
	if ( true !== $result ) {
		toolkit_calling_letter_upsert( $application_id, array(
			'status'     => 'failed',
			'last_error' => $result,
		) );
		toolkit_application_log_event( $application_id, 'calling_letter_failed', $result );
		return new WP_Error( 'calling_letter_render_failed', $result );
	}

	/* PDF is rendered from the same $tokens, so the two artifacts cannot
	 * disagree on any applicant detail. A PDF failure is recorded but does
	 * not fail the letter: the .docx is already on disk and printable. */
	$pdf_destination = toolkit_calling_letter_pdf_file_path( $application_id, $record->reference );
	$pdf_result      = toolkit_calling_letter_render_pdf( $pdf_destination, $tokens );
	if ( true !== $pdf_result ) {
		$pdf_destination = null;
		toolkit_application_log_event( $application_id, 'calling_letter_pdf_failed', is_string( $pdf_result ) ? $pdf_result : 'PDF rendering failed.' );
		error_log( 'Toolkit calling letter PDF: ' . ( is_string( $pdf_result ) ? $pdf_result : 'unknown error' ) );
	}

	$letter_id = toolkit_calling_letter_upsert( $application_id, array(
		'status'       => 'generated',
		'file_path'    => $destination,
		'pdf_path'     => $pdf_destination,
		'generated_at' => current_time( 'mysql', true ),
		'last_error'   => null,
	) );
	toolkit_application_log_event( $application_id, 'calling_letter_generated', 'Calling letter file generated.' );

	/* Delivery is separate from generation: only the on-submission path (and
	 * the course-name-resolver retry) asks to deliver. Regeneration never
	 * emails; a human uses the explicit Send action for that. */
	if ( $deliver ) {
		$delivery = toolkit_calling_letter_deliver( $application_id, false );
		if ( is_wp_error( $delivery ) ) {
			error_log( 'Toolkit calling letter delivery: ' . $delivery->get_error_message() );
		}
	}

	return true;
}

/**
 * Upsert-by-application_id. Relies on the UNIQUE KEY on application_id, so
 * concurrent calls for two *different* applications never contend, and two
 * calls for the *same* application (submit + resolver retry) safely update
 * the same row instead of creating a duplicate.
 */
function toolkit_calling_letter_upsert( $application_id, array $fields ) {
	global $wpdb;
	$table = toolkit_calling_letter_table_name();
	$now   = current_time( 'mysql', true );
	$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE application_id = %d", $application_id ) );
	$fields['updated_at'] = $now;
	if ( $existing ) {
		$wpdb->update( $table, $fields, array( 'id' => (int) $existing ) );
		return (int) $existing;
	}
	$fields['application_id'] = $application_id;
	$wpdb->insert( $table, $fields );
	return (int) $wpdb->insert_id;
}

/* Fires from toolkit_application_store() right after a new application is
 * durably saved — this is the "as soon as an applicant submits" trigger,
 * independent of whether Mzizi relay is enabled/queued/etc. */
add_action( 'toolkit_application_stored', 'toolkit_calling_letter_generate_safely' );

/* Fires from toolkit_application_resolve_names() on success — catches the
 * case where course_name wasn't available yet at submission time. */
add_action( 'toolkit_application_names_resolved', 'toolkit_calling_letter_generate_safely' );

/* ---- Secure download handler ---- */

add_action( 'admin_post_toolkit_calling_letter_download', function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to download calling letters.' );
	}
	$letter_id = absint( $_GET['letter'] ?? 0 );
	check_admin_referer( 'toolkit_calling_letter_download_' . $letter_id );
	global $wpdb;
	$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . toolkit_calling_letter_table_name() . ' WHERE id = %d', $letter_id ) );
	if ( ! $row ) {
		wp_die( 'Calling letter file not found.' );
	}
	/* PDF is the default: 'docx' has to be asked for explicitly. */
	$format = 'docx' === ( $_GET['format'] ?? 'pdf' ) ? 'docx' : 'pdf';
	$path   = 'pdf' === $format ? $row->pdf_path : $row->file_path;
	if ( empty( $path ) || ! is_readable( $path ) ) {
		wp_die( 'Calling letter file not found.' );
	}
	nocache_headers();
	header( 'Content-Type: ' . ( 'pdf' === $format ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ) );
	header( 'Content-Disposition: attachment; filename="' . basename( $path ) . '"' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'Content-Length: ' . filesize( $path ) );
	readfile( $path );
	exit;
} );

/* ---- Manual regenerate action ---- */

add_action( 'admin_post_toolkit_calling_letter_regenerate', function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to regenerate calling letters.' );
	}
	$application_id = absint( $_POST['application'] ?? 0 );
	check_admin_referer( 'toolkit_calling_letter_regenerate_' . $application_id );
	$result = toolkit_calling_letter_generate( $application_id );
	$args   = array( 'page' => 'toolkit-calling-letters' );
	$args[ is_wp_error( $result ) ? 'regenerate_error' : 'regenerated' ] = is_wp_error( $result ) ? rawurlencode( $result->get_error_message() ) : 1;
	wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	exit;
} );

/* ---- Explicit (re)send ----
 * Deliberately separate from Regenerate: this is the only admin control that
 * contacts the applicant, and it forces delivery even if the letter was
 * already emailed, because a human has chosen to send it again. */
add_action( 'admin_post_toolkit_calling_letter_send', function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to send calling letters.' );
	}
	$application_id = absint( $_POST['application'] ?? 0 );
	check_admin_referer( 'toolkit_calling_letter_send_' . $application_id );
	$result = toolkit_calling_letter_deliver( $application_id, true );
	$args   = array( 'page' => 'toolkit-calling-letters' );
	if ( is_wp_error( $result ) ) {
		$args['send_error'] = rawurlencode( $result->get_error_message() );
	} else {
		$row = $GLOBALS['wpdb']->get_row( $GLOBALS['wpdb']->prepare( 'SELECT email_status FROM ' . toolkit_calling_letter_table_name() . ' WHERE application_id = %d', $application_id ) );
		$args[ ( $row && 'sent' === $row->email_status ) ? 'sent' : 'send_error' ] = ( $row && 'sent' === $row->email_status ) ? 1 : rawurlencode( 'The letter could not be emailed. Check the applicant email and mail delivery.' );
	}
	wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
	exit;
} );

/* ---- Settings save ---- */

add_action( 'admin_post_toolkit_calling_letter_save_settings', function() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to update calling-letter settings.' );
	}
	check_admin_referer( 'toolkit_calling_letter_save_settings' );
	foreach ( array( 'email', 'sms', 'print' ) as $channel ) {
		update_option( "toolkit_calling_letter_{$channel}_enabled", isset( $_POST[ "{$channel}_enabled" ] ) ? '1' : '0', false );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'toolkit-calling-letters', 'settings_updated' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
} );

/* ---- Admin UI ---- */

add_action( 'admin_menu', function() {
	add_submenu_page( 'toolkit-control', 'Calling Letters', 'Calling Letters', 'manage_options', 'toolkit-calling-letters', 'toolkit_calling_letter_render_admin_page' );
}, 20 );

function toolkit_calling_letter_status_label( $status ) {
	$labels = array(
		'pending'               => 'Pending',
		'awaiting_course_name'  => 'Awaiting course name',
		'generated'             => 'Generated',
		'failed'                => 'Failed',
	);
	return $labels[ $status ] ?? ucwords( str_replace( '_', ' ', (string) $status ) );
}

function toolkit_calling_letter_channel_label( $status ) {
	$labels = array(
		'sent'     => 'Sent',
		'queued'   => 'Queued',
		'failed'   => 'Failed',
		'disabled' => 'Disabled',
	);
	return $labels[ $status ] ?? ucfirst( (string) $status );
}

function toolkit_calling_letter_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have permission to view calling letters.' );
	}
	global $wpdb;
	toolkit_calling_letter_install_storage();
	$table = toolkit_calling_letter_table_name();
	$app_table = toolkit_application_table_name();

	toolkit_application_admin_header( 'Calling Letters', 'Automatic admission calling letters, generated on submission and emailed to applicants.' );

	if ( ! toolkit_calling_letter_renderer_available() ) {
		echo '<div class="notice notice-error"><p><strong>Letter generation is unavailable on this server.</strong> No ZIP support could be loaded, so DOCX files cannot be written. Applications are still received and stored normally — only letter generation is affected.</p></div>';
	}
	if ( isset( $_GET['regenerated'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Calling letter regenerated.</p></div>';
	if ( isset( $_GET['regenerate_error'] ) ) echo '<div class="notice notice-error"><p><strong>Could not regenerate:</strong> ' . esc_html( wp_unslash( $_GET['regenerate_error'] ) ) . '</p></div>';
	if ( isset( $_GET['settings_updated'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Delivery channel settings saved.</p></div>';
	if ( isset( $_GET['sent'] ) ) echo '<div class="notice notice-success is-dismissible"><p>Calling letter sent to the applicant.</p></div>';
	if ( isset( $_GET['send_error'] ) ) echo '<div class="notice notice-error"><p><strong>Could not send:</strong> ' . esc_html( wp_unslash( $_GET['send_error'] ) ) . '</p></div>';

	$total     = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	$generated = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'generated'" );
	$failed    = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'failed'" );
	$emailed   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE email_status = 'sent'" );

	echo '<section class="toolkit-admin__stats">';
	printf( '<span class="toolkit-admin__stat"><span>Total letters</span><strong>%s</strong><small>One per application</small></span>', number_format_i18n( $total ) );
	printf( '<span class="toolkit-admin__stat"><span>Generated</span><strong>%s</strong><small>Ready to view/email</small></span>', number_format_i18n( $generated ) );
	printf( '<span class="toolkit-admin__stat"><span>Emailed</span><strong>%s</strong><small>Delivered to applicant inbox</small></span>', number_format_i18n( $emailed ) );
	printf( '<span class="toolkit-admin__stat"><span>Needs attention</span><strong>%s</strong><small>Failed or awaiting course name</small></span>', number_format_i18n( $failed ) );
	echo '</section>';

	echo '<section class="toolkit-security-layout"><form class="toolkit-admin__panel toolkit-security-form" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="toolkit_calling_letter_save_settings">';
	wp_nonce_field( 'toolkit_calling_letter_save_settings' );
	echo '<div class="toolkit-admin__panel-heading"><div><small>Delivery</small><h2>Channels</h2></div></div><p>Generation always runs automatically on submission. Delivery channels are independent switches — email can send today; SMS and print are recorded as queued only until a real gateway/workflow is connected.</p>';
	echo '<div class="toolkit-security-switches">';
	echo '<label><input type="checkbox" name="email_enabled" value="1" ' . checked( toolkit_calling_letter_channel_enabled( 'email' ), true, false ) . '> <span><strong>Email</strong><small>Sends the letter as an attachment via the site\'s normal outgoing mail.</small></span></label>';
	echo '<label><input type="checkbox" name="sms_enabled" value="1" ' . checked( toolkit_calling_letter_channel_enabled( 'sms' ), true, false ) . '> <span><strong>Bulk SMS</strong><small>No gateway connected yet — marks records queued only.</small></span></label>';
	echo '<label><input type="checkbox" name="print_enabled" value="1" ' . checked( toolkit_calling_letter_channel_enabled( 'print' ), true, false ) . '> <span><strong>Print</strong><small>No automated workflow yet — marks records queued for manual handling.</small></span></label>';
	echo '</div><div class="toolkit-security-actions"><button class="button button-primary button-hero" type="submit">Save channel settings</button></div></form></section>';

	$page  = max( 1, absint( $_GET['paged'] ?? 1 ) );
	$limit = 40;
	$items = $wpdb->get_results( $wpdb->prepare(
		"SELECT l.*, a.reference, a.created_at AS application_created_at FROM {$table} l JOIN {$app_table} a ON a.id = l.application_id ORDER BY l.updated_at DESC LIMIT %d OFFSET %d",
		$limit, ( $page - 1 ) * $limit
	) );

	echo '<div class="toolkit-admin__table-wrap"><table class="widefat striped toolkit-admin__table"><thead><tr><th>Reference</th><th>Applicant</th><th>Status</th><th>Email</th><th>SMS</th><th>Print</th><th>Generated</th><th></th></tr></thead><tbody>';
	if ( ! $items ) {
		echo '<tr><td colspan="8">No calling letters yet.</td></tr>';
	}
	foreach ( $items as $row ) {
		$app     = $wpdb->get_row( $wpdb->prepare( "SELECT payload FROM {$app_table} WHERE id = %d", $row->application_id ) );
		$payload = $app ? toolkit_application_decrypt_payload( $app->payload ) : null;
		$name    = ( $payload && ! is_wp_error( $payload ) ) ? trim( $payload['first_name'] . ' ' . $payload['surname'] ) : 'Encrypted record';
		printf(
			'<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>',
			esc_html( $row->reference ),
			esc_html( $name ),
			esc_html( toolkit_calling_letter_status_label( $row->status ) ),
			esc_html( toolkit_calling_letter_channel_label( $row->email_status ) ),
			esc_html( toolkit_calling_letter_channel_label( $row->sms_status ) ),
			esc_html( toolkit_calling_letter_channel_label( $row->print_status ) ),
			$row->generated_at ? esc_html( get_date_from_gmt( $row->generated_at, 'd M Y H:i' ) ) : '—'
		);
		if ( 'generated' === $row->status ) {
			$download_link = function( $format, $label, $primary ) use ( $row ) {
				printf(
					'<a class="button button-small%s" href="%s">%s</a> ',
					$primary ? ' button-primary' : '',
					esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'toolkit_calling_letter_download', 'letter' => $row->id, 'format' => $format ), admin_url( 'admin-post.php' ) ), 'toolkit_calling_letter_download_' . $row->id ) ),
					esc_html( $label )
				);
			};
			if ( $row->pdf_path ) {
				$download_link( 'pdf', 'PDF', true );
			}
			if ( $row->file_path ) {
				$download_link( 'docx', 'Word', false );
			}
		}
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">';
		echo '<input type="hidden" name="action" value="toolkit_calling_letter_regenerate">';
		echo '<input type="hidden" name="application" value="' . esc_attr( $row->application_id ) . '">';
		wp_nonce_field( 'toolkit_calling_letter_regenerate_' . $row->application_id );
		echo '<button class="button button-small" type="submit">Regenerate</button></form>';
		if ( 'generated' === $row->status ) {
			$send_label = ( 'sent' === $row->email_status ) ? 'Resend' : 'Send';
			echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline">';
			echo '<input type="hidden" name="action" value="toolkit_calling_letter_send">';
			echo '<input type="hidden" name="application" value="' . esc_attr( $row->application_id ) . '">';
			wp_nonce_field( 'toolkit_calling_letter_send_' . $row->application_id );
			printf( '<button class="button button-small" type="submit">%s</button></form>', esc_html( $send_label ) );
		}
		echo '</td></tr>';
	}
	echo '</tbody></table></div></div>';
}
