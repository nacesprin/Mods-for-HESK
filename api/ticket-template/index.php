<?php
define('IN_SCRIPT', 1);
define('HESK_PATH', '../../');
define('API_PATH', '../');
require(HESK_PATH . 'hesk_settings.inc.php');
require(HESK_PATH . 'inc/common.inc.php');
require(API_PATH . 'core/output.php');
require(API_PATH . 'dao/ticket_template_dao.php');

hesk_load_api_database_functions();
hesk_dbConnect();

// Routing
$request_method = $_SERVER['REQUEST_METHOD'];

if ($request_method == 'GET') {
    if (isset($_GET['id'])) {
        $results = get_ticket_template($hesk_settings, $_GET['id']);
    } else {
        $results = get_ticket_template($hesk_settings);
    }
    output($results);
} else {
    return http_response_code(405);
}