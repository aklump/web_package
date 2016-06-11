<?php
/**
 * Basic parse of configuration files: .info, .json, .yml
 */
use \Symfony\Component\Yaml\Yaml;

require_once getenv('LOBSTER_ROOT') . '/bootstrap.php';

$file = $argv[1];

$info = array();
if (is_readable($file)) {
  $ext = pathinfo($file, PATHINFO_EXTENSION);
  switch ($ext) {
    case 'info':
      $info = parse_ini_file($file);
      break;
    case 'json':
      $info = json_decode(file_get_contents($file));
      break;
    case 'yml':
    case 'yaml':
      try {
        $info = Yaml::parse(file_get_contents($file));
      } catch (\Exception $e) {
        // Purposefully left blank.
      }
  }
}

if (isset($argv[2])) {
  print isset($info[$argv[2]]) ? $info[$argv[2]] : '';
}
else {
  print wp_key_value_chart($info);
}
