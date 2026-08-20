#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.08.20.2
target_ref=toolkit-release-2026.08.20.2
endpoint=https://wp46.host-ww.net:2083/execute
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
resume=${TOOLKIT_DEPLOY_RESUME:-0}
verify_only=${TOOLKIT_DEPLOY_VERIFY_ONLY:-0}
if [ -e "$rollback" ] && [ "$resume" != 1 ] && [ "$verify_only" != 1 ]; then
	printf 'rollback exists: %s (use the reviewed resume or verify-only mode)\n' "$rollback" >&2
	exit 3
fi

cp_auth=${CPANEL_AUTH:-}
if [ -z "$cp_auth" ] && [ -f "$secrets_file" ]; then
	cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
fi
[ -n "$cp_auth" ] || { printf 'set CPANEL_AUTH or create %s\n' "$secrets_file" >&2; exit 4; }

work=$(mktemp -d)
trap 'rm -rf -- "$work"' EXIT
mkdir -p "$work/wp-content/themes/eduma-child/inc" "$work/current/inc" "$work/verify/inc"
mkdir -p "$rollback/wp-content/themes/eduma-child/inc"

fetch_file() {
	remote_dir=$1
	remote_file=$2
	destination=$3
	response=$(curl -fsS -G -u "$cp_auth" \
		--data-urlencode "dir=$remote_dir" \
		--data-urlencode "file=$remote_file" \
		--data-urlencode 'update_html_document_encoding=0' \
		--data-urlencode 'to_charset=utf-8' \
		"$endpoint/Fileman/get_file_content")
	printf '%s' "$response" | php -r '
		$j=json_decode(stream_get_contents(STDIN), true);
		if (($j["status"] ?? 0) !== 1 || !isset($j["data"]["content"])) exit(5);
		echo $j["data"]["content"];
	' > "$destination"
}

upload_file() {
	remote_dir=$1
	local_file=$2
	response=$(curl -fsS -u "$cp_auth" \
		-F "dir=$remote_dir" \
		-F 'overwrite=1' \
		-F "file-1=@$local_file" \
		"$endpoint/Fileman/upload_files")
	printf '%s' "$response" | php -r '
		$j=json_decode(stream_get_contents(STDIN), true);
		if (($j["status"] ?? 0) !== 1 || (int)($j["data"]["succeeded"] ?? 0) !== 1 || (int)($j["data"]["failed"] ?? 0) !== 0) exit(6);
	'
}

files=(
	inc/cultural-week-stories.php
	inc/mosiria-story.php
	inc/supplied-stories.php
	inc/calling-letters.php
	functions.php
)

for relative in "${files[@]}"; do
	remote_dir=$remote_theme
	remote_file=$relative
	if [[ "$relative" == inc/* ]]; then
		remote_dir=$remote_theme/inc
		remote_file=${relative#inc/}
	fi
	if [ "$resume" = 1 ] || [ "$verify_only" = 1 ]; then
		[ -f "$rollback/wp-content/themes/eduma-child/$relative" ] || { printf 'missing rollback file for resume: %s\n' "$relative" >&2; exit 8; }
		fetch_file "$remote_dir" "$remote_file" "$work/current/$relative"
		if [ "$resume" = 1 ]; then
			cmp "$rollback/wp-content/themes/eduma-child/$relative" "$work/current/$relative" || { printf 'remote changed since rollback; refusing resume: %s\n' "$relative" >&2; exit 9; }
		fi
	else
		fetch_file "$remote_dir" "$remote_file" "$rollback/wp-content/themes/eduma-child/$relative"
	fi
	cp "$rollback/wp-content/themes/eduma-child/$relative" "$work/wp-content/themes/eduma-child/$relative"
	case "$relative" in
		inc/cultural-week-stories.php|inc/mosiria-story.php|inc/supplied-stories.php) base_ref=b41c546 ;;
		*) base_ref=0d99d88 ;;
	esac
	git -C "$repo" diff --binary "$base_ref" "$target_ref" -- "wp-content/themes/eduma-child/$relative" \
		| patch --batch --fuzz=0 -d "$work" -p1
done

for relative in "${files[@]}"; do
	php -l "$work/wp-content/themes/eduma-child/$relative" >/dev/null
done
rg -q "return '${release}';" "$work/wp-content/themes/eduma-child/functions.php"
rg -q '\$version = '\''1\.2\.0'\'';' "$work/wp-content/themes/eduma-child/inc/calling-letters.php"
rg -q 'return \$accepted \? '\''submitted'\'' : '\''failed'\'';' "$work/wp-content/themes/eduma-child/inc/calling-letters.php"
rg -q "toolkit_calling_letter_email_enabled', '0'" "$work/wp-content/themes/eduma-child/inc/calling-letters.php"
rg -q "_yoast_wpseo_focuskw" "$work/wp-content/themes/eduma-child/inc/cultural-week-stories.php"
rg -q "_yoast_wpseo_focuskw" "$work/wp-content/themes/eduma-child/inc/mosiria-story.php"
rg -q "_yoast_wpseo_focuskw" "$work/wp-content/themes/eduma-child/inc/supplied-stories.php"

if [ "$verify_only" != 1 ]; then
	for relative in "${files[@]}"; do
		remote_dir=$remote_theme
		if [[ "$relative" == inc/* ]]; then remote_dir=$remote_theme/inc; fi
		upload_file "$remote_dir" "$work/wp-content/themes/eduma-child/$relative"
	done
fi

for relative in "${files[@]}"; do
	remote_dir=$remote_theme
	remote_file=$relative
	verify_file=$work/verify/$relative
	if [[ "$relative" == inc/* ]]; then
		remote_dir=$remote_theme/inc
		remote_file=${relative#inc/}
	fi
	fetch_file "$remote_dir" "$remote_file" "$verify_file"
	cmp "$work/wp-content/themes/eduma-child/$relative" "$verify_file"
done

routes=(
	/apply/
	/cultural-week-official-wear-day/
	/cultural-week-golden-oldies-day/
	/cultural-week-african-wear-day/
	/cultural-week-career-wear-day/
	/geofrey-mosiria-visits-the-toolkit/
	/africa-forward-youth-innovation-day-career-fair-2026/
	/alumni-mentorship-success-stories-2026/
)
for route in "${routes[@]}"; do
	code=$(curl -sS --max-time 30 -o "$work/route.html" -w '%{http_code}' "${public_base}${route}?toolkit_release_check=${release}")
	[ "$code" = 200 ] || { printf 'route failed: %s%s (%s)\n' "$public_base" "$route" "$code" >&2; exit 7; }
done
rg -q "ver=${release}" "$work/route.html"

printf 'environment=%s release=%s rollback=%s routes=%s files=verified-byte-identical\n' \
	"$environment" "$release" "$rollback" "${#routes[@]}"
