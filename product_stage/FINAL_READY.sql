-- =============================================================================
-- FINAL READY SQL for product_stage (selective payment/plan cherry-pick)
-- Safe to run once. Re-runnable: skips existing tables/columns/settings.
-- Does NOT claim official Altum v63. Marks product as 60.1.0 / code 6100.
-- Run on the database this site uses (phpMyAdmin or mysql CLI), then clear cache.
-- =============================================================================

-- Helper: add column only if missing
DROP PROCEDURE IF EXISTS `altum_add_column_if_missing`;

DELIMITER $$
CREATE PROCEDURE `altum_add_column_if_missing`(
    IN p_table VARCHAR(64),
    IN p_column VARCHAR(64),
    IN p_definition VARCHAR(512)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM `information_schema`.`COLUMNS`
        WHERE `TABLE_SCHEMA` = DATABASE()
          AND `TABLE_NAME` = p_table
          AND `COLUMN_NAME` = p_column
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_table, '` ADD COLUMN `', p_column, '` ', p_definition);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

-- =============================================================================
-- 1) payment_processors table (user biolink payment processors)
-- =============================================================================
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 2) codes date window
-- =============================================================================
CALL `altum_add_column_if_missing`('codes', 'start_date', 'datetime NULL');
CALL `altum_add_column_if_missing`('codes', 'end_date', 'datetime NULL');

-- =============================================================================
-- 3) payments status / refunds / credit-notes support
-- =============================================================================
CALL `altum_add_column_if_missing`('payments', 'status', "varchar(32) NOT NULL DEFAULT 'completed'");
CALL `altum_add_column_if_missing`('payments', 'refunds', 'longtext NULL');
CALL `altum_add_column_if_missing`('payments', 'refunded_total', "decimal(12,2) NOT NULL DEFAULT '0.00'");
CALL `altum_add_column_if_missing`('payments', 'refunded_status', 'varchar(32) NULL');

UPDATE `payments`
SET `refunds` = '[]'
WHERE `refunds` IS NULL OR `refunds` = '';

UPDATE `payments`
SET `status` = 'completed'
WHERE `status` IS NULL OR `status` = '';

-- =============================================================================
-- 4) Custom site column (explore) — keep if already present
-- =============================================================================
CALL `altum_add_column_if_missing`('links', 'is_explore_things', 'tinyint DEFAULT 0 NULL');

-- =============================================================================
-- 5) Disabled defaults for NEW platform gateways (required so pay page does not 500)
-- INSERT IGNORE relies on UNIQUE KEY `key` on settings
-- =============================================================================
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
('paddle_billing', '{"is_enabled":false,"mode":"sandbox","api_key":"","secret_key":"","client_side_token":"","currencies":["USD"]}'),
('klarna', '{"is_enabled":false,"mode":"https://api.klarna.com","username":"","password":"","currencies":["USD"]}'),
('plisio', '{"is_enabled":false,"secret_key":"","accepted_cryptocurrencies":[],"default_cryptocurrency":"BTC","currencies":["USD"]}'),
('plisio_whitelabel', '{"is_enabled":false,"secret_key":"","accepted_cryptocurrencies":[],"default_cryptocurrency":"BTC","payment_blocks_fee":0,"currencies":["USD"]}'),
('revolut', '{"is_enabled":false,"mode":"sandbox","secret_key":"","webhook_id":"","currencies":["USD"]}');

-- =============================================================================
-- 6) Mark cherry-pick applied (not official v63)
-- =============================================================================
UPDATE `settings`
SET `value` = '{"version":"60.1.0","code":"6100"}'
WHERE `key` = 'product_info';

-- Cleanup helper
DROP PROCEDURE IF EXISTS `altum_add_column_if_missing`;

-- Done.
-- After running: clear app cache (uploads/cache / language cache) and reload admin settings.
