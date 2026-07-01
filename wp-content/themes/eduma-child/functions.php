<?php

function thim_child_enqueue_styles() {
	wp_enqueue_style( 'thim-parent-style', get_template_directory_uri() . '/style.css', array(), THIM_THEME_VERSION );

	if ( is_front_page() ) {
		$css_ver = filemtime( get_stylesheet_directory() . '/hero-slider.css' );
		wp_enqueue_style( 'eduma-child-hero-slider', get_stylesheet_directory_uri() . '/hero-slider.css', array(), $css_ver );

		$js_ver = filemtime( get_stylesheet_directory() . '/hero-slider.js' );
		wp_enqueue_script( 'eduma-child-hero-slider', get_stylesheet_directory_uri() . '/hero-slider.js', array(), $js_ver, true );
	}
}

add_action( 'wp_enqueue_scripts', 'thim_child_enqueue_styles', 1000 );

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
		return str_replace( "rel='stylesheet'", "rel='stylesheet' media='print' onload=\"this.media='all'\"", $html );
	}
	return $html;
}, 10, 2 );

/* === PERFORMANCE: Defer non-critical JS === */

add_filter( 'script_loader_tag', function( $tag, $handle ) {
	$defer_handles = [ 'jquery', 'thim-main', 'thim-scripts', 'thim-custom-script' ];
	if ( in_array( $handle, $defer_handles, true ) && ! is_admin() ) {
		return str_replace( ' src', ' defer src', $tag );
	}
	return $tag;
}, 10, 2 );

/* === PERFORMANCE: Dequeue parent theme bloat === */

add_action( 'wp_enqueue_scripts', function() {
	wp_dequeue_style( 'font-awesome-4-shim' );
	wp_dequeue_style( 'elementor-icons-thim-ekits-fonts' );
	wp_dequeue_script( 'thim-smooth-scroll' );
	wp_dequeue_script( 'thim-scripts-course-filter' );
	wp_dequeue_script( 'thim-scripts-course-filter-v2' );
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
