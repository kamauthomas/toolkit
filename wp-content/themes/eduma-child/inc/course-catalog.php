<?php

function eduma_child_2026_course_catalog() {
	$assets = get_stylesheet_directory_uri() . '/assets/images/courses/';

	return array(
		'electrical-installation' => array(
			'title' => 'Electrical Installation', 'short' => 'Build practical installation, wiring, safety, and testing skills for the electrical trade.',
			'image' => $assets . 'electrical.jpg', 'icon' => 'fa-bolt', 'qualification' => 'Electrical Installation Skills Certificate', 'examining' => 'NITA Trade Test',
			'duration' => '6 months: 3 months of on-site training and 3 months in industry', 'entry' => 'KCPE or KCSE certificate', 'fees' => 'KES 150,000 standard; KES 120,962 subsidised', 'intakes' => 'January, April, July and October',
			'outcomes' => array( 'Electrical safety and tools', 'Domestic and commercial wiring', 'Installation testing', 'Industry attachment' ),
		),
		'solar-technician' => array(
			'title' => 'Solar Technician Pathway', 'short' => 'Train for solar PV installation and electrical work, with a focused upskilling option for experienced learners.',
			'image' => $assets . 'solar.jpg', 'icon' => 'fa-solar-panel', 'qualification' => 'Solar PV Installer or Solar Electrician Skills Certificate', 'examining' => 'NITA Solar PV Assessment, Wireman Grade III or Solar T1',
			'duration' => '6 months: 3 months of on-site training and 3 months in industry; upskilling course: 10 days', 'entry' => 'KCPE or KCSE certificate; prior electrical experience for upskilling', 'fees' => 'KES 150,000 standard; KES 120,962 subsidised; upskilling KES 30,684', 'intakes' => 'January, April, July and October; upskilling monthly',
			'outcomes' => array( 'Solar PV system fundamentals', 'Safe installation practice', 'Testing and maintenance', 'Industry attachment' ),
		),
		'advanced-welding-vr' => array(
			'title' => 'Advanced MIG/MAG Welding with VR', 'short' => 'Develop advanced MIG/MAG welding skills through workshop practice, simulation, and industry placement.',
			'image' => $assets . 'welding.jpg', 'icon' => 'fa-fire', 'qualification' => 'Advanced Welding Skills Certificate with VR', 'examining' => 'KEBS, NITA or IIW',
			'duration' => '6 months: 3 months of on-site training and 3 months in industry', 'entry' => 'KCPE or KCSE certificate', 'fees' => 'KES 176,276 standard; KES 120,962 subsidised', 'intakes' => 'January, April, July and October',
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
		'welding-and-fabrication' => array( 'title' => 'MIG/MAG Welding', 'short' => 'Build confident MIG/MAG welding technique through guided workshop practice and modern learning tools.', 'seo_title' => 'MIG/MAG Welding Training in Kenya | The Toolkit for Skills and Innovation', 'seo_description' => 'Study practical MIG/MAG welding at The Toolkit for Skills and Innovation in Kikuyu, Kenya, through guided workshop training, VR-supported learning, and instructor feedback.', 'image' => $assets . 'welding.jpg', 'icon' => 'fa-fire', 'url' => home_url( '/our-ventures/construction-sector-skills/' ), 'duration' => 'Contact admissions for the current schedule', 'intakes' => 'Confirmed by admissions', 'qualification' => 'Practical MIG/MAG welding training', 'examining' => 'Confirmed for the selected pathway', 'entry' => 'Admissions will advise based on the selected level', 'outcomes' => array( 'Workshop safety', 'MIG/MAG process setup', 'Weld technique and control', 'Quality-aware practical work' ) ),
		'renewable-energy' => array( 'title' => 'Renewable Energy', 'seo_title' => 'Solar Installation Training in Kenya | Toolkit', 'seo_description' => 'Build practical solar installation foundations in Kikuyu, Kenya, including system principles, safe installation practice, testing and maintenance awareness.', 'short' => 'Build technical foundations for solar and emerging green-skills opportunities.', 'image' => $assets . 'solar.jpg', 'icon' => 'fa-solar-panel', 'url' => home_url( '/our-ventures/renewable-energy/' ), 'duration' => 'Contact admissions for the current schedule', 'intakes' => 'Confirmed by admissions', 'qualification' => 'Renewable energy skills training', 'examining' => 'Confirmed for the selected pathway', 'entry' => 'Admissions will advise based on the selected level', 'outcomes' => array( 'Solar energy fundamentals', 'Safe installation practice', 'System testing', 'Green-skills career awareness' ) ),
		'organic-farming-skills' => array( 'title' => 'Organic Farming Skills', 'seo_title' => 'Organic Farming Training in Kenya | Toolkit', 'seo_description' => 'Learn practical organic farming in Kikuyu, Kenya, with applied training in soil care, crop planning, sustainable production and agricultural enterprise.', 'short' => 'Learn practical and sustainable approaches to agriculture and enterprise.', 'image' => $assets . 'experiences/organic-farm.jpg', 'icon' => 'fa-seedling', 'url' => home_url( '/our-ventures/organic-farming-skills/' ), 'duration' => 'Contact admissions for the current schedule', 'intakes' => 'Confirmed by admissions', 'qualification' => 'Practical organic farming skills training', 'examining' => 'Confirmed for the selected pathway', 'entry' => 'Contact admissions for current requirements', 'outcomes' => array( 'Sustainable crop production', 'Soil and resource care', 'Farm planning', 'Agricultural enterprise skills' ) ),
		'digital-skills-online-jobs' => array( 'title' => 'Digital Skills', 'short' => 'Strengthen practical digital capability for changing work and entrepreneurship.', 'image' => $assets . 'digital.jpg', 'icon' => 'fa-laptop-code', 'url' => home_url( '/our-ventures/access-online-jobs/' ), 'duration' => 'Contact admissions for the current schedule', 'intakes' => 'Confirmed by admissions', 'qualification' => 'Practical digital skills training', 'examining' => 'Confirmed for the selected pathway', 'entry' => 'Contact admissions for current requirements', 'outcomes' => array( 'Digital literacy', 'Productivity tools', 'Online work readiness', 'Digital enterprise foundations' ) ),
		'recognition-prior-learning' => array( 'title' => 'Recognition of Prior Learning', 'seo_title' => 'Recognition of Prior Learning Assessment Kenya | Toolkit', 'seo_description' => 'Explore a Recognition of Prior Learning pathway in Kenya for documenting existing trade skills, preparing evidence and progressing toward assessment.', 'short' => 'Turn existing skills and experience into recognised progression pathways.', 'image' => $assets . 'welding.jpg', 'icon' => 'fa-award', 'url' => home_url( '/our-ventures/construction-sector-skills/recognition-of-prior-learning-rpl/' ), 'duration' => 'Assessment-led pathway', 'intakes' => 'Confirmed by admissions', 'qualification' => 'Recognition of Prior Learning assessment', 'examining' => 'Confirmed for the selected trade', 'entry' => 'Relevant existing skills or work experience', 'outcomes' => array( 'Skills evidence preparation', 'Competency assessment', 'Gap identification', 'Progression guidance' ) ),
		'consultancy-research' => array( 'title' => 'Consultancy and Research', 'seo_title' => 'Workforce Training Research and Consultancy Kenya | Toolkit', 'seo_description' => 'Partner with Toolkit in Kenya on workforce research, training-needs analysis, practical programme design, implementation support and learning reviews.', 'short' => 'Access practical support for workforce development, training, and research needs.', 'image' => $assets . 'entrepreneurship.jpg', 'icon' => 'fa-chart-line', 'url' => home_url( '/our-ventures/tti-consultancy-and-research/' ), 'duration' => 'Engagement-based', 'intakes' => 'Contact the Toolkit team', 'qualification' => 'Custom institutional engagement', 'examining' => 'Not applicable', 'entry' => 'Designed around the partner brief', 'outcomes' => array( 'Training needs analysis', 'Workforce programme design', 'Applied research', 'Implementation support' ) ),
		'online-training-jielimishe' => array( 'title' => 'Online Training Portal - Jielimishe', 'seo_title' => 'Online Vocational Training in Kenya | Jielimishe', 'seo_description' => 'Access structured online vocational learning from Toolkit through Jielimishe, with guided resources, digital participation and course-specific progression.', 'short' => 'Access Toolkit learning opportunities through the Jielimishe online training portal.', 'image' => $assets . 'digital.jpg', 'icon' => 'fa-laptop', 'url' => home_url( '/our-ventures/online-training-portal-jielimishe/' ), 'duration' => 'Varies by online course', 'intakes' => 'Course-specific enrolment', 'qualification' => 'Online learning pathway', 'examining' => 'Varies by selected course', 'entry' => 'Varies by selected course', 'outcomes' => array( 'Flexible online learning', 'Structured learning resources', 'Digital participation', 'Course-specific progression' ) ),
		'welding-virtual-reality' => array( 'title' => 'Training Welders with Virtual Reality', 'seo_title' => 'Virtual Reality Welding Training in Kenya | Toolkit', 'seo_description' => 'Explore VR-supported welding training in Kenya that helps learners build confidence, rehearse safe techniques and progress into guided workshop practice.', 'short' => 'Use virtual-reality simulation alongside guided practice to build welding confidence and technique.', 'image' => $assets . 'welding.jpg', 'icon' => 'fa-vr-cardboard', 'url' => home_url( '/our-ventures/construction-sector-skills/training-welders-with-virtual-reality/' ), 'duration' => 'Contact admissions for the current schedule', 'intakes' => 'Confirmed by admissions', 'qualification' => 'VR-supported welding training', 'examining' => 'Confirmed for the selected pathway', 'entry' => 'Admissions will advise based on the selected level', 'outcomes' => array( 'Simulation-led practice', 'Technique development', 'Safe process rehearsal', 'Workshop progression' ) ),
	);
}

function eduma_child_legacy_course_page_map() {
	return array(
		'construction-sector-skills'              => 'welding-and-fabrication',
		'renewable-energy'                        => 'renewable-energy',
		'organic-farming-skills'                  => 'organic-farming-skills',
		'access-online-jobs'                      => 'digital-skills-online-jobs',
		'recognition-of-prior-learning-rpl'       => 'recognition-prior-learning',
		'tti-consultancy-and-research'            => 'consultancy-research',
		'online-training-portal-jielimishe'       => 'online-training-jielimishe',
		'training-welders-with-virtual-reality'   => 'welding-virtual-reality',
	);
}

function eduma_child_get_legacy_course_key_for_page() {
	if ( ! is_page() ) {
		return false;
	}
	$map  = eduma_child_legacy_course_page_map();
	$slug = get_post_field( 'post_name', get_queried_object_id() );
	return isset( $map[ $slug ] ) ? $map[ $slug ] : false;
}

function eduma_child_get_legacy_course_for_page() {
	if ( ! is_page() ) {
		return false;
	}
	$key     = eduma_child_get_legacy_course_key_for_page();
	$catalog = eduma_child_legacy_course_catalog();
	return $key && isset( $catalog[ $key ] ) ? $catalog[ $key ] : false;
}

function eduma_child_course_experience( $key ) {
	$images = get_stylesheet_directory_uri() . '/assets/images/courses/';
	$config = array(
		'welding-and-fabrication' => array( 'theme' => 'welding', 'eyebrow' => 'The welding floor', 'heading' => 'Set up. Strike. Inspect. Improve.', 'intro' => 'Learn through a deliberate workshop rhythm: understand the process, prepare safely, practise with feedback, and inspect the result.', 'secondary' => $images . 'experiences/vr-welding.jpg', 'secondary_alt' => 'Toolkit learner using virtual-reality welding equipment', 'moments' => array( array( 'fa-shield-alt', 'Prepare safely', 'Read the work area, select PPE and set up equipment with purpose.' ), array( 'fa-fire', 'Control the process', 'Develop MIG/MAG torch control through guided, repeated practice.' ), array( 'fa-search', 'Read the weld', 'Inspect workmanship, identify defects and make informed adjustments.' ) ) ),
		'renewable-energy' => array( 'theme' => 'energy', 'eyebrow' => 'The energy field', 'heading' => 'Learn where power meets practical work.', 'intro' => 'Move from system fundamentals to safe installation, testing and maintenance in an applied solar learning environment.', 'secondary' => $images . 'experiences/solar-workshop.jpg', 'secondary_alt' => 'Toolkit learners working with practical solar training rigs', 'moments' => array( array( 'fa-sun', 'Understand the system', 'Connect generation, storage, control and electrical safety principles.' ), array( 'fa-tools', 'Install with care', 'Practise mounting, wiring and component handling in realistic tasks.' ), array( 'fa-bolt', 'Test and maintain', 'Measure performance, diagnose faults and document completed work.' ) ) ),
		'organic-farming-skills' => array( 'theme' => 'organic', 'eyebrow' => 'The teaching farm', 'heading' => 'Learn with soil on your hands.', 'intro' => 'Follow the full growing cycle in a living classroom, from healthy soil and crop planning to harvest quality and farm enterprise.', 'secondary' => $images . 'experiences/organic-farm.jpg', 'secondary_alt' => 'Organic farming learners examining crops and compost on a teaching farm', 'moments' => array( array( 'fa-seedling', 'Build healthy soil', 'Work with compost, soil care and resource-conscious production.' ), array( 'fa-leaf', 'Grow with intent', 'Plan crops, monitor plant health and use water responsibly.' ), array( 'fa-chart-line', 'Think as an enterprise', 'Connect production choices with quality, markets and farm records.' ) ) ),
		'digital-skills-online-jobs' => array( 'theme' => 'digital', 'eyebrow' => 'The digital studio', 'heading' => 'Practise the work, not just the software.', 'intro' => 'Build useful digital habits through guided tasks that mirror communication, productivity, online work and enterprise needs.', 'secondary' => $images . 'digital.jpg', 'secondary_alt' => 'Learner developing practical digital skills on a laptop', 'moments' => array( array( 'fa-laptop', 'Work confidently', 'Organise files, communicate clearly and use everyday productivity tools.' ), array( 'fa-globe', 'Navigate online work', 'Understand platforms, professional conduct and safe digital participation.' ), array( 'fa-lightbulb', 'Create value', 'Apply digital tools to real tasks, services and enterprise ideas.' ) ) ),
		'recognition-prior-learning' => array( 'theme' => 'recognition', 'eyebrow' => 'Evidence to recognition', 'heading' => 'Make existing skill visible.', 'intro' => 'Turn work experience into a structured evidence journey, identify any competency gaps and prepare for trade assessment.', 'secondary' => $images . 'experiences/learner-outcomes.jpg', 'secondary_alt' => 'Toolkit graduates celebrating recognised learning outcomes', 'moments' => array( array( 'fa-briefcase', 'Map experience', 'Connect previous work and practical ability to recognised competencies.' ), array( 'fa-folder-open', 'Build evidence', 'Prepare a clear portfolio and demonstrate skill during assessment.' ), array( 'fa-award', 'Plan progression', 'Use the result to guide certification, further learning or employment.' ) ) ),
		'consultancy-research' => array( 'theme' => 'enterprise', 'eyebrow' => 'From question to action', 'heading' => 'Evidence designed to be used.', 'intro' => 'Work with Toolkit to understand a workforce challenge, shape a practical response and carry learning into implementation.', 'secondary' => $images . 'experiences/learner-outcomes.jpg', 'secondary_alt' => 'Toolkit learners representing practical programme outcomes', 'moments' => array( array( 'fa-search', 'Frame the need', 'Clarify the workforce, training or research question.' ), array( 'fa-project-diagram', 'Design the response', 'Translate evidence into a practical programme or implementation plan.' ), array( 'fa-chart-line', 'Measure the result', 'Track learning, outcomes and opportunities for improvement.' ) ) ),
		'online-training-jielimishe' => array( 'theme' => 'digital', 'eyebrow' => 'Your online classroom', 'heading' => 'Structured learning, wherever you are.', 'intro' => 'Use a clear online pathway to access learning resources, complete guided activities and keep progressing.', 'secondary' => $images . 'digital.jpg', 'secondary_alt' => 'Learner accessing structured online training', 'moments' => array( array( 'fa-sign-in-alt', 'Enter your pathway', 'Access the selected course and understand its learning sequence.' ), array( 'fa-play-circle', 'Learn actively', 'Use resources and activities to build course-specific capability.' ), array( 'fa-check-circle', 'Track progress', 'Complete the required learning and follow the next-step guidance.' ) ) ),
		'welding-virtual-reality' => array( 'theme' => 'welding', 'eyebrow' => 'Simulation meets workshop', 'heading' => 'Rehearse safely. Practise deliberately.', 'intro' => 'Use immersive simulation to develop movement, process awareness and confidence before reinforcing technique in the workshop.', 'secondary' => $images . 'experiences/vr-welding.jpg', 'secondary_alt' => 'Toolkit learner practising welding technique in virtual reality', 'moments' => array( array( 'fa-vr-cardboard', 'Enter the simulation', 'Practise setup, positioning and movement in a controlled environment.' ), array( 'fa-chart-bar', 'Use the feedback', 'Review performance indicators and focus the next practice attempt.' ), array( 'fa-fire', 'Transfer the skill', 'Carry improved control and awareness into guided workshop work.' ) ) ),
	);
	return isset( $config[ $key ] ) ? $config[ $key ] : array( 'theme' => 'skills', 'eyebrow' => 'Applied learning', 'heading' => 'Build skill through purposeful practice.', 'intro' => 'Move from clear foundations into guided tasks, feedback and practical progression.', 'secondary' => '', 'secondary_alt' => '', 'moments' => array() );
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
