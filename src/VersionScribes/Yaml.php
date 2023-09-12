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
      $data = \Symfony\Component\Yaml\Yaml::parseFile($this->source);
      $data = array_change_key_case($data);
    }

    return $data['version'] ?? VersionScribeInterface::DEFAULT;
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
      // TODO Handle what to do here.
      throw new \RuntimeException(sprintf('The version %s does not appear in %s exactly once; update failed.', (string) $old, $this->source));
    }
    $data = \Symfony\Component\Yaml\Yaml::dump(['version' => (string) $version]);

    return file_put_contents($this->source, $data);
  }
}
