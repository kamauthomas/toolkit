<?php

function eduma_child_get_hero_slides() {
	$uploads = wp_get_upload_dir()['baseurl'];
	return array(
		array(
			'image'         => $uploads . '/2025/05/TOOLKIT-scaled.jpg',
			'eyebrow'       => 'TOOLKIT FOR SKILLS AND INNOVATION',
			'heading'       => "Empowering Africa Through<br>Skills, Innovation & Leadership",
			'description'   => 'Equipping young Africans with hands-on skills and entrepreneurial mindset to create sustainable communities and global impact.',
			'primary_cta'   => array( 'label' => 'EXPLORE COURSES', 'url' => home_url( '/our-ventures/' ) ),
			'secondary_cta' => array( 'label' => 'OUR IMPACT', 'url' => home_url( '/the-toolkit-foundation-copy/' ) ),
		),
		array(
			'image'         => $uploads . '/2025/05/DAV8986-scaled.jpg',
			'eyebrow'       => 'HANDS-ON TRAINING',
			'heading'       => "Build Real-World Skills<br>for Tomorrow's Economy",
			'description'   => 'From welding to renewable energy, our courses blend theory with practical experience in modern workshops.',
			'primary_cta'   => array( 'label' => 'EXPLORE COURSES', 'url' => home_url( '/our-ventures/' ) ),
			'secondary_cta' => null,
		),
		array(
			'image'         => $uploads . '/2025/05/DAV4119-scaled.jpg',
			'eyebrow'       => 'COMMUNITY & IMPACT',
			'heading'       => "Building Sustainable Communities<br>Across Africa Together",
			'description'   => 'Join a movement of young leaders driving change through innovation, entrepreneurship, and technical excellence.',
			'primary_cta'   => array( 'label' => 'EXPLORE COURSES', 'url' => home_url( '/our-ventures/' ) ),
			'secondary_cta' => null,
		),
	);
}
