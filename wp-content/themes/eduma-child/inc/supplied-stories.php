<?php
/**
 * Verified editorial packages supplied for the Toolkit blog.
 */

function toolkit_supplied_stories() {
	$alumni_images = get_stylesheet_directory_uri() . '/assets/images/blogs/alumni-mentorship/';
	$africa_forward_images = get_stylesheet_directory_uri() . '/assets/images/blogs/africa-forward/';
	$icm_images = get_stylesheet_directory_uri() . '/assets/images/blogs/icm-visit/';
	return array(
		'icm-visit' => array(
			'slug'          => 'icm-tvet-uk-visits-the-toolkit',
			'date'          => '2026-08-03 09:00:00',
			'theme'         => 'icm',
			'day'           => '3 August 2026 / ICM-TVET UK',
			'label'         => 'Partnership Visit',
			'category_name' => 'Partnerships',
			'category_slug' => 'partnerships',
			'title'         => 'ICM-TVET UK Delegation Visited The Toolkit',
			'standfirst'    => 'The visit explored potential collaboration and internationally recognised programmes while introducing the ICM team to Toolkit’s practical, industry-led training model.',
			'section_title' => 'Inside the ICM-TVET UK visit',
			'focus_keyphrase' => 'ICM-TVET UK visit',
			'seo_description' => 'The ICM-TVET UK visit explored collaboration, recognised programmes and Toolkit’s practical skills-training facilities in Kikuyu, Kenya.',
			'content'       => array(
				'The ICM-TVET UK visit took place on 3 August 2026, when The Toolkit for Skills and Innovation welcomed ICM Africa Regional Director Kevin Kanisio Osundwa and Bernice from the ICM team for a courtesy and partnership engagement.',
				'The delegation toured Toolkit’s training facilities in Kikuyu and received an introduction to the institution’s practical, industry-led approach to skills development. The engagement placed the learning environment, available programmes and learner experience at the centre of the conversation.',
				'During the facility tour, the visitors saw spaces used for practical training as well as Toolkit’s organic farm. These stops helped the team discuss how applied learning can connect technical knowledge with the settings in which learners practise and build confidence.',
				'The ICM-TVET UK visit also created room to explore potential collaboration and the introduction of internationally recognised programmes. The discussion was exploratory: any future programme, intake or certification arrangement would require formal confirmation before being presented to prospective learners.',
				'For Toolkit, partnership conversations are most useful when they support accurate course guidance, credible learning pathways and stronger progression opportunities. The visit therefore focused on understanding each institution’s role and where further discussion could add value for learners.',
				'Toolkit will publish confirmed programme information through its official course, notice and admissions channels. Prospective learners should use those current sources, or contact admissions directly, rather than treating an exploratory visit as an announcement of a new qualification.',
			),
			'images'        => array(
				$icm_images . '01-main.jpeg',
				$icm_images . '02.jpeg',
				$icm_images . '03.jpeg',
				$icm_images . '04.jpeg',
				$icm_images . '05.jpeg',
				$icm_images . '06.jpeg',
				$icm_images . '07.jpeg',
				$icm_images . '08.jpeg',
				$icm_images . '09.jpeg',
				$icm_images . '10.jpeg',
			),
		),
		'africa-forward' => array(
			'slug'          => 'africa-forward-youth-innovation-day-career-fair-2026',
			'date'          => '2026-07-15 18:00:00',
			'theme'         => 'africa-forward',
			'day'           => '14–15 July 2026 / University of Nairobi',
			'label'         => 'Youth & Innovation',
			'category_name' => 'Industry Engagement',
			'category_slug' => 'industry-engagement',
			'title'         => 'Toolkit at Africa Forward Youth and Innovation Day',
			'standfirst'    => 'Toolkit demonstrated practical welding, solar and language pathways while connecting with students, partners and stakeholders at the University of Nairobi career fair.',
			'focus_keyphrase' => 'Africa Forward Youth and Innovation Day',
			'seo_description' => 'See how Toolkit presented welding, solar and language training at the Africa Forward Youth and Innovation Day career fair in Nairobi.',
			'content'       => array(
				'Toolkit participated in the Africa Forward Youth and Innovation Day Career Fair, held at the University of Nairobi on 14 and 15 July 2026.',
				'Across the two-day event, the Welding, Solar Installation and Language teams demonstrated hands-on training and the career pathways available through practical skills development.',
				'Toolkit representatives spoke with students, partners and stakeholders about industry-focused programmes and the role of technical and vocational education in preparing young people for work.',
				'Distinguished guests visited the exhibition area, while representatives from institutions including Alliance Française, TotalEnergies, the Royal Danish Embassy and Equity Group Foundation contributed to the wider exchange on youth opportunity and skills development.',
				'Students from the University of Nairobi, Strathmore University, PC Kinyanjui Technical Training Institute and other institutions explored training options, future enrolment and potential partnerships with the Toolkit team.',
			),
			'images'        => array(
				$africa_forward_images . '747744050_122198395526475633_8065643064745133731_n.jpg',
				$africa_forward_images . '749232211_122198395256475633_8824732662711525514_n.jpg',
				$africa_forward_images . '748573441_122198395400475633_1117624892553734563_n.jpg',
			),
		),
		'alumni-mentorship' => array(
			'slug'          => 'alumni-mentorship-success-stories-2026',
			'date'          => '2026-06-27 09:00:00',
			'theme'         => 'alumni',
			'day'           => 'Saturday 27 June 2026 / Alumni mentorship',
			'label'         => 'Alumni Voices',
			'category_name' => 'Alumni Stories',
			'category_slug' => 'alumni-stories',
			'title'         => 'Alumni Returned to Inspire the Next Generation',
			'standfirst'    => 'Former Toolkit students returned to share honest career lessons, challenges and opportunities with current trainees.',
			'focus_keyphrase' => 'Toolkit alumni mentorship',
			'seo_description' => 'Toolkit alumni returned to share practical career lessons, challenges and opportunities with current trainees during a mentorship event in Kikuyu.',
			'content'       => array(
				'There was no greater testimony than seeing Toolkit alumni return, not simply to visit, but to inspire the next generation.',
				'The Alumni Mentorship and Success Stories event, held on 27 June 2026, reflected how every journey begins with a decision to learn, grow and persevere.',
				'Former students spoke to current trainees about their career journeys, the challenges they overcame, the opportunities they embraced and how their practical training continued to shape their progress.',
				'Current trainees listened, asked questions and learned from real experiences that made future career possibilities feel tangible.',
			),
			'images'        => array(
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
	$content_version = '2026.08.13.3';
	if ( $content_version === get_option( 'toolkit_supplied_stories_published' ) ) {
		return;
	}
	foreach ( toolkit_supplied_stories() as $story ) {
		$category = term_exists( $story['category_slug'], 'category' );
		if ( ! $category ) {
			$category = wp_insert_term( $story['category_name'], 'category', array( 'slug' => $story['category_slug'] ) );
		}
		$category_id = is_array( $category ) ? absint( $category['term_id'] ) : absint( $category );
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
		if ( ! empty( $story['focus_keyphrase'] ) ) {
			update_post_meta( $result, '_yoast_wpseo_focuskw', sanitize_text_field( $story['focus_keyphrase'] ) );
		}
		if ( ! empty( $story['seo_description'] ) ) {
			update_post_meta( $result, '_yoast_wpseo_metadesc', sanitize_text_field( $story['seo_description'] ) );
		}
	}
	update_option( 'toolkit_supplied_stories_published', $content_version, false );
}
add_action( 'init', 'toolkit_publish_supplied_stories', 32 );
