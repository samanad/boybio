<?php
/* cloub.io WebAuthn / security key login */

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Models\User;
use Altum\Response;
use Altum\WebAuthn;

defined('ALTUMCODE') || die();

class WebauthnAjax extends Controller {

    public function index() {
        WebAuthn::ensure_table();

        $content_type = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
        if(empty($_POST) && stripos($content_type, 'application/json') !== false) {
            $_POST = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        $request_type = $_POST['request_type'] ?? '';

        $csrf_check_passed = \Altum\Csrf::check('token') || \Altum\Csrf::check('global_token');
        $csrf_strict_validation = isset(settings()->security) && isset(settings()->security->csrf_strict_validation_is_enabled) ? settings()->security->csrf_strict_validation_is_enabled : false;

        if(!$csrf_check_passed && $csrf_strict_validation) {
            Response::json(l('global.error_message.invalid_csrf_token'), 'error');
        }

        switch($request_type) {
            case 'register_options':
                $this->register_options();
                break;

            case 'register_verify':
                $this->register_verify();
                break;

            case 'login_options':
                $this->login_options();
                break;

            case 'login_verify':
                $this->login_verify();
                break;

            case 'delete':
                $this->delete();
                break;

            default:
                Response::json(l('global.error_message.basic'), 'error');
        }
    }

    private function register_options() {
        \Altum\Authentication::guard();

        Response::json('', 'success', [
            'publicKey' => WebAuthn::registration_options($this->user),
        ]);
    }

    private function register_verify() {
        \Altum\Authentication::guard();

        try {
            $attestation = json_decode($_POST['credential'] ?? '{}', true);
            if(!is_array($attestation)) {
                throw new \Exception('credential');
            }

            $parsed = WebAuthn::verify_registration($attestation);

            if(db()->where('credential_id', $parsed['credential_id'])->has('users_security_keys')) {
                Response::json(l('account.security_key.error_message.exists'), 'error');
            }

            $name = input_clean($_POST['name'] ?? '', 64);
            if($name === '') {
                $name = l('account.security_key.default_name');
            }

            db()->insert('users_security_keys', [
                'user_id' => $this->user->user_id,
                'name' => $name,
                'credential_id' => $parsed['credential_id'],
                'public_key' => $parsed['public_key'],
                'counter' => $parsed['counter'],
                'datetime' => get_date(),
            ]);

            Response::json(l('account.security_key.success_message.registered'), 'success');
        } catch(\Exception $exception) {
            Response::json(l('account.security_key.error_message.register'), 'error');
        }
    }

    private function login_options() {
        \Altum\Authentication::guard('guest');

        $email = input_clean_email($_POST['email'] ?? '');
        $user = null;

        if($email) {
            $user = db()->where('email', $email)->getOne('users', ['user_id', 'status']);
            if($user && $user->status != 1) {
                $user = null;
            }
        }

        Response::json('', 'success', [
            'publicKey' => WebAuthn::assertion_options($user),
        ]);
    }

    private function login_verify() {
        \Altum\Authentication::guard('guest');

        try {
            $assertion = json_decode($_POST['credential'] ?? '{}', true);
            if(!is_array($assertion)) {
                throw new \Exception('credential');
            }

            $key = WebAuthn::verify_assertion($assertion);
            $user = db()->where('user_id', $key->user_id)->getOne('users', ['user_id', 'email', 'name', 'status', 'password', 'token_code', 'language']);

            if(!$user || $user->status != 1) {
                Response::json(l('login.error_message.user_not_active'), 'error');
            }

            $this->complete_login($user);
        } catch(\Exception $exception) {
            Response::json(l('login.security_key.error_message'), 'error');
        }
    }

    private function delete() {
        \Altum\Authentication::guard();

        $security_key_id = (int) ($_POST['security_key_id'] ?? 0);

        db()->where('user_id', $this->user->user_id)->where('security_key_id', $security_key_id)->delete('users_security_keys');

        Response::json(l('account.security_key.success_message.deleted'), 'success');
    }

    private function complete_login($user) {
        $token_code = $user->token_code;

        if(empty($user->token_code)) {
            $token_code = md5($user->email . microtime());
            db()->where('user_id', $user->user_id)->update('users', ['token_code' => $token_code]);
        }

        $remember_days = (int) (settings()->users->login_rememberme_cookie_days ?? 3650);
        if($remember_days < 365) {
            $remember_days = 3650;
        }

        set_device_cookie('user_id', (string) $user->user_id, $remember_days);
        set_device_cookie('token_code', (string) $token_code, $remember_days);
        set_device_cookie('user_password_hash', md5($user->password), $remember_days);

        $_SESSION['user_id'] = $user->user_id;
        $_SESSION['user_password_hash'] = md5($user->password);
        unset($_SESSION['twofa_required']);

        (new User())->login_aftermath_update($user->user_id, 'security_key');

        Alerts::add_info(sprintf(l('login.info_message.logged_in'), $user->name));

        $redirect = process_and_get_redirect_params() ?? 'dashboard';

        if(\Altum\Language::$name == $user->language) {
            $url = url($redirect);
        } else {
            $prefix = \Altum\Language::$active_languages[$user->language] ? \Altum\Language::$active_languages[$user->language] . '/' : '';
            $url = SITE_URL . $prefix . $redirect;
        }

        Response::json('', 'success', ['url' => $url]);
    }

}
