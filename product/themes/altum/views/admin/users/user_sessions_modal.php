<?php defined('ALTUMCODE') || die() ?>

<div class="modal fade" id="user_sessions_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-fw fa-sm fa-satellite-dish text-primary-900 mr-2"></i>
                    <span id="user_sessions_modal_title"><?= l('admin_user_sessions_modal.header') ?></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" title="<?= l('global.close') ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <p class="text-muted small mb-3" id="user_sessions_modal_subheader">
                    <?= sprintf(l('admin_user_sessions_modal.subheader'), \Altum\Models\UsersSessions::ALIVE_MINUTES) ?>
                </p>
                <div id="user_sessions_modal_loading" class="text-muted py-4 text-center d-none">
                    <i class="fas fa-fw fa-spin fa-spinner mr-1"></i> <?= l('global.loading') ?>
                </div>
                <div id="user_sessions_modal_empty" class="text-muted py-4 text-center d-none">
                    <?= l('admin_user_sessions_modal.empty') ?>
                </div>
                <div id="user_sessions_modal_list" class="d-none"></div>
            </div>

        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    'use strict';

    const renderSessionCard = (s) => {
        const flags = [];
        if (s.is_admin) flags.push(`<span class="badge badge-primary mr-1"><?= l('admin_user_sessions_modal.is_admin') ?></span>`);
        if (s.admin_impersonation) flags.push(`<span class="badge badge-warning mr-1"><?= l('admin_user_sessions_modal.impersonation') ?></span>`);

        const country = s.country ? `${s.country}${s.country_code ? ` (${s.country_code})` : ''}` : '<?= l('global.unknown') ?>';
        const device = s.device_label || s.device_type || '<?= l('global.unknown') ?>';

        return `
            <div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between align-items-start mb-2">
                    <div>
                        <strong>${s.ip || '—'}</strong>
                        <div class="small text-muted">${country}${s.city_name ? ` · ${s.city_name}` : ''}${s.continent ? ` · ${s.continent}` : ''}</div>
                    </div>
                    <div class="text-right">
                        ${flags.join('')}
                        <div class="small text-muted mt-1">${s.last_activity_ago || ''}</div>
                    </div>
                </div>
                <div class="row small">
                    <div class="col-md-6 mb-1"><span class="text-muted"><?= l('admin_user_sessions_modal.device') ?>:</span> ${device}</div>
                    <div class="col-md-6 mb-1"><span class="text-muted"><?= l('admin_user_sessions_modal.os') ?>:</span> ${s.os_name || '—'}</div>
                    <div class="col-md-6 mb-1"><span class="text-muted"><?= l('admin_user_sessions_modal.browser') ?>:</span> ${s.browser_name || '—'} ${s.browser_language ? `(${s.browser_language})` : ''}</div>
                    <div class="col-md-6 mb-1"><span class="text-muted"><?= l('admin_user_sessions_modal.started') ?>:</span> ${s.datetime_display || '—'}</div>
                    <div class="col-md-6 mb-1"><span class="text-muted"><?= l('admin_user_sessions_modal.last_activity') ?>:</span> ${s.last_activity_display || '—'}</div>
                    <div class="col-12 mb-1"><span class="text-muted"><?= l('admin_user_sessions_modal.session_id') ?>:</span> <code class="small">${s.session_id || '—'}</code></div>
                    <div class="col-12"><span class="text-muted"><?= l('admin_user_sessions_modal.user_agent') ?>:</span> <span class="d-block text-break">${s.user_agent || '—'}</span></div>
                </div>
            </div>
        `;
    };

    $('#user_sessions_modal').on('show.bs.modal', event => {
        const button = $(event.relatedTarget);
        const userId = button.data('user-id');
        const userName = button.data('user-name') || '';
        const modal = $(event.currentTarget);

        modal.find('#user_sessions_modal_title').text(`<?= l('admin_user_sessions_modal.header') ?> — ${userName}`);
        modal.find('#user_sessions_modal_loading').removeClass('d-none');
        modal.find('#user_sessions_modal_empty').addClass('d-none');
        modal.find('#user_sessions_modal_list').addClass('d-none').empty();

        fetch(`${url}admin/users/sessions/${userId}?global_token=${encodeURIComponent(global_token)}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.json())
            .then(data => {
                modal.find('#user_sessions_modal_loading').addClass('d-none');
                if (!data.ok || !data.sessions || !data.sessions.length) {
                    modal.find('#user_sessions_modal_empty').removeClass('d-none');
                    return;
                }
                const html = data.sessions.map(renderSessionCard).join('');
                modal.find('#user_sessions_modal_list').html(html).removeClass('d-none');
            })
            .catch(() => {
                modal.find('#user_sessions_modal_loading').addClass('d-none');
                modal.find('#user_sessions_modal_empty').removeClass('d-none').text('<?= l('global.error_message.basic') ?>');
            });
    });
</script>
<?php \Altum\Event::add_content(ob_get_clean(), 'javascript') ?>
