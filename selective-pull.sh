#!/bin/bash

# Selective Git Pull Script
# This script allows you to pull only specific files or preview changes before pulling

set -e

cd /var/www/www-root/data/www/boybio.net

echo "=== Selective Git Pull Process ==="
echo ""

# Safety checks
if [ ! -f "product/config.php" ]; then
    echo "ERROR: config.php is missing! Aborting."
    exit 1
fi

if [ ! -d "product/uploads" ]; then
    echo "ERROR: uploads directory is missing! Aborting."
    exit 1
fi

if [ ! -d "product/plugins" ]; then
    echo "ERROR: plugins directory is missing! Aborting."
    exit 1
fi

echo "✓ Safety checks passed"
echo ""

# Fetch latest changes without merging
echo "Fetching latest changes..."
git fetch origin backup
echo "✓ Fetch complete"
echo ""

# Show what would change
echo "Files that would be updated:"
git diff --name-only HEAD origin/backup | head -20
echo ""

# Option 1: Preview mode - show what would change without actually changing
if [ "$1" == "--preview" ]; then
    echo "=== PREVIEW MODE ==="
    echo "Showing what would change (no files will be modified):"
    echo ""
    git diff --stat HEAD origin/backup
    echo ""
    echo "To see specific file changes:"
    echo "  git diff HEAD origin/backup -- product/path/to/file.php"
    echo ""
    echo "To actually pull, run without --preview flag"
    exit 0
fi

# Option 2: Pull specific files only
if [ "$1" == "--files" ] && [ -n "$2" ]; then
    echo "=== SELECTIVE FILE PULL ==="
    echo "Pulling only specified files..."
    echo ""
    
    # Protected files that should NEVER be overwritten
    PROTECTED_FILES=("product/config.php" "product/app/config/config.php")
    
    # Checkout specific files from remote branch
    while IFS= read -r file; do
        # Skip protected files
        SKIP_FILE=false
        for protected in "${PROTECTED_FILES[@]}"; do
            if [ "$file" == "$protected" ]; then
                echo "PROTECTED (skipped): $file - This file contains sensitive configuration and will not be overwritten"
                SKIP_FILE=true
                break
            fi
        done
        
        if [ "$SKIP_FILE" = true ]; then
            continue
        fi
        
        if [ -f "$file" ] || [ -d "$file" ]; then
            echo "Updating: $file"
            git checkout origin/backup -- "$file" 2>/dev/null || echo "  Warning: Could not update $file"
        else
            echo "Creating: $file"
            git checkout origin/backup -- "$file" 2>/dev/null || echo "  Warning: Could not create $file"
        fi
    done <<< "$2"
    
    echo ""
    echo "✓ Selected files updated"
    echo ""
    echo "Note: You may need to fix permissions:"
    echo "  chown -R www-data:www-data product/"
    echo "  chmod -R 755 product/"
    exit 0
fi

# Option 3: Merge with strategy to prefer remote for conflicts
if [ "$1" == "--theirs" ]; then
    echo "=== MERGE WITH REMOTE PREFERENCE ==="
    echo "Merging with preference for remote changes..."
    echo ""
    
    # Stash local changes
    if git stash > /dev/null 2>&1; then
        echo "✓ Local changes stashed"
        STASHED=true
    else
        STASHED=false
    fi
    
    # Merge with strategy
    if git merge -X theirs origin/backup --no-commit; then
        echo "✓ Merge successful (not committed yet)"
        echo ""
        echo "Review changes with: git status"
        echo "If everything looks good, commit with: git commit"
        echo "Or abort with: git merge --abort"
    else
        echo "Merge had conflicts or errors"
        if [ "$STASHED" = true ]; then
            git stash pop
        fi
        exit 1
    fi
    exit 0
fi

# Option 4: Standard pull but handle untracked files better
echo "=== STANDARD PULL (with untracked file handling) ==="
echo ""

# Protected files - backup before pull
PROTECTED_FILES=("product/config.php" "product/app/config/config.php")
for protected in "${PROTECTED_FILES[@]}"; do
    if [ -f "$protected" ]; then
        echo "Backing up protected file: $protected"
        cp "$protected" "$protected.backup.$(date +%Y%m%d_%H%M%S)" 2>/dev/null || true
    fi
done

# Check for untracked files that would conflict
echo "Checking for conflicting untracked files..."
CONFLICTING=$(git merge-tree $(git merge-base HEAD origin/backup) HEAD origin/backup 2>/dev/null | grep -E "^\+.*" | awk '{print $2}' | head -20)

if [ ! -z "$CONFLICTING" ]; then
    echo "Warning: These untracked files may conflict:"
    echo "$CONFLICTING"
    echo ""
    echo "Options:"
    echo "  1. Remove conflicting files manually"
    echo "  2. Use --theirs flag to prefer remote versions"
    echo "  3. Use --files flag to pull only specific files"
    exit 1
fi

# Stash local changes
if git stash > /dev/null 2>&1; then
    echo "✓ Local changes stashed"
    STASHED=true
else
    STASHED=false
fi

# Pull
if git pull origin backup; then
    echo "✓ Pull successful"
    
    # Restore protected files if they were overwritten
    for protected in "${PROTECTED_FILES[@]}"; do
        if [ -f "$protected.backup."* ] 2>/dev/null; then
            BACKUP_FILE=$(ls -t "$protected.backup."* 2>/dev/null | head -1)
            if [ -f "$BACKUP_FILE" ]; then
                # Check if file was actually changed (compare with backup)
                if ! cmp -s "$protected" "$BACKUP_FILE" 2>/dev/null; then
                    echo "Restoring protected file: $protected"
                    cp "$BACKUP_FILE" "$protected"
                    echo "✓ Restored $protected from backup"
                fi
                # Clean up backup
                rm -f "$BACKUP_FILE"
            fi
        fi
    done
else
    echo "Pull failed - see error above"
    if [ "$STASHED" = true ]; then
        git stash pop
    fi
    exit 1
fi

# Fix permissions
echo ""
echo "Fixing permissions..."
chown -R www-data:www-data product/ 2>/dev/null || true
chmod -R 755 product/ 2>/dev/null || true
chmod -R 775 product/uploads/ 2>/dev/null || true

# Clear cache
echo "Clearing cache..."
rm -rf product/uploads/cache/* 2>/dev/null || true
chmod -R 777 product/uploads/cache/ 2>/dev/null || true

echo ""
echo "=== Pull Complete ==="

if [ "$STASHED" = true ]; then
    echo "Note: You had local changes that were stashed."
    echo "To restore them: git stash pop"
fi




