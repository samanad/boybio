<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="form-group custom-control custom-switch">
        <input id="csrf_strict_validation_is_enabled" name="csrf_strict_validation_is_enabled" type="checkbox" class="custom-control-input" <?= (isset(settings()->security) && isset(settings()->security->csrf_strict_validation_is_enabled) && settings()->security->csrf_strict_validation_is_enabled) ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="csrf_strict_validation_is_enabled"><i class="fas fa-fw fa-sm fa-shield-alt text-muted mr-1"></i> <?= l('admin_settings.security.csrf_strict_validation_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.security.csrf_strict_validation_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="biolink_edit_allowed_ip"><i class="fas fa-fw fa-sm fa-edit text-muted mr-1"></i> <?= l('admin_settings.security.biolink_edit_allowed_ip') ?></label>
        <input type="text" id="biolink_edit_allowed_ip" name="biolink_edit_allowed_ip" class="form-control" value="<?= isset(settings()->security) && isset(settings()->security->biolink_edit_allowed_ip) ? settings()->security->biolink_edit_allowed_ip : '' ?>" placeholder="165.22.58.120" />
        <small class="form-text text-muted"><?= l('admin_settings.security.biolink_edit_allowed_ip_help') ?></small>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>

