UPDATE `settings` SET `value` = '{"version":"60.1.0","code":"6100"}' WHERE `key` = 'product_info';

-- SEPARATOR --

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

-- SEPARATOR --

-- X -- ALTER TABLE `codes` ADD COLUMN `start_date` datetime NULL;

-- SEPARATOR --

-- X -- ALTER TABLE `codes` ADD COLUMN `end_date` datetime NULL;

-- SEPARATOR --

-- X -- ALTER TABLE `payments` ADD COLUMN `status` varchar(32) NOT NULL DEFAULT 'completed';

-- SEPARATOR --

-- X -- ALTER TABLE `payments` ADD COLUMN `refunds` longtext NULL;

-- SEPARATOR --

-- X -- ALTER TABLE `payments` ADD COLUMN `refunded_total` decimal(12,2) NOT NULL DEFAULT '0.00';

-- SEPARATOR --

-- X -- ALTER TABLE `payments` ADD COLUMN `refunded_status` varchar(32) NULL;

-- SEPARATOR --

-- X -- UPDATE `payments` SET `refunds` = '[]' WHERE `refunds` IS NULL OR `refunds` = '';

-- SEPARATOR --

-- X -- UPDATE `payments` SET `status` = 'completed' WHERE `status` IS NULL OR `status` = '';

-- SEPARATOR --

-- X -- ALTER TABLE `links` ADD COLUMN `is_explore_things` tinyint DEFAULT 0 NULL;

-- SEPARATOR --

-- X -- INSERT INTO `settings` (`key`, `value`) VALUES ('paddle_billing', '{"is_enabled":false,"mode":"sandbox","api_key":"","secret_key":"","client_side_token":"","currencies":["USD"]}') ON DUPLICATE KEY UPDATE `key` = `key`;

-- SEPARATOR --

-- X -- INSERT INTO `settings` (`key`, `value`) VALUES ('klarna', '{"is_enabled":false,"mode":"https://api.klarna.com","username":"","password":"","currencies":["USD"]}') ON DUPLICATE KEY UPDATE `key` = `key`;

-- SEPARATOR --

-- X -- INSERT INTO `settings` (`key`, `value`) VALUES ('plisio', '{"is_enabled":false,"secret_key":"","accepted_cryptocurrencies":[],"default_cryptocurrency":"BTC","currencies":["USD"]}') ON DUPLICATE KEY UPDATE `key` = `key`;

-- SEPARATOR --

-- X -- INSERT INTO `settings` (`key`, `value`) VALUES ('plisio_whitelabel', '{"is_enabled":false,"secret_key":"","accepted_cryptocurrencies":[],"default_cryptocurrency":"BTC","payment_blocks_fee":0,"currencies":["USD"]}') ON DUPLICATE KEY UPDATE `key` = `key`;

-- SEPARATOR --

-- X -- INSERT INTO `settings` (`key`, `value`) VALUES ('revolut', '{"is_enabled":false,"mode":"sandbox","secret_key":"","webhook_id":"","currencies":["USD"]}') ON DUPLICATE KEY UPDATE `key` = `key`;
