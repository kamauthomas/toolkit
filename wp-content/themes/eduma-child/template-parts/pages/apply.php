<?php
get_header();
$course_key = isset( $_GET['course'] ) ? sanitize_key( wp_unslash( $_GET['course'] ) ) : '';
?>
<main id="main-content" class="toolkit-page toolkit-apply-page">
	<header class="application-shell__header">
		<div><p class="toolkit-kicker">Toolkit admissions</p><h1>Online Student Application Form</h1><p>Step-by-step application <span aria-hidden="true">•</span> Clear guidance <span aria-hidden="true">•</span> Easy completion</p></div>
		<div class="application-progress"><strong>Step <span data-step-number>1</span> of 6</strong><span data-progress-label>16% complete</span><div><i data-progress-bar></i></div></div>
	</header>

	<nav class="application-steps" aria-label="Application progress">
		<?php
		$steps = array(
			array( 'Personal Details', 'Basic information', 'far fa-user' ),
			array( 'Contact Details', 'Reachability', 'far fa-address-card' ),
			array( 'Course Selection', 'Choose your course', 'fas fa-graduation-cap' ),
			array( 'Background', 'Education & experience', 'far fa-file-alt' ),
			array( 'Documents', 'Prepare supporting files', 'far fa-file' ),
			array( 'Review & Submit', 'Confirm & submit', 'far fa-check-circle' ),
		);
		foreach ( $steps as $index => $step ) :
			?><button type="button" data-step-target="<?php echo esc_attr( $index ); ?>" <?php echo 0 === $index ? 'class="is-active" aria-current="step"' : ''; ?>><b><?php echo esc_html( $index + 1 ); ?></b><i class="<?php echo esc_attr( $step[2] ); ?>" aria-hidden="true"></i><span><?php echo esc_html( $step[0] ); ?><small><?php echo esc_html( $step[1] ); ?></small></span></button><?php
		endforeach;
		?>
	</nav>

	<div class="application-layout">
		<form id="toolkit-application-form" class="application-form" novalidate>
			<div class="application-form__heading"><i class="far fa-user" aria-hidden="true"></i><div><h2 data-step-title>1. Personal Details</h2><p data-step-help>Tell us who you are.</p></div><span><b>*</b> Required fields</span></div>
			<div class="application-message" role="status" aria-live="polite" hidden></div>
			<?php if ( ! toolkit_mzizi_submission_enabled() ) : ?>
				<div class="application-admissions-notice" role="note">
					<div><strong>Apply directly to Toolkit Admissions</strong><span>Complete the form once and keep the reference shown after submission. Admissions will review your information and contact you about the next step.</span></div>
				</div>
			<?php endif; ?>
			<input class="application-honeypot" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">

			<fieldset data-step-panel="0"><legend class="screen-reader-text">Personal details</legend><div class="application-grid application-grid--3">
				<label>First name <b>*</b><input name="first_name" autocomplete="given-name" required></label>
				<label>Middle name<input name="middle_name" autocomplete="additional-name"></label>
				<label>Surname <b>*</b><input name="surname" autocomplete="family-name" required></label>
				<label>Gender <b>*</b><select name="gender" required><option value="">Select gender</option><option value="F">Female</option><option value="M">Male</option><option value="I">Intersex</option></select></label>
				<label>Nationality<input name="nationality" autocomplete="country-name" maxlength="80" value="Kenya"></label>
			</div></fieldset>

			<fieldset data-step-panel="1" hidden><legend class="screen-reader-text">Contact details</legend><div class="application-grid application-grid--2">
				<label>Email address <b>*</b><input type="email" name="email" autocomplete="email" required></label>
				<label>County of residence <b>*</b><select name="county" autocomplete="address-level1" required><option value="">Loading counties…</option></select></label>
				<label>Primary phone <b>*</b><input type="tel" name="primary_phone" autocomplete="tel" placeholder="+254 7xx xxx xxx" required></label>
				<label>Secondary or guardian phone <b>*</b><input type="tel" name="secondary_phone" placeholder="+254 7xx xxx xxx" required></label>
			</div></fieldset>

			<fieldset data-step-panel="2" hidden><legend class="screen-reader-text">Course selection</legend><div class="application-grid application-grid--2">
				<label>Campus <b>*</b><select name="school_id" required><option value="">Loading campuses…</option></select></label>
				<label>Course interested in <b>*</b><select name="course_id" id="toolkit-course-choice" data-initial-course="<?php echo esc_attr( $course_key ); ?>" required disabled><option value="">Select a campus first</option></select></label>
				<label>Intake <b>*</b><select name="intake_id" required disabled><option value="">Select a course first</option></select></label>
				<label>Study mode<select name="study_mode"><option value="">Select mode, if applicable</option><option value="Physical">Physical</option><option value="Online">Online</option></select></label>
				<label>Who will pay the fees?<select name="sponsorship_type"><option value="">Select one, if applicable</option><option value="Self-Sponsored">Self-Sponsored</option><option value="Sponsored">Sponsored</option></select></label>
				<label>How did you hear about us?<select name="referral_source"><option value="">Loading sources…</option></select></label>
			</div></fieldset>

			<fieldset data-step-panel="3" hidden><legend class="screen-reader-text">Education and experience</legend><div class="application-grid application-grid--2">
				<label>KCSE mean grade<input name="mean_grade" maxlength="20" placeholder="e.g. C-"></label>
				<label>High school attended<input name="high_school" maxlength="160"></label>
				<label class="application-grid__wide">Other qualifications<textarea name="qualifications" rows="5" maxlength="1200" placeholder="List any relevant certificates, qualifications or prior training."></textarea></label>
			</div></fieldset>

			<fieldset data-step-panel="4" hidden><legend class="screen-reader-text">Documents</legend><div class="application-document-note"><i class="far fa-folder-open" aria-hidden="true"></i><div><h3>Prepare your supporting documents</h3><p>Admissions may request identification, education certificates or other supporting documents after reviewing your application.</p><ul><li>Use clear, readable copies.</li><li>Do not email sensitive documents unless Admissions requests them through an approved channel.</li><li>Keep originals available for verification.</li></ul></div></div></fieldset>

			<fieldset data-step-panel="5" hidden><legend class="screen-reader-text">Review and submit</legend><div id="application-review" class="application-review"></div><label class="application-consent"><input type="checkbox" name="consent" value="1" required><span>I confirm that the information is accurate and consent to The Toolkit for Skills and Innovation securely storing and processing it for admissions. I have read the <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>" target="_blank" rel="noopener">privacy notice</a>. <b>*</b></span></label><?php if ( toolkit_application_turnstile_site_key() ) : ?><div class="cf-turnstile" data-sitekey="<?php echo esc_attr( toolkit_application_turnstile_site_key() ); ?>"></div><?php endif; ?><p class="application-submit-note" data-submit-note></p></fieldset>

		<footer class="application-form__footer"><button type="button" class="toolkit-btn toolkit-btn--secondary" data-previous hidden><i class="fas fa-arrow-left" aria-hidden="true"></i> Previous</button><span>Your progress: <b data-progress-label>16% complete</b></span><button type="button" class="toolkit-btn toolkit-btn--primary" data-next>Continue <i class="fas fa-arrow-right" aria-hidden="true"></i></button><button type="submit" class="toolkit-btn toolkit-btn--primary" data-submit hidden>Submit application <i class="fas fa-arrow-right" aria-hidden="true"></i></button></footer>
		</form>

		<aside class="application-guide"><p class="toolkit-kicker">Course & field guide</p><h2 id="toolkit-course-title">Choose your course</h2><p id="toolkit-course-description">Select a programme to see its current learning focus and admissions guidance.</p><div class="application-guide__meta"><span>Duration <b id="toolkit-course-duration">Confirmed by Admissions</b></span><span>Location <b>Kikuyu, Kenya</b></span></div><h3>Application guidance</h3><ul><li><i class="fas fa-check-circle" aria-hidden="true"></i> Complete all required contact details.</li><li><i class="fas fa-check-circle" aria-hidden="true"></i> Admissions confirms current fees and intake dates.</li><li><i class="fas fa-check-circle" aria-hidden="true"></i> Keep your reference after successful submission.</li></ul><div class="application-help"><i class="far fa-lightbulb" aria-hidden="true"></i><p><strong>Need more help?</strong> Call Admissions on <a href="tel:+254709549200">+254 709 549 200</a>, WhatsApp <a href="https://wa.me/254711802855" target="_blank" rel="noopener noreferrer">+254 711 802 855</a>, or email <a href="mailto:office@toolkitafrica.ac.ke">office@toolkitafrica.ac.ke</a>.</p></div></aside>
	</div>
</main>
<?php get_footer();
