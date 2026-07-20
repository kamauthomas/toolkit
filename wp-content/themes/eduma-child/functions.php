<?php

require_once get_stylesheet_directory() . '/inc/site-metrics.php';

/**
 * Deployment switches. Constants take priority so operations can change state
 * instantly without editing theme files or deleting WordPress content.
 */
function eduma_child_switch( $constant, $option, $default = false ) {
	if ( defined( $constant ) ) {
		return (bool) constant( $constant );
	}
	$value = get_option( $option, null );
	return null === $value ? $default : (bool) $value;
}

function eduma_child_redesign_enabled() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
	$default = 'demo.toolkitafrica.ac.ke' === $host || in_array( $host, array( '127.0.0.1:8001', 'localhost:8001' ), true );
	return eduma_child_switch( 'TOOLKIT_REDESIGN_ENABLED', 'toolkit_redesign_enabled', $default );
}

function eduma_child_2026_catalog_enabled() {
	return eduma_child_switch( 'TOOLKIT_2026_CATALOG_ENABLED', 'toolkit_2026_catalog_enabled', false );
}

function eduma_child_2026_pricing_enabled() {
	return eduma_child_2026_catalog_enabled() && eduma_child_switch( 'TOOLKIT_2026_PRICING_ENABLED', 'toolkit_2026_pricing_enabled', false );
}

function eduma_child_is_custom_surface() {
	if ( ! eduma_child_redesign_enabled() || is_admin() ) {
		return false;
	}
	if ( get_query_var( 'toolkit_connect' ) || is_front_page() || is_page( array( 'our-ventures', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact', 'toolkit-blog' ) ) || get_query_var( 'toolkit_course' ) ) {
		return true;
	}
	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	return (bool) eduma_child_get_legacy_course_for_page();
}

function thim_child_enqueue_styles() {
	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	$legacy_course = eduma_child_get_legacy_course_for_page();
	wp_enqueue_style( 'thim-parent-style', get_template_directory_uri() . '/style.css', array(), THIM_THEME_VERSION );

	// Brand tokens — shared design tokens used site-wide
	$brand_ver = filemtime( get_stylesheet_directory() . '/brand-tokens.css' );
	wp_enqueue_style( 'eduma-child-brand-tokens', get_stylesheet_directory_uri() . '/brand-tokens.css', array(), $brand_ver );

	if ( is_front_page() && eduma_child_redesign_enabled() ) {
		$css_ver = filemtime( get_stylesheet_directory() . '/hero-slider.css' );
		wp_enqueue_style( 'eduma-child-hero-slider', get_stylesheet_directory_uri() . '/hero-slider.css', array( 'eduma-child-brand-tokens' ), $css_ver );

		$js_ver = filemtime( get_stylesheet_directory() . '/hero-slider.js' );
		wp_enqueue_script( 'eduma-child-hero-slider', get_stylesheet_directory_uri() . '/hero-slider.js', array(), $js_ver, true );
		$experience_path = get_stylesheet_directory() . '/assets/js/home-experience.js';
		wp_enqueue_script( 'toolkit-home-experience', get_stylesheet_directory_uri() . '/assets/js/home-experience.js', array(), filemtime( $experience_path ), true );
	}

	if ( eduma_child_redesign_enabled() && ( is_page( array( 'our-ventures', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact', 'toolkit-blog' ) ) || $legacy_course || get_query_var( 'toolkit_course' ) ) ) {
		$page_css_ver = filemtime( get_stylesheet_directory() . '/page-redesign.css' );
		$page_js_ver  = filemtime( get_stylesheet_directory() . '/page-redesign.js' );
		wp_enqueue_style( 'eduma-child-page-redesign', get_stylesheet_directory_uri() . '/page-redesign.css', array( 'eduma-child-brand-tokens' ), $page_css_ver );
		wp_enqueue_script( 'eduma-child-page-redesign', get_stylesheet_directory_uri() . '/page-redesign.js', array(), $page_js_ver, true );
	}

	if ( eduma_child_redesign_enabled() && get_query_var( 'toolkit_connect' ) ) {
		$page_css_ver = filemtime( get_stylesheet_directory() . '/page-redesign.css' );
		$connect_ver  = filemtime( get_stylesheet_directory() . '/assets/css/connect.css' );
		wp_enqueue_style( 'eduma-child-page-redesign', get_stylesheet_directory_uri() . '/page-redesign.css', array( 'eduma-child-brand-tokens' ), $page_css_ver );
		wp_enqueue_style( 'toolkit-connect', get_stylesheet_directory_uri() . '/assets/css/connect.css', array( 'eduma-child-page-redesign' ), $connect_ver );
	}
}

add_action( 'wp_enqueue_scripts', 'thim_child_enqueue_styles', 1000 );

/* Custom templates render immediately and do not need Eduma's blocking preloader. */
add_filter( 'theme_mod_thim_preload', function( $enabled ) {
	return is_admin() ? $enabled : false;
}, 100 );

add_filter( 'body_class', function( $classes ) {
	if ( ! is_admin() ) {
		$classes = array_diff( $classes, array( 'thim-body-preload', 'fixloader' ) );
	}
	return $classes;
}, 100 );

/* === HOMEPAGE TEMPLATE: prefer the child front-page over builder page templates === */

add_filter( 'template_include', function( $template ) {
	if ( ! is_admin() && is_front_page() && eduma_child_redesign_enabled() ) {
		$front_page_template = get_stylesheet_directory() . '/front-page.php';

		if ( file_exists( $front_page_template ) ) {
			return $front_page_template;
		}
	}

	return $template;
}, 9999 );

add_filter( 'template_include', function( $template ) {
	if ( is_admin() || ! eduma_child_redesign_enabled() ) {
		return $template;
	}

	if ( get_query_var( 'toolkit_course' ) && eduma_child_2026_catalog_enabled() ) {
		return get_stylesheet_directory() . '/template-parts/pages/course-detail.php';
	}
	if ( get_query_var( 'toolkit_connect' ) ) {
		return get_stylesheet_directory() . '/template-parts/pages/connect.php';
	}

	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	if ( eduma_child_get_legacy_course_for_page() ) {
		return get_stylesheet_directory() . '/template-parts/pages/course-detail.php';
	}

	$page_templates = array(
		'about-toolkit-africa'          => 'template-parts/pages/institutional.php',
		'the-toolkit-foundation-copy'  => 'template-parts/pages/institutional.php',
		'the-toolkit-foundation'       => 'template-parts/pages/institutional.php',
		'contact'                      => 'template-parts/pages/contact.php',
		'toolkit-blog'                 => 'template-parts/pages/blog.php',
		'our-ventures'                 => 'template-parts/pages/courses.php',
		'notice-board'               => 'template-parts/pages/notice-board.php',
		'toolkit-courses-apply-today' => 'template-parts/pages/apply.php',
	);

	foreach ( $page_templates as $slug => $relative_path ) {
		if ( is_page( $slug ) ) {
			$custom_template = get_stylesheet_directory() . '/' . $relative_path;
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}
	}

	return $template;
}, 10000 );

/* Theme-owned course routes keep the verified catalog separate from legacy pages. */
add_action( 'init', function() {
	$routes_version = eduma_child_2026_catalog_enabled() ? '2026-2-on' : '2026-2-off';
	if ( eduma_child_2026_catalog_enabled() ) {
		add_rewrite_rule( '^courses/([^/]+)/?$', 'index.php?toolkit_course=$matches[1]', 'top' );
	}
	if ( get_option( 'eduma_child_course_routes_version' ) !== $routes_version ) {
		flush_rewrite_rules( false );
		update_option( 'eduma_child_course_routes_version', $routes_version, false );
	}
} );

/* Database-independent profile landing page for social account bios. */
add_action( 'init', function() {
	$route_version = eduma_child_redesign_enabled() ? '2026-1-on' : '2026-1-off';
	if ( eduma_child_redesign_enabled() ) {
		add_rewrite_rule( '^connect/?$', 'index.php?toolkit_connect=1', 'top' );
	}
	if ( $route_version !== get_option( 'eduma_child_connect_route_version' ) ) {
		flush_rewrite_rules( false );
		update_option( 'eduma_child_connect_route_version', $route_version, false );
	}
} );

add_filter( 'query_vars', function( $vars ) {
	$vars[] = 'toolkit_course';
	$vars[] = 'toolkit_connect';
	return $vars;
} );

add_action( 'template_redirect', function() {
	if ( get_query_var( 'toolkit_connect' ) ) {
		global $wp_query;
		if ( ! eduma_child_redesign_enabled() ) {
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		$wp_query->is_404 = false;
		status_header( 200 );
	}
}, 0 );

add_filter( 'redirect_canonical', function( $redirect_url, $requested_url ) {
	$path = wp_parse_url( $requested_url, PHP_URL_PATH );
	return in_array( untrailingslashit( $path ), array( '/llms.txt', '/llms-full.txt' ), true ) ? false : $redirect_url;
}, 10, 2 );

/* Retire unused public routes without deleting their WordPress records. */
add_action( 'template_redirect', function() {
	if ( is_page( 'students-portal' ) ) {
		wp_safe_redirect( home_url( '/our-ventures/' ), 301, 'Toolkit Africa' );
		exit;
	}
	if ( is_page( 'courses' ) ) {
		wp_safe_redirect( home_url( '/our-ventures/' ), 301, 'Toolkit Africa' );
		exit;
	}
	if ( is_page( 'blog' ) ) {
		wp_safe_redirect( home_url( '/toolkit-blog/' ), 301, 'Toolkit Africa' );
		exit;
	}
}, 1 );

add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function( $ids ) {
	foreach ( array( 'students-portal', 'eventer-shortcode-preview-page', 'courses', 'blog' ) as $path ) {
		$page = get_page_by_path( $path );
		if ( $page ) {
			$ids[] = (int) $page->ID;
		}
	}
	return array_values( array_unique( $ids ) );
} );

/* Staff accounts are operational identities, not public author profile pages. */
add_filter( 'wpseo_sitemap_exclude_author', '__return_empty_array' );

add_filter( 'wpseo_robots_array', function( $robots ) {
	if ( is_author() || is_page( array( 'students-portal', 'eventer-shortcode-preview-page', 'courses', 'blog' ) ) ) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
} );

/* === SEO: curated metadata for child-theme page rebuilds === */

function eduma_child_redesigned_page_metadata() {
	if ( ! eduma_child_redesign_enabled() ) {
		return false;
	}
	if ( is_front_page() ) {
		return array(
			'title'       => 'Practical Skills Training in Kenya | Toolkit Africa',
			'description' => 'Toolkit Africa equips young people and women with practical vocational skills, recognised assessment, and pathways to employment or entrepreneurship.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/toolkit-social-home.webp',
		);
	}
	if ( get_query_var( 'toolkit_connect' ) ) {
		return array(
			'title'       => 'Connect with Toolkit Africa | Courses, Admissions and Updates',
			'description' => 'Apply for practical skills training, explore Toolkit Africa courses, read current notices, contact admissions, and follow our official social channels.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/toolkit-social-home.webp',
		);
	}
	$course_slug = get_query_var( 'toolkit_course' );
	if ( $course_slug ) {
		require_once get_stylesheet_directory() . '/inc/course-catalog.php';
		$course = eduma_child_get_course( sanitize_key( $course_slug ) );
		if ( $course ) {
			return array(
				'title'       => $course['title'] . ' | Toolkit Africa',
				'description' => $course['short'] . ' Review 2026 entry requirements, duration, fees, intakes, and application steps.',
				'image'       => $course['image'],
			);
		}
	}
	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	$legacy_course = eduma_child_get_legacy_course_for_page();
	if ( $legacy_course ) {
		return array(
			'title'       => isset( $legacy_course['seo_title'] ) ? $legacy_course['seo_title'] : $legacy_course['title'] . ' | Toolkit Africa',
			'description' => isset( $legacy_course['seo_description'] ) ? $legacy_course['seo_description'] : $legacy_course['short'] . ' Review the learning focus, delivery details, and application steps.',
			'image'       => $legacy_course['image'],
		);
	}
	if ( is_page( 'our-ventures' ) ) {
		return array(
			'title'       => 'Our Courses | Toolkit Africa',
			'description' => 'Explore practical Toolkit Africa courses in welding, renewable energy, digital skills, agriculture, and enterprise-focused learning.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/about.jpg',
		);
	}
	if ( is_page( 'about-toolkit-africa' ) ) {
		return array(
			'title'       => 'About Toolkit Africa | Skills, Innovation and Opportunity',
			'description' => 'Learn how Toolkit Africa connects practical skills, innovation, industry exposure, employment, and entrepreneurship pathways for young people and women.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/about.jpg',
		);
	}
	if ( is_page( 'the-toolkit-foundation-copy' ) ) {
		return array(
			'title'       => 'Impact and Insights | Toolkit Africa',
			'description' => 'Explore how Toolkit Africa connects practical learning with employability, industrial exposure, livelihoods, and inclusive opportunity.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/impact.jpg',
		);
	}
	if ( is_page( 'the-toolkit-foundation' ) ) {
		return array(
			'title'       => 'The Toolkit Foundation | Inclusive Skills Development',
			'description' => 'Discover Toolkit Foundation programmes supporting inclusive technical, digital, green, and enterprise skills for underserved communities.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/foundation.jpg',
		);
	}
	if ( is_page( 'contact' ) ) {
		return array(
			'title'       => 'Contact Toolkit Africa | Courses, Admissions and Partnerships',
			'description' => 'Contact Toolkit Africa about courses, admissions, partnerships, and visiting the Toolkit Skills and Innovation Hub in Kikuyu, Kenya.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/contact.jpg',
		);
	}
	if ( is_page( 'toolkit-blog' ) ) {
		return array(
			'title'       => 'Toolkit Blog | Skills, Innovation and Opportunity',
			'description' => 'Read Toolkit Africa news, learner stories, skills insights, programme updates, and perspectives on youth employment and innovation.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/impact.jpg',
		);
	}
	if ( is_page( 'construction-sector-skills' ) ) {
		return array(
			'title'       => 'MIG/MAG Welding Training | Toolkit Africa',
			'description' => 'Develop practical MIG/MAG welding skills with hands-on training, VR-enabled learning, and career-focused support at Toolkit Africa.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/welding.jpg',
		);
	}

	if ( is_page( 'notice-board' ) ) {
		return array(
			'title'       => 'Notice Board | Toolkit Africa',
			'description' => 'Find current Toolkit Africa announcements, admissions guidance, opportunities, events, and important notices in one place.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/notice-board.jpg',
		);
	}

	if ( is_page( 'toolkit-courses-apply-today' ) ) {
		return array(
			'title'       => 'Apply for a Course | Toolkit Africa',
			'description' => 'Prepare your Toolkit Africa course application, review the admission steps, and continue securely to the online application portal.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/about.jpg',
		);
	}

	return false;
}

add_filter( 'wpseo_title', function( $title ) {
	$metadata = eduma_child_redesigned_page_metadata();
	return $metadata ? $metadata['title'] : $title;
} );

add_filter( 'wpseo_metadesc', function( $description ) {
	$metadata = eduma_child_redesigned_page_metadata();
	return $metadata ? $metadata['description'] : $description;
} );

add_filter( 'wpseo_opengraph_desc', function( $description ) {
	$metadata = eduma_child_redesigned_page_metadata();
	return $metadata ? $metadata['description'] : $description;
} );

add_filter( 'wpseo_opengraph_title', function( $title ) {
	$metadata = eduma_child_redesigned_page_metadata();
	return $metadata ? $metadata['title'] : $title;
} );

add_filter( 'wpseo_twitter_title', function( $title ) {
	$metadata = eduma_child_redesigned_page_metadata();
	return $metadata ? $metadata['title'] : $title;
} );

function eduma_child_course_canonical_url() {
	$slug = sanitize_key( get_query_var( 'toolkit_course' ) );
	if ( $slug ) {
		return home_url( '/courses/' . $slug . '/' );
	}
	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	$legacy_course = eduma_child_get_legacy_course_for_page();
	return $legacy_course ? $legacy_course['url'] : false;
}

add_filter( 'wpseo_canonical', function( $canonical ) {
	if ( get_query_var( 'toolkit_connect' ) ) {
		return home_url( '/connect/' );
	}
	if ( is_front_page() && eduma_child_redesign_enabled() ) {
		return home_url( '/' );
	}
	$course_url = eduma_child_course_canonical_url();
	return $course_url ? $course_url : $canonical;
} );

add_filter( 'wpseo_opengraph_url', function( $url ) {
	if ( get_query_var( 'toolkit_connect' ) ) {
		return home_url( '/connect/' );
	}
	if ( is_front_page() && eduma_child_redesign_enabled() ) {
		return home_url( '/' );
	}
	$course_url = eduma_child_course_canonical_url();
	return $course_url ? $course_url : $url;
} );

add_filter( 'wpseo_sitemap_page_content', function( $content ) {
	if ( ! eduma_child_redesign_enabled() ) {
		return $content;
	}
	return $content . '<url><loc>' . esc_url( home_url( '/connect/' ) ) . '</loc><lastmod>' . esc_html( gmdate( DATE_W3C, filemtime( get_stylesheet_directory() . '/template-parts/pages/connect.php' ) ) ) . '</lastmod></url>';
} );

add_filter( 'wpseo_opengraph_image', function( $image ) {
	$metadata = eduma_child_redesigned_page_metadata();
	return $metadata ? $metadata['image'] : $image;
} );

add_filter( 'wpseo_opengraph_image_width', function( $width ) {
	return is_front_page() && eduma_child_redesign_enabled() ? 1200 : $width;
} );

add_filter( 'wpseo_opengraph_image_height', function( $height ) {
	return is_front_page() && eduma_child_redesign_enabled() ? 630 : $height;
} );

add_filter( 'wpseo_opengraph_image_type', function( $type ) {
	return is_front_page() && eduma_child_redesign_enabled() ? 'image/webp' : $type;
} );

add_filter( 'wpseo_twitter_image', function( $image ) {
	$metadata = eduma_child_redesigned_page_metadata();
	return $metadata ? $metadata['image'] : $image;
} );

add_filter( 'wpseo_schema_webpage', function( $data ) {
	$metadata = eduma_child_redesigned_page_metadata();
	if ( ! $metadata ) {
		return $data;
	}

	$data['name']        = $metadata['title'];
	$data['description'] = $metadata['description'];
	if ( get_query_var( 'toolkit_connect' ) ) {
		$connect_url        = home_url( '/connect/' );
		$data['@type']      = 'WebPage';
		$data['url']        = $connect_url;
		$data['@id']        = $connect_url . '#webpage';
		$data['breadcrumb'] = array( '@id' => $connect_url . '#breadcrumb' );
		unset( $data['datePublished'], $data['dateModified'] );
	}
	$course_url = eduma_child_course_canonical_url();
	if ( $course_url ) {
		$data['url'] = $course_url;
		$data['@id'] = $course_url . '#webpage';
		$data['breadcrumb'] = array( '@id' => $course_url . '#breadcrumb' );
	}
	unset( $data['primaryImageOfPage'], $data['image'], $data['thumbnailUrl'] );

	return $data;
} );

add_filter( 'wpseo_schema_breadcrumb', function( $data ) {
	if ( get_query_var( 'toolkit_connect' ) ) {
		$connect_url = home_url( '/connect/' );
		$data['@id'] = $connect_url . '#breadcrumb';
		$data['itemListElement'] = array(
			array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) ),
			array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Connect' ),
		);
		return $data;
	}
	$course_url = eduma_child_course_canonical_url();
	if ( ! $course_url ) {
		return $data;
	}
	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	$course = eduma_child_get_course( sanitize_key( get_query_var( 'toolkit_course' ) ) );
	if ( ! $course ) {
		return $data;
	}
	$data['@id'] = $course_url . '#breadcrumb';
	$data['itemListElement'] = array(
		array( '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url( '/' ) ),
		array( '@type' => 'ListItem', 'position' => 2, 'name' => 'Our Courses', 'item' => home_url( '/our-ventures/' ) ),
		array( '@type' => 'ListItem', 'position' => 3, 'name' => $course['title'] ),
	);
	return $data;
} );

/* Strengthen the Toolkit Africa entity and course relationships for search engines. */
add_action( 'wp_head', function() {
	if ( ! eduma_child_redesign_enabled() ) {
		return;
	}

	$home = home_url( '/' );
	$graph = array(
		array(
			'@type'         => array( 'Organization', 'EducationalOrganization' ),
			'@id'           => $home . '#organization',
			'name'          => 'Toolkit Africa',
			'alternateName' => array( 'Toolkit', 'Toolkit for Skills and Innovation', 'Toolkit Skills and Innovation Hub' ),
			'url'           => $home,
			'logo'          => get_stylesheet_directory_uri() . '/assets/images/toolkit-logo.png',
			'email'         => 'office@toolkitafrica.ac.ke',
			'telephone'     => '+254709549200',
			'address'       => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => 'Karen-Kikuyu Southern Bypass',
				'addressLocality' => 'Kikuyu',
				'addressCountry'  => 'KE',
			),
			'sameAs'        => array(
				'https://www.tiktok.com/@thetoolkitafrika',
				'https://www.facebook.com/toolkitafrica',
				'https://x.com/toolkitafrica',
				'https://www.instagram.com/thetoolkitafrika',
				'https://www.linkedin.com/company/the-toolkit-iskills-tti-ltd',
				'https://www.youtube.com/@toolkitafrica',
			),
		),
	);

	if ( is_front_page() ) {
		$graph[] = array(
			'@type'         => 'WebSite',
			'@id'           => $home . '#website',
			'url'           => $home,
			'name'          => 'Toolkit Africa',
			'alternateName' => array( 'Toolkit', 'Toolkit for Skills and Innovation' ),
			'publisher'     => array( '@id' => $home . '#organization' ),
			'inLanguage'    => 'en-KE',
		);
	}

	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	$slug   = sanitize_key( get_query_var( 'toolkit_course' ) );
	$course = $slug ? eduma_child_get_course( $slug ) : eduma_child_get_legacy_course_for_page();
	if ( $course ) {
		$course_url = $slug ? home_url( '/courses/' . $slug . '/' ) : $course['url'];
		$graph[] = array(
			'@type'            => 'Course',
			'@id'              => $course_url . '#course',
			'name'             => $course['title'],
			'description'      => $course['short'],
			'url'              => $course_url,
			'image'            => $course['image'],
			'provider'         => array( '@id' => $home . '#organization' ),
			'inLanguage'       => 'en-KE',
			'courseMode'       => 'Onsite',
			'educationalLevel' => isset( $course['entry'] ) ? $course['entry'] : 'Contact admissions',
			'teaches'          => $course['outcomes'],
		);
	}

	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 30 );

/* === HEADER LOGO: avoid falling back to the parent Eduma logo === */

add_action( 'after_setup_theme', function() {
	remove_action( 'thim_logo', 'thim_logo', 1 );
	add_action( 'thim_logo', 'eduma_child_toolkit_logo', 1 );
}, 20 );

function eduma_child_toolkit_logo() {
	$logo_url = get_stylesheet_directory_uri() . '/assets/images/toolkit-logo.png';

	printf(
		'<a href="%s" title="%s" rel="home" class="thim-logo"><img src="%s" alt="%s" width="200" height="132"></a>',
		esc_url( home_url( '/' ) ),
		esc_attr( get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' ) ),
		esc_url( $logo_url ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

add_filter( 'theme_mod_thim_logo', 'eduma_child_toolkit_logo_url', 20 );
add_filter( 'theme_mod_thim_sticky_logo', 'eduma_child_toolkit_logo_url', 20 );
add_filter( 'theme_mod_thim_logo_mobile', 'eduma_child_toolkit_logo_url', 20 );

function eduma_child_toolkit_logo_url( $logo ) {
	return get_stylesheet_directory_uri() . '/assets/images/toolkit-logo.png';
}

/* === HOMEPAGE HEADER: Keep parent toolbar from creating a dark search band === */

add_filter( 'theme_mod_thim_toolbar_show', function( $show ) {
	return is_admin() ? $show : false;
});

/* === PERFORMANCE: Preload the first hero image on the front page === */

add_action( 'wp_head', function() {
	if ( ! is_front_page() ) {
		return;
	}

	require_once get_stylesheet_directory() . '/inc/hero-slides.php';
	$slides = eduma_child_get_hero_slides();
	if ( empty( $slides[0]['image'] ) ) {
		return;
	}

	printf( '<link rel="preload" as="image" href="%s" type="image/webp" media="(min-width: 768px)" fetchpriority="high">' . "\n", esc_url( $slides[0]['image'] ) );
	printf( '<link rel="preload" as="image" href="%s" type="image/webp" media="(max-width: 767px)" fetchpriority="high">' . "\n", esc_url( $slides[0]['image_mobile'] ) );
}, 2 );

/* === PERFORMANCE: Remove bloat === */

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

add_action( 'send_headers', function() {
	if ( ! is_admin() ) {
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()' );
		if ( is_ssl() ) {
			header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains' );
		}
	}
} );

add_filter( 'wp_resource_hints', function( $urls, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$urls = array_filter( $urls, function( $url ) {
			return false === strpos( $url, 's.w.org' );
		});
	}
	return $urls;
}, 10, 2 );

add_filter( 'style_loader_tag', function( $html, $handle ) {
	if ( eduma_child_is_custom_surface() && 'thim-parent-style' === $handle ) {
		return str_replace( " media='all'", " media='print' onload=\"this.media='all'\"", $html ) . '<noscript>' . $html . '</noscript>';
	}
	if ( strpos( $handle, 'font-awesome' ) !== false || strpos( $handle, 'thim-font-icon' ) !== false || strpos( $handle, 'ionicons' ) !== false || strpos( $handle, 'flaticon' ) !== false || strpos( $handle, 'font-pe-icon' ) !== false ) {
		return str_replace( " media='all'", " media='print' onload=\"this.media='all'\"", $html );
	}
	return $html;
}, 10, 2 );

/* Keep Thim's color/layout variables but remove its large generated webfont set. */
add_action( 'wp_head', function() {
	if ( eduma_child_is_custom_surface() ) {
		ob_start();
	}
}, 998 );

add_action( 'wp_head', function() {
	if ( ! eduma_child_is_custom_surface() || 0 === ob_get_level() ) {
		return;
	}
	$customizer_css = ob_get_clean();
	$customizer_css = preg_replace( '/@font-face\s*\{[^}]*\}/i', '', $customizer_css );
	echo $customizer_css; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}, 1000 );

/* === AI DISCOVERY: concise and expanded machine-readable site guides === */
add_action( 'wp_head', function() {
	if ( eduma_child_redesign_enabled() ) {
		printf( '<link rel="alternate" type="text/plain" href="%s" title="Toolkit Africa AI information">' . "\n", esc_url( home_url( '/llms.txt' ) ) );
	}
}, 3 );

add_action( 'init', function() {
	add_rewrite_rule( '^llms\.txt$', 'index.php?toolkit_llms=llms.txt', 'top' );
	add_rewrite_rule( '^llms-full\.txt$', 'index.php?toolkit_llms=llms-full.txt', 'top' );
	if ( '1' !== get_option( 'toolkit_llms_routes_version' ) ) {
		flush_rewrite_rules( false );
		update_option( 'toolkit_llms_routes_version', '1', false );
	}
} );

add_filter( 'query_vars', function( $vars ) {
	$vars[] = 'toolkit_llms';
	return $vars;
} );

add_action( 'template_redirect', function() {
	$requested = get_query_var( 'toolkit_llms' );
	if ( ! $requested ) {
		return;
	}
	$is_full = 'llms-full.txt' === $requested;
	$lines = array(
		'# Toolkit Africa',
		'> Toolkit Africa is a Kenya-based social enterprise providing practical vocational skills, assessment support, and pathways to employment or entrepreneurship for young people and women.',
		'> The names Toolkit, Toolkit Africa, Toolkit for Skills and Innovation, and Toolkit Skills and Innovation Hub refer to this organization.',
		'',
		'## Canonical resources',
		'- [Homepage](' . home_url( '/' ) . ')',
		'- [About Toolkit Africa](' . home_url( '/about-toolkit-africa/' ) . ')',
		'- [Current course directory](' . home_url( '/our-ventures/' ) . ')',
		'- [MIG/MAG Welding training](' . home_url( '/our-ventures/construction-sector-skills/' ) . ')',
		'- [Admissions guidance](' . home_url( '/our-ventures/toolkit-courses-apply-today/' ) . ')',
		'- [Notice Board](' . home_url( '/notice-board/' ) . ')',
		'- [Toolkit Blog](' . home_url( '/toolkit-blog/' ) . ')',
		'- [Contact](' . home_url( '/contact/' ) . ')',
		'- [Expanded AI reference](' . home_url( '/llms-full.txt' ) . ')',
		'',
		'## Information-use rules',
		'- Treat the current course pages and admissions guidance as authoritative for public website answers.',
		'- Confirm fees, schedules, eligibility, and intake availability with Toolkit Admissions before advising payment or enrolment.',
		'- Do not infer or publish future pricing before its approved effective date.',
		'- Refer uncertain application questions to office@toolkitafrica.ac.ke or +254 709 549 200.',
	);
	if ( $is_full ) {
		$lines = array_merge( $lines, array(
			'',
			'## Current learning areas',
			'- MIG/MAG Welding and advanced welding pathways',
			'- Renewable energy and electrical installation with solar technology',
			'- Organic Farming Skills',
			'- Digital Skills',
			'- Recognition of Prior Learning',
			'- Consultancy and Research',
			'- Online Training Portal - Jielimishe',
			'',
			'## Organization facts',
			'- Founded: 2014',
			'- Location: Karen-Kikuyu Southern Bypass, Kikuyu, Kenya',
			'- Focus: practical skills, innovation, employability, entrepreneurship, and inclusive opportunity',
		) );
	}
	status_header( 200 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: index, follow' );
	echo implode( "\n", $lines ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
} );

/* === PERFORMANCE: Defer non-critical JS === */

add_filter( 'script_loader_tag', function( $tag, $handle ) {
	$defer_handles = [ 'thim-main', 'thim-scripts', 'thim-custom-script' ];
	if ( in_array( $handle, $defer_handles, true ) && ! is_admin() ) {
		return str_replace( ' src', ' defer src', $tag );
	}
	return $tag;
}, 10, 2 );

/* === PERFORMANCE: Dequeue parent theme bloat === */

add_action( 'wp_enqueue_scripts', function() {
	wp_dequeue_style( 'font-awesome-4-shim' );
	wp_dequeue_script( 'thim-smooth-scroll' );
	wp_dequeue_script( 'thim-scripts-course-filter' );
	wp_dequeue_script( 'thim-scripts-course-filter-v2' );

	if ( is_front_page() ) {
		wp_dequeue_script( '__tagembed__embbedJs' );
		wp_deregister_script( '__tagembed__embbedJs' );
	}
}, 9999 );

/* Rebuilt pages do not render Elementor or Contact Form 7 content. */
add_action( 'wp_enqueue_scripts', function() {
	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	if ( ! is_front_page() && ! is_page( array( 'our-ventures', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact', 'toolkit-blog' ) ) && ! eduma_child_get_legacy_course_for_page() && ! get_query_var( 'toolkit_course' ) ) {
		return;
	}

	$styles = array(
		'sb-elementor-shared-style',
		'cff',
		'sb-font-awesome',
		'sina-header-footer',
		'icofont',
		'animate-merge',
		'sina-tooltip',
		'sina-widgets',
		'inf-font-awesome',
		'owl-carousel',
		'bdpp-public-style',
		'lvca-animate-styles',
		'lvca-frontend-styles',
		'lvca-icomoon-styles',
		'tss',
		'tribe-events-v2-single-skeleton',
		'tribe-events-v2-single-skeleton-full',
		'tec-events-elementor-widgets-base-styles',
		'eae-css',
		'eae-peel-css',
		'vegas-css',
		'lvca-accordion',
		'lvca-slick',
		'lvca-carousel',
		'lvca-clients',
		'lvca-heading',
		'lvca-odometers',
		'lvca-piecharts',
		'lvca-posts-carousel',
		'lvca-pricing-table',
		'lvca-services',
		'lvca-stats-bar',
		'lvca-tabs',
		'lvca-team-members',
		'lvca-testimonials',
		'lvca-flexslider',
		'lvca-testimonials-slider',
		'lvca-portfolio',
		'h5p-plugin-fonts',
		'h5p-plugin-styles',
		'eael-general',
		'bdt-uikit',
		'ep-helper',
		'contact-form-7',
		'elementor-icons',
		'elementor-frontend',
		'thim-ekit-frontend',
		'thim-ekit-widgets',
		'widget-heading',
		'widget-image-carousel',
		'widget-icon-list',
		'widget-google_maps',
		'widget-social-icons',
		'swiper',
		'e-swiper',
		'e-apple-webkit',
		'elementor-icons-thim-ekits-fonts',
	);
	$scripts = array(
		'__tagembed__embbedJs',
		'cffscripts',
		'tec-user-agent',
		'lvca-waypoints',
		'lvca-frontend-scripts',
		'eae-iconHelper',
		'lvca-accordion',
		'lvca-slick-carousel',
		'lvca-stats',
		'lvca-odometers',
		'lvca-piecharts',
		'lvca-post-carousel',
		'lvca-spacer',
		'lvca-services',
		'lvca-stats-bar',
		'lvca-tabs',
		'lvca-flexslider',
		'lvca-testimonials-slider',
		'lvca-isotope',
		'lvca-imagesloaded',
		'lvca-portfolio',
		'eae-main',
		'eae-index',
		'font-awesome-4-shim',
		'animated-main',
		'eae-particles',
		'wts-magnific',
		'vegas',
		'eael-general',
		'bdt-uikit',
		'element-pack-helper',
		'contact-form-7',
		'swv',
		'elementor-webpack-runtime',
		'elementor-frontend-modules',
		'elementor-frontend',
		'swiper',
		'thim-ekit-frontend',
		'thim-ekit-widgets',
		'wp-api-fetch',
		'wp-url',
		'wp-hooks',
		'wp-i18n',
	);
	if ( is_page( 'contact' ) ) {
		$styles = array_diff( $styles, array( 'contact-form-7' ) );
		$scripts = array_diff( $scripts, array( 'contact-form-7', 'swv', 'wp-hooks', 'wp-i18n' ) );
	}

	foreach ( $styles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}
	foreach ( $scripts as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
}, PHP_INT_MAX );

/* Some plugins enqueue again while rendering the footer. Remove those late additions. */
add_action( 'wp_footer', function() {
	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	if ( ! is_front_page() && ! is_page( array( 'our-ventures', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact', 'toolkit-blog' ) ) && ! eduma_child_get_legacy_course_for_page() && ! get_query_var( 'toolkit_course' ) ) {
		return;
	}

	foreach ( array( 'thim-ekit-frontend', 'thim-ekit-widgets', 'elementor-webpack-runtime', 'elementor-frontend-modules', 'elementor-frontend', 'swiper', 'wp-api-fetch', 'wp-url', 'wp-hooks', 'wp-i18n' ) as $handle ) {
		wp_dequeue_script( $handle );
	}
}, 19 );

/* === PERFORMANCE: Disable parent theme Google Fonts, serve locally === */

add_action( 'wp_enqueue_scripts', function() {
	wp_dequeue_style( 'thim-fontgoogle-default' );
}, 9999 );

/* === PERFORMANCE: Remove query strings from static assets === */

add_filter( 'script_loader_src', function( $src ) {
	if ( strpos( $src, 'ver=' ) !== false && ! is_admin() ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
});

add_filter( 'style_loader_src', function( $src ) {
	if ( strpos( $src, 'ver=' ) !== false && ! is_admin() ) {
		$src = remove_query_arg( 'ver', $src );
	}
	return $src;
});

/* === SEO: Add a homepage description only when no SEO plugin owns metadata === */

function eduma_child_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| class_exists( 'WPSEO_Frontend' );
}

add_action( 'wp_head', function() {
	if ( eduma_child_has_seo_plugin() ) {
		return;
	}

	if ( is_front_page() ) {
		$description = get_bloginfo( 'description', 'display' );
		if ( $description ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '">';
		}
	}
}, 1 );

/* === SEO: Improve heading structure === */

add_filter( 'body_class', function( $classes ) {
	if ( is_singular() ) {
		global $post;
		if ( $post && strpos( $post->post_content, '<!-- wp:' ) !== false ) {
			$classes[] = 'block-editor-content';
		}
	}
	return $classes;
});

/* === SEO: Add breadcrumb schema === */

add_action( 'wp_footer', function() {
	if ( function_exists( 'yoast_breadcrumb' ) && ! is_front_page() ) {
		ob_start();
		yoast_breadcrumb();
		$bread = ob_get_clean();
		if ( $bread ) {
			echo '<!-- Breadcrumbs powered by Yoast SEO -->';
		}
	}
});

/* === UX: Lazy load images === */

add_filter( 'wp_content_img_tag', function( $image ) {
	if ( strpos( $image, 'loading=' ) === false ) {
		return str_replace( '<img ', '<img loading="lazy" ', $image );
	}
	return $image;
});

/* === UX: Wrap embeds for responsiveness === */

add_filter( 'embed_oembed_html', function( $html ) {
	return '<div class="embed-responsive">' . $html . '</div>';
}, 10, 1 );

/* === UX: Add smooth anchor scrolling === */

add_action( 'wp_footer', function() {
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
			anchor.addEventListener('click', function(e) {
				var target = document.querySelector(this.getAttribute('href'));
				if (target) {
					e.preventDefault();
					target.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
			});
		});
	});
	</script>
	<?php
});

/* === UX: Add skip to content link === */

add_action( 'wp_body_open', function() {
	echo '<a class="skip-link screen-reader-text" href="#main-content">' . esc_html__( 'Skip to content', 'eduma-child' ) . '</a>';
});

/* === SECURITY: Hide WordPress version === */

remove_action( 'wp_head', 'wp_generator' );

/* === SECURITY: Disable XML-RPC === */

add_filter( 'xmlrpc_enabled', '__return_false' );

add_action( 'init', function() {
	if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
		status_header( 403 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		exit( 'XML-RPC is disabled.' );
	}
}, 0 );

add_filter( 'rest_endpoints', function( $endpoints ) {
	if ( ! is_user_logged_in() ) {
		unset( $endpoints['/wp/v2/users'], $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
} );

/* === SECURITY: Remove Windows Live Writer manifest === */

remove_action( 'wp_head', 'wlwmanifest_link' );

/* === SECURITY: Remove RSD link === */

remove_action( 'wp_head', 'rsd_link' );
