<?php

require_once get_stylesheet_directory() . '/inc/site-metrics.php';
require_once get_stylesheet_directory() . '/inc/application-adapter.php';
require_once get_stylesheet_directory() . '/inc/calling-letter-pdf.php';
require_once get_stylesheet_directory() . '/inc/calling-letters.php';
require_once get_stylesheet_directory() . '/inc/reception-integration.php';
require_once get_stylesheet_directory() . '/inc/cultural-week-stories.php';
require_once get_stylesheet_directory() . '/inc/mosiria-story.php';
require_once get_stylesheet_directory() . '/inc/youtube-feed.php';
require_once get_stylesheet_directory() . '/inc/supplied-stories.php';

function toolkit_editorial_story_preview() {
	$story = toolkit_cultural_week_preview();
	if ( $story ) {
		return $story;
	}
	$story = toolkit_mosiria_story_preview();
	return $story ? $story : toolkit_supplied_story_preview();
}

/**
 * Increment for every public demo/production release. It is included in asset
 * URLs and triggers one server-side object/page-cache purge per environment.
 */
function toolkit_theme_release() {
	return '2026.08.24.3';
}

function toolkit_is_demo_environment() {
	$host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
	return 'demo.toolkitafrica.ac.ke' === $host;
}

function toolkit_canonical_brand_name() {
	return 'The Toolkit for Skills and Innovation';
}

function toolkit_normalize_public_brand_copy( $text ) {
	if ( ! is_string( $text ) || '' === $text ) {
		return $text;
	}
	$text = str_ireplace(
		array(
			'The Toolkit for Skills and Innovation Hub',
			'The Toolkit for Skills and Innovation hub',
			'The Toolkit for Skills &amp; Innovation Hub',
			'The Toolkit for Skills & Innovation Hub',
			'The Toolkit Skills &amp; Innovation Hub',
			'The Toolkit Skills & Innovation Hub',
			'Toolkit Skills &amp; Innovation Hub',
			'Toolkit Skills & Innovation Hub',
			'The Toolkit for Skills &amp; Innovation',
			'The Toolkit for Skills & Innovation',
			'The Toolkit For Skills and Innovation',
			'the hub&#8217;s',
			'the hub’s',
			'at the hub',
		),
		array(
			toolkit_canonical_brand_name(),
			toolkit_canonical_brand_name(),
			toolkit_canonical_brand_name(),
			toolkit_canonical_brand_name(),
			toolkit_canonical_brand_name(),
			toolkit_canonical_brand_name(),
			toolkit_canonical_brand_name(),
			toolkit_canonical_brand_name(),
			toolkit_canonical_brand_name(),
			toolkit_canonical_brand_name(),
			toolkit_canonical_brand_name(),
			'the institution&#8217;s',
			'the institution’s',
			'at the institution',
		),
		$text
	);
	/* Legacy posts contain several spacing/"for" variants not covered above. */
	$text = preg_replace(
		'~\b(?:The\s+)?Toolkit(?:\s+for)?\s+Skills\s+(?:&(?:amp;)?\s*)?and\s+Innovation\s+Hub\b~iu',
		toolkit_canonical_brand_name(),
		$text
	);
	$text = preg_replace( '~\b(?:iShahit|Isahit)\s+hub\b~iu', 'iShahit centre', $text );
	$text = preg_replace(
		'~https?://(?:demo\.)?toolkitafrica\.ac\.ke/new/our-ventures/toolkit-courses-apply-today/?~i',
		home_url( '/apply/' ),
		$text
	);
	$text = str_replace( '/new/our-ventures/toolkit-courses-apply-today/', home_url( '/apply/' ), $text );
	return $text;
}

function toolkit_normalize_schema_brand_copy( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'toolkit_normalize_schema_brand_copy', $value );
	}
	return is_string( $value ) ? toolkit_normalize_public_brand_copy( $value ) : $value;
}

add_filter( 'the_title', 'toolkit_normalize_public_brand_copy', 20 );
add_filter( 'the_title', function( $title, $post_id ) {
	if ( $post_id && 'youth-international-skills-day-12th-august-2025' === get_post_field( 'post_name', $post_id ) ) {
		return 'International Youth Day — 12 August 2025';
	}
	return $title;
}, 30, 2 );
add_filter( 'get_the_excerpt', 'toolkit_normalize_public_brand_copy', 20 );
add_filter( 'the_content', 'toolkit_normalize_public_brand_copy', 20 );
/* Run again after builders/shortcodes that may restore raw imported copy. */
add_filter( 'the_title', 'toolkit_normalize_public_brand_copy', PHP_INT_MAX );
add_filter( 'get_the_excerpt', 'toolkit_normalize_public_brand_copy', PHP_INT_MAX );
add_filter( 'the_content', 'toolkit_normalize_public_brand_copy', PHP_INT_MAX );
add_filter( 'the_content', function( $content ) {
	if ( ! is_singular( 'post' ) ) {
		return $content;
	}
	/* The article template owns the page H1; legacy body headings start at H2. */
	return preg_replace_callback( '/<\/?h1(?=\s|>)/i', static function( $match ) {
		return str_starts_with( strtolower( $match[0] ), '</' ) ? '</h2' : '<h2';
	}, $content );
}, 40 );

/* Repair imported legacy content images that were published with blank alt text. */
add_filter( 'the_content', function( $content ) {
	if ( is_admin() || false === stripos( $content, '<img' ) ) return $content;
	$post_id = get_the_ID();
	$title   = $post_id ? wp_strip_all_tags( get_the_title( $post_id ) ) : toolkit_canonical_brand_name();
	$index   = 0;
	return preg_replace_callback( '/<img\b[^>]*>/i', static function( $match ) use ( $title, &$index ) {
		$tag = $match[0];
		if ( preg_match( '/\balt\s*=\s*(["\x27])(.*?)\1/i', $tag, $alt_match ) && '' !== trim( wp_strip_all_tags( html_entity_decode( $alt_match[2] ) ) ) ) {
			return $tag;
		}
		$index++;
		$alt = '';
		if ( preg_match( '/\bsrc\s*=\s*(["\x27])(.*?)\1/i', $tag, $src_match ) && $src_match[2] ) {
			$attachment_id = attachment_url_to_postid( html_entity_decode( $src_match[2] ) );
			if ( $attachment_id ) {
				$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );
				if ( ! $alt ) $alt = trim( (string) get_the_title( $attachment_id ) );
			}
		}
		if ( ! $alt ) $alt = sprintf( '%s — story image %d', $title, $index );
		$tag = preg_replace( '/\s+alt\s*=\s*(["\x27]).*?\1/i', '', $tag );
		return preg_replace( '/\s*\/?>$/', ' alt="' . esc_attr( $alt ) . '">', $tag );
	}, $content );
}, 50 );

/* Ensure legacy featured-image helpers describe meaningful thumbnails. */
add_filter( 'wp_get_attachment_image_attributes', function( $attributes, $attachment ) {
	if ( ! empty( $attributes['alt'] ) ) return $attributes;
	$parent_id = $attachment instanceof WP_Post ? (int) $attachment->post_parent : 0;
	if ( $parent_id ) $attributes['alt'] = wp_strip_all_tags( get_the_title( $parent_id ) );
	if ( empty( $attributes['alt'] ) && $attachment instanceof WP_Post ) {
		$attributes['alt'] = wp_strip_all_tags( get_the_title( $attachment ) );
	}
	return $attributes;
}, 20, 2 );
add_filter( 'wpseo_title', 'toolkit_normalize_public_brand_copy', 20 );
add_filter( 'wpseo_metadesc', 'toolkit_normalize_public_brand_copy', 20 );
add_filter( 'wpseo_opengraph_title', 'toolkit_normalize_public_brand_copy', 20 );
add_filter( 'wpseo_opengraph_desc', 'toolkit_normalize_public_brand_copy', 20 );
add_filter( 'wpseo_schema_graph', 'toolkit_normalize_schema_brand_copy', 20 );

/* Keep database-backed theme and plugin output aligned with the public brand. */
add_filter( 'pre_option_blogname', function( $pre_option ) {
	return eduma_child_redesign_enabled() ? toolkit_canonical_brand_name() : $pre_option;
} );
add_filter( 'wpseo_opengraph_site_name', function( $site_name ) {
	return eduma_child_redesign_enabled() ? toolkit_canonical_brand_name() : $site_name;
} );

function toolkit_asset_version( $path ) {
	return toolkit_theme_release() . '.' . ( file_exists( $path ) ? filemtime( $path ) : '0' );
}

add_action( 'init', function() {
	if ( get_option( 'toolkit_theme_release' ) === toolkit_theme_release() ) {
		return;
	}

	wp_cache_flush();
	do_action( 'litespeed_purge_all' );
	do_action( 'litespeed_purge_cssjs' );
	update_option( 'toolkit_theme_release', toolkit_theme_release(), false );
}, 1 );

add_action( 'send_headers', function() {
	if ( eduma_child_is_custom_surface() ) {
		header( 'X-Toolkit-Release: ' . toolkit_theme_release() );
	}
} );

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

function toolkit_speak_up_enabled() {
	$host          = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
	$local_default = in_array( $host, array( 'demo.toolkitafrica.ac.ke', '127.0.0.1:8001', 'localhost:8001' ), true );
	return eduma_child_switch( 'TOOLKIT_SPEAK_UP_ENABLED', 'toolkit_speak_up_enabled', $local_default );
}

function eduma_child_is_custom_surface() {
	if ( ! eduma_child_redesign_enabled() || is_admin() ) {
		return false;
	}
	if ( get_query_var( 'toolkit_showcase' ) || get_query_var( 'toolkit_connect' ) || get_query_var( 'toolkit_reception' ) || ( toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' ) ) || is_front_page() || is_singular( 'post' ) || is_page( array( 'our-ventures', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact', 'toolkit-blog', 'gallery-2', 'tti-media' ) ) || get_query_var( 'toolkit_course' ) ) {
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
	$brand_ver = toolkit_asset_version( get_stylesheet_directory() . '/brand-tokens.css' );
	wp_enqueue_style( 'eduma-child-brand-tokens', get_stylesheet_directory_uri() . '/brand-tokens.css', array(), $brand_ver );

	if ( is_front_page() && eduma_child_redesign_enabled() ) {
		$css_ver = toolkit_asset_version( get_stylesheet_directory() . '/hero-slider.css' );
		wp_enqueue_style( 'eduma-child-hero-slider', get_stylesheet_directory_uri() . '/hero-slider.css', array( 'eduma-child-brand-tokens' ), $css_ver );

		$js_ver = toolkit_asset_version( get_stylesheet_directory() . '/hero-slider.js' );
		wp_enqueue_script( 'eduma-child-hero-slider', get_stylesheet_directory_uri() . '/hero-slider.js', array(), $js_ver, true );
		$experience_path = get_stylesheet_directory() . '/assets/js/home-experience.js';
		wp_enqueue_script( 'toolkit-home-experience', get_stylesheet_directory_uri() . '/assets/js/home-experience.js', array(), toolkit_asset_version( $experience_path ), true );
	}

	if ( eduma_child_redesign_enabled() && ( get_query_var( 'toolkit_showcase' ) || ( toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' ) ) || is_singular( 'post' ) || is_page( array( 'our-ventures', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact', 'toolkit-blog', 'gallery-2', 'tti-media' ) ) || $legacy_course || get_query_var( 'toolkit_course' ) ) ) {
		$page_css_ver = toolkit_asset_version( get_stylesheet_directory() . '/page-redesign.css' );
		$page_js_ver  = toolkit_asset_version( get_stylesheet_directory() . '/page-redesign.js' );
		wp_enqueue_style( 'eduma-child-page-redesign', get_stylesheet_directory_uri() . '/page-redesign.css', array( 'eduma-child-brand-tokens' ), $page_css_ver );
		wp_enqueue_script( 'eduma-child-page-redesign', get_stylesheet_directory_uri() . '/page-redesign.js', array(), $page_js_ver, true );
	}

	if ( eduma_child_redesign_enabled() && is_page( 'toolkit-courses-apply-today' ) ) {
		$application_path = get_stylesheet_directory() . '/assets/js/application-form.js';
		$captcha_site_key = toolkit_mzizi_submission_enabled() ? toolkit_application_turnstile_site_key() : '';
		if ( $captcha_site_key ) {
			wp_enqueue_script( 'cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true );
		}
		wp_enqueue_script( 'toolkit-application-form', get_stylesheet_directory_uri() . '/assets/js/application-form.js', array(), toolkit_asset_version( $application_path ), true );
		wp_localize_script( 'toolkit-application-form', 'toolkitApplication', array(
			'endpoint'          => esc_url_raw( rest_url( 'toolkit/v1/application/submit' ) ),
			'optionsEndpoint'   => esc_url_raw( rest_url( 'toolkit/v1/application/options' ) ),
			'coursesEndpoint'   => esc_url_raw( rest_url( 'toolkit/v1/application/courses' ) ),
			'intakesEndpoint'   => esc_url_raw( rest_url( 'toolkit/v1/application/intakes' ) ),
			/* Cache-safe, not user-bound — see toolkit_application_form_token(). */
			'formToken'         => toolkit_application_form_token(),
			'captchaSiteKey'    => $captcha_site_key,
			'admissionsEmail'   => 'office@toolkitafrica.ac.ke',
			'admissionsPhone'   => '+254 709 549 200',
			'admissionsWhatsApp'=> '+254 711 802 855',
		) );
	}

	if ( eduma_child_redesign_enabled() && get_query_var( 'toolkit_connect' ) ) {
		$page_css_ver = toolkit_asset_version( get_stylesheet_directory() . '/page-redesign.css' );
		$connect_ver  = toolkit_asset_version( get_stylesheet_directory() . '/assets/css/connect.css' );
		wp_enqueue_style( 'eduma-child-page-redesign', get_stylesheet_directory_uri() . '/page-redesign.css', array( 'eduma-child-brand-tokens' ), $page_css_ver );
		wp_enqueue_style( 'toolkit-connect', get_stylesheet_directory_uri() . '/assets/css/connect.css', array( 'eduma-child-page-redesign' ), $connect_ver );
	}

	if ( eduma_child_redesign_enabled() && get_query_var( 'toolkit_reception' ) ) {
		$page_css_ver = toolkit_asset_version( get_stylesheet_directory() . '/page-redesign.css' );
		$form_css     = get_stylesheet_directory() . '/assets/css/reception-form.css';
		$form_js      = get_stylesheet_directory() . '/assets/js/reception-form.js';
		wp_enqueue_style( 'eduma-child-page-redesign', get_stylesheet_directory_uri() . '/page-redesign.css', array( 'eduma-child-brand-tokens' ), $page_css_ver );
		wp_enqueue_style( 'toolkit-reception-form', get_stylesheet_directory_uri() . '/assets/css/reception-form.css', array( 'eduma-child-page-redesign' ), toolkit_asset_version( $form_css ) );
		wp_enqueue_script( 'toolkit-reception-form', get_stylesheet_directory_uri() . '/assets/js/reception-form.js', array(), toolkit_asset_version( $form_js ), true );
		wp_localize_script( 'toolkit-reception-form', 'toolkitReception', array(
			'endpoint'  => esc_url_raw( rest_url( 'toolkit/v1/reception/submit' ) ),
			/* Cache-safe, not user-bound — see toolkit_application_form_token(). */
			'formToken' => toolkit_application_form_token(),
		) );
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
		if ( eduma_child_is_custom_surface() ) {
			$classes[] = 'toolkit-modern-surface';
		}
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
	if ( get_query_var( 'toolkit_reception' ) ) {
		return get_stylesheet_directory() . '/template-parts/pages/reception.php';
	}
	if ( toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' ) ) {
		return get_stylesheet_directory() . '/template-parts/pages/speak-up.php';
	}
	if ( 'graduation' === get_query_var( 'toolkit_showcase' ) ) {
		return get_stylesheet_directory() . '/template-parts/pages/graduation.php';
	}
	if ( 'testimonials' === get_query_var( 'toolkit_showcase' ) ) {
		return get_stylesheet_directory() . '/template-parts/pages/testimonials.php';
	}
	if ( 'footprint' === get_query_var( 'toolkit_showcase' ) ) {
		return get_stylesheet_directory() . '/template-parts/pages/footprint.php';
	}

	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	if ( eduma_child_get_legacy_course_for_page() ) {
		return get_stylesheet_directory() . '/template-parts/pages/course-detail.php';
	}

	$page_templates = array(
		'about-toolkit-africa'          => 'template-parts/pages/institutional.php',
		'toolkit-in-brief'              => 'template-parts/pages/institutional.php',
		'the-toolkit-foundation-copy'  => 'template-parts/pages/institutional.php',
		'the-toolkit-foundation'       => 'template-parts/pages/institutional.php',
		'contact'                      => 'template-parts/pages/contact.php',
		'speak-up'                     => 'template-parts/pages/speak-up.php',
		'toolkit-blog'                 => 'template-parts/pages/blog.php',
		'gallery-2'                    => 'template-parts/pages/media-gallery.php',
		'tti-media'                    => 'template-parts/pages/media-gallery.php',
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

/* Outcome and trust pages remain theme-owned so their evidence-led layouts do
 * not depend on legacy builders or placeholder database records. */
add_action( 'init', function() {
	$route_version = eduma_child_redesign_enabled() ? '2026-2-on' : '2026-2-off';
	if ( eduma_child_redesign_enabled() ) {
		add_rewrite_rule( '^graduation/?$', 'index.php?toolkit_showcase=graduation', 'top' );
		add_rewrite_rule( '^testimonials/?$', 'index.php?toolkit_showcase=testimonials', 'top' );
		add_rewrite_rule( '^footprint/?$', 'index.php?toolkit_showcase=footprint', 'top' );
	}
	if ( $route_version !== get_option( 'eduma_child_showcase_route_version' ) ) {
		flush_rewrite_rules( false );
		update_option( 'eduma_child_showcase_route_version', $route_version, false );
	}
} );

/* Database-independent restricted speak-up page. */
add_action( 'init', function() {
	$route_version = toolkit_speak_up_enabled() ? '2026-2-on' : '2026-2-off';
	if ( eduma_child_redesign_enabled() && toolkit_speak_up_enabled() ) add_rewrite_rule( '^speak-up/?$', 'index.php?toolkit_speak_up=1', 'top' );
	if ( get_option( 'eduma_child_speak_up_route_version' ) !== $route_version ) { flush_rewrite_rules( false ); update_option( 'eduma_child_speak_up_route_version', $route_version, false ); }
} );

/* Database-independent public reception request page. */
add_action( 'init', function() {
	$route_version = eduma_child_redesign_enabled() ? '2026-1-on' : '2026-1-off';
	if ( eduma_child_redesign_enabled() ) {
		add_rewrite_rule( '^reception/?$', 'index.php?toolkit_reception=1', 'top' );
	}
	if ( $route_version !== get_option( 'eduma_child_reception_route_version' ) ) {
		flush_rewrite_rules( false );
		update_option( 'eduma_child_reception_route_version', $route_version, false );
	}
} );

/* Keep the database-backed application page on the concise public URL. */
add_action( 'init', function() {
	$page          = get_page_by_path( 'our-ventures/toolkit-courses-apply-today' );
	$route_version = $page ? '2026-1-' . $page->ID : '2026-1-missing';
	if ( $page ) {
		add_rewrite_rule( '^apply/?$', 'index.php?page_id=' . (int) $page->ID, 'top' );
	}
	if ( $route_version !== get_option( 'eduma_child_apply_route_version' ) ) {
		flush_rewrite_rules( false );
		update_option( 'eduma_child_apply_route_version', $route_version, false );
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
	$vars[] = 'toolkit_reception';
	$vars[] = 'toolkit_speak_up';
	$vars[] = 'toolkit_showcase';
	return $vars;
} );

add_action( 'template_redirect', function() {
	if ( in_array( get_query_var( 'toolkit_showcase' ), array( 'graduation', 'testimonials', 'footprint' ), true ) ) {
		global $wp_query;
		$wp_query->is_404 = false;
		$wp_query->is_home = false;
		$wp_query->is_archive = false;
		status_header( 200 );
	}
	if ( get_query_var( 'toolkit_speak_up' ) ) {
		global $wp_query;
		if ( ! toolkit_speak_up_enabled() ) {
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		$wp_query->is_404     = false;
		$wp_query->is_home    = false;
		$wp_query->is_archive = false;
		status_header( 200 );
	}
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
	if ( get_query_var( 'toolkit_reception' ) ) {
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
	return in_array( untrailingslashit( $path ), array( '/llms.txt', '/llms-full.txt', '/apply', '/graduation', '/testimonials', '/footprint' ), true ) ? false : $redirect_url;
}, 10, 2 );

/* Retire unused public routes without deleting their WordPress records. */
add_action( 'template_redirect', function() {
	$request_path = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : '';
	if ( '/new/our-ventures/toolkit-courses-apply-today' === untrailingslashit( $request_path ) ) {
		wp_safe_redirect( home_url( '/apply/' ), 301, 'The Toolkit for Skills and Innovation' );
		exit;
	}
	if ( is_page( 'toolkit-courses-apply-today' ) && '/apply' !== untrailingslashit( $request_path ) ) {
		wp_safe_redirect( home_url( '/apply/' ), 301, 'The Toolkit for Skills and Innovation' );
		exit;
	}
	$retired_routes = array(
		'/students-portal'             => '/our-ventures/',
		'/courses'                     => '/our-ventures/',
		'/blog'                        => '/toolkit-blog/',
		'/research'                    => '/our-ventures/tti-consultancy-and-research/',
		'/the-toolkit-foundation-copy' => '/the-toolkit-foundation/',
	);
	$retired_path   = untrailingslashit( $request_path );
	if ( isset( $retired_routes[ $retired_path ] ) ) {
		wp_safe_redirect( home_url( $retired_routes[ $retired_path ] ), 301, 'The Toolkit for Skills and Innovation' );
		exit;
	}
}, 1 );

function eduma_child_non_public_page_slugs() {
	return array(
		'students-portal',
		'eventer-shortcode-preview-page',
		'courses',
		'blog',
		'research',
		'the-toolkit-foundation-copy',
		'account',
		'user-register',
		'user-login',
		'forgot-password',
		'reset-password',
		'user-account',
		'my-account',
		'lp-profile',
		'lp-term-conditions',
		'lp-become-a-teacher',
		'lp-checkout',
		'instructors',
		'instructor',
	);
}

/**
 * Imported staff plugins and builder kits duplicate the authoritative About
 * page without adding standalone search value. Keep them available to
 * WordPress administrators, but out of public search indexes and sitemaps.
 */
function toolkit_non_indexable_post_types() {
	return array( 'wps-team-members', 'sptp_member', 'thim_elementor_kit' );
}

function toolkit_non_indexable_post_slugs() {
	return array( 'test-blog-post-1', '__trashed', 'toolkit-makes-history-monday-5-february-2024-statement-on-skills-in-advanced-welding-at-radisson-blu-presided-by-president-ruto-and-polish-president-duda-copy' );
}

add_filter( 'wpseo_sitemap_exclude_post_type', function( $excluded, $post_type ) {
	return in_array( $post_type, toolkit_non_indexable_post_types(), true ) ? true : $excluded;
}, 20, 2 );

add_filter( 'wpseo_sitemap_exclude_taxonomy', function( $excluded, $taxonomy ) {
	return 'category' === $taxonomy ? true : $excluded;
}, 20, 2 );

add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function( $ids ) {
	foreach ( toolkit_non_indexable_post_slugs() as $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'post' );
		if ( $post ) {
			$ids[] = (int) $post->ID;
		}
	}
	return array_values( array_unique( $ids ) );
}, 20 );

add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function( $ids ) {
	foreach ( eduma_child_non_public_page_slugs() as $path ) {
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
	$post_type = get_post_type();
	$post_slug = is_singular() ? get_post_field( 'post_name', get_queried_object_id() ) : '';
	if (
		toolkit_is_demo_environment()
		|| is_author()
		|| is_category()
		|| is_page( eduma_child_non_public_page_slugs() )
		|| in_array( $post_type, toolkit_non_indexable_post_types(), true )
		|| in_array( $post_slug, toolkit_non_indexable_post_slugs(), true )
	) {
		$robots['index']  = 'noindex';
		$robots['follow'] = 'follow';
	}
	return $robots;
} );

/* Keep the public demo out of search indexes without weakening production. */
add_filter( 'wp_robots', function( $robots ) {
	if ( toolkit_is_demo_environment() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['index'], $robots['nofollow'] );
	}
	return $robots;
} );

/* === SEO: curated metadata for child-theme page rebuilds === */

function toolkit_story_seo_copy( $post_id ) {
	$slug = get_post_field( 'post_name', $post_id );
	$copy = array(
		'icm-tvet-uk-visits-the-toolkit' => array(
			'title'       => 'ICM-TVET UK Visit | Toolkit Partnership Story',
			'description' => 'The ICM-TVET UK visit explored collaboration, recognised programmes and Toolkit’s practical skills-training facilities in Kikuyu, Kenya.',
		),
		'geofrey-mosiria-visits-the-toolkit' => array(
			'title'       => 'Geoffrey Mosiria Visits Toolkit | Official Visit',
			'description' => 'Geoffrey Omatoke Mosiria toured Toolkit training facilities in Kikuyu and spoke with learners about practical skills, employment and entrepreneurship.',
		),
		'africa-forward-youth-innovation-day-career-fair-2026' => array(
			'title'       => 'Africa Forward Youth & Innovation Day | Toolkit',
			'description' => 'See how Toolkit presented welding, solar and language training at the Africa Forward Youth and Innovation Day career fair in Nairobi.',
		),
		'alumni-mentorship-success-stories-2026' => array(
			'title'       => 'Toolkit Alumni Mentorship | Career Lessons',
			'description' => 'Toolkit alumni returned to share practical career lessons, challenges and opportunities with current trainees during a mentorship event in Kikuyu.',
		),
		'cultural-week-official-wear-day' => array(
			'title'       => 'Official Wear Day | Toolkit Cultural Week 2026',
			'description' => 'Toolkit students and staff marked Official Wear Day with a celebration of professionalism, confidence and workplace readiness.',
		),
		'cultural-week-golden-oldies-day' => array(
			'title'       => 'Golden Oldies Day | Toolkit Cultural Week 2026',
			'description' => 'Golden Oldies Day brought classic style and shared memories to Toolkit Cultural Week in Kikuyu on 14 July 2026.',
		),
		'cultural-week-african-wear-day' => array(
			'title'       => 'African Wear Day | Toolkit Cultural Week 2026',
			'description' => 'African Wear Day celebrated heritage, unity and diversity through traditional dress during Toolkit Cultural Week in Kikuyu.',
		),
		'cultural-week-career-wear-day' => array(
			'title'       => 'Career Wear Day | Toolkit Cultural Week 2026',
			'description' => 'Career Wear Day helped Toolkit learners connect personal presentation with professional identity and future work pathways.',
		),
		'youth-international-skills-day-12th-august-2025' => array(
			'title'       => 'International Youth Day 2025 | Toolkit',
			'description' => 'Toolkit marked International Youth Day on 12 August 2025 with reflections on practical training, youth opportunity, employment and entrepreneurship.',
		),
		'careers-in-mig-mag-welding-insights-from-our-webinar' => array(
			'title'       => 'MIG/MAG Welding Careers Webinar | Toolkit',
			'description' => 'Explore career pathways, industry applications and practical insights discussed during Toolkit’s MIG/MAG welding careers webinar.',
		),
		'toolkit-shines-with-tujiajiri-mentorship-program-for-solar-energy-trainees' => array(
			'title'       => 'Solar Energy Trainee Mentorship | Toolkit',
			'description' => 'Toolkit solar trainees gained career guidance and industry insight through the Tujiajiri mentorship programme.',
		),
		'ilo-youth-employment-training-workshop' => array(
			'title'       => 'ILO Youth Employment Workshop | Toolkit',
			'description' => 'Toolkit staff explored youth employability, entrepreneurship and training practice during an ILO youth employment workshop.',
		),
		'igniting-her-future-innovateher-roll-out-at-the-toolkit-for-skills-and-innovation-hub' => array(
			'title'       => 'InnovateHER STEM-TVET Programme | Toolkit',
			'description' => 'InnovateHER introduced young women to STEM-TVET and vocational career pathways through practical technology and skills experiences at Toolkit.',
		),
		'dont-miss-this-insightful-podcast-on-youth-skills-and-job-creation-in-africa-%f0%9f%a7%a0%f0%9f%92%bc' => array(
			'title'       => 'Youth Skills & Job Creation Podcast | Toolkit',
			'description' => 'Jane Muigai Kamphuis and Caroline Njuki discussed ecosystem thinking, youth skills and scalable employment outcomes in Africa.',
		),
		'in-the-news' => array(
			'title'       => 'Toolkit in the News: NTV Coverage | Toolkit 2022',
			'description' => 'Watch NTV coverage featuring The Toolkit and its work advancing practical skills, employability and opportunity for young people in Kenya.',
		),
		'in-the-news-2' => array(
			'title'       => 'Toolkit in the News: The Star Coverage | Toolkit 2022',
			'description' => 'Read The Star coverage of Kenya’s plans to recognise Jua Kali artisans and the role of skills assessment and certification.',
		),
		'toolkit-iskills-partnership-don-bosco-boys-town' => array(
			'title'       => 'Toolkit iSkills and Don Bosco Boys’ Town | Toolkit 2019',
			'description' => 'Toolkit iSkills partnered with Don Bosco Boys’ Town to strengthen electrical, refrigeration and air-conditioning training facilities.',
		),
	);
	return isset( $copy[ $slug ] ) ? $copy[ $slug ] : array();
}

function toolkit_seo_trim_chars( $text, $maximum ) {
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( (string) $text ) ) );
	if ( mb_strlen( $text ) <= $maximum ) {
		return $text;
	}
	$trimmed = mb_substr( $text, 0, max( 1, $maximum - 1 ) );
	$space   = mb_strrpos( $trimmed, ' ' );
	if ( false !== $space && $space > (int) floor( $maximum * 0.65 ) ) {
		$trimmed = mb_substr( $trimmed, 0, $space );
	}
	return rtrim( $trimmed, " \t\n\r\0\x0B,.;:–—-" ) . '…';
}

function toolkit_prepare_seo_description( $text, $story_title = '' ) {
	$text = html_entity_decode( (string) $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( $text ) ) ) );
	if ( '' === $text ) {
		$text = 'Read this Toolkit update about ' . rtrim( $story_title, '.!? ' ) . '.';
	}
	if ( mb_strlen( $text ) < 100 ) {
		$text .= ' Discover more about practical skills, training and pathways to work and enterprise.';
	}
	if ( mb_strlen( $text ) < 100 ) {
		$text .= ' Read the full update.';
	}
	return toolkit_seo_trim_chars( $text, 160 );
}

function eduma_child_redesigned_page_metadata() {
	if ( ! eduma_child_redesign_enabled() ) {
		return false;
	}
	if ( is_front_page() ) {
		return array(
			'title'       => 'Practical Skills Training in Kenya | The Toolkit',
			'description' => 'The Toolkit equips young people and women in Kenya with practical vocational skills, recognised assessment and pathways to employment or entrepreneurship.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/toolkit-social-home.webp',
		);
	}
	if ( get_query_var( 'toolkit_connect' ) ) {
		return array(
			'title'       => 'Connect with The Toolkit | Admissions and Courses',
			'description' => 'Explore Toolkit courses, start an application, read current notices, contact admissions and follow our official channels for practical skills updates.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/toolkit-social-home.webp',
		);
	}
	if ( get_query_var( 'toolkit_reception' ) ) {
		return array(
			'title'       => 'Reception | The Toolkit for Skills and Innovation',
			'description' => 'Send a reception request to The Toolkit for Skills and Innovation before your visit to our training centre in Kikuyu, Kenya.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/toolkit-social-home.webp',
		);
	}
	if ( 'footprint' === get_query_var( 'toolkit_showcase' ) ) {
		return array(
			'title'       => 'Toolkit Footprint | A Decade in Youth Skills and Employability',
			'description' => 'A dated record of Toolkit programmes in youth skills and employability from 2014, delivered with partners including NITA, UK aid, World Vision, ILO, UNESCO and GIZ.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/about.jpg',
		);
	}
	if ( 'graduation' === get_query_var( 'toolkit_showcase' ) ) {
		return array(
			'title'       => 'Toolkit Graduation | Skills, Achievement and Progress',
			'description' => 'Explore Toolkit graduation moments, learner achievements and the practical training journeys celebrated by our institution in Kikuyu, Kenya.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/graduation/kmm-2071-jpg.webp',
		);
	}
	if ( 'testimonials' === get_query_var( 'toolkit_showcase' ) ) {
		return array(
			'title'       => 'Toolkit Testimonials | Graduate and Learner Voices',
			'description' => 'Hear attributable Toolkit testimonials from graduates and learners about practical training, confidence, careers and progression into opportunity.',
			'image'       => 'https://i.ytimg.com/vi/VOIpU5tRRvo/maxresdefault.jpg',
		);
	}
	if ( toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' ) ) {
		return array(
			'title'       => 'Speak Up Safely | The Toolkit for Skills and Innovation',
			'description' => 'Use the dedicated Toolkit speak-up channel to report safety, misconduct, fraud, harassment, discrimination, or another serious concern for restricted review.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/toolkit-social-home.webp',
		);
	}
	if ( is_singular( 'post' ) ) {
		$post_id     = get_queried_object_id();
		$seo_copy    = toolkit_story_seo_copy( $post_id );
		$story_title = toolkit_normalize_public_brand_copy( get_the_title( $post_id ) );
		$suffix      = ' | Toolkit ' . get_the_date( 'Y', $post_id );
		$post_title  = $seo_copy ? $seo_copy['title'] : toolkit_seo_trim_chars( $story_title, 60 - mb_strlen( $suffix ) ) . $suffix;
		$description = get_the_excerpt( $post_id );
		if ( $seo_copy ) {
			$description = $seo_copy['description'];
		} elseif ( ! $description ) {
			$description = get_post_field( 'post_content', $post_id );
		}
		return array(
			'title'       => $post_title,
			'description' => toolkit_normalize_public_brand_copy( toolkit_prepare_seo_description( $description, $story_title ) ),
			'image'       => toolkit_story_image_url( $post_id, 'full' ),
		);
	}
	$course_slug = get_query_var( 'toolkit_course' );
	if ( $course_slug ) {
		require_once get_stylesheet_directory() . '/inc/course-catalog.php';
		$course = eduma_child_get_course( sanitize_key( $course_slug ) );
		if ( $course ) {
			return array(
				'title'       => $course['title'] . ' | The Toolkit for Skills and Innovation',
				'description' => $course['short'] . ' Review 2026 entry requirements, duration, fees, intakes, and application steps.',
				'image'       => $course['image'],
			);
		}
	}
	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	$legacy_course = eduma_child_get_legacy_course_for_page();
	if ( $legacy_course ) {
		$legacy_title       = isset( $legacy_course['seo_title'] ) ? $legacy_course['seo_title'] : $legacy_course['title'] . ' | The Toolkit for Skills and Innovation';
		$legacy_description = isset( $legacy_course['seo_description'] ) ? $legacy_course['seo_description'] : $legacy_course['short'] . ' Review the learning focus, delivery details, and application steps.';
		if ( is_page( 'construction-sector-skills' ) ) {
			$legacy_title       = 'MIG/MAG Welding Course in Kenya | Toolkit';
			$legacy_description = 'Explore practical MIG/MAG welding training in Kikuyu with workshop practice, VR-supported learning and application guidance.';
		}
		return array(
			'title'       => $legacy_title,
			'description' => $legacy_description,
			'image'       => $legacy_course['image'],
		);
	}
	if ( is_page( 'our-ventures' ) ) {
		return array(
			'title'       => 'Our Courses | The Toolkit for Skills and Innovation',
			'description' => 'Explore practical courses from The Toolkit for Skills and Innovation in welding, renewable energy, digital skills, agriculture, and enterprise-focused learning.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/about.jpg',
		);
	}
	if ( is_page( 'about-toolkit-africa' ) ) {
		return array(
			'title'       => 'Skills Training in Kenya | About The Toolkit',
			'description' => 'Skills training in Kenya from The Toolkit combines practical learning, recognised assessment, industry exposure and clear pathways to work.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/about.jpg',
		);
	}
	if ( is_page( 'the-toolkit-foundation-copy' ) ) {
		return array(
			'title'       => 'Impact and Insights | The Toolkit for Skills and Innovation',
			'description' => 'Explore how The Toolkit for Skills and Innovation connects practical learning with employability, industrial exposure, livelihoods, and inclusive opportunity.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/impact.jpg',
		);
	}
	if ( is_page( 'the-toolkit-foundation' ) ) {
		return array(
			'title'       => 'Toolkit Foundation | Inclusive Skills Development',
			'description' => 'Toolkit Foundation supports inclusive technical, digital, green and enterprise skills development for underserved communities in Kenya.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/foundation.jpg',
		);
	}
	if ( is_page( 'contact' ) ) {
		return array(
			'title'       => 'Contact Toolkit | Admissions and Partnerships',
			'description' => 'Contact Toolkit about courses, admissions, partnerships or visits to our practical training centre on the Karen-Kikuyu Southern Bypass.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/contact.jpg',
		);
	}
	if ( is_page( 'toolkit-blog' ) ) {
		return array(
			'title'       => 'Toolkit Blog | Skills, Innovation and Opportunity',
			'description' => 'Read The Toolkit for Skills and Innovation news, learner stories, skills insights, programme updates, and perspectives on youth employment and innovation.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/blogs/legacy-context/blog-field-skills.jpg',
		);
	}
	if ( is_home() || is_page( 'blog' ) ) {
		return array(
			'title'       => 'Latest Toolkit News and Updates | Skills in Kenya',
			'description' => 'Read current Toolkit news, learner stories, programme updates and practical insights about skills, employment, enterprise and innovation in Kenya.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/blogs/legacy-context/blog-field-skills.jpg',
		);
	}
	if ( is_page( 'construction-sector-skills' ) ) {
		return array(
			'title'       => 'MIG/MAG Welding Training | The Toolkit for Skills and Innovation',
			'description' => 'Develop practical MIG/MAG welding skills with hands-on training, VR-enabled learning, and career-focused support at The Toolkit for Skills and Innovation.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/welding.jpg',
		);
	}

	if ( is_page( 'notice-board' ) ) {
		return array(
			'title'       => 'Notice Board | The Toolkit for Skills and Innovation',
			'description' => 'Find current announcements, admissions guidance, opportunities, events, and important notices from The Toolkit for Skills and Innovation.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/notice-board.jpg',
		);
	}

	if ( is_page( 'toolkit-courses-apply-today' ) ) {
		return array(
			'title'       => 'Apply for a Course | The Toolkit for Skills and Innovation',
			'description' => 'Prepare your course application to The Toolkit for Skills and Innovation, review the admission steps, and continue securely to the online application portal.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/about.jpg',
		);
	}
	if ( is_page( 'privacy-policy' ) ) {
		return array(
			'title'       => 'Privacy Policy | The Toolkit for Skills and Innovation',
			'description' => 'Read how The Toolkit handles website, enquiry, application and communications information, including your privacy choices and contact routes.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/toolkit-social-home.webp',
		);
	}
	if ( is_page( 'research' ) ) {
		return array(
			'title'       => 'Skills and Employment Research | The Toolkit for Skills and Innovation',
			'description' => 'Explore The Toolkit for Skills and Innovation research and evidence on vocational skills, youth employment, industry needs, green jobs, and practical workforce development.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/foundation.jpg',
		);
	}
	if ( is_page( 'building-young-female-farmers-of-tomorrow' ) ) {
		return array(
			'title'       => 'Young Women in Agriculture | Toolkit',
			'description' => 'Learn about The Toolkit for Skills and Innovation initiatives supporting young women to build practical agriculture, enterprise, and livelihood skills.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/courses/experiences/organic-farm.jpg',
		);
	}
	if ( is_page( 'toolkit-in-brief' ) ) {
		return array(
			'title'       => 'Toolkit in Brief | Skills Training Model',
			'description' => 'Toolkit in Brief explains our mission, values and skills training model for practical learning, recognised assessment, employment and enterprise.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/pages/about.jpg',
		);
	}
	if ( is_page( 'tti-media' ) ) {
		return array(
			'title'       => 'Toolkit Videos | Practical Skills in Action',
			'description' => 'Toolkit videos show learners, trainers, workshops, partnerships and practical vocational skills programmes in action.',
			'image'       => get_stylesheet_directory_uri() . '/assets/images/blogs/legacy-context/welding-vr-training.jpg',
		);
	}
	if ( is_page( 'gallery-2' ) ) {
		return array(
			'title'       => 'Toolkit Training Gallery | Skills in Action',
			'description' => 'Browse Toolkit training gallery images from workshops, learner activities, practical courses, partnerships and community programmes.',
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

add_filter( 'wpseo_twitter_description', function( $description ) {
	$metadata = eduma_child_redesigned_page_metadata();
	return $metadata ? $metadata['description'] : $description;
} );

/*
 * Redesigned pages and editorial stories are partly rendered by the child
 * theme rather than stored in the block editor. Give Yoast the same visible
 * <main> content that search engines receive, otherwise its editor analysis
 * incorrectly reports thin copy, no links, and no images.
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$post_id = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
	if ( ! $post_id || ! in_array( get_post_type( $post_id ), array( 'page', 'post' ), true ) || 'publish' !== get_post_status( $post_id ) ) {
		return;
	}

	wp_enqueue_script( 'jquery' );
	wp_localize_script( 'jquery', 'toolkitYoastRenderedContent', array(
		'url'      => get_permalink( $post_id ),
		'selector' => 'main.toolkit-page',
	) );
	wp_add_inline_script( 'jquery', <<<'JS'
( function( $ ) {
	'use strict';
	var config = window.toolkitYoastRenderedContent || {};
	var renderedContent = '';
	var registered = false;

	function prepareContent( html ) {
		var documentCopy = new DOMParser().parseFromString( html, 'text/html' );
		var content = documentCopy.querySelector( config.selector || 'main.toolkit-page' );
		if ( ! content ) return '';
		content.querySelectorAll( 'script, style, noscript, template' ).forEach( function( element ) {
			element.remove();
		} );
		return content.innerHTML;
	}

	function registerAnalysisContent() {
		if ( registered || ! renderedContent || typeof window.YoastSEO === 'undefined' || ! window.YoastSEO.app || typeof window.YoastSEO.app.registerPlugin !== 'function' || typeof window.YoastSEO.app.registerModification !== 'function' ) return;
		window.YoastSEO.app.registerPlugin( 'ToolkitRenderedContent', { status: 'ready' } );
		window.YoastSEO.app.registerModification( 'content', function() { return renderedContent; }, 'ToolkitRenderedContent', 10 );
		registered = true;
		if ( typeof window.YoastSEO.app.refresh === 'function' ) window.YoastSEO.app.refresh();
	}

	if ( ! config.url ) return;
	fetch( config.url, { credentials: 'same-origin' } )
		.then( function( response ) {
			if ( ! response.ok ) throw new Error( 'Rendered page request returned HTTP ' + response.status );
			return response.text();
		} )
		.then( function( html ) {
			renderedContent = prepareContent( html );
			registerAnalysisContent();
		} )
		.catch( function( error ) {
			if ( window.console && typeof window.console.warn === 'function' ) window.console.warn( 'Toolkit Yoast rendered-content analysis was unavailable.', error );
		} );
	$( window ).on( 'YoastSEO:ready', registerAnalysisContent );
} )( jQuery );
JS
	);
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
	if ( is_page( 'toolkit-courses-apply-today' ) ) {
		return home_url( '/apply/' );
	}
	if ( toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' ) ) {
		return home_url( '/speak-up/' );
	}
	if ( get_query_var( 'toolkit_connect' ) ) {
		return home_url( '/connect/' );
	}
	if ( get_query_var( 'toolkit_reception' ) ) {
		return home_url( '/reception/' );
	}
	if ( is_front_page() && eduma_child_redesign_enabled() ) {
		return home_url( '/' );
	}
	$course_url = eduma_child_course_canonical_url();
	return $course_url ? $course_url : $canonical;
} );

function toolkit_demo_production_canonical_url( $canonical = '' ) {
	if ( is_page( 'toolkit-courses-apply-today' ) ) {
		$canonical = home_url( '/apply/' );
	} elseif ( get_query_var( 'toolkit_connect' ) ) {
		$canonical = home_url( '/connect/' );
	} elseif ( get_query_var( 'toolkit_reception' ) ) {
		$canonical = home_url( '/reception/' );
	} elseif ( toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' ) ) {
		$canonical = home_url( '/speak-up/' );
	} elseif ( in_array( get_query_var( 'toolkit_showcase' ), array( 'graduation', 'testimonials', 'footprint' ), true ) ) {
		$canonical = home_url( '/' . get_query_var( 'toolkit_showcase' ) . '/' );
	} elseif ( is_front_page() ) {
		$canonical = home_url( '/' );
	} elseif ( is_singular() ) {
		$canonical = get_permalink( get_queried_object_id() );
	} elseif ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$canonical  = $posts_page ? get_permalink( $posts_page ) : home_url( '/blog/' );
	}
	return $canonical
		? preg_replace( '~^https?://demo\.toolkitafrica\.ac\.ke~i', 'https://toolkitafrica.ac.ke', $canonical )
		: '';
}

/*
 * Yoast suppresses its complete canonical presenter on noindex pages. Keep
 * demo noindex,follow while explicitly identifying the matching production
 * URL. The filter covers presenters Yoast retains; wp_head covers those it
 * omits entirely.
 */
add_filter( 'wpseo_canonical', function( $canonical ) {
	return toolkit_is_demo_environment() ? toolkit_demo_production_canonical_url( $canonical ) : $canonical;
}, 100 );

add_action( 'wp_head', function() {
	if ( ! toolkit_is_demo_environment() ) {
		return;
	}
	$canonical = toolkit_demo_production_canonical_url();
	if ( $canonical ) {
		echo '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
	}
}, 2 );

/* Yoast owns BreadcrumbList schema; suppress Eduma's duplicate footer graph. */
add_filter( 'thim/structured_data/types', function( $types ) {
	return eduma_child_has_seo_plugin() ? array( 'toolkit-yoast-schema-owner' ) : $types;
}, 100 );

add_filter( 'wpseo_opengraph_url', function( $url ) {
	if ( in_array( get_query_var( 'toolkit_showcase' ), array( 'graduation', 'testimonials', 'footprint' ), true ) ) {
		return home_url( '/' . get_query_var( 'toolkit_showcase' ) . '/' );
	}
	if ( is_page( 'toolkit-courses-apply-today' ) ) {
		return home_url( '/apply/' );
	}
	if ( toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' ) ) {
		return home_url( '/speak-up/' );
	}
	if ( get_query_var( 'toolkit_connect' ) ) {
		return home_url( '/connect/' );
	}
	if ( get_query_var( 'toolkit_reception' ) ) {
		return home_url( '/reception/' );
	}
	if ( is_front_page() && eduma_child_redesign_enabled() ) {
		return home_url( '/' );
	}
	$course_url = eduma_child_course_canonical_url();
	return $course_url ? $course_url : $url;
} );

add_filter( 'wpseo_schema_webpage', function( $data ) {
	$showcase = get_query_var( 'toolkit_showcase' );
	if ( in_array( $showcase, array( 'graduation', 'testimonials', 'footprint' ), true ) ) {
		$url                 = home_url( '/' . $showcase . '/' );
		$metadata            = eduma_child_redesigned_page_metadata();
		$data['@id']         = $url . '#webpage';
		$data['url']         = $url;
		$data['name']        = $metadata['title'];
		$data['description'] = $metadata['description'];
		$data['@type']       = 'CollectionPage';
		unset( $data['breadcrumb'] );
		return $data;
	}
	$is_speak_up = toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' );
	$is_reception = (bool) get_query_var( 'toolkit_reception' );
	if ( ! $is_speak_up && ! $is_reception ) return $data;
	$metadata            = eduma_child_redesigned_page_metadata();
	$route               = $is_speak_up ? 'speak-up' : 'reception';
	$data['@id']         = home_url( '/' . $route . '/#webpage' );
	$data['url']         = home_url( '/' . $route . '/' );
	$data['name']        = $metadata['title'];
	$data['description'] = $metadata['description'];
	$data['@type']       = 'WebPage';
	unset( $data['breadcrumb'] );
	return $data;
}, 30 );

add_filter( 'wpseo_schema_webpage', function( $data ) {
	if ( ! is_page( 'toolkit-courses-apply-today' ) ) {
		return $data;
	}
	$url                = home_url( '/apply/' );
	$data['url']        = $url;
	$data['@id']        = $url . '#webpage';
	$data['breadcrumb'] = array( '@id' => $url . '#breadcrumb' );
	return $data;
}, 40 );

add_filter( 'wpseo_schema_breadcrumb', function( $data ) {
	if ( is_page( 'toolkit-courses-apply-today' ) ) {
		$data['@id'] = home_url( '/apply/#breadcrumb' );
	}
	if ( isset( $data['itemListElement'] ) && is_array( $data['itemListElement'] ) ) {
		$legacy_blog = untrailingslashit( home_url( '/blog/' ) );
		foreach ( $data['itemListElement'] as &$item ) {
			if ( isset( $item['item'] ) && $legacy_blog === untrailingslashit( $item['item'] ) ) {
				$item['item'] = home_url( '/toolkit-blog/' );
			}
		}
		unset( $item );
	}
	return $data;
}, 40 );

add_filter( 'wpseo_sitemap_entry', function( $url, $type, $object ) {
	if ( 'post' === $type && $object instanceof WP_Post && (int) get_option( 'page_for_posts' ) === (int) $object->ID ) {
		return false;
	}
	if ( 'post' === $type && $object instanceof WP_Post && 'toolkit-courses-apply-today' === $object->post_name ) {
		$url['loc'] = home_url( '/apply/' );
	}
	if ( 'post' === $type && $object instanceof WP_Post && 'page' === $object->post_type && eduma_child_redesign_enabled() ) {
		$theme_changed = filemtime( get_stylesheet_directory() . '/functions.php' );
		$current_mod   = isset( $url['mod'] ) ? strtotime( $url['mod'] ) : 0;
		$url['mod']    = gmdate( DATE_W3C, max( (int) $theme_changed, (int) $current_mod ) );
	}
	return $url;
}, 20, 3 );

add_filter( 'wpseo_sitemap_page_content', function( $content ) {
	if ( ! eduma_child_redesign_enabled() ) {
		return $content;
	}
	$entries = array(
		'/connect/'     => 'template-parts/pages/connect.php',
		'/graduation/'  => 'template-parts/pages/graduation.php',
		'/testimonials/'=> 'template-parts/pages/testimonials.php',
		'/footprint/'   => 'template-parts/pages/footprint.php',
	);
	foreach ( $entries as $route => $relative ) {
		$content .= '<url><loc>' . esc_url( home_url( $route ) ) . '</loc><lastmod>' . esc_html( gmdate( DATE_W3C, filemtime( get_stylesheet_directory() . '/' . $relative ) ) ) . '</lastmod></url>';
	}
	return $content;
} );

/* Yoast adds the WordPress posts index separately from normal page entries. */
add_filter( 'wpseo_sitemap_post_type_first_links', function( $links, $post_type ) {
	if ( 'post' !== $post_type ) return $links;
	$legacy_blog = untrailingslashit( home_url( '/blog/' ) );
	return array_values( array_filter( $links, static function( $link ) use ( $legacy_blog ) {
		return ! isset( $link['loc'] ) || $legacy_blog !== untrailingslashit( $link['loc'] );
	} ) );
}, 20, 2 );

add_filter( 'wpseo_opengraph_image', function( $image ) {
	$metadata = eduma_child_redesigned_page_metadata();
	return $metadata ? $metadata['image'] : $image;
} );

/* The string filter only replaces an existing image. Theme-rendered pages do
 * not always have an indexable image, so seed Yoast's image container too. */
add_filter( 'wpseo_add_opengraph_images', function( $image_container ) {
	$metadata = eduma_child_redesigned_page_metadata();
	if ( $metadata && ! empty( $metadata['image'] ) && method_exists( $image_container, 'add_image_by_url' ) ) {
		$image_container->add_image_by_url( $metadata['image'] );
	}
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

add_filter( 'wpseo_schema_organization', function( $data ) {
	if ( ! eduma_child_redesign_enabled() ) {
		return $data;
	}
	$data['name'] = toolkit_canonical_brand_name();
	if ( isset( $data['logo']['caption'] ) ) {
		$data['logo']['caption'] = toolkit_canonical_brand_name();
	}
	return $data;
} );

add_filter( 'wpseo_schema_website', function( $data ) {
	if ( ! eduma_child_redesign_enabled() ) {
		return $data;
	}
	$data['name'] = toolkit_canonical_brand_name();
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

/*
 * Extend Yoast's graph instead of printing a second, conflicting graph.
 * Every relationship reuses Yoast's existing stable entity IDs.
 */
add_filter( 'wpseo_schema_graph', function( $graph ) {
	if ( ! eduma_child_redesign_enabled() || ! is_array( $graph ) ) {
		return $graph;
	}

	$home            = home_url( '/' );
	$organization_id = $home . '#organization';
	$metadata        = eduma_child_redesigned_page_metadata();
	$image_id        = $metadata ? $metadata['image'] . '#primaryimage' : '';
	$has_image       = false;
	$has_course      = false;
	$has_webpage     = false;
	foreach ( $graph as $candidate ) {
		if ( ! is_array( $candidate ) ) {
			continue;
		}
		$candidate_types = isset( $candidate['@type'] ) ? (array) $candidate['@type'] : array();
		if ( array_intersect( array( 'Organization', 'EducationalOrganization' ), $candidate_types ) && isset( $candidate['@id'] ) ) {
			$organization_id = $candidate['@id'];
			break;
		}
	}
	$organization_url = preg_replace( '/#organization$/', '', $organization_id );

	foreach ( $graph as &$node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}
		$types = isset( $node['@type'] ) ? (array) $node['@type'] : array();

		if ( array_intersect( array( 'Organization', 'EducationalOrganization' ), $types ) ) {
			$node['@type']         = array_values( array_unique( array_merge( $types, array( 'Organization', 'EducationalOrganization' ) ) ) );
			$node['name']          = toolkit_canonical_brand_name();
			$node['alternateName'] = array( 'Toolkit' );
			$node['url']           = $organization_url;
			$node['email']         = 'office@toolkitafrica.ac.ke';
			$node['telephone']     = '+254709549200';
			$node['contactPoint']  = array(
				array(
					'@type'       => 'ContactPoint',
					'contactType' => 'admissions and voice enquiries',
					'telephone'   => '+254709549200',
				),
				array(
					'@type'       => 'ContactPoint',
					'contactType' => 'WhatsApp enquiries',
					'telephone'   => '+254711802855',
					'url'         => 'https://wa.me/254711802855',
				),
			);
			$node['address']       = array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => 'Karen-Kikuyu Southern Bypass',
				'addressLocality' => 'Kikuyu',
				'addressCountry'  => 'KE',
			);
			$node['sameAs']        = array(
				'https://www.tiktok.com/@thetoolkitafrika',
				'https://www.facebook.com/toolkitafrica',
				'https://x.com/toolkitafrica',
				'https://www.instagram.com/thetoolkitafrika',
				'https://www.linkedin.com/company/the-toolkit-iskills-tti-ltd',
				'https://www.youtube.com/@toolkitafrica',
			);
			if ( isset( $node['logo']['caption'] ) ) {
				$node['logo']['caption'] = toolkit_canonical_brand_name();
			}
		}

		if ( array_intersect( array( 'WebSite', 'WebPage', 'Article', 'Course' ), $types ) ) {
			$node['inLanguage'] = 'en-KE';
		}
		if ( in_array( 'Course', $types, true ) ) {
			$has_course = true;
		}
		if ( in_array( 'WebPage', $types, true ) ) {
			$has_webpage = true;
		}
		if ( in_array( 'ImageObject', $types, true ) && $image_id && isset( $node['@id'] ) && $image_id === $node['@id'] ) {
			$has_image = true;
		}
		if ( $metadata && array_intersect( array( 'WebPage', 'Article' ), $types ) ) {
			$node['image'] = array( '@id' => $image_id );
			if ( in_array( 'WebPage', $types, true ) ) {
				$node['primaryImageOfPage'] = array( '@id' => $image_id );
			}
		}
		if ( is_singular( 'post' ) && in_array( 'Article', $types, true ) ) {
			$author = isset( $node['author'] ) ? $node['author'] : array();
			if ( empty( $author ) || ( is_array( $author ) && empty( array_filter( $author ) ) ) ) {
				$node['author'] = array( '@id' => $organization_id );
			}
		}
	}
	unset( $node );

	/* Virtual routes must not inherit the database-backed Blog breadcrumb. */
	if ( get_query_var( 'toolkit_showcase' ) || get_query_var( 'toolkit_reception' ) || ( toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' ) ) ) {
		$graph = array_values(
			array_filter(
				$graph,
				static function ( $node ) {
					$types = is_array( $node ) && isset( $node['@type'] ) ? (array) $node['@type'] : array();
					return ! in_array( 'BreadcrumbList', $types, true );
				}
			)
		);
	}

	if ( toolkit_speak_up_enabled() && get_query_var( 'toolkit_speak_up' ) && $metadata && ! $has_webpage ) {
		$graph[] = array(
			'@type'              => 'WebPage',
			'@id'                => home_url( '/speak-up/#webpage' ),
			'url'                => home_url( '/speak-up/' ),
			'name'               => $metadata['title'],
			'description'        => $metadata['description'],
			'isPartOf'           => array( '@id' => preg_replace( '/#organization$/', '#website', $organization_id ) ),
			'about'              => array( '@id' => $organization_id ),
			'primaryImageOfPage' => array( '@id' => $image_id ),
			'image'              => array( '@id' => $image_id ),
			'inLanguage'         => 'en-KE',
		);
	}
	$showcase = sanitize_key( get_query_var( 'toolkit_showcase' ) );
	if ( in_array( $showcase, array( 'graduation', 'testimonials', 'footprint' ), true ) && $metadata && ! $has_webpage ) {
		$showcase_url = home_url( '/' . $showcase . '/' );
		$graph[]      = array(
			'@type'              => 'CollectionPage',
			'@id'                => $showcase_url . '#webpage',
			'url'                => $showcase_url,
			'name'               => $metadata['title'],
			'description'        => $metadata['description'],
			'isPartOf'           => array( '@id' => preg_replace( '/#organization$/', '#website', $organization_id ) ),
			'about'              => array( '@id' => $organization_id ),
			'primaryImageOfPage' => array( '@id' => $image_id ),
			'image'              => array( '@id' => $image_id ),
			'inLanguage'         => 'en-KE',
		);
	}
	if ( $metadata ) {
		$graph = array_values(
			array_filter(
				$graph,
				static function ( $node ) use ( $image_id ) {
					if ( ! is_array( $node ) || ! isset( $node['@id'] ) || $node['@id'] === $image_id ) {
						return true;
					}
					$types = isset( $node['@type'] ) ? (array) $node['@type'] : array();
					return ! ( in_array( 'ImageObject', $types, true ) && str_ends_with( $node['@id'], '#primaryimage' ) );
				}
			)
		);
	}
	foreach ( $graph as &$node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}
		$types = isset( $node['@type'] ) ? (array) $node['@type'] : array();
		if ( is_singular( 'post' ) && in_array( 'Article', $types, true ) ) {
			$node['author'] = array( '@id' => $organization_id );
		}
	}
	unset( $node );

	if ( $metadata && ! $has_image ) {
		$graph[] = array(
			'@type'      => 'ImageObject',
			'@id'        => $image_id,
			'url'        => $metadata['image'],
			'contentUrl' => $metadata['image'],
			'caption'    => $metadata['title'],
			'inLanguage' => 'en-KE',
		);
	}

	require_once get_stylesheet_directory() . '/inc/course-catalog.php';
	$slug   = sanitize_key( get_query_var( 'toolkit_course' ) );
	$course = $slug ? eduma_child_get_course( $slug ) : eduma_child_get_legacy_course_for_page();
	if ( $course && ! $has_course ) {
		$course_url = $slug ? home_url( '/courses/' . $slug . '/' ) : $course['url'];
		$graph[]     = array(
			'@type'      => 'Course',
			'@id'        => $course_url . '#course',
			'name'       => $course['title'],
			'description' => $course['short'],
			'url'        => $course_url,
			'image'      => $course['image'],
			'provider'   => array( '@id' => $organization_id ),
			'inLanguage' => 'en-KE',
			'courseMode' => 'Onsite',
			'teaches'    => $course['outcomes'],
		);
	}

	return $graph;
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
		printf( '<link rel="alternate" type="text/plain" href="%s" title="The Toolkit for Skills and Innovation AI information">' . "\n", esc_url( home_url( '/llms.txt' ) ) );
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
		'# The Toolkit for Skills and Innovation',
		'> The Toolkit for Skills and Innovation is a Kenya-based social enterprise providing practical vocational skills, assessment support, and pathways to employment or entrepreneurship for young people and women.',
		'> The Toolkit for Skills and Innovation is also referred to as Toolkit.',
		'',
		'## Canonical resources',
		'- [Homepage](' . home_url( '/' ) . ')',
		'- [About The Toolkit for Skills and Innovation](' . home_url( '/about-toolkit-africa/' ) . ')',
		'- [Current course directory](' . home_url( '/our-ventures/' ) . ')',
		'- [MIG/MAG Welding training](' . home_url( '/our-ventures/construction-sector-skills/' ) . ')',
		'- [Admissions guidance](' . home_url( '/apply/' ) . ')',
		'- [Notice Board](' . home_url( '/notice-board/' ) . ')',
		'- [Toolkit Blog](' . home_url( '/toolkit-blog/' ) . ')',
		'- [Contact](' . home_url( '/contact/' ) . ')',
		'- [Expanded AI reference](' . home_url( '/llms-full.txt' ) . ')',
		'',
		'## Information-use rules',
		'- Treat the current course pages and admissions guidance as authoritative for public website answers.',
		'- Confirm fees, schedules, eligibility, and intake availability with Toolkit Admissions before advising payment or enrolment.',
		'- Do not infer or publish future pricing before its approved effective date.',
		'- Refer uncertain application questions to office@toolkitafrica.ac.ke, call +254 709 549 200, or WhatsApp +254 711 802 855.',
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
	if ( ! is_front_page() && ! is_singular( 'post' ) && ! is_page( array( 'our-ventures', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact', 'toolkit-blog' ) ) && ! eduma_child_get_legacy_course_for_page() && ! get_query_var( 'toolkit_course' ) ) {
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
	if ( ! is_front_page() && ! is_singular( 'post' ) && ! is_page( array( 'our-ventures', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact', 'toolkit-blog' ) ) && ! eduma_child_get_legacy_course_for_page() && ! get_query_var( 'toolkit_course' ) ) {
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

/*
 * Keep WordPress asset version query strings. They are deliberate cache keys,
 * and stripping them caused browsers to retain superseded CSS and JavaScript
 * after a release.
 */

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
