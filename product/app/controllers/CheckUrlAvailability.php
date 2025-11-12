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

namespace Altum\Controllers;

use Altum\Response;

defined('ALTUMCODE') || die();

class CheckUrlAvailability extends Controller {

    public function index() {

        if(!empty($_POST) && isset($_POST['url'])) {
            
            /* Check CSRF */
            if(!\Altum\Csrf::check('global_token')) {
                Response::json(l('global.error_message.invalid_csrf_token'), 'error');
            }
            
            $url = get_slug($_POST['url'], '-', false);
            $domain_id = isset($_POST['domain_id']) && $_POST['domain_id'] ? (int) $_POST['domain_id'] : null;
            
            if(empty($url)) {
                Response::json(l('global.error_message.empty_field'), 'error');
            }
            
            /* Check if URL is available */
            $domain_id_where = $domain_id ? "AND `domain_id` = {$domain_id}" : "AND (`domain_id` IS NULL OR `domain_id` = 0)";
            $is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$url}' {$domain_id_where}")->num_rows;
            
            /* Check if URL is blacklisted */
            $is_blacklisted = false;
            if(array_key_exists($url, \Altum\Router::$routes['']) || in_array($url, \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $url)) {
                $is_blacklisted = true;
            }
            if(in_array(mb_strtolower($url), settings()->links->blacklisted_keywords ?? [])) {
                $is_blacklisted = true;
            }
            
            /* If URL is available and not blacklisted, and subdirectory redirect is enabled */
            $subdirectory_redirect_enabled = (isset(settings()->main->subdirectory_redirect_is_enabled) && settings()->main->subdirectory_redirect_is_enabled) ||
                                             (isset(settings()->links->subdirectory_redirect_is_enabled) && settings()->links->subdirectory_redirect_is_enabled);
            $subdirectory_redirect_base_url = !empty(settings()->main->subdirectory_redirect_base_url) ? settings()->main->subdirectory_redirect_base_url : 
                                               (!empty(settings()->links->subdirectory_redirect_base_url) ? settings()->links->subdirectory_redirect_base_url : '');
            
            if(!$is_existing_link && !$is_blacklisted && 
               $subdirectory_redirect_enabled &&
               !empty($subdirectory_redirect_base_url)) {
                
                /* Build redirect URL */
                $base_url = rtrim($subdirectory_redirect_base_url, '/');
                $redirect_url = $base_url . '/' . $url;
                
                /* Store message in session */
                $_SESSION['subdirectory_available_message'] = sprintf(l('index.subdirectory_available_message'), $url);
                $_SESSION['subdirectory_available_url'] = $redirect_url;
                
                Response::json('', 'success', [
                    'available' => true,
                    'redirect_url' => $redirect_url,
                    'message' => sprintf(l('index.subdirectory_available_message'), $url)
                ]);
            }
            
            /* URL is not available or feature is disabled */
            Response::json('', 'success', [
                'available' => false
            ]);
        }
        
        Response::json(l('global.error_message.basic'), 'error');
    }

}

