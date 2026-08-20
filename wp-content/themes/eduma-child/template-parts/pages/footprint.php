<?php
/**
 * Toolkit footprint in youth skills and employability.
 * A dated record of programmes delivered with named partners, 2014-2026.
 * Each era is one stride along the trail; partners are attributed per milestone.
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
?>
<main id="main-content" class="toolkit-page toolkit-footprint-page">
	<section class="toolkit-footprint-hero">
		<div>
			<p class="toolkit-kicker">The Toolkit for Skills and Innovation</p>
			<h1>Our footprint in youth skills and employability</h1>
			<p>A decade of walking alongside young Kenyans — from the first painters and plumbers trained in 2014 to a Skills and Innovation Hub teaching welding in virtual reality. Every step was taken with a partner.</p>
			<ul class="toolkit-footprint-stats">
				<li><strong>2014</strong><span>Where we started</span></li>
				<li><strong><?php echo esc_html( count( $partners ) ); ?></strong><span>Named partners</span></li>
				<li><strong><?php echo esc_html( count( $eras ) ); ?></strong><span>Programme eras</span></li>
				<li><strong><?php echo esc_html( $milestone_count ); ?></strong><span>Recorded milestones</span></li>
			</ul>
		</div>
	</section>

	<section class="toolkit-footprint-trail" aria-label="Toolkit milestones, 2014 to 2026">
		<ol>
			<?php foreach ( $eras as $index => $era ) : ?>
			<li class="toolkit-footprint-era<?php echo 0 === $index % 2 ? '' : ' is-alt'; ?>">
				<span class="toolkit-footprint-step" aria-hidden="true">
					<svg viewBox="0 0 40 54" role="presentation" focusable="false"><ellipse cx="21" cy="34" rx="13" ry="17"/><ellipse cx="9" cy="20" rx="6.5" ry="9" transform="rotate(-16 9 20)"/><circle cx="16" cy="7" r="3.4"/><circle cx="23.5" cy="4.5" r="3"/><circle cx="30" cy="5.5" r="2.6"/><circle cx="35" cy="9" r="2.2"/></svg>
				</span>
				<article>
					<h2><?php echo esc_html( $era['years'] ); ?></h2>
					<dl>
						<?php foreach ( $era['items'] as $item ) : ?>
						<div><dt><?php echo esc_html( $item[0] ); ?></dt><dd><?php echo esc_html( $item[1] ); ?></dd></div>
						<?php endforeach; ?>
					</dl>
				</article>
			</li>
			<?php endforeach; ?>
		</ol>
	</section>

	<section class="toolkit-footprint-partners">
		<h2>Partners who walked with us</h2>
		<ul>
			<?php foreach ( $partners as $partner ) : ?>
			<li><?php echo esc_html( $partner ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="toolkit-institutional-cta">
		<div>
			<h2>Walk the next stretch with us</h2>
			<p>Explore current practical learning pathways, or talk to Toolkit about partnership.</p>
		</div>
		<a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Explore courses <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
	</section>
</main>
<?php get_footer();
