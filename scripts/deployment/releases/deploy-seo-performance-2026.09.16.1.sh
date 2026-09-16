#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.09.16.1
base_ref=ef5eb65
target_ref=29b4912
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

git -C "$repo" rev-parse --verify "$base_ref^{commit}" >/dev/null
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

text_files=(
	front-page.php
	hero-slider.css
	hero-slider.js
	inc/course-catalog.php
	inc/hero-slides.php
	page-redesign.css
	single.php
	style.css
	template-parts/pages/course-detail.php
	functions.php
)
image_files=(
	assets/images/hero-slide-1-mobile.webp
	assets/images/hero-slide-2-mobile.webp
	assets/images/hero-slide-3-mobile.webp
)

remote_location() {
	local relative=$1
	case "$relative" in
		inc/*) printf '%s\n%s\n' "$remote_theme/inc" "${relative#inc/}" ;;
		template-parts/pages/*) printf '%s\n%s\n' "$remote_theme/template-parts/pages" "${relative#template-parts/pages/}" ;;
		assets/images/*) printf '%s\n%s\n' "$remote_theme/assets/images" "${relative#assets/images/}" ;;
		*) printf '%s\n%s\n' "$remote_theme" "$relative" ;;
	esac
}

fetch_text() {
	local remote_dir=$1 remote_file=$2 destination=$3
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 60 -fsS \
		-b "$CPANEL_SESSION_COOKIE" -G \
		--data-urlencode "dir=$remote_dir" \
		--data-urlencode "file=$remote_file" \
		--data-urlencode 'update_html_document_encoding=0' \
		--data-urlencode 'to_charset=utf-8' \
		"$CPANEL_SESSION_API/Fileman/get_file_content" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||!isset($j["data"]["content"]))exit(5);echo $j["data"]["content"];' > "$destination"
}

upload_file() {
	local remote_dir=$1 local_file=$2
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 90 -fsS \
		-b "$CPANEL_SESSION_COOKIE" -F "dir=$remote_dir" -F 'overwrite=1' -F "file-1=@$local_file" \
		"$CPANEL_SESSION_API/Fileman/upload_files" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0)exit(6);'
}

for relative in "${text_files[@]}"; do
	mapfile -t location < <(remote_location "$relative")
	mkdir -p "$work/current/$(dirname "$relative")" "$work/expected/wp-content/themes/eduma-child/$(dirname "$relative")"
	fetch_text "${location[0]}" "${location[1]}" "$work/current/$relative"
	cp "$work/current/$relative" "$work/expected/wp-content/themes/eduma-child/$relative"
	git -C "$repo" diff --binary "$base_ref" "$target_ref" -- "wp-content/themes/eduma-child/$relative" \
		| patch --batch --fuzz=0 -d "$work/expected" -p1
done
printf 'preflight text patches: ok\n'

for relative in "${image_files[@]}"; do
	mkdir -p "$work/current/$(dirname "$relative")" "$work/base/$(dirname "$relative")" "$work/expected/wp-content/themes/eduma-child/$(dirname "$relative")"
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 60 -fsSL \
		-o "$work/current/$relative" "$public_base/wp-content/themes/eduma-child/$relative?toolkit_preflight=$release"
	git -C "$repo" show "$base_ref:wp-content/themes/eduma-child/$relative" > "$work/base/$relative"
	cmp "$work/current/$relative" "$work/base/$relative" || { printf 'remote image drifted: %s\n' "$relative" >&2; exit 7; }
	git -C "$repo" show "$target_ref:wp-content/themes/eduma-child/$relative" > "$work/expected/wp-content/themes/eduma-child/$relative"
done
printf 'preflight image drift checks: ok\n'

theme="$work/expected/wp-content/themes/eduma-child"
php -l "$theme/functions.php" >/dev/null
php -l "$theme/front-page.php" >/dev/null
php -l "$theme/inc/course-catalog.php" >/dev/null
php -l "$theme/inc/hero-slides.php" >/dev/null
php -l "$theme/single.php" >/dev/null
php -l "$theme/template-parts/pages/course-detail.php" >/dev/null
node --check "$theme/hero-slider.js"
require_pattern() {
	local pattern=$1 file=$2 label=$3
	rg -q "$pattern" "$file" || { printf 'payload validation failed: %s\n' "$label" >&2; exit 8; }
}
require_pattern "return '${release}';" "$theme/functions.php" 'release marker'
require_pattern 'picture class="hero-slider__media"' "$theme/front-page.php" 'responsive hero picture'
require_pattern 'function prepareSlide' "$theme/hero-slider.js" 'deferred hero preparation'
require_pattern 'MIG and MAG welding: what learners should know' "$theme/template-parts/pages/course-detail.php" 'welding comparison copy'
if rg -q 'iskill-banner-04\.jpg' "$theme/style.css"; then
	printf 'retired Quick Links background remains in payload\n' >&2
	exit 8
fi
printf 'payload validation: ok\n'

# The complete payload is valid before a persistent rollback directory exists.
for relative in "${text_files[@]}" "${image_files[@]}"; do
	mkdir -p "$rollback/wp-content/themes/eduma-child/$(dirname "$relative")"
	cp "$work/current/$relative" "$rollback/wp-content/themes/eduma-child/$relative"
done

# Upload dependent templates/assets first. functions.php activates the release
# and cache purge only after every dependency is in place.
for relative in "${text_files[@]}"; do
	[ "$relative" = functions.php ] && continue
	mapfile -t location < <(remote_location "$relative")
	upload_file "${location[0]}" "$theme/$relative"
done
for relative in "${image_files[@]}"; do
	mapfile -t location < <(remote_location "$relative")
	upload_file "${location[0]}" "$theme/$relative"
done
upload_file "$remote_theme" "$theme/functions.php"

for relative in "${text_files[@]}"; do
	mapfile -t location < <(remote_location "$relative")
	mkdir -p "$work/verify/$(dirname "$relative")"
	fetch_text "${location[0]}" "${location[1]}" "$work/verify/$relative"
	cmp "$theme/$relative" "$work/verify/$relative"
done

for relative in "${image_files[@]}"; do
	mkdir -p "$work/verify/$(dirname "$relative")"
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 60 -fsSL \
		-o "$work/verify/$relative" "$public_base/wp-content/themes/eduma-child/$relative?toolkit_release_check=$release"
	cmp "$theme/$relative" "$work/verify/$relative"
done

check_page() {
	local path=$1 output=$2
	local code
	code=$(curl -sS --max-time 45 -o "$output" -w '%{http_code}' "$public_base$path")
	[ "$code" = 200 ] || { printf 'route failed: %s%s (%s)\n' "$public_base" "$path" "$code" >&2; exit 9; }
	if rg -qi 'fatal error|critical error|uncaught error' "$output"; then
		printf 'PHP error marker found on %s%s\n' "$public_base" "$path" >&2
		exit 10
	fi
}

check_page "/?toolkit_release_check=$release" "$work/home.html"
check_page "/our-ventures/construction-sector-skills/?toolkit_release_check=$release" "$work/welding.html"
check_page "/careers-in-mig-mag-welding-insights-from-our-webinar/?toolkit_release_check=$release" "$work/story.html"
check_page "/llms-full.txt?toolkit_release_check=$release" "$work/llms-full.txt"

rg -q "hero-slider\.css\\?ver=${release}" "$work/home.html"
rg -q 'hero-slider__media' "$work/home.html"
rg -q 'data-src=.*hero-slide-2\.webp' "$work/home.html"
if rg -q 'sina-widgets\.min\.js|sina-tooltip\.min\.js|owl\.carousel\.min\.js' "$work/home.html"; then
	printf 'legacy Sina carousel scripts remain on homepage\n' >&2
	exit 11
fi
rg -q 'MIG/MAG Welding Training in Kenya' "$work/welding.html"
rg -q 'MIG and MAG welding: what learners should know' "$work/welding.html"
rg -q 'One welding hub, three distinct learner needs' "$work/welding.html"
rg -q 'Explore MIG/MAG welding training' "$work/story.html"
rg -q 'Virtual-reality welding training' "$work/llms-full.txt"

printf 'environment=%s release=%s routes=4x200 files=verified-byte-identical deferred-hero=verified welding=verified rollback=%s\n' \
	"$environment" "$release" "$rollback"
