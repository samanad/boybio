<?php

namespace Altum\Controllers;

use Altum\Logger;
use Altum\Models\User;
use Altum\Response;

defined('ALTUMCODE') || die();

class BoybiosApi extends Controller {

    public function index() {

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $secret = defined('BOYBIOS_WORKER_SECRET') ? (string) BOYBIOS_WORKER_SECRET : '';
        $given = (string) ($_SERVER['HTTP_X_BOYBIOS_SECRET'] ?? '');

        if($secret === '' || !hash_equals($secret, $given)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'error' => 'forbidden']);
            die();
        }

        $client_ip = trim((string) ($_SERVER['HTTP_X_BOYBIOS_CLIENT_IP'] ?? ''));
        if(filter_var($client_ip, FILTER_VALIDATE_IP)) {
            $_SERVER['HTTP_CF_CONNECTING_IP'] = $client_ip;
        }

        $action = $this->params[0] ?? '';
        $input = $this->json_input();

        switch($action) {
            case 'register':
                $this->register($input);
                break;

            case 'login':
                $this->login($input);
                break;

            case 'account':
                $this->account($input);
                break;

            default:
                http_response_code(404);
                echo json_encode(['ok' => false, 'error' => 'not_found']);
                die();
        }
    }

    private function json_input() {
        $raw = file_get_contents('php://input');
        if(!$raw) {
            return $_POST;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function fail($message, $code = 400, $fields = []) {
        http_response_code($code);
        echo json_encode(['ok' => false, 'error' => $message, 'fields' => (object) $fields]);
        die();
    }

    private function ok($data = [], $code = 200) {
        http_response_code($code);
        echo json_encode(array_merge(['ok' => true], $data));
        die();
    }

    private function user_payload($user) {
        $billing = is_string($user->billing ?? null) ? json_decode($user->billing) : ($user->billing ?? (object) []);

        return [
            'user_id' => (int) $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'timezone' => $user->timezone,
            'anti_phishing_code' => $user->anti_phishing_code,
            'is_newsletter_subscribed' => (bool) $user->is_newsletter_subscribed,
            'plan_id' => $user->plan_id,
            'status' => (int) $user->status,
            'referral_key' => $user->referral_key,
            'billing' => $billing,
            'api_key' => $user->api_key,
        ];
    }

    private function require_user($input) {
        $api_key = (string) ($input['api_key'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? ''));
        $api_key = preg_replace('/^Bearer\s+/i', '', $api_key);

        if($api_key === '') {
            $this->fail('missing_api_key', 401);
        }

        $user = db()->where('api_key', $api_key)->getOne('users');
        if(!$user || (int) $user->status !== 1) {
            $this->fail('invalid_session', 401);
        }

        $user->billing = json_decode($user->billing ?? '');
        return $user;
    }

    private function register($input) {
        if(!settings()->users->register_is_enabled) {
            $this->fail('registration_disabled', 403);
        }

        $email = trim(mb_strtolower((string) ($input['email'] ?? '')));
        $name = input_clean_name($input['name'] ?? '', 64);
        if($name === '' && $email !== '') {
            $name = input_clean_name(explode('@', $email)[0], 64);
        }
        $password = (string) ($input['password'] ?? '');
        $newsletter = !empty($input['is_newsletter_subscribed']);

        $fields = [];
        if($name === '' || mb_strlen($name) > 64) {
            $fields['name'] = 'invalid_name';
        }
        if($email === '' || !preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
            $fields['email'] = 'invalid_email';
        } elseif(db()->where('email', $email)->has('users')) {
            $fields['email'] = 'email_exists';
        } elseif(!settings()->users->email_aliases_is_enabled && str_contains($email, '+')) {
            $fields['email'] = 'email_aliases_not_allowed';
        }
        if(mb_strlen($password) < 6 || mb_strlen($password) > 64) {
            $fields['password'] = 'invalid_password';
        }
        if($fields) {
            $readable = [];
            $map = [
                'invalid_name' => 'Enter a name',
                'invalid_email' => 'Enter a valid email',
                'email_exists' => 'This email is already registered',
                'email_aliases_not_allowed' => 'This email is not allowed',
                'invalid_password' => 'Password must be 6 to 64 characters',
            ];
            foreach($fields as $code) {
                $readable[] = $map[$code] ?? $code;
            }
            $this->fail(implode('. ', $readable), 422, $fields);
        }

        $email_domain = get_domain_from_email($email);
        if(settings()->users->blacklisted_domains && in_array($email_domain, settings()->users->blacklisted_domains)) {
            $this->fail('blacklisted_domain', 422, ['email' => 'blacklisted_domain']);
        }

        $email_code = md5($email . microtime());
        $registered_user = (new User())->create(
            $email,
            $password,
            $name,
            (int) !settings()->users->email_confirmation,
            'boybios',
            $email_code,
            null,
            $newsletter,
            'free',
            json_encode(settings()->plan_free->settings ?? ''),
            get_date(),
            settings()->main->default_timezone
        );

        Logger::users($registered_user['user_id'], 'register.success');

        $user = db()->where('user_id', $registered_user['user_id'])->getOne('users');
        $payload = $this->user_payload($user);

        if(!(int) $user->status) {
            $email_template = get_email_template(
                [
                    '{{NAME}}' => str_replace('.', '. ', $name),
                ],
                l('global.emails.user_activation.subject'),
                [
                    '{{ACTIVATION_LINK}}' => url('activate-user?email=' . md5($email) . '&email_activation_code=' . $email_code . '&type=user_activation'),
                    '{{NAME}}' => str_replace('.', '. ', $name),
                ],
                l('global.emails.user_activation.body')
            );

            send_mail($email, $email_template->subject, $email_template->body);

            unset($payload['api_key']);
            $this->ok(['needs_email_confirmation' => true, 'user' => $payload]);
        }

        if(settings()->users->welcome_email_is_enabled) {
            $email_template = get_email_template(
                [],
                l('global.emails.user_welcome.subject'),
                [
                    '{{NAME}}' => $name,
                    '{{URL}}' => url(),
                    '{{DASHBOARD_LINK}}' => url('dashboard'),
                ],
                l('global.emails.user_welcome.body')
            );
            send_mail($email, $email_template->subject, $email_template->body);
        }

        $this->ok(['needs_email_confirmation' => false, 'user' => $payload]);
    }

    private function login($input) {
        $email = trim(mb_strtolower((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');
        $twofa_token = input_clean(str_replace(' ', '', $input['twofa_token'] ?? ''));

        if(!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $this->fail('wrong_credentials', 401);
        }

        $user = db()->where('email', $email)->getOne('users');
        if(!$user || !password_verify($password, $user->password)) {
            if($user) {
                Logger::users($user->user_id, 'login.wrong_password');
            }
            $this->fail('wrong_credentials', 401);
        }

        if((int) $user->status !== 1) {
            $this->fail('user_not_active', 403);
        }

        if(!empty($user->twofa_secret)) {
            if($twofa_token === '') {
                $this->ok(['needs_twofa' => true]);
            }
            $twofa = new \RobThree\Auth\TwoFactorAuth(new \RobThree\Auth\Providers\Qr\BaconQrCodeProvider(format: 'svg'), settings()->main->title, 6, 30);
            if(!$twofa->verifyCode($user->twofa_secret, $twofa_token)) {
                $this->fail('invalid_twofa', 401);
            }
        }

        (new User())->login_aftermath_update($user->user_id);
        $user = db()->where('user_id', $user->user_id)->getOne('users');

        $this->ok(['needs_twofa' => false, 'user' => $this->user_payload($user)]);
    }

    private function account($input) {
        $user = $this->require_user($input);

        if($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->ok(['user' => $this->user_payload($user)]);
        }

        if(!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PATCH', 'PUT'], true)) {
            $this->fail('method_not_allowed', 405);
        }

        $name = isset($input['name']) ? input_clean_name($input['name'], 64) : $user->name;
        $email = isset($input['email']) ? input_clean_email($input['email']) : $user->email;
        $timezone = isset($input['timezone']) && in_array($input['timezone'], \DateTimeZone::listIdentifiers())
            ? query_clean($input['timezone'])
            : $user->timezone;
        $anti_phishing_code = isset($input['anti_phishing_code']) ? input_clean($input['anti_phishing_code'], 8) : $user->anti_phishing_code;
        $is_newsletter_subscribed = array_key_exists('is_newsletter_subscribed', $input)
            ? (int) !empty($input['is_newsletter_subscribed'])
            : (int) $user->is_newsletter_subscribed;

        if($name === '' || mb_strlen($name) > 64) {
            $this->fail('validation', 422, ['name' => 'invalid_name']);
        }
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->fail('validation', 422, ['email' => 'invalid_email']);
        }
        if(db()->where('email', $email)->has('users') && $email !== $user->email) {
            $this->fail('validation', 422, ['email' => 'email_exists']);
        }

        $update = [
            'name' => $name,
            'timezone' => $timezone,
            'anti_phishing_code' => $anti_phishing_code,
            'is_newsletter_subscribed' => $is_newsletter_subscribed,
        ];

        if(empty($user->payment_subscription_id) && !empty($input['billing']) && is_array($input['billing'])) {
            $billing_in = $input['billing'];
            $billing = [
                'type' => in_array($billing_in['type'] ?? '', ['personal', 'business']) ? $billing_in['type'] : ($user->billing->type ?? 'personal'),
                'name' => input_clean($billing_in['name'] ?? ($user->billing->name ?? ''), 128),
                'address' => input_clean($billing_in['address'] ?? ($user->billing->address ?? ''), 128),
                'city' => input_clean($billing_in['city'] ?? ($user->billing->city ?? ''), 64),
                'county' => input_clean($billing_in['county'] ?? ($user->billing->county ?? ''), 64),
                'zip' => input_clean($billing_in['zip'] ?? ($user->billing->zip ?? ''), 32),
                'country' => array_key_exists($billing_in['country'] ?? '', get_countries_array()) ? query_clean($billing_in['country']) : ($user->billing->country ?? 'US'),
                'phone' => input_clean($billing_in['phone'] ?? ($user->billing->phone ?? ''), 32),
                'tax_id' => ($billing_in['type'] ?? '') === 'business' ? input_clean($billing_in['tax_id'] ?? '', 64) : '',
                'notes' => input_clean($billing_in['notes'] ?? ($user->billing->notes ?? ''), 512),
            ];
            $update['billing'] = json_encode($billing);
        }

        $old_password = (string) ($input['old_password'] ?? '');
        $new_password = (string) ($input['new_password'] ?? '');
        if($old_password !== '' && $new_password !== '') {
            if(!password_verify($old_password, $user->password)) {
                $this->fail('validation', 422, ['old_password' => 'invalid_current_password']);
            }
            if(mb_strlen($new_password) < 6 || mb_strlen($new_password) > 64) {
                $this->fail('validation', 422, ['new_password' => 'invalid_password']);
            }
            $update['password'] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        if($email !== $user->email) {
            if(settings()->users->email_confirmation) {
                $update['email_activation_code'] = md5($email . microtime());
            } else {
                $update['email'] = $email;
            }
        }

        db()->where('user_id', $user->user_id)->update('users', $update);
        cache()->deleteItemsByTag('user_id=' . $user->user_id);

        $user = db()->where('user_id', $user->user_id)->getOne('users');
        $this->ok(['user' => $this->user_payload($user)]);
    }
}
