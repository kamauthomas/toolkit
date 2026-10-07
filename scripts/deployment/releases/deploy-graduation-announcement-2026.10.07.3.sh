#!/usr/bin/env bash
set -euo pipefail
environment=${1:-}
repo=$(git -C "$(dirname -- "${BASH_SOURCE[0]}")" rev-parse --show-toplevel)
release=2026.10.07.3
case "$environment" in
  demo) root=/home/bfyigiln/demo.toolkitafrica.ac.ke; base=https://demo.toolkitafrica.ac.ke ;;
  production) root=/home/bfyigiln/public_html; base=https://toolkitafrica.ac.ke ;;
  *) printf 'Usage: %s demo|production\n' "$0" >&2; exit 2 ;;
esac
remote_theme="$root/wp-content/themes/eduma-child"
theme="$repo/wp-content/themes/eduma-child"
evidence="$repo/reports/graduation-popup-2026-10-07/deployment/$environment"
rollback="$repo/rollbacks/$environment-pre-$release"
[[ ! -e "$rollback" ]] || { printf 'Rollback already exists; refusing repeat deployment\n' >&2; exit 3; }
mkdir -p "$evidence" "$rollback"
cp_auth=${CPANEL_AUTH:-}
[[ -n "$cp_auth" ]] || cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "${TOOLKIT_DEPLOY_SECRETS:-$repo/.toolkit-deploy/secrets.env}" | tail -n 1)
source "$repo/scripts/deployment/lib/cpanel-session.sh"
cpanel_session_open "$cp_auth"
cpanel_user=${cp_auth%%:*}
trap cpanel_session_close EXIT
fetch() {
  local file=$1 target=$2
  mkdir -p "$(dirname "$target")"
  curl --retry 3 --retry-all-errors --retry-delay 2 -fsS --connect-timeout 15 --max-time 60 -b "$CPANEL_SESSION_COOKIE" -G \
    --data-urlencode "dir=$remote_theme/$(dirname "$file")" --data-urlencode "file=$(basename "$file")" \
    --data-urlencode 'update_html_document_encoding=0' --data-urlencode 'to_charset=utf-8' \
    "$CPANEL_SESSION_API/Fileman/get_file_content" \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||!isset($j["data"]["content"]))exit(5);echo $j["data"]["content"];' > "$target"
}
fetch_optional() {
  local file=$1 target=$2 response="$evidence/optional-status/$(basename "$1").json"
  mkdir -p "$(dirname "$target")" "$(dirname "$response")"
  curl --retry 3 --retry-all-errors --retry-delay 2 -fsS --connect-timeout 15 --max-time 60 -b "$CPANEL_SESSION_COOKIE" -G \
    --data-urlencode "dir=$remote_theme/$(dirname "$file")" --data-urlencode "file=$(basename "$file")" \
    --data-urlencode 'update_html_document_encoding=0' --data-urlencode 'to_charset=utf-8' \
    "$CPANEL_SESSION_API/Fileman/get_file_content" -o "$response" || return 8
  php -r '$j=json_decode(file_get_contents($argv[1]),true);if(($j["status"]??0)===1&&isset($j["data"]["content"])){echo $j["data"]["content"];exit(0);}$errors=implode(" ",(array)($j["errors"]??[]));if(preg_match("/does not exist|doesn.t exist|No such file(?: or directory)?|not found/i",$errors))exit(4);fwrite(STDERR,"Remote file absence was not established; refusing overwrite.\n");exit(8);' "$response" > "$target"
}
upload() {
  local file=$1
  curl --retry 3 --retry-all-errors --retry-delay 2 -fsS --connect-timeout 15 --max-time 90 -b "$CPANEL_SESSION_COOKIE" \
    -F "dir=$remote_theme/$(dirname "$file")" -F 'overwrite=1' -F "file-1=@$evidence/payload/$file" \
    "$CPANEL_SESSION_API/Fileman/upload_files" \
    | php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0)exit(6);'
}
existing=(functions.php template-parts/pages/graduation.php)
added=(inc/graduation-announcement.php template-parts/graduation-announcement.php assets/css/graduation-announcement.css assets/js/graduation-announcement.js assets/calendar/toolkit-graduation-2026.ics)
for file in "${existing[@]}"; do
  fetch "$file" "$rollback/$file"
  # Compare with the inspected environment, then patch only exact anchors.
  cmp "$rollback/$file" "$repo/reports/graduation-popup-2026-10-07/baseline/$environment/$file"
  mkdir -p "$evidence/payload/$(dirname "$file")"
  cp "$rollback/$file" "$evidence/payload/$file"
done
patch --batch --fuzz=0 -d "$evidence/payload" -p4 < "$repo/scripts/deployment/releases/graduation-announcement-$release-$environment.patch"
for file in "${added[@]}"; do
  # Never replace an uninspected remote file that happens to have this name.
  if fetch_optional "$file" "$evidence/pre-existing/$file"; then
    if ! cmp -s "$evidence/pre-existing/$file" "$theme/$file"; then
      cmp "$evidence/pre-existing/$file" "$repo/reports/graduation-popup-2026-10-07/baseline/$environment/$file"
    fi
    mkdir -p "$rollback/$(dirname "$file")"; cp "$evidence/pre-existing/$file" "$rollback/$file"
  else
    status=$?; [[ "$status" = 4 ]] || exit "$status"
    printf '%s\n' "$file" >> "$rollback/NEW-FILES.txt"
  fi
  mkdir -p "$evidence/payload/$(dirname "$file")"; cp "$theme/$file" "$evidence/payload/$file"
done
for file in functions.php inc/graduation-announcement.php template-parts/graduation-announcement.php template-parts/pages/graduation.php; do php -l "$evidence/payload/$file"; done
node --check "$evidence/payload/assets/js/graduation-announcement.js"
rg -Fq "return '$release';" "$evidence/payload/functions.php"
curl --retry 3 --retry-all-errors --retry-delay 2 -fsS --connect-timeout 15 --max-time 60 -b "$CPANEL_SESSION_COOKIE" -G "$CPANEL_SESSION_API2" \
  --data-urlencode "cpanel_jsonapi_user=$cpanel_user" --data-urlencode 'cpanel_jsonapi_apiversion=2' \
  --data-urlencode 'cpanel_jsonapi_module=Fileman' --data-urlencode 'cpanel_jsonapi_func=mkdir' \
  --data-urlencode "path=$remote_theme/assets" --data-urlencode 'name=calendar' --data-urlencode 'permissions=0755' \
  | php -r '$j=json_decode(stream_get_contents(STDIN),true);if((int)($j["cpanelresult"]["event"]["result"]??0)!==1&&!str_contains(json_encode($j),"exists"))exit(7);'
# Activate the hook/cache release only after its dependencies are present.
for file in "${added[@]}" template-parts/pages/graduation.php functions.php; do upload "$file"; done
for file in "${added[@]}" "${existing[@]}"; do
  fetch "$file" "$evidence/verified/$file"; cmp "$evidence/payload/$file" "$evidence/verified/$file"
done
for path in / /our-ventures/ /graduation/ /apply/ /contact/; do
  name=$(printf '%s' "$path" | tr '/' '-'); [[ "$name" != '-' ]] || name=home
  curl --retry 3 --retry-all-errors --retry-delay 2 -fsS --connect-timeout 15 --max-time 60 -D "$evidence/$name.headers" \
    "$base$path?graduation_check=$release" -o "$evidence/$name.html"
  ! rg -qi 'fatal error|critical error|uncaught error' "$evidence/$name.html"
  rg -Fiq "x-toolkit-release: $release" "$evidence/$name.headers"
  case "$path" in
    /apply/|/contact/) ! rg -q 'data-graduation-announcement' "$evidence/$name.html" ;;
    *) rg -q 'data-graduation-announcement' "$evidence/$name.html"; rg -q "graduation-announcement.js.*$release" "$evidence/$name.html" ;;
  esac
done
rg -Fq 'Thursday, 22 October 2026' "$evidence/-graduation-.html"
for file in assets/css/graduation-announcement.css assets/js/graduation-announcement.js assets/calendar/toolkit-graduation-2026.ics assets/images/toolkit-logo.png assets/images/graduation/kmm-2143-jpg.webp; do
  mkdir -p "$evidence/public/$(dirname "$file")"
  curl --retry 3 --retry-all-errors --retry-delay 2 -fsS --connect-timeout 15 --max-time 60 "$base/wp-content/themes/eduma-child/$file?ver=$release" -o "$evidence/public/$file"
  cmp "$theme/$file" "$evidence/public/$file"
done
python3 - "$evidence" "$environment" <<'PY'
import hashlib,json,sys
from pathlib import Path
p=Path(sys.argv[1]);rows=[{'file':str(f.relative_to(p/'verified')),'sha256':hashlib.sha256(f.read_bytes()).hexdigest()} for f in sorted((p/'verified').rglob('*')) if f.is_file()]
report={'environment':sys.argv[2],'release':'2026.10.07.3','files':rows,'public_routes_checked':5,'popup_excluded':['/apply/','/contact/'],'unchanged_official_logo_and_source_photo_verified':True,'forms_submitted':0}
(p/'verification.json').write_text(json.dumps(report,indent=2)+'\n');print(json.dumps(report))
PY
