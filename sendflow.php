<?php

/*
 * Plugin Name: Sendflow Web Push Notifications
 * Plugin URI: https://sendflow.pl/
 * Description: Sendflow is a Web Push Notification platform.
 * Version: 1.0.0
 * Author: Sendflow
 * Author URI: https://sendflow.pl/
 * License: MIT
 */

if (!defined('SENDFLOW_PLUGIN_VERSION')) {
    define('SENDFLOW_PLUGIN_VERSION', '1.0.0');
}

if (!defined('SENDFLOW_URL')) {
    define('SENDFLOW_URL', plugin_dir_url(__FILE__));
}

if (!defined('SENDFLOW_ENDPOINT')) {
    define('SENDFLOW_ENDPOINT', 'sendflow.pl');
}

if (!defined('SENDFLOW_PROTOCOL')) {
    define('SENDFLOW_PROTOCOL', 'https');
}

require_once plugin_dir_path(__FILE__) . 'sendflow-admin.php';
require_once plugin_dir_path(__FILE__) . 'sendflow-public.php';

add_action('init', array('SendflowAdmin', 'init'));
add_action('init', array('SendflowPublic', 'init'));
