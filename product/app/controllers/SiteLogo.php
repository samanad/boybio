<?php
namespace Altum\Controllers;

defined('ALTUMCODE') || die();

/**
 * Stream the site logo from the configured uploads storage via the current site host.
 * Avoids broken /uploads redirects and legacy CDN hosts (e.g. linkofbio.com) in the browser.
 */
class SiteLogo extends Controller {

    public function index() {
        $theme = isset($this->params[0]) && $this->params[0] === 'dark' ? 'dark' : 'light';
        $file = function_exists('get_main_logo_filename') ? get_main_logo_filename($theme) : '';

        if($file === '' || preg_match('/[\\\\\\/]/', $file)) {
            http_response_code(404);
            die();
        }

        /* Prefer the matching theme path; fall back to the other when using the opposite file */
        $upload_key = 'logo_' . $theme;
        if(empty(settings()->main->{'logo_' . $theme}) && !empty(settings()->main->{'logo_' . ($theme === 'dark' ? 'light' : 'dark')})) {
            $upload_key = 'logo_' . ($theme === 'dark' ? 'light' : 'dark');
        }

        $remote_url = \Altum\Uploads::get_full_url($upload_key) . $file;
        $local_path = UPLOADS_PATH . \Altum\Uploads::get_path($upload_key) . $file;

        $body = null;
        $content_type = null;

        if(is_file($local_path)) {
            $body = @file_get_contents($local_path);
            $content_type = @mime_content_type($local_path) ?: null;
        }

        if(($body === null || $body === false || $body === '') && $remote_url) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 8,
                    'follow_location' => 1,
                    'user_agent' => 'CloubSiteLogo/1.0',
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);
            $body = @file_get_contents($remote_url, false, $context);
            if(isset($http_response_header) && is_array($http_response_header)) {
                foreach($http_response_header as $header_line) {
                    if(stripos($header_line, 'Content-Type:') === 0) {
                        $content_type = trim(substr($header_line, strlen('Content-Type:')));
                        break;
                    }
                }
            }
        }

        if($body === null || $body === false || $body === '') {
            http_response_code(404);
            die();
        }

        if(!$content_type || $content_type === 'application/octet-stream') {
            $ext = mb_strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $content_type = match($ext) {
                'svg' => 'image/svg+xml',
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'avif' => 'image/avif',
                'ico' => 'image/x-icon',
                default => 'image/png',
            };
        }

        header('Content-Type: ' . $content_type);
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        echo $body;
        die();
    }
}
