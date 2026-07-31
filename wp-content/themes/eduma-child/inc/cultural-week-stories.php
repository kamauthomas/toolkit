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
			$story['image'] = get_stylesheet_directory_uri() . '/assets/images/blogs/cultural-week/' . $key . '/01.jpg';
			return $story;
		}
	}
	return array();
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
	$stories[ $key ]['images'] = array( $base . '01.jpg', $base . '02.jpg', $base . '03.jpg' );
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
