<?php
defined( 'ABSPATH' ) || exit;
$graduands = toolkit_graduands_published();
$courses = $graduands ? array_unique( array_column( $graduands['rows'], 'course' ) ) : array();
?>
<section class="graduands toolkit-section" id="graduands" aria-labelledby="graduands-title" data-graduands>
	<header class="graduands__heading"><div><p class="toolkit-kicker">Class of 2026</p><h2 id="graduands-title">Graduands list</h2><p>Celebrating our graduands and their training pathways.</p></div><?php if ( $graduands ) : ?><button class="toolkit-btn toolkit-btn--secondary" type="button" data-graduands-print hidden>Print / save as PDF</button><?php endif; ?></header>
	<?php if ( ! $graduands ) : ?>
	<div class="graduands__pending"><strong>The confirmed list is being prepared.</strong><p>Please check back for the approved names and courses. This QR link will open the latest published list.</p></div>
	<?php else : ?>
	<p class="graduands__updated"><?php echo esc_html( count( $graduands['rows'] ) ); ?> graduands · Updated <?php echo esc_html( wp_date( 'j F Y', strtotime( $graduands['published_at'] ) ) ); ?></p>
	<div class="graduands__filters" data-graduands-filters hidden><label for="graduand-search">Find a name<input id="graduand-search" type="search" autocomplete="off" placeholder="Type a name" data-graduands-search></label><label for="graduand-course">Course<select id="graduand-course" data-graduands-course><option value="">All courses</option><?php foreach ( $courses as $course ) : ?><option value="<?php echo esc_attr( $course ); ?>"><?php echo esc_html( $course ); ?></option><?php endforeach; ?></select></label><button type="button" data-graduands-reset>Clear filters</button></div>
	<p class="graduands__count" data-graduands-count role="status" aria-live="polite"><?php echo esc_html( count( $graduands['rows'] ) ); ?> names</p>
	<div class="graduands__table"><table><caption class="screen-reader-text">Approved Toolkit Class of 2026 graduands and courses</caption><thead><tr><th scope="col">Name</th><th scope="col">Course</th></tr></thead><tbody><?php foreach ( $graduands['rows'] as $row ) : ?><tr data-graduand data-name="<?php echo esc_attr( $row['display_name'] ); ?>" data-course="<?php echo esc_attr( $row['course'] ); ?>"><td><?php echo esc_html( $row['display_name'] ); ?></td><td><?php echo esc_html( $row['course'] ); ?></td></tr><?php endforeach; ?></tbody></table></div>
	<p class="graduands__empty" data-graduands-empty hidden>No names match these filters. Try part of the name or choose another course.</p>
	<p class="graduands__help">For a name or course correction, please <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">contact Toolkit</a>.</p>
	<?php endif; ?>
</section>
