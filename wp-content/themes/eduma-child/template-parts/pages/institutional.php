<?php
get_header();

$slug    = get_post_field( 'post_name', get_queried_object_id() );
$assets  = get_stylesheet_directory_uri() . '/assets/images/pages/';
$pages   = array(
	'about-toolkit-africa' => array(
		'kicker'      => 'Skills training in Kenya',
		'title'       => 'Skills training in Kenya that opens real opportunities',
		'intro'       => 'Skills training in Kenya should lead somewhere. Founded in 2014, Toolkit helps young people and women build market-relevant skills and move towards employment or entrepreneurship.',
		'image'       => $assets . 'about.jpg',
		'image_alt'   => 'Practical skills training in Kenya at The Toolkit',
		'section'     => 'Skills training built around opportunity',
		'body'        => 'Our skills training in Kenya combines practical technical learning with employability, digital, and entrepreneurship skills. We work with public institutions, development partners, and employers so learning remains connected to changing labour markets.',
		'cards'       => array(
			array( 'title' => 'Our vision', 'text' => 'A leader in powering Africa with skilled, confident, and productive youth.', 'icon' => 'fa-compass' ),
			array( 'title' => 'Our mission', 'text' => 'Transform vulnerable youth towards prosperity through innovation and skills for current and future labour markets.', 'icon' => 'fa-bullseye' ),
			array( 'title' => 'How we work', 'text' => 'Practical training, recognised skills, industry exposure, and pathways into work or enterprise.', 'icon' => 'fa-people-carry' ),
		),
		'cta_title'   => 'Find the right learning pathway',
		'cta_text'    => 'Explore Toolkit courses and practical programmes.',
		'cta_label'   => 'Explore courses',
		'cta_url'     => home_url( '/our-ventures/' ),
	),
	'toolkit-in-brief' => array(
		'kicker'      => 'Toolkit in Brief',
		'title'       => 'A practical skills training model built around opportunity',
		'intro'       => 'Toolkit in Brief explains how practical learning, recognised skills and industry connection support pathways towards work and enterprise.',
		'image'       => $assets . 'about.jpg',
		'image_alt'   => 'Toolkit in Brief skills training model',
		'section'     => 'How the Toolkit skills training model works',
		'body'        => 'Toolkit combines hands-on technical learning with employability, digital and entrepreneurship skills. Programmes respond to changing labour markets while admissions guidance helps learners choose an appropriate pathway.',
		'cards'       => array(
			array( 'title' => 'Practical learning', 'text' => 'Learners build capability through guided practice and applied tasks.', 'icon' => 'fa-tools' ),
			array( 'title' => 'Skills recognition', 'text' => 'Relevant pathways prepare learners to demonstrate competence through recognised assessment.', 'icon' => 'fa-award' ),
			array( 'title' => 'Opportunity', 'text' => 'Industry exposure and job-linkage support connect learning with work and enterprise.', 'icon' => 'fa-route' ),
		),
		'cta_title'   => 'Choose a practical learning pathway',
		'cta_text'    => 'Compare current Toolkit courses and ask admissions for current guidance.',
		'cta_label'   => 'Explore courses',
		'cta_url'     => home_url( '/our-ventures/' ),
	),
	'the-toolkit-foundation-copy' => array(
		'kicker'      => 'Impact and insights',
		'title'       => 'From practical learning to lasting livelihoods',
		'intro'       => 'Toolkit connects hands-on learning with industrial exposure, employability skills, and pathways into work and enterprise.',
		'image'       => $assets . 'impact.jpg',
		'section'     => 'Our foundational purpose',
		'body'        => 'We equip vulnerable young people with professional, life, digital, and entrepreneurship skills for current and future labour markets. Learners gain practical experience and guidance designed to help them enter the workplace with confidence.',
		'cards'       => array(
			array( 'title' => 'Job linkages', 'text' => 'Industry exposure helps learners understand workplace expectations and build relevant experience.', 'icon' => 'fa-briefcase' ),
			array( 'title' => 'Inclusive opportunity', 'text' => 'Programmes create practical pathways for young people and women facing barriers to work.', 'icon' => 'fa-hands-helping' ),
			array( 'title' => 'Market relevance', 'text' => 'Training is shaped by technologies, standards, and skills demanded in changing sectors.', 'icon' => 'fa-chart-line' ),
		),
		'cta_title'   => 'See Toolkit stories and updates',
		'cta_text'    => 'Follow current programmes, partnerships, and learner journeys.',
		'cta_label'   => 'Visit Toolkit Blog',
		'cta_url'     => home_url( '/toolkit-blog/' ),
	),
	'the-toolkit-foundation' => array(
		'kicker'      => 'The Toolkit Foundation',
		'title'       => 'Skills and opportunity where they are needed most',
		'intro'       => 'The Toolkit Foundation supports inclusive skills development for underserved communities, including women and displaced young people.',
		'image'       => $assets . 'foundation.jpg',
		'section'     => 'Building practical pathways',
		'body'        => 'Foundation programmes use technical, digital, and enterprise learning to strengthen confidence, livelihoods, and community resilience. Work in Kakuma has included renewable-energy and solar-skills training delivered with institutional and development partners.',
		'cards'       => array(
			array( 'title' => 'Inclusive skilling', 'text' => 'Training is designed around the realities and aspirations of underserved learners.', 'icon' => 'fa-users' ),
			array( 'title' => 'Green opportunity', 'text' => 'Renewable-energy skills connect community needs with growing technical sectors.', 'icon' => 'fa-solar-panel' ),
			array( 'title' => 'Partnership delivery', 'text' => 'Public, humanitarian, academic, and private-sector partners strengthen programme reach.', 'icon' => 'fa-handshake' ),
		),
		'cta_title'   => 'Connect with the Foundation',
		'cta_text'    => 'Speak with Toolkit about partnerships and programme support.',
		'cta_label'   => 'Contact Toolkit',
		'cta_url'     => home_url( '/contact/' ),
	),
);

$page = $pages[ $slug ] ?? $pages['about-toolkit-africa'];
$is_about = 'about-toolkit-africa' === $slug;
$is_brief = 'toolkit-in-brief' === $slug;
$team = array(
	array( 'name' => 'Jane Muigai Kamphuis', 'role' => 'Founder & Director', 'image' => 'jane-muigai-kamphuis.jpg', 'bio' => 'Jane founded Toolkit in 2014 and leads its mission to connect vulnerable young people and women with skills, certification and pathways to work.' ),
	array( 'name' => 'Hosea Mugera', 'role' => 'Manager', 'image' => 'hosea-mugera.jpg', 'bio' => 'Hosea supports the day-to-day management of Toolkit and the delivery of its skills and innovation programmes.' ),
	array( 'name' => 'Marion Ngige', 'role' => 'HR Manager', 'image' => 'marion-ngige.jpg', 'bio' => 'Marion brings more than eight years of human-resource experience to the people, systems and culture behind Toolkit programmes.' ),
	array( 'name' => 'Reuben Waburi', 'role' => 'Career Development Manager', 'image' => 'reuben-waburi.jpg', 'bio' => 'Reuben is an electrical engineer and project manager with more than two decades of experience supporting career and technical development.' ),
	array( 'name' => 'Ann Nyokabi', 'role' => 'Youth Skills Manager', 'image' => 'ann-nyokabi.jpg', 'bio' => 'Ann has more than eleven years of experience spanning technical practice, training and learner engagement.' ),
	array( 'name' => 'Daniel Omondi', 'role' => 'Head of Welding', 'image' => 'daniel-omondi.jpg', 'bio' => 'Daniel brings mechanical-engineering and welding experience to the leadership of Toolkit’s practical welding programmes.' ),
	array( 'name' => 'Silvester Kibet', 'role' => 'Job Linkage Officer', 'image' => 'silvester-kibet.jpg', 'bio' => 'Silvester supports learners as they prepare for and connect with employment opportunities.' ),
	array( 'name' => 'Muthomi Murage', 'role' => 'Job Linkage Officer', 'image' => 'muthomi-murage.jpg', 'bio' => 'Muthomi supports digital technology and learner pathways into work.' ),
	array( 'name' => 'Beatrice Mutinda', 'role' => 'Job Linkage Officer', 'image' => 'beatrice-mutinda.jpg', 'bio' => 'Beatrice brings an ICT technical-training background to learner and job-linkage support.' ),
	array( 'name' => 'Michael Ndwiga', 'role' => 'Trainer', 'image' => 'michael-ndwiga.jpg', 'bio' => 'Michael serves as a welding instructor within Toolkit’s practical training team.' ),
	array( 'name' => 'Josephine Kemunto', 'role' => 'Workshop Assistant', 'image' => 'josephine-kemunto.jpg', 'bio' => 'Josephine supports learners and trainers in the day-to-day operation of Toolkit workshops.' ),
	array( 'name' => 'Robert Chemitei', 'role' => 'Trainer', 'image' => 'robert-chemitei.jpg', 'bio' => 'Robert is an ICT practitioner and trainer who supports practical technology learning.' ),
	array( 'name' => 'Esther Liberatta', 'role' => 'Trainer', 'image' => 'esther-liberatta.jpg', 'bio' => 'Esther teaches entrepreneurship and helps learners develop practical enterprise skills.' ),
	array( 'name' => 'Doreen Kiriinya', 'role' => 'Accountant', 'image' => 'doreen-kiriinya.jpg', 'bio' => 'Doreen supports Toolkit’s financial operations and programme accountability.' ),
	array( 'name' => 'Phylis Kibet', 'role' => 'Logistics Officer', 'image' => 'phylis-kibet.jpg', 'bio' => 'Phylis coordinates transport and logistics that support Toolkit activities.' ),
	array( 'name' => 'Gladwell Mumbi', 'role' => 'Communication Associate', 'image' => 'gladwell-mumbi.jpg', 'bio' => 'Gladwell supports Toolkit’s communication, marketing and engagement work.' ),
	array( 'name' => 'Lucy Muthoni', 'role' => 'Customer Service Associate', 'image' => 'lucy-muthoni.jpg', 'bio' => 'Lucy supports front-desk services and assists learners, visitors and customers.' ),
	array( 'name' => 'Lucy Wanjiru', 'role' => 'Customer Service Associate', 'image' => 'lucy-wanjiru.jpg', 'bio' => 'Lucy supports front-desk services, customer care and learner enquiries.' ),
);
?>
<main id="main-content" class="toolkit-page toolkit-institutional-page">
	<section class="toolkit-institutional-hero" style="background-image:url('<?php echo esc_url( $page['image'] ); ?>')">
		<div><p class="toolkit-kicker"><?php echo esc_html( $page['kicker'] ); ?></p><h1><?php echo esc_html( $page['title'] ); ?></h1><p><?php echo esc_html( $page['intro'] ); ?></p><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $page['cta_url'] ); ?>"><?php echo esc_html( $page['cta_label'] ); ?> <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
	</section>
	<section class="toolkit-institutional-intro toolkit-section"><div><p class="toolkit-kicker">The Toolkit for Skills and Innovation</p><h2><?php echo esc_html( $page['section'] ); ?></h2><p><?php echo esc_html( $page['body'] ); ?></p></div><img src="<?php echo esc_url( $page['image'] ); ?>" width="760" height="520" alt="<?php echo esc_attr( $page['image_alt'] ?? $page['section'] ); ?>"></section>
	<section class="toolkit-institutional-values toolkit-section"><div class="toolkit-section__heading"><p class="toolkit-kicker">What guides the work</p><h2>Purpose in practice</h2></div><div><?php foreach ( $page['cards'] as $card ) : ?><article><i class="fas <?php echo esc_attr( $card['icon'] ); ?>" aria-hidden="true"></i><h3><?php echo esc_html( $card['title'] ); ?></h3><p><?php echo esc_html( $card['text'] ); ?></p></article><?php endforeach; ?></div></section>
	<?php if ( $is_about ) : ?>
	<section class="toolkit-section toolkit-institutional-intro" aria-labelledby="toolkit-about-training-title"><div><p class="toolkit-kicker">A learner-centred approach</p><h2 id="toolkit-about-training-title">How skills training in Kenya becomes practical opportunity</h2><p>Effective skills training in Kenya needs more than classroom instruction. Learners need clear information before enrolment, guided practice while training and realistic support as they prepare for work or enterprise. Toolkit brings these stages together so that technical ability develops alongside communication, digital participation, problem-solving and workplace awareness.</p><p>The process begins with course guidance. Prospective learners can compare the <a href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">current practical learning pathways</a> and ask admissions about entry requirements, schedules, fees, available funding and assessment arrangements. These details can change between programmes and intakes, so Toolkit confirms them before enrolment rather than relying on outdated general claims.</p><h3>Practice connected to real settings</h3><p>Learning activities are designed around application. Depending on the pathway, this can include guided workshop tasks, digital tools, simulation, field activity or enterprise exercises. Trainers help learners understand safe working methods, build technical confidence and reflect on the quality of their work. This approach makes it easier to connect a lesson with the decisions and standards encountered in a practical setting.</p><p>Toolkit also uses stories, photographs and video to help prospective learners understand the learning environment before applying. The <a href="<?php echo esc_url( home_url( '/tti-media/' ) ); ?>">official Toolkit video collection</a> draws from the institution’s public <a href="https://www.youtube.com/@toolkitafrica" target="_blank" rel="noopener">YouTube channel</a>, while the blog documents programmes, partnerships and learner experiences with dates and context.</p><h3>Recognition, work readiness and progression</h3><p>Skills development continues beyond the practical task itself. Where a pathway involves assessment or recognition, learners receive relevant preparation and admissions confirms the applicable examining arrangements. Employability and enterprise learning help participants explain what they can do, understand workplace expectations and consider different routes into income generation.</p><p>No training provider can guarantee a job or business outcome. Toolkit’s role is to strengthen capability, improve readiness and connect learning with credible progression routes. Industry exposure, institutional partnerships and job-linkage support can help learners navigate opportunities, but results also depend on the selected pathway, demonstrated competence, labour-market conditions and individual circumstances.</p><h3>Choosing the right Toolkit pathway</h3><p>A suitable course should reflect a learner’s goals, current experience, availability and entry profile. Someone building a new technical foundation may need a different route from an experienced artisan seeking recognition of prior learning. Prospective learners should review the course information, check the <a href="<?php echo esc_url( home_url( '/notice-board/' ) ); ?>">Toolkit Notice Board</a> and <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">contact admissions</a> when a requirement is unclear.</p><p>This combination of accurate guidance, applied learning and progression support defines Toolkit’s approach to skills training in Kenya. It is intended to help young people and women make informed choices, build demonstrable capability and move towards work or enterprise with a clearer understanding of the next step.</p></div><img src="<?php echo esc_url( $page['image'] ); ?>" width="760" height="520" loading="lazy" alt="Learners building practical skills through training in Kenya"></section>
	<section class="toolkit-about-method toolkit-section" aria-labelledby="toolkit-about-method-title"><div><p class="toolkit-kicker">From training to livelihoods</p><h2 id="toolkit-about-method-title">A practical skills training journey</h2><p>Toolkit’s model is designed around the practical steps a learner needs to move forward, from gaining job-relevant competence to demonstrating it and navigating the next opportunity.</p></div><ol><li><span>01</span><h3>Train</h3><p>Hands-on technical, digital, employability and enterprise learning.</p></li><li><span>02</span><h3>Certify</h3><p>Preparation for skills recognition through relevant assessment bodies.</p></li><li><span>03</span><h3>Connect</h3><p>Industry exposure and pathways towards employment or entrepreneurship.</p></li></ol></section>
	<section class="toolkit-team toolkit-section" aria-labelledby="toolkit-team-title"><div class="toolkit-team__heading"><div><p class="toolkit-kicker">People behind the work</p><h2 id="toolkit-team-title">Our Team</h2></div><p>Meet the people delivering training, learner support, career development and the day-to-day work of Toolkit.</p></div><div class="toolkit-team__grid"><?php foreach ( $team as $member ) : ?><article><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/team/' . $member['image'] ); ?>" width="900" height="900" loading="lazy" alt="Portrait of <?php echo esc_attr( $member['name'] ); ?>"><div><p><?php echo esc_html( $member['role'] ); ?></p><h3><?php echo esc_html( $member['name'] ); ?></h3><span><?php echo esc_html( $member['bio'] ); ?></span></div></article><?php endforeach; ?></div></section>
	<?php endif; ?>
	<?php if ( $is_brief ) : ?>
	<section class="toolkit-section toolkit-institutional-intro"><div><p class="toolkit-kicker">From learning to livelihoods</p><h2>Toolkit in Brief: the learner journey</h2><p>The journey begins with clear information. Learners compare available courses, discuss entry guidance with admissions and select a pathway that fits their goals and current experience.</p><p>Training then combines instruction with practical activity. Depending on the programme, learners may use workshops, digital tools, simulation, field learning or enterprise exercises. Trainers focus on safe practice, technical confidence and the ability to apply knowledge.</p><p>Skills development also includes preparation for the workplace. Employability, communication, digital participation and entrepreneurship help learners understand how competence connects with real opportunities.</p><p>Finally, Toolkit works with institutions, employers and development partners to strengthen assessment, industry exposure and progression. Current course availability, schedules, fees and examining arrangements are confirmed by admissions because they can differ by pathway and intake.</p></div><img src="<?php echo esc_url( $page['image'] ); ?>" width="760" height="520" loading="lazy" alt="Toolkit in Brief practical learner journey"></section>
	<?php endif; ?>
	<section class="toolkit-institutional-cta"><div><h2><?php echo esc_html( $page['cta_title'] ); ?></h2><p><?php echo esc_html( $page['cta_text'] ); ?></p></div><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $page['cta_url'] ); ?>"><?php echo esc_html( $page['cta_label'] ); ?> <i class="fas fa-arrow-right" aria-hidden="true"></i></a></section>
</main>
<?php get_footer();
