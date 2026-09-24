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
            <?php if(!empty(settings()->main->{'logo_' . \Altum\ThemeStyle::get()}) && !empty(settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'})): ?>
                <img
                    src="<?= settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'} ?>"
                    class="navbar-logo"
                    alt="<?= l('global.accessibility.logo_alt') ?>"
                    width="150"
                    height="48"
                    style="width:150px!important;max-width:150px!important;height:auto!important;max-height:48px!important;display:block!important;object-fit:contain!important;background:#111827;padding:4px;border-radius:6px;"
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
