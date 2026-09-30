#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.09.30.2
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
	'inc/site-metrics.php'
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
done
patch --batch --fuzz=0 -d "$work" -p4 < "$script_dir/link-bio-qr-$release.patch"
php -l "$work/functions.php" >/dev/null
php -l "$work/inc/site-metrics.php" >/dev/null
rg -q "return '${release}';" "$work/functions.php"
rg -Fq 'https://lnk.bio/The_Toolkit_Africa' "$work/inc/site-metrics.php"
rg -Fq 'link-bio-qr.svg' "$work/inc/site-metrics.php"
printf 'payload validation: ok\n'

for file in "${files[@]}"; do
	fetch_file "$file" "$rollback/wp-content/themes/eduma-child/$file"
done

# Refuse to replace an unexpected pre-existing QR asset.
for asset in link-bio-qr.png link-bio-qr.svg; do
    status=$(curl --connect-timeout 15 --max-time 30 -sS -o "$work/existing-$asset" -w '%{http_code}' "$public_base/wp-content/themes/eduma-child/assets/images/$asset")
    if [ "$status" = 200 ]; then
        cmp "$repo/wp-content/themes/eduma-child/assets/images/$asset" "$work/existing-$asset"
    else
        [ "$status" = 404 ] || { printf 'unexpected QR asset status: %s\n' "$status" >&2; exit 6; }
    fi
 done
printf 'uploading QR assets and metrics, then activating release\n'
for asset in link-bio-qr.png link-bio-qr.svg; do
    upload_file "$repo/wp-content/themes/eduma-child/assets/images/$asset" "assets/images/$asset"
done
upload_file "$work/inc/site-metrics.php" 'inc/site-metrics.php'
upload_file "$work/functions.php" 'functions.php'

printf 'verifying deployed files\n'
for file in "${files[@]}"; do
	fetch_file "$file" "$work/verify/$file"
	cmp "$work/$file" "$work/verify/$file"
done

for asset in link-bio-qr.png link-bio-qr.svg; do
    curl --connect-timeout 15 --max-time 45 -fsS "$public_base/wp-content/themes/eduma-child/assets/images/$asset?qr_check=$release" -o "$work/verify/$asset"
    cmp "$repo/wp-content/themes/eduma-child/assets/images/$asset" "$work/verify/$asset"
done
for suffix in "?qr_check=$release" ''; do
    page="$work/apply.html"
    code=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 45 -sS -o "$page" -w '%{http_code}' "$public_base/apply/$suffix")
    [ "$code" = 200 ] || { printf 'Apply route failed (%s)\n' "$code" >&2; exit 7; }
    ! rg -qi 'fatal error|critical error|uncaught error' "$page"
    rg -Fq 'id="toolkit-application-form"' "$page"
    rg -Fq 'toolkit/v1/application/submit' "$page"
    rg -q 'toolkit-metrics.js\?ver=' "$page"
done
release_header=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 30 -fsSI \
	"$public_base/?release_check=$release" | tr -d '\r' | sed -n 's/^x-toolkit-release: //p' | tail -n 1)
[ "$release_header" = "$release" ] || { printf 'unexpected release header: %s\n' "$release_header" >&2; exit 8; }

printf 'environment=%s release=%s files=verified-byte-identical apply=200 qr-assets=200 rollback=%s\n' "$environment" "$release" "$rollback"
