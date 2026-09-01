#!/usr/bin/env bash
set -euo pipefail

wordpress_repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../../.." && pwd)
campus_repo=$(cd -- "$wordpress_repo/../SmartLecturer_VirtualCampus/virtual-campus" && pwd)
campus_git=$(git -C "$campus_repo" rev-parse --show-toplevel)
release=2026.09.01.4
commit=ea8c2fe
private_root=/home/bfyigiln/virtual-campus-test
public_base=https://campus-test.toolkitafrica.ac.ke
relative=app/Http/Middleware/SecurityHeaders.php
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$wordpress_repo/.toolkit-deploy/secrets.env}

git -C "$campus_git" merge-base --is-ancestor "$commit" HEAD || { printf 'Campus HEAD does not contain %s\n' "$commit" >&2; exit 3; }
[[ -z $(git -C "$campus_git" status --short) ]] || { printf 'Campus tree is dirty\n' >&2; exit 4; }
[[ -f $secrets_file ]] || { printf 'Missing deployment secrets file\n' >&2; exit 5; }

source "$secrets_file"
source "$wordpress_repo/scripts/deployment/lib/cpanel-session.sh"
cpanel_session_open "$CPANEL_AUTH"
cpanel_user=${CPANEL_AUTH%%:*}
cron_line=
cleanup() {
  if [[ -n ${cron_line:-} ]]; then
    api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=remove_line' --data-urlencode "line=$cron_line" >/dev/null 2>&1 || true
  fi
  cpanel_session_close
}
trap cleanup EXIT

api2() {
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 90 -fsS -b "$CPANEL_SESSION_COOKIE" -G "$CPANEL_SESSION_API2" \
    --data-urlencode "cpanel_jsonapi_user=$cpanel_user" --data-urlencode 'cpanel_jsonapi_apiversion=2' "$@"
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
mkdir -p "$rollback/$(dirname -- "$relative")"
chmod 700 "$rollback"
fetch "$private_root/$(dirname -- "$relative")" "$(basename -- "$relative")" "$rollback/$relative"
upload "$private_root/$(dirname -- "$relative")" "$campus_repo/$relative"

log="$private_root/storage/logs/deploy-$release.log"
command="/usr/bin/flock -n /tmp/toolkit-campus-test-deploy.lock -c 'cd $private_root && /usr/local/bin/ea-php84 artisan optimize:clear > $log 2>&1 && /usr/local/bin/ea-php84 artisan config:cache >> $log 2>&1 && echo CAMPUS_INCREMENT_OK >> $log'"
api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=add_line' \
  --data-urlencode "command=$command" --data-urlencode 'minute=*' --data-urlencode 'hour=*' --data-urlencode 'day=*' --data-urlencode 'month=*' --data-urlencode 'weekday=*' \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1)exit(13);'
cron_line=$(api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=listcron' \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);$needle=$argv[1];foreach(($j["cpanelresult"]["data"]??[]) as $row){if(($row["command"]??"")===$needle){echo $row["count"]??"";break;}}' "$command")
[[ -n $cron_line ]] || { printf 'Unable to identify temporary cron line\n' >&2; exit 15; }

for _ in {1..12}; do
  sleep 10
  if fetch "$private_root/storage/logs" "deploy-$release.log" "$rollback/deploy.log" 2>/dev/null && rg -q 'CAMPUS_INCREMENT_OK' "$rollback/deploy.log"; then break; fi
done
rg -q 'CAMPUS_INCREMENT_OK' "$rollback/deploy.log" || { sed -n '1,100p' "$rollback/deploy.log" >&2; exit 14; }
api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=remove_line' --data-urlencode "line=$cron_line" \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1)exit(16);'
cron_line=

headers=$(curl --connect-timeout 15 --max-time 45 -fsSI "$public_base/login")
rg -qi '^Strict-Transport-Security: max-age=31536000' <<<"$headers"
[[ $(curl --connect-timeout 15 --max-time 45 -sS -o /dev/null -w '%{http_code}' "$public_base/") == 200 ]]
printf 'environment=staging release=%s commit=%s url=%s hsts=verified rollback=%s\n' "$release" "$commit" "$public_base" "$rollback"
