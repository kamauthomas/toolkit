<?php
/**
 * Cultural Week story manifests recovered from the supplied archive.
 */
function toolkit_cultural_week_stories() {
	return array(
		'official' => array(
			'slug'       => 'cultural-week-official-wear-day',
			'date'       => '2026-07-13 09:00:00',
			'theme'      => 'official',
			'day'        => 'Monday 13 July 2026 / Official Wear',
			'label'      => 'Cultural Week',
			'title'      => 'Official Wear Day: Dressed for Success',
			'standfirst' => 'Professionalism, confidence and workplace readiness took centre stage as students and staff stepped out in official wear.',
			'content'    => array(
				'The Toolkit community came together in official wear to celebrate professionalism, confidence and workplace readiness.',
				'Students and staff embraced the values that shape successful careers and presented themselves with purpose.',
				'The day reinforced the importance of building skills, developing character and preparing for a professional future.',
			),
		),
		'oldies' => array(
			'slug'       => 'cultural-week-golden-oldies-day',
			'date'       => '2026-07-14 09:00:00',
			'theme'      => 'oldies',
			'image_order'=> array( '02.jpg', '03.jpg', '01.jpg' ),
			'day'        => 'Tuesday 14 July 2026 / Golden Oldies',
			'label'      => 'Cultural Week',
			'title'      => 'Golden Oldies: A Trip Down Memory Lane',
			'standfirst' => 'Timeless fashion, classic style and unforgettable smiles turned the day into a warm celebration of shared history.',
			'content'    => array(
				'Students and staff took a trip down memory lane as they embraced the Golden Oldies theme with timeless fashion, classic style and unforgettable smiles.',
				'The occasion became more than a dress-up day: it celebrated history, culture and the generations that shaped the community.',
				'The experience honoured shared heritage while looking ahead to a future built one skill at a time.',
			),
		),
		'african' => array(
			'slug'       => 'cultural-week-african-wear-day',
			'date'       => '2026-07-15 09:00:00',
			'theme'      => 'african',
			'day'        => 'Wednesday 15 July 2026 / African Wear',
			'label'      => 'Cultural Week',
			'title'      => 'African Wear Day: Culture Worn with Pride',
			'standfirst' => 'A celebration of heritage, unity and diversity through the colours, forms and confidence of traditional African wear.',
			'content'    => array(
				'The Toolkit community wore its culture with pride.',
				'The day celebrated the beauty of African culture through striking traditional wear and personal expression.',
				'Students and staff embraced a shared spirit of unity, heritage and diversity.',
			),
		),
		'career' => array(
			'slug'       => 'cultural-week-career-wear-day',
			'date'       => '2026-07-16 09:00:00',
			'theme'      => 'career',
			'day'        => 'Thursday 16 July 2026 / Career Wear',
			'label'      => 'Cultural Week',
			'title'      => 'Career Wear Day: Dressing for the Future',
			'standfirst' => 'Students and staff turned career ambition into a visible statement of confidence, character and workplace readiness.',
			'content'    => array(
				'Dressed for success.',
				'The Toolkit community celebrated professionalism, confidence and workplace readiness through career wear.',
				'The day connected practical skills and personal character with the professional futures students were preparing to pursue.',
			),
		),
	);
}

function toolkit_cultural_week_story_for_slug( $slug ) {
	foreach ( toolkit_cultural_week_stories() as $key => $story ) {
		if ( $story['slug'] === $slug ) {
			$story['key']   = $key;
			$first_image    = isset( $story['image_order'][0] ) ? $story['image_order'][0] : '01.jpg';
			$story['image'] = get_stylesheet_directory_uri() . '/assets/images/blogs/cultural-week/' . $key . '/' . $first_image;
			return $story;
		}
	}
	return array();
}

/**
 * Resolve the most relevant image for a story wherever it is rendered.
 */
function toolkit_story_image_url( $post_id, $size = 'large', $fallback_index = 0 ) {
	$cultural = toolkit_cultural_week_story_for_slug( get_post_field( 'post_name', $post_id ) );
	if ( $cultural ) {
		return $cultural['image'];
	}
	$mosiria = function_exists( 'toolkit_mosiria_story_for_slug' )
		? toolkit_mosiria_story_for_slug( get_post_field( 'post_name', $post_id ) )
		: array();
	if ( $mosiria ) {
		return $mosiria['image'];
	}
	$supplied = function_exists( 'toolkit_supplied_story_for_slug' )
		? toolkit_supplied_story_for_slug( get_post_field( 'post_name', $post_id ) )
		: array();
	if ( $supplied ) {
		return $supplied['image'];
	}

	$legacy_images = array(
		'youth-international-skills-day-12th-august-2025' => 'blogs/legacy-context/youth-skills-graduates.jpg',
		'dont-miss-this-insightful-podcast-on-youth-skills-and-job-creation-in-africa-%f0%9f%a7%a0%f0%9f%92%bc' => 'team/jane-muigai-kamphuis.jpg',
		'careers-in-mig-mag-welding-insights-from-our-webinar' => 'blogs/legacy-context/welding-vr-training.jpg',
		'toolkit-shines-with-tujiajiri-mentorship-program-for-solar-energy-trainees' => 'blogs/legacy-context/solar-mentorship.jpg',
		'ilo-youth-employment-training-workshop' => 'team/hosea-mugera.jpg',
		'igniting-her-future-innovateher-roll-out-at-the-toolkit-for-skills-and-innovation-hub' => 'blogs/legacy-context/innovateher-vr.jpg',
		'toolkit-makes-history-monday-5-february-2024-statement-on-skills-in-advanced-welding-at-radisson-blu-presided-by-president-ruto-and-polish-president-duda' => 'blogs/legacy-context/welding-vr-training.jpg',
		'toolkit-makes-history-monday-5-february-2024-statement-on-skills-in-advanced-welding-at-radisson-blu-presided-by-president-ruto-and-polish-president-duda-copy' => 'blogs/legacy-context/welding-vr-training.jpg',
	);
	$slug = get_post_field( 'post_name', $post_id );
	if ( isset( $legacy_images[ $slug ] ) ) {
		return get_stylesheet_directory_uri() . '/assets/images/' . $legacy_images[ $slug ];
	}

	$thumbnail_id   = get_post_thumbnail_id( $post_id );
	$thumbnail_file = $thumbnail_id ? get_attached_file( $thumbnail_id ) : '';
	if ( $thumbnail_file && file_exists( $thumbnail_file ) ) {
		return get_the_post_thumbnail_url( $post_id, $size );
	}

	/* Imported stories often retained relevant inline media but lost featured-image files. */
	$normalize_story_image = static function( $candidate ) {
		$candidate = esc_url_raw( html_entity_decode( (string) $candidate ) );
		if ( str_starts_with( $candidate, '/' ) ) $candidate = home_url( $candidate );
		$image_host = strtolower( (string) wp_parse_url( $candidate, PHP_URL_HOST ) );
		$home_host  = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );
		$owned_hosts = array( 'toolkitafrica.ac.ke', 'www.toolkitafrica.ac.ke', 'demo.toolkitafrica.ac.ke', 'toolkitiskills.com', 'www.toolkitiskills.com' );
		if ( ! $candidate || ( $home_host !== $image_host && ! in_array( $image_host, $owned_hosts, true ) ) ) return '';
		return preg_replace( '~^https?://(?:www\.)?(?:toolkitafrica\.ac\.ke|toolkitiskills\.com)~i', home_url(), $candidate );
	};
	$content = (string) get_post_field( 'post_content', $post_id );
	if ( preg_match( '/<img\b[^>]*\bsrc\s*=\s*(["\x27])(.*?)\1/i', $content, $match ) ) {
		$content_image = $normalize_story_image( $match[2] );
		if ( $content_image ) return $content_image;
	}

	$elementor_data = json_decode( (string) get_post_meta( $post_id, '_elementor_data', true ), true );
	if ( is_array( $elementor_data ) ) {
		$queue = array( $elementor_data );
		while ( $queue ) {
			$item = array_shift( $queue );
			if ( ! is_array( $item ) ) continue;
			foreach ( $item as $key => $value ) {
				if ( 'url' === $key && is_string( $value ) && preg_match( '/\.(?:jpe?g|png|webp)(?:\?.*)?$/i', $value ) ) {
					$elementor_image = $normalize_story_image( $value );
					if ( $elementor_image ) return $elementor_image;
				}
				if ( is_array( $value ) ) $queue[] = $value;
			}
		}
	}

	$category_names = wp_get_post_terms( $post_id, 'category', array( 'fields' => 'names' ) );
	if ( is_wp_error( $category_names ) ) $category_names = array();
	$context = strtolower( get_the_title( $post_id ) . ' ' . get_post_field( 'post_excerpt', $post_id ) . ' ' . implode( ' ', $category_names ) );
	$pools   = array(
		'agriculture' => array( 'courses/agriculture.jpg', 'courses/experiences/organic-farm.jpg', 'blogs/legacy-context/blog-field-skills.jpg' ),
		'construction'=> array( 'courses/welding.jpg', 'courses/experiences/vr-welding.jpg', 'blogs/legacy-context/welding-vr-training.jpg', 'pages/welding.jpg' ),
		'green'       => array( 'courses/solar.jpg', 'courses/electrical.jpg', 'courses/experiences/solar-workshop.jpg', 'blogs/legacy-context/solar-mentorship.jpg' ),
		'digital'     => array( 'courses/digital.jpg', 'blogs/legacy-context/innovateher-vr.jpg', 'blogs/legacy-context/blog-field-skills.jpg' ),
		'inclusion'   => array( 'blogs/legacy-context/innovateher-vr.jpg', 'courses/experiences/learner-outcomes.jpg', 'blogs/legacy-context/blog-field-skills.jpg' ),
		'employability'=> array( 'courses/entrepreneurship.jpg', 'courses/experiences/learner-outcomes.jpg', 'blogs/legacy-context/blog-field-skills.jpg', 'pages/impact.jpg' ),
		'partnerships'=> array( 'pages/impact.jpg', 'pages/foundation.jpg', 'pages/contact.jpg', 'blogs/legacy-context/blog-field-skills.jpg' ),
	);
	$rules = array(
		'agriculture' => '/\b(?:agri|agriculture|farm|farming|food|organic|crop|livestock)\b/',
		'construction'=> '/\b(?:weld|welding|construction|scaffold|artisan|vr|virtual reality|tvet|vtc|mining|oil)\b/',
		'green'       => '/\b(?:solar|renewable|energy|climate|environment|green|power)\b/',
		'digital'     => '/\b(?:digital|ict|online|technology|innovation|future of work|facebook)\b/',
		'inclusion'   => '/\b(?:women|woman|girl|female|inclusion|inclusive|wia54)\b/',
		'employability'=> '/\b(?:entrepreneur|business|employment|employability|job|career|skills|training)\b/',
		'partnerships'=> '/\b(?:partnership|visit|meeting|forum|workshop|conference|summit|delegation|award)\b/',
	);
	$selected_pool = $pools['partnerships'];
	foreach ( $rules as $pool_key => $pattern ) {
		if ( preg_match( $pattern, $context ) ) { $selected_pool = $pools[ $pool_key ]; break; }
	}
	$stable_index = absint( crc32( (string) $slug ) ) + absint( $fallback_index );
	return get_stylesheet_directory_uri() . '/assets/images/' . $selected_pool[ $stable_index % count( $selected_pool ) ];
}

function toolkit_cultural_week_preview() {
	$stories = toolkit_cultural_week_stories();
	$host    = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
	$key     = '';

	if ( in_array( $host, array( '127.0.0.1:8001', 'localhost:8001' ), true ) && isset( $_GET['story-preview'] ) ) {
		$key = sanitize_key( wp_unslash( $_GET['story-preview'] ) );
	} elseif ( is_singular( 'post' ) ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		foreach ( $stories as $story_key => $story ) {
			if ( $story['slug'] === $slug ) {
				$key = $story_key;
				break;
			}
		}
	}

	if ( ! $key || ! isset( $stories[ $key ] ) ) {
		return array();
	}
	$base = get_stylesheet_directory_uri() . '/assets/images/blogs/cultural-week/' . $key . '/';
	$order = isset( $stories[ $key ]['image_order'] ) ? $stories[ $key ]['image_order'] : array( '01.jpg', '02.jpg', '03.jpg' );
	$stories[ $key ]['images'] = array_map(
		static function ( $filename ) use ( $base ) {
			return $base . $filename;
		},
		$order
	);
	return $stories[ $key ];
}

/**
 * Publish the supplied stories once per environment. Existing matching slugs
 * are preserved, making the release safe to repeat.
 */
function toolkit_publish_cultural_week_stories() {
	$content_version = '2026.07.31.1';
	if ( $content_version === get_option( 'toolkit_cultural_week_2026_published' ) ) {
		return;
	}

	$category = term_exists( 'Cultural Week', 'category' );
	if ( ! $category ) {
		$category = wp_insert_term( 'Cultural Week', 'category', array( 'slug' => 'cultural-week' ) );
	}
	$category_id = is_array( $category ) ? absint( $category['term_id'] ) : absint( $category );

	foreach ( toolkit_cultural_week_stories() as $story ) {
		$existing = get_page_by_path( $story['slug'], OBJECT, 'post' );
		$content = '';
		foreach ( $story['content'] as $paragraph ) {
			$content .= '<p>' . esc_html( $paragraph ) . '</p>';
		}
		wp_insert_post(
			array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'ID'            => $existing ? $existing->ID : 0,
				'post_name'     => $story['slug'],
				'post_title'    => $story['title'],
				'post_excerpt'  => $story['standfirst'],
				'post_content'  => $content,
				'post_date'     => $story['date'],
				'post_category' => $category_id ? array( $category_id ) : array(),
			)
		);
	}

	update_option( 'toolkit_cultural_week_2026_published', $content_version, false );
}
add_action( 'init', 'toolkit_publish_cultural_week_stories', 30 );
