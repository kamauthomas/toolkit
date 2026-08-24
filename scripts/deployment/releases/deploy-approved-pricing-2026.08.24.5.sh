#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../../.." && pwd)
release=2026.08.24.5
change_commit=8c94e11
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
[ -n "$cp_auth" ] || { printf 'missing CPANEL_AUTH\n' >&2; exit 4; }
work=$(mktemp -d); trap 'rm -rf -- "$work"' EXIT
fetch() { curl -fsS -G -u "$cp_auth" --data-urlencode "dir=$1" --data-urlencode "file=$2" --data-urlencode 'update_html_document_encoding=0' --data-urlencode 'to_charset=utf-8' "$endpoint/Fileman/get_file_content" | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(($j["status"]??0)!==1||!isset($j["data"]["content"])) exit(5); echo $j["data"]["content"];' > "$3"; }
upload() { curl -fsS -u "$cp_auth" -F "dir=$1" -F 'overwrite=1' -F "file-1=@$2" "$endpoint/Fileman/upload_files" | php -r '$j=json_decode(stream_get_contents(STDIN),true); if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0) exit(6);'; }

mkdir -p "$rollback/wp-content/themes/eduma-child/template-parts/pages" "$work/wp-content/themes/eduma-child/template-parts/pages"
for relative in functions.php page-redesign.css template-parts/pages/courses.php template-parts/pages/course-detail.php; do
	remote_dir="$remote_theme/$(dirname "$relative")"
	[ "$(dirname "$relative")" = . ] && remote_dir=$remote_theme
	fetch "$remote_dir" "$(basename "$relative")" "$rollback/wp-content/themes/eduma-child/$relative"
	cp "$rollback/wp-content/themes/eduma-child/$relative" "$work/wp-content/themes/eduma-child/$relative"
	git -C "$repo" diff --binary "${change_commit}^" "$change_commit" -- "wp-content/themes/eduma-child/$relative" | patch --batch --fuzz=0 -d "$work" -p1
done
php -l "$work/wp-content/themes/eduma-child/functions.php" >/dev/null
php -l "$work/wp-content/themes/eduma-child/template-parts/pages/courses.php" >/dev/null
php -l "$work/wp-content/themes/eduma-child/template-parts/pages/course-detail.php" >/dev/null
rg -q "return '${release}';" "$work/wp-content/themes/eduma-child/functions.php"

upload "$remote_theme/template-parts/pages" "$work/wp-content/themes/eduma-child/template-parts/pages/courses.php"
upload "$remote_theme/template-parts/pages" "$work/wp-content/themes/eduma-child/template-parts/pages/course-detail.php"
upload "$remote_theme" "$work/wp-content/themes/eduma-child/page-redesign.css"
upload "$remote_theme" "$work/wp-content/themes/eduma-child/functions.php"

page=$(curl -fsSL --max-time 30 "$public_base/our-ventures/?toolkit_release_check=$release")
printf '%s' "$page" | rg -q "ver=${release}"
printf 'environment=%s release=%s route=200 rollback=%s\n' "$environment" "$release" "$rollback"
