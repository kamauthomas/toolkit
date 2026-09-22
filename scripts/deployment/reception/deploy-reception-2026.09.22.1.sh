#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
wordpress_repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../../.." && pwd)
reception_repo=$(cd -- "$wordpress_repo/../reception-system" && pwd)
release=2026.09.22.1
base_commit=f6c4845
target_commit=7e8db57
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$wordpress_repo/.toolkit-deploy/secrets.env}

case "$environment" in
  demo)
    private_root=/home/bfyigiln/reception-system-demo
    public_root=/home/bfyigiln/reception-public-demo
    public_base=https://reception-demo.toolkitafrica.ac.ke
    ;;
  production)
    private_root=/home/bfyigiln/reception-system
    public_root=/home/bfyigiln/reception-public
    public_base=https://reception.toolkitafrica.ac.ke
    ;;
  *) printf 'usage: %s demo|production\n' "$0" >&2; exit 2 ;;
esac

git -C "$reception_repo" merge-base --is-ancestor "$target_commit" HEAD || { printf 'Reception HEAD does not contain %s\n' "$target_commit" >&2; exit 3; }
git -C "$reception_repo" diff --quiet "$target_commit" -- app config database public resources routes || { printf 'Reception deployable payload differs from %s\n' "$target_commit" >&2; exit 3; }
[[ -z $(git -C "$reception_repo" status --porcelain --untracked-files=all -- app config database public resources routes) ]] || { printf 'Reception deployable paths are dirty\n' >&2; exit 4; }
[[ -f $secrets_file ]] || { printf 'Missing deployment secrets file\n' >&2; exit 5; }

source "$secrets_file"
source "$wordpress_repo/scripts/deployment/lib/cpanel-session.sh"
cpanel_session_open "$CPANEL_AUTH"
cpanel_user=${CPANEL_AUTH%%:*}
cron_line=
env_work=
work=$(mktemp -d)
cleanup() {
  if [[ -n ${cron_line:-} ]]; then
    api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=remove_line' --data-urlencode "line=$cron_line" >/dev/null 2>&1 || true
  fi
  [[ -z ${env_work:-} ]] || rm -rf -- "$env_work"
  rm -rf -- "$work"
  cpanel_session_close
}
trap cleanup EXIT

rollback="$wordpress_repo/rollbacks/reception-${environment}-pre-${release}"
[[ ! -e $rollback ]] || { printf 'Rollback already exists: %s\n' "$rollback" >&2; exit 6; }
mkdir -p "$rollback/private" "$rollback/public" "$wordpress_repo/.toolkit-deploy"
chmod 700 "$rollback"

private_files=(
  app/Http/Controllers/LocationCheckoutController.php
  app/Http/Controllers/PublicReceptionController.php
  app/Http/Controllers/Staff/ExportController.php
  app/Http/Controllers/Staff/QrDraftController.php
  app/Http/Controllers/Staff/VisitController.php
  app/Http/Middleware/AuditAccess.php
  app/Http/Middleware/SecurityHeaders.php
  app/Models/Visit.php
  config/reception.php
  database/migrations/2026_09_22_120000_add_location_checkout_metadata_to_visits.php
  resources/views/layouts/reception.blade.php
  resources/views/layouts/staff.blade.php
  resources/views/reception/thanks.blade.php
  resources/views/staff/drafts/index.blade.php
  resources/views/staff/settings/edit.blade.php
  resources/views/staff/users/index.blade.php
  resources/views/staff/visits/show.blade.php
  routes/web.php
)
public_files=(assets/css/reception.css assets/js/location-checkout.js)
new_private_files=(
  app/Http/Controllers/LocationCheckoutController.php
  database/migrations/2026_09_22_120000_add_location_checkout_metadata_to_visits.php
)
new_public_files=(assets/js/location-checkout.js)

api2() {
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 45 -fsS -b "$CPANEL_SESSION_COOKIE" -G "$CPANEL_SESSION_API2" \
    --data-urlencode "cpanel_jsonapi_user=$cpanel_user" --data-urlencode 'cpanel_jsonapi_apiversion=2' "$@"
}
fetch() {
  local dir=$1 file=$2 target=$3
  mkdir -p "$(dirname -- "$target")"
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 45 -fsS -b "$CPANEL_SESSION_COOKIE" -G \
    --data-urlencode "dir=$dir" --data-urlencode "file=$file" --data-urlencode 'show_hidden=1' \
    --data-urlencode 'update_html_document_encoding=0' --data-urlencode 'to_charset=utf-8' \
    "$CPANEL_SESSION_API/Fileman/get_file_content" \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||!isset($j["data"]["content"]))exit(11);echo $j["data"]["content"];' > "$target"
}
upload() {
  local dir=$1 file=$2
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 90 -fsS -b "$CPANEL_SESSION_COOKIE" -F "dir=$dir" -F 'overwrite=1' -F "file-1=@$file" \
    "$CPANEL_SESSION_API/Fileman/upload_files" \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0)exit(12);'
}
is_new_private() {
  local needle=$1 item
  for item in "${new_private_files[@]}"; do [[ $item != "$needle" ]] || return 0; done
  return 1
}
is_new_public() {
  local needle=$1 item
  for item in "${new_public_files[@]}"; do [[ $item != "$needle" ]] || return 0; done
  return 1
}

printf 'checking remote baseline and creating rollback\n'
for relative in "${private_files[@]}"; do
  if is_new_private "$relative"; then
    if fetch "$private_root/$(dirname -- "$relative")" "$(basename -- "$relative")" "$work/unexpected/private/$relative" 2>/dev/null; then
      printf 'Expected new remote file already exists: %s\n' "$relative" >&2
      exit 7
    fi
    mkdir -p "$(dirname -- "$rollback/private/$relative.new")"
    printf 'NEW\n' > "$rollback/private/$relative.new"
  else
    fetch "$private_root/$(dirname -- "$relative")" "$(basename -- "$relative")" "$rollback/private/$relative"
    mkdir -p "$(dirname -- "$work/baseline/$relative")"
    git -C "$reception_repo" show "$base_commit:$relative" > "$work/baseline/$relative"
    cmp "$work/baseline/$relative" "$rollback/private/$relative" || { printf 'Remote baseline drift: %s\n' "$relative" >&2; exit 8; }
  fi
done
for relative in "${public_files[@]}"; do
  if is_new_public "$relative"; then
    if fetch "$public_root/$(dirname -- "$relative")" "$(basename -- "$relative")" "$work/unexpected/public/$relative" 2>/dev/null; then
      printf 'Expected new remote file already exists: public/%s\n' "$relative" >&2
      exit 7
    fi
    mkdir -p "$(dirname -- "$rollback/public/$relative.new")"
    printf 'NEW\n' > "$rollback/public/$relative.new"
  else
    fetch "$public_root/$(dirname -- "$relative")" "$(basename -- "$relative")" "$rollback/public/$relative"
    mkdir -p "$(dirname -- "$work/baseline/public/$relative")"
    git -C "$reception_repo" show "$base_commit:public/$relative" > "$work/baseline/public/$relative"
    cmp "$work/baseline/public/$relative" "$rollback/public/$relative" || { printf 'Remote baseline drift: public/%s\n' "$relative" >&2; exit 8; }
  fi
done
fetch "$private_root" .env "$rollback/private.env"
chmod 600 "$rollback/private.env"

env_work=$(mktemp -d)
chmod 700 "$env_work"
env_payload="$env_work/.env"
cp "$rollback/private.env" "$env_payload"
chmod 600 "$env_payload"
php -r '$f=$argv[1];$pairs=["RECEPTION_RETENTION"=>"indefinite","RECEPTION_GEOFENCE_ENABLED"=>"false","RECEPTION_GEOFENCE_LATITUDE"=>"-1.2704916","RECEPTION_GEOFENCE_LONGITUDE"=>"36.6644851","RECEPTION_GEOFENCE_RADIUS_METERS"=>"100","RECEPTION_GEOFENCE_EXIT_BUFFER_METERS"=>"35","RECEPTION_GEOFENCE_MAX_ACCURACY_METERS"=>"80","RECEPTION_GEOFENCE_CONFIRMATION_SECONDS"=>"30","RECEPTION_GEOFENCE_MONITOR_MINUTES"=>"720"];$s=file_get_contents($f);foreach($pairs as $k=>$v){$line=$k."=".$v;if(preg_match("/^".preg_quote($k,"/")."=.*/m",$s))$s=preg_replace("/^".preg_quote($k,"/")."=.*/m",$line,$s);else $s.=PHP_EOL.$line;}file_put_contents($f,$s);' "$env_payload"

printf 'uploading reception release with geofence disabled\n'
for relative in "${private_files[@]}"; do upload "$private_root/$(dirname -- "$relative")" "$reception_repo/$relative"; done
for relative in "${public_files[@]}"; do upload "$public_root/$(dirname -- "$relative")" "$reception_repo/public/$relative"; done
upload "$private_root" "$env_payload"
rm -rf -- "$env_work"
env_work=

log="$private_root/storage/logs/deploy-${release}.log"
command="/usr/local/bin/ea-php84 $private_root/artisan migrate --force > $log 2>&1 && /usr/local/bin/ea-php84 $private_root/artisan optimize:clear >> $log 2>&1 && /usr/local/bin/ea-php84 $private_root/artisan route:list --name=reception.location-checkout >> $log 2>&1 && echo RECEPTION_RELEASE_OK >> $log"
api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=add_line' \
  --data-urlencode "command=$command" --data-urlencode 'minute=*' --data-urlencode 'hour=*' --data-urlencode 'day=*' --data-urlencode 'month=*' --data-urlencode 'weekday=*' \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1)exit(13);'

cron_line=$(api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=listcron' \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);$needle=$argv[1];foreach(($j["cpanelresult"]["data"]??[]) as $row){if(($row["command"]??"")===$needle){echo $row["count"]??"";break;}}' "$command")
[[ -n $cron_line ]] || { printf 'Unable to identify temporary cron line\n' >&2; exit 15; }

for _ in {1..12}; do
  sleep 10
  if fetch "$private_root/storage/logs" "deploy-${release}.log" "$rollback/deploy.log" 2>/dev/null && rg -q 'RECEPTION_RELEASE_OK' "$rollback/deploy.log"; then break; fi
done
rg -q 'RECEPTION_RELEASE_OK' "$rollback/deploy.log" || { printf 'Migration did not report success\n' >&2; exit 14; }
rg -q 'reception.location-checkout' "$rollback/deploy.log"

api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=remove_line' --data-urlencode "line=$cron_line" \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1)exit(16);'
cron_line=

printf 'verifying remote file identity\n'
for relative in "${private_files[@]}"; do
  fetch "$private_root/$(dirname -- "$relative")" "$(basename -- "$relative")" "$work/verify/private/$relative"
  cmp "$reception_repo/$relative" "$work/verify/private/$relative"
done
for relative in "${public_files[@]}"; do
  fetch "$public_root/$(dirname -- "$relative")" "$(basename -- "$relative")" "$work/verify/public/$relative"
  cmp "$reception_repo/public/$relative" "$work/verify/public/$relative"
done

home="$work/home.html"
checkin="$work/check-in.html"
login="$work/login.html"
[[ $(curl --connect-timeout 15 --max-time 45 -sS -o "$home" -w '%{http_code}' "$public_base/") == 200 ]]
rg -q 'How can we help you today?' "$home"
[[ $(curl --connect-timeout 15 --max-time 45 -sS -o "$checkin" -w '%{http_code}' "$public_base/check-in") == 200 ]]
! rg -qi 'fatal error|critical error|uncaught error' "$home" "$checkin"
[[ $(curl --connect-timeout 15 --max-time 45 -sS -o "$login" -w '%{http_code}' "$public_base/login") == 200 ]]
! rg -qi 'fatal error|critical error|uncaught error' "$login"
headers=$(curl --connect-timeout 15 --max-time 45 -fsSI "$public_base/login")
rg -qi '^Cache-Control: .*no-store' <<<"$headers"
rg -qi '^X-Robots-Tag: noindex, nofollow, noarchive' <<<"$headers"
[[ $(curl --connect-timeout 15 --max-time 45 -sS -o /dev/null -w '%{http_code}' "$public_base/assets/js/location-checkout.js") == 200 ]]
curl --connect-timeout 15 --max-time 45 -fsS "$public_base/assets/js/location-checkout.js" -o "$work/location-checkout.js"
cmp "$reception_repo/public/assets/js/location-checkout.js" "$work/location-checkout.js"

printf 'environment=%s release=%s commit=%s verified=1 geofence=disabled rollback=%s\n' "$environment" "$release" "$target_commit" "$rollback"
