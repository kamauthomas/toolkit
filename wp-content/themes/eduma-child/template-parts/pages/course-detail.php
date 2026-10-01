<?php
require_once get_stylesheet_directory() . '/inc/course-catalog.php';
$slug   = sanitize_key( get_query_var( 'toolkit_course' ) );
$course = $slug ? eduma_child_get_course( $slug ) : eduma_child_get_legacy_course_for_page();
if ( ! $course ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	include get_404_template();
	return;
}
$apply_url  = home_url( '/apply/?course=' . rawurlencode( $course['title'] ) );
$kicker     = $slug || ! empty( $course['brochure_pricing'] ) ? 'Revised September 2026 fees' : 'Toolkit course pathway';
$course_key = $slug ? $slug : eduma_child_get_legacy_course_key_for_page();
$experience = eduma_child_course_experience( $course_key );
$is_welding_hub       = 'welding-and-fabrication' === $course_key;
$has_published_pricing = eduma_child_2026_pricing_enabled() && ! empty( $course['fees'] ) && ( $slug || ! empty( $course['brochure_pricing'] ) );
$page_heading          = $is_welding_hub ? 'MIG/MAG Welding Training in Kenya' : $course['title'];
get_header();
?>
<main id="main-content" class="toolkit-page toolkit-course-page toolkit-catalog-course toolkit-course--<?php echo esc_attr( $experience['theme'] ); ?>">
	<div class="toolkit-breadcrumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a><span>/</span><a href="<?php echo esc_url( home_url( '/our-ventures/' ) ); ?>">Our Courses</a><span>/</span><span><?php echo esc_html( $course['title'] ); ?></span></div>
	<section class="toolkit-course-hero toolkit-course-hero--immersive" style="background-image:url('<?php echo esc_url( $course['image'] ); ?>')">
		<div class="toolkit-course-hero__content"><p class="toolkit-kicker"><?php echo esc_html( $kicker ); ?></p><h1><?php echo esc_html( $page_heading ); ?></h1><p><?php echo esc_html( $course['short'] ); ?></p><div class="toolkit-actions"><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $apply_url ); ?>">Apply now</a><a class="toolkit-btn toolkit-btn--secondary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Ask admissions</a></div></div>
		<div class="toolkit-course-hero__rail" aria-label="Learning approach"><span><i class="fas fa-hands-helping"></i> Guided practice</span><span><i class="fas fa-tools"></i> Applied learning</span><span><i class="fas fa-route"></i> Progression support</span></div>
	</section>
	<nav class="toolkit-course-dock" aria-label="Course quick actions">
		<div class="toolkit-course-dock__identity"><span>Current course</span><strong><?php echo esc_html( $page_heading ); ?></strong></div>
		<div class="toolkit-course-dock__fact"><span><?php echo $has_published_pricing ? 'Tuition fee' : 'Schedule'; ?></span><strong><?php echo esc_html( $has_published_pricing ? $course['fees'] : $course['duration'] ); ?></strong></div>
		<a class="toolkit-course-dock__details" href="#course-details">View details</a>
		<a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $apply_url ); ?>">Apply now <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
	</nav>
	<section class="toolkit-course-scene"><div class="toolkit-course-scene__copy"><p class="toolkit-kicker"><?php echo esc_html( $experience['eyebrow'] ); ?></p><h2><?php echo esc_html( $experience['heading'] ); ?></h2><p><?php echo esc_html( $experience['intro'] ); ?></p><div class="toolkit-course-scene__moments"><?php foreach ( $experience['moments'] as $moment ) : ?><article><i class="fas <?php echo esc_attr( $moment[0] ); ?>"></i><div><h3><?php echo esc_html( $moment[1] ); ?></h3><p><?php echo esc_html( $moment[2] ); ?></p></div></article><?php endforeach; ?></div></div><?php if ( $experience['secondary'] ) : ?><figure><img src="<?php echo esc_url( $experience['secondary'] ); ?>" width="900" height="1100" loading="lazy" alt="<?php echo esc_attr( $experience['secondary_alt'] ); ?>"><figcaption><?php echo esc_html( $experience['eyebrow'] ); ?></figcaption></figure><?php endif; ?></section>
	<section id="course-details" class="toolkit-section toolkit-course-data"><div><p class="toolkit-kicker">Course facts</p><h2>Plan your training</h2><p><?php echo $has_published_pricing ? 'Tuition fees below are from Toolkit’s revised September 2026 Kikuyu Campus poster. Exam fees are listed separately. Admissions can advise on installments, financing, subsidies, and intake availability.' : 'Admissions will confirm programme availability, eligibility, schedules, and current costs before enrolment.'; ?></p></div><dl><div><dt>Qualification</dt><dd><?php echo esc_html( $course['qualification'] ); ?></dd></div><div><dt>Examining body</dt><dd><?php echo esc_html( $course['examining'] ); ?></dd></div><div><dt>Duration</dt><dd><?php echo esc_html( $course['duration'] ); ?></dd></div><div><dt>Entry requirement</dt><dd><?php echo esc_html( $course['entry'] ); ?></dd></div><?php if ( $has_published_pricing ) : ?><div><dt>Tuition fee</dt><dd><?php echo esc_html( $course['fees'] ); ?><?php if ( ! empty( $course['pricing_note'] ) ) : ?><small><?php echo esc_html( $course['pricing_note'] ); ?></small><?php endif; ?></dd></div><div><dt>Exam fees</dt><dd>Listed separately; Admissions will confirm the assessment for your course. <a href="<?php echo esc_url( home_url( '/our-ventures/#exam-fees' ) ); ?>">View published exam fees</a>.</dd></div><?php else : ?><div><dt>Fees</dt><dd>Contact admissions for current fees and available funding options.</dd></div><?php endif; ?><div><dt>Intakes</dt><dd><?php echo esc_html( $course['intakes'] ); ?></dd></div></dl></section>
	<?php if ( $has_published_pricing && ! empty( $course['fee_options'] ) ) : ?>
	<section class="toolkit-section toolkit-course-data"><div><p class="toolkit-kicker">Delivery categories</p><h2>Welding upskilling tuition</h2><p>All five upskilling categories run for one month. Choose the fee that matches your delivery category and location.</p></div><dl><?php foreach ( $course['fee_options'] as $category => $fee ) : ?><div><dt><?php echo esc_html( $category ); ?></dt><dd><?php echo esc_html( $fee ); ?></dd></div><?php endforeach; ?></dl></section>
	<?php endif; ?>
	<section class="toolkit-section toolkit-course-outcomes"><div><p class="toolkit-kicker">Capability map</p><h2>What you build along the way</h2></div><div class="toolkit-skill-grid"><?php foreach ( $course['outcomes'] as $index => $outcome ) : ?><article><span>0<?php echo esc_html( $index + 1 ); ?></span><i class="fas fa-check-circle"></i><h3><?php echo esc_html( $outcome ); ?></h3></article><?php endforeach; ?></div></section>
	<?php if ( $is_welding_hub ) : ?>
	<section class="toolkit-section toolkit-welding-guide" aria-labelledby="mig-mag-explained">
		<div class="toolkit-welding-guide__intro"><p class="toolkit-kicker">Welding course guide</p><h2 id="mig-mag-explained">MIG and MAG welding: what learners should know</h2><p>MIG and MAG are wire-fed arc-welding processes. MIG uses an inert shielding gas, while MAG uses an active gas or gas mixture. The appropriate process, gas, settings and technique depend on the material, joint and approved welding procedure.</p></div>
		<div class="toolkit-welding-guide__grid">
			<article><h3>Start with safe setup</h3><p>Learn to assess the work area, select personal protective equipment, prepare the machine and understand the purpose of each setting before welding.</p></article>
			<article><h3>Build repeatable control</h3><p>Practise torch angle, travel speed, position and consistency with instructor feedback instead of relying on trial and error alone.</p></article>
			<article><h3>Inspect and improve</h3><p>Review weld appearance, identify common defects and connect each result to preparation, settings and technique.</p></article>
		</div>
	</section>
	<section class="toolkit-section toolkit-welding-pathways" aria-labelledby="welding-pathways-heading">
		<div><p class="toolkit-kicker">Choose a pathway</p><h2 id="welding-pathways-heading">One welding hub, three distinct learner needs</h2><p>This page introduces Toolkit’s broad MIG/MAG training pathway. Use the specialist pages when your main goal is simulation-supported practice or recognition of skills gained through work.</p></div>
		<div class="toolkit-welding-pathways__grid">
			<article><span>01</span><h3>Practical MIG/MAG training</h3><p>Build workshop safety, process setup, torch control and weld-quality awareness through guided practice.</p><strong aria-current="page">You are viewing this pathway</strong></article>
			<article><span>02</span><h3>VR-supported welding</h3><p>Understand how Toolkit uses simulation for controlled rehearsal and feedback before skills are reinforced through guided workshop practice.</p><a href="<?php echo esc_url( home_url( '/our-ventures/construction-sector-skills/training-welders-with-virtual-reality/' ) ); ?>">Explore virtual-reality welding training <i class="fas fa-arrow-right" aria-hidden="true"></i></a></article>
			<article><span>03</span><h3>Recognition of Prior Learning</h3><p>For people with relevant work experience, the RPL route focuses on documenting existing competence, identifying gaps and preparing for assessment.</p><a href="<?php echo esc_url( home_url( '/our-ventures/construction-sector-skills/recognition-of-prior-learning-rpl/' ) ); ?>">Explore the RPL assessment pathway <i class="fas fa-arrow-right" aria-hidden="true"></i></a></article>
		</div>
	</section>
	<section class="toolkit-section toolkit-welding-evidence" aria-labelledby="welding-evidence-heading">
		<div><p class="toolkit-kicker">First-hand evidence</p><h2 id="welding-evidence-heading">See Toolkit’s welding work in context</h2><p>Explore training demonstrations, instructor development, career conversations and workshop media published by Toolkit.</p></div>
		<ul>
			<li><a href="<?php echo esc_url( home_url( '/careers-in-mig-mag-welding-insights-from-our-webinar/' ) ); ?>">MIG/MAG welding careers webinar</a></li>
			<li><a href="<?php echo esc_url( home_url( '/empowering-kenyan-welding-instructors-a-journey-of-excellence-and-inclusion/' ) ); ?>">Kenyan welding instructor development</a></li>
			<li><a href="<?php echo esc_url( home_url( '/toolkit-vr-welding-demonstration-by-nita-director-general-stephen-ogenga/' ) ); ?>">Toolkit VR welding demonstration</a></li>
			<li><a href="<?php echo esc_url( home_url( '/tti-media/' ) ); ?>">Toolkit training videos</a></li>
		</ul>
	</section>
	<?php endif; ?>
	<section class="toolkit-section toolkit-course-faq"><div><p class="toolkit-kicker">Course questions</p><h2>Before you apply</h2></div><div><details><summary>Where is this course offered?</summary><p>The Toolkit for Skills and Innovation delivers practical training on the Karen-Kikuyu Southern Bypass in Kikuyu, Kenya. Admissions will confirm the delivery location for your intake.</p></details><details><summary>How do I confirm current fees and intake dates?</summary><p><?php echo $has_published_pricing ? 'Use the revised September 2026 tuition fee above, then contact Admissions about installment plans, financing, subsidy eligibility, intake availability, and any course-specific assessment costs.' : 'Contact Toolkit Admissions before enrolment. The team will confirm current fees, funding options, schedules, availability, and any assessment costs for this course.'; ?></p></details><?php if ( $is_welding_hub ) : ?><details><summary>Does virtual reality replace workshop welding practice?</summary><p>No. Toolkit uses VR-supported learning as a controlled place to rehearse movement, setup and process awareness. Learners then reinforce technique through instructor-guided workshop practice.</p></details><details><summary>Which Toolkit welding pathway should I choose?</summary><p>Use this MIG/MAG welding page for the broad training route, the VR welding page for simulation-led methodology, and the Recognition of Prior Learning page if you already have relevant workplace skills. Admissions will confirm the suitable level.</p></details><details><summary>Which body assesses or certifies the welding course?</summary><p>The assessment and certification route depends on the selected programme. Ask Admissions to confirm the current examining or assessment body, qualification wording, assessment costs and required documents in writing before enrolment.</p></details><?php endif; ?><details><summary>How do I apply?</summary><p>Use the Apply now link on this page to begin the application process. Admissions can help you confirm the suitable course level and required documents.</p></details></div></section>
	<section class="toolkit-support"><i class="fas fa-headset"></i><div><h2>Ready to take the next step?</h2><p>Start your application or ask admissions to confirm the right level, intake, and funding options.</p></div><a class="toolkit-btn toolkit-btn--primary" href="<?php echo esc_url( $apply_url ); ?>">Apply now <i class="fas fa-arrow-right"></i></a></section>
</main>
<?php get_footer();
