#!/usr/bin/env bash
set -euo pipefail

ROOT=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
VERSION=${1:-$(date -u +%Y%m%d-%H%M%S)}
RELEASE_DIR="$ROOT/releases/toolkit-main-$VERSION"
STAGE=$(mktemp -d)
trap 'rm -rf "$STAGE"' EXIT

mkdir -p "$RELEASE_DIR"
git -C "$ROOT" archive HEAD wp-content/themes/eduma-child | tar -x -C "$STAGE"

THEME="$STAGE/wp-content/themes/eduma-child"
if find "$STAGE" \( -name '.agents' -o -name '.codex' -o -name 'AGENT_TRACE*' -o -name 'AGENTS.md' -o -name '.~lock.*' -o -name '.DS_Store' -o -name 'Thumbs.db' \) -print -quit | grep -q .; then
	echo 'Release aborted: local agent, editor, or operating-system metadata entered the archive.' >&2
	exit 1
fi
find "$THEME" -type f -print0 | sort -z | xargs -0 sha256sum > "$RELEASE_DIR/SHA256SUMS"
sed -i "s#$STAGE/##" "$RELEASE_DIR/SHA256SUMS"

cat > "$RELEASE_DIR/ROLLOUT-CONSTANTS.php.txt" <<'EOF'
// Start with every release switch disabled.
define( 'TOOLKIT_REDESIGN_ENABLED', false );
define( 'TOOLKIT_2026_CATALOG_ENABLED', false );
define( 'TOOLKIT_2026_PRICING_ENABLED', false );
EOF

tar -C "$STAGE/wp-content/themes" -czf "$RELEASE_DIR/eduma-child.tar.gz" eduma-child
sha256sum "$RELEASE_DIR/eduma-child.tar.gz" > "$RELEASE_DIR/ARCHIVE-SHA256SUM"
git -C "$ROOT" rev-parse HEAD > "$RELEASE_DIR/GIT-COMMIT"

printf 'Release: %s\nFiles: %s\nArchive: %s\n' \
  "$RELEASE_DIR" \
  "$(find "$THEME" -type f | wc -l)" \
  "$RELEASE_DIR/eduma-child.tar.gz"
