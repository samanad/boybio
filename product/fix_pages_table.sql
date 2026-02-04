-- Fix pages table schema issues
-- This script ensures the pages table has all required columns

-- Check and add 'order' column if it doesn't exist
SET @dbname = DATABASE();
SET @tablename = "pages";
SET @columnname = "order";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column order already exists in pages' AS result;",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " int DEFAULT 0 AFTER `position`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add 'is_published' column if it doesn't exist
SET @columnname = "is_published";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column is_published already exists in pages' AS result;",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " tinyint DEFAULT 1 AFTER `total_views`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add 'open_in_new_tab' column if it doesn't exist
SET @columnname = "open_in_new_tab";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 'Column open_in_new_tab already exists in pages' AS result;",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " tinyint DEFAULT 1 AFTER `language`;")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update any NULL values in order column to 0
UPDATE `pages` SET `order` = 0 WHERE `order` IS NULL;

-- Update any NULL values in is_published column to 1
UPDATE `pages` SET `is_published` = 1 WHERE `is_published` IS NULL;

-- Verify the table structure
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'pages'
ORDER BY ORDINAL_POSITION;

SELECT 'Pages table structure verified and fixed!' AS result;



















