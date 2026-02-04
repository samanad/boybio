-- Complete fix for footer links
-- This script will ensure the "Built with 66biolinks" and "Software by AltumCode" pages exist and are updated

-- Step 1: Check current state
SELECT 'Current footer pages:' AS step;
SELECT page_id, url, title, position, is_published, `order` 
FROM pages 
WHERE position = 'bottom' 
ORDER BY `order` ASC;

-- Step 2: Delete old entries if they exist with wrong URLs
DELETE FROM pages 
WHERE position = 'bottom' 
AND (
    (url = 'https://altumcode.com/' AND title = 'Software by AltumCode')
    OR (url = 'https://altumco.de/66biolinks' AND title = 'Built with 66biolinks')
);

-- Step 3: Insert or update "built with love" page
INSERT INTO `pages` (`pages_category_id`, `url`, `title`, `description`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) 
VALUES (NULL, 'https://biolink.dev', 'built with love', '', '', 'external', 'bottom', NULL, 1, 0, 0, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `url` = 'https://biolink.dev',
    `title` = 'built with love',
    `last_datetime` = NOW();

-- Step 4: Insert or update "from saman" page  
INSERT INTO `pages` (`pages_category_id`, `url`, `title`, `description`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) 
VALUES (NULL, 'https://saman.host', 'from saman', '', '', 'external', 'bottom', NULL, 1, 1, 0, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `url` = 'https://saman.host',
    `title` = 'from saman',
    `last_datetime` = NOW();

-- Step 5: Update existing pages if they have the old URLs
UPDATE `pages` 
SET 
    `url` = 'https://biolink.dev',
    `title` = 'built with love',
    `last_datetime` = NOW()
WHERE `url` = 'https://altumco.de/66biolinks' 
AND `position` = 'bottom';

UPDATE `pages` 
SET 
    `url` = 'https://saman.host',
    `title` = 'from saman',
    `last_datetime` = NOW()
WHERE `url` = 'https://altumcode.com/' 
AND `position` = 'bottom';

-- Step 6: Ensure all footer pages are published
UPDATE `pages` 
SET `is_published` = 1 
WHERE `position` = 'bottom' 
AND `is_published` = 0;

-- Step 7: Verify final state
SELECT 'Final footer pages:' AS step;
SELECT page_id, url, title, position, is_published, `order` 
FROM pages 
WHERE position = 'bottom' 
ORDER BY `order` ASC;

SELECT '✅ Footer pages updated! Now clear your cache using clear_cache.php' AS result;



















