#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.09.22.1
base_ref=03938fd
target_ref=48a946b
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

git -C "$repo" merge-base --is-ancestor "$target_ref" HEAD || { printf 'HEAD does not contain %s\n' "$target_ref" >&2; exit 3; }
git -C "$repo" diff --quiet "$target_ref" -- \
	wp-content/themes/eduma-child/functions.php \
	wp-content/themes/eduma-child/template-parts/pages/contact.php || { printf 'Contact-map payload differs from %s\n' "$target_ref" >&2; exit 3; }

rollback="$repo/rollbacks/${environment}-pre-${release}"
[ ! -e "$rollback" ] || { printf 'rollback exists: %s\n' "$rollback" >&2; exit 4; }

cp_auth=${CPANEL_AUTH:-}
[ -n "$cp_auth" ] || [ ! -f "$secrets_file" ] || cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
[ -n "$cp_auth" ] || { printf 'missing cPanel credentials\n' >&2; exit 5; }

work=$(mktemp -d)
source "$repo/scripts/deployment/lib/cpanel-session.sh"
printf 'opening cPanel session\n'
cpanel_session_open "$cp_auth"
printf 'cPanel session: ok\n'
cleanup() {
	cpanel_session_close
	rm -rf -- "$work"
}
trap cleanup EXIT

files=(
	'template-parts/pages/contact.php'
	'functions.php'
)

fetch_file() {
	local remote_file=$1 destination=$2
	local remote_dir filename
	remote_dir=$(dirname -- "$remote_file")
	filename=$(basename -- "$remote_file")
	[ "$remote_dir" = . ] && remote_dir=''
	mkdir -p "$(dirname -- "$destination")"
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 60 -fsS \
		-b "$CPANEL_SESSION_COOKIE" -G \
		--data-urlencode "dir=$remote_theme${remote_dir:+/$remote_dir}" \
		--data-urlencode "file=$filename" \
		--data-urlencode 'update_html_document_encoding=0' \
		--data-urlencode 'to_charset=utf-8' \
		"$CPANEL_SESSION_API/Fileman/get_file_content" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||!isset($j["data"]["content"]))exit(5);echo $j["data"]["content"];' > "$destination"
}

upload_file() {
	local local_file=$1 remote_file=$2
	local remote_dir
	remote_dir=$(dirname -- "$remote_file")
	[ "$remote_dir" = . ] && remote_dir=''
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 90 -fsS \
		-b "$CPANEL_SESSION_COOKIE" \
		-F "dir=$remote_theme${remote_dir:+/$remote_dir}" \
		-F 'overwrite=1' -F "file-1=@$local_file" \
		"$CPANEL_SESSION_API/Fileman/upload_files" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0)exit(6);'
}

printf 'fetching and patching live files\n'
for file in "${files[@]}"; do
	fetch_file "$file" "$work/$file"
	git -C "$repo" diff --binary "$base_ref" "$target_ref" -- "wp-content/themes/eduma-child/$file" \
		| patch --batch --fuzz=0 -d "$work" -p4
done

php -l "$work/functions.php" >/dev/null
php -l "$work/template-parts/pages/contact.php" >/dev/null
rg -q "return '${release}';" "$work/functions.php"
rg -Fq 'No. 7219 Karen-Kikuyu Highway, Thogoto' "$work/template-parts/pages/contact.php"
rg -Fq 'query=-1.2704916%2C36.6644851' "$work/template-parts/pages/contact.php"
rg -Fq 'q=-1.2704916%2C36.6644851&amp;z=17&amp;output=embed' "$work/template-parts/pages/contact.php"
printf 'payload validation: ok\n'

for file in "${files[@]}"; do
	fetch_file "$file" "$rollback/wp-content/themes/eduma-child/$file"
done

printf 'uploading Contact template and activating release\n'
upload_file "$work/template-parts/pages/contact.php" 'template-parts/pages/contact.php'
upload_file "$work/functions.php" 'functions.php'

printf 'verifying deployed files\n'
for file in "${files[@]}"; do
	fetch_file "$file" "$work/verify/$file"
	cmp "$work/$file" "$work/verify/$file"
done

page="$work/contact.html"
code=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 45 -sS \
	-o "$page" -w '%{http_code}' "$public_base/contact/?map_check=$release")
[ "$code" = 200 ] || { printf 'route failed: %s/contact/ (%s)\n' "$public_base" "$code" >&2; exit 7; }
! rg -qi 'fatal error|critical error|uncaught error' "$page"
rg -Fq 'No. 7219 Karen-Kikuyu Highway, Thogoto' "$page"
rg -Fq 'q=-1.2704916%2C36.6644851&amp;z=17&amp;output=embed' "$page"
rg -Fq 'query=-1.2704916%2C36.6644851' "$page"

release_header=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 30 -fsSI \
	"$public_base/?release_check=$release" | tr -d '\r' | sed -n 's/^x-toolkit-release: //p' | tail -n 1)
[ "$release_header" = "$release" ] || { printf 'unexpected release header: %s\n' "$release_header" >&2; exit 8; }

printf 'environment=%s release=%s files=verified-byte-identical contact=200 map=present rollback=%s\n' \
	"$environment" "$release" "$rollback"
