#!/usr/bin/env bash
set -euo pipefail

environment=${1:-}
repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../../.." && pwd)
release=2026.08.24.2
endpoint=https://wp46.host-ww.net:2083/execute
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$repo/.toolkit-deploy/secrets.env}

case "$environment" in
	demo) remote_theme=/home/bfyigiln/demo.toolkitafrica.ac.ke/wp-content/themes/eduma-child; public_url=https://demo.toolkitafrica.ac.ke/ ;;
	production) remote_theme=/home/bfyigiln/public_html/wp-content/themes/eduma-child; public_url=https://toolkitafrica.ac.ke/ ;;
	*) printf 'usage: %s demo|production\n' "$0" >&2; exit 2 ;;
esac

rollback="$repo/rollbacks/${environment}-pre-${release}"
resume=${TOOLKIT_DEPLOY_RESUME:-0}
[ -e "$rollback" ] && [ "$resume" != 1 ] && { printf 'rollback exists: %s (use TOOLKIT_DEPLOY_RESUME=1 after checking it)\n' "$rollback" >&2; exit 3; }
cp_auth=${CPANEL_AUTH:-}
[ -n "$cp_auth" ] || [ ! -f "$secrets_file" ] || cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
[ -n "$cp_auth" ] || { printf 'set CPANEL_AUTH or create %s\n' "$secrets_file" >&2; exit 4; }

work=$(mktemp -d); trap 'rm -rf -- "$work"' EXIT
fetch() { curl -fsS -G -u "$cp_auth" --data-urlencode "dir=$1" --data-urlencode "file=$2" --data-urlencode 'update_html_document_encoding=0' --data-urlencode 'to_charset=utf-8' "$endpoint/Fileman/get_file_content" | php -r '$j=json_decode(stream_get_contents(STDIN),true); if (($j["status"]??0)!==1||!isset($j["data"]["content"])) exit(5); echo $j["data"]["content"];' > "$3"; }
upload() { curl -fsS -u "$cp_auth" -F "dir=$1" -F 'overwrite=1' -F "file-1=@$2" "$endpoint/Fileman/upload_files" | php -r '$j=json_decode(stream_get_contents(STDIN),true); if (($j["status"]??0)!==1||(int)($j["data"]["succeeded"]??0)!==1||(int)($j["data"]["failed"]??0)!==0) exit(6);'; }

mkdir -p "$rollback/wp-content/themes/eduma-child" "$work/wp-content/themes/eduma-child"
for relative in functions.php footer.php style.css; do
	if [ "$resume" = 1 ]; then
		fetch "$remote_theme" "$relative" "$work/current-$relative"
		cmp "$rollback/wp-content/themes/eduma-child/$relative" "$work/current-$relative"
	else
		fetch "$remote_theme" "$relative" "$rollback/wp-content/themes/eduma-child/$relative"
	fi
	cp "$rollback/wp-content/themes/eduma-child/$relative" "$work/wp-content/themes/eduma-child/$relative"
	git -C "$repo" diff --binary HEAD^ HEAD -- "wp-content/themes/eduma-child/$relative" | patch --batch --fuzz=0 -d "$work" -p1
done

theme="$work/wp-content/themes/eduma-child"
php -l "$theme/functions.php" >/dev/null
php -l "$theme/footer.php" >/dev/null
rg -q "return '${release}';" "$theme/functions.php"
rg -q 'toolkit-whatsapp-float' "$theme/footer.php" "$theme/style.css"
for relative in functions.php footer.php style.css; do upload "$remote_theme" "$theme/$relative"; done
for relative in functions.php footer.php style.css; do fetch "$remote_theme" "$relative" "$work/verify-$relative"; cmp "$theme/$relative" "$work/verify-$relative"; done
code=$(curl -sS --max-time 30 -o "$work/home.html" -w '%{http_code}' "$public_url?toolkit_release_check=$release")
[ "$code" = 200 ] || { printf 'route failed: %s (%s)\n' "$public_url" "$code" >&2; exit 7; }
rg -q "ver=${release}" "$work/home.html"
rg -q 'toolkit-whatsapp-float|wa.me/254711802855' "$work/home.html"
printf 'environment=%s release=%s route=200 files=verified-byte-identical whatsapp=float-restored rollback=%s\n' "$environment" "$release" "$rollback"
