#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.09.16.3
base_ref=158a949
target_ref=756ffc3
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
[ ! -e "$rollback" ] || { printf 'rollback exists: %s\n' "$rollback" >&2; exit 3; }

cp_auth=${CPANEL_AUTH:-}
[ -n "$cp_auth" ] || [ ! -f "$secrets_file" ] || cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
[ -n "$cp_auth" ] || { printf 'missing cPanel credentials\n' >&2; exit 4; }

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

files=( single.php functions.php )

fetch_file() {
	local remote_file=$1 destination=$2
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 60 -fsS \
		-b "$CPANEL_SESSION_COOKIE" -G \
		--data-urlencode "dir=$remote_theme" \
		--data-urlencode "file=$remote_file" \
		--data-urlencode 'update_html_document_encoding=0' \
		--data-urlencode 'to_charset=utf-8' \
		"$CPANEL_SESSION_API/Fileman/get_file_content" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||!isset($j["data"]["content"]))exit(5);echo $j["data"]["content"];' > "$destination"
}

upload_file() {
	local local_file=$1
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 90 -fsS \
		-b "$CPANEL_SESSION_COOKIE" -F "dir=$remote_theme" -F 'overwrite=1' -F "file-1=@$local_file" \
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
php -l "$work/single.php" >/dev/null
rg -q "return '${release}';" "$work/functions.php"
rg -q 'VR Welding Training in Kakuma' "$work/functions.php"
rg -q 'Welding Instructor Training in Kenya' "$work/functions.php"
rg -q 'More welding insights' "$work/single.php"
rg -q "post_name__in" "$work/single.php"
printf 'payload validation: ok\n'

for file in "${files[@]}"; do
	mkdir -p "$rollback/wp-content/themes/eduma-child"
	fetch_file "$file" "$rollback/wp-content/themes/eduma-child/$file"
done

printf 'uploading templates\n'
upload_file "$work/single.php"
upload_file "$work/functions.php"

printf 'verifying deployed files\n'
for file in "${files[@]}"; do
	fetch_file "$file" "$work/verify-$file"
	cmp "$work/$file" "$work/verify-$file"
done

check_page() {
	local path=$1 output=$2
	local code
	code=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 45 -sS -o "$output" -w '%{http_code}' "$public_base$path")
	[ "$code" = 200 ] || { printf 'route failed: %s%s (%s)\n' "$public_base" "$path" "$code" >&2; exit 7; }
	! rg -qi 'fatal error|critical error|uncaught error' "$output"
}

check_page '/careers-in-mig-mag-welding-insights-from-our-webinar/?cluster_check=202609163' "$work/careers.html"
check_page '/kakuma-refugee-camp-gets-top-technology-training-welders-with-virtual-reality-simulators/?cluster_check=202609163' "$work/kakuma.html"
check_page '/empowering-kenyan-welding-instructors-a-journey-of-excellence-and-inclusion/?cluster_check=202609163' "$work/instructors.html"
check_page '/geofrey-mosiria-visits-the-toolkit/?cluster_check=202609163' "$work/non-welding.html"

rg -q '<title>MIG/MAG Welding Careers Webinar' "$work/careers.html"
rg -q 'More welding insights' "$work/careers.html"
rg -q 'Explore welding training' "$work/careers.html"
rg -q 'VR Welding Training in Kakuma' "$work/kakuma.html"
rg -q 'refugee and host-community youth' "$work/kakuma.html"
rg -q 'Welding Instructor Training in Kenya' "$work/instructors.html"
rg -q '40-hour training programme' "$work/instructors.html"
rg -q 'More from Toolkit' "$work/non-welding.html"
if rg -q 'More welding insights' "$work/non-welding.html"; then
	printf 'welding cluster leaked into non-welding story\n' >&2
	exit 8
fi

release_header=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 30 -fsSI "$public_base/?release_check=$release" | tr -d '\r' | sed -n 's/^x-toolkit-release: //p' | tail -n 1)
[ "$release_header" = "$release" ] || { printf 'unexpected release header: %s\n' "$release_header" >&2; exit 9; }

printf 'environment=%s release=%s files=verified-byte-identical routes=4x200 cluster=verified rollback=%s\n' \
	"$environment" "$release" "$rollback"
