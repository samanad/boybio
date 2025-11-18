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

class ServiceWorker extends Controller {

    public function index() {

        /* Set proper headers for service worker */
        header('Content-Type: application/javascript');
        header('Service-Worker-Allowed: /');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        /* Check if PWA is enabled and service worker file exists */
        if(\Altum\Plugin::is_active('pwa') && settings()->pwa->is_enabled) {
            $service_worker_file = UPLOADS_PATH . \Altum\Uploads::get_path('pwa') . 'sw.js';
            
            if(file_exists($service_worker_file)) {
                readfile($service_worker_file);
                die();
            }
        }

        /* Return empty service worker if no file exists (prevents redirect errors) */
        echo "// Service Worker\n";
        echo "// No service worker file configured\n";
        echo "self.addEventListener('install', function(event) {\n";
        echo "    self.skipWaiting();\n";
        echo "});\n";
        echo "self.addEventListener('activate', function(event) {\n";
        echo "    event.waitUntil(self.clients.claim());\n";
        echo "});\n";
        
        die();
    }

}








