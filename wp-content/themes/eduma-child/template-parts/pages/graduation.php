<?php
/**
 * Evidence-led graduation and achievement page.
 * Ceremony date remains deliberately unconfirmed until an approved date is supplied.
 */
get_header();
$base = get_stylesheet_directory_uri() . '/assets/images/graduation/';
$gallery = array(
	array( 'kmm-2071-jpg.webp', 'Toolkit graduates sharing a joyful moment during a graduation ceremony' ),
	array( 'kmm-2146-jpg.webp', 'Graduates celebrating after completing their training journey' ),
	array( 'kmm-2218-jpg.webp', 'Toolkit graduates gathered in academic regalia during the ceremony' ),
	array( 'kmm-2122-jpg.webp', 'A Toolkit trainee walking towards the graduation celebration' ),
	array( 'kmm-2134-jpg.webp', 'A Toolkit graduate celebrating an important learning milestone' ),
	array( 'kmm-2136-jpg.webp', 'Graduate achievement and community pride at Toolkit' ),
	array( 'kmm-2138-jpg.webp', 'Toolkit graduates marking the completion of a learning pathway' ),
	array( 'kmm-2143-jpg.webp', 'A graduation moment shared by learners and the Toolkit community' ),
	array( 'kmm-2226-jpg.webp', 'Graduates in regalia celebrating progress and new possibilities' ),
	array( 'kmm-2332-jpg.webp', 'A formal graduation address during the Toolkit celebration' ),
	array( 'deborah-img-20251028-wa0103-jpg.webp', 'Graduate portrait from the Toolkit graduation collection' ),
	array( 'jesse-kihanya-img-20251028-wa0094-jpg.webp', 'Toolkit graduate portrait celebrating personal achievement' ),
	array( 'img-20251028-wa0048-jpg.webp', 'Graduate photographed in regalia after the Toolkit ceremony' ),
	array( 'img-20251028-wa0066-jpg.webp', 'Toolkit graduate marking the completion of a training journey' ),
);
?>
<main id="main-content" class="toolkit-page toolkit-graduation-page">
	<section class="graduation-hero">
		<div class="graduation-hero__copy"><p class="toolkit-kicker">Achievement made visible</p><h1>Graduation at Toolkit</h1><p>Graduation celebrates more than a ceremony. It recognises the discipline, practical learning and personal growth behind every completed training journey.</p><div class="graduation-hero__actions"><a class="toolkit-btn toolkit-btn--primary" href="#graduation-gallery">Explore the gallery</a><a class="toolkit-btn toolkit-btn--secondary" href="<?php echo esc_url( home_url( '/testimonials/' ) ); ?>">Hear graduate voices</a></div></div>
		<figure><img src="<?php echo esc_url( $base . 'kmm-2071-jpg.webp' ); ?>" width="1800" height="1153" alt="Toolkit graduates sharing a joyful moment during a graduation ceremony"><figcaption><strong>A shared milestone</strong><span>Skills • Achievement • Progression</span></figcaption></figure>
	</section>

	<section class="graduation-date toolkit-section" aria-labelledby="graduation-date-title"><span aria-hidden="true"><i class="fas fa-calendar-check"></i></span><div><p class="toolkit-kicker">Next ceremony</p><h2 id="graduation-date-title">Graduation date announcement pending</h2><p>The confirmed date, programme and attendance guidance will be published here and on the <a href="<?php echo esc_url( home_url( '/notice-board/' ) ); ?>">Toolkit Notice Board</a> after institutional approval.</p></div><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact Toolkit <i class="fas fa-arrow-right" aria-hidden="true"></i></a></section>

	<section class="graduation-meaning toolkit-section" aria-labelledby="graduation-meaning-title"><div><p class="toolkit-kicker">A record of progress</p><h2 id="graduation-meaning-title">Where practical learning becomes achievement</h2><p>Each graduation moment represents hours of guided practice, assessment preparation, problem-solving and persistence. The ceremony brings learners, trainers, families and partners together to recognise that work.</p><p>Toolkit’s graduation story spans different pathways and learner journeys. The photographs below preserve the scale of the celebration while keeping the focus on the people whose effort made it possible.</p></div><ol><li><span>01</span><div><h3>Skills built</h3><p>Practical competence developed through workshop-led and applied learning.</p></div></li><li><span>02</span><div><h3>Progress recognised</h3><p>Completion and achievement acknowledged within the applicable learning pathway.</p></div></li><li><span>03</span><div><h3>Next steps opened</h3><p>Graduates prepare to pursue work, enterprise, further learning or skills recognition.</p></div></li></ol></section>

	<section id="graduation-gallery" class="graduation-gallery toolkit-section" aria-labelledby="graduation-gallery-title"><header><div><p class="toolkit-kicker">Graduation gallery</p><h2 id="graduation-gallery-title">Pride in every frame</h2></div><p>A visual archive of collective celebration and individual achievement at The Toolkit for Skills and Innovation.</p></header><div class="graduation-gallery__grid">
		<?php foreach ( $gallery as $index => $item ) : ?><figure class="graduation-photo graduation-photo--<?php echo esc_attr( ( $index % 7 ) + 1 ); ?>"><button type="button" data-gallery-image="<?php echo esc_url( $base . $item[0] ); ?>" data-gallery-alt="<?php echo esc_attr( $item[1] ); ?>"><img src="<?php echo esc_url( $base . $item[0] ); ?>" width="1800" height="1200" loading="<?php echo $index < 3 ? 'eager' : 'lazy'; ?>" alt="<?php echo esc_attr( $item[1] ); ?>"><span>View photograph <i class="fas fa-expand-alt" aria-hidden="true"></i></span></button><figcaption><?php echo esc_html( 'Graduation archive / ' . str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></figcaption></figure><?php endforeach; ?>
	</div></section>

	<section class="graduation-outcomes toolkit-section"><div><p class="toolkit-kicker">Beyond the ceremony</p><h2>Achievement continues after graduation</h2><p>A certificate or ceremony is one milestone in a longer progression story. Toolkit continues to connect practical learning with workplace confidence, credible pathways and the decisions graduates make next.</p></div><div class="graduation-outcomes__links"><a href="<?php echo esc_url( home_url( '/testimonials/' ) ); ?>"><strong>Graduate testimonials</strong><span>Hear directly from attributable learner voices.</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a><a href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>"><strong>Training pathways</strong><span>Explore the practical courses behind new milestones.</span><i class="fas fa-arrow-right" aria-hidden="true"></i></a></div></section>
	<dialog class="memory-lightbox graduation-lightbox" data-gallery-dialog aria-label="Expanded graduation photograph"><button type="button" data-gallery-close aria-label="Close photograph">×</button><img src="" alt=""><p></p></dialog>
</main>
<?php get_footer(); ?>
