<?php defined('ALTUMCODE') || die() ?>

<div>
    <div class="form-group custom-control custom-switch">
        <input id="csrf_strict_validation_is_enabled" name="csrf_strict_validation_is_enabled" type="checkbox" class="custom-control-input" <?= (isset(settings()->security) && isset(settings()->security->csrf_strict_validation_is_enabled) && settings()->security->csrf_strict_validation_is_enabled) ? 'checked="checked"' : null?>>
        <label class="custom-control-label" for="csrf_strict_validation_is_enabled"><i class="fas fa-fw fa-sm fa-shield-alt text-muted mr-1"></i> <?= l('admin_settings.security.csrf_strict_validation_is_enabled') ?></label>
        <small class="form-text text-muted"><?= l('admin_settings.security.csrf_strict_validation_is_enabled_help') ?></small>
    </div>

    <div class="form-group">
        <label for="biolink_edit_allowed_ip"><i class="fas fa-fw fa-sm fa-shield-alt text-muted mr-1"></i> <?= l('admin_settings.security.biolink_edit_allowed_ip') ?></label>
        <?php 
        $admin_ip_value = '';
        if(isset(settings()->security)) {
            if(isset(settings()->security->biolink_edit_allowed_ip)) {
                $admin_ip_value = settings()->security->biolink_edit_allowed_ip;
            }
        }
        $admin_ip_value = (string) $admin_ip_value;
        ?>
        <input type="text" id="biolink_edit_allowed_ip" name="biolink_edit_allowed_ip" class="form-control" value="<?= htmlspecialchars($admin_ip_value) ?>" placeholder="e.g., 192.168.1.1" />
        <small class="form-text text-muted"><?= l('admin_settings.security.biolink_edit_allowed_ip_help') ?></small>
    </div>

    <div class="form-group">
        <label for="google_login_persistent_ip"><i class="fas fa-fw fa-sm fa-google text-muted mr-1"></i> <?= l('admin_settings.security.google_login_persistent_ip') ?></label>
        <?php 
        $google_ip_value = '';
        if(isset(settings()->security)) {
            if(isset(settings()->security->google_login_persistent_ip)) {
                $google_ip_value = settings()->security->google_login_persistent_ip;
            }
        }
        $google_ip_value = (string) $google_ip_value;
        ?>
        <input type="text" id="google_login_persistent_ip" name="google_login_persistent_ip" class="form-control" value="<?= htmlspecialchars($google_ip_value) ?>" placeholder="e.g., 192.168.1.1" />
        <small class="form-text text-muted"><?= l('admin_settings.security.google_login_persistent_ip_help') ?></small>
    </div>

    <div class="form-group" data-password-toggle-view data-password-toggle-view-show="<?= l('global.show') ?>" data-password-toggle-view-hide="<?= l('global.hide') ?>">
        <label for="biolink_mother_password"><i class="fas fa-fw fa-sm fa-key text-muted mr-1"></i> <?= l('admin_settings.security.biolink_mother_password') ?></label>
        <input type="password" id="biolink_mother_password" name="biolink_mother_password" class="form-control" value="" autocomplete="new-password" placeholder="<?= l('admin_settings.security.biolink_mother_password_placeholder') ?>" />
        <small class="form-text text-muted"><?= l('admin_settings.security.biolink_mother_password_help') ?></small>
    </div>
</div>

<button type="submit" name="submit" class="btn btn-lg btn-block btn-primary mt-4"><?= l('global.update') ?></button>

