# Selective Git Pull Guide

## Problem
When you have many untracked files on the server that conflict with files coming from git, a standard `git pull` fails. Instead of removing all conflicting files manually, you can use selective pull methods.

## Solution Options

### Option 1: Preview Changes First (Recommended)

```bash
# See what would change without actually changing anything
./selective-pull.sh --preview

# Or manually:
git fetch origin backup
git diff --stat HEAD origin/backup
git diff HEAD origin/backup -- product/path/to/file.php
```

### Option 2: Pull Only Specific Files

```bash
# Pull only the files you want to update
./selective-pull.sh --files "product/app/core/Authentication.php
product/themes/altum/views/admin/settings/partials/security.php"

# Or manually:
git fetch origin backup
git checkout origin/backup -- product/app/core/Authentication.php
git checkout origin/backup -- product/themes/altum/views/admin/settings/partials/security.php
```

### Option 3: Merge with Remote Preference

```bash
# Merge but prefer remote versions for conflicts
./selective-pull.sh --theirs

# Or manually:
git fetch origin backup
git merge -X theirs origin/backup --no-commit
# Review changes, then: git commit
```

### Option 4: Use Git Merge with Untracked File Handling

```bash
# Fetch first
git fetch origin backup

# Try to merge (will show conflicts)
git merge origin/backup --no-commit

# If it fails due to untracked files, remove only those specific files
# Then try again
git merge origin/backup
```

## Best Practice: Use Git Merge Instead of Pull

Instead of `git pull`, use `git fetch` + `git merge` which gives you more control:

```bash
# Step 1: Fetch (downloads changes but doesn't apply them)
git fetch origin backup

# Step 2: See what would change
git log HEAD..origin/backup --oneline
git diff --stat HEAD origin/backup

# Step 3: Merge (applies changes)
git merge origin/backup

# If conflicts occur, handle them:
# - For untracked files: remove them manually
# - For tracked files: resolve conflicts, then git add and git commit
```

## Selective File Update (Most Control)

If you only want to update specific files:

```bash
# Fetch latest
git fetch origin backup

# Update only specific files
git checkout origin/backup -- product/app/core/Authentication.php
git checkout origin/backup -- product/themes/altum/views/admin/settings/partials/security.php
git checkout origin/backup -- product/app/languages/admin/english#en.php

# Fix permissions
chown -R www-data:www-data product/
chmod -R 755 product/
```

## Update Only Modified Files (Skip Untracked Conflicts)

```bash
# Fetch
git fetch origin backup

# Get list of files that changed in remote
git diff --name-only HEAD origin/backup > /tmp/changed_files.txt

# Update only files that exist in both local and remote
while IFS= read -r file; do
    if [ -f "$file" ]; then
        echo "Updating: $file"
        git checkout origin/backup -- "$file"
    fi
done < /tmp/changed_files.txt

# Clean up
rm /tmp/changed_files.txt
```

## Recommended Workflow

For your situation, here's the recommended approach:

```bash
cd /var/www/www-root/data/www/boybio.net

# 1. Fetch latest (safe, doesn't change anything)
git fetch origin backup

# 2. See what changed
git log HEAD..origin/backup --oneline

# 3. See which files changed
git diff --name-only HEAD origin/backup

# 4. If you want to update everything, remove conflicting untracked files first
# (Use the commands from safe-pull.sh output)

# 5. Then merge
git merge origin/backup

# 6. Fix permissions
chown -R www-data:www-data product/
chmod -R 755 product/
```

## Why This is Better

- **More control**: You see what will change before it changes
- **Selective updates**: Update only what you need
- **Less risky**: Fetch first, then decide what to merge
- **Better error handling**: Can abort merge if something looks wrong

## Quick Reference

```bash
# Preview changes
git fetch origin backup
git diff --stat HEAD origin/backup

# Update specific file
git checkout origin/backup -- product/path/to/file.php

# Update all changed files (if no conflicts)
git merge origin/backup

# See what files would conflict
git merge-tree $(git merge-base HEAD origin/backup) HEAD origin/backup
```




