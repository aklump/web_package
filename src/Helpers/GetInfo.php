<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Traits\HasConfigTrait;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Yaml\Exception\ParseException;

class GetInfo {

  use HasConfigTrait;

  public function __construct($config) {
    $this->setConfig($config);
  }

  public function __invoke(): array {
    $config = $this->getConfig();
    $path_to_info = $config[Config::VERSION_FILE];
    if (!Path::isAbsolute($path_to_info)) {
      $path_to_info = Path::makeAbsolute($path_to_info, ROOT_PATH);
    }

    // TODO Turn this into a real serializer implementation.
    $ext = strtolower(Path::getExtension($path_to_info));
    $contents = file_get_contents($path_to_info);
    $data = [];
    switch ($ext) {
      case 'yml':
      case 'yaml':
        $data = \Symfony\Component\Yaml\Yaml::parse($contents) ?? [];
        if (!is_array($data)) {
          throw new \UnexpectedValueException("$path_to_info must be a YAML array.");
        }
        break;
      case 'json':
        $data = json_decode($contents, TRUE);
        if (!is_array($data)) {
          throw new \UnexpectedValueException("$path_to_info must be a JSON array.");
        }
        break;
    }

    return $this->mutate($data);
  }

  private function mutate(array $data) {
    if (!array_key_exists('author', $data) && array_key_exists('authors', $data)) {
      $data['author'] = $data['authors'][0]['name'] ?? '';
      $email = $data['authors'][0]['email'] ?? '';
      if ($email) {
        $data['author'] .= " <$email>";
      }
    }

    return $data;
  }
}
