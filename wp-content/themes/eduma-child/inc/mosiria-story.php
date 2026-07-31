<?php
/**
 * Editorial package for the official visit by Geofrey Omatoke Mosiria.
 */

function toolkit_mosiria_story() {
	$base = get_stylesheet_directory_uri() . '/assets/images/blogs/mosiria/';
	return array(
		'slug'       => 'geofrey-mosiria-visits-the-toolkit',
		'date'       => '2026-07-17 09:00:00',
		'theme'      => 'mosiria',
		'day'        => 'Friday 17 July 2026 / Official visit',
		'label'      => 'Leadership Visit',
		'title'      => 'Geofrey Mosiria Visits The Toolkit',
		'standfirst' => 'The Nairobi City County Chief Officer for Citizen Engagement and Customer Service toured Toolkit training facilities and spoke with learners about practical skills and the future of work.',
		'content'    => array(
			'The Toolkit for Skills and Innovation welcomed Geofrey Omatoke Mosiria, Nairobi City County Chief Officer for Citizen Engagement and Customer Service, for an official visit on 17 July 2026.',
			'Principal Sylvester Theophile and members of the management, admissions and training teams introduced Toolkit’s approach to accessible, industry-relevant skills development.',
			'The visit included the Virtual Reality Welding Lab, welding workshop, solar technology department and organic farming demonstration facilities. The team also presented Toolkit’s relationship with the International Institute of Welding and its role within Kenya’s welding education network.',
			'Speaking with learners, Mosiria encouraged young people to pursue practical skills that respond to changing industry needs and create pathways into employment and entrepreneurship.',
			'The engagement highlighted the contribution of quality technical and vocational education and training to economic opportunity, public service and community transformation.',
		),
		'images'     => array(
			$base . '749270159_122198710652475633_7338792061693937381_n.jpg',
			$base . '748573523_122198710352475633_1254292409236897295_n.jpg',
			$base . '749270185_122198710346475633_1554920615259977771_n.jpg',
			$base . '749286210_122198709962475633_1058401222683598158_n.jpg',
			$base . '748481183_122198709170475633_5103971124779668125_n.jpg',
		),
	);
}

function toolkit_mosiria_story_for_slug( $slug ) {
	$story = toolkit_mosiria_story();
	if ( $story['slug'] !== $slug ) {
		return array();
	}
	$story['image'] = $story['images'][0];
	return $story;
}

function toolkit_mosiria_story_preview() {
	$story = toolkit_mosiria_story();
	$host  = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
	$local = in_array( $host, array( '127.0.0.1:8001', 'localhost:8001' ), true )
		&& isset( $_GET['story-preview'] )
		&& 'mosiria' === sanitize_key( wp_unslash( $_GET['story-preview'] ) );
	$live  = is_singular( 'post' ) && $story['slug'] === get_post_field( 'post_name', get_queried_object_id() );
	return $local || $live ? $story : array();
}

function toolkit_publish_mosiria_story() {
	$content_version = '2026.07.31.2';
	if ( $content_version === get_option( 'toolkit_mosiria_story_published' ) ) {
		return;
	}
	$story    = toolkit_mosiria_story();
	$existing = get_page_by_path( $story['slug'], OBJECT, 'post' );
	$content  = '';
	foreach ( $story['content'] as $paragraph ) {
		$content .= '<p>' . esc_html( $paragraph ) . '</p>';
	}
	$post_id = wp_insert_post(
		array(
			'ID'           => $existing ? $existing->ID : 0,
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_name'    => $story['slug'],
			'post_title'   => $story['title'],
			'post_excerpt' => $story['standfirst'],
			'post_content' => $content,
			'post_date'    => $story['date'],
		),
		true
	);
	if ( ! is_wp_error( $post_id ) ) {
		update_option( 'toolkit_mosiria_story_published', $content_version, false );
	}
}
add_action( 'init', 'toolkit_publish_mosiria_story', 31 );
