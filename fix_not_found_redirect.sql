-- Fix not_found_url redirect issue after changing SITE_URL
-- This will clear the not_found_url setting so it doesn't redirect to the old URL

-- Option 1: Clear the not_found_url (recommended - will show default 404 page)
UPDATE `settings` 
SET `value` = JSON_SET(`value`, '$.not_found_url', '') 
WHERE `key` = 'main';

-- Option 2: Update not_found_url to new domain (if you want to keep redirecting)
-- Replace 'https://oldurl.com/404' with your old URL
-- Replace 'https://newurl.com/404' with your new URL
-- UPDATE `settings` 
-- SET `value` = JSON_SET(`value`, '$.not_found_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.not_found_url')), 'https://oldurl.com/', 'https://newurl.com/')) 
-- WHERE `key` = 'main';






