-- Update main settings subdirectory_redirect_base_url from linkdooni.com to dumein.com
UPDATE `settings` 
SET `value` = JSON_SET(
    `value`, 
    '$.subdirectory_redirect_base_url', 
    'https://dumein.com'
)
WHERE `key` = 'main' 
AND JSON_EXTRACT(`value`, '$.subdirectory_redirect_base_url') = 'https://linkdooni.com';

