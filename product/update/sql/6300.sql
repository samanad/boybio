UPDATE `settings` SET `value` = '{\"version\":\"63.0.0\", \"code\":\"6300\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

-- X -- ALTER TABLE `links` ADD COLUMN `page_language` varchar(8) NULL AFTER `seo_keywords` IF NOT EXISTS;

-- SEPARATOR --

-- X -- ALTER TABLE `biolinks_themes` ADD COLUMN `auto_apply_settings` tinyint(1) DEFAULT 0 AFTER `settings` IF NOT EXISTS;


