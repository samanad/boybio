<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="form-group custom-control custom-switch">
        <input id="csrf_strict_validation_is_enabled" name="csrf_strict_validation_is_enabled" type="checkbox" class="custom-control-input" <?= (isset(settings()->security) && isset(settings()->security->csrf_strict_validation_is_enabled) && settings()->security->csrf_strict_validation_is_enabled) ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="csrf_strict_validation_is_enabled"><i class="fas fa-fw fa-sm fa-shield-alt text-muted mr-1"></i> <?= l('admin_settings.security.csrf_strict_validation_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.security.csrf_strict_validation_is_enabled_help') ?></small>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>

