UPDATE `settings` SET `value` = '{\"version\":\"61.0.0\", \"code\":\"6100\"}' WHERE `key` = 'product_info';

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
  KEY `user_id` (`user_id`),
  CONSTRAINT `payment_processors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SEPARATOR --

ALTER TABLE `codes` 
ADD COLUMN `start_date` datetime NULL AFTER `datetime`,
ADD COLUMN `end_date` datetime NULL AFTER `start_date`;

-- SEPARATOR --

ALTER TABLE `payments` 
ADD COLUMN `status` varchar(32) DEFAULT 'completed' AFTER `type`,
ADD COLUMN `refunds` text NULL AFTER `business`;

-- SEPARATOR --

UPDATE `payments` SET `refunds` = '[]' WHERE `refunds` IS NULL;

-- SEPARATOR --

-- X -- ALTER TABLE `links` ADD COLUMN `is_explore_things` tinyint DEFAULT 0 NULL AFTER `directory_is_enabled`;
