UPDATE `settings` SET `value` = '{\"version\":\"61.0.0\", \"code\":\"6100\"}' WHERE `key` = 'product_info';

-- SEPARATOR --

alter table links add is_explore_things tinyint default 0 null after directory_is_enabled;

