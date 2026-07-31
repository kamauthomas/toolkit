<?php
/**
 * Front page template - renders child-theme homepage sections + Elementor content.
 * Keeping the first viewport here avoids homepage regressions from parent theme
 * updates or page-builder template changes.
 */
if ( function_exists( 'eduma_child_redesign_enabled' ) && ! eduma_child_redesign_enabled() ) {
	include get_template_directory() . '/page.php';
	return;
}
get_header();
require_once get_stylesheet_directory() . '/inc/hero-slides.php';
$slides = eduma_child_get_hero_slides();
?>
<div id="hero-slider" class="hero-slider" aria-roledescription="carousel" aria-label="Featured slides" tabindex="0">

	<div class="hero-slider__slides" role="list">
		<?php foreach ( $slides as $index => $slide ) :
			$slide_num = $index + 1;
			$is_first  = $index === 0;
		?>
		<div class="hero-slider__slide<?php echo $is_first ? ' is-active' : ''; ?>"
			 role="group"
			 aria-roledescription="slide"
			 aria-label="Slide <?php echo esc_attr( $slide_num ); ?> of <?php echo count( $slides ); ?>"
			 aria-hidden="<?php echo $is_first ? 'false' : 'true'; ?>"
			 data-slide="<?php echo esc_attr( $slide_num ); ?>"
			 style="--hero-image: url('<?php echo esc_url( $slide['image'] ); ?>'); --hero-image-mobile: url('<?php echo esc_url( $slide['image_mobile'] ); ?>');">
			<div class="hero-slider__overlay"></div>
			<div class="hero-slider__content">
				<span class="hero-slider__eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></span>
				<?php if ( $is_first ) : ?>
					<h1 class="hero-slider__heading"><?php echo wp_kses_post( $slide['heading'] ); ?></h1>
				<?php else : ?>
					<p class="hero-slider__heading" aria-hidden="true"><?php echo wp_kses_post( $slide['heading'] ); ?></p>
				<?php endif; ?>
				<p class="hero-slider__desc"><?php echo esc_html( $slide['description'] ); ?></p>
				<div class="hero-slider__actions">
					<a class="hero-slider__btn hero-slider__btn--primary" href="<?php echo esc_url( $slide['primary_cta']['url'] ); ?>">
						<?php echo esc_html( $slide['primary_cta']['label'] ); ?>
						<svg class="hero-slider__arrow-icon" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 3L7 4L10.5 7.5H3V8.5H10.5L7 12L8 13L13 8L8 3Z" fill="currentColor"/></svg>
					</a>
					<?php if ( $slide['secondary_cta'] ) : ?>
						<a class="hero-slider__btn hero-slider__btn--secondary" href="<?php echo esc_url( $slide['secondary_cta']['url'] ); ?>">
							<?php echo esc_html( $slide['secondary_cta']['label'] ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<div class="hero-slider__counter" aria-hidden="true">
		<span class="hero-slider__counter-current">01</span>
		<span class="hero-slider__counter-line"></span>
		<span class="hero-slider__counter-total">0<?php echo count( $slides ); ?></span>
	</div>

	<button class="hero-slider__scroll-cue" aria-label="Scroll to content below">
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 14L4 8L5.5 6.5L10 11L14.5 6.5L16 8L10 14Z" fill="currentColor"/></svg>
	</button>

	<button class="hero-slider__arrow hero-slider__arrow--next" aria-label="Next slide">
		<svg width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
	</button>

	<div class="hero-slider__pagination" role="tablist" aria-label="Slide controls">
		<?php foreach ( $slides as $index => $slide ) : ?>
		<button class="hero-slider__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
				role="tab"
				aria-label="Go to slide <?php echo $index + 1; ?>"
				aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
				data-slide="<?php echo $index + 1; ?>"></button>
		<?php endforeach; ?>
		<button class="hero-slider__pause" aria-label="Pause autoplay">
			<svg class="hero-slider__pause-icon" width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="2" y="1" width="3.5" height="12" rx="1" fill="currentColor"/><rect x="8.5" y="1" width="3.5" height="12" rx="1" fill="currentColor"/></svg>
			<svg class="hero-slider__play-icon" width="14" height="14" viewBox="0 0 14 14" fill="none" style="display:none"><path d="M4 1L12 7L4 13V1Z" fill="currentColor"/></svg>
		</button>
	</div>

	<div class="hero-slider__video-badge" role="button" tabindex="0" data-video-id="LmZhEabXyUc" aria-label="Play video: Watch Our Story">
		<span class="hero-slider__video-play">
			<svg width="18" height="18" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="8" fill="#ED6E0D"/><path d="M7 5.5L12 9L7 12.5V5.5Z" fill="white"/></svg>
		</span>
		<span class="hero-slider__video-text">
			<span class="hero-slider__video-title">Watch Our Story</span>
			<span class="hero-slider__video-sub">Play Video</span>
		</span>
		<span class="hero-slider__video-divider"></span>
		<span class="hero-slider__video-duration">02:45</span>
	</div>

	<div class="hero-slider__modal" role="dialog" aria-modal="true" aria-label="Video player" hidden>
		<div class="hero-slider__modal-backdrop"></div>
		<div class="hero-slider__modal-content">
			<button class="hero-slider__modal-close" type="button" aria-label="Close video">&times;</button>
			<div class="hero-slider__modal-video" data-video-container></div>
		</div>
	</div>
</div>

<section class="hero-features">
	<div class="container">
		<div class="hero-features__grid">
			<div class="hero-features__item">
				<div class="hero-features__icon hero-features__icon--green">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2v-5"/></svg>
				</div>
				<h3 class="hero-features__title">Industry-Aligned<br>Courses</h3>
				<p class="hero-features__desc">Practical training tailored to real-world needs.</p>
			</div>
			<div class="hero-features__item">
				<div class="hero-features__icon hero-features__icon--orange">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
				</div>
				<h3 class="hero-features__title">Hands-on<br>Learning</h3>
				<p class="hero-features__desc">Learn by doing through modern workshops &amp; labs.</p>
			</div>
			<div class="hero-features__item">
				<div class="hero-features__icon hero-features__icon--green">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
				</div>
				<h3 class="hero-features__title">Innovation<br>Driven</h3>
				<p class="hero-features__desc">Fostering creativity and problem-solving.</p>
			</div>
			<div class="hero-features__item">
				<div class="hero-features__icon hero-features__icon--orange">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
				</div>
				<h3 class="hero-features__title">Community<br>Impact</h3>
				<p class="hero-features__desc">Building sustainable communities across Africa.</p>
			</div>
		</div>
	</div>
</section>

<section class="home-iw-banner" aria-label="Toolkit at the International Institute of Welding 2026">
	<div class="home-iw-banner__inner">
		<a class="home-iw-banner__link" href="https://toolkitiiwppt.my.canva.site/iiw-general-assembly-presentation"><span>International Institute of Welding</span><strong>Toolkit at IIW 2026</strong><em>See the presentation <i class="fas fa-arrow-right" aria-hidden="true"></i></em></a>
	</div>
</section>

<section id="home-who-we-are" class="home-who" aria-labelledby="home-who-title">
	<div class="home-who__inner">
		<div class="home-who__top">
			<div class="home-who__video">
				<button class="home-who__video-card"
						type="button"
						data-youtube-id="LmZhEabXyUc"
						aria-label="Play The Toolkit for Skills and Innovation video">
					<img
						class="home-who__video-thumb"
						src="https://img.youtube.com/vi/LmZhEabXyUc/hqdefault.jpg"
						alt="The Toolkit for Skills and Innovation video"
						loading="lazy"
						width="480"
						height="360">
					<span class="home-who__video-channel" aria-hidden="true">
						<span class="home-who__video-channel-icon">T</span>
						<span class="home-who__video-channel-text">
							<strong>The Toolkit iSkills TTI Ltd</strong>
							<span>The Toolkit for Skills and Innovation</span>
						</span>
					</span>
					<span class="home-who__video-logo" aria-hidden="true">T</span>
					<span class="home-who__video-play" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 22 22" focusable="false"><path d="M7 4.5L17 11L7 17.5V4.5Z" fill="currentColor"/></svg>
					</span>
					<span class="home-who__video-youtube" aria-hidden="true">
						<span>Watch on</span>
						<i class="fa fa-youtube-play"></i>
						<strong>YouTube</strong>
					</span>
				</button>
			</div>

			<div class="home-who__copy">
				<p class="home-who__eyebrow">Who We Are</p>
				<h2 id="home-who-title" class="home-who__title">
					<span>Empowering Youth.</span>
					<span>Building Futures.</span>
				</h2>
				<p>The <strong>Toolkit</strong> for Skills and Innovation is a Kenya-based social enterprise founded in 2014 with the goal of disrupting youth unemployment. The Toolkit trains vulnerable youth and women, certifies their skills with regulatory bodies, and then links them to employment or entrepreneurship.</p>
			</div>

			<div class="home-who__quick">
				<h3>Quick Links</h3>
				<a class="home-who__pill" href="<?php echo esc_url( home_url( '/our-ventures/toolkit-courses-apply-today/' ) ); ?>">
					<span class="home-who__pill-icon" aria-hidden="true"><i class="fas fa-graduation-cap"></i></span>
					<span>Toolkit Courses: Apply Now</span>
					<i class="fas fa-arrow-right" aria-hidden="true"></i>
				</a>
				<a class="home-who__pill" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
					<span class="home-who__pill-icon" aria-hidden="true"><i class="fas fa-headset"></i></span>
					<span>Contact Us</span>
					<i class="fas fa-arrow-right" aria-hidden="true"></i>
				</a>
			</div>
		</div>

		<div class="home-who__bottom">
			<div class="home-who__beliefs">
				<article class="home-who__belief">
					<span class="home-who__badge home-who__badge--olive" aria-hidden="true"><i class="fas fa-eye"></i></span>
					<h3>Our Vision</h3>
					<p>A leader in powering Africa with skilled, confident, and productive youth.</p>
				</article>
				<article class="home-who__belief">
					<span class="home-who__badge home-who__badge--olive" aria-hidden="true"><i class="fas fa-bullseye"></i></span>
					<h3>Our Mission</h3>
					<p>We transform vulnerable youth to prosperity through innovation and skills for current and future labour markets.</p>
				</article>
			</div>

			<div class="home-who__stats" aria-label="Toolkit impact statistics">
				<div class="home-who__stat">
					<span class="home-who__badge home-who__badge--peach" aria-hidden="true"><i class="fas fa-users"></i></span>
					<strong data-impact-count="11190">11,190</strong>
					<span>Total Youth Impacted</span>
				</div>
				<div class="home-who__stat">
					<span class="home-who__badge home-who__badge--peach" aria-hidden="true"><i class="fas fa-tools"></i></span>
					<strong data-impact-count="4987">4,987</strong>
					<span>Construction Sector</span>
				</div>
				<div class="home-who__stat">
					<span class="home-who__badge home-who__badge--peach" aria-hidden="true"><i class="fas fa-desktop"></i></span>
					<strong data-impact-count="3537">3,537</strong>
					<span>Digital Skills &amp; Online Jobs</span>
				</div>
				<div class="home-who__stat">
					<span class="home-who__badge home-who__badge--peach" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
					<strong data-impact-count="554">554</strong>
					<span>Consultancy &amp; Research</span>
				</div>
				<div class="home-who__stat">
					<span class="home-who__badge home-who__badge--peach" aria-hidden="true"><i class="fas fa-seedling"></i></span>
					<strong data-impact-count="2112">2,112</strong>
					<span>Organic Farming</span>
				</div>
			</div>
		</div>
	</div>
</section>

<section class="home-pathways" aria-labelledby="home-pathways-title">
	<div class="home-modern__inner">
		<div class="home-modern__heading">
			<div><p class="home-modern__kicker">Learning pathways</p><h2 id="home-pathways-title">Skills built around real work</h2></div>
			<p>Choose practical training that combines workshop experience, recognised assessment and the confidence to move into employment or enterprise.</p>
		</div>
		<div class="home-pathways__grid">
			<div class="home-pathways__video"><button class="toolkit-video-facade" type="button" data-video-id="my0S14iTew8" aria-label="Play Toolkit skills training video"><img src="https://i.ytimg.com/vi/my0S14iTew8/hqdefault.jpg" width="480" height="360" loading="lazy" alt="Toolkit practical skills training"><span aria-hidden="true">▶</span></button></div>
			<div class="home-pathways__list">
				<a class="home-pathway" href="<?php echo esc_url( home_url( '/our-ventures/construction-sector-skills/' ) ); ?>"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/courses/welding.jpg' ); ?>" width="520" height="300" loading="lazy" alt="Practical welding training"><span><small>Training pathway</small><strong>Welding Sector</strong><em>Learn more <i class="fas fa-arrow-right" aria-hidden="true"></i></em></span></a>
				<a class="home-pathway" href="<?php echo esc_url( home_url( '/our-ventures/renewable-energy/' ) ); ?>"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/courses/solar.jpg' ); ?>" width="520" height="300" loading="lazy" alt="Solar and renewable energy training"><span><small>Strategic sector</small><strong>Renewable Sector</strong><em>Learn more <i class="fas fa-arrow-right" aria-hidden="true"></i></em></span></a>
			</div>
		</div>
		<a class="home-modern__text-link" href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Browse all current courses <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
	</div>
</section>

<section class="home-testimonials" aria-labelledby="home-testimonials-title"><div class="home-modern__inner">
	<div class="home-modern__heading"><div><p class="home-modern__kicker">Learner voices</p><h2 id="home-testimonials-title">Testimonials</h2></div><p>Stories from graduates who have turned practical training into confidence, employment and new opportunities.</p></div>
	<div class="home-testimonials__grid">
			<article><div class="home-testimonial__video"><button class="toolkit-video-facade" type="button" data-video-id="VOIpU5tRRvo" aria-label="Play Caroline Kieru testimonial"><img src="https://i.ytimg.com/vi/VOIpU5tRRvo/hqdefault.jpg" width="480" height="360" loading="lazy" alt="Caroline Kieru sharing her Toolkit training experience"><span aria-hidden="true">▶</span></button></div><span class="home-testimonial__quote" aria-hidden="true">“</span><blockquote>Toolkit transformed my passion into a profession. MIG welding and Virtual Reality training built the skills that took me from a humble background into the welding industry and now to work in France.</blockquote><footer><strong>Caroline Kieru</strong><span>International 6G Welder</span></footer></article>
			<article><div class="home-testimonial__video"><button class="toolkit-video-facade" type="button" data-video-id="0sjNPAXN8pw" aria-label="Play Clifford Leisi testimonial"><img src="https://i.ytimg.com/vi/0sjNPAXN8pw/hqdefault.jpg" width="480" height="360" loading="lazy" alt="Clifford Leisi sharing his Toolkit welding experience"><span aria-hidden="true">▶</span></button></div><span class="home-testimonial__quote" aria-hidden="true">“</span><blockquote>The hands-on, immersive experience sharpened my skills, built my confidence and prepared me for real-world challenges. Today I am working as a professional welder.</blockquote><footer><strong>Clifford Leisi</strong><span>Toolkit MIG Welder</span></footer></article>
			<article><div class="home-testimonial__video"><button class="toolkit-video-facade" type="button" data-video-id="LJGs1t8T6Bc" aria-label="Play Carol Njoki testimonial"><img src="https://i.ytimg.com/vi/LJGs1t8T6Bc/hqdefault.jpg" width="480" height="360" loading="lazy" alt="Carol Njoki sharing her Toolkit training experience"><span aria-hidden="true">▶</span></button></div><span class="home-testimonial__quote" aria-hidden="true">“</span><blockquote>Through hands-on training, mentorship and support from passionate instructors, I gained skills and confidence I never thought possible. Toolkit helped me discover my potential.</blockquote><footer><strong>Carol Njoki</strong><span>Toolkit MIG Welder</span></footer></article>
	</div>
</div></section>

<section class="home-method" aria-labelledby="home-method-title">
	<div class="home-modern__inner home-method__layout">
		<div class="home-method__copy"><p class="home-modern__kicker">The Toolkit approach</p><h2 id="home-method-title">A clearer route from learning to opportunity</h2><p>Training works best when technical ability is supported by recognised standards, workplace habits and access to industry. Our programmes bring those elements together.</p><a class="home-modern__button" href="<?php echo esc_url( home_url( '/about-toolkit-africa/' ) ); ?>">How Toolkit works <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
		<ol class="home-method__steps">
			<li><span>01</span><div><h3>Train</h3><p>Build practical competence through workshop-led and applied learning.</p></div></li>
			<li><span>02</span><div><h3>Certify</h3><p>Prepare for assessment aligned with relevant regulatory and industry standards.</p></div></li>
			<li><span>03</span><div><h3>Connect</h3><p>Strengthen employability and create pathways towards work or enterprise.</p></div></li>
		</ol>
	</div>
</section>

<?php
$home_stories = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'ignore_sticky_posts' => true ) );
if ( $home_stories->have_posts() ) :
	?>
<section class="home-stories" aria-labelledby="home-stories-title"><div class="home-modern__inner">
	<div class="home-modern__heading"><div><p class="home-modern__kicker">From the field</p><h2 id="home-stories-title">Ideas, partnerships and progress</h2></div><a class="home-modern__text-link" href="<?php echo esc_url( home_url( '/toolkit-blog/' ) ); ?>">Visit Toolkit Blog <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
		<div class="home-stories__grid"><?php $story_index = 0; while ( $home_stories->have_posts() ) : $home_stories->the_post(); ?><article><a class="home-story__image" href="<?php the_permalink(); ?>"><img src="<?php echo esc_url( toolkit_story_image_url( get_the_ID(), 'large', $story_index ) ); ?>" width="620" height="380" loading="lazy" alt="<?php echo esc_attr( get_the_title() ); ?>"></a><div><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p><a href="<?php the_permalink(); ?>">Read story <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div></article><?php $story_index++; endwhile; ?></div>
</div></section>
<?php wp_reset_postdata(); endif; ?>

<section class="home-apply-band"><div><p>Admissions</p><h2>Ready to build practical skills?</h2><span>Review the current courses and complete a guided application.</span></div><div><a class="home-modern__button home-modern__button--orange" href="<?php echo esc_url( home_url( '/our-ventures/toolkit-courses-apply-today/' ) ); ?>">Apply now <i class="fas fa-arrow-right" aria-hidden="true"></i></a><a class="home-modern__button home-modern__button--outline" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Talk to admissions</a></div></section>
<?php

get_footer();
