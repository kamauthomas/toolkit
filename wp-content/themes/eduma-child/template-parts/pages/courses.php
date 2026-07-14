<?php
require_once get_stylesheet_directory() . '/inc/course-catalog.php';
get_header();
$catalog   = eduma_child_course_catalog();
$apply_url = home_url( '/our-ventures/toolkit-courses-apply-today/' );
$hero      = get_stylesheet_directory_uri() . '/assets/images/courses/electrical.jpg';
?>
<main id="main-content" class="toolkit-page toolkit-courses-page">
	<section class="toolkit-courses-hero" style="background-image:url('<?php echo esc_url( $hero ); ?>')"><div><p class="toolkit-kicker">2026 course catalog</p><h1>Practical pathways for work and enterprise</h1><p>Explore the programmes documented in Toolkit's 2026 admissions prospectus, with clear entry requirements, duration, fees, and intake information.</p><div class="toolkit-actions"><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $apply_url ); ?>">Apply now <i class="fas fa-arrow-right"></i></a><a class="toolkit-btn toolkit-btn--secondary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Talk to admissions</a></div></div></section>
	<section class="toolkit-section toolkit-course-directory"><div class="toolkit-directory-heading"><div><p class="toolkit-kicker">Choose a pathway</p><h2>Courses currently in scope</h2></div><p>Course information below follows the official 2026 prospectus. Programme availability is confirmed during admission.</p></div><div class="toolkit-course-directory__grid"><?php foreach ( $catalog as $slug => $course ) : ?><article><div class="toolkit-course-directory__image" style="background-image:url('<?php echo esc_url( $course['image'] ); ?>')"><i class="fas <?php echo esc_attr( $course['icon'] ); ?>"></i></div><div><h2><?php echo esc_html( $course['title'] ); ?></h2><p><?php echo esc_html( $course['short'] ); ?></p><ul><li><strong>Duration:</strong> <?php echo esc_html( $course['duration'] ); ?></li><li><strong>Intakes:</strong> <?php echo esc_html( $course['intakes'] ); ?></li></ul><a href="<?php echo esc_url( home_url( '/courses/' . $slug . '/' ) ); ?>">View course details <i class="fas fa-arrow-right"></i></a></div></article><?php endforeach; ?></div></section>
	<section class="toolkit-support"><i class="fas fa-comments"></i><div><h2>Not sure which pathway fits?</h2><p>Admissions can help compare entry requirements, schedules, fees, and funding options.</p></div><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $apply_url ); ?>">Apply now <i class="fas fa-arrow-right"></i></a></section>
</main>
<?php get_footer();
