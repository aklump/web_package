<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\Traits\ReaderTrait;
use AKlump\WebPackage\Traits\WriterTrait;
use AKlump\WebPackage\VersionScribeInterface;
use z4kn4fein\SemVer\Version;

class Yaml implements VersionScribeInterface {

  use ReaderTrait;
  use WriterTrait;

  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }

  public function read(): Version {
    $data = \Symfony\Component\Yaml\Yaml::parseFile($this->source);
    $data = array_change_key_case($data);

    return $this->getVersion($data['version'] ?? '');
  }

  /**
   * @inheritDoc
   */
  public function write(Version $version): bool {
    if (file_exists($this->source)) {
      $old = $this->read();
      if ($this->replaceVersionInFile($this->source, $old, $version)) {
        return TRUE;
      }
      // TODO Handle what to do here.
      throw new \RuntimeException(sprintf('The version %s appears in %s more than once; update failed.', (string) $old, $this->source));
    }
    $data = \Symfony\Component\Yaml\Yaml::dump(['version' => (string) $version]);

    return file_put_contents($this->source, $data);
  }
}
