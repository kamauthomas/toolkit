<?php

function eduma_child_2026_course_catalog() {
	$assets = get_stylesheet_directory_uri() . '/assets/images/courses/';

	return array(
		'electrical-installation' => array(
			'title' => 'Electrical Installation', 'short' => 'Build practical installation, wiring, safety, and testing skills for the electrical trade.',
			'image' => $assets . 'electrical.jpg', 'icon' => 'fa-bolt', 'qualification' => 'Electrical Installation Skills Certificate', 'examining' => 'NITA Trade Test',
			'duration' => '6 months: 3 months at the Hub and 3 months in industry', 'entry' => 'KCPE or KCSE certificate', 'fees' => 'KES 150,000 standard; KES 120,962 subsidised', 'intakes' => 'January, April, July and October',
			'outcomes' => array( 'Electrical safety and tools', 'Domestic and commercial wiring', 'Installation testing', 'Industry attachment' ),
		),
		'solar-technician' => array(
			'title' => 'Solar Technician Pathway', 'short' => 'Train for solar PV installation and electrical work, with a focused upskilling option for experienced learners.',
			'image' => $assets . 'solar.jpg', 'icon' => 'fa-solar-panel', 'qualification' => 'Solar PV Installer or Solar Electrician Skills Certificate', 'examining' => 'NITA Solar PV Assessment, Wireman Grade III or Solar T1',
			'duration' => '6 months: 3 months at the Hub and 3 months in industry; upskilling course: 10 days', 'entry' => 'KCPE or KCSE certificate; prior electrical experience for upskilling', 'fees' => 'KES 150,000 standard; KES 120,962 subsidised; upskilling KES 30,684', 'intakes' => 'January, April, July and October; upskilling monthly',
			'outcomes' => array( 'Solar PV system fundamentals', 'Safe installation practice', 'Testing and maintenance', 'Industry attachment' ),
		),
		'advanced-welding-vr' => array(
			'title' => 'Advanced Welding with VR', 'short' => 'Develop advanced welding skills through workshop practice, simulation, and industry placement.',
			'image' => $assets . 'welding.jpg', 'icon' => 'fa-fire', 'qualification' => 'Advanced Welding Skills Certificate with VR', 'examining' => 'KEBS, NITA or IIW',
			'duration' => '6 months: 3 months at the Hub and 3 months in industry', 'entry' => 'KCPE or KCSE certificate', 'fees' => 'KES 176,276 standard; KES 120,962 subsidised', 'intakes' => 'January, April, July and October',
			'outcomes' => array( 'Workshop safety', 'Advanced welding techniques', 'VR-supported practice', 'Quality control and industry attachment' ),
		),
		'smart-agriculture' => array(
			'title' => 'Smart Agriculture', 'short' => 'Learn climate-smart production methods and practical enterprise skills for resilient agriculture.',
			'image' => $assets . 'agriculture.jpg', 'icon' => 'fa-seedling', 'qualification' => 'Climate Smart Agriculture Certificate', 'examining' => 'NITA',
			'duration' => '6 months; specialist short courses run for 1 to 2 days', 'entry' => 'KCPE or KCSE certificate; short courses are open entry', 'fees' => 'KES 73,586 including examination; short courses from KES 4,000', 'intakes' => 'January, April, July and October; short courses monthly',
			'outcomes' => array( 'Climate-smart production', 'Resource-efficient farming', 'Farm enterprise skills', 'Applied field learning' ),
		),
		'entrepreneurship' => array(
			'title' => 'Entrepreneurship Suite', 'short' => 'Choose focused business courses designed for enterprise development, planning, and growth.',
			'image' => $assets . 'entrepreneurship.jpg', 'icon' => 'fa-chart-line', 'qualification' => 'Toolkit or CDACC certificate, depending on course', 'examining' => 'Toolkit or CDACC, depending on course',
			'duration' => '2 weeks to 3 months', 'entry' => 'Open entry', 'fees' => 'KES 8,000 to KES 35,000, depending on course', 'intakes' => 'Monthly',
			'outcomes' => array( 'Business opportunity assessment', 'Planning and financial basics', 'Market development', 'Enterprise growth skills' ),
		),
		'digital-skills-technology' => array(
			'title' => 'Digital Skills', 'short' => 'Progress from digital literacy to specialist pathways including cyber security and AI automation.',
			'image' => $assets . 'digital.jpg', 'icon' => 'fa-laptop-code', 'qualification' => 'CDACC Level 3 or Toolkit certificate, depending on course', 'examining' => 'CDACC or Toolkit, depending on course',
			'duration' => '2 weeks to 6 months', 'entry' => 'Open entry for most courses', 'fees' => 'KES 18,000 to KES 65,000, depending on course', 'intakes' => 'Monthly',
			'outcomes' => array( 'Digital literacy', 'Productivity and online work', 'Cyber security foundations', 'AI and automation foundations' ),
		),
		'languages' => array(
			'title' => 'French & German Languages', 'short' => 'Build recognised language proficiency for study, work, mobility, and international opportunities.',
			'image' => $assets . 'languages.jpg', 'icon' => 'fa-language', 'qualification' => 'French or German language proficiency by level', 'examining' => 'Alliance Francaise for French; Goethe-Institut for German',
			'duration' => 'A1: 3 months; A2: 3 months; B1: 4 months', 'entry' => 'Open entry', 'fees' => 'French: KES 35,000 to KES 45,000 per level; German: KES 40,000 to KES 50,000 per level', 'intakes' => 'Monthly; online and physical delivery',
			'outcomes' => array( 'Everyday communication', 'Progressive A1 to B1 proficiency', 'Exam preparation', 'Work and mobility readiness' ),
		),
	);
}

function eduma_child_legacy_course_catalog() {
	$assets = get_stylesheet_directory_uri() . '/assets/images/courses/';
	return array(
		'welding-and-fabrication' => array( 'title' => 'Welding and Fabrication', 'short' => 'Develop practical workshop skills with industry-aware training and modern learning tools.', 'image' => $assets . 'welding.jpg', 'icon' => 'fa-fire', 'url' => home_url( '/our-ventures/construction-sector-skills/' ), 'duration' => 'Contact admissions for the current schedule', 'intakes' => 'Confirmed by admissions' ),
		'renewable-energy' => array( 'title' => 'Renewable Energy', 'short' => 'Build technical foundations for solar and emerging green-skills opportunities.', 'image' => $assets . 'solar.jpg', 'icon' => 'fa-solar-panel', 'url' => home_url( '/our-ventures/renewable-energy/' ), 'duration' => 'Contact admissions for the current schedule', 'intakes' => 'Confirmed by admissions' ),
		'organic-farming-skills' => array( 'title' => 'Organic Farming Skills', 'short' => 'Learn practical and sustainable approaches to agriculture and enterprise.', 'image' => $assets . 'agriculture.jpg', 'icon' => 'fa-seedling', 'url' => home_url( '/our-ventures/organic-farming-skills/' ), 'duration' => 'Contact admissions for the current schedule', 'intakes' => 'Confirmed by admissions' ),
		'digital-skills-online-jobs' => array( 'title' => 'Digital Skills', 'short' => 'Strengthen practical digital capability for changing work and entrepreneurship.', 'image' => $assets . 'digital.jpg', 'icon' => 'fa-laptop-code', 'url' => home_url( '/our-ventures/access-online-jobs/' ), 'duration' => 'Contact admissions for the current schedule', 'intakes' => 'Confirmed by admissions' ),
		'recognition-prior-learning' => array( 'title' => 'Recognition of Prior Learning', 'short' => 'Turn existing skills and experience into recognised progression pathways.', 'image' => $assets . 'entrepreneurship.jpg', 'icon' => 'fa-award', 'url' => home_url( '/our-ventures/construction-sector-skills/recognition-of-prior-learning-rpl/' ), 'duration' => 'Assessment-led pathway', 'intakes' => 'Confirmed by admissions' ),
		'consultancy-research' => array( 'title' => 'Consultancy and Research', 'short' => 'Access practical support for workforce development, training, and research needs.', 'image' => $assets . 'languages.jpg', 'icon' => 'fa-chart-line', 'url' => home_url( '/our-ventures/tti-consultancy-and-research/' ), 'duration' => 'Engagement-based', 'intakes' => 'Contact the Toolkit team' ),
	);
}

function eduma_child_course_is_enabled( $slug ) {
	$constant = 'TOOLKIT_COURSE_' . strtoupper( str_replace( '-', '_', $slug ) ) . '_ENABLED';
	return eduma_child_switch( $constant, 'toolkit_course_' . $slug . '_enabled', true );
}

function eduma_child_course_catalog() {
	if ( ! eduma_child_2026_catalog_enabled() ) {
		return eduma_child_legacy_course_catalog();
	}
	return array_filter( eduma_child_2026_course_catalog(), function( $course, $slug ) {
		return eduma_child_course_is_enabled( $slug );
	}, ARRAY_FILTER_USE_BOTH );
}

function eduma_child_get_course( $slug ) {
	if ( ! eduma_child_2026_catalog_enabled() ) {
		return false;
	}
	$catalog = eduma_child_course_catalog();
	return isset( $catalog[ $slug ] ) ? $catalog[ $slug ] : false;
}
