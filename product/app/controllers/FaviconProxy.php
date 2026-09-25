<?php
namespace Altum\Controllers;

defined('ALTUMCODE') || die();

/**
 * Same-origin favicon proxy.
 * Fetches DuckDuckGo ip3 icons so dashboard <img> tags paint (cross-origin DDG often fails in-page).
 * For biolink slugs (e.g. "sam"), ?guess=1 tries sam.com then sam.ir.
 */
class FaviconProxy extends Controller {

    public function index() {
        $raw = isset($this->params[0]) ? rawurldecode((string) $this->params[0]) : '';
        $raw = mb_strtolower(trim($raw));
        $raw = preg_replace('/^www\./', '', $raw);
        $guess = isset($_GET['guess']) && (string) $_GET['guess'] !== '0' && (string) $_GET['guess'] !== '';

        if($raw === '' || preg_match('/[^a-z0-9.\-]/', $raw) || str_contains($raw, '..')) {
            $this->output_fallback('?');
        }

        $candidates = [];
        if(str_contains($raw, '.')) {
            $candidates[] = $raw;
        } elseif($guess || !str_contains($raw, '.')) {
            /* Biolink slug → try common TLDs (not the biolink host like boy.bio) */
            foreach(['com', 'ir', 'net', 'org', 'io'] as $tld) {
                $candidates[] = $raw . '.' . $tld;
            }
        } else {
            $candidates[] = $raw;
        }

        $cache_key = 'favicon_proxy_' . md5(implode('|', $candidates));
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

        foreach($candidates as $domain) {
            $fetched = $this->fetch_ddg_favicon($domain);
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
        }

        $this->output_fallback($raw);
    }

    private function fetch_ddg_favicon(string $domain): ?array {
        $url = 'https://external-content.duckduckgo.com/ip3/' . rawurlencode($domain) . '.ico';

        foreach([true, false] as $verify_ssl) {
            $body = null;
            $content_type = null;

            if(function_exists('curl_init')) {
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT => 8,
                    CURLOPT_USERAGENT => 'CloubFaviconProxy/1.0',
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

            if(($body === null || $body === '') ) {
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

            if($body === null || $body === false || $body === '') {
                continue;
            }

            /* DDG sometimes returns a tiny placeholder / HTML on miss */
            if(strlen($body) < 32 || stripos((string) $content_type, 'text/html') !== false) {
                continue;
            }

            if(!$content_type || $content_type === 'application/octet-stream') {
                $content_type = 'image/x-icon';
            }

            return ['body' => $body, 'type' => $content_type];
        }

        return null;
    }

    private function output_fallback(string $label_source): void {
        if(function_exists('get_local_favicon_data_uri')) {
            $data_uri = get_local_favicon_data_uri($label_source);
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
