<?php
/** Expected amounts independently transcribed from the retained September poster. */
function get_stylesheet_directory_uri() { return 'https://toolkitafrica.ac.ke/wp-content/themes/eduma-child'; }
function home_url( $path = '/' ) { return 'https://toolkitafrica.ac.ke' . $path; }
require __DIR__ . '/../../wp-content/themes/eduma-child/inc/course-catalog.php';
function verify( $condition, $message ) { if ( ! $condition ) throw new RuntimeException( $message ); }
$courses = eduma_child_2026_course_catalog();
$expected = array(
 'advanced-welding-vr' => 'KES 127,895',
 'advanced-welding-upskilling' => 'KES 34,375 to KES 55,017, depending on category',
 'electrical-installation' => 'KES 120,962',
 'solar-pv-installer' => 'KES 120,962',
 'solar-electrician' => 'KES 120,962',
 'solar-upskilling' => 'KES 30,684',
 'smart-agriculture' => 'KES 73,568; short courses from KES 4,000',
 'digital-skills-technology' => 'KES 18,000 to KES 65,000, depending on course',
 'entrepreneurship' => 'KES 8,000 to KES 35,000, depending on course',
);
foreach ( array( 'french-a1','french-a2','french-b1','german-a1','german-a2','german-b1' ) as $slug ) {
 $expected[ $slug ] = 'KES 35,000 per level';
 verify( '3 months' === $courses[ $slug ]['duration'], "Incorrect language duration: $slug" );
 verify( empty( $courses[ $slug ]['pricing_note'] ), "Obsolete language package: $slug" );
}
verify( 15 === count( $courses ), 'Programme coverage changed' );
foreach ( $expected as $slug => $fees ) verify( $fees === $courses[ $slug ]['fees'], "Incorrect tuition: $slug" );
$categories = array('On-campus'=>'KES 55,017','Industry (Nairobi Metropolitan)'=>'KES 34,375','Industry (Upcountry)'=>'KES 39,575','TVET (Nairobi Metropolitan)'=>'KES 39,315','TVET (Upcountry)'=>'KES 44,515');
verify( $categories === $courses['advanced-welding-upskilling']['fee_options'], 'Welding category mix-up' );
verify( '1 month' === $courses['advanced-welding-upskilling']['duration'], 'Incorrect welding upskilling duration' );
verify( '15 days' === $courses['solar-upskilling']['duration'], 'Incorrect solar upskilling duration' );
verify( array('Grade I'=>'KES 9,950','Grade II'=>'KES 8,050','Grade III'=>'KES 6,750','Solar T1'=>'KES 7,050','Solar T2'=>'KES 8,450') === eduma_child_published_exam_fees(), 'Incorrect separate exam fees' );
$legacy=eduma_child_legacy_course_catalog();
verify( 'KES 127,895' === $legacy['welding-and-fabrication']['fees'], 'Legacy welding is stale' );
verify( $categories === $legacy['welding-and-fabrication']['fee_options'], 'Legacy welding categories are stale' );
verify( 'KES 120,962' === $legacy['renewable-energy']['fees'], 'Legacy solar is stale' );
verify( $expected['smart-agriculture'] === $legacy['organic-farming-skills']['fees'], 'Legacy agriculture is stale' );
verify( ! isset($legacy['recognition-prior-learning']['fees']), 'Unpriced RPL acquired a fee' );
// Exercise the real templates, including the existing pricing-off control.
$pricing_enabled = true;
$current_slug = 'advanced-welding-upskilling';
function get_stylesheet_directory() { return __DIR__ . '/../../wp-content/themes/eduma-child'; }
function eduma_child_2026_catalog_enabled() { return true; }
function eduma_child_2026_pricing_enabled() { return $GLOBALS['pricing_enabled']; }
function eduma_child_switch(...$args) { return true; }
function get_query_var($key) { return $GLOBALS['current_slug']; }
function sanitize_key($value) { return $value; }
function get_header() {}
function get_footer() {}
function esc_html($value) { return htmlspecialchars((string)$value); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_html($value); }
function render_pricing_template($name) {
 ob_start(); include get_stylesheet_directory().'/template-parts/pages/'.$name.'.php'; return ob_get_clean();
}
$html = render_pricing_template('course-detail');
foreach ($categories as $label => $fee) verify(str_contains($html,$label) && str_contains($html,$fee),'Missing rendered welding category: '.$label);
verify(str_contains($html,'/our-ventures/#exam-fees'),'Missing separate exam fee link');
$html = render_pricing_template('courses');
verify(15 === substr_count($html,'toolkit-course-directory__fee'),'Missing directory tuition cards');
foreach(eduma_child_published_exam_fees() as $label=>$fee) verify(str_contains($html,$label) && str_contains($html,$fee),'Missing rendered separate exam: '.$label);
$pricing_enabled=false;
$html=render_pricing_template('course-detail');
verify(!str_contains($html,'KES ') && !str_contains($html,'Welding upskilling tuition'),'Pricing-off leaks category fees');
$html=render_pricing_template('courses');
verify(!str_contains($html,'KES ') && !str_contains($html,'Published exam fees'),'Pricing-off leaks exam fees');
$pricing_enabled=true;
$pages = array();
foreach ( $expected as $slug => $fees ) {
 $pages[ '/courses/' . $slug . '/' ] = array( $fees, 'Tuition fee', 'revised September 2026' );
}
$pages['/courses/advanced-welding-upskilling/'] = array_merge( $pages['/courses/advanced-welding-upskilling/'], array_values($categories), array_keys($categories), array('1 month') );
$pages['/courses/solar-upskilling/'][] = '15 days';
foreach ( array('french-a1','french-a2','french-b1','german-a1','german-a2','german-b1') as $slug ) $pages['/courses/'.$slug.'/'][] = '3 months';
$pages['/our-ventures/'] = array_merge( array_values($expected), array_keys(eduma_child_published_exam_fees()), array_values(eduma_child_published_exam_fees()), array('Published exam fees','revised September 2026') );
$pages['/our-ventures/construction-sector-skills/'] = array_merge(array('KES 127,895','Welding upskilling tuition'),array_values($categories),array_keys($categories));
$pages['/our-ventures/renewable-energy/'] = array('KES 120,962','KES 30,684 for 15 days');
$pages['/our-ventures/organic-farming-skills/'] = array($expected['smart-agriculture']);
$pages['/our-ventures/access-online-jobs/'] = array($expected['digital-skills-technology']);
$pages['/our-ventures/construction-sector-skills/recognition-of-prior-learning-rpl/'] = array('Contact admissions for current fees and available funding options.');
$pages['/apply/'] = array('id="toolkit-application-form"');
$pages['/llms.txt'] = array('Use the revised September 2026 tuition fees');
if ( in_array('--manifest', $argv, true) ) {
 echo json_encode(array('pages'=>$pages, 'obsolete'=>array('KES 176,276','KES 66,300','KES 73,586','KES 150,000','KES 45,000','KES 40,000','KES 50,000','package: KES','7 July 2026 admissions brochure')), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
 echo "PASS: 15 programme fees, 5 welding categories, 5 exam rates, duration corrections and legacy mappings\n";
}
