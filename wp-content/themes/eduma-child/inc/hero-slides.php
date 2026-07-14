<?php

function eduma_child_get_hero_slides() {
	$assets = get_stylesheet_directory_uri() . '/assets/images/';
	$apply_url = home_url( '/our-ventures/toolkit-courses-apply-today/' );
	return array(
		array(
			'image'         => $assets . 'hero-slide-1.jpg',
			'eyebrow'       => 'TOOLKIT FOR SKILLS AND INNOVATION',
			'heading'       => "Empowering Africa Through<br>Skills, Innovation & Leadership",
			'description'   => 'Equipping young Africans with hands-on skills and entrepreneurial mindset to create sustainable communities and global impact.',
			'primary_cta'   => array( 'label' => 'EXPLORE COURSES', 'url' => home_url( '/our-ventures/' ) ),
			'secondary_cta' => array( 'label' => 'APPLY NOW', 'url' => $apply_url ),
		),
		array(
			'image'         => $assets . 'hero-slide-2.jpg',
			'eyebrow'       => 'HANDS-ON TRAINING',
			'heading'       => "Build Real-World Skills<br>for Tomorrow's Economy",
			'description'   => 'From welding to renewable energy, our courses blend theory with practical experience in modern workshops.',
			'primary_cta'   => array( 'label' => 'EXPLORE COURSES', 'url' => home_url( '/our-ventures/' ) ),
			'secondary_cta' => array( 'label' => 'APPLY NOW', 'url' => $apply_url ),
		),
		array(
			'image'         => $assets . 'hero-slide-3.jpg',
			'eyebrow'       => 'COMMUNITY & IMPACT',
			'heading'       => "Building Sustainable Communities<br>Across Africa Together",
			'description'   => 'Join a movement of young leaders driving change through innovation, entrepreneurship, and technical excellence.',
			'primary_cta'   => array( 'label' => 'EXPLORE COURSES', 'url' => home_url( '/our-ventures/' ) ),
			'secondary_cta' => array( 'label' => 'APPLY NOW', 'url' => $apply_url ),
		),
	);
}
