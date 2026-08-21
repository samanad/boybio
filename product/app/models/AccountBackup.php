<?php
/* cloub.io account backup: export / import biolinks + account data */

namespace Altum\Models;

defined('ALTUMCODE') || die();

class AccountBackup extends Model {

    public const FORMAT = 'cloub-account-backup';
    public const FORMAT_VERSION = 1;

    public const TABLES = [
        'projects' => 'project_id',
        'pixels' => 'pixel_id',
        'domains' => 'domain_id',
        'splash_pages' => 'splash_page_id',
        'payment_processors' => 'payment_processor_id',
        'notification_handlers' => 'notification_handler_id',
        'links' => 'link_id',
        'biolink_blocks' => 'biolink_block_id',
        'qr_codes' => 'qr_code_id',
        'data' => 'datum_id',
    ];

    public const SKIP_ACCOUNT_ON_IMPORT = [
        'user_id', 'status', 'source', 'ip', 'continent_code', 'country', 'city_name',
        'device_type', 'os_name', 'browser_name', 'browser_language', 'total_logins',
        'last_activity', 'last_login_datetime', 'datetime', 'payment_subscription_id',
        'payment_processor', 'payment_total_amount', 'payment_currency', 'token_code',
        'email_activation_code', 'lost_password_code',
    ];

    public function table_exists($table) {
        $table = preg_replace('/[^a-z0-9_]/i', '', $table);
        $result = database()->query("SHOW TABLES LIKE '{$table}'");
        return $result && $result->num_rows > 0;
    }

    public function table_columns($table) {
        $table = preg_replace('/[^a-z0-9_]/i', '', $table);
        $columns = [];
        $result = database()->query("SHOW COLUMNS FROM `{$table}`");
        if(!$result) return $columns;
        while($row = $result->fetch_object()) {
            $columns[$row->Field] = $row;
        }
        return $columns;
    }

    public function offload_is_ready() {
        return \Altum\Plugin::is_active('offload') && !empty(settings()->offload->uploads_url);
    }

    public function public_file_url($uploads_key, $filename) {
        if(!$filename) return null;
        return \Altum\Uploads::get_full_url($uploads_key) . $filename;
    }

    public function local_file_path($uploads_key, $filename) {
        if(!$filename) return null;
        return \Altum\Uploads::get_full_path($uploads_key) . $filename;
    }

    public function file_is_on_server($uploads_key, $filename) {
        $path = $this->local_file_path($uploads_key, $filename);
        return $path && is_file($path);
    }

    public function fetch_user_rows($user_id) {
        $out = [];
        foreach(self::TABLES as $table => $pk) {
            if(!$this->table_exists($table)) {
                $out[$table] = ['_missing' => true, 'rows' => []];
                continue;
            }
            $columns = $this->table_columns($table);
            if(!isset($columns['user_id'])) {
                $out[$table] = ['_missing' => true, 'rows' => []];
                continue;
            }
            $rows = [];
            $user_id = (int) $user_id;
            $result = database()->query("SELECT * FROM `{$table}` WHERE `user_id` = {$user_id}");
            if($result) {
                while($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
            }
            $out[$table] = ['pk' => $pk, 'rows' => $rows];
        }
        return $out;
    }

    public function account_payload($user) {
        $row = db()->where('user_id', $user->user_id)->getOne('users');
        if(!$row) return null;
        $data = (array) $row;
        $data['_meta'] = [
            'source_site' => defined('SITE_URL') ? SITE_URL : '',
            'source_user_id' => (int) $user->user_id,
        ];
        return $data;
    }

    public function collect_media($account, $tables) {
        $media = [];

        $this->add_media_file($media, 'users', $account['avatar'] ?? null, 'account.avatar');
        $this->harvest_media($media, $account, 'account');

        foreach($tables as $table => $payload) {
            foreach(($payload['rows'] ?? []) as $row) {
                $this->harvest_media($media, $row, $table);
            }
        }

        foreach(($tables['links']['rows'] ?? []) as $link) {
            $settings = $this->decode_json($link['settings'] ?? null);
            $this->add_media_file($media, 'biolink_background', $settings->background ?? null, 'links.background');
            $this->add_media_file($media, 'favicons', $settings->favicon ?? null, 'links.favicon');
            $this->add_media_file($media, 'biolink_seo_image', $settings->seo->image ?? ($settings->seo_image ?? null), 'links.seo_image');
            $this->add_media_file($media, 'avatars', $settings->vcard_avatar ?? null, 'links.vcard_avatar');
            $this->add_media_file($media, 'files', $settings->file ?? null, 'links.file');
            $this->add_media_file($media, 'favicons', $settings->cloaking_favicon ?? null, 'links.cloaking_favicon');
            $this->add_media_file($media, 'opengraph', $settings->cloaking_opengraph ?? null, 'links.cloaking_opengraph');
            if(!empty($settings->static_folder)) {
                $this->add_media_folder($media, 'static', $settings->static_folder, 'links.static_folder');
            }
        }

        foreach(($tables['biolink_blocks']['rows'] ?? []) as $block) {
            $settings = $this->decode_json($block['settings'] ?? null);
            foreach(['image', 'image_new', 'poster', 'video', 'audio'] as $field) {
                $this->add_media_file($media, 'block_images', $settings->{$field} ?? null, 'blocks.' . $field);
            }
            $this->add_media_file($media, 'block_thumbnail_images', $settings->thumbnail ?? ($settings->image ?? null), 'blocks.thumbnail');
            $this->add_media_file($media, 'files', $settings->file ?? ($settings->video ?? ($settings->audio ?? null)), 'blocks.file');
            $this->add_media_file($media, 'products_files', $settings->file ?? null, 'blocks.product_file');
            $this->add_media_file($media, 'avatars', $settings->avatar ?? ($settings->vcard_avatar ?? null), 'blocks.avatar');
        }

        foreach(($tables['qr_codes']['rows'] ?? []) as $qr) {
            $this->add_media_file($media, 'qr_code', $qr['qr_code'] ?? null, 'qr.qr_code');
            $this->add_media_file($media, 'qr_code_logo', $qr['qr_code_logo'] ?? null, 'qr.logo');
            $this->add_media_file($media, 'qr_code_background', $qr['qr_code_background'] ?? null, 'qr.background');
            $this->add_media_file($media, 'qr_code_foreground', $qr['qr_code_foreground'] ?? null, 'qr.foreground');
        }

        foreach(($tables['splash_pages']['rows'] ?? []) as $splash) {
            $settings = $this->decode_json($splash['settings'] ?? null);
            $this->add_media_file($media, 'splash_pages', $settings->logo ?? null, 'splash.logo');
            $this->add_media_file($media, 'splash_pages', $settings->favicon ?? null, 'splash.favicon');
            $this->add_media_file($media, 'splash_pages', $settings->opengraph ?? null, 'splash.opengraph');
        }

        return array_values($media);
    }

    private function harvest_media(&$media, $node, $source, $field = null) {
        if(is_string($node)) {
            $decoded = json_decode($node);
            if(json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                $this->harvest_media($media, $decoded, $source, $field);
                return;
            }
            $this->add_media_from_string($media, $node, $source, $field);
            return;
        }
        if(is_object($node) || is_array($node)) {
            foreach((array) $node as $key => $value) {
                $this->harvest_media($media, $value, $source, is_string($key) ? $key : $field);
            }
        }
    }

    private function add_media_from_string(&$media, $value, $source, $field = null) {
        if(!is_string($value) || $value === '') return;

        $parsed = $this->parse_upload_reference($value);
        if($parsed) {
            if(!empty($parsed['folder'])) {
                $this->add_media_folder($media, $parsed['key'], $parsed['filename'], $source . '.' . ($field ?: 'url'));
            } else {
                $this->add_media_file($media, $parsed['key'], $parsed['filename'], $source . '.' . ($field ?: 'url'));
            }
            return;
        }

        $key = $this->uploads_key_from_field($field);
        if($key) {
            $this->add_media_file($media, $key, $value, $source . '.' . $field);
        }
    }

    private function uploads_key_from_field($field) {
        $map = [
            'avatar' => 'users',
            'vcard_avatar' => 'avatars',
            'background' => 'biolink_background',
            'favicon' => 'favicons',
            'image' => 'block_images',
            'image_new' => 'block_images',
            'poster' => 'block_images',
            'thumbnail' => 'block_thumbnail_images',
            'file' => 'files',
            'video' => 'files',
            'audio' => 'files',
            'logo' => 'splash_pages',
            'opengraph' => 'splash_pages',
            'qr_code' => 'qr_code',
            'qr_code_logo' => 'qr_code_logo',
            'qr_code_background' => 'qr_code_background',
            'qr_code_foreground' => 'qr_code_foreground',
            'cloaking_favicon' => 'favicons',
            'cloaking_opengraph' => 'opengraph',
            'white_label_logo_light' => 'users',
            'white_label_logo_dark' => 'users',
            'white_label_favicon' => 'users',
        ];
        return $map[$field] ?? null;
    }

    private function parse_upload_reference($value) {
        if(!is_string($value) || $value === '') return null;
        $path = $value;
        if(preg_match('#https?://#i', $value)) {
            $parts = parse_url($value);
            $path = $parts['path'] ?? '';
        }
        $path = ltrim($path, '/');
        if(str_starts_with($path, UPLOADS_URL_PATH)) {
            $path = mb_substr($path, mb_strlen(UPLOADS_URL_PATH));
        }
        foreach(['uploads/', 'upload/'] as $prefix) {
            if(str_starts_with($path, $prefix)) {
                $path = mb_substr($path, mb_strlen($prefix));
            }
        }
        $path = ltrim($path, '/');
        if($path === '' || str_contains($path, '..')) return null;

        $segments = explode('/', $path);
        if(count($segments) < 2) return null;
        $folder = $segments[0] . '/';
        $filename = end($segments);
        $uploads = \Altum\Uploads::$uploads ?: (require APP_PATH . 'includes/uploads.php');
        \Altum\Uploads::$uploads = $uploads;
        foreach($uploads as $key => $conf) {
            $conf_path = $conf['path'] ?? ($key . '/');
            if($conf_path === $folder || rtrim($conf_path, '/') === rtrim($folder, '/')) {
                $is_folder = count($segments) > 2 || !str_contains($filename, '.');
                return ['key' => $key, 'filename' => $is_folder && count($segments) > 2 ? implode('/', array_slice($segments, 1)) : $filename, 'folder' => false];
            }
        }
        return null;
    }

    private function decode_json($value) {
        if(is_object($value) || is_array($value)) return (object) json_decode(json_encode($value));
        if(!is_string($value) || $value === '') return (object) [];
        $decoded = json_decode($value);
        return is_object($decoded) ? $decoded : (object) [];
    }

    private function looks_like_filename($name) {
        if(!is_string($name) || $name === '' || mb_strlen($name) > 240) return false;
        if(str_contains($name, '/') || str_contains($name, '\\')) return false;
        if(str_starts_with($name, 'http://') || str_starts_with($name, 'https://')) return false;
        return (bool) preg_match('/^[A-Za-z0-9._-]+\.[A-Za-z0-9]{2,8}$/', $name);
    }

    private function add_media_file(&$media, $uploads_key, $filename, $source) {
        if(!$this->looks_like_filename($filename)) return;
        $id = $uploads_key . '/' . $filename;
        if(isset($media[$id])) return;

        $on_server = $this->file_is_on_server($uploads_key, $filename);
        $media[$id] = [
            'key' => $uploads_key,
            'filename' => $filename,
            'path' => \Altum\Uploads::get_path($uploads_key) . $filename,
            'url' => $this->public_file_url($uploads_key, $filename),
            's3_key' => UPLOADS_URL_PATH . \Altum\Uploads::get_path($uploads_key) . $filename,
            'on_server' => $on_server,
            'storage' => $on_server ? 'server' : ($this->offload_is_ready() ? 'offload' : 'missing'),
            'folder' => false,
            'source' => $source,
        ];
    }

    private function add_media_folder(&$media, $uploads_key, $folder, $source) {
        if(!is_string($folder) || $folder === '' || str_contains($folder, '..')) return;
        $id = $uploads_key . '/' . $folder . '/';
        if(isset($media[$id])) return;
        $local = rtrim($this->local_file_path($uploads_key, $folder), '/') . '/';
        $on_server = is_dir($local);
        $media[$id] = [
            'key' => $uploads_key,
            'filename' => $folder,
            'path' => \Altum\Uploads::get_path($uploads_key) . $folder . '/',
            'url' => $this->public_file_url($uploads_key, $folder . '/'),
            's3_key' => UPLOADS_URL_PATH . \Altum\Uploads::get_path($uploads_key) . $folder . '/',
            'on_server' => $on_server,
            'storage' => $on_server ? 'server' : ($this->offload_is_ready() ? 'offload' : 'missing'),
            'folder' => true,
            'source' => $source,
        ];
    }

    public function build_package($user, $destination) {
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');
        $account = $this->account_payload($user);
        $tables = $this->fetch_user_rows($user->user_id);
        $media = $this->collect_media($account, $tables);

        $manifest = [
            'format' => self::FORMAT,
            'format_version' => self::FORMAT_VERSION,
            'product' => defined('PRODUCT_NAME') ? PRODUCT_NAME : '66biolinks',
            'exported_at' => date('c'),
            'destination' => $destination,
            'source_site' => defined('SITE_URL') ? SITE_URL : '',
            'source_user_id' => (int) $user->user_id,
            'source_email' => $user->email,
            'counts' => [
                'links' => count($tables['links']['rows'] ?? []),
                'biolink_blocks' => count($tables['biolink_blocks']['rows'] ?? []),
                'media' => count($media),
            ],
            'offload' => [
                'active' => $this->offload_is_ready(),
                'uploads_url' => $this->offload_is_ready() ? settings()->offload->uploads_url : null,
                'cdn_uploads_url' => $this->offload_is_ready() ? (settings()->offload->cdn_uploads_url ?? null) : null,
            ],
        ];

        $dir = rtrim(sys_get_temp_dir(), '/') . '/cloub-backup-' . $user->user_id . '-' . bin2hex(random_bytes(4));
        mkdir($dir, 0777, true);
        mkdir($dir . '/tables', 0777, true);
        mkdir($dir . '/media', 0777, true);

        file_put_contents($dir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        file_put_contents($dir . '/account.json', json_encode($account, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        foreach($tables as $table => $payload) {
            file_put_contents($dir . '/tables/' . $table . '.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        $media_written = [];
        $bytes = 0;
        $failed = 0;
        foreach($media as $item) {
            $include_bytes = $destination === 'pc' || !empty($item['on_server']);
            $item['included'] = false;
            if($include_bytes) {
                $ok = $this->copy_media_into_package($dir, $item);
                $item['included'] = $ok;
                $item['missing'] = !$ok;
                if($ok && empty($item['folder'])) {
                    $packed = $dir . '/media/' . $item['path'];
                    $item['bytes'] = is_file($packed) ? filesize($packed) : 0;
                    $bytes += $item['bytes'];
                }
                if(!$ok) $failed++;
            } else {
                $item['included'] = false;
                $item['reference_only'] = true;
            }
            $media_written[] = $item;
        }
        $manifest['counts']['media_bytes'] = $bytes;
        $manifest['counts']['media_failed'] = $failed;
        file_put_contents($dir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        file_put_contents($dir . '/media-index.json', json_encode($media_written, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $zip_path = $dir . '.zip';
        $this->zip_directory($dir, $zip_path);
        $this->rrmdir($dir);

        return [
            'zip_path' => $zip_path,
            'manifest' => $manifest,
            'filename' => 'cloub-account-' . $user->user_id . '-' . date('Ymd-His') . '.zip',
        ];
    }

    private function copy_media_into_package($dir, $item) {
        $target_rel = 'media/' . $item['path'];
        $target = $dir . '/' . $target_rel;
        $target_dir = dirname($target);
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        if(!empty($item['folder'])) {
            if(!empty($item['on_server'])) {
                $this->rcopy(rtrim($this->local_file_path($item['key'], $item['filename']), '/') . '/', $target);
                return is_dir($target);
            }
            return $this->download_offload_prefix($item, $target);
        }

        if(!empty($item['on_server']) && is_file($this->local_file_path($item['key'], $item['filename']))) {
            return copy($this->local_file_path($item['key'], $item['filename']), $target);
        }

        if($this->download_offload_object($item, $target)) {
            return true;
        }

        foreach($this->public_url_candidates($item) as $url) {
            if($this->download_url_to($url, $target)) return true;
        }
        return false;
    }

    private function public_url_candidates($item) {
        $urls = [];
        $path = ltrim($item['path'] ?? '', '/');
        $filename = $item['filename'] ?? '';
        $key = $item['key'] ?? '';
        if(!empty($item['url'])) $urls[] = $item['url'];
        if($key && $filename) $urls[] = $this->public_file_url($key, $filename);
        if($this->offload_is_ready()) {
            foreach([settings()->offload->cdn_uploads_url ?? null, settings()->offload->uploads_url ?? null] as $base) {
                if(!$base) continue;
                $urls[] = rtrim($base, '/') . '/' . $path;
                $urls[] = rtrim($base, '/') . '/' . UPLOADS_URL_PATH . $path;
            }
        }
        return array_values(array_unique(array_filter($urls)));
    }

    private function download_offload_object($item, $dest) {
        if(!$this->offload_is_ready()) return false;
        $key = $item['s3_key'] ?? (UPLOADS_URL_PATH . ($item['path'] ?? ''));
        if($key === '' || $key === UPLOADS_URL_PATH) return false;
        try {
            $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
            $s3->getObject([
                'Bucket' => settings()->offload->storage_name,
                'Key' => $key,
                'SaveAs' => $dest,
            ]);
            return is_file($dest) && filesize($dest) > 0;
        } catch(\Exception $exception) {
            @unlink($dest);
            return false;
        }
    }

    private function download_offload_prefix($item, $target_dir) {
        if(!$this->offload_is_ready()) return false;
        try {
            $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
            $prefix = UPLOADS_URL_PATH . $item['path'];
            $objects = $s3->getIterator('ListObjectsV2', [
                'Bucket' => settings()->offload->storage_name,
                'Prefix' => $prefix,
            ]);
            $any = false;
            foreach($objects as $object) {
                $key = $object['Key'] ?? '';
                if($key === '' || str_ends_with($key, '/')) continue;
                $rel = mb_substr($key, mb_strlen($prefix));
                if($rel === '' || str_contains($rel, '..')) continue;
                $local = rtrim($target_dir, '/') . '/' . $rel;
                if(!is_dir(dirname($local))) mkdir(dirname($local), 0777, true);
                $s3->getObject([
                    'Bucket' => settings()->offload->storage_name,
                    'Key' => $key,
                    'SaveAs' => $local,
                ]);
                $any = true;
            }
            return $any;
        } catch(\Exception $exception) {
            return false;
        }
    }

    private function download_url_to($url, $dest) {
        $fp = fopen($dest, 'w');
        if(!$fp) return false;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 cloub-account-backup',
            CURLOPT_FAILONERROR => false,
        ]);
        $ok = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);
        if(!$ok || $code >= 400 || !is_file($dest) || filesize($dest) < 1) {
            @unlink($dest);
            return false;
        }
        return true;
    }

    private function zip_directory($dir, $zip_path) {
        $zip = new \ZipArchive();
        if($zip->open($zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create zip');
        }
        $dir = realpath($dir);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach($files as $file) {
            $full = $file->getRealPath();
            $local = ltrim(str_replace($dir, '', $full), '/');
            $zip->addFile($full, $local);
            if(str_starts_with($local, 'media/') && is_file($full) && filesize($full) > 1024 * 1024) {
                $zip->setCompressionName($local, \ZipArchive::CM_STORE);
            }
        }
        $zip->close();
    }

    public function account_offload_folder($user_id) {
        $user_id = (int) $user_id;
        return UPLOADS_URL_PATH . \Altum\Uploads::get_path('account_backups') . 'account-' . $user_id . '/';
    }

    public function account_offload_prefixes($user_id) {
        $user_id = (int) $user_id;
        return [
            $this->account_offload_folder($user_id),
            UPLOADS_URL_PATH . \Altum\Uploads::get_path('account_backups') . $user_id . '/',
        ];
    }

    public function key_belongs_to_account($key, $user_id) {
        if(!$key || str_contains($key, '..')) return false;
        foreach($this->account_offload_prefixes($user_id) as $prefix) {
            if(str_starts_with($key, $prefix)) return true;
        }
        return false;
    }

    private function put_offload_object($s3, $params) {
        try {
            $s3->putObject($params);
        } catch(\Exception $exception) {
            $params['ACL'] = 'private';
            $s3->putObject($params);
        }
    }

    public function ensure_account_offload_folder($user) {
        $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
        $folder = $this->account_offload_folder($user->user_id);
        $this->put_offload_object($s3, [
            'Bucket' => settings()->offload->storage_name,
            'Key' => $folder,
            'Body' => '',
            'ContentType' => 'application/x-directory',
        ]);
        return $folder;
    }

    public function upload_package_to_offload($user, $zip_path, $filename) {
        if(!$this->offload_is_ready()) {
            throw new \RuntimeException('offload_not_ready');
        }
        $folder = $this->ensure_account_offload_folder($user);
        $key = $folder . $filename;
        $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
        $this->put_offload_object($s3, [
            'Bucket' => settings()->offload->storage_name,
            'Key' => $key,
            'SourceFile' => $zip_path,
            'ContentType' => 'application/zip',
        ]);
        return [
            'key' => $key,
            'folder' => $folder,
            'url' => rtrim((string) (settings()->offload->cdn_uploads_url ?: settings()->offload->uploads_url), '/') . '/' . ltrim(str_replace(UPLOADS_URL_PATH, '', $key), '/'),
        ];
    }

    public function list_offload_packages($user_id) {
        if(!$this->offload_is_ready()) return [];
        try {
            $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
            $list = [];
            $seen = [];
            foreach($this->account_offload_prefixes($user_id) as $prefix) {
                $objects = $s3->getIterator('ListObjectsV2', [
                    'Bucket' => settings()->offload->storage_name,
                    'Prefix' => $prefix,
                ]);
                foreach($objects as $object) {
                    $key = $object['Key'] ?? '';
                    if(isset($seen[$key]) || !str_ends_with(mb_strtolower($key), '.zip')) continue;
                    $seen[$key] = true;
                    $list[] = [
                        'key' => $key,
                        'filename' => basename($key),
                        'size' => $object['Size'] ?? 0,
                        'modified' => isset($object['LastModified']) ? (string) $object['LastModified'] : null,
                    ];
                }
            }
            return $list;
        } catch(\Exception $exception) {
            return [];
        }
    }

    public function download_offload_package($key, $dest) {
        $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
        $s3->getObject([
            'Bucket' => settings()->offload->storage_name,
            'Key' => $key,
            'SaveAs' => $dest,
        ]);
        return is_file($dest);
    }

    public function extract_package($zip_path, $dest_dir = null) {
        $dest_dir = $dest_dir ?: (rtrim(sys_get_temp_dir(), '/') . '/cloub-import-' . bin2hex(random_bytes(6)));
        mkdir($dest_dir, 0777, true);
        $zip = new \ZipArchive();
        if($zip->open($zip_path) !== true) {
            throw new \RuntimeException('invalid_zip');
        }
        $zip->extractTo($dest_dir);
        $zip->close();

        $manifest_path = $dest_dir . '/manifest.json';
        if(!is_file($manifest_path)) {
            throw new \RuntimeException('invalid_package');
        }
        $manifest = json_decode(file_get_contents($manifest_path));
        if(!$manifest || ($manifest->format ?? null) !== self::FORMAT) {
            throw new \RuntimeException('invalid_package');
        }
        return $dest_dir;
    }

    public function read_package($dir) {
        $manifest = json_decode(file_get_contents($dir . '/manifest.json'), true);
        $account = json_decode(file_get_contents($dir . '/account.json'), true);
        $media_index = is_file($dir . '/media-index.json') ? json_decode(file_get_contents($dir . '/media-index.json'), true) : [];
        $tables = [];
        foreach(self::TABLES as $table => $pk) {
            $path = $dir . '/tables/' . $table . '.json';
            $tables[$table] = is_file($path) ? json_decode(file_get_contents($path), true) : ['pk' => $pk, 'rows' => []];
        }
        return compact('manifest', 'account', 'tables', 'media_index');
    }

    public function detect_conflicts($package, $target_user, $mode = 'merge') {
        $conflicts = [];
        $account = $package['account'];
        $target_id = $target_user ? (int) $target_user->user_id : 0;
        $target_email = $target_user ? mb_strtolower($target_user->email) : '';

        if($mode === 'create') {
            $email = $account['email'] ?? '';
            $taken = $email && db()->where('email', $email)->has('users');
            if($taken) {
                $conflicts[] = [
                    'id' => 'account_email',
                    'severity' => 'error',
                    'message' => sprintf(l('account_backup.conflict.email_taken_create'), $email),
                    'options' => ['override'],
                    'needs_email' => true,
                ];
            }
            $exported_plan = $account['plan_id'] ?? null;
            $plan_exists = $exported_plan && (db()->where('plan_id', $exported_plan)->has('plans') || in_array($exported_plan, ['free', 'custom']));
            if($exported_plan && !$plan_exists) {
                $conflicts[] = [
                    'id' => 'account_plan',
                    'severity' => 'info',
                    'message' => sprintf(l('account_backup.conflict.plan_missing'), $exported_plan),
                    'options' => ['keep'],
                ];
            }
        } else {
            $conflicts[] = [
                'id' => 'merge_account',
                'severity' => 'info',
                'message' => l('account_backup.conflict.merge_account'),
                'options' => ['keep'],
            ];
        }

        $own_id = 0;
        $other_id = 0;
        $slug = 0;
        $domain_host = 0;
        $domain_examples = [];
        $missing_tables = [];
        $missing_themes = [];
        $id_own_examples = [];
        $id_other_examples = [];
        $slug_examples = [];

        $themes = [];
        if($this->table_exists('biolinks_themes')) {
            $result = database()->query("SELECT `biolink_theme_id` FROM `biolinks_themes`");
            while($result && $row = $result->fetch_object()) {
                $themes[(int) $row->biolink_theme_id] = true;
            }
        }

        foreach($package['tables'] as $table => $payload) {
            if(!empty($payload['_missing']) || !$this->table_exists($table)) {
                if(!empty(($payload['rows'] ?? []))) $missing_tables[] = $table;
                continue;
            }
            $pk = $payload['pk'] ?? (self::TABLES[$table] ?? null);
            $columns = $this->table_columns($table);
            foreach(($payload['rows'] ?? []) as $row) {
                $id = $row[$pk] ?? null;
                if($id && isset($columns[$pk])) {
                    $existing = db()->where($pk, $id)->getOne($table, [$pk, 'user_id']);
                    if($existing) {
                        if($target_id && (int) $existing->user_id === $target_id) {
                            $own_id++;
                            if(count($id_own_examples) < 8) $id_own_examples[] = $table . '#' . $id;
                        } else {
                            $other_id++;
                            if(count($id_other_examples) < 8) $id_other_examples[] = $table . '#' . $id;
                        }
                    }
                }
                if($table === 'links' && !empty($row['url'])) {
                    $query = db()->where('url', $row['url']);
                    if(isset($row['domain_id'])) $query->where('domain_id', $row['domain_id']);
                    $taken_link = $query->getOne('links', ['link_id', 'user_id', 'url']);
                    if($taken_link && (int) $taken_link->link_id !== (int) ($row['link_id'] ?? 0)) {
                        $slug++;
                        if(count($slug_examples) < 8) $slug_examples[] = $row['url'];
                    }
                }
                if($table === 'domains' && !empty($row['host'])) {
                    $taken_domain = db()->where('host', $row['host'])->getOne('domains', ['domain_id', 'user_id', 'host']);
                    if($taken_domain && (int) $taken_domain->domain_id !== (int) ($row['domain_id'] ?? 0) && (int) $taken_domain->user_id !== $target_id) {
                        $domain_host++;
                        if(count($domain_examples) < 8) $domain_examples[] = $row['host'];
                    }
                }
                if($table === 'links') {
                    $settings = $this->decode_json($row['settings'] ?? null);
                    $theme_id = (int) ($settings->biolink_theme_id ?? ($row['biolink_theme_id'] ?? 0));
                    if($theme_id && !isset($themes[$theme_id])) {
                        $missing_themes[$theme_id] = true;
                    }
                }
            }
        }

        if($own_id) {
            $conflicts[] = [
                'id' => 'id_exists_own',
                'severity' => 'info',
                'message' => sprintf(l('account_backup.conflict.id_exists_own_merge'), $own_id, implode(', ', $id_own_examples)),
                'options' => ['skip'],
                'count' => $own_id,
            ];
        }
        if($other_id) {
            $conflicts[] = [
                'id' => 'id_exists_other',
                'severity' => 'error',
                'message' => sprintf(l('account_backup.conflict.id_exists_other'), $other_id, implode(', ', $id_other_examples)),
                'options' => ['skip', 'new_id'],
                'count' => $other_id,
            ];
        }
        if($slug) {
            $conflicts[] = [
                'id' => 'slug_taken',
                'severity' => 'ask',
                'message' => sprintf(l('account_backup.conflict.slug_taken'), $slug, implode(', ', $slug_examples)),
                'options' => ['skip', 'suffix'],
                'count' => $slug,
            ];
        }
        if($domain_host) {
            $conflicts[] = [
                'id' => 'domain_taken',
                'severity' => 'ask',
                'message' => sprintf(l('account_backup.conflict.domain_taken'), $domain_host, implode(', ', $domain_examples)),
                'options' => ['skip'],
                'count' => $domain_host,
            ];
        }
        if($missing_tables) {
            $conflicts[] = [
                'id' => 'missing_tables',
                'severity' => 'info',
                'message' => sprintf(l('account_backup.conflict.missing_tables'), implode(', ', $missing_tables)),
                'options' => ['skip'],
            ];
        }
        if($missing_themes) {
            $conflicts[] = [
                'id' => 'missing_theme',
                'severity' => 'ask',
                'message' => sprintf(l('account_backup.conflict.missing_theme'), implode(', ', array_keys($missing_themes))),
                'options' => ['default'],
            ];
        }

        $media_missing = 0;
        $package_dir = $package['_dir'] ?? '';
        foreach(($package['media_index'] ?? []) as $item) {
            $in_zip = $package_dir && !empty($item['path']) && is_file($package_dir . '/media/' . $item['path']);
            $on_cloud = !empty($item['url']);
            if(!$in_zip && !$on_cloud && empty($item['folder'])) $media_missing++;
        }
        if($media_missing) {
            $conflicts[] = [
                'id' => 'missing_media',
                'severity' => 'info',
                'message' => sprintf(l('account_backup.conflict.missing_media'), $media_missing),
                'options' => ['continue'],
            ];
        }

        $conflicts[] = [
            'id' => 'missing_domain',
            'severity' => 'info',
            'message' => l('account_backup.conflict.missing_domain'),
            'options' => ['main'],
        ];

        return $conflicts;
    }


    public function create_account_from_backup($account, $decisions = []) {
        $columns = $this->table_columns('users');
        $notes = [];
        $insert = [];

        $skip = array_flip(array_merge(self::SKIP_ACCOUNT_ON_IMPORT, ['type', 'user_id', 'token_code', 'api_key', 'referral_key']));

        foreach($account as $field => $value) {
            if($field[0] === '_' || isset($skip[$field]) || !isset($columns[$field])) continue;
            if(is_array($value) || is_object($value)) $value = json_encode($value);
            $insert[$field] = $value;
        }

        $email = input_clean_email($decisions['account_email_override'] ?? ($account['email'] ?? ''));
        if(!$email || filter_var($email, FILTER_VALIDATE_EMAIL) == false) {
            throw new \RuntimeException('email_invalid');
        }
        if(db()->where('email', $email)->has('users')) {
            throw new \RuntimeException('email_taken');
        }
        $insert['email'] = $email;

        if(empty($insert['password'] ?? null)) {
            throw new \RuntimeException('password_missing');
        }

        $insert['name'] = $insert['name'] ?? 'User';
        $insert['status'] = 1;
        $insert['type'] = 0;
        $insert['source'] = 'account_backup';
        $insert['datetime'] = get_date();
        $insert['token_code'] = md5(uniqid('', true) . random_bytes(16));
        $insert['email_activation_code'] = null;
        $insert['lost_password_code'] = null;
        $insert['payment_subscription_id'] = '';
        $insert['payment_processor'] = '';
        $insert['payment_total_amount'] = 0;
        $insert['payment_currency'] = '';
        $insert['total_logins'] = 0;

        $api_key = $account['api_key'] ?? '';
        if(!$api_key || (isset($columns['api_key']) && db()->where('api_key', $api_key)->has('users'))) {
            $api_key = md5(uniqid('', true) . random_bytes(16));
            $notes[] = 'api_key_regenerated';
        }
        if(isset($columns['api_key'])) $insert['api_key'] = $api_key;

        $referral_key = $account['referral_key'] ?? '';
        if(!$referral_key || (isset($columns['referral_key']) && db()->where('referral_key', $referral_key)->has('users'))) {
            $referral_key = md5(uniqid('', true) . random_bytes(16));
            $notes[] = 'referral_key_regenerated';
        }
        if(isset($columns['referral_key'])) $insert['referral_key'] = $referral_key;

        $plan_id = $account['plan_id'] ?? 'free';
        $plan_exists = $plan_id && (db()->where('plan_id', $plan_id)->has('plans') || in_array($plan_id, ['free', 'custom']));
        if(!$plan_exists) {
            $insert['plan_id'] = 'free';
            if(isset($columns['plan_settings'])) $insert['plan_settings'] = json_encode(settings()->plan_free->settings);
            $insert['plan_expiration_date'] = get_date();
            $notes[] = 'plan_fell_back_to_free';
        }

        foreach(array_keys($insert) as $field) {
            if(!isset($columns[$field])) unset($insert[$field]);
        }

        $user_id = db()->insert('users', $insert);
        if(!$user_id) {
            throw new \RuntimeException('account_create_failed');
        }

        cache()->deleteItemsByTag('user_id=' . $user_id);

        return [
            'user' => (new User())->get_user_by_user_id($user_id),
            'notes' => $notes,
            'email' => $email,
        ];
    }

    public function apply_import($package_dir, $target_user, $decisions) {
        @set_time_limit(0);
        $package = $this->read_package($package_dir);
        $package['_dir'] = $package_dir;
        $log = [];
        $remap = [];
        $mode = $decisions['mode'] ?? 'merge';

        database()->query('SET FOREIGN_KEY_CHECKS = 0');

        if($mode === 'merge') {
            $log[] = ['table' => 'users', 'status' => 'unchanged', 'notes' => ['merge_keeps_current_account']];
        }
        $target_user = (new User())->get_user_by_user_id($target_user->user_id);

        $order = array_keys(self::TABLES);
        foreach($order as $table) {
            $payload = $package['tables'][$table] ?? null;
            if(!$payload || !$this->table_exists($table) || !empty($payload['_missing'])) {
                if(!empty($payload['rows'])) $log[] = ['table' => $table, 'status' => 'skipped_missing_table', 'count' => count($payload['rows'])];
                continue;
            }
            $result = $this->import_table($table, $payload, $target_user, $decisions, $remap, $package_dir);
            $log[] = $result;
        }

        $media_result = $this->import_media($package['media_index'] ?? [], $package_dir, $decisions);
        $log[] = $media_result;

        database()->query('SET FOREIGN_KEY_CHECKS = 1');
        cache()->deleteItemsByTag('user_id=' . $target_user->user_id);

        return $log;
    }

    private function import_account($account, $target_user, $decisions) {
        $columns = $this->table_columns('users');
        $update = [];
        $notes = [];

        $safe = ['name', 'billing', 'timezone', 'anti_phishing_code', 'is_newsletter_subscribed', 'language', 'extra', 'preferences', 'avatar', 'currency'];
        foreach($safe as $field) {
            if(array_key_exists($field, $account) && isset($columns[$field])) {
                $update[$field] = $account[$field];
            }
        }

        if(($decisions['account_email'] ?? 'keep') === 'import' && !empty($account['email']) && isset($columns['email'])) {
            if(!db()->where('email', $account['email'])->has('users')) {
                $update['email'] = $account['email'];
            } else {
                $notes[] = 'email_not_imported_taken';
            }
        } else {
            $notes[] = 'email_kept';
        }

        if(($decisions['account_credentials'] ?? 'keep') === 'import') {
            foreach(['password', 'twofa_secret', 'api_key', 'referral_key'] as $field) {
                if(!empty($account[$field]) && isset($columns[$field])) {
                    if($field === 'referral_key' && db()->where('referral_key', $account[$field])->has('users')) {
                        $notes[] = 'referral_key_not_imported_taken';
                        continue;
                    }
                    $update[$field] = $account[$field];
                }
            }
        } else {
            $notes[] = 'credentials_kept';
        }

        if(($decisions['account_plan'] ?? 'keep') === 'import' && !empty($account['plan_id'])) {
            $plan_exists = db()->where('plan_id', $account['plan_id'])->has('plans') || in_array($account['plan_id'], ['free', 'custom']);
            if($plan_exists) {
                foreach(['plan_id', 'plan_settings', 'plan_expiration_date', 'plan_trial_done', 'plan_expiry_reminder'] as $field) {
                    if(array_key_exists($field, $account) && isset($columns[$field])) {
                        $update[$field] = $account[$field];
                    }
                }
            } else {
                $notes[] = 'plan_not_imported_missing';
            }
        } else {
            $notes[] = 'plan_kept';
        }

        if($update) {
            db()->where('user_id', $target_user->user_id)->update('users', $update);
        }

        return ['table' => 'users', 'status' => 'updated', 'fields' => array_keys($update), 'notes' => $notes];
    }

    private function import_table($table, $payload, $target_user, $decisions, &$remap, $package_dir) {
        $pk = $payload['pk'];
        $columns = $this->table_columns($table);
        $inserted = 0;
        $replaced = 0;
        $skipped = 0;
        $new_ids = 0;

        $own_action = $decisions['id_exists_own'] ?? 'replace';
        $other_action = $decisions['id_exists_other'] ?? 'new_id';
        $slug_action = $decisions['slug_taken'] ?? 'suffix';

        foreach(($payload['rows'] ?? []) as $row) {
            $row = $this->filter_row_columns($row, $columns);
            $old_id = $row[$pk] ?? null;
            $row['user_id'] = $target_user->user_id;

            if(in_array($table, ['biolink_blocks', 'data', 'qr_codes']) && !empty($row['link_id']) && $table !== 'qr_codes') {
                if(!empty($row['link_id']) && !isset($remap['links'][$row['link_id']]) && $table === 'biolink_blocks') {
                    $skipped++;
                    continue;
                }
            }
            if($table === 'data' && !empty($row['link_id']) && !isset($remap['links'][$row['link_id']])) {
                $skipped++;
                continue;
            }

            $row = $this->apply_fk_remap($table, $row, $remap, $decisions);

            if($table === 'links' && ($decisions['missing_theme'] ?? 'default') === 'default') {
                if(isset($row['settings'])) {
                    $settings = $this->decode_json($row['settings']);
                    $theme_id = (int) ($settings->biolink_theme_id ?? 0);
                    if($theme_id && $this->table_exists('biolinks_themes') && !db()->where('biolink_theme_id', $theme_id)->has('biolinks_themes')) {
                        $settings->biolink_theme_id = null;
                        $row['settings'] = json_encode($settings);
                    }
                }
            }

            if($table === 'links' && isset($row['domain_id']) && (int) $row['domain_id'] > 0) {
                $domain_ok = $this->table_exists('domains') && db()->where('domain_id', $row['domain_id'])->where('user_id', $target_user->user_id)->has('domains');
                if(!$domain_ok && ($decisions['missing_domain'] ?? 'main') === 'main') {
                    $row['domain_id'] = 0;
                }
            }

            if($table === 'links' && !empty($row['url'])) {
                $query = db()->where('url', $row['url']);
                if(isset($row['domain_id'])) $query->where('domain_id', $row['domain_id']);
                $taken = $query->getOne('links', ['link_id', 'user_id']);
                if($taken && (int) $taken->link_id !== (int) $old_id) {
                    if($slug_action === 'skip') {
                        $skipped++;
                        continue;
                    }
                    if($slug_action === 'suffix') {
                        $row['url'] = $this->unique_slug($row['url'], $row['domain_id'] ?? 0);
                    }
                }
            }

            $existing = $old_id && isset($columns[$pk]) ? db()->where($pk, $old_id)->getOne($table, [$pk, 'user_id']) : null;
            $use_new_id = false;

            if($existing) {
                if($target_id && (int) $existing->user_id === $target_id) {
                    if($own_action === 'skip') {
                        $skipped++;
                        $remap[$table][$old_id] = $old_id;
                        continue;
                    }
                    db()->where($pk, $old_id)->delete($table);
                    $replaced++;
                } else {
                    if($other_action === 'skip') {
                        $skipped++;
                        continue;
                    }
                    $use_new_id = true;
                }
            }

            if($use_new_id) {
                unset($row[$pk]);
            }

            $new_pk = db()->insert($table, $row);
            if($use_new_id) {
                $remap[$table][$old_id] = $new_pk;
                $new_ids++;
            } else {
                $remap[$table][$old_id] = $old_id;
                if(!$existing) $inserted++;
            }
        }

        if(isset($columns[$pk])) {
            $max = database()->query("SELECT MAX(`{$pk}`) AS `m` FROM `{$table}`")->fetch_object()->m ?? 0;
            if($max) database()->query("ALTER TABLE `{$table}` AUTO_INCREMENT = " . ((int) $max + 1));
        }

        return compact('table', 'inserted', 'replaced', 'skipped', 'new_ids');
    }

    private function apply_fk_remap($table, $row, $remap, $decisions) {
        $map = [
            'links' => ['project_id' => 'projects', 'splash_page_id' => 'splash_pages'],
            'biolink_blocks' => ['link_id' => 'links'],
            'qr_codes' => ['project_id' => 'projects'],
            'data' => ['link_id' => 'links', 'biolink_block_id' => 'biolink_blocks', 'project_id' => 'projects'],
            'splash_pages' => ['project_id' => 'projects'],
        ];
        foreach(($map[$table] ?? []) as $field => $source_table) {
            if(!isset($row[$field]) || !$row[$field]) continue;
            if(isset($remap[$source_table][$row[$field]])) {
                $row[$field] = $remap[$source_table][$row[$field]];
            }
        }
        if($table === 'links' && !empty($row['pixels_ids'])) {
            $ids = $this->decode_json($row['pixels_ids']);
            $ids = (array) $ids;
            $new = [];
            foreach($ids as $pid) {
                $new[] = $remap['pixels'][$pid] ?? $pid;
            }
            $row['pixels_ids'] = json_encode(array_values($new));
        }
        return $row;
    }

    private function unique_slug($url, $domain_id) {
        $base = $url;
        $i = 2;
        do {
            $try = $base . '-' . $i;
            $query = db()->where('url', $try)->where('domain_id', $domain_id);
            $exists = $query->has('links');
            $i++;
        } while($exists && $i < 1000);
        return $try;
    }

    private function filter_row_columns($row, $columns) {
        $out = [];
        foreach($row as $key => $value) {
            if($key[0] === '_') continue;
            if(!isset($columns[$key])) continue;
            if(is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }
            $out[$key] = $value;
        }
        return $out;
    }

    private function import_media($index, $package_dir, $decisions) {
        $copied = 0;
        $referenced = 0;
        $failed = 0;
        foreach($index as $item) {
            $key = $item['key'] ?? '';
            $filename = $item['filename'] ?? '';
            if(!$key || !$filename) continue;
            $zip_path = $package_dir . '/media/' . ($item['path'] ?? '');
            try {
                if(!empty($item['folder'])) {
                    if(is_dir(rtrim($zip_path, '/'))) {
                        $dest = rtrim(\Altum\Uploads::get_full_path($key) . $filename, '/') . '/';
                        if($this->offload_is_ready()) {
                            $this->upload_dir_to_offload($zip_path, \Altum\Uploads::get_path($key) . $filename . '/');
                        } else {
                            if(!is_dir($dest)) mkdir($dest, 0777, true);
                            $this->rcopy($zip_path, $dest);
                        }
                        $copied++;
                    } else {
                        $referenced++;
                    }
                    continue;
                }
                if(is_file($zip_path)) {
                    $this->store_file($key, $filename, $zip_path);
                    $copied++;
                } else {
                    $referenced++;
                }
            } catch(\Exception $exception) {
                $failed++;
            }
        }
        return ['table' => 'media', 'copied' => $copied, 'referenced' => $referenced, 'failed' => $failed];
    }

    private function store_file($uploads_key, $filename, $source_path) {
        if($this->offload_is_ready()) {
            $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
            $params = [
                'Bucket' => settings()->offload->storage_name,
                'Key' => UPLOADS_URL_PATH . \Altum\Uploads::get_path($uploads_key) . $filename,
                'SourceFile' => $source_path,
                'ContentType' => mime_content_type($source_path) ?: 'application/octet-stream',
            ];
            try {
                $s3->putObject($params + ['ACL' => 'public-read']);
            } catch(\Exception $exception) {
                $s3->putObject($params);
            }
            return;
        }
        $dest_dir = \Altum\Uploads::get_full_path($uploads_key);
        if(!is_dir($dest_dir)) mkdir($dest_dir, 0777, true);
        copy($source_path, $dest_dir . $filename);
    }

    private function upload_dir_to_offload($local_dir, $dest_prefix) {
        $s3 = new \Aws\S3\S3Client(get_aws_s3_config());
        $local_dir = realpath($local_dir);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($local_dir, \FilesystemIterator::SKIP_DOTS));
        foreach($files as $file) {
            if(!$file->isFile()) continue;
            $rel = ltrim(str_replace($local_dir, '', $file->getRealPath()), '/');
            $s3->putObject([
                'Bucket' => settings()->offload->storage_name,
                'Key' => UPLOADS_URL_PATH . $dest_prefix . $rel,
                'SourceFile' => $file->getRealPath(),
                'ACL' => 'public-read',
            ]);
        }
    }

    public function rrmdir($dir) {
        if(!is_dir($dir)) {
            if(is_file($dir)) @unlink($dir);
            return;
        }
        $items = array_diff(scandir($dir), ['.', '..']);
        foreach($items as $item) {
            $path = $dir . '/' . $item;
            is_dir($path) ? $this->rrmdir($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    private function rcopy($src, $dst) {
        $src = rtrim($src, '/') . '/';
        $dst = rtrim($dst, '/') . '/';
        if(!is_dir($dst)) mkdir($dst, 0777, true);
        foreach(array_diff(scandir($src), ['.', '..']) as $item) {
            $from = $src . $item;
            $to = $dst . $item;
            is_dir($from) ? $this->rcopy($from, $to) : copy($from, $to);
        }
    }
}
