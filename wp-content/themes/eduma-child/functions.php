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

	if ( is_page( array( 'our-ventures', 'construction-sector-skills', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact' ) ) || get_query_var( 'toolkit_course' ) ) {
		$page_css_ver = filemtime( get_stylesheet_directory() . '/page-redesign.css' );
		$page_js_ver  = filemtime( get_stylesheet_directory() . '/page-redesign.js' );
		wp_enqueue_style( 'eduma-child-page-redesign', get_stylesheet_directory_uri() . '/page-redesign.css', array( 'eduma-child-brand-tokens' ), $page_css_ver );
		wp_enqueue_script( 'eduma-child-page-redesign', get_stylesheet_directory_uri() . '/page-redesign.js', array(), $page_js_ver, true );
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
	if ( ! is_admin() && is_front_page() ) {
		$front_page_template = get_stylesheet_directory() . '/front-page.php';

		if ( file_exists( $front_page_template ) ) {
			return $front_page_template;
		}
	}

	return $template;
}, 9999 );

add_filter( 'template_include', function( $template ) {
	if ( is_admin() ) {
		return $template;
	}

	if ( get_query_var( 'toolkit_course' ) ) {
		return get_stylesheet_directory() . '/template-parts/pages/course-detail.php';
	}

	$page_templates = array(
		'about-toolkit-africa'          => 'template-parts/pages/institutional.php',
		'the-toolkit-foundation-copy'  => 'template-parts/pages/institutional.php',
		'the-toolkit-foundation'       => 'template-parts/pages/institutional.php',
		'contact'                      => 'template-parts/pages/contact.php',
		'our-ventures'                 => 'template-parts/pages/courses.php',
		'construction-sector-skills' => 'template-parts/pages/welding.php',
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
	add_rewrite_rule( '^courses/([^/]+)/?$', 'index.php?toolkit_course=$matches[1]', 'top' );
	if ( get_option( 'eduma_child_course_routes_version' ) !== '2026-1' ) {
		flush_rewrite_rules( false );
		update_option( 'eduma_child_course_routes_version', '2026-1', false );
	}
} );

add_filter( 'query_vars', function( $vars ) {
	$vars[] = 'toolkit_course';
	return $vars;
} );

/* === SEO: curated metadata for child-theme page rebuilds === */

function eduma_child_redesigned_page_metadata() {
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
	if ( is_page( 'our-ventures' ) ) {
		return array(
			'title'       => 'Our Courses | Toolkit Africa',
			'description' => 'Explore practical Toolkit Africa courses in welding, renewable energy, digital skills, agriculture, and enterprise-focused learning.',
			'image'       => wp_get_upload_dir()['baseurl'] . '/2025/05/TOOLKIT-scaled.jpg',
		);
	}
	if ( is_page( 'about-toolkit-africa' ) ) {
		return array(
			'title'       => 'About Toolkit Africa | Skills, Innovation and Opportunity',
			'description' => 'Learn how Toolkit Africa connects practical skills, innovation, industry exposure, employment, and entrepreneurship pathways for young people and women.',
			'image'       => wp_get_upload_dir()['baseurl'] . '/2025/05/TOOLKIT1-1536x863.jpg',
		);
	}
	if ( is_page( 'the-toolkit-foundation-copy' ) ) {
		return array(
			'title'       => 'Impact and Insights | Toolkit Africa',
			'description' => 'Explore how Toolkit Africa connects practical learning with employability, industrial exposure, livelihoods, and inclusive opportunity.',
			'image'       => wp_get_upload_dir()['baseurl'] . '/2025/05/DAV4119-1536x1025.jpg',
		);
	}
	if ( is_page( 'the-toolkit-foundation' ) ) {
		return array(
			'title'       => 'The Toolkit Foundation | Inclusive Skills Development',
			'description' => 'Discover Toolkit Foundation programmes supporting inclusive technical, digital, green, and enterprise skills for underserved communities.',
			'image'       => wp_get_upload_dir()['baseurl'] . '/2025/05/solar-2-website.jpg',
		);
	}
	if ( is_page( 'contact' ) ) {
		return array(
			'title'       => 'Contact Toolkit Africa | Courses, Admissions and Partnerships',
			'description' => 'Contact Toolkit Africa about courses, admissions, partnerships, and visiting the Toolkit Skills and Innovation Hub in Kikuyu, Kenya.',
			'image'       => wp_get_upload_dir()['baseurl'] . '/2025/05/DAV8986-scaled-e1747646195282-1536x554.jpg',
		);
	}
	if ( is_page( 'construction-sector-skills' ) ) {
		return array(
			'title'       => 'Welding and Fabrication Training | Toolkit Africa',
			'description' => 'Develop practical welding and fabrication skills with hands-on training, VR-enabled learning, and career-focused support at Toolkit Africa.',
			'image'       => wp_get_upload_dir()['baseurl'] . '/2025/05/WELDING-6.jpg',
		);
	}

	if ( is_page( 'notice-board' ) ) {
		return array(
			'title'       => 'Notice Board | Toolkit Africa',
			'description' => 'Find current Toolkit Africa announcements, admissions guidance, opportunities, events, and important notices in one place.',
			'image'       => wp_get_upload_dir()['baseurl'] . '/2025/05/DAV8986-scaled.jpg',
		);
	}

	if ( is_page( 'toolkit-courses-apply-today' ) ) {
		return array(
			'title'       => 'Apply for a Course | Toolkit Africa',
			'description' => 'Prepare your Toolkit Africa course application, review the admission steps, and continue securely to the online application portal.',
			'image'       => wp_get_upload_dir()['baseurl'] . '/2025/05/TOOLKIT-scaled.jpg',
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
	return $slug ? home_url( '/courses/' . $slug . '/' ) : false;
}

add_filter( 'wpseo_canonical', function( $canonical ) {
	$course_url = eduma_child_course_canonical_url();
	return $course_url ? $course_url : $canonical;
} );

add_filter( 'wpseo_opengraph_url', function( $url ) {
	$course_url = eduma_child_course_canonical_url();
	return $course_url ? $course_url : $url;
} );

add_filter( 'wpseo_opengraph_image', function( $image ) {
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

/* Rebuilt pages do not render Elementor or Contact Form 7 content. */
add_action( 'wp_enqueue_scripts', function() {
	if ( ! is_page( array( 'our-ventures', 'construction-sector-skills', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact' ) ) && ! get_query_var( 'toolkit_course' ) ) {
		return;
	}

	$styles = array(
		'sb-elementor-shared-style',
		'cff',
		'sb-font-awesome',
		'sina-header-footer',
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
	if ( ! is_page( array( 'our-ventures', 'construction-sector-skills', 'notice-board', 'toolkit-courses-apply-today', 'about-toolkit-africa', 'the-toolkit-foundation-copy', 'the-toolkit-foundation', 'contact' ) ) && ! get_query_var( 'toolkit_course' ) ) {
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

/* === SECURITY: Remove Windows Live Writer manifest === */

remove_action( 'wp_head', 'wlwmanifest_link' );

/* === SECURITY: Remove RSD link === */

remove_action( 'wp_head', 'rsd_link' );
