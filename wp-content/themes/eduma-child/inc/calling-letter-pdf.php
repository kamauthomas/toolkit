<?php
/**
 * Native PDF rendering for admissions calling letters.
 *
 * Why hand-written rather than a library: the host runs native PHP where
 * cPanel cannot add extensions (no ext-zip, no Imagick), Composer is not
 * available on the account, and the one PDF library already on disk is
 * vendored inside an unrelated plugin (eventer/vendors/dompdf) — depending on
 * a plugin's private vendor tree from the theme would break the moment that
 * plugin is updated or deactivated. The letter is a single fixed A4 page, so
 * a purpose-built writer is smaller than any library's bootstrap and carries
 * no third-party CVE surface.
 *
 * Only PDF features the letter actually needs are implemented: the base-14
 * Helvetica family (no font embedding), FlateDecode streams, RGB images with
 * a soft mask for transparency, filled rectangles, and text placement.
 */

/* ---- Glyph metrics ----
 * Adobe AFM advance widths, 1/1000 em. Needed to measure strings for word
 * wrapping and right/centre alignment; base-14 fonts are not embedded, so
 * these must be carried here. Oblique shares its upright's widths.
 */
function toolkit_pdf_widths( $bold ) {
	static $cache = array();
	$key = $bold ? 'b' : 'r';
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}
	$regular = '278 278 355 556 556 889 667 191 333 333 389 584 278 333 278 278 556 556 556 556 556 556 556 556 556 556 278 278 584 584 584 556 1015 667 667 722 722 667 611 778 722 278 500 667 556 833 722 778 667 778 722 667 611 722 667 944 667 667 611 278 278 278 469 556 333 556 556 500 556 556 278 556 556 222 222 500 222 833 556 556 556 556 333 500 278 556 500 722 500 500 500 334 260 334 584';
	$boldw   = '278 333 474 556 556 889 722 238 333 333 389 584 278 333 278 278 556 556 556 556 556 556 556 556 556 556 333 333 584 584 584 611 975 722 722 722 722 667 611 778 722 278 556 722 611 833 722 778 667 778 722 667 611 722 667 944 667 667 611 333 278 333 584 556 333 556 611 556 611 556 333 611 611 278 278 556 278 889 611 611 611 611 389 556 333 611 556 778 556 556 500 389 280 389 584';
	$values  = array_map( 'intval', explode( ' ', $bold ? $boldw : $regular ) );
	$table   = array();
	foreach ( $values as $offset => $width ) {
		$table[ 32 + $offset ] = $width;
	}
	$cache[ $key ] = $table;
	return $table;
}

/**
 * UTF-8 to WinAnsiEncoding. Applicant names are the only unpredictable text
 * on the page; anything outside WinAnsi degrades to '?' rather than emitting
 * a byte the viewer would render as mojibake. Codepoints are resolved first
 * and mapped second — mapping to high bytes up front would leave them
 * indistinguishable from raw UTF-8 continuation bytes.
 */
function toolkit_pdf_winansi( $text ) {
	/* The 0x80-0x9F block is where WinAnsi diverges from Latin-1; everything
	 * from 0xA0 up is identical, so only these need an explicit mapping. */
	static $specials = array(
		0x20AC => 0x80, 0x201A => 0x82, 0x0192 => 0x83, 0x201E => 0x84, 0x2026 => 0x85,
		0x2020 => 0x86, 0x2021 => 0x87, 0x02C6 => 0x88, 0x2030 => 0x89, 0x0160 => 0x8A,
		0x2039 => 0x8B, 0x0152 => 0x8C, 0x017D => 0x8E, 0x2018 => 0x91, 0x2019 => 0x92,
		0x201C => 0x93, 0x201D => 0x94, 0x2022 => 0x95, 0x2013 => 0x96, 0x2014 => 0x97,
		0x02DC => 0x98, 0x2122 => 0x99, 0x0161 => 0x9A, 0x203A => 0x9B, 0x0153 => 0x9C,
		0x017E => 0x9E, 0x0178 => 0x9F,
	);
	$text = (string) $text;
	$out  = '';
	$len  = strlen( $text );
	for ( $i = 0; $i < $len; $i++ ) {
		$byte = ord( $text[ $i ] );
		if ( $byte < 0x80 ) {
			$out .= $text[ $i ];
			continue;
		}
		if ( $byte >= 0xC0 && $byte <= 0xDF ) {
			$extra = 1;
			$code  = $byte & 0x1F;
		} elseif ( $byte >= 0xE0 && $byte <= 0xEF ) {
			$extra = 2;
			$code  = $byte & 0x0F;
		} elseif ( $byte >= 0xF0 && $byte <= 0xF4 ) {
			$extra = 3;
			$code  = $byte & 0x07;
		} else {
			continue; // Stray continuation byte; the sequence it belonged to is gone.
		}
		for ( $n = 1; $n <= $extra && $i + $n < $len; $n++ ) {
			$code = ( $code << 6 ) | ( ord( $text[ $i + $n ] ) & 0x3F );
		}
		$i += $extra;
		if ( isset( $specials[ $code ] ) ) {
			$out .= chr( $specials[ $code ] );
		} elseif ( $code >= 0xA0 && $code <= 0xFF ) {
			$out .= chr( $code );
		} else {
			$out .= '?';
		}
	}
	return $out;
}

function toolkit_pdf_text_width( $text, $bold, $size ) {
	$widths = toolkit_pdf_widths( $bold );
	$text   = toolkit_pdf_winansi( $text );
	$total  = 0;
	$len    = strlen( $text );
	for ( $i = 0; $i < $len; $i++ ) {
		$total += $widths[ ord( $text[ $i ] ) ] ?? 556;
	}
	return $total * $size / 1000;
}

function toolkit_pdf_escape( $text ) {
	return strtr( toolkit_pdf_winansi( $text ), array( '\\' => '\\\\', '(' => '\\(', ')' => '\\)', "\r" => '' ) );
}

/* ---- PNG decoding ----
 * PDF cannot consume a PNG directly: it needs the colour samples and the
 * alpha channel as two separate streams. The template's images are 8-bit
 * RGBA, so the IDAT is inflated, PNG row filters are reversed, and the
 * channels are split. Done row by row so peak memory stays at one scanline
 * rather than the whole decoded bitmap.
 */
function toolkit_pdf_png_decode( $binary, $ink_only = false ) {
	if ( substr( $binary, 0, 8 ) !== "\x89PNG\r\n\x1a\n" ) {
		return new WP_Error( 'pdf_png', 'Not a PNG.' );
	}
	$pos    = 8;
	$idat   = '';
	$header = null;
	$length = strlen( $binary );
	while ( $pos + 8 <= $length ) {
		$size = unpack( 'N', substr( $binary, $pos, 4 ) )[1];
		$type = substr( $binary, $pos + 4, 4 );
		$body = substr( $binary, $pos + 8, $size );
		if ( 'IHDR' === $type ) {
			$header = unpack( 'Nwidth/Nheight/Cbits/Ccolor/Ccompression/Cfilter/Cinterlace', $body );
		} elseif ( 'IDAT' === $type ) {
			$idat .= $body;
		} elseif ( 'IEND' === $type ) {
			break;
		}
		$pos += 12 + $size;
	}
	if ( ! $header || '' === $idat ) {
		return new WP_Error( 'pdf_png', 'PNG is missing IHDR or IDAT.' );
	}
	if ( 8 !== (int) $header['bits'] || 0 !== (int) $header['interlace'] ) {
		return new WP_Error( 'pdf_png', 'Only 8-bit non-interlaced PNGs are supported.' );
	}
	$channels = array( 0 => 1, 2 => 3, 4 => 2, 6 => 4 );
	if ( ! isset( $channels[ (int) $header['color'] ] ) ) {
		return new WP_Error( 'pdf_png', 'Unsupported PNG colour type ' . $header['color'] . '.' );
	}
	$bpp  = $channels[ (int) $header['color'] ];
	$grey = in_array( (int) $header['color'], array( 0, 4 ), true );
	$alpha_channels = in_array( (int) $header['color'], array( 4, 6 ), true ) ? 1 : 0;
	$colour_bytes   = $bpp - $alpha_channels;

	$raw = @gzuncompress( $idat );
	if ( false === $raw ) {
		return new WP_Error( 'pdf_png', 'PNG image data could not be inflated.' );
	}
	$width  = (int) $header['width'];
	$height = (int) $header['height'];
	$stride = $width * $bpp;
	if ( strlen( $raw ) < $height * ( $stride + 1 ) ) {
		return new WP_Error( 'pdf_png', 'PNG image data is truncated.' );
	}

	$colour = '';
	$alpha  = '';
	$prior  = array_fill( 0, $stride, 0 );
	$offset = 0;
	for ( $row = 0; $row < $height; $row++ ) {
		$filter  = ord( $raw[ $offset ] );
		$offset++;
		$current = array_values( unpack( 'C*', substr( $raw, $offset, $stride ) ) );
		$offset += $stride;
		for ( $i = 0; $i < $stride; $i++ ) {
			$left  = $i >= $bpp ? $current[ $i - $bpp ] : 0;
			$up    = $prior[ $i ];
			switch ( $filter ) {
				case 1:
					$current[ $i ] = ( $current[ $i ] + $left ) & 0xFF;
					break;
				case 2:
					$current[ $i ] = ( $current[ $i ] + $up ) & 0xFF;
					break;
				case 3:
					$current[ $i ] = ( $current[ $i ] + ( ( $left + $up ) >> 1 ) ) & 0xFF;
					break;
				case 4:
					$corner    = $i >= $bpp ? $prior[ $i - $bpp ] : 0;
					$estimate  = $left + $up - $corner;
					$da        = abs( $estimate - $left );
					$db        = abs( $estimate - $up );
					$dc        = abs( $estimate - $corner );
					$predictor = ( $da <= $db && $da <= $dc ) ? $left : ( $db <= $dc ? $up : $corner );
					$current[ $i ] = ( $current[ $i ] + $predictor ) & 0xFF;
					break;
			}
		}
		$prior = $current;
		for ( $x = 0; $x < $width; $x++ ) {
			$base = $x * $bpp;
			if ( $grey ) {
				$red = $green = $blue = $current[ $base ];
			} else {
				$red   = $current[ $base ];
				$green = $current[ $base + 1 ];
				$blue  = $current[ $base + 2 ];
			}
			if ( $ink_only ) {
				/* Line art scanned onto an opaque white sheet: re-derive
				 * transparency from how dark each pixel is, so the white
				 * card does not sit as a solid rectangle over whatever the
				 * image is layered on top of. Anti-aliased edges keep their
				 * softness because they land on mid alpha. */
				$luminance = (int) round( 0.299 * $red + 0.587 * $green + 0.114 * $blue );
				$colour   .= "\x00\x00\x00";
				$alpha    .= chr( 255 - $luminance );
				continue;
			}
			$colour .= chr( $red ) . chr( $green ) . chr( $blue );
			if ( $alpha_channels ) {
				$alpha .= chr( $current[ $base + $colour_bytes ] );
			}
		}
	}

	return array(
		'width'  => $width,
		'height' => $height,
		'colour' => gzcompress( $colour, 9 ),
		'alpha'  => ( $alpha_channels || $ink_only ) ? gzcompress( $alpha, 9 ) : '',
	);
}

/* ---- Page writer ---- */

class Toolkit_Pdf_Page {
	const WIDTH  = 595.28; // A4 at 72dpi.
	const HEIGHT = 841.89;

	protected $stream = '';
	protected $images = array(); // handle => decoded PNG.

	/** PDF's origin is bottom-left; the layout code thinks top-down. */
	protected function flip( $y ) {
		return self::HEIGHT - $y;
	}

	public function rect( $x, $y, $width, $height, array $rgb ) {
		$this->stream .= sprintf(
			"%.4F %.4F %.4F rg %.2F %.2F %.2F %.2F re f\n",
			$rgb[0] / 255,
			$rgb[1] / 255,
			$rgb[2] / 255,
			$x,
			$this->flip( $y + $height ),
			$width,
			$height
		);
	}

	public function text( $x, $baseline, $string, $bold, $size, array $rgb = array( 0, 0, 0 ), $italic = false ) {
		if ( '' === (string) $string ) {
			return;
		}
		$font = $bold ? ( $italic ? '/F4' : '/F2' ) : ( $italic ? '/F3' : '/F1' );
		$this->stream .= sprintf(
			"BT %s %.2F Tf %.4F %.4F %.4F rg 1 0 0 1 %.2F %.2F Tm (%s) Tj ET\n",
			$font,
			$size,
			$rgb[0] / 255,
			$rgb[1] / 255,
			$rgb[2] / 255,
			$x,
			$this->flip( $baseline ),
			toolkit_pdf_escape( $string )
		);
	}

	public function line( $x1, $y1, $x2, $y2, $thickness, array $rgb ) {
		$this->stream .= sprintf(
			"%.4F %.4F %.4F RG %.2F w %.2F %.2F m %.2F %.2F l S\n",
			$rgb[0] / 255,
			$rgb[1] / 255,
			$rgb[2] / 255,
			$thickness,
			$x1,
			$this->flip( $y1 ),
			$x2,
			$this->flip( $y2 )
		);
	}

	public function image( $handle, array $decoded, $x, $y, $width, $height ) {
		$this->images[ $handle ] = $decoded;
		$this->stream .= sprintf(
			"q %.2F 0 0 %.2F %.2F %.2F cm /%s Do Q\n",
			$width,
			$height,
			$x,
			$this->flip( $y + $height ),
			$handle
		);
	}

	/** Assemble the object graph and serialise. */
	public function output() {
		$objects = array();
		$add     = function ( $body ) use ( &$objects ) {
			$objects[] = $body;
			return count( $objects );
		};

		$font_ids = array();
		foreach ( array( 'F1' => 'Helvetica', 'F2' => 'Helvetica-Bold', 'F3' => 'Helvetica-Oblique', 'F4' => 'Helvetica-BoldOblique' ) as $name => $base ) {
			$font_ids[ $name ] = $add( "<< /Type /Font /Subtype /Type1 /BaseFont /{$base} /Encoding /WinAnsiEncoding >>" );
		}

		$image_ids = array();
		foreach ( $this->images as $handle => $decoded ) {
			$smask_ref = '';
			if ( '' !== $decoded['alpha'] ) {
				$mask_id   = $add(
					"<< /Type /XObject /Subtype /Image /Width {$decoded['width']} /Height {$decoded['height']}"
					. ' /ColorSpace /DeviceGray /BitsPerComponent 8 /Filter /FlateDecode /Length '
					. strlen( $decoded['alpha'] ) . " >>\nstream\n" . $decoded['alpha'] . "\nendstream"
				);
				$smask_ref = " /SMask {$mask_id} 0 R";
			}
			$image_ids[ $handle ] = $add(
				"<< /Type /XObject /Subtype /Image /Width {$decoded['width']} /Height {$decoded['height']}"
				. ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode' . $smask_ref . ' /Length '
				. strlen( $decoded['colour'] ) . " >>\nstream\n" . $decoded['colour'] . "\nendstream"
			);
		}

		$compressed = gzcompress( $this->stream, 9 );
		$content_id = $add( '<< /Filter /FlateDecode /Length ' . strlen( $compressed ) . " >>\nstream\n" . $compressed . "\nendstream" );

		$font_entries = '';
		foreach ( $font_ids as $name => $id ) {
			$font_entries .= "/{$name} {$id} 0 R ";
		}
		$image_entries = '';
		foreach ( $image_ids as $handle => $id ) {
			$image_entries .= "/{$handle} {$id} 0 R ";
		}
		$resources = '<< /Font << ' . trim( $font_entries ) . ' >>'
			. ( $image_entries ? ' /XObject << ' . trim( $image_entries ) . ' >>' : '' ) . ' >>';

		/* Page needs its parent's id and Pages needs its kid's id, so both
		 * are reserved before either body is written. */
		$page_id  = count( $objects ) + 1;
		$pages_id = $page_id + 1;
		$add( sprintf(
			'<< /Type /Page /Parent %d 0 R /MediaBox [0 0 %.2F %.2F] /Resources %s /Contents %d 0 R >>',
			$pages_id,
			self::WIDTH,
			self::HEIGHT,
			$resources,
			$content_id
		) );
		$add( "<< /Type /Pages /Kids [{$page_id} 0 R] /Count 1 >>" );
		$catalog_id = $add( "<< /Type /Catalog /Pages {$pages_id} 0 R >>" );
		$info_id    = $add( '<< /Producer (The Toolkit for Skills & Innovation) /Title (Calling Letter) /CreationDate (D:' . gmdate( 'YmdHis' ) . "Z) >>" );

		$pdf     = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
		$offsets = array();
		foreach ( $objects as $index => $body ) {
			$offsets[ $index + 1 ] = strlen( $pdf );
			$pdf .= ( $index + 1 ) . " 0 obj\n" . $body . "\nendobj\n";
		}
		$xref_offset = strlen( $pdf );
		$count       = count( $objects ) + 1;
		$pdf        .= "xref\n0 {$count}\n0000000000 65535 f \n";
		for ( $i = 1; $i <= count( $objects ); $i++ ) {
			$pdf .= sprintf( "%010d 00000 n \n", $offsets[ $i ] );
		}
		$pdf .= "trailer\n<< /Size {$count} /Root {$catalog_id} 0 R /Info {$info_id} 0 R >>\n"
			. "startxref\n{$xref_offset}\n%%EOF\n";
		return $pdf;
	}
}

/* ---- Rich-text flow ----
 * A "run" is array( text, bold ). A paragraph is a list of runs that wraps as
 * one continuous string, so bold fragments break across lines correctly
 * instead of each run being wrapped in isolation.
 */

function toolkit_pdf_wrap_runs( array $runs, $size, $max_width ) {
	$lines   = array( array() );
	$width   = 0;
	$line    = 0;
	foreach ( $runs as $run ) {
		list( $text, $bold ) = $run;
		$words = preg_split( '/(\s+)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
		foreach ( $words as $word ) {
			$word_width = toolkit_pdf_text_width( $word, $bold, $size );
			$is_space   = '' === trim( $word );
			if ( $is_space && ! $lines[ $line ] ) {
				continue; // No leading space on a fresh line.
			}
			if ( ! $is_space && $width + $word_width > $max_width && $lines[ $line ] ) {
				$line++;
				$lines[ $line ] = array();
				$width          = 0;
			}
			$lines[ $line ][] = array( $word, $bold );
			$width           += $word_width;
		}
	}
	return $lines;
}

function toolkit_pdf_line_width( array $line, $size ) {
	$width = 0;
	foreach ( $line as $run ) {
		$width += toolkit_pdf_text_width( $run[0], $run[1], $size );
	}
	return $width;
}

/**
 * Draw wrapped runs starting at $y (top of the first line's em box).
 * Returns the y position just below the last line.
 */
function toolkit_pdf_draw_paragraph( Toolkit_Pdf_Page $page, array $runs, $x, $y, $max_width, $size, $leading, $options = array() ) {
	$align     = $options['align'] ?? 'left';
	$underline = ! empty( $options['underline'] );
	$colour    = $options['colour'] ?? array( 0, 0, 0 );
	$lines     = toolkit_pdf_wrap_runs( $runs, $size, $max_width );
	foreach ( $lines as $line ) {
		if ( ! $line ) {
			continue;
		}
		/* Trailing spaces would push a right-aligned line off its margin. */
		while ( $line && '' === trim( end( $line )[0] ) ) {
			array_pop( $line );
		}
		$line_width = toolkit_pdf_line_width( $line, $size );
		$cursor     = $x;
		if ( 'right' === $align ) {
			$cursor = $x + $max_width - $line_width;
		} elseif ( 'center' === $align ) {
			$cursor = $x + ( $max_width - $line_width ) / 2;
		}
		$baseline = $y + $size * 0.8;
		$start    = $cursor;
		foreach ( $line as $run ) {
			$page->text( $cursor, $baseline, $run[0], $run[1], $size, $colour, ! empty( $options['italic'] ) );
			$cursor += toolkit_pdf_text_width( $run[0], $run[1], $size );
		}
		if ( $underline ) {
			$page->rect( $start, $baseline + $size * 0.11, $cursor - $start, max( 0.6, $size * 0.055 ), $colour );
		}
		$y += $leading;
	}
	return $y;
}

/* ---- Letterhead assets ----
 * The logo, stamp and signature already ship inside the .docx template, so
 * there is nothing extra to upload or keep in sync: they are pulled straight
 * out of word/media/. Decoding is the slow part (pure-PHP inflate plus row
 * unfiltering), so the finished PDF streams are cached on disk beside the
 * template and reused by every later letter.
 */
function toolkit_calling_letter_pdf_asset( $entry, $ink_only = false ) {
	static $memo = array();
	$memo_key = $entry . ( $ink_only ? '|ink' : '' );
	if ( isset( $memo[ $memo_key ] ) ) {
		return $memo[ $memo_key ];
	}
	$template = toolkit_calling_letter_template_path();
	$binary   = toolkit_calling_letter_zip_read( $template, $entry );
	if ( false === $binary || '' === (string) $binary ) {
		return new WP_Error( 'pdf_asset', 'Template is missing ' . $entry . '.' );
	}
	$cache_file = toolkit_calling_letter_private_dir() . '/_pdfimg-' . md5( $memo_key . '|' . strlen( $binary ) . '|' . md5( $binary ) ) . '.cache';
	if ( is_readable( $cache_file ) ) {
		$cached = @unserialize( (string) file_get_contents( $cache_file ) );
		if ( is_array( $cached ) && isset( $cached['width'], $cached['colour'] ) ) {
			$memo[ $memo_key ] = $cached;
			return $cached;
		}
	}
	$decoded = toolkit_pdf_png_decode( $binary, $ink_only );
	if ( is_wp_error( $decoded ) ) {
		return $decoded;
	}
	file_put_contents( $cache_file, serialize( $decoded ) );
	$memo[ $memo_key ] = $decoded;
	return $decoded;
}

/**
 * Intake wording is the one piece of body copy that expires. It lives in an
 * option rather than a literal so it can be corrected without a code change,
 * defaulting to whatever the .docx template currently says.
 */
function toolkit_calling_letter_intake_label() {
	$label = trim( (string) get_option( 'toolkit_calling_letter_intake', '' ) );
	return '' !== $label ? $label : 'August/September 2026 Intake';
}

/**
 * Render the calling letter as a single-page A4 PDF.
 * $tokens: full_name, phone, first_name, course, letter_date.
 * Returns true, or a string error message.
 */
function toolkit_calling_letter_render_pdf( $destination_path, array $tokens ) {
	$olive  = array( 139, 148, 55 );
	$blue   = array( 9, 105, 128 );
	$orange = array( 241, 91, 35 );
	$link   = array( 31, 90, 168 );
	$ink    = array( 0, 0, 0 );

	$page  = new Toolkit_Pdf_Page();
	$left  = 72.0;
	$right = 523.0;
	$width = $right - $left;

	/* Letterhead. Measurements mirror the .docx header so the PDF and the
	 * Word copy of the same letter are visually interchangeable. */
	$logo = toolkit_calling_letter_pdf_asset( 'word/media/image5.png' );
	if ( is_wp_error( $logo ) ) {
		return $logo->get_error_message();
	}
	$page->image( 'ImLogo', $logo, 23.8, 25.0, 110.0, 110.0 * $logo['height'] / $logo['width'] );

	/* Each contact line is placed explicitly rather than wrapped: a phone
	 * number broken across two lines is the exact defect this replaces. */
	$contact_right = 567.2;
	$y             = 12.0;
	$page->text( $contact_right - toolkit_pdf_text_width( 'The Toolkit for Skills & Innovation', true, 10.5 ), $y + 8.4, 'The Toolkit for Skills & Innovation', true, 10.5 );
	$y += 14.0;
	$address = array(
		'The Toolkit Skills & Innovation Hub 7219 Karen-Kikuyu',
		'Highway (Southern Bypass) Kikuyu',
		'Tel: +254 731 802 855 / Mob: +254 711 802 855',
	);
	foreach ( $address as $line ) {
		$page->text( $contact_right - toolkit_pdf_text_width( $line, false, 9.5 ), $y + 7.6, $line, false, 9.5 );
		$y += 13.0;
	}
	$web = 'office@toolkitafrica.ac.ke | www.toolkitafrica.ac.ke';
	$x   = $contact_right - toolkit_pdf_text_width( $web, false, 9.5 );
	$page->text( $x, $y + 7.6, $web, false, 9.5, $link );
	$page->rect( $x, $y + 8.7, toolkit_pdf_text_width( $web, false, 9.5 ), 0.6, $link );

	/* Full-bleed tricolour rule, in equal thirds. */
	$third = Toolkit_Pdf_Page::WIDTH / 3;
	foreach ( array( $olive, $blue, $orange ) as $index => $colour ) {
		$page->rect( $index * $third, 118.0, $third + 0.5, 1.6, $colour );
	}

	$size    = 10.5;
	$leading = 14.6;
	$y       = 133.0;

	$y = toolkit_pdf_draw_paragraph( $page, array( array( $tokens['letter_date'], true ) ), $left, $y, $width, $size, $leading, array( 'align' => 'right' ) );
	$y += 3.0;
	$y = toolkit_pdf_draw_paragraph( $page, array( array( strtoupper( $tokens['full_name'] ), true ) ), $left, $y, $width, $size, $leading );
	$y = toolkit_pdf_draw_paragraph( $page, array( array( $tokens['phone'], true ) ), $left, $y, $width, $size, $leading );
	$y = toolkit_pdf_draw_paragraph( $page, array( array( 'Dear ', false ), array( $tokens['first_name'], true ), array( ',', false ) ), $left, $y, $width, $size, $leading );
	$y = toolkit_pdf_draw_paragraph(
		$page,
		array( array( 'RE: INVITATION TO JOIN THE TOOLKIT FOR SKILLS & INNOVATION', true ) ),
		$left,
		$y,
		$width,
		$size,
		$leading,
		array( 'underline' => true )
	);

	$paragraphs = array(
		array( array( 'Thank you for choosing ', false ), array( 'The Toolkit for Skills & Innovation.', true ) ),
		array(
			array( 'We are pleased to invite you to join our ', false ),
			array( toolkit_calling_letter_intake_label(), true ),
			array( ' and begin your journey towards acquiring practical, industry-relevant skills that lead to employment, entrepreneurship, and career success.', false ),
		),
		array(
			array( 'To secure your place in the ', false ),
			array( strtoupper( $tokens['course'] ), true ),
			array( ', kindly complete your admission by:', false ),
		),
	);
	foreach ( $paragraphs as $runs ) {
		$y = toolkit_pdf_draw_paragraph( $page, $runs, $left, $y, $width, $size, $leading );
	}

	$bullet = function ( $runs ) use ( $page, $left, $width, $size, $leading, &$y ) {
		$page->text( $left + 14.0, $y + $size * 0.8, "\u{2022}", false, $size );
		$y = toolkit_pdf_draw_paragraph( $page, $runs, $left + 28.0, $y, $width - 28.0, $size, $leading );
	};

	$bullet( array( array( 'Completing the Registration Form.', false ) ) );
	$bullet( array(
		array( 'Paying a minimum ', false ),
		array( 'admission fee of KSh 2,000', true ),
		array( ' (part of your tuition fees).', false ),
	) );

	$y = toolkit_pdf_draw_paragraph( $page, array( array( 'Upon registration, you will receive your:', false ) ), $left, $y, $width, $size, $leading );
	foreach ( array( 'Official Admission Letter', 'Fee Structure', 'Payment Receipt', 'Student Reporting Guide' ) as $item ) {
		$bullet( array( array( $item, false ) ) );
	}

	$y = toolkit_pdf_draw_paragraph( $page, array( array( 'At Toolkit, you will benefit from:', false ) ), $left, $y, $width, $size, $leading );
	$benefits = array(
		'Competency-Based Education and Training (CBET)',
		'Hands-on practical training and industry work-based learning',
		'Modern workshops and technology-enabled learning',
		'Nationally and Internationally recognized certification',
		'Career guidance and job placement support through our Talent Connect Office',
	);
	foreach ( $benefits as $item ) {
		$bullet( array( array( $item, false ) ) );
	}

	$y = toolkit_pdf_draw_paragraph(
		$page,
		array( array( 'We look forward to welcoming you to our community of skilled, confident, and productive professionals.', false ) ),
		$left,
		$y,
		$width,
		$size,
		$leading
	);
	$y = toolkit_pdf_draw_paragraph(
		$page,
		array(
			array( 'All fees payable to the college are paid to the bank account details below. Please note that the college ', false ),
			array( 'DOES NOT ACCEPT CASH PAYMENTS.', true ),
		),
		$left,
		$y,
		$width,
		$size,
		$leading
	);

	/* Banking table. */
	$y     += 3.0;
	$cols   = array( 110.0, 175.0, 82.0, 84.0 );
	$rows   = array(
		array( array( 'Bank', true ), array( 'Account Name', true ), array( 'Account no', true ), array( 'Branch', true ) ),
		array( array( 'NCBA Bank', false ), array( 'The Toolkit Iskills (TTI) Limited', false ), array( '4006580089', false ), array( 'Riverside', false ) ),
		array( array( 'MPESA PAYBILL NO', false ), array( 'The Toolkit Institute (TTI) Limited', false ), array( '998446', false ), array( 'Acc. Student Name', false ) ),
	);
	$row_height = 15.8;
	$table_top  = $y;
	$cell_size  = 9.5;
	foreach ( $rows as $index => $row ) {
		$cell_x = $left;
		foreach ( $row as $column => $cell ) {
			$page->text( $cell_x + 3.0, $y + $row_height - 4.6, $cell[0], $cell[1], $cell_size );
			$cell_x += $cols[ $column ];
		}
		$y += $row_height;
	}
	$table_bottom = $y;
	for ( $i = 0; $i <= count( $rows ); $i++ ) {
		$page->rect( $left, $table_top + $i * $row_height, $width, 0.7, $ink );
	}
	$cell_x = $left;
	foreach ( array_merge( $cols, array( 0.0 ) ) as $column_width ) {
		$page->rect( $cell_x, $table_top, 0.7, $table_bottom - $table_top, $ink );
		$cell_x += $column_width;
	}

	$y += 4.0;
	$y  = toolkit_pdf_draw_paragraph(
		$page,
		array( array( 'All cheques are payable to: ', false ), array( 'THE TOOLKIT ISKILLS (TTI) LIMITED', true ) ),
		$left,
		$y,
		$width,
		$size,
		$leading
	);
	$y = toolkit_pdf_draw_paragraph( $page, array( array( 'Yours faithfully,', false ) ), $left, $y, $width, $size, $leading );

	/* Stamp first, signature over it — the same stacking the .docx uses. */
	$stamp     = toolkit_calling_letter_pdf_asset( 'word/media/image1.png' );
	/* The signature scan is an opaque white card, so it is loaded ink-only —
	 * otherwise it masks the stamp it is meant to sit inside. */
	$signature = toolkit_calling_letter_pdf_asset( 'word/media/image3.png', true );
	if ( is_wp_error( $stamp ) ) {
		return $stamp->get_error_message();
	}
	if ( is_wp_error( $signature ) ) {
		return $signature->get_error_message();
	}
	$stamp_top = $y + 2.0;
	$page->image( 'ImStamp', $stamp, $left - 17.3, $stamp_top, 117.0, 117.0 * $stamp['height'] / $stamp['width'] );
	$page->image( 'ImSign', $signature, $left - 6.4, $stamp_top + 14.0, 88.0, 88.0 * $signature['height'] / $signature['width'] );
	$y = $stamp_top + 117.0 * $stamp['height'] / $stamp['width'] + 6.0;

	$y = toolkit_pdf_draw_paragraph( $page, array( array( 'THEOPHILE SYLVESTER', false ) ), $left, $y, $width, $size, $leading );
	$y = toolkit_pdf_draw_paragraph( $page, array( array( 'Principal', true ) ), $left, $y, $width, $size, $leading, array( 'underline' => true ) );

	/* Footer: full-bleed orange rule broken by the centred strapline. */
	$tagline    = 'A leader in powering Africa with skilled, confident and productive youth';
	$tag_width  = toolkit_pdf_text_width( $tagline, false, 10.0 );
	$tag_left   = ( Toolkit_Pdf_Page::WIDTH - $tag_width ) / 2;
	$footer_y   = 803.0;
	$page->rect( 0, $footer_y, max( 0, $tag_left - 12.0 ), 1.6, $orange );
	$page->rect( $tag_left + $tag_width + 12.0, $footer_y, Toolkit_Pdf_Page::WIDTH - ( $tag_left + $tag_width + 12.0 ), 1.6, $orange );
	$page->text( $tag_left, $footer_y + 4.0, $tagline, false, 10.0, array( 51, 51, 51 ), true );

	$written = file_put_contents( $destination_path, $page->output() );
	if ( false === $written ) {
		return 'Calling letter PDF could not be written to ' . $destination_path . '.';
	}
	return true;
}
