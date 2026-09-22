#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.09.22.2
base_ref=81e81f5
target_ref=0e6c947
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
	*) printf 'usage: %s demo|production\n' "$0" >&2; exit 2 ;;
esac

rollback="$repo/rollbacks/${environment}-pre-${release}"
[ ! -e "$rollback" ] || { printf 'rollback exists: %s\n' "$rollback" >&2; exit 3; }
git -C "$repo" merge-base --is-ancestor "$target_ref" HEAD || { printf 'HEAD does not contain %s\n' "$target_ref" >&2; exit 4; }
git -C "$repo" diff --quiet "$target_ref" -- wp-content/themes/eduma-child/functions.php || { printf 'functions.php differs from %s\n' "$target_ref" >&2; exit 4; }

cp_auth=${CPANEL_AUTH:-}
[ -n "$cp_auth" ] || [ ! -f "$secrets_file" ] || cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
[ -n "$cp_auth" ] || { printf 'missing cPanel credentials\n' >&2; exit 5; }

work=$(mktemp -d)
source "$repo/scripts/deployment/lib/cpanel-session.sh"
cpanel_session_open "$cp_auth"
cleanup() {
	cpanel_session_close
	rm -rf -- "$work"
}
trap cleanup EXIT

fetch_file() {
	local destination=$1
	mkdir -p "$(dirname -- "$destination")"
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 60 -fsS \
		-b "$CPANEL_SESSION_COOKIE" -G --data-urlencode "dir=$remote_theme" \
		--data-urlencode 'file=functions.php' --data-urlencode 'update_html_document_encoding=0' \
		--data-urlencode 'to_charset=utf-8' "$CPANEL_SESSION_API/Fileman/get_file_content" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||!isset($j["data"]["content"]))exit(5);echo $j["data"]["content"];' > "$destination"
}

upload_file() {
	local source_file=$1
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 90 -fsS \
		-b "$CPANEL_SESSION_COOKIE" -F "dir=$remote_theme" -F 'overwrite=1' -F "file-1=@$source_file" \
		"$CPANEL_SESSION_API/Fileman/upload_files" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0)exit(6);'
}

printf 'fetching and patching live functions.php\n'
fetch_file "$work/functions.php"
git -C "$repo" diff --binary "$base_ref" "$target_ref" -- wp-content/themes/eduma-child/functions.php \
	| patch --batch --fuzz=0 -d "$work" -p4

php -l "$work/functions.php" >/dev/null
rg -q "return '${release}';" "$work/functions.php"
[ "$(rg -o "'toolkit-in-brief'" "$work/functions.php" | wc -l)" -ge 6 ]
printf 'payload validation: ok\n'

mkdir -p "$rollback/wp-content/themes/eduma-child"
fetch_file "$rollback/wp-content/themes/eduma-child/functions.php"

printf 'activating release\n'
upload_file "$work/functions.php"
fetch_file "$work/verify/functions.php"
cmp "$work/functions.php" "$work/verify/functions.php"

page="$work/toolkit-in-brief.html"
code=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 45 -sS -o "$page" -w '%{http_code}' \
	"$public_base/about-toolkit-africa/toolkit-in-brief/?style_check=$release")
[ "$code" = 200 ] || { printf 'Toolkit in Brief failed: %s\n' "$code" >&2; exit 7; }
! rg -qi 'fatal error|critical error|uncaught error' "$page"
rg -q 'page-redesign\.css\?ver=2026\.09\.22\.2\.' "$page"
rg -q '<body[^>]*toolkit-modern-surface' "$page"
rg -q 'toolkit-institutional-hero' "$page"
rg -q 'toolkit-institutional-values' "$page"

release_header=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 30 -fsSI \
	"$public_base/about-toolkit-africa/toolkit-in-brief/?release_check=$release" \
	| tr -d '\r' | sed -n 's/^x-toolkit-release: //p' | tail -n 1)
[ "$release_header" = "$release" ] || { printf 'unexpected release header: %s\n' "$release_header" >&2; exit 8; }

about_code=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 30 -sS -o /dev/null -w '%{http_code}' \
	"$public_base/about-toolkit-africa/?regression_check=$release")
[ "$about_code" = 200 ] || { printf 'About regression route failed: %s\n' "$about_code" >&2; exit 9; }

printf 'environment=%s release=%s functions=byte-identical brief=200 styled=1 about=200 rollback=%s\n' \
	"$environment" "$release" "$rollback"
