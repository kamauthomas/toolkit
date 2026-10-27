<?php
/** Isolated real catalogue/hooks/template tests; no database, network or submissions. */
$root=dirname(__DIR__,2).'/wp-content/themes/eduma-child';$hooks=[];$query=[];$redesign=true;$catalog=true;$demo=false;$disabled=[];$checks=0;
function add_filter($name,$callback,$priority=10,$args=1){$GLOBALS['hooks'][$name][$priority][]=$callback;}
function add_action($name,$callback,$priority=10,$args=1){add_filter($name,$callback,$priority,$args);}
function apply_filters($name,$value){$all=$GLOBALS['hooks'][$name]??[];ksort($all);foreach($all as $callbacks)foreach($callbacks as $fn)$value=$fn($value);return $value;}
function emit($name){$all=$GLOBALS['hooks'][$name]??[];ksort($all);ob_start();foreach($all as $callbacks)foreach($callbacks as $fn)$fn();return ob_get_clean();}
function get_query_var($key){return $GLOBALS['query'][$key]??'';}
function home_url($path=''){return ($GLOBALS['demo']?'https://demo.toolkitafrica.ac.ke':'https://toolkitafrica.ac.ke').$path;}
function get_stylesheet_directory_uri(){return home_url('/wp-content/themes/eduma-child');}
function get_stylesheet_directory(){return $GLOBALS['root'];}
function sanitize_key($value){return preg_replace('/[^a-z0-9_-]/','',strtolower($value));}
function esc_url($value){return htmlspecialchars($value,ENT_QUOTES,'UTF-8');}
function esc_html($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');}
function esc_attr($value){return esc_html($value);}
function eduma_child_redesign_enabled(){return $GLOBALS['redesign'];}
function eduma_child_2026_catalog_enabled(){return $GLOBALS['catalog'];}
function toolkit_speak_up_enabled(){return true;}
function eduma_child_switch($constant,$option,$default){return !in_array($option,$GLOBALS['disabled'],true);}
function toolkit_is_demo_environment(){return $GLOBALS['demo'];}
function eduma_child_2026_pricing_enabled(){return false;}
function get_header(){}
function get_footer(){}
require $root.'/inc/seo-discovery.php';
function check($ok,$label){if(!$ok)throw new RuntimeException($label);$GLOBALS['checks']++;}

$base='<url><loc>'.home_url('/').'</loc></url>';
$xml=apply_filters('wpseo_sitemap_page_content',$base);
preg_match_all('~<loc>(.*?)</loc>~',$xml,$matches);
check(count($matches[1])===16,'All 15 enabled courses join the original homepage');
check(count(array_unique($matches[1]))===16,'No duplicated URLs');
check($xml===apply_filters('wpseo_sitemap_page_content',$xml),'Repeated sitemap filter is idempotent');
$disabled=['toolkit_course_advanced-welding-vr_enabled'];$xml=apply_filters('wpseo_sitemap_page_content',$base);check(!str_contains($xml,'/courses/advanced-welding-vr/'),'Disabled course excluded');$disabled=[];
$catalog=false;check(apply_filters('wpseo_sitemap_page_content',$base)===$base,'Disabled catalogue adds no routes');$catalog=true;
$redesign=false;check(apply_filters('wpseo_sitemap_page_content',$base)===$base,'Disabled redesign preserves sitemap');$redesign=true;

foreach([false,true] as $demo){
 foreach(['graduation','testimonials','footprint'] as $showcase){
  $query=['toolkit_showcase'=>$showcase];$html=emit('wp_head');
  check(substr_count($html,'rel="canonical"')===1,'One virtual canonical');
  check(str_contains($html,'https://toolkitafrica.ac.ke/'.$showcase.'/'),'Canonical always targets production');
  check(apply_filters('wpseo_canonical','old')===false,'Suppress second Yoast canonical only for owned virtual page');
 }
 foreach(eduma_child_course_catalog() as $slug=>$course){
  $query=['toolkit_course'=>$slug];$html=emit('wp_head');
  check(substr_count($html,'rel="canonical"')===1&&str_contains($html,'https://toolkitafrica.ac.ke/courses/'.$slug.'/'),'Canonical for every enabled course');
  $url=home_url('/courses/'.$slug.'/');$graph=[['@type'=>'Course','@id'=>$url.'#course'],['@type'=>'WebSite','@id'=>home_url('/#website')]];
  $graph=apply_filters('wpseo_schema_graph',$graph);$ids=array_column($graph,'@id');
  check(in_array($url.'#webpage',$ids,true)&&in_array($url.'#breadcrumb',$ids,true),'Page and breadcrumb fill same graph');
  check(count($graph)===4&&apply_filters('wpseo_schema_graph',$graph)===$graph,'Graph is idempotent with no duplicate nodes');
 }
}
$demo=false;
foreach(['unknown','../advanced-welding-vr','advanced-welding-vr?x=1',['array']] as $bad){$query=['toolkit_course'=>$bad];check(emit('wp_head')==='','Invalid course does not gain a public canonical');}
$query=['toolkit_showcase'=>'staff'];check(emit('wp_head')==='','Unapproved showcase ignored');
$query=['toolkit_course'=>'advanced-welding-vr','toolkit_showcase'=>'graduation'];check(str_contains(emit('wp_head'),'/courses/advanced-welding-vr/'),'Canonical follows actual course-first router precedence');
$query=['toolkit_showcase'=>'graduation','toolkit_reception'=>true];check(emit('wp_head')==='','Do not override other routed surfaces');
$query=[];check(apply_filters('wpseo_canonical','original')==='original','Existing database-backed canonical unchanged');
$query=['toolkit_showcase'=>'graduation'];$redesign=false;check(emit('wp_head')==='','No virtual canonical with redesign off');$redesign=true;
$query=['toolkit_course'=>'advanced-welding-vr'];$disabled=['toolkit_course_advanced-welding-vr_enabled'];check(emit('wp_head')==='','No canonical for disabled course');$disabled=[];
$query=[];
function is_page($slug=''){return !$slug || $slug==='construction-sector-skills';}
function get_queried_object_id(){return 1;}
function get_post_field($key,$id){return 'construction-sector-skills';}
ob_start();include $root.'/template-parts/pages/course-detail.php';$page=ob_get_clean();
foreach(['advanced-welding-vr','advanced-welding-upskilling'] as $slug)check(str_contains($page,'href="'.home_url('/courses/'.$slug.'/').'"'),'Hub directly links detailed programme');
check(str_contains($page,'6 months: 3 months')&&str_contains($page,'KCPE or KCSE')&&str_contains($page,'January, April, July and October'),'Approved programme facts reused without reinterpretation');
check(str_contains($page,'/recognition-of-prior-learning-rpl/')&&str_contains($page,'/training-welders-with-virtual-reality/'),'Existing RPL and VR guidance retained');
$catalog=false;ob_start();include $root.'/template-parts/pages/course-detail.php';$legacy=ob_get_clean();check(!str_contains($legacy,'/courses/advanced-welding-vr/'),'Hub contains no inactive catalogue link');$catalog=true;
check(eduma_child_get_course('advanced-welding-vr')['fees']==='KES 127,895','Skilling tuition matches owner instruction');
check(eduma_child_get_course('advanced-welding-upskilling')['fees']==='KES 55,017','Upskilling primary tuition matches owner instruction');
$story=toolkit_seo_graduation_update();check(str_contains($story['content'],'/graduation/#programme')&&str_contains($story['content'],'/graduation/#graduands'),'New resource article links actual approved anchors');
check(!str_contains($story['content'],'<script')&&!str_contains($story['content'],'exam results'),'Article contains no scripts or invented results');
echo $checks." SEO sitemap, canonical, schema, toggle, negative-route and real-template checks passed\n";
