<?php
/** Current fees: November Principal notice for welding; retained September poster otherwise. */
function get_stylesheet_directory_uri() { return 'https://toolkitafrica.ac.ke/wp-content/themes/eduma-child'; }
function home_url( $path = '/' ) { return 'https://toolkitafrica.ac.ke' . $path; }
require __DIR__ . '/../../wp-content/themes/eduma-child/inc/course-catalog.php';
function verify( $condition, $message ) { if ( ! $condition ) throw new RuntimeException( $message ); }
$courses = eduma_child_2026_course_catalog();
$expected = array(
 'advanced-welding-vr' => 'KES 176,276',
 'advanced-welding-upskilling' => 'KES 66,300',
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
verify( !isset($courses['advanced-welding-upskilling']['fee_options']), 'Superseded categories still advertised' );
verify( '1 month' === $courses['advanced-welding-upskilling']['duration'], 'Incorrect welding upskilling duration' );
verify( '15 days' === $courses['solar-upskilling']['duration'], 'Incorrect solar upskilling duration' );
verify( array('Grade I'=>'KES 9,950','Grade II'=>'KES 8,050','Grade III'=>'KES 6,750','Solar T1'=>'KES 7,050','Solar T2'=>'KES 8,450') === eduma_child_published_exam_fees(), 'Incorrect separate exam fees' );
$legacy=eduma_child_legacy_course_catalog();
verify( 'KES 176,276' === $legacy['welding-and-fabrication']['fees'], 'Legacy welding is stale' );
verify( !isset($legacy['welding-and-fabrication']['fee_options']) && str_contains($legacy['welding-and-fabrication']['pricing_note'],'KES 66,300'), 'Legacy welding retains superseded options' );
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
function is_page($slug='') { return true; }
function get_queried_object_id() { return 1; }
function get_post_field($key,$id) { return $GLOBALS['post_slug'] ?? 'construction-sector-skills'; }
function get_header() {}
function get_footer() {}
function esc_html($value) { return htmlspecialchars((string)$value); }
function esc_attr($value) { return esc_html($value); }
function esc_url($value) { return esc_html($value); }
function render_pricing_template($name) {
 ob_start(); include get_stylesheet_directory().'/template-parts/pages/'.$name.'.php'; return ob_get_clean();
}
$obsolete_welding = array('KES 127,895','KES 55,017','KES 34,375','KES 39,575','KES 39,315','KES 44,515');
foreach (array('advanced-welding-vr','advanced-welding-upskilling','') as $current_slug) {
 $html = render_pricing_template('course-detail');
 verify(str_contains($html,'Approved training cost') && str_contains($html,'official approved training costs'), 'Missing approved cost authority');
 foreach ($obsolete_welding as $old) verify(!str_contains($html,$old),'Obsolete welding fee: '.$old);
 verify(!str_contains($html,'revised September 2026') && !str_contains($html,'All five upskilling categories'),'Old authority on welding');
 verify(!str_contains($html,'/our-ventures/#exam-fees'),'Unapproved additive exam fee claim on welding');
 verify(str_contains($html,'Youth Skills Manager') && str_contains($html,'eligibility'),'Missing financing guidance');
 verify(!str_contains($html,'Principal') && !str_contains($html,'10 November 2026') && !str_contains($html,'.pdf'),'Local notice leaked into course page');
 verify(str_contains($html,$current_slug==='advanced-welding-upskilling'?'KES 66,300':'KES 176,276'),'Wrong rendered approved cost');
}
foreach ($expected as $current_slug => $fee) {
 $html=render_pricing_template('course-detail');
 verify(str_contains($html,$fee),'Missing programme fee: '.$current_slug);
}
$current_slug='solar-upskilling';
$html=render_pricing_template('course-detail');
verify(str_contains($html,'/our-ventures/#exam-fees') && str_contains($html,'KES 30,684'),'Non-welding pricing or assessment changed');
$current_slug='';$post_slug='recognition-of-prior-learning-rpl';
verify(!str_contains(render_pricing_template('course-detail'),'KES '),'Unpriced RPL gained a fee');
$current_slug='advanced-welding-upskilling';
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
 $welding=in_array($slug,array('advanced-welding-vr','advanced-welding-upskilling'),true);
 $pages[ '/courses/' . $slug . '/' ] = array( $fees, $welding?'Approved training cost':'Tuition fee', $welding?'official approved training costs':'revised September 2026' );
}
$pages['/courses/advanced-welding-upskilling/'][] = '1 month';
$pages['/courses/solar-upskilling/'][] = '15 days';
foreach ( array('french-a1','french-a2','french-b1','german-a1','german-a2','german-b1') as $slug ) $pages['/courses/'.$slug.'/'][] = '3 months';
$pages['/our-ventures/'] = array_merge( array_values($expected), array_keys(eduma_child_published_exam_fees()), array_values(eduma_child_published_exam_fees()), array('Published exam fees','revised September 2026') );
$pages['/our-ventures/construction-sector-skills/'] = array('KES 176,276','KES 66,300','Approved training cost','official approved training costs');
$pages['/our-ventures/renewable-energy/'] = array('KES 120,962','KES 30,684 for 15 days');
$pages['/our-ventures/organic-farming-skills/'] = array($expected['smart-agriculture']);
$pages['/our-ventures/access-online-jobs/'] = array($expected['digital-skills-technology']);
$pages['/our-ventures/construction-sector-skills/recognition-of-prior-learning-rpl/'] = array('Contact admissions for current fees and available funding options.');
$pages['/apply/'] = array('id="toolkit-application-form"');
$pages['/llms.txt'] = array('KES 176,276','KES 66,300');
if ( in_array('--manifest', $argv, true) ) {
 echo json_encode(array('pages'=>$pages, 'obsolete'=>array_merge($obsolete_welding,array('KES 73,586','KES 150,000','KES 45,000','KES 40,000','KES 50,000','package: KES','7 July 2026 admissions brochure'))), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
} else {
 echo "PASS: 15 programme fees, approved November welding costs, 5 exam rates, duration corrections and legacy mappings\n";
}
