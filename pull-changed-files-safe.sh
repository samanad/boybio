#!/bin/bash

# Safe pull script - only updates files that exist in remote branch
# Ignores files that are only local or only remote

set -e

cd /var/www/www-root/data/www/boybio.net

echo "=== Pulling Changed Files (Safe Mode) ==="
echo ""

# Safety checks
if [ ! -f "product/config.php" ]; then
    echo "ERROR: config.php is missing! Aborting."
    exit 1
fi

# Fetch latest changes
echo "Fetching latest changes from remote..."
git fetch origin backup
echo "✓ Fetch complete"
echo ""

# Get list of files that changed and exist in remote
echo "Checking for changed files..."
UPDATED_COUNT=0
SKIPPED_COUNT=0
ERROR_COUNT=0

# Protected files that should NEVER be overwritten
PROTECTED_FILES=(
    "product/config.php"
    "product/app/config/config.php"
)

# Get files that differ between local and remote
# Use process substitution to avoid subshell issues with counters
while IFS= read -r file; do
    # Skip empty lines
    [ -z "$file" ] && continue
    
    # Skip protected files
    SKIP_FILE=false
    for protected in "${PROTECTED_FILES[@]}"; do
        if [ "$file" == "$protected" ]; then
            echo "  PROTECTED (skipped): $file"
            SKIP_FILE=true
            SKIPPED_COUNT=$((SKIPPED_COUNT + 1))
            break
        fi
    done
    
    if [ "$SKIP_FILE" = true ]; then
        continue
    fi
    
    # Check if file exists in remote branch
    if git cat-file -e "origin/backup:$file" 2>/dev/null; then
        # Check if file actually differs (not just listed)
        if git diff --quiet HEAD origin/backup -- "$file" 2>/dev/null; then
            # File is the same, skip it silently
            continue
        fi
        
        # File exists in remote and differs, try to update it
        if [ -f "$file" ] || [ -d "$file" ]; then
            echo "  Updating: $file"
            if git checkout origin/backup -- "$file" 2>/dev/null; then
                UPDATED_COUNT=$((UPDATED_COUNT + 1))
            else
                echo "    Warning: Could not update $file"
                ERROR_COUNT=$((ERROR_COUNT + 1))
            fi
        else
            # File doesn't exist locally but exists in remote - create it
            echo "  Creating: $file"
            if git checkout origin/backup -- "$file" 2>/dev/null; then
                UPDATED_COUNT=$((UPDATED_COUNT + 1))
            else
                echo "    Warning: Could not create $file"
                ERROR_COUNT=$((ERROR_COUNT + 1))
            fi
        fi
    else
        # File doesn't exist in remote - skip it (it's local-only)
        echo "  Skipping (local-only): $file"
        SKIPPED_COUNT=$((SKIPPED_COUNT + 1))
    fi
done < <(git diff --name-only HEAD origin/backup)

echo ""
echo "✓ Update complete"
echo "  Updated: $UPDATED_COUNT files"
echo "  Skipped: $SKIPPED_COUNT local-only files"
echo "  Errors: $ERROR_COUNT files"
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

