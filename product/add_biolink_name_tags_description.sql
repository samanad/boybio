-- Biolink name, description, and tags for list/search (run once)
ALTER TABLE `links`
  ADD COLUMN `name` varchar(128) DEFAULT NULL AFTER `url`,
  ADD COLUMN `description` varchar(512) DEFAULT NULL AFTER `name`,
  ADD COLUMN `tags` text DEFAULT NULL AFTER `description`;
