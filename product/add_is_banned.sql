-- Add banned badge column for biolinks (run once on your database)
ALTER TABLE `links` ADD COLUMN `is_banned` tinyint DEFAULT 0 NULL AFTER `is_verified`;
