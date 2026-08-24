<?php
require_once get_stylesheet_directory() . '/inc/course-catalog.php';
$slug   = sanitize_key( get_query_var( 'toolkit_course' ) );
$course = $slug ? eduma_child_get_course( $slug ) : eduma_child_get_legacy_course_for_page();
if ( ! $course ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	include get_404_template();
	return;
}
$apply_url  = home_url( '/apply/?course=' . rawurlencode( $course['title'] ) );
$kicker     = $slug ? '2026 admissions prospectus' : 'Toolkit course pathway';
$course_key = $slug ? $slug : eduma_child_get_legacy_course_key_for_page();
$experience = eduma_child_course_experience( $course_key );
get_header();
?>
<main id="main-content" class="toolkit-page toolkit-course-page toolkit-catalog-course toolkit-course--<?php echo esc_attr( $experience['theme'] ); ?>">
	<div class="toolkit-breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><a href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Our Courses</a><span>/</span><span><?php echo esc_html( $course['title'] ); ?></span></div>
	<section class="toolkit-course-hero toolkit-course-hero--immersive" style="background-image:url('<?php echo esc_url( $course['image'] ); ?>')">
		<div class="toolkit-course-hero__content"><p class="toolkit-kicker"><?php echo esc_html( $kicker ); ?></p><h1><?php echo esc_html( $course['title'] ); ?></h1><p><?php echo esc_html( $course['short'] ); ?></p><div class="toolkit-actions"><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $apply_url ); ?>">Apply now</a><a class="toolkit-btn toolkit-btn--secondary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Ask admissions</a></div></div>
		<div class="toolkit-course-hero__rail" aria-label="Learning approach"><span><i class="fas fa-hands-helping"></i> Guided practice</span><span><i class="fas fa-tools"></i> Applied learning</span><span><i class="fas fa-route"></i> Progression support</span></div>
	</section>
	<section class="toolkit-course-scene"><div class="toolkit-course-scene__copy"><p class="toolkit-kicker"><?php echo esc_html( $experience['eyebrow'] ); ?></p><h2><?php echo esc_html( $experience['heading'] ); ?></h2><p><?php echo esc_html( $experience['intro'] ); ?></p><div class="toolkit-course-scene__moments"><?php foreach ( $experience['moments'] as $moment ) : ?><article><i class="fas <?php echo esc_attr( $moment[0] ); ?>"></i><div><h3><?php echo esc_html( $moment[1] ); ?></h3><p><?php echo esc_html( $moment[2] ); ?></p></div></article><?php endforeach; ?></div></div><?php if ( $experience['secondary'] ) : ?><figure><img src="<?php echo esc_url( $experience['secondary'] ); ?>" width="900" height="1100" loading="lazy" alt="<?php echo esc_attr( $experience['secondary_alt'] ); ?>"><figcaption><?php echo esc_html( $experience['eyebrow'] ); ?></figcaption></figure><?php endif; ?></section>
	<section class="toolkit-section toolkit-course-data"><div><p class="toolkit-kicker">Course facts</p><h2>Plan your training</h2><p>Admissions will confirm programme availability, eligibility, schedules, and current costs before enrolment.</p></div><dl><div><dt>Qualification</dt><dd><?php echo esc_html( $course['qualification'] ); ?></dd></div><div><dt>Examining body</dt><dd><?php echo esc_html( $course['examining'] ); ?></dd></div><div><dt>Duration</dt><dd><?php echo esc_html( $course['duration'] ); ?></dd></div><div><dt>Entry requirement</dt><dd><?php echo esc_html( $course['entry'] ); ?></dd></div><?php if ( $slug && eduma_child_2026_pricing_enabled() ) : ?><div><dt>2026 fees</dt><dd><?php echo esc_html( $course['fees'] ); ?><?php if ( ! empty( $course['pricing_note'] ) ) : ?><small><?php echo esc_html( $course['pricing_note'] ); ?></small><?php endif; ?></dd></div><?php else : ?><div><dt>Fees</dt><dd>Contact admissions for current fees and available funding options.</dd></div><?php endif; ?><div><dt>Intakes</dt><dd><?php echo esc_html( $course['intakes'] ); ?></dd></div></dl></section>
	<section class="toolkit-section toolkit-course-outcomes"><div><p class="toolkit-kicker">Capability map</p><h2>What you build along the way</h2></div><div class="toolkit-skill-grid"><?php foreach ( $course['outcomes'] as $index => $outcome ) : ?><article><span>0<?php echo esc_html( $index + 1 ); ?></span><i class="fas fa-check-circle"></i><h3><?php echo esc_html( $outcome ); ?></h3></article><?php endforeach; ?></div></section>
	<section class="toolkit-section toolkit-course-faq"><div><p class="toolkit-kicker">Course questions</p><h2>Before you apply</h2></div><div><details><summary>Where is this course offered?</summary><p>The Toolkit for Skills and Innovation delivers practical training on the Karen-Kikuyu Southern Bypass in Kikuyu, Kenya. Admissions will confirm the delivery location for your intake.</p></details><details><summary>How do I confirm current fees and intake dates?</summary><p>Contact Toolkit Admissions before enrolment. The team will confirm current fees, funding options, schedules, availability, and any assessment costs for this course.</p></details><details><summary>How do I apply?</summary><p>Use the Apply now link on this page to begin the application process. Admissions can help you confirm the suitable course level and required documents.</p></details></div></section>
	<section class="toolkit-support"><i class="fas fa-headset"></i><div><h2>Ready to take the next step?</h2><p>Start your application or ask admissions to confirm the right level, intake, and funding options.</p></div><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $apply_url ); ?>">Apply now <i class="fas fa-arrow-right"></i></a></section>
</main>
<?php get_footer();
