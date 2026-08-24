#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../../.." && pwd)
release=2026.08.24.4
endpoint=https://wp46.host-ww.net:2083/execute
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$repo/.toolkit-deploy/secrets.env}
case "$environment" in
	demo) remote_theme=/home/bfyigiln/demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child; public_base=https://demo.toolkitafrica.ac.ke ;;
	production) remote_theme=/home/bfyigiln/public_html/wp-content/themes/eduma-child; public_base=https://toolkitafrica.ac.ke ;;
	*) printf 'usage: %s demo|production\n' "$0" >&2; exit 2 ;;
esac

rollback="$repo/rollbacks/${environment}-pre-${release}"
[ ! -e "$rollback" ] || { printf 'rollback exists: %s\n' "$rollback" >&2; exit 3; }
cp_auth=${CPANEL_AUTH:-}; [ -n "$cp_auth" ] || [ ! -f "$secrets_file" ] || cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
[ -n "$cp_auth" ] || exit 4
work=$(mktemp -d); trap 'rm -rf -- "$work"' EXIT
fetch() { curl -fsS -G -u "$cp_auth" --data-urlencode "dir=$1" --data-urlencode "file=$2" --data-urlencode 'update_html_document_encoding=0' --data-urlencode 'to_charset=utf-8' "$endpoint/Fileman/get_file_content" | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(($j["status"]??0)!==1||!isset($j["data"]["content"])) exit(5); echo $j["data"]["content"];' > "$3"; }
upload() { curl -fsS -u "$cp_auth" -F "dir=$1" -F 'overwrite=1' -F "file-1=@$2" "$endpoint/Fileman/upload_files" | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0) exit(6);'; }

mkdir -p "$rollback/wp-content/themes/eduma-child/inc" "$work/wp-content/themes/eduma-child/inc"
for relative in functions.php inc/course-catalog.php; do
	remote_dir=$remote_theme; [ "$(dirname "$relative")" = inc ] && remote_dir="$remote_theme/inc"
	fetch "$remote_dir" "$(basename "$relative")" "$rollback/wp-content/themes/eduma-child/$relative"
	cp "$rollback/wp-content/themes/eduma-child/$relative" "$work/wp-content/themes/eduma-child/$relative"
	git -C "$repo" diff --binary HEAD^ HEAD -- "wp-content/themes/eduma-child/$relative" | patch --batch --fuzz=0 -d "$work" -p1
done
php -l "$work/wp-content/themes/eduma-child/functions.php" >/dev/null
php -l "$work/wp-content/themes/eduma-child/inc/course-catalog.php" >/dev/null
rg -q "return '${release}';" "$work/wp-content/themes/eduma-child/functions.php"

for image in "$repo"/wp-content/themes/eduma-child/assets/images/courses/{electrical-installation,solar-pv-installer,solar-electrician,solar-upskilling,advanced-welding-vr,advanced-welding-upskilling,smart-agriculture,entrepreneurship,digital-skills}.webp; do upload "$remote_theme/assets/images/courses" "$image"; done
upload "$remote_theme/inc" "$work/wp-content/themes/eduma-child/inc/course-catalog.php"
upload "$remote_theme" "$work/wp-content/themes/eduma-child/functions.php"

catalog=$(curl -fsSL --max-time 30 "$public_base/our-ventures/?toolkit_release_check=$release")
printf '%s' "$catalog" | rg -q "ver=${release}"
for name in electrical-installation solar-pv-installer solar-electrician solar-upskilling advanced-welding-vr advanced-welding-upskilling smart-agriculture entrepreneurship digital-skills; do
	[ "$environment" = demo ] || printf '%s' "$catalog" | rg -q "$name\.webp"
	curl -fsS --max-time 20 -o /dev/null "$public_base/wp-content/themes/eduma-child/assets/images/courses/$name.webp"
done
printf 'environment=%s release=%s distinct-course-images=15 route=200 rollback=%s\n' "$environment" "$release" "$rollback"
