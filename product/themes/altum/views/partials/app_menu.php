<?php defined('ALTUMCODE') || die() ?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border border-gray-100 mt-4 index-highly-rounded d-lg-none">
    <div class="container">
        <a
            href="<?= url() ?>"
            class="navbar-brand d-flex"
            data-logo
            data-light-value="<?= !empty(settings()->main->logo_light) ? settings()->main->logo_light_full_url : settings()->main->title ?>"
            data-light-class="<?= !empty(settings()->main->logo_light) ? 'img-fluid navbar-logo' : '' ?>"
            data-light-tag="<?= !empty(settings()->main->logo_light) ? 'img' : 'span' ?>"
            data-dark-value="<?= !empty(settings()->main->logo_dark) ? settings()->main->logo_dark_full_url : settings()->main->title ?>"
            data-dark-class="<?= !empty(settings()->main->logo_dark) ? 'img-fluid navbar-logo' : '' ?>"
            data-dark-tag="<?= !empty(settings()->main->logo_dark) ? 'img' : 'span' ?>"
        >
            <?php
            $logo_theme = \Altum\ThemeStyle::get();
            $logo_src = function_exists('get_main_logo_data_uri') ? get_main_logo_data_uri($logo_theme) : '';
            if($logo_src === '') {
                $logo_src = settings()->main->{'logo_' . $logo_theme . '_full_url'} ?? '';
            }
            ?>
            <?php if($logo_src !== ''): ?>
                <img
                    src="<?= $logo_src ?>"
                    class="navbar-logo"
                    alt="<?= l('global.accessibility.logo_alt') ?>"
                    width="70"
                    height="48"
                    style="width:70px!important;max-width:70px!important;height:auto!important;max-height:48px!important;display:block!important;object-fit:contain!important;padding:6px;border-radius:50%;background:radial-gradient(circle at 50% 50%,rgba(255,252,245,.98) 0 48%,#3a3f4b 55%,#1e2229 100%);box-shadow:0 0 14px 5px rgba(255,250,240,.75),0 0 28px 10px rgba(250,240,220,.35);"
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
