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

defined('ALTUMCODE') || die();

ini_set('display_errors', 'Off');

/* Error handlers */
function altumcode_shutdown_handler() {
    $last_error = error_get_last();

    if($last_error && ($last_error['type'] === E_ERROR || $last_error['type'] === E_CORE_ERROR || $last_error['type'] === E_PARSE || $last_error['type'] === E_COMPILE_ERROR)) {
        /* Log the actual error so you can fix it (e.g. in uploads/logs/fatal_YYYY-MM-DD.log) */
        if (defined('ROOT_PATH')) {
            $log_dir = ROOT_PATH . 'uploads/logs';
            if (!is_dir($log_dir)) {
                @mkdir($log_dir, 0777, true);
            }
            if (is_dir($log_dir) && is_writable($log_dir)) {
                $log_file = $log_dir . '/fatal_' . date('Y-m-d') . '.log';
                $line = date('Y-m-d H:i:s') . ' ' . $last_error['type'] . ' ' . $last_error['message'] . ' in ' . $last_error['file'] . ' on line ' . $last_error['line'] . "\n";
                @file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
            }
        }
        echo <<<ALTUM

<style>
    html {
        background: #161538;
        font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
        width: 100%;
        height: 100%;
        color: #eeeeee;
    }
    body {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    a {
        color: #c3beff;
        text-decoration: none;
    }
    .text-white {
        color: white;
    }
    .altumcode-logo {
        width: 3rem;
        height: auto;
    }
    .buttons {
        margin-top: 1.5rem;
    }
</style>

ALTUM;

        echo '<div>';
        echo '<h1 class="text-white">Internal server error</h1>';
        echo '<p>Our website is having some issues right now.</p>';
        echo '<p>We are actively working on fixing the issue.</p>';
        echo '</div>';
        die();

    }
}

/* Register error handlers */
register_shutdown_function('altumcode_shutdown_handler');

