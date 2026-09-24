<?php
/*
 * Telegram-like resilience & authenticity helpers (offline cache, official hosts).
 */

defined('ALTUMCODE') || die();

/**
 * Official hostnames allowed to run the full PWA / resilience client.
 * Configure via settings()->main->official_hosts (comma/newline separated).
 */
function resilience_official_hosts(): array {
    $hosts = [];

    $configured = (string) (settings()->main->official_hosts ?? '');
    foreach(preg_split('/[\s,]+/', $configured) as $part) {
        $part = mb_strtolower(trim($part));
        $part = preg_replace('/^https?:\/\//', '', $part);
        $part = rtrim($part, '/');
        if($part !== '') {
            $hosts[] = $part;
        }
    }

    /* Always include the configured site host */
    $site_host = mb_strtolower((string) (parse_url(SITE_URL, PHP_URL_HOST) ?? ''));
    if($site_host !== '') {
        $hosts[] = $site_host;
    }

    return array_values(array_unique(array_filter($hosts)));
}

function resilience_is_official_host(?string $host = null): bool {
    $host = mb_strtolower((string) ($host ?? ($_SERVER['HTTP_HOST'] ?? '')));
    $host = preg_replace('/:\d+$/', '', $host);
    if($host === '') {
        return false;
    }

    $official = resilience_official_hosts();
    if(!$official) {
        return true;
    }

    foreach($official as $allowed) {
        if($host === $allowed || string_ends_with($host, '.' . $allowed)) {
            return true;
        }
    }

    return false;
}

function resilience_signing_secret(): string {
    $license = (string) (settings()->license->license ?? '');
    $cron = (string) (settings()->cron->key ?? '');
    return hash('sha256', $license . '|' . $cron . '|cloub-official-hosts|v1');
}

/** Signed official-host list for clients (anti-mirror). */
function resilience_signed_hosts_payload(): array {
    $hosts = resilience_official_hosts();
    $issued_at = time();
    $expires_at = $issued_at + (60 * 60 * 24 * 7);
    $body = json_encode([
        'hosts' => $hosts,
        'issued_at' => $issued_at,
        'expires_at' => $expires_at,
        'site_url' => SITE_URL,
    ], JSON_UNESCAPED_SLASHES);
    $signature = hash_hmac('sha256', $body, resilience_signing_secret());

    return [
        'payload' => $body,
        'signature' => $signature,
        'algorithm' => 'HMAC-SHA256',
        'hosts' => $hosts,
        'issued_at' => $issued_at,
        'expires_at' => $expires_at,
        'site_url' => SITE_URL,
    ];
}

function resilience_client_config(bool $is_dashboard = false): array {
    return [
        'enabled' => true,
        'is_dashboard' => $is_dashboard,
        'site_url' => SITE_URL,
        'official_hosts' => resilience_official_hosts(),
        'is_official_host' => resilience_is_official_host(),
        'authenticity_url' => url('official'),
        'hosts_json_url' => url('official') . '?format=json',
        'sw_url' => SITE_URL . 'sw.js',
        'cache_prefix' => 'cloub-resilience-v1',
        'passcode_enabled' => $is_dashboard,
        'connection_banner' => true,
        'iframe_guard' => true,
        'soft_refresh' => true,
        'offline_message' => l('resilience.offline_banner') ?: 'Offline — showing saved content',
        'updating_message' => l('resilience.updating_banner') ?: 'Updating…',
        'mirror_warning' => l('resilience.mirror_warning') ?: 'Unofficial host detected.',
        'verify_label' => l('resilience.verify_link') ?: 'Verify authenticity',
    ];
}

/**
 * Email the account owner when a new IP/device signs in (Telegram-like login alert).
 */
function resilience_send_login_alert(int $user_id, string $method = 'classic', string $previous_ip = '', int $previous_total_logins = 0, $user = null): void {
    try {
        if(!$user) {
            $user = db()->where('user_id', $user_id)->getOne('users', [
                'user_id', 'email', 'name', 'anti_phishing_code', 'language'
            ]);
        }
        if(!$user || empty($user->email)) {
            return;
        }

        $ip = get_ip();

        /* Skip first login and same-IP re-login */
        if($previous_total_logins < 1) {
            return;
        }
        if($previous_ip !== '' && $previous_ip === $ip) {
            return;
        }

        $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT'] ?? '');
        $browser = $whichbrowser->browser->name ?? 'unknown';
        $os = $whichbrowser->os->name ?? 'unknown';
        $device = get_this_device_type();

        $email_template = get_email_template(
            [],
            l('global.emails.user_login_alert.subject'),
            [
                '{{NAME}}' => $user->name,
                '{{IP}}' => $ip,
                '{{DEVICE}}' => $device,
                '{{BROWSER}}' => $browser,
                '{{OS}}' => $os,
                '{{METHOD}}' => $method,
                '{{DATETIME}}' => \Altum\Date::get(get_date(), 1),
                '{{SESSIONS_LINK}}' => url('account-sessions'),
            ],
            l('global.emails.user_login_alert.body')
        );

        send_mail(
            $user->email,
            $email_template->subject,
            $email_template->body,
            ['anti_phishing_code' => $user->anti_phishing_code ?? null, 'language' => $user->language ?? null]
        );
    } catch(\Throwable $e) {
        /* never break login */
    }
}
