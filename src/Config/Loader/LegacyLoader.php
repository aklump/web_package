<?php

namespace AKlump\WebPackage\Config\Loader;

use Symfony\Component\Config\Loader\FileLoader;

class LegacyLoader extends FileLoader {

  /**
   * @inheritDoc
   */
  public function load($resource, string $type = NULL) {
    if (!file_exists($resource)) {
      return [];
    }
    $contents = explode(PHP_EOL, file_get_contents($resource));
    $contents = implode(PHP_EOL, array_filter($contents, function (string $line) {
      return !empty(trim($line)) && !preg_match('/^[\s#\/]+/', $line);
    }));
    $data = parse_ini_string($contents);

    return is_array($data) ? $data : [];
  }

  /**
   * @inheritDoc
   */
  public function supports($resource, string $type = NULL) {
    return is_string($resource) && !pathinfo($resource, PATHINFO_EXTENSION);
  }

}
