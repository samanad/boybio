<?php defined('ALTUMCODE') || die();

$is_dashboard = !isset($this->link) && (\Altum\Router::$path ?? '') !== 'l';
if(in_array(\Altum\Router::$controller_key ?? '', ['link', 'biolink-block'], true) || (\Altum\Router::$path ?? '') === 'l') {
    $is_dashboard = false;
}
/* Public biolink views use l/ wrappers */
if(isset($this->link) || (isset($data->link) && ($data->link->type ?? null) === 'biolink')) {
    $is_dashboard = false;
}
if(in_array(\Altum\Router::$controller_key ?? '', ['dashboard', 'links', 'link', 'account', 'account-sessions', 'account-logs', 'account-preferences', 'account-plan', 'account-payments', 'account-api', 'account-backup', 'account-delete', 'teams-system', 'qr-codes', 'biolinks-templates', 'data'], true)) {
    $is_dashboard = true;
}

$resilience = function_exists('resilience_client_config') ? resilience_client_config($is_dashboard) : null;
if(!$resilience) return;
?>
<script>window.ALTUM_RESILIENCE = <?= json_encode($resilience, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= ASSETS_FULL_URL ?>js/resilience.js?v=<?= PRODUCT_CODE ?>" defer></script>
