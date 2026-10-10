<?php

namespace AKlump\WebPackage\Config\Loader;

use Symfony\Component\Yaml\Yaml;

/**
 * Loads a YAML configuration file.
 *
 * @see \AKlump\WebPackage\Config\Loader\LegacyLoader for why this does not
 * extend a symfony/config loader class.
 */
class YamlLoader {

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
    $contents = file_get_contents($resource);
    $data = Yaml::parse($contents);

    return is_array($data) ? $data : [];
  }

  /**
   * @param string $resource
   *   The absolute path to the file.
   *
   * @return bool
   */
  public function supports($resource, ?string $type = NULL) {
    return is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), [
        'yml',
        'yaml',
      ]);
  }
}
