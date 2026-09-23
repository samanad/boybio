<?php
/*
 * Custom admin bridge SSO for peer sites (e.g. shazdeha.com).
 * Issues a short-lived HMAC-signed token after Altum admin login.
 */

namespace Altum\Controllers;

defined('ALTUMCODE') || die();

class AdminBridge extends Controller {

    public function index() {
        throw_404();
    }

    public function authorize() {
        \Altum\Authentication::guard('admin');

        $secret = self::get_secret();
        if(!$secret) {
            http_response_code(500);
            echo 'ADMIN_BRIDGE_SECRET is not configured.';
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

    private static function get_secret() {
        $secret = getenv('ADMIN_BRIDGE_SECRET');
        if($secret === false || $secret === null || $secret === '') {
            $secret = $_SERVER['ADMIN_BRIDGE_SECRET'] ?? '';
        }
        return is_string($secret) && strlen($secret) >= 16 ? $secret : '';
    }

    private static function get_peers() {
        $raw = getenv('ADMIN_BRIDGE_PEERS');
        if($raw === false || $raw === null || $raw === '') {
            $raw = $_SERVER['ADMIN_BRIDGE_PEERS'] ?? '';
        }
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
        /* Disallow credentials / fragments in return */
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
