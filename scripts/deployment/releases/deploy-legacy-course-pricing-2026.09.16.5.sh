#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.09.16.5
target_ref=93cea4d
verify_only=${TOOLKIT_DEPLOY_VERIFY_ONLY:-0}
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$repo/.toolkit-deploy/secrets.env}

case "$environment" in
	demo)
		remote_theme=/home/bfyigiln/demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child
		public_base=https://demo.toolkitafrica.ac.ke
		base_ref=a819514
		;;
	production)
		remote_theme=/home/bfyigiln/public_html/wp-content/themes/eduma-child
		public_base=https://toolkitafrica.ac.ke
		base_ref=d4ad2a8
		;;
	*)
		printf 'usage: %s demo|production\n' "$0" >&2
		exit 2
		;;
esac

rollback="$repo/rollbacks/${environment}-pre-${release}"
if [ -e "$rollback" ]; then
	[ "$verify_only" = 1 ] || { printf 'rollback exists: %s\n' "$rollback" >&2; exit 3; }
else
	[ "$verify_only" = 0 ] || { printf 'verify-only requires rollback: %s\n' "$rollback" >&2; exit 3; }
fi

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

files=(
	'template-parts/pages/course-detail.php'
	'inc/course-catalog.php'
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
	if [ "$verify_only" = 1 ]; then
		mkdir -p "$(dirname -- "$work/$file")"
		cp "$rollback/wp-content/themes/eduma-child/$file" "$work/$file"
	else
		fetch_file "$file" "$work/$file"
	fi
	if ! git -C "$repo" diff --quiet "$base_ref" "$target_ref" -- "wp-content/themes/eduma-child/$file"; then
		git -C "$repo" diff --binary "$base_ref" "$target_ref" -- "wp-content/themes/eduma-child/$file" \
			| patch --batch --fuzz=0 -d "$work" -p4
	fi
done

for file in "${files[@]}"; do
	php -l "$work/$file" >/dev/null
done
rg -q "return '${release}';" "$work/functions.php"
rg -q "brochure_pricing.*true" "$work/inc/course-catalog.php"
[ "$(rg -o "brochure_pricing.*true" "$work/inc/course-catalog.php" | wc -l)" -eq 4 ]
rg -Fq "! empty( \$course['brochure_pricing'] )" "$work/template-parts/pages/course-detail.php"
printf 'payload validation: ok\n'

if [ "$verify_only" = 0 ]; then
	for file in "${files[@]}"; do
		fetch_file "$file" "$rollback/wp-content/themes/eduma-child/$file"
	done

	printf 'uploading templates\n'
	upload_file "$work/template-parts/pages/course-detail.php" 'template-parts/pages/course-detail.php'
	upload_file "$work/inc/course-catalog.php" 'inc/course-catalog.php'
	upload_file "$work/functions.php" 'functions.php'
else
	printf 'verify-only: upload skipped\n'
fi

printf 'verifying deployed files\n'
for file in "${files[@]}"; do
	fetch_file "$file" "$work/verify/$file"
	cmp "$work/$file" "$work/verify/$file"
done

check_page() {
	local path=$1 output=$2
	local code
	code=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 45 -sS -o "$output" -w '%{http_code}' "$public_base$path")
	[ "$code" = 200 ] || { printf 'route failed: %s%s (%s)\n' "$public_base" "$path" "$code" >&2; exit 7; }
	! rg -qi 'fatal error|critical error|uncaught error' "$output"
}

check_page '/our-ventures/construction-sector-skills/?fee_check=202609165' "$work/welding.html"
check_page '/our-ventures/renewable-energy/?fee_check=202609165' "$work/solar.html"
check_page '/our-ventures/organic-farming-skills/?fee_check=202609165' "$work/agriculture.html"
check_page '/our-ventures/access-online-jobs/?fee_check=202609165' "$work/digital.html"
check_page '/our-ventures/construction-sector-skills/recognition-of-prior-learning-rpl/?fee_check=202609165' "$work/rpl.html"
check_page '/llms.txt?fee_check=202609165' "$work/llms.txt"

rg -q '2026 fees' "$work/welding.html"
rg -q 'KES 176,276 standard; KES 120,962 subsidised' "$work/welding.html"
rg -q 'Advanced Welding Upskilling: KES 66,300 standard; KES 30,684 subsidised' "$work/welding.html"
rg -q 'KES 150,000 standard; KES 120,962 subsidised' "$work/solar.html"
rg -q 'Solar Upskilling: KES 30,684' "$work/solar.html"
rg -q 'KES 73,586 including examination; short courses from KES 4,000' "$work/agriculture.html"
rg -q 'KES 18,000 to KES 65,000, depending on course' "$work/digital.html"
rg -q 'Contact admissions for current fees and available funding options.' "$work/rpl.html"
if rg -q '2026 fees|KES [0-9]' "$work/rpl.html"; then
	printf 'brochure pricing leaked into unpriced RPL control\n' >&2
	exit 8
fi
rg -q 'Use the published 2026 brochure fees where a course page shows them' "$work/llms.txt"

release_header=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 30 -fsSI "$public_base/?release_check=$release" | tr -d '\r' | sed -n 's/^x-toolkit-release: //p' | tail -n 1)
[ "$release_header" = "$release" ] || { printf 'unexpected release header: %s\n' "$release_header" >&2; exit 9; }

printf 'environment=%s release=%s files=verified-byte-identical routes=6x200 priced_hubs=4 unpriced_control=verified rollback=%s\n' \
	"$environment" "$release" "$rollback"
