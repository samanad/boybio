-- Clear the not_found_url setting to stop redirecting to old URL
UPDATE `settings` 
SET `value` = JSON_SET(`value`, '$.not_found_url', '') 
WHERE `key` = 'main';






