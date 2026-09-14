#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../../.." && pwd)
release=2026.09.14.1
target_ref=toolkit-release-${release}
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$repo/.toolkit-deploy/secrets.env}

case "$environment" in
	demo)
		remote_theme=/home/bfyigiln/demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child
		public_base=https://demo.toolkitafrica.ac.ke
		;;
	production)
		remote_theme=/home/bfyigiln/public_html/wp-content/themes/eduma-child
		public_base=https://toolkitafrica.ac.ke
		;;
	*)
		printf 'usage: %s demo|production\n' "$0" >&2
		exit 2
		;;
esac

git -C "$repo" rev-parse --verify "$target_ref^{commit}" >/dev/null
rollback="$repo/rollbacks/${environment}-pre-${release}"
[ ! -e "$rollback" ] || { printf 'rollback exists: %s\n' "$rollback" >&2; exit 3; }

cp_auth=${CPANEL_AUTH:-}
[ -n "$cp_auth" ] || [ ! -f "$secrets_file" ] || cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
[ -n "$cp_auth" ] || { printf 'missing cPanel credentials\n' >&2; exit 4; }

work=$(mktemp -d)
source "$repo/scripts/deployment/lib/cpanel-session.sh"
cpanel_session_open "$cp_auth"
cleanup() {
	cpanel_session_close
	rm -rf -- "$work"
}
trap cleanup EXIT

fetch() {
	local dir=$1 file=$2 out=$3
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 60 -fsS -b "$CPANEL_SESSION_COOKIE" -G \
		--data-urlencode "dir=$dir" \
		--data-urlencode "file=$file" \
		--data-urlencode 'update_html_document_encoding=0' \
		--data-urlencode 'to_charset=utf-8' \
		"$CPANEL_SESSION_API/Fileman/get_file_content" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true); if(($j["status"]??0)!==1||!isset($j["data"]["content"])) exit(5); echo $j["data"]["content"];' > "$out"
}

upload() {
	local dir=$1 file=$2
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 90 -fsS -b "$CPANEL_SESSION_COOKIE" \
		-F "dir=$dir" -F 'overwrite=1' -F "file-1=@$file" "$CPANEL_SESSION_API/Fileman/upload_files" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true); if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0) exit(6);'
}

files=(functions.php inc/application-adapter.php assets/js/application-form.js)
for relative in "${files[@]}"; do
	remote_dir="$remote_theme/$(dirname "$relative")"
	[ "$(dirname "$relative")" = . ] && remote_dir=$remote_theme
	mkdir -p "$rollback/wp-content/themes/eduma-child/$(dirname "$relative")" "$work/wp-content/themes/eduma-child/$(dirname "$relative")"
	fetch "$remote_dir" "$(basename "$relative")" "$rollback/wp-content/themes/eduma-child/$relative"
	cp "$rollback/wp-content/themes/eduma-child/$relative" "$work/wp-content/themes/eduma-child/$relative"
	git -C "$repo" diff --binary "$target_ref^" "$target_ref" -- "wp-content/themes/eduma-child/$relative" \
		| patch --batch --fuzz=0 -d "$work" -p1
done

php -l "$work/wp-content/themes/eduma-child/functions.php" >/dev/null
php -l "$work/wp-content/themes/eduma-child/inc/application-adapter.php" >/dev/null
node --check "$work/wp-content/themes/eduma-child/assets/js/application-form.js"
rg -q "return '${release}';" "$work/wp-content/themes/eduma-child/functions.php"
rg -q 'function toolkit_application_visible_options' "$work/wp-content/themes/eduma-child/inc/application-adapter.php"
rg -q "indexOf\('kakuma'\)" "$work/wp-content/themes/eduma-child/assets/js/application-form.js"

# Activate PHP first, add the stale-response browser safeguard second, and
# upload functions.php last so its release change purges caches only after all
# dependent files are present.
upload "$remote_theme/inc" "$work/wp-content/themes/eduma-child/inc/application-adapter.php"
upload "$remote_theme/assets/js" "$work/wp-content/themes/eduma-child/assets/js/application-form.js"
upload "$remote_theme" "$work/wp-content/themes/eduma-child/functions.php"

for relative in "${files[@]}"; do
	remote_dir="$remote_theme/$(dirname "$relative")"
	[ "$(dirname "$relative")" = . ] && remote_dir=$remote_theme
	fetch "$remote_dir" "$(basename "$relative")" "$work/verify-$(basename "$relative")"
	cmp "$work/wp-content/themes/eduma-child/$relative" "$work/verify-$(basename "$relative")"
done

page_url="$public_base/apply/?toolkit_release_check=$release"
code=$(curl -sS --max-time 30 -o "$work/apply.html" -w '%{http_code}' "$page_url")
[ "$code" = 200 ] || { printf 'apply route failed: %s (%s)\n' "$page_url" "$code" >&2; exit 7; }
rg -q "application-form\\.js\\?ver=${release}" "$work/apply.html"
if rg -qi 'fatal error|critical error|uncaught error' "$work/apply.html"; then
	printf 'PHP error marker found on %s\n' "$page_url" >&2
	exit 8
fi

token=$(php -r '$h=file_get_contents($argv[1]); preg_match("/\\\"formToken\\\":\\\"([^\\\"]+)/",$h,$m); echo $m[1]??"";' "$work/apply.html")
[ -n "$token" ] || { printf 'application form token missing\n' >&2; exit 9; }
options_code=$(curl -sS --max-time 30 -o "$work/options.json" -w '%{http_code}' \
	-H "X-Toolkit-Form-Token: $token" -H "Origin: $public_base" \
	"$public_base/wp-json/toolkit/v1/application/options")
[ "$options_code" = 200 ] || { printf 'options request failed: %s\n' "$options_code" >&2; exit 10; }
php -r '
	$j=json_decode(file_get_contents($argv[1]),true);
	$schools=$j["schools"]??array();
	if(!$schools) { fwrite(STDERR,"no public campuses returned\n"); exit(11); }
	foreach($schools as $school) {
		$label=(string)($school["label"]??"");
		if(stripos($label,"kakuma")!==false) { fwrite(STDERR,"Kakuma remains visible\n"); exit(12); }
		echo $label,"\n";
	}
' "$work/options.json"

printf 'environment=%s release=%s route=200 options=200 kakuma=hidden files=verified-byte-identical rollback=%s\n' \
	"$environment" "$release" "$rollback"
