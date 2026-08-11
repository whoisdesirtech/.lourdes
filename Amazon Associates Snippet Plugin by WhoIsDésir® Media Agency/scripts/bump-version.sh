#!/usr/bin/env bash
#
# bump-version.sh <version> [-m "summary"] [--skip-tests]
#
# Bumps the Amazon Associates PHP Snippets plugin to <version> (x.y.z) and
# pins a dated changelog entry in readme.txt (canonical history) and
# README.md (top-of-file highlight).
#
# Files updated:
#   - amazon-associates-snippets.php   header * Version: + AA_SNIPPETS_VERSION
#   - readme.txt                       Stable tag + Changelog entry
#   - README.md                        title (vX.Y.Z) + "What's New" section
#
# Examples:
#   ./scripts/bump-version.sh 1.5.1
#   ./scripts/bump-version.sh 1.6.0 -m "Added OAuth token refresh on 401 responses"
#   ./scripts/bump-version.sh 1.5.2 --skip-tests

set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_DIR="$SCRIPT_DIR/../amazon-associates-snippets"
MAIN_FILE="$PLUGIN_DIR/amazon-associates-snippets.php"
README_TXT="$PLUGIN_DIR/readme.txt"
README_MD="$PLUGIN_DIR/README.md"

SUMMARY=""
RUN_TESTS=1
FORCE=0

# ---- Parse args -----------------------------------------------------------
if [[ $# -lt 1 ]]; then
    echo "Usage: bump-version.sh <version> [-m \"summary\"] [--skip-tests]" >&2
    echo "  <version> must be in x.y.z format (e.g. 1.5.1)" >&2
    exit 1
fi

NEW="$1"
shift
while [[ $# -gt 0 ]]; do
    case "$1" in
        -m) SUMMARY="${2:-}"; shift 2 ;;
        --skip-tests) RUN_TESTS=0; shift ;;
        --force) FORCE=1; shift ;;
        *) echo "Unknown argument: $1" >&2; exit 1 ;;
    esac
done

# ---- Validate version format ---------------------------------------------
if ! [[ "$NEW" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo "Error: '$NEW' is not a valid x.y.z version." >&2
    exit 1
fi

# ---- Current version (source of truth: AA_SNIPPETS_VERSION) ---------------
CURRENT="$(sed -n "s/^define( 'AA_SNIPPETS_VERSION', '\([0-9.]*\)' );.*/\1/p" "$MAIN_FILE")"
if [[ -z "$CURRENT" ]]; then
    echo "Error: could not read current version from $MAIN_FILE" >&2
    exit 1
fi

# ---- Semver compare --------------------------------------------------------
CMP="$(awk -v a="$NEW" -v b="$CURRENT" 'BEGIN{split(a,A,".");split(b,B,".");
for(i=1;i<=3;i++){if(A[i]>B[i]){print "newer";exit} if(A[i]<B[i]){print "older";exit}} print "equal"}')"

if [[ "$CMP" == "equal" ]]; then
    echo "Error: version is already $CURRENT — nothing to bump." >&2
    exit 1
fi
if [[ "$CMP" == "older" ]] && [[ "$FORCE" == "0" ]]; then
    echo "Error: $NEW is older than current version $CURRENT." >&2
    echo "       Bump forward (or pass --force to downgrade)." >&2
    exit 1
fi

echo "Bumping plugin $CURRENT -> $NEW"

# ---- Changelog bullets ------------------------------------------------------
DATE="$(date +"%B %-d, %Y")"
BULLETS=""
if [[ -n "$SUMMARY" ]]; then
    BULLETS="* $SUMMARY"
else
    BULLETS="* Version bump to $NEW (from $CURRENT)."
fi

# If a git repo, append commit subjects since the last tag as extra bullets.
if git -C "$PLUGIN_DIR" rev-parse --is-inside-work-tree >/dev/null 2>&1; then
    LAST_TAG="$(git -C "$PLUGIN_DIR" describe --tags --abbrev=0 2>/dev/null || true)"
    RANGE="${LAST_TAG:-HEAD}..HEAD"
    if [[ -n "$LAST_TAG" ]] && git -C "$PLUGIN_DIR" log --oneline "$RANGE" >/dev/null 2>&1; then
        while IFS= read -r line; do
            BULLETS="$BULLETS
* $line"
        done < <(git -C "$PLUGIN_DIR" log --oneline "$RANGE")
    fi
fi

# ---- Apply version edits ---------------------------------------------------
perl -i -pe "s/^\s*\* Version:.*/* Version:           $NEW/" "$MAIN_FILE"
perl -i -pe "s/^define\( 'AA_SNIPPETS_VERSION', '[^']*' \);/define( 'AA_SNIPPETS_VERSION', '$NEW' );/" "$MAIN_FILE"
perl -i -pe "s/^Stable tag:.*/Stable tag: $NEW/" "$README_TXT"
perl -i -pe "s/\(v[0-9]+\.[0-9]+\.[0-9]+\)/(v$NEW)/" "$README_MD"

# ---- readme.txt: insert changelog entry ------------------------------------
perl -0pi -e "s/== Changelog ==\n/== Changelog ==\n\n= $NEW - $DATE =\n$BULLETS\n\n/" "$README_TXT"

# ---- README.md: replace the "What's New" section ---------------------------
perl -0pi -e "s/## What's New.*?^---$/## What's New in Version $NEW\n\n$BULLETS\n\n---/ms" "$README_MD"

# ---- Verify edits ----------------------------------------------------------
echo ""
echo "Updated:"
grep -n "^\* Version:" "$MAIN_FILE"
grep -n "^define( 'AA_SNIPPETS_VERSION'" "$MAIN_FILE"
grep -n "^Stable tag:" "$README_TXT"
grep -n "^= $NEW - " "$README_TXT" || true
grep -n "(v$NEW)" "$README_MD"

# ---- Tests ------------------------------------------------------------------
if [[ "$RUN_TESTS" == "1" ]]; then
    echo ""
    echo "Running tests..."
    (cd "$SCRIPT_DIR/.." && ./vendor/bin/phpunit)
fi

echo ""
echo "Done. New version: $NEW"
