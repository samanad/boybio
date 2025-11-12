-- ============================================
-- Update Footer Pages
-- Replaces the default branding links with custom ones
-- ============================================

-- Update "Built with 66biolinks" to "built with love"
UPDATE `pages` 
SET 
    `url` = 'https://biolink.dev',
    `title` = 'built with love',
    `last_datetime` = NOW()
WHERE `url` = 'https://altumco.de/66biolinks' 
AND `position` = 'bottom';

-- Update "Software by AltumCode" to "from saman"
UPDATE `pages` 
SET 
    `url` = 'https://saman.host',
    `title` = 'from saman',
    `last_datetime` = NOW()
WHERE `url` = 'https://altumcode.com/' 
AND `position` = 'bottom';

-- Show updated footer pages
SELECT page_id, url, title, position, is_published, `order`
FROM pages 
WHERE position = 'bottom' 
ORDER BY `order` ASC;

-- Clear pages cache by updating a dummy setting (forces cache refresh)
-- The cache will be automatically cleared on next page load, but you can also:
-- 1. Visit: https://yourdomain.com/clear_cache.php
-- 2. Or delete: uploads/cache/* files

SELECT 'Footer pages updated successfully! IMPORTANT: Clear cache after running this script!' AS result;

