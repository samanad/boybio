<?php defined('ALTUMCODE') || die(); ?>
<?php
/* Safe for PHP 8+: avoid Undefined array key "HTTP_USER_AGENT" (e.g. CLI or some proxies) */
$_ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
if ($_ua === '' || (strpos($_ua, 'bot') !== false || strpos($_ua, 'crawl') !== false)) {
    return;
}
$pwa_custom = __DIR__ . '/pwa_custom.php';
if (file_exists($pwa_custom)) {
    require $pwa_custom;
}
