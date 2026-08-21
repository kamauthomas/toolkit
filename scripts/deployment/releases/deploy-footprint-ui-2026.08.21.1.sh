#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.08.21.1
base_ref=d8b61c7
target_ref=${TOOLKIT_DEPLOY_TARGET_REF:-toolkit-release-2026.08.21.1}
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
mkdir -p "$work/wp-content/themes/eduma-child/template-parts/pages" "$work/current/template-parts/pages" "$work/verify/template-parts/pages"
mkdir -p "$rollback/wp-content/themes/eduma-child/template-parts/pages"

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
	page-redesign.css
	template-parts/pages/footprint.php
	functions.php
)

remote_location() {
	case "$1" in
		template-parts/pages/*) printf '%s\n%s\n' "$remote_theme/template-parts/pages" "${1#template-parts/pages/}" ;;
		*) printf '%s\n%s\n' "$remote_theme" "$1" ;;
	esac
}

for relative in "${files[@]}"; do
	mapfile -t location < <(remote_location "$relative")
	remote_dir=${location[0]}
	remote_file=${location[1]}
	if [ "$resume" = 1 ] || [ "$verify_only" = 1 ]; then
		[ -f "$rollback/wp-content/themes/eduma-child/$relative" ] || { printf 'missing rollback file: %s\n' "$relative" >&2; exit 8; }
		fetch_file "$remote_dir" "$remote_file" "$work/current/$relative"
		if [ "$resume" = 1 ]; then
			cmp "$rollback/wp-content/themes/eduma-child/$relative" "$work/current/$relative" || { printf 'remote changed since rollback; refusing resume: %s\n' "$relative" >&2; exit 9; }
		fi
	else
		fetch_file "$remote_dir" "$remote_file" "$rollback/wp-content/themes/eduma-child/$relative"
	fi
	cp "$rollback/wp-content/themes/eduma-child/$relative" "$work/wp-content/themes/eduma-child/$relative"
	git -C "$repo" diff --binary "$base_ref" "$target_ref" -- "wp-content/themes/eduma-child/$relative" \
		| patch --batch --fuzz=0 -d "$work" -p1
done

php -l "$work/wp-content/themes/eduma-child/functions.php" >/dev/null
php -l "$work/wp-content/themes/eduma-child/template-parts/pages/footprint.php" >/dev/null
rg -q "return '${release}';" "$work/wp-content/themes/eduma-child/functions.php"
rg -q 'toolkit-footprint-hero__inner' "$work/wp-content/themes/eduma-child/page-redesign.css"
rg -q 'The full programme record, made easier to follow.' "$work/wp-content/themes/eduma-child/template-parts/pages/footprint.php"
if rg -q 'toolkit-footprint-trail' "$work/wp-content/themes/eduma-child/page-redesign.css" "$work/wp-content/themes/eduma-child/template-parts/pages/footprint.php"; then
	printf 'obsolete footprint trail remains in payload\n' >&2
	exit 10
fi

if [ "$verify_only" != 1 ]; then
	# Activate the cache/release transition last, after the template and CSS exist.
	for relative in "${files[@]}"; do
		mapfile -t location < <(remote_location "$relative")
		upload_file "${location[0]}" "$work/wp-content/themes/eduma-child/$relative"
	done
fi

for relative in "${files[@]}"; do
	mapfile -t location < <(remote_location "$relative")
	fetch_file "${location[0]}" "${location[1]}" "$work/verify/$relative"
	cmp "$work/wp-content/themes/eduma-child/$relative" "$work/verify/$relative"
done

check_url="${public_base}/footprint/?toolkit_release_check=${release}"
code=$(curl -sS --max-time 30 -o "$work/footprint.html" -w '%{http_code}' "$check_url")
[ "$code" = 200 ] || { printf 'footprint route failed: %s (%s)\n' "$check_url" "$code" >&2; exit 7; }
rg -q 'Skills grow when people build together.' "$work/footprint.html"
rg -q 'The full programme record, made easier to follow.' "$work/footprint.html"
rg -q "ver=${release}" "$work/footprint.html"
if rg -qi 'fatal error|critical error|uncaught error' "$work/footprint.html"; then
	printf 'PHP error marker found on %s\n' "$check_url" >&2
	exit 11
fi

for asset in \
	/wp-content/themes/eduma-child/assets/images/pages/impact.jpg \
	/wp-content/themes/eduma-child/assets/images/courses/electrical.jpg \
	/wp-content/themes/eduma-child/assets/images/graduation/kmm-2071-jpg.webp \
	/wp-content/themes/eduma-child/assets/images/courses/experiences/solar-workshop.jpg; do
	asset_code=$(curl -sS --max-time 30 -o /dev/null -w '%{http_code}' "${public_base}${asset}")
	[ "$asset_code" = 200 ] || { printf 'footprint asset failed: %s%s (%s)\n' "$public_base" "$asset" "$asset_code" >&2; exit 12; }
done

printf 'environment=%s release=%s rollback=%s route=200 assets=4 files=verified-byte-identical\n' \
	"$environment" "$release" "$rollback"
