<?php
/*
 * Custom admin bridge SSO for peer sites (e.g. shazdeha.com).
 * Issues a short-lived HMAC-signed token after Altum admin login.
 *
 * Config (preferred): .env next to the domain root, e.g.
 *   /var/www/www-root/data/www/boybio.net/.env
 * Also accepts process env / $_SERVER as fallback.
 */

namespace Altum\Controllers;

defined('ALTUMCODE') || die();

class AdminBridge extends Controller {

    private static $env_cache = null;

    public function index() {
        throw_404();
    }

    public function authorize() {
        \Altum\Authentication::guard('admin');

        $secret = self::get_secret();
        if(!$secret) {
            http_response_code(500);
            echo 'ADMIN_BRIDGE_SECRET is not configured (set it in /.env on the domain root).';
            die();
        }

        $return_url = isset($_GET['return_url']) ? trim($_GET['return_url']) : '';
        $state = isset($_GET['state']) ? trim($_GET['state']) : '';

        if($return_url === '' || !self::is_allowed_return_url($return_url)) {
            http_response_code(400);
            echo 'Invalid or disallowed return_url.';
            die();
        }

        $user = \Altum\Authentication::$user;
        $aud = self::origin_from_url($return_url);
        $exp = time() + 120;
        $payload = [
            'sub' => (string) $user->user_id,
            'email' => (string) ($user->email ?? ''),
            'exp' => $exp,
            'aud' => $aud,
            'nonce' => bin2hex(random_bytes(8)),
        ];

        $token = self::sign_token($payload, $secret);

        $separator = (strpos($return_url, '?') === false) ? '?' : '&';
        $redirect = $return_url . $separator . http_build_query([
            'token' => $token,
            'state' => $state,
        ]);

        header('Location: ' . $redirect);
        die();
    }

    private static function env_value($key) {
        $from_env = getenv($key);
        if(is_string($from_env) && $from_env !== '') {
            return $from_env;
        }
        if(isset($_SERVER[$key]) && is_string($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }
        $file = self::load_dotenv();
        if(isset($file[$key]) && is_string($file[$key]) && $file[$key] !== '') {
            return $file[$key];
        }
        return '';
    }

    private static function load_dotenv() {
        if(self::$env_cache !== null) {
            return self::$env_cache;
        }

        self::$env_cache = [];
        foreach(self::dotenv_candidate_paths() as $path) {
            if(!is_readable($path)) {
                continue;
            }
            $parsed = self::parse_dotenv_file($path);
            if($parsed) {
                self::$env_cache = $parsed;
                break;
            }
        }
        return self::$env_cache;
    }

    private static function dotenv_candidate_paths() {
        $paths = [];

        /* Exact path you requested */
        $paths[] = '/var/www/www-root/data/www/boybio.net/.env';

        /* Domain root = parent of product/ (ROOT_PATH) */
        if(defined('ROOT_PATH')) {
            $paths[] = rtrim(dirname(rtrim(ROOT_PATH, '/\\')), '/\\') . '/.env';
            $paths[] = rtrim(ROOT_PATH, '/\\') . '/.env';
        }

        /* Walk up from this controller file */
        $dir = realpath(__DIR__ . '/../../..'); /* product/ */
        if($dir) {
            $paths[] = $dir . '/.env';
            $parent = dirname($dir);
            if($parent && $parent !== $dir) {
                $paths[] = $parent . '/.env';
            }
        }

        return array_values(array_unique($paths));
    }

    private static function parse_dotenv_file($path) {
        $out = [];
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if($lines === false) {
            return [];
        }
        foreach($lines as $line) {
            $line = trim($line);
            if($line === '' || $line[0] === '#' || $line[0] === ';') {
                continue;
            }
            if(stripos($line, 'export ') === 0) {
                $line = trim(substr($line, 7));
            }
            $eq = strpos($line, '=');
            if($eq === false) {
                continue;
            }
            $key = trim(substr($line, 0, $eq));
            $value = trim(substr($line, $eq + 1));
            if($key === '') {
                continue;
            }
            if(
                (strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"') ||
                (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'")
            ) {
                $value = substr($value, 1, -1);
            }
            $out[$key] = $value;
        }
        return $out;
    }

    private static function get_secret() {
        $secret = self::env_value('ADMIN_BRIDGE_SECRET');
        return is_string($secret) && strlen($secret) >= 16 ? $secret : '';
    }

    private static function get_peers() {
        $raw = self::env_value('ADMIN_BRIDGE_PEERS');
        if(!is_string($raw) || trim($raw) === '') {
            $raw = 'https://www.shazdeha.com,https://shazdeha.com';
        }
        $peers = [];
        foreach(explode(',', $raw) as $part) {
            $origin = self::normalize_origin(trim($part));
            if($origin) {
                $peers[$origin] = true;
            }
        }
        return array_keys($peers);
    }

    private static function normalize_origin($url) {
        if(!$url) return '';
        $parts = parse_url($url);
        if(!$parts || empty($parts['scheme']) || empty($parts['host'])) return '';
        if(!in_array(strtolower($parts['scheme']), ['https', 'http'], true)) return '';
        $origin = strtolower($parts['scheme']) . '://' . strtolower($parts['host']);
        if(!empty($parts['port'])) {
            $origin .= ':' . $parts['port'];
        }
        return $origin;
    }

    private static function origin_from_url($url) {
        return self::normalize_origin($url);
    }

    private static function is_allowed_return_url($return_url) {
        $parts = parse_url($return_url);
        if(!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }
        if(!in_array(strtolower($parts['scheme']), ['https', 'http'], true)) {
            return false;
        }
        if(!empty($parts['user']) || !empty($parts['pass']) || isset($parts['fragment'])) {
            return false;
        }
        $origin = self::origin_from_url($return_url);
        if(!$origin) return false;
        return in_array($origin, self::get_peers(), true);
    }

    private static function b64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function sign_token(array $payload, $secret) {
        $body = self::b64url_encode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $sig = self::b64url_encode(hash_hmac('sha256', $body, $secret, true));
        return $body . '.' . $sig;
    }

}
