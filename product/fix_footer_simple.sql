-- Simple direct fix for footer pages
-- Run this in phpMyAdmin

-- First, make sure pages exist (delete old ones first)
DELETE FROM `pages` WHERE `position` = 'bottom' AND (`url` LIKE '%altumcode%' OR `url` LIKE '%altumco.de%' OR `url` LIKE '%66biolinks%');

-- Insert the two footer pages
INSERT INTO `pages` (`pages_category_id`, `url`, `title`, `description`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) 
VALUES 
(NULL, 'https://biolink.dev', 'built with love', '', '', 'external', 'bottom', NULL, 1, 0, 0, 1, NOW(), NOW()),
(NULL, 'https://saman.host', 'from saman', '', '', 'external', 'bottom', NULL, 1, 1, 0, 1, NOW(), NOW());

-- Make sure they're published
UPDATE `pages` SET `is_published` = 1 WHERE `position` = 'bottom';

-- Show what we have
SELECT page_id, url, title, is_published FROM pages WHERE position = 'bottom' ORDER BY `order` ASC;











