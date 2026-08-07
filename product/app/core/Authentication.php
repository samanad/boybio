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

namespace Altum;

use Altum\Models\User;

defined('ALTUMCODE') || die();

class Authentication {

    public static $is_logged_in = null;
    public static $user_id = null;
    public static $user = null;
    public static $login_guard_is_set = false;

    public static function check() {

        /* Verify if the current route allows use to do the check */
        if(\Altum\Router::$controller_settings['no_authentication_check']) {
            return false;
        }

        /* Already logged in from previous checks */
        if(self::$is_logged_in) {
            return self::$user_id;
        }

        /* Check the cookies first */
        if(
            isset($_COOKIE['user_id'])
            && isset($_COOKIE['token_code'])
            && mb_strlen($_COOKIE['token_code']) > 0
            && $user = (new User())->get_user_by_user_id($_COOKIE['user_id'])
        ) {
           if($user->token_code == $_COOKIE['token_code'] && isset($_COOKIE['user_password_hash']) && $_COOKIE['user_password_hash'] == md5($user->password)) {
               self::$is_logged_in = true;
               self::$user_id = $user->user_id;

               self::$user = $user;
               
               /* If user is on Google login persistent IP and has persistent_login_enabled, refresh cookies */
               $google_persistent_ip = isset(settings()->security) && isset(settings()->security->google_login_persistent_ip) ? settings()->security->google_login_persistent_ip : '';
               $current_ip = get_ip();
               if(!empty($google_persistent_ip) && $current_ip && $current_ip === $google_persistent_ip && $user->status == 1) {
                   $user_extra = json_decode($user->extra ?? '{}', true);
                   $persistent_enabled = isset($user_extra['persistent_login_enabled']) && $user_extra['persistent_login_enabled'] === true;
                   $persistent_method = $user_extra['persistent_login_method'] ?? '';
                   
                   if($persistent_enabled && $persistent_method === 'google') {
                       /* Refresh cookies to ensure they don't expire (1 year) */
                       setcookie('user_id', $user->user_id, time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                       setcookie('token_code', $user->token_code, time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                       setcookie('user_password_hash', md5($user->password), time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                       
                       /* Set device cookie to remember this device for future auto-login (10 years) */
                       setcookie('persistent_ip_device_user_id', $user->user_id, time() + (365 * 10 * 24 * 60 * 60), COOKIE_PATH);
                   }
               }

               return true;
           }
        }

        /* Check the Session */
        if(
            session_has('user_id')
            && !empty(session_get('user_id'))
            && $user = (new User())->get_user_by_user_id(session_get('user_id'))
        ) {
            if(session_has('user_password_hash') && session_get('user_password_hash') == md5($user->password ?? '')) {
                self::$is_logged_in = true;
                self::$user_id = $user->user_id;

                self::$user = $user;
                
                /* If user is on Google login persistent IP and has persistent_login_enabled, refresh cookies and session */
                $google_persistent_ip = isset(settings()->security) && isset(settings()->security->google_login_persistent_ip) ? settings()->security->google_login_persistent_ip : '';
                $current_ip = get_ip();
                if(!empty($google_persistent_ip) && $current_ip && $current_ip === $google_persistent_ip && $user->status == 1) {
                    $user_extra = json_decode($user->extra ?? '{}', true);
                    $persistent_enabled = isset($user_extra['persistent_login_enabled']) && $user_extra['persistent_login_enabled'] === true;
                    $persistent_method = $user_extra['persistent_login_method'] ?? '';
                    
                    if($persistent_enabled && $persistent_method === 'google') {
                        /* Generate token_code if not exists */
                        if(empty($user->token_code)) {
                            $user->token_code = md5($user->user_id . $user->password . time() . rand());
                            db()->where('user_id', $user->user_id)->update('users', ['token_code' => $user->token_code]);
                            self::$user->token_code = $user->token_code;
                        }
                        
                        /* Refresh cookies to ensure they don't expire (1 year) */
                        setcookie('user_id', $user->user_id, time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                        setcookie('token_code', $user->token_code, time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                        setcookie('user_password_hash', md5($user->password), time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                        
                        /* Set device cookie to remember this device for future auto-login (10 years) */
                        setcookie('persistent_ip_device_user_id', $user->user_id, time() + (365 * 10 * 24 * 60 * 60), COOKIE_PATH);
                    }
                }

                return true;
            }
        }

        /* Check for Google Login Persistent IP - auto-login users with persistent_login_enabled */
        $google_persistent_ip = isset(settings()->security) && isset(settings()->security->google_login_persistent_ip) ? settings()->security->google_login_persistent_ip : '';
        $current_ip = get_ip();
        
        if(!empty($google_persistent_ip) && $current_ip && $current_ip === $google_persistent_ip) {
            /* First, check device cookie for persistent IP users (even after logout) */
            $device_user_id = $_COOKIE['persistent_ip_device_user_id'] ?? null;
            
            /* If device cookie exists, try to auto-login that user */
            if($device_user_id) {
                $user = (new User())->get_user_by_user_id($device_user_id);
                
                if($user && $user->status == 1) {
                    /* Check if user has persistent_login_enabled */
                    $user_extra = json_decode($user->extra ?? '{}', true);
                    $persistent_enabled = isset($user_extra['persistent_login_enabled']) && $user_extra['persistent_login_enabled'] === true;
                    $persistent_method = $user_extra['persistent_login_method'] ?? '';
                    
                    /* If user has persistent login enabled (from Google login), auto-login them */
                    if($persistent_enabled && $persistent_method === 'google') {
                        /* Generate token_code if not exists */
                        if(empty($user->token_code)) {
                            $user->token_code = md5($user->user_id . $user->password . time() . rand());
                            db()->where('user_id', $user->user_id)->update('users', ['token_code' => $user->token_code]);
                        }
                        
                        /* Start session if not already started */
                        session_start_if_not_started();
                        
                        /* Set session variables */
                        session_set('user_id', $user->user_id);
                        session_set('user_password_hash', md5($user->password));
                        
                        /* Set/refresh cookies for persistent login (1 year) */
                        setcookie('user_id', $user->user_id, time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                        setcookie('token_code', $user->token_code, time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                        setcookie('user_password_hash', md5($user->password), time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                        
                        /* Set authentication state */
                        self::$is_logged_in = true;
                        self::$user_id = $user->user_id;
                        self::$user = $user;
                        
                        return true;
                    }
                }
            }
            
            /* Fallback: Check if we have a user_id in session or cookie (even if expired) */
            $potential_user_id = session_get('user_id') ?? $_COOKIE['user_id'] ?? null;
            
            if($potential_user_id) {
                $user = (new User())->get_user_by_user_id($potential_user_id);
                
                if($user && $user->status == 1) {
                    /* Check if user has persistent_login_enabled */
                    $user_extra = json_decode($user->extra ?? '{}', true);
                    $persistent_enabled = isset($user_extra['persistent_login_enabled']) && $user_extra['persistent_login_enabled'] === true;
                    $persistent_method = $user_extra['persistent_login_method'] ?? '';
                    
                    /* If user has persistent login enabled (from Google login), auto-login them */
                    if($persistent_enabled && $persistent_method === 'google') {
                        /* Generate token_code if not exists */
                        if(empty($user->token_code)) {
                            $user->token_code = md5($user->user_id . $user->password . time() . rand());
                            db()->where('user_id', $user->user_id)->update('users', ['token_code' => $user->token_code]);
                        }
                        
                        /* Start session if not already started */
                        session_start_if_not_started();
                        
                        /* Set session variables */
                        session_set('user_id', $user->user_id);
                        session_set('user_password_hash', md5($user->password));
                        
                        /* Set/refresh cookies for persistent login (1 year) */
                        setcookie('user_id', $user->user_id, time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                        setcookie('token_code', $user->token_code, time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                        setcookie('user_password_hash', md5($user->password), time() + (365 * 24 * 60 * 60), COOKIE_PATH);
                        
                        /* Set device cookie to remember this device for future auto-login (10 years) */
                        setcookie('persistent_ip_device_user_id', $user->user_id, time() + (365 * 10 * 24 * 60 * 60), COOKIE_PATH);
                        
                        /* Set authentication state */
                        self::$is_logged_in = true;
                        self::$user_id = $user->user_id;
                        self::$user = $user;
                        
                        return true;
                    }
                }
            }
        }

        return false;
    }


    public static function is_admin() {

        if(!self::check()) {
            return false;
        }

        return self::$user->type > 0;
    }


    public static function guard($permission = 'user') {

        switch ($permission) {
            case 'guest':

                if(self::check()) {
                    redirect(isset($_GET['redirect']) ? $_GET['redirect'] : 'dashboard');
                }

                break;

            case 'user':

                if(!self::check()) {
                    /* Check IP-based auto-login for admin (also works for user pages) */
                    $allowed_ip = isset(settings()->security) && isset(settings()->security->biolink_edit_allowed_ip) ? settings()->security->biolink_edit_allowed_ip : '';
                    $current_ip = get_ip();
                    
                    /* If IP matches and user is not logged in, auto-login the first admin user */
                    if(!empty($allowed_ip) && $current_ip && $current_ip === $allowed_ip) {
                        /* Get the first active admin user */
                        $admin_user = db()->where('type', 1)->where('status', 1)->getOne('users', ['user_id', 'password', 'token_code']);
                        
                        if($admin_user) {
                            /* Start session if not already started */
                            session_start_if_not_started();
                            
                            /* Generate token_code if not exists */
                            if(empty($admin_user->token_code)) {
                                $admin_user->token_code = md5($admin_user->user_id . $admin_user->password . time() . rand());
                                db()->where('user_id', $admin_user->user_id)->update('users', ['token_code' => $admin_user->token_code]);
                            }
                            
                            /* Set session variables to log in the admin */
                            session_set('user_id', $admin_user->user_id);
                            session_set('user_password_hash', md5($admin_user->password));
                            
                            /* Set cookies for persistent login */
                            setcookie('user_id', $admin_user->user_id, time() + (86400 * 30), COOKIE_PATH);
                            setcookie('token_code', $admin_user->token_code, time() + (86400 * 30), COOKIE_PATH);
                            setcookie('user_password_hash', md5($admin_user->password), time() + (86400 * 30), COOKIE_PATH);
                            
                            /* Force re-check authentication */
                            self::$is_logged_in = null;
                            self::$user_id = null;
                            self::$user = null;
                            
                            /* Now check again */
                            if(self::check()) {
                                /* Admin is now logged in, continue with normal checks */
                            } else {
                                redirect('login?redirect=' . \Altum\Router::$original_request . (\Altum\Router::$original_request_query ? '?' . \Altum\Router::$original_request_query : null));
                            }
                        } else {
                            redirect('login?redirect=' . \Altum\Router::$original_request . (\Altum\Router::$original_request_query ? '?' . \Altum\Router::$original_request_query : null));
                        }
                    } else {
                        redirect('login?redirect=' . \Altum\Router::$original_request . (\Altum\Router::$original_request_query ? '?' . \Altum\Router::$original_request_query : null));
                    }
                }

                /* Check if user is banned */
                if(self::$user->status != 1) {
                    self::logout();
                }

                self::$login_guard_is_set = true;

                break;

            case 'admin':

                /* Check IP-based auto-login for admin */
                if(!self::check()) {
                    $allowed_ip = isset(settings()->security) && isset(settings()->security->biolink_edit_allowed_ip) ? settings()->security->biolink_edit_allowed_ip : '';
                    $current_ip = get_ip();
                    
                    /* If IP matches and user is not logged in, auto-login the first admin user */
                    if(!empty($allowed_ip) && $current_ip && $current_ip === $allowed_ip) {
                        /* Get the first active admin user */
                        $admin_user = db()->where('type', 1)->where('status', 1)->getOne('users', ['user_id', 'password', 'token_code']);
                        
                        if($admin_user) {
                            /* Start session if not already started */
                            session_start_if_not_started();
                            
                            /* Generate token_code if not exists */
                            if(empty($admin_user->token_code)) {
                                $admin_user->token_code = md5($admin_user->user_id . $admin_user->password . time() . rand());
                                db()->where('user_id', $admin_user->user_id)->update('users', ['token_code' => $admin_user->token_code]);
                            }
                            
                            /* Set session variables to log in the admin */
                            session_set('user_id', $admin_user->user_id);
                            session_set('user_password_hash', md5($admin_user->password));
                            
                            /* Set cookies for persistent login */
                            setcookie('user_id', $admin_user->user_id, time() + (86400 * 30), COOKIE_PATH);
                            setcookie('token_code', $admin_user->token_code, time() + (86400 * 30), COOKIE_PATH);
                            setcookie('user_password_hash', md5($admin_user->password), time() + (86400 * 30), COOKIE_PATH);
                            
                            /* Force re-check authentication */
                            self::$is_logged_in = null;
                            self::$user_id = null;
                            self::$user = null;
                            
                            /* Now check again */
                            if(self::check()) {
                                /* Admin is now logged in, continue with normal checks */
                            } else {
                                redirect('login?redirect=' . \Altum\Router::$original_request . (\Altum\Router::$original_request_query ? '?' . \Altum\Router::$original_request_query : null));
                            }
                        } else {
                            redirect('login?redirect=' . \Altum\Router::$original_request . (\Altum\Router::$original_request_query ? '?' . \Altum\Router::$original_request_query : null));
                        }
                    } else {
                        redirect('login?redirect=' . \Altum\Router::$original_request . (\Altum\Router::$original_request_query ? '?' . \Altum\Router::$original_request_query : null));
                    }
                }

                /* Check if user is banned */
                if(self::$user->status != 1) {
                    self::logout();
                }

                /* Check if user is admin */
                if(self::$user->type != 1) {
                    redirect('dashboard');
                }

                self::$login_guard_is_set = true;

                break;
        }

    }


    public static function logout($page = '') {

        if(self::check()) {
            db()->where('user_id', self::$user_id)->update('users', ['token_code' => '']);

            /* Clear the cache */
            cache()->deleteItemsByTag('user_id=' . self::$user_id);
        }

        session_destroy();
        setcookie('user_id', '', time()-30, COOKIE_PATH);
        setcookie('token_code', '', time()-30, COOKIE_PATH);
        setcookie('user_password_hash', '', time()-30, COOKIE_PATH);
        setcookie('spotlight_has_results', '', time()-30, COOKIE_PATH);
        
        /* Note: We do NOT clear persistent_ip_device_user_id cookie on logout
         * This allows the device to auto-login again when on the persistent IP */

        if($page !== false) {
            redirect($page);
        }
    }

    public static function get_authorization_bearer() {
        $headers = getallheaders();
        $authorization_header = $headers['Authorization'] ?? $headers['authorization'] ?? null;

        if(!$authorization_header) {
            return null;
        }

        if(!preg_match('/Bearer\s(\S+)/', $authorization_header, $matches)) {
            return null;
        }

        return $matches[1];
    }
}
