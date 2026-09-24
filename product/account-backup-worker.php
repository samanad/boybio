<?php
if(php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("cli only\n");
}

$user_id = (int) ($argv[1] ?? 0);
$destination = ($argv[2] ?? '') === 'offload' ? 'offload' : 'pc';
$exclude_over = (int) ($argv[3] ?? 0);
if($user_id < 1) {
    fwrite(STDERR, "user id required\n");
    exit(1);
}

$_GET['altum'] = 'cron';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';

const DEBUG = 0;
const MYSQL_DEBUG = 0;
const LOGGING = 1;
const CACHE = 1;
const ALTUMCODE = 66;

require_once __DIR__ . '/app/init.php';

if(empty($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] === 'localhost') {
    $host = parse_url(SITE_URL, PHP_URL_HOST);
    if($host) $_SERVER['HTTP_HOST'] = $host;
}

\Altum\Cache::initialize();
\Altum\Plugin::initialize();
\Altum\Language::initialize();

$row = db()->where('user_id', $user_id)->getOne('users');
if(!$row) {
    fwrite(STDERR, "user not found\n");
    exit(1);
}

$user = json_decode(json_encode($row));
$backup = new \Altum\Models\AccountBackup();
$backup->run_queued_export($user, $destination, $exclude_over);
exit(0);
