#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.08.20.1
base_ref=e30af2f
target_ref=9a7244b
endpoint=https://wp46.host-ww.net:2083/execute
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$repo/.toolkit-deploy/secrets.env}
case "$environment" in
	demo) remote_theme=/home/bfyigiln/demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child; public_url=https://demo.toolkitafrica.ac.ke/apply/ ;;
	production) remote_theme=/home/bfyigiln/public_html/wp-content/themes/eduma-child; public_url=https://toolkitafrica.ac.ke/apply/ ;;
	*) printf 'usage: %s demo|production\n' "$0" >&2; exit 2 ;;
esac
rollback="$repo/rollbacks/${environment}-pre-${release}"
[ ! -e "$rollback" ] || { printf 'rollback exists: %s\n' "$rollback" >&2; exit 3; }
cp_auth=${CPANEL_AUTH:-}
if [ -z "$cp_auth" ] && [ -f "$secrets_file" ]; then cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1); fi
[ -n "$cp_auth" ] || { printf 'set CPANEL_AUTH\n' >&2; exit 4; }
work=$(mktemp -d)
trap 'rm -rf -- "$work"' EXIT
mkdir -p "$work/wp-content/themes/eduma-child/inc" "$work/verify/inc" "$rollback/wp-content/themes/eduma-child/inc"
fetch() {
	dir=$1; file=$2; out=$3
	response=$(curl -fsS -G -u "$cp_auth" --data-urlencode "dir=$dir" --data-urlencode "file=$file" --data-urlencode 'update_html_document_encoding=0' --data-urlencode 'to_charset=utf-8' "$endpoint/Fileman/get_file_content")
	printf '%s' "$response" | php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||!isset($j["data"]["content"]))exit(5);echo $j["data"]["content"];' > "$out"
}
upload() {
	dir=$1; file=$2
	response=$(curl -fsS -u "$cp_auth" -F "dir=$dir" -F 'overwrite=1' -F "file-1=@$file" "$endpoint/Fileman/upload_files")
	printf '%s' "$response" | php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0)exit(6);'
}
for rel in functions.php inc/application-adapter.php inc/calling-letters.php; do
	dir="$remote_theme"; file=$rel
	if [[ "$rel" == inc/* ]]; then dir="$remote_theme/inc"; file=${rel#inc/}; fi
	fetch "$dir" "$file" "$rollback/wp-content/themes/eduma-child/$rel"
	cp "$rollback/wp-content/themes/eduma-child/$rel" "$work/wp-content/themes/eduma-child/$rel"
	git -C "$repo" diff --binary "$base_ref" "$target_ref" -- "wp-content/themes/eduma-child/$rel" | patch --batch --fuzz=0 -d "$work" -p1
done
php -l "$work/wp-content/themes/eduma-child/functions.php" >/dev/null
php -l "$work/wp-content/themes/eduma-child/inc/application-adapter.php" >/dev/null
php -l "$work/wp-content/themes/eduma-child/inc/calling-letters.php" >/dev/null
rg -q "return '${release}';" "$work/wp-content/themes/eduma-child/functions.php"
rg -q 'ON DUPLICATE KEY UPDATE' "$work/wp-content/themes/eduma-child/inc/calling-letters.php"
upload "$remote_theme/inc" "$work/wp-content/themes/eduma-child/inc/application-adapter.php"
upload "$remote_theme/inc" "$work/wp-content/themes/eduma-child/inc/calling-letters.php"
upload "$remote_theme" "$work/wp-content/themes/eduma-child/functions.php"
fetch "$remote_theme/inc" application-adapter.php "$work/verify/inc/application-adapter.php"
fetch "$remote_theme/inc" calling-letters.php "$work/verify/inc/calling-letters.php"
fetch "$remote_theme" functions.php "$work/verify/functions.php"
cmp "$work/wp-content/themes/eduma-child/inc/application-adapter.php" "$work/verify/inc/application-adapter.php"
cmp "$work/wp-content/themes/eduma-child/inc/calling-letters.php" "$work/verify/inc/calling-letters.php"
cmp "$work/wp-content/themes/eduma-child/functions.php" "$work/verify/functions.php"
code=$(curl -sS --max-time 30 -o "$work/apply.html" -w '%{http_code}' "$public_url?toolkit_release_check=$release")
[ "$code" = 200 ]
rg -q "ver=${release}" "$work/apply.html"
printf 'environment=%s release=%s rollback=%s http=%s files=verified-byte-identical\n' "$environment" "$release" "$rollback" "$code"
