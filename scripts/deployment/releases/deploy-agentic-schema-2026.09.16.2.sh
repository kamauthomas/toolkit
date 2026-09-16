#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.09.16.2
base_ref=29b4912
target_ref=158a949
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

fetch_functions() {
	local destination=$1
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 60 -fsS \
		-b "$CPANEL_SESSION_COOKIE" -G \
		--data-urlencode "dir=$remote_theme" \
		--data-urlencode 'file=functions.php' \
		--data-urlencode 'update_html_document_encoding=0' \
		--data-urlencode 'to_charset=utf-8' \
		"$CPANEL_SESSION_API/Fileman/get_file_content" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||!isset($j["data"]["content"]))exit(5);echo $j["data"]["content"];' > "$destination"
}

upload_functions() {
	local local_file=$1
	curl --retry 3 --retry-all-errors --retry-delay 2 --connect-timeout 15 --max-time 90 -fsS \
		-b "$CPANEL_SESSION_COOKIE" -F "dir=$remote_theme" -F 'overwrite=1' -F "file-1=@$local_file" \
		"$CPANEL_SESSION_API/Fileman/upload_files" \
		| php -r '$j=json_decode(stream_get_contents(STDIN),true);if(($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0)exit(6);'
}

printf 'fetching live functions.php\n'
fetch_functions "$work/functions.php"
printf 'applying pinned patch\n'
git -C "$repo" diff --binary "$base_ref" "$target_ref" -- wp-content/themes/eduma-child/functions.php \
	| patch --batch --fuzz=0 -d "$work" -p4

php -l "$work/functions.php" >/dev/null
rg -q "return '${release}';" "$work/functions.php"
rg -q "schema_path = '/wp-json/yoast/v1/schema-aggregator/'" "$work/functions.php"
rg -q "allow_line .*'Allow: '" "$work/functions.php"
rg -q "schema_path .*'get-xml'" "$work/functions.php"

curl -fsSL -o "$work/robots-before.txt" "$public_base/robots.txt?before=$release"
printf 'payload and crawler baseline: ok\n'
mkdir -p "$rollback/wp-content/themes/eduma-child"
fetch_functions "$rollback/wp-content/themes/eduma-child/functions.php"

printf 'uploading functions.php\n'
upload_functions "$work/functions.php"
printf 'verifying deployed file and public crawler policy\n'
fetch_functions "$work/functions-verify.php"
cmp "$work/functions.php" "$work/functions-verify.php"

curl -fsSL -o "$work/robots-after.txt" "$public_base/robots.txt?after=$release"
if [ "$environment" = demo ]; then
	cmp "$work/robots-before.txt" "$work/robots-after.txt"
else
	rg -q '^Allow: /wp-json/yoast/v1/schema-aggregator/$' "$work/robots-after.txt"
	rg -q '^Disallow: /wp-json/$' "$work/robots-after.txt"
	rg -q '^Sitemap: https://toolkitafrica\.ac\.ke/wp-json/yoast/v1/schema-aggregator/get-xml$' "$work/robots-after.txt"
	curl -fsSL -o "$work/schemamap.xml" "$public_base/wp-json/yoast/v1/schema-aggregator/get-xml"
	rg -q 'schema-aggregator/get-schema/(page|post)' "$work/schemamap.xml"
fi

release_header=$(curl -fsSI "$public_base/?release_check=$release" | tr -d '\r' | sed -n 's/^x-toolkit-release: //p' | tail -n 1)
[ "$release_header" = "$release" ] || { printf 'unexpected release header: %s\n' "$release_header" >&2; exit 7; }

printf 'environment=%s release=%s functions=verified-byte-identical robots=verified rollback=%s\n' \
	"$environment" "$release" "$rollback"
