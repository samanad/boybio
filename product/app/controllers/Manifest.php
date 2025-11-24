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

namespace Altum\Controllers;

defined('ALTUMCODE') || die();

class Manifest extends Controller {

    public function index() {

        /* Set proper headers for manifest */
        header('Content-Type: application/manifest+json');
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        /* Check if PWA is enabled */
        if(!\Altum\Plugin::is_active('pwa') || !settings()->pwa->is_enabled) {
            http_response_code(404);
            die();
        }

        /* If cloud offload is active, redirect to cloud URL */
        if(\Altum\Plugin::is_active('offload') && settings()->offload->uploads_url) {
            /* UPLOADS_FULL_URL is already set correctly in Settings.php and includes the base URL */
            $cloud_manifest_url = UPLOADS_FULL_URL . \Altum\Uploads::get_path('pwa') . 'manifest.json';
            header('Location: ' . $cloud_manifest_url, true, 307);
            die();
        }

        /* Cloud is not active - serve local manifest */
        $manifest_file_path = UPLOADS_PATH . \Altum\Uploads::get_path('pwa') . 'manifest.json';
        
        if(file_exists($manifest_file_path)) {
            readfile($manifest_file_path);
            die();
        }

        /* Manifest file doesn't exist */
        http_response_code(404);
        die();
    }

}

