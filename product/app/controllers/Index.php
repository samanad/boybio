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

use Altum\Models\Domain;

defined('ALTUMCODE') || die();

class Index extends Controller {

    public function index() {

        /* Don't redirect if this is a PWA launch (utm_source=pwa) - always show the start page */
        $is_pwa_launch = isset($_GET['utm_source']) && $_GET['utm_source'] === 'pwa';
        
        /* Custom index redirect if set, but skip for PWA launches */
        if(!empty(settings()->main->index_url) && !$is_pwa_launch) {
            header('Location: ' . settings()->main->index_url); die();
        }

        /* Plans View */
        $view = new \Altum\View('partials/plans', (array) $this);
        $this->add_view_content('plans', $view->run());

        /* Check if the cache exists */
        $cache_instance = cache()->getItem('index_stats');

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            $total_users = database()->query("SELECT MAX(`user_id`) AS `total` FROM `users`")->fetch_object()->total ?? 0;
            $total_links = database()->query("SELECT MAX(`link_id`) AS `total` FROM `links`")->fetch_object()->total ?? 0;
            $total_qr_codes = database()->query("SELECT MAX(`qr_code_id`) AS `total` FROM `qr_codes`")->fetch_object()->total ?? 0;
            $total_track_links = database()->query("SELECT MAX(`id`) AS `total` FROM `track_links`")->fetch_object()->total ?? 0;
            if(\Altum\Plugin::is_active('aix')) {
                if(settings()->aix->documents_is_enabled) {
                    $total_documents = database()->query("SELECT MAX(`document_id`) AS `total` FROM `documents`")->fetch_object()->total ?? 0;
                }

                if(settings()->aix->images_is_enabled && settings()->aix->images_display_latest_on_index) {
                    $total_images = database()->query("SELECT MAX(`image_id`) AS `total` FROM `images`")->fetch_object()->total ?? 0;
                    $images = db()->orderBy('image_id', 'DESC')->get('images', 16);
                }
            }
            $stats = [
                'total_users' => $total_users,
                'total_links' => $total_links,
                'total_qr_codes' => $total_qr_codes,
                'total_track_links' => $total_track_links,
                'total_documents' => $total_documents ?? null,
                'total_images' => $total_images ?? null,
                'images' => $images ?? [],
            ];

            /* Save to cache */
            cache()->save($cache_instance->set($stats)->expiresAfter(3600));

        } else {

            /* Get cache */
            $stats = $cache_instance->get();
            extract($stats);

        }

        if(settings()->main->display_index_latest_blog_posts) {
            $language = \Altum\Language::$name;

            /* Blog posts query */
            $blog_posts_result_query = "
                SELECT * 
                FROM `blog_posts`
                WHERE (`language` = '{$language}' OR `language` IS NULL) AND `is_published` = 1 
                ORDER BY `blog_post_id` DESC
                LIMIT 3
            ";

            $blog_posts = \Altum\Cache::cache_function_result('blog_posts?hash=' . md5($blog_posts_result_query), 'blog_posts', function() use ($blog_posts_result_query) {
                $blog_posts_result = database()->query($blog_posts_result_query);

                /* Iterate over the blog posts */
                $blog_posts = [];

                while($row = $blog_posts_result->fetch_object()) {
                    /* Transform content if needed */
                    $row->content = json_decode($row->content) ? convert_editorjs_json_to_html($row->content) : output_blog_post_content($row->content);

                    $blog_posts[] = $row;
                }

                return $blog_posts;
            });
        }

        $tools_categories = require APP_PATH . 'includes/tools/categories.php';
        $enabled_tools = count(array_filter((array) settings()->tools->available_tools));

        /* Get the available domains to use for claim URL */
        $all_domains = (new Domain())->get_available_additional_domains();
        $claim_url_available_domains_raw = settings()->links->claim_url_available_domains ?? null;
        /* Convert object to array if needed */
        $claim_url_available_domains = [];
        if($claim_url_available_domains_raw) {
            if(is_object($claim_url_available_domains_raw)) {
                $claim_url_available_domains = json_decode(json_encode($claim_url_available_domains_raw), true);
            } else {
                $claim_url_available_domains = $claim_url_available_domains_raw;
            }
        }
        
        /* Filter domains based on admin settings */
        $domains = [];
        if(!empty($claim_url_available_domains)) {
            foreach($all_domains as $domain_id => $domain) {
                if(in_array($domain_id, $claim_url_available_domains)) {
                    $domains[$domain_id] = $domain;
                }
            }
        } else {
            /* If no domains selected, show all (backward compatibility) */
            $domains = $all_domains;
        }
        
        /* Add main domain if enabled and selected */
        if(settings()->links->main_domain_is_enabled && in_array(0, $claim_url_available_domains)) {
            $site_url_parsed = parse_url(SITE_URL);
            $main_domain_host = $site_url_parsed['host'] ?? '';
            if($main_domain_host) {
                $main_domain = new \stdClass();
                $main_domain->domain_id = 0;
                $main_domain->scheme = $site_url_parsed['scheme'] ?? 'https://';
                $main_domain->host = $main_domain_host;
                $main_domain->url = SITE_URL;
                $main_domain->type = 0;
                $domains[0] = $main_domain;
            }
        } elseif(settings()->links->main_domain_is_enabled && empty($claim_url_available_domains)) {
            /* If no domains selected but main domain is enabled, show it (backward compatibility) */
            $site_url_parsed = parse_url(SITE_URL);
            $main_domain_host = $site_url_parsed['host'] ?? '';
            if($main_domain_host) {
                $main_domain = new \stdClass();
                $main_domain->domain_id = 0;
                $main_domain->scheme = $site_url_parsed['scheme'] ?? 'https://';
                $main_domain->host = $main_domain_host;
                $main_domain->url = SITE_URL;
                $main_domain->type = 0;
                $domains[0] = $main_domain;
            }
        }

        /* Main View */
        $view = new \Altum\View('index/index', (array) $this);
        $this->add_view_content('content', $view->run([
            'total_users' => $total_users,
            'total_links' => $total_links,
            'total_qr_codes' => $total_qr_codes,
            'total_track_links' => $total_track_links,
            'total_documents' => $total_documents ?? null,
            'total_images' => $total_images ?? null,
            'images' => $images ?? null,
            'blog_posts' => $blog_posts ?? [],
            'tools_categories' => $tools_categories,
            'enabled_tools' => $enabled_tools,
            'domains' => $domains,
        ]));

    }

}
