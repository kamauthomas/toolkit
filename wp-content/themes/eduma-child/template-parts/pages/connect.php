<?php
get_header();
$assets = get_stylesheet_directory_uri() . '/assets/images/';
$apply  = home_url( '/apply/' );
?>
<main id="main-content" class="toolkit-connect-page">
	<section class="connect-hero">
		<img class="connect-hero__image" src="<?php echo esc_url( $assets . 'courses/experiences/vr-welding.jpg' ); ?>" width="1400" height="900" alt="A Toolkit learner building practical welding skills with virtual-reality training">
		<div class="connect-hero__shade" aria-hidden="true"></div>
		<img class="connect-hero__logo" src="<?php echo esc_url( $assets . 'toolkit-logo.png' ); ?>" width="190" height="116" alt="The Toolkit for Skills and Innovation">
		<div class="connect-hero__content">
			<p class="connect-hero__eyebrow">Skills to opportunity.</p>
			<h1>Your next step starts here.</h1>
			<p>Explore practical training, get current admissions guidance, and stay connected with The Toolkit for Skills and Innovation.</p>
		</div>
	</section>

	<section class="connect-actions" aria-label="Toolkit quick links">
		<a class="connect-action connect-action--primary" href="<?php echo esc_url( $apply ); ?>" data-metric="connect_apply">
			<span class="connect-action__icon"><i class="fas fa-file-signature" aria-hidden="true"></i></span>
			<span><small>Admissions</small><strong>Start your application</strong></span>
			<i class="fas fa-arrow-right" aria-hidden="true"></i>
		</a>
		<a class="connect-action" href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>" data-metric="connect_courses">
			<span class="connect-action__icon"><i class="fas fa-graduation-cap" aria-hidden="true"></i></span>
			<span><small>Find your pathway</small><strong>Explore our courses</strong></span>
			<i class="fas fa-arrow-right" aria-hidden="true"></i>
		</a>
		<a class="connect-action" href="<?php echo esc_url( home_url( '/notice-board/' ) ); ?>" data-metric="connect_notices">
			<span class="connect-action__icon"><i class="fas fa-bullhorn" aria-hidden="true"></i></span>
			<span><small>Latest information</small><strong>View the Notice Board</strong></span>
			<i class="fas fa-arrow-right" aria-hidden="true"></i>
		</a>
		<a class="connect-action connect-action--whatsapp" href="https://wa.me/254711802855?text=Hello%20The%20Toolkit%20for%20Skills%20and%20Innovation%2C%20I%20would%20like%20admissions%20guidance." target="_blank" rel="noopener noreferrer" data-metric="connect_whatsapp">
			<span class="connect-action__icon"><i class="fab fa-whatsapp" aria-hidden="true"></i></span>
			<span><small>WhatsApp admissions</small><strong>+254 711 802 855</strong></span>
			<i class="fas fa-external-link-alt" aria-hidden="true"></i>
		</a>
	</section>

	<section class="connect-feature">
		<div>
			<p class="connect-kicker">Inside Toolkit</p>
			<h2>See practical learning in action.</h2>
			<p>Meet learners, enter the workshops, and follow the skills journeys shaping employment and enterprise.</p>
		</div>
		<a href="https://www.youtube.com/@toolkitafrica" target="_blank" rel="noopener noreferrer" data-metric="connect_youtube_feature"><i class="fab fa-youtube" aria-hidden="true"></i> Watch on YouTube</a>
	</section>

	<section class="connect-socials" aria-labelledby="connect-social-title">
		<p class="connect-kicker">Official channels</p>
		<h2 id="connect-social-title">Follow the work</h2>
		<div class="connect-socials__grid">
			<a href="https://www.tiktok.com/@thetoolkitafrika" target="_blank" rel="noopener noreferrer" data-metric="connect_tiktok"><i class="fab fa-tiktok" aria-hidden="true"></i><span>TikTok<small>@thetoolkitafrika</small></span></a>
			<a href="https://www.instagram.com/thetoolkitafrika" target="_blank" rel="noopener noreferrer" data-metric="connect_instagram"><i class="fab fa-instagram" aria-hidden="true"></i><span>Instagram<small>@thetoolkitafrika</small></span></a>
			<a href="https://www.facebook.com/toolkitafrica" target="_blank" rel="noopener noreferrer" data-metric="connect_facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i><span>Facebook<small>The Toolkit for Skills and Innovation</small></span></a>
			<a href="https://www.linkedin.com/company/the-toolkit-iskills-tti-ltd" target="_blank" rel="noopener noreferrer" data-metric="connect_linkedin"><i class="fab fa-linkedin-in" aria-hidden="true"></i><span>LinkedIn<small>Toolkit iSkills</small></span></a>
			<a href="https://www.youtube.com/@toolkitafrica" target="_blank" rel="noopener noreferrer" data-metric="connect_youtube"><i class="fab fa-youtube" aria-hidden="true"></i><span>YouTube<small>@toolkitafrica</small></span></a>
			<a href="https://x.com/toolkitafrica" target="_blank" rel="noopener noreferrer" data-metric="connect_x"><i class="fab fa-twitter" aria-hidden="true"></i><span>X<small>@toolkitafrica</small></span></a>
			<a href="https://whatsapp.com/channel/0029Vb6PfqR5Ejy79JAJlb1f" target="_blank" rel="noopener noreferrer" data-metric="connect_whatsapp_channel"><i class="fab fa-whatsapp" aria-hidden="true"></i><span>WhatsApp<small>Official channel</small></span></a>
		</div>
	</section>

	<footer class="connect-footer">
		<a href="tel:+254709549200" data-metric="connect_call"><i class="fas fa-phone-alt" aria-hidden="true"></i> +254 709 549 200</a>
		<a href="mailto:office@toolkitafrica.ac.ke" data-metric="connect_email"><i class="far fa-envelope" aria-hidden="true"></i> Email Toolkit</a>
		<p>The Toolkit for Skills and Innovation, Kikuyu, Kenya</p>
	</footer>
</main>
<?php get_footer();
