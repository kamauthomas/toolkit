<?php
get_header();
$assets = get_stylesheet_directory_uri() . '/assets/images/pages/';
$excluded_posts = array();
foreach ( get_posts( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => -1 ) ) as $candidate ) {
	if ( '__trashed' === $candidate->post_name || preg_match( '/\bcopy\b/i', $candidate->post_title ) ) {
		$excluded_posts[] = $candidate->ID;
	}
}
$posts  = new WP_Query( array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 12,
	'ignore_sticky_posts' => true,
	'post__not_in'        => $excluded_posts,
) );
$images = array( 'impact.jpg', 'about.jpg', 'foundation.jpg', 'notice-board.jpg' );
$story_image = function ( $post_id, $fallback_index = 0 ) use ( $assets, $images ) {
	$cultural = toolkit_cultural_week_story_for_slug( get_post_field( 'post_name', $post_id ) );
	if ( $cultural ) {
		return $cultural['image'];
	}
	$thumbnail_id   = get_post_thumbnail_id( $post_id );
	$thumbnail_file = $thumbnail_id ? get_attached_file( $thumbnail_id ) : '';
	if ( $thumbnail_file && file_exists( $thumbnail_file ) ) {
		return get_the_post_thumbnail_url( $post_id, 'large' );
	}
	return $assets . $images[ $fallback_index % count( $images ) ];
};
?>
<main id="main-content" class="toolkit-page toolkit-blog-page">
	<section class="toolkit-blog-hero">
		<div><p class="toolkit-kicker">Toolkit Blog</p><h1>Skills, ideas and stories from the field</h1><p>Read updates on practical learning, youth employment, industry partnerships and the people shaping new pathways to work.</p></div>
		<img src="<?php echo esc_url( $assets . 'impact.jpg' ); ?>" width="900" height="620" alt="Toolkit learner taking part in practical technical training">
	</section>
	<?php if ( $posts->have_posts() ) : $posts->the_post(); ?>
	<section class="toolkit-blog-feature toolkit-section">
		<a href="<?php the_permalink(); ?>"><img src="<?php echo esc_url( $story_image( get_the_ID() ) ); ?>" width="780" height="520" alt="<?php echo esc_attr( get_the_title() ); ?>"></a>
		<div><p class="toolkit-kicker">Latest story</p><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 34 ) ); ?></p><a class="toolkit-btn toolkit-btn--primary" href="<?php the_permalink(); ?>">Read the story <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
	</section>
	<section class="toolkit-blog-feed toolkit-section" aria-labelledby="toolkit-blog-feed-title">
		<div class="toolkit-blog-feed__heading"><div><p class="toolkit-kicker">More from Toolkit</p><h2 id="toolkit-blog-feed-title">Recent stories and updates</h2></div><p>Programme news, conversations and practical insight from Toolkit teams and partners.</p></div>
		<div class="toolkit-blog-grid"><?php $index = 1; while ( $posts->have_posts() ) : $posts->the_post(); ?><article><a class="toolkit-blog-card__image" href="<?php the_permalink(); ?>"><img src="<?php echo esc_url( $story_image( get_the_ID(), $index ) ); ?>" width="620" height="380" loading="lazy" alt="<?php echo esc_attr( get_the_title() ); ?>"></a><div><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p><a href="<?php the_permalink(); ?>">Read story <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div></article><?php $index++; endwhile; ?></div>
	</section>
	<?php else : ?>
	<section class="toolkit-blog-empty toolkit-section"><p class="toolkit-kicker">New stories coming soon</p><h2>Toolkit updates are being prepared</h2><p>For current programme and admissions information, visit the Notice Board or speak with the Toolkit team.</p><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( home_url( '/notice-board/' ) ); ?>">Visit Notice Board</a></section>
	<?php endif; wp_reset_postdata(); ?>
	<section class="toolkit-institutional-cta"><div><h2>Looking for admissions information?</h2><p>See current notices and use the guided application route.</p></div><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( home_url( '/our-ventures/toolkit-courses-apply-today/' ) ); ?>">Apply now <i class="fas fa-arrow-right" aria-hidden="true"></i></a></section>
</main>
<?php get_footer();
