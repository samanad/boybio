<?php
namespace Altum\Controllers;

defined('ALTUMCODE') || die();

/**
 * Same-origin Gravatar proxy — real Gravatar image, painted reliably in-page.
 */
class GravatarProxy extends Controller {

    public function index() {
        $hash = isset($this->params[0]) ? strtolower(trim((string) $this->params[0])) : '';
        if(!preg_match('/^[a-f0-9]{32}$/', $hash)) {
            $this->output_fallback();
        }

        $size = isset($_GET['s']) ? (int) $_GET['s'] : 80;
        $size = max(1, min(2048, $size));
        $d = isset($_GET['d']) ? preg_replace('/[^a-z0-9\-]/i', '', (string) $_GET['d']) : 'identicon';
        $d = $d !== '' ? $d : 'identicon';
        $r = isset($_GET['r']) ? preg_replace('/[^a-z]/i', '', (string) $_GET['r']) : 'g';
        $r = $r !== '' ? $r : 'g';

        $cache_key = 'gravatar_proxy_' . md5($hash . '|' . $size . '|' . $d . '|' . $r);
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

        $remote = 'https://www.gravatar.com/avatar/' . $hash . '?s=' . $size . '&d=' . rawurlencode($d) . '&r=' . rawurlencode($r);
        $fetched = $this->fetch($remote);

        if($fetched) {
            try {
                if(function_exists('cache')) {
                    $item = cache()->getItem($cache_key);
                    $item->set($fetched)->expiresAfter(60 * 60 * 24);
                    cache()->save($item);
                }
            } catch(\Throwable $e) {
                /* ignore */
            }
            header('Content-Type: ' . $fetched['type']);
            header('Cache-Control: public, max-age=86400');
            header('X-Content-Type-Options: nosniff');
            echo $fetched['body'];
            die();
        }

        $this->output_fallback($hash);
    }

    private function fetch(string $url): ?array {
        foreach([true, false] as $verify_ssl) {
            $body = null;
            $content_type = null;

            if(function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 8,
                    CURLOPT_USERAGENT => 'CloubGravatarProxy/1.0',
                    CURLOPT_SSL_VERIFYPEER => $verify_ssl,
                    CURLOPT_SSL_VERIFYHOST => $verify_ssl ? 2 : 0,
                ]);
                $body = curl_exec($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $ctype = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                curl_close($ch);
                if($code >= 400 || $body === false || $body === '') {
                    $body = null;
                }
                if(is_string($ctype) && $ctype !== '') {
                    $content_type = explode(';', $ctype)[0];
                }
            }

            if($body === null || $body === '') {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 8,
                        'follow_location' => 1,
                        'user_agent' => 'CloubGravatarProxy/1.0',
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
            }

            if($body === null || $body === false || $body === '' || strlen($body) < 16) {
                continue;
            }

            if(!$content_type || $content_type === 'application/octet-stream' || stripos($content_type, 'text/html') !== false) {
                $content_type = 'image/jpeg';
            }

            return ['body' => $body, 'type' => $content_type];
        }

        return null;
    }

    private function output_fallback(?string $hash = null): void {
        $email_stub = $hash ?: 'user';
        if(function_exists('get_local_avatar_data_uri')) {
            $data_uri = get_local_avatar_data_uri($email_stub, 80);
            if(str_starts_with($data_uri, 'data:image/svg+xml;base64,')) {
                $body = base64_decode(substr($data_uri, strlen('data:image/svg+xml;base64,')));
                header('Content-Type: image/svg+xml');
                header('Cache-Control: public, max-age=3600');
                echo $body;
                die();
            }
        }
        http_response_code(404);
        die();
    }
}
