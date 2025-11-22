# Protecting config.php from Git Overwrites

## Problem
The `config.php` file contains sensitive database credentials and site configuration. If this file is tracked in git and gets overwritten during a pull, it will break the site.

## Solution Implemented

1. **Removed config.php from git tracking** - The file is now in `.gitignore` and no longer tracked
2. **Created config.php.example** - A template file that can be safely tracked in git
3. **Updated pull scripts** - All pull scripts now protect config.php from being overwritten
4. **Added backup/restore mechanism** - Scripts automatically backup and restore config.php if it gets overwritten

## Safe Pull Commands

### Option 1: Use the protected pull script
```bash
cd /var/www/www-root/data/www/boybio.net
./pull-changed-files-safe.sh
```

### Option 2: Use selective-pull.sh (also protected)
```bash
cd /var/www/www-root/data/www/boybio.net
./selective-pull.sh
```

### Option 3: Manual protected pull
```bash
cd /var/www/www-root/data/www/boybio.net

# Backup config.php first
cp product/config.php product/config.php.backup

# Fetch and pull
git fetch origin backup
git diff --name-only --diff-filter=M HEAD origin/backup | while read file; do
    # Skip config.php
    if [ "$file" != "product/config.php" ] && [ "$file" != "product/app/config/config.php" ]; then
        if [ -f "$file" ]; then
            git checkout origin/backup -- "$file" 2>/dev/null && echo "Updated: $file" || echo "Skipped: $file"
        fi
    else
        echo "PROTECTED (skipped): $file"
    fi
done 2>/dev/null

# Restore config.php if it was overwritten
if ! cmp -s product/config.php product/config.php.backup 2>/dev/null; then
    echo "Restoring config.php from backup"
    cp product/config.php.backup product/config.php
fi
rm -f product/config.php.backup

chown -R www-data:www-data product/
chmod -R 755 product/
```

## Protected Files
The following files are automatically protected from being overwritten:
- `product/config.php`
- `product/app/config/config.php`

## If config.php Gets Overwritten

If config.php accidentally gets overwritten, you can restore it from backup:

```bash
# Find the most recent backup
ls -t product/config.php.backup.* | head -1

# Restore it
cp product/config.php.backup.YYYYMMDD_HHMMSS product/config.php
```

## Setting Up config.php on a New Server

1. Copy the example file:
   ```bash
   cp product/config.php.example product/config.php
   ```

2. Edit config.php with your database credentials:
   ```bash
   nano product/config.php
   ```

3. Set proper permissions:
   ```bash
   chmod 644 product/config.php
   chown www-data:www-data product/config.php
   ```

## Verification

To verify config.php is protected:
```bash
cd /var/www/www-root/data/www/boybio.net
git status product/config.php
# Should show: "nothing to commit, working tree clean" or the file should not appear
```

