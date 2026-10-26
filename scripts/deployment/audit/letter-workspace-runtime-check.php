<?php
// Local source executed inline through the existing CLI; never uploaded.
$phase='bootstrap';$checks=[];$networkStop=false;$transaction=false;
function workspaceCheck($ok,$label){global $phase,$checks;$phase=$label;if(!$ok)throw new RuntimeException('workspace_check_failed');$checks[]=$label;}
try{
    [$unused,$root,$environment]=$argv;
    require $root.'/vendor/autoload.php';$app=require $root.'/bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    workspaceCheck(rtrim(config('app.url'),'/')==='https://reception'.($environment==='demo'?'-demo':'').'.toolkitafrica.ac.ke','canonical_environment');
    workspaceCheck(App\Services\CallingLetterGateway::enabled(),'calling_letters_enabled');
    workspaceCheck(Illuminate\Support\Facades\Schema::hasTable('manual_calling_letters'),'manual_schema_ready');
    foreach(['index','search','create','review','edit-details','store','show','download','destroy'] as $name)workspaceCheck(Illuminate\Support\Facades\Route::has('staff.calling-letters.'.$name),'route_'.$name);
    $app['events']->listen(Illuminate\Http\Client\Events\ResponseReceived::class,function($event)use(&$networkStop){if(in_array($event->response->status(),[403,406,429])||$event->response->header('Retry-After'))$networkStop=true;});
    $app['events']->listen(Illuminate\Http\Client\Events\ConnectionFailed::class,function()use(&$networkStop){$networkStop=true;});
    config(['session.driver'=>'array']);$session=$app['session']->driver();$session->start();$session->save();
    if($environment==='production'){
        // In-memory view actor only: no saved account, request dispatch, audit or issuance.
        $actor=new App\Models\User(['name'=>'Private release verification','role'=>'administrator','is_active'=>true,'must_change_password'=>false]);
        Illuminate\Support\Facades\Auth::setUser($actor);
        $app['view']->share('errors',new Illuminate\Support\ViewErrorBag());
        foreach(['reception','website','manual'] as $source){
            $request=Illuminate\Http\Request::create(route('staff.calling-letters.index'),'GET',['source'=>$source]);$request->setUserResolver(fn()=>$actor);$request->setLaravelSession($session);
            $app->instance('request',$request);
            $response=app(App\Http\Controllers\Staff\CallingLetterWorkspaceController::class)->index($request);
            workspaceCheck($response->getStatusCode()===200&&str_contains($response->getContent(),'calling-letters.css?v=20261026.2'),'readonly_render_'.$source);
        }
        $request=Illuminate\Http\Request::create(route('staff.calling-letters.create'));$request->setUserResolver(fn()=>$actor);$request->setLaravelSession($session);$app->instance('request',$request);
        $response=app(App\Http\Controllers\Staff\CallingLetterWorkspaceController::class)->create($request);
        workspaceCheck($response->getStatusCode()===200&&str_contains($response->getContent(),'Review details'),'readonly_create_view');
    }else{
        Illuminate\Support\Facades\DB::beginTransaction();$transaction=true;$nonce=strtoupper(bin2hex(random_bytes(5)));
        $actor=App\Models\User::create(['name'=>'Synthetic workspace administrator','email'=>'workspace-'.$nonce.'@toolkitafrica.ac.ke','password'=>bin2hex(random_bytes(24)),'role'=>'administrator','is_active'=>true,'must_change_password'=>false]);
        $call=function($name,$method,$data=[],$parameters=[],$user=null)use($app,$session,$actor){
            $user=$user??$actor;Illuminate\Support\Facades\Auth::setUser($user);
            $r=Illuminate\Http\Request::create(route($name,$parameters),$method,[...$data,'_token'=>$session->token()],[config('session.cookie')=>$session->getId()]);$r->headers->set('Accept','application/json');$r->setUserResolver(fn()=>$user);
            $kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$response=$kernel->handle($r);$kernel->terminate($r,$response);return $response;
        };
        $r=$call('staff.calling-letters.index','GET');workspaceCheck($r->getStatusCode()===200&&str_contains($r->getContent(),'calling-letters.css?v=20261026.2'),'workspace_sidebar_and_style');
        $r=$call('staff.calling-letters.create','GET');workspaceCheck($r->getStatusCode()===200&&str_contains($r->getContent(),'phone_country'),'manual_details_form');
        $details=['name'=>'WORKSPACE DEMO TEST '.$nonce,'phone'=>'254712345678','phone_country'=>'KE','course'=>'Welding','intake'=>'January 2027 - DEMO ONLY'];
        $r=$call('staff.calling-letters.review','POST',$details);workspaceCheck($r->getStatusCode()===200&&str_contains($r->getContent(),'Review before issuing'),'review_without_issue');
        $before=App\Models\Applicant::count();$data=[...$details,'confirmed_issuance'=>'1'];$phase='manual_real_render';usleep(4000000);
        $r=$call('staff.calling-letters.store','POST',$data);workspaceCheck($r->getStatusCode()===302,'manual_issue_redirect');
        $letter=App\Models\ManualCallingLetter::where('issued_by',$actor->id)->first();workspaceCheck($letter&&$letter->version===1&&$letter->phone==='+254712345678','manual_saved_and_normalised');
        workspaceCheck(App\Models\Applicant::count()===$before,'manual_did_not_register_applicant');
        $raw=Illuminate\Support\Facades\DB::table('manual_calling_letters')->where('id',$letter->id)->first();workspaceCheck(!str_contains($raw->name,$details['name'])&&!str_contains($raw->pdf_base64,$letter->pdf_base64),'recipient_and_pdf_encrypted');
        $r=$call('staff.calling-letters.download','GET',[],['letter'=>$letter->id]);workspaceCheck($r->getStatusCode()===200&&str_starts_with($r->getContent(),chr(37).'PDF-')&&hash_equals($letter->sha256,hash('sha256',$r->getContent())),'manual_pdf_download');
        $r=$call('staff.calling-letters.store','POST',$data);workspaceCheck($r->getStatusCode()===302&&App\Models\ManualCallingLetter::where('reference',$letter->reference)->count()===1,'manual_duplicate_prevented');
        $phase='manual_replacement_real_render';usleep(4000000);$r=$call('staff.calling-letters.store','POST',[...$data,'replacement_id'=>$letter->id,'confirmed_replacement'=>'1','intake'=>'February 2027 - DEMO ONLY']);
        workspaceCheck($r->getStatusCode()===302&&App\Models\ManualCallingLetter::where('reference',$letter->reference)->count()===2,'replacement_keeps_history');
        $r=$call('staff.calling-letters.show','GET',[],['letter'=>$letter->id]);workspaceCheck($r->getStatusCode()===200&&str_contains($r->getContent(),'Version history'),'issued_page_and_history');
        $r=$call('staff.calling-letters.search','POST',['source'=>'manual','search'=>$nonce]);workspaceCheck($r->getStatusCode()===200&&str_contains($r->getContent(),$details['name']),'private_search');
        $manager=clone $actor;$manager->role='manager';$r=$call('staff.calling-letters.store','POST',$data,[],$manager);workspaceCheck($r->getStatusCode()===403,'manager_issue_denied_private_fixture');
        $r=$call('staff.calling-letters.show','GET',[],['letter'=>$letter->id],$manager);workspaceCheck($r->getStatusCode()===200&&!str_contains($r->getContent(),'Prepare replacement'),'manager_readonly_history');
        $a=App\Models\Applicant::create(['reference'=>'APP-UI-'.$nonce,'name'=>'WORKSPACE NATIVE DEMO TEST','phone'=>'+254712345678','email'=>'workspace@example.invalid','contact_hash'=>hash('sha256',$nonce),'course_interest'=>'welding','preferred_contact'=>'email','source'=>'staff','status'=>'new','submitted_at'=>now(),'consent_at'=>now()]);
        $r=$call('staff.calling-letters.review','POST',[...$details,'applicant_id'=>$a->id]);workspaceCheck($r->getStatusCode()===200&&str_contains($r->getContent(),'recipient_signature'),'native_guided_review');
        $signature=hash_hmac('sha256',json_encode([$a->id,$a->name,$a->phone],JSON_THROW_ON_ERROR),config('app.key'));$phase='native_guided_real_render';usleep(4000000);
        $r=$call('staff.applicants.calling-letters.issue','POST',['course'=>'Welding','intake'=>'January 2027 - DEMO ONLY','confirmed_issuance'=>'1','workspace'=>'1','recipient_signature'=>$signature],['applicant'=>$a->id]);
        workspaceCheck($r->getStatusCode()===302&&App\Models\CallingLetter::where('applicant_id',$a->id)->count()===1,'native_guided_issue');
        $r=$call('staff.applicants.calling-letters.index','GET',[],['applicant'=>$a->id]);workspaceCheck($r->getStatusCode()===200&&str_contains($r->getContent(),'calling-letters.css?v=20261026.2'),'native_dedicated_history');
        $r=$call('staff.calling-letters.destroy','DELETE',['reference_confirmation'=>$letter->reference,'confirmed_test_data'=>'1'],['letter'=>$letter->id]);workspaceCheck($r->getStatusCode()===302&&!App\Models\ManualCallingLetter::where('reference',$letter->reference)->exists(),'admin_test_cleanup');
        Illuminate\Support\Facades\DB::rollBack();$transaction=false;
        workspaceCheck(!App\Models\User::where('email',$actor->email)->exists()&&!App\Models\Applicant::where('reference',$a->reference)->exists()&&!App\Models\ManualCallingLetter::where('reference',$letter->reference)->exists(),'all_demo_fixtures_rolled_back');
    }
    echo json_encode(['workspace_acceptance'=>'passed','environment'=>$environment,'checks'=>$checks,'network_stop'=>$networkStop,'production_test_applications'=>0,'production_test_letters'=>0]),"\n";
}catch(Throwable $error){
    if($transaction){try{Illuminate\Support\Facades\DB::rollBack();}catch(Throwable $ignored){}}
    echo json_encode(['workspace_acceptance'=>'failed','phase'=>$phase,'checks'=>$checks,'network_stop'=>$networkStop]),"\n";exit(1);
}
