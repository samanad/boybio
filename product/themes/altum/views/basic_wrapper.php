<?php defined('ALTUMCODE') || die() ?>
<!DOCTYPE html>
<html lang="<?= \Altum\Language::$code ?>" dir="<?= l('direction') ?>" class="h-100">
<head>
    <title><?= \Altum\Title::get() ?></title>
    <base href="<?= SITE_URL; ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <?php if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled): ?>
        <meta name="theme-color" content="<?= settings()->pwa->theme_color ?>"/>
        <link rel="manifest" href="<?= SITE_URL . UPLOADS_URL_PATH . \Altum\Uploads::get_path('pwa') . 'manifest.json?v=' . (settings()->pwa->app_start_url ? md5(settings()->pwa->app_start_url) : time()) ?>" />
        <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                var swUrl = '<?= SITE_URL ?>sw.js';
                navigator.serviceWorker.register(swUrl, { scope: '/' }).then(function(registration) {
                    console.log('Service Worker registered successfully:', registration.scope);
                }).catch(function(error) {
                    console.log('Service Worker registration failed:', error);
                });
            });
        }
        </script>
    <?php endif ?>

    <?php if(\Altum\Meta::$description): ?>
        <meta name="description" content="<?= \Altum\Meta::$description ?>" />
    <?php endif ?>
    <?php if(\Altum\Meta::$keywords): ?>
        <meta name="keywords" content="<?= \Altum\Meta::$keywords ?>" />
    <?php endif ?>

    <?php \Altum\Meta::output() ?>

    <?php if(\Altum\Meta::$canonical): ?>
        <link rel="canonical" href="<?= \Altum\Meta::$canonical ?>" />
    <?php endif ?>

    <?php if(\Altum\Meta::$robots): ?>
        <meta name="robots" content="<?= \Altum\Meta::$robots ?>">
    <?php endif ?>

    <link rel="alternate" href="<?= SITE_URL . \Altum\Router::$original_request ?>" hreflang="x-default" />
    <?php if(count(\Altum\Language::$active_languages) > 1): ?>
        <?php foreach(\Altum\Language::$active_languages as $language_name => $language_code): ?>
            <?php if(settings()->main->default_language != $language_name): ?>
                <link rel="alternate" href="<?= SITE_URL . $language_code . '/' . \Altum\Router::$original_request ?>" hreflang="<?= $language_code ?>" />
            <?php endif ?>
        <?php endforeach ?>
    <?php endif ?>

    <?php if(!empty(settings()->main->favicon)): ?>
        <link href="<?= settings()->main->favicon_full_url ?>" rel="icon" />
    <?php endif ?>

    <link href="<?= ASSETS_FULL_URL . 'css/' . \Altum\ThemeStyle::get_file() . '?v=' . PRODUCT_CODE ?>" id="css_theme_style" rel="stylesheet" media="screen,print">
    <?php foreach(['custom.css'] as $file): ?>
        <link href="<?= ASSETS_FULL_URL . 'css/' . $file . '?v=' . PRODUCT_CODE ?>" rel="stylesheet" media="screen">
    <?php endforeach ?>

    <?= \Altum\Event::get_content('head') ?>

        <?php if(is_logged_in() && !user()->plan_settings->export->pdf): ?>
            <style>@media print { body { display: none; } }</style>
        <?php endif ?>

    <?php if(!empty(settings()->custom->head_js)): ?>
        <?= get_settings_custom_head_js() ?>
    <?php endif ?>

    <?php if(!empty(settings()->custom->head_css)): ?>
        <style><?= settings()->custom->head_css ?></style>
    <?php endif ?>
</head>

<body class="<?= l('direction') == 'rtl' ? 'rtl' : null ?> bg-gray-50 <?= in_array(\Altum\Router::$controller_key, ['login', 'register']) ? \Altum\Router::$controller_key . '-background' : null ?> <?= \Altum\ThemeStyle::get() == 'dark' ? 'cc--darkmode' : null ?>" data-theme-style="<?= \Altum\ThemeStyle::get() ?>">
<?php if(!empty(settings()->custom->body_content)): ?>
    <?= settings()->custom->body_content ?>
<?php endif ?>

<?php //ALTUMCODE:DEMO if(DEMO) echo include_view(THEME_PATH . 'views/partials/ac_banner.php', ['demo_url' => 'https://66biolinks.com/demo/', 'product_name' => PRODUCT_NAME, 'product_url' => PRODUCT_URL, 'product_buy_url' => PRODUCT_BUY_URL]) ?>

<?php require THEME_PATH . 'views/partials/announcements.php' ?>
<?php require THEME_PATH . 'views/partials/cookie_consent.php' ?>
<?php if(settings()->main->admin_spotlight_is_enabled || settings()->main->user_spotlight_is_enabled) require THEME_PATH . 'views/partials/spotlight.php' ?>

<main class="altum-animate altum-animate-fill-none altum-animate-fade-in py-6">
    <div class="container">
        <div class="d-flex flex-column align-items-center">
            <div class="col-xs-12 col-md-10 col-lg-7 col-xl-6">

                <div class="mb-5 text-center">
                    <a href="<?= url() ?>" class="text-decoration-none text-dark">
                        <?php if(settings()->main->{'logo_' . \Altum\ThemeStyle::get()} != ''): ?>
                            <img src="<?= settings()->main->{'logo_' . \Altum\ThemeStyle::get() . '_full_url'} ?>" class="img-fluid navbar-logo" alt="<?= l('global.accessibility.logo_alt') ?>" />
                        <?php else: ?>
                            <span class="h3"><?= settings()->main->title ?></span>
                        <?php endif ?>
                    </a>
                </div>

                <div class="card rounded-2x">
                    <div class="card-body p-5">
                        <?= $this->views['content'] ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<?= \Altum\Event::get_content('modals') ?>

<?php require THEME_PATH . 'views/partials/js_global_variables.php' ?>

<?php foreach(['libraries/jquery.slim.min.js', 'libraries/popper.min.js', 'libraries/bootstrap.min.js', 'custom.js', 'libraries/fontawesome.min.js', 'libraries/fontawesome-solid.min.js', 'libraries/fontawesome-brands.modified.js'] as $file): ?>
    <script src="<?= ASSETS_FULL_URL ?>js/<?= $file ?>?v=<?= PRODUCT_CODE ?>"></script>
<?php endforeach ?>

<?= \Altum\Event::get_content('javascript') ?>

<script>
'use strict';

/* PWA Start URL Persistence - Ensure start page remains fixed on device */
(function() {
    const PWA_START_URL_KEY = 'pwa_start_url';
    const currentUrl = new URL(window.location.href);
    let currentPath = currentUrl.pathname + currentUrl.search.replace(/[?&]utm_[^&]*/g, '').replace(/^&/, '?');
    
    // Check if running in PWA standalone mode
    const isPWAStandalone = window.matchMedia('(display-mode: standalone)').matches || 
                            window.navigator.standalone === true ||
                            (window.matchMedia('(display-mode: fullscreen)').matches && document.referrer === '');
    
    // Check if user is locked (from PHP settings)
    const lockedUserIds = <?= json_encode(isset(settings()->pwa->pwa_locked_user_ids) && !empty(settings()->pwa->pwa_locked_user_ids) ? array_map('trim', explode(',', settings()->pwa->pwa_locked_user_ids)) : []) ?>;
    const currentUserId = <?= is_logged_in() ? \Altum\Authentication::$user_id : 'null' ?>;
    const isUserLocked = currentUserId && lockedUserIds.includes(String(currentUserId));
    
    // For locked users, force Persian (/fa/chats) as the start URL
    if (isUserLocked) {
        // Ensure the path starts with /fa/chats
        const faChatsPath = '/fa/chats';
        const currentPathWithoutQuery = currentUrl.pathname;
        
        // If not already on /fa/chats, redirect to it
        if (!currentPathWithoutQuery.startsWith('/fa/chats')) {
            const faChatsUrl = window.location.origin + faChatsPath + (currentUrl.search ? currentUrl.search : '');
            console.log('PWA: Locked user detected, forcing Persian chats page:', faChatsUrl);
            window.location.replace(faChatsUrl);
            return;
        }
        
        // Force the stored URL to be /fa/chats for locked users
        currentPath = faChatsPath + (currentUrl.search.replace(/[?&]utm_[^&]*/g, '').replace(/^&/, '?') || '');
    }
    
    // If utm_source=pwa is present, store this as the start URL (first time PWA is opened)
    if (currentUrl.searchParams.get('utm_source') === 'pwa') {
        // For locked users, always store /fa/chats
        const startUrl = isUserLocked ? '/fa/chats' : (currentPath || '/');
        localStorage.setItem(PWA_START_URL_KEY, startUrl);
        console.log('PWA Start URL stored:', startUrl, isUserLocked ? '(locked user - forced Persian)' : '');
    }
    
    // On PWA launch (standalone mode), ensure we're on the stored start URL
    // Only redirect on initial launch (no referrer or referrer is from outside)
    if (isPWAStandalone) {
        const storedStartUrl = localStorage.getItem(PWA_START_URL_KEY);
        if (storedStartUrl) {
            // For locked users, override stored URL with /fa/chats
            const targetUrl = isUserLocked ? '/fa/chats' : storedStartUrl;
            
            // Check if this is an initial launch (no referrer or referrer is external)
            const isInitialLaunch = !document.referrer || 
                                   !document.referrer.includes(window.location.hostname) ||
                                   document.referrer === '';
            
            // Only redirect on initial launch and if not already on the target URL
            if (isInitialLaunch && targetUrl !== currentPath) {
                const fullStartUrl = window.location.origin + targetUrl;
                console.log('PWA: Initial launch detected, redirecting to stored start URL:', fullStartUrl, isUserLocked ? '(locked user)' : '');
                window.location.replace(fullStartUrl); // Use replace to avoid adding to history
                return; // Prevent further execution
            }
        } else if (isUserLocked && isPWAStandalone) {
            // If no stored URL but user is locked, set it to /fa/chats
            localStorage.setItem(PWA_START_URL_KEY, '/fa/chats');
            if (!currentPath.startsWith('/fa/chats')) {
                const fullStartUrl = window.location.origin + '/fa/chats';
                console.log('PWA: Locked user, no stored URL, redirecting to Persian chats:', fullStartUrl);
                window.location.replace(fullStartUrl);
                return;
            }
        }
    }
})();
</script>
</body>
</html>
