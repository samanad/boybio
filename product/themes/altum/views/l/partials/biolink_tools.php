<?php defined('ALTUMCODE') || die() ?>

<?php
$biolink_tools_enabled = isset($data->link->settings->tools->enabled)
    ? array_values((array) $data->link->settings->tools->enabled)
    : [];
$password_tool_on = in_array('password', $biolink_tools_enabled, true);
$biolink_tools_enabled = array_values(array_filter($biolink_tools_enabled, function($tool_id) {
    return $tool_id !== 'password';
}));
$biolink_tools_enabled = array_slice($biolink_tools_enabled, 0, 2);
if($password_tool_on) {
    array_unshift($biolink_tools_enabled, 'password');
}
$biolink_tools_magnifier = isset($data->link->settings->tools->magnifier) && in_array($data->link->settings->tools->magnifier, ['light', 'advanced'], true)
    ? $data->link->settings->tools->magnifier
    : 'light';

$biolink_tools_favicon = '';
if(!empty($data->link->settings->favicon)) {
    $biolink_tools_favicon = \Altum\Uploads::get_full_url('favicons') . $data->link->settings->favicon;
} elseif(!empty($data->link->settings->pwa_icon)) {
    $biolink_tools_favicon = \Altum\Uploads::get_full_url('app_icon') . $data->link->settings->pwa_icon;
} elseif(!empty(settings()->main->favicon)) {
    $biolink_tools_favicon = settings()->main->favicon_full_url;
}
?>

<?php if(count($biolink_tools_enabled)): ?>
    <div
        id="biolink-tools"
        data-tools="<?= htmlspecialchars(json_encode(array_values($biolink_tools_enabled)), ENT_QUOTES, 'UTF-8') ?>"
        data-magnifier="<?= htmlspecialchars($biolink_tools_magnifier, ENT_QUOTES, 'UTF-8') ?>"
        data-favicon="<?= htmlspecialchars($biolink_tools_favicon, ENT_QUOTES, 'UTF-8') ?>"
        hidden
    ></div>
<?php endif ?>
