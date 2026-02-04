-- Update Site URLs in Database
-- Replace 'https://oldurl.com/' with your OLD URL
-- Replace 'https://newurl.com/' with your NEW URL

-- Update main settings URLs
UPDATE `settings` SET `value` = JSON_SET(
    `value`,
    '$.index_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.index_url')), 'https://oldurl.com/', 'https://newurl.com/'),
    '$.not_found_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.not_found_url')), 'https://oldurl.com/', 'https://newurl.com/'),
    '$.terms_and_conditions_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.terms_and_conditions_url')), 'https://oldurl.com/', 'https://newurl.com/'),
    '$.privacy_policy_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.privacy_policy_url')), 'https://oldurl.com/', 'https://newurl.com/')
) WHERE `key` = 'main';

-- Update offload URLs (if offload plugin is active)
UPDATE `settings` SET `value` = JSON_SET(
    `value`,
    '$.assets_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.assets_url')), 'https://oldurl.com/', 'https://newurl.com/'),
    '$.uploads_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.uploads_url')), 'https://oldurl.com/', 'https://newurl.com/'),
    '$.cdn_assets_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.cdn_assets_url')), 'https://oldurl.com/', 'https://newurl.com/'),
    '$.cdn_uploads_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.cdn_uploads_url')), 'https://oldurl.com/', 'https://newurl.com/')
) WHERE `key` = 'offload';

-- Update links subdirectory redirect base URL
UPDATE `settings` SET `value` = JSON_SET(
    `value`,
    '$.subdirectory_redirect_base_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.subdirectory_redirect_base_url')), 'https://oldurl.com/', 'https://newurl.com/')
) WHERE `key` = 'links';

-- Update PWA app start URL
UPDATE `settings` SET `value` = JSON_SET(
    `value`,
    '$.app_start_url', REPLACE(JSON_UNQUOTE(JSON_EXTRACT(`value`, '$.app_start_url')), 'https://oldurl.com/', 'https://newurl.com/')
) WHERE `key` = 'pwa';

-- Note: SSO website URLs need to be updated manually via Admin Panel
-- as they are stored in a nested array structure






