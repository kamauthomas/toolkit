<?php
if ( function_exists( 'eduma_child_redesign_enabled' ) && ! eduma_child_redesign_enabled() ) {
	include get_template_directory() . '/footer.php';
	return;
}
/**
 * Lightweight site footer owned by the child theme.
 */
$logo = get_stylesheet_directory_uri() . '/assets/images/toolkit-logo.png';
?>
<footer id="colophon" class="toolkit-site-footer">
	<div class="toolkit-site-footer__main">
		<div class="toolkit-site-footer__inner">
			<div class="toolkit-site-footer__brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="The Toolkit for Skills and Innovation home">
					<img src="<?php echo esc_url( $logo ); ?>" width="180" height="130" alt="The Toolkit for Skills and Innovation">
				</a>
				<p>Practical skills, innovation, and opportunity for Africa's young people.</p>
			</div>

			<nav class="toolkit-site-footer__nav" aria-label="Footer navigation">
				<h2>Explore</h2>
				<a href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Our courses</a>
				<a href="<?php echo esc_url( home_url( '/graduation/' ) ); ?>">Graduation</a>
				<a href="<?php echo esc_url( home_url( '/testimonials/' ) ); ?>">Testimonials</a>
				<a href="<?php echo esc_url( home_url( '/notice-board/' ) ); ?>">Notice board</a>
				<a href="<?php echo esc_url( home_url( '/the-toolkit-foundation-copy/' ) ); ?>">Toolkit Foundation</a>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact us</a>
			</nav>

			<div class="toolkit-site-footer__contact">
				<h2>Get in touch</h2>
				<a href="tel:+254709549200"><i class="fas fa-phone-alt" aria-hidden="true"></i><span>+254 709 549 200</span></a>
				<a href="https://wa.me/254711802855" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp" aria-hidden="true"></i><span>WhatsApp +254 711 802 855</span></a>
				<a href="mailto:office@toolkitafrica.ac.ke"><i class="far fa-envelope" aria-hidden="true"></i><span>office@toolkitafrica.ac.ke</span></a>
				<p><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span>Karen-Kikuyu Southern Bypass, Kikuyu, Kenya</span></p>
				<div class="toolkit-site-footer__socials" aria-label="Toolkit social media">
					<a href="https://www.tiktok.com/@thetoolkitafrika" aria-label="TikTok" title="TikTok"><i class="fab fa-tiktok" aria-hidden="true"></i></a>
					<a href="https://www.facebook.com/toolkitafrica" aria-label="Facebook" title="Facebook"><i class="fab fa-facebook-f" aria-hidden="true"></i></a>
					<a href="https://x.com/toolkitafrica" aria-label="X" title="X"><i class="fab fa-twitter" aria-hidden="true"></i></a>
					<a href="https://www.instagram.com/thetoolkitafrika" aria-label="Instagram" title="Instagram"><i class="fab fa-instagram" aria-hidden="true"></i></a>
					<a href="https://www.linkedin.com/company/the-toolkit-iskills-tti-ltd" aria-label="LinkedIn" title="LinkedIn"><i class="fab fa-linkedin-in" aria-hidden="true"></i></a>
					<a href="https://www.youtube.com/@toolkitafrica" aria-label="YouTube" title="YouTube"><i class="fab fa-youtube" aria-hidden="true"></i></a>
					<a href="https://whatsapp.com/channel/0029Vb6PfqR5Ejy79JAJlb1f" aria-label="WhatsApp channel" title="WhatsApp"><i class="fab fa-whatsapp" aria-hidden="true"></i></a>
				</div>
			</div>

			<div class="toolkit-site-footer__action">
				<h2>Ready to learn?</h2>
				<p>Explore practical training and choose the pathway that fits your goals.</p>
				<a class="toolkit-site-footer__button" href="<?php echo esc_url( home_url( '/apply/' ) ); ?>">Apply now <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
			</div>
		</div>
	</div>
	<div class="toolkit-site-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> The Toolkit for Skills and Innovation. All rights reserved.</p>
	</div>
</footer>
<?php $support_settings = function_exists( 'toolkit_support_settings' ) ? toolkit_support_settings() : array( 'enabled' => 0 ); ?>
<?php if ( ! empty( $support_settings['enabled'] ) ) : ?>
<aside class="toolkit-chat" aria-label="Toolkit website assistant">
	<button class="toolkit-chat__toggle" data-metric="chat_open" type="button" aria-expanded="false" aria-controls="toolkit-chat-panel"><i class="far fa-comment-dots" aria-hidden="true"></i><span>Need help?</span></button>
	<div id="toolkit-chat-panel" class="toolkit-chat__panel" hidden>
		<header><div><strong>Toolkit Assistant</strong><span>Information, enquiries and feedback</span></div><button type="button" data-chat-close aria-label="Close assistant"><i class="fas fa-times" aria-hidden="true"></i></button></header>
		<div class="toolkit-chat__messages" aria-live="polite"><p class="is-assistant">Loading support options...</p></div>
		<div class="toolkit-chat__choices"></div>
	</div>
</aside>
<?php endif; ?>
</div><!-- #main-content -->
<?php do_action( 'thim_end_content_pusher' ); ?>
</div><!-- .content-pusher -->
<?php do_action( 'thim_end_wrapper_container' ); ?>
</div><!-- .wrapper-container -->
<?php wp_footer(); ?>
</body>
</html>
