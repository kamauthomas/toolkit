<?php
// Private CLI-only release check. It never prints credentials or visitor/staff records.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = $argv[1];
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$users = App\Models\User::where('is_active', true)->get(['role','email']);
$toolkit = fn ($user) => str_ends_with(strtolower($user->email), '@toolkitafrica.ac.ke');
$administrators = $users->where('role', 'administrator');
$allowedLegacy = $users->filter(fn ($user) => strtolower(trim($user->email)) === App\Rules\ToolkitStaffEmail::LEGACY_EMAIL)->count();
$blocked = $users->reject(fn ($user) => App\Rules\ToolkitStaffEmail::allows($user->email))->count();
if ($allowedLegacy > 1) { throw new RuntimeException('More than one approved legacy account'); }
$counts = ['active_allowed_legacy_staff'=>$allowedLegacy, 'active_disallowed_staff'=>$blocked, 'active_staff'=>$users->count(), 'active_non_toolkit_staff'=>$users->reject($toolkit)->count(), 'toolkit_administrators'=>$administrators->filter($toolkit)->count()];
echo json_encode(['staff_domain_precheck'=>$counts], JSON_UNESCAPED_SLASHES).PHP_EOL;
if (!$counts['toolkit_administrators']) { fwrite(STDERR, "No active Toolkit administrator; stop rollout to avoid lockout.\n"); exit(7); }
if (($argv[2] ?? '') === 'precheck') { exit; }
foreach (['users','visits'] as $table) {
    if (!Illuminate\Support\Facades\Schema::hasColumn($table, 'deleted_at')) { throw new RuntimeException('Archive migration missing'); }
}
foreach (['staff.visits.checkout','staff.visits.details','staff.visits.destroy','staff.users.destroy','staff.visits.create','staff.visits.store','staff.applicants.create','staff.applicants.store','staff.users.activity','staff.visits.delete-test-data','staff.applicants.delete-test-data','staff.applicants.details'] as $route) {
    if (!Illuminate\Support\Facades\Route::has($route)) { throw new RuntimeException('Missing route '.$route); }
}
foreach (['0712345678','0112345678','+254 712 345 678'] as $phone) {
    if (!App\Support\ContactValidation::mobile($phone)) { throw new RuntimeException('Valid mobile rejected'); }
}
foreach (['12345678901234','12345678901234567890','0712345678abc'] as $phone) {
    if (App\Support\ContactValidation::mobile($phone)) { throw new RuntimeException('Invalid mobile accepted'); }
}
foreach (['test@test','audit@com'] as $email) {
    if (App\Support\ContactValidation::qualifiedEmail($email)) { throw new RuntimeException('Invalid email accepted'); }
}
$rule = new App\Rules\ToolkitStaffEmail();
foreach (['test@test','fixture@gmail.com','fixture@toolkitafrica.ac.ke.example.com','other@toolkitiskills.com','toolkit@toolkitiskills.com.example.com'] as $email) {
    $failed=false; $rule->validate('email', $email, function() use (&$failed) { $failed=true; });
    if (!$failed) { throw new RuntimeException('Staff domain restriction failed'); }
}
foreach (['staff.registration'=>['flow'=>'visit'], 'staff.users.activity'=>['events'=>collect()]] as $view=>$data) {
    // Compilation confirms deployed templates parse; staff-dependent rendering is tested separately using synthetic records.
    $file = $app['view.finder']->find($view); $app['blade.compiler']->compile($file);
}
if (!App\Rules\ToolkitStaffEmail::allows('toolkit@toolkitiskills.com')) { throw new RuntimeException('Approved legacy account rejected'); }
if (count(App\Support\ContactValidation::countries())!==245 || App\Support\ContactValidation::phone('07400123456','GB')!=='+447400123456' || App\Support\ContactValidation::phone('0712345678','KE')!=='+254712345678' || App\Support\ContactValidation::phone('123456789012345678','KE')!==null) { throw new RuntimeException('International phone validation failed'); }
echo "RECEPTION_AUDIT_RUNTIME_OK\n";
