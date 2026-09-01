#!/usr/bin/env bash
set -euo pipefail

wordpress_repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../../.." && pwd)
campus_repo=$(cd -- "$wordpress_repo/../SmartLecturer_VirtualCampus/virtual-campus" && pwd)
campus_git=$(git -C "$campus_repo" rev-parse --show-toplevel)
release=2026.09.01.3
commit=c89fa98
hostname=campus-test.toolkitafrica.ac.ke
private_root=/home/bfyigiln/virtual-campus-test
public_base="https://$hostname"
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$wordpress_repo/.toolkit-deploy/secrets.env}
state_file=${TOOLKIT_CAMPUS_TEST_STATE:-$wordpress_repo/.toolkit-deploy/virtual-campus-test.env}

git -C "$campus_git" merge-base --is-ancestor "$commit" HEAD || { printf 'Campus HEAD does not contain %s\n' "$commit" >&2; exit 3; }
[[ -z $(git -C "$campus_git" status --short) ]] || { printf 'Campus tree is dirty\n' >&2; exit 4; }
[[ -f $campus_repo/public/build/manifest.json ]] || { printf 'Missing compiled Campus assets\n' >&2; exit 5; }
[[ -d $campus_repo/vendor ]] || { printf 'Missing locked Composer dependencies\n' >&2; exit 5; }
[[ -f $secrets_file && -f $state_file && -f $campus_repo/.env ]] || { printf 'Missing private deployment input\n' >&2; exit 6; }

source "$secrets_file"
source "$state_file"
source "$wordpress_repo/scripts/deployment/lib/cpanel-session.sh"

work=$(mktemp -d)
chmod 700 "$work"
archive="$work/campus-test-$release.tar.gz"
env_dir="$work/environment"
env_payload="$env_dir/.env"
mkdir -p "$env_dir"
chmod 700 "$env_dir"

cleanup() {
  if [[ -n ${cron_line:-} && -n ${CPANEL_SESSION_API2:-} ]]; then
    api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=remove_line' --data-urlencode "line=$cron_line" >/dev/null 2>&1 || true
  fi
  rm -rf -- "$work"
  cpanel_session_close
}
cron_line=
trap cleanup EXIT

php -r '
function envMap(string $file): array {
    $values = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        if (str_starts_with(ltrim($line), "#") || ! str_contains($line, "=")) continue;
        [$key, $value] = explode("=", $line, 2);
        $values[trim($key)] = trim(trim($value), "\"\x27");
    }
    return $values;
}
function quoted(string $value): string {
    return "\"".str_replace(["\\", "\"", "\r", "\n"], ["\\\\", "\\\"", "", ""], $value)."\"";
}
[$localFile, $stateFile, $target] = array_slice($argv, 1);
$local = envMap($localFile); $state = envMap($stateFile);
foreach (["LIVEKIT_URL", "LIVEKIT_API_KEY", "LIVEKIT_API_SECRET"] as $required) {
    if (($local[$required] ?? "") === "") { fwrite(STDERR, "Missing required LiveKit setting: $required\n"); exit(7); }
}
$values = [
    "APP_NAME" => "Toolkit Virtual Campus", "APP_ENV" => "staging",
    "APP_KEY" => $state["APP_KEY"] ?? "", "APP_DEBUG" => "false",
    "APP_URL" => "https://campus-test.toolkitafrica.ac.ke",
    "APP_LOCALE" => "en", "APP_FALLBACK_LOCALE" => "en", "LOG_CHANNEL" => "stack",
    "LOG_LEVEL" => "warning", "DB_CONNECTION" => "mysql", "DB_HOST" => "localhost",
    "DB_PORT" => "3306", "DB_DATABASE" => $state["DB_DATABASE"] ?? "",
    "DB_USERNAME" => $state["DB_USERNAME"] ?? "", "DB_PASSWORD" => $state["DB_PASSWORD"] ?? "",
    "SESSION_DRIVER" => "database", "SESSION_LIFETIME" => "120", "SESSION_ENCRYPT" => "true",
    "SESSION_SECURE_COOKIE" => "true", "SESSION_HTTP_ONLY" => "true", "SESSION_SAME_SITE" => "lax",
    "CACHE_STORE" => "database", "QUEUE_CONNECTION" => "database", "FILESYSTEM_DISK" => "local",
    "MAIL_MAILER" => "log", "HASH_DRIVER" => "argon2id",
    "CAMPUS_REGISTRATION_ENABLED" => "false", "CAMPUS_DEMO_MODE" => "true",
    "CAMPUS_DEMO_PASSWORD" => $state["CAMPUS_DEMO_PASSWORD"] ?? "",
    "CAMPUS_CERTIFICATES_ENABLED" => "false", "CAMPUS_MEDIA_ENABLED" => "true",
    "CAMPUS_MEDIA_PROVIDER" => "livekit", "LIVEKIT_URL" => $local["LIVEKIT_URL"],
    "LIVEKIT_API_KEY" => $local["LIVEKIT_API_KEY"], "LIVEKIT_API_SECRET" => $local["LIVEKIT_API_SECRET"],
    "CAMPUS_LIVE_CAPTIONS_READY" => "false", "CAMPUS_LIVE_TESTING_ACKNOWLEDGED" => "true",
];
foreach (["APP_KEY", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD", "CAMPUS_DEMO_PASSWORD"] as $required) {
    if (($values[$required] ?? "") === "") { fwrite(STDERR, "Missing private state: $required\n"); exit(8); }
}
$output = ""; foreach ($values as $key => $value) $output .= $key."=".quoted($value).PHP_EOL;
file_put_contents($target, $output, LOCK_EX); chmod($target, 0600);
' "$campus_repo/.env" "$state_file" "$env_payload"

tar -czf "$archive" -C "$campus_repo" \
  --exclude='./.env' --exclude='./.git' --exclude='./node_modules' \
  --exclude='./storage/logs/*' --exclude='./storage/framework/cache/data/*' \
  --exclude='./storage/framework/sessions/*' --exclude='./storage/framework/views/*' \
  --exclude='./database/*.sqlite' .

cpanel_session_open "$CPANEL_AUTH"
cpanel_user=${CPANEL_AUTH%%:*}
api2() {
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 120 -fsS -b "$CPANEL_SESSION_COOKIE" -G "$CPANEL_SESSION_API2" \
    --data-urlencode "cpanel_jsonapi_user=$cpanel_user" --data-urlencode 'cpanel_jsonapi_apiversion=2' "$@"
}
upload() {
  local dir=$1 file=$2
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 300 -fsS -b "$CPANEL_SESSION_COOKIE" \
    -F "dir=$dir" -F 'overwrite=1' -F "file-1=@$file" "$CPANEL_SESSION_API/Fileman/upload_files" \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0)exit(12);'
}
fetch() {
  local dir=$1 file=$2 target=$3
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 60 -fsS -b "$CPANEL_SESSION_COOKIE" -G \
    --data-urlencode "dir=$dir" --data-urlencode "file=$file" --data-urlencode 'show_hidden=1' \
    "$CPANEL_SESSION_API/Fileman/get_file_content" \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||!isset($j["data"]["content"]))exit(11);echo $j["data"]["content"];' > "$target"
}

upload "$private_root" "$archive"
upload "$private_root" "$env_payload"

remote_archive="$private_root/$(basename -- "$archive")"
log="$private_root/storage/logs/deploy-$release.log"
command="/usr/bin/flock -n /tmp/toolkit-campus-test-deploy.lock -c 'cd $private_root && /usr/bin/tar -xzf $remote_archive && /bin/chmod -R u+rwX storage bootstrap/cache && /usr/local/bin/ea-php84 artisan migrate:fresh --seed --force > $log 2>&1 && /usr/local/bin/ea-php84 artisan optimize:clear >> $log 2>&1 && /usr/local/bin/ea-php84 artisan config:cache >> $log 2>&1 && /usr/local/bin/ea-php84 artisan route:cache >> $log 2>&1 && /usr/local/bin/ea-php84 artisan view:cache >> $log 2>&1 && /bin/rm -f $remote_archive && echo CAMPUS_RELEASE_OK >> $log'"
api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=add_line' \
  --data-urlencode "command=$command" --data-urlencode 'minute=*' --data-urlencode 'hour=*' --data-urlencode 'day=*' --data-urlencode 'month=*' --data-urlencode 'weekday=*' \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1)exit(13);'
cron_line=$(api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=listcron' \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);$needle=$argv[1];foreach(($j["cpanelresult"]["data"]??[]) as $row){if(($row["command"]??"")===$needle){echo $row["count"]??"";break;}}' "$command")
[[ -n $cron_line ]] || { printf 'Unable to identify temporary cron line\n' >&2; exit 15; }

for _ in {1..18}; do
  sleep 10
  if fetch "$private_root/storage/logs" "deploy-$release.log" "$work/deploy.log" 2>/dev/null && rg -q 'CAMPUS_RELEASE_OK' "$work/deploy.log"; then break; fi
done
rg -q 'CAMPUS_RELEASE_OK' "$work/deploy.log" || { sed -n '1,120p' "$work/deploy.log" >&2; exit 14; }
api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=remove_line' --data-urlencode "line=$cron_line" \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1)exit(16);'
cron_line=

home=$(curl --connect-timeout 15 --max-time 45 -fsS "$public_base/")
rg -q 'Toolkit Virtual Campus' <<<"$home"
login=$(curl --connect-timeout 15 --max-time 45 -fsS "$public_base/login")
rg -q 'Sign in' <<<"$login"
asset=$(sed -n 's/.*src="\([^\"]*\/build\/assets\/app-[^\"]*\.js\)".*/\1/p' <<<"$home" | head -n 1)
[[ -n $asset ]]
if [[ $asset == http://* || $asset == https://* ]]; then asset_url=$asset; else asset_url="$public_base$asset"; fi
[[ $(curl --connect-timeout 15 --max-time 45 -sS -o /dev/null -w '%{http_code}' "$asset_url") == 200 ]]
[[ $(curl --connect-timeout 15 --max-time 45 -sS -o /dev/null -w '%{http_code}' "$public_base/register") != 200 ]]

printf 'environment=staging release=%s commit=%s url=%s verified=1\n' "$release" "$commit" "$public_base"
