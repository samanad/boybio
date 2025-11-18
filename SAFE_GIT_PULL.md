# Safe Git Pull Process for Production Server

## ⚠️ IMPORTANT: Never Use `git clean -fd`

**NEVER run `git clean -fd` on a production server!** This command deletes ALL untracked files and directories, including:
- `product/config.php` (database credentials)
- `product/uploads/` (user uploads, logos, files)
- Custom language translations
- Any other local files not in git

## ✅ Safe Pull Process

### Step 1: Check Current Status

```bash
cd /var/www/www-root/data/www/boybio.net

# Check current status
git status

# Check what branch you're on
git branch
```

### Step 2: Handle Local Changes Safely

**If you have local changes to tracked files:**

```bash
# Stash local changes (saves them temporarily)
git stash

# Pull the updates
git pull origin backup

# If you need your stashed changes back:
# git stash pop
```

**If you have untracked files that conflict:**

```bash
# First, PREVIEW what would be deleted (dry run)
git clean -fdn

# Review the output carefully!
# If it shows important files, DO NOT proceed with git clean

# Instead, manually remove ONLY the conflicting files:
# Example: if it says "product/app/controllers/Chats.php would be removed"
rm -f product/app/controllers/Chats.php
rm -f product/app/controllers/admin/AdminChats.php
rm -f product/themes/altum/views/app_wrapper.php

# Then pull
git pull origin backup
```

### Step 3: Safe Pull Command

```bash
# Navigate to project root
cd /var/www/www-root/data/www/boybio.net

# Fetch latest changes
git fetch origin backup

# Check what will change (preview)
git log HEAD..origin/backup --oneline

# Pull the changes
git pull origin backup
```

### Step 4: Handle Conflicts (if any)

**If you get "local changes would be overwritten":**

```bash
# List the conflicting files
git status

# For each conflicting file, decide:
# Option A: Keep your local version
git checkout --ours product/path/to/file.php

# Option B: Use the remote version
git checkout --theirs product/path/to/file.php

# Option C: Stash your changes
git stash
git pull origin backup
git stash pop  # Then manually merge if needed
```

### Step 5: After Pulling

```bash
# Fix permissions
chown -R www-data:www-data product/
chmod -R 755 product/
chmod -R 775 product/uploads/

# Clear cache
rm -rf product/uploads/cache/*
chmod -R 777 product/uploads/cache/

# Verify config.php still exists (should never be deleted)
ls -la product/config.php
```

## 🛡️ Protected Files (Never Delete)

These files are protected by `.gitignore` and should NEVER be deleted:

- `product/config.php` - Database credentials
- `product/uploads/` - All user uploads, logos, files
- `product/uploads/cache/` - Cache files (can be cleared, but directory should exist)
- Custom language files in `product/app/languages/` (if you have custom translations)

## 📋 Quick Reference: Safe Pull Commands

```bash
# Standard safe pull
cd /var/www/www-root/data/www/boybio.net
git fetch origin backup
git pull origin backup

# If you have local changes
git stash
git pull origin backup
git stash pop  # Only if you need your changes back

# If you have conflicting untracked files
# 1. Preview what would be deleted:
git clean -fdn

# 2. Manually remove ONLY the specific conflicting files
# 3. Then pull:
git pull origin backup
```

## 🔄 Complete Safe Pull Script

Save this as `safe-pull.sh`:

```bash
#!/bin/bash

cd /var/www/www-root/data/www/boybio.net

echo "=== Safe Git Pull Process ==="
echo ""

# Check if config.php exists
if [ ! -f "product/config.php" ]; then
    echo "ERROR: config.php is missing! Aborting."
    exit 1
fi

# Check if uploads directory exists
if [ ! -d "product/uploads" ]; then
    echo "ERROR: uploads directory is missing! Aborting."
    exit 1
fi

# Stash any local changes
echo "Stashing local changes..."
git stash

# Fetch latest
echo "Fetching latest changes..."
git fetch origin backup

# Pull
echo "Pulling changes..."
git pull origin backup

# Fix permissions
echo "Fixing permissions..."
chown -R www-data:www-data product/
chmod -R 755 product/
chmod -R 775 product/uploads/

# Clear cache
echo "Clearing cache..."
rm -rf product/uploads/cache/*
chmod -R 777 product/uploads/cache/

echo ""
echo "=== Pull Complete ==="
echo "If you had stashed changes, restore them with: git stash pop"
```

Make it executable:
```bash
chmod +x safe-pull.sh
```

Then use it:
```bash
./safe-pull.sh
```

## ❌ What NOT to Do

1. ❌ **NEVER** run `git clean -fd` without checking what it will delete first
2. ❌ **NEVER** delete `product/config.php` - it contains database credentials
3. ❌ **NEVER** delete `product/uploads/` - it contains user files
4. ❌ **NEVER** force push to production (`git push --force`)
5. ❌ **NEVER** pull without checking status first

## ✅ What TO Do

1. ✅ Always use `git clean -fdn` first to preview
2. ✅ Always stash local changes before pulling
3. ✅ Always verify `config.php` exists after pulling
4. ✅ Always fix permissions after pulling
5. ✅ Always clear cache after pulling
6. ✅ Always backup before major operations

## 🔍 Troubleshooting

**If you accidentally deleted important files:**

1. Check if they're in git history:
   ```bash
   git log --all --full-history -- product/config.php
   ```

2. Restore from git (if it was tracked):
   ```bash
   git checkout HEAD -- product/config.php
   ```

3. If not in git, restore from server backup or recreate:
   - `config.php` - Recreate with database credentials
   - `uploads/` - Restore from backup or re-upload files

