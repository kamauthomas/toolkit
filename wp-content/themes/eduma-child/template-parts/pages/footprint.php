<?php
/**
 * Toolkit footprint in youth skills and employability.
 * A dated record of programmes delivered with named partners, 2014-2026.
 * The source chronology is grouped into readable chapters without changing the
 * programme descriptions or attributing outcomes that the record does not show.
 */
get_header();

$eras = array(
	array( 'years' => '2014 – 2016', 'items' => array(
		array( 'Crown', 'Mobilization and skills training for youth painters' ),
		array( 'UK aid', 'Training of youth plumbers' ),
		array( 'World Vision', 'Mobilization and training for painters and tilers' ),
	) ),
	array( 'years' => '2017', 'items' => array(
		array( 'NITA', 'Developed standards and instruments for pedagogical skills' ),
		array( 'UK aid', 'Skilling youth welders in Mombasa' ),
		array( 'NITA', 'Training of assessors and certifiers of plumbers and welders in Mombasa' ),
	) ),
	array( 'years' => '2017 – 2018', 'items' => array(
		array( 'Jaffer Foundation', 'Training of welders in Mombasa' ),
	) ),
	array( 'years' => '2017 – 2021', 'items' => array(
		array( 'AGOL', 'On-the-job training for youth technicians' ),
	) ),
	array( 'years' => '2018', 'items' => array(
		array( 'World Vision', 'Training of electricians and plumbers in Nairobi and Kiambu' ),
		array( 'World Vision', 'Training of painters, electricians and plumbers' ),
		array( 'Toolkit', 'Upgrading of the Electricals Department — instructors, curriculum, equipment and workshop' ),
		array( 'NITA & KYEOP', 'Training youth electricians and plumbers in Mombasa and Kisumu' ),
	) ),
	array( 'years' => '2018 – 2020', 'items' => array(
		array( 'Habitat for Humanity', 'Developed the Online Training and Deployment Portal (OTAD)' ),
	) ),
	array( 'years' => '2019', 'items' => array(
		array( 'SOS Children’s Villages', 'Life and Employability Skills Training' ),
		array( 'Concern Worldwide', 'Upskilling of instructors from 10 vocational training colleges in Nairobi' ),
		array( 'ILO', 'Research on best practices in apprenticeship in Kenya' ),
	) ),
	array( 'years' => '2020', 'items' => array(
		array( 'UNESCO', 'Renewable energy and environmental labour market analysis' ),
		array( 'Toolkit', 'Technical audit of electrical skills training' ),
		array( 'ILO', 'Research on regulatory gaps for apprenticeship in Kenya' ),
	) ),
	array( 'years' => '2020 – 2022', 'items' => array(
		array( 'Safaricom Foundation', 'Training youth on Life and Employability Skills using technology' ),
	) ),
	array( 'years' => '2020 – 2026', 'items' => array(
		array( 'Lishe Demo Farm', 'Green jobs and climate-smart agriculture for youth in Kiambu' ),
	) ),
	array( 'years' => '2021 – 2022', 'items' => array(
		array( 'Sector partners', 'Upskilling instructors and assessors in advanced technology for training welders' ),
		array( 'ILO', 'Digital skills gap assessment' ),
		array( 'Habitat for Humanity', 'Recognition of Prior Learning (RPL) for construction workers' ),
	) ),
	array( 'years' => '2021 – 2024', 'items' => array(
		array( 'Challenge Fund for Youth Employment', 'Green jobs and solar skills training for youth in Kenya' ),
		array( 'Embassy of France', 'Green jobs and solar skills for 100 girls in Turkana County' ),
	) ),
	array( 'years' => '2022 – 2023', 'items' => array(
		array( 'GIZ', 'Training welders using advanced Virtual Reality (VR) technology' ),
		array( 'Toolkit', 'Establishment of the Toolkit Skills and Innovation Hub' ),
	) ),
);

/* Partner roll-call, de-duplicated in first-appearance order. Generic labels are
   omitted because they name no external organisation. */
$partners = array();
$milestone_count = 0;
foreach ( $eras as $era ) {
	$milestone_count += count( $era['items'] );
	foreach ( $era['items'] as $item ) {
		foreach ( explode( ' & ', $item[0] ) as $partner ) {
			if ( ! in_array( $partner, array( 'Toolkit', 'Sector partners' ), true ) && ! in_array( $partner, $partners, true ) ) {
				$partners[] = $partner;
			}
		}
	}
}

$assets = get_stylesheet_directory_uri() . '/assets/images/';
$focus_areas = array(
	array(
		'title' => 'Practical trades and workforce readiness',
		'copy'  => 'Hands-on training in painting, plumbing, welding and electrical work, supported by life and employability skills.',
		'image' => $assets . 'courses/electrical.jpg',
		'alt'   => 'A learner undertaking practical electrical training',
	),
	array(
		'title' => 'Standards, recognition and evidence',
		'copy'  => 'Work on training standards, instructor and assessor capacity, apprenticeship research and Recognition of Prior Learning.',
		'image' => $assets . 'graduation/kmm-2071-jpg.webp',
		'alt'   => 'Toolkit graduates gathered after their graduation ceremony',
	),
	array(
		'title' => 'Green and technology transitions',
		'copy'  => 'Solar skills, climate-smart agriculture, digital delivery and virtual reality brought new tools into practical learning.',
		'image' => $assets . 'courses/experiences/solar-workshop.jpg',
		'alt'   => 'Learners working with solar equipment during an outdoor practical session',
	),
);
$chapters = array(
	array(
		'id'      => 'foundations',
		'period'  => '2014 – 2016',
		'title'   => 'Building the foundations',
		'copy'    => 'The record begins with trade-specific mobilisation and practical training for painters, plumbers and tilers.',
		'indexes' => array( 0 ),
	),
	array(
		'id'      => 'scale',
		'period'  => '2017 – 2020',
		'title'   => 'Growing delivery and strengthening standards',
		'copy'    => 'Toolkit expanded training across several trades and locations while contributing to standards, assessor development and online delivery.',
		'indexes' => array( 1, 2, 3, 4, 5 ),
	),
	array(
		'id'      => 'evidence',
		'period'  => '2019 – 2022',
		'title'   => 'Adding evidence, employability and digital reach',
		'copy'    => 'Research, instructor development and technology-enabled employability programmes broadened the institution’s role beyond trade instruction alone.',
		'indexes' => array( 6, 7, 8, 9 ),
	),
	array(
		'id'      => 'innovation',
		'period'  => '2021 – 2026',
		'title'   => 'Advancing green skills and training technology',
		'copy'    => 'The latest entries bring together Recognition of Prior Learning, solar and agricultural skills, digital assessment and virtual reality.',
		'indexes' => array( 10, 11, 12 ),
	),
);
?>
<main id="main-content" class="toolkit-page toolkit-footprint-page">
	<section class="toolkit-footprint-hero">
		<div class="toolkit-footprint-hero__inner">
			<div class="toolkit-footprint-hero__copy">
				<p class="toolkit-kicker">Our institutional record · 2014–2026</p>
				<h1>Skills grow when people build together.</h1>
				<p class="toolkit-footprint-lede">Our footprint records more than a decade of practical skills, employability, research and innovation programmes delivered with partners across Kenya.</p>
				<div class="toolkit-footprint-actions">
					<a class="toolkit-btn toolkit-btn--primary" href="#journey">Explore the journey <i class="fas fa-arrow-down" aria-hidden="true"></i></a>
					<a class="toolkit-footprint-text-link" href="#partners">See our partners <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
				</div>
			</div>
			<figure class="toolkit-footprint-hero__media">
				<img src="<?php echo esc_url( $assets . 'pages/impact.jpg' ); ?>" alt="Toolkit graduates and trainers gathered after a skills programme">
				<figcaption>Practical learning. Strong partnerships. New possibilities.</figcaption>
			</figure>
		</div>
		<div class="toolkit-footprint-record" aria-label="Source record at a glance">
			<p><span>Source record</span> The figures below describe documented programme entries, not participant totals.</p>
			<ul class="toolkit-footprint-stats">
				<li><strong>2014–2026</strong><span>Documented span</span></li>
				<li><strong><?php echo esc_html( $milestone_count ); ?></strong><span>Programme records</span></li>
				<li><strong><?php echo esc_html( count( $partners ) ); ?></strong><span>Named partners</span></li>
			</ul>
		</div>
	</section>

	<nav class="toolkit-footprint-nav" aria-label="Footprint page sections">
		<a href="#focus">What we have worked on</a>
		<a href="#journey">How the work developed</a>
		<a href="#partners">Partners</a>
	</nav>

	<section id="focus" class="toolkit-footprint-focus">
		<header class="toolkit-footprint-section-heading">
			<p class="toolkit-kicker">A connected body of work</p>
			<h2>Practical skills are only one part of the story.</h2>
			<p>The programme record shows three connected areas of contribution: direct training, stronger systems for skills recognition, and newer approaches to green and technology-enabled work.</p>
		</header>
		<div class="toolkit-footprint-focus__grid">
			<?php foreach ( $focus_areas as $index => $area ) : ?>
			<article class="toolkit-footprint-focus-card">
				<div class="toolkit-footprint-focus-card__media">
					<img src="<?php echo esc_url( $area['image'] ); ?>" alt="<?php echo esc_attr( $area['alt'] ); ?>" loading="lazy">
					<span aria-hidden="true">0<?php echo esc_html( $index + 1 ); ?></span>
				</div>
				<div>
					<h3><?php echo esc_html( $area['title'] ); ?></h3>
					<p><?php echo esc_html( $area['copy'] ); ?></p>
				</div>
			</article>
			<?php endforeach; ?>
		</div>
	</section>

	<section id="journey" class="toolkit-footprint-journey">
		<header class="toolkit-footprint-section-heading toolkit-footprint-section-heading--light">
			<p class="toolkit-kicker">How the work developed</p>
			<h2>The full programme record, made easier to follow.</h2>
			<p>Every entry below is retained from the supplied institutional footprint. Partner attribution appears alongside the work it supported.</p>
		</header>
		<div class="toolkit-footprint-chapters">
			<?php foreach ( $chapters as $chapter_index => $chapter ) : ?>
			<section id="<?php echo esc_attr( $chapter['id'] ); ?>" class="toolkit-footprint-chapter">
				<header class="toolkit-footprint-chapter__intro">
					<span class="toolkit-footprint-chapter__number" aria-hidden="true">0<?php echo esc_html( $chapter_index + 1 ); ?></span>
					<p><?php echo esc_html( $chapter['period'] ); ?></p>
					<h3><?php echo esc_html( $chapter['title'] ); ?></h3>
					<span><?php echo esc_html( $chapter['copy'] ); ?></span>
				</header>
				<div class="toolkit-footprint-chapter__records">
					<?php foreach ( $chapter['indexes'] as $era_index ) : $era = $eras[ $era_index ]; ?>
					<article class="toolkit-footprint-era">
						<h4><?php echo esc_html( $era['years'] ); ?></h4>
						<ul>
							<?php foreach ( $era['items'] as $item ) : ?>
							<li><strong><?php echo esc_html( $item[0] ); ?></strong><span><?php echo esc_html( $item[1] ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					</article>
					<?php endforeach; ?>
				</div>
			</section>
			<?php endforeach; ?>
		</div>
	</section>

	<section id="partners" class="toolkit-footprint-partners">
		<div>
			<p class="toolkit-kicker">Shared effort</p>
			<h2>Partners in the record</h2>
			<p>These organisations are named in the supplied 2014–2026 programme chronology.</p>
		</div>
		<ul>
			<?php foreach ( $partners as $partner ) : ?>
			<li><?php echo esc_html( $partner ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="toolkit-footprint-cta">
		<div>
			<p class="toolkit-kicker">Build what comes next</p>
			<h2>Turn practical skills into lasting opportunity.</h2>
			<p>Explore our current learning pathways or start a conversation about partnership.</p>
		</div>
		<div class="toolkit-footprint-cta__actions">
			<a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Explore courses <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
			<a class="toolkit-btn toolkit-btn--secondary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Talk to Toolkit</a>
		</div>
	</section>
</main>
<?php get_footer();
