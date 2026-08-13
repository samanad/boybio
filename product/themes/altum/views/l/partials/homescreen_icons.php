<?php defined('ALTUMCODE') || die() ?>
<?php
/* Home-screen / "Add to App" icons for obscure browsers that ignore rel=icon and need apple-touch-icon / manifest icons. */
$homescreen_icon_url = null;
$homescreen_icon_sizes = '192x192';
$pwa_settings = settings()->pwa ?? null;

if(!empty($this->link->settings->pwa_icon)) {
    $homescreen_icon_url = \Altum\Uploads::get_full_url('app_icon') . $this->link->settings->pwa_icon;
    $homescreen_icon_sizes = '512x512';
} elseif(!empty($this->link->settings->favicon)) {
    $homescreen_icon_url = \Altum\Uploads::get_full_url('favicons') . $this->link->settings->favicon;
} elseif(!empty($this->link->settings->seo->image ?? null)) {
    $homescreen_icon_url = \Altum\Uploads::get_full_url('block_images') . $this->link->settings->seo->image;
} elseif(!empty($pwa_settings->app_icon)) {
    $homescreen_icon_url = \Altum\Uploads::get_full_url('app_icon') . $pwa_settings->app_icon;
    $homescreen_icon_sizes = '512x512';
} elseif(!empty(settings()->main->favicon)) {
    $homescreen_icon_url = settings()->main->favicon_full_url;
}

$homescreen_app_name = trim((string) ($this->link->settings->seo->title ?? '')) ?: ($this->link->url ?? settings()->main->title);
$homescreen_theme_color = !empty($this->link->settings->pwa_theme_color) && verify_hex_color($this->link->settings->pwa_theme_color)
    ? $this->link->settings->pwa_theme_color
    : (!empty($pwa_settings->theme_color) ? $pwa_settings->theme_color : '#000000');

/* Cache-bust when the stored filename changes */
if($homescreen_icon_url) {
    $homescreen_icon_url .= (str_contains($homescreen_icon_url, '?') ? '&' : '?') . 'v=' . substr(md5(basename(parse_url($homescreen_icon_url, PHP_URL_PATH) ?: $homescreen_icon_url)), 0, 10);
}

$has_custom_pwa_manifest = \Altum\Plugin::is_active('pwa')
    && !empty($pwa_settings->is_enabled)
    && ($this->user->plan_settings->custom_pwa_is_enabled ?? false)
    && !empty($this->link->settings->pwa_is_enabled)
    && !empty($this->link->settings->pwa_file_name);

$homescreen_manifest_url = null;
if($has_custom_pwa_manifest) {
    $homescreen_manifest_url = SITE_URL . UPLOADS_URL_PATH . \Altum\Uploads::get_path('pwa') . $this->link->settings->pwa_file_name . '.json';
} elseif($homescreen_icon_url) {
    $homescreen_manifest_url = $this->link->full_url . (str_contains($this->link->full_url, '?') ? '&' : '?') . 'homescreen_manifest=1';
} elseif(\Altum\Plugin::is_active('pwa') && !empty($pwa_settings->is_enabled)) {
    $homescreen_manifest_url = SITE_URL . UPLOADS_URL_PATH . \Altum\Uploads::get_path('pwa') . 'manifest.json';
}
?>

<?php if($homescreen_manifest_url): ?>
    <link rel="manifest" href="<?= $homescreen_manifest_url ?>" />
<?php endif ?>

<meta name="theme-color" content="<?= $homescreen_theme_color ?>" />
<meta name="mobile-web-app-capable" content="yes" />
<meta name="application-name" content="<?= e($homescreen_app_name) ?>" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
<meta name="apple-mobile-web-app-title" content="<?= e($homescreen_app_name) ?>" />

<?php if($homescreen_icon_url): ?>
    <link rel="apple-touch-icon" href="<?= $homescreen_icon_url ?>" sizes="<?= $homescreen_icon_sizes ?>" />
    <link rel="apple-touch-icon" href="<?= $homescreen_icon_url ?>" />
    <link rel="icon" type="image/png" href="<?= $homescreen_icon_url ?>" sizes="<?= $homescreen_icon_sizes ?>" />
    <link rel="shortcut icon" href="<?= $homescreen_icon_url ?>" />
<?php endif ?>
