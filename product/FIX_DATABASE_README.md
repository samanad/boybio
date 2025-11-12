# Database Fix Instructions

## Overview
This fix script resolves errors that occur after importing a database that doesn't match the v60.0.0 schema.

## Errors Fixed

1. **Fatal Error**: `Unknown column 'project_id' in 'INSERT INTO' query`
   - **Location**: `app/controllers/l/Link.php` line 437
   - **Fix**: Adds `project_id` column to `track_links` table

2. **Warning**: `Undefined property: stdClass::$is_enabled` in `cookie_consent.php`
   - **Location**: `themes/altum/views/partials/cookie_consent.php` line 3
   - **Fix**: Ensures `cookie_consent` setting exists with proper default values

3. **Warning**: `Undefined property: stdClass::$sixsixpusher_is_enabled` in `Router.php`
   - **Location**: `app/core/Router.php` line 1742
   - **Fix**: Adds `sixsixpusher_is_enabled` to `links` settings

4. **Warning**: `Undefined property: stdClass::$qr_codes_is_enabled` in `app_sidebar.php`
   - **Location**: `themes/altum/views/partials/app_sidebar.php` line 88
   - **Fix**: Ensures `qr_codes_is_enabled` exists in `codes` settings

5. **Warning**: `Undefined property: stdClass::$share_buttons` in `share_buttons.php`
   - **Location**: `themes/altum/views/partials/share_buttons.php` line 10+
   - **Fix**: Adds `share_buttons` object to `socials` settings with all required properties (facebook, threads, x, pinterest, linkedin, reddit, whatsapp, telegram, snapchat, microsoft_teams, email, copy, share, print)

## How to Apply the Fix

### Option 1: Using phpMyAdmin or MySQL Client

1. Log into your database management tool (phpMyAdmin, MySQL Workbench, etc.)
2. Select your database
3. Go to the SQL tab
4. Copy and paste the contents of `fix_database_errors.sql`
5. Click "Go" or "Execute"
6. Verify you see the success message: "Database fixes completed successfully!"

### Option 2: Using Command Line (SSH)

```bash
# Navigate to your product directory
cd /path/to/your/product

# Run the SQL file (replace with your database credentials)
mysql -u your_username -p your_database_name < fix_database_errors.sql
```

### Option 3: Using MySQL Command Line

```sql
-- Connect to MySQL
mysql -u your_username -p

-- Select your database
USE your_database_name;

-- Run the fix script
SOURCE /path/to/product/fix_database_errors.sql;
```

## What the Script Does

1. **Adds `project_id` column** to `track_links` table (if it doesn't exist)
2. **Updates existing records** to populate `project_id` from the `links` table
3. **Adds foreign key constraint** for `project_id` (if it doesn't exist)
4. **Creates/updates `cookie_consent` setting** with default values
5. **Adds `qr_codes_is_enabled`** to `codes` setting (if missing)
6. **Adds `sixsixpusher_is_enabled`** to `links` setting (if missing)
7. **Adds `share_buttons`** to `socials` setting with all required properties (if missing)
8. **Updates product version** to 60.0.0

## Safety Features

- The script checks if columns/constraints already exist before adding them
- Uses conditional INSERT/UPDATE statements to avoid duplicates
- Safe to run multiple times (idempotent)

## After Running the Fix

**IMPORTANT: You MUST clear the cache after running the SQL script!**

The application caches settings, so even after updating the database, you need to clear the cache.

### Option 1: Use the cache clearing script (Recommended)
1. Upload `clear_cache.php` to your website root directory (same level as `product/` folder)
2. Access it via browser: `https://yourdomain.com/clear_cache.php`
3. Delete `clear_cache.php` after use for security

### Option 2: Manual cache clearing
- Delete cache files in your cache directory (usually `uploads/cache/` or similar)
- Or restart PHP-FPM: `sudo service php-fpm restart` (or `php8.1-fpm`, `php8.2-fpm`, etc.)

### Option 3: Via Admin Panel (if available)
- Some installations have a cache clearing option in the admin panel

After clearing cache:
1. Refresh your website
2. The errors should be resolved

## Notes

- **Backup your database** before running any SQL scripts
- The script is safe to run multiple times
- If you encounter any issues, restore from backup and contact support

## Troubleshooting

If you still see errors after running the script:

1. Verify the script executed successfully
2. Check that all settings exist: `SELECT * FROM settings WHERE key IN ('cookie_consent', 'codes', 'links');`
3. Verify the column exists: `DESCRIBE track_links;` (should show `project_id`)
4. Clear PHP opcache if enabled: `opcache_reset()` or restart PHP-FPM

