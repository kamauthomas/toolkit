<?php
/**
 * Verified editorial packages supplied for the Toolkit blog.
 */

function toolkit_supplied_stories() {
	$alumni_images = get_stylesheet_directory_uri() . '/assets/images/blogs/alumni-mentorship/';
	return array(
		'alumni-mentorship' => array(
			'slug'       => 'alumni-mentorship-success-stories-2026',
			'date'       => '2026-06-27 09:00:00',
			'theme'      => 'alumni',
			'day'        => 'Saturday 27 June 2026 / Alumni mentorship',
			'label'      => 'Alumni Voices',
			'title'      => 'Alumni Returned to Inspire the Next Generation',
			'standfirst' => 'Former Toolkit students returned to share honest career lessons, challenges and opportunities with current trainees.',
			'content'    => array(
				'There was no greater testimony than seeing Toolkit alumni return, not simply to visit, but to inspire the next generation.',
				'The Alumni Mentorship and Success Stories event, held on 27 June 2026, reflected how every journey begins with a decision to learn, grow and persevere.',
				'Former students spoke to current trainees about their career journeys, the challenges they overcame, the opportunities they embraced and how their practical training continued to shape their progress.',
				'Current trainees listened, asked questions and learned from real experiences that made future career possibilities feel tangible.',
			),
			'images'     => array(
				$alumni_images . '735831791_1029417302786107_5332289813976630089_n.jpg',
				$alumni_images . '735799434_1029416776119493_4509382025383934307_n.jpg',
				$alumni_images . '735945567_1029416636119507_2625003696084195407_n.jpg',
			),
		),
	);
}

function toolkit_supplied_story_for_slug( $slug ) {
	foreach ( toolkit_supplied_stories() as $story ) {
		if ( $story['slug'] === $slug ) {
			$story['image'] = $story['images'][0];
			return $story;
		}
	}
	return array();
}

function toolkit_supplied_story_preview() {
	$slug = is_singular( 'post' ) ? get_post_field( 'post_name', get_queried_object_id() ) : '';
	$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
	if ( in_array( $host, array( '127.0.0.1:8001', 'localhost:8001' ), true ) && isset( $_GET['story-preview'] ) ) {
		$key     = sanitize_key( wp_unslash( $_GET['story-preview'] ) );
		$stories = toolkit_supplied_stories();
		return isset( $stories[ $key ] ) ? $stories[ $key ] : array();
	}
	return $slug ? toolkit_supplied_story_for_slug( $slug ) : array();
}

function toolkit_publish_supplied_stories() {
	$content_version = '2026.07.31.1';
	if ( $content_version === get_option( 'toolkit_supplied_stories_published' ) ) {
		return;
	}
	$category = term_exists( 'Alumni Stories', 'category' );
	if ( ! $category ) {
		$category = wp_insert_term( 'Alumni Stories', 'category', array( 'slug' => 'alumni-stories' ) );
	}
	$category_id = is_array( $category ) ? absint( $category['term_id'] ) : absint( $category );
	foreach ( toolkit_supplied_stories() as $story ) {
		$content = '';
		foreach ( $story['content'] as $paragraph ) {
			$content .= '<p>' . esc_html( $paragraph ) . '</p>';
		}
		$existing = get_page_by_path( $story['slug'], OBJECT, 'post' );
		$result   = wp_insert_post(
			array(
				'ID'            => $existing ? $existing->ID : 0,
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'post_name'     => $story['slug'],
				'post_title'    => $story['title'],
				'post_excerpt'  => $story['standfirst'],
				'post_content'  => $content,
				'post_date'     => $story['date'],
				'post_category' => $category_id ? array( $category_id ) : array(),
			),
			true
		);
		if ( is_wp_error( $result ) ) {
			return;
		}
	}
	update_option( 'toolkit_supplied_stories_published', $content_version, false );
}
add_action( 'init', 'toolkit_publish_supplied_stories', 32 );
