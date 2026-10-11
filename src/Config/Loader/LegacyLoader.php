<?php

namespace AKlump\WebPackage\Config\Loader;

/**
 * Loads the legacy extension-less (ini-style) configuration file.
 *
 * This deliberately does not extend a symfony/config loader class.  Their
 * load() and supports() signatures carry return types in symfony/config 7+
 * (mixed, bool) that cannot be declared on PHP 7.3, and have none in 5.4/6.4.
 */
class LegacyLoader {

  /**
   * @param string $resource
   *   The absolute path to the file.
   *
   * @return array
   */
  public function load($resource, ?string $type = NULL) {
    if (!file_exists($resource)) {
      return [];
    }
    $contents = explode(PHP_EOL, file_get_contents($resource));
    $contents = implode(PHP_EOL, array_filter($contents, function (string $line) {
      return !empty(trim($line)) && !preg_match('/^[\[\s#\/]+/', $line);
    }));
    $data = parse_ini_string($contents);

    return is_array($data) ? $data : [];
  }

  /**
   * @param string $resource
   *   The absolute path to the file.
   *
   * @return bool
   */
  public function supports($resource, ?string $type = NULL) {
    return is_string($resource) && !pathinfo($resource, PATHINFO_EXTENSION);
  }

}
