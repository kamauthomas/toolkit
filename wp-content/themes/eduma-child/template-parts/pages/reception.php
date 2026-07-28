<?php
get_header();
$active = toolkit_reception_submission_enabled();
?>
<main id="main-content" class="toolkit-page toolkit-reception-page">
	<section class="reception-hero">
		<div>
			<p class="toolkit-kicker">Plan your visit</p>
			<h1>Connect with reception before you arrive</h1>
			<p>Share a few details securely with our reception team. They can prepare for your enquiry and follow up with you directly.</p>
		</div>
	</section>
	<section class="reception-layout">
		<div class="reception-guide">
			<p class="toolkit-kicker">Toolkit Skills &amp; Innovation Hub</p>
			<h2>Make your visit count</h2>
			<ul>
				<li>Tell reception why you would like to visit.</li>
				<li>Receive a reference when your details have been recorded.</li>
				<li>Reception will review your request and follow up where needed.</li>
			</ul>
			<p class="reception-direct"><strong>Need immediate assistance?</strong><br><a href="tel:+254709549200">Call +254 709 549 200</a></p>
		</div>
		<div class="reception-card">
			<p class="toolkit-kicker">Reception request</p>
			<h2>How can we receive you?</h2>
			<?php if ( $active ) : ?>
				<form class="reception-form" data-reception-form>
					<label>Full name <input name="name" type="text" minlength="2" maxlength="120" autocomplete="name" required></label>
					<label>Phone number <input name="phone" type="tel" minlength="7" maxlength="24" autocomplete="tel" required></label>
					<label>Email address <input name="email" type="email" maxlength="160" autocomplete="email"></label>
					<label>Organization <input name="organization" type="text" maxlength="120" autocomplete="organization"></label>
					<label class="full">Purpose of visit
						<select name="purpose" required>
							<option value="">Choose one</option>
							<option value="course_enquiry">Course enquiry</option>
							<option value="partnership">Partnership</option>
							<option value="meeting">Meeting</option>
							<option value="delivery">Delivery</option>
							<option value="event">Event</option>
							<option value="other">Other</option>
						</select>
					</label>
					<label class="reception-hp" aria-hidden="true">Website <input name="website" type="text" tabindex="-1" autocomplete="off"></label>
					<label class="reception-consent"><input name="consent" type="checkbox" value="1" required><span>I consent to Toolkit Africa using these details to manage and follow up on my reception request.</span></label>
					<button type="submit">Send to reception</button>
					<p class="reception-form-status" data-form-status role="status" aria-live="polite"></p>
					<p class="reception-privacy">This form sends your details to Toolkit Africa’s reception system. It does not check you in; reception records physical arrival separately.</p>
				</form>
			<?php else : ?>
				<p>Online reception is being prepared. Please call <a href="tel:+254709549200">+254 709 549 200</a> for assistance.</p>
			<?php endif; ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
