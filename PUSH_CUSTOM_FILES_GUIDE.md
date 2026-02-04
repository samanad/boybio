# Guide: Pushing Custom Files to Git

## ⚠️ Important Notes

### ❌ DO NOT Push These:
- **`product/uploads/`** - Contains user-generated content (logos, files, images). Can be very large and is environment-specific. Should stay on server only.
- **`product/config.php`** - Contains database credentials. Should NEVER be in git for security.

### ✅ CAN Push These (with caution):
- **`product/plugins/`** - Plugin configurations (but be selective)
- **`product/app/languages/`** - Custom translations (good to track)

## Option 1: Push Only Plugin Config Files

If you want to track plugin configurations but not all plugin files:

```bash
cd /var/www/www-root/data/www/boybio.net

# Remove plugins/ from .gitignore temporarily
# Edit .gitignore and comment out: # product/plugins/

# Add only config files from plugins
git add product/plugins/*/config.php
git add product/plugins/*/config.json
git add product/plugins/*/settings.json

# Commit
git commit -m "Add plugin configurations"

# Push
git push origin backup
```

## Option 2: Push All Plugins (Not Recommended)

If you really want to push the entire plugins folder:

```bash
cd /var/www/www-root/data/www/boybio.net

# Remove plugins/ from .gitignore
# Edit .gitignore: remove or comment out the line: product/plugins/

# Add plugins folder
git add product/plugins/

# Commit
git commit -m "Add plugins folder"

# Push
git push origin backup
```

**Warning:** This will track ALL plugin files. Make sure you don't have sensitive data in plugins.

## Option 3: Push Custom Translations

To push custom language translations:

```bash
cd /var/www/www-root/data/www/boybio.net

# Check what language files you have
ls -la product/app/languages/

# Add your custom translation files
# Example: if you modified Romanian translations
git add product/app/languages/romanian#ro.php
git add product/app/languages/admin/romanian#ro.php

# Or add all language files (if you want to track all)
git add product/app/languages/

# Commit
git commit -m "Add/update custom language translations"

# Push
git push origin backup
```

## Recommended Approach: Selective Plugin Configs + Translations

```bash
cd /var/www/www-root/data/www/boybio.net

# 1. Update .gitignore to allow specific plugin files
# Edit .gitignore and change:
#   product/plugins/
# To:
#   product/plugins/*
#   !product/plugins/*/config.php
#   !product/plugins/*/config.json
#   !product/plugins/*/settings.json

# 2. Add plugin configs
git add product/plugins/*/config.php product/plugins/*/config.json product/plugins/*/settings.json 2>/dev/null || true

# 3. Add custom translations
git add product/app/languages/romanian#ro.php product/app/languages/admin/romanian#ro.php 2>/dev/null || true

# 4. Check what will be committed
git status

# 5. Commit
git commit -m "Add plugin configurations and custom translations"

# 6. Push
git push origin backup
```

## What NOT to Do

1. ❌ **NEVER** add `product/uploads/` - It's huge and contains user data
2. ❌ **NEVER** add `product/config.php` - Contains sensitive credentials
3. ❌ **NEVER** add cache files - They're temporary
4. ⚠️ **Be careful** with plugins - Only add config files, not user data

## Safe Workflow

```bash
cd /var/www/www-root/data/www/boybio.net

# 1. Check what you want to add
git status

# 2. Add only what you need
git add product/plugins/*/config.php
git add product/app/languages/romanian#ro.php

# 3. Review what will be committed
git status

# 4. Commit
git commit -m "Add plugin configs and custom translations"

# 5. Push
git push origin backup
```




