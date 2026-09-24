<?php defined('ALTUMCODE') || die() ?>

<?= \Altum\Alerts::output_alerts() ?>


    <h1 class="h4"><?= l('account_backup.restore.header') ?></h1>
    <p class="text-muted"><?= l('account_backup.restore.subheader') ?></p>

    <?php if($data->review): ?>
        <div class="card mb-4 border-primary">
            <div class="card-body">
                <h2 class="h5"><?= l('account_backup.review.header') ?></h2>
                <p class="text-muted"><?= l('account_backup.review.subheader_create') ?></p>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <tr><th><?= l('account_backup.review.source') ?></th><td><?= htmlspecialchars($data->review['manifest']['source_site'] ?? '') ?></td></tr>
                        <tr><th><?= l('global.email') ?></th><td><?= htmlspecialchars($data->review['account']['email']) ?></td></tr>
                        <tr><th><?= l('global.name') ?></th><td><?= htmlspecialchars($data->review['account']['name']) ?></td></tr>
                        <tr><th><?= l('account_backup.review.plan') ?></th><td><?= htmlspecialchars($data->review['account']['plan_id']) ?></td></tr>
                        <tr><th><?= l('account_backup.review.links') ?></th><td><?= (int) ($data->review['counts']['links'] ?? 0) ?></td></tr>
                        <tr><th><?= l('account_backup.review.blocks') ?></th><td><?= (int) ($data->review['counts']['biolink_blocks'] ?? 0) ?></td></tr>
                    </table>
                </div>

                <form action="" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                    <?php foreach($data->review['conflicts'] as $conflict): ?>
                        <?php if(!empty($conflict['needs_email'])): ?>
                            <div class="form-group">
                                <label for="account_email_override"><?= l('account_backup.import.email_override') ?></label>
                                <input id="account_email_override" type="email" name="account_email_override" class="form-control" placeholder="<?= htmlspecialchars($data->review['account']['email']) ?>" />
                                <small class="form-text text-muted"><?= l('account_backup.import.email_override_help') ?></small>
                            </div>
                        <?php endif ?>
                        <div class="form-group border rounded p-3 mb-3">
                            <p class="mb-2"><?= $conflict['message'] ?></p>
                            <?php $options = $conflict['options'] ?? ['continue']; $first = true; ?>
                            <?php foreach($options as $option): ?>
                                <div class="custom-control custom-radio">
                                    <input id="c_<?= $conflict['id'] ?>_<?= $option ?>" name="<?= $conflict['id'] ?>" type="radio" class="custom-control-input" value="<?= $option ?>" <?= $first ? 'checked="checked"' : null ?>>
                                    <label class="custom-control-label" for="c_<?= $conflict['id'] ?>_<?= $option ?>"><?= l('account_backup.option.' . $option) ?></label>
                                </div>
                                <?php $first = false; ?>
                            <?php endforeach ?>
                        </div>
                    <?php endforeach ?>

                    <button type="submit" name="type" value="import_confirm" class="btn btn-primary"><?= l('account_backup.review.confirm_create') ?></button>
                    <button type="submit" name="type" value="import_cancel" class="btn btn-light"><?= l('global.cancel') ?></button>
                </form>
            </div>
        </div>
    <?php endif ?>

    <div class="card">
        <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                <input type="hidden" name="restore_mode" value="create" />
                <div class="form-group">
                    <label for="package"><?= l('account_backup.import.file') ?></label>
                    <input id="package" type="file" name="package" accept=".zip,application/zip" class="form-control-file" required="required" />
                </div>
                <button type="submit" name="type" value="import_upload" class="btn btn-block btn-primary"><?= l('account_backup.restore.submit') ?></button>
            </form>
            <div class="mt-3 small text-muted">
                <a href="<?= url('login') ?>"><?= l('login.menu') ?></a>
            </div>
        </div>
    </div>
