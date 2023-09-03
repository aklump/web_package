<?php

namespace AKlump\WebPackage\Config\Loader;

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;

class YamlLoader extends FileLoader {


  /**
   * @inheritDoc
   */
  public function load($resource, string $type = NULL) {
    if (!file_exists($resource)) {
      return [];
    }
    $contents = file_get_contents($resource);
    $data = Yaml::parse($contents);

    return is_array($data) ? $data : [];
  }

  /**
   * @inheritDoc
   */
  public function supports($resource, string $type = NULL) {
    return is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), [
        'yml',
        'yaml',
      ]);
  }
}
