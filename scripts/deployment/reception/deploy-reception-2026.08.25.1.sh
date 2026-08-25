#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
wordpress_repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../../.." && pwd)
reception_repo=$(cd -- "$wordpress_repo/../reception-system" && pwd)
release=2026.08.25.1
commit=86b7293
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

git -C "$reception_repo" merge-base --is-ancestor "$commit" HEAD || { printf 'Reception HEAD does not contain %s\n' "$commit" >&2; exit 3; }
git -C "$reception_repo" diff --quiet "$commit"..HEAD -- app config database public resources routes || { printf 'Reception deployable payload changed after %s\n' "$commit" >&2; exit 3; }
[[ -z $(git -C "$reception_repo" status --short) ]] || { printf 'Reception tree is dirty\n' >&2; exit 4; }
[[ -f $secrets_file ]] || { printf 'Missing deployment secrets file\n' >&2; exit 5; }

source "$secrets_file"
source "$wordpress_repo/scripts/deployment/lib/cpanel-session.sh"
cpanel_session_open "$CPANEL_AUTH"
cpanel_user=${CPANEL_AUTH%%:*}
cron_line=
env_work=
cleanup() {
  if [[ -n ${cron_line:-} ]]; then
    api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=remove_line' --data-urlencode "line=$cron_line" >/dev/null 2>&1 || true
  fi
  [[ -z ${env_work:-} ]] || rm -rf -- "$env_work"
  cpanel_session_close
}
trap cleanup EXIT

rollback="$wordpress_repo/rollbacks/reception-${environment}-pre-${release}"
[[ ! -e $rollback ]] || { printf 'Rollback already exists: %s\n' "$rollback" >&2; exit 6; }
mkdir -p "$rollback/private" "$rollback/public" "$wordpress_repo/.toolkit-deploy"
chmod 700 "$rollback"

private_files=(
  app/Console/Commands/ApplyReceptionRetention.php
  app/Http/Controllers/PublicReceptionController.php
  app/Http/Controllers/QrDraftController.php
  app/Http/Controllers/QrEntryController.php
  app/Http/Controllers/Staff/DashboardController.php
  app/Http/Controllers/Staff/SystemSettingsController.php
  app/Http/Middleware/SecurityHeaders.php
  app/Http/Requests/StoreApplicantRequest.php
  app/Http/Requests/StoreVisitRequest.php
  app/Models/Applicant.php
  app/Models/QrScanSession.php
  app/Models/SystemSetting.php
  app/Models/Visit.php
  app/Support/IntegrationSettings.php
  config/reception.php
  config/services.php
  database/migrations/2026_08_25_094500_add_other_details_to_reception_records.php
  database/migrations/2026_08_25_140000_create_qr_scan_sessions_table.php
  database/migrations/2026_08_25_160000_create_system_settings_table.php
  resources/views/layouts/reception.blade.php
  resources/views/layouts/staff.blade.php
  resources/views/reception/applicant.blade.php
  resources/views/reception/home.blade.php
  resources/views/reception/visit.blade.php
  resources/views/staff/applicants/show.blade.php
  resources/views/staff/dashboard.blade.php
  resources/views/staff/settings/edit.blade.php
  resources/views/staff/visits/show.blade.php
  routes/web.php
)
public_files=(assets/css/reception.css assets/js/reception.js)

api2() {
  curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 45 -fsS -b "$CPANEL_SESSION_COOKIE" -G "$CPANEL_SESSION_API2" \
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
backup_if_present() {
  local root=$1 relative=$2 destination=$3
  mkdir -p "$(dirname -- "$destination/$relative")"
  case "$relative" in
    app/Http/Controllers/QrDraftController.php|app/Http/Controllers/QrEntryController.php|app/Http/Controllers/Staff/SystemSettingsController.php|app/Models/QrScanSession.php|app/Models/SystemSetting.php|app/Support/IntegrationSettings.php|config/reception.php|database/migrations/2026_08_25_094500_add_other_details_to_reception_records.php|database/migrations/2026_08_25_140000_create_qr_scan_sessions_table.php|database/migrations/2026_08_25_160000_create_system_settings_table.php|resources/views/staff/settings/edit.blade.php)
      printf 'NEW\n' > "$destination/$relative.new"
      ;;
    *)
      fetch "$root/$(dirname -- "$relative")" "$(basename -- "$relative")" "$destination/$relative"
      ;;
  esac
}

mkdir_remote "$private_root/resources/views/staff" settings
for relative in "${private_files[@]}"; do backup_if_present "$private_root" "$relative" "$rollback/private"; done
for relative in "${public_files[@]}"; do backup_if_present "$public_root" "$relative" "$rollback/public"; done
fetch "$private_root" .env "$rollback/private.env"
chmod 600 "$rollback/private.env"

staff_path_file="$wordpress_repo/.toolkit-deploy/reception-${environment}-staff-path.txt"
if [[ -f $staff_path_file ]]; then
  staff_path=$(<"$staff_path_file")
else
  staff_path="reception-$(openssl rand -hex 10)"
  umask 077; printf '%s\n' "$staff_path" > "$staff_path_file"
fi
env_work=$(mktemp -d); chmod 700 "$env_work"; env_payload="$env_work/.env"; cp "$rollback/private.env" "$env_payload"; chmod 600 "$env_payload"
php -r '$f=$argv[1];$pairs=["RECEPTION_STAFF_PATH"=>$argv[2],"RECEPTION_RETENTION"=>"indefinite","RECEPTION_WHATSAPP_BUSINESS_NUMBER"=>"254711802855"];$s=file_get_contents($f);foreach($pairs as $k=>$v){$line=$k."=".$v;if(preg_match("/^".preg_quote($k,"/")."=.*/m",$s))$s=preg_replace("/^".preg_quote($k,"/")."=.*/m",$line,$s);else $s.=PHP_EOL.$line;}file_put_contents($f,$s);' "$env_payload" "$staff_path"

for relative in "${private_files[@]}"; do upload "$private_root/$(dirname -- "$relative")" "$reception_repo/$relative"; done
for relative in "${public_files[@]}"; do upload "$public_root/$(dirname -- "$relative")" "$reception_repo/public/$relative"; done
upload "$private_root" "$env_payload"
rm -rf "$env_work"; env_work=

log="$private_root/storage/logs/deploy-${release}.log"
command="/usr/local/bin/ea-php84 $private_root/artisan migrate --force > $log 2>&1 && /usr/local/bin/ea-php84 $private_root/artisan optimize:clear >> $log 2>&1 && echo RECEPTION_RELEASE_OK >> $log"
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

api2 --data-urlencode 'cpanel_jsonapi_module=Cron' --data-urlencode 'cpanel_jsonapi_func=remove_line' --data-urlencode "line=$cron_line" \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1)exit(16);'
cron_line=

curl --connect-timeout 15 --max-time 45 -fsS "$public_base/" | rg -q 'How can we help you today?'
[[ $(curl --connect-timeout 15 --max-time 45 -sS -o /dev/null -w '%{http_code}' "$public_base/staff") == 404 ]]
[[ $(curl --connect-timeout 15 --max-time 45 -sS -o /dev/null -w '%{http_code}' "$public_base/qr/onsite/visit.svg") == 200 ]]
headers=$(curl --connect-timeout 15 --max-time 45 -fsSI "$public_base/$staff_path/login")
rg -qi '^Cache-Control: .*no-store' <<<"$headers"
rg -qi '^X-Robots-Tag: noindex, nofollow, noarchive' <<<"$headers"
[[ $(curl --connect-timeout 15 --max-time 45 -sS -o /dev/null -w '%{http_code}' "$public_base/$staff_path/settings/integrations") == 302 ]]

printf 'environment=%s release=%s commit=%s verified=1 rollback=%s\n' "$environment" "$release" "$commit" "$rollback"
