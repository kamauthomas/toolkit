<?php
/**
 * Modern editorial layout for individual Toolkit stories.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id       = get_the_ID();
	$preview       = toolkit_editorial_story_preview();
	$blog_url      = home_url( '/toolkit-blog/' );
	$fallback      = get_stylesheet_directory_uri() . '/assets/images/pages/impact.jpg';
	$thumbnail_id  = get_post_thumbnail_id( $post_id );
	$thumbnail_file = $thumbnail_id ? get_attached_file( $thumbnail_id ) : '';
	$hero_image    = $thumbnail_file && file_exists( $thumbnail_file ) ? get_the_post_thumbnail_url( $post_id, 'full' ) : $fallback;
	$categories    = get_the_category();
	$category_name = $preview ? $preview['label'] : ( $categories ? $categories[0]->name : 'Toolkit story' );
	$story_title   = $preview ? $preview['title'] : get_the_title();
	$reading_words = str_word_count( $preview ? implode( ' ', $preview['content'] ) : wp_strip_all_tags( get_the_content() ) );
	$reading_time  = max( 1, (int) ceil( $reading_words / 220 ) );
	$story_number  = str_pad( (string) ( absint( $post_id ) % 100 ), 2, '0', STR_PAD_LEFT );
	$standfirst    = $preview ? $preview['standfirst'] : get_the_excerpt();
	if ( ! $standfirst ) {
		$standfirst = wp_trim_words( wp_strip_all_tags( get_the_content() ), 30 );
	}
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
	<?php if ( $preview ) : $hero_image = $preview['images'][0]; endif; ?>
	<main id="main-content" class="toolkit-page toolkit-story-page <?php echo $preview ? esc_attr( 'toolkit-story-theme--' . $preview['theme'] ) : ''; ?>">
		<article <?php post_class( 'toolkit-story' ); ?>>
			<header class="toolkit-story-hero toolkit-story-cover">
				<div class="toolkit-story-hero__copy">
					<div class="toolkit-story-cover__topline">
						<a class="toolkit-story-back" href="<?php echo esc_url( $blog_url ); ?>"><i class="fas fa-arrow-left" aria-hidden="true"></i> All stories</a>
						<span><?php echo esc_html( $preview ? $preview['day'] : 'Field Notes / ' . $story_number ); ?></span>
					</div>
					<p class="toolkit-story-label"><i aria-hidden="true"></i><?php echo esc_html( $category_name ); ?></p>
					<h1><?php echo esc_html( $story_title ); ?></h1>
					<p class="toolkit-story-standfirst"><?php echo esc_html( $standfirst ); ?></p>
					<div class="toolkit-story-meta">
						<span><small>Published</small><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></time></span>
						<span><small>Reading time</small><?php echo esc_html( $reading_time ); ?> minutes</span>
					</div>
				</div>
				<figure>
					<img src="<?php echo esc_url( $hero_image ); ?>" width="1400" height="900" alt="<?php echo esc_attr( $story_title ); ?>">
					<figcaption><span>Toolkit Stories</span><b>Skills → Work → Opportunity</b></figcaption>
				</figure>
			</header>

			<div class="toolkit-story-layout">
				<aside class="toolkit-story-share" aria-label="Share this story">
					<span>Pass it on</span>
					<a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo rawurlencode( get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Share on LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
					<a href="https://wa.me/?text=<?php echo rawurlencode( get_the_title() . ' ' . get_permalink() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
					<a href="mailto:?subject=<?php echo rawurlencode( get_the_title() ); ?>&amp;body=<?php echo rawurlencode( get_permalink() ); ?>" aria-label="Share by email"><i class="far fa-envelope" aria-hidden="true"></i></a>
				</aside>
				<div class="toolkit-story-content">
					<div class="toolkit-story-content__marker"><span>The brief</span><i></i></div>
					<?php if ( $preview ) : ?>
						<?php foreach ( $preview['content'] as $paragraph ) : ?><p><?php echo esc_html( $paragraph ); ?></p><?php endforeach; ?>
						<div class="toolkit-story-day-gallery" aria-label="<?php echo esc_attr( $story_title . ' gallery' ); ?>">
							<?php foreach ( $preview['images'] as $index => $image ) : ?><figure><img src="<?php echo esc_url( $image ); ?>" width="1200" height="900" alt="<?php echo esc_attr( $story_title . ' — image ' . ( $index + 1 ) ); ?>"><figcaption><?php echo esc_html( $category_name . ' / ' . str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></figcaption></figure><?php endforeach; ?>
						</div>
					<?php else : the_content(); endif; ?>
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
						$related_image = toolkit_story_image_url( get_the_ID(), 'large' );
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
