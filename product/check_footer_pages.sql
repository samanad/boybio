-- Check all footer pages in the database
SELECT 
    page_id,
    url,
    title,
    position,
    is_published,
    `order`,
    type
FROM pages 
WHERE position = 'bottom'
ORDER BY `order` ASC;

-- Check if the updated pages exist
SELECT 
    CASE 
        WHEN COUNT(*) = 2 THEN '✅ Both pages found'
        ELSE CONCAT('⚠️ Found ', COUNT(*), ' page(s) instead of 2')
    END AS status,
    GROUP_CONCAT(CONCAT(page_id, ':', title, ' -> ', url) SEPARATOR ' | ') AS pages
FROM pages 
WHERE position = 'bottom' 
AND (
    (url = 'https://biolink.dev' AND title = 'built with love')
    OR (url = 'https://saman.host' AND title = 'from saman')
);

-- Show all pages with position = 'bottom'
SELECT 'All bottom pages:' AS info;
SELECT page_id, url, title, is_published, `order` 
FROM pages 
WHERE position = 'bottom' 
ORDER BY `order` ASC;











