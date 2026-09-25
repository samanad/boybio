<?php
namespace Altum\Controllers;

defined('ALTUMCODE') || die();

/**
 * Same-origin DuckDuckGo favicon proxy so dashboard <img> tags paint.
 */
class FaviconProxy extends Controller {

    public function index() {
        $domain = isset($this->params[0]) ? rawurldecode((string) $this->params[0]) : '';
        $domain = preg_replace('/^www\./', '', mb_strtolower(trim($domain)));
        $domain = preg_replace('/[^a-z0-9.-]/i', '', $domain);

        if($domain === '' || mb_strlen($domain) > 253 || !preg_match('/[a-z0-9]/i', $domain)) {
            $this->output_fallback('?');
        }

        $cache_key = 'favicon_proxy_' . md5($domain);
        try {
            if(function_exists('cache')) {
                $item = cache()->getItem($cache_key);
                if($item->isHit()) {
                    $cached = $item->get();
                    if(is_array($cached) && !empty($cached['body']) && !empty($cached['type'])) {
                        header('Content-Type: ' . $cached['type']);
                        header('Cache-Control: public, max-age=86400');
                        header('X-Content-Type-Options: nosniff');
                        echo $cached['body'];
                        die();
                    }
                }
            }
        } catch(\Throwable $e) {
            /* ignore */
        }

        $remote_url = 'https://external-content.duckduckgo.com/ip3/' . rawurlencode($domain) . '.ico';
        $body = null;
        $content_type = null;

        foreach([true, false] as $verify_ssl) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 8,
                    'follow_location' => 1,
                    'user_agent' => 'CloubFaviconProxy/1.0',
                ],
                'ssl' => [
                    'verify_peer' => $verify_ssl,
                    'verify_peer_name' => $verify_ssl,
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
            if(is_string($body) && $body !== '') {
                break;
            }

            if(function_exists('curl_init')) {
                $ch = curl_init($remote_url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 8,
                    CURLOPT_USERAGENT => 'CloubFaviconProxy/1.0',
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
                if(is_string($body) && $body !== '') {
                    break;
                }
            }
        }

        if(!is_string($body) || $body === '') {
            $this->output_fallback($domain);
        }

        if(!$content_type || $content_type === 'application/octet-stream' || str_starts_with($content_type, 'text/')) {
            $content_type = 'image/x-icon';
        }

        try {
            if(function_exists('cache')) {
                $item = cache()->getItem($cache_key);
                $item->set(['body' => $body, 'type' => $content_type])->expiresAfter(60 * 60 * 24);
                cache()->save($item);
            }
        } catch(\Throwable $e) {
            /* ignore */
        }

        header('Content-Type: ' . $content_type);
        header('Cache-Control: public, max-age=86400');
        header('X-Content-Type-Options: nosniff');
        echo $body;
        die();
    }

    private function output_fallback(string $domain): void {
        $data_uri = get_local_favicon_data_uri($domain);
        $raw = substr($data_uri, strlen('data:image/svg+xml;base64,'));
        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=3600');
        echo base64_decode($raw);
        die();
    }
}
