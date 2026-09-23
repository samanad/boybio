<?php
/*
 * Custom admin bridge SSO for peer sites (e.g. shazdeha.com).
 * Issues a short-lived HMAC-signed token after Altum admin login.
 *
 * Secrets from .env (never expose errors to the public — always 404):
 *   /var/www/www-root/data/www/boybio.net/.env   (parent of product/)
 *   or product/.env if open_basedir blocks the parent
 */

namespace Altum\Controllers;

defined('ALTUMCODE') || die();

class AdminBridge extends Controller {

    private static $env_cache = null;

    public function index() {
        self::deny_public();
    }

    private static function deny_public() {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo "you can't access this page";
        die();
    }

    public function authorize() {
        /* Never leak config details — plain denial for outsiders */
        if(!self::get_secret()) {
            self::deny_public();
        }

        \Altum\Authentication::guard('admin');

        $return_url = isset($_GET['return_url']) ? trim($_GET['return_url']) : '';
        $state = isset($_GET['state']) ? trim($_GET['state']) : '';

        if($return_url === '' || !self::is_allowed_return_url($return_url)) {
            self::deny_public();
        }

        $user = \Altum\Authentication::$user;
        if(!$user || (int) ($user->type ?? 0) !== 1) {
            self::deny_public();
        }

        $secret = self::get_secret();
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
            $parsed = self::try_read_dotenv($path);
            if($parsed) {
                self::$env_cache = $parsed;
                break;
            }
        }
        return self::$env_cache;
    }

    private static function try_read_dotenv($path) {
        if(!$path) {
            return [];
        }
        /* Suppress open_basedir / permission warnings — never show to clients */
        $raw = @file_get_contents($path);
        if($raw === false || $raw === '') {
            return [];
        }
        return self::parse_dotenv_string($raw);
    }

    private static function dotenv_candidate_paths() {
        $paths = [];

        /*
         * product/ is the PHP app root; open_basedir usually allows only this tree.
         * Prefer product/.env — parent boybio.net/.env is often unreadable.
         */
        if(defined('ROOT_PATH')) {
            $product = rtrim(ROOT_PATH, '/\\');
            $paths[] = $product . '/.env';
            $domain = dirname($product);
            if($domain && $domain !== $product) {
                $paths[] = $domain . '/.env';
            }
        }

        $product_from_file = realpath(__DIR__ . '/../../..');
        if($product_from_file) {
            $paths[] = $product_from_file . '/.env';
            $paths[] = dirname($product_from_file) . '/.env';
        }

        if(!empty($_SERVER['DOCUMENT_ROOT'])) {
            $doc = realpath($_SERVER['DOCUMENT_ROOT']) ?: rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
            $paths[] = $doc . '/.env';
            $paths[] = dirname($doc) . '/.env';
        }

        $paths[] = '/var/www/www-root/data/www/boybio.net/product/.env';
        $paths[] = '/var/www/www-root/data/www/boybio.net/.env';

        return array_values(array_unique(array_filter($paths)));
    }

    private static function parse_dotenv_string($raw) {
        $out = [];
        $lines = preg_split('/\r\n|\r|\n/', $raw);
        if(!$lines) {
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
        if(is_string($secret) && strlen($secret) >= 16) {
            return $secret;
        }
        @error_log('AdminBridge: ADMIN_BRIDGE_SECRET missing or unreadable. Tried: ' . implode(' | ', self::dotenv_candidate_paths()));
        return '';
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
