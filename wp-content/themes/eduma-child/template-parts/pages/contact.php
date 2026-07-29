<?php
get_header();
$image = get_stylesheet_directory_uri() . '/assets/images/pages/contact.jpg';
?>
<main id="main-content" class="toolkit-page toolkit-contact-page">
	<section class="toolkit-contact-hero" style="background-image:url('<?php echo esc_url( $image ); ?>')"><div><p class="toolkit-kicker">Contact Toolkit</p><h1>Let’s start a useful conversation</h1><p>Ask about courses, admissions, partnerships, or visiting The Toolkit for Skills and Innovation.</p></div></section>
	<section class="toolkit-contact-layout toolkit-section">
		<div class="toolkit-contact-details"><p class="toolkit-kicker">Get in touch</p><h2>How can we help?</h2><p>Choose the most direct route below or send a message through the form.</p><a href="tel:+254709549200"><i class="fas fa-phone-alt" aria-hidden="true"></i><span><small>Call us</small>+254 709 549 200</span></a><a href="https://wa.me/254711802855" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp" aria-hidden="true"></i><span><small>WhatsApp us</small>+254 711 802 855</span></a><a href="mailto:office@toolkitafrica.ac.ke"><i class="far fa-envelope" aria-hidden="true"></i><span><small>Email us</small>office@toolkitafrica.ac.ke</span></a><p class="toolkit-contact-address"><i class="fas fa-map-marker-alt" aria-hidden="true"></i><span><small>Visit us</small>The Toolkit for Skills and Innovation, Karen-Kikuyu Southern Bypass, Kikuyu, Kenya</span></p></div>
		<div class="toolkit-contact-form"><p class="toolkit-kicker">Send a message</p><h2>Tell us what you need</h2><?php echo do_shortcode( '[contact-form-7 id="10981" title="Contact form 1"]' ); ?></div>
	</section>
	<section class="toolkit-contact-map"><iframe loading="lazy" src="https://maps.google.com/maps?q=Toolkit%20Skills%20and%20Innovation%20Hub&amp;t=m&amp;z=12&amp;output=embed&amp;iwloc=near" title="The Toolkit for Skills and Innovation location"></iframe></section>
</main>
<?php get_footer();
