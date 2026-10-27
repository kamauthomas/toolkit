<?php
/** WP API fixture for one owned editorial publication; no database/network. */
define('TOOLKIT_SEO_PUBLISH_LIBRARY_ONLY',true);define('OBJECT','OBJECT');
$posts=[];$meta=[];$inserts=0;$updates=0;$collision=false;$insert_error=false;$publish_error=false;$checks=0;
function toolkit_seo_graduation_update(){return ['slug'=>'class-of-2026-programme-and-graduands','title'=>'Class of 2026: programme and graduands','seo_title'=>'Class of 2026 | Toolkit','excerpt'=>'Approved resource overview','image'=>'https://toolkitafrica.ac.ke/approved.webp','content'=>'<p>Approved programme and graduands resources.</p>'];}
function wp_kses_post($content){return $content;}
function wp_slash($data){return $data;}
function get_page_by_path($slug,$kind,$types){foreach($GLOBALS['posts'] as $p)if($p->post_name===$slug)return clone $p;return null;}
function get_post_meta($id,$key,$single){return $GLOBALS['meta'][$id][$key]??'';}
function wp_insert_post($data,$error){$GLOBALS['inserts']++;if($GLOBALS['insert_error'])return new stdClass;$id=count($GLOBALS['posts'])+1;$GLOBALS['meta'][$id]=$data['meta_input'];unset($data['meta_input']);$data['ID']=$id;$GLOBALS['posts'][$id]=(object)$data;return $id;}
function wp_update_post($data,$error){$GLOBALS['updates']++;if($GLOBALS['publish_error']&&$data['post_status']==='publish')return new stdClass;$p=$GLOBALS['posts'][$data['ID']];$p->post_status=$data['post_status'];if($GLOBALS['collision']&&$data['post_status']==='publish')$p->post_name.='-2';return $p->ID;}
function get_post_field($key,$id){return $GLOBALS['posts'][$id]->$key;}
function get_permalink($id){return 'https://toolkitafrica.ac.ke/'.$GLOBALS['posts'][$id]->post_name.'/';}
function is_wp_error($value){return is_object($value);}
require dirname(__DIR__).'/deployment/audit/seo-discovery-publish.php';
function check($ok,$label){if(!$ok)throw new RuntimeException($label);$GLOBALS['checks']++;}
function expect_failure($code){try{toolkit_seo_publish_resource();throw new LogicException('Expected failure');}catch(RuntimeException $e){check($e->getMessage()===$code,'Correct safe failure: '.$code);}}
$r=toolkit_seo_publish_resource();check($r['created']&&$r['post_id']===1,'One owned record created');check($posts[1]->post_status==='publish','Draft validated and published');
$before=$updates;$again=toolkit_seo_publish_resource();check(!$again['created']&&$again['post_id']===1&&$inserts===1&&$updates===$before,'Repeated request does not rewrite or duplicate publication');
$saved=clone $posts[1];$saved_meta=$meta[1];
$posts[1]->post_content.='Human edit';expect_failure('resource_slug_or_content_conflict');check($updates===$before,'Human changes remain untouched');$posts[1]=clone $saved;
unset($meta[1]['_toolkit_seo_release']);expect_failure('resource_ownership_or_metadata_conflict');$meta[1]=$saved_meta;
$posts[1]->post_type='page';expect_failure('resource_slug_or_content_conflict');$posts[1]=clone $saved;
$posts[1]->post_status='draft';$resumed=toolkit_seo_publish_resource();check(!$resumed['created']&&$inserts===1&&$posts[1]->post_status==='publish','Owned complete draft can be reconciled without duplication');
$posts=[];$meta=[];$collision=true;expect_failure('resource_concurrent_slug_conflict_retained_draft');check($posts[1]->post_status==='draft','Concurrent slug collision leaves only a private draft');$collision=false;
$posts=[];$meta=[];$insert_error=true;expect_failure('resource_draft_failed');check(!$posts,'Insertion failure creates no public resource');$insert_error=false;
$publish_error=true;expect_failure('resource_publish_failed');check($posts[1]->post_status==='draft','Failed publication remains a draft');
echo $checks." editorial ownership, idempotence, interrupted draft, failure and collision checks passed\n";
