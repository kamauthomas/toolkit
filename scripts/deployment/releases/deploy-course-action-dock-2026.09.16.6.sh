#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.09.16.6
base_ref=93cea4d
target_ref=f3b6395
verify_only=${TOOLKIT_DEPLOY_VERIFY_ONLY:-0}
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
	'page-redesign.css'
	'hero-slider.css'
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
	git -C "$repo" diff --binary "$base_ref" "$target_ref" -- "wp-content/themes/eduma-child/$file" \
		| patch --batch --fuzz=0 -d "$work" -p4
done

php -l "$work/functions.php" >/dev/null
php -l "$work/template-parts/pages/course-detail.php" >/dev/null
for file in page-redesign.css hero-slider.css; do
	[ "$(tr -cd '{' < "$work/$file" | wc -c)" -eq "$(tr -cd '}' < "$work/$file" | wc -c)" ]
done
rg -q "return '${release}';" "$work/functions.php"
rg -Fq 'class="toolkit-course-dock"' "$work/template-parts/pages/course-detail.php"
rg -Fq 'href="#course-details"' "$work/template-parts/pages/course-detail.php"
rg -Fq 'id="course-details"' "$work/template-parts/pages/course-detail.php"
rg -q '\.toolkit-course-dock \{ position:sticky;.*top:0;' "$work/page-redesign.css"
rg -q 'left: 20px;' "$work/hero-slider.css"
rg -q 'right: auto;' "$work/hero-slider.css"
printf 'payload validation: ok\n'

if [ "$verify_only" = 0 ]; then
	for file in "${files[@]}"; do
		fetch_file "$file" "$rollback/wp-content/themes/eduma-child/$file"
	done

	printf 'uploading templates and styles\n'
	upload_file "$work/template-parts/pages/course-detail.php" 'template-parts/pages/course-detail.php'
	upload_file "$work/page-redesign.css" 'page-redesign.css'
	upload_file "$work/hero-slider.css" 'hero-slider.css'
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

check_page '/our-ventures/construction-sector-skills/?dock_check=202609166' "$work/welding.html"
check_page '/our-ventures/renewable-energy/?dock_check=202609166' "$work/solar.html"
check_page '/courses/advanced-welding-vr/?dock_check=202609166' "$work/catalogue.html"
check_page '/our-ventures/construction-sector-skills/recognition-of-prior-learning-rpl/?dock_check=202609166' "$work/rpl.html"
check_page '/?dock_check=202609166' "$work/home.html"

for output in welding solar catalogue rpl; do
	[ "$(rg -o 'class="toolkit-course-dock"' "$work/$output.html" | wc -l)" -eq 1 ]
	rg -Fq 'href="#course-details"' "$work/$output.html"
	rg -Fq 'id="course-details"' "$work/$output.html"
	rg -q 'class="toolkit-btn toolkit-btn--primary" href="[^" ]*/apply/\?course=' "$work/$output.html"
done
rg -q 'KES 176,276 standard; KES 120,962 subsidised' "$work/welding.html"
rg -q '<span>2026 fees</span>' "$work/welding.html"
rg -q '<span>Schedule</span>' "$work/rpl.html"
rg -q 'hero-slider__video-badge' "$work/home.html"

release_header=$(curl --retry 2 --retry-all-errors --connect-timeout 15 --max-time 30 -fsSI "$public_base/?release_check=$release" | tr -d '\r' | sed -n 's/^x-toolkit-release: //p' | tail -n 1)
[ "$release_header" = "$release" ] || { printf 'unexpected release header: %s\n' "$release_header" >&2; exit 8; }

printf 'environment=%s release=%s files=verified-byte-identical routes=5x200 course_docks=4 home_collision_fix=present rollback=%s\n' \
	"$environment" "$release" "$rollback"
