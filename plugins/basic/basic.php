<?php
/**
 * Basic parse of configuration files: .info, .json, .yml
 *
 * @deprecated
 */

namespace AKlump\LoftLib\Component\Config;

use AKlump\WebPackage\Output\FacePlant;
use AKlump\WebPackage\VersionScribeFactory;
use z4kn4fein\SemVer\Version;

require_once getenv('LOBSTER_ROOT') . '/lobster.php';
$path = $argv[1];
$dir = dirname($argv[1]);
$file = basename($argv[1]);
$key = isset($argv[2]) ? $argv[2] : NULL;
$value = isset($argv[3]) ? $argv[3] : NULL;

// In 2023 this was updated to use a class-based means of version read/write.
// It only works for the version key so we'll see if we can handle it, and if
// not, we'll pass it on to the legacy handler further down.
if ('version' === $key) {
  $factory = new VersionScribeFactory();
  $scribe = $factory($path);
  if (isset($scribe)) {
    $operation = !empty($value) ? 'write' : 'read';
    if ('read' === $operation) {
      print $scribe->read();
    }
    else {
      $version = Version::parse($value, FALSE);
      if (!$scribe->write($version)) {
        FacePlant::echo(sprintf('Failed to update version file: %s', $path));
        exit(1);
      }
    }
    exit(0);
  }
}


// Legacy Handlers.
$ext = pathinfo($file, PATHINFO_EXTENSION);
$options = array('install' => TRUE);
switch ($ext) {
  case 'ini':
    $conf = new ConfigIni($dir, $file, $options);
    break;

  case 'info':
    $conf = new ConfigDrupalInfo($dir, $file, $options);
    break;

  case 'json':
    $conf = new ConfigJson($dir, $file, $options);
    if ($file === 'composer.json' && $key === 'author') {
      $key = 'authors';
    }
    break;

  // @link https://robreid.io/semver/
  case 'semver':
    $key = ucfirst($key);
    $conf = new ConfigYaml($dir, $file, $options + [
        'inline' => 6,
        'indent' => 2,
      ]);
    break;

  case 'yml':
  case 'yaml':
    $conf = new ConfigYaml($dir, $file, $options + [
        'inline' => 6,
        'indent' => 2,
      ]);
    break;

  default:
    FacePlant::echo(sprintf('Unknown version file or type: %s', $path));
    exit(1);
}

//
//
// If we have a value then we need to write the value
if ($value) {

  // The composer files needs author as 'authors'.
  if ($file === 'composer.json' && $key === 'authors') {
    $current = $conf->read($key, array(
      array(
        'name' => '',
        'email' => '',
        'homepage' => '',
        'role' => '',
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
          'name' => $name,
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
  print is_array($stored) ? json_encode($stored, JSON_UNESCAPED_SLASHES) : $stored;
}
else {
  print wp_key_value_chart($conf->readAll());
}
