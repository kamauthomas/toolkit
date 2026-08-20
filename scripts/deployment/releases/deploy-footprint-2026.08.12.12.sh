#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
release=2026.08.12.12
base_ref=e9c1fe3
target_ref=toolkit-release-2026.08.12.12
endpoint=https://wp46.host-ww.net:2083/execute
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$repo/.toolkit-deploy/secrets.env}

case "$environment" in
  demo)
    remote_theme=/home/bfyigiln/demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child
    public_url=https://demo.toolkitafrica.ac.ke/footprint/
    ;;
  production)
    remote_theme=/home/bfyigiln/public_html/wp-content/themes/eduma-child
    public_url=https://toolkitafrica.ac.ke/footprint/
    ;;
  *)
    printf 'usage: %s demo|production\n' "$0" >&2
    exit 2
    ;;
esac

rollback="$repo/rollbacks/${environment}-pre-${release}"
if [ -e "$rollback" ]; then
  printf 'rollback target already exists: %s\n' "$rollback" >&2
  exit 3
fi

cp_auth=${CPANEL_AUTH:-}
if [ -z "$cp_auth" ] && [ -f "$secrets_file" ]; then
  cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
fi
if [ -z "$cp_auth" ]; then
  printf 'set CPANEL_AUTH or create %s\n' "$secrets_file" >&2
  exit 4
fi

work=$(mktemp -d)
mkdir -p "$work/wp-content/themes/eduma-child/template-parts/pages"
mkdir -p "$rollback/wp-content/themes/eduma-child"

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

for file in functions.php page-redesign.css; do
  fetch_file "$remote_theme" "$file" "$rollback/wp-content/themes/eduma-child/$file"
  cp "$rollback/wp-content/themes/eduma-child/$file" "$work/wp-content/themes/eduma-child/$file"
done

template_response=$(curl -fsS -G -u "$cp_auth" \
  --data-urlencode "dir=$remote_theme/template-parts/pages" \
  --data-urlencode 'file=footprint.php' \
  --data-urlencode 'update_html_document_encoding=0' \
  --data-urlencode 'to_charset=utf-8' \
  "$endpoint/Fileman/get_file_content")
if printf '%s' "$template_response" | php -r '$j=json_decode(stream_get_contents(STDIN),true); exit((($j["status"]??0)===1 && isset($j["data"]["content"])) ? 0 : 1);'; then
  printf 'remote footprint.php already exists; refusing to overwrite it\n' >&2
  exit 7
fi
printf '%s\n' 'wp-content/themes/eduma-child/template-parts/pages/footprint.php' > "$rollback/NEW_FILES.txt"

for relative in \
  wp-content/themes/eduma-child/functions.php \
  wp-content/themes/eduma-child/page-redesign.css
do
  git -C "$repo" diff --binary "$base_ref" "$target_ref" -- "$relative" | patch --batch --fuzz=0 -d "$work" -p1
done
git -C "$repo" show "$target_ref:wp-content/themes/eduma-child/template-parts/pages/footprint.php" \
  > "$work/wp-content/themes/eduma-child/template-parts/pages/footprint.php"

php -l "$work/wp-content/themes/eduma-child/functions.php" >/dev/null
php -l "$work/wp-content/themes/eduma-child/template-parts/pages/footprint.php" >/dev/null
test "$(rg -c 'toolkit_showcase=footprint' "$work/wp-content/themes/eduma-child/functions.php")" -eq 1
test "$(rg -c 'toolkit-footprint-trail' "$work/wp-content/themes/eduma-child/page-redesign.css")" -ge 1
rg -q "return '${release}';" "$work/wp-content/themes/eduma-child/functions.php"

upload_file "$remote_theme" "$work/wp-content/themes/eduma-child/page-redesign.css"
upload_file "$remote_theme/template-parts/pages" "$work/wp-content/themes/eduma-child/template-parts/pages/footprint.php"
upload_file "$remote_theme" "$work/wp-content/themes/eduma-child/functions.php"

mkdir -p "$work/verify/template-parts/pages"
fetch_file "$remote_theme" page-redesign.css "$work/verify/page-redesign.css"
fetch_file "$remote_theme/template-parts/pages" footprint.php "$work/verify/template-parts/pages/footprint.php"
fetch_file "$remote_theme" functions.php "$work/verify/functions.php"
cmp "$work/wp-content/themes/eduma-child/page-redesign.css" "$work/verify/page-redesign.css"
cmp "$work/wp-content/themes/eduma-child/template-parts/pages/footprint.php" "$work/verify/template-parts/pages/footprint.php"
cmp "$work/wp-content/themes/eduma-child/functions.php" "$work/verify/functions.php"

printf 'environment=%s\nrelease=%s\nrollback=%s\nurl=%s\nremote_files=verified-byte-identical\n' \
  "$environment" "$release" "$rollback" "$public_url"
