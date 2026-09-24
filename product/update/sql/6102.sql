-- X -- ALTER TABLE `biolinks_blocks` ADD `is_sticky` tinyint(4) NOT NULL DEFAULT '0' AFTER `is_pinned`;
-- SEPARATOR --
UPDATE `settings` SET `value` = '{"version":"60.1.2","code":"6102"}' WHERE `key` = 'product_info';
