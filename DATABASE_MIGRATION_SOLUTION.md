# Database Migration Solution - SQL Files Created

## Problem
The SQL migration files for versions 61, 62, and 63 were not included in the extracted v63 zip file.

## Solution
✅ **I've created the missing SQL migration files manually** based on code analysis of the controllers and features.

---

## Files Created

### 1. `product/update/sql/6100.sql` (Version 61.0.0)
**Creates:**
- `payment_processors` table - For user payment processor management
- Adds `start_date` and `end_date` columns to `codes` table
- Adds `status` and `refunds` columns to `payments` table

**Key Changes:**
```sql
CREATE TABLE `payment_processors` (
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

ALTER TABLE `codes` 
ADD COLUMN `start_date` datetime NULL,
ADD COLUMN `end_date` datetime NULL;

ALTER TABLE `payments` 
ADD COLUMN `status` varchar(32) DEFAULT 'completed',
ADD COLUMN `refunds` text NULL;
```

### 2. `product/update/sql/6200.sql` (Version 62.0.0)
**Creates:**
- Optional columns for biolink page language and theme auto-apply
- Marked with `-- X --` so they won't fail if columns already exist

### 3. `product/update/sql/6300.sql` (Version 63.0.0)
**Creates:**
- Final version update
- Any additional v63-specific changes

### 4. Updated `product/update/info.php`
- Updated version to 63.0.0
- Added migration codes: 6100, 6200, 6300 to the `$updates` array

---

## How to Run Migrations

### Option 1: Automatic (Recommended)

1. **Access the update page**: 
   ```
   https://yoursite.com/update/
   ```

2. **The system will automatically:**
   - Detect your current version (60.0.0)
   - Find SQL files 6100, 6200, 6300
   - Run them in order
   - Update version to 63.0.0

### Option 2: Manual (If Needed)

1. **Backup your database first!**

2. **Run via phpMyAdmin:**
   - Go to phpMyAdmin
   - Select your database
   - Go to SQL tab
   - Copy and paste contents of each SQL file
   - Run them in order: 6100.sql, 6200.sql, 6300.sql

3. **Or via command line:**
   ```bash
   mysql -u username -p database_name < product/update/sql/6100.sql
   mysql -u username -p database_name < product/update/sql/6200.sql
   mysql -u username -p database_name < product/update/sql/6300.sql
   ```

---

## Verification

After running migrations, verify the changes:

```sql
-- Check version
SELECT `value` FROM `settings` WHERE `key` = 'product_info';
-- Should show: {"version":"63.0.0","code":"6300"}

-- Check payment_processors table exists
SHOW TABLES LIKE 'payment_processors';

-- Check codes table has new columns
DESCRIBE `codes`;
-- Should show: start_date, end_date

-- Check payments table has new columns
DESCRIBE `payments`;
-- Should show: status, refunds
```

---

## Important Notes

1. **Backup First**: Always backup your database before running migrations

2. **Error Handling**: 
   - Queries marked with `-- X --` won't stop execution if they fail
   - This is intentional for optional columns that might already exist

3. **Extended License**: 
   - The migration system supports Extended License queries
   - If you have Extended License, additional queries may run

4. **Testing**: 
   - Test payment processor creation after migration
   - Test credit notes functionality
   - Verify discount codes work with start/end dates

---

## What These Migrations Do

### Payment Processors Table
- Allows users to create and manage their own payment processor configurations
- Used by the Payment Blocks plugin
- Supports multiple payment gateways per user

### Codes Table Updates
- `start_date`: When discount code becomes active
- `end_date`: When discount code expires
- Allows time-based discount codes

### Payments Table Updates
- `status`: Payment status (completed, refunded, partially_refunded, chargeback)
- `refunds`: JSON array of refund/chargeback information
- Enables credit notes and refund tracking

---

## Status

✅ **SQL migration files created**
✅ **info.php updated**
✅ **Ready to run migrations**

**Next Step**: Access `/update/` page or run SQL files manually to update your database.


