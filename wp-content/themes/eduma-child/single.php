<?php
/**
 * Modern editorial layout for individual Toolkit stories.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id       = get_the_ID();
	$blog_url      = home_url( '/toolkit-blog/' );
	$fallback      = get_stylesheet_directory_uri() . '/assets/images/pages/impact.jpg';
	$thumbnail_id  = get_post_thumbnail_id( $post_id );
	$thumbnail_file = $thumbnail_id ? get_attached_file( $thumbnail_id ) : '';
	$hero_image    = $thumbnail_file && file_exists( $thumbnail_file ) ? get_the_post_thumbnail_url( $post_id, 'full' ) : $fallback;
	$categories    = get_the_category();
	$category_name = $categories ? $categories[0]->name : 'Toolkit story';
	$reading_words = str_word_count( wp_strip_all_tags( get_the_content() ) );
	$reading_time  = max( 1, (int) ceil( $reading_words / 220 ) );
	$related       = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => 3,
			'post__not_in'        => array( $post_id ),
			'ignore_sticky_posts' => true,
			'category__in'        => wp_get_post_categories( $post_id ),
		)
	);
	?>
	<main id="main-content" class="toolkit-page toolkit-story-page">
		<article <?php post_class( 'toolkit-story' ); ?>>
			<header class="toolkit-story-hero">
				<div class="toolkit-story-hero__copy">
					<a class="toolkit-story-back" href="<?php echo esc_url( $blog_url ); ?>"><i class="fas fa-arrow-left" aria-hidden="true"></i> Toolkit Blog</a>
					<p class="toolkit-kicker"><?php echo esc_html( $category_name ); ?></p>
					<h1><?php the_title(); ?></h1>
					<div class="toolkit-story-meta">
						<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<span><?php echo esc_html( $reading_time ); ?> min read</span>
					</div>
				</div>
				<figure><img src="<?php echo esc_url( $hero_image ); ?>" width="1400" height="900" alt="<?php echo esc_attr( get_the_title() ); ?>"></figure>
			</header>

			<div class="toolkit-story-layout">
				<aside class="toolkit-story-share" aria-label="Share this story">
					<span>Share</span>
					<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo rawurlencode( get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Share on LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
					<a href="https://wa.me/?text=<?php echo rawurlencode( get_the_title() . ' ' . get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
					<a href="mailto:?subject=<?php echo rawurlencode( get_the_title() ); ?>&amp;body=<?php echo rawurlencode( get_permalink() ); ?>" aria-label="Share by email"><i class="far fa-envelope" aria-hidden="true"></i></a>
				</aside>
				<div class="toolkit-story-content">
					<?php the_content(); ?>
					<?php wp_link_pages( array( 'before' => '<nav class="toolkit-story-pages" aria-label="Story pages">', 'after' => '</nav>' ) ); ?>
				</div>
			</div>

			<nav class="toolkit-story-nav" aria-label="More Toolkit stories">
				<div><?php previous_post_link( '%link', '<span>Previous story</span><strong>%title</strong>' ); ?></div>
				<div><?php next_post_link( '%link', '<span>Next story</span><strong>%title</strong>' ); ?></div>
			</nav>
		</article>

		<?php if ( $related->have_posts() ) : ?>
			<section class="toolkit-story-related toolkit-section" aria-labelledby="toolkit-related-title">
				<div class="toolkit-story-related__heading"><div><p class="toolkit-kicker">Continue reading</p><h2 id="toolkit-related-title">More from Toolkit</h2></div><a href="<?php echo esc_url( $blog_url ); ?>">View all stories <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
				<div class="toolkit-story-related__grid">
					<?php while ( $related->have_posts() ) : $related->the_post(); ?>
						<?php
						$related_thumbnail = get_post_thumbnail_id();
						$related_file      = $related_thumbnail ? get_attached_file( $related_thumbnail ) : '';
						$related_image     = $related_file && file_exists( $related_file ) ? get_the_post_thumbnail_url( get_the_ID(), 'large' ) : $fallback;
						?>
						<article><a href="<?php the_permalink(); ?>"><img src="<?php echo esc_url( $related_image ); ?>" width="620" height="380" loading="lazy" alt="<?php echo esc_attr( get_the_title() ); ?>"></a><div><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3></div></article>
					<?php endwhile; ?>
				</div>
			</section>
		<?php endif; wp_reset_postdata(); ?>

		<section class="toolkit-institutional-cta"><div><h2>Turn insight into action</h2><p>Explore practical courses and current opportunities at The Toolkit for Skills and Innovation.</p></div><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Explore courses <i class="fas fa-arrow-right" aria-hidden="true"></i></a></section>
	</main>
	<?php
endwhile;

get_footer();
