#!/usr/bin/env bash
# version-bump.sh
# Bump the plugin version, update all version references, create a git tag,
# and add a CHANGELOG.md entry.
#
# Usage:
#   ./version-bump.sh <plugin> <new-version>
#
# Examples:
#   ./version-bump.sh mc-woo-remote-automations 1.2.0
#   ./version-bump.sh mc-remote-api 1.2.0

set -euo pipefail

PLUGIN="${1:-}"
NEW_VERSION="${2:-}"

# ─── Validation ───────────────────────────────────────────────────────────────
if [[ -z "$PLUGIN" || -z "$NEW_VERSION" ]]; then
    echo "Usage: $0 <plugin> <new-version>"
    echo "  plugin       : mc-woo-remote-automations | mc-remote-api"
    echo "  new-version  : e.g. 1.2.0"
    exit 1
fi

if [[ ! "$PLUGIN" =~ ^(mc-woo-remote-automations|mc-remote-api)$ ]]; then
    echo "Error: plugin must be 'mc-woo-remote-automations' or 'mc-remote-api'."
    exit 1
fi

if [[ ! "$NEW_VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Error: new-version must follow semantic versioning (e.g. 1.2.0)."
    exit 1
fi

PLUGIN_DIR="./${PLUGIN}"
MAIN_PHP="${PLUGIN_DIR}/${PLUGIN}.php"
README_TXT="${PLUGIN_DIR}/readme.txt"

if [[ ! -f "$MAIN_PHP" ]]; then
    echo "Error: Main plugin file not found at ${MAIN_PHP}"
    exit 1
fi

# ─── Detect current version ───────────────────────────────────────────────────
CURRENT_VERSION=$(grep -m1 'Version:' "$MAIN_PHP" | awk '{print $NF}')
echo "Plugin       : $PLUGIN"
echo "Current ver  : $CURRENT_VERSION"
echo "New ver      : $NEW_VERSION"
echo ""

# ─── Update main plugin file header ──────────────────────────────────────────
sed -i "s/ \* Version:.*/ * Version:           ${NEW_VERSION}/" "$MAIN_PHP"

# ─── Update version constant ─────────────────────────────────────────────────
# Handles both MC_WOO_REMOTE_VERSION and MC_REMOTE_API_VERSION
CONST_NAME=$(grep -o "[A-Z_]*_VERSION" "$MAIN_PHP" | head -1)
sed -i "s/define( '${CONST_NAME}', '[0-9]*\.[0-9]*\.[0-9]*' );/define( '${CONST_NAME}', '${NEW_VERSION}' );/" "$MAIN_PHP"

# ─── Update readme.txt Stable tag ─────────────────────────────────────────────
if [[ -f "$README_TXT" ]]; then
    sed -i "s/^Stable tag:.*/Stable tag: ${NEW_VERSION}/" "$README_TXT"
    echo "Updated stable tag in readme.txt"
fi

# ─── Add CHANGELOG.md entry ───────────────────────────────────────────────────
TODAY=$(date +%Y-%m-%d)
CHANGELOG_ENTRY="## [${NEW_VERSION}] - ${TODAY}

### Added
-

### Changed
-

### Fixed
-

### Security
-

"

# Insert after the first line (# Changelog header)
if [[ -f "CHANGELOG.md" ]]; then
    TMPFILE=$(mktemp)
    head -3 CHANGELOG.md > "$TMPFILE"
    echo "$CHANGELOG_ENTRY" >> "$TMPFILE"
    tail -n +4 CHANGELOG.md >> "$TMPFILE"
    mv "$TMPFILE" CHANGELOG.md
    echo "Added CHANGELOG.md entry for ${NEW_VERSION}"
fi

# ─── Git commit and tag ────────────────────────────────────────────────────────
echo ""
echo "Staging changes..."
git add "$MAIN_PHP" "$README_TXT" CHANGELOG.md 2>/dev/null || true

read -rp "Commit and tag v${NEW_VERSION}? [y/N] " CONFIRM
if [[ "$CONFIRM" =~ ^[Yy]$ ]]; then
    git commit -m "chore: bump ${PLUGIN} version to ${NEW_VERSION}"
    git tag -a "v${NEW_VERSION}" -m "Release ${NEW_VERSION}"
    echo ""
    echo "✅ Committed and tagged v${NEW_VERSION}."
    echo "Push with: git push origin main --tags"
else
    echo "Skipped git commit. Review and commit manually."
fi
