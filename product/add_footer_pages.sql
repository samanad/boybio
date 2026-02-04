-- ============================================
-- Add Footer Pages to Database
-- This script adds the default AltumCode and 66biolinks footer links
-- so they can be edited/removed via Admin Panel > Pages
-- ============================================

-- Add "Software by AltumCode" link if it doesn't exist
INSERT INTO `pages` (`pages_category_id`, `url`, `title`, `description`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) 
SELECT NULL, 'https://altumcode.com/', 'Software by AltumCode', '', '', 'external', 'bottom', NULL, 1, 1, 0, 1, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `pages` 
    WHERE `url` = 'https://altumcode.com/' AND `position` = 'bottom'
);

-- Add "Built with 66biolinks" link if it doesn't exist
INSERT INTO `pages` (`pages_category_id`, `url`, `title`, `description`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) 
SELECT NULL, 'https://altumco.de/66biolinks', 'Built with 66biolinks', '', '', 'external', 'bottom', NULL, 1, 0, 0, 1, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `pages` 
    WHERE `url` = 'https://altumco.de/66biolinks' AND `position` = 'bottom'
);

-- Show current footer pages
SELECT page_id, url, title, position, is_published, `order`
FROM pages 
WHERE position = 'bottom' 
ORDER BY `order` ASC;

SELECT 'Footer pages added successfully! You can now edit/delete them via Admin Panel > Pages' AS result;



















