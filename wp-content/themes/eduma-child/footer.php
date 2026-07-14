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
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Toolkit Africa home">
					<img src="<?php echo esc_url( $logo ); ?>" width="180" height="130" alt="Toolkit Africa">
				</a>
				<p>Practical skills, innovation, and opportunity for Africa's young people.</p>
			</div>

			<nav class="toolkit-site-footer__nav" aria-label="Footer navigation">
				<h2>Explore</h2>
				<a href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Our courses</a>
				<a href="<?php echo esc_url( home_url( '/notice-board/' ) ); ?>">Notice board</a>
				<a href="<?php echo esc_url( home_url( '/the-toolkit-foundation-copy/' ) ); ?>">Toolkit Foundation</a>
				<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact us</a>
			</nav>

			<div class="toolkit-site-footer__contact">
				<h2>Get in touch</h2>
				<a href="tel:+254709549200"><i class="fas fa-phone-alt" aria-hidden="true"></i><span>+254 709 549 200</span></a>
				<a href="mailto:office@toolkitafrica.ac.ke"><i class="far fa-envelope" aria-hidden="true"></i><span>office@toolkitafrica.ac.ke</span></a>
				<p><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span>Karen-Kikuyu Southern Bypass, Kikuyu, Kenya</span></p>
			</div>

			<div class="toolkit-site-footer__action">
				<h2>Ready to learn?</h2>
				<p>Explore practical training and choose the pathway that fits your goals.</p>
				<a class="toolkit-site-footer__button" href="<?php echo esc_url( home_url( '/our-ventures/toolkit-courses-apply-today/' ) ); ?>">Apply now <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
			</div>
		</div>
	</div>
	<div class="toolkit-site-footer__bottom">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> Toolkit Africa. All rights reserved.</p>
	</div>
</footer>
</div><!-- #main-content -->
<?php do_action( 'thim_end_content_pusher' ); ?>
</div><!-- .content-pusher -->
<?php do_action( 'thim_end_wrapper_container' ); ?>
</div><!-- .wrapper-container -->
<?php wp_footer(); ?>
</body>
</html>
