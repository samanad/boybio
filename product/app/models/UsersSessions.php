<?php
/*
 * Custom: track alive user/admin sessions for admin users list.
 */

namespace Altum\Models;

defined('ALTUMCODE') || die();

class UsersSessions extends Model {

    public const ALIVE_MINUTES = 30;

    public static function ensure_table(): void {
        static $done = false;
        if($done) return;
        $done = true;

        database()->query("
            CREATE TABLE IF NOT EXISTS `users_sessions` (
                `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                `user_id` bigint unsigned NOT NULL,
                `session_id` varchar(128) NOT NULL,
                `ip` varchar(64) DEFAULT NULL,
                `continent_code` varchar(8) DEFAULT NULL,
                `country_code` varchar(8) DEFAULT NULL,
                `city_name` varchar(64) DEFAULT NULL,
                `device_type` varchar(32) DEFAULT NULL,
                `os_name` varchar(64) DEFAULT NULL,
                `browser_name` varchar(64) DEFAULT NULL,
                `browser_language` varchar(16) DEFAULT NULL,
                `user_agent` varchar(512) DEFAULT NULL,
                `is_admin` tinyint(1) NOT NULL DEFAULT 0,
                `admin_impersonation` tinyint(1) NOT NULL DEFAULT 0,
                `datetime` datetime DEFAULT NULL,
                `last_activity` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `session_id` (`session_id`),
                KEY `user_id` (`user_id`),
                KEY `last_activity` (`last_activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    public static function collect_meta(): array {
        $ip = get_ip();
        $continent_code = $country_code = $city_name = null;
        try {
            $maxmind = (get_maxmind_reader_city())->get($ip);
            $continent_code = $maxmind['continent']['code'] ?? null;
            $country_code = $maxmind['country']['iso_code'] ?? null;
            $city_name = $maxmind['city']['names']['en'] ?? null;
        } catch(\Exception $exception) {
            /* ignore geo failures */
        }

        $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT'] ?? '');
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        if(mb_strlen($ua) > 512) {
            $ua = mb_substr($ua, 0, 512);
        }

        return [
            'ip' => $ip,
            'continent_code' => $continent_code,
            'country_code' => $country_code,
            'city_name' => $city_name,
            'device_type' => get_this_device_type(),
            'os_name' => $whichbrowser->os->name ?? null,
            'browser_name' => $whichbrowser->browser->name ?? null,
            'browser_language' => isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null,
            'user_agent' => $ua,
        ];
    }

    public static function upsert_current(int $user_id, bool $is_admin = false): void {
        self::ensure_table();

        if(session_status() !== PHP_SESSION_ACTIVE) {
            if(function_exists('session_start_if_not_started')) {
                session_start_if_not_started();
            } else {
                @session_start();
            }
        }

        $session_id = session_id();
        if(!$session_id || !$user_id) {
            return;
        }

        $meta = self::collect_meta();
        $now = get_date();
        $admin_impersonation = function_exists('session_has') ? (session_has('admin_user_id') ? 1 : 0) : (isset($_SESSION['admin_user_id']) ? 1 : 0);

        $existing = db()->where('session_id', $session_id)->getOne('users_sessions', ['id']);

        $payload = array_merge($meta, [
            'user_id' => $user_id,
            'session_id' => $session_id,
            'is_admin' => $is_admin ? 1 : 0,
            'admin_impersonation' => $admin_impersonation,
            'last_activity' => $now,
        ]);

        if($existing) {
            db()->where('id', $existing->id)->update('users_sessions', $payload);
        } else {
            $payload['datetime'] = $now;
            db()->insert('users_sessions', $payload);
        }
    }

    public static function touch_current(int $user_id): void {
        self::ensure_table();
        $session_id = session_id();
        if(!$session_id || !$user_id) {
            return;
        }

        $existing = db()->where('session_id', $session_id)->getOne('users_sessions', ['id']);
        if($existing) {
            db()->where('id', $existing->id)->update('users_sessions', [
                'last_activity' => get_date(),
                'ip' => get_ip(),
            ]);
            return;
        }

        $user = db()->where('user_id', $user_id)->getOne('users', ['type']);
        self::upsert_current($user_id, !empty($user->type));
    }

    public static function end_current(): void {
        self::ensure_table();
        $session_id = session_id();
        if(!$session_id) {
            return;
        }
        db()->where('session_id', $session_id)->delete('users_sessions');
    }

    public static function prune(int $alive_minutes = self::ALIVE_MINUTES): void {
        self::ensure_table();
        /* Keep a longer history for popup (7 days), only count recent as alive */
        $cutoff = (new \DateTime())->modify('-7 days')->format('Y-m-d H:i:s');
        db()->where('last_activity', $cutoff, '<')->delete('users_sessions');
    }

    public static function alive_cutoff_sql(int $alive_minutes = self::ALIVE_MINUTES): string {
        return (new \DateTime())->modify('-' . $alive_minutes . ' minutes')->format('Y-m-d H:i:s');
    }

    /** @return array<int,int> user_id => count */
    public static function alive_counts_for_users(array $user_ids, int $alive_minutes = self::ALIVE_MINUTES): array {
        self::ensure_table();
        $user_ids = array_values(array_filter(array_map('intval', $user_ids)));
        if(!$user_ids) {
            return [];
        }

        $cutoff = self::alive_cutoff_sql($alive_minutes);
        $in = implode(',', $user_ids);
        $counts = array_fill_keys($user_ids, 0);
        $result = database()->query("
            SELECT `user_id`, COUNT(*) AS `total`
            FROM `users_sessions`
            WHERE `user_id` IN ({$in})
              AND `last_activity` >= '{$cutoff}'
            GROUP BY `user_id`
        ");
        while($row = $result->fetch_object()) {
            $counts[(int) $row->user_id] = (int) $row->total;
        }
        return $counts;
    }

    public static function get_alive_for_user(int $user_id, int $alive_minutes = self::ALIVE_MINUTES): array {
        self::ensure_table();
        $cutoff = self::alive_cutoff_sql($alive_minutes);
        $rows = [];
        $result = database()->query("
            SELECT *
            FROM `users_sessions`
            WHERE `user_id` = {$user_id}
              AND `last_activity` >= '{$cutoff}'
            ORDER BY `last_activity` DESC
            LIMIT 100
        ");
        while($row = $result->fetch_object()) {
            $rows[] = $row;
        }
        return $rows;
    }
}
