<?php
get_header();

$slug    = get_post_field( 'post_name', get_queried_object_id() );
$assets  = get_stylesheet_directory_uri() . '/assets/images/pages/';
$pages   = array(
	'about-toolkit-africa' => array(
		'kicker'      => 'About Toolkit',
		'title'       => 'Skills that open real opportunities',
		'intro'       => 'Toolkit is a Kenya-based social enterprise founded in 2014 to help young people and women build market-relevant skills and move towards employment or entrepreneurship.',
		'image'       => $assets . 'about.jpg',
		'section'     => 'Who we are',
		'body'        => 'Our programmes combine practical technical training with employability, digital, and entrepreneurship skills. We work with public institutions, development partners, and employers so learning remains connected to changing labour markets.',
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
?>
<main id="main-content" class="toolkit-page toolkit-institutional-page">
	<section class="toolkit-institutional-hero" style="background-image:url('<?php echo esc_url( $page['image'] ); ?>')">
		<div><p class="toolkit-kicker"><?php echo esc_html( $page['kicker'] ); ?></p><h1><?php echo esc_html( $page['title'] ); ?></h1><p><?php echo esc_html( $page['intro'] ); ?></p><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $page['cta_url'] ); ?>"><?php echo esc_html( $page['cta_label'] ); ?> <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
	</section>
	<section class="toolkit-institutional-intro toolkit-section"><div><p class="toolkit-kicker">Toolkit Africa</p><h2><?php echo esc_html( $page['section'] ); ?></h2><p><?php echo esc_html( $page['body'] ); ?></p></div><img src="<?php echo esc_url( $page['image'] ); ?>" width="760" height="520" alt="<?php echo esc_attr( $page['section'] ); ?>"></section>
	<section class="toolkit-institutional-values toolkit-section"><div class="toolkit-section__heading"><p class="toolkit-kicker">What guides the work</p><h2>Purpose in practice</h2></div><div><?php foreach ( $page['cards'] as $card ) : ?><article><i class="fas <?php echo esc_attr( $card['icon'] ); ?>" aria-hidden="true"></i><h3><?php echo esc_html( $card['title'] ); ?></h3><p><?php echo esc_html( $card['text'] ); ?></p></article><?php endforeach; ?></div></section>
	<section class="toolkit-institutional-cta"><div><h2><?php echo esc_html( $page['cta_title'] ); ?></h2><p><?php echo esc_html( $page['cta_text'] ); ?></p></div><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $page['cta_url'] ); ?>"><?php echo esc_html( $page['cta_label'] ); ?> <i class="fas fa-arrow-right" aria-hidden="true"></i></a></section>
</main>
<?php get_footer();
