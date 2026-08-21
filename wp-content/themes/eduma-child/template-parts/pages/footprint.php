<?php
/**
 * Toolkit footprint in youth skills and employability.
 * A faithful, responsive interpretation of the supplied institutional poster.
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

/* The supplied poster uses seven vertical columns, with overlapping programme
   periods nested below their main year. Keeping that grouping makes the web
   version immediately recognisable while the mobile layout can stack cleanly. */
$poster_columns = array(
	array( 0 ),
	array( 1, 2, 3 ),
	array( 4, 5 ),
	array( 6 ),
	array( 7, 8, 9 ),
	array( 10, 11 ),
	array( 12 ),
);
?>
<main id="main-content" class="toolkit-page toolkit-footprint-page">
	<header class="toolkit-footprint-title">
		<p>The Toolkit for Skills and Innovation</p>
		<h1>Toolkit footprint in youth skills and employability</h1>
		<div class="toolkit-footprint-rule" aria-hidden="true"><span></span></div>
	</header>

	<section class="toolkit-footprint-poster" aria-label="Toolkit programme footprint from 2014 to 2026">
		<ol class="toolkit-footprint-columns">
			<?php foreach ( $poster_columns as $column_index => $era_indexes ) : ?>
			<li class="toolkit-footprint-column" style="--footprint-order:<?php echo esc_attr( $column_index ); ?>">
				<span class="toolkit-footprint-dot" aria-hidden="true"></span>
				<div class="toolkit-footprint-column__line">
					<?php foreach ( $era_indexes as $era_index ) : $era = $eras[ $era_index ]; ?>
					<article class="toolkit-footprint-era">
						<h2><?php echo esc_html( $era['years'] ); ?></h2>
						<ul>
							<?php foreach ( $era['items'] as $item ) : ?>
							<li>
								<strong><?php echo esc_html( $item[0] ); ?></strong>
								<span><?php echo esc_html( $item[1] ); ?></span>
							</li>
							<?php endforeach; ?>
						</ul>
					</article>
					<?php endforeach; ?>
				</div>
				<span class="toolkit-footprint-step" aria-hidden="true">
					<svg viewBox="0 0 64 42" role="presentation" focusable="false"><ellipse cx="36" cy="25" rx="18" ry="12" transform="rotate(8 36 25)"/><ellipse cx="13" cy="27" rx="8" ry="5" transform="rotate(28 13 27)"/><circle cx="12" cy="16" r="4"/><circle cx="19" cy="10" r="3.6"/><circle cx="27" cy="7" r="3.2"/><circle cx="35" cy="7" r="2.8"/></svg>
				</span>
			</li>
			<?php endforeach; ?>
		</ol>
	</section>

	<p class="toolkit-footprint-source">Programme chronology supplied by The Toolkit for Skills and Innovation.</p>
</main>
<?php get_footer();
