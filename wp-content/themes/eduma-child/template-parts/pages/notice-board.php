<?php
get_header();
$image     = get_stylesheet_directory_uri() . '/assets/images/pages/notice-board.jpg';
$apply_url = home_url( '/our-ventures/toolkit-courses-apply-today/' );
?>
<main id="main-content" class="toolkit-page toolkit-notice-page">
	<section class="toolkit-notice-hero" style="background-image:url('<?php echo esc_url( $image ); ?>')">
		<div class="toolkit-notice-hero__copy"><p class="toolkit-kicker">Stay informed</p><h1>Notice Board</h1><p>Stay up to date with admissions guidance, opportunities, events, and important updates from Toolkit Africa.</p><span aria-hidden="true"></span></div>
		<label class="toolkit-notice-search"><i class="fas fa-search" aria-hidden="true"></i><input type="search" placeholder="Search announcements..." aria-label="Search announcements"></label>
	</section>

	<section class="toolkit-section toolkit-notices">
		<div class="toolkit-notice-controls">
			<div class="toolkit-filter-group" role="group" aria-label="Filter notices"><button class="is-active" data-filter="all">All</button><button data-filter="admissions">Admissions</button><button data-filter="opportunity">Opportunities</button><button data-filter="event">Events</button><button data-filter="notice">Notices</button></div>
			<div class="toolkit-view-controls"><label for="toolkit-notice-sort">Sort by:</label><select id="toolkit-notice-sort" aria-label="Sort notices"><option>Newest first</option></select><button data-view="grid" class="is-active" aria-label="Grid view"><i class="fas fa-th" aria-hidden="true"></i></button><button data-view="list" aria-label="List view"><i class="fas fa-list" aria-hidden="true"></i></button></div>
		</div>
		<div class="toolkit-notice-grid">
			<article data-category="admissions"><div class="toolkit-notice-card__content"><span class="toolkit-tag">Admissions</span><h2>Course applications are open</h2><p>Explore current practical programmes and prepare your application through our guided admissions route.</p><div class="toolkit-notice-card__meta"><span><i class="far fa-clock" aria-hidden="true"></i> Current</span><span><i class="far fa-user-circle" aria-hidden="true"></i> Admissions office</span></div><a href="<?php echo esc_url( $apply_url ); ?>">Start application <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div><img src="<?php echo esc_url( $image ); ?>" width="600" height="400" alt="Toolkit students and graduates"></article>
			<article data-category="notice"><div class="toolkit-notice-card__content"><span class="toolkit-tag toolkit-tag--orange">Important notice</span><h2>Anti-fraud guidance</h2><p>Confirm application and payment instructions with Toolkit before sending money or personal documents.</p><div class="toolkit-notice-card__meta"><span><i class="fas fa-shield-alt" aria-hidden="true"></i> Safety guidance</span><span><i class="far fa-user-circle" aria-hidden="true"></i> Admin office</span></div><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact Toolkit <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div><div class="toolkit-notice-card__symbol"><i class="fas fa-shield-alt" aria-hidden="true"></i></div></article>
			<article data-category="opportunity event"><div class="toolkit-notice-card__content"><span class="toolkit-tag">Opportunity</span><h2>Skills and training pathways</h2><p>Discover practical technical, digital, enterprise, and green-skills learning opportunities.</p><div class="toolkit-notice-card__meta"><span><i class="far fa-clock" aria-hidden="true"></i> Ongoing</span><span><i class="fas fa-map-marker-alt" aria-hidden="true"></i> Toolkit hubs</span></div><a href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Explore courses <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/pages/about.jpg' ); ?>" width="600" height="400" alt="Toolkit learner in training attire"></article>
		</div>
	</section>
	<section class="toolkit-notice-update"><i class="far fa-bell" aria-hidden="true"></i><div><h2>Never miss an update</h2><p>Speak with the Toolkit team for current admissions and programme information.</p></div><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact Toolkit</a></section>
</main>
<?php get_footer();
