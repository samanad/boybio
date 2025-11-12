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

namespace Altum\Models;

use Altum\Language;

defined('ALTUMCODE') || die();

class Page extends Model {

    public function get_pages($position) {

        $pages_data = [];

        $cache_instance = cache()->getItem('pages_all');

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {
            $result = null;
            
            // Try with ORDER BY first
            try {
                $result = database()->query('SELECT `url`, `title`, `type`, `open_in_new_tab`, `language`, `icon`, `position`, `plans_ids` FROM `pages` WHERE `is_published` = 1 ORDER BY `pages`.`order` ASC');
            } catch(\Exception $e) {
                // If ORDER BY or plans_ids fails, try without plans_ids
                try {
                    $result = database()->query('SELECT `url`, `title`, `type`, `open_in_new_tab`, `language`, `icon`, `position` FROM `pages` WHERE `is_published` = 1 ORDER BY `pages`.`order` ASC');
                } catch(\Exception $e2) {
                    // If ORDER BY still fails, try without it
                    try {
                        $result = database()->query('SELECT `url`, `title`, `type`, `open_in_new_tab`, `language`, `icon`, `position` FROM `pages` WHERE `is_published` = 1');
                    } catch(\Exception $e3) {
                        // Last resort: minimal query
                        $result = database()->query('SELECT `url`, `title`, `type`, `position` FROM `pages` WHERE `is_published` = 1');
                    }
                }
            }

            if($result && $result !== false) {
            while($row = $result->fetch_object()) {
                    // Handle plans_ids if column exists, otherwise set to empty
                    if(isset($row->plans_ids)) {
                $row->plans_ids = json_decode($row->plans_ids ?? '');
                    } else {
                        $row->plans_ids = [];
                    }
                    // Set defaults for missing columns
                    if(!isset($row->open_in_new_tab)) {
                        $row->open_in_new_tab = 1;
                    }
                    if(!isset($row->language)) {
                        $row->language = null;
                    }
                    if(!isset($row->icon)) {
                        $row->icon = null;
                    }

                $pages_data[] = $row;
                }
            }

            cache()->save($cache_instance->set($pages_data)->expiresAfter(CACHE_DEFAULT_SECONDS)->addTag('pages'));

        } else {

            /* Get cache */
            $pages_data = $cache_instance->get();

        }

        $filtered_pages = [];

        foreach($pages_data as $page) {

            /* Only keep pages that match the requested position */
            if($page->position != $position) {
                continue;
            }

            /* Make sure the language of the page still exists */
            if($page->language && !isset(\Altum\Language::$active_languages[$page->language])) {
                continue;
            }

            if($page->type == 'internal') {
                $page->target = '_self';
                $page->url = SITE_URL . ($page->language ? \Altum\Language::$active_languages[$page->language] . '/' : null) . 'page/' . $page->url;
            } else {
                $page->target = $page->open_in_new_tab ? '_blank' : '_self';
            }

            /* Check language */
            if($page->language && $page->language != Language::$name) {
                continue;
            }

            /* Filter by plan if needed */
            if(!empty($page->plans_ids)) {
                if(!is_logged_in()) continue;

                if(!in_array(user()->plan_id, $page->plans_ids)) {
                    continue;
                }
            }

            $filtered_pages[] = $page;
        }

        return $filtered_pages;
    }

}
