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
            $domain_id_where = $domain_id ? "AND `domain_id` = " . (int) $domain_id : "AND (`domain_id` IS NULL OR `domain_id` = 0)";
            $escaped_url = database()->real_escape_string($url);
            $is_existing_link = database()->query("SELECT `link_id` FROM `links` WHERE `url` = '{$escaped_url}' {$domain_id_where}")->num_rows;
            
            /* Check if URL is blacklisted/banned */
            $is_blacklisted = false;
            if(array_key_exists($url, \Altum\Router::$routes['']) || in_array($url, \Altum\Language::$active_languages) || file_exists(ROOT_PATH . $url)) {
                $is_blacklisted = true;
            }
            if(in_array(mb_strtolower($url), settings()->links->blacklisted_keywords ?? [])) {
                $is_blacklisted = true;
            }
            
            /* Determine status */
            if($is_blacklisted) {
                /* URL is banned/blacklisted */
                Response::json('', 'success', [
                    'status' => 'banned',
                    'message' => l('index.claim_url_banned')
                ]);
            } elseif($is_existing_link) {
                /* URL is already used */
                Response::json('', 'success', [
                    'status' => 'used',
                    'message' => l('index.claim_url_used')
                ]);
            } else {
                /* URL is available */
                /* Get domain info for redirect URL */
                $full_url = SITE_URL . $url;
                if($domain_id) {
                    $domain = db()->where('domain_id', $domain_id)->getOne('domains', ['scheme', 'host']);
                    if($domain) {
                        $full_url = $domain->scheme . $domain->host . '/' . $url;
                    }
                }
                
                Response::json('', 'success', [
                    'status' => 'available',
                    'message' => l('index.claim_url_available'),
                    'full_url' => $full_url,
                    'redirect_url' => url('register') . '?claim-url=' . urlencode($url) . ($domain_id ? '&domain-id=' . $domain_id : '')
                ]);
            }
        }
        
        Response::json(l('global.error_message.basic'), 'error');
    }

}

