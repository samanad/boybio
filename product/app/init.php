<?php
/*
 * Copyright (c) 2025 AltumCode (https://altumcode.com/)
 *
 * This software is licensed exclusively by AltumCode and is sold only via https://altumcode.com/.
 * Unauthorized distribution, modification, or use of this software without a valid license is not permitted and may be subject to applicable legal actions.
 *
 * 🌍 View all other existing AltumCode projects via https://altumcode.com/
 * 📧 Get in touch for support or general queries via https://altumcode.com/contact
 * 📤 Download the latest version via https://altumcode.com/downloads
 *
 * 🐦 X/Twitter: https://x.com/AltumCode
 * 📘 Facebook: https://facebook.com/altumcode
 * 📸 Instagram: https://instagram.com/altumcode
 */

defined('ALTUMCODE') || die();
define('ROOT_PATH', realpath(__DIR__ . '/..') . '/');
const APP_PATH = ROOT_PATH . 'app/';
const PLUGINS_PATH = ROOT_PATH . 'plugins/';
const THEME_PATH = ROOT_PATH . 'themes/altum/';
const THEME_URL_PATH = 'themes/altum/';
const ASSETS_PATH = THEME_PATH . 'assets/';
const ASSETS_URL_PATH = THEME_URL_PATH . 'assets/';
const UPLOADS_PATH = ROOT_PATH . 'uploads/';
const UPLOADS_URL_PATH = 'uploads/';
const CACHE_DEFAULT_SECONDS = 2592000;

/* Starting to include the required files */
require_once APP_PATH . 'includes/debug.php';
if(!DEBUG) require_once APP_PATH . 'includes/500.php';
require_once APP_PATH . 'includes/product.php';

/* Config file */
require_once ROOT_PATH . 'config.php';

/* Domain redirect: Redirect boybio.net to cloub.io, but allow PWA-related requests */
if(defined('SITE_URL') && !empty(SITE_URL)) {
    $current_host = $_SERVER['HTTP_HOST'] ?? '';
    $site_url_host = parse_url(SITE_URL, PHP_URL_HOST);
    
    /* Check if we're on the old domain and should redirect */
    if($current_host === 'boybio.net' || $current_host === 'www.boybio.net') {
        /* Get the request URI */
        $request_uri = $_SERVER['REQUEST_URI'] ?? '/';
        $request_path = parse_url($request_uri, PHP_URL_PATH);
        
        /* List of paths that should NOT be redirected (PWA-related) */
        $pwa_excluded_paths = [
            '/sw.js',
            '/manifest.json',
            '/uploads/pwa/',
            '/api/',
            '/ajax/',
            '/link-ajax',
            '/biolink-block-ajax',
            '/push-subscribers',
        ];
        
        /* Check if this is a PWA-related request */
        $is_pwa_request = false;
        foreach($pwa_excluded_paths as $excluded_path) {
            if(strpos($request_path, $excluded_path) === 0) {
                $is_pwa_request = true;
                break;
            }
        }
        
        /* Also check for service worker registration or manifest requests */
        if(isset($_SERVER['HTTP_USER_AGENT']) && (
            strpos($_SERVER['HTTP_USER_AGENT'], 'ServiceWorker') !== false ||
            strpos($request_uri, 'sw.js') !== false ||
            strpos($request_uri, 'manifest.json') !== false
        )) {
            $is_pwa_request = true;
        }
        
        /* Redirect to new domain if not a PWA request */
        if(!$is_pwa_request) {
            $new_url = 'https://cloub.io' . $request_uri;
            if(!empty($_SERVER['QUERY_STRING'])) {
                $new_url .= '?' . $_SERVER['QUERY_STRING'];
            }
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $new_url);
            exit;
        }
    }
}

/* Establish cookie / session on this path specifically */
define('COOKIE_PATH', preg_replace('|https?://[^/]+|i', '', SITE_URL));

/* Determine if we should set the samesite=strict */
session_set_cookie_params([
    'lifetime' => null,
    'path' => COOKIE_PATH,
    'samesite' => 'Lax',
    'secure' => str_starts_with(SITE_URL, 'https://'),
]);

/* Only start a session handler if we need to */
$should_start_session = !isset($_GET['altum'])
    || (
        !str_starts_with($_GET['altum'], 'cron')
        && !str_starts_with($_GET['altum'], 'sitemap')
        && !str_starts_with($_GET['altum'], 'webhook-')
        && !str_starts_with($_GET['altum'], 'api/')
    );

if($should_start_session) {
    session_start();
}

/* Autoloader */
spl_autoload_register (function ($class) {
    $namespace_prefix = 'Altum';
    $split = explode('\\', $class);

    if($split[0] !== $namespace_prefix) {
        return;
    }

    /* Altum core */
    if(isset($split[1]) && !isset($split[2])) {
        require_once APP_PATH . 'core/' . $split[1] . '.php';
    }

    /* Traits, Models, Helpers */
    if(isset($split[1], $split[2]) && in_array($split[1], ['Traits', 'Models', 'Helpers'])) {
        $folder = mb_strtolower($split[1]);
        require_once APP_PATH . $folder . '/' . $split[2] . '.php';
    }

    /* Payment Gateways helpers */
    if(isset($split[1], $split[2]) && $split[1] == 'PaymentGateways') {
        require_once APP_PATH . 'helpers/payment-gateways/' . $split[2] . '.php';
    }

    /* Qr codes helpers */
    if(isset($split[1], $split[2]) && $split[1] == 'QrCodes') {
        require_once APP_PATH . 'helpers/qr-codes/' . $split[2] . '.php';
    }
});

/* Require files */
require_once APP_PATH . 'core/Controller.php';
require_once APP_PATH . 'core/Model.php';

/* Load some helpers */
require_once APP_PATH . 'helpers/Link.php';
require_once APP_PATH . 'helpers/core.php';
require_once APP_PATH . 'helpers/others.php';
require_once APP_PATH . 'helpers/links.php';
require_once APP_PATH . 'helpers/strings.php';
require_once APP_PATH . 'helpers/email.php';
require_once APP_PATH . 'helpers/66uptime.php';

/* Autoload for vendor */
require_once ROOT_PATH . 'vendor/autoload.php';

