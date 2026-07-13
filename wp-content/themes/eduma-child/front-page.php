<?php
/**
 * Front page template - renders child-theme homepage sections + Elementor content.
 * Keeping the first viewport here avoids homepage regressions from parent theme
 * updates or page-builder template changes.
 */
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
			 style="background-image: url(<?php echo esc_url( $slide['image'] ); ?>);">
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

	<div class="hero-slider__video-badge" role="button" tabindex="0" aria-label="Play video: Watch Our Story">
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
			<button class="hero-slider__modal-close" aria-label="Close video">&times;</button>
			<div class="hero-slider__modal-video">
				<p style="color:#fff;text-align:center;padding:4rem;">Video placeholder — embed URL here.</p>
			</div>
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

<section class="home-iw-banner" aria-label="Toolkit at the International Institute of Welding 2025">
	<div class="home-iw-banner__inner">
		<a class="home-iw-banner__link" href="https://toolkitiiwppt.my.canva.site/iiw-general-assembly-presentation">
			<img
				src="<?php echo esc_url( content_url( 'uploads/2025/06/toolkit-at-the-International-Institute-of-welding-2025-2.png' ) ); ?>"
				alt="Toolkit at the International Institute of Welding 2025"
				width="1600"
				height="200"
				loading="lazy">
		</a>
	</div>
</section>

<section id="home-who-we-are" class="home-who" aria-labelledby="home-who-title">
	<div class="home-who__inner">
		<div class="home-who__top">
			<div class="home-who__video">
				<button class="home-who__video-card"
						type="button"
						data-youtube-id="LmZhEabXyUc"
						aria-label="Play The Toolkit Skills and Innovation Hub video">
					<img
						class="home-who__video-thumb"
						src="https://img.youtube.com/vi/LmZhEabXyUc/hqdefault.jpg"
						alt=""
						loading="lazy"
						width="480"
						height="360">
					<span class="home-who__video-channel" aria-hidden="true">
						<span class="home-who__video-channel-icon">T</span>
						<span class="home-who__video-channel-text">
							<strong>The Toolkit iSkills TTI Ltd</strong>
							<span>The Toolkit Skills &amp; Innovation Hub</span>
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
				<a class="home-who__pill" href="https://toolkit.mzizi.co.ke/portal/OnlineApplicationForm.aspx?q=d0d6b2f229d733c1e3156244805125a2">
					<span class="home-who__pill-icon" aria-hidden="true"><i class="fas fa-graduation-cap"></i></span>
					<span>Toolkit Courses: Apply Now</span>
					<i class="fas fa-arrow-right" aria-hidden="true"></i>
				</a>
				<a class="home-who__pill" href="https://toolkitafrica.ac.ke/new/contact/">
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
					<strong>11,190</strong>
					<span>Total Youth Impacted</span>
				</div>
				<div class="home-who__stat">
					<span class="home-who__badge home-who__badge--peach" aria-hidden="true"><i class="fas fa-tools"></i></span>
					<strong>4,987</strong>
					<span>Construction Sector</span>
				</div>
				<div class="home-who__stat">
					<span class="home-who__badge home-who__badge--peach" aria-hidden="true"><i class="fas fa-desktop"></i></span>
					<strong>3,537</strong>
					<span>Digital Skills &amp; Online Jobs</span>
				</div>
				<div class="home-who__stat">
					<span class="home-who__badge home-who__badge--peach" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
					<strong>554</strong>
					<span>Consultancy &amp; Research</span>
				</div>
				<div class="home-who__stat">
					<span class="home-who__badge home-who__badge--peach" aria-hidden="true"><i class="fas fa-seedling"></i></span>
					<strong>2,112</strong>
					<span>Organic Farming</span>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
while ( have_posts() ) : the_post();
	the_content();
endwhile;

get_footer();
