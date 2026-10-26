<?php
// Executed inline by the release worker, never uploaded as an endpoint/helper.
$phase = "bootstrap"; $checks = []; $networkStop = false; $transaction = false; $pdf = null;
function releaseCheck($ok, $label) { global $checks, $phase; $phase = $label; if (!$ok) throw new RuntimeException("release_check_failed"); $checks[] = $label; }
try {
    [$unused, $root, $environment, $log] = $argv;
    require $root . "/vendor/autoload.php";
    $app = require $root . "/bootstrap/app.php";
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $expected = "https://reception" . ($environment === "demo" ? "-demo" : "") . ".toolkitafrica.ac.ke";
    releaseCheck(rtrim(config("app.url"), "/") === $expected, "canonical_environment");
    releaseCheck(App\Services\CallingLetterGateway::enabled(), "calling_letters_enabled");
    releaseCheck(Illuminate\Support\Facades\Schema::hasTable("calling_letters"), "additive_table_ready");
    $app["events"]->listen(Illuminate\Http\Client\Events\ResponseReceived::class, function ($event) use (&$networkStop) {
        if (in_array($event->response->status(), [403,406,429]) || $event->response->header("Retry-After")) $networkStop = true;
    });
    $app["events"]->listen(Illuminate\Http\Client\Events\ConnectionFailed::class, function () use (&$networkStop) { $networkStop = true; });
    $candidate = null;
    foreach (file($log, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $value = json_decode($line, true);
        if (is_array($value) && ($value["wordpress_letters"] ?? false)) $candidate = $value["candidate_id"] ?? null;
    }
    $website = "no_generated_source_pdf";
    if ($candidate) {
        $record = App\Models\WebsiteApplication::where("source_site", $environment)->where("source_id", $candidate)->where("source_missing", false)->first();
        releaseCheck($record !== null, "website_letter_matches_mirror");
        $phase = "existing_website_pdf";
        usleep(4000000);
        $artifact = app(App\Services\CallingLetterGateway::class)->website($record);
        releaseCheck(strlen($artifact["pdf"]) > 100 && hash_equals($artifact["sha256"], hash("sha256", $artifact["pdf"])), "existing_website_pdf_received");
        $website = "verified";
    }
    if ($environment === "demo") {
        // Real demo interpreter/configuration and MySQL, with all fixture rows rolled back.
        $initial = Illuminate\Support\Facades\DB::transactionLevel();
        releaseCheck($initial === 0, "no_existing_transaction");
        $before = App\Models\Applicant::count();
        Illuminate\Support\Facades\DB::beginTransaction(); $transaction = true;
        $nonce = strtoupper(bin2hex(random_bytes(4)));
        $admin = App\Models\User::create(["name"=>"Synthetic Release Administrator", "email"=>"release-".$nonce."@toolkitafrica.ac.ke", "password"=>bin2hex(random_bytes(24)), "role"=>"administrator", "is_active"=>true, "must_change_password"=>false]);
        $applicant = App\Models\Applicant::create(["reference"=>"APP-REL-".$nonce, "name"=>"RELEASE DEMO TEST NOT FOR ISSUE", "phone"=>"+254712345678", "email"=>"release@example.invalid", "contact_hash"=>hash("sha256",$nonce), "course_interest"=>"welding", "preferred_contact"=>"email", "source"=>"staff", "status"=>"new", "submitted_at"=>now(), "consent_at"=>now()]);
        config(["session.driver"=>"array"]); $session = $app["session"]->driver(); $session->start(); $session->save();
        $call = function ($name, $method, $data, $parameters, $actor) use ($app, $session) {
            Illuminate\Support\Facades\Auth::setUser($actor);
            $request = Illuminate\Http\Request::create(route($name,$parameters),$method,[...$data,"_token"=>$session->token()],[config("session.cookie")=>$session->getId()]);
            $request->headers->set("Accept","application/json"); $request->setUserResolver(fn()=>$actor);
            $kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$response=$kernel->handle($request);$kernel->terminate($request,$response);return $response;
        };
        $parameters=["applicant"=>$applicant->id];$data=["course"=>"Welding","intake"=>"January 2027 - DEMO ONLY","confirmed_issuance"=>"1"];
        $phase="demo_issue";usleep(4000000);$response=$call("staff.applicants.calling-letters.issue","POST",$data,$parameters,$admin);
        releaseCheck($response->getStatusCode()===302,"administrator_issue_redirect");
        $letter=App\Models\CallingLetter::where("applicant_id",$applicant->id)->first();
        releaseCheck($letter && $letter->issued_by===$admin->id,"administrator_issue_recorded");
        releaseCheck(!str_contains(Illuminate\Support\Facades\DB::table("calling_letters")->where("id",$letter->id)->value("pdf_base64"),$letter->pdf_base64),"pdf_encrypted_at_rest");
        $phase="demo_download";$response=$call("staff.applicants.calling-letters.download","GET",[],[...$parameters,"callingLetter"=>$letter->id],$admin);
        releaseCheck($response->getStatusCode()===200 && str_starts_with($response->getContent(),chr(37)."PDF-") && hash_equals($letter->sha256,hash("sha256",$response->getContent())),"issued_pdf_download");
        $pdf=base64_encode($response->getContent());
        $call("staff.applicants.calling-letters.issue","POST",$data,$parameters,$admin);
        releaseCheck(App\Models\CallingLetter::where("applicant_id",$applicant->id)->count()===1,"repeat_issue_idempotent");
        $phase="demo_history";$response=$call("staff.applicants.calling-letters.index","GET",[],$parameters,$admin);
        releaseCheck($response->getStatusCode()===200 && str_contains($response->getContent(),"name=\"confirmed_issuance\""),"administrator_history_form");
        $manager=clone $admin;$manager->role="manager";
        $response=$call("staff.applicants.calling-letters.issue","POST",$data,$parameters,$manager);
        releaseCheck($response->getStatusCode()===403,"manager_issue_denied_private_fixture");
        $response=$call("staff.applicants.calling-letters.index","GET",[],$parameters,$manager);
        releaseCheck($response->getStatusCode()===200 && !str_contains($response->getContent(),"name=\"confirmed_issuance\""),"manager_history_read_only");
        Illuminate\Support\Facades\DB::rollBack(); $transaction=false;
        releaseCheck(App\Models\Applicant::count()===$before && !App\Models\Applicant::where("reference",$applicant->reference)->exists(),"demo_fixtures_rolled_back");
    }
    echo json_encode(["acceptance"=>"passed","environment"=>$environment,"checks"=>$checks,"website_pdf"=>$website,"demo_pdf"=>$pdf,"network_stop"=>$networkStop,"production_test_applications"=>0]),"\n";
} catch (Throwable $error) {
    if ($transaction) { try { Illuminate\Support\Facades\DB::rollBack(); } catch (Throwable $ignored) {} }
    echo json_encode(["acceptance"=>"failed","phase"=>$phase,"checks"=>$checks,"network_stop"=>$networkStop]),"\n";
    exit(1);
}
