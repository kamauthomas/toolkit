#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../../.." && pwd)
release=2026.08.24.3
endpoint=https://wp46.host-ww.net:2083/execute
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$repo/.toolkit-deploy/secrets.env}

case "$environment" in
	demo) remote_theme=/home/bfyigiln/demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child; public_url=https://demo.toolkitafrica.ac.ke/our-ventures/ ;;
	production) remote_theme=/home/bfyigiln/public_html/wp-content/themes/eduma-child; public_url=https://toolkitafrica.ac.ke/our-ventures/ ;;
	*) printf 'usage: %s demo|production\n' "$0" >&2; exit 2 ;;
esac

rollback="$repo/rollbacks/${environment}-pre-${release}"
[ ! -e "$rollback" ] || { printf 'rollback exists: %s\n' "$rollback" >&2; exit 3; }
cp_auth=${CPANEL_AUTH:-}
[ -n "$cp_auth" ] || [ ! -f "$secrets_file" ] || cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
[ -n "$cp_auth" ] || { printf 'missing cPanel credentials\n' >&2; exit 4; }

work=$(mktemp -d); trap 'rm -rf -- "$work"' EXIT
fetch() { curl -fsS -G -u "$cp_auth" --data-urlencode "dir=$1" --data-urlencode "file=$2" --data-urlencode 'update_html_document_encoding=0' --data-urlencode 'to_charset=utf-8' "$endpoint/Fileman/get_file_content" | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(($j["status"]??0)!==1||!isset($j["data"]["content"])) exit(5); echo $j["data"]["content"];' > "$3"; }
upload() { curl -fsS -u "$cp_auth" -F "dir=$1" -F 'overwrite=1' -F "file-1=@$2" "$endpoint/Fileman/upload_files" | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0) exit(6);'; }

files=(functions.php inc/course-catalog.php inc/site-metrics.php page-redesign.css template-parts/pages/course-detail.php)
for relative in "${files[@]}"; do
	remote_dir="$remote_theme/$(dirname "$relative")"; [ "$(dirname "$relative")" = . ] && remote_dir=$remote_theme
	remote_file=$(basename "$relative")
	mkdir -p "$rollback/wp-content/themes/eduma-child/$(dirname "$relative")" "$work/wp-content/themes/eduma-child/$(dirname "$relative")"
	fetch "$remote_dir" "$remote_file" "$rollback/wp-content/themes/eduma-child/$relative"
	cp "$rollback/wp-content/themes/eduma-child/$relative" "$work/wp-content/themes/eduma-child/$relative"
	git -C "$repo" diff --binary HEAD^ HEAD -- "wp-content/themes/eduma-child/$relative" | patch --batch --fuzz=0 -d "$work" -p1
done

for file in "$work"/wp-content/themes/eduma-child/{functions.php,inc/course-catalog.php,inc/site-metrics.php,template-parts/pages/course-detail.php}; do php -l "$file" >/dev/null; done
rg -q "return '${release}';" "$work/wp-content/themes/eduma-child/functions.php"
rg -q '2026 brochure pricing' "$work/wp-content/themes/eduma-child/inc/site-metrics.php"
rg -q "'german-b1'|'advanced-welding-upskilling'" "$work/wp-content/themes/eduma-child/inc/course-catalog.php"

for relative in "${files[@]}"; do
	remote_dir="$remote_theme/$(dirname "$relative")"; [ "$(dirname "$relative")" = . ] && remote_dir=$remote_theme
	upload "$remote_dir" "$work/wp-content/themes/eduma-child/$relative"
	fetch "$remote_dir" "$(basename "$relative")" "$work/verify-$(basename "$relative")"
	cmp "$work/wp-content/themes/eduma-child/$relative" "$work/verify-$(basename "$relative")"
done

code=$(curl -sS --max-time 30 -o "$work/catalog.html" -w '%{http_code}' "$public_url?toolkit_release_check=$release")
[ "$code" = 200 ] || { printf 'catalog route failed: %s\n' "$code" >&2; exit 7; }
rg -q "ver=${release}" "$work/catalog.html"
if rg -qi 'fatal error|critical error|uncaught error' "$work/catalog.html"; then printf 'PHP error marker found\n' >&2; exit 8; fi
printf 'environment=%s release=%s route=200 files=verified-byte-identical pricing-toggle=deployed rollback=%s\n' "$environment" "$release" "$rollback"
