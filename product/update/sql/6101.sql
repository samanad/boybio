-- X -- ALTER TABLE `biolinks_blocks` ADD `is_pinned` tinyint(4) NOT NULL DEFAULT '0' AFTER `is_enabled`;
-- SEPARATOR --
-- X -- ALTER TABLE `biolinks_blocks` ADD INDEX `biolinks_blocks_is_pinned_index` (`is_pinned`);
-- SEPARATOR --
UPDATE `settings` SET `value` = '{"version":"60.1.1","code":"6101"}' WHERE `key` = 'product_info';
