<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>
    <?= $this->views['account_header_menu'] ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1"><?= l('account_sessions.header') ?></h1>
            <p class="text-muted mb-0 small"><?= sprintf(l('account_sessions.subheader'), (int) $data->alive_minutes) ?></p>
        </div>
        <form method="post" onsubmit="return confirm('<?= e(l('account_sessions.confirm_revoke_others')) ?>')">
            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
            <button type="submit" name="revoke_others" value="1" class="btn btn-sm btn-outline-danger"><?= l('account_sessions.revoke_others') ?></button>
        </form>
    </div>

    <div class="table-responsive table-custom-container">
        <table class="table table-custom">
            <thead>
            <tr>
                <th><?= l('global.device') ?></th>
                <th><?= l('global.ip') ?></th>
                <th><?= l('global.datetime') ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php if(!count($data->sessions)): ?>
                <tr><td colspan="4" class="text-muted"><?= l('global.no_data') ?></td></tr>
            <?php endif ?>
            <?php foreach($data->sessions as $row): ?>
                <tr>
                    <td>
                        <div class="font-weight-600">
                            <?= e(($row->browser_name ?: l('global.unknown')) . ' / ' . ($row->os_name ?: l('global.unknown'))) ?>
                            <?php if($row->session_id === $data->current_session_id): ?>
                                <span class="badge badge-success ml-1"><?= l('account_sessions.current') ?></span>
                            <?php endif ?>
                        </div>
                        <div class="small text-muted"><?= e($row->device_type ?: '') ?> · <?= e($row->country_code ?: '') ?> <?= e($row->city_name ?: '') ?></div>
                    </td>
                    <td><code><?= e($row->ip) ?></code></td>
                    <td>
                        <div><?= \Altum\Date::get($row->last_activity, 1) ?></div>
                        <div class="small text-muted"><?= l('account_sessions.last_activity') ?></div>
                    </td>
                    <td class="text-right">
                        <?php if($row->session_id !== $data->current_session_id): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                                <input type="hidden" name="revoke_session_id" value="<?= e($row->session_id) ?>" />
                                <button type="submit" class="btn btn-sm btn-light text-danger"><?= l('account_sessions.revoke') ?></button>
                            </form>
                        <?php endif ?>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h2 class="h6"><?= l('account_sessions.passcode_header') ?></h2>
            <p class="small text-muted"><?= l('account_sessions.passcode_help') ?></p>
            <div class="form-row">
                <div class="col-md-6 mb-2">
                    <input type="password" id="resilience_passcode" class="form-control" placeholder="<?= e(l('account_sessions.passcode_placeholder')) ?>" />
                </div>
                <div class="col-md-6 mb-2">
                    <button type="button" class="btn btn-primary" id="resilience_passcode_save"><?= l('global.save') ?></button>
                    <button type="button" class="btn btn-light" id="resilience_passcode_clear"><?= l('global.delete') ?></button>
                </div>
            </div>
            <div class="small text-success d-none" id="resilience_passcode_ok"><?= l('global.success_message.save') ?></div>
        </div>
    </div>
</div>

<script>
document.getElementById('resilience_passcode_save')?.addEventListener('click', function () {
    var pass = document.getElementById('resilience_passcode').value || '';
    if (!window.CloubResilience) return;
    window.CloubResilience.setPasscode(pass).then(function () {
        document.getElementById('resilience_passcode_ok').classList.remove('d-none');
    });
});
document.getElementById('resilience_passcode_clear')?.addEventListener('click', function () {
    if (!window.CloubResilience) return;
    window.CloubResilience.clearPasscode();
    document.getElementById('resilience_passcode').value = '';
    document.getElementById('resilience_passcode_ok').classList.remove('d-none');
});
</script>
