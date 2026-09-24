<?php
/*
 * Lightweight session helpers used by customized Authentication / App code.
 */

function session_start_if_not_started() {
    static $session_started = null;

    if($session_started !== null) {
        return $session_started;
    }

    if(session_status() === PHP_SESSION_ACTIVE) {
        return $session_started = true;
    }

    $should_start_session = true;
    if(isset(\Altum\Router::$controller_settings['allow_sessions']) && !\Altum\Router::$controller_settings['allow_sessions']) {
        $should_start_session = false;
    }

    if($should_start_session) {
        @session_start();
        return $session_started = (session_status() === PHP_SESSION_ACTIVE);
    }

    return $session_started = false;
}

function session_set($key, $value) {
    session_start_if_not_started();
    $_SESSION[$key] = $value;
}

function session_get($key, $default = null) {
    session_start_if_not_started();
    return $_SESSION[$key] ?? $default;
}

function session_unset_key($key) {
    session_start_if_not_started();
    if(isset($_SESSION[$key])) {
        unset($_SESSION[$key]);
    }
}

function session_has($key) {
    session_start_if_not_started();
    return isset($_SESSION[$key]);
}
