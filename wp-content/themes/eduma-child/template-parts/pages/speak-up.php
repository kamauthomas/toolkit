<?php
/** Dedicated, privacy-conscious speak-up page. */
get_header();
?>
<main class="toolkit-speak-up" id="main-content">
	<section class="toolkit-speak-up__intro">
		<p class="toolkit-eyebrow">A safer way to raise a concern</p>
		<h1>Speak up safely</h1>
		<p class="toolkit-speak-up__lede">Share a concern about safety, misconduct, fraud, harassment, or another serious issue. Reports are kept separate from ordinary website enquiries for restricted review.</p>
		<div class="toolkit-speak-up__notice"><strong>Before you continue</strong><span>Do not use this form for emergencies. Call local emergency services. Do not include passwords, financial account details, or information that could put someone at immediate risk. This form is not a guarantee of legal anonymity.</span></div>
	</section>
	<section class="toolkit-speak-up__card" aria-labelledby="speak-up-form-title">
		<h2 id="speak-up-form-title">Submit a report</h2>
		<form class="toolkit-speak-up__form" data-speak-up-form>
			<label>Concern type<select name="category" required><option value="">Select one</option><option value="safety">Safety concern</option><option value="misconduct">Misconduct</option><option value="fraud">Fraud or misuse of resources</option><option value="harassment">Harassment or discrimination</option><option value="other">Another serious concern</option></select></label>
			<label>What happened?<textarea name="report" rows="8" minlength="30" maxlength="5000" required placeholder="Share dates, locations, people involved, and what you observed. Avoid unnecessary identifying details."></textarea></label>
			<label class="toolkit-speak-up__check"><input type="checkbox" name="contact_me" value="1" data-contact-toggle> I would like Toolkit to follow up with me.</label>
			<div class="toolkit-speak-up__contact" data-contact-fields hidden><label>Name<input name="name" maxlength="100" autocomplete="name"></label><label>Email<input name="email" type="email" maxlength="160" autocomplete="email"></label><label>Phone<input name="phone" type="tel" maxlength="40" autocomplete="tel"></label></div>
			<label class="toolkit-speak-up__check"><input type="checkbox" name="consent" value="1" required> I understand this report will be stored for restricted review and may be shared only with authorised reviewers who need it to respond.</label>
			<input class="toolkit-speak-up__trap" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
			<input type="hidden" name="page" value="/speak-up/">
			<button class="toolkit-button toolkit-button--primary" type="submit">Submit report</button><p class="toolkit-speak-up__status" role="status"></p>
		</form>
	</section>
	<section class="toolkit-speak-up__direct"><h2>Prefer a direct channel?</h2><p>Call or WhatsApp The Toolkit Director. Contact: <a href="tel:+254102802855">0102802855</a>. Email address: <a href="mailto:speakup@toolkitafrica.ac.ke">speakup@toolkitafrica.ac.ke</a>. Say that your message is a speak-up report.</p></section>
</main>
<script>
(function(){var f=document.querySelector('[data-speak-up-form]');if(!f)return;var t=f.querySelector('[data-contact-toggle]'),c=f.querySelector('[data-contact-fields]'),s=f.querySelector('.toolkit-speak-up__status');t.addEventListener('change',function(){c.hidden=!t.checked;});f.addEventListener('submit',function(e){e.preventDefault();var b=f.querySelector('button'),d={};new FormData(f).forEach(function(v,k){d[k]=v;});d.contact_me=!!d.contact_me;d.consent=!!d.consent;b.disabled=true;s.textContent='Sending securely…';fetch('<?php echo esc_url( rest_url( 'toolkit/v1/speak-up' ) ); ?>',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify(d)}).then(function(r){return r.json().then(function(x){if(!r.ok)throw Error(x.message||'The report could not be saved.');return x;});}).then(function(x){f.reset();c.hidden=true;s.textContent=x.message;}).catch(function(x){s.textContent=x.message;b.disabled=false;});});}());
</script>
<?php get_footer(); ?>
