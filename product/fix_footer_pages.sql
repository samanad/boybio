-- ============================================
-- Fix Footer Pages Display
-- This script ensures pages are enabled and clears cache
-- ============================================

-- 1. Ensure content setting exists and pages_is_enabled is set
INSERT INTO `settings` (`key`, `value`) 
SELECT 'content', '{"blog_is_enabled":false,"pages_is_enabled":true,"pages_share_is_enabled":true,"pages_popular_widget_is_enabled":true,"pages_views_is_enabled":true}'
WHERE NOT EXISTS (SELECT 1 FROM `settings` WHERE `key` = 'content');

-- 2. Update content setting to enable pages if it exists but pages_is_enabled is missing or false
UPDATE `settings` 
SET `value` = JSON_SET(
  `value`,
  '$.pages_is_enabled', COALESCE(JSON_EXTRACT(`value`, '$.pages_is_enabled'), true)
)
WHERE `key` = 'content' 
AND (JSON_EXTRACT(`value`, '$.pages_is_enabled') IS NULL OR JSON_EXTRACT(`value`, '$.pages_is_enabled') = false);

-- 3. Verify the pages exist and are published
UPDATE `pages` 
SET `is_published` = 1, `position` = 'bottom'
WHERE `url` IN ('https://altumcode.com/', 'https://altumco.de/66biolinks')
AND `position` = 'bottom';

-- 4. Show current footer pages
SELECT page_id, url, title, position, is_published, `order`
FROM pages 
WHERE position = 'bottom' 
ORDER BY `order` ASC;

SELECT 'Footer pages fixed! IMPORTANT: Clear cache after running this script!' AS result;



















