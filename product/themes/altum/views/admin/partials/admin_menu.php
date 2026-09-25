<?php defined('ALTUMCODE') || die() ?>

<?php
$admin_logo_light = function_exists('get_main_logo_embed') ? get_main_logo_embed('light') : (settings()->main->logo_light_full_url ?? '');
$admin_logo_dark = function_exists('get_main_logo_embed') ? get_main_logo_embed('dark') : (settings()->main->logo_dark_full_url ?? '');
$admin_logo_has_light = $admin_logo_light !== '';
$admin_logo_has_dark = $admin_logo_dark !== '';
$admin_logo_theme = \Altum\ThemeStyle::get();
$admin_logo_src = $admin_logo_theme === 'dark'
    ? ($admin_logo_has_dark ? $admin_logo_dark : $admin_logo_light)
    : ($admin_logo_has_light ? $admin_logo_light : $admin_logo_dark);
?>

<div class="p-3 mt-3 p-lg-0 mt-lg-0">
    <nav class="navbar navbar-expand-lg navbar-light rounded admin-navbar-top">
        <div
            class="navbar-brand text-truncate cloub-logo-sun cloub-logo-sun--sm"
            data-logo
            data-light-value="<?= $admin_logo_has_light ? $admin_logo_light : settings()->main->title ?>"
            data-light-class="<?= $admin_logo_has_light ? 'img-fluid admin-navbar-logo-top' : '' ?>"
            data-light-tag="<?= $admin_logo_has_light ? 'img' : 'span' ?>"
            data-dark-value="<?= $admin_logo_has_dark ? $admin_logo_dark : settings()->main->title ?>"
            data-dark-class="<?= $admin_logo_has_dark ? 'img-fluid admin-navbar-logo-top' : '' ?>"
            data-dark-tag="<?= $admin_logo_has_dark ? 'img' : 'span' ?>"
            
            id="sidebar_title"
            tabindex="0"
            data-toggle="tooltip"
            data-placement="right"
            data-html="true"
            data-trigger="hover"
            data-delay='{ "hide": 5500 }'
            title="
            <div class='d-flex text-left flex-column'>
                <div class='mb-2'><a href='<?= url() ?>' class='text-gray-50 text-decoration-none'>🌐 &nbsp; <?= l('index.menu') ?></a></div>
                <div><a href='<?= url('dashboard') ?>' class='text-gray-50 text-decoration-none'>🖥️ &nbsp; <?= l('dashboard.menu') ?></a></div>
            </div>
            "
        >
            <?php if($admin_logo_src !== ''): ?>
                <img src="<?= $admin_logo_src ?>" class="img-fluid admin-navbar-logo-top" alt="<?= l('global.accessibility.logo_alt') ?>" />
            <?php else: ?>
                <span><?= settings()->main->title ?></span>
            <?php endif ?>
        </div>

        <ul class="navbar-nav ml-auto">
            <button class="btn navbar-custom-toggler" type="button" id="admin_menu_toggler" aria-controls="main_navbar" aria-expanded="false" aria-label="<?= l('global.accessibility.toggle_navigation') ?>">
                <i class="fas fa-fw fa-bars"></i>
            </button>
        </ul>
    </nav>
</div>
