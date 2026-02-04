#!/bin/bash

# Selective Git Push Script
# This script allows you to push only specific folders to git

set -e

cd /var/www/www-root/data/www/boybio.net

echo "=== Selective Git Push Process ==="
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

# Check current branch
CURRENT_BRANCH=$(git branch --show-current)
echo "Current branch: $CURRENT_BRANCH"
echo ""

# Show what will be pushed
echo "=== Files to be pushed ==="
echo ""

# Check what exists
if [ -d "product/uploads/main" ]; then
    echo "✓ product/uploads/main/ (static assets)"
    FILES_TO_ADD="$FILES_TO_ADD product/uploads/main/"
fi

if [ -d "product/uploads/pwa" ]; then
    echo "✓ product/uploads/pwa/ (PWA assets)"
    FILES_TO_ADD="$FILES_TO_ADD product/uploads/pwa/"
fi

if [ -d "product/uploads/favicons" ]; then
    echo "✓ product/uploads/favicons/ (favicons)"
    FILES_TO_ADD="$FILES_TO_ADD product/uploads/favicons/"
fi

if [ -d "product/plugins" ]; then
    echo "✓ product/plugins/ (plugins folder)"
    FILES_TO_ADD="$FILES_TO_ADD product/plugins/"
fi

if [ -d "product/app/languages" ]; then
    echo "✓ product/app/languages/ (translations)"
    FILES_TO_ADD="$FILES_TO_ADD product/app/languages/"
fi

echo ""

# Option 1: Preview mode - show what would be added without actually adding
if [ "$1" == "--preview" ]; then
    echo "=== PREVIEW MODE ==="
    echo "Files that would be added:"
    echo ""
    for folder in $FILES_TO_ADD; do
        if [ -d "$folder" ] || [ -f "$folder" ]; then
            echo "  $folder"
            git status --porcelain "$folder" 2>/dev/null | head -10 || echo "    (untracked)"
        fi
    done
    echo ""
    echo "To actually push, run without --preview flag"
    exit 0
fi

# Option 2: Add and commit only specific folders
echo "=== Adding selected folders ==="
echo ""

# Stash any other uncommitted changes first
if git stash > /dev/null 2>&1; then
    echo "✓ Stashed other uncommitted changes"
    STASHED=true
else
    STASHED=false
fi

# Add only the folders we want
ADDED_ANY=false
for folder in $FILES_TO_ADD; do
    if [ -d "$folder" ] || [ -f "$folder" ]; then
        echo "Adding: $folder"
        git add "$folder" 2>/dev/null && ADDED_ANY=true || echo "  Warning: Could not add $folder"
    fi
done

if [ "$ADDED_ANY" = false ]; then
    echo ""
    echo "No files to add. All selected folders are already committed or don't exist."
    if [ "$STASHED" = true ]; then
        git stash pop
    fi
    exit 0
fi

echo ""
echo "=== Review what will be committed ==="
git status --short | head -20
echo ""

# Ask for confirmation (or use --yes flag to skip)
if [ "$1" != "--yes" ]; then
    echo "Press Enter to commit and push, or Ctrl+C to cancel..."
    read
fi

# Commit
echo ""
echo "=== Committing changes ==="
git commit -m "Add/update: uploads (static assets), plugins, and translations" || {
    echo "ERROR: Commit failed!"
    if [ "$STASHED" = true ]; then
        git stash pop
    fi
    exit 1
}

echo "✓ Committed"
echo ""

# Fetch latest from remote first
echo "=== Fetching latest from remote ==="
git fetch origin backup
echo "✓ Fetch complete"
echo ""

# Check if we need to merge first
if git merge-base --is-ancestor origin/backup HEAD 2>/dev/null; then
    echo "✓ Local branch is ahead, can push directly"
    CAN_PUSH=true
else
    echo "⚠️  Remote has commits we don't have locally"
    echo "   We'll try to push anyway (may need to merge first)"
    CAN_PUSH=false
fi

echo ""
echo "=== Pushing to origin/backup ==="

# Try to push
if git push origin "$CURRENT_BRANCH:backup" 2>&1; then
    echo ""
    echo "✓ Push successful!"
else
    PUSH_EXIT_CODE=$?
    echo ""
    echo "⚠️  Push failed (exit code: $PUSH_EXIT_CODE)"
    echo ""
    if [ $PUSH_EXIT_CODE -eq 1 ]; then
        echo "This usually means the remote has commits we don't have."
        echo ""
        echo "Options:"
        echo "1. Merge remote first, then push:"
        echo "   git merge origin/backup"
        echo "   git push origin $CURRENT_BRANCH:backup"
        echo ""
        echo "2. Force push (⚠️  WARNING: This overwrites remote!):"
        echo "   git push origin $CURRENT_BRANCH:backup --force"
        echo ""
        echo "3. Create a new commit with merge:"
        echo "   git merge origin/backup --no-commit"
        echo "   git commit -m 'Merge remote changes'"
        echo "   git push origin $CURRENT_BRANCH:backup"
    fi
fi

echo ""

# Restore stashed changes
if [ "$STASHED" = true ]; then
    echo "Restoring stashed changes..."
    git stash pop || echo "  (No conflicts with stashed changes)"
fi

echo ""
echo "=== Push Process Complete ==="




