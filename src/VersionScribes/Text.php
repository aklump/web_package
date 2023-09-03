<?php

namespace AKlump\WebPackage\VersionScribes;

use z4kn4fein\SemVer\Version;

class Text implements \AKlump\WebPackage\VersionScribeInterface {

  use \AKlump\WebPackage\Traits\ReaderTrait;
  use \AKlump\WebPackage\Traits\WriterTrait;

  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }

  public function read(): Version {
    $contents = file_get_contents($this->source);

    return $this->getVersion($contents);
  }

  /**
   * @inheritDoc
   */
  public function write(Version $version): bool {
    // TODO: Implement write() method.
  }
}
