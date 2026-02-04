# Database Migration Information - Version 60 to 63

## Migration System Overview

✅ **The database migration system IS built into the script.**

The update system works as follows:

### How It Works

1. **Location**: `product/update/` folder contains:
   - `update.php` - The migration runner script
   - `info.php` - Defines current version and list of SQL files
   - `sql/` folder - Contains numbered SQL migration files

2. **Migration Process**:
   - The system reads your current version from the database
   - It finds all SQL files that need to be run (from your version to the target version)
   - It executes them in order
   - It separates Regular and Extended License queries

3. **SQL File Naming**:
   - Files are numbered: `6100.sql`, `6200.sql`, `6300.sql`
   - Version 61.0.0 = 6100
   - Version 62.0.0 = 6200  
   - Version 63.0.0 = 6300
   - Sub-versions: `6101.sql`, `6201.sql`, etc.

### Current Status

**Your Current Product:**
- ✅ Has `update/` folder with migration system
- ✅ Has SQL files up to `6100.sql` (Version 61.0.0)
- ✅ Migration system is functional

**Version 63:**
- ⚠️ **No `update/` folder found in extracted v63 files**
- ⚠️ **SQL migration files for 61, 62, 63 are MISSING**

---

## What You Need to Do

### Option 1: Extract SQL Files from v63 Zip (Recommended)

The SQL files should be in the v63 zip file. You need to:

1. **Extract the update folder from v63 zip**:
   ```powershell
   # Extract only the update folder
   Expand-Archive -Path '66biolinks-v63.zip' -DestinationPath 'temp_v63_update' -Force
   ```

2. **Copy SQL files**:
   - Copy `temp_v63_update/product/update/sql/6100.sql` (if exists)
   - Copy `temp_v63_update/product/update/sql/6200.sql` (for v62)
   - Copy `temp_v63_update/product/update/sql/6300.sql` (for v63)
   - Copy any sub-version files (6101, 6201, 6301, etc.)

3. **Update info.php**:
   - Update `product/update/info.php` to include new version codes:
     ```php
     define('NEW_PRODUCT_VERSION', '63.0.0');
     define('NEW_PRODUCT_CODE', '6300');
     
     $updates = [
         // ... existing updates ...
         '6000',
         '6100',  // Add if exists
         '6200',  // Add for v62
         '6300',  // Add for v63
     ];
     ```

### Option 2: Check if SQL Files Are Embedded

Sometimes SQL files might be:
- In a separate download
- In the install dump.sql file
- Embedded in the update.php file itself

### Option 3: Manual Database Updates

If SQL files are not available, you'll need to manually create the database changes based on the new features:

**Required Tables/Columns for v63:**
1. **Payment Processors Table** (if not exists):
   ```sql
   CREATE TABLE IF NOT EXISTS `payment_processors` (
     `payment_processor_id` bigint unsigned NOT NULL AUTO_INCREMENT,
     `user_id` bigint unsigned NOT NULL,
     `name` varchar(64) NOT NULL,
     `processor` varchar(32) NOT NULL,
     `settings` text,
     `is_enabled` tinyint(1) DEFAULT 1,
     `datetime` datetime DEFAULT NULL,
     `last_datetime` datetime DEFAULT NULL,
     PRIMARY KEY (`payment_processor_id`),
     KEY `user_id` (`user_id`)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

2. **Credit Notes** (likely added to payments table or separate table)

3. **Tax Import Features** (likely new columns in taxes table)

4. **Discount Code Start/End Dates**:
   ```sql
   ALTER TABLE `codes` 
   ADD COLUMN `start_date` datetime NULL AFTER `datetime`,
   ADD COLUMN `end_date` datetime NULL AFTER `start_date`;
   ```

5. **Payment Refund/Chargeback Status**:
   ```sql
   ALTER TABLE `payments` 
   ADD COLUMN `status` varchar(32) DEFAULT 'completed' AFTER `type`;
   ```

---

## How to Run Migrations

### Automatic (Recommended)

1. **Access the update page**: `https://yoursite.com/update/`
2. The system will automatically:
   - Detect your current version (60.0.0)
   - Find all SQL files from 6100 to 6300
   - Run them in order
   - Apply Extended License queries if you have Extended License

### Manual (If Needed)

1. **Backup your database first!**
2. Run SQL files manually via phpMyAdmin or command line:
   ```bash
   mysql -u username -p database_name < update/sql/6100.sql
   mysql -u username -p database_name < update/sql/6200.sql
   mysql -u username -p database_name < update/sql/6300.sql
   ```

---

## Important Notes

1. **Backup First**: Always backup your database before running migrations

2. **Extended License**: Some SQL queries are marked with `-- EXTENDED SEPARATOR --` and only run if you have Extended License

3. **Error Handling**: The update system has error handling - queries marked with `-- X --` won't stop execution if they fail

4. **Version Tracking**: The system tracks your version in the `settings` table with key `product_info`

---

## Next Steps

1. ✅ **Check v63 zip file** for `update/sql/` folder
2. ✅ **Extract SQL files** if found
3. ✅ **Copy to product/update/sql/** folder
4. ✅ **Update info.php** with new version codes
5. ✅ **Run update system** via `/update/` page
6. ✅ **Verify database changes** were applied

---

## Verification

After running migrations, verify:

```sql
-- Check current version
SELECT `value` FROM `settings` WHERE `key` = 'product_info';
-- Should show: {"version":"63.0.0","code":"6300"}

-- Check if payment_processors table exists
SHOW TABLES LIKE 'payment_processors';

-- Check if codes table has start_date/end_date
DESCRIBE `codes`;
```

---

**Status**: Migration system is built-in, but SQL files for v61-v63 need to be extracted from the zip file.

**Action Required**: Extract `update/sql/` folder from v63 zip and copy SQL files to your product folder.


