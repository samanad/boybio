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

defined('ALTUMCODE') || die();

class Directory extends Controller {

    public function index() {
        if(!settings()->links->biolinks_is_enabled || !settings()->links->directory_is_enabled) {
            redirect('not-found');
        }

        /* Private biolinks: directory is admin-only (no public browse / export / search) */
        if(biolinks_discovery_is_prevented()) {
            $user = \Altum\Authentication::$user ?? null;
            if(!is_logged_in() || !$user || $user->type != 1) {
                redirect('not-found');
            }
        }

        /* Prepare the filtering system */
        $filters = (new \Altum\Filters(['is_verified'], ['url'], ['clicks', 'url']));
        $user = \Altum\Authentication::$user ?? null;
        $user_preferences = $user->preferences ?? null;
        $filters->set_default_order_by('clicks', $user_preferences->default_order_type ?? settings()->main->default_order_type);
        $filters->set_default_results_per_page($user_preferences->default_results_per_page ?? settings()->main->default_results_per_page);

        /* Which bio link pages to display based on user role? */
        $is_admin = is_logged_in() && $user && $user->type == 1;
        $is_user = is_logged_in() && $user && $user->type != 1;
        
        $directory_display_join = '';
        
        if($is_admin) {
            /* Admin always sees only verified badge links (requested by users) */
            $directory_display_where = 'AND `is_verified` = 1';
        } elseif($is_user) {
            /* Users always see explore things links (set by admin) */
            $directory_display_where = 'AND `is_explore_things` = 1';
        } else {
            /* Guests see links from the directory_guest_links list */
            $directory_guest_links = settings()->links->directory_guest_links ?? [];
            
            if(empty($directory_guest_links)) {
                /* No links configured, show nothing */
                $directory_display_where = 'AND 1 = 0';
                $directory_display_join = '';
            } else {
                /* Build WHERE clause to match links in the allowed list */
                $allowed_conditions = [];
                $needs_domains_join = false;
                
                foreach($directory_guest_links as $domain_host => $links) {
                    if(empty($links)) continue;
                    
                    /* For main domain (domain_id = 0 or NULL) */
                    if($domain_host == (parse_url(SITE_URL, PHP_URL_HOST))) {
                        $link_urls = [];
                        foreach($links as $link_url) {
                            $link_url = trim($link_url);
                            if(empty($link_url)) continue;
                            $link_urls[] = "'" . database()->real_escape_string($link_url) . "'";
                        }
                        if(!empty($link_urls)) {
                            $allowed_conditions[] = "((`links`.`domain_id` = 0 OR `links`.`domain_id` IS NULL) AND `links`.`url` IN (" . implode(', ', $link_urls) . "))";
                        }
                    } else {
                        /* For custom domains */
                        $needs_domains_join = true;
                        $link_urls = [];
                        foreach($links as $link_url) {
                            $link_url = trim($link_url);
                            if(empty($link_url)) continue;
                            $link_urls[] = "'" . database()->real_escape_string($link_url) . "'";
                        }
                        if(!empty($link_urls)) {
                            $allowed_conditions[] = "(`domains`.`host` = '" . database()->real_escape_string($domain_host) . "' AND `links`.`url` IN (" . implode(', ', $link_urls) . "))";
                        }
                    }
                }
                
                if(!empty($allowed_conditions)) {
                    $directory_display_where = 'AND (' . implode(' OR ', $allowed_conditions) . ')';
                    $directory_display_join = $needs_domains_join ? 'LEFT JOIN `domains` ON `links`.`domain_id` = `domains`.`domain_id`' : '';
                } else {
                    /* No valid links, show nothing */
                    $directory_display_where = 'AND 1 = 0';
                    $directory_display_join = '';
                }
            }
        }

        /* Prepare the paginator */
        $total_rows_query = "SELECT COUNT(*) AS `total` FROM `links` {$directory_display_join} WHERE `links`.`type` = 'biolink' AND `links`.`is_enabled` = 1 AND `links`.`directory_is_enabled` = 1 {$directory_display_where} {$filters->get_sql_where()}";
        $total_rows_result = database()->query($total_rows_query);
        $total_rows = $total_rows_result ? $total_rows_result->fetch_object()->total ?? 0 : 0;
        $paginator = (new \Altum\Paginator($total_rows, $filters->get_results_per_page(), $_GET['page'] ?? 1, url('directory?' . $filters->get_get() . '&page=%d')));

        /* Get the links list for the project */
        $links_result = database()->query("
            SELECT 
                `links`.*, `domains`.`scheme`, `domains`.`host`, `domains`.`link_id` as `domain_link_id`
            FROM 
                `links`
            LEFT JOIN 
                `domains` ON `links`.`domain_id` = `domains`.`domain_id`
            WHERE 
                `links`.`type` = 'biolink'
                AND `links`.`is_enabled` = 1
                AND `links`.`directory_is_enabled` = 1
                {$directory_display_where}
                {$filters->get_sql_where('links')}
                {$filters->get_sql_order_by('links')}
            {$paginator->get_sql_limit()}
        ");

        /* Iterate over the links */
        $links = [];

        while($row = $links_result->fetch_object()) {
            $row->full_url = $row->domain_id ? $row->scheme . $row->host . '/' . ($row->domain_link_id == $row->link_id ? null : $row->url) : SITE_URL . $row->url;
            $row->settings = json_decode($row->settings ?? '');

            $links[] = $row;
        }

        /* Export handler */
        process_export_csv($links, ['url', 'full_url', 'clicks', 'is_verified'], sprintf(l('links.title')));
        process_export_json($links, ['url', 'full_url', 'clicks', 'is_verified'], sprintf(l('links.title')));

        /* Prepare the pagination view */
        $pagination = (new \Altum\View('partials/pagination', (array) $this))->run(['paginator' => $paginator]);

        /* Prepare the view */
        $data = [
            'links'             => $links,
            'pagination'        => $pagination,
            'filters'           => $filters,
        ];

        $view = new \Altum\View('directory/index', (array) $this);

        $this->add_view_content('content', $view->run($data));

    }

}


