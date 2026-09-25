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

class NotFound extends Controller {

    public function index() {

        /* Missing local /uploads/* must NEVER hit custom not_found_url (e.g. cloubio.ir).
           Offloaded files are only on CDN; .htaccess sends the miss into the app as a 404. */
        if($this->serve_or_redirect_missing_upload()) {
            return;
        }

        /* Handle subdirectory redirect */
        if(isset($_GET['subdirectory_redirect']) && isset($_SESSION['subdirectory_redirect_url']) && isset($_SESSION['subdirectory_redirect_message'])) {
            $redirect_url = $_SESSION['subdirectory_redirect_url'];
            $message = $_SESSION['subdirectory_redirect_message'];
            
            /* Clear session variables */
            unset($_SESSION['subdirectory_redirect_url']);
            unset($_SESSION['subdirectory_redirect_message']);
            
            /* Show message and redirect */
            \Altum\Alerts::add_info($message);
            
            /* Redirect after a short delay to show message */
            header('Refresh: 2; url=' . $redirect_url);
        }

        /* Custom 404 redirect if set — never for upload assets (handled above) */
        if(!empty(settings()->main->not_found_url)) {
            header('Location: ' . settings()->main->not_found_url); die();
        }

        header('HTTP/1.0 404 Not Found');

        $view = new \Altum\View('notfound/index', (array) $this);

        $this->add_view_content('content', $view->run());

    }

    /**
     * If this 404 was caused by a missing uploads/* file, stream it from CDN (same-origin)
     * or 302 to the CDN URL. Returns true when the request was handled.
     */
    private function serve_or_redirect_missing_upload(): bool {
        $original = (string) (\Altum\Router::$original_request ?? '');
        $uploads_prefix = defined('UPLOADS_URL_PATH') ? trim(UPLOADS_URL_PATH, '/') : 'uploads';

        if($original === '' || (!str_starts_with($original, $uploads_prefix . '/') && $original !== $uploads_prefix)) {
            return false;
        }

        $relative = ltrim(substr($original, strlen($uploads_prefix)), '/');
        if($relative === '' || str_contains($relative, '..')) {
            http_response_code(404);
            die();
        }

        /* Local file (rare when fully offloaded) */
        $local_path = (defined('UPLOADS_PATH') ? UPLOADS_PATH : (ROOT_PATH . 'uploads/')) . $relative;
        if(is_file($local_path)) {
            $this->stream_file($local_path, null);
            return true;
        }

        /* Prefer CDN / offload public URL (never redirect to custom not_found_url like cloubio.ir) */
        $cdn_bases = [];
        if(defined('UPLOADS_CDN_FULL_URL') && UPLOADS_CDN_FULL_URL) {
            $cdn_bases[] = UPLOADS_CDN_FULL_URL;
        }
        if(\Altum\Plugin::is_active('offload')) {
            foreach([settings()->offload->cdn_uploads_url ?? null, settings()->offload->uploads_url ?? null] as $base) {
                $base = is_string($base) ? trim($base) : '';
                if($base !== '') {
                    $cdn_bases[] = rtrim($base, '/') . '/';
                }
            }
        }

        $site_uploads = rtrim(SITE_URL, '/') . '/' . $uploads_prefix . '/';
        $remote_url = null;
        foreach(array_unique($cdn_bases) as $base) {
            /* Build candidate URL without double "uploads/" */
            $base_trim = rtrim($base, '/') . '/';
            if(str_ends_with(rtrim($base, '/'), '/' . $uploads_prefix) || str_ends_with(rtrim($base, '/'), $uploads_prefix)) {
                $candidate = $base_trim . $relative;
            } else {
                $candidate = $base_trim . $uploads_prefix . '/' . $relative;
            }

            /* Skip self (would loop back into this 404) */
            if(str_starts_with($candidate, $site_uploads)) {
                continue;
            }
            $remote_url = $candidate;
            break;
        }

        if($remote_url) {
            /* Stream through this host so <img src="https://cloub.io/uploads/..."> stays same-origin */
            if($this->stream_remote($remote_url)) {
                return true;
            }

            /* Fallback: redirect browser to CDN (better than cloubio.ir) */
            header('Location: ' . $remote_url, true, 302);
            die();
        }

        http_response_code(404);
        die();
    }

    private function stream_file(string $path, ?string $content_type): void {
        if(!$content_type) {
            $content_type = @mime_content_type($path) ?: 'application/octet-stream';
        }
        header('Content-Type: ' . $content_type);
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        die();
    }

    private function stream_remote(string $url): bool {
        $body = null;
        $content_type = null;

        /* Try with SSL verify, then without — this host's CA bundle is often incomplete */
        foreach([true, false] as $verify_ssl) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 12,
                    'follow_location' => 1,
                    'user_agent' => 'CloubUploadsProxy/1.0',
                ],
                'ssl' => [
                    'verify_peer' => $verify_ssl,
                    'verify_peer_name' => $verify_ssl,
                ],
            ]);
            $body = @file_get_contents($url, false, $context);
            if(isset($http_response_header) && is_array($http_response_header)) {
                foreach($http_response_header as $header_line) {
                    if(stripos($header_line, 'Content-Type:') === 0) {
                        $content_type = trim(substr($header_line, strlen('Content-Type:')));
                        break;
                    }
                }
            }
            if($body !== null && $body !== false && $body !== '') {
                break;
            }

            if(function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 12,
                    CURLOPT_USERAGENT => 'CloubUploadsProxy/1.0',
                    CURLOPT_SSL_VERIFYPEER => $verify_ssl,
                    CURLOPT_SSL_VERIFYHOST => $verify_ssl ? 2 : 0,
                ]);
                $body = curl_exec($ch);
                $ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if($code >= 400) {
                    $body = null;
                }
                if(is_string($ctype) && $ctype !== '') {
                    $content_type = explode(';', $ctype)[0];
                }
                if($body !== null && $body !== false && $body !== '') {
                    break;
                }
            }
        }

        if($body === null || $body === false || $body === '') {
            return false;
        }

        if(!$content_type || $content_type === 'application/octet-stream') {
            $ext = mb_strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $content_type = match($ext) {
                'svg' => 'image/svg+xml',
                'jpg', 'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'avif' => 'image/avif',
                'ico' => 'image/x-icon',
                'json' => 'application/json',
                'js' => 'application/javascript',
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
