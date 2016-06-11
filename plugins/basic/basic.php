<?php
/**
 * Basic parse of configuration files: .info, .json, .yml
 */
namespace AKlump\LoftLib\Component\Config;

require_once getenv('LOBSTER_ROOT') . '/bootstrap.php';

$dir = dirname($argv[1]);
$file = basename($argv[1]);
$key = isset($argv[2]) ? $argv[2] : NULL;
$value = isset($argv[3]) ? $argv[3] : NULL;

$ext = pathinfo($file, PATHINFO_EXTENSION);
$options = isset($value) ? array('install' => TRUE) : array();
switch ($ext) {
  case 'info':
    $conf = new ConfigIni($dir, $file, $options);
    break;
  case 'json':
    $conf = new ConfigJson($dir, $file, $options);
    break;
  case 'yml':
  case 'yaml':
    $conf = new ConfigYaml($dir, $file, $options);
    break;
}

//
//
// If we have a value then we need to write the value
if ($value) {
  $conf->write($key, $value);
}


if (isset($key)) {
  print $conf->read($key, '');
}
else {
  print wp_key_value_chart($conf->readAll());
}
