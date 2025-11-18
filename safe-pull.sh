#!/bin/bash

# Safe Git Pull Script for Production Server
# This script safely pulls updates without deleting important files

set -e  # Exit on error

cd /var/www/www-root/data/www/boybio.net

echo "=== Safe Git Pull Process ==="
echo ""

# Safety checks - abort if critical files are missing
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

# Check for untracked files that might conflict
echo "Checking for conflicting untracked files..."
UNTRACKED=$(git status --porcelain | grep "^??" | awk '{print $2}' | head -20)

if [ ! -z "$UNTRACKED" ]; then
    echo "Warning: Found untracked files:"
    echo "$UNTRACKED" | head -10
    echo ""
    echo "These files will NOT be deleted. If they conflict with incoming changes,"
    echo "you may need to manually remove specific files after seeing the error."
    echo ""
fi

# Stash any local changes to tracked files
echo "Stashing local changes to tracked files..."
if git stash > /dev/null 2>&1; then
    echo "✓ Local changes stashed"
    STASHED=true
else
    echo "✓ No local changes to stash"
    STASHED=false
fi
echo ""

# Fetch latest changes
echo "Fetching latest changes from origin/backup..."
git fetch origin backup
echo "✓ Fetch complete"
echo ""

# Show what will be updated
echo "Changes to be pulled:"
git log HEAD..origin/backup --oneline | head -10
echo ""

# Pull the changes
echo "Pulling changes..."
if git pull origin backup; then
    echo "✓ Pull successful"
else
    echo ""
    echo "ERROR: Pull failed!"
    echo ""
    echo "If you see 'untracked files would be overwritten':"
    echo "1. Check which files are listed"
    echo "2. Manually remove ONLY those specific files:"
    echo "   rm -f product/path/to/conflicting/file.php"
    echo "3. Run this script again"
    echo ""
    
    # Restore stashed changes if pull failed
    if [ "$STASHED" = true ]; then
        echo "Restoring stashed changes..."
        git stash pop
    fi
    
    exit 1
fi
echo ""

# Fix permissions
echo "Fixing permissions..."
chown -R www-data:www-data product/ 2>/dev/null || true
chmod -R 755 product/ 2>/dev/null || true
chmod -R 775 product/uploads/ 2>/dev/null || true
echo "✓ Permissions fixed"
echo ""

# Clear cache
echo "Clearing cache..."
rm -rf product/uploads/cache/* 2>/dev/null || true
chmod -R 777 product/uploads/cache/ 2>/dev/null || true
echo "✓ Cache cleared"
echo ""

# Final safety check
if [ ! -f "product/config.php" ]; then
    echo "ERROR: config.php was deleted during pull! This should never happen."
    echo "Please restore from backup immediately."
    exit 1
fi

if [ ! -d "product/uploads" ]; then
    echo "ERROR: uploads directory was deleted during pull! This should never happen."
    echo "Please restore from backup immediately."
    exit 1
fi

if [ ! -d "product/plugins" ]; then
    echo "ERROR: plugins directory was deleted during pull! This should never happen."
    echo "Please restore from backup immediately."
    exit 1
fi

echo "=== Pull Complete ==="
echo ""

if [ "$STASHED" = true ]; then
    echo "Note: You had local changes that were stashed."
    echo "To restore them, run: git stash pop"
    echo ""
fi

echo "Next steps:"
echo "1. Test the website to ensure everything works"
echo "2. Check for any errors in the logs"
echo "3. If you had stashed changes, review them with: git stash show"
echo ""

