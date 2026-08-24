#!/usr/bin/env bash
set -euo pipefail

repo=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/../.." && pwd)
destination=${1:-/mnt/windows/Users/User/Desktop/Toolkit}
source_documents=${TOOLKIT_DOCUMENTS_SOURCE:-/home/t316/Documents}
source_images=${TOOLKIT_IMAGES_SOURCE:-/home/t316/Downloads/Images}

if ! findmnt -T "$destination" -t ntfs,ntfs3,exfat,vfat,fuseblk >/dev/null 2>&1; then
	printf 'destination is not on a mounted Windows volume: %s\n' "$destination" >&2
	exit 2
fi

mkdir -p "$destination/Website/eduma-child" "$destination/Creative/XCF" "$destination/Creative/Posters" "$destination/Project-Docs"

# Keep editable source folders; explicitly exclude credentials, deploy secrets, backups and repository metadata.
rsync -a --delete \
	--exclude='.git/' --exclude='.toolkit-deploy/' --exclude='rollbacks/' --exclude='wp-config.php' \
	--exclude='*.sql' --exclude='*.sql.gz' --exclude='*.dump' --exclude='*.zip' --exclude='*.tar*' \
	--exclude='*.log' --exclude='secrets.env' \
	"$repo/wp-content/themes/eduma-child/" "$destination/Website/eduma-child/"
rsync -a --delete \
	--exclude='*.env' --exclude='secrets.env' --exclude='wp-config.php' --exclude='*.key' --exclude='*.pem' \
	"$repo/docs/" "$destination/Project-Docs/docs/"
for file in WORKLOG.md README.md README.txt; do
	[ -f "$repo/$file" ] && rsync -a "$repo/$file" "$destination/Project-Docs/"
done

# Creative source files remain editable and are kept separate from the website source.
if [ -d "$source_documents" ]; then
	find "$source_documents" -maxdepth 1 -type f -iname '*.xcf' -exec rsync -a {} "$destination/Creative/XCF/" \;
	find "$source_documents" -maxdepth 1 -type f \( -iname '*.png' -o -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.pdf' \) -exec rsync -a {} "$destination/Creative/Posters/" \;
fi
if [ -d "$source_images" ]; then
	find "$source_images" -maxdepth 1 -type f \( -iname '*.png' -o -iname '*.jpg' -o -iname '*.jpeg' -o -iname '*.pdf' \) -exec rsync -a {} "$destination/Creative/Posters/" \;
fi

printf 'Toolkit sync complete: %s\n' "$destination"
printf 'Files are editable folders; credentials, secrets, backups and database dumps were excluded.\n'
