<?php

function thim_child_enqueue_styles() {
	wp_enqueue_style( 'thim-parent-style', get_template_directory_uri() . '/style.css', array(), THIM_THEME_VERSION );

	// Brand tokens — shared design tokens used site-wide
	$brand_ver = filemtime( get_stylesheet_directory() . '/brand-tokens.css' );
	wp_enqueue_style( 'eduma-child-brand-tokens', get_stylesheet_directory_uri() . '/brand-tokens.css', array(), $brand_ver );

	if ( is_front_page() ) {
		$css_ver = filemtime( get_stylesheet_directory() . '/hero-slider.css' );
		wp_enqueue_style( 'eduma-child-hero-slider', get_stylesheet_directory_uri() . '/hero-slider.css', array( 'eduma-child-brand-tokens' ), $css_ver );

		$js_ver = filemtime( get_stylesheet_directory() . '/hero-slider.js' );
		wp_enqueue_script( 'eduma-child-hero-slider', get_stylesheet_directory_uri() . '/hero-slider.js', array(), $js_ver, true );
	}
}

add_action( 'wp_enqueue_scripts', 'thim_child_enqueue_styles', 1000 );

/* === HOMEPAGE TEMPLATE: prefer the child front-page over builder page templates === */

add_filter( 'template_include', function( $template ) {
	if ( ! is_admin() && is_front_page() ) {
		$front_page_template = get_stylesheet_directory() . '/front-page.php';

		if ( file_exists( $front_page_template ) ) {
			return $front_page_template;
		}
	}

	return $template;
}, 9999 );

/* === HEADER LOGO: avoid falling back to the parent Eduma logo === */

add_action( 'after_setup_theme', function() {
	remove_action( 'thim_logo', 'thim_logo', 1 );
	add_action( 'thim_logo', 'eduma_child_toolkit_logo', 1 );
}, 20 );

function eduma_child_toolkit_logo() {
	$logo_url = content_url( 'uploads/2019/05/Toolkit-Logo.jpg' );

	printf(
		'<a href="%s" title="%s" rel="home" class="thim-logo"><img src="%s" alt="%s" width="300" height="141"></a>',
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
	return content_url( 'uploads/2019/05/Toolkit-Logo.jpg' );
}

/* === HOMEPAGE HEADER: Keep parent toolbar from creating a dark search band === */

add_filter( 'theme_mod_thim_toolbar_show', function( $show ) {
	if ( ! is_admin() && is_front_page() ) {
		return false;
	}

	return $show;
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

	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high">' . "\n",
		esc_url( $slides[0]['image'] )
	);
}, 2 );

/* === PERFORMANCE: Remove bloat === */

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );

add_filter( 'wp_resource_hints', function( $urls, $relation_type ) {
	if ( 'dns-prefetch' === $relation_type ) {
		$urls = array_filter( $urls, function( $url ) {
			return false === strpos( $url, 's.w.org' );
		});
	}
	return $urls;
}, 10, 2 );

add_filter( 'style_loader_tag', function( $html, $handle ) {
	if ( strpos( $handle, 'font-awesome' ) !== false || strpos( $handle, 'ionicons' ) !== false || strpos( $handle, 'flaticon' ) !== false || strpos( $handle, 'font-pe-icon' ) !== false ) {
		return str_replace( " media='all'", " media='print' onload=\"this.media='all'\"", $html );
	}
	return $html;
}, 10, 2 );

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

/* === SEO: Add meta description fallback === */

add_action( 'wp_head', function() {
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Frontend' ) ) {
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

/* === SECURITY: Remove Windows Live Writer manifest === */

remove_action( 'wp_head', 'wlwmanifest_link' );

/* === SECURITY: Remove RSD link === */

remove_action( 'wp_head', 'rsd_link' );
