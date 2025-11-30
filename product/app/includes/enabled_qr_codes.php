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

$enabled_qr_codes = [];

$available_qr_codes = isset(settings()->codes) && isset(settings()->codes->available_qr_codes) ? settings()->codes->available_qr_codes : new \StdClass();

foreach(require APP_PATH . 'includes/qr_codes.php' as $type => $value) {
    if(isset($available_qr_codes->{$type}) && $available_qr_codes->{$type}) {
        $enabled_qr_codes[$type] = $value;
    }
}

return $enabled_qr_codes;
