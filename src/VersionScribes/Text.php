<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\Traits\WriterTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\Model\Version;

class Text implements VersionScribeInterface {

  use WriterTrait;

  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }

  public function read(): string {
    if (file_exists($this->source)) {
      $contents = file_get_contents($this->source);
      return Version::parse($contents, false);
    }

    return VersionScribeInterface::DEFAULT;
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

    return file_put_contents($this->source, $version);
  }

  public function getFilepath(): string {
    return $this->source;
  }

}
