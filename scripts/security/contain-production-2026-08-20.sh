#!/usr/bin/env bash
set -euo pipefail

script_dir=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
repo=$(git -C "$script_dir" rev-parse --show-toplevel)
endpoint=https://wp46.host-ww.net:2083/execute
remote_root=/home/bfyigiln/public_html
operation=security-containment-2026-08-20
rollback="$repo/rollbacks/$operation"
secrets_file=${TOOLKIT_DEPLOY_SECRETS:-$repo/.toolkit-deploy/secrets.env}

cp_auth=${CPANEL_AUTH:-}
if [ -z "$cp_auth" ] && [ -f "$secrets_file" ]; then
	cp_auth=$(sed -n 's/^CPANEL_AUTH=//p' "$secrets_file" | tail -n 1)
fi
if [ -z "$cp_auth" ]; then
	printf 'set CPANEL_AUTH or create %s\n' "$secrets_file" >&2
	exit 2
fi
work=$(mktemp -d)
trap 'rm -rf -- "$work"' EXIT
mkdir -p "$rollback"
if [ -f "$rollback/COMPLETED" ]; then
	printf 'operation already completed: %s\n' "$rollback" >&2
	exit 3
fi

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
		$j=json_decode(stream_get_contents(STDIN),true);
		if (($j["status"]??0)!==1 || !isset($j["data"]["content"])) exit(4);
		echo $j["data"]["content"];
	' > "$destination"
}

upload_file() {
	remote_dir=$1
	local_file=$2
	remote_file=$3
	response=$(curl -fsS -u "$cp_auth" \
		-F "dir=$remote_dir" \
		-F 'overwrite=1' \
		-F "file-1=@$local_file;filename=$remote_file" \
		"$endpoint/Fileman/upload_files")
	printf '%s' "$response" | php -r '
		$j=json_decode(stream_get_contents(STDIN),true);
		if (($j["status"]??0)!==1 || (int)($j["data"]["succeeded"]??0)!==1 || (int)($j["data"]["failed"]??0)!==0) exit(5);
	'
}

if [ ! -f "$rollback/.htaccess" ]; then
	fetch_file "$remote_root" .htaccess "$rollback/.htaccess"
fi
if rg -q '^# BEGIN Toolkit incident containment 2026-08-20$' "$rollback/.htaccess"; then
	printf 'remote containment marker already exists; refusing to add it twice\n' >&2
	exit 6
fi

{
	printf '%s\n' \
		'# BEGIN Toolkit incident containment 2026-08-20' \
		'<FilesMatch "^(readme\.html|toolkitiskills\.zip)$">' \
		'<IfModule authz_core_module>' \
		'    Require all denied' \
		'</IfModule>' \
		'<IfModule !authz_core_module>' \
		'    Order allow,deny' \
		'    Deny from all' \
		'</IfModule>' \
		'</FilesMatch>' \
		'# END Toolkit incident containment 2026-08-20' \
		''
	sed -n '1,$p' "$rollback/.htaccess"
} > "$work/.htaccess"

test "$(rg -c '^# BEGIN Toolkit incident containment 2026-08-20$' "$work/.htaccess")" -eq 1
test "$(rg -Fxc '<FilesMatch "^(readme\.html|toolkitiskills\.zip)$">' "$work/.htaccess")" -eq 1

printf '%s\n' \
	'# BEGIN Toolkit Eventer quarantine 2026-08-20' \
	'<IfModule authz_core_module>' \
	'    Require all denied' \
	'</IfModule>' \
	'<IfModule !authz_core_module>' \
	'    Order allow,deny' \
	'    Deny from all' \
	'</IfModule>' \
	'# END Toolkit Eventer quarantine 2026-08-20' \
	> "$work/eventer.htaccess"

upload_file "$remote_root" "$work/.htaccess" .htaccess
upload_file "$remote_root/wp-content/plugins/eventer" "$work/eventer.htaccess" .htaccess
fetch_file "$remote_root" .htaccess "$work/.htaccess.remote"
fetch_file "$remote_root/wp-content/plugins/eventer" .htaccess "$work/eventer.htaccess.remote"
cmp "$work/.htaccess" "$work/.htaccess.remote"
cmp "$work/eventer.htaccess" "$work/eventer.htaccess.remote"

check_status() {
	expected=$1
	url=$2
	actual=000
	for attempt in 1 2 3 4 5; do
		actual=$(curl -sS --max-time 20 -o /dev/null -w '%{http_code}' "$url")
		[ "$actual" = "$expected" ] && break
		[ "$attempt" -lt 5 ] && sleep 3
	done
	if [ "$actual" != "$expected" ]; then
		printf 'unexpected HTTP status after retries: expected=%s actual=%s url=%s\n' "$expected" "$actual" "$url" >&2
		exit 7
	fi
	printf '%s\t%s\n' "$actual" "$url"
}

check_status 403 'https://toolkitafrica.ac.ke/Old%20Sites/readme.html'
check_status 403 'https://toolkitafrica.ac.ke/Old%20Sites/toolkitiskills.zip'
check_status 403 'https://toolkitafrica.ac.ke/wp-content/plugins/eventer/eventer.php'
check_status 200 'https://toolkitafrica.ac.ke/'
check_status 200 'https://toolkitafrica.ac.ke/apply/'

touch "$rollback/COMPLETED"
printf 'operation=%s\nrollback=%s\nremote_file_verified=byte-identical\n' \
	"$operation" "$rollback"
