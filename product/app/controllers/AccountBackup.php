<?php
/* cloub.io account backup / restore */

namespace Altum\Controllers;

use Altum\Alerts;
use Altum\Models\AccountBackup as AccountBackupModel;

defined('ALTUMCODE') || die();

class AccountBackup extends Controller {

    public function index() {

        if(\Altum\Router::$controller_key === 'account-restore') {
            $this->restore();
            return;
        }

        \Altum\Authentication::guard();

        $backup = new AccountBackupModel();
        $this->handle_post($backup, true);

        $offload_ready = $backup->offload_is_ready();
        $review = $this->build_review($backup, $this->user);
        $export_preview = $_SESSION['account_backup_export'] ?? null;

        if(session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $cloud_packages = $offload_ready ? $backup->list_offload_packages($this->user->user_id) : [];

        $menu = new \Altum\View('partials/account_header_menu', (array) $this);
        $this->add_view_content('account_header_menu', $menu->run());

        $view = new \Altum\View('account-backup/index', (array) $this);
        $this->add_view_content('content', $view->run([
            'offload_ready' => $offload_ready,
            'cloud_packages' => $cloud_packages,
            'review' => $review,
            'export_preview' => $export_preview,
            'logged_in' => true,
        ]));
    }

    public function restore() {

        if(\Altum\Authentication::check()) {
            redirect('account-backup');
        }

        $backup = new AccountBackupModel();
        $this->handle_post($backup, false);

        $review = $this->build_review($backup, null);

        $view = new \Altum\View('account-backup/restore', (array) $this);
        $this->add_view_content('content', $view->run([
            'review' => $review,
            'logged_in' => false,
        ]));
    }

    private function handle_post(AccountBackupModel $backup, $logged_in) {
        if(empty($_POST)) return;

        if(!\Altum\Csrf::check()) {
            Alerts::add_error(l('global.error_message.invalid_csrf_token'));
            return;
        }

        $type = $_POST['type'] ?? '';

        try {
            if($type === 'export_pc' && $logged_in) {
                $this->prepare_export($backup, 'pc');
            } elseif($type === 'export_offload' && $logged_in) {
                $this->prepare_export($backup, 'offload');
            } elseif($type === 'export_confirm' && $logged_in) {
                $this->export_confirm($backup);
            } elseif($type === 'export_cancel' && $logged_in) {
                unset($_SESSION['account_backup_export']);
                redirect('account-backup');
            } elseif($type === 'import_upload') {
                $this->import_upload($backup, $logged_in);
            } elseif($type === 'import_cloud' && $logged_in) {
                $this->import_cloud($backup);
            } elseif($type === 'import_confirm') {
                $this->import_confirm($backup, $logged_in);
            } elseif($type === 'import_cancel') {
                $this->import_cancel($backup, $logged_in);
            }
        } catch(\Exception $exception) {
            $message = $exception->getMessage();
            $known = [
                'offload_not_ready' => l('account_backup.error.offload_not_ready'),
                'invalid_zip' => l('account_backup.error.invalid_zip'),
                'invalid_package' => l('account_backup.error.invalid_package'),
                'no_file' => l('account_backup.error.no_file'),
                'email_taken' => l('account_backup.error.email_taken'),
                'email_invalid' => l('account_backup.error.email_invalid'),
                'password_missing' => l('account_backup.error.password_missing'),
                'account_create_failed' => l('account_backup.error.account_create_failed'),
            ];
            Alerts::add_error($known[$message] ?? (l('global.error_message.basic') . ' (' . $message . ')'));
        }
    }

    private function build_review(AccountBackupModel $backup, $target_user) {
        if(empty($_SESSION['account_backup_import_dir']) || !is_dir($_SESSION['account_backup_import_dir'])) {
            return null;
        }
        $mode = $_SESSION['account_backup_restore_mode'] ?? ($target_user ? 'merge' : 'create');
        $package = $backup->read_package($_SESSION['account_backup_import_dir']);
        $package['_dir'] = $_SESSION['account_backup_import_dir'];
        return [
            'mode' => $mode,
            'manifest' => $package['manifest'],
            'account' => [
                'email' => $package['account']['email'] ?? '',
                'name' => $package['account']['name'] ?? '',
                'plan_id' => $package['account']['plan_id'] ?? '',
                'source_user_id' => $package['account']['_meta']['source_user_id'] ?? ($package['manifest']['source_user_id'] ?? ''),
            ],
            'counts' => $package['manifest']['counts'] ?? [],
            'conflicts' => $backup->detect_conflicts($package, $target_user, $mode),
        ];
    }

    private function prepare_export(AccountBackupModel $backup, $destination) {
        if($destination === 'offload' && !$backup->offload_is_ready()) {
            throw new \RuntimeException('offload_not_ready');
        }
        $_SESSION['account_backup_export'] = $backup->prepare_export($this->user, $destination);
        redirect('account-backup');
    }

    private function export_confirm(AccountBackupModel $backup) {
        $preview = $_SESSION['account_backup_export'] ?? null;
        if(!$preview) {
            throw new \RuntimeException('no_file');
        }
        $destination = ($preview['destination'] ?? '') === 'offload' ? 'offload' : 'pc';
        $exclude_over = !empty($_POST['exclude_large']) ? AccountBackupModel::LARGE_FILE_BYTES : 0;
        unset($_SESSION['account_backup_export']);
        if($destination === 'pc' && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        ignore_user_abort(true);
        $this->export($backup, $destination, $exclude_over);
    }

    private function export(AccountBackupModel $backup, $destination, $exclude_over = 0) {
        if($destination === 'offload' && !$backup->offload_is_ready()) {
            throw new \RuntimeException('offload_not_ready');
        }

        $package = $backup->build_package($this->user, $destination, [
            'exclude_over_bytes' => $exclude_over,
        ]);
        \Altum\Logger::users($this->user->user_id, 'account.backup.exported.' . $destination);

        if($destination === 'pc') {
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $package['filename'] . '"');
            header('Content-Length: ' . filesize($package['zip_path']));
            header('Cache-Control: no-store');
            readfile($package['zip_path']);
            @unlink($package['zip_path']);
            die();
        }

        $uploaded = $backup->upload_package_to_offload($this->user, $package['zip_path'], $package['filename']);
        @unlink($package['zip_path']);
        Alerts::add_success(sprintf(l('account_backup.success.exported_offload'), $uploaded['folder'], basename($uploaded['key'])));
        redirect('account-backup');
    }

    private function restore_mode($logged_in) {
        $mode = $_POST['restore_mode'] ?? '';
        if($mode === 'create') return 'create';
        if($logged_in && $mode === 'merge') return 'merge';
        return $logged_in ? 'merge' : 'create';
    }

    private function import_upload(AccountBackupModel $backup, $logged_in) {
        if(empty($_FILES['package']['tmp_name']) || !is_uploaded_file($_FILES['package']['tmp_name'])) {
            throw new \RuntimeException('no_file');
        }
        $this->stash_zip($backup, $_FILES['package']['tmp_name'], $this->restore_mode($logged_in));
        redirect($logged_in ? 'account-backup' : 'account-restore');
    }

    private function import_cloud(AccountBackupModel $backup) {
        $key = $_POST['cloud_key'] ?? '';
        if(!$key || !$backup->offload_is_ready()) {
            throw new \RuntimeException('no_file');
        }
        if(!$backup->key_belongs_to_account($key, $this->user->user_id)) {
            throw new \RuntimeException('no_file');
        }
        $tmp = rtrim(sys_get_temp_dir(), '/') . '/cloub-dl-' . bin2hex(random_bytes(4)) . '.zip';
        $backup->download_offload_package($key, $tmp);
        $this->stash_zip($backup, $tmp, $this->restore_mode(true));
        @unlink($tmp);
        redirect('account-backup');
    }

    private function stash_zip(AccountBackupModel $backup, $zip_path, $mode) {
        if(!empty($_SESSION['account_backup_import_dir'])) {
            $backup->rrmdir($_SESSION['account_backup_import_dir']);
        }
        $_SESSION['account_backup_import_dir'] = $backup->extract_package($zip_path);
        $_SESSION['account_backup_restore_mode'] = $mode;
    }

    private function import_confirm(AccountBackupModel $backup, $logged_in) {
        if(empty($_SESSION['account_backup_import_dir']) || !is_dir($_SESSION['account_backup_import_dir'])) {
            throw new \RuntimeException('invalid_package');
        }

        $mode = $_SESSION['account_backup_restore_mode'] ?? ($logged_in ? 'merge' : 'create');
        $decisions = [
            'mode' => $mode,
            'account_email_override' => input_clean_email($_POST['account_email_override'] ?? ''),
            'id_exists_own' => 'skip',
            'id_exists_other' => in_array($_POST['id_exists_other'] ?? 'new_id', ['skip', 'new_id']) ? $_POST['id_exists_other'] : 'new_id',
            'slug_taken' => in_array($_POST['slug_taken'] ?? 'suffix', ['skip', 'suffix']) ? $_POST['slug_taken'] : 'suffix',
            'missing_theme' => 'default',
            'missing_domain' => 'main',
            'missing_media' => 'continue',
            'missing_tables' => 'skip',
        ];

        if($mode === 'create') {
            $package = $backup->read_package($_SESSION['account_backup_import_dir']);
            $created = $backup->create_account_from_backup($package['account'], $decisions);
            $log = $backup->apply_import($_SESSION['account_backup_import_dir'], $created['user'], $decisions);
            array_unshift($log, ['table' => 'users', 'status' => 'created', 'email' => $created['email'], 'notes' => $created['notes']]);

            $backup->rrmdir($_SESSION['account_backup_import_dir']);
            unset($_SESSION['account_backup_import_dir'], $_SESSION['account_backup_restore_mode']);

            \Altum\Logger::users($created['user']->user_id, 'account.backup.created');
            $_SESSION['account_backup_import_log'] = $log;

            if(!$logged_in) {
                session_set('user_id', $created['user']->user_id);
                session_set('user_password_hash', md5($created['user']->password ?? ''));
                Alerts::add_success(sprintf(l('account_backup.success.created_logged_in'), $created['email']));
                redirect('dashboard');
            }

            Alerts::add_success(sprintf(l('account_backup.success.created'), $created['email']));
            redirect('account-backup');
        }

        $log = $backup->apply_import($_SESSION['account_backup_import_dir'], $this->user, $decisions);
        $backup->rrmdir($_SESSION['account_backup_import_dir']);
        unset($_SESSION['account_backup_import_dir'], $_SESSION['account_backup_restore_mode']);

        \Altum\Logger::users($this->user->user_id, 'account.backup.merged');
        Alerts::add_success(l('account_backup.success.merged'));
        $_SESSION['account_backup_import_log'] = $log;
        redirect('account-backup');
    }

    private function import_cancel(AccountBackupModel $backup, $logged_in) {
        if(!empty($_SESSION['account_backup_import_dir'])) {
            $backup->rrmdir($_SESSION['account_backup_import_dir']);
            unset($_SESSION['account_backup_import_dir'], $_SESSION['account_backup_restore_mode']);
        }
        Alerts::add_info(l('account_backup.info.cancelled'));
        redirect($logged_in ? 'account-backup' : 'account-restore');
    }
}
