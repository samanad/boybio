<?php
namespace Altum\Controllers;

defined('ALTUMCODE') || die();

class Official extends Controller {

    public function index() {
        /* Signed host list for clients */
        if(($_GET['format'] ?? '') === 'json') {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: public, max-age=300');
            echo json_encode(
                function_exists('resilience_signed_hosts_payload')
                    ? resilience_signed_hosts_payload()
                    : ['hosts' => [], 'signature' => ''],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
            die();
        }

        $hosts = function_exists('resilience_official_hosts') ? resilience_official_hosts() : [];
        $is_official = function_exists('resilience_is_official_host') ? resilience_is_official_host() : true;
        $offline = isset($_GET['offline']);

        \Altum\Title::set(l('resilience.official_title'));

        $data = [
            'hosts' => $hosts,
            'is_official' => $is_official,
            'offline' => $offline,
            'current_host' => preg_replace('/:\\d+$/', '', mb_strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''))),
        ];

        $view = new \Altum\View('official/index', (array) $this);
        $this->add_view_content('content', $view->run($data));
    }
}
