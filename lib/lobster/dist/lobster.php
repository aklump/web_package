<?php
/**
 * @file
 * Bootstrap the PHP portion of Lobster
 */

// Lobster core php
require_once getenv('LOBSTER_ROOT') . '/bootstrap.php';
require_once getenv('LOBSTER_ROOT') . '/functions.php';

global $lobster_conf;
$lobster_conf = json_decode(getenv('LOBSTER_JSON'));

// TODO This should not be hardcoded, but how to pass in json?
$lobster_conf->lobster->color_settings->escape = "\033";

$lobster_op = $lobster_conf->app->args[0];

// Now process the app-level includes
$base = getenv('LOBSTER_APP_ROOT') . '/includes';
if (file_exists($file = $base . '/bootstrap.php')) {
    require_once $file;
}
if (file_exists($file = $base . '/functions.php')) {
    require_once $file;
}
if (file_exists($file = $base . '/init.php')) {
    require_once $file;
}

$route_id = pathinfo($argv[0], PATHINFO_FILENAME);
if (file_exists($file = $base . '/preroute.php')) {
    require_once $file;
}
if (file_exists($file = $base . '/preroute.' . $route_id . '.php')) {
    require_once $file;
}

// TODO Rethink how this works, currently files are included twice
