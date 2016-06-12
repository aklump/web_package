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
$options = array('install' => TRUE);
switch ($ext) {
  case 'ini':
  case 'info':
    $conf = new ConfigIni($dir, $file, $options);
    break;
  case 'json':
    $conf = new ConfigJson($dir, $file, $options);
    if ($file === 'composer.json' && $key === 'author') {
      $key = 'authors';
    }
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

  // The composer files needs author as 'authors'.
  if ($file === 'composer.json' && $key === 'authors') {
    $current = $conf->read($key, array(
      array(
        'name'     => '',
        'email'    => '',
        'homepage' => '',
        'role'     => '',
      ),
    ));
    $name = $value;
    $email = '';
    if (preg_match('/(.+?)\s*<(.+)>/', $value, $matches)) {
      $name = $matches[1];
      $email = $matches[2];
    }

    $found = FALSE;
    foreach ($current as $k => $item) {
      if ($email === $item['email'] || $name === $item['name']) {
        $current[$k] = array_merge($item, array(
          'name'  => $name,
          'email' => $email,
        ));
        $found = TRUE;
      }
    }

    if (!$found) {
      $current[0]['name'] = $name;
      $current[0]['email'] = $email;
    }
    $value = $current;
  }

  return $conf->write($key, $value) ? 0 : 1;
}

if (!empty($key)) {
  $stored = $conf->read($key, '');
  if (is_array($stored) && $file === 'composer.json' && $key === 'authors') {
    $stored = reset($stored);
    $stored = $stored['name'] . ' <' . $stored['email'] . '>';
  }
  print $stored;
}
else {
  print wp_key_value_chart($conf->readAll());
}
