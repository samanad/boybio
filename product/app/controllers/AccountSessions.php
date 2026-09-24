<?php
namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Models\UsersSessions;

defined('ALTUMCODE') || die();

class AccountSessions extends Controller {

    public function index() {
        UsersSessions::ensure_table();
        UsersSessions::upsert_current((int) $this->user->user_id, (bool) $this->user->type);

        if(!empty($_POST)) {
            if(!\Altum\Csrf::check()) {
                Alerts::add_error(l('global.error_message.invalid_csrf_token'));
                redirect('account-sessions');
            }

            if(isset($_POST['revoke_session_id'])) {
                $sid = (string) $_POST['revoke_session_id'];
                if($sid === session_id()) {
                    Alerts::add_error(l('account_sessions.error_current'));
                } elseif(UsersSessions::revoke_for_user((int) $this->user->user_id, $sid)) {
                    Alerts::add_success(l('account_sessions.success_revoked'));
                } else {
                    Alerts::add_error(l('global.error_message.basic'));
                }
                redirect('account-sessions');
            }

            if(isset($_POST['revoke_others'])) {
                UsersSessions::revoke_others((int) $this->user->user_id, session_id());

                /* Rotate forever-login token so other devices' cookies stop working */
                $new_token = md5(($this->user->email ?? '') . microtime() . random_bytes(8));
                db()->where('user_id', $this->user->user_id)->update('users', ['token_code' => $new_token]);
                $remember_days = (int) (settings()->users->login_rememberme_cookie_days ?? 3650);
                if($remember_days < 365) {
                    $remember_days = 3650;
                }
                if(function_exists('set_device_cookie')) {
                    set_device_cookie('user_id', (string) $this->user->user_id, $remember_days);
                    set_device_cookie('token_code', (string) $new_token, $remember_days);
                    set_device_cookie('user_password_hash', md5($this->user->password ?? ''), $remember_days);
                }
                cache()->deleteItemsByTag('user_id=' . $this->user->user_id);

                Alerts::add_success(l('account_sessions.success_revoked_others'));
                redirect('account-sessions');
            }
        }

        $sessions = UsersSessions::get_all_for_user((int) $this->user->user_id, 50);
        $current_session_id = session_id();

        \Altum\Title::set(l('account_sessions.title'));

        $data = [
            'sessions' => $sessions,
            'current_session_id' => $current_session_id,
            'alive_minutes' => UsersSessions::ALIVE_MINUTES,
        ];

        $view = new \Altum\View('account-sessions/index', (array) $this);
        $this->add_view_content('content', $view->run($data));
    }
}
