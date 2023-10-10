<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\Traits\WriterTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\Model\Version;

class Yaml implements VersionScribeInterface {

  use WriterTrait;

  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }


  public function getFilepath(): string {
    return $this->source;
  }

  public function read(): string {
    if (file_exists($this->source)) {
      $data = \Symfony\Component\Yaml\Yaml::parseFile($this->source) ?? [];
      if (!is_array($data)) {
        throw new \RuntimeException(sprintf("Invalid YAML in file %s\n%s", $this->source, $data));
      }
      $data = array_change_key_case($data);
    }

    if (empty($data['version'])) {
      return '';
    }

    return $data['version'];
  }

  /**
   * @inheritDoc
   */
  public function write(string $version): bool {
    if (file_exists($this->source)) {
      $old = $this->read();
      if ($this->replaceVersionInFile($this->source, $old, $version)) {
        return TRUE;
      }
      $data = \Symfony\Component\Yaml\Yaml::parseFile($this->source) ?? [];
      $data['version'] = (string) $version;

      return file_put_contents($this->source, \Symfony\Component\Yaml\Yaml::dump($data));
    }
    $data = \Symfony\Component\Yaml\Yaml::dump(['version' => (string) $version]);

    return file_put_contents($this->source, $data);
  }

}
