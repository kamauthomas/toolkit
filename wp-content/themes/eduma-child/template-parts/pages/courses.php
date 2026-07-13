<?php
get_header();
$uploads   = wp_get_upload_dir()['baseurl'];
$apply_url = home_url( '/our-ventures/toolkit-courses-apply-today/' );
$courses   = array(
	array( 'title' => 'Welding and Fabrication', 'description' => 'Develop practical workshop skills with industry-aware training and modern learning tools.', 'url' => home_url( '/our-ventures/construction-sector-skills/' ), 'image' => $uploads . '/2025/05/WELDING-6.jpg', 'icon' => 'fa-fire' ),
	array( 'title' => 'Renewable Energy', 'description' => 'Build the technical foundations for emerging green-skills opportunities.', 'url' => home_url( '/our-ventures/renewable-energy/' ), 'image' => $uploads . '/2025/05/solar-2-website.jpg', 'icon' => 'fa-solar-panel' ),
	array( 'title' => 'Organic Farming Skills', 'description' => 'Learn practical, sustainable approaches to agriculture and enterprise.', 'url' => home_url( '/our-ventures/organic-farming-skills/' ), 'image' => $uploads . '/2025/05/DAV8986-scaled.jpg', 'icon' => 'fa-seedling' ),
	array( 'title' => 'Digital Skills and Online Jobs', 'description' => 'Strengthen digital capability for changing work and entrepreneurship.', 'url' => home_url( '/access-online-jobs/' ), 'image' => $uploads . '/2025/05/DAV4119-scaled.jpg', 'icon' => 'fa-laptop-code' ),
	array( 'title' => 'Recognition of Prior Learning', 'description' => 'Turn existing skills and experience into recognised progression pathways.', 'url' => home_url( '/recognition-of-prior-learning-rpl/' ), 'image' => $uploads . '/2025/05/TOOLKIT-scaled.jpg', 'icon' => 'fa-award' ),
	array( 'title' => 'Consultancy and Research', 'description' => 'Access practical support for workforce, training, and research needs.', 'url' => home_url( '/our-ventures/tti-consultancy-and-research/' ), 'image' => $uploads . '/2025/05/DAV8986-scaled.jpg', 'icon' => 'fa-chart-line' ),
);
?>
<main id="main-content" class="toolkit-page toolkit-courses-page">
	<section class="toolkit-courses-hero" style="background-image:url('<?php echo esc_url( $uploads . '/2025/05/TOOLKIT-scaled.jpg' ); ?>')"><div><p class="toolkit-kicker">Skills for opportunity</p><h1>Our courses</h1><p>Choose practical learning pathways designed to help young people build confidence, capability, and meaningful work opportunities.</p><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $apply_url ); ?>">Start your application <i class="fas fa-arrow-right"></i></a></div></section>
	<section class="toolkit-section toolkit-course-directory"><div class="toolkit-directory-heading"><div><p class="toolkit-kicker">Explore pathways</p><h2>Learn by doing.</h2></div><p>Each pathway connects relevant technical knowledge with practical experience and guidance from the Toolkit team.</p></div><div class="toolkit-course-directory__grid"><?php foreach ( $courses as $course ) : ?><article><div class="toolkit-course-directory__image" style="background-image:url('<?php echo esc_url( $course['image'] ); ?>')"><i class="fas <?php echo esc_attr( $course['icon'] ); ?>"></i></div><div><h2><?php echo esc_html( $course['title'] ); ?></h2><p><?php echo esc_html( $course['description'] ); ?></p><a href="<?php echo esc_url( $course['url'] ); ?>">Explore course <i class="fas fa-arrow-right"></i></a></div></article><?php endforeach; ?></div></section>
	<section class="toolkit-support"><i class="fas fa-comments"></i><div><h2>Not sure which course fits your goals?</h2><p>Speak with admissions for guidance on pathways, entry requirements, and current intakes.</p></div><a class="toolkit-btn toolkit-btn--secondary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Talk to admissions <i class="fas fa-arrow-right"></i></a></section>
</main>
<?php get_footer();
