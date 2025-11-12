-- Complete fix for footer pages
-- This ensures pages exist AND the pages feature is enabled

-- Step 1: Enable pages feature in settings
-- First, ensure content setting exists
INSERT INTO `settings` (`key`, `value`) 
SELECT 'content', '{"pages_is_enabled":1,"blog_is_enabled":1}'
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key` = 'content');

-- Now update it to enable pages
UPDATE `settings` 
SET `value` = JSON_SET(
    COALESCE(NULLIF(`value`, ''), '{}'),
    '$.pages_is_enabled', 1
)
WHERE `key` = 'content';

-- Step 2: Delete old footer pages
DELETE FROM `pages` 
WHERE `position` = 'bottom' 
AND (`url` LIKE '%altumcode%' OR `url` LIKE '%altumco.de%' OR `url` LIKE '%66biolinks%');

-- Step 3: Insert new footer pages
INSERT INTO `pages` 
(`pages_category_id`, `url`, `title`, `description`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) 
VALUES 
(NULL, 'https://biolink.dev', 'built with love', '', '', 'external', 'bottom', NULL, 1, 0, 0, 1, NOW(), NOW()),
(NULL, 'https://saman.host', 'from saman', '', '', 'external', 'bottom', NULL, 1, 1, 0, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `url` = VALUES(`url`),
    `title` = VALUES(`title`),
    `is_published` = 1,
    `last_datetime` = NOW();

-- Step 4: Ensure all footer pages are published
UPDATE `pages` 
SET `is_published` = 1 
WHERE `position` = 'bottom';

-- Step 5: Verify
SELECT 'Settings check:' AS step;
SELECT `key`, JSON_EXTRACT(`value`, '$.pages_is_enabled') AS pages_is_enabled 
FROM `settings` 
WHERE `key` = 'content';

SELECT 'Footer pages check:' AS step;
SELECT page_id, url, title, is_published, `order` 
FROM pages 
WHERE position = 'bottom' 
ORDER BY `order` ASC;

SELECT '✅ Done! Now clear cache: https://yourdomain.com/clear_cache.php' AS result;

