<?php
require_once get_stylesheet_directory() . '/inc/course-catalog.php';
$slug   = sanitize_key( get_query_var( 'toolkit_course' ) );
$course = eduma_child_get_course( $slug );
if ( ! $course ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	include get_404_template();
	return;
}
$apply_url = home_url( '/our-ventures/toolkit-courses-apply-today/?course=' . rawurlencode( $course['title'] ) );
get_header();
?>
<main id="main-content" class="toolkit-page toolkit-course-page toolkit-catalog-course">
	<div class="toolkit-breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><a href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Our Courses</a><span>/</span><span><?php echo esc_html( $course['title'] ); ?></span></div>
	<section class="toolkit-course-hero" style="background-image:url('<?php echo esc_url( $course['image'] ); ?>')">
		<div class="toolkit-course-hero__content"><p class="toolkit-kicker">2026 admissions prospectus</p><h1><?php echo esc_html( $course['title'] ); ?></h1><p><?php echo esc_html( $course['short'] ); ?></p><div class="toolkit-actions"><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $apply_url ); ?>">Apply now</a><a class="toolkit-btn toolkit-btn--secondary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Ask admissions</a></div></div>
	</section>
	<section class="toolkit-section toolkit-course-data"><div><p class="toolkit-kicker">Course facts</p><h2>Plan your training</h2><p>These details are taken from the Toolkit Admissions Prospectus 2026. Admissions will confirm availability and eligibility before enrolment.</p></div><dl><div><dt>Qualification</dt><dd><?php echo esc_html( $course['qualification'] ); ?></dd></div><div><dt>Examining body</dt><dd><?php echo esc_html( $course['examining'] ); ?></dd></div><div><dt>Duration</dt><dd><?php echo esc_html( $course['duration'] ); ?></dd></div><div><dt>Entry requirement</dt><dd><?php echo esc_html( $course['entry'] ); ?></dd></div><div><dt>Fees</dt><dd><?php echo esc_html( $course['fees'] ); ?></dd></div><div><dt>Intakes</dt><dd><?php echo esc_html( $course['intakes'] ); ?></dd></div></dl></section>
	<section class="toolkit-section toolkit-course-outcomes"><div><p class="toolkit-kicker">Learning focus</p><h2>What this pathway develops</h2></div><div class="toolkit-skill-grid"><?php foreach ( $course['outcomes'] as $outcome ) : ?><article><i class="fas fa-check-circle"></i><h3><?php echo esc_html( $outcome ); ?></h3></article><?php endforeach; ?></div></section>
	<section class="toolkit-support"><i class="fas fa-headset"></i><div><h2>Ready to take the next step?</h2><p>Start your application or ask admissions to confirm the right level, intake, and funding options.</p></div><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $apply_url ); ?>">Apply now <i class="fas fa-arrow-right"></i></a></section>
</main>
<?php get_footer();
