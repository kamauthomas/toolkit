<?php
/** Discovery for enabled public virtual pages; Yoast remains the schema owner. */
require_once __DIR__ . '/course-catalog.php';

function toolkit_seo_virtual_path() {
	if ( ! eduma_child_redesign_enabled() ) return '';
	$slug = get_query_var( 'toolkit_course' );
	if ( $slug && eduma_child_2026_catalog_enabled() ) {
		return is_string( $slug ) && sanitize_key( $slug ) === $slug && eduma_child_get_course( $slug ) ? '/courses/' . $slug . '/' : '';
	}
	if ( get_query_var( 'toolkit_connect' ) || get_query_var( 'toolkit_reception' ) || ( toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' ) ) ) return '';
	$showcase = get_query_var( 'toolkit_showcase' );
	if ( is_string( $showcase ) && in_array( $showcase, array( 'graduation', 'testimonials', 'footprint' ), true ) ) {
		return '/' . $showcase . '/';
	}
	return '';
}

function toolkit_seo_virtual_canonical() {
	$path = toolkit_seo_virtual_path();
	if ( ! $path ) return '';
	return preg_replace( '~^https://demo\.toolkitafrica\.ac\.ke~', 'https://toolkitafrica.ac.ke', home_url( $path ) );
}

/* Virtual routes can lack Yoast's page indexable/presenter. Own exactly one
 * canonical there, including demo; database-backed pages keep their old owner. */
add_filter( 'wpseo_canonical', function( $canonical ) {
	return toolkit_seo_virtual_path() ? false : $canonical;
}, 200 );
add_action( 'wp_head', function() {
	$canonical = toolkit_seo_virtual_canonical();
	if ( $canonical ) echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
}, 3 );

add_filter( 'wpseo_sitemap_page_content', function( $content ) {
	if ( ! eduma_child_redesign_enabled() || ! eduma_child_2026_catalog_enabled() ) return $content;
	$modified = max( filemtime( __DIR__ . '/course-catalog.php' ), filemtime( dirname( __DIR__ ) . '/template-parts/pages/course-detail.php' ) );
	foreach ( eduma_child_course_catalog() as $slug => $course ) {
		if ( sanitize_key( $slug ) !== $slug || ! eduma_child_get_course( $slug ) ) continue;
		$url = esc_url( home_url( '/courses/' . $slug . '/' ) );
		if ( preg_match( '~<loc>\s*' . preg_quote( $url, '~' ) . '\s*</loc>~', $content ) ) continue;
		$content .= '<url><loc>' . $url . '</loc><lastmod>' . esc_html( gmdate( DATE_W3C, $modified ) ) . '</lastmod></url>';
	}
	return $content;
}, 30 );

/* Some virtual courses have a Course node but no page/breadcrumb pieces. Fill
 * only the missing pieces in the same graph, with the existing entity IDs. */
add_filter( 'wpseo_schema_graph', function( $graph ) {
	$slug = get_query_var( 'toolkit_course' );
	if ( ! is_array( $graph ) || ! is_string( $slug ) || ! toolkit_seo_virtual_path() || ! eduma_child_get_course( $slug ) ) return $graph;
	$course = eduma_child_get_course( $slug );
	$url = home_url( '/courses/' . $slug . '/' );
	$ids = array();
	foreach ( $graph as $node ) {
		if ( is_array( $node ) && isset( $node['@id'] ) ) $ids[ $node['@id'] ] = true;
	}
	if ( ! isset( $ids[ $url . '#webpage' ] ) ) {
		$graph[] = array(
			'@type' => 'WebPage', '@id' => $url . '#webpage', 'url' => $url,
			'name' => $course['title'], 'description' => $course['short'], 'inLanguage' => 'en-KE',
			'isPartOf' => array( '@id' => home_url( '/#website' ) ),
			'mainEntity' => array( '@id' => $url . '#course' ),
			'breadcrumb' => array( '@id' => $url . '#breadcrumb' ),
		);
	}
	if ( ! isset( $ids[ $url . '#breadcrumb' ] ) ) {
		$graph[] = array(
			'@type' => 'BreadcrumbList', '@id' => $url . '#breadcrumb',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Our Courses', 'item' => home_url( '/our-ventures/' ) ),
				array( '@type' => 'ListItem', 'position' => 3, 'name' => $course['title'], 'item' => $url ),
			),
		);
	}
	return $graph;
}, 200 );

/** An editorial pointer to already approved public resources, not an event recap. */
function toolkit_seo_graduation_update() {
	return array(
		'slug' => 'class-of-2026-programme-and-graduands',
		'title' => 'Class of 2026: programme and graduands',
		'seo_title' => 'Class of 2026: Programme and Graduands | Toolkit',
		'excerpt' => 'Find the published Class of 2026 ceremony programme, graduands by course and official graduation information from The Toolkit for Skills and Innovation.',
		'image' => get_stylesheet_directory_uri() . '/assets/images/graduation/kmm-2071-jpg.webp',
		'content' => '<p>The Toolkit for Skills and Innovation’s Class of 2026 graduation page brings the published ceremony programme and graduands list together in one place.</p>'
			. '<h2>Find the programme and graduands</h2><p>The programme sets out the ceremony’s planned order of events. The graduands section lists names by course, making it easier for learners, families and partners to find the relevant programme.</p>'
			. '<p><a href="' . esc_url( home_url( '/graduation/#programme' ) ) . '">View the Class of 2026 graduation programme</a> or <a href="' . esc_url( home_url( '/graduation/#graduands' ) ) . '">browse the published graduands list</a>.</p>'
			. '<h2>Explore the learning pathways</h2><p>Toolkit’s training combines practical learning with progression towards work and enterprise. Visitors considering a programme can <a href="' . esc_url( home_url( '/our-ventures/' ) ) . '">compare the current courses</a> and <a href="' . esc_url( home_url( '/contact/' ) ) . '">contact Admissions</a> about the suitable level, intake and assessment requirements.</p>'
			. '<p>Use the official graduation page for the published resources and direct any questions about a name or course listing to the Toolkit team.</p>',
	);
}
