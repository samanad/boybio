<?php defined('ALTUMCODE') || die() ?>

<?php
$logo_light_embed = function_exists('get_main_logo_embed') ? get_main_logo_embed('light') : (settings()->main->logo_light_full_url ?? '');
$logo_dark_embed = function_exists('get_main_logo_embed') ? get_main_logo_embed('dark') : (settings()->main->logo_dark_full_url ?? '');
$logo_theme = \Altum\ThemeStyle::get();
$logo_src = $logo_theme === 'dark'
    ? ($logo_dark_embed !== '' ? $logo_dark_embed : $logo_light_embed)
    : ($logo_light_embed !== '' ? $logo_light_embed : $logo_dark_embed);
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border border-gray-100 mt-4 index-highly-rounded d-lg-none">
    <div class="container">
        <a
            href="<?= url() ?>"
            class="navbar-brand d-flex cloub-logo-sun cloub-logo-sun--sm"
            data-logo
            data-light-value="<?= $logo_light_embed !== '' ? $logo_light_embed : settings()->main->title ?>"
            data-light-class="<?= $logo_light_embed !== '' ? 'img-fluid navbar-logo' : '' ?>"
            data-light-tag="<?= $logo_light_embed !== '' ? 'img' : 'span' ?>"
            data-dark-value="<?= $logo_dark_embed !== '' ? $logo_dark_embed : settings()->main->title ?>"
            data-dark-class="<?= $logo_dark_embed !== '' ? 'img-fluid navbar-logo' : '' ?>"
            data-dark-tag="<?= $logo_dark_embed !== '' ? 'img' : 'span' ?>"
        >
            <?php if($logo_src !== ''): ?>
                <img
                    src="<?= $logo_src ?>"
                    class="navbar-logo"
                    alt="<?= l('global.accessibility.logo_alt') ?>"
                    width="70"
                    height="48"
                />
            <?php else: ?>
                <?= settings()->main->title ?>
            <?php endif ?>
        </a>

        <button class="btn navbar-custom-toggler d-lg-none" type="button" id="app_menu_toggler" aria-controls="main_navbar" aria-expanded="false" aria-label="<?= l('global.accessibility.toggle_navigation') ?>">
            <i class="fas fa-fw fa-bars"></i>
        </button>
    </div>
</nav>
