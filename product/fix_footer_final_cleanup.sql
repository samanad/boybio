-- Final cleanup: Remove duplicates and ensure correct pages exist

-- Step 1: Delete all duplicate footer pages, keep only the first two (IDs 6 and 7)
DELETE FROM `pages` 
WHERE `position` = 'bottom' 
AND `page_id` NOT IN (6, 7)
AND (`url` = 'https://biolink.dev' OR `url` = 'https://saman.host');

-- Step 2: If pages 6 and 7 don't exist, create them
INSERT INTO `pages` 
(`page_id`, `pages_category_id`, `url`, `title`, `description`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) 
SELECT 6, NULL, 'https://saman.host', 'from saman', '', '', 'external', 'bottom', NULL, 1, 1, 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `pages` WHERE `page_id` = 6);

INSERT INTO `pages` 
(`page_id`, `pages_category_id`, `url`, `title`, `description`, `content`, `type`, `position`, `language`, `open_in_new_tab`, `order`, `total_views`, `is_published`, `datetime`, `last_datetime`) 
SELECT 7, NULL, 'https://biolink.dev', 'built with love', '', '', 'external', 'bottom', NULL, 1, 0, 0, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM `pages` WHERE `page_id` = 7);

-- Step 3: Update pages 6 and 7 to ensure they're correct
UPDATE `pages` 
SET 
    `url` = 'https://saman.host',
    `title` = 'from saman',
    `is_published` = 1,
    `order` = 1,
    `last_datetime` = NOW()
WHERE `page_id` = 6;

UPDATE `pages` 
SET 
    `url` = 'https://biolink.dev',
    `title` = 'built with love',
    `is_published` = 1,
    `order` = 0,
    `last_datetime` = NOW()
WHERE `page_id` = 7;

-- Step 4: Verify final state
SELECT 'Final footer pages:' AS step;
SELECT page_id, url, title, is_published, `order` 
FROM pages 
WHERE position = 'bottom' 
ORDER BY `order` ASC;

SELECT '✅ Cleanup complete! Now clear cache: https://yourdomain.com/clear_cache.php' AS result;
















