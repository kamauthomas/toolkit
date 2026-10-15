<?php
// Private CLI-only fixture check: real deployed PHP, SQLite in memory, no real records.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
try {
$root = $argv[1];
foreach (['APP_ENV'=>'testing','APP_KEY'=>'base64:'.base64_encode(str_repeat('x',32)),'DB_CONNECTION'=>'sqlite','DB_DATABASE'=>':memory:','DB_URL'=>'','SESSION_DRIVER'=>'array','CACHE_STORE'=>'array','QUEUE_CONNECTION'=>'sync'] as $k=>$v) { putenv($k.'='.$v); $_ENV[$k]=$v; $_SERVER[$k]=$v; }
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['app.env'=>'testing','app.key'=>'base64:'.base64_encode(str_repeat('x',32)),'database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:','database.connections.sqlite.url'=>null,'session.driver'=>'array','cache.default'=>'array','queue.default'=>'sync']);
Illuminate\Support\Facades\DB::purge();
if (Illuminate\Support\Facades\DB::connection()->getDriverName() !== 'sqlite' || Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== ':memory:') { throw new RuntimeException('Unsafe fixture database'); }
Illuminate\Support\Facades\Artisan::call('migrate', ['--force'=>true]);
$admin = App\Models\User::create(['name'=>'Synthetic Review Administrator','email'=>'review@toolkitafrica.ac.ke','password'=>'unused-synthetic-review','role'=>'administrator','is_active'=>true,'must_change_password'=>false]);
$checks = [];
function checkReview($ok, $message) { global $checks; if (!$ok) { throw new RuntimeException($message); } $checks[]=$message; }
function reviewGet($name, $parameters = []) {
 global $app,$admin;
 $request = Illuminate\Http\Request::create(route($name,$parameters),'GET');
 Illuminate\Support\Facades\Auth::setUser($admin);$request->setUserResolver(fn()=>$admin);
 $kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$response=$kernel->handle($request);$kernel->terminate($request,$response);
 checkReview($response->getStatusCode()===200,'HTTP 200 '.$name);
 return $response->getContent();
}
$out=$root.'/storage/app/private/review-2026.10.15.1';if(!is_dir($out))mkdir($out,0700,true);
for($i=1;$i<=26;$i++) App\Models\Applicant::create(['reference'=>'APP-SYNTHETIC-'.$i,'name'=>'Synthetic Applicant '.$i,'phone'=>'+254712345678','email'=>'fixture@example.com','contact_hash'=>str_repeat('a',64),'course_interest'=>['digital_skills','renewable_energy','electrical_installation'][$i%3],'preferred_contact'=>'email','source'=>'staff','status'=>'new','submitted_at'=>now()->subMinutes($i),'consent_at'=>now()]);
for($i=0;$i<55;$i++) App\Models\AuditLog::create(['user_id'=>$admin->id,'action'=>'staff.login']);
for($i=0;$i<7;$i++) App\Models\Visit::create(['reference'=>'VIS-SYNTHETIC-'.$i,'name'=>$i===0?'Synthetic Returning Visitor':'Synthetic Visitor '.$i,'phone'=>$i<2?'+254712345678':'+25472234567'.$i,'contact_hash'=>hash_hmac('sha256',$i===1?'0712345678':($i===0?'+254712345678':'+25472234567'.$i),config('app.key')),'purpose'=>['meeting','course_enquiry','event'][$i%3],'status'=>$i<2?'checked_in':'checked_out','source'=>'staff','checked_in_at'=>now()->subDays($i),'checked_out_at'=>$i<2?null:now()->subDays($i)->addHour(),'consent_at'=>now()]);
$pages=[
 'overview'=>reviewGet('staff.dashboard'),
 'applicants'=>reviewGet('staff.applicants.index'),
 'applicants-page-2'=>reviewGet('staff.applicants.index',['page'=>2]),
 'staff-access'=>reviewGet('staff.users.index'),
 'staff-activity'=>reviewGet('staff.users.activity'),
 'visitors'=>reviewGet('staff.visits.index'),
 'returning-visitor'=>reviewGet('staff.visits.show',['visit'=>1]),
];
checkReview(str_contains($pages['overview'],'Daily visitor arrivals'),'Real daily attendance panel rendered');
checkReview(str_contains($pages['applicants'],'staff-pagination') && str_contains($pages['applicants'],'Next →'),'Applicant plain pagination rendered');
checkReview(str_contains($pages['applicants-page-2'],'Page 2 of 2'),'Applicant next page rendered');
checkReview(str_contains($pages['staff-activity'],'staff-pagination'),'Staff activity plain pagination rendered');
checkReview(str_contains($pages['visitors'],'Returning · visit 2'),'Historical phone variant returns matched label');
checkReview(str_contains($pages['returning-visitor'],'VIS-SYNTHETIC-1') && str_contains($pages['returning-visitor'],'still show as on site'),'Open prior visit history retained and flagged');
foreach($pages as $name=>$body) { $body=str_replace('/'.trim(config('reception.staff_path'),'/'),'/reception-control',$body); file_put_contents($out.'/'.$name.'.html',$body); }
$result=['result'=>'RECEPTION_REVIEW_OK','checks'=>count($checks),'details'=>$checks,'database'=>'SQLite :memory:','real_records_changed'=>0];file_put_contents($out.'/results.json',json_encode($result,JSON_PRETTY_PRINT));echo json_encode($result).PHP_EOL;
} catch(Throwable $error) { fwrite(STDERR,'RECEPTION_REVIEW_FAILED: '.$error->getMessage().PHP_EOL);exit(1); }
