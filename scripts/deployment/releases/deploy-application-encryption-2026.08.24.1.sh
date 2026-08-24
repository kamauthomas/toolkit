#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.08.24.1
base_ref=toolkit-release-2026.08.21.2
target_ref=${TOOLKIT_DEPLOY_TARGET_REF:-HEAD}
endpoint=https://wp46.host-ww.net:2083/execute
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$repo/.toolkit-deploy/secrets.env}

case "$environment" in
	demo)
		remote_theme=/home/bfyigiln/demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child
		public_url=https://demo.toolkitafrica.ac.ke/apply/
		;;
	production)
		remote_theme=/home/bfyigiln/public_html/wp-content/themes/eduma-child
		public_url=https://toolkitafrica.ac.ke/apply/
		;;
	*)
		printf 'usage: %s demo|production\n' "$0" >&2
		exit 2
		;;
esac

rollback="$repo/rollbacks/${environment}-pre-${release}"
resume=${TOOLKIT_DEPLOY_RESUME:-0}
[ -e "$rollback" ] && [ "$resume" != 1 ] && { printf 'rollback exists: %s (use TOOLKIT_DEPLOY_RESUME=1 only after checking it)\n' "$rollback" >&2; exit 3; }

cp_auth=${CPANEL_AUTH:-}
if [ -z "$cp_auth" ] && [ -f "$secrets_file" ]; then
	cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
fi
[ -n "$cp_auth" ] || { printf 'set CPANEL_AUTH or create %s\n' "$secrets_file" >&2; exit 4; }

work=$(mktemp -d)
trap 'rm -rf -- "$work"' EXIT
mkdir -p "$work/wp-content/themes/eduma-child/inc" "$work/verify/inc" "$rollback/wp-content/themes/eduma-child/inc"

fetch() {
	dir=$1; file=$2; out=$3
	response=$(curl -fsS -G -u "$cp_auth" \
		--data-urlencode "dir=$dir" \
		--data-urlencode "file=$file" \
		--data-urlencode 'update_html_document_encoding=0' \
		--data-urlencode 'to_charset=utf-8' \
		"$endpoint/Fileman/get_file_content")
	printf '%s' "$response" | php -r '
		$j=json_decode(stream_get_contents(STDIN),true);
		if (($j["status"]??0)!==1||!isset($j["data"]["content"])) exit(5);
		echo $j["data"]["content"];
	' > "$out"
}

upload() {
	dir=$1; file=$2
	response=$(curl -fsS -u "$cp_auth" -F "dir=$dir" -F 'overwrite=1' -F "file-1=@$file" "$endpoint/Fileman/upload_files")
	printf '%s' "$response" | php -r '
		$j=json_decode(stream_get_contents(STDIN),true);
		if (($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0) exit(6);
	'
}

for relative in functions.php inc/application-adapter.php; do
	remote_dir=$remote_theme
	remote_file=$relative
	if [[ "$relative" == inc/* ]]; then
		remote_dir="$remote_theme/inc"
		remote_file=${relative#inc/}
	fi
	if [ "$resume" = 1 ]; then
		[ -f "$rollback/wp-content/themes/eduma-child/$relative" ] || { printf 'missing rollback file: %s\n' "$relative" >&2; exit 8; }
		fetch "$remote_dir" "$remote_file" "$work/current-${relative##*/}"
		cmp "$rollback/wp-content/themes/eduma-child/$relative" "$work/current-${relative##*/}" || { printf 'remote changed since rollback: %s\n' "$relative" >&2; exit 9; }
	else
		mkdir -p "$(dirname "$rollback/wp-content/themes/eduma-child/$relative")"
		fetch "$remote_dir" "$remote_file" "$rollback/wp-content/themes/eduma-child/$relative"
	fi
	cp "$rollback/wp-content/themes/eduma-child/$relative" "$work/wp-content/themes/eduma-child/$relative"
	git -C "$repo" diff --binary "$base_ref" "$target_ref" -- "wp-content/themes/eduma-child/$relative" | patch --batch --fuzz=0 -d "$work" -p1
done

php -l "$work/wp-content/themes/eduma-child/functions.php" >/dev/null
php -l "$work/wp-content/themes/eduma-child/inc/application-adapter.php" >/dev/null
rg -q "return '${release}';" "$work/wp-content/themes/eduma-child/functions.php"
rg -q "TOOLKIT_APPLICATION_ENCRYPTION_KEYS|applications-migrate-encryption" "$work/wp-content/themes/eduma-child/inc/application-adapter.php"

upload "$remote_theme/inc" "$work/wp-content/themes/eduma-child/inc/application-adapter.php"
upload "$remote_theme" "$work/wp-content/themes/eduma-child/functions.php"
fetch "$remote_theme/inc" application-adapter.php "$work/verify/application-adapter.php"
fetch "$remote_theme" functions.php "$work/verify/functions.php"
cmp "$work/wp-content/themes/eduma-child/inc/application-adapter.php" "$work/verify/application-adapter.php"
cmp "$work/wp-content/themes/eduma-child/functions.php" "$work/verify/functions.php"

code=$(curl -sS --max-time 30 -o "$work/apply.html" -w '%{http_code}' "$public_url?toolkit_release_check=$release")
[ "$code" = 200 ] || { printf 'apply route failed: %s (%s)\n' "$public_url" "$code" >&2; exit 7; }
rg -q "ver=${release}" "$work/apply.html"
if rg -qi 'fatal error|critical error|uncaught error' "$work/apply.html"; then
	printf 'PHP error marker found on %s\n' "$public_url" >&2
	exit 11
fi

printf 'environment=%s release=%s rollback=%s route=200 files=verified-byte-identical encryption-code=deployed keyring=migration-pending\n' \
	"$environment" "$release" "$rollback"
