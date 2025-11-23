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

/* Simple wrapper for phpFastCache */

defined('ALTUMCODE') || die();

class Cache {
    public static $adapter;

    public static function initialize($force_enable = false) {

        $driver = $force_enable ? 'Files' : (CACHE ? 'Files' : 'Devnull');

        /* Cache adapter for phpFastCache */
        if($driver == 'Files') {
            $cache_path = UPLOADS_PATH . 'cache';
            
            /* Ensure cache directory exists and is writable */
            if(!is_dir($cache_path)) {
                @mkdir($cache_path, 0777, true);
            }
            
            /* Try to make cache directory writable if it's not */
            if(is_dir($cache_path) && !is_writable($cache_path)) {
                @chmod($cache_path, 0777);
            }
            
            $config = new \Phpfastcache\Drivers\Files\Config([
                'securityKey' => PRODUCT_KEY,
                'path' => $cache_path,
                'preventCacheSlams' => true,
                'cacheSlamsTimeout' => 20,
                'secureFileManipulation' => true
            ]);
        } else {
            $config = new \Phpfastcache\Config\Config([
                'path' => UPLOADS_PATH . 'cache',
            ]);
        }

        \Phpfastcache\CacheManager::setDefaultConfig($config);

        try {
            self::$adapter = \Phpfastcache\CacheManager::getInstance($driver);
        } catch(\Exception $e) {
            /* If cache initialization fails, fall back to Devnull driver */
            if($driver == 'Files') {
                $config = new \Phpfastcache\Config\Config([
                    'path' => UPLOADS_PATH . 'cache',
                ]);
                \Phpfastcache\CacheManager::setDefaultConfig($config);
                self::$adapter = \Phpfastcache\CacheManager::getInstance('Devnull');
            } else {
                throw $e;
            }
        }
    }

    public static function cache_function_result($key, $tag, $function_to_cache, $cached_seconds = CACHE_DEFAULT_SECONDS) {
        if(!$cached_seconds) return $function_to_cache();

        /* Try to check if the user posts exists via the cache */
        $cache_instance = cache()->getItem($key);

        /* Set cache if not existing */
        if(is_null($cache_instance->get())) {

            $result = $function_to_cache();

            $cache_item = $cache_instance->set($result)->expiresAfter($cached_seconds);

            if($tag) {
                if(is_array($tag)) {
                    foreach($tag as $tag_key) $cache_item->addTag($tag_key);
                } else {
                    $cache_item->addTag($tag);
                }
            }

            cache()->save($cache_item);

        } else {

            /* Get cache */
            $result = $cache_instance->get();

        }

        return $result;
    }

}
