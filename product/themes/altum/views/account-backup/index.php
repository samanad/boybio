<?php defined('ALTUMCODE') || die() ?>

<div class="container">
    <?= \Altum\Alerts::output_alerts() ?>

    <?= $this->views['account_header_menu'] ?>

    <div class="d-flex align-items-center mb-3">
        <h1 class="h4 m-0"><?= l('account_backup.header') ?></h1>
        <div class="ml-2">
            <span data-toggle="tooltip" title="<?= l('account_backup.subheader') ?>">
                <i class="fas fa-fw fa-info-circle text-muted"></i>
            </span>
        </div>
    </div>
    <p class="text-muted mb-4"><?= l('account_backup.subheader') ?></p>

    <?php if(!empty($_SESSION['account_backup_import_log'])): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6"><?= l('account_backup.log.title') ?></h2>
                <ul class="small mb-0">
                    <?php foreach($_SESSION['account_backup_import_log'] as $row): ?>
                        <li>
                            <strong><?= htmlspecialchars($row['table'] ?? '') ?></strong>
                            <?= htmlspecialchars(json_encode(array_diff_key($row, ['table' => 1]), JSON_UNESCAPED_UNICODE)) ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            </div>
        </div>
        <?php unset($_SESSION['account_backup_import_log']) ?>
    <?php endif ?>

    <?php if($data->review): ?>
        <div class="card mb-4 border-primary">
            <div class="card-body">
                <h2 class="h5"><?= l('account_backup.review.header') ?></h2>
                <p class="text-muted"><?= ($data->review['mode'] ?? '') === 'create' ? l('account_backup.review.subheader_create') : l('account_backup.review.subheader_merge') ?></p>
                <p class="font-weight-bold"><?= ($data->review['mode'] ?? '') === 'create' ? l('account_backup.mode.create') : l('account_backup.mode.merge') ?></p>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <tr><th><?= l('account_backup.review.source') ?></th><td><?= htmlspecialchars($data->review['manifest']['source_site'] ?? '') ?></td></tr>
                        <tr><th><?= l('global.email') ?></th><td><?= htmlspecialchars($data->review['account']['email']) ?></td></tr>
                        <tr><th><?= l('global.name') ?></th><td><?= htmlspecialchars($data->review['account']['name']) ?></td></tr>
                        <tr><th><?= l('account_backup.review.plan') ?></th><td><?= htmlspecialchars($data->review['account']['plan_id']) ?></td></tr>
                        <tr><th><?= l('account_backup.review.links') ?></th><td><?= (int) ($data->review['counts']['links'] ?? 0) ?></td></tr>
                        <tr><th><?= l('account_backup.review.blocks') ?></th><td><?= (int) ($data->review['counts']['biolink_blocks'] ?? 0) ?></td></tr>
                        <tr><th><?= l('account_backup.review.media') ?></th><td><?= (int) ($data->review['counts']['media'] ?? 0) ?></td></tr>
                        <tr><th><?= l('account_backup.review.destination_mode') ?></th><td><?= htmlspecialchars($data->review['manifest']['destination'] ?? '') ?></td></tr>
                    </table>
                </div>

                <form action="" method="post" role="form">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                    <?php foreach($data->review['conflicts'] as $conflict): ?>
                        <?php if(!empty($conflict['needs_email'])): ?>
                            <div class="form-group">
                                <label for="account_email_override"><?= l('account_backup.import.email_override') ?></label>
                                <input id="account_email_override" type="email" name="account_email_override" class="form-control" value="" placeholder="<?= htmlspecialchars($data->review['account']['email']) ?>" />
                                <small class="form-text text-muted"><?= l('account_backup.import.email_override_help') ?></small>
                            </div>
                        <?php endif ?>
                        <div class="form-group border rounded p-3 mb-3">
                            <div class="font-weight-bold mb-1">
                                <?php if(($conflict['severity'] ?? '') === 'error'): ?>
                                    <span class="badge badge-danger"><?= l('account_backup.review.needs_choice') ?></span>
                                <?php elseif(($conflict['severity'] ?? '') === 'info'): ?>
                                    <span class="badge badge-info"><?= l('account_backup.review.info') ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?= l('account_backup.review.needs_choice') ?></span>
                                <?php endif ?>
                            </div>
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

                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" name="type" value="import_confirm" class="btn btn-primary"><?= ($data->review['mode'] ?? '') === 'create' ? l('account_backup.review.confirm_create') : l('account_backup.review.confirm_merge') ?></button>
                        <button type="submit" name="type" value="import_cancel" class="btn btn-light"><?= l('global.cancel') ?></button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif ?>

    <?php $job = $data->export_job ?? null; ?>
    <?php if(!empty($job['status'])): ?>
        <div class="card mb-4 <?= ($job['status'] ?? '') === 'error' ? 'border-danger' : 'border-info' ?>">
            <div class="card-body">
                <?php if(in_array($job['status'], ['queued', 'running'], true)): ?>
                    <p class="mb-0"><?= l('account_backup.export.job.running') ?></p>
                    <script>setTimeout(function () { window.location.reload(); }, 8000);</script>
                <?php elseif(($job['status'] ?? '') === 'done' && !empty($job['filename'])): ?>
                    <p><?= sprintf(l('account_backup.export.job.done'), htmlspecialchars($job['filename']), htmlspecialchars(\Altum\Models\AccountBackup::format_bytes($job['bytes'] ?? 0))) ?></p>
                    <?php if(!empty($job['offload_url'])): ?>
                        <p class="small text-muted"><?= l('account_backup.export.job.offload_kept') ?></p>
                        <a class="btn btn-primary" href="<?= htmlspecialchars($job['offload_url']) ?>"><?= l('account_backup.export.job.download') ?></a>
                    <?php else: ?>
                        <a class="btn btn-primary" href="<?= url('account-backup?download=' . urlencode($job['filename'])) ?>"><?= l('account_backup.export.job.download') ?></a>
                    <?php endif ?>
                <?php elseif(($job['status'] ?? '') === 'error'): ?>
                    <p class="mb-0 text-danger"><?= sprintf(l('account_backup.export.job.error'), htmlspecialchars($job['message'] ?? '')) ?></p>
                <?php endif ?>
            </div>
        </div>
    <?php endif ?>

    <?php if(!empty($data->export_preview)): ?>
        <?php $preview = $data->export_preview; ?>
        <div class="card mb-4 border-primary">
            <div class="card-body">
                <h2 class="h5"><?= l('account_backup.export.preview.header') ?></h2>
                <p class="text-muted"><?= l('account_backup.export.preview.help') ?></p>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm">
                        <tr><th><?= l('account_backup.review.destination_mode') ?></th><td><?= htmlspecialchars($preview['destination']) ?></td></tr>
                        <tr><th><?= l('account_backup.review.links') ?></th><td><?= (int) ($preview['links'] ?? 0) ?></td></tr>
                        <tr><th><?= l('account_backup.review.blocks') ?></th><td><?= (int) ($preview['blocks'] ?? 0) ?></td></tr>
                        <tr><th><?= l('account_backup.review.media') ?></th><td><?= (int) ($preview['media'] ?? 0) ?></td></tr>
                        <tr>
                            <th><?= l('account_backup.export.preview.size') ?></th>
                            <td><?= !empty($preview['counts_only']) ? l('account_backup.export.preview.counts_note') : htmlspecialchars(\Altum\Models\AccountBackup::format_bytes($preview['total_bytes'] ?? 0)) ?></td>
                        </tr>
                        <?php if(empty($preview['counts_only']) && !empty($preview['unknown'])): ?>
                            <tr>
                                <th><?= l('account_backup.export.preview.other') ?></th>
                                <td><?= sprintf(l('account_backup.export.preview.size_unknown'), (int) $preview['unknown']) ?></td>
                            </tr>
                        <?php endif ?>
                        <?php if(empty($preview['counts_only'])): ?>
                        <tr>
                            <th><?= l('account_backup.export.preview.large_header') ?></th>
                            <td>
                                <?php if(empty($preview['large_count'])): ?>
                                    <?= l('account_backup.export.preview.large_none') ?>
                                <?php else: ?>
                                    <?= (int) $preview['large_count'] ?> · <?= htmlspecialchars(\Altum\Models\AccountBackup::format_bytes($preview['large_bytes'] ?? 0)) ?>
                                <?php endif ?>
                            </td>
                        </tr>
                        <?php endif ?>
                        <?php if(!empty($preview['large_count'])): ?>
                            <tr>
                                <th><?= l('account_backup.export.preview.size_if_excluded') ?></th>
                                <td><?= htmlspecialchars(\Altum\Models\AccountBackup::format_bytes($preview['size_without_large'] ?? 0)) ?></td>
                            </tr>
                        <?php endif ?>
                    </table>
                </div>
                <?php if(!empty($preview['large'])): ?>
                    <ul class="small mb-3">
                        <?php foreach($preview['large'] as $file): ?>
                            <li>
                                <?= htmlspecialchars($file['path'] ?? '') ?>
                                · <?= htmlspecialchars(\Altum\Models\AccountBackup::format_bytes($file['bytes'] ?? 0)) ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>
                <form action="" method="post">
                    <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                    <div class="custom-control custom-checkbox mb-3">
                        <input id="exclude_large" name="exclude_large" type="checkbox" class="custom-control-input" value="1" checked="checked">
                        <label class="custom-control-label" for="exclude_large">
                            <strong><?= l('account_backup.export.preview.exclude_large') ?></strong>
                            <div class="small text-muted"><?= l('account_backup.export.preview.exclude_large_help') ?></div>
                        </label>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" name="type" value="export_confirm" class="btn btn-primary"><?= l('account_backup.export.preview.create') ?></button>
                        <button type="submit" name="type" value="export_cancel" class="btn btn-light"><?= l('global.cancel') ?></button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif ?>

    <div class="row">
        <div class="col-12 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5"><i class="fas fa-fw fa-download fa-sm mr-1"></i> <?= l('account_backup.export.header') ?></h2>
                    <p class="text-muted"><?= l('account_backup.export.help') ?></p>

                    <form action="" method="post" class="mb-3">
                        <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                        <button type="submit" name="type" value="export_pc" class="btn btn-block btn-primary">
                            <i class="fas fa-fw fa-desktop mr-1"></i> <?= l('account_backup.export.pc') ?>
                        </button>
                        <small class="form-text text-muted"><?= l('account_backup.export.pc_help') ?></small>
                    </form>

                    <form action="" method="post">
                        <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                        <button type="submit" name="type" value="export_offload" class="btn btn-block btn-outline-primary" <?= $data->offload_ready ? '' : 'disabled="disabled"' ?>>
                            <i class="fas fa-fw fa-cloud mr-1"></i> <?= l('account_backup.export.offload') ?>
                        </button>
                        <small class="form-text text-muted">
                            <?= $data->offload_ready ? l('account_backup.export.offload_help') : l('account_backup.error.offload_not_ready') ?>
                        </small>
                    </form>

                    <?php if(!empty($data->local_packages)): ?>
                        <hr>
                        <h3 class="h6"><?= l('account_backup.export.local.header') ?></h3>
                        <?php foreach($data->local_packages as $item): ?>
                            <a class="btn btn-sm btn-outline-secondary mb-2 d-block" href="<?= url('account-backup?download=' . urlencode($item['filename'])) ?>">
                                <?= htmlspecialchars($item['filename']) ?>
                                · <?= htmlspecialchars(\Altum\Models\AccountBackup::format_bytes($item['size'] ?? 0)) ?>
                            </a>
                        <?php endforeach ?>
                    <?php endif ?>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="h5"><i class="fas fa-fw fa-upload fa-sm mr-1"></i> <?= l('account_backup.import.header') ?></h2>
                    <p class="text-muted"><?= l('account_backup.import.help') ?></p>

                    <form action="" method="post" enctype="multipart/form-data" class="mb-4">
                        <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />

                        <div class="form-group">
                            <div class="custom-control custom-radio mb-2">
                                <input id="restore_create" name="restore_mode" type="radio" class="custom-control-input" value="create">
                                <label class="custom-control-label" for="restore_create">
                                    <strong><?= l('account_backup.mode.create') ?></strong>
                                    <div class="small text-muted"><?= l('account_backup.mode.create_help') ?></div>
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input id="restore_merge" name="restore_mode" type="radio" class="custom-control-input" value="merge" checked="checked">
                                <label class="custom-control-label" for="restore_merge">
                                    <strong><?= l('account_backup.mode.merge') ?></strong>
                                    <div class="small text-muted"><?= l('account_backup.mode.merge_help') ?></div>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="package"><?= l('account_backup.import.file') ?></label>
                            <input id="package" type="file" name="package" accept=".zip,application/zip" class="form-control-file" required="required" />
                        </div>
                        <button type="submit" name="type" value="import_upload" class="btn btn-block btn-primary">
                            <?= l('account_backup.import.from_pc') ?>
                        </button>
                    </form>

                    <?php if($data->offload_ready): ?>
                        <form action="" method="post">
                            <input type="hidden" name="token" value="<?= \Altum\Csrf::get() ?>" />
                            <div class="form-group">
                                <div class="custom-control custom-radio mb-2">
                                    <input id="restore_create_cloud" name="restore_mode" type="radio" class="custom-control-input" value="create">
                                    <label class="custom-control-label" for="restore_create_cloud"><?= l('account_backup.mode.create') ?></label>
                                </div>
                                <div class="custom-control custom-radio mb-2">
                                    <input id="restore_merge_cloud" name="restore_mode" type="radio" class="custom-control-input" value="merge" checked="checked">
                                    <label class="custom-control-label" for="restore_merge_cloud"><?= l('account_backup.mode.merge') ?></label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cloud_key"><?= l('account_backup.import.from_cloud') ?></label>
                                <select id="cloud_key" name="cloud_key" class="custom-select" <?= count($data->cloud_packages) ? '' : 'disabled="disabled"' ?>>
                                    <option value=""><?= l('account_backup.import.choose_cloud') ?></option>
                                    <?php foreach($data->cloud_packages as $item): ?>
                                        <option value="<?= htmlspecialchars($item['key']) ?>">
                                            <?= htmlspecialchars($item['filename']) ?>
                                            <?php if(!empty($item['modified'])): ?> · <?= htmlspecialchars($item['modified']) ?><?php endif ?>
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <?php foreach($data->cloud_packages as $item): ?>
                                <?php if(!empty($item['url'])): ?>
                                    <a class="small d-block mb-1" href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['filename']) ?> · <?= htmlspecialchars(\Altum\Models\AccountBackup::format_bytes($item['size'] ?? 0)) ?></a>
                                <?php endif ?>
                            <?php endforeach ?>
                            <button type="submit" name="type" value="import_cloud" class="btn btn-block btn-outline-primary" <?= count($data->cloud_packages) ? '' : 'disabled="disabled"' ?>>
                                <?= l('account_backup.import.load_cloud') ?>
                            </button>
                        </form>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>
