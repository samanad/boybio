#!/bin/bash

# Simple script to pull only files from recent commits
# Usage: bash pull-recent-changes.sh [number_of_commits]
# Default: pulls files from last 3 commits

set -e

cd /var/www/www-root/data/www/boybio.net

# Number of recent commits to check (default: 3)
COMMITS=${1:-3}

echo "=== Pulling Files from Last $COMMITS Commits ==="
echo ""

# Safety check
if [ ! -f "product/config.php" ]; then
    echo "ERROR: config.php is missing! Aborting."
    exit 1
fi

# Fetch latest changes
echo "Fetching latest changes..."
git fetch origin backup
echo "✓ Fetch complete"
echo ""

# Get files changed in recent commits
echo "Files changed in last $COMMITS commits:"
CHANGED_FILES=$(git diff --name-only HEAD origin/backup~$COMMITS origin/backup 2>/dev/null | grep -v "^product/config.php" | grep -v "^product/app/config/config.php" || true)

if [ -z "$CHANGED_FILES" ]; then
    echo "  No files to update."
    echo ""
    echo "=== Complete ==="
    exit 0
fi

# Count files
FILE_COUNT=$(echo "$CHANGED_FILES" | grep -c . || echo "0")

echo "  Found $FILE_COUNT file(s) to update"
echo ""

# Protected files
PROTECTED_FILES=("product/config.php" "product/app/config/config.php")

UPDATED=0
SKIPPED=0

# Update each file
while IFS= read -r file; do
    [ -z "$file" ] && continue
    
    # Check if protected
    SKIP=false
    for protected in "${PROTECTED_FILES[@]}"; do
        if [ "$file" == "$protected" ]; then
            echo "  PROTECTED (skipped): $file"
            SKIP=true
            SKIPPED=$((SKIPPED + 1))
            break
        fi
    done
    
    if [ "$SKIP" = true ]; then
        continue
    fi
    
    # Check if file exists in remote
    if git cat-file -e "origin/backup:$file" 2>/dev/null; then
        echo "  Updating: $file"
        if git checkout origin/backup -- "$file" 2>/dev/null; then
            UPDATED=$((UPDATED + 1))
        else
            echo "    ✗ Failed to update"
        fi
    fi
done <<< "$CHANGED_FILES"

echo ""
echo "✓ Update complete"
echo "  Updated: $UPDATED files"
echo "  Skipped: $SKIPPED protected files"
echo ""

# Fix permissions
echo "Fixing permissions..."
chown -R www-data:www-data product/ 2>/dev/null || true
chmod -R 755 product/ 2>/dev/null || true
chmod -R 775 product/uploads/ 2>/dev/null || true

# Clear cache
echo "Clearing cache..."
rm -rf product/uploads/cache/* 2>/dev/null || true
chmod -R 777 product/uploads/cache/ 2>/dev/null || true

echo ""
echo "=== Complete ==="

