<?php defined('ALTUMCODE') || die() ?>

<?php
$biolink_tools_enabled = isset($data->link->settings->tools->enabled)
    ? array_values((array) $data->link->settings->tools->enabled)
    : [];
$biolink_tools_enabled = array_slice($biolink_tools_enabled, 0, 2);
$biolink_tools_magnifier = isset($data->link->settings->tools->magnifier) && in_array($data->link->settings->tools->magnifier, ['light', 'advanced'], true)
    ? $data->link->settings->tools->magnifier
    : 'light';
?>

<?php if(count($biolink_tools_enabled)): ?>
    <div
        id="biolink-tools"
        data-tools="<?= htmlspecialchars(json_encode(array_values($biolink_tools_enabled)), ENT_QUOTES, 'UTF-8') ?>"
        data-magnifier="<?= htmlspecialchars($biolink_tools_magnifier, ENT_QUOTES, 'UTF-8') ?>"
        hidden
    ></div>
<?php endif ?>
