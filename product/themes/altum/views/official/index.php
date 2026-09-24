<?php defined('ALTUMCODE') || die() ?>
<div class="container py-5" style="max-width:720px">
    <div class="mb-4">
        <h1 class="h3 mb-2"><?= l('resilience.official_title') ?></h1>
        <p class="text-muted mb-0"><?= l('resilience.official_subheader') ?></p>
    </div>

    <?php if($data->offline): ?>
        <div class="alert alert-secondary"><?= l('resilience.offline_banner') ?></div>
    <?php endif ?>

    <?php if($data->is_official): ?>
        <div class="alert alert-success">
            <i class="fas fa-fw fa-shield-alt mr-1"></i>
            <?= sprintf(l('resilience.official_ok'), '<strong>' . e($data->current_host) . '</strong>') ?>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            <i class="fas fa-fw fa-exclamation-triangle mr-1"></i>
            <?= sprintf(l('resilience.official_bad'), '<strong>' . e($data->current_host) . '</strong>') ?>
        </div>
    <?php endif ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h2 class="h5"><?= l('resilience.official_list_header') ?></h2>
            <ul class="mb-0 pl-3">
                <?php foreach($data->hosts as $host): ?>
                    <li class="mb-1"><code><?= e($host) ?></code></li>
                <?php endforeach ?>
            </ul>
            <?php if(!count($data->hosts)): ?>
                <p class="text-muted mb-0"><?= l('resilience.official_list_empty') ?></p>
            <?php endif ?>
        </div>
    </div>

    <p class="small text-muted mt-4 mb-0"><?= l('resilience.official_help') ?></p>
</div>
