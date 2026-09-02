#!/usr/bin/env bash
set -euo pipefail

wordpress_repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../../.." && pwd)
campus_repo=$(cd -- "$wordpress_repo/../SmartLecturer_VirtualCampus/virtual-campus" && pwd)
campus_git=$(git -C "$campus_repo" rev-parse --show-toplevel)
release=2026.09.02.3
commit=3596b87
private_root=/home/bfyigiln/virtual-campus-test
public_base=https://campus-test.toolkitafrica.ac.ke
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$wordpress_repo/.toolkit-deploy/secrets.env}
state_file=${TOOLKIT_CAMPUS_TEST_STATE:-$wordpress_repo/.toolkit-deploy/virtual-campus-test.env}
acceptance_email=signup.acceptance.20260902@campus.test
existing_files=(
  app/Http/Controllers/AdminDashboardController.php
  app/Http/Controllers/AuthController.php
  config/campus.php
  public/assets/css/campus.css
  resources/views/auth/login.blade.php
  resources/views/auth/register.blade.php
  resources/views/layouts/app.blade.php
  resources/views/welcome.blade.php
  routes/console.php
  routes/web.php
)
new_files=(app/Domain/Identity/StudentRegistrationPolicy.php)

git -C "$campus_git" merge-base --is-ancestor "$commit" HEAD || { printf 'Campus HEAD does not contain %s\n' "$commit" >&2; exit 3; }
[[ -z $(git -C "$campus_git" status --short) ]] || { printf 'Campus tree is dirty\n' >&2; exit 4; }
[[ -f $secrets_file && -f $state_file ]] || { printf 'Missing private deployment input\n' >&2; exit 5; }

work=$(mktemp -d)
chmod 700 "$work"
source "$secrets_file"
source "$state_file"
acceptance_password="Tt!${CAMPUS_DEMO_PASSWORD}"
source "$wordpress_repo/scripts/deployment/lib/cpanel-session.sh"
cpanel_session_open "$CPANEL_AUTH"
cpanel_user=${CPANEL_AUTH%%:*}
cron_line=
cleanup() {
  if [[ -n ${cron_line:-} ]]; then
    api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=remove_line' --data-urlencode "line=$cron_line" >/dev/null 2>&1 || true
  fi
  cpanel_session_close
  rm -rf -- "$work"
}
trap cleanup EXIT

api2() {
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 90 -fsS -b "$CPANEL_SESSION_COOKIE" -G "$CPANEL_SESSION_API2" \
    --data-urlencode "cpanel_jsonapi_user=$cpanel_user" --data-urlencode 'cpanel_jsonapi_apiversion=2' "$@"
}
mkdir_remote() {
  local parent=$1 name=$2
  api2 --data-urlencode 'cpanel_jsonapi_module=Fileman' --data-urlencode 'cpanel_jsonapi_func=mkdir' \
    --data-urlencode "path=$parent" --data-urlencode "name=$name" --data-urlencode 'permissions=0750' \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1&&!str_contains(json_encode($j),"exists"))exit(10);'
}
fetch() {
  local dir=$1 file=$2 target=$3
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 60 -fsS -b "$CPANEL_SESSION_COOKIE" -G \
    --data-urlencode "dir=$dir" --data-urlencode "file=$file" --data-urlencode 'show_hidden=1' \
    "$CPANEL_SESSION_API/Fileman/get_file_content" \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||!isset($j["data"]["content"]))exit(11);echo $j["data"]["content"];' > "$target"
}
upload() {
  local dir=$1 file=$2
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 90 -fsS -b "$CPANEL_SESSION_COOKIE" \
    -F "dir=$dir" -F 'overwrite=1' -F "file-1=@$file" "$CPANEL_SESSION_API/Fileman/upload_files" \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0)exit(12);'
}

rollback="$wordpress_repo/rollbacks/campus-test-pre-$release"
[[ ! -e $rollback ]] || { printf 'Rollback already exists: %s\n' "$rollback" >&2; exit 6; }
mkdir -p "$rollback"
chmod 700 "$rollback"
for relative in "${existing_files[@]}"; do
  mkdir -p "$rollback/$(dirname -- "$relative")"
  fetch "$private_root/$(dirname -- "$relative")" "$(basename -- "$relative")" "$rollback/$relative"
done
fetch "$private_root" .env "$rollback/.env"
chmod 600 "$rollback/.env"

cp "$rollback/.env" "$work/.env"
php -r '
$file=$argv[1]; $values=[
  "CAMPUS_REGISTRATION_ENABLED"=>"true",
  "CAMPUS_REGISTRATION_TESTING_ACKNOWLEDGED"=>"true",
  "CAMPUS_REGISTRATION_PRODUCTION_READY"=>"false",
];
$lines=file($file, FILE_IGNORE_NEW_LINES);
$lines=array_values(array_filter($lines, static function(string $line) use ($values): bool {
  foreach(array_keys($values) as $key) if(str_starts_with($line, $key."=")) return false;
  return true;
}));
foreach($values as $key=>$value) $lines[]=$key."=".$value;
file_put_contents($file, implode(PHP_EOL,$lines).PHP_EOL);
' "$work/.env"
chmod 600 "$work/.env"

mkdir_remote "$private_root/app/Domain" Identity
for relative in "${existing_files[@]}" "${new_files[@]}"; do
  upload "$private_root/$(dirname -- "$relative")" "$campus_repo/$relative"
done
upload "$private_root" "$work/.env"

log="$private_root/storage/logs/deploy-$release.log"
command="/usr/bin/flock -n /tmp/toolkit-campus-test-deploy.lock -c 'cd $private_root && /usr/local/bin/ea-php84 artisan optimize:clear > $log 2>&1 && /usr/local/bin/ea-php84 artisan config:cache >> $log 2>&1 && /usr/local/bin/ea-php84 artisan campus:readiness --json >> $log 2>&1 && echo CAMPUS_SIGNUP_OK >> $log'"
api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=add_line' \
  --data-urlencode "command=$command" --data-urlencode 'minute=*' --data-urlencode 'hour=*' --data-urlencode 'day=*' --data-urlencode 'month=*' --data-urlencode 'weekday=*' \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1)exit(13);'
cron_line=$(api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=listcron' \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);$needle=$argv[1];foreach(($j["cpanelresult"]["data"]??[]) as $row){if(($row["command"]??"")===$needle){echo $row["count"]??"";break;}}' "$command")
[[ -n $cron_line ]] || { printf 'Unable to identify temporary cron line\n' >&2; exit 15; }

for _ in {1..12}; do
  sleep 10
  if fetch "$private_root/storage/logs" "deploy-$release.log" "$work/deploy.log" 2>/dev/null && rg -q 'CAMPUS_SIGNUP_OK' "$work/deploy.log"; then break; fi
done
rg -q 'CAMPUS_SIGNUP_OK' "$work/deploy.log" || { sed -n '1,160p' "$work/deploy.log" >&2; exit 14; }
rg -q '"ready": true' "$work/deploy.log"
rg -q 'supervised non-production testing' "$work/deploy.log"
api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=remove_line' --data-urlencode "line=$cron_line" \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1)exit(16);'
cron_line=

register_html="$work/register.html"
cookie_jar="$work/cookies.txt"
[[ $(curl --connect-timeout 15 --max-time 45 -sS -c "$cookie_jar" -o "$register_html" -w '%{http_code}' "$public_base/register") == 200 ]]
rg -q 'Create student account' "$register_html"
csrf=$(php -r '$h=file_get_contents($argv[1]);preg_match("~name=\"_token\" value=\"([^\"]+)\"~",$h,$m);echo $m[1]??"";' "$register_html")
[[ -n $csrf ]]
sleep 2
status=$(curl --connect-timeout 15 --max-time 45 -sS -b "$cookie_jar" -c "$cookie_jar" -o /dev/null -w '%{http_code}' \
  --data-urlencode "_token=$csrf" --data-urlencode 'name=Signup Acceptance Student' --data-urlencode "email=$acceptance_email" \
  --data-urlencode "password=$acceptance_password" --data-urlencode "password_confirmation=$acceptance_password" \
  --data-urlencode 'website=' "$public_base/register")
[[ $status == 302 ]]
curl --connect-timeout 15 --max-time 45 -fsS -b "$cookie_jar" "$public_base/dashboard" > "$work/dashboard.html"
rg -q 'No active courses yet' "$work/dashboard.html"
rg -q 'Student' "$work/dashboard.html"
[[ $(curl --connect-timeout 15 --max-time 45 -sS -b "$cookie_jar" -o /dev/null -w '%{http_code}' "$public_base/admin") == 403 ]]

printf 'environment=staging release=%s commit=%s url=%s signup=verified role=student enrolments=none admin=denied rollback=%s\n' "$release" "$commit" "$public_base" "$rollback"
