<?php
/**
 * Modern editorial layout for individual Toolkit stories.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$post_id       = get_the_ID();
	$post_slug     = get_post_field( 'post_name', $post_id );
	$preview       = toolkit_editorial_story_preview();
	$legacy_profiles = array(
		'youth-international-skills-day-12th-august-2025' => array(
			'theme'   => 'youth-skills',
			'label'   => 'Youth & Skills',
			'topline' => 'Skills in focus / Field story',
			'marker'  => 'Skills snapshot',
		),
		'dont-miss-this-insightful-podcast-on-youth-skills-and-job-creation-in-africa-%f0%9f%a7%a0%f0%9f%92%bc' => array(
			'theme'   => 'conversation',
			'label'   => 'Toolkit Conversation',
			'topline' => 'Listen & learn / Conversation',
			'marker'  => 'The conversation',
		),
		'careers-in-mig-mag-welding-insights-from-our-webinar' => array(
			'theme'   => 'welding',
			'label'   => 'Industry Insight',
			'topline' => 'Welding careers / Technical brief',
			'marker'  => 'Inside the trade',
		),
		'toolkit-shines-with-tujiajiri-mentorship-program-for-solar-energy-trainees' => array(
			'theme'   => 'solar',
			'label'   => 'Industry Mentorship',
			'topline' => 'Solar pathways / Field notes',
			'marker'  => 'Learning in practice',
		),
		'ilo-youth-employment-training-workshop' => array(
			'theme'   => 'employment',
			'label'   => 'Youth Employment',
			'topline' => 'Work pathways / Workshop notes',
			'marker'  => 'Workshop brief',
		),
		'igniting-her-future-innovateher-roll-out-at-the-toolkit-for-skills-and-innovation-hub' => array(
			'theme'   => 'innovateher',
			'label'   => 'Women in Innovation',
			'topline' => 'InnovateHER / Programme story',
			'marker'  => 'The programme',
		),
	);
	$legacy_profile = isset( $legacy_profiles[ $post_slug ] ) ? $legacy_profiles[ $post_slug ] : array();
	$blog_url      = home_url( '/toolkit-blog/' );
	$hero_image    = toolkit_story_image_url( $post_id, 'full' );
	$categories    = get_the_category();
	$category_name = $preview ? $preview['label'] : ( $legacy_profile ? $legacy_profile['label'] : ( $categories ? $categories[0]->name : 'Toolkit story' ) );
	$story_theme   = $preview ? $preview['theme'] : ( $legacy_profile ? $legacy_profile['theme'] : 'field-notes' );
	$story_topline = $preview ? $preview['day'] : ( $legacy_profile ? $legacy_profile['topline'] : 'Field Notes / ' . str_pad( (string) ( absint( $post_id ) % 100 ), 2, '0', STR_PAD_LEFT ) );
	$content_marker = $legacy_profile ? $legacy_profile['marker'] : 'The brief';
	$story_title   = $preview ? $preview['title'] : get_the_title();
	$reading_words = str_word_count( $preview ? implode( ' ', $preview['content'] ) : wp_strip_all_tags( get_the_content() ) );
	$reading_time  = max( 1, (int) ceil( $reading_words / 220 ) );
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
	<main id="main-content" class="toolkit-page toolkit-story-page <?php echo esc_attr( 'toolkit-story-theme--' . $story_theme ); ?>">
		<article <?php post_class( 'toolkit-story' ); ?>>
			<header class="toolkit-story-hero toolkit-story-cover">
				<div class="toolkit-story-hero__copy">
					<div class="toolkit-story-cover__topline">
						<a class="toolkit-story-back" href="<?php echo esc_url( $blog_url ); ?>"><i class="fas fa-arrow-left" aria-hidden="true"></i> All stories</a>
						<span><?php echo esc_html( $story_topline ); ?></span>
					</div>
					<p class="toolkit-story-label"><i aria-hidden="true"></i><?php echo esc_html( $category_name ); ?></p>
					<h1><?php echo esc_html( $story_title ); ?></h1>
					<p class="toolkit-story-standfirst"><?php echo esc_html( $standfirst ); ?></p>
					<div class="toolkit-story-meta">
						<span><small>Published</small><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( 'j M Y' ) ); ?></time></span>
						<span><small>Reading time</small><?php echo esc_html( $reading_time . ' ' . ( 1 === $reading_time ? 'minute' : 'minutes' ) ); ?></span>
					</div>
				</div>
				<figure>
					<img src="<?php echo esc_url( $hero_image ); ?>" width="1400" height="900" alt="<?php echo esc_attr( $story_title ); ?>">
					<figcaption><span><?php echo esc_html( $category_name ); ?></span><b>Skills → Work → Opportunity</b></figcaption>
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
					<div class="toolkit-story-content__marker"><span><?php echo esc_html( $content_marker ); ?></span><i></i></div>
					<?php if ( $preview ) : ?>
						<?php if ( ! empty( $preview['section_title'] ) ) : ?><h2><?php echo esc_html( $preview['section_title'] ); ?></h2><?php endif; ?>
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
